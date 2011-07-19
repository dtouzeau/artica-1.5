<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.main_cf.inc');
$user=new usersMenus();
$tpl=new templates();
if($user->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}

if(isset($_GET["loadsaslList"])){LoadSaslTable();exit;}
if(isset($_GET["isp_server_name"])){add_isp_server_name();exit;}
if(isset($_GET["sasl_delete"])){sasl_delete();exit;}
if(isset($_GET["EditSasl"])){EditSasl();exit;}
page();

function Page(){
$main=new main_cf();
	$smtp_sasl_auth_enable=$main->main_array["smtp_sasl_auth_enable"];
	$page=CurrentPageName();
	
	$intro=applysettings("postfix") . RoundedLightBlue('<p style="text-align:justify;"><img src="img/infowarn-64.png" align=left style="margin:3px">{smtp_sasl_auth_enable_text}</p>');
	$intro=$intro . "<br>" . "" . "<script>LoadAjax('sasllist','$page?loadsaslList=yes');</script>";
	$form="".
	
	RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td valign='top'>" . Field_yesno_checkbox_img('smtp_sasl_auth_enable',$smtp_sasl_auth_enable,'{enable_disable}')."</td>
	<td>
		<H5>{smtp_sasl_auth_enable}</H5>
		<br>
		{smtp_sasl_auth_enable_text2}
	</td>
	</tr>
	<tr>
		<td align='right' colspan=2><input type='button' OnClick=\"javascript:EditSasl();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>") . "
	<br>
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/klakcon_285.png'></td>
	<td>
		<H5>{add_credentials}</H5>
	<br>
		<table style='width:100%'>
		<tr>
			<td colspan=2><strong>{isp_server_name}:</strong></td>
		</tr>
		<tr>
			<td colspan=2>" . Field_text('isp_server_name',null)."</td>
		</tr>		
		<tr>
			<td width=1% nowrap align='right'><strong>{username}:</strong></td>
			<td>" . Field_text('username',null)."</td>
		</tr>	
		<tr>
			<td align='right'><strong>{password}:</strong></td>
			<td>" . Field_text('password',null)."</td>
		</tr>
		<tr>
		<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:sasl_add_isp_relay();\">
		</tr>			
		</table>
	</td>
	</tr>
	</table>")."<br>" .RoundedLightGrey("<H5>{table}</H5><div id='sasllist'></div>");
	
	$html="
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=50%>	
	$form</td>
	<td valign='top'  width=50%>" . RoundedLightGreen("<table style='width:100%'>
	<tr><td width=1%>" . imgtootltip('restore-on.png','{go_back}',"MyHref('artica.wizard.php')") . "</td>
	<td><H5>{return_to} {artica_wizard}</H5></td>
	</tr></table>")."<br>$intro</td>
	</tr>
	</table>";
	
	
	
	
	
	
$cfg["LANG_FILE"]="artica.wizard.php";
$cfg["JS"][]="js/postfix-sasl.js";
$tpl=new template_users('{send_to_isp}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;


}
function LoadSaslTable(){
	$sls=new smtp_sasl_password_maps();
	$hash=$sls->smtp_sasl_password_hash;
	if(!is_array($hash)){echo "&nbsp;";return null;}
	$html="<table style='width:100%'>
	<tr>
	<td>&nbsp;</td>
	<td><strong>{isp_server_name}</strong></td>
	<td><strong>{username}</strong></td>
	<td>&nbsp;</td>
	</tr>";
	
	while (list ($num, $val) = each ($hash) ){
		$tb=explode(":",$val);
		$html=$html . "
		<tr>
		<td><img src='img/icon_newest_reply.gif'></td>
		<td><strong>$num</strong></td>
		<td><strong>{$tb[0]}</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"sasl_delete('$num')") ."</td>
		</tr>
		
		";
		
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html . "</table>",'artica.wizard.php');
	
}

function add_isp_server_name(){
	$ldap=new clladp();

		if(!$ldap->ExistsDN("cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix")){
			$upd["cn"][]="smtp_sasl_password_maps";
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$ldap->ldap_add("cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix",$upd);
			unset($upd);
			
		}
	
	$cn="cn={$_GET["isp_server_name"]},cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	if($ldap->ExistsDN($cn)){return null;}
	$upd["cn"]=$_GET["isp_server_name"];
	$upd["objectClass"][]="top";
	$upd["objectClass"][]="PostfixSmtpSaslPaswordMaps";
	$upd["SmtpSaslPasswordString"]="{$_GET["username"]}:{$_GET["password"]}";
	$ldap->ldap_add($cn,$upd);
	}
function sasl_delete(){
	$ldap=new clladp();
	$cn="cn={$_GET["sasl_delete"]},cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($cn,true);
	
}

function EditSasl(){
	
	$main=new main_cf();
	if(isset($_GET["EditSasl"])=="yes"){
		$main->smtp_sasl_password_maps_enable();
	}else{
		$main->smtp_sasl_password_maps_disable();
	}
	
	
}
?>