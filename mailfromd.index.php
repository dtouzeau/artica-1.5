<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.mailfromd.inc');
	$user=new usersMenus();
	if(!$user->AsPostfixAdministrator){header('location:users.index.php');exit;};
	if(isset($_GET["status"])){page_status();exit;}
	if(isset($_GET["section"])){main_page();exit;}
	if(isset($_GET["spamass_enabled"])){SaveForm();exit;}
	if(isset($_GET["MailFromdUserScript"])){SaveScript();exit;}
	if(isset($_GET["ApplySettings"])){ApplSettings();exit;}
	
	




page();
function page(){
$page=CurrentPageName();
$user=new usersMenus();
IF($_GET["hostname"]==null){$_GET["hostname"]=$user->hostname;}

$html="	
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

function demar1(){
   tant = tant+1;
   
        

   if (tant < 2 ) {                             //delai court pour le premier affichage !
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
               tant = 0;                            //reinitialise le compteur
               ChargeLogs();
                   
        demarre();                                 //on lance la fonction demarre qui relance le compteur
   }
}

function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	}
	
function ChargeIndex(){
	LoadAjax('main_config','$page?section=domains-rules&hostname={$_GET["hostname"]}');
	}	
</script>	
<table style='width:100%'>
<tr>
<td><div id=services_status></div></td>
</tr>
<td><div id=main_config></div></td>
</tr>
</table>	
	<script>ChargeLogs();ChargeIndex();demarre();</script>

	";
$cfg["JS"][]="js/amavis.js";	
$tpl=new template_users("{APP_MAILFROMD}",$html,$_SESSION,0,0,0,$cfg);
echo $tpl->web_page;			
	
	
}

