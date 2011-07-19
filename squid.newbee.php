<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	include_once ("ressources/jpgraph-3/src/jpgraph.php");
	include_once ("ressources/jpgraph-3/src/jpgraph_pie.php");
	include_once ("ressources/jpgraph-3/src/jpgraph_pie3d.php");
	
	
	if(isset($_GET["update-kav"])){apply_kavupdate();exit;}
	if(isset($_GET["update-kav-now"])){apply_kavupdate_perform();exit;}
	if(isset($_GET["update-kav-popup"])){apply_kavupdate_popup();exit;}
	if(isset($_GET["update-kav-logs"])){apply_kavupdate_logs();exit;}	
	
	
		
	if(isset($_GET["kav-license"])){echo kav4proxy_license_js();exit;}
	if(isset($_GET["Kav4proxy-license-popup"])){echo kav4proxy_license_popup();exit;}
	if(isset($_GET["Kav4proxy-license-delete"])){echo kav4proxy_license_delete();exit;}
	if(isset($_GET["kav4proxy-license-iframe"])){echo kav4proxy_license_iframe();exit;}	
	if( isset($_POST['upload']) ){kav4proxy_license_upload();exit();}
	
	
	$user=new usersMenus();
	if($user->SQUID_INSTALLED==false){header('location:users.index.php');exit();}
	if($user->AsSquidAdministrator==false){header('location:users.index.php');exit();}
	
	if(isset($_GET["reactivate-squid"])){reactivate_squid();exit;}
	
	if(isset($_GET["ajaxmenu"])){main_switch();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["fqdncache_size"])){main_save_array();exit;}
	if(isset($_GET["applysquid"])){applysquid();exit;}
	
	
	if(isset($_GET["wait-finish"])){exit;};
	if(isset($_GET["cache-list"])){echo cache_list();exit;}
	
	if(isset($_GET["connection-time"])){echo time_global();exit;}
	if(isset($_GET["connection-time-showgroup"])){echo time_groups();exit;}
	if(isset($_GET["connection-time-rule"])){echo time_rule();exit;}
	if(isset($_GET["end_time_hour"])){time_save();exit;}
	if(isset($_GET["time-rule-list"])){echo time_rule_list($_GET["gpid"],$_GET["ou"]);exit;}
	if(isset($_GET["ConnectionTimeDelete"])){time_rule_delete();exit;}
	
	if(isset($_GET["changecache-js"])){changecache_js();exit;}
	if(isset($_GET["changecache-popup"])){changecache_popup();exit;}
	if(isset($_GET["changecache-popup-content"])){changecache_popup_content();exit;}
	if(isset($_GET["SaveNewChache"])){changecache_save();exit;}
	
	
	
	
	if(isset($_GET["liste-des-caches"])){main_cache_list();exit;}
	
	
	if(isset($_GET["squid-net-loupe-js"])){net_control_center_js();exit;}
	if(isset($_GET["squid-net-loupe-popup"])){net_control_center_popup();exit;}
	if(isset($_GET["squid-transparent-js"])){transparent_js();exit;}
	if(isset($_GET["squid-transparent-popup"])){transparent_popup();exit;}
	if(isset($_GET["squid_transparent"])){transparent_save();exit;}
	
	

	
	if(isset($_GET["Kav4proxy-events-js"])){Kav4proxy_events_js();exit;}
	if(isset($_GET["Kav4proxy-events-popup"])){Kav4proxy_events_popup();exit;}
	if(isset($_GET["Kav4proxy-events-uris"])){echo Kav4proxy_events_daemon();exit;}
	if(isset($_GET["Kav4proxy-events-update"])){echo Kav4proxy_events_update();exit;}

	
	
	
	if(isset($_GET["dansguardian-events-js"])){dansguardian_events_js();exit;}
	if(isset($_GET["dansguardian-events-popup"])){dansguardian_events_popup();exit;}
	if(isset($_GET["dansguardian-events-uris"])){echo dansguardian_events_daemon();exit;}
	
	if(isset($_GET["dansguardian-stats-js"])){dansguardian_stats_js();exit;}
	if(isset($_GET["dansguardian-stats-popup"])){dansguardian_stats_popup();exit;}
	if(isset($_GET["dansguardian-stats-week"])){dansguardian_buildGraph_week();exit;}
	if(isset($_GET["dansguardian-stats-query"])){echo dansguardian_buildGraph_by_type();exit;}
	if(isset($_GET["dansguardian-stats-www"])){echo dansguardian_buildGraph_by_www();exit;}
	if(isset($_GET["dansguardian-stats-compile"])){echo dansguardian_build_stats();exit;}
	if(isset($_GET["DansGuardianRebuildSites"])){dansguardian_stats_rebuild_sites();exit;}
	
	if(isset($_GET["SARG"])){sarg_scan();exit;}
	if(isset($_GET["sarg-js"])){sarg_js();exit;}
	if(isset($_GET["sarg-date"])){sarg_date();exit;}
	
	
	if(isset($_GET["EnableDisableMain"])){main_enableETDisable();exit;}
	if(isset($_GET["SaveEnableSquidGLobal"])){main_enableETDisable_save();}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	
	js();
	
	
	
function sarg_js(){
	
	$tpl=new templates();
	$title=$tpl->_parse_body("{APP_SARG}");
	$page=CurrentPageName();
	$html="
	var mem_date=''; 
	
	function SargBrowseStart(dates){
		mem_date=dates;
		YahooWin4('750','$page?sarg-date='+dates,'$title');
	}	
	
	function StartSarg(){
		var load = window.open('sarg/index.html','','scrollbars=no,menubar=no,height=450,width=750,resizable=yes,toolbar=no,location=no,status=no');
		//YahooWin3('300','$page?SARG=yes','$title');
	}
	
	function SargBrowse(dates){
		YahooWin5('750','$page?sarg-date='+dates+'&date='+mem_date,'$title');
	}
	StartSarg();
	";
	
	echo $html;
	
}
	

function js(){
	
$page=CurrentPageName();
$tpl=new templates();
$prefix=str_replace(".","_",$page);
$title=$tpl->_ENGINE_parse_body("{web_proxy}");
$add_cache=$tpl->_ENGINE_parse_body("{APP_SQUID}");

if(isset($_GET["bullet-id"])){
	$bulltet_1="document.getElementById('{$_GET["bullet-id"]}_menubullet').src='img/wait.gif'";
	$bullet_2="document.getElementById('{$_GET["bullet-id"]}_menubullet').src='img/fullbullet.gif'";
}

$html="
var {$prefix}timerID  = null;
var {$prefix}timerID1  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var m_gpid;
var m_ou;

function {$prefix}load(){
	$bulltet_1
	$('#BodyContent').load('$page?popup=yes', function() {{$prefix}StartPage();});

}

function {$prefix}StartPage(){
	
	$bullet_2	
	//{$prefix}demarre();
}

	function {$prefix}demarre(){
		if(!document.getElementById('squid_main_config')){return;}
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=10-{$prefix}tant;
			if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",5000);
		      } else {
		{$prefix}tant = 0;
		              
		{$prefix}ChargeLogs();
		{$prefix}demarre();                                //la boucle demarre !
		   }
	}


function {$prefix}ChargeLogs(){
	LoadAjax('services_status_squid','squid.index.php?status=yes&hostname={$_GET["hostname"]}&apply-settings=no');
	}
	
function AjaxSquidDemarre(){
	LoadAjax('services_status_squid','squid.index.php?status=yes&hostname={$_GET["hostname"]}&apply-settings=no');
}	
	
function RefreshCaches(){
	document.getElementById('liste-des-caches').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	LoadAjax('liste-des-caches','$page?liste-des-caches=yes');
}
		
	function applysquid(){
		LoadAjax('applysquid','$page?applysquid=yes');
		}
		
		

	




function ConnectionTime(){
	YahooWin(500,'$page?connection-time=yes');
}

function ConnectionTimeSelectOU(){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_field','$page?connection-time-showgroup='+ou);
	}
	
function ConnectionTimeSelectGroup(){
	var ou=document.getElementById('ou').value;
	var gpid=document.getElementById('gpid').value;
	LoadAjax('ConnectionTimeRule','$page?connection-time-rule=yes&ou='+ou+'&gpid='+gpid);
	
}

function ConnecTimeRefreshlist(gpid,ou){
LoadAjax('rule_list','$page?time-rule-list=yes&gpid='+gpid+'&ou='+ou);   
}

var x_ConnectionTimeDelete= function (obj) {
	ConnecTimeRefreshlist(m_gpid,m_ou);
	}

function ConnectionTimeDelete(gpid,ou,d){
	var XHR = new XHRConnection();
	document.getElementById('rule_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'
	m_gpid=gpid;
	m_ou=ou;
	XHR.appendData('ConnectionTimeDelete',d);
	XHR.appendData('gpid',gpid);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('$page', 'GET',x_ConnectionTimeDelete);
}

".js_enable_disable_squid()."

{$prefix}load();";
echo $html;
	
}

function reactivate_squid(){
	
	$js=js_enable_disable_squid()."\nEnableDisableSQUID();";
	echo $js;
}
	
function popup(){
	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body(main_tabs(),'squid.index.php');
	
	echo $html;
	}

