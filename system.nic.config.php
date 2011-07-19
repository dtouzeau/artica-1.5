<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.system.nics.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.tcpip.inc');
	
	
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsSystemAdministrator==false){exit();}

if(isset($_GET["listnics"])){zlistnics_tabs();exit;}
if(isset($_GET["listnics2"])){zlistnics();exit;}
if(isset($_GET["js-virtual-add"])){virtual_js_add();exit;}

if(isset($_GET["BuildNetConf"])){BuildNetConf();exit;}
if($_GET["main"]=="listnics"){zlistnics_tabs();exit;}
if($_GET["main"]=="listnics2"){zlistnics();exit;}
if($_GET["main"]=="virtuals"){Virtuals();exit;}
if($_GET["main"]=="bridges"){Bridges();exit;}
if($_GET["main"]=="DNSServers"){DNS_SERVERS_POPUP();exit;}

if(isset($_GET["NetworkManager-check"])){NetworkManager_check();exit;}




if(isset($_GET["virtuals-list"])){virtuals_list();exit;}
if(isset($_GET["virt-ipaddr"])){virtuals_add();exit;}
if(isset($_GET["virt-del"])){virtuals_del();exit;}

if(isset($_GET["script"])){switch_script();exit;}

if(isset($_GET["netconfig"])){netconfig_popup();exit;}

if(isset($_GET["change-hostname-js"])){ChangeHostName_js();exit;}
if(isset($_GET["hostname"])){hostname();exit;}
if(isset($_GET["ChangeHostName"])){ChangeHostName();exit;}

if(isset($_GET["AddDNSServer"])){AddDNSServer();exit;}
if(isset($_GET["DeleteDNS"])){DeleteDNS();exit;}
if(isset($_GET["DNSServers"])){DNS_SERVERS_POPUP();}
if(isset($_GET["DNSServers-list"])){DNS_SERVERS_POPUP_LIST();}



if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["popup2"])){popup2();exit;}
if(isset($_GET["popup-tabs"])){tabs();exit;}
if(isset($_GET["popup-hostname"])){tabs_hostname();exit;}

if(isset($_GET["virtual-popup-add"])){virtual_add_form();exit;}
if(isset($_GET["cdir-ipaddr"])){virtual_cdir();exit;}
if(isset($_GET["postfix-virtual"])){virtuals_js();exit;}
if(isset($_GET["js-add-nic"])){echo virtuals_js_datas();exit;}

if(isset($_GET["bridges-list"])){Bridges_list();exit;}
if(isset($_GET["bridge-add"])){Bridges_add();exit;}
if(isset($_GET["bridge-del"])){Bridges_del();exit;}
if(isset($_GET["bridges-rules"])){Bridges_rules();exit;}

if(isset($_GET["NetWorkBroadCastAsIpAddr"])){NetWorkBroadCastAsIpAddr();exit;}



function popup(){
	$page=CurrentPageName();
	$html="<div id='MasterNetworkSection'></div>
	
	<script>
		LoadAjax('MasterNetworkSection','$page?popup2=yes');
	</script>
	";

	echo $html;
	
}

