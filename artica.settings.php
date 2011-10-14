<?php
$GLOBALS["ICON_FAMILY"]="PARAMETERS";
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.samba.inc');
	
	
$usersmenus=new usersMenus();

if(isset($_GET["js"])){js();exit;}

if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}


if(isset($_POST["DenyMiniWebFromStandardPort"])){js_web_miniadm_save();exit;}
if(isset($_POST["LighttpdArticaDisableSSLv2"])){js_LighttpdArticaDisableSSLv2_save();exit;}


if(isset($_GET["lighttpd_procs"])){HTTPS_PROCESSES_SAVE();exit;}
if(isset($_GET["LighttpdArticaDisabled"])){LighttpdArticaDisabled();exit;}
if(isset($_POST["ChangeSuperSuser"])){ChangeUserPassword();exit();}
if(isset($_GET["section"])){page_switch();exit;}
if(isset($_GET["GroupBehavior"])){GroupBehavior();exit;}
if(isset($_GET["SaveGroupBehavior"])){SaveGroupBehavior();exit;}
if(isset($_GET["SaveRelayBehavior"])){SaveRelayBehavior();exit;}
if(isset($_GET["RelayBehavior"])){RelayBehavior();exit;}
if(isset($_GET["ARTICA_FILTER_QUEUE_PATH"])){SaveSettings();}
if(isset($_GET["ArticaWebRootURI"])){SaveArticaWebRootURI();exit;}
if(isset($_GET["status"])){main_status();exit;}
if(isset($_GET["ArticaProxyServerEnabled"])){SaveProxySettings();exit;}
if(isset($_GET["ArticaMailAddonsLevel_switch"])){echo SMTP_PERFORMANCES_EXPLAIN($_GET["ArticaMailAddonsLevel_switch"]);exit;}
if(isset($_GET["ArticaMailAddonsLevel_save"])){SMTP_PERFORMANCES_SAVE();exit;}
if(isset($_GET["MysqlMaxEventsLogs"])){SaveSqlSettings();exit();}
if(isset($_GET["http_settings"])){HTTPS_PORT_SAVE();exit;}
if(isset($_GET["advlighttp"])){HTTPS_PROCESSES();exit;}

if(isset($_GET["smtp_notifications"])){SMTP_NOTIFICATIONS_SAVE();exit;}
if(isset($_GET["testnotif"])){SMTP_NOTIFICATIONS_NOTIF();exit;}
if(isset($_GET["smtp-notifs-tab"])){SMTP_NOTIFICATIONS_SWITCH();exit;}
if(isset($_GET["SMTP_NOTIFICATIONS_ADD_CC"])){SMTP_NOTIFICATIONS_ADD_CC();exit;}
if(isset($_GET["SMTP_NOTIFICATIONS_DEL_CC"])){SMTP_NOTIFICATIONS_DEL_CC();exit;}
if(isset($_GET["SMTP_NOTIFICATIONS_LIST_CC"])){echo SMTP_NOTIFICATIONS_CCLIST();exit;}

if(isset($_GET["ldapindex-js"])){ldapindex_js();exit;}
if(isset($_GET["ldapindex-popup"])){ldapindex_popup();exit;}
if(isset($_GET["ldapindex-start"])){ldapindex_save();exit;}

if(isset($_GET["ajax-notif"])){SMTP_NOTIFICATIONS_NOTIF_JS();exit;}
if(isset($_GET["ajax-notif-popup"])){SMTP_NOTIFICATIONS_POPUP();exit;}
if(isset($_GET["ajax-notif-start"])){SMTP_NOTIFICATIONS_TEST();exit;}

if(isset($_GET["js-index"])){js_index();exit;}
if(isset($_GET["js-web-interface"])){js_web_interface();exit;}
if(isset($_GET["js-notification-interface"])){js_notification_interface();exit;}
if(isset($_GET["js-web-interface2"])){js_web_interface2();exit;}
if(isset($_GET["js-web-fw"])){js_web_fw();exit;}
if(isset($_GET["js-web-fw-nets"])){js_web_fw_list();exit;}
if(isset($_GET["js-web-miniadm"])){js_web_miniadm();exit;}


if(isset($_GET["LighttpdAddNet"])){js_web_fw_add_net();exit;}
if(isset($_GET["LighttpdAddIP"])){js_web_fw_add_ip();exit;}
if(isset($_GET["LighttpdDelIP"])){js_web_fw_del_ip();exit;}
if(isset($_GET["LighttpdRestart"])){LighttpdRestart();exit;}


if(isset($_GET["cdir-ipaddr"])){calc_cdir();exit;}


if(isset($_GET["js-proxy-interface"])){js_proxy_interface();exit;}
if(isset($_GET["js-account-interface"])){js_account_interface();exit;}
if(isset($_GET["js-logs-interface"])){js_logs_interface();exit;}
if(isset($_GET["js-mysql-interface"])){js_mysql_interface();exit;}

if(isset($_GET["timezones"])){TIME_ZONE_SAVE();exit;}

if(isset($_GET["js-ldap-interface"])){LDAP_CONFIG_JS();exit;}
if(isset($_GET["js-ldap-popup"])){LDAP_CONFIG();exit;}
if(isset($_GET["main-ldap"])){LDAP_SWITCH();exit;}
if(isset($_GET["set_cachesize"])){LDAP_SAVE();exit;}
if(isset($_GET["ldap-networks-list"])){LDAP_CONFIG_NET_LIST();exit;}
if(isset($_GET["LdapAllowAnonymous"])){LDAP_SAVE();exit;}
if(isset($_GET["LdapListenIPAddr"])){LDAP_CONFIG_NET_ADD();exit;}
if(isset($_GET["ldap_other_network_card"])){LDAP_CONFIG_NET_ADD_OTHER();exit;}
if(isset($_GET["ldap-syncprov"])){LDAP_SYNCPROV();exit;}
if(isset($_GET["SaveSyncProv"])){LDAP_SYNCPROV_SAVE();exit;}



if(isset($_GET["ldapDelNet"])){LDAP_CONFIG_NET_DEL();exit;}

if(isset($_GET["mysql-audit"])){MYSQL_AUDIT();exit;}
if(isset($_GET["server_swap"])){MYSQL_AUDIT_PERFORM();exit;}



page_index();	


function js(){
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){echo "alert('no privileges');";exit;}



$addon=file_get_contents("js/artica_settings.js");
$tpl=new templates();

$SMTP_NOTIFICATIONS_ADD_CC=$tpl->javascript_parse_text('{SMTP_NOTIFICATIONS_ADD_CC}');


$title=$tpl->_ENGINE_parse_body('{global settings}');
$ldap_title=$tpl->_ENGINE_parse_body('{APP_OPENLDAP}');
$global_admin_account=$tpl->_ENGINE_parse_body('{global_admin_account}');
$global_proxy=$tpl->_ENGINE_parse_body('{global_proxy}');
$start="GlobalSettingsPage();";
if(isset($_GET["mysql-interface"])){$start="MysqlInterface();";}
if(isset($_GET["bigaccount-interface"])){$start="AccountsInterface();";}
if(isset($_GET["ByPopup"])){$start="GlobalSettingsPagePopup();";}
if(isset($_GET["func-webinterface"])){$start="WebInterFace();";}
if(isset($_GET["func-NotificationsInterface"])){$start="NotificationsInterface();";}
if(isset($_GET["func-ProxyInterface"])){$start="ProxyInterface();";}
if(isset($_GET["func-AccountsInterface"])){$start="AccountsInterface();";}
if(isset($_GET["func-LogsInterface"])){$start="LogsInterface();";}



$page=CurrentPageName();
$html="
	function GlobalSettingsPage(){
		AnimateDiv('BodyContent');
		$('#BodyContent').load('$page?js-index=yes');
		}
		
	function GlobalSettingsPagePopup(){
		YahooWin(888,'$page?js-index=yes','$title');
	}		
		
	function WebInterFace(){
		YahooWin2(690,'$page?js-web-interface=yes','$title');
	}
	
	function LDAPInterFace(){
		YahooWin2(500,'$page?js-ldap-interface=yes','$ldap_title');
	}	
	
	function NotificationsInterface(){
		Loadjs('artica.settings.php?ajax-notif=yes');
	
	}
	
	function ProxyInterface(){
		YahooWin2(590,'$page?js-proxy-interface=yes','$global_proxy');
	
	}
	
	function AccountsInterface(){
		YahooWin2(550,'$page?js-account-interface=yes','$global_admin_account');
	}
	
	function LogsInterface(){
		YahooWin2(500,'$page?js-logs-interface=yes','$title');
	}
	
	function MysqlInterface(){
		YahooWin2(750,'$page?js-mysql-interface=yes','$title');
	}
		
	
	$start;
	$addon
	
var x_SMTP_NOTIFICATIONS_ADD_CC= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				LoadAjax('notifcclist','$page?SMTP_NOTIFICATIONS_LIST_CC=yes');		
			}		
	
	function SMTP_NOTIFICATIONS_ADD_CC(){
		var email=prompt('$SMTP_NOTIFICATIONS_ADD_CC');
		if(email){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_NOTIFICATIONS_ADD_CC',email);
			document.getElementById('notifcclist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SMTP_NOTIFICATIONS_ADD_CC);
		}
	}
	
	function SMTP_NOTIFICATIONS_DEL_CC(num){
			var XHR = new XHRConnection();
			XHR.appendData('SMTP_NOTIFICATIONS_DEL_CC',num);
			document.getElementById('notifcclist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SMTP_NOTIFICATIONS_ADD_CC);
	}
var x_SaveTimeZone= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				WebInterFace();
			}		
	
	function SaveTimeZone(){
			var XHR = new XHRConnection();
			XHR.appendData('timezones',document.getElementById('timezones').value);
			document.getElementById('timezones_div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveTimeZone);
			
	}
	
	". MYSQL_AUDIT_js()."
	
";
	
echo $html;
	
	
}

function js_index(){
	
	if(GET_CACHED(__FILE__,__FUNCTION__)){return true;}
	
	
	$tpl=new templates();
	$mysql_settings=$tpl->_ENGINE_parse_body("{mysql_settings}");
	$web_interface_settings=Paragraphe("folder-performances-64.png","{web_interface_settings}","{web_interface_settings_text}",
	"javascript:Loadjs('$page?js=yes&func-webinterface=yes');");
	$SMTP_NOTIFICATIONS_PAGE=Paragraphe("folder-64-fetchmail.png","{smtp_notifications}","{smtp_notifications_text}",
	"javascript:Loadjs('$page?js=yes&func-NotificationsInterface=yes');");
	$proxy=Paragraphe("proxy-64.png","{http_proxy}","{http_proxy_text}",
	"javascript:Loadjs('$page?js=yes&func-ProxyInterface=yes');");
	
	$superuser=Paragraphe("superuser-64.png","{account}","{accounts_text}",
	"javascript:Loadjs('$page?js=yes&func-AccountsInterface=yes');");
	
	$logs=Paragraphe("scan-64.png","{logs_cleaning}","{logs_cleaning_text}",
	"javascript:Loadjs('$page?js=yes&func-LogsInterface=yes');");
	//$mysql=Paragraphe("folder-64-backup.png","{mysql_settings}","{mysql_settings_text}","javascript:MysqlInterface();","$mysql_settings");
	$perfs=Paragraphe("perfs-64.png","{artica_performances}","{artica_performances_text}","javascript:Loadjs('artica.performances.php');");
	
	
	$ldap=Paragraphe("database-setup-64.png","{openldap_parameters}","{openldap_parameters_text}","javascript:Loadjs('artica.settings.php?js-ldap-interface=yes');");
	
	
	 
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div style='font-size:18px;margin:10px;'>{artica_settings}</div>
		<img src='img/bg_user.jpg' style='margin:30px'><br>
	</td>
	<td valign='top'>
		<table style='width:99.5%'>
			<tr>
				<td valign='top'>$web_interface_settings</td>
				<td valign='top'>$perfs</td>
				
			</tr>
			<tr>
				<td valign='top'>$SMTP_NOTIFICATIONS_PAGE</td>
				<td valign='top'>$proxy</td>
				
			</tr>
			<tr>
				<td valign='top'>$superuser</td>
				<td valign='top'>$ldap</td>
				
			</tr>	
			<tr>
				<td valign='top'>$logs</td>
				<td valign='top'>&nbsp;</td>
				
			</tr>										
		</table>
	</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,'configure.server.php');
	SET_CACHED(__FILE__,__FUNCTION__,null,$datas);
	echo $datas;
	
	
}

function js_web_interface(){
	$tpl=new templates();
	$array["js-web-interface2"]='{web_interface_settings}';
	$array["js-web-fw"]='{security_access}';
	$array["js-web-miniadm"]='{ARTICA_MINIADM}';
	$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_jsweb style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_jsweb').tabs({
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


function js_web_interface2(){
	$content=HTTPS_PORT();
	$tpl=new templates();
	$html=$content;
	echo $tpl->_ENGINE_parse_body($html);
	}
function js_notification_interface(){
	$content=SMTP_NOTIFICATIONS_PAGE();
	$tpl=new templates();
	$html="<H1>{smtp_notifications}</H1>$content";
	echo $tpl->_ENGINE_parse_body($html);	
}
function js_proxy_interface(){
	$content=http_proxy();
	$tpl=new templates();
	$html="$content";
	echo $tpl->_ENGINE_parse_body($html);		
	}
function js_account_interface(){
	$content=GlobalAdmin();
	$tpl=new templates();
	$html="$content";
	echo $tpl->_ENGINE_parse_body($html);		
}
function js_logs_interface(){
	$content=MaxLogsFiles()."<br>".WebRoot();
	$tpl=new templates();
	$html="<H1>{logs_cleaning}</H1>$content";
	echo $tpl->_ENGINE_parse_body($html);		
}
function js_mysql_interface(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$content=MYSQL_MAX_EVENTS();
	$tpl=new templates();
	SET_CACHED(__FILE__,__FUNCTION__,null,$content);
	echo $tpl->_ENGINE_parse_body($content);		
}




function SMTP_NOTIFICATIONS_NOTIF_JS(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{smtp_notifications}');
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$html="
	{$prefix}timeout=0;
	
	YahooWin2('760','$page?ajax-notif-popup=yes','$title');
		
	function testnotifs(){
		SaveArticaSMTPNotifValues();
		YahooWin3('560','$page?ajax-notif-start=yes','$title');
		setTimeout(\"StartNotif()\",500);
	}
	
	function StartNotif(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>30){alert('ajax timeout!');return;}
		if(!document.getElementById('testnotifs')){
			setTimeout(\"StartNotif()\",500);
		}
		{$prefix}timeout=0;
		SendTestNotif();
		
	}
	
	function SendTestNotif(){
		LoadAjax('testnotifs','$page?testnotif=yes');
	
	}
	
	function Switchdiv(id){
	document.getElementById('notif1').style.display='none';
   	document.getElementById('notif2').style.display='none';
   	
   	if(document.getElementById('notif3')){
   		document.getElementById('notif3').style.display='none';
	}
   	
   	document.getElementById(id).style.display='block';     
	
	}
	
	
	
	
		
	";
	echo $html;
	}
	
function SMTP_NOTIFICATIONS_POPUP(){
	$tpl=new templates();
	//$form=SMTP_NOTIFICATIONS();
	echo "<form name='FFM120'><input type='hidden' name='' value='yes'>".$tpl->_ENGINE_parse_body(SMTP_NOTIFICATIONS_TABS())."</form>";
	
}

function SMTP_NOTIFICATIONS_TEST(){
	$html="<div style='width:99%;height:300px;overflow:auto' id='testnotifs'>
	
	</div>";
	
	echo $html;
	
}
	



function ldapindex_js(){
$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{index_ldap}');
	$page=CurrentPageName();
	$html="
	YahooWin(550,'$page?ldapindex-popup=yes','$title');
	
var x_StartLdapIndex= function (obj) {
	var response=obj.responseText;
	document.getElementById('slap_index_status').innerHTML=response;
	}		
	
	function StartLdapIndex(){
		var XHR = new XHRConnection();
		XHR.appendData('ldapindex-start','yes');
		document.getElementById('slap_index_status').innerHTML='<img src=\"img/wait_verybig.gif\">';
		XHR.sendAndLoad('$page', 'GET',x_StartLdapIndex);	
	}	

";
	echo $html;			
	
	
}

function ldapindex_popup(){
	

$html="<H1>{index_ldap}</H1>

<dov id='hookdiv'>
	
	<table style='widht:100%'>
	<tr>
	<td valign='top'><div id='slap_index_status' style='background-color:#CCCCCC'></div></td>
	<td valign='top'><p class=caption> {index_ldap_text}</p>
	<div style='width:100%;text-align:right'>
	". button('{index_ldap}',"StartLdapIndex();")."
	</div>
	
	</td>
	</tr>		
	</table>
	</div>
	";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function ldapindex_save(){
	
	$sock=new sockets();
	$datas=$sock->getfile('slapindex');
	$datas=explode("\n",$datas);
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){
			continue;
		}
		
		$html=$html . "<div style='font-size:10px;margin:2px'>$ligne</div>";
	}
	
	$html="<div style='width:100%;height:300px;overflow:auto;'>$html</div>";
	echo RoundedLightWhite($html);
	
}


function page_index(){
$page=CurrentPageName();	
	
$html="
<input type='hidden' id='interface_restarted' value='{interface_restarted}'>
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	}
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_user.jpg'></td>
	<td valign='top'><div id='services_status'></div><br></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='middle_area'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('middle_area','$page?section=yes&tab=0');demarre();</script>
	<script>LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');</script>
	";



$cfg["JS"][]="js/artica_settings.js";
$tpl=new template_users('{global settings}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
	
}



function page_switch(){
	
	switch ($_GET["tab"]) {
		case 0:page_tab_index();break;
		case 1:page_tab_account();break;
		case 2:page_tab_path();break;
		case 3:page_tab_proxy();break;
		case 4:page_tab_mysql();break;
		default:page_tab_index();break;
	}
	
	
}	
	
