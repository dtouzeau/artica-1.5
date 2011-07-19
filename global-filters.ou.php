<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["ArticaFakedMailFrom"])){SaveSettings();};

INDEX();


function INDEX(){
	if(!isset($_GET["ou"])){header('location:domains.index.php');exit;}
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$hash=$ldap->OUDatas($ou);
	$ArticaFakedMailFrom_table=array("pass"=>'{pass}',"quarantine"=>"{quarantine}","delete"=>"{delete}");
	$ArticaFakedMailFrom_field=Field_array_Hash($ArticaFakedMailFrom_table,"ArticaFakedMailFrom",$hash["ArticaFakedMailFrom"],null,null,null,'width:100px');
	
	$ArticaDenyNoMXRecords_table=array('pass'=>'{pass}',"reject"=>"{reject}");
	$ArticaDenyNoMXRecords_field=Field_array_Hash($ArticaDenyNoMXRecords_table,"ArticaDenyNoMXRecords",$hash["ArticaDenyNoMXRecords"],null,null,null,'width:100px');
	
	
	$ArticaOuTrustMyUSers_field=Field_yesno_checkbox_img('OuTrustMyUSers',$hash["OuTrustMyUSers"],'{enable_disable}');
	
	
	
	$ArticaOuTrustMyUSers="<H5>{trust_users}</H5>
	<div class=caption>{trust_users_text}</div>
	<table style='width:100%'>
	<tr>
		<td align='right' valign='top' nowrap><strong>{trust_users}</strong></td>
		<td align='left' valign='top'>$ArticaOuTrustMyUSers_field</td>
		
	</tr>	
	</table>
	
	";
	$ArticaOuTrustMyUSers=RoundedLightGreen($ArticaOuTrustMyUSers);
	
	$html="
	<form name='FFMQ'>
	<table style='width:100%;'>
	<tr>
		<td width=50% valign='top' style='margin:4px;padding:4px'>
	<input type='hidden' name='ou' value='$ou'>
	" . RoundedLightGrey("<H5>{ArticaFakedMailFrom}</H5>
	<div class=caption>{ArticaFakedMailFrom_text}</div>
	<table style='width:90%;border:1px solid #CCCCCC;padding:5px;margin:5px'>
	<tr>
		<td align='right' valign='top' nowrap><strong>{ArticaFakedMailFrom}</strong></td>
		<td align='left' valign='top'>$ArticaFakedMailFrom_field</td>
		
	</tr>
	</table>") . "</td>
	
	
	<td width=50% valign='top' style='margin:4px;padding:4px'>".
	
	RoundedLightGrey("
	<H5>{ArticaDenyNoMXRecords}</H5>
	<div class=caption>{ArticaDenyNoMXRecords_text}</div>
	<table style='width:90%;border:1px solid #CCCCCC;padding:5px;margin:5px'>
	<tr>
		<td align='right' valign='top' nowrap><strong>{ArticaDenyNoMXRecords}</strong></td>
		<td align='left' valign='top'>$ArticaDenyNoMXRecords_field</td>
		
	</tr>	
	</table>
	")."
	<br>
	$ArticaOuTrustMyUSers
	</td>
	</tr>
	<tr>
	<td colspan=2 align='right' style='border-top:1px solid #CCCCCC'><input type='submit' value='{edit}&nbsp;&raquo;' style='width:150px'></td>
	</tr>
	</table>
	</form>";
		
		
		
	
	
$cfg["JS"][]="js/quarantine.ou.js";
$tpl=new template_users('{artica_filters_rules}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}

function SaveSettings(){
	$ou=$_GET["ou"];
	$upd["ArticaFakedMailFrom"][0]=$_GET["ArticaFakedMailFrom"];
	$upd["ArticaDenyNoMXRecords"][0]=$_GET["ArticaDenyNoMXRecords"];
	$upd["OuTrustMyUSers"][0]=$_GET["OuTrustMyUSers"];
	
	
	
	$ldap=new clladp();
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$ldap->Ldap_modify($dn,$upd);
	
}


?>

