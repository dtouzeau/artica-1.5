<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}


		if(isset($_GET["script"])){ajax_js();exit;}
		if(isset($_GET["ajax-pop"])){ajax_pop();exit;}
		if(isset($_GET["message_size_limit"])){save();exit;}
		if(isset($_GET["PostfixNotifyMessagesRestrictions"])){PostfixNotifyMessagesRestrictions_save();exit;}
		if(isset($_GET["ArticaPolicyFilterMaxRCPTInternalDomainsOnly"])){ArticaPolicyFilterMaxRCPTInternalDomainsOnly_save();exit;}
		
		
		

function ajax_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{messages_restriction}');
	$html="
	var x='$x';
	$datas
	function LoadMain(){
		YahooWinS(550,'$page?ajax-pop=yes','$title');
	}
	
	
		
	LoadMain();
	
	";
	
	echo $html;
	
	
	
	
}

function PostfixNotifyMessagesRestrictions_save(){
	$sock=new sockets();
	$sock->SET_INFO("PostfixNotifyMessagesRestrictions",$_GET["PostfixNotifyMessagesRestrictions"]);
	$sock->getFrameWork("cmd.php?restart-artica-maillog=yes");
	
}

function ArticaPolicyFilterMaxRCPTInternalDomainsOnly_save(){
	$sock=new sockets();
	$sock->SET_INFO("ArticaPolicyFilterMaxRCPTInternalDomainsOnly",$_GET["ArticaPolicyFilterMaxRCPTInternalDomainsOnly"]);
}

function save(){
	$sock=new sockets();
	$main=new maincf_multi("master","master");
	$_GET["message_size_limit"]=($_GET["message_size_limit"]*1000)*1024;
	$_GET["virtual_mailbox_limit"]=($_GET["virtual_mailbox_limit"]*1000)*1024;
	$sock->SET_INFO("message_size_limit",$_GET["message_size_limit"]);
	$sock->SET_INFO("default_destination_recipient_limit",$_GET["default_destination_recipient_limit"]);
	$sock->SET_INFO("smtpd_recipient_limit",$_GET["smtpd_recipient_limit"]);
	$sock->SET_INFO("mime_nesting_limit",$_GET["mime_nesting_limit"]);
	$sock->SET_INFO("header_address_token_limit",$_GET["header_address_token_limit"]);
	$sock->SET_INFO("virtual_mailbox_limit",$_GET["virtual_mailbox_limit"]);
	$sock->SET_INFO("header_size_limit",$_GET["header_size_limit"]*1024);
	$sock->SET_INFO("SpamassassinMaxRCPTScore",$_GET["SpamassassinMaxRCPTScore"]);
	$main->SET_VALUE("max_rcpt_to",$_GET["max_rcpt_to"]);
	

		$users=new usersMenus();
		$users->LoadModulesEnabled();
		
		$EnableAmavisDaemon=$users->EnableAmavisDaemon;
		if(!$user->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
		if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}	
		$sock->getFrameWork("cmd.php?postfix-others-values=yes");
		if($EnableAmavisDaemon==1){$sock->getFrameWork("cmd.php?spamass-build=yes");}
}

