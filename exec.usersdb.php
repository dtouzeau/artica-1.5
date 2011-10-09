<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["AS_ROOT"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.lvm.org.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.groups.inc');
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}


if($argv[1]=='--lastuidnum'){lastuidnum();die();}


function lastuidnum(){
	
	$users=new user();
	echo "LastUidNumber() -> ".$users->LastUidNumber()."\n";
	
}