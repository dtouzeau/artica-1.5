<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsDnsAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
}	
	
if(isset($_GET["tabs"])){tabs();exit;}


js();

function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_DNSMASQ}");
	$page=CurrentPageName();
	$js=@file_get_contents("js/dnsmasq.js");
	$start="DNSMASQ_START2()";
	if(isset($_GET["popup"])){$start="DNSMASQ_START1()";}
	
	$html="
	function DNSMASQ_START2(){
			if(!document.getElementById('BodyContent')){alert('BodyContent no such id');}
			$('#BodyContent').load('$page?tabs=yes');
		}

	function DNSMASQ_START1(){
		YahooWin2(760,'$page?tabs=yes','$title');
			
		}			
	
	$start;
	
	
	$js
	";
	
	
	echo $html;
	
}

function tabs(){
	
	$sock=new sockets();
	$EnableDNSMASQLDAPDB=$sock->GET_INFO("EnableDNSMASQLDAPDB");
	if(!is_numeric($EnableDNSMASQLDAPDB)){$EnableDNSMASQLDAPDB=0;}
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["params"]='{dnsmasq_DNS_cache_settings}';
	if($EnableDNSMASQLDAPDB==1){$array["record-ldap"]='{ldap_records}';}
	$array["records"]='{dnsmasq_DNS_records}';
	$array["hosts"]='{hosts}';
	$array["logs"]='{events}';
	

	
	
	
	
	

	while (list ($num, $ligne) = each ($array) ){
		if($num=="params"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"dnsmasq.dns.settings.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		if($num=="records"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"dnsmasq.records.settings.php\"><span>$ligne</span></a></li>\n");
			continue;
		}

		if($num=="record-ldap"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"pdns.php?popup=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}				
		
		if($num=="hosts"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"dnsmasq.hosts.settings.php\"><span>$ligne</span></a></li>\n");
			continue;
		}			
		
		if($num=="logs"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"dnsmasq.daemon.events.php\"><span>$ligne</span></a></li>\n");
			continue;
		}			
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_dnsmasq style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_dnsmasq\").tabs();});
		</script>";		
		
	
}
	
	
function page(){
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true){}else{header('location:users.index.php');exit;}	
$html="<table style='width:600px' align=center>
<tr>
<td width=50% valign='top' class='caption' style='text-align:justify'>
<img src='img/bg_dns.jpg'><p>
{dnsmasq_intro}</p></td>
<td valign='top'>
	<table>";

if($usersmenus->AsPostfixAdministrator==true){
		$html=$html . "
		<tr><td valign='top' >".Paragraphe('folder-tools-64.jpg','{dnsmasq_DNS_cache_settings}','{dnsmasq_DNS_cache_settings_text}','dnsmasq.dns.settings.php') ."</td></tr>
		<tr><td valign='top' >".Paragraphe('folder-storage-64.jpg','{dnsmasq_DNS_records}','{dnsmasq_DNS_records_text}','dnsmasq.records.settings.php') ."</td></tr>
		<tr><td valign='top'>  ".Paragraphe('folder-logs-64.jpeg','{events}','{events_text}','dnsmasq.daemon.events.php') ."</td></tr>";
		}

		

		
$html=$html . "</table>
</td>
</tr>
</table>
";
$tpl=new template_users('DnsMasq',$html);
echo $tpl->web_page;
	
	
	
}
	