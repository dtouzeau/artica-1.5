<?php
	$GLOBALS["ICON_FAMILY"]="ANTISPAM";
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	writelogs($_SERVER['QUERY_STRING'],null,__FILE__);
	if(isset($_GET["status"])){page_status();exit;}
	if(isset($_GET["section"])){main_page();exit;}
	if(isset($_GET["script"])){main_select_scripts();exit;}
	if(isset($_GET["popup"])){main_select_popups();exit;}
	if(isset($_GET["TrustLocalHost"])){trustlocal_save();exit;}
	if(isset($_GET["ip_from"])){localnetwork_save();exit;}
	if(isset($_GET["ip_delete"])){localnetwork_del();exit;}
	if(isset($_GET["D_EXPLAIN"])){filterbehavior_explain();exit;}
	if(isset($_GET["INI_SAVE"])){INI_SAVE();exit;}
	if(isset($_GET["saveToServer"])){apply_popup2();exit;}
	if(isset($_GET["add_exts"])){filterextension_add();exit;}
	if(isset($_GET["del_ext"])){filterextension_del();exit;}
	if(isset($_GET["EnableAmavisBackup"])){backup_save();exit;}
	if(isset($_GET["ajax"])){ajax_js();exit;}
	if(isset($_GET["ajax-pop"])){ajax_popup();exit;}
	if(isset($_GET["sanesecurity-js"])){sanesecurity_js();exit;}
	if(isset($_GET["sanesecurity-popup"])){sanesecurity_popup();exit;}
	if(isset($_GET["sanesecurity_enable"])){sanesecurity_enable();exit;}
	if(isset($_GET["altermime-js"])){altermime_js();exit;}
	if(isset($_GET["altermime-popup"])){altermime_popup();exit;}
	if(isset($_GET["altermime_enable"])){altermime_enable();exit;}
	if(isset($_GET["altermime-disclaimer"])){altermime_disclaimer();exit;}
	if(isset($_GET["altermime-tinymce"])){altermime_disclaimer();exit;}
	if(isset($_POST["AlterMimeHTMLDisclaimer"])){altermime_disclaimer();exit();}
	if(isset($_GET["log_level"])){log_level_save();exit;}
	
	if(isset($_GET["hooking-js"])){hooking_js();exit;}
	if(isset($_GET["hooking-popup"])){hooking_popup();exit;}
	if(isset($_GET["EnableAmavisInMasterCF"])){hooking_save();exit;}
	
	if(isset($_GET["banned_extensions_include_local_net"])){banned_extensions_include_local_net();exit;}
	if(isset($_GET["AmavisMemoryInRAM"])){filterbehavior_performances_save();exit;}
	
	
page();

function ajax_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_AMAVIS}");
	$datas=file_get_contents('js/amavis.js');
	$page=CurrentPageName();
	
	$start="YahooWin0(777,'$page?ajax-pop=yes','$title');";
	
	if(isset($_GET["in-front-ajax"])){
		$start="$('#BodyContent').load('$page?ajax-pop=yes');";	
	}
	
	$html="
	$start
	$datas
	";
	
	echo $html;
	}
	
	
	
function sanesecurity_js(){
	$html="
	
	YahooWin(550,'amavis.index.php?sanesecurity-popup=yes','SaneSecurity');
	
var x_sanesecurity_enable= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
    YahooWin(550,'amavis.index.php?sanesecurity-popup=yes','SaneSecurity');    
	}	
	
	function sanesecurity_enable(){
	var XHR = new XHRConnection();
	XHR.appendData('sanesecurity_enable',document.getElementById('sanesecurity_enable').value);
	document.getElementById('sanesecuid').innerHTML='<img src=\"img/wait_verybig.gif\">';
	XHR.sendAndLoad('amavis.index.php', 'GET',x_sanesecurity_enable);	
	
	}
	";
	echo $html;	
	}
	
function altermime_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{disclaimer}');
	$page=CurrentPageName();
	$html="
	YahooWin(550,'amavis.index.php?altermime-popup=yes','$title');
	
var x_altermime_enable= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
    YahooWin(550,'amavis.index.php?altermime-popup=yes','$title');  
	}	
	
	function altermime_enable(){
	var XHR = new XHRConnection();
	XHR.appendData('altermime_enable',document.getElementById('altermime_enable').value);
	document.getElementById('sanesecuid').innerHTML='<img src=\"img/wait_verybig.gif\">';
	XHR.sendAndLoad('amavis.index.php', 'GET',x_altermime_enable);	
	
	}
	
	function LoadDisclaimer(){
	 s_PopUp('$page?altermime-disclaimer',700,600);
	}
	
	function LoadTinyMce(){
		Loadjs('js/tiny_mce/tiny_mce.js');
		setTimeout(\"LoadTinyMce2()\",1000);
	
	}
	
	function LoadTinyMce2(){
		Loadjs('amavis.index.php?altermime-tinymce=yes');
	}
	
	";
	echo $html;		
	
	
}

function hooking_js(){
$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{postfix_hooking}');
	$page=CurrentPageName();
	$html="
	YahooWin(550,'amavis.index.php?hooking-popup=yes','$title');
	
var x_EnableAmavisInMasterCFSave= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
   YahooWin(550,'amavis.index.php?hooking-popup=yes','$title'); 
	}		
	
	function EnableAmavisInMasterCFSave(){
		var EnableAmavisInMasterCF=document.getElementById('EnableAmavisInMasterCF').value;
		var XHR = new XHRConnection();
		XHR.appendData('EnableAmavisInMasterCF',document.getElementById('EnableAmavisInMasterCF').value);
		document.getElementById('hookdiv').innerHTML='<img src=\"img/wait_verybig.gif\">';
		XHR.sendAndLoad('amavis.index.php', 'GET',x_EnableAmavisInMasterCFSave);	
	}	

";
	echo $html;			
	
	
}

function hooking_popup(){
	
	
$array=array(0=>"{postfix_beforequeue}",1=>"{postfix_afterqueue}");
$amavis=new amavis();	
$html="
<dov id='hookdiv'>
	<div class=explain>{postfix_hooking_text}</div>
	<table style='widht:100%' class=table_form>
	<tr>
	<td class=legend>{select}:</td>
	<td>". Field_array_Hash($array,EnableAmavisInMasterCF,$amavis->EnableAmavisInMasterCF)."</td>
	</tr>
	<tr>
	<td colspan=2 align='right'>".button("{apply}","EnableAmavisInMasterCFSave()")."</td>
	</tr>	
	<tr>
	<td colspan=2><div class=explain>{postfix_beforequeue_text}</div></td>
	</tr>
<tr>
	<td colspan=2 align='right'><hr></td>
	</tr>		
	<tr>
	<td colspan=2><div class=explain>{postfix_afterqueue_text}</div></td>
	</tr>		
	</table>
	</div>
	";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function hooking_save(){
	
	$amavis=new amavis();
	$amavis->EnableAmavisInMasterCF=$_GET["EnableAmavisInMasterCF"];	
	$amavis->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();	
	$amavis->SaveToServer();
	
	
}

function altermime_enable(){
	$sock=new sockets();
	$sock->SET_INFO("EnableAlterMime",$_GET["altermime_enable"]);
	}

