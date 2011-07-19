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
	if(isset($_GET["popup-add-proxy"])){popup_add_proxy();exit;}
	if(isset($_GET["proxy_addr"])){popup_add_proxy_save();exit;}
	if(isset($_GET["proxylist"])){popup_add_proxy_list();exit;}
	if(isset($_GET["del_proxy_addr"])){popup_del_proxy_list();exit;}
	if(isset($_GET["view"])){popup_script();exit;}
	
	if(isset($_GET["final_proxy_addr"])){popup_add_final_proxy_save();exit;}
	if(isset($_GET["popup-final-proxy"])){popup_add_final_proxy();exit;}
	if(isset($_GET["del_proxy_final_addr"])){popup_del_final_proxy_list();exit;}
	
	
	if(isset($_GET["localHostOrDomainIs"])){popup_localHostOrDomainIs();exit;}
	if(isset($_GET["localHostOrDomainIs-add"])){popup_localHostOrDomainIs_add();exit;}
	if(isset($_GET["localHostOrDomainIs-list"])){popup_localHostOrDomainIs_list();exit;}
	if(isset($_GET["localHostOrDomainIs-del"])){popup_localHostOrDomainIs_del();exit;}
	
	if(isset($_GET["isInNet"])){popup_isInNet();exit;}
	if(isset($_GET["isInNet-add"])){popup_isInNet_add();exit;}
	if(isset($_GET["isInNet-list"])){popup_isInNet_list();exit;}
	if(isset($_GET["isInNet-del"])){popup_isInNet_del();exit;}
	if(isset($_GET["isInNet-del"])){popup_isInNet_del();exit;}
	
	if(isset($_GET["add_condition_plus"])){popup_add_condition_add();exit;}
	if(isset($_GET["add_condition"])){popup_add_condition();exit;}
	if(isset($_GET["DeleteCondition"])){popup_del_condition();exit;}
	
	if(isset($_POST["ProxyPacRemoveProxyListAtTheEnd"])){ProxyPacRemoveProxyListAtTheEnd();exit;}
	
	
	
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{proxy_pac_rules}");
	$add_a_proxy=$tpl->_ENGINE_parse_body("{add_a_proxy}");
	$add_localHostOrDomainIs=$tpl->_ENGINE_parse_body("{add_localHostOrDomainIs}");
	$add_condition=$tpl->_ENGINE_parse_body("{add_condition}");
	$isInNetProxy=$tpl->_ENGINE_parse_body("{isInNetProxy}");
	$view_script=$tpl->_ENGINE_parse_body("{view_script}");
	$final_proxy=$tpl->_ENGINE_parse_body("{final_proxy}");
	$page=CurrentPageName();
	$html="
		function squid_proxy_pac_rules_load(){
			YahooWin3('690','$page?popup=yes','$title');
		
		}
		
		
		function ProxysPacList(){
			LoadAjax('proxylist','$page?proxylist=yes');
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
		
		function squid_proxy_pacc_add_proxy(){
			YahooWin4('500','$page?popup-add-proxy=yes','$add_a_proxy');
		}
		
		function localHostOrDomainIs(){
			YahooWin4('500','$page?localHostOrDomainIs=yes','$add_localHostOrDomainIs');
		}
		
		function isInNet(){
			YahooWin4('500','$page?isInNet=yes','$isInNetProxy');
		}
		
		function ViewProxyPac(){
			YahooWin5('700','$page?view=yes','$view_script');
		}

		function AddProxycondition(ID){
			YahooWin4('500','$page?add_condition='+ID,'$add_condition');
		}
		
		function FinalProxyPacList(){
			YahooWin4('500','$page?popup-final-proxy=yes','$final_proxy');
		}
		
		var x_squid_proxy_pacc_add_proxy_perform= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin4Hide();
			ProxysPacList();
		}
		
		
	function DeleteCondition(num,key){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteCondition',num);
			XHR.appendData('DeleteConditionKey',key);
			document.getElementById('proxylist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_pacc_add_proxy_perform);
		}		

		
		
		
		
		function squid_proxy_pacc_add_proxy_perform(){
			var XHR = new XHRConnection();
			XHR.appendData('proxy_addr',document.getElementById('proxy_addr').value);
			XHR.appendData('listen_port',document.getElementById('proxy_port').value);
			document.getElementById('popup_add_proxy_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_pacc_add_proxy_perform);
		}
		
		function squid_proxy_pacc_add_finalproxy_perform(){
			var XHR = new XHRConnection();
			XHR.appendData('final_proxy_addr',document.getElementById('proxy_addr').value);
			XHR.appendData('listen_port',document.getElementById('proxy_port').value);
			document.getElementById('popup_add_proxy_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_pacc_add_proxy_perform);		
		}
		
		function squid_proxy_pacc_del_proxy(num){
			var XHR = new XHRConnection();
			XHR.appendData('del_proxy_addr',num);
			document.getElementById('proxylist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_pacc_add_proxy_perform);
		}
		
		function squid_proxy_pacc_del_final_proxy(num){
			var XHR = new XHRConnection();
			XHR.appendData('del_proxy_final_addr',num);
			document.getElementById('proxylist').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_squid_proxy_pacc_add_proxy_perform);
		}		
		
		
		function localHostOrDomainIsKeyPress(e){if(checkEnter(e)){localHostOrDomainIsAdd();}}
		function isInNetKeyPress(e){if(checkEnter(e)){isInNetAdd();}}
		
		
		
		var  x_localHostOrDomainIsAdd= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin4Hide();
			localHostOrDomainIsList();
		}
		
		function localHostOrDomainIsList(){
			LoadAjax('localHostOrDomainIsList','$page?localHostOrDomainIs-list=yes');
		}		
	
	function localHostOrDomainIsAdd(){
			var XHR = new XHRConnection();
			var value=document.getElementById('localHostOrDomainIs').value;
			if(document.getElementById('dnsDomainIs').checked){value='dnsDomainIs:'+value;}
			XHR.appendData('localHostOrDomainIs-add',value);
			document.getElementById('popup_localHostOrDomainIs_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_localHostOrDomainIsAdd);
		}

		function localHostOrDomainIsDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('localHostOrDomainIs-del',num);
			document.getElementById('localHostOrDomainIsList').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_localHostOrDomainIsAdd);		
		}
		
		
	var  x_isInNetAdd= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin4Hide();
			isInNetList();
		}		
		
	function isInNetAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('isInNet-add',document.getElementById('tcp_addr').value);
			XHR.appendData('isInNet-mask',document.getElementById('mask').value);
			document.getElementById('isInNet_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_isInNetAdd);
		}
		
	function isInNetDel(num){
			var XHR = new XHRConnection();
			XHR.appendData('isInNet-del',num);
			document.getElementById('isInNetList').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_isInNetAdd);		
	}

	function isInNetList(){
		LoadAjax('isInNetList','$page?isInNet-list=yes');
	}

	squid_proxy_pac_rules_load();";
	
	echo $html;
	
}	


function popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	$ProxyPacRemoveProxyListAtTheEnd=$sock->GET_INFO("ProxyPacRemoveProxyListAtTheEnd");
	$listen_port=$sock->GET_INFO("SquidProxyPacPort");
	if($datas["DisableLocalNetwork"]==null){
		$datas["DisableLocalNetwork"]=1;
	}
	
	$ip=new networking();
	if(is_array($ip->array_TCP)){
		while (list ($eth, $tcip) = each ($ip->array_TCP)){
			if($tcip==null){continue;}
			$uris=$uris."<li style='font-size:14px'>http://$tcip:$listen_port/proxy.pac</li>";
			
		}
		
	}
	
	

	$isiniNet="	<table style='width:100%'>
	<tr>
	<td class=legend font-size:13px'>{do_not_use_proxy_for_local_net}:</td>
	<td>". Field_checkbox("DisableLocalNetwork",1,$datas["DisableLocalNetwork"])."</td>
	</tr>
	</table>
	<hr>";
	
	
	$html="
	<H3>{uri_add_in_browser}:</H3>
	<ul>$uris</ul>
	<center>
	<table style='width:250px;margin:5px'>
	<tr>
		<td width=1% nowrap>". button("{add_a_proxy}","squid_proxy_pacc_add_proxy()")."</td>
		<tD width=1% nowrap>". button("{add_localHostOrDomainIs}","localHostOrDomainIs()")."</td>
		<tD width=1% nowrap>". button("{isInNetProxy}","isInNet()")."</td>
		<tD width=1% nowrap>". button("{final_proxy}","FinalProxyPacList()")."</td>
		<tD width=1% nowrap>". button("{view_script}","ViewProxyPac()")."</td>
	</tr>
	</table>
	</center>
	<div style='height:550px;overflow:auto;width:100%'>
	<div style='width:98%;height:220px;padding:3px;border:1px solid #CCCCCC;overflow:auto' id='proxylist'></div><br>
	<div style='width:98%;height:120px;padding:3px;border:1px solid #CCCCCC;overflow:auto' id='localHostOrDomainIsList'></div><br>
	<div style='width:98%;height:120px;padding:3px;border:1px solid #CCCCCC;overflow:auto' id='isInNetList'></div><br>
	</div>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{RemoveProxyListAtTheEnd}:</td>
		<td width=1%>". Field_checkbox("ProxyPacRemoveProxyListAtTheEnd",1,$ProxyPacRemoveProxyListAtTheEnd,"ProxyPacRemoveProxyListAtTheEndCheck()")."</td>
	</tr>
	</table>
	
	<script>

		
		function ProxyPacRemoveProxyListAtTheEndCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('ProxyPacRemoveProxyListAtTheEnd').checked){
				XHR.appendData('ProxyPacRemoveProxyListAtTheEnd',1);
			}else{
				XHR.appendData('ProxyPacRemoveProxyListAtTheEnd',0);
			}
			XHR.sendAndLoad('$page', 'POST');	
			
		}
		
		ProxysPacList();
		localHostOrDomainIsList();
		isInNetList();		
		
	</script>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function ProxyPacRemoveProxyListAtTheEnd(){
	$sock=new sockets();
	$sock->SET_INFO("ProxyPacRemoveProxyListAtTheEnd",$_POST["ProxyPacRemoveProxyListAtTheEnd"]);
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");
}

