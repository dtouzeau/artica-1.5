<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.ini.inc');	

if(isset($_GET["script"])){script();exit;}
if(isset($_GET["step"])){step();exit;}
if(isset($_GET["SaveSettings"])){SaveSettings();exit;}
if(isset($_GET["Cancel"])){Cancel();exit;}



function Cancel(){
	$sock=new sockets();
	$sock->SET_INFO("SmtpWizardFinish",1);		
	
}


function page_0(){
	$html="
	<p class=caption>{wizard_smtp_intro}</p>";
	echo BuildPage('{welcome_first_wizard}',$html);
	
}


function page_1(){
if($_COOKIE["company"]==null){
		$ldap=new clladp();
		$orgs=$ldap->hash_get_ou(false);
		$company=$orgs[0];
		
	}else{$company=$_COOKIE["company"];}
	
	$field_domain=Field_text('smtp_domain',$_COOKIE["smtp_domain"]);
	
	if($_COOKIE["smtp_domain"]==null){
		$sock=new sockets();
		if($sock->GET_INFO("MasterSMTPDomainName")==null){
			$ldap=new clladp();
			$domains=$ldap->hash_get_domains_ou($company);	
			if(is_array($domains)){$field_domain=Field_array_Hash($domains,'smtp_domain');}
		}else{
			$field_domain=Field_text('smtp_domain',$sock->GET_INFO("MasterSMTPDomainName"));
		}
	}
	
$html="
	<p class=caption>{company_domain}</p>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{company}:</td>
		<td>" . Field_text('company',$company)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{smtp_domain}:</td>
		<td>$field_domain</td>
	</tr>	
	</table>
	
	
	
	";
	echo BuildPage('{company_domain_t}',$html);
}

function page_2(){
	$users=new usersMenus();
	if($users->cyrus_imapd_installed){
		$text="<strong>{domain_routing_local}</strong>";
		
	}else{
	$smtp_relay=$_COOKIE["smtp_relay"];
	$text="
	<strong>{domain_routing_remote}</strong>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{smtp_relay}:</td>
		<td>" . Field_text('smtp_relay',$smtp_relay)."</td>
	</tr>
	</table>";
		
	}
	
	$html="
	<strong style='font-size:14px'>{$_COOKIE["smtp_domain"]}</strong>
	<p class=caption>{domain_routing_text}</p>
	<p>$text</p>";
	echo BuildPage('{domain_routing}',$html);
	
}

function page_4(){
	$r=true;
	
	if($_COOKIE["company"]==null){$r=false;}
	if($_COOKIE["smtp_domain"]==null){$r=false;}
	if($_COOKIE["username"]==null){$r=false;}
	if($_COOKIE["password"]==null){$r=false;}
	
	$users=new usersMenus();
	if(!$users->cyrus_imapd_installed){if($_COOKIE["smtp_relay"]==null){$r=false;}}
	
	if(!$r){$text="<p style='color:red;font-size:12px'>{wizard_finish_failed}</p>";}else{
		$text="<p style='color:black;font-weight:bold;font-size:12px'>{wizard_finish_text}</p>";
		$button="<tr>
		<td align='right' colspan=2><input type='button' OnClick=\"javascript:ApplyWizardSMTPSettings();\" value='{apply}&nbsp;&raquo;'></td>
		
	</tr>";		
	
		}
	$html="$text
	<div id='wizard_results'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{company}:</td>
		<td style='font-size:11px'>{$_COOKIE["company"]}</td>
	</tr>
	<tr>
		<td class=legend nowrap>{smtp_domain}:</td>
		<td style='font-size:11px'>{$_COOKIE["smtp_domain"]}</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{smtp_relay}:</td>
		<td style='font-size:11px'>{$_COOKIE["smtp_domain"]}&raquo;&raquo;&raquo;{$_COOKIE["smtp_relay"]}</td>
	</tr>		
	<tr>
		<td class=legend nowrap>{username}:</td>
		<td style='font-size:11px'>{$_COOKIE["username"]}@{$_COOKIE["smtp_domain"]}</td>
	</tr>
	<tr>
		<td class=legend nowrap>{password}:</td>
		<td style='font-size:11px'>{$_COOKIE["password"]}</td>
	</tr>				
	$button
	</div>
	</table>
";
	echo BuildPage('{wizard_finish}',$html);	
	
}

function page_3(){
	$users=new usersMenus();
	
	
	if($_COOKIE["username"]==null){
		$sock=new sockets();
		if($sock->GET_INFO("PostmasterAdress")==null){
		$username="postmaster";
		}else{
			$username=$sock->GET_INFO("PostmasterAdress");
		}
	
	}else{$username=$_COOKIE["username"];}
	
	$html="
	
	<p class=caption>{first_email_text}</p>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{username}:</td>
		<td>" . Field_text('username',$username,'width:100px')."<strong style='font-size:12px'>@{$_COOKIE["smtp_domain"]}</strong></td>
	</tr>
	<tr>
		<td class=legend nowrap>{password}:</td>
		<td>" . Field_text('password',$_COOKIE["password"])."</td>
	</tr>	
	</table>
	
	
	";
	
	
	echo BuildPage('{first_email}',$html);
	
}


