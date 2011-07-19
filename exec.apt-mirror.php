<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;$GLOBALS["RESTART"]=true;}

if($argv[1]=="--mirror-list"){BuildMirrorConf();die();}
if($argv[1]=="--vhost"){BuildVhost();die();}
if($argv[1]=="--perform"){perform();die();}
if($argv[1]=="--schedules"){schedules();die();}


function schedules(){
	@unlink("/etc/cron.d/apt-mirror");
	$php=LOCATE_PHP5_BIN2();
	$file=__FILE__;
	$sock=new sockets();
	shell_exec("/bin/rm -f /etc/cron.d/apt-mirror-* >/dev/null 2>&1");
	
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfigSchedule")));
	if(!is_array($config)){return;}
		$count=0;
		while (list ($uid, $schedule) = each ($config) ){
			if(trim($schedule)==null){continue;}
			$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
			$f[]="MAILTO=\"\"";
			$f[]="$schedule  root $php $file --perform >/dev/null 2>&1";
			$f[]="";
			@file_put_contents("/etc/cron.d/apt-mirror-$count",@implode("\n",$f));
			$count++;
			unset($f);
		}	
	}


function perform(){
	$sock=new sockets();
	$EnableAptMirror=$sock->GET_INFO("EnableAptMirror");
	
	if($EnableAptMirror<>1){
		echo "Starting......: Debian mirror feature is disabled\n";
		apt_mirror_events_file("Debian mirror feature is disabled");
		die();
	}	
	
	$unix=new unix();
	$pidpath="/etc/artica-postfix/cron.2/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidpath);
	
	if($unix->process_exists($pid)){
			echo "Starting......: Debian mirror already executed PID $pid\n";
			apt_mirror_events_file("Debian mirror already executed PID $pid");
			writelogs("Debian mirror already executed PID $pid",__FUNCTION__,__FILE__,__LINE__);
			die();
	}
	$getmypid=getmypid();
	@file_put_contents($pidpath,$getmypid);
	echo "Starting......: Debian mirror PID $getmypid\n";
	apt_mirror_events("INFO: Starting PID $getmypid",@implode("\n",$results));
	
	
	writelogs("New Pid:$getmypid -> $pidpath",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Pid path: $pidpath\n";
	apt_mirror_events_file("Starting......: Pid path: $pidpath");
	
	apt_mirror_events_file("-> BuildMirrorConf()");
	writelogs("-> BuildMirrorConf()",__FUNCTION__,__FILE__,__LINE__);
	
	BuildMirrorConf();
	apt_mirror_events_file("-> AptMirrorConfig");
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfig")));
	if($config["webserverpath"]==null){
		apt_mirror_events_file("-> No destination path set");
		echo "Starting......: Debian mirror No destination path set\n";
		return;
	}			
	$t1=time();
	$apt_mirror_bin=$unix->find_program("apt-mirror");
	
	apt_mirror_events_file("-> $t1 $apt_mirror_bin 2>&1");
	if(!is_file($apt_mirror_bin)){die();}
	exec("$apt_mirror_bin 2>&1",$results);
	$t2=time();
	$distanceOfTimeInWords=distanceOfTimeInWords($t1,$t2);
	apt_mirror_events_file("$distanceOfTimeInWords ($t1,$t2)");
	apt_mirror_events_file(@implode("\n",$results));
	
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#^([0-9\.]+)\s+([a-zA-Z])\s+will be downloaded into archive#",$line,$re)){
			$repos_size="{$re[1]} {$re[2]}: ";
		}
	}
	apt_mirror_events("INFO: Starting calculate {$config["webserverpath"]} directory size");
	$du_bin=$unix->find_program("du");
	if(is_dir($config["webserverpath"])){
		exec("$du -h -s {$config["webserverpath"]}",$results2);
		while (list ($num, $line) = each ($results2) ){	
			if(preg_match("#^([0-9\.,]+)([A-Za-z]+)\s+#",$line,$ri)){
				$sock->SET_INFO("AptMirrorRepoSize","{$ri[1]}{$ri[2]}");
				$repos_size="{$ri[1]}{$ri[2]}";
			}
		}
	}
	apt_mirror_events("INFO: Starting calculate {$config["webserverpath"]} directory size=$repos_size",@implode("\n",$results2));
	apt_mirror_events_file("$repos_size$distanceOfTimeInWords -> mysql");
	apt_mirror_events("$repos_size$distanceOfTimeInWords",@implode("\n",$results));

	
}

function apt_mirror_events($subject,$text=null){
	apt_mirror_events_file($subject,$text);
	$text=addslashes($text);
	$subject=addslashes($subject);
	$sql="INSERT INTO debian_mirror_events (zDate,subject,text) VALUES(NOW(),'$subject','$text');";
	$q=new mysql();
	
	apt_mirror_events_file($sql);
	
	
	$q->QUERY_SQL($sql,"artica_events");	
	
}
function apt_mirror_events_file($subject,$text){
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/apt-mirror.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$pid] $date $subject:: $text\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}


