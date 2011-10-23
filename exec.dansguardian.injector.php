<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
cpulimit();
$_GET["LOGFILE"]="/var/log/artica-postfix/dansguardian-logger.debug";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--simulate#",implode(" ",$argv))){$GLOBALS["SIMULATE"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if($argv[1]=="--import"){include_tpl_file($argv[2],$argv[3]);die();}
if($argv[1]=="--sites-infos"){ParseSitesInfos();die();}

$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
$unix=new unix();
$GLOBALS["CLASS_UNIX"]=$unix;
if($unix->process_exists($oldpid)){
	$time=$unix->PROCCESS_TIME_MIN($oldpid);
	events(basename(__FILE__).": Already executed $oldpid (since {$time}Mn).. aborting the process (line:  Line: ".__LINE__.")");
	events_tail("Already executed $oldpid (since {$time}Mn). aborting the process (line:  Line: ".__LINE__.")");
	if($time>120){
		events(basename(__FILE__).": killing $oldpid  (line:  Line: ".__LINE__.")");
		shell_exec("/bin/kill -9 $oldpid");
	}else{	
		die();
	}
}
$pid=getmypid();
$t1=time();
file_put_contents($pidfile,$pid);
events(basename(__FILE__).": running $pid");
events_tail("running $pid");	

if(!is_dir("/var/log/artica-postfix/dansguardian-stats4")){@mkdir("/var/log/artica-postfix/dansguardian-stats4",660,true);}
if(!is_dir("/var/log/artica-postfix/dansguardian-stats4-failed")){@mkdir("/var/log/artica-postfix/dansguardian-stats4-failed",660,true);}

ParseLogsNew();
//ParseLogs();
ParseSitesInfos();
PaseUdfdbGuard();
$t2=time();
$distanceOfTimeInWords=distanceOfTimeInWords($t1,$t2);

events(basename(__FILE__).": finish in $distanceOfTimeInWords");
$mem=round(((memory_get_usage()/1024)/1000),2);
events_tail("finish in $distanceOfTimeInWords {$mem}MB");
die();	

function ParseLogs(){
	$count=0;
	events_tail("dansguardian-stats:: parsing /var/log/artica-postfix/dansguardian-stats");
	foreach (glob("/var/log/artica-postfix/dansguardian-stats/*.sql") as $file) {
		$q=new mysql_squid_builder();
		usleep(20000);
		$count=$count+1;
		$sql=@file_get_contents($file);
		if(trim($sql)==null){@unlink("$file");continue;}
		$q->QUERY_SQL($sql,"artica_events");
		if($q->ok){
			events_tail("success Parse $file sql file","MAIN",__FILE__,__LINE__);
			@unlink("$file");
		}else{
			events_tail("Failed Parse $file sql file $count");
			writelogs("Failed Parse $file sql file $count","MAIN",__FILE__,__LINE__);
			writelogs("$q->mysql_error","MAIN",__FILE__,__LINE__);
			writelogs("SQL[\"$sql\"]","MAIN",__FILE__,__LINE__);
		}
		
		$q->ok=true;
		
	}
	events_tail("dansguardian-stats:: Deleted $count mysql files for proxy events");

}


function events($text){
		$date=@date("h:i:s");
		$pid=getmypid();
		$logFile=$_GET["LOGFILE"];
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($GLOBALS["debug"]){echo "$pid $text\n";}
		@fwrite($f, "$pid ".basename(__FILE__)." $date $text\n");
		@fclose($f);	
		}
		
function GetRuleName($filename){
	$tb=explode("\n", $filename);
	while (list ($index, $ligne) = each ($tb) ){
		if(preg_match("#^groupname.+?'(.+?)'#", $ligne,$re)){return $re[1];}
	}
}	

