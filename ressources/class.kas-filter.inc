<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.artica.inc');
include_once(dirname(__FILE__). '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.mysql.inc');


class kas_mysql{
	var $ou;
	var $q;
	
	function kas_mysql($ou){
		$this->ou=$ou;
		$this->q=new mysql();
	}
	
	
	public function GET_KEY($key){
		$sql="SELECT `value` FROM kas3 WHERE ou='$this->ou' AND `key`='$key'";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		//echo "<H1>$key:{$ligne["value"]}</H1>";
		return $ligne["value"];		
		
	}
	
	public function SET_VALUE($key,$value){
		$sql="SELECT `ID` FROM kas3 WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){
			$sql="INSERT INTO kas3  (`ou`,`key`,`value`) VALUES('$this->ou','$key','$value')";
		}else{
			$sql="UPDATE kas3 SET `value`='$value' WHERE ID={$ligne["ID"]}";
		}
		
		writelogs("$sql  [$key]",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nSET_VALUE($key,$value)";
		}
	}	
	
}



class kasUpdater{
	var $updater_data;
	var $array_updater_data;	
	
	
	function KasUpdater(){
		$sock=new sockets();
		writelogs("INFOS: Send socket to local server",__FUNCTION__,__FILE__);
		$this->updater_data=$sock->getfile('kas3ViewUpdateConf');
		writelogs("INFOS: done",__FUNCTION__,__FILE__);
		$this->parseUpdater();
	}
	
	function parseUpdater(){
		$ini=new Bs_IniHandler();
		$ini->loadString($this->updater_data);
		$this->array_updater_data=$ini->_params;
		
		
		
		
	}
	function SaveFile(){
		$ini=new Bs_IniHandler();
		//Apply global proxy configuration....
		$artica=new artica_general();
		if($artica->ArticaProxyServerEnabled=='yes'){
			$this->array_updater_data["updater.options"]["UseProxy"]="yes";
			$this->array_updater_data["updater.options"]["ProxyAddress"]="$artica->ArticaCompiledProxyUri";
		}
		
		
		
		
		$ini->_params=$this->array_updater_data;
		$ini->saveFile(dirname(__FILE__) . '/conf/kas_updater_conf');
		$sock=new sockets();
		$sock->getfile('kasUpdaterConf:' .dirname(__FILE__) . '/conf/kas_updater_conf');		
		if($sock->error){return false;}return true;
	}
	
}

class KasLicence{
	var $filedatas;
	var $array_licence;
	var $fulldata;

	
	function KasLicence(){
		$sock=new sockets();
		writelogs("INFOS: Send socket to local server",__FUNCTION__,__FILE__);
		$this->filedatas=$sock->getfile('kas3ViewLicenceInfos');
		writelogs("INFOS: done " . strlen($this->filedatas) . " bytes",__FUNCTION__,__FILE__);
		$this->ParseLicence();
	}
	
	function ParseLicence(){
		$tbl=explode("\n",$this->filedatas);
		$numCount=0;
		while (list ($num, $val) = each ($tbl) ){
			$val=trim($val);
			if($val==null){continue;}
			$this->fulldata=$this->fulldata.$val."<br>";
			
			
			if (preg_match('#Emails\s+([0-9]+)#i',$val,$regs)){
				$array[$numCount]["number_mails"]=$regs[1];
			}
			unset($regs);
			 if (preg_match('#Valid till:\s+(.+)\s+\(expires in ([0-9]+)#i',$val,$regs)){
			 	$array[$numCount]["valid_date"]=$regs[1];
			 	$array[$numCount]["expire_days"]=$regs[2];
			 }
			 
			 if(preg_match('#([A-Za-z_\-0-9]+)\s+([0-9\-A-Z]+)\s+([A-Za-z]+)\s+(\w+)\s+([0-9]+)\s+([a-z\sA-Z0-9]+)#',$val,$regs)){
			 	$array[$numCount]["key"]=$regs[1];
			 	$array[$numCount]["serial"]=$regs[2];
			 	$array[$numCount]["keytype"]=$regs[3];
			 	$array[$numCount]["licence"]=$regs[4];
			 	$array[$numCount]["volume"]=$regs[5];
			 	$array[$numCount]["expires"]=$regs[6];
			 	
			 }
			
		}
		$this->array_licence=$array;
		
	}
	
}


class kas_dns{
	var $array_datas;
	var $file_data;
	var $dn;
	function kas_dns(){
		$this->LoadConfig();
		
	}	
	
