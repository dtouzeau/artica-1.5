<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}


if($argv[1]=="--detect"){detect_kernels();die();}
if($argv[1]=="--install"){upgrade($argv[2]);die();}

function detect_kernels(){
	$unix=new unix();
	if(!$GLOBALS["VERBOSE"]){
	if(is_file("/usr/share/artica-postfix/ressources/logs/kernel.lst")){
		if($unix->file_time_min("/usr/share/artica-postfix/ressources/logs/kernel.lst")<360){die();}
	}}
	
$users=new usersMenus();
if(($users->LinuxDistriCode<>"DEBIAN") && ($users->LinuxDistriCode<>"UBUNTU")){die();}


$unix=new unix();
$apt_cache=$unix->find_program("apt-cache");




if($apt_cache==null){
	echo "Could not find apt-cache\n";
	die();
}

if(system_is_overloaded(basename(__FILE__))){
	$unix->send_email_events("apt-cache aborted, system is overloaded..", "will restart analyzis in next cycle", "system");
	die();
}

echo "$apt_cache search linux-image\n";
exec("$apt_cache search linux-image",$results);

while (list ($num, $val) = each ($results) ){
	
	if(preg_match("#linux-image-([0-9\.]+)-([0-9]+)-(.+?)\s+-\s+(.+?)$#",$val,$re)){
		$array["DPKG"][]=array("VERSION"=>$re[1],"BUILD"=>$re[2],"ARCH"=>$re[3],"INFOS"=>$re[4],
		"PACKAGE"=>"linux-image-{$re[1]}-{$re[2]}-{$re[3]}",
		"FULL_VERSION"=>"{$re[1]}-{$re[2]}-{$re[3]}"
		);
		
	}
	
	
	
}

$array["INFOS"]=CpuFamilyInfos();

@file_put_contents("/usr/share/artica-postfix/ressources/logs/kernel.lst",base64_encode(serialize($array)));

}

function CpuFamilyInfos(){
	$a=file_get_contents("/proc/cpuinfo");

	$f=explode("\n",$a);

	
	while (list ($num, $val) = each ($f) ){
		if(preg_match("#cpu family.+?([0-9]+)#",$val,$re)){
			$array["CPU_FAMILY"]=$re[1];
			continue;
		}else{
			
		}
		if(preg_match("#flags\s+\s+:.+?\s+lm\s+#",$val,$re)){
			$array["64BITS"]=true;
		}
		
		if(preg_match("#flags\s+\s+:.+?\s+ht\s+#",$val,$re)){
			$array["HT"]=true;
		}	

		if(preg_match("#model name.+?:(.+)#",$val,$re)){
			$array["MODEL"]=trim($re[1]);
		}			
		
		if(preg_match("#processor\s+.+?([0-9]+)#",$val,$re)){
			if($GLOBALS["VERBOSE"]){echo "processor:{$re[1]}\n";}
			$array["PROCESSOR"]=trim($re[1]);
		}
		
		
	}
$unix=new unix();	
$uname=$unix->find_program("uname");	
exec("$uname -r",$a);
$current_version=trim(@implode(" ",$a));
$array["PROCESSOR"]=$array["PROCESSOR"]+1;
$array["CURRENT"]=$current_version;	
return $array;	
}

function upgrade($package){
	
	system('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/bin/X11');	
	$unix=new unix();
	$apt_get=$unix->find_program("apt-get");
	if($apt_get==null){return;}
	$cmd="DEBIAN_FRONTEND=noninteractive $apt_get -o Dpkg::Options::=\"--force-confnew\" -y install $package";
	if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
	exec("DEBIAN_FRONTEND=noninteractive $apt_get -o Dpkg::Options::=\"--force-confnew\" -y install $package 2>&1",$results);
	$infos=@implode("\n",$results);
	if($GLOBALS["VERBOSE"]){echo $infos."\n";}
	unset($results);
	$update_grup=$unix->find_program("update-grub");
	if(is_file($update_grup)){
		exec($update_grup,$results);
	}
	if($GLOBALS["VERBOSE"]){echo @implode("\n",$results)."\n";}
	$infos=$infos."\n".@implode("\n",$results);
	
	send_email_events("Kernel $package upgrade results",$infos,"system");
	@unlink("/usr/share/artica-postfix/ressources/logs/kernel.lst");
	if(!$GLOBALS["VERBOSE"]){shell_exec("reboot");};
	
}






?>