function page_status(){
		$p=new usersMenus();
		$users=new usersMenus();	
		$socks=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($socks->getfile('mailfromd_status'));
		if(!is_array($ini->_params)){return null;}	
		
if($ini->_params["MAILFROMD"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$rouage_title='{start_service}';
		$rouage_text='{start_service_text}';
		$error= "";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		$rouage='rouage_off.png';
		$rouage_title='{stop_service}';
		$rouage_text='{stop_service_text}';		
	}
	

	
	$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_MAILFROMD}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["MAILFROMD"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td><strong>{$ini->_params["MAILFROMD"]["master_memory"]}&nbsp;kb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["MAILFROMD"]["master_version"]}</strong></td>
		</tr>	
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
$ini->loadString($socks->getfile('clamav_status'));	
if($ini->_params["CLAMAV"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$rouage_title='{start_service}';
		$rouage_text='{start_service_text}';
		$error= "";
		$serv_status="{stopped}";
	}else{
		$img="ok32.png";
		$serv_status="running";
		$rouage='rouage_off.png';
		$rouage_title='{stop_service}';
		$rouage_text='{stop_service_text}';		
		
	}

	$statusclam="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_CLAMAV}:</strong></td>
		<td><strong>$serv_status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["CLAMAV"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td><strong>{$ini->_params["CLAMAV"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["CLAMAV"]["master_version"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{pattern_version}:</strong></td>
		<td><strong>{$ini->_params["CLAMAV"]["pattern_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";	
$ini=new Bs_IniHandler();
$ini->loadString($socks->getfile('spamassassin_status'));

if($ini->_params["SPAMASSASSIN"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$rouage_title='{start_service}';
		$rouage_text='{start_service_text}';
		$error= "";
		$serv_status="{stopped}";
	}else{
		$img="ok32.png";
		$serv_status="running";
		$rouage='rouage_off.png';
		$rouage_title='{stop_service}';
		$rouage_text='{stop_service_text}';		
		
	}

	$statusspam="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_SPAMASSASSIN}:</strong></td>
		<td><strong>$serv_status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["SPAMASSASSIN"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td><strong>{$ini->_params["SPAMASSASSIN"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["SPAMASSASSIN"]["master_version"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{pattern_version}:</strong></td>
		<td><strong>{$ini->_params["SPAMASSASSIN"]["pattern_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";		
	
	$status=RoundedLightGreen($status);
	$statusclam=RoundedLightGreen($statusclam);
	$statusspam=RoundedLightGreen($statusspam);
	$tpl=new templates();
	
	$html="<table style='width:100%'>
	<tr>
	<td valign='top' width=50% align='center'><img src='img/bg_mailfromd.png'><br>$status
	<p class=caption style='text-align:left'><br>{about}</p>
	</td>
	<td valign='top' width=50%>$statusclam<br>$statusspam</td>
	</tr>
	</table>";
	
	
	echo $tpl->_ENGINE_parse_body($html);		
		
	}
	
function main_tabs(){
	$page=CurrentPageName();
	$array["global-settings"]='{global_settings}';
	$array["script"]='{script}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&section=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<br><div id=tablist>$html</div><br>";		
}	
	
	
function main_page(){
	
	switch ($_GET["section"]) {
		case "script":echo main_script();break;
		case "smtp-domain-rule":echo main_domain_rule_single();break;
		case "events":echo main_events();break;
		case "fresh-events":echo main_freshevents();break;
		case "global-settings":echo main_settings();break;
		default:echo main_settings();break;
	}
	
	
	
}

function main_events(){
	
	$html=main_tabs() . "<iframe style='width:100%;height:600px;border:0px;padding:0px' src='amavis.events.php'></iframe>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}


function main_settings(){
	$mailfrom=new mailfromd();
	$page=CurrentPageName();
	
	$html=main_tabs() . "
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<H5>{global_settings}</H5>
		<br>
		" . RoundedLightGrey("
		<H6>{enabled_plugins}</h6><br>
		<form name='ffm1'>
		<table style='width:100%'>
		<tr>
		<td align='right'><strong>{USE_CLIENT_SCRIPT}:</strong></td>
		<td align='left'>" . Field_numeric_checkbox_img('MailFromdUserUpdated',$mailfrom->MailFromdUserUpdated,'{enable_disable}') ."</td>
		</tr>	
		
		<tr>
		<td align='right'><strong>{APP_SPAMASSASSIN}:</strong></td>
		<td align='left'>" . Field_numeric_checkbox_img('spamass_enabled',$mailfrom->spamass_enabled,'{enable_disable}') ."</td>
		</tr>
		
		<tr>
		<td align='right'><strong>{APP_CLAMAV}:</strong></td>
		<td align='left'>" . Field_numeric_checkbox_img('clamav_enabled',$mailfrom->clamav_enabled,'{enable_disable}') ."</td>
		</tr>	
		<tr><td colspan=2><hr></td></tr>
		<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true);\" value='{edit}&nbsp;&raquo;&raquo;'></td></tr>
				
			</table>
			</form>
	") ."</td>
	<td valign='top'>
	" .  RoundedLightGrey(Paragraphe('system-64.png','{apply_to_server}','{apply_settings_text}',"javascript:ApplySettings(\"$page\")",'apply_settings'))."
	
	</td>
	</tr>
	</table>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function main_script(){
	$page=CurrentPageName();
	$mailfromd=new mailfromd();
	$html=main_tabs() . "
	<form name='ffm1'>
	<input type='hidden' name='UserUpdated' value='1'>
	<H5>{script}</H5>
	<br>
	<textarea style='margin:10px;font-size:12px;padding:10px;border:1px dotted #CCCCCC;width:100%;height:440px;background-image:none' name='MailFromdUserScript'>$mailfromd->MailFromdUserScript</textarea>
	<br>
	<div style='text-align:right;width:100%'><input type='button' OnClick=\"javascript:ParseForm('ffm1','$page',true);\" value='{edit}&nbsp;&raquo;&raquo;'></div>
	</form>
	";

$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function SaveForm(){
	$mailfromd=new mailfromd();
	$mailfromd->spamass_enabled=$_GET["spamass_enabled"];
	$mailfromd->clamav_enabled=$_GET["clamav_enabled"];
	$mailfromd->MailFromdUserUpdated=$_GET["MailFromdUserUpdated"];
	$mailfromd->SaveToLdap();
	
}

function SaveScript(){
	$mailfromd=new mailfromd();
	$_GET["MailFromdUserScript"]=stripcslashes($_GET["MailFromdUserScript"]);
	$mailfromd->MailFromdUserScript=$_GET["MailFromdUserScript"];
	$mailfromd->SaveToLdap();
	
}

function ApplSettings(){
	
	switch ($_GET["ApplySettings"]) {
		case -1:
			APPL_SETTINGS_0();
			break;
		case 0:
			APPL_SETTINGS_1();
			break;	
			
		case 1:
			APPL_SETTINGS_2();
			break;		
			
		case 2:
			APPL_SETTINGS_3();
			break;
		case 3:
			APPL_SETTINGS_4();
			break;												
		default:
			break;
	}
	
	
	
}

function APPL_SETTINGS_0(){
	
	$html="
	<h5>{apply_to_server}</H5>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/system-64.png' style='margin:5px'></td>
	<td valign='top'>
	<div id='message_0'></div>
	<div id='message_1'></div>
	<div id='message_2'></div>
	<div id='message_3'></div>
	</td></tr></table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function APPL_SETTINGS_1(){
	$mailfromd=new mailfromd();
	$mailfromd->SaveToLdap();
	
	$html="
	<strong><img src='img/fw_bold.gif'>&nbsp;{save_to_ldap_success}</strong>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function APPL_SETTINGS_2(){
	$mailfromd=new mailfromd();
	$mailfromd->SaveToServer();
	
	$html="
	<strong><img src='img/fw_bold.gif'>&nbsp;{save_to_server_success}</strong>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function APPL_SETTINGS_4(){
	$mailfromd=new mailfromd();
	$mailfromd->SaveToServer();
	
	$html="
	<strong class=caption>{close_windows}</strong>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}	
	
function APPL_SETTINGS_3(){
		$socks=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($socks->getfile('mailfromd_status'));
		if(!is_array($ini->_params)){return null;}	
		
if($ini->_params["MAILFROMD"]["running"]==0){	
	$html="<img src='img/fw_bold.gif'>&nbsp;<strong>{service_mailfromd_failed}</strong>";
}else{
	$html="<img src='img/fw_bold.gif'>&nbsp;<strong>{service_mailfromd_suc}</strong>";
}

$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

?>