function ParseLogsNew(){
	foreach (glob("/etc/dansguardian/dansguardianf*.conf") as $file) {
		$basename=basename($file);
		preg_match("#dansguardianf([0-9]+)\.#", $basename,$re);
		if($re[1]==1){$RULESD[1]="default";continue;}
		$RULESD[$re[1]]=GetRuleName($file);
		
	}
	
	
	
	$failedir="/var/log/artica-postfix/dansguardian-stats4-failed";
	events_tail("dansguardian-stats4:: parsing /var/log/artica-postfix/dansguardian-stats4 Line: ".__LINE__);
if (!$handle = opendir("/var/log/artica-postfix/dansguardian-stats4")) {
		events_tail("dansguardian-stats2:: -> glob failed in Line: ".__LINE__);
		return ;
	}		
	
	$c=0;
	$t=0;
	$q=new mysql_squid_builder();
	$q->CheckTables();
	if($q->MysqlFailed){events_tail("dansguardian-stats2:: Mysql connection failed, aborting.... Line: ".__LINE__);return;}
	
	$tables=array();
	
	while (false !== ($filename = readdir($handle))) {
		if($filename=="."){continue;}
		if($filename==".."){continue;}
		
		$targetFile="/var/log/artica-postfix/dansguardian-stats4/$filename";
		if(!is_file($targetFile)){events_tail("dansguardian-stats4:: $c -> $filename is not an sql file  Line: ".__LINE__);continue;}
		$t++;
		$array=unserialize(@file_get_contents($targetFile));
		@unlink($targetFile);
		if(!is_array($array)){events_tail("dansguardian-stats2:: $filename is not an array line:" .__LINE__);continue;}
		$userid=$array["userid"];
		if(trim($userid)=="-"){$userid=null;}
		$ipaddr=$array["ipaddr"];
		$uri=$array["uri"];
		if(preg_match("#^(?:[^/]+://)?([^/:]+)#",$uri,$re)){$sitename=$re[1];if(preg_match("#^www\.(.+)#",$sitename,$ri)){$sitename=$ri[1];}}
		if(!isset($GLOBALS["CATEGORIZED"][$sitename])){$GLOBALS["CATEGORIZED"][$sitename]=$q->GET_CATEGORIES($sitename);}
		$EVENT=$array["EVENT"];
		$WHY=$array["WHY"];
		$EXPLAIN=$array["EXPLAIN"];
		$BLOCKTYPE=$array["BLOCKTYPE"];
		$RULEID=$array["RULEID"];
		$TIME=$array["TIME"];;
		$mtime=strtotime($TIME);
		if($userid<>null){$ipaddr=$userid;}
		$category=addslashes($GLOBALS["CATEGORIZED"][$sitename]);
		if(!isset($RULESD[$RULEID])){events_tail("dansguardian-stats4:: Unable to find rule name for RuleID:`$RULEID` Line:".__LINE__);continue;}
		$rulename=addslashes($RULESD[$RULEID]);
		$uri=addslashes($uri);
		$EVENT=addslashes($EVENT);
		$WHY=addslashes($WHY);
		$EXPLAIN=addslashes($EXPLAIN);
		$BLOCKTYPE=addslashes($BLOCKTYPE);
		$tableblock=date('Ymd',$mtime)."_blocked";
		$tables[$tableblock][]="('$TIME','$ipaddr','$sitename','$category','$rulename','','$uri','$EVENT','$WHY','$EXPLAIN','$BLOCKTYPE')";
		
		
	}
	if($t==0){return;}
	events_tail("dansguardian-stats4:: Parsed $t files Line: ".__LINE__);
	if(count($tables)==0){events_tail("dansguardian-stats4:: tables is not an array Line: ".__LINE__);return;}
	
	
	while (list ($tablename, $queries) = each ($tables) ){
		events_tail("dansguardian-stats4:: $tablename -> " .count($queries). " queries Line: ".__LINE__);
		$sql="INSERT IGNORE INTO $tablename (`zDate`, `client` , `website`, `category` , `rulename` , `public_ip` , `uri` , `event` , `why` , `explain` , `blocktype`)
		VALUES ".@implode(",", $queries);
		$q->QUERY_SQL($sql);
		$data=array("ERROR"=>$q->mysql_error,"SQL"=>$sql);
		if(!$q->ok){
			events_tail("dansguardian-stats4:: $tablename -> $q->mysql_error Line: ".__LINE__);
			@file_put_contents($failedir."/". md5($sql), serialize($data));}
		
		
	}
	
  	
}

