<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');	
include_once('ressources/class.sockets.inc');	
include_once('ressources/kav4mailservers.inc');
include_once('ressources/class.kas-filter.inc');
include_once('ressources/class.main_cf.inc');
include_once("ressources/class.main_cf_filtering.inc");
include_once("ressources/class.html.pages.inc");
include_once("ressources/class.artica.inc");
include_once("ressources/class.cyrus.inc");
include_once("ressources/class.system.network.inc");

if(isset($_GET["script"])){echo switch_script();exit;}
if($_GET["section"]=="js"){echo main_page_js();exit;}


$users=new usersMenus();
if($users->AsArticaAdministrator==false){die();}
switch_main();

function switch_main(){
	
	switch ($_GET["main"]){
		case "init":$content=main_page();break;
		case "enable_kasper":main_kaspersky();exit;break;
		case "kasper_level":main_kaspersky_level();exit;break;
		case "kasper_action":main_kaspersky_action();exit;break;
		case "kasper_save":main_kaspersky_save();exit;break;
		case "fetchmail":main_fetchmail();exit;break;
		case "main_fetchmail_1":main_fetchmail_1();exit;break;
		case "main_fetchmail_2":main_fetchmail_2();exit;break;
		case "main_fetchmail_3":main_fetchmail_3();exit;break;
		case "main_fetchmail_build":main_fetchmail_build();exit;break;
		
		case "postmaster":main_postmaster();exit;break;
		
		default:$content=main_page();
	}
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(render_page($content));
	
}

function switch_script(){
		switch ($_GET["script"]){
		case "enable_kasper":scripts_kasper();break;
		case "fetchmail_script":main_fetchmail_script();break;
		case "wizard":echo file_get_contents('js/wizard.js');exit;break;
		case "postmaster_script":echo main_postmaster_script();exit;break;
		
		
	}
	
	
}



function render_page($content=null){

$hour=date('h');
$key_cache="CACHEINFOS_STATUSSEVERREDNDER$hour";
if(isset($_SESSION[$key_cache])){return $_SESSION[$key_cache];}
unset($_SESSION["DISTRI"]);

include_once('ressources/class.os.system.inc');
include_once("ressources/class.os.system.tools.inc");
if(!isset($_SESSION["DISTRI"])){
	$sys=new systeminfos();
	writelogs('Loading datas system for session',__FUNCTION__,__FILE__);
	$distri=$sys->ditribution_name;
	$kernel=$sys->kernel_version;
	$LIBC=$sys->libc_version;
	$temp=$sys->GetCpuTemp();
	$users=new usersMenus();
	$os=new os_system();
	$arraycpu=$os->cpu_info();
	$cpuspeed=round(($arraycpu["cpuspeed"]/1000*100)/100,2); 
	$host=$users->hostname;
	$publicip=@file_get_contents("ressources/logs/web/myIP.conf");

	$distri="<table style='width:100%;color:white;font-size:11px'>
				<tr>
					<td valign='top' style='font-size:12px'>
						<strong>{server}</strong>:&nbsp;$host<br><br>{public_ip}:&nbsp;<strong>$publicip</strong><br><strong> {$arraycpu["cpus"]} cpu(s):{$cpuspeed}GHz</strong>
					</td>
					<td valign='top' style='font-size:12px'>
						<strong>Artica&nbsp;$users->ARTICA_VERSION&nbsp;$distri&nbsp;kernel $kernel<br>libc $LIBC&nbsp;&nbsp;&Temp $temp&nbsp;C</strong>
					</td>
				</tr>
			</table>";
	
	$_SESSION["DISTRI"]=$distri;
}else{
	$distri=$_SESSION["DISTRI"];
}

$distri_logo="img/serv-mail-linux.png";
if(is_file("img/$users->LinuxDistriCode.png")){$distri_logo="img/$users->LinuxDistriCode.png";}
if(is_file("img/$users->LinuxDistriCode.gif")){$distri_logo="img/$users->LinuxDistriCode.gif";}

$artica=new artica_general();
	
	
	
	$html="
	<table style='width:100%;background-color:#005446'>
		<tr>
			<td style='background-color:#005447;margin:0px;padding:0px;border:0px'>
				<table style='width:100%;margin:0px;padding:0px;border:0px'>
				<tr>
					<td width=1%>
						<img src='$distri_logo'>
					</td>
					<td valign='top'>
							<table style='width:100%'>
								<tr>
									<td valign='top'><span style='color:white;font-size:18px;font-weight:bold'>{manage_your_server}</td>
								</tr>
								<tr>
								<td valign='top' style='padding-top:3px;border-top:1px dotted white;font-size:11px'>
									$distri
								</td>
								</tr>
							</table>
					</td>
				</tr>
				</table>
			 </td>
			</tr>
		<tr style='margin:0px;padding:0px;border:0px'>
			<td valign='top' style='background-color:#005446;margin:0px;padding:0px;border:0px'>
					$content
			</td>
		</tr>
	</table>
		<table style='width:100%;background-color:white'>
			<tr>
				<td width=1%>" . Field_checkbox('ConfigureYourserverStart',1,$artica->ArticaFirstWizard,"OnClick=\"javascript:ConfigureYourserver_Cancel();\"")."</td>
				<td align='left'>{disable_startup}</td>
			</tr>
		</table>	
	";
	$_SESSION[$key_cache]=$html;
	return $html;
	
	
}

function main_page_js(){
if($_COOKIE["configure_your-server-tab"]==null){$_COOKIE["configure_your-server-tab"]="section_system";}
	
$html="	
	
YahooWin0(765,'configure.server.php?section=init','your server');

function switchDiv(id){
	document.getElementById('section_system').style.display='none';
   	document.getElementById('section_apps').style.display='none';
   	document.getElementById('section_security').style.display='none';
   	document.getElementById('section_wizard').style.display='none';
   	document.getElementById('section_messaging').style.display='none';
   	document.getElementById(id).style.display='block';   
   	Set_Cookie('configure_your-server-tab', id, '3600', '/', '', '');
}



";

echo $html;
	
}