	function LoadConfig(){
		$ldap=new clladp();
		$this->dn="cn=kaspersky Antispam 3 rules,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($this->dn)){
			$upd["ObjectClass"][]="top";
			$upd["ObjectClass"][]="KasperskyAntiSpamSettings";
			$upd["cn"][0]="kaspersky Antispam 3 rules";
			$upd["KasperskyAntispamRules"][0]="DEFAULT";
			$upd["KasperskyAntispamFilterConf"][0]="DEFAULT";
			$upd["KasperskyAntispamDNSConf"][0]="DEFAULT";
			$ldap->ldap_add($this->dn,$upd);
			}
		$sr =@ldap_read($ldap->ldap_connection,$this->dn,'objectClass=KasperskyAntiSpamSettings');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			$this->file_data=$hash[0][strtolower("KasperskyAntispamDNSConf")][0];	
		}
		if($this->file_data==null){
			unset($upd);
			$upd["KasperskyAntispamDNSConf"][0]="DEFAULT";
			$ldap->Ldap_add_mod($this->dn,$upd);
		}
		$this->parseFile();		
		$this->FillDefaults();	
		
	}

	function parseFile(){
		if($this->file_data=="DEFAULT"){$this->FillDefaults();return null;}
		$s=explode("\n",$this->file_data);
		if(!is_array($s)){return null;}
		
		while (list ($num, $val) = each ($s) ){
			if(preg_match('#(.+)="(.+?)"#',$val,$regs)){
				$this->array_datas[$regs[1]]=$regs[2];
			}
		}
	}

	function FillDefaults(){
		writelogs("lines number=" . count($this->array_datas) ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(count($this->array_datas)>0){return null;}
		$this->array_datas["bl.spamcop.net"]=30;
		$this->array_datas["list.dsbl.org"]=50;
		$this->array_datas["dnsbl.njabl.org"]=50;
		$this->array_datas["relays.ordb.org"]=70;
		$this->array_datas["xbl-sbl.spamhaus.org"]=50;
		$this->SaveDatas();
		}
		
	function SaveDatas(){
		writelogs("lines number=" . count($this->array_datas) ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list ($num, $val) = each ($this->array_datas) ){
			if($num<>null){$line=$line . "$num=\"$val\"\n";}
		
	}
		$ldap=new clladp();
		writelogs("Save " . strlen($line) . " bytes" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$upd["KasperskyAntispamDNSConf"][0]=$line;
		$ldap->Ldap_modify($this->dn,$upd);
		echo $ldap->ldap_last_error;
		
	}
	
	function SaveToServer(){
		$rbls_xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
 		$rbls_xml=$rbls_xml."<DNSBlackList name=\"Common DNSBL list\" description=\"\">\n";
		
 		$rbls_xml2="<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
 		$rbls_xml2=$rbls_xml2 ."#include <base/base-lists.xml.macro>\n";
 		$rbls_xml2=$rbls_xml2 ."BEGIN_COMMON_RBL_LIST\n";
 		while (list ($num, $val) = each ($this->array_datas) ){
 			$rbls_xml=$rbls_xml."<Service description=\"\" zone=\"$num\" weight=\"$val\"/>\n";
 			$rbls_xml2=$rbls_xml2."RBL_ENTRY(\"$num\",$val)\n";
 		}
 		$rbls_xml=$rbls_xml."</DNSBlackList>\n";
 		$rbls_xml2=$rbls_xml2."END_COMMON_RBL_LIST\n";
 		
 		if (!$fp = fopen(dirname(__FILE__) . '/conf/kas-rbl.1', 'wb')) {return FALSE;}
    		if (!fwrite($fp, $rbls_xml)){return FALSE;}
    		@fclose($fp);
    		
		if (!$fp = fopen(dirname(__FILE__) . '/conf/kas-rbl.2', 'wb')) {return FALSE;}
    		if (!fwrite($fp, $rbls_xml2)){return FALSE;}
    		@fclose($fp);
		
    		$sock=new sockets();
    		$sock->getfile("kasdnsbl:".dirname(__FILE__) . '/conf');
    		
    		  			
	}
	
	
}


class kas_filter{
	var $file_data;
	var $dn;
	var $array_updater_data;
	var $array_datas;
	var $error=false;
	
	function kas_filter(){
		$this->LoadConfig();
	}
	
	function KasStatus(){
		$sock=new sockets();
		$pattern=$this->GetPatternDate();
		$datas=$sock->getfile('kasStatus');
		$datas=explode("\n",$datas);
		$st="
		<H5>{kas_status}</h5>
		<table style='width:100%'>";
		while (list ($key, $value) = each ($datas) ){
			if(trim($value)<>null){
			$tb=explode(';',$value);
			if($tb[2]==1){$img="img/ok24.png";}else{$img="img/warning24.png";}
			$st=$st.
			"<tr>
				<td width=1%' valign='top'><img src='$img'></td>
				<td valign='top' style='padding-bottom:5px'><strong style='font-size:11px'>{$tb[0]}</strong><br><i>&nbsp;PID:{$tb[1]}&nbsp;{$tb[3]} mb mem.</i></td>
			</tr>";
			}
			
		}	
			$st=$st."<tr>
					<td width=1%' valign='top'><img src='img/Database32.png'></td>
					<td valign='top' style='padding-bottom:5px'><strong style='font-size:11px'>Pattern:<br>$pattern</td>
				</tr>";			
		return $st;
	}
	
	
	function FillDefaults(){
		if($this->array_datas["ServerMaxFilters"]==null){$this->array_datas["ServerMaxFilters"]=10;}
		if($this->array_datas["ServerStartFilters"]==null){$this->array_datas["ServerStartFilters"]=0;}
		if($this->array_datas["ServerSpareFilters"]==null){$this->array_datas["ServerSpareFilters"]=0;}
		if($this->array_datas["FilterMaxMessages"]==null){$this->array_datas["FilterMaxMessages"]=300;}
		if($this->array_datas["FilterRandMessages"]==null){$this->array_datas["FilterRandMessages"]= 30;}
		if($this->array_datas["FilterMaxIdle"]==null){$this->array_datas["FilterMaxIdle"]= 300;}
		if($this->array_datas["FilterDelayedExit"]==null){$this->array_datas["FilterDelayedExit"]= 0;}
		if($this->array_datas["FilterDataTimeout"]==null){$this->array_datas["FilterDataTimeout"]= 30;}
		if($this->array_datas["FilterLicenseConnectTimeout"]==null){$this->array_datas["FilterLicenseConnectTimeout"]= 2;}
		if($this->array_datas["FilterLicenseDataTimeout"]==null){$this->array_datas["FilterLicenseDataTimeout"]= 3;}
		if($this->array_datas["FilterSPFDataTimeout"]==null){$this->array_datas["FilterSPFDataTimeout"]= 1;}
		if($this->array_datas["FilterDNSTimeout"]==null){$this->array_datas["FilterDNSTimeout"]= 10;}
		if($this->array_datas["FilterReceivedHeadersLimit"]==null){$this->array_datas["FilterReceivedHeadersLimit"]= 2;}
		if($this->array_datas["FilterParseMSOffice"]==null){$this->array_datas["FilterParseMSOffice"]='no';}
		if($this->array_datas["FilterUserLogFile"]==null){$this->array_datas["FilterUserLogFile"]= '/usr/local/ap-mailfilter3/log/filter.log';}
		if($this->array_datas["FilterStatLogFile"]==null){$this->array_datas["FilterStatLogFile"]= '/usr/local/ap-mailfilter3/log/filter.log';}
		if($this->array_datas["FilterUDSTimeout"]==null){$this->array_datas["FilterUDSTimeout"]= 10;}
		if($this->array_datas["FilterUDSEnabled"]==null){$this->array_datas["FilterUDSEnabled"]= 'no';}
		if($this->array_datas["LicenseMaxConnections"]==null){$this->array_datas["LicenseMaxConnections"]= 200;}
		if($this->array_datas["ClientConnectTimeout"]==null){$this->array_datas["ClientConnectTimeout"]= 40;}
		if($this->array_datas["ClientConnectTo"]==null){$this->array_datas["ClientConnectTo"]='tcp:127.0.0.1:2277';}
		if($this->array_datas["ClientOnError"]==null){$this->array_datas["ClientOnError"]='accept';}
		if($this->array_datas["ClientDefaultDomain"]==null){$this->array_datas["ClientDefaultDomain"]= 'localhost';}
		if($this->array_datas["ClientFilteringSizeLimit"]==null){$this->array_datas["ClientFilteringSizeLimit"]= 500;}
		if($this->array_datas["ClientMessageStoreMem"]==null){$this->array_datas["ClientMessageStoreMem"]= 0;}
		if($this->array_datas["SendMailAddress"]==null){$this->array_datas["SendMailAddress"]='unix:/var/run/kas-milter.socket';}
		if($this->array_datas["QMailOriginalQueue"]==null){$this->array_datas["QMailOriginalQueue"]='/var/qmail/bin/qmail-queue.kas';}
		if($this->array_datas["CGProAllTransports"]==null){$this->array_datas["CGProAllTransports"]='no';}
		if($this->array_datas["ControlCenterSendAlertsTo"]==null){$this->array_datas["ControlCenterSendAlertsTo"]='postmaster';}
		if($this->array_datas["ControlCenterLang"]==null){$this->array_datas["ControlCenterLang"]='en';}
		if($this->array_datas["MonitoringHttpd"]==null){$this->array_datas["MonitoringHttpd"]='yes';}
		if($this->array_datas["MonitoringKasMilter"]==null){$this->array_datas["MonitoringKasMilter"]='no';}
		if($this->array_datas["PipeInProtocol"]==null){$this->array_datas["PipeInProtocol"]='smtp';}
		if($this->array_datas["PipeOutProtocol"]==null){$this->array_datas["PipeOutProtocol"]='smtp';}
		if($this->array_datas["PipeOutgoingAddr"]==null){$this->array_datas["PipeOutgoingAddr"]='tcp:127.0.0.1:9025';}
		if($this->array_datas["PipeUseXForward"]==null){$this->array_datas["PipeUseXForward"]='yes';}
		if($this->array_datas["ClientDataTimeout"]==null){$this->array_datas["ClientDataTimeout"]='30';}
		
		}
	
	private function LoadConfig(){
		$sql="SELECT `value`,`key` FROM kas3 WHERE ou='filter.conf'";
		$q=new mysql();
		$count=0;
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$this->array_datas[$ligne["key"]]=$ligne["value"];
		}
		$this->FillDefaults();	
		
	}
	
	function GetPatternDate(){
		$sock=new sockets();
		return trim($sock->getfile('KasPatternDate'));
		
	}
	function GetTrapUpdatesError(){
		$sock=new sockets();
		$datas=$sock->getfile('KasTrapUpdatesErrors');
		$tbl=explode("\n",$datas);
		if(is_array($tbl)){
			return array_reverse($tbl);
		}
	}
	function GetTrapSuccessUpdates(){
		$sock=new sockets();
		$datas=$sock->getfile('KasTrapUpdatesSuccess');
		$tbl=explode("\n",$datas);
		if(is_array($tbl)){
			return array_reverse($tbl);
		}
	}	
	function ForceUpdateKas(){
		$sock=new sockets();
		$datas=$sock->getfile('KasForceUpdatesFromErrors');
		
	}
	function KasUpdateNow(){
		$sock=new sockets();
		$datas=$sock->getfile('KasUpdatesPatternNow');		
	}
	
	function CronTask(){
		
		$sock=new sockets();
		$result=trim($sock->getfile('KasGetCronTask'));
		if(trim($result<>null)){
			include_once(dirname(__FILE__).'/class.cron.inc');
			$cron=new cron(1);
			$array=$cron->ParseCronCommand($result,null,1);
			return  "{run_every} {$array["MONTH"]} {months}, {$array["DAY"]} {days}, {$array["HOURS"]}, {hours} {$array["MINUTES"]} {minutes} ";
		}else{return "{disabled}";}

	}
	
	function SaveConf(){
		$kas=new kas_mysql("filter.conf");
		reset($this->array_datas);
		while (list ($key, $val) = each ($this->array_datas) ){
			$kas->SET_VALUE($key,$val);
		}
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{success}');
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?kas-reconfigure=yes");
		
	}
	
	

	
		
	function SaveFile(){
    	$sock=new sockets();
    	$sock->getfile('kasconf:'.dirname(__FILE__) . '/conf/kas-filter.conf');
    	return TRUE;	
	}
	
	



	
	
	
	function GetRX($line,$key,$array){
		$line12= addcslashes($line,"\ \\[]()!?-;$@{}#/=%*;,:<>&~\"'|+");
		$line12=htmlentities($line12);
		$len=strlen($line12);
		$line12=str_replace('\\','&#92;',$line12);
		$array["_N_ACTION_"  . $key . "_SUBJECT_PREFIX_RX"]=0;
		$array["_L_ACTION_"  . $key . "_SUBJECT_PREFIX_RX"]=$len;
		$array["ACTION_"  . $key . "_SUBJECT_PREFIX_RX"]="$line12";
		return $array;
		

	}
	
	
	
	function BuildHexGroupName($num){
		$num=dechex($num);
		$num_len=strlen($num);
		$numZero=8-$num_len;
		for($i=0;$i<$numZero;$i++){
			$rt=$rt . "0";
		}
		return strtoupper($rt . "$num");
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}


class kas_user{
	
	var $rules_datas;
	var $mail_array;
	var $uid;
	var $ou;
	
	function kas_user($uid=null,$ou=null){
		$this->uid=$uid;
		$this->ou=$ou;
		$ldap=new clladp();
		
		if($this->ou<>null){
			$hash=$ldap->OUDatas($this->ou);
		}else{$hash=$ldap->UserDatas($uid);}
		$this->rules_datas=$hash["KasperkyASDatasRules"];
		$this->ParseRules();
		$this->FillDefault();
	}
	
	function ParseRules(){
		if($this->rules_datas==null){return null;}
		$tpl=explode("\n",$this->rules_datas);
		while (list ($num, $val) = each ($tpl) ){
			if(preg_match('#(.+?)="(.+?)"#',$val,$reg)){
				$this->mail_array[$reg[1]]=$reg[2];
			}
		}
		
	}
	function FillDefault(){
		if($this->mail_array["detection_rate"]==null){$this->mail_array["detection_rate"]=45;}
		if($this->mail_array["action_quarantine"]==null){$this->mail_array["action_quarantine"]=1;}
		if($this->mail_array["action_killmail"]==null){$this->mail_array["action_killmail"]=1;}
		if($this->mail_array["prepend_text"]==null){$this->mail_array["prepend_text"]="[SPAM!!!]";}
		if($this->mail_array["detection_rate1"]==null){$this->mail_array["second_rate"]=90;}
		if($this->mail_array["action_quarantine1"]==null){$this->mail_array["second_quarantine"]=0;}
		if($this->mail_array["action_killmail1"]==null){$this->mail_array["second_killmail"]=1;}
			
	}
	function SaveConf(){
		while (list ($num, $val) = each ($this->mail_array) ){
			$conf=$conf . "$num=\"$val\"\n";
		}
		
		
	$ldap=new clladp();
	if($this->ou<>null){
		$dn="ou=$this->ou,$ldap->suffix";
		}
		else{
			$hash=$ldap->UserDatas($this->uid);
			$dn=$hash["dn"];
		}
		$upd["KasperkyASDatasRules"][0]=$conf;
		if($ldap->Ldap_modify($dn,$upd)){return true;}else{echo $ldap->ldap_last_error;}
		return false;
	}
	
	
	
}

class kas_single{
	
	var $dn;
	var $main_array;
	var $datas;
	var $CommonActionDef;
	var $ACTION_SPAM_MODE;
	var $ACTION_SPAM_MODE_FIELD=array("-1"=>"{reject}","-3"=>"{delete}","0"=>"{pass}");
	var $ACTION_PROBABLE_MODE;
	var $ACTION_PROBABLE_USERINFO;
	var $ACTION_SPAM_SUBJECT_PREFIX;
	var $ACTION_PROBABLE_SUBJECT_PREFIX;
	var $ACTION_BLACKLISTED_SUBJECT_PREFIX;
	var $ACTION_FORMAL_SUBJECT_PREFIX;
	
	function kas_single(){
		
			$sock=new sockets();
			$this->datas=$sock->GET_INFO("KasperskyAntispamRules");
			$this->CommonActionDef=$sock->GET_INFO("KasperskyAntiSpamDefaultGroupCommonActionDef");
			if($this->CommonActionDef==null){$this->CommonActionDef=$this->DefaultGroupCommonActionDef();}
			$this->ParseDatas();	
		}
		
		
	
	
	function Save(){
	   $this->CommonActionDef=$this->DefaultGroupCommonActionDef();
		
		$sock=new sockets();
		$sock->SaveConfigFile($this->build_00000000_rule_def(),"KasperskyAntispamRules");
		$sock->SaveConfigFile($this->DefaultGroupCommonActionDef(),"KasperskyAntiSpamDefaultGroupCommonActionDef");
		$sock->getfile('kasconf:/etc/toto/titi');
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{APP_KAS3}: {success}');
		writelogs("done..",__CLASS__.'/'.__FUNCTION__,__FILE__);
	}
	
	
	function build_00000000_rule_def(){
		
		if($this->main_array["OPT_SPAM_RATE_LIMIT"]==null){$this->main_array["OPT_SPAM_RATE_LIMIT"]=3;}
		if($this->main_array["OPT_USE_LISTS"]==null){$this->main_array["OPT_USE_LISTS"]==1;}
		if($this->main_array["OPT_USE_DNS"]==null){$this->main_array["OPT_USE_DNS"]==0;}		
		
			$conf=$conf ."#pragma once\n";
			$conf=$conf ."#include <common/common-rule.def>\n";
			$conf=$conf ."\n";
			$conf=$conf ."<!-- INCLUDE: __FILE__ Group defines and opts settings (set by artica) -->\n";
			$conf=$conf ."\n";
			$conf=$conf ."BEGIN_GROUP_DEFS(0x00000000,\"Default group\",\"\")\n";
			$conf=$conf ."#ifndef _GROUP_ID\n";
			$conf=$conf ."#define _GROUP_ID 0x00000000\n";
			$conf=$conf ."#define _GROUP_ID_STR \"00000000\"\n";
			$conf=$conf ."#define _GROUP_NAME \"Default group\"\n";
			$conf=$conf ."#define _GROUP_MEMO \"\"\n";
			$conf=$conf ."#define _GROUP_MEMBERS \"\"\n";
			$conf=$conf ."#define _GROUP_ALLOWED_EMAILS \"00000000-allow.xml\"\n";
			$conf=$conf ."#define _GROUP_ALLOWED_IPS \"00000000-ipallow.xml\"\n";
			$conf=$conf ."#define _GROUP_DENIED_EMAILS \"00000000-deny.xml\"\n";
			$conf=$conf ."#define _GROUP_DENIED_IPS \"00000000-ipdeny.xml\"\n";
			$conf=$conf ."#endif\n";
			$conf=$conf ."END_GROUP_HEADER\n";
			
			while (list ($num, $val) = each ($this->main_array) ){
				$conf=$conf ."#define $num 	\"$val\"\n";
				$conf=$conf ."#define _N_{$num} 	0\n";
				$conf=$conf ."#define _L_{$num} 	".strlen($val)."\n\n";
				
			}
			
			$conf=$conf ."END_GROUP_DEFS\n";
			return $conf;		
		
	}
	
	
	
	function FillDefaults(){
		$sock=new sockets();
		return $sock->getfile("KasperskyAntispamRulesDef");
		}
		
	function ParseDatas(){
		
		
		if($this->datas==null){$this->datas=$this->FillDefaults();}
		$tbl=explode("\n",$this->datas);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)==null){continue;}
			if(preg_match('#define _(.+)#',$val)){continue;}
			if(preg_match('#define _(.+)#',$val)){continue;}
			
			if (preg_match('#[define|\s]+([A-Z\_]+)\s+"([0-9]+)"#',$val,$keys)){
				$this->main_array[$keys[1]]=$keys[2];
			}
			
			
		}
		

		
		
		
		writelogs("CommonActionDef=".strlen($this->CommonActionDef)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$tbl=explode("\n",$this->CommonActionDef);
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#define ACTION_SPAM_MODE\s+"(.+?)"#',$val,$re)){
				$this->ACTION_SPAM_MODE=$re[1];
				
			}
			if(preg_match('#define ACTION_PROBABLE_MODE\s+"(.+?)"#',$val,$re)){
				$this->ACTION_PROBABLE_MODE=$re[1];
				
			}
			
			if(preg_match('#define ACTION_PROBABLE_USERINFO\s+"(.+?)"#',$val,$re)){
				$this->ACTION_PROBABLE_USERINFO=$re[1];
				
			}			
			
			if(preg_match('#define ACTION_SPAM_SUBJECT_PREFIX\s+"(.+?)"#',$val,$re)){
				$this->ACTION_SPAM_SUBJECT_PREFIX=$re[1];
				
			}

			if(preg_match('#define ACTION_PROBABLE_SUBJECT_PREFIX\s+"(.+?)"#',$val,$re)){
				$this->ACTION_PROBABLE_SUBJECT_PREFIX=$re[1];
				
			}

			if(preg_match('#define\s+ACTION_BLACKLISTED_SUBJECT_PREFIX\s+"(.+?)"#',$val,$re)){
				writelogs("ACTION_BLACKLISTED_SUBJECT_PREFIX={$re[1]};",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->ACTION_BLACKLISTED_SUBJECT_PREFIX=$re[1];
				
			}


			if(preg_match('#define\s+ACTION_FORMAL_SUBJECT_PREFIX\s+"(.+?)"#',$val,$re)){
				$this->ACTION_FORMAL_SUBJECT_PREFIX=$re[1];
				
			}						
								
			

		}
			
			
			
		
		
		
	}
	
