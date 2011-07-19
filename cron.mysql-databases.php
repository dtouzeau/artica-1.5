<?php
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	die();
}
$sql=new mysql();
$sql->BuildTables();

if($sql->ok){die();}

if(preg_match("#Access denied for user.+?using password:#",$sql->mysql_error)){
	if(($mysql->mysql_server=="127.0.0.1") OR ($mysql->mysql_server=="localhost")){
		
		$unix=new unix();
		
		exec("/usr/share/artica-postfix/bin/artica-install --change-mysqlroot --inline root \"secret\"",$chroot);
	}
}	

?>