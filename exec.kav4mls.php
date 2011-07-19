<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.kavmilterd.inc');
include_once(dirname(__FILE__).'/ressources/class.postfix-multi.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if($argv[1]=="--reconfigure"){
	build_main();
	removes_rules();
	write_rules();
	die();
}

function removes_rules(){
	$dir_handle = @opendir("/etc/opt/kaspersky/kav4lms/groups.d");
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("/etc/opt/kaspersky/kav4lms/groups.d/$file")){continue;}
		  @unlink("/etc/opt/kaspersky/kav4lms/groups.d/$file");
		  continue;
		}
}


function write_rules(){
	
	$sql="SELECT ou,configlms FROM kavmilter";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$conf=base64_decode($ligne["config"]);
		$ou=$ligne["ou"];
		echo "Starting.... Kavmilter $ou rule\n";
		@file_put_contents("/etc/opt/kaspersky/kav4lms/groups.d/$ou.conf",$conf);
		PatchDomains($ou);
		}	
}
function PatchDomains($ou){
	$ldap=new clladp();
	if($ldap->ldapFailed){return null;}
	if(strtolower($ou)=="default"){return null;}
	$tbl=explode("\n",@file_get_contents("/etc/opt/kaspersky/kav4lms/groups.d/$ou.conf"));
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#Recipients=#",$val)){unset($tbl[$num]);}
		if(preg_match("#Senders=#",$val)){unset($tbl[$num]);}
	}
	
	$domains=$ldap->hash_get_domains_ou($ou);
	if(!is_array($domains)){
		@unlink("/etc/opt/kaspersky/kav4lms/groups.d/$ou.conf");
	}
	
	if(is_array($domains)){
		while (list ($num, $val) = each ($domains) ){
		$num=str_replace('.','\.',$num);
		$arr[]="Recipients=re:.*$num";
		}
	}
	
	
	if(!is_array($arr)){@unlink("/etc/opt/kaspersky/kav4lms/groups.d/$ou.conf");return ;}
	$arr[]="Senders=";
	reset($tbl);
	
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#\.definition\]#",$val)){
			$tbl[$num]=$tbl[$num]."\n".implode("\n",$arr);
		}
	}
	
	@file_put_contents("/etc/opt/kaspersky/kav4lms/groups.d/$ou.conf",implode("\n",$tbl));
	
}