function PaseUdfdbGuard(){
	$q=new mysql_squid_builder();
	$q->CheckTables();
	$count=0;
	$total=0;
	$tableblock=date('Ymd')."_blocked";
	$PREFIX="INSERT INTO `$tableblock` (client,website,category,rulename,public_ip) VALUES";
	events_tail("PaseUdfdbGuard:: parsing /var/log/artica-postfix/ufdbguard-queue Line: ".__LINE__);
	foreach (glob("/var/log/artica-postfix/ufdbguard-queue/*.sql") as $filename) {
		events_tail("dansguardian-stats:: parsing $filename Line: ".__LINE__);
		$content=@file_get_contents($filename);
		if($content==null){
			events_tail("PaseUdfdbGuard:: Fatal $filename is empty !");
			@unlink($filename);
		}
		$f[]=$content;
		@unlink($filename);
		$count++;
		$total++;
		if($count>500){
			events_tail("PaseUdfdbGuard:: $count -> send to mysql");
			$count=0;
			$q=new mysql_squid_builder();
			$sql=$PREFIX." ".@implode(",",$f);
			$f=array();
			$q->QUERY_SQL($sql);
			if(!$q->ok){
				@file_put_contents("/var/log/artica-postfix/ufdbguard-queue/".md5($sql)."error",$sql);
				events_tail("PaseUdfdbGuard:: Fatal $q->mysql_error");
				writelogs($q->mysql_error."\n",$sql,__FILE__,__LINE__);
			}
			
			$sql=null;
			continue;
		}
	}
	
	if(count($f)>0){
		$q=new mysql_squid_builder();
		$sql=$PREFIX." ".@implode(",",$f);
		$q->QUERY_SQL($sql);
		if(!$q->ok){
			@file_put_contents("/var/log/artica-postfix/ufdbguard-queue/".md5($sql)."error",$sql);
			events_tail("PaseUdfdbGuard:: Fatal $q->mysql_error");	
		}
		$sql=null;
	}
	
	events_tail("PaseUdfdbGuard:: $total files.");
	
	
}

function ParseSitesInfos(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.'.pid';
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid)){return null;}
	@file_put_contents($pidfile,getmypid());	
	
	$count=0;
	events_tail("dansguardian-stats:: parsing /var/log/artica-postfix/dansguardian-stats3");
	foreach (glob("/var/log/artica-postfix/dansguardian-stats3/*") as $filename) {
		if($GLOBALS["VERBOSE"]){echo "$filename\n";}
		events_tail("dansguardian-stats:: parsing $filename Line: ".__LINE__);
		$datas=unserialize(@file_get_contents("$filename"));
		if(!is_array($datas)){events_tail(basename($filename))." is not an array";@unlink($filename);continue;}
		usleep(20000);
		if(ParseSitesInfos_inject($datas)){@unlink($filename);}
		$count++;
	}
	events_tail("dansguardian-stats3:: $count analyzed files.");
	
}

