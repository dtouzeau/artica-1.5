<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.backup.inc');
	include_once('ressources/class.os.system.inc');
	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){die();}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["ProcessNice"])){save_process();exit;}
	if(isset($_POST["SyslogNgPref"])){save_process();exit;}
	if(isset($_POST["MysqlNice"])){save_process();exit;}
	if(isset($_GET["js"])){echo js_slider();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){main_status();exit;}
	if(isset($_GET["MX_REQUESTS"])){save_mimedefang();exit;}
	if(isset($_GET["main_config_mysql"])){echo main_config_mysql();exit;}
	
	if(isset($_GET["DisableWarnNotif"])){save_index_page();exit;}
	if(isset($_GET["cron-js"])){echo cron_js();exit;}
	if(isset($_GET["cron-popup"])){echo cron_popup();exit;}
	if(isset($_GET["cron-start"])){echo cron_start();exit;}
	if(isset($_GET["cron-apc"])){echo cron_apc();exit;}
	if(isset($_GET["cron-logon"])){cron_logon();exit;}
	if(isset($_GET["LANGUAGE_SELECTOR_REMOVE"])){cron_logon_save();exit;}
	
	if(isset($_GET["apc-cached-file-list"])){echo cron_apc_list();exit;}
	
	
	if(isset($_GET["cron-index-page"])){cron_index();exit;}
	
	if(isset($_GET["PoolCoverPageSchedule"])){cron_save();exit;}
	if(isset($_GET["MysqlTestsPerfs"])){mysql_test_perfs();exit;}
	
	js();
	
	
function popup(){
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(main_tabs());
	
}

function index(){

	
	//$content=main_config(1);
	
	$html="
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_perf.jpg'></td>
	<td valign='top'><div id='artica_perfomances_services_status'></div></td>
	</tr>
	</table>
	<table style='width:100%'>
	<tr>
		<td colspan=2 valign='top'><br>
		<p style='font-size:14px'>{about_perf}</p>
		</td>
	</tr>
	</table>
	<script>ArticaProcessesChargeLogs()</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
		
}



function cron_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{index_page_settings}');	
		$idmd='ArticaPerformancesSchedule_';
		
$html="	
function {$idmd}StartPage(){
		YahooWin2(550,'$page?cron-start=yes','$title');
		}
		
var x_SaveArticaProcessesSchedule= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				{$idmd}StartPage();
			}		
	
	function SaveArticaProcessesSchedule(){
		var XHR = new XHRConnection();
		XHR.appendData('PoolCoverPageSchedule',document.getElementById('PoolCoverPageSchedule').value);
		XHR.appendData('RTMMailSchedule',document.getElementById('RTMMailSchedule').value);
		document.getElementById('articaschedulesdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveArticaProcessesSchedule);
	
	}		
	
function SaveArticaIndexPage(){
	var XHR = new XHRConnection();
	if(document.getElementById('DisableWarnNotif').checked){XHR.appendData('DisableWarnNotif',1);}else{XHR.appendData('DisableWarnNotif',0);}
	if(document.getElementById('DisableJGrowl').checked){XHR.appendData('DisableJGrowl',1);}else{XHR.appendData('DisableJGrowl',0);}
	if(document.getElementById('DisableFrontEndArticaEvents').checked){XHR.appendData('DisableFrontEndArticaEvents',1);}else{XHR.appendData('DisableFrontEndArticaEvents',0);}
	if(document.getElementById('AllowShutDownByInterface').checked){XHR.appendData('AllowShutDownByInterface',1);}else{XHR.appendData('AllowShutDownByInterface',0);}	
	if(document.getElementById('DisableNoOrganization').checked){XHR.appendData('DisableNoOrganization',1);}else{XHR.appendData('DisableNoOrganization',0);}
	if(document.getElementById('DisableAPTNews').checked){XHR.appendData('DisableAPTNews',1);}else{XHR.appendData('DisableAPTNews',0);}
	if(document.getElementById('DisableWarningCalculation').checked){XHR.appendData('DisableWarningCalculation',1);}else{XHR.appendData('DisableWarningCalculation',0);}
	if(document.getElementById('DisableFrontBrowseComputers').checked){XHR.appendData('DisableFrontBrowseComputers',1);}else{XHR.appendData('DisableFrontBrowseComputers',0);}
	if(document.getElementById('DisableFrontArticaMeta').checked){XHR.appendData('DisableFrontArticaMeta',1);}else{XHR.appendData('DisableFrontArticaMeta',0);}

	

	
	if(document.getElementById('jgrowl_no_kas_update')){
		if(document.getElementById('jgrowl_no_kas_update').checked){
			XHR.appendData('jgrowl_no_kas_update',1);}else{
			XHR.appendData('jgrowl_no_kas_update',0);
		}
	}
	
	if(document.getElementById('jgrowl_no_clamav_update')){	
			if(document.getElementById('jgrowl_no_clamav_update').checked){
			XHR.appendData('jgrowl_no_clamav_update',1);}
			else{XHR.appendData('jgrowl_no_clamav_update',0);
			}
	}
	XHR.appendData('jGrowlMaxEvents',document.getElementById('jGrowlMaxEvents').value)
	
	
	XHR.sendAndLoad('$page', 'GET',x_SaveArticaProcessesSchedule);
	
}
			
{$idmd}StartPage()";
	
echo $html;	
	
}

function save_index_page(){
	$sock=new sockets();
	$sock->SET_INFO("DisableWarnNotif",$_GET["DisableWarnNotif"]);
	$sock->SET_INFO("DisableJGrowl",$_GET["DisableJGrowl"]);
	$sock->SET_INFO("jgrowl_no_clamav_update",$_GET["jgrowl_no_clamav_update"]);
	$sock->SET_INFO("jgrowl_no_kas_update",$_GET["jgrowl_no_kas_update"]);
	$sock->SET_INFO("DisableFrontEndArticaEvents",$_GET["DisableFrontEndArticaEvents"]);
	$sock->SET_INFO("AllowShutDownByInterface",$_GET["AllowShutDownByInterface"]);
	$sock->SET_INFO("jGrowlMaxEvents",$_GET["jGrowlMaxEvents"]);
	$sock->SET_INFO("DisableNoOrganization", $_GET["DisableNoOrganization"]);
	$sock->SET_INFO("DisableAPTNews", $_GET["DisableAPTNews"]);
	$sock->SET_INFO("DisableWarningCalculation", $_GET["DisableWarningCalculation"]);
	$sock->SET_INFO("DisableFrontBrowseComputers", $_GET["DisableFrontBrowseComputers"]);
	$sock->SET_INFO("ArticaMetaRemoveIndex", $_GET["DisableFrontArticaMeta"]);
	
	
	
	
	$sock->getFrameWork("cmd.php?refresh-frontend=yes");
	}

function cron_save(){
	$sock=new sockets();
	$sock->SET_INFO("PoolCoverPageSchedule",$_GET["PoolCoverPageSchedule"]);
	$sock->SET_INFO("RTMMailSchedule",$_GET["RTMMailSchedule"]);
	}

