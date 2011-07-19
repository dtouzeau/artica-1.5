<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.group-office.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

$user=new usersMenus();
if(!$user->GROUPOFFICE_INSTALLED){die();}
$param=$argv[1];
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($param=='--all'){_GetServers();die();}
if(preg_match('#--user=(.+)#',$param,$re)){fixuser(trim($re[1]));die();}
_GetServers();

function fixuser($uid){
	$ct=new user($uid);
	$ou=$ct->ou;
	$ldap=new clladp();
	$dn="ou=www,ou=$ou,dc=organizations,$ldap->suffix";
	$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
	$attr=array();
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);		
	for($i=0;$i<$hash["count"];$i++){
		$root=$hash[$i]["apachedocumentroot"][0];
		$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
		$apacheservername=trim($hash[$i]["apacheservername"][0]);
		$dn=$hash[$i]["dn"];
		if($wwwservertype<>"GROUPOFFICE"){continue;}
		$server_database=str_replace(" ","_",$apacheservername);
		$server_database=str_replace(".","_",$server_database);
		$server_database=str_replace("-","_",$server_database);	
		$groupoffice=new GroupOffice($server_database);
		$groupoffice->UpdateUser($uid);	
	}	
}


function _GetServers(){
	$ldap=new clladp();
	
	$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
	$attr=array();
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
	//print_r($hash);
	
	for($i=0;$i<$hash["count"];$i++){
		$root=$hash[$i]["apachedocumentroot"][0];
		$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
		$apacheservername=trim($hash[$i]["apacheservername"][0]);
		$dn=$hash[$i]["dn"];
		if($wwwservertype<>"GROUPOFFICE"){continue;}
		if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){$ou=$re[1];}
		$server_database=str_replace(" ","_",$apacheservername);
		$server_database=str_replace(".","_",$server_database);
		$server_database=str_replace("-","_",$server_database);
		ImportUsers(trim($ou),$server_database);
		}
}

function ImportUsers($ou,$database){
	if($database==null){
		writelogs("$ou -> database is null",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$ldap=new clladp();
	$dn="ou=users,ou=$ou,dc=organizations,$ldap->suffix";
	$pattern="(&(objectclass=userAccount)(cn=*))";
	$attr=array("uid","uidNumber");
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		$uidNumber=$hash[$i]["uidnumber"][0];
		$groupoffice=new GroupOffice($database);
		$groupoffice->UpdateUser($uid);
		}
		
	}

?>