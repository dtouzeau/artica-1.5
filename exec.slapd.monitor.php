<?php
$GLOBALS["BYPASS"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.graphs.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


$ldap=new clladp();

	$pattern="(objectClass=*)";
	$attr=array();
	
	$sr =ldap_search($ldap->ldap_connection,"cn=Monitor",$pattern,$attr);
	if($sr){
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	}else{
		
		echo $ldap->ldap_last_error;
	}
	
	if($hash["count"]>0){		
				for($i=0;$i<$hash["count"];$i++){
					//print_r($hash[$i]);
				}
				
	}
	
	
	$sr =ldap_search($ldap->ldap_connection,"cn=Total,cn=connections,cn=monitor",$pattern,$attr);
	if($sr){
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	}else{
		
		echo $ldap->ldap_last_error;
	}
	print_r($hash);
	
				
	
	
	


?>