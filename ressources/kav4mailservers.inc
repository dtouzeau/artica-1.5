<?php

include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.ldap.inc');

class kav4mailservers{
	var $array_conf;
	var $LicenseDomains;
	var $licenceDatas;
	var $array_licence_infos;
	var $file_data;
	var $version;
	var $pattern_date;
	var $pid;
	var $memory;
	var $error=false;
	
	function kav4mailservers($nosocks=0,$datas=null,$forceSocket=0){
		$ldap=new clladp();
		$conf=$ldap->ArticaDatas();
		$datas=$conf["kav4mailservers"];
		$this->ParsePatternDate();
		
		if($datas==null){
			$sock=new sockets();
			$datas=$sock->getfile('kav4mailservers.conf');
			$this->error=$sock->error;
			if($this->error==true){return null;}
			$this->ParseLicenceDomains();
			$this->ParseLicenceInfos();			
			}else{
				if($forceSocket==1){
					$sock=new sockets();
					$sock_datas=$sock->getfile('kav4mailservers.conf');
					if($sock_datas<>null){$datas=$sock_datas;}
				}
			}
		 $this->file_data=$datas;	
		 $ini=new Bs_IniHandler();
		 $ini->loadString($datas);
		 $this->array_conf=$ini->_params;
		 $this->ImportGroupDatas();
		 $this->GetDaemonInfos();
		

	}
	
	
	function GetDaemonInfos(){
		$sock=new sockets();
		$datas=$sock->getfile('aveserver_infos');
	
		$datas=explode(';',$datas);
		$this->version=$datas[2];
		$this->pid=$datas[0];
		$this->memory=$datas[1];
	}
	
	function UpdatesError(){
		$sock=new sockets();
		$datas=$sock->getfile("aveserver_updates_errors");
		$datas=explode("\n",$datas);
		if(is_array($datas)){
			$datas=array_reverse($datas);
		}
		return $datas;
		
	}
	function UpdatesSuccess(){
		$sock=new sockets();
		$datas=$sock->getfile("aveserver_updates_success");
		$datas=explode("\n",$datas);
		if(is_array($datas)){
			$datas=array_reverse($datas);
		}
		return $datas;
		
	}	
	
	function DaemonErrors(){
		$sock=new sockets();
		$datas=$sock->getfile("aveserver_daemon_error");
		$datas=explode("\n",$datas);
		if(is_array($datas)){
			$datas=array_reverse($datas);
		}
		return $datas;		
		
	}
	
	function DaemonLastLogs(){
		$sock=new sockets();
		$datas=$sock->getfile("aveserver_daemon_lastlogs");
		
		$datas=explode("\n",$datas);
		if(is_array($datas)){
			$datas=array_reverse($datas);
		}
		return $datas;			
		
	}
	function CronTask(){
		
		$sock=new sockets();
		$result=trim($sock->getfile('KavGetCronTask'));
		if(trim($result<>null)){
			include_once(dirname(__FILE__).'/class.cron.inc');
			$cron=new cron(1);
			$array=$cron->ParseCronCommand($result,null,1);
			return  "{run_every} {$array["MONTH"]} {months}, {$array["DAY"]} {days}, {$array["HOURS"]}, {hours} {$array["MINUTES"]} {minutes} ";
		}else{return "{disabled}";}

	}	
	
	function ImportGroupDatas(){
		$ldap=new clladp();
		$h=$ldap->AllGroupsKavSettings();
		writelogs("AllGroupsKavSettings() return " . count($h) . " entries",__FUNCTION__,__FILE__);
		$this->CleanAllGroups();
		if(!is_array($h)){return null;}
		while (list ($num, $ligne) = each ($h) ){
			$array=$this->ParseGroupText($ligne);
			$this->array_conf=$this->array_conf+$array;
		}

	}
	function CleanAllGroups(){
		reset($this->array_conf);
		while (list ($num, $ligne) = each ($this->array_conf) ){
			if(preg_match('#smtpscan.group:([0-9]+)#',$num,$reg)){
				unset($this->array_conf[$num]);
			}
		}
	}
	
	function ParsePatternDate(){
		$usermenus=new usersMenus();
		$data=$usermenus->aveserver_pattern_date;
		if(preg_match('#([0-9]{1,2})([0-9]{1,2})([0-9]{1,4});([0-9]{1,2})([0-9]{1,2})#',$data,$regs)){
			$this->pattern_date="".$regs[3]. "/" .$regs[2]. "/" .$regs[1] . " " . $regs[4] . ":" . $regs[5]  . ' (moscow GMT)';
		}
		
		
		
	}
	
