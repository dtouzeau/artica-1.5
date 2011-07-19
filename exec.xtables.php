<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}

if($argv[1]="--account-module"){load_XT_ACCOUNT();die();}



function KERNERL_VER(){
	$unix=new unix();
	$uname=$unix->find_program("uname");
	exec("$uname -r 2>&1",$results);
	$GLOBALS["KERNEL_V"]=trim(@implode(" ", $results));	
}


function load_XT_ACCOUNT(){
	$unix=new unix();
	$modprobe=$unix->find_program("modprobe");
	KERNERL_VER();
	echo "Starting......: Artica Kernel v{$GLOBALS["KERNEL_V"]}\n";
	if(is_file("/lib/modules/{$GLOBALS["KERNEL_V"]}/extra/xtables-addons/ACCOUNT/xt_ACCOUNT.ko")){
		echo "Starting......: Artica loading XT_ACCOUNT module...\n";
		shell_exec("$modprobe xt_ACCOUNT >/dev/null 2>&1");
	}else{
		echo "Starting......: Artica XT_ACCOUNT no such module...\n";
	}	
}