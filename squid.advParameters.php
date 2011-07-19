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
	if(isset($_GET["sizelimit"])){sizelimit_popup();exit;}
	
	if(isset($_GET["WCCP"])){WCCP_popup();exit;}
	if(isset($_GET["wccp2_enabled"])){WCCP_SAVE();exit;}
	
	if(isset($_GET["http-safe-ports"])){http_safe_ports_popup();exit;}
	if(isset($_GET["http-safe-ports-list"])){http_safe_ports_list();exit;}
	if(isset($_GET["http-safe-ports-add"])){http_safe_ports_add();exit;}
	if(isset($_GET["http-safe-ports-del"])){http_safe_ports_del();exit;}
	
	
	if(isset($_GET["http-safe-ports-ssl"])){http_safe_ports_ssl_popup();exit;}
	if(isset($_GET["http-safe-ports-ssl-list"])){http_safe_ports_ssl_list();exit;}
	if(isset($_GET["http-safe-ports-ssl-add"])){http_safe_ports_ssl_add();exit;}
	if(isset($_GET["http-safe-ports-ssl-del"])){http_safe_ports_ssl_del();exit;}	
	
	if(isset($_GET["allow_squid_localhost"])){allow_squid_localhost_save();exit;}
	
	if(isset($_GET["request_header_max_size"])){sizelimit_save();exit;}

	
js();

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squid_advanced_parameters}");
	
	$html="
	function SquidAVParamStart(){
		YahooWin('650','$page?popup=yes','$title');
	}
	SquidAVParamStart()";
	
	echo $html;
	
	
}

function allow_squid_localhost_save(){
	$squid=new squidbee();
	$squid->allow_squid_localhost=$_GET["allow_squid_localhost"];
	$squid->ignore_expect_100=$_GET["ignore_expect_100"];
	$squid->SaveToLdap();
}

function sizelimit_save(){
	$squid=new squidbee();
	$squid->global_conf_array["request_header_max_size"]=$_GET["request_header_max_size"];
	$squid->global_conf_array["request_body_max_size"]=$_GET["request_body_max_size"];
	$squid->global_conf_array["reply_body_max_size"]=$_GET["reply_body_max_size"];
	$squid->EnableChangeRequestSize=$_GET["EnableChangeRequestSize"];
	$squid->SaveToLdap();
	
}