function page_tab_proxy(){
	$prxy=http_proxy();
	$html=tabs()."<br>
	<center>
		<table style='width:70%'>
		<tr>
			<td width=100%' valign='top'>$prxy</td>
			
		</tr>
		</table>
		</center>";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function page_tab_mysql(){
$html=tabs()."<br>
	<center>
		<table style='width:100%'>
		<tr>
			<td width=100%' valign='top'>" . MYSQL_MAX_EVENTS() ."</td>
			
		</tr>
		</table>
		</center>";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}





function SMTP_PERFORMANCES(){
	$array_perf=array(
	0=>"{all_modules}",
	1=>"{without_artica_modules}",
	2=>"{without_antivirus}",
	3=>"{without_antispam_module}");
	
	$ldap=new clladp();
	$hash=$ldap->ArticaDatas();
	$level=$hash["ArticaMailAddonsLevel"];
	$field=Field_array_Hash($array_perf,'ArticaMailAddonsLevel',$level,"ArticaMailAddonsLevel_switch()");
	
	
	$html="<H5>{artica_smtp_performances}</H5>
	<p class=caption>{artica_smtp_performances_text}</p>" . RoundedLightGreen(
	"<table style='width:100%'>
	<tr>
		<td><strong>{select}:</strong></td>
		<td>$field</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ArticaMailAddonsLevel_save();\"></td>
	</tr>
	</table>") . "<br><div id=smtp_performances_explain>" . SMTP_PERFORMANCES_EXPLAIN($level) . "</div>";
	
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function HTTPS_PORT(){
	$httpd=new httpd();
	$users=new usersMenus();
	$page=CurrentPageName();
	$use_apache=null;
	$sock=new sockets();
	$tpl=new templates();
	$LighttpdUserAndGroup=$sock->GET_INFO("LighttpdUserAndGroup");
	$LighttpdArticaDisabled=$sock->GET_INFO("LighttpdArticaDisabled");
	$LighttpdArticaDisableSSLv2=$sock->GET_INFO("LighttpdArticaDisableSSLv2");
	$ConfirmDisableArticaWebConsole=$tpl->javascript_parse_text("{ConfirmDisableArticaWebConsole}");
	
	
	if($LighttpdUserAndGroup==null){$LighttpdUserAndGroup="www-data:www-data";}
	$ArticaHttpsPort=$sock->GET_INFO("ArticaHttpsPort");
	$ArticaHttpUseSSL=$sock->GET_INFO("ArticaHttpUseSSL");
	if(!is_numeric($ArticaHttpUseSSL)){$ArticaHttpUseSSL=1;}
	if(!is_numeric($LighttpdArticaDisabled)){$LighttpdArticaDisabled=0;}
	if(!is_numeric($LighttpdArticaDisableSSLv2)){$LighttpdArticaDisableSSLv2=0;}
	if($ArticaHttpsPort==null){$ArticaHttpsPort="9000";}
	
	$lighttp_processes="<tr>
			<td nowrap nowrap class=legend>{processes}:</strong></td>
			<td><input type='button' value='{advanced_settings}&nbsp;&raquo;' OnClick=\"javascript:YahooWin3(440,'$page?advlighttp=yes');\"></td>
		</tr>
		<tr>
			<td nowrap nowrap class=legend>{LighttpdUseLdap}:</strong></td>
			<td>" . Field_numeric_checkbox_img('LighttpdUseLdap',$httpd->LighttpdUseLdap,'{LighttpdUseLdap_text}')."</td>
		</tr>	";
	
	if($users->lighttpd_installed){
		$use_apache="<tr>
			<td nowrap class=legend>{disable_webconsole_engine}:</strong></td>
			<td>" . Field_checkbox('LighttpdArticaDisabled',1,$LighttpdArticaDisabled,"LighttpdArticaDisabledCheck()")."</td>
		</tr>";
		$lighttp_processes="<input type='hidden' id='LighttpdUseLdap' value=',$httpd->LighttpdUseLdap'>";
		
	}
	
	
	$lighttpd_usenossl="
		<tr>
			<td nowrap nowrap class=legend>{ssl}:</strong></td>
			<td>". Field_checkbox("ArticaHttpUseSSL",1,$ArticaHttpUseSSL,"WebArticaCheck()")."</td>
		</tr>	
		<tr>
			<td nowrap nowrap class=legend>{disableSSLv2}:</strong></td>
			<td>". Field_checkbox("LighttpdArticaDisableSSLv2",1,$LighttpdArticaDisableSSLv2,"disableSSLv2Check()")."</td>
		</tr>	
	";
	
	
	
	
	
	if($use_apache==null){$use_apache="<input type='hidden' name='ApacheArticaEnabled' value='0'>";}
	
	$html="
	<input type='hidden' id='interface_restarted' value='{interface_restarted}'>
	<form name='FFM109'>
	<input type='hidden' name='http_settings' value='yes'>
	<script>
		function LighttpdArticaDisabledCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('LighttpdArticaDisabled').checked){
				if(!confirm('$ConfirmDisableArticaWebConsole')){return;}
				XHR.appendData('LighttpdArticaDisabled',1);
				
			}else{
				XHR.appendData('LighttpdArticaDisabled',0);
			}
			LighttpdArticaDisabledcheck();
			XHR.sendAndLoad('$page', 'GET');
		
		}
		
		function disableSSLv2Check(){
			var XHR = new XHRConnection();
			if(document.getElementById('LighttpdArticaDisableSSLv2').checked){
				XHR.appendData('LighttpdArticaDisableSSLv2',1);
				
			}else{
				XHR.appendData('LighttpdArticaDisableSSLv2',0);
			}
			XHR.sendAndLoad('$page', 'POST');
		
		}		
		
		
		
		function WebArticaCheck(){
			document.getElementById('LighttpdArticaDisableSSLv2').disabled=true;
			if(document.getElementById('ArticaHttpUseSSL')){
				if(document.getElementById('ArticaHttpUseSSL').checked){
					document.getElementById('LighttpdArticaDisableSSLv2').disabled=false;	
					}
			}
		
		}
		
		function LighttpdArticaDisabledcheck(){
			if(!document.getElementById('LighttpdArticaDisabled')){return;}
			document.getElementById('LighttpdArticaDisableSSLv2').disabled=true;
			document.getElementById('ArticaHttpUseSSL').disabled=true;
			document.getElementById('https_port').disabled=true;
			document.getElementById('LighttpdUserAndGroup').disabled=true;
			if(!document.getElementById('LighttpdArticaDisabled').checked){
				document.getElementById('LighttpdArticaDisableSSLv2').disabled=false;
				document.getElementById('ArticaHttpUseSSL').disabled=false;
				document.getElementById('https_port').disabled=false;
				document.getElementById('LighttpdUserAndGroup').disabled=false;		
			}
		
		}
		
		
	WebArticaCheck();	
	LighttpdArticaDisabledcheck();
	</script>
	
	<H5>{web_interface_settings}</H5>
	";
	
	if($users->lighttpd_installed){
		$lighttpd= "
		$use_apache
		$lighttp_processes
		$lighttpd_usenossl
						
		";
		
	}else{$html=$html . "<input type='hidden' name='ApacheArticaEnabled' value='1'>";}
	
$html=$html . "
<table style='width:100%'>
<tr>
	<td valign='top'>
	". Paragraphe("64-settings.png","{advanced_options}","{php_advanced_options_text}","javascript:Loadjs('phpconfig.php')")."</td>
	<td valign='top'>
			<table style='width:100%' class=form>
			$lighttpd
			<tr>
				<td nowrap class=legend>{listen_port}:</strong></td>
				<td>" . Field_text('https_port',trim($ArticaHttpsPort),'width:100px;font-size:13px;padding:3px')."</td>
			</tr>
			<tr>
				<td nowrap class=legend>{username}:</strong></td>
				<td>" . Field_text('LighttpdUserAndGroup',trim($LighttpdUserAndGroup),'width:130px;font-size:13px;padding:3px')."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'>
					<span style='padding-top:3px'><a href='javascript:blur();' OnClick=\"javascript:Loadjs('phpconfig.php');\">{advanced_options}</a></span>
				</td>
			</tr>			
			<tr>
				<td colspan=2 align='right'>
					<hr>
					". button("{apply}","HTTPS_PORT()")."
					
				</td>
			</tr>
		
		</table>
	</td>
</tR>
</table>
</form>
".TIME_ZONE_SETTINGS();
	
	return  $html;
	
}

