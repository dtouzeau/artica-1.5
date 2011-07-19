<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.users.menus.inc');
	$usersmenus=new usersMenus();
	if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}

	
	if(isset($_GET["ReloadNameServers"])){echo nameserverTable();exit;}
	if(isset($_GET["NicNameServerMove"])){NicNameServerMove();exit;}
	if(isset($_GET["ReloadSearchServers"])){echo SearchTable();exit;}
	if(isset($_GET["NicNameSearchMove"])){NicNameSearchMove();exit;}
	if(isset($_GET["NicNameServerDelete"])){NicNameServerDelete();exit;}
	if(isset($_GET["NicNameSearchDelete"])){NicNameSearchDelete();exit;}
	if(isset($_GET["NicAddSearchDomain"])){NicAddSearchDomain();exit;}
	if(isset($_GET["NicAddNameServer"])){NicAddNameServer();exit;}
	
StartPage();
function StartPage(){

	$usersmenus=new usersMenus();
	if($usersmenus->dnsmasq_installed==true){
		$dnsmasq='{infos_dnsmasq}';
	}
	
	
$searchname=RoundedLightGrey("<H5>{nic_dns_search}</H5>
<p>{nic_dns_search_text}</p>
<center>
<input type=button value='{add_search_domains}&nbsp;&raquo;' OnClick=\"javascript:NicAddSearchDomain();\">
<input type='hidden' id='add_search_domain_text' value='{add_search_domain_text}'>
<div id='searchTable' style='padding:20px'>
		" . SearchTable() . "
	</div>
</center>").
	
//$html=new HtmlPages();
$page="

<table style='width:100%'>
<tr>
<td valign='top'><img align='left' src='img/bg_static-dns.jpg' style='margin:3px'></td>
<td valign='top'><p>{nic_static_dns_text} <strong>$dnsmasq</strong></p>
</td>
</tr>
<tr>
<td colspan=2>
<br>".RoundedLightGrey("



<H5>{nic_nameserver}</H5>
<p>{nic_nameserver_text}</p>
<center>
<input type=button value='{add_nameserver}&nbsp;&raquo;' OnClick=\"javascript:NicAddNameServer()\";>
<input type='hidden' id='add_nameserver_text' value='{add_nameserver_text}'>
<div id='nametable' style='padding:20px'>
	" . nameserverTable() . "
</div>

")."</td></tr></table>";

$JS["JS"][]='js/system.staticdns.js';
$tpl=new template_users('{nic_static_dns}',$page,0,0,0,0,$JS);
echo $tpl->web_page;
}


function SearchTable(){
	$nic=new networking();
	
	if(is_array($nic->array_conf["articanameserverssearchdomains"])){
		$html="<table style='width:250px;border:1px dotted #CCCCCC'>
		<tr>";
		while (list ($index, $articanameserverssearchdomains) = each ($nic->array_conf["articanameserverssearchdomains"]) ){
			if($articanameserverssearchdomains<>'none'){
			$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"NicNameSearchMove('$index','up')") ."</td>";
			$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"NicNameSearchMove('$index','down')") ."</td>";			
			$html=$html . "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><code>$articanameserverssearchdomains</code></td>
			$cell_up
			$cell_down			
			<td>" . imgtootltip('x.gif','{delete}',"NicNameSearchDelete('$index')")."</td>
			</tr>";
			}
		}
	$html=$html . "</table>";
	}
	
	//articanameserverssearchdomains
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function nameserverTable(){
		$nic=new networking();
		if(is_array($nic->arrayNameServers)){
		$html="<table style='width:250px;border:1px dotted #CCCCCC'>
		<tr>";
		while (list ($index, $articanameservers) = each ($nic->arrayNameServers) ){
			if($articanameservers<>'none'){
			$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"NicNameServerMove('$index','up')") ."</td>";
			$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"NicNameServerMove('$index','down')") ."</td>";
			$html=$html . "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><code>$articanameservers</code></td>
			$cell_up
			$cell_down
			<td>" . imgtootltip('x.gif','{delete}',"NicNameServerDelete('$index')")."</td>
			</tr>";
			}
		}
	$html=$html . "</table>";
	}
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
}
function NicNameServerMove(){
	$index=$_GET["NicNameServerMove"];
	$move=$_GET["move"];
	$nic=new networking();
	$newarray=array_move_element($nic->arrayNameServers,$nic->arrayNameServers[$index],$move);
	$nic->BuildResolvConf();
	}
	
function NicNameSearchMove(){
	$index=$_GET["NicNameSearchMove"];
	$move=$_GET["move"];
	$nic=new networking();
	
	$newarray=array_move_element($nic->array_conf["articanameserverssearchdomains"],$nic->array_conf["articanameserverssearchdomains"][$index],$move);
	$ldap=new clladp();
	$dn="cn=system_dns,cn=artica,$ldap->suffix";
	while (list ($index, $articanameservers) = each ($newarray) ){
	$upd["articanameserverssearchdomains"][]=$articanameservers;
	}

	$ldap->Ldap_modify($dn,$upd);	
	}
function NicNameServerDelete(){
	$index=$_GET["NicNameServerDelete"];
	$nic=new networking();
	$nic->nameserver_delete($nic->arrayNameServers[$index]);
	}
function NicNameSearchDelete(){
	$index=$_GET["NicNameSearchDelete"];
	$nic=new networking();
	unset($nic->array_conf["articanameserverssearchdomains"][$index]);
	if(count($nic->array_conf["articanameserverssearchdomains"])==0){
		$upd["articanameserverssearchdomains"][0]='none';
		$ldap=new clladp();
		$dn="cn=system_dns,cn=artica,$ldap->suffix";
		$ldap->Ldap_modify($dn,$upd);			
		return null;
		}
	
	while (list ($index, $articanameservers) = each ($nic->array_conf["articanameserverssearchdomains"]) ){
	$upd["articanameserverssearchdomains"][]=$articanameservers;
	}
	$ldap=new clladp();
	$dn="cn=system_dns,cn=artica,$ldap->suffix";
	$ldap->Ldap_modify($dn,$upd);		
}
function NicAddSearchDomain(){
	$net=new networking();
	$net->SearchDomain_add($_GET["NicAddSearchDomain"]);
	}
function NicAddNameServer(){
	$net=new networking();
	$net->nameserver_add($_GET["NicAddNameServer"]);
	}