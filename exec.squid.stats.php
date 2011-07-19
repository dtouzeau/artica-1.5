<?php
$GLOBALS["BYPASS"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.graphs.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
}

$unix=new unix();
$GLOBALS["CLASS_UNIX"]=$unix;
events("Executed " .@implode(" ",$argv));

if($argv[1]=='--hours'){clients_hours();die();}
if($argv[1]=='--hours-graphs'){today_hours_index();die();}
if($argv[1]=='--clients'){clients_days($argv[2]);die();}
if($argv[1]=='--status'){status();die();}
if($argv[1]=='--fill-categories'){FillCategories();die();}
if($argv[1]=='--hits-clients'){tablehitsclients();die();}
if($argv[1]=='--squid_events_sites'){squid_events_sites();die();}
if($argv[1]=='--parse-days'){ParseDays();die();}




if($argv[1]=='--tables'){
	$q=new mysql();
	$q->CheckTablesSquid();
	die();
}

if($argv[1]=='--graphs'){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".1.pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){die();}
	$mypid=getmygid();
	@file_put_contents($pidfile,$mypid);	
	events("-> today_hits()");
	today_hits();
	events("-> today_size()");
	today_size();
	events("-> month_size()");
	month_size();
	events("-> month_hits()");
	month_hits();
	events("-> today_hours_index()");
	today_hours_index();
	events("-> DONE !");
	shell_exec("/bin/chmod -R 755 /usr/share/artica-postfix/ressources/logs/*.png");
	die();	
}

if($argv[1]=='--maintenance'){
	if(!is_dir("/etc/artica-postfix/pids")){@mkdir("/etc/artica-postfix/pids",666,true);}
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".2.pid";
	$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".2.time";
	$timefileDB="/etc/artica-postfix/pids/".basename(__FILE__).".mysql.time";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){
		events("Already process exists $oldpid aborting");
		die();
	}
	$mypid=getmygid();
	@file_put_contents($pidfile,$mypid);
	
	$time=$unix->file_time_min($timefile);
	events("Time {$time}Mn/120Mn");
	if($time<120){
		events("Need to wait 120Mn");
		die();
	}
	@unlink($timefile);
	@file_put_contents($timefile,"#");
	events("-> ParseDays()");
	ParseDays();
	events("-> today_hits()");
	today_hits();
	events("-> today_size()");
	today_size();
	events("-> month_size()");
	month_size();
	events("-> month_hits()");
	month_hits();
	
	events("-> ParseDays()");
	ParseDays();
	events("-> clients_hours()");
	clients_hours();
	events("-> today_hours_index()");
	today_hours_index();
	events("-> tablehitsclients()");
	tablehitsclients();
	events("-> FillCategories()");
	FillCategories();
	events("-> squid_events_sites()");
	squid_events_sites();
	events("-> FillCategories()");
	FillCategories();
	
	$time=$unix->file_time_min($timefileDB);
	events("Time {$time}Mn/1440Mn");
	if($time<1440){events("Need to wait 1440Mn");die();}
	@unlink($timefileDB);
	@file_put_contents($timefileDB,"#");	
	
	
	events("REPAIR TABLE / OPTIMIZE TABLE");
	$q=new mysql();
	$q->QUERY_SQL("REPAIR TABLE `dansguardian_events`","artica_events");
	$q->QUERY_SQL("OPTIMIZE TABLE `dansguardian_events`","artica_events");
}


function tablehitsclients(){
	
$sql="SELECT days, hours FROM squid_events_clients_sites ORDER BY days, hours DESC LIMIT 0 , 1";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$day_from=trim($ligne["days"]);
		$hours_from=trim($ligne["hours"]);
	}
	
	echo "tablehitsclients() FROM $day_from,$hours_from:00:00\n";
	if($day_from<>null){
		$fromsql=" AND DATE_FORMAT( zDate, '%Y-%m-%d' )>='$day_from' AND DATE_FORMAT( zDate, '%H' )>$hours_from";
	}
	
	
$day=date('Y-m-d');
$hour=date('H');	
	
