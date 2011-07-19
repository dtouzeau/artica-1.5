<?php
include_once(dirname(__FILE__) . '/logs.inc');
include_once(dirname(__FILE__) . '/class.templates.inc');
include_once(dirname(__FILE__) . '/class.ldap.inc');


class pdns{
	var $domainname=null;
	var $suffix;
	var $last_error;
	var $DisablePowerDnsManagement=0;
	
//------------------------------------------------------------------------------------------------------------------------------	
	function pdns($domainname=null){
		
		if(!isset($GLOBALS["DisablePowerDnsManagement"])){
			$sock=new sockets();
			$this->DisablePowerDnsManagement=$sock->GET_INFO("DisablePowerDnsManagement");
			if(!is_numeric($this->DisablePowerDnsManagement)){$this->DisablePowerDnsManagement=0;}
			$GLOBALS["DisablePowerDnsManagement"]=$this->DisablePowerDnsManagement;
		}else{
			$this->DisablePowerDnsManagement=$GLOBALS["DisablePowerDnsManagement"];
		}
		
		
		if($domainname<>null){
			$this->domainname=$domainname;
			$this->BuildBranch();
		}
		
	}
//------------------------------------------------------------------------------------------------------------------------------	
	private function BuildBranch(){
		if($this->DisablePowerDnsManagement==1){return;}
		$ldap=new clladp();
		$up=array();
		$dn="ou=dns,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$up["objectClass"][]="top";
			$up["objectClass"][]="organizationalUnit";
			$up["ou"][]="dns";
			$ldap->ldap_add($dn,$up);
		}else{
			writelogs("ou=dns,$ldap->suffix exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		
		$tbl=explode(".",$this->domainname);
		if(!is_array($tbl)){
			echo "Check domain $this->domainname, cannot split in '.'\n Class: ".__CLASS__."\nLine:".__LINE__;
			writelogs("Check domain $this->domainname, cannot split in '.' ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		
		$extension=$tbl[count($tbl)-1];
		if($extension==null){return null;}
		
		
		$dn="dc=$extension,ou=dns,$ldap->suffix";

		
		
		if(!$ldap->ExistsDN($dn)){
			$up=array();
			writelogs("Check domain $dn doesn`t exists, add it.",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$up["objectClass"][]="top";
			$up["objectClass"][]="dcobject";
			$up["objectClass"][]="dNSDomain2";
			$up["objectClass"][]="domainRelatedObject";
			$up["dc"][]="$extension";
			$up["associateddomain"][]="$extension";
			if(!$ldap->ldap_add($dn,$up)){
				echo "Perhaps the dNSDomain2 is not loaded, restart the LDAP service and retry adding the entry\n";
				return;
			}
			unset($up);
			
		}else{
			writelogs(" $dn exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}

		
		
		$strippeddomain=str_replace(".$extension","",$this->domainname);
		$dn="dc=$strippeddomain,dc=$extension,ou=dns,$ldap->suffix";
		$this->suffix=$dn;
		
		
		if(!$ldap->ExistsDN($dn)){
			writelogs("Check domain $dn doesn`t exists, add it.",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$up["objectClass"][]="top";
			$up["objectClass"][]="dcobject";
			$up["objectClass"][]="dNSDomain2";
			$up["objectClass"][]="domainRelatedObject";
			$up["dc"][]="$strippeddomain";
			$up["soarecord"][]="ns.$this->domainname me@$this->domainname 1 1800 3600 86400 7200";
			$up["nsrecord"][]="ns.$this->domainname";
			$up["ARecord"][]="127.0.0.1";
			$up["associateddomain"][]="$this->domainname";
			$ldap->ldap_add($dn,$up);
			unset($up);
			}
		$dn="dc=ns,dc=$strippeddomain,dc=$extension,ou=dns,$ldap->suffix";
		
		if(!$ldap->ExistsDN($dn)){
			writelogs("Check domain $dn doesn`t exists, add it.",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$up["objectClass"][]="top";
			$up["objectClass"][]="dNSDomain2";
			$up["objectClass"][]="domainRelatedObject";
			$up["dc"][]="ns";
			$up["ARecord"][]="127.0.0.1";
			$up["associateddomain"][]="ns.$this->domainname";
			$ldap->ldap_add($dn,$up);
			unset($up);
			}
	
	
	}
	
//------------------------------------------------------------------------------------------------------------------------------	
	private function GetDNFromMac($macaddress){
		$suffix="ou=dns,$ldap->suffix";
		$sr = @ldap_search($ldap->ldap_connection,$suffix,"(&(objectclass=dNSDomain2)(MacRecord=$macaddress))",array());
		if($sr){
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			return $hash[0]["dn"];
		}
	}
	
//------------------------------------------------------------------------------------------------------------------------------
	private function DeleteFromMacAddrr($macaddress){
		$ldap=new clladp();
		if($macaddress==null){return null;}
		$suffix="ou=dns,$ldap->suffix";
		
		$sr = @ldap_search($ldap->ldap_connection,$suffix,"(&(objectclass=dNSDomain2)(MacRecord=$macaddress))",array());
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			writelogs("find mac $macaddress ".count($hash)." entries",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			for($i=0;$i<$hash["count"];$i++){
				if($hash[$i]["dn"]<>null){
					writelogs("Delete {$hash[$i]["dn"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$ldap->ldap_delete($hash[$i]["dn"]);
				}
			}
		}else{
			writelogs("find mac (&(objectclass=dNSDomain2)(MacRecord=$macaddress)) ERROR",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
	}
//------------------------------------------------------------------------------------------------------------------------------	
	private function ArticaMetaReplic(){
		if($this->DisablePowerDnsManagement==1){return;}
		$sock=new sockets();
		if($sock->GET_INFO("ArticaMetaEnabled")==1){
			$sock->getFrameWork("cmd.php?artica-meta-export-dns=yes");
		}		
	}
	
//------------------------------------------------------------------------------------------------------------------------------	
	public function EditIPName($computername,$IP,$DnsType,$macaddress=null){
			if($this->DisablePowerDnsManagement==1){return true;}
			$ldap=new clladp();
			$up=array();
			
			if(preg_match('#[0-9]+\.[0-9]+\.[0-9]+\.#',$computername)){
				writelogs("Cannot put in DNS an ip address as computer name",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return false;
			}
			
			if($this->domainname==null){
				writelogs("Cannot put in DNS a computer that did not have any domain",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return false;
			}	

			if(substr($computername,0,1)=='.'){$computername=substr($computername,1,strlen($computername));}
			$computername=str_replace('..','.',$computername);
			$computername=str_replace('$','',$computername);
			$fqdn="$computername.$this->domainname";		
			$fqdn=str_replace('..','.',$fqdn);			
			
			$macaddress=trim($macaddress);
			if($macaddress<>null){
				$dn=$this->GetDNFromMac($macaddress);
				if($dn<>null){
					writelogs("$macaddress = \"$dn\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$up["associateddomain"]="$fqdn";
					if(!$ldap->Ldap_add_mod($dn,$up)){echo $ldap->ldap_last_error;return false;}
					$this->ArticaMetaReplic();
					return true;
				}
			}
			
			
			
			$dn="dc=$computername,$this->suffix";
			if(!$ldap->ExistsDN($dn)){
				writelogs("Add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$up["objectClass"][]="top";
				$up["objectClass"][]="dNSDomain2";
				$up["objectClass"][]="domainRelatedObject";
				$up["associatedDomain"][]="$fqdn";
				$up["dc"][0]=$computername;
				if($macaddress<>null){$up["MacRecord"][]=$macaddress;}
				if($DnsType=="A"){$up["arecord"][]=$IP;}
				if(!$ldap->ldap_add($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
			}else{
				if($DnsType=="A"){$up["arecord"][]=$IP;}
				$up["arecord"][]=$IP;
				if(!$ldap->Ldap_modify($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
				
			}

			unset($up);
			if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$IP,$re)){
				$arpa1=$re[3].".".$re[2].".". $re[1].".in-addr.arpa";
				$arpa2_dc_ip=$re[4].".".$re[3].".".$re[2]."in-addr.arpa";
				
			}else{
				$this->last_error=" $IP bad value\n";
				return false;
			}
			
			$dn="dc=$arpa1,$this->suffix";
			if(!$ldap->ExistsDN($dn)){
				writelogs("Add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$up["objectClass"][]="top";
				$up["objectClass"][]="dNSDomain2";
				$up["objectClass"][]="domainRelatedObject";	
				$up["dc"][]="$arpa1";	
				$up["soarecord"][]="ns.$this->domainname me@$this->domainname 1 1800 3600 86400 7200";
				$up["nsrecord"][]="ns.$this->domainname";
				$up["associateddomain"][]="$arpa1";
				if(!$ldap->ldap_add($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
			}
			unset($up);
			$dn="dc={$re[4]},dc=$arpa1,$this->suffix";
		if(!$ldap->ExistsDN($dn)){
				writelogs("Add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$up["objectClass"][]="top";
				$up["objectClass"][]="dNSDomain2";
				$up["objectClass"][]="domainRelatedObject";	
				//$up["objectClass"][]="ArticaComputerInfos";
				$up["dc"][]="$re[4]";	
				$up["ptrrecord"][]="$fqdn";
				$up["associateddomain"][]="{$re[4]}.$arpa1";
				$up["MacRecord"][]=$macaddress;
				if(!$ldap->ldap_add($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}
			}else{
				$up["ptrrecord"]="$fqdn";
				if(!$ldap->Ldap_add_mod($dn,$up)){$this->last_error=$ldap->ldap_last_error; return false;}		
			}
			$this->ArticaMetaReplic();
			return true;
			
		}
		
	public function GetIp($servername){
			if($this->DisablePowerDnsManagement==1){return null;}
			$ldap=new clladp();
			$user=new usersMenus();
			$filter="(&(Objectclass=dNSDomain2)(associatedDomain=$servername))";
			$sr = @ldap_search($ldap->ldap_connection,"ou=dns,$ldap->suffix",$filter,array("aRecord"));
			if(!$sr){return null;}
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			return $hash[0]["arecord"][0];
			
		}
		
	public function IpToHosts($ip){
		if($this->DisablePowerDnsManagement==1){return null;}
		$ldap=new clladp();
		
		$resolved=gethostbyaddr($ip);
		if($resolved<>null){
			if($resolved<>$ip){
				$array[$resolved]=$resolved;
			}
		}
		$filter="(&(Objectclass=dNSDomain2)(aRecord=$ip))";
		$sr = @ldap_search($ldap->ldap_connection,"ou=dns,$ldap->suffix",$filter,array("associatedDomain"));
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		
		
		for($i=0;$i<$hash["count"];$i++){
			for($Z=0;$Z<$hash[$i]["associateddomain"]["count"];$Z++){
				$array[$hash[$i]["associateddomain"][$Z]]=$hash[$i]["associateddomain"][$Z];
			}
			
		}
		
		if(is_array($array)){
			while (list ($index, $host) = each ($array) ){
				$return[]=$host;
			}
		}
		
		return $return;}
	

	
	
}




?>