function BuildVhost(){
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfig")));
	$EnableAptMirror=$sock->GET_INFO("EnableAptMirror");

	if($EnableAptMirror<>1){
		echo "Starting......: Debian mirror feature is disabled\n";
		@file_put_contents("/usr/local/apache-groupware/conf/mirror-vhosts.conf","#");
		return;
	}
	
	if($config["webserverpath"]==null){
		echo "Starting......: Debian mirror No destination path set\n";
		@file_put_contents("/usr/local/apache-groupware/conf/mirror-vhosts.conf","#");
		return;
	}
	
	if($config["webservername"]==null){
		echo "Starting......: Debian mirror no web servername set\n";
		@file_put_contents("/usr/local/apache-groupware/conf/mirror-vhosts.conf","#");
		return;
	}	
	

	
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	echo "Starting......: Debian mirror apache listen on port $ApacheGroupWarePort\n";
	
$conf[]="<VirtualHost *:80>";
$conf[]="	ServerName webdav.touzeau.com";
$conf[]="	ServerAdmin webmaster@webdav.touzeau.com";
$conf[]="	DocumentRoot {$config["webserverpath"]}";
if($config["DebianEnabled"]==1){
	$conf[]="\tAlias /debian {$config["webserverpath"]}/{$config["debian_mirror"]}/debian";
	$conf[]="\t<Directory {$config["webserverpath"]}/mirror/{$config["debian_mirror"]}/debian>";
	$conf[]="\t\tOptions +Indexes +SymlinksIfOwnerMatch";
	$conf[]="\t\tIndexOptions NameWidth=* +SuppressDescription";
	$conf[]="\t</Directory>";
	$conf[]="";
	$conf[]="\tAlias /security-debian {$config["webserverpath"]}/mirror/security.debian.org";
	$conf[]="\t<Directory {$config["webserverpath"]}/mirror/security.debian.org>";
	$conf[]="\t\tOptions +Indexes +SymlinksIfOwnerMatch";
	$conf[]="\tIndexOptions NameWidth=* +SuppressDescription";
	$conf[]="\t</Directory>";
	$conf[]="";
	$conf[]="\tAlias /debian-volatile {$config["webserverpath"]}/mirror/volatile.debian.org/debian-volatile";
	$conf[]="\t<Directory {$config["webserverpath"]}/mirror/volatile.debian.org/debian-volatile>";
	$conf[]="\t\tOptions +Indexes +SymlinksIfOwnerMatch";
	$conf[]="\t\tIndexOptions NameWidth=* +SuppressDescription";
	$conf[]="\t</Directory>";
	$conf[]="";
	$conf[]="\tAlias /debian-backports {$config["webserverpath"]}/mirror/www.backports.org/debian";
	$conf[]="\t\t<Directory {$config["webserverpath"]}/mirror/www.backports.org/debian>";
	$conf[]="\t\tOptions +Indexes +SymlinksIfOwnerMatch";
	$conf[]="\t\tIndexOptions NameWidth=* +SuppressDescription";
	$conf[]="\t</Directory>";
}
$conf[]="";
if($config["UbuntuEnabled"]==1){
	$conf[]="\tAlias /ubuntu {$config["webserverpath"]}/mirror/{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu";
	$conf[]="\t\t<Directory {$config["webserverpath"]}/mirror/{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu>";
	$conf[]="\t\tOptions +Indexes +SymlinksIfOwnerMatch";
	$conf[]="\t\tIndexOptions NameWidth=* +SuppressDescription";
	$conf[]="\t</Directory>";
	$conf[]="\tLogLevel debug";
	$conf[]="\t</VirtualHost>";	
}
$conf[]="";

@file_put_contents("/usr/local/apache-groupware/conf/mirror-vhosts.conf",@implode("\n",$conf));
echo "Starting......: Debian mirror set virtual host config done (mirror-vhosts.conf)\n";	
}


