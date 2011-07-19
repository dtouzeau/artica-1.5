<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.openvpn.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.tcpip.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}

if(isset($_GET["startpage"])){echo startpage();exit;}
if(isset($_GET["wizard"])){wizard();exit;}
if(isset($_GET["wizard-key"])){wizard_key();exit;}
if(isset($_GET["wizard-server"])){wizard_server();exit;}
if(isset($_GET["wizard-finish"])){wizard_finish();exit;}
if(isset($_GET["KEY_COUNTRY_NAME"])){SaveCertificate();exit;}
if(isset($_GET["ENABLE_SERVER"])){SaveServerConf();exit;}
if(isset($_GET["ENABLE_BRIDGE"])){SaveBridgeMode();exit;}
if(isset($_GET["VPN_DNS_DHCP_1"])){SaveServerConf();exit;}
if(isset($_GET["restart-server"])){RestartServer();exit;}
if(isset($_GET["server-settings"])){server_settings();exit;}
if(isset($_GET["server-settings-js"])){server_settings_js();exit;}
if(isset($_GET["routes"])){routes_settings();exit;}
if(isset($_GET["ROUTE_SHOULD_BE"])){routes_shouldbe();exit;}
if(isset($_GET["ROUTE_FROM"])){routes_add();exit;}
if(isset($_GET["routes-list"])){routes_list();exit;}
if(isset($_GET["DELETE_ROUTE_FROM"])){routes_delete();exit;}

if(isset($_GET["events-js"])){events_js();exit;}
if(isset($_GET["events"])){events();exit;}
if(isset($_GET["events-session"])){events_sessions();exit;}
if(isset($_GET["session-js"])){events_sessions_js();exit;}
if(isset($_GET["clients-settings"])){Clients_settings();exit;}
if(isset($_GET["ncc"])){ncc();exit;}
if(isset($_GET["OpenVPNChangeServerMode"])){OpenVPNChangeServerMode();exit;}
if(isset($_GET["BRIDGE_ETH_SHOW"])){echo ShowIPConfig($_GET["BRIDGE_ETH_SHOW"]);exit;}
if(isset($_GET["index"])){index_page();exit;}
if(isset($_GET["rebuild-certificate"])){rebuild_certificate();exit;}
if(isset($_GET["build-server-events"])){events_content();exit;}
if(isset($_GET["events-sessions-details"])){events_sessions_details();exit;}
if(isset($_GET["openvpn-status"])){status();exit;}

js();


function events_sessions_js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?events-session=yes');";
	
}
function server_settings_js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?server-settings=yes');";
}
function events_js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?events=yes');";
}

function routes_add(){
	$vpn=new openvpn();
	$vpn->routes[$_GET["ROUTE_FROM"]]=$_GET["ROUTE_MASK"];
	$vpn->Save();
	
}
function routes_delete(){
	$vpn=new openvpn();
	unset($vpn->routes[$_GET["DELETE_ROUTE_FROM"]]);
	$vpn->Save();
	
}

function routes_list($noecho=0){
	$vpn=new openvpn();
	if(!is_array($vpn->routes)){return null;}
	reset($vpn->routes);
	$html="
<center><table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:75%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{ipaddr}</th>
	<th>{netmask}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	$classtr=null;
	while (list ($num, $ligne) = each ($vpn->routes) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
		if(trim($ligne)==null){continue;}
		$html=$html ."
			<tr class=$classtr>
		 	<td width=1%><img src='img/fw_bold.gif'></td>
			<td style='font-size:14px;font-weight:bold'><code>$num</code></td>
			<td style='font-size:14px;font-weight:bold'><code>$ligne</code></td>
			<td width=1%>" . imgtootltip('delete-32.png','{delete}',"OpenVPNRoutesDelete('$num')")."</td>
			</tr>
			";
						
						
			
				}	

	$html=$html . "</table></center>";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);
	
}

