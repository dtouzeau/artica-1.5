<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsSquidAdministrator){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}



if(isset($_GET["services"])){section_services();exit;}
if(isset($_GET["status"])){squid_left_status();exit;}
if(isset($_GET["squid-services"])){all_status();exit;}
if(isset($_GET["architecture-tabs"])){section_architecture_tabs();exit;}
if(isset($_GET["architecture-status"])){section_architecture_status();exit;}
if(isset($_GET["architecture-content"])){section_architecture_content();exit;}




if(isset($_GET["members-status"])){section_members_status();exit;}
if(isset($_GET["members-content"])){section_members_content();exit;}
if(isset($_GET["basic_filters-content"])){section_basic_filters_content();exit;}



//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
if(!$users->AsAnAdministratorGeneric){die("Not autorized");}
if(isset($_GET["off"])){off();exit;}
if(function_exists($_GET["function"])){call_user_func($_GET["function"]);exit;}

$page=CurrentPageName();
$tpl=new templates();
$sock=new sockets();
$users=new usersMenus();

$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("service-check-48.png", "services_status","system_information_text", "QuickLinkSystems('section_status')"));
$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-parameters.png", "parameters","section_security_text", "QuickLinkSystems('section_architecture')"));
$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-network-user.png", "members","softwares_mangement_text", "QuickLinkSystems('section_members')"));
$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("Firewall-Secure-48.png", "basic_filters","softwares_mangement_text", "QuickLinkSystems('section_basic_filters')"));

if($users->DANSGUARDIAN_INSTALLED){
	if($users->AsDansGuardianAdministrator){
		$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("web-filtering-48.png", "WEB_FILTERING","softwares_mangement_text", "QuickLinkSystems('section_webfiltering_dansguardian')"));
	}
}
if($users->APP_UFDBGUARD_INSTALLED){
	if($users->AsDansGuardianAdministrator){
		$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("web-filtering-48.png", "WEB_FILTERING","softwares_mangement_text", "QuickLinkSystems('section_webfiltering_dansguardian')"));
	}
}


if($users->KAV4PROXY_INSTALLED){
	$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("bigkav-48.png", "APP_KAV4PROXY","softwares_mangement_text", "QuickLinkSystems('section_kav4proxy')"));
	
}


$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("web-site-48.png", "main_interface","main_interface_back_interface_text", "QuickLinksHide()"));

$count=1;


while (list ($key, $line) = each ($tr) ){
	if($line==null){continue;}
	$f[]="<li id='kwick1'>$line</li>";
	$count++;
	
}



	
	$html="
            <div id='QuickLinksTop'>
                <ul class='kwicks'>
					".@implode("\n", $f)."
                    
                </ul>
            </div>
	
	<div id='quicklinks-samba' style='width:900px'></div>
	<div id='BodyContent' style='width:900px'></div>
	
	
	<script>
		function LoadQuickTaskBar(){
			$(document).ready(function() {
				$('#QuickLinksTop .kwicks').kwicks({max: 205,spacing:  5});
			});
		}
		
		function QuickLinksSamba(){
			Set_Cookie('QuickLinkCache', 'quicklinks.fileshare.php', '3600', '/', '', '');
			LoadAjax('BodyContent','quicklinks.fileshare.php');
		}
		
		function QuickLinksProxy(){
			Set_Cookie('QuickLinkCache', 'quicklinks.proxy.php', '3600', '/', '', '');
			LoadAjax('BodyContent','quicklinks.proxy.php');		
		
		}
		
		function QuickLinksKav4Proxy(){
			Set_Cookie('QuickLinksKav4Proxy', 'kav4proxy.php?inline=yes', '3600', '/', '', '');
			LoadAjax('BodyContent','kav4proxy.php?inline=yes');		
		
		}		
		
		
		
		function QuickLinkSystems(sfunction){
			Set_Cookie('QuickLinkCacheProxy', '$page?function='+sfunction, '3600', '/', '', '');
			LoadAjax('BodyContent','$page?function='+sfunction);
		}
		
		function QuickLinkMemory(){
			var memorized=Get_Cookie('QuickLinkCacheProxy');
			if(!memorized){
				QuickLinkSystems('section_status');
				return;
			}
			
			if(memorized.length>0){
				LoadAjax('BodyContent',memorized);
			}else{
				QuickLinkSystems('section_status');

			}
		
		}
		
		LoadQuickTaskBar();
		QuickLinkMemory();
	</script>
	";
	
	
	


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);





