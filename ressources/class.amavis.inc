<?php

include_once(dirname(__FILE__).'/class.main_cf.inc');
include_once(dirname(__FILE__).'/class.ini.inc');
include_once(dirname(__FILE__).'/class.system.network.inc');
include_once(dirname(__FILE__).'/class.maincf.multi.inc');
class amavis{
	
	var $dn;
	var $suffix;
	var $main_array;
	var $amavis_conf;
	var $ldap_error;
	var $table_domains;
	var $ArticaConfig;
	var $extensions=array();
	var $copy_to_domain=null;
	var $copy_to_domain_array=array();
	var $CopyToDomainSpool;
	var $EnableScanSecurity;
	var $AlterMimeHTMLDisclaimer;
	var $EnableAmavisInMasterCF=0;
	var $EnableBlockUsersTroughInternet=0;
	var $EnableQuarantineSpammy=0;
	var $EnableQuarantineSpammy2=0;
	var $EnableLDAPAmavis=0;
	var $EnableAmavisDKIMVerification=0;
	var $AmavisNoInternetTests=0;
	
	function amavis(){
	if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}else{$GLOBALS["AS_ROOT"]=false;}}
		writelogs("init amavis class....",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock=new sockets();
		writelogs("socket initialized",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->ArticaConfig=$sock->GET_INFO('AmavisGlobalConfiguration');
		$this->amavis_conf=$sock->GET_INFO('AmavisConfigFile');
		$this->copy_to_domain=$sock->GET_INFO("AmavisCopyToDomain");
		$this->CopyToDomainSpool=$sock->GET_INFO("CopyToDomainSpool");
		$this->EnableScanSecurity=$sock->GET_INFO("EnableScanSecurity");
		$this->EnableAmavisInMasterCF=$sock->GET_INFO("EnableAmavisInMasterCF");
		$this->EnableBlockUsersTroughInternet=$sock->GET_INFO("EnableBlockUsersTroughInternet");
		$this->EnableQuarantineSpammy=$sock->GET_INFO("EnableQuarantineSpammy");
		$this->EnableQuarantineSpammy2=$sock->GET_INFO("EnableQuarantineSpammy2");
		$this->EnableLDAPAmavis=$sock->GET_INFO("EnableLDAPAmavis");
		$this->EnableDKIMVerification=$sock->GET_INFO("EnableAmavisDKIMVerification");
		if(!is_numeric($this->EnableLDAPAmavis)){$this->EnableLDAPAmavis=0;}
		if(!is_numeric($this->EnableDKIMVerification)){$this->EnableDKIMVerification=0;}
		if(!is_numeric($this->AmavisNoInternetTests)){$this->AmavisNoInternetTests=1;}
		
		
		writelogs("this->EnableScanSecurity=$this->EnableScanSecurity",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($this->CopyToDomainSpool==null){
			writelogs("CopyToDomainSpool=/var/spool/artica/copy-to-domain",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->CopyToDomainSpool="/var/spool/artica/copy-to-domain";
			$sock->SET_INFO("CopyToDomainSpool","/var/spool/artica/copy-to-domain");
		}
		
		if($this->EnableScanSecurity==null){
			writelogs("SET_INFO:: EnableScanSecurity=1",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$sock->SET_INFO("EnableScanSecurity",1);
			$this->EnableScanSecurity=1;
		}
		
		
		
		writelogs("Parse Settings....",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->ParseGlobalSettings();
		writelogs("ParseGlobalSettings initialized",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->BuildDefaults();
		writelogs("BuildDefaults initialized",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->ParseCopyToDomains();
		writelogs("ParseCopyToDomains initialized",__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("Loading amavis class done ....",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		
	function DisclaimerExample(){
		
		return "<p style=\"font-size:12px\"><i>This email and its attachments may be confidential and are intended solely for the use of the individual to whom it is addressed.<br>
		 Any views or opinions expressed are solely those of the author and do not necessarily represent those of &laquo;[business name]&raquo;.<br>
		If you are not the intended recipient of this email and its attachments, you must take no action based upon them, nor must you copy or show them to anyone.<br>
		<br><br>Please contact the sender if you believe you have received this email in error.</i></p>";
		
	}
	

		
	function ParseGlobalSettings(){
			$ini=new Bs_IniHandler();
			$ini->loadString($this->ArticaConfig);
			$this->main_array=$ini->_params;
			if(trim($this->main_array["FILTER"]["EXT"])<>null){
				$this->extensions=explode(';',$this->main_array["FILTER"]["EXT"]);
			}
			if(!is_array($this->extensions)){$this->extensions=array();}
			}
			
	function del_extentions($ext){
		$myarray=$this->extensions;
		writelogs("deleting banned extentions \"$ext\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(is_array($this->extensions)){
			while (list ($num, $ligne) = each ($this->extensions) ){
				if($ligne==$ext){
					unset($this->extensions[$num]);
				}
				}
		}
		$result=implode(';',$this->extensions);
		writelogs("adding banned extentions after filter \"$result\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->main_array["FILTER"]["EXT"]=$result;
		reset($this->extensions);
		$this->Save();
		
	}
	
	
	function ParseCopyToDomains(){
		$ini=new Bs_IniHandler();
		$ini->loadString($this->copy_to_domain);
		if(!is_array($ini->_params)){$this->copy_to_domain_array=array();return false;}
		$this->copy_to_domain_array=$ini->_params;
		}
		
	function SaveCopyToDomains(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->copy_to_domain_array;
		$sock=new sockets();
		$sock->SaveConfigFile($ini->toString(),"AmavisCopyToDomain");
		
	}
	
	
			
	function add_extentions($ext){
		$ext=trim($ext);
		writelogs("adding banned extentions \"$ext\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$myarray=$this->extensions;
		if(is_array($myarray)){
			while (list ($num, $ligne) = each ($myarray) ){
				$ext_arr[$ligne]=$ligne;
				}
		}
		
		
		$array=explode(" ",$ext);
		if(!is_array($array)){return false;}
		while (list ($num, $ligne) = each ($array) ){
			
			$ext_arr[$ligne]=$ligne;
		}
		
		unset($this->extensions);
		while (list ($num, $ligne) = each ($ext_arr) ){
			$this->extensions[]=$ligne;
		}
		$result=implode(';',$this->extensions);
		writelogs("adding banned extentions after filter \"$result\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->main_array["FILTER"]["EXT"]=$result;
		reset($this->extensions);
		$this->Save();
		
		
	}
		
		
	function BuildDefaults(){
		
		if($this->main_array["NETWORK"]["TrustLocalHost"]==null){
			$this->main_array["NETWORK"]["TrustLocalHost"]=0;
			}
		if($this->main_array["NETWORK"]["LocalNetwork"]==null){$this->main_array["NETWORK"]["LocalNetwork"]="10.0.0.0/8,172.16.0.0/12,192.168.0.0/16";}
		if($this->main_array["NETWORK"]["banned_extensions_include_local_net"]==null){$this->main_array["NETWORK"]["banned_extensions_include_local_net"]=0;}
		
		if($this->main_array["BEHAVIORS"]["final_virus_destiny"]==null){$this->main_array["BEHAVIORS"]["final_virus_destiny"]="D_DISCARD";}
		if($this->main_array["BEHAVIORS"]["final_spam_destiny"]==null){$this->main_array["BEHAVIORS"]["final_spam_destiny"]="D_BOUNCE";}
		if($this->main_array["BEHAVIORS"]["final_bad_header_destiny"]==null){$this->main_array["BEHAVIORS"]["final_bad_header_destiny"]="D_PASS";}
		if($this->main_array["BEHAVIORS"]["final_banned_destiny"]==null){$this->main_array["BEHAVIORS"]["final_banned_destiny"]="D_BOUNCE";}
		
		if($this->main_array["BEHAVIORS"]["warnbadhsender"]==null){$this->main_array["BEHAVIORS"]["warnbadhsender"]="0";}
		if($this->main_array["BEHAVIORS"]["warnvirusrecip"]==null){$this->main_array["BEHAVIORS"]["warnvirusrecip"]="0";}
		if($this->main_array["BEHAVIORS"]["warnbannedrecip"]==null){$this->main_array["BEHAVIORS"]["warnbannedrecip"]="0";}
		if($this->main_array["BEHAVIORS"]["warnbadhrecip"]==null){$this->main_array["BEHAVIORS"]["warnbadhrecip"]="0";}
		if($this->main_array["BEHAVIORS"]["warn_offsite"]==null){$this->main_array["BEHAVIORS"]["warn_offsite"]="0";}
		
		if($this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]==null){$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]="root@localhost.localdomain";}
		if($this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]==null){$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]="root@localhost.localdomain";}
		if($this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]==null){$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]="root@localhost.localdomain";}
		if($this->main_array["BEHAVIORS"]["virus_admin"]==null){$this->main_array["BEHAVIORS"]["virus_admin"]="undef";}
		
		
		
		if($this->main_array["BEHAVIORS"]["sa_tag_level_deflt"]==null){$this->main_array["BEHAVIORS"]["sa_tag_level_deflt"]="-999";}
		if($this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]==null){$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]="6.31";}
		if($this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]==null){$this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]='10.0';}
		if($this->main_array["BEHAVIORS"]["sa_dsn_cutoff_level"]==null){$this->main_array["BEHAVIORS"]["sa_dsn_cutoff_level"]="9";}
		if($this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]==null){$this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]='15';}
		if($this->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]==null){$this->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]="no";}
		
		if($this->main_array["BEHAVIORS"]["spam_subject_tag2_maps"]==null){$this->main_array["BEHAVIORS"]["spam_subject_tag2_maps"]="***SPAM*** _SCORE_ (_REQD_)";}
		if($this->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"]==null){$this->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"]='25';}
		if($this->main_array["BEHAVIORS"]["log_level"]==null){$this->main_array["BEHAVIORS"]["log_level"]="1";}
		if($this->main_array["BEHAVIORS"]["sa_timeout"]==null){$this->main_array["BEHAVIORS"]["sa_timeout"]="30";}
		
		
			
		
		if(!is_numeric($this->main_array["BEHAVIORS"]["max_servers"])){
			if($GLOBALS["AS_ROOT"]){echo "Starting......: amavisd-new max_servers is not set, define to 5\n";}
			$this->main_array["BEHAVIORS"]["max_servers"]="5";
		}
		if($this->main_array["BEHAVIORS"]["child_timeout"]==null){$this->main_array["BEHAVIORS"]["child_timeout"]="5*600";}
		if($this->main_array["BEHAVIORS"]["max_requests"]==null){$this->main_array["BEHAVIORS"]["max_requests"]="30";}	
		
		$this->main_array["BEHAVIORS"]["virus_admin"]=str_replace('\\','',$this->main_array["BEHAVIORS"]["virus_admin"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]=str_replace("\\","",$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]=str_replace("\\","",$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]=str_replace("\\","",$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]);		
		
		}
		
		
	
	
	function Save(){
		$ini=new Bs_IniHandler();
		
		
		$ini->_params=$this->main_array;
		$sock=new sockets();
		$this->AlterMimeHTMLDisclaimer=stripslashes($this->AlterMimeHTMLDisclaimer);
		$sock->SET_INFO("EnableScanSecurity",$this->EnableScanSecurity);
		$sock->SET_INFO("EnableAmavisInMasterCF",$this->EnableAmavisInMasterCF);
		$sock->SET_INFO("EnableBlockUsersTroughInternet",$this->EnableBlockUsersTroughInternet);
		$sock->SET_INFO("EnableQuarantineSpammy",$this->main_array["BEHAVIORS"]["spam_quarantine_spammy"]);
		$sock->SET_INFO("EnableQuarantineSpammy2",$this->main_array["BEHAVIORS"]["spam_quarantine_spammy2"]);
		
		writelogs("Saving AlterMimeTXTDisclaimer configuration with cleaned ".strlen($disctxt),__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("Saving amavis configuration...",__CLASS__.'/'.__FUNCTION__,__FILE__);

		$sock->SaveConfigFile($ini->toString(),'AmavisGlobalConfiguration');
		$sock->getFrameWork("cmd.php?amavis-restart=yes");
		
		
		
		if($this->main_array["BEHAVIORS"]["replicate_conf_all_domains"]==1){
			$ldap=new clladp();
			$arr=$ldap->hash_get_all_domains();
			if(is_array($arr)){
				while (list ($num, $val) = each ($arr) ){
					$dom=new DomainsTools();
					$dom->LoadAmavisDomain($num);
					$dom->SetDefaultAmavisConfig();
					
					}
				}
		}
		
		
		
		
		}
		
function CleanHtml($data){
	$data=str_replace("\n",'',$data);
	return $data;
}

function BuildNetworks(){
	include_once(dirname(__FILE__).'/class.tcpip.inc');
	if($this->main_array["NETWORK"]["TrustLocalHost"]==1){
		$net[]="127.0.0.0/8";
	}
    
	$ldap=new clladp();
	$tcp=new IP();
	$networks=$ldap->load_mynetworks();
	
	
	
	
	if(is_array($networks)){
		while (list ($num, $val) = each ($networks) ){
			$val=trim($val);
			if($val==null){continue;}
			if($val=="127.0.0.1"){continue;}
			if(preg_match("#127\..+#",$val)){continue;}
			if($tcp->isIPAddressOrRange($val)){
				$net[]=$val; 	
			}else{
				echo "Wrong value $val\n";
			}
			
			
		}
	}
	
	$sql="SELECT * FROM amavisd_bypass ORDER BY ip_addr";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["ip_addr"]=trim($ligne["ip_addr"]);
		$ip=trim($ligne["ip_addr"]);
		if($ip==null){continue;}
		if(is_array($ip)){continue;}
		$aaa=explode(".",$val);if(count($aaa)<3){echo "Wrong value $val\n";continue;}
		$net[]="{$ligne["ip_addr"]}";
	}	
	
	
	if(is_array($net)){
		while (list ($num, $val) = each ($net) ){
			echo "Starting......: amavisd-new Network :$val\n";
		}
	}
	
	
	if(is_array($net)){
		reset($net);
		$conf=implode(' ',$net);
	}
	return $conf;
	
}

