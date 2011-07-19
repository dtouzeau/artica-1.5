<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	$users=new usersMenus();
$tpl=new templates();
if(!PostFixVerifyRights()){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}	

if(isset($_GET["popup"])){smtpd_client_restrictions_popup();exit;}
if(isset($_GET["reject_unknown_client_hostname"])){smtpd_client_restrictions_save();exit;}


js();


function js_smtpd_client_restrictions_save(){
	$page=CurrentPageName();
	
	return "
	
var x_smtpd_client_restrictions_save_new= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	YahooWin2Hide();
	if(document.getElementById('main_config_postfix_security')){
		RefreshTab('main_config_postfix_security');
	}
}
	
function smtpd_client_restrictions_save(){
	var XHR = new XHRConnection();
		if(document.getElementById('reject_unknown_client_hostname').checked){XHR.appendData('reject_unknown_client_hostname',1);}
		else{XHR.appendData('reject_unknown_client_hostname',0);}
	
		if(document.getElementById('reject_unknown_reverse_client_hostname').checked){XHR.appendData('reject_unknown_reverse_client_hostname',1);}
		else{XHR.appendData('reject_unknown_reverse_client_hostname',0);}		
		
		if(document.getElementById('reject_unknown_sender_domain').checked){XHR.appendData('reject_unknown_sender_domain',1);}
		else{XHR.appendData('reject_unknown_sender_domain',0);}	
		
		
		if(document.getElementById('reject_invalid_hostname').checked){XHR.appendData('reject_invalid_hostname',1);}
		else{XHR.appendData('reject_invalid_hostname',0);}	
				
		if(document.getElementById('reject_non_fqdn_sender').checked){XHR.appendData('reject_non_fqdn_sender',1);}
		else{XHR.appendData('reject_non_fqdn_sender',0);}

		if(document.getElementById('EnablePostfixAntispamPack').checked){XHR.appendData('EnablePostfixAntispamPack',1);}
		else{XHR.appendData('EnablePostfixAntispamPack',0);}		
			
		if(document.getElementById('reject_forged_mails').checked){XHR.appendData('reject_forged_mails',1);}
		else{XHR.appendData('reject_forged_mails',0);}		

		if(document.getElementById('EnableGenericrDNSClients').checked){XHR.appendData('EnableGenericrDNSClients',1);}
		else{XHR.appendData('EnableGenericrDNSClients',0);}			
		
		if(document.getElementById('EnablePostfixInternalDomainsCheck').checked){XHR.appendData('EnablePostfixInternalDomainsCheck',1);}
		else{XHR.appendData('EnablePostfixInternalDomainsCheck',0);}				
		
		if(document.getElementById('RestrictToInternalDomains').checked){XHR.appendData('RestrictToInternalDomains',1);}
		else{XHR.appendData('RestrictToInternalDomains',0);}		
		
		if(document.getElementById('disable_vrfy_command').checked){XHR.appendData('disable_vrfy_command',1);}
		else{XHR.appendData('disable_vrfy_command',0);}			
		
		document.getElementById('smtpd_client_restrictions_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_smtpd_client_restrictions_save_new);	
	}

	";
	
	
}


