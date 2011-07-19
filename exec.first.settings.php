<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.cyrus.inc');
include_once(dirname(__FILE__).'/ressources/class.monit.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");



if($argv[1]=="--monit"){monit();die();}

$sock=new sockets();
$ArticaFirstWizard=$sock->GET_INFO('ArticaFirstWizard');
if($ArticaFirstWizard==1){die();}




if($users->POSTFIX_INSTALLED){
	$ldap->AddDomainEntity($domainname,"$domainname");
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
}

if($users->cyrus_imapd_installed){
	$cyr=new cyrus();
	$cyr->CreateMailbox("postmaster");
}

$sock->SET_INFO("ArticaFirstWizard",1);
die();



function events($text){
		$logFile="/var/log/artica-postfix/artica-status.debug";
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$page=CurrentPageName();
		@fwrite($f, "$date [$pid] $page $text\n");
		@fclose($f);	
		}

function monit(){
	$monit=new monit();
	$monit->save();
}

?>