function altermime_disclaimer(){
	
	
	$sock=new sockets();
	if(isset($_POST["AlterMimeHTMLDisclaimer"])){
		writelogs("Saving disclaimer size=". strlen($_POST["AlterMimeHTMLDisclaimer"]),__FUNCTION__,__FILE__);
		$_POST["AlterMimeHTMLDisclaimer"]=str_replace('href="\&quot;',"href=\"",$_POST["AlterMimeHTMLDisclaimer"]);
		$_POST["AlterMimeHTMLDisclaimer"]=str_replace('\&quot;','"',$_POST["AlterMimeHTMLDisclaimer"]);
		
		
		
		$sock->SaveConfigFile(stripslashes($_POST["AlterMimeHTMLDisclaimer"]),"AlterMimeHTMLDisclaimer");
		$AlterMimeHTMLDisclaimer=$_POST["AlterMimeHTMLDisclaimer"];
	}else{
		$AlterMimeHTMLDisclaimer=$sock->GET_INFO("AlterMimeHTMLDisclaimer");
	}
	
	$DisclaimerExample= "<p style=\"font-size:12px\"><i>This email and its attachments may be confidential and are intended solely for the use of the individual to whom it is addressed.<br>
		 Any views or opinions expressed are solely those of the author and do not necessarily represent those of &laquo;[business name]&raquo;.<br>
		If you are not the intended recipient of this email and its attachments, you must take no action based upon them, nor must you copy or show them to anyone.<br>
		<br><br>Please contact the sender if you believe you have received this email in error.</i></p>";
		
	
	if($AlterMimeHTMLDisclaimer==null){
		$AlterMimeHTMLDisclaimer=$DisclaimerExample;
		$sock->SaveConfigFile($DisclaimerExample,"AlterMimeHTMLDisclaimer");
	}
	
	$tpl=new templates();
	$tiny=TinyMce('AlterMimeHTMLDisclaimer',$AlterMimeHTMLDisclaimer);
	$page=CurrentPageName();
	
	$html="
	<H1>{edit_disclaimer}</H1>
	<p class=caption>{edit_disclaimer_text}</p>
	<form name='tinymcedisclaimer' method='post' action=\"$page\">
	$tiny
	</form>
	
	
	";
	$tpl=new template_users('{edit_disclaimer}',$html,0,1,1);
echo $tpl->web_page;	
}


function altermime_popup(){
	
	$users=new usersMenus();
	if(!$users->ALTERMIME_INSTALLED){echo altermime_failed();exit;}
	$sock=new sockets();
	$EnableAlterMime=$sock->GET_INFO('EnableAlterMime');
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	if($EnableArticaSMTPFilter<>1){echo altermime_articafilter_failed();exit;}
	$level=Paragraphe_switch_img('{activate_disclaimer}',"{altermime_switch}","altermime_enable",$EnableAlterMime,null,280);
	$tinymce=Paragraphe('icon-html-64.png','{edit_disclaimer}','{edit_disclaimer_text}',"javascript:LoadDisclaimer()");
	$settings=Paragraphe('64-settings.png','{parameters}','{edit_paremeters}',"javascript:Loadjs('altermime.php')");
	
	
	
	$tpl=new templates();
	$html="
	<div class=explain>{disclaimer_explain}</div>
	<table style='widht:100%'>
	<tr>
		<td valign='top'>
			<div id='sanesecuid'>
			$level
			</div>
			<div style='text-align:right'><hr>". button("{apply}","altermime_enable()")."
			</div>
			<hr>
		</td>
		<td valign='top'>
		$settings
		
		$tinymce
		</td>
	</tr>";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function altermime_failed(){
	
	$level=Paragraphe_switch_disable('{APP_ALTERMIME}',"{APP_ALTERMIME_NOT_INSTALLED}","{APP_ALTERMIME_NOT_INSTALLED}");
	$tpl=new templates();
	$html="<H1>{disclaimer}</H1>
	<p class=caption>{disclaimer_text}</p>
	<table style='widht:100%'>
	<tr>
		<td valign='top'>
			<div id='sanesecuid'>
			$level
			</div>
		</td>
		<td valign='top'>".Paragraphe('add-remove-64.png','{application_setup}','{application_setup_txt}',"javascript:Loadjs('setup.index.progress.php?product=APP_ALTERMIME&start-install=yes')")."</td>
	</tr>";
	echo $tpl->_ENGINE_parse_body($html);	
}
function altermime_articafilter_failed(){
	$level=Paragraphe_switch_disable('{APP_ALTERMIME}',"{APP_ARTICA_FILTER_NOT_ENABLED}","{APP_ARTICA_FILTER_NOT_ENABLED}");
	$activate=Paragraphe('64-folder-install.png','{AS_ACTIVATE}','{AS_ACTIVATE_TEXT}',"javascript:Loadjs('postfix.index.php?script=antispam')",null,210,null,0,true);
$tpl=new templates();
	$html="<H1>{disclaimer}</H1>
	<p class=caption>{disclaimer_text}</p>
	<table style='widht:100%'>
	<tr>
		<td valign='top'>
			<div id='sanesecuid'>
			$level
			</div>
		</td>
		<td valign='top'>$activate</td>
	</tr>";
	echo $tpl->_ENGINE_parse_body($html);	
}

function sanesecurity_popup(){
	
	$amavis=new amavis();
	$level=Paragraphe_switch_img('{enable_sanesecurity}',"{sanesecurity_switch}","sanesecurity_enable",$amavis->EnableScanSecurity);
	
	$tpl=new templates();
	$html="<H1>SaneSecurity Addons</H1>
	<p class=caption>{sanesecurity_explain}</p>
	<table style='widht:100%'>
	<tr>
		<td valign='top'>
			<div id='sanesecuid'>
			$level
			</div>
		</td>
		<td valign='top'>
			<hr>". button("{apply}","sanesecurity_enable()")."
		</td>
	</tr>
	</table>";
	echo $tpl->_ENGINE_parse_body($html);
	}
function sanesecurity_enable(){
	$amavis=new amavis();
	$amavis->EnableScanSecurity=$_GET["sanesecurity_enable"];
	$amavis->Save();
	$amavis->SaveToServer();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("SaneSecurity Addons: {success}\n");
	
}
	

function ajax_popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableAmavisInMasterCF=$sock->GET_INFO("EnableAmavisInMasterCF");
	if(!is_numeric($EnableAmavisInMasterCF)){$EnableAmavisInMasterCF=0;}
	$array["perfs"]='{performances}';
	$array["global-settings"]='{global_settings}';
	$array["plugins"]='{plugins}';
	if($EnableAmavisInMasterCF==1){
		$array["bypass"]='{bypass}';
	}
	$array["events"]='{daemon_events}';
	$array["config-file"]='{config_file}';
	$array["global-status"]='{status}';
	



	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="perfs"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"amavis.daemon.status.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="bypass"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"amavis.bypass.php\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		if($num=="plugins"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"amavis.daemon.plugins.php\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num&section=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_amavis style='width:750px;height:520px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_amavis').tabs();
			
			
			});
		</script>";		

	
}


function page_status($noecho=0){
			$ini=new Bs_IniHandler();
			$sock=new sockets();
			$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?amavis-get-status=yes')));
			$status_amavis=DAEMON_STATUS_ROUND("AMAVISD",$ini,null);
			$status_amavismilter=DAEMON_STATUS_ROUND("AMAVISD_MILTER",$ini,null);
			$status_spamassassin=DAEMON_STATUS_ROUND("SPAMASSASSIN",$ini,null);
			$status_clamav=DAEMON_STATUS_ROUND("CLAMAV",$ini,null);
			

	$tpl=new templates();
	
	$html="<table style='width:100%'>
	<tr>
	<td valign='top' width=50% valign='top'>$status_amavis<br>$status_amavismilter</td>
	<td valign='top' width=50% valign='top'>$status_spamassassin<br>$status_clamav</td>
	</tr>
	</table>";
	
	if($noecho==1){return  $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);		
		
	}
	

	
	
function main_page(){
	$tpl=new templates();
	switch ($_GET["section"]) {

		case "smtp-domain-rule":echo main_domain_rule_single();break;
		case "events":echo main_events();break;
		case "config-file":echo main_config_amavisfile();break;
		case "global-settings":echo main_settings();break;
		case "events":echo main_events();break;
		case "global-status":echo page_status(1);break;
		default:echo main_settings();break;
	}
	
	
	
}

