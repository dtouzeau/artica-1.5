<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
//server-syncronize-64.png
$GLOBALS["FORCE"]=false;
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=="--start-process"){startprocess($argv[2],$argv[3]);exit;}

$unix=new unix();
$GLOBALS["CLASS_UNIX"]=$unix;
$pidfile="/etc/artica-postfix/".basename(__FILE__)."pid";
$currentpid=trim(@file_get_contents($pidefile));
if($unix->process_exists($currentpid)){die();}

@file_put_contents($pidfile,getmypid());

if($argv[1]=="--bandwith"){bandwith();exit;}
if($argv[1]=="--loadavg"){loadavg();exit;}
if($argv[1]=="--mem"){loadmem();exit;}
if($argv[1]=="--cpu"){loadcpu();exit;}
if($argv[1]=="--queues"){ParseLoadQeues();exit;}
if($argv[1]=="--zombies"){zombies();exit;}



checkProcess1();

function startprocess($APP_NAME,$cmd){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__)."/pids/".__FUNCTION__.".".$APP_NAME.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){writelogs("Already process $pid exists",__FUNCTION__,__FILE__,__LINE__);return;}
	@file_put_contents($pidfile, getmypid());
	exec("/etc/init.d/artica-postfix start $cmd 2>&1",$results);
	if($GLOBALS["VERBOSE"]){echo "\n".@implode("\n",$results)."\n";return;}
	$unix->send_email_events("$APP_NAME stopped","Artica tried to start it:\n".@implode("\n",$results),"system");
	
}
function bandwith(){
	$sock=new sockets();
	$EnableBandwithCalculation=$sock->GET_INFO("EnableBandwithCalculation");
	if(!is_numeric($EnableBandwithCalculation)){$EnableBandwithCalculation=0;}
	if($EnableBandwithCalculation==0){return;}	
	
	$file="/usr/share/artica-postfix/ressources/logs/web/bandwith-mon.txt";
	$ftime=file_time_min($file);
	events("$ftime ". basename($file),__FUNCTION__,__LINE__);
	if($ftime<10){return;}
	if($GLOBALS["VERBOSE"]){echo "\n***\n/usr/share/artica-postfix/bin/bandwith.pl\n***\n";}
	exec("/usr/share/artica-postfix/bin/bandwith.pl 2>&1",$results);
	$text=@implode("",$results);
	if(!preg_match("#([0-9\.,]+)#",$text,$re)){
		events("$text unable to preg_match",__FUNCTION__,__LINE__);
		return;
	}
	
		$re[1]=str_replace(",",".",$re[1]);
		$mbs=round($re[1],0);
		events("$mbs MB/S bandwith",__FUNCTION__,__LINE__);
		$sql="INSERT INTO bandwith_stats (`zDate`,`bandwith`) VALUES(NOW(),'$mbs');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){events("$q->mysql_error \"$sql\"",__FUNCTION__,__LINE__);}
		@unlink($file);
		@file_put_contents($file,$mbs);
		@chmod($file,0770);
	}

function events($text,$function=null,$line=0){
		$filename=basename(__FILE__);
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $function:: $text (L.$line)","/usr/share/artica-postfix/ressources/logs/launch.watchdog.task");	
		}	
function checkProcess1(){
	
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("bin/process1");
	if($pid<5){return null;}
	$process1=$unix->PROCCESS_TIME_MIN($pid);
	$mem=$unix->PROCESS_MEMORY($pid);
	Myevents("process1: $pid ($process1 mn) memory:$mem Mb",__FUNCTION__);
	
	if($mem>30){
		@copy("/var/log/artica-postfix/process1.debug","/var/log/artica-postfix/process1.killed".time().".debug");
		system("/bin/kill -9 $pid");
		$unix->send_email_events(
		"artica process1 (process1) Killed",
		"Process1 use too much memory $mem MB","watchdog"); 		
	}
	
	if($process1>2){
		@copy("/var/log/artica-postfix/process1.debug","/var/log/artica-postfix/process1.killed".time().".debug");
		system("/bin/kill -9 $pid");
		$unix->send_email_events(
		"artica process1 (process1) Killed",
		"Process1 run since $process1 Pid: $pid and exceed 2 minutes live","watchdog"); 
	}

}

function Myevents($text=null,$function=null){
			$pid=getmypid();
			$file="/var/log/artica-postfix/watchdog.debug";
			@mkdir(dirname($file));
		    $logFile=$file;
		 
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>100000){unlink($logFile);}
   		}
		$date=date('Y-m-d H:i:s'). " [$pid]: ";
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date $function:: $text\n");
		@fclose($f);
}


