<?php
//http://www.alexa.com/siteowners/widgets/sitestats?
include_once('ressources/class.templates.inc');
include_once('ressources/class.ini.inc');
include_once('ressources/class.syslogs.inc');

if(isset($_GET["verbose"])){
	$GLOBALS["DEBUG_TEMPLATE"]=true;
	$GLOBALS["VERBOSE"]=true;
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);	
}
$GLOBALS["CURRENT_PAGE"]=CurrentPageName();


if(isset($_GET["tab"])){switch_tabs();exit;}
if(isset($_GET["Newtab"])){add_tab();exit;}
if(isset($_GET["delete-tab"])){delete_tab();exit;}
if(isset($_GET["DeleteTabConfirmed"])){delete_tab_confirmed();exit;}
if(isset($_GET["rebuild-icons"])){rebuildicons();exit;}
if(isset($_GET["add-icon"])){main_icon_js();exit;}
if(isset($_GET["show-icons"])){main_icon_list();exit;}
if(isset($_GET["new_icon"])){add_icon();exit;}
if(isset($_GET["delete_icon"])){del_icon();exit;}
if(isset($_GET["ChangeClass"])){echo main_icon_list_list();exit;}
if(isset($_GET["manage-icon"])){echo manage_icons_js();exit;}
if(isset($_GET["show-manage"])){echo manage_icon_page();exit;}
if(isset($_GET["move-widget"])){echo manage_icon_move();exit;}
if(isset($_GET["widget-manage-list"])){echo manage_icons_list($_GET["widget-manage-list"]);exit;}
if(isset($_GET["main"])){switch_main();exit;}
if(isset($_GET["left-menus"])){left_menus();exit;}





function switch_main(){

	switch ($_GET["main"]) {
		case "frontend":admin_index();exit;break;
		case "main":admin_index();exit;break;
		case "warnings":admin_warnings();exit;break;
		case "monitorix":admin_monitorix();exit;break;
		case "emails_received":admin_emails_received();exit;break;
		case "emails_amavis":admin_emails_amavis();exit;break;
		case "graphs":admin_graph();exit;break;
		case "HTTP_FILTER_STATS":HTTP_FILTER_STATS();exit;break;
		case "HTTP_BLOCKED_STATS":	HTTP_BLOCKED_STATS();exit;break;
		case "system":admin_system();exit;break;
		case "kaspersky":kaspersky();exit;break;
		default:;break;
	}
	}

function HTTP_FILTER_STATS(){
	$sock=new sockets();
	$tpl=new templates();
	$datas=base64_decode($sock->getFrameWork("squid.php?squid-realtime-cache=yes"));
	$EVENTS=unserialize($datas);
	if(!is_array($EVENTS)){
		echo $tpl->_ENGINE_parse_body("<center><H2>{error_miss_datas}</H2></center>");
		return;
		
	}
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{date}</th>
		<th width=1%>{member}</th>
		<th width=99%>{website}</th>
	</tr>
</thead>
<tbody class='tbody'>";
$GLOBALS["RTIME"][]=array($sitename,$uri,$TYPE,$REASON,$CLIENT,$date,$zMD5,$site_IP,$Country,$size,$username,$mac);
	
	while (list ($num, $rows) = each ($EVENTS) ){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$sitename=$rows[0];
		$uri=$rows[1];
		$TYPE=$rows[2];
		$REASON=$rows[3];
		$CLIENT=$rows[4];
		$date=$rows[5];
		$zMD5=$rows[6];
		$site_IP=$rows[7];
		$Country=$rows[8];
		$size=$rows[9];
		$username=$rows[10];
		$mac=$rows[11];
		
		$ipaddr=$CLIENT;
		
		if(strlen(trim($username))>1){
			$ipaddr=$username;
		}
		$today=date('Y-m-d');
		$date=str_replace($today, "", $date);

		$html=$html."
		<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold;color:$color' nowrap>$date</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>$ipaddr<div style='font-size:9px'>$mac</div></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>$sitename</a><div style='font-size:9px'>($Country): $uri</div></td>
		</tr>
		";
	}	
	
	$html=$html."</tbody></table>";
	
	echo $tpl->_ENGINE_parse_body("$html");
	
}

function HTTP_BLOCKED_STATS(){
	$html=@file_get_contents("ressources/logs/blocked-rtmm.html");
	$tpl=new templates();
	echo "<input type='hidden' id='switch' value='{$_GET["main"]}'>";
	echo $tpl->_ENGINE_parse_body("$html");	
	
}