function events(){
	
	$page=CurrentPageName();
	
	$html="
	
	<div style='width:100%;height:750px;overflow:auto' id='build-server-events'></div>
	<script>
		LoadAjax('build-server-events','$page?build-server-events=yes');
	</script>
	
	
	
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function events_content(){
	$sock=new sockets();
	$page=CurrentPageName();
	$datas=unserialize(base64_decode($sock->getFrameWork("network.php?OpenVPNServerLogs=yes")));
	$tpl=new templates();
	$tbl=array_reverse($datas);
	
$html=$tpl->_ENGINE_parse_body("<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>". imgtootltip("refresh-24.png","{refresh}","LoadAjax('build-server-events','$page?build-server-events=yes');")."</th>
	<th>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>");		
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		if(strpos($ligne, "WWWWWW")>0){continue;}
		if(strpos($ligne, "WRWRWRW")>0){continue;}
		if(strpos($ligne, "rWRrWRw")>0){continue;}
		if(trim($ligne)=="WWWR"){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if(preg_match("#^([A-Za-z]+)\s+([A-Za-z]+)\s+([0-9]+)\s+([0-9\:]+)\s+([0-9]+)\s+us=.+?\s+(.+)#", $ligne,$re)){
			$ligne=$re[6];
			$time=$re[4];
		}
		$ligne=htmlentities($ligne);
		if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#", $ligne,$re)){
			$ligne=str_replace($re[0], "<b>{$re[0]}</b>", $ligne);
		}
		
		
		$html=$html . "<tr class=$classtr>
		<td style='font-size:11px'>$time</td>
		<td><code style='font-size:11px'>$ligne</td>
		</tr>";
	}
	
	echo "$html</table>";
	
}

function events_sessions_details(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?OpenVPNServerSessions=yes')));
	
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("refresh-24.png","{refresh}","LoadAjax('events_sessions_details','$page?events-sessions-details=yes');")."</th>
	<th>{username}</th>
	<th>{ip_address}</th>
	<th>{b_received}</th>
	<th>{b_sent}</th>
	<th>{time}</th>
	</tr>
</thead>
<tbody class='tbody'>";		

while (list ($num, $ligne) = each ($tbl) ){
	if(preg_match("#([0-9\.]+),(.+?),([0-9\.\:]+),(.+)#", $ligne,$re)){$array[$re[2]]["LOCAL_IP"]=$re[1];}
	if(preg_match('#(.+?),([0-9\.\:]+),([0-9]+),([0-9]+),(.+)#',$ligne,$re)){
		if(preg_match("#(.+?):#", $re[2],$ri)){$re[2]=$ri[1];}
		$array[$re[1]]["REMOTE_IP"]=$re[2];
		$array[$re[1]]["b_received"]=$re[3];
		$array[$re[1]]["b_sent"]=$re[4];
		$array[$re[1]]["time"]=$re[5];
		
		
	}

}
	
	
	while (list ($uid, $ligne) = each ($array) ){
		if($uid==null){continue;}
		if($ligne["REMOTE_IP"]==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne["b_received"]=$ligne["b_received"]/1024;
		$ligne["b_received"]=FormatBytes($ligne["b_received"]);
		
		$ligne["b_sent"]=$ligne["b_sent"]/1024;
		$ligne["b_sent"]=FormatBytes($ligne["b_sent"]);		
		
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/connect-32.png'></td>
			<td nowrap><strong style='font-size:14px'>$uid</strong></td>
			<td nowrap><strong style='font-size:14px'>{$ligne["REMOTE_IP"]}/{$ligne["LOCAL_IP"]}</strong></td>
			<td nowrap><strong style='font-size:14px'>{$ligne["b_received"]}</strong></td>
			<td nowrap><strong style='font-size:14px'>{$ligne["b_sent"]}</strong></td>
			<td nowrap><strong style='font-size:14px'>{$ligne["time"]}</strong></td>
		</tr>";
		
	}	
	

	
	$html=$html."</table>";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}

function events_sessions(){
	$page=CurrentPageName();
	
	$html="
	<div style='width:100%;height:250px;overflow:auto' id='events_sessions_details'></div>
	<script>
		LoadAjax('events_sessions_details','$page?events-sessions-details=yes');
	</script>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}






function routes_shouldbe(){
	$ip=$_GET["ROUTE_SHOULD_BE"];
	if(preg_match("#([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".0.0.0";
		$calc_ip_end=$re[1].".255.255.255";
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.0.0";
		$calc_ip_end=$re[1].".{$re[2]}.255.255";
	}

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}	

	
	$ip=new IP();
	$cdir=$ip->ip2cidr($calc_ip,$calc_ip_end);
	$arr=$ip->parseCIDR($cdir);
	$rang=$arr[0];
	$netbit=$arr[1];
	$ipv=new ipv4($calc_ip,$netbit);
	echo "<strong>$cdir {$ipv->address()} - {$ipv->netmask()}</strong>"; 
	
	
}


function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_OPENVPN}');
	$OPENVPN_WIZARD=$tpl->_ENGINE_parse_body('{OPENVPN_WIZARD}');
	$OPENVPN_SERVER_SETTINGS=$tpl->_ENGINE_parse_body('{OPENVPN_SERVER_SETTINGS}');
	$events=$tpl->_ENGINE_parse_body('{events}');
	$NETWORK_CONTROL_CENTER=$tpl->_ENGINE_parse_body('{NETWORK_CONTROL_CENTER}');
	$page=CurrentPageName();
	$function="LoadOpenvpn();";
	if(isset($_GET["infront"])){$function="LoadOpenVPNv2();";}
	
	
	$html="
	
		function LoadOpenvpn(){
			YahooWin2('705','$page?startpage=yes','$title');
		
		}
		
		function LoadOpenVPNv2(){
			$('#BodyContent').load('$page?startpage=yes');
		}
		
		function StartWizard(){
			LoadAjax('wizarddiv','$page?wizard-key=yes');
		
		}
		
		function StartWizardServer(){
			LoadAjax('wizarddiv','$page?wizard-server=yes');
		
		}

		function WizardFinish(){
			LoadAjax('wizarddiv','$page?wizard-finish=yes');
		
		}		
		
		var x_SaveWizardKey= function (obj) {
			var tempvalue=obj.responseText;
			StartWizardServer();

		}	
		
		var x_SaveWizardServer= function (obj) {
			var tempvalue=obj.responseText;
			WizardFinish();
			}
			


		var x_SaveServerSettings= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			RefreshTab('main_openvpn_config');
			}	

		var x_SaveClientsSettings= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			OpenVPNClientsSettings();
			}				
		
		function SaveWizardServer(){
		var XHR = new XHRConnection();
		XHR.appendData('ENABLE_SERVER',document.getElementById('ENABLE_SERVER').value);
		XHR.appendData('LISTEN_PORT',document.getElementById('LISTEN_PORT').value);
		XHR.appendData('IP_START',document.getElementById('IP_START').value);
		XHR.appendData('NETMASK',document.getElementById('NETMASK').value);
		document.getElementById('wizarddiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveWizardServer);
			
		}
		
		function SaveServerSettings(){
		var XHR = new XHRConnection();
		
		if(document.getElementById('ENABLE_SERVER')){
			if(document.getElementById('ENABLE_SERVER').checked){
				XHR.appendData('ENABLE_SERVER','1');
			}else{
				XHR.appendData('ENABLE_SERVER','0');
			}
		}

		if(document.getElementById('DEV_TYPE')){XHR.appendData('DEV_TYPE',document.getElementById('DEV_TYPE').value);}
		if(document.getElementById('LISTEN_PORT')){XHR.appendData('LISTEN_PORT',document.getElementById('LISTEN_PORT').value);}
		if(document.getElementById('IP_START')){XHR.appendData('IP_START',document.getElementById('IP_START').value);}
		if(document.getElementById('NETMASK')){XHR.appendData('NETMASK',document.getElementById('NETMASK').value);}
		if(document.getElementById('PUBLIC_IP')){XHR.appendData('PUBLIC_IP',document.getElementById('PUBLIC_IP').value);}
		if(document.getElementById('BRIDGE_ETH')){XHR.appendData('BRIDGE_ETH',document.getElementById('BRIDGE_ETH').value);}
		
		
		if(document.getElementById('VPN_SERVER_IP')){XHR.appendData('VPN_SERVER_IP',document.getElementById('VPN_SERVER_IP').value);}
		if(document.getElementById('VPN_DHCP_FROM')){XHR.appendData('VPN_DHCP_FROM',document.getElementById('VPN_DHCP_FROM').value);}
		if(document.getElementById('VPN_DHCP_TO')){XHR.appendData('VPN_DHCP_TO',document.getElementById('VPN_DHCP_TO').value);}
		if(document.getElementById('VPN_SERVER_IP')){XHR.appendData('VPN_SERVER_IP',document.getElementById('VPN_SERVER_IP').value);}
		if(document.getElementById('SERVER_IP_START')){XHR.appendData('SERVER_IP_START',document.getElementById('SERVER_IP_START').value);}
		if(document.getElementById('SERVER_IP_END')){XHR.appendData('SERVER_IP_END',document.getElementById('SERVER_IP_END').value);}
		if(document.getElementById('VPN_DHCP_FROM_END')){XHR.appendData('VPN_DHCP_FROM_END',document.getElementById('VPN_DHCP_FROM_END').value);}
		if(document.getElementById('VPN_DHCP_TO_END')){XHR.appendData('VPN_DHCP_TO_END',document.getElementById('VPN_DHCP_TO_END').value);}
		if(document.getElementById('VPN_SERVER_DHCP_MASK')){XHR.appendData('VPN_SERVER_DHCP_MASK',document.getElementById('VPN_SERVER_DHCP_MASK').value);}
		if(document.getElementById('LISTEN_PROTO')){XHR.appendData('LISTEN_PROTO',document.getElementById('LISTEN_PROTO').value);}
		
		if(document.getElementById('VPN_DNS_DHCP_1')){XHR.appendData('VPN_DNS_DHCP_1',document.getElementById('VPN_DNS_DHCP_1').value);}
		if(document.getElementById('VPN_DNS_DHCP_2')){XHR.appendData('VPN_DNS_DHCP_2',document.getElementById('VPN_DNS_DHCP_2').value);}
		if(document.getElementById('LOCAL_BIND')){XHR.appendData('LOCAL_BIND',document.getElementById('LOCAL_BIND').value);}
		if(document.getElementById('IPTABLES_ETH')){XHR.appendData('IPTABLES_ETH',document.getElementById('IPTABLES_ETH').value);}
		if(document.getElementById('OpenVpnPasswordCert')){XHR.appendData('OpenVpnPasswordCert',document.getElementById('OpenVpnPasswordCert').value);}
		if(document.getElementById('BRIDGE_ADDR')){XHR.appendData('BRIDGE_ADDR',document.getElementById('BRIDGE_ADDR').value);}
		if(document.getElementById('CLIENT_NAT_PORT')){XHR.appendData('CLIENT_NAT_PORT',document.getElementById('CLIENT_NAT_PORT').value);}
		
		
		
		
		
		if(document.getElementById('OPENVPN_CLIENT_SETTINGS')){
			document.getElementById('OPENVPN_CLIENT_SETTINGS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveClientsSettings);
		}
		
		if(document.getElementById('OPENVPN_SERVER_SETTINGS')){
			document.getElementById('OPENVPN_SERVER_SETTINGS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveServerSettings);
			}
			
		
	}
	
	function OpenVPNChangeServerMode(){
		var XHR = new XHRConnection();
		if(document.getElementById('ENABLE_BRIDGE').checked){
			XHR.appendData('ENABLE_BRIDGE',1);
		}else{
			XHR.appendData('ENABLE_BRIDGE',0);
		}
		document.getElementById('OPENVPN_SERVER_SETTINGS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveServerSettings);
		
	}
	
		
		function SaveWizardKey(){
			var XHR = new XHRConnection();
        	XHR.appendData('KEY_COUNTRY_NAME',document.getElementById('KEY_COUNTRY_NAME').value);
        	XHR.appendData('KEY_PROVINCE',document.getElementById('KEY_PROVINCE').value);
			XHR.appendData('KEY_CITY',document.getElementById('KEY_CITY').value);
			XHR.appendData('KEY_ORG',document.getElementById('KEY_ORG').value);
			XHR.appendData('KEY_EMAIL',document.getElementById('KEY_EMAIL').value);	
			document.getElementById('wizarddiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveWizardKey);	
		
		}
		
	var x_RouteShouldbe= function (obj) {
			var tempvalue=obj.responseText;
			document.getElementById('shouldbe').innerHTML=tempvalue;
			}					
		
		function RouteShouldbe(){
			var ROUTE_FROM=document.getElementById('ROUTE_FROM').value;
			var XHR = new XHRConnection();
			XHR.appendData('ROUTE_SHOULD_BE',ROUTE_FROM);
			XHR.sendAndLoad('$page', 'GET',x_RouteShouldbe);	
		}
		
	var x_OpenVpnAddRoute= function (obj) {
			var tempvalue=obj.responseText;
			LoadAjax('routeslist','$page?routes-list=yes');
			}				
		
		function OpenVpnAddRoute(){
			var XHR = new XHRConnection();
			XHR.appendData('ROUTE_FROM',document.getElementById('ROUTE_FROM').value);
			XHR.appendData('ROUTE_MASK',document.getElementById('ROUTE_MASK').value);
			document.getElementById('routeslist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_OpenVpnAddRoute);
			}
		
		function OpenVPNRoutesDelete(index){
			var XHR = new XHRConnection();
			XHR.appendData('DELETE_ROUTE_FROM',index);
			XHR.sendAndLoad('$page', 'GET',x_OpenVpnAddRoute);
			}
			
	var x_OpenVPNChangeNIC= function (obj) {
			var tempvalue=obj.responseText;
			document.getElementById('nicvpninfo').innerHTML=tempvalue;
			}				
			
		function OpenVPNChangeNIC(){
			var XHR = new XHRConnection();
			XHR.appendData('BRIDGE_ETH_SHOW',document.getElementById('BRIDGE_ETH').value);
			XHR.sendAndLoad('$page', 'GET',x_OpenVPNChangeNIC);
		
		}
		
		
		
		function OpenVPNEventsServer(){YahooWin3('705','$page?events=yes','$events');}		
		function OpenVPNEventsSessions(){YahooWin3('705','$page?events-session=yes','$events');}
		function OpenVPNClientsSettings(){YahooWin4('600','$page?clients-settings=yes','$OPENVPN_CLIENT_SETTINGS');}
		function LoadOpenVpnServerSettings(){YahooWin4('700','$page?server-settings=yes','$OPENVPN_SERVER_SETTINGS');}
		function OpenVPNNCC(){YahooWin4('800','$page?ncc=yes','$NETWORK_CONTROL_CENTER');}
		
		
	
		$function
	
	";
		
	echo $html;
	
	
}

function rebuild_certificate(){
	
	$tpl=new templates();
	$text=$tpl->javascript_parse_text("{rebuild_openvpn_certificate_perform}");
	$html="
	
	alert('$text');
	
	";	
	echo $html;
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?openvpn-rebuild-certificate=yes");
	
	
	
}


function startpage(){
	$html=GET_CACHED(__FILE__,__FUNCTION__,null,TRUE);
	if($html<>null){return $html;}
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]='{index}';
	
	$array["server-settings"]="{OPENVPN_SERVER_SETTINGS}";
	$array["remote-sites"]="{REMOTE_SITES_VPN}";
	$array["events-session"]="{sessions}";
	$array["events"]="{events}";

	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="remote-sites"){
			$tab[]="<li><a href=\"openvpn.remotesites.php?infront=yes\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$tab[]="<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
			
		}
	$tpl=new templates();
	
	

	$html="
		<div id='main_openvpn_config' style='background-color:white;width:755px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_openvpn_config').tabs();
			});
		</script>
	
	";
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	return $html;	
	
}	
	