function popup2(){
$page=CurrentPageName();	
$html="
	<div class=explain >{network_about}</div>
	<div id='hostname_cf'></div>
	<div id='nic_status'></div>
	<div id='nic_tabs'></div>
<script>
	LoadAjax('nic_tabs','$page?popup-tabs=yes');
</script>
	
	";




$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function tabs(){
	
	
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["listnics"]='{main_interfaces}';
	$array["DNSServers"]='{dns_nameservers}';
	$array["virtuals"]='{virtual_interfaces}';
	if($users->VLAN_INSTALLED){$array["vlan"]='VLAN';}
	$array["bridges"]='{bridges}';
	$array["routes"]='{routes}';
	$array["hard"]='{hardware}';
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="hard"){
			$html[]= "<li><a href=\"system.nic.infos.php?popup=yes\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}
		
		if($num=="routes"){
			$html[]= "<li><a href=\"system.nic.routes.php\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}	

		if($num=="vlan"){
			$html[]= "<li><a href=\"system.nic.vlan.php\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}

		if($num=="snort"){
			$html[]= "<li><a href=\"system.nic.snort.php\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}			
		
		$html[]= "<li><a href=\"$page?main=$num\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
	}
	
	
	echo "
	<div id='main_config_hostname'></div>
	<div id='main_config_nics' style='width:750px;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>

	<script>
		$(document).ready(function() {
			$(\"#main_config_nics\").tabs();});
			
	</script>";		
	
	
	
}

function tabs_hostname(){
	$sock=new sockets();
	$page=CurrentPageName();
	$hostname=$sock->getFrameWork("cmd.php?full-hostname=yes");	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<div style='margin-bottom:10px;text-align:right' OnMouseOver=\"this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\" >
		<a href=\"javascript:blur();\" 
		OnClick=\"javascript:Loadjs('$page?change-hostname-js=yes');\" 
		style='font-size:16px;font-weight:bold;text-decoration:underline;'>{hostname}:&nbsp;&nbsp;&laquo;$hostname&raquo;<a><br>
		<i style='font-size:9px'>{click_to_edit}</i>
	</div>
	
	
	
	");	
	
}


function js(){
	$add=js_addon()."\n".file_get_contents("js/system-network.js");
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{net_settings}');
	$page=CurrentPageName();
	$prefix=md5($page);
	$openjs="YahooWin(700,'$page?popup=yes','$title');";
	IF(isset($_GET["in-front-ajax"])){
		$openjs="$('#BodyContent').load('$page?popup=yes');";
	}
	
	$html="
	$add
	$openjs
";
	
	echo $html;
}


function js_addon(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}
	
	$page=CurrentPageName();
	return "



function NicSettingsChargeLogs(){
	RefreshTab('main_config_nics');
	setTimeout(\"NicSettingsChargeHostanme()\",1000);
	}
	
function NicSettingsChargeHostanme(){
	LoadAjax('hostname_cf','$page?hostname=yes');
}

";
	
}

function switch_script(){
	
	switch ($_GET["script"]) {
		case "netconfig":echo netconfig();break;
	
		default:
			break;
	}
	
}

function hostname(){
$nic=new networking();
$nameserver=$nic->arrayNameServers;
$dns_text="<table class=form>";


if(is_array($nameserver)){
	while (list ($num, $val) = each ($nameserver) ){
		$val=trim($val);
		$dns_text=$dns_text."<tr " . CellRollOver_jaune().">
			<td width=1%><img src='img/fw_bold.gif'>
			<td class=legend nowrap>{nameserver}:</td>
			<td width=99% nowrap><strong style='font-size:11px'>$val</strong></td>
			<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"DeleteDNS('$val');")."</td>
			</tr>";
		
		
	}
}

$dns_text=$dns_text."
<tr>
<td align='right' colspan=4><hr>". button("{add}","AddDNSServer();")."</td>
</tr>
</table>
<br>
<input type='hidden' name='ChangeHostName' id='ChangeHostName' value='{ChangeHostName}'>





<table class=form>
<tr>
	<td class=legend>{hostname}:</td>
	<td><strong style='font-size:12px'><strong>$nic->hostname</strong></td>
	<td width=1%>". button("{edit}","ChangeHostName('$nic->hostname');")."</td>
</tr>
</table>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($dns_text);
}

function ChangeHostName_js(){
	$sock=new sockets();
	$tpl=new templates();
	$hostname=$sock->getFrameWork("cmd.php?full-hostname=yes");	
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}
	$changehostname_text=$tpl->javascript_parse_text("{ChangeHostName}");
	$page=CurrentPageName();
	
$html="
var x_ChangeHostName= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	if(document.getElementById('MasterNetworkSection')){
		LoadAjax('MasterNetworkSection','$page?popup2=yes');
	}
		
}

function ChangeHostName(){
		var DisableNetworksManagement=$DisableNetworksManagement;
		if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
		var hostname=prompt('$changehostname_text','$hostname');
		var XHR = new XHRConnection();
		XHR.appendData('ChangeHostName',hostname);
		if(document.getElementById('MasterNetworkSection')){document.getElementById('MasterNetworkSection').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";}
		XHR.sendAndLoad('$page', 'GET',x_ChangeHostName);

}

ChangeHostName();
";	

echo $html;
	
}

function ChangeHostName(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}	
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;}
	
	
	$tpl=new templates();
	if($_GET["ChangeHostName"]=='null'){
		echo $tpl->_ENGINE_parse_body('{cancel}');
		return null;}
	$_GET["ChangeHostName"]=trim(strtolower($_GET["ChangeHostName"]));
	$t=explode(".",$_GET["ChangeHostName"]);
	if(count($t)==1){echo $tpl->_ENGINE_parse_body("{$_GET["ChangeHostName"]}: {not_an_fqdn}");return;}
	
	$sock=new sockets();
	$sock->SET_INFO("myhostname",$_GET["ChangeHostName"]);
	$sock->getFrameWork("cmd.php?ChangeHostName={$_GET["ChangeHostName"]}");
	
	
	$users=new usersMenus();
	if($users->POSTFIX_INSTALLED){
		$sock->getFrameWork("cmd.php?postfix-others-values=yes");
		
	}
	
	
	
}

function zlistnics_tabs(){
	$array["listnics2"]='{nics}';
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();	
	if($users->SNORT_INSTALLED){$array["snort"]='{APP_SNORT}';}
	$array["firewall"]='{incoming_firewall}';
	$array["firewall-white"]='{whitelist}';
	

	
	
		while (list ($num, $ligne) = each ($array) ){
		if($num=="snort"){
			$html[]= "<li><a href=\"system.nic.snort.php\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}
		
		if($num=="firewall"){
			$html[]= "<li><a href=\"system.firewall.in.php\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}	

		if($num=="firewall-white"){
			$html[]= "<li><a href=\"whitelists.admin.php?popup-hosts=yes\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
			continue;
		}			
		
		$html[]= "<li><a href=\"$page?main=$num\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
	}
	
	$tab=time();
	echo "
	<div id='tabs_listnics2' style='margin:-8px;margin-right:-25px;width: 730px;height:555px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>

	<script>
		$(document).ready(function() {
			$(\"#tabs_listnics2\").tabs();});
			
	</script>";		
	
}



function zlistnics(){
	$sock=new sockets();
	$snortInterfaces=array();
	$LXCEthLocked=$sock->GET_INFO("LXCEthLocked");
	
	if(!is_numeric($LXCEthLocked)){$LXCEthLocked=0;}
	
	$LXCInterface=$sock->GET_INFO("LXCInterface");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}
	$page=CurrentPageName();
	$tpl=new templates();
	$apply_network_configuration=$tpl->_ENGINE_parse_body("{apply_network_configuration}");
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$apply_network_configuration_warn=$tpl->javascript_parse_text("{apply_network_configuration_warn}");
	
	$users=new usersMenus();
	if($users->SNORT_INSTALLED){
		$EnableSnort=$sock->GET_INFO("EnableSnort");
		if($EnableSnort==1){
			$snortInterfaces=unserialize(base64_decode($sock->GET_INFO("SnortNics")));
		}
	}	
	
	$tcp=new networking();
	
	
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	
	$count=0;
	writelogs(count($datas). " rows for nic infos",__FUNCTION__,__FILE__,__LINE__);
	
	
	$tr[]=$tpl->_ENGINE_parse_body("
		<table style='width:320px;margin:3px;padding:3px; 
		OnMouseOver=\";this.style.cursor='pointer';this.style.background='#F5F5F5';\"
		OnMouseOut=\";this.style.cursor='default';this.style.background='#FFFFFF';\"
		class=form
		>
		<tr>
			<td valign='top' width=1%><img src='img/ipv6-64.png'></td>
			<td valign='top' style='padding:4px'>
				<div style='font-size:13px'>
					
					<strong style='font-size:14px'>
						<a href=\"javascript:blur()\" 
						OnClick=\"javascript:Loadjs('system.nic.ipv6.php')\" 
						style='text-decoration:underline;font-weight:bold;font-size:16px'>IPv6: {parameters}</a></strong><br>
					<a href=\"javascript:blur()\" OnClick=\"javascript:Loadjs('system.nic.ipv6.php')\" style='text-decoration:underline'>{ipv6_explain_enable_text}</a>
					
				</div>
			</td>
		</tr>
		</table>
		");
	
	
	
	while (list ($num, $val) = each ($datas) ){
		writelogs("Found: $val",__FUNCTION__,__FILE__,__LINE__);
		$val=trim($val);
		if(preg_match('#master#',$val)){continue;}
		if(preg_match("#^veth.+?#",$val)){continue;}
		if(preg_match("#^tunl[0-9]+#",$val)){continue;}
		if(preg_match("#^dummy[0-9]+#",$val)){continue;}
		if(preg_match("#^gre[0-9]+#",$val)){continue;}
		if(preg_match("#^ip6tnl[0-9]+#",$val)){continue;}
		if(preg_match("#^sit[0-9]+#",$val)){continue;}
		if(preg_match("#^vlan[0-9]+#",$val)){continue;}
		
		
		$nic=new system_nic();
		if(!$nic->unconfigured){		
			if($LXCEthLocked==1){if($val==$LXCInterface){
				writelogs("LXCEthLocked:$LXCEthLocked; $val==$LXCInterface -> abort",__FUNCTION__,__FILE__,__LINE__);
				continue;
				}
			}
		}
		
		if(trim($val)==null){continue;}
		$tcp->ifconfig(trim($val));
		$text=listnicinfos(trim($val),"Loadjs('$page?script=netconfig&nic=$val')");
		$js="javascript:Loadjs('system.nic.edit.php?nic=$val')";
		if(!$tcp->linkup){
			$img_on="64-win-nic-off.png";
			
		}else{
			$img_on="64-win-nic.png";
			if($snortInterfaces[trim($val)]==1){$img_on="64-win-nic-snort.png";}
		}
		
		$tr[]="
		<table style='width:320px;margin:3px;padding:3px; 
		OnMouseOver=\";this.style.cursor='pointer';this.style.background='#F5F5F5';\"
		OnMouseOut=\";this.style.cursor='default';this.style.background='#FFFFFF';\"
		class=form>
		<tr>
			<td valign='top' width=1%><img src='img/$img_on'></td>
			<td valign='top' style='padding:4px'>
				<div OnClick=\"$js\">$text</div>
				<table style='width:100%'>
				<tr>
					<td width=1% nowrap><i>$val</td>
					<td width=99%><div style='text-align:right'>". imgtootltip("plus-16.png","{add_virtual_ip_addr_explain_js}","Loadjs('$page?js-add-nic=$val')")."</div></td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		";

		}
		
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
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

	$html=@implode("\n", $tables);
		
	echo "
	<div style='text-align:right'>". button("$apply_network_configuration","BuildNetConf()")."</div>
	<div id='NetworkManager-status'></div>
	$html
	
	

	
	<script>
		LoadAjax('main_config_hostname','$page?popup-hostname=yes');
		LoadAjax('NetworkManager-status','$page?NetworkManager-check=yes');
		
		var X_BuildNetConf= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			
		}		

		function BuildNetConf(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
			if(confirm('$apply_network_configuration_warn')){	
				var XHR = new XHRConnection();
				XHR.appendData('BuildNetConf',1);
				XHR.sendAndLoad('$page', 'GET',X_BuildNetConf);
			}
		}


		
	</script>
	
	";
	}
	


function listnicinfos($nicname,$js=null){
	$sock=new sockets();
	$nicinfos=$sock->getFrameWork("cmd.php?nicstatus=$nicname");
	$tbl=explode(";",$nicinfos);
	$tpl=new templates();
	
	$_netmask=html_entity_decode($tpl->_ENGINE_parse_body("{netmask}"));
	if(strlen($_netmask)>11){$_netmask=texttooltip(substr($_netmask,0,8)."...:",$tpl->_ENGINE_parse_body("{netmask}"));}else{$_netmask=$_netmask.":";}
	$wire='';
	if(trim($tbl[5])=="yes"){
		$wire=" (wireless)";
	}
	
	$defaults_infos_array=base64_encode(serialize(array("IP"=>$tbl[0],"NETMASK"=>$tbl[2],"GW"=>$tbl[4],"NIC"=>$nicname)));
	if($js<>null){$href="<a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-weight:bold;font-size:13px;text-decoration:underline'>";}
	
	
	$html="
	<input type='hidden' id='infos_$nicname' value='$defaults_infos_array'>
	<table style='width:99.5%' class=form>
	<tr>
		<td class=legend nowrap>{tcp_address}:</td>
		<td style='font-weight:bold;font-size:13px'>$href{$tbl[0]}</a></td>
	</tr>
	<tr>
		<td class=legend nowrap>$_netmask</td>
		<td style='font-weight:bold;font-size:13px'>$href{$tbl[2]}</a></td>
	</tr>	
	<tr>
		<td class=legend nowrap>{gateway}:</td>
		<td style='font-weight:bold;font-size:13px'>$href{$tbl[4]}</a></td>
	</tr>		
	<tr>
		<td class=legend nowrap>{mac_addr}:</td>
		<td style='font-weight:bold;font-size:13px'>$href{$tbl[1]}</a></td>
	</tr>	
	</table>
	";
	
	
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function netconfig(){
	$page=CurrentPageName();
	$html="
	YahooWin2(300,'$page?netconfig={$_GET["nic"]}','{$_GET["nic"]}','');
	
	function ipconfig(eth){
		YahooWin2(390,'$page?ipconfig='+eth+'&nic='+eth,eth,'');
	}
	

	
	";
	return $html;
	}

function netconfig_popup(){
	$eth=$_GET["netconfig"];
	$text_ip=listnicinfos($eth);
	
	$ip=new networking();
	$page=CurrentPageName();
	$arrayNic=$ip->GetNicInfos($eth);
	

	$sock=new sockets();
	$type=$sock->getfile("SystemNetworkUse");
	$nicinfos=$sock->getFrameWork("cmd.php?nicstatus=$eth");
	
	$tbl=explode(";",$nicinfos);
	$wire=false;
	if(trim($tbl[5])=="yes"){$wire=true;}		
	

	
	$button=button("{properties}","ipconfig('$eth')");
	if($wire){
		$button="<div style='background-color:#F5F59F;border:1px solid #676767;padding:3px;margin:3px;font-weight:bold'>
		{warning_wireless_nic}
		</div>";
	}
	
	$html="
	
	$text_ip
	
	<div class=explain>
	{network_style}:<strong>$type</strong>
	</div>
	<div class=form>
		<H3>{dns_servers}:</H3>
			". implode(",",$arrayNic["NAMESERVERS"])."
			
		</div>	
	
	<div style='margin:4px;text-align:right;'>
		$button
	</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}









function AddDNSServer(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	
	
	$ip=new networking();
	$ip->nameserver_add($_GET["AddDNSServer"]);
	$tpl=new templates();

	
}

function DeleteDNS(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	
	
	$ip=new networking();
	$ip->nameserver_delete($_GET["DeleteDNS"]);

	}
	
	
function virtuals_js(){

	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{virtual_interfaces}");
	$html="
	YahooWin(700,'$page?main=virtuals','$title');
	
	";
	
	echo $html;
	
}

function virtual_js_add(){
	$js=virtuals_js_datas();
	
	$html="
	$js
	VirtualIPAdd();
	";
	
	echo $html;
	
	
}


function virtuals_js_datas(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_interfaces=$tpl->_ENGINE_parse_body('{virtual_interfaces}');
	$tpl=new templates();
	$default_load="VirtualIPRefresh();";
	if(isset($_GET["js-add-nic"])){
		$default_load="VirtualIPJSAdd('{$_GET["js-add-nic"]}');";
	}
	
	
	$sock=new sockets();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	$NoGatewayForVirtualNetWork=$sock->GET_INFO("NoGatewayForVirtualNetWork");
	if(!is_numeric($NoGatewayForVirtualNetWork)){$NoGatewayForVirtualNetWork=0;}
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}		
	
	
	
	if($_GET["function-after"]<>null){$function_after="{$_GET["function-after"]}();";}
	$apply_network_configuration_warn=$tpl->javascript_parse_text("{apply_network_configuration_warn}");
	
	$html="
		var windows_size=500;
	
		function VirtualIPAdd(){
			YahooWin2(windows_size,'$page?virtual-popup-add=yes&default-datas={$_GET["default-datas"]}&function-after={$_GET["function-after"]}','$virtual_interfaces');
		
		}
		
		function VirtualIPJSAdd(nic){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var defaultDatas='';
			if(document.getElementById('infos_'+nic)){
				defaultDatas=document.getElementById('infos_'+nic).value;
			}
			YahooWin2(windows_size,'$page?virtual-popup-add=yes&default-datas='+defaultDatas,'$virtual_interfaces');
		}
		
		function VirtualsEdit(ID){
			YahooWin2(500,'$page?virtual-popup-add=yes&ID='+ID,'$virtual_interfaces');
		}
		
		var X_CalcCdirVirt= function (obj) {
			var results=obj.responseText;
			document.getElementById('cdir').value=results;
		}		
		
		function CalcCdirVirt(recheck){
			var cdir=document.getElementById('cdir').value;
			if(recheck==0){
				if(cdir.length>0){return;}
			}
			var XHR = new XHRConnection();
			
			XHR.appendData('cdir-ipaddr',document.getElementById('ipaddr').value);
			XHR.appendData('netmask',document.getElementById('netmask').value);
			XHR.sendAndLoad('$page', 'GET',X_CalcCdirVirt);
		}
		
		var X_VirtualIPAddSave= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();
			if(document.getElementById('main_openvpn_config')){RefreshTab('main_openvpn_config');}
			VirtualIPRefresh();
			$function_after
			
		}
		

		function VirtualIPRefresh(){
			if(document.getElementById('virtuals-list')){
				LoadAjax('virtuals-list','$page?virtuals-list=yes');
			}
		}
		
		function BuildVirtuals(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
			if(confirm('$apply_network_configuration_warn')){
				if(document.getElementById('virtuals-list')){document.getElementById('virtuals-list').innerHTML='<center><img src=img/wait_verybig.gif></center>';}
				
				if(document.getElementById('virtuals-list')){
					LoadAjax('virtuals-list','$page?virtuals-list=yes&build=yes');
				}
			}
		}
		
		function VirtualsDelete(id){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}		
			document.getElementById('virtuals-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			var XHR = new XHRConnection();
			XHR.appendData('virt-del',id);
			XHR.sendAndLoad('$page', 'GET',X_VirtualIPAddSave);
		}
		
		$default_load	
	";
		
	return $html;
}

	
function Virtuals(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_interfaces=$tpl->_ENGINE_parse_body('{virtual_interfaces}');
	$nics=new system_nic();
	if($nics->unconfigured){
		$error="<div class=explain style='color:red'>{NIC_UNCONFIGURED_ERROR}</div>";
	}
	
	
	$html="$error
	<div style='float:left'>". imgtootltip("20-refresh.png","{refresh}","VirtualIPRefresh()")."</div>
	
	
	<div id='virtuals-list'></div>	
	<script>
	". virtuals_js_datas()."
	</script>";
	

	echo $tpl->_ENGINE_parse_body($html);	
	
}

function virtual_add_form(){
	$ldap=new clladp();
	$sock=new sockets();
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();	
	$nics=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$NoGatewayForVirtualNetWork=$sock->GET_INFO("NoGatewayForVirtualNetWork");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($NoGatewayForVirtualNetWork)){$NoGatewayForVirtualNetWork=0;}
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}
	$NoGatewayForVirtualNetWorkExplain=$tpl->javascript_parse_text("{NoGatewayForVirtualNetWorkExplain}");	
	$title_button="{add}";
	if(!is_numeric($_GET["ID"])){$_GET["ID"]=0;}
	
	if($_GET["ID"]>0){
		$sql="SELECT * FROM nics_virtuals WHERE ID='{$_GET["ID"]}'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$title_button="{apply}";
	}
	
	if(isset($_GET["default-datas"])){
			$default_array=unserialize(base64_decode($_GET["default-datas"]));
			if(is_array($default_array)){
				$ligne["nic"]=$default_array["NIC"];
			if(preg_match("#(.+?)\.([0-9]+)$#",$default_array["IP"],$re)){
				if($re[2]>254){$re[2]=1;}
				$re[2]=$re[2]+1;
				$ligne["ipaddr"]="{$re[1]}.{$re[2]}";
				$ligne["gateway"]=$default_array["GW"];
				$ligne["netmask"]=$default_array["NETMASK"];
			}
		}
	}	
	
	$styleOfFields="font-size:14px;padding:3px";
	$ous=$ldap->hash_get_ou(true);
	$ous["openvpn_service"]="{APP_OPENVPN}";
	
	if($users->crossroads_installed){
		if($EnablePostfixMultiInstance==1){
			$ous["crossroads"]="{load_balancer}";
		}
	}
	
	while (list ($num, $val) = each ($nics) ){
		$nics_array[$val]=$val;
	}
	$nics_array[null]="{select}";
	
	$ous[null]="{select}";
	
	$nic_field=Field_array_Hash($nics_array,"nic",$ligne["nic"],null,null,0,"font-size:14px;padding:3px");
	$ou_fields=Field_array_Hash($ous,"org",$ligne["org"],null,null,0,"font-size:14px;padding:3px");
	$html="
	<div id='virtip'>
	". Field_hidden("ID","{$_GET["ID"]}")."
	<table style='width:100%'>
	<tr>
		<td class=legend>{nic}</td>
		<td>$nic_field</td>
	</tr>
	<tr>
		<td class=legend>{organization}</td>
		<td>$ou_fields</td>
	</tr>	
	<tr>
			<td class=legend>{tcp_address}:</td>
			
			<td>" . field_ipv4("ipaddr",$ligne["ipaddr"],$styleOfFields,false,"CalcCdirVirt(0)")."</td>
		</tr>
		<tr>
			<td class=legend>{netmask}:</td>
			<td>" . field_ipv4("netmask",$ligne["netmask"],$styleOfFields,false,"CalcCdirVirt(0)")."</td>
		</tr>
		<tr>
			<td class=legend>CDIR:</td>
			<td style='padding:-1px;margin:-1px'>
			<table style='width:99%;padding:-1px;margin:-1px'>
			<tr>
			<td width=1%>
			" . Field_text("cdir",$ligne["cdir"],"$styleOfFields;width:190px",null,null,null,false,null,$DISABLED)."</td>
			<td align='left'> ".imgtootltip("img_calc_icon.gif","cdir","CalcCdirVirt(1)") ."</td>
			</tr>
			</table></td>
		</tr>			
		<tr>
			<td class=legend>{gateway}:</td>
			<td>" . field_ipv4("gateway_virtual",$ligne["gateway"],$styleOfFields,false)."</td>
		</tr>	
	</table>
	</div>
	<div id='infosVirtual' style='font-size:13px'></div>
	<div style='text-align:right'><hr>". button($title_button,"VirtualIPAddSave()")."</div>
	<script>
		var Netid={$_GET["ID"]};
		var cdir=document.getElementById('cdir').value;
		var netmask=document.getElementById('netmask').value;
		if(netmask.length>0){
			if(cdir.length==0){
				CalcCdirVirt(0);
				}
			}
		if(Netid>0){
			document.getElementById('ipaddr').disabled=true;
		}
		
		
		function CheckGateway(){
			var NoGatewayForVirtualNetWork=$NoGatewayForVirtualNetWork;
			document.getElementById('gateway_virtual').disabled=false;
			if(NoGatewayForVirtualNetWork==1){
				document.getElementById('gateway_virtual').disabled=true;
				document.getElementById('gateway_virtual').value='';
				Ipv4FieldDisable('gateway_virtual');
				document.getElementById('infosVirtual').innerHTML='$NoGatewayForVirtualNetWorkExplain';
				
			}
			
			
		}
		
		
		function VirtualIPAddSave(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			var NoGatewayForVirtualNetWork=$NoGatewayForVirtualNetWork;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}		
			var XHR = new XHRConnection();
			XHR.appendData('virt-ipaddr',document.getElementById('ipaddr').value);
			XHR.appendData('netmask',document.getElementById('netmask').value);
			XHR.appendData('cdir',document.getElementById('cdir').value);
			if(NoGatewayForVirtualNetWork==0){XHR.appendData('gateway',document.getElementById('gateway_virtual').value);}
			if(NoGatewayForVirtualNetWork==1){XHR.appendData('gateway','');}
			XHR.appendData('nic',document.getElementById('nic').value);
			XHR.appendData('org',document.getElementById('org').value);
			XHR.appendData('ID',document.getElementById('ID').value);
			AnimateDiv('virtip');
			XHR.sendAndLoad('$page', 'GET',X_VirtualIPAddSave);
		}		
		
		CheckGateway();
		
	</script>
	
	";

	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function virtual_cdir(){
	$ipaddr=$_GET["cdir-ipaddr"];
	$newmask=$_GET["netmask"];
	$ip=new IP();
	
	if($newmask<>null){
		echo $ip->maskTocdir($ipaddr, $newmask);
	}
	
}

function NetWorkBroadCastAsIpAddr(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	

	$sock->SET_INFO("NetWorkBroadCastAsIpAddr",$_GET["NetWorkBroadCastAsIpAddr"]);
	$sock->SET_INFO("NoGatewayForVirtualNetWork",$_GET["NoGatewayForVirtualNetWork"]);
	
	
}

function virtuals_add(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	
	
	if($_GET["nic"]==null){echo $tpl->_ENGINE_parse_body("{nic}=null");exit;}
	$PING=trim($sock->getFrameWork("cmd.php?ping=".urlencode($_GET["virt-ipaddr"])));
	
	if($PING=="TRUE"){
		echo $tpl->javascript_parse_text("{$_GET["virt-ipaddr"]}:\n{ip_already_exists_in_the_network}");
		return;
	}
	
	$NoGatewayForVirtualNetWork=$sock->GET_INFO("NoGatewayForVirtualNetWork");
	if(!is_numeric($NoGatewayForVirtualNetWork)){$NoGatewayForVirtualNetWork=0;}	
	
	if($NoGatewayForVirtualNetWork==1){$_GET["gateway"]=null;}
	
	
	$sql="
	INSERT INTO nics_virtuals (nic,org,ipaddr,netmask,cdir,gateway)
	VALUES('{$_GET["nic"]}','{$_GET["org"]}','{$_GET["virt-ipaddr"]}','{$_GET["netmask"]}','{$_GET["cdir"]}','{$_GET["gateway"]}');
	";
	
	if($_GET["ID"]>0){
		$sql="UPDATE nics_virtuals SET nic='{$_GET["nic"]}',
		org='{$_GET["org"]}',
		ipaddr='{$_GET["virt-ipaddr"]}',
		netmask='{$_GET["netmask"]}',
		cdir='{$_GET["cdir"]}',
		gateway='{$_GET["gateway"]}' WHERE ID={$_GET["ID"]}";
	}
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function BuildNetConf(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?virtuals-ip-reconfigure=yes&stay=no");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{operation_launched_in_background}");
}

function virtuals_list(){
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$q=new mysql();
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$apply_network_configuration_warn=$tpl->javascript_parse_text("{apply_network_configuration_warn}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	
	$sock=new sockets();
	if(isset($_GET["build"])){
		$sock->getFrameWork("cmd.php?virtuals-ip-reconfigure=yes&stay=no");
		$html="<div style='color:#A90404;font-size:16px'>{operation_launched_in_background}</div>";
	}
	
	$interfaces=unserialize(base64_decode($sock->getFrameWork("cmd.php?ifconfig-interfaces=yes")));
	$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$style=CellRollOver();
	
	$html=$html."
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:98%'>
<thead class='thead'>
	<tr>
		<th>". imgtootltip("plus-24.png","{add}","VirtualIPAdd()")."</th>
		<th nowrap>{organization}</th>
		<th nowrap>{nic}</th>
		<th nowrap>{tcp_address}</th>
		<th nowrap>{netmask}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody class='tbody'>
	";	
	
			$net=new networking();
			$ip=new IP();	
		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		
		$eth="{$ligne["nic"]}:{$ligne["ID"]}";
		if($ligne["cdir"]==null){
			$ligne["cdir"]=$net->array_TCP[$ligne["nic"]];
			$eth=$ligne["nic"];
		}
		$img="22-win-nic-off.png";
		
		if($interfaces[$eth]<>null){
			$img="22-win-nic.png";
		}
		
		$ligne["org"]=str_replace("LXC-INTERFACES","{APP_LXC}",$ligne["org"]);
		
		if(trim($ligne["org"])==null){
			$ligne["org"]="<strong style='color:red'>{no_organization}</strong>";
		}
		
		if($ligne["org"]=="crossroads"){
			$ligne["org"]="<a href=\"javascript:blur();\" 
			OnClick=\"javascript:Loadjs('postfix.multiple.crossroads.php?ipaddr=". urlencode($ligne["ipaddr"])."');\" 
			style='font-size:14px;text-decoration:underline;font-weight:bold'>{load_balancer}</a>";
			$img="folder-dispatch-22-grey.png";
			if($interfaces[$eth]<>null){$img="folder-dispatch-22.png";}
			
		}
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/$img'></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["org"]}</strong></td>
			<td><strong style='font-size:14px' align='right'>$eth</strong></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["ipaddr"]}</strong></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["netmask"]}</strong></td>
			<td width=1%>". imgtootltip("24-administrative-tools.png","{edit}","VirtualsEdit({$ligne["ID"]})")."</td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","VirtualsDelete({$ligne["ID"]})")."</td>
		</tr>
		
		
		";
		
	}
	$sock=new sockets();
	$page=CurrentPageName();
	
	$html=$html."</tbody></table></center>
	<p>&nbsp;</p>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=50%>
		<table class=form>
		<tr>
			<td class=legend>{broadcast_has_ipaddr}</td>
			<td>". Field_checkbox("NetWorkBroadCastAsIpAddr",1,$sock->GET_INFO("NetWorkBroadCastAsIpAddr"),"NetWorkBroadCastAsIpAddrSave()")."</td>
		</tr>
		<tr>
			<td class=legend>{NoGatewayForVirtualNetWork}</td>
			<td>". Field_checkbox("NoGatewayForVirtualNetWork",1,$sock->GET_INFO("NoGatewayForVirtualNetWork"),"NetWorkBroadCastAsIpAddrSave()")."</td>
		</tr>		
		</table>
	</td>
	<td valign='top' width=50%>
	<div style='text-align:right'>". button("{apply_network_configuration}","BuildNetConf()")."</div>
	</td>
	</tr>
	</table>	
	
	<script>
	
		var X_NetWorkBroadCastAsIpAddrSave= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			
		}
		
		function NetWorkBroadCastAsIpAddrSave(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var XHR = new XHRConnection();
			if(document.getElementById('NetWorkBroadCastAsIpAddr').checked){
			XHR.appendData('NetWorkBroadCastAsIpAddr',1);}else{XHR.appendData('NetWorkBroadCastAsIpAddr',0);}
			
			if(document.getElementById('NoGatewayForVirtualNetWork').checked){
			XHR.appendData('NoGatewayForVirtualNetWork',1);}else{XHR.appendData('NoGatewayForVirtualNetWork',0);}
						
			XHR.sendAndLoad('$page', 'GET',X_NetWorkBroadCastAsIpAddrSave);
		}
		
		var X_BuildNetConf= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_nics');
		}		

		function BuildNetConf(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
			if(confirm('$apply_network_configuration_warn')){	
				var XHR = new XHRConnection();
				XHR.appendData('BuildNetConf',1);
				if(document.getElementById('virtuals-list')){document.getElementById('virtuals-list').innerHTML='<center><img src=img/wait_verybig.gif></center>';}
				if(document.getElementById('NetworkManager-status')){document.getElementById('NetworkManager-status').innerHTML='<center><img src=img/wait_verybig.gif></center>';}
				XHR.sendAndLoad('$page', 'GET',X_BuildNetConf);
			}
		}		
	
	</script>		
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function virtuals_del(){
	
	$sock=new sockets();
	$tpl=new templates();
	$q=new mysql();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
		$sql="SELECT * FROM nics_virtuals WHERE ID='{$_GET["ID"]}'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$ipaddr=$ligne["ipaddr"];
		$main=new maincf_multi(null,null,$ipaddr);
		if($main->myhostname<>null){
			echo $tpl->javascript_parse_text("{cannot_delete_address_postfix_instance}:\n$main->myhostname\n{organization}\n$main->ou\n");
			return;
		}
		
		$sql="SELECT hostname,ou FROM samba_hosts WHERE ipaddr='$ipaddr'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["hostname"]<>null){
			echo $tpl->javascript_parse_text("{cannot_delete_address_samba_instance}:\n{$ligne["hostname"]}\n{organization}\n{$ligne["ou"]}\n");
			return;
		}
	
		if(!is_numeric(trim($_GET["virt-del"]))){return ;}
		$sql="DELETE FROM nics_virtuals WHERE ID={$_GET["virt-del"]}";
		
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q-ok){echo $q->mysql_error;return;}
		
		$sql="DELETE FROM iptables_bridge WHERE nics_virtuals_id={$_GET["virt-del"]}";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		
		$sql="DELETE FROM crossroads_smtp WHERE ipaddr='{$_GET["virt-del"]}'";
		
		
		if(!$q-ok){echo $q->mysql_error;return;}
		
}


