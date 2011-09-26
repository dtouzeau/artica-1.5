<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["AS_ROOT"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.lvm.org.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.groups.inc');
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--users'){CountDeUsers();die();}
if($argv[1]=='--fix-etc-hosts'){fixEtcHosts();die();}
if($argv[1]=='--sslbridge'){sslbridgre();die();}
if($argv[1]=='--ads-destroy'){ads_destroy();die();}
if($argv[1]=='--ads'){activedirectory();kinit();die();}
if($argv[1]=='--ping-ads'){activedirectory_ping();die();}
if($argv[1]=='--logon-scripts'){LogonScripts();die();}
if($argv[1]=='--administrator'){administrator_update();die();}
if($argv[1]=='--loglevel'){set_log_level($argv[2]);die();}
if($argv[1]=='--quotas-recheck'){quotasrecheck();die();}
if($argv[1]=='--quotas-recheck'){quotasrecheck();die();}
if($argv[1]=='--ldap-groups'){ldap_groups();die();}
if($argv[1]=='--testjoin'){test_join();die();}
if($argv[1]=='--recycles'){recycles();die();}
if($argv[1]=='--trash-restore'){recycles_restore();die();}
if($argv[1]=='--trash-delete'){recycles_delete();die();}
if($argv[1]=='--trash-scan'){ScanTrashs();die();}
if($argv[1]=='--check-privs'){recycles_privileges($argv[2],$argv[3]);die();}
if($argv[1]=='--smbstatus'){smbstatus_injector();die();}




if($argv[1]=='--help'){help_output();die();}

function help_output(){
	echo "--users...........: Save users number in cache\n";
	echo "--fix-etc-hosts...: Fix hostname in /etc/hosts\n";
	echo "--ads-destroy.....: Destroy Active directory connection\n";
	echo "--ads.............: Create Active directory connection\n";
	echo "--ping-ads........: refresh Active Directory connection\n";
	echo "--logon-scripts...: Perform logon scripts installation\n";
	echo "--administrator...: update administrator informations\n";
	echo "--loglevel........: Set log level (1-10)\n";
	echo "--quotas-recheck..: re-check filesystem quotas\n";
	echo "--ldap-groups.....: re-check groups LDAP population\n";
	
}


$unix=new unix();
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
if($unix->process_exists($oldpid)){
	writelogs(basename(__FILE__).":Already executed PID: $oldpid.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}




if($argv[1]=='--home'){CheckHomeFor($argv[2],null);die();}
if($argv[1]=='--homes'){ParseHomeDirectories();die();}

if($argv[1]=='--reconfigure'){reconfigure();die();}
if($argv[1]=='--samba-audit'){SambaAudit();die();}

if($argv[1]=='--check-dirs'){CheckExistentDirectories();die();}

if($argv[1]=='--build'){build();die();}

if($argv[1]=='--disable-profiles'){DisableProfiles();die();}
if($argv[1]=='--enable-profiles'){EnableProfiles();	die();}

if($argv[1]=='--fix-lmhost'){fix_lmhosts();die();}
if($argv[1]=='--fix-HideUnwriteableFiles'){fix_hide_unwriteable_files();die();}
if($argv[1]=='--usb-mount'){usb_mount($argv[2],$argv[3]);exit;}
if($argv[1]=='--usb-umount'){usb_umount($argv[2],$argv[3]);exit;}
if($argv[1]=='--smbtree'){smbtree();exit;}



$users=new usersMenus();
if(!$users->SAMBA_INSTALLED){echo "Samba is not installed\n";die();}

FixsambaDomainName();

function FixsambaDomainName(){
	$smb=new samba();
	$workgroup=$smb->main_array["global"]["workgroup"];
	$smb->CleanAllDomains($workgroup);
	}
	
function ldap_groups(){
	$gp=new groups();
	$gp->EditSambaGroups();
	$gp->SambaGroupsBuild();
	$gp->CreateGuestUser();
}	

function LoadConfs(){
$sock=new sockets();
$ArticaSambaAutomAskCreation=$sock->GET_INFO("ArticaSambaAutomAskCreation");
$HomeDirectoriesMask=$sock->GET_INFO("HomeDirectoriesMask");
$SharedFoldersDefaultMask=$sock->GET_INFO("SharedFoldersDefaultMask");
if(!is_numeric($ArticaSambaAutomAskCreation)){$ArticaSambaAutomAskCreation=1;}
if(!is_numeric($HomeDirectoriesMask)){$HomeDirectoriesMask=0775;}
if(!is_numeric($SharedFoldersDefaultMask)){$SharedFoldersDefaultMask=0755;}	
$GLOBALS["HomeDirectoriesMask"]=$HomeDirectoriesMask;
$GLOBALS["ArticaSambaAutomAskCreation"]=$ArticaSambaAutomAskCreation;
$GLOBALS["SharedFoldersDefaultMask"]=$SharedFoldersDefaultMask;
}


function ParseHomeDirectories(){
		if(!isset($GLOBALS["HomeDirectoriesMask"])){LoadConfs();}
		$ldap=new clladp();
		$profile_path=null;
		$attr=array("homeDirectory","uid","dn");
		$pattern="(&(objectclass=sambaSamAccount)(uid=*))";
		$sock=new sockets();
		
		if(trim($profile_path)==null){$profile_path="/home/export/profile";}	
		$sock=new sockets();
		$SambaRoamingEnabled=$sock->GET_INFO('SambaRoamingEnabled');
		if($SambaRoamingEnabled==1){EnableProfiles();}else{DisableProfiles();}			
		
		$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,".$ldap->suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		$sock=new sockets();
		for($i=0;$i<$hash["count"];$i++){
			$dn=$hash[$i]["dn"];
			$uid=$hash[$i]["uid"][0];
			$homeDirectory=$hash[$i][strtolower("homeDirectory")][0];
			writelogs("loading: {$hash[$i]["uid"][0]}",__FUNCTION__,__FILE__,__LINE__);
			if(preg_match("#ou=users,dc=samba,dc=organizations#",$dn)){writelogs("$uid:No a standard user...SKIP",__FUNCTION__,__FILE__,__LINE__);continue;}
			if($uid==null){writelogs("uid is null, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			if($uid=="nobody"){writelogs("uid is nobody, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			if($uid=="root"){writelogs("uid is root, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			if(substr($uid,strlen($uid)-1,1)=='$'){writelogs("$uid:This is a computer, SKIP ",__FUNCTION__,__FILE__,__LINE__);continue;}
			writelogs("-> CheckHomeFor($uid,$homeDirectory)",__FUNCTION__,__FILE__,__LINE__);
			CheckHomeFor($uid,$homeDirectory);
			}
		}
		
function CheckHomeFor($uid,$homeDirectory=null){
	if(!isset($GLOBALS["HomeDirectoriesMask"])){LoadConfs();}
	$ct=new user($uid);
	if($homeDirectory==null){$homeDirectory=$ct->homeDirectory;}
	
	echo "Starting......: Home $uid checking home: $homeDirectory\n";
	
	if($GLOBALS["profile_path"]==null){
		$sock=new sockets();
		$profile_path=$sock->GET_INFO('SambaProfilePath');
		$GLOBALS["profile_path"]=$profile_path;
	}
	if($ct->ou==null){writelogs("$uid: OU=NULL, No a standard user...SKIP",__FUNCTION__,__FILE__,__LINE__);return;}
	$ou=$ct->ou;
	$uid=strtolower($uid);
	$newdir=trim(getStorageEnabled($ou,$uid));
	if($newdir<>null){
		$newdir="$newdir/$uid";
		writelogs("LVM: [$ou]:: storage=$newdir;homeDirectory=$homeDirectory",__FUNCTION__,__FILE__,__LINE__);
		if($newdir<>$homeDirectory){
			writelogs("$uid:: change $homeDirectory to $newdir",__FUNCTION__,__FILE__,__LINE__);
			$ct->homeDirectory=$newdir;
			$ct->edit_system();
			$homeDirectory=$newdir;
		}
	}
	
if($homeDirectory==null){
	$homeDirectory="/home/$uid";
	writelogs("$uid:: change $homeDirectory",__FUNCTION__,__FILE__,__LINE__);
	$ct->homeDirectory=$homeDirectory;
	$ct->edit_system();
	}
	
	if($GLOBALS["profile_path"]<>null){
		$export="$profile_path/$uid";
		writelogs("Checking export:$export",__FUNCTION__,__FILE__,__LINE__);
		@mkdir($export);
		@chmod($export,0775);
		@chown($export,$uid);
	}
	
	
	writelogs("Checking home:$homeDirectory",__FUNCTION__,__FILE__,__LINE__);

	@mkdir($homeDirectory);
	if($GLOBALS["ArticaSambaAutomAskCreation"]==1){
		shell_exec("/bin/chmod {$GLOBALS["HomeDirectoriesMask"]} $homeDirectory");
	}
	@chown($homeDirectory,$uid);
	
	if($ct->WebDavUser==1){
		$unix=new unix();
		$find=$unix->find_program("find");
		$apacheuser=$unix->APACHE_GROUPWARE_ACCOUNT();
		if(preg_match("#(.+?):#",$apacheuser,$re)){$apacheuser=$re[1];}
		$internet_folder="$homeDirectory/Internet Folder";
		if(!is_dir($internet_folder)){@mkdir($internet_folder,$GLOBALS["SharedFoldersDefaultMask"],true);}else{
		@chmod($internet_folder,$GLOBALS["SharedFoldersDefaultMask"]);
		}
		$internet_folder=$unix->shellEscapeChars($internet_folder);
		echo "Starting......: Home $uid checking home: $internet_folder\n";
		writelogs("Checking $ct->uid:$apacheuser :$internet_folder",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/bin/chown -R $ct->uid:$apacheuser $internet_folder >/dev/null 2>&1 &");
		shell_exec("$find $internet_folder -type d -exec chmod {$GLOBALS["SharedFoldersDefaultMask"]} {} \; >/dev/null 2>&1 &");
	}
	
	
	
}

function getStorageEnabled($ou,$uid){
if($GLOBALS["LVM_$ou"]==null){
		$lvm=new lvm_org($ou);
		if($lvm->storage_enabled<>null){
			writelogs("Checking $ou:$lvm->storage_enabled subdir=$lvm->OuBackupStorageSubDir",__FUNCTION__,__FILE__,__LINE__);
			$GLOBALS["LVM_$ou"]="$lvm->storage_enabled";
			$GLOBALS["LVM_{$ou}_subdir"]="$lvm->OuBackupStorageSubDir";
		}
	}
	
	$storage_enabled=trim($GLOBALS["LVM_$ou"]);
	$OuBackupStorageSubDir=trim($GLOBALS["LVM_{$ou}_subdir"]);
	if($storage_enabled==null){return null;}	
	
	if($GLOBALS[$storage_enabled]<>null){
		$storage_mounted=$GLOBALS[$storage_enabled];
	 }else{	
		$sock=new sockets();
		$storage_mounted=trim(base64_decode($sock->getFrameWork("cmd.php?get-mounted-path=".base64_encode($storage_enabled))));
		if($storage_mounted<>null){$storage_mounted="$storage_mounted/$OuBackupStorageSubDir";}
		$GLOBALS[$storage_enabled]=$storage_mounted;
	 }
	 
	 return $storage_mounted;
	 
}

		
function DisableProfiles(){
	$ldap=new clladp();
	$pattern="(&(objectclass=sambaSamAccount)(sambaProfilePath=*))";
	$attr=array("sambaProfilePath","uid","dn");
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i][strtolower("uid")][0];
		$dn=$hash[$i][strtolower("dn")];
		$sambaProfilePath=$hash[$i][strtolower("sambaProfilePath")][0];
		$upd["sambaProfilePath"]=$sambaProfilePath;
		$ldap->Ldap_del_mod($dn,$upd);
		}
}
function EnableProfiles(){
		$ldap=new clladp();
		$sock=new sockets();
		$smb=new samba();
		$upd=array();
		$SambaAdminServerDefined=$sock->GET_INFO("SambaAdminServerDefined");
		
		$SAMBA_HOSTNAME=$smb->main_array["global"]["netbios name"];
		$SAMBA_IP=gethostbyname($SAMBA_HOSTNAME);
		if(trim($SAMBA_IP)==null){$SAMBA_IP=$SAMBA_HOSTNAME;}
		if(trim($SAMBA_IP)=="127.0.0.1"){$SAMBA_IP=$SAMBA_HOSTNAME;}
		if(trim($SAMBA_IP)=="127.0.1.1"){$SAMBA_IP=$SAMBA_HOSTNAME;}
		if(trim($SAMBA_IP)=="127.0.0.2"){$SAMBA_IP=$SAMBA_HOSTNAME;}		
		if($SambaAdminServerDefined<>null){$SAMBA_IP=$SambaAdminServerDefined;}
		$profile_path=$sock->GET_INFO('SambaProfilePath');
		if(trim($profile_path)==null){$profile_path="/home/export/profile";}
		$profile_base=basename($profile_path);	
		
		$attr=array("dn","uid","SambaProfilePath");
		$pattern="(&(objectclass=sambaSamAccount)(uid=*))";	
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$uid=$hash[$i]["uid"][0];
			$SambaProfilePath=$hash[$i][strtolower("SambaProfilePath")][0];
			
			if(strpos($uid,'$')>0){continue;}
			$dn=$hash[$i]["dn"];
			
			if(preg_match("#127\.0\.#",$SambaProfilePath)){
				echo "$SambaProfilePath no match change it\n";
				$upd["SambaProfilePath"][0]='\\\\' .$SAMBA_IP. '\\'.$profile_base.'\\' . $uid;
				$ldap->Ldap_modify($dn,$upd);
			}
			if(!is_dir("$profile_path/$uid")){@mkdir("$profile_path/$uid");}
			@chmod("$profile_path/$uid",0755);
			shell_exec("/bin/chown $uid $profile_path/$uid");
			
		}	
		
}

function build(){
	reconfigure();
	
}

function CheckFilesAndDirectories(){
	if(is_file("/var/lib/samba/usershares/data")){@unlink("/var/lib/samba/usershares/data");}
	if(!is_dir("/var/lib/samba/usershares/data")){@mkdir("/var/lib/samba/usershares/data",0644,true);}
	@chmod("/var/lib/samba/usershares/data",1644);
	shell_exec("/bin/chown root:root /var/lib/samba/usershares/data");	
	
}


function reconfigure(){
	if($GLOBALS["VERBOSE"]){writelogs("starting reconfigure()",__FUNCTION__,__FILE__,__LINE__);}
	$unix=new unix();
	$sock=new sockets();
	if($GLOBALS["VERBOSE"]){writelogs("->clladp()",__FUNCTION__,__FILE__,__LINE__);}
	$ldap=new clladp();
	$smbpasswd=$unix->find_program("smbpasswd");
	if($GLOBALS["VERBOSE"]){writelogs("smbpasswd=$smbpasswd -->samba()",__FUNCTION__,__FILE__,__LINE__);}
	$samba=new samba();
	$net=$unix->LOCATE_NET_BIN_PATH();	
	$ldap_passwd=$ldap->ldap_password;
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	$EnableSambaRemoteLDAP=$sock->GET_INFO("EnableSambaRemoteLDAP");
	
	if($EnableSambaRemoteLDAP==1){
		$SambaRemoteLDAPInfos=unserialize(base64_decode($sock->GET_INFO("SambaRemoteLDAPInfos")));
		$ldap_passwd=$SambaRemoteLDAPInfos["user_dn_password"];
	}
	

	
	if($EnableSambaActiveDirectory==1){activedirectory();}
	CheckFilesAndDirectories();
	FixsambaDomainName();
	echo "Starting......: Samba building main configuration...\n";
	@file_put_contents("/etc/samba/smb.conf",$samba->BuildConfig());
	echo "Starting......: Samba $smbpasswd -w ****\n";
	shell_exec("$smbpasswd -w \"$ldap_passwd\"");

	SambaAudit();
	fixEtcHosts();
	
	$master_password=$samba->GetAdminPassword("administrator");
	$SambaEnableEditPosixExtension=$sock->GET_INFO("SambaEnableEditPosixExtension");
	if($SambaEnableEditPosixExtension==1){
		$cmd="$net idmap secret {$samba->main_array["global"]["workgroup"]} \"$ldap_passwd\" >/dev/null 2>&1 &";
		shell_exec($cmd);
		$cmd="$net idmap secret alloc \"$ldap_passwd\" >/dev/null 2>&1 &";
		shell_exec($cmd);
	}
	
	if($EnableSambaActiveDirectory==1){kinit();}
	shell_exec($unix->LOCATE_APACHE_BIN_PATH()." /usr/share/artica-postfix/exec.pam.php --build");
	 
	
	$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ".__FILE__." --check-dirs");
	$unix->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --samba-reconfigure");
	reload();
	
	
	}
	
function reload(){
	$unix=new unix();
	
	$pidof=$unix->find_program("pidof");
	$smbd=$unix->find_program("smbd");
	$winbindd=$unix->find_program("winbindd");
	$kill=$unix->find_program("kill");
	exec("$pidof $smbd 2>&1",$results);
	echo "Starting......: samba reloading smbd:$smbd...\n";
	$tbl=explode(" ",@implode(" ",$results));
	while (list ($index, $pid) = each ($tbl) ){
		$pid=trim($pid);
		if(!is_numeric($pid)){continue;}
		if($pid<10){continue;}
		echo "Starting......: samba reloading smbd pid: $pid\n";
		shell_exec("/bin/kill -HUP $pid 2>&1 >/dev/null");
	}
	$results=array();
	exec("$pidof winbindd 2>&1",$results);
	echo "Starting......: samba reloading winbindd:$smbd...\n";
	$tbl=explode(" ",@implode(" ",$results));
	while (list ($index, $pid) = each ($tbl) ){
		$pid=trim($pid);
		if(!is_numeric($pid)){continue;}
		if($pid<10){continue;}
		echo "Starting......: samba reloading winbindd pid: $pid\n";
		shell_exec("/bin/kill -HUP $pid 2>&1 >/dev/null");
	}	
	
}
	
function fixEtcHosts(){
		$sock=new sockets();
		$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
		$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
		if($DisableEtcHosts==1){
			echo "Starting......: DisableEtcHosts is active, skip this procedure\n";
			writelogs("/etc/hosts:: DisableEtcHosts is active, skip this procedure",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return;
		}

		
	$unix=new unix();
	$hostname_bin=$unix->find_program("hostname");
	exec($hostname_bin,$hostname_array);
	$hostname=trim(@implode("",$hostname_array));
	if(preg_match("#^(.+?)\.(.+?)$#",$hostname,$re)){
		$hostname=$re[1];
		$domainname=$re[2];
	}

	$hostnameConfig=$sock->GET_INFO("myhostname");
	if(preg_match("#^(.+?)\.(.+?)$#",$hostnameConfig,$re)){
		$hostname_config=$re[1];
		$domainname_config=$re[2];
	}	
	
	if($hostname_config<>$hostname){$hostname=$hostname_config;}
	if($EnableSambaActiveDirectory==1){
		$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
		$domainname_config=$config["ADDOMAIN"];
	}
	
	if($domainname<>$domainname_config){$domainname=$domainname_config;}
	
	if($hostname==null){$hostname="localhost";}
	if($domainname==null){$domainname="localdomain";}
	
	echo "Starting......: hostname is $hostname and local domain is $domainname\n";
	
	shell_exec("$hostname_bin \"$hostname.$domainname\"");
	
	$f=explode("\n",@file_get_contents("/etc/hosts"));
	$preg_domainname=str_replace(".","\.",$domainname);
	$found=false;
	$foundMyhostanme=false;
	$domainname=str_replace("localhost.localdomain","localdomain",$domainname);
	if($domainname==null){$domainname="localdomain";}
	
	$mod=false;
	while (list ($index, $line) = each ($f) ){
		if(trim($line)==null){continue;}
		if(preg_match("#127\.0\.0\.1\s+localhost\s+#",$line)){
			echo "Starting......: bad localhost entry found in /etc/hosts line $index\n";
			$found=false;
			$mod=true;
			continue;
		}
		
		if(!$found){
			if(preg_match("#127\.0\.0\.1\s+$hostname\.$domainname\s+.+?localhost#",$line)){
				echo "Starting......: $hostname.$domainname + localhost entry found in /etc/hosts line $index\n";
				if($index>0){
					echo "Starting......: this is not in the first line..\n";
					unset($f[$index]);
					array_unshift($f,$line);
					echo "Starting......: saving ". count($f)." lines in /etc/hosts\n";
					@file_put_contents("/etc/hosts",@implode("\n",$f)."\n");
					return;
				}
				
				$found=true;
				continue;
			}
		}
		if(preg_match("#127\.0\.0\.1\s+localhost\s+localhost#", $line)){$mode=true;}
		if(preg_match("#127\.0\.0\.1\s+localhost#", $line)){$mode=true;}
		
	}
	
	reset($f);

	
	$line='';
	$already=array();
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#127\.0\.0\.1\s+localhost\s+#", $line)){if($GLOBALS["VERBOSE"]){echo "Starting......: skip line \"$line\"\n";}continue;}			
		if(preg_match("#127\.0\.0\.1\s+$hostname\s+#", $line)){if($GLOBALS["VERBOSE"]){echo "Starting......: skip line \"$line\"\n";}continue;}
		if(preg_match("#127\.0\.0\.1\s+$hostname\.$domainname$#", $line)){if($GLOBALS["VERBOSE"]){echo "Starting......: skip line \"$line\"\n";}continue;}
		if(trim($line)==null){if($GLOBALS["VERBOSE"]){echo "Starting......: skip line \"$line\"\n";}continue;}
		if(isset($already[$line])){continue;}
		
		//writelogs("/etc/hosts:: line \"$line\"\n",__FUNCTION__,__FILE__,__LINE__);
		$newf[]=$line;
		$already[$line]=true;
	}
	
	if(!$found){
		echo "Starting......: localhost is not found in /etc/hosts\n";
		array_unshift($newf,"127.0.0.1\t$hostname.$domainname\t$hostname\tlocalhost");
		$mod=true;
	}
	
	
	if($mod){
		echo "Starting......: saving ". count($newf)." lines in /etc/hosts\n";
		@file_put_contents("/etc/hosts",$prefix.@implode("\n",$newf)."\n");
	}
	
}




function ads_destroy(){
	$unix=new unix();
	$net=$unix->LOCATE_NET_BIN_PATH();
	$kdestroy=$unix->find_program("kdestroy");
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$adminpassword=$config["PASSWORD"];
	$adminpassword=$unix->shellEscapeChars($adminpassword);
	$cmd="$net ads leave -U {$config["ADADMIN"]}%$adminpassword 2>&1";
	echo "Starting......: Samba remove connection\n";
	if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
	exec("$cmd",$results);
	while (list ($index, $line) = each ($results) ){if(trim($line)==null){continue;}echo "Starting......: Samba $results\n";}
	unset($results);	
	if($GLOBALS["VERBOSE"]){echo $kdestroy."\n";}
	exec("$kdestroy 2>&1",$results);
	while (list ($index, $line) = each ($results) ){if(trim($line)==null){continue;}echo "Starting......: Samba $results\n";}
}
	
	
function kinit(){
	$unix=new unix();
	$kinit=$unix->find_program("kinit");
	$echo=$unix->find_program("echo");
	$net=$unix->LOCATE_NET_BIN_PATH();
	$hostname=$unix->find_program("hostname");
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$domain=strtoupper($config["ADDOMAIN"]);
	$domain_lower=strtolower($config["ADDOMAIN"]);
	$cachefile="/etc/artica-postfix/NetADSInfo.cache";
	$CyrusToAD=$sock->GET_INFO("CyrusToAD");
	if(!is_numeric($CyrusToAD)){$CyrusToAD=0;}
	$ADSERVER_IP=$config["ADSERVER_IP"];
	@unlink("/etc/artica-postfix/NetADSInfo.cache");
	
	$ad_server=strtolower($config["ADSERVER"]);
	$kinitpassword=$config["PASSWORD"];
	$kinitpassword=$unix->shellEscapeChars($kinitpassword);
	
	if($kinit<>null){	
		shell_exec("$echo $kinitpassword|$kinit {$config["ADADMIN"]}@$domain");
	}
	
	
	exec($hostname,$results);
	$servername=trim(@implode(" ",$results));
	echo "Starting......: Samba using server name has $servername.$domain_lower\n";
	shell_exec("/usr/share/artica-postfix/bin/artica-install --change-hostname $servername.$domain_lower");
	echo "Starting......: connecting to $ad_server.$domain_lower\n";
	@unlink($cachefile);
	
	$NetADSINFOS=$unix->SAMBA_GetNetAdsInfos();
	$KDC_SERVER=$NetADSINFOS["KDC server"];
	$adminpassword=$config["PASSWORD"];
	
	$WINBINDPASSWORD=$config["WINBINDPASSWORD"];
	if(strlen($WINBINDPASSWORD)>2){
		$WINBINDPASSWORD=$unix->shellEscapeChars($WINBINDPASSWORD);
		exec("$net setauthuser -U winbind%$WINBINDPASSWORD 2>&1",$results);
		while (list ($index, $line) = each ($results) ){writelogs("setauthuser [winbind]: $line",__FUNCTION__,__FILE__,__LINE__);}
	}else{
		exec("$net setauthuser -U {$config["ADADMIN"]}%$kinitpassword 2>&1",$results);
	}
	
	echo "Starting......: checking winbindd daemon...\n";
	shell_exec("/etc/init.d/artica-postfix start winbindd");
	
	$adminpassword=$unix->shellEscapeChars($adminpassword);
	
	
	
	if($KDC_SERVER==null){
		$cmd="$net ads join -W $ad_server.$domain_lower -S $ad_server -U {$config["ADADMIN"]}%$adminpassword 2>&1";
		if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
		
		exec("$cmd",$results);
		
		while (list ($index, $line) = each ($results) ){
			writelogs("ads join [{$config["ADADMIN"]}]: $line",__FUNCTION__,__FILE__,__LINE__);
			
			if(preg_match("#DNS update failed#",$line)){
				echo "Starting......: ADS Join FAILED with command line \"$cmd\"\n";
			}
			
			if(preg_match("#The network name cannot be found#",$line)){
				echo "Starting......: ADS Join $ad_server.$domain_lower failed, unable to resolve it\n";
				if($ADSERVER_IP<>null){
					if(!$GLOBALS["CHANGE_ETC_HOSTS_AD"]){
						$line=base64_encode("$ADSERVER_IP\t$ad_server.$domain_lower\t$ad_server");
						$sock->getFrameWork("cmd.php?etc-hosts-add=$line");
						$GLOBALS["CHANGE_ETC_HOSTS_AD"]=true;
						echo "Starting......: ADS Join add $ad_server.$domain_lower $ADSERVER_IP in hosts file done, restart\n";
						kinit();
						return;
					}
				}
			}
			
			echo "Starting......: ADS Join $ad_server.$domain_lower ($line)\n";
		}
	}else{
		echo "Starting......: ADS Already joined to \"$KDC_SERVER\"\n";
	}
	
	
	
	if($CyrusToAD==1){
		echo "Starting......: Activate PAM for Cyrus sasl\n";
		EnablePamd();
	}else{
		echo "Starting......: Disable PAM for Cyrus sasl\n";
		DisablePamd();
	}
	
}

function activedirectory(){
	include_once(dirname(__FILE__)."/ressources/class.kdc.inc");
	$sock=new sockets();
	$EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
	if(!is_numeric("$EnableKerbAuth")){$EnableKerbAuth=0;}	
	if($EnableKerbAuth==1){
		$verbosed=null;
		if($GLOBALS["VERBOSE"]){$verbosed=" --verbose";}
		$unix=new unix();
		$cmd=$unix->LOCATE_PHP5_BIN()." ". dirname(__FILE__)."/exec.kerbauth.php --build$verbosed";
		echo "Enable Kerberos authentification is enabled, executing kerberos auth\n";
		shell_exec($cmd);
	
	}
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$kdc=new kdc();
	$kdc->suffix_domain=$config["ADDOMAIN"];
	$kdc->netbios_servername=$config["ADSERVER"];
	$kdc->administrator=$config["ADADMIN"];
	$kdc->wintype=$config["WINDOWS_SERVER_TYPE"];
	$kdc->build();	
	
	
	
	
}

function activedirectory_ping(){
	$sock=new sockets();
	$unix=new unix();
	$filetime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){return;}
	if($EnableSambaActiveDirectory<>1){return;}
	$ping_dc=false;
	$time=$unix->file_time_min($filetime);
	if($time<120){
		if(!$GLOBALS["VERBOSE"]){return;}
		echo "$filetime ({$time}Mn)\n";
	}
	
	$kinit=$unix->find_program("kinit");
	$echo=$unix->find_program("echo");
	$net=$unix->LOCATE_NET_BIN_PATH();
	$wbinfo=$unix->find_program("wbinfo");
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$domain=strtoupper($config["ADDOMAIN"]);
	$domain_lower=strtolower($config["ADDOMAIN"]);

	$ADSERVER_IP=$config["ADSERVER_IP"];	
	$ad_server=strtolower($config["ADSERVER"]);
	$kinitpassword=$config["PASSWORD"];
	$kinitpassword=$unix->shellEscapeChars($kinitpassword);
	
	$clock_explain="The clock on you system (Linux/UNIX) is too far off from the correct time.\nYour machine needs to be within 5 minutes of the Kerberos servers in order to get any tickets.\nYou will need to run ntp, or a similar service to keep your clock within the five minute window";
	
	
	$cmd="$echo $kinitpassword|$kinit {$config["ADADMIN"]}@$domain 2>&1";
	echo "$cmd\n";
	exec("$cmd",$kinit_results);
	while (list ($num, $ligne) = each ($kinit_results) ){
		if(preg_match("#Clock skew too great while getting initial credentials#", $ligne)){$unix->send_email_events("Active Directory connection clock issue", "kinit program claim\n$ligne\n$clock_explain", "system");}
		if(preg_match("#Client not found in Kerberos database while getting initial credentials#", $ligne)){$unix->send_email_events("Active Directory authentification issue", "kinit program claim\n$ligne\n", "system");}
		if($GLOBALS["VERBOSE"]){echo "kinit: $ligne\n";}
	}	
	

	exec("$wbinfo --ping-dc 2>&1",$ping_dc_results);
	
	while (list ($num, $ligne) = each ($ping_dc_results) ){
		if($GLOBALS["VERBOSE"]){echo "ping-dc: $ligne\n";}
		if(preg_match("#succeeded#", $ligne)){$ping_dc=true;}
	}
	
	@unlink($filetime);
	@file_put_contents($filetime, time());
	
	
}


function CheckExistentDirectories(){
	$change=false;
	$sock=new sockets();
	$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
	if($ArticaMetaEnabled==null){$ArticaMetaEnabled=0;}
	$SharedFoldersDefaultMask=$sock->GET_INFO("SharedFoldersDefaultMask");
	$ArticaSambaAutomAskCreation=$sock->GET_INFO("ArticaSambaAutomAskCreation");
	if(!is_numeric($ArticaSambaAutomAskCreation)){$ArticaSambaAutomAskCreation=1;}
	if(!is_numeric($SharedFoldersDefaultMask)){$SharedFoldersDefaultMask=0755;}
	if($ArticaSambaAutomAskCreation==0){$ArticaSambaAutomAskCreation=0600;}
	$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/SambaSMBConf");
	if(is_array($ini->_params)){
		while (list ($index, $array) = each ($ini->_params) ){
			if($index=="print$"){continue;}
			if($index=="printers"){continue;}
			if($index=="homes"){continue;}
			if($index=="global"){continue;}
			if($array["path"]==null){continue;}
			if(is_link($array["path"])){continue;}
			if(is_dir($array["path"])){continue;}else{if($ArticaMetaEnabled==1){@mkdir($array["path"],$SharedFoldersDefaultMask,true);continue;}}
			unset($ini->_params[$index]);
			$change=true;
			continue;
		}
	}
	
	$ini->saveFile("/etc/artica-postfix/settings/Daemons/SambaSMBConf");
	
}


function SambaAudit(){
	if($GLOBALS["VERBOSE"]){writelogs("starting SambaAudit()",__FUNCTION__,__FILE__,__LINE__);}
	$sock=new sockets();
	$EnableSambaXapian=$sock->GET_INFO("EnableSambaXapian");
	$EnableScannedOnly=$sock->GET_INFO('EnableScannedOnly');
	if($EnableSambaXapian==null){$EnableSambaXapian=1;}
	if($EnableScannedOnly==null){$EnableScannedOnly=1;}
	$users=new usersMenus();
	if(!$users->XAPIAN_PHP_INSTALLED){$EnableSambaXapia=0;}
	if(!$users->SCANNED_ONLY_INSTALLED){$EnableScannedOnly=0;}
	
	$sambaZ=new samba();
	$write=false;
	
	
	while (list ($num, $ligne) = each ($sambaZ->main_array) ){
		if($num<>"homes"){if($ligne["path"]==null){continue;}}
		if($num=="profiles"){continue;}
		if($num=="printers"){continue;}
		if($num=="print$"){continue;}
		if($num=="netlogon"){continue;}
		$vfs_objects=$ligne["vfs object"];
		
		
		if($EnableSambaXapian==1){
			if(!IsVfsExists($vfs_objects,"full_audit")){
				$ini->_params[$num]["vfs object"]=$ini->_params[$num]["vfs object"]." full_audit";
				$ini->_params[$num]["vfs object"]=VFSClean($ini->_params[$num]["vfs object"]);
				$ini->_params[$num]["full_audit:prefix"]="%u|%I|%m|%S|%P";
				$ini->_params[$num]["full_audit:success"]="rename unlink pwrite write";
				$ini->_params[$num]["full_audit:failure"]="none";
				$ini->_params[$num]["full_audit:facility"]="LOCAL7";
				$ini->_params[$num]["full_audit:priority"]="NOTICE";				
				$write=true;
			}
		}else{
			if(IsVfsExists($vfs_objects,"full_audit")){
				$ini->_params[$num]["vfs object"]=str_replace("full_audit","",$ini->_params[$num]["vfs object"]);
				$ini->_params[$num]["vfs object"]=VFSClean($ini->_params[$num]["vfs object"]);
				unset($ini->_params[$num]["full_audit:prefix"]);
				unset($ini->_params[$num]["full_audit:success"]);
				unset($ini->_params[$num]["full_audit:failure"]);
				unset($ini->_params[$num]["full_audit:facility"]);
				unset($ini->_params[$num]["full_audit:priority"]);
				$write=true;
			}
		}
		
		if($EnableScannedOnly==0){
			if(IsVfsExists($vfs_objects,"scannedonly")){
				$ini->_params[$num]["vfs object"]=str_replace("scannedonly","",$ini->_params[$num]["vfs object"]);
				$ini->_params[$num]["vfs object"]=VFSClean($ini->_params[$num]["vfs object"]);
				$write=true;
			}
		}		
}
	
if($write){$sambaZ->SaveToLdap(true);}
	
	
	
	
}

function IsVfsExists($line,$module){
	$tbl=explode(" ",$line);
	if(!is_array($tbl)){return false;}
	while (list ($num, $ligne) = each ($tbl) ){
		if(strtolower(trim($ligne))==$module){return true;}
	}
	return false;
}
function VFSClean($line){
	$tbl=explode(" ",$line);
	if(!is_array($tbl)){return false;}
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		$r[]=$ligne;
	}

	if(!is_array($r)){return null;}
	return implode(" ",$r);
	
}


function LogonScripts(){
	
	
	$sql="SELECT * FROM logon_scripts";
	if($GLOBALS["VERBOSE"]){echo "$sql\n";}
	writelogs("checking /home/netlogon security settings",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("/home/netlogon");
	@chmod("/home/netlogon",0755);
	LogonScripts_remove();
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if($GLOBALS["VERBOSE"]){echo "DEBUG:: ".mysql_num_rows($results)." items\n";}
	
	if(!$q->ok){
		writelogs("mysql failed \"SELECT * FROM logon_scripts\" in artica_backup database",__FUNCTION__,__FILE__,__LINE__);
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count++;
		$gpid=$ligne["gpid"];
		$script=$ligne["script_code"];
		if($GLOBALS["VERBOSE"]){echo "DEBUG:: Script group id: $gpid\n";}
		if($gpid==null){writelogs("gpid is null, skip",__FUNCTION__,__FILE__,__LINE__);continue;}
		if($script==null){writelogs("script contains no data, skip",__FUNCTION__,__FILE__,__LINE__);continue;}
		$script=base64_decode($script);
		$script=str_replace("\n","\r\n",$script);
		$script=$script."\r\n";
		writelogs("Saving /home/netlogon/artica-$gpid.bat",__FUNCTION__,__FILE__,__LINE__);
		@file_put_contents("/home/netlogon/artica-$gpid.bat",$script);
		LogonScripts_updateusers($gpid);		
	}
	writelogs("$count scripts updated.",__FUNCTION__,__FILE__,__LINE__);
	
	
}

function LogonScripts_updateusers($gpid){
	$gp=new groups($gpid);
	if(!is_array($gp->members_array)){
		writelogs("Group $gpid did not store users.",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$script="artica-$gpid.bat";
	$members_array=$gp->members_array;
	if($GLOBALS["VERBOSE"]){echo "DEBUG:: GROUP $gpid ". count($members_array) ." Members\n";}
	while (list ($uid, $ligne) = each ($members_array) ){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:: GROUP $gpid:: Updating uid $uid\n";}
		$u=new user($uid);
		
		if($u->dn==null){continue;}
		if($u->NotASambaUser){writelogs("$uid is not a Samba user",__FUNCTION__,__FILE__,__LINE__);continue;}
		writelogs("edit $uid for $script script name",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["VERBOSE"]){echo "DEBUG:: $uid -> $script\n";}
		$u->Samba_edit_LogonScript($script);
			
		
	}
	
}




function LogonScripts_remove(){
	$dir_handle = @opendir("/home/netlogon");
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("/home/netlogon/$file")){continue;}
		  if(preg_match("#artica-[0-9]+#",$file)){
		  	if($GLOBALS["VERBOSE"]){echo "removing /home/netlogon/$file\n";}
		  	@unlink("/home/netlogon/$file");
		  }
		  continue;
		}
}


function CountDeUsers(){
	$ldap=new clladp();
	$arr=$ldap->hash_users_ou(null);
	@file_put_contents("/etc/artica-postfix/UsersNumber",count($arr));
}

function fix_lmhosts(){
	$smb=new samba();
	$smb->main_array["global"]["name resolve order"]=null;
	$smb->SaveToLdap();
	
}

function fix_hide_unwriteable_files(){
	
	$smb=new samba();
	while (list ($key, $array) = each ($smb->main_array)){
		while (list ($valuename, $value) = each ($array) ){
			
			if($valuename=="hide_unwriteable_files"){
				echo "Found $key,$valuename\n";
				$mod=true;
				unset($smb->main_array[$key][$valuename]);
				$smb->main_array[$key]["hide unwriteable files"]=$value;
			}
		}
	}
	
	if($mod==true){$smb->SaveToLdap();}
	
	
}


function usb_mount($uuid,$user){
	$usb=new usb($uuid);
	$unix=new unix();
	writelogs("Mounting $uuid ($usb->TYPE) from $user",__FUNCTION__,__FILE__,__LINE__);
	$path="/media/$uuid";
	$mount=new mount();
	if($mount->ismounted($path)){exit(0);}
	$mount_bin=$unix->find_program("mount");
	$blkid_bin=$unix->find_program("blkid");
	
	writelogs("mount:$mount_bin blkid:$blkid_bin",__FUNCTION__,__FILE__,__LINE__);
	
	if($mount==null){exit(1);}
	if($blkid_bin==null){exit(1);}
	if($usb->TYPE==null){
		exec("$blkid_bin -s UUID -s TYPE 2>&1",$results);
		writelogs("$blkid_bin -s UUID -s TYPE 2>&1 ".count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
		while (list ($key, $line) = each ($results)){
		if(preg_match('#UUID="'.$uuid.'"\s+TYPE="(.+?)"#i',$line,$re)){$type=$re[1];}
		}
	}
	
	if($type==null){
		writelogs("Unable to find type...",__FUNCTION__,__FILE__,__LINE__);
		exit(1);
	}
	
	unset($results);
	
	if(!is_dir($path)){
		if(!@mkdir($path,null,true)){
			writelogs("create dir \"$path\" permission denied  ",__FUNCTION__,__FILE__,__LINE__);
			exit(1);
		}
	}
	
	$cmd="$mount_bin -t $type $usb->path $path 2>&1";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	if(!$mount->ismounted($path)){
		writelogs("Mounting $uuid failed",__FUNCTION__,__FILE__,__LINE__);
		while (list ($key, $line) = each ($results)){writelogs("$line",__FUNCTION__,__FILE__,__LINE__);}
		exit(1);
	}
	exit(0);
	
}

function usb_umount($uuid,$user){
	$usb=new usb($uuid);
	$unix=new unix();
	writelogs("Umount $uuid ($usb->TYPE) from $user",__FUNCTION__,__FILE__,__LINE__);
	$path="/media/$uuid";
	$mount=new mount();
	if(!$mount->ismounted($path)){exit(0);}
	$umount_bin=$unix->find_program("umount");
	exec("$umount_bin -l $path 2>&1",$results);
	while (list ($key, $line) = each ($results)){writelogs("$line",__FUNCTION__,__FILE__,__LINE__);}
	if(!$mount->ismounted($path)){exit(0);}
	exit(1);
	
}

function sslbridgre(){
	if(!is_file("/usr/share/artica-postfix/sslbridge/index.php")){return;}
	$unix=new unix();
	$ligghtpd=$unix->LIGHTTPD_USER();
	shell_exec("/bin/chown -R $ligghtpd:$ligghtpd /usr/share/artica-postfix/sslbridge");
	shell_exec("/bin/chmod -R 755 /usr/share/artica-postfix/sslbridge");
	$f[]="<?php";
	$f[]="define(\"LOCALHOST\", \"localhost\");";
	$f[]="define(\"SYSTEMDIR\", '/tmp');";
	$f[]="// define(\"DEBUG\", true);";
	$f[]="?>";
	@file_put_contents("/usr/share/artica-postfix/sslbridge/config.php",@implode("\n",$f));
	
	
}
function EnablePamd(){
$f[]="# PAM configuration file for Cyrus IMAP service";
$f[]="# \$Id: imap.pam 5 2005-03-12 23:19:45Z sven $";
$f[]="#";
$f[]="# If you want to use Cyrus in a setup where users don't have";
$f[]="# accounts on the local machine, you'll need to make sure";
$f[]="# you use something like pam_permit for account checking.";
$f[]="#";
$f[]="# Remember that SASL (and therefore Cyrus) accesses PAM"; 
$f[]="# modules through saslauthd, and that SASL can only deal with";
$f[]="# plaintext passwords if PAM is used.";
$f[]="#";
$f[]="auth     sufficient pam_krb5.so no_user_check validate";
$f[]="account  sufficient pam_permit.so";
@file_put_contents("/etc/pam.d/imap",@implode("\n",$f));
@file_put_contents("/etc/pam.d/smtp",@implode("\n",$f));


}

function DisablePamd(){
	
$f[]="# PAM configuration file for Cyrus IMAP service";
$f[]="# \$Id: imap.pam 5 2005-03-12 23:19:45Z sven $";
$f[]="#";
$f[]="# If you want to use Cyrus in a setup where users don't have";
$f[]="# accounts on the local machine, you'll need to make sure";
$f[]="# you use something like pam_permit for account checking.";
$f[]="#";
$f[]="# Remember that SASL (and therefore Cyrus) accesses PAM"; 
$f[]="# modules through saslauthd, and that SASL can only deal with";
$f[]="# plaintext passwords if PAM is used.";
$f[]="#";
$f[]="@include common-auth";
$f[]="@include common-account";
@file_put_contents("/etc/pam.d/imap",@implode("\n",$f));
@unlink("/etc/pam.d/smtp");
}


function smbtree(){
	$unix=new unix();
	$timefile="/etc/artica-postfix/smbtree.cache";
	$smbtree=$unix->find_program("smbtree");
	if(!is_file($smbtree)){return;}
	$time=file_time_min($timefile);
	if($time>5){
		exec("$smbtree -N 2>&1",$results);
		@file_put_contents($timefile,serialize($results));
	}
	$results=unserialize(@file_get_contents($timefile));
	
	$final=array();
	while (list ($index, $ligne) = each ($results)){
		$ligne=trim($ligne);
		if($GLOBALS["VERBOSE"]){echo "check \"$ligne\"\n";}
		if(preg_match("#^([A-Za-z0-9\_\-]+)$#",$ligne,$re)){
				if($GLOBALS["VERBOSE"]){echo "Found DOMAIN {$re[1]}\n";}
				$DOMAIN=$re[1];
				continue;
			}

		$tr=explode('\\',$ligne);
		if(count($tr)>0){
			unset($tr[0]);
			unset($tr[1]);
			if(count($tr)>1){
				$final[$DOMAIN][$tr[2]]["IP"]=nmblookup($tr[2],null);
				$final[$DOMAIN][$tr[2]]["SHARES"][]=$tr[3];
			}
		
		}
	}
	
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/smbtree.array",serialize($final));
	shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/smbtree.array");
	
}

function nmblookup($hostname,$ip){
	if(!isset($GLOBALS["nmblookup"])){
		$unix=new unix();
		$GLOBALS["nmblookup"]=$unix->find_program("nmblookup");
	}
	if(trim($hostname)==null){return $ip;}
	if(isset($GLOBALS["NMBLOOKUP-INFOS"][$hostname])){return $GLOBALS["NMBLOOKUP-INFOS"][$hostname];}
	
	$hostname=str_replace('$','',$hostname);
	if($GLOBALS["nmblookup"]==null){
		$unix=new unix();
		$GLOBALS["nmblookup"]=$unix->find_program("nmblookup");
	}
	
	if($GLOBALS["nmblookup"]==null){
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> Could not found binary\n";}
		return $ip;
	}
	if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$hostname)){
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> hostname match IP string, aborting\n";}
		return $ip;
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$ip,$re)){
		$broadcast="{$re[1]}.{$re[2]}.{$re[3]}.255";
	}else{
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> $ip not match for broadcast addr\n";}
		$cmd="{$GLOBALS["nmblookup"]} $hostname 2>&1";
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> $cmd\n";}
		exec($cmd,$results);
	}
	
	if(count($results)==0){
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> broadcast=$broadcast\n";}
		$cmd="{$GLOBALS["nmblookup"]} -B $broadcast $hostname";
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> $cmd\n";}
		exec($cmd,$results);
	}
	
	$hostname_pattern=str_replace(".","\.",$hostname);
	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Got a positive name query response from\s+([0-9\.]+)#",$ligne,$re)){
			if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> {$re[1]}\n";}
			$GLOBALS["NMBLOOKUP-INFOS"][$hostname]=$re[1];
			return $re[1];
		}
		
		if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+).+?$hostname_pattern#",$ligne,$re)){
		if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> {$re[1]}.{$re[2]}.{$re[3]}.{$re[4]}\n";}
			$GLOBALS["NMBLOOKUP-INFOS"][$hostname]="{$re[1]}.{$re[2]}.{$re[3]}.{$re[4]}";
			return "{$re[1]}.{$re[2]}.{$re[3]}.{$re[4]}";
		}
		
		
	}
	if($GLOBALS["VERBOSE"]){echo " nmblookup:: --> NO MATCH\n";}
	$GLOBALS["NMBLOOKUP-INFOS"][$hostname]=$ip;
	return $ip;
}

