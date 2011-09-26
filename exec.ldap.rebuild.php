<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if($argv[1]=="--test-connexion"){test_connexion();die();}


$ldap=new clladp();




if($ldap->suffix==null){die();}

$sock=new sockets();
if($sock->GET_INFO("EnableLDAPSyncProvClient")==1){die();}

if(!$ldap->ExistsDN("dc=organizations,$ldap->suffix")){
	writelogs("Create new entry dc=organizations,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
	$upd["objectClass"][]="top";
	$upd["objectClass"][]="organization";
	$upd["objectClass"][]="dcObject";
	$upd["o"][]="organizations";
	$upd["dc"][]="organizations";
	if(!$ldap->ldap_add("dc=organizations,$ldap->suffix",$upd)){
		writelogs("Unable to create new entry dc=organizations,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
		writelogs($ldap->ldap_last_error,__FUNCTION,__FILE__,__LINE__);
		die();
	}

	$ldap->ArticaCreate();
}else{
	$ldap->ArticaCreate();
	die();
}
	
$hash=GetOus();	
if(is_array($hash)){
	while (list ($num, $ligne) = each ($hash) ){
		mouvDN($ligne,"dc=organizations,$ldap->suffix");
		
	}
}

$neworganizations=$ldap->hash_get_ou();
if(is_array($neworganizations)){
	while (list ($num, $org) = each ($neworganizations) ){
		CheckUsers($org);
		CheckGroups($org);
	}
	
	
}



if($ldap->ExistsDN("dc=samba,$ldap->suffix")){
	writelogs("Move Samba branch: \"dc=samba,$ldap->suffix\"",__FUNCTION,__FILE__,__LINE__);
	mouvUser("dc=samba",$ldap->suffix,"dc=organizations,$ldap->suffix");
}

if(!$ldap->ExistsDN("dc=NAB,$ldap->suffix")){
	writelogs("Creating Address book root tree,dc=NAB,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
	CreateNAB();
}




function GetOus(){
	$ldap=new clladp();
	$dn=$ldap->suffix;
	$con=$ldap->ldap_connection;
	$filter="(objectclass=organizationalUnit)";
	$attrs=array();
	$sr=ldap_search($con, $dn, $filter);
	if(!$sr){
		writelogs("Looks good, no organizations in first branch..,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
		return array();
	
	}
    $entries=ldap_get_entries($con, $sr);
    for($i=0;$i<=$entries["count"];$i++){
    	$dnsearch=$entries[$i]["dn"];
    	if($dnsearch==null){continue;}
    	$tmpdn=$dnsearch;
    	$tmpdn=str_replace(",$ldap->suffix",'',$tmpdn);
    	if(strpos($tmpdn,"dc=")==0){
    		$res[]=$tmpdn;
    	}
  	 }
  	 return $res;
  	 
}
	
function mouvDN($olddn,$newdnroot){
	$ldap=new clladp();
	$dn="$olddn,$ldap->suffix";
	$newdn=$olddn;
	$newParent="dc=organizations,$ldap->suffix";
	
	if(!ldap_rename($ldap->ldap_connection, $dn, $newdn, $newParent, true)){
		writelogs("failed move $newdn,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
	}else{
		writelogs("success move $newdn,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
	}
}


function CheckUsers($org){
	$ldap=new clladp();
	$dn_users="ou=users,ou=$org,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn_users)){
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="organizationalUnit";
		$upd["ou"]="users";
		if(!$ldap->ldap_add($dn_users,$upd)){
			writelogs("failed create $dn_users,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
			return null;
		}
	}
	
	$hash=SearchUsers($org);
	if(!is_array($hash)){return null;}
	
	while (list ($num, $userdn) = each ($hash) ){
		mouvUser($userdn,"ou=$org,dc=organizations,$ldap->suffix","ou=users,ou=$org,dc=organizations,$ldap->suffix");
	}	
}

function CheckGroups($org){
	$ldap=new clladp();
	$dn_users="ou=groups,ou=$org,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn_users)){
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="organizationalUnit";
		$upd["ou"]="groups";
		if(!$ldap->ldap_add($dn_users,$upd)){
			writelogs("failed create $dn_users,$ldap->suffix",__FUNCTION,__FILE__,__LINE__);
			return null;
		}
	}
	
	$hash=SearchGroups($org);
	if(!is_array($hash)){return null;}
	
	while (list ($num, $groupdn) = each ($hash) ){
		mouvUser($groupdn,"ou=$org,dc=organizations,$ldap->suffix","ou=groups,ou=$org,dc=organizations,$ldap->suffix");
	}	
}


function mouvUser($olddn,$olddnRoot,$newroot){
	$ldap=new clladp();
	$dn="$olddn,$olddnRoot";
	$newdn=$olddn;
	$newParent=$newroot;
	
	if(!ldap_rename($ldap->ldap_connection, $dn, $newdn, $newParent, true)){
		writelogs("failed move $dn",__FUNCTION,__FILE__,__LINE__);
	}else{
		writelogs("success move $newdn,$newParent",__FUNCTION,__FILE__,__LINE__);
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
		$dnsearch=str_replace(",ou=$org,dc=organizations,$ldap->suffix","",$dnsearch);
		if(strpos($dnsearch,"ou=users")>0){continue;}
    	$res[]=$dnsearch;
  	}
  	
  	return $res;
}
function SearchGroups($org){
	$ldap=new clladp();
	$dn="ou=$org,dc=organizations,$ldap->suffix";
	$filter="(&(objectclass=posixGroup)(cn=*))";
	$attrs[]="dn";
	$con=$ldap->ldap_connection;
	$sr=ldap_search($con, $dn, $filter,$attrs);
	if(!$sr){return false;}
	$entries=ldap_get_entries($con, $sr);	
	for($i=0;$i<=$entries["count"];$i++){
		$dnsearch=$entries[$i]["dn"];
		if($dnsearch==null){continue;}
		$dnsearch=str_replace(",ou=$org,dc=organizations,$ldap->suffix","",$dnsearch);
		if(strpos($dnsearch,"ou=groups")>0){continue;}
    	$res[]=$dnsearch;
  	}
  	
  	return $res;
}

function CreateNAB(){
	$ldap=new clladp();
	$dn="dc=NAB,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="organization";
		$upd["objectClass"][]="dcObject";
		$upd["o"][]="NAB";
		$upd["dc"][]="NAB";
		if(!$ldap->ldap_add($dn,$upd)){
			writelogs("failed creating $dn",__FUNCTION,__FILE__,__LINE__);
		}
		
	}

}
function test_connexion(){
	$ldap=new clladp();
	if($ldap->ldapFailed){$result="FALSE";}else{$result="TRUE";}
	@file_put_contents("/etc/artica-postfix/LDAP_TESTS", $result);
	
}

?>