function MakeRX($value){
		$line12= addcslashes($value,"\ \\[]()!?-;$@{}#/=%*;,:<>&~\"'|+");
		$line12=htmlentities($line12);
		$len=strlen($line12);
		$line12=str_replace('\\','&#92;',$line12);
		return array($line12,$len);
}
	
	
function DefaultGroupCommonActionDef(){
	if($this->ACTION_SPAM_MODE==null){$this->ACTION_SPAM_MODE=0;}
	if($this->ACTION_PROBABLE_MODE==null){$this->ACTION_PROBABLE_MODE=0;}
	if($this->ACTION_PROBABLE_USERINFO==null){$this->ACTION_PROBABLE_USERINFO="PROBABLE";}
	if($this->ACTION_SPAM_SUBJECT_PREFIX==null){$this->ACTION_SPAM_SUBJECT_PREFIX="[SPAM]";}
	if($this->ACTION_PROBABLE_SUBJECT_PREFIX==null){$this->ACTION_PROBABLE_SUBJECT_PREFIX="[PROBALE_SPAM]";}
	if($this->ACTION_FORMAL_SUBJECT_PREFIX==null){$this->ACTION_FORMAL_SUBJECT_PREFIX=".";}
	
	$ACTION_FORMAL_SUBJECT_PREFIX=$this->ACTION_FORMAL_SUBJECT_PREFIX;
	$ACTION_FORMAL_SUBJECT_PREFIX_RX=$this->MakeRX($ACTION_FORMAL_SUBJECT_PREFIX_RX);
	
	$ACTION_BLACKLISTED_SUBJECT_PREFIX=$this->ACTION_BLACKLISTED_SUBJECT_PREFIX;
	if(trim($ACTION_BLACKLISTED_SUBJECT_PREFIX)==null){$ACTION_BLACKLISTED_SUBJECT_PREFIX=".";}
	$ACTION_BLACKLISTED_SUBJECT_PREFIX_RX=$this->MakeRX($ACTION_BLACKLISTED_SUBJECT_PREFIX);
	
	
	if($this->ACTION_SPAM_SUBJECT_PREFIX==null){$this->ACTION_SPAM_SUBJECT_PREFIX=".";}
	$ACTION_SPAM_SUBJECT_PREFIX_RX=$this->MakeRX($this->ACTION_SPAM_SUBJECT_PREFIX);
	$ACTION_PROBABLE_SUBJECT_PREFIX_RX=$this->MakeRX($this->ACTION_SPAM_SUBJECT_PREFIX);
	
	
	//Formal
	$ACTION_FORMAL_SUBJECT_PREFIX=$this->ACTION_FORMAL_SUBJECT_PREFIX;
	if(trim($ACTION_FORMAL_SUBJECT_PREFIX)==null){$ACTION_FORMAL_SUBJECT_PREFIX=".";}
	$ACTION_FORMAL_SUBJECT_PREFIX_RX=$this->MakeRX($ACTION_FORMAL_SUBJECT_PREFIX);
	
	
$action_def="#pragma once
#include <common/common-action.def>

<!-- INCLUDE: __FILE__ Group actions settings (set by artica " .date('Y-m-d H:I:s').") -->

BEGIN_GROUP_DEFS(0x00000000,\"Default group\",\"\")
#ifndef _GROUP_ID
#define _GROUP_ID 0x00000000
#define _GROUP_ID_STR \"00000000\"
#define _GROUP_NAME \"Default group\"
#define _GROUP_MEMO \"\"
#define _GROUP_MEMBERS \"\"
#define _GROUP_ALLOWED_EMAILS \"00000000-allow.xml\"
#define _GROUP_ALLOWED_IPS \"00000000-ipallow.xml\"
#define _GROUP_DENIED_EMAILS \"00000000-deny.xml\"
#define _GROUP_DENIED_IPS \"00000000-ipdeny.xml\"
#endif
END_GROUP_HEADER
#define ACTION_PROBABLE_SUBJECT_PREFIX 	\"$this->ACTION_PROBABLE_SUBJECT_PREFIX\"
#define _N_ACTION_PROBABLE_SUBJECT_PREFIX 	0
#define _L_ACTION_PROBABLE_SUBJECT_PREFIX 	".strlen($this->ACTION_PROBABLE_SUBJECT_PREFIX)."
#define ACTION_PROBABLE_SUBJECT_PREFIX_RX 	\"{$ACTION_PROBABLE_SUBJECT_PREFIX_RX[0]}\"
#define _N_ACTION_PROBABLE_SUBJECT_PREFIX_RX 	0
#define _L_ACTION_PROBABLE_SUBJECT_PREFIX_RX 	{$ACTION_PROBABLE_SUBJECT_PREFIX_RX[1]}


#define ACTION_BLACKLISTED_MODE 	\"0\"
#define _N_ACTION_BLACKLISTED_MODE 	0
#define _L_ACTION_BLACKLISTED_MODE 	1

#define ACTION_BLACKLISTED_SUBJECT_PREFIX 	\"$ACTION_BLACKLISTED_SUBJECT_PREFIX\"
#define _N_ACTION_BLACKLISTED_SUBJECT_PREFIX 	0
#define _L_ACTION_BLACKLISTED_SUBJECT_PREFIX 	".strlen($ACTION_BLACKLISTED_SUBJECT_PREFIX)."

#define ACTION_BLACKLISTED_SUBJECT_PREFIX_RX 	\"{$ACTION_BLACKLISTED_SUBJECT_PREFIX_RX[0]}\"
#define _N_ACTION_BLACKLISTED_SUBJECT_PREFIX_RX 	0
#define _L_ACTION_BLACKLISTED_SUBJECT_PREFIX_RX 	{$ACTION_BLACKLISTED_SUBJECT_PREFIX_RX[1]}

#define ACTION_FORMAL_USERINFO 	\"FORMAL\"
#define _N_ACTION_FORMAL_USERINFO 	0
#define _L_ACTION_FORMAL_USERINFO 	6
#define ACTION_FORMAL_MODE 	\"0\"
#define _N_ACTION_FORMAL_MODE 	0
#define _L_ACTION_FORMAL_MODE 	1

#define ACTION_FORMAL_SUBJECT_PREFIX_RX 	\"$ACTION_FORMAL_SUBJECT_PREFIX\"
#define _N_ACTION_FORMAL_SUBJECT_PREFIX_RX 	0
#define _L_ACTION_FORMAL_SUBJECT_PREFIX_RX 	".strlen($ACTION_FORMAL_SUBJECT_PREFIX)."
#define ACTION_FORMAL_SUBJECT_PREFIX 	\"{$ACTION_FORMAL_SUBJECT_PREFIX_RX[0]}\"
#define _N_ACTION_FORMAL_SUBJECT_PREFIX 	0
#define _L_ACTION_FORMAL_SUBJECT_PREFIX 	{$ACTION_FORMAL_SUBJECT_PREFIX_RX[1]}

#define ACTION_NORMAL_MODE 	\"0\"
#define _N_ACTION_NORMAL_MODE 	0
#define _L_ACTION_NORMAL_MODE 	1
#define ACTION_PROBABLE_MODE 	\"$this->ACTION_PROBABLE_MODE\"
#define _N_ACTION_PROBABLE_MODE 	$this->ACTION_PROBABLE_MODE
#define _L_ACTION_PROBABLE_MODE 	".strlen($this->ACTION_PROBABLE_MODE)."
#define ACTION_TRUSTED_SUBJECT_PREFIX 	\"\"
#define _N_ACTION_TRUSTED_SUBJECT_PREFIX 	0
#define _L_ACTION_TRUSTED_SUBJECT_PREFIX 	0

#define ACTION_NORMAL_SUBJECT_PREFIX 	\"\"
#define _N_ACTION_NORMAL_SUBJECT_PREFIX 	0
#define _L_ACTION_NORMAL_SUBJECT_PREFIX 	0
#define ACTION_TRUSTED_SUBJECT_PREFIX_RX 	\".\"
#define _N_ACTION_TRUSTED_SUBJECT_PREFIX_RX 	0
#define _L_ACTION_TRUSTED_SUBJECT_PREFIX_RX 	1
#define ACTION_PROBABLE_USERINFO 	\"$this->ACTION_PROBABLE_USERINFO\"
#define _N_ACTION_PROBABLE_USERINFO 	0
#define _L_ACTION_PROBABLE_USERINFO 	".strlen($this->ACTION_PROBABLE_USERINFO)."
#define ACTION_SPAM_MODE 	\"$this->ACTION_SPAM_MODE\"
#define _N_ACTION_SPAM_MODE 	$this->ACTION_SPAM_MODE
#define _L_ACTION_SPAM_MODE 	" . strlen($this->ACTION_SPAM_MODE)."
#define ACTION_SPAM_KEYWORDS 	\"SPAM\"
#define _N_ACTION_SPAM_KEYWORDS 	0
#define _L_ACTION_SPAM_KEYWORDS 	4
#define ACTION_NORMAL_USERINFO 	\"PASS\"
#define _N_ACTION_NORMAL_USERINFO 	0
#define _L_ACTION_NORMAL_USERINFO 	4


#define ACTION_BLACKLISTED_KEYWORDS 	\"BLACKLISTED\"
#define _N_ACTION_BLACKLISTED_KEYWORDS 	0
#define _L_ACTION_BLACKLISTED_KEYWORDS 	11
#define ACTION_FORMAL_SUBJECT_SUFFIX 	\"\"
#define _N_ACTION_FORMAL_SUBJECT_SUFFIX 	0
#define _L_ACTION_FORMAL_SUBJECT_SUFFIX 	0

#define ACTION_SPAM_SUBJECT_SUFFIX 	\"\"
#define _N_ACTION_SPAM_SUBJECT_SUFFIX 	0
#define _L_ACTION_SPAM_SUBJECT_SUFFIX 	0
#define ACTION_PROBABLE_SUBJECT_SUFFIX 	\"\"
#define _N_ACTION_PROBABLE_SUBJECT_SUFFIX 	0
#define _L_ACTION_PROBABLE_SUBJECT_SUFFIX 	0
#define ACTION_TRUSTED_SUBJECT_SUFFIX 	\"\"
#define _N_ACTION_TRUSTED_SUBJECT_SUFFIX 	0
#define _L_ACTION_TRUSTED_SUBJECT_SUFFIX 	0
#define ACTION_TRUSTED_KEYWORDS 	\"TRUSTED\"
#define _N_ACTION_TRUSTED_KEYWORDS 	0
#define _L_ACTION_TRUSTED_KEYWORDS 	7
#define ACTION_BLACKLISTED_SUBJECT_SUFFIX 	\"\"
#define _N_ACTION_BLACKLISTED_SUBJECT_SUFFIX 	0
#define _L_ACTION_BLACKLISTED_SUBJECT_SUFFIX 	0
#define ACTION_NORMAL_SUBJECT_SUFFIX 	\"\"
#define _N_ACTION_NORMAL_SUBJECT_SUFFIX 	0
#define _L_ACTION_NORMAL_SUBJECT_SUFFIX 	0
#define ACTION_SPAM_USERINFO 	\"SPAM\"
#define _N_ACTION_SPAM_USERINFO 	0
#define _L_ACTION_SPAM_USERINFO 	4
#define ACTION_PROBABLE_KEYWORDS 	\"PROBABLE\"
#define _N_ACTION_PROBABLE_KEYWORDS 	0
#define _L_ACTION_PROBABLE_KEYWORDS 	8
#define ACTION_NORMAL_KEYWORDS 	\"\"
#define _N_ACTION_NORMAL_KEYWORDS 	0
#define _L_ACTION_NORMAL_KEYWORDS 	0

#define ACTION_TRUSTED_MODE 	\"0\"
#define _N_ACTION_TRUSTED_MODE 	0
#define _L_ACTION_TRUSTED_MODE 	1
#define ACTION_BLACKLISTED_USERINFO 	\"BLACK\"
#define _N_ACTION_BLACKLISTED_USERINFO 	0
#define _L_ACTION_BLACKLISTED_USERINFO 	5
#define ACTION_FORMAL_KEYWORDS 	\"FORMAL\"
#define _N_ACTION_FORMAL_KEYWORDS 	0
#define _L_ACTION_FORMAL_KEYWORDS 	6
#define ACTION_NORMAL_SUBJECT_PREFIX_RX 	\".\"
#define _N_ACTION_NORMAL_SUBJECT_PREFIX_RX 	0
#define _L_ACTION_NORMAL_SUBJECT_PREFIX_RX 	1
#define ACTION_SPAM_SUBJECT_PREFIX_RX 	\"{$ACTION_SPAM_SUBJECT_PREFIX_RX[0]}\"
#define _N_ACTION_SPAM_SUBJECT_PREFIX_RX 	0
#define _L_ACTION_SPAM_SUBJECT_PREFIX_RX 	{$ACTION_SPAM_SUBJECT_PREFIX_RX[1]}
#define ACTION_SPAM_SUBJECT_PREFIX 	\"$this->ACTION_SPAM_SUBJECT_PREFIX\"
#define _N_ACTION_SPAM_SUBJECT_PREFIX 	0
#define _L_ACTION_SPAM_SUBJECT_PREFIX 	".strlen($this->ACTION_SPAM_SUBJECT_PREFIX)."
END_GROUP_DEFS\n";	
	
	
return $action_def;	
	
	
	
}
	
	
function SaveToserver(){
	
	$this->BuildDefaultFiles();
	
	
}
	
	function BuildDefaultFiles(){
		if($this->main_array["OPT_PROBABLE_SPAM_ON"]==null){$this->main_array["OPT_PROBABLE_SPAM_ON"]=1;}
		if($this->main_array["OPT_LANG_KOREAN"]==null){$this->main_array["OPT_LANG_KOREAN"]=0;}
		if($this->main_array["OPT_SPAM_RATE_LIMIT"]==null){$this->main_array["OPT_SPAM_RATE_LIMIT"]=3;}
		$rule_def="#pragma once
#include <common/common-rule.def>

<!-- INCLUDE: __FILE__ Group defines and opts settings (set by artica " .date('Y-m-d H:I:s').") -->

BEGIN_GROUP_DEFS(0x00000000,\"Default group\",\"\")
#ifndef _GROUP_ID
#define _GROUP_ID 0x00000000
#define _GROUP_ID_STR \"00000000\"
#define _GROUP_NAME \"Default group\"
#define _GROUP_MEMO \"\"
#define _GROUP_MEMBERS \"\"
#define _GROUP_ALLOWED_EMAILS \"00000000-allow.xml\"
#define _GROUP_ALLOWED_IPS \"00000000-ipallow.xml\"
#define _GROUP_DENIED_EMAILS \"00000000-deny.xml\"
#define _GROUP_DENIED_IPS \"00000000-ipdeny.xml\"
#endif
END_GROUP_HEADER
#define OPT_USE_SURBL 	\"{$this->main_array["OPT_USE_SURBL"]}\"
#define _N_OPT_USE_SURBL 	{$this->main_array["OPT_USE_SURBL"]}
#define _L_OPT_USE_SURBL 	1
#define OPT_USE_DNS 	\"{$this->main_array["OPT_USE_DNS"]}\"
#define _N_OPT_USE_DNS 	{$this->main_array["OPT_USE_DNS"]}
#define _L_OPT_USE_DNS 	1
#define OPT_CF_OBSCENE 	\"0\"
#define _N_OPT_CF_OBSCENE 	0
#define _L_OPT_CF_OBSCENE 	1
#define OPT_FILTRATION_ON 	\"{$this->main_array["OPT_FILTRATION_ON"]}\"
#define _N_OPT_FILTRATION_ON 	{$this->main_array["OPT_FILTRATION_ON"]}
#define _L_OPT_FILTRATION_ON 	1
#define OPT_HEADERS_FROM_OR_TO_NO_DOMAIN 	\"{$this->main_array["OPT_HEADERS_FROM_OR_TO_NO_DOMAIN"]}\"
#define _N_OPT_HEADERS_FROM_OR_TO_NO_DOMAIN 	{$this->main_array["OPT_HEADERS_FROM_OR_TO_NO_DOMAIN"]}
#define _L_OPT_HEADERS_FROM_OR_TO_NO_DOMAIN 	1
#define OPT_HEADERS_FROM_OR_TO_DIGITS 	\"{$this->main_array["OPT_HEADERS_FROM_OR_TO_DIGITS"]}\"
#define _N_OPT_HEADERS_FROM_OR_TO_DIGITS 	{$this->main_array["OPT_HEADERS_FROM_OR_TO_DIGITS"]}
#define _L_OPT_HEADERS_FROM_OR_TO_DIGITS 	1
#define OPT_HEADERS_SUBJECT_WS_OR_DOTS 	\"{$this->main_array["OPT_HEADERS_SUBJECT_WS_OR_DOTS"]}\"
#define _N_OPT_HEADERS_SUBJECT_WS_OR_DOTS 	{$this->main_array["OPT_HEADERS_SUBJECT_WS_OR_DOTS"]}
#define _L_OPT_HEADERS_SUBJECT_WS_OR_DOTS 	1
#define OPT_SPF 	\"{$this->main_array["OPT_SPF"]}\"
#define _N_OPT_SPF 	{$this->main_array["OPT_SPF"]}
#define _L_OPT_SPF 	1
#define OPT_DNS_DNSBL 	\"{$this->main_array["OPT_DNS_DNSBL"]}\"
#define _N_OPT_DNS_DNSBL 	{$this->main_array["OPT_DNS_DNSBL"]}
#define _L_OPT_DNS_DNSBL 	1
#define OPT_DNS_HOST_IN_DNS 	\"{$this->main_array["OPT_DNS_HOST_IN_DNS"]}\"
#define _N_OPT_DNS_HOST_IN_DNS 	{$this->main_array["OPT_DNS_HOST_IN_DNS"]}
#define _L_OPT_DNS_HOST_IN_DNS 	1
#define OPT_LANG_THAI 	\"{$this->main_array["OPT_LANG_THAI"]}\"
#define _N_OPT_LANG_THAI 	{$this->main_array["OPT_LANG_THAI"]}
#define _L_OPT_LANG_THAI 	1
#define OPT_PROBABLE_SPAM_ON 	\"{$this->main_array["OPT_PROBABLE_SPAM_ON"]}\"
#define _N_OPT_PROBABLE_SPAM_ON 	{$this->main_array["OPT_PROBABLE_SPAM_ON"]}
#define _L_OPT_PROBABLE_SPAM_ON 	1
#define OPT_HEADERS_SUBJECT_TOO_LONG 	\"{$this->main_array["OPT_HEADERS_SUBJECT_TOO_LONG"]}\"
#define _N_OPT_HEADERS_SUBJECT_TOO_LONG 	{$this->main_array["OPT_HEADERS_SUBJECT_TOO_LONG"]}
#define _L_OPT_HEADERS_SUBJECT_TOO_LONG 	1
#define OPT_HEADERS_TO_UNDISCLOSED 	\"{$this->main_array["OPT_HEADERS_TO_UNDISCLOSED"]}\"
#define _N_OPT_HEADERS_TO_UNDISCLOSED 	{$this->main_array["OPT_HEADERS_TO_UNDISCLOSED"]}
#define _L_OPT_HEADERS_TO_UNDISCLOSED 	1
#define OPT_LANG_KOREAN 	\"{$this->main_array["OPT_LANG_KOREAN"]}\"
#define _N_OPT_LANG_KOREAN 	{$this->main_array["OPT_LANG_KOREAN"]}
#define _L_OPT_LANG_KOREAN 	1
#define OPT_SPAM_RATE_LIMIT 	\"{$this->main_array["OPT_SPAM_RATE_LIMIT"]}\"
#define _N_OPT_SPAM_RATE_LIMIT 	{$this->main_array["OPT_SPAM_RATE_LIMIT"]}
#define _L_OPT_SPAM_RATE_LIMIT 	1
#define OPT_LANG_JAPANESE 	\"{$this->main_array["OPT_LANG_JAPANESE"]}\"
#define _N_OPT_LANG_JAPANESE 	{$this->main_array["OPT_LANG_JAPANESE"]}
#define _L_OPT_LANG_JAPANESE 	1
#define OPT_LANG_CHINESE 	\"{$this->main_array["OPT_LANG_CHINESE"]}\"
#define _N_OPT_LANG_CHINESE 	{$this->main_array["OPT_LANG_CHINESE"]}
#define _L_OPT_LANG_CHINESE 	1
#define OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID 	\"{$this->main_array["OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID"]}\"
#define _N_OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID 	{$this->main_array["OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID"]}
#define _L_OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID 	1
#define OPT_USE_LISTS 	\"0\"
#define _N_OPT_USE_LISTS 	0
#define _L_OPT_USE_LISTS 	1
END_GROUP_DEFS\n";


$profiles="<?xml version=\"1.0\" encoding=\"utf-8\"?>
#include <base/profiles.xml.macro>
BEGIN_PROFILE_REF_LIST
PROFILE_REF(0x00000000,\"00000000-profile.xml\",\"All\",\", \")
END_PROFILE_REF_LIST\n";

$emails_xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>
#include <base/emails.xml.macro>
BEGIN_EMAIL_REF_LIST
EMAIL_REF(0x00000000,\"00000000-allow.xml\",\"00000000-deny.xml\",\"00000000-members.xml\")
END_EMAIL_REF_LIST\n";

$iplists_xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>
#include <base/iplists.xml.macro>
BEGIN_IP_REF_LIST
IP_REF(0x00000000,\"00000000-ipallow.xml\",\"00000000-ipdeny.xml\")
END_IP_REF_LIST\n";

$deny="<?xml version=\"1.0\" encoding=\"utf-8\"?>
#include <base/group-deny.xml.macro>
BEGIN_GROUP_DENY_EMAIL_LIST(0x00000000)
END_GROUP_DENY_EMAIL_LIST\n\n";

$tempDir=dirname(__FILE__) . "/conf/kasDatas";
if(!is_dir($tempDir)){my_mkdir($tempDir);}	
				
	$tempfile=$tempDir . "/00000000-action.def";
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $this->DefaultGroupCommonActionDef()); 
	fclose($fp); 			
	
	$tempfile=$tempDir . "/00000000-rule.def";
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $rule_def); 
	fclose($fp); 

	$tempfile=$tempDir . "/00000000-deny.xml";
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $deny); 
	fclose($fp); 		
	
	$tempfile=$tempDir . "/profiles.xml";
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $profiles); 
	fclose($fp);	
	
	$tempfile=$tempDir . "/emails.xml";
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $emails_xml); 
	fclose($fp);	

	$tempfile=$tempDir . "/iplists.xml";
	$fp = fopen($tempfile, "w",0);
	fputs($fp, $iplists_xml); 
	fclose($fp);	

	$sock=new sockets();
	$sock->getfile('kasrules:'.$tempDir);
	
		
	}
	
}


?>