$sql="SELECT COUNT( ID ) AS thits, sitename,country, CLIENT, DATE_FORMAT( zDate, '%Y-%m-%d' ) AS tday, DATE_FORMAT( zDate, '%H' ) AS thour, SUM( QuerySize ) AS tsize
FROM dansguardian_events
WHERE DATE_FORMAT( zDate, '%Y-%m-%d' )<='$day' 
AND DATE_FORMAT( zDate, '%H' )<$hour 
$fromsql
GROUP BY sitename, CLIENT,country ORDER BY UNIX_TIMESTAMP(zDate)";

echo $sql."\n";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");

	
	$prefix="INSERT IGNORE INTO squid_events_clients_sites(`zMD5`,`days`,`client`,`websites`,`hours`,`size`,`hits`,`category`,`country`) VALUES";
	$unix=new unix();
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(preg_match("#^www\.(.+?)$#i",trim($ligne["sitename"]),$re)){$ligne["sitename"]=$re[1];}
			$category=GetCategory($ligne['sitename']);
			if(trim($ligne["country"])==null){
				$country_array=GeoIP($ligne["websites"]);$country=$country_array[0];
				if($country<>null){
					$sql="UPDATE dansguardian_events SET `country`='".addslashes($country)."' WHERE sitename='{$ligne["sitename"]}'";
					$q->QUERY_SQL($sql,"artica_events");
					$ligne["country"]=$country;
				}
				
			}		
		
		$country=addslashes($ligne["country"]);
		$zmd5=md5("('{$ligne['tday']}','{$ligne['CLIENT']}','{$ligne['sitename']}','{$ligne['thour']}','{$ligne['tsize']}','{$ligne['thits']}','$category','$country'");
		$sq[]="('$zmd5','{$ligne['tday']}','{$ligne['CLIENT']}','{$ligne['sitename']}','{$ligne['thour']}','{$ligne['tsize']}','{$ligne['thits']}','$category','$country')";
		
		if(count($sq)>100){
			echo "100\n";
			$sql=$prefix.@implode(",",$sq);
			writelogs("Writing 100 (".strlen($sql)." bytes) sql queries in squid_events_clients_sites",__FUNCTION__,__FILE__,__LINE__);
			unset($sq);
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				$unix->send_email_events("Error: Web Proxy Category statistics (tablehitsclients)","Errors: 
				$sql\n$q->mysql_error\nTable squid_events_clients_sites, database artica_events
				server:$q->mysql_server:$q->mysql_port
				user:$q->mysql_admin
				","proxy");
				return;
			}	
		}
		
	}
	
	if(count($sq)>1){
		echo count($sq)."\n";
		$sql=$prefix.@implode(",",$sq);
		$q->QUERY_SQL($sql,"artica_events");
	}
	
}

function squid_events_sites(){
	$q=new mysql();
	if($GLOBALS["VERBOSE"]){echo "Empty table...\n";}
	$sql="TRUNCATE TABLE `squid_events_sites`";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="SELECT SUM(hits) as thits, SUM(size) as tsize, websites,category,country FROM squid_events_clients_sites GROUP BY websites,category,country";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
		if(!$q->ok){
				$unix=new unix();
				$unix->send_email_events("Error: Web Proxy Category statistics (squid_events_sites)","Errors: 
				$sql\n$q->mysql_error\nTable squid_events_sites, database artica_events
				server:$q->mysql_server:$q->mysql_port
				user:$q->mysql_admin
				","proxy");
				return;
			}		
	
	if($GLOBALS["VERBOSE"]){echo mysql_num_rows($results) ." rows\n";}
	
	$prefix="INSERT IGNORE INTO squid_events_sites (website,hits,size,category,country) VALUES ";
	$already=array();
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		if(!isset($already[$ligne["websites"]])){
			$country=addslashes($ligne["country"]);
			if(is_numeric($country)){$country=null;}
			
			if($country==null){
				$country_array=GeoIP($ligne["websites"]);$country=$country_array[0];
				if($country<>null){
					$sql="UPDATE squid_events_clients_sites SET `country`='".addslashes($country)."' WHERE websites='{$ligne["websites"]}'";
					$q->QUERY_SQL($sql,"artica_events");
				}
				
			}
			if($country==null){$country_array=GeoIP("www.".$ligne["websites"]);$country=$country_array[0];}
			if(trim($ligne["category"])==null){$ligne["category"]=GetCategory($ligne["websites"]);}
			if(preg_match("#^www\.(.+?)$#i",trim($ligne["websites"]),$re)){$ligne["websites"]=$re[1];}
			$country=addslashes($country);
			
			$qs[]="('{$ligne["websites"]}','{$ligne["thits"]}','{$ligne["tsize"]}','{$ligne["category"]}','$country')";
			$already[$ligne["websites"]]=true;
		}
		
		if(count($qs)>500){
			$sql=$prefix.@implode(",",$qs);
			$q->QUERY_SQL($sql,"artica_events");
			$qs=array();
		}
		
	}
	
	if(count($qs)>0){
			$sql=$prefix.@implode(",",$qs);
			$q->QUERY_SQL($sql,"artica_events");
			$qs=array();
		}	
	
}



