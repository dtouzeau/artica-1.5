<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){		
	$tpl=new templates();
	echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();exit();
	}
if(isset($_GET["script"])){index_script();exit;}
if(isset($_GET["index_popup"])){index_page();exit;}


if(isset($_GET["dhcp-tab"])){dhcp_switch();exit;}
if(isset($_GET["dhcp-status"])){dhcp_status();exit;}



if(isset($_GET["index_dhcp"])){dhcp_index_js();exit;}
if(isset($_GET["index_dhcp_popup"])){dhcp_tabs();exit;}
if(isset($_GET["dhcp_enable_popup"])){dhcp_enable();exit;}
if(isset($_GET["dhcp_form"])){echo dhcp_form();exit;}
if(isset($_GET["dhcp-list"])){echo dhcp_computers_scripts();exit;}
if(isset($_GET["dhcp-pxe"])){echo dhcp_pxe_form();exit;}
if(isset($_GET["pxe_enable"])){echo dhcp_pxe_save();exit;}

if(isset($_GET["SaveDHCPSettings"])){dhcp_save();exit;}
if(isset($_GET["EnableDHCPServer"])){dhcp_enable_save();exit;}
if(isset($_GET["AsGatewayForm"])){echo gateway_page();exit;}
if(isset($_GET["gayteway_enable"])){echo gateway_enable();exit;}
if(isset($_GET["EnableArticaAsGateway"])){gateway_save();exit;}
if(isset($_GET["popup-network-masks"])){popup_networks_masks();exit;}
if(isset($_GET["show-script"])){dhcp_scripts();exit;}

if(isset($_POST["OnlySetGateway"])){OnlySetGateway_save();exit;}

function index_script(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{APP_ARTICA_GAYTEWAY}');
	$html="
		YahooWin0(550,'$page?index_popup=yes','$title');
	
	
	";
	
	echo $html;
}

function dhcp_index_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{APP_DHCP}');
	$pxe=$tpl->_ENGINE_parse_body('{APP_DHCP} {PXE}');	
	$enable=$tpl->_ENGINE_parse_body("{EnableDHCPServer}");
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
		
	
	$start="DHCPDGBCONF();";
	if(isset($_GET["in-front-ajax"])){
		$start="DHCPDGBCONF2();";
	}
	
	$html="
		function DHCPDGBCONF(){
		YahooWin2(790,'$page?index_dhcp_popup=yes','$title');
		setTimeout(\"DHCPCOmputers()\",800);
		}
		
		function DHCPDGBCONF2(){
		$('#BodyContent').load('$page?index_dhcp_popup=yes');
		setTimeout(\"DHCPCOmputers()\",800);
		}		

		function EnableDHCPServerForm(){
			YahooWin3(350,'$page?dhcp_enable_popup=yes','$enable');
		}
		
		function PxeConfig(){
		YahooWin3(630,'$page?dhcp-pxe=yes','$pxe');
		
		}
		
		var x_EnableDHCPServerSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			if(document.getElementById('main_config_dhcpd')){RefreshTab('main_config_dhcpd');}
			YahooWin3Hide();
		}			
		
		
		function EnableDHCPServerSave(){
			var DisableNetworksManagement=$DisableNetworksManagement;	
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
			var XHR = new XHRConnection();
			XHR.appendData('EnableDHCPServer',document.getElementById('EnableDHCPServer').value);
			document.getElementById('img_EnableDHCPServer').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_EnableDHCPServerSave);	
		}

	function DHCPCOmputers(){
			if(!document.getElementById('dhcpd_lists')){
				setTimeout(\"DHCPCOmputers()\",800);
			}
			LoadAjax('dhcpd_lists','$page?dhcp-list=yes');
		}
		
var x_SaveDHCPSettings= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	RefreshTab('main_config_dhcpd');
	}		
		
	function SaveDHCPSettings(){
		var DisableNetworksManagement=$DisableNetworksManagement;	
		if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
		var XHR = new XHRConnection();
		XHR.appendData('SaveDHCPSettings','yes');
		XHR.appendData('range1',document.getElementById('range1').value);
		XHR.appendData('range2',document.getElementById('range2').value);
		XHR.appendData('gateway',document.getElementById('gateway').value);
		XHR.appendData('netmask',document.getElementById('netmask').value);
		XHR.appendData('DNS_1',document.getElementById('DNS_1').value);
		XHR.appendData('DNS_2',document.getElementById('DNS_2').value);
		XHR.appendData('max_lease_time',document.getElementById('max_lease_time').value);
		XHR.appendData('dhcp_listen_nic',document.getElementById('dhcp_listen_nic').value);
		XHR.appendData('EnableDHCPServer',document.getElementById('EnableDHCPServer').value);
		XHR.appendData('ntp_server',document.getElementById('ntp_server').value);
		XHR.appendData('subnet',document.getElementById('subnet').value);
		XHR.appendData('broadcast',document.getElementById('broadcast_dhcp_main').value);
		XHR.appendData('WINS',document.getElementById('WINSDHCPSERV').value);
		
		
		
		if(document.getElementById('EnableArticaAsDNSFirst')){
			if(document.getElementById('EnableArticaAsDNSFirst').checked){XHR.appendData('EnableArticaAsDNSFirst',1);}else{XHR.appendData('EnableArticaAsDNSFirst',0);}
		}else{
			XHR.appendData('EnableArticaAsDNSFirst',0);
		}
		
		if(document.getElementById('IncludeDHCPLdapDatabase').checked){XHR.appendData('IncludeDHCPLdapDatabase',1);}else{XHR.appendData('IncludeDHCPLdapDatabase',0);}
		if(document.getElementById('EnableDHCPUseHostnameOnFixed').checked){XHR.appendData('EnableDHCPUseHostnameOnFixed',1);}else{XHR.appendData('EnableDHCPUseHostnameOnFixed',0);}
		
		if(document.getElementById('DHCPPing_check').checked){XHR.appendData('DHCPPing_check',1);}else{XHR.appendData('DHCPPing_check',0);}
		if(document.getElementById('DHCPauthoritative').checked){XHR.appendData('DHCPauthoritative',1);}else{XHR.appendData('DHCPauthoritative',0);}
		
		XHR.appendData('ddns_domainname',document.getElementById('ddns_domainname').value);
		document.getElementById('dhscpsettings').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDHCPSettings);	

	}
	
		