function cron_index(){
	$users=new usersMenus();
	$sock=new sockets();
	$DisableWarnNotif=$sock->GET_INFO("DisableWarnNotif");
	$DisableJGrowl=$sock->GET_INFO("DisableJGrowl");
	$jgrowl_no_clamav_update=$sock->GET_INFO("jgrowl_no_clamav_update");
	$DisableFrontEndArticaEvents=$sock->GET_INFO("DisableFrontEndArticaEvents");
	$jgrowl_no_kas_update=$sock->GET_INFO("jgrowl_no_kas_update");
	$AllowShutDownByInterface=$sock->GET_INFO('AllowShutDownByInterface');
	$jGrowlMaxEvents=$sock->GET_INFO('jGrowlMaxEvents');
	$DisableToolTips=$_COOKIE["DisableToolTips"];
	$DisableHelpToolTips=$_COOKIE["DisableHelpToolTips"];
	$DisableNoOrganization=$sock->GET_INFO('DisableNoOrganization');
	$DisableAPTNews=$sock->GET_INFO('DisableAPTNews');
	$DisableWarningCalculation=$sock->GET_INFO('DisableWarningCalculation');
	$DisableFrontBrowseComputers=$sock->GET_INFO('DisableFrontBrowseComputers');
	$DisableFrontArticaMeta=$sock->GET_INFO('ArticaMetaRemoveIndex');

	
	
	
	
	//no_organization
	
	if($DisableWarnNotif==null){$DisableWarnNotif=0;}
	if($DisableJGrowl==null){$DisableJGrowl=0;}
	if($jgrowl_no_clamav_update==null){$jgrowl_no_clamav_update=0;}
	if($DisableFrontEndArticaEvents==null){$DisableFrontEndArticaEvents=0;}
	if($AllowShutDownByInterface==null){$AllowShutDownByInterface=0;}
	if($ArticaInCgroups==null){$ArticaInCgroups=0;}
	
	
	$DisableWarnNotif=Field_checkbox("DisableWarnNotif",1,$DisableWarnNotif);
	$DisableJGrowl=Field_checkbox("DisableJGrowl",1,$DisableJGrowl);
	$jgrowl_no_clamav_update=Field_checkbox("jgrowl_no_clamav_update",1,$jgrowl_no_clamav_update);
	$DisableFrontEndArticaEvents=Field_checkbox("DisableFrontEndArticaEvents",1,$DisableFrontEndArticaEvents);
	$jgrowl_no_kas_update=Field_checkbox("jgrowl_no_kas_update",1,$jgrowl_no_kas_update);
	$AllowShutDownByInterface=Field_checkbox("AllowShutDownByInterface",1,$AllowShutDownByInterface);
	$DisableNoOrganization=Field_checkbox("DisableNoOrganization",1,$DisableNoOrganization);
	$DisableAPTNews=Field_checkbox("DisableAPTNews",1,$DisableAPTNews);
	$DisableWarningCalculation=Field_checkbox("DisableWarningCalculation",1,$DisableWarningCalculation);
	$DisableFrontBrowseComputers=Field_checkbox("DisableFrontBrowseComputers", 1,$DisableFrontBrowseComputers);
	$DisableFrontArticaMeta=Field_checkbox("DisableFrontArticaMeta", 1,$DisableFrontArticaMeta);

	
	
	
	if($jGrowlMaxEvents==null){$jGrowlMaxEvents=50;}

	$jgrowl_no_kas_update="	<tr>
		<td class=legend>{jgrowl_no_kas_update}:</td>
		<td valign='top'>$jgrowl_no_kas_update</tD>
	</tr>
	<tr><td colspan=2 style='border-top:1px solid #005447'>&nbsp;</td></tr>";
	
	if(!$users->kas_installed){
		$jgrowl_no_kas_update=null;
	}
	
	
	$noclamav="	<tr>
		<td class=legend>{jgrowl_no_clamav_update}:</td>
		<td>$jgrowl_no_clamav_update</tD>
	</tr>
	<tr><td colspan=2 style='border-top:1px solid #005447'>&nbsp;</td></tr>";
	
	
	if($users->KASPERSKY_SMTP_APPLIANCE){
		$noclamav=null;
	}
	
	$html="
	<div class=explain>{frontend_disables_options_explain}</div>
	<div id='articaschedulesdiv'>
<table style='width:100%' class=form>
	<tr>
		<td class=legend>{disable}:{smtp_notification_not_saved}:</td>
		<td valign='top'>$DisableWarnNotif</tD>
	</tr>
	<tr>
		<td class=legend>{disable}:{icon_artica_events_front_end}:</td>
		<td valign='top'>$DisableFrontEndArticaEvents</tD>
	</tr>
	<tr>
		<td class=legend>{disable_jgrowl}:</td>
		<td valign='top'>$DisableJGrowl</tD>
	</tr>	
	<tr>
		<td class=legend>{disable}:{no_organization}:</td>
		<td valign='top'>$DisableNoOrganization</tD>
	</tr>	
	<tr>
		<td class=legend>{DisableAPTNews}:</td>
		<td valign='top'>$DisableAPTNews</tD>
	</tr>
	<tr>
		<td class=legend>{DisableWarningCalculation}:</td>
		<td valign='top'>$DisableWarningCalculation</tD>
	</tr>	
	<tr>
		<td class=legend>{disable}:{browse_computers}:</td>
		<td valign='top'>$DisableFrontBrowseComputers</tD>
	</tr>		
	<tr>
		<td class=legend>{disable}:{meta-console}:</td>
		<td valign='top'>$DisableFrontArticaMeta</tD>
	</tr>		
		
		 
	<tr>
		<td class=legend>{jGrowlMaxEvents}:</td>
		<td valign='top'>". Field_text("jGrowlMaxEvents",$jGrowlMaxEvents,"width:30px")."</tD>
	</tr>	
	
$noclamav
$jgrowl_no_kas_update	
	
	<tr>
		<td class=legend>{enable_shutdown_interface}:</td>
		<td valign='top'>$AllowShutDownByInterface</tD>
	</tr>
	<tr>
		<td class=legend>{disable_tooltips}:</td>
		<td valign='top'>".Field_checkbox("DisableToolTips",1,$DisableToolTips,"DisableToolTipsSave()")."</tD>
	</tr>
	<tr>
		<td class=legend>{disable_help_tooltips}:</td>
		<td valign='top'>".Field_checkbox("DisableHelpToolTips",1,$DisableHelpToolTips,"DisableToolTipsSave()")."</tD>
	</tr>	
	
				
	<td colspan=2 align='right'>
			<hr>". button("{edit}","SaveArticaIndexPage()")."
				
		</td>
	</tr>
</table>
</div>	   


<script>
	function DisableToolTipsSave(){
		var DisableToolTips=0;
		var DisableHelpToolTips=0;
		if(document.getElementById('DisableToolTips').checked){ DisableToolTips=1;}
		if(document.getElementById('DisableHelpToolTips').checked){ DisableHelpToolTips=1;}
		Set_Cookie('DisableToolTips', DisableToolTips, '3600', '/', '', '');
		Set_Cookie('DisableHelpToolTips', DisableHelpToolTips, '3600', '/', '', '');
		CacheOff();
	
	}
</script>


";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"RTMMailConfig.php");	
}

