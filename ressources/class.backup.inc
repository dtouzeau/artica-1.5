<?php
include_once(dirname(__FILE__) . '/class.ini.inc');


class backup_protocols{
	
	var $backuptypes=array();
	
	
	function backup_protocols(){
		$this->FillTypes();
	}
	
	
	private function FillTypes(){
			$storages["usb"]="{usb_external_drive}";
			$storages["smb"]="{remote_smb_server}";
			$storages["rsync"]="{remote_rsync_server}";	
			$storages["automount"]="{automount_ressource}";
			$storages["local"]="{local_directory}";		
			$this->backuptypes=$storages;
	}
	
	public function ParseProto($pattern){
	if(preg_match("#^smb:#",$pattern)){
			return $this->extract_smb_protocol($pattern);
			
		}
		
	if(preg_match("#^usb:#",$pattern)){
			return $this->extract_usb_protocol($pattern);
		}	

	if(preg_match("#^rsync#",$pattern)){
			return $this->extract_rsync_protocol($pattern);
		}
		
	if(preg_match("#^automount:(.+)#",$pattern,$re)){
			return $re[1];
		}

	if(preg_match("#^local:(.+)#",$pattern,$re)){
			return $re[1];
		}			

	return array();
		
	}
	
	
	function build_smb_protocol($servername,$remote_folder,$username,$password){
		if($servername==null){return null;}
		if($username<>null){
			$user="$username";
			if($password<>null){
				$user=$user.":$password";
			}
		}
		
		$pattern="smb://";
		if($user<>null){
			$pattern=$pattern."$user@$servername";
		}else{
			$pattern=$pattern."$servername";
		}
		
		if($remote_folder<>null){
			$pattern=$pattern."/$remote_folder";
			
		}
		
		
	return $pattern;	
	}
	
	function build_rsync_protocol($servername,$remote_folder,$username,$password){
		if($servername==null){return null;}
		if($username<>null){
			$user="$username";
			if($password<>null){
				$user=$user.":$password";
			}
		}
		
		$pattern="rsync://";
		if($user<>null){
			$pattern=$pattern."$user@$servername";
		}else{
			$pattern=$pattern."$servername";
		}
		
		if($remote_folder<>null){
			$pattern=$pattern."/$remote_folder";
			
		}
		
		
	return $pattern;	
	}	
	
	function extractFirsRessource($pattern){
		if(preg_match("#usb:\/\/(.+?)\/#",$pattern,$re)){
			return $re[1];
		}
		
		if(preg_match("#usb:\/\/(.+)$#",$pattern,$re)){
			return $re[1];
		}		
		
		if(preg_match("#smb:\/\/(.+?)@(.+?)\/#",$pattern,$re)){
			return $re[2];
		}
		
		if(preg_match("#smb:\/\/(.+?)\/#",$pattern,$re)){
			return $re[1];
		}
		
		if(preg_match("#rsync:\/\/(.+?)@(.+?)\/#",$pattern,$re)){
			return $re[2];
		}			

		if(preg_match("#rsync:\/\/(.+?)\/#",$pattern,$re)){
			return $re[1];
		}	
		
		if(preg_match("#automount:(.+?)$#",$pattern,$re)){
			return $re[1];
		}			
		if(preg_match("#local:(.+?)$#",$pattern,$re)){
			return $re[1];
		}				
		
	}
	
	function extract_smb_protocol($pattern){
	if(preg_match("#smb:\/\/(.+?):(.+?)@(.+?)\/(.+)$#",$pattern,$re)){
			return array(
				"USER"=>$re[1],
				"PASSWORD"=>$re[2],
				"SERVER"=>$re[3],
				"FOLDER"=>$re[4]
				);
		}
		
		if(preg_match("#smb:\/\/(.+?)\/(.+)#",$pattern,$re)){
			return array(
				"USER"=>null,
				"PASSWORD"=>null,
				"SERVER"=>$re[1],
				"FOLDER"=>$re[2]
				);
		}		
		
		
		
	}
	
	
	function extract_rsync_protocol($pattern){
	if(preg_match("#rsync:\/\/(.+?):(.+?)@(.+?)\/(.+)$#",$pattern,$re)){
			return array(
				"USER"=>$re[1],
				"PASSWORD"=>$re[2],
				"SERVER"=>$re[3],
				"FOLDER"=>$re[4]
				);
		}
		
		if(preg_match("#rsync:\/\/(.+?)\/(.+)#",$pattern,$re)){
			return array(
				"USER"=>null,
				"PASSWORD"=>null,
				"SERVER"=>$re[1],
				"FOLDER"=>$re[2]
				);
		}				
		
	}
	
