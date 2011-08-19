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
	
	if(isset($_GET["message_size_limit"])){save();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{messages_restriction}');
	$html="
	var x='$x';
	$datas
	function LoadMainMMultiRestrictions(){
		YahooWinS(550,'$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
	}
	
var x_MainMultiSaveMessagesRestrictions=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.Length>0){alert(tempvalue);}
	YahooWinSHide();
    }	
	
function MainMultiSaveMessagesRestrictions(){
	var XHR = new XHRConnection();
	XHR.appendData('message_size_limit',document.getElementById('message_size_limit').value);
	XHR.appendData('default_destination_recipient_limit',document.getElementById('default_destination_recipient_limit').value);
	XHR.appendData('smtpd_recipient_limit',document.getElementById('smtpd_recipient_limit').value);
	XHR.appendData('mime_nesting_limit',document.getElementById('mime_nesting_limit').value);
	XHR.appendData('header_address_token_limit',document.getElementById('header_address_token_limit').value);
	XHR.appendData('virtual_mailbox_limit',document.getElementById('virtual_mailbox_limit').value);
	XHR.appendData('max_rcpt_to',document.getElementById('max_rcpt_to').value);
	
	
	
	XHR.appendData('hostname','{$_GET["hostname"]}');
	XHR.appendData('ou','{$_GET["ou"]}');
	document.getElementById('messages_restriction_id').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_MainMultiSaveMessagesRestrictions);
}		
		
	LoadMainMMultiRestrictions();
	
	";
	
	echo $html;
	
	
	
	
}

function save(){
		$ou=$_GET["ou"];
		$hostname=$_GET["hostname"];
		$maincf=new maincf_multi($hostname,$ou);	
		
	$sock=new sockets();
	$_GET["message_size_limit"]=($_GET["message_size_limit"]*1000)*1024;
	$_GET["virtual_mailbox_limit"]=($_GET["virtual_mailbox_limit"]*1000)*1024;
	$maincf->SET_VALUE("message_size_limit",$_GET["message_size_limit"]);
	$maincf->SET_VALUE("default_destination_recipient_limit",$_GET["default_destination_recipient_limit"]);
	$maincf->SET_VALUE("smtpd_recipient_limit",$_GET["smtpd_recipient_limit"]);
	$maincf->SET_VALUE("mime_nesting_limit",$_GET["mime_nesting_limit"]);
	$maincf->SET_VALUE("header_address_token_limit",$_GET["header_address_token_limit"]);
	$maincf->SET_VALUE("virtual_mailbox_limit",$_GET["virtual_mailbox_limit"]);
	$maincf->SET_VALUE("max_rcpt_to",$_GET["max_rcpt_to"]);
	$sock->getFrameWork("cmd.php?postfix-multi-settings=$hostname");
}

function popup(){
		
		$ou=$_GET["ou"];
		$hostname=$_GET["hostname"];
		$maincf=new maincf_multi($hostname,$ou);
		$message_size_limit=$maincf->GET("message_size_limit");
		$default_destination_recipient_limit=$maincf->GET("default_destination_recipient_limit");
		$smtpd_recipient_limit=$maincf->GET("smtpd_recipient_limit");
		$mime_nesting_limit=$maincf->GET("mime_nesting_limit");
		$header_address_token_limit=$maincf->GET("header_address_token_limit");
		$virtual_mailbox_limit=$maincf->GET("virtual_mailbox_limit");
		$message_size_limit=($maincf->GET("message_size_limit")/1024)/1000;
		$virtual_mailbox_limit=($maincf->GET("virtual_mailbox_limit")/1024)/1000;
		$max_rcpt_to=$maincf->GET("max_rcpt_to");
		
		if($message_size_limit==null){$message_size_limit=(10240000/1024)/1000;}
		if($virtual_mailbox_limit==null){$virtual_mailbox_limit=(10240000/1024)/1000;}
		if($default_destination_recipient_limit==null){$default_destination_recipient_limit=50;}
		if($smtpd_recipient_limit==null){$smtpd_recipient_limit=1000;}
		if($mime_nesting_limit==null){$mime_nesting_limit=100;}
		if($header_address_token_limit==null){$header_address_token_limit=10240;}		
		if(!is_numeric($max_rcpt_to)){$max_rcpt_to=0;}
		
		$html="
		
		<div id='messages_restriction_id'>
		<div style='font-size:16px'><strong>{restrictions}</strong></div>
		<table class=form style='width:100%'>
		<tr>
			    <td nowrap class=legend style='font-size:13px'>{message_size_limit}</strong>:</td>
			    <td style='font-size:13px'>" . Field_text('message_size_limit',$message_size_limit,'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;MB</td>
			    <td>". help_icon('{message_size_limit_text}')."</td>
		</tr>
		<tr>
			    <td nowrap class=legend style='font-size:13px'>{virtual_mailbox_limit}</strong>:</td>
			    <td style='font-size:13px'>" . Field_text('virtual_mailbox_limit',$virtual_mailbox_limit,'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;MB </td>
			    <td>". help_icon('{virtual_mailbox_limit_text}')."</td>
		</tr>		
		<tr>
			    <td nowrap class=legend style='font-size:13px'>{max_rcpt_to}</strong>:</td>
			    <td style='font-size:13px'>" . Field_text('max_rcpt_to',$max_rcpt_to,'width:60px;font-size:13px;padding:3px;text-align:right')."&nbsp;MB</td>
			    <td>". help_icon('{max_rcpt_to_text}')."</td>
		</tr>	
		<tr>
			    <td nowrap class=legend style='font-size:13px'>{mime_nesting_limit}</strong>:</td>
			    <td>" . Field_text('mime_nesting_limit',$mime_nesting_limit,'width:60px;font-size:13px;padding:3px;text-align:right')." </td>
			     <td>". help_icon('{mime_nesting_limit_text}')."</td>
		</tr>	
		<tr>
			    <td nowrap class=legend style='font-size:13px'>{header_address_token_limit_field}</strong>:</td>
			    <td>" . Field_text('header_address_token_limit',$header_address_token_limit,'width:60px;font-size:13px;padding:3px;text-align:right')." </td>
			     <td>". help_icon('{header_address_token_limit_explain}')."</td>
		</tr>
		</table>
						
		
		<div style='font-size:16px'><strong>{performances}</strong></div>
		<table class=form style='width:100%'>
		<tr>
			    <td nowrap class=legend style='font-size:13px'>{default_destination_recipient_limit}</strong>:</td>
			    <td>" . Field_text('default_destination_recipient_limit',$default_destination_recipient_limit,'width:60px;font-size:13px;padding:3px;text-align:right')."</td>
			     <td>". help_icon('{default_destination_recipient_limit_text}')."</td>
	</tr>
	
	<tr>
			    <td nowrap class=legend style='font-size:13px'>{smtpd_recipient_limit}</strong>:</td>
			    <td>" . Field_text('smtpd_recipient_limit',$smtpd_recipient_limit,'width:60px;font-size:13px;padding:3px;text-align:right')."</td>
			     <td>". help_icon('{smtpd_recipient_limit_text}')."</td>
		</tr>
		<tr><td colspan=2 align='rigth' style='padding-right:10px;text-align:right'>
		<hr>". button("{apply}","MainMultiSaveMessagesRestrictions()")."
		</td></tr>
	</table>
	</div>";


$tpl=new templates();
echo  $tpl->_parse_body($html);
}	

?>