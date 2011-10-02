<?php
$GLOBALS["ICON_FAMILY"]="SYSTEM";
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.syslogs.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

if(isset($_GET["HideTips"])){HideTips();exit;}

$users=new usersMenus();
if(!$users->AsAnAdministratorGeneric){writelogs("Redirect to users.index.php",__FUNCTION__,__FILE__,__LINE__);header('location:miniadm.php');exit;}


if(isset($_GET["warnings"])){warnings_js();exit;}
if(isset($_GET["warnings-popup"])){warnings_popup();exit;}
if(isset($_GET["main_admin_tabs"])){echo main_admin_tabs();exit;}


if(isset($_GET["StartStopService-js"])){StartStopService_js();exit;}
if(isset($_GET["StartStopService-popup"])){StartStopService_popup();exit;}
if(isset($_GET["StartStopService-perform"])){StartStopService_perform();exit;}
if(isset($_GET["postfix-status-right"])){echo status_postfix();exit;}

if(isset($_GET["graph"])){graph();exit;}
if(isset($_GET["start-all-services"])){START_ALL_SERVICES();exit;}
if($_GET["status"]=="left"){status_left();exit;}
if($_GET["status"]=="right"){status_right();exit;}



if(isset($_GET["postfix-status"])){POSTFIX_STATUS();exit;}
if(isset($_GET["AdminDeleteAllSqlEvents"])){warnings_delete_all();exit;}
if(isset($_GET["ShowFileLogs"])){ShowFileLogs();exit;}
if(isset($_GET["buildtables"])){CheckTables();exit;}
if(isset($_GET["CheckDaemon"])){CheckDaemon();exit;}
if(isset($_GET["EmergencyStart"])){EmergencyStart();exit;}
if(isset($_GET["memcomputer"])){status_computer();exit;}
if(isset($_GET["mem-dump"])){status_memdump();exit;}
if(isset($_GET["memory-status"])){status_memdump_js();exit;}
if(isset($_GET["artica-meta"])){artica_meta();exit;}
if(isset($_GET["admin-ajax"])){page($users);exit;}

page($users);


function HideTips(){
	$sock=new sockets();
	$sock->SET_INFO($_GET["HideTips"],1);
}

function warnings_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{$_GET["count"]} {warnings}");
	echo "YahooWinS('330','$page?warnings-popup=yes','$title');";
	
	
}

function warnings_popup(){
	$content=@file_get_contents("ressources/logs/status.warnings.html");
	$page=CurrentPageName();
	$tpl=new templates();	
	echo $tpl->_ENGINE_parse_body($content);
}


function StartStopService_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($_GET["typ"]==1){$title_pre='{starting}';}else{$title_pre="{stopping}";}
	$title_s=$tpl->_ENGINE_parse_body("$title_pre::{{$_GET["apps"]}}");
	$apps=base64_encode($_GET["apps"]);
	$html="
		function StartStopServiceStart(){
			YahooLogWatcher(550,'$page?StartStopService-popup=yes&cmd={$_GET["cmd"]}&typ={$_GET["typ"]}&apps=$apps','$title_s');
		}
	
	
	
	StartStopServiceStart()";
	
	echo $html;
}