function admin_system(){
	
	
	$users=new usersMenus();
	$browse=Buildicon64("DEF_ICO_BROWSE_COMP");
	$disks=Paragraphe("64-hd.png",'{internal_hard_drives}','{internal_hard_drives_text}',"javascript:Loadjs('system.internal.disks.php');","{internal_hard_drives_text}");
	$net=Paragraphe('network-connection2.png','{net_settings}','{net_settings_text}',"javascript:Loadjs('system.nic.config.php?js=yes')",'net_settings_text');
	$frontend_settings=Paragraphe("64-settings.png",'{index_page_settings}','{index_page_settings_text}',"javascript:Loadjs('artica.performances.php?cron-js=yes');","{internal_hard_drives_text}");
	$memdump=Paragraphe("stats-64.png",'{processes_memory}','{processes_memory_text}',"javascript:LoadMemDump();","{processes_memory_text}");
	$artica_events=Paragraphe('events-64.png','{artica_events}','{artica_events_text}',"javascript:Loadjs('artica.events.php');","{artica_events_text}");
	$phpldapadmin=Paragraphe('phpldap-admin-64.png','{APP_PHPLDAPADMIN}','{APP_PHPLDAPADMIN_TEXT}',"javascript:s_PopUpFull('ldap/index.php',1024,800);","{APP_PHPLDAPADMIN_TEXT}");
	$phpmyadmin=Paragraphe('phpmyadmin-64.png','{APP_PHPMYADMIN}','{APP_PHPMYADMIN_TEXT}',"javascript:s_PopUpFull('mysql/index.php',1024,800);","{APP_PHPMYADMIN_TEXT}");
	
	
	$ActiveDirectoryConnection=Paragraphe('wink3_bg.png',
	'{APP_AD_CONNECT}',
	'{APP_AD_CONNECT_TEXT}',
	"javascript:Loadjs('ad.connect.php');","{APP_AD_CONNECT_TEXT}");
	
	
	
	
	
	//phpmyadmin-64.png
	//
	
	$backup=Paragraphe('64-backup.png','{manage_backups}','{manage_backups_text}',"javascript:Loadjs('backup.tasks.php');","{manage_backups_text}");

	$users=new usersMenus();
	if(!$users->phpldapadmin_installed){
		$phpldapadmin=Paragraphe('phpldap-admin-64-grey.png','{APP_PHPLDAPADMIN}','{error_app_not_installed_disabled}',"","{APP_PHPLDAPADMIN_TEXT}");
	}
	if(!$users->phpmyadmin_installed){
		$phpmyadmin=Paragraphe('phpmyadmin-grey-64.png','{APP_PHPMYADMIN}','{error_app_not_installed_disabled}',"","{APP_PHPMYADMIN_TEXT}");
	}	
	
	if($users->SQUID_INSTALLED){
		$sock=new sockets();
		$SQUIDEnable=trim($sock->GET_INFO("SQUIDEnable"));
		if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
		if($SQUIDEnable==0){
			$reactivate_squid=Paragraphe('warning64.png','{SQUID_DISABLED}','{SQUID_DISABLED_TEXT}',"javascript:Loadjs('squid.newbee.php?reactivate-squid=yes')","{SQUID_DISABLED_TEXT}");
		}
	}
	
	if($users->FROM_ISO){
		$FROMISO=Paragraphe('64-CD.png','{MENU_CONSOLE}','{MENU_CONSOLE_TEXT}',"javascript:Loadjs('artica.iso.php')","{MENU_CONSOLE_TEXT}");
		
	}
	
	
	
	$massmailing=Paragraphe('mass-mailing-64.png','{email_campaigns}','{APP_MASSMAILING_ENABLE_TEXT}',"javascript:Loadjs('system.enable.massmailing.php');","{APP_MASSMAILING_ENABLE_TEXT}");
	$tr[]=$frontend_settings;
	$tr[]=$FROMISO;
	$tr[]=$memdump;
	$tr[]=$artica_events;
	$tr[]=$ActiveDirectoryConnection;
	$tr[]=$disks;
	$tr[]=$net;
	$tr[]=$browse;
	$tr[]=$backup;
	$tr[]=$reactivate_squid;
	$tr[]=$phpldapadmin;
	$tr[]=$phpmyadmin;
	$tr[]=$massmailing;
	
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
	$results=$tpl->_ENGINE_parse_body($html);
	writelogs("SET CACHED ".strlen($results)." bytes",__FILE__,__FUNCTION__,__LINE__);
	
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$results);
	echo $results;
}	


