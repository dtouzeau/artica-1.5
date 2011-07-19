<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');

if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}

$ldap=new clladp();

?>