function StartStopService_popup(){
	$page=CurrentPageName();
	
	$html="
	
	<div style='padding:3px;margin:3px;font-size:11px;width:100%;height:450px;overflow:auto' id='StartStopService_popup'>
	</div>
	
	<script>
		LoadAjax('StartStopService_popup','$page?StartStopService-perform=yes&cmd={$_GET["cmd"]}&typ={$_GET["typ"]}&apps={$_GET["apps"]}');
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function StartStopService_perform(){
	$cmd=$_GET["cmd"];
	$typ=$_GET["typ"];
	$apps=base64_decode($_GET["apps"]);
	$sock=new sockets();
	if($typ==1){
		$datas=$sock->getFrameWork("cmd.php?start-service-name=$cmd");
	}else{
		$datas=$sock->getFrameWork("cmd.php?stop-service-name=$cmd");
	}
	
	$tbl=unserialize(base64_decode($datas));
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{$apps}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne==null)){continue;}
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html . "
			<tr class=$classtr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td ><code style='font-size:14px'>" . htmlentities($ligne)."</code></td>
			</tr>
			";
			
		
	}
	

	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html. "</tbody></table>");

	$html=$html."
	
	<script>
		if(document.getElementById('main_config_pptpd')){RefreshTab('main_config_pptpd');}
		if(document.getElementById('squid_main_config')){RefreshTab('squid_main_config');}
		if(document.getElementById('services_status')){RefreshTab('services_status');}
	</script>
	
	
	";
	
	
	echo $html;
	
	
	
}
function page($usersmenus){
	$left_menus=null;
if(isset($_GET["admin-ajax"])){
	echo "<script>LoadAjax('middle','quicklinks.php');</script>";
	return;
	
}else{	
	//if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return null;}
}
$ldap=new clladp();
$page=CurrentPageName();
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$hash=$ldap->UserDatas($_SESSION["uid"]);
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
if($hash["displayName"]==null){$hash["displayName"]="{Administrator}";}
$sock=new sockets();
$ou=$hash["ou"];
$users=new usersMenus();
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);

if(isset($_COOKIE["artica-template"])){
	if(is_file("ressources/templates/{$_COOKIE["artica-template"]}/JQUERY_UI")){
		$GLOBALS["JQUERY_UI"]=trim(@file_get_contents("ressources/templates/{$_COOKIE["artica-template"]}/JQUERY_UI"));
	}
}

if($users->KASPERSKY_SMTP_APPLIANCE){
	if($sock->GET_INFO("KasperskyMailApplianceWizardFinish")<>1){
		$wizard_kaspersky_mail_appliance="Loadjs('wizard.kaspersky.appliance.php');";
	}
}

	if($users->KASPERSKY_WEB_APPLIANCE){
		//$GLOBALS["CHANGE_TEMPLATE"]="squid.kav.html";
		//$GLOBALS["JQUERY_UI"]="kavweb";
	}

	if(isset($_GET["admin-ajax"])){$left_menus="LoadAjax('TEMPLATE_LEFT_MENUS','/admin.tabs.php?left-menus=yes');";}


$html="
<script>
	LoadAjax('middle','quicklinks.php');
</script>


";	
	
	
$tpl=new template_users($title,$html,$_SESSION,0,0,0,$cfg);
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$html=$tpl->web_page;
SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;	
return;	
	

$html="	
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var fire=0;
var loop=0;
var loop2=0;
var reste=0;
var mem_ossys=0;