function FillCategories(){
	$unix=new unix();
	$cachetime="/etc/artica/postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	if($cachetime>2880){
		$sql="SELECT websites FROM `squid_events_sites_day` GROUP BY websites";
	}else{ 
		$sql="SELECT websites FROM `squid_events_sites_day` WHERE LENGTH( category ) =0 GROUP BY websites";
	}
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";}
		$unix->send_email_events("Error: Web Proxy Category statistics ","Errors: $sql\n$q->mysql_error","proxy");
		return;
	}
	
	@unlink($cachetime);
	@file_put_contents($cachetime,"#");

	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$website=$ligne["websites"];
		$cat=GetCategory($website);
		if($cat<>null){
			$sql="UPDATE `squid_events_sites_day` SET `category`='$cat' WHERE `websites`='$website'";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				$unix->send_email_events("Error: Web Proxy Category statistics ","Errors: $sql\n$q->mysql_error\nTable squid_events_sites_day, database artica_events","proxy");
				return;
			}
			$sql="UPDATE `squid_events_clients_sites` SET `category`='$cat' WHERE `websites`='$website'";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				$unix->send_email_events("Error: Web Proxy Category statistics ","Errors: $sql\n$q->mysql_error\nTable squid_events_clients_sites, database artica_events","proxy");
				return;
			}			
			
			
		}
	}	
	
	
}

function GetCategory($www){
	if(preg_match("#^www\.(.+)#",$www,$re)){$www=$re[1];}
	$sql="SELECT category FROM dansguardian_community_categories WHERE pattern='$www' and enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[]=$ligne["category"];
	}
	
	if(is_array($f)){return @implode(",",$f);}
}


$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
if($oldpid<100){$oldpid=null;}
$unix=new unix();
if($unix->process_exists($oldpid)){if($GLOBALS["VERBOSE"]){echo "Already executed pid $oldpid\n";}die();}
$mypid=getmygid();
@file_put_contents($pidfile,$mypid);






function status(){
	$sock=new sockets();
	$lastdate=$sock->GET_INFO("SquidStatsDayLastDay");
	echo "Last Date parsed...............: $lastdate\n";
	$q=new mysql();
	$sql="SHOW TABLE STATUS WHERE Name LIKE 'squid%'";
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "$q->mysql_error for $sql\n";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	echo "Mysql Table....................: \"{$ligne["Name"]}\"  ({$ligne["Rows"]} Row(s)) last update on {$ligne["Update_time"]}\n";
	
	}
	
	$sql="SHOW TABLE STATUS WHERE Name LIKE 'dansguardian%'";
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "$q->mysql_error for $sql\n";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	echo "Mysql Table....................: \"{$ligne["Name"]}\"  ({$ligne["Rows"]} Row(s)) last update on {$ligne["Update_time"]}\n";
	
	}

	
	
}