function main_config_amavisfile(){
	$sock=new sockets();
	$conf=base64_decode($sock->getFrameWork("cmd.php?amavis-configuration-file=yes"));
	$tbl=explode("\n",$conf);
	$html="<div style='background-color:white;width:100%;height:600px;overflow:auto;font-size:11px;'>
	<table style='width:100%'>
	
	";
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$ligne=htmlentities($ligne);
		$ligne=str_replace(' ',"&nbsp;",$ligne);
		if(preg_match("#^\##",$ligne)){continue;}
		$html=$html . "<tr>
		<td width=1%><strong>$num.</strong></td>
		<td width=99%>$ligne</td>
		</tr>";
		
	}
	
	$html=$html . "</table></div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function main_events(){
	
	$page=CurrentPageName();
	$amavis=new amavis();
	$sock=new sockets();
	$users=new usersMenus();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?amavis-get-events&maillog=$users->maillog_path")));
	$tbl=array_reverse ($tbl, TRUE);
	$AmavisDebugSpamassassin=$sock->GET_INFO("AmavisDebugSpamassassin");
	
	$html="<table style='width:99%' class=table_form>";
		$count=0;
		while (list ($num, $val) = each ($tbl) ){
			
			if(trim($val)==null){continue;}
			$count=$count+1;
			if($count>300){break;}
			$color="black";
			if(preg_match('#^([A-Za-z]+)\s+([0-9:]+)\s+([0-9:]+)\s+(.+?)\s+(.+?)\[([0-9]+)\]:(.+)#',$val,$re)){
				$re[7]=htmlentities($re[7]);
				
				if(preg_match("#No decoder#",$re[7])){
					$color="red";
				}
				$style="style='padding-bottom:2px;border-bottom:1px solid #CCCCCC;color:$color'";
				$html=$html . "
			<tr " . CellRollOver().">
			<td valign='top' nowrap $style>{$re[1]} {$re[2]} {$re[3]}</td>
			<td valign='top' $style>{$re[6]}</td>
			<td valign='top' $style>{$re[7]}</td>
			</tr>";
			}else{
			$html=$html . "
			<tr" . CellRollOver().">
			<td valign='top' colspan=3 $style>$val</code>
			</td>
			</tr>";
			}
			

		}
		
	for($i=0;$i<6;$i++){
		$hash[$i]="{log_level} 0$i";
		
	}
	
	$html="
	<form name='ffmlogs'>
	<table style='width:49%' class=form align='right'>
	
	<tr>
		<td>". imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_amavis')")."</td>
		<td class=legend nowrap>{sa_debug}:</td>
		<td>". Field_checkbox("AmavisDebugSpamassassin",1,$AmavisDebugSpamassassin)."</td>
		<td class=legend nowrap>{log_level}</td>
		<td>" . Field_array_Hash($hash,'log_level',$amavis->main_array["BEHAVIORS"]["log_level"])."</td>
		<td width=1%><input type='button' OnClick=\"javascript:SaveAmavisEventsParams();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</form>	
	<div id='amavisevents' style='width:100%;height:500px;overflow:auto'>$html</table></div>
	
	<script>
	var x_SaveAmavisEventsParams= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
	   	  RefreshTab('main_config_amavis');  
		}		
	
		function SaveAmavisEventsParams(){
			var XHR = new XHRConnection();
			XHR.appendData('log_level',document.getElementById('log_level').value);
			if(document.getElementById('AmavisDebugSpamassassin').checked){
				XHR.appendData('AmavisDebugSpamassassin',1);
			}else{
				XHR.appendData('AmavisDebugSpamassassin',0);
			}
			document.getElementById('amavisevents').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_SaveAmavisEventsParams);	

		}	
</script>		
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function main_settings($noecho=0){
	$amavis=new amavis();
	$page=CurrentPageName();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$array=array("D_PASS"=>"{D_PASS}","D_DISCARD"=>"{D_DISCARD}","D_BOUNCE"=>"{D_BOUNCE}");
	
	$kas3=Paragraphe('folder-caterpillar.png','{APP_KAS3}','{KAS3_TEXT}','javascript:Loadjs("kas.group.rules.php?ajax=yes")',null,210,null,0,true);
	$kas3_bad=Paragraphe('folder-caterpillar-grey.png','{APP_KAS3}','{feature_disabled}',null,null,210,null,0,true);
	
	if($users->kas_installed){
		if($users->KasxFilterEnabled){
			$kas=$kas3;
		}else{
			$kas=$kas3_bad;
		}
		
	}
	
	
	$trustlocal=Paragraphe("network-connection2.png",'{trust_local}','{trust_local_text}',"javascript:Loadjs('$page?script=trustlocal')",null,210,100);
	$localNetwork=Paragraphe("64-ip-settings.png",'{local_network}','{local_network_text}',"javascript:Loadjs('$page?script=localnetwork')",null,210,100);
	$filterbhavior=Paragraphe("64-milter-behavior.png",'{filter_behavior}','{filter_behavior_text}',"javascript:Loadjs('$page?script=filterbehavior')",null,210,100);
	$notification=Paragraphe("mail4-64.png",'{smtp_notification}','{notification_text}',"javascript:Loadjs('$page?script=notification')",null,210,100);
	$spamassassin=Paragraphe("folder-64-spamassassin-grey.png",'{spamassassin}','{feature_not_installed}',null,null,210,100);
	$whitelist=Paragraphe("folder-64-spamassassin-grey.png",'{spamassassin}','{feature_not_installed}',null,null,210,100);
	
	$pieces_jointes=Paragraphe("pieces-jointes.png",'{filter_extension}','{filter_extension_text}',"javascript:Loadjs('$page?script=filterextension')",null,210,100);
	
	if($users->spamassassin_installed){
	$spamassassin=Paragraphe("folder-64-spamassassin.png",'{spamassassin}','{spamassassin_text}',"javascript:Loadjs('$page?script=spamassassin')",null,210,100);
	}
	
	//$apply=Paragraphe('system-64.png','{apply config}','{APPLY_SETTINGS_AMAVIS}',"javascript:Loadjs('$page?script=apply')",'APPLY_SETTINGS_AMAVIS',210,100);
	$apply=Buildicon64("DEF_ICO_AMAVIS_RESTART",210,100);
	
	
	
	$prepost=Paragraphe("folder-equerre-64.png",'{postfix_hooking}','{postfix_hooking_text}',"javascript:Loadjs('$page?hooking-js=yes')",'postfix_hooking_text',210,100);
	
	$spf=Paragraphe("spf-logo-64.png",'{APP_SPF}','{APP_SPF_TINY_TEXT}',"javascript:Loadjs('spamassassin.spf.php')",'APP_SPF_TINY_TEXT',210,100);
	
	$users=new usersMenus();
	if($users->CLAMD_INSTALLED){
		$sanesecurity=Paragraphe('folder-64-denywebistes.png','SaneSecurity signatures','{sanesecurity_text}',"javascript:Loadjs('$page?sanesecurity-js=yes')",'sanesecurity_text',210,100);	
	}
	
	$html="
	<H5>{global_settings}</H5>
	<table>
			<tr>
			<td valign='top' >$trustlocal</td>
			<td valign='top' >$filterbhavior</td>
			<td valign='top' >$apply</td>
			</tr>
			<tr>
			<td valign='top' >$pieces_jointes</td>
			<td valign='top'>$spf</td>
			<td valign='top' >$spamassassin</td>
			</tr>			
			<td valign='top'>$prepost</td>
			<td valign='top' >$notification</td>
			<td valign='top'>$sanesecurity</td>			
			</tr>
			
	</table>
	

	";
	
$tpl=new templates();
	if($noecho==0){echo $tpl->_ENGINE_parse_body($html);}else{return $tpl->_ENGINE_parse_body($html,'postfix.index.php');} 
}

