<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.system.nics.inc');	
	
	
	$page=CurrentPageName();
	$q=new mysql();
	$q->check_vps_tables();		
?>