<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.dansguardian.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
cpulimit();
$_GET["LOGFILE"]="/var/log/artica-postfix/dansguardian-logger.debug";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--simulate#",implode(" ",$argv))){$GLOBALS["SIMULATE"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('display_errors', 1);	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if($argv[1]=="--import"){include_tpl_file($argv[2],$argv[3]);die();}
if($argv[1]=="--sites-infos"){ParseSitesInfos();die();}

$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
$unix=new unix();
$GLOBALS["CLASS_UNIX"]=$unix;
if($unix->process_exists($oldpid)){
	$time=$unix->PROCCESS_TIME_MIN($oldpid);
	events(basename(__FILE__).": Already executed $oldpid (since {$time}Mn).. aborting the process (line: ".__LINE__.")");
	events_tail("Already executed $oldpid (since {$time}Mn). aborting the process (line: ".__LINE__.")");
	die();
	
}
$pid=getmypid();
$t1=time();
file_put_contents($pidfile,$pid);
events_tail(basename(__FILE__).": running $pid");



if(migrate_single_db()){
	events_tail(basename(__FILE__).": -> migrate_month_db()");
	migrate_month_db();
}
migrate_categories();

function migrate_categories(){
$q=new mysql();	
if(!$q->TABLE_EXISTS("dansguardian_community_categories", "artica_backup")){
		events_tail("dansguardian_community_categories is not exists, should be OK");
		return true;
	}

	$sql="SELECT category FROM dansguardian_community_categories GROUP BY category";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$mysql_num_rows=mysql_num_rows($results);
	events_tail("$mysql_num_rows categories");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		migrate_categories_single($ligne["category"]);
		
	}

	
}