function smtpd_client_restrictions_popup(){
	
	
	$sock=new sockets();
	$users=new usersMenus();
	$EnablePostfixAntispamPack_value=$sock->GET_INFO('EnablePostfixAntispamPack');	
	$EnableGenericrDNSClients=$sock->GET_INFO("EnableGenericrDNSClients");
	$reject_forged_mails=$sock->GET_INFO('reject_forged_mails');	
	
	
	$EnablePostfixInternalDomainsCheck=$sock->GET_INFO('EnablePostfixInternalDomainsCheck');
	$RestrictToInternalDomains=$sock->GET_INFO('RestrictToInternalDomains');
	
	$reject_unknown_client_hostname=$sock->GET_INFO('reject_unknown_client_hostname');
	$reject_unknown_reverse_client_hostname=$sock->GET_INFO('reject_unknown_reverse_client_hostname');
	$reject_unknown_sender_domain=$sock->GET_INFO('reject_unknown_sender_domain');
	$reject_invalid_hostname=$sock->GET_INFO('reject_invalid_hostname');
	$reject_non_fqdn_sender=$sock->GET_INFO('reject_non_fqdn_sender');
	$disable_vrfy_command=$sock->GET_INFO('disable_vrfy_command');
	
	if($EnablePostfixInternalDomainsCheck==null){$EnablePostfixInternalDomainsCheck=0;}
	
		
	
	
	
	
	
	
	
	$whitelists=Paragraphe("routing-domain-relay.png","{PostfixAutoBlockDenyAddWhiteList}","{PostfixAutoBlockDenyAddWhiteList_explain}","javascript:Loadjs('postfix.iptables.php?white-js=yes')");
	$rollover=CellRollOver();
	
	if(!$users->POSTFIX_PCRE_COMPLIANCE){
		$EnableGenericrDNSClients=0;
		$EnableGenericrDNSClientsDisabled=1;
		$EnableGenericrDNSClientsDisabledText="<br><i><span style='color:red;font-size:11px'>{EnableGenericrDNSClientsDisabledText}</span></i>";
	}
	
	
	
$html="


<div style='float:right;margin:5px'>$whitelists</div><div class=explain>{smtpd_client_restrictions_text}</div>
	<input type='hidden' id='EnableGenericrDNSClientsDisabled' value='$EnableGenericrDNSClientsDisabled'>
	<div id='smtpd_client_restrictions_div' style='height:300px;overflow:auto'>
	<table class=tableView>
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("disable_vrfy_command",1,$disable_vrfy_command)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{disable_vrfy_command}</td>
	<td valign='middle' width=1%>". help_icon("{disable_vrfy_command_text}")."</td>
	</tr>	
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("reject_unknown_client_hostname",1,$reject_unknown_client_hostname)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_unknown_client_hostname}</td>
	<td valign='middle' width=1%>". help_icon("{reject_unknown_client_hostname_text}")."</td>
	</tr>
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("reject_unknown_reverse_client_hostname",1,$reject_unknown_reverse_client_hostname)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_unknown_reverse_client_hostname}</td>
	<td valign='middle' width=1%>". help_icon("{reject_unknown_reverse_client_hostname_text}")."</td>
	</tr>
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("reject_unknown_sender_domain",1,$reject_unknown_sender_domain)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_unknown_sender_domain}</td>
	<td valign='middle' width=1%>". help_icon("{reject_unknown_sender_domain_text}")."</td>
	</tr>
	
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("reject_invalid_hostname",1,$reject_invalid_hostname)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_invalid_hostname}</td>
	<td valign='middle' width=1%>". help_icon("{reject_invalid_hostname_text}")."</td>
	</tr>
	
	<tr  class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("reject_non_fqdn_sender",1,$reject_non_fqdn_sender)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_non_fqdn_sender}</td>
	<td valign='middle' width=1%>". help_icon("{reject_non_fqdn_sender_text}")."</td>
	</tr>
	
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("reject_forged_mails",1,$reject_forged_mails)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_forged_mails}</td>
	<td valign='middle' width=1%>". help_icon("{reject_forged_mails_text}")."</td>
	</tr>	
	
	
	<tr  class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("EnablePostfixAntispamPack",1,$EnablePostfixAntispamPack_value)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{EnablePostfixAntispamPack}</td>
	<td valign='middle' width=1%>". help_icon("{EnablePostfixAntispamPack_text}")."</td>
	</tr>
	
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("EnableGenericrDNSClients",1,$EnableGenericrDNSClients,null,null)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{EnableGenericrDNSClients}$EnableGenericrDNSClientsDisabledText</td>
	<td valign='middle' width=1%>". help_icon("{EnableGenericrDNSClients_text}")."</td>
	</tr>

	<tr  class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("EnablePostfixInternalDomainsCheck",1,$EnablePostfixInternalDomainsCheck)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{EnablePostfixInternalDomainsCheck}</td>
	<td valign='middle' width=1%>". help_icon("{EnablePostfixInternalDomainsCheck_text}")."</td>
	</tr>	
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("RestrictToInternalDomains",1,$RestrictToInternalDomains,null,null)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{RestrictToInternalDomains}</td>
	<td valign='middle' width=1%>". help_icon("{RestrictToInternalDomains_text}")."</td>
	</tr>	
						
	</table>
	</div>

	<div style='width:100%;text-align:right'>
	". button("{edit}","smtpd_client_restrictions_save()")."
	
	</div>

	<script>
		function EnableGenericrDNSClientsDisabledCheck(){
			if(document.getElementById('EnableGenericrDNSClientsDisabled').value==1){
				document.getElementById('EnableGenericrDNSClients').disabled=true;
			}
		}
		
		EnableGenericrDNSClientsDisabledCheck();
	</script>
	
	";


