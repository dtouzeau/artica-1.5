<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__).'/class.lvm.org.inc');


class safebox{
	var $uid;
	var $CryptedHome;
	var $CryptedHomePassword=null;
	var $CryptedHomeSize=null;
	var $ou;
	var $repository;
	var $crypted_filepath;
	var $crypted_filesize;
	var $home;
	var $mounted;
	var $homeDirectory;
		
	function safebox($uid=null){
		if($uid<>null){
			$this->uid=$uid;
			$this->Load();}
		
	}
	
	private function Load(){
		$ldap=new clladp();
		$uid=$this->uid;
		$filter=array("CryptedHome","CryptedHomePassword","CryptedHomeSize","dn","homeDirectory");
		$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix","(uid=$uid)",$filter);
		if(!$sr){
			writelogs("Unable to find : $uid stamp DoesNotExists has TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		
		//$hash=ldap_get_entries($this->ldap_connection,$sr);
		$attrs = ldap_get_entries($ldap->ldap_connection,$sr);
		
		$this->homeDirectory=$attrs[0]["homedirectory"][0];
		$this->CryptedHomePassword=$attrs[0]["cryptedhomepassword"][0];
		$this->CryptedHomeSize=$attrs[0]["cryptedhomesize"][0];		
		$this->CryptedHome=$attrs[0]["cryptedhome"][0];
		if(preg_match("#ou=users,ou=(.+?),dc=organizations#",$attrs[0]["dn"],$re)){$this->ou=$re[1];}
		$lvm=new lvm_org($this->ou);
		$repository=trim($lvm->storage_enabled);
		writelogs("$this->ou=$repository",__FUNCTION__,__FILE__,__LINE__);
		
		if($repository==nul){
			writelogs("No repository available for $this->ou use system disk",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$repository="/home";
		}	

		if($repository<>"/home"){
			$mounts=$this->GetFSTabMountPoint($repository);
		
			if(!is_array($mounts)){
				writelogs("Unable to find mount point of $repository",__FUNCTION__,__FILE__,__LINE__);
				return;
			}
	
			if($mounts[0]==null){
				writelogs("Unable to find mount point of $repository",__FUNCTION__,__FILE__,__LINE__);
				return;		
			}

			$repository=$mounts[0];
		}
		$this->home=$repository;
		$this->repository=$repository."/.SafeBoxes";
		if($this->repository<>null){
			$this->crypted_filepath="$this->repository/$this->uid";
			$this->crypted_filesize=$this->GetSafeBoxSize();
			$this->mounted=$this->isMounted();
		}
	}
	
	private function GetFSTabMountPoint($dev){
			if($dev==null){return null;}
			$dev=$this->MapperDetect($dev);
			$array=array();
			$dev=str_replace("/","\/",$dev);
			$datas=explode("\n",@file_get_contents("/etc/fstab"));
			while (list ($num, $ligne) = each ($datas) ){
				 	if($ligne==null){continue;}
				 	if(preg_match("#$dev\s+(.+?)\s+#",$ligne,$re)){
				 			$array[]=trim($re[1]);
				 			continue;
				 		}
				 	}
			
			return $array;
	
	}	
	
	private function MapperDetect($dev){
		$sock=new sockets();
		return trim($sock->getFrameWork("cmd.php?lvs-mapper=$dev"));
	}

	private function GetSafeBoxSize(){
		$sock=new sockets();
		$size=trim($sock->getFrameWork("cmd.php?filesize=$this->crypted_filepath"));
		writelogs("$this->crypted_filepath::$size bytes",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$size=round(($size/1024)/1000)/1000;
		
		return $size;	
	}
	
	private function isMounted(){
		$mapper="/dev/mapper/".basename($this->crypted_filepath);
		$sock=new sockets();
		$results=trim($sock->getFrameWork("cmd.php?disk-ismounted=yes&dev=$mapper"));
		if($results=="TRUE"){return true;}
	}
	
}

?>