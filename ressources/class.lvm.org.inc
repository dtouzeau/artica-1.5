<?php


class lvm_org{
	
	private $ou="";
	public $disklist=array();
	public $storage_enabled="";
	public $OuBackupStorageSubDir="users";
	function lvm_org($ou=null){
		
		if($ou<>null){
			$this->ou=$ou;
			$this->disklist=$this->GetDisks();
			$this->storage_enabled=$this->GetEnabledStorage();
		}
		
	}
	
	public function AffectDev($dev){
		$this->BuildBranch();
		$ldap=new clladp();
		$dn="cn=$dev,cn=virtual_disks,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="LVMGroup";
			$upd["cn"][0]="$dev";
			if(!$ldap->ldap_add($dn,$upd)){
				echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;
				return;
			}
			unset($upd);			
		}
		
	}
	
	public function DeAffectDev($dev){
		$this->BuildBranch();
		$ldap=new clladp();
		$dn="cn=$dev,cn=virtual_disks,ou=$this->ou,dc=organizations,$ldap->suffix";
		if($ldap->ExistsDN($dn)){
			if(!$ldap->ldap_delete($dn,true)){
				echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;
				return;
			}
			
		}
		
	}	
	
	private function BuildBranch(){
		$ldap=new clladp();
		$dn="cn=virtual_disks,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"][0]="virtual_disks";
			if(!$ldap->ldap_add($dn,$upd)){
				echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;
				return;
			}
			unset($upd);
		}
		
		
		$dn="cn=storages_enabled,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"][0]="storages_enabled";
			if(!$ldap->ldap_add($dn,$upd)){
				echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;
				return;
			}
			unset($upd);
		}
	}
	
	public function FindOuByDev($dev){
		$ldap=new clladp();
		$attr=array("dn");
		$pattern="(&(objectclass=LVMGroup)(cn=$dev))";
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]>0){
			$dn=$hash[0]["dn"];
			if(preg_match("#cn=$dev,cn=virtual_disks,ou=(.+?),#",$dn,$re)){return $re[1];}
		}
	}
	
	private function GetDisks(){
		$ldap=new clladp();
		$dn="cn=virtual_disks,ou=$this->ou,dc=organizations,$ldap->suffix";
		$results=array();
		$sr =@ldap_search($ldap->ldap_connection,$dn,'objectClass=LVMGroup',array("cn"));
		if (!$sr) {
			writelogs("No datas on $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return $results;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
		//writelogs("{$hash["count"]} in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		for($i=0;$i<$hash["count"];$i++){
			$results[$hash[$i]["cn"][0]]=true;
		}
		
		return $results;
	}
	
	public function SaveEnabledStorage($dev,$OuBackupStorageSubDir){
			$ldap=new clladp();
			if($OuBackupStorageSubDir==null){$OuBackupStorageSubDir="users";}
			$dn="cn=storages_enabled,ou=$this->ou,dc=organizations,$ldap->suffix";
			if($ldap->ExistsDN($dn)){
				$ldap->ldap_delete($dn,true);
			}
			$this->BuildBranch();
			if($dev==null){return null;}
			$dn="cn=$dev,cn=storages_enabled,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd["objectClass"][]="top";
				$upd["objectClass"][]="OuBackupStorage";
				$upd["cn"][0]="$dev";
				$upd["OuBackupStorageSubDir"][0]=$OuBackupStorageSubDir;
				if(!$ldap->ldap_add($dn,$upd)){
					echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;
					return;
				}
				unset($upd);
			}
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?samba-build-homes=yes");
			
			
	}
	
	private function GetEnabledStorage(){
		$ldap=new clladp();
		$dn="cn=storages_enabled,ou=$this->ou,dc=organizations,$ldap->suffix";
		$results=array();
		$sr =@ldap_search($ldap->ldap_connection,$dn,'objectClass=OuBackupStorage',array("cn","OuBackupStorageSubDir"));
		if (!$sr) {
			writelogs("No datas on $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return $results;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
		//print_r($hash);
		//writelogs("{$hash["count"]} in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$this->OuBackupStorageSubDir=$hash[0]["oubackupstoragesubdir"][0];
		return $hash[0]["cn"][0];
		
	}	
	
	
}

?>