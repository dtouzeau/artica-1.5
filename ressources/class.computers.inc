<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.groups.inc');
include_once(dirname(__FILE__).'/class.user.inc');
include_once(dirname(__FILE__).'/class.bind9.inc');
include_once(dirname(__FILE__).'/class.crypt.php');
include_once(dirname(__FILE__).'/class.dhcpd.inc');
include_once(dirname(__FILE__).'/class.ocs.inc');
include_once(dirname(__FILE__) . '/class.pdns.inc');


class computers{
	var $uid;
	var $dn;
	var $local_sid;
	var $samba_installed=false;
	var $objectClass_array=array();
	var $homeDirectory;
	var $gecos;
	var $shadowMax;
	var $shadowLastChange;
	var $loginShell;
	var $description;
	var $sambaAcctFlags="[W          ]";
	var $sambaLogonTime=0;
	var $sambaLogoffTime=2147483647;
	var $sambaKickoffTime=2147483647;
	var $sambaPwdCanChange=0;
	var $gidNumber=515;
	var $sambaPwdMustChange=2147483647;
	var $sambaPrimaryGroupSID;
	var $uidNumber;
	var $sambaSID;
	var $ComputerIP;
	var $ComputerOpenPorts;
	var $ComputerOS;
	var $ComputerUpTime;
	var $ComputerMachineType;
	var $ComputerRunning;
	var $ComputerMacAddress;
	var $ComputerHopCount;
	var $ldap_error;
	var $DnsType;
	var $DnsZoneName;
	var $DNSCname=array();
	var $ComputerCPU;
	var $DnsMXLength;
	var $ComputerCryptedInfos;
	var $ou;
	var $DisplayName;
	var $EnableBackupAccount;
	var $ComputerRealName;
	var $dhcpHWAddress;
	var $dhcpStatements;
	var $dhcpfixed=0;
	var $KasperkyAVScanningDatas=null;
	var $VolatileIPAddress=1;
	var $ComputerScanSchedule;
	var $userPassword;
	var $sambaLMPassword;
	var $sambaNTPassword;
	
function computers($uid=null){
	    $uid=str_replace('$$','$',$uid);
		$users=new usersMenus();
		$ldap=new clladp();
		$this->samba_installed=$users->SAMBA_INSTALLED;
		$this->local_sid=$ldap->LOCAL_SID();
		
		
		
		if($uid<>null){
			$this->uid=$uid;
			$this->LoadDatas();
			$this->DisplayName=strtoupper($uid);
			$this->DisplayName=substr($this->DisplayName,0,strlen($this->DisplayName)-1);	
			$this->ChangeDisplayName();
			}
			
			

	}
	
function ChangeDisplayName(){
	if($this->ComputerIP<>null){
		$tmp=@gethostbyaddr($this->ComputerIP);
		if($tmp<>null){
			if($tmp<>$this->ComputerIP){$this->DisplayName=$tmp;}
		}
	}	
	
}
	
	
private function LoadDatas(){
	$ldap=new clladp();
		
		if($this->uid==null){
			writelogs("Resquest an uid null, aborting",__CLASS__.'/'.__FUNCTION__,__FILE__);return null;}
		
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(uid=$this->uid)");
		if(!$sr){
			writelogs("Unable to find : $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( 'INFOS: bad value $entry_id: (' . $entry_id . ')  find: (uid=' . $this->uid . ') -> aborting function search engine doesn`t found the pattern',__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
		$this->dn=$ldap->_Get_dn_userid($this->uid);
		//writelogs("DN $this->uid: {$this->dn}",__CLASS__.'/'.__FUNCTION__,__FILE__);
		for($i=0;$i<$attrs["objectClass"]["count"];$i++){
			$this->objectClass_array[$attrs["objectClass"][$i]]=$attrs["objectClass"][$i];
			
		}
		
		
		$this->cn=$attrs["cn"][0];
		$this->homeDirectory=$attrs["homeDirectory"][0];
		$this->gecos=$attrs["gecos"][0];
		$this->shadowMax=$attrs["shadowMax"][0];
		$this->shadowLastChange=$attrs["shadowLastChange"][0];
		$this->loginShell=$attrs["loginShell"][0];
		$this->description=$attrs["description"][0];
		
		$this->sambaAcctFlags=$attrs["sambaAcctFlags"][0];
		$this->sambaLogonTime=$attrs["sambaLogonTime"][0];
		$this->sambaLogoffTime=$attrs["sambaLogoffTime"][0];
		$this->sambaKickoffTime=$attrs["sambaKickoffTime"][0];
		$this->sambaPwdCanChange=$attrs["sambaPwdCanChange"][0];
		$this->gidNumber=$attrs["gidNumber"][0];
		$this->sambaPwdMustChange=$attrs["sambaPwdMustChange"][0];
		$this->uidNumber=$attrs["uidNumber"][0];
		$this->sambaSID=$attrs["sambaSID"][0];
		$this->ComputerIP=$attrs["ComputerIP"][0];
		$this->ComputerOpenPorts=$attrs["ComputerOpenPorts"][0];								
		$this->ComputerOS=$attrs["ComputerOS"][0];			
		$this->ComputerUpTime=$attrs["ComputerUpTime"][0];			
		$this->ComputerMachineType=$attrs["ComputerMachineType"][0];					
		$this->ComputerRunning=$attrs["ComputerRunning"][0];
		$this->ComputerMacAddress=$attrs["ComputerMacAddress"][0];
		$this->ComputerHopCount=$attrs["ComputerHopCount"][0];
		$this->ComputerCPU=$attrs["ComputerCPU"][0];
		$this->DnsMXLength=$attrs["DnsMXLength"][0];
		$this->ComputerCryptedInfos=$attrs["ComputerCryptedInfos"][0];
		$this->KasperkyAVScanningDatas=$attrs["KasperkyAVScanningDatas"][0];
		$this->VolatileIPAddress=$attrs['VolatileIPAddress'][0];
		$this->ComputerScanSchedule=$attrs['ComputerScanSchedule'][0];
		if($this->VolatileIPAddress==null){$this->VolatileIPAddress=1;}
		
		
		$this->userPassword=$attrs['userPassword'][0];
		$this->sambaLMPassword=$attrs['sambaLMPassword'][0];
		$this->sambaNTPassword=$attrs['sambaNTPassword'][0];
		
		
		$dhcp=new dhcpd_hosts($this->uid);
		$this->dhcpHWAddress=$dhcp->dhcpHWAddress;
		$this->dhcpStatements=$dhcp->dhcpStatements;
	
		
		
		if(($this->dhcpHWAddress<>null) && ($this->dhcpStatements<>null)){
			if(preg_match("#fixed-address#",$this->dhcpStatements)){
				$this->dhcpfixed=1;
			}
			
		}
		if($this->dhcpfixed==1){$this->VolatileIPAddress=0;}
		
		if(substr($this->uid,strlen($this->uid)-1,1)=='$'){$this->ComputerRealName=substr($this->uid,0,strlen($this->uid)-1);}
		else{$this->ComputerRealName=$this->uid;}		
		
		if($this->VolatileIPAddress==1){
			$ip=gethostbyname($this->ComputerRealName);
			if($ip<>null){
				if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ip)){$this->ComputerIP=$ip;}
			
			}
		}		
		
		
		$this->DecryptCryptedInfos();
		$this->EnableBackupAccount=$attrs["EnableBackupAccount"][0];
		$this->ou="Computer";

		
		if($this->ComputerIP==null){
			$this->ComputerIP=$this->fixed_gethostbyname($this->ComputerRealName);
		}		
		
		//DNS
		$this->DnsType=$attrs["DnsType"][0];
		$this->DnsZoneName=$attrs["DnsZoneName"][0];
		
		for($i=0;$i<$attrs["DnsCname"]["count"];$i++){
			$this->DNSCname[$attrs["DnsCname"]["$i"]]=$attrs["DnsCname"]["$i"];
		}
		
		
		$this->OcsPasserelle();
		
		}
		
private function SambaLoadpasswd(){
		$sock=new sockets();
		$datas=trim(base64_decode($sock->getFrameWork("cmd.php?smbpass=".base64_encode($this->userPassword))));
			
		
		if(preg_match('#([0-9A-Z]+):([0-9A-Z]+)#',$datas,$re)){
			$this->sambaLMPassword=$re[1];
			$this->sambaNTPassword=$re[2];
			writelogs("$this->userPassword= $datas=sambaLMPassword:$this->sambaLMPassword sambaNTPassword:$this->sambaNTPassword",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
	}

public function ComputerChangePassword($password){
		    $upd=array();
			$this->userPassword=$password;
			$this->SambaLoadpasswd();
			$upd["sambaLMPassword"][0]=$this->sambaLMPassword;
			$upd["sambaNTPassword"][0]=$this->sambaNTPassword;
			$upd["userPassword"][0]=$this->userPassword;
			$upd["sambaPwdLastSet"][0]=time();
			$upd["sambaPwdMustChange"][0]=2147483647;
			$upd["sambaBadPasswordCount"][0]=0;
			$upd["sambaBadPasswordTime"][0]=0;
			$upd["sambaLogonHours"][0]=-1;		
			$ldap=new clladp();
			$res=$ldap->Ldap_modify($this->dn,$upd);
			$this->ldap_error=$ldap->ldap_last_error;
			$this->artica_meta();
			return $res;
	
}
		
		
function OcsPasserelle(){
	if($this->ComputerMacAddress=="UnKnown"){return null;}
	if($this->ComputerMacAddress==null){return null;}
	$ocs=new ocs($this->ComputerMacAddress);
	if($ocs->HARDWARE_ID==0){return null;}
	writelogs("OcsPasserelle: HARDWARE_ID=$ocs->HARDWARE_ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$ocs->LoadParams();
	$COmputerName=$this->uid;
	$COmputerName=str_replace('$','',$COmputerName);
	
	$update=false;
	if($this->VolatileIPAddress==0){
		if($ocs->ComputerIP<>null){
			if($ocs->ComputerIP<>$this->ComputerIP){$this->ComputerIP=$ocs->ComputerIP;$update=true;}
		}
	}

	if($ocs->ComputerName<>null){
	if($COmputerName<>$ocs->ComputerName){
		//writelogs("OcsPasserelle: $COmputerName <>$ocs->ComputerName",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->uid=$ocs->ComputerName.'$';$update=true;}
	}
	
	if($update){$this->Edit();}
	
	
}
		
		
function Edit(){
	$upd["uid"][0]=$this->uid;
	$uidNumber=$this->uidNumber;
	$uidNumber_c=2 *$uidNumber+1000;
	$users=new usersMenus();
	$group=new groups();
	$ldap=new clladp();
	$this->VerifyDefault();
	if($this->dhcpfixed==1){$this->VolatileIPAddress=0;}
	if($this->DnsMXLength==null){$this->DnsMXLength=10;}
	
	writelogs("ComputerIP: $this->ComputerIP",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$upd=array();
	$this->ComputerIP=str_replace("$",'',$this->ComputerIP);
	$upd["ComputerIP"][0]=$this->ComputerIP;
	$upd["ComputerMacAddress"][0]=$this->ComputerMacAddress;
	$upd["DnsZoneName"][0]=$this->DnsZoneName;
	$upd["DnsType"][0]=$this->DnsType;
	$upd["ComputerCPU"][0]=$this->ComputerCPU;	
	$upd["DnsMXLength"][0]=$this->DnsMXLength;
	$upd["sambaPrimaryGroupSID"][0]=$group->samba_group_sid_from_gid('515');
	$upd["sambaSID"][0]=$ldap->LOCAL_SID() .'-' . $uidNumber_c;	
	$upd["VolatileIPAddress"][0]=$this->VolatileIPAddress;
	$ldap=new clladp();
	$res=true;
	
	if(!$ldap->Ldap_modify($this->dn,$upd)){
		writelogs("1) Modify DN $this->dn ERROR",__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("VolatileIPAddress=$this->VolatileIPAddress",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$res=FALSE;
		
	}
	unset($upd);	
	
	
	
	$this->ComputerOS=replace_accents($this->ComputerOS);
	if(preg_match("#Microsoft.+?Windows.+?7#i",$this->ComputerOS)){$this->ComputerOS="Windows 7";}

	if($this->ComputerOpenPorts<>null){		$upd["ComputerOpenPorts"][0]=$this->ComputerOpenPorts;}
	if($this->ComputerOS<>null){			$upd["ComputerOS"][0]=$this->ComputerOS;}
	if($this->ComputerUpTime<>null){		$upd["ComputerUpTime"][0]=$this->ComputerUpTime;}
	if($this->ComputerMachineType<>null){	$upd["ComputerMachineType"][0]=$this->ComputerMachineType;}
	if($this->ComputerRunning<>null){		$upd["ComputerRunning"][0]=$this->ComputerRunning;}
	if($this->ComputerMacAddress<>null){	$upd["ComputerMacAddress"][0]=$this->ComputerMacAddress;}
	if($this->ComputerHopCount<>null){		$upd["ComputerHopCount"][0]=$this->ComputerHopCount;}
	

	if($this->VolatileIPAddress==0){
		$dns=new pdns($this->DnsZoneName);
		$dns->EditIPName(strtolower($this->uid),$this->ComputerIP,$this->DnsType,$this->ComputerMacAddress);
	}
	
	$ldap=new clladp();
	
	$this->EXPLODE_LDAP_ARRAY($upd);
	
	$this->SaveDHCPMode();
	$sock=new sockets();
	$sock->getfile("SetComputerBackupSchedule");
	$this->artica_meta();	
	return $res;
	
	}
	
private function EXPLODE_LDAP_ARRAY($array){
	$ldap=new clladp();
	$upd=array();
	while (list ($num, $arr) = each ($array) ){
		while (list ($i, $val) = each ($arr) ){
			$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="$num: $val";
			unset($upd);
			$upd[trim($num)][0]=trim($val);
			if(!$ldap->Ldap_modify($this->dn,$upd)){
				$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="$num = $val (error)";
				writelogs("modify $num = $val ERROR",__CLASS__.'/Edit/EXPLODE_LDAP_ARRAY',__FILE__,__LINE__);
				$newarr[$num]=$val;
				if(!$ldap->Ldap_add_mod($this->dn,$newarr)){
						$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="$num = $val (error)";
						writelogs("Ldap_add_mod $num = $val ERROR",__CLASS__.'/Edit/EXPLODE_LDAP_ARRAY',__FILE__,__LINE__);
					
				}
			}
			
			unset($upd);
			//writelogs("\$upd[\"$num\"][$i]=$val",__CLASS__.'/'.__FUNCTION__,__FILE__);
	}}
	
}
	
function SaveDHCPMode(){
	//writelogs("dhcpfixed: $this->dhcpfixed for $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__);
	if(($this->dhcpfixed==1) && ($this->ComputerIP<>null) && ($this->ComputerMacAddress<>null)){
			include_once(dirname(__FILE__.'/class.dhcpd.inc'));
			$dhcp=new dhcpd_hosts($this->uid);
			$dhcp->MAC=$this->ComputerMacAddress;
			$dhcp->IP=$this->ComputerIP;
			$dhcp->AddFixed();
			writelogs("Requested to fix the dhcp computer $this->uid=$this->ComputerIP",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?apply-dhcpd=yes");
			
		}

		if($this->dhcpfixed==0){
			include_once(dirname(__FILE__.'/class.dhcpd.inc'));
			$dhcp=new dhcpd(1);
			$dhcp->DeleteFixed($this->uid);
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?apply-dhcpd=yes");			
		}
}
	
	
function VerifyDefault(){
	$u=array();
	if($this->DnsType==null){$this->DnsType="A";}
	if($this->DnsZoneName==null){$this->DnsZoneName="localhost";}
	if($this->ComputerIP==null){
		$resolved=@gethostbyname($this->uid);
		if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$resolved)){
			writelogs("gethostbyname($this->uid)=$resolved",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->ComputerIP=$resolved;
		}
	}
	
	if($this->ComputerIP==null){$this->ComputerIP='0.0.0.0';}
	if($this->ComputerMacAddress==null){$this->ComputerMacAddress='Unknown';}
	if($this->ComputerOS==null){$this->ComputerOS="Unknown";}
	if($this->ComputerCPU==null){$this->ComputerCPU="Unknown";}
	if(strpos($this->uid,'$')==0){$this->uid=$this->uid.'$';}
	$ldap=new clladp();
	$dn="dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organization";
	 			$u["objectClass"][]="dcObject";
	 			$u["o"][]="samba";
	 			$u["dc"][]="samba";
	 			if(!$ldap->ldap_add($dn,$u)){
	 				echo "Failed\n$ldap->ldap_last_error\nLine: ".__LINE__." of ". __FILE__."\n";
	 				return null;}
	 			unset($u);
	 		}

		$dn="ou=Computer,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organizationalUnit";
	 			$u["ou"][]="Computer";
	 			if(!$ldap->ldap_add($dn,$u)){
	 				echo "Failed\n$ldap->ldap_last_error\nLine: ".__LINE__." of ". __FILE__."\n";
	 				return null;}
	 			unset($u);
	 			}
	 		
	}
	
	
	
	


function DecryptCryptedInfos(){
	if($this->ComputerCryptedInfos==null){return null;}
	$sock=new sockets();
	$cryptPass=$sock->GET_INFO("OrigninalCryptPassword");
	$crypt=new SimpleCrypt($cryptPass);
	$this->ComputerCryptedInfos=$crypt->decrypt($this->ComputerCryptedInfos);
	
	
}

function fixed_gethostbyname ($host) {
    $ip = @gethostbyname(trim($host));
    if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ip)){
    	if ($ip != $host) { return $ip; } else return null;
    }
}

function SaveCryptedInfos(){
	$sock=new sockets();
	$ldap=new clladp();
	
	if($this->dn==null){
		$this->dn="cn=$this->uid,ou=Computer,dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($this->dn)){$this->Add();}
	}
	
	$cryptPass=$sock->GET_INFO("OrigninalCryptPassword");
	if($cryptPass==null){
		$cryptPass=$ldap->ldap_password;
		$sock->SET_INFO("OrigninalCryptPassword",$cryptPass);
		}
		
	$array=array();
	$crypt=new SimpleCrypt($cryptPass);
	$array["ComputerCryptedInfos"][0]=$crypt->encrypt($this->ComputerCryptedInfos);
	
	if(!$ldap->Ldap_modify($this->dn,$array)){
				echo "\n".__FUNCTION__."\n".__FILE__."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
				return false;
			}
		
	$sock=new sockets();
	$sock->getfile("SetComputerBackupSchedule");	
	$this->artica_meta();	
	return true;
	
}


function ComputerIDFromMAC($mac){
	$pattern="(&(objectclass=posixAccount)(ComputerMacAddress=$mac))";
	$attr=array();
	$ldap=new clladp();
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if($hash[0]["uid"][0]<>null){return $hash[0]["uid"][0];}
	
	$mac=str_replace(':',"-",$mac);
	$pattern="(&(objectclass=posixAccount)(ComputerMacAddress=$mac))";
	$attr=array();
	$ldap=new clladp();
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if($hash[0]["uid"][0]<>null){return trim($hash[0]["uid"][0]);}	
	
	$mac=str_replace('-',":",$mac);
	$pattern="(&(objectclass=posixAccount)(ComputerMacAddress=$mac))";
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if($hash[0]["uid"][0]<>null){return trim($hash[0]["uid"][0]);}		
	
	
	
}

function Edit_ScanSchedule($cron){
	writelogs("Set Antivirus scanner AT \"$cron\" for computer in $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$ldap=new clladp();
	if($cron==null){
		$upd["ComputerScanSchedule"]=$this->ComputerScanSchedule;
		if($ldap->Ldap_del_mod($this->dn,$upd)){$this->artica_meta();}
	}else{
		$upd["ComputerScanSchedule"][0]=$cron;
		if($ldap->Ldap_modify($this->dn,$upd)){$this->artica_meta();}
	}
	
	
}


function ComputerAddAlias($alias){
	$ldap=new clladp();
	writelogs("adding computer aliase $alias in $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$upd["DnsCname"]=$alias;
	if($ldap->Ldap_add_mod($this->dn,$upd)){$this->artica_meta();}
	}
function ComputerDelAlias($alias){
$ldap=new clladp();
	writelogs("delete computer aliase $alias in $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$upd["DnsCname"]=$alias;
	$ldap->Ldap_del_mod($this->dn,$upd);
	$this->artica_meta();	
}


	
function Add(){
	$this->ComputerIP=str_replace("$",'',$this->ComputerIP);
	if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$this->ComputerIP)){$this->ComputerIP=null;}
	$this->uid=str_replace(" ","",$this->uid);
	if($this->ComputerMacAddress<>null){
		if($this->ComputerMacAddress<>"Unknown"){
			$uid_mac=$this->ComputerIDFromMAC($this->ComputerMacAddress);
			if(trim($uid_mac)<>null){
					writelogs("$this->ComputerMacAddress= $uid_mac, change to this uid",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$this->uid=$uid_mac;
			}
		}
		
	}
	
	$this->VerifyDefault();
	$ldap=new  clladp();
	
	
	$this->dn="cn=$this->uid,ou=Computer,dc=samba,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($this->dn)){
		$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="LDAP::$this->dn exists -> Edit()";
		return $this->Edit();
	}
		$group=new  groups(null);
	
		$upd=array();
		$user=new user(null);
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="ArticaComputerInfos";
		$upd["objectClass"][]="posixAccount";
		$upd["objectClass"][]="sambaSamAccount";
		$upd["objectClass"][]="shadowAccount";
		$upd["cn"][]=$this->uid;
		$upd["uid"][]=$this->uid;
		$upd["homeDirectory"][]="$this->uid/tmp";
		$upd["gecos"][]="Computer";
		$upd["shadowMax"][]="20000";
		$upd["shadowLastChange"][]="-1";
		$upd["loginShell"][]="/bin/false";
		$upd["description"][]="Computer";
		$upd["sambaAcctFlags"][]="[W          ]";
		$upd["sambaLogonTime"][]="0";
		$upd["sambaLogoffTime"][]="2147483647";
		$upd["sambaKickoffTime"][]="2147483647";
		$upd["sambaPwdCanChange"][]="0";
		$upd["gidNumber"][]="515";
		$upd["sambaPwdMustChange"][]="2147483647";
		$upd["sambaPrimaryGroupSID"][]=$group->samba_group_sid_from_gid('515');
		$upd["uidNumber"][]=$user->uidNumberCheck(1);
		$upd["sambaSID"][]=$ldap->LOCAL_SID() .'-' . $user->SambaGetSid();
		
		if($this->ComputerIP==null){$this->ComputerIP='0.0.0.0';}
		if($this->ComputerMachineType==null){$this->ComputerMachineType='UnKnown';}
		if($this->ComputerRunning==null){$this->ComputerRunning='UnKnown';}
		if($this->ComputerOS==null){$this->ComputerOS='UnKnown';}
		if($this->ComputerRealName==null){$this->ComputerRealName=str_replace("$","",$this->uid);}
		
		$upd["ComputerIP"][]=$this->ComputerIP;
		$upd["ComputerOpenPorts"][]="UnKnown";
		$upd["ComputerOS"][]=$this->ComputerOS;
		$upd["ComputerUpTime"][]="UnKnown";
		$upd["ComputerMachineType"][]=$this->ComputerMachineType;
		$upd["ComputerRunning"][]=$this->ComputerRunning;
		$upd["ComputerMacAddress"][]=$this->ComputerMacAddress;
		$upd["ComputerHopCount"][]="UnKnown";	
		$upd["ComputerCPU"]=$this->ComputerCPU;
		$upd["VolatileIPAddress"]=$this->VolatileIPAddress;
		
		//DNS
		$upd["DnsZoneName"][]=$this->DnsZoneName;
		$upd["DnsType"][]=$this->DnsType;
		
		writelogs("adding computer $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$res=$ldap->ldap_add($this->dn,$upd);
		if(!$res){
			writelogs("adding computer $this->dn failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
				
		}else{
			$this->SaveDHCPMode();
			if($this->ComputerIP<>'0.0.0.0'){
				$sock=new sockets();
				$sock->getFrameWork('cmd.php?apply-bind=yes');
				$this->artica_meta();
			}
		}
		
		$this->ldap_error=$ldap->ldap_last_error;
		return $res;
		}
		
	private function artica_meta(){
		$sock=new sockets();
		$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
		if($ArticaMetaEnabled==1){
			$uid_am=str_replace("$","",$this->uid);
			$sock->getFrameWork("cmd.php?artica-meta-computer=$uid_am");
		}
	}		
		
function UpdateComputerSID(){
		$group=new  groups(null);
		$ldap=new clladp();
		$user=new user(null);
		$upd=array();
		$this->dn="cn=$this->uid,ou=Computer,dc=samba,dc=organizations,$ldap->suffix";
		$upd["sambaPrimaryGroupSID"][]=$group->samba_group_sid_from_gid('515');
		$upd["uidNumber"][]=$user->uidNumberCheck(1);
		$upd["sambaSID"][]=$ldap->LOCAL_SID() .'-' . $user->SambaGetSid();
		echo "$this->uid: ".$ldap->LOCAL_SID() .'-' . $user->SambaGetSid()."\n";
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			echo "\nFunction: ".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error."\n";
			return false;
		}
		$this->artica_meta();
		return true;		
	
}
		
	function DeleteComputer(){
		$ldap=new clladp();
		$ldap->ldap_delete($this->dn);
		$hash=$this->Groups_list();
		if(is_array($hash)){
		while (list ($num, $val) = each ($hash) ){
			$group=new groups($num);
			writelogs("Delete computer from $val",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$group->DeleteUserFromThisGroup($this->uid);
		}}
		
			$dhcp=new dhcpd(1);
			$dhcp->DeleteFixed($this->uid);		
		
		$sock=new sockets();
		$sock->getfile("SetComputerBackupSchedule");	
		
		
		}

	function uidNumberCheck(){
		if($this->uidNumber<>null){return null;}
		$ldap=new clladp();
      	$r =@ldap_search($ldap->ldapbind, $ldap->suffix, 'uidnumber=*');
      		if ($r){
         		ldap_sort($ldap->ldap_connection, $r, "uidnumber");
            	$result = ldap_get_entries($ldap->ldap_connection, $r);
         		$count = $result['count'];
         		$biguid = $result[$count-1]['uidnumber'][0];
         		$this->uidNumber=$biguid+1;
         		return null;
      		}
     		 $this->uidNumber=1000;
		}
	
	function SambaGetSid(){
		return 2 *$this->uidNumber+ 1000;
	}
	
function Groups_list(){
		$ldap=new clladp();
		$attrs=array('gidNumber','displayName','cn');
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=posixGroup)(memberUid=$this->uid))",$attrs);	
			if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				if(!is_array($result)){return array();}
				for($i=0;$i<$result["count"];$i++){
					if($result[$i]["displayname"][0]==null){$result[$i]["displayname"][0]=$result[$i]["cn"][0];}
					$res[$result[$i]["gidnumber"][0]]=$result[$i]["displayname"][0];
				}
					return $res;
			}	
}

	function save_EnableBackupAccount(){
		$ldap=new clladp();
		$array=array();
		if($this->objectClass_array["UserArticaClass"]==null){
			writelogs("Add a new objectClass called \"UserArticaClass\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
			reset($this->objectClass_array);
			while (list ($num, $line) = each ($this->objectClass_array)){
				$array["objectClass"][]=$line;
			}
			$array["objectClass"][]="UserArticaClass";
		}
				
		
		$array["EnableBackupAccount"][0]=$this->EnableBackupAccount;
		if(!$ldap->Ldap_modify($this->dn,$array)){
			echo "\nFunction: ".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
			return false;
		}
		
		$sock=new sockets();
		$sock->getfile("SetComputerBackupSchedule");	
		$this->artica_meta();
		
		}
		
	function SaveScannerOptions(){
		$ldap=new clladp();
		$tpl=new templates();
		if($this->KasperkyAVScanningDatas==null){return ;}
		$upd=array();
		if($this->objectClass_array["ArticaSettings"]==null){
			while (list ($num, $ligne) = each ($this->objectClass_array) ){
				$upd["objectClass"][]=$ligne;
			}
			$upd["objectClass"][]="ArticaSettings";
			
		}
		
		$upd["KasperkyAVScanningDatas"][0]=$this->KasperkyAVScanningDatas;
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			echo "\nFunction: ".__FUNCTION__."\n".dirname(__FILE__)."\nLine:".__LINE__."\n".$ldap->ldap_last_error;
			return false;
		}		
		$this->artica_meta();
		return true;
		
		
	}
	
function OSLIST($fieldset=false){
		include_once(dirname(__FILE__)."/class.computers.hardware.inc");
			
	  if($fieldset){
	  	while (list ($num, $ligne) = each ($COMPUTER_OS_LIST) ){
	  		$return[$ligne]=$ligne;
	  	}
	  	return $return;
	  }
		
	  return $array;
	
	
}
		
		
}

class Wol{
 
 	public function wake($mac){
 		$sock=new sockets();
 		$mac=base64_encode($mac);
 		return nl2br(base64_decode($sock->getFrameWork("cmd.php?wake-on-lan=$mac"))); 
 		
 	}
 } 

?>