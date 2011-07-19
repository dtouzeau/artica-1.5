<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.postfix-multi.inc');
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.assp-multi.inc');


if($argv[1]=='--org'){
		CheckInstall($argv[2]);
		$ass=new assp_multi($argv[2]);
		$ass->WriteConf();
		echo "Starting......: ASSP writing configuration done\n";
		if(!$ass->running){
			$instance=str_replace(" ","-",$ass->ou);
			@chdir("/usr/share/assp-$instance");
			@chmod("/usr/share/assp-$instance/assp.pl",0777);
			@shell_exec("/usr/share/assp-$instance/assp.pl &");
		}
}


function CheckInstall($ou){
	$instance=str_replace(" ","-",$ou);
	$root="/usr/share/assp-$instance";
	@mkdir("$root",0755,true);
	
	$dirs[]="certs";
	$dirs[]="debug";
	$dirs[]="discarded";
	$dirs[]="docs";
	$dirs[]="errors";
	$dirs[]="files";
	$dirs[]="images";
	$dirs[]="logs";
	$dirs[]="notes";
	$dirs[]="notspam";
	$dirs[]="okmail";
	$dirs[]="pb";
	$dirs[]="quarantine";
	$dirs[]="rc";
	$dirs[]="reports";
	$dirs[]="resendmail";
	$dirs[]="spam";
	$dirs[]="tmp";
	while (list ($nul, $Dirname) = each ($dirs) ){
		if(!is_dir("$root/$Dirname")){
			echo "Starting......: ASSP ($instance) Creating \"$root/$Dirname\" directory\n";
			@mkdir("$root/$Dirname",0666,true);
		}
	}
	
	$f=explode("\n",@file_get_contents("/usr/share/artica-postfix/bin/install/assp-files.txt"));
	while (list ($nul, $filename) = each ($f) ){
		if($filename==null){continue;}
		if(!is_file("$root/$filename")){
			echo "Starting......: ASSP ($instance) installing \"$filename\" file\n";
			@copy("/usr/share/assp/$filename","$root/$filename");
			}
	}
	

	
}

?>
