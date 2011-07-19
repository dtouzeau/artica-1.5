<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if($user->AMAVIS_INSTALLED==false){header('location:users.index.php');exit();}

	
$form="<iframe style='border:0px;width:100%;height:600px' src=\"https://{$_SERVER['SERVER_ADDR']}:{$_SERVER['SERVER_PORT']}/quarantine\"></iframe>";
$tpl=new template_users("{QUARANTINE}",$form,$_SESSION,0,0,0,$cfg);
echo $tpl->web_page;
?>