function administrator_update(){
	$samba=new samba();
	$admin_password=$samba->GetAdminPassword("administrator");
	if($admin_password==null){echo "No password set\n";return;}
	$samba->EditAdministrator("administrator",$admin_password);
	echo "updating administrator done...\n";
}

function set_log_level($level){
	$samba=new samba();
	$samba->main_array["global"]["log level"]=$level;
	$samba->SaveToLdap();
	
}	

function quotasrecheck(){
	$unix=new unix();
	$quotaoff=$unix->find_program("quotaoff");
	$quotaon=$unix->find_program("quotaon");
	$quotacheck=$unix->find_program("quotacheck");
	if(is_file($quotacheck)){
		if($GLOBALS["VERBOSE"]){echo " quotacheck:: --> no such file\n";}
		return;
	}
	
	shell_exec("$quotaoff -a");
	shell_exec("$quotacheck -vagum");
	shell_exec("$quotaon -a");
	
}

function test_join(){
	$sock=new sockets();
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	if($EnableSambaActiveDirectory==0){return;}
	
	
	$unix=new unix();
	$net=$unix->LOCATE_NET_BIN_PATH();
	exec("$net ads testjoin 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Join is OK#", $ligne)){return;}
		
	}
	
	$adsjoinerror=@implode("\n", $results);
	$results=array();
	
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	$ad_server=strtolower($config["ADSERVER"]);
	$domain_lower=strtolower($config["ADDOMAIN"]);
	$adminpassword=$config["PASSWORD"];
	$adminpassword=$unix->shellEscapeChars($adminpassword);
	$cmd="$net ads join -W $ad_server.$domain_lower -S $ad_server -U {$config["ADADMIN"]}%$adminpassword 2>&1";
	exec($cmd,$results1);
	$cmd="net join -U {$config["ADADMIN"]}%$adminpassword -S $ad_server 2>&1";
	exec($cmd,$results2);
	$unix->send_email_events("Join to [$ad_server] Active Directory Domain failed", "NET claim:".@implode("\n", $results)."
	Artica reconnect the system to the Active Directory report:\n".@implode("\n", $results1)."\n".@implode("\n", $results2), "system");
	reload();
	
	
}

