<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	$user=new usersMenus();
	if(!$user->AsPostfixAdministrator){header('location:users.index.php');exit;};
	
	if(isset($_GET["popup-files"])){
		
		
	}
	
	
?>