function sizelimit_popup(){
	$page=CurrentPageName();
	$aray_m["KB"]="KB";
	$aray_m["MB"]="MB";
	$aray_m[null]="{none}";
	
	$squid=new squidbee();
	$EnableChangeRequestSize=$squid->EnableChangeRequestSize;
	
	
	if(preg_match("#([0-9]+)\s+([A-Z]+)#",$squid->global_conf_array["request_header_max_size"],$re)){
		$request_header_max_size_v=$re[1];
		
	}
	
	if(preg_match("#([0-9]+)\s+([A-Z]+)#",$squid->global_conf_array["request_body_max_size"],$re)){
		$request_body_max_size_v=$re[1];
		
	}

	if(preg_match("#([0-9]+)\s+([A-Z]+)#",$squid->global_conf_array["reply_body_max_size"],$re)){
		$reply_body_max_size_v=$re[1];
		
	}
	if(preg_match("#([0-9]+)#",$squid->global_conf_array["client_request_buffer_max_size"],$re)){
		$client_request_buffer_max_size=$re[1];
		
	}
	
	if(preg_match("#([0-9]+)#",$squid->global_conf_array["reply_header_max_size"],$re)){
		$reply_header_max_size=$re[1];
		
	}	
	
	
	$html="
	<div id='SquidAVParamID'></div>
	<table style='width:100%' class=form>
	<tr>
		<td style='font-size:12px;' class=legend>{enable}:</td>
		<td>". Field_checkbox("EnableChangeRequestSize",1,$EnableChangeRequestSize,"EnableChangeRequestSizeCheck()")."</td>
		<td colspan=2>&nbsp;</td>
	</tr>	
	<tr>
		<td style='font-size:12px;' class=legend>{request_header_max_size}:</td>
		<td>". Field_text("request_header_max_size",$request_header_max_size_v,"font-size:13px;padding:3px;width:60px")."</td>
		<td style='font-size:13px;' width=1%>&nbsp;KB</td>
		<td>". help_icon("{request_header_max_size_text}")."</td>
	</tr>
	<tr>
		<td style='font-size:12px;' class=legend>{reply_header_max_size}:</td>
		<td>". Field_text("reply_header_max_size",$reply_header_max_size,"font-size:13px;padding:3px;width:60px")."</td>
		<td style='font-size:13px;' width=1%>&nbsp;KB</td>
		<td>". help_icon("{reply_header_max_size_text}")."</td>
	</tr>	
	
	
	<tr>
		<td style='font-size:12px;' class=legend>{client_request_buffer_max_size}:</td>
		<td>". Field_text("client_request_buffer_max_size",$client_request_buffer_max_size,"font-size:13px;padding:3px;width:60px")."</td>
		<td style='font-size:13px;' width=1%>&nbsp;KB</td>
		<td>". help_icon("{client_request_buffer_max_size_text}")."</td>
	</tr>	
	
	
	
	<tr>
		<td style='font-size:12px' class=legend>{request_body_max_size}:</td>
		<td>". Field_text("request_body_max_size",$request_body_max_size_v,"font-size:13px;padding:3px;width:60px")."</td>
		<td style='font-size:13px;' width=1%>&nbsp;KB</td>
		<td>". help_icon("{request_body_max_size_text}")."</td>
	</tr>	
	
	<tr>
		<td style='font-size:12px' class=legend>{reply_body_max_size}:</td>
		<td>". Field_text("reply_body_max_size",$reply_body_max_size_v,"font-size:13px;padding:3px;width:60px")."</td>
		<td style='font-size:13px;' width=1%>&nbsp;KB</td>
		<td>". help_icon("{reply_body_max_size_text}")."</td>
	</tr>		
	
	
	
	<tr>
		<td colspan=4 align='right'>
			<hr>
				". button("{apply}","SquidAVParamSave()")."
		</td>
	</tr>
	</table>
	<hr>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{allow_squid_localhost}</td>
		<td>". Field_checkbox("allow_squid_localhost",1,$squid->allow_squid_localhost,"EnableChangeRequestSizeCheck()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{ignore_expect_100}</td>
		<td>". Field_checkbox("ignore_expect_100",1,$squid->ignore_expect_100,"")."</td>
		<td>". help_icon("{ignore_expect_100_text}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'>
			<hr>
				". button("{apply}","SquidLocalHostSave()")."
		</td>
	</tr>	
	</table>
	
	
<script>
var X_SquidAVParamSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){
		alert(results);
		document.getElementById('SquidAVParamID').innerHTML='';
		
	}
	RefreshTab('main_squid_adv');
	}

	function SquidAVParamSave(){
		var XHR = new XHRConnection();
		XHR.appendData('request_header_max_size',
		document.getElementById('request_header_max_size').value+' KB');
		
		XHR.appendData('request_body_max_size',
		document.getElementById('request_body_max_size').value+' KB');
		
		XHR.appendData('reply_body_max_size',document.getElementById('reply_body_max_size').value);
		XHR.appendData('reply_header_max_size',document.getElementById('reply_header_max_size').value);
		XHR.appendData('client_request_buffer_max_size',document.getElementById('client_request_buffer_max_size').value);
		
		
				
		if(document.getElementById('EnableChangeRequestSize').checked){
			XHR.appendData('EnableChangeRequestSize',1);
			
		}else{
			XHR.appendData('EnableChangeRequestSize',0);
		}
		
		document.getElementById('SquidAVParamID').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SquidAVParamSave);				
	}
	
	function SquidLocalHostSave(){
		var XHR = new XHRConnection();
		if(document.getElementById('allow_squid_localhost').checked){
			XHR.appendData('allow_squid_localhost',1);
		}else{
			XHR.appendData('allow_squid_localhost',0);
		}
		
		if(document.getElementById('ignore_expect_100').checked){
			XHR.appendData('ignore_expect_100',1);
		}else{
			XHR.appendData('ignore_expect_100',0);
		}		
		
		
		
		document.getElementById('SquidAVParamID').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SquidAVParamSave);		
	}

	function EnableChangeRequestSizeCheck(){
		document.getElementById('reply_body_max_size').disabled=true;
		document.getElementById('request_header_max_size').disabled=true;
		document.getElementById('request_body_max_size').disabled=true;
		document.getElementById('reply_body_max_size').disabled=true;
		document.getElementById('reply_header_max_size').disabled=true;
		document.getElementById('client_request_buffer_max_size').disabled=true;
		
		
		if(document.getElementById('EnableChangeRequestSize').checked){
			document.getElementById('reply_body_max_size').disabled=false;
			document.getElementById('request_header_max_size').disabled=false;
			document.getElementById('request_body_max_size').disabled=false;
			document.getElementById('reply_body_max_size').disabled=false;	
			document.getElementById('reply_header_max_size').disabled=false;
			document.getElementById('client_request_buffer_max_size').disabled=false;			
			}	
		}
	
	EnableChangeRequestSizeCheck();
