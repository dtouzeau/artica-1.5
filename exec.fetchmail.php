<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/ressources/class.fetchmail.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.maincf.multi.inc");
$GLOBALS["SINGLE_DEBUG"]=false;
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if($argv[1]=="--multi-start"){BuildRules();die();}
if($argv[1]=="--single-debug"){SingleDebug($argv[2]);die();}

BuildRules();

function SingleDebugEvents($subject,$text,$ID){
	$q=new mysql();
	$pid=getmypid();
	$CurrentDate=date('Y-m-d H:i:s');
	if($GLOBALS["VERBOSE"]){echo "$CurrentDate $subject\n$text\n\n";}
	
	
	$text=addslashes($text);
	$subject=addslashes($subject);
	$sql="INSERT INTO fetchmail_debug_execute (subject,account_id,zDate,events,PID) 
	VALUES('$subject','$ID','$CurrentDate','$text','$pid')";
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error."\n";}
	return;	
}


function SingleDebug($ID){
	$q=new mysql();
	$q->BuildTables();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
	$unix=new unix();
	$pid=$unix->get_pid_from_file($pidfile);
	$fetchmail=$unix->find_program("fetchmail");
	if($unix->process_exists($pid)){
		SingleDebugEvents("Task aborted","This task is aborted, it already running PID $pid, please wait before executing a new task",$ID);
		return;
	}
	@file_put_contents($pidfile, getmypid());
	SingleDebugEvents("Task executed","Starting rule number $ID\nThis task is executed please wait before executing a new task",$ID);
	
	
	$fetch=new fetchmail();
	$output=array();
	
		$fetch=new fetchmail();
		$l[]="set logfile /var/log/fetchmail-rule-$ID.log";
		$l[]="set daemon $fetch->FetchmailPoolingTime";
		$l[]="set postmaster \"$fetch->FetchmailDaemonPostmaster\"";
		$l[]="set idfile \"/var/log/fetchmail.$ID.id\"";	
		$l[]="";	
	$GLOBALS["SINGLE_DEBUG"]=true;
	BuildRules();
	$pattern=$GLOBALS["FETCHMAIL_RULES_ID"][$ID];
	$l[]=$pattern;	
	@file_put_contents("/tmp/fetchmailrc.$ID",@implode("\n", $l));
	shell_exec("/bin/chmod 600 /tmp/fetchmailrc.$ID");
	$cmd="$fetchmail -v -N -f /tmp/fetchmailrc.$ID --pidfile /tmp/fetcmailrc.$ID.pid 2>&1";
	exec($cmd,$output);
	SingleDebugEvents("Task finish with ". count($output)." event(s)",@implode("\n", $output),$ID);
	
}


