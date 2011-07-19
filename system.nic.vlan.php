<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	//http://www.mysidenotes.com/2007/08/17/vlan-configuration-on-ubuntu-debian/
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsSystemAdministrator==false){exit;}
	
	if(isset($_GET["vlans-list"])){vlan_list();exit;}
	if(isset($_GET["vlan-popup-add"])){vlan_add_form();exit;}
	if(isset($_GET["cdir-ipaddr"])){vlan_cdir();exit;}
	if(isset($_GET["vlan-ipaddr"])){vlan_add();exit;}
	if(isset($_GET["vlan-del"])){vlan_del();exit;}
	if(isset($_GET["NetWorkBroadCastVLANAsIpAddr"])){NetWorkBroadCastVLANAsIpAddrSave();exit;}
	
	
	vlans_start();
	
	
	
function vlans_start(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_interfaces=$tpl->_ENGINE_parse_body('{virtual_interfaces}');
	$html="
	<div style='float:left'>". imgtootltip("20-refresh.png","{refresh}","VLANRefresh()")."</div>
	
	
	<div id='vlans-list'></div>	
	<script>
	". vlans_js_datas()."
	</script>";
	echo $tpl->_ENGINE_parse_body($html);	
	}

function vlan_add(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	
	if($_GET["nic"]==null){echo $tpl->_ENGINE_parse_body("{nic}=null");exit;}
	
	$sql="INSERT INTO nics_vlan (nic,org,ipaddr,netmask,cdir,gateway)
		VALUES('{$_GET["nic"]}','{$_GET["org"]}','{$_GET["vlan-ipaddr"]}','{$_GET["netmask"]}','{$_GET["cdir"]}','{$_GET["gateway"]}');
		";
	
	if($_GET["ID"]>0){
		$sql="UPDATE nics_vlan SET nic='{$_GET["nic"]}',
		org='{$_GET["org"]}',
		ipaddr='{$_GET["vlan-ipaddr"]}',
		netmask='{$_GET["netmask"]}',
		cdir='{$_GET["cdir"]}',
		gateway='{$_GET["gateway"]}' WHERE ID={$_GET["ID"]}";
	}
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
}	
function vlan_cdir(){
	$ipaddr=$_GET["cdir-ipaddr"];
	$newmask=$_GET["netmask"];
	$ip=new IP();
	if($newmask<>null){echo $ip->maskTocdir($ipaddr, $newmask);}
	}

function vlan_add_form(){
	$ldap=new clladp();
	$sock=new sockets();
	$page=CurrentPageName();
	$nics=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	$title_button="{add}";
	
	
	if($_GET["ID"]>0){
		$sql="SELECT * FROM nics_vlan WHERE ID='{$_GET["ID"]}'";
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
	
	$styleOfFields="width:190px;font-size:14px;padding:3px";
	$ous=$ldap->hash_get_ou(true);
	$ous["openvpn_service"]="{APP_OPENVPN}";
	while (list ($num, $val) = each ($nics) ){
		$nics_array[$val]=$val;
	}
	$nics_array[null]="{select}";
	$ous[null]="{select}";
	
	$nic_field=Field_array_Hash($nics_array,"nic",$ligne["nic"],null,null,0,"font-size:14px;padding:3px");
	$ou_fields=Field_array_Hash($ous,"org",$ligne["org"],null,null,0,"font-size:14px;padding:3px");
	$html="
	<div id='virtip-vlan'>
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
			<td>" . Field_text("ipaddr",$ligne["ipaddr"],$styleOfFields,null,"CalcCdirVirt(0)",null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend>{netmask}:</td>
			<td>" . Field_text("netmask",$ligne["netmask"],$styleOfFields,null,"CalcCdirVirt(0)",null,false,null,$DISABLED)."</td>
		</tr>
		<tr>
			<td class=legend>CDIR:</td>
			<td style='padding:-1px;margin:-1px'>
			<table style='width:99%;padding:-1px;margin:-1px'>
			<tr>
			<td width=1%>
			" . Field_text("cdir",$ligne["cdir"],$styleOfFields,null,null,null,false,null,$DISABLED)."</td>
			<td align='left'> ".imgtootltip("img_calc_icon.gif","cdir","CalcCdirVirt(1)") ."</td>
			</tr>
			</table></td>
		</tr>			
		<tr>
			<td class=legend>{gateway}:</td>
			<td>" . Field_text("gateway",$ligne["gateway"],$styleOfFields,null,null,null,false,null,$DISABLED)."</td>
		</tr>	
	</table>
	</div>
	<div style='text-align:right'><hr>". button($title_button,"VLANaddSave()")."</div>
	<script>
		var cdir=document.getElementById('cdir').value;
		var netmask=document.getElementById('netmask').value;
		if(netmask.length>0){
			if(cdir.length==0){
				CalcCdirVirt(0);
				}
			}
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function vlans_js_datas(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_interfaces=$tpl->_ENGINE_parse_body('{virtual_interfaces}');
	$tpl=new templates();
	$default_load="VLANRefresh();";
	if(isset($_GET["js-add-nic"])){
		$default_load="VirtualIPJSAdd('{$_GET["js-add-nic"]}');";
	}
	
	
	$sock=new sockets();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	
	
	$html="
		var windows_size=500;
	
		function VlanAdd(){
			YahooWin2(windows_size,'$page?vlan-popup-add=yes&default-datas={$_GET["default-datas"]}','VLAN::');
		
		}
		
		function VLANIPJSAdd(nic){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var defaultDatas='';
			if(document.getElementById('infos_'+nic)){
				defaultDatas=document.getElementById('infos_'+nic).value;
			}
			YahooWin2(windows_size,'$page?virtual-popup-add=yes&default-datas='+defaultDatas,'$virtual_interfaces');
		}
		
		function VLANEdit(ID){
			YahooWin2(500,'$page?vlan-popup-add=yes&ID='+ID,'VLAN::'+ID);
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
		
		var X_VLANaddSave= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();
			if(document.getElementById('main_openvpn_config')){RefreshTab('main_openvpn_config');}
			VLANRefresh();
			
		}
		
		function VLANaddSave(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}		
			var XHR = new XHRConnection();
			XHR.appendData('vlan-ipaddr',document.getElementById('ipaddr').value);
			XHR.appendData('netmask',document.getElementById('netmask').value);
			XHR.appendData('cdir',document.getElementById('cdir').value);
			XHR.appendData('gateway',document.getElementById('gateway').value);
			XHR.appendData('nic',document.getElementById('nic').value);
			XHR.appendData('org',document.getElementById('org').value);
			XHR.appendData('ID',document.getElementById('ID').value);
			document.getElementById('virtip-vlan').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',X_VLANaddSave);
		}
		function VLANRefresh(){
			LoadAjax('vlans-list','$page?vlans-list=yes');
		}
		
		function BuildVLANs(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}		
			if(document.getElementById('vlans-list')){
				LoadAjax('vlans-list','$page?vlans-list=yes&build=yes');
			}
		}
		
		function VLANDelete(id){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}		
			document.getElementById('vlans-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			var XHR = new XHRConnection();
			XHR.appendData('vlan-del',id);
			XHR.sendAndLoad('$page', 'GET',X_VLANaddSave);
		}
		
		$default_load	
	";
		
	return $html;
}