</script>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function WCCP_popup(){
	$squid=new squidbee();
	$page=CurrentPageName();
	
	$wccp2_forwarding_method_hash=array(
	1=>"{wccp2_forwarding_method_hash_1}",2=>"{wccp2_forwarding_method_hash_2}");
	
	$wccp2_return_method_hash=array(
		"gre"=>"GRE encapsulation",
		"l2"=>"L2 redirect"
	
	);
	
	$wccp2_assignment_method_hash=array(
		"hash"=>"Hash assignment",
		"mask"=>"Mask assignment"
	
	);	

	
	
	
$html="

	<H3>{WCCP_NAME}</H3>
	<input type='hidden' id='transparent_enabled' value='$squid->hasProxyTransparent'>
	<div class=explain>{WCCP_HOWTO}</div>
	<div id='SquidAVParamWCCP'></div>
	<table style='width:100%'>
	<tr>
		<td style='font-size:12px;' class=legend>{wccp2_enabled}:</td>
		<td>". Field_checkbox("wccp2_enabled",1,$squid->wccp2_enabled,"wccp2_enabled()")."</td>
		<td>&nbsp;</td>
	</tr>
	
	
	<tr>
		<td style='font-size:12px' class=legend nowrap>{wccp2_router}:</td>
		<td>". Field_text("wccp2_router",$squid->wccp2_router,"font-size:12px;padding:3px;width:220px")."</td>
		<td></td>
	</tr>	
	
	<tr>
		<td style='font-size:12px' class=legend nowrap>{wccp2_forwarding_method}:</td>
		<td>". Field_array_Hash($wccp2_forwarding_method_hash,"wccp2_forwarding_method",$squid->wccp2_forwarding_method)."</td>
		<td>&nbsp;</td>
	</tr>		
	
	<tr>
		<td style='font-size:12px' class=legend nowrap>{wccp2_return_method}:</td>
		<td>". Field_array_Hash($wccp2_return_method_hash,"wccp2_return_method",$squid->wccp2_return_method)."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td style='font-size:12px' class=legend nowrap>{wccp2_assignment_method}:</td>
		<td>". Field_array_Hash($wccp2_assignment_method_hash,"wccp2_assignment_method",$squid->wccp2_assignment_method)."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=4 align='right'>
			<hr>
				". button("{apply}","SquidWccp2ParamSave()")."
		</td>
	</tr>
	</table>
	
	<script>
	
var X_SquidWccp2ParamSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){
		alert(results);
		document.getElementById('SquidAVParamWCCP').innerHTML='';
		}
	RefreshTab('main_squid_adv');
	}
		
	function SquidWccp2ParamSave(){
		var XHR = new XHRConnection();
		
		if(document.getElementById('wccp2_enabled').checked){
			XHR.appendData('wccp2_enabled',1);
		}else{
			XHR.appendData('wccp2_enabled',0);
		}
		
		XHR.appendData('wccp2_router',
		document.getElementById('wccp2_router').value);

		XHR.appendData('wccp2_forwarding_method',
		document.getElementById('wccp2_forwarding_method').value);

		XHR.appendData('wccp2_return_method',
		document.getElementById('wccp2_return_method').value);

		XHR.appendData('wccp2_assignment_method',
		document.getElementById('wccp2_assignment_method').value);		
		
		
		document.getElementById('SquidAVParamWCCP').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SquidWccp2ParamSave);				
	}	
	
	
	function wccp2_disable_all(){
		document.getElementById('wccp2_forwarding_method').disabled=true;
		document.getElementById('wccp2_router').disabled=true;
		document.getElementById('wccp2_forwarding_method').disabled=true;
		document.getElementById('wccp2_return_method').disabled=true;
		document.getElementById('wccp2_assignment_method').disabled=true;
	}
	
	function wccp2_enable_all(){
		document.getElementById('wccp2_forwarding_method').disabled=false;
		document.getElementById('wccp2_router').disabled=false;
		document.getElementById('wccp2_forwarding_method').disabled=false;
		document.getElementById('wccp2_return_method').disabled=false;
		document.getElementById('wccp2_assignment_method').disabled=false;
	}	
	
	function wccp2_enabled(){
		wccp2_disable_all();
		if(document.getElementById('transparent_enabled').value!=='1'){
			document.getElementById('wccp2_enabled').disabled=true;
			document.getElementById('wccp2_enabled').checked=false;
			return ;
		}
		if(document.getElementById('wccp2_enabled').checked){wccp2_enable_all();}
	}

	wccp2_enabled();
	</script>
	";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function WCCP_SAVE(){
	$squid=new squidbee();
	$squid->wccp2_enabled=$_GET["wccp2_enabled"];
	$squid->wccp2_router=$_GET["wccp2_router"];
	$squid->wccp2_forwarding_method=$_GET["wccp2_forwarding_method"];
	$squid->wccp2_return_method=$_GET["wccp2_return_method"];
	$squid->wccp2_assignment_method=$_GET["wccp2_assignment_method"];
	$squid->SaveToLdap();
}


function popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["sizelimit"]=$tpl->_ENGINE_parse_body('{squid_sizelimit}');
	$array["http-safe-ports"]=$tpl->_ENGINE_parse_body('{http_safe_ports}');
	$array["http-safe-ports-ssl"]=$tpl->_ENGINE_parse_body('{http_safe_ports} (SSL)');
	$array["WCCP"]=$tpl->_ENGINE_parse_body('WCCP');
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_squid_adv style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squid_adv').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
}

function http_safe_ports_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$HTTP_ADD_SAFE_PORTS_EXPLAIN=$tpl->javascript_parse_text("{HTTP_ADD_SAFE_PORTS_EXPLAIN}");
	$GIVE_A_NOTE=$tpl->javascript_parse_text("{GIVE_A_NOTE}");
	$html="
	<div class=explain>{HTTP_SAFE_PORTS_EXPLAIN}</div>
	<div style='text-align:right'>". button("{add}","HTTPSafePortAdd()")."</div>
	
	<div style='margin-top:10px;height:400px;overflow:auto' id='HTTP_SAFE_PORTS_LIST'></div>
	
	<script>
		function REFRESH_HTTP_SAFE_PORTS_LIST(){
			LoadAjax('HTTP_SAFE_PORTS_LIST','$page?http-safe-ports-list=yes');
		}
		
		var x_HTTPSafePortAdd=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			REFRESH_HTTP_SAFE_PORTS_LIST();
		}
		
		function HTTPSafePortAdd(){
				var XHR = new XHRConnection();	
				var explain='';
				var value=prompt('$HTTP_ADD_SAFE_PORTS_EXPLAIN');
				explain=prompt('$GIVE_A_NOTE','my specific web port...');
				if(value){
					XHR.appendData('http-safe-ports-add',value);
					XHR.appendData('http-safe-ports-explain',explain);
					document.getElementById('HTTP_SAFE_PORTS_LIST').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
					XHR.sendAndLoad('$page', 'GET',x_HTTPSafePortAdd);
				}
		}		
			
		
	REFRESH_HTTP_SAFE_PORTS_LIST();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function http_safe_ports_ssl_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$HTTP_ADD_SAFE_PORTS_EXPLAIN=$tpl->javascript_parse_text("{HTTP_ADD_SAFE_PORTS_EXPLAIN}");
	$GIVE_A_NOTE=$tpl->javascript_parse_text("{GIVE_A_NOTE}");
	$html="
	<div class=explain>{HTTP_SAFE_PORTS_EXPLAIN} <strong>SSL/HTTPS</strong></div>
	<div style='text-align:right'>". button("{add}","HTTPSafePortSSLAdd()")."</div>
	
	<div style='margin-top:10px;height:400px;overflow:auto' id='HTTP_SAFE_PORTS_SSL_LIST'></div>
	
	<script>
		function REFRESH_HTTP_SAFE_PORTS_SSL_LIST(){
			LoadAjax('HTTP_SAFE_PORTS_SSL_LIST','$page?http-safe-ports-ssl-list=yes');
		}
		
		var x_HTTPSafePortSSLAdd=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			REFRESH_HTTP_SAFE_PORTS_SSL_LIST();
		}
		
		function HTTPSafePortSSLAdd(){
				var XHR = new XHRConnection();	
				var explain='';
				var value=prompt('$HTTP_ADD_SAFE_PORTS_EXPLAIN');
				explain=prompt('$GIVE_A_NOTE','my specific web port...');
				if(value){
					XHR.appendData('http-safe-ports-ssl-add',value);
					XHR.appendData('http-safe-ports-ssl-explain',explain);
					document.getElementById('HTTP_SAFE_PORTS_SSL_LIST').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
					XHR.sendAndLoad('$page', 'GET',x_HTTPSafePortSSLAdd);
				}
		}		
			
		
	REFRESH_HTTP_SAFE_PORTS_SSL_LIST();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
		
	
}

