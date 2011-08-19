<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
$GLOBALS["NO_PID_CHECKS"]=false;

if($argv[1]=="--av-uris"){ParseKav4UriLogs();die();}
if($argv[1]=="--av-events"){av_events();die();}
if($argv[1]=="--av-stats"){av_stats();die();}

$unix=new unix();
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$pid=$unix->get_pid_from_file($pidfile);
if($unix->process_exists($pid,basename(__FILE__))){
	writelogs(basename(__FILE__).":Already executed pid $pid.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

@file_put_contents($pidfile, getmypid());

if($argv[1]=="--retrans"){ParseRetranslatorLogs();die();}



$GLOBALS["NO_PID_CHECKS"]=true;
ParseKas3Logs();
ParseKav4ProxyLogs();
ParseKav4UriLogs();
av_stats();
ParseKavmilterLogs();
ParseRetranslatorLogs();

function stats_pid(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".". __FUNCTION__.".pid";
	$pid=$unix->get_pid_from_file($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){
		writelogs(basename(__FILE__).":Already executed pid $pid.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
		die();
	}
	
	@file_put_contents($pidfile, getmypid());
	
}

function ParseKav4UriLogs(){
	$unix=new unix();
	if(system_is_overloaded(basename(__FILE__))){
		if($GLOBALS["VERBOSE"]){"System overloaded\n";}
		return;
	}
	if(!$GLOBALS["NO_PID_CHECKS"]){	if(stats_pid()){return;}}	
	
	$tablename="Kav4Proxy_".date('Y').date('m');
	if($GLOBALS["VERBOSE"]){echo "Table $tablename/artica_events...\n";}
	$q=new mysql();
	if(!$q->TABLE_EXISTS($tablename, "artica_events")){
	$sql="CREATE TABLE `artica_events`.`$tablename` (
		`zmd5` VARCHAR( 90 ) NOT NULL ,
		`zDate` DATETIME NOT NULL ,
		`size` INT( 3 ) NOT NULL ,
		`status` VARCHAR( 40 ) NOT NULL ,
		`ICAP_SERVER` VARCHAR( 40 ) NOT NULL ,
		`uid` VARCHAR( 128 ) NOT NULL ,
		`client` VARCHAR( 40 ) NOT NULL ,
		`uri` VARCHAR( 255 ) NOT NULL ,
		`country` VARCHAR( 90 ) NOT NULL ,
		`sitename` VARCHAR( 128 ) NOT NULL ,
		`category` VARCHAR( 90 ) NOT NULL ,
		PRIMARY KEY ( `zmd5` ) ,
		INDEX ( `zDate` , `size` , `status` , `ICAP_SERVER` , `uid` , `client` , `country` , `sitename` , `category` )
		)";	
		$q->QUERY_SQL($sql,"artica_events");
		if($GLOBALS["VERBOSE"]){echo "Table $tablename/artica_events failed...\n";}
		if(!$q->ok){$unix->send_email_events("Unable to create $tablename/artica_events" , "Kaspersky statistics has been aborted\n$q->mysql_error", "proxy");return;}
	
	}
	
	$WorkingDirectory="/var/log/artica-postfix/kav4Server-queue";
	$WorkingDirectoryError="/var/log/artica-postfix/kav4Server-errors";
	if(!is_dir($WorkingDirectoryError)){@mkdir($WorkingDirectoryError,0600,true);}
	
 	if (!$handle = @opendir($WorkingDirectory)) {
 		if($GLOBALS["VERBOSE"]){echo "$WorkingDirectory no such directory\n";}
 		return ;
 	}	
	//$newArray=array("DATE" =>$date,"SIZE"=>$size,"STATUS"=>$status,"ICAP_SERVER"=>$icap_server,"UID"=>$uid,
		//"CLIENT"=>$clientip,"URI"=>$uri,"COUNTRY"=>$Country,"SITENAME"=>$sitename);	
	if($GLOBALS["VERBOSE"]){echo "Processing $WorkingDirectory\n";}
	$prefixsql="INSERT IGNORE INTO $tablename (`zmd5`,`zDate`,`size`,`status`,`ICAP_SERVER`,`uid`,`client`,`uri`,`country`,`sitename`) VALUES ";
	
	
	while (false !== ($filename = readdir($handle))) {
		$targetFile="$WorkingDirectory/$filename";
		if($GLOBALS["VERBOSE"]){echo "Processing $targetFile\n";}
		if(!is_file($targetFile)){
			if($GLOBALS["VERBOSE"]){echo "Processing $targetFile no such file\n";}
			continue;
		}
		$array=unserialize(@file_get_contents($targetFile));
		if(!is_array($array)){@unlink($targetFile);if($GLOBALS["VERBOSE"]){echo "Processing $targetFile not an array\n";}continue;}
		$md5=md5(serialize($array));
		$suffix[]="('$md5','{$array["DATE"]}','{$array["SIZE"]}','{$array["STATUS"]}','{$array["ICAP_SERVER"]}',
		'{$array["UID"]}','{$array["CLIENT"]}','{$array["URI"]}','{$array["COUNTRY"]}',
		'{$array["SITENAME"]}')";
		
		@unlink($targetFile);
		if(count($suffix)>500){
			$d=$d+count($suffix);
			$sql="$prefixsql ".@implode(",", $suffix);
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){@file_put_contents($sql,"$WorkingDirectoryError/".md5($sql).".err");$unix->send_email_events("kav4proxy statistics Mysql error", "$q->mysql_error\nProcess has been aborted and saved in $WorkingDirectoryError directory", "proxy");}
			if(system_is_overloaded(basename(__FILE__))){$unix->send_email_events("kav4proxy statistics aborted du to overload computer", "Will retry in next cycle", "proxy");return;}		
		}
	}
	
	if(count($suffix)>1){
		$d=$d+count($suffix);
		$sql="$prefixsql ".@implode(",", $suffix);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){@file_put_contents($sql,"$WorkingDirectoryError/".md5($sql)."err");$unix->send_email_events("kav4proxy statistics Mysql error", "$q->mysql_error\nProcess has been aborted and saved in $WorkingDirectoryError directory", "proxy");}	
	}
	
	if($GLOBALS["VERBOSE"]){echo "processed $d files\n";}
}



