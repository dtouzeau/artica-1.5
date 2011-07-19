<?php

include_once(dirname(__FILE__)."/class.mysql.inc");

class ldapOu{
	var $bind_dn;
	var $bindPassword;
	var $ldap_host;
	var $ldap_port=389;
	var $ldap_connection;
	var $ldapbind;
	var $suffix;
	var $ldap_last_error;
	var $ldapFailed=false;
	var $use_suse_schema=false;
	var $ldap_last_error_num;
	var $QueryUsers="";
	var $ou=null;
	
	
	function ldapOu($connexion_id){
		$q=new mysql();
		$sql="SELECT ou,ldapdatas FROM ldap_ou_import WHERE ID=$connexion_id";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$this->ou=$ligne["ou"];
		$db=unserialize($ligne["ldapdatas"]);
		$this->ldap_port=$db["server_port"];
		$this->ldap_host=$db["server_host"];
		$this->suffix=$db["search_base"];
		$this->QueryUsers=$db["query_filter"];
		$this->bind_dn=$db["bind_dn"];
		$this->bindPassword=$db["bind_password"];
		
		
		
		
		
	}
	
	public function searchUsers(){
		$this->ldap_perform_connection();
		$pattern=$this->QueryUsers;
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$pattern,array());
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		return $hash;	
	}	
	
	public function GetCountDeUsers(){
		$this->ldap_perform_connection();
		$pattern=$this->QueryUsers;
		$sr =@ldap_search($this->ldap_connection,$this->suffix,$pattern,array());
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		return $hash["count"];	
	}
	
private function ldap_perform_connection(){
		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 
		
			if(!is_numeric($this->ldap_port)){$this->ldap_port=389;}
	
		
		
			if(function_exists("debug_backtrace")){
				$trace=debug_backtrace();
				$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			
			}
		
	if(!function_exists("ldap_connect")){
		if(function_exists("debug_backtrace")){$trace=debug_backtrace();if(isset($trace[1])){$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";writeLogs("-> Call to undefined function ldap_connect() $called".__LINE__,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}}		
		return false;
	}				
		
	writelogs("-> ldap_connect($this->ldap_host, $this->ldap_port ) $called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	$this->ldap_connection=ldap_connect($this->ldap_host, $this->ldap_port ) ;
	
	 if(!$this->ldap_connection){
	 	writelogs("-> ldap_connect FAILED ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__); 
	 	@ldap_close();
	 	return false;
	 }
	 
	 ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
	 ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 	
	 writelogs("ldap_bind -> $this->bind_dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 $this->ldapbind=@ldap_bind($this->ldap_connection, $this->bind_dn, $this->bindPassword);
	 if($this->ldapbind){
	 	return true;
	 }else{
	 	$this->ldap_last_error_num=ldap_errno($this->ldap_connection) ;
	 	$msg=ldap_err2str(ldap_errno($this->ldap_connection));
   		$this->ldap_last_error="\n****\nError number $this->ldap_last_error_num\nAction:ldapbind\nuser:$this->bind_dn\nserver:$this->ldap_host:$this->ldap_port\ndn:$this->bind_dn:$this->bindPassword\n$msg****\n";
	 	writelogs("ldap_bind -> FAILED...$this->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__); 
	 }
	 
}	
	
	
}

?>
	