function main_page(){
	if(isset($_SESSION["$key_cache"])){return $_SESSION["$key_cache"];}
	$clamav=clamav();
	$kaspersky=kaspersky();
	$fetchmail=fetchmail();
	$samba1=samba_domain();
	$system_links=icon_system();
	$samab=samba_links();
	$applis=applis();
	$K=statkaspersky();
	$sysinfos=sysinfos();
	$cert=certificate();
	$ar_perf=atica_perf();
	$apt=apt();
	$sock=new sockets();
	$backup=incremental_backup();
	$firstsettings=FirstWizard();
	$wizard_postmaster=postmaster();
	$Postfix_links=Postfix_links();
	$button_messaging=button_messaging();
	$users=new usersMenus();
	$icon_events=icon_events();
	$nic_settings=nic_settings();
	$icon_memory=icon_memory();
	$icon_view_queue=icon_view_queue();
	$icon_amavis=icon_amavisdnew();
	$icon_openvpn=icon_openvpn();
	$icon_adduser=icon_adduser();
	$scancomputers=scancomputers();
	$icon_harddrive=icon_harddrive();
	$postfix_events=postfix_events();
	$postfix_reports=postfix_reports();
	$icon_troubleshoot=icon_troubleshoot();
	$icon_update_clamav=icon_update_clamav();
	$dmidecode=dmidecode();
	$external_ports=icon_externalports();
	$icon_update_artica=icon_update_artica();
	$icon_update_spamassassin_blacklist=icon_update_spamassassin_blacklist();
	$sharenfs=sharenfs();
	$clientnfs=clientnfs();
	$wizard_kasperAPPSMTP=wizard_kaspersky_appliance_smtp();
	$wizard_backup=wizard_backup();
	
	if(!$users->POSTFIX_INSTALLED){$button_messaging=null;}
	
	if($users->KASPERSKY_SMTP_APPLIANCE){
		$clamav=null;
		$icon_amavis=null;
		$icon_update_spamassassin_blacklist=null;
		}
	
	
if($_COOKIE["configure_your-server-tab"]==null){$_COOKIE["configure_your-server-tab"]="section_system";}
$form_display["section_system"]="none";
$form_display["section_apps"]="none";
$form_display["section_security"]="none";
$form_display["section_wizard"]="none";
$form_display["section_messaging"]="none";
$form_display[$_COOKIE["configure_your-server-tab"]]="block";


	
	
	$form_system="
	<div id='section_system' style='display:{$form_display["section_system"]};width:100%;height:300px;overflow:auto'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>
				$sysinfos$ar_perf$icon_events$icon_openvpn$scancomputers$sharenfs$clientnfs
			</td>
			<td valign='top'>
				$system_links$nic_settings$icon_memory$icon_harddrive$dmidecode$icon_adduser</td>
		</tr>
		</table>
	</div>
	";
	
	$form_apps="
		<div id='section_apps' style='display:{$form_display["section_apps"]}'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>
				$applis$apt
			</td>
			<td valign='top' width=1%>$icon_update_clamav$icon_update_spamassassin_blacklist$icon_update_artica</td>
		</tr>
		</table>
	</div>
	";
	
	$form_security="
		<div id='section_security' style='display:{$form_display["section_security"]}'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>
				$kaspersky$K$clamav$icon_troubleshoot
			</td>
			<td valign='top' width=1%>
			$cert$external_ports$backup
			</td>
		</tr>
		</table>
	</div>
	";	
	
	$form_wizard="
		<div id='section_wizard' style='display:{$form_display["section_wizard"]}'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>
				$wizard_kasperAPPSMTP$firstsettings$wizard_postmaster$icon_adduser
			</td>
			<td valign='top' width=1%>
			$wizard_backup$samab$samba1
			</td>
		</tr>
		</table>
	</div>
	";		
	
$section_messaging="<div id='section_messaging' style='display:{$form_display["section_messaging"]}'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>
				$Postfix_links
			</td>
			<td valign='top' width=1%>
				$icon_view_queue$icon_amavis$postfix_events$postfix_reports
			</td>
		</tr>
		</table>
	</div>
	";		
	

$buttons=button_system().button_apps().button_security().button_wizard().$button_messaging;
$buttons=RoundedLightWhite($buttons);



$html="
<table style='width:100%;style='margin:-2px;'>
<tr>
<td valign='top' style='background-color:#005446;padding:3px;'>
	$buttons
</td>
<td valign='top' style='padding:3px;'><div style='width:510px'>".RoundedLightWhite($form_system.$form_apps.$form_security.$form_wizard.$section_messaging)."</div>
</td>
</tr>
</table>
";


	$_SESSION["$key_cache"]=$html;
	return $html;
}



function button_messaging(){
	$img="48-messaging.png";
	$js="javascript:switchDiv('section_messaging');";
	$element=element_rollover($js);
	$html="
	<table style='width:200px;margin-top:4px;' $element>
		<tr>
			<td width=1% valign='top'>" . imgtootltip($img,'{messaging}',"$js",'messaging_text')."</td>
			<td><strong style='font-size:12px'>{messaging}</strong><br><a href=\"$js;\">{messaging_text}</a></td>
		</tr>
	</table>";	
	return $html;		
}

function button_wizard(){
	
	$img="48-wizard.png";
	$js="javascript:switchDiv('section_wizard');";
	$element=element_rollover($js);
	$html="
	<table style='width:200px;margin-top:4px;' $element>
		<tr>
			<td width=1% valign='top'>" . imgtootltip($img,'{wizard}',"$js",'wizard_section_text')."</td>
			<td><strong style='font-size:12px'>{wizard}</strong><br><a href=\"$js;\">{wizard_section_text}</a></td>
		</tr>
	</table>";	
	return $html;		
}

function button_system(){
		
	$js="javascript:switchDiv('section_system');";
	
	$element=element_rollover($js);
	$html="
	<table style='width:200px;margin-top:4px;' $element>
		<tr>
			<td width=1% valign='top'>" . imgtootltip('48-computer.png','{system_information}',"$js",'system_information')."</td>
			<td><strong style='font-size:12px'>{system_information}</strong><br><a href=\"$js;\">{system_information_text}</a></td>
		</tr>
	</table>";	
	
	return $html;
}