var x_SavePXESettings= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	PxeConfig();
	}		
	
	
	function SavePXESettings(){
		var DisableNetworksManagement=$DisableNetworksManagement;	
		if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
		var XHR = new XHRConnection();
		XHR.appendData('pxe_enable',document.getElementById('pxe_enable').value);
		XHR.appendData('pxe_file',document.getElementById('pxe_file').value);
		XHR.appendData('pxe_server',document.getElementById('pxe_server').value);
		document.getElementById('dhcppxeform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePXESettings);	

	}
		
	$start";
	
	echo $html;	
}


function index_page(){
	$bind9=Paragraphe('folder-64-bind9-grey.png','{APP_BIND9}','{APP_BIND9_TEXT}',"",null,210,null,0,false);
	$openvpn=Paragraphe('64-openvpn-grey.png','{APP_OPENVPN}','{APP_OPENVPN_TEXT}',"",null,210,null,0,false);	
	$users=new usersMenus();
	
	
	if($users->dhcp_installed){
		$dhcp=Buildicon64('DEF_ICO_DHCP');
		}
		
	if($users->BIND9_INSTALLED==true){
		$bind9=ICON_BIND9();
	}
	
	if($users->OPENVPN_INSTALLED==true){
		$openvpn=Paragraphe('64-openvpn.png','{APP_OPENVPN}','{APP_OPENVPN_TEXT}',"javascript:Loadjs('index.openvpn.php')",null,210,null,0,false);	
	}
	
	$comp=ICON_ADD_COMPUTER();
	$gateway=Buildicon64('DEF_ICO_GATEWAY');
	$html="<div style='width:530px'>
	<table>
	<tr>
	<td valign='top'>$gateway</td>
	<td valign='top'>$dhcp</td>
	</tr>
	<tr>
	<td valign='top'>$bind9</td>
	<td valign='top'>$comp</td>
	</tr>
	<tr>
		<td valign='top'>$openvpn</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"system.index.php");
}

