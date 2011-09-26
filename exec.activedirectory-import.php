<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
include_once(dirname(__FILE__)."/ressources/class.active.directory.inc");

if($argv[1]=='groups'){checksGroups();exit;}
ActiveDirectoryToMysql();

function ActiveDirectoryToMysql(){
	$sock=new sockets();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	
	if($EnableManageUsersTroughActiveDirectory==0){die();}
	
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
	$pidTime="/etc/artica-postfix/pids/".basename(__FILE__).".time";
	$oldpid=$unix->get_pid_from_file($pidfile);
	$sock=new sockets();
	$ActiveDirectoryMysqlSinc=$sock->GET_INFO($ActiveDirectoryMysqlSinc);
	if(!is_numeric($ActiveDirectoryMysqlSinc)){$ActiveDirectoryMysqlSinc=5;}
	if($ActiveDirectoryMysqlSinc==0){$ActiveDirectoryMysqlSinc=1;}
	$ActiveDirectoryMysqlSinc=$ActiveDirectoryMysqlSinc*60;
	if($unix->process_exists($oldpid,basename(__FILE__))){
		writelogs("Process $oldpid already exists",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if(system_is_overloaded(basename(__FILE__))){
		writelogs("Overloaded system, aborting",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	@file_put_contents($pidfile, getmypid());
	
	if(!$GLOBALS["FORCE"]){if($unix->file_time_min($pidTime)<$ActiveDirectoryMysqlSinc){return;}}
	@unlink($pidTime);
	@file_put_contents($pidTime, time());
		
	
	
$t1=time();
$ldap=new ldapAD();
$hash=$ldap->Ldap_search($ldap->suffix,"(objectClass=organizationalUnit)",array("name","ou","dn"),5000);
if(!is_numeric($hash["count"])){$hash["count"]=0;}
if($hash["count"]==0){return;}
$q=new mysql();

$q->QUERY_SQL("TRUNCATE TABLE `activedirectory_users`","artica_backup");

if(!$q->ok){
	$unix->send_email_events("ActiveDirectory: mysql error $q->mysql_error", "process aborted. Will restart in next cycle", "system");
	return;
}


$q->QUERY_SQL("TRUNCATE TABLE `activedirectory_groups`","artica_backup");
$q->QUERY_SQL("TRUNCATE TABLE `activedirectory_groupsNames`","artica_backup");

$sql="SELECT ou,dn,enabled,OnlyBranch FROM activedirectory_orgs ORDER BY ou";
$results=$q->QUERY_SQL($sql,"artica_backup");	
$BranchsInMyql=mysql_num_rows($results);
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$OUCONFIG[$ligne["dn"]]["PARAMS"]["ENABLED"]=$ligne["enabled"];
	$OUCONFIG[$ligne["dn"]]["PARAMS"]["OnlyBranch"]=$ligne["OnlyBranch"];
	
}

$GLOBALS["MEMORY_COUNT_USERS"]=0;
$GLOBALS["MEMORY_COUNT_GROUPS"]=0;
for($i=0;$i<$hash["count"];$i++){
	$OrganizationDN=utf8_encode($hash[$i]["dn"]);
	if(isset($OUCONFIG[$OrganizationDN])){
		if($OUCONFIG[$OrganizationDN]["PARAMS"]["ENABLED"]==0){
			echo "Importing users from {$hash[$i]["ou"][0]} {$OrganizationDN} aborted (disabled)\n";
			continue;
		}
	}
	
	if($BranchsInMyql>0){
		if(!isset($OUCONFIG[$OrganizationDN])){
			echo "Importing users from {$hash[$i]["ou"][0]} {$OrganizationDN} is not in mysql database (disabled)\n";
			continue;
		}
		
	}
	
	$OnlyBranch=$OUCONFIG[$OrganizationDN]["PARAMS"]["OnlyBranch"];
	
	$dn=utf8_encode($hash[$i]["dn"]);
	$ou=utf8_encode($hash[$i]["ou"][0]);
	$dn=addslashes($dn);
	$ou=addslashes($ou);
	$sql="INSERT IGNORE INTO activedirectory_orgs (ou,dn) VALUES('$ou','$dn')";
	$q->QUERY_SQL($sql,"artica_backup");
	echo "Importing users from {$hash[$i]["ou"][0]} {$hash[$i]["dn"]} OnlyBranch=$OnlyBranch\n";
	importuser($hash[$i]["dn"],$ou,$OnlyBranch);
	
	
}
if($GLOBALS["MEMORY_COUNT_USERS"]==0){@unlink($pidTime);}

checksGroups();

$distanceOfTimeInWords=$unix->distanceOfTimeInWords($t1,time());
$unix->send_email_events("ActiveDirectory: {$GLOBALS["MEMORY_COUNT_USERS"]} members / {$GLOBALS["MEMORY_COUNT_GROUPS"]} groups imported", "These items has been imported into the cache database in $distanceOfTimeInWords", "system");


}


function importuser($suffix,$ou,$OnlyBranch=0){


$ldap=new ldapAD();

if($OnlyBranch==1){
	$hash=$ldap->Ldap_list($suffix,"(objectClass=user)",array(),5000);}
else{
	$hash=$ldap->Ldap_search($suffix,"(objectClass=user)",array(),5000);
}
echo " {$hash["count"]} users\n";

$prefix="INSERT IGNORE INTO activedirectory_users 
(dn,samaccountname,mail,userprincipalname,displayname,ou,telephonenumber,mobile,givenname,title,sn) VALUES";
$q=new mysql();
for($i=0;$i<$hash["count"];$i++){
	$dn=$hash[$i]["dn"];
	$displayname=$hash[$i]["displayname"][0];
	$userprincipalname=$hash[$i]["userprincipalname"][0];
	$samaccountname=$hash[$i]["samaccountname"][0];
	$telephoneNumber=$hash[$i]["telephoneNumber"][0];
	$mobile=$hash[$i]["mobile"][0];
	$mail=$hash[$i]["mail"][0];
	
	$givenname=$hash[$i]["givenname"][0];
	$title=$hash[$i]["title"][0];
	$sn=$hash[$i]["sn"][0];
	
	
	
	for($z=0;$z<$hash[$i]["memberof"]["count"];$z++){
		LinkGroups($hash[$i]["memberof"][$z],$dn);
	}
	$dn=addslashes(utf8_encode($dn));
	$displayname=addslashes(utf8_encode($displayname));
	$userprincipalname=addslashes(utf8_encode($userprincipalname));
	$samaccountname=addslashes(utf8_encode($samaccountname));
	
	$givenname=addslashes(utf8_encode($givenname));
	$title=addslashes(utf8_encode($title));
	$sn=addslashes(utf8_encode($sn));
	
	
	$GLOBALS["MEMORY_COUNT_USERS"]=$GLOBALS["MEMORY_COUNT_USERS"]+1;
	$sql[]="('$dn','$samaccountname','$mail','$userprincipalname','$displayname','$ou','$telephoneNumber','$mobile','$givenname','$title','$sn')";
	if(count($sql)>500){
		if($GLOBALS["VERBOSE"]){"echo add 500 users\n";}
		$sqlfinal=$prefix." ".@implode(",", $sql);
		$q->QUERY_SQL($sqlfinal,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n";return;}
		$sql=array();
	}

}


if(count($sql)>0){
		if($GLOBALS["VERBOSE"]){"echo add ".count($sql)." users\n";}
		$sqlfinal=$prefix." ".@implode(",", $sql);
		$q->QUERY_SQL($sqlfinal,"artica_backup");	
}

}

function LinkGroups($groupdn,$userdn){
	$q=new mysql();
	$groupdn=utf8_encode($groupdn);
	$userdn=utf8_encode($userdn);
	$mdkey=md5("$groupdn$userdn");
	$groupdn=addslashes($groupdn);
	$userdn=addslashes($userdn);
	$sql="INSERT IGNORE INTO activedirectory_groups(groupdn,userdn,mdkey) VALUES ('$groupdn','$userdn','$mdkey')";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	
}

function checksGroups(){
	$ldap=new ldapAD();
	$sql="SELECT groupdn FROM activedirectory_groups GROUP BY groupdn";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	echo mysql_num_rows($results)." groups to parse\n";
	$prefix="INSERT IGNORE INTO activedirectory_groupsNames (dn,groupname,UsersCount,description) VALUES";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		//echo $ligne["groupdn"]."\n";
		$hash=$ldap->Ldap_search($ligne["groupdn"], "objectclass=*", array());
		//echo $ligne["groupdn"]."= {$hash["count"]} items\n";
		
		
		
		for($i=0;$i<$hash["count"];$i++){
			$cn=$hash[$i]["cn"][0];
			$dn=utf8_encode($hash[$i]["dn"]);
			$cn=utf8_encode($cn);
			$UsersCount=$hash[$i]["member"]["count"];
			$description=utf8_encode($hash[$i]["description"][0]);
			$dn=addslashes($dn);
			$cn=addslashes($cn);
			$description=addslashes($description);
			$GLOBALS["MEMORY_COUNT_GROUPS"]=$GLOBALS["MEMORY_COUNT_GROUPS"]+1;
			$sqli[]="('$dn','$cn',$UsersCount,'$description')";	
			if(count($sqli)>500){
				$sqlfinal=$prefix." ".@implode(",", $sqli);
				$q->QUERY_SQL($sqlfinal,"artica_backup");
				if(!$q->ok){echo $q->mysql_error."\n";return;}
				$sqli=array();
			}
		}
		
	}
	
	if(count($sqli)>0){
		$sqlfinal=$prefix." ".@implode(",", $sqli);
		$q->QUERY_SQL($sqlfinal,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n";return;}
		$sql=array();
	}	
}