function clients_hours(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){die();}
	$mypid=getmygid();
	@file_put_contents($pidfile,$mypid);

	$q=new mysql();
	$q->CheckTablesSquid();
	
	if(!$q->TABLE_EXISTS('squid_events_hours','artica_events')){
		$unix->send_email_events("Unable to create statistics for \"squid_events_hours\"","Table squid_events_hours did not exists, please contact Artica support team","proxy");
		return;
	}
	
	$today=date('Y-m-d');
	$hour=date('H');
	$sql="SELECT COUNT(uri) as tcount, SUM(QuerySize) as tsize,DATE_FORMAT(zDate,'%Y-%m-%d %H') as tdate 
	FROM dansguardian_events WHERE zDate >DATE_ADD(zDate,INTERVAL -24 HOUR) GROUP BY tdate";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";}
		$unix->send_email_events("Unable to synthetize Web Proxy hour statistics ","Errors: $sql\n$q->mysql_error","proxy");
		return;
	}

	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$md5=md5($ligne["tdate"]);
		if(preg_match("#(.+?)\s+([0-9]+)#",$ligne["tdate"],$re)){
			$day=$re[1];
			$hour=$re[2];
		}
		$sql="INSERT INTO squid_events_hours (zmd5,`day`,`hour`,`hits`,`www_size`) VALUES('$md5','$day','$hour','{$ligne["tcount"]}','{$ligne["tsize"]}');";
		
		if(clients_hours_md($md5)<>null){
			$sql="UPDATE squid_events_hours SET `hits`='{$ligne["tcount"]}', `www_size`='{$ligne["tsize"]}' WHERE `zmd5`='$md5'";
		}
		
		$q->QUERY_SQL($sql,"artica_events");
		
		
	}
}
function clients_hours_md($md5){
	$sql="SELECT zmd5 FROM squid_events_hours WHERE zmd5='$md5'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));
	return $ligne["zmd5"];
	
}

function getDays(){
$sql="SELECT DATE_FORMAT( zDate, '%Y-%m-%d' ) AS tday FROM dansguardian_events GROUP BY tday ORDER BY tday";	
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	events(__FUNCTION__." (".__LINE__."):: Found {$ligne["tday"]}");
	$days[$ligne["tday"]]=true;
	}
	
	$sql="SELECT DATE_FORMAT( days, '%Y-%m-%d' ) AS tday FROM squid_events_sites_day  GROUP BY tday ORDER BY tday";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($days[$ligne["tday"]]){
			events(__FUNCTION__." (".__LINE__."):: remove {$ligne["tday"]}");
			unset($days[$ligne["tday"]]);}
	}
	
	while (list ($key, $line) = each ($days) ){
		$t[]=$key;
	}
	$GLOBALS["DAY_TO_PARSE"]=$t[0];
}





function ParseDays(){

	$sock=new sockets();
	$q=new mysql();
	$sql="SELECT DATE_FORMAT( days, '%Y-%m-%d' ) AS tday FROM squid_events_clients_day WHERE days 
	<= DATE_SUB( NOW( ) , INTERVAL 1 DAY ) ORDER BY tday DESC LIMIT 0 , 1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";}
		return;
	}
	
	$lastday=trim($ligne["tday"]);
	events(__FUNCTION__." (".__LINE__."):: last parsed day now is $lastday");
	
	if($lastday<>null){
		$sqlday="WHERE zDate>'$lastday' AND zDate < DATE_SUB( NOW( ) , INTERVAL 1 DAY )";
	}
	
	
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday FROM dansguardian_events $sqlday GROUP BY tday order by tday";
	if($GLOBALS["VERBOSE"]){echo $sql." ". __FUNCTION__." ".__LINE__."\n";}

	
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	
	
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";}
		send_email_events("Unable to synthetize Web Proxy day statistics ","Errors: $sql\n$q->mysql_error","system");
	}
	
	$num_rows = mysql_num_rows($results);
	if($num_rows==0){
		if($GLOBALS["VERBOSE"]){echo "No datas ". __FUNCTION__." ".__LINE__."\n";}
		return;
	}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$dayToParse=$ligne["tday"];
		$daysToNotify[]=$ligne["tday"];
		events(__FUNCTION__." (".__LINE__."):: Analyze $dayToParse");
		if(!ParseSingleDay($dayToParse)){
			send_email_events("Unable to synthetize Web Proxy day statistics for the day $dayToParse","Errors: ". @implode("\n",$GLOBALS[__FILE__]["EVENTS"]),"system");
			return;
		}

	}
	
	if(count($daysToNotify)>0){
		send_email_events("Success synthetize ". count($daysToNotify)." Web Proxy day statistics","Days: \n". @implode("\n",$daysToNotify),"system");
	}

}

