<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');	
	include_once('ressources/class.kas-filter.inc');
	
	

page();	
function page(){
$usersmenus=new usersMenus();
$kas=new kas_filter();
$pattern_date=$kas->GetPatternDate();
$linkPattern=texttooltip('{ave_pattern_date}','{time_date_com_text_moscow}','http://www.timeanddate.com/worldclock/city.html?n=166');
if($usersmenus->AsPostfixAdministrator==true or $usersmenus->AllowChangeKas==true or $usersmenus->AllowChangeAntiSpamSettings==true){}else{header('location:users.index.php');exit;}	
$html="
<div class='caption'><strong>$linkPattern:&nbsp;$pattern_date</div>
<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'><img src='img/caterpillarkas.jpg'>
</td>
<td valign='top'>
	<table>";

if($usersmenus->AsPostfixAdministrator==true){
		$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-caterpillar.jpg','{antispam_engine}','{antispam_engine_text}','kas.engine.settings.php') ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-lego.jpg','{product_update_settings}','{product_update_settings_text}','kas.keepupd2date.settings.php') ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-licence.jpg','{product_licence}','{product_licence_text}','kas.licence.settings.php') ."</td></tr>";
		}
		
if($usersmenus->AllowChangeKas==true){
		$artica=new artica_general();
		if($artica->EnableGroups=='yes'){
			$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-groupe.jpg','{antispam_rules_group}','{antispam_rules_group_text}','kas.group.rules.php') ."</td></tr>";
		}
		else{
			$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-rules-64.jpg','{antispam_rules}','{antispam_rules_text}','kas.group.rules.php') ."</td></tr>";	
		}
}

if($usersmenus->AllowChangeAntiSpamSettings==true){
	$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-userrules-64.jpg','{antispam_user_rules}','{antispam_user_rules_text}','kas.user.rules.php') ."</td></tr>";
	
}

		
		
		
$html=$html . "</table>
</td>
</tr>
</table>
";
$tpl=new template_users('Kaspersky Anti-spam',$html);
echo $tpl->web_page;
	
	
	
}
	