function recycles(){
	$smb=new samba();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	
	$unix=new unix();
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		$unix->send_email_events("Virtual trashs: Instance PID $pid already running, task is canceled", "A maintenance task pid $pid is already running... This task pid ".getmypid()." is aborted", "samba");
		return;
	}
	@file_put_contents($pidfile, getmypid());
	$recycles=$smb->LOAD_RECYCLES_BIN();
	
	
	
	if(count($recycles)==0){return;}
	$unix=new unix();
	while (list ($directory, $none) = each ($recycles) ){
		$ShareName=$directory;
		$path=$smb->main_array[$directory]["path"].'/.RecycleBin$';
		if($path==null){continue;}
		echo "recycles:: Parsing $directory -> $path\n ";
		if(!is_dir($path)){continue;}
		$finalDirectories=$unix->dirdir($path);
		while (list ($DirUid, $none) = each ($finalDirectories) ){
			$uid=basename($DirUid);
			echo "recycles:: Parsing recycles for $uid -> $DirUid\n ";
			Recycles_inject($DirUid,$uid,$ShareName);
		}
		
		
		
		
	}
	//DirRecursiveFiles
	
	
	
}

function Recycles_inject($path,$uid,$ShareName){
	$unix=new unix();
	$q=new mysql();
	$arrays=$unix->DirRecursiveFiles($path);
	$prefix="INSERT IGNORE INTO samba_recycle_bin_list (path,uid,sharename,filesize) VALUES";
	while (list ($index, $userpath) = each ($arrays) ){
		$size=@filesize($userpath);
		$userpath=addslashes($userpath);
		$path=addslashes($path);
		$uid=addslashes($uid);
		$sql[]="('$userpath','$uid','$ShareName','$size')";
		if(count($sql)>500){
				$finalsql="$prefix ".@implode(",", $sql);
				$sql=array();
				$q->QUERY_SQL($finalsql,"artica_backup");
				if(!$q->ok){echo $q->mysql_error;echo "\n$finalsql\n";}
		}
			
		}
		
		
	if(count($sql)>0){
		if($GLOBALS["VERBOSE"]){echo "Inserting ".count($sql)." events\n";}
		$finalsql="$prefix ".@implode(",", $sql);$sql=array();
		$q->QUERY_SQL($finalsql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;echo "\n$finalsql\n";}
	}
	
}