function dhcp_pxe_form(){
	$sock=new sockets();
	$EnableDHCPFixPxeThinClient=$sock->GET_INFO("EnableDHCPFixPxeThinClient");
	if($EnableDHCPFixPxeThinClient==1){
		echo "<script>
		YahooWin3Hide();
		Loadjs('artica.has.pxe.php');
		</script>
		";return;}
		
		
	$dhcp=new dhcpd();
	$enable=Paragraphe_switch_img('{enable}','{EnablePXEDHCP}',"pxe_enable",$dhcp->pxe_enable);
	
	$form=RoundedLightWhite("<div id='dhcppxeform'>
	<table style='width:100%'>
			<tr>
				<td valign='top'>$enable</td>
			<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td class=legend nowrap style='font-size:13px'>{pxe_file}:</td>
				<td>".Field_text('pxe_file',$dhcp->pxe_file,'width:130px;font-size:13px;padding:3px')."</td>
				<td>&nbsp;</td>
			</tr>	
			<tr>
				<td class=legend nowrap style='font-size:13px'>{pxe_server}:</td>
				<td>".Field_text('pxe_server',$dhcp->pxe_server,'width:130px;font-size:13px;padding:3px')."</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan=3 align='right'><hr>
				". button("{apply}","SavePXESettings()")."
					
				
				</td>
			</tr>					
			
			</table>
			</td>
		</tr>
		</table>");
	$html="
	<div class=explain>{PXE_DHCP_MINI_TEXT}</div>
	$form
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function dhcp_form(){
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	$dhcp=new dhcpd();
	$page=CurrentPageName();
	
	$users=new usersMenus();
	$sock=new sockets();
	$EnableDHCPServer=$sock->GET_INFO('EnableDHCPServer');
	$EnableDHCPUseHostnameOnFixed=$sock->GET_INFO('EnableDHCPUseHostnameOnFixed');
	$IncludeDHCPLdapDatabase=$sock->GET_INFO('IncludeDHCPLdapDatabase');
	if(!is_numeric($IncludeDHCPLdapDatabase)){$IncludeDHCPLdapDatabase=1;}
	
	if(count($domains)==0){$dom=Field_text('ddns_domainname',$dhcp->ddns_domainname,"font-size:13px;");}
	else{
		$domains[null]="{select}";
		$dom=Field_array_Hash($domains,'ddns_domainname',$dhcp->ddns_domainname,null,null,null,";font-size:13px;padding:3px");}
		$nic=$dhcp->array_tcp;
		if($dhcp->listen_nic==null){$dhcp->listen_nic="eth0";
	}
	
	
	while (list ($num, $val) = each ($nic) ){
		if($num==null){continue;}
		if($num=="lo"){continue;}
		$nics[$num]=$num;
	}
	if($dhcp->listen_nic<>null){
		$nics[$dhcp->listen_nic]=$dhcp->listen_nic;
	}
	$nics[null]='{select}';

	
	if(($users->BIND9_INSTALLED) OR ($users->POWER_DNS_INSTALLED) ){
		
		$EnableArticaAsDNSFirst=Field_checkbox("EnableArticaAsDNSFirst",1,$dhcp->EnableArticaAsDNSFirst);
		
		
	}else{
		$EnableArticaAsDNSFirst=Field_numeric_checkbox_img_disabled('EnableArticaAsDNSFirst',0,'{enable_disable}');	
	}
	
	$EnableDHCPUseHostnameOnFixed=Field_checkbox("EnableDHCPUseHostnameOnFixed",1,$EnableDHCPUseHostnameOnFixed);
	$IncludeDHCPLdapDatabase=Field_checkbox("IncludeDHCPLdapDatabase",1,$IncludeDHCPLdapDatabase,"OnlySetGatewayFCheck()");
	$authoritative=Field_checkbox("DHCPauthoritative",1,$dhcp->authoritative);
	$ping_check=Field_checkbox("DHCPPing_check",1,$dhcp->ping_check);
	$html="

			<div id='dhscpsettings' class=form>
			<input type='hidden' id='EnableDHCPServer' value='$EnableDHCPServer' name='EnableDHCPServer'>
			<table style='width:98%'>
			
			<tr>
				<td class=legend style='font-size:13px'>{EnableArticaAsDNSFirst}:</td>
				<td>$EnableArticaAsDNSFirst</td>
				<td>&nbsp;</td>
				<td>". help_icon('{EnableArticaAsDNSFirst_explain}')."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{IncludeDHCPLdapDatabase}:</td>
				<td>$IncludeDHCPLdapDatabase</td>
				<td>&nbsp;</td>
				<td>". help_icon('{IncludeDHCPLdapDatabase_explain}')."</td>
			</tr>				
			
			<tr>
				<td class=legend style='font-size:13px'>{EnableDHCPUseHostnameOnFixed}:</td>
				<td>$EnableDHCPUseHostnameOnFixed</td>
				<td>&nbsp;</td>
				<td>". help_icon('{EnableDHCPUseHostnameOnFixed_explain}')."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{authoritative}:</td>
				<td>$authoritative</td>
				<td>&nbsp;</td>
				<td>". help_icon('{authoritativeDHCP_explain}')."</td>
			</tr>								
			<tr>
				<td class=legend style='font-size:13px'>{DHCPPing_check}:</td>
				<td>$ping_check</td>
				<td>&nbsp;</td>
				<td>". help_icon('{DHCPPing_check_explain}')."</td>
			</tr>
		
			
			<tr>
				<td class=legend style='font-size:13px'>{nic}:</td>
				<td>".Field_array_Hash($nics,'dhcp_listen_nic',$dhcp->listen_nic,null,null,null,";font-size:13px;padding:3px")."</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:13px'>{ddns_domainname}:</td>
				<td>$dom</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{max_lease_time}:</td>
				<td style='font-size:13px'>".Field_text('max_lease_time',$dhcp->max_lease_time,'width:60px;font-size:13px;padding:3px')."&nbsp;seconds</td>
				<td>&nbsp;</td>
				<td >".help_icon('{max_lease_time_text}')."</td>
			</tr>	
			
			<tr>
				<td class=legend style='font-size:13px'>{subnet}:</td>
				<td>".field_ipv4('subnet',$dhcp->subnet,null,false)."</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>			
			<tr>
				<td class=legend style='font-size:13px'>{netmask}:</td>
				<td>".field_ipv4('netmask',$dhcp->netmask,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{gateway}:</td>
				<td>".field_ipv4('gateway',$dhcp->gateway,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{only_use_this} ({gateway})</td>
				<td>".Field_checkbox("OnlySetGateway", 1,$dhcp->OnlySetGateway,"OnlySetGatewayCheck()")."</td>
				<td>&nbsp;</td>
			</tr>			
					
			<tr>
				<td class=legend style='font-size:13px'>{DNSServer} 1:</td>
				<td>".field_ipv4('DNS_1',$dhcp->DNS_1,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{DNSServer} 2:</td>
				<td>".field_ipv4('DNS_2',$dhcp->DNS_2,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{wins_server}:</td>
				<td>".field_ipv4('WINSDHCPSERV',$dhcp->WINS,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>					
			<tr>
				<td class=legend style='font-size:13px'>{ntp_server} <span style='font-size:10px'>({optional})</span>:</td>
				<td>".Field_text('ntp_server',$dhcp->ntp_server,'width:228px;font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:13px'>{range} {from}:</td>
				<td>".field_ipv4('range1',$dhcp->range1,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{range} {to}:</td>
				<td>".field_ipv4('range2',$dhcp->range2,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>			
			<tr>
				<td class=legend style='font-size:13px'>{broadcast}:</td>
				<td>".field_ipv4('broadcast_dhcp_main',$dhcp->broadcast,'font-size:13px;padding:3px')."&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>					
			<tr>
				<td colspan=4 align='right'><hr>
				". button("{edit}","SaveDHCPSettings()")."
					
				
				</td>
			</tr>		
			</table>
			
			</div><br>
			<script>
				function OnlySetGatewayCheck(){
					var XHR = new XHRConnection();
					if(document.getElementById('OnlySetGateway').checked){XHR.appendData('OnlySetGateway',1);	}else{XHR.appendData('OnlySetGateway',0);}
					XHR.sendAndLoad('$page', 'POST');
					OnlySetGatewayFCheck();					
					
					
				}
				
				function OnlySetGatewayFCheck(){
					if(document.getElementById('OnlySetGateway').checked){
						document.getElementById('EnableArticaAsDNSFirst').disabled=true;
					}else{
						document.getElementById('EnableArticaAsDNSFirst').disabled=false;
					}
					
					if(!document.getElementById('IncludeDHCPLdapDatabase').checked){
						document.getElementById('EnableDHCPUseHostnameOnFixed').disabled=true;}else{
						document.getElementById('EnableDHCPUseHostnameOnFixed').disabled=false;
						}
					
					
				
				}				
				
				
				
				
			OnlySetGatewayFCheck();
			</script>
			
		
	";

	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		
	
}

function dhcp_computers_scripts(){
	$dhc=new dhcpd();
	$array=$dhc->LoadfixedAddresses();
	if(!is_array($array)){return null;}
	
	$html="
	<table style='width:100%'>
	";
	
	
		$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99.5%'>
<thead class='thead'>
	<tr>
	<th colspan=4>&nbsp;{fixedHosts}</th>
	</tr>
</thead>
<tbody class='tbody'>";	

	
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$#", trim($num))){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne["MAC"]=str_replace("hardware ethernet","",$ligne["MAC"]);
		$js=MEMBER_JS("$num$",1,1);
		$html=$html . "
		<tr  class=$classtr>
			<td valign='top'><img src='img/computer-32.png'></td>
			<td><strong><a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:14px'>$num</a></td>
			<td><strong><a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:14px'>{$ligne["MAC"]}</a></td>
			<td><strong><a href=\"javascript:blur();\" OnClick=\"javascript:$js\" style='font-size:14px'>{$ligne["IP"]}</a></td>
		</tr>
			
		";
		
	}
	
	$html=$html."</tbody></table>";
	$tpl=new templates();
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
}

function dhcp_scripts(){
	$dhcp=new dhcpd();
	
	if(trim($dhcp->conf)==null){
		$dhcp->conf="{ERROR_NO_CONFIG_SAVED}";
	}
	
	$html="
	<textarea style='width:100%;height:400px;border:1px solid #CCCCCC;background-color:white;font-size:12px'>$dhcp->conf</textarea>";
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);		
	
}

function dhcp_switch(){
	switch ($_GET["dhcp-tab"]) {
		case "status":dhcp_index();break;
		case "config":echo dhcp_form();break;
		case "hosts":echo dhcp_computers_scripts();break;
	}
	
	
}

function dhcp_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?dhcpd-status=yes")));
	$status=DAEMON_STATUS_ROUND("DHCPD",$ini);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($status);
}