function migrate_categories_single($category){
	$categoryTB=str_replace('/',"_",$category);
	$categoryTB=str_replace('-',"_",$categoryTB);
	
	$qA=new mysql_squid_builder();
	$q=new mysql();
	
	$qA->CreateCategoryTable($categoryTB);
	if(!$qA->TABLE_EXISTS("category_$categoryTB")){
		events_tail("Unable to create category_$categoryTB table");
		return false;
	}
	$sql="SELECT * FROM dansguardian_community_categories WHERE category='$category'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("Fatal Error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
	$mysql_num_rows=mysql_num_rows($results);
	if($GLOBALS["VERBOSE"]){echo $sql." => $mysql_num_rows\n";}
	
	$prefix="INSERT IGNORE INTO category_$categoryTB(`zmd5`,`zDate`,`category`,`pattern`,`enabled`,`uuid`) VALUES ";
		$c=0;
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$c++;
			$f[]="('{$ligne["zmd5"]}','{$ligne["zDate"]}','{$ligne["category"]}','{$ligne["pattern"]}','{$ligne["enabled"]}','{$ligne["uuid"]}')";
			if(count($f)>500){
				events_tail("Injecting $c rows");
				$qA->QUERY_SQL("$prefix".@implode(",", $f));
				$f=array();
				if(!$q->ok){writelogs("Fatal Error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
			}
		}

		
		if(count($f)>0){
			$c=$c+count($f);
			events_tail("Injecting $c rows");
			$qA->QUERY_SQL("$prefix".@implode(",", $f));
			if(!$q->ok){writelogs("Fatal Error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
		}
		
	events_tail("removing category $category in original table");
	$sql="DELETE FROM dansguardian_community_categories WHERE category='$category'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("Fatal Error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
	return true;
}


events_tail(basename(__FILE__).": FINISH $pid");

function events_tail($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/proxy-injector.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$GLOBALS["CLASS_UNIX"]->events(basename(__FILE__)." $date $text");
		if($GLOBALS["VERBOSE"]){echo "$date $text\n";}
		@fwrite($f, "$pid ".basename(__FILE__)." $date $text\n");
		@fclose($f);	
		}
		
function migrate_month_db(){
	$q=new mysql();
	
	$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'artica_events' AND table_name LIKE 'dansguardian_events_%'";
	$results=$q->QUERY_SQL($sql);
		if(!$q->ok){writelogs("Fatal Error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
		$mysql_num_rows=mysql_num_rows($results);
		if($GLOBALS["VERBOSE"]){echo $sql." => $mysql_num_rows\n";}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			events_tail(" = > {$ligne["c"]}");
			if(!migrate_single_db($ligne["c"])){
				events_tail("{$ligne["c"]} Failed");
				return;
			}
			
		}	
}	
		
function migrate_single_db($tablename=null){
	$q=new mysql();
	if($tablename==null){$tablename="dansguardian_events";}
	if(!$q->TABLE_EXISTS("$tablename", "artica_events")){
		events_tail("$tablename is not exists, should be OK");
		return true;
	}
	
	events_tail("Checking listed days...");
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday,DATE_FORMAT(zDate,'%Y%m%d') as newtable FROM $tablename GROUP BY tday,newtable";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	$mysql_num_rows=mysql_num_rows($results);
	
	if($mysql_num_rows==0){
		events_tail("Delete table $tablename");
		$sql="DROP TABLE $tablename";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){echo $q->mysql_error."\n";}
		return true;
	}
	events_tail("Migrate '$mysql_num_rows' days");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$day=$ligne["tday"];
		$newtable=$ligne["newtable"];
		if(!migrate_single_db_day($day,$newtable,$tablename)){return false;}
	}
	
	return true;
}

function migrate_single_db_day($day,$newtable,$oldtable=null){
	if($oldtable==null){$oldtable="dansguardian_events";}
	events_tail("Migrate $day day data to dansguardian_events_$newtable");
	$table="dansguardian_events_$newtable";
	$qA=new mysql_squid_builder();
	$qA->CheckTables($table);
	$sql="SELECT * FROM $oldtable WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day'";
	$q=new mysql();
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	$countnom=mysql_num_rows($results);
	events_tail("Migrate $day datas to dansguardian_events_$newtable ($countnom)");
	if(!$q->ok){events_tail("Mysql error $q->mysql_error");return; }
	
	$prefixsql="INSERT IGNORE INTO $table (`sitename`,`uri`,`TYPE`,`REASON`,`CLIENT`,`zDate`,`zMD5`,`remote_ip`,`country`,`QuerySize`,`uid`,`cached`) VALUES ";
	$c=0;
	$f=array();
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$c++;
		if(!isset($ligne["cached"])){$ligne["cached"]=0;}
		$ligne["country"]=addslashes($ligne["country"]);
		$ligne["sitename"]=addslashes($ligne["sitename"]);
		$ligne["uri"]=addslashes($ligne["uri"]);
		$ligne["TYPE"]=addslashes($ligne["TYPE"]);
		$ligne["REASON"]=addslashes($ligne["REASON"]);
		$ligne["CLIENT"]=addslashes($ligne["CLIENT"]);
		$f[]="('{$ligne["sitename"]}','{$ligne["uri"]}','{$ligne["TYPE"]}','{$ligne["REASON"]}','{$ligne["CLIENT"]}','{$ligne["zDate"]}',
		'{$ligne["zMD5"]}','{$ligne["remote_ip"]}','{$ligne["country"]}','{$ligne["QuerySize"]}','{$ligne["uid"]}','{$ligne["cached"]}')";
		
		if(count($f)>500){
			events_tail("Injecting $c/$countnom rows...");
			$qA->QUERY_SQL("$prefixsql".@implode(",", $f),"squidlogs");
			if(!$qA->ok){echo $qA->mysql_error."\n";return;}
			$f=array();
		}
	}	
	
	if(count($f)>0){
		events_tail("Injecting ".count($f)."/$countnom rows...");
		$qA->QUERY_SQL("$prefixsql".@implode(",", $f),"squidlogs");
	}	
	events_tail("Removing $day datas in old table...");
	$sql="DELETE FROM $oldtable WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day'";
	$q->QUERY_SQL($sql,"artica_events");
	events_tail("$day = $c elements migrated to new dansguardian_events_$newtable");
	return true;
}

