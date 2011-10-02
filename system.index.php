<?php
$GLOBALS["ICON_FAMILY"]="SYSTEM";
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.users.menus.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{header('location:users.index.php');exit;}		
	
	if($_GET["newtab"]=="network"){network_js();exit;}
	if(isset($_GET["tab"])){main_switch();exit;}
	if(isset($_GET["ajaxmenu"])){echo popup();exit;}
	if(isset($_GET["js"])){main_ajax();exit;}
	if(isset($_GET["AdressBookPopup"])){echo AdressBookPopup();exit;}
	if(isset($_GET["EnableRemoteAddressBook"])){AdressBookPopup_save();exit;}

	
	
	
page();

function network_js(){
	$page=CurrentPageName();
	$html="LoadAjax('BodyContent','$page?tab=network&newinterface=yes');";
	echo $html;
}

function page(){
$page=CurrentPageName();	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{header('location:users.index.php');exit;}	
$sys=new systeminfos();

$distri=$sys->ditribution_name;


$html="
<div class=caption>Distribution: <strong>$distri</strong>&nbsp;Kernel:&nbsp;$sys->kernel_version&nbsp;LIBC:&nbsp;$sys->libc_version</div>
<table style='width:600px' align=center>
<tr>
<td width=1% valign='top'>
	<table>
		<tr>
			<td width=1% valign='top'>
				<img src='img/system.jpg'>
			</td>
			<td valign='top'>
				<table style='width:100%'>
					<tr><td valign='top'>  ".Paragraphe('folder-tasks-64.jpg','{system_tasks}','{system_tasks_text}','system.tasks.settings.php') ."</td></tr>
					<tr><td valign='top' >".Paragraphe('folder-network-64.jpg','{nic_infos}','{nic_infos_text}','system.nic.config.php') ."</td></tr>
				</table>
			</td>
		</tr>
	</table>
</td>
</tr>
</table>
	
	
	
<script>
".add_script()."
</script>

";
$tpl=new template_users('System',$html);
echo $tpl->web_page;
}

function popup(){
	
	$html=main_tab();
	
	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	echo $html;
	
}

function add_script(){
	$page=CurrentPageName();
	$html="
	 function AdressBookPopup(){
 		YahooWin(600,'$page?AdressBookPopup=yes');
 	}
 
function x_AddressBookSave(obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){
                alert(tempvalue);
	}
	AdressBookPopup();
}
	
 
 function AddressBookSave(){
   if(!document.getElementById('EnableRemoteAddressBook')){
   	return false;
   }
   
if(!document.getElementById('EnablePerUserRemoteAddressBook')){
   	return false;
   }   
   
   
   
   
   
    var XHR = new XHRConnection();
	XHR.appendData('EnableRemoteAddressBook',document.getElementById('EnableRemoteAddressBook').value);
	XHR.appendData('EnablePerUserRemoteAddressBook',document.getElementById('EnablePerUserRemoteAddressBook').value);
	XHR.appendData('EnableNonEncryptedLdapSession',document.getElementById('EnableNonEncryptedLdapSession').value);
	document.getElementById('rdr').innerHTML=\"<center style='width:400px'><img src='img/wait_verybig.gif'></center>\";
	XHR.sendAndLoad('$page', 'GET',x_AddressBookSave);
 
 }

	";
	
	return $html;
	
	
}


function main_ajax(){
	
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
	$adds=add_script();
	$page=CurrentPageName();
	$sys=new systeminfos();
	$distri=$sys->ditribution_name;	
	$sys->libc_version=trim($sys->libc_version);
	$sys->kernel_version=trim($sys->kernel_version);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{system}");
	
	
	$html="
	$adds
	
	function LoadSystem(){
		$('#BodyContent').load('$page?ajaxmenu=yes');
		//YahooWinS(750,'$page?ajaxmenu=yes','$title:: $distri&nbsp;Kernel:&nbsp;$sys->kernel_version&nbsp;LIBC:&nbsp;$sys->libc_version');
		//setTimeout(\"LoadSystemBack()\",900);
	}
	
	function LoadSystemBack(){
		$back
		YahooSetupControlHide();
	}
	
	LoadSystem();
	";
	
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
	
}


