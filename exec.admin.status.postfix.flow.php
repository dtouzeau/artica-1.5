<?php
if(!function_exists("posix_getuid")){echo "posix_getuid !! not exists\n";}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["FORCE"]=false;
$GLOBALS["EXECUTED_AS_ROOT"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");



if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;echo "FORCED!!\n";}



if($argv[1]=="--newvers"){$status=new status();$status->BuildNewersions();die();}
if($argv[1]=="--status-right"){status_right();die();}




if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	if(!$GLOBALS["FORCE"]){die();}
}




if($GLOBALS["VERBOSE"]){
	writelogs(basename(__FILE__).":DEBUG:Executed",basename(__FILE__),__FILE__,__LINE__);
}

if($argv[1]=="--setup-center"){setup_center();die();}
if($argv[1]=="--services"){services();die();}
if($argv[1]=="--mysql"){test_mysql();die();}
if($argv[1]=="--monit"){test_monit();die();}

if($argv[1]=='--force'){$_GET["FORCE"]=true;}
if(!$_GET["FORCE"]){
	if(system_is_overloaded()){die();}
	if(!Build_pid_func(__FILE__,"MAIN")){
		if(!is_file("/usr/share/artica-postfix/ressources/logs/status.right.1.html")){status_right();}
		writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
		die();
	}
}
	
if($argv[1]=='--setup'){
	setup_center();
	error_log("setup_center() die in ".__FILE__);
	die();	
}
	
if(!$GLOBALS["FORCE"]){
		$sock=new sockets();
		$PoolCoverPageSchedule=intval($sock->GET_INFO('PoolCoverPageSchedule'));
		if($PoolCoverPageSchedule<1){$PoolCoverPageSchedule=10;}
		$timef=file_get_time_min("/etc/artica-postfix/croned.2/".md5(__FILE__));
		if($GLOBALS["VERBOSE"]){echo "DEBUG:$timef <> $PoolCoverPageSchedule\n";}
		if($timef<$PoolCoverPageSchedule){
			if(!is_file("/usr/share/artica-postfix/ressources/logs/status.right.1.html")){status_right();}
			die();
		}
	}
$timef="/etc/artica-postfix/croned.2/".md5(__FILE__);	
@unlink($timef);
@file_put_contents($timef,date('Y-m-d H:i:s'));

BuildingExecStatus("Build deamons status...",10);
daemons_status();
events("daemons_status(); OK");


status_right();
events("status_right(); OK");
BuildingExecStatus("Right Postfix pan...",40);



BuildingExecStatus("New versions...",55);
if(isset($status)){BuildJgrowlVersions($status);}
events("BuildVersions(); OK");

BuildingExecStatus("Setup Center...",60);
setup_center();
events("setup_center(); OK");
BuildingExecStatus("Samba status...",80);
samba_status();
events("samba_status(); OK");
BuildingExecStatus("Done...",100);

@unlink($timef);
$restults=file_put_contents($timef,'#');
events(basename(__FILE__).":: stamp \"$timef\" ($restults) done...");

function services(){
	events(basename(__FILE__).":: running daemons_status()");
	daemons_status();
	events(basename(__FILE__).":: running daemons_status done...");
}