function tabs(){
		$tpl=new templates();
		$page=CurrentPageName();
		$users=new usersMenus();
	
		$array["services"]='{samba_quicklinks_services}';
		$array["blacklist_databases"]='{blacklist_databases}';
		if($users->KAV4PROXY_INSTALLED){
			$array["kav4proxy"]='{APP_KAV4PROXY}';
			
		}
		
		
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="kav4proxy"){
			$tab[]="<li><a href=\"kav4proxy.php?inline=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			continue;
		}
		
		
		if($num=="blacklist_databases"){
			$tab[]="<li><a href=\"squid.blacklist.php\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			continue;
		}
	
		$tab[]="<li><a href=\"$page?$num=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			
		}
	
	
	

	$html="
		<div id='main_squid_quicklinks_config' style='background-color:white;margin-top:10px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squid_quicklinks_config').tabs();
			

			});
		</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);

}

function section_webfiltering_dansguardian(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<div id='QuicklinksDansguardian'></div>
	<script>
		LoadAjax('QuicklinksDansguardian','dansguardian2.php');
	</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);
	}	
	



function section_kav4proxy(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<div id='QuicklinksKav4proxy'></div>
	<script>
		LoadAjax('QuicklinksKav4proxy','kav4proxy.php?inline=yes');
	</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);
	}

