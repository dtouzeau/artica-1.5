<?php
include_once(dirname(__FILE__) .'/class.templates.inc');
include_once(dirname(__FILE__) .'/class.ldap.inc');
include_once(dirname(__FILE__) .'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.groups.inc');
include_once(dirname(__FILE__).'/class.auto-aliases.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.privileges.inc');


class user{
	var $DoesNotExists;
	var $DisplayName;
	var $domainname;
	var $password;
	var $uid;
	var $NotASambaUser=false;
	var $mail;
	var $aliases;
	var $UserExists=false;
	var $error;
	var $ou;
	var $dn;
	var $group_id;
	var $mailDir;
	var $homeDirectory;
	var $local_sid;
	var $samba_installed=false;
	var $objectClass_array=array();
	var $MailboxActive;
	var $ArticaUserFilterRule;
	var $FTPStatus;
	var $accountActive;
	var $accountGroup;
	var $sambaLogonTime;
	var $sambaLogoffTime;
	var $sambaKickoffTime;
	var $sambaPwdCanChange;
	var $sambaPrimaryGroupSID;
	var $sambaAcctFlags;
	var $sambaNTPassword;
	var $sambaLMPassword;
	var $sambaHomePath;
	var $sambaHomeDrive;
	var $sambaProfilePath;
	var $SambaAdminServerIPDefined;
	var $DisableAccountLessThan4Caracters=false;	
	var $MailingListAddressGroup=0;
	var $zarafaAdmin=0;
		
	var $uidNumber;
	var $FinalDateToLive=0;
	var $vacationActive="FALSE";
	var $vacationStart=null;
	var $vacationEnd=null;
	var $vacationInfo=null;
	
	
	var $FTPDownloadBandwidth=550;	
	var $FTPDownloadRatio=5;	
	var $FTPQuotaFiles=50;	
	var $FTPQuotaMBytes=1000;	
	var $FTPUploadBandwidth=550;	
	var $FTPUploadRatio=1;	
	
	//Joomla
	var $JoomlaGroup;
	
	//amavis
	var $amavisSpamLover="FALSE";
	var $amavisBadHeaderLover="FALSE";
	var $amavisBypassVirusChecks="FALSE";
	var $amavisBypassSpamChecks="FALSE";
	var $amavisBypassHeaderChecks="FALSE";
	var $amavisSpamTagLevel="-999";
	var $amavisSpamTag2Level="5";
	var $amavisSpamKillLevel="5";
	var $amavisSpamModifiesSubj="TRUE";
	var $FetchMailMatchAddresses=array();	
	var $EnableBackupAccount=0;
	var $RsyncBackupTargetPath=null;
	
	var $gecos;
	var $loginShell;
	var $sambaSID;
	var $sambaPrimaryGroupGID;
	var $gidNumber_array=array();
	var $givenName;
	var $AsAnSambaAccount;
	var $attributs_array=array();
	var $homeDirectoryBinded=array();
	var $sn;
	var $samba_groups=array();
	var $cn;
	var $MailAlternateAddress;
	var $RecipientToAdd=array();
	var $ldap_error;
	var $SenderCanonical;
	var $fetchmail_rules=array();
	var $array_groups=array();
	var $ArticaInterfaceLogon;
	var $jpegPhoto;
	var $jpegPhotoError='';
	var $img_identity;
	var $jpegPhoto_datas='';
	var $thumbnail_path;
	var $telephoneNumber;
	var $mobile;
	var $title;
	var $postalCode;
	var $postalAddress;
	var $street;
	var $town;
	var $postOfficeBox;	
	var $email_addresses=array();
	
	var $MailboxSecurityParameters;
	var $MailBoxMaxSize;
	var $AllowedSMTPTroughtInternet;
	var $amavisWhitelistSender=array();
	var $amavisBlacklistSender=array();
	var $AlternateSmtpRelay;
	var $DotClearUserEnabled;
	var $EnableUserSpamLearning;
	var $vacationEnabled="FALSE";
	var $privileges_array=array();
	var $cyrus_imapd_installed=false;
	var $UserRelayHostForward=null;
	var $HASH_ALL_MAILS=array();
	var $UsersInterfaceDatas;
	var $ThumbnailPath;
	var $CryptedHome;
	var $CryptedHomePassword=null;
	var $CryptedHomeSize=null;
	var $SenderBccMaps=null;
	var $SAMBA_ENABLED=true;
	var $ArticaGroupPrivileges=null;
	
	var $zarafaQuotaWarn=0;
	var $zarafaQuotaSoft=0;
	var $zarafaQuotaHard=0;
	var $zarafaQuotaOverride=0;
	var $zarafaMbxLang;
	var $WebDavUser=0;
	var $EnableManageUsersTroughActiveDirectory=false;
	var $ldapClass;

			
	function user($uid=null,$dn=null){
		$users=new usersMenus();
		$this->cyrus_imapd_installed=$users->cyrus_imapd_installed;
		$group=new groups(null);
		$ldap=new clladp();
		$sock=new sockets();
		if($ldap->ldapFailed){return null;}
		$this->ldapClass=$ldap;
		$this->samba_installed=$users->SAMBA_INSTALLED;
		$DisableAccountLessThan4Caracters=$sock->GET_INFO("DisableAccountLessThan4Caracters");
		if(is_numeric($DisableAccountLessThan4Caracters)){
			if($DisableAccountLessThan4Caracters==1){$this->DisableAccountLessThan4Caracters=true;}
		}
		
		if($users->SAMBA_INSTALLED){
			$SambaEnabled=trim($sock->GET_INFO("SambaEnabled"));
			if(strlen($SambaEnabled)==0){$SambaEnabled=1;}
			if($SambaEnabled<>1){$this->SAMBA_ENABLED=false;}
		}else{
			$this->SAMBA_ENABLED=false;
		}
		
		$this->local_sid=$ldap->LOCAL_SID();
		$this->EnableManageUsersTroughActiveDirectory=$ldap->EnableManageUsersTroughActiveDirectory;
		
		if($uid<>null){
			if($this->EnableManageUsersTroughActiveDirectory){
				$this->LoadFromActiveDirectory($uid);
				return;
			}
		}
		
		
		$this->samba_groups=$group->samba_group_list();

		if($uid<>null){
			$this->uid=trim(strtolower($uid));
			$this->LoadUserDatas($dn);
			$this->GetDefaults();
			$this->GetGroups($this->uid);
			$this->draw_jpeg_photos();
			
		}
		
	}
	
	
	function GetDefaults(){
			$this->uid=trim($this->uid);
			$this->uid=str_replace("'","",$this->uid);
			$this->uid=str_replace(" ",".",$this->uid);
			if($this->password==null){$this->BuildDefaultPassword();}
			$emailsp=explode('@',$this->mail);
			if($this->domainname==null){$this->domainname=$emailsp[1];}
			if($this->DisplayName==null){$this->DisplayName=$this->uid;}
			
			if($this->postalCode==null){$this->postalCode='0000';}
			if($this->postalAddress==null){$this->postalAddress='none';}
			if($this->mobile==null){$this->mobile='00.00.00.00.00';}
			if($this->telephoneNumber==null){$this->telephoneNumber='00.00.00.00.00';}
			if($this->homeDirectory==null){$this->homeDirectory="/home/$this->uid";}
			if($this->mailDir==null){$this->mailDir="/home/$this->uid/mail";}
			if($this->MailboxActive==null){$this->MailboxActive="TRUE";}
			if($this->ArticaUserFilterRule==null){$this->ArticaUserFilterRule="DEFAULT";}
			if($this->FTPStatus==null){$this->FTPStatus="FALSE";}
			if($this->accountActive==null){$this->accountActive="TRUE";}
			if($this->EnableUserSpamLearning==null){$this->EnableUserSpamLearning=0;}
			if($this->sambaLogonTime==null){$this->sambaLogonTime=0;}
			if($this->sambaLogoffTime==null){$this->sambaLogoffTime=2147483647;}
			if($this->sambaKickoffTime==null){$this->sambaKickoffTime=2147483647;}
			if($this->sambaPwdCanChange==null){$this->sambaPwdCanChange=0;}
			if($this->gecos==null){$this->gecos=$this->DisplayName;}
			if($this->sambaAcctFlags==null){$this->sambaAcctFlags="[UX]";}
			if($this->loginShell==null){$this->loginShell="/bin/bash";}
			if($this->givenName==null){$this->givenName=$this->uid;}
			if($this->sn==null){$this->sn=$this->uid;}
			if($this->vacationActive==null){$this->vacationActive="FALSE";}
			if($this->AllowedSMTPTroughtInternet==null){$this->AllowedSMTPTroughtInternet=1;}
			if(count($this->array_groups)>0){$this->group_id=$this->array_groups[0];}
			if($this->vacationStart==null){$this->vacationStart=time();}
			if($this->vacationEnd==null){$this->vacationEnd=time()+172800;}
			if($this->vacationInfo==null){$this->vacationInfo="\nI am away from the office and to not have access to my email.\nI will respond after I return.\n\nThanks.";}
			if($this->vacationEnabled==null){$this->vacationEnabled="FALSE";}
			
			if($this->EnableBackupAccount==null){$this->EnableBackupAccount=0;}
			
			
			//AMAVIS
			if($this->amavisSpamLover==null){$this->amavisSpamLover="FALSE";}
			if($this->amavisBadHeaderLover==null){$this->amavisBadHeaderLover="FALSE";}
			if($this->amavisBypassVirusChecks==null){$this->amavisBypassVirusChecks="FALSE";}
			if($this->amavisBypassSpamChecks==null){$this->amavisBypassSpamChecks="FALSE";}
			if($this->amavisBypassHeaderChecks==null){$this->amavisBypassHeaderChecks="FALSE";}
			if($this->amavisSpamTagLevel==null){$this->amavisSpamTagLevel="-999";}
			if($this->amavisSpamTag2Level==null){$this->amavisSpamTag2Level="5";}
			if($this->amavisSpamKillLevel==null){$this->amavisSpamKillLevel="5";}
			if($this->amavisSpamModifiesSubj==null){$this->amavisSpamModifiesSubj="TRUE";}

			
			if($this->FTPDownloadBandwidth==null){$this->FTPDownloadBandwidth="550";}
			if($this->FTPDownloadRatio==null){$this->FTPDownloadRatio="5";}
			if($this->FTPQuotaFiles==null){$this->FTPQuotaFiles="50";}
			if($this->FTPQuotaMBytes==null){$this->FTPQuotaMBytes="50";}
			if($this->FTPStatus==null){$this->FTPStatus="FALSE";}
			if($this->FTPUploadBandwidth==null){$this->FTPUploadBandwidth="550";}
			if($this->FTPUploadRatio==null){$this->FTPUploadRatio="1";}		
			if($this->JoomlaGroup==null){$this->JoomlaGroup="Registered";}
			if($this->zarafaQuotaWarn==null){$this->zarafaQuotaWarn="0";}
			if($this->zarafaAdmin==null){$this->zarafaAdmin="0";}
			if($this->zarafaQuotaSoft==null){$this->zarafaQuotaSoft="0";}
			if($this->zarafaQuotaHard==null){$this->zarafaQuotaHard="0";}
			if($this->zarafaQuotaOverride==null){$this->zarafaQuotaOverride="0";}
			
			if($this->WebDavUser==null){$this->WebDavUser=0;}
			
			
			}
			
	function BuildDefaultPassword(){
		if($this->EnableManageUsersTroughActiveDirectory){return;}
		if(!is_array($this->array_groups)){
			if($this->group_id==null){
				$this->password=PasswordGenerator();
			}else{
				$this->array_groups[]=$this->group_id;
			}
		}
		if(!is_array($this->array_groups)){$this->password=PasswordGenerator();}
		reset($this->array_groups);
		while (list ($num, $ligne) = each ($this->array_groups)){
			$group=new groups($ligne);
			if($group->DefaultGroupPassword==null){continue;}
			$this->password=$group->DefaultGroupPassword;
			break;
		}
		
	}
	