function AddNetwork($cdir){
	$net=explode(",",$this->main_array["NETWORK"]["LocalNetwork"]);
	$net[]=$cdir;
	$this->main_array["NETWORK"]["LocalNetwork"]=implode(',',$net);
	$this->Save();
	}
	
private function getInternalIPs(){
	$os=new networking();
	$array=$os->ALL_IPS_GET_ARRAY();
	$array["127.0.0.1"]="127.0.0.1";
	if(!is_array($array)){return null;}
	
	while (list ($num, $ligne) = each ($array) ){
		if(trim($ligne)==null){continue;}
		$newarr[]=$ligne;
	}
	
	return implode(" ",$newarr);
	
}
	
function DelNetwork($index){
	$net=explode(",",$this->main_array["NETWORK"]["LocalNetwork"]);
	unset($net[$index]);
	$this->main_array["NETWORK"]["LocalNetwork"]=@implode(',',$net);
	$this->Save();
	}
	
function GetLocalDomains(){
	//parse ldap table and format maps.
	$ldap=new clladp();
	$hash=$ldap->AllDomains();
	if(!is_array($hash)){return null;}
	while (list ($num, $ligne) = each ($hash) ){
		$domains=$domains ."'$num',";
		
	}
	if(substr($domains,strlen($domains)-1,1)==','){$domains=substr($domains,0,strlen($domains)-1);}
	return $domains;
	}
		
