<?php
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc");}
include_once(dirname(__FILE__) . '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.ini.inc');

class artica_general{
	var $EnableGroups="no";
	var $RelayType="mail";
	var $_params;
	var $ArticaFilterEnabled;
	var $MailFromdEnabled;
	var $ArticaProxyServerName;
	var $ArticaProxyServerPort;
	var $ArticaProxyServerUsername;
	var $ArticaProxyServerUserPassword;
	var $ArticaProxyServerEnabled;
	var $ArticaCompiledProxyUri;
	var $ArticaVersion;
	var $KasxFilterEnabled;
	var $MysqlMaxEventsLogs;
	var $ApacheArticaEnabled;
	var $ApacheConfig;
	var $lighttpConfig;	
	var $OBMEnabled;
	var $ArticaFirstWizard=0;
	var $MysqlAdminAccount;
	var $ClamavMilterEnabled;
	var $SpamAssMilterEnabled=0;
	var $spfmilterEnabled=0;
	var $EnableSyslogMysql=1;
	var $MimeDefangEnabled=0;
	var $DkimFilterEnabled;
	var $ArticaUsbBackupKeyID="NONE";
	var $NmapScanEnabled=1;
	var $RoundCubeLightHTTPD;
	var $RoundCubeHTTPEngineEnabled=0;
	var $RoundCubeArticaConfigurationFile;
	var $RoundCubeConfigurationFile;
	var $EnableMonitorix;
	var $LighttpdUseLdap;
	var $EnableFetchmail;
	var $EnableFDMFetch;
	var $MasterCFEnabled;
	var $P3ScanEnabled;
	var $SmtpNotificationConfig;
	var $MaxTempLogFilesDay;
	var $ArticaPerformancesSettings;
	var $sTunnel4enabled;
	var $EnableMilterBogom;
	var $EnableMysqlFeatures;
	var $MysqlServerName;
	var $EnableVirtualDomainsInMailBoxes;
	var $EnableCollectdDaemon=0;
	var $EnableMilterSpyDaemon=0;
	var $EnableAmavisDaemon=0;
	var $JCheckMailEnabled=0;
	var $EnableAmavisBackup=0;
	var $kavmilterEnable=0;
	var $EnableRemoteAddressBook=0;
	var $EnablePerUserRemoteAddressBook=0;
	var $EnableNonEncryptedLdapSession=1;
	var $EnableArticaAsGateway=0;
	var $EnableDHCPServer=0;
	var $ArticaBackupEnabled;
	var $MilterGreyListEnabled=0;
	var $PureFtpdEnabled;
	var $sock;
	var $ldapClass;
	
