<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}}
$unix=new unix();
$apache_usr=$unix->APACHE_SRC_ACCOUNT();
$pure_pw=$unix->find_program("pure-pw");
if(strlen($pure_pw)<4){
	echo "Starting......: pure-ftpd pure-pw no such file\n";
	die();
}

if(!is_file("/etc/pure-ftpd/conf/Umask")){@file_put_contents("/etc/pure-ftpd/conf/Umask","173 072");}


$ldap=new clladp();
if($ldap->ldapFailed){
	echo "Starting......: pure-ftpd ldap failed\n";
	die();
}


		$attr=array();
		$pattern="(&(objectClass=PureFTPdUser)(FTPStatus=TRUE))";
		$sr=@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		if(!$sr){
			echo "Starting......: pure-ftpd (&(objectClass=PureFTPdUser)(FTPStatus=TRUE)) Failed\n";
			die();
		}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		
		if($hash["count"]==0){
			echo "Starting......: pure-ftpd no users\n";
			die();
		}
		
		if($hash["count"]>0){
			for($i=0;$i<$hash["count"];$i++){
				$homedirectory=$hash[$i]["homedirectory"][0];
				$FTPDownloadBandwidth=$hash[$i][strtolower("FTPDownloadBandwidth")][0];
				$FTPUploadBandwidth=$hash[$i][strtolower("FTPUploadBandwidth")][0];
				$FTPQuotaFiles=$hash[$i][strtolower("FTPQuotaFiles")][0];
				$FTPQuotaMBytes=$hash[$i][strtolower("FTPQuotaMBytes")][0];
				$FTPUploadRatio=$hash[$i][strtolower("FTPUploadRatio")][0];
				$FTPDownloadRatio=$hash[$i][strtolower("FTPDownloadRatio")][0];
				$userPassword=$hash[$i][strtolower("userPassword")][0];
				$uid=$hash[$i]["uid"][0];
				$user="ftp";
				if(strpos($homedirectory,"/www/")>0){$user=$apache_usr;}
				
				
				
				$opts[]="(echo \"$userPassword\";echo \"$userPassword\")|$pure_pw useradd $uid  -u $user -g $user -d \"$homedirectory\"";
				if($FTPDownloadBandwidth<>null){$opts[]="-t $FTPDownloadBandwidth";}
				if($FTPUploadBandwidth<>null){$opts[]="-T $FTPUploadBandwidth";}
				if($FTPQuotaFiles<>null){$opts[]="-n $FTPQuotaFiles";}
				if($FTPQuotaMBytes<>null){$opts[]="-N $FTPQuotaMBytes";}
				if($FTPUploadRatio<>null){$opts[]="-q $FTPUploadRatio";}
				if($FTPDownloadRatio<>null){$opts[]="-Q $FTPDownloadRatio";}
				
				
				if(!is_dir($homedirectory)){@mkdir($homedirectory,0755,true);}
				if(is_dir($homedirectory)){
					@chmod($homedirectory,755);
					@chown($homedirectory,$user);
				}
				$opts[]="-f /etc/pureftpd.passwd";
				$opts[]="-m /etc/pure-ftpd/pureftpd.pdb";
				$cmds[]=@implode(" ",$opts);
				unset($opts);
				
			}
			
		}
		
echo "Starting......: pure-ftpd ". count($cmds)." users\n";
if(!is_array($cmds)){return null;}
while (list ($num, $val) = each ($cmds) ){
	if(trim($val)==null){continue;}
	if($GLOBALS["VERBOSE"]){echo $val."\n";}
	shell_exec("$val >/dev/null 2>&1");
}
if(!is_file("/etc/pure-ftpd/pureftpd.pdb")){
	shell_exec("$pure_pw mkdb /etc/pure-ftpd/pureftpd.pdb -f /etc/pureftpd.passwd");
}

