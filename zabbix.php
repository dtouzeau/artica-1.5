<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableZabbixServer"])){Save();exit;}
	
	js();
	
	
function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_ZABIX_SERVER}');
	

	
$html="
	function zabbix_start(){
			YahooWin5(650,'$page?popup=yes','$title');
		
		}
		
	var x_SaveZabbixConf= function (obj) {
		var response=obj.responseText;
		zabbix_start();
		
		}			
		
	function SaveZabbixConf(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableZabbixServer',document.getElementById('EnableZabbixServer').value);
		XHR.appendData('EnableZabbixAgent',document.getElementById('EnableZabbixAgent').value);
		XHR.appendData('ZabbixAgentServerIP',document.getElementById('ZabbixAgentServerIP').value);
		
		document.getElementById('zabbix').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/ajax-loader.gif\"></center>';
		
		XHR.sendAndLoad('$page', 'GET', x_SaveZabbixConf);		
	
	}
	zabbix_start();
	";
	echo $html;	
	}
	
	
function Save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableZabbixServer",$_GET["EnableZabbixServer"]);
	$sock->SET_INFO("EnableZabbixAgent",$_GET["EnableZabbixAgent"]);
	$sock->SET_INFO("ZabbixAgentServerIP",$_GET["ZabbixAgentServerIP"]);
	$sock->getFrameWork("cmd.php?zabbix-restart=yes");
}
	
function popup(){
	
	$sock=new sockets();
	$EnableZabbixServer=$sock->GET_INFO("EnableZabbixServer");
	$EnableZabbixAgent=$sock->GET_INFO("EnableZabbixAgent");
	$ZabbixAgentServerIP=$sock->GET_INFO("ZabbixAgentServerIP");
	if($ZabbixAgentServerIP==null){$ZabbixAgentServerIP="127.0.0.1";}
	if($EnableZabbixServer==null){$EnableZabbixServer=1;}
	if($EnableZabbixAgent==null){$EnableZabbixAgent=1;}	
	
	
	$EnableZabbixServerField=Paragraphe_switch_img("{EnableZabbixServer}","{EnableZabbixServer_text}","EnableZabbixServer",$EnableZabbixServer);
	$EnableZabbixAgentField=Paragraphe_switch_img("{EnableZabbixAgent}","{EnableZabbixAgent_text}","EnableZabbixAgent",$EnableZabbixAgent);
	$zabbix=Paragraphe("zabbix_med.gif",'{APP_ZABIX_SERVER_CONSOLE}','{APP_ZABIX_SERVER_TEXT}',"javascript:s_PopUp('zabbix',1024,768);","{APP_ZABIX_SERVER_TEXT}",300,76,1);

	$html="
	<div id='zabbix'>
	<table style=width:100%>
	<tr>
		<td valign='top'>
			$EnableZabbixServerField<br>$EnableZabbixAgentField<br>
		</td>
		<td valign='top'>$zabbix<hr>
			<table style='width:100%'>
				<tr>
					<td class=legend>{ZabbixAgentServerIP}:</td>
					<td class=legend>". Field_text("ZabbixAgentServerIP",$ZabbixAgentServerIP,"width:120px")."</td>
				</tr>
			</table>
		</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><hr>
				". button("{edit}","SaveZabbixConf()")."</td>
			</tr>
	</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

	
?>