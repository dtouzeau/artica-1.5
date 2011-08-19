<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.user.inc");
include_once(dirname(__FILE__)."/class.mysql.inc");
include_once(dirname(__FILE__)."/class.samba.aliases.inc");



class samba{
	var $dn;
	var $main_array=array();
	var $global_conf;
	var $main_folders;
	var $main_shared_folders=array();
	var $SambaUsbShare;
	var $usb_array=array();
	var $LOCAL_SID;
	var $SambaEnableEditPosixExtension=0; // see http://wiki.samba.org/index.php/Ldapsam_Editposix
	var $EnableSambaActiveDirectory=0;
	var $config_activedirectory=array();
	var $DisableWinbindd=0;
	var $WINBINDD_INSTALLED=false;
	var $CUPS_INSTALLED=false;
	var $EnablePrintersSharing=0;
	var $posix_getuid=-1;
	var $TypeOfSamba=1;
	var $ldap;
	var $usersMenus;
	var $SAMBA_VERSION=0;
	var $samba_aliases=array();
	var $SECOND_PART_CONF="";
	var $WORKGROUP="";
	var $EnableSambaVirtualsServers=0;
	var $EnableKerbAuth=0;
	var $PHP5_BIN;
	var $EnableSMB2=0;
	var $client_ntlmv2_auth=0;
	
