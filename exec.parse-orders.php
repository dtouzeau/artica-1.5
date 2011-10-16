<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) .'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/ressources/class.users.menus.inc");

$GLOBALS["EXEC_PID_FILE"]="/etc/artica-postfix/".basename(__FILE__).".damon.pid";


$oldpid=@file_get_contents($GLOBALS["EXEC_PID_FILE"]);
$unix=new unix();
if($unix->process_exists($oldpid)){
	echo("Starting......: artica-background Already executed pid $oldpid\n");
	die();
}

if($argv[1]=="--manual"){
	
	FillMemory();ParseLocalQueue();die();}

$sock=new sockets();
$EnableArticaBackground=$sock->GET_INFO("EnableArticaBackground");
if(!is_numeric($EnableArticaBackground)){$EnableArticaBackground=1;}
if($EnableArticaBackground==0){die();}
$GLOBALS["TOTAL_MEMORY_MB"]=$unix->TOTAL_MEMORY_MB();


if($GLOBALS["TOTAL_MEMORY_MB"]<400){
	$oldpid=@file_get_contents($GLOBALS["EXEC_PID_FILE"]);
	if($unix->process_exists($oldpid,basename(__FILE__))){events("Process Already exist pid $oldpid");die();}	
	$childpid=posix_getpid();
	echo("Starting......: artica-background lower config, remove fork\n");
	@file_put_contents($GLOBALS["EXEC_PID_FILE"],$childpid);
	FillMemory();
	$renice_bin=$GLOBALS["CLASS_UNIX"]->find_program("renice");
	events("$renice_bin 19 $childpid",__FUNCTION__,__LINE__);
	shell_exec('export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/bin/X11 &');
	shell_exec("$renice_bin 19 $childpid &");
	events("Started pid $childpid",__FUNCTION__,__LINE__);	
	ParseLocalQueue();
	if($GLOBALS["EXECUTOR_DAEMON_ENABLED"]==1){
		$nohup=$unix->find_program("nohup");
		shell_exec(trim($nohup." ".$unix->LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.executor.php --all >/dev/null 2>&1"));
	}
	
	die();
}




if(function_exists("pcntl_signal")){
	pcntl_signal(SIGTERM,'sig_handler');
	pcntl_signal(SIGINT, 'sig_handler');
	pcntl_signal(SIGCHLD,'sig_handler');
	pcntl_signal(SIGHUP, 'sig_handler');
}else{
	print "Starting......: artica-background undefined function \"pcntl_signal\"\n";
	die();
}


set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
$stop_server=false;
$reload=false;
$pid=pcntl_fork();


	if ($pid == -1) {
	     die("Starting......: artica-background fork() call asploded!\n");
	} else if ($pid) {
	     print "Starting......: artica-background fork()ed successfully.\n";
	     die();
	}


	$childpid=posix_getpid();
	@file_put_contents($GLOBALS["EXEC_PID_FILE"],$childpid);
	FillMemory();
	
	$renice_bin=$GLOBALS["CLASS_UNIX"]->find_program("renice");
	events("$renice_bin 19 $childpid",__FUNCTION__,__LINE__);
	shell_exec('export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/bin/X11 &');
	shell_exec("$renice_bin 19 $childpid &");
	events("Started pid $childpid",__FUNCTION__,__LINE__);
	
	while ($stop_server==false) {
		sleep(3);
		ParseLocalQueue();
		if($reload){
			$reload=false;
			events("reload daemon",__FUNCTION__,__LINE__);
			FillMemory();			
		}
	}
	

function sig_handler($signo) {
    global $stop_server;
    global $reload;
    switch($signo) {
        case SIGTERM: {$stop_server = true;break;}        
        case 1: {$reload=true;}
        default: {
        	if($signo<>17){events("Receive sig_handler $signo",__FUNCTION__,__LINE__);}
        }
    }
}


function FillMemory(){
	
	include_once(dirname(__FILE__).'/ressources/class.sockets.inc');
	$GLOBALS["CLASS_SOCKETS"]=new sockets();
	$GLOBALS["CLASS_USERS"]=new usersMenus();
	$GLOBALS["CLASS_UNIX"]=new unix();	
	$GLOBALS["TOTAL_MEMORY_MB"]=$GLOBALS["CLASS_UNIX"]->TOTAL_MEMORY_MB();	
	$GLOBALS["NICE"]=$GLOBALS["CLASS_UNIX"]->EXEC_NICE();
	$GLOBALS["NOHUP"]=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
	$GLOBALS["systemMaxOverloaded"]=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("systemMaxOverloaded");
	$GLOBALS["CPU_NUMBER"]=intval($GLOBALS["CLASS_USERS"]->CPU_NUMBER);
	$EnableArticaExecutor=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArticaExecutor");
	
	if(!is_numeric($EnableArticaExecutor)){$EnableArticaExecutor=1;}	
	$GLOBALS["EXECUTOR_DAEMON_ENABLED"]=$EnableArticaExecutor;
	

	
}


function MemoryInstances(){
	$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
	if(!is_file($pgrep)){return 0;}
	
	
	
$Toremove["exec.parse-orders.php"]=true;
$Toremove["exec.syslog.php"]=true;
$Toremove["exec.maillog.php"]=true;
$Toremove["exec.status.php"]=true;
$Toremove["exec.executor.php"]=true;
$Toremove["exec.ufdbguard-tail.php"]=true;
$Toremove["exec.squid-tail.php"]=true;
$Toremove["exec.fetmaillog.php"]=true;
$Toremove["exec.dansguardian-tail.php"]=true;	
$Toremove["exec.auth-tail.php"]=true;	
$Toremove["exec.artica-filter-daemon.php"]=true;	
$Toremove["exec.postfix-logger.php"]=true;	
if(!is_file($pgrep)){return;}
	
	
	$array=array();
	$cmd="$pgrep -l -f \"artica-postfix/exec\..*?\.php\" 2>&1";
	events("$cmd",__FUNCTION__,__LINE__);
	exec("$cmd",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+.+?\s+\/usr\/share\/artica-postfix\/(.+?)\.php.*?$#",$ligne,$re)){
			$filename=trim($re[2]).".php";
			if($Toremove[$filename]){continue;}
			if(!is_numeric($re[1])){continue;}
			if(!$GLOBALS["CLASS_UNIX"]->process_exists($re[1])){continue;}
			if($GLOBALS["CLASS_UNIX"]->PID_IS_CHROOTED($re[1])){continue;}
			$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($re[1]);
			
			
			if($filename=="exec.artica.meta.php"){
				if($time>20){
					$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.artica.meta.php is killed after {$time}Mn live",null,"system");
					events("killing exec.artica.meta.php it freeze...",__FUNCTION__,__LINE__);
					shell_exec("/bin/kill -9 {$re[1]}");
				}
			}
			
			
		 	if($filename=="exec.clean.logs.php"){
				if($time>60){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.clean.logs.php is killed after {$time}Mn live",null,"system");
				events("killing exec.clean.logs.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}
			
			if($filename=="exec.squid.stats.php"){
				if($time>60){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.squid.stats.php is killed after {$time}Mn live",null,"system");
				events("killing exec.squid.stats.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}
			
			if($filename=="exec.mysql.build.php"){
				if($time>30){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.mysql.build.php is killed after {$time}Mn live",null,"system");
				events("killing exec.mysql.build.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}
						
			if($filename=="exec.smtp-hack.export.php"){
				if($time>10){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.smtp-hack.export.php is killed after {$time}Mn live",null,"system");
				events("killing exec.smtp-hack.export.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}
			
			if($filename=="exec.postfix-logger.php"){
				if($time>10){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.postfix-logger.php is killed after {$time}Mn live",null,"system");
				events("killing exec.postfix-logger.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}	

			if($filename=="exec.openvpn.php"){
				if($time>5){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.openvpn.php is killed after {$time}Mn live",null,"system");
				events("killing exec.openvpn.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}
			
			if($filename=="exec.test-connection.php"){
				if($time>5){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.test-connection.php is killed after {$time}Mn live",null,"system");
				events("killing exec.test-connection.php it freeze...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
				}
			}

			if($filename=="exec.watchdog.php"){
				if($time>5){
					$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] exec.watchdog.php is killed after {$time}Mn live",null,"system");
					events("killing exec.openvpn.php it freeze...",__FUNCTION__,__LINE__);
					shell_exec("/bin/kill -9 {$re[1]}");
				}				
			}
			
			$array[]="[{$re[1]}] $filename ({$time}Mn)";
		}
	}
	
	
	$count=count($array);
	if(count($array)>0){
		events("$count processe(s) In memory: ".@implode(",",$array),__FUNCTION__,__LINE__);
	}
	$mem=round(((memory_get_usage()/1024)/1000),2);
	events("{$mem}MB consumed in memory",__FUNCTION__,__LINE__);
	
	
	//yorel
	exec("$pgrep -l -f \"perl.+?yorel-upd\" 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+#",$ligne,$re)){
			if($GLOBALS["CLASS_UNIX"]->PID_IS_CHROOTED($re[1])){continue;}
			$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($re[1]);
			if($time>10){
				$GLOBALS["CLASS_UNIX"]->send_email_events("[artica-background] yorel-upd is killed after {$time}Mn live");
				events("killing yorel-upd it {$re[1]} freeze {$time}Mn...",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 {$re[1]}");
			}
		}
		
	}
	
	
	
	
	return $count;
	
	
}

function ParseLocalQueue(){
		if(systemMaxOverloaded()){events("[OVERLOAD]:: running in max overload mode, aborting queue");return;}
		$EnableArticaBackground=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArticaBackground");
		if(!is_numeric($EnableArticaBackground)){$EnableArticaBackground=1;}	
	
	if($EnableArticaBackground==0){
		$mef=basename(__FILE__);
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$oldpid=@file_get_contents($pidfile);
		if($GLOBALS["CLASS_UNIX"]->process_exists($oldpid,$mef)){events("Process Already exist pid $oldpid line:".__LINE__);return;}	
		@file_put_contents($pidfile, getmypid());	
	}
	
	$MemoryInstances=MemoryInstances();
	if($MemoryInstances>4){events("Too much php processes in memory, aborting");return;}
	if(!is_numeric($MemoryInstances)){$MemoryInstances=0;}

if(is_file("/etc/artica-postfix/orders.queue")){
		$size=@filesize("/etc/artica-postfix/orders.queue");
		if($size>0){
			events("Loading /etc/artica-postfix/orders.queue $size bytes");
			$orders_queue=explode("\n",@file_get_contents("/etc/artica-postfix/orders.queue"));
			if(is_array($orders_queue)){
				while (list ($num, $ligne) = each ($orders_queue) ){
					if(trim($ligne)==null){continue;}
					$orders[md5($ligne)]=$ligne;
				}	
			}
		}
		@unlink("/etc/artica-postfix/orders.queue");	
	}


if(is_file("/etc/artica-postfix/background")){
		$size=@filesize("/etc/artica-postfix/background");
		if($size>0){
			events("Loading /etc/artica-postfix/background $size bytes");
			$background=explode("\n",@file_get_contents("/etc/artica-postfix/background"));
			if(is_array($background)){
				while (list ($num, $ligne) = each ($background) ){
					if(trim($ligne)==null){continue;}
					$orders[md5($ligne)]=$ligne;
				}
			}
		}
		@unlink("/etc/artica-postfix/background");
		
}

	if(is_file("/var/log/artica-postfix/executor-daemon.log")){
		$time_exec=file_time_min("/var/log/artica-postfix/executor-daemon.log");
		events("executor-daemon.log $time_exec Min");
		if($time_exec>5){
			events("artica-executor is freeze ($time_exec minutes), restart it (see /tmp/watchdog.executor.log)");
			system(trim("/etc/init.d/artica-postfix restart artica-exec >/tmp/watchdog.executor.log 2>&1"));
			events("done...");
		}
	}
	
	
	if(is_file("/usr/share/artica-postfix/ressources/logs/global.status.ini")){
		$time_status=file_time_min("/usr/share/artica-postfix/ressources/logs/global.status.ini");
		events("global.status.ini $time_exec Min");
		if($time_status>5){
			events("artica-status is freeze ($time_status minutes for /usr/share/artica-postfix/ressources/logs/global.status.ini), restart it (see /tmp/watchdog.status.log)");
			system(trim("/etc/init.d/artica-postfix restart artica-status >/tmp/watchdog.status.log 2>&1"),$results);
			$cmd=trim($nice.$nohup." ".$GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.status.php --all >/dev/null 2>&1 &");
			events("$cmd done...");
		}	
	}
	events("artica-executor: {$time_exec}mn; artica-status: {$time_status}mn ");

	
	if(count($orders)==0){
		events("artica-executor: queue is empty...");
		artica_exec();
		return null;
	}
	
	
	
	//events("[NORMAL]:: NICE={$GLOBALS["NICE"]}");
	$nice=$GLOBALS["NICE"];
	$orders_number=count($orders);
	$count_max=$orders_number;
	if($count_max>4){$count_max=4;}
	if($orders_number>10){if(!$GLOBALS["OVERLOAD"]){$count_max=10;}}
	$count=0;
	

	
	if($count_max+$MemoryInstances>10){$count_max=10-$MemoryInstances;}
	
	if($GLOBALS["TOTAL_MEMORY_MB"]<400){
		events("Lower config switch to 2 max processes...mem:{$GLOBALS["TOTAL_MEMORY_MB"]}MB");
		$count_max=2;
	}

	while (list ($num, $cmd) = each ($orders) ){
		if(trim($cmd)==null){continue;}
		if(preg_match("#artica-make#", $cmd)){
			events("artica-make detected \"$cmd\", execute this task first...");
			shell_exec("$nice$cmd$devnull");
			unset($orders[$num]);
		}
	}
	reset($orders);
	
	
	events("Orders:$orders_number Loaded instances:$MemoryInstances Max to order:$count_max");
	
	while (list ($num, $cmd) = each ($orders) ){
		if(trim($cmd)==null){continue;}
		
		$devnull=" >/dev/null 2>&1";
		if(strpos($cmd,">")>0){$devnull=null;}

		if(system_is_overloaded(__FILE__)){
			if($count>=$count_max){break;}
			unset($orders[$num]);
			events("[OVERLOAD]:: running in overload mode $nice$cmd$devnull");
			shell_exec("$nice$cmd$devnull");
			events("[OVERLOAD]:: $cmd was successfully executed, parse next");
			$count++;
			continue;
		}
		$count++;
		events("[NORMAL]:: running in normal mode $nice$cmd$devnull &");
		shell_exec("$nice$cmd$devnull &");
		events("[NORMAL]::[$num] $cmd was successfully executed, parse next");
		unset($orders[$num]);
		if($count>=$count_max){break;}
	}
	
	
	events("$count/$orders_number order(s) executed...end;");
	if(is_array($orders)){
		if(count($orders)>0){
			reset($orders);
			$fh = fopen("/etc/artica-postfix/background", 'w') or die("can't open file");
			while (list ($num, $cmd) = each ($orders) ){
				$datas="$cmd\n";
				fwrite($fh, $datas);
				}
			fclose($fh);
			events("Queued ". count($orders)." order(s)");
		}
	}
	
	

}

function artica_exec(){
	if($GLOBALS["EXECUTOR_DAEMON_ENABLED"]==0){
		$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
		$nice=$GLOBALS["NICE"];
		$cmd=trim($nice.$nohup." ".$GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.executor.php --all >/dev/null 2>&1");
		events("Executor disabled execute it \"$cmd\"");
				
		shell_exec($cmd);
	}
}

function events($text){
		
		$filename=basename(__FILE__);
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text");
		events2($text);
}

function events2($text){
		$common="/var/log/artica-postfix/parse.orders.log";
		$size=@filesize($common);
		if($size>100000){@unlink($common);}
		$pid=getmypid();
		$date=date("Y-m-d H:i:s");
		$h = @fopen($common, 'a');
		$sline="[$pid] $text";
		$line="$date [$pid] $text\n";
		@fwrite($h,$line);
		@fclose($h);
	}	


?>