function main_switch(){
	
	switch ($_GET["tab"]) {
		case "system":main_system();exit;break;
		case "network":main_network();exit;break;
		case "services":main_services();exit;break;
		case "dns":main_dns();exit;break;
		case "upd":main_update();exit;break;
		default:main_system();exit;break;
			
	}
	
}


function main_tab(){
	$tpl=new templates();
	if($_GET["tab"]==null){$_GET["tab"]="system";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["system"]=$tpl->_ENGINE_parse_body('{system}');
	$array["network"]=$tpl->_ENGINE_parse_body('{network}');
	$array["dns"]=$tpl->_ENGINE_parse_body('{dns}');
	$array["services"]=$tpl->_ENGINE_parse_body('{services}');
	$array["upd"]=$tpl->_ENGINE_parse_body('{update}');
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		if(strlen($ligne)>28){
			$ligne=texttooltip(substr($ligne,0,25)."...",$ligne,null,null,1);
		}
		
		$html[]= "<li><a href=\"$page?tab=$num&hostname=$hostname\"><span>$ligne</span></a></li>\n";
		
		//$html=$html . "<li><a href=\"javascript:LoadAjax('main_system_settings','$page?tab=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=main_system_settings style='width:100%;height:700px;overflow:auto;background-color:white;'>
				<ul>". implode("\n",$html)."</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#main_system_settings').tabs();
			

			});
		</script>";			
	
	
	
}

function main_system(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$users=new usersMenus();
	$automount=Buildicon64("DEF_ICO_AUTOFS_CENTER");
	$disks=Buildicon64("DEF_ICO_DISKS");
	$ntp=Buildicon64('DEF_ICO_NTP');
	$hardware=Paragraphe('folder-hardware-64.png','{hardware_info}','{hardware_info_text}',"javascript:Loadjs('system.hardware.php')");
	$memory=Paragraphe('folder-memory-64.png','{memory_info}','{memory_info_text}',"javascript:Loadjs('system.memory.php?js=yes')");
	$proc_infos=Buildicon64("DEF_ICO_PROC_INFOS");
	$philesight=Buildicon64('DEF_ICON_PHILESIGHT');
	$mysql=Buildicon64('DEF_ICO_MYSQL');
	$usb=Buildicon64('DEF_ICO_DEVCONTROL');
	$perfs=Paragraphe('64-performances.png','{artica_performances}','{artica_performances_text}',"javascript:Loadjs('artica.performances.php')");
	$zabix=Buildicon64("DEF_ICO_ZABBIX");
	$kernel=Paragraphe("linux-inside-64.png","{system_kernel}","{system_kernel_text}","javascript:Loadjs('system.kernel.debian.php')");
	$clock=Paragraphe("clock-gold-64.png","{server_time2}","{server_time2_text}","javascript:Loadjs('index.time.php?settings=yes');");
	$syslog=Paragraphe("syslog-64.png","{system_log}","{system_log_text}","javascript:Loadjs('syslog.engine.php?windows=yes');");
	$automount=Paragraphe("magneto-64.png","{automount_center}","{automount_center_text}","javascript:Loadjs('autofs.php?windows=yes');");
	
	$RootPasswordChangedTXT=Paragraphe('cop-lock-64.png',
		"{root_password_not_changed}",
		"{root_password_not_changed_text}",
		"javascript:Loadjs('system.root.pwd.php')",
		"{root_password_not_changed_text}");

	if(!$users->autofs_installed){
		$automount=Paragraphe("magneto-64-grey.png","{automount_center}","{automount_center_text}","");
	}
	
	if(!$users->deduplication_installed){$img_dedup="deduplication-64-grey.png";}else{$img_dedup="deduplication-64.png";}
	
	$File_Deduplication=Paragraphe($img_dedup,"{file_deduplication}","{file_deduplication_text}","javascript:Loadjs('system.file.deduplication.php')");
	
	if(($users->LinuxDistriCode<>"DEBIAN") && ($users->LinuxDistriCode<>"UBUNTU")){$kernel=null;}
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		$File_Deduplication=null;
		$zabix=null;
	}
	
	if(isset($_GET["newinterface"])){
		
	}
		
	if(!$users->ZABBIX_INSTALLED){$zabix=null;}
	$tr[]=$ntp;
	$tr[]=$clock;
	$tr[]=$hardware;
	$tr[]=$memory;
	$tr[]=$proc_infos;
	$tr[]=$kernel;
	$tr[]=$syslog;
	$tr[]=$philesight;
	$tr[]=$zabix;
	$tr[]=$perfs;
	$tr[]=$automount;
	$tr[]=$disks;
	$tr[]=$File_Deduplication;
	$tr[]=$usb;
	$tr[]=$RootPasswordChangedTXT;

	