function buildconf(){
	$users=new usersMenus();
	$ldap=new clladp();
	$altermime=false;
	$EnableAmavisInMasterCF=false;
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$AmavisByPassOutgoingMessages=$sock->GET_INFO("AmavisByPassOutgoingMessages");
	$enable_dkim_verification=$sock->GET_INFO("enable_dkim_verification");
	$AmavisDebugSpamassassin=$sock->GET_INFO("AmavisDebugSpamassassin");
	if(!is_numeric($AmavisByPassOutgoingMessages)){$AmavisByPassOutgoingMessages=1;}
	if(!is_numeric($AmavisDebugSpamassassin)){$AmavisDebugSpamassassin=1;}
	$myhostname=$sock->GET_INFO("myhostname");
	if($myhostname==null){
		$myhostname=$users->fqdn;
	}
	
	if(posix_getuid()==0){echo "Starting......: amavisd-new buildconf() EnablePostfixMultiInstance=$EnablePostfixMultiInstance\n";} 
	
	
	if(!preg_match("#\..+?$#",$myhostname)){
		$myhostname="$myhostname.local";
	}
	
	
		$this->main_array["BEHAVIORS"]["virus_admin"]=str_replace('\\','',$this->main_array["BEHAVIORS"]["virus_admin"]);
		$this->main_array["BEHAVIORS"]["spam_admin"]=str_replace('\\','',$this->main_array["BEHAVIORS"]["spam_admin"]);
		$this->main_array["BEHAVIORS"]["banned_admin"]=str_replace('\\','',$this->main_array["BEHAVIORS"]["banned_admin"]);
		$this->main_array["BEHAVIORS"]["bad_header_admin"]=str_replace('\\','',$this->main_array["BEHAVIORS"]["bad_header_admin"]);
		
		$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]=str_replace("\\","",$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]=str_replace("\\","",$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]=str_replace("\\","",$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]);
		if($this->main_array["BEHAVIORS"]["sa_tag_level_deflt"]>$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]){
		$this->main_array["BEHAVIORS"]["sa_tag_level_deflt"]=$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"];}
		
		if($this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]==null){$this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]='10.0';}
		if($this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]==null){$this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]='15';}
		if($this->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"]==null){$this->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"]='25';}
		

		$this->EnableQuarantineSpammy=$this->main_array["BEHAVIORS"]["spam_quarantine_spammy"];
		$this->EnableQuarantineSpammy2=$this->main_array["BEHAVIORS"]["spam_quarantine_spammy2"];
		
		
		if($this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]<$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]){
			$this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]=$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]+1;
		}
		
		if($this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]<$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]){
			$this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]=$this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"];
		}	

		if($this->main_array["BEHAVIORS"]["warnbadhsender"]==null){$this->main_array["BEHAVIORS"]["warnbadhsender"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnvirussender"]==null){$this->main_array["BEHAVIORS"]["warnvirussender"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnspamsender"]==null){$this->main_array["BEHAVIORS"]["warnspamsender"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnbannedsender"]==null){$this->main_array["BEHAVIORS"]["warnbannedsender"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnvirusrecip"]==null){$this->main_array["BEHAVIORS"]["warnvirusrecip"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnbannedrecip"]==null){$this->main_array["BEHAVIORS"]["warnbannedrecip"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnbadhrecip"]==null){$this->main_array["BEHAVIORS"]["warnbadhrecip"]="undef";}
		if($this->main_array["BEHAVIORS"]["warnbannedrecip"]==null){$this->main_array["BEHAVIORS"]["warnbannedrecip"]="undef";}
		if($this->main_array["BEHAVIORS"]["newvirus_admin"]==null){$this->main_array["BEHAVIORS"]["newvirus_admin"]="undef";}
		if($this->main_array["BEHAVIORS"]["virus_admin"]==null){$this->main_array["BEHAVIORS"]["virus_admin"]="undef";}
		if($this->main_array["BEHAVIORS"]["spam_admin"]==null){$this->main_array["BEHAVIORS"]["spam_admin"]="undef";}
		if($this->main_array["BEHAVIORS"]["banned_admin"]==null){$this->main_array["BEHAVIORS"]["banned_admin"]="undef";}
		if($this->main_array["BEHAVIORS"]["bad_header_admin"]==null){$this->main_array["BEHAVIORS"]["bad_header_admin"]="undef";}
		if($this->main_array["BEHAVIORS"]["child_timeout"]==null){$this->main_array["BEHAVIORS"]["child_timeout"]="5*600";}										
		if($this->main_array["BEHAVIORS"]["max_requests"]==null){$this->main_array["BEHAVIORS"]["max_requests"]="30";}
		if($this->main_array["BEHAVIORS"]["max_servers"]==null){$this->main_array["BEHAVIORS"]["max_servers"]="5";}
		if($this->main_array["BEHAVIORS"]["trust_my_net"]==null){$this->main_array["BEHAVIORS"]["trust_my_net"]="0";}
		if($this->main_array["BEHAVIORS"]["sa_timeout"]==null){$this->main_array["BEHAVIORS"]["sa_timeout"]="30";}
		if(!is_numeric($this->main_array["BEHAVIORS"]["enable_db"])){$this->main_array["BEHAVIORS"]["enable_db"]="0";}
		if(!is_numeric($this->main_array["BEHAVIORS"]["sa_timeout"])){$this->main_array["BEHAVIORS"]["sa_timeout"]="30";}
		if(!is_numeric($this->main_array["BEHAVIORS"]["enable_global_cache"])){$this->main_array["BEHAVIORS"]["enable_global_cache"]="0";}
		
		
		
		if(posix_getuid()==0){echo "Starting......: amavisd-new build configuration loglevel:{$this->main_array["BEHAVIORS"]["log_level"]}\n";}
		if(posix_getuid()==0){echo "Starting......: amavisd-new build configuration myhostname:$myhostname\n";}
		
		
		//if($users->ALTERMIME_INSTALLED){if($this->EnableAlterMime==1){$altermime=true;}}	
		if($this->EnableAmavisInMasterCF==1){$EnableAmavisInMasterCF=true;}
		$conf=$conf."use strict;\n";
		$conf=$conf."\n";
		$conf=$conf."# Configuration file with artica builder " . date('Y-m-d H::i:s')."\n";
		$conf=$conf."\n";
		$conf=$conf."include_config_files('/var/amavis-plugins/check-external-users.conf');\n";
		$conf=$conf."\n";
		$conf=$conf."# COMMONLY ADJUSTED SETTINGS:\n";
		$conf=$conf."\n";
		$conf=$conf."# PERFORMANCES:\n";
		$conf=$conf."\$max_servers = {$this->main_array["BEHAVIORS"]["max_servers"]};\n";
		$conf=$conf."\$child_timeout={$this->main_array["BEHAVIORS"]["child_timeout"]};\n";
		$conf=$conf."\$max_requests={$this->main_array["BEHAVIORS"]["max_requests"]};\n";
		$conf=$conf."\$pid_file='/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid';\n";
		$conf=$conf."\$daemon_user = 'postfix';\n";
		$conf=$conf."\$daemon_group = 'postfix';\n";
		$conf=$conf."\$TEMPBASE = \"\$MYHOME/tmp\";   # working directory, needs to exist, -T\n";
		$conf=$conf."\$ENV{TMPDIR} = \$TEMPBASE;    # environment variable TMPDIR, used by SA, etc.\n";
		$conf=$conf."\$QUARANTINEDIR = '/var/virusmails';  # -Q\n";
		$conf=$conf."\$log_level = {$this->main_array["BEHAVIORS"]["log_level"]};# verbosity 0..5, -d\n";		
		$conf=$conf."\$log_recip_templ 	= undef;    # disable by-recipient level-0 log entries\n";
		$conf=$conf."\$DO_SYSLOG = 1;              # log via syslogd (preferred)\n";
		$conf=$conf."\$sa_debug = $AmavisDebugSpamassassin;\n";
		$conf=$conf."\$sa_spawned = 1;\n";
		$conf=$conf."\$sa_timeout = {$this->main_array["BEHAVIORS"]["sa_timeout"]};\n";
		$conf=$conf."\$syslog_facility 	= 'mail';   # Syslog facility as a string\n";
		$conf=$conf."\$syslog_priority 	= 'debug';  # Syslog base (minimal) priority as a string,\n";
		$conf=$conf."#\$LOGFILE 		    = \"/var/log/amavis/amavis.log\";\n";
		$conf=$conf."\$MAXLEVELS = 14;\n";
		$conf=$conf."\$MAXFILES = 1500;\n";
		$conf=$conf."\$MIN_EXPANSION_QUOTA =      100*1024;  # bytes  (default undef, not enforced)\n";
		$conf=$conf."\$MAX_EXPANSION_QUOTA = 300*1024*1024;  # bytes  (default undef, not enforced)\n";
		$conf=$conf."\n";
		$conf=$conf."\$enable_db = {$this->main_array["BEHAVIORS"]["enable_db"]};              # enable use of BerkeleyDB/libdb (SNMP and nanny)\n";
		$conf=$conf."\$enable_global_cache = {$this->main_array["BEHAVIORS"]["enable_global_cache"]};    # enable use of libdb-based cache if \$enable_db=1\n";
		$conf=$conf."\$nanny_details_level = 2;    # nanny verbosity: 1: traditional, 2: detailed\n";
		if($this->EnableDKIMVerification==1){
			$conf=$conf."\$enable_dkim_verification = 1;  # enable DKIM signatures verification\n";
			$conf=$conf."\$enable_dkim_signing = 1;    # load DKIM signing code, keys defined by dkim_key\n";
		}else{
			$conf=$conf."\$enable_dkim_verification = 0;\n";
			$conf=$conf."\$enable_dkim_signing = 0;\n";
		}
		
		if($this->EnableLDAPAmavis==1){
			$conf=$conf."\$ldap_lookups_no_at_means_domain = 1;\n";
			$conf=$conf."\$enable_ldap  = 1;\n";
			$conf=$conf."\$default_ldap = {\n";
			$conf=$conf."\thostname      => [ '$ldap->ldap_host' ],\n";
			$conf=$conf."\tport		     => $ldap->ldap_port,\n";
			$conf=$conf."\ttimeout       => 5,\n";
			$conf=$conf."\ttls           => 0,\n";
			$conf=$conf."\tbase          => 'dc=organizations,$ldap->suffix',\n";
			$conf=$conf."\tquery_filter  => '(&(objectclass=amavisAccount)(|(mailAlias=%m)(mail=%m)(associatedDomain=%m)(cn=%m)))',\n";
			$conf=$conf."\tbind_dn       => 'cn=$ldap->ldap_admin,$ldap->suffix',\n";
			$conf=$conf."\tbind_password => '$ldap->ldap_password',\n";
			$conf=$conf."};";
		}else{
			$conf=$conf."\$enable_ldap  = 0;\n";
		}
		$conf=$conf."\n";
		$conf=$conf."@local_domains_maps = ( [" . $this->GetLocalDomains()."] );  # list of all local domains\n";
		$conf=$conf."@mynetworks = qw(".$this->BuildNetworks().");\n";
		$conf=$conf."\n";
		if($EnablePostfixMultiInstance<>1){
			$conf=$conf."\$unix_socketname = \"/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock\";  # amavisd-release or amavis-milter\n";
		}
		
		if($EnablePostfixMultiInstance==1){$multiple_policies=$this->LoadMultiPolicies();}
		
		if(!is_array($GLOBALS["inet_socket_port"])){
			$conf=$conf."\$inet_socket_port = 10024;   # listen on this local TCP port(s)\n";
		}else{
			$GLOBALS["inet_socket_port"][]="10024";
			$conf=$conf."\$inet_socket_port =  [". @implode(",",$GLOBALS["inet_socket_port"]). "]; # listen on this local TCP port(s)\n";
		}

		$mydomainsExploded=explode(".",$myhostname);
		if($mydomainsExploded[1]<>null){
			unset($mydomainsExploded[0]);
			$conf=$conf."\$mydomain  = \"". @implode(".",$mydomainsExploded)."\";  # domain name\n";
		}
		$conf=$conf."\$myhostname = \"$myhostname\";  # must be a fully-qualified domain name!\n";
		//$conf=$conf."\$X_VIRUS_LINE = \"$myhostname\";\n";
		
		
		
		
		
		$currentip=$this->getInternalIPs();
		$conf=$conf."#adding net acl $currentip of all interfaces prevent DENIED ACCESS from IP 172.16.X.YYY\n";
		$conf=$conf ."@inet_acl = qw($currentip);\n";
		
		if($EnablePostfixMultiInstance<>1){
			if($EnableAmavisInMasterCF){
				$conf=$conf ."\$inet_socket_bind ='127.0.0.1';\n";
				$conf=$conf ."\$forward_method = 'smtp:[127.0.0.1]:10025';\n";
				$conf=$conf ."\$notify_method = 'smtp:[127.0.0.1]:10025';\n";
				
			}else{
				$conf=$conf."\$forward_method ='pipe:flags=q argv=/usr/sbin/sendmail -Ac -i -f \${sender} -- \${recipient}';\n";
				$conf=$conf."\$notify_method = \$forward_method;\n";	
			}
		}
		
		
		
		if($EnablePostfixMultiInstance==1){
			echo "Starting......: amavisd-new multiple instances is enabled\n";
			$conf=$conf .$multiple_policies;
		}
		

		
		
		$conf=$conf."\n";
		$conf=$conf.$this->banned_rules_maps()."\n";
		$conf=$conf."\n";
		
		$conf=$conf."\$policy_bank{'MYNETS'} = {   # mail originating from @mynetworks\n";
		$conf=$conf."\toriginating => 1,  # is true in MYNETS by default, but let's make it explicit\n";
		$conf=$conf."\tos_fingerprint_method => undef,  # don't query p0f for internal clients\n";
		if($EnableAmavisInMasterCF){
			$conf=$conf ."\tforward_method => 'smtp:[127.0.0.1]:10025',\n";
			$conf=$conf ."\tnotify_method => 'smtp:[127.0.0.1]:10025',\n";
			}
		
		if($altermime){
			$conf=$conf."\tallow_disclaimers => 1,\n";
			}

		if($this->main_array["BEHAVIORS"]["trust_my_net"]==1){
			$conf=$conf ."\tbypass_spam_checks_maps => [1], # don't spam-check internal mail\n";
			if($this->main_array["NETWORK"]["banned_extensions_include_local_net"]==0){
				$conf=$conf ."\tbypass_banned_checks_maps => [1], # don't banned-check internal mail\n";
			}
			$conf=$conf ."\tbypass_header_checks_maps => [1], # don't header-check internal mail\n";
		}
		if($this->main_array["NETWORK"]["banned_extensions_include_local_net"]==1){
			if(is_array($this->extensions)){
				$conf=$conf."\tbanned_filename_maps => ['GENERAL'],\n";
			}
		}	


		$conf=$conf."};\n";
		$conf=$conf."\n";
		
		
		
		$conf=$conf."\$interface_policy{'SOCK'} = 'AM.PDP-SOCK'; # only applies with \$unix_socketname\n";
		$conf=$conf."\n";
		$conf=$conf."# Use with amavis-release over a socket or with Petr Rehor's amavis-milter.c\n";
		$conf=$conf."# (with amavis-milter.c from this package or old amavis.c client use 'AM.CL'):\n";
		$conf=$conf."\$policy_bank{'AM.PDP-SOCK'} = {\n";
		$conf=$conf."  protocol => 'AM.PDP',\n";
		$conf=$conf."  notify_method  => 'pipe:flags=q argv=/usr/sbin/sendmail -Ac -i -f \${sender} -- \${recipient}',";
		$conf=$conf."  auth_required_release => 0,  # do not require secret_id for amavisd-release\n";
		
		if($this->main_array["NETWORK"]["banned_extensions_include_local_net"]==1){
			if(is_array($this->extensions)){
				$conf=$conf."  banned_filename_maps => ['GENERAL'],\n";
			}
		}
		$conf=$conf."};\n";
		$conf=$conf."\n";
		$conf=$conf."# Use in combination with dedicated artica-filters\n";
		$conf=$conf."\$policy_bank{'killAll'} = {protocol => 'AM.PDP',final_destiny_by_ccat => { (CC_CATCHALL) => D_DISCARD }};\n";
		$conf=$conf."\$sa_tag_level_deflt  = {$this->main_array["BEHAVIORS"]["sa_tag_level_deflt"]};  # add spam info headers if at, or above that level\n";
		$conf=$conf."\$sa_tag2_level_deflt = {$this->main_array["BEHAVIORS"]["sa_tag2_level_deflt"]};  # add 'spam detected' headers at that level\n";
		$conf=$conf."\$sa_tag3_level_deflt = {$this->main_array["BEHAVIORS"]["sa_tag3_level_deflt"]};  # add 'spam detected' headers at that level\n";
		$conf=$conf."\$sa_kill_level_deflt = {$this->main_array["BEHAVIORS"]["sa_kill_level_deflt"]};  # triggers spam evasive actions (e.g. blocks mail)\n";
		$conf=$conf."\$sa_dsn_cutoff_level = {$this->main_array["BEHAVIORS"]["sa_dsn_cutoff_level"]};   # spam level beyond which a DSN is not sent\n";
		$conf=$conf."\$sa_crediblefrom_dsn_cutoff_level = 18; # likewise, but for a likely valid From\n";
		$conf=$conf."\$sa_quarantine_cutoff_level = {$this->main_array["BEHAVIORS"]["sa_quarantine_cutoff_level"]}; # spam level beyond which quarantine is off\n";
		$conf=$conf."#\$penpals_bonus_score = 8;    # (no effect without a @storage_sql_dsn database)\n";
		$conf=$conf."#\$penpals_threshold_high = \$sa_kill_level_deflt;  # don't waste time on hi spam\n";
		$conf=$conf."\$bounce_killer_score = 100;  # spam score points to add for joe-jobbed bounces\n";
		$conf=$conf."\n";
		//$conf=$conf."\$sa_configpath='/etc/spamassassin';\n";
		//$conf=$conf."\$sa_siteconfigpath='/etc/spamassassin/';\n";
		
		
		$conf=$conf."\$sa_mail_body_size_limit = 400*1024; # don't waste time on SA if mail is larger\n";
		$conf=$conf."\$sa_local_tests_only = $this->AmavisNoInternetTests;    # only tests which do not require internet access?\n";
		$conf=$conf."#\$sa_spam_subject_tag = {$this->main_array["BEHAVIORS"]["spam_subject_tag2_maps"]}\n";
		
		if($this->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]=="yes"){
			if($this->main_array["BEHAVIORS"]["spam_subject_tag_maps_enable"]<>null){
				$conf=$conf."@spam_subject_tag_maps  = ('{$this->main_array["BEHAVIORS"]["spam_subject_tag_maps"]}');\n";
			}else{
				$conf=$conf."@spam_subject_tag_maps  = undef;\n";
			}
		}else{
			$conf=$conf."@spam_subject_tag_maps  = undef;\n";
		}
		$conf=$conf."@spam_subject_tag2_maps = ('{$this->main_array["BEHAVIORS"]["spam_subject_tag2_maps"]}');\n";
		$conf=$conf."@spam_subject_tag3_maps =('{$this->main_array["BEHAVIORS"]["spam_subject_tag2_maps"]}');\n";
		$conf=$conf."\n";
		$conf=$conf."\$timestamp_fmt_mysql = 1;\n";
		$conf=$conf."\n";
		
		$this->main_array["BEHAVIORS"]["virus_admin"]=str_replace("@","\@",$this->main_array["BEHAVIORS"]["virus_admin"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]=str_replace("@","\@",$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]=str_replace("@","\@",$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]);
		$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]=str_replace("@","\@",$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]);
		
		$conf=$conf."\$virus_admin               = \"{$this->main_array["BEHAVIORS"]["virus_admin"]}\";  # notifications recip.\n";
		$conf=$conf."\$mailfrom_notify_admin     = \"{$this->main_array["BEHAVIORS"]["mailfrom_notify_admin"]}\";  # notifications sender\n";
		$conf=$conf."\$mailfrom_notify_recip     = \"{$this->main_array["BEHAVIORS"]["mailfrom_notify_recip"]}\";  # notifications sender\n";
		$conf=$conf."\$mailfrom_notify_spamadmin = \"{$this->main_array["BEHAVIORS"]["mailfrom_notify_spamadmin"]}\"; # notifications sender\n";
		$conf=$conf."\$mailfrom_to_quarantine = ''; # null return path; uses original sender if undef\n";
		$conf=$conf."\n";
		
		if($enable_dkim_verification==1){
			$conf=$conf."\$enable_dkim_verification=1;\n";
		}
		
		$smtpd_recipient_limit=$sock->GET_INFO("smtpd_recipient_limit");
		if(!is_numeric($smtpd_recipient_limit)){$smtpd_recipient_limit=50;}
		if($smtpd_recipient_limit>0){
			$conf=$conf."\$smtpd_recipient_limit=$smtpd_recipient_limit;\n";
		}
		
		$conf=$conf."\n".$this->DKIM_outgoing_list()."\n";
		$conf=$conf."# WHITELISTING, list of whitelisted senders...\n";
		$conf=$conf.$this->whitelist_sender_maps();
		$conf=$conf."\n";
		$conf=$conf."@addr_extension_virus_maps      = ('virus');\n";
		$conf=$conf."@addr_extension_banned_maps     = ('banned');\n";
		$conf=$conf."@addr_extension_spam_maps       = ('spam');\n";
		$conf=$conf."@addr_extension_bad_header_maps = ('badh');\n";
		$conf=$conf."# \$recipient_delimiter = '+';  # undef disables address extensions altogether\n";
		$conf=$conf."# when enabling addr extensions do also Postfix/main.cf: recipient_delimiter=+\n";
		$conf=$conf."\n";
		$conf=$conf."\$path = '/usr/local/sbin:/usr/local/bin:/usr/sbin:/sbin:/usr/bin:/bin:/usr/share/artica-postfix/bin';\n";
		
		if($altermime){
				$conf=$conf."\n";
				$conf=$conf."#AlterMime implementation\n";
				$conf=$conf."\$altermime = '/usr/local/bin/altermime'; \n";
				$conf=$conf."@altermime_args_disclaimer = qw(--disclaimer=/usr/local/etc/altermime-disclaimer.txt --disclaimer-html=/usr/local/etc/altermime-disclaimer.html --log-syslog);\n";
				$conf=$conf."\n";
			}
		
		
		$conf=$conf."\$dspam = '';\n";
		$conf=$conf."\$defang_virus  = 1;  # MIME-wrap passed infected mail\n";
		$conf=$conf."\$defang_banned = 1;  # MIME-wrap passed mail containing banned name\n";
		$conf=$conf."# for defanging bad headers only turn on certain minor contents categories:\n";
		$conf=$conf."\$defang_by_ccat{+CC_BADH.\",3\"} = 1;  # NUL or CR character in header\n";
		$conf=$conf."\$defang_by_ccat{+CC_BADH.\",5\"} = 1;  # header line longer than 998 characters\n";
		$conf=$conf."\$defang_by_ccat{+CC_BADH.\",6\"} = 1;  # header field syntax error\n";
		
		
		
		if($altermime){
			$conf=$conf."\$defang_maps_by_ccat{+CC_CLEAN} = [ 'disclaimer' ];\n";
			$conf=$conf."\$defang_maps_by_ccat{+CC_BADH} = [ 'disclaimer' ];\n";	
		}
		
		$conf=$conf."\n";
		$conf=$conf."\$final_virus_destiny      = {$this->main_array["BEHAVIORS"]["final_virus_destiny"]};\n";
		$conf=$conf."\$final_banned_destiny     = {$this->main_array["BEHAVIORS"]["final_banned_destiny"]};\n";
		$conf=$conf."\$final_spam_destiny       = {$this->main_array["BEHAVIORS"]["final_spam_destiny"]};\n";
		$conf=$conf."\$final_bad_header_destiny = {$this->main_array["BEHAVIORS"]["final_bad_header_destiny"]};\n";
		$conf=$conf."\$mailfrom_to_quarantine = undef;\n";
		$conf=$conf."\$spam_quarantine_method   = 'local:spam-%m.gz';\n";
		
		if($this->EnableQuarantineSpammy==1){
			$conf=$conf."\$defang_by_ccat{CC_SPAMMY, 1};\n";	
			$conf=$conf."\$quarantine_method_by_ccat{+CC_SPAMMY} = \$spam_quarantine_method;\n";
			$conf=$conf."\$quarantine_to_maps_by_ccat{CC_SPAMMY} = \@spam_quarantine_to_maps;\n";	
			$conf=$conf."\$final_destiny_by_ccat{+CC_SPAMMY} =\$final_spam_destiny;\n";
		}	
		
		if($this->EnableQuarantineSpammy2==1){
			$conf=$conf."\$defang_by_ccat{+CC_SPAMMY.\",1\"} = 1;\n";
			$conf=$conf."\$quarantine_method_by_ccat{CC_SPAMMY.\",1\"} = \$spam_quarantine_method;\n";
			$conf=$conf."\$quarantine_to_maps_by_ccat{CC_SPAMMY.\",1\"} = \@spam_quarantine_to_maps;\n";
			$conf=$conf."\$final_destiny_by_ccat{+CC_SPAMMY.\",1\"} =\$final_spam_destiny;\n";
		}

			$conf=$conf."\$bad_header_quarantine_method = undef;\n";
			$conf=$conf."\n";
			$conf=$conf."# \$os_fingerprint_method = 'p0f:*:2345';  # to query p0f-analyzer.pl\n";
			$conf=$conf."\n";
			$conf=$conf."#Sender Notifications\n";
			$conf=$conf."\$warnbadhsender = {$this->main_array["BEHAVIORS"]["warnbadhsender"]};\n";
			$conf=$conf."\$warnvirussender = {$this->main_array["BEHAVIORS"]["warnvirussender"]};\n";
			$conf=$conf."\$warnspamsender = {$this->main_array["BEHAVIORS"]["warnspamsender"]};\n";
			$conf=$conf."\$warnbannedsender = {$this->main_array["BEHAVIORS"]["warnbannedsender"]};\n";
			$conf=$conf."\n";
			$conf=$conf."#Recipient Notifications\n";
			$conf=$conf."\$warnvirusrecip = {$this->main_array["BEHAVIORS"]["warnvirusrecip"]};\n";
			$conf=$conf."\$warnbannedrecip = {$this->main_array["BEHAVIORS"]["warnbannedrecip"]};\n";
			$conf=$conf."\$warnbadhrecip = {$this->main_array["BEHAVIORS"]["warnbadhrecip"]};\n";
			$conf=$conf."\n";
			$conf=$conf."#Admin Notifications\n";
			if($this->main_array["BEHAVIORS"]["newvirus_admin"]<>"undef"){$conf=$conf."\$newvirus_admin = \"{$this->main_array["BEHAVIORS"]["newvirus_admin"]}\";\n";}else{$conf=$conf."\$newvirus_admin = undef;\n";}
			if($this->main_array["BEHAVIORS"]["virus_admin"]<>"undef"){$conf=$conf."\$virus_admin = \"{$this->main_array["BEHAVIORS"]["virus_admin"]}\";\n";}else{$conf=$conf."\$virus_admin = undef;\n";}
			if($this->main_array["BEHAVIORS"]["spam_admin"]<>"undef"){$conf=$conf."\$spam_admin = \"{$this->main_array["BEHAVIORS"]["spam_admin"]}\";\n";}else{$conf=$conf."\$spam_admin = undef;\n";}
			if($this->main_array["BEHAVIORS"]["bad_header_admin"]<>"undef"){$conf=$conf."\$bad_header_admin = \"{$this->main_array["BEHAVIORS"]["bad_header_admin"]}\";\n";}else{$conf=$conf."\$bad_header_admin = undef;\n";}
			$conf=$conf."\n";
			$conf=$conf."#templates Notifications\n";
			$conf=$conf."\$notify_sender_templ      = read_text(\"/usr/local/etc/amavis/template-dsn.txt\");\n";
			$conf=$conf."\$notify_virus_sender_templ= read_text(\"/usr/local/etc/amavis/template-virus-sender.txt\");\n";
			$conf=$conf."\$notify_virus_admin_templ = read_text(\"/usr/local/etc/amavis/template-virus-admin.txt\");\n";
			$conf=$conf."\$notify_virus_recips_templ= read_text(\"/usr/local/etc/amavis/template-virus-recipient.txt\");\n";
			$conf=$conf."\$notify_spam_sender_templ = read_text(\"/usr/local/etc/amavis/template-virus-sender.txt\");\n";
			$conf=$conf."\$notify_spam_admin_templ  = read_text(\"/usr/local/etc/amavis/template-spam-admin.txt\");\n";
			$conf=$conf."\n";
			
			
			$conf=$conf."\n";
			$conf=$conf."# REMAINING IMPORTANT VARIABLES ARE LISTED HERE BECAUSE OF LONGER ASSIGNMENTS\n";
			$conf=$conf."\n";
			$conf=$conf."@keep_decoded_original_maps = (new_RE(\n";
			$conf=$conf."# qr'^MAIL\$',   # retain full original message for virus checking (can be slow)\n";
			if($this->EnableScanSecurity==1){
				$conf=$conf."  qr'^MAIL-UNDECIPHERABLE\$', # recheck full mail if it contains undecipherables (enable by EnableScanSecurity)\n";
			}
			$conf=$conf."  qr'^(ASCII(?! cpio)|text|uuencoded|xxencoded|binhex)'i,\n";
			$conf=$conf."# qr'^Zip archive data',     # don't trust Archive::Zip\n";
			$conf=$conf."));\n";
			$conf=$conf."\n";
			$conf=$conf.$this->banned_filename_re();
			$conf=$conf."\n# See http://support.microsoft.com/default.aspx?scid=kb;EN-US;q262631\n";
			$conf=$conf."# and http://www.cknow.com/vtutor/vtextensions.htm\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."# ENVELOPE SENDER SOFT-WHITELISTING / SOFT-BLACKLISTING\n";
			$conf=$conf.$this->per_recip_blacklist_whitelist_sender_lookup_tables();
			$conf=$conf."\n";
			$conf=$conf."@score_sender_maps = ({\n"; 
			$conf=$conf."\n";
			$conf=$conf."  '.' => [new_RE(\n";
			$conf=$conf."    [qr'^(bulkmail|offers|cheapbenefits|earnmoney|foryou)@'i         => 5.0],\n";
			$conf=$conf."    [qr'^(greatcasino|investments|lose_weight_today|market\.alert)@'i=> 5.0],\n";
			$conf=$conf."    [qr'^(money2you|MyGreenCard|new\.tld\.registry|opt-out|opt-in)@'i=> 5.0],\n";
			$conf=$conf."    [qr'^(optin|saveonlsmoking2002k|specialoffer|specialoffers)@'i   => 5.0],\n";
			$conf=$conf."    [qr'^(stockalert|stopsnoring|wantsome|workathome|yesitsfree)@'i  => 5.0],\n";
			$conf=$conf."    [qr'^(your_friend|greatoffers)@'i                                => 5.0],\n";
			$conf=$conf."    [qr'^(inkjetplanet|marketopt|MakeMoney)\d*@'i                    => 5.0] ),\n";
			$conf=$conf."\n";
			$conf=$conf."read_hash(\"/usr/local/etc/sender_scores_sitewide\"),\n";
			$conf=$conf."   {\n";
			$conf=$conf.$this->bypass_byrecipients_score()."\n";
			$conf=$conf."     'nobody@cert.org'                        => -3.0,\n";
			$conf=$conf."     'cert-advisory@us-cert.gov'              => -3.0,\n";
			$conf=$conf."     'owner-alert@iss.net'                    => -3.0,\n";
			$conf=$conf."     'slashdot@slashdot.org'                  => -3.0,\n";
			$conf=$conf."     'securityfocus.com'                      => -3.0,\n";
			$conf=$conf."     'ntbugtraq@listserv.ntbugtraq.com'       => -3.0,\n";
			$conf=$conf."     'security-alerts@linuxsecurity.com'      => -3.0,\n";
			$conf=$conf."     'mailman-announce-admin@python.org'      => -3.0,\n";
			$conf=$conf."     'amavis-user-admin@lists.sourceforge.net'=> -3.0,\n";
			$conf=$conf."     'amavis-user-bounces@lists.sourceforge.net' => -3.0,\n";
			$conf=$conf."     'spamassassin.apache.org'                => -3.0,\n";
			$conf=$conf."     'notification-return@lists.sophos.com'   => -3.0,\n";
			$conf=$conf."     'owner-postfix-users@postfix.org'        => -3.0,\n";
			$conf=$conf."     'owner-postfix-announce@postfix.org'     => -3.0,\n";
			$conf=$conf."     'owner-sendmail-announce@lists.sendmail.org'   => -3.0,\n";
			$conf=$conf."     'sendmail-announce-request@lists.sendmail.org' => -3.0,\n";
			$conf=$conf."     'donotreply@sendmail.org'                => -3.0,\n";
			$conf=$conf."     'ca+envelope@sendmail.org'               => -3.0,\n";
			$conf=$conf."     'noreply@freshmeat.net'                  => -3.0,\n";
			$conf=$conf."     'owner-technews@postel.acm.org'          => -3.0,\n";
			$conf=$conf."     'ietf-123-owner@loki.ietf.org'           => -3.0,\n";
			$conf=$conf."     'cvs-commits-list-admin@gnome.org'       => -3.0,\n";
			$conf=$conf."     'rt-users-admin@lists.fsck.com'          => -3.0,\n";
			$conf=$conf."     'clp-request@comp.nus.edu.sg'            => -3.0,\n";
			$conf=$conf."     'surveys-errors@lists.nua.ie'            => -3.0,\n";
			$conf=$conf."     'emailnews@genomeweb.com'                => -5.0,\n";
			$conf=$conf."     'yahoo-dev-null@yahoo-inc.com'           => -3.0,\n";
			$conf=$conf."     'returns.groups.yahoo.com'               => -3.0,\n";
			$conf=$conf."     'clusternews@linuxnetworx.com'           => -3.0,\n";
			$conf=$conf."     lc('lvs-users-admin@LinuxVirtualServer.org')    => -3.0,\n";
			$conf=$conf."     lc('owner-textbreakingnews@CNNIMAIL12.CNN.COM') => -5.0,\n";
			$conf=$conf."     'sender@example.net'                     =>  3.0,\n";
			$conf=$conf."     '.example.net'                           =>  1.0,\n";
			$conf=$conf."\n";
			$conf=$conf."   },\n";
			$conf=$conf."  ],\n";
			$conf=$conf."});\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."@decoders = (\n";
			$conf=$conf."  ['mail', \&do_mime_decode],\n";
			$conf=$conf."  ['asc',  \&do_ascii],\n";
			$conf=$conf."  ['uue',  \&do_ascii],\n";
			$conf=$conf."  ['hqx',  \&do_ascii],\n";
			$conf=$conf."  ['ync',  \&do_ascii],\n";
			$conf=$conf."  ['F',    \&do_uncompress, ['unfreeze','freeze -d','melt','fcat'] ],\n";
			$conf=$conf."  ['Z',    \&do_uncompress, ['uncompress','gzip -d','zcat'] ],\n";
			$conf=$conf."  ['gz',   \&do_uncompress,  'gzip -d'],\n";
			$conf=$conf."  ['gz',   \&do_gunzip],\n";
			$conf=$conf."  ['bz2',  \&do_uncompress,  'bzip2 -d'],\n";
			$conf=$conf."  ['lzo',  \&do_uncompress,  'lzop -d'],\n";
			$conf=$conf."  ['rpm',  \&do_uncompress, ['rpm2cpio.pl','rpm2cpio'] ],\n";
			$conf=$conf."  ['cpio', \&do_pax_cpio,   ['pax','gcpio','cpio'] ],\n";
			$conf=$conf."  ['tar',  \&do_pax_cpio,   ['pax','gcpio','cpio'] ],\n";
			$conf=$conf."  ['deb',  \&do_ar,          'ar'],\n";
			$conf=$conf."# ['a',    \&do_ar,          'ar'],  # unpacking .a seems an overkill\n";
			$conf=$conf."  ['zip',  \&do_unzip],\n";
			$conf=$conf."  ['7z',   \&do_7zip,       ['7zr','7za','7z'] ],\n";
			$conf=$conf."  ['rar',  \&do_unrar,      ['rar','unrar'] ],\n";
			$conf=$conf."  ['arj',  \&do_unarj,      ['arj','unarj'] ],\n";
			$conf=$conf."  ['arc',  \&do_arc,        ['nomarch','arc'] ],\n";
			$conf=$conf."  ['zoo',  \&do_zoo,        ['zoo','unzoo'] ],\n";
			$conf=$conf."  ['lha',  \&do_lha,         'lha'],\n";
			$conf=$conf."# ['doc',  \&do_ole,         'ripole'],\n";
			$conf=$conf."  ['cab',  \&do_cabextract,  'cabextract'],\n";
			$conf=$conf."  ['tnef', \&do_tnef_ext,    'tnef'],\n";
			$conf=$conf."  ['tnef', \&do_tnef],\n";
			$conf=$conf."# ['sit',  \&do_unstuff,     'unstuff'],  # broken/unsafe decoder\n";
			$conf=$conf."  ['exe',  \&do_executable, ['rar','unrar'], 'lha', ['arj','unarj'] ],\n";
			$conf=$conf.");\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."@av_scanners = (\n";
			
			if($users->CLAMAV_INSTALLED){
				if(strlen($users->CLAMAV_SOCKET)>0){
					$conf=$conf."['ClamAV-clamd',\&ask_daemon, [\"CONTSCAN {}\", \"$users->CLAMAV_SOCKET\"],qr/\bOK$/, qr/\bFOUND$/,qr/^.*?: (?!Infected Archive)(.*) FOUND$/m ]\n";
				}
			}
			
			$conf=$conf.");\n";
			$conf=$conf."@av_scanners_backup = ();\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."1;  # insure a defined return value";
			return $conf;	
}


private function bypass_byrecipients_score(){
	$q=new mysql();
	$sql="SELECT * FROM amavis_bypass_rcpt ORDER BY `pattern`";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql\n";return null;}	
	$count=0;
	$f=array();
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["pattern"]=trim($ligne["pattern"]);
		$ip=trim($ligne["pattern"]);
		if($ip==null){continue;}
		if(is_array($ip)){continue;}
		if(strpos(" $ip","@")==0){$pattern=".$ip";}else{$pattern=$ip;}
		$f[]="     '$pattern'                        => -99.0";
	}
	if(count($f)>0){
		return @implode(",",$f).',';
	}
		
}	