function BuildMirrorConf(){

	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("AptMirrorConfig")));
	$EnableAptMirror=$sock->GET_INFO("EnableAptMirror");
	
	if($EnableAptMirror<>1){echo "Starting......: Debian mirror feature is disabled\n";return;}
	if($config["webserverpath"]==null){echo "Starting......: Debian mirror No destination path set\n";return;}
	@mkdir($config["webserverpath"],666,true);
	
	$paths[]="mirror";
	$paths[]="skel";
	$paths[]="var";
	while (list ($key, $line) = each ($paths) ){@mkdir("{$config["webserverpath"]}/$line");}
	
	
	
	$deb_lenny_pp[]="{$config["debian_mirror"]}/debian/ lenny main contrib non-free";
	$deb_lenny_pp[]="http://security.debian.org/ lenny/updates main contrib non-free";
	$deb_lenny_pp[]="http://volatile.debian.org/debian-volatile lenny/volatile main contrib non-free";
	$deb_lenny_pp[]="{$config["debian_mirror"]}/debian lenny main/debian-installer";
	$deb_lenny_pp[]="{$config["debian_mirror"]}/debian lenny-proposed-updates main contrib non-free";
	$deb_lenny_src[]="deb-src {$config["debian_mirror"]}/debian lenny-proposed-updates main contrib non-free";
	$deb_lenny_src[]="deb-src http://volatile.debian.org/debian-volatile lenny/volatile main contrib non-free";
	$deb_lenny_src[]="deb-src http://security.debian.org/ lenny/updates main contrib non-free";
	$deb_lenny_src[]="deb-src {$config["debian_mirror"]}/debian/ lenny main contrib non-free";
	if($config["UbuntuCountryCode"]==null){$config["UbuntuCountryCode"]="us";}
	if($config["nthreads"]==null){$config["nthreads"]=2;}
	if(!is_numeric($config["nthreads"])){$config["nthreads"]=2;}
	
$f[]="set base_path    {$config["webserverpath"]}";
$f[]="set mirror_path  \$base_path/mirror";
$f[]="set skel_path    \$base_path/skel";
$f[]="set var_path     \$base_path/var";
$f[]="set cleanscript \$var_path/clean.sh";
$f[]="set defaultarch  i386";
$f[]="set nthreads     {$config["nthreads"]}";
$f[]="set _tilde 0";
	
	

if($config["hardy"]==1){
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy main restricted";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy-updates main restricted";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy-security main restricted";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy universe multiverse";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy-updates universe multiverse";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy-security universe multiverse";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu hardy-backports main restricted universe multiverse";
	
	$cleans["http://archive.ubuntu.com/ubuntu"]="http://archive.ubuntu.com/ubuntu"; 
	$cleans["http://archive.canonical.com/ubuntu"]="http://archive.canonical.com/ubuntu";
	$cleans["http://packages.medibuntu.org"]="http://packages.medibuntu.org";
	$cleans["http://security.ubuntu.com/ubuntu"]="http://security.ubuntu.com/ubuntu";
	
}

if($config["intrepid"]==1){
	$deb_ubuntu[]="http://old-releases.ubuntu.com/ubuntu intrepid main restricted";
	$deb_ubuntu[]="http://old-releases.ubuntu.com/ubuntu intrepid-updates main restricted";
	$deb_ubuntu[]="http://old-releases.ubuntu.com/ubuntu intrepid universe";
	$deb_ubuntu[]="http://old-releases.ubuntu.com/ubuntu intrepid-updates universe";
	$deb_ubuntu[]="http://old-releases.ubuntu.com/ubuntu intrepid multiverse";
	$deb_ubuntu[]="http://old-releases.ubuntu.com/ubuntu intrepid-updates multiverse";
	$deb_ubuntu[]="http://archive.canonical.com/ubuntu intrepid partner";
	
	$cleans["http://old-releases.ubuntu.com/ubuntu"]="http://old-releases.ubuntu.com/ubuntu"; 
	$cleans["http://archive.ubuntu.com/ubuntu"]="http://archive.ubuntu.com/ubuntu"; 
	$cleans["http://archive.canonical.com/ubuntu"]="http://archive.canonical.com/ubuntu";
	$cleans["http://security.ubuntu.com/ubuntu"]="http://security.ubuntu.com/ubuntu";	
}


if($config["Jaunty"]==1){
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu jaunty main restricted universe multiverse";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu jaunty-updates main restricted universe multiverse";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu jaunty-security main restricted universe multiverse";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu jaunty-security main restricted universe multiverse";
	$deb_ubuntu[]="http://archive.canonical.com/ubuntu jaunty partner";
	$deb_ubuntu[]="http://packages.medibuntu.org/ jaunty free non-free";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu jaunty main/debian-installer restricted/debian-installer universe/debian-installer multiverse/debian-installer";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu jaunty-updates main/debian-installer universe/debian-installer";
	$deb_ubuntu[]="http://archive.ubuntu.com/ubuntu jaunty-security main/debian-installer"; 
	
	$cleans["http://archive.ubuntu.com/ubuntu"]="http://archive.ubuntu.com/ubuntu"; 
	$cleans["http://archive.canonical.com/ubuntu"]="http://archive.canonical.com/ubuntu";
	$cleans["http://packages.medibuntu.org"]="http://packages.medibuntu.org";
	$cleans["http://security.ubuntu.com/ubuntu"]="http://security.ubuntu.com/ubuntu";	
	
}

