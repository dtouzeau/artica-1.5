<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');

if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}


if($argv[1]=="--check-adms"){
	CheckCyrusByDomains();
	die();
}

$log=false;
$username=$argv[1];
$verbose=$argv[2];
if($verbose=='--verbose'){$_GET["log"]=true;}
if(!CheckBranch()){die();}
CheckCyrusByDomains();


if(trim($username)==null){
	writelogs( "No username specified...",__FUNCTION__,__FILE__,__LINE__);
	die();
}


$ldap=new clladp();
if($ldap->ldapFailed){
	writelogs('Unable to logon to ldap server...',__FUNCTION__,__FILE__,__LINE__);
	die();
}



$users=new usersMenus();
$cyrus_text_password=$users->cyrus_ldap_admin_password;
$cyrus_password=$ldap->CyrusPassword();
if($cyrus_password<>$cyrus_text_password){
	writelogs('Cyrus password did not match, modify it...',__FUNCTION__,__FILE__,__LINE__);
	if(!ChangeCyrusPassword("cyrus",$cyrus_text_password)){return false;}
	$cyrus_password=$cyrus_text_password;
}

ChangeCyrusPassword($username,$cyrus_password);
UpdateMurderUser($cyrus_password);


function ChangeCyrusPassword($username,$password){
	$ldap=new clladp();
	if(strlen($password)==0){writelogs('ChangeCyrusPassword():: No password specified',__FUNCTION__,__FILE__,__LINE__);return false;}
	
if($ldap->ldapFailed){
	writelogs('Unable to logon to ldap server...',__FUNCTION__,__FILE__,__LINE__);
	return false;
}	
	
	if(!$ldap->ExistsDN("cn=$username,dc=organizations,$ldap->suffix")){
		if(!AddNewCyrusUser($username,$password)){
			writelogs("Unable to add $username ...",__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
	}
	
	
	
	writelogs("Update Cyrus password for cn=$username,dc=organizations,$ldap->suffix ...",__FUNCTION__,__FILE__,__LINE__);	
	$arr["userPassword"][]=$password;
	if(!$ldap->Ldap_modify("cn=$username,dc=organizations,{$ldap->suffix}",$arr)){
		writelogs("ChangeCyrusPassword():: $ldap->ldap_last_error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	return true;
	
}
function UpdateMurderUser($password){
	$ldap=new clladp();
	if(strlen($password)==0){writelogs('UpdateMurderUser():: No password specified',__FUNCTION__,__FILE__,__LINE__);return false;}
	
if($ldap->ldapFailed){
	writelogs('Unable to logon to ldap server...',__FUNCTION__,__FILE__,__LINE__);
	return false;
}	
	
	if(!$ldap->ExistsDN("cn=murder,dc=organizations,$ldap->suffix")){
		if(!AddNewMurderUser($password)){
			writelogs("Unable to add muder username ...",__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
	}
	
	
	
	writelogs("Update murder password for cn=murder,dc=organizations,$ldap->suffix ...",__FUNCTION__,__FILE__,__LINE__);	
	$arr["userPassword"][]=$password;
	if(!$ldap->Ldap_modify("cn=murder,dc=organizations,{$ldap->suffix}",$arr)){
		writelogs("ChangeCyrusPassword():: $ldap->ldap_last_error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	return true;
	
}


function AddNewMurderUser($password){
$ldap=new clladp();

if(!$ldap->ExistsDN("dc=organizations,$ldap->suffix")){
	$ldap->BuildMasterTree();
}

$dn="cn=murder,dc=organizations,{$ldap->suffix}";
if($ldap->ExistsDN($dn)){
	UpdateMurderUser($password);
	return;
}

writelogs("Creating new user $dn ...",__FUNCTION__,__FILE__,__LINE__);
$arr["objectClass"][]='top';
$arr["objectClass"][]='inetOrgPerson';
$arr["cn"][]="murder";
$arr["sn"][]="murder";;
$arr["uid"][]="murder";;
$arr["userPassword"][]=$password;	
if(!$ldap->ldap_add($dn,$arr)){
	writelogs("AddNewMurderUser():: Failed to to add cyrus $dn",__FUNCTION__,__FILE__,__LINE__);
	writelogs("AddNewMurderUser():: $ldap->ldap_last_error",__FUNCTION__,__FILE__,__LINE__);
	return false;
}
writelogs("Success...adding \"murder\" account",__FUNCTION__,__FILE__,__LINE__);
return true;
}


function AddNewCyrusUser($username,$password){
$ldap=new clladp();

if(!$ldap->ExistsDN("dc=organizations,$ldap->suffix")){
	$ldap->BuildMasterTree();
}

$dn="cn=$username,dc=organizations,{$ldap->suffix}";

writelogs("Creating new user $dn ...",__FUNCTION__,__FILE__,__LINE__);
$arr["objectClass"][]='top';
$arr["objectClass"][]='inetOrgPerson';
$arr["cn"][]=$username;
$arr["sn"][]=$username;
$arr["uid"][]=$username;
$arr["userPassword"][]=$password;	
if(!$ldap->ldap_add($dn,$arr)){
	writelogs("AddNewCyrusUser():: Failed to to add cyrus $dn",__FUNCTION__,__FILE__,__LINE__);
	writelogs("AddNewCyrusUser():: $ldap->ldap_last_error",__FUNCTION__,__FILE__,__LINE__);
	return false;
}
writelogs("Success...adding $username",__FUNCTION__,__FILE__,__LINE__);
return true;
}

function CheckBranch(){
	$ldap=new clladp();
	if(!$ldap->ExistsDN("dc=organizations,$ldap->suffix")){
		writelogs("CheckBranch():: creating the new branch dc=organizations,$ldap->suffix",__FUNCTION__,__FILE__,__LINE__);
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="organization";
		$upd["objectClass"][]="dcObject";
		$upd["o"][]="organizations";
		$upd["dc"][]="organizations";
		if(!$ldap->ldap_add("dc=organizations,$ldap->suffix",$upd)){
			writelogs("Unable to create new entry dc=organizations,$ldap->suffix",__FUNCTION__,__FILE__,__LINE__);
			writelogs($ldap->ldap_last_error,__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		return true;
		
	}else{
		writelogs("CheckBranch():: The new branch dc=organizations,$ldap->suffix exists",__FUNCTION__,__FILE__,__LINE__);
		return true;
	}
		
}


function CheckCyrusByDomains(){
	if(!is_file('/etc/artica-postfix/settings/Daemons/EnableVirtualDomainsInMailBoxes')){return null;}
	$EnableVirtualDomainsInMailBoxes=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/EnableVirtualDomainsInMailBoxes'));
	if($EnableVirtualDomainsInMailBoxes<>1){return null;}
	$users=new usersMenus();
	$cyrus_text_password=$users->cyrus_ldap_admin_password;	
	
	$ldap=new clladp();
	$locals_domains=$ldap->hash_get_all_local_domains();
	
	writelogs("wants to set virtdomains, " . count($locals_domains) . " local domains",__FUNCTION__,__FILE__,__LINE__);
	
	if(!is_array($locals_domains)){
		writelogs("wants to set virtdomains, but no domains set in ldap",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	while (list ($num, $ligne) = each ($locals_domains) ){
		writelogs("check $num domains",__FUNCTION__,__FILE__,__LINE__);
		if($num==null){continue;}
		$cyr="cyrus@$num";
		$cyrus_accounts[$cyr]=$cyr;
		ChangeCyrusPassword($cyr,$cyrus_text_password);
		
	}
	
	$cyrus_accounts["cyrus"]="cyrus";
	$change=false;
	
	$local_admins=get_cyrus_admins();
	
	while (list ($num, $ligne) = each ($cyrus_accounts) ){
		if(!$local_admins[$ligne]){
			writelogs("Must add $ligne in cyrus configuration...",__FUNCTION__,__FILE__,__LINE__);
			$local_admins[$ligne]=true;
			$change=true;
		}
		
	}
	
	if($change){
		while (list ($num, $ligne) = each ($local_admins) ){
			if(trim($num)==null){continue;}
			$admins=$admins . $num. " ";
		}
		set_cyrus_admins($admins);
	}else{
		writelogs("no admins to add in /etc/imapd.conf",__FUNCTION__,__FILE__,__LINE__);
	}
	
	reset($cyrus_accounts);
	while (list ($num, $ligne) = each ($cyrus_accounts) ){
		if(is_file("/usr/sbin/testsaslauthd")){
			writelogs("Testing cyrus account $num",__FUNCTION__,__FILE__,__LINE__);
			system("/usr/sbin/testsaslauthd -u $num -p $cyrus_text_password >/dev/null 2>&1");
		}
		
	}
		
		
	
	
	
	
	
}


function get_cyrus_admins(){
	
	if(!is_file("/etc/imapd.conf")){
		writelogs("wants to set virtdomains, but unable to stat /etc/imapd.conf",__FUNCTION__,__FILE__,__LINE__);
		return array();
	}
	
	$datas=file_get_contents("/etc/imapd.conf");
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match("#^admins:(.+)#",$ligne,$re)){
			$admins=$re[1];
			break;
		}
		
	}
	
	if(trim($admins)==null){return array();}
	
	$admins_array=explode(" ",$admins);
	
	while (list ($num, $ligne) = each ($admins_array) ){
		if(trim($ligne)==null){continue;}
		$results[trim($ligne)]=trim($ligne);
	}
	
	
	return $results;
	
	
}

function set_cyrus_admins($admins){
	
	if(!is_file("/etc/imapd.conf")){
		writelogs("wants to set virtdomains, but unable to stat /etc/imapd.conf",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	$added=false;
	$datas=file_get_contents("/etc/imapd.conf");
	$tbl=explode("\n",$datas);	
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match("#^admins:#",$ligne)){
			$tbl[$num]="admins: $admins";
			$added=true;
			break;
		}
		
	}
	
	if(!$added){
		$tbl[]="admins: $admins";
	}
	
	$fullconf=implode("\n",$tbl);
	file_put_contents("/etc/imapd.conf",$fullconf);
}

function Cyrus_initd_path(){
	if(is_file("/etc/init.d/cyrus2.2")){return "/etc/init.d/cyrus2.2";}
   	if(is_file('/etc/init.d/cyrus-imapd')){return '/etc/init.d/cyrus-imapd';}
   	if(is_file('/etc/init.d/cyrus21')){return '/etc/init.d/cyrus21';}
   	if(is_file('/etc/init.d/cyrus2.2')){return '/etc/init.d/cyrus2.2';}  		
}
	








?>