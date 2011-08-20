<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__)."/framework/class.settings.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
$GLOBALS["EXEC_PID_FILE"]="/etc/artica-postfix/".basename(__FILE__).".daemon.pid";
$unix=new unix();

if($unix->process_exists(@file_get_contents($GLOBALS["EXEC_PID_FILE"]))){
	print "Starting......: artica-executor Already executed pid ". @file_get_contents($GLOBALS["EXEC_PID_FILE"])."...\n";
	die();
}
FillMemory();
if($argv[1]=='--mails-archives'){mailarchives();die();}
if($argv[1]=='--stats-console'){stats_console();die();}

if($argv[1]=='--all'){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
	$pidtime="/etc/artica-postfix/".basename(__FILE__).".time";
	if($unix->file_time_min($pidtime)<3){die();}
	@unlink($pidtime);
	@file_put_contents($pidtime, time());
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid,basename(__FILE__))){events("Process $oldpid  already in memory","MAIN");die();}
	@file_put_contents($pidfile, getmypid());
	launch_all_status();die();
}

if(preg_match("#--(.+)#", $argv[1],$re)){if(function_exists($re[1])){events("Execute {$re[1]}() -> \"{$argv[1]}\"" ,"MAIN");call_user_func($re[1]);die();}}


if($argv[1]<>null){events("Unable to understand ". implode(" ",$argv),"MAIN");die();}

$nofork=false;
if(!function_exists("pcntl_signal")){$nofork=true;}
if($GLOBALS["TOTAL_MEMORY_MB"]<400){$nofork=true;}

if($nofork){
	print "Starting......: artica-status pcntl_fork module not loaded !\n";
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
	
	
	$childpid=posix_getpid();
	@file_put_contents($pidfile,$childpid);	
	
	$timefile="/etc/artica-postfix/".basename(__FILE__).".time";
	if(file_time_min($timefile)>1){
		@unlink($timefile);
		launch_all_status();
		@file_put_contents($timefile,"#");
	}
	
	die();
	
}


if(!$nofork){
	pcntl_signal(SIGTERM,'sig_handler');
	pcntl_signal(SIGINT, 'sig_handler');
	pcntl_signal(SIGCHLD,'sig_handler');
	pcntl_signal(SIGHUP, 'sig_handler');
}else{
	print "Starting......: artica-executor undefined function \"pcntl_signal\"\n";
}