function kaspersky(){
	$frontend_settings=Paragraphe("64-settings.png",'{index_page_settings}','{index_page_settings_text}',"javascript:Loadjs('artica.performances.php?cron-js=yes');","{internal_hard_drives_text}");
	$artica_events=Paragraphe('events-64.png','{artica_events}','{artica_events_text}',"javascript:Loadjs('artica.events.php');","{artica_events_text}");
	$wizard=Paragraphe('kaspersky-wizard-64.png','{wizard_kaspersky_smtp_appliance}','{wizard_kaspersky_smtp_appliance_text_wizard}',"javascript:Loadjs('wizard.kaspersky.appliance.php');","{wizard_kaspersky_smtp_appliance_text_wizard}");
	$patterns=Paragraphe('64-kaspersky-databases-status.png','{patterns_vers_status}','{kaspersky_av_text}',"javascript:YahooWin(580,'kaspersky.index.php','Kaspersky');","{kaspersky_av_text}");
	$kas3=Paragraphe('folder-caterpillar.png','{APP_KAS3}','{KAS3_TEXT}','javascript:Loadjs("kas.group.rules.php?ajax=yes")',"{KAS3_TEXT}");
	$kavmilter=Paragraphe('icon-antivirus-64.png','{APP_KAVMILTER}','{APP_KAVMILTER_TEXT}',"javascript:Loadjs('milter.index.php?ajax=yes')","{APP_KAVMILTER_TEXT}");
	$retranslator=Paragraphe('64-retranslator.png','{APP_KRETRANSLATOR}','{APP_KRETRANSLATOR_TEXT}',"javascript:Loadjs('index.retranslator.php')",'APP_KRETRANSLATOR_TEXT');
	
		$tr[]=$kas3;
		$tr[]=$kavmilter;
		$tr[]=$retranslator;
		$tr[]=$patterns;
		$tr[]=$wizard;
		$tr[]=$artica_events;	
		$tr[]=$frontend_settings;			


	
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
	
	
	
$html="<div style='width:700px'>
	$html
</div>";		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html,"configure.server.php,postfix.index.php");	
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
	echo $html;
}


	
function admin_graph(){
/*header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("content-type:text/html");
*/
$users=new usersMenus();
$users->LoadModulesEnabled();
$sock=new sockets();

if($users->kas_installed){
	if($users->KasxFilterEnabled){
		kas_stats();
		exit;
	}
	
}

if($users->GRAPHDEFANG_INSTALLED){
	if($users->MimeDefangEnabled==1){
		graph_defang();
		exit;
	}
}


$style="style='border:1px dotted #CCCCCC;padding:3px;margin:3px;text-align:center'";

	$md=md5(date('Ymdhis'));
	$users=new usersMenus(nul,0,$_GET["hostname"]);
	if($users->POSTFIX_INSTALLED==true){
		if($sock->GET_INFO("MailGraphEnabled")==1){
				$g_postfix="<div id='g_postfix' $style>
			<H3>Postfix</H3>
			<img src='images.listener.php?uri=mailgraph/mailgraph_0.png&md=$md'>
			</div>";
		}
	
	}
	
	if($users->SQUID_INSTALLED==true){
		$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
		if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
		if($SQUIDEnable==1){
		$g_squid="
			<div id='g_squid' $style>
				<H3>Squid</H3>
					<img src='images.listener.php?uri=squid/rrd/connections.day.1.png&md=$md'>
				
					<img src='ressources/logs/hours-squid-hits.png' style='margin-top:5px'>
					<br>
					<img src='ressources/logs/hours-squid-size.png' style='margin-top:5px'>
			</div>";
		}
	}
	
	
	$g_system="<div id='g_sys' $style>
		<H3>system</H3>
			<img src='images.listener.php?uri=system/rrd/01cpu-1day.png&md=$md'>
	</div>";	
	
	$p="<input type='hidden' id='switch' value='{$_GET["main"]}'>
	$g_squid.$g_system.$g_postfix.";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($p);
	
	
}
	
function admin_emails_amavis(){
	include_once("ressources/class.amavis.inc");
	$d=new amavis_sql();
	echo "<input type='hidden' id='switch' value='{$_GET["main"]}'>";
	echo $d->LastReceived();
	
}