$tables[]="<table style='width:100%;margin-top:10px'><tbody><tr>";
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
				
$tables[]="</tbody></table>";	
	
	$html=implode("\n",$tables);

	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}
	
function main_network(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return;}
	$users=new usersMenus();
	$fw=Paragraphe('folder-64-firewall-grey.png','{APP_IPTABLES}','{error_app_not_installed_disabled}','','error_app_not_installed_disabled');
	$nmap=Paragraphe('folder-64-nmap-grey.png','{APP_NMAP}','{error_app_not_installed_disabled}','','error_app_not_installed_disabled');
	$network=Paragraphe('network-connection2.png','{net_settings}','{net_settings_text}',"javascript:Loadjs('system.nic.config.php?js=yes')",'net_settings_text');
	$pdns=Buildicon64('DEF_ICO_PDNS');
	$crossroads=Paragraphe('load-blancing-64-grey.png','{APP_CROSSROADS}','{load_balancing_intro_text}',"");
	
	
	
	if($users->IPTABLES_INSTALLED){
			$fw=Paragraphe('folder-64-firewall.png','{APP_IPTABLES}','{APP_IPTABLES_TEXT}','iptables.index.php');
			}	
			
	if($users->HAMACHI_INSTALLED){
		$hamachi=Paragraphe('logmein_logo-64.gif','{APP_AMACHI}','{APP_AMACHI_TEXT}',"javascript:Loadjs('hamachi.php')");
	}
	
	
			
	if($users->nmap_installed){
			$nmap=Paragraphe('folder-64-nmap.png','{APP_NMAP}','{APP_NMAP_TEXT}',"javascript:Loadjs('nmap.index.php')");
			}

	$gateway=Paragraphe('relayhost.png','{APP_ARTICA_GAYTEWAY}','{APP_ARTICA_GAYTEWAY_TEXT}',"javascript:Loadjs('index.gateway.php?script=yes')");	
	$dhcp=Buildicon64('DEF_ICO_DHCP');
	
	if($users->OPENVPN_INSTALLED){
		$openvpn=Buildicon64('DEF_ICO_OPENVPN');
	}
	
	
	$EmergingThreats=Paragraphe('emerging-threads-64-grey.png','{EmergingThreats}','{EmergingThreats_text}');
	if($users->IPSET_INSTALLED){
		$EmergingThreats=Paragraphe('emerging-threads-64.png','{EmergingThreats}',
		'{EmergingThreats_text}',"javascript:Loadjs('system.EmergingThreats.php')");	
	}
	
	$IpBlocksA=Paragraphe('ipblock-64-grey.png','{block_countries}','{ipblocks_text}');
	if($users->IPSET_INSTALLED){
		$IpBlocksA=Paragraphe('ipblock-64.png','{block_countries}',
		'{ipblocks_text}',"javascript:Loadjs('system.ipblock.php')");	
	}	
	

	
	
	if(!$user->POWER_DNS_INSTALLED){$pdns=Paragraphe('dns-64-grey.png','{APP_PDNS}','{APP_PDNS_TEXT}');}
	

	
	if($users->KASPERSKY_SMTP_APPLIANCE){
		$openvpn=null;
		$dhcp=null;
		$nmap=null;
		$fw=null;
		$gateway=null;
	}
	
	
	
	if($user->crossroads_installed){
		$crossroads=Paragraphe('load-blancing-64.png','{APP_CROSSROADS}','{load_balancing_intro_text}',
		"javascript:Loadjs('crossroads.index.php')");
	}	
	
	$sock=new sockets();
	$EnableQOSInterface=$sock->GET_INFO("EnableQOSInterface");
	if($EnableQOSInterface==null){$EnableQOSInterface=1;}
	
	if($sock->GET_INFO("EnableQOSInterface")==1){
		$qos=Paragraphe("qos-64.png","{Q.O.S}","{qos_intro}","javascript:Loadjs('qos.php')");
		if(!$users->qos_tools_installed){
			$qos=Paragraphe("qos-64-grey.png","{Q.O.S}","{qos_intro}","");
		}		
	}
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		$dhcp=null;
		$EmergingThreats=null;
		$fw=null;
		$nmap=null;
		$openvpn=null;
		$pdns=null;
		$crossroads=null;
	}
	
	if(isset($_GET["newinterface"])){$network=null;$openvpn=null;}	
	
	
	$tr[]=$network;
	$tr[]=$gateway;
	$tr[]=$dhcp;
	$tr[]=$qos;
	$tr[]=$pdns;
	$tr[]=$openvpn;
	$tr[]=$nmap;
	$tr[]=$crossroads;
	$tr[]=$fw;
	$tr[]=$EmergingThreats;
	$tr[]=$IpBlocksA;	
	
	
	if(isset($_GET["newinterface"])){
		$network=null;$openvpn=null;
		$static=Paragraphe('folder-64-dns-grey.png','{nic_static_dns}','{nic_static_dns_text}','');
		$bind9=ICON_BIND9();
		$etc_hosts=Buildicon64("DEF_ICO_ETC_HOSTS");
		$pdns=Buildicon64('DEF_ICO_PDNS');
		$dyndns=Paragraphe('folder-64-dyndns.png','{nic_dynamic_dns}','{nic_dynamic_dns_text}','system.nic.dynamicdns.php');
		$rbl_check=Paragraphe('check-64.png','{rbl_check_artica}','{rbl_check_artica_text}',"javascript:Loadjs('system.rbl.check.php')");
		$dnsmasq=Paragraphe('dns-64.png','{APP_DNSMASQ}','{APP_DNSMASQ_TEXT}',"javascript:Loadjs('dnsmasq.index.php')");
		
	
		
		if(!$user->BIND9_INSTALLED){$static=null;$bind9=null;}	
		if(!$user->POWER_DNS_INSTALLED){$pdns=Paragraphe("dns-64-grey.png","{APP_PDNS}","{APP_PDNS_TEXT}");}
		if(!$user->dnsmasq_installed){$dnsmasq=Paragraphe("dns-64-grey.png","{APP_DNSMASQ}",'{APP_DNSMASQ_TEXT}');}
		if($user->KASPERSKY_SMTP_APPLIANCE){$dyndns=null;}
		if($user->KASPERSKY_WEB_APPLIANCE){$bind9=null;$pdns=null;$dyndns=null;}
	
		$tr[]=$pdns;
		$tr[]=$dnsmasq;
		$tr[]=$bind9;
		$tr[]=$static;
		$tr[]=$dyndns;
		$tr[]=$etc_hosts;
		$tr[]=$rbl_check;		
		
	}	


	
	$tables[]="<center><table style='width:70%;margin-top:10px'><tbody><tr>";
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
					
	$tables[]="</tbody></table></center>";	
	
	$html=implode("\n",$tables);	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}