set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
$stop_server=false;
$reload=false;
$pid=pcntl_fork();


	if ($pid == -1) {
	     die("Starting......: artica-executor fork() call asploded!\n");
	} else if ($pid) {
	     print "Starting......: artica-executor fork()ed successfully.\n";
	     die();
	}

	
	$childpid=posix_getpid();
	@file_put_contents($GLOBALS["EXEC_PID_FILE"],$childpid);
	FillMemory();
	
	$renice_bin=$unix->find_program("renice");
	if(is_file($renice_bin)){
		events("$renice_bin 19 $childpid",__FUNCTION__,__LINE__);
		shell_exec("$renice_bin 19 $childpid &");
	}
	$GLOBALS["CLASS_SOCKETS"]=new sockets();
	$GLOBALS["CLASS_USERS"]=new settings_inc();
	$GLOBALS["CLASS_UNIX"]=new unix();	
	
	while ($stop_server==false) {
		
		sleep(3);
		launch_all_status();
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
	$unix=new unix();
	$GLOBALS["TIME"]=unserialize(@file_get_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS"));
	
	
	if(GET_INFO_DAEMON("cpuLimitEnabled")==1){$GLOBALS["cpuLimitEnabled"]=true;}else{$GLOBALS["cpuLimitEnabled"]=false;}
	$_GET["NICE"]=$unix->EXEC_NICE();
	$GLOBALS["PHP5"]=$unix->LOCATE_PHP5_BIN();
	$GLOBALS["SU"]=$unix->find_program("su");
	
	$users=new settings_inc();
	$sock=new sockets();
	$DisableArticaStatusService=$sock->GET_INFO("DisableArticaStatusService");
	$EnableArticaExecutor=$sock->GET_INFO("EnableArticaExecutor");
	if(!is_numeric($DisableArticaStatusService)){$DisableArticaStatusService=0;}
	if(!is_numeric($EnableArticaExecutor)){$EnableArticaExecutor=1;}
	
	
	$GLOBALS["SPAMASSASSIN_INSTALLED"]=$users->spamassassin_installed;
	$GLOBALS["ARTICA_STATUS_DISABLED"]=$DisableArticaStatusService;
	$GLOBALS["EXECUTOR_DAEMON_ENABLED"]=$EnableArticaExecutor;
	$GLOBALS["SQUID_INSTALLED"]=$users->SQUID_INSTALLED;
	$GLOBALS["KAV4PROXY_INSTALLED"]=$users->KAV4PROXY_INSTALLED;
	$GLOBALS["POSTFIX_INSTALLED"]=$users->POSTFIX_INSTALLED;
	$GLOBALS["SAMBA_INSTALLED"]=$users->SAMBA_INSTALLED;
	$GLOBALS["GREYHOLE_INSTALLED"]=$users->GREYHOLE_INSTALLED;
	$GLOBALS["MUNIN_CLIENT_INSTALLED"]=$users->SAMBA_INSTALLED;
	$GLOBALS["CYRUS_IMAP_INSTALLED"]=$users->cyrus_imapd_installed;
	$_GET["MIME_DEFANGINSTALLED"]=$users->MIMEDEFANG_INSTALLED;
	$GLOBALS["DANSGUARDIAN_INSTALLED"]=$users->DANSGUARDIAN_INSTALLED;
	$GLOBALS["OPENVPN_INSTALLED"]=$users->OPENVPN_INSTALLED;
	$GLOBALS["OCS_INSTALLED"]=$users->OCSI_INSTALLED;
	$GLOBALS["UFDBGUARD_INSTALLED"]=$users->APP_UFDBGUARD_INSTALLED;
	$GLOBALS["KAS_INSTALLED"]=$users->kas_installed;
	$GLOBALS["ZARAFA_INSTALLED"]=$users->ZARAFA_INSTALLED;
	$GLOBALS["XAPIAN_PHP_INSTALLED"]=$users->XAPIAN_PHP_INSTALLED;
	$GLOBALS["AUDITD_INSTALLED"]=$users->APP_AUDITD_INSTALLED;
	$GLOBALS["VIRTUALBOX_INSTALLED"]=$users->VIRTUALBOX_INSTALLED;
	$GLOBALS["DRUPAL7_INSTALLED"]=$users->DRUPAL7_INSTALLED;
	if($GLOBALS["VERBOSE"]){writelogs("DANSGUARDIAN_INSTALLED={$GLOBALS["DANSGUARDIAN_INSTALLED"]}","MAIN",__FILE__,__LINE__);}
	$GLOBALS["EnableArticaWatchDog"]=GET_INFO_DAEMON("EnableArticaWatchDog");
	if($GLOBALS["VERBOSE"]){if($GLOBALS["POSTFIX_INSTALLED"]){events("Postfix is installed...");}}
	if($GLOBALS["VERBOSE"]){events("Nice=\"\", php5 {$GLOBALS["PHP5"]}");}	
	$GLOBALS["EnableInterfaceMailCampaigns"]=$sock->GET_INFO("EnableInterfaceMailCampaigns");
	$GLOBALS["CLASS_SOCKETS"]=$sock;
	$GLOBALS["TOTAL_MEMORY_MB"]=$unix->TOTAL_MEMORY_MB();
	$sock=null;
	$unix=null;
	$users=null;
	
	
	}
	
function watchdog_artica_status(){
	if(is_file("/var/log/artica-postfix/status-daemon.log")){
		$time=file_time_min("/var/log/artica-postfix/status-daemon.log");
		if($time>5){
			events("artica-status seems freeze, restart daemon",__FUNCTION__,__LINE__);
			sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart artica-status");
			@unlink("/var/log/artica-postfix/status-daemon.log");
			events("done...",__FUNCTION__,__LINE__);
		}
	}
	
}	






die();

function stats_console(){
	$array[]="exec.admin.smtp.flow.status.php";
	$array[]="exec.postfix-logger.php --postfix";
	$array[]="exec.postfix.iptables.php";
	$array[]="exec.last.100.mails.php";
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		$GLOBALS["CMDS"][]=$cmd;
	}	
		
}

