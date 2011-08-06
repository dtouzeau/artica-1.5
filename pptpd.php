<?php
	session_start();
	if($_SESSION["uid"]==null){
		if(count($_GET)>0){echo "<script>window.location.href ='logoff.php';</script>";die();}
		echo "window.location.href ='logoff.php';";die();
	}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}


	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["events"])){events();exit;}
	
	
	if(isset($_GET["pptpd-status"])){pptpd_status();exit;}
	if(isset($_GET["pptpd-clients-status"])){clients_status();exit;}
	
	
	if(isset($_GET["server"])){server_config();exit;}
	if(isset($_GET["EnablePPTPDVPN"])){server_config_save();exit;}
	if(isset($_GET["members"])){members();exit;}
	if(isset($_GET["vpn-users"])){vpn_users();exit;}
	if(isset($_GET["local-users"])){local_users();exit;}
	if(isset($_GET["VPNAddMember"])){vpn_add_member();exit;}
	if(isset($_GET["VPNDelMember"])){vpn_del_member();exit;}
	if(isset($_GET["client"])){client_popup();exit;}
	
	if(isset($_GET["add-vpn-con-js"])){add_vpn_js();exit;}
	if(isset($_GET["add-vpn-con-popup"])){add_vpn_popup();exit;}
	if(isset($_GET["pptp-connexions"])){connexions_list();exit;}
	if(isset($_GET["CONNEXION_NAME"])){add_vpn_save();exit;}
	if(isset($_GET["VPNDelCon"])){add_vpn_del();exit;}
	
	if(isset($_GET["vpnUserAssign"])){vpn_add_member_ip();exit;}
	if(isset($_GET["route_ip"])){add_vpn_popup_cdir();exit;}
	if(isset($_GET["vpn-client-routes-list"])){addvpn_routes_popup_list();exit;}
	if(isset($_GET["vpn-client-routes-del"])){addvpn_routes_del();exit;}
	if(isset($_GET["route_real"])){add_vpn_routes();exit;}
	
	if(isset($_GET["server-routes-delete"])){server_routes_del();exit;}
	if(isset($_GET["server_route_real"])){server_routes_add();exit;}
	if(isset($_GET["server-routes"])){server_routes_list();exit;}
	
	
	
	
js();


function add_vpn_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ADD_CONNEXION}");
	if($_GET["name"]<>null){
		$title=base64_decode($_GET["name"]);
	}
	$html="
		YahooWin2(450,'$page?add-vpn-con-popup=yes&name={$_GET["name"]}','$title');
	
	";
	echo $html;
}

function js(){
	$page=CurrentPageName();
	
$html="	
	function PPTPDIndexLoadpage(){
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes');
	}
	PPTPDIndexLoadpage();
	
	
	
	
";	
	
echo $html;	
}