function main_select_scripts(){
	
	switch ($_GET["script"]) {
		case "trustlocal":echo trustlocal_js();break;
		case "localnetwork":echo localnetwork_js();break;
		case "filterbehavior":echo filterbehavior_js();break;
		case "notification":echo notification_js();break;
		case "spamassassin":echo spamassassin_js();break;
		case "filterextension":echo filterextension_js();break;
		case "apply":echo apply_js();break;
		case "backup":echo backup_js();break;
	
		default:
			break;
	}
	
}

function main_select_popups(){
		switch ($_GET["popup"]) {
		case "trustlocal":echo trustlocal_popup();break;
		case "localnetwork":echo localnetwork_popup();break;
		case "filterbehavior":echo filterbehavior_popup();break;
		case "filterbehavior2":echo filterbehavior2_popup();break;
		case "notification":echo notification_popup();break;
		case "notification2":echo notification_index();break;
		case "templates":echo notification_templates();break;
		
		case "spamassassin":echo spamassassin_popup();break;
		case "apply":echo apply_popup();break;
		case "filterextension":echo filterextension_popup();break;
		case "backup":backup_popup();break;
		default:
			break;
	}
	
}

function trustlocal_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{trust_local}");
	$html="YahooWin(550,'$page?popup=trustlocal','$title');
	
	var x_ApplyTrustLocalHost=function(obj){
      var tempvalue=obj.responseText;
      alert(tempvalue);
      YahooWin(550,'$page?popup=trustlocal','$title');
	  }
	
	function ApplyTrustLocalHost(){
	  var XHR = new XHRConnection();
      XHR.appendData('TrustLocalHost',document.getElementById('TrustLocalHost').value);
      document.getElementById('img_TrustLocalHost').src='img/wait_verybig.gif';
      XHR.sendAndLoad('$page', 'GET',x_ApplyTrustLocalHost);
	 }
	";
	return $html;
	}
	
function backup_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{backupemail_behavior}","postfix.index.php");
	$html="YahooWin(550,'$page?popup=backup','$title');
	
	var x_ApplyAmavisBackup=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
      YahooWin(550,'$page?popup=backup','$title');
	  }
	
	function ApplyAmavisBackup(){
	  var XHR = new XHRConnection();
      XHR.appendData('EnableAmavisBackup',document.getElementById('EnableAmavisBackup').value);
      document.getElementById('img_EnableAmavisBackup').src='img/wait_verybig.gif';
      XHR.sendAndLoad('$page', 'GET',x_ApplyAmavisBackup);
	 }	
	
	
	";
	return $html;
}


function backup_save(){
	
	$sock=new sockets();
	$EnableAmavisBackup=$_GET["EnableAmavisBackup"];
	if($_GET["EnableAmavisBackup"]==1){
		$sock->SET_INFO("MailArchiverEnabled",0);
	}
	$sock->SET_INFO("EnableAmavisBackup",$EnableAmavisBackup);

	
}


function backup_popup(){
	
$sock=new sockets();
$milter=Paragraphe_switch_img('{enable_backup}','{enable_backup_amavis_text}','EnableAmavisBackup',$sock->GET_INFO("EnableAmavisBackup"),'{enable_disable}',290);

	$html="
	<table style='width:100%'>
	<tr>

	<td valign='top' width=50%>
		$milter
	</td>
	<td valign='top' width=50% style='margin:4px'>
		" . applysettingsGeneral('apply','ApplyAmavisBackup()','apply_backup_behavior')."
	
	</td>	
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');		
	
}


function trustlocal_popup(){
	$amavis=new amavis();
	$loopback=Paragraphe_switch_img('{trust_local}','{trust_local_explain}','TrustLocalHost',$amavis->main_array["NETWORK"]["TrustLocalHost"],'{enable_disable}',290);
	$html="
	<div class=explain>{trust_local_text}</p>
	<table style='width:100%'>
	<tr>

	<td valign='top' width=50%>
		$loopback
	</td>
	<td valign='top' width=50% style='margin:4px'>
		" . applysettingsGeneral('apply','ApplyTrustLocalHost()','save_policies')."
	
	</td>	
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
function trustlocal_save(){
	$amavis=new amavis();
	$amavis->main_array["NETWORK"]["TrustLocalHost"]=$_GET["TrustLocalHost"];
	$amavis->Save();
	}
function log_level_save(){
	$amavis=new amavis();
	$sock=new sockets();
	$sock->SET_INFO("AmavisDebugSpamassassin",$_GET["AmavisDebugSpamassassin"]);
	$amavis->main_array["BEHAVIORS"]["log_level"]=$_GET["log_level"];
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{log_level} -> '.$_GET["log_level"]."\n" );
	$amavis->Save();
}

function filterbehavior_explain(){
	$tpl=new templates();
	if($_GET["D_EXPLAIN"]==null){return null;}
	
	echo "<div class=explain>".$tpl->_ENGINE_parse_body("{{$_GET["D_EXPLAIN"]}_EXP}")."</div>";
	
}

function filterbehavior_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{filter_behavior}");
	$html="

	function LoadAmavisFilterBehavior(){
		YahooWin(750,'$page?popup=filterbehavior','$title');
	}

	var x_d_exp=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('D_EXPLAIN').innerHTML=tempvalue;
      }
	
	 function load_d_exp(e){
	 	var XHR = new XHRConnection();
	 	XHR.appendData('D_EXPLAIN',e.value);
		XHR.sendAndLoad('$page', 'GET',x_d_exp);
	}
	

	
	
	 LoadAmavisFilterBehavior();
	
	";
	
	return $html;	
}

function filterbehavior_performances_save(){
	$AmavisMemoryInRAM=$_GET["AmavisMemoryInRAM"];
	if($AmavisMemoryInRAM>0){
		if($AmavisMemoryInRAM<128){$AmavisMemoryInRAM=128;}
	}
	
	$sock=new sockets();
	$sock->SET_INFO('AmavisMemoryInRAM',$AmavisMemoryInRAM);
	$amavis=new amavis();
	$amavis->main_array["BEHAVIORS"]["max_servers"]=$_GET["max_servers"];
	$amavis->main_array["BEHAVIORS"]["max_requests"]=$_GET["max_requests"];
	$amavis->main_array["BEHAVIORS"]["child_timeout"]=$_GET["child_timeout"];
	$amavis->Save();
	$tpl=new templates();
	echo $tpl->_parse_body("{ERROR_NEED_TO_SAVEAPPLY}");
	
	
}


function spamassassin_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{spamassassin}");
	$html="
	YahooWin(550,'$page?popup=spamassassin','$title');
	
var x_ParseForm_Spamassassin=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
      YahooWin(550,'$page?popup=spamassassin','$title');
      }		
	
	
	";
	return $html;		
}

function apply_js(){
$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{apply}");
	$html="
	YahooWin(550,'$page?popup=apply','$title');	
	setTimeout(\"SaveAPPLY()\",1000);
	
var x_SaveAPPLY=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('apply_results').innerHTML=tempvalue;
      }	
	
	function SaveAPPLY(){
		document.getElementById('apply_results').innerHTML='<img src=img/wait.gif>';
		var XHR = new XHRConnection();
	 	XHR.appendData('saveToServer','yes');
		XHR.sendAndLoad('$page', 'GET',x_SaveAPPLY);
	}
	
	
	";
	return $html;	
}


function notification_js(){
$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{notification}");
	$html="

	YahooWin(650,'$page?popup=notification','$title');
	";
	return $html;	
}
	