	function artica_general($ldapClass=null){
		$CurrentPageName=CurrentPageName();
		$file=null;
		if(!is_a($ldapClass,"clladp")){$this->ldapClass=new clladp();}else{$this->ldapClass=$ldapClass;}
		$this->sock=new sockets();
		$this->LoadOnDisk();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: LoadOnDisk() done.. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->lOADING ArticaDatas()");}
		$hash=$this->ldapClass->ArticaDatas();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->ArticaDatas() done.. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->LoadProxySettings();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->LoadProxySettings() done.. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->__loadArticaSettings();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->__loadArticaSettings() done.. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->__loadArticaSettings2();
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->__loadArticaSettings2() done.. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		if(isset($hash["ArticaGlobalSettings"])){$file=$hash["ArticaGlobalSettings"];}
		$this->ArticaFilterEnabled=$hash["ArticaFilterEnabled"];
		if(isset($hash["OBMEnabled"])){$this->OBMEnabled=$hash["OBMEnabled"];}
		
		if($CurrentPageName=="logon.php"){error_log("$CurrentPageName:: ->ldap_close() done.. ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		
		if($file==null){return null;}
		$ini=new Bs_IniHandler();
		$ini->loadString($file);
		$this->_params=$ini->_params;
		if($this->_params["groups"]["enable_groups_behavior"]<>null){$this->EnableGroups=$this->_params["groups"]["enable_groups_behavior"];}
		if($this->_params["Relay"]["RelayType"]<>null){$this->RelayType=$this->_params["Relay"]["RelayType"];}
		
		
	}
	
	
	function LoadOnDisk(){

			$this->ArticaVersion=$this->sock->getfile('artica_version');
			$this->RoundCubeHTTPEngineEnabled=$this->sock->GET_INFO("RoundCubeHTTPEngineEnabled");
			$this->EnableAmavisDaemon=$this->sock->GET_INFO("EnableAmavisDaemon");			
			$this->RoundCubeLightHTTPD=$this->sock->GET_INFO("RoundCubeLightHTTPD");
			$this->RoundCubeConfigurationFile=$this->sock->GET_INFO("RoundCubeConfigurationFile");
			$this->RoundCubeArticaConfigurationFile=$this->sock->GET_INFO("RoundCubeArticaConfigurationFile");
			$this->MysqlAdminAccount=$this->sock->MYSQL_INFO('database_admin').":".$this->sock->MYSQL_INFO('database_password');
			$this->MysqlServerName=$this->sock->MYSQL_INFO('mysql_server');
			$this->MimeDefangEnabled=$this->sock->GET_INFO("MimeDefangEnabled");
			$this->JCheckMailEnabled=$this->sock->GET_INFO("JCheckMailEnabled");
			$this->EnableAmavisBackup=$this->sock->GET_INFO("EnableAmavisBackup");
			$this->kavmilterEnable=$this->sock->GET_INFO("kavmilterEnable");
			$this->MailFromdEnabled=$this->sock->GET_INFO("MailFromdEnabled");
			$this->EnableRemoteAddressBook=$this->sock->GET_INFO("EnableRemoteAddressBook");
			$this->EnableFetchmail=$this->sock->GET_INFO("EnableFetchmail");
			$this->SpamAssMilterEnabled=$this->sock->GET_INFO("SpamAssMilterEnabled");
			$this->EnableArticaAsGateway=$this->sock->GET_INFO("EnableArticaAsGateway");
			$this->EnableDHCPServer=$this->sock->GET_INFO("EnableDHCPServer");
			$this->EnableMilterSpyDaemon=$this->sock->GET_INFO("EnableMilterSpyDaemon");
			$this->ClamavMilterEnabled=$this->sock->GET_INFO("ClamavMilterEnabled");
			$this->ArticaBackupEnabled=$this->sock->GET_INFO("ArticaBackupEnabled");
			$this->EnableVirtualDomainsInMailBoxes=$this->sock->GET_INFO("EnableVirtualDomainsInMailBoxes");
			$this->MilterGreyListEnabled=$this->sock->GET_INFO("MilterGreyListEnabled");
			$this->KasxFilterEnabled=$this->sock->GET_INFO("KasxFilterEnabled");
			$this->PureFtpdEnabled=$this->sock->GET_INFO("PureFtpdEnabled");
			$this->EnablePerUserRemoteAddressBook=$this->sock->GET_INFO("EnablePerUserRemoteAddressBook");
			$this->EnableNonEncryptedLdapSession=$this->sock->GET_INFO("EnableNonEncryptedLdapSession");
			$this->sTunnel4enabled=$this->sock->GET_INFO("sTunnel4enabled");
			$this->MasterCFEnabled=$this->sock->GET_INFO("MasterCFEnabled");
			if($this->EnableNonEncryptedLdapSession==null){$this->EnableNonEncryptedLdapSession=1;}
			if($this->MysqlServerName==null){$this->MysqlServerName='127.0.0.1';}
			
			
	}
	

	
	
	function __loadArticaSettings2(){
		if(!function_exists("ldap_connect")){return array();}
		$ldap=new clladp();
		if(!$ldap->UseLdap){return null;}
		if($ldap->ldapFailed){return null;}
		$upd=array();
		$dn="cn=artica-settings2,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			writelogs("$dn doesn't exists, create it....",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="ArticaSettings3";
			$upd["cn"][]="artica-settings2";
			$upd["MaxTempLogFilesDay"][]="3";
			if(!$ldap->ldap_add($dn,$upd)){
				$ldap->ldap_close();
			}
		}
			
		$sr =@ldap_read($ldap->ldap_connection,"cn=artica-settings2,cn=artica,$ldap->suffix",'objectClass=ArticaSettings3');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			$this->MaxTempLogFilesDay=$hash[0][strtolower("MaxTempLogFilesDay")][0];
			if(isset($hash[0][strtolower("ArticaPerformancesSettings")][0])){$this->ArticaPerformancesSettings=$hash[0][strtolower("ArticaPerformancesSettings")][0];}
			$ldap->ldap_close();
		}
		
		if($this->ArticaPerformancesSettings==null){$this->ArticaPerformancesSettings="#";}
		
		}
		
