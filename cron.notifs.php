<?php
include_once(dirname(__FILE__) . '/class.cronldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/logs.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/ressources/class.http.pear.inc');
include_once(dirname(__FILE__).'/ressources/class.artica-meta.inc');

if($argv[1]=='--sendmail'){SendMailNotification(null,null,true);die();}
if(preg_match("#--verbose#",implode(" ",$argv))){$_GET["DEBUG"]=true;}
if($argv[1]=='--tests'){send_email_events($argv[2],"system","test");die();}


$unix=new unix();
$pidfile="/etc/artica-postfix/croned.1/cron.notifs.php.pid";
$pid=@file_get_contents($pidfile);
if($unix->process_exists($pid,basename(__FILE__))){
	$time=$unix->PROCCESS_TIME_MIN($pid);
	if($time>50){
		$status[]=@file_get_contents("/proc/$pid/status");
		$status[]=@file_get_contents("/proc/$pid/cmdline");
		$unix->send_email_events("cron.notif has been killed (ghost process)", "$pid: running since $time minutes\n". @implode("\n", $status), "system");
		$kill=$unix->find_program("kill");
		shell_exec("/bin/kill -9 $pid");
	}else{
		writelogs("Already instance executed $pid since $time minutes","MAIN",__FILE__,__LINE__);
		die();
	}
}


$pid=getmypid();
@mkdir("/etc/artica-postfix/croned.1");
file_put_contents($pidfile,$pid);
events("new pid $pid");
echo events("Starting parsing events...");
ParseEvents();
echo events("Starting Launch notifications");
LaunchNotifs();
echo events("Die");
die();


function ParseReboot(){
$path="/etc/artica-postfix/reboot";	
$f=new filesClasses();
$hash=$f->DirListTable($path);
if(!is_array($hash)){return null;}	
events(count($hash) . " reboot file(s) notifications...");

$mysql=new mysql();

	while (list ($num, $file) = each ($hash)){
		$date=date("Y-m-d H:i:s",$file);
		$array=unserialize(@file_get_contents($path.'/'.$file));
		$buffer=$array["buffer"];
		$dmsg=@implode("\n",$array["dmsg"]);			
		$text="Why artica has rebooted ?Detected\n$buffer\n\nDmsg:$dmsg";
		$subject="Reboot report";
        $subject=addslashes($subject);
        $text=addslashes($text);
        $sql="INSERT IGNORE INTO events (zDate,hostname,
        	process,text,context,content,attached_files,recipient,event_id) VALUES(
        	'$date',
        	'$mysql->hostname',
        	'reboot',
        	'$subject',
        	'system','$text','$text','','')";   		
		
		
		 writelogs("New notification: reboot report (". strlen($text)." bytes) $date",__FUNCTION__,__FILE__,__LINE__);
		if(!$mysql->QUERY_SQL($sql,'artica_events')){
			events("Mysql error keep $path/$file;");
			events("Fatal: $mysql->mysql_error",__FUNCTION__,__FILE__,__LINE__);
			continue;
			
		}
		
		@unlink($path.'/'.$file);
	}
}



function ParseEvents(){
ParseReboot();
$path="/var/log/artica-postfix/events";
$f=new filesClasses();
$hash=$f->DirListTable($path);
if(!is_array($hash)){return null;}

$users=new usersMenus();
$sock=new sockets();
$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");

echo date('Y-m-d h:i:s')." " .count($hash) . " file(s) notifications...\n";
events(count($hash) . " file(s) notifications...");

if(count($hash)==0){return;}
 if($ArticaMetaEnabled==1){
 	$meta=new artica_meta();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));	
	$ArticaMetaHostname=$meta->ArticaMetaHostname;
	events("ArticaMetaEnabled: \"$ArticaMetaEnabled\" -> ($ArticaMetaHostname)");
 }