function ConstructVirtsIP(){
	$nic=new system_nic();
	$nic->ConstructVirtsIP();
}

function Bridges(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
	$q=new mysql();
	$sock=new sockets();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$nics_array[null]="{select}";
	$nics_virtual[null]="{select}";
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
		
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		
		$eth="{$ligne["nic"]}:{$ligne["ID"]}";
		$nics_virtual[$ligne["ID"]]="$eth ({$ligne["ipaddr"]})";
		$nics_array[$eth]=$eth;
	}
	
	$tcp=new networking();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	$tcp=new networking();
	
while (list ($num, $val) = each ($datas) ){
		$infos=$tcp->GetNicInfos($val);
		$nics_array[$val]=" $val ({$infos["IPADDR"]})";
	}
	$rules=$tpl->_ENGINE_parse_body("{rules}");
	$html="<div class=explain>{VIRTUAL_BRIDGES_EXPLAIN}</div>
	<div style=text-align:right'></div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' width=1% nowrap>{from}:</td>
		<td width=1% nowrap>". Field_array_Hash($nics_virtual,"VirtualID",null,null,null,0,"font-size:13px;padding:3px")."</td>
		<td class=legend style='font-size:13px' width=1% nowrap>{to}:</td>
		<td width=1%>". Field_array_Hash($nics_array,"RealInterface",null,null,null,0,"font-size:13px;padding:3px")."</td>
		<td width=1% nowrap>". button("{add_bridge}","BridgeAdd()")."</td>
		<td width=99%>&nbsp;</td>
	</tr>
	</table>
	
	<hr>
	<div id='bridge-list' style='heigth:250px;overflow:auto'></div>
	
	<script>
		var X_BridgeAdd= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			BridgeRefresh();
		}
		
		function BridgeAdd(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var XHR = new XHRConnection();
			XHR.appendData('bridge-add','yes');
			XHR.appendData('VirtualID',document.getElementById('VirtualID').value);
			XHR.appendData('RealInterface',document.getElementById('RealInterface').value);
			document.getElementById('bridge-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',X_BridgeAdd);
		}
		
		function BridgeDelete(ID){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}		
			var XHR = new XHRConnection();
			XHR.appendData('bridge-del',ID);
			document.getElementById('bridge-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',X_BridgeAdd);
		}
		
		function BridgeRefresh(){
			LoadAjax('bridge-list','$page?bridges-list=yes');
			
		}
		
		function BridgeRules(ID){
			YahooWin('700','$page?bridges-rules='+ID,'$rules::'+ID);
		}
		
		BridgeRefresh();
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function Bridges_add(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}
	$_GET["RealInterface"]=trim($_GET["RealInterface"]);
	$_GET["VirtualID"]=trim($_GET["VirtualID"]);
	if(trim($_GET["VirtualID"])==null){return;}
	if(trim($_GET["RealInterface"])==null){return;}
	$md5=md5(trim($_GET["RealInterface"]).trim($_GET["VirtualID"]));
	
	$sql="INSERT INTO iptables_bridge (`nics_virtuals_id`,`nic_linked`,`zmd5`) VALUES ('{$_GET["VirtualID"]}','{$_GET["RealInterface"]}','$md5')";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?virtual-ip-build-bridges=yes");	
	
	
}
function Bridges_del(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	
	
	if(!is_numeric(trim($_GET["bridge-del"]))){echo "{$_GET["bridge-del"]} not a numeric";return;}
	$sql="DELETE FROM iptables_bridge WHERE ID={$_GET["bridge-del"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?virtual-ip-build-bridges=yes");
}