function connexions_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>{connexions}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	if(is_array($array)){
		while (list ($conexionname, $m_array) = each ($array) ){
			$conexionname_enc=base64_encode($conexionname);
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$open=imgtootltip("online-con-32.png","{edit}","Loadjs('$page?add-vpn-con-js=yes&name=$conexionname_enc');");
			$delete=imgtootltip("delete-32.png","{delete}","VPNDelCon('$conexionname_enc')");
			$color="black";
			if($m_array["ENABLED"]<>1){$color="#A29B9B";}
			
			$html=$html."
			<tr class=$classtr>
			<td width=1%>$open</td>
			<td><strong style='font-size:14px;color:$color' >$conexionname</td>
			<td><strong style='font-size:14px;color:$color'>{$m_array["vpn_servername"]}</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
	}
	
	$html=$html."</tbody></table>
	
	<script>
	var x_VPNDelCon= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshConexionList();
	}		
		function VPNDelCon(con){
			var XHR = new XHRConnection();
			XHR.appendData('VPNDelCon',con);	
			document.getElementById('pptp-connexions').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_VPNDelCon);	
		}
		
	
	function RefreshClientsVPNStatus(){
		LoadAjax('clients-status2','$page?pptpd-clients-status=yes');
	}
			
	RefreshClientsVPNStatus();
	</script>
		
	
	";	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function events(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$q=new mysql();
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{date}</th>
	<th>{connection}</th>
	<th colspan=2>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
		$sql="SELECT * FROM vpn_events ORDER BY `stime` DESC LIMIT 0,250";
		$results=$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$date=date("Y-m-d H:i:s",$ligne["stime"]);
			
			$ok="32-infos.png";
			if(preg_match("#failed#",$ligne["subject"])){$ok="danger32.png";}
			
			$html=$html."
			<tr class=$classtr>
			<td width=1% nowrap style='font-size:13px'>$date</td>
			<td width=1% nowrap style='font-size:14px'><strong>{$ligne["IPPARAM"]}</strong></td>
			<td width=50%><strong style='font-size:11px'><code>{$ligne["subject"]}</code></strong></td>
			<td width=1%>". imgtootltip("$ok",$ligne["text"])."</td>
			</tr>
			";			
			
			
		}
	$html=$html."</tbody></table>
		<div style='margin:20px;text-align:right'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_pptpd')")."</div>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function add_vpn_tabs(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["connection"]="{connection}";
	$array["routes"]="{iproutes}";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?add-vpn-con-popup=yes&$num=yes&name={$_GET["name"]}\"><span>$ligne</span></a></li>\n");
	}
	
	$id=time();
	
	echo "
	<div id='$id' style='width:100%;height:400px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#$id').tabs({
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

function add_vpn_popup_cdir(){
	
	$ip=$_GET["route_ip"];
	$mask=$_GET["route_netmask"];
	if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$ip)){return;}
	if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$mask)){return;}
	$sock=new sockets();
	$calc=base64_encode("$ip/$mask");
	echo base64_decode($sock->getFrameWork("cmd.php?cdir-calc=$calc"));
}

function add_vpn_popup_routes(){
	$CONNEXION=base64_decode($_GET["name"]);
	$page=CurrentPageName();
	$tpl=new templates();

	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{ip}:</td>
		<td valign='top'>". Field_text("route_ip",null,"font-size:13px;padding:3px",null,"CalcRouteCDIR()")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{netmask}:</td>
		<td valign='top'>". Field_text("route_netmask",null,"font-size:13px;padding:3px",null,"CalcRouteCDIR()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{cdir}:</td>
		<td valign='top'>". Field_text("route_real",null,"font-size:13px;padding:3px",null)."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{gateway_vpn_server}:</td>
		<td valign='top'>". Field_checkbox("use_vpn_server",1,1,"RouteDisableFree()",null)."</td>
	</tr>		
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{gateway}:</td>
		<td valign='top'>". Field_text("gateway",null,"font-size:13px;padding:3px",null)."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","SaveNewRoute()")."</td>
	</tr>
	</table>
	<div style='margin-top:9px;height:150px;overflow:auto' id='vpn-routes-list'></div>
	
	<script>
		function RouteDisableFree(){
			document.getElementById('gateway').disabled=true;
			if(document.getElementById('gateway').checked){
				document.getElementById('gateway').disabled=false;
			}
		}
		
	var x_CalcRouteCDIR= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){document.getElementById('route_real').value=tempvalue;}
		}			
		
		function CalcRouteCDIR(){
			var XHR = new XHRConnection();
			XHR.appendData('route_ip',document.getElementById('route_ip').value);
			XHR.appendData('route_netmask',document.getElementById('route_netmask').value);	
			XHR.sendAndLoad('$page', 'GET',x_CalcRouteCDIR);
		}
		
		
	var x_SaveNewRoute= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			RefreshRoutesList();
		}			
		
		function SaveNewRoute(){
			var tempvalue=document.getElementById('route_real').value;
			if(tempvalue.length>3){
				var XHR = new XHRConnection();
				XHR.appendData('route_real',document.getElementById('route_real').value);
				if(document.getElementById('use_vpn_server').checked){XHR.appendData('use_vpn_server',1);}else{XHR.appendData('use_vpn_server',0);}
				XHR.appendData('gateway',document.getElementById('gateway').value);
				XHR.appendData('name','{$_GET["name"]}');
				XHR.sendAndLoad('$page', 'GET',x_SaveNewRoute);			
			}
		
		}
		
		function RefreshRoutesList(){
			LoadAjax('vpn-routes-list','$page?vpn-client-routes-list={$_GET["name"]}');
		}
	
		RouteDisableFree();
		RefreshRoutesList();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function addvpn_routes_popup_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$name=base64_decode($_GET["vpn-client-routes-list"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	

	$routes=$array[$name]["ROUTES"];
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=5>{routes}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	
	if(is_array($routes)){
		while (list ($route, $conf) = each ($routes) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			if($conf["use_vpn_server"]==1){$conf["gateway"]="{vpn_server}";}
			$route_en=base64_encode($route);
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=50%><strong style='font-size:13px'>$route</strong></td>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=50%><strong style='font-size:13px'>{$conf["gateway"]}</strong></td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","RouteVPNDelete('$route_en')")."</td>
			</tr>
			";
		}
	}

	$html=$html."</table>
	
	<script>
		
	
	var x_RouteVPNDelete= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshRoutesList();
		}			
	
		function RouteVPNDelete(route_enc){
			var XHR = new XHRConnection();
			XHR.appendData('name','{$_GET["vpn-client-routes-list"]}');
			XHR.appendData('vpn-client-routes-del',route_enc);
			document.getElementById('vpn-routes-list').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RouteVPNDelete);		
		
		}
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function addvpn_routes_del(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
	$con=base64_decode($_GET["name"]);	
	$route=base64_decode($_GET["vpn-client-routes-del"]);
	unset($array[$con]["ROUTES"][$route]);
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPVpnClients");
}