$mysql=new mysql();

	while (list ($num, $file) = each ($hash)){
		
		$text=null;
		$processname=null;
		$date=null;
		$context=null;
		$subject=null;
		$recipient=null;
		$bigtext=@file_get_contents($path.'/'.$file);
		
		echo date('Y-m-d h:i:s')." Parsing $file ". strlen($bigtext)." bytes text\n";
		
		$ini=new Bs_IniHandler();
		if(preg_match("#<text>(.+?)</text>#is",$bigtext,$re)){
			$text=$re[1];
			if(strlen($text)>0){
				$bigtext=str_replace($re[0],'',$bigtext);
				$bigtext=str_replace("'", "`", $bigtext);
			}
		}
		
		if(preg_match("#<attachedfiles>(.+?)</attachedfiles>#is",$bigtext,$re)){
				$files_text=addslashes($re[1]);
		}		
		
		
		$ini->loadString($bigtext);
        $processname=$ini->_params["LOG"]["processname"];
        $date=$ini->_params["LOG"]["date"];
        $context=$ini->_params["LOG"]["context"];
        if($context=="YTowOnt9"){$context="system";}
        $subject=$ini->_params["LOG"]["subject"];
        $recipient=$ini->_params["LOG"]["recipient"];        
        
        if(strlen($text)<2){
        	$text=$ini->_params["LOG"]["text"];
        }
        
        $arrayToSend["context"]=$context;
        $arrayToSend["subject"]=$subject;
        $arrayToSend["text"]=$text;
        $arrayToSend["date"]=$date;
        
		echo date('Y-m-d h:i:s')." Parsing subject $subject ". strlen($text)." bytes text\n";
        
        writelogs("New notification: $subject (". strlen($text)." bytes) $date",__FUNCTION__,__FILE__,__LINE__);
        
        $event_id=time();
        //$text=addslashes($text);
        $text=str_replace("'", "`", $text);
        $subject=str_replace("'", "`", $subject);
        $text=addslashes($text);
        $subject=addslashes($subject);
        if(strlen($text)<5){writelogs("Warning New notification: $subject content seems to be empty ! \"$text\"",__FUNCTION__,__FILE__,__LINE__);}
        $sql="INSERT IGNORE INTO events (zDate,hostname,process,text,context,content,attached_files,recipient,event_id) VALUES(
        	'$date',
        	'$users->hostname',
        	'$processname',
        	'$subject',
        	'$context','$text','$files_text','$recipient','$event_id')";        
        
        if(!$mysql->UseMysql){
            $sql="INSERT IGNORE INTO events (id,zDate,hostname,
        	process,text,context,content,attached_files,recipient,event_id) VALUES(
        	'$event_id','$date',
        	'$users->hostname',
        	'$processname',
        	'$subject',
        	'$context','$text','$files_text','$recipient','$event_id')"; 	
        }
        	
       events(date('Y-m-d h:i:s')." run mysql query -> $subject"); 
		
		if(!$mysql->QUERY_SQL($sql,'artica_events')){
			events("Mysql error keep $path/$file;");
			events("Fatal: $mysql->mysql_error",__FUNCTION__,__FILE__,__LINE__);
			
			if(preg_match("#Access denied for user.+?using password:#",$mysql->mysql_error)){
				events("Access denied for user password: $mysql->mysql_server@$mysql->mysql_admin:$mysql->mysql_password detected");
				if(($mysql->mysql_server=="127.0.0.1") OR ($mysql->mysql_server=="localhost")){
					$ldap=new clladp();
					$unix=new unix();
					$ldap->ldap_password=$unix->shellEscapeChars($ldap->ldap_password);
					writelogs("Try to change the mysql password: /usr/share/artica-postfix/bin/artica-install --change-mysqlroot --inline root \"secret\"",__FUNCTION__,__FILE__,__LINE__);
					exec("/usr/share/artica-postfix/bin/artica-install --change-mysqlroot --inline root \"secret\" 2>&1",$chroot);
					writelogs("Chaning password=".@implode("\n",$chroot),__FUNCTION__,__FILE__,__LINE__);
					die();
				}
			}			
			
			
			if(preg_match("#Unknown column#",$mysql->mysql_error)){events("->BuildTables()");$mysql->BuildTables();}
			if(preg_match("#Unknown database#",$mysql->mysql_error)){events("->BuildTables()");$mysql->BuildTables();}
			if(preg_match("#connect to local MySQL server through socket#",$mysql->mysql_error)){shell_exec("/etc/init.d/artica-postfix start mysql &");}
			writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
			break;
			}
			
			unlink($path.'/'.$file);
        
        if($ArticaMetaEnabled==1){
	        	$pidfile="/etc/artica-postfix/pids/exec.artica.meta.php.SendStatus.pid";
				$sock=new sockets();
				$ArticaMetaPoolTimeMin=$sock->GET_INFO("ArticaMetaPoolTimeMin");
				if(!is_numeric($ArticaMetaPoolTimeMin)){$ArticaMetaPoolTimeMin=15;}
				if($ArticaMetaPoolTimeMin<2){$ArticaMetaPoolTimeMin=15;}
				$minutes=file_time_min($pidfile);
				if($minutes<round(($ArticaMetaPoolTimeMin/2.5))){
					$meta->events(basename($pidfile).":{$minutes}<".round(($ArticaMetaPoolTimeMin/2.5))."Mn, aborting",__FUNCTION__,__FILE__,__LINE__);
					return;
				}
        	
	        	$http=new httpget();
	        	$meta->events("Send notification \"{$arrayToSend["subject"]}\" to Artica Meta",__FUNCTION__,__FILE__,__LINE__);
				$metaconsole=$http->send("$ArticaMetaHostname/lic.status.notifs.php","post",array(
					"DATAS"=>$datasToSend,
					"NOTIF"=>base64_encode(serialize($arrayToSend))
					));
				
				events("META CONSOLE: $metaconsole aborting notifications");
				if($metaconsole=="FAILED_CONNECT"){
					$meta->events("Result:\"$metaconsole\"",__FUNCTION__,__FILE__,__LINE__);
					return;
				}
			
				if(!is_file("/etc/artica-postfix/artica-meta.tasks")){	
		        	if(preg_match("#<TASKS>(.+?)</TASKS>#is",$metaconsole,$re)){
						$meta->events("Save tasks to /etc/artica-postfix/artica-meta.tasks",__FUNCTION__,__FILE__,__LINE__);
						@file_put_contents("/etc/artica-postfix/artica-meta.tasks",$re[1]);
						$cmd=LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.tasks.php >/dev/null 2>&1 &";
						$meta->events("TASKS ->$cmd",__FUNCTION__,__FILE__,__LINE__);
						shell_exec($cmd);
		        	}
				}
		
        }
		
			
			
			
        
        
        
        
        $text=addslashes($text);
        $context=addslashes($context);
        $subject=addslashes($subject);
        
        
        
        
	}

	if(count($hash)>0){events(count($hash). " events queue parsed...");}
	
	if($ArticaMetaEnabled==1){
		if(is_file("/etc/artica-postfix/artica-meta.tasks")){
			$NICE=EXEC_NICE();
			shell_exec($NICE.LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.tasks.php &");
		}
	}


}