function cron_start(){
	$array["cron-index-page"]="{index_page_settings}";
	$array["cron-popup"]="{ARTICA_PROCESS_SCHEDULE}";
	$array["cron-logon"]="{LOGON_PAGE}";
	
	if(function_exists("apc_cache_info")){
		$array["cron-apc"]="{APP_PHP_APC}";
	}
	
	$page=CurrentPageName();
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
		}
	
	
echo "
	<div id=admin_index_settings style='width:99%;height:auto;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#admin_index_settings').tabs();
			
			
			});
		</script>";		
	
}


function cron_popup(){
	
	
	for($i=2;$i<380;$i++){
		$Cover[$i]=$i;
		
	}
	$sock=new sockets();
	$PoolCoverPageSchedule=$sock->GET_INFO('PoolCoverPageSchedule');
	if($PoolCoverPageSchedule==null){$PoolCoverPageSchedule=20;}
	$PoolCoverPageSchedule=Field_array_Hash($Cover,'PoolCoverPageSchedule',$PoolCoverPageSchedule);
	
	$RTMMailSchedule=$sock->GET_INFO('RTMMailSchedule');
	if($RTMMailSchedule==null){$RTMMailSchedule=35;}
	$RTMMailSchedule=Field_array_Hash($Cover,'RTMMailSchedule',$RTMMailSchedule);	
	
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/cron-128.png'></td>
	<td valign='top'>
	<p class=caption>{ARTICA_PROCESS_SCHEDULE_EXPLAIN}</p>
	<div id='articaschedulesdiv'>
<table style='width:100%'>
	<tr>
		<td class=legend>{ADMIN_COVER_PAGE_STATUS}:</td>
		<td>$PoolCoverPageSchedule&nbsp;mn</tD>
	</tr>
	<tr>
		<td class=legend>{RTMMail}:</td>
		<td>$RTMMailSchedule&nbsp;mn</tD>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>". button("{edit}","SaveArticaProcessesSchedule()")."
				
		</td>
	</tr>
</table>
</div>	   
</td>
</tr>
</table>
";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"RTMMailConfig.php");
}
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{tune_title}');
	$title_mysql=$tpl->_ENGINE_parse_body('{service_performances}');
	$idmd='ArticaPerformancesIndex_';
	