function add_vpn_routes(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
	$con=base64_decode($_GET["name"]);
	$array[$con]["ROUTES"][$_GET["route_real"]]=array("use_vpn_server"=>$_GET["use_vpn_server"],"gateway"=>$_GET["gateway"]);
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPVpnClients");
}


function add_vpn_popup(){
	
	if(isset($_GET["connection"])){add_vpn_popup_form();exit;}
	if(isset($_GET["routes"])){add_vpn_popup_routes();exit;}
	
	if(trim($_GET["name"])<>null){add_vpn_tabs();exit;}
	add_vpn_popup_form();	
}


function add_vpn_popup_form(){
	$page=CurrentPageName();
	$tpl=new templates();
		
	if(trim($_GET["name"])<>null){
			$sock=new sockets();
			$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
			$con=base64_decode($_GET["name"]);
			$PPTPDConfig=$array[$con];
	}
	
	
	$ip=new networking();
	while (list ($eth, $ip_addr) = each ($ip->array_TCP) ){
		if(trim($ip_addr)==null){continue;}
		if(preg_match("#ppp#",$eth)){continue;}
		$bcrelay_ar[$eth]="$eth ($ip_addr)";
		
	}	
	
	$html="
	<div id='pptcondiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{enabled}:</td>
		<td>".Field_checkbox("con_enabled",1,$PPTPDConfig["ENABLED"],"PPTPDConnexionDisabled()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{connexion_name}:</td>
		<td>". Field_text("CONNEXION_NAME",$PPTPDConfig["CONNEXION_NAME"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{vpn_server_addr}:</td>
		<td>". Field_text("vpn_servername",$PPTPDConfig["vpn_servername"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{username}:</td>
		<td>". Field_text("username",$PPTPDConfig["username"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{password}:</td>
		<td>". Field_password("password",$PPTPDConfig["password"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>LAN-to-LAN:</td>
		<td>".Field_checkbox("LANTOLAN",1,$PPTPDConfig["LANTOLAN"],"SwitchLanToLan()")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{link_net_to}:</td>
		<td>". Field_array_Hash($bcrelay_ar,"ETH_LINK",$PPTPDConfig["ETH_LINK"],null,null,0,"font-size:13px;padding:3px;width:150px")."</td>
	</tr>				
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SavePPTPDConnexion()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
	var x_SavePPTPDConnexion= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin2Hide();
			if(document.getElementById('main_config_pptpd')){RefreshTab('main_config_pptpd');}
	}			
		
	function SavePPTPDConnexion(){
		var XHR = new XHRConnection();
		if(document.getElementById('con_enabled').checked){XHR.appendData('ENABLED',1);}else{XHR.appendData('ENABLED',0);}
		if(document.getElementById('LANTOLAN').checked){XHR.appendData('LANTOLAN',1);}else{XHR.appendData('LANTOLAN',0);}
		XHR.appendData('ETH_LINK',document.getElementById('ETH_LINK').value);
		XHR.appendData('CONNEXION_NAME',document.getElementById('CONNEXION_NAME').value);
		XHR.appendData('vpn_servername',document.getElementById('vpn_servername').value);
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',document.getElementById('password').value);
		document.getElementById('pptcondiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePPTPDConnexion);	
	}
	
	function PPTPDConnexionDisabled(){
		document.getElementById('CONNEXION_NAME').disabled=true;
		document.getElementById('vpn_servername').disabled=true;
		document.getElementById('username').disabled=true;
		document.getElementById('password').disabled=true;
		document.getElementById('ETH_LINK').disabled=true;
		document.getElementById('LANTOLAN').disabled=true;
		if(document.getElementById('con_enabled').checked){
			document.getElementById('CONNEXION_NAME').disabled=false;
			document.getElementById('vpn_servername').disabled=false;
			document.getElementById('username').disabled=false;
			document.getElementById('password').disabled=false;	
			document.getElementById('ETH_LINK').disabled=false;	
			document.getElementById('LANTOLAN').disabled=false;
		
		}
	
	}
	
	function SwitchLanToLan(){
	document.getElementById('ETH_LINK').disabled=true;
		if(document.getElementById('con_enabled').checked){
			if(document.getElementById('LANTOLAN').checked){document.getElementById('ETH_LINK').disabled=false;}
			}
	}
	
	
	PPTPDConnexionDisabled();
	SwitchLanToLan();
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function add_vpn_save(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));
	
	while (list ($num, $ligne) = each ($_GET) ){
		$array[$_GET["CONNEXION_NAME"]][$num]=$ligne;
	}
	
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPVpnClients");
	
}

