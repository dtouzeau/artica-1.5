<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");


$user=new usersMenus();
$tpl=new Templates();
if($user->AsPostfixAdministrator==false){header('location:users.index.php');}

if(isset($_GET["smtpd_sasl_auth_enable"])){smtpd_sasl_auth_enable();exit;}
if(isset($_GET["TLSLoggingSave"])){TLSLoggingLevelSave();exit;}
if(isset($_GET["TLSStartTLSOffer"])){TLSStartTLSOffer();exit;}
if(isset($_GET["smtp_tls_note_starttls_offer"])){smtp_tls_note_starttls_offer();exit;}
if(isset($_GET["TLSAddSMTPServer"])){TLSAddSMTPServer();exit;}
if(isset($_GET["loadinfos"])){echo otherinfo();exit;}


postfix_sasl();
	
function postfix_sasl(){

		$conf=new main_cf();
		
		$enable_sasl=Field_yesno_checkbox_img('smtpd_sasl_auth_enable',$conf->main_array["smtpd_sasl_auth_enable"],'{enable_disable}');
		$smtpd_sasl_authenticated_header=Field_yesno_checkbox_img('smtpd_sasl_authenticated_header',$conf->main_array["smtpd_sasl_authenticated_header"],'{enable_disable}');
		$smtp_sender_dependent_authentication=Field_yesno_checkbox_img('smtp_sender_dependent_authentication',$conf->main_array["smtp_sender_dependent_authentication"],'{enable_disable}');
		
		
		$html="
		<form name='sasl'>
		<table style='width:100%'>
		<tr>
		<td width=40% valign='top'>" . RoundedLightBlue("
		<div  style='padding:5px;font-size:11px;'>
		<img src=\"img/infowarn-64.png\" align=left style=\"margin:3px\">{sasl_intro}</div>")."
		</td>
		<td width='60%' valign='top'>
	".RoundedLightGreen("
		<table style='width:100%;padding:5px'>
			<tr>
			<td width=1% align='center'>$enable_sasl</td>
			<td><strong>{smtpd_sasl_auth_enable}</strong>
			</tr>
			<td width=1% align='center'>$smtpd_sasl_authenticated_header</td>
			<td><strong>{smtpd_sasl_authenticated_header}</strong>
			</tr>
			</tr>
			<td width=1% align='center' valign='top'>$smtp_sender_dependent_authentication</td>
			<td><strong>{smtp_sender_dependent_authentication}</strong><div class=caption>{smtp_sender_dependent_authentication_text}</div>
			</tr>			
			<tr>
			<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveEnableSasl();\"></td>
			</tr>
		</table>
			<br><br><br>") . "</td></tr></table></form>";
		
$page=otherinfo();

		
$js["JS"][]='js/postfix-sasl.js';
 $tplusr=new template_users('{sasl_title}',"$html<br><div id='infos'>$page</div>",0,0,0,0,$js);
 echo $tplusr->web_page;
	
}

function otherinfo(){
	

$conf=new main_cf();
if($conf->main_array["smtpd_sasl_auth_enable"]=='yes'){
	$sls=new smtp_sasl_password_maps();
	$hash=$sls->smtp_sasl_password_hash;		
	if(count($hash)==0){
		$warn=RoundedLightYellow("<img src='img/warning32.png' style='margin:5px' align='left'><strong>{warning_no_sasl_database}</strong>");
	}
}else{return null;}

		
		
$play_is=playis();

$page="<table style='width:100%'>
<tr>
<td valign='top'>$warn</td>
<td>$play_is</td>
</tr>
</table>";
$tpl=new templates();

return $tpl->_ENGINE_parse_body($page);	
	
}


function smtpd_sasl_auth_enable(){
	$main=new main_cf();
	$clientRestriction= new smtpd_restrictions();
	//smtpd_recipient_restrictions
	if($_GET["smtpd_sasl_auth_enable"]=="no"){$clientRestriction->DeleteKey("permit_sasl_authenticated","smtpd_recipient_restrictions");}
	if($_GET["smtpd_sasl_auth_enable"]=="yes"){
		$clientRestriction->AddKey("permit_mynetworks",'smtpd_recipient_restrictions');
		$clientRestriction->AddKey("permit_sasl_authenticated",'smtpd_recipient_restrictions');
		$clientRestriction->AddKey("reject_unauth_destination",'smtpd_recipient_restrictions');
	 	}
	$main->main_array["smtp_sender_dependent_authentication"]=$_GET["smtp_sender_dependent_authentication"];
	$main->main_array["smtpd_sasl_auth_enable"]=$_GET["smtpd_sasl_auth_enable"];
	$main->main_array["smtpd_sasl_authenticated_header"]=$_GET["smtpd_sasl_authenticated_header"];
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
}

function playis(){
$play_is=RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td valign='top'>". imgtootltip('icon_settings-64.png','{edit}',"MyHref('artica.wizard.ispout.php')")."</td>
	<td valign='top'>
		<H5>{fill_database}</h5>
		{fill_database_text}
	
	</td>
	</tr>
	</table>",null,1);

$tpl=new templates();
return $tpl->_ENGINE_parse_body($play_is,'artica.wizard.php');
	
}


	
	

	


?>