function TIME_ZONE_SETTINGS(){
	
$timezone[]="Africa/Abidjan";                 //,0x000000 },
	$timezone[]="Africa/Accra";                   //,0x000055 },
	$timezone[]="Africa/Addis_Ababa";             //,0x0000FD },
	$timezone[]="Africa/Algiers";                 //,0x000153 },
	$timezone[]="Africa/Asmara";                  //,0x00027E },
	$timezone[]="Africa/Asmera";                  //,0x0002D4 },
	$timezone[]="Africa/Bamako";                  //,0x00032A },
	$timezone[]="Africa/Bangui";                  //,0x000395 },
	$timezone[]="Africa/Banjul";                  //,0x0003EA },
	$timezone[]="Africa/Bissau";                  //,0x000461 },
	$timezone[]="Africa/Blantyre";                //,0x0004C7 },
	$timezone[]="Africa/Brazzaville";             //,0x00051C },
	$timezone[]="Africa/Bujumbura";               //,0x000571 },
	$timezone[]="Africa/Cairo";                   //,0x0005B5 },
	$timezone[]="Africa/Casablanca";              //,0x00097C },
	$timezone[]="Africa/Ceuta";                   //,0x000A58 },
	$timezone[]="Africa/Conakry";                 //,0x000D5F },
	$timezone[]="Africa/Dakar";                   //,0x000DCA },
	$timezone[]="Africa/Dar_es_Salaam";           //,0x000E30 },
	$timezone[]="Africa/Djibouti";                //,0x000E9D },
	$timezone[]="Africa/Douala";                  //,0x000EF2 },
	$timezone[]="Africa/El_Aaiun";                //,0x000F47 },
	$timezone[]="Africa/Freetown";                //,0x000FAD },
	$timezone[]="Africa/Gaborone";                //,0x0010BC },
	$timezone[]="Africa/Harare";                  //,0x001117 },
	$timezone[]="Africa/Johannesburg";            //,0x00116C },
	$timezone[]="Africa/Kampala";                 //,0x0011DA },
	$timezone[]="Africa/Khartoum";                //,0x001259 },
	$timezone[]="Africa/Kigali";                  //,0x00136C },
	$timezone[]="Africa/Kinshasa";                //,0x0013C1 },
	$timezone[]="Africa/Lagos";                   //,0x00141C },
	$timezone[]="Africa/Libreville";              //,0x001471 },
	$timezone[]="Africa/Lome";                    //,0x0014C6 },
	$timezone[]="Africa/Luanda";                  //,0x00150A },
	$timezone[]="Africa/Lubumbashi";              //,0x00155F },
	$timezone[]="Africa/Lusaka";                  //,0x0015BA },
	$timezone[]="Africa/Malabo";                  //,0x00160F },
	$timezone[]="Africa/Maputo";                  //,0x001675 },
	$timezone[]="Africa/Maseru";                  //,0x0016CA },
	$timezone[]="Africa/Mbabane";                 //,0x001732 },
	$timezone[]="Africa/Mogadishu";               //,0x001788 },
	$timezone[]="Africa/Monrovia";                //,0x0017E3 },
	$timezone[]="Africa/Nairobi";                 //,0x001849 },
	$timezone[]="Africa/Ndjamena";                //,0x0018C8 },
	$timezone[]="Africa/Niamey";                  //,0x001934 },
	$timezone[]="Africa/Nouakchott";              //,0x0019A7 },
	$timezone[]="Africa/Ouagadougou";             //,0x001A12 },
	$timezone[]="Africa/Porto-Novo";              //,0x001A67 },
	$timezone[]="Africa/Sao_Tome";                //,0x001ACD },
	$timezone[]="Africa/Timbuktu";                //,0x001B22 },
	$timezone[]="Africa/Tripoli";                 //,0x001B8D },
	$timezone[]="Africa/Tunis";                   //,0x001C87 },
	$timezone[]="Africa/Windhoek";                //,0x001EB1 },
	$timezone[]="America/Adak";                   //,0x0020F8 },
	$timezone[]="America/Anchorage";              //,0x00246E },
	$timezone[]="America/Anguilla";               //,0x0027E2 },
	$timezone[]="America/Antigua";                //,0x002837 },
	$timezone[]="America/Araguaina";              //,0x00289D },
	$timezone[]="America/Argentina/Buenos_Aires"; //,0x0029F8 },
	$timezone[]="America/Argentina/Catamarca";    //,0x002BA6 },
	$timezone[]="America/Argentina/ComodRivadavia";  //,0x002D67 },
	$timezone[]="America/Argentina/Cordoba";      //,0x002F0D },
	$timezone[]="America/Argentina/Jujuy";        //,0x0030E2 },
	$timezone[]="America/Argentina/La_Rioja";     //,0x003296 },
	$timezone[]="America/Argentina/Mendoza";      //,0x00344E },
	$timezone[]="America/Argentina/Rio_Gallegos"; //,0x00360E },
	$timezone[]="America/Argentina/Salta";        //,0x0037C3 },
	$timezone[]="America/Argentina/San_Juan";     //,0x00396F },
	$timezone[]="America/Argentina/San_Luis";     //,0x003B27 },
	$timezone[]="America/Argentina/Tucuman";      //,0x003E05 },
	$timezone[]="America/Argentina/Ushuaia";      //,0x003FC1 },
	$timezone[]="America/Aruba";                  //,0x00417C },
	$timezone[]="America/Asuncion";               //,0x0041E2 },
	$timezone[]="America/Atikokan";               //,0x0044C7 },
	$timezone[]="America/Atka";                   //,0x00459D },
	$timezone[]="America/Bahia";                  //,0x004903 },
	$timezone[]="America/Barbados";               //,0x004A8C },
	$timezone[]="America/Belem";                  //,0x004B26 },
	$timezone[]="America/Belize";                 //,0x004C21 },
	$timezone[]="America/Blanc-Sablon";           //,0x004D9D },
	$timezone[]="America/Boa_Vista";              //,0x004E51 },
	$timezone[]="America/Bogota";                 //,0x004F5A },
	$timezone[]="America/Boise";                  //,0x004FC6 },
	$timezone[]="America/Buenos_Aires";           //,0x00535D },
	$timezone[]="America/Cambridge_Bay";          //,0x0054F6 },
	$timezone[]="America/Campo_Grande";           //,0x00581E },
	$timezone[]="America/Cancun";                 //,0x005B0D },
	$timezone[]="America/Caracas";                //,0x005D4F },
	$timezone[]="America/Catamarca";              //,0x005DB6 },
	$timezone[]="America/Cayenne";                //,0x005F5C },
	$timezone[]="America/Cayman";                 //,0x005FBE },
	$timezone[]="America/Chicago";                //,0x006013 },
	$timezone[]="America/Chihuahua";              //,0x00652A },
	$timezone[]="America/Coral_Harbour";          //,0x006779 },
	$timezone[]="America/Cordoba";                //,0x00680B },
	$timezone[]="America/Costa_Rica";             //,0x0069B1 },
	$timezone[]="America/Cuiaba";                 //,0x006A3B },
	$timezone[]="America/Curacao";                //,0x006D19 },
	$timezone[]="America/Danmarkshavn";           //,0x006D7F },
	$timezone[]="America/Dawson";                 //,0x006EC3 },
	$timezone[]="America/Dawson_Creek";           //,0x0071E0 },
	$timezone[]="America/Denver";                 //,0x0073BA },
	$timezone[]="America/Detroit";                //,0x007740 },
	$timezone[]="America/Dominica";               //,0x007A9F },
	$timezone[]="America/Edmonton";               //,0x007AF4 },
	$timezone[]="America/Eirunepe";               //,0x007EAC },
	$timezone[]="America/El_Salvador";            //,0x007FBF },
	$timezone[]="America/Ensenada";               //,0x008034 },
	$timezone[]="America/Fort_Wayne";             //,0x0084DB },
	$timezone[]="America/Fortaleza";              //,0x00839D },
	$timezone[]="America/Glace_Bay";              //,0x008745 },
	$timezone[]="America/Godthab";                //,0x008ABC },
	$timezone[]="America/Goose_Bay";              //,0x008D80 },
	$timezone[]="America/Grand_Turk";             //,0x00923D },
	$timezone[]="America/Grenada";                //,0x0094EC },
	$timezone[]="America/Guadeloupe";             //,0x009541 },
	$timezone[]="America/Guatemala";              //,0x009596 },
	$timezone[]="America/Guayaquil";              //,0x00961F },
	$timezone[]="America/Guyana";                 //,0x00967C },
	$timezone[]="America/Halifax";                //,0x0096FD },
	$timezone[]="America/Havana";                 //,0x009C13 },
	$timezone[]="America/Hermosillo";             //,0x009F86 },
	$timezone[]="America/Indiana/Indianapolis";   //,0x00A064 },
	$timezone[]="America/Indiana/Knox";           //,0x00A2F5 },
	$timezone[]="America/Indiana/Marengo";        //,0x00A68C },
	$timezone[]="America/Indiana/Petersburg";     //,0x00A932 },
	$timezone[]="America/Indiana/Tell_City";      //,0x00AE7F },
	$timezone[]="America/Indiana/Vevay";          //,0x00B118 },
	$timezone[]="America/Indiana/Vincennes";      //,0x00B353 },
	$timezone[]="America/Indiana/Winamac";        //,0x00B607 },
	$timezone[]="America/Indianapolis";           //,0x00AC15 },
	$timezone[]="America/Inuvik";                 //,0x00B8C0 },
	$timezone[]="America/Iqaluit";                //,0x00BBB7 },
	$timezone[]="America/Jamaica";                //,0x00BED9 },
	$timezone[]="America/Jujuy";                  //,0x00BF9E },
	$timezone[]="America/Juneau";                 //,0x00C148 },
	$timezone[]="America/Kentucky/Louisville";    //,0x00C4C6 },
	$timezone[]="America/Kentucky/Monticello";    //,0x00C8E4 },
	$timezone[]="America/Knox_IN";                //,0x00CC69 },
	$timezone[]="America/La_Paz";                 //,0x00CFDA },
	$timezone[]="America/Lima";                   //,0x00D041 },
	$timezone[]="America/Los_Angeles";            //,0x00D0E9 },
	$timezone[]="America/Louisville";             //,0x00D4FA },
	$timezone[]="America/Maceio";                 //,0x00D8EF },
	$timezone[]="America/Managua";                //,0x00DA29 },
	$timezone[]="America/Manaus";                 //,0x00DADC },
	$timezone[]="America/Marigot";                //,0x00DBDE },
	$timezone[]="America/Martinique";             //,0x00DC33 },
	$timezone[]="America/Mazatlan";               //,0x00DC9F },
	$timezone[]="America/Mendoza";                //,0x00DF0C },
	$timezone[]="America/Menominee";              //,0x00E0C0 },
	$timezone[]="America/Merida";                 //,0x00E441 },
	$timezone[]="America/Mexico_City";            //,0x00E67C },
	$timezone[]="America/Miquelon";               //,0x00E8F7 },
	$timezone[]="America/Moncton";                //,0x00EB69 },
	$timezone[]="America/Monterrey";              //,0x00F000 },
	$timezone[]="America/Montevideo";             //,0x00F247 },
	$timezone[]="America/Montreal";               //,0x00F559 },
	$timezone[]="America/Montserrat";             //,0x00FA6F },
	$timezone[]="America/Nassau";                 //,0x00FAC4 },
	$timezone[]="America/New_York";               //,0x00FE09 },
	$timezone[]="America/Nipigon";                //,0x010314 },
	$timezone[]="America/Nome";                   //,0x010665 },
	$timezone[]="America/Noronha";                //,0x0109E3 },
	$timezone[]="America/North_Dakota/Center";    //,0x010B13 },
	$timezone[]="America/North_Dakota/New_Salem"; //,0x010EA7 },
	$timezone[]="America/Panama";                 //,0x011250 },
	$timezone[]="America/Pangnirtung";            //,0x0112A5 },
	$timezone[]="America/Paramaribo";             //,0x0115DB },
	$timezone[]="America/Phoenix";                //,0x01166D },
	$timezone[]="America/Port-au-Prince";         //,0x01171B },
	$timezone[]="America/Port_of_Spain";          //,0x011936 },
	$timezone[]="America/Porto_Acre";             //,0x011837 },
	$timezone[]="America/Porto_Velho";            //,0x01198B },
	$timezone[]="America/Puerto_Rico";            //,0x011A81 },
	$timezone[]="America/Rainy_River";            //,0x011AEC },
	$timezone[]="America/Rankin_Inlet";           //,0x011E24 },
	$timezone[]="America/Recife";                 //,0x01210A },
	$timezone[]="America/Regina";                 //,0x012234 },
	$timezone[]="America/Resolute";               //,0x0123F2 },
	$timezone[]="America/Rio_Branco";             //,0x0126EB },
	$timezone[]="America/Rosario";                //,0x0127EE },
	$timezone[]="America/Santarem";               //,0x012994 },
	$timezone[]="America/Santiago";               //,0x012A99 },
	$timezone[]="America/Santo_Domingo";          //,0x012E42 },
	$timezone[]="America/Sao_Paulo";              //,0x012F08 },
	$timezone[]="America/Scoresbysund";           //,0x013217 },
	$timezone[]="America/Shiprock";               //,0x013505 },
	$timezone[]="America/St_Barthelemy";          //,0x013894 },
	$timezone[]="America/St_Johns";               //,0x0138E9 },
	$timezone[]="America/St_Kitts";               //,0x013E3C },
	$timezone[]="America/St_Lucia";               //,0x013E91 },
	$timezone[]="America/St_Thomas";              //,0x013EE6 },
	$timezone[]="America/St_Vincent";             //,0x013F3B },
	$timezone[]="America/Swift_Current";          //,0x013F90 },
	$timezone[]="America/Tegucigalpa";            //,0x0140B1 },
	$timezone[]="America/Thule";                  //,0x014130 },
	$timezone[]="America/Thunder_Bay";            //,0x014377 },
	$timezone[]="America/Tijuana";                //,0x0146C0 },
	$timezone[]="America/Toronto";                //,0x014A35 },
	$timezone[]="America/Tortola";                //,0x014F4C },
	$timezone[]="America/Vancouver";              //,0x014FA1 },
	$timezone[]="America/Virgin";                 //,0x0153DE },
	$timezone[]="America/Whitehorse";             //,0x015433 },
	$timezone[]="America/Winnipeg";               //,0x015750 },
	$timezone[]="America/Yakutat";                //,0x015B90 },
	$timezone[]="America/Yellowknife";            //,0x015EFB },
	$timezone[]="Antarctica/Casey";               //,0x01620B },
	$timezone[]="Antarctica/Davis";               //,0x016291 },
	$timezone[]="Antarctica/DumontDUrville";      //,0x01631B },
	$timezone[]="Antarctica/Mawson";              //,0x0163AD },
	$timezone[]="Antarctica/McMurdo";             //,0x016429 },
	$timezone[]="Antarctica/Palmer";              //,0x01672B },
	$timezone[]="Antarctica/Rothera";             //,0x016A47 },
	$timezone[]="Antarctica/South_Pole";          //,0x016ABD },
	$timezone[]="Antarctica/Syowa";               //,0x016DC5 },
	$timezone[]="Antarctica/Vostok";              //,0x016E33 },
	$timezone[]="Arctic/Longyearbyen";            //,0x016EA8 },
	$timezone[]="Asia/Aden";                      //,0x0171DA },
	$timezone[]="Asia/Almaty";                    //,0x01722F },
	$timezone[]="Asia/Amman";                     //,0x0173AE },
	$timezone[]="Asia/Anadyr";                    //,0x01766E },
	$timezone[]="Asia/Aqtau";                     //,0x01795C },
	$timezone[]="Asia/Aqtobe";                    //,0x017B5B },
	$timezone[]="Asia/Ashgabat";                  //,0x017D13 },
	$timezone[]="Asia/Ashkhabad";                 //,0x017E30 },
	$timezone[]="Asia/Baghdad";                   //,0x017F4D },
	$timezone[]="Asia/Bahrain";                   //,0x0180C2 },
	$timezone[]="Asia/Baku";                      //,0x018128 },
	$timezone[]="Asia/Bangkok";                   //,0x018410 },
	$timezone[]="Asia/Beirut";                    //,0x018465 },
	$timezone[]="Asia/Bishkek";                   //,0x018772 },
	$timezone[]="Asia/Brunei";                    //,0x01891E },
	$timezone[]="Asia/Calcutta";                  //,0x018980 },
	$timezone[]="Asia/Choibalsan";                //,0x0189F9 },
	$timezone[]="Asia/Chongqing";                 //,0x018B72 },
	$timezone[]="Asia/Chungking";                 //,0x018C61 },
	$timezone[]="Asia/Colombo";                   //,0x018D10 },
	$timezone[]="Asia/Dacca";                     //,0x018DAC },
	$timezone[]="Asia/Damascus";                  //,0x018E4D },
	$timezone[]="Asia/Dhaka";                     //,0x01919D },
	$timezone[]="Asia/Dili";                      //,0x01923E },
	$timezone[]="Asia/Dubai";                     //,0x0192C7 },
	$timezone[]="Asia/Dushanbe";                  //,0x01931C },
	$timezone[]="Asia/Gaza";                      //,0x01941F },
	$timezone[]="Asia/Harbin";                    //,0x019768 },
	$timezone[]="Asia/Ho_Chi_Minh";               //,0x01984F },
	$timezone[]="Asia/Hong_Kong";                 //,0x0198C7 },
	$timezone[]="Asia/Hovd";                      //,0x019A93 },
	$timezone[]="Asia/Irkutsk";                   //,0x019C0B },
	$timezone[]="Asia/Istanbul";                  //,0x019EF2 },
	$timezone[]="Asia/Jakarta";                   //,0x01A2DF },
	$timezone[]="Asia/Jayapura";                  //,0x01A389 },
	$timezone[]="Asia/Jerusalem";                 //,0x01A40D },
	$timezone[]="Asia/Kabul";                     //,0x01A73C },
	$timezone[]="Asia/Kamchatka";                 //,0x01A78D },
	$timezone[]="Asia/Karachi";                   //,0x01AA72 },
	$timezone[]="Asia/Kashgar";                   //,0x01AC3F },
	$timezone[]="Asia/Kathmandu";                 //,0x01AD10 },
	$timezone[]="Asia/Katmandu";                  //,0x01AD76 },
	$timezone[]="Asia/Kolkata";                   //,0x01ADDC },
	$timezone[]="Asia/Krasnoyarsk";               //,0x01AE55 },
	$timezone[]="Asia/Kuala_Lumpur";              //,0x01B13E },
	$timezone[]="Asia/Kuching";                   //,0x01B1FB },
	$timezone[]="Asia/Kuwait";                    //,0x01B2E9 },
	$timezone[]="Asia/Macao";                     //,0x01B33E },
	$timezone[]="Asia/Macau";                     //,0x01B479 },
	$timezone[]="Asia/Magadan";                   //,0x01B5B4 },
	$timezone[]="Asia/Makassar";                  //,0x01B897 },
	$timezone[]="Asia/Manila";                    //,0x01B950 },
	$timezone[]="Asia/Muscat";                    //,0x01B9D5 },
	$timezone[]="Asia/Nicosia";                   //,0x01BA2A },
	$timezone[]="Asia/Novokuznetsk";              //,0x01BD12 },
	$timezone[]="Asia/Novosibirsk";               //,0x01C015 },
	$timezone[]="Asia/Omsk";                      //,0x01C309 },
	$timezone[]="Asia/Oral";                      //,0x01C5F1 },
	$timezone[]="Asia/Phnom_Penh";                //,0x01C7C1 },
	$timezone[]="Asia/Pontianak";                 //,0x01C839 },
	$timezone[]="Asia/Pyongyang";                 //,0x01C8FA },
	$timezone[]="Asia/Qatar";                     //,0x01C967 },
	$timezone[]="Asia/Qyzylorda";                 //,0x01C9CD },
	$timezone[]="Asia/Rangoon";                   //,0x01CBA3 },
	$timezone[]="Asia/Riyadh";                    //,0x01CC1B },
	$timezone[]="Asia/Saigon";                    //,0x01CC70 },
	$timezone[]="Asia/Sakhalin";                  //,0x01CCE8 },
	$timezone[]="Asia/Samarkand";                 //,0x01CFE8 },
	$timezone[]="Asia/Seoul";                     //,0x01D11E },
	$timezone[]="Asia/Shanghai";                  //,0x01D1C2 },
	$timezone[]="Asia/Singapore";                 //,0x01D2A2 },
	$timezone[]="Asia/Taipei";                    //,0x01D359 },
	$timezone[]="Asia/Tashkent";                  //,0x01D471 },
	$timezone[]="Asia/Tbilisi";                   //,0x01D5A2 },
	$timezone[]="Asia/Tehran";                    //,0x01D75C },
	$timezone[]="Asia/Tel_Aviv";                  //,0x01D9CA },
	$timezone[]="Asia/Thimbu";                    //,0x01DCF9 },
	$timezone[]="Asia/Thimphu";                   //,0x01DD5F },
	$timezone[]="Asia/Tokyo";                     //,0x01DDC5 },
	$timezone[]="Asia/Ujung_Pandang";             //,0x01DE4E },
	$timezone[]="Asia/Ulaanbaatar";               //,0x01DECA },
	$timezone[]="Asia/Ulan_Bator";                //,0x01E025 },
	$timezone[]="Asia/Urumqi";                    //,0x01E172 },
	$timezone[]="Asia/Vientiane";                 //,0x01E239 },
	$timezone[]="Asia/Vladivostok";               //,0x01E2B1 },
	$timezone[]="Asia/Yakutsk";                   //,0x01E59E },
	$timezone[]="Asia/Yekaterinburg";             //,0x01E884 },
	$timezone[]="Asia/Yerevan";                   //,0x01EB90 },
	$timezone[]="Atlantic/Azores";                //,0x01EE94 },
	$timezone[]="Atlantic/Bermuda";               //,0x01F397 },
	$timezone[]="Atlantic/Canary";                //,0x01F678 },
	$timezone[]="Atlantic/Cape_Verde";            //,0x01F94E },
	$timezone[]="Atlantic/Faeroe";                //,0x01F9C7 },
	$timezone[]="Atlantic/Faroe";                 //,0x01FC6B },
	$timezone[]="Atlantic/Jan_Mayen";             //,0x01FF0F },
	$timezone[]="Atlantic/Madeira";               //,0x020241 },
	$timezone[]="Atlantic/Reykjavik";             //,0x02074A },
	$timezone[]="Atlantic/South_Georgia";         //,0x020903 },
	$timezone[]="Atlantic/St_Helena";             //,0x020C1B },
	$timezone[]="Atlantic/Stanley";               //,0x020947 },
	$timezone[]="Australia/ACT";                  //,0x020C70 },
	$timezone[]="Australia/Adelaide";             //,0x020F8D },
	$timezone[]="Australia/Brisbane";             //,0x0212B9 },
	$timezone[]="Australia/Broken_Hill";          //,0x021380 },
	$timezone[]="Australia/Canberra";             //,0x0216BE },
	$timezone[]="Australia/Currie";               //,0x0219DB },
	$timezone[]="Australia/Darwin";               //,0x021D0E },
	$timezone[]="Australia/Eucla";                //,0x021D94 },
	$timezone[]="Australia/Hobart";               //,0x021E69 },
	$timezone[]="Australia/LHI";                  //,0x0221C7 },
	$timezone[]="Australia/Lindeman";             //,0x022462 },
	$timezone[]="Australia/Lord_Howe";            //,0x022543 },
	$timezone[]="Australia/Melbourne";            //,0x0227EE },
	$timezone[]="Australia/North";                //,0x022B13 },
	$timezone[]="Australia/NSW";                  //,0x022B87 },
	$timezone[]="Australia/Perth";                //,0x022EA4 },
	$timezone[]="Australia/Queensland";           //,0x022F7C },
	$timezone[]="Australia/South";                //,0x023028 },
	$timezone[]="Australia/Sydney";               //,0x023345 },
	$timezone[]="Australia/Tasmania";             //,0x023682 },
	$timezone[]="Australia/Victoria";             //,0x0239C7 },
	$timezone[]="Australia/West";                 //,0x023CE4 },
	$timezone[]="Australia/Yancowinna";           //,0x023D9A },
	$timezone[]="Brazil/Acre";                    //,0x0240BC },
	$timezone[]="Brazil/DeNoronha";               //,0x0241BB },
	$timezone[]="Brazil/East";                    //,0x0242DB },
	$timezone[]="Brazil/West";                    //,0x0245B8 },
	$timezone[]="Canada/Atlantic";                //,0x0246B0 },
	$timezone[]="Canada/Central";                 //,0x024B98 },
	$timezone[]="Canada/East-Saskatchewan";       //,0x0254A2 },
	$timezone[]="Canada/Eastern";                 //,0x024FB2 },
	$timezone[]="Canada/Mountain";                //,0x02562B },
	$timezone[]="Canada/Newfoundland";            //,0x0259A1 },
	$timezone[]="Canada/Pacific";                 //,0x025ECC },
	$timezone[]="Canada/Saskatchewan";            //,0x0262E5 },
	$timezone[]="Canada/Yukon";                   //,0x02646E },
	$timezone[]="CET";                            //,0x026771 },
	$timezone[]="Chile/Continental";              //,0x026A7A },
	$timezone[]="Chile/EasterIsland";             //,0x026E15 },
	$timezone[]="CST6CDT";                        //,0x027157 },
	$timezone[]="Cuba";                           //,0x0274A8 },
	$timezone[]="EET";                            //,0x02781B },
	$timezone[]="Egypt";                          //,0x027ACE },
	$timezone[]="Eire";                           //,0x027E95 },
	$timezone[]="EST";                            //,0x0283A6 },
	$timezone[]="EST5EDT";                        //,0x0283EA },
	$timezone[]="Etc/GMT";                        //,0x02873B },
	$timezone[]="Etc/GMT+0";                      //,0x028807 },
	$timezone[]="Etc/GMT+1";                      //,0x028891 },
	$timezone[]="Etc/GMT+10";                     //,0x02891E },
	$timezone[]="Etc/GMT+11";                     //,0x0289AC },
	$timezone[]="Etc/GMT+12";                     //,0x028A3A },
	$timezone[]="Etc/GMT+2";                      //,0x028B55 },
	$timezone[]="Etc/GMT+3";                      //,0x028BE1 },
	$timezone[]="Etc/GMT+4";                      //,0x028C6D },
	$timezone[]="Etc/GMT+5";                      //,0x028CF9 },
	$timezone[]="Etc/GMT+6";                      //,0x028D85 },
	$timezone[]="Etc/GMT+7";                      //,0x028E11 },
	$timezone[]="Etc/GMT+8";                      //,0x028E9D },
	$timezone[]="Etc/GMT+9";                      //,0x028F29 },
	$timezone[]="Etc/GMT-0";                      //,0x0287C3 },
	$timezone[]="Etc/GMT-1";                      //,0x02884B },
	$timezone[]="Etc/GMT-10";                     //,0x0288D7 },
	$timezone[]="Etc/GMT-11";                     //,0x028965 },
	$timezone[]="Etc/GMT-12";                     //,0x0289F3 },
	$timezone[]="Etc/GMT-13";                     //,0x028A81 },
	$timezone[]="Etc/GMT-14";                     //,0x028AC8 },
	$timezone[]="Etc/GMT-2";                      //,0x028B0F },
	$timezone[]="Etc/GMT-3";                      //,0x028B9B },
	$timezone[]="Etc/GMT-4";                      //,0x028C27 },
	$timezone[]="Etc/GMT-5";                      //,0x028CB3 },
	$timezone[]="Etc/GMT-6";                      //,0x028D3F },
	$timezone[]="Etc/GMT-7";                      //,0x028DCB },
	$timezone[]="Etc/GMT-8";                      //,0x028E57 },
	$timezone[]="Etc/GMT-9";                      //,0x028EE3 },
	$timezone[]="Etc/GMT0";                       //,0x02877F },
	$timezone[]="Etc/Greenwich";                  //,0x028F6F },
	$timezone[]="Etc/UCT";                        //,0x028FB3 },
	$timezone[]="Etc/Universal";                  //,0x028FF7 },
	$timezone[]="Etc/UTC";                        //,0x02903B },
	$timezone[]="Etc/Zulu";                       //,0x02907F },
	$timezone[]="Europe/Amsterdam";               //,0x0290C3 },
	$timezone[]="Europe/Andorra";                 //,0x029501 },
	$timezone[]="Europe/Athens";                  //,0x02977D },
	$timezone[]="Europe/Belfast";                 //,0x029AC0 },
	$timezone[]="Europe/Belgrade";                //,0x029FF7 },
	$timezone[]="Europe/Berlin";                  //,0x02A2C0 },
	$timezone[]="Europe/Bratislava";              //,0x02A616 },
	$timezone[]="Europe/Brussels";                //,0x02A948 },
	$timezone[]="Europe/Bucharest";               //,0x02AD7F },
	$timezone[]="Europe/Budapest";                //,0x02B0A9 },
	$timezone[]="Europe/Chisinau";                //,0x02B41C },
	$timezone[]="Europe/Copenhagen";              //,0x02B7AA },
	$timezone[]="Europe/Dublin";                  //,0x02BAB4 },
	$timezone[]="Europe/Gibraltar";               //,0x02BFC5 },
	$timezone[]="Europe/Guernsey";                //,0x02C41C },
	$timezone[]="Europe/Helsinki";                //,0x02C953 },
	$timezone[]="Europe/Isle_of_Man";             //,0x02CC09 },
	$timezone[]="Europe/Istanbul";                //,0x02D140 },
	$timezone[]="Europe/Jersey";                  //,0x02D52D },
	$timezone[]="Europe/Kaliningrad";             //,0x02DA64 },
	$timezone[]="Europe/Kiev";                    //,0x02DDC7 },
	$timezone[]="Europe/Lisbon";                  //,0x02E0DE },
	$timezone[]="Europe/Ljubljana";               //,0x02E5E2 },
	$timezone[]="Europe/London";                  //,0x02E8AB },
	$timezone[]="Europe/Luxembourg";              //,0x02EDE2 },
	$timezone[]="Europe/Madrid";                  //,0x02F238 },
	$timezone[]="Europe/Malta";                   //,0x02F5FE },
	$timezone[]="Europe/Mariehamn";               //,0x02F9B7 },
	$timezone[]="Europe/Minsk";                   //,0x02FC6D },
	$timezone[]="Europe/Monaco";                  //,0x02FF78 },
	$timezone[]="Europe/Moscow";                  //,0x0303B3 },
	$timezone[]="Europe/Nicosia";                 //,0x030705 },
	$timezone[]="Europe/Oslo";                    //,0x0309ED },
	$timezone[]="Europe/Paris";                   //,0x030D1F },
	$timezone[]="Europe/Podgorica";               //,0x031165 },
	$timezone[]="Europe/Prague";                  //,0x03142E },
	$timezone[]="Europe/Riga";                    //,0x031760 },
	$timezone[]="Europe/Rome";                    //,0x031AA5 },
	$timezone[]="Europe/Samara";                  //,0x031E68 },
	$timezone[]="Europe/San_Marino";              //,0x032194 },
	$timezone[]="Europe/Sarajevo";                //,0x032557 },
	$timezone[]="Europe/Simferopol";              //,0x032820 },
	$timezone[]="Europe/Skopje";                  //,0x032B4B },
	$timezone[]="Europe/Sofia";                   //,0x032E14 },
	$timezone[]="Europe/Stockholm";               //,0x03311C },
	$timezone[]="Europe/Tallinn";                 //,0x0333CB },
	$timezone[]="Europe/Tirane";                  //,0x033705 },
	$timezone[]="Europe/Tiraspol";                //,0x033A0B },
	$timezone[]="Europe/Uzhgorod";                //,0x033D99 },
	$timezone[]="Europe/Vaduz";                   //,0x0340B0 },
	$timezone[]="Europe/Vatican";                 //,0x034343 },
	$timezone[]="Europe/Vienna";                  //,0x034706 },
	$timezone[]="Europe/Vilnius";                 //,0x034A33 },
	$timezone[]="Europe/Volgograd";               //,0x034D72 },
	$timezone[]="Europe/Warsaw";                  //,0x03507B },
	$timezone[]="Europe/Zagreb";                  //,0x03545C },
	$timezone[]="Europe/Zaporozhye";              //,0x035725 },
	$timezone[]="Europe/Zurich";                  //,0x035A66 },
	$timezone[]="Factory";                        //,0x035D15 },
	$timezone[]="GB";                             //,0x035D86 },
	$timezone[]="GB-Eire";                        //,0x0362BD },
	$timezone[]="GMT";                            //,0x0367F4 },
	$timezone[]="GMT+0";                          //,0x0368C0 },
	$timezone[]="GMT-0";                          //,0x03687C },
	$timezone[]="GMT0";                           //,0x036838 },
	$timezone[]="Greenwich";                      //,0x036904 },
	$timezone[]="Hongkong";                       //,0x036948 },
	$timezone[]="HST";                            //,0x036B14 },
	$timezone[]="Iceland";                        //,0x036B58 },
	$timezone[]="Indian/Antananarivo";            //,0x036D11 },
	$timezone[]="Indian/Chagos";                  //,0x036D85 },
	$timezone[]="Indian/Christmas";               //,0x036DE7 },
	$timezone[]="Indian/Cocos";                   //,0x036E2B },
	$timezone[]="Indian/Comoro";                  //,0x036E6F },
	$timezone[]="Indian/Kerguelen";               //,0x036EC4 },
	$timezone[]="Indian/Mahe";                    //,0x036F19 },
	$timezone[]="Indian/Maldives";                //,0x036F6E },
	$timezone[]="Indian/Mauritius";               //,0x036FC3 },
	$timezone[]="Indian/Mayotte";                 //,0x037039 },
	$timezone[]="Indian/Reunion";                 //,0x03708E },
	$timezone[]="Iran";                           //,0x0370E3 },
	$timezone[]="Israel";                         //,0x037351 },
	$timezone[]="Jamaica";                        //,0x037680 },
	$timezone[]="Japan";                          //,0x037745 },
	$timezone[]="Kwajalein";                      //,0x0377CE },
	$timezone[]="Libya";                          //,0x037831 },
	$timezone[]="MET";                            //,0x03792B },
	$timezone[]="Mexico/BajaNorte";               //,0x037C34 },
	$timezone[]="Mexico/BajaSur";                 //,0x037F9D },
	$timezone[]="Mexico/General";                 //,0x0381E2 },
	$timezone[]="MST";                            //,0x038440 },
	$timezone[]="MST7MDT";                        //,0x038484 },
	$timezone[]="Navajo";                         //,0x0387D5 },
	$timezone[]="NZ";                             //,0x038B4E },
	$timezone[]="NZ-CHAT";                        //,0x038ECC },
	$timezone[]="Pacific/Apia";                   //,0x0391B4 },
	$timezone[]="Pacific/Auckland";               //,0x039232 },
	$timezone[]="Pacific/Chatham";                //,0x0395BE },
	$timezone[]="Pacific/Easter";                 //,0x0398B5 },
	$timezone[]="Pacific/Efate";                  //,0x039C13 },
	$timezone[]="Pacific/Enderbury";              //,0x039CD9 },
	$timezone[]="Pacific/Fakaofo";                //,0x039D47 },
	$timezone[]="Pacific/Fiji";                   //,0x039D8B },
	$timezone[]="Pacific/Funafuti";               //,0x039E01 },
	$timezone[]="Pacific/Galapagos";              //,0x039E45 },
	$timezone[]="Pacific/Gambier";                //,0x039EBD },
	$timezone[]="Pacific/Guadalcanal";            //,0x039F22 },
	$timezone[]="Pacific/Guam";                   //,0x039F77 },
	$timezone[]="Pacific/Honolulu";               //,0x039FCD },
	$timezone[]="Pacific/Johnston";               //,0x03A061 },
	$timezone[]="Pacific/Kiritimati";             //,0x03A0B3 },
	$timezone[]="Pacific/Kosrae";                 //,0x03A11E },
	$timezone[]="Pacific/Kwajalein";              //,0x03A17B },
	$timezone[]="Pacific/Majuro";                 //,0x03A1E7 },
	$timezone[]="Pacific/Marquesas";              //,0x03A246 },
	$timezone[]="Pacific/Midway";                 //,0x03A2AD },
	$timezone[]="Pacific/Nauru";                  //,0x03A337 },
	$timezone[]="Pacific/Niue";                   //,0x03A3AF },
	$timezone[]="Pacific/Norfolk";                //,0x03A40D },
	$timezone[]="Pacific/Noumea";                 //,0x03A462 },
	$timezone[]="Pacific/Pago_Pago";              //,0x03A4F2 },
	$timezone[]="Pacific/Palau";                  //,0x03A57B },
	$timezone[]="Pacific/Pitcairn";               //,0x03A5BF },
	$timezone[]="Pacific/Ponape";                 //,0x03A614 },
	$timezone[]="Pacific/Port_Moresby";           //,0x03A669 },
	$timezone[]="Pacific/Rarotonga";              //,0x03A6AD },
	$timezone[]="Pacific/Saipan";                 //,0x03A789 },
	$timezone[]="Pacific/Samoa";                  //,0x03A7EC },
	$timezone[]="Pacific/Tahiti";                 //,0x03A875 },
	$timezone[]="Pacific/Tarawa";                 //,0x03A8DA },
	$timezone[]="Pacific/Tongatapu";              //,0x03A92E },
	$timezone[]="Pacific/Truk";                   //,0x03A9BA },
	$timezone[]="Pacific/Wake";                   //,0x03AA13 },
	$timezone[]="Pacific/Wallis";                 //,0x03AA63 },
	$timezone[]="Pacific/Yap";                    //,0x03AAA7 },
	$timezone[]="Poland";                         //,0x03AAEC },
	$timezone[]="Portugal";                       //,0x03AECD },
	$timezone[]="PRC";                            //,0x03B3C9 },
	$timezone[]="PST8PDT";                        //,0x03B47A },
	$timezone[]="ROC";                            //,0x03B7CB },
	$timezone[]="ROK";                            //,0x03B8E3 },
	$timezone[]="Singapore";                      //,0x03B987 },
	$timezone[]="Turkey";                         //,0x03BA3E },
	$timezone[]="UCT";                            //,0x03BE2B },
	$timezone[]="Universal";                      //,0x03BE6F },
	$timezone[]="US/Alaska";                      //,0x03BEB3 },
	$timezone[]="US/Aleutian";                    //,0x03C21C },
	$timezone[]="US/Arizona";                     //,0x03C582 },
	$timezone[]="US/Central";                     //,0x03C610 },
	$timezone[]="US/East-Indiana";                //,0x03D01A },
	$timezone[]="US/Eastern";                     //,0x03CB1B },
	$timezone[]="US/Hawaii";                      //,0x03D284 },
	$timezone[]="US/Indiana-Starke";              //,0x03D312 },
	$timezone[]="US/Michigan";                    //,0x03D683 },
	$timezone[]="US/Mountain";                    //,0x03D9BA },
	$timezone[]="US/Pacific";                     //,0x03DD33 },
	$timezone[]="US/Pacific-New";                 //,0x03E138 },
	$timezone[]="US/Samoa";                       //,0x03E53D },
	$timezone[]="UTC";                            //,0x03E5C6 },
	$timezone[]="W-SU";                           //,0x03E8BD },
	$timezone[]="WET";                            //,0x03E60A },
	$timezone[]="Zulu";                           //,0x03EBF8 },	
	
	for($i=0;$i<count($timezone);$i++){
		$array[$timezone[$i]]=$timezone[$i];
	}
	$sock=new sockets();
	$timezone_def=trim($sock->GET_INFO('timezones'));
	if(trim($timezone_def)==null){$timezone_def="Europe/Paris";}
	$field=Field_array_Hash($array,'timezones',$timezone_def);
	$sock=new sockets();
	
	$default=
	
	$html="
	<H5>{timezones}</H5>
	<div id='timezones_div'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{timezone}:</td>
		<td>$field</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{apply}","SaveTimeZone()")."
		
	</tr>
	</table>
	</div>
	
	
	";
	
	return  $html;	
	
}