function section_members(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<div class=explain>{squid_members_explain}</div>
	
	<table style='width:100%'>
	<tbody>
		<tr>
			<td style='width:1%' valign='top'><div id='members-status'></div></td>
			<td style='width:99%' valign='top'><div id='members-content'></div></td>
		</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('members-status','$page?members-status=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function section_basic_filters(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<div class=explain>{squid_basic_filters_explain}</div>
	<div id='basic_filters-content'></div>	
	<script>
		LoadAjax('basic_filters-content','$page?basic_filters-content=yes');
	</script>
	";
		echo $tpl->_ENGINE_parse_body($html);
	
}


function section_architecture(){
	$page=CurrentPageName();
	$tpl=new templates();
	echo "<div id='squid-section-architecture'></div>
		<script>
		LoadAjax('squid-section-architecture','$page?architecture-tabs=yes');
	</script>
	
	";

}	
	
function section_architecture_start(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<div class=explain>{squid_architecture_explain}</div>
	
	<table style='width:100%'>
	<tbody>
		<tr>
			<td style='width:1%' valign='top'><div id='architecture-status'></div></td>
			<td style='width:99%' valign='top'><div id='architecture-content'></div></td>
		</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('architecture-status','$page?architecture-status=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function section_basic_filters_content(){
	$page=CurrentPageName();
	$sock=new sockets();
	$users=new usersMenus();
	$banUserAgent=Paragraphe('user-agent-ban-64.png','{ban_browsers}','{ban_browsers_text}',"javascript:Loadjs('squid.popups.php?script=user-agent-ban')");	
	$ftp_user=Paragraphe('ftp-user-64.png','{squid_ftp_user}','{squid_ftp_user_text}',"javascript:Loadjs('squid.ftp.user.php')");
	$messengers=Paragraphe('messengers-64.png','{instant_messengers}','{squid_instant_messengers_text}',"javascript:Loadjs('squid.messengers.php')");	
	//$filetype=Paragraphe("pieces-jointes.png","{file_blocking}","{file_blocking_text}","javascript:acl_fileblock()");
	$connection_time=Paragraphe("64-planning.png","{connection_time}","{connection_time_text}","javascript:ConnectionTime();");
	$denywebistes=Paragraphe("folder-64-denywebistes.png","{deny_websites}","{deny_websites_text}","javascript:Loadjs('squid.popups.php?script=url_regex');");

	//$tr[]=$filetype;
	$tr[]=$denywebistes;
	$tr[]=$messengers;
	$tr[]=$ftp_user;
	$tr[]=$banUserAgent;
	$tr[]=$connection_time;
	
	$html="<center><div style='width:700px'>".CompileTr3($tr)."</div></center>";
	$tpl=new templates();
	$html= $tpl->_ENGINE_parse_body($html,'squid.index.php');
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
	echo $html;		
		
}


function section_members_content(){
	$page=CurrentPageName();
	$sock=new sockets();
	$users=new usersMenus();
	
	$authenticate_users=Paragraphe('members-priv-64.png','{authenticate_users}','{authenticate_users_text}',"javascript:Loadjs('squid.popups.php?script=ldap')");	
	$APP_SQUIDKERAUTH=Paragraphe('wink3_bg.png','{APP_SQUIDKERAUTH}','{APP_SQUIDKERAUTH_TEXT}',"javascript:Loadjs('squid.adker.php')");
	$blackcomputer=Paragraphe("64-black-computer.png","{black_ip_group}",'{black_ip_group_text}',"javascript:Loadjs('dansguardian.bannediplist.php');");
	$whitecomputer=Paragraphe("64-white-computer.png","{white_ip_group}",'{white_ip_group_text}',"javascript:Loadjs('dansguardian.exceptioniplist.php');");

	if(!$users->MSKTUTIL_INSTALLED){
		$APP_SQUIDKERAUTH=Paragraphe('wink3_bg-grey.png','{APP_SQUIDKERAUTH}','{APP_SQUIDKERAUTH_TEXT}',"javascript:Loadjs('squid.adker.php')");
	}
	if(strlen($users->squid_kerb_auth_path)<2){
		$APP_SQUIDKERAUTH=Paragraphe('wink3_bg-grey.png','{APP_SQUIDKERAUTH}','{APP_SQUIDKERAUTH_TEXT}',"javascript:Loadjs('squid.adker.php')");
	}	
	

	$tr[]=$APP_SQUIDKERAUTH;
	$tr[]=$authenticate_users;
	$tr[]=$blackcomputer;
	$tr[]=$whitecomputer;
	
	$html=CompileTr3($tr);
	$tpl=new templates();
	$html= $tpl->_ENGINE_parse_body($html,'squid.index.php');
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
	echo $html;		
	
		
}

function section_architecture_tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$array["architecture-content"]='{main_parameters}';
	$array["caches"]='{caches}';
	
	


	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="caches"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"squid.caches.php?byQuicklinks=yes\" style='font-size:14px'><span>$ligne</span></a></li>\n");
			continue;
			
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=$time\" style='font-size:14px'><span>$ligne</span></a></li>\n");
	}
	
	
	
	echo "$menus
	<div id=main_squid_quicklinks_tabs style='width:99%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
			$(document).ready(function(){
				$('#main_squid_quicklinks_tabs').tabs();
			});
		</script>";	

}