function dhcp_tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["config"]='{settings}';
	$array["shared-network"]='{groups}';
	$array["hosts"]='{hosts}';
	$array["leases"]='{leases}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="shared-network"){
			$html[]= "<li><a href=\"dhcpd.shared-networks.php\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		if($num=="leases"){
			$html[]= "<li><a href=\"dhcpd.leases.php\"><span>$ligne</span></a></li>\n";
			continue;
		}		
		
		$html[]= "<li><a href=\"$page?dhcp-tab=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_config_dhcpd style='width:100%;height:700px;overflow:auto'>
		<ul>". $tpl->_ENGINE_parse_body(implode("\n",$html))."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_dhcpd').tabs();
			});
		</script>";		
}	
	


function dhcp_index(){
	$page=CurrentPageName();
	$config=Paragraphe("64-settings.png","{APP_DHCP_MAIN_CONF}","{APP_DHCP_MAIN_CONF_TEXT}","javascript:YahooWin3(700,'index.gateway.php?show-script=yes','{APP_DHCP_MAIN_CONF}');");
	$pxe=	Paragraphe("pxe-64.png","{PXE}","{PXE_DHCP_MINI_TEXT}","javascript:PxeConfig();");
	$routes=Buildicon64('DEF_ICO_DHCP_ROUTES');
	$events=Buildicon64('DEF_ICO_DHCP_EVENTS');
	$pcs=Buildicon64('DEF_ICO_BROWSE_COMP');
	$enable=Paragraphe("modem-64.png","{EnableDHCPServer}","{EnableDHCPServer_text}",
	"javascript:EnableDHCPServerForm()","{EnableDHCPServer_text}");
	
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td valign='top'>
			<H1>{APP_DHCP}</H1>
			<div id='dhcp-status'></div>
		</td>
		<td valign='top'>
	
			<table style='width:100%'>
			<tr>
				<td valign='top'>$enable</td>
				<td valign='top'>$config</td>
				
			</tr>
			<tr>
				
				<td valign='top'>$routes</td>
				<td valign='top'>$pxe</td>
				
			</tr>
			<tr>
				<td valign='top'>$events</td>
				<td valign='top'>$pcs</td>
			</tr>
			</table>
	</td>
</tr>
</table>
<script>
	LoadAjax('dhcp-status','$page?dhcp-status=yes');
</script>


	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);

}

