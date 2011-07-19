<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.kavmilterd.inc');
include_once(dirname(__FILE__).'/ressources/class.postfix-multi.inc');
include_once(dirname(__FILE__).'/ressources/class.retranslator.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");





$user=new usersMenus();
$param=$argv[1];
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--noreload#",implode(" ",$argv))){$GLOBALS["NORELOAD"]=true;}

if($argv[1]=="--SendmailPath"){SendmailPath(true);exit;}
if($argv[1]=="--default-group"){DefaultGroup();exit;}
if($argv[1]=="--templates"){DefaultTemplates();exit;}




build_main();
Removes();


	$sql="SELECT ou,config FROM kavmilter";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$conf=base64_decode($ligne["config"]);
		$ou=$ligne["ou"];
		@file_put_contents("/etc/kav/5.6/kavmilter/groups.d/$ou.conf",$conf);
		PatchDomains($ou);
		PatchIncludeByName($ou);
		PatchAdminAddresses($ou);
		echo "Starting......: Kaspersky Mail server rule:$ou ok\n";
		}
		
	SendmailPath();	
	if(!$GLOBALS["NORELOAD"]){
		echo "Starting......: Kaspersky Mail server reloading\n";
		shell_exec("/usr/share/artica-postfix/bin/artica-install --kavmilter-reload");
	}
	
	
function Removes(){
	$dir_handle = @opendir("/etc/kav/5.6/kavmilter/groups.d");
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("/etc/kav/5.6/kavmilter/groups.d/$file")){continue;}
		  if($file=="default.conf"){continue;}
		  @unlink("/etc/kav/5.6/kavmilter/groups.d/$file");
		  continue;
		}
}	


function PatchDomains($ou){
	$ldap=new clladp();
	if($ldap->ldapFailed){return null;}
	if(strtolower($ou)=="default"){return nul;}
	$tbl=explode("\n",@file_get_contents("/etc/kav/5.6/kavmilter/groups.d/$ou.conf"));
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#Recipients=#",$val)){unset($tbl[$num]);}
		if(preg_match("#Senders=#",$val)){unset($tbl[$num]);}
	}
	
	$domains=$ldap->hash_get_domains_ou($ou);
	if(!is_array($domains)){
		@unlink("/etc/kav/5.6/kavmilter/groups.d/$ou.conf");
	}
	
	if(is_array($domains)){
		while (list ($num, $val) = each ($domains) ){
		$num=str_replace('.','\.',$num);
		$arr[]="Recipients=re:.*$num";
		}
	}
	
	
	if(!is_array($arr)){@unlink("/etc/kav/5.6/kavmilter/groups.d/$ou.conf");return ;}
	$arr[]="Senders=";
	reset($tbl);
	
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#group\.definition#",$val)){
			$tbl[$num]=$tbl[$num]."\n".implode("\n",$arr);
		}
	}
	
	@file_put_contents("/etc/kav/5.6/kavmilter/groups.d/$ou.conf",implode("\n",$tbl));
	
}

function PatchAdminAddresses($ou){
$modified=false;	
$kavmilter_file="/etc/kav/5.6/kavmilter/groups.d/$ou.conf";
$tbl=explode("\n",@file_get_contents($kavmilter_file));
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#AdminAddresses=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/AdminAddresses is null, create a default one\n";
				$tbl[$num]="AdminAddresses=root@localhost.localdomain";
				$modified=true;
				continue;
			}
		}

if(preg_match("#EnableNotifications=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/EnableNotifications is null, set off by default\n";
				$tbl[$num]="EnableNotifications=off";
				$modified=true;
				continue;
			}
		}

	if(preg_match("#AdminSubject=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/AdminSubject is null, set by default\n";
				$tbl[$num]="AdminSubject=Admin virus notification!";
				$modified=true;
				continue;
			}
		}		
		
		