function ajax_pop(){
		
		$sock=new sockets();
		$users=new usersMenus();
		$users->LoadModulesEnabled();
		
		$EnableAmavisDaemon=$users->EnableAmavisDaemon;
		if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
		if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}
		
		$EnableArticaPolicyFilter=$sock->GET_INFO("EnableArticaPolicyFilter");
		if(!is_numeric($EnableArticaPolicyFilter)){$EnableArticaPolicyFilter=0;}
		
		
		
		$ArticaPolicyFilterMaxRCPTInternalDomainsOnly=$sock->GET_INFO("ArticaPolicyFilterMaxRCPTInternalDomainsOnly");
		$SpamassassinMaxRCPTScore=$sock->GET_INFO("SpamassassinMaxRCPTScore");
		$page=CurrentPageName();
		$main=new main_cf();
		$main->FillDefaults();
		
		$message_size_limit=$sock->GET_INFO("message_size_limit");
		
		
		$main->main_array["default_destination_recipient_limit"]=$sock->GET_INFO("default_destination_recipient_limit");
		$main->main_array["smtpd_recipient_limit"]=$sock->GET_INFO("smtpd_recipient_limit");
		$main->main_array["mime_nesting_limit"]=$sock->GET_INFO("mime_nesting_limit");
		$PostfixNotifyMessagesRestrictions=$sock->GET_INFO("PostfixNotifyMessagesRestrictions");
		
		$main=new maincf_multi("master","master");
		$header_address_token_limit=$sock->GET_INFO("header_address_token_limit");
		$header_size_limit=$sock->GET_INFO("header_size_limit");
		$max_rcpt_to=$main->GET("max_rcpt_to");
		
		if(!is_numeric($message_size_limit)){$message_size_limit=10240000;}
		
		$main->main_array["virtual_mailbox_limit"]=$sock->GET_INFO("virtual_mailbox_limit");
		$message_size_limit=($message_size_limit/1024)/1000;
		$main->main_array["virtual_mailbox_limit"]=($main->main_array["virtual_mailbox_limit"]/1024)/1000;
		
		
		if(!is_numeric($header_address_token_limit)){$header_address_token_limit=10240;}
		if(!is_numeric($header_size_limit)){$header_size_limit=102400;}
		if(!is_numeric($max_rcpt_to)){$max_rcpt_to=0;}
		if(!is_numeric($PostfixNotifyMessagesRestrictions)){$PostfixNotifyMessagesRestrictions=0;}
		$header_size_limit=($header_size_limit/1024);
		if(!is_numeric($SpamassassinMaxRCPTScore)){$SpamassassinMaxRCPTScore=10;}
		
		
		
		$html="
		
		<div id='messages_restriction_id'>
		
		

	


		<div style='font-size:16px'><strong>{max_rcpt_to}</strong></div>
		<table class=form style='width:100%'>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{max_rcpt_to}</strong>:</td>
			<td style='font-size:13px'>" . Field_text('max_rcpt_to',$max_rcpt_to,'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;{recipients} </td>
			<td>". help_icon('{max_rcpt_to_text}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{max_rcpt_to_onlyForLocalDomains}</strong>:</td>
			<td style='font-size:13px'>".Field_checkbox("ArticaPolicyFilterMaxRCPTInternalDomainsOnly",1,$ArticaPolicyFilterMaxRCPTInternalDomainsOnly,"ArticaPolicyFilterMaxRCPTInternalDomainsOnlySave()")."</td>
			<td>". help_icon('{max_rcpt_to_onlyForLocalDomains_text}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{score}</strong>:</td>
			<td style='font-size:13px'>".Field_text('SpamassassinMaxRCPTScore',$SpamassassinMaxRCPTScore,'width:40px;font-size:13px;padding:3px;text-align:right')."</td>
			<td>". help_icon('{SpamassassinMaxRCPTScore_text}')."</td>
		</tr>
		<tr>
		<td colspan=3 align='rigth' style='padding-right:10px;text-align:right'>
			". button("{apply}","SaveMessagesRestrictions()")."</td>
		</tr>
		</table>									
		
		<div style='font-size:16px'><strong>{restrictions}</strong></div>
		<table class=form style='width:100%'>

		
		<tr>
			<td nowrap class=legend style='font-size:13px'>{message_size_limit}</strong>:</td>
			<td style='font-size:13px'>" . Field_text('message_size_limit',$message_size_limit,'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;MB</td>
			<td>". help_icon('{message_size_limit_text}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{virtual_mailbox_limit}</strong>:</td>
			<td style='font-size:13px'>" . Field_text('virtual_mailbox_limit',$main->main_array["virtual_mailbox_limit"],'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;MB </td>
			<td>". help_icon('{virtual_mailbox_limit_text}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{mime_nesting_limit}</strong>:</td>
			<td>" . Field_text('mime_nesting_limit',$main->main_array["mime_nesting_limit"],'width:60px;font-size:13px;padding:3px;text-align:right')." </td>
			<td>". help_icon('{mime_nesting_limit_text}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{header_address_token_limit_field}</strong>:</td>
			<td>" . Field_text('header_address_token_limit',$header_address_token_limit,'width:60px;font-size:13px;padding:3px;text-align:right')." </td>
			<td>". help_icon('{header_address_token_limit_explain}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{header_size_limit}:</td>
			<td style='font-size:13px'>" . Field_text('header_size_limit',$header_size_limit,'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;KB </td>
			<td>". help_icon('{header_size_limit_text}')."</td>
		</tr>
		<tr>
			<td nowrap class=legend style='font-size:13px'>{notify}:</td>
			<td style='font-size:13px'>" . Field_checkbox("PostfixNotifyMessagesRestrictions",1,$PostfixNotifyMessagesRestrictions,"PostfixNotifyMessagesRestrictionsSave()")."</td>
			<td>". help_icon('{PostfixNotifyMessagesRestrictions_text}')."</td>
		</tr>
	<tr>
	<td colspan=3 align='rigth' style='padding-right:10px;text-align:right'>
		". button("{apply}","SaveMessagesRestrictions()")."</td>
	</tr>		
		</table>
		
		
	<div style='font-size:16px'><strong>{performances}</strong></div>
	<table class=form style='width:100%'>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{default_destination_recipient_limit}</strong>:</td>
		<td>" . Field_text('default_destination_recipient_limit',$main->main_array["default_destination_recipient_limit"],'width:60px;font-size:13px;padding:3px;text-align:right')."</td>
		<td>". help_icon('{default_destination_recipient_limit_text}')."</td>
	</tr>
	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtpd_recipient_limit}</strong>:</td>
		<td>" . Field_text('smtpd_recipient_limit',$main->main_array["smtpd_recipient_limit"],'width:60px;font-size:13px;padding:3px;text-align:right')."</td>
		<td>". help_icon('{smtpd_recipient_limit_text}')."</td>
	</tr>



	<tr>
	<td colspan=3 align='rigth' style='padding-right:10px;text-align:right'>
		". button("{apply}","SaveMessagesRestrictions()")."</td>
	</tr>
	</table>
	</div>
	<script>