function main_index(){
$html="	
<h1>{web_proxy}</H1>
<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
	<img src='img/bg_squid.jpg'></td>
	<td valign='top' style='font-size:12px'><div style='float:right'>". 
imgtootltip("refresh-32.png","{refresh}","AjaxSquidDemarre()")."</div>{APP_SQUID_TEXT}
			<center style='margin:5px'>". button("{restart_all_services}","Loadjs('squid.restart.php')")."</center>
		</td>
	
	</td>
	</tr>
	</table>
	<div id='services_status_squid'></div>
	<br><script>AjaxSquidDemarre()</script>	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function main_switch(){
	
	if(isset($_GET["ajaxmenu"])){echo "<div id='squid_main_config'>";}
	
	switch ($_GET["main"]) {
		case "index":echo main_index();break;
		case "yes":echo main_config();break;
		case "filters":echo main_filter();break;
		case "cache":echo main_cache();break;
		case "events":echo main_events_tabs();break;
		case "icons-events":echo main_events();break;
		
		default:echo main_config();break;
	}
	
	if(isset($_GET["ajaxmenu"])){echo "</div>";}
	
}

function main_tabs(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]='{index}';
	$array["yes"]='{squid_net_settings}';
	$array["filters"]='{filters}';
	$array["bandwith_limitation"]='{bandwith_limitation}';
	$array["cache"]='{cache_title}';
	$array["events"]='{events_stats}';

	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		
		if($num=="cache"){
			$html[]= "<li><a href=\"squid.caches.php?js=yes\"><span>$ligne</span></a></li>\n";
			continue;
		}		
		
		if($num=="bandwith_limitation"){
			$html[]= "<li><a href=\"squid.bandwith.php\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$html[]= "<li><a href=\"$page?main=$num&hostname=$hostname\"><span>$ligne</span></a></li>\n";
		//$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "
	<div id=squid_main_config style='width:750px;heigth:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_main_config').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";			
}	


function main_config(){

$page=CurrentPageName();
$sock=new sockets();

	$users=new usersMenus();
	$your_network=Paragraphe('folder-realyrules-64.png','{your_network}','{your_network_text}',"javascript:Loadjs('squid.popups.php?script=network')");
	$listen_port=Paragraphe('network-connection2.png','{listen_port}','{listen_port_text}',"javascript:Loadjs('squid.popups.php?script=listen_port')");
	$dns_servers=Paragraphe('64-bind.png','{dns_servers}','{dns_servers_text}',"javascript:Loadjs('squid.popups.php?script=dns')");
	$applysquid=applysquid_icon();

	
	$visible_hostname=Paragraphe('64-work-station-linux.png','{visible_hostname}','{visible_hostname_intro}',"javascript:Loadjs('squid.popups.php?script=visible_hostname')");
	$your_network_loupe=Paragraphe('64-win-nic-loupe.png','{your_network_loupe}','{your_network_loupe_text}',"javascript:Loadjs('$page?squid-net-loupe-js=yes')");
	$transparent_mode=Paragraphe('relayhost.png','{transparent_mode}','{transparent_mode_text}',"javascript:Loadjs('$page?squid-transparent-js=yes')");
	$enable_squid_service=Paragraphe('bg-server-settings-64.png','{enable_squid_service}','{enable_squid_service_text}',"javascript:EnableDisableSQUID()");
	
	$squid_advanced_parameters=Paragraphe('64-settings.png','{squid_advanced_parameters}','{squid_advanced_parameters_text}',"javascript:Loadjs('squid.advParameters.php')");
	$squid_parent_proxy=Paragraphe('server-redirect-64.png','{squid_parent_proxy}','{squid_parent_proxy_text}',"javascript:Loadjs('squid.parent.proxy.php')");
	$squid_reverse_proxy=Paragraphe('squid-reverse-64.png','{squid_reverse_proxy}','{squid_reverse_proxy_text}',"javascript:Loadjs('squid.reverse.proxy.php')");

    $proxy_pac=Paragraphe('user-script-64.png','{proxy_pac}','{proxy_pac_text}',"javascript:Loadjs('squid.proxy.pac.php')");
    $proxy_pac_rules=Paragraphe('proxy-pac-rules-64.png','{proxy_pac_rules}','{proxy_pac_text}',"javascript:Loadjs('squid.proxy.pac.rules.php')");
    
    
   
    $sslbump=Paragraphe('web-ssl-64.png','{squid_sslbump}','{squid_sslbump_text}',
    			"javascript:Loadjs('squid.sslbump.php')");
    
    
    $performances_tuning=Paragraphe('performance-tuning-64.png','{tune_squid_performances}','{tune_squid_performances_text}',"javascript:Loadjs('squid.perfs.php')");
    $squid_conf=Paragraphe('script-view-64.png','{configuration_file}','{display_generated_configuration_file}',"javascript:Loadjs('squid.conf.php')");
    
 	$SquidEnableProxyPac=$sock->GET_INFO("SquidEnableProxyPac");	
 	
 	
 	
 	
	if($users->SARG_INSTALLED){
		$sarg=Paragraphe('sarg-logo.png','{APP_SARG}','{display_product_events}',"javascript:Loadjs('sarg.php')","{display_product_events}");
	}


 	
	
	if($sock->GET_INFO("SquidActHasReverse")==1){
		$listen_port=null;
		$proxy_pac=null;
		$proxy_pac_rules=null;
		$SquidEnableProxyPac=0;
		$squid_accl_websites=Paragraphe('website-64.png','{squid_accel_websites}','{squid_accel_websites_text}',"javascript:Loadjs('squid.reverse.websites.php')");
	}
	
	if($sock->GET_INFO("hasProxyTransparent")==1){
		$proxy_pac=null;
		$proxy_pac_rules=null;
		$SquidEnableProxyPac=0;
	}
	
	
if($sock->GET_INFO("SquidEnableProxyPac")<>1){$proxy_pac_rules=null;}	
	
	
	$tr=array();
	$tr[]=$applysquid;
	$tr[]=$your_network;
	$tr[]=$your_network_loupe;
	$tr[]=$listen_port;
	$tr[]=$dns_servers;
	$tr[]=$proxy_pac;
	$tr[]=$proxy_pac_rules;
	$tr[]=$visible_hostname;
	$tr[]=$transparent_mode;
	$tr[]=$performances_tuning;
	$tr[]=$squid_conf;
	$tr[]=$squid_parent_proxy;
	$tr[]=$squid_reverse_proxy;	
	$tr[]=$sslbump;
	$tr[]=$squid_accl_websites;
	$tr[]=$squid_advanced_parameters;
	$tr[]=$enable_squid_service;
	$tr[]=$sarg;

	$html=CompileTr3($tr);
	
	
	
$tpl=new templates();
$html= $tpl->_ENGINE_parse_body($html,'squid.index.php');
SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;
	
}




function js_enable_disable_squid(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{enable_squid_service}');
	
	$html="
	function EnableDisableSQUID(){
		YahooWin('300','$page?EnableDisableMain=yes');
	}
	
var x_SaveEnableSquidGLobal=function (obj) {
		RTMMailHide();
		YahooWinHide();
		Loadjs('squid.newbee.php');
	}		
	
	function SaveEnableSquidGLobal(){
		var XHR = new XHRConnection();
    	XHR.appendData('SaveEnableSquidGLobal',document.getElementById('SQUIDEnable').value);
 		document.getElementById('EnableETDisableSquidDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveEnableSquidGLobal);
	}
";
	return $html;
}


function main_filter(){
$page=CurrentPageName();
$squid=new squidbee();
$users=new usersMenus();
$tpl=new templates();
$sock=new sockets();
$SquidActHasReverse=$sock->GET_INFO("SquidActHasReverse");


$filetype=Paragraphe("pieces-jointes.png","{file_blocking}","{file_blocking_text}","javascript:acl_fileblock()");
$connection_time=Paragraphe("64-planning.png","{connection_time}","{connection_time_text}","javascript:ConnectionTime();");
$denywebistes=Paragraphe("folder-64-denywebistes.png","{deny_websites}","{deny_websites_text}","javascript:Loadjs('squid.popups.php?script=url_regex');");

if($users->KAV4PROXY_INSTALLED){
	if($squid->enable_kavproxy==1){
		$update_kaspersky=Paragraphe('kaspersky-update-64.png','{UPDATE_ANTIVIRUS}','{APP_KAV4PROXY}<br>{UPDATE_ANTIVIRUS_TEXT}',"javascript:Loadjs('$page?update-kav=yes')");
		$license_kaspersky=Paragraphe('64-kav-license.png','{license_info}','{APP_KAV4PROXY}<br>{license_info_text}',"javascript:Loadjs('$page?kav-license=yes')");
		$settings_kaspersky=Paragraphe('kav4proxy-settings-64.png','{parameters}','{APP_KAV4PROXY}<br>{parameters}',"javascript:Loadjs('kav4proxy.php')");
		
	}
}

if($users->C_ICAP_INSTALLED){
	if($squid->enable_cicap==1){
		$cicap=Paragraphe('clamav-64.png','{CICAP_AV}','{CICAP_AV_TEXT}',"javascript:Loadjs('c-icap.index.php');");
		$clamav_unofficial=Paragraphe("clamav-64.png","{clamav_unofficial}","{clamav_unofficial_text}",
	"javascript:Loadjs('clamav.unofficial.php')",null,210,100,0,true);
	}
}
$denywebistes_2="&nbsp;";
if($users->DANSGUARDIAN_INSTALLED){
	if($squid->enable_dansguardian==1){
		$dansguardian=Paragraphe('icon-chevallier-564.png','{DANSGUARDIAN_RULES}','{dansguardian_rules_text}',"javascript:Loadjs('dansguardian.index.php?js=yes&switch=from-squid')");
		$clamav_unofficial=Paragraphe("clamav-64.png","{clamav_unofficial}","{clamav_unofficial_text}",
	"javascript:Loadjs('clamav.unofficial.php')",null,210,100,0,true);
	}}
	
if($users->SQUIDGUARD_INSTALLED){
	if($squid->enable_squidguard==1){
		$dansguardian=Paragraphe('icon-chevallier-564.png','{DANSGUARDIAN_RULES}','{squidguard_rules_text}',"javascript:Loadjs('dansguardian.index.php?js=yes&switch=from-squid')");
		$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)"); 
		$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
				
	}
}


if($users->APP_UFDBGUARD_INSTALLED){
	$ufdbguard_settings=Paragraphe("filter-sieve-64.png","{APP_UFDBGUARD}","{APP_UFDBGUARD_PARAMETERS}",
	"javascript:Loadjs('ufdbguard.php')");	
	if($squid->enable_UfdbGuard==1){
		$dansguardian=Paragraphe('icon-chevallier-564.png','{DANSGUARDIAN_RULES}','{squidguard_rules_text}',"javascript:Loadjs('dansguardian.index.php?js=yes&switch=from-squid')");
		$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)"); 
		$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
				
	}
}
//stop-ads-64-grey.png


