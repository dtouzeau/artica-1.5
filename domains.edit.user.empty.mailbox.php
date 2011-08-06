<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.cyrus.inc');	
	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	if(!GetRights_aliases()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["size_of_message"])){EMPTY_MBX();exit;}
	

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{empty_this_mailbox}");
	$confirm_mailbox_deletetion=$tpl->javascript_parse_text("{confirm_mailbox_deletetion}");
	
	
	$html="
	function empty_this_mailbox_load(){
		YahooWin3(550,'$page?userid={$_GET["userid"]}&popup=yes','$title');
	}
	
	function empty_this_mailbox_perform_check(e){if(checkEnter(e)){empty_this_mailbox_perform();}}
	
	var x_empty_this_mailbox_perform= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('container-users-tabs');
		document.getElementById('empty-div').innerHTML='<img src=img/poubelle-128.png>';
	}

function empty_this_mailbox_perform(){
	if(confirm('$confirm_mailbox_deletetion')){
		var XHR = new XHRConnection();
		XHR.appendData('size_of_message',document.getElementById('size_of_message').value);
		XHR.appendData('age_of_message',document.getElementById('age_of_message').value);
		XHR.appendData('userid','{$_GET["userid"]}');
		XHR.appendData('submailbox',document.getElementById('submailbox').value);
		
		
		document.getElementById('empty-div').innerHTML='<center style=width:100%><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_empty_this_mailbox_perform);	
	}
	
}
	empty_this_mailbox_load();
	";
	echo $html;
}

function EMPTY_MBX(){
	$userid=$_GET["userid"];
	$sock=new sockets();
	echo base64_decode($sock->getFrameWork("cmd.php?cyrus-empty-mailbox=yes&uid=$userid&size_of_message={$_GET["size_of_message"]}&age_of_message={$_GET["age_of_message"]}&submailbox={$_GET["submailbox"]}&by={$_SESSION["uid"]}"));
	
}


function popup(){
	$cyrus=new cyrus();
	
	$array=$cyrus->ListMailboxes($_GET["userid"]);
	$array[null]="{all}";
	unset($array["INBOX"]);
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><div id='empty-div'>
		<img src='img/poubelle-128.png'></td>
		</div>
	<td valign='top'>
			<div class=explain>{empty_this_mailbox_explain}</div>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend style='font-size:13px'>{size_of_message}:</td>
				<td style='font-size:14px'>". Field_text("size_of_message",null,"width:50px;font-size:14px;padding:3px",null,null,null,false,"empty_this_mailbox_perform_check(event)")."&nbsp;MB</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{age_of_message}:</td>
				<td style='font-size:14px'>". Field_text("age_of_message",30,"width:50px;font-size:14px;padding:3px",null,null,null,false,"empty_this_mailbox_perform_check(event)")."&nbsp;{days}</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{submailbox}:</td>
				<td style='font-size:14px'>". Field_array_Hash($array,"submailbox","Junk",null,null,0,"font-size:14px;padding:3px")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>
					". button("{delete}","empty_this_mailbox_perform()")."
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}



?>