function recycles_delete(){
	$unix=new unix();
	$sql="SELECT * FROM samba_recycle_bin_list WHERE delete=1";
	$q=new mysql();
	$unix=new unix();
	$c=0;
	$mv=$unix->find_program("mv");
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$path=$ligne["path"];
		$path_org=$path;
		
		$sql="DELETE FROM samba_recycle_bin_list WHERE path='$path_org'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			$unix->send_email_events("Virtual trashs: mysql error $q->mysql_error", "File $path \ncannot be deleted from trash", "samba");
			continue;
		}
		if(is_file($path_org)){		
			@unlink($path_org);
			$c++;
		}

	} 	
	
	if($c>0){
		$unix->send_email_events("Virtual trashs: $c file(s) deleted", "$c File(s) has been automatically deleted from virtual trash.\n", "samba");
	}
	
}

function recycles_privileges($SourcePath,$uid){
	$unix=new unix();
	$dir=dirname($SourcePath);
	if(strpos($dir, "RecycleBin$/$uid/")==0){return;}
	$DestPos=strpos($SourcePath,"/.RecycleBin$");
	$finalDestination=substr($SourcePath, 0,$DestPos);
	
	
	$suffix=substr($dir, 0,strpos($dir, "RecycleBin$/$uid/")+strlen("RecycleBin$/$uid/"));
	
	$dir=substr($dir, strpos($dir, "RecycleBin$/$uid/")+strlen("RecycleBin$/$uid/"),strlen($dir));
	
	echo $dir."\n";
	echo "suffix:$suffix\n";
	echo "Destination:$finalDestination\n";
	$tr=explode("/",$dir);
	
	while (list ($index, $directory) = each ($tr) ){
		$dirs[]=$directory;
		$sourcedir="$suffix/". @implode("/", $dirs);
		$sourcedir=str_replace('//', "/", $sourcedir);
		$actualPerms = file_perms($sourcedir,true);
		$stat=stat($sourcedir);
		$uid=$stat["uid"];
		$gid=$stat["gid"];
		
		
		if($GLOBALS["VERBOSE"]){echo "recycles_privileges():: $sourcedir -> $actualPerms ($uid $gid)\n";}
		$FinalDirectoryDestination="$finalDestination/". @implode("/", $dirs);
		$FinalDirectoryDestination=str_replace('//', "/", $FinalDirectoryDestination);
		if(!is_dir($FinalDirectoryDestination)){
			@mkdir($FinalDirectoryDestination,$actualPerms,true);
			shell_exec("/bin/chmod $actualPerms ".$unix->shellEscapeChars($FinalDirectoryDestination));
			shell_exec("/bin/chown $uid:$gid ".$unix->shellEscapeChars($FinalDirectoryDestination));
		}
	}
}

