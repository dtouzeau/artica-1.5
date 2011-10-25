<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}


if($argv[1]=="--schedules-mldonkey"){schedules_mldonkey();die();}
if($argv[1]=="--postmaster-cron"){postmaster_cron();die();}
if($argv[1]=="--artica-schedule"){artica_schedule();die();}
if($argv[1]=="--artica-reboot-schedule"){artica_reboot_schedule();die();}
if($argv[1]=="--reboot-task"){artica_reboot_task();die();}
if($argv[1]=="--squid-recategorize-task"){squid_recategorize_task();die();}

function artica_reboot_schedule(){
		$targetfile="/etc/cron.d/RebootScheduler";
		@unlink($targetfile);
		$sock=new sockets();
		$unix=new unix();
		$AutoRebootSchedule=$sock->GET_INFO("AutoRebootSchedule");
		if(!is_numeric($AutoRebootSchedule)){$AutoRebootSchedule=0;}
		if($GLOBALS["VERBOSE"]){echo "AutoRebootSchedule = $AutoRebootSchedule\n";}
		if($AutoRebootSchedule==0){return;}
		$AutoRebootScheduleText=$sock->GET_INFO("AutoRebootScheduleText");
		if($GLOBALS["VERBOSE"]){echo "AutoRebootScheduleText = $AutoRebootScheduleText\n";}
		if($AutoRebootScheduleText==null){$sock->SET_INFO("AutoRebootSchedule", 0);return;}
		$php5=$unix->LOCATE_PHP5_BIN();
 		
 		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="MAILTO=\"\"";
		$f[]="$AutoRebootScheduleText  root $php5 ".__FILE__." --reboot-task >/dev/null 2>&1";
		$f[]="";	
		if($GLOBALS["VERBOSE"]){echo " -> $targetfile\n";}
		@file_put_contents($targetfile,implode("\n",$f));
		if(!is_file($targetfile)){if($GLOBALS["VERBOSE"]){echo " -> $targetfile No such file\n";}}
		
		$chmod=$unix->find_program("chmod");
		shell_exec("$chmod 640 $targetfile");
		unset($f);	
}

function squid_recategorize_task(){
	$sock=new sockets();
	$unix=new unix();	
	$targetfile="/etc/cron.d/SquidStatsRecategorizeScheduler";
	@unlink($targetfile);
	$RecategorizeProxyStats=$sock->GET_INFO("RecategorizeProxyStats");
	$RecategorizeCronTask=$sock->GET_INFO("RecategorizeCronTask");
	if(!is_numeric($RecategorizeProxyStats)){$RecategorizeProxyStats=1;}
	if($GLOBALS["VERBOSE"]){echo "RecategorizeCronTask = $RecategorizeCronTask -> $RecategorizeProxyStats\n";}
	if($RecategorizeProxyStats==0){return;}	
	if($RecategorizeCronTask==null){$RecategorizeCronTask="0 5 * * *";}	
	$php5=$unix->LOCATE_PHP5_BIN();
 	$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
	$f[]="MAILTO=\"\"";
	$f[]="$RecategorizeCronTask  root $php5 /usr/share/artica-postfix/exec.squid.stats.php --re-categorize >/dev/null 2>&1";
	$f[]="";	
	if($GLOBALS["VERBOSE"]){echo " -> $targetfile\n";}
	@file_put_contents($targetfile,implode("\n",$f));
	if(!is_file($targetfile)){if($GLOBALS["VERBOSE"]){echo " -> $targetfile No such file\n";}}
	$chmod=$unix->find_program("chmod");
	shell_exec("$chmod 640 $targetfile");
	unset($f);		
	
	
}

function artica_reboot_task(){
		$sock=new sockets();
		$unix=new unix();
		$AutoRebootSchedule=$sock->GET_INFO("AutoRebootSchedule");
		if(!is_numeric($AutoRebootSchedule)){$AutoRebootSchedule=0;}
		if($GLOBALS["VERBOSE"]){echo "AutoRebootSchedule = $AutoRebootSchedule\n";}
		if($AutoRebootSchedule==0){die();}	
		$reboot_bin=$unix->find_program("reboot");
		if(!is_file($reboot_bin)){$unix->send_email_events("Unable to reboot computer !", "reboot, no such binary", "system");die();}
		$unix->send_email_events("Reboot computer performed", "This computer has been rebooted with the reboot scheduled task ($reboot_bin) set in artica performances section", "system");
		shell_exec($reboot_bin);
}


