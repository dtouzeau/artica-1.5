<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.system.nics.inc');
	
	
	
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsSystemAdministrator==false){exit;}
	if(isset($_POST["UseSnort"])){UseSnort();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["ipconfig-nic"])){ipconfig_nic();exit;}
	if(isset($_GET["save_nic"])){save_nic();exit;}
	
	if(isset($_GET["ipconfig-routes"])){ipconfig_routes();exit;}
	if(isset($_GET["ifconfig-route-list"])){ipconfig_routes_list();exit;}
	if(isset($_GET["add-routes"])){ipconfig_routes_add();exit;}
	if(isset($_GET["del-routes"])){ipconfig_routes_del();exit;}	
	
	
	js();

	
function js(){
	$page=CurrentPageName();
	
	$html="YahooWin2('480','$page?tabs=yes&netconfig={$_GET["nic"]}','{$_GET["nic"]}');";
	echo $html;
	
	
}	

function UseSnort(){
	$eth=$_POST["eth"];
	$value=$_POST["UseSnort"];
	$sock=new sockets();
	$snortInterfaces=unserialize(base64_decode($sock->GET_INFO("SnortNics")));
	if($value==0){
		unset($snortInterfaces[$eth]);
	}else{
		$snortInterfaces[$eth]=1;
	}
	
	$sock->SaveConfigFile(base64_encode(serialize($snortInterfaces)),"SnortNics");
	$sock->getFrameWork("cmd.php?restart-snort=yes");
}


function tabs(){
	$nic=$_GET["netconfig"];
	if(strlen($_GET["nic"])>3){$nic=$_GET["nic"];}
	$tpl=new templates();
	$page=CurrentPageName();
	$array["ipconfig-nic"]='{parameters}';
	$array["ipconfig-routes"]='{routes}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes&nic=$nic\"><span>$ligne</span></a></li>\n";
	}
	
	
	$html= "
	<div id=main_config_$nic style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_$nic').tabs();
			});
		</script>";		
	echo $tpl->_ENGINE_parse_body($html);
}