$html="var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;

	function {$idmd}demarre(){
		if(!YahooWinOpen()){return false;}
		{$idmd}tant = {$idmd}tant+1;
		{$idmd}reste=10-{$idmd}tant;
		if ({$idmd}tant < 10 ) {                           
			{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",3000);
	      } else {
			{$idmd}tant = 0;
			{$idmd}ChargeLogs();
			{$idmd}demarre();                                
	   }
	}
	
	
	function {$idmd}StartPage(){
		YahooWin(820,'$page?popup=yes','$title');
		setTimeout(\"{$idmd}ChargeLogs();\",1000);	
		setTimeout(\"{$idmd}demarre()\",1000);
	}	


	function {$idmd}ChargeLogs(){
		LoadAjax('artica_perfomances_services_status','$page?status=yes');
	}

	function ArticaProcessesChargeLogs(){
		{$idmd}ChargeLogs();
		setTimeout(\"{$idmd}demarre()\",1000);
	}
	
	function refresh_services(){
		{$idmd}ChargeLogs();
	}
	

	
	
function LoadAjaxLocal(ID,uri) {
		var XHR = new XHRConnection();
		XHR.setRefreshArea(ID);
		xID=ID;
		document.getElementById(ID).innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait.gif\"></center>';
		XHR.sendAndLoad(uri,\"GET\",x_ajax);
}

var x_ajax= function (obj) {
	var tempvalue=obj.responseText;
	document.getElementById(xID).innerHTML=tempvalue;
	StartSlider();
}

function setSliderVal(value){
document.getElementById('v').value=value;
ChargeLogs();
}
//0=>'{select}',1=>high,2=>medium,3=>low,4=>very_low

function mimedefang_macro(){
	var macro=document.getElementById('mimedefang_macro').value;
	if(macro=='1'){
		document.getElementById('MX_REQUESTS').value=1000;
		document.getElementById('MX_MINIMUM').value=5;
		document.getElementById('MX_MAXIMUM').value=50;		
		document.getElementById('MX_MAX_RSS').value=100000;				
		document.getElementById('MX_MAX_AS').value=300000;						
		}
	
	if(macro=='2'){
		document.getElementById('MX_REQUESTS').value=200;
		document.getElementById('MX_MINIMUM').value=2;
		document.getElementById('MX_MAXIMUM').value=10;		
		document.getElementById('MX_MAX_RSS').value=100000;				
		document.getElementById('MX_MAX_AS').value=500000;						
		}	

	if(macro=='3'){
		document.getElementById('MX_REQUESTS').value=100;
		document.getElementById('MX_MINIMUM').value=2;
		document.getElementById('MX_MAXIMUM').value=5;		
		document.getElementById('MX_MAX_RSS').value=100000;				
		document.getElementById('MX_MAX_AS').value=200000;						
		}
	if(macro=='4'){
		document.getElementById('MX_REQUESTS').value=50;
		document.getElementById('MX_MINIMUM').value=1;
		document.getElementById('MX_MAXIMUM').value=2;		
		document.getElementById('MX_MAX_RSS').value=90000;				
		document.getElementById('MX_MAX_AS').value=150000;						
		}				
}

{$idmd}StartPage();
	
";	
	
echo $html;

}

function main_tabs(){
	
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["artica_process"]='{artica_process}';
	$array["optimize"]='{optimization}';
	$array["cgroups"]='{APP_CGROUPS}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="optimize"){$html[]= "<li><a href=\"artica.optimize.php\"><span>$ligne</span></a></li>\n";continue;}
		if($num=="cgroups"){$html[]= "<li><a href=\"cgroups.php\"><span>$ligne</span></a></li>\n";continue;}
		$html[]= "<li><a href=\"$page?main=$num&hostname=$hostname\"><span>$ligne</span></a></li>\n";
		
		}
	
	
	return "
	<div id=main_config_articaproc style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_articaproc').tabs();
			

			});
		</script>";		
}


	
	
function main_page(){
	

	
	$html=
	"<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_perf.jpg'>	<p class=caption>{about_perf}</p></td>
	<td valign='top'><div id='services_status'></div></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();ChargeLogs();LoadAjaxLocal('main_config','$page?main=yes');</script>
	
	";
	//slider-thumb
	$tpl=new template_users('{tune_title}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "index":index();exit;break;
		case "artica_process":main_config();exit;break;
		default:
			break;
	}
	
	
}	

function main_config($return=0){
	$users=new usersMenus();
	$html="
	<table style='width:97%'>
	<td valign='top' width=50%>".
	main_config_artica().
	main_warn_preload().
	main_config_syslogng().main_config_mimedefang().
	"</td>
	</tr>
	</table>";
	

	if($return==1){return $html;}

	echo $html;
	
}
	
function main_config_artica(){
	//ArticaPerformancesSettings
	$sock=new sockets();
	$users=new usersMenus();
	$page=CurrentPageName();
	
	$MaxtimeBackupMailSizeCalculate=trim($sock->GET_INFO("MaxtimeBackupMailSizeCalculate"));
	$systemForkProcessesNumber=trim($sock->GET_INFO("systemForkProcessesNumber"));
	$cpulimit=trim($sock->GET_INFO("cpulimit"));
	$cpuLimitEnabled=trim($sock->GET_INFO("cpuLimitEnabled"));
	$SystemV5CacheEnabled=trim($sock->GET_INFO("SystemV5CacheEnabled"));
	$NoDryReboot=$sock->GET_INFO("NoDryReboot");
	$NoOutOfMemoryReboot=$sock->GET_INFO("NoOutOfMemoryReboot");
	$AutoRebootSchedule=$sock->GET_INFO("AutoRebootSchedule");
	$EnableBandwithCalculation=$sock->GET_INFO("EnableBandwithCalculation");
	$ArticaInCgroups=$sock->GET_INFO('ArticaInCgroups');
	$ArticaCgroup=$sock->GET_INFO('ArticaCgroup');
	$systemMaxOverloaded=trim($sock->GET_INFO("systemMaxOverloaded"));
	$AutoRebootScheduleText=trim($sock->GET_INFO("AutoRebootScheduleText"));
	
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	$CGROUPS_INSTALLED=0;
	if($users->CGROUPS_INSTALLED){$CGROUPS_INSTALLED=1;}
	

	if(strlen(trim($SystemV5CacheEnabled))==0){$SystemV5CacheEnabled=0;}
	if(!is_numeric($NoDryReboot)){$NoDryReboot=0;}
	if(!is_numeric($NoOutOfMemoryReboot)){$NoOutOfMemoryReboot=0;}
	if(!is_numeric($cpuLimitEnabled)){$sock->SET_INFO("cpuLimitEnabled",0);$cpuLimitEnabled=0;}
	if(!is_numeric($MaxtimeBackupMailSizeCalculate)){$MaxtimeBackupMailSizeCalculate=300;}
	if(!is_numeric($cpulimit)){$cpulimit=0;}
	if(!is_numeric($cgroupsEnabled)){$cgroupsEnabled=0;}
	if(!is_numeric($AutoRebootSchedule)){$AutoRebootSchedule=0;}
	
	
	
	$q=new mysql();
	$sql="SELECT groupname  FROM cgroups_groups ORDER BY groupname";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$cgroups[$ligne["groupname"]]=	$ligne["groupname"];	
		
	}
	$CountDeCgroups=count($cgroups);


if($users->POSTFIX_INSTALLED){
	$backupmailsize="<tr>
	<td nowrap width=1% align='right' class=legend>{MaxtimeBackupMailSizeCalculate}:</td>
	<td nowrap>". Field_text("MaxtimeBackupMailSizeCalculate",$MaxtimeBackupMailSizeCalculate,"width:60px;font-size:13px;padding:3px")."&nbsp;{minutes}</td>
	<td>" . help_icon("{MaxtimeBackupMailSizeCalculate_explain}")."</td>
</tr>";
}

$ini=new Bs_IniHandler();


$ini->loadString($sock->GET_INFO("ArticaPerformancesSettings"));
$page=CurrentPageName();

$arrp=array(10=>"{default}",-15=>"{high}",10=>"{medium}",12=>"{low}",19=>'{very_low}');
$cpulimit_array=array(
	0=>"{no_limit}",
	10=>"10%",
	20=>"20%",
	30=>"30%",
	35=>"35%",
	40=>"40%",
	45=>"45%",
	50=>"50%",
	55=>"55%",
	60=>"60%",
	65=>"65%",
	70=>"70%",
	75=>"75%",
	80=>"80%",
	85=>"85%",
	90=>"90%",
	95=>"95%",		
);


$arrp=Field_array_Hash($arrp,'ProcessNice',$ini->_params["PERFORMANCES"]["ProcessNice"]);
$cpulimit_f=Field_array_Hash($cpulimit_array,'cpulimit',$cpulimit);


	$arrp_mysql=array(null=>"{default}",0=>"{ISP_MODE}",1=>"{high}",2=>"{medium}",3=>"{low}",4=>'{very_low}');
	$mysql_nice=Field_array_Hash($arrp_mysql,'MysqlNice',$ini->_params["PERFORMANCES"]["MysqlNice"]);



if($ini->_params["PERFORMANCES"]["NoBootWithoutIP"]==null){$ini->_params["PERFORMANCES"]["NoBootWithoutIP"]=0;}
if($ini->_params["PERFORMANCES"]["useIonice"]==null){$ini->_params["PERFORMANCES"]["useIonice"]=1;}
$icon_schedule=Buildicon64("DEF_ICO_ARTICA_CRON_SCHEDULE");
$icon_phlisight=Paragraphe("philesight-64.png","{APP_PHILESIGHT}","{APP_PHILESIGHT_PARAMETERS}","javascript:Loadjs('philesight.php?js-settings=yes')");
$MaxMailEventsLogs=$sock->GET_INFO("MaxMailEventsLogs");	
if($MaxMailEventsLogs==null){$MaxMailEventsLogs=400000;}
if($MaxMailEventsLogs<100){$MaxMailEventsLogs=4000;}
$html="
<div id=ffm1>
<table style='width:100%'>
<tbody>
<tr>
	<td valign='top'>
		
		<table style='width:100%' class=form>
		<tbody>
		<tr>
			<td nowrap width=1% align='right' class=legend>{artica_process}:</td>
			<td>$arrp</td>
			<td>" . help_icon("{artica_process_explain}")."</td>
		</tr>
		<tr>
			<td nowrap width=1% align='right' class=legend>{ArticaInCgroups}:</td>
			<td>" . Field_checkbox("ArticaInCgroups",1,$ArticaInCgroups,"ArticaCgroupCheck()")."</td>
			<td>" . help_icon("{ArticaInCgroups_explain}")."</td>
		</tr>
		<tr>
			<td nowrap width=1% align='right' class=legend>{APP_CGROUPS}:</td>
			<td>" . Field_array_Hash($cgroups,"ArticaCgroup",null,$ArticaCgroup)."</td>
			<td>&nbsp;</td>
		</tr>					
		<tr>
			<td nowrap width=1% align='right' class=legend>{cpuLimitEnabled}:</td>
			<td>" . Field_checkbox("cpuLimitEnabled",1,$cpuLimitEnabled,"CheckCPULimit()")."</td>
			<td>" . help_icon("{cpuLimitEnabled_explain}")."</td>
		</tr>			
		
		<tr>
			<td nowrap width=1% align='right' class=legend>{cpulimit}:</td>
			<td>$cpulimit_f</td>
			<td>" . help_icon("{artica_cpulimit_explain}")."</td>
		</tr>
		
		<tr>
			<td nowrap width=1% align='right' class=legend>{systemMaxOverloaded}:</td>
			<td nowrap>". Field_text("systemMaxOverloaded",$systemMaxOverloaded,"width:60px;font-size:13px;padding:3px")."&nbsp;{load}</td>
			<td>" . help_icon("{systemMaxOverloaded_explain}")."</td>
		</tr>
		<tr>
			<td nowrap width=1% align='right' class=legend>{systemForkProcessesNumber}:</td>
			<td nowrap>". Field_text("systemForkProcessesNumber",$systemForkProcessesNumber,"width:60px;font-size:13px;padding:3px")."&nbsp;{processes}</td>
			<td>" . help_icon("{systemForkProcessesNumber_explain}")."</td>
		</tr>		
		
		<tr>
			<td nowrap width=1% align='right' class=legend>{mysql_server_consumption}:</td>
			<td>$mysql_nice</td>
			<td>" . help_icon("{mysql_server_text}")."</td>
		</tr>
		
		<tr>
			<td nowrap width=1% align='right' class=legend>{SystemV5CacheEnabled}:</td>
			<td>" . Field_checkbox("SystemV5CacheEnabled",1,$SystemV5CacheEnabled)."</td>
			<td>" . help_icon("{SystemV5CacheEnabled_explain}")."</td>
		</tr>	
		
				
		<tr>
			<td nowrap width=1% align='right' class=legend>{useIonice}:</td>
			<td>" . Field_checkbox("useIonice",1,$ini->_params["PERFORMANCES"]["useIonice"])."</td>
			<td>" . help_icon("{useIonice_explain}")."</td>
		</tr>
		
		<tr>
			<td nowrap width=1% align='right' class=legend>{NoBootWithoutIP}:</td>
			<td>" . Field_checkbox("NoBootWithoutIP",1,$ini->_params["PERFORMANCES"]["NoBootWithoutIP"])."</td>
			<td>" . help_icon("{NoBootWithoutIP_explain}")."</td>
		</tr>
		<tr>
			<td nowrap width=1% align='right' class=legend>{DisableFollowServiceHigerThan1G}:</td>
			<td>" . Field_checkbox("DisableFollowServiceHigerThan1G",1,$ini->_params["PERFORMANCES"]["DisableFollowServiceHigerThan1G"])."</td>
			<td>" . help_icon("{DisableFollowServiceHigerThan1G_explain}")."</td>
		</tr>
		<tr>
			<td nowrap width=1% align='right' class=legend>{EnableArticaWatchDog}:</td>
			<td>" . Field_checkbox("EnableArticaWatchDog",1,$sock->GET_INFO('EnableArticaWatchDog'))."</td>
			<td>" . help_icon("{EnableArticaWatchDog_explain}")."</td>
		</tr>
		<tr>
			<td nowrap width=1% align='right' class=legend>{EnableBandwithCalculation}:</td>
			<td>" . Field_checkbox("EnableBandwithCalculation",1,$EnableBandwithCalculation)."</td>
			<td>" . help_icon("{EnableBandwithCalculation_explain}")."</td>
		</tr>		
		$backupmailsize
		<tr>
			<td nowrap width=1% align='right' class=legend>{MaxEventsInDatabase} (mail):</td>
			<td nowrap>". Field_text("MaxMailEventsLogs",$MaxMailEventsLogs,"width:90px;font-size:13px;padding:3px")."</td>
			<td></td>
		</tr>		
			<td colspan=3 align='right'><hr>". button("{edit}","SavePerformancesMasterForm()")."</td>
		</tr>
		</tbody>
		</table>
		<input type='hidden' id='AutoRebootScheduleText' value='$AutoRebootScheduleText'>		
		<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td colspan=3><div style='font-size:16px'>{reboot}</td>
			</tr>
			<tr>
				<td nowrap width=1% align='right' class=legend>{NoDryReboot}:</td>
				<td>" . Field_checkbox("NoDryReboot",1,$NoDryReboot)."</td>
				<td>" . help_icon("{NoDryReboot_explain}")."</td>
			</tr>
			<tr>
				<td nowrap width=1% align='right' class=legend>{NoOutOfMemoryReboot}:</td>
				<td>" . Field_checkbox("NoOutOfMemoryReboot",1,$NoOutOfMemoryReboot)."</td>
				<td>" . help_icon("{NoOutOfMemoryReboot_explain}")."</td>
			</tr>
			<tr>
				<td nowrap width=1% align='right' class=legend>{scheduled_reboot}:</td>
				<td>" . Field_checkbox("AutoRebootSchedule",1,$AutoRebootSchedule,'CheckRebootSchedule()')."</td>
				<td align=left><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('cron.php?field=AutoRebootScheduleText')\" style='font-size:13px;text-decoration:underline;color:black' id='scheduleAID2'>{schedule}</a></td>
			</tr>
		</tr>		
			<td colspan=3 align='right'><hr>". button("{edit}","SavePerformancesMasterForm()")."</td>
		</tr>			
		</tbody>							
		</table>
		
</form>
</td>
<td valign='top'>
	$icon_schedule$icon_phlisight
</td>
</tr>
</tbody>
</table>
</div>
<script>
	var x_SavePerformancesMasterForm= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}
		RefreshTab('main_config_articaproc');
	}


	function SavePerformancesMasterForm(){
		var XHR=XHRParseElements('ffm1');
		AnimateDiv('ffm1');
		XHR.sendAndLoad('$page', 'POST',x_SavePerformancesMasterForm);
	
	}



	function ArticaCgroupCheck(){
		
		var CGROUPS_INSTALLED=$CGROUPS_INSTALLED;
		var cgroupsEnabled=$cgroupsEnabled;
		var CountDeCgroups=$CountDeCgroups;
		document.getElementById('ArticaCgroup').disabled=true;
		document.getElementById('ArticaInCgroups').disabled=true;
		
		if(CGROUPS_INSTALLED==0){return;}
		if(cgroupsEnabled==0){return;}
		if(CountDeCgroups==0){return;}
		document.getElementById('ArticaCgroup').disabled=false;
		document.getElementById('ArticaInCgroups').disabled=false;	
			 
		if(document.getElementById('ArticaInCgroups').checked){
			document.getElementById('cpuLimitEnabled').disabled=true;
			document.getElementById('ProcessNice').disabled=true;
			document.getElementById('cpulimit').disabled=true;
			document.getElementById('useIonice').disabled=true;
			
		}else{
			document.getElementById('cpuLimitEnabled').disabled=false;
			document.getElementById('useIonice').disabled=false;
			document.getElementById('ProcessNice').false=true;
			CheckCPULimit();		
		}
		
	}
	
	
	function CheckCPULimit(){
		document.getElementById('cpulimit').disabled=true;
		if(document.getElementById('cpuLimitEnabled').disabled){return;}
		if(document.getElementById('cpuLimitEnabled').checked){
			document.getElementById('cpulimit').disabled=false;
		}
	}
	
		function CheckRebootSchedule(){
			if(!document.getElementById('AutoRebootSchedule').checked){
				document.getElementById('scheduleAID2').style.color='#CCCCCC';
			}else{
				document.getElementById('scheduleAID2').style.color='black';
			}
		
		}
		
	

