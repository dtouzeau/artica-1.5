<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["EnableKavICAPRemote"])){Save();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{kaspersky_antivirus_connector}");
	$html="YahooWin5('350','$page?popup=yes','$title')";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$EnableKavICAPRemote=$sock->GET_INFO("EnableKavICAPRemote");
	if(!is_numeric($EnableKavICAPRemote)){$EnableKavICAPRemote=0;}
	$KavICAPRemoteAddr=$sock->GET_INFO("KavICAPRemoteAddr");
	$KavICAPRemotePort=$sock->GET_INFO("KavICAPRemotePort");
	
	if(!is_numeric($KavICAPRemotePort)){$KavICAPRemotePort=1344;}
	$html="
	<div class=explain>{kavicap_remote_explain}</div>
	<div id='kavicap-sect'>
	<table style='width:99%' class=form>
	<tbody>
	<tr>
		<td class=legend>{enable}:</td>
		<td>". Field_checkbox("EnableKavICAPRemote", 1,$EnableKavICAPRemote,"EnableKavICAPRemoteCheck()")."</td>
	</tr>
	<tr>
		<td class=legend>{ipaddr}:</td>
		<td>". Field_text("KavICAPRemoteAddr",$KavICAPRemoteAddr,"font-size:14px;width:120px;padding:4px")."</td>
	</tR>
	<tr>
		<td class=legend>{listen_port}:</td>
		<td>". Field_text("KavICAPRemotePort",$KavICAPRemotePort,"font-size:14px;width:60px;padding:4px")."</td>
	</tR>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveKavICAPSet()")."</td>
	</tr>
	</tbody>
	</table>
	</div>
	<script>
	var x_SaveKavICAPSet= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		if(document.getElementById('admin_perso_tabs')){RefreshTab('admin_perso_tabs');}
		YahooWin5Hide();
	}	
	
	function SaveKavICAPSet(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableKavICAPRemote').checked){
			XHR.appendData('EnableKavICAPRemote',1);
		}else{
			XHR.appendData('EnableKavICAPRemote',0);
		}
		
		XHR.appendData('KavICAPRemoteAddr',document.getElementById('KavICAPRemoteAddr').value);
		XHR.appendData('KavICAPRemotePort',document.getElementById('KavICAPRemotePort').value);
		AnimateDiv('kavicap-sect');
		XHR.sendAndLoad('$page', 'POST',x_SaveKavICAPSet);		
		}
	
	function EnableKavICAPRemoteCheck(){
		document.getElementById('KavICAPRemoteAddr').disabled=true;
		document.getElementById('KavICAPRemotePort').disabled=true;
		if(document.getElementById('EnableKavICAPRemote').checked){
			document.getElementById('KavICAPRemoteAddr').disabled=false;
			document.getElementById('KavICAPRemotePort').disabled=false;		
		}
	
	}
	EnableKavICAPRemoteCheck();
</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function Save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableKavICAPRemote", $_POST["EnableKavICAPRemote"]);
	$sock->SET_INFO("KavICAPRemoteAddr", $_POST["KavICAPRemoteAddr"]);
	$sock->SET_INFO("KavICAPRemotePort", $_POST["KavICAPRemotePort"]);
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
}