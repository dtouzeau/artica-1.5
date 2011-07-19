<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');	
	include_once('ressources/class.kavmilterd.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.postfix-multi.inc');
	
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==true or $usersmenus->AllowChangeKas==true){}else{header('location:users.index.php');exit;}	
if( isset($_POST['upload']) ){Kavmilter_Addkey_LicenceUploaded();exit();}
if(isset($_GET["status"])){kavmilterd_status();exit;}
if(isset($_GET["main"])){switchTab();exit;}
if(isset($_GET["kavmilterd_save_standard"])){kavmilterd_save_standard();exit;}
if(isset($_GET["KavMilterdDeleteNotify"])){PageGroupRule_Notify_delete();exit;}
if(isset($_GET["DEST"])){PageGroupRule_Notify_save();exit;}
if(isset($_GET["SavePolicyRule"])){PageGroupRule_save();exit;}
if(isset($_GET["PolicyRule"])){echo PageGroupRule();exit;}
if(isset($_GET["kavmilter_events"])){echo kavmilter_events_page();exit;}
if(isset($_GET["KavMilterAddkey"])){echo Kavmilter_Addkey();exit;}
if(isset($_GET["iframe_addkey"])){echo Kavmilter_Addkey_form();exit;}
if(isset($_GET["stats"])){echo kavmilter_stats_generate();exit;}
if(isset($_GET["kavmilter_select_notify_action"])){PageGroupRule_Notify_select_action();exit();}
if(isset($_GET["enable_kavmilter"])){kavmilter_save_enable();exit;}
if(isset($_GET["ajax"])){ajaxmode();exit;}
if(isset($_GET["ajax-pop"])){popup();exit;}
if(isset($_GET["rebuild"])){rebuild();exit;}
if(isset($_GET["UseUpdateServerUrl"])){save_update();exit;}
if(isset($_GET["UseAVBasesSet"])){save_general();exit;}



function ajaxmode(){
	$data=file_get_contents("js/kavmilterd.js");
	$html="
	$data
	YahooWin(745,'milter.index.php?ajax-pop=yes','Kaspersky Antivirus (milter edition)')
	
	";
	echo $html;
	
}

function rebuild(){
	$kav=new kavmilterd();
	$kav->ReBuildAllRules();
	
	
}

function popup(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(main_tabs());
	
}




function main_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["globalsettings"]='{globalsettings}';
	$array["logs"]='{logs}';	
	$array["statistics"]='{statistics}';
	$array["license"]='{license}';		
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?main=$num&tab=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	return "
	<div id=main_config_kav4lms style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_kav4lms').tabs({
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
function switchTab(){
	switch ($_GET["tab"]) {
		case "index":index();exit;
		case "globalsettings":GlobalSettings();break;
	    case "update":Update();break;
	    case "logs":kavmilter_events();break;
	    case "statistics":kavmilter_stats();break;
	    case "license":kavmilter_license();break;
		default:GlobalSettings();break;
	}
	
}

