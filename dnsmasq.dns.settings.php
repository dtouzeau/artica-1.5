<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');

	
	if(posix_getuid()<>0){
		$user=new usersMenus();
		if($user->AsDnsAdministrator==false){
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
			die();exit();
		}
	}	
	
	
if(isset($_GET["SaveConf1"])){SaveConf1();exit;}
if(isset($_GET["interfaces"])){interfaces();exit;}
if(isset($_GET["InterfacesReload"])){echo LoadInterfaces();exit;}
if(isset($_GET["addressesReload"])){echo Loadaddresses();exit;}
if(isset($_GET["ListentAddressesReload"])){echo LoadListenAddress();exit;}
if(isset($_GET["DnsmasqDeleteInterface"])){DnsmasqDeleteInterface();exit;}

if(isset($_GET["listen_addresses"])){SaveListenAddress();exit;}
if(isset($_GET["DnsmasqDeleteListenAddress"])){DnsmasqDeleteListenAddress();exit;}
if(isset($_GET["EnableDNSMASQ"])){EnableDNSMASQSave();exit;}
if(isset($_GET["get-status"])){status();exit;}




page();

function status(){
	$tpl=new templates();
	if(is_file("ressources/logs/global.status.ini")){
		$ini=new Bs_IniHandler("ressources/logs/global.status.ini");
	}else{
		$sock=new sockets();
		$datas=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));
		$ini=new Bs_IniHandler($datas);
	}
	
	$status=DAEMON_STATUS_ROUND("DNSMASQ",$ini,null);
	echo $tpl->_ENGINE_parse_body($status);

}


function page(){

	$cf=new dnsmasq();
	$page=CurrentPageName();
	$tpl=new templates();
	$sys=new systeminfos();
	$sys->array_interfaces[null]='{select}';
	$sys->array_tcp_addr[null]='{select}';
	$interfaces=Field_array_Hash($sys->array_interfaces,'interfaces',null,"style:font-size:14px;padding:3px;");
	$tcpaddr=Field_array_Hash($sys->array_tcp_addr,'listen_addresses',null,"style:font-size:14px;padding:3px;");
	$sock=new sockets();
	$EnableDNSMASQ=$sock->GET_INFO("EnableDNSMASQ");
	if(!is_numeric($EnableDNSMASQ)){$EnableDNSMASQ=1;}
	// kill -USR1 17226
$html="
<table style='width:100%'>
<tr>
<td width=1% valign='top'><div id='get-status'></div></td>
<td width=99% valign='top'>
<div class=explain id='dnsmaskrool'>{dnsmasq_intro_settings}</div>
</td>
</tr>
</table>
<table style='width:100%' class=form>
<tr>
	<td align='right' valign='top' class=legend>{EnableDNSMASQ}:</td>
	<td align='left' valign='top'>". Field_checkbox("EnableDNSMASQ",1,$EnableDNSMASQ,"EnableDNSMASQSave()")."</td>
	<td align='left' valign='top'  width=1%>". help_icon("{EnableDNSMASQ_explain}")."</td>
</tr>
</table>

<form name='ffm1'>
<table style='width:100%' class=form>
<input type='hidden' name='SaveConf1' value='yes'>

<tr>
	<td align='right' valign='top' class=legend>{domain-needed}:</td>
	<td align='left' valign='top'>" . Field_key_checkbox_img('domain-needed',$cf->main_array["domain-needed"],'{enable_disable}')."</td>
	<td align='left' valign='top'  width=1%>". help_icon("{domain-needed_text}")."</td>
</tr>
<tr>
<td align='right' valign='top' class=legend>{expand-hosts}:</td>
<td align='left' valign='top'   >" . Field_key_checkbox_img('expand-hosts',$cf->main_array["expand-hosts"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{expand-hosts_text}")."</td>
</tr>


