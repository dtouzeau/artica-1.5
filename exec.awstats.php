<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.awstats.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

$users=new usersMenus();
if(!$users->awstats_installed){"echo awstats not installed....\n";die();}

if($argv[1]=="--single"){exectute_awstats($argv[2],true);exit;}
if($argv[1]=="--postfix"){awstats_mail();exit;}
if($argv[1]=="--postfix-parse"){artica_parse($argv[2],false);exit;}
if($argv[1]=="--cleanlogs"){clean_maillogs();exit;}
if($argv[1]=="--cron"){awstats_cron();exit;}



run_general();


function run_general(){

	$unix=new unix();

	$perl=$unix->find_program("perl");
	$awstats=$unix->LOCATE_AWSTATS_BIN();
	$awstats_buildstaticpages=$unix->LOCATE_AWSTATS_BUILDSTATICPAGES_BIN();
	
	if(strlen($awstats)==0){
		if($GLOBALS["VERBOSE"]){echo "awstats failed 'awstats.pl' no such file\n";}
		$unix->send_email_events("awstats failed: \"awstats.pl\" no such file","please contact Artica support team","system");
		die();
	}	
	
	if(strlen($perl)==0){
		if($GLOBALS["VERBOSE"]){echo "awstats failed perl no such file\n";}
		$unix->send_email_events("awstats failed: perl no such file","please contact Artica support team","system");
		die();
	}	
	
	if(strlen($awstats_buildstaticpages)==0){
		if($GLOBALS["VERBOSE"]){echo "awstats failed awstats_buildstaticpages.pl no such file\n";}
		$unix->send_email_events("awstats failed: awstats_buildstaticpages.pl no such file","please contact Artica support team","system");
		die();
	}
	
	$sql="SELECT `website` FROM `awstats` WHERE `key`='AwstatsEnabled' AND `value`='1'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	
	
	if(!$q->TABLE_EXISTS('awstats_files','artica_backup')){
		if($GLOBALS["VERBOSE"]){echo "awstats_files mysql table doesn not exists\n";}
		$q->CheckTablesAwstats();
		
		if(!$q->TABLE_EXISTS('awstats_files','artica_backup')){
			if($GLOBALS["VERBOSE"]){echo "awstats_files mysql table does not exists\n";}
			$unix->send_email_events("awstats failed: database error","awstats_files no such table\n\n$sql\n");
			return;
		}		
	}
	
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";}
		$unix->send_email_events("awstats failed: database error","$q->mysql_error\n\n$sql\n");
		die();
			
	}
	
	$websitesnumber=mysql_num_rows($results);
	if($GLOBALS["VERBOSE"]){echo "$websitesnumber websites\n";}
	if($websitesnumber==0){die();}
	$nice=EXEC_NICE();
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		echo "Running awsats for {$ligne["website"]}\n";
		$servername=$ligne["website"];
		exectute_awstats($servername);
	}
	
	$sock=new sockets();
	if($sock->GET_INFO("ArticaMetaEnabled")==1){
		shell_exec($nice.LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-awstats-files");
	}	
	
}