function add_vpn_del(){
	$con=base64_decode($_GET["VPNDelCon"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
	unset($array[$con]);
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPVpnClients");
}

function client_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_pptpd')")."</div>";
	$addcon=Paragraphe("vpn-connection-add-64.png","{ADD_CONNEXION}","{ADD_PPTP_CONNEXION_TEXT}","javascript:AddConPPTPD()");
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>$addcon<p>&nbsp;</p><div id='clients-status2'></div>$refresh
		</td>
		<td valign='top' width=99%>
			<div id='pptp-connexions'></div>
		</td>	
	</tr>
	</table>

	<script>
		function AddConPPTPD(){
			Loadjs('$page?add-vpn-con-js=yes');
		}
		
		function RefreshConexionList(){
			LoadAjax('pptp-connexions','$page?pptp-connexions=yes');
		
		}
		RefreshConexionList();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["status"]='{status}';
	if($users->PPTPD_INSTALLED){
		$array["server"]="{server}";
		$array["members"]="{members}";
	}
	if($users->PPTP_INSTALLED){$array["client"]="{client}";}
	
	$array["events"]="{events}";
	

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_pptpd style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_pptpd').tabs({
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


function status(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_pptpd')")."</div>";
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><div class=explain>{PPTPD_EXPLAIN}</div></td>
		<td valign='top'><div id='pptdp-status' style='width:280px'></div>$refresh</td>
	</tr>
	</table>
	<script>
		LoadAjax('pptdp-status','$page?pptpd-status=yes');
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function pptpd_status(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?pptpd-ini-status=yes")));
	$status=DAEMON_STATUS_ROUND("APP_PPTPD",$ini,null,0);	
	
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?pptpd-ifconfig=yes")));
	
	if(is_array($array)){
		while (list ($num, $ligne) = each ($array) ){
			$p[]=Paragraphe("network-connection2.png","{nic}:&nbsp;$num","<strong style=font-size:13px>{$ligne["INET"]}<hr>{$ligne["REMOTE"]} {$ligne["MASK"]}</strong>",null,null,270);
		}
	}
	
	
	
	echo $tpl->_ENGINE_parse_body($status)."<br>". @implode("<br>",$p)."<div id='clients-status'></div>
	
	<script>
		LoadAjax('clients-status','$page?pptpd-clients-status=yes');
	</script>
	";
}

function server_config(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$users=new usersMenus();
	$PPTPDConfig=unserialize(base64_decode($sock->GET_INFO("PPTPDConfig")));
	$EnablePPTPDVPN=$sock->GET_INFO("EnablePPTPDVPN");
	if($EnablePPTPDVPN==null){$EnablePPTPDVPN=0;}
	
	if(trim($PPTPDConfig["SERVER_IP"])==null){$PPTPDConfig["SERVER_IP"]="192.168.25.1";}
	if(trim($PPTPDConfig["NETMASK"])==null){$PPTPDConfig["NETMASK"]="255.255.255.0";}
	if(trim($PPTPDConfig["SERVER_NAME"])==null){$PPTPDConfig["SERVER_NAME"]=$users->hostname;}
	if(trim($PPTPDConfig["LINK_NET_FROM"])==null){$PPTPDConfig["LINK_NET_FROM"]="0";}
	if(trim($PPTPDConfig["CRYPT"])==null){$PPTPDConfig["CRYPT"]="1";}
	
	
	
	if($PPTPDConfig["SERVER_IP_FROM"]==null){
		preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP"],$rI);
		$end=$rI[4]+1;
		if($end>255){$end=254;}		
		$newip="{$rI[1]}.{$rI[2]}.{$rI[3]}.".$end;
		$PPTPDConfig["SERVER_IP_FROM"]=$newip;
	}
	
	if($PPTPDConfig["SERVER_IP_TO"]==null){
		preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP"],$re);
		$end=$re[4]+50;
		if($end>255){$end=254;}
		$PPTPDConfig["SERVER_IP_TO"]="{$re[1]}.{$re[2]}.{$re[3]}.".$end;
	}

	$ip=new networking();
	while (list ($eth, $ip_addr) = each ($ip->array_TCP) ){
		if(trim($ip_addr)==null){continue;}
		if(preg_match("#ppp#",$eth)){continue;}
		$bcrelay_ar[$eth]="$eth ($ip_addr)";
		
	}
	$bcrelay_ar[null]="{none}";
	
	//bcrelay
	
	
	$html="
	<div id='pptpd_div'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>".  Paragraphe_switch_img("{ENABLE_VPN_SERVER}","{ENABLE_VPN_SERVER_TEXT}","EnablePPTPDVPN","$EnablePPTPDVPN")."</td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{server_ip}:</td>
		<td>". Field_text("SERVER_IP",$PPTPDConfig["SERVER_IP"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{servername}:</td>
		<td>". Field_text("SERVER_NAME",$PPTPDConfig["SERVER_NAME"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{affectip_from}:</td>
		<td>". Field_text("SERVER_IP_FROM",$PPTPDConfig["SERVER_IP_FROM"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{affectip_to}:</td>
		<td>". Field_text("SERVER_IP_TO",$PPTPDConfig["SERVER_IP_TO"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{netmask}:</td>
		<td>". Field_text("NETMASK",$PPTPDConfig["NETMASK"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{DNS_1}:</td>
		<td>". Field_text("DNS_1",$PPTPDConfig["DNS_1"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{DNS_2}:</td>
		<td>". Field_text("DNS_2",$PPTPDConfig["DNS_2"],"font-size:13px;padding:3px;width:120px")."</td>
	</tr>			

	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{link_net_to}:</td>
		<td>". Field_array_Hash($bcrelay_ar,"bcrelay",$PPTPDConfig["bcrelay"],"linkNETfrom()",null,0,"font-size:13px;padding:3px;width:150px")."</td>
	</tr>		
	
	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{link_net_from}:</td>
		<td>". Field_checkbox("LINK_NET_FROM",1,$PPTPDConfig["LINK_NET_FROM"])."</td>
	</tr>		
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{crypted_connection}:</td>
		<td>". Field_checkbox("CRYPT",1,$PPTPDConfig["CRYPT"])."</td>
	</tr>	
	</table>
	</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SavePPTPDServerForm()")."</td>
	</tr>
	</table>
	</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' style='border-right:5px solid #999999;padding:5px'>
			<div style='font-size:14px'><strong>{clients_routes}</strong></div>
			<div style='font-size:13px'><i style='font-size:13px'>{clients_routes_add_text}</i></div>
	</td>
		<td valign='top' >
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{ip_match}:</td>
		<td valign='top'>". Field_text("AUTOROUTE",null,"font-size:13px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{ip}:</td>
		<td valign='top'>". Field_text("server_route_ip",null,"font-size:13px;padding:3px;width:120px",null,"CalcRouteServerCDIR()")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{netmask}:</td>
		<td valign='top'>". Field_text("server_route_netmask",null,"font-size:13px;padding:3px;width:120px",null,"CalcRouteServerCDIR()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{cdir}:</td>
		<td valign='top'>". Field_text("server_route_real",null,"font-size:13px;padding:3px;width:120px",null)."</td>
	</tr>	
	<tr>
		<td colspan=2 align=right>
			<hr>". button('{add}',"AddRouteServerRule()")."
		</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>			
	<div id='auto-routes-server' style='margin:5px;width:100%;height:150px;overflow:auto;border:1px dotted #CCCCCC'></div>
	
	
	
	<div class=explain>{PPTP_EXPLAIN_TEXT}</div>
	<script>
	var x_SavePPTPDServerForm= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_pptpd');
	}	

	
	function linkNETfrom(){
		var net=document.getElementById('bcrelay').value;
		if(net.length<3){document.getElementById('LINK_NET_FROM').disabled=true;}else{document.getElementById('LINK_NET_FROM').disabled=false;}
	}
		
function SavePPTPDServerForm(){
	var XHR = new XHRConnection();
	if(document.getElementById('LINK_NET_FROM').checked){XHR.appendData('LINK_NET_FROM',1);}else{XHR.appendData('LINK_NET_FROM',0);}
	if(document.getElementById('CRYPT').checked){XHR.appendData('CRYPT',1);}else{XHR.appendData('CRYPT',0);}
	XHR.appendData('EnablePPTPDVPN',document.getElementById('EnablePPTPDVPN').value);
	XHR.appendData('SERVER_IP',document.getElementById('SERVER_IP').value);
	XHR.appendData('SERVER_IP_FROM',document.getElementById('SERVER_IP_FROM').value);
	XHR.appendData('SERVER_IP_TO',document.getElementById('SERVER_IP_TO').value);
	XHR.appendData('bcrelay',document.getElementById('bcrelay').value);	
	XHR.appendData('DNS_1',document.getElementById('DNS_1').value);
	XHR.appendData('DNS_2',document.getElementById('DNS_2').value);
	XHR.appendData('NETMASK',document.getElementById('NETMASK').value);
	document.getElementById('pptpd_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SavePPTPDServerForm);	
	}
	
	var x_SavePPTPDServerForm= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshServerRoutes();
	}

	var x_AddRouteServerRule= function (obj) {
			RefreshServerRoutes();
		}
	
	
	function AddRouteServerRule(){
		var XHR = new XHRConnection();
		XHR.appendData('AUTOROUTE',document.getElementById('AUTOROUTE').value);
		XHR.appendData('server_route_real',document.getElementById('server_route_real').value);
		document.getElementById('auto-routes-server').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_AddRouteServerRule);
	}
	
	var x_CalcRouteServerCDIR= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){document.getElementById('server_route_real').value=tempvalue;}
		}			
		
		function CalcRouteServerCDIR(){
			var XHR = new XHRConnection();
			XHR.appendData('route_ip',document.getElementById('server_route_ip').value);
			XHR.appendData('route_netmask',document.getElementById('server_route_netmask').value);	
			XHR.sendAndLoad('$page', 'GET',x_CalcRouteServerCDIR);
		}	
		
		function RefreshServerRoutes(){
			LoadAjax('auto-routes-server','$page?server-routes=yes');
		}
	
	linkNETfrom();
	RefreshServerRoutes();
	document.getElementById('CRYPT').checked=true;
	document.getElementById('CRYPT').disabled=true;
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function server_routes_add(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDConfigRoutes")));
	$array[$_GET["AUTOROUTE"]]=$_GET["server_route_real"];
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPDConfigRoutes");
	$sock->getFrameWork("cmd.php?pptpd-restart=yes");
	
}
function server_routes_del(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDConfigRoutes")));
	unset($array[$_GET["server-routes-delete"]]);
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPDConfigRoutes");
	$sock->getFrameWork("cmd.php?pptpd-restart=yes");
	
}
	

