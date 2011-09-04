<?php
if(is_file("/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE")){die();}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.demime.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');




if($argv[1]=="--computers-schedule"){
	ScheduleComputers();
	die();
}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__)." Already executed.. aborting the process");
	die();
}

rsync_queue();
rsync_server_queue();
dar_queue();

die();


function rsync_server_queue(){
	$q=new mysql();
	$q->BuildTables();
	foreach (glob("/var/log/artica-postfix/rsync/*") as $filename) {
		ScanRsyncServer($filename);
	}
}


function rsync_queue(){
		@mkdir("/var/log/artica-postfix/rsync-queue",0755,true);
		$array=DirList("/var/log/artica-postfix/rsync-queue");
		
		if(!is_array($array)){
			events("No files in queue...");
			return null;
		}
		
		
		
		events("Processing ".count($array)." files");
		
			while (list ($num, $file) = each ($array) ){
			if(ScanFile($file)){
				@unlink("/var/log/artica-postfix/rsync-queue/$file");
			}else{
				events("Processing /var/log/artica-postfix/rsync-queue/$file failed");
			}
				
				
			}
	
}	

function dar_queue(){
		$array=DirList_queue("/var/log/artica-postfix/dar-queue");
		
		if(!is_array($array)){
			events("No files in queue...");
			return null;
		}
		
		
		
		events("Processing ".count($array)." files in /var/log/artica-postfix/dar-queue");
		
			while (list ($num, $file) = each ($array) ){
			if(DarFile($file)){
				@unlink("/var/log/artica-postfix/dar-queue/$file");
			}else{
				events("Processing /var/log/artica-postfix/dar-queue/$file failed");
			}
				
				
			}
	
}




function DarFile($filename){
	events("Processing $filename file");
	$target_file="/var/log/artica-postfix/dar-queue/$filename";
	$ini=new Bs_IniHandler($target_file);
	$sql="INSERT INTO dar_events (date_start,date_end,db_path,xml,source,failed,builded,error)
		VALUES('{$ini->_params["INCREMENTAL"]["started_on"]}',
		'{$ini->_params["INCREMENTAL"]["finish_on"]}',
		'{$ini->_params["INCREMENTAL"]["db_path"]}',
		'{$ini->_params["INCREMENTAL"]["xml"]}',
		'{$ini->_params["INCREMENTAL"]["ressource"]}',
		'{$ini->_params["INCREMENTAL"]["failed"]}',0,'{$ini->_params["INCREMENTAL"]["error"]}')";

$q=new mysql();
$q->QUERY_SQL($sql,'artica_events');
return $q->ok;	
}

function ScanRsyncServer($filename){
	
	$datas=unserialize(@file_get_contents($filename));
	if(!is_array($datas)){return null;}
	
	$sql="INSERT INTO rsync_server (ip_address,zDate,transaction_size)
	VALUES('{$datas["IP"]}','{$datas["DATE"]}','{$datas["SIZE"]}');";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){return null;}
	@unlink($filename);
	
	
}

	
function ScanFile($filename){

	$target_file="/var/log/artica-postfix/rsync-queue/$filename";
	if(!is_file($target_file)){return false;}
	
	$datas=@file_get_contents($target_file);
	
	if(preg_match('#<started_on>(.+)</started_on>#',$datas,$re)){
		$start_on=$re[1];
	}
	
	if(preg_match('#<finishon>(.+)</finishon>#',$datas,$re)){
		$start_off=$re[1];
	}	
	
	if(preg_match('#<rsyncevents>(.*?)</rsyncevents>#is',$datas,$re)){
		$events=$re[1];
	}

	if(preg_match('#<folder>(.*?)</folder>#is',$datas,$re)){
		$folder_log=$re[1];
	}

	if(preg_match('#<foldermd5>(.*?)</foldermd5>#is',$datas,$re)){
		$foldermd5=$re[1];
	}	

	if(preg_match('#<server>(.*?)</server>#is',$datas,$re)){
		$storage_server=$re[1];
	}		
	
	

	$failed=0;
	$events_bytes=strlen($events);
	if($events_bytes==0){$failed=1;}
	else{if(preg_match("#rsync error#is",$events)){$failed=1;}}

if($failed==0){		
	if(preg_match("#Number of files transferred:\s+([0-9]+)#is",$events,$re)){$num_files=$re[1];}
}
	
if(preg_match("#sent\s+([0-9]+)\s+bytes\s+received.+?bytes\s+([0-9\.]+)\s+bytes\/sec#is",$events,$re)){
	$sent=round($re[1]/1024,2);
	$speed=round($re[2]/1024,2);
}

$events=addslashes($events);
	
events("Processing $filename events=$events_bytes bytes, start=$start_on, finish=$start_off failed=$failed, num files=$num_files sent={$sent}KB speed=$speed KB/s");
if($sent_size==null){$sent_size=0;}
if($num_files==null){$num_files=0;}
if($speed==null){$speed=0;}

$sql="INSERT INTO rsync_events (folder_md5,date_start,date_end,events,path,failed,sent_size,speed,numfiles,storage_server)
VALUES('$foldermd5','$start_on','$start_off','$events','$folder_log','$failed','$sent_size','$speed','$num_files','$storage_server')";

$q=new mysql();
$q->QUERY_SQL($sql,'artica_events');
return $q->ok;

	
}