private function whitelist_sender_maps(){
	$sql="SELECT * FROM postfix_global_whitelist WHERE enabled=1 ORDER BY sender";	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql\n";return null;}	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["sender"]);
		if($pattern==null){continue;}
		$pattern=str_replace('.','\.',$pattern);
		$pattern=str_replace("*",".*",$pattern);
		$pattern="\tqr'$pattern$'i\n";	
		$f[]=$pattern;
	}	
	
	if(count($f)==0){return;}
	echo "Starting......: amavisd-new whitelisted senders ". count($f)." items\n";
	$final[]="@whitelist_sender_maps = ( new_RE(\n";
   	$final[]=@implode(",",$f);
	$final[]="));";
	$final[]="";
	
	return @implode("\n",$final);
	
	
}


private function LoadMultiPolicies(){
	$sock=new sockets();
	$already=array();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$sql="SELECT * FROM postfix_multi WHERE `key`= 'amavis_local_port' AND uuid='$uuid'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$interfaces[]="";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ou=$ligne["ou"];
		$ip_address=$ligne["ip_address"];
		$port=$ligne["value"];
		if(!is_numeric($port)){
			echo "Starting......: amavisd-new \"$ip_address\" ($uuid) no defined port !\n";
			continue;
		}
		
		if($already[md5("$ip_address:$port")]){continue;}
		$main=new maincf_multi(null,$ou,$ip_address);
		$hostname=$main->myhostname;
		if(trim($hostname)==null){
			echo "Starting......: amavisd-new $ip_address:$port in ou $ou have no defined hostname !!!\n";
		}
		
		
		echo "Starting......: amavisd-new $ip_address:$port for $hostname\n";
		$interfaces[]="\$interface_policy{'$port'} = '$hostname';";
		if(posix_getuid()==0){echo "Starting......: amavisd-new bin port:$port\n";} 
		$GLOBALS["inet_socket_port"][]=$port;
		$policy[]=$this->LoadMultiplePoliciesConfig($hostname);
		$already[md5("$ip_address:$port")]=true;
	}
	
	return @implode("\n",$interfaces)."\n\n".@implode("\n",$policy);
	
}

