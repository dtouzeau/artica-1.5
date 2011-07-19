<?php

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql-server.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$unix=new unix();
$unix->events("Executing ".@implode(" ",$argv));


if($argv[1]=='--execute'){execute_sql($argv[2],$argv[3]);die();}
if($argv[1]=='--database-exists'){execute_database_exists($argv[2]);die();}
if($argv[1]=='--table-exists'){execute_table_exists($argv[2],$argv[3]);die();}
if($argv[1]=='--rownum'){execute_rownum($argv[2],$argv[3]);die();}
if($argv[1]=='--GetAsSQLText'){GetAsSQLText($argv[2]);die();}
if($argv[1]=='--backup'){Backup($argv[2]);die();}
if($argv[1]=='--checks'){checks();die();}
if($argv[1]=='--maintenance'){maintenance();die();}


if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if($argv[1]=='--tables'){$mysql=new mysql();$mysql->BuildTables();die();}
if($argv[1]=='--imapsync'){rebuild_imapsync();die();}
if($argv[1]=='--rebuild-zarafa'){rebuild_zarafa();die();}
if($argv[1]=='--squid-events-purge'){squid_events_purge();die();}
if($argv[1]=='--mysqlcheck'){mysqlcheck($argv[2],$argv[3]);die();}





$q=new mysqlserver();

$unix=new unix();
$mem=$unix->TOTAL_MEMORY_MB();
echo "\n";
echo "Starting......: Mysql my.cnf........: Total memory {$mem}MB\n";

if($mem<550){
	echo "Starting......:Mysql my.cnf........: SWITCH TO LOWER CONFIG.\n";
	$datas=$q->Mysql_low_config();
	if($mem<390){
		echo "Starting......:Mysql my.cnf........: SWITCH TO VERY LOWER CONFIG.\n";
		$datas=$q->Mysql_verlow_config();
	}
}else{
	$datas=$q->BuildConf();
}

if(!is_file($argv[1])){echo "Starting......:Mysql my.cnf........: unable to stat {$argv[1]}\n";die();}

@file_put_contents($argv[1],$datas);
echo "Starting......: Mysql my.cnf........:Updating \"{$argv[1]}\" success ". strlen($datas)." bytes\n";

function checks(){
	$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);
	$q=new mysql();
	$q->BuildTables();	
}

function rebuild_imapsync(){
	$q=new mysql();
	writelogs("DELETE imapsync table...",__FUNCTION__,__FILE__,__LINE__);
	$sql="DROP TABLE `imapsync`";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql:: $q->mysql_error\n";}
	writelogs("Rebuild tables",__FUNCTION__,__FILE__,__LINE__);
	$q->BuildTables();
	}
	
function rebuild_zarafa(){
	$q=new mysql();
	$q->DELETE_DATABASE("zarafa");
	shell_exec("/etc/init.d/artica-postfix restart zarafa");
	}
	
function execute_sql($filename,$database){
	$q=new mysql();
	$q->QUERY_SQL(@file_get_contents($filename),$database);
	if(!$q->ok){echo "ERROR: $q->mysql_error";}
	
}
function execute_database_exists($database){
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($database)){echo "FALSE\n";die();}
	echo "TRUE\n";
	
}
function execute_table_exists($database,$table){
	$q=new mysql();
	if(!$q->TABLE_EXISTS($table,$database)){echo "FALSE\n";die();}
	echo "TRUE\n";
	
}
function execute_create_database($database,$table){
	$q=new mysql();
	if(!$q->TABLE_EXISTS($table,$database)){echo "FALSE\n";die();}
	echo "TRUE\n";
	
}
function execute_rownum($database,$table){
	$q=new mysql();
	$table=trim($table);
	$sql="SELECT count(*) as tcount FROM $table";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$database));
	if($ligne["tcount"]==null){echo "0\n";return;}
	echo "{$ligne["tcount"]}\n";
}
function GetAsSQLText($filename){
	$datas=@file_get_contents($filename);
	$datas=addslashes($datas);
	@file_put_contents($filename,$datas);
}

