<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.kas-filter.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-smtp-domain"])){popup_smtp_domain();exit;}
	if(isset($_GET["popup-network"])){popup_network();exit;}
	if(isset($_GET["ORGANIZATION"])){$_SESSION["WIZARD"]["ORGANIZATION"]=$_GET["ORGANIZATION"];exit;}
	if(isset($_GET["WIZARD_CANCEL"])){WIZARD_CANCEL();exit;}
	
	if(isset($_GET["SMTP_DOM"])){
		$_SESSION["WIZARD"]["SMTP_DOM"]=$_GET["SMTP_DOM"];
		$_SESSION["WIZARD"]["MAILBOX_IP"]=$_GET["MAILBOX_IP"];
		exit;
	}
	
	if(isset($_GET["SMTP_NET"])){$_SESSION["WIZARD"]["SMTP_NET"]=$_GET["SMTP_NET"];exit;}
	if(isset($_GET["popup-finish"])){popup_finish();exit;}
	if(isset($_GET["COMPILE"])){COMPILE();exit;}
	
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{welcome_kaspersky_mail_appliance}");
	$WIZARD_FIRST_ROUTED_DOMAIN=$tpl->_ENGINE_parse_body("{WIZARD_FIRST_ROUTED_DOMAIN}");
	$WIZARD_COMPILE=$tpl->_ENGINE_parse_body("{WIZARD_COMPILE}");
	$html="
	
		function WizardKasAppLoad(){
			YahooWin(650,'$page?popup=yes','$title');
		
		}
		
		function WizardKasSMTPDomainShow(){
			YahooWin(650,'$page?popup-smtp-domain=yes','$WIZARD_FIRST_ROUTED_DOMAIN');
		}
		
		function WizardNetworkShow(){
			YahooWin(650,'$page?popup-network=yes','$WIZARD_POSTFIX_NETWORK');
		}
		
		function WizardFinishShow(){
			YahooWin(650,'$page?popup-finish=yes','$WIZARD_COMPILE');
		}
		
		function CancelWizard(){
			YahooWinHide();
			var XHR = new XHRConnection();
			XHR.appendData('WIZARD_CANCEL','yes');
			XHR.sendAndLoad('$page', 'GET');			
		}
		
	var x_WizardKasSMTPDomain= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardKasSMTPDomainShow();
	 }	

	var x_WizardNetwork= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardNetworkShow();
	 }	

	var x_WizardFinish= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardFinishShow();
	 }

function CloseTimeOut(){
		Loadjs('domains.manage.org.index.php?js=yes&ou='+document.getElementById('ou').value);
	 	YahooWinHide();
	 	
	}

	var x_WizardCompileNow= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){
			document.getElementById('wizard_compile_logs').innerHTML=tempvalue;
		}

		var XHR = new XHRConnection();
			XHR.appendData('WIZARD_CANCEL','yes');
			XHR.sendAndLoad('$page', 'GET');		
			setTimeout(\"CloseTimeOut()\",900);
		
	 }		 
		
		function WizardKasSMTPDomain(){
			var XHR = new XHRConnection();
			var org=document.getElementById('ORGANIZATION').value;
			if(org.length>1){
				XHR.appendData('ORGANIZATION',document.getElementById('ORGANIZATION').value);
				XHR.sendAndLoad('$page', 'GET',x_WizardKasSMTPDomain);
			}
		}
		
		function WizardNetwork(){
			var XHR = new XHRConnection();
			
			var SMTP_DOM=document.getElementById('SMTP_DOM').value;
			var MAILBOX_IP=document.getElementById('MAILBOX_IP').value;
			if(SMTP_DOM.length==0){return;}
			if(MAILBOX_IP.length==0){return;}
			
			
			XHR.appendData('SMTP_DOM',document.getElementById('SMTP_DOM').value);
			XHR.appendData('MAILBOX_IP',document.getElementById('MAILBOX_IP').value);
			XHR.sendAndLoad('$page', 'GET',x_WizardNetwork);		
		}
		
		function WizardFinish(){
			var SMTP_NET=document.getElementById('SMTP_NET').value;
			if(SMTP_NET.length==0){return;}
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_NET',document.getElementById('SMTP_NET').value);
			XHR.sendAndLoad('$page', 'GET',x_WizardFinish);				
		
		}
		
		function WizardCompileNow(){
			var XHR = new XHRConnection();
			XHR.appendData('COMPILE','yes');
			document.getElementById('wizard_compile_logs').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_WizardCompileNow);	
		}
	
	
	WizardKasAppLoad();";
	
	echo $html;
}

function popup(){
	$html="
	<img src='img/kaspersky-wizard_bg.png'>
	<H1>{welcome_kaspersky_mail_appliance}</H1>
	<p style='font-size:14px'>{KAVAPPMAIL_INTRO}</p>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_FIRST_ORG}</H3>
	<p style='font-size:14px'>{WIZARD_FIRST_ORG_EXPLAIN}</p>
	". Field_text("ORGANIZATION",$_SESSION["WIZARD"]["ORGANIZATION"],"font-size:14px;padding:5px")."
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{cancel}","CancelWizard()")."</td>
			<td width=50% align='right'>". button("{next}","WizardKasSMTPDomain()")."</td>
		</tr>
	</table>
			
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_smtp_domain(){
$html="
	<img src='img/kaspersky-wizard_bg.png'>
	<H1>{welcome_kaspersky_mail_appliance}</H1>
	<div style='color:#005447;font-size:13px'>{WIZARD_FIRST_ORG}: {$_SESSION["WIZARD"]["ORGANIZATION"]}</div>
	<hr>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_FIRST_ROUTED_DOMAIN}</H3>
	<p style='font-size:14px'>{WIZARD_FIRST_ROUTED_DOMAIN_EXPLAIN}</p>
	