function button_apps(){
	$img="48-apps.png";
	$js="javascript:switchDiv('section_apps');";
	$element=element_rollover($js);
	$html="
	<table style='width:200px;margin-top:4px;' $element>
		<tr>
			<td width=1% valign='top'>" . imgtootltip($img,'{softwares}',"$js",'softwares_mangement_text')."</td>
			<td><strong style='font-size:12px'>{softwares}</strong><br><a href=\"$js;\">{softwares_mangement_text}</a></td>
		</tr>
	</table>";	
	return $html;	
	
}

function button_security(){
	$img="48-bouclier.png";
	$js="javascript:switchDiv('section_security');";
	$element=element_rollover($js);
	$html="
	<table style='width:200px;margin-top:4px;' $element>
		<tr>
			<td width=1% valign='top'>" . imgtootltip($img,'{security}',"$js",'section_security_text')."</td>
			<td><strong style='font-size:12px'>{security}</strong><br><a href=\"$js;\">{section_security_text}</a></td>
		</tr>
	</table>";	
	return $html;	
	
	
}

function main_kaspersky_action(){
	include_once('ressources/class.kas-filter.inc');
	$kas=new kas_single();
	$html="
	<table style='width:100%'>
	<tr>
		<td align='right'><strong>{ACTION_SPAM_MODE}:</strong></td>
		<td>" . Field_array_Hash($kas->ACTION_SPAM_MODE_FIELD,'ACTION_SPAM_MODE',$kas->ACTION_SPAM_MODE)."</td>
	</tr>
	<tr>
		<td align='right'><strong>{ACTION_SPAM_SUBJECT}:</strong></td>
		<td>" . Field_text('ACTION_SPAM_SUBJECT_PREFIX',$kas->ACTION_SPAM_SUBJECT_PREFIX,'width:100%')."</td>
	<td>
	<tr><td colspan=2><hr></td></tr>
	
	
	
	<tr>
		<td align='right'><strong>{ACTION_PROBABLE_MODE}:</td>
		<td>" . Field_array_Hash($kas->ACTION_SPAM_MODE_FIELD,'ACTION_PROBABLE_MODE',$kas->ACTION_PROBABLE_MODE)."</td>
	</tr>	
	<tr>
		<td align='right'><strong>{ACTION_PROBABLE_MODE_SUBJECT}:</td>
		<td>" . Field_text('ACTION_PROBABLE_SUBJECT_PREFIX',$kas->ACTION_PROBABLE_SUBJECT_PREFIX,'width:100%')."</td>
	</tr>		
	<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:kavStep4();\" value='{build}&nbsp;&raquo;'></td></tr>
	
	
	</table>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'kas.group.rules.php');	

	
}


function main_kaspersky(){
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$page=CurrentPageName();

	$html="
	<p style='font-size:12px;font-weight:bold'>{welcome_kaspersky}</p>
	<table style='width:100%'>
	";
	
	if($users->kas_installed){
		$html=$html . "<tr>
	<td width=1%>
		" . Field_numeric_checkbox_img('enable_kasper',$users->KasxFilterEnabled,'{enable_disable}').
	"</td>
	<td>{enable_kaspersky_antispam}</td>
	</tr>";
	
	
	}
	
	if($users->KAV_MILTER_INSTALLED){
		$html=$html . "<tr>
	<td width=1%>
		" . Field_numeric_checkbox_img('enable_kav',$users->KAVMILTER_ENABLED,'{enable_disable}').
	"</td>
	<td>{enable_kaspersky_antivirus}</td>
	</tr>";
	
	
	}	
	
	$html=$html . "
	<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:kavStep2();\" value='{next}&nbsp;&raquo;'></td></tr>
	
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function main_kaspersky_level(){
		include_once('ressources/class.kas-filter.inc');
		$kas=new kas_single();
		$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
		$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->main_array["OPT_SPAM_RATE_LIMIT"]);
		
		$html="
		<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{OPT_SPAM_RATE_LIMIT}:</strong></td>
			<td valign='top'>$OPT_SPAM_RATE_LIMIT</td>
			<td valign='top'>{OPT_SPAM_RATE_LIMIT_TEXT}</td>
		<tr><td colspan=3 align='right'><input type='button' OnClick=\"javascript:kavStep3();\" value='{next}&nbsp;&raquo;'></td></tr>
		</tr>
		</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'kas.group.rules.php');		
	
	
}

function kaspersky(){
	$users=new usersMenus();
	
	if(!$users->POSTFIX_INSTALLED){return false;}
	$users->LoadModulesEnabled();
	$page=CurrentPageName();
	if($users->kas_installed OR $users->KAV_MILTER_INSTALLED){
		return LocalParagraphe("enable_kaspersky","enable_kaspersky_text","Loadjs('configure.server.php?script=enable_kasper')","bigkav24.png");
	}
	
}

function postmaster(){
$sock=new sockets();
if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return null;}	
$users=new usersMenus();
if(!$users->POSTFIX_INSTALLED){return false;}	
return LocalParagraphe("postmaster","postmaster_text","Loadjs('postfix.postmaster.php')","folder-useraliases2-48.png");	
}

function Firstwizard(){
	return LocalParagraphe("first_settings","first_settings","Loadjs('configure.server.php?script=wizard')","folder-update-48.png");	
}

function wizard_kaspersky_appliance_smtp(){
	$users=new usersMenus();
	if(!$users->KASPERSKY_SMTP_APPLIANCE){return null;}
	return LocalParagraphe("wizard_kaspersky_smtp_appliance","wizard_kaspersky_smtp_appliance_text_wizard","Loadjs('wizard.kaspersky.appliance.php')","kaspersky-wizard-48.png");
}


function samba_domain(){
	$users=new usersMenus();
	if(!$users->SAMBA_INSTALLED){return null;}
	$page=CurrentPageName();
	return LocalParagraphe("domain_controler","domain_controler_text","Loadjs('wizard.samba.domain.php?script=domain')","48-samba-pdc.png");	
	}

function clamav(){
	$page=CurrentPageName();
	return LocalParagraphe("clamav_av","clamav_av_text","Loadjs('clamav.index.php');","clamav-48.png");
	}



function nic_settings(){
	$page=CurrentPageName();
	$js="Loadjs('system.nic.config.php?js=yes')";
	$img="48-win-nic-loupe.png";
	return LocalParagraphe("nic_settings","nic_settings_text",$js,$img);
	}
	
