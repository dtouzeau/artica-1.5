<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SquidActHasReverse"])){Save();exit;}
	
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squid_reverse_proxy}");
	$page=CurrentPageName();
	$html="
		function squid_reverse_proxy_load(){
			YahooWin3('600','$page?popup=yes','$title');
		
		}
		
		var x_squid_reverse_proxy_save= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			CacheOff();
			YahooWin3Hide();
			RefreshTab('squid_main_config');
		}		
		
		function squid_reverse_proxy_save(){
		 	var XHR = new XHRConnection();
			XHR.appendData('SquidActHasReverse',document.getElementById('SquidActHasReverse').value);
			XHR.appendData('listen_port',document.getElementById('listen_port').value);
			document.getElementById('reversid').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_squid_reverse_proxy_save);
		}
		
	squid_reverse_proxy_load();";
	
	echo $html;
	
}


function popup(){
	
	$sock=new sockets();
	$SquidActHasReverse=$sock->GET_INFO("SquidActHasReverse");
	$listen_port=$sock->GET_INFO("SquidActHasReverseListenPort");
	if($listen_port==null){$listen_port=80;}
	$fiedld=Paragraphe_switch_img("{enable_squid_reverse}","{enable_squid_reverse_text}","SquidActHasReverse",$SquidActHasReverse,null,500);
	
	$html="
	<center style='width:100%' id='reversid'>
	<table>
	<tr>
	<td colspan=2>$fiedld</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}</td>
		<td>". Field_text("listen_port",$listen_port,"font-size:14px;width:90px;padding:5px")."</td>
	</tr>
	</table>
	</center>
	<div style='width:100%;text-align:right'>". button("{apply}","squid_reverse_proxy_save()")."</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function Save(){
	$sock=new sockets();
	$sock->SET_INFO("SquidActHasReverse",$_GET["SquidActHasReverse"]);
	$sock->SET_INFO("SquidActHasReverseListenPort",$_GET["listen_port"]);
	$sock->getFrameWork("cmd.php?squidnewbee=yes");	
}


	
?>