function squid_events_purge(){
	$q=new mysql();
	$t1=time();
	$sock=new sockets();
	$nice=EXEC_NICE();
	$squidMaxTableDays=$sock->GET_INFO("squidMaxTableDays");
	$squidMaxTableDaysBackup=$sock->GET_INFO("squidMaxTableDaysBackup");
	$squidMaxTableDaysBackupPath=$sock->GET_INFO("squidMaxTableDaysBackupPath");
	if($squidMaxTableDays==null){$squidMaxTableDays=730;}
	if($squidMaxTableDaysBackup==null){$squidMaxTableDaysBackup=1;}
	if($squidMaxTableDaysBackupPath==null){$squidMaxTableDaysBackupPath="/home/squid-mysql-bck";}
	

	$sql="SELECT COUNT( ID ) as tcount FROM `dansguardian_events` WHERE `zDate` < DATE_SUB( NOW( ) , INTERVAL $squidMaxTableDays DAY )";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	$events_number=$ligne["tcount"];
	if($events_number==0){return;}
	if($events_number<0){return;}
	if(!is_numeric($events_number)){return;}
	
	$unix=new unix();
	$mysqldump=$unix->find_program("mysqldump");
	$gzip_bin=$unix->find_program("gzip");
	$stat_bin=$unix->find_program("stat");
	
	if($squidMaxTableDaysBackup==1){
			
			if(!is_file($mysqldump)){
				send_email_events("PURGE: unable to stat mysqldump the backup cannot be performed",
				"task aborted, uncheck the backup feature if you want to purge without backup",
				"proxy");
				return;
			}
			
			if(strlen($squidMaxTableDaysBackupPath)==0){
				send_email_events("PURGE: backup path was not set",
				"task aborted, uncheck the backup feature if you want to purge without backup",
				"proxy");
				return;		
			}
			@mkdir($squidMaxTableDaysBackupPath,600,true);
			$targeted_path="$squidMaxTableDaysBackupPath/".date("Y-m-d").".".time().".sql";
			$dumpcmd="$nice$mysqldump -u $q->mysql_admin -p$q->mysql_password -h $q->mysql_server artica_events dansguardian_events";
			$dumpcmd=$dumpcmd." -w \"zDate < DATE_SUB( NOW( ) , INTERVAL $squidMaxTableDays DAY )\" >$targeted_path";
			
			exec($dumpcmd,$results);
			$text_results=@implode("\n",$results);
			if(!is_file("$targeted_path")){
				send_email_events("PURGE: failed dump table",
				"task aborted,$targeted_path no such file\n$text_results\n uncheck the backup feature if you want to purge without backup\n$dumpcmd",
				"proxy");
				return;
			}
			
			if(is_file($gzip_bin)){
				$targeted_path_gz=$targeted_path.".gz";
				shell_exec("$nice$gzip_bin $targeted_path -c >$targeted_path_gz 2>&1");
				if(is_file($targeted_path_gz)){
					@unlink($targeted_path);
					$targeted_path=$targeted_path_gz;
				}
			}
			
			unset($results);
			exec("$stat_bin -c %s $targeted_path",$results);
			$filesize=trim(@implode("",$results));
			$filesize=$filesize/1024;
			$filesize=FormatBytes($filesize);
			$filesize=str_replace("&nbsp;"," ",$filesize);
	}
	
	$sql="DELETE FROM `dansguardian_events` WHERE `zDate` < DATE_SUB( NOW( ) , INTERVAL $squidMaxTableDays DAY )";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	
	if(!$q->ok){
		send_email_events("PURGE: failed removing $events_number elements",
		"task aborted,unable to delete $events_number elements,\nError:$q->mysql_error\n$sql",
		"proxy");
		return;	
	}
			
	$t2=time();
	
	$distanceOfTimeInWords=distanceOfTimeInWords($t1,$t2);
	
	if($squidMaxTableDaysBackup==1){
		$backuptext="\nRemoved elements are backuped on your specified folder:$squidMaxTableDaysBackupPath\nBackuped datas file:$targeted_path ($filesize)";
	}
	
	send_email_events("PURGE: success removing $events_number elements",
	"task successfully executed.\nExecution time:$distanceOfTimeInWords\nBackuped datas:$targeted_path",
	"proxy");	
	
	
}

