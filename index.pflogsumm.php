<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.pflogsumm.inc');
	include_once('ressources/class.users.menus.inc');


	if(isset($_GET["popup"])){pflogsumm_popup();exit;}
	if(isset($_GET["min"])){pflogsumm_save();exit;}
	if(isset($_GET["recipient"])){pflogsumm_recipient_add();exit;}
	if(isset($_GET["del-recipient"])){pflogsumm_recipient_del();exit;}
	if(isset($_GET["send-report"])){pflogsumm_recipient_send();exit;}
	if(isset($_GET["use_send_mail"])){pflogsumm_save();exit;}

	pflogsumm_js();


	
function pflogsumm_recipient_send(){
	
$tpl=new templates();
$send_report_now_text=$tpl->_ENGINE_parse_body('{send_report_now_text}');
$sock=new sockets();
$sock->getfile("pflogsummSend");
echo $send_report_now_text;
}


function pflogsumm_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_PFLOGSUMM}');
	$ask=$tpl->_ENGINE_parse_body('{PFLOGSUMM_GIVE_EMAIL_ADDRESS}');
	
	
	$html="
		function pflogsummLoad(){
			YahooWin3(600,'$page?popup=yes','$title');
		
		}
		
	var x_pflogsummSave= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
    	pflogsummLoad();
    }    
    
    
    var x_pflogsummSend= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
    	pflogsummLoad();
    }    
		
		
		function pflogsummSave(){
			var XHR = new XHRConnection();
			XHR.appendData('subject',document.getElementById('subject').value);
			XHR.appendData('sender',document.getElementById('sender').value);
			XHR.appendData('hour',document.getElementById('hour').value);
			XHR.appendData('min',document.getElementById('min').value);
			document.getElementById('PFLOGSUMM_DIV').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'GET',x_pflogsummSave);			
		}
		
		function pflogsummAddRecipient(){
			var recipient=prompt('$ask');
			
			var XHR = new XHRConnection();
			if(recipient.length>0){
				document.getElementById('PFLOGSUMM_DIV').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.appendData('recipient',recipient);
				XHR.sendAndLoad('$page', 'GET',x_pflogsummSave);
			}	
		}	

		function pflogsummDelRecipient(num){
				var XHR = new XHRConnection();
				document.getElementById('PFLOGSUMM_DIV').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.appendData('del-recipient',num);
				XHR.sendAndLoad('$page', 'GET',x_pflogsummSave);		
		}
		
		function pflogsummSend(){
			var XHR = new XHRConnection();
			document.getElementById('PFLOGSUMM_DIV').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.appendData('send-report','yes');
			XHR.sendAndLoad('$page', 'GET',x_pflogsummSend);	
		}
		
		function ChangeSMTPEngine(){
			var use_send_mail=0;
			document.getElementById('serverSettings').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			if(document.getElementById('use_send_mail').checked){
				use_send_mail=1;
			}else{
				use_send_mail=0;
			}
			
			var XHR = new XHRConnection();
			XHR.appendData('use_send_mail',use_send_mail);
			XHR.sendAndLoad('$page', 'GET',x_pflogsummSave);
		}
		
		
		
		pflogsummLoad();
		
	
	";
	
	echo $html;
	
}


function pflogsumm_save(){
	$pflogsumm=new pflogsumm();
	while (list ($num, $ligne) = each ($_GET) ){
		$pflogsumm->main_array["SETTINGS"]["$num"]=$ligne;
		
	}
	
	$pflogsumm->Save();
	
}

function pflogsumm_recipient_del(){
	$num=$_GET["del-recipient"];
	$pflogsumm=new pflogsumm();
	$tbl=explode(",",$pflogsumm->main_array["SETTINGS"]["recipients"]);
	unset($tbl[$num]);

if(is_array($tbl)){
		$pflogsumm->main_array["SETTINGS"]["recipients"]=implode(",",$tbl);
	}else{
		unset($pflogsumm->main_array["SETTINGS"]["recipients"]);
	}	
	$pflogsumm->Save();
}


function ServerSettings(){
	$pflogsumm=new pflogsumm();
	
	
$html="
<table style='width:100%' class=table_form>
				<td class=legend>{relay_address}:</td>
				<td><strong>$pflogsumm->smtp_server_name:$pflogsumm->smtp_server_port</td>
			</tr>
			<tr>
				<td class=legend>TLS:</td>
				<td><strong>$pflogsumm->tls_enabled</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><input type='button' OnClick=\"javascript:Loadjs('artica.settings.php?ajax-notif=yes');\" value='{parameters}&nbsp;&raquo;'></td>
			</tr>
			</table>";	
			
if($pflogsumm->main_array["SETTINGS"]["use_send_mail"]==1){return null;}
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}