function Loop(){
	loop = loop+1;
	loop2 = loop2+1;
	
	if(loop2>10){
		if(!IfWindowsOpen()){if(RunJgrowlCheck()){Loadjs('jGrowl.php');}}
		loop2=0;
	}
	
	
    fire=10-fire;
    if(loop<25){
    	setTimeout(\"Loop()\",5000);
    }else{
      loop=0;
      Loop();
    }
}

	function RunJgrowlCheck(){
		if(!document.getElementById('navigation')){return false;}
		if($('#jGrowl').size()==0){return true;}
		if($('#jGrowl').size()==1){return true;}
		return false;
	
	}

	function sysevents_query(){
		if(document.getElementById('q_daemons')){
			var q_daemons=document.getElementById('q_daemons').value;
			var q_lines=document.getElementById('q_lines').value;
			var q_search=document.getElementById('q_search').value;
			LoadAjax('events','$page?main=logs&q_daemons='+ q_daemons +'&q_lines=' + q_lines + '&q_search='+q_search+'&hostname={$_GET["hostname"]}');
			}
	
	}
	
	function LoadCadencee(){		
		Loadjs('jGrowl.php');	
		setTimeout(\"Loop()\",2000);
	}


	var x_{$idmd}ChargeLogs= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('progression_js_left').innerHTML=tempvalue;
		}		
	
function LoadMemDump(){
		YahooWin(500,'$page?mem-dump=yes');
	}



function CheckDaemon(){
	var XHR = new XHRConnection();
	XHR.appendData('CheckDaemon','yes');
	XHR.sendAndLoad('$page', 'GET');
	}	


</script>	
	".main_admin_tabs()."
	
	<script>
	
		LoadCadencee();
		RTMMailHide();
		$wizard_kaspersky_mail_appliance
		$left_menus
	</script>
	{$arr[0]}
	";

$cfg["JS"][]=$arr[1];
$cfg["JS"][]="js/admin.js";

if(isset($_GET["admin-ajax"])){
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__."-admin-ajax",$html);
	echo $html;
	exit;
}
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$tpl=new templates();
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$title=$tpl->_ENGINE_parse_body("<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('admin.chHostname.php');\" style='text-transform:lowercase;font-size:12px' >[<span id='hostnameInFront'>$usersmenus->hostname</span>]</a>&nbsp;{WELCOME} <span style='font-size:12px'>{$hash["displayName"]} </span>");

error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
if($users->KASPERSKY_SMTP_APPLIANCE){
	$title=$tpl->_ENGINE_parse_body("<span style='color:#005447'>{WELCOME}</span> <span style='font-size:13px;color:#005447'>For Kaspersky SMTP Appliance</span>&nbsp;|&nbsp;<span style='font-size:12px'>{$hash["displayName"]} - <span style='text-transform:lowercase'>$usersmenus->hostname</span></span>");
}
if($users->KASPERSKY_WEB_APPLIANCE){
	$title=$tpl->_ENGINE_parse_body("<span style='color:black'>{WELCOME}</span> <span style='font-size:13px;color:black'>For Kaspersky Web Appliance</span>&nbsp;|&nbsp;<span style='font-size:12px'>{$hash["displayName"]} - <span style='text-transform:lowercase'>$usersmenus->hostname</span></span>");
}
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
if($users->ZARAFA_APPLIANCE){
	$title=$tpl->_ENGINE_parse_body("<span style='color:#005447'>{WELCOME}</span> <span style='font-size:13px;color:#005447'>For Zarafa Mail Appliance</span>&nbsp;|&nbsp;<span style='font-size:12px'>{$hash["displayName"]} - <span style='text-transform:lowercase'>$usersmenus->hostname</span></span>");
	
}

error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$tpl=new template_users($title,$html,$_SESSION,0,0,0,$cfg);
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);
$html=$tpl->web_page;
SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;			
if($GLOBALS["VERBOSE"]){echo "<H1>Finish</H1>";}
error_log(basename(__FILE__)." ".__FUNCTION__.'() line '. __LINE__);	
	
}



