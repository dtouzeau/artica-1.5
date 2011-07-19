<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.kas-filter.inc');


$priv=new usersMenus();
if(isset($_GET["detection_rate"])){SaveSettings();exit;}

INDEX();


function INDEX(){
	$priv=new usersMenus();
	
	
	if(isset($_GET["ou"])){
		if($priv->AllowChangeKas==false){header('location:users.index.php');exit;}
		$ou=$_GET["ou"];
		$kas=new kas_user(null,$_GET["ou"]);
		$hidden="<input type='hidden' name='ou' value='$ou'>";
		$title="$ou: {org_antispam_rules}";
	}else{
		if($priv->AllowChangeAntiSpamSettings==false){header('location:users.index.php');exit;}
		$kas=new kas_user($_SESSION["uid"]);
		$title="{antispam_user_rules}";
	}
	
	for($i=1;$i<101;$i++){
		$array[$i]="$i %";
	}
	$second_rate=Field_array_Hash($array,"detection_rate1",$kas->mail_array["second_rate"]);
	$detection_rate=Field_array_Hash($array,"detection_rate",$kas->mail_array["detection_rate"]);
$page=CurrentPageName();	
$html="

<form name='FFM1'>
$hidden
<table style='width:600px' align=center>
<tr>
	<td width=1% valign='top'><img src='img/caterpillarkas.jpg'></td>
	<td valign='top'>
		<div class='caption'>{antispam_user_rules_text}</div>
		<div style='font-size:12px'>{spam_rules_intro}</div>
		
	</td>
</tr>
<td colspan=2>
<strong>{prepend_text}:</strong>" . Field_text('prepend_text',$kas->mail_array["prepend_text"])	."<hr>
<table style='width:100%'>
<tr>
<td>
<tr style='background-color:#27573F;color:white'>
	<td >{spam_detection_rate}</td>
	<td valign='top' nowrap><strong>{action_quarantine}</strong></td>
	<td valign='top' nowrap><strong>{action_prepend}</strong></td>
	<td valign='top' nowrap><strong>{action_killmail}</strong></td>
</tr>
<tr>
	<td class='bottom' align='right'>$detection_rate</td>
	<td valign='top' align='center' width=1% class=bottom>" . Field_numeric_checkbox_img('action_quarantine',$kas->mail_array["action_quarantine"],'{enable_disable}')."&nbsp;</td>
	<td valign='top' align='center' width=1% class=bottom>" . Field_numeric_checkbox_img('action_prepend',$kas->mail_array["action_prepend"],'{enable_disable}')."&nbsp;</td>
	<td valign='top' align='center' width=1% class=bottom>" . Field_numeric_checkbox_img('action_killmail',$kas->mail_array["action_killmail"],'{enable_disable}')."&nbsp;</td>
</tr>
<tr>
	<td class='bottom' align='right'>$second_rate</td>
	<td valign='top' align='center' width=1% class=bottom>" . Field_numeric_checkbox_img('second_quarantine',$kas->mail_array["second_quarantine"],'{enable_disable}')."&nbsp;</td>
	<td valign='top' align='center' width=1% class=bottom>" . Field_numeric_checkbox_img('second_prepend',$kas->mail_array["second_prepend"],'{enable_disable}')."&nbsp;</td>
	<td valign='top' align='center' width=1% class=bottom>" . Field_numeric_checkbox_img('second_killmail',$kas->mail_array["second_killmail"],'{enable_disable}')."&nbsp;</td>
</tr>
<tr><td colspan=4 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFM1','$page',true);\" value='{edit}&nbsp;&raquo;'></td></tr>
</table>
</td>
</tr>
</table>";
	
	

$tpl=new template_users($title,$html);
echo $tpl->web_page;
}
function SaveSettings(){
	$priv=new usersMenus();
	if(isset($_GET["ou"])){
		if($priv->AllowChangeKas==false){header('location:users.index.php');exit;}
		$kas=new kas_user(null,$_GET["ou"]);
	}else{
		if($priv->AllowChangeAntiSpamSettings==false){header('location:users.index.php');exit;}
		$kas=new kas_user($_SESSION["uid"]);
	}
	$tpl=new templates();
	while (list ($num, $val) = each ($_GET) ){
		$kas->mail_array[$num]=$val;
	}
	if(!$kas->SaveConf()){
		echo $tpl->_ENGINE_parse_body('{failed}');
	}else{echo $tpl->_ENGINE_parse_body('{success}');}
	
}