function file_perms($file, $octal = false){
    if(!file_exists($file)) return false;
    $perms = fileperms($file);
    $cut = $octal ? 2 : 3;
    return substr(decoct($perms), $cut);
}


function recycles_restore(){
	$sql="SELECT * FROM samba_recycle_bin_list WHERE restore=1";
	$q=new mysql();
	$unix=new unix();
	$mv=$unix->find_program("mv");
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$path=$ligne["path"];
		$path_org=$path;
		echo "$path\n";
		$uid=$ligne["uid"];
		$pathToRestore=str_replace("/.RecycleBin$/$uid", "", $path);
		$pathToRestoreorg=$pathToRestore;
		if(!is_file($path)){
			echo "FAILED $path no such file\n";
			$sql="DELETE FROM samba_recycle_bin_list WHERE path='$path_org'";
			$q->QUERY_SQL($sql,"artica_backup");
			continue;
		}
		
		
		$path=$unix->shellEscapeChars($path);
		$dirname=dirname($pathToRestore);
		$pathToRestore=$unix->shellEscapeChars($pathToRestore);
		
		
		echo "restore to \"$dirname\"\n";
		recycles_privileges($path_org,$uid);
		if(!is_dir($dirname)){
			echo "FAILED ! $dirname no such directory\n";
			$sql="UPDATE samba_recycle_bin_list SET restore=0 WHERE path='$path_org'";
			$q->QUERY_SQL($sql,"artica_backup");			
			continue;
		}
		
		$cmd="$mv -b $path $pathToRestore";
		$ras=shell_exec($cmd);
		if(!is_file($pathToRestoreorg)){
			echo "FAILED ! mv $path $pathToRestore $ras\n";
			$sql="UPDATE samba_recycle_bin_list SET restore=0 WHERE path='$path_org'";
			$q->QUERY_SQL($sql,"artica_backup");
			continue;
		}else{
			$sql="DELETE FROM samba_recycle_bin_list WHERE path='$path_org'";
			$q->QUERY_SQL($sql,"artica_backup");	
		}
		
	}
	
}