function index(){
	
	$tpl=new templates();
	
	$sock=new sockets();
	$bases=unserialize(base64_decode($sock->getFrameWork("cmd.php?kavmilter-bases-infos=yes")));
	
	
	
	$retranslator=Paragraphe('64-retranslator.png','{APP_KRETRANSLATOR}','{APP_KRETRANSLATOR_TEXT}',"javascript:Loadjs('index.retranslator.php')",'{APP_KRETRANSLATOR_TEXT}');
	$license_kaspersky=Paragraphe('64-kav-license.png','{license_info}','{APP_KAV4PROXY}<br>{license_info_text}',"javascript:Loadjs('squid.newbee.php?kav-license=yes&license-type=milter')");
	$pattern_status=Paragraphe('pattern-database-64.png','{antivirus_database}',"{date}:<b>{$bases[0]}</b><hr>{size}:<b>{$bases[1]}</b>");
	$default=Paragraphe('rule-64.png','{default_rule}',"{default_rule_text}","javascript:Loadjs('domains.edit.kavmilter.ou.php?ou=". base64_encode("default")."')");
	
	
	
	
	$html="<table style=width:100%>
	<tr>
		<td valign='top'>$retranslator</td>
		<td valign='top'>$license_kaspersky</td>
		<td valign='top'>$pattern_status</td>
	</tr>
	<tr>
		<td valign='top'>$default</td>
		<td valign='top'>&nbsp;</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function kavmilter_events(){
	
	$html="<br>
	<form name='ffm1'>
	<H4>{events}</H4>
	<iframe src='milter.index.php?kavmilter_events=yes' width='100%' height='500px' style='border:0px;'></iframe>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}



function Kavmilter_Addkey_LicenceUploaded(){
	$tmp_file = $_FILES['fichier']['tmp_name'];
	
	writelogs("tmp_file=$tmp_file",__FUNCTION__,__FILE__);
	
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){Kavmilter_Addkey_form('{error_unable_to_upload_file}');exit();}
	
	 $type_file = $_FILES['fichier']['type'];
	  if( !strstr($type_file, 'key')){	Kavmilter_Addkey_form('{error_file_extension_not_match} :key');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){Kavmilter_Addkey_form("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    include_once("ressources/class.sockets.inc");
    $socket=new sockets();
 	$res=$socket->getfile("kavmilter_licencemanager:$content_dir/$name_file");
	 $res=str_replace("\r","",$res);
	 $res=str_replace("Error registering keyfile","<strong style='color:red'>Error registering keyfile</strong>",$res);
	 
 	 $res=wordwrap($res,40,"\n",true);
 	 $res=nl2br($res);
 	 Kavmilter_Addkey_form($res);	
	
	
}


function Kavmilter_Addkey_form($error=null){
	$tpl=new templates();
	
	if($error<>null){$error="<br>".RoundedLightGrey($error)."<br>";}
	
	$form="
	<p>{aveserver_licence_add}</p>
	<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
	<p>
	<input type=\"file\" name=\"fichier\" size=\"30\">
	<div style='text-align:right;width:100%'><input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:190px'></div>
	</p>
	</form>

	";
	$form=$error .RoundedLightGreen($form);
	
$html="<html>
		
		<head>$tpl->head
			<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
			
		</head><body style='margin:0px;padding:0px;background-color:#FFFFFF'><br>$form</body></html>";		
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function kavmilter_stats(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$milter=new kavmilterd();
	$milter->BuildStatistics();
	$tpl=new templates();
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?kavmilterd=viruses",300,250,"",true,$users->ChartLicence);	
	$graph2=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?kavmilterd=perf",300,250,"",true,$users->ChartLicence);	
	
$html="<br>
	<form name='ffm1'>
	<H5>{statistics}</H5>
	<table style='width:100%'>
	<tr>
	<td valign='top'><h5>{scanner}</H5>	$graph1</td>
	<td valign='top'><h5>{performances}</H5>	$graph2</td>
	</tr>
	</table>";

	
	echo $tpl->_ENGINE_parse_body($html);
	
}




function kavmilter_license(){
	$sock=new sockets();
	$license_data=base64_decode($sock->getFrameWork('cmd.php?kavmilter_license=yes'));
	$license_data=htmlentities($license_data);
	$license_data=nl2br($license_data);
	$license_data=str_replace("<br />\n<br />","<br />",$license_data);
	$license_data=str_replace("License info:","<strong style='font-size:12px'>License info:</strong>",$license_data);
	$license_data=str_replace("Active key info:","<strong style='font-size:12px'>Active key info:</strong>",$license_data);
	$license_data=str_replace("Expiration date","<strong style='color:red'>Expiration date</strong>",$license_data);
	$license_data=str_replace("Kaspersky license manager for Linux","<H6 style='margin-top:0px'>Kaspersky license manager for Linux</H6>",$license_data);
	
	
	$license_data=RoundedLightGreen($license_data);
	$add_key=
	
	
	
	$html="<br>
	<form name='ffm1'>
	<H5>{license}</H5>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<div id='license_data'>	
	$license_data
	</div>
	</td>
	<td valign='top'>
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td>".Paragraphe('add-key-64.png','{add_a_license}','{add_a_license_text}',"javascript:Loadjs('squid.newbee.php?kav-license=yes&license-type=milter')") ."</td>
	</tr>
	</table>")."<br>
	
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
	<td>".Paragraphe('shopping-cart-64.png','{by_a_license}','{by_a_license_text}',"javascript:MyHref(\"http://www.kaspersky.com/buy_kaspersky_security_mail_server\")") ."</td>
	</tr>
	</table>")."<br>
	
	</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function kavmilter_events_page(){
	
		$sock=new sockets();
		
		$datas=$sock->getfile('kavmilter_logs');
		writelogs(strlen($datas) . ' bytes',__FUNCTION__,__FILE__);
		$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			$html=$html . "<div style='color:white;margin-bottom:3px;font-size:10px;'><code>$val</code></div>";
			
		}
		
		$logs= RoundedBlack($html);
		
		$tpl=new templates();
		$html="<html>
		
		<head>$tpl->head
			<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
			<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
			<META HTTP-EQUIV='REFRESH' CONTENT='10'>
		</head><body style='margin:0px;padding:0px;background-color:#FFFFFF'>$logs</body></html>";
		return $html;
		
	
}

function GlobalSettings($noecho=0){
	$milter=new kavmilterd();
	$page=CurrentPageName();
	$conf=new multi_config("kaspersky.server");
	$MilterTimeout=$conf->GET("MilterTimeout");
	if($MilterTimeout==null){$MilterTimeout=600;}
	$MaxScanRequests=$conf->GET("MaxScanRequests");
	if($MaxScanRequests==null){$MaxScanRequests=0;}
	$MaxScanTime=$conf->GET("MaxScanTime");
	if($MaxScanTime==null){$MaxScanTime=10;}
	
	$ScanPacked=$conf->GET("ScanPacked");
	if($ScanPacked==null){$ScanPacked="yes";}
	
	$ScanArchives=$conf->GET("ScanArchives");
	if($ScanArchives==null){$ScanArchives="yes";}
	
	$ScanCodeanalyzer=$conf->GET("ScanCodeanalyzer");
	if($ScanCodeanalyzer==null){$ScanCodeanalyzer="yes";}
	
	$UseAVBasesSet=$conf->GET("UseAVBasesSet");
	if($UseAVBasesSet==null){$UseAVBasesSet="extended";}
	
$arr1="
				<table style='width:100%' class=table_form>
				<tr>
				<td class=legend>{MilterTimeout}:</strong></td>
				<td align='left'>" . Field_text('MilterTimeout',$MilterTimeout,'width:100px')."</td>
				<td align='left'>" . help_icon('{MilterTimeout_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{MaxScanRequests}:</strong></td>
				<td align='left'>" . Field_text('MaxScanRequests',$MaxScanRequests,'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxScanRequests_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{MaxScanTime}:</strong></td>
				<td align='left'>" . Field_text('MaxScanTime',$MaxScanTime,'width:50px')."</td>
				<td align='left'>" . help_icon('{MaxScanTime_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{ScanArchives}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanArchives",$ScanArchives)."</td>
				<td align='left'>" . help_icon('{ScanArchives_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{ScanPacked}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanPacked",$ScanPacked)."</td>
				<td align='left'>" . help_icon('{ScanPacked_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{ScanCodeanalyzer}:</strong></td>
				<td align='left'>" . Field_yesno_checkbox("ScanCodeanalyzer",$ScanCodeanalyzer)."</td>
				<td align='left'>" . help_icon('{ScanCodeanalyzer_text}') . "</td>
				</tr>						
				<tr>
				<td class=legend>{UseAVBasesSet}:</strong></td>
				<td align='left'>" . Field_array_Hash(array("standard"=>"standard","extended"=>"extended","redundant"=>"redundant"),'UseAVBasesSet',$UseAVBasesSet)."</td>
				<td align='left'>" . help_icon('{UseAVBasesSet_text}') . "</td>
				</tr>	
				</table>";	

$script="<script>
	var X_SaveKavMilterGeneral= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_kav4lms');
		}
			
	
	
	function SaveKavMilterGeneral(){
			var XHR = new XHRConnection();
			if(document.getElementById('ScanArchives').checked){XHR.appendData('ScanArchives','yes');}else{XHR.appendData('ScanArchives','no');}
			if(document.getElementById('ScanPacked').checked){XHR.appendData('ScanPacked','yes');}else{XHR.appendData('ScanPacked','no');}
			if(document.getElementById('ScanCodeanalyzer').checked){XHR.appendData('ScanCodeanalyzer','yes');}else{XHR.appendData('ScanCodeanalyzer','no');}
			XHR.appendData('UseAVBasesSet',document.getElementById('UseAVBasesSet').value);
			XHR.appendData('MilterTimeout',document.getElementById('MilterTimeout').value);
			XHR.appendData('MaxScanRequests',document.getElementById('MaxScanRequests').value);
			XHR.appendData('MaxScanTime',document.getElementById('MaxScanTime').value);
			document.getElementById('kavmiltergeneralsets').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_SaveKavMilterGeneral);		
		
	}