	private function LoadFromActiveDirectory($uid){
		$this->uid=$uid;
		$ldap=new ldapAD();
		$sAMAccountName=$this->uid;
		$searchdn=$ldap->suffix;
		$sr =@ldap_search($ldap->ldap_connection,$searchdn,"(sAMAccountName=".$sAMAccountName.")");
		
		if(!$sr){
			writelogs("Unable to find : $uid stamp DoesNotExists has TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->DoesNotExists=true;
			return null;
		}
		
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (sAMAccountName=' . $sAMAccountName . ') -> aborting function search engine doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->DoesNotExists=true;
			return null;
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
		$this->dn=$ldap->_Get_dn_userid($this->uid);
		
		for($i=0;$i<$attrs["objectClass"]["count"];$i++){
			$class=$attrs["objectClass"][$i];
			$this->objectClass_array[$class]=$class;
		}
		
		for($i=0;$i<$attrs["gidNumber"]["count"];$i++){$this->gidNumber_array[$i]=$attrs["gidNumber"][$i];}		
		while (list ($num, $ligne) = each ($attrs)){if(!is_numeric($num)){$this->attributs_array[$num]=$num;}}
		$this->DisplayName=$attrs["displayName"][0];
		if($this->mail<>null){$this->mail=$attrs["mail"][0];$this->HASH_ALL_MAILS[]=$this->mail;}
		$this->givenName=$attrs["givenName"][0];
		$this->cn=$attrs["cn"][0];
		$this->accountGroup=$attrs["primarygroupid"][0];
		$this->sambaPrimaryGroupSID=$attrs["primarygroupid"][0];
		$this->sn=$attrs["sn"][0];
		$this->jpegPhoto=$attrs["jpegPhoto"][0];
		$this->jpegPhoto_datas=$attrs["jpegPhoto"][0];
		$this->telephoneNumber=$attrs["telephoneNumber"][0];
		$this->mobile=$attrs["mobile"][0];
		$this->title=$attrs["name"][0];
		$this->postalCode=$attrs["postalCode"][0];
		$this->postalAddress=$attrs["streetaddress"][0];
		$this->street=$attrs["streetaddress"][0];
		$this->town=$attrs["l"][0];
		$this->postOfficeBox=$attrs["postOfficeBox"][0];
		$this->UserExists=true;
		
		
		
	}
	
	
	function SaveAmavisConfig(){
		$upd=array();
		$upd["amavisSpamLover"][0]=$this->amavisSpamLover;
		$upd["amavisBadHeaderLover"][0]=$this->amavisBadHeaderLover;
		$upd["amavisBypassVirusChecks"][0]=$this->amavisBypassVirusChecks;
		$upd["amavisBypassSpamChecks"][0]=$this->amavisBypassSpamChecks;
		$upd["amavisBypassHeaderChecks"][0]=$this->amavisBypassHeaderChecks;
		$upd["amavisSpamTagLevel"][0]=$this->amavisSpamTagLevel;
		$upd["amavisSpamTag2Level"][0]=$this->amavisSpamTag2Level;
		$upd["amavisSpamKillLevel"][0]=$this->amavisSpamKillLevel;
		$upd["amavisSpamModifiesSubj"][0]=$this->amavisSpamModifiesSubj;
		writelogs("Saving amavis configuration for dn $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return false;}
		return true;
		}
		
	function DeleteAmavisConfig(){
		$upd=array();
		$upd["amavisSpamLover"]=$this->amavisSpamLover;
		$upd["amavisBadHeaderLover"]=$this->amavisBadHeaderLover;
		$upd["amavisBypassVirusChecks"]=$this->amavisBypassVirusChecks;
		$upd["amavisBypassSpamChecks"]=$this->amavisBypassSpamChecks;
		$upd["amavisBypassHeaderChecks"]=$this->amavisBypassHeaderChecks;
		$upd["amavisSpamTagLevel"]=$this->amavisSpamTagLevel;
		$upd["amavisSpamTag2Level"]=$this->amavisSpamTag2Level;
		$upd["amavisSpamKillLevel"]=$this->amavisSpamKillLevel;
		$upd["amavisSpamModifiesSubj"]=$this->amavisSpamModifiesSubj;
		$ldap=$this->ldapClass;
		$ldap->Ldap_del_mod($this->dn,$upd);
	}
	
	function SaveCyrusMailboxesParameters(){
		$upd=array();
		$ldap=new clladp();
		$upd["MailBoxMaxSize"][0]=$this->MailBoxMaxSize;
		$upd["MailboxActive"][0]=$this->MailboxActive;
		$upd["MailboxSecurityParameters"][0]=$this->MailboxSecurityParameters;
		writelogs("Saving cyrus Mailboxes parameters for dn $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return false;}
		
	}	
	

	
	function SieveScripts(){
				//SIEVE
			if($this->privileges_array["EnableSieveArticaScript"]==1){
				if($this->cyrus_imapd_installed){
					include_once(dirname(__FILE__).'/class.sieve.inc');
					$sieve=new clSieve($this->uid);
					$sieve->AddAutoScript();
				}
			}
		
	}
	
	
	function isEmailValid($email_to_check){
		writelogs("Checking $email_to_check",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(preg_match('#(.+?)@(.+?)#',$email_to_check,$re)){
			writelogs("{$re[1]} - {$re[2]} - {$re[3]} => OK",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return true;
		}else{
			writelogs("\"$email_to_check\" doesn't match (.+?)@(.+?)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		return false;
	}
			
			
	private function LoadUserDatas($userdn=null){
		$daemon_uid=posix_getuid();
		$ldap=$this->ldapClass;
		include_once(dirname(__FILE__)."/class.privileges.inc");
		if($this->ArticaGroupPrivileges==null){$this->ArticaGroupPrivileges="#";}
		$privs=new privileges($this->uid,$this->ArticaGroupPrivileges);
		$privilegesDatas=$privs->content;
		$this->privileges_array=$privs->privs;
		
		$uid=$this->uid;
		
		$group=new groups(null);
		if($uid==null){writelogs("Resquest an uid null, aborting",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);$this->DoesNotExists=true;return null;}
		if($uid==-100){return null;}
		
		
		$pattern_uid=$ldap->ldap_escape($uid);
		$searchdn="dc=organizations,$ldap->suffix";
		$sr =@ldap_search($ldap->ldap_connection,$searchdn,"(uid=".$pattern_uid.")");
		
		
		
		if(!$sr){
			writelogs("Unable to find : $uid stamp DoesNotExists has TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->DoesNotExists=true;
			return null;
		}
		
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (uid=' . $pattern_uid . ') -> aborting function search engine doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->DoesNotExists=true;
			return null;
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
		$this->dn=$ldap->_Get_dn_userid($this->uid);
		
		for($i=0;$i<$attrs["objectClass"]["count"];$i++){
			$class=$attrs["objectClass"][$i];
			$this->objectClass_array[$class]=$class;
		}
		
		
		
		
		for($i=0;$i<$attrs["gidNumber"]["count"];$i++){
			$this->gidNumber_array[$i]=$attrs["gidNumber"][$i];
		}		
		
		while (list ($num, $ligne) = each ($attrs)){
			if(!is_numeric($num)){
				$this->attributs_array[$num]=$num;
			}
		}
		if(!isset($this->objectClass_array["amavisAccount"])){$this->objectClass_array["amavisAccount"]=null;}
		
		$upd[]=array();
		if($this->objectClass_array["UserArticaClass"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){
				$upd["objectClass"][]=$ligne;
			}
			$upd["objectClass"][]="UserArticaClass";
			writelogs( "$this->dn::Adding new artica class UserArticaClass..",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$ldap->Ldap_modify($this->dn,$upd);
		}
		
		if($this->objectClass_array["ArticaSettings"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){
				$upd["objectClass"][]=$ligne;
			}
			$upd["objectClass"][]="ArticaSettings";
			writelogs( 'Adding new artica class ArticaSettings..',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$ldap->Ldap_modify($this->dn,$upd);
		}
		
		
		if($this->objectClass_array["amavisAccount"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){
				$upd["objectClass"][]=$ligne;
			}
			$upd["objectClass"][]="amavisAccount";
			writelogs( 'Adding new artica class amavisAccount..',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$ldap->Ldap_modify($this->dn,$upd);
		}
		
		if($this->objectClass_array["Vacation"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$updVac["objectClass"][]=$ligne;}
			writelogs("Adding new class 'Vacation' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$updVac["objectClass"]="Vacation";
			$updVac["vacationActive"][]="FALSE";
			if(!$ldap->Ldap_add_mod($this->dn,$updVac)){echo $ldap->ldap_last_error;return null;}
			unset($updVac);
			}		
			
			
		if($this->objectClass_array["PureFTPdUser"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$updVac["objectClass"][]=$ligne;}
			writelogs("Adding new class 'PureFTPdUser' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$updPure["objectClass"]="PureFTPdUser";
			if(!$ldap->Ldap_add_mod($this->dn,$updPure)){echo $ldap->ldap_last_error;return null;}
			unset($updPure);
			}
			
		if($this->objectClass_array["person"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$person["objectClass"][]=$ligne;}
			writelogs("Adding new class 'person' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$person["objectClass"]="person";
			if(!$ldap->Ldap_add_mod($this->dn,$person)){echo $ldap->ldap_last_error;return null;}
			unset($person);
			}	

		if($this->objectClass_array["organizationalPerson"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$person["objectClass"][]=$ligne;}
			writelogs("Adding new class 'organizationalPerson' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$person["objectClass"]="organizationalPerson";
			if(!$ldap->Ldap_add_mod($this->dn,$person)){echo $ldap->ldap_last_error;return null;}
			unset($person);
			}

		if($this->objectClass_array["zarafa-user"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$person["objectClass"][]=$ligne;}
			writelogs("Adding new class 'zarafa-user' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$person["objectClass"]="zarafa-user";
			if(!$ldap->Ldap_add_mod($this->dn,$person)){echo $ldap->ldap_last_error;return null;}
			unset($person);
			}				

		if($this->objectClass_array["inetOrgPerson"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$person["objectClass"][]=$ligne;}
			writelogs("Adding new class 'inetOrgPerson' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$person["objectClass"]="inetOrgPerson";
			if(!$ldap->Ldap_add_mod($this->dn,$person)){echo $ldap->ldap_last_error;return null;}
			unset($person);
			}		

		/*if($this->objectClass_array["evolutionPerson"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$person["objectClass"][]=$ligne;}
			writelogs("Adding new class 'evolutionPerson' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$person["objectClass"]="evolutionPerson";
			if(!$ldap->Ldap_add_mod($this->dn,$person)){echo $ldap->ldap_last_error;return null;}
			unset($person);
			}*/

			if($this->objectClass_array["mozillaOrgPerson"]==null){
			$cl=$this->objectClass_array;
			while (list ($num, $ligne) = each ($cl)){$person["objectClass"][]=$ligne;}
			writelogs("Adding new class 'mozillaOrgPerson' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$person["objectClass"]="mozillaOrgPerson";
			if(!$ldap->Ldap_add_mod($this->dn,$person)){echo $ldap->ldap_last_error;return null;}
			unset($person);
			}			

	
		$this->MailboxSecurityParameters=$attrs["MailboxSecurityParameters"][0];
		$this->homeDirectory=$attrs["homeDirectory"][0];
		$this->mailDir=$attrs["mailDir"][0];
		$this->DisplayName=$attrs["displayName"][0];
		$this->password=$attrs["userPassword"][0];
		$this->MailboxActive=$attrs["MailboxActive"][0];
		if(isset($attrs["ArticaUserFilterRule"][0])){$this->ArticaUserFilterRule=$attrs["ArticaUserFilterRule"][0];}
		$this->FTPStatus=$attrs["FTPStatus"][0];
		$this->accountActive=$attrs["accountActive"][0];
		$this->mail=$attrs["mail"][0];
		$this->HASH_ALL_MAILS[]=$this->mail;
		$this->uidNumber=$attrs["uidNumber"][0];
		$this->givenName=$attrs["givenName"][0];
		$this->cn=$attrs["cn"][0];
		if(isset($attrs["ThumbnailPath"][0])){$this->ThumbnailPath=$attrs["ThumbnailPath"][0];}
		if(isset($attrs["CryptedHome"][0])){$this->CryptedHome=$attrs["CryptedHome"][0];}
		$this->accountGroup=$attrs["accountGroup"][0];
		if(isset($attrs["sambaPrimaryGroupSID"][0])){$this->sambaPrimaryGroupSID=$attrs["sambaPrimaryGroupSID"][0];}
		if(isset($attrs["sambaLogonTime"][0])){$this->sambaLogonTime=$attrs["sambaLogonTime"][0];}
		if(isset($attrs["sambaLogoffTime"][0])){$this->sambaLogoffTime=$attrs["sambaLogoffTime"][0];}
		if(isset($attrs["sambaKickoffTime"][0])){$this->sambaKickoffTime=$attrs["sambaKickoffTime"][0];}
		if(isset($attrs["sambaPwdCanChange"][0])){$this->sambaPwdCanChange=$attrs["sambaPwdCanChange"][0];}
		if(isset($attrs["sambaSID"][0])){$this->sambaSID=$attrs["sambaSID"][0];}
		
		
		if(isset($attrs["sambaHomePath"][0])){$this->sambaHomePath=$attrs["sambaHomePath"][0];}
		if(isset($attrs["sambaHomeDrive"][0])){$this->sambaHomeDrive=$attrs["sambaHomeDrive"][0];}		
		if(isset($attrs["sambaProfilePath"][0])){$this->sambaProfilePath=$attrs["sambaProfilePath"][0];}
		if(isset($attrs["sambaAcctFlags"][0])){$this->sambaAcctFlags=$attrs["sambaAcctFlags"][0];}
		
		if(preg_match("#\\\(.+?)\\\#",$this->sambaProfilePath,$re)){
			$re[1]=str_replace('\\','',$re[1]);
			$this->SambaAdminServerDefined=$re[1];
		}
		
		$this->gecos=$attrs["gecos"][0];
		
		$this->loginShell=$attrs["loginShell"][0];	
		$this->sn=$attrs["sn"][0];
		$this->ArticaInterfaceLogon=$attrs["ArticaInterfaceLogon"][0];
		if(isset($attrs["jpegPhoto"][0])){$this->jpegPhoto=$attrs["jpegPhoto"][0];}
		$this->telephoneNumber=$attrs["telephoneNumber"][0];
		$this->mobile=$attrs["mobile"][0];
		if(isset($attrs["title"][0])){$this->title=$attrs["title"][0];}
		$this->postalCode=$attrs["postalCode"][0];
		$this->postalAddress=$attrs["postalAddress"][0];
		$this->street=$attrs["street"][0];
		$this->town=$attrs["l"][0];
		if(isset($attrs["postOfficeBox"][0])){$this->postOfficeBox=$attrs["postOfficeBox"][0];}
		$this->MailBoxMaxSize=$attrs["MailBoxMaxSize"][0];
		if(isset($attrs["AllowedSMTPTroughtInternet"][0])){$this->AllowedSMTPTroughtInternet=$attrs["AllowedSMTPTroughtInternet"][0];}
		if(isset($attrs["AlternateSmtpRelay"][0])){$this->AlternateSmtpRelay=$attrs["AlternateSmtpRelay"][0];}
		$this->FinalDateToLive=$attrs["FinalDateToLive"][0];
		$this->DotClearUserEnabled=$attrs["DotClearUserEnabled"][0];
		$this->PostFixUserTransport=$attrs["PostFixUserTransport"][0];
		$this->EnableUserSpamLearning=$attrs["EnableUserSpamLearning"][0];
		
		
		$this->vacationActive=$attrs["vacationActive"][0];
		$this->vacationStart=$attrs["vacationStart"][0];
		$this->vacationEnd=$attrs["vacationEnd"][0];
		$this->vacationInfo=$attrs["vacationInfo"][0];
		$this->vacationEnabled=$attrs["vacationEnabled"][0];
		$this->EnableBackupAccount=$attrs["EnableBackupAccount"][0];
		if(isset($attrs["ArticaGroupPrivileges"][0])){$this->ArticaGroupPrivileges=$attrs["ArticaGroupPrivileges"][0];}
		if(isset($attrs["RsyncBackupTargetPath"][0])){$this->RsyncBackupTargetPath=$attrs["RsyncBackupTargetPath"][0];}
		if(isset($attrs["UserRelayHostForward"][0])){$this->UserRelayHostForward=$attrs["UserRelayHostForward"][0];}
		if(isset($attrs["CryptedHomePassword"][0])){$this->CryptedHomePassword=$attrs["CryptedHomePassword"][0];}
		if(isset($attrs["CryptedHomeSize"][0])){$this->CryptedHomeSize=$attrs["CryptedHomeSize"][0];}
		if(isset($attrs["SenderBccMaps"][0])){$this->SenderBccMaps=$attrs["SenderBccMaps"][0];}
		if(isset($attrs["zarafaAdmin"][0])){$this->zarafaAdmin=$attrs["zarafaAdmin"][0];}
		if(isset($attrs["zarafaQuotaWarn"][0])){$this->zarafaQuotaWarn=$attrs["zarafaQuotaWarn"][0];}
		if(isset($attrs["zarafaQuotaSoft"][0])){$this->zarafaQuotaSoft=$attrs["zarafaQuotaSoft"][0];}
		if(isset($attrs["zarafaQuotaHard"][0])){$this->zarafaQuotaHard=$attrs["zarafaQuotaHard"][0];}
		if(isset($attrs["zarafaQuotaHard"][0])){$this->zarafaQuotaOverride=$attrs["zarafaQuotaHard"][0];}
		if(isset($attrs["zarafaMbxLang"][0])){$this->zarafaMbxLang=$attrs["zarafaMbxLang"][0];}
		
		
		
		
		//amavis
		if(isset($attrs["amavisSpamLover"][0])){$this->amavisSpamLover=$attrs["amavisSpamLover"][0];}
		if(isset($attrs["amavisBadHeaderLover"][0])){$this->amavisBadHeaderLover=$attrs["amavisBadHeaderLover"][0];}
		if(isset($attrs["amavisBypassVirusChecks"][0])){$this->amavisBypassVirusChecks=$attrs["amavisBypassVirusChecks"][0];}
		if(isset($attrs["amavisBypassSpamChecks"][0])){$this->amavisBypassSpamChecks=$attrs["amavisBypassSpamChecks"][0];}
		if(isset($attrs["amavisBypassHeaderChecks"][0])){$this->amavisBypassHeaderChecks=$attrs["amavisBypassHeaderChecks"][0];}
		if(isset($attrs["amavisSpamTagLevel"][0])){$this->amavisSpamTagLevel=$attrs["amavisSpamTagLevel"][0];}
		if(isset($attrs["amavisSpamTag2Level"][0])){$this->amavisSpamTag2Level=$attrs["amavisSpamTag2Level"][0];}
		if(isset($attrs["amavisSpamKillLevel"][0])){$this->amavisSpamKillLevel=$attrs["amavisSpamKillLevel"][0];}
		if(isset($attrs["amavisSpamModifiesSubj"][0])){$this->amavisSpamModifiesSubj=$attrs["amavisSpamModifiesSubj"][0];}
		$this->UserExists=true;
		
		//PureFTPD
		$this->FTPDownloadBandwidth=$attrs["FTPDownloadBandwidth"][0];
		$this->FTPDownloadRatio=$attrs["FTPDownloadRatio"][0];
		$this->FTPQuotaFiles=$attrs["FTPQuotaFiles"][0];
		$this->FTPQuotaMBytes=$attrs["FTPQuotaMBytes"][0];
		$this->FTPStatus=$attrs["FTPStatus"][0];
		$this->FTPUploadBandwidth=$attrs["FTPUploadBandwidth"][0];
		$this->FTPUploadRatio=$attrs["FTPUploadRatio"][0];
		
		if(isset($attrs["WebDavUser"][0])){$this->WebDavUser=$attrs["WebDavUser"][0];}
		if(isset($attrs["UsersInterfaceDatas"][0])){$this->UsersInterfaceDatas=$attrs["UsersInterfaceDatas"][0];}

		if($this->FinalDateToLive==null){$this->FinalDateToLive=0;}
		
		$this->JoomlaGroup=$attrs["JoomlaGroup"][0];
		
		if($daemon_uid>0){writelogs("($this->uid):: $this->uid = uidNumber:$this->uidNumber ;CryptedHome=\"$this->CryptedHome\",Vacation Enable=\"$this->vacationEnabled\"; vacationActive=\"$this->vacationActive\" EnableUserSpamLearning={$attrs["EnableUserSpamLearning"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		
		//uidNumber
		$newnumber=$this->uidNumberCheck(1);
		$old_id=$this->uidNumber;
		
		if($newnumber<>$old_id){
			$upd=array();
			writelogs("change $old_id to $newnumber",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd["uidNumber"][0]=$newnumber;
			$this->uidNumber=$upd["uidNumber"][0];
			$ldap->Ldap_modify($this->dn,$upd);
			$this->Samba_edit_user();			
		}
		
		if($this->uidNumber==2000){
			$ldap=new clladp();
			$upd=array();
			writelogs("$this->uidNumber = 2000 Create a new identifier for this user",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd["uidNumber"][0]=$this->uidNumberCheck();
			$this->uidNumber=$upd["uidNumber"][0];
			$ldap->Ldap_modify($this->dn,$upd);
			$this->Samba_edit_user();	
		}
		
		if(trim($this->uid)<>null){
			if($this->uidNumber==null){
				$upd=array();
				writelogs("Create a new identifier for this user",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$upd["uidNumber"][0]=$this->uidNumberCheck();
				$this->uidNumber=$upd["uidNumber"][0];
				$ldap->Ldap_modify($this->dn,$upd);
				$this->Samba_edit_user();	
			}
		}
		
		if($this->town==null){$this->town="Unknown town";}
		
		
		if($this->uid=="squidinternalauth"){return;}
		if(!isset($this->objectClass_array["sambaSamAccount"])){$this->objectClass_array["sambaSamAccount"]=null;}
		if($this->objectClass_array["sambaSamAccount"]<>null){$this->AsAnSambaAccount=1;}
		if($this->sambaPrimaryGroupSID<>null){$this->sambaPrimaryGroupGID=$group->samba_group_id_from_sid($this->sambaPrimaryGroupSID);}else{$this->NotASambaUser=true;}
		
		$asSamba=false;
		if(preg_match("#dc=samba#",$this->dn)){
			writelogs("This uid is in the samba organization $this->dn...#,$this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$asSamba=true;
		}
		if(!$asSamba){
	
	    if(@preg_match("#cn=".utf8_decode($this->cn).",ou=users,ou=(.+?),dc#",utf8_decode($this->dn),$re)){
	    	$this->ou=$re[1];
	    	
	    	}
		}else{
			writelogs("Unable to find ou in ".utf8_decode($this->dn),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		
		if($this->ou==null){
			$pattern_cn=$this->cn;
			$pattern_cn=str_replace('.','\.',$pattern_cn);
			$pattern_cn=str_replace('?','\?',$pattern_cn);
			$pattern_cn=str_replace('(','\(',$pattern_cn);
			$pattern_cn=str_replace(')','\)',$pattern_cn);
			$pattern_cn=trim($pattern_cn);
			
			writelogs("Search organization in $this->dn = \"cn=$pattern_cn,ou=users,ou=(.+?),dc=organizations\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(@preg_match("#cn=$pattern_cn,ou=users,ou=(.+?),dc=organizations#",$this->dn,$re)){
				$this->ou=$re[1];
				$this->ou=str_replace('users,ou=','',$this->ou);
	    		
			}else{
				writelogs("unable to find ou in $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
	   	$update_array=array();
		if(!$asSamba){
			
			$dn_master_branch="ou=users,ou=$this->ou,dc=organizations,$ldap->suffix";
			if($this->ou<>null){
				if(!$ldap->ExistsDN($dn_master_branch)){
				$update_array["objectClass"][]="top";
				$update_array["objectClass"][]="organizationalUnit";
				$update_array["ou"][]="users";
				if(!$ldap->ldap_add($dn_master_branch,$update_array)){
					writelogs("Unable to add master branch $dn_master_branch \"$this->ldap_last_error\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__); 
					echo basename(__FILE__).": Unable to add master branch\n$dn_master_branch\n$this->ldap_last_error";
					return false;
				}
				unset($update_array);
			
			}}}	

			
		if(!$asSamba){$nab=new nab($this->cn,$this->ou);}
		
		$this->MailAlternateAddress=$this->GetMailAlternateAddress();
	    if(isset($attrs["SenderCanonical"][0])){$this->SenderCanonical=$attrs["SenderCanonical"][0];}
	    if(!isset($attrs["FetchMailMatchAddresses"])){$attrs["FetchMailMatchAddresses"]["count"]=0;}
		if(!isset($attrs["FetchMailsRules"])){$attrs["FetchMailsRules"]["count"]=0;}
		if(!isset($attrs["mozillaSecondEmail"])){$attrs["mozillaSecondEmail"]["count"]=0;}
		if(!isset($attrs["homeDirectoryBinded"])){$attrs["homeDirectoryBinded"]["count"]=0;}
		if(!isset($attrs["RecipientToAdd"])){$attrs["RecipientToAdd"]["count"]=0;}
		if(!isset($attrs["mailAlias"])){$attrs["mailAlias"]["count"]=0;}
		if(!isset($attrs["amavisWhitelistSender"])){$attrs["amavisWhitelistSender"]["count"]=0;}
		if(!isset($attrs["amavisBlacklistSender"])){$attrs["amavisBlacklistSender"]["count"]=0;}
	    
	    
	    for($i=0;$i<$attrs["FetchMailMatchAddresses"]["count"];$i++){
	    	$this->FetchMailMatchAddresses[$attrs["FetchMailMatchAddresses"][$i]]=$attrs["FetchMailMatchAddresses"][$i];
	    	$this->HASH_ALL_MAILS[]=$attrs["FetchMailMatchAddresses"][$i];
	    	}	
	    for($i=0;$i<$attrs["FetchMailsRules"]["count"];$i++){$this->fetchmail_rules[]=$attrs["FetchMailsRules"][$i];}	
		for($i=0;$i<$attrs["mailAlias"]["count"];$i++){
			$this->aliases[]=$attrs["mailAlias"][$i];
			$this->HASH_ALL_MAILS[]=$attrs["mailAlias"][$i];
			}
			
			for($i=0;$i<$attrs["mozillaSecondEmail"]["count"];$i++){	
				$this->HASH_ALL_MAILS[]=$attrs["mozillaSecondEmail"][$i];
			}
			
		for($i=0;$i<$attrs["homeDirectoryBinded"]["count"];$i++){
			$this->homeDirectoryBinded[]=$attrs["homeDirectoryBinded"][$i];
			}
			
		for($i=0;$i<$attrs["RecipientToAdd"]["count"];$i++){
				$this->RecipientToAdd[]=$attrs["RecipientToAdd"][$i];
		}			
				
		for($i=0;$i<$attrs["amavisWhitelistSender"]["count"];$i++){$this->amavisWhitelistSender[$attrs["amavisWhitelistSender"][$i]]=$attrs["amavisWhitelistSender"][$i];}
		for($i=0;$i<$attrs["amavisBlacklistSender"]["count"];$i++){$this->amavisBlacklistSender[$attrs["amavisBlacklistSender"][$i]]=$attrs["amavisBlacklistSender"][$i];}

		if($this->MailboxSecurityParameters==null){
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."[mailbox]\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."l=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."r=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."s=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."w=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."i=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."p=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."c=0\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."d=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."a=0\n";
		}	
		
		if($this->MailBoxMaxSize==null){$this->MailBoxMaxSize=0;}
		if($this->AllowedSMTPTroughtInternet==null){$this->AllowedSMTPTroughtInternet=1;}
	
	//joomla
			if($this->objectClass_array["JoomlaUser"]==null){
			$cl=$this->objectClass_array;
			$upd=array();
			while (list ($num, $ligne) = each ($cl)){
				$upd["objectClass"][]=$ligne;
			}
			$upd["objectClass"][]="JoomlaUser";
			$upd["JoomlaGroup"][]="Registered";
			$upd["JoomlaBlockUser"][]="0";
			writelogs('Adding new artica class JoomlaUser..',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$ldap->Ldap_modify($this->dn,$upd);
		}	


	$this->MailingListAddressGroup=$this->MaillingListGroupGET();

		
	}
	
	function save_EnableBackupAccount(){
		$ldap=$this->ldapClass;
		$array=array();
		$array["EnableBackupAccount"][0]=$this->EnableBackupAccount;
		if($this->RsyncBackupTargetPath<>null){$array["RsyncBackupTargetPath"][0]=$this->RsyncBackupTargetPath;}
		if(!$ldap->Ldap_modify($this->dn,$array)){
			echo "\n".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
			return false;
		}
		
		$sock=new sockets();
		$sock->getfile("SynchronizeRsyncDaemon");
		
		
		
	}
	
	function edit_mailbox(){
		$ldap=$this->ldapClass;
		
		if($this->mail==null){return;}
		writelogs("editing mail has $this->mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$upd=array();
		$upd["mail"][0]=$this->mail;
		$upd["MailboxActive"][0]=$this->MailboxActive;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			echo "\n".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
			return false;
		}		
		$tpl=new templates();
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");}
		}
		
	function edit_system(){
		$upd=array();
		$ldap=$this->ldapClass;
		writelogs("editing system for $this->uid homeDirectory:$this->homeDirectory, uidNumber:$this->uidNumber",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($this->homeDirectory<>null){$upd["homeDirectory"][0]=$this->homeDirectory;}
		if($this->loginShell<>null){$upd["loginShell"][0]=$this->loginShell;}
		if($this->uidNumber<>null){$upd["uidNumber"][0]=$this->uidNumber;}
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			echo "\n".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
			return false;
		}		
		
		if($this->homeDirectory<>null){
			$this->UpdatePlugins();
		}
		return true;
	
	}
	
	function SaveZarafaMbxLang($lang){
		$upd=array();
		$ldap=new clladp();
		$upd["zarafaMbxLang"][0]=$lang;
		$ldap->Ldap_modify($this->dn,$upd);
	}
	
	function edit_safeBox(){
		$ldap=$this->ldapClass;
		$upd=array();
		$upd["CryptedHome"][0]=$this->CryptedHome;
		
		writelogs("$this->uid: CryptedHome:$this->CryptedHome",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if($this->CryptedHomePassword==null){
			$up["CryptedHomePassword"]=array();
			$ldap->Ldap_del_mod($this->dn,$up);
		}else{
			$upd["CryptedHomePassword"][0]=$this->CryptedHomePassword;
		}
		if($this->CryptedHomeSize<>null){
			$upd["CryptedHomeSize"]=$this->CryptedHomeSize;
		}
		
		
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			echo "\n".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
			return false;
		}		
		
		
	}
	
	
	
	function GetMailAlternateAddress(){
		$ldap=$this->ldapClass;
		$upd=array();
		$dn="cn=recipient_canonical_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["cn"][]="recipient_canonical_maps";
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostFixStructuralClass';
			$ldap->ldap_add($dn,$upd);
			}
		
		
		$dn="cn=$this->mail,cn=recipient_canonical_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			
			return null;
		}
		
		$sr =@ldap_search($ldap->ldap_connection,$dn,"(objectClass=*)");
		if(!$sr){
			writelogs("Unable to dn : $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (objectClass=*) -> aborting function search engine doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);	
		$this->MailAlternateAddress=$attrs["MailAlternateAddress"][0];
		return $attrs["MailAlternateAddress"][0];
		
	}
	
	function DeleteCanonical(){
		$array["SenderCanonical"]=$this->SenderCanonical;
		$ldap=$this->ldapClass;
		$ldap->Ldap_del_mod($this->dn,$array);
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");
		
	}
	
	private function MaillingListGroupGET(){
		$ldap=$this->ldapClass;
		$dn="cn=$this->mail,cn=aliases-mailing,ou=$this->ou,dc=organizations,$ldap->suffix";
		$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=MailingAliasesTable',array("MailingListAddressGroup"));
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			return $hash[0][strtolower("MailingListAddressGroup")][0];	
		}
		return 0;		
	}
	
	function MaillingListGroupEnable($value){
		$ldap=$this->ldapClass;
		$array["MailingListAddressGroup"][0]=$value;
		$dn="cn=$this->mail,cn=aliases-mailing,ou=$this->ou,dc=organizations,$ldap->suffix";
		
		
		if(!$ldap->Ldap_modify($dn,$array)){echo $ldap->ldap_last_error;return;}
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");}
		return true;
		
	}
	
	function add_Canonical(){
		$array["SenderCanonical"]=$this->SenderCanonical;
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_modify($this->dn,$array)){echo $ldap->ldap_last_error;return;}
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");}
	}
	
	function add_bcc($email){
		$sock=new sockets();
		$ldap=$this->ldapClass;
		$array=array();
		$array["RecipientToAdd"][0]=$email;
		if(!$ldap->Ldap_modify($this->dn,$array)){
				echo "RecipientToAdd:$ldap->ldap_last_error\n";
				return;
		}
			
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-bcc-tables=yes");}		
		return ;	
		

	}
	
	function SavePrivileges($content){
		$ldap=$this->ldapClass;
		$array=array();
		$array["ArticaGroupPrivileges"][0]=$content;
		if(!$ldap->Ldap_modify($this->dn,$array)){
			echo "SavePrivileges()\nError:$ldap->ldap_last_error\nclass:".__CLASS__."\nfile:".basename(__FILE__)."\nLine:".__LINE__;
			return;
		}		
		
	}
	
	function add_sender_bcc($email){
		$sock=new sockets();
		$ldap=$this->ldapClass;
		$array=array();
		$array["SenderBccMaps"][0]=$email;
		if(!$ldap->Ldap_modify($this->dn,$array)){
			echo "add_sender_bcc:$ldap->ldap_last_error\n";
			return;
		}
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-bcc-tables=yes");}
	}	
	
	function add_computer($macaddr,$uid){
		$dn="cn=hosts,$this->dn";
		$ldap=$this->ldapClass;
		$upd=array();
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"][]="hosts";
			if(!$ldap->ldap_add($dn,$upd)){
				echo "$ldap->ldap_last_error\nFile:".basename(__FILE__)."\nLine:".__LINE__;
				return;
			}
		}
		
		$dn="cn=$macaddr,cn=hosts,$this->dn";
		if(!$ldap->ExistsDN($dn)){
			$upd=array();
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="ComputerAfectation";
			$upd["cn"][]=$macaddr;
			$upd["ComputerMacAddress"][]=$macaddr;
			$upd["uid"][]=$uid;
			if(!$ldap->ldap_add($dn,$upd)){
				echo "$ldap->ldap_last_error\nFile:".basename(__FILE__)."\nLine:".__LINE__;
				return;
			}
		}
		
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?backuppc-affect=yes");}
		}
	
	function del_all_bcc(){
		if(!is_array($this->RecipientToAdd)){return;}
		reset($this->RecipientToAdd);
		$tpl=new templates();
		$ldap=$this->ldapClass;
		while (list ($num, $email) = each ($this->RecipientToAdd) ){
			$array["RecipientToAdd"]=$email;
			if(!$ldap->Ldap_del_mod($this->dn,$array)){
				echo $tpl->_ENGINE_parse_body("{failed} index:$num\nRecipientToAdd:$email\n$ldap->ldap_last_error\n");return;
				}
		}
		
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-bcc-tables=yes");
		
	}
	
	
	function del_bcc($num){
		$email=$this->RecipientToAdd[$num];
		$array["RecipientToAdd"]=$email;
		$ldap=$this->ldapClass;
		$tpl=new templates();
		if(!$ldap->Ldap_del_mod($this->dn,$array)){echo $tpl->_ENGINE_parse_body("{failed} index:$num\nRecipientToAdd:$email\n$ldap->ldap_last_error\n");}
		else{echo html_entity_decode($tpl->_ENGINE_parse_body("{success}\n{delete}:$email\n"));}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-bcc-tables=yes");
	}
	
	function add_transport($line){
		$dn=$this->dn;
		$line=str_replace('smtp:','',$line);
		$upd["AlternateSmtpRelay"]=$line;
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_add_mod($dn,$upd)){
			echo $ldap->ldap_last_error;
			return;
		}
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");}
	}
	
	function del_transport(){
		$dn=$this->dn;
		writelogs("Delete attribute AlternateSmtpRelay copy for \"$this->AlternateSmtpRelay\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$upd["AlternateSmtpRelay"]=$this->AlternateSmtpRelay;
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_del_mod($dn,$upd)){
			writelogs("Delete attribute AlternateSmtpRelay failed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			echo $ldap->ldap_last_error;
			return false;
		}	
		
		if($this->SenderCanonical<>null){
			$dn="cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";	
			if(!$ldap->ldap_delete($dn,true)){
				writelogs("Delete cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps failed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$dn="cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";	
				if(!$ldap->ldap_delete($dn,true)){
					writelogs("Delete cn=$this->uid,cn=sender_dependent_relayhost_maps failed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					echo $ldap->ldap_last_error;
					return false;
				}
				return false;
			}
		}else{
			$dn="cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ldap_delete($dn,true)){
				writelogs("Delete cn=$this->uid,cn=sender_dependent_relayhost_maps failed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				echo $ldap->ldap_last_error;
				return false;
			}			
		}
		writelogs("Success !",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");}
		return true;
	}
	
	
	function add_alias($mail){
		$dn=$this->dn;
		$ldap=$this->ldapClass;
		$mail=trim($mail);
		$updatearray=array();
		$updatearray["mailAlias"][]=$mail;
		if(!$ldap->Ldap_add_mod($dn,$updatearray)){echo $ldap->ldap_last_error;return;}
		$users=new usersMenus();
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");}
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}
		
	}
	
	
	function add_sharedfolder($path){
		$original_path=$path;
		$ldap=$this->ldapClass;
		if($this->dn==null){
			writelogs("Unable to find DN for \"$this->uid\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		
		$userdn="ou=$this->uid,ou=mounts,ou=$this->ou,dc=organizations,$ldap->suffix";
		
		
		$dn="cn=$this->homeDirectory/mounts,ou=auto.master,ou=mounts,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd=array();
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="automount";
			$upd["cn"][]="$this->homeDirectory/mounts";
			$upd["automountInformation"][]="ldap:$ldap->ldap_host:$userdn -g";
			if(!$ldap->ldap_add($dn,$upd)){
				echo "Error \nline: ".__LINE__.
				"\nFile: ".__FILE__.
				"\n: ".$ldap->ldap_last_error;
				return false;
				}
			}else{
				$upd["automountInformation"][]="ldap:$ldap->ldap_host:$userdn -g";
				if(!$ldap->Ldap_modify($dn,$upd)){
					echo "Error \nline: ".__LINE__.
						"\nFile: ".__FILE__.
						"\n: ".$ldap->ldap_last_error;
						return false;
				}
			}
			unset($upd);
			$dn="ou=mounts,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd=array();
				$upd["objectClass"][]="top";
				$upd["objectClass"][]="organizationalUnit";
				$upd["ou"][]="mounts";
				if(!$ldap->ldap_add($dn,$upd)){
					echo "Error \nline: ".__LINE__.
					"\nFile: ".__FILE__.
					"\n: ".$ldap->ldap_last_error;
					return false;
				}
				
			}
			unset($upd);
			$dn="ou=$this->uid,ou=mounts,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd=array();
				$upd["objectClass"][]="top";
				$upd["objectClass"][]="automountMap";
				$upd["ou"][]="$this->uid";
				if(!$ldap->ldap_add($dn,$upd)){
					echo "Error \nline: ".__LINE__.
					"\nFile: ".__FILE__.
					"\n: ".$ldap->ldap_last_error;
					return false;
				}
				
			}			

			$path=basename($path);
			unset($upd);
			$dn="cn=$path,ou=$this->uid,ou=mounts,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd=array();
				$upd["objectClass"][]="top";
				$upd["objectClass"][]="automount";
				$upd["cn"][]="$path";
				$upd["automountInformation"][]="-fstype=bind,rw  :$original_path";
				if(!$ldap->ldap_add($dn,$upd)){
					echo "Error \nline: ".__LINE__.
					"\nFile: ".__FILE__.
					"\n: ".$ldap->ldap_last_error;
					return false;
				}
				
			}			
			$sock=new sockets();
			$sock->getfile("chmodRWU:$original_path");
			return true;	
				
			
		}
	
	function add_homeDirectoryBinded($directory){
		$dn=$this->dn;
		$ldap=$this->ldapClass;	
		$updatearray=array();
		$updatearray["homeDirectoryBinded"][]=$directory;
		if(!$ldap->Ldap_add_mod($dn,$updatearray)){echo $ldap->ldap_last_error;return false;}
		$sock=new sockets();
		$datas=$sock->getfile("homeDirectoryBinded:$this->homeDirectory;$directory");
		if(trim($datas)<>null){echo $datas;}
	}
	
	function delete_homeDirectoryBinded($directory){
		$dn=$this->dn;
		$ldap=$this->ldapClass;	
		$updatearray=array();
		$updatearray["homeDirectoryBinded"]=$directory;
		if(!$ldap->Ldap_del_mod($dn,$updatearray)){echo $ldap->ldap_last_error;return false;}
		$sock=new sockets();
		$datas=$sock->getfile("homeDirectoryUBinded:$this->homeDirectory;$directory");
		if(trim($datas)<>null){echo $datas;}		
	}	
	
	function add_alias_fetchmail($mail){
		$dn=$this->dn;
		$ldap=$this->ldapClass;	
		$updatearray=array();
		$updatearray["FetchMailMatchAddresses"][]=$mail;
		if(!$ldap->Ldap_add_mod($dn,$updatearray)){echo $ldap->ldap_last_error;}
		$users=new usersMenus();
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");}
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}
		
		
	}
	
	function delete_alias($mail){
		$dn=$this->dn;
		$ldap=$this->ldapClass;	
		$updatearray=array();
		$updatearray["mailAlias"]=$mail;
		if(!$ldap->Ldap_del_mod($dn,$updatearray)){echo $ldap->ldap_last_error;return;}
		$users=new usersMenus();
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}
		
		
	}
	function del_alias_fetchmail($email){
		$dn=$this->dn;
		$ldap=$this->ldapClass;	
		$updatearray=array();
		$updatearray["FetchMailMatchAddresses"]=$email;
		if(!$ldap->Ldap_del_mod($dn,$updatearray)){echo 
		"Fetchmail alias:\"$email\"\n".
		$ldap->ldap_last_error;}	
	}
	
	
	function SaveMailAlternateAddress(){
		$ldap=$this->ldapClass;
		if($this->ou==null){if(preg_match("#ou=(.+?),dc=organizations#",$this->dn,$re)){$this->ou=$re[1];}}
		$dn="cn=$this->mail,cn=recipient_canonical_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		$upd=array();
		
		if($this->MailAlternateAddress<>null){
			if(!$ldap->ExistsDN($dn)){
				
				$upd["cn"][]=$this->mail;
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='RecipientCanonicalMaps';
				$upd["MailAlternateAddress"][]=$this->MailAlternateAddress;
				if(!$ldap->ldap_add($dn,$upd)){
					writelogs("Creating $dn failed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					return false;
				}
			}else{
				writelogs("Updating attribute MailAlternateAddress \"$this->MailAlternateAddress\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$upd["MailAlternateAddress"][]=$this->MailAlternateAddress;
				if(!$ldap->Ldap_modify($dn,$upd)){return false;}
			}
		}else{
			if($ldap->ExistsDN($dn)){
				writelogs("Deleting $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(!$ldap->ldap_delete($dn)){return false;}
			}
		}
		
		$users=new usersMenus();
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");}
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}		
		return true;
		
	}
	
	
	function AddAliasesMailing($email){
		$ldap=$this->ldapClass;
		$dn="cn=aliases-mailing,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
				$upd["cn"][]="aliases-mailing";
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='PostFixStructuralClass';
				if(!$ldap->ldap_add($dn,$upd)){return false;}
			
		}
		
		
		$dn="cn=$this->mail,cn=aliases-mailing,ou=$this->ou,dc=organizations,$ldap->suffix";
		
		writelogs("adding $email copy for $this->mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(!$ldap->ExistsDN($dn)){
				$upd["cn"][]=$this->mail;
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='MailingAliasesTable';
				$upd["MailingListAddress"][]=$email;
				if(!$ldap->ldap_add($dn,$upd)){return false;}
				return true;
		}		
		
		$upd["MailingListAddress"]=$email;
		if(!$ldap->Ldap_add_mod($dn,$upd)){return false;}
		$sock=new sockets();
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");	}
		$users=new usersMenus();
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}			
		return true;
		
	}
	
	function LoadAliasesMailing(){
		$ldap=$this->ldapClass;
		$res=array();
		$dn="cn=$this->mail,cn=aliases-mailing,ou=$this->ou,dc=organizations,$ldap->suffix";
		$ldap=$this->ldapClass;
		$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=MailingAliasesTable');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			for($i=0;$i<$hash[0]["mailinglistaddress"]["count"];$i++){
				$res[]=$hash[0]["mailinglistaddress"][$i];
			}
			
		}
		
	   return $res;
	}
	
	
	function MailingGroupsLoadAliases(){
		if($this->MailingListAddressGroup<>1){return array();}
		$groups=$this->Groups_list();
		$ldap=$this->ldapClass;
		if(!is_array($groups)){return array();}
		while (list ($num, $ligne) = each ($groups) ){
			$gp=new groups($num);
			if(!is_array($gp->members)){continue;}
			while (list ($index, $uid) = each ($gp->members) ){
				$email=$ldap->email_from_uid($uid);
				if($email==null){continue;}
				if($email==$this->mail){continue;}
				$array[]=$email;
			}
		}
		
		if(!is_array($array)){return array();}
		return $array;
		
	}
	
	
	function delete_AliasesMailing($mail){
		$ldap=$this->ldapClass;
		$res["MailingListAddress"]=$mail;
		$dn="cn=$this->mail,cn=aliases-mailing,ou=$this->ou,dc=organizations,$ldap->suffix";
		$ldap->Ldap_del_mod($dn,$res);
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-hash-tables=yes");
		$users=new usersMenus();
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}		
	}
	
	
	function find_ldap_items($users,$ou=null){
		$ldap=$this->ldapClass;
		$suffix=$ldap->suffix;
		if($ou<>null){
			$suffix="ou=$ou,dc=organizations,$ldap->suffix";
		}
		$users=$users."*";
		$users=str_replace('**','*',$users);
	
		
		$sr =@ldap_search($ldap->ldap_connection,$suffix,"(&(objectClass=userAccount)(|(uid=$users)(cn=$users)(displayName=$users)))",array("displayname","uid"));
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					$displayname=$result[$i]["displayname"][0];
					$uid=$result[$i]["uid"][0];
					if($displayname==null){$displayname=$uid;}
					$res[$uid]=$displayname;
				}
				
		}else{
			writelogs("Error for userAccount (".ldap_err2str(ldap_errno($ldap->ldap_connection)).")",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		
		$sr =@ldap_search($ldap->ldap_connection,$suffix,"(&(objectClass=posixGroup)(|(cn=$users)(displayName=$users)))",array("displayname","cn","gidNumber"));
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					$displayname=$result[$i]["displayname"][0];
					$gidNumber=$result[$i]["gidnumber"][0];
					$uid=$result[$i]["cn"][0];
					if($displayname==null){$displayname=$uid;}
					$res["@$uid"]="@$displayname:$gidNumber";
				}
				
		}else{
			writelogs("Error for posixGroup",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);		
		}
		writelogs("(&(objectClass=posixGroup)(uid=$users)) in $suffix ". count($res)." elements",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $res;
		
	}
		
		
	
	
	
	function find_samba_items($users,$ou=null){
		$ldap=$this->ldapClass;
		$suffix=$ldap->suffix;
		if($ou<>null){
			$suffix="ou=$ou,dc=organizations,$ldap->suffix";
		}
		$users=$users."*";
		$users=str_replace('**','*',$users);
	
		
		$sr =@ldap_search($ldap->ldap_connection,$suffix,"(&(objectClass=sambaSamAccount)(|(uid=$users)(cn=$users)(displayName=$users)))",array("displayname","uid"));
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					$displayname=$result[$i]["displayname"][0];
					$uid=$result[$i]["uid"][0];
					if($displayname==null){$displayname=$uid;}
					$res[$uid]=$displayname;
				}
				
		}else{
			writelogs("Error for sambaSamAccount (".ldap_err2str(ldap_errno($ldap->ldap_connection)).")",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		
		$sr =@ldap_search($ldap->ldap_connection,$suffix,"(&(objectClass=sambaGroupMapping)(|(cn=$users)(displayName=$users)))",array("displayname","cn"));
		if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				for($i=0;$i<$result["count"];$i++){
					$displayname=$result[$i]["displayname"][0];
					$uid=$result[$i]["cn"][0];
					if($displayname==null){$displayname=$uid;}
					$res["@$uid"]="@$displayname";
				}
				
		}else{
			writelogs("Error for sambaGroupMapping",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);		
		}
		writelogs("(&(objectClass=sambaSamAccount)(uid=$users)) in $suffix ". count($res)." elements",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		
		
		$query=$users;
		
		
	if($query==null){
			$sql="SELECT uid FROM getent_users ORDER BY uid LIMIT 0,50 ";
		}else{
			$query=str_replace("**", "*", $query);
			$query=str_replace("*", "%", $query);
			$sql="SELECT uid FROM getent_users WHERE uid LIKE '$query' ORDER BY uid LIMIT 0,50 ";
		}
		
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$uid=$ligne["uid"];
			$res[$uid]=$uid;
			
		}
		
		if($query==null){
			$sql="SELECT `group` FROM getent_groups ORDER BY `group` LIMIT 0,50 ";
		}else{
			$query=str_replace("**", "*", $query);
			$query=str_replace("*", "%", $query);
			$sql="SELECT `group` FROM getent_groups WHERE `group` LIKE '$query' ORDER BY `group` LIMIT 0,50 ";
		}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$uid=$ligne["group"];
			$res["@$uid"]="@$uid";
		}		
	
		writelogs("RETURN ARRAY OF  -> ". count($res)." elements",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $res;
		
	}
	
	function Samaba_delete_user(){
		$ldap=$this->ldapClass;
		$upd["objectClass"]="sambaSamAccount";
		if(!$ldap->Ldap_del_mod($this->dn,$upd)){return false;}
		$users=new usersMenus();
		$sock=new sockets();
		if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}		
		return true;		
		}
		
	function add_whitelist($mail){
		$ldap=$this->ldapClass;
		if($mail=="*@*"){return false;}
		if($mail=="*@*.*"){return false;}
		$array["amavisWhitelistSender"]=$mail;
		writelogs("adding $mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!$ldap->Ldap_add_mod($this->dn,$array)){
			$this->ldap_error=$ldap->ldap_last_error;
		}else{
			$this->del_blacklist($mail);
			$users=new usersMenus();
			$sock=new sockets();
			if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}			
			return true;
		}
	}
	
	function del_whitelist($mail){
		$ldap=$this->ldapClass;
		$array["amavisWhitelistSender"]=$mail;
		writelogs("Deleting $mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!$ldap->Ldap_del_mod($this->dn,$array)){
			$this->ldap_error=$ldap->ldap_last_error;
			echo $ldap->ldap_last_error;
		}else{
			$tpl=new templates();
			$users=new usersMenus();
			$sock=new sockets();
			if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}			
			return true;
		}
	}

	function del_blacklist($mail){
		$ldap=$this->ldapClass;
		$array["amavisBlacklistSender"]=$mail;
		writelogs("delete $mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(!$ldap->Ldap_del_mod($this->dn,$array)){
			$this->ldap_error=$ldap->ldap_last_error;
			echo $ldap->ldap_last_error;
		}else{
			$users=new usersMenus();
			$sock=new sockets();
			if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}			
			return true;
		}
		
		
	}	
	
	function add_blacklist($mail){
		if($mail=="*@*"){return false;}
		if($mail=="*@*.*"){return false;}	
		$ldap=$this->ldapClass;
		$uid=$ldap->uid_from_email($mail);
		if(strlen(trim($uid))>0){return false;}
		$array["amavisBlacklistSender"]=$mail;
		writelogs("adding $mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(!$ldap->Ldap_add_mod($this->dn,$array)){
			$this->ldap_error=$ldap->ldap_last_error;
		}else{
			$this->del_whitelist($mail);
			$users=new usersMenus();
			$sock=new sockets();
			if($users->ARTICA_META_ENABLED){$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");}			
			return true;
		}
		
		
	}
	
	
	function SenderCanonicalSMTPRelay(){
			$ou=$this->ou;
			$ldap=$this->ldapClass;			
			$dn="cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";

			$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=SenderDependentSaslInfos');
			if ($sr) {
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				$host=$hash[0]["sendercanonicalrelayhost"][0];
				$SenderCanonicalRelayName=$hash[0][strtolower("SenderCanonicalRelayName")][0];
			}
		
		if($host==null){return array();}
		$dn="cn=$SenderCanonicalRelayName,cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";	
		$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=SenderDependentSaslInfos');
		if ($sr) {
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				$AUTH=$hash[0]["sendercanonicalrelaypassword"][0];
			}
			

		writelogs("$this->uid: HOST=>$host,AUTH=>$AUTH",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		return array("HOST"=>$host,"AUTH"=>$AUTH);
		
		
		
		
		
	}
	
	
	private function BuildSenderCanonicalBranch($smtppattern,$SenderCanonicalRelayName){
		$upd=array();
		$ou=$this->ou;
		$ldap=$this->ldapClass;
		$dn="cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectclass"][0]="top";			
			$upd["objectclass"][0]="PostFixStructuralClass";
			$upd["cn"][0]="sender_dependent_relayhost_maps";
				
			if(!$ldap->ldap_add($dn,$upd)){
				writelogs("Error $dn: $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				echo "$dn\n$ldap->ldap_last_error\n".basename(__FILE__)."\nLine:".__LINE__;
				exit;
			}
			unset($upd);			
		}
		
		$dn="cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectclass"][0]="top";			
			$upd["objectclass"][0]="PostFixStructuralClass";
			$upd["cn"][0]="$this->uid";
				
			if(!$ldap->ldap_add($dn,$upd)){
				writelogs("Error $dn: $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				echo "$dn\n$ldap->ldap_last_error\n".basename(__FILE__)."\nLine:".__LINE__;
				exit;
			}
			unset($upd);			
		}

	$dn="cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectclass"][0]="top";			
			$upd["objectclass"][0]="SenderDependentSaslInfos";
			$upd["SenderCanonicalRelayName"][0]="$SenderCanonicalRelayName";
			$upd["SenderCanonicalRelayHost"][0]=$smtppattern;
			$upd["cn"][0]="$this->SenderCanonical";
				
			if(!$ldap->ldap_add($dn,$upd)){
				writelogs("Error $dn: $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				echo "$dn\n$ldap->ldap_last_error\n".basename(__FILE__)."\nLine:".__LINE__;
				exit;
			}
			unset($upd);			
		}		
		
		
	}
	
	function SenderCanoniCalSMTPRelayAdd($smtppattern,$username,$password,$SenderCanonicalRelayName=null){
		$ou=$this->ou;
		$ldap=$this->ldapClass;
		$smtppattern=str_replace("smtp:","",$smtppattern);
		writelogs("Adding $smtppattern (user:$username;pass=****),SenderCanonicalRelayName=$SenderCanonicalRelayName",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		$this->BuildSenderCanonicalBranch($smtppattern,$SenderCanonicalRelayName);
		$dn="cn=$SenderCanonicalRelayName,cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";	
		$smtppattern=str_replace("smtp:","",$smtppattern);
		$credentials="$username:$password";
		
		if(!$ldap->ExistsDN($dn)){
			$upd=array();
			$upd["objectclass"][0]="top";			
			$upd["objectclass"][0]="SenderDependentSaslInfos";
			$upd["cn"][0]=$SenderCanonicalRelayName;
			$upd["SenderCanonicalRelayPassword"][0]="$username:$password";
			
							
			if(!$ldap->ldap_add($dn,$upd)){
				writelogs("Error $dn: $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				echo "$ldap->ldap_last_error\n".basename(__FILE__)."\nLine:".__LINE__;
			}
			
		}else{
			writelogs("Update SenderCanonicalRelayHost=$smtppattern",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd["SenderCanonicalRelayHost"][0]=$smtppattern;
			if($credentials<>":"){
				$upd["SenderCanonicalRelayPassword"][0]="$username:$password";
			}
			
			if(!$ldap->Ldap_modify($dn,$upd)){
				if($ldap->ldap_last_error_num<>64){
				echo "$ldap->ldap_last_error\n".basename(__FILE__)."\nLine:".__LINE__;
				}
			}			
		}
		
		
		$dn="cn=$this->SenderCanonical,cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
		if($ldap->ExistsDN($dn)){
			writelogs("Update SenderCanonicalRelayHost=$smtppattern",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd["SenderCanonicalRelayHost"][0]=$smtppattern;
			if(!$ldap->Ldap_modify($dn,$upd)){
			if($ldap->ldap_last_error_num<>64){
				echo "ERROR number: $ldap->ldap_last_error_num\n
				DN:$dn\n
				$ldap->ldap_last_error\n".basename(__FILE__)."\nLine:".__LINE__;
				}
			}				
		}
	
		writelogs("Done....",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	}
	
	
	function obm(){
		$users=new usersMenus();
		if(!$users->OBM2_INSTALLED){return null;}
		$sock=new sockets();
		$sock->getfile("obm2:$this->uid");
		
		
	}
	
	function opengoo(){
		$users=new usersMenus();
		if(!$users->OPENGOO_INSTALLED){return null;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?opengoouid=$this->uid");
		}	
		
	private function GroupOffice(){
		$users=new usersMenus();
		if(!$users->GROUPOFFICE_INSTALLED){return null;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?GroupOfficeUid=$this->uid");
	}
	
	
function joomla(){
	$users=new usersMenus();
	if(!$users->JOOMLA_INSTALLED){
		writelogs("Joomla is not installed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	$q=new mysql();
	$database="joomla_$this->ou";
	if(!$q->DATABASE_EXISTS($database)){
		writelogs("database \"$database\" does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$groupmap["Public Frontend"]=29; 	
	$groupmap["Registered"]=18; 
	$groupmap["Author"]=19;	
	$groupmap["Editor"]=20;	
	$groupmap["Publisher"]=21;	
	$groupmap["Public Backend"]=30;
	$groupmap["Manager"]=23;
	$groupmap["Administrator"]=24;
	$groupmap["Super Administrator"]=25;	
	$database="joomla_$this->ou";
	
	$group=$groupmap[$this->JoomlaGroup];
	$sql="SELECT id FROM jos_users WHERE username=\"$this->uid\"";
	
	$date=date('Y-m-d H:i:s');
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$database));
    if(trim($ligne["id"])==null){
    	$sql="INSERT INTO jos_users (name,username,email,usertype,block,gid,registerDate)
    	VALUES('$this->DisplayName','$this->uid','$this->mail','$this->JoomlaGroup','0','$group','$date')";
    	$q->QUERY_SQL($sql,$database);
    	$sql="SELECT id FROM jos_users WHERE username=\"$this->uid\"";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$database));    	
    	
    }else{
    	$sql="UPDATE jos_users SET email='$this->mail', usertype='$this->JoomlaGroup',gid='$group' WHERE id='{$ligne["id"]}'";
    	$q->QUERY_SQL($sql,$database);

    }
	$id=$ligne["id"];
	if(trim($ligne["id"])==null){
		writelogs("Error while set $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$sql="SELECT name FROM jos_core_acl_aro WHERE value=$id";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$database));  
	if(trim($ligne["name"])==null){
		$sql="INSERT INTO jos_core_acl_aro (section_value,value,order_value,name,hidden) 
		VALUES('users','$id','0','$this->DisplayName',0)";
		$q->QUERY_SQL($sql,$database);
	}
	
	
	
	
	
}


	function Samba_edit_LogonScript($scriptname){
		$upd=array();
		$upd["sambaLogonScript"][0]=$scriptname;
		$ldap=$this->ldapClass;
		if($GLOBALS["VERBOSE"]){echo "DEBUG:: $scriptname -> Ldap_modify($this->dn)\n";}
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			writelogs("ERRROR for $this->uid $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			echo $ldap->ldap_last_error."\n";return null;
			}
		
	}
			
			
	function Samba_edit_user(){
		$users=new usersMenus();
		if(!$this->SAMBA_ENABLED){return;}
		
		$ldap=$this->ldapClass;
		include_once(dirname(__FILE__).'/class.samba.inc');
		
		$smb=new samba();
		$SAMBA_HOSTNAME=$smb->main_array["global"]["netbios name"];
		
		$addclass=false;
			$user=new usersMenus();
			$this->uidNumberCheck();
			$this->SambaLoadpasswd();
			$this->sambaSID=$ldap->LOCAL_SID() .'-' . $this->SambaGetSid();
			
			if($this->loginShell==null){$this->loginShell="/bin/false";}
			if($this->gecos==null){$this->gecos="user";}
			writelogs("sambaSID=\"$this->sambaSID\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
			if($this->sambaSID==null){return false;}
		
			if($this->objectClass_array["sambaSamAccount"]==null){
				$uy=new usersMenus();
				$upd["sambaSID"][0]=$ldap->LOCAL_SID().'-'. (2 * $this->uidNumber * 1000);
				writelogs("Adding new class 'sambaSamAccount' SID={$upd["sambaSID"][0]} for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$upd["objectClass"]="sambaSamAccount";
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
				unset($upd);
			}
			
			if($this->objectClass_array["person"]==null){
				$upd["objectClass"]="person";
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
				unset($upd);
				writelogs("Adding new class 'person' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);					
			}

			if($this->objectClass_array["organizationalPerson"]==null){
				$upd["objectClass"]="organizationalPerson";
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
				unset($upd);
				writelogs("Adding new class 'organizationalPerson' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
			}

			if($this->objectClass_array["inetOrgPerson"]==null){
				$upd["objectClass"]="inetOrgPerson";
				if($this->attributs_array["sn"]==null){$upd["sn"]=$this->sn;}
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
				unset($upd);
				writelogs("Adding new class 'inetOrgPerson' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
			}
			
			
			$groups_list=$this->GetGroups($this->uid);
			if($groups_list[0]==null){$groups_list[0]=0;}
			if($this->group_id==null){$this->group_id=$groups_list[0];}
			if($this->accountGroup==null){$this->accountGroup=$this->group_id;}
			if(!is_numeric($this->accountGroup)){$this->accountGroup=$this->group_id;}
			
					
			
			if($this->objectClass_array["posixAccount"]==null){
				$upd["objectClass"]="posixAccount";
				if($this->attributs_array["loginShell"]==null){$upd["loginShell"]=$this->loginShell;}
				if($this->attributs_array["gecos"]==null){$upd["gecos"]=$this->gecos;}
				if($this->attributs_array["gidNumber"]==null){$upd["gidNumber"]=$this->accountGroup;}
				if($this->attributs_array["uidNumber"]==null){$upd["uidNumber"]=$this->uidNumber;}
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
				unset($upd);
				writelogs("Adding new class 'posixAccount' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				
			}						

			if($this->objectClass_array["shadowAccount"]==null){
				writelogs("Adding new class 'shadowAccount' for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$upd["objectClass"]="shadowAccount";
				$upd["shadowMax"]=3650;
				$upd["shadowLastChange"]=round(time()/86400);
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return null;}
				unset($upd);
				
			}
			
			writelogs("Editing user...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			

			
			if($this->sambaPrimaryGroupGID==null){$this->sambaPrimaryGroupGID=513;}
			if($this->sambaLogonTime==null){$this->sambaLogonTime=0;}
			if($this->sambaLogoffTime==null){$this->sambaLogoffTime=2147483647;}
			if($this->sambaKickoffTime==null){$this->sambaKickoffTime=2147483647;}
			if($this->sambaPwdCanChange==null){$this->sambaPwdCanChange=1286597349;}
			if($this->sambaPwdMustChange==null){$this->sambaPwdMustChange=2147483647;}
			if($this->sambaAcctFlags==null){$this->sambaAcctFlags="[UX         ]";}
			if(trim($this->loginShell)==null){$this->loginShell="/bin/false";}
			if($this->gecos==null){$this->gecos="User in domain";}
			if($this->SambaAdminServerDefined==null){
				$SAMBA_IP=gethostbyname($SAMBA_HOSTNAME);
				
			}else{
				$SAMBA_IP=$this->SambaAdminServerDefined;
			}
			
			if(trim($SAMBA_IP)==null){$SAMBA_IP=$SAMBA_HOSTNAME;}
			if(trim($SAMBA_IP)=="127.0.0.1"){$SAMBA_IP=$SAMBA_HOSTNAME;}
			if(trim($SAMBA_IP)=="127.0.1.1"){$SAMBA_IP=$SAMBA_HOSTNAME;}
			$this->sambaHomePath='\\\\' .$SAMBA_IP. '\\' . $this->uid;
			$this->sambaHomeDrive="Z:";
			$this->sambaProfilePath='\\\\' .$SAMBA_IP. '\\profile\\' . $this->uid;
			writelogs("sambaPrimaryGroupGID.....: $this->sambaPrimaryGroupGID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);

			
			$group=new groups();
			$uy=new usersMenus();
			
			$upd["sambaLogonTime"][0]=$this->sambaLogonTime;
			$upd["sambaLogoffTime"][0]=$this->sambaLogoffTime;
			$upd["sambaKickoffTime"][0]=$this->sambaKickoffTime;
			$upd["sambaPwdCanChange"][0]=$this->sambaPwdCanChange;
			
			$upd["sambaHomePath"][0]=$this->sambaHomePath;
			$upd["sambaHomeDrive"][0]=$this->sambaHomeDrive;
			$sock=new sockets();
			if($sock->GET_INFO('SambaRoamingEnabled')==1){
				$upd["sambaProfilePath"][0]=$this->sambaProfilePath;
			}

			if($this->sambaLMPassword==null){$this->SambaLoadpasswd();}
			
			$upd["sambaAcctFlags"][0]=$this->sambaAcctFlags;
			$upd["sambaLMPassword"][0]=$this->sambaLMPassword;
			$upd["sambaNTPassword"][0]=$this->sambaNTPassword;
			$upd["sambaSID"][0]=$ldap->LOCAL_SID().'-'. (2 * $this->uidNumber * 1000);
			
			
			$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid($this->sambaPrimaryGroupGID);
			if($sambaPrimaryGroupSID==null){$sambaPrimaryGroupSID=$this->sambaPrimaryGroupGID;}
			
			$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
			writelogs("sambaPrimaryGroupSID={$upd["sambaPrimaryGroupSID"][0]} ($this->sambaPrimaryGroupGID)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			
			
			$upd["sambaPwdLastSet"][0]=time();
			$upd["sambaPwdMustChange"][0]=2147483647;
			$upd["sambaBadPasswordCount"][0]=0;
			$upd["sambaBadPasswordTime"][0]=0;
			$upd["sambaLogonHours"][0]=-1;
			
		
			//posixAccount
			$upd["loginShell"][0]=$this->loginShell;
			$upd["gecos"][0]=$this->gecos;
			$upd["gidNumber"][0]=$this->accountGroup;
			
			
			//shadowAccount
			$upd["shadowMax"][0]=999999;
			$upd["shadowLastChange"][0]=round(time()/86400);
			$upd["shadowMin"][0]=0;
			$upd["shadowWarning"][0]=7;			
			$upd["shadowInactive"][0]=-1;				
			$upd["shadowExpire"][0]=-1;					
			$upd["shadowFlag"][0]=0;					

			
			//inetOrgPerson
			$upd["sn"][0]=$this->sn;
			
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?samba-build-homes=yes");
			$sock->getFrameWork("cmd.php?smb-logon-scripts=yes");
			
			if(!$ldap->Ldap_modify($this->dn,$upd)){
					echo $ldap->ldap_last_error;
				
				}else{
				$tpl=new templates();
				$this->Rebuild_groups();
				
				
			}
	}
	
	
	function SambaLoadpasswd(){
		$sock=new sockets();
		$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=".base64_encode($this->password))));
		writelogs("$datas",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		
		if(preg_match('#([0-9A-Z]+):([0-9A-Z]+)#',$datas,$re)){
			$this->sambaLMPassword=$re[1];
			$this->sambaNTPassword=$re[2];
		}
	}
	
	function delivery_users($and=null){
			$mails=$_SESSION["ALL_MAILS"];
			if(!isset($_SESSION["ALL_MAILS"])){$user=new user($_SESSION["uid"]);$_SESSION["ALL_MAILS"]=$user->HASH_ALL_MAILS;}	
			$mails=$_SESSION["ALL_MAILS"];
			writelogs("eMail number for {$_SESSION["uid"]}: ".count($mails),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			
			if(count($mails)==1){return "delivery_user='{$mails[0]}' $and";}
			$delivery_user="delivery_user='{$mails[0]}' $and";
			unset($mails[0]);
			while (list ($num, $ligne) = each ($mails)){
				$num=$num;
				$delivery_user=$delivery_user." OR delivery_user='$ligne' $and";
			}
		return $delivery_user;		
	}	
	
	
	
	
	function uidNumberCheck($nochk=0){
			$uidNumber=$this->uidNumber;
			
		
			if($this->uidNumber==null){
				$this->uidNumber=$this->LastUidNumber();
				return $this->uidNumber;
			}
		
			if($this->uidNumber<2000){
				$this->uidNumber=$this->LastUidNumber();
				return $this->uidNumber;
			}
			$CountUidNumbers=$this->CountUidNumbers();
			if($CountUidNumbers>1){
				$this->uidNumber=$this->LastUidNumber();
				return $this->uidNumber;
			}
			
			return $this->uidNumber;
			
	}
	
	
	function LastUidNumber(){
		$ldap=new clladp();
		writelogs("Search the last uid number",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
      	$r =@ldap_search($ldap->ldap_connection, $ldap->suffix, '(uidnumber=*)',array());
      		if ($r){
         			ldap_sort($ldap->ldap_connection, $r, "uidnumber");
            		$result = ldap_get_entries($ldap->ldap_connection, $r);
         			$count = $result['count'];
         			$biguid = $result[$count-1]['uidnumber'][0];
         			writelogs("biguid=$biguid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
         			$biguid=$biguid+1;
      		}else{
      			$biguid=2000;
      		}
      		if($biguid<2000){$biguid=2000;}
      		return $biguid;
      		
	}
	
	function CountUidNumbers(){
			$ldap=new clladp();
			$res=0;
			
      		$r =@ldap_search($ldap->ldap_connection, $ldap->suffix, "(uidnumber=$this->uidNumber)",array("uidnumber"));
      		if ($r){
      			$result = ldap_get_entries($ldap->ldap_connection, $r);
      			$res=$result["count"];
      		}
      		return $res;
	}
	
	
	function SambaGetSid(){
		return 2 *$this->uidNumber+1000;
	}
	
	
	function SaveUser(){
		return $this->add_user();
	}
	
	
	function SaveWebDav(){
		$ldap=$this->ldapClass;
		$upd["WebDavUser"][0]=$this->WebDavUser;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			echo $ldap->ldap_last_error;	
			return;
		}

		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-apache-groupware=yes");
		
	}
	
function FTPSettingsEdit(){
	$ldap=$this->ldapClass;
	$upd=array();
	$upd["FTPDownloadBandwidth"][0]=$this->FTPDownloadBandwidth;
	$upd["FTPDownloadRatio"][0]=$this->FTPDownloadRatio;
	$upd["FTPQuotaFiles"][0]=$this->FTPQuotaFiles;
	$upd["FTPQuotaMBytes"][0]=$this->FTPQuotaMBytes;
	$upd["FTPStatus"][0]=$this->FTPStatus;
	$upd["FTPUploadBandwidth"][0]=$this->FTPUploadBandwidth;
	$upd["FTPUploadRatio"][0]=$this->FTPUploadRatio;
	if($this->homeDirectory<>null){
		$upd["homeDirectory"][0]=$this->homeDirectory;
	}
	
	if(!$ldap->Ldap_modify($this->dn,$upd)){
		echo $ldap->ldap_last_error;	
		return;
	}
	$sock=new sockets();
	if(!$GLOBALS["NO_COMPILE_POSTFIX"]){$sock->getFrameWork("cmd.php?pure-ftpd-users=yes");
	$datas=trim(base64_decode($sock->getFrameWork("cmd.php?chown=".base64_encode($this->homeDirectory)."&uid=".base64_encode($this->uid))));
	}
	if(strlen($datas)>0){echo $datas;}
}
	
	
	
	function zarafaSaveInfos(){
		$upd=array();
		$upd["zarafaQuotaHard"][0]=$this->zarafaQuotaHard;
		$upd["zarafaQuotaSoft"][0]=$this->zarafaQuotaSoft;
		$upd["zarafaQuotaWarn"][0]=$this->zarafaQuotaWarn;
		$upd["zarafaAdmin"][0]=$this->zarafaAdmin;
		
		
		IF(($this->zarafaQuotaHard+$this->zarafaQuotaSoft+$this->zarafaQuotaWarn)>0){
			$upd["zarafaQuotaOverride"][0]=1;
		}else{
			$upd["zarafaQuotaOverride"][0]=0;
		}
		
		
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			writelogs("$ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->error=__FUNCTION__.":\n".$ldap->ldap_last_error."\nin class ".__CLASS__;
			return false;
		}

		return true;
		
	}
	
	
	public function SaveVacationInfos(){
		$upd=array();
		$upd["vacationEnd"][0]=$this->vacationEnd;
		$upd["vacationStart"][0]=$this->vacationStart;
		$upd["vacationActive"][0]=$this->vacationActive;
		$upd["vacationInfo"][0]=$this->vacationInfo;
		
		writelogs("START: {$upd["vacationStart"][0]} END: {$upd["vacationEnd"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			writelogs("$ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->error=$ldap->ldap_last_error;
		}
	}
	
	public function AllowedSMTPTroughtInternet_edit(){
		$ldap=new clladp();
		$upd=array();
		$upd["AllowedSMTPTroughtInternet"][0]=$this->AllowedSMTPTroughtInternet;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			$this->error="Class: " .__CLASS__."\nFunction: ".__FUNCTION__."\nLine: ".__LINE__."\nLdap_modify: ".$ldap->ldap_last_error;
			return false;
		}
		return true;
	}
	
	
	function add_user(){
			$ldap=$this->ldapClass;
			$this->GetDefaults();
			$users=new usersMenus();
			include_once(dirname(__FILE__)."/class.artica.inc");
			$artica=new artica_general();
			if(!is_numeric(trim($this->group_id))){$this->group_id=0;}
			
			writelogs("Adding/modify user with group=$this->group_id, ou=$this->ou",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			
			$this->GetDefaults();
			if(!$this->DisableAccountLessThan4Caracters){
				if(strlen($this->uid)<4){
					writelogs("Adding $this->uid length<4 abort...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$tpl=new templates();	
					$this->ldap_error=$tpl->_ENGINE_parse_body("Error {logon} : $this->uid < 4 {size}");
					return false;
				}}
			
			if(strtolower(trim($this->uid))=="administrator"){
				writelogs("Adding $this->uid reserved administrator...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$tpl=new templates();	
				$this->ldap_error=$tpl->_ENGINE_parse_body("Error {logon} : $this->uid > {reserved}");
				return false;				
				
			}
			
			$group=new groups($this->group_id);
			
			if($this->ou==null){if($_GET["ou"]<>null){$this->ou=$_GET["ou"];}}
			
			if($this->ou==null){
				if(preg_match('#ou=(.+?),#',$group->dn,$res)){$this->ou=$res[1];}
			}
			writelogs("*** Create $this->uid in organization $this->ou ***",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			
			
			if(trim($this->ou)==null){
				writelogs("warning unable to get Organization name, create new one based on eMail address",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->ou=$this->domainname;
				$ldap->AddOrganization($this->ou);
			}
			
			
		if($group->ou<>$this->ou){
			writelogs("This group is not in this organization, return 0 for this group",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->group_id=0;
			}			
			
		
		if(!is_numeric($this->group_id)){
			writelogs("$this->uid has no real group, add 0 by default..."); 
			$this->group_id=0;
			}
		
				
		if($this->group_id==0){
				$group=new groups();
				if(!$group->add_new_group($this->ou.'_group',$this->ou)){
					$this->ldap_error="Adding default group:{$this->ou}_group  ".$group->ldap_error;
					return false;
				}
				$this->group_id=$group->GroupIDFromName($this->ou,$this->ou.'_group');
				writelogs("Adding default group \"{$this->ou}_group\":$this->group_id",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if($this->group_id==null){
					writelogs("warning unable to get group id",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->ldap_error="warning unable to get group id:".$group->ldap_error;
					return false;
				}
			}			
			
			if($this->mail==null){
				writelogs("No email specified, change to $this->uid@localhost.localdomain",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->mail=trim(strtolower($this->uid))."@localhost.localdomain";
			}
			
			if(trim($this->DisplayName)==null){$this->DisplayName=$this->uid;}
			if($this->givenName==null){$this->givenName=$this->DisplayName;}
			if($this->sn==null){$this->sn=$this->uid;}
			if($this->postalAddress==null){$this->postalAddress="none";}
			if($this->postalCode==null){$this->postalCode="0000";}
			if($this->town==null){$this->town="unknown";}
			if($this->street==null){$this->street="unknown";}
			if($this->mobile==null){$this->mobile="00.00.00.00.00";}
			if($this->telephoneNumber==null){$this->telephoneNumber="00.00.00.00.00";}
			if($this->MailboxActive==null){$this->MailboxActive='FALSE';}
			if($this->MailBoxMaxSize==null){$this->MailBoxMaxSize=0;}
			if($this->uidNumber==null){$this->uidNumber=$this->uidNumberCheck();}
			if($this->FinalDateToLive==null){$this->FinalDateToLive=0;}
			if($this->DotClearUserEnabled==null){$this->DotClearUserEnabled=0;}
			if($this->EnableUserSpamLearning==null){$this->EnableUserSpamLearning=0;}
			
			
			if($artica->EnableVirtualDomainsInMailBoxes==1){
				writelogs("Verify if $this->uid has email address",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(strpos($this->uid,'@')==0){
					writelogs("change $this->uid to $this->mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->uid=trim(strtolower($this->mail));
					$this->cn=trim(strtolower($this->mail));
				}
				
			}
			
			if($this->cn==null){$this->cn=$this->DisplayName;}
			$this->dn="cn=$this->cn,ou=users,ou=$this->ou,dc=organizations,$ldap->suffix";
			//$this->dn=$this->stripAccents($this->dn);
			
			if($this->MailboxSecurityParameters==null){
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."[mailbox]\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."l=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."r=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."s=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."w=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."i=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."p=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."c=0\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."d=1\n";
				$this->MailboxSecurityParameters=$this->MailboxSecurityParameters."a=0\n";
				}
			
			
			
			if($this->ArticaInterfaceLogon==null){
				$this->ArticaInterfaceLogon=$this->uid;
				$this->ArticaInterfaceLogon=str_replace('.','',$this->ArticaInterfaceLogon);
				$this->ArticaInterfaceLogon=str_replace('-','',$this->ArticaInterfaceLogon);
				$this->ArticaInterfaceLogon=str_replace('_','',$this->ArticaInterfaceLogon);
			}
			
			$this->SaveMailAlternateAddress();
			$upd=array();
			writelogs("Editing userid:($this->uid) $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			writelogs("This user has primary group $this->group_id",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			unset($upd);
			if(!$ldap->ExistsDN($this->dn)){
					$upd["objectClass"][]="top";
					$upd["objectClass"][]="person";
					$upd["objectClass"][]="organizationalPerson";
					$upd["objectClass"][]="inetOrgPerson";
					$upd["objectClass"][]="mozillaOrgPerson";					
					$upd["objectClass"][]="userAccount";
					$upd["objectClass"][]="posixAccount";
					$upd["objectClass"][]="ArticaSettings";					
					$upd["objectClass"][]="UserArticaClass";
					$upd["objectClass"][]="Vacation";
					$upd["objectClass"][]="PureFTPdUser";
					$upd["objectClass"][]="JoomlaUser";
			}
			
			
			if(strlen($this->jpegPhoto_datas)>0){$upd["jpegPhoto"][0]=$this->jpegPhoto_datas;}
					$upd["cn"][0]=$this->cn;	
					$upd["userid"][0]=trim(strtolower($this->uid));
					$upd["accountActive"][0]="TRUE";
					$upd["accountGroup"][0]=$this->group_id;
					
				//posixAccount	
					$upd["gidNumber"][0]=$this->group_id;				
					$upd["uidNumber"][0]=$this->uidNumber;
					$upd["loginShell"][0]=$this->loginShell;
					$upd["gecos"][0]=$this->gecos;
					
					
				//Vacation
					writelogs("vacationActive=\"$this->vacationActive\"..",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					if($this->vacationActive==null){$this->vacationActive="FALSE";}
					if($this->vacationStart==null){$this->vacationStart=time();}
					if($this->vacationEnd==null){$this->vacationEnd=time()+172800;}
					if($this->vacationInfo==null){$this->vacationInfo="\nI am away from the office and to not have access to my email.\nI will respond after I return.\n\nThanks.";}
					if($this->vacationEnabled==null){$this->vacationEnabled="FALSE";}
					
					
					$upd["vacationActive"][0]=$this->vacationActive;
					$upd["vacationEnd"][0]=$this->vacationEnd;
					$upd["vacationStart"][0]=$this->vacationStart;	
					$upd["vacationInfo"][0]=$this->vacationInfo;
					$upd["vacationEnabled"][0]=$this->vacationEnabled;

				//PureFTPD
					$upd["FTPDownloadBandwidth"][0]=$this->FTPDownloadBandwidth;
					$upd["FTPDownloadRatio"][0]=$this->FTPDownloadRatio;
					$upd["FTPQuotaFiles"][0]=$this->FTPQuotaFiles;
					$upd["FTPQuotaMBytes"][0]=$this->FTPQuotaMBytes;
					$upd["FTPStatus"][0]=$this->FTPStatus;
					$upd["FTPUploadBandwidth"][0]=$this->FTPUploadBandwidth;
					$upd["FTPUploadRatio"][0]=$this->FTPUploadRatio;						
					
				//Joomla
					if($this->JoomlaGroup==null){
						writelogs("JoomlaGroup is null, decide to turn to \"Registered\" value",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						$this->JoomlaGroup="Registered";}
					writelogs("JoomlaGroup=$this->JoomlaGroup",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$upd["JoomlaGroup"][0]=$this->JoomlaGroup;
					$upd["JoomlaBlockUser"][0]="0";
					
					$upd["domainName"][0]=$this->domainname;
					$upd["homeDirectory"][0]="/home/$this->uid";
					
					//infos
					$upd["DisplayName"][0]=$this->DisplayName;
					writelogs("DisplayName={$upd["DisplayName"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					writelogs("givenName={$this->givenName}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					writelogs("sn={$this->sn}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					writelogs("postalAddress={$this->postalAddress}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$upd["postalCode"][0]=$this->postalCode;
					$upd["postalAddress"][0]=$this->postalAddress;
					$upd["street"][0]=$this->street;
					$upd["givenName"][0]=$this->givenName;
					$upd["sn"][0]=$this->sn;
					$upd["mobile"][0]=$this->mobile;
					$upd["telephoneNumber"][0]=$this->telephoneNumber;
					$upd["mailDir"][0]="/home/$this->uid/mail";
					$upd["l"][0]=$this->town;
					$upd["userPassword"][0]=$this->password;
					if($this->mail<>null){$upd["mail"][0]=$this->mail;}
					$upd["ArticaInterfaceLogon"][0]=$this->ArticaInterfaceLogon;
					$upd["MailboxActive"][0]=$this->MailboxActive;
					$upd["MailboxSecurityParameters"][0]=$this->MailboxSecurityParameters;
					$upd["MailBoxMaxSize"][0]=$this->MailBoxMaxSize;
					
					$upd["FinalDateToLive"][0]=$this->FinalDateToLive;
					$upd["DotClearUserEnabled"][0]=$this->DotClearUserEnabled;
					$usersMenus=new usersMenus();
					$upd["PostFixUserTransport"][0]="lmtp:unix:$usersMenus->cyrus_lmtp_path";
					$upd["EnableUserSpamLearning"][0]=$this->EnableUserSpamLearning;
					
					
					if($this->SenderCanonical<>null){$upd["SenderCanonical"][0]=$this->SenderCanonical;}
				
				if(!$ldap->ExistsDN($this->dn)){
					writelogs("This user does not exists, adding $this->dn...",__FUNCTION__,__FILE__,__LINE__);
					writelogs("Creating a new uid number",__FUNCTION__,__FILE__,__LINE__);
					$this->uidNumber=$this->uidNumberCheck(1);
					$upd["uidNumber"][0]=$this->uidNumber;
					writelogs("ADD:: $this->uid:: DisplayName='$this->DisplayName' uidNumber=$this->uidNumber",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					
					
					$dnOu="ou=users,ou=$this->ou,dc=organizations,$ldap->suffix";
					if(!$ldap->ExistsDN($dnOu)){
						writelogs("ADD:$dnOu",__CLASS__."/".__FUNCTION__,__FILE__);
						$update_array=array();
						$update_array["objectClass"][]="organizationalUnit";
						$update_array["objectClass"][]="top";
						$update_array["ou"][0]="users";
						if(!$ldap->ldap_add($dnOu,$update_array)){
							writelogs("Error: Adding $dnOu $ldap->ldap_last_error",__CLASS__."/".__FUNCTION__,__FILE__);
							if($ldap->ldap_last_error_num<>68){echo "Error: Adding\ndn:$dnOu\n".$this->ldap_last_error;return false;}
							}
						}
				
					
					
					
					if($ldap->ldap_add($this->dn,$upd)){
						writelogs("adding $this->dn...success, linking user $this->uid to group $this->group_id now",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						$group=new groups($this->group_id);
					  	$group->user_add_to_group($this->uid);
					  	$this->CreateMailbox();
					  	$this->UpdatePlugins();
					  	
					  	$nab=new nab($this->uid,$this->ou);
						return true;
						}
					else{
						writelogs("adding $this->dn...failed...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						$this->ldap_error=$ldap->ldap_last_error;
						return false;
						}
						
										
				}else{
					writelogs("Modify $this->dn...because, already exists, update it...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$res=$ldap->Ldap_modify($this->dn,$upd);
					if($res){$this->CreateMailbox();}
					$this->ldap_error=$ldap->ldap_last_error;
					$this->UpdatePlugins();	
					$this->AutoAliases();
					$this->SieveScripts();
					$this->obm();
					$this->MLDonKey();
					$this->joomla();
					$nab=new nab($this->uid,$this->ou);		
				}

				
		$this->UpdatePlugins();		
		return $res;			
		$this->ldap_error=$ldap->ldap_last_error;			
		return false;
	
	}
	
	function SaveSaslDB2(){
		writelogs("save in /etc/sasldb2 by framework password entry for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sock=new sockets();
		$sock->getfile("SASLDB2:$this->uid;$this->password");
		
	}
	
	function SaveUserPhoto($content){
		$ldap=new clladp();
		$upd=array();
		$upd["jpegPhoto"][0]=$content;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			$this->error=$ldap->ldap_last_error;
			return false;
		}
		return true;
	}
	
	function SavePasswordUser($password){
		$upd=array();
		$upd["userPassword"][0]=$password;
		$ldap=new clladp();
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			$this->error=$ldap->ldap_last_error;
			return false;
		}
		$this->UpdatePlugins();	
		$this->AutoAliases();
		$this->SieveScripts();
		$this->obm();
		$this->MLDonKey();
		$this->joomla();		
		return true;
	}
	
	function DotClear(){
		writelogs("Check DotClear..",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($this->DotClearUserEnabled==1){
			writelogs("DotClear enabled..",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			include_once(dirname(__FILE__).'/class.dotclear.inc');			
			$dotclear=new dotclear();
			$dotclear->AddUserDotClear($this->uid,$this->password,$this->mail,$this->homeDirectory);
			}			
		
	}
	
public function SaveJunkLearning(){
	$ldap=new clladp();
	writelogs("EnableUserSpamLearning=$this->EnableUserSpamLearning",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	if(!is_numeric($this->EnableUserSpamLearning)){$this->EnableUserSpamLearning=0;}
	$upd["EnableUserSpamLearning"][0]=$this->EnableUserSpamLearning;
	if(!$ldap->Ldap_modify($this->dn,$upd)){
			$this->error=$ldap->ldap_last_error;
			$errortext=$this->error."\nFunction: ".__FUNCTION__."\nFile: ".__FILE__."\nLine:".__LINE__;
			writelogs($errortext,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			echo $errortext;
			return false;
		}
		
	return true;
	
}
	
	
	private function MLDonKey(){
		$users=new usersMenus();
		$sock=new sockets();
		if(!$users->MLDONKEY_INSTALLED){return false;}
		$EnableMLDonKey=$sock->GET_INFO("EnableMLDonKey");
		if($EnableMLDonKey==null){$EnableMLDonKey=1;}
		if($EnableMLDonKey==0){return null;}
		
		include_once(dirname(__FILE__)."/class.donkey.inc");
		$ml=new EmuleTelnet();
		if(!$ml->UserIsActivated($this->uid)){return;}
		$ml->UserAdd($this->uid,0);
	}
	
	
function stripAccents($string){
if ( !preg_match('/[\x80-\xff]/', $string) )
          return $string;
        if ($this->seems_utf8($string)) {
          $chars = array(
          chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
          chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
          chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
          chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
          chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
          chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
          chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
          chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
          chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
     	  chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
          chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
          chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
          chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
          chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
          chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
          chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
          chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
          chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
          chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
          chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
          chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
          chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
          chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
          chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
          chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
          chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
          chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
          chr(195).chr(191) => 'y',
          chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
          chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
          chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
          chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
          chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
          chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
          chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
          chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
          chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
          chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
          chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
          chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
          chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
          chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
          chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
          chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
          chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
          chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
          chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
          chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
          chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
          chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
          chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
          chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
          chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
          chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
          chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
          chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
          chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
          chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
          chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
          chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
          chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
          chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
          chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
          chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
          chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
          chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
          chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
          chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
          chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
          chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
          chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
          chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
          chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
          chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
          chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
          chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
          chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
          chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
          chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
          chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
          chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
          chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
          chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
          chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
          chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
          chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
          chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
          chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
          chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
          chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
          chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
          chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
          chr(226).chr(130).chr(172) => 'E',
          chr(194).chr(163) => '');
          $string = strtr($string, $chars);
        } else {
          // Assume ISO-8859-1 if not UTF-8
          $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
            .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
            .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
            .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
            .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
            .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
            .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
            .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
            .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
            .chr(252).chr(253).chr(255);
       
          $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
       
          $string = strtr($string, $chars['in'], $chars['out']);
          $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
          $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
          $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }
 
        return $string;
      }	

      
function seems_utf8($Str) { 
	$length = strlen($Str);
	for ($i=0; $i < $length; $i++) {
		if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
		elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($Str[$i]) & 0xC0) != 0x80))
			return false;
		}
	}
	return true;
}    

function UsersInterfaceDatasSave($datas){
	
	$upd["UsersInterfaceDatas"][0]=$datas;
	$ldap=$this->ldapClass;
	if(!$ldap->Ldap_modify($this->dn,$upd)){
		echo "Error ". __FUNCTION__."\n".basename(__FILE__)."\line: ".__LINE__."\n$ldap->ldap_last_error";
	}
	
	else{
		writelogs("UsersInterfaceDatasSave:: $this->dn success",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	}
	
	
}

function UsersInterfaceDatasDelete(){
	
	$upd["UsersInterfaceDatas"]=$this->UsersInterfaceDatas;
	$ldap=$this->ldapClass;
	if(!$ldap->Ldap_del_mod($this->dn,$upd)){
		echo "Error ". __FUNCTION__."\n".basename(__FILE__)."\line: ".__LINE__."\n$ldap->ldap_last_error";
	}
	
	else{
		writelogs("UsersInterfaceDatasDelete:: $this->dn success",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	}
	
	
}

	
	Function UpdatePlugins(){
		$users=new usersMenus();
		writelogs("Check OBM",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		include_once(dirname(__FILE__).'/class.obm.inc');
		$obm=new obm_export_single($this->uid);
		$obm->Export();	
		
		writelogs("Check DotClear",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		$this->DotClear();
		
		writelogs("Check Samba",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($this->AsAnSambaAccount==1){$this->Samba_edit_user();}
		
		include_once(dirname(__FILE__).'/class.main_cf.inc');
		$main=new main_cf();
		$main->check_sender_access();
		$this->AutoAliases();
		writelogs("Check SaveSaslDB2",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->SaveSaslDB2();
		writelogs("Check SieveScripts",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->SieveScripts();
		writelogs("Check opengoo",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->opengoo();
		$this->GroupOffice();
		writelogs("Check obm",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->obm();
		writelogs("Check joomla",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->joomla();
		writelogs("Check Backup account",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->save_EnableBackupAccount();		
		$sock=new sockets();
		writelogs("Check rsync",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sock=new sockets();
		
		if($users->ZARAFA_INSTALLED){
			writelogs("Save Zarafa informations....",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->zarafaSaveInfos();
		}
		
		if(!$GLOBALS["NO_COMPILE_POSTFIX"]){
			$sock->getFrameWork("cmd.php?rsync-reconfigure=yes");
			$sock->getFrameWork("cmd.php?roundcube-sync=yes&ou=$this->ou");
			$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
			$sock->getFrameWork("cmd.php?cluebringer-passwords=yes");
			$sock->getFrameWork("cmd.php?sarg-passwords=yes");
			$sock->getFrameWork("cmd.php?pure-ftpd-users=yes");
		
		
		if($users->ARTICA_META_ENABLED){
			$sock->getFrameWork("cmd.php?artica-meta-user=$this->uid");
			$sock->getFrameWork("cmd.php?artica-meta-ovpn=yes&uid=$this->uid");
		}
		
		if(posix_getuid()<>0){$sock->getFrameWork("cmd.php?home-single-user=". base64_encode($this->uid));}
		}
		
		
	}
	
	
	function CreateMailbox(){
		$users=new usersMenus();
		if($users->cyrus_imapd_installed){
			include_once(dirname(__FILE__)."/class.cyrus.inc");
			$cyr=new cyrus();
			$cyr->CreateMailbox($this->uid);
		}
		
		if($users->ZARAFA_INSTALLED){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?zarafa-user-create-store=$this->uid");
		}
		
	}
	
	
	function fetchmail_add_rule($line){
		$ldap=$this->ldapClass;
		$upd["FetchMailsRules"]=$line;
		
		$res=$ldap->Ldap_add_mod($this->dn,$upd);
		$this->ldap_error=$ldap->ldap_last_error;
		return $res;
	}
	
	function fetchmail_del_rule($num){
		$line=$this->fetchmail_rules[$num];
		$upd["FetchMailsRules"]=$line;
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_del_mod($this->dn,$upd)){echo $ldap->ldap_last_error;return false;}
		$tpl=new templates();
		echo html_entity_decode($tpl->_ENGINE_parse_body("Fetchmail:\n{delete}\n{success}\n"));	
		return true;	
		}
	
	function fetchmail_edit_rules(){
		
		while (list ($num, $rule) = each($this->fetchmail_rules) ){
			$upd["FetchMailsRules"][]=$rule;
			}
		$ldap=$this->ldapClass;
		if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;}else{
			$tpl=new templates();
			echo html_entity_decode($tpl->_ENGINE_parse_body("Fetchmail:\n{success}\n"));
			}
		
	}
	
	function alias_add($aliases){
		$ldap=$this->ldapClass;
		$updatearray["mailAlias"][]=$aliases;
		$res=$ldap->Ldap_add_mod($this->dn,$updatearray);
		$this->ldap_error=$ldap->ldap_last_error;
		return $res;
		}
		
	function AutoAliases(){
			$ldap=$this->ldapClass;
			$autoaliases=new AutoAliases($this->ou);
			$mail=$this->mail;
			if(preg_match('#(.+?)@(.+)#',$mail,$re)){
				$own_domain=$re[2];
				$firstpart=$re[1];
			}else{
				return null;
			}
			if(!is_array($autoaliases->DomainsArray)){return null;}
			while (list ($num, $val) = each ($autoaliases->DomainsArray) ){
				if(trim(strtolower($val))==$own_domain){continue;}
				$aliase="$firstpart@$val";
				$uid=$ldap->uid_from_email($aliase);
				if(trim($uid)==null){
					writelogs("$aliase email is not in ldap database add it for domain",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->add_alias($aliase);
					}else{
						writelogs("$aliase email is owned by $uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					}
				
			}
		
		
		}
		
		function GetGroups($userid,$reverse=0){
		if($userid==null){return null;}	
		$attrs=array('gidNumber','cn');
		$ldap=$this->ldapClass;
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=posixGroup)(memberUid=$userid))",$attrs);	
			if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				if(!is_array($result)){return array();}
				for($i=0;$i<$result["count"];$i++){
					
					$this->array_groups[]=$result[$i]["gidnumber"][0];
					if($reverse==1){$res[$result[$i]["gidnumber"][0]]=$result[$i]["cn"][0];}else{$res[$result[$i]["cn"][0]]=$result[$i]["gidnumber"][0];}
					
				}
			return  $res;}	
		}

		
		function VacationCheck(){
			
			if(preg_match('#(.+?)@(.+)#',$this->mail,$re)){
				$aliasVacation="{$re[1]}@vacation.{$re[2]}";
			}			
			
			$users=new usersMenus();
			if($users->cyrus_imapd_installed){
				$this->delete_AliasesMailing($aliasVacation);
				return null;
			}
			
			$curr=time();
			$master_active="TRUE";
			$active="FALSE";
			writelogs("$this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs("Current time :$curr Start Time: $this->vacationStart, End time: $this->vacationEnd",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($curr>$this->vacationStart){
				if($curr<$this->vacationEnd){
					writelogs("Vacation is true",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$active="TRUE";
				}
			}
			
			if($curr>$this->vacationEnd){
				writelogs("Vacation is false",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$active="FALSE";
				$master_active="FALSE";
			}
			writelogs("vacationEnabled=$master_active, vacationActive=$active",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd["vacationEnabled"][0]=$master_active;
			$upd["vacationActive"][0]=$active;
			$ldap=$this->ldapClass;
			if(!$ldap->Ldap_modify($this->dn,$upd)){
				echo $ldap->ldap_last_error;	
			}
			
			writelogs("masteractive=$master_active",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($master_active=="TRUE"){
				writelogs("Adding $this->mail, $aliasVacation for $this->mail",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				
			}else{
				writelogs("Deleting $this->mail, $aliasVacation for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->delete_AliasesMailing($this->mail);
				$this->delete_AliasesMailing($aliasVacation);
				}
			
		}
		
		function VacationDisable(){
			$upd=array();
			$upd["vacationEnabled"][0]="FALSE";
			$upd["vacationActive"][0]="FALSE";
			$ldap=$this->ldapClass;
			if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;return false;}
			return true;		
		}

		
		function CreateModifyRootUSer($password){
					$ldap=$this->ldapClass;
					$array=array();
					$dn="cn=administrator,ou=users,dc=samba,dc=organizations,$ldap->suffix";	
					$array["userPassword"][0]=$password;
					if(!$ldap->Ldap_modify($dn,$array)){echo $ldap->ldap_last_error;return false;}
					return true;
		}
		
		
		function GetRootPassword(){
			$ldap=$this->ldapClass;
			$dn="cn=administrator,ou=users,dc=samba,dc=organizations,$ldap->suffix";
			$hash=$ldap->Ldap_read($dn,'(ObjectClass=*)',array('userPassword'));
			if(!is_array($hash)){writelogs("no rows...",__CLASS__ . "/".__FUNCTION__,__FILE__,__LINE__);return null;}
			$pass= $hash[0][strtolower('userPassword')][0];
			writelogs("userPassword=".strlen($pass). " length bytes",__CLASS__ . "/".__FUNCTION__,__FILE__,__LINE__);
			$ldap->AddUserToGroup("512","administrator");
			$ldap->AddUserToGroup("544","administrator");
			
			return $pass;
			}
			
		function GetUsersSambaPrivileges($uid){
			$sock=new sockets();
			$password=$this->GetRootPassword();
			if($password==null){return array("ERR:No Root password !!!"=>1);}
			$data=$sock->getfile('NetUsePrivs:'.$password.';'.$uid);
			$data=explode("\n",$data);
			if(!is_array($data)){return array();}
			while (list ($num, $val) = each ($data) ){
				if(trim($val)==null){continue;}
				$MA[$val]=1;
				
			}
			if(!is_array($MA)){return array();}
			return $MA;
			
		}
		
		
		function _GetuidFromDn($dn){
			$ldap=$this->ldapClass;
			$attrs=array("uid");
			$search =@ldap_read($ldap->ldap_connection,$dn,'(objectClass=*)',$attrs);
			$result =@ldap_get_entries($ldap->ldap_connection, $search);
			return $result[0]["uid"][0];
			}
			
			
		function DeleteUser(){
			$dn=$this->dn;
			$uid=$this->uid;
			$sock=new sockets();
			writelogs("Deleting $dn ($ui)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$ldap=$this->ldapClass;
			$users=new usersMenus();
			
			if(!$ldap->ExistsDN($dn)){return false;}
			
			//delete binded folders.
			if(count($this->homeDirectoryBinded)>0){
				while (list ($num, $val) = each ($this->homeDirectoryBinded) ){
					$this->delete_homeDirectoryBinded($val);}
			}
			
			$dnMOUNTS="ou=$this->uid,ou=mounts,ou=$this->ou,dc=organizations,$ldap->suffix";
			if($ldap->ExistsDN($dnMOUNTS)){
				$ldap->ldap_delete($dnMOUNTS,false);
			}
			
			
			$ldap->ldap_delete($dn,true);
			$hash=$this->Groups_list();
			if(is_array($hash)){
			while (list ($num, $val) = each ($hash) ){
				$group=new groups($num);
				writelogs("Delete user ($uid) from $val",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$group->DeleteUserFromThisGroup($this->uid);
			}}
			
			$dn="cn=$this->uid,cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
			if($ldap->ExistsDN($dn)){
				$ldap->ldap_delete($dn,true);
			}
			
			//delete imapsync
			$sql="DELETE FROM imapsync WHERE uid='$uid'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			$sock->getFrameWork("cmd.php?imapsync-cron=yes");
			
			//delete fetchmail rules.
			$sql="DELETE FROM fetchmail_rules WHERE uid='$uid'";
			$q->QUERY_SQL($sql,"artica_backup");
			
			//delete Quarantine reports
			$sql="DELETE FROM quarantine_report_users WHERE userid='$uid'";
			$q->QUERY_SQL($sql,"artica_backup");
			
			//delete picasa schedule
			$sql="DELETE FROM picasa WHERE userid='$uid'";
			$q->QUERY_SQL($sql,"artica_backup");			
			
			//joomla web sites
			$sql="UPDATE joomla_users SET install=-1,status='{scheduled}:{delete}' WHERE uid='$this->uid'";
			$q->QUERY_SQL($sql,"artica_backup");
			
			
			
			if($users->POSTFIX_INSTALLED){
				$sock->getFrameWork("cmd.php?restart-fetchmail=yes");
				$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
				$sock->getFrameWork("cmd.php?cluebringer-passwords=yes");
			}
			$sock->getFrameWork("cmd.php?pdf-quarantine-cron=yes");
			$sock->getFrameWork("cmd.php?reload-apache-groupware=yes");
			$sock->getFrameWork("cmd.php?install-joomla=yes");
			$sock->getFrameWork("cmd.php?sarg-passwords=yes");			
			
			
			//Emule user ?
			
			if($users->MLDONKEY_INSTALLED){
				$EnableMLDonKey=$sock->GET_INFO("EnableMLDonKey");
				if($EnableMLDonKey==null){$EnableMLDonKey=1;}
				if($EnableMLDonKey==1){
					include_once(dirname(__FILE__)."/class.donkey.inc");
					$ml=new EmuleTelnet();
					if($ml->UserIsActivated($this->uid)){$ml->UserDelete($this->uid);}
				}
			}			
		}
			
		function Rebuild_groups(){
			$upd["gidNumber"][0]=$this->sambaPrimaryGroupGID;
			$ldap=$this->ldapClass;
			if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error."\n".__FUNCTION__."\n".__FILE__."\n line ".__LINE__;}
			
		}
			
		function Groups_list(){
			
			if($this->EnableManageUsersTroughActiveDirectory){
				include_once(dirname(__FILE__)."/class.mysql.inc");
				$dn=utf8_encode($this->dn);
				$sql="select activedirectory_groups.groupdn,activedirectory_groupsNames.groupname from activedirectory_groups,activedirectory_groupsNames
				where activedirectory_groups.userdn='$dn' AND activedirectory_groups.groupdn=activedirectory_groupsNames.dn ORDER BY activedirectory_groupsNames.groupname";
				$q=new mysql();
				$results=$q->QUERY_SQL($sql,"artica_backup");
				$BranchsInMyql=mysql_num_rows($results);
				while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
					$res[$ligne["groupdn"]]=$ligne["groupname"];
					
				}
				if(count($res)>0){return $res;}
				
				
				$ldap=new ldapAD();
				$attrs=array("sAMAccountName");
				$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=group)(member=$this->dn))",$attrs);
				if($sr){
					$result = ldap_get_entries($ldap->ldap_connection, $sr);
					if(!is_array($result)){return array();}
				}
				
				for($i=0;$i<$result["count"];$i++){
					if($result[$i][strtolower("sAMAccountName")][0]==null){$result[$i]["displayname"][0]=$result[$i]["cn"][0];}
					$res[$result[$i]["dn"][0]]=$result[$i][strtolower("sAMAccountName")][0];
					
				}
				
				return $res;
			
			}
			
			
			$ldap=$this->ldapClass;
			$attrs=array('gidNumber','displayName','cn');
			$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=posixGroup)(memberUid=$this->uid))",$attrs);	
				if($sr){
					$result = ldap_get_entries($ldap->ldap_connection, $sr);
					if(!is_array($result)){return array();}
					for($i=0;$i<$result["count"];$i++){
						if($result[$i]["displayname"][0]==null){$result[$i]["displayname"][0]=$result[$i]["cn"][0];}
						$res[$result[$i]["gidnumber"][0]]=$result[$i]["displayname"][0];
						}
					
				}
				writelogs("$this->uid is in ".count($res)." groups",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(count($res)==0){
					writelogs("$this->uid is in master group id $this->group_id",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					if(is_array($this->gidNumber_array)){
						
						$groups=$this->gidNumber_array;
						while (list ($num, $gidNumber) = each ($groups) ){
							$group=new groups($gidNumber);
							$res[$gidNumber]=$group->groupName;
						}
					}
					
				}
			return $res;
				
			}

			
			
			
function draw_jpeg_photos(){
	$base=dirname(__FILE__)."/profiles/icons";
	if(!is_dir($base)){
		writelogs("creating directory $base",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		@mkdir("$base",0755,true);
	}
	
	
	if(strlen($this->jpegPhoto)==0){
		$this->img_identity="img/contact-unknown-user.png";
		return null;
	}
	
	if(is_file(dirname(__FILE__)."/$this->ThumbnailPath")){
		$this->img_identity=str_replace('//','/',"ressources/$this->ThumbnailPath");
		return $this->img_identity;
	}
	writelogs("Unable to stat ".dirname(__FILE__)."/$this->ThumbnailPath building a new one",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);

	$uid=md5($this->uid);
	@mkdir($base,0755);
	include_once(dirname(__FILE__).'/class.images.inc');
	$jpeg_filename="$base/{$uid}-origin.tmp";
	
	if(file_exists($jpeg_filename)){unlink($jpeg_filename);}
	
	if(!file_exists($jpeg_filename)){
		writelogs("Create temporary image ". strlen($this->jpegPhoto)." bytes in $jpeg_filename",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$outjpeg = @fopen($jpeg_filename, "wb");
		@fwrite($outjpeg, $this->jpegPhoto);
		@fclose ($outjpeg);
	}
	writelogs(basename($jpeg_filename)." = " . @filesize($jpeg_filename)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	$image=new images($jpeg_filename);
	if($image->mustInstall){
		writelogs("Return to default image $image->missing_module",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->jpegPhotoError=$image->missing_module;
		return null; 
	}
	$jpeg_dimensions=@getimagesize($jpeg_filename);
	$img_type=array(1=>"gif",2=>'jpg',3=>'png',4=>'swf',5=>'psd',6=>'bmp',7=>'tiff',8=>'tiff',9=>'jpc',10=>'jp2',11=>'jpx');
	
	$thumbnail_path="$base/thumbnail-96-{$uid}.{$img_type[$jpeg_dimensions[2]]}";
	writelogs("Image type={$img_type[$jpeg_dimensions[2]]},Image Path=$thumbnail_path",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	
	
	$this->thumbnail_path=$thumbnail_path;
	
	if($image->thumbnail(96,96,$thumbnail_path)){
		$this->img_identity="ressources/profiles/icons/thumbnail-96-{$uid}.{$img_type[$jpeg_dimensions[2]]}";
		$ldap=$this->ldapClass;
		$upd["ThumbnailPath"][0]="profiles/icons/thumbnail-96-{$uid}.{$img_type[$jpeg_dimensions[2]]}";
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			writelogs("error saving thumbnail path in ldap",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		return null;
	}else{
		$this->img_identity="img/contact-unknown-user.png"; 
	}
	
	
}



}


class nab{
	var $dn;
	var $ou;
	var $uid;
	var $ldapClass;
	
	function nab($uid,$ou){
		$this->ldapClass=new clladp();
		$this->uid=$uid;
		$this->ou=$ou;
		$this->buildNab();
		
	}
	
	function buildNab(){
		$upd=array();
		$ldap=$this->ldapClass;
		$dn="dc=NAB,$ldap->suffix";
		if(!$this->ldapClass->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organization";
			$upd["objectClass"][]="dcObject";
			$upd["o"][]="NAB";
			$upd["dc"][]="NAB";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);
		}
		
		$dn="dc=$this->ou,dc=NAB,$ldap->suffix";
		if(!$this->ldapClass->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organization";
			$upd["objectClass"][]="dcObject";
			$upd["o"][]="$this->ou";
			$upd["dc"][]="$this->ou";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);
		}
			
		$dn="ou=users,dc=$this->ou,dc=NAB,$ldap->suffix";
		if(!$this->ldapClass->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"]="users";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);			
		}
		
		$dn="ou=People,dc=$this->ou,dc=NAB,$ldap->suffix";
		if(!$this->ldapClass->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"]="People";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);			
		}	

		$dn="ou=$this->uid,ou=People,dc=$this->ou,dc=NAB,$ldap->suffix";
		if(!$this->ldapClass->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="organizationalUnit";
			$upd["ou"]="$this->uid";
			if(!$ldap->ldap_add($dn,$upd)){
				return false;
			}
			unset($upd);			
		}		
		

		
		//$dn="ou=People,dc=NAB,$ldap->suffix";
		
		
	}
	
	
}


		

?>