<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica-smtp-sync.inc');

$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["add"])){popup_add();exit;}
	if(isset($_GET["servername"])){popup_save();exit;}
	if(isset($_GET["sync-table"])){echo popup_table();exit;}
	if(isset($_GET["delete"])){popup_delete();exit;}
	js();
	
	
function js(){

	$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body('{smtp_sync_artica}');
	$title2=$tpl->_ENGINE_parse_body('{smtp_sync_artica_add}');
	
	$html="
		function smtp_sync_artica_start(){
			YahooWin2(600,'$page?popup=yes','$title');
		}
		
		function AddServerSyncArticaSMTP(){
			YahooWin3(450,'$page?add=yes','$title');
		
		}
		
		function SaveServerSyncArticaSMTP(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddRelayRecipientTableSave','yes');
		XHR.appendData('recipient',document.getElementById('recipient').value);
		XHR.sendAndLoad('$page', 'GET',X_PostfixDeleteRelayRecipient);
		
		}
		
		
		function RefreshList(){
			LoadAjax('sync-table','$page?sync-table=yes');
		}
	

	
var X_SaveServerSyncArticaSMTP= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		YahooWin3Hide();
		RefreshList();
	}		
function SaveServerSyncArticaSMTP(){
		var XHR = new XHRConnection();
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('port',document.getElementById('port').value);
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',document.getElementById('password').value);
		document.getElementById('smtpsyncid').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveServerSyncArticaSMTP);
		
	}		
	
function DelServerSyncArticaSMTP(server){
		var XHR = new XHRConnection();
		XHR.appendData('delete',server);
		XHR.sendAndLoad('$page', 'GET',X_SaveServerSyncArticaSMTP);
}
	
	smtp_sync_artica_start();
	";
	echo $html;
}


function popup(){
	$table=popup_table();
	$html="
	<p style='font-size:13px'>{smtp_sync_artica_explain}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='sync-table' style='width:100%;height:250px;overflow:auto;padding:3px;border:1px solid #CCCCCC'>$table</div></td>
		<td valign='top' style='padding-left:5px'>".Paragraphe("sender-relay-table.png","{smtp_sync_artica_add}","{smtp_sync_artica_add_text}","javascript:AddServerSyncArticaSMTP()")."</td>
	</tr>
	</table>
	
		";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_add(){
	$html="
	<p style='font-size:13px'>{smtp_sync_artica_add_text}</p>
	<div id='smtpsyncid'>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend>{servername}:</td>
		<td valign='top'>". Field_text("servername",null,'width:120px')."</td>
	<tr>
	<tr>
		<td valign='top' class=legend>{port} (SSL):</td>
		<td valign='top'>". Field_text("port","9000",'width:90px')."</td>
	<tr>	
	<tr>
		<td valign='top' class=legend>{username}:</td>
		<td valign='top'>". Field_text("username","admin",'width:120px')."</td>
	<tr>	
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td valign='top'>". Field_password("password","",'width:120px')."</td>
	<tr>	
	</table>
	<div style='width:100%;text-align:right'>". button("{add}","SaveServerSyncArticaSMTP();")."</div>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function popup_save(){
	$sync=new articaSMTPSync();
	$sync->Add($_GET["servername"],$_GET["port"],$_GET["username"],$_GET["password"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?sync-remote-smtp-artica=yes");
	}
	
function popup_delete(){
	$sync=new articaSMTPSync();
	$sync->Delete($_GET["delete"]);
	
	
}
function popup_table(){
	$sync=new articaSMTPSync();
	if(!is_array($sync->serverList)){return;}
	
	$html="<table style='width:100%'>";
	
	while (list ($server, $array) = each ($sync->serverList) ){
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:12px'>$server</strong></td>
			<td><strong style='font-size:12px'>{$array["PORT"]}</strong></td>
			<td><strong style='font-size:12px'>{$array["user"]}</strong></td>
			<td><strong style='font-size:12px'>{$array["users"]} {users}</strong></td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DelServerSyncArticaSMTP('$server:{$array["PORT"]}')")."</td>
		</tr>
		";	
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);		
	
}

?>