<?php
ini_set("memory_limit","400M");
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc")){include_once(dirname(__FILE__) . '/class.templates.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc");}
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc");}
include_once(dirname(__FILE__).'/class.artica.inc');

class kavmilterd{
	var $main_array;
	var $rule_array;
	var $rule_notify_array;
	var $PolicyNumber_array=array();
	var $stats_array;
	var $KavMilterdGroup;
	var $milter_enabled;
	var $RuleDatas;
	var $ArticaRuleName;
	
	
	function kavmilterd(){
		$this->GetKavMilterConf();
		
	}
	
	
	function GetSatus(){
		$sock=new sockets();
		$datas=trim(base64_decode($sock->getFrameWork('cmd.php?kavmilter-mem=yes')));
		
		$pattern_date=$this->ParsePatternDate();
		if(trim($datas)==null){
			writelogs("kavmilter_mem -> no datas.. error ???",__FUNCTION__ . '/'. __CLASS__,__FILE__);
			$img="danger32.png";
			$text="<h6>{some_service_is_stopped}</h6>";
		}else{
			$img="ok32.png";
			$tbl=explode(";",$datas);
			$count=count($tbl)-1;
			$html="
			
			<i>{running} $count {processes}</i>
			<table style=width:100%'>";
			while (list ($num, $val) = each ($tbl) ){
				if(preg_match('#(.+?)=(.+)#',$val,$re)){
					$html=$html . "<tr>
					<td><strong>kavmilter</strong>
					<td>{$re[1]}</td><td><strong>{$re[2]} mb</strong></td></tr>";
					
				}
				
			
			}$html=$html . "</table>";$text=$html;}
			
				
			return "
			<table>
			<tr>
			<td width=1% valign='top'><img src='img/$img'></td>
			<td valing='top'><H5>{status}</H5>
				$text
			
			</td>
			</tr>
			<tr>
			<td colspan=2><br>" . RoundedLightGrey("<strong>{ave_pattern_date}</strong><br> <strong>$pattern_date</strong>")."</td>
			</tr>
			</table>";
			
		
		
		
		
		
	}
	

	
	
	
	function ParsePatternDate(){
		$sock=new sockets();
		$data=base64_decode($sock->getFrameWork("cmd.php?kavmilter-pattern=yes"));
		if(preg_match('#([0-9]{1,2})([0-9]{1,2})([0-9]{1,4});([0-9]{1,2})([0-9]{1,2})#',$data,$regs)){
			return "".$regs[3]. "/" .$regs[2]. "/" .$regs[1] . " " . $regs[4] . ":" . $regs[5]  . ' (moscow GMT)';
		}
		
	}
	
