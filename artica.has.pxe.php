<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
include_once('ressources/class.system.network.inc');

$users=new usersMenus();
if(!$users->AsSystemAdministrator){		
	$tpl=new templates();
	echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableDHCPServer"])){save();exit;}

	js();
function js(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$title=$tpl->_ENGINE_parse_body("{ACTIVATE_ARTICA_ASPXE}");
	echo "YahooWin3('370','$page?popup=yes','$title')";
	}
	
	
function popup(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();			
	$EnableDHCPServer=$sock->GET_INFO('EnableDHCPServer');
	$dhcp=new dhcpd();
	$enable_pxe=Paragraphe_switch_img('{ACTIVATE_ARTICA_ASPXE}','{EnablePXEDHCP}',"pxe_enable",$dhcp->pxe_enable,null,330);
	$enable_dhcp=Paragraphe_switch_img("{EnableDHCPServer}","{EnableDHCPServer_text}","EnableDHCPServer",$EnableDHCPServer,"EnableDHCPServer_text",330);
	$GetMyNicDefault=GetMyNicDefault();
	$GetMyIpDefault=GetMyIpDefault($GetMyNicDefault);
	$defaultgateway=GetMyDefaultGateway($GetMyNicDefault);
	$GetMyINetmaskDefault=GetMyINetmaskDefault($GetMyNicDefault);
	$dnss=unserialize(base64_decode($sock->getFrameWork("cmd.php?ip-get-default-dns=yes")));	
	$html="
	<H1>{thinclients}</H1>
	<div id='dhcppxeform1'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=50%>$enable_dhcp</td>
	</tr>
	<tr>
		<td valign='top' width=50%>$enable_pxe</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SavePXEForm()")."</td>
	</tr>
	</table>		
	<hr>
	<div style='font-size:11px'><i>{default}: $GetMyNicDefault ($GetMyIpDefault/$GetMyINetmaskDefault),{gateway}:$defaultgateway, DNS:". @implode(",",$dnss)."</i></div>
	<script>
	var x_SavePXEForm= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		YahooWin3Hide();
		if(document.getElementById('thinclient-list')){
			thinclient_list();
		}
	}		
	
	
	function SavePXEForm(){
		var XHR = new XHRConnection();
		XHR.appendData('pxe_enable',document.getElementById('pxe_enable').value);
		XHR.appendData('EnableDHCPServer',document.getElementById('EnableDHCPServer').value);
		document.getElementById('dhcppxeform1').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePXEForm);	

	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableDHCPServer",$_GET["EnableDHCPServer"]);
	$dhcp=new dhcpd();
	$pxe_enable=$_GET["pxe_enable"];
	if($dhcp->listen_nic==null){
		$eth=GetMyNicDefault();
		$dhcp->listen_nic=$eth;
	}else{
		$eth=$dhcp->listen_nic;
	}	
	
	if($pxe_enable==1){
		if($dhcp->pxe_server==null){
			$dhcp->pxe_server=GetMyIpDefault($eth);
			$dhcp->pxe_file="/pxe/pxelinux.0";
		}
	}
	
	$dhcp->pxe_enable=$pxe_enable;
	$sock->SET_INFO("EnableDHCPFixPxeThinClient",$pxe_enable);
	if($dhcp->gateway==null){$dhcp->gateway=GetMyDefaultGateway($eth);}
	
	
	if($dhcp->range1==null){$dhcp->range1=GetMyRange($eth).".10";}
	if($dhcp->range2==null){$dhcp->range1=GetMyRange($eth).".250";}
	$dnss=unserialize(base64_decode($sock->getFrameWork("cmd.php?ip-get-default-dns=yes")));	
	if($dhcp->DNS_1==null){$dhcp->DNS_1=$dnss[0];}
	if($dhcp->DNS_2==null){$dhcp->DNS_2=$dnss[1];}
	$dhcp->Save();
	
}

function GetMyIpDefault($eth){
		if(is_array($GLOBALS["IPDEF-$eth"])){return $GLOBALS["IPDEF-$eth"]["IPADDR"];}
		$ip=new networking();
		$GLOBALS["IPDEF-$eth"]=$ip->GetNicInfos($eth);	
		return $GLOBALS["IPDEF-$eth"]["IPADDR"];	
	
}
function GetMyINetmaskDefault($eth){
		if(is_array($GLOBALS["IPDEF-$eth"])){return $GLOBALS["IPDEF-$eth"]["NETMASK"];}
		$ip=new networking();
		$GLOBALS["IPDEF-$eth"]=$ip->GetNicInfos($eth);	
		return $GLOBALS["IPDEF-$eth"]["NETMASK"];	
	
}
function GetMyDefaultGateway($eth){
		if(is_array($GLOBALS["IPDEF-$eth"])){return $GLOBALS["IPDEF-$eth"]["GATEWAY"];}
		$ip=new networking();
		$GLOBALS["IPDEF-$eth"]=$ip->GetNicInfos($eth);	
		return $GLOBALS["IPDEF-$eth"]["GATEWAY"];	
	
}
function GetMyNicDefault(){
		$sock=new sockets();
		$devs=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics")));
		$eth=$devs[0];
		return $eth;
}
function GetMyRange($eth){
	$ipdef=GetMyIpDefault($eth);
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$ipdef)){
		return "{$re[1]}.{$re[1]}.{$re[1]}";
	}
}







?>