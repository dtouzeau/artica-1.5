<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.backup.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.cyrus.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


$GLOBALS["ONLY_TESTS"]=false;
$GLOBALS["ONNLY_MOUNT"]=false;
$GLOBALS["NO_UMOUNT"]=false;
$GLOBALS["PCOPY"]=false;
$GLOBALS["NO_STANDARD_BACKUP"]=false;
$date=date('Y-m-d');
$GLOBALS["ADDLOG"]="/var/log/artica-postfix/backup-starter-$date.log";
@mkdir("/var/log/artica-postfix/sql-events-queue");


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--only-test#",implode(" ",$argv))){$GLOBALS["ONLY_TESTS"]=true;}
if(preg_match("#--no-umount#",implode(" ",$argv))){$GLOBALS["NO_UMOUNT"]=true;}
if(preg_match("#--no-standard-backup#",implode(" ",$argv))){$GLOBALS["NO_STANDARD_BACKUP"]=true;}
if(preg_match("#--no-reload#",implode(" ",$argv))){$GLOBALS["NO_RELOAD"]=true;}
if(preg_match("#--mysql-db#",implode(" ",$argv))){backup_mysql_databases_list(0);die();}

if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
$GLOBALS["USE_RSYNC"]=false;
$GLOBALS["INTRO_CMDLINES"]=@implode(" ",$argv);


if($argv[1]=="--restore-mbx"){
	restorembx($argv[2]);
	die();
}

if(preg_match("#--cron#",implode(" ",$argv))){
	buildcron();
	die();
}


if($argv[1]=="--usb"){
	mount_usb("usb://{$argv[2]}",0,true);
	die();
}

if($argv[1]=="--mount"){
	$id=$argv[2];
	while (list ($num, $cmd) = each ($argv) ){
		if(preg_match("#--dir=(.+)#",$cmd,$re)){$GLOBALS["DIRLIST"]="/".$re[1];continue;}
		if(preg_match("#--id=([0-9]+)#",$cmd,$re)){$id=$re[1];continue;}
		if(preg_match("#--list#",$cmd,$re)){$GLOBALS["dirlist"]=true;}			
		}
	
	$GLOBALS["ONNLY_MOUNT"]=true;
	writelogs(date('m-d H:i:s')." "."mounting $id",__FUNCTION__,__FILE__);
	$dir=backup($id);
	ParseMailboxDir($dir);
	if(!$GLOBALS["NO_UMOUNT"]){shell_exec("umount -l $dir");}
	die();
	}