function build_main(){
	
$conf_upd=new multi_config("kaspersky.updater");
$conf_scan=new multi_config("kaspersky.server");
	$MilterTimeout=$conf_scan->GET("MilterTimeout");
	if($MilterTimeout==null){$MilterTimeout=600;}
	$MaxScanRequests=$conf_scan->GET("MaxScanRequests");
	if($MaxScanRequests==null){$MaxScanRequests=0;}
	$MaxScanTime=$conf_scan->GET("MaxScanTime");
	if($MaxScanTime==null){$MaxScanTime=10;}			
			
	$ScanPacked=$conf_scan->GET("ScanPacked");
	if($ScanPacked==null){$ScanPacked="yes";}
	
	$ScanArchives=$conf_scan->GET("ScanArchives");
	if($ScanArchives==null){$ScanArchives="yes";}
	
	$ScanCodeanalyzer=$conf_scan->GET("ScanCodeanalyzer");
	if($ScanCodeanalyzer==null){$ScanCodeanalyzer="yes";}
	
	$UseAVBasesSet=$conf_scan->GET("UseAVBasesSet");
	if($UseAVBasesSet==null){$UseAVBasesSet="extended";}	



$ldap=new clladp();
$hash=$ldap->hash_get_all_domains();
while (list ($num, $val) = each ($hash) ){
	$doms[]=$num;
}

if(is_array($doms)){$domains=implode(",",$doms);}

$conf[]="[kav4lms:server.settings]";
$conf[]="RunAsUser=postfix";
$conf[]="RunAsGroup=postfix";
$conf[]="ServiceSocket=local:/var/run/kav4lms/kavmd.sock";
$conf[]="ServiceSocketPerms=0600";
$conf[]="AdminSocket=local:/var/run/kav4lms/kavmdctl.sock";
$conf[]="AdminSocketPerms=0600";
$conf[]="MaxWatchdogRetries=10";
$conf[]="MaxClientRequests=20";
$conf[]="MaxScanRequests=$MaxScanRequests";
$conf[]="LicensedUsersDomains=$domains";
$conf[]="";
$conf[]="[kav4lms:server.log]";
$conf[]="";
$conf[]="Options=all";
$conf[]="Destination=syslog:kavmd@mail";
$conf[]="Append=yes";
$conf[]="RotateRounds=10";
$conf[]="RotateSize=1M";
$conf[]="";
$conf[]="[kav4lms:server.statistics]";
$conf[]="Options=all";
$conf[]="Format=txt";
$conf[]="Destination=file:/var/opt/kaspersky/kav4lms/stats/statistics.xml";
$conf[]="RawDestination=file:/var/opt/kaspersky/kav4lms/stats/statistics.raw";
$conf[]="";
$conf[]="[kav4lms:server.notifications]";
$conf[]="ProductAdmins=postmaster";
$conf[]="ProductNotify=all";
$conf[]="Subject=Anti-virus notification message";
$conf[]="Charset=us-ascii";
$conf[]="TransferEncoding=7bit";
$conf[]="NotifierRelay=smtp:127.0.0.1:25";
$conf[]="NotifierQueue=/var/opt/kaspersky/kav4lms/nqueue/";
$conf[]="NotifierTimeout=5";
$conf[]="NotifierPersistence=no";
$conf[]="Templates=/etc/opt/kaspersky/kav4lms/templates-admin/en";
$conf[]="";
$conf[]="[kav4lms:server.snmp]";
$conf[]="SNMPServices=none";
$conf[]="SNMPTraps=none";
$conf[]="AlertThreshold=10";
$conf[]="Socket=inet:705@127.0.0.1";
$conf[]="PingInterval=30";
$conf[]="Timeout=5";
$conf[]="Retries=10";
$conf[]="";
$conf[]="[kav4lms:filter]";
$conf[]="";
$conf[]="[kav4lms:filter.settings]";
$conf[]="RunAsUser=postfix";
$conf[]="RunAsGroup=postfix";
$conf[]="FilterSocket=inet:1052@127.0.0.1";
$conf[]="FilterSocketPerms=0600";
$conf[]="ServiceSocket=local:/var/run/kav4lms/kavmd.sock";
$conf[]="ForwardSocket=inet:10026@127.0.0.1";
$conf[]="FilterTimeout=600";
$conf[]="FilterThreads=10";
$conf[]="MaxMilterThreads=0";
$conf[]="AdminSocket=local:/var/run/kav4lms/filterctl.sock";
$conf[]="AdminSocketPerms=0600";
$conf[]="";
$conf[]="[kav4lms:filter.log]";
$conf[]="Options=all";
$conf[]="Destination=syslog:kav4lms-filters@mail";
$conf[]="Append=yes";
$conf[]="RotateRounds=10";
$conf[]="RotateSize=1M";
$conf[]="";
$conf[]="[kav4lms:groups]";
$conf[]="_includes = kav4lms/groups.d/";
$conf[]="[path]";
$conf[]="BasesPath=/var/opt/kaspersky/kav4lms/bases/";
$conf[]="LicensePath=/var/opt/kaspersky/kav4lms/licenses/";
$conf[]="TempPath=/var/tmp/";
$conf[]="PidPath=/var/run/kav4lms/";
$conf[]="iCheckerDBFile=/var/opt/kaspersky/kav4lms/iChecker.db";
$conf[]="";
$conf[]="[locale]";
$conf[]="DateFormat=%Y-%m-%d";
$conf[]="TimeFormat=%H:%M:%S";
$conf[]="Strings=locale.d/strings.en";
$conf[]="";
$conf[]="[options]";
$conf[]="User=postfix";
$conf[]="Group=postfix";
$conf[]="";
$conf[]="[updater]";
$conf[]="[updater.path]";
$conf[]="BackUpPath=/var/opt/kaspersky/kav4lms/bases.backup/";
$conf[]="";
$conf[]="[updater.options]";
$conf[]="UpdateComponentsList=AVS, AVS_OLD, CORE, Updater, BLST";
$conf[]="RetranslateComponentsList=";
$conf[]="KeepSilent=no";

			$UseUpdateServerUrl=$conf_upd->GET("UseUpdateServerUrl");
			$UseUpdateServerUrlOnly=$conf_upd->GET("UseUpdateServerUrlOnly");
			$UpdateServerUrl=$conf_upd->GET("UpdateServerUrl");
			$RegionSettings=$conf_upd->GET("RegionSettings");
			$UseProxy=$conf_upd->GET("UseProxy");
			
			if($UseProxy==null){$UseProxy="no";}
			if($UseUpdateServerUrlOnly==null){$UseUpdateServerUrlOnly="no";}
			if($RegionSettings==null){$RegionSettings="eu";}
			if($UseUpdateServerUrl==null){$UseUpdateServerUrl="no";}


$conf[]="UseUpdateServerUrl=$UseUpdateServerUrl";
$conf[]="UseUpdateServerUrlOnly=$UseUpdateServerUrlOnly";
$conf[]="UpdateServerUrl=$UpdateServerUrl";
$conf[]="RegionSettings=$RegionSettings";
$conf[]="ConnectTimeout=30";
$conf[]="ProxyAddress={$conf_upd->GET("ProxyAddress")}";
$conf[]="PassiveFtp=yes";
$conf[]="UseProxy=$UseProxy";
$conf[]="Index=u0607g.xml";
$conf[]="IndexRelativeServerPath=index/6";
$conf[]="";
$conf[]="[updater.report]";
$conf[]="Append=no";
$conf[]="ReportFileName=/var/log/kaspersky/kav4lms/keepup2date.log";
$conf[]="ReportLevel=3";
$conf[]="";
$conf[]="[updater.actions]";
$conf[]="OnAny=/opt/kaspersky/kav4lms/bin/kav4lms-cmd -m update -e %EVENT_NAME% -w '%AVS_UPDATE_DATE%' >/dev/null";
$conf[]="OnStarted=";
$conf[]="OnUpdated=/opt/kaspersky/kav4lms/bin/kav4lms-cmd -x bases";
$conf[]="OnRetranslated=";
$conf[]="OnNotUpdated=";
$conf[]="OnFailed=";
$conf[]="OnRolledback=/opt/kaspersky/kav4lms/bin/kav4lms-cmd -x bases";
$conf[]="OnBasesCheck=/opt/kaspersky/kav4lms/lib/bin/avbasestest %TEMP_BASES_PATH% %BASES_PATH%";
$conf[]="[scanner.display]";
$conf[]="ShowContainerResultOnly=false";
$conf[]="ShowProgress=true";
$conf[]="ShowOk=true";
$conf[]="ShowObjectResultOnly=false";
$conf[]="";
$conf[]="[scanner.options]";
$conf[]="SelfExtArchives=yes";
$conf[]="ExcludeDirs=/dev:/udev:/proc:/sys";
$conf[]="MailBases=yes";
$conf[]="Archives=$ScanArchives";
$conf[]="Packed=$ScanPacked";
$conf[]="#ExcludeMask=";
$conf[]="UseAVbasesSet=$UseAVBasesSet";
$conf[]="#MaxLoadAvg=";
$conf[]="LocalFS=false";
$conf[]="Cure=yes";
$conf[]="MailPlain=yes";
$conf[]="Heuristic=yes";
$conf[]="Recursion=true";
$conf[]="Ichecker=yes";
$conf[]="FollowSymlinks=true";
$conf[]="";
$conf[]="";
$conf[]="[scanner.report]";
$conf[]="Append=true";
$conf[]="ShowContainerResultOnly=false";
$conf[]="ShowOk=true";
$conf[]="ReportLevel=4";
$conf[]="ShowObjectResultOnly=false";
$conf[]="ReportFileName=/var/log/kaspersky/kav4lms/kavscanner.log";
$conf[]="";
$conf[]="";
$conf[]="[scanner.container]";
$conf[]="#OnProtected=";
$conf[]="#OnWarning=";
$conf[]="#OnSuspicion=";
$conf[]="#OnCorrupted=";
$conf[]="#OnInfected=";
$conf[]="#OnError=";
$conf[]="#OnCured=";
$conf[]="";
$conf[]="";
$conf[]="[scanner.path]";
$conf[]="#BackupPath=";
$conf[]="";
$conf[]="[scanner.object]";
$conf[]="#OnProtected=";
$conf[]="#OnWarning=";
$conf[]="#OnSuspicion=";
$conf[]="#OnCorrupted=";
$conf[]="#OnInfected=";
$conf[]="#OnError=";
$conf[]="#OnCured=";
$conf[]="";

@file_put_contents("/etc/opt/kaspersky/kav4lms.conf",implode("\n",$conf));

}

?>