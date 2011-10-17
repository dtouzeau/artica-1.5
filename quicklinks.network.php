<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsSystemAdministrator){$tpl=new templates();$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');echo "<H1>$alert</H1>";die();	}
if(isset($_GET["start"])){start();exit;}
if(function_exists($_GET["function"])){call_user_func($_GET["function"]);exit;}


js();


function js(){
	$page=CurrentPageName();
	echo "<script>LoadAjax('middle','$page?start=yes');</script>";
}

function start(){
	
$page=CurrentPageName();
$tpl=new templates();
$sock=new sockets();
$users=new usersMenus();

$nic=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("folder-network-48.png", "parameters",null, "QuickLinkSystems('section_mynic')"));
$openvpn=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-openvpn.png", "APP_OPENVPN",null, "QuickLinkSystems('section_openvpn')"));
$network_services=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-network-server.png", "network_services",null, "QuickLinkSystems('section_network_services')"));
$dhcp=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-dhcp.png", "APP_DHCP",null, "QuickLinkSystems('section_dhcp')"));
$computers=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-computer-alias.png", "browse_computers","browse_computers_text", "QuickLinkSystems('section_computers')"));



$stats=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("perf-stats-48.png", "statistics",null, "QuickLinkSystems('section_statistics')"));


if(!$users->OPENVPN_INSTALLED){$openvpn=null;}

$tr[]=$nic;
$tr[]=$dhcp;
$tr[]=$network_services;
$tr[]=$computers;
$tr[]=$stats;
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
	
	<div id='BodyContent' style='width:900px'></div>
	
	
	<script>
		function LoadQuickTaskBar(){
			$(document).ready(function() {
				$('#QuickLinksTop .kwicks').kwicks({max: 205,spacing:  5});
			});
		}
		
	
		function QuickLinkSystems(sfunction){
			Set_Cookie('QuickLinkCacheNet', '$page?function='+sfunction, '3600', '/', '', '');
			LoadAjax('BodyContent','$page?function='+sfunction);
		}
		
		function QuickLinkMemory(){
			var memorized=Get_Cookie('QuickLinkCacheNet');
			if(!memorized){QuickLinkSystems('section_mynic');return;}
			if(memorized.length>0){LoadAjax('BodyContent',memorized);}else{QuickLinkSystems('section_mynic');}
		
		}
		
		LoadQuickTaskBar();
		QuickLinkMemory();
	</script>
	";
	
	
	


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function section_mynic(){echo "<script>Loadjs('system.nic.config.php?js=yes&in-front-ajax=yes&newinterface=yes')</script>";}
function section_openvpn(){echo "<script>Loadjs('index.openvpn.php?infront=yes')</script>";}
function section_network_services(){echo "<script>Loadjs('system.index.php?newtab=network')</script>";}
function section_dhcp(){echo "<script>Loadjs('index.gateway.php?index_dhcp=yes&in-front-ajax=yes&newinterface=yes')</script>";}
function section_statistics(){echo "<script>Loadjs('statistics.vnstat.php?newinterface=yes')</script>";}
function section_computers(){echo "<script>Loadjs('ocs.search.php?js-in-front=yes')</script>";}



tabs();
function active_directory_status(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$sock=new sockets();
	$users=new usersMenus();
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	if($EnableSambaActiveDirectory==0){
		$status=Paragraphe("64-grey.png", "{NO_AD_CONNECTION}", "{NO_AD_CONNECTION_TEXT}");
		
		
	}else{
		$datas=$sock->getFrameWork("samba.php?test-ads-join=yes");
		if(preg_match("#FALSE:(.+)#", $datas,$re)){
			$status=Paragraphe("error-64.png", "{AD_CONNECTION_ERROR}", "{error}:{$re[1]}");
		}
		if(preg_match("#TRUE#", $datas,$re)){
			$array=unserialize(base64_decode($sock->getFrameWork("samba.php?adsinfos=yes")));
			while (list ($num, $ligne) = each ($array) ){
				$tr[]="<tr><td class=legend nowrap>$num:</td><td><strong style='font-size:11px'>$ligne</strong></td></tr>";
				$ht=$ht."$num:<strong>$ligne</strong><br>";
			}
			$smbver=newtsamba();
			
			$status=Paragraphe("ok64.png", "{AD_CONNECTION_OK}", $ht);
			if($smbver<>$users->SAMBA_VERSION){
				$infos=Paragraphe("64-infos.png", "V$smbver...", "{samba_info_latest_ad_explain}:V.$users->SAMBA_VERSION","javascript:Loadjs('setup.index.php?js=yes');");
			}
		}		
		
	}
	
	if(count($tr)>0){
		$status2="<table class=form>".@implode("\n", $tr)."</table>";
	}
	
	$html="<center><img src='img/ad-server-logo.png' style='margin:10px'>
	$status$infos$status2</center>
	";
echo $tpl->_ENGINE_parse_body($html);	
}