function Bridges_list(){
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2  nowrap>{from}</th>
	<th nowrap>&nbsp;</th>
	<th colspan=2 nowrap>{to}</th>
	<th>{rules}</th>
	<th nowrap>{delete}</th>
	</tr>
</thead>";
	
	$sql="SELECT * FROM iptables_bridge ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if(preg_match("#doesn't exist#",$q->mysql_error)){
			$q->BuildTables();
			echo "<script>BridgeRefresh();</script>";
	}
		
		echo "<H2>$q->mysql_error</H2>";}
	$tcp=new networking();
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ipaddrinfos=VirtualNicInfosIPaddr($ligne["nics_virtuals_id"]);
		$nic_linked=$ligne["nic_linked"];
		$infos=$tcp->GetNicInfos($nic_linked);
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% style='padding:3px'><img src='img/folder-network-32.png'></td>
			<td width=33%><strong style='font-size:14px'>{$ipaddrinfos["ETH"]} ({$ipaddrinfos["IPADDR"]})</strong></td>
			<td width=33% style='padding:3px' align='center'><img src='img/arrow-right-32.png'></td>
			<td width=1% style='padding:3px'><img src='img/folder-network-32.png'></td>
			<td width=33% nowrap><strong style='font-size:14px'>$nic_linked ({$infos["IPADDR"]})</strong></td>
			<td width=1% align=center>". imgtootltip("script-32.png","{rules}","BridgeRules({$ligne["ID"]})")."</td>
			<td width=1% align=center>". imgtootltip("delete-24.png","{delete}","BridgeDelete({$ligne["ID"]})")."</td>
			
		</tr>";		
	
	}
		
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function Bridges_rules(){
	$sock=new sockets();
	$tpl=new templates();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?iptables-bridge-rules={$_GET["bridges-rules"]}")));
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th>{rules}</th>
	</tr>
</thead>";	
	if(is_array($datas)){
	while (list ($num, $val) = each ($datas) ){
	$html=$html."
		<tr class=$classtr>
		<td><code style='font-size:12px'>$val</code></td>
		</tr>";	
		
	}
	}else{
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
	}
	
$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function DNS_SERVERS_POPUP(){
$tpl=new templates();
$page=CurrentPageName();
$sock=new sockets();
$DeleteDNS=$tpl->javascript_parse_text("{DeleteDNS}");
$AddDNSServer=$tpl->javascript_parse_text("{AddDNSServer}");
$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}	
$add=Paragraphe("cluster-replica-add.png","{add_forwarder}","{add_forwarder_text}","javascript:AddDNSServer()");	
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<td valign='top' width=1%>$add</td>
	<td valign='top'>
		<div id='nameserver-list' style='width:100%;height:250px;overflow:auto'></div>
	</td>
	</tr>
	</table>
	
	
	<script>
		function DNsServerList(){
			LoadAjax('nameserver-list','$page?DNSServers-list=yes');
		}
		
		var x_RefreshDNS= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			DNsServerList();
				
		}		
		function AddDNSServer(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var hostname=prompt('$AddDNSServer');
			var XHR = new XHRConnection();
			XHR.appendData('AddDNSServer',hostname);
			XHR.sendAndLoad('$page', 'GET',x_RefreshDNS);	
		}	
	
	DNsServerList();
	
	
	
	</script>";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
	