function wizard_backup(){
$page=CurrentPageName();
	$js="Loadjs('wizard.backup-all.php')";
	$img="48-dar-index.png";
	return LocalParagraphe("manage_backups","manage_backups_text",$js,"48-dar-index.png");
	

	
}

function scancomputers(){
	$js="Loadjs('computer-browse.php')";
	$img="48-win-nic-browse.png";
	return LocalParagraphe("browse_computers","browse_computers_text",$js,$img);
	}
	
	function sharenfs(){
	$users=new usersMenus();
	if(!$users->NFS_SERVER_INSTALLED){return null;}
	$js="Loadjs('SambaBrowse.php')";
	$img="nfs-32.png";
	return LocalParagraphe("nfs_share","nfs_share_text",$js,$img);
	}

function clientnfs(){
	$users=new usersMenus();
	if(!$users->autofs_installed){return null;}
	$js="Loadjs('nfs-client.php')";
	$img="database-network-32.png";
	return LocalParagraphe("NFS_CLIENT","NFS_CLIENT_TEXT",$js,$img);
}		
	
function postfix_events(){
	$js="Loadjs('postfix-realtime-events.php')";
	$img="48-logs-view.png";
	return LocalParagraphe("postfix_realtime_events","postfix_realtime_events_text",$js,$img);
	}

function dmidecode(){
	$js="Loadjs('dmidecode.php')";
	$img="system-48.png";
	return LocalParagraphe("dmidecode","dmidecode_text",$js,$img);
	}		
function icon_update_artica(){
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){return null;}
	$js="Loadjs('artica.update.php?js=yes')";
	$img="folder-48-artica-update.png";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(LocalParagraphe("artica_autoupdate","artica_autoupdate_text",$js,$img),'system.index.php');	
	}
	
function icon_update_spamassassin_blacklist(){
	$users=new usersMenus();
	if(!$users->spamassassin_installed){return null;}
	if(!$users->AsPostfixAdministrator){return null;}
	$js="Loadjs('sa-blacklist.php')";
	$img="48-spam.png";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(LocalParagraphe("APP_SA_BLACKLIST","APP_SA_BLACKLIST_AUTOUPDATE",$js,$img),'system.index.php');	
	}	
	
function statkaspersky(){return LocalParagraphe("Kaspersky","kaspersky_av_text","YahooWin(580,'kaspersky.index.php','Kaspersky');","bigkav-48.png");}
function sysinfos(){return LocalParagraphe("sysinfos","sysinfos_text","s_PopUp('phpsysinfo/index.php',1000,600,1);","scan-48.png");}
function certificate(){return LocalParagraphe("ssl_certificate","ssl_certificate_text","Loadjs('postfix.tls.php?js-certificate=yes')","folder-lock-48.png");}
function apt(){$users=new usersMenus();if(!$users->AsDebianSystem){return null;}return LocalParagraphe("repository_manager","repository_manager_text","Loadjs('artica.repositories.php')","folder-lock-48.png");}
function incremental_backup(){$js="Loadjs('wizard.backup-all.php')";return LocalParagraphe("manage_backups","manage_backups_text",$js,"48-dar-index.png");}
function atica_perf(){$js="Loadjs('artica.performances.php')";return LocalParagraphe("artica_performances","artica_performances_text",$js,"48-perf.png");}
function applis(){return LocalParagraphe("install_applis","install_applis_text","Loadjs('setup.index.php?js=yes')","bg-applis.png");}



function LocalParagraphe($title,$text,$js,$img){
		$js=str_replace("javascript:","",$js);
		$id=md5($js);
		$img_id="{$id}_img";
	$html="
	<table style='width:198px;'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($img,"{{$text}}","$js",null,$img_id)."</td>
	<td><strong style='font-size:12px'>{{$title}}</strong><div style='font-size:11px'>{{$text}}</div></td>
	</tr>
	</table>";
	

return "<div style=\"width:200px;margin:2px\" 
	OnMouseOver=\"javascript:ParagrapheWhiteToYellow('$id',0);this.style.cursor='pointer';\" 
	OnMouseOut=\"javascript:ParagrapheWhiteToYellow('$id',1);this.style.cursor='auto'\" OnClick=\"javascript:$js\">
  <b id='{$id}_1' class=\"RLightWhite\">
  <b id='{$id}_2' class=\"RLightWhite1\"><b></b></b>
  <b id='{$id}_3' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_4' class=\"RLightWhite3\"></b>
  <b id='{$id}_5' class=\"RLightWhite4\"></b>
  <b id='{$id}_6' class=\"RLightWhite5\"></b></b>

  <div id='{$id}_0' class=\"RLightWhitefg\" style='padding:2px;'>
   $html
  </div>

  <b id='{$id}_7' class=\"RLightWhite\">
  <b id='{$id}_8' class=\"RLightWhite5\"></b>
  <b id='{$id}_9' class=\"RLightWhite4\"></b>
  <b id='{$id}_10' class=\"RLightWhite3\"></b>
  <b id='{$id}_11' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_12' class=\"RLightWhite1\"><b></b></b></b>
</div>
";		
		
	
}







