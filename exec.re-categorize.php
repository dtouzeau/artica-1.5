<?php
$GLOBALS["BYPASS"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.graphs.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--old#",implode(" ",$argv))){$GLOBALS["OLD"]=true;}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
}
if($GLOBALS["VERBOSE"]){ini_set('display_errors', 1);	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}

	if(!is_dir("/etc/artica-postfix/pids")){@mkdir("/etc/artica-postfix/pids",666,true);}
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){events("Already process exists $oldpid aborting");die();}
	$mypid=getmypid();
	@file_put_contents($pidfile,$mypid);
	
	
	$q=new mysql_squid_builder();
	
	$sql="SELECT * FROM categorize_changes";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){writelogs("Fatal Error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);die();}
	if(mysql_num_rows($results)==0){echo"No changes\n";die();}	
	$table_hours=$q->LIST_TABLES_HOURS();
	$table_days=$q->LIST_TABLES_DAYS();
	
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(isset($ALREADY[$ligne["sitename"]])){
				$q->QUERY_SQL("DELETE FROM categorize_changes WHERE `zmd5`='{$ligne["zmd5"]}'");
				continue;
			}
			$website=$ligne["sitename"];
			$categories=$q->GET_CATEGORIES($website,true);
			$ALREADY[$ligne["sitename"]]=true;
			reset($table_hours);
			reset($table_days);
			$categories=addslashes($categories);
			$t=time();
			while (list ($num, $table) = each ($table_hours) ){
				if($GLOBALS["VERBOSE"]){echo "Update $table\n";}
				$q->QUERY_SQL("UPDATE $table SET category='$categories' WHERE sitename='$website'");
				if(!$q->ok){writelogs($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);}
			}
			while (list ($num, $table) = each ($table_days) ){
				if($GLOBALS["VERBOSE"]){echo "Update $table\n";}
				$q->QUERY_SQL("UPDATE $table SET category='$categories' WHERE sitename='$website'");
				if(!$q->ok){writelogs($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);}
			}			
			
			$took=$unix->distanceOfTimeInWords($t,time());
			$q->QUERY_SQL("DELETE FROM categorize_changes WHERE `zmd5`='{$ligne["zmd5"]}'");
			writelogs_squid("$website/$categories has been re-categorized in ". count($table_days)." days tables and ". count($table_hours)." hours tables ($took)" , __FUNCTION__, __FILE__,__LINE__,"categorize");
		}	
	
	
	echo "Finish...\n";
	
	
	

function events($text){
		if($GLOBALS["VERBOSE"]){echo $text."\n";}
		$common="/var/log/artica-postfix/squid.stats.log";
		$size=@filesize($common);
		if($size>100000){@unlink($common);}
		$pid=getmypid();
		$date=date("Y-m-d H:i:s");
		$GLOBALS["CLASS_UNIX"]->events(basename(__FILE__)."$date $text");
		$h = @fopen($common, 'a');
		$sline="[$pid] $text";
		$line="$date [$pid] $text\n";
		@fwrite($h,$line);
		@fclose($h);
}