function ParseSingleDay($day){
	clients_days($day);
	events(__FUNCTION__." (".__LINE__."):: Parsing day $day");
	
	$sql="SELECT COUNT(sitename) as hits,sitename,SUM(QuerySize) as tsize
	FROM dansguardian_events WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY sitename ";
	
	$sqlintro="INSERT IGNORE INTO squid_events_sites_day (`days`,`websites`,`website_size`,`website_hits`) VALUES ";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		events(__FUNCTION__." (".__LINE__."):: $day failed $q->mysql_error");
		events(__FUNCTION__." (".__LINE__."):: $day failed $sql");
		return false;
	}
	
	$num_rows = mysql_num_rows($results);
	if($num_rows==0){
		if($GLOBALS["VERBOSE"]){echo "No datas ". __FUNCTION__." ".__LINE__."\n";}
		return;
	}else{
		events(__FUNCTION__." (".__LINE__."):: $num_rows rows");
	}	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$sqlPlus[]="('$day','{$ligne["sitename"]}','{$ligne["tsize"]}','{$ligne["hits"]}')";
		usleep(100000);
		
		if(count($sqlPlus)>2000){
			events(__FUNCTION__." (".__LINE__."):: $day ".count($sqlPlus)." rows");
			$sqlfin=$sqlintro.@implode("\n,",$sqlPlus);
			$q->QUERY_SQL($sqlfin,"artica_events");
			if(!$q->ok){
				
				events(__FUNCTION__." (".__LINE__."):: $day failed $q->mysql_error");
				return false;
			}
			unset($sqlPlus);
		}
	}
	
	if(count($sqlPlus)>0){
		events(__FUNCTION__." (".__LINE__."):: $day ".count($sqlPlus)." rows");
		$sqlfin=$sqlintro.@implode("\n,",$sqlPlus);
		$q->QUERY_SQL($sqlfin,"artica_events");
			if(!$q->ok){
				events(__FUNCTION__." (".__LINE__."):: $day failed $q->mysql_error");
				return false;
			}
	}else{
		events(__FUNCTION__." (".__LINE__."):: $day $sql 0 rows");
	}
	
	
	
	return true;
	
}

function clients_days($day){
	events(__FUNCTION__." (".__LINE__."):: Parsing day $day");
	
	$sql="SELECT COUNT(sitename) as hits,CLIENT,SUM(QuerySize) as tsize
	FROM dansguardian_events WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY CLIENT ";
	$sqlintro="INSERT INTO squid_events_clients_day (`days`,`CLIENT`,`size`,`hits`) VALUES ";
		
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		events(__FUNCTION__." (".__LINE__."):: $day failed $q->mysql_error");
		events(__FUNCTION__." (".__LINE__."):: $day failed $sql");
		return false;
	}	
	events(__FUNCTION__." (".__LINE__."):: $day ".mysql_num_rows($results)." rows");

	$num_rows = mysql_num_rows($results);

	if($num_rows==0){
		if($GLOBALS["VERBOSE"]){echo "No datas ". __FUNCTION__." ".__LINE__."\n";}
		return;
	}	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$sqlPlus[]="('$day','{$ligne["CLIENT"]}','{$ligne["tsize"]}','{$ligne["hits"]}')";
		usleep(90000);
		
		if(count($sqlPlus)>2000){
			events(__FUNCTION__." (".__LINE__."):: $day ".count($sqlPlus)." rows");
			$sqlfin=$sqlintro.@implode("\n,",$sqlPlus);
			$q->QUERY_SQL($sqlfin,"artica_events");
			if(!$q->ok){
				events(__FUNCTION__." (".__LINE__."):: $day failed $q->mysql_error");
				return false;
			}
			unset($sqlPlus);
		}
	}
	
	if(count($sqlPlus)>0){
		events(__FUNCTION__." (".__LINE__."):: $day ".count($sqlPlus)." rows");
		$sqlfin=$sqlintro.@implode("\n,",$sqlPlus);
		$q->QUERY_SQL($sqlfin,"artica_events");
			if(!$q->ok){
				events(__FUNCTION__." (".__LINE__."):: $day failed $q->mysql_error");
				return false;
			}
	}else{
		events(__FUNCTION__." (".__LINE__."):: $day $sql 0 rows");
	}
	
	return true;	
}




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