if($users->ADZAPPER_INSTALLED){
	if($squid->enable_adzapper==1){
		$addzapper=Paragraphe('stop-ads-64.png','{block_banner_advertisements}',
'{addzapper_block_banner_advertisements}',"javascript:Loadjs('squid.adzapper.php')");
		$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)"); 
		$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");
				
	}
}

if($users->APP_SQUIDCLAMAV_INSTALLED){
	if($squid->enable_squidclamav==1){
		$template=Paragraphe("banned-template-64.png","{template_label}",'{template_explain}',"javascript:s_PopUp('dansguardian.template.php',800,800)"); 
		$squidguardweb=Paragraphe("parameters2-64.png","{banned_page_webservice}","{banned_page_webservice_text}","javascript:Loadjs('squidguardweb.php')");		
	}
}


//

	$apply="<span id='applysquid'>" . applysquid_icon()."</span>";
	$plugins=Paragraphe('folder-lego.png','{activate_plugins}','{activate_plugins_text}',"javascript:Loadjs('squid.popups.php?script=plugins')");	
	$banUserAgent=Paragraphe('user-agent-ban-64.png','{ban_browsers}','{ban_browsers_text}',"javascript:Loadjs('squid.popups.php?script=user-agent-ban')");	
	$BandwithLimit=Paragraphe("bandwith-limit-64.png","{bandwith_limitation}","{bandwith_limitation_text}",
	"javascript:Loadjs('squid.bandwith.php')");
	$blackcomputer=Paragraphe("64-black-computer.png","{black_ip_group}",'{black_ip_group_text}',"javascript:Loadjs('dansguardian.bannediplist.php');");
	$whitecomputer=Paragraphe("64-white-computer.png","{white_ip_group}",'{white_ip_group_text}',"javascript:Loadjs('dansguardian.exceptioniplist.php');");
	
	if($SquidActHasReverse==1){$dansguardian=null;}
	
	
	if(!$squid->ACL_ARP_ENABLED){
		$arpinfos=Paragraphe("warning64.png","{no_acl_arp}",'{no_acl_arp_text}',"");
		
	}else{
		$arpinfos=Paragraphe("64-info.png","{yes_acl_arp}",'{yes_acl_arp_text}',"");
		
	}
	
	$authenticate_users=Paragraphe('members-priv-64.png','{authenticate_users}','{authenticate_users_text}',"javascript:Loadjs('squid.popups.php?script=ldap')");
	$useragent_db=Paragraphe('user-agent-64.png','{useragent_database}','{useragent_database_text}',"javascript:Loadjs('squid.user.agent.php')");
	$ftp_user=Paragraphe('ftp-user-64.png','{squid_ftp_user}','{squid_ftp_user_text}',"javascript:Loadjs('squid.ftp.user.php')");
	$templates_error=Paragraphe('squid-templates-64.png','{squid_templates_error}','{squid_templates_error_text}',"javascript:Loadjs('squid.templates.php')");	
	$messengers=Paragraphe('messengers-64.png','{instant_messengers}','{squid_instant_messengers_text}',"javascript:Loadjs('squid.messengers.php')");
	
	
	//http://www.faqs.org/docs/Linux-HOWTO/Bandwidth-Limiting-HOWTO.html#AEN60
	
	
	$tr[]=$plugins;
	$tr[]=$authenticate_users;
	$tr[]=$useragent_db;
	$tr[]=$messengers;
	$tr[]=$ftp_user;
	$tr[]=$denywebistes;
	$tr[]=$blackcomputer;
	$tr[]=$whitecomputer;
	$tr[]=$arpinfos;
	$tr[]=$banUserAgent;
	$tr[]=$addzapper;
	$tr[]=$ufdbguard_settings;
	$tr[]=$connection_time;
	$tr[]=$cicap;
	$tr[]=$clamav_unofficial;
	$tr[]=$template;
	$tr[]=$templates_error;
	$tr[]=$squidguardweb;
	$tr[]=$dansguardian;
	$tr[]=$license_kaspersky;
	$tr[]=$update_kaspersky;
	$tr[]=$settings_kaspersky;
	$tr[]=$denywebistes_2;	
	$html=CompileTr3($tr);

	
	


	

return $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}


function main_enableETDisable(){
	
	
	$sock=new sockets();
	$SQUIDEnable=trim($sock->GET_INFO("SQUIDEnable"));
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	
	$field=Paragraphe_switch_img("{enable_squid_service}","{enable_squid_service_explain}","SQUIDEnable",$SQUIDEnable);
	
	
	$html="
	<div id='EnableETDisableSquidDiv'>
	<p class=caption>{enable_squid_service_text}</p>
	$field
	<hr>
	<div style='text-align:right'><input type='button' OnClick=\"javascript:SaveEnableSquidGLobal()\" value='{edit}&nbsp;&raquo;'></div>
	</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}

function main_enableETDisable_save(){
	$sock=new sockets();
	$sock->SET_INFO('SQUIDEnable',$_GET["SaveEnableSquidGLobal"]);
	$sock->getFrameWork('cmd.php?squid-reconfigure=yes');
}






function Kav4proxy_events_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_KAV4PROXY}','squid.index.php');
	
	$html="
	function Kav4ProxyEventStart(){
		YahooWin5('700','$page?Kav4proxy-events-popup=yes','$title');
	}
	
	
	
	
	Kav4ProxyEventStart();
	";
	
	echo $html;
	
	
}

function dansguardian_events_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->DANSGUARDIAN_INSTALLED){
		$html="alert('" . $tpl->javascript_parse_text('{APP_DANSGUARDIAN}:{not_installed}')."');";
		echo $html;
		return false;
	}
	
	$title=$tpl->_ENGINE_parse_body('{APP_DANSGUARDIAN}','squid.index.php');
	
	$html="
	function DansguardianEventStart(){
		YahooWin5('700','$page?dansguardian-events-popup=yes','$title');
	}
	
	
	
	
	DansguardianEventStart();
	";
	
	echo $html;	
	
}

function dansguardian_stats_rebuild_sites(){
	$sock=new sockets();
	$sock->getfile('DansguardianRebuildStatsSites');
	
}

function dansguardian_stats_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_DANSGUARDIAN} {statistics}','squid.index.php');
	$command_background=$tpl->_ENGINE_parse_body('{apply_upgrade_help}','squid.index.php');
	$command_background=str_replace("\n","\\n",$command_background);
	$html="
	function DansguardianStatsStart(){
		YahooWin5('800','$page?dansguardian-stats-popup=yes','$title');
		
	}
	
	function ShowGraphDansGuardianDetails(type,time){
		LoadAjax('dansgraph','$page?dansguardian-stats-query='+type+'&time='+time);
	}
	
	function ShowGraphDansGuardianWebSite(www,time,type){
		LoadAjax('dansgraph','$page?dansguardian-stats-www='+www+'&time='+time+'&type='+type);
	}	
	
	
var x_RebuildSites= function (obj) {
	alert('$command_background');
	}	
	
	function RebuildSites(){
		var XHR = new XHRConnection();
		XHR.appendData('DansGuardianRebuildSites','yes');
		XHR.sendAndLoad('$page', 'GET',x_RebuildSites);
		
	}
	
	DansguardianStatsStart();";
	
	echo $html;		
	
}

function dansguardian_buildGraph_by_www(){
	$sql="SELECT count(sitename) as tcount,uri,TYPE,REASON,CLIENT FROM `dansguardian_events` 
	WHERE YEARWEEK( zDate ) = YEARWEEK(NOW()) AND 
	TYPE='{$_GET["type"]}' 
	AND sitename='{$_GET["dansguardian-stats-www"]}' GROUP BY uri,TYPE,REASON,CLIENT ORDER BY tcount DESC LIMIT 0,100";

	$sitename=dansguardian_buildsitename($_GET["dansguardian-stats-www"]);
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_events');
		
	$html="<table style='width:100%'>";
	
	while ($ligne = mysql_fetch_array($results)) { 
		if($ligne["TYPE"]<>null){
			
			$data[]=$ligne["tcount"];
			$labels[]=$sitename;			
			$jsa="ShowGraphDansGuardianWebSite('{$ligne["sitename"]}','week','{$_GET["dansguardian-stats-query"]}');";
			$uri=texttooltip(substr($ligne["uri"],0,90).'...',$ligne["uri"]);
			
			$html=$html . "<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'>
				<td><strong style='font-size:11px' valign='top'>{$ligne["tcount"]}</td>
				<td><strong style='font-size:11px' valign='top'>{$ligne["CLIENT"]}</td>
				<td><strong style='font-size:11px' valign='top'>$uri</td>
				<td><strong style='font-size:11px' valign='top'>{$ligne["REASON"]}</td>				
				</tr>
				
				";
			$js[]="$jsa";
			
		}
		
	}	
$top="	
<div style='font-size:13px;font-weight:bold;margin:5px'>
<a href='#' OnClick=\"javascript:DansguardianStatsStart()\">&laquo;&nbsp;{back}</a>&nbsp;
<a href='#' OnClick=\"javascript:ShowGraphDansGuardianDetails('{$_GET["type"]}','{$_GET["time"]}')\">&laquo;&nbsp;{$_GET["type"]}</a>&nbsp;
</div>
<div style='width:100%;height:300px;overflow:auto'>
$html
</table>
</div>
";	

$top=RoundedLightWhite($top);
	$tpl=new templates();
return $tpl->_ENGINE_parse_body($top);

}

