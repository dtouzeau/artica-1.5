<?php
include_once(dirname(__FILE__).'/class.ldap.inc');


class vhosts{
	var $ou=null;
	
	
	function vhosts($ou=null){
		if($ou<>null){
			$this->ou=$ou;
			$this->LoadVhosts();
		}
		
		
		
	}
	
	
	private function LoadVhosts(){
		$ldap=new clladp();
		if($this->ou<>null){
			$suffix="ou=$this->ou,dc=organizations,$ldap->suffix";
		}else{
			$suffix="dc=organizations,$ldap->suffix";
		}
		$pattern="(&(objectclass=apacheConfig)(cn=*))";
		$filters=array();
		
		$sr = @ldap_search($ld,$suffix,$pattern,$filters);
		
		
		
	}
	
	
}

?>