function spamassassin_popup(){
$amavis=new amavis();
$users=new usersMenus();
$tpl=new templates();

$ipcountry=Paragraphe('folder-64-denywebistes-grey.png','{deny_countries}','{error_feature_not_installed}<br>RelayCountry');



if($users->spamassassin_ipcountry){
	$ipcountry=Paragraphe('folder-64-denywebistes.png','{deny_countries}','{deny_countries_text_spam}',"javascript:Loadjs('spamassassin.RelayCountry.php')");
	
}


$page=CurrentPageName();


$form_js="ParseForm('FFM_filterbehavior_popup','$page',false,false,false,'amavisspamassassin',null,x_ParseForm_Spamassassin);";
$sa_quarantine_cutoff_level=$tpl->_ENGINE_parse_body('{sa_quarantine_cutoff_level}','spamassassin.index.php');
$sa_tag3_level_defltl=$tpl->_ENGINE_parse_body('{sa_tag3_level_deflt}','spamassassin.index.php');


if(strlen($sa_quarantine_cutoff_level)>70){
	$sa_quarantine_cutoff_level=texttooltip(substr($sa_quarantine_cutoff_level,0,67)."..:",$sa_quarantine_cutoff_level,null,null,1);
}

if(strlen($sa_tag3_level_defltl)>70){
	$sa_tag3_level_defltl=texttooltip(substr($sa_tag3_level_defltl,0,67)."..:",$sa_tag3_level_defltl,null,null,1);
}

$html="
<div id='amavisspamassassin'>
	<form name='FFM_filterbehavior_popup'>
	<input type='hidden' name='INI_SAVE' value='BEHAVIORS' id='INI_SAVE'>
	<div class=explain>{spamassassin_text}</div>
	<table style='width:100%'>	
		<tr>
			<td class=legend nowrap>{replicate_all_domains}:</td>
			<td width=1%>" . Field_numeric_checkbox_img('replicate_conf_all_domains',$amavis->main_array["BEHAVIORS"]["replicate_conf_all_domains"],'{replicate_all_domains}') . "</td>
			<td>&nbsp;</td>			
		</tr>	
		<tr>
			<td class=legend nowrap>{sa_tag2_level_deflt}:</td>
			<td width=1%>". Field_text('sa_tag2_level_deflt',$amavis->main_array["BEHAVIORS"]["sa_tag2_level_deflt"],'width:90px')."</td>
			<td>" . Field_numeric_checkbox_img('spam_quarantine_spammy',$amavis->EnableQuarantineSpammy,'{spam_quarantine_spammy}') . "</td>			
		</tr>
		<tr>
			<td class=legend nowrap>$sa_tag3_level_defltl</td>
			<td width=1%>". Field_text('sa_tag3_level_deflt',$amavis->main_array["BEHAVIORS"]["sa_tag3_level_deflt"],'width:90px')."</td>
			<td>" . Field_numeric_checkbox_img('spam_quarantine_spammy2',$amavis->EnableQuarantineSpammy2,'{spam_quarantine_spammy}') . "</td>
		</tr>	
		<tr>
			<td class=legend nowrap>{sa_kill_level_deflt}:</td>
			<td width=1%>". Field_text('sa_kill_level_deflt',$amavis->main_array["BEHAVIORS"]["sa_kill_level_deflt"],'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>	
		</tr>		
		<tr><td colspan=3><hr></td></tR>
		<tr>
			<td class=legend nowrap>{sa_dsn_cutoff_level}:</td>
			<td width=1%>". Field_text('sa_dsn_cutoff_level',$amavis->main_array["BEHAVIORS"]["sa_dsn_cutoff_level"],'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class=legend nowrap>$sa_quarantine_cutoff_level</td>
			<td width=1%>". Field_text('sa_quarantine_cutoff_level',$amavis->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"],'width:90px')."</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<hr>
<table style='width:100%'>	
		<tr>
			<td class=legend nowrap>{spam_subject_tag_maps}:</td>
			<td width=1%>" . Field_yesno_checkbox_img('spam_subject_tag_maps_enable',$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"],'{enable_disable}')."</td>
			<td width=1%>". Field_text('spam_subject_tag_maps',$amavis->main_array["BEHAVIORS"]["spam_subject_tag_maps"],'width:190px')."</td>
			<td class=legend nowrap>{score}:</td>
			<td>" . Field_text("sa_tag_level_deflt",$amavis->main_array["BEHAVIORS"]["sa_tag_level_deflt"],'width:33px')."</td>
		</tr>	
		<tr>
			<td class=legend nowrap>{spam_subject_tag2_maps}:</td>
			<td>&nbsp;</td>
			<td width=1%>". Field_text('spam_subject_tag2_maps',$amavis->main_array["BEHAVIORS"]["spam_subject_tag2_maps"],'width:190px')."</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
			<td class=legend nowrap>{sa_timeout}:</td>
			<td>&nbsp;</td>
			<td width=1%>". Field_text('sa_timeout',$amavis->main_array["BEHAVIORS"]["sa_timeout"],'width:60px')."&nbsp;<span style='font-size:13px'>{seconds}</span></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>		
		
	<tr>
		<td colspan=5 align='right'>
		<hr>
		". button("{apply}","$form_js")."
		</td>
	</tr>			
	</table>
	</form>
	<table style='width:100%'>
	<tr>
		<td valign='top'>".Paragraphe('64-learning.png','{salearnschedule}','{salearnschedule_text}',"javascript:Loadjs('spamassassin.index.php?salearn-schedule-js=yes')")."</td>
		<td valign='top'>$ipcountry</td>
	</tr>
	</table>
	</div>";

	
	
	
	echo $tpl->_ENGINE_parse_body($html,'spamassassin.index.php');	
	}

function notification_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["notification2"]=$tpl->_ENGINE_parse_body('{notification}');
	$array["templates"]=$tpl->_ENGINE_parse_body('{templates}');


	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?popup=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_amavis_notifications style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_amavis_notifications').tabs();
			
			
			});
		</script>";		
	
	
}

function notification_templates(){
	$tr[]=Paragraphe("64-templates.png","{amavis-template-dsn}","{amavis-template-dsn}","javascript:Loadjs('amavis.templates.php?type=template-dsn')");
	$tr[]=Paragraphe("64-templates.png","{template-spam-admin}","{template-spam-admin}","javascript:Loadjs('amavis.templates.php?type=template-spam-admin')");
	$tr[]=Paragraphe("64-templates.png","{template-spam-sender}","{template-spam-sender}","javascript:Loadjs('amavis.templates.php?type=template-spam-sender')");
	$tr[]=Paragraphe("64-templates.png","{template-virus-admin}","{template-virus-admin}","javascript:Loadjs('amavis.templates.php?type=template-virus-admin')");
	$tr[]=Paragraphe("64-templates.png","{template-virus-recipient}","{template-virus-recipient}","javascript:Loadjs('amavis.templates.php?type=template-virus-recipient')");
	$tr[]=Paragraphe("64-templates.png","{template-virus-sender}","{template-virus-sender}","javascript:Loadjs('amavis.templates.php?type=template-virus-sender')");
	
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

echo @implode("\n",$tables);
		
}