function launch_all_status(){
	$functions=array("group5","group10","group30s","group10s","group0","group2","group300","group120","group30","group60mn","group5h","group24h","watchdog_artica_status");
	$system_is_overloaded=system_is_overloaded();
	$systemMaxOverloaded=systemMaxOverloaded();
	FillMemory();
	
	while (list ($num, $func) = each ($functions) ){
		if($system_is_overloaded){
				events("System is overloaded: ({$GLOBALS["SYSTEM_INTERNAL_LOAD"]}}, pause 10 seconds",__FUNCTION__,__LINE__);
				sleep(10);
				continue;
			}else{
				if($systemMaxOverloaded){
					events("System is very overloaded, pause stop",__FUNCTION__,__LINE__);
					return;
					continue;
				}
			}
			
			
			usleep(10000); 
			call_user_func($func);
	}
	$already=array();
	$AlreadyTests=array();
	if(count($GLOBALS["CMDS"])>0){
		events("scheduling ".count($GLOBALS["CMDS"])." commands",__FUNCTION__,__LINE__);
		$FileDataCommand=@file_get_contents('/etc/artica-postfix/background');
  		$tbl=explode("\n",$FileDataCommand);
  		while (list ($num, $zcommands) = each ($GLOBALS["CMDS"]) ){
			if(trim($zcommands)==null){continue;}
			
	  		if(preg_match("#^(.+?)\s+#",$zcommands,$re)){
	  			if(!$AlreadyTests[$fileTests]){
					$fileTests=trim("{$re[1]}");
					if(!is_file($fileTests)){
						events("running $fileTests No such file",__FUNCTION__,__LINE__);
						continue;
					}else{
						$AlreadyTests[$fileTests]=true;
					}
	  			}
			}
			
			
			if(!$already[$zcommands]){
				$tbl[]=$zcommands;
				$already[$zcommands]=true;
			}
  		}
  		
  		
		@file_put_contents('/etc/artica-postfix/background',implode("\n",$tbl));  
		unset($GLOBALS["CMDS"]);		
		$mem=round(((memory_get_usage()/1024)/1000),2);
		events("{$mem}MB consumed in memory",__FUNCTION__,__LINE__);
		if($GLOBALS["TOTAL_MEMORY_MB"]<400){
			$unix=new unix();
			$cmd=trim($nohup." ".$unix->LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.parse-orders.php >/dev/null 2>&1 &");
			
			shell_exec($cmd);	
		}		
	}
	
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
	
	
}



// sans vérifications, toutes les 5 minutes
function group5(){
	if(!is_numeric($GLOBALS["TIME"]["GROUP5"])){$GLOBALS["TIME"]["GROUP5"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP5"]==0)){$GLOBALS["TIME"]["GROUP5"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP5"]);
	if($mins<5){return;}
	events("{$GLOBALS["TIME"]["GROUP5"]} =  $mins Minutes / 5Mn",__FUNCTION__,__LINE__);	
	$GLOBALS["TIME"]["GROUP5"]=time();	
			
	$unix=new unix();
	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array["exec.watchdog.postfix.queue.php"]="exec.watchdog.postfix.queue.php";
		$array["exec.postfix.iptables.php"]="exec.postfix.iptables.php --parse-queue";
		$array["exec.postfix.iptables.php --export-drop"]="exec.postfix.iptables.php --export-drop";
		$array["exec.postfix-logger.php"]="exec.postfix-logger.php --postqueue-clean";
	}
	if($GLOBALS["VIRTUALBOX_INSTALLED"]){$array["exec.virtualbox.php --maintenance"]="exec.virtualbox.php --maintenance";}
	if($GLOBALS["KAV4PROXY_INSTALLED"]){$array["exec.kaspersky-update-logs.php --av-uris"];}
	 
	
	
	$array["exec.dstat.top.php"]="exec.dstat.top.php";
	$array["exec.admin.status.postfix.flow.php"]="exec.admin.status.postfix.flow.php";
	$array["exec.admin.smtp.flow.status.php"]="exec.admin.smtp.flow.status.php";
	//$array["exec.remote-install.php"]="exec.remote-install.php";
	$array["exec.parse.dar-xml.php"]="exec.parse.dar-xml.php";
	$array["exec.import-networks.php"]="exec.import-networks.php";
	$array["cron.notifs.php"]="cron.notifs.php";
	$array["exec.watchdog.php"]="exec.watchdog.php";
	
	
	if($GLOBALS["OPENVPN_INSTALLED"]){
		$array["exec.openvpn.php --schedule"]="exec.openvpn.php --schedule";
	}
	
	if($GLOBALS["SQUID_INSTALLED"]){
		$array["exec.web-community-filter.php"]="exec.web-community-filter.php";
		if($GLOBALS["DANSGUARDIAN_INSTALLED"]){
			if(!is_file("/usr/share/artica-postfix/ressources/logs/dansguardian.patterns")){
				$array["exec.dansguardian.compile.php --patterns"]="exec.dansguardian.compile.php --patterns";
			}
		}
	
	}

	if($GLOBALS["SAMBA_INSTALLED"]){
		if($GLOBALS["XAPIAN_PHP_INSTALLED"]){
			$array["exec.xapian.index.php"]="exec.xapian.index.php";
		}
		$array["exec.samba.php --smbtree"]="exec.samba.php --smbtree";
	}
	
	
	if(is_file("/usr/sbin/glusterfsd")){
		$array["exec.gluster.php"]="exec.gluster.php --notify-server";
	}
	
	if($GLOBALS["EnableArticaWatchDog"]==1){
		$array2[]="artica-install --start-minimum-daemons";
	}
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		if($GLOBALS["KAS_INSTALLED"]){
			$array2[]="artica-update --kas3";
		}
		
	}
	

	
	$array2[]="artica-install --generate-status";
	
	if($GLOBALS["OVERLOADED"]){
		unset($array["exec.dstat.top.php"]);
		unset($array["exec.admin.status.postfix.flow.php"]);
		unset($array["exec.parse.dar-xml.php"]);
		unset($array["exec.import-networks.php"]);
		unset($array["exec.admin.smtp.flow.status.php"]);
	}
	
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		mailarchives();
	}
	
	if(is_array($array2)){
	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}}		
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}
//sans vérifications toutes les 30mn
function group30(){
	if(!is_numeric($GLOBALS["TIME"]["GROUP30"])){$GLOBALS["TIME"]["GROUP30"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP30"]==0)){$GLOBALS["TIME"]["GROUP30"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP30"]);
	
	
	if($mins<30){return;}
	$GLOBALS["TIME"]["GROUP30"]=time();
	events("Starting $mins (minutes)",__FUNCTION__,__LINE__);
	
	$array["exec.activedirectory-import.php"];
	
	
	if($GLOBALS["SQUID_INSTALLED"]){
		$array[]="exec.squid.stats.php --graphs";
		$array[]="exec.squid.blacklists.php --inject";
	}
	if($GLOBALS["SAMBA_INSTALLED"]){
		$array[]="exec.picasa.php";
		$array[]="exec.samba.php --ScanTrashs";
	
	
	}
		
	if($GLOBALS["DRUPAL7_INSTALLED"]){$array[]="exec.freeweb.php --drupal-cron";}
	if($GLOBALS["SPAMASSASSIN_INSTALLED"]){	$array[]="exec.spamassassin.php --sa-update-check";}
	
	
	
	
	$array[]="exec.emerging.threats.php";
	$array[]="exec.my-rbl.check.php --checks";
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}	
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}