//smtpd_client_connection_rate_limit = 100
//smtpd_client_recipient_rate_limit = 20
	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
	
	
}


function smtpd_client_restrictions_save(){
	$sock=new sockets();
	
	
	$sock->SET_INFO('reject_unknown_client_hostname',$_GET["reject_unknown_client_hostname"]);
	$sock->SET_INFO('reject_unknown_reverse_client_hostname',$_GET["reject_unknown_reverse_client_hostname"]);
	$sock->SET_INFO('reject_unknown_sender_domain',$_GET["reject_unknown_sender_domain"]);
	$sock->SET_INFO('reject_invalid_hostname',$_GET["reject_invalid_hostname"]);
	$sock->SET_INFO('reject_non_fqdn_sender',$_GET["reject_non_fqdn_sender"]);
	$sock->SET_INFO('EnablePostfixAntispamPack',$_GET["EnablePostfixAntispamPack"]);
	$sock->SET_INFO('reject_forged_mails',$_GET["reject_forged_mails"]);
	$sock->SET_INFO('EnableGenericrDNSClients',$_GET["EnableGenericrDNSClients"]);
	$sock->SET_INFO('EnablePostfixInternalDomainsCheck',$_GET["EnablePostfixInternalDomainsCheck"]);
	$sock->SET_INFO('RestrictToInternalDomains',$_GET["RestrictToInternalDomains"]);	
	$sock->SET_INFO('disable_vrfy_command',$_GET["disable_vrfy_command"]);
	
	
	
	$sock->getFrameWork("cmd.php?postfix-smtpd-restrictions=yes");
			
		
	
}




function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	$title=$tpl->_ENGINE_parse_body('{smtpd_client_restrictions_icon}',"postfix.index.php");
	$title2=$tpl->_ENGINE_parse_body('{PostfixAutoBlockManageFW}',"postfix.index.php");
	$title_compile=$tpl->_ENGINE_parse_body('{PostfixAutoBlockCompileFW}',"postfix.index.php");
	
	$prefix="smtpd_client_restriction";
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin4Open()){return false;}
		if ({$prefix}tant < 5 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
	      } else {
				{$prefix}tant = 0;
				{$prefix}CheckProgress();
				{$prefix}demarre();                                
	   }
	}	
	
	
	function {$prefix}StartPostfixPopup(){
		YahooWin2(650,'$page?popup=yes','$title');
	}
	
var x_smtpd_client_restrictions_save= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	{$prefix}StartPostfixPopup();
}		
	

	
	function PostfixAutoBlockStartCompile(){
		{$prefix}CheckProgress();
		{$prefix}demarre();       
	}
	
	var x_{$prefix}CheckProgress= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('PostfixAutoBlockCompileStatusCompile').innerHTML=tempvalue;
	}	
	
	function {$prefix}CheckProgress(){
			var XHR = new XHRConnection();
			XHR.appendData('compileCheck','yes');
			XHR.sendAndLoad('$page', 'GET',x_{$prefix}CheckProgress);
	
	}

	
	function PostfixIptablesSearchKey(e){
			if(checkEnter(e)){
				PostfixIptablesSearch();
			}
	}

		
".js_smtpd_client_restrictions_save()."
	
	
	{$prefix}StartPostfixPopup();
	";
	echo $html;
	}
	
function PostFixVerifyRights(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsPostfixAdministrator){return true;}
	if($usersmenus->AsMessagingOrg){return true;}
	}	
?>