function dansguardian_stats_popup(){
	
	
	$img=dansguardian_buildGraph_week();
	$html="<H1>{APP_DANSGUARDIAN} {statistics}</H1>
	<div id='dansgraph'>$img</div>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function dansguardian_buildsitename($md5){
	$sql="SELECT website FROM dansguardian_sites WHERE website_md5='$md5'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	return $ligne["website"];
	
}

function dansguardian_buildGraph_by_type(){
	$sql="SELECT COUNT( sitename ) AS tcount ,sitename,TYPE FROM `dansguardian_events` 
	WHERE YEARWEEK( zDate ) = YEARWEEK(NOW()) AND TYPE='{$_GET["dansguardian-stats-query"]}' 
	GROUP BY TYPE,sitename ORDER BY tcount DESC LIMIT 0 , 30";
	$md5=md5($sql);

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_events');
		
	$html="<table style='width:100%'>";
	
	while ($ligne = mysql_fetch_array($results)) { 
		if($ligne["TYPE"]<>null){
			
			$sitename=dansguardian_buildsitename($ligne["sitename"]);
			$data[]=$ligne["tcount"];
			$labels[]=$sitename;			
			$jsa="ShowGraphDansGuardianWebSite('{$ligne["sitename"]}','week','{$_GET["dansguardian-stats-query"]}');";
			$html=$html . "<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'>
				<td><strong style='font-size:11px'>{$ligne["tcount"]}</td>
				<td><strong style='font-size:11px'><a href='#' OnClick=\"$jsa\">$sitename</a></td>
				</tr>
				
				";
			$js[]="$jsa";
			
		}
		
	}
	
	$html=$html."
	<tr>
		<td colspan=2><a href='#' OnClick=\"javascript:RebuildSites();\">{rebuild_sites}</a></td>
	</tr>
	</table>";

   $tpl=new templates();




$p1 = new PiePlot3D($data);
$p1->SetSize(.4); 
$p1->SetAngle(75); 
$p1->SetCSIMTargets($js,$labels);
$p1->SetCenter(0.3,0.5);
$p1->ExplodeAll(10); 
$p1->SetLegends($labels);
//$p1->SetSliceColors(array('red','blue','green','navy','orange')); 

$graph = new PieGraph(470,350,'auto');
$graph->Add($p1);
$graph->title->Set("Week");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->legend->Pos(0,0,'right','top');
$graph->legend->SetFillColor('white'); 
$graph->legend->SetLineWeight(0);
//$graph->legend->SetLayout(LEGEND_HOR); //hori 
$graph->legend->SetColor('black'); 
$graph->legend->SetShadow("white",0); 
$graph->SetFrame(false); 
if(function_exists("imageantialias")){$graph->img->SetAntiAliasing();}
$mapName = 'MapName';
$imgMap = $graph->GetHTMLImageMap($mapName); 
$graph->Stroke("ressources/logs/$md5.png");

$html=  "
<a href='#' OnClick=\"javascript:DansguardianStatsStart()\">&laquo;&nbsp;{back}</a>
<table style='width:100%'>

<tr>
	<td valign='top'>
$imgMap
".RoundedLightWhite("
<img src='ressources/logs/$md5.png' alt='graph' ismap usemap='#$mapName' border='0'>")."
</td>
<td valign='top'>".RoundedLightWhite($html)."</td>
</tr>
</table>

";


return $tpl->_ENGINE_parse_body($html);
	
	
}

function dansguardian_build_stats(){
	
	$sock=new sockets();
	$sock->getfile('DansGuardianCompileStatistics');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<center>
	<div style='font-size:22px;color:red'>{success}</div>
	</center>
	");
}


function dansguardian_buildGraph_week(){
include_once(dirname(__FILE__).'/listener.graphs.php');
$sql="SELECT COUNT( sitename ) AS tcount ,TYPE FROM `dansguardian_events` WHERE YEARWEEK( zDate ) = YEARWEEK( NOW( ) ) GROUP BY TYPE ORDER BY tcount DESC LIMIT 0 , 30";
if(isset($_GET["dansguardian-stats-query"])){return dansguardian_buildGraph_by_type();}

$md5=md5($sql);


	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_events');
	$html="<table style='width:100%'>";
	while ($ligne = mysql_fetch_array($results)) { 
		if($ligne["TYPE"]<>null){
			$data[]=$ligne["tcount"];
			$labels[]=$ligne["TYPE"];
			$jsa="javascript:ShowGraphDansGuardianDetails('{$ligne["TYPE"]}','week');";
			$html=$html . "<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'>
				<td><strong style='font-size:11px'>{$ligne["tcount"]}</td>
				<td><strong style='font-size:11px'><a href='#' OnClick=\"$jsa\">{$ligne["TYPE"]}</a></td>
				</tr>
				
				";
			$js[]="$jsa";
			
		}
		
	}
	
	
	
   $html=$html."</table>";
   if (!is_array($data)){
   		die("<center>".ICON_DANSGUARDIAN_STATISTICS()."</center>");
   }
   
   $tpl=new templates();

   



$p1 = new PiePlot3D($data);
$p1->SetSize(.4); 
$p1->SetAngle(75); 
$p1->SetCSIMTargets($js,$labels);
$p1->SetCenter(0.3,0.5);
$p1->ExplodeAll(10); 
$p1->SetLegends($labels);
//$p1->SetSliceColors(array('red','blue','green','navy','orange')); 

$graph = new PieGraph(470,350,'auto');
$graph->Add($p1);
$graph->title->Set("Week");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->legend->Pos(0,0,'right','top');
$graph->legend->SetFillColor('white'); 
$graph->legend->SetLineWeight(0);
//$graph->legend->SetLayout(LEGEND_HOR); //hori 
$graph->legend->SetColor('black'); 
$graph->legend->SetShadow("white",0); 
$graph->SetFrame(false); 
if(function_exists("imageantialias")){$graph->img->SetAntiAliasing();}
$mapName = 'MapName';
$imgMap = $graph->GetHTMLImageMap($mapName); 
$graph->Stroke("ressources/logs/$md5.png");

$html=  "
<table style='width:100%'>

<tr>
	<td valign='top'>
$imgMap
".RoundedLightWhite("
<img src='ressources/logs/$md5.png' alt='graph' ismap usemap='#$mapName' border='0'>")."
</td>
<td valign='top'>".RoundedLightWhite($html)."</td>
</tr>
</table>

";


return $html;

	
}



function kav4proxy_license_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_KAV4PROXY}','squid.index.php');
	if($_GET["license-type"]=="milter"){$title=$tpl->_ENGINE_parse_body('{APP_KAVMILTER}','squid.index.php');}
	if($_GET["license-type"]=="kas"){$title=$tpl->_ENGINE_parse_body('{APP_KAS3}','squid.index.php');}
	
	$html="
	function Kav4ProxyLicenseStart(){
		YahooWin5('700','$page?Kav4proxy-license-popup=yes&license-type={$_GET["license-type"]}','$title');
	}
	
	
	var x_Kav4ProxyDeleteKey= function (obj) {
		Kav4ProxyLicenseStart();
	}
		
	function Kav4ProxyDeleteKey(){
		var XHR = new XHRConnection();
		document.getElementById('kav4licenseDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.appendData('Kav4proxy-license-delete','yes');
		XHR.appendData('license-type','{$_GET["license-type"]}');
		XHR.sendAndLoad('$page', 'GET',x_Kav4ProxyDeleteKey);
	}
	
	Kav4ProxyLicenseStart();
";
	echo $html;	
	}
	
function kav4proxy_license_delete(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?Kav4ProxyLicenseDelete&type='.$_GET["license-type"]));	
}


function kav4proxy_license_popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?Kav4ProxyLicense&type='.$_GET["license-type"]));
	
	
	
	$tp=explode("\n",$datas);
	$html="<H1>{license_info}</H1>
	<div style='width:100%;height:200px;overflow:auto' id='kav4licenseDiv'>
	<div style='text-align:right;padding-right:3px'>
	". button("{delete}","Kav4ProxyDeleteKey()")."
	</div>
	<table style='width:99%' class=table_form>";
	
while (list ($num, $val) = each ($tp)){
		if(trim($val)==null){continue;}
		$val=htmlspecialchars($val);
		if(strlen($val)>89){$val=texttooltip(substr($val,0,86).'...',$val,null,null,1);}
		$html=$html . "<tr><td style='font-size:12px'><code>$val</code></td></tr>";
			
}