function DNS_SERVERS_POPUP_LIST(){
$sock=new sockets();	
$nic=new networking();
$nameserver=$nic->arrayNameServers;
$tpl=new templates();
$page=CurrentPageName();
$DeleteDNS=$tpl->javascript_parse_text("{DeleteDNS}");
$AddDNSServer=$tpl->javascript_parse_text("{AddDNSServer}");
$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}



	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{nameserver}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		


if(is_array($nameserver)){
	while (list ($num, $val) = each ($nameserver) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$val=trim($val);
		$html=$html."<tr>
			<td width=1%><img src='img/32-network-server.png'>
			<td width=99% nowrap><strong style='font-size:18px;font-weight:bold'>$val</strong></td>
			<td width=1%>" . imgtootltip('delete-32.png','{delete}',"DeleteDNS('$val');")."</td>
			</tr>";
		
		
	}
}
	
	
	$html=$html."</tbody></table>
	<script>

	
	function DeleteDNS(nameserver){
		var DisableNetworksManagement=$DisableNetworksManagement;
		if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
		if(confirm('$DeleteDNS\\n'+nameserver)){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteDNS',nameserver);
			XHR.sendAndLoad('$page', 'GET',x_RefreshDNS);	
		}

	}	
	
	</script>
	
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function NetworkManager_check(){
	
	$nic=new system_nic();
	if($nic->unconfigured){
		$tpl=new templates();
		$error="<div class=explain style='color:red'>{NIC_UNCONFIGURED_ERROR}</div>";
		echo $tpl->_ENGINE_parse_body($error);
	}
	
}



//if(isset($_GET["cdir-ipaddr"])){virtual_cdir();exit;}
	