function kernel_mismatch(){
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	
	if(($users->LinuxDistriCode<>"DEBIAN") && ($users->LinuxDistriCode<>"UBUNTU")){return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?system-debian-kernel=yes");
	$array=unserialize(base64_decode(@file_get_contents(dirname(__FILE__)."/ressources/logs/kernel.lst")));
	$CPU_FAMILY=$array["INFOS"]["CPU_FAMILY"];
	$soitquatrebits=$array["INFOS"]["64BITS"];
	$HT_SUPPORT=$array["INFOS"]["HT"];
	$CURRENT=$array["INFOS"]["CURRENT"];
	$MODEL=$array["INFOS"]["MODEL"];
	if($CPU_FAMILY<10){$icpu="i{$CPU_FAMILY}86";}
	if(preg_match("#.+?-([0-9]+)86#",$CURRENT,$re)){$kernel_arch="i{$re[1]}86";}
	
	if($kernel_arch<>null){
		if($icpu<>null){
			if($icpu<>$kernel_arch){
				$must_change=true;
			}
		}
	}
if(!$must_change){return null;}
	
	
	return 	NotifyAdmin('warning64.png',
		"{kernel_mismatch}",
		"{kernel_mismatch_text}",
		"javascript:Loadjs('system.kernel.debian.php')",
		"{kernel_mismatch_text}",300,80);
	
}



function daemons_status(){
if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	$artica=new artica_general();
	$tpl=new templates();
	$sock=new sockets();
	$ONLY_SAMBA=false;
	if($GLOBALS["VERBOSE"]){echo "DEBUG:daemons_status:: -> Stating...\n";}
	$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
	if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
	$statusSpamassassinUpdateFile="/usr/share/artica-postfix/ressources/logs/sa-update-status.html";
	$statusSpamassassinUpdateText=null;$service=null;$blacklist=null;$no_orgs=null;$zabbix=null;$samba=null;$computers=null;$nobackup=null;$check_apt=null;$services=null;
	
	if(!$users->SQUID_INSTALLED){
		if(!$users->POSTFIX_INSTALLED){
			if($users->SAMBA_INSTALLED){
				$ONLY_SAMBA=true;
			}
		}
	}
	
	if($users->collectd_installed){
		if($artica->EnableCollectdDaemon==1){
			$collectd=NotifyAdmin("64-charts.png","{collectd_statistics}","{collectd_statistics_text}","javascript:YahooWin(790,'collectd.index.php?PopUp=yes')","services_status_text",300,76);
		}
	}
	
	$interface=new networking();
	$i=0;
	if(is_array($interface->array_TCP)){
		while (list ($num, $val) = each ($interface->array_TCP) ){
			if($val==null){continue;}
			$i++;
			$iptext=$iptext."<div style='font-size:11px'><strong>{nic}:$num:<a href='#' OnClick=\"javascript:Loadjs('system.nic.config.php?js=yes')\">$val</a></strong></div>";
			if($i>2){break;}
		}
	}
	
	if($EnableArticaSMTPStatistics==1){
		if(!$users->KASPERSKY_WEB_APPLIANCE){
			if($users->POSTFIX_INSTALLED){
			$monthly_stats=NotifyAdmin("statistics-network-32.png","{monthly_statistics}","{monthly_statistics_text}","javascript:Loadjs('smtp.daily.statistics.php')","{monthly_statistics_text}",300,76,1);
			}
	}}
	
	$services="
	$collectd
	";
	
	
	
	
	$ini=new Bs_IniHandler();
	$ini->loadFile("/etc/artica-postfix/smtpnotif.conf");
	$ArticaMetaEnabled=$sock->GET_INFO($ArticaMetaEnabled);
	
	$RootPasswordChanged=$sock->GET_INFO("RootPasswordChanged");
	if($RootPasswordChanged<>1){
		$RootPasswordChangedTXT=NotifyAdmin('warning64.png',
		"{root_password_not_changed}",
		"{root_password_not_changed_text}",
		"javascript:Loadjs('system.root.pwd.php')",
		"{root_password_not_changed_text}",300,80);
	}
	
	
	
	if($ArticaMetaEnabled<>1){
	if($sock->GET_INFO("DisableWarnNotif")<>1){
		if(trim($ini->_params["SMTP"]["enabled"]==null)){
		$js="javascript:Loadjs('artica.settings.php?ajax-notif=yes')";
		$services=NotifyAdmin('danger64.png',
		"{smtp_notification_not_saved}",
		"{smtp_notification_not_saved_text}",
		"$js",
		"{smtp_notification_not_saved}",300,80);
		
	}}}
	
	
	if(!$users->KASPERSKY_WEB_APPLIANCE){
		if($ArticaMetaEnabled<>1){
		if($sock->GET_INFO("WizardBackupSeen")<>1){
			$js="javascript:Loadjs('wizard.backup-all.php')";
			$nobackup=NotifyAdmin('danger64.png',"{BACKUP_WARNING_NOT_CONFIGURED}","{BACKUP_WARNING_NOT_CONFIGURED_TEXT}","$js","{BACKUP_WARNING_NOT_CONFIGURED_TEXT}",300,80);
		}}}
	
	$DisableAPTNews=$sock->GET_INFO('DisableAPTNews');
	if(!is_numeric($DisableAPTNews)){$DisableAPTNews=0;}
	if($DisableAPTNews==0){
		$datas=trim(@file_get_contents("/etc/artica-postfix/apt.upgrade.cache"));
		if(preg_match('#nb:([0-9]+)\s+#is',$datas,$re)){
		$check_apt=NotifyAdmin('32-infos.png',"{upgrade_your_system}","{$re[1]}&nbsp;{packages_to_upgrade}","javascript:Loadjs('artica.repositories.php?show=update')",null,300,76);
		}
	}
	
	$DisableNoOrganization=$sock->GET_INFO('DisableNoOrganization');
	if(!is_numeric($DisableNoOrganization)){$DisableNoOrganization=0;}
	if($DisableNoOrganization==0){
		$ldap=new clladp();
		$hash=$ldap->hash_get_ou();
		$ldap->ldap_close();
		if(count($hash)<1){
		$no_orgs=NotifyAdmin('warning-panneau-32.png',"{no_organization}","{no_organization_text_jgrowl}","javascript:TreeAddNewOrganisation()",null,300,76);
		}
	}
	
	
	if(!$users->KASPERSKY_WEB_APPLIANCE){
		if($users->POSTFIX_INSTALLED){
				$ok=true;
				$main=new main_cf();
				if(!$main->CheckMyNetwork()){		
					NotifyAdmin('pluswarning64.png','{postfix_mynet_not_conf}','{postfix_mynet_not_conf_text}',"javascript:Loadjs('postfix.network.php?ajax=yes');","{postfix_mynet_not_conf}",300,73);
					}
				}
	}
		
	
	
	
	if($users->BadMysqlPassword==1){
			$services="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/superuser-64-bad.png'></td>
	<td valign='top'><H5>{error_mysql_user}</H5><p class=caption>{error_mysql_user_text}</p></td>
	</tr>
	</table>";
	$services=RoundedLightGrey($services,"artica.settings.php",1);	
	}


		
	$DisableFrontBrowseComputers=$sock->GET_INFO('DisableFrontBrowseComputers');
	if(!is_numeric($DisableFrontBrowseComputers)){$DisableFrontBrowseComputers=0;}
	if($DisableFrontBrowseComputers==0){	
		if($ONLY_SAMBA){
			$computers=NotifyAdmin("32-win-nic-browse.png",'{browse_computers}','{browse_computers_text}',"javascript:Loadjs('computer-browse.php');","{browse_computers_text}",300,76,1);
			$samba=NotifyAdmin("explorer-32.png",'{explorer}','{SHARE_FOLDER_TEXT}',"javascript:Loadjs('tree.php');","{SHARE_FOLDER_TEXT}",300,76,1);
		}
	}

		
	if(!$users->KASPERSKY_WEB_APPLIANCE){
			if(!is_file("/etc/artica-postfix/KASPER_MAIL_APP")){
				if($users->ZABBIX_INSTALLED){
				$EnableZabbixServer=$sock->GET_INFO("EnableZabbixServer");
				if($EnableZabbixServer==null){$EnableZabbixServer=1;}
				if($EnableZabbixServer==1){
					$zabbix=NotifyAdmin("zabbix_med.gif",'{APP_ZABIX_SERVER}','{APP_ZABIX_SERVER_TEXT}',
					"javascript:Loadjs('zabbix.php')","{APP_ZABIX_SERVER_TEXT}",300,76,1);
					}
				}
			}
	}	
	
	if($sock->GET_INFO("DisableFrontEndArticaEvents")<>1){
		$q=new mysql();
		$sql="SELECT COUNT(ID) as tcount FROM events";
		$events_sql=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		
		
	}
	
	$kernel_mismatch=kernel_mismatch();
	$newversion=null;
	$kavicap_license_error=kavicap_license_error();
	$cicap_bad=CicapBadParams();
	if($GLOBALS["VERBOSE"]){echo "DEBUG:daemons_status:: -> squid_filters_infos()\n";}
	$squidfilters=squid_filters_infos();
	if($users->POSTFIX_INSTALLED){
		if(is_file("ressources/logs/web/blacklisted.html")){
			$blacklist=@file_get_contents("ressources/logs/web/blacklisted.html");
		}
	}
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		kavproxyInfos();
	}
	
	if(is_file($statusSpamassassinUpdateFile)){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:$statusSpamassassinUpdateFile file exists...\n";}
		$statusSpamassassinUpdateText=@file_get_contents($statusSpamassassinUpdateFile);
	}else{
		if($GLOBALS["VERBOSE"]){echo "DEBUG:$statusSpamassassinUpdateFile no such file...\n";}
	}
	
	
	
	@unlink("/usr/share/artica-postfix/ressources/logs/status.warnings.html");
	@unlink("/usr/share/artica-postfix/ressources/logs/status.inform.html");
	$DisableWarningCalculation=$sock->GET_INFO("DisableWarningCalculation");
	if(!is_numeric("DisableWarningCalculation")){$DisableWarningCalculation=0;}
	$final="
	
	$no_orgs
	<span id='loadavggraph'></span>
	<span id='kav4proxyGraphs'></span>		
	$squidfilters
	$cicap_bad
	$events_NotifyAdmin
	$zabbix
	$monthly_stats
	$newversion
	$samba
	$computers";

events(__FUNCTION__."/usr/share/artica-postfix/ressources/logs/status.global.html ok");	
file_put_contents('/usr/share/artica-postfix/ressources/logs/status.global.html',$final);
system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.global.html');	
	
}