function notification_index(){
$amavis=new amavis();
$page=CurrentPageName();
$tpl=new templates();
if($amavis->main_array["BEHAVIORS"]["virus_admin"]=="undef"){$amavis->main_array["BEHAVIORS"]["virus_admin"]=null;}

	$mailfrom_notify_admin=$tpl->_ENGINE_parse_body("{mailfrom_notify_admin}:");
	$mailfrom_notify_recip=$tpl->_ENGINE_parse_body("{mailfrom_notify_recip}:");
	$mailfrom_notify_spamadmin=$tpl->_ENGINE_parse_body("{mailfrom_notify_spamadmin}:");
	$mailfrom_notify=$tpl->_ENGINE_parse_body("{mailfrom_notify}:");
	$virus_admin=$tpl->_ENGINE_parse_body("{virus_admin}:");
	$warnbadhsender=$tpl->_ENGINE_parse_body("{warnbadhsender}:");
	$warnbadhrecip=$tpl->_ENGINE_parse_body("{warnbadhrecip}:");
	$warnvirusrecip=$tpl->_ENGINE_parse_body("{warnvirusrecip}:");
	$warnbannedrecip=$tpl->_ENGINE_parse_body("{warnbannedrecip}:");
	
	
	$sytrip_text=80;
	$sytrip_text_=$sytrip_text-3;
	
	
	if(strlen($mailfrom_notify_admin)>$sytrip_text){$mailfrom_notify_admin=texttooltip(substr($mailfrom_notify_admin,$sytrip_text_)."...:",$mailfrom_notify_admin);}
	if(strlen($mailfrom_notify_recip)>$sytrip_text){$mailfrom_notify_recip=texttooltip(substr($mailfrom_notify_recip,0,$sytrip_text_)."...:",$mailfrom_notify_recip);}
	if(strlen($mailfrom_notify_spamadmin)>$sytrip_text){$mailfrom_notify_spamadmin=texttooltip(substr($mailfrom_notify_spamadmin,0,$sytrip_text_)."...:",$mailfrom_notify_spamadmin);}
	if(strlen($mailfrom_notify)>$sytrip_text){$mailfrom_notify=texttooltip(substr($mailfrom_notify,0,$sytrip_text_)."...:",$mailfrom_notify);}
	if(strlen($virus_admin)>$sytrip_text){$virus_admin=texttooltip(substr($virus_admin,0,$sytrip_text_)."...:",$virus_admin);}
	if(strlen($warnbadhsender)>$sytrip_text){$warnbadhsender=texttooltip(substr($warnbadhsender,0,$sytrip_text_)."...:",$warnbadhsender);}
	if(strlen($warnbadhrecip)>$sytrip_text){$warnbadhrecip=texttooltip(substr($warnbadhrecip,0,$sytrip_text_)."...:",$warnbadhrecip);}
	if(strlen($warnvirusrecip)>$sytrip_text){$warnvirusrecip=texttooltip(substr($warnvirusrecip,0,$sytrip_text_)."...:",$warnvirusrecip);}
	if(strlen($warnbannedrecip)>$sytrip_text){$warnbannedrecip=texttooltip(substr($warnbannedrecip,0,$sytrip_text_)."...:",$warnbannedrecip);}
	
	


$html="
	<div id='amavisnotifs'>
	<input type='hidden' name='INI_SAVE' value='BEHAVIORS' id='INI_SAVE'>
	<div class=explain>{notification_text}</div>
	<table style='width:100%'>	
	<tr>
		<td colspan=2><span style='margin-top:5px;font-size:14px'>$mailfrom_notify</span></td>
	</tR>
	
	
	
		<tr>
			<td class=legend nowrap>$mailfrom_notify_admin</td>
			<td width=1%>". Field_text('mailfrom_notify_admin',$amavis->main_array["BEHAVIORS"]["mailfrom_notify_admin"],'width:180px;padding:3px;font-size:13px')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$mailfrom_notify_recip</td>
			<td width=1%>". Field_text('mailfrom_notify_recip',$amavis->main_array["BEHAVIORS"]["mailfrom_notify_recip"],'width:180px;padding:3px;font-size:13px')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>$mailfrom_notify_spamadmin</td>
			<td width=1%>". Field_text('mailfrom_notify_spamadmin',$amavis->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"],'width:180px;padding:3px;font-size:13px')."</td>
		</tr>				
	</tr>
	<tr>
		<td colspan=2><span style='margin-top:5px;font-size:14px'>{smtp_notification}:</span><hr></td>
	</tR>	
		<tr>
			<td class=legend nowrap>$virus_admin</td>
			<td width=1%>". Field_text('virus_admin',$amavis->main_array["BEHAVIORS"]["virus_admin"],'width:180px;padding:3px;font-size:13px')."</td>
		</tr>	
	<tr>
		<td class=legend nowrap>$warnbadhsender</td>
		<td width=1%>". Field_checkbox('warnbadhsender',1,$amavis->main_array["BEHAVIORS"]["warnbadhsender"])."</td>
	</tr>
	<tr>
		<td class=legend nowrap>$warnbadhrecip</td>
		<td width=1%>". Field_checkbox('warnbadhrecip',1,$amavis->main_array["BEHAVIORS"]["warnbadhrecip"])."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>$warnvirusrecip</td>
		<td width=1%>". Field_checkbox('warnvirusrecip',1,$amavis->main_array["BEHAVIORS"]["warnvirusrecip"])."</td>
	</tr>		
	<tr>
		<td class=legend nowrap>$warnbannedrecip</td>
		<td width=1%>". Field_checkbox('warnbannedrecip',1,$amavis->main_array["BEHAVIORS"]["warnbannedrecip"])."</td>
	</tr>
	<tr>
		<td colspan=2><span style='margin-top:5px;font-size:14px'>{administrator}:{categories}</span><hr></td>
	</tr>
	<tr>
		<td class=legend nowrap>Virus</td>
		<td width=1%>". Field_checkbox('admin_maps_by_ccat_cc_virus',1,$amavis->main_array["BEHAVIORS"]["admin_maps_by_ccat_cc_virus"])."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{banned}</td>
		<td width=1%>". Field_checkbox('admin_maps_by_ccat_cc_banned',1,$amavis->main_array["BEHAVIORS"]["admin_maps_by_ccat_cc_banned"])."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{unchecked}</td>
		<td width=1%>". Field_checkbox('admin_maps_by_ccat_cc_unchecked',1,$amavis->main_array["BEHAVIORS"]["admin_maps_by_ccat_cc_unchecked"])."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{spam}</td>
		<td width=1%>". Field_checkbox('admin_maps_by_ccat_cc_spam',1,$amavis->main_array["BEHAVIORS"]["admin_maps_by_ccat_cc_spam"])."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{bad_header}</td>
		<td width=1%>". Field_checkbox('admin_maps_by_ccat_cc_badh',1,$amavis->main_array["BEHAVIORS"]["admin_maps_by_ccat_cc_badh"])."</td>
	</tr>				
	<tr>
		<td colspan=2 align='right'><hr>
		". button("{apply}","SaveNotificationsEngine()")."
		
		</td>
	</tr>	
	</table>
	</div>
	<script>
	
	
	var x_SaveNotificationsEngine=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			RefreshTab('main_amavis_notifications');
		}
	
		function SaveNotificationsEngine(){
			var XHR = new XHRConnection();
			XHR.appendData('INI_SAVE','BEHAVIORS');
			XHR.appendData('mailfrom_notify_admin',document.getElementById('mailfrom_notify_admin').value);
			XHR.appendData('mailfrom_notify_recip',document.getElementById('mailfrom_notify_recip').value);
			XHR.appendData('mailfrom_notify_spamadmin',document.getElementById('mailfrom_notify_spamadmin').value);
			XHR.appendData('virus_admin',document.getElementById('virus_admin').value);			
			if(document.getElementById('warnbadhsender').checked){XHR.appendData('warnbadhsender',1);}else{XHR.appendData('warnbadhsender',0);}
			if(document.getElementById('warnbadhrecip').checked){XHR.appendData('warnbadhrecip',1);}else{XHR.appendData('warnbadhrecip',0);}
			if(document.getElementById('warnvirusrecip').checked){XHR.appendData('warnvirusrecip',1);}else{XHR.appendData('warnvirusrecip',0);}
			if(document.getElementById('warnbannedrecip').checked){XHR.appendData('warnbannedrecip',1);}else{XHR.appendData('warnbannedrecip',0);}
			
			if(document.getElementById('admin_maps_by_ccat_cc_virus').checked){XHR.appendData('admin_maps_by_ccat_cc_virus',1);}else{XHR.appendData('admin_maps_by_ccat_cc_virus',0);}
			if(document.getElementById('admin_maps_by_ccat_cc_banned').checked){XHR.appendData('admin_maps_by_ccat_cc_banned',1);}else{XHR.appendData('admin_maps_by_ccat_cc_banned',0);}
			if(document.getElementById('admin_maps_by_ccat_cc_unchecked').checked){XHR.appendData('admin_maps_by_ccat_cc_unchecked',1);}else{XHR.appendData('admin_maps_by_ccat_cc_unchecked',0);}
			if(document.getElementById('admin_maps_by_ccat_cc_spam').checked){XHR.appendData('admin_maps_by_ccat_cc_spam',1);}else{XHR.appendData('admin_maps_by_ccat_cc_spam',0);}
			if(document.getElementById('admin_maps_by_ccat_cc_badh').checked){XHR.appendData('admin_maps_by_ccat_cc_badh',1);}else{XHR.appendData('admin_maps_by_ccat_cc_badh',0);}
			
			
			
			AnimateDiv('amavisnotifs');
			XHR.sendAndLoad('$page', 'GET',x_SaveNotificationsEngine);			
			}
	</script>
	";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function banned_extensions_include_local_net(){
	
	$amavis=new amavis();
	$amavis->main_array["NETWORK"]["banned_extensions_include_local_net"]=$_GET["banned_extensions_include_local_net"];
	$amavis->Save();
}


