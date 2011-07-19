<?php
$GLOBALS["VERBOSE"]=false;
$GLOBALS["DEBUG"]=false;;
$GLOBALS["FORCE"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.sockets.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.autofs.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');





if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=="--build"){build();exit;}
if($argv[1]=="--fsck"){fsck();exit;}

function build(){
	@unlink("/etc/greyhole.conf");
	if(!checkdb()){
		echo "Starting......: Checking database failed\n";
		return;
	
	}
	@mkdir("/var/spool/greyhole",777,true);
	shell_exec("/bin/chmod 777 /var/spool/greyhole >/dev/null 2>&1");
	$sock=new sockets();
	$EnableGreyHoleDebug=$sock->GET_INFO("EnableGreyHoleDebug");
	if(!is_numeric($EnableGreyHoleDebug)){$EnableGreyHoleDebug=0;}
	
	if($EnableGreyHoleDebug==1){$DEBUG="DEBUG";}else{$DEBUG="INFO";}
	echo "Starting......: greyhole verbosity $DEBUG\n";
	$unix=new unix();
	$q=new mysql();
	$f[]="db_engine = mysql";
	$f[]="db_host = $q->mysql_server";
	$f[]="db_user = $q->mysql_admin";
	$f[]="db_pass = $q->mysql_password";
	$f[]="db_name = greyhole";
	$f[]="email_to = root";
	$f[]="greyhole_log_file = /var/log/greyhole.log";
	$f[]="log_level = $DEBUG";
	$f[]="log_memory_usage = no";
	$f[]="df_cache_time = 15";
	$f[]="balance_modified_files = no";
	$f[]="check_for_open_files = yes";
	$f[]="max_queued_tasks = 100000";
	$f[]="dir_selection_algorithm = most_available_space";
	
	
	$touch=$unix->find_program("touch");
	$sql="SELECT * FROM greyhole_spools ORDER BY free_g DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$autofs=new autofs();
	$hash=$autofs->automounts_Browse();
		if(!$q->ok){
			echo $q->mysql_error;
			@file_put_contents("/etc/greyhole.conf","#");
		}	
		
	$count=mysql_num_rows($results);
	if($count==0){echo "Starting......: greyhole no pool defined\n";return;}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array=$autofs->hash_by_dn[$ligne["dn"]];
		$FOLDER=$array["FOLDER"];
		$size=$ligne["free_g"];
		echo "Starting......: greyhole storage pool directory $FOLDER  min_free: {$size}gb\n";
		$f[]="storage_pool_directory = /automounts/$FOLDER, min_free: {$size}gb";
		if(!is_file("/automounts/$FOLDER/.greyhole_uses_this")){
			@file_put_contents("/automounts/$FOLDER/.greyhole_uses_this","#");
		}
	}
	
	
	$sql="SELECT * FROM `greyhole_dirs` WHERE num_copies > 0";
	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		echo "Starting......: greyhole {$ligne["shared_dir"]} with {$ligne["num_copies"]} num_copies\n";
		$f[]="num_copies[{$ligne["shared_dir"]}] = {$ligne["num_copies"]}";
	}
	
	$unix=new unix();
	$modprobe=$unix->find_program("modprobe");
	shell_exec("$modprobe cifs >/dev/null 2>&1");
	@file_put_contents("/proc/fs/cifs/OplockEnabled","0");
	@file_put_contents("/etc/greyhole.conf",@implode("\n",$f));
	
}

function fsck(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET("/usr/bin/greyhole --fsck --dont-walk-graveyard");
	
}

function checkdb(){
	$createtable=false;
	$q=new mysql();
	if(!$q->DATABASE_EXISTS("greyhole")){
		$createtable=true;
		echo "Starting......: greyhole creating mysql database \"greyhole\"\n";
		$q->CREATE_DATABASE("greyhole");
		if(!$q->ok){
			echo "Starting......: greyhole $q->mysql_error\n";
			return false;
		}
	}
	
if(!$q->TABLE_EXISTS("settings","greyhole")){	
	echo "Starting......: greyhole create table \"settings\"\n";
		$sql="CREATE TABLE `settings` (
		`name` TINYTEXT NOT NULL,
		`value` TEXT NOT NULL,
		PRIMARY KEY ( `name`(255) )
		) ENGINE = MYISAM;";

	$q->QUERY_SQL($sql,"greyhole");

	$sql="INSERT INTO `settings` (`name`, `value`) VALUES ('last_read_log_smbd_line', '0');";
	$q->QUERY_SQL($sql,"greyhole");
	$sql="INSERT INTO `settings` (`name`, `value`) VALUES ('last_OOS_notification', '0');";
	$q->QUERY_SQL($sql,"greyhole");
}
	
	
if(!$q->TABLE_EXISTS("tasks","greyhole")){	
	echo "Starting......: greyhole create table \"tasks\"\n";
		$sql="CREATE TABLE `tasks` (
		`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`action` VARCHAR( 10 ) NOT NULL,
		`share` TINYTEXT NOT NULL,
		`full_path` TINYTEXT NULL,
		`additional_info` TINYTEXT NULL,
		`complete` ENUM( 'yes',  'no', 'frozen', 'thawed', 'idle') NOT NULL,
		`event_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE = MYISAM;";
		$q->QUERY_SQL($sql,"greyhole");
		$sql="ALTER TABLE `tasks` ADD INDEX `find_next_task` ( `complete` , `share` (64) , `id` );";
		$q->QUERY_SQL($sql,"greyhole");
}
	
if(!$q->TABLE_EXISTS("tasks_completed","greyhole")){	
	echo "Starting......: greyhole create table \"tasks_completed\"\n";
		$sql="CREATE TABLE `tasks_completed` (
		`id` BIGINT UNSIGNED NOT NULL,
		`action` VARCHAR( 10 ) NOT NULL,
		`share` TINYTEXT NOT NULL,
		`full_path` TINYTEXT NULL,
		`additional_info` TINYTEXT NULL,
		`complete` ENUM( 'yes',  'no' ) NOT NULL,
		`event_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE = MYISAM;";
		$q->QUERY_SQL($sql,"greyhole");	
		if(!$q->ok){echo "Starting......: greyhole $q->mysql_error\n";} 
	}
	
	echo "Starting......: greyhole checking database and table done\n";
	return true;
}


?>
