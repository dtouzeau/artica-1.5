<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.sieve.inc');
session_start();
if(!isset($_SESSION["uid"])){header('location:logon.php');exit;}

SieveHead();
function SieveHead(){
	$sieve=new clSieve($_SESSION["uid"]);
	$userid=$_SESSION["uid"];
	$usersMenus=new usersMenus();
	
$html="
<input type='hidden' id='prompt_add_rule_name' value='{prompt_add_rule_name}'>
<table style='width:600px' align=center>
<tr>
<td valign='top'>
<img src='img/bg_mailbox_rules.jpg'>
</td>
<td valign='top'>
<table>";
$html=$html . "<tr>
	<td valign='top'> . ".Paragraphe('folder-mailrules-64.jpg','{content_rules}','{content_rules_text}','user.content.rules.php') ."</td>
	</tr>";
if($usersMenus->cyrus_imapd_installed==true){
$html=$html . "<tr>
	<td valign='top'> . ".Paragraphe('folder-mailrules-64.jpg','{sieve_rules}','{sieve_rules_text}','javascript:SieveLoadRuleUSer("'.$userid.'")') ."</td>
	</tr>
	<tr>
	<td valign='top' >".Paragraphe('folder-fetchmail-plus-64.jpg','{add_new_sieve_rule}','{add_new_sieverule_text}','javascript:SieveAddRuleUser("'.$userid.'")') ."</td>
	</tr>
";}

$html=$html . "
<tr>
<td valign='top' >".Paragraphe('folder-white-64.png','{AllowEditAsWbl}','{white list explain}','users.aswb.php') ."</td>
</tr>";



$html=$html . "
</table>
</td>
</tr>
</table>
";
$cfg["JS"][]='js/artica_sieve.js';
$tpl=new template_users('{manage_your_mailbox_rules}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;
	
	
	
}	
	
	


        
?>