function TIME_ZONE_SAVE(){
	$sock=new sockets();
	$sock->SET_INFO('timezones',$_GET["timezones"]);
	$sock->getFrameWork('cmd.php?restart-web-server=yes');
}





FUNCTION HTTPS_PROCESSES(){
		$httpd=new httpd();
		$tpl=new templates();
		$lighttp_max_load_per_proc=$tpl->_parse_body('{lighttp_max_load_per_proc}');
		if(strlen($lighttp_max_load_per_proc)>40){$lighttp_max_load_per_proc=texttooltip(substr($lighttp_max_load_per_proc,0,37)."...",$lighttp_max_load_per_proc);}
		
		
$HTML="
<input type='hidden' id='interface_restarted' value='{interface_restarted}'>
<form name='FFM119'>
	<input type='hidden' name='lighttpd_procs' value='yes'>
	
	<table style='width:100%'>
	<tr>
		<td nowrap class=legend>{lighttp_max_proc}:</strong></td>
		<td>" . Field_text('lighttp_max_proc',trim($httpd->lighttp_max_proc),'width:30px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend>{lighttp_min_proc}:</strong></td>
		<td>" . Field_text('lighttp_min_proc',trim($httpd->lighttp_min_proc),'width:30px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend>$lighttp_max_load_per_proc:</strong></td>
		<td>" . Field_text('lighttp_max_load_per_proc',trim($httpd->lighttp_max_load_per_proc),'width:30px')."</td>
	</tr>		

	<tr>
		<td nowrap class=legend>{PHP_FCGI_CHILDREN}:</strong></td>
		<td>" . Field_text('PHP_FCGI_CHILDREN',trim($httpd->PHP_FCGI_CHILDREN),'width:30px')."</td>
	</tr>	
	<tr>
		<td nowrap class=legend>{PHP_FCGI_MAX_REQUESTS}:</strong></td>
		<td>" . Field_text('PHP_FCGI_MAX_REQUESTS',trim($httpd->PHP_FCGI_MAX_REQUESTS),'width:30px')."</td>
	</tr>		
	
	
	
	<tr>
		<td colspan=2 align='right'><hr>
		<input type='button' OnClick=\"javascript:HTTPS_PROCESSES();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
</table>
</form>";	
	
$HTML=RoundedLightWhite($HTML);
$HTML="<H1>lighttpd {processes}</H1>$HTML";


	echo $tpl->_ENGINE_parse_body($HTML);		
}

function SMTP_NOTIFICATIONS_TABS(){

	$tpl=new templates();
	$user=new usersMenus();
	$page=CurrentPageName();
	
	$array["notif1"]='{parameters}';
	$array["notif2"]='{notifications}';
	if($user->POSTFIX_INSTALLED){
		$array["notif3"]='{APP_POSTFIX}';
		
	}

	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?smtp-notifs-tab=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	return "
	<div id='main_config_notifs' style='width:100%;height:530px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_notifs').tabs({
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

function SMTP_NOTIFICATIONS_ADD_CC(){
	$sock=new sockets();
	
	$tbl=explode("\n",$sock->GET_INFO("SmtpNotificationConfigCC"));
	$tbl[]=$_GET["SMTP_NOTIFICATIONS_ADD_CC"];
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$cc[$ligne]=$ligne;
	}
	
	while (list ($num, $ligne) = each ($cc) ){
		$cc_final[]=$num;
	}	
	
	$sock->SaveConfigFile(implode("\n",$cc_final),"SmtpNotificationConfigCC");
	
	
}

function SMTP_NOTIFICATIONS_DEL_CC(){
	$sock=new sockets();
	$tbl=explode("\n",$sock->GET_INFO("SmtpNotificationConfigCC"));
	unset($tbl[$_GET["SMTP_NOTIFICATIONS_DEL_CC"]]);
	if(!is_array($tbl)){
		$final=null;
	}else{
		$final=implode("\n",$tbl);
	}
	
	$sock->SaveConfigFile(implode("\n",$cc_final),"SmtpNotificationConfigCC");
}

function SMTP_NOTIFICATIONS_CCLIST(){
	$sock=new sockets();
	$tbl=explode("\n",$sock->GET_INFO("SmtpNotificationConfigCC"));
	if(!is_array($tbl)){return null;}
	
	$html="<table style='width:99%'>";
while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		$html=$html . "
		<tr ". CellRollOver().">
			<td width=1%>". imgtootltip('ed_delete.gif',"{delete}","SMTP_NOTIFICATIONS_DEL_CC($num)")."</td>
			<td><code style='font-size:10px'>$ligne</code></td>
		</tr>
		";
	}	
	
	
	$html=$html . "</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
	
	
}

function SMTP_NOTIFICATIONS_SWITCH(){
	echo SMTP_NOTIFICATIONS();
}



function SMTP_NOTIFICATIONS(){
	
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();
	$sock=new sockets();
	$ini->loadString($sock->getFrameWork("cmd.php?SmtpNotificationConfigRead=yes"));
	$EnableMONITSmtpNotif=$sock->GET_INFO("EnableMONITSmtpNotif");
	$jGrowlNotifsDisabled=$sock->GET_INFO("jGrowlNotifsDisabled");
	
	if(!is_numeric($jGrowlNotifsDisabled)){$jGrowlNotifsDisabled=1;}
	if($EnableMONITSmtpNotif==null){$EnableMONITSmtpNotif=1;}
	
	if($ini->_params["SMTP"]["smtp_server_port"]==null){$ini->_params["SMTP"]["smtp_server_port"]=25;}
	if($ini->_params["SMTP"]["smtp_sender"]==null){
		$users=new usersMenus();
		$ini->_params["SMTP"]["smtp_sender"]="artica@$users->fqdn";
		}
		
if($ini->_params["SMTP"]["PostfixQueueEnabled"]==null){$ini->_params["SMTP"]["PostfixQueueEnabled"]=1;}
if($ini->_params["SMTP"]["PostfixQueueMaxMails"]==null){$ini->_params["SMTP"]["PostfixQueueMaxMails"]=20;}

if($ini->_params["SMTP"]["SystemCPUAlarm"]==null){$ini->_params["SMTP"]["SystemCPUAlarm"]=1;}
if($ini->_params["SMTP"]["SystemCPUAlarmPourc"]==null){$ini->_params["SMTP"]["SystemCPUAlarmPourc"]=95;}
if($ini->_params["SMTP"]["SystemCPUAlarmMin"]==null){$ini->_params["SMTP"]["SystemCPUAlarmMin"]=5;}	
if($ini->_params["SMTP"]["monit"]==null){$ini->_params["SMTP"]["monit"]=1;}
		
	if(!$users->msmtp_installed){
		$warn=Paragraphe("64-infos.png","{APP_MSMTP}","{APP_MSMTP_NOT_INSTALLED}",
		"javascript:Loadjs('setup.index.progress.php?product=APP_MSMTP&start-install=yes');");
	}
	

	$SystemCPUAlarmMin_arr=array(5=>5,10=>10,15=>15,30=>30,60=>60,120=>120,180=>180,240=>240);
	$SystemCPUAlarmMin=Field_array_Hash($SystemCPUAlarmMin_arr,'SystemCPUAlarmMin',$ini->_params["SMTP"]["SystemCPUAlarmMin"]);
	
	$member_add=Paragraphe("member-add-64.png","{add_recipient}","{add_recipient_text}",
		"javascript:SMTP_NOTIFICATIONS_ADD_CC();");
	
	$notifcclist=SMTP_NOTIFICATIONS_CCLIST();
		
		//Switchdiv
	
$notif1="
	<div class=explain>{smtp_notifications_text}</div>
	<div id='notif1'>
	<table style='width:99%'>
	<tr>
	<td valign='top'>$warn$member_add<hr><div id='notifcclist' style='width:100%;height:110px;overflow:auto'>$notifcclist</div></td>
	<td valign='top'>
	
	<table style='width:99%'>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{jGrowlNotifsDisabled}:</strong></td>
		<td>" . Field_checkbox("jGrowlNotifsDisabled",1,$jGrowlNotifsDisabled,"")."</td>
	</tr>	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_enabled}:</strong></td>
		<td>" . Field_checkbox("enabled",1,$ini->_params["SMTP"]["enabled"],"SMTPNotifArticaEnableSwitch()")."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{EnableMONITSmtpNotif}:</strong></td>
		<td>" . Field_checkbox("EnableMONITSmtpNotif",1,$EnableMONITSmtpNotif,"")."</td>
	</tr>

	
	
	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_server_name}:</strong></td>
		<td>" . Field_text('smtp_server_name',trim($ini->_params["SMTP"]["smtp_server_name"]),'font-size:13px;padding:3px;width:150px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_server_port}:</strong></td>
		<td>" . Field_text('smtp_server_port',trim($ini->_params["SMTP"]["smtp_server_port"]),'font-size:13px;padding:3px;width:30px')."</td>
	</tr>	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_sender}:</strong></td>
		<td>" . Field_text('smtp_sender',trim($ini->_params["SMTP"]["smtp_sender"]),'font-size:13px;padding:3px;width:150px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_dest}:</strong></td>
		<td>" . Field_text('smtp_dest',trim($ini->_params["SMTP"]["smtp_dest"]),'font-size:13px;padding:3px;width:150px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_auth_user}:</strong></td>
		<td>" . Field_text('smtp_auth_user',trim($ini->_params["SMTP"]["smtp_auth_user"]),'font-size:13px;padding:3px;width:150px')."</td>
	</tr>	
	<tr>
		<td nowrap class=legend style='font-size:13px'>{smtp_auth_passwd}:</strong></td>
		<td>" . Field_password('smtp_auth_passwd',trim($ini->_params["SMTP"]["smtp_auth_passwd"]),'font-size:13px;padding:3px;width:100px')."</td>
	</tr>
	<tr>
		<td nowrap class=legend style='font-size:13px'>{tls_enabled}:</strong></td>
		<td>" . Field_checkbox("tls_enabled",1,$ini->_params["SMTP"]["tls_enabled"])."</td>
	</tr>	

	
	
	<tr>
		<td align='left'>
			". button("{test}","testnotifs()")."
			
		</td>
		<td align='right'>".button('{edit}',"javascript:SaveArticaSMTPNotifValues();")."</td>

	</tr>
</table>



</td>