if($config["maverick"]==1){
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick-updates main restricted";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick main restricted";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick universe";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick-updates universe";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick multiverse";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick-updates multiverse";
	$deb_ubuntu_src[]="deb-src http://extras.ubuntu.com/ubuntu maverick main";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu maverick-security main restricted";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu maverick-security universe";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu maverick-security multiverse";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick-updates main restricted";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick universe";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick-updates universe";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick multiverse";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu maverick-updates multiverse";
	$deb_ubuntu[]="http://extras.ubuntu.com/ubuntu maverick main";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu maverick-security main restricted";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu maverick-security universe";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu maverick-security multiverse";
	$deb_ubuntu[]="http://download.virtualbox.org/virtualbox/debian maverick non-free";	
	
	$cleans["http://archive.ubuntu.com/ubuntu"]="http://archive.ubuntu.com/ubuntu"; 
	$cleans["http://archive.canonical.com/ubuntu"]="http://archive.canonical.com/ubuntu";
	$cleans["http://packages.medibuntu.org"]="http://packages.medibuntu.org";
	$cleans["http://security.ubuntu.com/ubuntu"]="http://security.ubuntu.com/ubuntu";	

}


if($config["lucid"]==1){
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid-updates main restricted";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid main restricted";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid universe";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid-updates universe";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid multiverse";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid-updates multiverse";
	$deb_ubuntu_src[]="deb-src http://extras.ubuntu.com/ubuntu lucid main";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu lucid-security main restricted";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu lucid-security universe";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu lucid-security multiverse";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid-updates main restricted";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid universe";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid-updates universe";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid multiverse";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu lucid-updates multiverse";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu lucid-security main restricted";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu lucid-security universe";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu lucid-security multiverse";
	$deb_ubuntu[]="http://download.virtualbox.org/virtualbox/debian lucid non-free";	
	
	$cleans["http://archive.ubuntu.com/ubuntu"]="http://archive.ubuntu.com/ubuntu"; 
	$cleans["http://archive.canonical.com/ubuntu"]="http://archive.canonical.com/ubuntu";
	$cleans["http://packages.medibuntu.org"]="http://packages.medibuntu.org";
	$cleans["http://security.ubuntu.com/ubuntu"]="http://security.ubuntu.com/ubuntu";	

}

if($config["karmic"]==1){
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic-updates main restricted";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic main restricted";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic universe";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic-updates universe";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic multiverse";
	$deb_ubuntu_src[]="deb-src http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic-updates multiverse";
	$deb_ubuntu_src[]="deb-src http://extras.ubuntu.com/ubuntu karmic main";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu karmic-security main restricted";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu karmic-security universe";
	$deb_ubuntu_src[]="deb-src http://security.ubuntu.com/ubuntu karmic-security multiverse";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic-updates main restricted";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic universe";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic-updates universe";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic multiverse";
	$deb_ubuntu[]="http://{$config["UbuntuCountryCode"]}.archive.ubuntu.com/ubuntu karmic-updates multiverse";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu karmic-security main restricted";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu karmic-security universe";
	$deb_ubuntu[]="http://security.ubuntu.com/ubuntu karmic-security multiverse";
	$deb_ubuntu[]="http://download.virtualbox.org/virtualbox/debian karmic non-free";	
	
	$cleans["http://archive.ubuntu.com/ubuntu"]="http://archive.ubuntu.com/ubuntu"; 
	$cleans["http://archive.canonical.com/ubuntu"]="http://archive.canonical.com/ubuntu";
	$cleans["http://packages.medibuntu.org"]="http://packages.medibuntu.org";
	$cleans["http://security.ubuntu.com/ubuntu"]="http://security.ubuntu.com/ubuntu";	

}

if($config["DebianEnabled"]==1){
	$cleans[]="clean {$config["debian_mirror"]}";	
	while (list ($key, $line) = each ($deb_lenny_pp) ){
		$f[]="deb-i386 ".$line;
		if($config["Debian64"]==1){$f[]="deb-amd64 ".$line;}
	}
	$f[]="";
}

if($config["UbuntuEnabled"]==1){
	if(is_array($deb_ubuntu)){		
		while (list ($key, $line) = each ($deb_ubuntu) ){
			$f[]="deb-i386 ".$line;
			if($config["Ubuntu64"]==1){$f[]="deb-amd64 ".$line;}
		}
	}
	$f[]="";
}

while (list ($key, $line) = each ($cleans) ){$f[]="clean ".$line;}

@file_put_contents("/etc/apt/mirror.list",@implode("\n",$f));
echo "Starting......: Debian /etc/apt/mirror.list done.\n";
}
//http://doc.ubuntu-fr.org/sources.list
