<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.system.network.inc');

	
	if(posix_getuid()<>0){
		$user=new usersMenus();
		if($user->AsDnsAdministrator==false){
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
			die();exit();
		}
	}	
	
	if(isset($_GET["address_server"])){SaveAddress();exit;}
	if(isset($_GET["hosts"])){Loadaddresses();exit;}
	if(isset($_GET["DnsmasqDeleteAddress"])){DnsmasqDeleteAddress();exit();}
	
	
	
	page();	
function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$time=time();
	$html="

<div class=explain>{dnsmasq_address_text}</div>
<center>
<div id='dnsmasq-hosts-div-$time'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=5>&nbsp;</th>
		
	</tr>
</thead>
<tbody class='tbody'>
<tr>
	<td nowrap class=legend>{domain_or_server}</td>
	<td>" . Field_text("address_server-$time",null,"font-size:14px;padding:3px;width:170px") . "</td>
	<td nowrap class=legend>{ip}:</td>
	<td><table>
			<tr style='background-color:transparent'>
			<td width=1% nowrap style='border:0px;padding:0px;margin:0px'>" . Field_text('addr_1',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px;border:0px;padding:0px;margin:0px' width=1% nowrap>.</td>
			<td width=1% nowrap style='border:0px;padding:0px;margin:0px'>" . Field_text('addr_2',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px;border:0px;padding:0px;margin:0px' width=1% nowrap>.</td>
			<td width=1% nowrap style='border:0px;padding:0px;margin:0px'>" . Field_text('addr_3',null,'width:35px;font-size:13px;padding:3px')."</td>
			<td style='font-size:13px;border:0px;padding:0px;margin:0px' width=1% nowrap>.</td>
			<td width=1% nowrap style='border:0px;padding:0px;margin:0px'>" . Field_text('addr_4',null,'width:35px;font-size:13px;padding:3px')."</td>	 
		</tr>	
	</table
	</td>
	<td width=1%>". button("{add}","AddDnsMasqHost()")."</td>
	</tr>
	</table>
	</center>
</div>
<div id='array_addresses_$time'></div>


<script>
		var x_AddDnsMasqHost= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}
			DnsMasqLoadHosts();
			}		
	
	
		function AddDnsMasqHost(){	
			var XHR = new XHRConnection();
			XHR.appendData('add-host','yes');
			XHR.appendData('address_server',document.getElementById('address_server-$time').value);
			XHR.appendData('addr_1',document.getElementById('addr_1').value);
			XHR.appendData('addr_2',document.getElementById('addr_2').value);
			XHR.appendData('addr_3',document.getElementById('addr_3').value);
			XHR.appendData('addr_4',document.getElementById('addr_4').value);
			XHR.sendAndLoad('$page', 'GET',x_AddDnsMasqHost);		
		}
		
		function DnsMasqLoadHosts(){
			LoadAjax('array_addresses_$time','$page?hosts=yes');
		}
		
	function DnsmasqDeleteAddress(num){
		var XHR = new XHRConnection();	
		XHR.appendData('DnsmasqDeleteAddress',num);	
		XHR.sendAndLoad('$page', 'GET',x_AddDnsMasqHost);	
		
		
	}		
		
		DnsMasqLoadHosts();
</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}	


function SaveAddress(){
	$ip=new networking();
	$server=$_GET["address_server"];
	if(trim($server)==null){echo "Host cannot be null!\n";return;}
	$adip="{$_GET["addr_1"]}.{$_GET["addr_2"]}.{$_GET["addr_3"]}.{$_GET["addr_4"]}";
	if(!$ip->checkIP($adip)){echo "IP $adip\nFailed";return;}
	$conf=new dnsmasq();
	$conf->array_address[$server]=$adip;
	writelogs("save $server $adip",__FUNCTION__,__FILE__);
	$conf->SaveConf();
	}
function DnsmasqDeleteAddress(){
	$conf=new dnsmasq();
	unset($conf->array_address[$_GET["DnsmasqDeleteAddress"]]);
	$conf->SaveConf();	
}

function Loadaddresses(){
	$conf=new dnsmasq();
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=3>{hosts}</th>
		
	</tr>
</thead>
<tbody class='tbody'>";
	if(is_array($conf->array_address)){
	while (list ($index, $line) = each ($conf->array_address) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html . "
		<tr class=$classtr>
			<td  width=50% style='font-size:16px'>$index</td>
			<td  width=50% style='font-size:16px'>$line</td>
			<td  width=1%>" . imgtootltip('delete-32.png','{delete}',"DnsmasqDeleteAddress('$index');")."</td>
		</tr>";
	}}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}