function BuildRules(){
		$unix=new unix();
		$sock=new sockets();
		$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
		$fetch=new fetchmail();
		$l[]="set logfile /var/log/fetchmail.log";
		$l[]="set daemon $fetch->FetchmailPoolingTime";
		$l[]="set postmaster \"$fetch->FetchmailDaemonPostmaster\"";
		$l[]="set idfile \"/var/log/fetchmail.id\"";	
		$l[]="";

		$sql="SELECT * FROM fetchmail_rules WHERE enabled=1";
		$q=new mysql();
		
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "Starting......: fetchmail saving configuration file FAILED\n";
			return false;
		}
		$array=array();
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ID=$ligne["ID"];
			$ligne["poll"]=trim($ligne["poll"]);
			if($ligne["poll"]==null){continue;}
			if($ligne["proto"]==null){continue;}
			if($ligne["uid"]==null){continue;}
			$user=new user($ligne["uid"]);
			if(trim($user->mail)==null){
				echo "Starting......: fetchmail uid has no mail !!!, skip it..\n";
				$unix->send_email_events("Fetchmail rule for {$ligne["uid"]}/{$ligne["poll"]} has been skipped", "cannot read email address from LDAP", "mailbox");
				continue;
			}
			$ligne["is"]=$user->mail;
			$smtphost=null;
			$sslfingerprint=null;
			$fetchall=null;
			$timeout=null;
			$port=null;
			$aka=null;
			$folder=null;
			$tracepolls=null;
			$interval=null;
			$keep=null;
			$fetchall=null;
			$sslcertck=null;
			
			if($ligne["proto"]=="httpp"){$ligne["proto"]="pop3";}
			
			if(trim($ligne["port"])>0){$port="port {$ligne["port"]}";}
			if(trim($ligne["aka"])<>null){$aka="\n\taka {$ligne["aka"]}";}
			if($ligne["ssl"]==1){$ssl="\n\tssl\n\tsslproto ''";}	
			if($ligne["timeout"]>0){$timeout="\n\ttimeout {$ligne["timeout"]}";}
			if($ligne["folder"]<>null){$folder="\n\tfolder {$ligne["folder"]}";}				
			if($ligne["tracepolls"]==1){$tracepolls="\n\ttracepolls";}
			if($ligne["interval"]>0){$interval="\n\tinterval {$ligne["interval"]}";}		
			if($ligne["keep"]==1){$keep="\n\tkeep ";}
			if($ligne["nokeep"]==1){$keep="\n\tnokeep";}
			if($ligne["multidrop"]==1){$ligne["is"]="*";}
			if($ligne["fetchall"]==1){$fetchall="\n\tfetchall";}
			if(strlen(trim($ligne["sslfingerprint"]))>10){$sslfingerprint="\n\tsslfingerprint '{$ligne["sslfingerprint"]}'";}
			if($ligne["sslcertck"]==1){$sslcertck="\n\tsslcertck";}					
			
			
			if($EnablePostfixMultiInstance==1){
				if($GLOBALS["DEBUG"]){echo "multiple instances::poll={$ligne["poll"]} smtp_host={$ligne["smtp_host"]}\n";}
				if(strlen(trim($ligne["smtp_host"]))==0){continue;}
				$smtphost="\n\tsmtphost ".multi_get_smtp_ip($ligne["smtp_host"]);
			}
			
			
			if(trim($ssl)==null){$ssl="\n\tsslproto ssl23\n\tno ssl";}
			$pattern="poll {$ligne["poll"]}$tracepolls\n\tproto {$ligne["proto"]} $port$interval$timeout\n\tuser \"{$ligne["user"]}\"\n\tpass {$ligne["pass"]}\n\tis {$ligne["is"]}$aka$folder$ssl$fetchall$keep$multidrop$sslfingerprint$sslcertck$smtphost\n\n";
			if($GLOBALS["DEBUG"]){echo "$pattern\n";}

			$multi_smtp[$ligne["smtp_host"]][]=$pattern;
			$l[]=$pattern;
			$GLOBALS["FETCHMAIL_RULES_ID"][$ID]=$pattern;
		}
		
		if($GLOBALS["SINGLE_DEBUG"]){
			echo "Starting......: fetchmail single-debug, aborting nex step\n";
			return;
		}
		
		if($EnablePostfixMultiInstance==1){
			echo "Starting......: fetchmail postfix multiple instances enabled (".count($multi_smtp).") hostnames\n";
			@unlink("/etc/artica-postfix/fetchmail.schedules");
			
			if(is_array($multi_smtp)){
				if($GLOBALS["DEBUG"]){print_r($multi_smtp);}
				while (list ($hostname, $rules) = each ($multi_smtp)){
					echo "Starting......: fetchmail $hostname save rules...\n";
					@file_put_contents("/etc/postfix-$hostname/fetchmail.rc",@implode("\n",$rules));
					@chmod("/etc/postfix-$hostname/fetchmail.rc",0600);
					$schedule[]=multi_build_schedule($hostname);
					if(!is_fetchmailset($hostname)){
						$restart=true;
					}else{
						echo "Starting......: fetchmail $hostname already scheduled...\n";
					}
				}
				if($restart){
					@file_put_contents("/etc/artica-postfix/fetchmail.schedules",@implode("\n",$schedule));
					system("/etc/init.d/artica-postfix restart fcron");
				}
			}
		return;
		}
		
		
		
		if(is_array($l)){$conf=implode("\n",$l);}else{$conf=null;}
		@file_put_contents("/etc/fetchmailrc",$conf);
		shell_exec("/bin/chmod 600 /etc/fetchmailrc");
		echo "Starting......: fetchmail saving configuration file done\n";
			
}

function is_fetchmailset($hostname){
	
	if(!is_array($GLOBALS["crontab"])){
		exec("/usr/share/artica-postfix/bin/fcrontab -c /etc/artica-cron/artica-cron.conf  -l -u root 2>&1",$results);
		$GLOBALS["crontab"]=$results;
	}
	if($GLOBALS["DEBUG"]){echo __FUNCTION__.":: $hostname ". count($GLOBALS["crontab"])." lines\n";}
	$hostname=str_replace(".","\.",$hostname);
	while (list ($i, $line) = each ($GLOBALS["crontab"])){
		if(preg_match("#bin\/fetchmail.+?fetchmailrc\s+\/etc\/postfix-$hostname#",$line)){
			return true;
		}else{
		if($GLOBALS["DEBUG"]){echo __FUNCTION__.":: $line NO MATCH #bin\/fetchmail.+?fetchmailrc \/etc\/$hostname#\n";}
		}
		
	}
	return false;
	
}


function multi_get_smtp_ip($hostname){
	if($GLOBALS["SMTP_HOSTS_IP_FETCHMAIL"][$hostname]<>null){return $GLOBALS["SMTP_HOSTS_IP_FETCHMAIL"][$hostname];}
	$main=new maincf_multi($hostname);
	$GLOBALS["SMTP_HOSTS_IP_FETCHMAIL"][$hostname]=$main->ip_addr;
	echo "Starting......: fetchmail $hostname ($main->ip_addr)\n";
	return $main->ip_addr;
	
}

function multi_build_schedule($hostname){
	$unix=new unix();
	$fetchmail=$unix->find_program("fetchmail");
	if($fetchmail==null){return null;}	
	$main=new maincf_multi($hostname);
	$array=unserialize(base64_decode($main->GET_BIGDATA("PostfixMultiFetchMail")));	
	if($array[$hostname]["enabled"]<>1){return null;}
	if($array[$hostname]["schedule"]==null){return null;}
	if($array[$hostname]["schedule"]<2){return null;}
	echo "Starting......: fetchmail $hostname scheduling each {$array[$hostname]["schedule"]}mn\n";
	return "{$array[$hostname]["schedule"]} $fetchmail --nodetach --fetchmailrc /etc/postfix-$hostname/fetchmail.rc >>/var/log/fetchmail.log";
	
	
}




?>