function exectute_awstats($servername,$articameta=false){
	$unix=new unix();
	$perl=$unix->find_program("perl");
	$awstats=$unix->LOCATE_AWSTATS_BIN();
	$GLOBALS["ARTICAMETA"]=$articameta;
	$awstats_buildstaticpages=$unix->LOCATE_AWSTATS_BUILDSTATICPAGES_BIN();	
	$q=new mysql();
	$nice=EXEC_NICE();
	$GLOBALS["nice"]=$nice;	
	$aw=new awstats($servername);
		$config=$aw->buildconf();
		$configlength=strlen($config);
		if($configlength<10){
			if($GLOBALS["VERBOSE"]){echo "configuration file lenght failed $configlength bytes, aborting $servername\n";}
			return;
		}
		
		@file_put_contents("/etc/awstats/awstats.$servername.conf",$config);
		@chmod("/etc/awstats/awstats.$servername.conf",644);
		$Lang=$aw->GET("Lang");
		if($Lang==null){$Lang="auto";}
		@mkdir("/var/tmp/awstats/$servername",666,true);
		$t1=time();
		$cmd="$nice$perl $awstats_buildstaticpages -config=$servername -update -lang=$Lang -awstatsprog=$awstats -dir=/var/tmp/awstats/$servername 2>&1";
		if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
		exec($cmd,$results);
		if($GLOBALS["VERBOSE"]){echo @implode("\n",$results)."\n";}
		$t2=time();
		awstats_import_sql($servername,$articameta);
		$time_duration=distanceOfTimeInWords($t1,$t2);
		if($GLOBALS["VERBOSE"]){echo "$time_duration\n";}
		$unix->send_email_events("generating awstats statistics for $servername success $time_duration",@implode("\n",$results),"system");	
	}