function ScanTrashs(){
	$unix=new unix();
	$ScanTrashPeriod=$sock->GET_INFO("ScanTrashTime");
	$ScanTrashTTL=$sock->GET_INFO("ScanTrashTTL");
	if(system_is_overloaded(basename(__FILE__))){$unix->send_email_events("Scanning virtual trashs aborted (system is overloaded)", "The task was stopped and will restarted in $ScanTrashPeriod minutes", "samba");return;}
	if(!is_numeric($ScanTrashPeriod)){$ScanTrashPeriod=450;}
	if(!is_numeric($ScanTrashTTL)){$ScanTrashTTL=7;}	
	if($ScanTrashPeriod<30){$ScanTrashPeriod=30;}
	if($ScanTrashTTL<1){$ScanTrashTTL=1;}
	$filetime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	if($unix->file_time_min($filetime)<$ScanTrashPeriod){return;}
	@unlink($filetime);
	@file_put_contents($filetime, time());
	$sql="UPDATE samba_recycle_bin_list SET delete=1 WHERE zDate<DATE_SUB(NOW(), INTERVAL $ScanTrashTTL DAY)";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){$unix->send_email_events("Virtual trashs: unable to set files TTL, mysql error", "Artica cannot update the mysql table\n$sql\n", "samba");}
	recycles_delete();
	recycles();	
}