</tr>
</table>
</div>
<script>
var x_SaveArticaSMTPNotifValues= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshTab('main_config_notifs');
	
	}

	function SaveArticaSMTPNotifValues(){
		var XHR = new XHRConnection();
		if(document.getElementById('enabled').checked){XHR.appendData('enabled',1);}else {XHR.appendData('enabled',0);}
		if(document.getElementById('jGrowlNotifsDisabled').checked){XHR.appendData('jGrowlNotifsDisabled',1);}else {XHR.appendData('jGrowlNotifsDisabled',0);}
		
		
		
		if(document.getElementById('tls_enabled').checked){XHR.appendData('tls_enabled',1);}else {XHR.appendData('tls_enabled',0);}
		if(document.getElementById('EnableMONITSmtpNotif').checked){XHR.appendData('EnableMONITSmtpNotif',1);}else {XHR.appendData('EnableMONITSmtpNotif',0);}
		XHR.appendData('smtp_server_name',document.getElementById('smtp_server_name').value);
		XHR.appendData('smtp_server_port',document.getElementById('smtp_server_port').value);
		XHR.appendData('smtp_sender',document.getElementById('smtp_sender').value);
		XHR.appendData('smtp_dest',document.getElementById('smtp_dest').value);
		XHR.appendData('smtp_auth_user',document.getElementById('smtp_auth_user').value);
		XHR.appendData('smtp_auth_passwd',document.getElementById('smtp_auth_passwd').value);
		XHR.appendData('smtp_notifications','yes');
		document.getElementById('notif1').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SaveArticaSMTPNotifValues);
	}
	
	function SMTPNotifArticaEnableSwitch(){
		document.getElementById('smtp_auth_passwd').disabled=true;
		document.getElementById('EnableMONITSmtpNotif').disabled=true;
		document.getElementById('smtp_auth_user').disabled=true;
		document.getElementById('smtp_dest').disabled=true;
		document.getElementById('smtp_sender').disabled=true;
		document.getElementById('smtp_server_port').disabled=true;
		document.getElementById('smtp_server_name').disabled=true;
		document.getElementById('tls_enabled').disabled=true;
		if(document.getElementById('enabled').checked){
			document.getElementById('EnableMONITSmtpNotif').disabled=false;
			document.getElementById('smtp_auth_passwd').disabled=false;
			document.getElementById('smtp_auth_user').disabled=false;
			document.getElementById('smtp_dest').disabled=false;
			document.getElementById('smtp_sender').disabled=false;
			document.getElementById('smtp_server_port').disabled=false;
			document.getElementById('smtp_server_name').disabled=false;
			document.getElementById('tls_enabled').disabled=false;		
		}
	}
	SMTPNotifArticaEnableSwitch();
</script>
";
$notif2="
<div id='notif2'>
<br>
<div class=explain>{notification_context}</div>
<table style='width:100%'>
<tr>
	<td valign='top' class=legend>{system}:</td>
	<td valign='top'>" . Field_checkbox('system',1,$ini->_params["SMTP"]["system"])."</td>
</tr>
<tr>
	<td valign='top' class=legend>{security}:</td>
	<td valign='top'>" . Field_checkbox('security',1,$ini->_params["SMTP"]["security"])."</td>
</tr>

<tr>
	<td valign='top' class=legend>{sa-learn}:</td>
	<td valign='top'>" . Field_checkbox('sa-learn',1,$ini->_params["SMTP"]["sa-learn"])."</td>
</tr>

<tr>
	<td valign='top' class=legend>{logs_cleaning}:</td>
	<td valign='top'>" . Field_checkbox('logs_cleaning',1,$ini->_params["SMTP"]["logs_cleaning"])."</td>
</tr>
<tr>
	<td valign='top' class=legend>{update}:</td>
	<td valign='top'>" . Field_checkbox('update',1,$ini->_params["SMTP"]["update"])."</td>
</tr>
<tr>
	<td valign='top' class=legend>{KASPERSKY_UPDATES}:</td>
	<td valign='top'>" . Field_checkbox('KASPERSKY_UPDATES',1,$ini->_params["SMTP"]["KASPERSKY_UPDATES"])."</td>
</tr>

<tr>
	<td valign='top' class=legend>{backup}:</td>
	<td valign='top'>" . Field_checkbox('backup',1,$ini->_params["SMTP"]["backup"])."</td>
</tr>
<tr>
	<td valign='top' class=legend>{mailbox}:</td>
	<td valign='top'>" . Field_checkbox('mailbox',1,$ini->_params["SMTP"]["mailbox"])."</td>
</tr>
<tr>
	<td valign='top' class=legend>postfix:</td>
	<td valign='top'>" . Field_checkbox('postfix',1,$ini->_params["SMTP"]["postfix"])."</td>
</tr>
<tr>
	<td valign='top' class=legend>VIPTrack:</td>
	<td valign='top'>" . Field_checkbox('VIPTrack',1,$ini->_params["SMTP"]["VIPTrack"])."</td>
</tr>

<tr>
		<td nowrap class=legend>{APP_MONIT}:</strong></td>
		<td>" . Field_checkbox('monit',1,$ini->_params["SMTP"]["monit"])."</td>
	</tr>
<tr>
	<td valign='top' colspan=2 align='right'><hr>".button('{edit}',"SaveNotificationsContext()")."</td>
</tr>
</table>
<br><div style='font-size:14px;font-weight:bold;border-bottom:1px solid #005447'>{CPU_ALARM}</div>
<table style='width:100%'>
<tr>
	<td valign='top' class=legend>{enable}:</td>
	<td valign='top'>" . Field_checkbox('SystemCPUAlarm',1,$ini->_params["SMTP"]["SystemCPUAlarm"],"NotifSwitch()")."</td>
</tr>
<tr>
	<td valign='top' class=legend>{SystemCPUAlarmPourc}:</td>
	<td valign='top'>" . Field_text('SystemCPUAlarmPourc',$ini->_params["SMTP"]["SystemCPUAlarmPourc"],'width:40px')."&nbsp;%</td>
</tr>
<tr>
	<td valign='top' class=legend>{during}:</td>
	<td valign='top'>$SystemCPUAlarmMin&nbsp;mn</td>
</tr>
<tr>
	<td valign='top' colspan=2 align='right'><hr>".button('{edit}',"SaveNotificationsContext();")."</td>
</tr>
</table>
</div>
<script>
      
	function NotifSwitch(){
		var enabled='{$ini->_params["SMTP"]["enabled"]}';
		DisableFieldsFromId('notif2');						
		if(enabled!=='1'){return;}
		EnableFieldsFromId('notif2');
		
		
		document.getElementById('SystemCPUAlarmPourc').disabled=true;
		document.getElementById('SystemCPUAlarmMin').disabled=true;
		if(document.getElementById('SystemCPUAlarm').checked){
			document.getElementById('SystemCPUAlarmPourc').disabled=false;
			document.getElementById('SystemCPUAlarmMin').disabled=false;
		}	
	
	}
	
	var x_SaveNotificationsContext= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_notifs');
	
	}	
	
	function SaveNotificationsContext(){
		var XHR=XHRParseElements('notif2');
		XHR.appendData('smtp_notifications','yes');
		document.getElementById('notif2').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SaveNotificationsContext);
	}
	
	NotifSwitch();
</script>


";
$notif3="
<div id='notif3'>
<br>
<div class=explain>{APP_POSTFIX} {notifications}</div>
<table style='width:100%'>
<tr>
	<td valign='top' class=legend>{PostfixQueueEnabled}:</td>
	<td valign='top'>" . Field_checkbox('PostfixQueueEnabled',1,$ini->_params["SMTP"]["PostfixQueueEnabled"],'NotifSwitch()')."</td>
</tr>
<tr>
	<td valign='top' class=legend>{PostfixQueueMaxMails}:</td>
	<td valign='top'>" . Field_text('PostfixQueueMaxMails',$ini->_params["SMTP"]["PostfixQueueMaxMails"],'width:60px')."</td>
</tr>
<tr>
	<td valign='top' colspan=2 align='right'>".button('{edit}',"javascript:SavePostFixNotifications()")."</td>
</tr>
</table>
</div>
<script>
	var x_SavePostFixNotifications= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_notifs');
	
	}

	function SavePostFixNotifications(){
		var XHR = new XHRConnection();
		if(document.getElementById('PostfixQueueEnabled').checked){XHR.appendData('PostfixQueueEnabled',1);}else {XHR.appendData('PostfixQueueEnabled',0);}
		XHR.appendData('PostfixQueueMaxMails',document.getElementById('PostfixQueueMaxMails').value);
		XHR.appendData('smtp_notifications','yes');
		document.getElementById('notif3').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SavePostFixNotifications);
	}
	
	function NotifSwitch(){
		var enabled='{$ini->_params["SMTP"]["enabled"]}';
		document.getElementById('PostfixQueueEnabled').disabled=true;
		document.getElementById('PostfixQueueMaxMails').disabled=true;
		if(enabled!=='1'){return;}
		document.getElementById('PostfixQueueEnabled').disabled=false;
		if(document.getElementById('PostfixQueueEnabled').checked){
			document.getElementById('PostfixQueueMaxMails').disabled=false;
		}
		
	
	}
	
	NotifSwitch();
</script>

";

switch ($_GET["smtp-notifs-tab"]) {
	case "notif1":$HTML=$notif1;break;
	case "notif2":$HTML=$notif2;break;
	case "notif3":$HTML=$notif3;break;
	default:$HTML=$notif1;
	break;
}


$tpl=new templates();
return $tpl->_ENGINE_parse_body($HTML);
	
}


function SMTP_NOTIFICATIONS_NOTIF(){
	$sock=new sockets();
	
	
	
	$datas=$sock->getFrameWork("cmd.php?testnotif=yes");
	//echo $datas;
	$datas=explode("\n",$datas);
	$datas=array_reverse($datas,true);
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		if(preg_match("#error while loading shared libraries:\s+(.+?)\s+#",$ligne,$re)){
			$success="<div style='margin:5px;text-align:center;font-size:14px;font-weight:bold;color:red;border:1px solid red;padding:4px;margin:5px;background-color:white'>
				LIBRARY {$re[1]} ERROR, please <a href='#' OnClick=\"javascript:Loadjs('setup.index.progress.php?product=APP_MSMTP&start-install=yes')\">
				reinstall</a><br>
				<hr style='border:1px'>
					<code>$ligne</code>
				<hr style='border:1px'>
			</div><br>";
			continue;
		}
		if(preg_match('#No such file or directory#',$ligne)){continue;}
		if(preg_match('#EX_OK#',$ligne)){
			$success="<div style='margin:5px;background-color:white;text-align:center;font-size:14px;font-weight:bold;color:red;border:1px solid red;padding:4px;margin:5px'>{success}</div><br>";
			continue;
		}
		
		if(preg_match('#cannot use a secure authentication method#',$ligne,$re)){
		$success="<div style='margin:5px;background-color:white;text-align:center;font-size:14px;font-weight:bold;color:red;border:1px solid red;padding:4px;margin:5px'>
			{failed}<br>$ligne</div><br>";
			continue;
			
		}
		if(preg_match('#errormsg=(.+?)\s+exitcode=EX_UNAVAILABLE#',$ligne,$re)){
			$success="<div style='margin:5px;background-color:white;text-align:center;font-size:14px;font-weight:bold;color:red;border:1px solid red;padding:4px;margin:5px'>
			{failed}<br>{$re[1]}</div><br>";
			continue;
			
		}
				
		
		
		
		
		$html=$html. "<div style='border-bottom:1px solid #CCCCCC'><code>" . htmlspecialchars($ligne)."</code></div>";
		
	}
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($success.$html);	
}



function SMTP_NOTIFICATIONS_SAVE(){
	$sock=new sockets();
	if(isset($_GET["EnableMONITSmtpNotif"])){$sock->SET_INFO("EnableMONITSmtpNotif",$_GET["EnableMONITSmtpNotif"]);}
	if(isset($_GET["jGrowlNotifsDisabled"])){$sock->SET_INFO("jGrowlNotifsDisabled",$_GET["jGrowlNotifsDisabled"]);}
	
	
	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("SmtpNotificationConfig"));
	
	
	while (list ($num, $ligne) = each ($_GET) ){
		$ini->_params["SMTP"][$num]=$ligne;
	}
	
	
	$sock->SaveConfigFile($ini->toString(),"SmtpNotificationConfig");
	$sock->getFrameWork("cmd.php?SmtpNotificationConfig=yes");
	$sock->getFrameWork("cmd.php?RestartDaemon=yes");
	$sock->getFrameWork("cmd.php?monit-restart=yes");
	$sock->getFrameWork("cmd.php?notifier-restart=yes");
	
	
	
	
}


function HTTPS_PROCESSES_SAVE(){
	$l=new httpd();
	$l->lighttp_max_load_per_proc=$_GET["lighttp_max_load_per_proc"];
	$l->lighttp_max_proc=$_GET["lighttp_max_proc"];
	$l->lighttp_min_proc=$_GET["lighttp_min_proc"];
	$l->PHP_FCGI_CHILDREN=$_GET["PHP_FCGI_CHILDREN"];
	$l->PHP_FCGI_MAX_REQUESTS=$_GET["PHP_FCGI_MAX_REQUESTS"];
	$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("\n{interface_restarted}");
	$l->SaveToServer();
	}



function SMTP_PERFORMANCES_EXPLAIN($index){
	$html=RoundedLightGrey('{modules_'.$index.'}');
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	}
	
function SMTP_PERFORMANCES_SAVE(){
	$ldap=new clladp();
	$dn="cn=artica,$ldap->suffix";
	$up["ArticaMailAddonsLevel"][0]=$_GET["ArticaMailAddonsLevel_save"];
	$ldap->Ldap_modify($dn,$up);
}


function SMTP_NOTIFICATIONS_PAGE(){

		$notifs="
	
	<H5>{smtp_notifications}</H5>
	<p class=caption>{smtp_notifications_text}</p>
	<div style='text-align:right'>
		<input type='button' OnClick=\"javascript:Loadjs('artica.settings.php?ajax-notif=yes')\" value='{smtp_notifications}'>
	</div>
	";
		
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($notifs);
	
}

function page_tab_index(){
	$warn_dnsmasq=warn_dnsmasq();
	$smtp_performances=SMTP_PERFORMANCES();
	$https_port=HTTPS_PORT();
	$notifs=SMTP_NOTIFICATIONS_PAGE();
	$notifs=RoundedLightGrey($notifs);
	$ldap=LDAP_SETTINGS();
	
	$html=tabs()."<br>
		<table style='width:100%'>
		<tr>
			<td width=50%' valign='top'>$https_port$ldap</td>
			<td width=50%' valign='top'>$notifs<br>$warn_dnsmasq</td>
		</tr>
		</table>";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function page_tab_account(){
	$html=tabs()."<br>
		<table style='width:100%'>
		<tr>
			<td width=50%' valign='top'>" . GlobalAdmin(). "</td>
			<td width=50%' valign='top'>" . CyrusUser()."</td>
		</tr>
		</table>";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function page_tab_path(){
		$html=tabs()."<br>
		<table style='width:100%'>
		<tr>
			<td width=50%' valign='top'>". WebRoot(). "<br>".MaxLogsFiles()."</td>
			<td width=50%' valign='top'>" . queuePath()."</td>
		</tr>
		</table>";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function MaxLogsFiles(){
$users=new usersMenus();
$ldap=new clladp();
$artica=new artica_general();
$maxday=$artica->MaxTempLogFilesDay;
	
$ini=new Bs_IniHandler();
$ini->loadString($artica->ArticaPerformancesSettings);	
if($ini->_params["PERFORMANCES"]["ArticaMaxLogsSize"]==null){$ini->_params["PERFORMANCES"]["ArticaMaxLogsSize"]=500;}
//
$form="<table style='width:90%;margin:5px;padding:5px'>
		<tr>
				<td align='left' nowrap class=legend width=1%>{ArticaMaxTempLogFilesDay}:</td>
				<td nowrap>" . Field_text('ArticaMaxTempLogFilesDay',$maxday,'width:40px')." {days}</td>
				<td width=1%>" . help_icon('{ArticaMaxTempLogFilesDay_text}')."</td>
		</tr>
	
				
		<tr>
			<td nowrap class=legend>{ArticaMaxLogsSize}:</td>
			<td nowrap>" . Field_text('ArticaMaxLogsSize',$ini->_params["PERFORMANCES"]["ArticaMaxLogsSize"],'width:45px')." Mb</td>
			<td width=1%>" . help_icon('{ArticaMaxLogsSize_text}')."</td>
		</tr>			
		
		<tr>
			<td align='right' colspan=3>
				<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ArticaWebRootURI();\">
			</td>
		</tr>
		</table>";	
return RoundedLightWhite($form);	
}


function WebRoot(){
$users=new usersMenus();
$ldap=new clladp();
$hash=$ldap->ArticaDatas();
$ArticaWebRootURI=$hash["ArticaWebRootURI"];

$ArticaWebRootURI=str_replace("0.0.0.0",$users->hostname,$ArticaWebRootURI);

$webroot="<table style='width:90%;margin:5px;padding:5px'>
			<tr>
				<td align='left' nowrap class=legend>{ArticaWebRootURI}</strong>:</td>
			</tr>
			<tr>
				<td>" . Field_text('ArticaWebRootURI',$ArticaWebRootURI,'width:290px')."</td>
			</tr>
			<tr>	
			<td ><span class=caption>{ArticaWebRootURI_text}</span></td>
		</tr>			
		<tr>
		<tr>
			<td align='right'>
				<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ArticaWebRootURI();\">
			</td>
		</tr>
		</table>";




return RoundedLightGrey($webroot);	
	
}


function queuePath(){
	
$users=new usersMenus();
$ldap=new clladp();
$hash=$ldap->ArticaDatas();

$ArticaMaxSubQueueNumber=$users->ARTICA_FILTER_MAXSUBQUEUE;
$content_filter_queue_path=$users->ARTICA_FILTER_QUEUE_PATH;


	
$forms="<form name='FFM1'>
<input type='hidden' name='ARTICA_FILTER_MAXSUBQUEUE' id='ARTICA_FILTER_MAXSUBQUEUE' value='$ArticaMaxSubQueueNumber'>
	<center>
		<table style='width:90%;margin:5px;padding:5px'>
			<tr>
				<td align='left' nowrap class=legend>{ARTICA_FILTER_QUEUE_PATH}</strong>:</td>
			</tr>
			<tr>
				<td>" . Field_text('ARTICA_FILTER_QUEUE_PATH',$content_filter_queue_path)."</td>
			</tr>
		<tr>
			
			<td ><span class=caption>{ARTICA_FILTER_QUEUE_PATH_TEXT}</span></td>
		</tr>
	
		<tr><td align='right'><input type='submit' value='{edit}&nbsp;&raquo;'></td></tr>

		</table>
		</center>
		</form>";

return RoundedLightGrey($forms);	
	
}