	function GetKavMilterConf(){
		$sock=new sockets();
		$kavmilterconf=$sock->GET_INFO("kavmilterConf");
		
		if(trim($kavmilterconf)==null){
			$sock->SaveConfigFile($this->BuildConf(),"kavmilterConf");
			$kavmilterconf=$sock->GET_INFO("kavmilterConf");
		}
		$this->milter_enabled=$sock->GET_INFO("kavmilterEnable");
		
		$tbl=explode("\n",$kavmilterconf);
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#(.+?)=(.*)#',$val,$res)){
				$this->main_array[trim($res[1])]=trim($res[2]);
			}
		}
		
		}
		
	function SaveToLdap($rebuild=0,$norestart=0){
		$sock=new sockets();
		$sock->SaveConfigFile($this->BuildConf(),"kavmilterConf");
		$sock->SET_INFO("kavmilterEnable",$this->milter_enabled);
		if($norestart==0){$sock->getFrameWork('cmd.php?kavmilter-configure=yes');}
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("KavMilter:Global {success}\n");
		return true;
		
	}
	

	
	
	function BuildConf(){
		   if($this->main_array["MaxScanRequests"]==null){$this->main_array["MaxScanRequests"]=0;}
		   if($this->main_array["MilterTimeout"]==null){$this->main_array["MilterTimeout"]=600;}
		   if($this->main_array["MaxScanTime"]==null){$this->main_array["MaxScanTime"]=10;}
		   
		   if($this->main_array["MaxScanTime"]==null){$this->main_array["MaxScanTime"]=10;}
		   if($this->main_array["ScanArchives"]==null){$this->main_array["ScanArchives"]="yes";}
		   if($this->main_array["ScanPacked"]==null){$this->main_array["ScanPacked"]="yes";}
		   if($this->main_array["ScanCodeanalyzer"]==null){$this->main_array["ScanCodeanalyzer"]="yes";}
		   if($this->main_array["UseAVBasesSet"]==null){$this->main_array["UseAVBasesSet"]="standard";}
		   if($this->main_array["LogFacility"]==null){$this->main_array["LogFacility"]="syslog";}
		   if($this->main_array["LogRotate"]==null){$this->main_array["LogRotate"]="yes";}
		   if($this->main_array["RotateSize"]==null){$this->main_array["RotateSize"]="1";}
		   if($this->main_array["RotateRounds"]==null){$this->main_array["RotateRounds"]="10";}
		   if($this->main_array["UseUpdateServerUrl"]==null){$this->main_array["UseUpdateServerUrl"]="no";}
		   if($this->main_array["UpdateServerUrl"]==null){$this->main_array["UpdateServerUrl"]="ftp://downloads1.kaspersky-labs.com";}
		   if($this->main_array["UseUpdateServerUrlOnly"]==null){$this->main_array["UseUpdateServerUrlOnly"]="no";}
		   if($this->main_array["RegionSettings"]==null){$this->main_array["RegionSettings"]="Europe";}
		   if($this->main_array["UseProxy"]==null){$this->main_array["UseProxy"]="no";}
		   if($this->main_array["PassiveFtp"]==null){$this->main_array["PassiveFtp"]="no";}
		   
		   if(preg_match('#([0-9]+)#',$this->main_array["RotateSize"],$re)){$this->main_array["RotateSize"]=$re[1];}
		   $sock=new sockets();
			$SendmailPath=trim(base64_decode($sock->getFrameWork("cmd.php?SendmailPath=yes")));
			$ldap=new clladp();
			$hash=$ldap->hash_get_all_domains();
		
			$conf=$cont. "[kavmilter.global]\n";
			$conf=$conf . "\n";
			$conf=$conf . "RunAsUid=kav\n";
			$conf=$conf . "RunAsGid=kav\n";
			$conf=$conf . "ServiceSocket=inet:1052@localhost\n";
			$conf=$conf . "MilterTimeout={$this->main_array["MilterTimeout"]}\n";
			$conf=$conf . "WatchdogMaxRetries=10\n";
			$conf=$conf . "TempDir=/var/db/kav/5.6/kavmilter/tmp/\n";
			while (list ($num, $val) = each ($hash) ){
				if($num==null){continue;}
				$conf=$conf . "LicensedUsersDomains=$num\n";
			}
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[kavmilter.snmp]\n";
			$conf=$conf . "SNMPServices=none\n";
			$conf=$conf . "SNMPTraps=none\n";
			$conf=$conf . "AlertThreshold=10\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[kavmilter.agentx]\n";
			$conf=$conf . "Socket=/var/agentx/master\n";
			$conf=$conf . "PingInterval=30\n";
			$conf=$conf . "Timeout=5\n";
			$conf=$conf . "Retries=10\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[kavmilter.engine]\n";
			$conf=$conf . "MaxScanRequests={$this->main_array["MaxScanRequests"]}\n";
			$conf=$conf . "MaxScanTime={$this->main_array["MaxScanTime"]}\n";
			$conf=$conf . "ScanArchives=yes\n";
			$conf=$conf . "ScanPacked={$this->main_array["ScanPacked"]}\n";
			$conf=$conf . "ScanCodeanalyzer={$this->main_array["ScanCodeanalyzer"]}\n";
			$conf=$conf . "UseAVBasesSet={$this->main_array["UseAVBasesSet"]}\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[kavmilter.log]\n";
			$conf=$conf . "LogFacility=syslog\n";
			$conf=$conf . "LogFilepath=/var/log/kav/5.6/kavmilter/kavmilter.log\n";
			$conf=$conf . "LogOption=all\n";
			$conf=$conf . "LogOption=-all.debug\n";
			$conf=$conf . "LogRotate={$this->main_array["LogRotate"]}\n";
			$conf=$conf . "RotateSize={$this->main_array["RotateSize"]}MB\n";
			$conf=$conf . "RotateRounds={$this->main_array["RotateRounds"]}\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[kavmilter.statistics]\n";
			$conf=$conf . "TrackStatistics=all\n";
			$conf=$conf . "DataFormat=text\n";
			$conf=$conf . "DataFile=/var/log/kav/5.6/kavmilter/statistics.data\n";
			$conf=$conf . "MessageStatistics=/var/log/kav/5.6/kavmilter/message-statistics.data\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[path]\n";
			$conf=$conf . "BasesPath=/var/db/kav/5.6/kavmilter/bases/\n";
			$conf=$conf . "LicensePath=/var/db/kav/5.6/kavmilter/licenses/\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[locale]\n";
			$conf=$conf . "DateFormat=%d-%m-%Y\n";
			$conf=$conf . "TimeFormat=%H:%M:%S\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[updater.path]\n";
			$conf=$conf . "UploadPatchPath=/var/db/kav/5.6/kavmilter/patches/\n";
			$conf=$conf . "BackUpPath=/var/db/kav/5.6/kavmilter/bases/backup/\n";
			$conf=$conf . "AVBasesTestPath=/opt/kav/5.6/kavmilter/bin/avbasestest\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[updater.options]\n";
			$conf=$conf . "KeepSilent=no\n";
			
			$conf=$conf . "UseUpdateServerUrl={$this->main_array["UseUpdateServerUrl"]}\n";
			$conf=$conf . "UpdateServerUrl={$this->main_array["UpdateServerUrl"]}\n";
			$conf=$conf . "UseUpdateServerUrlOnly={$this->main_array["UseUpdateServerUrlOnly"]}\n";
			$conf=$conf . "RegionSettings={$this->main_array["RegionSettings"]}\n";
			$conf=$conf . "PostUpdateCmd=/opt/kav/5.6/kavmilter/bin/kavmilter -r bases\n";
			$conf=$conf . "ConnectTimeout=30\n";
			
			$artica=new artica_general();
			if($artica->ArticaProxyServerEnabled=='yes'){
				$this->main_array["UseProxy"]='yes';
				$this->main_array["ProxyAddress"]=$artica->ArticaCompiledProxyUri;
			}
			
			$conf=$conf . "UseProxy={$this->main_array["UseProxy"]}\n";
			$conf=$conf . "ProxyAddress={$this->main_array["ProxyAddress"]}\n";
			$conf=$conf . "PassiveFtp={$this->main_array["PassiveFtp"]}\n";
			$conf=$conf . "\n";
			$conf=$conf . "\n";
			$conf=$conf . "[updater.report]\n";
			$conf=$conf . "Append=no\n";
			$conf=$conf . "ReportFileName=/var/log/kav/5.6/kavmilter/keepup2date.log\n";
			$conf=$conf . "ReportLevel=3\n";		
			return $conf;		
		
	}
	
	
	function LoadRule($ou,$noolopp=false){
		
		writelogs("Load kav milter rule group name \"$ou\"",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		$sock=new sockets();
		$this->ArticaRuleName=$ou;
		$sql="SELECT config FROM kavmilter WHERE ou='$ou'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$this->RuleDatas=base64_decode($ligne["config"]);

		if($this->RuleDatas==null){
			writelogs("Datas of \"{$ou}_KavMilterGroupRule\" is null save a new one",__CLASS__ . '/' . __FUNCTION__,__FILE__);
			if(strtolower($rule_name)=='default'){$level=0;}else{$level="10";}
			$this->rule_array["GroupName"]=$ou;
			$this->rule_array["Priority"]=$level;
			$this->SaveRuleToLdap();
			if(!$noolopp){$this->LoadRule($ou,true);}
		}
		writelogs("Datas of \"{$ou}\" are ".strlen($this->RuleDatas)." bytes len",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		if(strlen(trim($this->RuleDatas))==0){
			writelogs("Datas of \"{$ou}\" is defenitively bugged, abort..",__CLASS__ . '/' . __FUNCTION__,__FILE__);
			return null;
		}
		
			// load rules value ------------------------------------
			$tbl=explode("\n",$this->RuleDatas);
			while (list ($num, $val) = each ($tbl) ){
				if(preg_match('#(.+?)=(.+?)\.(.+)#',$val,$res)){
					$this->rule_array[trim($res[1])]=trim($res[2]);
				}
				
				if(preg_match('#^Notify(.+)=([a-z]+)#',$val,$res)){
					$this->rule_notify_array[trim($res[1])][trim($res[2])]=trim($res[2]);
				}
				
			}			

				$this->rule_array["Priority"]=$this->GetPolicyNumber($rule_name);
				$this->RuleDefault();
		
		
			
		
	}
	
	function SaveRuleToLdap($echo=0){	
		$datas=$this->BuildRule();
		$sql="SELECT config FROM kavmilter WHERE ou='$this->ArticaRuleName'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["config"]==null){$add=true;}		
		
			$sql="UPDATE kavmilter SET config='".base64_encode($conf)."' WHERE ou='$this->ArticaRuleName';";
			if($add){
				$sql="INSERT INTO kavmilter(ou,config) VALUES('$this->ArticaRuleName','".base64_encode($conf)."')";
			}
			
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				echo $q->mysql_error;
				return;
			}		
		
		$sock=new sockets();	
		$sock->getFrameWork('cmd.php?kavmilter-configure=yes');
		if($echo==1){
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body("KavMilter:$rule_name rule {success}\n");		
		}
		return true;
		}
		

	
	
	function RuleDefault(){
		  $rulename=$this->ArticaRuleName;
		   if($this->rule_array["ScanPolicy"]==null){$this->rule_array["ScanPolicy"]="message";}
           if($this->rule_array["AddDisclaimer"]==null){$this->rule_array["AddDisclaimer"]="no";}
           if($this->rule_array["DefaultAction"]==null){$this->rule_array["DefaultAction"]="delete";}
           if($this->rule_array["DefaultAction"]==null){$this->rule_array["DefaultAction"]="delete";}
           if($this->rule_array["SuspiciousAction"]==null){$this->rule_array["SuspiciousAction"]="delete";}
           if($this->rule_array["ProtectedAction"]==null){$this->rule_array["ProtectedAction"]="skip";}
           if($this->rule_array["ErrorAction"]==null){$this->rule_array["ErrorAction"]="skip";}
           if($this->rule_array["VirusNameAction"]==null){$this->rule_array["VirusNameAction"]="drop";}
           if($this->rule_array["UsePlaceholderNotice"]==null){$this->rule_array["UsePlaceholderNotice"]="yes";}
           if($this->rule_array["EnableNotifications"]==null){$this->rule_array["EnableNotifications"]="yes";}
           if($this->rule_array["AdminAddresses"]==null){$this->rule_array["AdminAddresses"]="root@localhost";}
           if($this->rule_array["PostmasterAddress"]==null){$this->rule_array["PostmasterAddress"]="postmaster";}
           
           
           if(!isset($this->rule_notify_array["Sender"])){$this->rule_notify_array["Sender"]["none"]="none";}
           if(!isset($this->rule_notify_array["Recipients"])){$this->rule_notify_array["Recipients"]["none"]="none";}
           if(!isset($this->rule_notify_array["Admin"])){$this->rule_notify_array["Admin"]["none"]="none";}
           
		   if($this->rule_array["MessageSubject"]==null){$this->rule_array["MessageSubject"]="Anti-virus notification message";}
		   if($this->rule_array["SenderSubject"]==null){$this->rule_array["SenderSubject"]="Anti-virus notification message (sender)";}
		   if($this->rule_array["ReceiverSubject"]==null){$this->rule_array["ReceiverSubject"]="Anti-virus notification message (recipient)";}
		   if($this->rule_array["AdminSubject"]==null){$this->rule_array["AdminSubject"]="Anti-virus notification message (Admin)";}
		  	if($rulename<>'default'){if($this->rule_array["Priority"]==0){$this->rule_array["Priority"]=10;}}
		   unset($this->rule_notify_array["Recipient"]);
	}
	
	

	
	function BuildRule(){
			$rulename=$this->ArticaRuleName;
			writelogs("[$rulename]: Building rule",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->RuleDefault();
			if(strtolower($rulename)=='default'){$this->rule_array["Priority"]=0;}
					
				$sock=new sockets();
				$SendmailPath=trim(base64_decode($sock->getFrameWork("cmd.php?SendmailPath=yes")));
				$conf[]="[group.definition]";
				$conf[]="GroupName=$rulename ";
				$conf[]="Priority={$this->rule_array["Priority"]}";
				$conf[]="Recipients=";
				$conf[]="Senders=";
				$conf[]="";
				$conf[]="[group.settings]";
				$conf[]=" ";
				$conf[]="ScanPolicy={$this->rule_array["ScanPolicy"]}";
				$conf[]="AddXHeaders=yes";
				$conf[]="AddDisclaimer={$this->rule_array["AddDisclaimer"]}";
				$conf[]="";
				$conf[]="[group.actions] ";
				$conf[]="DefaultAction={$this->rule_array["DefaultAction"]}";
				$conf[]="SuspiciousAction={$this->rule_array["SuspiciousAction"]}";
				$conf[]="ProtectedAction={$this->rule_array["ProtectedAction"]}";
				$conf[]="ErrorAction={$this->rule_array["ErrorAction"]}";
				$conf[]="VirusNameAction={$this->rule_array["VirusNameAction"]}";
				$conf[]="VirusNameList=";
				$conf[]="UsePlaceholderNotice={$this->rule_array["UsePlaceholderNotice"]}";
				$conf[]="";
				$conf[]="[group.filter]";
				$conf[]="IncludeMime=";
				$conf[]="ExcludeMime=";
				$conf[]="IncludeName=  ";
				$conf[]="ExcludeName=";
				$conf[]="IncludeSize=";
				$conf[]="ExcludeSize= ";
				$conf[]="FilteredMimeAction=skip";
				$conf[]="FilteredNameAction=skip  ";
				$conf[]="FilteredSizeAction=skip";
				$conf[]="";
				$conf[]="[group.notifications]";
				$conf[]="EnableNotifications=yes ";
				$conf[]="NotifySender=none";
				$conf[]="NotifyRecipients=all ";
				$conf[]="NotifyAdmin=none";
				$conf[]="AdminAddresses={$this->rule_array["AdminAddresses"]}";
				$conf[]="PostmasterAddress=postmaster";
				$conf[]="MessageDir=/var/db/kav/5.6/kavmilter/templates";
				$conf[]="MessageSubject=Anti-virus notification message";
				$conf[]="Charset=us-ascii";
				$conf[]="TransferEncoding=7bit";
				$conf[]="RejectReply=Message rejected because it contains malware";
				$conf[]="SendmailPath=$SendmailPath";
				$conf[]="UseCustomTemplates=no";
				$conf[]="SenderSubject=Anti-virus sender notification";
				$conf[]="ReceiverSubject=Anti-virus receiver notification";
				$conf[]="AdminSubject=Anti-virus administrator notification";
				$conf[]="";
				$conf[]="[group.backup]";
				$conf[]="BackupPolicy=info";
				$conf[]="BackupOption=all ";
				$conf[]="BackupDir=/var/db/kav/5.6/kavmilter/backup/";			
			
				$old_rule=implode("\n",$conf);
				unset($conf);
				
				$conf[]="[kav4lms:groups.$rulename]";
				$conf[]="";
				$conf[]="[kav4lms:groups.$rulename.definition]";
				$conf[]="Priority={$this->rule_array["Priority"]}";
				$conf[]="Recipients=";
				$conf[]="Senders=";
				$conf[]="";
				$conf[]="[kav4lms:groups.$rulename.settings]";
				$conf[]="AddDisclaimer={$this->rule_array["AddDisclaimer"]}";
				$conf[]="AddXHeaders=message";
				$conf[]="Check=all";
				$conf[]="MIMEEncodingHeuristics=no";
				$conf[]="MaxScanDepth=10";
				$conf[]="MaxScanTime=30";
				$conf[]="RejectReply=Message rejected because it contains malware";
				$conf[]="ScanArchives=yes";
				$conf[]="ScanPacked=yes";
				$conf[]="ScanPolicy=message";
				$conf[]="UseAVBasesSet=standard";
				$conf[]="UseCodeAnalyzer=yes";
				$conf[]="UsePlaceholderNotice={$this->rule_array["UsePlaceholderNotice"]}";
				$conf[]="";
				$conf[]="[kav4lms:groups.$rulename.actions]";
				$conf[]="ErrorAction={$this->rule_array["ErrorAction"]}";
				$conf[]="FilteredMimeAction=skip";
				$conf[]="FilteredNameAction=skip";
				$conf[]="FilteredSizeAction=skip";
				$conf[]="InfectedAction=skip";
				$conf[]="ProtectedAction={$this->rule_array["ProtectedAction"]}";
				$conf[]="SuspiciousAction={$this->rule_array["SuspiciousAction"]}";
				$conf[]="VirusNameAction={$this->rule_array["VirusNameAction"]}";
				$conf[]="";
				$conf[]="[kav4lms:groups.$rulename.contentfiltering]";
				$conf[]="ExcludeMime=";
				$conf[]="ExcludeName=";
				$conf[]="ExcludeSize=";
				$conf[]="IncludeMime=";
				$conf[]="IncludeName=";
				$conf[]="IncludeSize=";
				$conf[]="RenameTo=.vir";
				$conf[]="VirusNameList=";
				$conf[]="";
				$conf[]="[kav4lms:groups.$rulename.notifications]";
				$conf[]="AdminAddresses=postmaster";
				$conf[]="AdminSubject=Anti-virus administrator notification";
				$conf[]="Charset=us-ascii";
				$conf[]="NotifyAdmin=none";
				$conf[]="NotifyRecipients=all";
				$conf[]="NotifySender=none";
				$conf[]="PostmasterAddress=POSTMASTER@localhost";
				$conf[]="RecipientsSubject=Anti-virus recipient notification";
				$conf[]="SenderSubject=Anti-virus sender notification";
				$conf[]="Subject=Anti-virus notification message";
				$conf[]="Templates=/etc/opt/kaspersky/kav4lms/templates/en";
				$conf[]="TransferEncoding=7bit";
				$conf[]="UseCustomTemplates=no";
				$conf[]="";
				$conf[]="[kav4lms:groups.$rulename.backup]";
				$conf[]="Destination=/var/opt/kaspersky/kav4lms/backup/";
				$conf[]="Options=all";
				$conf[]="Policy=info";				
				
			
			return $rule;
		
		
	}
	
	function BuildStatistics(){
		$sock=new sockets();
		$datas=$sock->getfile('kavmilter_stats');
		$tbl=explode("\n",$datas);
		
		if(!is_array($tbl)){return null;}
		while (list ($num, $val) = each ($tbl) ){
		
			if(preg_match('#(.+?)=(.*)#',$val,$res)){
					$this->stats_array[$res[1]]=$res[2];
				}
			}
		}
	
	
	
}