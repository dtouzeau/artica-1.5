<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.os.system.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

$_GET["APT-GET"]="/usr/bin/apt-get";
if(!is_file($_GET["APT-GET"])){die();}

if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if($argv[1]=='--update'){GetUpdates();die();}
if($argv[1]=='--upgrade'){UPGRADE();die();}
if($argv[1]=='--sources-list'){CheckSourcesList();die();}




function GetUpdates(){
@mkdir("/usr/share/artica-postfix/ressources/logs/web",755,true);
@unlink("/usr/share/artica-postfix/ressources/logs/web/debian.update.html");
if(COUNT_REPOS()==0){INSERT_DEB_PACKAGES();}	
$unix=new unix();
$tmpf=$unix->FILE_TEMP();
CheckSourcesList();
$sock=new sockets();	
$ini=new Bs_IniHandler();
$configDisk=trim($sock->GET_INFO('ArticaAutoUpdateConfig'));	
$ini->loadString($configDisk);	
$AUTOUPDATE=$ini->_params["AUTOUPDATE"];
if(trim($AUTOUPDATE["auto_apt"])==null){$AUTOUPDATE["auto_apt"]="no";}

shell_exec("{$_GET["APT-GET"]} update >/dev/null 2>&1");
shell_exec("{$_GET["APT-GET"]} -f install --force-yes >/dev/null 2>&1");
shell_exec("{$_GET["APT-GET"]} upgrade -s >$tmpf 2>&1");
	
$datas=@file_get_contents($tmpf);
$tbl=explode("\n",$datas);
writelogs("Found ". strlen($datas)." bytes for apt",__FUNCTION__,__FILE__,__LINE__);
@unlink($tmpf);

	while (list ($num, $val) = each ($tbl) ){
		if($val==null){continue;}
		if(preg_match("#^Inst\s+(.+?)\s+#",$val,$re)){
			$packages[]=$re[1];
			writelogs("Found {$re[1]} new package",__FUNCTION__,__FILE__,__LINE__);
			//dpkg_configure_a();
			
		}else{
			if(preg_match("#dpkg was interrupted.+?dpkg --configure -a#",$val)){
				writelogs("Error found ",__FUNCTION__,__FILE__,__LINE__);
			}
			writelogs("Garbage \"$val\"",__FUNCTION__,__FILE__,__LINE__);
		}
		
	}

	$count=count($packages);
	if($count>0){
		@file_put_contents("/etc/artica-postfix/apt.upgrade.cache",implode("\n",$packages));
		$text="You can perform upgrade of linux packages for\n".@file_get_contents("/etc/artica-postfix/apt.upgrade.cache");
		send_email_events("new upgrade $count packages(s) ready",$text,"system");
		
		$paragraph=Paragraphe('64-infos.png',"$count {system_packages}",
		"$count {system_packages_can_be_upgraded}","javascript:Loadjs('artica.repositories.php');
		","{system_packages_can_be_upgraded}",300,80);
		@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/debian.update.html", $paragraph);
		shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/web/debian.update.html");
		
		if($AUTOUPDATE["auto_apt"]=="yes"){UPGRADE();}
	}else{
		writelogs("No new packages...",__FUNCTION__,__FILE__,__LINE__);
		@unlink("/etc/artica-postfix/apt.upgrade.cache");
	}



}

function dpkg_configure_a(){
	
	$unix=new unix();
	$binpath=$unix->find_program("dpkg-reconfigure");
	if(strlen($binpath)==null){return;}
	exec("$binpath -a -f -p 2>&1",$results);
	while (list ($num, $val) = each ($results) ){
		$val=strip_error_perl($val);
		if($val==null){continue;}
		if(preg_match("#-reconfigure:\s+(.+?)\s+is broken or not fully installed#",$val,$re)){
			$f[]="ERROR DETECTED! on {$re[1]} package, see artica support \"$val\"";
			continue;
		}
		$f[]=$val;
	}
	
	if(count($f)>0){
		send_email_events("System: DPKG reconfigure results","It seems that the system need to reconfigure package, this is the results:".@implode("\n",$f),"system");
	}
	
}


function strip_error_perl($line){
	
if(strpos($line,"warning: Setting locale failed.")>0){return null;}
if(strpos($line,"Please check that your locale settings")>0){return null;}
if(strpos($line,"LANGUAGE =")>0){return null;}
if(strpos($line,"LC_ALL =")>0){return null;}
if(strpos($line,'LANG = "')>0){return null;}
if(strpos($line,"supported and installed on your system.")>0){return null;}
return $line;	
	
	
}

function COUNT_REPOS(){
	$sql="SELECT COUNT(package_name) FROM debian_packages";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return($ligne["tcount"]);
}



function INSERT_DEB_PACKAGES(){
	if(!is_file("/usr/bin/dpkg")){die();}
	$sql="TRUNCATE TABLE `debian_packages`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();	
	shell_exec("/usr/bin/dpkg -l >$tmpf 2>&1");
	$datas=@file_get_contents($tmpf);
	@unlink($tmpf);
	$tbl=explode("\n",$datas);
	while (list ($num, $val) = each ($tbl) ){
		if($val==null){continue;}
			
	if(preg_match("#^([a-z]+)\s+(.+?)\s+(.+?)\s+(.+)#",$val,$re)){
			$content=addslashes($re[4]);
			$pname=$re[2];
			$package_description=addslashes(PACKAGE_EXTRA_INFO($pname));
			
		$sql="INSERT INTO debian_packages(package_status,package_name,package_version,package_info,package_description) 
  		VALUES('{$re[1]}','$pname','{$re[3]}','$content','$package_description');";
  		$q->QUERY_SQL($sql,"artica_backup");  			
			
		}	
	}
}

function PACKAGE_EXTRA_INFO($pname){
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();		
	shell_exec("/usr/bin/dpkg-query -p $pname >$tmpf 2>&1");
	$datas=@file_get_contents($tmpf);
	@unlink($tmpf);
}

function UPGRADE(){
	@unlink("/usr/share/artica-postfix/ressources/logs/web/debian.update.html");
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();		
$txt="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin\n";
$txt=$txt."echo \$PATH >$tmpf 2>&1\n";
$txt=$txt."rm -f $tmpf\n";

$tmpf=$unix->FILE_TEMP();	
@file_put_contents($tmpf,$txt);
@chmod($tmpf,'0777');
shell_exec($tmpf);

$tmpf=$unix->FILE_TEMP();
$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes update >$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);


$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes install -f >$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);