function main_services(){
	$sock=new sockets();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if($users->DOTCLEAR_INSTALLED){
		$dotclear=Paragraphe('64-dotclear.png','{APP_DOTCLEAR}','{APP_DOTCLEAR_TEXT}','dotclear.index.php','APP_DOTCLEAR_TEXT');
	}
	
	if($users->OBM2_INSTALLED){
		$obm2=Paragraphe('64-obm2.png','{APP_OBM2}','{APP_OBM2_TEXT}',"javascript:Loadjs('obm2.index.php')",'APP_OBM2_TEXT');
	}
		
	
	if($users->openldap_installed){
		$addressbook=Paragraphe("64-addressbook.png","{remote_addressbook}","{remote_addressbook_text}","javascript:AdressBookPopup();");
	}
	
	if($users->XAPIAN_PHP_INSTALLED){
		$instantsearch=Paragraphe("64-xapian.png","{InstantSearch}","{InstantSearch_text}","javascript:Loadjs('instantsearch.php');");
	}

	if($users->OPENGOO_INSTALLED){
		$opengoo=Paragraphe("64-opengoo.png","{APP_OPENGOO}","{APP_OPENGOO_TEXT}","javascript:Loadjs('opengoo.php');");
	}		

	if($users->phpldapadmin_installed){
		$phpldapadmin=Paragraphe('phpldap-admin-64.png','{APP_PHPLDAPADMIN}','{APP_PHPLDAPADMIN_TEXT}',"javascript:s_PopUpFull('ldap/index.php',1024,800);","{artica_events_text}");
	}	
	
	if($users->MLDONKEY_INSTALLED){
		$mldonkey=Paragraphe('64-emule.png','{APP_MLDONKEY}','{APP_MLDONKEY_TEXT}',"javascript:Loadjs('mldonkey.php')","");
	}
	
	if($users->KAV4FS_INSTALLED){
		$kav4fs=Paragraphe('bigkav-64.png','{APP_KAV4FS}','{APP_KAV4FS_TEXT}',"javascript:Loadjs('kav4fs.php')","");
		
		
	}
	
	$massmailing=Paragraphe('mass-mailing-64.png','{email_campaigns}','{APP_MASSMAILING_ENABLE_TEXT}',"javascript:Loadjs('system.enable.massmailing.php');","{APP_MASSMAILING_ENABLE_TEXT}");
	$userautofill=Paragraphe('member-add-64.png','{auto_account}','{auto_account_text}',"javascript:Loadjs('auto-account.php?script=yes')",'auto_account_text');
	$installed_applis=Paragraphe('folder-applications-64.jpg','{installed_applications}','{installed_applications_text}','system.applications.php','installed_applications_text');
	$add_remove=Paragraphe('add-remove-64.png','{application_setup}','{application_setup_txt}',"javascript:Loadjs('setup.index.php?js=yes')");
	$services=Paragraphe('folder-servicesm-64.jpg','{manage_services}','{manage_services_text}','javascript:Loadjs("admin.index.services.status.php?js=yes");','manage_services_text');

	if($users->BACKUPPC_INSTALLED){
		$backuppc=Paragraphe('backuppc-64.png','{APP_BACKUPPC}','{APP_BACKUPPC_TEXT}',"javascript:Loadjs('backup-pc.index.php');",'APP_BACKUPPC_TEXT');
	}
	if($users->OCSI_INSTALLED){
		$ocs=Paragraphe('64-ocs.png','{APP_OCSI}','{APP_OCSI_TEXT}',"javascript:Loadjs('ocs.ng.php');",'APP_OCSI_TEXT');
	}	
	
	if($users->OCS_LNX_AGENT_INSTALLED){
		$ocsAgent=Paragraphe('64-ocs.png','{APP_OCSI_LINUX_CLIENT}','{APP_OCSI_LINUX_CLIENT_TEXT}',"javascript:Loadjs('ocs.agent.php');",'APP_OCSI_LINUX_CLIENT_TEXT');
	}
	
	if($users->APP_AUDITD_INSTALLED){
		$auditd=Paragraphe('folder-watch-64.png','{APP_AUDITD}','{APP_AUDITD_TEXT}',"javascript:Loadjs('auditd.php');",'APP_AUDITD_TEXT');
		
	}
	
	if($users->DROPBOX_INSTALLED){
		$dropbox=Paragraphe('dropbox-64.png','{APP_DROPBOX}','{APP_DROPBOX_TEXT}',"javascript:Loadjs('samba.dropbox.php')",null,210,null,0,false);
	}

	if($users->APACHE_INSTALLED){
		$apache=Paragraphe('apache-groupeware-64.png','{APP_GROUPWARE_APACHE}','{APP_GROUPWARE_APACHE_TEXT}',"javascript:Loadjs('apache-groupware.php')",null,210,null,0,false);
	}
	
	if($users->CLAMD_INSTALLED){
		$clamav=Paragraphe('clamav-64.png','{clamav_protect}','{clamav_protect_disable_text}',"javascript:Loadjs('clamav.enable.php')",null,210,null,0,false);
	}	
	
	if($users->APACHE_INSTALLED){
		$F_IMG="free-web-64.png";
		$FreeWebLeftMenu=$sock->GET_INFO("FreeWebLeftMenu");
		if(!is_numeric($FreeWebLeftMenu)){$FreeWebLeftMenu=1;}
		if($FreeWebLeftMenu==0){$F_IMG="free-web-64-grey.png";}
		$free_web=Paragraphe($F_IMG,'{free_web_servers}','{free_web_servers_text}',"javascript:Loadjs('system.index.freeweb.php')",null,210,null,0,false);
	}
	
	$APP_SABNZBDPLUS=Paragraphe('sab2_64-grey.png','{APP_SABNZBDPLUS}','{APP_SABNZBDPLUS_TEXT}',"",null,210,null,0,false);
	if($users->APP_SABNZBDPLUS_INSTALLED){
		$APP_SABNZBDPLUS=Paragraphe('sab2_64.png','{APP_SABNZBDPLUS}','{APP_SABNZBDPLUS_TEXT}',"javascript:Loadjs('sabnzbdplus.php')",null,210,null,0,false);
		
	}
	
		
	
	
	$metaconsole=Paragraphe("artica-meta-64.png","{meta-console}","{meta-console-text}","javascript:Loadjs('artica.meta.php')",null,210,null,0,false);
	
	
	if($users->LXC_INSTALLED){
		$LXC=Paragraphe('64-computer-alias.png','{APP_LXC}','{APP_LXC_TEXT}',"javascript:Loadjs('lxc.index.php')",null,210,null,0,false);
	}
	
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		$clamav=null;
		$addressbook=null;
		$userautofill=null;
		$massmailing=null;
		$APP_SABNZBDPLUS=null;
		$free_web=null;
		$apache=null;
		$backuppc=null;
		$auditd=null;
		$LXC=null;
	}
	
	//ApacheGroupware

	
	$tr[]=$add_remove;
	$tr[]=$services;
	$tr[]=$metaconsole;
	$tr[]=$backuppc;
	$tr[]=$auditd;
	$tr[]=$phpldapadmin;
	$tr[]=$apache;
	$tr[]=$free_web;
	$tr[]=$clamav;
	$tr[]=$mldonkey;
	$tr[]=$massmailing;
	$tr[]=$addressbook;
	$tr[]=$userautofill;
	$tr[]=$obm2;
	$tr[]=$dotclear;
	$tr[]=$kav4fs;
	$tr[]=$ocs;
	$tr[]=$ocsAgent;
	$tr[]=$dropbox;
	$tr[]=$LXC;
	$tr[]=$APP_SABNZBDPLUS;


	
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
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}

