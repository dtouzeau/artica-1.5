<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/kav4mailservers.inc');
	
	if(isset($_GET["Status"])){echo Status();exit;}

page();	
function page(){
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true or $usersmenus->AllowChangeKas==true){}else{header('location:users.index.php');exit;}	
$page=CurrentPageName();



$html="
<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'>
	<table>
	<tr>
		<td width=1% valign='top'><img src='img/tank.jpg'></td>
		</tr>
	</table>
</td>
<td valign='top'>
<div id='servinfos'></div>
<script>LoadAjax('servinfos','$page?Status=yes');</script>
<br>
	<table>";

if($usersmenus->AsPostfixAdministrator==true){
	$html=$html . "	
	
	<tr><td valign='top' >".Paragraphe('folder-lego.jpg','{product_update_settings}','{product_update_settings_text}','kav.keepupd2date.settings.php') ."</td></tr>
	<tr><td valign='top' >".Paragraphe('folder-licence.jpg','{product_licence}','{product_licence_text}','kav.licence.settings.php') ."</td></tr>
	<tr><td valign='top'>  ".Paragraphe('folder-logs-64.jpeg','{events}','{events_text}','kav.events.php') ."</td></tr>";
		}
		

		

		
$html=$html . "</table>
</td>
</tr>
</table>
";
$tpl=new template_users('Kaspersky Antivirus',$html);
echo $tpl->web_page;
	
	
	
}

function Status(){
$kav=new kav4mailservers();
$linkPattern=texttooltip($kav->pattern_date,'{time_date_com_text_moscow}','http://www.timeanddate.com/worldclock/city.html?n=166');
$page=CurrentPageName();
if($kav->pid==null){$img1="status_critical.gif";}else{$img1="status_ok.gif";}

$status=RoundedLightGreen("
<H4>Status</H4>
<table style='width:100%'>
<tr>
	<td valign='top'align='center'><img src='img/$img1'></td>
	<td align=right valign='top' ><strong>{use_pid}:</strong></td>
	<td valign='top'>$kav->pid</td>
</tr>
<tr>
	<td valign='top' align='center'><img src='img/$img1'></td>
	<td align=right valign='top'><strong>{memory}:</strong></td>
	<td valign='top'>$kav->memory mb</td>
</tr>

<tr>
	<td valign='top' align='center'><img src='img/icon_info.gif'></td>
	<td align=right valign='top'><strong>{version}:</strong></td>
	<td valign='top'>$kav->version</td>
</tr>
<tr>
<td valign='top' align='center'><img src='img/icon_info.gif'></td>
<td nowrap align=right valign='top'><strong>{pattern_ver}:</strong></td>
<td><strong>$linkPattern</strong></td>

</tr>
<tr><td colspan=3 align='right'>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('servinfos','$page?Status=yes');")."</td></tr>
</table>");
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($status);	
	
}
	