if(preg_match("#PostmasterAddress=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/PostmasterAddress is null, set one by default\n";
				$tbl[$num]="PostmasterAddress=root@localhost.localdomain";
				$modified=true;
				continue;
			}
		}
		
	if(preg_match("#NotifyAdmin=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/NotifyAdmin is null, set off default\n";
				$tbl[$num]="NotifyAdmin=none";
				$modified=true;
				continue;
			}
		}		

	if(preg_match("#NotifySender=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/NotifySender is null, set off default\n";
				$tbl[$num]="NotifySender=none";
				$modified=true;
				continue;
			}
		}

		if(preg_match("#NotifyRecipients=(.*)#",$val,$re)){
			if(trim($re[1])==null){
				echo "Starting......: Kaspersky Mail server line: ".($num+1).", $ou/NotifyRecipients is null, set off default\n";
				$tbl[$num]="NotifyRecipients=none";
				$modified=true;
				continue;
			}
		}		
		
		
		
	}
if($modified){
	echo "Starting......: Kaspersky Mail server saving configuration.\n";
	@file_put_contents($kavmilter_file,implode("\n",$tbl));
	}	
	
}

function PatchIncludeByName($ou){
	$sql=new mysql();
	$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='$ou' ORDER BY IncludeByName";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$kavmilter_file="/etc/kav/5.6/kavmilter/groups.d/$ou.conf";
	if(!$q->ok){return null;}
		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IncludeByName"]==null){continue;}
		$f[]=$ligne["IncludeByName"];
		
	}
	
	if(is_array($f)){
	$pattern=".*\.(". implode("|",$f).")$";
	}else{
		$pattern=null;
	}
	
	PatchValue("IncludeName",$pattern,$kavmilter_file);
	$tbl=explode("\n",@file_get_contents("/etc/kav/5.6/kavmilter/groups.d/$ou.conf"));
	}



function SendmailPath($reload=false){
	$unix=new unix();
	$sendmail=$unix->LOCATE_SENDMAIL_PATH();
	if(!is_file($sendmail)){return ;}
	
	$dir_handle = @opendir("/etc/kav/5.6/kavmilter/groups.d");
	if(!$dir_handle){return null;}
	$count=0;	
	while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("/etc/kav/5.6/kavmilter/groups.d/$file")){continue;}
		  PatchValue("SendmailPath",$sendmail,"/etc/kav/5.6/kavmilter/groups.d/$file");
		 }	
		 
	if(!$GLOBALS["NORELOAD"]){if($reload){shell_exec("/usr/share/artica-postfix/bin/artica-install --kavmilter-reload");}}
	
}
function PatchValue($key,$value,$file){
	$tbl=explode("\n",@file_get_contents("$file"));
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match("#$key=#",$val)){
			$tbl[$num]="$key=$value";
			$modified=true;
		}
	}
	
	if($modified){@file_put_contents($file,implode("\n",$tbl));}
		
		
}

function DefaultGroup(){
	@copy("/usr/share/artica-postfix/bin/install/kavmilter.default.conf","/etc/kav/5.6/kavmilter/groups.d/default.conf");
	SendmailPath(true);
}