//sans vérifications toutes les 10mn
function group10(){
	if(!is_numeric($GLOBALS["TIME"]["GROUP10"])){$GLOBALS["TIME"]["GROUP10"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP10"]==0)){$GLOBALS["TIME"]["GROUP10"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP10"]);
	
	
	if($mins<10){return;}
	events("Starting $mins (minutes) {$GLOBALS["TIME"]["GROUP10"]} ".time(),__FUNCTION__,__LINE__);	
	$GLOBALS["TIME"]["GROUP10"]=time();
	events("Starting {$GLOBALS["GROUP10"]}",__FUNCTION__,__LINE__);
	
	
	$EnablePhileSight=GET_INFO_DAEMON("EnablePhileSight");
	if($EnablePhileSight==null){$EnablePhileSight=1;}
	
	$array[]="exec.getent.php";
	$array[]="exec.clean.logs.php --clean-tmp";
	
	if($GLOBALS["OCS_INSTALLED"]){$array[]="exec.ocsweb.php --injection";	}
	if($GLOBALS["AUDITD_INSTALLED"]){$array[]="exec.auditd.php --import";}
	if($GLOBALS["SQUID_INSTALLED"]){$array[]="exec.dansguardian.last.php";}
	if($GLOBALS["EnableArticaWatchDog"]==1){$array2[]="artica-install --startall";}
	if($GLOBALS["ZARAFA_INSTALLED"]){$array[]="exec.zarafa.adbookldap.php --all";	}

	
	if($EnablePhileSight==1){$array[]="exec.philesight.php --check";}
	$array[]="exec.kaspersky-update-logs.php";
	$array[]="exec.emailrelay.php --notifier-queue";
	$array[]="exec.watchdog.php --queues";
	$array[]="exec.freeweb.php --perms";
	
	if($GLOBALS["UFDBGUARD_INSTALLED"]){$array[]="exec.web-community-filter.php --groupby";}

	$array2[]="process1 --force";
	$array2[]="artica-install --check-virus-logs";
	$array2[]="artica-install --monit-check";
	$array2[]="process1 --cleanlogs";
	
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}

	while (list ($index, $file) = each ($array2) ){
		
		$cmd="/usr/share/artica-postfix/bin/$file";
		
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}

	if($GLOBALS["MUNIN_CLIENT_INSTALLED"]){
		$GLOBALS["CMDS"][]="{$GLOBALS["SU"]} - munin --shell=/bin/bash munin-cron";
	}
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
	
}