function artica_schedule(){
		@unlink("/etc/cron.d/arscheduler");
		$sock=new sockets();
		$unix=new unix();
		$EnableScheduleUpdates=$sock->GET_INFO("EnableScheduleUpdates");
		if(!is_numeric($EnableScheduleUpdates)){$EnableScheduleUpdates=0;}
		if($GLOBALS["VERBOSE"]){echo "EnableScheduleUpdates = $EnableScheduleUpdates\n";}
		if($EnableScheduleUpdates==0){return;}
		$ArticaScheduleUpdates=$sock->GET_INFO("ArticaScheduleUpdates");
		if($GLOBALS["VERBOSE"]){echo "EnableScheduleUpdates = $ArticaScheduleUpdates\n";}
		if($ArticaScheduleUpdates==null){$sock->SET_INFO("EnableScheduleUpdates", 0);return;}
		$targetfile="/etc/cron.d/arscheduler";
 		
 		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="MAILTO=\"\"";
		$f[]="$ArticaScheduleUpdates  root /usr/share/artica-postfix/bin/artica-update --bycron";
		$f[]="";	
		if($GLOBALS["VERBOSE"]){echo " -> $targetfile\n";}
		@file_put_contents($targetfile,implode("\n",$f));
		if(!is_file($targetfile)){if($GLOBALS["VERBOSE"]){echo " -> $targetfile No such file\n";}}
		
		$chmod=$unix->find_program("chmod");
		shell_exec("$chmod 640 /etc/cron.d/arscheduler");
		unset($f);	
}


function schedules_mldonkey(){
	
	@unlink("/etc/artica-postfix/mldonkey.tasks");
	$sql="SELECT * FROM mldonkey ORDER BY schedule_time DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");

	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$params=unserialize(base64_decode($ligne["parameters"]));
		$params["hours"];
		$params["minutes"];
		if($params["minutes"]==null){$params["minutes"]="59";}
		if($params["hours"]==null){$params["hours"]="0";}
		$f[]="{$params["minutes"]} {$params["hours"]} * * * ".LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.mldonkey.php --settings {$ligne["ID"]}";
	}
	
	if(is_array($f)){
		@file_put_contents("/etc/artica-postfix/mldonkey.tasks",@implode("\n",$f));	
	}
}


function postmaster_cron(){
	
	$sock=new sockets();
	$PostfixPostmaster=$sock->GET_INFO("PostfixPostmaster");
	$ar[]="/etc/cron.d";
	
	while (list ($index, $directory) = each ($ar) ){
	foreach (glob("$directory/*") as $filename) {
		postmaster_cron_check($PostfixPostmaster,$filename);
		}
	}

}

function postmaster_cron_check($email,$filename){
	$dirname=dirname($filename);
	$f=explode("\n",@file_get_contents($filename));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#MAILTO=(.*)#",$line,$re)){
			$orgmail=trim($re[1]);
			$orgmail=str_replace('"',"",$orgmail);
			$orgmail=str_replace("'","",$orgmail);
			if($orgmail<>$email){
				if($GLOBALS["VERBOSE"]){echo "$line Found \"$orgmail\" <> \"$email\"\n";}
				$f[$index]="MAILTO=\"$email\"";
				@file_put_contents($filename,@implode("\n",$f));
				echo "Starting......: Artica patching $dirname/".basename($filename)." $email\n";
				return;
			}
			return;
		}else{
			if($GLOBALS["VERBOSE"]){echo "$line no match #MAILTO=(.*)#\n";}
		}
		
	}
	$final="MAILTO=\"$email\"\n".@implode("\n",$f);
	@file_put_contents($filename,$final);
	echo "Starting......: Artica patching $dirname/".basename($filename)." $email\n";
	
	
}

?>