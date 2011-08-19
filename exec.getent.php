<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
getent();
die();

function getent(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
	$pidTime="/etc/artica-postfix/pids/".basename(__FILE__).".time";
	$oldpid=$unix->get_pid_from_file($pidfile);
	if($unix->process_exists($oldpid,basename(__FILE__))){
		writelogs("Process $oldpid already exists",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if(system_is_overloaded(basename(__FILE__))){
		writelogs("Overloaded system, aborting",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	@file_put_contents($pidfile, getmypid());
	
	if(!$GLOBALS["FORCE"]){if($unix->file_time_min($pidTime)<120){return;}}
	@unlink($pidTime);
	@file_put_contents($pidTime, time());
	
	
	
	$getent=$unix->find_program("getent");
	exec("$getent passwd 2>&1",$results);
	$prefix="INSERT IGNORE INTO getent_users (uid) VALUES ";
	$q=new mysql();
	$q->QUERY_SQL("TRUNCATE TABLE `getent_users`","artica_backup");
	
	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?):.+?:#", $ligne,$re)){
			$re[1]=addslashes(utf8_encode($re[1]));
			$sql[]="('{$re[1]}')";
			if(count($sql)>500){
				$sqlfinal=$prefix.@implode(",", $sql);
				$q->QUERY_SQL($sqlfinal,"artica_backup");
				if(!$q->ok){echo $q->mysql_error."\n";}
				$sql=array();
			}
		}
	}
	
if(count($sql)>1){
	$sqlfinal=$prefix.@implode(",", $sql);
	$q->QUERY_SQL($sqlfinal,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n";}
	$sql=array();
	}	
	
	
exec("$getent group 2>&1",$results);
	$prefix="INSERT IGNORE INTO getent_groups (`group`) VALUES ";
	$q=new mysql();
	$q->QUERY_SQL("TRUNCATE TABLE `getent_groups`","artica_backup");
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?):#", $ligne,$re)){
			$re[1]=addslashes(utf8_encode($re[1]));
			$sql[]="('{$re[1]}')";
			if(count($sql)>500){
				$sqlfinal=$prefix.@implode(",", $sql);
				$q->QUERY_SQL($sqlfinal,"artica_backup");
				if(!$q->ok){echo $q->mysql_error."\n";}
				$sql=array();
			}
		}
	}
	
if(count($sql)>1){
	$sqlfinal=$prefix.@implode(",", $sql);
	$q->QUERY_SQL($sqlfinal,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n";}
	$sql=array();
	}	
	
	
}