function ipconfig_nic(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}	
	$eth=$_GET["nic"];
	$users=new usersMenus();
	if($users->SNORT_INSTALLED){
		$EnableSnort=$sock->GET_INFO("EnableSnort");
		if($EnableSnort<>1){$jsSnort="DisableSnortInterface();";}
		$snortInterfaces=unserialize(base64_decode($sock->GET_INFO("SnortNics")));

	}
	if(!$users->SNORT_INSTALLED){$jsSnort="DisableSnortInterface();";}
	
	
	
	$nic=new system_nic($_GET["nic"]);
	
	
	
	
	$html="
	<div id='edit-config-$eth'>
	<form name='ffm$eth'>
	<table style='width:100%'>
	<input type='hidden' name='save_nic' id='save_nic' id='save_nic' value='$eth'>
	
	<tr>
		<td class=legend style='font-size:14px'>{enabled}:</td>
		<td width=1%>" . Field_checkbox('enabled',1,$nic->enabled,'SwitchDHCP()')."</td>
	</tr>		
	<tr>
		<td class=legend style='font-size:14px'>{use_dhcp}:</td>
		<td width=1%>" . Field_checkbox('dhcp',1,$nic->dhcp,'SwitchDHCP()')."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{enable_ids}:</td>
		<td width=1%>" . Field_checkbox('UseSnort',1,$snortInterfaces[$eth],'SwitchSnort()')."</td>
	</tr>
	
	</tr>
	</table>
	
	
	
	<table style='width:99.5%' class=form>
		
		<tr>
			<td class=legend style='font-size:14px'>{tcp_address}:</td>
			<td>" . field_ipv4("IPADDR",$nic->IPADDR,'padding:3px;font-size:18px',null,null,null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:14px'>{netmask}:</td>
			<td>" . field_ipv4("NETMASK",$nic->NETMASK,'padding:3px;font-size:18px',null,null,null,false,null,$DISABLED)."</td>
		</tr>	
		<tr>
			<td class=legend style='font-size:14px'>{gateway}:</td>
			<td>" . field_ipv4("GATEWAY",$nic->GATEWAY,'padding:3px;font-size:18px',null,null,null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:14px'>{broadcast}:</td>
			<td>" . field_ipv4("BROADCAST",$nic->BROADCAST,'padding:3px;font-size:18px',null,null,null,false,null,$DISABLED)."</td>
		</tr>		
	</table>
	
	<br>
	
	<table style='width:99.5%' class=form>
	<tr>
		<td class=legend style='font-size:14px'>{primary_dns}:</td>
		<td>" . field_ipv4("DNS_1",$nic->DNS1,'padding:3px;font-size:18px',null,null,null,false,null)."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{secondary_dns}:</td>
		<td>" . field_ipv4("DNS_2",$nic->DNS2,'padding:3px;font-size:18px',null,null,null,false,null)."</td>
	</tr>	
	</table>
		
	
	
	<table style='width:100%'>
	<tr>
	<td align='right'>
		". button("{edit}","SaveNicSettings()")."
	</td>
	</tr>
	</table>
	</div>
	<script>
	
		var X_SaveNicSettings= function (obj) {
			var results=obj.responseText;
			if(document.getElementById('main_config_$eth')){RefreshTab('main_config_$eth');}
				
			}

		function logofff(){
			var ipaddr=document.getElementById('IPADDR').value;
			document.location.href='https://'+ipaddr+':{$_SERVER['SERVER_PORT']}';
		}
	
		function SaveNicSettings(){
			var XHR = new XHRConnection();
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			if(document.getElementById('dhcp').checked){XHR.appendData('dhcp','1');}else{XHR.appendData('dhcp','0');}
			if(document.getElementById('enabled').checked){XHR.appendData('enabled','1');}else{XHR.appendData('enabled','0');}
			XHR.appendData('IPADDR',document.getElementById('IPADDR').value);
			XHR.appendData('NETMASK',document.getElementById('NETMASK').value);
			XHR.appendData('GATEWAY',document.getElementById('GATEWAY').value);
			XHR.appendData('DNS_1',document.getElementById('DNS_1').value);
			XHR.appendData('DNS_2',document.getElementById('DNS_2').value);
			XHR.appendData('BROADCAST',document.getElementById('BROADCAST').value);
			XHR.appendData('save_nic',document.getElementById('save_nic').value);
			if(document.getElementById('zlistnic-info-$eth')){AnimateDiv('zlistnic-info-$eth');}
			if(document.getElementById('edit-config-$eth')){AnimateDiv('edit-config-$eth');}
			XHR.sendAndLoad('$page', 'GET',X_SaveNicSettings);
			
		}
		
		var x_SwitchSnort= function (obj) {
			RefreshTab('tabs_listnics2');
			}		
		
		
		
		function SwitchSnort(){
			var XHR = new XHRConnection();
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
			if(document.getElementById('UseSnort').checked){XHR.appendData('UseSnort','1');}else{XHR.appendData('UseSnort','0');}
			XHR.appendData('eth','$eth');
			XHR.sendAndLoad('$page', 'POST',x_SwitchSnort);
		}
		
		
		function LockNic(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			document.getElementById('dhcp').disabled=true;
			document.getElementById('IPADDR').disabled=true;
			document.getElementById('NETMASK').disabled=true;
			document.getElementById('GATEWAY').disabled=true;
			document.getElementById('DNS_1').disabled=true;
			document.getElementById('DNS_2').disabled=true;
			document.getElementById('save_nic').disabled=true;
			if(DisableNetworksManagement==1){return;}
			document.getElementById('dhcp').disabled=false;
			document.getElementById('IPADDR').disabled=false;
			document.getElementById('NETMASK').disabled=false;
			document.getElementById('GATEWAY').disabled=false;
			document.getElementById('DNS_1').disabled=false;
			document.getElementById('DNS_2').disabled=false;
			document.getElementById('save_nic').disabled=false;	
			if(document.getElementById('zlistnic-info-$eth')){LoadAjax('zlistnic-info-$eth','system.nic.config.php?nic-builder=$eth');}
			SwitchDHCP();		
		
		}
		
	function SwitchDHCP(){
		document.getElementById('IPADDR').disabled=true;
		document.getElementById('NETMASK').disabled=true;
		document.getElementById('GATEWAY').disabled=true;
		document.getElementById('BROADCAST').disabled=true;
		document.getElementById('dhcp').disabled=true;
		
		if(document.getElementById('enabled').checked==false){return;}
		
		document.getElementById('dhcp').disabled=false;
		if(document.getElementById('dhcp').checked==true){return;}
		
		document.getElementById('IPADDR').disabled=false;
		document.getElementById('NETMASK').disabled=false;
		document.getElementById('GATEWAY').disabled=false;
		document.getElementById('BROADCAST').disabled=false;
		
	}		
	
	function DisableSnortInterface(){
		document.getElementById('UseSnort').disabled=true;
	}
		
	$jsSnort	
	LockNic();
	</script>	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function save_nic(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ip=new networking();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}
	$nic=trim($_GET["save_nic"]);
	$IPADDR=trim($_GET["IPADDR"]);
	$NETMASK=trim($_GET["NETMASK"]);
	$GATEWAY=trim($_GET["GATEWAY"]);
	$BROADCAST=trim($_GET["BROADCAST"]);
	$DNS_1=$_GET["DNS_1"];
	$DNS_2=$_GET["DNS_2"];
	$dhcp=trim($_GET["dhcp"]);
	$arrayNic=$ip->GetNicInfos($nic);
	
	
	$q=new mysql();
	$sql="SELECT ipaddr FROM nic_virtuals WHERE ipaddr='$IPADDR'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["ipaddr"]<>null){
		echo $tpl->javascript_parse_text("{already_used}: $IPADDR (Virtual)");
		return;
	}
	
	$sql="SELECT ipaddr FROM nic_vlan WHERE ipaddr='$IPADDR'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["ipaddr"]<>null){
		echo $tpl->javascript_parse_text("{already_used}: $IPADDR (VLAN)");
		return;
	}	
	
	$ROUTES=base64_encode(serialize($arrayNic["ROUTES"]));
	
	if(!$ip->checkIP($IPADDR)){echo "CheckIP: Address: $IPADDR = False;\n";return;}
	if(!$ip->checkIP($NETMASK)){echo "CheckIP: NetMask $NETMASK = False;\n";return;}
	if($GATEWAY<>"0.0.0.0"){
		if(!$ip->checkIP($GATEWAY)){echo "CheckIP: Gateway $GATEWAY = False;\n";return;}
	}
	if($DNS_1<>null){
		if(!$ip->checkIP($DNS_1)){echo "CheckIP: DNS 1 $DNS_1 = False;\nOr set null value to remove this message";return;}	
	}
	
	if($DNS_2<>null){
		if(!$ip->checkIP($DNS_2)){echo "CheckIP: DNS 2 $DNS_2 = False;\nOr set null value to remove this message";return;}	
	}	
	
	$tpl=new templates();
	$nics=new system_nic($nic);
	$nics->eth=$nic;
	$nics->IPADDR=$IPADDR;
	$nics->NETMASK=$NETMASK;
	$nics->GATEWAY=$GATEWAY;
	$nics->BROADCAST=$BROADCAST;
	$nics->DNS1=$DNS_1;
	$nics->DNS2=$DNS_2;
	$nics->dhcp=$_GET["dhcp"];
	$nics->enabled=$_GET["enabled"];
	if($nics->SaveNic()){echo $tpl->javascript_parse_text('{success}\n{success_save_nic_infos}');}
}

