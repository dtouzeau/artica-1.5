<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');


if(isset($_GET["edit_mailbox"])){main_page();exit;}
if(isset($_GET["edit_mailbox_settings"])){edit_mailbox_settings();exit;}
if(isset($_GET["set_quota"])){set_quota();exit;}

function main_page(){
	$mailbox=new MailBoxes();
	$mailbox->Cyrus_load();
	
	$mailbox_infos=$mailbox->MailBoxes_get_infos($_GET["edit_mailbox"]);
	$server=$mailbox->global_config["_GLOBAL"]["mailboxes_server"];
	
	$_field_date="
		<tr>
	<td><strong>{mailbox_created}</strong></td>
	<td>$mailbox_infos->created</td>
	</tr>";
	
	$_field_maildir="
		<tr>
	<td><strong>{mailbox_path}</strong></td>
	<td>$mailbox_infos->maildir</td>
	</tr>
	";

	if($mailbox->Cyrus_Exists($_GET["edit_mailbox"])){
	$tblq=explode('/',$mailbox->Cyrus_hash[$_GET["edit_mailbox"]]["QUOTA"]);
	if($tblq[0]>0){$max_quota=$tblq[0]/1000;}else {$max_quota=0;}
	if($tblq[1]>0){$used_quota=round($tblq[1]/1000);}else {$used_quota=0;}
		
	$field_quota="<tr>
	<td><strong>{quota} {used}:&nbsp;$used_quota mb</strong>
	</td>
	<td><input type='text' id='max_quota' value='$max_quota' style='width:30px'> (mb)&nbsp;<i><span style='font-size:9px'>({quota_0})</span><i></td>
	</tr>";
	}	
	
	$rowspan=4;
	
	if($server=='cyrus'){
	$_field_date=null;
	$_field_maildir=null;
	$rowspan=4;
$button_set_quotas="
&nbsp;<input type='button' value='{bt_set_quota}' OnClick=\"javascript:set_quota('{$_GET["edit_mailbox"]}');\">";
	
	}
	
	$html="
	<input type='hidden' id='no_password' value='{ERR_NO_PASS}'>
	<input type='hidden' id='password_no_match' value='{ERR_NO_PASS_MATCH}'>	
	<input type='hidden' id='ERR_SET_QUOTA_SUCCESS'  value='{ERR_SET_QUOTA_SUCCESS}'>
	<FIELDSET style='width:550px'><LEGEND>
	{mailbox_title} {$_GET["edit_mailbox"]}</LEGEND>
	<table>
	<td style='padding:4px' width=1% valign='top'><img src='img/user-maibox.png'></td>
	<td valing='top'>
		<table>
		<tr>
		$field_date	
		$_field_maildir
		<tr>
		<td><strong>{mailbox_username}</strong></td>
		<td>{$_GET["edit_mailbox"]}</td>
		</tr>	
		$field_quota
		<tr>
		<td><strong>{mailbox_password}</strong></td>
		<td><input type=password id='password' value=''></td>
		</tr>
		<tr>
		<td><strong>{mailbox_confirm_password}</strong></td>
		<td><input type=password id='password2' value=''></td>
		</tr>	
		
		</table>
		<br>
		<table>
		<td>
		<table style='border:0px;width:100px;text-align:center;clear:all' align='center'>
			<tr>
				<td style='padding:0px;margin:0px;clear:both'>
					<input type='button' value='{bt_mailbox_edit}'  OnClick=\"javascript:edit_mailbox_settings('{$_GET["edit_mailbox"]}');\"></td>
					<td style='padding:0px;margin:0px;clear:both'$button_set_quotas</td>
				</tr>
		</table>
		</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
	
	</FIELDSET>
	
	";
	$tpl=new Templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
}


function edit_mailbox_settings(){
	$email=$_GET["edit_mailbox_settings"];
	$password=$_GET['password'];
	$quota=$_GET["max_quota"];
	$mail=new MailBoxes();
	$mail->MailBoxes_adduser($email,$password,$quota);
	}
	
function set_quota(){
	$email=$_GET["set_quota"];
	$quota=$_GET["max_quota"];
	$mail=new MailBoxes();
	$mail->MailBoxes_adduser($email,null,$quota);
}
?>