function main_admin_tabs(){
	if($GLOBALS["VERBOSE"]){echo "<li>".__FUNCTION__." line:".__LINE__."</li>";}
	$array["t:frontend"]="{status}";
	$users=new usersMenus();
	$sys=new syslogs();
	$artica=new artica_general();
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$array["t:graphs"]='{graphs}';	
	if($artica->EnableMonitorix==1){$array["t:monitorix"]='{monitorix}';}
	
	if($users->POSTFIX_INSTALLED){
		$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
		if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
		if($EnableArticaSMTPStatistics==1){	
		$array["t:emails_received"]="{emails_received}";
		}
	}

	
	$sock=new sockets();
	if($users->SQUID_INSTALLED){
		$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
		if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
		if($SQUIDEnable==1){
			$array["t:HTTP_FILTER_STATS"]="{MONITOR}";
			$array["t:HTTP_BLOCKED_STATS"]="{blocked_websites}";
		}
	}
	

if($users->KASPERSKY_SMTP_APPLIANCE){
	$array["t:kaspersky"]="Kaspersky";	
}else{
	$array["t:system"]="{webinterface}";
}	

if($users->AsSystemAdministrator){$array["t:cnx"]="{connections}";}
$array=$array+main_admin_tabs_perso_tabs();
$count=count($array);
if($count<7){$array["add-tab"]="{add}&nbsp;&raquo;";}
$page=CurrentPageName();
$tpl=new templates();
$width="758px";
if(isset($_GET["tab-font-size"])){$style="style=font-size:{$_GET["tab-font-size"]}";}
if(isset($_GET["tab-width"])){$width=$_GET["tab-width"];}
if(isset($_GET["newfrontend"])){$newfrontend="&newfrontend=yes";}


	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#t:(.+)#",$num,$re)){
			$ligne=$tpl->javascript_parse_text($ligne);
			if(strlen($ligne)>15){$ligne=substr($ligne,0,12)."...";}
			
			if($re[1]=="cnx"){
				$html[]= "<li ><a href=\"admin.cnx.php$newfrontend\"><span $style>$ligne</span></a></li>\n";
				continue;
			}
			
			$html[]= "<li><a href=\"admin.tabs.php?main={$re[1]}$newfrontend\"><span $style>$ligne</span></a></li>\n";
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"admin.tabs.php?tab=$num$newfrontend\"><span $style>$ligne</span></a></li>\n");
		}
	
	
return "
	<div id='mainlevel' style='width:$width;height:auto;'>
		<div id=admin_perso_tabs style='width:$width;height:auto;'>
			<ul>". implode("\n",$html)."</ul>
		</div>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#admin_perso_tabs\").tabs();});
		</script>";		
		
}

function main_admin_tabs_perso_tabs(){
	$uid=$_SESSION["uid"];
	if(!is_file("ressources/profiles/$uid.tabs")){return array();}
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	if(!is_array($ini->_params)){return array();}
	while (list ($num, $ligne) = each ($ini->_params) ){
		if($ligne["name"]==null){continue;}
		$array[$num]=$ligne["name"];
		
	}
	if(!is_array($array)){return array();}
	return $array;
}
function POSTFIX_STATUS(){
	$users=new usersMenus();
	$tpl=new templates();

	if($users->POSTFIX_INSTALLED){
			$status=new status();
			echo $tpl->_ENGINE_parse_body($status->Postfix_satus());
			exit;
	}	
}

function status_computer(){
	include_once("ressources/class.os.system.tools.inc");
	status_mysql();

	
	$os=new os_system();
	$html=RoundedLightGrey($os->html_Memory_usage())."<br>";
	echo $html;
}

function status_mysql(){
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	
	
	$sql="SELECT count(*) FROM admin_cnx";
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if(preg_match("#Access denied for user#",$q->mysql_error)){
			$error=urlencode(base64_encode("$q->mysql_error"));
			echo "
			<script>
				Loadjs('admin.mysql.error.php?error=$error');
			</script>
			";return;
		}
		
		if(preg_match("#Unknown database.+?artica_.+?#",$q->mysql_error)){
			$q->BuildTables();
			$q=new mysql();
			$sql="SELECT count(*) FROM admin_cnx";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				echo RoundedLightGrey($tpl->_ENGINE_parse_body(Paragraphe('danger64.png',"{mysql_error}","$q->mysql_error",null,"$q->mysql_error",330,80)))."<br>";
				return;
			}
		}
		
		if(preg_match("#table.+?admin_cnx.+?exist#",$q->mysql_error)){
			$q->BuildTables();
			$q=new mysql();
			$sql="SELECT count(*) FROM admin_cnx";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				echo RoundedLightGrey($tpl->_ENGINE_parse_body(Paragraphe('danger64.png',"{mysql_error}","$q->mysql_error",null,"$q->mysql_error",330,80)))."<br>";
				return;
			}			
			
		}
		
		
	
		echo RoundedLightGrey($tpl->_ENGINE_parse_body(Paragraphe('danger64.png',"{mysql_error}","$q->mysql_error",null,"$q->mysql_error",330,80)))."<br>";
		
	}	
	
}

