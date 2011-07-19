<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.tcpip.inc');
	
	
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsSystemAdministrator==false){exit;}
	if(isset($_GET["EnableipV6"])){EnableipV6Save();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="IPv6";
	
	echo "YahooWin3(550,'$page?popup=yes','$title')";
	
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$EnableipV6=$sock->GET_INFO("EnableipV6");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	if(!is_numeric($EnableipV6)){$EnableipV6=0;}
	
	$html="
	<div id='EnableipV6Div'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/ipv6-128.png'></td>
		<td valign='top'>
			". Paragraphe_switch_img("{enable_ipv6}", "{enable_ipv6_text}","EnableipV6",$EnableipV6,null,350).
			"<hr>
			<div style='text-align:right'>". button("{apply}","EnableipV6Save()")."</div>
		</td>
	</tr>
	</table>
	</div>
	<script>
	
		var X_EnableipV6Save= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin3Hide();
		}			
	
	function EnableipV6Save(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}			
			var XHR = new XHRConnection();
			XHR.appendData('EnableipV6',document.getElementById('EnableipV6').value);
			AnimateDiv('EnableipV6Div');
			XHR.sendAndLoad('$page', 'GET',X_EnableipV6Save);
		}
	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function EnableipV6Save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableipV6",$_GET["EnableipV6"]);
	$sock->getFrameWork("network.php?ipv6=yes");
}
?>