<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.backup.emails.inc');
include_once('ressources/class.getlive.inc');

if(isset($_GET["direction"])){query();exit;}
if(isset($_GET["ShowBackupMail"])){ShowBackupMail();exit;}
if(isset($_GET["msgbodyif"])){ShowBackupMailInside();exit;}
if(isset($_GET["enabled"])){SaveAccount();exit;}

main_page();


function main_page(){
	
	$html="
	<table style='width:650px' align=center>
<tr>
<td valign='top'>
	<img src='img/bg_hotmail.png' style='margin:5px;padding:5px;border:1px solid #CCCCCC'>
</td>
<td valign='top'>
<p class=caption>{getlive_about}</p>
</td>
</tr>
<tr>
	<td colspan=2>
	<div id='main_config_postfix'>
		</div>
	</td>
</tr>
		
</table>
<br>".liveForm();
	
$tpl=new template_users('{hotmail}',$html,$_SESSION,0,0,0,$cfg);
echo $tpl->web_page;	
	
}

function liveForm(){
	$uid=$_SESSION["uid"];
	$get=new getlive($uid);
	$array=$get->ParseConfig($uid);
	$page=CurrentPageName();
	
	$html="<div style='background-image:url(img/bg_form-1.png);padding:10px;width:550px;border:1px solid #CCCCCC;margin-left:100px;'>
	<form name='ffm1'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{enable}:</td>
		<td>" . Field_numeric_checkbox_img('enabled',$array["enabled"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
	</tr	
	<tr>
		<td class=legend nowrap>{username}:</td>
		<td>" . Field_text('UserName',$array["UserName"],'width:100px')."</td>
		<td>{username_text}</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>" . Field_text('Password',$array["Password"],'width:100px')."</td>
		<td>{password_text}</td>
	</tr>	
	<tr>
		<td class=legend valign='top'>{Domain}:</td>
		<td valign='top'>" . Field_text('Domain',$array["Domain"],'width:100px')."</td>
		<td valign='top'>{Domain_text}</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{Delete_messages}:</td>
		<td valign='top'>" . Field_yesno_checkbox('Delete',$array["Delete"])."</td>
		<td valign='top'>{Delete_messages_text}</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{MarkRead}:</td>
		<td valign='top'>" . Field_yesno_checkbox('MarkRead',$array["MarkRead"])."</td>
		<td valign='top'>{MarkRead_text}</td>
	</tr>		
	<tr>
		<td colspan=3 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true);\" value='{submit}&nbsp;&raquo;'>
		</td>
	</tr>	
	</table>
	</div>
	
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function SaveAccount(){
	
	$uid=$_SESSION["uid"];
	$get=new getlive($uid);
	while (list ($num, $ligne) = each ($_GET)){
		$get->main_conf[$uid][$num]=$ligne;
	}
	
	$user=new user($uid);
	$get->main_conf[$uid]["user"]=$user->mail;
	
	$get->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}