function popup_add_proxy_save(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));	
	$datas["PROXYS"][]="{$_GET["proxy_addr"]}:{$_GET["listen_port"]}";
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");
	
}

function popup_add_final_proxy_save(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	$datas["FINAL_PROXY"][]="{$_GET["final_proxy_addr"]}:{$_GET["listen_port"]}";	
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");	
}

function popup_del_proxy_list(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	unset($datas["PROXYS"][$_GET["del_proxy_addr"]]);
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");
}

function popup_del_final_proxy_list(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	unset($datas["FINAL_PROXY"][$_GET["del_proxy_final_addr"]]);
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");	
}

function popup_localHostOrDomainIs_add(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));	
	$datas["localHostOrDomainIs"][]=$_GET["localHostOrDomainIs-add"];
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");	
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");
}
function popup_localHostOrDomainIs_del(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	unset($datas["localHostOrDomainIs"][$_GET["localHostOrDomainIs-del"]]);
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");	
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");
}

function popup_isInNet_add(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	$datas["isInNet"][]=array($_GET["isInNet-add"],$_GET["isInNet-mask"]);
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");		
}
function popup_isInNet_del(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	unset($datas["isInNet"][$_GET["isInNet-del"]]);
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");	
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");	
}

function popup_localHostOrDomainIs_list(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));		
	if(!is_array($datas["localHostOrDomainIs"])){return null;}	

	$html="<table style='width:100%'>
	<tr>
		<th colspan=3>{hosts_without_proxy}</th>
	</tr>
	";
	
	while (list ($num, $uri) = each ($datas["localHostOrDomainIs"])){
		
		if(preg_match("#dnsDomainIs:(.+?)$#",trim($uri),$re)){
			$uri=$re[1]." <i>{IE8_compatibility}</i>";
		}
		
		$html=$html."<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:14px'>$uri</td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","localHostOrDomainIsDel('$num')")."</td>
		</tr>";
		
		
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}