ArticaCgroupCheck();
CheckCPULimit();
CheckRebootSchedule();
</script>

";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}


function main_config_mysql(){
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();
	$sock=new sockets();
	$ini->loadString($sock->GET_INFO("ArticaPerformancesSettings"));
		
$tpl=new templates();
$title_perfs=$tpl->_ENGINE_parse_body('{service_performances}');
	$testperfs="javascript:YahooWin3(400,'artica.performances.php?MysqlTestsPerfs=yes','$title_perfs');";
	$users=new usersMenus();
	if(!$users->mysql_installed){return "no";}
	$arrp=array(null=>"{default}",0=>"{ISP_MODE}",1=>"{high}",2=>"{medium}",3=>"{low}",4=>'{very_low}');
	$arrp=Field_array_Hash($arrp,'MysqlNice',$ini->_params["PERFORMANCES"]["MysqlNice"]);
	$html="<H5>{mysql_server_consumption}</h5>
	<p class=caption>{mysql_server_text}</p>
<form name=ffmsql>
<table style='width:100%' class=table_form>
<tr>
	<td nowrap width=1% align='right'><strong>{mysql_server_consumption}:</strong></td>
	<td>$arrp</td>
	<td>" . help_icon("{mysql_server_text}")."</td>
</tr>
<tr>
	<td colspan=3 align='right'>". button("{edit}","ParseForm('ffmsql','$page',true);")."
</tr>
<tr>
	<td colspan=3 align='right'>". button("{service_performances}",$testperfs)."
</tr>
</table>
</form>	
	
	";
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}