function admin_emails_received(){
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->GET_INFO("RTMMailConfig"));
	$FILTERBY=$ini->_params["ENGINE"]["FILTERBY"];
	echo "<input type='hidden' id='switch' value='{$_GET["main"]}'>";
	if($FILTERBY<>null){
		$datas=@file_get_contents("/usr/share/artica-postfix/ressources/logs/last.100.mails.".md5($FILTERBY).".html");
	}else{
		$datas=@file_get_contents("/usr/share/artica-postfix/ressources/logs/last.100.mails.html");
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($datas);
}
	

function admin_monitorix(){
	$md=md5(date('Ymdhis'));

	$user=new usersMenus();
	$table=$user->array_monitorix();
	
	while (list ($num, $val) = each ($table) ){
		$html=$html ."<div style='float:left;margin:5px;'><img src='images.listener.php?uri=$val&md=$md'></div>";
		
	}
	
	
	$g_system="
<div id='g_sys2' style='width:700px;text-align:center'>
		<H3>{monitorix}</H3>
		<center>
			$html
	</center>
	</div>

	
";	
	
	$p="
	<input type='hidden' id='switch' value='{$_GET["main"]}'>
	$g_system.$g_postfix.$g_squid";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($p);		
}

function admin_warnings(){
	$q=new syslogs();
	
	if(isset($_GET["delete"])){
		$q->delete_warnings($_GET["delete"]);
		
	}
	
	$header_top="
	<div style='text-align:right'>
	<input type='button' value='{delete_all_events}' OnClick=\"javascript:AdminDeleteAllSqlEvents();\">
	</div>
	<br>
	
	";
	
	
	
$tpl=new templates();
	$r=$q->get_warnings();
	$count=0;
	$style="style='border-bottom:1px dotted #CCCCCC'";
	$html="<input type='hidden' id='switch' value='{$_GET["main"]}'>";
	while($ligne=@mysql_fetch_array($r,MYSQL_ASSOC)){
		$ligne["event_text"]=htmlentities($ligne["event_text"]);
		
		$ligne["event_text"]=str_replace('{','<br><p class=caption>{',$ligne["event_text"]);
		$ligne["event_text"]=str_replace('}','}</p>',$ligne["event_text"]);
		
		$div="<table style='width:100%'>
		<tr><td colspan=2 align='right'>" . imgtootltip('ed_delete.gif','{delete}',"DeleteWarning('{$ligne["zMD5"]}')")."</td></tr>
		<tr>
		<td nowrap align='right'><strong>{service}:</strong></td>
		<td><strong>{$ligne["daemon"]}</td></strong>
		</tr>
		<tr><td colspan=2><hr></td></tr>
		<td nowrap align='right' valign='top'><strong>{notification}:</strong></td>
		<td style='font-size:11px;font-weight:bold;padding:3px;margin:3px;border:1px dotted #CCCCCC'>{$ligne["event_text"]}</td>
		</tr>
		</table>";
		
		$html=$html . RoundedLightGrey($div)."<br>";
		
	}
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body("$header_top<center><div style='width:490px'>$html</div></center>")."<br>";
	echo $html;
	
	
}

function admin_index(){
//if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return null;}
$newfrontend=false;
$ajaxadd=null;
if(isset($_GET["newfrontend"])){$newfrontend=true;}	

if($newfrontend){$ajaxadd="&newfrontend=yes";}

$page=CurrentPageName();
	$html="
	<table style='width:700px'>
	<tbody>
	<tr>
		<td valign='top' width='50%'>
			<div id='left_status'><center><img src='img/wait_verybig.gif'></center></div>
		</td>
		<td valign='top' width='50%'>
			<div id='right_status'><center><img src='img/wait_verybig.gif'></center></div>
			<script>
				LoadAjax('right_status','admin.index.php?status=right&counter=1$ajaxadd');		
			</script>
					
		</td>
	</tr>
	</tbody>
	</table>
	
	<div style='text-align:right'>". imgtootltip('32-refresh.png',"{refresh}","RefreshTab('admin_perso_tabs')")."</div>

";
	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html);
	echo $datas;	
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$datas);
	
}
	

if(isset($_GET["fill-tab"])){
	$uid=$_SESSION["uid"];
	$cache_tab_file="ressources/profiles/$uid.{$_GET["fill-tab"]}";	
	echo file_get_contents($cache_tab_file);
	exit;
}




