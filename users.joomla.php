<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');

if(!$_GET["userid"]){die();}	
$users=new usersMenus();
if(!$users->AllowAddUsers){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();
}

if(isset($_GET["JoomlaGroup"])){joomla_save();exit;}
if(isset($_GET["p"])){joomla_index();exit;}


js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_JOOMLA}');
	$uid=$_GET["userid"];
	$html="
	
	function JoomlaUserLoad(){
		YahooWin6(400,'$page?userid=$uid&p=yes','$title');
	
	}
	
var x_JoomlaSaveUserPriv= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	JoomlaUserLoad();
	}

function JoomlaSaveUserPriv(){
	var XHR = new XHRConnection();
	XHR.appendData('JoomlaGroup',document.getElementById('JoomlaGroup').value);
	XHR.appendData('userid','$uid');	
	document.getElementById('JoomlaGroupdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_JoomlaSaveUserPriv);
	
}	
	
	JoomlaUserLoad();
	
	";
	
	echo $html;
	
	
}

function joomla_index(){
	
	$tpl=new templates();
	
	$table["Public Frontend"]="Public Frontend";
	$table["Registered"]="Registered";
	$table["Author"]="Author";
	$table["Editor"]="Editor";
	$table["Publisher"]="Publisher";
	$table["Public Backend"]="Public Backend";
	$table["Manager"]="Manager";
	$table["Administrator"]="Administrator";
	$table["Super Administrator"]="Super Administrator";
	
	$user=new user($_GET["userid"]);
	
	$field=Field_array_Hash($table,'JoomlaGroup',$user->JoomlaGroup);
	
	$html="<H1>$user->sn::{joomla_privileges}</H1>
	
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/64.joomla.png'></td>
	<td valign='top'>
	<div id='JoomlaGroupdiv'>
	<table style='width:99%' class=table_form>
	<tr>
		<td class=legend nowrap>{SAMBA_GROUP_PRIVILEGES}:</td>
		<td>$field</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:JoomlaSaveUserPriv(); value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</div>
	</td>
	</tr>
	</table>";
	
	
	echo $tpl->_ENGINE_parse_body($html,"domains.edit.user.php");
	
	
}

function joomla_save(){
	
	$user=new user($_GET["userid"]);
	$user->JoomlaGroup=$_GET["JoomlaGroup"];
	if($user->add_user()){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("Joomla: {success}\n");
	}
	
}


?>