function smbstatus_injector(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){
		writelogs("$pid already exists in memory, aborting",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	@file_put_contents($pidfile, getmypid());
	
	$q=new mysql();
	$q->QUERY_SQL("TRUNCATE TABLE smbstatus_users","artica_events");
	$smbstatus=$unix->find_program("smbstatus");
	if(!is_file($smbstatus)){return;}
	exec("$smbstatus -p 2>&1",$results);

	$prefix="INSERT IGNORE INTO smbstatus_users ( `pid`,`username`,`usersgroup`,`computer`,`ip_addr`) VALUES ";
	while (list ($index, $line) = each ($results) ){
		if(trim($line)==null){continue;}
		if(!preg_match("#([0-9]+)\s+(.+?)\s+(.+?)\s+\s+(.+?)\s+\((.+?)\)#", $line,$re)){
			if($GLOBALS["VERBOSE"]){echo "[P]:'$line' -> no match #([0-9]+)\s+(.+?)\s+(.+?)\s+\s+(.+?)\s+\((.+?)\)#\n";}
			
			continue;}
		$sql[]="('{$re[1]}','{$re[2]}','{$re[3]}','{$re[4]}','{$re[5]}')";		
		if(count($sql)>500){
			$injectsql=$prefix.@implode(",", $sql);
			$sql=array();
			$q->QUERY_SQL($injectsql,"artica_events");
		}	
	}
	
	if(count($sql)>0){
		$injectsql=$prefix.@implode(",", $sql);
		$sql=array();
		$q->QUERY_SQL($injectsql,"artica_events");
	}		
	
	$results=array();
	exec("$smbstatus -S 2>&1",$results);
	
	while (list ($index, $line) = each ($results) ){
		if(trim($line)==null){continue;}
		if(!preg_match("#^(.+?)\s+([0-9]+)\s+(.+?)\s+(.+)$#", $line,$re)){if($GLOBALS["VERBOSE"]){echo "[S]:'$line' -> no match ^(.+?)\s+([0-9]+)\s+(.+?)\s+(.+)$\n";}continue;}
			$share=addslashes($re[1]);
			$pid=$re[2];
			$time=strtotime($re[4]);
			$date=date('Y-m-d H:i:s',$time);
			if($GLOBALS["VERBOSE"]){echo "SHARE='$share' {$re[4]} = $date pid=$pid\n ";}
			
			$q->QUERY_SQL("UPDATE smbstatus_users SET `sharename`='$share',`zDate`='$date' WHERE `pid`='$pid'","artica_events");
			if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";}}
			
	}	
	
	$results=array();
	exec("$smbstatus -L 2>&1",$results);
		
	$q->QUERY_SQL("TRUNCATE TABLE smbstatus_users_dirs","artica_events");
	$prefix="INSERT IGNORE INTO smbstatus_users_dirs (`zmd5`, `pid`,`directory`,`filepath`,`zDate`) VALUES ";
	while (list ($index, $line) = each ($results) ){
	if(trim($line)==null){continue;}
	if(!preg_match("#^([0-9]+)\s+[0-9]+\s+[A-Z\_]+\s+[a-z0-9]+\s+[A-Z\_\+]+\s+[A-Z\_\+]+\s+(.+?)\s+\s+(.+?)\s+\s+(.+?)$#", $line,$re)){if($GLOBALS["VERBOSE"]){echo "$line -> no match\n";}continue;}
		$pid=$re[1];
		$dir=addslashes($re[2]);
		$path=addslashes($re[3]);
		$time=strtotime($re[4]);
		$date=date('Y-m-d H:i:s',$time);
		$md5=md5("$pid$dir$path$date");
		if($GLOBALS["VERBOSE"]){echo "$pid -> $dir\n";}
		
		$sql[]="('$md5','$pid','$dir','$path','$date')";		
		if(count($sql)>500){
			$injectsql=$prefix.@implode(",", $sql);
			$sql=array();
			$q->QUERY_SQL($injectsql,"artica_events");
		}			
	}
	
	if(count($sql)>0){
		$injectsql=$prefix.@implode(",", $sql);
		$sql=array();
		$q->QUERY_SQL($injectsql,"artica_events");
	}		

}





?>