function today_hours_index(){
	
	
	$fileName = dirname(__FILE__)."/ressources/logs/hours-squid-hits.png";
	if(file_get_time_min($fileName)>60){
		@unlink($fileName);
		$today=date('Y-m-d');
		$g=new artica_graphs($fileName,60);
		$sql="SELECT * FROM squid_events_hours WHERE `day` = '$today' ORDER BY hour";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_events");
		
		if(!$q->ok){
			writelogs($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);
		}		
		
		$num_rows = mysql_num_rows($results);
		if($num_rows==0){
			if($GLOBALS["VERBOSE"]){echo "No datas \"$sql\" ". __FUNCTION__." ".__LINE__."\n";}
			return;
		}		
		
		

		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$size=round(($ligne["www_size"]/1024)/1000);
			if($GLOBALS["VERBOSE"]){echo "{$ligne["hour"]}:{$ligne["hits"]} ".__FUNCTION__."\n";}
			$ss[]=$size;
			$hh[] =$ligne["hour"];
			$g->xdata[] =$ligne["hour"];
			$g->ydata[]=$ligne["hits"];
		}

		$g->title='today: hits number/hour';
		$g->x_title="hours";
		$g->y_title="hits_number";
		$g->width=700;
		$g->line_green();

		
		
		$fileName = dirname(__FILE__)."/ressources/logs/hours-squid-size.png";
		@unlink($fileName);
			
		$g=new artica_graphs($fileName,60);
		$g->ydata=$ss;
		$g->xdata=$hh;
		$g->title='today: size MB/hour';
		$g->x_title="hours";
		$g->y_title="MB";
		$g->width=700;
		$g->line_green();
		
		
		
	}else{
	  if($GLOBALS["VERBOSE"]){echo "$fileName cache block ".__FUNCTION__." (".__LINE__.")\n";}
	}	

	shell_exec("/bin/chmod -R 755 /usr/share/artica-postfix/ressources/logs/*.png");
	
}



function today_hits(){
	
	
	$unix=new unix();
	$unix->events("running....");
	
	$fileName = dirname(__FILE__)."/ressources/logs/day-squid-hits.png";
	if(file_get_time_min($fileName)<60){
		if($GLOBALS["VERBOSE"]){echo "$fileName cache block ".__FUNCTION__." (".__LINE__.")\n";}
		return null;
	}
	@unlink($fileName);
	$g=new artica_graphs($fileName,60);
	
	
	
	$sql="SELECT COUNT( ID ) as tcount, DATE_FORMAT( zDate, '%h' ) AS tdate
		FROM dansguardian_events
		WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' )
		GROUP BY tdate";
	
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
if($q->mysql_error){
	writelogs($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);
}

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($GLOBALS["VERBOSE"]){echo "{$ligne["tdate"]}:{$ligne["tcount"]} ".__FUNCTION__."\n";}
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]=$ligne["tdate"];
}

$g->title='today: hits number';
$g->x_title="hours";
$g->y_title="hits_number";
$g->width=700;
return $g->line_green();
}	
	
function today_size(){
	$fileName = dirname(__FILE__)."/ressources/logs/day-squid-size.png";
	if(file_get_time_min($fileName)<60){
	if($GLOBALS["VERBOSE"]){echo "$fileName cache block ".__FUNCTION__." (".__LINE__.")\n";}
		return null;
	}
	
	@unlink($fileName);

$g=new artica_graphs($fileName,60);
if(!$g->checkfile()){return $fileName;}
	
	$sql="SELECT SUM(QuerySize) as tcount, DATE_FORMAT( zDate, '%h' ) AS tdate
		FROM dansguardian_events
		WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' )
		GROUP BY tdate";
	
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($GLOBALS["VERBOSE"]){echo "{$ligne["tdate"]}:{$ligne["tcount"]} ".__FUNCTION__."\n";}
	$g->ydata[]=round(($ligne["tcount"]/1024)/1000);
	$g->xdata[]  =$ligne["tdate"];
}


$g->title="today size MB";
$g->x_title="hours";
$g->y_title="MB";
$g->width=700;
$g->line_green();
@chmod($fileName,0777);

}	
	
