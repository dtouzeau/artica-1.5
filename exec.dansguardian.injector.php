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
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if($argv[1]=="--import"){include_tpl_file($argv[2],$argv[3]);die();}
if($argv[1]=="--sites-infos"){ParseSitesInfos();die();}

$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
$unix=new unix();
$GLOBALS["CLASS_UNIX"]=$unix;
if($unix->process_exists($oldpid)){
	$time=$unix->PROCCESS_TIME_MIN($oldpid);
	events(basename(__FILE__).": Already executed $oldpid (since {$time}Mn).. aborting the process (line: ".__LINE__.")");
	events_tail("Already executed $oldpid (since {$time}Mn). aborting the process (line: ".__LINE__.")");
	if($time>120){
		events(basename(__FILE__).": killing $oldpid  (line: ".__LINE__.")");
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

if(!is_dir("/var/log/artica-postfix/dansguardian-stats")){@mkdir("/var/log/artica-postfix/dansguardian-stats",660,true);}
if(!is_dir("/var/log/artica-postfix/dansguardian-stats2")){@mkdir("/var/log/artica-postfix/dansguardian-stats2",660,true);}
if(!is_dir("/var/log/artica-postfix/dansguardian-stats3")){@mkdir("/var/log/artica-postfix/dansguardian-stats3",660,true);}

ParseLogsNew();
ParseLogs();
ParseSitesInfos();
PaseUdfdbGuard();
$t2=time();
$distanceOfTimeInWords=distanceOfTimeInWords($t1,$t2);

events(basename(__FILE__).": finish in $distanceOfTimeInWords");
events_tail("finish in $distanceOfTimeInWords");
die();	

function ParseLogs(){
	$count=0;
	events_tail("dansguardian-stats:: parsing /var/log/artica-postfix/dansguardian-stats");
	foreach (glob("/var/log/artica-postfix/dansguardian-stats/*.sql") as $file) {
		$q=new mysql();
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
		
	

function ParseLogsNew(){
	
	
if (!$handle = opendir("/var/log/artica-postfix/dansguardian-stats2")) {
		events_tail("dansguardian-stats2:: -> glob failed in line:".__LINE__);
		return ;
	}		
	
	$c=0;
	$t=0;
	$q=new mysql();
	$q->BuildTables();
	$dansguardian_events="dansguardian_events_".date('Ym');
	$prefixsql="INSERT IGNORE INTO $dansguardian_events (`sitename`,`uri`,`TYPE`,`REASON`,`CLIENT`,`zDate`,`zMD5`,`remote_ip`,`country`,`QuerySize`,`uid`,`cached`) VALUES ";
	while (false !== ($filename = readdir($handle))) {
		$targetFile="/var/log/artica-postfix/dansguardian-stats2/$filename";
		if(!is_file($targetFile)){
			events_tail("dansguardian-stats2:: $c -> $filename is not an sql file ".__LINE__);
			continue;
		}
		if(!preg_match("#.+?\.sql$#",basename($filename))){
			events_tail("dansguardian-stats2:: $c -> $filename is not an sql file ".__LINE__);
			continue;
		}
		
		$datas=@file_get_contents($targetFile);
		if(trim($datas)==null){
			events_tail("dansguardian-stats2:: $filename is empty ! " .__LINE__);
			continue;
		}
		$datas=str_replace("Lao People's Democratic Republic","Lao People\'s Democratic Republic",$datas);
		$sql="$prefixsql $datas";
		$c++;
		$t++;
		if($c>100){events_tail("dansguardian-stats2:: $t entries ".__LINE__);$c=0;}
		$q->QUERY_SQL($sql,"artica_events");
		
		if(!$q->ok){
			events_tail("dansguardian-stats2:: $q->mysql_error  lines " .__LINE__);
			events_tail("dansguardian-stats2:: Error in file $targetFile  lines " .__LINE__);
			continue;
		}
		
		@unlink($targetFile);
		usleep(5000);
		if($t>5000){
			events_tail("dansguardian-stats2:: squid more than 5.000 entries ".__LINE__);
			break;
		}
	}

}

function PaseUdfdbGuard(){
	
	$count=0;
	$total=0;
	$PREFIX="INSERT INTO `blocked_websites` (client,website,category,rulename,public_ip) VALUES";
	events_tail("dansguardian-stats:: parsing /var/log/artica-postfix/ufdbguard-queue line:".__LINE__);
	foreach (glob("/var/log/artica-postfix/ufdbguard-queue/*.sql") as $filename) {
		events_tail("dansguardian-stats:: parsing $filename line:".__LINE__);
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
			$q=new mysql();
			$sql=$PREFIX." ".@implode(",",$f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
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
		$q=new mysql();
		$sql=$PREFIX." ".@implode(",",$f);
		$q->QUERY_SQL($sql,"artica_events");
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
		events_tail("dansguardian-stats:: parsing $filename line:".__LINE__);
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
	$q=new mysql();
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
	$q=new mysql();
	$sql="SELECT category FROM dansguardian_community_categories WHERE pattern='$sitename'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("Fatal Error: $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$cats[$ligne["category"]]=$ligne["category"];
	}
	
	if(!is_array($cats)){return null;}
	while (list ($category, $none) = each ($cats) ){
		$f[]=$category;
	}
	return @implode(",",$f);
}

function include_tpl_file($path,$category){
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	if($uuid==null){echo "UUID=NULL; Aborting";return;}
	if($category==null){echo "CATEGORY=NULL; Aborting";return;}				
	if(!is_file($path)){
		echo "$path no such file\n";
		return;
	}
	
	$q=new mysql();
	$array=array();
	$f=@explode("\n",@file_get_contents($path));
	$count_websites=count($f);
	$i=0;$d=0;$group=0;
	while (list ($index, $website) = each ($f) ){
		$i++;
		$d++;
		if($d>1000){
			$group=$group+$d;
			events_tail("include_tpl_file($category):: importing $group sites...");
			$d=0;
		}
		if($website==null){return;}
		$www=trim(strtolower($website));
		if(preg_match("#www\.(.+?)$#i",$www,$re)){$www=$re[1];}
		$md5=md5($www.$category);	
		if($array[$md5]){echo "$www already exists\n";continue;}
		$enabled=1;
		$sql_add="INSERT INTO dansguardian_community_categories (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')";		
		$array[$md5]=true;
		if($GLOBALS["SIMULATE"]){echo "$i/$count_websites: $sql_add\n";continue;}
		
		$q->QUERY_SQL($sql_add,"artica_backup");
		if(!$q->ok){echo "$i/$count_websites Failed: $www\n";}else{echo "$i/$count_websites Success: $www\n";}
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