function ParseKas3Logs(){
	$dir="/var/log/artica-postfix/kaspersky/kas3";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofKas3FilesUpdated=NumberofKas3FilesUpdated("$dir/$file");
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Anti-Spam: $NumberofKas3FilesUpdated pattern file(s) updated";
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
					
		}
		
		@unlink("$dir/$file");
	}
}

function ParseKavmilterLogs(){
	$dir="/var/log/artica-postfix/kaspersky/kavmilter";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofKas3FilesUpdated=NumberofKavFilesUpdated("$dir/$file");
		if($NumberofKas3FilesUpdated<0){
			$subject="Kaspersky Antivirus Mail: update failed";	
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			@unlink("$dir/$file");
			continue;
		}
		
		
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Antivirus Mail: $NumberofKas3FilesUpdated new viruses in databases";
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
					
		}
		
		@unlink("$dir/$file");
	}
}

function ParseKav4ProxyLogs(){
	$dir="/var/log/artica-postfix/kaspersky/kav4proxy";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofKas3FilesUpdated=NumberofKavFilesUpdated("$dir/$file");
		
		
		if($NumberofKas3FilesUpdated<0){
			$subject="Kaspersky Antivirus Proxy: update failed";	
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			ParseKav4ProxyLogsMysql($date,$subject,"$dir/$file");
			continue;
		}
		
		
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Antivirus Proxy: $NumberofKas3FilesUpdated new viruses in databases";
			ParseKav4ProxyLogsMysql($date,$subject,"$dir/$file");
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			continue;		
		}
		
		if(AllAreUp2date("$dir/$file")){
			ParseKav4ProxyLogsMysql($date,"All files are up-to-date","$dir/$file");
			continue;
		}
		
		if(completed("$dir/$file")){
			ParseKav4ProxyLogsMysql($date,"Update completed successfully","$dir/$file");
			continue;
		}
		
		$size=@filesize("$dir/$file");
		
		ParseKav4ProxyLogsMysql($date,"Updates launched...($size bytes)","$dir/$file");
	}
}

function AllAreUp2date($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	if(preg_match("#All files are up-to-date#",$line)){return true;}
	}
	return false;
}
function completed($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	if(preg_match("#Update 'Kaspersky Anti-Virus for Proxy Server' completed successfully#",$line)){return true;}
	}
	return false;
}


function ParseKav4ProxyLogsMysql($date,$subject,$filename){
	$datas=@file_get_contents($filename);
	$datas=addslashes($datas);
	$sql="INSERT IGNORE INTO kav4proxy_updates (zDate,subject,content) VALUES('$date','$subject','$datas')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		$unix=new unix();
		$unix->send_email_events("Mysql error ".__FUNCTION__.",".basename(__FILE__), $q->mysql_error, "system");
	}
	@unlink("$filename");
}


function NumberofKas3FilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^File updated.+#",$line)){$count++;continue;}
	}
	
	return $count;
}

function NumberofKavFilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	$line=trim($line);
	if(preg_match("#^Extended AV bases are OK, latest update.+?,.+?([0-9]+)#",$line)){
		$count=$re[1];
		break;
	}
	
	
	if(preg_match("#^Update.+?Kaspersky.+?failed$#",$line)){
		$count=-1;
	}
	
	
	}
	
	return $count;
}
function NumberofRestransFilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	$line=trim($line);
	if(preg_match("#file installed\s+'(.+?)\.(avc|set|ini|lst|dt)#",$line,$re)){
		$fi[]=$re[1].".".$re[2];
		continue;
		}
	if(preg_match("#File updated\s+'(.+?)\.(avc|set|ini|lst|dt)#",$line,$re)){
		$fi[]=$re[1].".".$re[2];
		continue;
		}		
	}



if(is_array($fi)){
	reset($fi);
	while (list ($a, $path_local) = each ($fi) ){
		
		if(is_file($path_local)){
			$size=@filesize($path_local);
			$bgsize=$bgsize+$size;
		}else{
			echo "FAILED $path_local\n";
		}
		
	}
}
if($bgsize>0){
	$bgsize=$bgsize/1024;
	$bgsize=str_replace("&nbsp;"," ",FormatBytes($bgsize));
}

return array(count($fi),$bgsize);
}


function ParseRetranslatorLogs(){
	
	$unix=new unix();
	if($unix->PIDOF("/usr/share/artica-postfix/bin/retranslator.bin")>0){return ;}
	
$dir="/var/log/kretranslator";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#retranslator-([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+).debug#",$file,$re)){continue;}
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofFilesUpdated=NumberofRestransFilesUpdated("$dir/$file");
		if($NumberofFilesUpdated[0]>0){
			$subject="Kaspersky Retranslator: {$NumberofFilesUpdated[0]} files updated ({$NumberofFilesUpdated[1]})";	
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			@unlink("$dir/$file");
			continue;
		}
		
		@unlink("$dir/$file");
	}	
	
}


function av_stats(){
	
	$GLOBALS["NO_PID_CHECKS"]=true;
	
	$users=new usersMenus();
	if(!$users->KAV4PROXY_INSTALLED){
		if($GLOBALS["VERBOSE"]){writelogs("Kav4Proxy is not installed...",__FUNCTION__,__FILE__,__LINE__);}
		return;
		
	}
	
	$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$unix=new unix();
	$minute=$unix->file_time_min($timefile);
	if(!$GLOBALS["FORCE"]){
		if($minute<15){
		if($GLOBALS["VERBOSE"]){writelogs("{$minute}Mn need 15, aborting",__FUNCTION__,__FILE__,__LINE__);}
		return;
		}
	}
	
	$pid=$unix->get_pid_from_file("/var/run/kav4proxy/kavicapserver.pid");
	if(!$unix->process_exists($pid)){
		if($GLOBALS["VERBOSE"]){writelogs("Process antivirus statistics failed, Kav4Proxy seems not running (PID:$pid)",__FUNCTION__,__FILE__,__LINE__);}
		$unix->send_email_events("Process antivirus statistics failed, Kav4Proxy seems not running (PID:$pid)", "/var/run/kav4proxy/kavicapserver.pid as no valid PID", "proxy");
		return;
	}
		
	@unlink($timefile);
	@file_put_contents($timefile, time());
	$kill=$unix->find_program("kill");
	if($GLOBALS["VERBOSE"]){writelogs("$kill -USR2 $pid",__FUNCTION__,__FILE__,__LINE__);}
	shell_exec("$kill -USR2 $pid");
	if(!is_file("/var/log/kaspersky/kav4proxy/counter.stats")){
		if(is_file("/var/log/kaspersky/kav4proxy/av.stats")){av_events();}
		if($GLOBALS["VERBOSE"]){writelogs("/var/log/kaspersky/kav4proxy/counter.stats no such file",__FUNCTION__,__FILE__,__LINE__);}
		return;
	}
	
	if(is_file("/var/log/kaspersky/kav4proxy/av.stats")){av_events();}
	
	$FileExploded=explode("\n", @file_get_contents("/var/log/kaspersky/kav4proxy/counter.stats"));
	
	if($GLOBALS["VERBOSE"]){writelogs("/var/log/kaspersky/kav4proxy/counter.stats ". count($FileExploded) . " items",__FUNCTION__,__FILE__,__LINE__);}
	
	$val=array();
	while (list ($num, $line) = each ($FileExploded) ){
		if(preg_match("#^(.+?)\s+([0-9\.]+)#", $line,$re)){
			if($GLOBALS["VERBOSE"]){writelogs("item: {$re[1]} = \"{$re[2]}\"",__FUNCTION__,__FILE__,__LINE__);}
			$val[trim($re[1])]=trim($re[2]);
		}else{
			if($GLOBALS["VERBOSE"]){writelogs("$line no match ^(.+?)\s+([0-9\.]+)",__FUNCTION__,__FILE__,__LINE__);}
		}
	}
	
	if(count($val)==0){
		if($GLOBALS["VERBOSE"]){writelogs("\$val no items, aborting",__FUNCTION__,__FILE__,__LINE__);}
		return;
	}
	$fields[]="`zDate`";
	$values[]="'". date('Y-m-d H:i:s')."'";
	
	
	while (list ($num, $line) = each ($val) ){
		if($num==null){continue;}
		$fields[]="`$num`";
		$values[]="'$line'";
		
	}	
	
	
	$sql="INSERT IGNORE INTO kav4proxy_av_stats (".@implode(",", $num).") VALUES(".@implode(",", $values).")";
	if($GLOBALS["VERBOSE"]){writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		$unix->send_email_events("Process antivirus statistics failed, mysql errors", "Query was: $sql\nError was:$q->mysql_error\nData was\n".@file_get_contents("/var/log/kaspersky/kav4proxy/counter.stats"), "proxy");
		return;
	}
	@unlink("/var/log/kaspersky/kav4proxy/counter.stats");
	
	
}