function server_routes_list(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDConfigRoutes")));
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{ip_match}</th>
	<th>{cdir}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	if(is_array($array)){
		while (list ($ip, $cdir) = each ($array) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			
			$delete=imgtootltip("delete-24.png","{delete}","RoutesServerDelete('$ip')");
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:14px'>$ip</td>
			<td align=center><strong style='font-size:14px'>$cdir</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
	}
	
	$html=$html."</tbody></table>
	<script>
	var x_RoutesServerDelete= function (obj) {
			var tempvalue=obj.responseText;
			RefreshServerRoutes();
		}			
		
		function RoutesServerDelete(ip){
			var XHR = new XHRConnection();
			XHR.appendData('server-routes-delete',ip);
			document.getElementById('auto-routes-server').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_RoutesServerDelete);
		}		
	
	</script>
	";	
	echo $tpl->_ENGINE_parse_body($html);
}

function server_config_save(){
	$sock=new sockets();
	$sock->SET_INFO("EnablePPTPDVPN",$_GET["EnablePPTPDVPN"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"PPTPDConfig");
	$sock->getFrameWork("cmd.php?pptpd-restart=yes");
}

function members(){
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	$sock=new sockets();
	if(!$users->ARTICA_META_ENABLED){	
		$createUser=imgtootltip("identity-add-48.png","{add user explain}","Loadjs('create-user.php');");
	}else{
		if($sock->GET_INFO("AllowArticaMetaAddUsers")==1){
			$createUser=imgtootltip("identity-add-48.png","{add user explain}","Loadjs('create-user.php');");
		}
	}
	
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=50%>
		<center>". Field_text("vpn_search",null,"font-size:13px;padding:3px",null,null,null,false,"SearchVPNEnter(event)")."</center>
		<hr>
		<div style='width:100%;height:300px;overflow:auto' id='local-users'></div>
		<div style='text-align:right;width:100%;padding-top:5px;border-top:1px solid #CCCCCC'>$createUser</div>
	</td>
	<td valign='top'  width=50%>
		
		<div style='width:100%;height:300px;overflow:auto' id='vpn-users'></div>
	</td>
	</tr>
	<script>
		function Refresh_vpn(){
			LoadAjax('vpn-users','$page?vpn-users=yes');
		}
		
		function RefreshLocalMember(){
			var search=escape(document.getElementById('vpn_search').value);
			LoadAjax('local-users','$page?local-users='+search);
		}
		
		function SearchVPNEnter(e){
			if(checkEnter(e)){RefreshLocalMember();}
		}
		
		Refresh_vpn();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function local_users(){
	$stringtofind=$_GET["local-users"];
	$ldap=new clladp();
	$page=CurrentPageName();
	$tpl=new templates();		
	//if($stringtofind==null){$stringtofind="*";}
	$hash=$ldap->UserSearch(null,$stringtofind);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDMembers")));	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>{members}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	for($i=0;$i<$hash[0]["count"];$i++){
		$ligne=$hash[0][$i];
		$uid=$ligne["uid"][0];
		if($uid==null){continue;}
		if($uid=="squidinternalauth"){continue;}
		if($array[$uid]<>null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ct=new user($uid);
			$js=MEMBER_JS($uid,1,1);
			$img=imgtootltip("contact-48.png","{view}",$js);
			$add=imgtootltip("plus-24.png","{add}","VPNAddMember('$uid')");
			$html=$html."
			<tr class=$classtr>
			<td width=1%>$img</td>
			<td><strong style='font-size:14px'>$ct->DisplayName</td>
			<td width=1%>$add</td>
			</tr>
			";		
		
	}
	$html=$html."</tbody></table>
	<script>
	
	var x_VPNAddMember= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			Refresh_vpn();
	}		
		function VPNAddMember(uid){
			var XHR = new XHRConnection();
			XHR.appendData('VPNAddMember',uid);	
			document.getElementById('vpn-users').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_VPNAddMember);	
		}
		
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}