function awstats_import_sql($servername,$articameta){
$q=new mysql();	
$sql="DELETE FROM awstats_files WHERE `servername`='$servername'";
		$q->QUERY_SQL($sql,"artica_backup");
		
		foreach (glob("/var/tmp/awstats/$servername/awstats.*") as $filename) {
			
			if(basename($filename)=="awstats.$servername.html"){
				$awstats_filename="index";
			}else{
				if(preg_match("#awstats\.(.+)\.([a-z0-9]+)\.html#",$filename,$re)){$awstats_filename=$re[2];}
			}
			if($GLOBALS["VERBOSE"]){echo "$servername: $awstats_filename\n";}
			if($awstats_filename<>null){
				$content=addslashes(@file_get_contents("$filename"));
				$results[]="Importing $filename";
				@unlink($filename);
				$sql="INSERT INTO awstats_files (`servername`,`awstats_file`,`content`)
				VALUES('$servername','$awstats_filename','$content')";
				$q->QUERY_SQL($sql,"artica_backup");
				if(!$q->ok){
					if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";}
					$unix->send_email_events("awstats for $servername failed database error",$q->mysql_error,"system");
					die();
				}
			}
					$q->ok;		
		}

if($articameta){
	$sock=new sockets();
	if($sock->GET_INFO("ArticaMetaEnabled")==1){
	shell_exec($GLOBALS["nice"].LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-awstats-files");
	}	
}				
		
		
}

function awstats_mail(){
	$users=new usersMenus();
	if(!$users->POSTFIX_INSTALLED){return;}
	$unix=new unix();
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		echo "$oldpid is already in memory\n";
		return;
	}
	
	$pid=posix_getpid();
	echo "running $pid PID\n";
	@file_put_contents($pidfile,$pid);
	$tt1=time();
	
	$nohup=$unix->find_program("nohup");
	if(!$users->awstats_installed){
		$unix->send_email_events("awstats is not installed, artica will install it itself",$q->mysql_error,"postfix");
		shell_exec(trim("$nohup /usr/share/artica-postfix/bin/artica-make APP_AWSTATS >/dev/null &"));
		return;
	}
	
	
	$sock=new sockets();
	$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");	
	$GLOBALS["maillogconvert"]=$unix->LOCATE_maillogconvert();
	$GLOBALS["zcat"]=$unix->find_program("zcat");
	$GLOBALS["perl"]=$unix->find_program("perl");
	$GLOBALS["nice"]=EXEC_NICE();
	$GLOBALS["sed"]=$unix->find_program("sed");
	
	
	if($GLOBALS["VERBOSE"]){
		echo "maillogconvert..........:{$GLOBALS["maillogconvert"]}\n";
		echo "zcat....................:{$GLOBALS["zcat"]}\n";
		echo "perl....................:{$GLOBALS["perl"]}\n";
		echo "nice....................:{$GLOBALS["nice"]}\n";
		echo "sed.....................:{$GLOBALS["sed"]}\n";
		
		
	}
	
	if(strlen($GLOBALS["maillogconvert"])==null){
		echo "maillogconvert.pl, no such file\n";
		return;
	}
	@mkdir("/var/log/mail-backup",666,true);
	
	foreach (glob("/var/log/mail.log.*.gz") as $filename) {
		shell_exec("{$GLOBALS["nice"]}{$GLOBALS["zcat"]} $filename >/tmp/mail.log");
		$t1=time();
		prepflog("/tmp/mail.log");
		$distanceOfTimeInWords=distanceOfTimeInWords($t1,time());
		shell_exec("/bin/mv $filename /var/log/mail-backup/");
		if($GLOBALS["VERBOSE"]){echo basename($filename)." $distanceOfTimeInWords\n";}
		$ev[]=basename($filename)." " .$distanceOfTimeInWords;
		@unlink("/tmp/mail.log");
		}
	
	foreach (glob("/var/log/mail.log.*") as $filename) {
		if(!preg_match("#\.[0-9]+$#",basename($filename))){
			if($GLOBALS["VERBOSE"]){echo basename($filename)." SKIP\n";}
			continue;
		}
		$t1=time();
		prepflog($filename);
		$distanceOfTimeInWords=distanceOfTimeInWords($t1,time());
		if($GLOBALS["VERBOSE"]){echo basename($filename)." $distanceOfTimeInWords\n";}
		$ev[]=basename($filename)." " .$distanceOfTimeInWords;
		shell_exec("/bin/mv $filename /var/log/mail-backup/");
		
	}
	$t1=time();
	prepflog("/var/log/mail.log");
	$distanceOfTimeInWords=distanceOfTimeInWords($t1,time());	
	$ev[]=basename("/var/log/mail.log")." " .$distanceOfTimeInWords;
	if($GLOBALS["VERBOSE"]){echo basename("/var/log/mail.log")." $distanceOfTimeInWords\n";}
	
	//$cmd="$nice$perl /usr/share/artica-postfix/bin/prepflog.pl </tmp/mail.log|$nice$perl $maillogconvert standard >>/var/log/artica-postfix/awstats-postfix.stats";
	
	
	foreach (glob("/var/log/artica-mail/*.stats") as $filename) {
		if(preg_match("#(.+?)\.([0-9]+)\.stats#",basename($filename),$re)){
			$instance=$re[1];
			$time=$re[2];
			$cmd="{$GLOBALS["nice"]}{$GLOBALS["perl"]} {$GLOBALS["maillogconvert"]} standard< $filename >/var/log/artica-mail/$instance.$time.aws";
			if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
			shell_exec($cmd);
			@unlink($filename);
		}
	}
	$filecount=0;
	foreach (glob("/var/log/artica-mail/*.aws") as $filename) {
		artica_parse($filename);$filecount++;$filecountl[]=$filename;}
	
	$distanceOfTimeInWords=distanceOfTimeInWords($tt1,time());	
	if($filecount>0){
		$unix->send_email_events("AWSTATS:: Success generating $filecount stats files ($distanceOfTimeInWords)",@implode("\n",$filename),"postfix");
		$cmd="{$GLOBALS["nice"]}".LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-postfix-events >/dev/null 2>&1 &";
		shell_exec($cmd);
	}	
	
	
	clean_maillogs();

}