<tr>
<td align='right' valign='top' class=legend>{bogus-priv}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('bogus-priv',$cf->main_array["bogus-priv"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{bogus-priv_text}")."</td>
</tr>
<tr>
<td align='right' valign='top'  valign='top'  class=legend>{filterwin2k}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('filterwin2k',$cf->main_array["filterwin2k"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{filterwin2k_text}")."</td>
</tr>
<tr>
<td align='right' valign='top'  valign='top'  class=legend>{strict-order}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('strict-order',$cf->main_array["strict-order"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{strict-order_text}")."</td>
</tr>

<tr>
<td align='right' valign='top'  valign='top' class=legend >{no-resolv}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('no-resolv',$cf->main_array["no-resolv"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{no-resolv_text}")."</td>
</tr>
<tr>
<td align='right' valign='top'  valign='top'  class=legend>{no-negcache}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('no-negcache',$cf->main_array["no-negcache"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{no-negcache_text}")."</td>
</tr>



<tr>
<td align='right' valign='top'  valign='top'  class=legend>{no-poll}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('no-poll',$cf->main_array["no-poll"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{no-poll_text}")."</td>
</tr>

<tr>
<td align='right' valign='top'  valign='top'  class=legend>{log-queries}:</td>
<td align='left' valign='top' >" . Field_key_checkbox_img('log-queries',$cf->main_array["log-queries"],'{enable_disable}')."</td>
<td align='left' valign='top'  width=1%>". help_icon("{log-queries_text}")."</td>
</tr>



</table>

<table style='width:100%'>
</tr>
<td align='right' valign='top'  valign='top'   nowrap class=legend>{resolv-file}:</td>
<td align='left' valign='top' >" . Field_text('resolv-file',$cf->main_array["resolv-file"],"font-size:13px;padding:3px;")."</td>
<td align='left' valign='top'  >". help_icon("{resolv-file_text}")."</td>
</tr>
</tr>
<td align='right' valign='top'  valign='top'   nowrap class=legend>{cache-size}:</td>
<td align='left' valign='top' >" . Field_text('cache-size',$cf->main_array["cache-size"],"font-size:13px;padding:3px;width:70px")."</td>
<td align='left' valign='top'  >". help_icon("{cache-size_text}")."</td>
</tr>

</tr>
<td align='right' valign='top'  valign='top'   nowrap class=legend>{domain}:</td>
<td align='left' valign='top' >" . Field_text('domain',$cf->main_array["domain"],"font-size:13px;padding:3px;")."</td>
<td align='left' valign='top'  >". help_icon("{dnsmasq_domain_explain}")."</td>
</tr>

</tr>
<td colspan=3 align='right'><hr>". button("{apply}","ParseForm('ffm1','$page',true);")."</td>
</tr>
</table>
</form>

<div style='font-size:16px'>{interface}</div>
<div class=explain>{dnmasq_interface_text}</div>
<form name='ffm2'>
<center>
<table style='width:130px'>
	<tr><td valign='middle'>$interfaces</td>
	<td valign='middle'>". button("{add}","ParseForm('ffm2','$page',true);InterfacesReload()")."</td>
	</tr>
</table>
</center>
</form>
<div id='dnmasq_interface'>" . LoadInterfaces() . "</div>

<p>&nbsp;</p>
<div style='font-size:16px'>{dnsmasq_listen_address}</div>
<div class=explain>{dnsmasq_listen_address_text}</div>
<form name='ffm21'>
<center>
<table style='width:170px'>
	<tr>
		<td>$tcpaddr</td>
		<td>". button("{add}","ParseForm('ffm21','$page',true);ListentAddressesReload()")."</td>
	</tr>
</table>
</center>
</form>
<div id='dnsmasq_listen_address'>" . LoadListenAddress() . "</div>






<script>

	var x_EnableDNSMASQSaveBack= function (obj) {
		RefreshTab('main_config_dnsmasq');
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		
	}		
	
	function EnableDNSMASQSave(key){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableDNSMASQ').checked){
			XHR.appendData('EnableDNSMASQ',1);	
		}else{
			XHR.appendData('EnableDNSMASQ',0);	
		}
		document.getElementById('dnsmaskrool').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_EnableDNSMASQSaveBack);
	}	
	
	
	LoadAjax('get-status','$page?get-status=yes');
	
</script>

";
	
echo $tpl->_ENGINE_parse_body($html);
	
}

function LoadInterfaces(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_interface)){return null;}
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=3>&nbsp;</th>
		
	</tr>
</thead>
<tbody class='tbody'>";	
	while (list ($index, $line) = each ($conf->array_interface) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/folder-network-32.png'></td>
			<td  width=99% style='font-size:16px'>$line</td>
			<td  width=1%>" . imgtootltip('delete-32.png','{delete}',"DnsmasqDeleteInterface('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table>");
	
}
function LoadListenAddress(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_listenaddress)){return null;}
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=3>&nbsp;</th>
		
	</tr>
</thead>
<tbody class='tbody'>";
	while (list ($index, $line) = each ($conf->array_listenaddress) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/folder-network-32.png'></td>
			<td  width=99% style='font-size:16px'>$line</td>
			<td  width=1%>" . imgtootltip('delete-32.png','{delete}',"DnsmasqDeleteListenAddress('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}


function SaveConf1(){
	unset($_GET["SaveConf1"]);
	
	if($_GET["resolv-file"]=='/etc/resolv.conf'){$_GET["resolv-file"]="/etc/dnsmasq.resolv.conf";}
	
	$conf=new dnsmasq();
	while (list ($key, $line) = each ($_GET) ){
		if($line<>null){
			$conf->main_array[$key]=$line;	
		}else{unset($conf->main_array[$key]);}
		}
	$conf->SaveConf(); 
}

function interfaces(){
	$conf=new dnsmasq();
	$conf->array_interface[]=$_GET["interfaces"];
	$conf->SaveConf();
}
function DnsmasqDeleteInterface(){
	$conf=new dnsmasq();
	unset($conf->array_interface[$_GET["DnsmasqDeleteInterface"]]);
	$conf->SaveConf();
}



function SaveListenAddress(){
	$addr=$_GET["listen_addresses"];
	$conf=new dnsmasq();
	$conf->array_listenaddress[]=$addr;
	$conf->SaveConf();
}
function DnsmasqDeleteListenAddress(){
	$index=$_GET["DnsmasqDeleteListenAddress"];
	$conf=new dnsmasq();
	unset($conf->array_listenaddress[$index]);
	$conf->SaveConf();
	
}

function EnableDNSMASQSave(){
	$sock=new sockets();
	
	$users=new usersMenus();
	$EnablePDNS=$sock->GET_INFO("EnablePDNS");
	if(!is_numeric($EnablePDNS)){$EnablePDNS=1;}	
	if($_GET["EnableDNSMASQ"]==1){
		if($users->POWER_DNS_INSTALLED){
			if($EnablePDNS==1){
				$tpl=new templates();
				echo $tpl->javascript_parse_text("{COULD_NOT_PERF_OP_SOFT_ENABLED}:\n{APP_PDNS}");
				$sock->SET_INFO("EnableDNSMASQ",0);
				return;
			}
		}

	}
	$sock->SET_INFO("EnableDNSMASQ",$_GET["EnableDNSMASQ"]);
	$sock->getFrameWork("cmd.php?restart-dnsmasq=yes");
}
	