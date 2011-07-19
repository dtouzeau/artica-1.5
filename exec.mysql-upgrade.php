<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

$unix=new unix();



$mysql_upgrade=$unix->find_program("mysql_upgrade");
if(!is_file($mysql_upgrade)){
	events("Unable to stat mysql_upgrade");
}



function events($text){
	$q=new debuglogs();
	$text=dirname(__FILE__)." ".$text;
	$q->events($text,"/var/log/artica-postfix/mysql.upgrade.log");
	
}

?>