function clean_maillogs(){
	
	$t1=time();
	$datte_start=date('Y-m-d H:i:s');
	$unix=new unix();
	$pidfile="/etc/artica-postfix/cron.2/".basename(__FILE__).".".__FUNCTION__.".pid";
	if($unix->process_exists(@file_get_contents($pidfile))){
		echo "Cleaning maillogs, already executed...\n";
		return;
	}
	
	@file_get_contents($pidfile,getmypid());
	
	$q=new mysql();
	$sock=new sockets();
	$num=$q->COUNT_ROWS("smtp_logs","artica_events");
	if($GLOBALS["VERBOSE"]){echo "smtp_logs: Storing $num rows\n";}
	$MaxMailEventsLogs=$sock->GET_INFO("MaxMailEventsLogs");
	if($MaxMailEventsLogs==null){$MaxMailEventsLogs=400000;}
	if($MaxMailEventsLogs<100){$MaxMailEventsLogs=4000;}
	if($GLOBALS["VERBOSE"]){echo "MaxMailEventsLogs:$MaxMailEventsLogs max rows\n";}
	if($num>$MaxMailEventsLogs){
		$todelete=$num-$MaxMailEventsLogs;
		if($GLOBALS["VERBOSE"]){echo "smtp_logs: deleting :$todelete rows\n";}
		$sql="DELETE FROM smtp_logs ORDER BY time_connect LIMIT $todelete";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){echo $q->mysql_error;}
	}else{
		if($GLOBALS["VERBOSE"]){echo "smtp_logs: No need to clean...\n";}
	}
	
	
	$num1=$q->COUNT_ROWS("mails_stats","artica_events");
	if($GLOBALS["VERBOSE"]){echo "mails_stats: Storing $num rows\n";}
	
	if($num1>$MaxMailEventsLogs){
		$todelete1=$num1-$MaxMailEventsLogs;
		if($GLOBALS["VERBOSE"]){echo "mails_stats: deleting :$todelete1 rows  \n";}
		$sql="DELETE FROM mails_stats ORDER BY zDate LIMIT $todelete1";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){echo $q->mysql_error;}
	}else{
		if($GLOBALS["VERBOSE"]){echo "mails_stats:No need to clean...\n";}
	}	
	
	

  $sql="OPTIMIZE TABLE `smtp_logs`";
  $q=new mysql();
  $q->QUERY_SQL($sql,"artica_events");	
  
  $sql="OPTIMIZE TABLE `mails_stats`";
  $q=new mysql();
  $q->QUERY_SQL($sql,"artica_events");  
  $t2=time();
  $timeexecuted=distanceOfTimeInWords($t1,$t2);
  $sum=$todelete1+$todelete;
	if($sum>0){
		$unix->send_email_events("success deleting $sum events in databases ($timeexecuted)",
		"mails_stats:$todelete1 events deleted and store $num rows (has being optimized)\nsmtp_logs:$todelete events and  and store $num1 rows (being optimized)\nOperation time:$timeexecuted and started at $datte_start","system");
	
	}

}


function prepflog($filename){
	if($GLOBALS["EnablePostfixMultiInstance"]>0){
		if(!is_array($GLOBALS["POSTFIX_INSTANCES"])){
			$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname'";
			$q=new mysql;
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo "$sql $q->mysql_error\n";return;}
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
				$ligne["value"]=trim($ligne["value"]);
				if($ligne["value"]==null){continue;}
				if(strtolower($ligne["value"])=="master"){continue;}
				$GLOBALS["POSTFIX_INSTANCES"]["postfix-{$ligne["value"]}"]="{$ligne["value"]}";
			}
		}
		
	}
	
	@mkdir("/var/log/artica-mail",0666,true);
	$t=time();
	if(is_array($GLOBALS["POSTFIX_INSTANCES"])){
		while (list ($instance, $ligne) = each ($GLOBALS["POSTFIX_INSTANCES"]) ){
			$cmd="{$GLOBALS["nice"]}{$GLOBALS["perl"]} /usr/share/artica-postfix/bin/prepflog.pl --syslog_name $instance<$filename >/var/log/artica-mail/$ligne.$t.log";
			if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
			shell_exec($cmd);
			prepflog_replace("/var/log/artica-mail/$ligne.$t.log","/var/log/artica-mail/$ligne.$t.stats",$instance);
			@unlink("/var/log/artica-mail/$ligne.$t.log");
		}
	}
	
	
	$cmd="{$GLOBALS["nice"]}{$GLOBALS["perl"]} /usr/share/artica-postfix/bin/prepflog.pl<$filename >/var/log/artica-mail/postfix.$t.stats";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	shell_exec($cmd);
	}
	