function main_warn_preload(){
	return null;
	$users=new usersMenus();
	if($users->preload_installed){return null;}
	$html="<H5>{APP_PRELOAD_NOTINSTALLED}</h5>
	<p class=caption>{APP_PRELOAD_NOTINSTALLED_TEXT}</p>";
	
$tpl=new templates();
return "<div style='float:left;margin:4px;width:300px'>".RoundedLightGrey($tpl->_ENGINE_parse_body($html))."</div>";		
	
}

function main_config_syslogng(){
	$users=new usersMenus();
	if(!$users->syslogng_installed){return null;}
	$arrp=array(null=>'{select}',1=>"{all}",2=>"{only_mail}",3=>"{only_errors}",4=>'{no_sql_injection}');
	$page=CurrentPageName();

	$sock=new sockets();
	$performances=$sock->GET_INFO("ArticaPerformancesSettings");
	$ini=new Bs_IniHandler();
	$ini->loadString($performances);		
if($ini->_params["PERFORMANCES"]["SyslogNgPref"]==null){$ini->_params["PERFORMANCES"]["SyslogNgPref"]=1;}
if($ini->_params["PERFORMANCES"]["syslogng_log_fifo_size"]==null){$ini->_params["PERFORMANCES"]["syslogng_log_fifo_size"]=2048;}
if($ini->_params["PERFORMANCES"]["syslogng_sync"]==null){$ini->_params["PERFORMANCES"]["syslogng_sync"]=0;}
if($ini->_params["PERFORMANCES"]["syslogng_max_connections"]==null){$ini->_params["PERFORMANCES"]["syslogng_max_connections"]=50;}



	$arrp=Field_array_Hash($arrp,'SyslogNgPref',$ini->_params["PERFORMANCES"]["SyslogNgPref"]);
	$html="<H5>{syslog_server_consumption}</h5>
	<p class=caption>{syslog_server_consumption_text}</p>
<form name=ffmsyslog>
<table style='width:100%' class=table_form>
<tr>
	<td nowrap width=1% align='right'><strong>{syslog_server_consumption}:</strong></td>
	<td>$arrp</td>
	<td>" . help_icon('{syslogng_intro}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{log_fifo_size}:</strong></td>
	<td>" . Field_text('syslogng_log_fifo_size',$ini->_params["PERFORMANCES"]["syslogng_log_fifo_size"])."</td>
	<td>" . help_icon('{log_fifo_size_text}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{syslogng_sync}:</strong></td>
	<td>" . Field_text('syslogng_sync',$ini->_params["PERFORMANCES"]["syslogng_sync"])."</td>
	<td>" . help_icon('{syslogng_sync_text}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{syslogng_max_connections}:</strong></td>
	<td>" . Field_text('syslogng_max_connections',$ini->_params["PERFORMANCES"]["syslogng_max_connections"])."</td>
	<td>" . help_icon('{syslogng_max_connections_text}')."</td>
</tr>


<tr>
	<td colspan=2 align='right'><input type=button OnClick=\"javascript:ParseForm('ffmsyslog','$page',true);\" value='{edit}&nbsp;&raquo;'>
</tr>
</table>
</form>	
	
	";
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
	
}


function main_config_mimedefang(){
	$users=new usersMenus();
	if(!$users->MIMEDEFANG_INSTALLED){return null;}
	$users->LoadModulesEnabled();
	if($users->MimeDefangEnabled<>1){return null;}
	
$sock=new sockets();
$ini=new Bs_IniHandler();
$ini->loadString($sock->GET_INFO("ArticaPerformancesSettings"));	

$MX_REQUESTS=$ini->_params["MIMEDEFANG"]["MX_REQUESTS"];
if($MX_REQUESTS==null){$MX_REQUESTS=200;}

$MX_MINIMUM=$ini->_params["MIMEDEFANG"]["MX_MINIMUM"];
if($MX_MINIMUM==null){$MX_MINIMUM=2;}

$MX_MAXIMUM=$ini->_params["MIMEDEFANG"]["MX_MAXIMUM"];
if($MX_MAXIMUM==null){$MX_MAXIMUM=10;}

$MX_MAX_RSS=$ini->_params["MIMEDEFANG"]["MX_MAX_RSS"];
if($MX_MAX_RSS==null){$MX_MAX_RSS=30000;}

$MX_MAX_AS=$ini->_params["MIMEDEFANG"]["MX_MAX_AS"];
if($MX_MAX_AS==null){$MX_MAX_AS=90000;}

if($MX_REQUESTS>900){$mimedefang_macro=1;}
if($MX_REQUESTS<300){$mimedefang_macro=2;}
if($MX_REQUESTS<101){$mimedefang_macro=3;}
if($MX_REQUESTS<60){$mimedefang_macro=4;}



$arrp=array(0=>'{select}',1=>"{high}",2=>"{medium}",3=>"{low}",4=>'{very_low}');
$arrp=Field_array_Hash($arrp,'mimedefang_macro',$mimedefang_macro,"mimedefang_macro()");
	


$html="<H5>{mimedefang_consumption}</h5>
	<p class=caption>{mimedefang_consumption_text}</p>
	
	<table style='width:100%'>
		<tr>
		<td nowrap width=1% align='right'><strong>{mimedefang_macro}:</strong></td>
		<td>$arrp</td>	
		</tr>
	</table>
	
<form name=ffmmimedefang>
<table style='width:100%'>
<tr>
	<td nowrap width=1% align='right'><strong>{MX_REQUESTS}:</strong></td>
	<td>".Field_text('MX_REQUESTS',$MX_REQUESTS,'width:90px')."</td>
	<td nowrap width=1% align='left'>".help_icon('{MX_REQUESTS_TEXT}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{MX_MINIMUM}:</strong></td>
	<td>".Field_text('MX_MINIMUM',$MX_MINIMUM,'width:90px')."</td>
	<td nowrap width=1% align='left'>".help_icon('{MX_MINIMUM_TEXT}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{MX_MAXIMUM}:</strong></td>
	<td>".Field_text('MX_MAXIMUM',$MX_MAXIMUM,'width:90px')."</td>
	<td nowrap width=1% align='left'>".help_icon('{MX_MAXIMUM_TEXT}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{MX_MAX_RSS}:</strong></td>
	<td>".Field_text('MX_MAX_RSS',$MX_MAX_RSS,'width:90px')."</td>
	<td nowrap width=1% align='left'>".help_icon('{MX_MAX_RSS_TEXT}')."</td>
</tr>
<tr>
	<td nowrap width=1% align='right'><strong>{MX_MAX_AS}:</strong></td>
	<td>".Field_text('MX_MAX_AS',$MX_MAX_AS,'width:90px')."</td>
	<td nowrap width=1% align='left'>".help_icon('{MX_MAX_AS_TEXT}')."</td>
</tr>
<tr>
	<td colspan=2 align='right'><input type=button OnClick=\"javascript:ParseForm('ffmmimedefang','$page',true);\" value='{edit}&nbsp;&raquo;'>
</tr>
</table>
</form>	";	
$tpl=new templates();
return "<div style='float:left;margin:4px;width:300px'>".RoundedLightGrey($tpl->_ENGINE_parse_body($html))."</div>";	
	
}