function GroupBehavior(){
$page=CurrentPageName();	
$artica=new artica_general();
$enablegroup=Field_yesno_checkbox_img('enableGroups',$artica->EnableGroups,'{enable_disable}');	
	
$html="<div style='margin:5px;padding:5px'>
<h3>{group_behavior}</h3>
{group_behavior_text2}
<p>&nbsp;</p>
<form name='ffm1'>
<input type='hidden' name='SaveGroupBehavior' value='1'>
<table style='width:40%'>
<tr>
<td><strong>{group_behavior}:</td><td width=1%>$enablegroup</td>
<td><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"> </td>
</tr>
</table>
</form>
</div>";
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
}
function SaveGroupBehavior(){
	$artica=new artica_general();
	$tpl=new templates();
	$artica->EnableGroups=$_GET["enableGroups"];
	if($artica->Save()==true){echo $tpl->_ENGINE_parse_body('{success}');}
}
function SaveRelayBehavior(){
	$artica=new artica_general();
	$tpl=new templates();
	$artica->RelayType=$_GET["relay"];	
	if($artica->Save()==true){echo $tpl->_ENGINE_parse_body('{success}');}
}

function RelayBehavior(){
$page=CurrentPageName();	
$artica=new artica_general();
$hash_relay=array("mail"=>"mail","relay"=>"relay","single"=>"Hub");
$field_relay=Field_array_Hash($hash_relay,'relay',$artica->RelayType,null,null,0,'width:100%;');
$html="<div style='margin:5px;padding:5px'>
<h3>{relay_behavior}</h3>
{relay_behavior_text2}
<p>&nbsp;</p>
<form name='ffm1'>
<input type='hidden' name='SaveRelayBehavior' value='1'>
<table style='width:60%'>
<tr>
<td><strong>{relay_behavior}:</td><td>$field_relay</td>
<td><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"> </td>
</tr>
</table>
</form>
</div>";
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);	
	
}

function DatabaseFileSize(){
		if(is_file('LocalDatabases/artica_database.db')){
		$filesize=filesize('LocalDatabases/artica_database.db');
		$size=round($filesize/1024) . "&nbsp;kb";
		}
		
if(is_file('LocalDatabases/rbl_database.db')){
		$filesizeRBL=filesize('LocalDatabases/rbl_database.db');
		$filesizeRBL=round($filesizeRBL/1024) . "&nbsp;kb";
		}		
	
	$html="<H5>{local_database_size}</H5>
	<table style='width:100%'>
<tr>
<td width=1%><img src='img/icon_mini_info.gif'></td>
<td nowrap nowrap class=legend>{email_database}&nbsp;{size}:</td>
</tr>
<tr>
<td colspan=2 nowrap class=legend>$size</string></td>
</tr>
<tr>
<td width=1%><img src='img/icon_mini_info.gif'></td>
<td nowrap nowrap class=legend>{rbl_cache_database}&nbsp;{size}:</td>
</tr>
<tr>
<td colspan=2 nowrap class=legend>$filesizeRBL</string></td>
</tr>
</table>";
	return "<br>" .RoundedLightGrey($html) . "<br>";
	
}


function DaemonsStatus(){
	$sock=new sockets();
	$datas=$sock->getfile('ARTICA_ALL_STATUS');
	$tpl=explode("\n",$datas);
	if(!is_array($tpl)){return null;}
	$usermenus=new usersMenus();
	
	$icon_mysql="img/icon_mini_read.gif";
	
	$html="
	<H4>{status}</h4>
	<table style='margin:3px;;width:100%'>
			<tr>
		<td width=1% ><img src='img/icon_mini_info.gif'></td>
		<td ><strong>{artica_version}</strong></td>
		<td ><strong>$usermenus->ARTICA_VERSION</strong></td>
		</tr><tr><td colspan=3>&nbsp;</td></tr>";
	
	while (list ($num, $ligne) = each ($tpl) ){
		if($ligne<>null){
		$table=explode(';',$ligne);
		$name=$table[0];
		$status=$table[1];
		$memory=$table[2];
		if($status==0){$icon="img/icon_mini_off.jpg";}else{$icon="img/icon_mini_read.gif";}
		$html=$html . "
		<tr>
		<td width=1%><img src='$icon'></td>
		<td  style='padding:4px'><strong>$name</strong></td>
		<td  style='padding:4px'><strong>$memory mb</strong></td>
		</tr>
		
		";}
		
		
	}
	
	
	
	$html =RoundedLightGreen($html."</table>");
	
	
	$tpl=new templates();
	echo iframe($tpl->_ENGINE_parse_body($html . "<br>" . DatabaseFileSize()),10,'230px');
	
}

function SaveSettings(){
	
	$sock=new sockets();
	$sock->getfile('SET_ARTICA_FILTER:QueuePath=' . $_GET["ARTICA_FILTER_QUEUE_PATH"]);
	$sock->getfile('SET_ARTICA_FILTER:MAX_QUEUE_NUMBER=' . $_GET["ARTICA_FILTER_MAXSUBQUEUE"]);
	}
	
function SaveProxySettings(){
	$artica=new artica_general();
	while (list ($num, $ligne) = each ($_GET) ){
		$artica->$num=$ligne;
	}
	$artica->SaveProxySettings();
	
}

function SaveArticaWebRootURI(){
	$ArticaWebRootURI=$_GET["ArticaWebRootURI"];
	if(!is_numeric($_GET["ArticaMaxTempLogFilesDay"])){$_GET["ArticaMaxTempLogFilesDay"]=3;}
	
	$artica=new artica_general();
	
	$ini=new Bs_IniHandler();
	$ini->loadString($artica->ArticaPerformancesSettings);
	
	
	
	$ini->_params["PERFORMANCES"]["ArticaMaxLogsSize"]=$_GET["ArticaMaxLogsSize"];
	
	writelogs("Save PERFORMANCES/ArticaMaxLogsSize={$ini->_params["PERFORMANCES"]["ArticaMaxLogsSize"]}",__FUNCTION__,__FILE__);
	
	$artica->ArticaPerformancesSettings=$ini->toString();
	
	$artica->MaxTempLogFilesDay=$_GET["ArticaMaxTempLogFilesDay"];
	$artica->Save();
	
	
	$ldap=new clladp();
	$upd["ArticaWebRootURI"][0]=$ArticaWebRootURI;
	if(!$ldap->Ldap_modify("cn=artica,$ldap->suffix",$upd)){
		echo $ldap->ldap_last_error;}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('Logs:{success}');
		}
	


}

function CyrusUser(){
	$ldap=new clladp();
	$dn="cn=cyrus,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){$im='danger24.png';};
	$res=@ldap_read($ldap->ldap_connection,$dn,"(objectClass=*)",array());
		if($res){$hash=ldap_get_entries($ldap->ldap_connection,$res);
		$userpassword=$hash[0]["userpassword"][0];
	
}
if($userpassword<>null){$im='ok24.png';}else{$im='danger24.png';}
$html="<H5>Cyrus account</h5>
<table><tr><td width=1%><img src='img/$im'></td><td><strong>cyrus:$userpassword</strong></td></tr></table>";
return RoundedLightGreen($html);

}

function tabs(){
	$tpl=new templates();
	$user=new usersMenus();
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{infos}';
	
	if($_SESSION["uid"]=='-100'){
		$array[]='{account_settings}';
	}
	$array[]='{global_paths}';
	$array[]='{http_proxy}';
	$array[]='{sql_database}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		if(strlen($ligne)>20){$ligne=texttooltip(substr($ligne,0,17)."...",$ligne,null,null,1);}
		$html=$html . "<li><a href=\"javascript:LoadAjax('middle_area','$page?section=yes&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function warn_dnsmasq(){
	$users=new usersMenus();

if($users->dnsmasq_installed==false){
	$warn_dnsmasq="
	
	<table style='width:90%;margin:5px;padding:5px'>
		<tr>
			
			<td align='left'>
			<img src='img/idee-64.png' align='left' style='margin:4px'><H5>{UseDNSMasq}</H5>
			<div class=caption colspan=2>{UseDNSMasq_text}</div>
			</td>
		</tr>
		</table>";
		
	return RoundedLightGreen($warn_dnsmasq);
	
}	
	
}


function GlobalAdmin(){
	
	if($_SESSION["uid"]<>'-100'){return null;}
	$page=CurrentPageName();
	$users=new usersMenus();
	$openldap=1;
	$sock=new sockets();
	$ArticaMetaDisableMasterAccount=$sock->GET_INFO("ArticaMetaDisableMasterAccount");
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$ldap=new clladp();
	
	
	
	$global_admin_confirm=$tpl->javascript_parse_text("{global_admin_confirm}");
	
	$adv_options="<a href='#' OnClick=\"javascript:Loadjs('artica.settings.php?js-ldap-interface=yes');\" style='font-size:13px'>{advanced_options}</a>";
	if(!$users->openldap_installed){$openldap=0;$adv_options=null;}
	if($ArticaMetaDisableMasterAccount==1){$ldap->ldap_password=null;$adv_options=null;}
	if($userpassword<>null){$im='ok24.png';}else{$im='danger24.png';}
$html="
<div id='ChangePasswordDivNOtifiy'>
<table>
	<tr>
		<td width=1% valign='top'><img src='img/superuser-64.png'>
	</td>
	<td>
		<table style='width:100%'>
		<tr>
		<td align='right' class=legend nowrap style='font-size:13px'>{username}:</strong></td>
		<td align='left'>" . Field_text("change_admin",$ldap->ldap_admin,'width:150px;font-size:13px;padding:3px',
		"script:ChangeGlobalAdminPasswordCheck(event)")."</td>
		</tr>
		<tr>
		<td align='right' class=legend nowrap class=legend>{password}:</strong></td>
		<td align='left'>" . Field_password("change_password",$ldap->ldap_password,"width:100px;font-size:13px;padding:3px",
		"script:ChangeGlobalAdminPasswordCheck(event)")."</td>
		</tr>
	
		
		
		<tr>
			<td colspan=2 style='border-bottom:1px solid #CCCCCC;padding-top:4px'><strong style='font-size:14px;'>{ldap_parameters}</strong></td>
		</tr>
		<tr>
			<td colspan=2>&nbsp;</td>
		</tr>
		<tr>
		<td align='right' class=legend nowrap style='font-size:13px'>{ldap_suffix}:</strong></td>
		<td align='left'>" . Field_text("ldap_suffix",$ldap->suffix,"font-size:13px;padding:3px",
		"script:ChangeGlobalAdminPasswordCheck(event)")."</td>
		</tr>	
		<tr>	
		<td align='right' class=legend nowrap style='font-size:13px'>{ldap_server}:</strong></td>
		<td align='left'>" . Field_text("ldap_server",$ldap->ldap_host,"font-size:13px;padding:3px",
		null,null,null,false,"DisableLDAP()")."</td>
		</tr>	
		<tr>	
		<td align='right' class=legend nowrap style='font-size:13px'>{ldap_port}:</strong></td>
		<td align='left'>" . Field_text("ldap_port",$ldap->ldap_port,"font-size:13px;padding:3px")."</td>
		</tr>
		<tr>	
		<td align='right' class=legend nowrap style='font-size:13px'>{change_ldap_server_settings}:</strong></td>
		<td align='left'>" . Field_checkbox('change_ldap_server_settings',1,0,null,'{change_ldap_server_settings_text}')."</td>
		<tr>
			<td colspan=2 align='right'>		
				$adv_options
			</td>
		</tr>								
		<tr>
			<td colspan=2 align='right'>
				<hr>". button("{apply}","ChangeGlobalAdminPassword()")."
			</td>
		</tr>
		</table>
	</td>
	</tr>
</table>
</div>
<script>


function DisableLDAP(){
	var opinst=$openldap;
	var ldap_server=document.getElementById('ldap_server').value;
	if(opinst==1){return;}
	if(ldap_server=='127.0.0.1'){
		document.getElementById('change_ldap_server_settings').disabled=true;
		document.getElementById('ldap_port').disabled=true;
		document.getElementById('ldap_suffix').disabled=true;
	}else{
		document.getElementById('change_ldap_server_settings').disabled=false;
		document.getElementById('ldap_port').disabled=false;
		document.getElementById('ldap_suffix').disabled=false;	
	}

}

var X_ChangeGlobalAdminPassword= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	MyHref(\"logoff.php\");
	}
	
function ChangeGlobalAdminPasswordCheck(e){
	if(checkEnter(e)){ChangeGlobalAdminPassword();}
}


function ChangeGlobalAdminPassword(){
	var ArticaMetaDisableMasterAccount='$ArticaMetaDisableMasterAccount';
	var password=document.getElementById('change_password').value;
	//var password=escape(document.getElementById('change_password').value);
	if(password.indexOf('+')>0){alert('+ character not allowed');return;}
	if(ArticaMetaDisableMasterAccount=='1'){
		alert('$ERROR_NO_PRIVS');
		return;
	}
	if(confirm('$global_admin_confirm')){
		var XHR = new XHRConnection();
		XHR.appendData('change_admin',document.getElementById('change_admin').value);
		XHR.appendData('change_password',password);
		
		
		XHR.appendData('suffix',document.getElementById('ldap_suffix').value);
		XHR.appendData('ldap_server',document.getElementById('ldap_server').value);
		XHR.appendData('ldap_port',document.getElementById('ldap_port').value);
		if(document.getElementById('change_ldap_server_settings').checked){
			XHR.appendData('change_ldap_server_settings','yes');
		}
		
		XHR.appendData('ChangeSuperSuser','yes');
		document.getElementById('ChangePasswordDivNOtifiy').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',X_ChangeGlobalAdminPassword);
		
		
	}
}

function ProtectAccount(){
	var ArticaMetaDisableMasterAccount='$ArticaMetaDisableMasterAccount';
	if(ArticaMetaDisableMasterAccount=='1'){
		document.getElementById('change_admin').disabled=true;
		document.getElementById('change_password').disabled=true;
		document.getElementById('ldap_suffix').disabled=true;
		document.getElementById('ldap_server').disabled=true;
		document.getElementById('ldap_port').disabled=true;
		document.getElementById('change_ldap_server_settings').disabled=true;
	
	}
}

DisableLDAP();
ProtectAccount();
</script>


";
return $html;	
	
	
	
}


function http_proxy(){
	$artica=new artica_general();
	if($artica->ArticaProxyServerEnabled==null){$artica->ArticaProxyServerEnabled="no";}
	$page=CurrentPageName();
$html="
<table>
	<tr>
		<td width=1%><img src='img/proxy-147.png'>
	</td>
	<td>
		<div id='js-proxy-interface-div'>
		<table style='width:100%'>
		<tr>
		<td align='right' class=legend nowrap style='font-size:13px'>{ArticaProxyServerEnabled}:</strong></td>
		<td align='left'>" . Field_checkbox('ArticaProxyServerEnabled','yes',$artica->ArticaProxyServerEnabled,"EnableDisableProxySetts()")."</td>
		</tr>			
		<tr>
		<td align='right' class=legend nowrap style='font-size:13px'>{ArticaProxyServerName}:</strong></td>
		<td align='left'>" . Field_text("ArticaProxyServerName",$artica->ArticaProxyServerName,'width:150px;font-size:13px;padding:3px')."</td>
		</tr>
		<tr>
		<td align='right' class=legend nowrap style='font-size:13px'>{ArticaProxyServerPort}:</strong></td>
		<td align='left'>" . Field_text("ArticaProxyServerPort",$artica->ArticaProxyServerPort,'width:60px;font-size:13px;padding:3px')."</td>
		</tr>
		<tr>
		<td align='right' class=legend nowrap style='font-size:13px'>{ArticaProxyServerUsername}:</strong></td>
		<td align='left'>" . Field_text("ArticaProxyServerUsername",$artica->ArticaProxyServerUsername,'width:150px;font-size:13px;padding:3px')."</td>
		</tr>	
		<tr>
		<td align='right' class=legend nowrap class=legend>{ArticaProxyServerUserPassword}:</strong></td>
		<td align='left'>" . Field_text("ArticaProxyServerUserPassword",$artica->ArticaProxyServerUserPassword,'width:150px;font-size:13px;padding:3px')."</td>
		</tr>				
		<tr>
			<td colspan=2 align='right'><hr>". button("{apply}","SaveJsProxyInterface()")."</td>
		</tr>
		</table>
		</div>
	</td>
	</tr>
</table>
<script>

function EnableDisableProxySetts(){
	document.getElementById('ArticaProxyServerName').disabled=true;
	document.getElementById('ArticaProxyServerPort').disabled=true;
	document.getElementById('ArticaProxyServerUsername').disabled=true;
	document.getElementById('ArticaProxyServerUserPassword').disabled=true;
	if(document.getElementById('ArticaProxyServerEnabled').checked){
		document.getElementById('ArticaProxyServerName').disabled=false;
		document.getElementById('ArticaProxyServerPort').disabled=false;
		document.getElementById('ArticaProxyServerUsername').disabled=false;
		document.getElementById('ArticaProxyServerUserPassword').disabled=false;	
	}
	
}

var X_SaveJsProxyInterface= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	document.getElementById('js-proxy-interface-div').innerHTML='';
	YahooWin2Hide();
	}

	function SaveJsProxyInterface(){
		var XHR = new XHRConnection();
		if(document.getElementById('ArticaProxyServerEnabled').checked){
			XHR.appendData('ArticaProxyServerEnabled','yes');
		}else{
			XHR.appendData('ArticaProxyServerEnabled','no');
		}
		
		XHR.appendData('ArticaProxyServerName',document.getElementById('ArticaProxyServerName').value);
		XHR.appendData('ArticaProxyServerPort',document.getElementById('ArticaProxyServerPort').value);
		XHR.appendData('ArticaProxyServerUsername',document.getElementById('ArticaProxyServerUsername').value);
		XHR.appendData('ArticaProxyServerUserPassword',document.getElementById('ArticaProxyServerUserPassword').value);
		document.getElementById('js-proxy-interface-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveJsProxyInterface);
	
	}
	EnableDisableProxySetts();
</script>


";
return $html;	
	
	
	
}

function ChangeUserPassword(){
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.httpd.inc');
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	
	if($_SESSION["uid"]<>'-100'){echo $ERROR_NO_PRIVS;return;}
	if($sock->GET_INFO("ArticaMetaDisableMasterAccount")==1){echo $ERROR_NO_PRIVS;return;}
	
	$users=new usersMenus();
	$username=trim($_POST["change_admin"]);
	$password=url_decode_special($_POST["change_password"]);
	

	
	$md5=md5($username.$password);
	$ldap=new clladp();
	$md52=md5(trim($ldap->ldap_admin).trim($ldap->ldap_password));
	$tpl=new templates();
	
	$ldap_server=$_POST["ldap_server"];
	$ldap_port=$_POST["ldap_port"];
	$suffix=$_POST["suffix"];
	$change_ldap_server_settings=$_POST["change_ldap_server_settings"];
	if($change_ldap_server_settings<>'yes'){$change_ldap_server_settings="no";}
	
	writelogs("change_password $password",__FUNCTION__,__FILE__,__LINE__ );
	$password=base64_encode($password);
	writelogs("change_password $password",__FUNCTION__,__FILE__,__LINE__ );
	
	if($ldap_server==null){$ldap_server="127.0.0.1";}
	if($ldap_port==null){$ldap_port="389";}
	if($suffix==null){$suffix="dc=nodomain";}
	$username=urlencode($username);
	$password=urlencode($password);
	$cmd="cmd.php?ChangeLDPSSET=yes&ldap_server=$ldap_server&ldap_port=$ldap_port&suffix=$suffix";
	$cmd=$cmd."&change_ldap_server_settings=$change_ldap_server_settings&username=$username&password=$password";
	$datas=$sock->getFrameWork("$cmd");
	echo replace_accents(html_entity_decode($tpl->_ENGINE_parse_body("{success}:$ldap_server:$ldap_port ($suffix)\n$username\n-------\"\"------")));

	
}