function vpn_add_member(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDMembers")));	
	$array[$_GET["VPNAddMember"]]=array("ASSIGN_IP"=>"*");
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPDMembers");
	$sock->getFrameWork("cmd.php?pptpd-chap=yes");
	
}
function vpn_del_member(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDMembers")));	
	unset($array[$_GET["VPNDelMember"]]);
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPDMembers");
	$sock->getFrameWork("cmd.php?pptpd-chap=yes");
	
}

function vpn_add_member_ip(){
	$uid=$_GET["vpnUserAssign"];
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDMembers")));	
	$array[$uid]["ASSIGN_IP"]=$_GET["ip"];	
	$sock->SaveConfigFile(base64_encode(serialize($array)),"PPTPDMembers");
	$sock->getFrameWork("cmd.php?pptpd-chap=yes");	
}


function vpn_users(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ip_address=$tpl->_ENGINE_parse_body("{ip_address}");		
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDMembers")));
	
	$PPTPDConfig=unserialize(base64_decode($sock->GET_INFO("PPTPDConfig")));
	if($PPTPDConfig["SERVER_IP_FROM"]==null){
		preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP"],$rI);
		$end=$rI[4]+1;
		if($end>255){$end=254;}		
		$newip="{$rI[1]}.{$rI[2]}.{$rI[3]}.".$end;
		$PPTPDConfig["SERVER_IP_FROM"]=$newip;
	}
	
	if($PPTPDConfig["SERVER_IP_TO"]==null){
		preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP"],$re);
		$end=$re[4]+50;
		if($end>255){$end=254;}
		$PPTPDConfig["SERVER_IP_TO"]="{$re[1]}.{$re[2]}.{$re[3]}.".$end;
	}	
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{vpn_members}</th>
	<th>{ip_address}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	if(is_array($array)){
		while (list ($uid, $conf) = each ($array) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ct=new user($uid);
			$js=MEMBER_JS($uid,1,1);
			$img=imgtootltip("contact-48.png","{view}",$js);
			$delete=imgtootltip("delete-32.png","{delete}","VPNDelMember('$uid')");
			if($conf["ASSIGN_IP"]=="*"){$ip="{no}";}
			if($conf["ASSIGN_IP"]==null){$ip="{no}";$conf["ASSIGN_IP"]="*";}
			if(!preg_match("#[0-9\.\:]+#",$conf["ASSIGN_IP"])){$ip="{dynamic}";$conf["ASSIGN_IP"]="*";}
			
			$html=$html."
			<tr class=$classtr>
			<td width=1%>$img</td>
			<td><strong style='font-size:14px'>$ct->DisplayName</td>
			<td align=center><strong style='font-size:11px;text-decoration:underline' OnClick=\"javascript:vpnUserAssign('$uid','{$conf["ASSIGN_IP"]}')\" OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\">$ip</td>
			<td width=1%>$delete</td>
			</tr>
			";
			
		}
	}
	
	$html=$html."</tbody></table>
	<script>
		RefreshLocalMember();
		
	var x_VPNDelMember= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			Refresh_vpn();
		}
				
		function VPNDelMember(uid){
			var XHR = new XHRConnection();
			XHR.appendData('VPNDelMember',uid);	
			document.getElementById('vpn-users').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_VPNDelMember);	
		}
		
		function vpnUserAssign(uid,def){
			var ip=prompt('$ip_address: {$PPTPDConfig["SERVER_IP_FROM"]} --> {$PPTPDConfig["SERVER_IP_TO"]}',def);
			if(ip){
				var XHR = new XHRConnection();
				XHR.appendData('vpnUserAssign',uid);	
				XHR.appendData('ip',ip);
				XHR.sendAndLoad('$page', 'GET',x_VPNDelMember);	
			}
		}
		
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}



function clients_status(){
	$users=new usersMenus();
	if(!$users->PPTP_INSTALLED){return null;}
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?pptpd-clients-ini-status=yes")));
	if(!is_array($ini->_params)){return;}
	while (list ($KEY, $array) = each ($ini->_params) ){
		$status[]=$tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("$KEY",$ini,"<i><strong>{connexion_name}: &laquo;{$array["service_name"]}&raquo;</strong></i>",0));
	}	
	
	
	$html=@implode("<br>",$status);
	$html=str_replace("{","",$html);
	$html=str_replace("}","",$html);
		
	echo $tpl->_ENGINE_parse_body($html);		
	
}



?>