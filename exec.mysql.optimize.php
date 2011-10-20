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


if($argv[1]=="--cron"){set_cron();die();}
if($argv[1]=="--optimize"){optimize();die();}



function set_cron(){
		$targetfile="/etc/cron.d/MysqlOptimize";
		@unlink($targetfile);
		$sock=new sockets();
		$unix=new unix();
		$EnableMysqlOptimize=$sock->GET_INFO("EnableMysqlOptimize");
		if(!is_numeric($EnableMysqlOptimize)){$EnableMysqlOptimize=0;}
		if($GLOBALS["VERBOSE"]){echo "EnableMysqlOptimize = $EnableMysqlOptimize\n";}
		if($EnableMysqlOptimize==0){return;}
		$MysqlOptimizeSchedule=$sock->GET_INFO("MysqlOptimizeSchedule");
		if($GLOBALS["VERBOSE"]){echo "MysqlOptimizeSchedule = $MysqlOptimizeSchedule\n";}
		$php5=$unix->LOCATE_PHP5_BIN();
 		
 		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="MAILTO=\"\"";
		$f[]="$MysqlOptimizeSchedule  root $php5 ".__FILE__." --optimize >/dev/null 2>&1";
		$f[]="";	
		if($GLOBALS["VERBOSE"]){echo " -> $targetfile\n";}
		@file_put_contents($targetfile,implode("\n",$f));
		if(!is_file($targetfile)){if($GLOBALS["VERBOSE"]){echo " -> $targetfile No such file\n";}}
		
		$chmod=$unix->find_program("chmod");
		shell_exec("$chmod 640 $targetfile");
		unset($f);	
}

function optimize(){
		$sock=new sockets();
		$unix=new unix();
		$q=new mysql();
		$basename=basename(__FILE__);
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".MAIN.pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid,$basename)){mysql_admin_events("Already running pid $pid, aborting",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;			
		
		
		if($GLOBALS["VERBOSE"]){echo "Start ok ->CheckTables() \n";}
		$q->BuildTables();	
		$EnableMysqlOptimize=$sock->GET_INFO("EnableMysqlOptimize");
		if(!is_numeric($EnableMysqlOptimize)){$EnableMysqlOptimize=0;}	
		if($GLOBALS["VERBOSE"]){echo "EnableMysqlOptimize= $EnableMysqlOptimize \n";}
		if($EnableMysqlOptimize==0){return;}
		$t1=time();
		$ARRAY=unserialize(base64_decode($sock->GET_INFO("MysqlOptimizeDBS")));
		if($GLOBALS["VERBOSE"]){echo "MysqlOptimizeDBS= ".count($ARRAY)." \n";}
		
		mysql_admin_events("Starting optimize ". count($ARRAY)." databases ",__FUNCTION__,__FILE__,__LINE__,"defrag");
		$c=0;
		
		while (list ($database, $enabled) = each ($ARRAY) ){
			if(!is_numeric($enabled)){continue;}
			
			if($enabled==1){
				$c++;
				optimize_tables($database);}
			
		}
	
		$time=$unix->distanceOfTimeInWords($t1,time(),true);
		mysql_admin_events("$c Database(s) checked $time",__FUNCTION__,__FILE__,__LINE__,"defrag");	
}

function optimize_tables($database){
	$q=new mysql();
	$unix=new unix();
	mysql_admin_events("Starting optimize tables in database $database",__FUNCTION__,__FILE__,__LINE__,"defrag");
	$sql="SHOW TABLE STATUS FROM `$database`";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$database);
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$TableName=$ligne["Name"];
		$Data_free=$ligne["Data_free"];
		if(!is_numeric($Data_free)){continue;}
		if($Data_free==0){continue;}
		$t1=time();
		$Data_free=FormatBytes($Data_free/1024);
		$Data_free=str_replace("&nbsp;", " ", $Data_free);
		mysql_admin_events("Table $database/`$TableName` need to be optimized ($Data_free Free)",__FUNCTION__,__FILE__,__LINE__,"defrag");	
		$q->QUERY_SQL("OPTIMIZE TABLE `$TableName`",$database);
		if(!$q->ok){$unix->mysql_admin_events("$database/$TableName Error $q->mysql_error",__FUNCTION__,__FILE__,__LINE__,"defrag");	continue;}
		$time=$unix->distanceOfTimeInWords($t1,time(),true);
		mysql_admin_events("Table $database/`$TableName` optimized $time",__FUNCTION__,__FILE__,__LINE__,"defrag");
	}
}