function LaunchNotifs(){
$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
$sa_learn=$ini->_params["SMTP"]["sa-learn"];
$system=$ini->_params["SMTP"]["system"];
$update=$ini->_params["SMTP"]["update"];	
$q=new mysql();


$sql="SELECT COUNT(*) as tcount FROM events";
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));

events("Mysql store {$ligne["tcount"]} events");



if($ligne["tcount"]>4000){
	$sql="DELETE FROM events ORDER BY zDate LIMIT 1000";
	events("Mysql Delete 1000 old events");
	$q->QUERY_SQL($sql,"artica_events");
}


$sql="SELECT * FROM `events` WHERE sended=0 ORDER BY zDate DESC LIMIT 0,100";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			
	
			$attached_files=unserialize(base64_decode($ligne["attached_files"]));
			$context=$ligne["context"];
			$ligne["content"]=str_replace('[br]',"\n",$ligne["content"]);
			events("New event: {$ligne["text"]}, context=$context");
			if($ini->_params["SMTP"][$context]==1){
				$ligne["content"]="{$ligne["zDate"]} :{$ligne["process"]}: {$ligne["text"]}\n\n-----------------------------------------------------\n{$ligne["content"]}\n";
				events("Notify {$ligne["text"]} -> SendMailNotification()");
				SendMailNotification($ligne["content"],"[$context]: {$ligne["text"]}",false,$attached_files,$ligne["recipient"]);
			}else{
				events("$context is not enabled, notifications disabled");
			}
			$sql="UPDATE events SET sended=1 WHERE ID={$ligne["ID"]}";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				events("Mysql error $sql");
			}
	
	
	
	}

}

function events($text){
		$pid=getmypid();
		$filename=basename(__FILE__);
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/notifications.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="$date {$filename}[{$pid}] $text\n";
		if($_GET["DEBUG"]){echo $line;}
		@fwrite($f,$line);
		@fclose($f);
	}



//sa-learn
//system
//update
	
	

?>