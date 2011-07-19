<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');	
include_once('ressources/class.sockets.inc');	
include_once('ressources/kav4mailservers.inc');
include_once('ressources/class.kas-filter.inc');
include_once('ressources/class.main_cf.inc');
include_once("ressources/class.main_cf_filtering.inc");
include_once("ressources/class.html.pages.inc");
include_once("ressources/class.artica.inc");
include_once("ressources/class.cyrus.inc");
include_once("ressources/class.system.network.inc");



if(isset($_GET["ApplyConfig"])){ApplyConfig();exit;}
if(isset($_GET["operations"])){operations();exit;}
if(isset($_GET["TreeSystemInstall"])){TreeSystemInstall();exit;}
if(isset($_GET["TreeSystemInstallCheck"])){TreeSystemInstallCheck();exit;}
if(isset($_GET["TreeSystemInstallContinue"])){TreeSystemInstallContinue();exit;}
if(isset($_GET["TreeSystemPerformInstall"])){TreeSystemPerformInstall();exit;}
if(isset($_GET["Step"])){echo Step();exit;}
if(isset($_GET["ApplyNumber"])){ApplyNumber_switch();exit;}



function Step(){
	$_GET["NO_ECHO"]=true;
	switch ($_GET["Step"]) {
		case 'kavmail':ApplyConfigKavMail();break;
		case 'postfix':ApplyConfigPostfix();break;
		case 'kas':ApplyConfigKas();break;
		case 'Start':StartApplyConfig();break;
		case "mbx":ApplyConfigMbx();break;
		case "tasks":ApplyConfigTasks();break;
		case "dns":ApplyConfigDns();break;
		case "tcp":ApplyConfigTCP();break;
		case "fetch":ApplyConfigFetchMail();break;	
		case "fetchmail":ApplyConfigFetchMail();break;	
		case "mailman":ApplyConfigMailman();break;
		case "kav4proxy":ApplyConfigKav4proxy();break;
		case "squid":ApplyConfigSquid();break;
		case "dansguardian":ApplyConfigDansGuardian();break;
		case "pure-ftpd":ApplyConfigPureftpd();break;
		case "sqlgrey":ApplyConfigSqlgrey();break;
			
		case "x":StartApplyConfig();break;
		default:
			break;
	}
	
}


function ApplyNumber_switch(){
	
	switch ($_GET["ApplyNumber"]) {
		case 10:ApplyConfigPostfix();break;
		case 20:ApplyConfigKas();break;
		case 30:ApplyConfigKavMail();break;
		case 35:ApplyConfigMimeDefang();break;
		case 40:ApplyConfigMbx();break;
		case 45:ApplyConfigPureftpd();break;
		case 50:ApplyConfigFetchMail();break;
		case 55:ApplyConfigDns();;break;
		case 60:ApplyConfigTasks();;break;
		case 65:ApplyConfigTCP();;break;
		case 70:ApplyConfigSquid();;break;
		case 75:ApplyConfigKav4proxy();;break;
		case 80:ApplyConfigDansGuardian();;break;
		case 85:ApplyConfigMailman();;break;
		case 100:ApplyConfigHTTP();;break;
		default:
			break;
	}
	
}


function ApplyConfigHTTP(){
	include_once("ressources/class.httpd.inc");
	$http=new httpd();
	$http->SaveToServer();
	}

function StartApplyConfig(){
	
	$postfix=texttooltip('{postfix_main_settings}','{click_to_launch}',"ApplySingle('postfix','postfix')");
	$kavmail=texttooltip('{aveserver_main_settings}','{click_to_launch}',"ApplySingle('kavmail','kavmail')");
	$kas=texttooltip('{kas_main_settings}','{click_to_launch}',"ApplySingle('kas','kas')");
	$mbx=texttooltip('{mailbox_main_settings}','{click_to_launch}',"ApplySingle('mbx','mbx')");
	$tasks=texttooltip('{tasks_main_settings}','{click_to_launch}',"ApplySingle('tasks','tasks')");
	$dns=texttooltip('{dns_main_settings}','{click_to_launch}',"ApplySingle('dns','dns')");
	$tcp=texttooltip('{tcp_main_settings}','{click_to_launch}',"ApplySingle('tcp','tcp')");
	$fetch=texttooltip('{fetchmail_main_settings}','{click_to_launch}',"ApplySingle('fetch','fetch')");
	$mailman=texttooltip('{mailman_main_settings}','{click_to_launch}',"ApplySingle('mailman','mailman')");
	$kav4proy=texttooltip('{kav4proy_main_settings}','{click_to_launch}',"ApplySingle('kav4proxy','kav4proxy')");
	$squid=texttooltip('{squid_main_settings}','{click_to_launch}',"ApplySingle('squid','squid')");
	$dansguardian=texttooltip('{dansguardian_main_settings}','{click_to_launch}',"ApplySingle('dansguardian','dansguardian')");
	$pureftpd=texttooltip('{pureftpd_main_settings}','{click_to_launch}',"ApplySingle('pure-ftpd','pure-ftpd')");	
	$sqlgrey=texttooltip('{sqlgrey_main_settings}','{click_to_launch}',"ApplySingle('sqlgrey','sqlgrey')");	
	
	$html="
	
	<input type='hidden' id='httprestart' value='{httprestart}'>
	<div style='margin:15px'><H4>{apply config}</H4>
	
	<div align=right' style='text-align:right;float:right' id='button'><input type='button' OnClick=\"javascript:buttonApply();\" value='Go&nbsp;&raquo;&raquo;'></div>
	<center id='applystart'>
	<br>
	<div id='progressbar'></div>
	<br>
	<div id='textbar'></div>
	</center>
	<p>{apply config text}</p>
	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}