function popup_add_proxy_list(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));		
	if(!is_array($datas["PROXYS"])){return null;}
	
	$html="<table style='width:100%'>
	<tr>
		<th colspan=4>{proxys_list}</th>
	</tr>
	";
	//print_r($datas);
	while (list ($num, $uri) = each ($datas["PROXYS"])){
		if($color==null){$color="#fafafa";}else{$color=null;}
		
		$html=$html."<tr style='background-color:$color;'>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:14px' ".CellRollOver("AddProxycondition($num)","{add_condition}").">$uri</td>
		<td valign='top'>". popup_add_proxy_list_conditions($num,$datas["CONDITIONS"][$num])."</td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","squid_proxy_pacc_del_proxy('$num')")."</td>
		</tr>";
		
		
	}
	
	if(count($datas["FINAL_PROXY"])>0){
		$html=$html."<tr><td colspan=4><hr></td></tr>";
		while (list ($num, $uri) = each ($datas["FINAL_PROXY"])){
			if($color==null){$color="#fafafa";}else{$color=null;}
			$html=$html."<tr style='background-color:$color;'>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td style='font-size:14px'>{final_proxy}:$uri</td>
				<td>&nbsp;</td>
				<td width=1%>". imgtootltip("ed_delete.gif","{delete}","squid_proxy_pacc_del_final_proxy('$num')")."</td>
				</tr>";
		}	
	}
	
	
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_add_proxy_list_conditions($num,$array){
	
	if(!is_array($array)){return null;}	
	$html="<table style='width:80%'>";
	while (list ($index, $condition) = each ($array)){

		if($condition["dnsDomainIs"]<>null){
			$html=$html."<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'>
			<td nowrap>{dnsDomainIs}:&laquo;{$condition["dnsDomainIs"]}&raquo;</td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteCondition($num,$index)")."</td>
			</tr>";
		}

		if($condition["isPlainhost"]<>null){
			$html=$html."<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'>
			<td nowrap>{isPlainhost}:&laquo;{$condition["isPlainhost"]}&raquo;</td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteCondition($num,$index)")."</td>
			</tr>";
		}	

		if($condition["FailOverProxy"]<>null){
			$html=$html."<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'>
			<td nowrap>{other_proxy}:&laquo;{$condition["FailOverProxy"]}&raquo;</td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DeleteCondition($num,$index)")."</td>
			</tr>";
		}			
		
		
		
		
		
	}
	
	$html=$html."</table>";
	return $html;
}



function popup_isInNet_list(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));		
	if(!is_array($datas["isInNet"])){return null;}
	
	$html="<table style='width:100%'>
	<tr>
		<th colspan=3>{whitelisted_networks}</th>
	</tr>
	";
	
	while (list ($num, $uri) = each ($datas["isInNet"])){
		$html=$html."<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:14px'>{$uri[0]}&nbsp;-&nbsp;{$uri[1]}</td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","isInNetDel('$num')")."</td>
		</tr>";
		
		
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_localHostOrDomainIs(){
	$html="
	<div id='popup_localHostOrDomainIs_div'>
	<p style='font-size:14px'>{localHostOrDomainIs_explain}</p>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{servername}:</td>
		<td>". Field_text("localHostOrDomainIs",null,"font-size:13px;padding:3px",null,null,null,false,"localHostOrDomainIsKeyPress(event)")."</td>
	</tr>
	
	<tr>
		<td class=legend>{IE8_compatibility}</td>
		<td>". Field_checkbox("dnsDomainIs",1,0,null)."</td>
	</tr>	
	
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{add}","localHostOrDomainIsAdd()")."</td>
	</tr>
	</table>	
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_isInNet(){
	$html="
	<div id='isInNet_div'>
	<p style='font-size:14px'>{isInNet_explain}</p>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{ip_address}:</td>
		<td>". Field_text("tcp_addr",null,"font-size:13px;padding:3px",null,null,null,false,"isInNetKeyPress(event)")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{mask}:</td>
		<td>". Field_text("mask",null,"font-size:13px;padding:3px",null,null,null,false,"isInNetKeyPress(event)")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{add}","isInNetAdd()")."</td>
	</tr>
	</table>	
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
		
	
}