function av_events(){
	
	if(!$GLOBALS["NO_PID_CHECKS"]){if(stats_pid()){return;}}		
	
	if(!is_dir("/var/log/artica-postfix/kav4Server-queue")){@mkdir("/var/log/artica-postfix/kav4Server-queue",0600,true);}
	$f=fopen("/var/log/kaspersky/kav4proxy/av.stats",'r');
	$data='';
	$c=0;
	while(!feof($f)){
    	$data=fgets($f);
    	$sitename=null;
    	$Country=null;
    	$virus=null;
    	$date=null;
		if(!preg_match("#([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9\:]+)\s+([0-9]+)\s+(.+?)\s+(RESPMOD|REQMOD)\s+(.+?)\s+(.+?)\s+(.+?)\s+(.+)#", $data, $re)){continue;}
		$day=$re[1];
		$month=$re[2];
		$year=$re[3];
		$time=$re[4];
		$size=$re[5];
		$status=$re[6];
		$filter=$re[7];
		$icap_server=$re[8];
		$uid=$re[9];
		$clientip=$re[10];
		$uri=$re[11];
		$c++;
		if(preg_match("#INFECTED\s+(.+)#", $status,$ri)){
			$virus=$ri[1];
			$status="INFECTED";
		}
		
		if(preg_match("#^(?:[^/]+://)?([^/:]+)#",$uri,$re)){
			$sitename=$re[1];
			if(preg_match("#^www\.(.+)#",$sitename,$ri)){$sitename=$ri[1];}
		}
		if($sitename==null){continue;}
 		$date="$year-$month-$day $time";
 		$database="kasperskyav$month";
		
		if(trim($GLOBALS["IPs"][$sitename])==null){
				$site_IP=trim(gethostbyname($sitename));
				$GLOBALS["IPs"][$sitename]=$site_IP;
			}else{
				$site_IP=$GLOBALS["IPs"][$sitename];
			}
			
			if(count($_GET["IPs"])>5000){unset($_GET["IPs"]);}
			if(count($_GET["COUNTRIES"])>5000){unset($_GET["COUNTRIES"]);}
	
		if(trim($GLOBALS["COUNTRIES"][$site_IP])==null){
			if(function_exists("geoip_record_by_name")){
				if($site_IP==null){$site_IP=$sitename;}
				$record = @geoip_record_by_name($site_IP);
				if ($record) {
					$Country=$record["country_name"];
					$GLOBALS["COUNTRIES"][$site_IP]=$Country;
				}
			}else{
				$geoerror="geoip_record_by_name no such function...";
			}
		}else{
			$Country=$GLOBALS["COUNTRIES"][$site_IP];
		}			
			
		$newArray=array();
		$newArray=array("DATE" =>$date,"SIZE"=>$size,"STATUS"=>$status,"ICAP_SERVER"=>$icap_server,"UID"=>$uid,
		"CLIENT"=>$clientip,"URI"=>$uri,"COUNTRY"=>$Country,"SITENAME"=>$sitename);	
		$newline=serialize($newArray);
		$md5=md5($newline);
		$nextfile="/var/log/artica-postfix/kav4Server-queue/$md5.sql";
		file_put_contents($nextfile, $newline);
		$newline=null;
		$Country=null;
		
	}

	fclose($f); 
	@unlink("/var/log/kaspersky/kav4proxy/av.stats");
	echo "$c files processed...\n";
	$GLOBALS["NO_PID_CHECKS"]=true;
	ParseKav4UriLogs();
	
}









?>