<table style='width:100%'>
		<tr>
			<td align='right'><strong style='font-size:13px'>{smtp_domain}:</td>
			<td>". Field_text("SMTP_DOM",$_SESSION["WIZARD"]["SMTP_DOM"],"font-size:14px;padding:5px;")."</td>
		</tr>
		<tr>
			<td align='right'><strong style='font-size:13px'>{mailbox_server_address}:</td>
			<td>". Field_text("MAILBOX_IP",$_SESSION["WIZARD"]["MAILBOX_IP"],"font-size:14px;padding:5px;")."</td>
		</tr>		
	</table>	
	
	
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardKasAppLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardNetwork()")."</td>
		</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function popup_network(){
$html="
	<img src='img/kaspersky-wizard_bg.png'>
	<H1>{welcome_kaspersky_mail_appliance}</H1>
	<div style='color:#005447;font-size:13px'>{WIZARD_FIRST_ORG}: {$_SESSION["WIZARD"]["ORGANIZATION"]}</div>
	<div style='color:#005447;font-size:13px'>{WIZARD_FIRST_ROUTED_DOMAIN}: {$_SESSION["WIZARD"]["SMTP_DOM"]}&nbsp&raquo;&raquo;&nbsp;{$_SESSION["WIZARD"]["MAILBOX_IP"]}</div>
	<hr>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_INTERNAL_SMTP_NETWORK}</H3>
	<p style='font-size:14px'>{WIZARD_INTERNAL_SMTP_NETWORK_EXPLAIN}</p>
	
<table style='width:100%'>
		<tr>
			<td align='right'><strong style='font-size:13px'>{WIZARD_INTERNAL_SMTP_NETWORK}:</td>
			<td>". Field_text("SMTP_NET",$_SESSION["WIZARD"]["SMTP_NET"],"font-size:14px;padding:5px;")."</td>
		</tr>
	</table>	
	
	
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardKasSMTPDomainShow()")."</td>
			<td width=50% align='right'>". button("{finish}","WizardFinish()")."</td>
		</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
	
}

function popup_finish(){
		$ou=$_SESSION["WIZARD"]["ORGANIZATION"];
		$ou=str_replace(" ","_",$ou);	
$html="
	<img src='img/kaspersky-wizard_bg.png'>
	<H1>{welcome_kaspersky_mail_appliance}</H1>
	<input type='hidden' id='ou' value='{$ou}'>
	<div style='color:#005447;font-size:13px'>{WIZARD_FIRST_ORG}: {$ou}</div>
	<div style='color:#005447;font-size:13px'>{WIZARD_FIRST_ROUTED_DOMAIN}: {$_SESSION["WIZARD"]["SMTP_DOM"]}&nbsp&raquo;&raquo;&nbsp;{$_SESSION["WIZARD"]["MAILBOX_IP"]}</div>
	<div style='color:#005447;font-size:13px'>{WIZARD_INTERNAL_SMTP_NETWORK}: {$_SESSION["WIZARD"]["SMTP_NET"]}</div>
	<hr>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_COMPILE}</H3>
	<p style='font-size:14px'>{WIZARD_COMPILE_EXPLAIN}</p>
	

	<div id='wizard_compile_logs'>
<table style='width:100%'>
		<tr>
			<td align='center'>". button("{WIZARD_COMPILE}","WizardCompileNow()")."</td>
		</tr>
	</table>		
	
	
	</div>
	
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardNetworkShow()")."</td>
			<td width=50% align='right'></td>
		</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
		
	
	
}

function COMPILE(){
		$sock=new sockets();
		$sock->SET_INFO("KasxFilterEnabled",1);
		$sock->SET_INFO("kavmilterEnable",1);
		$domain=trim(strtolower($_SESSION["WIZARD"]["ORGANIZATION"]));
		$ou=$_SESSION["WIZARD"]["ORGANIZATION"];
		$ou=str_replace(" ","_",$ou);
		
		
		$ldap=new clladp();
		$ldap->AddOrganization($_SESSION["WIZARD"]["ORGANIZATION"]);
		
		$hashdoms=$ldap->hash_get_all_domains();
	
		if($hashdoms[$domain]==null){
			$ldap->AddRelayDomain($ou,$domain,$_SESSION["WIZARD"]["MAILBOX_IP"],25);
		}		
		
		$kas=new kas_mysql($ou);
		$kas->SET_VALUE("OPT_FILTRATION_ON",1);
		
		
		
		$main=new main_cf();
		$main->add_my_networks($_SESSION["WIZARD"]["SMTP_NET"]);
		$main->save_conf_to_server();
		
		$sock->getFrameWork("cmd.php?kas-reconfigure=yes");
		$sock->getFrameWork("cmd.php?kavmilter-configure");
	
		$tpl=new templates();
		
		$html="
		
		<H3>{success}</H3><center>". button("{close}","CloseTimeOut()")."</center>";
		
		echo $tpl->_ENGINE_parse_body("{success}");
	
}

function WIZARD_CANCEL(){
	$sock=new sockets();
	$sock->SET_INFO("KasperskyMailApplianceWizardFinish",1);
	
}
	
//kaspersky-wizard_bg.png
?>