$html=$html . "</table>
</div>
<center>
<iframe SRC='$page?kav4proxy-license-iframe=yes&license-type={$_GET["license-type"]}' WIDTH=99% FRAMEBORDER=0 MARGINWIDTH=0 MARGINHEIGHT=0 SCROLLING=no></iframe>
</center>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function kav4proxy_license_iframe($error=null){
	$page=CurrentPageName();
	$html="
	<span style='color:red;font-weight:bold;font-size:12px;padding-left:5px'>$error</span>
	<table style='width:90%' class=table_form align='center'>
	<tr>
		<td class=legend valign='middle'>{upload_new_license}:</td>
		<td>
			<form method=\"post\" enctype=\"multipart/form-data\" action=\"squid.newbee.php\">
					<p>
					<input type=\"file\" name=\"fichier\" size=\"30\">
					<input type=\"hidden\" name=\"license-type\" value='{$_GET["license-type"]}'>
					<input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:90px'>
					</p>
			</form>
		</td>
</tr>
</table>
	
	";
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo iframe($html,0);
	
}
function kav4proxy_license_upload(){
	$tmp_file = $_FILES['fichier']['tmp_name'];
	if($_SESSION[$tmp_file]["up"]){
		writelogs("Uploading license $tmp_file already sended",__FUNCTION__,__FILE__);
		kav4proxy_license_iframe($_SESSION[$tmp_file]["results"]);
		exit;
		}
	$_SESSION[$tmp_file]["up"]=true;
	
	writelogs("Uploading license $tmp_file",__FUNCTION__,__FILE__);
	$content_dir=dirname(__FILE__)."/ressources/logs";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){
		writelogs("not uploaded $tmp_file",__FUNCTION__,__FILE__);
		kav4proxy_license_iframe('{error_unable_to_upload_file}');exit();
	}
	
	 $type_file = $_FILES['fichier']['type'];
	 if( !strstr($type_file, 'key')){kav4proxy_license_iframe('{error_file_extension_not_match} :key');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){kav4proxy_license_upload("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    $socket=new sockets();
    writelogs("Kav4ProxyUploadLicense:$content_dir/$name_file",__FUNCTION__,__FILE__);
 	$res=base64_decode($socket->getFrameWork("cmd.php?Kav4ProxyUploadLicense=$content_dir/$name_file&type={$_POST["license-type"]}"));
	$tp=explode("\n",$res);
	$tp[]="ltp:{$_POST["license-type"]}";
	
	while (list ($num, $val) = each ($tp)){
		if(trim($val)==null){continue;}
		$val=htmlspecialchars($val);
		
		$html=$html . "<div><code>$val</code></div>";
		}
  	writelogs("$html",__FUNCTION__,__FILE__);
$_SESSION[$tmp_file]["results"]=$html;    
kav4proxy_license_upload($html);
}

function Kav4proxy_events_popup(){
	$page=CurrentPageName();
	$logs=Kav4proxy_events_daemon();
	$html="<H1>{APP_KAV4PROXY}</H1>
	<table style='width:100%' class=table_form>
	<tr>
	<td width=50% align=center>
		<input type='button' OnClick=\"javascript:LoadAjax('kav4proxyevents','$page?Kav4proxy-events-uris=yes');\" value='{daemon_events}&nbsp;&raquo;'>
	</td>
<td width=50% align=center>
		<input type='button' OnClick=\"javascript:LoadAjax('kav4proxyevents','$page?Kav4proxy-events-update=yes');\" value='{update_events}&nbsp;&raquo;'>
	</td>	
	</tr>
	</table>
	
	
	<div id='kav4proxyevents' style='width:100%;height:300px;overflow:auto;background-color:white;padding:3px;'>$logs
	</div>
	
	
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function dansguardian_events_popup(){
	$page=CurrentPageName();
	$logs=dansguardian_events_daemon();
	$html="<H1>{APP_DANSGUARDIAN}</H1>
	<table style='width:100%' class=table_form>
	<tr>
	<td width=50% align=center>
		<input type='button' OnClick=\"javascript:LoadAjax('DansguardianEventStart','$page?dansguardian-events-uris=yes');\" value='{daemon_events}&nbsp;&raquo;'>
	</td>
	</tr>
	</table>
	
	
	<div id='DansguardianEventStart' style='width:100%;height:300px;overflow:auto;background-color:white;padding:3px;'>$logs
	</div>
	
	
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function Kav4proxy_events_daemon(){
	$sock=new sockets();
	$datas=$sock->getfile('Kav4ProxyDaemonEvents');
	$tp=explode("\n",$datas);
	$u=array_reverse($tp);
	
	while (list ($num, $val) = each ($u)){
		if(trim($val)==null){continue;}
		$val=htmlspecialchars($val);
		if(strlen($val)>89){$val=texttooltip(substr($val,0,86).'...',$val,null,null,1);}
		$html=$html . "<div><code>$val</code></div>";
		}
	
	return $html;
	
}

FUNCTION dansguardian_events_daemon(){
$sock=new sockets();
	$datas=$sock->getfile('DansguardianDaemonEvents');
	$tp=explode("\n",$datas);
	$u=array_reverse($tp);
	
	while (list ($num, $val) = each ($u)){
		if(trim($val)==null){continue;}
		$val=htmlspecialchars($val);
		if(strlen($val)>89){$val=texttooltip(substr($val,0,86).'...',$val,null,null,1);}
		$html=$html . "<div><code>$val</code></div>";
		}
	
	return $html;	
	
}




function Kav4proxy_events_update(){
	
	$sock=new sockets();
	$datas=$sock->getfile('Kav4ProxyUpdateEvents');
	$tp=explode("\n",$datas);
	$u=array_reverse($tp);
	
	while (list ($num, $val) = each ($u)){
		if(trim($val)==null){continue;}
		$val=str_replace("'","\"",$val);
		$val=htmlspecialchars($val);
		if(strlen($val)>89){$val=texttooltip(substr($val,0,86).'...',$val,null,null,1);}
		$html=$html . "<div><code>$val</code></div>";
		
		
		
	}
	
	return $html;
	
}


function changecache_js(){
	$page=CurrentPageName();
	$main_path=urlencode($_GET["changecache-js"]);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{change_main_cache_path}','squid.index.php');
	$html="
	var Original_path='$main_path';
	
	
	function changeCacheIndex(){
		YahooWin(500,'$page?changecache-popup=$main_path','$title');
		}
	
	
	
	var x_SaveNewChache= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		RefreshTab('squid_main_config');
		YahooWinHide();
	}	
	
	function SaveNewChache(){
		var XHR = new XHRConnection();
		XHR.appendData('SaveNewChache',document.getElementById('change_cache_path_to').value);
		XHR.appendData('OldCache','$main_path');
		document.getElementById('changecachediv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveNewChache);		
		}
		
		
changeCacheIndex();";
	echo $html;
}


function transparent_js(){
$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{transparent_mode}','squid.index.php');
	$html="
	

	function TransparentIndex(){
		YahooWin(500,'$page?squid-transparent-popup=yes','$title');
	
	}
	
	TransparentIndex();
	
var x_SaveTransparentProxy= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	TransparentIndex();
}	
	
	function SaveTransparentProxy(){
		var XHR = new XHRConnection();
		XHR.appendData('squid_transparent',document.getElementById('squid_transparent').value);
		document.getElementById('squid_transparentdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveTransparentProxy);		
		}";
	echo $html;
}

function transparent_save(){
	$artica=new artica_general();
	$squid=new squidbee();
	$squid->hasProxyTransparent=$_GET["squid_transparent"];
	if($_GET["squid_transparent"]==1){
		$artica->EnableArticaAsGateway=1;$artica->Save();
		$squid->LDAP_AUTH=0;
		}
	$squid->SaveToLdap();
	$squid->SaveToServer();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squid-reconfigure=yes");
	
}

function transparent_popup(){
	
	$squid=new squidbee();
	$field=Paragraphe_switch_img('{transparent_mode}','{transparent_mode_text}','squid_transparent',$squid->hasProxyTransparent,null,350);
	$html="
	
	<div id='squid_transparentdiv'>
		<div class=explain>{transparent_mode_explain}</div>
		$field
		<p>&nbsp;</p>
		<div style='text-align:right;width:100%'><hr>". button("{apply}","SaveTransparentProxy();")."</div>
	
	</div>
	
	
	";
	
$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
}

function net_control_center_js(){
	$page=CurrentPageName();
	$main_path=$_GET["changecache-js"];
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{your_network_loupe}','squid.index.php');
	$html="
	function ChargeNetControlCenter(){
		YahooWinS(850,'$page?squid-net-loupe-popup=yes','$title');
	
	}
	ChargeNetControlCenter();
	";	
	
	echo $html;
	
	
}


function net_control_center_reverse_popup(){
	$sock=new sockets();
	$listen_port=$sock->GET_INFO("SquidActHasReverseListenPort");
	if($listen_port==null){$listen_port=80;}
	$www_names=net_control_center_websites_list();
	$www_namesips=net_control_center_websites_list(false);
	$ip=new networking();
	if(is_array($ip->array_TCP)){
		while (list ($num, $val) = each ($ip->array_TCP)){
			if($val==null){continue;}
			$internals_proxy=$internals_proxy."<li>$val:$listen_port</li>";
		}
	}	
	
	
$squid_accl_websites=Paragraphe('website-64.png','{squid_accel_websites}','{squid_accel_websites_text}',"javascript:Loadjs('squid.reverse.websites.php')");
$squid_reverse_proxy=Paragraphe('squid-reverse-64.png','{squid_reverse_proxy}','{squid_reverse_proxy_text}',"javascript:Loadjs('squid.reverse.proxy.php')");	
	
$panel="<div style='position:absolute;top:80px;left:600px;width:230px;'>
 <table style='width:100%'>
 <tr>
 	<td valign='top'>$squid_accl_websites</td>
 </tr>
	<td valign='top'>$squid_reverse_proxy</td>
</tr>
 </table>
 </div>";	
	
	
	$www_names="<div style='position:absolute;top:150px;left:400px;font-size:14px;font-weight:bold'>$www_names</div>";
	
	$www_namesips="<div style='position:absolute;top:420px;left:200px;font-size:14px;font-weight:bold'>$www_namesips</div>";
	
	$listen_port="<div style='position:absolute;top:300px;left:290px;font-size:14px;font-weight:bold'>
	<H3>NAT TO:</H3>
	<ul>$internals_proxy</ul></div>";
	$html="
	<div style='width:100%;background-image:url(img/squid-net-reverse.jpg);height:481px;background-repeat:no-repeat;width:550px;border:1px dotted #CCCCCC'>
	$www_names$listen_port$www_namesips$panel
	</div>";
	
	$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
}

function net_control_center_websites_list($asname=true){
	
			$sql="SELECT * FROM squid_accel ORDER BY ID DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
				$website_name=$ligne["website_name"];
				$ip=$ligne["website_ip"];
				$port=$ligne["website_port"];
				if($port==null){$port=80;}
				if($website_name==null){continue;}
				if($ip==null){continue;}
				if($asname){
					$html=$html."<li>$website_name</li>";
					}else{
						$html=$html."<li>$ip:$port (<span style='font-size:11px'>$website_name</span>)</li>";				
					}
			}	
	
	return "<ul>$html</ul>";
}


function net_control_center_popup(){
	
	$sock=new sockets();
	$SquidActHasReverse=$sock->GET_INFO("SquidActHasReverse");
	if($SquidActHasReverse==1){net_control_center_reverse_popup();exit;}
	
	$ip=new networking();
	$squid=new squidbee();
	$count_dns=0;
	if(is_array($squid->dns_array)){
		while (list ($num, $val) = each ($squid->dns_array)){
			if($val==null){continue;}
			$count_dns=$count_dns+1;
			$dns=$dns."<tr><td width=1%><img src='img/fw_bold.gif'></td><td width=99%><strong>$val</strong></td></tr>";
		}
		
	}
	
	if($count_dns==0){
		
		if(is_array($ip->arrayNameServers)){
			while (list ($num, $val) = each ($ip->arrayNameServers)){
				if($val==null){continue;}
				$count_dns=$count_dns+1;
				$dns=$dns."<tr><td width=1%><img src='img/fw_bold.gif'></td><td width=99%><strong>$val</strong></td></tr>";
			}
			
		}
	}
	
	if($count_dns>0){
		$dns_div="<div style='position:absolute;top:300px;left:420px'>
		<strong>{nic_static_dns}</strong>
		<table style='width:50px'>
		$dns
		</table>
		</div>";
	}
	
	if(count($squid->network_array)==0){
		$network_t="<tr><td width=1%><img src='img/fw_bold.gif'></td><td width=99%><strong>{error_miss_datas}</strong></td></tr>";
	}else{
		while (list ($num, $val) = each ($squid->network_array)){
			$network_t=$network_t."<tr><td width=1%><img src='img/fw_bold.gif'></td><td width=99%><strong>$val</strong></td></tr>";
		}
	}
	
	
	$proxy_port=
	"<div style='position:absolute;top:160px;left:90px'>
	<a href=\"javascript:Loadjs('squid.popups.php?script=listen_port')\"><strong>{listen_port}:$squid->listen_port</strong></a>
	</div>";
	
	if(is_array($ip->array_TCP)){
		while (list ($num, $val) = each ($ip->array_TCP)){
			$array=$ip->GetNicInfos($num);
			if($array["GATEWAY"]==null){continue;}
			if($array["GATEWAY"]=="0.0.0.0"){continue;}
			$gw=$gw."<tr><td width=1%><img src='img/fw_bold.gif'></td><td width=99%><strong>{$array["GATEWAY"]}</strong></td></tr>";
		}
	}

$gayteway="
	<div style='position:absolute;top:170px;left:320px'><img src='img/gateway.png'></div>
	<div style='position:absolute;top:110px;left:280px'>
		<strong>{gateway}</a></strong>
		<table style='width:150px'>
		$gw
		</table>
		</div>";	
	

if($squid->hasProxyTransparent){
$gw=null;	
if(is_array($ip->array_TCP)){
	reset($ip->array_TCP);
		while (list ($num, $val) = each ($ip->array_TCP)){
			if($val==null){continue;}
			$gw=$gw."<tr><td width=1%><img src='img/fw_bold.gif'></td><td width=99%><strong>$val</strong></td></tr>";
		}
	}

	$transparent="<div style='position:absolute;top:120px;left:90px'><strong style='color:red'>{transparent_mode}</strong></div>";
	
	$gayteway="
	
	<div style='position:absolute;top:420px;left:280px'>
		<strong>{gateway}&nbsp;<span class=caption>({should_connected_to})</a></span></strong>
		<table style='width:150px'>
		$gw
		</table>
		</div>";
}



	
	
$network_d="
	<div style='position:absolute;top:420px;left:120px'>
		<strong><a href=\"javascript:Loadjs('squid.popups.php?script=network')\">{your_network}</a></strong>
		<table style='width:150px'>
		$network_t
		</table>
		</div>";	


 $panel="<div style='position:absolute;top:80px;left:600px;width:230px;'>
 <table style='width:100%'>
 <tr>
 	<td valign='top'>" . Paragraphe('folder-realyrules-64.png','{your_network}','{your_network_text}',"javascript:Loadjs('squid.popups.php?script=network')")."</td>
 </tr>
	<td valign='top'>" . Paragraphe('network-connection2.png','{listen_port}','{listen_port_text}',"javascript:Loadjs('squid.popups.php?script=listen_port')")."</td>

	<tr>
<td valign='top'>" . Paragraphe('relayhost.png','{transparent_mode}','{transparent_mode_text}',"javascript:Loadjs('$page?squid-transparent-js=yes')")."</td>
</tr>
 </table>
 </div>";
	
	$html="<H1>{your_network_loupe}</H1>
	<div style='width:100%;background-image:url(img/squid-net-550.png);height:481px;background-repeat:no-repeat'>
	$transparent$gayteway$dns_div$network_d$proxy_port$panel
	</div>
	
	
	";
	

	
	$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
}


function changecache_save(){
	$newcache=trim($_GET["SaveNewChache"]);
	$OldCache=$_GET["OldCache"];
	if(trim($newcache)==null){return null;}
	if(trim($OldCache)==null){return null;}
	if(preg_match("#^\/home#",$newcache,$re)){
		echo "/home -> false\n";
		exit;
	}
	$newcache=str_replace(" ","_",$newcache);
	$newcache=str_replace("'","_",$newcache);
	$newcache=str_replace("$","_",$newcache);
	$newcache=str_replace('\\',"_",$newcache);
	$newcache=str_replace("%","_",$newcache);
	$newcache=str_replace("!","",$newcache);
	$newcache=str_replace("*","",$newcache);
	$newcache=str_replace("¡","",$newcache);
	
	
	
	$squid=new squidbee();
	$squid->CACHE_PATH=$newcache;
	$squid->SaveToLdap();
	$squid->SaveToServer();

	}
	
function changecache_popup(){
	$main_path=$_GET["changecache-popup"];
	$tpl=new templates();
	$page=CurrentPageName();
	$cache=urlencode($main_path);
	$time=time();
	$html="
	
	<span style='font-size:16px'>{change_main_cache_path}</span>
	<div id='$time'></div>

	<script>
		LoadAjax('$time','$page?changecache-popup-content=$cache');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}	
	
function changecache_popup_content(){
	$main_path=$_GET["changecache-popup-content"];
	
	$html="
	
	<div id='changecachediv'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<img src='img/idrive-96.png' align='left' style='margin:5px'>
	</td>
	<td valign='top'>
	<div class=explain>{change_main_cache_path_explain}</div>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{from}:</td>
		<td><div style='font-size:12px'>$main_path</div></td>
	</tr>
	
	<td class=legend>{to}:</td>
	<td>".Field_text('change_cache_path_to',null,'width:220px').
	"&nbsp;<input type='button' OnClick=\"Loadjs('SambaBrowse.php?no-shares=yes&field=change_cache_path_to');\" value='{browse}...'></td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr><input type='button' OnClick=\"SaveNewChache();\" value='{apply}...'></td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function cache_list(){


	$squid=new squidbee();

	$html="
	<div id='cachesfolders'>
	<H3>{additional_caches}</h3>
	<table style='width:100%'>";
	if(is_array($squid->cache_list)){
		while (list ($num, $val) = each ($squid->cache_list)){
			$val["cache_size"]=FormatBytes($val["cache_size"]*1024);
			$html=$html."
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td " . CellRollOver("AddCache('$num')")."><strong style='font-size:13px'>$num</strong></td>
			<td style='font-size:13px'>{$val["cache_size"]}</td>
			<td width=1%>".imgtootltip("ed_delete.gif","{delete}","DeleteCache('$num')")."</td>
			</tr>
			";
		
		}
	}
	
	
	
	$html=$html."</table></div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}

function main_save_array(){
	$squid=new squidbee();
	
	if(isset($_GET["cache_size"])){
		$squid->CACHE_SIZE=$_GET["cache_size"];
		unset($_GET["cache_size"]);
	}
	if(isset($_GET["cache_type"])){
		$squid->CACHE_TYPE=$_GET["cache_type"];
		unset($_GET["cache_type"]);
	}
	
	
	while (list ($num, $val) = each ($_GET) ){
		$squid->global_conf_array[$num]=$val;
	}
	$squid->SaveToLdap();
}

function applysquid_icon(){
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body(Paragraphe('system-64.png','{apply_squid}','{apply_squid_text}',"javascript:applysquid()"),'squid.index.php');
}

function apply_kavupdate(){
	$tpl=new templates();
	$page=CurrentPageName();
	$text=$tpl->_ENGINE_parse_body('{UPDATE_ANTIVIRUS_DATABASE_PERFORMED}');
	$prefix="kavupdate";
	$html="
	{$prefix}tant=0;
	function StartUpdateKavProxySchedule(){
		if(!YahooWin2Open()){return;}
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=10-{$prefix}tant;
			if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"StartUpdateKavProxySchedule()\",1500);
		      } else {
				{$prefix}tant = 0;
				StartUpdateKavProxyChargeLogs();
				StartUpdateKavProxySchedule(); 
		 }
	}	
	
	var x_UpdateKav4Proxy= function (obj) {
	      StartUpdateKavProxySchedule();
	      
	}
	var x_StartUpdateKavProxyChargeLogs= function (obj) {
	      var results=obj.responseText;
	      document.getElementById('update-kav-update-id').innerHTML=results;
	}	

	function StartUpdateKavProxyChargeLogs(){
			var XHR = new XHRConnection();
			XHR.appendData('update-kav-logs','yes');
			XHR.appendData('type','{$_GET["type"]}');
			XHR.sendAndLoad('$page', 'GET',x_StartUpdateKavProxyChargeLogs);	
	}

		function UpdateKav4Proxy(){
			YahooWin2(650,'$page?update-kav-popup=yes&type={$_GET["type"]}','$text');
		
					
		}	
			UpdateKav4Proxy();
	";
	
	echo $html;	
}

function apply_kavupdate_logs(){
	$f=explode("\n",@file_get_contents("/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate"));
	$tpl=new templates();
	if(!is_array($f)){echo "<center><img src=\"img/wait_verybig.gif\"></center>";exit;}
	while (list ($num, $ligne) = each ($f) ){	
		if($ligne==null){continue;}
		echo "<div><code style='font-size:11px'>".$tpl->_ENGINE_parse_body($ligne)."</code></div>\n";
	}
	
}

function apply_kavupdate_perform(){
	$sock=new sockets();
	
	
	$sock->getFrameWork("cmd.php?UpdateKav4Proxy=yes&type={$_GET["type"]}");
	
}

function apply_kavupdate_popup(){
	$page=CurrentPageName();
	$html="
	<div id='update-kav-update-id' style='width:100%;height:350px;overflow:auto'><center><img src=\"img/wait_verybig.gif\"></center></div>
	<script>
		var XHR = new XHRConnection();
		XHR.appendData('update-kav-now','yes');
		XHR.appendData('type','{$_GET["type"]}');
		XHR.sendAndLoad('$page', 'GET',x_UpdateKav4Proxy);	
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
}

function applysquid(){
	$squid=new squidbee();
	$squid->SaveToLdap();
	$squid->SaveToServer();
	echo applysquid_icon();
}


function main_events_tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["access"]='{access_events}';
	$array["icons-events"]='{options}';
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="access"){
			$html[]= "<li><a href=\"squid.access.logs.php\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		
		
		$html[]= "<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_squid_events_tabs style='width:100%;height:600px;overflow:auto'>
		<ul>". $tpl->_ENGINE_parse_body(implode("\n",$html))."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squid_events_tabs').tabs();
			});
		</script>";	
	
}