function main_dns(){
	
	
	$user=new usersMenus();
	$static=Paragraphe('folder-64-dns-grey.png','{nic_static_dns}','{nic_static_dns_text}','');
	$bind9=ICON_BIND9();
	$etc_hosts=Buildicon64("DEF_ICO_ETC_HOSTS");
	$pdns=Buildicon64('DEF_ICO_PDNS');
	$dyndns=Paragraphe('folder-64-dyndns.png','{nic_dynamic_dns}','{nic_dynamic_dns_text}','system.nic.dynamicdns.php');
	$rbl_check=Paragraphe('check-64.png','{rbl_check_artica}','{rbl_check_artica_text}',"javascript:Loadjs('system.rbl.check.php')");
	$dnsmasq=Paragraphe('dns-64.png','{APP_DNSMASQ}','{APP_DNSMASQ_TEXT}',"javascript:Loadjs('dnsmasq.index.php')");
	

	
	if(!$user->BIND9_INSTALLED){
		$static=null;
		$bind9=null;
	}	
	
	if(!$user->POWER_DNS_INSTALLED){$pdns=Paragraphe("dns-64-grey.png","{APP_PDNS}","{APP_PDNS_TEXT}");}
	if(!$user->dnsmasq_installed){$dnsmasq=Paragraphe("dns-64-grey.png","{APP_DNSMASQ}",'{APP_DNSMASQ_TEXT}');}
		
	if($user->KASPERSKY_SMTP_APPLIANCE){
		$dyndns=null;
	}

	if($user->KASPERSKY_WEB_APPLIANCE){
		$bind9=null;
		$pdns=null;
		$dyndns=null;
	}
	
	$tr[]=$pdns;
	$tr[]=$dnsmasq;
	$tr[]=$bind9;
	$tr[]=$static;
	$tr[]=$dyndns;
	$tr[]=$etc_hosts;
	$tr[]=$rbl_check;

	
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
echo $tpl->_ENGINE_parse_body($html);
	
}