function rebuildicons(){
	$tab=$_GET["rebuild-icons"];
	$uid=$_SESSION["uid"];
	$cache_tab_file="ressources/profiles/$uid.$tab";
	@unlink($cache_tab_file);
	$page=CurrentPageName();
	echo "Loadjs('$page?tab=$tab');";
	
}
	
function get_perso_tabs(){
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

function add_tab(){
	$uid=$_SESSION["uid"];
	@mkdir("ressources/profiles");
	
	$tab=md5($_GET["Newtab"]);
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$ini->_params[$tab]["name"]=$_GET["Newtab"];
	$ini->saveFile("ressources/profiles/$uid.tabs");
	}

function switch_tabs(){
	if(trim($_GET["tab"]==null)){die();}
	switch ($_GET["tab"]){
		case "frontend":main_admin();break;
		case "add-tab":main_add_tab();exit;break;
		default:perso_page($_GET["tab"]);
	}
	
}

function delete_tab(){
	$page=CurrentPageName();
	$tpl=new templates();
	$text=$tpl->_ENGINE_parse_body('{error_want_operation}');
	
	$html="
	var x_DeleteAdminTab= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		document.location.href='admin.index.php';
		}
		
	function DeleteAdminTab(){
			if(confirm('$text')){
				var XHR = new XHRConnection();
     			XHR.appendData('DeleteTabConfirmed','{$_GET["delete-tab"]}');
     			document.getElementById('BodyContentTabs').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     			document.getElementById('BodyContent').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";                               		      	
     			XHR.sendAndLoad('$page', 'GET',x_DeleteAdminTab);	
				}
		}
	DeleteAdminTab();";
		
echo $html;		
	
}

function main_admin(){
	$page=CurrentPageName();
	
	echo "LoadAjax('BodyContent','admin.index.php?admin-ajax=yes');
	setTimeout(\"AdminIndexChargeFunctions()\",900);
	
	function AdminIndexChargeFunctions(){
		Demarre();Loop();CheckDaemon();LoadMasterTabs();
	}
	
function switch_tab(num,hostname){
	var uri='admin.index.php?main='+ num +'&hostname='+hostname;
	Delete_Cookie('ARTICA-INDEX-ADMIN-TAB', '/', '');
	Set_Cookie('ARTICA-INDEX-ADMIN-TAB', uri, '3600', '/', '', '');
	LoadAjax('events',uri)
}	
	
	";}
	
function main_add_tab(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ask=$tpl->_parse_body('{ADD_NEW_TAB_ASK}');
	
	$html="
	<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>
	<script>
	var x_MainAdminAddTab= function (obj) {
	var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		document.location.href='admin.index.php';
	}
	
	function MainAdminAddTab(){
		var tabname=prompt('$ask');
		if(tabname){
			var XHR = new XHRConnection();
     		XHR.appendData('Newtab',tabname);
     		document.getElementById('BodyContentTabs').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     		XHR.sendAndLoad('$page', 'GET',x_MainAdminAddTab);	
		
		}else{
			SelectTabID('admin_perso_tabs',1);
			//document.location.href='admin.index.php';
			return;
		}
	
	}
	
	MainAdminAddTab();
	</script>
";
	
	
echo $html;	
}


function perso_page($tab){
$uid=$_SESSION["uid"];
$page=CurrentPageName();
$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
$cache_tab_file="ressources/profiles/$uid.$tab";
if(!is_file($cache_tab_file)){BuildCacheTab($tab);}
header("location: $page?fill-tab=$tab");
return;
}

function BuildCacheTab($tab){
	$uid=$_SESSION["uid"];
	$cache_tab_file="ressources/profiles/$uid.$tab";
	$page=CurrentPageName();
	$cancel=Paragraphe("64-cancel.png","{DELETE_THIS_TAB}","{DELETE_THIS_TAB_TEXT}","javascript:Loadjs('$page?delete-tab=$tab');");
	$add=Paragraphe("64-circle-plus.png","{ADD_WIDGET}","{ADD_WIDGET_TEXT}","javascript:Loadjs('$page?add-icon=$tab');");
	
	$settings=Paragraphe('64-widget-manage.png','{MANAGE_WIDGETS}','{MANAGE_WIDGETS_TEXT}',"javascript:Loadjs('$page?manage-icon=$tab');");
	
	$rebuild=Paragraphe("64-refresh.png","{REBUILD_PAGE}","{REBUILD_PAGE_TEXT}","javascript:Loadjs('$page?rebuild-icons=$tab');");
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	
	$count=0;
	$ico=new deficons();
	
	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==null){continue;}
		$icon_s[$ligne]=$ligne;
	}
	
if(is_array($icon_s)){
	while (list ($num, $ligne) = each ($icon_s) ){
		if($count==3){
			$t=$t."</tr><tr>";
			$count=0;
		}
		$t=$t."<td valign='top'>".$ico->BuildIcon($ligne)."</td>";
		$count=$count+1;
		
	}
}

$title=$ini->_params[$tab]["name"];

$html="
<H1>$title</H1>
<div style='width:100%;height:728px;overflow:auto'>
<table style='width:100%'>
<tr>
$t
</tr>
<tr>
<td valign='top'>$add</td>	
<td valign='top'>$settings</td>
<td valign='top'>$cancel</td>
</tr>
</table>

</div>";

$tpl=new templates();
$final=$tpl->_ENGINE_parse_body($html);
file_put_contents($cache_tab_file,$final);	
}