function newtsamba(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	if($GLOBALS["INDEXFF"]==null){$GLOBALS["INDEXFF"]=@file_get_contents(dirname(__FILE__). '/ressources/index.ini');}
	$ini->loadString($GLOBALS["INDEXFF"]);
	return $ini->_params["NEXT"]["samba"];
}


function tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	
		$array["services"]='{samba_quicklinks_services}';
		$array["sharing_behavior"]='{sharing_behavior}';
		$array["shared_folders"]='{shared_folders}';
		
		$array["virtual_servers"]='{virtual_servers}';
		$array["remote_announce"]='{networks}';
		$array["acldisks"]="ACLs";
		
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="remote_announce"){
			$tab[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.remote_announce.php\"><span style='font-size:14px'>$ligne</span></a></li>\n");
			continue;
		}		

		if($num=="acldisks"){
			$tab[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.shared.folders.list.php?acldisks=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n");
			continue;
		}	

		if($num=="shared_folders"){
			$tab[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.index.php?main=shared_folders\"><span style='font-size:14px'>$ligne</span></a></li>\n");
			continue;
		}

		if($num=="virtual_servers"){
			$tab[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.virtual-servers.php?popup=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n");
			continue;
		}				
		
		
		
		
		$tab[]="<li><a href=\"$page?$num=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n";
			
		}
	
	
	

	$html="
		<div id='main_samba_quicklinks_config' style='background-color:white;margin-top:10px'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_samba_quicklinks_config').tabs();
			

			});
		</script>
	
	";	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function section_sharing_behavior(){
	if(GET_CACHED(__FILE__, __FUNCTION__,__FUNCTION__)){return;}
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();	
	$NTconfig=Buildicon64("DEF_ICO_NTPOL");
	$roaming=Buildicon64('DEF_ICO_ROAMINGP');
	$activedirectory=Paragraphe("wink3_bg.png","{ad_samba_member}","{ad_samba_member_text}","javascript:Loadjs('ad.connect.php')");
	
	$icon_samba_type=Paragraphe("64-server-ask.png",'{windows_network_neighborhood}','{windows_network_neighborhood_text}',
	"javascript:neighborhood();",'{windows_network_neighborhood}');
	
	$admin_domain=Paragraphe("members-priv-64.png",'{domain_admin}','{domain_admin_text}',
	"javascript:DomainAdmin();",'{domain_admin_text}');
	
	
	$acl_support=Paragraphe("acl-support-64.png",'{ACLS_SUPPORT}','{ACLS_SUPPORT_TEXT}',
	"javascript:Loadjs('samba.acls.settings.php');",'{ACLS_SUPPORT_TEXT}');
	
	$samba_ldap=Paragraphe("database-connect-settings-64.png",'{APP_LDAP}','{SAMBA_LDAP_EXTERN_TEXT}',
	"javascript:Loadjs('samba.ldap.php?js=yes');",'{ACLS_SUPPORT_TEXT}');
	
	$wins_server=Paragraphe("64-win-nic-browse.png",'{wins_server}','{samba_wins_explain}',
	"javascript:Loadjs('samba.wins.php?js=yes');",'{samba_wins_explain}');
	
		
	
	
	$tr[]=$activedirectory;
	$tr[]=$icon_samba_type;
	$tr[]=$admin_domain;
	$tr[]=$acl_support;
	$tr[]=$samba_ldap;
	$tr[]=$wins_server;
	$tr[]=$NTconfig;		
	$tr[]=$roaming;
	$tr[]=$greyhole;
	
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

	$html="<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><div id='smb-ad-status'></div></td>
		<td width=99%' valign='top'>".@implode("\n", $tables)."</td>
	</tr>
	</table>
	<script>
		LoadAjax('smb-ad-status','$page?ad-status=yes');
		Loadjs('samba.index.php?jsaddons=yes');
	</script>
	";
	
	$html=$tpl->_ENGINE_parse_body($html);

	SET_CACHED(__FILE__, __FUNCTION__, __FUNCTION__, $html);
	echo $html;
}