function filterbehavior_popup(){
	
	
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$array["filterbehavior2"]='{parameters}';
	$array["bypass"]='{bypass_amavisd}';
	


	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="bypass"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"amavis.bypass.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?popup=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_amavisfilterbehavior style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_amavisfilterbehavior').tabs({
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


function filterbehavior2_popup(){
$amavis=new amavis();

$sock=new sockets();
$AmavisMemoryInRAM=$sock->GET_INFO("AmavisMemoryInRAM");
if($AmavisMemoryInRAM==null){$AmavisMemoryInRAM=0;}


$page=CurrentPageName();
	$array=array(
		null=>"{select}",
		"D_PASS"=>"{D_PASS}",
		"D_DISCARD"=>'{D_DISCARD}',
		"D_BOUNCE"=>'{D_BOUNCE}',
		"D_REJECT"=>'{D_REJECT}');
		
		
	$final_virus_destiny=Field_array_Hash($array,"final_virus_destiny",$amavis->main_array["BEHAVIORS"]["final_virus_destiny"],"load_d_exp(this)");
	$final_banned_destiny=Field_array_Hash($array,"final_banned_destiny",$amavis->main_array["BEHAVIORS"]["final_banned_destiny"],"load_d_exp(this)");
	$final_spam_destiny=Field_array_Hash($array,"final_spam_destiny",$amavis->main_array["BEHAVIORS"]["final_spam_destiny"],"load_d_exp(this)");
	$final_bad_header_destiny=Field_array_Hash($array,"final_bad_header_destiny",$amavis->main_array["BEHAVIORS"]["final_bad_header_destiny"],"load_d_exp(this)");
	
	
$behavior_form="<div id='FFM_filterbehavior_popup'>
	<input type='hidden' name='INI_SAVE' value='BEHAVIORS' id='INI_SAVE'>
	
	<table style='width:100%' class=form>	
	<tr>
		<td class=legend nowrap>{final_virus_destiny}:</td>
		<td>$final_virus_destiny</td>
	</tr>
	<tr>
		<td class=legend nowrap>{final_banned_destiny}:</td>
		<td>$final_banned_destiny</td>
	</tr>
	<tr>
		<td class=legend nowrap>{final_spam_destiny}:</td>
		<td>$final_spam_destiny</td>
	</tr>
	<tr>
		<td class=legend nowrap>{final_bad_header_destiny}:</td>
		<td>$final_bad_header_destiny</td>
	</tr>
	<tr>
		<td class=legend nowrap>{EnableBlockUsersTroughInternet}:</td>
		<td>". Field_checkbox('EnableBlockUsersTroughInternet',1,$amavis->EnableBlockUsersTroughInternet)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{transfert_messages_if_av_failed}:</td>
		<td>". Field_checkbox('always_clean',1,$amavis->main_array["BEHAVIORS"]["always_clean"])."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{trust_my_net}:</td>
		<td>". Field_checkbox('trust_my_net',1,$amavis->main_array["BEHAVIORS"]["trust_my_net"])."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{amavis_enable_db}:</td>
		<td>". Field_checkbox('enable_db',1,$amavis->main_array["BEHAVIORS"]["enable_db"],"CheckAmaCache()")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{amavis_enable_global_cache}:</td>
		<td>". Field_checkbox('enable_global_cache',1,$amavis->main_array["BEHAVIORS"]["enable_global_cache"])."</td>
	</tr>	
	
	
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SaveAmavisFilterBehavior();")."</td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	<td><span id=D_EXPLAIN></span></td>
	</tr>
	</table>
	</div>	";
	
$performances="
<table style='width:100%' class=form>	
	<tr>
		<td class=legend nowrap>{AmavisMemoryInRAM}:</td>
		<td>" . Field_text('AmavisMemoryInRAM',$AmavisMemoryInRAM,"width:50px")."&nbsp;<strong style='font-size:13px'>M</strong></td>
		<td width=1%>" . help_icon('{AmavisMemoryInRAM_TEXT}')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{max_servers}:</td>
		<td>" . Field_text('max_servers',$amavis->main_array["BEHAVIORS"]["max_servers"],"width:50px")."&nbsp;<strong style='font-size:13px'>{processes}</strong></td>
		<td width=1%>" . help_icon('{max_servers_text}')."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{max_requests}:</td>
		<td>" . Field_text('max_requests',$amavis->main_array["BEHAVIORS"]["max_requests"],"width:50px")."&nbsp;<strong style='font-size:13px'></strong></td>
		<td width=1%>" . help_icon('{max_requests_text}')."</td>
	</tr>		

	<tr>
		<td class=legend nowrap>{child_timeout}:</td>
		<td>" . Field_text('child_timeout',$amavis->main_array["BEHAVIORS"]["child_timeout"],"width:50px")."&nbsp;<strong style='font-size:13px'>{seconds}</strong></td>
		<td width=1%>" . help_icon('{child_timeout_text}')."</td>
	</tr>	
	<tr><td colspan=3><hr></td></tr>
	<tr><td colspan=3 align='right'>". button("{apply}","SaveAmavisPerformances();")."</td></tr>
	
</table>


";

	
	

	$html="
	<div class=explain>{filter_behavior_text}</div>
		$behavior_form
	<hr>
		<div id='performancesamavis'>
			<div style='font-size:16px'>{AMAVIS_PERFS}</div>
				$performances
		</div>
		
<script>
	var x_SaveAmavisPerformances=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      LoadAmavisFilterBehavior();
      }	
	
	function SaveAmavisPerformances(){
		var XHR = new XHRConnection();
		XHR.appendData('AmavisMemoryInRAM',document.getElementById('AmavisMemoryInRAM').value);
		XHR.appendData('max_servers',document.getElementById('max_servers').value);
		XHR.appendData('max_requests',document.getElementById('max_requests').value);
		XHR.appendData('child_timeout',document.getElementById('child_timeout').value);
		AnimateDiv('performancesamavis');
		XHR.sendAndLoad('$page', 'GET',x_SaveAmavisPerformances);		
		}	
	function SaveAmavisFilterBehavior(){
		var XHR = new XHRConnection();
		XHR.appendData('INI_SAVE','BEHAVIORS');
		XHR.appendData('final_virus_destiny',document.getElementById('final_virus_destiny').value);
		XHR.appendData('final_banned_destiny',document.getElementById('final_banned_destiny').value);
		XHR.appendData('final_spam_destiny',document.getElementById('final_spam_destiny').value);
		XHR.appendData('final_bad_header_destiny',document.getElementById('final_bad_header_destiny').value);
		if(document.getElementById('EnableBlockUsersTroughInternet').checked){XHR.appendData('EnableBlockUsersTroughInternet',1);}else{XHR.appendData('EnableBlockUsersTroughInternet',0);}
		if(document.getElementById('trust_my_net').checked){XHR.appendData('trust_my_net',1);}else{XHR.appendData('trust_my_net',0);}
		if(document.getElementById('enable_db').checked){XHR.appendData('enable_db',1);}else{XHR.appendData('enable_db',0);}
		if(document.getElementById('enable_global_cache').checked){XHR.appendData('enable_global_cache',1);}else{XHR.appendData('enable_global_cache',0);}
		if(document.getElementById('always_clean').checked){XHR.appendData('always_clean',1);}else{XHR.appendData('always_clean',0);}
		
		
		
		AnimateDiv('performancesamavis'); 
		XHR.sendAndLoad('$page', 'GET',x_SaveAmavisPerformances);	    
	}
	
	function CheckAmaCache(){
		document.getElementById('enable_global_cache').disabled=true;
		if(document.getElementById('enable_db').checked){
			document.getElementById('enable_global_cache').disabled=false;
		}
	}
	CheckAmaCache();
</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function localnetwork_js(){
$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{local_network}");
	$html="YahooWin(550,'$page?popup=localnetwork','$title');
	
	var x_AddNetworkIP=function(obj){
      var tempvalue=obj.responseText;
      alert(tempvalue);
      YahooWin(550,'$page?popup=localnetwork','$title');
	  }
	
	 function AddNetworkIP(){
	 	var XHR = new XHRConnection();
	 	XHR.appendData('ip_from',document.getElementById('ip_from').value);
	 	XHR.appendData('ip_to',document.getElementById('ip_to').value);
	 	document.getElementById('myform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\";  
		XHR.sendAndLoad('$page', 'GET',x_AddNetworkIP);
	}
	
	function DelNetworkIP(index){
		var XHR = new XHRConnection();
	 	XHR.appendData('ip_delete',index);
	 	document.getElementById('myform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\"; 
	 	XHR.sendAndLoad('$page', 'GET',x_AddNetworkIP);
	}
	 	
	";
	return $html;	
	}

function apply_popup2(){	
$amavis=new amavis();
$amavis->Save();
$amavis->SaveToServer();
$main=new main_cf();
$main->save_conf();
$main->save_conf_to_server();
}	
	
function apply_popup(){
	
	$html="
	<div id='myform'>
	<H1>{apply}</H1>
	<p class=caption>{APPLY_SETTINGS_AMAVIS}</p>
	<H2 style='color:red' id='apply_results'></H2>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}	
	
function localnetwork_popup(){
$amavis=new amavis();
	
	$html="
	<div id='myform'>
	<H1>{local_network}</H1>
	<p class=caption>{local_network_text}</p>
	<p class=caption>{local_network_explain}</p>
	<table style='width:100%'>
	<tr>
	<td class=legend nowrap>{ip_from}:</td>
	<td>" . Field_text('ip_from',null)."</td>
	<td class=legend nowrap>{ip_to}:</td>
	<td>" . Field_text('ip_to',null)."</td>
	<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddNetworkIP()\"></td>
	</tr>
	</table>
	<hr>
	<table style='width:100%'>
	";
	
	$net=explode(",",$amavis->main_array["NETWORK"]["LocalNetwork"]);
	while (list ($num, $ligne) = each ($net) ){
		if(trim($ligne)==null){continue;}
		$html=$html. "<tr " . CellRollOver().">
		<td width=1%><img src='img/network-1.gif'>
		<td><span style='font-size:12px;font-weight:bold'>$ligne</span></td>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"DelNetworkIP('$num')")."</td>
		</tr>
		<tr><td colspan=3><hr></td></tr>
		";
		
	}
	
	$html=$html."
	</table>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function localnetwork_del(){
	$amavis=new amavis();
	$amavis->DelNetwork($_GET["ip_delete"]);
	
}
	
function localnetwork_save(){
	include_once("ressources/class.tcpip.inc");
	$ip=new IP();
	$net=$ip->ip2cidr($_GET["ip_from"],$_GET["ip_to"]);
	if(trim($net)==null){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{$_GET["ip_from"]}=>{$_GET["ip_to"]}=>{failed}\n");
		
	}else{
		$amavis=new amavis();
		$amavis->AddNetwork($net);
		
		
	}
	
}


function filterextension_js(){
$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{filter_extension}");
	$html="
	YahooWin(550,'$page?popup=filterextension','$title');
	
var x_AmavisAddExtFilter=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
      YahooWin(550,'$page?popup=filterextension','$title');
	  }	
	
	function AmavisAddExtFilter(){
	  var text=document.getElementById('AmavisAddExtFilter_text').value;
	  var exts=prompt(text);
	  if(exts){
	    var XHR = new XHRConnection();
	 	XHR.appendData('add_exts',exts);
	    XHR.sendAndLoad('$page', 'GET',x_AmavisAddExtFilter);
	  }
	}
	
	function AmavisDelExtFilter(ext){
	 var XHR = new XHRConnection();
	 	XHR.appendData('del_ext',ext);
	    XHR.sendAndLoad('$page', 'GET',x_AmavisAddExtFilter);
	}
	
	function BannedExtJsLocal(){
	 var XHR = new XHRConnection();
		if(document.getElementById('banned_extensions_include_local_net').checked){
			XHR.appendData('banned_extensions_include_local_net','1');
		}else{
			XHR.appendData('banned_extensions_include_local_net','0');
		}
		XHR.sendAndLoad('$page', 'GET');
	}

	";	
	return $html;	
	
}


function filterextension_popup(){
$amavis=new amavis();

$local_net=Field_checkbox("banned_extensions_include_local_net",
1,$amavis->main_array["NETWORK"]["banned_extensions_include_local_net"],"BannedExtJsLocal()");

$html="
<input type='hidden' id='AmavisAddExtFilter_text' value='{AmavisAddExtFilter_text}'>
	<div class=explain>{filter_extension_text}</div>
	<table style='width:100%'>
		<tr>
			<td class=legend>{apply_rules_for_local_users}:</td>
			<td>$local_net</td>
		</tr>
	</table>
	
	<div style='width:100%;text-align:right'>". button("{add_ban_ext}","AmavisAddExtFilter()")."</div>
	
	</div>
	";
	
	$tablestyle="style='width:100px;margin-right:5px;border-right:1px solid #CCCCCC'";
	
	
$table="
<H3>{extension_list}</h3><hr>
<div style='width:100%;height:400px;overflow:auto'>";

$sql=new mysql();
	$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='_Global' ORDER BY IncludeByName";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){return null;}
		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["IncludeByName"]==null){continue;}
	if(file_exists("img/ext/{$ligne["IncludeByName"]}_small.gif")){$img="img/ext/{$ligne["IncludeByName"]}_small.gif";}else{$img="img/ext/ico_small.gif";}
	
	$table=$table."
	<div style='float:left;margin:2px'>
		<table style='width:80px;border:1px solid #CCCCCC'>
			<tr " . CellRollOver().">
				<td width=1%' align='center'><img src='$img'></td>
				<td width=1%'>" . imgtootltip('ed_delete_grey.gif',"{global_rule}",";")."</td>
			</tr>
			<tr>
				<td align='center' colspan=2><strong style='font-size:11px'>{$ligne["IncludeByName"]}</td>
			</tr>
		</table>
	</div>";
		
	}	



if(is_array($amavis->extensions)){

while (list ($num, $ligne) = each ($amavis->extensions) ){
	
	if(file_exists('img/ext/'.$ligne.'_small.gif')){$img="img/ext/{$ligne}_small.gif";}else{$img="img/ext/ico_small.gif";}
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

function filterextension_add(){
	$amavis=new amavis();
	$amavis->add_extentions($_GET["add_exts"]);
}

function filterextension_del(){
	$amavis=new amavis();
	$amavis->del_extentions($_GET["del_ext"]);	
}


function INI_SAVE(){
	$amavis=new amavis();
	while (list ($num, $ligne) = each ($_GET) ){
		writelogs("Saving [$num]=[$ligne]",__FUNCTION__,__FILE__,__LINE__);
		$amavis->main_array[$_GET["INI_SAVE"]][$num]=$ligne;
	}
	$amavis->EnableBlockUsersTroughInternet=$_GET["EnableBlockUsersTroughInternet"];
	$amavis->Save();
}







?>