function kavproxyInfos(){
	$unix=new unix();
	$patterns=$unix->KAV4PROXY_PATTERN();
	if($patterns==null){
		$unix->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --kav4proxy");
	}
	

}


function squid_filters_infos(){
	$sock=new sockets();
if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	if(!$users->SQUID_INSTALLED){return null;}
	$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
	if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
	
	if($SQUIDEnable==0){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:squid_filters_infos():: SQUIDEnable is not enabled... Aborting\n";}
		return;
	}	
	$filtered=false;
	$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
	$squidGuardEnabled=$sock->GET_INFO("squidGuardEnabled");
	$DansGuardianEnabled=$sock->GET_INFO("DansGuardianEnabled");
	if($EnableUfdbGuard==1){$filtered=true;}
	if($squidGuardEnabled==1){$filtered=true;}
	if($DansGuardianEnabled==1){$filtered=true;}
	if($users->SQUID_ICAP_ENABLED){$filtered=true;}
	if(!$filtered){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:squid_filters_infos():: Not filtered\n";}
		return null;}
	
	$EnableCommunityFilters=$sock->GET_INFO("EnableCommunityFilters");
	if($EnableCommunityFilters==null){$EnableCommunityFilters=1;}
	if($EnableCommunityFilters<>1){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:squid_filters_infos():: EnableCommunityFilters is not enabled... Aborting\n";}
		return;}
	
	$sql="SELECT count(*) as tcount FROM `dansguardian_sitesinfos` WHERE `dbpath` = ''";	
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($GLOBALS["VERBOSE"]){echo "DEBUG:squid_filters_infos():: EnableCommunityFilters {$ligne["tcount"]}\n";}
	if($ligne["tcount"]==0){return null;}
	
	return NotifyAdmin("32-categories.png",$ligne["tcount"]." {websites_not_categorized}",
	"{websites_not_categorized_text}","javascript:Loadjs('squid.visited.php')",null,300,76,1);

}

