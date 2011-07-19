<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}
$page="
<div id='rightInfos'>" . PageSystem_interfaces() . "</div>";
$tpl=new template_users('{nic_interfaces}',$page);
echo $tpl->web_page;



function PageSystem_interfaces(){
	$tpl=new templates();
	$sys=new systeminfos();
	if(!is_array($sys->array_ip)){return $tpl->_ENGINE_parse_body('{system error} line' . __LINE__);}
	
	
	
	
	while (list ($num, $val) = each ($sys->array_ip) ){
		$ip=$ip . "<tr>
		<td width=1%><img src='img/nic-table.jpg'></td>
		<td width=1%>$num</td>
		<td>{$val["MAC"]}</td>
		<td >{$val["IP"]}</td>
		</tr>";
		}	
		
		
if(is_array($sys->array_dns_servers)){
	while (list ($num, $val) = each ($sys->array_dns_servers) ){
		$count=$count+1;
		$dns=$dns . "<tr>
		<td width=1%><img src='img/nic-table.jpg'></td>
		<td width=50%>{dns_server} $count</td>
		<td width=49%>$val</td>
		</tr>";
		}	
	
}
	
	$html=
"<table style='width:600px' align=center>
<tr>
<td valign='top'>
	<img src='img/150-nic.jpg'>
	<table style='width:100%;margin-top:5px'>
	<tr>
	<td valign='top' >".Paragraphe('folder-equerre-64.jpg','{nic_static_dns}','{nic_static_dns_text}','system.nic.staticdns.php') ."</td>
	</tr>
	<tr>
	<td valign='top' >".Paragraphe('64-ip-settings.png','{net_settings}','{net_settings_text}','system.nic.config.php') ."</td>
	</tr>	
	
	
	
	</table>
	
</td>
<td valign='top'>	
" . RoundedLightGrey("
	<h5>{nic_interfaces}</H5>
		<table style='width:70%' align='center'>
		$ip
		</table>
	")."<br>" . RoundedLightGreen("
	<h5>{dns_servers}</H5>
		<table style='width:70%' align='center'>
		$dns
		</table>		
		")."
	</td>
	</tr>
	</table>";
	
	return $tpl->_ENGINE_parse_body($html);	
	
}