function http_safe_ports_add(){
	$port=$_GET["http-safe-ports-add"];
	$port=str_replace('"',"",$port);
	$explain=$_GET["http-safe-ports-explain"];
	if($explain=="my specific web port..."){$explain="added on ".date("Y-m-d H:i");}
	if($explain==null){$explain="added on ".date("Y-m-d H:i");}
	$sock=new sockets();
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsList")));	
	$ports[$port]=$explain;
	$sock->SaveConfigFile(base64_encode(serialize($ports)),"SquidSafePortsList");
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
	
}

function http_safe_ports_del(){
	$sock=new sockets();
	$port=base64_decode($_GET["http-safe-ports-del"]);
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsList")));	
	unset($ports[$port]);
	$sock->SaveConfigFile(base64_encode(serialize($ports)),"SquidSafePortsList");
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
}
function http_safe_ports_ssl_del(){
	$sock=new sockets();
	$port=base64_decode($_GET["http-safe-ports-ssl-del"]);
	writelogs("SSL_Safe::Delete SSL port $port",__FUNCTION__,__FILE__,__LINE__);
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsSSLList")));	
	unset($ports[$port]);
	$sock->SaveConfigFile(base64_encode(serialize($ports)),"SquidSafePortsSSLList");
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
}
function http_safe_ports_ssl_add(){
	$port=$_GET["http-safe-ports-ssl-add"];
	$port=str_replace('"',"",$port);
	$explain=$_GET["http-safe-ports-ssl-explain"];
	if($explain=="my specific web port..."){$explain="added on ".date("Y-m-d H:i");}
	if($explain==null){$explain="added on ".date("Y-m-d H:i");}
	$sock=new sockets();
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsSSLList")));	
	$ports[$port]=$explain;
	$sock->SaveConfigFile(base64_encode(serialize($ports)),"SquidSafePortsSSLList");
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
	
}