//toutes les minutes
function group0(){
	
	if(!is_numeric($GLOBALS["TIME"]["GROUP0"])){$GLOBALS["TIME"]["GROUP0"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP0"]==0)){$GLOBALS["TIME"]["GROUP0"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP0"]);
	
	if($mins<1){return;}	


	events("Starting {$GLOBALS["TIME"]["GROUP0"]} 1mn",__FUNCTION__,__LINE__);
	$GLOBALS["TIME"]["GROUP0"]=time();

	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.whiteblack.php";
		$array[]="exec.postfix-logger.php";
	}
	

	if(is_array($array)){
		while (list ($index, $file) = each ($array) ){
			if(system_is_overloaded()){events(__FUNCTION__. ":: die, overloaded");die();}
			$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
			events("schedule $cmd",__FUNCTION__,__LINE__);
			$GLOBALS["CMDS"][]=$cmd;
		}
	}

	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));

}
//toutes les 2 minutes
function group2(){
	
	
if(!is_numeric($GLOBALS["TIME"]["GROUP2"])){$GLOBALS["TIME"]["GROUP2"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP2"]==0)){$GLOBALS["TIME"]["GROUP2"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP2"]);
	
	if($mins<2){return;}	
	events("Starting {$GLOBALS["TIME"]["GROUP2"]} 2mn",__FUNCTION__,__LINE__);
	$GLOBALS["TIME"]["GROUP2"]=time();
	
	
	$array[]="exec.dhcpd-leases.php";
	$array[]="exec.mailbackup.php";
	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.postfix-logger.php --cnx-errors";
		$array[]="exec.postfix-logger.php --cnx-only";
	}
	

	if($GLOBALS["OCSI_INSTALLED"]){$array[]="exec.remote-agent-install.php";}
	if(!function_exists("pcntl_fork")){$array[]="exec.status.php";}
	if(!system_is_overloaded()){
		if($GLOBALS["SQUID_INSTALLED"]){$array[]="exec.dansguardian.injector.php";}
	}
	if($GLOBALS["CYRUS_IMAP_INSTALLED"]){$array[]="exec.cyrus-restore.php --ad-sync";}
	if($GLOBALS["ARTICA_STATUS_DISABLED"]==1){$array[]="exec.status.php --all";}
	
	if(is_array($array)){
		while (list ($index, $file) = each ($array) ){
			$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
			events("schedule $cmd",__FUNCTION__,__LINE__);
			$GLOBALS["CMDS"][]=$cmd;
		}	
	}
	
	
	if(is_array($array2)){
		while (list ($index, $file) = each ($array2) ){
			$cmd="/usr/share/artica-postfix/bin/$file";
			events("schedule $cmd",__FUNCTION__,__LINE__);
			$GLOBALS["CMDS"][]=$cmd;
		}	
	}
