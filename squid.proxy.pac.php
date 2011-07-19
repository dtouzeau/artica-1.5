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
	if(isset($_GET["SquidEnableProxyPac"])){Save();exit;}
	
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{proxy_pac}");
	$page=CurrentPageName();
	$html="
		function squid_proxy_pac_load(){
			YahooWin3('600','$page?popup=yes','$title');
		
		}
		
		var x_squid_proxy_pac_save= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			CacheOff();
			YahooWin3Hide();
			RefreshTab('squid_main_config');					
				
		}		
		
		function squid_proxy_pac_save(){
		 	var XHR = new XHRConnection();
			XHR.appendData('SquidEnableProxyPac',document.getElementById('SquidEnableProxyPac').value);
			XHR.appendData('listen_port',document.getElementById('listen_port').value);
			document.getElementById('proxypacid').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_pac_save);
		}
		
	squid_proxy_pac_load();";
	
	echo $html;
	
}


function popup(){
	
	$sock=new sockets();
	$SquidEnableProxyPac=$sock->GET_INFO("SquidEnableProxyPac");
	$listen_port=$sock->GET_INFO("SquidProxyPacPort");
	if($listen_port==null){$listen_port=8890;}
	$fiedld=Paragraphe_switch_img("{enable_squid_proxy_pac}","{enable_squid_proxy_pac_text}","SquidEnableProxyPac",$SquidEnableProxyPac,null,500);
	
	
	$html="
	<center style='width:100%' id='proxypacid'>
	<table>
	<tr>
	<td colspan=2>$fiedld</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>HTTP&nbsp;{listen_port}</td>
		<td>". Field_text("listen_port",$listen_port,"font-size:14px;width:90px;padding:5px")."</td>
	</tr>
	</table>
	</center>
	<div style='width:100%;text-align:right'>". button("{apply}","squid_proxy_pac_save()")."</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function Save(){
	$sock=new sockets();
	$sock->SET_INFO("SquidEnableProxyPac",$_GET["SquidEnableProxyPac"]);
	$sock->SET_INFO("SquidProxyPacPort",$_GET["listen_port"]);
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");	
	popup_add_proxy_list();
}

function popup_add_proxy_list(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));		
	if(!is_array($datas["PROXYS"])){
		$squid=new squidbee();
		$listend_port=$squid->listen_port;
		$tpc=new networking();
		while (list ($eth, $ip) = each ($tpc->array_TCP)){
		if($ip==null){continue;}
		$datas["PROXYS"][]="$ip:$listend_port";
		}	
	}else{
		return;
	}
	
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");
	
}




	
?>