function build_main(){
			
			$sock=new sockets();
			$conf_upd=new multi_config("kaspersky.updater");
			$conf_scan=new multi_config("kaspersky.server");
			$sock=new sockets();
			$bases="/var/db/kav/5.6/kavmilter/bases/";
			if($sock->GET_INFO("RetranslatorEnabled")==1){
				echo "Starting......: Kaspersky Mail server using retranslator for pattern databases.\n";
				$bases="/var/db/kav/databases/bases/av/avc/i386/";
			}else{
				echo "Starting......: Kaspersky Mail server retranslator is not enabled.\n";
			}
			
			
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
  			$sock=new sockets();
			$SendmailPath=trim(base64_decode($sock->getFrameWork("cmd.php?SendmailPath=yes")));
			$ldap=new clladp();
			$hash=$ldap->hash_get_all_domains();
			$conf[]= "[kavmilter.global]";
			$conf[]= "RunAsUid=kav";
			$conf[]= "RunAsGid=kav";
			$conf[]= "ServiceSocket=inet:1052@localhost";
			$conf[]= "MilterTimeout=$MilterTimeout";
			$conf[]= "WatchdogMaxRetries=10";
			$conf[]= "TempDir=/var/db/kav/5.6/kavmilter/tmp/";
			while (list ($num, $val) = each ($hash) ){
				if($num==null){continue;}
				$conf[]= "LicensedUsersDomains=$num";
			}
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[kavmilter.snmp]";
			$conf[]= "SNMPServices=none";
			$conf[]= "SNMPTraps=none";
			$conf[]= "AlertThreshold=10";
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[kavmilter.agentx]";
			$conf[]= "Socket=/var/agentx/master";
			$conf[]= "PingInterval=30";
			$conf[]= "Timeout=5";
			$conf[]= "Retries=10";
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[kavmilter.engine]";
			$conf[]= "MaxScanRequests=$MaxScanRequests";
			$conf[]= "MaxScanTime=$MaxScanTime";
			$conf[]= "ScanArchives=$ScanArchives";
			$conf[]= "ScanPacked=$ScanPacked";
			$conf[]= "ScanCodeanalyzer=$ScanCodeanalyzer";
			$conf[]= "UseAVBasesSet=$UseAVBasesSet";
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[kavmilter.log]";
			$conf[]= "LogFacility=syslog";
			$conf[]= "LogFilepath=/var/log/kav/5.6/kavmilter/kavmilter.log";
			$conf[]= "LogOption=all";
			$conf[]= "LogOption=-all.debug";
			$conf[]= "LogRotate=yes";
			$conf[]= "RotateSize=5MB";
			$conf[]= "RotateRounds=5";
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[kavmilter.statistics]";
			$conf[]= "TrackStatistics=all";
			$conf[]= "DataFormat=text";
			$conf[]= "DataFile=/var/log/kav/5.6/kavmilter/statistics.data";
			$conf[]= "MessageStatistics=/var/log/kav/5.6/kavmilter/message-statistics.data";
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[path]";
			$conf[]= "BasesPath=$bases";
			$conf[]= "LicensePath=/var/db/kav/5.6/kavmilter/licenses/";
			$conf[]= "";
			$conf[]="";
			$conf[]="[locale]";
			$conf[]="DateFormat=%Y-%m-%d";
			$conf[]="TimeFormat=%H:%M:%S";
			$conf[]="";
			$conf[]="";
			$conf[]="[updater.path]";
			$conf[]="UploadPatchPath=/var/db/kav/5.6/kavmilter/patches/";
			$conf[]="BackUpPath=/var/db/kav/5.6/kavmilter/bases/backup/";
			$conf[]="AVBasesTestPath=/opt/kav/5.6/kavmilter/bin/avbasestest";
			$conf[]="";
			$conf[]="";
			$conf[]="[updater.options]";
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
			$conf[]="UpdateServerUrl=$UseUpdateServerUrl";
			$conf[]="RegionSettings=$RegionSettings";
			$conf[]="ConnectTimeout=30";
			$conf[]="ProxyAddress={$conf_upd->GET("ProxyAddress")}";
			$conf[]="PassiveFtp=yes";
			$conf[]="PostUpdateCmd=/opt/kav/5.6/kavmilter/bin/kavmilter -r bases";
			$conf[]="ConnectTimeout=30";
			$conf[]= "UseProxy=$UseProxy";
			$conf[]= "";
			$conf[]= "";
			$conf[]= "[updater.report]";
			$conf[]= "Append=no";
			$conf[]= "ReportFileName=/var/log/kav/5.6/kavmilter/keepup2date.log";
			$conf[]= "ReportLevel=3";	
			@file_put_contents("/etc/kav/5.6/kavmilter/kavmilter.conf",implode("\n",$conf));
			DefaultTemplates();

}

function DefaultTemplates(){
$f[]="/var/db/kav/5.6/kavmilter/templates/message_default_notify";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_infected_warn";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_suspicious_warn";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_error_warn";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_filtered_warn";
$f[]="/var/db/kav/5.6/kavmilter/templates/part_infected_deleted";
$f[]="/var/db/kav/5.6/kavmilter/templates/part_suspicious_deleted";
$f[]="/var/db/kav/5.6/kavmilter/templates/part_filtered_deleted";
$f[]="/var/db/kav/5.6/kavmilter/templates/part_filtered_renamed";
$f[]="/var/db/kav/5.6/kavmilter/templates/part_protected_deleted";
$f[]="/var/db/kav/5.6/kavmilter/templates/part_error_deleted";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_sender_notify";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_recipients_notify";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_admin_notify";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_admin_update";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_admin_discarded";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_scan_mismatch";
$f[]="/var/db/kav/5.6/kavmilter/templates/message_admin_fault";

while (list ($num, $val) = each ($f) ){
	if(!is_file($val)){@file_put_contents($val,"#");}
}

	
}





?>