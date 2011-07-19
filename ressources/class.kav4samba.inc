<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.artica.inc');

class kav4samba{
	var $main_array;
	var $main_groups;
	var $dn;
	function kav4samba(){
		
		$this->LoadConfig();
		
		
	}
	
	function LoadConfig(){
		$ldap=new clladp();
		$this->dn='cn=kav4samba,cn=artica,'.$ldap->suffix;
		
		if(!$ldap->ExistsDN($this->dn)){
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='kav4samba';
			$upd["kav4sambaConf"][]=$this->build_config();
			$upd["cn"]="kav4samba";
			$ldap->ldap_add($this->dn,$upd);
			unset($upd);
		}
		
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=*)",array());
		if($res){$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$kav4sambaConf=$hash[0][strtolower('kav4sambaConf')][0];
			}
			
		
			$ini=new Bs_IniHandler();
			$ini->loadString($kav4sambaConf);
			$this->main_array=$ini->_params;
			$this->Build_defaults();
			
			
		
	}
	
	

	
	function Build_defaults(){
		$artica=new artica_general();
		if($artica->ArticaProxyServerEnabled=='yes'){
			$this->main_array["updater.options"]["UseProxy"]='yes';
			$this->main_array["updater.options"]["ProxyAddress"]=$artica->ArticaCompiledProxyUri;
		}else{
			$this->main_array["updater.options"]["UseProxy"]='no';
		}
		
	if($this->main_array["scanner.options"]["UseAVbasesSet"]==null){$this->main_array["scanner.options"]["UseAVbasesSet"]='standard';}
	if($this->main_array["scanner.options"]["Packed"]==null){$this->main_array["scanner.options"]["Packed"]='yes';}
	if($this->main_array["scanner.options"]["Recursion"]==null){$this->main_array["scanner.options"]["Recursion"]='yes';}
	if($this->main_array["scanner.options"]["Cure"]==null){$this->main_array["scanner.options"]["Cure"]='no';}
	if($this->main_array["scanner.options"]["LocalFS"]==null){$this->main_array["scanner.options"]["LocalFS"]='no';}
	if($this->main_array["scanner.options"]["MailPlain"]==null){$this->main_array["scanner.options"]["MailPlain"]='yes';}
	if($this->main_array["scanner.options"]["MailBases"]==null){$this->main_array["scanner.options"]["MailBases"]='yes';}
	if($this->main_array["scanner.options"]["Archives"]==null){$this->main_array["scanner.options"]["Archives"]='yes';}
	if($this->main_array["scanner.options"]["SelfExtArchives"]==null){$this->main_array["scanner.options"]["SelfExtArchives"]='yes';}
	if($this->main_array["scanner.options"]["Ichecker"]==null){$this->main_array["scanner.options"]["Ichecker"]='no';}
	if($this->main_array["scanner.options"]["MaxLoadAvg"]==null){$this->main_array["scanner.options"]["MaxLoadAvg"]='0.5';}
	
	
		
	}
	
	
	function build_config(){
				$this->Build_defaults();
				$conf=$conf."[path]\n";
				$conf=$conf."BasesPath=/var/opt/kaspersky/kav4samba/bases\n";
				$conf=$conf."LicensePath=/var/opt/kaspersky/kav4samba/licenses\n";
				$conf=$conf."IcheckerDbFile=/var/opt/kaspersky/kav4samba/ichecker.db\n";
				$conf=$conf."\n";
				$conf=$conf."[locale]\n";
				$conf=$conf."DateFormat=%d-%m-%Y\n";
				$conf=$conf."TimeFormat=%H:%M:%S\n";
				$conf=$conf."\n";
				$conf=$conf."[samba.options]\n";
				$conf=$conf."ExcludeDirs=\n";
				$conf=$conf."ExcludeMask=\n";
				$conf=$conf."Packed=yes\n";
				$conf=$conf."Archives=yes\n";
				$conf=$conf."SelfExtArchives=yes\n";
				$conf=$conf."MailBases=yes\n";
				$conf=$conf."MailPlain=yes\n";
				$conf=$conf."Heuristic=yes\n";
				$conf=$conf."Cure=no\n";
				$conf=$conf."Ichecker=no\n";
				$conf=$conf."FileCacheSize=2000\n";
				$conf=$conf."CheckFilesLimit=20\n";
				$conf=$conf."BgCheckFilesLimit=5\n";
				$conf=$conf."BgSheduleTime=0\n";
				$conf=$conf."HashType=md5\n";
				$conf=$conf."UseAVbasesSet=standard\n";
				$conf=$conf."\n";
				$conf=$conf."[samba.path]\n";
				$conf=$conf."BackupPath=\n";
				$conf=$conf."SambaConfigFile=/etc/samba/smb.conf\n";
				$conf=$conf."PidFile=/var/run/kav4samba/kavsamba.pid\n";
				$conf=$conf."\n";
				$conf=$conf."[samba.shares]\n";
				$conf=$conf."CheckOnOpen=yes\n";
				$conf=$conf."CheckOnClose=yes\n";
				$conf=$conf."\n";
				$conf=$conf."[samba.actions]\n";
				$conf=$conf."OnInfected={$this->main_array["samba.actions"]["OnInfected"]}\n";
				$conf=$conf."OnSuspicion={$this->main_array["samba.actions"]["OnSuspicion"]}\n";
				$conf=$conf."OnWarning={$this->main_array["samba.actions"]["OnWarning"]}\n";
				$conf=$conf."OnCured={$this->main_array["samba.actions"]["OnCured"]}\n";
				$conf=$conf."OnProtected={$this->main_array["samba.actions"]["OnProtected"]}\n";
				$conf=$conf."OnCorrupted={$this->main_array["samba.actions"]["OnCorrupted"]}\n";
				$conf=$conf."OnError={$this->main_array["samba.actions"]["OnError"]}\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."[samba.notify]\n";
				$conf=$conf."OnInfected=\n";
				$conf=$conf."OnSuspicion=\n";
				$conf=$conf."OnWarning=\n";
				$conf=$conf."OnCured=\n";
				$conf=$conf."OnProtected=\n";
				$conf=$conf."OnCorrupted=\n";
				$conf=$conf."OnError=\n";
				$conf=$conf."\n";
				$conf=$conf."[samba.report]\n";
				$conf=$conf."ReportFileName=/var/log/kaspersky/kav4samba/kavsamba.log\n";
				$conf=$conf."ReportMaxSize=\n";
				$conf=$conf."ReportLevel=4\n";
				$conf=$conf."Append=yes\n";
				$conf=$conf."ShowOk=yes\n";
				$conf=$conf."\n";
				$conf=$conf."[scanner.options]\n";
				$conf=$conf."ExcludeDirs=\n";
				$conf=$conf."ExcludeMask=\n";
				$conf=$conf."Packed={$this->main_array["scanner.options"]["Packed"]}\n";
				$conf=$conf."Archives={$this->main_array["scanner.options"]["Archives"]}\n";
				$conf=$conf."SelfExtArchives={$this->main_array["scanner.options"]["SelfExtArchives"]}\n";
				$conf=$conf."MailBases={$this->main_array["scanner.options"]["MailBases"]}\n";
				$conf=$conf."MailPlain={$this->main_array["scanner.options"]["MailPlain"]}\n";
				$conf=$conf."Heuristic=yes\n";
				$conf=$conf."LocalFS={$this->main_array["scanner.options"]["LocalFS"]}\n";
				$conf=$conf."Cure={$this->main_array["scanner.options"]["Cure"]}\n";
				$conf=$conf."Recursion={$this->main_array["scanner.options"]["Recursion"]}\n";
				$conf=$conf."Ichecker={$this->main_array["scanner.options"]["Ichecker"]}\n";
				$conf=$conf."UseAVbasesSet={$this->main_array["scanner.options"]["UseAVbasesSet"]}\n";
				$conf=$conf."MaxLoadAvg={$this->main_array["scanner.options"]["MaxLoadAvg"]}\n";
				$conf=$conf."\n";
				$conf=$conf."[scanner.path]\n";
				$conf=$conf."BackupPath=\n";
				$conf=$conf."\n";
				$conf=$conf."[scanner.object]\n";
				$conf=$conf."OnInfected=\n";
				$conf=$conf."OnSuspicion=\n";
				$conf=$conf."OnWarning=\n";
				$conf=$conf."OnCured=\n";
				$conf=$conf."OnProtected=\n";
				$conf=$conf."OnCorrupted=\n";
				$conf=$conf."OnError=\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."[scanner.container]\n";
				$conf=$conf."OnInfected=\n";
				$conf=$conf."OnSuspicion=\n";
				$conf=$conf."OnWarning=\n";
				$conf=$conf."OnCured=\n";
				$conf=$conf."OnProtected=\n";
				$conf=$conf."OnCorrupted=\n";
				$conf=$conf."OnError=\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."[scanner.report]\n";
				$conf=$conf."ReportFileName=/var/log/kaspersky/kav4samba/kavscanner.log\n";
				$conf=$conf."ReportLevel=4\n";
				$conf=$conf."Append=yes\n";
				$conf=$conf."ShowOk=yes\n";
				$conf=$conf."ShowObjectResultOnly=no\n";
				$conf=$conf."ShowContainerResultOnly=no\n";
				$conf=$conf."\n";
				$conf=$conf."[scanner.display]\n";
				$conf=$conf."ShowOk=yes\n";
				$conf=$conf."ShowProgress=yes\n";
				$conf=$conf."ShowObjectResultOnly=no\n";
				$conf=$conf."ShowContainerResultOnly=no\n";
				$conf=$conf."\n";
				$conf=$conf."[updater.path]\n";
				$conf=$conf."BackUpPath=/var/opt/kaspersky/kav4samba/bases.backup\n";
				$conf=$conf."AVBasesTestPath=/opt/kaspersky/kav4samba/lib/bin/avbasestest\n";
				$conf=$conf."\n";
				$conf=$conf."[updater.options]\n";
				$conf=$conf."KeepSilent=no\n";
				$conf=$conf."PassiveFtp=yes\n";
				$conf=$conf."UseUpdateServerUrl=no\n";
				$conf=$conf."UseUpdateServerUrlOnly=no\n";
				$conf=$conf."UpdateServerUrl=\n";
				$conf=$conf."PostUpdateCmd=/etc/init.d/kav4samba reload_avbase\n";
				$conf=$conf."RegionSettings=eu\n";
				$conf=$conf."ConnectTimeout=30\n";
				$conf=$conf."UseProxy={$this->main_array["updater.options"]["UseProxy"]}\n";
				$conf=$conf."ProxyAddress={$this->main_array["updater.options"]["ProxyAddress"]}\n";
				$conf=$conf."\n";
				$conf=$conf."[updater.report]\n";
				$conf=$conf."Append=yes\n";
				$conf=$conf."ReportFileName=/var/log/kaspersky/kav4samba/keepup2date.log\n";
				$conf=$conf."ReportLevel=4\n";
				$conf=$conf."\n";
				return $conf;
		
	}
	
	function SaveToLdap(){
		$conf=$this->build_config();
		$tpl=new templates();
		$ldap=new clladp();
		$hash['kav4sambaConf'][0]=$conf;
		if(!$ldap->Ldap_modify($this->dn,$hash)){echo $ldap->ldap_last_error;return false;}else{
			$sock=new sockets();
			$sock->getfile('kav4sambasave');
			echo $tpl->_ENGINE_parse_body('{success}');
			return true;
			}
	}
	
	
		
	
	
}

?>