function kavicap_license_error(){
if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	$sock=new sockets();
	if(!$users->KAV4PROXY_INSTALLED){return null;}
	$kavicapserverEnabled=$sock->GET_INFO("kavicapserverEnabled");
	if($kavicapserverEnabled==null){$kavicapserverEnabled=0;}	
	if($kavicapserverEnabled==0){return null;}
	if(!$users->KAV4PROXY_LICENSE_ERROR){return null;}
	
	$pattern_date=trim(base64_decode($sock->getFrameWork("cmd.php?kav4proxy-pattern-date=yes")));

	if($pattern_date==null){
	return NotifyAdmin("license-error-64.png",'{av_pattern_database}',"{APP_KAV4PROXY}:: {av_pattern_database_obsolete_or_missing}","","{APP_KAV4PROXY}",300,76,1);
	}
	
	return NotifyAdmin("license-error-64.png",'{license_error}',"{APP_KAV4PROXY}:: $users->KAV4PROXY_LICENSE_ERROR_TEXT",
	"javascript:Loadjs('Kav4Proxy.License.php');","{license}",300,76,1);
	
	
}

function Squidbettersquidver(){
if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	$sock=new sockets();
	if(!$users->SQUID_INSTALLED){return null;}
	$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
	if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
	
	if($SQUIDEnable==0){return;}
	$squid=new squidbee();
	$ver=$squid->intvalVersion;
	if($ver>316){return;}

	
	return NotifyAdmin("software-back-64.png",'{GET_LAST_SQUID_VER}',"{GET_LAST_SQUID_VER_TEXT}"
	,"javascript:Loadjs('setup.index.php?js=yes');","{GET_LAST_SQUID_VER_TEXT}",300,76,1);
	
	
}