function ApplyConfigMimeDefang(){
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$tpl=new templates();
	if(!$users->POSTFIX_INSTALLED){
		echo $tpl->_ENGINE_parse_body(NotInstalled('postfix'));
		exit;
	}
	
	
	if(!$users->MIMEDEFANG_INSTALLED){
		echo $tpl->_ENGINE_parse_body( NotInstalled('mimedefang'));
		exit;
	}
	
	if($users->MimeDefangEnabled<>1){
		echo $tpl->_ENGINE_parse_body( NotEnabled("mimedefang","MimeDefangEnabled=$users->MimeDefangEnabled"));
		exit;
	}
	
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.mysql.inc');
	$mime=new mimedefang();
	$mime->SaveToLdap();
	echo $tpl->_ENGINE_parse_body(Success("mimedefang"));
	
	
}


function ApplyConfigPostfix(){
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$tpl=new templates();
	if($users->AsPostfixAdministrator==true){
		
		//Apply amavis settings if installed
		if($users->AMAVIS_INSTALLED==1){
			if($users->EnableAmavisDaemon==1){
				include_once("ressources/class.amavis.inc");
				$amavis=new amavis();
				$amavis->Save();
				$amavis->SaveToServer();
				}
			}
		//------------------------------------
		
		
		$main=new main_cf();
		$main->save_conf();
		if(!$main->save_conf_to_server()){
			$result= InfosError('postfix_main_settings','{error}');
			echo $tpl->_ENGINE_parse_body($result);
			return null;
		}
		$filters=new main_header_check();
		$filters->SaveToDaemon();	


		
	if($users->KAV_MILTER_INSTALLED){
		if($users->KAVMILTER_ENABLED==1){
			include_once("ressources/class.kavmilterd.inc");
			$kavmilterd=new kavmilterd();
			$kavmilterd->ReBuildAllRules();
			$kavmilterd->SaveToLdap(0,1);
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body(Success("aveserver_main_settings"));		
		}
	}
		
		if($users->AMAVIS_INSTALLED){
			if($users->EnableAmavisDaemon==1){
				include_once("ressources/class.amavis.inc");
				$amavis=new amavis();
				$amavis->Save();
			}
		}
		
		
		if($users->kas_installed){
			if($users->KasxFilterEnabled==1){
				include_once("ressources/class.kas-filter.inc");
				$kas=new kas_single();
				$kas->Save();
			}
		}
			
		$result= Success("postfix_main_settings");
	
	}else{
		$result= NotAllowed("postfix_main_settings");
	}
echo $tpl->_ENGINE_parse_body($result);
}

function ApplyConfigKavMail(){
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if($users->KAV_MILTER_INSTALLED){
		if($users->KAVMILTER_ENABLED==1){
		include_once("ressources/class.kavmilterd.inc");
		$kavmilterd=new kavmilterd();
		$kavmilterd->ReBuildAllRules();
		$kavmilterd->SaveToLdap();
		$result= Success("aveserver_main_settings");
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($result);		
		}
	}
}

function Success($explain){return  "<text><table style='width:100%'><tr><td width=1% valign='top'><img src=img/icon_ok.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} {success}</td></tr></table></text>";}
function NotInstalled($explain){return  "<text><table style='width:100%'><tr><td width=1% valign='top'>	<img src=img/icon_mini_warning.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} {not_installed}</td></tr></table></text>";}
function NotAllowed($explain){return  "<text><table style='width:100%'><tr><td width=1% valign='top'><img src=img/icon_mini_warning.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} {not allowed}</td></tr></table></text>";}
function NoDatas($explain){return  "<text><table style='width:100%'><tr><td width=1% valign='top'><img src=img/icon_mini_warning.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} {error_no_datas}</td></tr></table></text>";}
function Infos($explain,$infos){return  "<text><table style='width:100%'><tr><td width=1% valign='top'><img src=img/icon_info.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} {".$infos."}</td></tr></table></text>";}
function InfosError($explain,$infos){return  "<text><table style='width:100%'><tr><td width=1% valign='top'><img src=img/icon_mini_warning.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} ".$infos."</td></tr></table></text>";}
function NotEnabled($explain,$infos){return  "<text><table style='width:100%'><tr><td width=1% valign='top'><img src=img/icon_mini_warning.gif align=left style='margin-right:5px'></td><td valign='top'>{".$explain."} {not_enabled} $infos</td></tr></table></text>";}


