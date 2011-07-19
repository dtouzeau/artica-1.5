<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');


writelogs("user executed : ".posix_getuid(),__FUNCTION__,__FILE__,__LINE__);
if(posix_getuid()<>0){
	
	write_syslog("requested has ". posix_getuid(). " uid \"{$argv[1]}\" but Cannot be used in web server mode",__FILE__);
	die();
	
}

$log=false;
$username=$argv[1];
$verbose=$argv[2];
if($verbose=='--verbose'){$_GET["log"]=true;}



if(trim($username)==null){die();}
$GLOBALS["ADDLOG"]="/usr/share/artica-postfix/ressources/logs/web/samba-add-computer.log";

writelogs("Adding new computer $username",__FUNCTION__,__FILE__,__LINE__);

$computer=new computers($username);
if($computer->Add()){
	write_syslog("Success adding new computer $username",__FILE__);
}else{
	write_syslog("Failed adding new computer $username",__FILE__);
}



?>