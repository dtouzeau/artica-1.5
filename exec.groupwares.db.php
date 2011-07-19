<?php
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.apache.inc');
	include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
	include_once(dirname(__FILE__).'/ressources/class.pdns.inc');
	include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/ressources/class.joomla.php');
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	
	
$ou=$argv[1];
if(is_base64_encoded($ou)){$ou=base64_decode($ou);}
	
if($ou==null){die("No organisation defined\n");}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}



$ldap=new clladp();
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
$attr=array();
$sr=@ldap_search($ldap->ldap_connection,"ou=www,ou=$ou,dc=organizations,$ldap->suffix",$pattern,$attr);
if(!$sr){
	echo "Starting......: database LDAP ERROR $ldap->ldap_last_error\n";
	return ;
}


$hash=ldap_get_entries($ldap->ldap_connection,$sr);	

for($i=0;$i<$hash["count"];$i++){
	
	$root=$hash[$i]["apachedocumentroot"][0];
	$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
	$apacheservername=trim($hash[$i]["apacheservername"][0]);
	echo "\nStarting......: Groupware checking $apacheservername host ($wwwservertype)\n";
		$dn=$hash[$i]["dn"];
		$server_database=str_replace(".","_",$apacheservername);
		$server_database=str_replace("-","_",$server_database);	
		
		
		if($wwwservertype=="ROUNDCUBE"){
			echo "Starting......: database ROUNDCUBE DATABASE $server_database for $ou\n";
			ROUNDCUBE($server_database,$ou);
		}

}


function ROUNDCUBE($database,$ou){
	
	$ldap=new clladp();
	$users=$ldap->hash_users_ou($ou);
	if(!is_array($users)){return;}
	reset($users);
	while (list ($uid, $displayname) = each ($users) ){
		if($uid==null){continue;}
		echo "Starting......: Groupware Checking roundcube $database:: $uid - $displayname\n";
		
		$id=ROUNDCUBE_GetidFromUser($database,$uid);
		$u=new user($uid);
		if($id==0){
			ROUNDCUBE_CreateRoundCubeUser($database,$uid,$u->mail,'127.0.0.1');
			$id=ROUNDCUBE_GetidFromUser($database,$uid);
		}
		
		if($id==0){continue;}
		$identity_id=ROUNDCUBE_GetidentityFromuser_id($database,$id);
		if($identity_id==0){
			ROUNDCUBE_CreateRoundCubeIdentity($database,$id,$displayname,$u->mail,$ou);
			$identity_id=ROUNDCUBE_GetidentityFromuser_id($database,$id);
			}
		
		if($identity_id==0){continue;}
		
		$count=$count+1;
		ROUNDCUBE_UpdateRoundCubeIdentity($bd,$identity_id,$u->mail);		
		}
	
	
	
}

function ROUNDCUBE_CreateRoundCubeUser($bd,$user_id,$email,$mailhost){
	$date=date('Y-m-d H:i:s');
	$sql="INSERT INTO `users` (`username`, `mail_host`, `language`,`created`) VALUES 
	('$user_id','127.0.0.1','en_US','$date');
	";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
	if(!$q->ok){
		echo $q->mysql_error."\n";
	}
	
}


function ROUNDCUBE_GetidFromUser($bd,$uid){
	$sql="SELECT user_id FROM users where username='$uid'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$bd);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$userid[]=$ligne["user_id"];
	}
	if(!is_array($userid)){return 0;}else{return $userid[0];}
}
function ROUNDCUBE_GetidentityFromuser_id($bd,$user_id){
	$sql="SELECT identity_id FROM identities where user_id='$user_id'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$bd);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$id[]=$ligne["identity_id"];
	}
	
	if(!is_array($id)){return 0;}else{return $id[0];}
}
function ROUNDCUBE_CreateRoundCubeIdentity($bd,$user_id,$DisplayName,$email,$ou){
	$sql="INSERT INTO `identities` (
		`user_id`, 
		`del`, 
		`standard`, 
		`name`, 
		`organization`, 
		`email`, 
		`reply-to`) 
		VALUES (
		'$user_id',
		'0',
		'1',
		'$DisplayName','$ou','$email','$email');";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
}
function ROUNDCUBE_UpdateRoundCubeIdentity($bd,$identity_id,$email){
	$sql="UPDATE identities SET email='$email', `reply-to`='$email' WHERE identity_id='$identity_id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
	
}




?>