function vlan_list(){
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$q=new mysql();
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	
	$sock=new sockets();
	if(isset($_GET["build"])){
		ConstructVLANIP();
		$html="<div style='color:#A90404;font-size:16px'>{operation_launched_in_background}</div>";
	}
	
	$interfaces=unserialize(base64_decode($sock->getFrameWork("cmd.php?ifconfig-interfaces=yes")));
	$sql="SELECT * FROM nics_vlan ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$style=CellRollOver();
	$html=$html."
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add}","VlanAdd()")."</th>
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
		
		$eth="vlan{$ligne["ID"]}/{$ligne["nic"]}";
		if($ligne["cdir"]==null){
			$ligne["cdir"]=$net->array_TCP[$ligne["nic"]];
			$eth=$ligne["nic"];
		}
		$img="22-win-nic-off.png";
		
		if($interfaces["vlan{$ligne["ID"]}"]<>null){
			$img="22-win-nic.png";
		}
		
		if(trim($ligne["org"])==null){
			$ligne["org"]="<strong style='color:red'>{no_organization}</strong>";
		}
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/$img'></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["org"]}</strong></td>
			<td><strong style='font-size:14px' align='right'>$eth</strong></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["ipaddr"]}</strong></td>
			<td><strong style='font-size:14px' align='right'>{$ligne["netmask"]}</strong></td>
			<td width=1%>". imgtootltip("24-administrative-tools.png","{edit}","VLANEdit({$ligne["ID"]})")."</td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","VLANDelete({$ligne["ID"]})")."</td>
		</tr>
		
		
		";
		
	}
	$sock=new sockets();
	$page=CurrentPageName();
	
	$html=$html."</tbody></table></center>
	<p>&nbsp;</p>
	<div style='text-align:right'>". button("{reconstruct_vlans}","BuildVLANs()")."</div>
	<p>&nbsp;</p>
	<table class=form>
	<tr>
		<td class=legend>{broadcast_has_ipaddr}</td>
		<td>". Field_checkbox("NetWorkBroadCastVLANAsIpAddr",1,$sock->GET_INFO("NetWorkBroadCastVLANAsIpAddr"),"NetWorkBroadCastAsVLANIpAddrSave()")."</td>
	</tr>
	</table>
	
	<script>
	
		var X_NetWorkBroadCastAsIpAddrSave= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			
		}
		
		function NetWorkBroadCastAsVLANIpAddrSave(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var XHR = new XHRConnection();
			if(document.getElementById('NetWorkBroadCastVLANAsIpAddr').checked){
			XHR.appendData('NetWorkBroadCastVLANAsIpAddr',1);}else{XHR.appendData('NetWorkBroadCastVLANAsIpAddr',0);}
			XHR.sendAndLoad('$page', 'GET',X_NetWorkBroadCastAsIpAddrSave);
		}	
	
	</script>		
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function vlan_del(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
		if(!is_numeric(trim($_GET["vlan-del"]))){return ;}
		$sql="DELETE FROM nics_vlan WHERE ID={$_GET["vlan-del"]}";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;}
		
		$sql="DELETE FROM iptables_bridge WHERE nics_vlan_id={$_GET["vlan-del"]}";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
				
		
}
function NetWorkBroadCastVLANAsIpAddrSave(){
	$sock=new sockets();
	$sock->SET_INFO("NetWorkBroadCastVLANAsIpAddr",$_GET["NetWorkBroadCastVLANAsIpAddr"]);
}

function ConstructVLANIP(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?vlan-ip-reconfigure=yes");
}