function CicapBadParams(){
if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	if(!$users->C_ICAP_INSTALLED){return null;}
	$sock=new sockets();
	$enable_cicap=$sock->GET_INFO('CicapEnabled');
	$CiCapViralatorMode=$sock->GET_INFO('CiCapViralatorMode');
	if($enable_cicap==null){return;}
	if($enable_cicap==0){return;}
	if($CiCapViralatorMode==null){return;}
	if($CiCapViralatorMode==0){return;}	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO('CicapInternalConfiguration'));
	$VirHTTPServer=$ini->_params["CONF"]["VirHTTPServer"];
	$notify=false;
	if($VirHTTPServer==null){$notify=true;}
	if(preg_match('#https://(.*?)/exec#',$VirHTTPServer,$re)){
		if(trim($re[1])==null){$notify=true;}
		if(trim($re[1])=="127.0.0.1"){$notify=true;}
		if(trim($re[1])=="localhost"){$notify=true;}
	}else{
		$notify=true;
	}
	
	if(!$notify){return;}	
	return NotifyAdmin("bad-parameter-64.png",'{BAD_CONFIGURATION_CICAP}',
	"{BAD_CONFIGURATION_CICAP_BAD_PARAM}: {VirHTTPServer}",
	"javascript:Loadjs('c-icap.index.php?runthis=cicap_daemons');","{VirHTTPServer}",300,76,1);
	
}





function status_right(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$cachefile="/usr/share/artica-postfix/ressources/logs/status.right.1.html";
	$cachemem="/usr/share/artica-postfix/ressources/logs/status.memory.html";
	if(is_file($cachefile)){
		$minutes=file_time_min($pidfile);
		if($minutes<10){
			events("Stopping status, currently {$minutes}Mn need to wait 10Mn (".filesize($cachefile)." Bytes)",__FUNCTION__,__FILE__,__LINE__);
			events("$cachefile (".filesize($cachefile)." Bytes)",__FUNCTION__,__FILE__,__LINE__);
			@chmod($cachefile,0777);	
			return;	
		}
	}
	
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){
		events("Stopping status, Process Already running",__FUNCTION__,__FILE__,__LINE__);
		return;	
		
	}	
	
	@file_put_contents($pidfile,@getmypid());
	$postfix=BuildStatusRight();
	@unlink($cachefile);
	@file_put_contents($cachefile,"$postfix");
	@file_put_contents($cachemem,$_GET["CURRENT_MEMORY"]);
	shell_exec("/bin/chmod 777 $cachefile >/dev/null");
	shell_exec("/bin/chmod 777 $cachemem >/dev/null");
	events(__FUNCTION__."() done..");
}


function BuildStatusRight(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$minutes=file_time_min($pidfile);
	if($minutes<10){
		events("Stopping status, currently {$minutes}Mn need to wait 10Mn",__FUNCTION__,__FILE__,__LINE__);
		return;	
	}
	
	
if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	$sock=new sockets();
	$tpl=new templates();
	$status=new status();
	
	
	$SAMBA_INSTALLED=0;
	$SQUID_INSTALLED=0;
	$POSTFIX_INSTALLED=0;
	
	if($users->POSTFIX_INSTALLED){$POSTFIX_INSTALLED=1;}

	if($users->SQUID_INSTALLED){
		$SQUID_INSTALLED=1;
		$SQUID_INSTALLED=$sock->GET_INFO("SQUIDEnable");
		if($SQUID_INSTALLED==null){$SQUID_INSTALLED=1;}
	}
	
	if($users->SAMBA_INSTALLED){
		$SAMBA_INSTALLED=1;
		$SAMBA_INSTALLED=$sock->GET_INFO("SambaEnabled");
		if($SAMBA_INSTALLED==null){$SAMBA_INSTALLED=1;}
	}
	
	events("POSTFIX_INSTALLED=$POSTFIX_INSTALLED,SQUID_INSTALLED=$SQUID_INSTALLED,SAMBA_INSTALLED=$SAMBA_INSTALLED");
	writelogs("POSTFIX_INSTALLED=$POSTFIX_INSTALLED,SQUID_INSTALLED=$SQUID_INSTALLED,SAMBA_INSTALLED=$SAMBA_INSTALLED",__FUNCTION__,__FILE__,__LINE__);
	if($SQUID_INSTALLED==1){$Squid_status=$status->Squid_status();}	
	
	if($POSTFIX_INSTALLED==0){
		if($SQUID_INSTALLED==1){return $Squid_status;}
		if($SAMBA_INSTALLED==1){return StatusSamba();}
	}else{
		
		return $status->Postfix_satus();
	}
}