	function extract_usb_protocol($pattern){
	if(preg_match("#usb:\/\/(.+?)$#",$pattern,$re)){
			return array(
				"SERVER"=>$re[1],
				"FOLDER"=>null
				);
		}
		
		if(preg_match("#usb:\/\/(.+?)/(.+?)$#",$pattern,$re)){
			return array(
				"SERVER"=>$re[1],
				"FOLDER"=>$re[2]
				);
		}				
		
	}	
	
	
}


class backup{
	var $ArticaBackupConf;
	var $ArticaBackupEnabled;
	var $HdBackupConfig;
	var $MountBackupConfig;
	var $HdBackup=array();
	var $MountBackup=array();
	var $perso_path=array();
	var $external_storage=array();
	var $params;
	var $dn;
	var $ldap_error;
	
	function backup(){
		$this->BuildTree();
		$this->load();
		$this->_ParseHDBackups();
		
		
		
	}
	
	function load(){
	$sock=new sockets();
	$ldap=new clladp();
	$artica=new artica_general();
	$this->ArticaBackupEnabled=$artica->ArticaBackupEnabled;
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=ArticaBackup)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->ArticaBackupConf=$hash[0][strtolower("ArticaBackupConf")][0];
			$this->HdBackupConfig=$hash[0][strtolower("HdBackupConfig")][0];
			$this->MountBackupConfig=$hash[0][strtolower("MountBackupConfig")][0];
		}
			
		$ini=new Bs_IniHandler();
		$ini->loadString($this->ArticaBackupConf);
		$this->params=$ini->_params["backup"];
		if($this->ArticaBackupEnabled==null){$this->ArticaBackupEnabled=0;}
		
		$this->params["ArticaBackupMaxTimeToLiveInDay"]=$sock->GET_INFO("ArticaBackupMaxTimeToLiveInDay");
		
		if($this->params["backup_path"]==null){$this->params["backup_path"]='/opt/artica/backup';}
		if($this->params["backup_time"]==null){$this->params["backup_time"]='03:00';}
		if($this->params["ArticaBackupMaxTimeToLiveInDay"]==null){$this->params["ArticaBackupMaxTimeToLiveInDay"]='7';}
		
		
		
		$tbl=explode("\n",$this->ArticaBackupConf);
		if(is_array($tbl)){
			while (list($num,$val)=each($tbl)){
				if(preg_match('#PersoPath=(.+)#',$val,$re)){
					$this->perso_path[]=$re[1];
				}
				
			}
			
		}
		
		$tbl2=explode("\n",$this->MountBackupConfig);
		if(is_array($tbl2)){
			while (list($num,$val)=each($tbl2)){
					if($val<>null){
						$this->MountBackup[]=$val;
					}
				}
				
			}
			
		}		
		
		
		
		
	function _ParseHDBackups(){
		$tbl=explode("\n",$this->HdBackupConfig);
		if(!is_array($tbl)){
			$this->HdBackup=array();
			return false;
		}
		
	    $this->HdBackup=$tbl;
		
		
	}
	
	
	function add_personal_backup($path){
		$this->perso_path[]=$path;
		$this->SaveToLdap();
		
	}
	
	function del_usb_backup($uid){
	if(is_array($this->HdBackup)){
		for($i=0;$i<count($this->HdBackup);$i++){
			if($this->HdBackup[$i]==$uid){
				unset($this->HdBackup[$i]);
				
			}
		}}
			
	$this->SaveToLdap();		
	}
	
	function add_usb_backup($uid){
		if(is_array($this->HdBackup)){
		for($i=0;$i<count($this->HdBackup);$i++){
			if($this->HdBackup[$i]==$uid){
				return false;
			}
		}}
		
		$this->HdBackup[]=$uid;
	}
	
	function BuildTree(){
		$ldap=new clladp();
		$this->dn="cn=artica-backup,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($this->dn)){
			$upd["objectClass"][]="ArticaBackup";
			$upd["objectClass"][]="top";
			$upd["ArticaBackupConf"][]=$this->BuildConf();
			$artica=new artica_general();
			$artica->ArticaBackupEnabled=1;
			$artica->Save();
			$upd["cn"][]="artica-backup";
			$ldap->ldap_add($this->dn,$upd);
			unset($upd);
			}
		}
		
		
		
	function BuildConf(){
		if($this->params["backup_path"]==null){$this->params["backup_path"]='/opt/artica/backup';}
		if($this->params["backup_time"]==null){$this->params["backup_time"]='01:00';}
		if($this->params["ArticaBackupMaxTimeToLiveInDay"]==null){$this->params["ArticaBackupMaxTimeToLiveInDay"]='7';}
		
		
		
		if(preg_match('#([0-9]+):([0-9]+)#',$this->params["backup_time"],$re)){
			$this->params["cron_cmd"]="{$re[2]} {$re[1]} * * * root /usr/share/artica-postfix/bin/artica-backup --backup >/dev/null 2>&1";
		}
		
		$conf=$conf ."[backup]\n";
		$conf=$conf ."backup_path={$this->params["backup_path"]}\n";
		$conf=$conf ."backup_time={$this->params["backup_time"]}\n";
		$conf=$conf ."cron_cmd={$this->params["cron_cmd"]}\n";
		$conf=$conf ."maxTimeToLiveInDay={$this->params["ArticaBackupMaxTimeToLiveInDay"]}\n";
		
		if(is_array($this->perso_path)){
			while (list($num,$val)=each($this->perso_path)){
				$conf=$conf."PersoPath=$val\n";
			}
		}
		
		return $conf;		
		}
		
	function SaveToLdap(){
		
		$sock=new sockets();
		$sock->SET_INFO("ArticaBackupMaxTimeToLiveInDay",$this->params["ArticaBackupMaxTimeToLiveInDay"]);
		$sock->SET_INFO("ArticaBackupTargetLocalPath",$this->params["backup_path"]);
		$ldap=new clladp();
		$upd["ArticaBackupConf"][]=$this->BuildConf();
			$artica=new artica_general();
			$artica->ArticaBackupEnabled=$this->ArticaBackupEnabled;
			$artica->Save();
		$upd["MountBackupConfig"][]=implode("\n",$this->MountBackup);
		if(is_array($this->HdBackup)){
		$upd["HdBackupConfig"][]=implode("\n",$this->HdBackup);
		}
		$res=$ldap->Ldap_modify($this->dn,$upd);
		$this->ldap_error=$ldap->ldap_last_error;
		
		if($res){
			$sock=new sockets();
			$sock->getfile('ArticaBackupSaveConf');
		}
		return $res;
	}
	
	
	
	
}