function status_right(){
	include_once(dirname(__FILE__)."/ressources/class.browser.detection.inc");
	$users=new usersMenus();
	$sock=new sockets();
	$tpl=new templates();
	$newfrontend=false;
	if(isset($_GET["newfrontend"])){$newfrontend=true;}
	if(!$users->AsArticaAdministrator){die("<H2 style='color:red'>permission denied</H2>");}
	$page=CurrentPageName();
	
	$ldap=new clladp();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	
		
	$hostname=base64_decode($sock->getFrameWork("network.php?fqdn=yes"));
	writelogs("network.php?fqdn=yes -> hostname=\"$hostname\"",__FUNCTION__,__FILE__,__LINE__);
	$mustchangeHostname=false;
	if(preg_match("#Name or service not known#", $hostname)){$mustchangeHostname=true;}
	if(preg_match("#locahost\.localdomain#", $hostname)){$mustchangeHostname=true;}
	if(preg_match("#[A-Za-z]+\s+[A-Za-z]+#", $hostname)){$mustchangeHostname=true;}
	
	if(!$mustchangeHostname){if(preg_match("#locahost\.localdomain#", $users->hostname)){$mustchangeHostname=true;}}
	if(!$mustchangeHostname){if(strpos($hostname, ".")==0){$mustchangeHostname=true;}}
	
	if($mustchangeHostname){
	writelogs("hostname=\"$hostname\" mustchangeHostname=True",__FUNCTION__,__FILE__,__LINE__);
	}else{
		writelogs("hostname=\"$hostname\" mustchangeHostname=False",__FUNCTION__,__FILE__,__LINE__);
	}
	
	if($mustchangeHostname){echo "<script>Loadjs('admin.chHostname.php');</script>";}	
	
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?ForceRefreshRight=yes');
	
	if(!$newfrontend){
		$infos="LoadAjaxTiny('right-status-infos','admin.left.php?part1=yes');";
	}else{
		$ajaxadd="&newfrontend=yes";
	}
	
	$script="
	<div id='mem_status_computer' style='text-align:center'>".$tpl->_ENGINE_parse_body(@file_get_contents("ressources/logs/status.memory.html"))."</div>
	\n
	<div id='right-status-infos'></div>
	<script>
		LoadAjax('left_status','$page?status=left$ajaxadd');
		$infos
	</script>\n";
	
	
	
	if($users->POSTFIX_INSTALLED){
			if($GLOBALS["VERBOSE"]){echo "$page -> status_postfix() LINE:".__LINE__."\n";}
			echo status_postfix().$script;
			return null;
		}
	
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
		
	
	if($users->SQUID_INSTALLED){
		$SQUIDEnable=trim($sock->GET_INFO("SQUIDEnable"));
		if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
		if($SQUIDEnable==0){
			if($users->KASPERSKY_WEB_APPLIANCE){
				echo $memory.status_kav4proxy().$script;
				return null;
			}
			
		}
		
		if($users->KASPERSKY_WEB_APPLIANCE){echo $memory.status_squid_kav().$script;return;}
		
		if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
		echo $memory.status_squid().$script;
		return null;
	}else{
		if($users->KASPERSKY_WEB_APPLIANCE){echo $memory.status_kav4proxy().$script;return;}
		
		
	}
	
	if($users->SAMBA_INSTALLED){echo $memory.StatusSamba().$script;return null;}
	echo "$script";
	
	}
	
	
function StatusSamba(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$status=new status();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$html=$status->Samba_status();
	return $tpl->_ENGINE_parse_body($html);		
	
}

