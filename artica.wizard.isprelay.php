<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.main_cf.inc');
$user=new usersMenus();
$tpl=new templates();
if($user->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}

if(isset($_GET["EditRelayhost"])){EditRelayhost();exit;}



page();

function Page(){
$main=new main_cf();

	$page=CurrentPageName();
	$intro=RoundedLightGreen('<img src="img/infowarn-64.png" align=left style="margin:3px">{send_isp_relay_text3}');
	
	$form="".
	
	RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td>
		<H5>{servername}</H5>
		<br>
		{send_isp_relay_text2}
	</td>
	</tr>
	<tr>
			<td colspan=2>" . Field_text('isp_server_ip',$main->main_array["relayhost"])."</td>
		</tr>	
	<tr>
		<td align='right' colspan=2><input type='button' OnClick=\"javascript:EditRelayhost();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>") . "
	<br>";

	
	$html="
	
	<table style='width:100%'>
	<tr>
	<td valign='top' style='width:50%'>

	$form</td>
	<td valign='top'>
		" . RoundedLightGreen("<table style='width:100%'>
	<tr><td width=1%>" . imgtootltip('restore-on.png','{go_back}',"MyHref('artica.wizard.php')") . "</td>
	<td><H5>{return_to} {artica_wizard}</H5></td>
	</tr></table>")."<br>" . applysettings("postfix") ."<br>$intro</td>
	</tr>
	</table>";
	
	
	
	
	
	
$cfg["LANG_FILE"]="artica.wizard.php";
$cfg["JS"][]="js/postfix-sasl.js";
$tpl=new template_users('{send_isp_relay}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;


}

function EditRelayhost(){
	$main=new main_cf();
	$main->main_array["relayhost"]=$_GET["EditRelayhost"];
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
	
}

?>