function main_update(){
	$users=new usersMenus();
	if($users->APT_MIRROR_INSTALLED){
		$img=$users->LinuxDistriCode."_mirror-64.png";
		$apt_mirror=Paragraphe($img,'{REPOSITORY_DEB_MIRROR}','{REPOSITORY_DEB_MIRROR_TEXT}',"javascript:Loadjs('apt-mirror.php')",null,210,null,0,false);
	}
	
//Paragraphe('64-dar-index.png','{incremental_backup}','{incremental_backup_text}',"javascript:Loadjs('dar.index.php?js=yes');",'repository_manager_text')	

		$UpdateUtility=Paragraphe('64-retranslator-grey.png',
		'{APP_KASPERSKY_UPDATE_UTILITY}',
		'{APP_KASPERSKY_UPDATE_UTILITY_TEXT}',
		"",'APP_KASPERSKY_UPDATE_UTILITY_TEXT');	
	
	if($users->KASPERSKY_UPDATE_UTILITY_INSTALLED){
		$UpdateUtility=Paragraphe('64-retranslator.png',
		'{APP_KASPERSKY_UPDATE_UTILITY}',
		'{APP_KASPERSKY_UPDATE_UTILITY_TEXT}',
		"javascript:Loadjs('KasperskyUpdateUtility.php')",'APP_KASPERSKY_UPDATE_UTILITY_TEXT');
		
	}
	
	$artica=Paragraphe('folder-64-artica-update.png','{artica_autoupdate}','{artica_autoupdate_text}',"javascript:Loadjs('artica.update.php?js=yes')",'artica_autoupdate_text');
	$apt=Buildicon64("DEF_ICO_APT");
	$retrans=Paragraphe('64-retranslator.png','{APP_KRETRANSLATOR}','{APP_KRETRANSLATOR_TEXT}',"javascript:Loadjs('index.retranslator.php')",'APP_KRETRANSLATOR_TEXT');
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		$apt_mirror=null;
		
	}
	
	
	$tr[]=$artica;
	$tr[]=$UpdateUtility;
	$tr[]=$retrans;
	$tr[]=$apt_mirror;
	
	
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
	echo $tpl->_ENGINE_parse_body($html);			
	
}
function AdressBookPopup(){
	include_once('ressources/class.artica.inc');
	$users=new usersMenus();
	$artica=new artica_general();
	$info=Paragraphe_switch_img('{remote_addressbook}','{remote_addressbook_explain}','EnableRemoteAddressBook',$artica->EnableRemoteAddressBook,'{enable_disable}',290);
	$singleAddressBook=Paragraphe_switch_img('{per_user_addressbook}','{per_user_addressbook_explain}','EnablePerUserRemoteAddressBook',$artica->EnablePerUserRemoteAddressBook,'{enable_disable}',290);
	$singleAddressBook_button="<input type='button' OnClick=\"javascript:AdressBookAcls();\" value='{edit_acls}&nbsp;&raquo;'>";
	
	
	if($users->SLPAD_LOCKED){
		$info=Paragraphe('warning64.png','{error}','{ERROR_SLAPDCONF_LOCKED}',null,null,210,null,1);
		$singleAddressBook=null;
		$singleAddressBook_button=null;
	}
	
	$singleAddressBook_button=null;
	
$html="<div id='rdr'>
	<H1>{remote_addressbook}</H1>
	<p class=caption>{remote_addressbook_text}</p>
	<table style='width:100%'>
	<tr>

	<td valign='top' width=100%>
		<table style='width:100%'>
		<tr>
			<td valign='top' style='width:300px'>
				$info
			</td>
			<td valign='top'>
				<input type='button' OnClick=\"javascript:AddressBookSave();\" value='{edit}&nbsp;&raquo;'>
			</td>
		</tr>
		</table>
		
		<br>
		
		<table style='width:100%'>
		<tr>
			<td valign='top' style='width:300px'>
				$singleAddressBook
			</td>
			<td valign='top'>
				<input type='button' OnClick=\"javascript:AddressBookSave();\" value='{edit}&nbsp;&raquo;'>
				<hr>
				<table style='width:100%'>
					<tr>
						<td class=legend>{enable_non_encrypted_sessions}</td>
						<td>" . Field_numeric_checkbox_img('EnableNonEncryptedLdapSession',$users->EnableNonEncryptedLdapSession)."</td>
					</tr>
				</table>
				$singleAddressBook_button
			</td>
		</tr>
		</table>		
		
		
		
	</td>
	
	</tr>
	</table>
	</div>
	";
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);		

}