function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile="/var/log/artica-postfix/artica-rsync.debug";
		$size=@filesize($logFile);
		if($size>5000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date rsync-events[$pid]: $text\n");
		@fclose($f);	
		}
		
		
function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){events("Unable to open \"$path\"",__FILE__);return array();}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		if(preg_match("#\.sql$#",$file)){
			$array[$file]=$file;
			continue;
			}
		
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
	}
	
function DirList_queue($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){writelogs("Unable to open \"$path\"",__FUNCTION__,__FILE__,__LINE__);return array();}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		if(preg_match("#\.queue$#",$file)){
			$array[$file]=$file;
			continue;
			}
		
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
	}	

	
function _get_computers_to_backup(){
	$ldap=new clladp();
	$filters=array("uid");
    $dr=@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",
    "(&(objectClass=ArticaComputerInfos)(EnableBackupAccount=1))",$filters);	
	if($dr){
		$results = ldap_get_entries($ldap->ldap_connection,$dr);
		
		for($i=0;$i<$results["count"];$i++){
			$res[$results[$i]["uid"][0]]=$results[$i]["uid"][0];
		}
	}
		return $res;

}


function ScheduleComputers(){
	$users=new usersMenus();
	DeleteCronFilesBackupComputer();
	$nice=$users->EXEC_NICE;
	$array=_get_computers_to_backup();
	if(!is_array($array)){events("No computers scheduled...");return false;}
	events(count($array)." schedule computers");
	
	
	while (list ($num, $computer) = each ($array) ){
		$comp=new computers($computer);
		$ini=new Bs_IniHandler();
		$ini->loadString($comp->ComputerCryptedInfos);
		if($ini->_params["BACKUP_PROTO"]["enable_smb"]<>1){continue;}
		$username=$ini->_params["ACCOUNT"]["USERNAME"];
		$password=$ini->_params["ACCOUNT"]["PASSWORD"];
		
		 if($comp->ComputerIP<>null){
			if($comp->ComputerIP<>"0.0.0.0"){
				$computer_ip=$comp->ComputerIP;
			}
		}
		
		if($computer_ip==null){$computer_ip=$comp->ComputerRealName;}
		
		$paths=getFoldersList($ini);
		events("schedule $computer ". count($paths). " paths to backup");
		if(count($paths)==0){continue;}
		while (list ($index, $folder) = each ($paths) ){
				if(preg_match("#^(.+?)\/#",$folder,$re)){
					$first_folder=$re[1];
					$folder=str_replace("$first_folder/","",$folder);
				}else{
					$first_folder=$folder;
					$folder=" ";
				}
				$first_folder=str_replace('$','\$',$first_folder);
				$folder=str_replace('$','\$',$folder);
				$schedule=$ini->_params["SCHEDULE"]["cron"];
				if(trim($schedule)==null){continue;}
				
				$cmd="$schedule root $nice/usr/share/artica-postfix/bin/artica-backup --incremental-computer $computer_ip \"$username\" \"$password\" \"$first_folder\" \"$folder\" >/dev/null 2>&1\n";
				$cron_file="/etc/cron.d/artica-".md5($cmd)."-bcmp";
				$rei=@file_put_contents($cron_file,$cmd);
				if(!$rei){events("Failed writing $cron_file");}
				events("schedule $schedule smb://$computer_ip/{$first_folder} ($folder) to backup scheduled for $cron_file");
				
				}
		
			
		
		
		
	}
	
	
	
	
}

function DeleteCronFilesBackupComputer(){
	$path="/etc/cron.d";
	$dir_handle = @opendir($path);
	if(!$dir_handle){write_syslog("Unable to open \"$path\"",__FILE__);return array();}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		if(preg_match("#artica.+?-bcmp$#",$file)){
			events("Deleting cron job $path/$file");
			@unlink("$path/$file");
			continue;
			}
		
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);	
	
}

function getFoldersList($ini){
		while (list ($num, $line) = each ($ini->_params)){
			if(preg_match("#share:(.+)#",$num)){$path[]=$line["path"];}
		}	
	return $path;
}


?>