function step(){
	
	switch ($_GET["step"]) {
		case 0:echo page_0();break;
		case 1:echo page_1();break;
		case 2:echo page_2();break;
		case 3:echo page_3();break;
		case 4:echo page_4();break;
	
		default: page_0();
			break;
	}
	
}

function BuildPage($title,$content){
	$step=$_GET["step"]+1;
	$sept=$_GET["step"]-1;
	$back="<input type='button' OnClick=\"javascript:start($sept);\" value='&laquo;&nbsp;{back}'>";
	$fw="<input type='button' OnClick=\"javascript:start($step);\" value='{next}&nbsp;&raquo;'>";
	if($sept<0){$back="<input type='button' OnClick=\"javascript:CancelWizard();\" value='&laquo;&nbsp;{cancel}'>";}
	if($step>4){$fw=null;}
	$html="
	
	<h1 style='width:103.5%'>$title</H1>
	
	<table style='width:1OO%'>
	<tr>
	<td valign='top'><img src='img/96-wizard.png' style='margin:4px;padding:1px;border:1px solid #CCCCCC'></td>
	<td valign='top'>
		<div id='wizardid'>
		$content	
		</div>
	</td>
	</tr>
	<tr>
		<td colspan=2 style='border-top:1px solid #CCCCCC' align='right'>
			<table style='width:100%'>
			<td>$back</td>
			<td align='right'>$fw</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
	
	
}

function script(){
	$page=CurrentPageName();
	$title="{welcome_first_wizard}";
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body($title);
	$html="
	
	function start(step){
		ParseContent();
		YahooWin2(450,'$page?step='+step,'$title','');
	}
	
	function ApplyWizardSMTPSettings(){
		LoadAjax('wizard_results','$page?SaveSettings=yes');
	
	}
	
var X_CancelWizard= function (obj) {
	var results=obj.responseText;
	YAHOO.example.container.dialog2.hide();	
	}
	
	function CancelWizard(){
			var XHR = new XHRConnection();
		    XHR.appendData('Cancel','yes');
		    XHR.sendAndLoad('$page', 'GET',X_CancelWizard);
	}
	
	function ParseContent(){
	if(!document.getElementById('wizardid')){return;}
	var z=document.getElementById('wizardid');
	var l=z.getElementsByTagName('input').length;
	for(var j=0;j<l;j++){
		field=z.getElementsByTagName('input')[j];
		Set_Cookie(field.id,field.value, '3600', '/', '', '');
      }
    
	
	var l=z.getElementsByTagName('select').length;
	for(var j=0;j<l;j++){
		field=z.getElementsByTagName('select')[j];
		Set_Cookie(field.id,field.value, '3600', '/', '', '');
      }
    }    
	setTimeout(\"start(0)\",2000);
	
	";
	
	echo $html;
	
}

function SaveSettings(){
	echo "<div><code>";
	$ldap=new clladp();
	$usersMenus=new usersMenus();
	$ldap->AddOrganization($_COOKIE["company"]);
	echo "</div></code>";
	echo "<div><code>";
	
	if(!$usersMenus->cyrus_imapd_installed){
		$ldap->AddRelayDomain($_COOKIE["company"],$_COOKIE["smtp_domain"],$_COOKIE["smtp_relay"],25);
	}else{
		$ldap->AddDomainEntity($_COOKIE["company"],$_COOKIE["smtp_domain"]);
	}
	echo "</div></code>";
	echo "<div><code>";
	$users=new user($_COOKIE["username"]);
	$users->mail="{$_COOKIE["username"]}@{$_COOKIE["smtp_domain"]}";
	$users->password=$_COOKIE["password"];
	$users->ou=$_COOKIE["company"];
	$users->add_user();
	$users->add_alias("root");
	$users->add_alias("mailflt3");
	$users->add_alias("root@localhost.localdomain");	
	$users->add_alias("root@$usersMenus->fqdn");		
	$users->add_alias("postmaster@$usersMenus->fqdn");
	$users->add_alias("postmaster@localhost.localdomain");	
	$users->add_alias("postmaster");		
	echo "</div></code>";
	
	$sock=new sockets();
	$sock->SET_INFO("PostmasterAdress",$users->mail);
	$sock->SET_INFO("MasterSMTPDomainName",$_COOKIE["smtp_domain"]);
	$sock->SET_INFO("SmtpWizardFinish",1);	
	
	
	if($usersMenus->AMAVIS_INSTALLED){
		echo "<div><code>";
		include_once("ressources/class.amavis.inc");
		$amavis=new amavis();
		$amavis->Save();
		$sock->SET_INFO("EnableAmavisDaemon","1");
		$amavis->SaveToServer();
		echo "</div></code>";
	}
	if($usersMenus->BIND9_INSTALLED){
	include_once("ressources/class.bind9.inc");
	include_once("ressources/class.system.network.inc");
		$net=new networking();
		if(is_array($net->arrayNameServers)){
			$dns=implode("\n",$net->arrayNameServers);
			$sock->SaveConfigFile($dns,"PostfixBind9DNSList");
			$sock->SET_INFO('PostfixEnabledInBind9',1);
		}
	}
	
	
	echo "<div><code>";
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
	echo "</div></code>";
	
	

	
}