function main_events(){
	$page=CurrentPageName();
	$sock=new sockets();
	
	
	
	
	$usersmenus=new usersMenus();
	if($usersmenus->awstats_installed==true){
			$awstats_squid=Paragraphe('folder-awstats-64.png','{awstats}','{awstats_text}','cgi-bin/awstats.pl?config=squid');
		}
		
	if($usersmenus->KAV4PROXY_INSTALLED){
		if($sock->GET_INFO("kavicapserverEnabled")==1){
			$kav4proxy=Paragraphe('folder-logs-64-k.png','{APP_KAV4PROXY}','{display_product_events}',"javascript:Loadjs('$page?Kav4proxy-events-js=yes')");
		}
	}
	
	if($usersmenus->DANSGUARDIAN_INSTALLED){
		if($sock->GET_INFO("DansGuardianEnabled")==1){
			$dansguardian=Paragraphe('folder-dansguardian-64.png','{APP_DANSGUARDIAN}','{display_product_events}',"javascript:Loadjs('$page?dansguardian-events-js=yes')","");
			$dansguardian_stats=Paragraphe('64-dansguardian-stats.png','{APP_DANSGUARDIAN} {statistics}','{display_product_events}',"javascript:Loadjs('$page?dansguardian-stats-js=yes')","");
		}
	}
	
	if($usersmenus->SARG_INSTALLED){
		$sarg=Paragraphe('sarg-logo.png','{APP_SARG}','{display_product_events}',"javascript:Loadjs('sarg.php')","{display_product_events}");
	} 	
	
	$artica_maintenance=Paragraphe('table-synonyms-settings-64.png','{ARTICA_DATABASE_MAINTENANCE}','{ARTICA_DATABASE_SQUID_MAINTENANCE}',"javascript:Loadjs('squid.mysql.php')");
	
	$cachemgr=Paragraphe("perf-stats-64-grey.png","{cachemgr}","{cachemgr_text}");
	if($usersmenus->APACHE_INSTALLED ){
		if(strlen($usersmenus->SQUID_CACHMGR)>5){
			$cachemgr=Paragraphe("perf-stats-64.png","{cachemgr}","{cachemgr_text}","javascript:Loadjs('squid.cachemgr.php')");
		}
	}	
	$tr[]=$cachemgr;	
	$tr[]=$awstats_squid;
	$tr[]=$kav4proxy;
	$tr[]=$dansguardian;
	$tr[]=$dansguardian_stats;
	$tr[]=$sarg;
	$tr[]=$artica_maintenance;
	


	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	

$html=implode("\n",$tables);		


	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html,'squid.index.php');		
		
}