function scripts_kasper(){
	$html="
	var ENABLE_KAS;
	var ENABLE_KAV;
	var OPT_SPAM_RATE_LIMIT;
	var ACTION_PROBABLE_SUBJECT_PREFIX;
	var ACTION_PROBABLE_MODE;
	var ACTION_SPAM_SUBJECT_PREFIX;
	var ACTION_SPAM_MODE;
	
	YahooWin(350,'configure.server.php?main=enable_kasper','{enable_kaspersky}');
	
	function kavStep2(){
		 if (document.getElementById('enable_kasper')){
		 	ENABLE_KAS=document.getElementById('enable_kasper').value;
		 }
		 
		 if (document.getElementById('enable_kav')){
		 	ENABLE_KAV=document.getElementById('enable_kav').value;
		 }	
		 
		 if(ENABLE_KAS==0){
		 	if(ENABLE_KAV==0){
		 		 YahooWin(450,'configure.server.php?main=kasper_save&ENABLE_KAS=0&ENABLE_KAV=0','{building_kaspersky}');
		 		 return;
		 	}
		 }
		  
		 YahooWin(450,'configure.server.php?main=kasper_level','{enable_kaspersky}');
	}
	
	function kavStep3(){
			OPT_SPAM_RATE_LIMIT=document.getElementById('OPT_SPAM_RATE_LIMIT').value;
			 YahooWin(450,'configure.server.php?main=kasper_action','{enable_kaspersky}');
		}
		
	function kavStep3(){
			OPT_SPAM_RATE_LIMIT=document.getElementById('OPT_SPAM_RATE_LIMIT').value;
			 YahooWin(450,'configure.server.php?main=kasper_action','{enable_kaspersky}');
		}

	function kavStep4(){	
		ACTION_PROBABLE_SUBJECT_PREFIX=document.getElementById('ACTION_PROBABLE_SUBJECT_PREFIX').value;
		ACTION_PROBABLE_MODE=document.getElementById('ACTION_PROBABLE_MODE').value;
		ACTION_SPAM_SUBJECT_PREFIX=document.getElementById('ACTION_SPAM_SUBJECT_PREFIX').value;
		ACTION_SPAM_MODE=document.getElementById('ACTION_SPAM_MODE').value;
		var uri='&ENABLE_KAS='+ENABLE_KAS+'&ENABLE_KAV='+ENABLE_KAV+'&OPT_SPAM_RATE_LIMIT='+OPT_SPAM_RATE_LIMIT+'&ACTION_PROBABLE_SUBJECT_PREFIX='+ACTION_PROBABLE_SUBJECT_PREFIX+'&ACTION_PROBABLE_MODE='+ACTION_PROBABLE_MODE+'&ACTION_SPAM_SUBJECT_PREFIX='+ACTION_SPAM_SUBJECT_PREFIX+'&ACTION_SPAM_MODE='+ACTION_SPAM_MODE;
		 YahooWin(450,'configure.server.php?main=kasper_save'+uri,'{building_kaspersky}');
	}
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	return $html;
	
}
function main_kaspersky_save(){
	include_once('ressources/class.artica.inc');
	include_once(dirname(__FILE__) . '/ressources/class.kavmilterd.inc');
	$ENABLE_KAS=$_GET["ENABLE_KAS"];
	$ENABLE_KAV=$_GET["ENABLE_KAV"];
	$OPT_SPAM_RATE_LIMIT=$_GET["OPT_SPAM_RATE_LIMIT"];
	$ACTION_PROBABLE_SUBJECT_PREFIX=$_GET["ACTION_PROBABLE_SUBJECT_PREFIX"];
	$ACTION_PROBABLE_MODE=$_GET["ACTION_PROBABLE_MODE"];
	$ACTION_SPAM_SUBJECT_PREFIX=$_GET["ACTION_SPAM_SUBJECT_PREFIX"];
	$ACTION_SPAM_MODE=$_GET["ACTION_SPAM_MODE"];
	
	$artica=new artica_general();
	$artica->KasxFilterEnabled=$ENABLE_KAS;
	$artica->Save();
	
	
	$milter=new kavmilterd();
	if($ENABLE_KAV==1){$ENABLE_KAV="yes";}else{$ENABLE_KAV="no";}
	$milter->milter_enabled=$ENABLE_KAV;
	$milter->SaveToLdap();
	
	$kas=new kas_single();
	if($OPT_SPAM_RATE_LIMIT<>null){
		$kas->main_array["OPT_SPAM_RATE_LIMIT"]=$OPT_SPAM_RATE_LIMIT;
		$kas->ACTION_PROBABLE_MODE=$ACTION_PROBABLE_MODE;
		$kas->ACTION_SPAM_MODE=$ACTION_SPAM_MODE;
		$kas->ACTION_PROBABLE_SUBJECT_PREFIX=$ACTION_PROBABLE_SUBJECT_PREFIX;
		$kas->ACTION_SPAM_SUBJECT_PREFIX=$ACTION_SPAM_SUBJECT_PREFIX;
		}
	$kas->Save();
	$kas->SaveToserver();
	
	$main=new main_cf();
	$main->save_conf_to_server();
	
	$html=Paragraphe('ok32.png','{success}','{success_apply_kas}');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}



function icon_system(){
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('admin.index.services.status.php?js=yes')";
	$img="rouage-48.png";
	return LocalParagraphe("manage_services","manage_services_text",$js,$img);
}

function icon_openvpn(){
	$users=new usersMenus();
	if($users->KASPERSKY_SMTP_APPLIANCE){return null;}
	if(!$users->AsAnAdministratorGeneric){return null;}
	if(!$users->OPENVPN_INSTALLED){return null;}
	$js="Loadjs('index.openvpn.php')";
	$img="42-openvpn.png";
	return LocalParagraphe("APP_OPENVPN","APP_OPENVPN_TEXT",$js,$img);	
	}
	
	function icon_externalports(){
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){return null;}
	
	$js="Loadjs('external-ports.php')";
	$img="48-bind.png";
	return LocalParagraphe("EXTERNAL_PORTS","EXTERNAL_PORTS_TEXT",$js,$img);	
	}	

function icon_memory(){
$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('system.memory.php?js=yes')";
	$img="48-memory.png";
	return LocalParagraphe("system_memory","system_memory_text",$js,$img);	
	}
	
function icon_harddrive(){
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('system.internal.disks.php')";
	$img="48-hd.png";
	return LocalParagraphe("internal_hard_drives","internal_hard_drives_text",$js,$img);	
	}	
	
function icon_amavisdnew(){
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){return null;}
	$js="Loadjs('amavis.index.php?ajax=yes');";
	$img="42-amavis.png";
	return LocalParagraphe("APP_AMAVISD_NEW","APP_AMAVISD_NEW_TEXT",$js,$img);	
}	
	
	
function icon_adduser(){
	$users=new usersMenus();
	$sock=new sockets();
	if(!$users->AllowAddUsers){return null;}
	if($users->ARTICA_META_ENABLED){
		if($sock->GET_INFO("AllowArticaMetaAddUsers")<>1){return null;}
	}
	$js="Loadjs('create-user.php');";
	$img="identity-add-48.png";
	return LocalParagraphe("add_user","add user explain",$js,$img);			
}
	