private function LoadMultiplePoliciesConfig($hostname){
	echo "Starting......: amavisd-new $hostname: building policy bank.\n";
	$sock=new sockets();
	$main=new maincf_multi($hostname);
	if(trim($main->ip_addr)==null){
		echo "Starting......: amavisd-new $hostname: ip_addr is null for this host, aborting\n";
		return;
	}
	$params=unserialize(base64_decode($main->GET_BIGDATA("amavis_config")));
	$nets=unserialize($main->GET_BIGDATA("mynetworks"));
	$enable_dkim_signing=$sock->GET_INFO("enable_dkim_signing");
	
	$conf[]="\$policy_bank{'$main->myhostname'} ={";
	$conf[]="\tforward_method => 'smtp:[$main->ip_addr]:10026',";
	$conf[]="\tnotify_method  => 'smtp:[$main->ip_addr]:10026',";
	$conf[]="\tlocal_client_bind_address =>'127.0.0.1',";
	$conf[]="\tsmtpd_greeting_banner =>'amavis.$main->myhostname \${protocol} \${product} \${version-id} (\${version-date}) amavisd-new service ready',";
	$conf[]="\tlocalhost_name => 'amavis.$main->myhostname',";
	$conf[]="\toriginating => 1,";
	
	echo "Starting......: amavisd-new $hostname: Enable DKIM signing=$enable_dkim_signing\n";
	
	if($enable_dkim_signing==1){
		$ldap=new clladp();
		$domains=$ldap->hash_get_all_domains();
		if(is_array($domains)){
			$conf[]="\tdkim_signature_options_bysender_maps => [{";
			while (list ($domain, $line) = each ($domains)){
				$md5=md5($domain);
					if(is_file("/etc/amavis/dkim/$md5.key.pem")){
						$conf[]="\t  '$domain'  => { d => '$domain', a => 'rsa-sha256', ttl => 10*24*3600 },";
					}
			}
			
			$conf[]="\t  '.' => { a => 'rsa-sha256', c => 'relaxed/simple', ttl => 30*24*3600 },";
			$conf[]=" \t   }],";
		}

	}
	
	if(is_array($nets)){
		$conf[]="\tinet_acl => [qw( ".@implode(" ",$nets)." $main->ip_addr 127.0.0.1 )],";
	}
	
	if($params["sa_tag_level_deflt"]<>null){$conf[]="\tspam_tag_level_maps => [{$params["sa_tag_level_deflt"]}],";}
	if($params["sa_tag2_level_deflt"]<>null){$conf[]="\tspam_tag2_level_maps => [{$params["sa_tag_level_deflt"]}],";}
	if($params["sa_tag3_level_deflt"]<>null){$conf[]="\tspam_tag3_level_maps => [{$params["sa_tag3_level_deflt"]}],";}
	if($params["sa_kill_level_deflt"]<>null){$conf[]="\tspam_kill_level_maps => [{$params["sa_kill_level_deflt"]}],";}
	if($params["sa_dsn_cutoff_level"]<>null){$conf[]="\tspam_dsn_cutoff_level_maps => [{$params["sa_dsn_cutoff_level"]}],";}
	if($params["sa_quarantine_cutoff_level"]<>null){$conf[]="\tspam_quarantine_cutoff_level_maps => [{$params["sa_quarantine_cutoff_level"]}],";}
	if($params["spam_subject_tag2_maps"]<>null){$conf[]="\tspam_subject_tag2_maps => ['{$params["spam_subject_tag2_maps"]}'],";}
	
	
	
	if($params["spam_subject_tag_maps_enable"]=="yes"){
			if($params["spam_subject_tag_maps_enable"]<>null){
				$conf[]="\tspam_subject_tag_maps  = ['{$params["spam_subject_tag_maps"]}'],";
			}
	}

	if($params["spam_quarantine_spammy2"]==1){
				$conf[]="\tfinal_destiny_by_ccat=> {(CC_SPAMMY,1)},";
			}
			
	if($params["mailfrom_notify_admin"]<>null){$conf[]="\tvirus_admin_maps => [{$params["mailfrom_notify_admin"]}],";}
	if($params["mailfrom_notify_spamadmin"]<>null){$conf[]="\tspam_admin_maps => [{$params["mailfrom_notify_spamadmin"]}],";}				
	if($params["virus_admin"]<>null){$conf[]="\tvirus_admin_maps => [{$params["virus_admin"]}],";}
 	if($params["warnbadhsender"]<>null){$conf[]="\twarnbadhsender => {$params["warnbadhsender"]},";}
	if($params["warnbadhrecip"]<>null){$conf[]="\twarnbadhrecip => {$params["warnbadhrecip"]},";}
	if($params["warnvirusrecip"]<>null){$conf[]="\twarnvirusrecip => {$params["warnvirusrecip"]},";}
	if($params["warnbannedrecip"]<>null){$conf[]="\twarnbannedrecip => {$params["warnbannedrecip"]},";}
	$banned_files=$this->LoadMultiplePoliciesBannedFiles($main->myhostname);
	if($banned_files<>null){$conf[]=$banned_files;}
	$conf[]="\t};";	
	$conf[]="";	
	return @implode("\n",$conf);		
			
}