</script>";	
	
	
	$html="<table STYLE='width:100%;'>
		<tr>
	<td valign='top' ><div id='kavmiltergeneralsets'><img src='img/global-settings-128.png'></div>
	
	</td>
	
	<td valign='top' >
		$arr1
	</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>
			". button("{edit}","SaveKavMilterGeneral()")."
		</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function save_update(){
	$conf=new multi_config("kaspersky.updater");
	
	while (list ($num, $ligne) = each ($_GET) ){
		$conf->SET_VALUE($num,$ligne);
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kavmilter-configure=yes");
	
}

function save_general(){
	$conf=new multi_config("kaspersky.server");
	
	while (list ($num, $ligne) = each ($_GET) ){
		$conf->SET_VALUE($num,$ligne);
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kavmilter-configure=yes");
	
}


	
function Update(){
	$page=CurrentPageName();
	$conf=new multi_config("kaspersky.updater");
	$artica=new artica_general();
	$UseProxy=Field_yesno_checkbox("UseProxy",$conf->GET("UseProxy"));
	$ProxyAddress=Field_text('ProxyAddress',$conf->GET("ProxyAddress"),'width:250px');
	
	if($artica->ArticaProxyServerEnabled=='yes'){
		$UseProxy="<strong>Yes</strong><input type='hidden' name='UseProxy' value='yes'>";
		$ProxyAddress="<strong>$artica->ArticaCompiledProxyUri</strong><input type='hidden' name='ProxyAddress' value='$artica->ArticaProxyServerEnabled'>";
	}

	
$script="<script>
	var X_KavUpdaterSettings= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_kav4lms');
		}
			
	
	
	function KavUpdaterSettings(){
			var XHR = new XHRConnection();
			if(document.getElementById('UseUpdateServerUrl').checked){XHR.appendData('UseUpdateServerUrl','yes');}else{XHR.appendData('UseUpdateServerUrl','no');}
			if(document.getElementById('UseUpdateServerUrlOnly').checked){XHR.appendData('UseUpdateServerUrlOnly','yes');}else{XHR.appendData('UseUpdateServerUrlOnly','no');}
			XHR.appendData('UpdateServerUrl',document.getElementById('UpdateServerUrl').value);
			XHR.appendData('RegionSettings',document.getElementById('RegionSettings').value);
			if(document.getElementById('UseProxy').checked){XHR.appendData('UseProxy','yes');}else{XHR.appendData('UseProxy','no');}
			XHR.appendData('ProxyAddress',document.getElementById('ProxyAddress').value);
			document.getElementById('dialog2').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_KavUpdaterSettings);		
		
	}
</script>";	
	
$arr="

<table style=width:100%'>
				<tr>
				<td class=legend>{UseUpdateServerUrl}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("UseUpdateServerUrl",$conf->GET("UseUpdateServerUrl"))."</td>
				<td align='left'>" . help_icon('{UseUpdateServerUrl_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{UseUpdateServerUrlOnly}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("UseUpdateServerUrlOnly",$conf->GET("UseUpdateServerUrlOnly"))."</td>
				<td align='left'>" . help_icon('{UseUpdateServerUrlOnly_text}') . "</td>
				</tr>		
				<tr>
				<td class=legend>{UpdateServerUrl}:</strong></td>
				<td align='left'>" . Field_text('UpdateServerUrl',$conf->GET("UpdateServerUrl"),'width:250px')."</td>
				<td align='left'>" . help_icon('{UpdateServerUrl_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{RegionSettings}:</strong></td>
				<td align='left'>" . Field_array_Hash(array("Russia"=>"Russia","Europe"=>"Europe","America"=>"America","China"=>"China","Japan"=>"Japan","Korea"=>"Korea"),'RegionSettings',$conf->GET("RegionSettings"))."</td>
				<td align='left'>" . help_icon('{RegionSettings_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{UseProxy}:</strong></td>
					<td align='left'>$UseProxy</td>
				<td align='left'>&nbsp;</td>
				</tr>		
				<tr>
				<td class=legend>{ProxyAddress}:</strong></td>
				<td align='left'>$ProxyAddress</td>
				<td align='left'>" . help_icon('{ProxyAddress_text}') . "</td>
				</tr>																
				
				</table>";	
				
$update_kaspersky=Paragraphe('kaspersky-update-64.png','{UPDATE_ANTIVIRUS}','{APP_KAVMILTER}<br>{UPDATE_ANTIVIRUS_TEXT}',"javascript:Loadjs('squid.newbee.php?update-kav=yes&type=milter')");				
	
	$html="$script<div id='KavUpdaterSettingsid'>
	<form name='ffm1'>
	<table STYLE='width:100%;'>
		<tr>
	<td valign='top' >$arr</td>
	<td valign='top' >$arr3</td>
	</tr>
	<td colspan=2 align='right'><hr>". button("{edit}","KavUpdaterSettings()")."</td>
	</tr>
	</table>
	</div>
	$update_kaspersky
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.newbee.php');	
}