function icon_troubleshoot(){
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){return null;}
	$js="Loadjs('index.troubleshoot.php');";
	$img="48-troubleshoots.png";
	return LocalParagraphe("troubleshoot","troubleshoot_explain",$js,$img);				
		
}
	
function icon_update_clamav(){
	$users=new usersMenus();
	if(!$users->CLAMAV_INSTALLED){return null;}
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('clamav.update.php');";
	$img="clamav-update-48.png";
	return LocalParagraphe("UPDATE_CLAMAV","UPDATE_CLAMAV_EXPLAIN",$js,$img);				
}	
	
function icon_view_queue(){
	$js="Loadjs('postfix.queue.monitoring.php?js')";
	$img="48-bg_addresses.png";
	return LocalParagraphe("queue_monitoring","queue_monitoring_text",$js,$img);		
}

function icon_events(){
	$img="info-48.png";
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){return null;}	
	$js="Loadjs('artica.events.php')";
	return LocalParagraphe("artica_events","artica_events_text",$js,$img);
	}

function samba_links(){
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){return null;}
	if(!$users->SAMBA_INSTALLED){return null;}
	
	$html="<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/alias-32.gif'></td>
	<td><strong style='font-size:12px'>Samba: {usefull_links}</strong></td>
	</tr>
	<tr>
	<td colspan=2 style='padding-left:20px'>
		<table style='width:100%'>
		<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td>
				<a href='smb-audit/index.php' target=_new><strong style='font-size:11px'>{samba_audit}</strong></a>
				<p class=caption>{samba_audit_text}</p>
			</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>	
	";
	return $html;
}

function postfix_reports(){
	$users=new usersMenus();
	if(!$users->PFLOGSUMM_INSTALLED){return false;}
	if(!$users->AsPostfixAdministrator){return false;}
	return LocalParagraphe('postfix_smtp_reports','postfix_smtp_reports_text',"Loadjs('index.pflogsumm.php')","42-milterspy.png");
	
}


function Postfix_links(){
	
	$users=new usersMenus();
	
	if(!$users->POSTFIX_INSTALLED){return false;}	
	
	if($users->roundcube_installed){
		$roundcube="
		<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td>
				<a href='#' OnClick=\"javascript:Loadjs('roundcube.index.php?script=yes')\"'><strong style='font-size:11px'>{APP_ROUNDCUBE}</strong></a>
				<p class=caption>{APP_ROUNDCUBE_TEXT}</p>
			</td>			
		</tr>
		";
		
	}
	
	$html="
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/alias-32.gif'></td>
	<td><strong style='font-size:12px'>Postfix: {usefull_links}</strong></td>
	</tr>
	<tr>
	<td colspan=2 style='padding-left:20px'>
		<table style='width:100%'>
		<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td>
				<a href=\"javascript:Loadjs('postfix.network.php?ajax=yes');\"><strong style='font-size:11px'>{postfix_network}</strong></a>
				<p class=caption>{postfix_network_text}</p>
			</td>
		</tr>
		<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td>
				<a href='#' OnClick=\"javascript:Loadjs('postfix.plugins.php?js=yes')\"><strong style='font-size:11px'>{POSTFIX_PLUGINS}</strong></a>
				<p class=caption>{postfix_plugins_text}</p>
			</td>			
		</tr>
	$roundcube
	</table>
	</td>
	</tr>
		
	</table>";
	
	return $html;
}

function fetchmail(){
	$users=new usersMenus();
	if(!$users->fetchmail_installed){return null;}
	return LocalParagraphe("play_with_isp","play_with_isp","Loadjs('configure.server.php?script=fetchmail_script')","home-48.png");
}