function ipconfig_routes(){
	$ip=new networking();
	$eth=$_GET["nic"];
	$nic=$_GET["nic"];
	$page=CurrentPageName();
	$arrayNic=$ip->GetNicInfos($eth);
	$page=CurrentPageName();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$users=new usersMenus();
	if($users->AsSystemAdministrator){$AsNetworksAdministrator=1;}else{$AsNetworksAdministrator=0;}

	$html="
	<center>
	<table style='width:100px' class=form id='routes-$eth'>
		<tr>
			<td class=legend width=1% nowrap>{from_ip_address}:</td>
			<td width=1% nowrap>" . Field_text('ip_1',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('ip_2',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('ip_3',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td style='font-size:13px' width=1% nowrap><strong>0</strong></td>	 
		 </tr>
		 <tr>
			<td class=legend>{netmask}:</td>
			<td width=1% nowrap>" . Field_text('mask_1',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('mask_2',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('mask_3',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('mask_4',null,'width:35px;font-size:13px;padding:3px')."</td>
		 </tr>
		<tr>
			<td class=legend width=1% nowrap>{gateway}:</td>
			<td width=1% nowrap>" . Field_text('gw_1',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('gw_2',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('gw_3',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px' width=1% nowrap>.</td>
			<td width=1% nowrap>" . Field_text('gw_4',null,'width:35px;font-size:13px;padding:3px')."</td>	 
		</tr>	
			<td colspan=8 align='right' ><hr>". button("{add}","AddRouteIpNic{$_GET["nic"]}()")."</td>
		</tr>		 				
	</table>	
	</center>
	
	<div id='routes-list-{$_GET["nic"]}' style='height:225px;width:100%;overflow:auto'></div>
	
	<script>
		var x_AddRouteIpNic{$_GET["nic"]}= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			RefreshTab('main_config_$nic');
			
			
			}		
	
	
		function AddRouteIpNic{$_GET["nic"]}(){
			var AsNetworksAdministrator='$AsNetworksAdministrator';
			if(AsNetworksAdministrator!=='1'){alert('$ERROR_NO_PRIVS');return;}				
			var XHR=XHRParseElements('routes-{$_GET["nic"]}');
			XHR.appendData('add-routes','yes');
			XHR.appendData('eth','{$_GET["nic"]}');
			XHR.appendData('nic','{$_GET["nic"]}');
			XHR.sendAndLoad('$page', 'GET',x_AddRouteIpNic{$_GET["nic"]});		
		
		}
	
	LoadAjax('routes-list-{$_GET["nic"]}','$page?ifconfig-route-list=yes&nic={$_GET["nic"]}');
	</script>";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function ipconfig_routes_list(){
	$ip=new networking();
	$eth=$_GET["nic"];
	$nic=$_GET["nic"];
	$page=CurrentPageName();
	$nicClass=new system_nic($_GET["nic"]);
	writelogs("Loading routes from {$_GET["nic"]}",__FUNCTION__,__FILE__,__LINE__);
	$routes=$nicClass->ROUTES;
	$users=new usersMenus();
	if($users->AsSystemAdministrator){$AsNetworksAdministrator=1;}else{$AsNetworksAdministrator=0;}	
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");	
 	if(!is_array($routes)){return null;}
 	$page=CurrentPageName();
 	
 	$html="
 	<br>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th nowrap>{from_ip_address}</th>
	<th nowrap>{gateway}</th>
	<th nowrap>&nbsp;</th>
	
	</tr>
