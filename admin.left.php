<?php
$GLOBALS["ICON_FAMILY"]="SYSTEM";
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
if(isset($_GET["part1"])){left_infos_1();exit;}
if(isset($_GET["partall"])){left_infos_1();left_infos_2();exit;}
if(isset($_GET["old-menu"])){old_menus_js();exit;}
if(isset($_GET["old-menu-popup"])){old_menu_popup();exit;}

function left_infos_1(){
	$newfrontend=false;
	$users=new usersMenus();
	$sock=new sockets();
	$tpl=new templates();
	$ldap=new clladp();
	if(!$users->AsArticaAdministrator){die("<H2 style='color:red'>permission denied</H2>");}
	$page=CurrentPageName();	
	
	$DisablePurchaseInfo=$sock->GET_INFO("DisablePurchaseInfo");
	if(!is_numeric($DisablePurchaseInfo)){$DisablePurchaseInfo=0;}
	if($DisablePurchaseInfo==0){
		echo $tpl->_ENGINE_parse_body(ParagrapheTEXT("technical-support-32.png",'{ARTICA_P_SUPPORT}','{ARTICA_P_SUPPORT_TEXT}',"javascript:Loadjs('artica.subscription.php');"));
	}
	if(is_file("ressources/logs/status.inform.html")){
		echo $tpl->_ENGINE_parse_body(@file_get_contents("ressources/logs/status.inform.html"));
	}

	if($ldap->ldap_password=="secret"){
		echo ParagrapheTEXT("danger32-user-lock.png",'{MANAGER_DEFAULT_PASSWORD}',
		'{MANAGER_DEFAULT_PASSWORD_TEXT}',"javascript:Loadjs('artica.settings.php?js=yes&bigaccount-interface=yes');",null,330);
	}

		if(!function_exists("browser_detection")){include(dirname(__FILE__).'/ressources/class.browser.detection.inc');}
		$browser=browser_detection();
	
	if($browser=="ie"){
		echo ParagrapheTEXT("no-ie-32.png",'{NOIEPLEASE} !!','{NOIEPLEASE_TEXT}',"javascript:s_PopUp('http://www.mozilla-europe.org/en/firefox/','800',800);",null,330);
	}
	
	if($sock->GET_INFO("EnableNightlyInFrontEnd")==1){NightlyNotifs();}
	
	if($users->VMWARE_HOST){
		if(!$users->VMWARE_TOOLS_INSTALLED){
			echo ParagrapheTEXT("vmware-logo-48.png",'{INSTALL_VMWARE_TOOLS}','{INSTALL_VMWARE_TOOLS_TEXT}',
			"javascript:Loadjs('setup.index.progress.php?product=APP_VMTOOLS&start-install=yes');",null,330);
		}
	}
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	if($users->VIRTUALBOX_HOST){
		if(!$users->APP_VBOXADDINTION_INSTALLED){
			echo ParagrapheTEXT("virtualbox-48.png",'{INSTALL_VBOX_TOOLS}','{INSTALL_VBOX_TOOLS_TEXT}',
			"javascript:Loadjs('setup.index.progress.php?product=APP_VBOXADDITIONS&start-install=yes');",null,330);
		}
	}
	
	if($users->ZARAFA_INSTALLED){
		$q=new mysql();
		$ctc=$q->COUNT_ROWS("zarafa_orphaned","artica_backup");
		if($ctc>0){
			echo ParagrapheTEXT("inbox-error-48.png","$ctc {ORPHANED_STORES}",'{ORPHANED_STORES_TEXT}',
			"javascript:Loadjs('zarafa.orphans.php?js=yes');",null,330);
			}
	}	
	
}

function left_infos_2(){
	$users=new usersMenus();
	if(is_file("ressources/logs/web/debian.update.html"));
	$apt=@file_get_contents("ressources/logs/web/debian.update.html");
	$html=@file_get_contents("ressources/logs/status.global.html");
	$sock=new sockets();
	$DisableWarningCalculation=$sock->GET_INFO("DisableWarningCalculation");
	$DisableAPTNews=$sock->GET_INFO("DisableAPTNews");
	$kavicapserverEnabled=$sock->GET_INFO("kavicapserverEnabled");
	if(!is_numeric($kavicapserverEnabled)){$kavicapserverEnabled=0;}
	if(!is_numeric($DisableWarningCalculation)){$DisableWarningCalculation=0;}
	if(!is_numeric($DisableAPTNews)){$DisableAPTNews=0;}
	
	if($users->KASPERSKY_WEB_APPLIANCE){$kavicapserverEnabled=1;}
	
	if($DisableAPTNews==1){$apt=null;}
	
	
	
	$tpl=new templates();
	$page=CurrentPageName();
	echo $tpl->_ENGINE_parse_body("$apt$html")."
	<div id='artica-meta'></div>
	
	<script>
		function CheckArticaMeta(){
			var kavicapserverEnabled=$kavicapserverEnabled;
			LoadAjax('artica-meta','$page?artica-meta=yes');
			
			if(kavicapserverEnabled==1){
				if(document.getElementById('kav4proxyGraphs')){LoadAjax('kav4proxyGraphs','admin.index.kav4proxy.php');}
			}
		}
	
	
		function CheckSquid(){
			if(document.getElementById('page-index-squid-status')){
				LoadAjax('page-index-squid-status','squid.index.php?page-index-squid-status=yes');
			}
		
		}
	
		CheckArticaMeta();
	</script>
	
	";
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?ForceRefreshLeft=yes');	
	
}

function NightlyNotifs(){
	$sock=new sockets();
	$ini=new Bs_IniHandler("ressources/index.ini");
	$nightly=$ini->get("NEXT","artica-nightly");
	$version=$sock->getFrameWork("cmd.php?uri=artica_version");
	
	$nightlybin=str_replace('.','',$nightly);
	$versionbin=str_replace('.','',$version);
	if($versionbin==0){return;}
	if($nightlybin==0){return;}
	
	if($nightlybin>$versionbin){
		echo ParagrapheTEXT("32-infos.png","{NEW_NIGHTLYBUILD}: $nightly",'{NEW_NIGHTLYBUILD_TEXT}',
			"javascript:Loadjs('artica.update.php?js=yes');",null,330);
		
	}
}

function old_menus_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->javascript_parse_text("{advanced_options}");
	$html="YahooSetupControl(189,'$page?old-menu-popup=yes','$title')";
	echo $html;
	
}

function old_menu_popup(){
	echo"<div id='old-menus-popup'></div>
	<script>LoadAjax('old-menus-popup','admin.tabs.php?left-menus=yes');</script>";
	 
}