function delete_tab_confirmed(){
	$tab=$_GET["DeleteTabConfirmed"];
	$uid=$_SESSION["uid"];
	$page=CurrentPageName();	
	$cache_tab_file="ressources/profiles/$uid.$tab";
	@unlink($cache_tab_file);
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	unset($ini->_params[$tab]);
	$ini->saveFile("ressources/profiles/$uid.tabs");
	
	
}

function manage_icons_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{WIDGETS_AREA}");	
	$html="
	function MainAdminWidgetManageLaunch(){
		YahooWin(500,'$page?show-manage=yes&icon={$_GET["manage-icon"]}','$title');
	
	}	
	
var x_AddWidgetIcon= function (obj) {	
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}	
	Loadjs('admin.tabs.php?rebuild-icons={$_GET["manage-icon"]}');
	}		
	
	function DelWidgetIcon(icon_name){
		var XHR = new XHRConnection();
     	XHR.appendData('delete_icon',icon_name);
     	XHR.appendData('delete_icon_tab','{$_GET["manage-icon"]}');
     	document.getElementById('BodyContent').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     	XHR.sendAndLoad('$page', 'GET',x_AddWidgetIcon);
     	Loadjs('$page?rebuild-icons={$_GET["manage-icon"]}');
     	LoadAjax('widgetlist','$page?widget-manage-list={$_GET["manage-icon"]}');	
	}	
	
	function WidgetDown(num){
		LoadAjax('widgetlist','$page?move-widget='+num +'&icon-page={$_GET["manage-icon"]}&move=down');
		Loadjs('$page?rebuild-icons={$_GET["manage-icon"]}');
	}
	
	function WidgetUp(num){
		LoadAjax('widgetlist','$page?move-widget='+num +'&icon-page={$_GET["manage-icon"]}&move=up');
		Loadjs('$page?rebuild-icons={$_GET["manage-icon"]}');
	}
	
	MainAdminWidgetManageLaunch();
	";
	
	echo $html;
	
}

function manage_icon_move(){
	$tab=$_GET["icon-page"];
	$uid=$_SESSION["uid"];	
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	
	$icons2=array_move_element($icons,$icons[$_GET["move-widget"]],$_GET["move"]);
	
	if(count($icons2)==0){echo manage_icons_list($tab);exit;}
	reset($icons2);

while (list ($num, $ligne) = each ($icons2) ){
		if($ligne==null){continue;}
		$icon_s[$ligne]=$ligne;
	}

while (list ($num, $ligne) = each ($icon_s) ){
		if($ligne==null){continue;}
		$icon_t[]=$ligne;
	}	

$ini->_params[$tab]["icons"]=implode(',',$icon_t);
$ini->saveFile("ressources/profiles/$uid.tabs");
echo manage_icons_list($tab);
	
}

function manage_icon_page(){
	
	$tpl=new templates();
	
	$icons=manage_icons_list($_GET["icon"]);
	
	$html="<H1>{MANAGE_WIDGETS}</H1>
	".RoundedLightWhite("
	<div style='height:450px;overflow:auto' id='widgetlist'>$icons</div>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function manage_icons_list($tab){
	$uid=$_SESSION["uid"];	
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	$count=0;
	$ico=new deficons();
	
	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==null){continue;}
		$icon_s[]=$ligne;
	}
	
	if(is_array($icon_s)){
		while (list ($num, $ligne) = each ($icon_s) ){
			$t=$t.$ico->BuildIconRow($ligne,$num);
		}
	}

	$html="<table style='width:100%'>$t</table>";
	
	return $html;

	
}