	function ParseGroupText($datas){
		$zini=new Bs_IniHandler(True);
		$zini->loadString($datas);
		
		return $zini->_params;
		}
	
	
	function ParseLicenceDomains(){
		$datas=$this->array_conf["smtpscan.license"]["LicenseDomains"];
		$datas=str_replace('\.','.',$datas);
		$tbl=explode(',',$datas);
		while (list ($num, $ligne) = each ($tbl) ){
			writelogs("LicenseDomains=$ligne ",__FUNCTION__,__FILE__);
			$this->LicenseDomains[$ligne]=$ligne;
		}
		
	}
	
	function CorrectDefaultValues(){
		if($this->array_conf["updater.options"]["ConnectTimeout"]==null){$this->array_conf["updater.options"]["ConnectTimeout"]=30;}
		if($this->array_conf["updater.options"]["PostUpdateCmd"]==null){$this->array_conf["updater.options"]["PostUpdateCmd"]="/etc/init.d/aveserver reload";}
		
		
	}
	
	function CorrectGroups(){
		while (list ($num, $ligne) = each ($this->array_conf) ){
			if(preg_match('#smtpscan.group:([0-9]+)#',$num,$f)){
				writelogs("Try to correct missing values for " . $f[1] . " group",__FUNCTION__,__FILE__);
				$this->_CorrectErrors($f[1]);
			}
		}
	}
	