function prepflog_replace($filename,$fileto,$instance){
	$handle=fopen($filename,'r');
	$handle2=fopen($fileto,'w');
	$total=filesize($filename);
	$blocksize=1024;
	$sent=0;
	while($sent < $total){
    	$buf=fread($handle, $blocksize);
    	$buf=str_replace("$instance","postfix",$buf);
    	fwrite($handle2, $buf);
    	$sent += $blocksize;
	}
	
	fclose($handle);
	fclose($handle2);  
}

function artica_parse($filename){
	echo "Parsing $filename\n";
	if(preg_match("#^(.+?)\.[0-9]+\.aws$#",basename($filename),$re)){$instancename=$re[1];}
	
	$f=explode("\n",@file_get_contents($filename));
	$prefixsql="INSERT IGNORE INTO `mails_stats`(`zmd5`,`zDate`,`instance`,`sender`,`sender_domain`,`recipient`,`recipient_domain`,
	`sender_ip`,`recipient_ip`,`smtpcode`,`mailsize`,`artica_meta`) VALUES
	";
	$events_number=0;
	while (list ($num, $ligne) = each ($f) ){
		if(trim($ligne)==null){continue;}
		if(preg_match("#([0-9\-]+)\s+([0-9\:]+)\s+(.*?)\s+(.*?)\s+(.*?)\s+(.*?)\s+SMTP\s+-\s+([0-9]+)\s+([0-9\?]+)#",$ligne,$re)){
			$day=$re[1];
			$time=$re[2];
			$from=strtolower($re[3]);
			$to=strtolower($re[4]);
			$ipfrom=$re[5];
			$ipto=$re[6];
			$smtpcode=$re[7];
			$size=$re[8];
			if(!is_numeric($size)){$size=0;}
			$zdate="$day $time";
			$domainfrom="";
			$domainto="";
			if($from=="<>"){$from="Unknown";}
			if($to=="<>"){$to="Unknown";}
			
			if(preg_match("#(.+?)@(.+)#",$from,$re)){$domainfrom=$re[2];}
			if(preg_match("#(.+?)@(.+)#",$to,$re)){$domainto=$re[2];}
			if($domainfrom==null){$domainfrom="Unknown";}
			if($domainto==null){$domainto="Unknown";}
			$md5=md5("$instancename$day$time$from$to$size");
			$sq[]="('$md5','$zdate','$instancename','$from','$domainfrom','$to','$domainto','$ipfrom','$ipto','$smtpcode','$size',0)";
			$events_number++;
			
		}else{
			echo $ligne. "FAILED\n";
		}
		
		
		
	}
	
	if(is_array($sq)){
		$sql="$prefixsql".@implode(",",$sq);
	}
	$q=new mysql();
	$unix=new unix();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "Mysql error:$q->msql_error\n";
	
	
	return;}
	
	
	
	$unix=new unix();
	$awstats=$unix->LOCATE_AWSTATS_BIN();
	$GLOBALS["perl"]=$unix->find_program("perl");
	$GLOBALS["nice"]=EXEC_NICE();
	$awstats_buildstaticpages=$unix->LOCATE_AWSTATS_BUILDSTATICPAGES_BIN();	
	
	$awstats_conf[]="LogFile=$filename";
	$awstats_conf[]="LogType=M";
	$awstats_conf[]="LogFormat=\"%time2 %email %email_r %host %host_r %method %url %code %bytesd\"";
	$awstats_conf[]="LevelForBrowsersDetection=0";
	$awstats_conf[]="LevelForOSDetection=0";
	$awstats_conf[]="LevelForRefererAnalyze=0";
	$awstats_conf[]="LevelForRobotsDetection=0";
	$awstats_conf[]="LevelForWormsDetection=0";
	$awstats_conf[]="LevelForSearchEnginesDetection=0";
	$awstats_conf[]="LevelForFileTypesDetection=0";
	$awstats_conf[]="ShowMenu=1";
	$awstats_conf[]="ShowSummary=HB";
	$awstats_conf[]="ShowMonthStats=HB";
	$awstats_conf[]="ShowDaysOfMonthStats=HB";
	$awstats_conf[]="ShowDaysOfWeekStats=HB";
	$awstats_conf[]="ShowHoursStats=HB";
	$awstats_conf[]="ShowDomainsStats=0";
	$awstats_conf[]="ShowHostsStats=HBL";
	$awstats_conf[]="ShowAuthenticatedUsers=0";
	$awstats_conf[]="ShowRobotsStats=0";
	$awstats_conf[]="ShowEMailSenders=HBML";
	$awstats_conf[]="ShowEMailReceivers=HBML";
	$awstats_conf[]="ShowSessionsStats=0";
	$awstats_conf[]="ShowPagesStats=0";
	$awstats_conf[]="ShowFileTypesStats=0";
	$awstats_conf[]="ShowFileSizesStats=0";
	$awstats_conf[]="ShowBrowsersStats=0";
	$awstats_conf[]="ShowOSStats=0";
	$awstats_conf[]="ShowOriginStats=0";
	$awstats_conf[]="ShowKeyphrasesStats=0";
	$awstats_conf[]="ShowKeywordsStats=0";
	$awstats_conf[]="ShowMiscStats=0";
	$awstats_conf[]="ShowHTTPErrorsStats=0";
	$awstats_conf[]="ShowSMTPErrorsStats=1";
	
	@file_put_contents("/etc/awstats/awstats.$instancename.conf",@implode("\n",$awstats_conf));
	@chmod("/etc/awstats/awstats.$instancename.conf",644);
	$t1=time();
	@mkdir("/var/tmp/awstats/$instancename",0666,true);
	$cmd="{$GLOBALS["nice"]}{$GLOBALS["perl"]} $awstats_buildstaticpages -config=$instancename -update -lang=auto -awstatsprog=$awstats -dir=/var/tmp/awstats/$instancename 2>&1";
	if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
	exec($cmd,$results);
	if($GLOBALS["VERBOSE"]){echo @implode("\n",$results)."\n";}
	awstats_import_sql($instancename,$GLOBALS["ARTICAMETA"]);
	$t2=time();	
	@unlink($filename);
	
}