function AdressBookPopup_save(){
	$artica=new artica_general();
	$artica->EnableRemoteAddressBook=$_GET["EnableRemoteAddressBook"];
	$artica->EnablePerUserRemoteAddressBook=$_GET["EnablePerUserRemoteAddressBook"];
	$artica->EnableNonEncryptedLdapSession=$_GET["EnableNonEncryptedLdapSession"];
	$artica->Save();
	$sock=new sockets();
	
	$LdapAclsPlus=$sock->GET_INFO('LdapAclsPlus');
	//if(trim($LdapAclsPlus)==null){$LdapAclsPlus=AdressBookAclDefault();}
	$LdapAclsPlus=AdressBookAclDefault();
	$sock->SaveConfigFile(CleanLdapAclsPlus($LdapAclsPlus),"LdapAclsPlus");
	
	$sock->getfile('OpenLdapRestart');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{success}:Address Book\n");
	
}
function AdressBookAclDefault(){
	$ldap=new clladp();
	$html="access to dn.regex=\"^ou=([^,]+),ou=People,dc=([^,]+),dc=NAB,$ldap->suffix$\"\n";
	$html=$html . "\tattrs=entry,@inetOrgPerson\n";
	$html=$html . "\tby dn.exact,expand=\"cn=$1,ou=users,ou=$2,dc=organizations,$ldap->suffix\" write\n";
	return $html;
}

function CleanLdapAclsPlus($content){
	$tbl=explode("\n",$content);
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$conf=$conf . "$ligne\n";
		
	}
	
	return $conf;
	
}


 	
	



?>	