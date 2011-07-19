<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

$GLOBALS["VERBOSE"]=true;

if($argv[1]=='--users'){parseusers();exit;}



function parseusers(){
	
$ldap=new clladp();	
$hash=GetOus();	
if(is_array($hash)){
	while (list ($num, $ligne) = each ($hash) ){
		echo "
		================================================
				Found organization: $num
		================================================
		";
		SearchUsers($num);
		
	}
}

}


function SearchUsers($org){
	$ldap=new clladp();
	$dn="ou=$org,dc=organizations,$ldap->suffix";
	$filter="(&(objectclass=userAccount)(cn=*))";
	$attrs[]="dn";
	$con=$ldap->ldap_connection;
	$sr=ldap_search($con, $dn, $filter,$attrs);
	if(!$sr){return false;}
	$entries=ldap_get_entries($con, $sr);	
	for($i=0;$i<=$entries["count"];$i++){
		$dnsearch=$entries[$i]["dn"];
		if($dnsearch==null){continue;}
		echo $dnsearch."\n";
  	}
  	
  	
}

function GetOus(){
	$ldap=new clladp();
	return $ldap->hash_get_ou(true);
  	 
}