function dhcp_pxe_save(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$dhcp=new dhcpd();
	while (list ($index, $line) = each ($_GET) ){
		$dhcp->$index=$line;
	}
	$dhcp->Save();
	
}

function dhcp_enable(){
	$sock=new sockets();
	$form=Paragraphe_switch_img("{EnableDHCPServer}","{EnableDHCPServer_text}","EnableDHCPServer",
	$sock->GET_INFO("EnableDHCPServer"),"EnableDHCPServer_text",330);
	$html="
	$form
	<div style='text-align:right;width:100%'>
	<HR>
		". button("{edit}","EnableDHCPServerSave()")."</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function dhcp_enable_save(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$dhcp=new dhcpd();
	$sock=new sockets();
	$sock->SET_INFO('EnableDHCPServer',$_GET["EnableDHCPServer"]);
	$dhcp->Save();
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}");
}

function dhcp_save(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	
	$dhcp=new dhcpd();
	$sock=new sockets();
	$sock->SET_INFO('EnableDHCPServer',$_GET["EnableDHCPServer"]);
	$sock->SET_INFO('EnableDHCPUseHostnameOnFixed',$_GET["EnableDHCPUseHostnameOnFixed"]);
	$sock->SET_INFO("IncludeDHCPLdapDatabase", $_GET["IncludeDHCPLdapDatabase"]);
	
	
	$dhcp->listen_nic=$_GET["dhcp_listen_nic"];
	$dhcp->ddns_domainname=$_GET["ddns_domainname"];
	$dhcp->max_lease_time=$_GET["max_lease_time"];
	$dhcp->netmask=$_GET["netmask"];
	$dhcp->range1=$_GET["range1"];
	$dhcp->range2=$_GET["range2"];
	$dhcp->subnet=$_GET["subnet"];
	$dhcp->broadcast=$_GET["broadcast"];
	$dhcp->WINS=$_GET["WINS"];
	$dhcp->ping_check=$_GET["DHCPPing_check"];
	$dhcp->authoritative=$_GET["DHCPauthoritative"];
	
	
	 
	
	$tpl=new templates();

	$dhcp->gateway=$_GET["gateway"];
	$dhcp->DNS_1=$_GET["DNS_1"];
	$dhcp->DNS_2=$_GET["DNS_2"];
	$dhcp->ntp_server=$_GET["ntp_server"];

	$dhcp->EnableArticaAsDNSFirst=$_GET["EnableArticaAsDNSFirst"];
	$dhcp->Save();
	}
	
	
	