function index_page(){	
	$page=CurrentPageName();
	$sock=new sockets();
	$wizard=Paragraphe('64-wizard.png','{OPENVPN_WIZARD}','{OPENVPN_WIZARD_TEXT}',
	"javascript:YahooWin3('500','$page?wizard=yes','{OPENVPN_WIZARD}')",null,210,null,0,false);	
	

	$routes=Paragraphe('64-win-nic.png','{additional_routes}','{additional_routes_text}',
	"javascript:YahooWin3('500','$page?routes=yes','{additional_routes}')",null,210,null,0,false);
	
	
	$clients_settings=Paragraphe('global-settings.png','{OPENVPN_CLIENT_SETTINGS}',
	'{OPENVPN_CLIENT_SETTINGS_TEXT}',"javascript:OpenVPNClientsSettings()",null,210,null,0,false);
	
	
	$ncc=Paragraphe('64-win-nic-loupe.png','{NETWORK_CONTROL_CENTER}',
	'{NETWORK_CONTROL_CENTER_TEXT}',"javascript:OpenVPNNCC()",null,210,null,0,false);
	
	$remote_add=Paragraphe('HomeNone-64.png','{REMOTE_SITES_VPN}',
	'{REMOTE_SITES_VPN_TEXT}',"javascript:Loadjs('openvpn.remotesites.php')",null,210,null,0,false);
	
	
	
	$server_connect=Paragraphe('server-connect-64.png','{OPENVPN_SERVER_CONNECT}',
	'{OPENVPN_SERVER_CONNECT_TEXT}',"javascript:Loadjs('openvpn.servers-connect.php')",null,210,null,0,false);
	
	
	$rebuild_certificates=Paragraphe("vpn-rebuild.png","{rebuild_openvpn_certificate}",
	"{rebuild_openvpn_certificate_text}","javascript:Loadjs('$page?rebuild-certificate=yes')",null,210,null,0,false);
	
	
	$artica=Buildicon64("DEF_ICO_OPENVPN_ARTICA_CLIENTS");
	
	$f[]=$remote_add;
	$f[]=$server_connect;
	$f[]=$routes;
	$f[]=$ncc;
	$f[]=$rebuild_certificates;
	//$f[]=$artica;
	$q=new mysql();
	$sql="SELECT ID,enabled,servername,serverport,connexion_name,connexion_type,routes FROM vpnclient WHERE connexion_type=2 and enabled=1 ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$running=$sock->getFrameWork("openvpn.php?is-client-running={$ligne["ID"]}");
		if($running=="TRUE"){$img_running="network-connection2.png";}else{$img_running="network-connection2-grey.png";}	
		if(preg_match("#(.+?):#", $ligne["connexion_name"],$re)){$ligne["connexion_name"]=$re[1];}	
		
		$f[]=Paragraphe($img_running, $ligne["connexion_name"], "{manage_your_vpn_client_connection_text}","javascript:Loadjs('index.openvpn.client.php?ID={$ligne["ID"]}&cname={$ligne["connexion_name"]}')",null,210,null,0,false);
		
	}
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($f) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>";	
	
	
	
	$status=status(1);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><div class=explain>{APP_OPENVPN_TEXT}</div></td>
		<td valign='top' ><img src='img/bg_openvpn.png'></td>
	</tr>
	</table>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=280px><div id='openvpn-status'></div></td>
	<td valign='top'>". @implode("\n", $tables)."</td>
	</tr>
	</table>
	<script>
		LoadAjax('openvpn-status','$page?openvpn-status=yes');
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function wizard(){
	
	$html="<H1>{WELCOME_WIZARD}</H1>
	<div class=explain>{WELCOME_WIZARD_TEXT}</div>
	" . RoundedLightWhite("
	<div id='wizarddiv'>
		<div style='text-align:right'><input type='button' OnClick=\"javascript:StartWizard()\" value='{START_WIZARD}&nbsp;&raquo;'></div>
	</div>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function wizard_key(){
	
	$vpn=new openvpn();
	$country=Field_array_Hash($vpn->array_country_codes,"KEY_COUNTRY_NAME",$vpn->main_array["GLOBAL"]["KEY_COUNTRY_NAME"]);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/64-key.png'></td>
		<td valign='top'>
		<H3>{PKI}</H3>
		<div class=explain>{WIZARD_STEP1}</div>
		</td>
		<table style='width:100%'>
			<tr>
				<td class=legend>{country}:</td>
				<td style='font-size:12px'>{$vpn->array_country_codes[$vpn->main_array["GLOBAL"]["KEY_COUNTRY"]]}</td>
			</tr>
			<tr>
				<td class=legend>{change}:</td>
				<td>$country</td>
			</tr>
			<tr>
				<td class=legend>{province}:</td>
				<td>" . Field_text('KEY_PROVINCE',$vpn->main_array["GLOBAL"]["KEY_PROVINCE"],'width:220px')."</td>
			</tr>	
			<tr>
				<td class=legend>{city}:</td>
				<td>" . Field_text('KEY_CITY',$vpn->main_array["GLOBAL"]["KEY_CITY"],'width:220px')."</td>
			</tr>
			<tr>
				<td class=legend>{organization}:</td>
				<td>" . Field_text('KEY_ORG',$vpn->main_array["GLOBAL"]["KEY_ORG"],'width:220px')."</td>
			</tr>
			<tr>
				<td class=legend>{email}:</td>
				<td>" . Field_text('KEY_EMAIL',$vpn->main_array["GLOBAL"]["KEY_EMAIL"],'width:220px')."</td>
			</tr>														
			<tr>
				<td colspan=2 align='right'><input type='button' OnClick=\"javascript:SaveWizardKey()\" value='{next}&nbsp;&raquo;'></td>
			</tr>
	</tr>
	</table>
";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}

function wizard_server(){
$vpn=new openvpn();



	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/routing-domain-relay.png'></td>
		<td valign='top'>
		<H3>{PKI}</H3>
		<p class=capion>{WIZARD_SERVER}</p>
		</td>
		<table style='width:100%'>
			<tr>
				<td class=legend>{enable_openvpn_server_mode}:</td>
				<td style='font-size:12px'>" . Field_checkbox('ENABLE_SERVER','1',$vpn->main_array["GLOBAL"]["ENABLE_SERVER"])."</td>
			</tr>
			<tr>
				<td class=legend>{listen_port}:</td>
				<td>" . Field_text('LISTEN_PORT',$vpn->main_array["GLOBAL"]["LISTEN_PORT"],'width:90px')." UDP</td>
			</tr>
			<tr>
				<td colspan=2><br>
					<p class=caption>{LOCAL_NETWORK}</p>
				</td>
			<tr>
				<td class=legend>{from_ip_address}:</td>
				<td>" . Field_text('IP_START',$vpn->main_array["GLOBAL"]["IP_START"],'width:210px')."</td>
			<tr>
			<tr>
				<td class=legend>{netmask}:</td>
				<td>" . Field_text('NETMASK',$vpn->main_array["GLOBAL"]["NETMASK"],'width:210px')."</td>
			<tr>
					
			<tr>
				<td colspan=2 align='right'>
					<input type='button' OnClick=\"javascript:WizardFindMyNetworksMask()\" value='{newtork_help_me}'>
				</td>
			</tr>
				
			
				<td align='left'><input type='button' OnClick=\"javascript:StartWizard()\" value='&laquo;&nbsp;{back}'></td>
				<td align='right'><input type='button' OnClick=\"javascript:SaveWizardServer()\" value='{next}&nbsp;&raquo;'></td>
			</tr>
	</tr>
	</table>
";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function server_settings(){
	
$vpn=new openvpn();
$nic=new networking();
$sock=new sockets();
$OpenVpnPasswordCert=$sock->GET_INFO("OpenVpnPasswordCert");
if($OpenVpnPasswordCert==null){$OpenVpnPasswordCert="MyKey";}




//openvpn_access_interface

$DEV_TYPE=$vpn->main_array["GLOBAL"]["DEV_TYPE"];
	

$dev=Field_array_Hash(
	array("tun"=>"{routed_IP_tunnel}","tap0"=>"{ethernet_tunnel}"),
	"DEV_TYPE",$vpn->main_array["GLOBAL"]["DEV_TYPE"],
	"OpenVPNChangeServerMode()",null,0,'font-size:13px;padding:3px'
	);
	
	
$dev="{routed_IP_tunnel}<input type='hidden' name='DEV_TYPE' id='DEV_TYPE' value='tun'>";	
	

if($vpn->main_array["GLOBAL"]["IP_START"]==null){$vpn->main_array["GLOBAL"]["IP_START"]="10.8.0.0";}
if($vpn->main_array["GLOBAL"]["NETMASK"]==null){$vpn->main_array["GLOBAL"]["NETMASK"]="255.255.255.0";} 

			
				

if($vpn->main_array["GLOBAL"]["ENABLE_BRIDGE_MODE"]==1){$openvpn_local=null;}
$CLIENT_NAT_PORT=$vpn->main_array["GLOBAL"]["CLIENT_NAT_PORT"];

if($CLIENT_NAT_PORT==null){$CLIENT_NAT_PORT=$vpn->main_array["GLOBAL"]["LISTEN_PORT"];}
$vpn->main_array["GLOBAL"]["PUBLIC_IP"];
$vpn->main_array["GLOBAL"]["CLIENT_NAT_PORT"];
$vpn->main_array["GLOBAL"]["LISTEN_PORT"];
$vpn->main_array["GLOBAL"]["LISTEN_PROTO"];
$vpn->main_array["GLOBAL"]["ENABLE_SERVER"];
$vpn->main_array["GLOBAL"]["ENABLE_BRIDGE_MODE"];
$vpn->main_array["GLOBAL"]["IP_START"];
$vpn->main_array["GLOBAL"]["IPTABLES_ETH"];

if($vpn->main_array["GLOBAL"]["PUBLIC_IP"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{public_ip_addr}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["LISTEN_PORT"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{listen_port}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["CLIENT_NAT_PORT"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{listen_port}:{public_ip_addr}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["IP_START"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{from_ip_address}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["NETMASK"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{netmask}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["LISTEN_PROTO"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{protocol}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["IP_START"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{from_ip_address}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
if($vpn->main_array["GLOBAL"]["NETMASK"]==null){
$status=LocalParagraphe("MISSING_PARAMETER","{MISSING_PARAMETER_TEXT}<br><strong>{netmask}</strong>","Loadjs('index.openvpn.server.php')","42-red.png");
}
$LDAP_AUTH="{no}";
$EnableOpenVPNEndUserPage="{no}";
if($vpn->main_array["GLOBAL"]["LDAP_AUTH"]==1){$LDAP_AUTH="{yes}";}
if($sock->GET_INFO("EnableOpenVPNEndUserPage")==1){$EnableOpenVPNEndUserPage="{yes}";}

$wake_up_ip=$vpn->main_array["GLOBAL"]["WAKEUP_IP"];
$tcp_ip=new ip();
if(!$tcp_ip->isValid($wake_up_ip)){$wake_up_ip="{disabled}";}


$ahref_edit="<a href=\"javascript:blur();\" OnClick=\"Loadjs('index.openvpn.server.php');\" style='font-size:13px;text-decoration:underline'>";

$openvpn_local="
			<tr>
				<td class=legend style='font-size:13px'>$ahref_edit{openvpn_local}</a>:</td>
				<td style='font-size:13px'>$ahref_edit{$vpn->main_array["GLOBAL"]["LOCAL_BIND"]}</a></td>
				<td>&nbsp;</td>
			</tr>";	



$mandatories="<table style='width:100%' class=form>
			<tr>
				<td class=legend style='font-size:13px'>$ahref_edit{listen_port}:</a></td>
				<td style='font-size:13px'>$ahref_edit{$vpn->main_array["GLOBAL"]["LISTEN_PORT"]}&nbsp;{$vpn->main_array["GLOBAL"]["LISTEN_PROTO"]}</a></td>
				<td>&nbsp;</td>
			</tr>
			$openvpn_local
			<tr>
				<td class=legend style='font-size:13px'>$ahref_edit{public_ip_addr}</a>:</td>
				<td style='font-size:13px;'>$ahref_edit{$vpn->main_array["GLOBAL"]["PUBLIC_IP"]}:$CLIENT_NAT_PORT</a></td>
				<td>&nbsp;</td>
			<tr>	
			<tr>
				<td class=legend style='font-size:13px'>$ahref_edit{password}:</a></td>
				<td style='font-size:13px'>$ahref_edit*****</a></td>
				<td>&nbsp;</td>
			<tr>
			</table>";
$proxy="{no}";
if($vpn->main_array["GLOBAL"]["USE_RPROXY"]==1){
	$proxy="{$vpn->main_array["GLOBAL"]["PROXYADDR"]}:{$vpn->main_array["GLOBAL"]["PROXYPORT"]}";
}	

if($vpn->main_array["GLOBAL"]["IPTABLES_ETH"]==null){$vpn->main_array["GLOBAL"]["IPTABLES_ETH"]="{no}";}
				
$mode_tun="<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{enable_authentication}</a>:</td>
		<td style='font-size:13px'>$ahref_edit{$LDAP_AUTH}</td>
		<td>&nbsp;</td>
	<tr>
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{EnableOpenVPNEndUserPage}</a>:</td>
		<td style='font-size:13px'>$ahref_edit{$EnableOpenVPNEndUserPage}</td>
		<td>&nbsp;</td>
	<tr>	
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{reverse_proxy}</a>:</td>
		<td style='font-size:13px'>$ahref_edit$proxy</td>
		<td>&nbsp;</td>
	<tr>	
	
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{from_ip_address}</a>:</td>
		<td style='font-size:13px'>$ahref_edit{$vpn->main_array["GLOBAL"]["IP_START"]}</td>
		<td>&nbsp;</td>
	<tr>
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{netmask}</a>:</td>
		<td style='font-size:13px'>$ahref_edit{$vpn->main_array["GLOBAL"]["NETMASK"]}</a></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{openvpn_access_interface}</a>:</td>
		<td style='font-size:13px'>$ahref_edit{$vpn->main_array["GLOBAL"]["IPTABLES_ETH"]}</a></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>$ahref_edit{wake_up_ip}</a>:</td>
		<td style='font-size:13px'>$ahref_edit$wake_up_ip</a></td>
		<td>&nbsp;</td>
	</tr>	
	
</table>";	

	$VPN_SERVER_IP=$vpn->main_array["GLOBAL"]["VPN_SERVER_IP"];
	$VPN_DHCP_FROM=$vpn->main_array["GLOBAL"]["VPN_DHCP_FROM"];
	$VPN_DHCP_TO=$vpn->main_array["GLOBAL"]["VPN_DHCP_TO"];
	

	$tcp=new networking();
	if($vpn->main_array["GLOBAL"]["BRIDGE_ETH"]==null){$vpn->main_array["GLOBAL"]["BRIDGE_ETH"]="eth0";}
	$array_ip=$tcp->GetNicInfos($vpn->main_array["GLOBAL"]["BRIDGE_ETH"]);
	if($vpn->main_array["GLOBAL"]["VPN_SERVER_IP"]==null){$vpn->main_array["GLOBAL"]["VPN_SERVER_IP"]=$array_ip["IPADDR"];}
	if($vpn->main_array["GLOBAL"]["NETMASK"]==null){$vpn->main_array["GLOBAL"]["NETMASK"]=$array_ip["NETMASK"];}
	
if($vpn->main_array["GLOBAL"]["ENABLE_BRIDGE_MODE"]==1){
	
	$nics=Field_array_Hash($vpn->virtual_ip_lists(),'BRIDGE_ETH',$vpn->main_array["GLOBAL"]["BRIDGE_ETH"]);
}

$mode_tap="
	<div style='width:100%;margin-bottom:5px'>
		<table style='width:100%'>
			<tr>
				<td valign='top'>
					<div id='nicvpninfo' style='float:right;margin:5px;'>".ShowIPConfig($vpn->main_array["GLOBAL"]["BRIDGE_ETH"])."</div>
				</td>
				<td valign='top'>
					<div class=explain>{SERVER_MODE_TAP}</div>
				</td>
			</tr>
		</table>
	</div>
<table style='width:100%'>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{BRIDGE_ETH}:</td>
		<td width=1% nowrap>$nics</td>
		<td align='left' width=1% nowrap>". texttooltip("{add_virtual_ip_address}","{add_virtual_ip_address}","Loadjs('system.nic.config.php?js-add-nic=yes')",null,0,"font-size:13px;padding:3px")."</td>
	<tr>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{BRIDGE_ADDR}:</td>
		<td width=1% nowrap>" . Field_text('BRIDGE_ADDR',$vpn->main_array["GLOBAL"]["BRIDGE_ADDR"],'width:120px;font-size:13px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend nowrap style='font-size:13px'>{VPN_DHCP_FROM}:</td>
		<td width=1% nowrap>" . Field_text('VPN_DHCP_FROM',$vpn->main_array["GLOBAL"]["VPN_DHCP_FROM"],'width:120px;font-size:13px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{VPN_DHCP_TO}:</td>
		<td width=1% nowrap>" . Field_text('VPN_DHCP_TO',$vpn->main_array["GLOBAL"]["VPN_DHCP_TO"],'width:120px;font-size:13px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>			
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveServerSettings()")."</td>
	</tr>				
</table>
";

$mode=$mode_tun;
if($vpn->main_array["GLOBAL"]["DEV_TYPE"]=="tap0"){
	$mode=$mode_tap;
}

if($vpn->main_array["GLOBAL"]["ENABLE_BRIDGE_MODE"]==1){
	$mode=$mode_tap;
}

$schedule=LocalParagraphe("OPENVPN_SCHEDULE_RUN","OPENVPN_SCHEDULE_RUN_TEXT","Loadjs('index.openvpn.schedule.php')",
"ScheduleSettings-48.png");

if($vpn->main_array["GLOBAL"]["ENABLE_SERVER"]<>1){$status=LocalParagraphe("OPENVPN_NOT_ENABLED","OPENVPN_NOT_ENABLED_TEXT","Loadjs('index.openvpn.server.php')","warning42.png");}

if($status==null){
	$status=LocalParagraphe("OPENVPN_APPLY_CONFIG","OPENVPN_APPLY_CONFIG_TEXT","YahooWin3(500,'index.openvpn.php?restart-server=yes','{APP_OPENVPN_APPLY}')","reconfigure-42.png");
	$script=LocalParagraphe("BUILD_OPENVPN_CLIENT_CONFIG","BUILD_OPENVPN_CLIENT_CONFIG_TEXT","Loadjs('index.openvpn.build.client.php')","script-42.png");
	
}

$html="
<table style='width:100%'>
<tr>
<td valign='top'>
<div id='OPENVPN_SERVER_SETTINGS'>
<table style='width:100%'>
		<tr>
			<td valign='top' style='padding-right:5px;border-right:5px solid #CCCCCC'>$script$status$schedule</td>
			<td valign='top'>
			<div style='font-size:16px'>{service_parameters}</div>
			$mandatories$mode</td>
		</tr>
		</table>
		
	</div>
</td>
</tr>
</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
// openvpn --remote touzeau.ath.cx --port 1194 --dev tun --comp-lzo --tls-client --ca /home/dtouzeau/ca.crt --cert /home/dtouzeau/dtouzeau.crt --key /home/dtouzeau/dtouzeau.key --verb 5 --pull	
}

function ShowIPConfig($eth){
	
	
	$openvpn=new openvpn();
	$array_ip=$openvpn->virtual_ip_information();
	
	
	$html="<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{BRIDGE_ETH}:</td>
		<td><span style='font-weight:bold;font-size:11px'>$eth</span></td>
	</tr>	
	<tr>
		<td class=legend nowrap>{ip_address}:</td>
		<td><span style='font-weight:bold;font-size:11px'>{$array_ip["IPADDR"]}</span></td>
	</tr>
	<tr>
		<td class=legend nowrap>{netmask}:</td> 
		<td><span style='font-weight:bold;font-size:11px'>{$array_ip["NETMASK"]}</span></td>
	</tr>	
	<tr>
		<td class=legend nowrap>{gateway}:</td>
		<td><span style='font-weight:bold;font-size:11px'>{$array_ip["GATEWAY"]}</span></td>
	</tr>		
	</table>";
	$tpl=new templates();
	return RoundedLightGrey($tpl->_ENGINE_parse_body($html)); 
	
	
}


function Clients_settings(){
	$vpn=new openvpn();
	$VPN_SERVER_IP=Field_text('VPN_SERVER_IP',$vpn->main_array["GLOBAL"]["VPN_SERVER_IP"],'width:120px');
	$VPN_DHCP_FROM=Field_text('VPN_DHCP_FROM',$vpn->main_array["GLOBAL"]["VPN_DHCP_FROM"],'width:120px');
	$VPN_DHCP_TO=Field_text('VPN_DHCP_TO',$vpn->main_array["GLOBAL"]["VPN_DHCP_TO"],'width:120px');	
	
	$ip=new IP;
	if(preg_match('#(.+?)\.([0-9]+)$#',$vpn->main_array["GLOBAL"]["VPN_DHCP_FROM"],$re)){
		$cdir=$ip->ip2cidr("{$re[1]}.0","{$re[1]}.255");
		
	}
	
	
if($vpn->main_array["GLOBAL"]["VPN_SERVER_DHCP_MASK"]==null){$vpn->main_array["GLOBAL"]["VPN_SERVER_DHCP_MASK"]=$vpn->main_array["GLOBAL"]["NETMASK"];}	
	
$html=$html="
<H1>{OPENVPN_CLIENT_SETTINGS}</H1>
<table style='width:100%'>
<tr>
	<td valign='top'><img src='img/global-settings.png'></td>
	<td valign='top'>
<div id='OPENVPN_CLIENT_SETTINGS'>
<table style='width:100%'><tr>
				<td class=legend>{VPN_SERVER_IP}:</td>
				<td>$VPN_SERVER_IP</td>
			<tr>
			<tr>
				<td class=legend>{VPN_SERVER_DHCP}:</td>
				<td align='left'>
					<table style='width:90%'>
						<tr>
							<td class=legend>{from}:</td><td>$VPN_DHCP_FROM</td><td class=legend>{to}:</td><td>$VPN_DHCP_TO</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class=legend>{VPN_SERVER_DHCP_MASK}:</td>
				<td>".Field_text('VPN_SERVER_DHCP_MASK',$vpn->main_array["GLOBAL"]["VPN_SERVER_DHCP_MASK"],'width:120px')."&nbsp;cdir:$cdir</td>
			<tr>
			<tr>
				<td class=legend>{dns_server} 1:</td>
				<td>".Field_text('VPN_DNS_DHCP_1',$vpn->main_array["GLOBAL"]["VPN_DNS_DHCP_1"],'width:120px')."</td>
			<tr>
			<tr>
				<td class=legend>{dns_server} 2:</td>
				<td>".Field_text('VPN_DNS_DHCP_2',$vpn->main_array["GLOBAL"]["VPN_DNS_DHCP_2"],'width:120px')."</td>
			<tr>																	
			<tr>
				<td colspan=2 align='right'>
					<hr>
					". button("{apply}",":SaveServerSettings()")."
					
				</td>
			</tr>
			</table></div>
			</td>
			</tr>
			</table>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);				
	
}

function wizard_finish(){
	$apply=ICON_OPENVPN_APPLY();
	$html="<H3>{WIZARD_FINISH}</H3>
	<p class=caption>{WIZARD_FINISH_TEXT}</p>
	<center>$apply</center>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function OpenVPNChangeServerMode(){
	$DEV_TYPE=$_GET["OpenVPNChangeServerMode"];
	$vpn=new openvpn();
	$vpn->main_array["GLOBAL"]["DEV_TYPE"]=$DEV_TYPE;
	$vpn->Save();
	$tpl=new templates();
	//echo $tpl->_ENGINE_parse_body('{success}: {switch}=> '.$DEV_TYPE);	
	}

function SaveServerConf(){
	$tpl=new templates();
	if(isset($_GET["OpenVpnPasswordCert"])){
		$sock=new sockets();
		$oldpassword=$sock->GET_INFO("OpenVpnPasswordCert");
		if($oldpassword==null){$oldpassword="MyKey";}
		if($oldpassword<>$_GET["OpenVpnPasswordCert"]){
			echo $tpl->javascript_parse_text("{OPENVPN_PASSWORD_CHANGED}");
			
		}
		
		$sock->SET_INFO("OpenVpnPasswordCert",$_GET["OpenVpnPasswordCert"]);
	}
	
	$vpn=new openvpn();
	while (list ($num, $ligne) = each ($_GET) ){
		$vpn->main_array["GLOBAL"][$num]=$ligne;
		
	}
	$vpn->Save();	
	
	
	}


function SaveCertificate(){
	
	$vpn=new openvpn();
	while (list ($num, $ligne) = each ($_GET) ){
		$vpn->main_array["GLOBAL"][$num]=$ligne;
		
	}
	$vpn->Save();
	$vpn->BuildCertificate();
	
}

function routes_settings(){
	$list=routes_list(1);
	$html="
	<div class=explain>{routes_explain}</div>
	<table style='width:100%' class=form>
	 <tr>
	 	<td class=legend>{from_ip_address}:</td>
	 	<td>" . Field_text('ROUTE_FROM',null,'width:110px;font-size:13px;padding:3px',null,'RouteShouldbe()',null,false,'RouteShouldbe()')."</td>
	 </tr>
	<tr>
	 	<td class=legend>{netmask}:</td>
	 	<td>" . Field_text('ROUTE_MASK',null,'width:110px;font-size:13px;padding:3px')."</td>
	 </tr>	
	<tr>
	<td colspan=2 class='legend' style='padding-right:50px'><span id='shouldbe'></span></td>
	<tr>
		<td colspan=2 align='right' ><hr>". button("{add}","OpenVpnAddRoute()")."</td>
	</tr>
	</table>
	<br>
	<div style='width:100%;height:150px;overflow:auto' id='routeslist'>$list</div>";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function RestartServer(){
	$sock=new sockets();
	$datas=$sock->getfile("RestartOpenVPNServer");
	$tbl=explode("\n",$datas);
	if(is_array($tbl)){
	$tbl=array_reverse($tbl);
	
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$RRR[$ligne]=$ligne;
			
		}
	
	while (list ($num, $ligne) = each ($RRR) ){
		$l=$l."<div><code style='font-size:10px'>" . htmlentities($ligne)."</code></div>";
	}}
	
	
	$html="<div style='width:100%;height:200px;overflow:auto'>$l</div>";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function status($noecho=0){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?openvpn-status=yes")));
	$status=DAEMON_STATUS_ROUND("OPENVPN_SERVER",$ini);
	
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?openvpn-clients-status=yes")));
	if(is_array($ini->_params)){
		while (list ($key, $ligne) = each ($ini->_params)){
		$status=$status.DAEMON_STATUS_ROUND($key,$ini);
		}			
			
	}
	
	
	
	if($noecho==1){return $status;}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($status);
	}
		
}

function ncc(){
	
	$net=new networking();
	$ip=new IP();
	$vpn=new openvpn();
	
$nic=new networking();

while (list ($num, $ligne) = each ($nic->array_TCP) ){
	if($ligne==null){continue;}
		$ethi[$num]=$ligne;
	}  	
	
	// LOCAL_NETWORK IP_START NETMASK  
	$listen_eth=$vpn->main_array["GLOBAL"]["BRIDGE_ETH"];
	$local_ip=$net->array_TCP[$listen_eth];
	$listen_eth_ip=$local_ip;
	$public_ip=$vpn->main_array["GLOBAL"]["PUBLIC_IP"];
	$LISTEN_PORT=$vpn->main_array["GLOBAL"]["LISTEN_PORT"];
	$LISTEN_PROTO=$vpn->main_array["GLOBAL"]["LISTEN_PROTO"];
	$VPN_SERVER_IP=$vpn->main_array["GLOBAL"]["VPN_SERVER_IP"];
	$VPN_DHCP_FROM=$vpn->main_array["GLOBAL"]["VPN_DHCP_FROM"];
	$VPN_DHCP_TO=$vpn->main_array["GLOBAL"]["VPN_DHCP_TO"];
	$VPN_DNS_DHCP_1=$vpn->main_array["GLOBAL"]["VPN_DNS_DHCP_1"];
	$VPN_DNS_DHCP_2=$vpn->main_array["GLOBAL"]["VPN_DNS_DHCP_2"];
	$PUBLIC_IP=$vpn->main_array["GLOBAL"]["PUBLIC_IP"];
	$IPTABLES_ETH=$vpn->main_array["GLOBAL"]["IPTABLES_ETH"];
	$DEV_TYPE=$vpn->main_array["GLOBAL"]["DEV_TYPE"];
	$IP_START=$vpn->main_array["GLOBAL"]["IP_START"];
	$CLIENT_NAT_PORT=$vpn->main_array["GLOBAL"]["CLIENT_NAT_PORT"];
	
	$VPN_SERVER_DHCP_MASK=$vpn->main_array["GLOBAL"]["VPN_SERVER_DHCP_MASK"];
	if($local_ip==null){$listen_eth_ip="<span style='color:red'>{error}</span>";}
	if($public_ip==null){$public_ip="<span style='color:white'>{error}</span>";}
	
	if($VPN_SERVER_IP==null){$VPN_SERVER_IP="<span style='color:red'>{error}</span>";}
	if($VPN_DHCP_FROM==null){$VPN_DHCP_FROM="<span style='color:red'>{error}</span>";}
	if($VPN_DHCP_TO==null){$VPN_DHCP_TO="<span style='color:red'>{error}</span>";}
	if($VPN_SERVER_DHCP_MASK==null){$VPN_SERVER_DHCP_MASK="<span style='color:red'>{error}</span>";}
	
	if($CLIENT_NAT_PORT==null){$CLIENT_NAT_PORT=$LISTEN_PORT;}
	
	
	if($IPTABLES_ETH<>null){$VPN_SERVER_IP=$ethi[$IPTABLES_ETH];}
	
	if($LISTEN_PORT==null){$LISTEN_PORT="<span style='color:red'>{error}</span>";}
	
	$listen_eth="$listen_eth  (br0)<br>$listen_eth_ip";
	if($listen_eth==null){$listen_eth="<span style='color:red'>{error}</span>";}
	
	if($DEV_TYPE=='tun'){
		$listen_eth=" $VPN_SERVER_IP <-> tun0 iptables";
		$VPN_DHCP_FROM=$IP_START;
		if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$VPN_DHCP_FROM,$re)){
		$VPN_DHCP_FROM="<span style='color:red'>{error}</span>";
		}else{
		$cdir=$ip->ip2cidr("{$re[1]}.{$re[2]}.{$re[3]}.0","{$re[1]}.{$re[2]}.{$re[3]}.255");
		$tb=explode("/",$cdir);
		$v4=new ipv4($tb[0],$tb[1]);
		$VPN_DHCP_FROM="{$re[1]}.{$re[2]}.{$re[3]}.2";
		$VPN_DHCP_TO="{$re[1]}.{$re[2]}.{$re[3]}.254";
		$VPN_SERVER_DHCP_MASK="{$tb[0]} - " . $v4->netmask();
		}
	}
	
	if($VPN_SERVER_IP==null){$VPN_SERVER_IP="<span style='color:red'>{error}</span>";}
	if($VPN_DHCP_FROM==null){$VPN_DHCP_FROM="<span style='color:red'>{error}</span>";}
	if($VPN_DHCP_TO==null){$VPN_DHCP_TO="<span style='color:red'>{error}</span>";}
	if($VPN_SERVER_DHCP_MASK==null){$VPN_SERVER_DHCP_MASK="<span style='color:red'>{error}</span>";}	
	
	
	if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$local_ip,$re)){
		$local_network="<span style='color:red'>{error}</span>";
	}else{
		$cdir=$ip->ip2cidr("{$re[1]}.{$re[2]}.{$re[3]}.0","{$re[1]}.{$re[2]}.{$re[3]}.255");
		$tb=explode("/",$cdir);
		$v4=new ipv4($tb[0],$tb[1]);
		$local_network="{$tb[0]} - " . $v4->netmask();
	}
	
	$sql="SELECT * FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["IP_START"];
		$mask=$ligne["netmask"];
		if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$ip,$re)){continue;}
		$route[]="<span style='font-size:10px'>route {$re[1]}.{$re[2]}.{$re[3]}.0 $mask GW $VPN_SERVER_IP</span>";
		
	}
	
if(is_array($route)){
	$routes=implode("<br>",$route);
}

	
	$html="
	<H1>{NETWORK_CONTROL_CENTER}</H1>
	<div style='background-image:url(img/bg_vpn1.png);width:750px;height:420px;background-repeat:no-repeat;font-size:13px'></div>
	<div style='position:absolute;top:30px;left:700px;'><input type='button' OnClick=\"javascript:OpenVPNNCC()\" value='{refresh}'></div>
	<div style='position:absolute;top:240px;left:210px;font-size:14px;text-align:center'>{BRIDGE_ETH}<br>$listen_eth</div>
	<div style='position:absolute;top:450px;left:80px;font-size:14px;text-align:center'>{local_network}<br>$local_network<br>$routes</div>
	<div style='position:absolute;top:125px;left:410px;font-size:14px;text-align:center;color:black;background-color:#D7E4FB;padding:3px;border:1px solid black'>
		{public_ip_addr}<br>$public_ip<br>{listen_port}:$LISTEN_PORT:$CLIENT_NAT_PORT ($LISTEN_PROTO)
	</div>
	<div style='position:absolute;top:125px;left:230px;font-size:14px;text-align:center;'>{VPN_SERVER_IP}<br>$VPN_SERVER_IP</div>
	<div style='position:absolute;top:190px;left:580px;font-size:12px;text-align:center;;background-color:#FFFF99;border:1px solid black;padding:3px'>
		DHCP<br>$VPN_DHCP_FROM - $VPN_DHCP_TO
		<br>{netmask} $VPN_SERVER_DHCP_MASK<br>
		{dns_servers}:$VPN_DNS_DHCP_1 $VPN_DNS_DHCP_2
	</div>
	
	";
	

	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function SaveBridgeMode(){
	$vpn=new openvpn();
	$vpn->main_array["GLOBAL"]["ENABLE_BRIDGE_MODE"]=$_GET["ENABLE_BRIDGE"];
	$vpn->Save();
}

function LocalParagraphe($title,$text,$js,$img){
		$js=str_replace("javascript:","",$js);
		$id=md5($js);
		$img_id="{$id}_img";
		if(strpos($text,"}")==0){$text="{{$text}}";}
		
		
	$html="
	<table style='width:198px;'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($img,$text,"$js",null,$img_id)."</td>
	<td><strong style='font-size:12px;font-family:Verdana;letter-spacing:-1px'>{{$title}}</strong><div style='font-size:10px;font-family:Verdana;letter-spacing:-1px'>$text</div></td>
	</tr>
	</table>";
	

return "<div style=\"width:200px;margin:2px\" 
	OnMouseOver=\"javascript:ParagrapheWhiteToYellow('$id',0);this.style.cursor='pointer';\" 
	OnMouseOut=\"javascript:ParagrapheWhiteToYellow('$id',1);this.style.cursor='auto'\" OnClick=\"javascript:$js\">
  <b id='{$id}_1' class=\"RLightWhite\">
  <b id='{$id}_2' class=\"RLightWhite1\"><b></b></b>
  <b id='{$id}_3' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_4' class=\"RLightWhite3\"></b>
  <b id='{$id}_5' class=\"RLightWhite4\"></b>
  <b id='{$id}_6' class=\"RLightWhite5\"></b></b>

  <div id='{$id}_0' class=\"RLightWhitefg\" style='padding:2px;'>
   $html
  </div>

  <b id='{$id}_7' class=\"RLightWhite\">
  <b id='{$id}_8' class=\"RLightWhite5\"></b>
  <b id='{$id}_9' class=\"RLightWhite4\"></b>
  <b id='{$id}_10' class=\"RLightWhite3\"></b>
  <b id='{$id}_11' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_12' class=\"RLightWhite1\"><b></b></b></b>
</div>
";		
		
	
}


?>