class dar_back{
	var $main_array=array();
	var $exclude_files_array=array();
	var $target_options=array();
	var $FolderSize="OM";
	var $exclude_smb_share=array();
	var $exclude_home_shares=array();
	var $smb_shares=array();
	var $homes_shares=array();
	var $perso_shares=array();
	var $array_days=array();
	var $external_storage=array();
	
	function dar_back(){
	if(isset($_SESSION["CLASS_DAR_BACK"])){
			while (list($num,$val)=each($_SESSION["CLASS_DAR_BACK"])){
				$this->$num=$val;
			}
			
			return true;
	}
		
		writelogs("Loading class",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$datas=$sock->GET_INFO("DarBackupConfig");
		$ini->loadString($datas);
		$this->main_array=$ini->_params;
		$this->exclude_files_array=explode("\n",$sock->GET_INFO("DarBackupExcludeFiles"));
		
		$this->GetFolderSize();
		$this->ParseExcludeSmbShares();
		$this->ParseExcludeHomeShares();
		$this->ParseSMBShares();
		$this->ParseHomesDirectories();
		$this->ParsePersoShares();
		$this->ParseStoragesList();
		$this->target_options=array(
		 "artica_conf",
		 "ldap_datas",
		 "mysql_datas",
		 "mailboxes",
		 "OnFly",
		"shares_folders",
		 "homes",		 
		 "user_defined"
		);
		
		$this->array_days=array("sunday","monday","tuesday","wednesday","thursday","friday","saturday");
		$this->BuildDefaults();
		$_SESSION["CLASS_DAR_BACK"]=$this;
		
		
		
		
	}
	//64-download.png
	function BuildDefaults(){
		if($this->main_array["GLOBAL"]["dar_file"]==null){$this->main_array["GLOBAL"]["dar_file"]="/home/artica/increment/backup";}
		if($this->main_array["GLOBAL"]["notify"]==null){$this->main_array["GLOBAL"]["notify"]="1";}
		if($this->main_array["GLOBAL"]["enable"]==null){$this->main_array["GLOBAL"]["enable"]="1";}
		
		
		if($this->main_array["BACKUP"]["artica_conf"]==null){$this->main_array["BACKUP"]["artica_conf"]="1";}
		if($this->main_array["BACKUP"]["ldap_datas"]==null){$this->main_array["BACKUP"]["ldap_datas"]="1";}
		if($this->main_array["BACKUP"]["mysql_datas"]==null){$this->main_array["BACKUP"]["mysql_datas"]="1";}
		if($this->main_array["BACKUP"]["shares_folders"]==null){$this->main_array["BACKUP"]["shares_folders"]="1";}
		if($this->main_array["BACKUP"]["homes"]==null){$this->main_array["BACKUP"]["homes"]="1";}
		if($this->main_array["BACKUP"]["mailboxes"]==null){$this->main_array["BACKUP"]["mailboxes"]="1";}
		if($this->main_array["BACKUP"]["OnFly"]==null){$this->main_array["BACKUP"]["OnFly"]="1";}
		if($this->main_array["BACKUP"]["slice_size_mb"]==null){$this->main_array["BACKUP"]["slice_size_mb"]="750";}
		if($this->main_array["BACKUP"]["nice_int"]==null){$this->main_array["BACKUP"]["nice_int"]="15";}
		if($this->main_array["BACKUP"]["user_defined"]==null){$this->main_array["BACKUP"]["user_defined"]="0";}
		
		if($this->main_array["BACKUP"]["hsched"]==null){$this->main_array["BACKUP"]["hsched"]="01";}
		if($this->main_array["BACKUP"]["msched"]==null){$this->main_array["BACKUP"]["msched"]="00";}
		
		
		if($this->main_array["BACKUP"]["minimal_compress"]==null){
			writelogs("Set minimal_compress to 51200 ({$this->main_array["BACKUP"]["minimal_compress"]})",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->main_array["BACKUP"]["minimal_compress"]=51200;
			}
			
		if($this->main_array["BACKUP"]["compress_level"]==null){$this->main_array["BACKUP"]["compress_level"]=6;}
		
		
		if(count($this->exclude_files_array)<2){
			$this->exclude_files_array[]="*.iso";
			$this->exclude_files_array[]="*.gz";
			$this->exclude_files_array[]="*.zip";
			$this->exclude_files_array[]="*.rar";
			$this->exclude_files_array[]="*.arj";
			$this->exclude_files_array[]="*.tar";
			$this->exclude_files_array[]="*.nrg";
			$this->exclude_files_array[]="*.img";
			$this->exclude_files_array[]="*.exe";
		}
		
		while (list($num,$val)=each($this->array_days)){
			if($this->main_array["BACKUP"][$val]==null){$this->main_array["BACKUP"][$val]=1;}
			
		}
		reset($this->array_days);
		
	}
	
	function ParseExcludeSmbShares(){
		$sock=new sockets();
		$datas=explode("\n",$sock->GET_INFO('DarBackupExcludeSmbShares'));
		if(is_array($datas)){
			while (list($num,$val)=each($datas)){
				$val=trim($val);
				if(trim($val)==null){continue;}
				$this->exclude_smb_share[md5($val)]=$val;
			}
		}
		
	}
	
	function ParseStoragesList(){
		$darfile=$this->main_array["GLOBAL"]["dar_file"];
		$sock=new sockets();
		$datas=explode("\n",$sock->GET_INFO('DarBackupStoragesList'));
		if(is_array($datas)){
			while (list($num,$val)=each($datas)){
				$val=trim($val);
				if(trim($val)==null){continue;}
				$this->external_storage[md5($val)]=$val;	
				
			}
		}
		$this->external_storage[md5($darfile)]="file:$darfile";
	}
	
	function ParseSMBShares(){
		$sock=new sockets();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?samba-shares-list=yes")));
		if(!is_array($datas)){return null;}
		while (list($num,$val)=each($datas)){
			if(trim($val)==null){continue;}
			$val=trim($val);
			$this->smb_shares[md5($val)]=$val;
		}
		
		
	}
	
	function BuildCron(){
		$line="{$this->main_array["BACKUP"]["msched"]} {$this->main_array["BACKUP"]["hsched"]} * * ";
		while (list($num,$val)=each($this->array_days)){
			if($this->main_array["BACKUP"][$val]==1){
				$days[]=$num;
			}
		}
		if(!is_array($days)){
			unset($this->main_array["GLOBAL"]["cron"]);
			return null;
		}
		$line=$line .implode(",",$days) . " root /usr/share/artica-postfix/bin/artica-backup --incremental >/dev/null";
		$this->main_array["GLOBAL"]["cron"]=$line;
	}
	
	function ParsePersoShares(){
		$sock=new sockets();
		$sock->SET_INFO("devnull","#");
		$file=$sock->GET_INFO("DarBackupPersoShares");
		
		$datas=explode("\n",$file);
		if(!is_array($datas)){
			writelogs("No persoShares ",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		writelogs(count($datas)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list($num,$val)=each($datas)){
			if(trim($val)==null){continue;}
			$val=trim($val);
			writelogs("ParsePersoShares=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->perso_shares[md5($val)]=$val;
		}
		
		
	}	
	
	
	
	
	function ParseHomesDirectories(){
		$sock=new sockets();
		$datas=explode("\n",$sock->getfile('homesFoldersList'));
		if(!is_array($datas)){return null;}
		while (list($num,$val)=each($datas)){
			if(trim($val)==null){continue;}
			$val=trim($val);
			$this->homes_shares[md5($val)]=$val;
		}
		
	}
	
	function ParseExcludeHomeShares(){
		$sock=new sockets();
		$datas=explode("\n",$sock->GET_INFO('DarBackupExcludeHomeShares'));
		if(is_array($datas)){
			while (list($num,$val)=each($datas)){
				$val=trim($val);
				if(trim($val)==null){continue;}
				$this->exclude_home_shares[md5($val)]=$val;
			}
		}
		
	}	
	
	
	function Save(){
		$ini=new Bs_IniHandler();
		$this->BuildCron();
		$this->SaveUserDefinedSchedule();
		$ini->_params=$this->main_array;
		$sock=new sockets();
		$sock->SaveConfigFile($ini->toString(),"DarBackupConfig");
		
		if(is_array($this->exclude_files_array)){
			while (list($num,$val)=each($this->exclude_files_array)){
				if(trim($val)==null){continue;}
				$exclude_files_array=$exclude_files_array.$val."\n";
			}
		}
		
		$sock->SaveConfigFile($exclude_files_array,"DarBackupExcludeFiles");
		
		if(is_array($this->exclude_smb_share)){
			while (list($num,$val)=each($this->exclude_smb_share)){
				if(trim($val)==null){continue;}
				$exclude_smb_share=$exclude_smb_share . $val."\n";
			}
		}
		$sock->SaveConfigFile($exclude_smb_share,"DarBackupExcludeSmbShares");
		
		if(is_array($this->exclude_home_shares)){
			while (list($num,$val)=each($this->exclude_home_shares)){
				if(trim($val)==null){continue;}
				$exclude_home_shares=$exclude_home_shares . $val."\n";
			}
		}		
		$sock->SaveConfigFile($exclude_home_shares,"DarBackupExcludeHomeShares");
		
		
		if(is_array($this->perso_shares)){
			while (list($num,$val)=each($this->perso_shares)){
				if(trim($val)==null){continue;}
				$perso_shares=$perso_shares . $val."\n";
			}
		}

		if(is_array($this->external_storage)){
			reset($this->external_storage);
		while (list($num,$val)=each($this->external_storage)){
				if(trim($val)==null){continue;}
				if(!preg_match('#^(usb|smb)#',$val)){continue;}
				$external_storage=$external_storage . $val."\n";
			}
			$sock->SaveConfigFile($external_storage,"DarBackupStoragesList");		
		}
		
		$sock->SaveConfigFile($perso_shares,"DarBackupPersoShares");		
		$sock->getfile('DarSaveCron');
		unset($_SESSION["CLASS_DAR_BACK"]);
	}
	
	function GetFolderSize(){
		$sock=new sockets();
		$this->FolderSize=trim($sock->getfile("foldersize:{$this->main_array["GLOBAL"]["dar_file"]}"));
		
	}
	
	function SaveUserDefinedSchedule(){
		$sock=new sockets();
		$sock->getfile('DarDeletePersoScheduleMin');
		if(!is_array($this->main_array["USER_SCHEDULES"])){return null;}
		
		while (list($num,$val)=each($this->main_array["USER_SCHEDULES"])){
			$id=$num;
			$original_path=$this->perso_shares[$id];
			if($original_path==null){
				unset($this->main_array["USER_SCHEDULES"][$id]);
				continue;
			}
			
			$sock->getfile("DarPersoScheduleMin:$id;{$this->main_array["USER_SCHEDULES"][$id]};$original_path");			
			
		}
		
		
		
	}
	
	
	
}


?>