	function DynList(){
		$dn="cn=dynlist,cn=artica";
		
		
	}
	
	
	function __loadArticaSettings(){
		if(!function_exists("ldap_connect")){return array();}
		$ldap=new clladp();
		if(!$ldap->UseLdap){return null;}
		if($ldap->ldapFailed){return null;}
		$upd=array();
		$dn="cn=artica-settings,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			error_log("$dn doesn't exists, create it....in ".__FILE__." line ". __LINE__);
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="ArticaSettings2";
			$upd["cn"][]="artica-settings";
			$upd["MysqlMaxEventsLogs"][]="200000";
			$upd["ApacheArticaEnabled"][]="0";
			$upd["ArticaFirstWizard"][]="0";
			$upd["DkimFilterEnabled"][]="0";
			$upd["ArticaUsbBackupKeyID"][]="NONE";
			
			if(!$ldap->ldap_add($dn,$upd)){
				$ldap->ldap_close();
				error_log("unable to create $dn ".__FILE__." line ". __LINE__);
				return ;
			}
			
		}
		$sock=new sockets();
		$this->NmapScanEnabled=$sock->GET_INFO("NmapScanEnabled");
		$this->ArticaUsbBackupKeyID=$sock->GET_INFO("ArticaUsbBackupKeyID");
		$this->ApacheArticaEnabled=$sock->GET_INFO("ApacheArticaEnabled");
		
