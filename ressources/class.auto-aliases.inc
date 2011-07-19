<?php

class AutoAliases{
	var $DomainsArray=array();
	var $dn;
	var $ou;
	var $error;
	
	function AutoAliases($ou){
		$this->ou=$ou;
		$this->loadAutoAliases();
		
	}
	
	
	function CheckForDomain($domainName){
		
		
		
		
	}
	
	function loadAutoAliases(){
		$ldap=new clladp();
		$this->dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		writelogs($dn,__FUNCTION__,__FILE__);
		$search =@ldap_read($ldap->ldap_connection,$this->dn,'(objectClass=*)');
		if (!$search){return null;}
		$entry_id = ldap_first_entry($ldap->ldap_connection,$search);
		$attrs = ldap_get_attributes($ldap->ldap_connection,$entry_id);
		
		$Autoaliases=$attrs["AutoDomainAliases"][0];
		if($Autoaliases==null){return null;};
		$arr=explode(";",$Autoaliases);
		writelogs("explode=".count($arr),__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!is_array($arr)){return null;}
		while (list ($num, $val) = each ($arr) ){
			if(!preg_match('#(.+?)\.(.+)#',$val)){continue;}
			$this->DomainsArray[$val]=$val;
			
		}
		
		
	}
	
	function Save(){
		if(is_array($this->DomainsArray)){
			$upd=array();
			while (list ($num, $val) = each ($this->DomainsArray) ){
				$arr[]=$val;
			}
			
			if(is_array($arr)){$newval=implode(";",$arr);}else{$newval="NULL";}
			
		}else{
			$newval="NULL";
		}
		
		$upd["AutoDomainAliases"][0]=$newval;
		$ldap=new clladp();
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			$this->error=$ldap->ldap_last_error;
			return false;
		}
		return true;
		
	}
	
	
}