function ParseLoadQeues(){
	$unix=new unix();
	foreach (glob("/etc/artica-postfix/loadavg.queue/*.queue") as $filename) {
		$filebase=basename($filename);
		if($GLOBALS["VERBOSE"]){echo "parse $filename\n";}
		
		if(preg_match("#^([0-9]+)\.([0-9]+)\.queue$#",$filebase,$re)){$filebase="{$re[1]}.{$re[2]}.0.queue";}
		
		
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.queue$#",$filebase,$re)){
			if(system_is_overloaded()){
				$unix->events(basename(__FILE__).": ParseLoadQeues() system is overloaded aborting for $filename");
				return;
			}
			
			if(is_file("$filename.lsof")){
				$lsofTEXT=ParseLsof("$filename.lsof");
				@unlink("$filename.lsof");
			}else{
				if($GLOBALS["VERBOSE"]){echo "$filename.lsof no such file\n";}
			}
			
			$time=date("Y-m-d H:i:s",$re[1]);
			$load="{$re[2]}.{$re[3]}";
			if($GLOBALS["VERBOSE"]){echo "$time: $load\n";}
			$datas=loadavg_table($filename,$lsof);
			$unix->send_email_events("System Load - $load - exceed rule (processes)",$datas,"system",$time);
			if(strlen($lsofTEXT)>50){
				$unix->send_email_events("System Load - $load - exceed rule (opened files)",$lsofTEXT,"system",$time);
			}
			@unlink($filename);
		}else{
			echo "$filebase did not match ([0-9]+)\.([0-9]+)\.([0-9]+)\.queue\n";
		}
		
		
	}
	
	
}

function ParseLsof($filename){
	$results=@explode("\n",@file_get_contents($filename));
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?)\s+[0-9]+#",$ligne,$re)){
			if(!isset($array[$re[1]])){$array[$re[1]]=0;}
			$array[$re[1]]=$array[$re[1]]+1;
		}
	
	}
$htm[]="<html><head></head><body>";
$htm[]="<table style='width:100%'>";
	$htm[]="<tr>";
	$htm[]="<th>Process</th>";
	$htm[]="<th>Files NB</th>";
	$htm[]="</tr>";

while (list ($prc, $count) = each ($array) ){
	$htm[]="<tr>";
	$htm[]="<td><strong>$prc</strong></td>";
	$htm[]="<td><strong>$count</strong></td>";
	$htm[]="</tr>";
}	
	$htm[]="</table></body></html>";
	if($GLOBALS["VERBOSE"]){echo "$filename ". count($htm)." rows\n";}
	return @implode("\n",$htm);
}


function loadcpu(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__)."/pids/".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){writelogs("Already process $pid exists",__FUNCTION__,__FILE__,__LINE__);return;}		
	@file_put_contents($pidfile, getmypid());
	
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	if(file_time_min($timefile)<15){return null;}
	@unlink($timefile);
	@file_put_contents($timefile,"#");	
	$datas=loadavg_table();
	if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes body text\n";}
	$unix->send_email_events("System CPU exceed rule",$datas,"system");
	checkProcess1();
}
function loadmem(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__)."/pids/".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){writelogs("Already process $pid exists",__FUNCTION__,__FILE__,__LINE__);return;}	
	@file_put_contents($pidfile, getmypid());
		
	include_once("ressources/class.os.system.tools.inc");
	$unix=new unix();
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	if(file_time_min($timefile)<15){return null;}
	@unlink($timefile);
	@file_put_contents($timefile,"#");	
	$sys=new os_system();
	$mem=$sys->realMemory();
	
	$pourc=$mem["ram"]["percent"];
	$ram_used=$mem["ram"]["used"];
	$ram_total=$mem["ram"]["total"];	
	
	$datas=loadavg_table();
	if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes body text\n";}	
	$unix->send_email_events("System Memory $pourc% used exceed rule",$datas,"system");
	checkProcess1();
}


function loadavg(){
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__)."/pids/".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){writelogs("Already process $pid exists",__FUNCTION__,__FILE__,__LINE__);return;}	
	@file_put_contents($pidfile, getmypid());
	
	@mkdir("/etc/artica-postfix/croned.1",0666,true);
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=trim(@file_get_contents($pidfile));
	if($unix->process_exists($pid)){die();}
	$array_load=sys_getloadavg();
	$pid=getmypid();
	@file_put_contents($pidfile,$pid);
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	$timeMin=file_time_min($timefile);
	if(!$GLOBALS["FORCE"]){if($timeMin<5){
		writelogs("{$timeMin}Mn, aborting",__FUNCTION__,__FILE__,__LINE__);return null;}}
	@unlink($timefile);
	@file_put_contents($timefile,"#");	
	
	
	
	$ps=$unix->find_program("ps");	
	$lsof=$unix->find_program("lsof");	
	mkdir("/etc/artica-postfix/loadavg.queue",0666,true);
	$internal_load=$array_load[0];
	$time=time();			
	shell_exec("$ps -aux >/etc/artica-postfix/loadavg.queue/$time.$internal_load.queue 2>&1");
	shell_exec("$lsof -r 0 >/etc/artica-postfix/loadavg.queue/$time.$internal_load.queue.lsof 2>&1");

}

