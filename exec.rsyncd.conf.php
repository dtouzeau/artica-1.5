<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.rsync.inc');


$ini=new Bs_IniHandler();
$pid=getmypid();
if($argv[1]=="--no-reboot"){$GLOBALS["NOREBOOT"]=true;}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

$_GET["RsyncStoragePath"]=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/RsyncStoragePath"));
if($_GET["RsyncStoragePath"]==null){$_GET["RsyncStoragePath"]="/var/spool/rsync";}


create_password_files();


echo "Search user that EnableBackupAccount is enabled\n";
$array=GetUserList();
if(!is_array($array)){
	echo "No users...\n";
	die();
}

while (list ($organisation, $users) = each ($array) ){
	echo "Parsing organization $organisation\n";
	BuildConfig($organisation,$users);
}


function BuildConfig($organisation,$users){
	@mkdir("/etc/rsync/secrets/$organisation",null,true);
	$rsyncd=new rsyncd_conf();
	
	while (list ($num, $user) = each ($users) ){
		$uid=$user["uid"];
		$RsyncBackupTargetPath=$user["RsyncBackupTargetPath"];
		if($RsyncBackupTargetPath==null){$RsyncBackupTargetPath=$_GET["RsyncStoragePath"];}
		echo "Parsing user number $num ($uid)\n";
		$path_name=md5(strtolower("$organisation$uid"));
		$path="$RsyncBackupTargetPath/organizations/$organisation/$uid";
		@mkdir($path,null,true);
		$auth_users=$uid;
		$rsyncd_secrets[]="$uid:{$user["password"]}";
		
		$rsyncd->main_array[$path]["NAME"]=$path_name;
		$rsyncd->main_array[$path]["auth users"]=$uid;
		$rsyncd->main_array[$path]["comment"]="$uid in $organisation";
		$rsyncd->main_array[$path]["auth users"]=$uid;
		$rsyncd->main_array[$path]["secrets file"]="/etc/rsync/secrets/$organisation/rsyncd.$uid.secrets";

		}

			
		@file_put_contents("/etc/rsync/secrets/$organisation/rsyncd.$uid.secrets",implode("\n",$rsyncd_secrets));	
		@chmod("/etc/rsync/secrets/$organisation/rsyncd.$uid.secrets",0600);

		$rsyncd->save();
	
	
}

		

function GetUserList(){
		$ldap=new clladp();
				$attr=array("uid","userPassword","RsyncBackupTargetPath");
				$pattern="(&(objectclass=userAccount)(EnableBackupAccount=1))";
				$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$pattern,$attr);
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				if($hash["count"]==0){
				echo "No users found...\n";
				return null;
				}
		
				
		for($i=0;$i<$hash["count"];$i++){
			$dn=$hash[$i]["dn"];
			$password=$hash[$i]["userpassword"][0];
			$RsyncBackupTargetPath=$hash[$i][strtolower("RsyncBackupTargetPath")][0];
			$uid=$hash[$i]["uid"][0];
			if(!preg_match('#ou=users,ou=(.+?),dc=organizations#',$dn,$re)){continue;}
			$ou=$re[1];
			$array[trim($re[1])][]=array("uid"=>$uid,"password"=>$password,"RsyncBackupTargetPath"=>$RsyncBackupTargetPath);
			
			
			
		}	
		
		return $array;
	
}


function create_password_files(){
	$rsyncd=new rsyncd_conf();
	
	while (list ($path, $key) = each ($rsyncd->main_array) ){
		if($key["secrets file"]==null){
			writelogs($path." no secret path, skip",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		
		@mkdir(dirname($key["secrets file"]),null,true);
		$users=explode(",",$key["auth users"]);
		if(!is_array($users)){continue;}
			while (list ($index, $uid) = each ($users) ){
				if($uid==null){continue;}
				$userClass=new user($uid);
				writelogs($path." set secret file for $uid",__FUNCTION__,__FILE__,__LINE__);
				$rsyncd_secrets[]="$uid:$userClass->password";
			}
			
		if(is_array($rsyncd_secrets)){
			@file_put_contents($key["secrets file"],implode("\n",$rsyncd_secrets));
			@chmod($key["secrets file"],0600);
			unset($rsyncd_secrets);
		}
		
	}
	$rsyncd->save();
	
}


?>