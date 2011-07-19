<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.mysql.inc');
session_start();

$users=new usersMenus();

INDEX();


function INDEX(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];	
	$Graph=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?weekMessagesPerDay=$mail",250,250,"FFFFFF",true,$usermenus->ChartLicence);
	
$html=$Graph;	
$JS["JS"][]="js/user.quarantine.js";
$tpl=new template_users('{messages_performance}',$html,0,0,0,0,$JS);
echo $tpl->web_page;		
	
}