function main_postmaster(){
	$user=new usersMenus();
	$servername=$user->hostname;
	
	
	if(isset($_GET["postmaster_mail"])){
		if($_GET["postmaster_mail"]==null){
			echo main_result(true,'No mailbox set','{postmaster}');
			exit;
		}
			include_once('ressources/class.user.inc');
			$mail=$_GET["postmaster_mail"];
			$ldap=new clladp();
			
			$uid=$ldap->uid_from_email($mail);
			writelogs("$mail has uid $uid",__FUNCTION__,__FILE__);
			$users=new user($uid);
			$users->mail=$mail;
			if(!$users->add_user()){
				echo main_result(true,$users->ldap_error,'{postmaster}');
				exit;
			}
			
			if(!$users->alias_add("postmaster@$servername")){
				echo main_result(true,"postmaster@$servername:$users->ldap_error",'{postmaster}');
				exit;
			}
			if(!$users->alias_add("root@$servername")){
				echo main_result(true,"root@$servername:$users->ldap_error",'{postmaster}');
				exit;
			}
			
				include_once("ressources/class.main_cf.inc");
				$main=new main_cf();
				$main->main_array["double_bounce_sender"]=$mail;
				$main->main_array["address_verify_sender"]=$mail;
				$main->main_array["2bounce_notice_recipient"]=$mail;
				$main->main_array["error_notice_recipient"]=$mail;
				$main->main_array["delay_notice_recipient"]=$mail;
				$main->main_array["empty_address_recipient"]=$mail;
				$main->save_conf();		
				$main->save_conf_to_server();	
				
				$amavis=new amavis();
				$amavis->main_array["BEHAVIORS"]["virus_admin"]=$mail;
				$amavis->main_array["BEHAVIORS"]["mailfrom_notify_admin"]=$mail;
				$amavis->main_array["BEHAVIORS"]["mailfrom_notify_recip"]=$mail;
				$amavis->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]=$mail;
				$amavis->Save();
				$amavis->SaveToServer();
				
			
			echo main_result(false,"<br>root@$servername<br>postmaster@$servername<br>{success}",'{postmaster}');
			return null;
		}
	
	
	$postmasters="postmaster@$servername,root@$servername";
	
	$html="
	<strong>{postmaster}</strong>
	<p class=caption>{postmaster_explain}&nbsp;$postmasters</p>
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{local_mail}:</strong></td>
			<td valign='top'>" . Field_text('postmaster_mail',$_GET["postmaster_mail"],'width:100%')."</td>
		</tr>
					
		<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:postmaster1();\" value='{build}&nbsp;&raquo;'></td></tr>
		</tr>
		</table>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function main_postmaster_script(){
	
	$html="
	var postmaster_mail='';
	YahooWin(450,'configure.server.php?main=postmaster','{postmaster}');
	
	
	function postmaster1(){
		postmaster_mail=document.getElementById('postmaster_mail').value;
		YahooWin(450,'configure.server.php?main=postmaster&postmaster_mail='+postmaster_mail,'{postmaster}');
	
	}
	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function main_fetchmail_script(){
	
	$html="
	var isp_address_mail='';
	var isp_pop3_server='';
	var isp_smtp_server='';
	
	var isp_account='';
	var isp_password='';
	var local_email='';
	var local_password='';
	
	var isp_smtp_account='';
	var isp_smtp_password='';
	
	var relay_server='';
	
	YahooWin(450,'configure.server.php?main=fetchmail','{play_with_isp}');
	
	function fetchStep0(){
		var uri=buildquery();
		YahooWin(450,'configure.server.php?main=fetchmail'+uri,'{play_with_isp}');
	
	}
	
	function buildquery(){
	var b='&local_email='+local_email+'&isp_address_mail='+isp_address_mail+'&isp_pop3_server='+isp_pop3_server+'&isp_smtp_server='+isp_smtp_server;
	b=b+'&isp_account='+isp_account+'&isp_password='+isp_password+'&local_password='+local_password+'&isp_smtp_account='+isp_smtp_account+'&isp_smtp_password='+isp_smtp_password;
	return b;
	}
	
	
	function fetchStep1(){
		
	  if(document.getElementById('isp_address_mail')){
		isp_address_mail=document.getElementById('isp_address_mail').value;
		isp_pop3_server=document.getElementById('isp_pop3_server').value;
		isp_smtp_server=document.getElementById('isp_smtp_server').value;
	  	}
	  	var uri=buildquery();
		 YahooWin(450,'configure.server.php?main=main_fetchmail_1'+uri,'{accounts}');
	}
	
	function fetchStep2(){
		if(document.getElementById('isp_account')){
			isp_account=document.getElementById('isp_account').value;
			isp_password=document.getElementById('isp_password').value;
			local_email=document.getElementById('local_email').value;
			local_password=document.getElementById('local_password').value;
		}
		var uri=buildquery();
		
		YahooWin(450,'configure.server.php?main=main_fetchmail_2'+uri,'{send_auth}');
	}
	
	function fetchStep3(){
		if(document.getElementById('isp_account')){
			isp_smtp_account=document.getElementById('isp_account').value;
			isp_smtp_password=document.getElementById('isp_password').value;
		}
	
		var uri=buildquery();
		YahooWin(450,'configure.server.php?main=main_fetchmail_3'+uri,'{relay}');
	}
	
	function fetchStep4(){
		if(document.getElementById('relay_server')){
			relay_server=document.getElementById('relay_server').value;
		}
		
		var uri=buildquery();
		YahooWin(450,'configure.server.php?main=main_fetchmail_build'+uri,'{relay}');
	
	}
	
	
	";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);

	
}

function main_fetchmail(){
	
	$html="
	<strong>{play_with_isp_text}</strong>
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_address_mail}:</strong></td>
			<td valign='top'>" . Field_text('isp_address_mail',$_GET["isp_address_mail"],'width:100%')."</td>
		</tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_pop3_server}:</strong></td>
			<td valign='top'>" . Field_text('isp_pop3_server',$_GET["isp_pop3_server"],'width:100%')."</td>
		</tr>	
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_smtp_server}:</strong></td>
			<td valign='top'>" . Field_text('isp_smtp_server',$_GET["isp_smtp_server"],'width:100%')."</td>
		</tr>				
		<tr><td colspan=3 align='right'><input type='button' OnClick=\"javascript:fetchStep1();\" value='{next}&nbsp;&raquo;'></td></tr>
		</tr>
		</table>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_fetchmail_1(){
	
	
	$tb=explode('@',$_GET["isp_address_mail"]);
	
	$html="
	<strong>{play_with_isp_text}</strong>
	<H5>{remote_account}</H5>
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_account}:</strong></td>
			<td valign='top'>" . Field_text('isp_account',$tb[0],'width:100%')."</td>
		</tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_password}:</strong></td>
			<td valign='top'>" . Field_password('isp_password',$_GET["isp_password"],'width:100%')."</td>
		</tr>	
		<tr><td colspan=2 align='left'><H5>{local_account}</H5></td></tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{local_email}:</strong></td>
			<td valign='top'>" . Field_text('local_email',$_GET["local_email"],'width:100%')."</td>
		</tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{local_password}:</strong></td>
			<td valign='top'>" . Field_password('local_password',$_GET["local_password"],'width:100%')."</td>
		</tr>		
					
		<tr>
			<td align='left'><input type='button' OnClick=\"javascript:fetchStep0();\" value='&laquo;&nbsp;{back}'></td>
			<td align='right'><input type='button' OnClick=\"javascript:fetchStep2();\" value='{next}&nbsp;&raquo;'></td>
		</tr>
		</tr>
		</table>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function main_fetchmail_2(){
	
	$tb=explode('@',$_GET["isp_address_mail"]);
	
	$html="
	<strong>{play_with_isp_text}</strong>
	<H5>{send_auth}:smtp:{$_GET["isp_smtp_server"]}</H5>
	<p class=caption>{send_auth_text}</p>
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_account}:</strong></td>
			<td valign='top'>" . Field_text('isp_account',$_GET["isp_account"],'width:100%')."</td>
		</tr>
		<tr>
			<td align='right' nowrap valign='top'><strong>{isp_password}:</strong></td>
			<td valign='top'>" . Field_password('isp_password',$_GET["isp_password"],'width:100%')."</td>
		</tr>	
		
		<tr>
			<td align='left'><input type='button' OnClick=\"javascript:fetchStep1();\" value='&laquo;&nbsp;{back}'></td>
			<td align='right'><input type='button' OnClick=\"javascript:fetchStep3();\" value='{next}&nbsp;&raquo;'></td>
		</tr>
		</tr>
		</table>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function main_fetchmail_3(){
	$tb=explode('@',$_GET["isp_address_mail"]);
	
	$html="
	<strong>{play_with_isp_text}</strong>
	<H5>{relay}:{$_GET["local_email"]}</H5>
	<p class=caption>{relay_mail}</p>
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{relay_server}:</strong></td>
			<td valign='top'>" . Field_text('relay_server',$_GET["relay_server"],'width:100%')."</td>
		</tr>
		<tr>
			<td align='left'><input type='button' OnClick=\"javascript:fetchStep3();\" value='&laquo;&nbsp;{back}'></td>
			<td align='right'><input type='button' OnClick=\"javascript:fetchStep4();\" value='{build}&nbsp;&raquo;'></td>
		</tr>
		</tr>
		</table>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function main_fetchmail_build(){
	include_once('ressources/class.user.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.fetchmail.inc');
	
	
$failed=false;	
	
$isp_address_mail=$_GET["isp_address_mail"];
$isp_pop3_server=$_GET["isp_pop3_server"];
$isp_smtp_server=$_GET["isp_smtp_server"];
	
$isp_account=$_GET["isp_account"];
$isp_password=$_GET["isp_password"];
$local_email=$_GET["local_email"];
$local_password=$_GET["local_password"];
	
$isp_smtp_account=$_GET["isp_smtp_account"];
$isp_smtp_password=$_GET["isp_smtp_password"];
	
$relay_server=$_GET["relay_server"];


if($local_email==null){
	echo main_fetchmail_build_results(true,'local mail (False)');
	exit;
}

$ldap=new clladp();
writelogs("i try to found if user exists",__FUNCTION__,__FILE__);
$uid=$ldap->uid_from_email($local_email);
if($uid<>null){
	$user=new user($local_email);
	$ou=$user->ou;
}else{
	writelogs("no user found, create it",__FUNCTION__,__FILE__);
	$tb=explode("@",$local_email);
	$local_domain=$tb[1];  
	$user=new user($tb[0]);
	$ou=$ldap->ou_by_smtp_domain($local_domain);
	if($ou==null){
		$ou=$local_domain;
		writelogs("Adding new organization $ou",__FUNCTION__,__FILE__);
		$ldap->AddOrganization($ou);
	}	
	
}


	
	writelogs("Creating user",__FUNCTION__,__FILE__);
	$user=new user($local_email);
	$user->mail=$local_email;
	$user->password=$local_password;
	$user->ou=$ou;
	$user->SenderCanonical=$isp_address_mail;
	if(!$user->add_user()){
		echo main_fetchmail_build_results(true,$user->ldap_error);
		exit;
	}
	
	
	if($isp_smtp_account<>null){
		writelogs("Creating SMTP authentification for $isp_smtp_server width $isp_smtp_account",__FUNCTION__,__FILE__);
		$sasl=new smtp_sasl_password_maps();
		$sasl->add($isp_address_mail,$isp_smtp_account,$isp_password);
		$main=new main_cf();
		writelogs("Enable sasl engine in postfix",__FUNCTION__,__FILE__);
		$main->smtp_sasl_password_maps_enable_2();		
		
	}
	writelogs("Creating sender_dependent_relayhost_maps -> $isp_smtp_server",__FUNCTION__,__FILE__);
	$sender=new sender_dependent_relayhost_maps();
	if(!$sender->Add($isp_address_mail,$isp_smtp_server)){
		echo main_fetchmail_build_results(true,"sender_dependent_relayhost_maps:$sender->ldap_error");
		exit;
	}
	

	$fetchmail=new Fetchmail_settings();
	$array["poll"]=$isp_pop3_server;
	$array["proto"]="auto";
	$array["keep"]="yes";
	$array["user"]=$isp_account;
	$array["pass"]=$isp_password;
	$array["is"]=$local_email;
	$array["fetchall"]="yes";
	$line=$fetchmail->compile($array);
	if(!$user->fetchmail_add_rule($line)){
		echo main_fetchmail_build_results(true,"fetchmail rule:$user->ldap_error");
		exit;
	}
	
	$relay=new Routing($ou);
	if($relay_server<>null){
		if(!$relay->create_relay_server($local_domain,$relay_server,$ou)){
				echo main_fetchmail_build_results(true,"relay:$relay->ldap_error");
			}
		}else{
			if(!$relay->create_localdomain($ou,$local_domain)){
				echo main_fetchmail_build_results(true,"local domain:$relay->ldap_error");
			}
		}

	
	$fetchmail=new fetchmail();
	$fetchmail->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
	
	
	
	$info="<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td nowrap align='right'><strong>{organization}</strong>:</td>
		<td nowrap><strong>$ou</strong></td>
	</tr>
	<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td nowrap align='right'><strong>{local_mail}</strong>:</td>
		<td nowrap><strong>$local_email</strong></td>
	</tr>
	<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td nowrap align='right'><strong>{isp_address_mail}</strong>:</td>
		<td nowrap><strong>$isp_address_mail</strong></td>
	</tr>			
	</table>	
	";
	
	echo main_fetchmail_build_results(false,$info);
	
	
}

function main_fetchmail_build_results($failed=false,$info){
	$tpl=new templates();
	$bottom="
		<table style='width:100%'>
		<tr>
			<td align='right'><input type='button' OnClick=\"javascript:fetchStep3();\" value='&laquo;&nbsp;{back}'></td>
		</tr>
		</table>";
	
	if(!$failed){
		return $tpl->_ENGINE_parse_body(Paragraphe('ok32.png','{success}',"<br><strong style='color:black'>$info</strong>").$bottom);
	}else{
		return $tpl->_ENGINE_parse_body(Paragraphe('danger32.png','{failed}',"{failed_fetchmail}<br><strong style='color:red'>$info</strong>").$bottom);
	}

}

function main_result($failed=false,$info,$title){
	$tpl=new templates();
if(!$failed){
		return $tpl->_ENGINE_parse_body(Paragraphe('ok32.png','{success}',"<br><strong style='color:black'>$info</strong>").$bottom);
	}else{
		return $tpl->_ENGINE_parse_body(Paragraphe('danger32.png','{failed}',"$title<br><strong style='color:red'>$info</strong>").$bottom);
	}	
}




?>