	function samba(){
		$this->ldap=new clladp();
		$this->usersMenus=new usersMenus();
		$this->PHP5_BIN=$this->usersMenus->PHP_BIN_PATH;
		$sock=new sockets();
		$this->SAMBA_VERSION=$this->usersMenus->SAMBA_VERSION;
		$this->EnableSambaVirtualsServers=$sock->GET_INFO("EnableSambaVirtualsServers");
		$this->TypeOfSamba=$sock->GET_INFO("TypeOfSamba");
		$this->EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
		$this->SambaEnableEditPosixExtension=$sock->GET_INFO("SambaEnableEditPosixExtension");
		$this->EnableSMB2=$sock->GET_INFO("EnableSMB2");	
		$this->client_ntlmv2_auth=$sock->GET_INFO("client_ntlmv2_auth");	
		
		$this->check36Spects();
		
		if(isset($GLOBALS["AS_ROOT"])){
			if($GLOBALS["AS_ROOT"]){$this->posix_getuid=0;}else{$this->posix_getuid=100;}
		}else{
			$this->posix_getuid=posix_getuid();
		}
		if(!isset($GLOBALS["AS_ROOT"])){if($this->posix_getuid==0){$GLOBALS["AS_ROOT"]=true;}else{$GLOBALS["AS_ROOT"]=false;}}
		
		
		if($GLOBALS["VERBOSE"]){
			if(function_exists("debug_backtrace")){
				$trace=debug_backtrace();
				$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			
			}
			writelogs("init class samba() $called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}		
		
		
		if($GLOBALS["VERBOSE"]){writelogs("Verify LDAP branch...",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		$this->verifyBranch();
		if($GLOBALS["VERBOSE"]){writelogs("Verify LDAP branch done",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		
		
		$this->CheckAdmin();
		$this->WinbinddChoose();
		$this->LoadSettings();
		$this->ParseFolders();
	}
	
	private function WinbinddChoose(){
		if($this->TypeOfSamba==3){$this->DisableWinbindd=0;return;}
		
		$sock=new sockets();
		$this->EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
		if($this->EnableSambaActiveDirectory==1){$this->DisableWinbindd=0;return;}
		$DisableWinbindd=$sock->GET_INFO("DisableWinbindd");
		if(!is_numeric($DisableWinbindd)){
			$this->DisableWinbindd=1;
			$sock->SET_INFO("DisableWinbindd",1);
		}		
		$this->DisableWinbindd=$DisableWinbindd;
		
	}
	
	
function verifyBranch(){
	if($GLOBALS["VERBOSE"]){writelogs("verify cn=samba-config,cn=artica,...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	
	$upd=array();
	$this->dn="cn=samba-config,cn=artica,{$this->ldap->suffix}";
	if(!$this->ldap->ExistsDN($this->dn)){
		writelogs("Create $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='SambaArticaClass';
		$upd["cn"]="samba-config";
		$upd["SambaSMBConf"][]=$this->BuildConfig();
		$upd["SambaUsbShare"][]="#";
		if($GLOBALS["VERBOSE"]){writelogs("ldap->ldap_add($this->dn,Array);",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		if(!$this->ldap->ldap_add($this->dn,$upd)){
			writelogs("Ldap error {$this->ldap->ldap_last_error}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
	}
	if($GLOBALS["VERBOSE"]){writelogs("ldap->ldap_add($this->dn,Array); done..",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	unset($upd);
	$dn="cn={$this->ldap->ldap_admin},{$this->ldap->suffix}";
	if(!$this->ldap->ExistsDN($dn)){
		$upd["objectClass"][]='organizationalRole';
		$upd["cn"][]=$this->ldap->ldap_admin;
		$this->ldap->ldap_add($dn,$upd);
		
	}

	
}


function BuildDefaults(){
	if($GLOBALS["VERBOSE"]){writelogs("Starting check defaults values...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	
	$sock=new sockets();
	
	if($this->main_array["global"]["workgroup"]==null){$this->main_array["global"]["workgroup"]="MSHOME";}
	if($this->main_array["global"]["server string"]==null){$this->main_array["global"]["server string"]="%h server";}
	if($this->main_array["global"]["disable netbios"]==null){$this->main_array["global"]["disable netbios"]="no";}
	if($this->main_array["global"]["domain logons"]==null){$this->main_array["global"]["domain logons"]="no";}
	if($this->main_array["global"]["domain master"]==null){$this->main_array["global"]["domain master"]="no";}
	if($this->main_array["global"]["preferred master"]==null){$this->main_array["global"]["preferred master"]="no";}
	if($this->main_array["global"]["local master"]==null){$this->main_array["global"]["local master"]="yes";}
	if($this->main_array["global"]["os level"]==null){$this->main_array["global"]["os level"]="40";}
	if($this->main_array["global"]["netbios name"]==null){$this->main_array["global"]["netbios name"]=$users->hostname;}
	if($this->main_array["global"]["dns proxy"]==null){$this->main_array["global"]["dns proxy"]="no";}
	if($this->main_array["global"]["name resolve order"]==null){$this->main_array["global"]["name resolve order"]="host lmhosts wins bcast";}
	
	if($this->main_array["global"]["nt acl support"]==null){$this->main_array["global"]["nt acl support"]="yes";}
	if($this->main_array["global"]["map acl inherit"]==null){$this->main_array["global"]["map acl inherit"]="yes";}
	if($this->main_array["global"]["acl check permissions"]==null){$this->main_array["global"]["acl check permissions"]="yes";}
	if($this->main_array["global"]["acl group control"]==null){$this->main_array["global"]["acl group control"]="yes";}
	if($this->main_array["global"]["inherit permissions"]==null){$this->main_array["global"]["inherit permissions"]="no";}
	if($this->main_array["global"]["inherit acls"]==null){$this->main_array["global"]["inherit acls"]="no";}
	if($this->main_array["global"]["dns proxy"]==null){$this->main_array["global"]["dns proxy"]="no";}		
	
	
	
	
	
	if($this->usersMenus->CUPS_INSTALLED){
		$this->main_array["global"]["printing"]="cups";
		$this->main_array["global"]["printcap name"]="cups";
		$this->main_array["global"]["load printers"]="yes";
		}
		
	$SambaNetBiosName=trim($sock->GET_INFO("SambaNetBiosName"));
	if($SambaNetBiosName<>null){$this->main_array["global"]["netbios name"]=$SambaNetBiosName;}	
	if($GLOBALS["VERBOSE"]){writelogs("Building default settings done...",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
	$this->check36Spects();
}

function SaveUSB(){
	while (list ($uuid, $ligne) = each ($this->usb_array) ){
		$conf=$conf."<uuid>$uuid</uuid><name>{$ligne["name"]}</name><umounttime>{$ligne["time"]}</umounttime><maxoff>{$ligne["maxoff"]}</maxoff>\n";
		
	}
	$upd=array();
	reset($this->usb_array);
	$this->SambaUsbShare=$conf;
	$upd["SambaUsbShare"][]=$this->SambaUsbShare;
	
	if(!$this->ldap->Ldap_modify($this->dn,$upd)){echo $this->ldap->ldap_last_error;return null;}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{success}:Samba USB Devices;\n");
		$this->SaveToLdap();
	}	
	
}

function SaveToLdap($nosaveToFile=false){
	
	$conf=$this->BuildConfig();
	$group=new groups();
	$group->EditSambaGroups();
	$this->CreateGuestUser();
	$ldap=new clladp();
	$dn="uid=root,ou=users,dc=samba,dc=organizations,{$ldap->suffix}";
	if(!$ldap->ExistsDN($dn)){$this->createRootID();}
	$sock=new sockets();
	writelogs("compiling first part ". strlen($conf)." second part ". strlen($this->SECOND_PART_CONF)." bytes",__CLASS__,__FUNCTION__,__FILE__);
	$sock->SaveConfigFile($conf."\n".$this->SECOND_PART_CONF,"SambaSMBConf");
	$sock->SET_INFO("SambaEnableEditPosixExtension",$this->SambaEnableEditPosixExtension);
	$sock->SET_INFO("EnableSMB2", $this->EnableSMB2);
	$sock->SET_INFO("client_ntlmv2_auth", $this->client_ntlmv2_auth);
	
	
	
	if(!$nosaveToFile){$sock->getFrameWork('cmd.php?samba-save-config=yes');}
	
}

function SaveUsbInfos($uuid,$share_name,$array){
	$q=new mysql();
	$infos=base64_encode(serialize($array));
	$sql="SELECT `uuid` FROM samba_usb_share WHERE `uuid`='$uuid'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$sql="UPDATE samba_usb_share SET `settings`='$infos', `share_name`='$share_name' WHERE `uuid`='$uuid'";
	
	if($ligne["uuid"]==null){
		$sql="INSERT INTO samba_usb_share (`uuid`,`settings`,`share_name`) 
		VALUES ('$uuid','$share_name','$infos')";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	
}

function LoadSettings(){
		
		
		$sock=new sockets();
		$this->global_conf=$sock->GET_INFO("SambaSMBConf")."\n".$sock->GET_INFO("SambaSecondPartConf");
		if(strlen($this->global_conf)<50){$this->global_conf=base64_decode($sock->getFrameWork("cmd.php?samba-original-conf=yes"));}
		
		$ini=new Bs_IniHandler();
		$ini->loadString($this->global_conf);
		$this->main_array=$ini->_params;
		$this->BuildDefaults();
		if($this->SambaUsbShare==null){$this->SambaUsbShare='#';}
		$this->CleanAllDomains($this->main_array["global"]["workgroup"]);
		$this->ParseUSB();
		
	}

function ParseUSB(){
	$tbl=explode("\n",$this->SambaUsbShare);
	while (list ($index, $ligne) = each ($tbl) ){
		if(preg_match('#<uuid>(.+?)</uuid><name>(.+?)</name><umounttime>(.+?)</umounttime>#',$ligne,$re)){
			if(preg_match('#<maxoff>(.+?)</maxoff>#',$ligne,$ra)){
				$re[4]=$ra[1];
			}else{
				$re[4]=10;
			}
			
			$this->usb_array[$re[1]]=array("name"=>$re[2],"time"=>$re[3],"maxoff"=>$re[4]);
		}
		
	}
}
	

function ParseFolders(){
	reset($this->main_array);
	while (list ($FOLDER, $ligne) = each ($this->main_array) ){
		if($FOLDER<>"global"){
			if(is_array($ligne)){
				$this->main_folders[$FOLDER]=$FOLDER;
				$this->main_shared_folders[$this->main_array[$FOLDER]["path"]]=$FOLDER;
			}
		}
	}
	reset($this->main_array);
}

function UnShareFolder($fullpath_no_utf8encode=null){
	if($fullpath_no_utf8encode==null){return;}
	$requested_path=utf8_encode($fullpath_no_utf8encode);
	while (list ($key, $array) = each ($this->main_array)){
		if($key==null){continue;}
		if($array["path"]==$requested_path){
			$key_to_delete=$key;
			writelogs("directory {$array["path"]}/$key match $requested_path",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			break;
		}
	}
	
	reset($this->main_array);
	if($key_to_delete==null){return false;}
	unset($this->main_array[$key_to_delete]);
	$this->SaveToLdap();
	return true;
}
function GetShareName($fullpath_no_utf8encode=null){
	if($fullpath_no_utf8encode==null){return;}
	$requested_path=utf8_encode($fullpath_no_utf8encode);
	while (list ($key, $array) = each ($this->main_array)){
		if($key==null){continue;}
		if($array["path"]==$requested_path){
			reset($this->main_array);
			return $key;
			
		}
	}
}

function RemoveShareFromFolder($path){
	$ShareName=$this->GetShareName($path);
	if(strtolower($ShareName)=="global"){return;}
	if($ShareName<>null){
		unset($this->main_array[$ShareName]);
		$this->SaveToLdap();
	}
}


function CheckAdmin(){
	
	
	writelogs("suffix: {$this->ldap->suffix}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	$dn="cn=administrator,ou=users,dc=samba,dc=organizations,{$this->ldap->suffix}";
	if(!$this->ldap->ExistsDN("$dn")){
		$this->CreateAdministrator("administrator",$ldap->ldap_password);
	}
	
}


function GetAdminUidNumber($admin_name){
	
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,{$this->ldap->suffix}";
	$sr =@ldap_read($this->ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($this->ldap->ldap_connection,$sr);	
			return $hash[0][strtolower("uidNumber")][0];	
			
		}
	}
	
function GetAdminPassword($admin_name){
	
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,{$this->ldap->suffix}";
	$sr =@ldap_read($this->ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($this->ldap->ldap_connection,$sr);	
			return $hash[0][strtolower("userPassword")][0];	
		}
	}

	
function EditAdministrator($admin_name,$password){
	
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,{$this->ldap->suffix}";
	
	if(!$this->ldap->ExistsDN("$dn")){
		$this->CreateAdministrator($admin_name,$password);
		return true;
	}
	
	$sr =@ldap_read($this->ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($this->ldap->ldap_connection,$sr);
		}	
	
	$old_password=$hash[0][strtolower("userPassword")][0];
	$uidNumber=$hash[0][strtolower("uidNumber")][0];
	$sock=new sockets();
	$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=".base64_encode($password))));
	if(preg_match('#(.+?):(.+)#',$datas,$re)){
		$sambaLMPassword=$re[1];
		$sambaNTPassword=$re[2];
	}
	if($password==null){$password=$old_password;}
		
			
		
	
	$group=new groups();
	
	$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('512');
	if($sambaPrimaryGroupSID==null){echo "Warning SID FOR 512 doesn't found...\n";}
	$sambaSID=$this->ldap->LOCAL_SID().'-'. (2 * $uidNumber * 1000);	
	writelogs("sambaPrimaryGroupSID = $sambaPrimaryGroupSID",__CLASS__.'/'.__FUNCTION__,__FILE__);
	writelogs("sambaSID = $sambaSID",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
$upd=array();
	$upd["sambaLMPassword"][0]=		$sambaLMPassword;
	$upd["sambaNTPassword"][0]=		$sambaNTPassword;
	$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
	$upd["userPassword"][0]		   =$password;
	$upd["sambaSID"][0]=			$sambaSID;
	$upd["gidNumber"][0]='512';
	
	$group=new groups(544);
	$group->AddUsertoThisGroup("administrator");
	writelogs("Editing groups...",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$group->EditSambaGroups();

	
	
	if(!$this->ldap->Ldap_modify($dn,$upd)){
		writelogs("Unable to edit administrator:$admin_name settings!!!...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		echo $this->ldap->ldap_last_error;
		exit;
	}
	$tpl=new templates();
	writelogs("Success edit administrator:$admin_name settings!!!...",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	$user=new user();
	$rootpassword=$user->GetRootPassword();	
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeAddUsersPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeBackupPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeDiskOperatorPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeMachineAccountPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SePrintOperatorPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeRemoteShutdownPrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeRestorePrivilege;$admin_name;grant");
	$sock->getfile("SetNetUsePrivs:$rootpassword;SeTakeOwnershipPrivilege;$admin_name;grant");
	
	
}

function CreateGuestUser(){
	$suffix=$this->ldap->suffix;
	$upd=array();
	$dn="cn=nobody,ou=users,dc=samba,dc=organizations,$suffix";
	if($this->ldap->ExistsDN($dn)){
		$this->ldap->ldap_delete($dn);
	}
					
	$dn="uid=nobody,ou=users,dc=samba,dc=organizations,$suffix";
	if(!$this->ldap->ExistsDN("$dn")){
		$u_ser=new user();
		$group=new groups(null);
		$upd["objectClass"][]='account';
		$upd["objectClass"][]='posixAccount';
		$upd["objectClass"][]='sambaSAMAccount';
		$uidNumber=$u_ser->uidNumberCheck(1);		
		$upd["uidNumber"][0]=$uidNumber;
		//$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('514');
		$sambaSID=$this->ldap->LOCAL_SID().'-'. (2 * $uidNumber * 1000);	
		$upd["sambaPrimaryGroupSID"][0]="S-1-5-2";
		$upd["sambaAcctFlags"][0]='[DU         ]';
		$upd["sambaSID"][0]=$sambaSID;
		$upd["loginshell"][0]='/bin/sh';
		$upd["uid"][0]='nobody';	
		$upd["homedirectory"][0]="/nonexistent";
		$upd["gidnumber"][0]="1997";
		$upd["displayname"][0]="nobody";
		$upd["uid"][0]="nobody";
		$upd["cn"][0]="nobody";		
		if(!$this->ldap->ldap_add($dn,$upd)){
			echo $this->ldap->ldap_last_error ."(line ".__LINE__.":".__CLASS__.'/'.__FUNCTION__.',' .__FILE__;
		}
		
	}

}





function CreateAdministrator($admin_name,$password){
	if(!$this->usersMenus->SAMBA_INSTALLED){return null;}
	$sock=new sockets();
	$dn="cn=$admin_name,ou=users,dc=samba,dc=organizations,{$this->ldap->suffix}";
	$create=false;	
		
			
	
	
	if(!$this->ldap->ExistsDN("$dn")){
				$u_ser=new user();
				$create=true;
				$upd=array();
				writelogs("Create administrator account",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='inetOrgPerson';
				$upd["objectClass"][]='sambaSAMAccount';
				$upd["objectClass"][]='posixAccount';
				$uidNumber=$u_ser->uidNumberCheck(1);
				$upd["uidNumber"][0]=$uidNumber;
				if($admin_name=="root"){$upd["homeDirectory"][]='/root';}else{$upd["homeDirectory"][]="/home/$admin_name";}
				$upd["cn"][]=$admin_name;
				$upd["sn"][]=$admin_name;
				$upd["uid"][]=$admin_name;
				}
				$sock=new sockets();
				$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=".base64_encode($password))));			
				if(preg_match('#(.+?):(.+)#',$datas,$re)){
					$sambaLMPassword=$re[1];
					$sambaNTPassword=$re[2];
					}
				writelogs("sambaLMPassword=$sambaLMPassword, sambaNTPassword=$sambaNTPassword",__CLASS__.'/'.__FUNCTION__,__FILE__);				
				
				
				$group=new groups();
				$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('512');
				$sambaSID=$this->ldap->LOCAL_SID().'-'. (2 * $uidNumber * 1000);
				
				//Samba
				$upd["sambaPwdLastSet"][0]=		time();
				$upd["sambaLogonTime"][0]=		'0';
				$upd["sambaLogoffTime"][0]=		'2147483647';
				$upd["sambaKickoffTime"][0]=	'2147483647';
				$upd["sambaPwdCanChange"][0]=	'1286597349';
				$upd["sambaPwdMustChange"][0]=	'2147483647';
				$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
				$upd["sambaLMPassword"][0]=		$sambaLMPassword;
				$upd["sambaNTPassword"][0]=		$sambaNTPassword;
				$upd["sambaAcctFlags"][0]=		'[UX         ]';
				$upd["sambaSID"][0]=			$sambaSID;
				
				writelogs("upd[\"sambaLMPassword\"][]={$upd["sambaLMPassword"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
				
				//posixAccount
				$upd["gidNumber"][0]='512';
				$upd["loginShell"][0]='/bin/sh';
				$upd["userPassword"][]=$password;
	

				
	
				if($create){
					if(!$this->ldap->ldap_add($dn,$upd)){
						echo $this->ldap->ldap_last_error;
					}
					
				}else{
						$group=new groups(544);
						$group->AddUsertoThisGroup("administrator");
						$group->EditSambaGroups();
					
					
				}
	
	
}



function createRootID($requested_password=null){
	$users=new usersMenus();
	$group=new groups();
	$ldap=new clladp();
	$sock=new sockets();
	if($requested_password==null){$requested_password=$ldap->ldap_password;}
	$dn="ou=users,dc=samba,dc=organizations,{$ldap->suffix}";
	if(!$ldap->ExistsDN($dn)){
		$this->verifyBranch();
		$gp=new groups();
		$gp->SambaGroupsBuild();
	}
		
	$sock->getFrameWork("services.php?nsswitch=yes");
	$dn="uid=root,ou=users,dc=samba,dc=organizations,{$ldap->suffix}";
	if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn);}
	
	
	$sock=new sockets();
	$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=". base64_encode($requested_password))));			
	if(preg_match('#(.+?):(.+)#',$datas,$re)){$sambaLMPassword=$re[1];$sambaNTPassword=$re[2];}	
	$upd=array();
	$sambaPrimaryGroupSID=$group->samba_group_sid_from_gid('512');
	$sambaSID=$this->ldap->LOCAL_SID().'-1000';
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='inetOrgPerson';
				$upd["objectClass"][]='sambaSAMAccount';
				$upd["objectClass"][]='posixAccount';
				$upd["uidNumber"][0]=0;
				$upd["gidNumber"][0]=0;	
				$upd["cn"][0]="root";
				$upd["uid"][0]="root";
				$upd["sn"][0]="root";
				$upd["loginShell"][0]='/bin/bash';
				$upd["homeDirectory"][0]='/root';
				$upd["sambaPwdLastSet"][0]=		time();
				$upd["sambaLogonTime"][0]=		'0';
				$upd["sambaLogoffTime"][0]=		'2147483647';
				$upd["sambaKickoffTime"][0]=	'2147483647';
				$upd["sambaPwdCanChange"][0]=	'1286597349';
				$upd["sambaPwdMustChange"][0]=	'2147483647';
				$upd["sambaPrimaryGroupSID"][0]=$sambaPrimaryGroupSID;
				$upd["sambaLMPassword"][0]=		$sambaLMPassword;
				$upd["sambaNTPassword"][0]=		$sambaNTPassword;
				$upd["sambaAcctFlags"][0]=		'[UX         ]';
				$upd["sambaSID"][0]=			$sambaSID;	
				$upd["userPassword"][0]=$requested_password;			
				
	if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error.":".__FUNCTION__.",\n".__FILE__."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("services.php?changeRootPasswd=yes&pass=".urlencode(base64_encode($requested_password)));
	
	
}

function ChangeSID($sid){
	$usffix=$this->ldap->suffix;
	$dn="sambaDomainName={$this->main_array["global"]["workgroup"]},$usffix";
	if(!$this->ldap->ExistsDN($dn)){
		echo "Unable to stat $dn\n";
	}
	$upd=array();
	$upd["sambaSID"][0]=$sid;
	$this->ldap->Ldap_modify($dn,$upd);
	$sock=new sockets();
	
	
	
	$dn="cn=nobody,ou=users,dc=samba,dc=organizations,$usffix";
	if($this->ldap->ExistsDN($dn)){
		$this->ldap->ldap_delete($dn);
		$this->CreateGuestUser();
	}
	
	$sock->getFrameWork("cmd.php?samba-change-sid=$sid");
	
}

	public function GetNetWorks($DOMAIN){
		$q=new mysql();
		$sql="SELECT * FROM samba_remote_announce WHERE hostname='master-samba-artica' ORDER BY ID DESC";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$n=array();
		if(!$q->ok){echo "$q->mysql_error\n";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["domain"]<>null){$n[]="{$ligne["ipaddr"]}/$DOMAIN";}else{$n[]=$ligne["ipaddr"];}
		}
		if(count($n)>0){return "remote announce = ". @implode(" ",$n)."\n";}
		
		$nets=$this->ldap->load_mynetworks();
		if(!is_array($nets)){return;}
		
		while (list ($index, $ipmask) = each ($nets) ){
			
			if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)#",$ipmask,$re)){
				if(($re[1]==127) && ($re[2]==0) && ($re[3]==0)){continue;}
				if($this->posix_getuid==0){echo "Starting......: Samba adding net {$re[1]}.{$re[2]}.{$re[3]}.255/$DOMAIN\n";}
				$n[]="{$re[1]}.{$re[2]}.{$re[3]}.255/$DOMAIN";
			}
			
			
		}
		if(is_array($n)){return "remote announce = ". @implode(" ",$n)."\n";}
		
	}

private function WINBINDD_PARAMS(){
	
	$sock=new sockets();
	if(!$this->WINBINDD_INSTALLED){
		if($this->posix_getuid==0){echo "Starting......: WINBINDD NOT INSTALLED\n";}
		return "\n#WINBINDD NOT INSTALLED\n";
	}
	
	$version=$this->SAMBA_VERSION;
	$upTo36=0;
	if(preg_match("#^([0-9]+)\.([0-9]+)#", $version,$re)){
		$major=intval($re[1]);
		$minor=intval($re[2]);
		if($major>=3){if($minor>=6){$upTo36=1;$upTo357=1;}}
	}	
	
	if($this->EnableSambaActiveDirectory==1){$this->DisableWinbindd=0;}
	if($this->posix_getuid==0){echo "Starting......: Enable Active Directory...: $this->EnableSambaActiveDirectory\n";}
	if($this->posix_getuid==0){echo "Starting......: WINBINDD disabled.........: $this->DisableWinbindd\n";}
	if($this->posix_getuid==0){echo "Starting......: Type of Samba.............: $this->TypeOfSamba\n";}
	if($this->posix_getuid==0){echo "Starting......: Kerberos enabled..........: $this->EnableKerbAuth\n";}
	if($this->posix_getuid==0){echo "Starting......: Up to 3.6.x...............: $upTo36\n";}
	
	
	
	
	if($this->EnableKerbAuth==1){
		$kerbArray=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));
		$domainUp=strtoupper($kerbArray["WINDOWS_DNS_SUFFIX"]);
		$conf[]="\tsecurity = ADS";
		$conf[]="\tpassword server=*";
		$conf[]="\trealm = $domainUp";
		$conf[]="\tkerberos method = system keytab";
		$conf[]="\tidmap config $domainUp : backend = ad";
		$conf[]="\tidmap config $domainUp : readonly = yes";
		$conf[]="#\tidmap config $domainUp : schema_mode = rfc2307";
		$conf[]="\tidmap config $domainUp : range = 1000-4000000000";
		$conf[]="\tidmap uid = 1000-4000000000";
		$conf[]="\tidmap gid = 1000-4000000000";
		$conf[]="\tclient use spnego = yes";
		$conf[]="\tencrypt passwords = yes";
		$conf[]="\twinbind separator = +";				
		$conf[]="\twinbind use default domain = yes";		
		$conf[]="\twinbind enum users = yes";
		$conf[]="\twinbind enum groups = yes";		
		return @implode("\n", $conf);
	}	
	
	
	
	$this->WinbinddChoose();
	
	if($this->DisableWinbindd==1){
		if($this->posix_getuid==0){echo "Starting......: WINBINDD is disabled\n";}
		return "\n#WINBINDD DISABLED\n";
		$sock->getFrameWork("cmd.php?winbindd-stop=yes");
		
	}
	
	if($this->EnableKerbAuth==1){$this->EnableSambaActiveDirectory=0;}
	if($this->EnableKerbAuth==1){$this->DisableWinbindd=0;}
	
	
	
	$conf[]="#WINBINDD *******************************************************";
	
	
	
	
	if($this->EnableSambaActiveDirectory==1){
		if($this->posix_getuid==0){echo "Starting......: Samba join domain {$this->config_activedirectory["ADDOMAIN"]}\n";}
		if($this->posix_getuid==0){echo "Starting......: Samba master server {$this->config_activedirectory["ADSERVER"]}.{$this->config_activedirectory["ADDOMAIN"]}\n";}	
		$WORKGROUP=strtoupper($this->config_activedirectory["WORKGROUP"]);
		$conf[]="\tsecurity = ADS";
		//$conf[]="\tpassword server=".strtolower("{$this->config_activedirectory["ADSERVER"]}.{$this->config_activedirectory["ADDOMAIN"]}");
		$conf[]="\trealm = ". strtoupper($this->config_activedirectory["ADDOMAIN"])."\n";
		$conf[]="\tidmap config $WORKGROUP:backend = rid";
		$conf[]="\tidmap config $WORKGROUP:read only = yes";
		//$conf[]="\tidmap config $WORKGROUP:schema_mode = rfc2307";
		$conf[]="\tidmap config $WORKGROUP:range = 60000-50000000";
		$conf[]="\tidmap config $WORKGROUP:base_rid = 0";

 
		
		if($upTo36==1){
			$conf[]="\tidmap config * : backend = tdb";
			//$conf[]="\tidmap config * : range = 16777216-33554431";
			$conf[]="\tidmap config * : range =  1000000-1999999";
			$conf[]="\tclient use spnego = No";
        	$conf[]="\tclient use spnego principal = No";
		}else{
			$conf[]="\tidmap gid = 16777216-33554431";
			$conf[]="\tidmap uid = 16777216-33554431";
			$conf[]="\tclient use spnego = Yes";
		}
		$conf[]="\tencrypt passwords = Yes";
		if($this->client_ntlmv2_auth==1){$conf[]="\tclient ntlmv2 auth = Yes";$conf[]="\tclient lanman auth = No";}
		if($this->client_ntlmv2_auth==0){$conf[]="\tclient ntlmv2 auth = No";$conf[]="\tclient lanman auth = Yes";}
		$conf[]="\twinbind normalize names = Yes";
		$conf[]="\twinbind separator = /";				
		$conf[]="\twinbind use default domain = No";
		$conf[]="\twinbind enum users = Yes";
		$conf[]="\twinbind enum groups = Yes";	
		$conf[]="\twinbind nested groups = Yes";	
		
		
		$conf[]="\twinbind nss info = rfc2307"; 
		$conf[]="\twinbind offline logon = true";
  		$conf[]="\twinbind cache time = 5";
   		$conf[]="\twinbind refresh tickets = true";
   		$conf[]="\tkerberos method = system keytab";
		$conf[]="\tallow trusted domains = Yes";	
		$conf[]="\tserver signing = mandatory";
		$conf[]="\tclient signing = mandatory";	
		$conf[]="\tlm announce = No";
		$conf[]="\tntlm auth = No";
		$conf[]="\tlanman auth = No";
		$conf[]="\tpreferred master = No";
		
		
		
		$conf[]="";
		return @implode("\n",$conf);	
	}
	

	
	

	
	$conf[]="\twinbind use default domain = yes";
	$conf[]="\twinbind enum users = yes";
	$conf[]="\twinbind enum groups = yes";
	$conf[]="\tpassword server=*";
	$conf[]="\tencrypt passwords = yes";
	$conf[]="\twinbind separator = /";			
	$conf[]="\twinbind uid = 10000-20000";
	$conf[]="\twinbind gid = 10000-20000";
	$conf[]="\twinbind enum users = yes";
	$conf[]="\twinbind enum groups = yes";
	$conf[]="\tsecurity = user";
	$conf[]="";
	return @implode("\n",$conf);
	
}

function CUPS(){
	if($this->posix_getuid==0){if(!is_file("/etc/printcap")){shell_exec("/bin/touch /etc/printcap");}}
	if(!$this->CUPS_INSTALLED){return "\nprinting = bsd\n";}
	if($this->EnablePrintersSharing==0){return "\nprinting = bsd\n";}
	$conf[]="\n";
	$conf[]="# printer  -----------------------------------\n";
	$conf[]="load printers = yes\n";
	$conf[]="printing = cups\n";
	$conf[]="printcap name = cups\n";
	$conf[]="\n";
	return @implode("\n",$conf);
	
}

private function netbios_aliases(){
	
	$sql="SELECT hostname,ou,workgroup FROM samba_hosts ORDER BY hostname";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(strlen($ligne["hostname"])>19){$ligne["hostname"]=substr($ligne["hostname"], 0,19);}
		$this->samba_aliases[]=$ligne["hostname"];
		
	}
	if($GLOBALS["AS_ROOT"]){echo "Starting......: Samba ".count($this->samba_aliases)." virtual servers\n";}
	if(count($this->samba_aliases)==0){return null;}
	return @implode(" ", $this->samba_aliases);
	
}

private function check36Spects(){
	$version=$this->SAMBA_VERSION;
	$upTo36=0;
	if(preg_match("#^([0-9]+)\.([0-9]+)#", $version,$re)){
		$major=intval($re[1]);
		$minor=intval($re[2]);
		if($major>=3){if($minor>=6){$upTo36=1;$upTo357=1;}}
	}
	
	if($upTo36==0){$this->EnableSMB2=0;return;}
	if(!is_numeric($this->EnableSMB2)){$this->EnableSMB2=1;}
	if(!is_numeric($this->client_ntlmv2_auth)){$this->client_ntlmv2_auth=1;}
	writelogs("Samba 3.6.x detected EnableSMB2 = $this->EnableSMB2 client_ntlmv2_auth=$this->client_ntlmv2_auth",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
	
	
	if($this->EnableSMB2==1){$conf[]="\tmax protocol = SMB2";}
	return @implode("\n", $conf);
	
}


function BuildConfig(){
			if($GLOBALS["VERBOSE"]){writelogs("Sarting build configuration...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$sock=new sockets();
			$this->WINBINDD_INSTALLED=$this->usersMenus->WINBINDD_INSTALLED;
			$this->CUPS_INSTALLED=$this->usersMenus->CUPS_INSTALLED;
			$this->BuildDefaults();
			$this->DisableWinbindd=$sock->GET_INFO('DisableWinbindd');
			
			$SambaNetBiosName=trim($sock->GET_INFO("SambaNetBiosName"));
			$SambaActAsWins=$sock->GET_INFO("SambaActAsWins");
			$SambaWinsServer=$sock->GET_INFO("SambaWinsServer");
			$SambaUsDNS=$sock->GET_INFO("SambaUsDNS");		
			if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$SambaWinsServer)){$SambaWinsServer="0.0.0.0";}
			if(!is_numeric($SambaUsDNS)){$SambaUsDNS=0;}
			if(!is_numeric($SambaActAsWins)){$SambaActAsWins=0;}
			if($SambaActAsWins==1){$SambaWinsServer="0.0.0.0";}
									
			$this->EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
			$this->config_activedirectory=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
			if($SambaNetBiosName<>null){$this->main_array["global"]["netbios name"]=$SambaNetBiosName;}
			if($this->main_array["global"]["netbios name"]==null){
				$users=new usersMenus();
				$hostname=$users->hostname;
				if(preg_match("#^(.+?)\.#", $hostname,$re)){$hostname=$re[1];}
				$this->main_array["global"]["netbios name"]=$hostname;
				$sock->SET_INFO("SambaNetBiosName", $hostname);
			}
			$netbiosname=$this->main_array["global"]["netbios name"];
			

			if($GLOBALS["VERBOSE"]){writelogs("[$netbiosname]: Checking aliases...",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			$netbios_aliases=$this->netbios_aliases();
			
			writelogs("log level=".$this->main_array["global"]["log level"],__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			
			if($this->main_array["global"]["log file"]==null){$this->main_array["global"]["log file"]="/var/log/samba/log.%m";}
			if($this->main_array["global"]["log level"]==null){$this->main_array["global"]["log level"]=0;}
			
			
			
			if(preg_match('#^(.+?)\.#',$this->main_array["global"]["netbios name"],$re)){
				$this->main_array["global"]["netbios name"]=$re[1];
			}
			$this->main_array["global"]["netbios name"]=strtolower($this->main_array["global"]["netbios name"]);
			if(strlen($this->main_array["global"]["netbios name"]>19)){$this->main_array["global"]["netbios name"]=substr($this->main_array["global"]["netbios name"],0,19);}
			
			$conf[]="[global]";
			
			if($this->EnableSambaVirtualsServers==1){
				if($netbios_aliases<>null){
					$this->main_array["global"]["disable netbios"]="no";
					if($this->posix_getuid==0){echo "Starting......: [$netbiosname]: Virtual servers is enabled\n";} 
					$conf[]="\tnetbios aliases =  {$this->main_array["global"]["netbios name"]} ". strtoupper($netbios_aliases)."";
					$conf[]="\tsmb ports = 139";
					$conf[]="\tinclude = /etc/samba/virtual/smb.conf.%L";
					if($this->posix_getuid==0){@mkdir("/etc/samba/virtual",644,true);}
					while (list ($Asnull, $sambaAlias) = each ($this->samba_aliases) ){
						if($this->posix_getuid==0){echo "Starting......: [$netbiosname]: Virtual server [$sambaAlias]\n";} 
						$smbalias=new samba_aliases($sambaAlias);
						$smbalias->build();
					}
				}
			}				
			
			
			$WORKGROUP=strtoupper($this->main_array["global"]["workgroup"]);
			if($this->EnableSambaActiveDirectory==1){
				$WORKGROUP=strtoupper($this->config_activedirectory["WORKGROUP"]);
			}
			
			if($this->EnableKerbAuth==1){
				$arrayKerberos=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));
				$WORKGROUP=$arrayKerberos["ADNETBIOSDOMAIN"];
			}
			
			
			
			$this->WORKGROUP=$WORKGROUP;
			$conf[]="\tworkgroup = $WORKGROUP";
			$conf[]="\tnetbios name = {$this->main_array["global"]["netbios name"]}";
			$conf[]="\tserver string = {$this->main_array["global"]["server string"]}";
			$conf[]="\tdisable netbios ={$this->main_array["global"]["disable netbios"]}";
			$conf[]=$this->check36Spects();
			$this->main_array["global"]["name resolve order"]=str_replace("dns","",$this->main_array["global"]["name resolve order"]);
			if($GLOBALS["VERBOSE"]){writelogs("[$netbiosname]: name resolve order ={$this->main_array["global"]["name resolve order"]}",__CLASS__,__FUNCTION__,__FILE__);}
			
			$conf[]="\tname resolve order ={$this->main_array["global"]["name resolve order"]}";
			if($SambaUsDNS==1){$conf[]="\tdns proxy = Yes";}else{$conf[]="\tdns proxy = No";}
			if($SambaActAsWins==1){
				$conf[]="\twins support = Yes";
				$conf[]="\twins hook = $this->PHP5_BIN /usr/share/artica-postfix/exec.samba.wins.php";
			}else{
				$conf[]="\twins support = No";
			
			}
			if($SambaWinsServer<>"0.0.0.0"){$conf[]="\twins server = $SambaWinsServer";$conf[]="\twins proxy = Yes";}			
			$conf[]="\tmin protocol = NT1";
			
			if($GLOBALS["AS_ROOT"]){
				$SECOND_PART=$this->SECOND_PART();
				$conf[]=$SECOND_PART;
				if($this->posix_getuid==0){echo "Starting......: [$netbiosname]: ". strlen($SECOND_PART)." bytes for second part\n";} 
			}else{
				$this->SECOND_PART();
			}
			
			$this->CleanAllDomains($this->main_array["global"]["workgroup"]);	
			reset($this->main_array);
			return @implode("\n", $conf);
	
}

private function SECOND_PART(){
			$sock=new sockets();
			
			$this->EnablePrintersSharing=$sock->GET_INFO("EnablePrintersSharing");
			if($this->EnablePrintersSharing==null){$this->EnablePrintersSharing=0;$sock->SET_INFO("EnablePrintersSharing",0);}
			if(!is_numeric($this->client_ntlmv2_auth)){$this->client_ntlmv2_auth=1;}
			$DisableSambaFileSharing=$sock->GET_INFO("DisableSambaFileSharing");
			$SambaRoamingEnabled=$sock->GET_INFO('SambaRoamingEnabled');
			$SambaEnableRemoteAnnounce=$sock->GET_INFO("SambaEnableRemoteAnnounce");

			
			$netbiosname=$this->main_array["global"]["netbios name"];
			
			if(!is_numeric($DisableSambaFileSharing)){$DisableSambaFileSharing=0;}
			if(!is_numeric($SambaRoamingEnabled)){$SambaRoamingEnabled=0;}
			if(!is_numeric($SambaEnableRemoteAnnounce)){$SambaEnableRemoteAnnounce=0;}

			
				
			if($GLOBALS["VERBOSE"]){writelogs("DisableSambaFileSharing.: [$netbiosname]: $DisableSambaFileSharing",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			if($GLOBALS["VERBOSE"]){writelogs("SambaRoamingEnabled.....: [$netbiosname]: $SambaRoamingEnabled",__CLASS__.'/'.__FUNCTION__,__FILE__);}
			
			
			if($DisableSambaFileSharing==1){$this->EnablePrintersSharing=0;}

			if($SambaRoamingEnabled==1){
				$this->main_array["global"]["domain logons"]="yes";
			}
			
			if($SambaEnableRemoteAnnounce==1){
				$conf[]=$this->GetNetWorks($this->main_array["global"]["workgroup"]);
			}
			
			
			if($DisableSambaFileSharing==1){
				if($this->posix_getuid==0){echo "Starting......: [$netbiosname]: Samba limit to lo, file sharing is disabled\n";} 
				$conf[]="#Samba only used for AD connection, no shares";
				$conf[]="\tinterfaces = lo";
				$conf[]="\tbind interfaces only = yes";
			}
			

			
			
			
			if($this->main_array["global"]["log level"]<1){$this->main_array["global"]["log level"]=1;}

			$conf[]="\tsyslog = 3";
			$conf[]="\tlog level = {$this->main_array["global"]["log level"]}";
			$conf[]="\tlog file = {$this->main_array["global"]["log file"]}";
			$conf[]="\tdebug timestamp = yes";
			$conf[]="";
			$conf[]="#\tEnable symbolics links -----------------------------------";
			$conf[]="\tfollow symlinks = yes";
			$conf[]="\twide links = yes";
			$conf[]="\tunix extensions = no";
			$conf[]="";
			$conf[]="\tusershare allow guests = no";
			$conf[]="\tusershare max shares = 100";
			$conf[]="\tusershare owner only = true";
			$conf[]="\tusershare path=/var/lib/samba/usershares/data";
			
			$conf[]="\n#Guest access";
			$conf[]="\tguest account = nobody";
			$conf[]="\tmap to guest = Bad Password";
			$conf[]="\ttemplate homedir = /home/%U";
	  		$conf[]="\ttemplate shell = /bin/false";
			$conf[]="\tenable privileges = yes";
			
			
			
			if($this->EnableSambaActiveDirectory<>1){
				$conf[]="\tdomain master = {$this->main_array["global"]["domain master"]}";
				$conf[]="\tlocal master = {$this->main_array["global"]["local master"]}";
				$conf[]="\tpreferred master = {$this->main_array["global"]["preferred master"]}";
				$conf[]="\tdomain logons = {$this->main_array["global"]["domain logons"]}";
			}
			
			$conf[]="\tos level = {$this->main_array["global"]["os level"]}";
			$conf[]="\tldap passwd sync = no";
			$conf[]="";
			
			if($GLOBALS["VERBOSE"]){writelogs("WINBINDD_PARAMS()",__CLASS__,__FUNCTION__,__FILE__);}
			$conf[]=$this->WINBINDD_PARAMS();
			$conf[]=$this->CUPS();
			
			
			if($GLOBALS["VERBOSE"]){writelogs("VISTA/Windows7 compatibility",__CLASS__,__FUNCTION__,__FILE__);}
			$conf[]="#\tVISTA/Windows7 compatibility";
			


        	
			if($GLOBALS["VERBOSE"]){writelogs("ACLs settings",__CLASS__,__FUNCTION__,__FILE__);}
        	$conf[]="#\tACLs settings";
        	$conf[]="\tnt acl support={$this->main_array["global"]["nt acl support"]}";
        	$conf[]="\tmap acl inherit={$this->main_array["global"]["map acl inherit"]}";
        	$conf[]="\tacl check permissions={$this->main_array["global"]["acl check permissions"]}";
        	$conf[]="\tinherit permissions={$this->main_array["global"]["inherit permissions"]}";
        	$conf[]="\tinherit acls={$this->main_array["global"]["inherit acls"]}";      	
        	$conf[]="\tacl map full control=yes";
        	$conf[]="\tdos filemode=yes";
        	$conf[]="\tforce unknown acl user = no";
			$conf[]="";			
			if($GLOBALS["VERBOSE"]){writelogs("LDAP_SETTINGS()",__CLASS__,__FUNCTION__,__FILE__);}
			$conf[]=$this->LDAP_SETTINGS();
			
			

			if($SambaRoamingEnabled==1){
				$conf[]="logon path = \\\\{$this->main_array["global"]["netbios name"]}\profile\%U";
				$conf[]="logon drive = P:";
				$conf[]="logon home = \\\\{$this->main_array["global"]["netbios name"]}\%U";
			}else{
				$conf[]="logon path =\"\"";
				$conf[]="logon home =\"\"";
				$conf[]="logon drive = \"\"";
			}
			

			$conf[]="\tsocket options = TCP_NODELAY IPTOS_LOWDELAY IPTOS_THROUGHPUT SO_KEEPALIVE SO_RCVBUF=8192 SO_SNDBUF=8192";
			$conf[]="\tcase sensitive = No";
			$conf[]="\tdefault case = lower";
			$conf[]="\tpreserve case = yes";
			$conf[]="\tshort preserve case = yes";
			$conf[]="#character set = iso8859-1";
			$conf[]="#domain admin group = @admin";
			$conf[]="\twins support = Yes";
			$conf[]="#hosts allow = 192.168.0. 127.";
			$conf[]="\ttime server = yes";
			$conf[]="#MDFS parameters";
			$conf[]="\tmsdfs root = no";
			$conf[]="\thost msdfs = no";
			
			$conf[]="";
			$conf[]="# Shared Folders lists -----------------------------------";
			unset($this->main_array["global"]);
			
			if(is_array($this->usb_array)){
				while (list ($uuid, $array) = each ($this->usb_array) ){
				$this->main_array[$array["name"]]["path"]="/opt/artica/usb_mount/$uuid";
				$this->main_array[$array["name"]]["create mask"]="0660";
				$this->main_array[$array["name"]]["directory mask"]="0770";
				$this->main_array[$array["name"]]["force user"]="root";
				$this->main_array[$array["name"]]["force group"]="root";
				}
			}
			
			
			unset($this->main_array["netlogon"]);
			unset($this->main_array["profile"]);
			unset($this->main_array["profiles"]);
			unset($this->main_array["profiles.V2"]);
			unset($this->main_array["homes"]);
			unset($this->main_array["printers"]);
			unset($this->main_array["print$"]);
			
			$GREYHOLE=$this->LOAD_GREYHOLE();
			$RECYCLES=$this->LOAD_RECYCLES_BIN();
			reset($this->main_array);
			while (list ($FOLDER, $ligne) = each ($this->main_array) ){
				
					if(is_array($ligne)){
						if($FOLDER==null){
							if($ligne["path"]==null){continue;}
							$FOLDER=basename($ligne["path"]);
						}
						if($this->posix_getuid==0){echo "[$netbiosname]: Starting......: Samba shared folder \"$FOLDER\"\n";} 					
						$conf[]="\n[$FOLDER]";
						if($GLOBALS["VERBOSE"]){writelogs("Folder............: $FOLDER",__CLASS__,__FUNCTION__,__FILE__);}
						$vfs_modules=explode(" ",$ligne["vfs object"]);
						while (list ($none, $vfs_modules_line) = each ($vfs_modules) ){$vfs_modules_line=trim($vfs_modules_line);if($vfs_modules_line==null){continue;}$vfs[$vfs_modules_line]=true;}
						unset($vfs["greyhole"]);
						unset($vfs["recycle:repository"]);
						unset($vfs["recycle:keeptree"]);
						unset($ligne["dfree command"]);
						if(is_numeric($GREYHOLE[$FOLDER])){
							if($this->posix_getuid==0){echo "Starting......: [$netbiosname]: Samba greyhole on folder $FOLDER\n";} 
							$vfs["greyhole"]=true;
							$ligne["dfree command"]="/usr/bin/greyhole-dfree";
						}
						
						unset($ligne["recycle:keeptree"]);	
						unset($ligne["recycle:versions"]);		
						unset($ligne["recycle:touch"]);	
						unset($ligne["recycle:exclude"]);		
						unset($ligne["recycle:exclude_dir"]);		
						unset($ligne["recycle:maxsize"]);	
						unset($ligne["recycle:noversions"]);						
						if($RECYCLES[$FOLDER]){
							if($this->posix_getuid==0){echo "Starting......: [$netbiosname]: Samba recycle bin enabled on folder $FOLDER\n";} 
							$ligne["recycle:repository"]=".RecycleBin$/%U";
							$ligne["recycle:keeptree"]="yes";	
							$ligne["recycle:versions"]="yes";
							$ligne["recycle:touch"]="no";
							$ligne["recycle:exclude"]="*.tmp|*.temp|*.obj|~\$*";	
							$ligne["recycle:exclude_dir"]="/tmp|/temp|/cache";		
							$ligne["recycle:maxsize"]="1073741824";
							$ligne["recycle:noversions"]="*.mdb";	
							$vfs["recycle:repository"]=true;	
							$vfs["recycle:keeptree"]=true;					
							
						}
						
						if(is_array($vfs)){
							while (list ($a, $b) = each ($vfs) ){$newvfs[]=$a;}
							$ligne["vfs object"]=@implode(" ",$newvfs);
						}

						if(isset($ligne["browsable"])){$ligne["browseable"]=$ligne["browsable"];}
						if(!isset($ligne["browseable"])){$ligne["browseable"]="Yes";$ligne["browsable"]="Yes";}
						while (list ($KEY, $VALS) = each ($ligne) ){
							if($KEY=="finduser"){continue;}
							if($KEY=="SaveUseridPrivileges"){continue;}
							if($KEY=="write_list"){continue;}
							if($KEY=="read_list"){continue;}
							if($KEY=="valid_users"){continue;}
							if($VALS==null){continue;}
							writelogs("Folder............: $FOLDER $KEY = $VALS",__CLASS__,__FUNCTION__,__FILE__);
							$conf[]="\t$KEY = $VALS";
						
						}
				}
			}
			
			
				$conf[]="[netlogon]";
				$conf[]="\tpath = /home/netlogon/";
				$conf[]="\twritable = No";
				$conf[]="\tbrowseable = No";
				$conf[]="\twrite list = root";
				
				
			
			$domain_logon=$this->main_array["global"]["domain logons"];
			if($GLOBALS["VERBOSE"]){writelogs("[$netbiosname]: Domain logon......: $domain_logon",__CLASS__,__FUNCTION__,__FILE__);}
			if(strtolower($domain_logon)=='yes'){
				if($SambaRoamingEnabled==1){
					$profile_path=$sock->GET_INFO('SambaProfilePath');
					if(trim($profile_path)==null){$profile_path="/home/export/profile";}
					$conf[]="[profiles]";
					$conf[]="\t#see USERENV.LOG in c:\windows\debug\UserMod on Windows XP for troubleshooting";
					$conf[]="\tpath = $profile_path";
					$conf[]="\toplocks = false";
					$conf[]="\tlevel2 oplocks = false";
					$conf[]="\tcsc policy = disable";
					$conf[]="\tbrowseable = No";
					$conf[]="\twriteable = Yes";
					$conf[]="\tread only = No";
					$conf[]="\tprofile acls = yes";
					$conf[]="\tcreate mask = 0700";
					$conf[]="\tdirectory mask = 0700";
					$conf[]="\tcomment = Roaming user profile storage";
					$conf[]="\thide files=/Desktop.ini/Thumbs.db/lost+found";
					$conf[]="";
					$conf[]="[profiles.V2]";
	        		$conf[]="\tcopy = profiles";
	        		$conf[]="\tbrowseable = no";
	        		$conf[]="\tcsc policy = disable";	        		
	        		$conf[]="";
					$conf[]="[profile]";
	        		$conf[]="\tcopy = profiles";
	        		$conf[]="\tbrowseable = no";	        		

				}
			}	
					#
				$conf[]="[homes]";
				$conf[]="\tcomment = Personnal Folder";
				$conf[]="\tbrowseable = No";
				$conf[]="\twriteable = Yes\n"; 
			
			
			if($this->usersMenus->CUPS_INSTALLED){
				if($this->EnablePrintersSharing==1){
				$conf[]="\n[printers]";
				$conf[]="\tcomment = Printers";
				$conf[]="\tbrowseable = yes";
				$conf[]="\tpath = /var/spool/samba";
				$conf[]="\tprintable = yes";
				$conf[]="\tpublic = yes";
				$conf[]="\tguest ok = yes";
				$conf[]="\twritable = no";
 				$conf[]="\tcreate mode = 0700\n";
				$conf[]="[print$]";
  				$conf[]="\tcomment = Printers drivers";
  				$conf[]="\tpath = /etc/samba/printer_drivers";
  				$conf[]="\tbrowseable = yes";
  				$conf[]="\tguest ok = no";
  				$conf[]="\tread only = yes";
  				$conf[]="\twrite list = root,administrator,@Administrators,@lpadmin";	
				}		
			}
			$this->SECOND_PART_CONF=@implode("\n", $conf);
			
			
			writelogs("compiling second part ". strlen($this->SECOND_PART_CONF)." bytes",__CLASS__,__FUNCTION__,__FILE__);
			$sock->SaveConfigFile($this->SECOND_PART_CONF, "SambaSecondPartConf");
			if($this->EnableSambaVirtualsServer==1){
				if($GLOBALS["VERBOSE"]){writelogs("[$netbiosname]: Virtual servers: ".count($this->samba_aliases)." server(s)",__CLASS__,__FUNCTION__,__FILE__);}
			
				if(count($this->samba_aliases)>0){
					if($GLOBALS["AS_ROOT"]){
						$unix=new unix();
						$hostname_bin=$unix->find_program("hostname");
						exec("$hostname_bin -s 2>&1",$results);
						$hostname_text=trim(@implode("", $results));
						$finale="[global]\n\tworkgroup =$this->WORKGROUP\n\tnetbios name = $netbiosname\n$this->SECOND_PART_CONF";
						echo "Starting......: [$netbiosname]: Samba also known has $hostname_text\n";
						echo "Starting......: [$netbiosname]: Samba processing /etc/samba/virtual/smb.conf.0.0.0.0\n";
						echo "Starting......: [$netbiosname]: Samba processing /etc/samba/virtual/smb.conf.". strtolower($hostname_text)."\n";
						
						echo "Starting......: [$netbiosname]: Samba processing /etc/samba/virtual/smb.conf.$netbiosname\n";
						@file_put_contents("/etc/samba/virtual/smb.conf.0.0.0.0",$finale);
						@file_put_contents("/etc/samba/virtual/smb.conf.$netbiosname", $finale);
						@file_put_contents("/etc/samba/virtual/smb.conf.$hostname_text", $finale);
						@file_put_contents("/etc/samba/virtual/smb.conf.". strtoupper($hostname_text), $finale);
					}
					return;
				}
		}
		
		return $this->SECOND_PART_CONF;
	
}

public function LOAD_RECYCLES_BIN(){
	$sql="SELECT sharename FROM samba_recycle_bin";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[$ligne["sharename"]]=true;
	}	
	
	return $f;
}


private function LOAD_GREYHOLE(){
	$sock=new sockets();
	
	if(!$this->usersMenus->GREYHOLE_INSTALLED){return array();}
	$EnableGreyhole=$sock->GET_INFO('EnableGreyhole');
	if(!is_numeric($EnableGreyhole)){$EnableGreyhole=1;}
	if($EnableGreyhole<>1){return array();}
	$sql="SELECT * FROM `greyhole_dirs` WHERE num_copies > 0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[$ligne["shared_dir"]]=$ligne["num_copies"];
	}
	
	return $f;
	
}

private function LDAP_SETTINGS(){
	
	
	$sock=new sockets();
	$EnableSambaRemoteLDAP=$sock->GET_INFO("EnableSambaRemoteLDAP");
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	if(!is_numeric($EnableSambaRemoteLDAP)){$EnableSambaRemoteLDAP=0;}
	
	$version=$this->SAMBA_VERSION;
	$upTo36=0;
	if(preg_match("#^([0-9]+)\.([0-9]+)#", $version,$re)){
		$major=intval($re[1]);
		$minor=intval($re[2]);
		if($major>=3){if($minor>=6){$upTo36=1;$upTo357=1;}}
	}	

	
	
	$ldap_admin_dn="cn={$this->ldap->ldap_admin},{$this->ldap->suffix}";
	$suffix= $this->ldap->suffix;
	$ldap_group_suffix="dc=organizations";
	$ldap_user_suffix="dc=organizations";
	$ldap_machine_suffix="ou=Computer,dc=samba,dc=organizations";
	$idmap_ldap_base_dn="ou=idmap,dc=samba,dc=organizations,{$this->ldap->suffix}";
	$ldap_host=$this->ldap->ldap_host;
	if($ldap_host=="localhost"){$ldap_host="127.0.0.1";}
	$usessl="off";
	$ldap_uri="ldap://{$this->ldap->ldap_host}:{$this->ldap->ldap_port}";
	
	if($this->EnableKerbAuth==1){
		$f[]="#LDAP disabled, EnableKerbAuth is enabled,connection to AD first";
		$f[]="\tidmap backend = ad\n";
		return @implode("\n",$f);		
		
	}
	
	if($EnableSambaActiveDirectory==1){
		$f[]="#connection to AD first";
		$f[]="\tidmap backend = ad\n";
		

		
		}
	
	
	if($EnableSambaRemoteLDAP==1){
				$SambaRemoteLDAPInfos=unserialize(base64_decode($sock->GET_INFO("SambaRemoteLDAPInfos")));
				$ldap_admin_dn=$SambaRemoteLDAPInfos["user_dn"];
				$suffix= $SambaRemoteLDAPInfos["suffix"];
				$ldap_group_suffix=$SambaRemoteLDAPInfos["ldap_group_suffix"];
				$ldap_user_suffix=$SambaRemoteLDAPInfos["ldap_user_suffix"];
				$ldap_machine_suffix=$SambaRemoteLDAPInfos["ldap_machine_suffix"];
				$idmap_ldap_base_dn="ou=idmap,$suffix";
				$ldap_host=$SambaRemoteLDAPInfos["server_host"];
				if($ldap_host=="localhost"){$ldap_host="127.0.0.1";}
				$usessl="no";
				if($SambaRemoteLDAPInfos["ssl"]==1){$usessl="yes";}
				$ldap_uri="ldap://{$SambaRemoteLDAPInfos["server_host"]}:{$SambaRemoteLDAPInfos["server_port"]}";
				}
	
				//if($ldap_host=="127.0.0.1"){$ldap_admin_dn=null;}
				$conf[]="";	
				$conf[]="# LDAP settings -----------------------------------";
				$conf[]="\tldap delete dn = no";
				$conf[]="\tpassdb backend = ldapsam:$ldap_uri";
				$conf[]="#scripts -----------------------------------";
				$conf[]="\tadd machine script = /usr/share/artica-postfix/bin/artica-install --samba-add-computer \"%u\"";				
				$conf[]="\tldap admin dn = $ldap_admin_dn";
				$conf[]="\tldap suffix = $suffix";
				$conf[]="\tldap group suffix = $ldap_group_suffix";
				$conf[]="\tldap user suffix =  $ldap_user_suffix";			
				$conf[]="\tldap machine suffix = $ldap_machine_suffix";
				$conf[]="\tldap delete dn = yes";
				$conf[]="\tldap ssl  = $usessl";	
				if($this->SambaEnableEditPosixExtension==1){
					$conf[]="\n\n#Samba and the Editposix/Trusted Ldapsam extension";
					$conf[]="\tldapsam:trusted=yes";
	  				$conf[]="\tldapsam:editposix=yes";
				}				
				$group=new groups(null);
				$group->BuildOrdinarySambaGroups();
				
				$conf[]="\tldap idmap suffix = $idmap_ldap_base_dn";
		if($EnableSambaActiveDirectory==0){				
				$conf[]="\tencrypt passwords = true";
				if($this->posix_getuid==0){echo "Starting......: Samba ID IMAP:$this->DisableWinbindd\n";} 
				if($this->DisableWinbindd==0){
				if($upTo36==1){	
					$conf[]="\tidmap config * : backend = ldap:$ldap_uri";
       				$conf[]="\tidmap config * : range = 1000-1999999";
				}else{
				    $conf[]="\tidmap backend = ldap:$ldap_uri";       						 
	                $conf[]="\tidmap uid = 1000-1999999";
	                $conf[]="\tidmap gid = 1000-1999999";
				}
	 				$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:backend = ldap";
		  			$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:readonly = no";
		    		$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:default = yes";
		    		$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:ldap_base_dn = $idmap_ldap_base_dn";
		    		$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:ldap_user_dn = $ldap_admin_dn";
		    		$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:ldap_url = $ldap_uri";
		    		$conf[]="\tidmap config {$this->main_array["global"]["workgroup"]}:range = 1000-1999999";
					$conf[]="\tidmap alloc backend = ldap";
		    		$conf[]="\tidmap alloc config:ldap_base_dn = $idmap_ldap_base_dn";
		    		$conf[]="\tidmap alloc config:ldap_user_dn = $ldap_admin_dn";
		    		$conf[]="\tidmap alloc config:ldap_url = $ldap_uri";
		    		$conf[]="\tidmap alloc config:range = 1000-1999999";
				}
				
		}	
						
	$conf[]="";
	return @implode("\n",$conf);
}

function CleanAllDomains($CurrentDomain){
	if($CurrentDomain==null){return null;}
	
	//WriteLogs("sambaDomainName Current domain \"$CurrentDomain\" against ".strtolower($CurrentDomain) . " and " .strtolower($this->main_array["global"]["netbios name"]) ,__CLASS__.'/'.__FUNCTION__,__FILE__);

	
	$upper=strtoupper($CurrentDomain);
	$baddn="sambaDomainName=$upper,dc=samba,dc=organizations,{$this->ldap->suffix}";
	
	WriteLogs("Check $baddn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	
	if($this->ldap->ExistsDN($baddn)){
		WriteLogs("Delete $baddn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->ldap->ldap_delete($baddn,true);
	}
	$group=new groups();
	
	
	if(!$group->SAMBA_ENABLED){
		WriteLogs("Samba is not installed/enabled" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		return true;
	}
	
	$group=new groups();
	$group->BuildOrdinarySambaGroups();
	if(strlen($CurrentDomain)==0){return null;}
	
	
	$hostname=$this->usersMenus->hostname;
	if(preg_match("#^(.+?)\.#",$hostname,$re)){$hostname=strtolower($re[1]);}	
	$query="(ObjectClass=sambaDomain)";
	$filter=array("dn","sambaDomainName");
	$h=$this->ldap->Ldap_search($this->ldap->suffix,$query,$filter);
	if(!is_array($h)){
		$this->CreateDomainName($CurrentDomain);
		return null;}

	for($i=0;$i<$h["count"];$i++){
		$domain=$h[$i][strtolower("sambaDomainName")][0];
		$domain=strtolower($domain);
		//WriteLogs("sambaDomainName found \"$domain\" against ".strtolower($CurrentDomain),__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($domain==strtolower($CurrentDomain)){
			continue;
		}
		if($domain==strtolower($this->main_array["global"]["netbios name"])){
			continue;
		}
		
		WriteLogs("sambaDomainName delete {$h[$i]["dn"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->ldap->ldap_delete($h[$i]["dn"],false);
		
	}
	
	$this->CreateDomainName($CurrentDomain);
	

	
}

function CreateDomainName($CurrentDomain){
	$group=new groups();
	if(!$group->SAMBA_ENABLED){WriteLogs("Samba is not installed/enabled" ,__CLASS__.'/'.__FUNCTION__,__FILE__);return true;}
	$upd=array();
	$CurrentDomain=strtoupper($CurrentDomain);
	
	
	$SID=$this->ldap->LOCAL_SID();
	$dn="sambaDomainName=$CurrentDomain,{$this->ldap->suffix}";
	if(!$this->ldap->ExistsDN($dn)){
		$upd["objectClass"][]="sambaDomain";
		$upd["sambaDomainName"][]="$CurrentDomain";
		$upd["sambaSID"][]="$SID";
		$upd["sambaAlgorithmicRidBase"][]="1000";
		$upd["sambaNextUserRid"][]="1000";
		$upd["sambaMinPwdLength"][]="5";
		$upd["sambaPwdHistoryLength"][]="0";
		$upd["sambaLogonToChgPwd"][]="0";
		$upd["sambaMaxPwdAge"][]="-1";
		$upd["sambaMinPwdAge"][]="0";
		$upd["sambaLockoutDuration"][]="30";
		$upd["sambaLockoutObservationWindow"][]="30";
		$upd["sambaLockoutThreshold"][]="0";
		$upd["sambaForceLogoff"][]="-1";
		$upd["sambaRefuseMachinePwdChange"][]="0";
		if(!$this->ldap->ldap_add($dn,$upd)){
			echo "CreateDomainName($CurrentDomain)\n$SID\n{$this->ldap->ldap_last_error}\n";
			exit;
		}
		else{
			$tpl=new templates();
			writelogs("sambaDomainName:Creating $CurrentDomain $SID success",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
	}
}


function hash_privileges($folder){
	$h=array();
	$ta=explode(',',$this->main_array[$folder]["valid users"]);
	if(is_array($ta)){
		while (list ($KEY, $VALS) = each ($ta) ){
			if(trim($VALS)<>null){
				$h[$VALS]["valid users"]='yes';
			}
		}
	}
	
$ta=explode(',',$this->main_array[$folder]["write list"]);
	if(is_array($ta)){
		while (list ($KEY, $VALS) = each ($ta) ){
			if(trim($VALS)<>null){
				$h[$VALS]["write list"]='yes';
			}
		}
	}

$ta=explode(',',$this->main_array[$folder]["read list"]);
	if(is_array($ta)){
		while (list ($KEY, $VALS) = each ($ta) ){
			if(trim($VALS)<>null){
				$h[$VALS]["read list"]='yes';
			}
		}
	}	
	
	return $h;
}

function GetUsrsRights($userid){
	
	include_once(dirname(__FILE__).'/class.user.inc');
	
	$folders=$this->main_shared_folders;
	
		while (list ($num, $folder_name) = each ($folders) ){
		if($folder_name=="print$"){continue;}
		if($folder_name=="printers"){continue;}
		if($folder_name=="netlogon"){continue;}
		if($folder_name=="homes"){continue;}
		if($folder_name=="profile"){continue;}
		if($folder_name=="profiles"){continue;}
		$res[$folder_name]=$num;
		}
		
	if(!is_array($res)){return array();}
	
	$user=new user($userid);
	$user_group=$user->GetGroups($userid);
	$folderallowed[$user->homeDirectory]=true;
	while (list ($foldername, $path) = each ($res) ){
		
		
		$hash=$this->hash_privileges($foldername);
		if(!is_array($hash)){
			$folderallowed[$path]=true;
			continue;
		}
		while (list ($userGroup, $rights) = each ($hash) ){	
			if(substr($userGroup,0,1)=='@'){
				if(is_numeric($user_group[substr($userGroup,1,strlen($userGroup))])){
					$folderallowed[$path]=true;
					continue;
				}
		}
			
			if($userGroup==$userid){
				$folderallowed[$path]=true;
				continue;
			}
			
		}
		
	}
	
	if(!is_array($folderallowed)){return array();}else{	return $folderallowed;}
	
}
	
	
	
}

function CheckSambaRights(){
	$user=new usersMenus();
	if($user->AsArticaAdministrator){return true;}
	if($user->AsSambaAdministrator){return true;}
	if($user->AsOrgStorageAdministrator){return true;}
	return false;
}
/*
 * 
 * A voir :inherit permissions
 * A voir : hide unwriteable files
 * a voir les chown et les acls.
 * 
 cat /var/cache/samba/browse.dat
 Configuring the Windows XP Client:

Notes : Only Windows XP-Professional Edition can join the Domain, it does not work for WindowsXP-Home Edition.

STEPS:

1) Make sure that the workstation belonged to the same workgroup as the server and have a fixed IP address and hostname assigned.

2) Change the registry entry, run the command regedt32 and do the below
a) RequireSignOrSeal Registry hack

HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\netlogon\parameters
"RequireSignOrSeal"=dword:00000000

b) Use the Registry Editor and edit the
HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\System\CompatibleRUPSecurity to have the DWORD value of 1

3) Use the Group Policy Editor (gpedit.msc) and enable "Computer Configuration\Administrative Templates\System\User Profiles\Do not check for user ownership of Roaming Profile Folders".

4) Go to MyComputer right click Properties. Go to Change and click on Domain and enter the domain-name you want to join. When joining the domain for the First time enter userid as root and give the samba password. Make sure there is an entry for the root in the smbpasswd (samba password) file.

5) Reboot and then the changes will be effective.
http://wiki.samba.org/index.php/Samba4/Andrew_and_Jelmers_Fantasy_Page 
*/
?>