function time_global(){
	
	$ldap=new clladp();
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{select}";
	$ous_field=Field_array_Hash($ous,'ou',null,"ConnectionTimeSelectOU()");
	
	$squid=new squidbee();

	
	$html="
	<H1>{connection_time}</h1>
	<table style='width:100%' class=table_form'>
	<tr>
		<td class=legend nowrap>{ou}</td>
		<td>$ous_field</td>
	</tr>
	<tr>
		<td class=legend nowrap>group</td>
		<td><div id='group_field'></div></td>
	</table>
	<div id='ConnectionTimeRule' style='width:100%'></div>
	";
	
	if($squid->LDAP_AUTH<>1){
		$html="<H1>{error}&nbsp;{connection_time}</h1>
		<p class=caption style='font-size:14px'>{error_no_auth_squid}</p>
		";
		
	}
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,'squid.index.php');
	}
	
function time_rule(){
	if(trim($_GET["gpid"])==null){return null;}
	$ou=$_GET["ou"];
	$group=new groups($_GET["gpid"]);
	$group_name=$group->groupName;
	$days=time_hash_day();
	$page=CurrentPageName();	
		$form="
		<H3>{accept_time}:</H3>
		<form name='ffmruletime'>
		<input type='hidden' value='{$_GET["gpid"]}' id='gpid' name='gpid'>
		<input type='hidden' value='$group->ou' id='ou' name='ou'>
		<table style='width:100%' class=table_form>
		<tr>
		<td class=legend style='font-size:13px' nowrap>{day}:</td>
		<td>" . Field_array_Hash($days,"time_day",null)."</td>
		<td class=legend style='font-size:13px' nowrap>{from}:</td>
		<td>" . Field_array_Hash(time_hash_hour(),"time_hour","08").":".Field_array_Hash(time_hash_min(),"time_min","00")."</td>
		<td class=legend style='font-size:13px' nowrap>{to}:</td>
		<td>" . Field_array_Hash(time_hash_hour(),"end_time_hour","17").":".Field_array_Hash(time_hash_min(),"end_time_min","00")."</td>
		<td align='right'><input type='button' 
		OnClick=\"javascript:ParseForm('ffmruletime','$page',true,false,false,'rule_list','$page?time-rule-list=yes&gpid={$_GET["gpid"]}&ou=$group->ou');\" value='{add}&nbsp;&raquo;'></td>
		</tr>
		</table>	
		</form>
		";
	
	
	$html="
	<H3>$group_name</h3>
	<p class=caption>{connection_time_explain}</p>
	$form
	<div id='rule_list'>" . time_rule_list($_GET["gpid"],$group->ou)."</div>";

$tpl=new templates();	
return  $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
}

function time_rule_delete(){
	$day=$_GET["ConnectionTimeDelete"];
	$key="time:{$_GET["gpid"]}:{$_GET["ou"]}";
	$squid=new squidbee();
	unset($squid->acl_times[$key][$day]);
	$squid->SaveToLdap();
}

function time_rule_list($gpid,$ou){
	$key="time:$gpid:$ou";
	$tpl=new templates();
	$squid=new squidbee();
	if(!is_array($squid->acl_times[$key])){
		return $tpl->_ENGINE_parse_body("<br><strong style='font-size:13px'>{allow_all_days}</strong>");
	}
	
	$days=time_hash_day();
	$html="<table style='width:80%' class=table_form>";
	while (list ($a, $b) = each ($days)){
		if($a==null){continue;}
		if($squid->acl_times["time:$gpid:$ou"][$a]<>null){
			$html=$html . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td class=legend style='font-size:13px' nowrap><strong>$b</strong></td>
				<td width=99%>{allow}:&nbsp;{$squid->acl_times["time:$gpid:$ou"][$a]}</td>
				<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","ConnectionTimeDelete('$gpid','$ou','$a')")."</td>
			</tr>
			";
		}else{
			$html=$html . "
			<tr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td width=1% class=legend style='font-size:13px' nowrap><strong>$b</strong></td>
				<td colspan=2 width=99%><strong>{deny}</strong></td>
			</tr>";
		}
		
	}
	$html=$html . "</table>";
	
	return $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}

function time_save(){
	$tpl=new templates();
	if($_GET["time_day"]==null){echo $tpl->_ENGINE_parse_body("{day}: Wrong!\n");exit;}
	$squid=new squidbee();
	$squid->acl_times["time:{$_GET["gpid"]}:{$_GET["ou"]}"][$_GET["time_day"]]="{$_GET["time_hour"]}:{$_GET["time_min"]}-{$_GET["end_time_hour"]}:{$_GET["end_time_min"]}";
	$squid->SaveToLdap();
	echo $tpl->_ENGINE_parse_body("{$_GET["time_hour"]}:{$_GET["time_min"]}-{$_GET["end_time_hour"]}:{$_GET["end_time_min"]}:{success}\n");
	
}