var x_SaveMessagesRestrictions=function (obj) {
	tempvalue=obj.responseText;
	YahooWinSHide();
    }	
	
function SaveMessagesRestrictions(){
	var XHR = new XHRConnection();
	XHR.appendData('header_size_limit',document.getElementById('header_size_limit').value);
	XHR.appendData('message_size_limit',document.getElementById('message_size_limit').value);
	XHR.appendData('default_destination_recipient_limit',document.getElementById('default_destination_recipient_limit').value);
	XHR.appendData('smtpd_recipient_limit',document.getElementById('smtpd_recipient_limit').value);
	XHR.appendData('mime_nesting_limit',document.getElementById('mime_nesting_limit').value);
	XHR.appendData('header_address_token_limit',document.getElementById('header_address_token_limit').value);
	XHR.appendData('virtual_mailbox_limit',document.getElementById('virtual_mailbox_limit').value);
	XHR.appendData('max_rcpt_to',document.getElementById('max_rcpt_to').value);
	XHR.appendData('SpamassassinMaxRCPTScore',document.getElementById('SpamassassinMaxRCPTScore').value);
	
	
	
	document.getElementById('messages_restriction_id').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveMessagesRestrictions);
}	

function PostfixNotifyMessagesRestrictionsSave(){
	var XHR = new XHRConnection();
	if(document.getElementById('PostfixNotifyMessagesRestrictions').checked){
		XHR.appendData('PostfixNotifyMessagesRestrictions',1);
	}else{
		XHR.appendData('PostfixNotifyMessagesRestrictions',0);
	}
	XHR.sendAndLoad('$page', 'GET');
}

function ArticaPolicyFilterMaxRCPTInternalDomainsOnlySave(){
	var XHR = new XHRConnection();
	if(document.getElementById('ArticaPolicyFilterMaxRCPTInternalDomainsOnly').checked){
		XHR.appendData('ArticaPolicyFilterMaxRCPTInternalDomainsOnly',1);
	}else{
		XHR.appendData('ArticaPolicyFilterMaxRCPTInternalDomainsOnly',0);
	}
	XHR.sendAndLoad('$page', 'GET');
}


function CheckRequiredRestrictionsFields(){
	var EnableArticaPolicyFilter=$EnableAmavisDaemon;
	if(EnableArticaPolicyFilter==0){
		document.getElementById('ArticaPolicyFilterMaxRCPTInternalDomainsOnly').disabled=true;
		document.getElementById('SpamassassinMaxRCPTScore').disabled=true;
	}
}


CheckRequiredRestrictionsFields();
</script>
	
	
	";


$tpl=new templates();
echo  $tpl->_parse_body($html);
}