@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
	
}

function group10s(){
	return;
	$GLOBALS["TIME"]["GROUP10s"]=$GLOBALS["TIME"]["GROUP10s"]+1;
	
	if($GLOBALS["TIME"]["GROUP10s"]<30){return;}
	
	events("Starting {$GLOBALS["GROUP10s"]}",__FUNCTION__,__LINE__);
	$GLOBALS["TIME"]["GROUP10s"]=0;
  
	if(is_array($array)){
			while (list ($index, $file) = each ($array) ){
				$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
				events("schedule $cmd",__FUNCTION__,__LINE__);
				@$GLOBALS["CMDS"][]=$cmd;
			}	
		}
	
	if($GLOBALS["cpuLimitEnabled"]){$array2[]="process1 --cpulimit";}
	
	if(is_array($array2)){
			while (list ($index, $file) = each ($array2) ){
				$cmd="/usr/share/artica-postfix/bin/$file";
				events("schedule $cmd",__FUNCTION__,__LINE__);
				$GLOBALS["CMDS"][]=$cmd;
			}	
		}

	@unlink($fileTime);
	@file_put_contents($fileTime,"#");
	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}
function group30s(){
	
if(!is_numeric($GLOBALS["TIME"]["GROUP30s"])){$GLOBALS["TIME"]["GROUP30s"]=time();return;}
if(($GLOBALS["TIME"]["GROUP30s"]==0)){$GLOBALS["TIME"]["GROUP30s"]=time();return;} 

	$seconds=time()-$GLOBALS["TIME"]["GROUP30s"];
	
	
	if($seconds<30){
		
		return;
	}
	
	events("$seconds seconds {$GLOBALS["GROUP30s"]} / ". time(),__FUNCTION__,__LINE__);
	$GLOBALS["TIME"]["GROUP30s"]=time();
	$array[]="exec.mpstat.php";
	$array[]="exec.jgrowl.php --build";
	$array[]="cron.notifs.php";
    

	if($GLOBALS["cpuLimitEnabled"]){$array2[]="process1 --cpulimit";}
	if($GLOBALS["POSTFIX_INSTALLED"]){
		if($_GET["MIME_DEFANGINSTALLED"]){$array2[]="artica-install --graphdefang-gen";}
		if(!$GLOBALS["OVERLOADED"]){
				$array2[]="artica-thread-back";
				}
	}
	
  if(is_array($array)){
		while (list ($index, $file) = each ($array) ){
			$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
			events("schedule $cmd",__FUNCTION__,__LINE__);
			$GLOBALS["CMDS"][]=$cmd;
		}	
	}
	
	if(is_array($array2)){
			while (list ($index, $file) = each ($array2) ){
				$cmd="/usr/share/artica-postfix/bin/$file";
				events("schedule $cmd",__FUNCTION__,__LINE__);
				$GLOBALS["CMDS"][]=$cmd;
			}	
		}

		@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}