function StatusSamba(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getFrameWork("cmd.php?samba-status=yes"));
	$status_smbd=DAEMON_STATUS_ROUND("SAMBA_SMBD",$ini);
	$status_nmbd=DAEMON_STATUS_ROUND("SAMBA_NMBD",$ini);
	$html="
		<table style='width:100%'>
			<tr>
				<td valign='top'>
					<table style='width:100%'>
						<tr>
							<td valign='top' width=1%>" . imgtootltip('64-samba.png','{APP_SAMBA}',"javascript:Loadjs('fileshares.index.php?js=yes')")."</td>
							<td valign='top' ><br>$status_smbd<br>$status_nmbd</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>";	
	
	return $html;
}




function BuildJgrowlVersions(){
	$status=new status();
	$status->BuildNewersions();	
}

function setup_center(){
	return;
	if(!$GLOBALS["FORCE"]){
		if(!Build_pid_func(__FILE__,__FUNCTION__)){return false;}
		$time_file="/etc/artica-postfix/croned.2/".md5(__FILE__.__FUNCTION__);
		$tt=file_time_sec($time_file);
		if($tt<30){
			events(__FUNCTION__." $tt seconds, please wait 30s");
			return null;
		}
	}

	
		include_once(dirname(__FILE__).'/setup.index.php');
		
	
		error_log("Starting ". __FUNCTION__." in ".__FILE__);
	
		
		BuildingExecStatus("Setup center:: statistics...",52);
		stat_packages();
		BuildingExecStatus("Setup center:: SMTP...",54);
		smtp_packages();
		BuildingExecStatus("Setup center:: WEB...",56);
		web_packages();
		BuildingExecStatus("Setup center:: Proxy...",58);
		proxy_packages();
		BuildingExecStatus("Setup center:: Samba...",60);
		samba_packages();
		BuildingExecStatus("Setup center:: System...",62);
		system_packages();
		BuildingExecStatus("Setup center:: Xapian...",64);
		xapian_packages();
		BuildingExecStatus("Setup center:: done...",68);
		events(__FUNCTION__."() done..");	
	
}

function events($text){
		$d=new debuglogs();
		$logFile="/var/log/artica-postfix/artica-status.debug";
		writelogs($text,"NONE",__FILE__,__LINE__);
		$d->events(basename(__FILE__)." $text",$logFile);
		}
		
function samba_status(){
	$ini=new Bs_IniHandler();
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$user=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$user;}else{$user=$GLOBALS["CLASS_USERS_MENUS"];}
	$sock=new sockets();
	$tpl=new templates();
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	shell_exec("$php5 /usr/share/artica-postfix/exec.status.php --samba --nowachdog >/tmp/samba.status.ini 2>&1");
	$ini->loadFile("/tmp/samba.status.ini");
	if($user->SAMBA_INSTALLED){
		$samba_status=DAEMON_STATUS_ROUND("SAMBA_SMBD",$ini);
		$nmmbd=DAEMON_STATUS_ROUND("SAMBA_NMBD",$ini);
		$winbind=DAEMON_STATUS_ROUND("SAMBA_WINBIND",$ini);
		$kav_status=DAEMON_STATUS_ROUND("KAV4SAMBA",$ini);
		$SAMBA_SCANNEDONLY=DAEMON_STATUS_ROUND("SAMBA_SCANNEDONLY",$ini);
	}
	if($user->PUREFTP_INSTALLED){
		$pureftpd_status=DAEMON_STATUS_ROUND("PUREFTPD",$ini);
	}
	
	$results="$samba_status<br>$nmmbd<br>$winbind<br>$SAMBA_SCANNEDONLY<br>$kav_status<br>$pureftpd_status";
	
file_put_contents('/usr/share/artica-postfix/ressources/logs/status.samba.html',$results);		
system('/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/status.samba.html');
events(__FUNCTION__."() done..");	
}


function interface_error(){
	
	$ini=new Bs_IniHandler();
	if(!is_file("/usr/share/artica-postfix/ressources/logs/interface.events")){return null;}
	$ini->loadFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	
	while (list ($num, $ligne) = each ($ini->_params) ){
		if($ini->_params[$num]["error"]==null){continue;}
		$html=$html . NotifyAdmin("warning64.png","{error} {$num}",$ini->_params[$num]["error"],"javascript:StopInterfaceError('$num')");
		
	}
	events(__FUNCTION__."() done..");	
	return $html;
	
}

function test_monit(){
	$unix=new unix();
	$unix->monit_array();
	die();
}





?>