function popup_add_proxy(){
	
	$html="
	<div id='popup_add_proxy_div'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{proxy_addr}:</td>
		<td>". Field_text("proxy_addr",null,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td>". Field_text("proxy_port",null,"width:90px;font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>". button("{add}","squid_proxy_pacc_add_proxy_perform()")."</td>
	</tr>
	</table>	
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}


function popup_add_final_proxy(){
	$html="
	<div id='popup_add_proxy_div'>
	<div class=explain>{proxy_pac_final_proxy_text}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{proxy_addr}:</td>
		<td>". Field_text("proxy_addr",null,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td>". Field_text("proxy_port",null,"width:90px;font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>". button("{add}","squid_proxy_pacc_add_finalproxy_perform()")."</td>
	</tr>
	</table>	
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function popup_add_condition(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	$page=CurrentPageName();
	$proxyname=$datas["PROXYS"][$_GET["add_condition"]];
	
	$html="
	<div id='addconditiondiv'>
	<H3>{add_condition}:$proxyname</H3>
	<table style='width:100%'>
	<tr>
	<td style='font-size:13px' class=legend>{dnsDomainIs}:</td>
	<td>". Field_text("dnsDomainIs",null,'font-size:13px;padding:3px')."</td>
	</tr><td colspan=2 class=legend><i style='font-size:13px'>{dnsDomainIs_text}</i></td></tr>
	</tr><td colspan=2><hr></td></tr>
	<tr>
	<td style='font-size:13px' class=legend>{isPlainhost}:</td>
	<td>". Field_checkbox("isPlainhost",1,0)."</td>
	</tr>
	<td colspan=2 class=legend><i style='font-size:13px'>{isPlainhost_text}</i></td>
	
	</tr><td colspan=2><hr></td></tr>
	<tr>
	<td style='font-size:13px' class=legend valign='top'>{other_proxy}:</td>
	<td>
		<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:13px'>{proxy_addr}:</td>
				<td>". Field_text("other_proxy_addr",null,"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{listen_port}:</td>
				<td>". Field_text("other_proxy_port",null,"width:90px;font-size:13px;padding:3px")."</td>
			</tr>
		</table>
	</td>
	</tr>
	<td colspan=2 class=legend><i style='font-size:13px'>{proxy_pac_other_proxy_text}</i></td>	

	<tr><td colspan=2 align='right'><hr>". button("{add}","ProxyAddCondition()")."</td></tr>
	
	</table>
	</div>
	<script>
	
	var  x_ProxyAddCondition= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin4Hide();
			ProxysPacList();
		}
	
	
		function ProxyAddCondition(){
			var XHR = new XHRConnection();
			XHR.appendData('add_condition_plus',{$_GET["add_condition"]});
			XHR.appendData('dnsDomainIs',document.getElementById('dnsDomainIs').value);
			
			if(document.getElementById('isPlainhost').checked){
				XHR.appendData('isPlainhost','{yes}');
			}
			
			XHR.appendData('other_proxy_addr',document.getElementById('other_proxy_addr').value);
			XHR.appendData('other_proxy_port',document.getElementById('other_proxy_port').value);
			
			document.getElementById('addconditiondiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_ProxyAddCondition);			
		}
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function popup_add_condition_add(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));
	if(trim($_GET["dnsDomainIs"])<>null){
		$datas["CONDITIONS"][$_GET["add_condition_plus"]][]["dnsDomainIs"]=$_GET["dnsDomainIs"];
	}

	if(trim($_GET["isPlainhost"])<>null){
		$datas["CONDITIONS"][$_GET["add_condition_plus"]][]["isPlainhost"]=$_GET["isPlainhost"];
	}		
	
	if(trim($_GET["other_proxy_addr"])<>null){
		$datas["CONDITIONS"][$_GET["add_condition_plus"]][]["FailOverProxy"]="{$_GET["other_proxy_addr"]}:{$_GET["other_proxy_port"]}";
	}	
	
	
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");	
}

function popup_del_condition(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ProxyPacDatas")));	
	unset($datas["CONDITIONS"][$_GET["DeleteCondition"]][$_GET["DeleteConditionKey"]]);
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ProxyPacDatas");
	$sock->getFrameWork("cmd.php?proxy-pac-build=yes");					
}

function popup_script(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?proxy-pac-show=yes"));	
	$html="<textarea style='height:450px;overflow:auto;width:100%;font-size:12px'>$datas</textarea>";
	echo $html;
	
	
}


?>