function LoadMultiplePoliciesBannedFiles($hostname){
	
	$sql="SELECT * FROM smtp_attachments_blocking WHERE hostname='$hostname' ORDER BY IncludeByName";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IncludeByName"]==null){continue;}
			$f[]=$ligne["IncludeByName"];
		
	}
	if(!is_array($f)){return;}
	if(count($f)==0){return;}
	$pattern=implode("|",$f);
  	return "\tbanned_filename_maps => [new_RE(qr'.\.($pattern)$'i)],";	
	
}


function SaveToServer(){
	$this->Save();
	$sock=new sockets();
	$sock->getfile('CheckDaemon');
	$sock->getfile('saveamavis');
	// --without-restart
}

function banned_filename_re(){
	if(!is_array($this->extensions)){
		return "\n\$banned_filename_re=undef;\n";
	}
	
	if(count($this->extensions)==0){
		return "\n\$banned_filename_re=undef;\n";
	}
	
	reset($this->extensions);
	return "\n\$banned_filename_re=new_RE(qr'^\.(".implode('|',$this->extensions).")$');\n";
	
	
}

private  function banned_filename_maps(){
	if(!is_array($this->extensions)){return null;}
	if(count($this->extensions)==0){return null;}
	reset($this->extensions);
	$pattern=implode('|',$this->extensions);
	return "banned_filename_re = new_RE(qr'.\.($pattern)$'i)";
}