function main_status(){
	include_once("ressources/class.os.system.tools.inc");
	$os=new os_system();
	$arraycpu=$os->cpu_info();
	$cpuspeed=round($arraycpu["cpuspeed"]/1000*100)/100; 
	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/64-computer.png'></td>
	<td valign='top'>
	<table style='width:100%'>
		<tr>
			<td width=1% nowrap align='right' valign='top'>cpu:</td>
			<td width=99%><strong>{$arraycpu["model"]}</strong></td>
		</tr>
		<tr>
			<td width=1% nowrap align='right' valign='top'>Cache:</td>
			<td width=99%><strong>{$arraycpu["cache"]}</strong></td>
		</tr>		
		<tr>
			<td width=1% nowrap align='right'>{cpu_number}:</td>
			<td width=99%>{$arraycpu["cpus"]}</td>
		</tr>
		<tr>
			<td width=1% nowrap align='right'>{status}:</td>
			<td width=99%>{$cpuspeed}GHz</td>
		</tr>					
	</table>
	</td>
	</tr>
	</table>
	";
	$mem=$os->html_Memory_usage();
	
	$mem=RoundedLightGreen($mem);
	
	$html=RoundedLightGreen($html)."<br>$mem";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	

}


function save_process(){
$sock=new sockets();	
$ini=new Bs_IniHandler();
$ini->loadString($sock->GET_INFO("ArticaPerformancesSettings"));
if(isset($_POST["cpuLimitEnabled"])){$sock->SET_INFO('cpuLimitEnabled',$_POST["cpuLimitEnabled"]);}
if(isset($_POST["systemMaxOverloaded"])){$sock->SET_INFO('systemMaxOverloaded',$_POST["systemMaxOverloaded"]);}
if(isset($_POST["systemForkProcessesNumber"])){$sock->SET_INFO('systemForkProcessesNumber',$_POST["systemForkProcessesNumber"]);}
if(isset($_POST["SystemV5CacheEnabled"])){$sock->SET_INFO('SystemV5CacheEnabled',$_POST["SystemV5CacheEnabled"]);}
if(isset($_POST["MaxMailEventsLogs"])){$sock->SET_INFO('MaxMailEventsLogs',$_POST["MaxMailEventsLogs"]);}
if(isset($_POST["ArticaCgroup"])){$sock->SET_INFO('ArticaCgroup',$_POST["ArticaCgroup"]);}
if(isset($_POST["ArticaInCgroups"])){$sock->SET_INFO('ArticaInCgroups',$_POST["ArticaInCgroups"]);}

if(isset($_POST["AutoRebootScheduleText"])){$sock->SET_INFO('AutoRebootScheduleText',$_POST["AutoRebootScheduleText"]);}
if(isset($_POST["AutoRebootSchedule"])){$sock->SET_INFO('AutoRebootSchedule',$_POST["AutoRebootSchedule"]);}
if(isset($_POST["AutoRebootSchedule"])){$sock->getFrameWork("services.php?AutoRebootSchedule=yes");}

 


if(isset($_POST["NoDryReboot"])){
	$sock->SET_INFO('NoDryReboot',$_POST["NoDryReboot"]);
	$sock->getFrameWork("services.php?syslogger=yes");
}
if(isset($_POST["NoOutOfMemoryReboot"])){
	$sock->SET_INFO('NoOutOfMemoryReboot',$_POST["NoOutOfMemoryReboot"]);
	$sock->getFrameWork("services.php?syslogger=yes");
}

if(isset($_GET["EnableBandwithCalculation"])){$sock->SET_INFO('EnableBandwithCalculation',$_POST["EnableBandwithCalculation"]);}



	
	while (list ($num, $val) = each ($_POST) ){
		if(strpos($val, "javascript")>0){continue;}
		writelogs("Save $num == '$val'",__FUNCTION__,__FILE__,__LINE__);
		$ini->_params["PERFORMANCES"][$num]=$val;
		
	}
	
	
$sock->SaveConfigFile($ini->toString(),"ArticaPerformancesSettings");
$sock->getFrameWork('cmd.php?replicate-performances-config=yes');
$sock->getFrameWork('cmd.php?RestartDaemon=yes');


if(isset($_POST["MysqlNice"])){
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?restart-mysql=yes');
	
}

/*SyslogNgPref	4
syslogng_log_fifo_size	2048
syslogng_sync	0
*/

if(isset($_POST["SyslogNgPref"])){
	$sock=new sockets();
	$sock->getfile('restartsyslogng');	
	$sock->getfile("restartmysqldependencies");
}

if(isset($_POST["MaxtimeBackupMailSizeCalculate"])){
	$sock=new sockets();
	if($_GET["MaxtimeBackupMailSizeCalculate"]<20){$_POST["MaxtimeBackupMailSizeCalculate"]=20;}
	$sock->SET_INFO("MaxtimeBackupMailSizeCalculate",$_POST["MaxtimeBackupMailSizeCalculate"]);
}
	
}

function save_mimedefang(){
$artica=new artica_general();
$ini=new Bs_IniHandler();
$ini->loadString($artica->ArticaPerformancesSettings);
	
	while (list ($num, $val) = each ($_GET) ){
		$ini->_params["MIMEDEFANG"][$num]=$val;
		
	}
$artica->ArticaPerformancesSettings=$ini->toString();
$artica->Save();
	$sock=new sockets();
	$sock->getfile('restartmimedefang');
		
	
}