function pflogsumm_popup(){
	
	
	for($i=0;$i<24;$i++){
		if($i<10){$hu="0$i";}else{$hu=$i;}
		$H[$hu]=$hu;
		
	}
	
	for($i=0;$i<60;$i++){
		if($i<10){$hu="0$i";}else{$hu=$i;}
		$M[$hu]=$hu;
		
	}	
	
	$pflogsumm=new pflogsumm();
	
	if($pflogsumm->notif_enabled==0){
		$warning="
		
		<div style='float:right;margin:3px'>
		<input type='button' OnClick=\"javascript:Loadjs('artica.settings.php?ajax-notif=yes');\" value='{parameters}&nbsp;&raquo;'>
		</div>
		
		<p style='font-weight:bold;color:red;font-size:12px'>{ERROR_NOTIFICATION_NOT_ENABLED}</p>";
		
	}
	
	$ServerSettings=ServerSettings();
	
	$hour=Field_array_Hash($H,'hour',$pflogsumm->main_array["SETTINGS"]["hour"],null);
	$min=Field_array_Hash($M,'min',$pflogsumm->main_array["SETTINGS"]["min"],null);
	$pflogsumm_recipients_list=pflogsumm_recipients_list();
	$html="
	<H1>{APP_PFLOGSUMM}</h1>
	<div id='PFLOGSUMM_DIV'>
	<div style='float:right;margin:3px;'>
		<input type='button' OnClick=\"javascript:pflogsummSend();\" value='{send_report_now}&nbsp;&raquo;'>
	</div>
	<p class=caption>{APP_PFLOGSUMM_TEXT}</p>
	$warning
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{PFLOGSUMM_SEND_REPORT_EACH}:</td>
		<td>
			<table style='width:10%'>
				<tr>
					<td>$hour</td>
					<td>:</td>
					<td>$min</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class=legend>{subject}:</td>
		<td>" . Field_text('subject',$pflogsumm->main_array["SETTINGS"]["subject"],'width:310px')."</td>
	</tr>
	<tr>
		<td class=legend>{sender}:</td>
		<td>" . Field_text('sender',$pflogsumm->main_array["SETTINGS"]["sender"],'width:210px')."</td>
	</tr>	
	<tr>
		<td class=legend>{recipient}:</td>
		<td><input type='button' OnClick=\"javascript:pflogsummAddRecipient();\" value='{PFLOGSUMM_ADD_RECIPIENT}&nbsp;&raquo;'></td>
	</tr>		
	<tr>
		<td colspan=2><hr></td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:pflogsummSave();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	
	
	</table>
	<br>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<table style='width:100%' class=table_form>
			<tr>
				<td class=legend>{use_send_mail}:</td>
				<td>" . Field_checkbox('use_send_mail',1,$pflogsumm->main_array["SETTINGS"]["use_send_mail"],"ChangeSMTPEngine()")."</td>
			
			<tr>
			</table>
			<br>
			<div id='serverSettings'>
				$ServerSettings
			</div>
		</td>
		<td valign='top'>
			$pflogsumm_recipients_list
		</td>
		</tr>
		</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function pflogsumm_recipients_list(){
	$pflogsumm=new pflogsumm();
	$tbl=explode(",",$pflogsumm->main_array["SETTINGS"]["recipients"]);
	if(!is_array($tbl)){return null;}
	
	$html="
	<H3 style='text-transform:capitalize'>{recipients}</H3>
	<table style='width:100%' class=table_form>";
	
while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$html=$html . "
		<tr . " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>
				<strong style='font-size:13px'><code>$ligne</code></strong>
			</td>
			<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"pflogsummDelRecipient($num)")."</td>
		</tr>
		
		";
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}



function pflogsumm_recipient_add(){
	$pflogsumm=new pflogsumm();
	$tbl=explode(",",$pflogsumm->main_array["SETTINGS"]["recipients"]);
	$tbl[]=$_GET["recipient"];
	if(is_array($tbl)){
		$pflogsumm->main_array["SETTINGS"]["recipients"]=implode(",",$tbl);
	}else{
		unset($pflogsumm->main_array["SETTINGS"]["recipients"]);
	}
	
	$pflogsumm->Save();
	
	
}






?>