function ApplyConfigMbx(){
	

	$artica=new artica_general();
	$tpl=new templates();	
	if($artica->RelayType=="single"){echo $tpl->_parse_body(Infos('mailbox_main_settings','feature_disabled'));exit;}
	
	$ldap=new clladp();
	$hash=$ldap->Hash_all_mailboxesActives();

	if(!is_array($hash)){echo $tpl->_parse_body(Infos('mailbox_main_settings','no_mailboxes'));exit;}
	
	$cyrus=new cyrus();
	$cyrconf=new cyrus_conf();
	$cyrconf->SaveToLdap();
	
	
	while (list ($uid, $password) = each ($hash)){
		writelogs("Creating Mailbox $uid",__FUNCTION__,__FILE__);	
		if (!$cyrus->CreateMailbox($uid,1)){
				writelogs("Creating Mailbox $uid failed aborting",__FUNCTION__,__FILE__);	
				echo $tpl->_parse_body(InfosError('mailbox_main_settings',"{error_creating_mailbox} <strong>$uid</strong>"));
				
				exit;
			}
	}

	echo $tpl->_ENGINE_parse_body(Success('mailbox_main_settings'));
	
}

function ApplyConfigKas(){
	$prod="kas_main_settings";
	$tpl=new templates();	
	$user=new usersMenus()	;
	if($user->AllowChangeKas==false){
		echo $tpl->_parse_body(NotAllowed($prod));
		return null;
		}
		
	if($user->kas_installed==false){
		echo $tpl->_parse_body(NotInstalled($prod));
		return null;
	}
	
$ldap=new clladp();
	$kas=new kas_single();
	$kas->SaveToserver();
	$sock=new sockets();
	$sock->getfile('kasrules:' . dirname(__FILE__) . '/ressources/conf/kasDatas');	
	
	
	$kas=new kas_filter();
	$kas->SaveFile();
	
	$kas=new kas_dns();
	$kas->SaveToServer();
	
	echo $tpl->_parse_body(Success($prod));
	
}

function ApplyConfigTasks(){
	$prod="tasks_main_settings";
	$tpl=new templates();
	include_once( dirname(__FILE__) . '/ressources/class.cron.inc');
	$cron=new cron();
	if($cron->ApplyCronToServer()){
		echo $tpl->_ENGINE_parse_body(Success($prod));
	}else{echo $tpl->_ENGINE_parse_body(InfosError($prod,'{error}'));}
	}

function ApplyConfigDns(){
	$prod="dns_main_settings";
	include_once("ressources/class.dnsmasq.inc");
	include_once("ressources/class.system.network.inc");
	$users=new usersMenus();
	$dnsfile="/etc/resolv.conf";
	
	if($users->dnsmasq_installed==true){
		$dnsmasq=new dnsmasq();
		writelogs("DNSMASQ:: no resolv= " . $dnsmasq->main_array["no-resolv"],__FUNCTION__,__FILE__);
		//verify if dnsmasq is enabled
		if($dnsmasq->main_array["no-resolv"]=='justkey'){$dnsfile="/etc/resolv.conf";
		}else{$dnsfile=$dnsmasq->main_array["resolv-file"];}
		if($dnsfile==null){$dnsfile="/etc/resolv.conf";}
	}
	
	writelogs("RESOLV:: = $dnsfile",__FUNCTION__,__FILE__);
	$net=new networking();
	$net->SaveResolvconf($dnsfile);
	if($users->dnsmasq_installed==true){$dnsmasq->SaveConfToServer();}
	
	//inadyn
	$sock=new sockets();
	$sock->getfile("perform_inadyn");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(Success($prod));
	
	
}

function ApplyConfigTCP(){
	
	$users=new usersMenus();
	if($users->BIND9_INSTALLED){
		include_once(dirname(__FILE__).'/ressources/class.bind9.inc');
		$bind9=new bind9();
		$bind9->Compile();
	}
	
	
	
	
}