function section_architecture_content(){
$page=CurrentPageName();
$sock=new sockets();
$users=new usersMenus();
	$compilefile="ressources/logs/squid.compilation.params";
	if(!is_file($compilefile)){
		$sock->getFrameWork("squid.php?compil-params=yes");
	}
	
	$COMPILATION_PARAMS=unserialize(base64_decode(file_get_contents($compilefile)));
	
	$listen_port=Paragraphe('folder-network-64.png','{listen_port}','{listen_port_text}',"javascript:Loadjs('squid.popups.php?script=listen_port')");
	$dns_servers=Paragraphe('64-bind.png','{dns_servers}','{dns_servers_text}',"javascript:Loadjs('squid.popups.php?script=dns')");
	$visible_hostname=Paragraphe('64-work-station-linux.png','{visible_hostname}','{visible_hostname_intro}',"javascript:Loadjs('squid.popups.php?script=visible_hostname')");
	$transparent_mode=Paragraphe('relayhost.png','{transparent_mode}','{transparent_mode_text}',"javascript:Loadjs('squid.newbee.php?squid-transparent-js=yes')");
	$squid_parent_proxy=Paragraphe('server-redirect-64.png','{squid_parent_proxy}','{squid_parent_proxy_text}',"javascript:Loadjs('squid.parent.proxy.php')");
	$squid_reverse_proxy=Paragraphe('squid-reverse-64.png','{squid_reverse_proxy}','{squid_reverse_proxy_text}',"javascript:Loadjs('squid.reverse.proxy.php')");
	$your_network=Paragraphe('folder-realyrules-64.png','{your_network}','{your_network_text}',"javascript:Loadjs('squid.popups.php?script=network')");
    
    
    
    
    
   
    $sslbump=Paragraphe('web-ssl-64.png','{squid_sslbump}','{squid_sslbump_text}',"javascript:Loadjs('squid.sslbump.php')");
    
    if(!isset($COMPILATION_PARAMS["enable-ssl"])){
    	$sslbump=Paragraphe('web-ssl-64-grey.png','{squid_sslbump}','{squid_sslbump_text}',"");
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
	$tr=array();
	$tr[]=$listen_port;
	$tr[]=$visible_hostname;
	$tr[]=$transparent_mode;
	$tr[]=$dns_servers;
	$tr[]=$your_network;
	$tr[]=$APP_SQUIDKERAUTH;
	
	
	
	$tr[]=$proxy_pac;
	$tr[]=$proxy_pac_rules;
	
	
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
$html="<div id='architecture-status'></div>
<center style='width:100%'>
<div style='width:80%;text-align:center'>$html</div>
</center>
<script>LoadAjaxTiny('architecture-status','$page?architecture-status=yes');</script>";

$html=$tpl->_ENGINE_parse_body($html,'squid.index.php');
SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;	
	
}

function section_security(){
	
	$tr[]=kaspersky();
	$tr[]=statkaspersky();
	$tr[]=clamav();
	$tr[]=icon_troubleshoot();
	$tr[]=certificate();
	$tr[]=icon_externalports();
	$tr[]=incremental_backup();
$tables[]="<table style='width:100%' class=form><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	

$links=@implode("\n", $tables);
$heads=section_computer_header();
$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$heads</td>
	<td valign='top'>$links</td>
</tr>
</table>
";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function squid_left_status(){
	$tpl=new templates();
	$page=CurrentPageName();	
	include_once(dirname(__FILE__)."/ressources/class.status.inc");
	$status=new status();
	$squid_status=$status->Squid_status();
	
	$q=new mysql();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?squid-ini-status=yes")));
	$master_version=$ini->_params["SQUID"]["master_version"];
	$master_pid=$ini->_params["SQUID"]["master_pid"];
	$users=new usersMenus();
	$squid=new squidbee();
	
	
	
	
if($ini->_params["SQUID"]["running"]==0){
		$img="status_postfix_bg_failed.png";
		$status="{stopped}";
		$start="<hr><div style='text-align:right'>".button("{start}","Loadjs('StartStopServices.php?APP=APP_SQUID&cmd=squid-cache&action=start')")."</div>";
	}else{
			if(preg_match("#2\.3.*#",$master_version)){$img='status_postfix_bg_ok23.png';}
			if(preg_match("#2\.5.*#",$master_version)){$img='status_postfix_bg_ok25.png';}
			if(preg_match("#2\.7.*#",$master_version)){$img='status_postfix_bg_ok27.png';}
			if(preg_match("#2\.6.*#",$master_version)){$img='status_postfix_bg_ok26.png';}			
			if(preg_match("#2\.8.*#",$master_version)){$img='status_postfix_bg_ok28.png';}
			if(preg_match("#2\.9.*#",$master_version)){$img='status_postfix_bg_ok29.png';}
			if(preg_match("#3\.0.*#",$master_version)){$img='status_postfix_bg_ok30.png';}
			if(preg_match("#3\.1.*#",$master_version)){$img='status_postfix_bg_ok31.png';}
			if(preg_match("#3\.2.*#",$master_version)){$img='status_postfix_bg_ok32.png';}
			if(preg_match("#3\.3.*#",$master_version)){$img='status_postfix_bg_ok33.png';}
			$text="{service_running}<br>{using_version} $master_version {pid} $master_pid";
	}

if($ini->_params["SQUID"]["icap_enabled"]<>'1'){
	
	$icap="<table style='width:100%;margin:0px;' " .CellRollOver($js_service).">
		<tr>
			<td width=1%><img src='img/danger16.png'></td>
			<td align='left' nowrap><strong style='color:#D01A1A;font-size:11px'>{no_icap_support}</td>
			<td width=1% align='right'>&nbsp;</td>
		</tr>
	</table>";
	
	
}

	$EnableKavICAPRemote=$sock->GET_INFO("EnableKavICAPRemote");
	$KavICAPRemoteAddr=$sock->GET_INFO("KavICAPRemoteAddr");
	$KavICAPRemotePort=$sock->GET_INFO("KavICAPRemotePort");	
	if(!is_numeric($EnableKavICAPRemote)){$EnableKavICAPRemote=0;}
	
	if($EnableKavICAPRemote==1){
		$fp=@fsockopen($KavICAPRemoteAddr, $KavICAPRemotePort, $errno, $errstr, 1);
			if(!$fp){
				$text_kavicap_error="<div>{kavicap_unavailable_text}<br><strong>
				<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('squid.kavicap.php');\" style='font-size:12px;color:#D70707;text-decoration:underline'>$KavICAPRemoteAddr:$KavICAPRemotePort</a><br>$errstr</div>";				
			}
		
		@fclose($fp);			
	}
	
	$q=new mysql_squid_builder();
	
	if(!$q->TestingConnection()){
		$img="status_postfix_bg_failed.png";
		$title="{MYSQL_ERROR}";
		$text_error_sql="<div><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('squid.mysql.php');\" 
		style='font-size:12px;color:#D70707;text-decoration:underline'>$title:$q->mysql_error</a></div>";
	}	
	
	$q=new mysql_squid_builder();
	$requests=$q->EVENTS_SUM();
	$requests=numberFormat($requests,0,""," ");
	
	$q=new mysql();
	$sql="SELECT COUNT( ID ) as tcount FROM blocked_websites WHERE WEEK( zDate ) = WEEK( NOW( ) )";
	$ligneW=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$blocked_today=numberFormat($ligneW["tcount"],0,""," ")." {blocked_websites} {this_week}";
	
	$q=new mysql_squid_builder();
	$websitesnums=$q->COUNT_ROWS("dansguardian_sitesinfos","artica_backup");
	$websitesnums=numberFormat($websitesnums,0,""," ");	
	
	$q=new mysql_squid_builder();
	$categories=$q->COUNT_ROWS("dansguardian_community_categories");
	$categories=numberFormat($categories,0,""," ");		
	
	$sock=new sockets();
	$sock->SET_INFO("squidStatsCategoriesNum",$categories);
	$sock->SET_INFO("squidStatsWebSitesNum",$websitesnums);
	$sock->SET_INFO("squidStatsBlockedToday",$blocked_today);
	$sock->SET_INFO("squidStatsRequestNumber",$requests);
	
	$migration_pid=unserialize(base64_decode($sock->getFrameWork("squid.php?migration-stats=yes")));
	if(is_array($migration_pid)){
		$text_script="<span style='color:#B80000;font-size:13px'>{migration_script_run_text} PID:{$migration_pid[0]} {since}:{$migration_pid[1]}Mn</span>";
	}	
	
	if($users->KAV4PROXY_INSTALLED){
		$img="info-18.png";
		$text="{enabled}";
		
		if($squid->enable_kavproxy<>1){$text="{disabled}";$img="status_warning.gif";}
			$services_enabled=$services_enabled."
			<tr ". CellRollOver("Loadjs('squid.popups.php?script=plugins')").">
				<td width=1%><img src='img/$img'></td>
				<td class=legend style='font-size:13px;' align='left' width=1% nowrap>{APP_KAV4PROXY}:</td>
				<td class=legend style='font-size:14px' align='left' width=100%>$text</td>
			</tr>
			";
			
		}
	if($users->APP_UFDBGUARD_INSTALLED){
			$img="info-18.png";
		$text="{enabled}";
		$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
		if($EnableUfdbGuard<>1){$text="{disabled}";$img="status_warning.gif";}
			$services_enabled=$services_enabled."
			<tr ". CellRollOver("Loadjs('squid.popups.php?script=plugins')").">
				<td width=1%><img src='img/$img'></td>
				<td class=legend style='font-size:13px;' nowrap width=1% align='left'>{APP_UFDBGUARD}:</td>
				<td class=legend style='font-size:14px' width=100% align='left'>$text</td>
			</tr>
			";
			
		}	
	
	$design="
	$text_error_sql
	$text_script
	$text_kavicap_error
	<table style='width:250px;margin-top:10px;' class=form>
	<tbody>
		<tr>
			<td>&nbsp;</td>
			<td class=legend nowrap>{version}:</td>
			<td style='font-size:14px'>$master_version</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td class=legend>PID:</td>
			<td style='font-size:14px'>{$master_pid}</td>
		</tr>
			$services_enabled
		</tbody>
	</table>	
	</table>
	<div style='width:100%;text-align:right'>". imgtootltip("refresh-24.png","{refresh}","LoadAjax('squid-status','squid.main.quicklinks.php?status=yes');")."</div>
	
	";

	
	
	$html="
	$design
	<center>
	
		<div id='squid-status-stats' class=form style='width:90%'></div>
	</center>
	
	
	<script>
		LoadAjax('squid-status-stats','squid.traffic.statistics.php?squid-status-stats=yes');	
		LoadAjax('squid-services','$page?squid-services=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function section_status(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$html="
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><div id='squid-status'></div></td>
		<td width=99% valign='top'><div id='squid-services'>". @implode("\n", $tables)."</div></td>
	</tr>
	</table>
	
	<script>
		LoadAjax('squid-status','$page?status=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function section_members_status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$squid=new squidbee();
	$listen_port=$squid->listen_port;
	$visible_hostname=$squid->visible_hostname;
	$hasProxyTransparent=$squid->hasProxyTransparent;
	if($hasProxyTransparent==1){$hasProxyTransparent="{yes}";}else{$hasProxyTransparent="{no}";}
	
	if(!$squid->ACL_ARP_ENABLED){
		$arpinfos=
		"<table style='width:100%'>
		<tbody>
		<tr>
			<td width:1% valign='top'><img src='img/warning-panneau-32.png'></td>
			<td><strong style='font-size:12px'>{no_acl_arp}</strong><br>
			<span style='font-size:11px'>{no_acl_arp_text}</span></td>
		</tr>
		</tbody>
		</table>";
		
		
	}else{
		
		$arpinfos=
		"<table style='width:100%'>
		<tbody>
		<tr>
			<td width:1% valign='top'><img src='img/32-infos.png'></td>
			<td><strong style='font-size:12px'>{yes_acl_arp}</strong><br>
			<span style='font-size:11px'>{yes_acl_arp_text}</span></td>
		</tr>
		</tbody>
		</table>";		
		
		
	}	
	
	if(strlen($visible_hostname)>10){$visible_hostname=substr($visible_hostname, 0,7)."...";}
	$html="<table style='width:200px' class=form>
	<tr>
		<td class=legend nowrap>{version}:</td>
		<td>".texthref($squid->SQUID_VERSION,null)."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{listen_port}:</td>
		<td>".texthref($listen_port,"Loadjs('squid.popups.php?script=listen_port')")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{visible_hostname}:</td>
		<td>".texthref($visible_hostname,"Loadjs('squid.popups.php?script=visible_hostname')")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{transparent_mode}:</td>
		<td>".texthref($hasProxyTransparent,"Loadjs('squid.newbee.php?squid-transparent-js=yes')")."</td>
	</tr>	
	
	</table>
	$arpinfos
	<script>
		LoadAjax('members-content','$page?members-content=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function section_architecture_status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$squid=new squidbee();
	$listen_port=$squid->listen_port;
	$visible_hostname=$squid->visible_hostname;
	$hasProxyTransparent=$squid->hasProxyTransparent;
	if($hasProxyTransparent==1){$hasProxyTransparent="{yes}";}else{$hasProxyTransparent="{no}";}
	
	if(strlen($visible_hostname)>10){$visible_hostname=substr($visible_hostname, 0,7)."...";}
	$html="<table style='width:100%' class=form>
	<tr>
		<td class=legend nowrap>{version}:</td>
		<td>".texthref($squid->SQUID_VERSION,null)."</td>
		<td style='font-size:14px;font-weight:bold'>&nbsp;|&nbsp;</td>
		<td class=legend nowrap>{listen_port}:</td>
		<td>".texthref($listen_port,"Loadjs('squid.popups.php?script=listen_port')")."</td>
		<td style='font-size:14px;font-weight:bold'>&nbsp;|&nbsp;</td>
		<td class=legend nowrap>{visible_hostname}:</td>
		<td>".texthref($visible_hostname,"Loadjs('squid.popups.php?script=visible_hostname')")."</td>
		<td style='font-size:14px;font-weight:bold'>&nbsp;|&nbsp;</td>
		<td class=legend nowrap>{transparent_mode}:</td>
		<td>".texthref($hasProxyTransparent,"Loadjs('squid.newbee.php?squid-transparent-js=yes')")."</td>
	</tr>
	</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function all_status(){
	
	
	$page=CurrentPageName();
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$tpl=new templates();
	
	
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?squid-ini-status=yes')));


	$squid_status=DAEMON_STATUS_ROUND("SQUID",$ini,null,1);
	$dansguardian_status=DAEMON_STATUS_ROUND("DANSGUARDIAN",$ini,null,1);
	$kav=DAEMON_STATUS_ROUND("KAV4PROXY",$ini,null,1);
	$cicap=DAEMON_STATUS_ROUND("C-ICAP",$ini,null,1);
	$APP_PROXY_PAC=DAEMON_STATUS_ROUND("APP_PROXY_PAC",$ini,null,1);
	$APP_SQUIDGUARD_HTTP=DAEMON_STATUS_ROUND("APP_SQUIDGUARD_HTTP",$ini,null,1);
	$APP_UFDBGUARD=DAEMON_STATUS_ROUND("APP_UFDBGUARD",$ini,null,1);
	
	$md=md5(date('Ymhis'));
	$tr[]=$squid_status;
	$tr[]=$dansguardian_status;
	$tr[]=$kav;
	$tr[]=$cicap;
	$tr[]=$APP_PROXY_PAC;
	$tr[]=$APP_SQUIDGUARD_HTTP;
	$tr[]=$APP_UFDBGUARD;

	
	$tables[]="<table style='width:100%'><tr>";
	$t=0;
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==2){$t=0;$tables[]="</tr><tr>";}
			}
	
	if($t<2){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}	
	
	
	$refresh=imgtootltip("refresh-32.png","{refresh}","LoadAjax('squid-services','$page?squid-services=yes');");
	$tables[]="</table>";
	$html="
		<div class=explain>
			{APP_SQUID_TEXT}
		</div>
		<div style='text-align:right;margin-bottom:10px'>".button("{restart_all_services}","Loadjs('squid.restart.php')")."</div>
		".@implode("\n", $tables)."<div style='text-align:right'>$refresh</div>";
	
			
	
	
	echo $tpl->_parse_body($html);
	
	
	
	}

