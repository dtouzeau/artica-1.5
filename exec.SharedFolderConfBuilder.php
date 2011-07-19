<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');

$filter="(objectClass=SharedFolders)";
$attrs=array("SharedFolerConf","gidnumber");
$ldap=new clladp();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$filter,$attrs);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);
if($hash["count"]==0){die();}

if(is_dir("/etc/artica-postfix/SharedFolers")){system('/bin/rm -rf /etc/artica-postfix/SharedFolers');}
@mkdir("/etc/artica-postfix/SharedFolers");
   
for($i=0;$i<$hash["count"];$i++){
	$ini=new Bs_IniHandler();
	$sharedfolerconf=$hash[$i]["sharedfolerconf"][0];
	$gidnumber=$hash[$i]["gidnumber"][0];
	$ini->loadString($sharedfolerconf);
	if($ini->_params["members"]["members_count"]<1){continue;}
	$ini=VerifySharedFolders($gidnumber,$ini);
	
	
	if($ini->_params["SHARED_FOLDERS"]["sharedfolder_count"]==0){
		echo "NO shared folder in list\n";
		continue;
	}
	echo "Save file in /etc/artica-postfix/SharedFolers/$gidnumber.sha\n";
	$ini->saveFile("/etc/artica-postfix/SharedFolers/$gidnumber.sha");

	
}

function VerifySharedFolders($guid,$ini){
$filter="(&(objectClass=SharedFolders)(gidnumber=$guid))";
$attrs=array("SharedFolderList","gidnumber");
$ldap=new clladp();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$filter,$attrs);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
$count=$hash[0]["sharedfolderlist"]["count"];
if($count==null){$count=0;}
$ini->_params["SHARED_FOLDERS"]["sharedfolder_count"]=$count;
echo "Group $guid store $count folders to be shared\n";
if($count>0){
	for($i=0;$i<$count;$i++){
		$ini->_params["SHARED_FOLDERS"]["shared.$i"]=$hash[0]["sharedfolderlist"][$i];
		
	}
}
return $ini;
	
}


?>