function month_size(){
$fileName = dirname(__FILE__)."/ressources/logs/month-squid-size.png";
	if(file_get_time_min($fileName)<3600){
		if($GLOBALS["VERBOSE"]){echo "$filename cache block ".__FUNCTION__." (".__LINE__.")\n";}
		return null;
	}
	
	@unlink($fileName);
	$g=new artica_graphs($fileName,3600);
	if(!$g->checkfile()){
	writelogs("return $fileName",__FUNCTION__,__FILE__,__LINE__);return $fileName;}
	
	$sql="SELECT SUM(QuerySize) as tcount,DATE_FORMAT(zDate,'%d') as tdate
		FROM dansguardian_events
		WHERE MONTH(zDate) = MONTH(NOW()) AND YEAR(zDate)=YEAR(NOW())
		GROUP BY tdate";
	
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
if(!$q->ok){
	writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
}
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($GLOBALS["VERBOSE"]){echo "{$ligne["tdate"]}:{$ligne["tcount"]} ".__FUNCTION__."\n";}
	$g->ydata[]=round(($ligne["tcount"]/1024)/1000);
	$g->xdata[]=$ligne["tdate"];
}



$g->title="This mon size MB";
$g->x_title="days";
$g->y_title="MB";
$g->width=700;
$g->line_green();
@chmod($fileName,0777);
}
function month_hits(){
$fileName = dirname(__FILE__)."/ressources/logs/month-squid-hits.png";
	if(file_get_time_min($fileName)<3600){return null;}
	@unlink($fileName);
$g=new artica_graphs($fileName,3600);
if(!$g->checkfile()){
	writelogs("return $fileName",__FUNCTION__,__FILE__,__LINE__);
	return $fileName;}
	
	$sql="SELECT COUNT(ID) as tcount,DATE_FORMAT(zDate,'%d') as tdate
		FROM dansguardian_events
		WHERE MONTH(zDate) = MONTH(NOW()) AND YEAR(zDate)=YEAR(NOW())
		GROUP BY tdate";
	
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
if(!$q->ok){
	writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
}
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($GLOBALS["VERBOSE"]){echo "{$ligne["tdate"]}:{$ligne["tcount"]} ".__FUNCTION__."\n";}
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]=$ligne["tdate"];
}


$g->width=700;
$g->title="this_month hits number";
$g->x_title="days";
$g->y_title="hits_number";
$g->line_green();
@chmod($fileName,0777);

}

function GeoIP($servername){
	
	
	
	if(!function_exists("geoip_record_by_name")){
		if($GLOBALS["VERBOSE"]){echo "geoip_record_by_name no such function\n";}
		return array();
	}
	$site_IP=gethostbyname($servername);
	if($site_IP==null){events("GeoIP():: $site_IP is Null");return array();}
	
	
	if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$site_IP)){
		events("GeoIP():: $site_IP ->gethostbyname()");
		$site_IP=gethostbyname($site_IP);
		events("GeoIP():: $site_IP");
	}
	
	
	if(trim($GLOBALS["COUNTRIES"][$site_IP])<>null){
		events("GeoIP():: $site_IP {$GLOBALS["COUNTRIES"][$site_IP]}/{$GLOBALS["CITIES"][$site_IP]}");
		if($GLOBALS["VERBOSE"]){echo "$site_IP:: MEM={$GLOBALS["COUNTRIES"][$site_IP]}\n";}
		return array($GLOBALS["COUNTRIES"][$site_IP],$GLOBALS["CITIES"][$site_IP]);
	}
	
	$record = geoip_record_by_name($site_IP);
	if ($record) {
		$Country=$record["country_name"];
		$city=$record["city"];
		$GLOBALS["COUNTRIES"][$site_IP]=$Country;
		$GLOBALS["CITIES"][$site_IP]=$city;
		events("GeoIP():: $site_IP $Country/$city");
		return array($GLOBALS["COUNTRIES"][$site_IP],$GLOBALS["CITIES"][$site_IP]);
	}else{
		events("GeoIP():: $site_IP No record");
		if($GLOBALS["VERBOSE"]){echo "$site_IP:: No record\n";}
		return array();
	}
		
	return array();
}

?>