function main_icon_js(){

	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{WIDGETS_AREA}");
	
	$html="
	function MainAdminWidgetSection(){
		YahooWin(700,'$page?show-icons=yes&icon={$_GET["add-icon"]}','$title');
	
	}
var x_AddWidgetIcon= function (obj) {	
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}	
	Loadjs('admin.tabs.php?rebuild-icons={$_GET["add-icon"]}');
	AddIconChangeClass();
	}	
	
	function AddWidgetIcon(icon_name){
		var XHR = new XHRConnection();
     	XHR.appendData('new_icon',icon_name);
     	XHR.appendData('new_icon_tab','{$_GET["add-icon"]}');
     	document.getElementById('BodyContent').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     	XHR.sendAndLoad('$page', 'GET',x_AddWidgetIcon);	
	
	}
	
	function DelWidgetIcon(icon_name){
		var XHR = new XHRConnection();
     	XHR.appendData('delete_icon',icon_name);
     	XHR.appendData('delete_icon_tab','{$_GET["add-icon"]}');
     	document.getElementById('BodyContent').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait.gif'></center></div>\";
     	XHR.sendAndLoad('$page', 'GET',x_AddWidgetIcon);	
	}
	
	function AddIconChangeClass(){
		var class=document.getElementById('class').value;
		LoadAjax('icons_listes','$page?ChangeClass='+class+'&icon={$_GET["add-icon"]}');
		
	}
	
	MainAdminWidgetSection();
";
	
	
echo $html;		
	
	

}

function add_icon(){
	$uid=$_SESSION["uid"];
	$tab=$_GET["new_icon_tab"];
	$page=CurrentPageName();	
	$cache_tab_file="ressources/profiles/$uid.$tab";
	@unlink($cache_tab_file);
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$icons=explode(",",$ini->_params[$_GET["new_icon_tab"]]["icons"]);
	$icons[]=$_GET["new_icon"];
	$ini->_params[$_GET["new_icon_tab"]]["icons"]=implode(",",$icons);
	$ini->saveFile("ressources/profiles/$uid.tabs");
}
function del_icon(){
	$uid=$_SESSION["uid"];
	$tab=$_GET["delete_icon_tab"];
	$icon=$_GET["delete_icon"];
	$page=CurrentPageName();	
	$cache_tab_file="ressources/profiles/$uid.$tab";
	@unlink($cache_tab_file);
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$icons=explode(",",$ini->_params[$tab]["icons"]);

	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==$icon){
			unset($icons[$num]);
			}
	}
	
	
	$ini->_params[$tab]["icons"]=implode(",",$icons);
	$ini->saveFile("ressources/profiles/$uid.tabs");
}

function main_icon_list(){
	$ico=new deficons();
	$ico->categories[null]="{select}";
	$classes=Field_array_Hash($ico->categories,"class",$_GET["class"],"AddIconChangeClass()");
	
	
	
	$html="
	<input type='hidden' id='tabid' value='{$_GET["icon"]}'>
	<H1>{WIDGETS_AREA}</H1>
	<p class=caption>{WIDGETS_AREA_EXPLAIN}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{choose_section}:</td>
		<td>$classes</td>
	</tr>
	</table><div id='icons_listes' style='width:100%;height:400px;overflow:auto'>".main_icon_list_list()."</div>
	";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function main_icon_list_list(){
$ico=new deficons();
	$uid=$_SESSION["uid"];
	$tab=$_GET["icon"];
	$cache_tab_file="ressources/profiles/$uid.$tab";
	$ini=new Bs_IniHandler("ressources/profiles/$uid.tabs");
	$icons=explode(",",$ini->_params[$tab]["icons"]);
	
	while (list ($num, $ligne) = each ($icons) ){
		if($ligne==null){continue;}
		$icon_s[$ligne]=true;
	}	

	if($_GET["ChangeClass"]==null){$_GET["ChangeClass"]="NETWORK";}
	$icos=$ico->Build32_widgets();
	$array=$icos[$_GET["ChangeClass"]];


	while (list ($num, $ligne) = each ($array) ){
		
		if($count==3){
			$t=$t."</tr><tr>";
			$count=0;
		}
		if($icon_s[$num]){continue;}
	
		
		$t=$t."<td valign='top'>$ligne</td>";
		$count=$count+1;
		
		
	}	
	
$html="<table style='width:100%'>
	<tr>$t</tr>
	</table>";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
	
}

