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
<img src='img/bg_mailman.jpg'><br>
{mailman_intro}</td>
<td valign='top'>
	<table>";

if($usersmenus->AsPostfixAdministrator==true){
		$sock=new sockets();
		$EnableMailman=$sock->GET_INFO("EnableMailman");
		$enable_mailman=Paragraphe_switch_img('{ENABLE_MAILMAN}','{ENABLE_MAILMAN_TEXT}','EnableMailman',$EnableMailman);
		$manage_mailman_lists=Paragraphe('folder-64-mailman.jpg','{manage_mailman_lists}','{manage_mailman_lists_text}','mailman.lists.php');	
		
		$html=$html . "<tr>
		<td valign='top'>
			$manage_mailman_lists
			$enable_mailman
		</td></tr>
		<tr><td valign='top'>  ".Paragraphe('folder-logs-64.jpeg','{events}','{events_text}','mailman.daemon.events.php') ."</td></tr>";
		}

		

		
$html=$html . "</table>
</td>
</tr>
</table>
";
$tpl=new template_users('MailMan',$html);
echo $tpl->web_page;
	
	
	
}
	