function status_kav4proxy(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$status=new status();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$html=$status->kav4proxy_status();
	return $tpl->_ENGINE_parse_body($html);		
}

function status_squid(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$status=new status();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$html=$status->Squid_status();
	return $tpl->_ENGINE_parse_body($html);	
}

function status_squid_kav(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$status=new status();
	if($GLOBALS["VERBOSE"]){echo "$page LINE:".__LINE__."\n";}
	$html=$status->Squid_status();
	return $tpl->_ENGINE_parse_body($html);	
}
	



function status_postfix(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($_GET["counter"]==null){$_GET["counter"]=1;}
	if($_GET["counter"]==1){$newcounter=0;}else{$newcounter=1;}
	$counter=Field_hidden('counter',$newcounter);
	$cachefile="/usr/share/artica-postfix/ressources/logs/status.right.1.html";
	$cachemem="/usr/share/artica-postfix/ressources/logs/status.memory.html";
	$memory="<div id='mem_status_computer' style='text-align:center'>". @file_get_contents("$cachemem")."</div>";
	
	
	
	if(!is_file($cachefile)){
		writelogs("$cachefile no such file",__FUNCTION__,__FILE__,__LINE__);
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?right-status=yes");
		return $counter."
		<script>
			setTimeout(\"RestartRightStatus()\",5000);
			function RestartRightStatus(){
				LoadAjax('right_status','admin.index.php?status=right&counter=1');
			}	
		</script>";
		
		
	}
	$status=file_get_contents($cachefile);
	
	
	
	if(strlen(trim($status))<5){
		writelogs(basename($cachefile)." ".strlen($status)." bytes >return back",__FUNCTION__,__FILE__,__LINE__);
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?right-status=yes");
		return $counter."
		<script>
			setTimeout(\"RestartRightStatus()\",5000);
			function RestartRightStatus(){
				LoadAjax('right_status','admin.index.php?status=right&counter=1');
			}	
		</script>
		";
	}
	
	
	return $counter.$tpl->_ENGINE_parse_body($memory.$status)
	."<script>
		LoadAjax('mem_status_computer','$page?memcomputer=yes');
	</script>";
	
	
	;
	
}

function DateDiff($debut, $fin) {

	if(preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#",$debut,$re)){
		$t1=mktime($re[4], $re[5],$re[6], $re[2], $re[3], $re[1]);
	}
	
	if(preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+):([0-9]+)#",$fin,$re)){
		$t2=mktime($re[4], $re[5],$re[6], $re[2], $re[3], $re[1]);
	}	
	

  $t=$t1-$t2;
  if($t==0){return 0;};
  
  
  
  $diff = $t2 - $t1;
  
  return (($diff/60)+1);

}

function status_memdump_js(){
	$page=CurrentPageName();
	$html="
		var x_MemoryStatus= function (obj) {
			var results=obj.responseText;
			document.getElementById('mem_status_computer').innerHTML=results
		
		}	
	
	
		function MemoryStatus(){
			if(!document.getElementById('mem_status_computer')){return;}
			var XHR = new XHRConnection();
			XHR.appendData('memcomputer','yes');
			XHR.sendAndLoad('$page', 'GET',x_MemoryStatus);
		
		}
	MemoryStatus();";
	
	echo $html;
	
}


function status_memdump(){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?mempy=yes");
	$tbl=explode("\n",$datas);
	
	rsort($tbl);
	
	$html="<table class=table_form>";
	
	while (list ($num, $val) = each ($tbl) ){
		if(trim($val)==null){continue;}
		if(preg_match("#=\s+([0-9\.]+)\s+(MiB)\s+(.+)#",$val,$re)){
			$color=CellRollOver();
			if(intval($re[1])>50){$color="style='background-color:#F7D0CC;color:black'";}
			
			$html=$html."<tr $color>
				<td valign='top' width=1%><img src='img/status_service_run.png'></td>
				<td><strong style='font-size:13px'>{$re[3]}</strong></td>
				<td valign='top' width=1% nowrap><strong style='font-size:13px'>{$re[1]} {$re[2]}</strong></td>
				</tr>";
		}
	}
	
	$html="<H1>{memory_use}</H1>".RoundedLightWhite("<div style='width:100%;height:400Px;overflow:auto'>$html.</table></div>");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	





function status_left(){
	$newfrontend=false;
	if(isset($_GET["newfrontend"])){$newfrontend=true;}
	
	$html="
	<div id='status-left'></div>
	<script>
		LoadAjax('status-left','admin.index.loadvg.php');
		LoadAjax('admin-left-infos','admin.index.status-infos.php');
	</script>
	
	";
	echo $html;
	
	}




function warnings_delete_all(){
	$sql="TRUNCATE `notify`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	}














function ShowFileLogs(){
	$file="ressources/logs/{$_GET["ShowFileLogs"]}";
	$datas=file_get_contents($file);
	$datas=htmlentities($datas);
	$datas=nl2br($datas);
	$html="
	<H3>{service_info}</H3>
	<div style='overflow-y:auto'>
	<code style='font-size:10px'>$datas</code>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


FUNCTION CheckTables(){
	$sql=new mysql();
	$sql->BuildTables();	
	
}

FUNCTION CheckDaemon(){
	$sock=new sockets();
	$sock->getfile('CheckDaemon');
	
}

function START_ALL_SERVICES(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?start-all-services=yes");
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text("{start_all_services_perform}")."');";
}

function EmergencyStart(){
	$service_cmd=$_GET["EmergencyStart"];
	$sock=new sockets();
	$datas=$sock->getfile("EmergencyStart:$service_cmd");
	$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				if($arr[md5($val)]==true){continue;}
				$img=statusLogs($val);
			$html=$html . "
			<div style='black;margin-bottom:1px;padding:2px;border-bottom:1px dotted #CCCCCC;border-left:5px solid #CCCCCC;width:98%;'>
			<table style='width:100%'>
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td><td><code style='font-size:10px'>$val</code></td>
			</tr>
			</table>
			</div>";
			$arr[md5($val)]=true;
			
			}
		}	
		
		echo "<div style='width:100%;height:400px;overflow:auto;'>$html</div>";
	
}

function isoqlog(){
	
	
	echo "<input type='hidden' id='switch' value='{$_GET["main"]}'>";
	include_once('isoqlog.php');
	
	
}

function artica_meta(){
	$users=new usersMenus();
	$sock=new sockets();
	$q=new mysql();
	$DisableFrontArticaMeta=$sock->GET_INFO("EnableArticaMeta");
	if(!is_numeric($DisableFrontArticaMeta)){$DisableFrontArticaMeta=0;}
	$EnableArtica=$sock->GET_INFO("EnableArticaMeta");
	
	$ArticaMetaRemoveIndex=$sock->GET_INFO("ArticaMetaRemoveIndex");
	$DisableArticaMetaAgentInformations=$sock->GET_INFO("DisableArticaMetaAgentInformations");
	if($EnableArtica==null){$EnableArtica=1;}
	if($EnableArtica==1){
		if($ArticaMetaRemoveIndex<>1){
			$p=ParagrapheTEXT("artica-meta-32.png","{meta-console}","{meta-console-text}","javascript:Loadjs('artica.meta.php')",null,300);
		}
	}
	
	if($DisableArticaMetaAgentInformations==1){$p=null;}
	if($users->SAMBA_INSTALLED){
		$count=$q->COUNT_ROWS("smbstatus_users", "artica_events");
		if($count>0){
			$p1=ParagrapheTEXT("user-group-32.png", "$count {members_connected}", "{members_connected_samba_text}","javascript:Loadjs('samba.smbstatus.php')",null,300);
		}
	}
	
	
	
	
	$html="$p1$p
	<script>
		CheckSquid();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}





?>