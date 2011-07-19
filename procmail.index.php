<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	

page();	
function page(){
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true){}else{header('location:users.index.php');exit;}	
$html="<table style='width:600px' align=center>
<tr>
<td width=50% valign='top' class='caption' style='text-align:justify'>
<img src='img/bg_procmail.jpg'><br>
{about_procmail}</td>
<td valign='top'>
	<table>";

if($usersmenus->AsPostfixAdministrator==true){
		$html=$html . "<tr><td valign='top'>  ".Paragraphe('folder-rules-64.jpg','{manage_procmail_rules}','{manage_procmail_rules_text}','javascript:TreeProcMailRules();') ."</td></tr>
		<tr><td valign='top'>  ".Paragraphe('folder-logs-64.jpeg','{events}','{events_text}','procmail.daemon.events.php') ."</td></tr>";
		}

		

		
$html=$html . "</table>
</td>
</tr>
</table>
";
$tpl=new template_users('Procmail',$html);
echo $tpl->web_page;
	
	
	
}
	