$ID=$argv[1];
if($ID<1){
	writelogs(date('m-d H:i:s')." "."unable to get task ID \"{$GLOBALS["INTRO_CMDLINES"]}\" process die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}





backup($ID);
ParseMysqlEventsQueue();


function buildcron(){
	$unix=new unix();
	$path="/etc/cron.d";
	
	$sql="SELECT * FROM backup_schedules ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){return null;}	
	
	$files=$unix->DirFiles("/etc/cron.d");
	while (list ($num, $filename) = each ($files) ){
		if(preg_match("#artica-backup-([0-9]+)$#",$filename)){
			echo "Starting......: Backup remove $filename\n";
			@unlink("$path/$filename");
		}
	}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$schedule=$ligne["schedule"];
		echo "Starting......: Backup $schedule\n";
		$f[]="$schedule  ". LOCATE_PHP5_BIN()." ". __FILE__." {$ligne["ID"]} >/dev/null 2>&1";
		
	}
	
	@file_put_contents("/etc/artica-postfix/backup.tasks",@implode("\n",$f));
	if(!$GLOBALS["NO_RELOAD"]){
		system("/etc/init.d/artica-postfix restart daemon");
	}
	
}

function CheckCommandLineCopy(){
	if(!isset($GLOBALS["rsync_bin"])){$unix=new unix();$GLOBALS["rsync_bin"]=$unix->find_program("rsync");}
	
	writelogs("rsync={$GLOBALS["rsync_bin"]}",__FUNCTION__,__FILE__,__LINE__);
	if(is_file($GLOBALS["rsync_bin"])){$GLOBALS["COMMANDLINECOPY"]="{$GLOBALS["rsync_bin"]} -ar {SRC_PATH} {NEXT} --stats --chmod=ug=rwX,o=rwX";}
	if(!is_file($GLOBALS["rsync_bin"])){
		writelogs("rsync, no such binary, using cp bin instead...",__FUNCTION__,__FILE__,__LINE__);
		$GLOBALS["COMMANDLINECOPY"]="/bin/cp -ru {SRC_PATH} {NEXT}";
	}		
	writelogs("COMMANDLINECOPY={$GLOBALS["COMMANDLINECOPY"]}",__FUNCTION__,__FILE__,__LINE__);
	
}



function backup($ID){
	$date_start=time();
	$sock=new sockets();
	$q=new mysql();
	$unix=new unix();
	$users=new usersMenus();
		
	$GLOBALS["RESOURCE_MOUNTED"]=true;
	$sql="SELECT * FROM backup_schedules WHERE ID='$ID'";
	if($GLOBALS["VERBOSE"]){backup_events($ID,"initialization","$sql",__LINE__);}
	
	$mount_path="/opt/artica/mounts/backup/$ID";
	
	if(!$q->TABLE_EXISTS("backup_storages", "artica_backup",true)){
		$q->BuildTables();
		if(!$q->TABLE_EXISTS("backup_storages", "artica_backup",true)){
			backup_events($ID,"initialization","ERROR, backup_storages, no such table",__LINE__);
			return;
		}
	}
	
	
	$servername=$users->fqdn;
	$servername=str_replace('.(none)',"",$servername);
	$servername=str_replace(')',"",$servername);
	$servername=str_replace('(',"",$servername);
	$GLOBALS["MYSERVERNAME"]=$servername;
	$ExecBackupDeadAfterH=$sock->GET_INFO("ExecBackupDeadAfterH");
	if(!is_numeric($ExecBackupDeadAfterH)){$ExecBackupDeadAfterH=2;}
	if($ExecBackupDeadAfterH<2){$ExecBackupDeadAfterH=2;}
	$ExecBackupDeadAfterH=$ExecBackupDeadAfterH*60;
	
	
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	if(!$q->ok){
		send_email_events("Backup Task $ID:: Mysql database error !","Aborting backup\n$q->mysql_error","backup");
		backup_events($ID,"initialization","ERROR, Mysql database error\n$q->mysql_error",__LINE__);
		return false;
	}
	
	if(!$GLOBALS["ONNLY_MOUNT"]){
		$pid=$ligne["pid"];	
		if($unix->process_exists($pid)){
			$time=$unix->PROCCESS_TIME_MIN($pid);
			if($time>$ExecBackupDeadAfterH){
				send_email_events("Backup Task $ID:: Already instance $pid running since {$time}Mn","The old process was killed and a new backup task will be performed\nstatus:\n\n$unix->proc_status","backup");
			}else{
				send_email_events("Backup Task $ID:: Already instance $pid running since {$time}Mn","Aborting backup\n$unix->proc_status","backup");
				backup_events($ID,"initialization","ERROR, Already instance $pid running since {$time}Mn",$unix->proc_status);
				return false;
			}
		}
	}
	
	$sql="UPDATE backup_schedules set pid='".getmypid()."' WHERE ID='$ID'";
	$q->QUERY_SQL($sql,"artica_backup");
		
	$ressources=unserialize(base64_decode($ligne["datasbackup"]));
	if(count($ressources)==0){
		backup_events($ID,"initialization","ERROR,No source specified");
		send_email_events("Backup Task $ID::  No source specified","Aborting backup","backup");
		return false;
	}
	
	if($ressources["OPTIONS"]["STOP_IMAP"]==1){$GLOBALS["NO_STOP_CYRUS"]=" --no-cyrus-stop";}
	
	$backup=new backup_protocols();
	$resource_type=$ligne["resource_type"];
	$pattern=$ligne["pattern"];
	$first_ressource=$backup->extractFirsRessource($ligne["pattern"]);
	$container=$ligne["container"];
	backup_events($ID,"initialization","resource: $resource_type -> $first_ressource",__LINE__);
	if($resource_type==null){
		backup_events($ID,"initialization","ERROR,No resource specified");
		send_email_events("Backup Task $ID:: No resource specified !","Aborting backup","backup");
		return false;
	}
	
	
	
	
	if($resource_type=="smb"){
		$GLOBALS["CAN_CLEAN_CONTAINERS"]=true;
		$mounted_path_sep="/";
		if(!mount_smb($pattern,$ID,true)){
			backup_events($ID,"initialization","ERROR,$first_ressource unable to mount mount_smb()",__LINE__);
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to mount","Aborting backup","backup");
			
			return false;
		}
		$GLOBALS["PCOPY"]=true;
	}
	
	
	if($resource_type=="usb"){
		$GLOBALS["CAN_CLEAN_CONTAINERS"]=true;
		$mounted_path_sep="/";
		if(!mount_usb($pattern,$ID,true)){
			backup_events($ID,"initialization","ERROR,$first_ressource unable to mount mount_usb()",__LINE__);
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to mount","Aborting backup","backup");
			return false;
		}
		
		backup_events($ID,"initialization","INFO, using external device trough USB",__LINE__);
		$GLOBALS["PCOPY"]=true;
		
	}	
	
	if($resource_type=="rsync"){
		$mounted_path_sep=null;
		$mount_path=null;
		$GLOBALS["RESOURCE_MOUNTED"]=false;
		$GLOBALS["USE_RSYNC"]=true;
		$GLOBALS["NO_UMOUNT"]=true;
		$GLOBALS["CAN_CLEAN_CONTAINERS"]=false;
		if(!mount_rsync($pattern,$ID,true)){
			backup_events($ID,"initialization","ERROR,$first_ressource unable to connect");
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to connect","Aborting backup","backup");
			return false;
		}else{
			backup_events($ID,"initialization","INFO,$first_ressource connect success");
		}
		
		
	}

	if($resource_type=="automount"){
		$mounted_path_sep="/";
		$mount_path=$first_ressource;
		$GLOBALS["RESOURCE_MOUNTED"]=false;
		$GLOBALS["USE_RSYNC"]=true;
		$GLOBALS["NO_UMOUNT"]=true;
		$GLOBALS["CAN_CLEAN_CONTAINERS"]=true;
		$GLOBALS["MOUNTED_PATH_FINAL"]=$first_ressource;
		CheckCommandLineCopy();
		
		if(!mount_automount($pattern,$ID,true)){
			backup_events($ID,"initialization","ERROR,$first_ressource unable to connect");
			send_email_events("Backup Task $ID::  resource: $first_ressource unable to connect","Aborting backup","backup");
			return false;
		}
		backup_events($ID,"initialization","INFO,$first_ressource connect success");
		$GLOBALS["PCOPY"]=true;
	}

	if($resource_type=="local"){
		$mounted_path_sep="/";
		$mount_path=$first_ressource;
		$GLOBALS["RESOURCE_MOUNTED"]=false;
		$GLOBALS["CAN_CLEAN_CONTAINERS"]=true;
		$GLOBALS["USE_RSYNC"]=true;
		$GLOBALS["NO_UMOUNT"]=true;
		$GLOBALS["MOUNTED_PATH_FINAL"]=$first_ressource;
		CheckCommandLineCopy();
		
		if(!is_dir($first_ressource)){
			backup_events($ID,"initialization","ERROR,$first_ressource no such directory",__LINE__);
			send_email_events("Backup Task $ID::  resource: $first_ressource no such directory","Aborting backup","backup");
			return false;
		}
		backup_events($ID,"initialization","INFO,$first_ressource success");
		$GLOBALS["PCOPY"]=true;
	}		
	
	if($GLOBALS["ONLY_TESTS"]){
		if($GLOBALS["RESOURCE_MOUNTED"]){
			writelogs(date('m-d H:i:s')." "."[TASK $ID]:umount $first_ressource",__FUNCTION__,__FILE__,__LINE__);
			exec("umount -l $mount_path");
		}
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: terminated...",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if($GLOBALS["ONNLY_MOUNT"]){return $mount_path;}
	
	
	if($container=="daily"){
		backup_events($ID,"initialization","INFO, Daily container",__LINE__);
		$DirectoryContainer="backup.".date('Y-m-d')."/$servername";
		$GLOBAL["BACKUP_MEMORY_SQL"]["CONTAINER"]=$DirectoryContainer;
		$mount_path_final=$mount_path.$mounted_path_sep.$DirectoryContainer;
	}else{
		backup_events($ID,"initialization","INFO, Weekly container",__LINE__);
		$DirectoryContainer="backup.".date('Y-W')."/$servername";
		$GLOBAL["BACKUP_MEMORY_SQL"]["CONTAINER"]=$DirectoryContainer;
		$mount_path_final=$mount_path.$mounted_path_sep.$DirectoryContainer;
	}
	
if($GLOBALS["DEBUG"]){
		$cmd_verb=" --verbose";
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: Verbose mode detected",__FUNCTION__,__FILE__,__LINE__);
	}
	
@mkdir($mount_path_final,644,true);	

if(!$GLOBALS["NO_STANDARD_BACKUP"]){
	$GLOBALS["MOUNTED_PATH_FINAL"]=$mount_path_final;
	$WhatToBackup_ar=null;
	
	while (list ($num, $WhatToBackup) = each ($ressources) ){
		if(is_array($WhatToBackup)){$WhatToBackup_ar=implode(",",$WhatToBackup);}
		backup_events($ID,"initialization","INFO, WhatToBackup ($WhatToBackup) -> $WhatToBackup_ar",__LINE__);
		if($WhatToBackup=="all"){
			backup_events($ID,"initialization","INFO, Backup starting Running macro all cyrus, mysql, LDAP, Artica...",__LINE__);
			send_email_events("Backup Task $ID:: Backup starting Running macro all ","Backup is running","backup");
			if($users->cyrus_imapd_installed){
				backup_events($ID,"initialization","INFO, cyrus-imapd mailboxes processing");
				backup_cyrus($ID);
			}
			backup_events($ID,"initialization","INFO, LDAP Database processing",__LINE__);
			backup_ldap($ID);
			backup_events($ID,"initialization","INFO, Mysql Database processing",__LINE__);
			backup_mysql($ID);
			backup_events($ID,"initialization","INFO, Artica settings processing",__LINE__);
			backup_artica($ID);
			if($users->ZARAFA_INSTALLED){if($sock->GET_INFO("ZarafaStoreOutside")==1){backup_events($ID,"initialization","INFO, Zarafa external attachments processing...");backup_ZarafaOutside($ID);}}
			backup_events($ID,"initialization","continue to next process",__LINE__);
			continue;				
		}
	}
}else{
	backup_events($ID,"initialization","INFO, Skipping standard macros",__LINE__);
	
}
	
	$sql="SELECT * FROM backup_folders WHERE taskid=$ID";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){
		backup_events($ID,"personal","ERROR, mysql $q->mysql_error",__LINE__);
		return;
	}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$dd1=time();
			if($ligne["recursive"]==1){$recursive=" --recursive";}else{$recursive=null;}
			$path=trim(base64_decode($ligne["path"]));
			if(!is_dir($path)){
				backup_events($ID,"personal","ERROR, [$path] no such file or directory",__LINE__);
				continue;
				
			}
			
			backup_events($ID,"personal","INFO, Backup starting for $path",__LINE__);
			send_email_events("Backup Task $ID:: Backup starting $path","Backup is running for path $path","backup");
			backup_mkdir($path);
			$results=backup_copy($path,$path,$ID);
			$calculate=distanceOfTimeInWords($dd1,time());
			backup_events($ID,"personal","INFO, Backup finish for $path\n$results $calculate",__LINE__);
	}
	
	writelogs(date('m-d H:i:s')." "."[TASK $ID]: Calculate directory size on $mount_path_final",__FUNCTION__,__FILE__,__LINE__);
	$du=$unix->find_program("du");
	$dut1=time();
	$cmd="$du -s $mount_path_final";
	exec($cmd,$du_results);
	$calculate=distanceOfTimeInWords($dut1,time());
	$BackupSize=0;
	if(preg_match("#^([0-9]+)\s+#", @implode("", $du_results),$re)){
		$BackupSize=$re[1];
		backup_events($ID,"initialization","INFO, backup size $BackupSize bytes time:$calculate",__LINE__);
	}
	
	if($GLOBALS["CAN_CLEAN_CONTAINERS"]){
		backup_events($ID,"initialization","INFO, cleaning containers....",__LINE__);
		CleanContainers($ID,$mount_path_final);
	}else{
		backup_events($ID,"initialization","INFO, cannot clean containers, check protocols....",__LINE__);
	}
	
	
	
	$GLOBAL["BACKUP_MEMORY_SQL"]["mount_path_final"]=$mount_path_final;
	$zmd5=md5("{$GLOBAL["BACKUP_MEMORY_SQL"]["CONTAINER"]}{$GLOBALS["MYSERVERNAME"]}");
	$cnx_params=addslashes(base64_encode(serialize($GLOBAL["BACKUP_MEMORY_SQL"])));
	$sql="INSERT IGNORE INTO backup_storages (`taskid`,`size`,`cnx_params`,`zmd5`) VALUES('$ID','$BackupSize','$cnx_params','$zmd5')";
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="UPDATE backup_storages SET `size`='$BackupSize' WHERE `zmd5`='$zmd5'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){backup_events($ID,"initialization","ERROR, $q->mysql_error",__LINE__);}
	
	
	if(!$GLOBALS["NO_UMOUNT"]){
		writelogs(date('m-d H:i:s')." "."[TASK $ID]:umount $mount_path_final",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#^\/opt\/artica\/mounts\/backup\/[0-9]+(.+)#", $mount_path_final,$re)){
			$mount_path_final=str_replace($re[1], "", $mount_path_final);
			writelogs(date('m-d H:i:s')." "."[TASK $ID]:translated to $mount_path_final",__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		
		backup_events($ID,"initialization","INFO, umount $mount_path_final",__LINE__);
		writelogs(date('m-d H:i:s')." "."[TASK $ID]:umount $mount_path_final",__FUNCTION__,__FILE__,__LINE__);
		exec("umount -l $mount_path_final 2>&1",$resultsUmount);
		if(count($resultsUmount)>0){writelogs(date('m-d H:i:s')." "."[TASK $ID]:umount : ----- \n". @implode("\n", $resultsUmount)."\n",__FUNCTION__,__FILE__,__LINE__);}
		
	}
	
	$date_end=time();
	$calculate=distanceOfTimeInWords($date_start,$date_end);
	backup_events($ID,"TIME","INFO, Time: $calculate ($mount_path_final)",__LINE__);	
	backup_events($ID,"initialization","INFO, Backup task terminated",__LINE__);
	send_email_events("Backup Task $ID:: Backup stopping","Backup is stopped","backup");
	
	
	
	shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.cleanfiles.php");
}

function mount_automount($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$unix=new unix();
	$rsync=$unix->find_program("rsync");	
	if(!is_file($rsync)){
		backup_events($ID,"initialization","ERROR, unable to stat rsync ".__FUNCTION__);
		return false;
	}
	
	if(!preg_match("#automount:(.+)#",$pattern,$re)){
		backup_events($ID,"initialization","ERROR, $pattern not seems to be an automount protocol ".__FUNCTION__);
		return false;
	}
	$mount_path=$re[1];
	
	if(!is_dir($mount_path)){
		backup_events($ID,"initialization","ERROR, $mount_path no such directory ".__FUNCTION__);
	}
	if(!$testwrite){return true;}
	
	$md5=md5(date('Y-m-d H:i:s'));
	@file_put_contents("$mount_path/$md5","#");	
	if(!is_file("$mount_path/$md5")){
		backup_events($ID,"initialization","ERROR, (automount) $mount_path/$md5 permission denied");
		return false;	
	}
		
	if(is_file($rsync)){$GLOBALS["COMMANDLINECOPY"]="$rsync -ar {SRC_PATH} {NEXT} --stats --chmod=ug=rwX,o=rwX";}
	if(!is_file($rsync)){$GLOBALS["COMMANDLINECOPY"]="/bin/cp -ru {SRC_PATH} {NEXT}";}
	$GLOBALS["COMMANDLINE_MOUNTED_PATH"]=$mount_path;
			
	writelogs(date('m-d H:i:s')." "."[TASK $ID]: OK !",__FUNCTION__,__FILE__,__LINE__);
	if($GLOBALS["ONLY_TESTS"]){writelogs(date('m-d H:i:s')." "."<H2>{success}</H2>",__FUNCTION__,__FILE__,__LINE__);}
	return true;
	
}


function mount_usb($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$uuid=$backup->extractFirsRessource($pattern);
	$unix=new unix();
	$rsync=$unix->find_program("rsync");
	
	
	
	if($uuid==null){
		backup_events($ID,"initialization","ERROR, (usb) usb protocol error $pattern",__LINE__);
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: usb protocol error $pattern",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$usb=new usb($uuid);
	writelogs(date('m-d H:i:s')." "."[TASK $ID]: $uuid $usb->path FS_TYPE: $usb->ID_FS_TYPE",__FUNCTION__,__FILE__,__LINE__);	
	
	if($usb->ID_FS_TYPE==null){
		backup_events($ID,"initialization","ERROR, (usb) usb type error $pattern",__LINE__);
		return false;
	}
	
	if($usb->path==null){
		backup_events($ID,"initialization","ERROR, (usb) usb dev error $pattern",__LINE__);
		return false;
	}	
	
	$mount=new mount($GLOBALS["ADDLOG"]);
	$mount_path="/opt/artica/mounts/backup/$ID";
	
	if(!$mount->ismounted($mount_path)){
		backup_events($ID,"initialization","ERROR, (usb) local mount point $mount_path not mounted");
		@mkdir($mount_path,null,true);
	}	
	
	if(!$mount->usb_mount($mount_path,$usb->ID_FS_TYPE,$usb->path)){
		backup_events($ID,"initialization","ERROR, (usb) unable to mount target point");
		return false;	
		
	}
	
	if(!$testwrite){writelogs(date('m-d H:i:s')." "."[TASK $ID]: Test write has been cancelled",__FUNCTION__,__FILE__,__LINE__);	return true;}
	$md5=md5(date('Y-m-d H:i:s'));
	writelogs(date('m-d H:i:s')." "."[TASK $ID]: Test write Creating file \"$mount_path/$md5\"",__FUNCTION__,__FILE__,__LINE__);
	
	try {file_put_contents("$mount_path/$md5",time());}catch(Exception $e){$IOERROR=$e->getMessage();} 
	
	
	
	
	if(is_file("$mount_path/$md5")){
		@unlink("$mount_path/$md5");
		if(is_file($rsync)){
			$GLOBALS["COMMANDLINECOPY"]="$rsync -ar {SRC_PATH} {NEXT} --stats --chmod=ug=rwX,o=rwX";
		}else{
			$GLOBALS["COMMANDLINECOPY"]="/bin/cp -ru {SRC_PATH} {NEXT}";
		}
		
		$GLOBALS["COMMANDLINE_MOUNTED_PATH"]=$mount_path;		
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: OK !",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["ONLY_TESTS"]){writelogs(date('m-d H:i:s')." "."<H2>{success}</H2>",__FUNCTION__,__FILE__,__LINE__);}
		return true;
	}else{
		backup_events($ID,"initialization","ERROR, (usb) $mount_path/$md5 $IOERROR");
		backup_events($ID,"initialization","ERROR, (usb) $mount_path/$md5 should be a permission denied (I/O error)");
		$unix=new unix();
		$unix->send_email_events("Backup: task id $ID aborted, unable to write into the device $usb->path FS_TYPE: $usb->ID_FS_TYPE",
		"Artica has tried to write $mount_path/$md5 into this mounted device but it seems that it is impossible\n$IOERROR","backup");
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: Failed !!!, umounting...$mount_path",__FUNCTION__,__FILE__,__LINE__);
		$umount=$unix->find_program("umount");
		exec("$umount -l $mount_path");
	}	
	
	
	
}


		
function CleanContainers($ID,$mount_path_final){
	$sock=new sockets();
	$unix=new unix();
	$ExecBackupMaxContainers=$sock->GET_INFO("ExecBackupMaxContainers");
	if(!is_numeric($ExecBackupMaxContainers)){$ExecBackupMaxContainers=6;}
	events("ExecBackupMaxContainers: $ExecBackupMaxContainers",__LINE__);
	backup_events($ID,"CLEANING","INFO,$ExecBackupMaxContainers Max containers",__LINE__);
	if($ExecBackupMaxContainers==0){backup_events($ID,"initialization","CLEANING, cleaning containers stopped....");return;}
	
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(ID) as tcount FROM backup_storages WHERE taskid=$ID","artica_backup"));
	if(!$q->ok){
		writelogs("$q->mysql_error",__FUNCTION__,__LINE__);
		backup_events($ID,"CLEANING","ERROR, mysql $q->mysql_error",__LINE__);
		return;
	}	
	
	backup_events($ID,"CLEANING","INFO, {$ligne["tcount"]} Containers....",__LINE__);
	events("{$ligne["tcount"]} Containers....",__LINE__);
	if($ligne["tcount"]<$ExecBackupMaxContainers){backup_events($ID,"initialization","CLEANING, cleaning containers stopped....",__LINE__);return;}
	
	$sql="SELECT * FROM backup_storages WHERE taskid=$ID ORDER BY zDate DESC";
	events("$sql",__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){backup_events($ID,"CLEANING","ERROR, mysql $q->mysql_error",__LINE__);return;}
	
	if(preg_match("#backup\.([0-9\-]+)\/(.+?)$#", $mount_path_final,$re)){$mount_path_final=str_replace("backup.{$re[1]}/{$re[2]}", "", $mount_path_final);}
	backup_events($ID,"CLEANING","INFO, Clean containers in $mount_path_final",__LINE__);
	events("Clean containers in `$mount_path_final`",__LINE__);
	
	$unix=new unix();
	$rm=$unix->find_program("rm");
	events("rm = $rm / ExecBackupMaxContainers=$ExecBackupMaxContainers",__LINE__);
	
	$temparay=$unix->dirdir($mount_path_final);
	while (list ($index, $line) = each ($temparay) ){
		events("Found a directory called `$index`",__LINE__);
	}
	
	
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($c>$ExecBackupMaxContainers){
		$cnx_params=unserialize(base64_decode($ligne["cnx_params"]));
		$container=$cnx_params["CONTAINER"];
		if(trim($container)==null){backup_events($ID,"CLEANING","ERROR, {$ligne["ID"]} Container name is null....",__LINE__);continue;}
		$TargetDirectory="$mount_path_final/$container";
		$TargetDirectory=str_replace("//", "/", $TargetDirectory);
		events("Checking = `$TargetDirectory`",__LINE__);
		if(!is_dir($TargetDirectory)){backup_events($ID,"CLEANING","ERROR, {$ligne["ID"]} $TargetDirectory no such directory",__LINE__);continue;}
		events("$rm -rf $TargetDirectory",__LINE__);
		shell_exec("$rm -rf $TargetDirectory");
		if(is_dir("$TargetDirectory")){backup_events($ID,"CLEANING","ERROR, {$ligne["ID"]} $TargetDirectory permission denied",__LINE__);continue;}
		$q->QUERY_SQL("DELETE FROM backup_storages WHERE ID={$ligne["ID"]}","artica_backup");
	}	
		
	$c++;	
		
	}		
}



function mount_smb($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$unix=new unix();
	$rsync=$unix->find_program("rsync");
	$array=$backup->extract_smb_protocol($pattern);
	
	
	if(!is_array($array)){
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: smb protocol error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$mount_path="/opt/artica/mounts/backup/$ID";
	backup_events($ID,"initialization","INFO, local mount point $mount_path (mount_smb())");
	
	include_once(dirname(__FILE__)."/ressources/class.mount.inc");
	if($GLOBALS["VERBOSE"]){backup_events($ID,"initialization","INFO, mount({$GLOBALS["ADDLOG"]})");}
	$mount=new mount($GLOBALS["ADDLOG"]);
	if(!$mount->ismounted($mount_path)){
		backup_events($ID,"initialization","INFO, local mount point $mount_path not mounted (mount_smb())");
		@mkdir($mount_path,null,true);
	}

	
	if(!$mount->smb_mount($mount_path,$array["SERVER"],$array["USER"],$array["PASSWORD"],$array["FOLDER"])){
		backup_events($ID,"initialization","ERROR, unable to mount target server (mount_smb($mount_path,{$array["SERVER"]}))\n".@implode("\n", $GLOBALS["MOUNT_EVENTS"]),__LINE__);
		return false;
	}
	
	
	
	if(!$testwrite){return true;}
	
	$md5=md5(date('Y-m-d H:i:s'));
	exec("/bin/touch $mount_path/$md5 2>&1",$results_touch);
	if(is_file("$mount_path/$md5")){
		@unlink("$mount_path/$md5");
		backup_events($ID,"initialization","INFO, writing test successfully passed OK !");
		if($GLOBALS["ONLY_TESTS"]){writelogs(date('m-d H:i:s')." "."<H2>{success}</H2>",__FUNCTION__,__FILE__,__LINE__);}
		
		if(is_file($rsync)){
			$GLOBALS["COMMANDLINECOPY"]="$rsync -ar --no-p --no-g --no-o --chmod=ug=rwX,o=rwX {SRC_PATH} {NEXT} --stats -v";
		}else{
			$GLOBALS["COMMANDLINECOPY"]="/bin/cp -ru {SRC_PATH} {NEXT}";
		}
		
		$GLOBALS["COMMANDLINE_MOUNTED_PATH"]=$mount_path;		
		
		return true;
	}else{
		$logs_touch=implode("<br>",$results_touch);
		backup_events($ID,"initialization","ERROR, writing test failed");
		writelogs(date('m-d H:i:s')." "."[TASK $ID]: $logs_touch",__FUNCTION__,__FILE__,__LINE__);
		exec("umount -l $mount_path");
	}
}
function ParseMailboxDir($dir){
	$unix=new unix();
	$targetdir=$dir.$GLOBALS["DIRLIST"];
	@mkdir("/usr/share/artica-postfix/ressources/logs/cache");
	@chmod("/usr/share/artica-postfix/ressources/logs/cache",755);
	
	$cachefile="/usr/share/artica-postfix/ressources/logs/cache/".md5($GLOBALS["dirlist"].$targetdir)."list";
	if(is_file($cachefile)){
		if($unix->file_time_min($cachefile)<1441){
			echo @file_get_contents($cachefile);
			return;
		}
	}
	
	if($GLOBALS["dirlist"]){
		if($GLOBALS["USE_RSYNC"]){
			writelogs(date('m-d H:i:s')." "."Using rsync protocol $targetdir",__FUNCTION__,__FILE__,__LINE__);
			$ser=ParseMailboxDirRsync($targetdir);
			$ser=serialize($ser);
			//@file_put_contents($cachefile,$ser);
			echo $ser;
			return;			

		}
		writelogs(date('m-d H:i:s')." "."directory listing $targetdir",__FUNCTION__,__FILE__);
		exec("/usr/share/artica-postfix/bin/artica-install --dirlists $targetdir",$dirs);
		writelogs(count($dirs)." directories",__FUNCTION__,__FILE__);
		$ser=serialize($dirs);
		@file_put_contents($cachefile,$ser);
		echo $ser;
		return;
	}
	
	
	writelogs(date('m-d H:i:s')." "."parsing $targetdir",__FUNCTION__,__FILE__);
	if($GLOBALS["USE_RSYNC"]){
		writelogs(date('m-d H:i:s')." "."Using rsync protocol $dir",__FUNCTION__,__FILE__);
		$dirs=ParseMailboxDirRsync($targetdir);
		writelogs(count($dirs)." directories",__FUNCTION__,__FILE__);
		echo serialize($dirs);
		return;
	}
	
	
	$dirs=$unix->dirdir($targetdir);
	writelogs(count($dirs)." directories",__FUNCTION__,__FILE__);
	echo serialize($dirs);
	}
	
function restorembx($basedContent){
	$GLOBALS["ONNLY_MOUNT"]=true;
	$unix=new unix();
	$rsync=$unix->find_program("rsync");
	$chown=$unix->find_program("chown");
	$sudo=$unix->find_program("sudo");
	$reconstruct=$unix->LOCATE_CYRRECONSTRUCT();
	if(!is_file($rsync)){
		writelogs(date('m-d H:i:s')." "."Unable to stat rsync program",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if(!is_file($reconstruct)){
		writelogs(date('m-d H:i:s')." "."Unable to stat reconstruct program",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
		
	
	$array=unserialize(base64_decode($basedContent));
	$id=$array["taskid"];
	writelogs(date('m-d H:i:s')." "."mounting $id",__FUNCTION__,__FILE__);
	$mounted_dir=backup($id);	
	if($mounted_dir==null){
		writelogs(date('m-d H:i:s')." "."cannot mount task id $id",__FUNCTION__,__FILE__);
		return ;
		}
		
		
		
	$path=$array["path"];
	$uid=$array["uid"];
	
	if(preg_match("#INBOX\/(.+)#",$array["mailbox"],$re)){
		$mailbox=$re[1];
		$cyrus=new cyrus();
		$cyrus->CreateSubDir($uid,$mailbox);
	}else{
		$mailbox=$array["mailbox"];
	}
	$localimapdir=$unix->IMAPD_GET("partition-default");
	if(!is_dir($localimapdir)){writelogs(date('m-d H:i:s')." "."Unable to stat local partition-default",__FUNCTION__,__FILE__,__LINE__);return;}
	$userfs=str_replace(".","^",$uid);
	$firstletter=substr($userfs,0,1);
	$localuserfs="$localimapdir/$firstletter/user/$userfs";
	$localimapdir="$localimapdir/$firstletter/user/$userfs/";
	if(!is_dir($localimapdir)){writelogs(date('m-d H:i:s')." "."Unable to stat local \"$localimapdir\"",__FUNCTION__,__FILE__,__LINE__);return;}
	
	
	
	$remoteimapdir="$mounted_dir/$path/$mailbox";
	@mkdir($localimapdir,null,true);
	
	if(substr($remoteimapdir,strlen($remoteimapdir)-1,1)<>"/"){$remoteimapdir=$remoteimapdir."/";}
	
	
	 $cmd="$rsync -z --stats $remoteimapdir* $localimapdir 2>&1";
	if($GLOBALS["USE_RSYNC"]){
		$backup=new backup_protocols();
		writelogs(date('m-d H:i:s')." "."Using rsync protocol",__FUNCTION__,__FILE__,__LINE__);
		$array_config=$backup->extract_rsync_protocol($remoteimapdir);
		if(!is_array($array)){
			writelogs(date('m-d H:i:s')." "."[TASK $ID]: rsync protocol error",__FUNCTION__,__FILE__,__LINE__);
			return false;
		}	
		
		if($array_config["PASSWORD"]<>null){
			$tmpstr="/opt/artica/passwords/".md5($array_config["PASSWORD"]);
			@mkdir("/opt/artica/passwords",null,true);
			@file_put_contents($tmpstr,$array_config["PASSWORD"]);
			$pwd=" --password-file=$tmpstr";
		}
	
		if($array["USER"]<>null){
			$user="{$array["USER"]}@";
		}
		
		$cmd="$rsync$pwd --stats rsync://$user{$array_config["SERVER"]}/{$array_config["FOLDER"]}*  $localimapdir 2>&1";
		
		
		
	}
	writelogs(date('m-d H:i:s')." "."Restore from $remoteimapdir",__FUNCTION__,__FILE__,__LINE__);
	writelogs(date('m-d H:i:s')." "."Restore to $localimapdir",__FUNCTION__,__FILE__,__LINE__);
	writelogs(date('m-d H:i:s')." "."reconstruct path $reconstruct",__FUNCTION__,__FILE__,__LINE__);
	writelogs(date('m-d H:i:s')." "."$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$rsynclogs);
	
	$i=0;
	while (list ($num, $line) = each ($rsynclogs)){
		if(preg_match("#Number of files transferred:\s+([0-9]+)#",$line,$re)){$GLOBALS["events"][]="Files restored: {$re[1]}";}
		if(preg_match("#Total transferred file size:\s+([0-9]+)#",$line,$re)){$bytes=$re[1];$re[1]=round(($re[1]/1024)/1000)."M";$GLOBALS["events"][]="{$re[1]} size restored ($bytes bytes)";}		
		if(preg_match("#Permission denied#",$line)){$i=$i+1;}
		
	}
	$GLOBALS["events"][]="$i file(s) on error";
	shell_exec("$chown -R cyrus:mail $localuserfs");
	shell_exec("/bin/chmod -R 755 $localuserfs");
	
	$cmd="$sudo -u cyrus $reconstruct -r -f user/$uid 2>&1";
	writelogs(date('m-d H:i:s')." "."$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$rsynclogs);
	$GLOBALS["events"][]="Reconstruct information: ";
	while (list ($num, $line) = each ($rsynclogs)){
		$GLOBALS["events"][]="reconstructed path: $line";
	}	
	
	writelogs(date('m-d H:i:s')." "."restarting imap service",__FUNCTION__,__FILE__,__LINE__);
	system("/etc/init.d/artica-postfix restart imap");
	print_r($GLOBALS["events"]);
	
}



function mount_rsync($pattern,$ID,$testwrite=true){
	$backup=new backup_protocols();
	$unix=new unix();
	$rsync=$unix->find_program("rsync");
	
	if(!is_file($rsync)){
		backup_events($ID,"initialization","ERROR, unable to stat rsync ".__FUNCTION__);
		return false;
	}
	
	$array=$backup->extract_rsync_protocol($pattern);
	if(!is_array($array)){
		backup_events($ID,"initialization","ERROR, rsync protocol error ".__FUNCTION__);
		return false;
	}	
	
	backup_events($ID,"initialization","INFO, " .strlen($array["PASSWORD"])." length password ".__FUNCTION__);
	
	if($array["PASSWORD"]<>null){
		@mkdir("/root/.backup.pwd",600,true);
		$tmpstr="/root/.backup.pwd/$ID";
		@file_put_contents($tmpstr,$array["PASSWORD"]);
		if(!is_file($tmpstr)){
			backup_events($ID,"initialization","ERROR, $tmpstr no such file or directory ".__FUNCTION__);
			return false;
		}
		@chmod($tmpstr,600);
		@chown($tmpstr,"root:root");
		$pwd=" --password-file=$tmpstr";
	}
	
	if($array["USER"]<>null){
		$user="{$array["USER"]}@";
	}
	
	$fp=@fsockopen($array["SERVER"], 873, $errno, $errstr, 2);
	if(!$fp){
		backup_events($ID,"initialization","ERROR, Failed to connect to {$array["SERVER"]}:873 ($errstr) ".__FUNCTION__);
		@fclose($fp);
		return false;
	}
	backup_events($ID,"initialization","INFO,{$array["SERVER"]}:873 connection success",__FUNCTION__);
	@fclose($fp);	
	
	$pattern_list="$rsync --list-only$pwd rsync://$user{$array["SERVER"]}/{$array["FOLDER"]} --stats --dry-run 2>&1";
	
	if($GLOBALS["DEBUG"]){echo "mount_rsync():: Listing files or directories using \"$pattern_list\"\n";}
	
	exec($pattern_list,$results);
	
	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#\@ERROR#",$line)){
		backup_events($ID,"initialization","ERROR, failed to connect rsync://$user{$array["SERVER"]}/{$array["FOLDER"]}".__FUNCTION__);
		if($GLOBALS["DEBUG"]){echo "mount_rsync()::  found  \"$line\"\n";}
		}
		
		if(preg_match("#Number of files#",$line)){
			$GLOBALS["COMMANDLINECOPY"]="$rsync -ar --chmod=ug=rwX,o=rwX {SRC_PATH} rsync://$user{$array["SERVER"]}/{$array["FOLDER"]}/{NEXT} --stats $pwd";
			$GLOBALS["COMMANDLINE_MOUNTED_PATH"]=null;
			return true;
		
		}
	}

	backup_events($ID,"initialization","ERROR, No information has been returned... ".__FUNCTION__);
	system("/bin/rm -rf /tmp/artica-temp");
}

function ParseMailboxDirRsync($pattern){
	$backup=new backup_protocols();
	$unix=new unix();
	$rsync=$unix->find_program("rsync");

	$array=$backup->extract_rsync_protocol($pattern);
	if(!is_array($array)){
		writelogs(date('m-d H:i:s')." "."rsync protocol error",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	
	if($array["PASSWORD"]<>null){
		$tmpstr=$unix->FILE_TEMP();
		@file_put_contents($tmpstr,$array["PASSWORD"]);
		$pwd=" --password-file=$tmpstr";
	}
	
	if($array["USER"]<>null){
		$user="{$array["USER"]}@";
	}	
	
	$pattern_list="$rsync --list-only$pwd rsync://$user{$array["SERVER"]}/{$array["FOLDER"]}/ --stats --dry-run 2>&1";
	
	if($GLOBALS["DEBUG"]){echo "ParseMailboxDirRsync():: Listing files or directories using \"$pattern_list\"\n";}
	
	writelogs(date('m-d H:i:s')." "."$pattern_list",__FUNCTION__,__FILE__,__LINE__);
	exec($pattern_list,$results);
	@unlink($tmpstr);
	unset($array);
	
	while (list ($num, $line) = each ($results)){
		
		if(preg_match("#^d[rwx\-]+\s+[0-9]+\s+[0-9\/]+\s+[0-9\:]+\s+(.+)#",$line,$re)){
			writelogs($re[1],__FUNCTION__,__FILE__,__LINE__);
			if(trim($re[1])=='.'){continue;}
			$array[trim($re[1])]=trim($re[1]);
			continue;
		}
	}
	
	return $array;
	
}

function backup_ldap($ID){
	$unix=new unix();
	$slapcat=$unix->find_program("slapcat");
	if($slapcat==null){
		backup_events($ID,"ldap","ERROR, unable to stat slapcat");
		return false;
	}
	backup_events($ID,"ldap","INFO, exporting local database");
	shell_exec("$slapcat -l /tmp/ldap.ldif");
	
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/ldap_backup");
	if(!backup_isdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/ldap_backup")){
		backup_events($ID,"ldap","ERROR, ldap_backup permission denied or no such file or directory");
		return false;
	}
	
	$info=backup_copy("/tmp/ldap.ldif","{$GLOBALS["MOUNTED_PATH_FINAL"]}/ldap_backup",$ID);
	$ldap=new clladp();
	@file_put_contents("/tmp/suffix",$ldap->suffix);
	$info=backup_copy("/tmp/ldap.ldif","{$GLOBALS["MOUNTED_PATH_FINAL"]}/ldap_backup",$ID); 
}


function backup_mysql_oldway($ID){
	
	$sock=new sockets();	
	$unix=new unix();
	$date_start=time();

	
	$array=backup_mysql_databases_list($ID);
	if(!is_array($array)){
		events("ERROR, unable to get databases list",__FUNCTION__,__LINE__);
		send_email_events("Backup Task $ID:: Unable to backup mysql datas ","ERROR, unable to get databases list","backup");
		backup_events($ID,"mysql","ERROR, unable to get databases list",__LINE__);
		return false;		
	}	
	
	$q=new mysql();
	if($q->mysql_password<>null){$password=" --password=$q->mysql_password";}
	if($q->mysql_admin<>null){$user=" --user=$q->mysql_admin";}
	$temporarySourceDir=$sock->GET_INFO("ExecBackupTemporaryPath");
	if($temporarySourceDir==null){$temporarySourceDir="/home/mysqlhotcopy";}
	$temporarySourceDir="$temporarySourceDir/mysql";
	events("temporarySourceDir has ExecBackupTemporaryPath token was \"$temporarySourceDir\"",__FUNCTION__,__LINE__);
	events("Creating $temporarySourceDir",__FUNCTION__,__LINE__);	
	while (list ($num, $line) = each ($array)){
		if(trim($line)==null){continue;}
		$database_name=trim(basename($line));
		backup_events($ID,"mysql","INFO, dumping $database_name database",__LINE__);
		backup_mysql_database_mysqldump($ID,$database_name,$temporarySourceDir);
	}
	
	events("Create directory \"{$GLOBALS["MOUNTED_PATH_FINAL"]}/mysql\"",__FUNCTION__,__LINE__);
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/mysql");
	backup_copy("$temporarySourceDir/*","mysql",$ID);
	backup_events($ID,"mysql","INFO, backup remove content of $temporarySourceDir/*",__LINE__);
	events("INFO, backup remove content of $temporarySourceDir/*",__FUNCTION__,__LINE__);
	if(is_dir($temporarySourceDir)){
		events("/bin/rm -rf $temporarySourceDir/*",__FUNCTION__,__LINE__);
		shell_exec("/bin/rm -rf $temporarySourceDir/*");
	}
	backup_events($ID,"mysql","INFO, backup END without known error");
	
	$date_end=time();
	$calculate=distanceOfTimeInWords($date_start,$date_end);
	events("INFO, time: $calculate",__FUNCTION__,__LINE__);
	backup_events($ID,"mysql","INFO, time: $calculate");	
	
}


function backup_mysql($ID){
	$sock=new sockets();	
	$unix=new unix();
	$date_start=time();
	$mysqlhotcopy=$unix->find_program("mysqlhotcopy");
	$email_spacer="==========================================";
	
	
	if($mysqlhotcopy==null){
		backup_events($ID,"mysql","ERROR, unable to stat mysqlhotcopy");
		if(is_file($unix->find_program("mysqldump"))){
			backup_events($ID,"mysql","INFO, switch to mysqldump processing");
			return backup_mysql_oldway($ID);
		}
		events("ERROR, unable to stat mysqlhotcopy",__FUNCTION__,__LINE__);
		send_email_events("Backup Task $ID:: Unable to backup mysql datas ","ERROR, unable to stat mysqlhotcopy","backup");
		return false;
	}
	
	$array=backup_mysql_databases_list($ID);
	if(!is_array($array)){
		events("ERROR, unable to get databases list",__FUNCTION__,__LINE__);
		send_email_events("Backup Task $ID:: Unable to backup mysql datas ","ERROR, unable to get databases list","backup");
		backup_events($ID,"mysql","ERROR, unable to get databases list",__LINE__);
		return false;		
	}
	
	
	$q=new mysql();
	if($q->mysql_password<>null){$password=" --password=$q->mysql_password";}
	if($q->mysql_admin<>null){$user=" --user=$q->mysql_admin";}
	$temporarySourceDir=$sock->GET_INFO("ExecBackupTemporaryPath");
	if($temporarySourceDir==null){$temporarySourceDir="/home/mysqlhotcopy";}
	$temporarySourceDir="$temporarySourceDir/mysql";
	events("temporarySourceDir has ExecBackupTemporaryPath token was \"$temporarySourceDir\"",__FUNCTION__,__LINE__);
	events("Creating $temporarySourceDir",__FUNCTION__,__LINE__);
	
	
	@mkdir($temporarySourceDir,0666,true);
	if(!is_dir($temporarySourceDir)){
		events("Creating Unable to backup mysql datas ","ERROR, $temporarySourceDir permission denied or no such file or directory",__FUNCTION__,__LINE__);
		send_email_events("Backup Task $ID:: Unable to backup mysql datas ","ERROR, $temporarySourceDir permission denied or no such file or directory","backup");
		backup_events($ID,"mysql","ERROR, $temporarySourceDir permission denied or no such file or directory");
		return ;
	}
	
	backup_events($ID,"mysql","INFO, using $temporarySourceDir for temp backup");
	
	while (list ($num, $line) = each ($array)){
		if(trim($line)==null){continue;}
		$database_name=trim(basename($line));
		
		if(strtolower($database_name)=="log"){
			events("skipping database \"$database_name\"",__FUNCTION__,__LINE__);
			backup_events($ID,"mysql","INFO, mysqlhotcopy skip log database",__LINE__);
			continue;
		}
		
		if(strtolower($database_name)=="mysql"){
			events("skipping database \"$database_name\"",__FUNCTION__,__LINE__);
			backup_events($ID,"mysql","INFO, mysqlhotcopy skip mysql database",__LINE__);
			continue;
		}
				
		backup_events($ID,"mysql","INFO, mysqlhotcopy database ($database_name) stored in $line -> $temporarySourceDir");
		
		$mysqlhotcopy_command="$mysqlhotcopy --addtodest$user$password $database_name $temporarySourceDir 2>&1";
		events("\"$mysqlhotcopy_command\"",__FUNCTION__,__LINE__);
		exec($mysqlhotcopy_command,$results);
		
		while (list ($num_line, $evenement) = each ($results)){
			if(preg_match("#No space left on device#",$evenement)){
				events("ERROR, backup No space left on device ($temporarySourceDir)",__FUNCTION__,__LINE__);
				backup_events($ID,"mysql","ERROR, backup No space left on device ($temporarySourceDir)\n". implode("\n",$results));
				if(is_dir($temporarySourceDir)){shell_exec("/bin/rm -rf $temporarySourceDir/*");}
				return;
			}
			events("$evenement",__FUNCTION__,__LINE__);
			
			if(preg_match("#failed:#",$evenement)){
				events("ERROR, database: \"$database_name\" ($evenement)",__FUNCTION__,__LINE__);
				backup_events($ID,"mysql","ERROR, database: \"$database_name\" ($evenement)");
				$fulltext=@implode("\n",$results);
				send_email_events("Backup Task $ID:: database: \"$database_name\" failed to backup ","$email_spacer\n$mysqlhotcopy_command$email_spacer\nERROR:$email_spacer\n$evenement\n$email_spacer\n$fulltext","backup");
			}		
		}
		
		backup_events($ID,"mysql","INFO, backup $database_name\n". implode("\n",$results),__LINE__);
		
		if(strtolower($database_name)=="zarafa"){
			events("zarafa database -> backup_mysql_database_mysqldump($ID,zarafa,$temporarySourceDir)",__FUNCTION__,__LINE__);
			backup_mysql_database_mysqldump($ID,"zarafa",$temporarySourceDir);
		}

	}	
	backup_events($ID,"mysql","INFO, Send mysql backup to the \n". implode("\n",$results));
	
	
	events("Create directory \"{$GLOBALS["MOUNTED_PATH_FINAL"]}/mysql\"",__FUNCTION__,__LINE__);
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/mysql");
	backup_copy("$temporarySourceDir/*","mysql",$ID);
	backup_events($ID,"mysql","INFO, backup remove content of $temporarySourceDir/*");
	events("INFO, backup remove content of $temporarySourceDir/*",__FUNCTION__,__LINE__);
	if(is_dir($temporarySourceDir)){
		events("/bin/rm -rf $temporarySourceDir/*",__FUNCTION__,__LINE__);
		shell_exec("/bin/rm -rf $temporarySourceDir/*");
	}
	backup_events($ID,"mysql","INFO, backup END without known error");
	
	$date_end=time();
	$calculate=distanceOfTimeInWords($date_start,$date_end);
	events("INFO, time: $calculate",__FUNCTION__,__LINE__);
	backup_events($ID,"mysql","INFO, time: $calculate");
	
}

function backup_mysql_database_mysqldump($ID,$database,$temporarySourceDir){
	$date_start=time();
	$q=new mysql();
	$sock=new sockets();
	$NoBzipForBackupDatabasesDump=$sock->GET_INFO("NoBzipForBackupDatabasesDump");
	if($NoBzipForBackupDatabasesDump==null){$NoBzipForBackupDatabasesDump=1;}
	if($temporarySourceDir==null){$temporarySourceDir="/home/mysqlhotcopy";}
	if($q->mysql_password<>null){$password=" -p$q->mysql_password";}
	if($q->mysql_admin<>null){$user=" -u $q->mysql_admin";}
	
	if(!is_dir($temporarySourceDir)){@mkdir($temporarySourceDir,755,true);}
	
	
	$unix=new unix();
	$mysqldump=$unix->find_program("mysqldump");
	$bzip2=$unix->find_program("bzip2");
	if($mysqldump==null){backup_events($ID,"mysql","ERROR, Unable to find mysqldump",__LINE__);return;}
	
	if(!is_dir(dirname($target_file))){@mkdir(dirname($target_file),755,true);}
	$target_file="$temporarySourceDir/$database.tar.bz2";
	$bzip2_cmd="| $bzip2 ";
	
	if($NoBzipForBackupDatabasesDump==1){
		$bzip2_cmd=null;
		$target_file="$temporarySourceDir/$database.sql";
	}
	
	$cmd="$mysqldump$user$password --single-transaction --skip-add-locks --skip-lock-tables $database $bzip2_cmd> $target_file 2>&1";
	if($GLOBALS["VERBOSE"]){writelogs($cmd,__FUNCTION__,__FILE__,__LINE__);}
	backup_events($ID,"mysql","INFO, Dumping $database mysql database",__LINE__);
	exec($cmd,$results);
	$date_end=time();
	
	$calculate=distanceOfTimeInWords($date_start,$date_end);
	
	backup_events($ID,"mysql","INFO, $database $calculate",__LINE__);
	
	while (list ($num_line, $evenement) = each ($results)){
			if($GLOBALS["VERBOSE"]){writelogs($evenement,__FUNCTION__,__FILE__,__LINE__);}
			if(preg_match("#Error\s+([0-9]+)#",$evenement)){
				backup_events($ID,"mysql","ERROR, $evenement",__LINE__);
				return;
			}
		}	
	
	
	if(!is_file("$target_file")){backup_events($ID,"mysql","ERROR, Dumping $database mysql database failed, $target_file no such file or directory",__LINE__);return;}
	$size=$unix->file_size_human("$target_file");
	backup_events($ID,"mysql","INFO, END dumping $database mysql database ($size)",__LINE__);
}

function backup_mysql_databases_list($ID){
	$users=new usersMenus();
	$mysqldir=$users->mysqld_datadir;
	if(!is_dir($mysqldir)){
		backup_events($ID,"mysql","ERROR, unable to stat directory ($mysqldir)");
		return;
	}
	$unix=new unix();
	$array=$unix->dirdir($mysqldir);
	while (list ($num, $line) = each ($array)){
		writelogs("Found $line db",__FUNCTION__,__FILE__,__LINE__);
		$results[]=$line;
	}
	return $results;
}

function backup_artica($ID){
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/etc-artica-postfix");
	backup_copy("/etc/artica-postfix","etc-artica-postfix",$ID);
	backup_events($ID,"mysql","INFO, backup Artica done",__LINE__);
}

function backup_ZarafaOutside($ID){
	$sock=new sockets();
	$ZarafaStoreOutsidePath=$sock->GET_INFO("ZarafaStoreOutsidePath");
	if($ZarafaStoreOutsidePath==null){$ZarafaStoreOutsidePath="/var/lib/zarafa";}
	backup_events($ID,"Zarafa","INFO, Backup external attachments $ZarafaStoreOutsidePath");
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/zarafa-attachments");
	backup_copy("$ZarafaStoreOutsidePath","zarafa-attachments",$ID);
}


function backup_cyrus($ID){
	$date_start=time();
	$users=new usersMenus();
	if(!$users->cyrus_imapd_installed){
		backup_events($ID,"cyrus-imap","INFO, cyrus-impad NOT Installed");
		return true;
	}
	
	if($GLOBALS["COMMANDLINECOPY"]==null){
		backup_events($ID,"cyrus-imap","ERROR, COMMANDLINECOPY is null");
		return false;
	}
	
	$partition_default=$users->cyr_partition_default;
	$config_directory=$users->cyr_config_directory;

	backup_events($ID,"cyrus-imap","INFO, partition-default=$partition_default\nDirectory config=$config_directory");
	
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/cyrus-imap/partitiondefault");
	backup_mkdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/cyrus-imap/configdirectory");
	
	if(!backup_isdir("{$GLOBALS["MOUNTED_PATH_FINAL"]}/cyrus-imap/configdirectory")){
		backup_events($ID,"cyrus-imap","ERROR, $mounted_path/cyrus-imap/configdirectory permission denied or no such file or directory");
		return false;
	}

	$info=backup_copy($config_directory,"{$GLOBALS["MOUNTED_PATH_FINAL"]}/cyrus-imap/configdirectory",$ID);
	backup_events($ID,"cyrus-imap","INFO,configdirectory\n$info");
	$info=backup_copy($partition_default,"{$GLOBALS["MOUNTED_PATH_FINAL"]}/cyrus-imap/partitiondefault",$ID);
	backup_events($ID,"cyrus-imap","INFO,partitiondefault\n$info");
	$cmd="su - cyrus -c \"$users->ctl_mboxlist -d >/tmp/mailboxlist.txt\"";
	exec($cmd,$results);
	
	if(!is_file("/tmp/mailboxlist.txt")){
		backup_events($ID,"cyrus-imap","ERROR,unable to export mailbox list\n$cmd\n".implode("\n",$results));
	}
	$info=backup_copy("/tmp/mailboxlist.txt","{$GLOBALS["MOUNTED_PATH_FINAL"]}/cyrus-imap/mailboxlist.txt",$ID);
	backup_events($ID,"cyrus-imap","INFO, mailboxlist.txt\n$info");
	$date_end=time();
	$calculate=distanceOfTimeInWords($date_start,$date_end);
	backup_events($ID,"cyrus-imap","INFO, time: $calculate");
	
}

function backup_isdir($path){
	$results[]="SRC: $path";
	if($GLOBALS["USE_RSYNC"]){
		$cmd=str_replace("{SRC_PATH}","--list-only",$GLOBALS["COMMANDLINECOPY"]);
		$cmd=str_replace("-ar"," ",$cmd);
		$cmd=str_replace("{NEXT}","$path",$cmd);
		exec($cmd,$results);
		while (list ($num, $line) = each ($results)){if(preg_match("#No such file or directory#",$line)){return false;}}
		return true;
	}
	
	return is_dir($path);
	
}

function backup_copy($source_path,$dest_path,$ID=null){
		$date_start=time();
		$cmd=str_replace("{SRC_PATH}",$source_path,$GLOBALS["COMMANDLINECOPY"]);
		$GLOBALS["MOUNTED_PATH_FINAL"]=trim($GLOBALS["MOUNTED_PATH_FINAL"]);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] #########################################",__FUNCTION__,__FILE__,__LINE__);
		
		if($GLOBALS["PCOPY"]){
			writelogs(date('m-d H:i:s')." "."[TASK $ID] Protocol used is a local copy (PCOPY = TRUE) ",__FUNCTION__,__FILE__,__LINE__);
		}
		
		writelogs(date('m-d H:i:s')." "."[TASK $ID] Starting point {$GLOBALS["MOUNTED_PATH_FINAL"]}",__FUNCTION__,__FILE__,__LINE__);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] command line=$cmd",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["MOUNTED_PATH_FINAL"]<>null){
			$dest_path=str_replace($GLOBALS["MOUNTED_PATH_FINAL"],"",$dest_path);
			writelogs(date('m-d H:i:s')." "."[TASK $ID] dest_path=\"$dest_path\"",__FUNCTION__,__FILE__,__LINE__);
		}
		
		$final_path="{$GLOBALS["MOUNTED_PATH_FINAL"]}/$dest_path";
		$final_path=str_replace('//','/',$final_path);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] final_path=\"$final_path\"",__FUNCTION__,__FILE__,__LINE__);
		$cmd=str_replace("{NEXT}",$final_path,$cmd);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] Copy directory $source_path to \"$final_path\"",__FUNCTION__,__FILE__,__LINE__);
		
		if($GLOBALS["PCOPY"]){
			if(is_dir($source_path)){
				writelogs(date('m-d H:i:s')." "."[TASK $ID] $source_path is a directory...",__FUNCTION__,__FILE__,__LINE__);
				if(!is_dir($final_path)){
					writelogs(date('m-d H:i:s')." "."[TASK $ID] testing $final_path no such directory create it",__FUNCTION__,__FILE__,__LINE__);
					try {mkdir($final_path,644,true);}catch(Exception $e){$IOERROR=$e->getMessage();} 
					if(!is_dir($final_path)){
						backup_events($ID,"Copy","ERROR,Dir $final_path $IOERROR",__LINE__);
						return false;
					}
				}
			}
			
			if(is_file($source_path)){
				writelogs(date('m-d H:i:s')." "."[TASK $ID] $source_path is a file...",__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
		
		
		writelogs(date('m-d H:i:s')." "."[TASK $ID] FINAL COMMAND WAS \"$cmd\"",__FUNCTION__,__FILE__,__LINE__);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] EXECUTE....",__FUNCTION__,__FILE__,__LINE__);
		events("$cmd",__FUNCTION__,__LINE__);
		
		exec($cmd. " 2>&1",$results);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] Returning an array of ".count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
		if(!check_rsync_error($ID,$results)){
			events("check_rsync_error() !",__FUNCTION__,__LINE__);
			if($ID>0){backup_events($ID,"Copy","ERROR,$cmd",__LINE__);}
		}else{
			if($ID>0){backup_events($ID,"Copy","INFO,$cmd",__LINE__);}
		}
		
		
		$date_end=time();
		$calculate=distanceOfTimeInWords($date_start,$date_end);
		events("INFO, time: $calculate ($source_path)",__FUNCTION__,__LINE__);
		backup_events($ID,"Copy","INFO, time: $calculate ($source_path)",__LINE__);
		writelogs(date('m-d H:i:s')." "."[TASK $ID] #########################################",__FUNCTION__,__FILE__,__LINE__);
		
		
		return @implode("\n",$results);
		
}

function check_rsync_error($ID,$results){
	if(!is_array($results)){return true;}
		while (list ($num, $line) = each ($results)){
			if(preg_match("#rsync error#",$line)){
			 if(preg_match("#some files\/attrs were not transferred#",$line)){continue;}
			 if(preg_match("#some files vanished before they could be transferred#",$line)){continue;}
			 writelogs(date('m-d H:i:s')." "."[TASK $ID]: $line ",__FUNCTION__,__FILE__,__LINE__);
			 if($ID>0){backup_events($ID,"Copy","ERROR,$line\n$cmd",__LINE__);return false;}
			}
		}
	return true;	
	}


function backup_mkdir($path){
	$USE_RSYNC=$GLOBALS["USE_RSYNC"];
	if(preg_match("#bin\/cp\s+-#",$GLOBALS["COMMANDLINECOPY"])){$USE_RSYNC=false;}
		
		

	
	
	if($USE_RSYNC){
		writelogs(date('m-d H:i:s')." "."create directory /tmp/artica-temp/$path",__FUNCTION__,__FILE__,__LINE__);
		@mkdir("/tmp/artica-temp/$path",600,true);
		chdir("/tmp/artica-temp");
		@file_put_contents("/tmp/artica-temp/$path/.default","#");
		writelogs(date('m-d H:i:s')." "." COMMANDLINECOPY={$GLOBALS["COMMANDLINECOPY"]}",__FUNCTION__,__FILE__,__LINE__);
		$cmd=str_replace("{SRC_PATH}","/tmp/artica-temp/*",$GLOBALS["COMMANDLINECOPY"]);
		$cmd=str_replace("{NEXT}","",$cmd);
		
		if($cmd==null){
			writelogs("Warning, no command-line copy has been defined....",__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		
		events($cmd,__LINE__);
		system($cmd);
		shell_exec("/bin/rm -rf /tmp/artica-temp/*");
		chdir("/root");
		return;
	}
	
	@mkdir("$path",0600,true);
	if(!is_dir("$path")){
		writelogs("Unable to create directory $path no such file or directory",__FUNCTION__,__FILE__,__LINE__);
	}
}


function backup_events($task_id,$source_type,$text,$line){
	events("[TASK $task_id]: $text",$line);
	if(!isset($line)){$line="not defined";}
	$text=addslashes($text);
	$date=date('Y-m-d H:i:s');
	writelogs(date('m-d H:i:s')." "."[TASK $task_id]: $text L.$line",__FUNCTION__,__FILE__,__LINE__);
	$sql="INSERT INTO `backup_events`(task_id,zdate,backup_source,event) VALUES('$task_id','$date','$source_type','$text');";
	$md5=md5($sql);
	if(!$GLOBALS["ONLY_TESTS"]){
		@file_put_contents("/var/log/artica-postfix/sql-events-queue/$md5.sql",$sql);
	}
}
function events($text,$function,$line=0){
		$file=basename(__FILE__);
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-backup-php.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$file][$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}	


function ParseMysqlEventsQueue(){
	$q=new mysql();
	foreach (glob("/var/log/artica-postfix/sql-events-queue/*.sql") as $filename) {
			$sql=@file_get_contents($filename);
			$q->QUERY_SQL($sql,"artica_events");
			if($q->ok){
				@unlink($filename);
			}
		}	
	}
?>
