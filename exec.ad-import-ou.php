<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.activedirectory.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--output#",implode(" ",$argv))){$GLOBALS["output"]=true;}
	
	if($argv[1]=="--status"){status($argv[2]);die();}
	if($argv[1]=="--dist"){distri($argv[2]);die();}
	
	$ou=$argv[1];
	
	if($ou==null){
		echo "Please define the local organization..\n";
		die();
	}
	
	$ad=new wad($ou);
	$ad->Perform_import();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");
	
	
function distri($ou){
	$ad=new wad($ou);
	$ad->ImportDistriList();
	
}


function status($ou){
	$ad=new wad($ou);
	$ad->analyze();
	
}


?>