<?php
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
	include_once(dirname(__FILE__).'/ressources/class.amavis.inc');
	include_once(dirname(__FILE__).'/ressources/class.squid.inc');
	include_once(dirname(__FILE__).'/ressources/class.samba.inc');			
	
	
system('/usr/share/artica-postfix/bin/process1 --force '.md5(date('Y-m-d H:i:s')));	
$users=new usersMenus();
if($users->POSTFIX_INSTALLED){
	
	if($users->AMAVIS_INSTALLED){
		$amavis=new amavis();
		$amavis->Save();
		$amavis->SaveToServer();
		
	}
	
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
	system('/etc/init.d/artica-postfix restart postfix');
	
	if($users->cyrus_imapd_installed){
		system('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
		system('/etc/init.d/artica-postfix restart imap &');
	}
	
}

if($users->SQUID_INSTALLED){
	$squid=new squidbee();
	$squid->SaveToLdap();
	$squid->SaveToServer();
	system('/etc/init.d/artica-postfix restart squid &');
}

if($users->SAMBA_INSTALLED){
	$smb=new samba();
	$smb->SaveToLdap();
	system('/usr/share/artica-postfix/bin/artica-install --samba-reconfigure');
	system('/etc/init.d/artica-postfix restart samba &');
}
	
	
?>