function SaveSqlSettings(){
	
	$ar=new artica_general();
	writelogs("Save _GET[MysqlMaxEventsLogs]='{$_GET["MysqlMaxEventsLogs"]}'",__FUNCTION__,__FILE__);
	$ar->EnableSyslogMysql=$_GET["EnableSyslogMysql"];
	$ar->MysqlMaxEventsLogs=$_GET["MysqlMaxEventsLogs"];
	$ar->MysqlAdminAccount="{$_GET["mysqlroot"]}:{$_GET["pwd"]}";
	$ar->SaveMysqlSettings();
	$sock=new sockets();
	$sock->getfile("synchronizeModules");
	$datas=$sock->getfile('changemysqlpassword');
	$tbl=explode("\n",$datas);
	echo "\n";
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne<>null)){
			echo "$ligne\n";
		}
	}
	
}
function HTTPS_PORT_SAVE(){
	$httpd=new httpd();
	$sock=new sockets();

	if(preg_match('#(.+?):(.+)#',$_GET["LighttpdUserAndGroup"])){
		$sock->SET_INFO("LighttpdUserAndGroup",$_GET["LighttpdUserAndGroup"]);
	}
	$sock->SET_INFO("ArticaHttpsPort",$_GET["https_port"]);
	$sock->SET_INFO("ArticaHttpUseSSL",$_GET["ArticaHttpUseSSL"]);
	$httpd->https_port=$_GET["https_port"];
	$httpd->LighttpdUseLdap=$_GET["LighttpdUseLdap"];
	$httpd->ApacheConfig=$_GET["ApacheArticaEnabled"];
	$httpd->ApacheArticaEnabled=$_GET["ApacheArticaEnabled"];
	
	
	$httpd->SaveToServer();
	
}

function main_status(){
	$usermenus=new usersMenus();
	$sql=new mysql();
	$q="SELECT count(md5) as tcount FROM sys_events";
	$ligne=@mysql_fetch_array($sql->QUERY_SQL($q,"artica_events"));
	if($ligne["tcount"]==null){$ligne["tcount"]=0;}
	$event_count=$ligne["tcount"];
	
	$html="
	<table style='width:100%'>
	<tr>
	<td align='right' class=legend nowrap class=legend>{artica_version}:</td>
	<td align='left'><strong>$usermenus->ARTICA_VERSION</td>
	</tr>	
	<tr>
	<td align='right' class=legend nowrap class=legend>{events_table_count}:</td>
	<td align='left'><strong>$event_count</td>
	</tr>
	</table>";
	
	$html=RoundedLightGrey($html);
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	echo $html;
	}
	
function LDAP_CONFIG_JS(){
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){echo "alert('no privileges');";exit;}
$tpl=new templates();
$ldap_title=$tpl->_ENGINE_parse_body('{APP_LDAP}');
$page=CurrentPageName();
$html="
	function LDAPInterFace(){
		YahooWin3(690,'$page?js-ldap-popup=yes','$ldap_title');
	}
	
	
var x_ParseFormLDAP= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				RefreshTab('main_config_ldap_adv');
			}		
	
	function ParseFormLDAP(){
		var LdapAllowAnonymous;
		var EnableRemoteAddressBook;
		if(document.getElementById('LdapAllowAnonymous').checked){LdapAllowAnonymous=1;}else{LdapAllowAnonymous=0;}
		if(document.getElementById('EnableRemoteAddressBook').checked){EnableRemoteAddressBook=1;}else{EnableRemoteAddressBook=0;}	
		var XHR = new XHRConnection();
		XHR.appendData('set_cachesize',document.getElementById('set_cachesize').value);
		XHR.appendData('cachesize',document.getElementById('cachesize').value);
		XHR.appendData('LdapListenIPAddr',document.getElementById('LdapListenIPAddr').value);
		XHR.appendData('LdapAllowAnonymous',LdapAllowAnonymous);
		XHR.appendData('EnableRemoteAddressBook',EnableRemoteAddressBook);
		document.getElementById('cachesizediv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ParseFormLDAP);
	
	}
	
	function ParseFormLDAPDB(){
		var XHR = new XHRConnection();
		XHR.appendData('set_cachesize',document.getElementById('set_cachesize').value);
		XHR.appendData('cachesize',document.getElementById('cachesize').value);
		document.getElementById('ParseFormLDAPNET').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ParseFormLDAP);
	}
	


	
	
	
	LDAPInterFace();
	";
	
	echo $html;
	
}

function LDAP_SAVE(){
	
	$tpl=new templates();
	
	
	
	$sock=new sockets();
	if(isset($_GET["set_cachesize"])){
		if($_GET["set_cachesize"]>3999){
			echo $tpl->_ENGINE_parse_body('{error_max_value_is}: 3999M');
			exit;
		}
	
		if($_GET["cachesize"]<1000){
			echo $tpl->_ENGINE_parse_body(replace_accents(html_entity_decode('{error_min_value_is}: 1000 {entries}')));
			exit;
		}
		
		$_GET["set_cachesize"]=($_GET["set_cachesize"]*1000)*1024;
		$sock->SET_INFO("LdapDBSetCachesize",$_GET["set_cachesize"]);
	
	}
	if(isset($_GET["cachesize"])){$sock->SET_INFO("LdapDBCachesize",$_GET["cachesize"]);}
	if(isset($_GET["LdapAllowAnonymous"])){$sock->SET_INFO("LdapAllowAnonymous",$_GET["LdapAllowAnonymous"]);}
	if(isset($_GET["EnableRemoteAddressBook"])){$sock->SET_INFO("EnableRemoteAddressBook",$_GET["EnableRemoteAddressBook"]);}
	if(isset($_GET["LdapListenIPAddr"])){$sock->SET_INFO("LdapListenIPAddr",$_GET["LdapListenIPAddr"]);}
	if(isset($_GET["OpenLDAPLogLevel"])){$sock->SET_INFO("OpenLDAPLogLevel",$_GET["OpenLDAPLogLevel"]);}
	
	
	$sock->getFrameWork("cmd.php?ldap-restart=yes");
	
}

function LDAP_SWITCH(){
	switch ($_GET["main-ldap"]) {
		case "ldap-network":LDAP_CONFIG_NET();exit;break;
		case "ldap-status":LDAP_CONFIG_STATUS();exit;break;
		case "ldap-bdbd":LDAP_CONFIG_BDBD();exit;break;
		case "ldap-syncprov":LDAP_SYNCPROV();exit;break;
		default:
			;
		break;
	}
	
	
}

function LDAP_CONFIG(){
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	$array["ldap-network"]="{network_settings}";
	$array["ldap-status"]="{databases_status}";
	$array["ldap-bdbd"]="{ldap_configure_bdbd}";
	if($users->LDAP_SYNCPROV){
		$array["ldap-syncprov"]="{synchronization}";
	}
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?main-ldap=$num\"><span>$ligne</span></a></li>\n";
		
	}
	
	
	echo "
	<div id=main_config_ldap_adv style='width:100%;height:430px;overflow:auto'>
		<ul>". $tpl->_ENGINE_parse_body(implode("\n",$html))."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_ldap_adv').tabs({
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

function LDAP_CONFIG_BDBD(){
	$sock=new sockets();
	$LdapdbSize=$sock->getFrameWork('cmd.php?LdapdbSize=yes');
	if(preg_match('#(.+?)\s+#',$LdapdbSize,$re)){
		$LdapdbSize=$re[1];
	}	
	
	$LdapDBSetCachesize=$sock->GET_INFO("LdapDBSetCachesize");
	$LdapDBSCachesize=$sock->GET_INFO("LdapDBCachesize");
	if($LdapDBSetCachesize==null){$LdapDBSetCachesize=5120000;}
	if($LdapDBSCachesize==null){$LdapDBSCachesize=1000;}	
	$LdapDBSetCachesizeMo=($LdapDBSetCachesize/1024)/1000;	
	
	$html="<div id='ParseFormLDAPNET'>
	<table style='width:100%' class=table_form>
<tr>
			<td class=legend>{set_cachesize}:</td>
			<td>". Field_text('set_cachesize',$LdapDBSetCachesizeMo,'width:25px')."M&nbsp;</td>
		</tr>
		<tr>
			<td class=legend>{ldap_cache_size}:</td>
			<td>". Field_text('cachesize',$LdapDBSCachesize,'width:55px')."&nbsp;{entries}</td>
		</tr>		
		
		<tr>
			<td colspan=2 align='right'><hr>
			". button("{edit}","ParseFormLDAPDB()")."
			</td>
		</tr>	
	</table>
	</div>	
	";
 $tpl=new templates();
 echo $tpl->_ENGINE_parse_body($html);	
}


function LDAP_CONFIG_NET(){
	$sock=new sockets();
	$page=CurrentPageName();
	$nets[null]="{loopback}";
	$nets["all"]="{all}";
	$return=Paragraphe32("troubleshoot","troubleshoot_explain","Loadjs('index.troubleshoot.php');","48-troubleshoots.png",180);

	$loglevel_hash=array(
	0=>"{none}",
	256=>"{basic}",
	512=>"stats log entries sent",
	1024=>"Communications",
	16384=>"{synchronization}",
	2048=>"{debug}",
	);
	
	$OpenLDAPLogLevel=$sock->GET_INFO("OpenLDAPLogLevel");
	if(!is_numeric($OpenLDAPLogLevel)){$OpenLDAPLogLevel=256;}
	$loglevel_field=Field_array_Hash($loglevel_hash,"OpenLDAPLogLevel",$OpenLDAPLogLevel,"style:font-size:13px;padding:3px");
	
	$form_network="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/ldap-performances-128.png'><hr>$return</td>
		<td valign='top'>
	<div id='ParseFormLDAPNET'>
			<table style='width:100%'>
				
				<tr>
					<td class=legend nowrap>{allowanonymouslogin}:</td>
					<td><strong style='font-size:11px' nowrap>". Field_checkbox("LdapAllowAnonymous",1,$sock->GET_INFO('LdapAllowAnonymous'))."</td>
				</tr>	
				<tr>
					<td class=legend nowrap>{remote_addressbook_text}:</td>
					<td><strong style='font-size:11px' nowrap>". Field_checkbox("EnableRemoteAddressBook",1,$sock->GET_INFO('EnableRemoteAddressBook'))."</td>
				</tr>
				<tr>
					<td class=legend nowrap>{log_level}:</td>
					<td><strong style='font-size:11px' nowrap>$loglevel_field</td>
				</tr>						
				<tr>
					<td colspan=2 align='right'><hr>
					". button("{edit}","ParseFormLDAPNET()")."
					</td>
				</tr>	
			</table>
		<div id='ldap_networks'></div>
		<table style='width:100%'>
				
				<tr>
					<td class=legend nowrap>{other_network_card}:</td>
					<td>". Field_text("other_network_card",null,"font-size:13px;padding:3px")."</td>
					<td>". button("{add}","other_network_card_add()")."
				</tr>	
		</table>
		
	</div>
	</td>
	</tr>
	</table>
	<br>
	
	
	<script>
		function LoadLdapNets(){
			LoadAjax('ldap_networks','$page?ldap-networks-list=yes');
		}
		
	var x_other_network_card_add= function (obj) {
		LoadLdapNets();
		}		
	
	function other_network_card_add(){
		var XHR = new XHRConnection();
		XHR.appendData('ldap_other_network_card',document.getElementById('other_network_card').value);
		document.getElementById('ldap_networks').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_other_network_card_add);
	
	}	

	function ParseFormLDAPNET(){
		var LdapAllowAnonymous;
		var EnableRemoteAddressBook;
		if(document.getElementById('LdapAllowAnonymous').checked){LdapAllowAnonymous=1;}else{LdapAllowAnonymous=0;}
		if(document.getElementById('EnableRemoteAddressBook').checked){EnableRemoteAddressBook=1;}else{EnableRemoteAddressBook=0;}	
		var XHR = new XHRConnection();
		XHR.appendData('LdapAllowAnonymous',LdapAllowAnonymous);
		XHR.appendData('EnableRemoteAddressBook',EnableRemoteAddressBook);
		XHR.appendData('OpenLDAPLogLevel',document.getElementById('OpenLDAPLogLevel').value);
		document.getElementById('ParseFormLDAPNET').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ParseFormLDAP);
		}	
		
	LoadLdapNets();
	</script>
	
	";	
			
 $tpl=new templates();
 echo $tpl->_ENGINE_parse_body($form_network);
	
}

function LDAP_CONFIG_NET_LIST(){
	$page=CurrentPageName();
	$sock=new sockets();
	$net=new networking();
	$nets=$net->ALL_IPS_GET_ARRAY();	
	$SavedNets=explode("\n",$sock->GET_INFO('LdapListenIPAddr'));
	
	if(is_array($SavedNets)){
		while (list($num,$ip)=each($SavedNets)){
			unset($nets[$ip]);
		}
		
	}
	unset($nets["127.0.0.1"]);
	$nets_field=Field_array_Hash($nets,'LdapListenIPAddr',null,null,null,0,'font-size:14px;padding:3px');
	if(is_array($nets)){
		$form="
		<table style='width:100%'>
		<tr>
			<td class=legend nowrap>{ListenAddress}:</td>
			<td><strong style='font-size:11px' nowrap>$nets_field</td>
			<td width=1%>". button("{add}","ldapAddNet()")."</td>
		</tr>
		</table>	
		";
	}
	$SavedNets[]="127.0.0.1";
	if(is_array($SavedNets)){
		$t="<table style='width:100%'>";
		
		reset($SavedNets);
		while (list($num,$ip)=each($SavedNets)){
			if($ip==null){continue;}
			$delete=imgtootltip("delete-24.png","{delete}","ldapDelNet($num)");
			if($ip=="127.0.0.1"){$delete=null;}
			$t=$t."<tr ". CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong><code style='font-size:14px'>$ip</td>
			<td width=1%>$delete</td>
			</tr>";
		}
	}
	
	$t=$t."</table>";
	
	$html="
	$form
	<hr>
	$t
	<script>
		function ldapAddNet(){
			var XHR = new XHRConnection();
			XHR.appendData('LdapListenIPAddr',document.getElementById('LdapListenIPAddr').value);
			XHR.appendData('ldapAddNet','yes');
			document.getElementById('ldap_networks').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_ParseFormLDAP);		
		}
		
		function ldapDelNet(num){
			var XHR = new XHRConnection();
			XHR.appendData('ldapDelNet',num);
			document.getElementById('ldap_networks').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_ParseFormLDAP);				
		}
		
	</script>
	";
	 $tpl=new templates();
 	echo $tpl->_ENGINE_parse_body($html);
}

function LDAP_CONFIG_NET_ADD(){
	if(trim($_GET["LdapListenIPAddr"])==null){return;}
	$sock=new sockets();
	$SavedNets=explode("\n",$sock->GET_INFO('LdapListenIPAddr'));
	$SavedNets[]=$_GET["LdapListenIPAddr"];
	$sock->SaveConfigFile(@implode("\n",$SavedNets),"LdapListenIPAddr");
	$sock->getFrameWork("cmd.php?ldap-restart=yes");
}
function LDAP_CONFIG_NET_ADD_OTHER(){
	if(trim($_GET["ldap_other_network_card"])==null){return;}
	$sock=new sockets();
	$SavedNets=explode("\n",$sock->GET_INFO('LdapListenIPAddr'));
	$SavedNets[]=$_GET["ldap_other_network_card"];
	$sock->SaveConfigFile(@implode("\n",$SavedNets),"LdapListenIPAddr");
	$sock->getFrameWork("cmd.php?ldap-restart=yes");	
}

function LDAP_CONFIG_NET_DEL(){
	$sock=new sockets();
	$SavedNets=explode("\n",$sock->GET_INFO('LdapListenIPAddr'));
	unset($SavedNets[$_GET["ldapDelNet"]]);
	$sock->SaveConfigFile(@implode("\n",$SavedNets),"LdapListenIPAddr");
	$sock->getFrameWork("cmd.php?ldap-restart=yes");	
}



function LDAP_CONFIG_STATUS(){
	$sock=new sockets();
	$dbstat=explode("\n",$sock->getFrameWork("cmd.php?LdapdbStat=yes"));
	$LdapdbSize=$sock->getFrameWork('cmd.php?LdapdbSize=yes');
	if(preg_match('#(.+?)\s+#',$LdapdbSize,$re)){
		$LdapdbSize=$re[1];
	}
	
$db="<table style='width:100%'>
		<tr>
			<td class=legend>{database_size}:</td>
			<td><strong style='font-size:11px' nowrap>$LdapdbSize</td>
		</tr>";	
	
	if(is_array($dbstat)){
		while (list ($num, $ligne) = each ($dbstat) ){
			if(preg_match("#Program version ([0-9\.]+) doesn't match environment version ([0-9\.]+)#",$ligne,$re[1])){
				$db=$db."<tr>
					<td><strong style='color:red;font-size:12px'>Please install db tools for {$re[2]} version</strong></td>
					</tr>";
					break;
			}
			
			if(preg_match("#^([0-9]+)\s+(.+)#",$ligne,$re)){
				
				$db=$db."<tr>
					<td class=legend nowrap>{$re[2]}:</td>
					<td><strong style='font-size:11px'>{$re[1]}</strong></td>
					</tr>";
			}
		}
	}
		
		$db=$db."</table>";	
 $tpl=new templates();
 echo $tpl->_ENGINE_parse_body($db);
		
}

	
function LDAP_CONFIG_SETTINGS(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$LdapDBSetCachesize=$sock->GET_INFO("LdapDBSetCachesize");
	$LdapDBSCachesize=$sock->GET_INFO("LdapDBCachesize");
	if($LdapDBSetCachesize==null){$LdapDBSetCachesize=5120000;}
	if($LdapDBSCachesize==null){$LdapDBSCachesize=1000;}
	$LdapListenIPAddr=$sock->GET_INFO('LdapListenIPAddr');
	

		
	$dbstat=explode("\n",$sock->getFrameWork("cmd.php?LdapdbStat=yes"));
	$LdapdbSize=$sock->getFrameWork('cmd.php?LdapdbSize=yes');
	if(preg_match('#(.+?)\s+#',$LdapdbSize,$re)){
		$LdapdbSize=$re[1];
	}
	
	
	$net=new networking();
	$nets=$net->ALL_IPS_GET_ARRAY();
	
	
	$nets=Field_array_Hash($nets,'LdapListenIPAddr',$LdapListenIPAddr);
	$form_network="
	<H3>{antispam_engine_text}</H3> 
	<table style='width:100%' class=table_form>
		<tr>
			<td class=legend nowrap>{ListenAddress}:</td>
			<td><strong style='font-size:11px' nowrap>$nets</td>
		</tr>
		<tr>
			<td class=legend nowrap>{allowanonymouslogin}:</td>
			<td><strong style='font-size:11px' nowrap>". Field_checkbox("LdapAllowAnonymous",1,$sock->GET_INFO('LdapAllowAnonymous'))."</td>
		</tr>	
		<tr>
			<td class=legend nowrap>{remote_addressbook_text}:</td>
			<td><strong style='font-size:11px' nowrap>". Field_checkbox("EnableRemoteAddressBook",1,$sock->GET_INFO('EnableRemoteAddressBook'))."</td>
		</tr>
		
		<tr>
			<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:ParseFormLDAP();\" value='{edit}&nbsp;&raquo'></td>
		</tr>	
	</table>
	";
	
	//Program version 4.6 doesn't match environment version 4.7
		
		
		
		
		
		
		
		$form="
		<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>{set_cachesize}:</td>
			<td>". Field_text('set_cachesize',$LdapDBSetCachesizeMo,'width:25px')."M&nbsp;</td>
		</tr>
		<tr>
			<td class=legend>{ldap_cache_size}:</td>
			<td>". Field_text('cachesize',$LdapDBSCachesize,'width:55px')."&nbsp;{entries}</td>
		</tr>	
		<tr>
			<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:ParseFormLDAP();\" value='{edit}&nbsp;&raquo'></td>
		</tr>	
		</table>
		";
		
		
$html="<H1>{ldap_configure_bdbd}</H1>
<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/ldap-performances-128.png'><hr>$return</td>
		
		<td valign='top'>
		<div id='cachesizediv'>
		$form_network
			<H3>{databases_status}</h3>
			<p class=caption>{set_cachesize_text}</p>
				$db
			<hr>
				<H3>{ldap_configure_bdbd}</H3>
				$form
		 </div>
		</td>
	</tr>
</table>";		

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mysql.index.php');
	
	
}