private function banned_rules_maps(){
	if(!is_array($this->extensions)){return null;}
	if(count($this->extensions)==0){return null;}
	reset($this->extensions);
	$pattern=implode('|',$this->extensions);
	return "%banned_rules = ('GENERAL' => new_RE(qr'.\.($pattern)$'i));\n";
	
}

function per_recip_blacklist_whitelist_sender_lookup_tables(){
	$ldap=new clladp();
	$domain=$ldap->hash_get_all_domains();
	if(!is_array($domain)){return null;}
	
	while (list ($num, $line) = each ($domain)){
		writelogs("Search white list and black list for $num",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$dom_black=$this->BlackListFromDomain($num);
		if(strlen($dom_black)>2){
			writelogs("blacklist=$dom_black",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$array_black[]=$dom_black;
		}
		
		$dom_white=$this->WhitelistsFromDomain($num);
		if(strlen($dom_white)>2){
			writelogs("whitelist=$dom_white",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$array_white[]=$dom_white;
		}		
		
	}
	
	if(is_array($array_white)){
		$white="\n";
		$white=$white."\$per_recip_whitelist_sender_lookup_tables = {\n";
		$white=$white.implode(",\n",$array_white);
		$white=$white."\n\t};\n";
	}
	
	if(is_array($array_black)){
		$black="\n";
		$black=$black."\$per_recip_blacklist_sender_lookup_tables  = {\n";
		$black=$black.implode(",\n",$array_black);
		$black=$black."\n\t};\n";
	}	
	
	return $black.$white;
	
}
	
function BlackListFromDomain($domain){
	$ldap=new clladp();
	$dn="cn=$domain,cn=wlbl,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		return null;
	}
	
	$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=PostFixStructuralClass');
	if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($j=0;$j<$hash[0][strtolower("KasperkyASDatasDeny")]["count"];$j++){
				writelogs($hash[0][strtolower("KasperkyASDatasDeny")][$j],__CLASS__.'/'.__FUNCTION__,__FILE__);
				if(trim($hash[0][strtolower("KasperkyASDatasDeny")][$j])){
					$res_bl[]=$hash[0][strtolower("KasperkyASDatasDeny")][$j];
				}
			}
		}

	if(!is_array($res_bl)){return null;}
	return "'$domain'=> [qw(".implode(" ",$res_bl).")]";
	
}

function WhitelistsFromDomain($domain){
	$ldap=new clladp();
	$ldap=new clladp();
	$dn="cn=$domain,cn=wlbl,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		return null;
	}
	
	$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=PostFixStructuralClass');
	if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($j=0;$j<$hash[0][strtolower("kasperkyasdatasallow")]["count"];$j++){
				writelogs($hash[0][strtolower("kasperkyasdatasallow")][$j],__CLASS__.'/'.__FUNCTION__,__FILE__);
				if(trim($hash[0][strtolower("kasperkyasdatasallow")][$j])){
					$res_bl[]=$hash[0][strtolower("kasperkyasdatasallow")][$j];
				}
			}
		}

	if(!is_array($res_bl)){return null;}
	return "'$domain'=> [qw(".implode(" ",$res_bl).")]";
}