function awstats_cron(){
	
	if(is_file("/etc/cron.d/sendmail")){@unlink("/etc/cron.d/sendmail");}
	if(is_file("/etc/cron.d/php5")){
		$f[]="MAILTO=\"\"";
		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="09,39 * * * *     root   [ -x /usr/lib/php5/maxlifetime ] && [ -d /var/lib/php5 ] && find /var/lib/php5/ -type f -cmin +$(/usr/lib/php5/maxlifetime) -delete >/dev/null 2>&1";
		$f[]="";
		@file_put_contents("/etc/cron.d/php5", @implode("\n", $f));
		shell_exec("/bin/chmod 640 /etc/cron.d/awstats >/dev/null 2>&1");
	}
	
	
	unset($f);
	if(is_file("/etc/cron.d/awstats")){
		@unlink("/etc/cron.d/awstats");
		if(is_file("/usr/share/awstats/tools/update.sh")){
			$f[]="MAILTO=\"\"";
			$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
			$f[]="*/10 * * * * www-data [ -x /usr/share/awstats/tools/update.sh ] && /usr/share/awstats/tools/update.sh >/dev/null 2>&1";
			$f[]="";
			@file_put_contents("/etc/cron.d/awstats", @implode("\n", $f));
			shell_exec("/bin/chmod 640 /etc/cron.d/awstats >/dev/null 2>&1");
		}	
		
	}
}