function mysql_test_perfs(){
	
	$sock=new sockets();
	$q=new mysql();
	$time=$sock->getFrameWork("cmd.php?MySqlPerf=yes&username=$q->mysql_admin&pass=$q->mysql_password&host=$q->mysql_server&port=$q->mysql_port");

	$html="
	
	
	<div style='font-size:18px'>{service_performances}</div>
	<span style='font-size:14px;font-weight:bold;color:red'>{benchmark_result}: <code>$time seconds</code></span>
	<H2>{others_benchmarks}</H2>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>Dual core 3Ghz / 4 Go Mem</td>
		<td><strong style='font-size:12px'>1.36 seconds</strong></td>
	</tr>
	<tr>
		<td class=legend>AMD 64 3200+</td>
		<td><strong style='font-size:12px'>4.92 seconds</strong></td>
	</tr>
	<tr>
		<td class=legend>Intel Pentium 4 Dual Core (3.20 GHz)</td>
		<td><strong style='font-size:12px'>3.76 seconds</strong></td>
	</tr>	
	<tr>
		<td class=legend>Intel Xeon x2 (3.00 GHz)</td>
		<td><strong style='font-size:12px'>3.43 seconds</strong></td>
	</tr>			
	<tr>
	<td class=legend>AMD Athlon(tm) 64 X2 Dual Core Processor 4200+</td>
	<td><strong style='font-size:12px'>2.94 seconds</strong></td>
	</tr>
	<tR>
	<td class=legend>Intel(R) Core(TM)2 Duo CPU E7200 @ 2.53GHz</td>
	<td><strong style='font-size:12px'>2.49 seconds</strong></td>
	</tr>
	<tR>
	<td class=legend>Bi xeon 2.66 4 Go Mem</td>
	<td><strong style='font-size:12px'>1.59 seconds</strong></td>
	</tr>
	<tR>
	<td class=legend>Intel C2D T7200 @2GHz, 3Go Mem 64bits</td>
	<td><strong style='font-size:12px'>1.96 seconds</strong></td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function cron_apc(){
	$array=parsePHPModules();
	$array=$array["apc"];
	$page=CurrentPageName();
	$apc_cache_info=apc_cache_info();
	//print_r($apc_cache_info);
	
	while (list ($num, $val) = each ($apc_cache_info) ){
		if(is_array($val)){continue;}
		
		if($num=="file_upload_progress"){continue;}
		if($num=="start_time"){
			$val=date('M d D H:i:s',$val);
		}
		
		if($num=="mem_size"){
			$val=FormatBytes(($val/1024));
		}
		
		
		$html=$html."
		<tr>
			<td class=legend>{{$num}}:</td>
			<td><strong>$val</strong></td>
		</tr>
		
		";
	}
	
	$html=$html."
		<tr>
			<td class=legend>{cached_files_number}:</td>
			<td><strong>". count($apc_cache_info["cache_list"])."</strong></td>
		</tr>
	";
	$html="
	<H1>APC V.{$array["Version"]} {$array["Revision"]}</H1>
	<table style='width:100%'>
	$html
	</table>
	<div style='text-align:right'>". texttooltip("{cached_files}","{cached_files_list}","APCCachedFileList()")."</div>
	
	<script>
		function APCCachedFileList(){
			YahooWin3('650','$page?apc-cached-file-list=yes');
		}
	</script>";
	


	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function cron_logon(){
	$sock=new sockets();
	$logon_parameters=unserialize(base64_decode($sock->GET_INFO("LogonPageSettings")));
	$page=CurrentPageName();
	$lang["en"]="English";
	$lang["fr"]="Francais";
	$lang["po"]="Portugues";
	$lang["br"]="Brazilian";
	$lang["es"]="Espanol";
	$lang["it"]="Italiano";
	$lang["de"]="Deutsch";	
	$HTMLTitle=$sock->GET_INFO("HTMLTitle");
	if(trim($HTMLTitle)==null){$HTMLTitle="%s (%v)";}
	
	
	$html="
	<div id='cron-logon-div'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{remove_language_selector}</td>
		<td>". Field_checkbox("LANGUAGE_SELECTOR_REMOVE",1,
		$logon_parameters["LANGUAGE_SELECTOR_REMOVE"],"CronLogonApplySelector()")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{default_language}</td>
		<td>". Field_array_Hash($lang,"DEFAULT_LANGUAGE",$logon_parameters["DEFAULT_LANGUAGE"],null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{title_pages}:</td>
		<td>". Field_text("HTMLTitle",$HTMLTitle,"font-size:13px;padding:3px;width:180px")."</td>
	</tr>	
	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","CronLogonApply()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_CronLogonApply= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('admin_index_settings');
	}		
	
	function CronLogonApply(){
		var XHR = new XHRConnection();
		if(document.getElementById('LANGUAGE_SELECTOR_REMOVE').checked){XHR.appendData('LANGUAGE_SELECTOR_REMOVE',1);}else{XHR.appendData('LANGUAGE_SELECTOR_REMOVE',0);}
		XHR.appendData('DEFAULT_LANGUAGE',document.getElementById('DEFAULT_LANGUAGE').value);
		XHR.appendData('HTMLTitle',document.getElementById('HTMLTitle').value);
		AnimateDiv('cron-logon-div');
		XHR.sendAndLoad('$page', 'GET',x_CronLogonApply);
		
	
	}		
	
	function CronLogonApplySelector(){
		document.getElementById('DEFAULT_LANGUAGE').disabled=true;
		if(document.getElementById('LANGUAGE_SELECTOR_REMOVE').checked){
			document.getElementById('DEFAULT_LANGUAGE').disabled=false;
		}
	}
	CronLogonApplySelector();
	ChangeHTMLTitle();
	
</script>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function cron_logon_save(){
	$sock=new sockets();
	$logon_parameters=unserialize(base64_decode($sock->GET_INFO("LogonPageSettings")));
	
	while (list ($num, $val) = each ($_GET) ){
		$logon_parameters[$num]=$val;
	}
	
	if(isset($_GET["HTMLTitle"])){$sock->SET_INFO("HTMLTitle", $_GET["HTMLTitle"]);}
		
	$sock->SaveConfigFile(base64_encode(serialize($logon_parameters)),"LogonPageSettings");
	
}


function cron_apc_list(){
	$apc_cache_info=apc_cache_info();

	$html="
	<div style='height:500px;overflow:auto'>
	<table style='width:100%'>";
	while (list ($num, $array) = each ($apc_cache_info["cache_list"]) ){
		$filename=$array["filename"];
		$filename=str_replace(dirname(__FILE__)."/","",$filename);
		$mem_size=ParseBytes($array["mem_size"]/1024);
		$access_time=date('D H:i:s',$array["access_time"]);
		$html=$html."
			<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>$filename</strong></td>
			<td width=1% nowrap><strong>$mem_size</strong></td>
			<td width=1% nowrap><strong>$access_time</strong></td>
			</tr>";
		
	}
	
	$html=$html."</table></div";
	
	echo $html;
}

function parsePHPModules() {
 ob_start();
 phpinfo(INFO_MODULES);
 $s = ob_get_contents();
 ob_end_clean();

 $s = strip_tags($s,'<h2><th><td>');
 $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
 $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
 $vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
 $vModules = array();
 for ($i=1;$i<count($vTmp);$i++) {
  if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
   $vName = trim($vMat[1]);
   $vTmp2 = explode("\n",$vTmp[$i+1]);
   foreach ($vTmp2 AS $vOne) {
   $vPat = '<info>([^<]+)<\/info>';
   $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
   $vPat2 = "/$vPat\s*$vPat/";
   if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
     $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
   } elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
     $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
   }
   }
  }
 }
 return $vModules;
}


?>	