function section_services(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	
	$global_parameters=Paragraphe('parameters2-64.png',"{SAMBA_MAIN_PARAMS}",'{SAMBA_MAIN_PARAMS_TEXT}',
	"javascript:Loadjs('samba.index.php?main-params-js=yes')");
	
	

	$cups=Paragraphe('64-printer-grey.png',"{APP_CUPS}",'{feature_not_installed}');
	$greyhole=Paragraphe('folder-64-artica-backup-grey.png',"{APP_GREYHOLE}",'{feature_not_installed}');
	$dropbox=Paragraphe('dropbox-64-grey.png','{APP_DROPBOX}','{APP_DROPBOX_TEXT}',"",null,210,null,0,false);
		//$samba=Paragraphe('64-samba.png','{APP_SAMBA}','{APP_SAMBA_TEXT}',"javascript:Loadjs('samba.index.php?script=smbpop')",null,210,null,0,false);
		//$share=Paragraphe('64-share.png','{SHARE_FOLDER}','{SHARE_FOLDER_TEXT}',"javascript:Loadjs('SambaBrowse.php');",null,210,null,0,false);
		//$audit=Paragraphe('folder-64-spamassassin.png','{samba_audit}','{samba_audit_text}',"new:smb-audit/index.php",null,210,null,0,false);
		//$usb=Paragraphe('usb-share-64.png','{usb_share}','{usb_share_text}',"javascript:Loadjs('usb.browse.php')",null,210,null,0,false);
		

		if($users->CUPS_INSTALLED){$cups=Paragraphe('64-printer.png',"{APP_CUPS}",'{APP_CUPS_TEXT}',"javascript:Loadjs('cups.index.php')",null,210,null,0,false);}
		$plugins=Paragraphe("folder-lego.png","{plugins}","{enable_plugins_text}","javascript:Loadjs('samba.plugins.php')");
		if($users->GREYHOLE_INSTALLED){$greyhole=Paragraphe('folder-64-artica-backup.png',"{APP_GREYHOLE}",'{enable_grehole_text}',"javascript:Loadjs('greyhole.php')");}
		$sync=Buildicon64('DEF_ICO_SAMBA_SYNCHRO');
		
		if($users->DROPBOX_INSTALLED){
			$dropbox=Paragraphe('dropbox-64.png','{APP_DROPBOX}','{APP_DROPBOX_TEXT}',"javascript:Loadjs('samba.dropbox.php')",null,210,null,0,false);
		}

	$restart=Paragraphe("64-refresh.png",'{APP_SAMBA_RESTART}','{APP_SAMBA_RESTART_TEXT}',
	"javascript:RestartSmbServices();",'{APP_SAMBA_RESTART_TEXT}');

	$disable_samba=Paragraphe("server-disable-64.png",'{enable_disable_samba}','{enable_disable_samba_text}',
	"javascript:Loadjs('samba.disable.php');",'{enable_disable_samba_text}');
		
	$tr[]=$global_parameters;
	$tr[]=$sync;
	$tr[]=$restart;			
	$tr[]=$cups;
	$tr[]=$greyhole;
	$tr[]=$dropbox;
	$tr[]=$plugins;
	$tr[]=$disable_samba;
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

	$html="<table style='width:100%'>
	<tr>
		<td width=1% valign='top'>
		<div style='text-align:right;margin-bottom:10px'>". imgtootltip("refresh-24.png","{resfresh}","RefreshtSambaStatus()")."</div>
		
		<div id='smbservices-status'></div></td>
		<td width=99%' valign='top'>".@implode("\n", $tables)."</td>
	</tr>
	</table>
	<script>
		function RefreshtSambaStatus(){
			LoadAjax('smbservices-status','samba.index.php?status=yes');
			}
		
		RefreshtSambaStatus();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
		
}		