function ApplyConfigDansGuardian($noecho=0){
		$user=new usersMenus();

	if(!isset($_GET["hostname"])){$hostname=$user->hostname;}else{$hostname=$_GET["hostname"];}
	$prod="dansguardian_main_settings";
	$tpl=new templates();

	if($user->DANSGUARDIAN_INSTALLED==false){
		echo $tpl->_ENGINE_parse_body(NotInstalled($prod));
		exit;
	}
	include_once('ressources/class.dansguardian.inc');
	$dans=new dansguardian($hostname);
	$dans->SaveSettings();
	if(is_array($dans->Master_rules_index)){
		while (list ($num, $line) = each ($dans->Master_rules_index)){
			$rules=new dansguardian_rules($hostname,$num);
			$rules->SaveConfigFiles();
			}
		}

		
		
	$sock=new sockets();
	$sock->getfile('dansguardian_saveconf:' .$hostname);
	if($noecho==0){echo $tpl->_ENGINE_parse_body(Success($prod));}
	
}

function ApplyConfigSqlgrey(){
    $sock=new sockets();
    $tpl=new templates();
    $user=new usersMenus();
    if(!isset($_GET["hostname"])){$hostname=$user->hostname;}else{$hostname=$_GET["hostname"];}
    $prod="sqlgrey_main_settings";
    if($user->POSTFIX_INSTALLED){
    	$sock->getfile('sqlgrey_saveconf:' .$hostname);	
		echo $tpl->_ENGINE_parse_body(Success($prod));
	}else{
		echo $tpl->_ENGINE_parse_body(NotInstalled($prod));
		return "";
	}
}


function ApplyConfigFetchMail(){


	$prod="fetchmail_main_settings";
	$tpl=new templates();
	$user=new usersMenus();
	if($user->fetchmail_installed==false){
		echo $tpl->_ENGINE_parse_body(NotInstalled($prod));
		exit;
	}
	include_once('ressources/class.fetchmail.inc');
	$fetch=new fetchmail();
	$fetch->Save();
	$sock=new sockets();
	$sock->getfile('Savefetchmailrc');
	echo $tpl->_ENGINE_parse_body(Success($prod));	
}

function ApplyConfigKav4proxy(){
	$tpl=new templates();
	$user=new usersMenus();
	$prod="kav4proy_main_settings";
	if($user->SQUID_INSTALLED==false){
		echo $tpl->_ENGINE_parse_body(NotInstalled('squid'));
		exit;
	}

	if($user->KAV4PROXY_INSTALLED==false){
		echo $tpl->_ENGINE_parse_body(NotInstalled($prod));
		exit;		
		
	}
	
	$sock=new sockets();
	$sock->getfile('kav4proxy_saveconf');
	echo $tpl->_ENGINE_parse_body(Success($prod));	
		
}

function ApplyConfigMailman(){
	include_once('ressources/class.mailman.inc');
	$prod="mailman_main_settings";
	$tpl=new templates();
	$user=new usersMenus();
	if($user->MAILMAN_INSTALLED==false){
		echo $tpl->_ENGINE_parse_body(NotInstalled($prod));
		exit;
	}	
	
	$ldap=new clladp();
	$sock=new sockets();
	$filter="(&(Objectclass=ArticaMailManClass)(cn=*))";
	$sr = @ldap_search($ldap->ldap_connection,"cn=mailman,cn=artica,$ldap->suffix",$filter,array("mailmanouowner","cn"));
	if($sr){
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($hash["count"]>0){	
				for($i=0;$i<$hash["count"];$i++){
					$name=$hash[$i]["cn"][0];	
					$datas=$sock->getfile('MAILMAN_SINGLE:' .$name );
				}
			}
	}
	
	echo $tpl->_ENGINE_parse_body(Success($prod) . " <strong>(" . $hash["count"] . " policies)</strong>");	
}

function ApplyConfigSquid(){
	$user=new usersMenus();
	if(!isset($_GET["hostname"])){$hostname=$user->hostname;}else{$hostname=$_GET["hostname"];}	
	$tpl=new templates();
	
	
	$prod="squid_main_settings";
	$users=new usersMenus();
	
	if($user->SQUID_INSTALLED==false){
		echo $tpl->_ENGINE_parse_body(NotInstalled('squid'));
		exit;
	}
	
	include_once("ressources/class.squid.inc");
	$squid=new squidbee();
	$squid->SaveToLdap();
	$squid->SaveToServer();
	echo $tpl->_ENGINE_parse_body(Success($prod));	
	
}
function ApplyConfigPureftpd(){
	writelogs('Start pure-ftpd configuration....',__FUNCTION__,__FILE__);
	$prod="pureftpd_main_settings";
	$user=new usersMenus();
	if($user->PUREFTP_INSTALLED==true){
		if(!isset($_GET["hostname"])){$hostname=$user->hostname;}else{$hostname=$_GET["hostname"];}	
		$sock=new sockets();
		writelogs('Start pure-ftpd ->pureftpd_saveconf....',__FUNCTION__,__FILE__);
		$sock->getfile("pureftpd_saveconf:$hostname");
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body(Success($prod));			
	}

	}

?>