	function _CorrectErrors($groupname){
		writelogs("Try to correct infos from group $groupname");
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["SuspiciousQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["SuspiciousQuarantine"]="yes";}
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["InfectedQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["InfectedQuarantine"]="yes";}
		
		
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["Check"])){$this->array_conf["smtpscan.group:$groupname"]["Check"]="yes";}
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["AdminAddress"])){$this->array_conf["smtpscan.group:$groupname"]["AdminAddress"]="postmaster@localhost";}
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["AddXHeaders"])){$this->array_conf["smtpscan.group:$groupname"]["AddXHeaders"]="yes";}
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["AdminNotify"])){$this->array_conf["smtpscan.group:$groupname"]["AdminNotify"]="yes";}
		
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["RecipientAttachReport"])){$this->array_conf["smtpscan.group:$groupname"]["RecipientAttachReport"]="yes";}
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["RecipientAttachReport"])){$this->array_conf["smtpscan.group:$groupname"]["RecipientAttachReport"]="yes";}
		
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["InfectedSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["InfectedSenderNotify"]="no";}
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["InfectedRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["InfectedRecipientNotify"]="yes";}	
			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["SuspiciousSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["SuspiciousSenderNotify"]="no";}			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["SuspiciousRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["SuspiciousRecipientNotify"]="yes";}	
		
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["CuredSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["CuredSenderNotify"]="no";}	
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["CuredRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["CuredRecipientNotify"]="no";}	
				
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["CorruptedSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["CorruptedSenderNotify"]="no";}			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["CorruptedRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["CorruptedRecipientNotify"]="yes";}

		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["FilteredSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["FilteredSenderNotify"]="no";}			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["FilteredRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["FilteredRecipientNotify"]="yes";}		
			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["ErrorSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["ErrorSenderNotify"]="no";}			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["ErrorRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["ErrorRecipientNotify"]="yes";}		
		
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["ProtectedSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["ProtectedSenderNotify"]="no";}			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["ProtectedRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["ProtectedRecipientNotify"]="yes";}	

		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["WarningSenderNotify"])){$this->array_conf["smtpscan.group:$groupname"]["WarningSenderNotify"]="no";}			
		if(!preg_match('#(yes|no)#',$this->array_conf["smtpscan.group:$groupname"]["WarningRecipientNotify"])){$this->array_conf["smtpscan.group:$groupname"]["WarningRecipientNotify"]="yes";}				
		
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["RecipientAction"])){$this->array_conf["smtpscan.group:$groupname"]["RecipientAction"]="remove";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["CuredAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["CuredAdminAction"]="cured";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["CuredRecipientAction"])){$this->array_conf["smtpscan.group:$groupname"]["CuredRecipientAction"]="cured";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["ProtectedRecipientAction"])){$this->array_conf["smtpscan.group:$groupname"]["ProtectedRecipientAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["InfectedAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["InfectedAdminAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["SuspiciousAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["SuspiciousAdminAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["CorruptedAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["CorruptedAdminAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["FilteredAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["FilteredAdminAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["ErrorAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["ErrorAdminAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["ProtectedAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["ProtectedAdminAction"]="unchanged";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["WarningAdminAction"])){$this->array_conf["smtpscan.group:$groupname"]["WarningAdminAction"]="unchanged";}
				
		
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["WarningQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["WarningQuarantine"]="no";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["CuredQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["CuredQuarantine"]="no";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["CorruptedQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["CorruptedQuarantine"]="yes";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["ErrorQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["ErrorQuarantine"]="yes";}
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["ProtectedQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["ProtectedQuarantine"]="yes";}
				
		if(!isset($this->array_conf["smtpscan.group:$groupname"]["FilteredQuarantine"])){$this->array_conf["smtpscan.group:$groupname"]["FilteredQuarantine"]="yes";}
		}
		
		
	function Load_template_notify_datas($family,$type){
		$ldap=new clladp();
		$notifyLdapField="TemplateNotify$family$type";
		$value=$ldap->ArticaDatasFromField($notifyLdapField);
		writelogs("Load template $family $type ->" . strlen($value) . " caracters ",__FUNCTION__,__FILE__);
		
		if($value==null){
			writelogs("Load template $family $type -> socket to artica daemon",__FUNCTION__,__FILE__);
			$sock=new sockets();
			$value=$sock->getfile("AveTemplate:". strtolower($family)."_".strtolower($type));
			if($value<>null){$ldap->ArticaDatasAddField($notifyLdapField,$value);}
			}
			
		if(preg_match('#<subject>(.+)</subject#is',$value,$regs)){$res["SUBJECT"]=$regs[1];}else{writelogs("unable to match subject pattern for this data...",__FUNCTION__,__FILE__);}
		if(preg_match('#<template>(.+)</template>#is',$value,$regs)){$res["TPL"]=$regs[1];}else{writelogs("unable to match template pattern for this data...",__FUNCTION__,__FILE__);}

		return $res;
		}
	
	function ParseLicenceInfos(){
		
		$datas=$this->licenceDatas;
		if(preg_match('#Expiration date:(.+)?#i',$datas,$regs)){
			$array["EXPIRE"]=$regs[1];
		}
		
		if(preg_match('#Expiration date:(.+)?#i',$datas,$regs)){
			$array["EXPIRE"]=$regs[1];
		}
		if(preg_match('#Product name:(.+)?#i',$datas,$regs)){
			$array["NAME"]=$regs[1];
		}
		
		$this->array_licence_infos=$array;
	}
	
	function IsDomainLicenced($domain){
		if(!is_array($this->LicenseDomains)){return false;}
		writelogs("LicenseDomains= " . $this->LicenseDomains[$domain] . "?=$domain ",__FUNCTION__,__FILE__);
		if($this->LicenseDomains[$domain]<>$domain){return false;}else{return true;}
	}
	
	function SaveLdapDomains(){
		$ldap=new clladp();
		$this->LicenseDomains=$ldap->hash_get_all_domains();
		
	}
	
	function Save($nosock=0){
		$this->ImportGroupDatas();
		$this->SaveLdapDomains();
		if(is_array($this->LicenseDomains)){
			$this->array_conf["smtpscan.license"]["LicenseDomains"]=implode(",",$this->LicenseDomains);
			$this->array_conf["smtpscan.license"]["LicenseDomains"]=trim($this->array_conf["smtpscan.license"]["LicenseDomains"]);
			$this->array_conf["smtpscan.license"]["LicenseDomains"]=str_replace('.','\.',$this->array_conf["smtpscan.license"]["LicenseDomains"]);
			if(substr($this->array_conf["smtpscan.license"]["LicenseDomains"],0,1)==','){
				$this->array_conf["smtpscan.license"]["LicenseDomains"]=substr($this->array_conf["smtpscan.license"]["LicenseDomains"],1,strlen($this->array_conf["smtpscan.license"]["LicenseDomains"]));
			}
			
			writelogs("LicenseDomains= " . $this->array_conf["smtpscan.license"]["LicenseDomains"] . " ",__FUNCTION__,__FILE__);
		}
		
		$this->CorrectDefaultValues();
		$this->UseOnlyDefautltGroup();
		$this->CorrectGroups();
		$ini=new Bs_IniHandler();
		unset($this->array_conf["smtpscan.group:"]);
		$this->CheckDefaultGroup();
		// finally save all ----------------------------------------------------------------------------------------------------------------
		$ini->_params=$this->array_conf;
		$ldap=new clladp();
		$update_array["KasperkyAVScanningDatas"]=$ini->toString();
		if($ldap->Ldap_modify("cn=artica,$ldap->suffix",$update_array)==false){echo $ldap->ldap_last_error;return false;}
		$this->SaveToServer();
		return true;
		
		
		
	}
	
	function CheckDefaultGroup(){
		if(!isset($this->array_conf["smtpscan.group:default"])){
			$this->array_conf["smtpscan.group:default"]["Check"]="yes";
			$this->array_conf["smtpscan.group:default"]["AdminAddress"]="postmaster@localhost";
			$this->array_conf["smtpscan.group:default"]["Quarantine"]="no";
			$this->array_conf["smtpscan.group:default"]["AddXHeaders"]="yes";
			$this->array_conf["smtpscan.group:default"]["AdminNotify"]="yes";
			$this->array_conf["smtpscan.group:default"]["AdminAction"]="unchanged";
			$this->array_conf["smtpscan.group:default"]["SenderNotify"]="no";
			$this->array_conf["smtpscan.group:default"]["RecipientNotify"]="yes";
			$this->array_conf["smtpscan.group:default"]["RecipientAttachReport"]="yes";
			$this->array_conf["smtpscan.group:default"]["RecipientAction"]="remove";
			$this->array_conf["smtpscan.group:default"]["CuredAdminAction"]="cured";
			$this->array_conf["smtpscan.group:default"]["CuredRecipientAction"]="cured";
			$this->array_conf["smtpscan.group:default"]["CuredRecipientAction"]="unchanged";
			}
 
	}
	
	function UseOnlyDefautltGroup(){
		include_once(dirname(__FILE__) . '/class.artica.inc');
		$ldap=new clladp();
		$default_group_gid=$ldap->ArticaDefaultGroupGid();		
		$artica=new artica_general();
		
		if($artica->EnableGroups=='yes'){
			unset($this->array_conf["smtpscan.group:$default_group_gid"]);
			return null;
		}else{
			writelogs("Group feature is disabled... is default group $default_group_gid",__FUNCTION__,__FILE__);
			}
		
		writelogs("Array has " . count($this->array_conf) . " entries find pattern [smtpscan.group:$default_group_gid]",__FUNCTION__,__FILE__);
		reset($this->array_conf);
		while (list ($num, $ligne) = each ($this->array_conf) ){
			if(preg_match('#smtpscan.group:([0-9]+)#',$num,$reg)){
				if($reg[1]<>$default_group_gid){
					writelogs("Delete old group :{$reg[1]}",__FUNCTION__,__FILE__);
					unset($this->array_conf[$num]);
					}
			}
		}
		
		$domains=$this->LicenseDomains;
		if(is_array($domains)){
			while (list ($num, $ligne) = each ($domains) ){
				$arr[]="*@$ligne";
				}
			$this->array_conf["smtpscan.group:$default_group_gid"]["Recipients"]=implode(',',$arr);
		}
		
		$countdatas=count($this->array_conf["smtpscan.group:$default_group_gid"]);
		$this->array_conf["smtpscan.group:$default_group_gid"]["Recipients"]=str_replace('.','\.',$this->array_conf["smtpscan.group:$default_group_gid"]["Recipients"]);
		$this->array_conf["smtpscan.group:$default_group_gid"]["Recipients"]=str_replace('*','.*',$this->array_conf["smtpscan.group:$default_group_gid"]["Recipients"]);
		writelogs("Recipients= " .$this->array_conf["smtpscan.group:$default_group_gid"]["Recipients"]. "",__FUNCTION__,__FILE__);
		
		
	}
	
	function SaveToServer(){
		include_once(dirname(__FILE__) . '/class.users.menus.inc');
		$menus=new usersMenus();
		if($menus->kas_installed==true){
			$this->array_conf["smtpscan.general"]["ForwardMailer"]='smtp:127.0.0.1:9025';
			$this->array_conf["smtpscan.general"]["Protocol"]='smtp';
			}
		
		
		$ini=new Bs_IniHandler(dirname(__FILE__). '/conf/kav4mailservers.conf',True);
		$ini->_params=$this->array_conf;
		$this->SaveTemplatesToServer();
		$ini->saveFile(dirname(__FILE__). '/conf/kav4mailservers.conf');
		$sock=new sockets();
		$sock->getfile('kav4mailservers:' .dirname(__FILE__). '/conf/kav4mailservers.conf');
	}
	
	function SaveTemplatesToServer(){
		$types=array("corrupted","cured","suspicious","filtered","error","infected");
		$dest=array("admin","sender","recipient");
		while (list ($num, $action) = each ($types) ){
			reset($dest);
			while (list ($num, $destinataire) = each ($dest) ){
				$file="notify_$action"."_". "$destinataire";
				$LdapField="TemplateNotify$action$destinataire";
				$key="smtpscan.notify.$destinataire.$action";
				$Htemplate=$this->Load_template_notify_datas($action,$destinataire);
				writelogs("Save template name: $LdapField -> Subject " . strlen($Htemplate["SUBJECT"] ). " caracters template " .   strlen($Htemplate["TPL"]) . " caracters");
				writelogs("Save template name: $file");
				writelogs("Save template name: $key");
				if(strlen($Htemplate["TPL"])>0){
					$tempfile=dirname(__FILE__) . '/conf/'.$file;
					writelogs("OK Save template : $tempfile");
					$this->array_conf[$key]["Subject"]=$Htemplate["SUBJECT"];
					writelogs("writing $tempfile" ,__FUNCTION__,__FILE__);
					$fp = fopen($tempfile, "w",0); #open for writing
		  			fputs($fp, $Htemplate["TPL"]); #write all of $data to our opened file
		  			fclose($fp); #close the file	
				}			
				}
		}
		
		
		
	}
}
	
class kav4mailservers_single{
	var $ou;
	var $main_array;
	var $main_datas;
	var $dn;
	
	function kav4mailservers_single($ou=null){
		$ldap=new clladp();
		if($ou==null){
		 	$hash=$ldap->ArticaDatas();
			$this->dn="cn=artica,$ldap->suffix";
		}else{
			$hash=$ldap->OUDatas($ou);
			$this->dn="ou=$ou,dc=organizations,$ldap->suffix";
		}
		
		$this->main_datas=$hash["kav4mailservers"];
		$this->ParseMainDatas();
		$this->GetDefaultValue();
		
	}
	
	
	function GetDefaultValue(){
		if($this->main_array["NotifyFromAddress"]==null){$this->main_array["NotifyFromAddress"]="postmaster";}
		if($this->main_array["DeleteDetectedVirus"]==null){$this->main_array["DeleteDetectedVirus"]=1;}
		if($this->main_array["NotifyFrom"]==null){$this->main_array["NotifyFrom"]=1;}
		if($this->main_array["NotifyTo"]==null){$this->main_array["NotifyTo"]=1;}
		if($this->main_array["ArchiveMail"]==null){$this->main_array["ArchiveMail"]=1;}
		if($this->main_array["NotifyMessageSubject"]==null){$this->main_array["NotifyMessageSubject"]="%SUBJECT%";}
		if(trim($this->main_array["NotifyMessageTemplate"])==null){$this->main_array["NotifyMessageTemplate"]="<p><font face=\"arial,helvetica,sans-serif\" size=\"4\" color=\"#ff0000\#>Warning !!</font></p><p>The message %SUBJECT% sended by %SENDER% For %MAILTO% was infected please, try to send your messages without any viruses.</p><p><strong>Virus detected</strong> :</p><blockquote><p>%VIRUS%&nbsp;</p></blockquote>";}
		
		
	}
	
	function ParseMainDatas(){
		$tpl=explode("\n",$this->main_datas);
		if(count($tpl)==0){return null;}
		while (list ($num, $line) = each ($tpl) ){
			if(preg_match('#(.+?)="(.+?)"#',$line,$reg)){
				$this->main_array[$reg[1]]=$reg[2];
			}
		}
		unset($this->main_array["NotifyMessageTemplate"]);
		if(preg_match('#<NotifyMessageTemplate>(.+?)</NotifyMessageTemplate>#',$this->main_datas,$reg)){
			$this->main_array["NotifyMessageTemplate"]=$reg[1];
		}
		
	}
	
function SaveConf(){
	
	$NotifyMessageTemplate=$this->main_array["NotifyMessageTemplate"];
	unset($this->main_array["NotifyMessageTemplate"]);
	while (list ($k, $v) = each ($this->main_array) ){
		$line=$line . "$k=\"$v\"\n";
	}
	$NotifyMessageTemplate=str_replace('\"',"\"",$NotifyMessageTemplate);
		$line="$line\n<NotifyMessageTemplate>$NotifyMessageTemplate</NotifyMessageTemplate>";
		$ldap=new clladp();
		$upd["KasperkyAVScanningDatas"][0]=$line;
		$ldap->Ldap_modify($this->dn,$upd);
	}
}
	


?>