function kas_stats(){
	$style="style='border:1px dotted #CCCCCC;padding:3px;margin:3px;text-align:center'";
	$md=md5(date('Ymdhis'));	
$kasarray[]="d__all_7dM.png";
$kasarray[]="d00000000_1mM.png";
$kasarray[]="d00000000_24hS.png";
$kasarray[]="d00000000_1yM.png";
$kasarray[]="d00000000_7dS.png";
$kasarray[]="d00000000_7dM.png";
$kasarray[]="d__all_24hS.png";
$kasarray[]="g00000000_7dM.png";-
$kasarray[]="g00000000_24hS.png";
$kasarray[]="g__all_24hS.png";
$kasarray[]="d__all_1mM.png";
$kasarray[]="g00000000_24hM.png";
$kasarray[]="d00000000_24hM.png";
$kasarray[]="g00000000_1mM.png";
$kasarray[]="d00000000_1yS.png";
$kasarray[]="g__all_7dM.png";
$kasarray[]="d00000000_1mS.png";
$kasarray[]="g__all_7dS.png";
$kasarray[]="g00000000_1yM.png";
$kasarray[]="g00000000_1mS.png";
$kasarray[]="d__all_1mS.png";
$kasarray[]="g00000000_1yS.png";
$kasarray[]="g00000000_7dS.png";
$kasarray[]="g__all_24hM.png";
$kasarray[]="g__all_1mM.png";
$kasarray[]="d__all_1yM.png";
$kasarray[]="g__all_1mS.png";
$kasarray[]="d__all_7dS.png";
$kasarray[]="g__all_1yM.png";
$kasarray[]="d__all_1yS.png";
$kasarray[]="d__all_24hM.png";
$kasarray[]="g__all_1yS.png";	
$index=rand(0,count($kasarray));
	
preg_match("#_([0-9]+)([A-Z-a-z])([A-Z-a-z])\.#",$kasarray[$index],$re);
if($re[2]=='m'){$t='{month}';}
if($re[2]=='d'){$t='{days}';}
if($re[2]=='h'){$t='{hours}';}
if($re[2]=='y'){$t='{year}';}

if($re[3]=='S'){$f='bytes';}
if($re[3]=='M'){$f='Messages';}



	$g_system="
<div id='g_sys2' $style>
		<H3>{APP_KAS3} {$re[1]} $t / $f</H3>
			". LinkToolTipjs("{click_to_display_statistics_section}","javascript:Loadjs('index.kas3-stats.php')","border:0px")."	
			<img src='images.listener.php?uri=kas3/{$kasarray[$index]}&md=$md'></a>
	</div>
<div id='g_sys' $style>
		<H3>system</H3>
			<img src='images.listener.php?uri=system/rrd/01cpu-1day.png&md=$md'>
	</div>
	
";	
	
	$p="
	<input type='hidden' id='switch' value='{$_GET["main"]}'>
	$g_system.$g_postfix.$g_squid";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($p);	
}

function left_menus(){
	if(!isset($_SESSION["uid"])){return null;}
	
		$lang=$_COOKIE["artica-language"];
		if($lang==null){$lang="en";}
		$md5=md5("{$_SESSION["uid"]}{$lang}leftmenu");
		if(!$GLOBALS["VERBOSE"]){
			if(is_file("ressources/logs/web/menus-$md5.object.$lang.cache")){echo @file_get_contents("ressources/logs/web/menus-$md5.object.$lang.cache");exit;}
		}
		writelogs("LANG=$lang",__FUNCTION__,__FILE__,__LINE__);
	
	if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}:->new usersMenus(); function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
	//writelogs("Starting generating LEFT menus",__FUNCTION__,__FILE__,__LINE__);
	$menus=new usersMenus();
	$tpl=new templates();
	if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}: menus->BuildLeftMenus() function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
	$menus=$menus->BuildLeftMenus();
	$html="$menus<input type='hidden' id='add_new_organisation_text' value='{add_new_organisation_text}'>";
	//writelogs("finish generating LEFT menus",__FUNCTION__,__FILE__,__LINE__);
	if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}: tpl->_ENGINE_parse_body() function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
	$final=$tpl->_ENGINE_parse_body($html);
	@file_put_contents("ressources/logs/web/menus-$md5.object.$lang.cache",$final);
	echo $final;
	
}

?>
