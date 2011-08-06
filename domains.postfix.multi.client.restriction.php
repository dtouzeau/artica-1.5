<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
if(isset($_GET["popup"])){smtpd_client_restrictions_popup();exit;}
if(isset($_GET["reject_unknown_client_hostname"])){smtpd_client_restrictions_save();exit;}


js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{smtpd_client_restrictions_icon}',"postfix.index.php");
	$title2=$tpl->_ENGINE_parse_body('{PostfixAutoBlockManageFW}',"postfix.index.php");
	$title_compile=$tpl->_ENGINE_parse_body('{PostfixAutoBlockCompileFW}',"postfix.index.php");
	$ou=$_GET["ou"];
	$hostname=$_GET["hostname"];
	
	$prefix="smtpd_client_restriction_multi";
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
		YahooWin2(650,'$page?popup=yes&ou=$ou&hostname=$hostname','$title');
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
	
	var x_smtpd_client_restrictions_multi_save= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		{$prefix}StartPostfixPopup();
	}		
	
	
	
	function smtpd_client_restrictions_multi_save(){
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

		if(document.getElementById('RestrictToInternalDomains').checked){XHR.appendData('RestrictToInternalDomains',1);}
		else{XHR.appendData('RestrictToInternalDomains',0);}			
		
		if(document.getElementById('EnablePostfixInternalDomainsCheck').checked){XHR.appendData('EnablePostfixInternalDomainsCheck',1);}
		else{XHR.appendData('EnablePostfixInternalDomainsCheck',0);}	

		if(document.getElementById('disable_vrfy_command').checked){XHR.appendData('disable_vrfy_command',1);}
		else{XHR.appendData('disable_vrfy_command',0);}			
		
		
		 
		
		XHR.appendData('ou','$ou');
		XHR.appendData('hostname','$hostname');

		document.getElementById('smtpd_client_restrictions_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_smtpd_client_restrictions_multi_save);	
	}
	
	
	{$prefix}StartPostfixPopup();
	";
	echo $html;
	}




function smtpd_client_restrictions_popup(){
	
	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	
	$datas=$main->GET_BIGDATA("hash_smtp_restrictions");
	$restrictions=unserialize(base64_decode($datas));
	
	
	
	$EnablePostfixAntispamPack_value=$restrictions["EnablePostfixAntispamPack"];
	$reject_forged_mails=$restrictions['reject_forged_mails'];	
	//$whitelists=Paragraphe("routing-domain-relay.png","{PostfixAutoBlockDenyAddWhiteList}","{PostfixAutoBlockDenyAddWhiteList_explain}","javascript:Loadjs('postfix.iptables.php?white-js=yes')");
	$rollover=CellRollOver();
	
$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
	<img src='img/96-planetes-free.png'>
	</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div class=explain>{smtpd_client_restrictions_text}</div>
	</td>
	<td valign='top'>
		$whitelists
	</td>
	</tr>
	</table>
	
	</td>
	</tr>
	</table>
	<div id='smtpd_client_restrictions_div'>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
	
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("disable_vrfy_command",1,$restrictions["disable_vrfy_command"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{disable_vrfy_command}</td>
	<td valign='middle' width=1%>". help_icon("{disable_vrfy_command_text}")."</td>
	</tr>	
	
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("reject_unknown_client_hostname",1,$restrictions["reject_unknown_client_hostname"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_unknown_client_hostname}</td>
	<td valign='middle' width=1%>". help_icon("{reject_unknown_client_hostname_text}")."</td>
	</tr>
	
	<tr>
	<td valign='middle' width=1%>". Field_checkbox("reject_unknown_reverse_client_hostname",1,$restrictions["reject_unknown_reverse_client_hostname"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_unknown_reverse_client_hostname}</td>
	<td valign='middle' width=1%>". help_icon("{reject_unknown_reverse_client_hostname_text}")."</td>
	</tr>
	
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("reject_unknown_sender_domain",1,$restrictions["reject_unknown_sender_domain"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_unknown_sender_domain}</td>
	<td valign='middle' width=1%>". help_icon("{reject_unknown_sender_domain_text}")."</td>
	</tr>
	
	<tr >
	<td valign='middle' width=1%>". Field_checkbox("reject_invalid_hostname",1,$restrictions["reject_invalid_hostname"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_invalid_hostname}</td>
	<td valign='middle' width=1%>". help_icon("{reject_invalid_hostname_text}")."</td>
	</tr>
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("reject_non_fqdn_sender",1,$restrictions["reject_non_fqdn_sender"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_non_fqdn_sender}</td>
	<td valign='middle' width=1%>". help_icon("{reject_non_fqdn_sender_text}")."</td>
	</tr>
	<tr >
	<td valign='middle' width=1%>". Field_checkbox("reject_forged_mails",1,$reject_forged_mails)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{reject_forged_mails}</td>
	<td valign='middle' width=1%>". help_icon("{reject_forged_mails_text}")."</td>
	</tr>	
	
	
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("EnablePostfixAntispamPack",1,$EnablePostfixAntispamPack_value)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{EnablePostfixAntispamPack}</td>
	<td valign='middle' width=1%>". help_icon("{EnablePostfixAntispamPack_text}")."</td>
	</tr>	

	<tr>
	<td valign='middle' width=1%>". Field_checkbox("EnablePostfixInternalDomainsCheck",1,$restrictions["EnablePostfixInternalDomainsCheck"])."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{EnablePostfixInternalDomainsCheck}</td>
	<td valign='middle' width=1%>". help_icon("{EnablePostfixInternalDomainsCheck_text}")."</td>
	</tr>	
	<tr class=oddRow>
	<td valign='middle' width=1%>". Field_checkbox("RestrictToInternalDomains",1,$restrictions["RestrictToInternalDomains"],null,null)."</td>
	<td valign='middle' style='font-size:14px;text-transform:capitalize'>{RestrictToInternalDomains}</td>
	<td valign='middle' width=1%>". help_icon("{RestrictToInternalDomains_text}")."</td>
	</tr>		
	
	
	</table>
	</div>
<hr>
	<div style='width:100%;text-align:right'>
	". button("{edit}","smtpd_client_restrictions_multi_save()")."
	
	</div>	
	";


//smtpd_client_connection_rate_limit = 100
//smtpd_client_recipient_rate_limit = 20
	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
	
	
	
}



function smtpd_client_restrictions_save(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$EnablePostfixAntispamPack=$_GET["EnablePostfixAntispamPack"];
	$main->SET_BIGDATA("hash_smtp_restrictions",base64_encode(serialize($_GET)));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");
	}





?>