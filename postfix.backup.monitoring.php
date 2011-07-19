<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once ("ressources/charts.php");
	
$usersmenus=new usersMenus();
if(($usersmenus->AsPostfixAdministrator==false)){echo "alert('No privileges')";exit;}
if(isset($_GET["start"])){ArticaBackupPage_start();exit;}


js();
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{artica_backup_storage}');
	$page=CurrentPageName();
	$html="
function ArticaBackupPage(){
   YahooWin5('500','$page?start=yes','$title');                
}

ArticaBackupPage();

";

echo $html;

}

function ArticaBackupPage_start(){
	$users=new usersMenus();
	$ini=new Bs_IniHandler(dirname(__FILE__).'/ressources/logs/artica-backup-size.ini');
	$backup=$ini->_params["artica_backup"]["backup"];
	$eml=$ini->_params["artica_backup"]["eml"];
	$quarantines=$ini->_params["artica_backup"]["quarantines"];
	if($backup==null){$backup=0;}
	if($eml==null){$eml=0;}
	if($quarantines==null){$quarantines=0;}
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?ArticaMailBackup=yes&email=$eml&backup=$backup&quanrantines=$quarantines&attachments={$ini->_params["artica_backup"]["attachments"]}",
	400,180,"",true,$users->ChartLicence);	
	$size=$ini->_params["artica_backup"]["original_messages"]+$ini->_params["artica_backup"]["attachments"];
		$size=FormatBytes($size);	
	
	$html="<h1>{artica_backup_storage}</H1>
	" . RoundedLightWhite("<p class=caption>{artica_backup_storage_explain}")."</p><br>
	<H3>{artica_backup_storage}:&nbsp;&laquo;&nbsp;$size&nbsp;&raquo;</H3>
	<div id=chart style='background-color:#FFFFFF;border:1px solid #CCCCCC;padding:5px;margin:5px'>$graph1</div>
";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


	


?>