function MYSQL_AUDIT_js(){
	$page=CurrentPageName();
	return "
	
	function x_CheckMysqlAudit(obj) {
		var tempvalue=obj.responseText;
		document.getElementById('mysqlaudit').innerHTML=tempvalue;
	}	
	
	 function CheckMysqlAudit(){
		var XHR = new XHRConnection();
		XHR.appendData('server_memory',document.getElementById('server_memory').value);
		XHR.appendData('server_swap',document.getElementById('server_swap').value);
		document.getElementById('mysqlaudit').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_CheckMysqlAudit);		
	 
	 }
	
	";
	
}

function MYSQL_AUDIT(){
	$q=new mysql();
	include_once("ressources/class.os.system.tools.inc");
	$os=new os_system();
	$mem=$os->memory();

	
	$phy=round($mem["ram"]["total"]/1000);
	$swap=round($mem["swap"]["total"]/1000);
	$html="<H1>{mysql_audit}</H1>
	<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>{server}:</td>
			<td><strong>$q->mysql_server:$q->mysql_port</td>
		</tR>
		<tr>
		<td class=legend>{server_memory}:</td>
		<td> <strong>". Field_text('server_memory',$phy,'width:60px;font-size:14px;padding:3px'). "Mb</tD>
		</tr>
		<td class=legend>{server_swap}:</td>
		<td> <strong>". Field_text('server_swap',$swap,'width:60px;font-size:14px;padding:3px'). "Mb</tD>
		</tr>	
		<tr>
		<td colspan=2 align='right'><hr>". button("{go}","CheckMysqlAudit()")."
		</td>
		</tr>
		</table>
		<div style='width:100%;height:300px;overflow:auto' id='mysqlaudit'></div>";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mysql.index.php');
}

function MYSQL_AUDIT_PERFORM(){
	
$sock=new sockets();
	$q=new mysql();
	$datas=$sock->getFrameWork("cmd.php?mysql-audit=yes&username=$q->mysql_admin&pass=$q->mysql_password&host=$q->mysql_server&port=$q->mysql_port&server_memory={$_GET["server_memory"]}&server_swap={$_GET["server_swap"]}");
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		$html=$html."<div style='font-size:13px'>". htmlentities($ligne)."</div>\n";
	}
	echo $html;
}


function js_web_fw(){
	$page=CurrentPageName();
	$styleOfFields="width:190px;font-size:14px;padding:3px";
	$html="
	<div class=explain>{LIGHTTPD_IP_ACCESS_TEXT}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{tcp_address}:</td>
		<td >" . Field_text("ipaddr",$ligne["ipaddr"],$styleOfFields,null,"CalcCdirVirt(0)",null,false,null,$DISABLED)."</td>
		<td width=99% nowrap>". button("{add_single_ip}","LighttpdAddIP()")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{netmask}:</td>
		<td>" . Field_text("netmask",$ligne["netmask"],$styleOfFields,null,"CalcCdirVirt(0)",null,false,null,$DISABLED)."</td>
		<td>". imgtootltip("img_calc_icon-16.gif","{results}","CalcCdirVirt(1)")."</td>
	</tr>

	<tr>
		<td class=legend nowrap>{cdir}:</td>
		<td>" . Field_text("cdir",null,$styleOfFields,null,"",null,false,null,$DISABLED)."</td>
		<td>". button("{add_network}","LighttpdAddNet()")."</td>
	</table>

	<div style='width:95%;height:250px;margin:10px;padding:5px;border:1px dotted #CCCCCC;overflow:auto' id='lighttpd-nets'></div>
	
	<div style='margin:8px;witdh:100%;text-align:right'>". button("{restart_web_server}","LighttpdRestart()")."</div>
	<script>

			
		var X_CalcCdirVirt= function (obj) {
			var results=obj.responseText;
			document.getElementById('cdir').value=results;
		}	

		var X_LighttpdAddNet= function (obj) {
			var results=obj.responseText;
			RefreshLighttpdNets();
		}	

		var X_LighttpdRestart= function (obj) {
			var results=obj.responseText;
			alert(results);
			document.getElementById('dialog2').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			document.getElementById('BodyContent').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			document.getElementById('TEMPLATE_LEFT_MENUS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			setTimeout('locationNew()',10000);
		}
		
		function locationNew(){
			window.location.href='logoff.php';
		}
		
		function CalcCdirVirt(recheck){
			var cdir=document.getElementById('cdir').value;
			if(recheck==0){if(cdir.length>0){return;}}
			var XHR = new XHRConnection();
			XHR.appendData('cdir-ipaddr',document.getElementById('ipaddr').value);
			XHR.appendData('netmask',document.getElementById('netmask').value);
			XHR.sendAndLoad('$page', 'GET',X_CalcCdirVirt);
		}	
		
		
		

		function LighttpdAddNet(){
			var XHR = new XHRConnection();
			XHR.appendData('LighttpdAddNet',document.getElementById('cdir').value);
			XHR.sendAndLoad('$page', 'GET',X_LighttpdAddNet);
		}
		
		function LighttpdAddIP(){
			var XHR = new XHRConnection();
			XHR.appendData('LighttpdAddIP',document.getElementById('ipaddr').value);
			XHR.sendAndLoad('$page', 'GET',X_LighttpdAddNet);
		}	

		function LighttpdDelNet(KEY,INDEX){
			var XHR = new XHRConnection();
			XHR.appendData('LighttpdDelIP',KEY);
			XHR.appendData('num',INDEX);
			XHR.sendAndLoad('$page', 'GET',X_LighttpdAddNet);		
		}
		
		function RefreshLighttpdNets(){
			LoadAjax('lighttpd-nets','$page?js-web-fw-nets=yes');
		}
		
		function LighttpdRestart(){
			var XHR = new XHRConnection();
			XHR.appendData('LighttpdRestart','yes');
			XHR.sendAndLoad('$page', 'GET',X_LighttpdRestart);		
		}
		
	RefreshLighttpdNets();		
	</script>	
	";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mysql.index.php');	
}
function calc_cdir(){
	$ipaddr=$_GET["cdir-ipaddr"];
	$newmask=$_GET["netmask"];
	$calc=base64_encode("$ipaddr/$newmask");
	$sock=new sockets();
	echo trim(base64_decode($sock->getFrameWork("cmd.php?cdir-calc=$calc")));
}

function js_web_fw_add_net(){
	$net=$_GET["LighttpdAddNet"];
	$sock=new sockets();
	$LighttpdNets=unserialize(base64_decode($sock->GET_INFO("LighttpdNets")));
	if(!is_array($LighttpdNets)){$LighttpdNets=array();}
	$LighttpdNets["NETS"][]=$net;
	$sock->SaveConfigFile(base64_encode(serialize($LighttpdNets)),"LighttpdNets");
	
}

function js_web_fw_del_ip(){
	$key=$_GET["LighttpdDelIP"];
	$index=$_GET["num"];
	$sock=new sockets();
	$LighttpdNets=unserialize(base64_decode($sock->GET_INFO("LighttpdNets")));
	if(!is_array($LighttpdNets)){$LighttpdNets=array();}
	unset($LighttpdNets[$key][$index]);
	if(!is_array($LighttpdNets)){$LighttpdNets=array();}
	$sock->SaveConfigFile(base64_encode(serialize($LighttpdNets)),"LighttpdNets");
}

function js_web_fw_add_ip(){
	$net=$_GET["LighttpdAddIP"];
	$sock=new sockets();
	$LighttpdNets=unserialize(base64_decode($sock->GET_INFO("LighttpdNets")));
	if(!is_array($LighttpdNets)){$LighttpdNets=array();}
	$LighttpdNets["IPS"][]=$net;
	$sock->SaveConfigFile(base64_encode(serialize($LighttpdNets)),"LighttpdNets");	
}

function js_web_fw_list(){
	$sock=new sockets();
	$LighttpdNets=unserialize(base64_decode($sock->GET_INFO("LighttpdNets")));
	if(!is_array($LighttpdNets)){return null;}
	
$html="<table class=tableView>
	<thead class=thead>
	<tr>
		<th colspan=3>{networks}</th>
	</tr>
	</thead>
	";		
	if(isset($LighttpdNets["NETS"])){
		if(is_array($LighttpdNets["NETS"])){
			while (list ($num, $ligne) = each ($LighttpdNets["NETS"]) ){
				if(trim($ligne)==null){return;}
				if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
				$html=$html."
				<tr class=$cl>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:14px;font-weight:bold'>$ligne</td>
				<td width=1%>". imgtootltip("delete-24.png","{delete}","LighttpdDelNet('NETS',$num)")."</td>
				</tr>
				";
			}
		}
	}
	if(isset($LighttpdNets["IPS"])){
		if(is_array($LighttpdNets["IPS"])){	
			while (list ($num, $ligne) = each ($LighttpdNets["IPS"]) ){
				if(trim($ligne)==null){return;}
				if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
				$html=$html."
				<tr class=$cl>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:14px;font-weight:bold'>$ligne</td>
				<td width=1%>". imgtootltip("delete-24.png","{delete}","LighttpdDelNet('IPS',$num)")."</td>
				</tr>
				";
			}
		}
	}	
	
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'mysql.index.php');		
	
	
}

function LighttpdRestart(){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{success}\n$infos{interface_restarted}\n");
		$sock=new sockets();
		$sock->getFrameWork('cmd.php?restart-http-engine=yes');
}

function LDAP_SYNCPROV(){

	$tpl=new templates();
	$page=CurrentPageName();
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	$sock=new sockets();
	$EnableLDAPSyncProv=$sock->GET_INFO("EnableLDAPSyncProv");
	$EnableLDAPSyncProvClient=$sock->GET_INFO("EnableLDAPSyncProvClient");
	$SyncProvUser=$sock->GET_INFO("SyncProvUser");
	$LDAPSyncProvClientServer=$sock->GET_INFO("LDAPSyncProvClientServer");
	$LDAPSyncProvClientSearchBase=$sock->GET_INFO("LDAPSyncProvClientSearchBase");
	$LDAPSyncProvClientBindDN=$sock->GET_INFO("LDAPSyncProvClientBindDN");
	$LDAPSyncProvClientBindPassword=$sock->GET_INFO("LDAPSyncProvClientBindPassword");
	$EnableLDAPSyncProvClient=$sock->GET_INFO("EnableLDAPSyncProvClient");
	
	if($SyncProvUser<>null){
		$u=new user($SyncProvUser);
		$userdn=$u->dn;
	}
	
	if($EnableLDAPSyncProv==null){$EnableLDAPSyncProv=0;}
	$html="
	<div id='synprovdiv'>
	<table style='width:100%'>
	<tr>
		<td colspan=3><strong style='font-size:16px'>{server_mode}</strong></td>
	</tr>
	<tr>
		<td class=legend>{enable_ldap_sync_service}:</td>
		<td>". Field_checkbox("EnableLDAPSyncProv",1,$EnableLDAPSyncProv,"SyncProvCheck()")."</td>
		<td width=1% align='left'>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{sync_user}:</td>
		<td>".Field_text("sync_user",$SyncProvUser,"font-size:13px;padding:3px;width:150px")."</td>
		<td><input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('user.browse.php?field=sync_user')\"></td>
	</tr>
	<td colspan=3><strong style='font-size:11px'>$userdn</strong></td>
	<tr>
		<td colspan=3><hr><strong style='font-size:16px'>{client_mode}</strong></td>
	</tr>	
	<tr>
		<td class=legend>{enable_client_mode}:</td>
		<td>". Field_checkbox("EnableLDAPSyncProvClient",1,$EnableLDAPSyncProvClient,"SyncProvCheckClient()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{ldap_server}:</td>
		<td>".Field_text("LDAPSyncProvClientServer",$LDAPSyncProvClientServer,"font-size:13px;padding:3px;width:190px")."</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend>{searchbasedn}:</td>
		<td>".Field_text("LDAPSyncProvClientSearchBase",$LDAPSyncProvClientSearchBase,"font-size:13px;padding:3px;width:190px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{ldap_user_dn}:</td>
		<td>".Field_text("LDAPSyncProvClientBindDN",$LDAPSyncProvClientBindDN,"font-size:13px;padding:3px;width:290px")."</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend>{ldap_password}:</td>
		<td>".Field_password("LDAPSyncProvClientBindPassword",$LDAPSyncProvClientBindPassword,"font-size:13px;padding:3px;width:90px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveSyncProv()")."</td>
	</tr>
	
	</table>
	</div>
	<script>
		function SyncProvCheck(){
			document.getElementById('sync_user').disabled=true;
			document.getElementById('LDAPSyncProvClientServer').disabled=true;
			document.getElementById('LDAPSyncProvClientSearchBase').disabled=true;
			document.getElementById('LDAPSyncProvClientBindDN').disabled=true;
			document.getElementById('LDAPSyncProvClientBindPassword').disabled=true;

			if(document.getElementById('EnableLDAPSyncProv').checked){
				document.getElementById('EnableLDAPSyncProvClient').checked=false;
				document.getElementById('sync_user').disabled=false;
			}
			
			if(document.getElementById('EnableLDAPSyncProvClient').checked){
				document.getElementById('EnableLDAPSyncProv').checked=false;
				document.getElementById('LDAPSyncProvClientServer').disabled=false;
				document.getElementById('LDAPSyncProvClientSearchBase').disabled=false;
				document.getElementById('LDAPSyncProvClientBindDN').disabled=false;
				document.getElementById('LDAPSyncProvClientBindPassword').disabled=false;	
			}			
		
		}
		
		function SyncProvCheckClient(){
			if(document.getElementById('EnableLDAPSyncProvClient').checked){
				document.getElementById('EnableLDAPSyncProv').checked=false;
			}
			SyncProvCheck();
		}

		var x_SaveSyncProv= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_ldap_adv');
		}
				
		function SaveSyncProv(){
			var XHR = new XHRConnection();
			XHR.appendData('SaveSyncProv','yes');
			if(document.getElementById('EnableLDAPSyncProvClient').checked){XHR.appendData('EnableLDAPSyncProvClient',1);}else{XHR.appendData('EnableLDAPSyncProvClient',0);}
			if(document.getElementById('EnableLDAPSyncProv').checked){XHR.appendData('EnableLDAPSyncProv',1);}else{XHR.appendData('EnableLDAPSyncProv',0);}					
			
			XHR.appendData('sync_user',document.getElementById('sync_user').value);
			XHR.appendData('LDAPSyncProvClientServer',document.getElementById('LDAPSyncProvClientServer').value);
			XHR.appendData('LDAPSyncProvClientSearchBase',document.getElementById('LDAPSyncProvClientSearchBase').value);
			XHR.appendData('LDAPSyncProvClientBindDN',document.getElementById('LDAPSyncProvClientBindDN').value);
			XHR.appendData('LDAPSyncProvClientBindPassword',document.getElementById('LDAPSyncProvClientBindPassword').value);
			
			document.getElementById('synprovdiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveSyncProv);	
		}
		
	
	SyncProvCheck();	
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function LDAP_SYNCPROV_SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("EnableLDAPSyncProv",$_GET["EnableLDAPSyncProv"]);
	$sock->SET_INFO("EnableLDAPSyncProvClient",$_GET["EnableLDAPSyncProvClient"]);
	$sock->SET_INFO("SyncProvUser",$_GET["sync_user"]);
	$sock->SET_INFO("LDAPSyncProvClientServer",$_GET["LDAPSyncProvClientServer"]);
	$sock->SET_INFO("LDAPSyncProvClientSearchBase",$_GET["LDAPSyncProvClientSearchBase"]);
	$sock->SET_INFO("LDAPSyncProvClientBindDN",$_GET["LDAPSyncProvClientBindDN"]);
	$sock->SET_INFO("LDAPSyncProvClientBindPassword",$_GET["LDAPSyncProvClientBindPassword"]);
	include_once(dirname(__FILE__)."/ressources/class.user.inc");
	if($_GET["sync_user"]<>null){
		$ldap=new clladp();
		$u=new user($_GET["sync_user"]);
		$sock->SET_INFO("SyncProvUserDN",$u->dn);
	}
	$sock->getFrameWork("cmd.php?ldap-restart=yes");
}

function LighttpdArticaDisabled(){
	$sock=new sockets();
	$sock->SET_INFO("LighttpdArticaDisabled",$_GET["LighttpdArticaDisabled"]);
	$sock->getFrameWork("cmd.php?restart-web-server=yes");
	}
function js_LighttpdArticaDisableSSLv2_save(){
	$sock=new sockets();
	$sock->SET_INFO("LighttpdArticaDisableSSLv2",$_POST["LighttpdArticaDisableSSLv2"]);
	$sock->getFrameWork("cmd.php?restart-web-server=yes");
	}	
	


function js_web_miniadm(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$DenyMiniWebFromStandardPort=$sock->GET_INFO("DenyMiniWebFromStandardPort");
	
	$html="<table class=form style='width:100%'>
	<tr>
		<td class=legend>{deny_access_from_the_standard_port}:</td>
		<td>". Field_checkbox("DenyMiniWebFromStandardPort", 1,$DenyMiniWebFromStandardPort,"DenyMiniWebFromStandardPortCheck()")."</td>
	</tr>
	</table>
	
	<script>
		var x_DenyMiniWebFromStandardPortSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			
		}
				
		function DenyMiniWebFromStandardPortCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('DenyMiniWebFromStandardPort').checked){XHR.appendData('DenyMiniWebFromStandardPort',1);}else{XHR.appendData('DenyMiniWebFromStandardPort',0);}
			XHR.sendAndLoad('$page', 'POST',x_DenyMiniWebFromStandardPortSave);	
		}	
	
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}

function js_web_miniadm_save(){
	$sock=new sockets();
	if(isset($_POST["DenyMiniWebFromStandardPort"])){$sock->SET_INFO("DenyMiniWebFromStandardPort",$_POST["DenyMiniWebFromStandardPort"]);}
	$sock->getFrameWork("cmd.php?restart-web-server=yes");
}
	
?>