function DefaultRule(){
	$_GET["PolicyRule"]="default";
	$val=PageGroupRule();
$html="<br>
	<H4>{default_rule}</H4>
	<table STYLE='width:100%;'>
		<tr>
	<td valign='top' >
	<div style='padding:10px;margin:5px;border:1px dotted #CCCCCC'>
		<div id='KavMilterdPolicyZone'>$val</div><br>
	</div>
	</td>
	</tr>
	</table>";
	$_GET["PolicyRule"]="Default";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function PageGroupRule(){
    
	switch ($_GET["PolicyTab"]) {
		case 1:$content=PageGroupRule_Notify();break;
	   default:$content=PageGroupRule_Action();break;}
	
	$html=$content;
	

	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	}


function PageGroupRule_Action(){
	
	$milter=new kavmilterd();
	$milter->LoadRule($_GET["PolicyRule"]);
	$tabs=PageGroupRule_tabs();
    $page=CurrentPageName();
$ScanPolicy=RoundedLightGrey("
		<form name='ffm1'>
		<input type='hidden' name='PolicyRule' value='{$_GET["PolicyRule"]}'>
		<input type='hidden' name='SavePolicyRule' value='{$_GET["PolicyRule"]}'>
		
			<table style=width:100%'>
				<tr>
				<td class=legend>{ScanPolicy}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("message"=>"message","combined"=>"combined"),'ScanPolicy',$milter->rule_array["ScanPolicy"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{ScanPolicy_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{UsePlaceholderNotice}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("UsePlaceholderNotice",$milter->rule_array["UsePlaceholderNotice"])."</td>
				<td align='left'>" . help_icon('{UsePlaceholderNotice_text}') . "</td>
				</tr>				
				
				
				
				<tr>
				<td class=legend>{DefaultAction}:</strong></td>
					<td align='left'>" . 
					Field_array_Hash(array("warn"=>"warn","drop"=>"drop","reject"=>"reject","cure"=>"cure","delete"=>"delete","skip"=>"skip"),	
					'DefaultAction',$milter->rule_array["DefaultAction"],null,null,0,'width:150px')."
				</td>
				<td align='left'>" . help_icon('{DefaultAction_text}') . "</td>
				</tr>				
				<tr>
				<td class=legend>{SuspiciousAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("warn"=>"warn","drop"=>"drop","reject"=>"reject","delete"=>"delete","skip"=>"skip"),'SuspiciousAction',$milter->rule_array["SuspiciousAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{SuspiciousAction_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{ProtectedAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("delete"=>"delete","skip"=>"skip"),'ProtectedAction',$milter->rule_array["ProtectedAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{ProtectedAction_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{ErrorAction}:</strong></td>
					<td align='left'>" . Field_array_Hash(array("warn"=>"warn","delete"=>"delete","skip"=>"skip"),'ErrorAction',$milter->rule_array["ErrorAction"],null,null,0,'width:150px')."</td>
				<td align='left'>" . help_icon('{ErrorAction_text}') . "</td>
				</tr>
				<tr>
				<td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo' style='width:200px'
				OnClick=\"javascript:ParseForm('ffm1','$page',true);LoadAjax('KavMilterdPolicyZone','$page?PolicyRule={$_GET["PolicyRule"]}&PolicyTab={$_GET["PolicyTab"]}')\" value='{edit}&nbsp;&raquo;'>				
					</td>
				</tr>		
				
				</table>");		
	
	$html="<br>$tabs<br>
	<H3>{rule}:&nbsp;{$milter->rule_array["GroupName"]}</H3>
	$ScanPolicy
	
	
	
	";
	
	return $html;
}

function PageGroupRule_Notify_table(){
	$tpl=new templates();
	$milter=new kavmilterd();
	$milter->LoadRule($_GET["PolicyRule"]);
	if(!isset($milter->rule_notify_array)){return RoundedLightGreen($tpl->_ENGINE_parse_body('{error_no_datas}'));}
	reset($milter->rule_notify_array);
	$html=$html . "<table style='width:200px' align='center'>";
	while (list ($num, $val) = each ($milter->rule_notify_array) ){
		while (list ($c, $d) = each ($val)){
					$html=$html ."
					<tr>
						<td width='1%'><img src='img/fw_bold.gif'></td>
						<td>$num</td>
						<td>$c</td>
						<td>" . imgtootltip('x.gif','{delete}',"KavMilterdDeleteNotify('$num','$c')")."</td>
					</tr>";
					
		}
		
		
	}
	$html=$html . "</table>";
	return RoundedLightGreen($html);
	
}

function PageGroupRule_Notify_save(){
	$milter=new kavmilterd();
	$milter->LoadRule($_GET["PolicyRule"]);

	if($_GET["ACT"]=='all'){unset($milter->rule_notify_array[$_GET["DEST"]]);}else {$milter->rule_notify_array[$_GET["DEST"]]["all"]='';}
	if($_GET["ACT"]=='none'){unset($milter->rule_notify_array[$_GET["DEST"]]);}else {$milter->rule_notify_array[$_GET["DEST"]]["none"]='';}
	
	
	writelogs("{$_GET["DEST"]}={$_GET["ACT"]}",__FUNCTION__,__FILE__);
	$milter->rule_notify_array[$_GET["DEST"]][$_GET["ACT"]]=$_GET["ACT"];
	$milter->SaveRuleToLdap();
	}
	
function PageGroupRule_Notify_delete(){
$milter=new kavmilterd();
	$milter->LoadRule($_GET["PolicyRule"]);	
	unset($milter->rule_notify_array[$_GET["KavMilterdDeleteNotify"]][$_GET["ACTION"]]);
	$milter->SaveRuleToLdap();
}


function PageGroupRule_Notify_select_action(){
	
	$dest=$_GET["kavmilter_select_notify_action"];
	$milter=new kavmilterd();
	$milter->LoadRule($_GET["SelectedPolicyRule"]);
	
	
	
	
	$standard=array("infected"=>"infected","suspicious"=>"suspicious","protected"=>"protected",
				"filtered"=>"filtered","error"=>"error","all"=>"all","none"=>"none");
				
	$admin=array("infected"=>"infected","suspicious"=>"suspicious","protected"=>"protected",
				"filtered"=>"filtered","error"=>"error","all"=>"all","none"=>"none","discard"=>"discard","fault"=>"fault","update"=>"update");
				
				
	if($dest=='Admin'){$array=$admin;}else{$array=$standard;}
	
	
	while (list ($num, $ligne) = each ($milter->rule_notify_array[$dest]) ){
		unset($array[$ligne]);
	}
	
	
	echo Field_array_Hash($array,'ACT',null,null,null,0,'width:160px');
	
	
}


function PageGroupRule_Notify(){
	
	$milter=new kavmilterd();
	$milter->LoadRule($_GET["PolicyRule"]);
	$tabs=PageGroupRule_tabs();
    $page=CurrentPageName();
    
    $JS_select="kavmilter_select_notify_action('{$_GET["PolicyRule"]}')";
    
    
	$ScanPolicy=RoundedLightGrey("
		
			<table style='width:100%'>
			
				<tr>
				<td align='right' nowrap><strong>{add_not_rule}:</strong></td>
				<td align='left'>" . Field_array_Hash(array(null=>"{select}","Sender"=>"Sender","Recipients"=>"Recipients","Admin"=>"Admin"),'DEST',null,$JS_select,null,0,'width:160px')."</td>
				<td align='left'><div id='notify_rule_action'></div></td>
				<td align='left'><input type='button' OnClick=\"javascript:KavMilterdAddNotify()\" value='{add}&nbsp;&raquo;' style='width:100px'></td>
				<td align='left'>" . help_icon('{NotifySender_text}') . "</td>
				</tr>
			</table>	
			<br>
			<center>
				<div id='notifications_rules' style='width:250px'>" . PageGroupRule_Notify_table() . "</div>	
				<br>
			</center>
		<form name=\"ffm11\">
		<input type='hidden' name='PolicyRule' id='PolicyRule' value='{$_GET["PolicyRule"]}'>
		<input type='hidden' name='SavePolicyRule' value='{$_GET["PolicyRule"]}'>
				<table style='width:100%'>
				<tr>
				<td class=legend>{EnableNotifications}:</strong></td>
					<td align='left'>" . Field_yesno_checkbox("EnableNotifications",$milter->rule_array["EnableNotifications"])."</td>
				<td align='left'>" . help_icon('{EnableNotifications_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{AdminAddresses}:</strong></td>
				<td align='left'>" . Field_text('AdminAddresses',$milter->rule_array["AdminAddresses"],'width:250px')."</td>
				<td align='left'>" . help_icon('{AdminAddresses_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{PostmasterAddress}:</strong></td>
				<td align='left'>" . Field_text('PostmasterAddress',$milter->rule_array["PostmasterAddress"],'width:250px')."</td>
				<td align='left'>&nbsp;</td>
				</tr>									
				<tr>
				<td class=legend>{MessageSubject}:</strong></td>
				<td align='left'>" . Field_text('MessageSubject',$milter->rule_array["MessageSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{MessageSubject_text}') . "</td>
				</tr>		
				<tr>
				<td class=legend>{SenderSubject}:</strong></td>
				<td align='left'>" . Field_text('SenderSubject',$milter->rule_array["SenderSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{SenderSubject_text}') . "</td>
				</tr>
				<tr>
				<td class=legend>{ReceiverSubject}:</strong></td>
				<td align='left'>" . Field_text('ReceiverSubject',$milter->rule_array["ReceiverSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{ReceiverSubject_text}') . "</td>
				</tr>	
				<tr>
				<td class=legend>{AdminSubject}:</strong></td>
				<td align='left'>" . Field_text('AdminSubject',$milter->rule_array["AdminSubject"],'width:100%')."</td>
				<td align='left'>" . help_icon('{AdminSubject_text}') . "</td>
				</tr>																
				
				
				
				
				<tr>
				<td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo' style='width:200px'
				OnClick=\"javascript:ParseForm('ffm11','$page',true);LoadAjax('KavMilterdPolicyZone','$page?PolicyRule={$_GET["PolicyRule"]}&PolicyTab={$_GET["PolicyTab"]}')\" value='{edit}&nbsp;&raquo;'>				
					</td>
				</tr>		
				
				</table></form>");		
	
	$html="<br>$tabs<br>
	<H3>{rule}:&nbsp;{$milter->rule_array["GroupName"]}</H3>
	$ScanPolicy
	
	
	
	";
	
	return $html;
}


function PageGroupRule_tabs(){
	if(!isset($_GET["PolicyTab"])){$_GET["PolicyTab"]=0;};
	$page=CurrentPageName();
	$array[]='{WhenDetectViruses}';
	$array[]='{notifications}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["PolicyTab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('KavMilterdPolicyZone','$page?PolicyRule={$_GET["PolicyRule"]}&PolicyTab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function PageGroupRule_save(){
    $milter=new kavmilterd();
	$milter->LoadRule($_GET["PolicyRule"]);	
	
while (list ($num, $ligne) = each ($_GET) ){
		$milter->rule_array[$num]=$ligne;
	}
	$tpl=new templates();
	if($milter->SaveRuleToLdap()){echo $tpl->_ENGINE_parse_body('{success}');}else{echo $tpl->_ENGINE_parse_body('{failed}');}	
	
}
	