</thead>
<tbody class='tbody'>" ;
 $classtr=null;	
 	
 	
	   	 while (list ($ip, $ip_array) = each ($routes) ){
	   	 if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
	   	 $delete=imgtootltip("delete-24.png","{delete}","Delete{$_GET["nic"]}Route('$ip')");
	   	 if(isset($ip_array["DEV"])){$ip_array["GATEWAY"]=$ip_array["DEV"];}
		$html=$html."
			<tr class=$classtr>
			<tr>
				<td style='font-size:14px' width=1% nowrap>$ip/{$ip_array["NETMASK"]}</a></td>
				<td style='font-size:12px' width=1% nowrap>{$ip_array["GATEWAY"]}</td>
				<td style='font-size:12px' width=1%>$delete</td>
			</tr>";

	   	}
	   	
	$html=$html."</table>
	
	<script>
		
		var x_Delete{$_GET["nic"]}Route= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			RefreshTab('main_config_$nic');
		}		
	
	
		function Delete{$_GET["nic"]}Route(ip){
			var AsNetworksAdministrator='$AsNetworksAdministrator';
			if(AsNetworksAdministrator!=='1'){alert('$ERROR_NO_PRIVS');return;}		
			var XHR = new XHRConnection();		
			XHR.appendData('del-routes','yes');
			XHR.appendData('nic','{$_GET["nic"]}');
			XHR.appendData('IP',ip);
			XHR.sendAndLoad('$page', 'GET',x_Delete{$_GET["nic"]}Route);		
		
		}		
	</script>
	";   	
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function ipconfig_routes_add(){
	$user=new usersMenus();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	if(!$user->AsSystemAdministrator){echo $ERROR_NO_PRIVS;return;}
	$ip=new networking();
	$ip1="{$_GET["ip_1"]}.{$_GET["ip_2"]}.{$_GET["ip_3"]}.0";
	$mask="{$_GET["mask_1"]}.{$_GET["mask_2"]}.{$_GET["mask_3"]}.{$_GET["mask_4"]}";
	$gw="{$_GET["gw_1"]}.{$_GET["gw_2"]}.{$_GET["gw_3"]}.{$_GET["gw_4"]}";
	
	if(!$ip->checkIP($ip1)){echo "IP $ip1\nFailed";return;}
	if(!$ip->checkIP($mask)){echo "MASK $mask\nFailed";return;}
	if(!$ip->checkIP($gw)){echo "Gateway $gw\nFailed";return;}	
	$nic=new system_nic($_GET["nic"]);
	$nic->ROUTES[$ip1]=array("NETMASK"=>$mask,"GATEWAY"=>$gw);
	$nic->SaveNic();
	
}

function ipconfig_routes_del(){
	$user=new usersMenus();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	if(!$user->AsSystemAdministrator){echo $ERROR_NO_PRIVS;return;}
	$ip=new networking();
	$nic=new system_nic($_GET["nic"]);
	unset($nic->ROUTES[$_GET["IP"]]);
	$nic->SaveNic();	
}