function http_safe_ports_ssl_list(){
	$sock=new sockets();
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsSSLList")));
	$page=CurrentPageName();
	$tpl=new templates();	
	if(!is_array($ports)){$add=true;}
	if(count($ports)<2){$add=true;}
	
	
	
	if($add){
		writelogs("SSL_Safe:: is not an array...",__FUNCTION__,__FILE__,__LINE__);
		$ports["9000"]="Artica";
		$ports["443"]="HTTPS";
		$ports["563"]="https, snews";
		$ports["6667"]="tchat";
		writelogs("Saving new default list (".count($ports)." rows)",__FUNCTION__,__FILE__,__LINE__);
		$sock->SaveConfigFile(base64_encode(serialize($ports)),"SquidSafePortsSSLList");
	}  	

	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{ports}</th>
	<th>{note}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($port, $explain) = each ($ports) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$port_enc=base64_encode($port);
			$delete=imgtootltip("delete-24.png","{delete}","HttpSafePortSSLDelete('$port_enc')");
			$html=$html."
			<tr class=$classtr>
			<td width=1% nowrap align='right'><strong style='font-size:14px'>$port</td>
			<td align=left><strong style='font-size:14px'>$explain</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
	
	
	$html=$html."</tbody>
	</table>
	<script>
	var x_HttpSafePortSSLDelete= function (obj) {
			var tempvalue=obj.responseText;
			REFRESH_HTTP_SAFE_PORTS_SSL_LIST();
		}			
		
		function HttpSafePortSSLDelete(enc){
			var XHR = new XHRConnection();
			XHR.appendData('http-safe-ports-ssl-del',enc);
			document.getElementById('HTTP_SAFE_PORTS_SSL_LIST').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_HttpSafePortSSLDelete);
		}		
	
	</script>
	";	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function http_safe_ports_list(){
	$sock=new sockets();
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsList")));
	$page=CurrentPageName();
	$tpl=new templates();	
	if(!is_array($ports)){$add=true;}
	if(count($ports)<2){$add=true;}
	if($add){
		$ports["80"]="http";
		$ports["22"]="ssh";
		$ports["443 563"]="https, snews";
		$ports["1863"]="msn";
		$ports["70"]="gopher";
		$ports["210"]="wais";
		$ports["1025-65535"]="unregistered ports";
		$ports["280"]="http-mgmt";
		$ports["488"]="gss-http";
		$ports["591"]="filemaker";
		$ports["777"]="multiling http";
		$ports["631"]="cups";
		$ports["873"]="rsync";
		$ports["901"]="SWAT";	
		$sock->SaveConfigFile(base64_encode(serialize($ports)),"SquidSafePortsList");	
	}	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{ports}</th>
	<th>{note}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($port, $explain) = each ($ports) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$port_enc=base64_encode($port);
			$delete=imgtootltip("delete-24.png","{delete}","HttpSafePortDelete('$port_enc')");
			$html=$html."
			<tr class=$classtr>
			<td width=1% nowrap align='right'><strong style='font-size:14px'>$port</td>
			<td align=left><strong style='font-size:14px'>$explain</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
	
	
	$html=$html."</tbody>
	</table>
	<script>
	var x_HttpSafePortDelete= function (obj) {
			var tempvalue=obj.responseText;
			REFRESH_HTTP_SAFE_PORTS_LIST();
		}			
		
		function HttpSafePortDelete(enc){
			var XHR = new XHRConnection();
			XHR.appendData('http-safe-ports-del',enc);
			document.getElementById('HTTP_SAFE_PORTS_LIST').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_HttpSafePortDelete);
		}		
	
	</script>
	";	
	echo $tpl->_ENGINE_parse_body($html);
}



?>