		$CurrentPageName=CurrentPageName();
		if($CurrentPageName=="logon.php"){error_log("reading cn=artica-settings,cn=artica,$ldap->suffix ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$sr =@ldap_read($ldap->ldap_connection,"cn=artica-settings,cn=artica,$ldap->suffix",'objectClass=ArticaSettings2');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			
			
			
			$this->MysqlMaxEventsLogs=$hash[0][strtolower("MysqlMaxEventsLogs")][0];
			if(isset($hash[0][strtolower("lighttpConfig")][0])){$this->lighttpConfig=$hash[0][strtolower("lighttpConfig")][0];}
			if(isset($hash[0][strtolower("ApacheConfig")][0])){$this->ApacheConfig=$hash[0][strtolower("ApacheConfig")][0];}
			$this->ArticaFirstWizard=$hash[0][strtolower("ArticaFirstWizard")][0];
			
			
			if(isset($hash[0][strtolower("spfmilterEnabled")][0])){$this->spfmilterEnabled=$hash[0][strtolower("spfmilterEnabled")][0];}
			if(isset($hash[0][strtolower("EnableSyslogMysql")][0])){$this->EnableSyslogMysql=$hash[0][strtolower("EnableSyslogMysql")][0];}
			$this->DkimFilterEnabled=$hash[0][strtolower("DkimFilterEnabled")][0];
		
			if(isset($hash[0][strtolower("EnableMonitorix")][0])){$this->EnableMonitorix=$hash[0][strtolower("EnableMonitorix")][0];}
			if(isset($hash[0][strtolower("LighttpdUseLdap")][0])){$this->LighttpdUseLdap=$hash[0][strtolower("LighttpdUseLdap")][0];}
			if(isset($hash[0][strtolower("EnableFDMFetch")][0])){$this->EnableFDMFetch=$hash[0][strtolower("EnableFDMFetch")][0];}
			if(isset($hash[0][strtolower("P3ScanEnabled")][0])){$this->P3ScanEnabled=$hash[0][strtolower("P3ScanEnabled")][0];}
			if(isset($hash[0][strtolower("EnableMilterBogom")][0])){$this->EnableMilterBogom=$hash[0][strtolower("EnableMilterBogom")][0];}
			if(isset($hash[0][strtolower("EnableMysqlFeatures")][0])){$this->EnableMysqlFeatures=$hash[0][strtolower("EnableMysqlFeatures")][0];}
			if(isset($hash[0][strtolower("EnableCollectdDaemon")][0])){$this->EnableCollectdDaemon=$hash[0][strtolower("EnableCollectdDaemon")][0];}
			
			
			if($this->ClamavMilterEnabled==null){$this->ClamavMilterEnabled=0;}
			if($this->SpamAssMilterEnabled==null){$this->SpamAssMilterEnabled=0;}			
			if($this->spfmilterEnabled==null){$this->spfmilterEnabled=0;}
			if($this->EnableSyslogMysql==null){$this->EnableSyslogMysql=1;}
			if($this->DkimFilterEnabled==null){$this->DkimFilterEnabled=0;}
			if($this->NmapScanEnabled==null){$this->NmapScanEnabled=0;}
			if($this->RoundCubeHTTPEngineEnabled==null){$this->RoundCubeHTTPEngineEnabled=0;}
			if($this->RoundCubeConfigurationFile==null){$this->RoundCubeConfigurationFile="#";}
			if($this->RoundCubeLightHTTPD==null){$this->RoundCubeLightHTTPD="#";}	
			if($this->RoundCubeArticaConfigurationFile==null){$this->RoundCubeArticaConfigurationFile="NONE";}
			if($this->EnableMonitorix==null){$this->EnableMonitorix="0";}
			if($this->LighttpdUseLdap==null){$this->LighttpdUseLdap="0";}
			if($this->EnableFDMFetch==null){$this->EnableFDMFetch="0";}
			if($this->MasterCFEnabled==null){$this->MasterCFEnabled="0";}
			if($this->P3ScanEnabled==null){$this->P3ScanEnabled="0";}
			if($this->EnableFetchmail==null){$this->EnableFetchmail="0";}
			if($this->MaxTempLogFilesDay==null){$this->MaxTempLogFilesDay=3;}
			if($this->sTunnel4enabled==null){$this->sTunnel4enabled=0;}
			if($this->EnableMilterBogom==null){$this->EnableMilterBogom=0;}
			if($this->EnableMysqlFeatures==null){$this->EnableMysqlFeatures=1;}
			if($this->MysqlServerName==null){$this->MysqlServerName="127.0.0.1";}
			if($this->EnableVirtualDomainsInMailBoxes==null){$this->EnableVirtualDomainsInMailBoxes="0";}
			if($this->EnableCollectdDaemon==null){$this->EnableCollectdDaemon="0";}
			if($this->EnableMilterSpyDaemon==null){$this->EnableMilterSpyDaemon="0";}
			if($this->EnableAmavisDaemon==null){$this->EnableAmavisDaemon="0";}
			$ldap->ldap_close();
			if($CurrentPageName=="logon.php"){error_log("reading cn=artica-settings,cn=artica,$ldap->suffix done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
			

		}
		
	}
	
	

	
	
		

	
	
	function LoadProxySettings(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$CurrentPageName=CurrentPageName();
		$datas=$sock->GET_INFO("ArticaProxySettings");
		if($CurrentPageName=="logon.php"){error_log("-ArticaProxySettings=". strlen($datas) ." bytes ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		if(trim($datas)<>null){
			$ini->loadString($datas);
			$this->ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
			$this->ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
			$this->ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
			$this->ArticaProxyServerUsername=$ini->_params["PROXY"]["ArticaProxyServerUsername"];
			$this->ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];
			$this->ArticaCompiledProxyUri=$ini->_params["PROXY"]["ArticaCompiledProxyUri"];
		}
	  	
		}
	
	function SaveProxySettings(){
		$conf="[PROXY]\n";
		$conf=$conf."ArticaProxyServerName=$this->ArticaProxyServerName\n";
		$conf=$conf."ArticaProxyServerPort=$this->ArticaProxyServerPort\n";
		$conf=$conf."ArticaProxyServerUsername=$this->ArticaProxyServerUsername\n";
		$conf=$conf."ArticaProxyServerUserPassword=$this->ArticaProxyServerUserPassword\n";
		$conf=$conf."ArticaProxyServerEnabled=$this->ArticaProxyServerEnabled\n";
		
		if(trim($this->ArticaProxyServerUserPassword)<>null){$p=":$this->ArticaProxyServerUserPassword";}
		if(trim($this->ArticaProxyServerUsername)<>null){$at="$this->ArticaProxyServerUsername$p@";}
		if(trim($this->ArticaProxyServerPort)<>null){$port=":$this->ArticaProxyServerPort";}
		
		$uri="http://$at$this->ArticaProxyServerName$port";
		if($this->ArticaProxyServerEnabled=='yes'){
			echo $uri."\n----------------------------------------------\n";
		}
		$conf=$conf."ArticaCompiledProxyUri=$uri\n";
		$sock=new sockets();
		$sock->SaveConfigFile($conf,"ArticaProxySettings");
		$sock->getFrameWork("cmd.php?compile-proxy=yes");
		
	}
	
	function SaveSettings2(){
		if(!function_exists("ldap_connect")){return array();}
		if($this->ldapClass->suffix==null){$this->ldapClass=new clladp();}
		$upd=array();
		$dn="cn=artica-settings2,cn=artica,{$this->ldapClass->suffix}";
		$upd["MaxTempLogFilesDay"][0]=$this->MaxTempLogFilesDay;
		$upd["ArticaPerformancesSettings"][0]=$this->ArticaPerformancesSettings;
		if(!$this->ldapClass->Ldap_modify($dn,$upd)){
			echo "Save Artica LDAP Settings ".__FUNCTION__." failed\n$this->ldapClass->ldap_last_error";
		}
		
		
	}
	
	function SaveMysqlSettings(){
		
		
		
		//Fetchmail or FDM ??
		if ($this->EnableFetchmail==1){$this->EnableFDMFetch=0;}
		if ($this->EnableFDMFetch==1){
			writelogs("Disable Fetchmail because EnableFDMFetch is activated",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->EnableFetchmail=0;}
	
		if(preg_match('#(.+?):(.*)#',$this->MysqlAdminAccount,$re)){
			$rootm=$re[1];
			$pwd=$re[2];
			if($this->MysqlServerName==null){$this->MysqlServerName='127.0.0.1';}
			$arrayMysqlinfos=array("USER"=>$rootm,"PASSWORD"=>$pwd,"SERVER"=>$this->MysqlServerName);
			$cmd=base64_encode(serialize($arrayMysqlinfos));
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?change-mysql-params=$cmd");
			
		}
		
	//define rules when amavis is enabled:
	    if($this->JCheckMailEnabled==1){
	    	$this->SpamAssMilterEnabled=0;
			$this->ClamavMilterEnabled=0;
			$this->MimeDefangEnabled=0;
			$this->DkimFilterEnabled=0;
			$this->EnableMilterSpyDaemon=0;
			$this->EnableAmavisDaemon=0;
	    	}
	  
	
		if($this->EnableAmavisDaemon==1){
			writelogs("EnableAmavisDaemon is active, disable spamassassin,clamav,MimeDefang,DKIMFIlter,MilterSpy,MailArchiver",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->SpamAssMilterEnabled=0;
			$this->ClamavMilterEnabled=0;
			$this->MimeDefangEnabled=0;
			$this->DkimFilterEnabled=0;
			$this->EnableMilterSpyDaemon=0;
			}
			
		if($this->MimeDefangEnabled==1){
			$this->EnableAmavisDaemon=0;
			$this->ClamavMilterEnabled=0;
			$this->SpamAssMilterEnabled=0;
			$this->DkimFilterEnabled=0;
			}
			
		$sock=new sockets();
		$sock->SET_INFO('MimeDefangEnabled',$this->MimeDefangEnabled);
		$sock->SET_INFO('JCheckMailEnabled',$this->JCheckMailEnabled);
		$sock->SET_INFO('EnableAmavisBackup',$this->EnableAmavisBackup);
		$sock->SET_INFO('kavmilterEnable',$this->kavmilterEnable);
		$sock->SET_INFO('MailFromdEnabled',$this->MailFromdEnabled);
		$sock->SET_INFO('EnableRemoteAddressBook',$this->EnableRemoteAddressBook);
		$sock->SET_INFO('EnablePerUserRemoteAddressBook',$this->EnablePerUserRemoteAddressBook);
		$sock->SET_INFO("EnableNonEncryptedLdapSession",$this->EnableNonEncryptedLdapSession);
		$sock->SET_INFO('SpamAssMilterEnabled',$this->SpamAssMilterEnabled);
		$sock->SET_INFO('EnableArticaAsGateway',$this->EnableArticaAsGateway);
		$sock->SET_INFO('EnableDHCPServer',$this->EnableDHCPServer);
		$sock->SET_INFO('EnableFetchmail',$this->EnableFetchmail);
		$sock->SET_INFO('EnableMilterSpyDaemon',$this->EnableMilterSpyDaemon);
		$sock->SET_INFO('ClamavMilterEnabled',$this->ClamavMilterEnabled);
		$sock->SET_INFO('ArticaBackupEnabled',$this->ArticaBackupEnabled);
		$sock->SET_INFO('EnableVirtualDomainsInMailBoxes',$this->EnableVirtualDomainsInMailBoxes);
		$sock->SET_INFO('MilterGreyListEnabled',$this->MilterGreyListEnabled);
		$sock->SET_INFO('KasxFilterEnabled',$this->KasxFilterEnabled);
		$sock->SET_INFO('EnableAmavisDaemon',$this->EnableAmavisDaemon);
		$sock->SET_INFO('sTunnel4enabled',$this->sTunnel4enabled);
		$sock->SET_INFO('MasterCFEnabled',$this->MasterCFEnabled);

		
		if($this->ldapClass->suffix==null){$this->ldapClass=new clladp();}
		$dn="cn=artica-settings,cn=artica,{$this->ldapClass->suffix}";
		$upd=array();
		$upd["MysqlMaxEventsLogs"][0]=$this->MysqlMaxEventsLogs;
		if($this->lighttpConfig<>null){$upd["lighttpConfig"][0]=$this->lighttpConfig;}
		if($this->ApacheConfig<>null){$upd["ApacheConfig"][0]=$this->ApacheConfig;}
		
		$upd["ArticaFirstWizard"][0]=$this->ArticaFirstWizard;
		$upd["spfmilterEnabled"][0]=$this->spfmilterEnabled;
		if(function_exists("artica_get_memory_usage")){artica_get_memory_usage();}
		$upd["EnableSyslogMysql"][0]=$this->EnableSyslogMysql;
		$upd["DkimFilterEnabled"][0]=$this->DkimFilterEnabled;
		
		
		$upd["EnableMonitorix"][0]=$this->EnableMonitorix;
		$upd["LighttpdUseLdap"][0]=$this->LighttpdUseLdap;
		$upd["EnableFDMFetch"][0]=$this->EnableFDMFetch;
		$upd["P3ScanEnabled"][0]=$this->P3ScanEnabled;
		
		
		
		$sock=new sockets();
		$sock->SET_INFO("ArticaUsbBackupKeyID",$this->ArticaUsbBackupKeyID);
		$sock->SET_INFO("NmapScanEnabled",$this->NmapScanEnabled);
		$sock->SET_INFO("ApacheArticaEnabled",$this->ApacheArticaEnabled);
		
		
		$upd["EnableMilterBogom"][0]=$this->EnableMilterBogom;	
		writelogs("EnableMysqlFeatures=$this->EnableMysqlFeatures servername=$this->MysqlServerName",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		$upd["EnableMysqlFeatures"][0]=$this->EnableMysqlFeatures;
		$upd["MysqlServerName"][0]=$this->MysqlServerName;
		$upd["EnableCollectdDaemon"][0]=$this->EnableCollectdDaemon;
		$update_array=array();
		
	if(!$this->ldapClass->ExistsDN($dn)){
				$update_array["cn"][]='artica-settings';
				$update_array["objectClass"][]='organizationalRole';
				$update_array["objectClass"][]='ArticaSettings';
				$update_array["objectClass"][]='top';
				if(!$this->ldapClass->ldap_add($dn,$update_array)){
					echo "Building artica-settings branch failed\n{$this->ldapClass->ldap_last_error}";	
				}
	}else{
		writelogs("$dn exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
	}
		
		

	if(!$this->ldapClass->Ldap_modify($dn,$upd)){
		echo "Build LDAP Artica Parametres failed\n{$this->ldapClass->ldap_last_error}\n";
		return null;
	}
		writelogs("synchronizeModules...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock=new sockets();
		$sock->getfile('synchronizeModules');
		//$ldap->ldap_close();
		
	}
	
	
	function Save(){
		$ArticaFilterEnabled=$this->ArticaFilterEnabled;
		$this->SaveMysqlSettings();
		$this->_params["groups"]["enable_groups_behavior"]=$this->EnableGroups;
		$this->_params["Relay"]["RelayType"]=$this->RelayType;
		$ini=new Bs_IniHandler();
		$ini->_params=$this->_params;
		$file=$ini->toString();
		$hash=$this->ldapClass->ArticaDatas();
		$this->SaveMysqlSettings();
		$this->ChangeRelaySettings();
		$this->SaveSettings2();
		$sock=new sockets();
		$sock->getfile("synchronizeModules");
		return true;
		
	}
	
	function ChangeRelaySettings(){
	
		$ArrayOU=$this->ldapClass->hash_get_ou();
		if(!is_array($ArrayOU)){return null;}
		
		if($this->RelayType=='single'){
			writelogs("Relais type is a single relay... Add domains in VirtualDomainsMapsMTA",__FUNCTION__,__FILE__);
			while (list ($num, $val) = each ($ArrayOU) ){
				$this->_Add_VirtualDomainsMapsMTA($val);
			}
		}else{
			writelogs("Relais type is a medium relay... delete domains in VirtualDomainsMapsMTA if it exists",__FUNCTION__,__FILE__);	
			while (list ($num, $val) = each ($ArrayOU) ){
				$this->ldapClass->delete_all_VirtualDomainsMapsMTA($val);
			}			
		}
	}
	
	function _Add_VirtualDomainsMapsMTA($ou){
		
		$domains=$this->ldapClass->Hash_domains_table($ou);
		if(!is_array($domains)){
			writelogs("No domains for $ou..",__FUNCTION__,__FILE__);
			return null;
			}
			
		while (list ($domain, $val) = each ($domains) ){			
			$this->ldapClass->add_domains_to_VirtualDomainsMapsMTA($ou,$domain);
		}
	}			
	
	
}


class artica{
	var $array_config;
	var $array_ldap_config;
	var $dap;
	var $error=false;

	
	function artica(){
		$sock=new sockets();
		$confile=$sock->getfile('getMyConf');
		if($sock->error==true){$this->error=true;return null;}
		$ini=new Bs_IniHandler();
		$ini->loadString($confile);
		$this->array_config=$ini->_params;
		if($this->array_config["ARTICA"]["MANAGE_MAILBOXES"]=='yes'){$this->array_config["ARTICA"]["MANAGE_MAILBOXES"]="TRUE";}
		
		$confile=$sock->getfile('getMyLdapConf');
		$ini=new Bs_IniHandler();
		$ini->loadString($confile);
		$this->array_ldap_config=$ini->_params;
		
		}
		
	function Save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->array_config;
		$conffile=dirname(__FILE__) . '/conf/artica-postfix.conf';
		$ini->saveFile($conffile);
		$sock=new sockets();
		$sock->getfile('SaveMyConf:'.$conffile);
		}
	function saveLDapSettings(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->array_ldap_config;
		$conffile=dirname(__FILE__) . '/conf/artica-postfix-ldap.conf';
		$ini->saveFile($conffile);
		$sock=new sockets();
		$sock->getfile('SaveMyConf:'.$conffile);
		}
	
}
?>