function time_groups(){
	$ldap=new clladp();
	$gprs=$ldap->hash_groups($_GET["connection-time-showgroup"],1);
	$gprs[null]="{select}";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body(Field_array_Hash($gprs,'gpid',null,"ConnectionTimeSelectGroup()"));
	
	
}

function time_hash_day(){
	$array=array(
		null=>"{select}",
		"M"=>"{monday}",
		"T"=>"{tuesday}",
		"W"=>"{wednesday}",
		"H"=>"{thursday}",
		"F"=>"{friday}",
		"A"=>"{saturday}",
		"S"=>"{sunday}"
	);
	return $array;
	
}

function time_hash_hour(){
	for($i=0;$i<24;$i++){
		if(strlen($i)<2){$h["0$i"]="0$i";}else{$h[$i]=$i;}
		
	}
	return $h;
}
function time_hash_min(){
for($i=0;$i<60;$i++){
		if(strlen($i)<2){$h["0$i"]="0$i";}else{$h[$i]=$i;}
		
	}
	return $h;	
	
}


function filterextension_popup(){
$amavis=new amavis();
$html="
<input type='hidden' id='AmavisAddExtFilter_text' value='{AmavisAddExtFilter_text}'>
<H1>{filter_extension}</H1>
	<p class=caption>{filter_extension_text}</p>
	<div style='width:100%;text-align:right'>
	<input type='button' OnClick=\"javascript:AmavisAddExtFilter();\" value='{add_ban_ext}&nbsp;&raquo;'>
	</div>
	";
	
	$tablestyle="style='width:100px;margin-right:5px;border-right:1px solid #CCCCCC'";
	
	
$table="
<H3>{extension_list}</h3><hr>
<div style='width:100%;height:400px;overflow:auto'>";
if(is_array($amavis->extensions)){

while (list ($num, $ligne) = each ($amavis->extensions) ){
	
	if(file_exists('img/file_ico/'.$ligne.'.gif')){$img="img/file_ico/$ligne.gif";}else{$img="img/file_ico/unknown.gif";}
	$table=$table."
	<div style='float:left;margin:2px'>
	<table style='width:80px;border:1px solid #CCCCCC'>
	<tr " . CellRollOver().">
	<td width=1%' align='center'><img src='$img'></td>
	<td width=1%'>" . imgtootltip('ed_delete.gif',"{delete}","AmavisDelExtFilter('$ligne');")."</td>
	
	</tr>
	
	<tr>
	<td align='center' colspan=2><strong style='font-size:11px'>$ligne</td>
	
	
	</tr>
	</table>
	</div>";
	
}
}
$table=$table."</div>";
$html=$html . $table;
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function sarg_scan(){
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?Sarg-Scan=yes")));
	$table="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$js="SargBrowseStart('".trim($ligne)."')";
		$table=$table.
		"<tr ". CellRollOver($js).">
			<td valign='top'><code style='font-size:13px'>$ligne</code></td>
		</tr>";
		}
	$table=$table."</table>";
	
	
	$html="<h1>{APP_SARG}</H1>
	".RoundedLightWhite("
	<div style='width:100%;height:300px;overflow:auto'>$table</div>")
	;
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function sarg_date(){
	$file=$_GET["sarg-date"];
	if(isset($_GET["date"])){
		$file="{$_GET["date"]}/$file";
	}
	if(!preg_match('#\.(html|png)$#',$file)){$ext="/index.html";}
	
	if(preg_match('#\.png$#',$file)){
		
		$html=RoundedLightWhite("<div style='width:100%;height:500px;overflow:auto'>
		<img src='images.listener.php?uri=sarg/$file'>
		</div>");
		echo $html;
		exit;
		
	}
	
	$sock=new sockets();
	$datas=sarg_clean($sock->getfile("SargFile:$file$ext"));
	$datas=RoundedLightWhite($datas);
	$datas="<div style='width:100%;height:350px;overflow:auto'>$datas</div>";
	
	$html="<H1>". basename($file)."</H1>$datas
	
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function sarg_clean($datas){
	if(preg_match('#<body class="body">(.+?)</body#is',$datas,$re)){
		$datas=$re[1];
	}
	
	$datas=str_replace("images/datetime.png","img/18-schedulever.png",$datas);
	$datas=str_replace("../images/graph.png","img/18-chart.png",$datas);
	$datas=str_replace("../images/sarg.png","img/sarg-logo.png",$datas);	
	
	$root=$_GET["sarg-date"];
	if(preg_match('#(.+?)\/#',$root,$re)){
		$root=$re[1];
	}
	
	$style=sarg_style();
	
	if(preg_match_all('#<td class="link" colspan=11><a href="(.+?)">#is',$datas,$re)){
			while (list ($num, $ligne) = each ($re[0]) ){
			$datas=str_replace($ligne,"<td class=\"link\" colspan=11><a href='#' OnClick=\"javascript:SargBrowse('{$re[1][$num]}')\">",$datas);
		}
	}
	
	preg_match_all("#href='([a-zA-Z0-9\-\_\/\.]+)'#is",$datas,$re);
	if(is_array($re)){
		while (list ($num, $ligne) = each ($re[0]) ){
			$datas=str_replace($ligne,"href='#' OnClick=\"javascript:SargBrowse('{$re[1][$num]}')\"",$datas);
		}
	}
	preg_match_all('#<a href=(http|https|ftp|ftps)(.+?)title="(.+?)"#is',$datas,$re);
	if(is_array($re)){	
while (list ($num, $ligne) = each ($re[0]) ){
			$datas=str_replace($ligne,"<a href='#' OnClick=\"javascript:s_PopUpFull('{$re[1][$num]}{$re[2][$num]}',600,500,'{$re[3][$num]}');\" title=\"{$re[3][$num]}\"",$datas);
		}
	}
	
	preg_match_all("#<td class=\"data\"><a href=\"(.+?)\"#is",$datas,$re);
	if(is_array($re)){
		while (list ($num, $ligne) = each ($re[0]) ){
			$datas=str_replace($ligne,"<td class=\"data\"><a href=\"#\" OnClick=\"javascript:SargBrowse('$root/{$re[1][$num]}')\"",$datas);
		}
	}

	if(preg_match_all("#<td class=\"data2\"><a href=\"(http|https|ftp|ftps)(.+?)\"#is",$datas,$re)){
	
		while (list ($num, $ligne) = each ($re[0]) ){
			$datas=str_replace($ligne,"<td class=\"data2\"><a href='#' OnClick=\"javascript:s_PopUpFull('{$re[1][$num]}{$re[2][$num]}',600,500,'{$re[3][$num]}');\" title=\"{$re[3][$num]}\"",$datas);
		}
	}	
	
	
		
	
	return sarg_style().$datas;
	
	
	
}

function sarg_style(){
	
	return "<style>
.logo {font-family:Verdana,Tahoma,Arial;font-size:11px;color:#006699;}
.body {font-family:Tahoma,Verdana,Arial;color:#000000;background-color:white;}
.info {font-family:Tahoma,Verdana,Arial;font-size:10px;}
.info a:link,a:visited {font-family:Tahoma,Verdana,Arial;color:#0000FF;font-size:10px;text-decoration:none;}
.title {font-family:Tahoma,Verdana,Arial;font-size:11px;color:green;background-color:white;}
.title2 {font-family:Tahoma,Verdana,Arial;font-size:11px;color:green;background-color:white;text-align:left;}
.title3 {font-family:Tahoma,Verdana,Arial;font-size:11px;color:green;background-color:white;text-align:right;}
.header {font-family:Tahoma,Verdana,Arial;font-size:9px;color:darkblue;background-color:blanchedalmond;text-align:left;border-right:1px solid #666666;border-bottom:1px solid #666666;}
.header2 {font-family:Tahoma,Verdana,Arial;font-size:9px;color:darkblue;background-color:blanchedalmond;text-align:right;border-right:1px solid #666666;border-bottom:1px solid #666666;}
.header3 {font-family:Tahoma,Verdana,Arial;font-size:9px;color:darkblue;background-color:blanchedalmond;text-align:center;border-right:1px solid #666666;border-bottom:1px solid #666666;}
.text {font-family:Tahoma,Verdana,Arial;color:#000000;font-size:9px;}
.data {font-family:Tahoma,Verdana,Arial;color:#000000;font-size:9px;background-color:lavender;text-align:right;border-right:1px solid #6A5ACD;border-bottom:1px solid #6A5ACD;}
.data a:link,a:visited {font-family:Tahoma,Verdana,Arial;color:#0000FF;font-size:9px;background-color:lavender;text-align:right;text-decoration:none;}
.data2 {font-family:Tahoma,Verdana,Arial;color:#000000;font-size:9px;background-color:lavender;border-right:1px solid #6A5ACD;border-bottom:1px solid #6A5ACD;}
.data2 a:link,a:visited {font-family:Tahoma,Verdana,Arial;color:#0000FF;font-size:9px;background-color:lavender;text-decoration:none;}
.data3 {font-family:Tahoma,Verdana,Arial;color:#000000;font-size:9px;text-align:center;background-color:lavender;border-right:1px solid #6A5ACD;border-bottom:1px solid #6A5ACD;}
.data3 a:link,a:visited {font-family:Tahoma,Verdana,Arial;color:#0000FF;font-size:9px;text-align:center;background-color:lavender;text-decoration:none;}
.text {font-family:Tahoma,Verdana,Arial;color:#000000;font-size:9px;text-align:right;}
.link {font-family:Tahoma,Verdana,Arial;font-size:11px;color:#0000FF;}
.link a:link,a:visited {font-family:Tahoma,Verdana,Arial;font-size:11px;color:#0000FF;text-decoration:none;}
</style>
	";
}