function OnlySetGateway_save(){
	$sock=new sockets();
	$sock->SET_INFO("DHCPOnlySetGateway", $_POST["OnlySetGateway"]);
}	
	
	
function gateway_enable(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$artica=new artica_general();
	$enable=Paragraphe_switch_img('{ARTICA_AS_GATEWAY}','{ARTICA_AS_GATEWAY_EXPLAIN}','EnableArticaAsGateway',$artica->EnableArticaAsGateway);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($enable);		
}
function gateway_page(){
	$artica=new artica_general();
	$page=CurrentPageName();
	$html="
	<form name='ffm2'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='gayteway_enable'>" . gateway_enable()."</div></td>
		<td valign='top'><input type='button' OnClick=\"javascript:ParseForm('ffm2','$page',true,false,false,'gayteway_enable','$page?gayteway_enable=yes');\" value='{edit}&nbsp;&raquo;'>
		</td>
	</tr>
	</table>
	</form>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);			
}

function gateway_save(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$artica=new artica_general();
	$artica->EnableArticaAsGateway=$_GET["EnableArticaAsGateway"];
	$artica->Save();
	$dhcp=new dhcpd();
	$dhcp->Save();
	
}

function popup_networks_masks(){
	include_once(dirname(__FILE__)."/ressources/class.tcpip.inc");
	include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
	$net=new networking();
	$class_ip=new IP();
	$array=$net->ALL_IPS_GET_ARRAY();
	while (list ($index, $line) = each ($array) ){
		$ip=$index;
		if(preg_match('#(.+?)\.([0-9]+)$#',$ip,$re)){
			$ip_start=$re[1].".0";
			$ip_end=$re[1].".255";
			$cdir=$class_ip->ip2cidr($ip_start,$ip_end);
			if(preg_match("#(.+)\/([0-9]+)#",$cdir,$ri)){
				$ipv4=new ipv4($ri[1],$ri[2]);
				$netmask=$ipv4->netmask();
				$hosts=$class_ip->HostsNumber($index,$netmask);
				$html=$html."
				<tr>
					<td style='font-size:13px;font-weight:bold'>$ip_start</td>
					<td style='font-size:13px;font-weight:bold'>$netmask</td>
					<td style='font-size:13px;font-weight:bold'>$hosts</td>
					
				</tr>";
			}
		}
		
		
	}
	

	
	$html="<H1>{newtork_help_me}</H1>
	<p class=caption>{you_should_use_one_of_these_network}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<th>{from_ip_address}</th>
		<th>{netmask}</th>
		<th>{hosts_number}</th>
	</tr>
	$html
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}



?>