private function DKIM_outgoing_list(){
		$sock=new sockets();
		$enable_dkim_verification=$sock->GET_INFO("enable_dkim_verification");
		$enable_dkim_signing=$sock->GET_INFO("enable_dkim_signing");
		if($enable_dkim_signing==0){return "\$enable_dkim_signing = 0;";}
		$ldap=new clladp();
		$domains=$ldap->hash_get_all_domains();
		$array[]="#DKIM: Sign all outgoing mails in ". count($domains). " Domains...";
		$array[]="\$enable_dkim_signing = 1;";
		
		
		if(!is_array($domains)){
			echo "Starting......: amavisd-new DKIM no domains set\n";
			return null;
			}
			while (list ($domain, $line) = each ($domains)){
				$md5=md5($domain);
				if(is_file("/etc/amavis/dkim/$md5.key.pem")){
					$domain_name=str_replace(".","_",$domain);
					$array[]="dkim_key('$domain', '$domain_name', '/etc/amavis/dkim/$md5.key.pem');";
					echo "Starting......: amavisd-new DKIM ($domain) $md5.key.pem OK\n";
					
				}else{
					echo "Starting......: amavisd-new ERROR !!! DKIM ($domain) /etc/amavis/dkim/$md5.key.pem no such file\n";
				}
			}
	if(!is_array($array)){return "\$enable_dkim_signing = 0;";}
	$array[]="@dkim_signature_options_bysender_maps = ({ '.' => { ttl => 21*24*3600, c => 'relaxed/simple' } } );";		
	return @implode("\n",$array);
}


public function CheckDKIM(){
		if(posix_getuid()<>0){return;}	
		$sock=new sockets();
		$enable_dkim_verification=$sock->GET_INFO("enable_dkim_verification");
		$enable_dkim_signing=$sock->GET_INFO("enable_dkim_signing");
		if($enable_dkim_signing==0){
			echo "Starting......: amavisd-new DKIM signing is not activated\n";
			return;
		}
		
		$ldap=new clladp();
		$domains=$ldap->hash_get_all_domains();
		if(!is_array($domains)){return null;}
			while (list ($domain, $line) = each ($domains)){
				$md5=md5($domain);
				if(!is_file("/etc/amavis/dkim/$md5.key.pem")){
					echo "Starting......: amavisd-new DKIM build key for $domain\n";
					$cmd="/usr/local/sbin/amavisd -c /usr/local/etc/amavisd.conf genrsa /etc/amavis/dkim/$md5.key.pem";
					shell_exec("$cmd >/dev/null 2>&1");		
				}
				
			}
	}

}

class amavis_sql{
	
	function amavis_sql(){
		include_once(dirname(__FILE__).'/class.mysql.inc');
		include_once(dirname(__FILE__).'/class.templates.inc');
		
		
	}
	
	function CountReceived(){
		$sql="SELECT COUNT(*) AS TCOUNT FROM mails_events";
		$mysql=new mysql();
		$ligne=@mysql_fetch_array($mysql->QUERY_SQL($sql,"artica_events"));
		return $ligne["TCOUNT"];
	}
	
	function LastReceived(){
		$sql="SELECT * FROM mails_events ORDER BY ID DESC limit 0,100";
		$mysql=new mysql();
		$results=$mysql->QUERY_SQL($sql,"artica_events");
		$html="
		<br>
		<H5>100 {first_emails} (" . $this->CountReceived()." {total})</H5>
		<table style='width:100%'>
		<tr>
			<th>&nbsp;</th>
			<th>{date}</th>
			<th>{mail_from}</th>
			<th>{mail_to}</th>
			<th>{country}</th>
			<th>{region}</th>
			<th>{city}</th>
		</tr>
		
		";
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["Region"]==null){$ligne["Region"]="undefined";}
			$ligne["org_mailfrom"]=$ligne["mailfrom"];
			$ligne["org_mailto"]=$ligne["rcpt_to"];
			if(strlen($ligne["mailfrom"])>33){$ligne["mailfrom"]=substr($ligne["mailfrom"],0,30)."...";}
			if(strlen($ligne["rcpt_to"])>33){$ligne["rcpt_to"]=substr($ligne["rcpt_to"],0,30)."...";}
			
			if($ligne["spam"]==0){$background='white';}else{$background="#F8D3C3";}
			
			$html=$html . "
				<tr " . CellRollOver(null,$this->__LastReceived_tooltip($ligne)).">
				<td width=1% style='background-color:$background;border-bottom:1px solid #CCCCCC'><img src='img/fw_bold.gif'></td>
				<td nowrap style='background-color:$background;border-bottom:1px solid #CCCCCC'>{$ligne["zDate"]}</td>
				<td style='background-color:$background;border-bottom:1px solid #CCCCCC'>{$ligne["mailfrom"]}</td>
				<td style='background-color:$background;border-bottom:1px solid #CCCCCC'>{$ligne["rcpt_to"]}</td>
				<td style='background-color:$background;border-bottom:1px solid #CCCCCC'>{$ligne["Country"]}</td>
				<td nowrap style='background-color:$background;border-bottom:1px solid #CCCCCC'>{$ligne["Region"]}</td>
				<td style='background-color:$background;border-bottom:1px solid #CCCCCC'>{$ligne["City"]}</td>
				</tr>
				
				";
			}
			
			$html="<div style='width:100%;height:400px;overflow:auto'>$html</table></div>";
			$tpl=new templates();
			return $tpl->_ENGINE_parse_body($html);
		
	}
	
	function __LastReceived_tooltip($array){
		$array["subject"]=ASCII_TO_HTML($array["subject"]);
		if($array["spam"]==0){$spam="{no}";}else{$spam="yes";}
		$html="<table style=width:100%>";
		$html=$html."<tr>";
		$html=$html."	<td style=background-color:#CCCCCC><strong>{host}</td>";
		$html=$html."<td>{$array["relayhost"]}</td>";
		$html=$html."<tr>";
		$html=$html."	<td style=background-color:#CCCCCC><strong>{mail_from}</td>";
		$html=$html."<td>{$array["org_mailfrom"]}</td>";		
		$html=$html."</tr>";
		$html=$html."<tr>";
		$html=$html."	<td style=background-color:#CCCCCC><strong>{mail_to}</td>";
		$html=$html."<td>{$array["org_mailto"]}</td>";		
		$html=$html."</tr>";		
		$html=$html."<tr>";
		$html=$html."<td style=background-color:#CCCCCC><strong>{message_id}</td>";
		$html=$html."<td>{$array["message_id"]}</td>";
		$html=$html."</tr>";
		$html=$html."<tr>";
		$html=$html." <td style=background-color:#CCCCCC><strong>{subject}</td>";
		$html=$html." <td>{$array["subject"]}</td>";
		$html=$html."</tr>	";
		$html=$html."<tr>";
		$html=$html." <td style=background-color:#CCCCCC><strong>{spam}</td>";
		$html=$html." <td>$spam</td>";
		$html=$html."</tr>	";		
	    $html=$html."</table>";
		return $html;
		
		
	}
	

	
	
}