function ParseSitesInfos_inject($array){
	$sitename=strtolower($array["sitename"]);
	$sitename=str_replace("www.","",$sitename);
	$country=strtolower($array["country"]);
	$ipaddr=strtolower($array["ipaddr"]);
	$q=new mysql_squid_builder();
	$sql="SELECT website FROM dansguardian_sitesinfos WHERE website='$sitename'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$category_artica=ParseSitesInfos_artica_category($sitename);
	$categories=$category_artica;

	$tbl=explode(",",$categories);
	if(!is_array($tbl)){
		while (list ($num, $line) = each ($tbl) ){
			if(trim($line)==null){continue;}
			$cats[$line]=$line;
		}
	}
	
	if(is_array($cats)){while (list ($category, $none) = each ($cats) ){$f_cats[]=$category;}}
	$categories_sql=@implode(",",$f_cats);
	if($category_artica<>null){$community=1;}
	if($category_file<>null){$squidguard=1;}
	$country=addslashes($country);	
		
	if($ligne["website"]<>null){
		$prefix="UPDATE dansguardian_sitesinfos SET ";
		if($country<>null){$sqla[]="country='$country'";}
		if($ipaddr<>null){$sqla[]="ipaddr='$ipaddr'";}
		if($categories_sql<>null){$sqla[]="category='$categories_sql'";}
		if($categories_sql<>null){$sqla[]="dbpath='$categories_sql'";}
		if($community<>null){$sqla[]="community='$community'";}
		if($category_file<>null){$sqla[]="squidguard='$category_file'";}
		$sql="$prefix".@implode(",",$sqla)." WHERE website='{$ligne["website"]}'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			if($GLOBALS["VERBOSE"]){echo "$q->mysql_error::\n$sql\n";}
			events_tail("dansguardian-stats3:: $q->mysql_error:: $sql");
			return false;
		}		
		
		return true;
	}

	$sql="INSERT IGNORE INTO dansguardian_sitesinfos (website,category,community,squidguard,dbpath,country,ipaddr)
	VALUES('$sitename','$categories_sql','$community','$squidguard','$categories_sql','$country','$ipaddr')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo "$q->mysql_error::\n$sql\n";}
		events_tail("dansguardian-stats3:: $q->mysql_error:: $sql");
		return false;
	}
	
	events_tail("dansguardian-stats3:: $sitename: cats=$categories_sql");
	return true;
}

function ParseSitesInfos_artica_category($sitename){
	$q=new mysql_squid_builder();
	return $q->GET_CATEGORIES($sitename,true);
}

function include_tpl_file($path,$category){
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	if($uuid==null){echo "UUID=NULL; Aborting";return;}
	if($category==null){echo "CATEGORY=NULL; Aborting";return;}				
	if(!is_file($path)){echo "$path no such file\n";return;}
	
	$q=new mysql_squid_builder();
	$q->CreateCategoryTable($category);
	$TableDest="category_".$q->category_transform_name($category);	
	$array=array();
	$f=@explode("\n",@file_get_contents($path));
	$count_websites=count($f);
	$i=0;$d=0;$group=0;
	$prefix="INSERT IGNORE INTO $TableDest (zmd5,zDate,category,pattern,uuid) VALUES";
	while (list ($index, $website) = each ($f) ){
		$i++;$d++;
		if($d>1000){$group=$group+$d;events_tail("include_tpl_file($category):: importing $group sites...");$d=0;}
		if($website==null){return;}
		$www=trim(strtolower($website));
		if(preg_match("#www\.(.+?)$#i",$www,$re)){$www=$re[1];}
		$md5=md5($www.$category);	
		if($array[$md5]){echo "$www already exists\n";continue;}
		$enabled=1;
		$sql_add[]="('$md5',NOW(),'$category','$www','$uuid')";		
		$array[$md5]=true;
		if($GLOBALS["SIMULATE"]){echo "$i/$count_websites: $sql_add\n";continue;}
		if(count($sql_add)>500){
			$sql=$prefix.@implode(",",$sql_add);
			$q->QUERY_SQL($sql);
			if(!$q->ok){echo "$i/$count_websites Failed: $www\n";}else{echo "$i/$count_websites Success: $www\n";}
			$sql_add=array();
		}
	}
	
if(count($sql_add)>0){
			$sql=$prefix.@implode(",",$sql_add);
			$q->QUERY_SQL($sql);
			if(!$q->ok){echo "$i/$count_websites Failed: $www\n";}else{echo "$i/$count_websites Success: $www\n";}
			$sql_add=array();
		}	
	
	
echo " -------------------------------------------------\n";	
echo count($array)." websites done\n";
echo " -------------------------------------------------\n";	
}



function events_tail($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/proxy-injector.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$GLOBALS["CLASS_UNIX"]->events(basename(__FILE__)." $date $text");
		@fwrite($f, "$pid ".basename(__FILE__)." $date $text\n");
		@fclose($f);	
		}




		
		
		
?>