//5H
function group5h(){
	if(!is_numeric($GLOBALS["TIME"]["group5h"])){$GLOBALS["TIME"]["group5h"]=time();return;}
	if(($GLOBALS["TIME"]["group5h"]==0)){$GLOBALS["TIME"]["group5h"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["group5h"]);
	if($mins<300){return;}
	events("{$GLOBALS["TIME"]["group5h"]} =  $mins Minutes / 300Mn",__FUNCTION__,__LINE__);	
	$GLOBALS["TIME"]["group5h"]=time();

	
	$array[]="exec.awstats.php";
	if($GLOBALS["POSTFIX_INSTALLED"]){$array[]="exec.postfix.iptables.php --parse-sql";}
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd Minutes=$mins",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}

	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
	
	
}


//2H
function group300(){
	
	if(!is_numeric($GLOBALS["TIME"]["GROUP300"])){$GLOBALS["TIME"]["GROUP300"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP300"]==0)){$GLOBALS["TIME"]["GROUP300"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP300"]);
	
	if($mins<120){return;}
	
	
	$GLOBALS["TIME"]["GROUP300"]=time();	
	
	
	if(!is_file("/etc/artica-postfix/settings/Daemons/HdparmInfos")){sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.hdparm.php");}
	$array[]="exec.mysql.build.php --tables";
	$array[]="exec.mysql.build.php --maintenance";
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.organization.statistics.php";
		$array[]="exec.quarantine-clean.php";
		$array[]="exec.smtp-hack.export.php --export";
		$array[]="exec.postfix-logger.php --cnx-stats";
		$array[]="exec.smtp.events.clean.php";
		$array[]="exec.roundcube.php --verifyTables";
		
		
	}
	
	if($GLOBALS["SQUID_INSTALLED"]){$array[]="exec.squid.stats.php --maintenance";}	
	
	
	$array2[]="artica-install -geoip-updates";
	  
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}

	while (list ($index, $file) = each ($array2) ){
		$cmd="/usr/share/artica-postfix/bin/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}   
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}

//24H 1440mn
function group24h(){
	
	if(!is_numeric($GLOBALS["TIME"]["24H"])){$GLOBALS["TIME"]["24H"]=time();return;}
	if(($GLOBALS["TIME"]["24H"]==0)){$GLOBALS["TIME"]["24H"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["24H"]);
	
	if($mins<1440){return;}
	
	
	$GLOBALS["TIME"]["24H"]=time();	
	
	if($GLOBALS["GREYHOLE_INSTALLED"]){
		$array[]="exec.greyhole.php --fsck";
	}
	
	
	
		if(is_array($array)){	  
			while (list ($index, $file) = each ($array) ){
				$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
				events("schedule $cmd",__FUNCTION__,__LINE__);
				$GLOBALS["CMDS"][]=$cmd;
			}
		}

	if(is_array($array2)){
		while (list ($index, $file) = each ($array2) ){
			$cmd="/usr/share/artica-postfix/bin/$file";
			events("schedule $cmd",__FUNCTION__,__LINE__);
			$GLOBALS["CMDS"][]=$cmd;
		} 
	}  
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}

function group60mn(){
	if(!is_numeric($GLOBALS["TIME"]["group60mn"])){$GLOBALS["TIME"]["group60mn"]=time();return;}
	if(($GLOBALS["TIME"]["group60mn"]==0)){$GLOBALS["TIME"]["group60mn"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["group60mn"]);
	if($mins<60){return;}
	
	
	events("Starting {$GLOBALS["TIME"]["group60mn"]}",__FUNCTION__,__LINE__);
	$GLOBALS["TIME"]["group60mn"]=time();	
	
	
	
	if($GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableInterfaceMailCampaigns")==1){$array[]="exec.emailing.badmails.php";}
	if($GLOBALS["ZARAFA_INSTALLED"]){$array[]="exec.zarafa.build.stores.php --exoprhs";}
	$array[]="exec.my-rbl.check.php --myip";
	$array[]="exec.my-rbl.check.php --checks";
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}	
@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}


function group120(){
	
	if(!is_numeric($GLOBALS["TIME"]["GROUP120"])){$GLOBALS["TIME"]["GROUP120"]=time();return;}
	if(($GLOBALS["TIME"]["GROUP120"]==0)){$GLOBALS["TIME"]["GROUP120"]=time();return;} 	
	$mins=calc_time_min($GLOBALS["TIME"]["GROUP120"]);
	if($mins<120){return;}	
	
	
	$GLOBALS["TIME"]["GROUP120"]=time();
	
	events("Starting {$GLOBALS["GROUP120"]}",__FUNCTION__,__LINE__);
	
	
	
	$array[]="exec.apt-get.php --update";
	$array[]="exec.cleanfiles.php";
	
	if($GLOBALS["POSTFIX_INSTALLED"]){
		$array[]="exec.smtp.export.users.php --sync";
		$array[]="exec.quarantine-clean.php";
		$array[]="exec.awstats.php --postfix";
		$array[]="exec.awstats.php --cleanlogs";
		$array[]="exec.postfix.finder.php --logrotate";
	}
	
	
	if($GLOBALS["DANSGUARDIAN_INSTALLED"]){
		$array["exec.dansguardian.compile.php --patterns"]="exec.dansguardian.compile.php --patterns";
	}

	if($GLOBALS["CYRUS_IMAP_INSTALLED"]){
		$array["exec.cyrus.php --DirectorySize"]="exec.cyrus.php --DirectorySize";
	}
	
	while (list ($index, $file) = each ($array) ){
		$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CMDS"][]=$cmd;
	}	
	
	
	
	
	
	$array2[]="artica-install --awstats-generate";
	$array2[]="artica-update";
	$array2[]="artica-install --cups-drivers";
	$array2[]="artica-update --spamassassin-bl";
	$array2[]="artica-install -watchdog daemon";
	$array2[]="artica-make APP_MOD_QOS";
	
	if($GLOBALS["EnableArticaWatchDog"]==1){
		$array2[]="artica-install --urgency-start";
	}
	
	

	while (list ($index, $file) = each ($array2) ){
		events("schedule $cmd",__FUNCTION__,__LINE__);
		$cmd="/usr/share/artica-postfix/bin/$file";
		$GLOBALS["CMDS"][]=$cmd;
	}		
	$GLOBALS["CMDS"][]="/etc/init.d/artica-postfix restart clamd";
	@file_put_contents("/etc/artica-postfix/pids/".basename(__FILE__).".GLOBALS",serialize($GLOBALS["TIME"]));
}


function mailarchives(){
	$array[]="exec.mailarchive.php";
	$array[]="exec.mailbackup.php";
	$array[]="exec.fetchmail.sql.php";

	while (list ($index, $file) = each ($array) ){
		if(system_is_overloaded()){events(__FUNCTION__. ":: die, overloaded");die();}
			$cmd="{$GLOBALS["PHP5"]} /usr/share/artica-postfix/$file";
			events("schedule $cmd",__FUNCTION__,__LINE__);
			$GLOBALS["CMDS"][]=$cmd;
		}
		
	if($GLOBALS["VERBOSE"]){events(__FUNCTION__. ":: die...");}
}



function events($text,$function,$line=0){
		$l=new debuglogs();
		$filename=basename(__FILE__);
		$l->events("$filename $function:: $text (L.$line)","/var/log/artica-postfix/executor-daemon.log");
		}
?>