function loadavg_old(){
	
	$unix=new unix();
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__)."/pids/".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){writelogs("Already process $pid exists",__FUNCTION__,__FILE__,__LINE__);return;}	

	
	@mkdir("/etc/artica-postfix/croned.1",0666,true);
	$unix=new unix();
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=trim(@file_get_contents($pidfile));
	if($unix->process_exists($pid)){die();}

	$pid=getmypid();
	@file_put_contents($pidfile,$pid);
	
	$timefile="/etc/artica-postfix/croned.1/".basename(__FILE__).__FUNCTION__;
	if(file_time_min($timefile)<15){return null;}
	@unlink($timefile);
	@file_put_contents($timefile,"#");
	
	
	$array_load=sys_getloadavg();
	$internal_load=$array_load[0];		
	$datas=loadavg_table();
	if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes body text\n";}	
	$unix->send_email_events("System Load - $internal_load - exceed rule ",$datas,"system");
	checkProcess1();
}

function loadavg_table($filepath=null,$lsof=null){
	$array=array();
	if($filepath==null){
		$unix=new unix();
		$ps=$unix->find_program("ps");
		exec("$ps -aux",$results);
	}else{
		$results=explode("\n",@file_get_contents($filepath));
	}
	while (list ($index, $line) = each ($results) ){
	usleep(2000);
	if(!preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9\.]+)\s+.+?\s+.+?\s+([0-9\:]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
			if(preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9]+)\s+.+?\s+.+?\s+([a-zA-Z0-9]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
			$user=$re[1];
			$pid=$re[2];
			$pourcCPU=$re[3];
			$purcMEM=$re[4];
			$VSZ=$re[5];
			$RSS=$re[6];
			$START=$re[7];
			$TIME=$re[8];
			$cmd=$re[9];	
			$key="$pourcCPU$purcMEM";
			$key=str_replace(".",'',$key);
			
	$array[$key][]=array(
			"PID"=>$pid,
			"CPU"=>$pourcCPU,
			"MEM"=>$purcMEM,
			"START"=>$START,
			"TIME"=>$TIME,
			"CMD"=>$cmd
		);			
			
			continue;		
			
		}		
		
		
		continue;}	
	$user=$re[1];
	$pid=$re[2];
	$pourcCPU=$re[3];
	$purcMEM=$re[4];
	$VSZ=$re[5];
	$RSS=$re[6];
	$START=$re[7];
	$TIME=$re[8];
	$cmd=$re[9];
	
	$pourcCPU=str_replace("0.0","0",$pourcCPU);
	$purcMEM=str_replace("0.0","0",$purcMEM);
	
	$key="$pourcCPU$purcMEM";
	$key=str_replace(".",'',$key);
	
	$array[$key][]=array(
			"PID"=>$pid,
			"CPU"=>$pourcCPU,
			"MEM"=>$purcMEM,
			"START"=>$START,
			"TIME"=>$TIME,
			"CMD"=>$cmd
		);
	
	
	
	
		
	}
	
	if(count($array)<5){return @file_get_contents($filepath);}
	
	krsort($array);
	$htm[]="<html><head></head><body>";
	$htm[]="<table style='width:100%'>";
	$htm[]="<tr>";
	$htm[]="<th>PID</th>";
	$htm[]="<th>CPU</th>";
	$htm[]="<th>MEM</th>";
	$htm[]="<th>START</th>";
	$htm[]="<th>TIME</th>";
	$htm[]="<th>CMD</th>";
	$htm[]="</tr>";
	while (list ($index, $line) = each ($array) ){
		usleep(200000);
		while (list ($a, $barray) = each ($line) ){
			$htm[]="<tr>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["PID"]}</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["CPU"]}%</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["MEM"]}%</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["START"]}</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'>{$barray["TIME"]}</td>";
			$htm[]="<td style='font-size:10px;font-weight:bold'><code>{$barray["CMD"]}</code></td>";
			$htm[]="</tr>";
		}
	}
	
	$htm[]="</table></body></html>";
	return implode("",$htm);
}




?>