function Backup($table){
	$q=new mysql();
	$q->BackupTable($table,"artica_backup");
	
}

function mysqlcheck($db,$table){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){
		echo "Process already exists pid $oldpid\n";
		return;
	}
	
	$time1=time();
	$mysqlcheck=$unix->find_program("mysqlcheck"); 
	$q=new mysql();
	$cmd="$mysqlcheck -r $db $table -u $q->mysql_admin -p$q->mysql_password 2>&1";
	exec($cmd,$results);
	$time_duration=distanceOfTimeInWords($time1,time());	
	$unix->send_email_events("mysqlcheck results on $db/$table","$time_duration\n".@implode("\n",$results),"system");
}


function maintenance(){
	return null;
	$unix=new unix();
	$time=$unix->file_time_min("/etc/artica-postfix/mysql.optimize.time");
	$time1=time();
	$myisamchk=$unix->find_program("myisamchk");
	$mysqlcheck=$unix->find_program("mysqlcheck"); 
	
	if(!$GLOBALS["VERBOSE"]){
		if($time<1440){
		$unix->events(__FILE__."::".__FUNCTION__." {$time}Mn wait 1440Mn, aborting");	
		return;
		}
	}
	
	
	$mysqlcheck_logs="";
	@unlink("/etc/artica-postfix/mysql.optimize.time");
	@file_put_contents("/etc/artica-postfix/mysql.optimize.time","#");
	
	
	if(is_file($mysqlcheck)){
		exec("$mysqlcheck -A -1 2>&1",$mysqlcheck_array);
		$mysqlcheck_logs=$mysqlcheck_logs."\n".@implode("\n",$mysqlcheck_array);
		unset($mysqlcheck_array);
	}
	
	$q=new mysql();
	$sql="SHOW TABLES";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$table=$ligne["Tables_in_artica_backup"];
		$tt=time();
		if(is_file($mysqlcheck)){
			exec("$mysqlcheck -r artica_backup  $table 2>&1",$mysqlcheck_array);
			$mysqlcheck_logs=$mysqlcheck_logs."\n".@implode("\n",$mysqlcheck_array);
			unset($mysqlcheck_array);
		}		
			
		
		echo $table."\n";
		if(is_file($myisamchk)){
			shell_exec("$myisamchk -r --safe-recover --force /var/lib/mysql/artica_backup/$table");
		}else{
			$q->REPAIR_TABLE("artica_backup",$table);
		}
		
		$q->QUERY_SQL("OPTIMIZE table $table","artica_backup");
		$time_duration=distanceOfTimeInWords($tt,time());	
		$p[]="artica_backup/$table $time_duration";
		
	}

	
	$sql="SHOW TABLES";
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$table=$ligne["Tables_in_artica_events"];
		$tt=time();
		echo "Repair & optimize $table\n";
		
		if(is_file($mysqlcheck)){
			exec("$mysqlcheck -r artica_events $table 2>&1",$mysqlcheck_array);
			$mysqlcheck_logs=$mysqlcheck_logs."\n".@implode("\n",$mysqlcheck_array);
			unset($mysqlcheck_array);
		}		
		
		if(is_file($myisamchk)){
			shell_exec("$myisamchk -r --safe-recover --force /var/lib/mysql/artica_events/$table");
		}else{
			$q->REPAIR_TABLE("artica_events",$table);
		}
		
		$q->QUERY_SQL("OPTIMIZE table $table","artica_events");
		$time_duration=distanceOfTimeInWords($tt,time());	
		$p[]="artica_events/$table $time_duration";
			
		
	}

	$t2=time();
	$time_duration=distanceOfTimeInWords($time1,$t2);	
	send_email_events("Maintenance on databases artica_backup & artica_events done $time_duration",
	"Operations has be proceed on \n".@implode("\n",$p)."\nmysqlchecks results:\n$mysqlcheck_logs","system");
}

?>