$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes upgrade >>$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);

$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes dist-upgrade >>$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);

$cmd="DEBIAN_FRONTEND=noninteractive {$_GET["APT-GET"]} -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes autoremove >>$tmpf 2>&1";
writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);

$datas=@file_get_contents($tmpf);
$datassql=addslashes($datas);


$q=new mysql();
$sql="INSERT INTO debian_packages_logs(zDate,package_name,events,install_type) VALUES(NOW(),'artica-upgrade','$datassql','upgrade');";
$q->QUERY_SQL($sql,"artica_backup");  	
@unlink('/etc/artica-postfix/apt.upgrade.cache');

send_email_events("Debian/Ubuntu System upgrade operation",$datas,"system");
INSERT_DEB_PACKAGES();
send_email_events("Rebooting after upgrade operation","reboot command has been performed","system");
shell_exec("reboot");

}


function CheckSourcesList(){
if(is_file("/etc/lsb-release")){if($GLOBALS["VERBOSE"]){ "CheckSourcesList: Ubuntu system, aborting\n";}}	
if(!is_file("/etc/debian_version")){return;}
$ver=trim(@file_get_contents("/etc/debian_version"));
preg_match("#^([0-9]+)\.#",$ver,$re);
if(preg_match("#squeeze\/sid#",$ver)){$Major=6;}
$Major=$re[1];
echo "CheckSourcesList: Debian version $Major\n";
if(!is_numeric($Major)){ echo "CheckSourcesList: Debian version failed \"$ver\"\n";return;}

$f=@explode("\n",@file_get_contents("/etc/apt/sources.list"));
$detected=false;
while (list ($num, $val) = each ($f) ){if(preg_match("#deb\s+http:.+?#",$val)){echo "CheckSourcesList:  /etc/apt/sources.list correct, return\n";return;}}

$f=array();
if($Major==5){
	$f[]="deb http://ftp.fr.debian.org/debian/ lenny main";
	$f[]="deb-src http://ftp.fr.debian.org/debian/ lenny main";
	$f[]="deb http://security.debian.org/ lenny/updates main";
	$f[]="deb-src http://security.debian.org/ lenny/updates main";
	$f[]="deb http://volatile.debian.org/debian-volatile lenny/volatile main";
	$f[]="deb-src http://volatile.debian.org/debian-volatile lenny/volatile main";	
	$f[]="deb http://backports.debian.org/debian-backports lenny-backports main contrib non-free";
	@file_put_contents("/etc/apt/sources.list",@implode("\n",$f));
	echo "CheckSourcesList:  /etc/apt/sources.list configured, done...\n";
}
if($Major==6){
		$f[]="deb http://ftp.fr.debian.org/debian/ squeeze main";
		$f[]="deb-src http://ftp.fr.debian.org/debian/ squeeze main";
		$f[]="deb http://security.debian.org/ squeeze/updates main";
		$f[]="deb-src http://security.debian.org/ squeeze/updates main";
		$f[]="deb http://ftp.fr.debian.org/debian/ squeeze-updates main";
		$f[]="deb-src http://ftp.fr.debian.org/debian/ squeeze-updates main";
		@file_put_contents("/etc/apt/sources.list",@implode("\n",$f));
		echo "CheckSourcesList:  /etc/apt/sources.list configured, done...\n";	
}

}



?>