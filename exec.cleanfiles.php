<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	
	
$unix=new unix();
$pidpath="/etc/artica-postfix/pids.3/".basename(__FILE__)."pid";
if($unix->process_exists(@file_get_contents($pidpath))){
	writelogs(basename(__FILE__).":Already executed.. PID: ". @file_get_contents($pidpath). " aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

@file_put_contents($pidpath,getmypid());	
	

	CleanTempDirs();
	CleanArticaUpdateLogs();
	ParseMysqlEventsQueue();
	die();
	
	
	
function CleanTempDirs(){
	$unix=new unix();
	$dirs=$unix->dirdir("/tmp");
	if(!is_array($dirs)){return null;}
	while (list ($num, $ligne) = each ($dirs) ){
		if(trim($num)==null){continue;}
		$time=$unix->file_time_min($num);
		if($time<380){continue;}
		if(is_dir($num)){
			shell_exec("/bin/rm -rf \"$num\"");
		}
		
	}
	
}


function CleanArticaUpdateLogs(){
	foreach (glob("/var/log/artica-postfix/artica-update-*.debug") as $filename) {
		$file_time_min=file_time_min($filename);
		if(file_time_min($filename)>5752){@unlink($filename);}
		}

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