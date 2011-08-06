<?php
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after class.system.network.inc","MAIN",__LINE__);
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after class.mysql.inc","MAIN",__LINE__);
include_once(dirname(__FILE__).'/framework/class.unix.inc');
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after class.unix.inc","MAIN",__LINE__);
include_once(dirname(__FILE__)."/framework/frame.class.inc");
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after frame.class.inc","MAIN",__LINE__);
include_once(dirname(__FILE__)."/ressources/class.os.system.inc");
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after class.os.system.inc","MAIN",__LINE__);
include_once(dirname(__FILE__)."/framework/class.settings.inc");
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after class.settings.inc","MAIN",__LINE__);
$GLOBALS["FORCE"]=false;
$GLOBALS["EXECUTED_AS_ROOT"]=true;
$GLOBALS["DISABLE_WATCHDOG"]=false;
$GLOBALS["MY-POINTER"]="/etc/artica-postfix/pids/". basename(__FILE__).".pointer";
$GLOBALS["COMMANDLINE"]=implode(" ",$argv);
if(strpos($GLOBALS["COMMANDLINE"],"--verbose")>0){
	$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;
	ini_set('html_errors',0);
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);
	
}
if(preg_match("#--nowachdog#",$GLOBALS["COMMANDLINE"])){$GLOBALS["DISABLE_WATCHDOG"]=true;}
if(preg_match("#--force#",$GLOBALS["COMMANDLINE"])){$GLOBALS["FORCE"]=true;}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$sock=new sockets();
$DisableArticaStatusService=$sock->GET_INFO("DisableArticaStatusService");
if(!is_numeric($DisableArticaStatusService)){$DisableArticaStatusService=0;}
$unix=new unix();
$GLOBALS["ArticaWatchDogList"]=unserialize(base64_decode($sock->GET_INFO("ArticaWatchDogList")));
$GLOBALS["PHP5"]=$unix->LOCATE_PHP5_BIN();
$GLOBALS["NICE"]=$unix->EXEC_NICE();
$GLOBALS["nohup"]=$unix->find_program("nohup");
if($GLOBALS["VERBOSE"]){echo "DEBUG MODE ENABLED\n";}
if($GLOBALS["VERBOSE"]){echo "command line: {$GLOBALS["COMMANDLINE"]}\n";}
$GLOBALS["AMAVIS_WATCHDOG"]=unserialize(@file_get_contents("/etc/artica-postfix/amavis.watchdog.cache"));
$GLOBALS["TOTAL_MEMORY_MB"]=$unix->TOTAL_MEMORY_MB();
$sock=null;
$unix=null;


if(strlen($argv[1])>0){
	events("parsing command line ".@implode(";", $argv),"MAIN",__LINE__);
	$GLOBALS["CLASS_UNIX"]=new unix();
	$GLOBALS["CLASS_SOCKETS"]=new sockets();
	$GLOBALS["CLASS_USERS"]=new settings_inc();
	CheckCallable();
}

$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after declarations","MAIN",__LINE__);

if(strlen($argv[1])>2){events("parsing command line {$argv[1]}","MAIN",__LINE__);}

if($argv[1]=="--all"){
	events("-> launch_all_status()","MAIN",__LINE__);
	launch_all_status();
	die();
}
if($argv[1]=="--free"){echo getmem();exit;}
if($argv[1]=="--squid"){echo squid_master_status();exit;}
if($argv[1]=="--c-icap"){echo c_icap_master_status();exit;}
if($argv[1]=="--kav4proxy"){echo kav4Proxy_status();exit;}
if($argv[1]=="--dansguardian"){echo dansguardian_master_status();exit;}
if($argv[1]=="--wifi"){echo wpa_supplicant();;exit;}
if($argv[1]=="--fetchmail"){echo fetchmail();;exit;}
if($argv[1]=="--milter-greylist"){echo milter_greylist();;exit;}
if($argv[1]=="--framework"){echo framework();;exit;}
if($argv[1]=="--pdns"){echo pdns_server()."\n".pdns_recursor();exit;}
if($argv[1]=="--cyrus-imap"){echo cyrus_imap();exit;}
if($argv[1]=="--mysql"){echo "\n".mysql_server()."\n".mysql_mgmt()."\n". mysql_replica();exit;}
if($argv[1]=="--openldap"){echo "\n".openldap();;exit;}
if($argv[1]=="--saslauthd"){echo "\n".saslauthd();;exit;}
if($argv[1]=="--sysloger"){echo "\n".syslogger();;exit;}
if($argv[1]=="--squid-tail"){echo "\n".squid_tail();;exit;}
if($argv[1]=="--amavis"){echo "\n".amavis();exit;}
if($argv[1]=="--amavis-milter"){echo"\n". amavis_milter();exit;}
if($argv[1]=="--boa"){echo"\n". boa();exit;}
if($argv[1]=="--lighttpd"){echo"\n". lighttpd();exit;}
if($argv[1]=="--fcron"){echo"\n". fcron1()."\n".fcron2(); exit;}
if($argv[1]=="--clamav"){echo"\n". clamd()."\n".clamscan()."\n".clammilter()."\n".freshclam(); exit;}
if($argv[1]=="--retranslator"){echo"\n". retranslator_httpd(); exit;}
if($argv[1]=="--spamassassin"){echo spamassassin_milter()."\n".spamassassin();exit;}
if($argv[1]=="--postfix"){echo "\n".postfix();exit;}
if($argv[1]=="--postfix-logger"){echo "\n".postfix_logger();exit;}
if($argv[1]=="--mailman"){echo "\n".mailman();exit;}
if($argv[1]=="--kas3"){echo "\n".kas3_milter()."\n".kas3_ap(); exit;}
if($argv[1]=="--samba"){echo "\n".smbd()."\n".nmbd()."\n".winbindd()."\n".scanned_only()."\n"; exit;}
if($argv[1]=="--roundcube"){echo "\n".roundcube();exit;}
if($argv[1]=="--cups"){echo "\n".cups();exit;}
if($argv[1]=="--apache-groupware"){echo "\n".apache_groupware();exit;}
if($argv[1]=="--gdm"){echo "\n".gdm();exit;}
if($argv[1]=="--console-kit"){echo "\n".consolekit();exit;}
if($argv[1]=="--xfce"){echo "\n".xfce();exit;}
if($argv[1]=="--vmtools"){echo "\n".vmtools();exit;}
if($argv[1]=="--hamachi"){echo "\n".hamachi();exit;}
if($argv[1]=="--artica-notifier"){echo "\n".artica_notifier();exit;}
if($argv[1]=="--dhcpd"){echo "\n".dhcpd_server();exit;}
if($argv[1]=="--pure-ftpd"){echo "\n".pure_ftpd();exit;}
if($argv[1]=="--mldonkey"){echo "\n".mldonkey();exit;}
if($argv[1]=="--policydw"){echo "\n".policyd_weight();exit;}
if($argv[1]=="--backuppc"){echo "\n".backuppc();exit;}
if($argv[1]=="--kav4fs"){echo "\n".kav4fs()."\n".kav4fsavs();exit;}
if($argv[1]=="--ocsweb"){echo "\n".apache_ocsweb()."\n".apache_ocsweb_download()."\n";exit;}
if($argv[1]=="--ocsagent"){echo "\n".ocs_agent();exit;}
if($argv[1]=="--openssh"){echo "\n".openssh();exit;}
if($argv[1]=="--gluster"){echo "\n".gluster();exit;}
if($argv[1]=="--auditd"){echo "\n".auditd();exit;}
if($argv[1]=="--squidguard-http"){echo "\n".squidguardweb();exit;}
if($argv[1]=="--opendkim"){echo "\n".opendkim();exit;}
if($argv[1]=="--ufdbguardd"){echo "\n".ufdbguardd();exit;}
if($argv[1]=="--ufdb-tail"){echo "\n".ufdbguardd_tail();exit;}
if($argv[1]=="--squidguard-tail"){echo "\n".squidguard_logger();exit;}
if($argv[1]=="--dkim-milter"){echo "\n".milter_dkim();exit;}
if($argv[1]=="--dropbox"){echo "\n".dropbox();exit;}
if($argv[1]=="--artica-policy"){echo "\n".artica_policy();exit;}
if($argv[1]=="--vboxwebsrv"){echo "\n".virtualbox_webserv();exit;}
if($argv[1]=="--tftpd"){echo "\n".tftpd();exit;}
if($argv[1]=="--vdi"){echo "\n".virtualbox_webserv()."\n".tftpd()."\n".dhcpd_server();exit;}
if($argv[1]=="--crossroads"){echo "\n".crossroads();exit;}
if($argv[1]=="--artica-status"){echo "\n".artica_status();exit;}
if($argv[1]=="--artica-executor"){echo "\n".artica_executor();exit;}
if($argv[1]=="--artica-background"){echo "\n".artica_background();exit;}
if($argv[1]=="--pptpd"){echo "\n".pptpd();exit;}
if($argv[1]=="--pptpd-clients"){echo "\n".pptp_clients();exit;}
if($argv[1]=="--bandwith"){echo "\n".bandwith();exit;}
if($argv[1]=="--apt-mirror"){echo "\n".apt_mirror();exit;}
if($argv[1]=="--squidclamav-tail"){echo "\n".squid_clamav_tail();exit;}
if($argv[1]=="--ddclient"){echo "\n".ddclient();exit;}
if($argv[1]=="--cluebringer"){echo "\n".cluebringer();exit;}
if($argv[1]=="--apachesrc"){echo "\n".apachesrc();exit;}
if($argv[1]=="--assp"){echo "\n".assp();exit;}
if($argv[1]=="--freewebs"){echo "\n".apachesrc()."\n".pure_ftpd()."\n".tomcat();exit;}
if($argv[1]=="--openvpn"){echo "\n".openvpn();exit;}
if($argv[1]=="--vboxguest"){echo "\n".vboxguest();exit;}
if($argv[1]=="--sabnzbdplus"){echo "\n".sabnzbdplus();exit;}
if($argv[1]=="--openvpn-clients"){echo "\n".OpenVPNClientsStatus();exit;}
if($argv[1]=="--stunnel"){echo "\n".stunnel();exit;}
if($argv[1]=="--meta-checks"){echo "\n".meta_checks();exit;}
if($argv[1]=="--smbd"){echo "\n".smbd();exit;}
if($argv[1]=="--vnstat"){echo "\n".vnstat();exit;}
if($argv[1]=="--munin"){echo "\n".munin();exit;}
if($argv[1]=="--autofs"){echo "\n".autofs();exit;}
if($argv[1]=="--greyhole"){echo "\n".greyhole();exit;}
if($argv[1]=="--amavis-watchdog"){echo "\n".AmavisWatchdog();exit;}
if($argv[1]=="--dnsmasq"){echo "\n".dnsmasq();exit;}
if($argv[1]=="--iscsi"){echo "\n".iscsi();exit;}
if($argv[1]=="--yorel"){echo "\n".watchdog_yorel();exit;}
if($argv[1]=="--watchdog-service"){echo "\n".WATCHDOG($argv[2],$argv[3]);exit;}
if($argv[1]=="--postfwd2"){echo "\n".postfwd2();exit;}
if($argv[1]=="--zarafa-watchdog"){zarafa_watchdog();exit;}
if($argv[1]=="--vps"){echo vps_servers();exit;}
if($argv[1]=="--crossroads-multiple"){echo crossroads_multiple();exit;}
if($argv[1]=="--smartd"){echo "\n".smartd();exit;}
if($argv[1]=="--watchdog-me"){echo watchdog_me();die();}
if($argv[1]=="--auth-tail"){echo auth_tail();exit;}
if($argv[1]=="--snort"){echo snort();exit;}
if($argv[1]=="--xload"){echo xLoadAvg();$GLOBALS["VERBOSE"]=true;exit;}
if($argv[1]=="--greyhole-watchdog"){greyhole_watchdog();exit;}
if($argv[1]=="--greensql"){echo greensql();exit;}
if($argv[1]=="--nscd"){echo nscd();exit;}
if($argv[1]=="--tomcat"){echo tomcat();exit;}
if($argv[1]=="--openemm"){echo openemm()."\n".openemm_sendmail();exit;}

if($GLOBALS["VERBOSE"]){echo "cannot understand {$argv[1]} assume perhaps it is a function\n";}


if($argv[1]=="--functions"){
	$arr = get_defined_functions();
	print_r($arr);
	die();
}



if($argv[1]=="--all-squid"){
	$conf[]=dansguardian_tail_status();
	$conf[]=squid_master_status();
	$conf[]=c_icap_master_status();
	$conf[]=dansguardian_master_status();
	$conf[]=kav4Proxy_status();
	$conf[]=proxy_pac_status();
	$conf[]=squid_tail();
	$conf[]=squidguardweb();
	$conf[]=ufdbguardd();
	echo @implode("\n",$conf);
	die();
}
if($argv[1]=="--zarafa"){
	$conf[]=zarafa_web();
	$conf[]=zarafa_ical();
	$conf[]=zarafa_dagent();
	$conf[]=zarafa_monitor();
	$conf[]=zarafa_gateway();
	$conf[]=zarafa_spooler();
	$conf[]=zarafa_server();
	$conf[]=zarafa_licensed();
	$conf[]=zarafa_indexer();
	echo @implode("\n",$conf);
	die();
}



if($argv[1]=="--amavis-full"){
	$conf[]=spamassassin();
	$conf[]=clamd();
	$conf[]=amavis();
	$conf[]=amavis_milter();
	echo @implode("\n",$conf);
	die();
}
if($argv[1]=="--verbose"){unset($argv[1]);}
if(isset($argv[1])){
	if(strlen($argv[1])>0){
		write_syslog("Unable to understand {$argv[1]}",basename(__FILE__));
		die();
	}
}

if($DisableArticaStatusService==1){
	events("-> launch_all_status()","MAIN",__LINE__);
	launch_all_status();
	die();	
}


$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$pid=@file_get_contents($pidfile);
$unix=new unix();
if($unix->process_exists($pid,(basename(__FILE__)))){
	print "Starting......: artica-status Already executed PID $pid...\n";
	die();
}
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB artica-status Memory {$GLOBALS["TOTAL_MEMORY_MB"]}MB","MAIN",__LINE__);
print "Starting......: artica-status Memory {$GLOBALS["TOTAL_MEMORY_MB"]}MB\n";
if(!function_exists("pcntl_fork")){$nofork=true;}
if($GLOBALS["TOTAL_MEMORY_MB"]<400){$nofork=true;}
if($DisableArticaStatusService==1){$nofork=true;}


if($nofork){
	
	print "Starting......: artica-status pcntl_fork module not loaded !\n";
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
	$childpid=posix_getpid();
	events("{$mem}MB artica-status Memory NO fork.... pid=$childpid","MAIN",__LINE__);
	@file_put_contents($pidfile,$childpid);	
	
	$timefile="/etc/artica-postfix/".basename(__FILE__).".time";
	if(file_time_min($timefile)>1){
		@unlink($timefile);
		events("{$mem}MB artica-status Memory NO fork.... -> launch_all_status()","MAIN",__LINE__);
		launch_all_status();
		@file_put_contents($timefile,"#");
	}
	events("{$mem}MB artica-status Memory NO fork.... -> die()","MAIN",__LINE__);
	$nohup=$unix->find_program("nohup");
	shell_exec(trim($nohup." ".$unix->LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.parse-orders.php >/dev/null 2>&1 &"));	
	die();
	

	
}



if(function_exists("pcntl_signal")){
	pcntl_signal(SIGTERM,'sig_handler');
	pcntl_signal(SIGINT, 'sig_handler');
	pcntl_signal(SIGCHLD,'sig_handler');
	pcntl_signal(SIGHUP, 'sig_handler');
}	


set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);


$stop_server=false;
$reload=false;
$pid = pcntl_fork();
if ($pid == -1) {
     die("Starting......: artica-status fork() call asploded!\n");
} else if ($pid) {
     // we are the parent
     print "Starting......: artica-status fork()ed successfully.\n";
     die();
}

	$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
	$childpid=posix_getpid();
	@file_put_contents($pidfile,$childpid);
	events("Starting PID $childpid","MAIN",__LINE__);	
	
	
	$renice_bin=$unix->find_program("renice");
	events("$renice_bin 19 $childpid","MAIN",__LINE__);
	shell_exec("$renice_bin 19 $childpid &");	
	events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." before start service".__LINE__);
	$count=0;
	while ($stop_server==false) {
		$count++;
		sleep(5);
		$mem=round(((memory_get_usage()/1024)/1000),2);
		$timeDaemonFile=$unix->file_time_min("/etc/artica-postfix/pids/exec.status.time");
		$DaemonTime=$unix->file_time_min($timeDaemonFile);
		$timefile=$unix->file_time_min("/usr/share/artica-postfix/ressources/logs/global.status.ini");
		
		
		events("WAIT: $timefile/2mn {$mem}MB global.status.ini:{$timefile}Mn",__FUNCTION__,__LINE__);

		
		if(is_file("/usr/share/artica-postfix/ressources/logs/launch.status.task")){
			events("launch.status.task detected",__FUNCTION__,__LINE__);
			if(!is_file("/etc/artica-postfix/launch.status.lock")){@file_put_contents("/etc/artica-postfix/launch.status.lock",time());}
			$timeLock=$unix->file_time_min("/etc/artica-postfix/launch.status.lock");
			events("timeLock {$timeLock}Mn",__FUNCTION__,__LINE__);
			
			if($timeLock>0){
				@unlink("/usr/share/artica-postfix/ressources/logs/launch.status.task");
				@unlink($DaemonTime);@file_put_contents($DaemonTime, time());
				@unlink("/etc/artica-postfix/launch.status.lock");
				try {launch_all_status(true);} catch (Exception $e) {writelogs("Fatal while running function launch_all_status $e",__FUNCTION__,__FILE__,__LINE__);}
				continue;
			}
			
		}
		
		if($DaemonTime>=3){
			events("global.status.ini time ($DaemonTime) is more than 2Mn  -> Launch all status...",__FUNCTION__,__LINE__);
			@unlink($DaemonTime);@file_put_contents($DaemonTime, time());
			try {launch_all_status(true);} catch (Exception $e) {writelogs("Fatal while running function launch_all_status $e",__FUNCTION__,__FILE__,__LINE__);}
			$count=0;
			continue;			
		}
		
		if($timefile>3){
			@unlink($DaemonTime);@file_put_contents($DaemonTime, time());
			events("global.status.ini time ($timefile) is more than 2Mn  -> Launch all status...",__FUNCTION__,__LINE__);
			try {launch_all_status(true);} catch (Exception $e) {writelogs("Fatal while running function launch_all_status $e",__FUNCTION__,__FILE__,__LINE__);}
			continue;
		}
		
		if(!is_file("/usr/share/artica-postfix/ressources/logs/global.status.ini")){
			@unlink($DaemonTime);@file_put_contents($DaemonTime, time());
			events("global.status.ini does not exists  -> Launch all status...",__FUNCTION__,__LINE__);
			try {launch_all_status(true);} catch (Exception $e) {writelogs("Fatal while running function launch_all_status $e",__FUNCTION__,__FILE__,__LINE__);}
			continue;
		}		
		
		
		if($reload){
			$reload=false;
			events("reload daemon ($count seconds)",__FUNCTION__,__LINE__);
			$GLOBALS["CLASS_SOCKETS"]=new sockets();
			$GLOBALS["CLASS_USERS"]=new settings_inc();
			$GLOBALS["CLASS_UNIX"]=new unix();
			$GLOBALS["TIME_CLASS"]=time();
			$GLOBALS["ArticaWatchDogList"]=unserialize(base64_decode($GLOBALS["CLASS_SOCKETS"]->GET_INFO("ArticaWatchDogList")));
			unset($GLOBALS["GetVersionOf"]);	
			if(!is_file("/usr/share/artica-postfix/ressources/logs/global.status.ini")){
				@unlink($DaemonTime);@file_put_contents($DaemonTime, time());
				launch_all_status(true);
			}
			$GLOBALS["AMAVIS_WATCHDOG"]=unserialize(@file_get_contents("/etc/artica-postfix/amavis.watchdog.cache"));
			
				
		}
		
		include_once("ressources/class.os.system.tools.inc");
		$os=new os_system();
		$GLOBALS["MEMORY_INSTALLED"]=$os->memory();
		$os=null;
		$GLOBALS["CLASS_SOCKETS"]=null;
		$GLOBALS["CLASS_USERS"]=null;
		$GLOBALS["CLASS_UNIX"]=null;	
	}
	
events("die()...","MAIN",__LINE__);	
	

function sig_handler($signo) {
    global $stop_server;
    global $reload;
    switch($signo) {
        case SIGTERM: {
            $stop_server = true;
            break;
        }
        
        case 1: {
        	$reload=true;
        	
        }
        
        default: {
        	if($signo<>17){events("Receive sig_handler $signo",__FUNCTION__,__LINE__);}
        }
    }
}


function watchdog_me(){
	$unix=new unix();
	if($GLOBALS["TOTAL_MEMORY_MB"]<400){
		events("watchdog_me: {$GLOBALS["TOTAL_MEMORY_MB"]}M installed on this computer, aborting",__FUNCTION__,__LINE__);
		
		$cmd=trim($nohup." ".$unix->LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.parse-orders.php >/dev/null 2>&1 &");
		shell_exec($cmd);	
		$cmd=trim($nohup." ".$unix->LOCATE_PHP5_BIN()." ".__FILE__." --all >/dev/null 2>&1 &");
		shell_exec($cmd);	
		$cmd=trim($nohup." /etc/init.d/artica-postfix restart fcron >/dev/null 2>&1 &");
		shell_exec($cmd);	
		return;
	}
	$sock=new sockets();
	$DisableArticaStatusService=$sock->GET_INFO("DisableArticaStatusService");
	if(!is_numeric($DisableArticaStatusService)){$DisableArticaStatusService=0;}

	if($DisableArticaStatusService==1){
		$time_file=$unix->file_time_min($GLOBALS["MY-POINTER"]);
		events("Pointer: {$GLOBALS["MY-POINTER"]} = {$time_file}Mn",__FUNCTION__,__LINE__);
		if($time_file>3){
			events("Pointer: start artica-status !!!",__FUNCTION__,__LINE__);
			$cmd=trim($nohup." ".$unix->LOCATE_PHP5_BIN()." ".__FILE__." --all >/dev/null 2>&1 &");
			shell_exec($cmd);	
			$cmd=trim($nohup." /etc/init.d/artica-postfix restart fcron >/dev/null 2>&1 &");
			shell_exec($cmd);	
		}
		return;
	}
	
	
	$time_file=$unix->file_time_min($GLOBALS["MY-POINTER"]);
	events("Pointer: {$GLOBALS["MY-POINTER"]} = {$time_file}Mn",__FUNCTION__,__LINE__);
	if($time_file>3){
		events("Pointer: restart artica-status !!!",__FUNCTION__,__LINE__);
		shell_exec("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix restart artica-status >/dev/null 2>&1 &");
		
	}
	
	
}
function amavis_watchdog_load_conf(){
if(is_file("/etc/artica-postfix/settings/Daemons/AmavisGlobalConfiguration")){
			$ini=new iniFrameWork();
			$ini->loadFile("/etc/artica-postfix/settings/Daemons/AmavisGlobalConfiguration");
			$GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"]=$ini->_params["BEHAVIORS"]["max_servers"];
			$GLOBALS["AMAVIS_WATCHDOG_CHILD_TIMEOUT"]=$ini->_params["BEHAVIORS"]["child_timeout"];
			
		}	
		$GLOBALS["AMAVIS_WATCHDOG_CONF_TIME"]=filemtime("/usr/local/etc/amavisd.conf");
		
		events("/usr/local/etc/amavisd.conf: time:{$GLOBALS["AMAVIS_WATCHDOG_CONF_TIME"]}",__FUNCTION__,__LINE__);
		events("max_servers: {$GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"]}",__FUNCTION__,__LINE__);
		events("child_timeout: {$GLOBALS["AMAVIS_WATCHDOG_CHILD_TIMEOUT"]}",__FUNCTION__,__LINE__);
}

function amavis_watchdog_removebayes(){
	$f[]="bayes_journal";
	$f[]="bayes_seen";
	$f[]="bayes_toks";
	while (list ($num, $filename) = each ($f)){
		if(is_file("/etc/spamassassin/$filename")){@unlink("/etc/spamassassin/$filename");}
		if(is_file("/etc/mail/spamassassin/$filename")){@unlink("/etc/mail/spamassassin/$filename");}
	}

	
	
}


function AmavisWatchdog(){
	if(!is_file("/usr/local/etc/amavisd.conf")){return;}
	if(!isset($GLOBALS["AMAVIS_WATCHDOG_CONF_TIME"])){amavis_watchdog_load_conf();}
	if(!isset($GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"])){amavis_watchdog_load_conf();}
	$time=filemtime("/usr/local/etc/amavisd.conf");
	if($time<>$GLOBALS["AMAVIS_WATCHDOG_CONF_TIME"]){amavis_watchdog_load_conf();}
	
	
		if(preg_match("#([0-9]+)\*([0-9]+)#",$GLOBALS["AMAVIS_WATCHDOG_CHILD_TIMEOUT"],$re)){
			$seconds=intval($re[2]);
			$int=intval($re[1]);
			$AmavisWatchdogMaxInterval=round($int*$seconds)/60;
		}else{
			$AmavisWatchdogMaxInterval=50;
		}	
		
	$AmavisWatchdogFinalInterval=$AmavisWatchdogMaxInterval+5;		
	
	if(!is_numeric($AmavisWatchdogMaxInterval)){$AmavisWatchdogMaxInterval=50;}
	if(!is_numeric($GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"])){$GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"]=5;}
	
	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAmavisWatchdog");
	$AmavisWatchdogMaxCPU=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("AmavisWatchdogMaxCPU");
	$AmavisWatchdogKillProcesses=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("AmavisWatchdogKillProcesses");
	if(!is_numeric($enabled)){$enabled=1;}
	if($enabled==0){return;}
	
	if(!is_numeric($AmavisWatchdogMaxCPU)){$AmavisWatchdogMaxCPU=80;}
	if(!is_numeric($AmavisWatchdogKillProcesses)){$AmavisWatchdogKillProcesses=1;}
	
	
	if(!isset($GLOBALS["psbin"])){$GLOBALS["psbin"]=$GLOBALS["CLASS_UNIX"]->find_program("ps");}
	if(!isset($GLOBALS["grepbin"])){$GLOBALS["grepbin"]=$GLOBALS["CLASS_UNIX"]->find_program("grep");}
	if(!isset($GLOBALS["killbin"])){$GLOBALS["killbin"]=$GLOBALS["CLASS_UNIX"]->find_program("kill");}
	
	if(!isset($GLOBALS["AMAVIS_WATCHDOG"])){
		if(is_file("/etc/artica-postfix/amavis.watchdog.cache")){
			$GLOBALS["AMAVIS_WATCHDOG"]=unserialize(@file_get_contents("/etc/artica-postfix/amavis.watchdog.cache"));
		}
	}
	$notify_text="";
	$cmd="{$GLOBALS["psbin"]} aux|{$GLOBALS["grepbin"]} -E \"amavisd \(\" 2>&1";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	exec("$cmd",$results);
	$childs=0;
	while (list ($num, $line) = each ($results)){
		if(preg_match("#[a-z]+\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+).+?amavisd\s+\((.+?)\)#",$line,$re)){
			$type=$re[4];
			$pid=$re[1];
			$cpu_pourc=intval($re[2]);
			$cpumem=$re[3];
			$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($pid);
			$time_by_pid=$time;
			
			$rss=$GLOBALS["CLASS_UNIX"]->PROCESS_MEMORY($pid);
			$vm=$GLOBALS["CLASS_UNIX"]->PROCESS_CACHE_MEMORY($pid);
			
			$array_status[$pid]=array("TYPE"=>$type,"CPU"=>$cpu_pourc,"TIME"=>$time
			,"RSS"=>$rss,"VMSIZE"=>$vm
			
			);
			if($type<>"master"){if($type<>"virgin"){if($type<>"virgin child"){$childs++;}}}
			$info="$childs/{$GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"]} Found child type:$type pid:$pid CPU:$cpu_pourc% Memory:{$rss}Mb Cached :{$vm}Mb running since {$time}Mn max running:{$AmavisWatchdogFinalInterval}Mn";
			events("$info",__FUNCTION__,__LINE__);
			$text[]="$info";
			
			if($type<>"master"){
			if($time_by_pid>=$AmavisWatchdogFinalInterval){
						events("Killing $pid pid...",__FUNCTION__,__LINE__);
						shell_exec("{$GLOBALS["killbin"]} -9 $pid");
						$notify_text="This process has been killed";
						$GLOBALS["CLASS_UNIX"]->send_email_events("Warning Amavis child ($type) reach {$AmavisWatchdogFinalInterval}Mn ({$time_by_pid}Mn)",
						"Amavis child PID $pid using $cpu_pourc and has been detected {$GLOBALS["AMAVIS_WATCHDOG"][$pid]["count"]} times
						 in {$time_by_pid}Mn
						 $notify_text
						 \n".@implode("\n",$text),"postfix");
						  amavis_watchdog_removebayes();
						 continue;
					}
			}			
			
			
			
			if($cpu_pourc>$AmavisWatchdogMaxCPU){
				events("Warning on pid $pid",__FUNCTION__,__LINE__);
				if(!isset($GLOBALS["AMAVIS_WATCHDOG"][$pid]["time"])){
					$GLOBALS["AMAVIS_WATCHDOG"][$pid]["time"]=time();
					$GLOBALS["AMAVIS_WATCHDOG"][$pid]["count"]=1;
					continue;
				}else{
					$min_interval=calc_time_min($GLOBALS["AMAVIS_WATCHDOG"][$pid]["time"]);
					$GLOBALS["AMAVIS_WATCHDOG"][$pid]["count"]=$GLOBALS["AMAVIS_WATCHDOG"][$pid]["count"]+1;
					events("Last detected time $min_interval minutes add score +1 -> {$GLOBALS["AMAVIS_WATCHDOG"][$pid]["count"]}",__FUNCTION__,__LINE__);
					if($min_interval>$AmavisWatchdogMaxInterval){
						if($AmavisWatchdogKillProcesses==1){
							shell_exec("{$GLOBALS["killbin"]} -9 $pid");
							$notify_text="This process has been killed";
						}
						$GLOBALS["CLASS_UNIX"]->send_email_events("Warning Amavis child reach $AmavisWatchdogMaxCPU% CPU after {$AmavisWatchdogMaxInterval}Mn max running:$AmavisWatchdogFinalInterval",
						"Amavis child PID $pid using $cpu_pourc and has been detected {$GLOBALS["AMAVIS_WATCHDOG"][$pid]["count"]} times
						 in {$min_interval}Mn
						 $notify_text
						 \n".@implode("\n",$text),"postfix");
						 amavis_watchdog_removebayes();
						 continue;
					}
					
					

				}
				
			}else{
				if(isset($GLOBALS["AMAVIS_WATCHDOG"][$pid])){
					events("Remove warning on pid $pid",__FUNCTION__,__LINE__);
					unset($GLOBALS["AMAVIS_WATCHDOG"][$pid]);
				}
				
			}	
		}
	}
	
	/*if($childs>=$GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"]){
		$GLOBALS["CLASS_UNIX"]->send_email_events("Warning Amavis reach the maximal servers processes",
		"You have defined Amavis to run {$GLOBALS["AMAVIS_WATCHDOG_MAX_SERVERS"]}, you need to increase this value\nhere it
		is the processes list:\n".@implode("\n",$text),"postfix");
	}*/	
	
	
	if(is_array($GLOBALS["AMAVIS_WATCHDOG"])){
		while (list ($pid, $array) = each ($GLOBALS["AMAVIS_WATCHDOG"])){
			events("in memory... PID:$pid",__FUNCTION__,__LINE__);
			if(!$GLOBALS["CLASS_UNIX"]->process_exists($pid)){
				events("remove from memory... PID:$pid",__FUNCTION__,__LINE__);
				unset($GLOBALS["AMAVIS_WATCHDOG"][$pid]);
			}
		}
		
	}
	
	@file_put_contents("/etc/artica-postfix/amavis.watchdog.cache",@serialize($GLOBALS["AMAVIS_WATCHDOG"]));
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/amavis.infos.array",@serialize($array_status));
	@chmod("/usr/share/artica-postfix/ressources/logs/amavis.infos.array",0777);
	
}


function SwapWatchdog(){
	$reboot=false;
	$filecache="/etc/artica-postfix/cron.1/".basename(__FILE__).".".__FUNCTION__.".time";
	$SwapOffOn=unserialize(base64_decode($GLOBALS["CLASS_SOCKETS"]->GET_INFO("SwapOffOn")));
	if(!is_numeric($SwapOffOn["SwapEnabled"])){$SwapOffOn["SwapEnabled"]=1;}
	if(!is_numeric($SwapOffOn["SwapMaxPourc"])){$SwapOffOn["SwapMaxPourc"]=20;}
	if(!is_numeric($SwapOffOn["SwapMaxMB"])){$SwapOffOn["SwapMaxMB"]=0;}
	if($SwapOffOn["SwapEnabled"]==0){return;}
	$filetime=file_time_min($filecache);
	if($filetime<30){
		events("{$filetime}Mn need to wait 30mn",__FUNCTION__,__LINE__);
		return;
	}
	include_once(dirname(__FILE__)."/ressources/class.main_cf.inc");
	$sys=new systeminfos();
	events("$sys->swap_used/$sys->swap_total ",__FUNCTION__,__LINE__);
	$pourc=round(($sys->swap_used/$sys->swap_total)*100);
	events("{$sys->swap_used}MB used ($pourc%)",__FUNCTION__,__LINE__);
	if($SwapOffOn["SwapMaxMB"]>0){
		if($sys->swap_used>$SwapOffOn["SwapMaxMB"]){
			$execeed_text=$SwapOffOn["SwapMaxMB"]."MB";
			$reboot=true;
		}
	}
	if($SwapOffOn["SwapMaxMB"]==0){
		if($pourc>3){
			if($pourc>$SwapOffOn["SwapMaxPourc"]){
				$execeed_text=$SwapOffOn["SwapMaxPourc"]."%";
				$reboot=true;
			}
		}
	}	
	@unlink($filecache);
	@file_put_contents($filecache,"#");
	if(!$reboot){return;}
	
	$swapoff=$GLOBALS["CLASS_UNIX"]->find_program("swapoff");
	$swapon=$GLOBALS["CLASS_UNIX"]->find_program("swapon");
	
	if(!is_file($swapoff)){events("swapoff no such file",__FUNCTION__,__LINE__);shell_exec("sync; echo \"3\" > /proc/sys/vm/drop_caches >/dev/null 2>&1");return;}
	if(!is_file($swapon)){events("swapon no such file",__FUNCTION__,__LINE__);shell_exec("sync; echo \"3\" > /proc/sys/vm/drop_caches >/dev/null 2>&1");return;}
	
	$time=time();
	$cmd="$swapoff -a 2>&1";
	
	$results=array();
	$results[]=$cmd;
	events("running $cmd",__FUNCTION__,__LINE__);
	exec($cmd,$results);
	
	$cmd="$swapon -a 2>&1";
	
	$results[]=$cmd;
	events("running $cmd",__FUNCTION__,__LINE__);
	exec($cmd,$results);
	
	$text=@implode("\n",$results);
	$time_duration=distanceOfTimeInWords($time,time());
	shell_exec("sync; echo \"3\" > /proc/sys/vm/drop_caches >/dev/null 2>&1");
	events("results: $time_duration\n $text",__FUNCTION__,__LINE__);
	$GLOBALS["CLASS_UNIX"]->send_email_events("Memory swap purge $execeed_text ($time_duration)",$text,"system");
	
}

function xLoadAvg(){
	if(!isset($GLOBALS["CLASS_UNIX"])){CheckCallable();}
	if(function_exists("sys_getloadavg")){
		$timeDaemonFile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
		if(!is_file($timeDaemonFile)){@file_put_contents($timeDaemonFile, time());$GLOBALS["FORCE"]=true;}
		$DaemonTime=$GLOBALS["CLASS_UNIX"]->file_time_min($timeDaemonFile);	
		
		if($GLOBALS["VERBOSE"]){echo "\"$timeDaemonFile\" : $DaemonTime minutes...\n";}	
		
		if(!$GLOBALS["FORCE"]){
			if($DaemonTime<3){
				events_Loadavg("End due of time ($timeDaemonFile) = $DaemonTime < 3",__FUNCTION__,__LINE__);
				if($GLOBALS["VERBOSE"]){echo "End due of time\n";}
				return;
			}
		}
		@unlink($timeDaemonFile);
		@file_put_contents($timeDaemonFile, time());
		$array_load=sys_getloadavg();
		$ttt=time();
		$internal_load=$array_load[0];
		if($GLOBALS["VERBOSE"]){echo "System load $internal_load\n";}
		events_Loadavg("System load $internal_load",__FUNCTION__,__LINE__);
		if(!is_dir("/var/log/artica-postfix/loadavg")){@mkdir("/var/log/artica-postfix/loadavg",644,true);}
		events_Loadavg("saving in /var/log/artica-postfix/loadavg/$ttt",__FUNCTION__,__LINE__);
		@file_put_contents("/var/log/artica-postfix/loadavg/$ttt", $internal_load);
		$cmd="{$GLOBALS["nohup"]} {$GLOBALS["NICE"]} {$GLOBALS["PHP5"]} ".dirname(__FILE__)."/exec.syslog-engine.php --loadavg >/dev/null 2>&1 &";
		$cmd=trim($cmd);
		events_Loadavg("$cmd",__FUNCTION__,__LINE__);
		shell_exec($cmd);
	}else{
		events_Loadavg("Fatal: System load \"sys_getloadavg\" no such function",__FUNCTION__,__LINE__);
	}	
	
}


function launch_all_status($force=false){

	xLoadAvg();
	
	$trace=debug_backtrace();if(isset($trace[1])){$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";events("$called",__FUNCTION__,__LINE__);}	

	CheckCallable();
	if(!is_file("/usr/share/artica-postfix/ressources/logs/global.versions.conf")){
		events("-> artica-install --write-version",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --write-versions");
	}else{
		$filetime=file_time_min("/usr/share/artica-postfix/ressources/logs/global.versions.conf");
		events("global.versions.conf={$filetime}mn ",__FUNCTION__,__LINE__);
		if($filetime>60){
			events("global.versions.conf \"$filetime\"mn",__FUNCTION__,__LINE__);
			@unlink("/usr/share/artica-postfix/ressources/logs/global.versions.conf");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --write-versions");
		}
	}
	
	@unlink($GLOBALS["MY-POINTER"]);
	@file_put_contents($GLOBALS["MY-POINTER"],time());
	
	$functions=array("squid_master_status","c_icap_master_status","kav4Proxy_status","dansguardian_master_status","wpa_supplicant","fetchmail","milter_greylist",
	"framework","pdns_server","pdns_recursor","cyrus_imap","mysql_server","mysql_mgmt","mysql_replica","openldap","saslauthd","syslogger","squid_tail","amavis",
	"amavis_milter","boa","lighttpd","fcron1","fcron2","clamd","clamscan","clammilter","freshclam","retranslator_httpd","spamassassin_milter","spamassassin",
	"postfix","postfix_logger","mailman","kas3_milter","kas3_ap","smbd","nmbd","winbindd","scanned_only","roundcube","cups","apache-groupware","apache_groupware",
	"gdm","console-kit","xfce","vmtools","hamachi","artica_notifier","dhcpd_server","pure_ftpd","mldonkey","policyd_weight","backuppc","kav4fs","kav4fsavs",
	"apache_ocsweb","web_download","ocs_agent","openssh","gluster","auditd","squidguardweb","opendkim","ufdbguardd","squidguard_logger","milter_dkim","dropbox",
	"artica_policy","virtualbox_webserv","tftpd","dhcpd_server","crossroads","artica_status","artica_executor","artica_background","bandwith",
	 "pptpd","pptp_clients","apt_mirror","squid_clamav_tail","ddclient","cluebringer","apachesrc","zarafa_web","zarafa_ical","zarafa_dagent","zarafa_indexer",
	"zarafa_monitor","zarafa_gateway","zarafa_spooler","zarafa_server","assp","openvpn","vboxguest","sabnzbdplus","SwapWatchdog","artica_meta_scheduler",
	"OpenVPNClientsStatus","stunnel","meta_checks","zarafa_licensed","CheckCurl","ufdbguardd_tail","vnstat","NetAdsWatchdog","munin","autofs","greyhole",
	"dnsmasq","iscsi","watchdog_yorel","postfwd2","vps_servers","smartd","crossroads_multiple","auth_tail","greyhole_watchdog","greensql","nscd","tomcat",
	"openemm","openemm_sendmail"
	);
	$data1=$GLOBALS["TIME_CLASS"];
	$data2 = time();
	$difference = ($data2 - $data1); 	 
	$min=round($difference/60);	
	if($min>9){
		events("reloading classes...",__FUNCTION__,__LINE__);
		$GLOBALS["TIME_CLASS"]=time();
		$GLOBALS["CLASS_SOCKETS"]=new sockets();
		$GLOBALS["CLASS_USERS"]=new settings_inc();
		$GLOBALS["CLASS_UNIX"]=new unix();		
	} 
	
	
	
	while (list ($num, $func) = each ($functions) ){
		if(function_exists($func)){
			$mem=round(((memory_get_usage()/1024)/1000),2);
			
			events("running function \"$func\" {$mem}MB in memory",__FUNCTION__,__LINE__);
			if(!$force){
				if(system_is_overloaded()){
					events("System is overloaded: {$GLOBALS["SYSTEM_INTERNAL_LOAD"]}, pause 10 seconds",__FUNCTION__,__LINE__);
					AmavisWatchdog();
					greyhole_watchdog();
					sleep(10);
					return;
				}else{
					if(systemMaxOverloaded()){
					events("System is very overloaded {$GLOBALS["SYSTEM_INTERNAL_LOAD"]}, stop",__FUNCTION__,__LINE__);
					AmavisWatchdog();
					greyhole_watchdog();
					return;
					}
				}
			}
			
			try {
				$results=call_user_func($func);
			} catch (Exception $e) {
				writelogs("Fatal while running function $func ($e)",__FUNCTION__,__FILE__,__LINE__);
			}
			
			if(trim($results)<>null){$conf[]=$results;usleep(5000);}
		}
	}
	
	@unlink("/usr/share/artica-postfix/ressources/logs/global.status.ini");
	file_put_contents("/usr/share/artica-postfix/ressources/logs/global.status.ini",@implode("\n",$conf));
	@chmod(770,"/usr/share/artica-postfix/ressources/logs/global.status.ini");
	@file_put_contents("/etc/artica-postfix/cache.global.status",@implode("\n",$conf));	
	events("creating status done ". count($conf)." lines....",__FUNCTION__,__LINE__);
	$cmd=trim("{$GLOBALS["nohup"]} {$GLOBALS["NICE"]} {$GLOBALS["PHP5"]} ".dirname(__FILE__)."/exec.parse-orders.php --manual >/dev/null 2>&1 &");
	events($cmd);
	shell_exec($cmd);

}
// ========================================================================================================================================================

function artica_meta_scheduler(){
	if($GLOBALS["CLASS_SOCKETS"]->GET_INFO("ArticaMetaEnabled")==0){events("Artica meta console is disabled....",__FUNCTION__,__LINE__);return;}
	if($GLOBALS["PHP5"]==null){$GLOBALS["PHP5"]=LOCATE_PHP5_BIN2();}
	$agent_pid="/etc/artica-postfix/pids/exec.artica.meta.php.SendStatus.pid";
	$filetime=file_time_min($agent_pid);
	events("pid return {$filetime}Mn",__FUNCTION__,__LINE__);
	$cmd="{$GLOBALS["NICE"]}{$GLOBALS["PHP5"]} ". dirname(__FILE__)."/exec.artica.meta.php --status";
	
	if($filetime>15){
			events("It seems that scheduler did not wants to execute agent, i execute it myself",__FUNCTION__,__LINE__);
			$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
			if(strlen($nohup)>4){$cmd="$nohup $cmd >/dev/null 2>&1";}
			events("$cmd",__FUNCTION__,__LINE__);
			shell_exec($cmd);
			return;
	}
	
	events("Scheduling status to Artica meta console....GLOBALS[CLASS_UNIX]->THREAD_COMMAND_SET(\"$cmd\")",__FUNCTION__,__LINE__);
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("$cmd");
	events("Done...",__FUNCTION__,__LINE__);
	
	
}

function OpenVPNClientsStatus(){
	$q=new mysql();
	
	@unlink("/usr/share/artica-postfix/ressources/logs/openvpn-clients.status");
	$sql="SELECT ID,connexion_name FROM vpnclient WHERE `connexion_type`=2 AND `enabled`=1";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){
		events($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$id=$ligne["ID"];
		events("Checking VPN client N.$id",__FUNCTION__,__FILE__,__LINE__);
		$l[]="[{$ligne["connexion_name"]}]";
		$l[]="service_name={$ligne["connexion_name"]}";
		$l[]="service_cmd=openvpn";	
		$l[]="master_version=".GetVersionOf("openvpn");
		$l[]="service_disabled=1";
		$l[]="family=vpn";
		$l[]="watchdog_features=1";
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file("/etc/artica-postfix/openvpn/clients/$id/pid");
	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_OPENVPN {$ligne["connexion_name"]}","openvpn");
			$l[]="running=0\ninstalled=1";$l[]="";
		}else{	
			$l[]="running=1";
			$l[]=GetMemoriesOf($master_pid);
			$l[]="";
		}

	}
	if(is_array($l)){$final=implode("\n",$l);}
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/openvpn-clients.status",$final);
	return $final;
	
}


function squid_master_status(){
	

	
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." squid is not installed\n";}
		return null;
		}else{if($GLOBALS["VERBOSE"]){echo __FUNCTION__." squid is installed\n";}}
		
	
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){
		$SQUIDEnable=1;
		$GLOBALS["CLASS_SOCKETS"]->SET_INFO("SQUIDEnable",1);
	}
	exec("/usr/share/artica-postfix/bin/artica-install --export-version squid 2>&1",$results);
	$version=trim(implode("",$results));
	$l[]="[SQUID]";
	$l[]="service_name=APP_SQUID";
 	$l[]="master_version=$version";
 	$l[]="service_cmd=squid-cache";	
	$l[]="service_disabled=$SQUIDEnable";
	$l[]="watchdog_features=1";
	$l[]="explain=SQUID_CACHE_TINYTEXT";
	$l[]="remove_cmd=--squid-remove";
	$l[]="family=squid";
	if($GLOBALS["CLASS_USERS"]->SQUID_ICAP_ENABLED){
		$l[]="icap_enabled=1";
	}
	
	if(!is_file("/var/log/squid/cache.log")){shell_exec("/etc/init/d/artica-postfix restart squid-cache >/dev/null 2>&1 &");}
	
	if($SQUIDEnable==0){$l[]="running=0\ninstalled=1";return implode("\n",$l);return;}
	
	$pid=trim(@file_get_contents("/var/run/squid.pid"));
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($pid)){
		WATCHDOG("APP_SQUID","squid-cache");
		$l[]="running=0\ninstalled=1";
		$l[]="";
		return implode("\n",$l);return;
	}
	$l[]="running=1";
    $l[]=GetMemoriesOf($pid);
	
	$l[]="";
	return implode("\n",$l);return;	
}
// ========================================================================================================================================================



function squid_clamav_tail(){
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){if($GLOBALS["VERBOSE"]){echo __FUNCTION__." squid is not installed\n";}return null;}else{if($GLOBALS["VERBOSE"]){echo __FUNCTION__." squid is installed\n";}}	
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;$GLOBALS["CLASS_SOCKETS"]->SET_INFO("SQUIDEnable",1);}		
	$EnableSquidClamav=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSquidClamav");	
	if($SQUIDEnable==0){$EnableSquidClamav=0;}
	
	$master_pid=trim(@file_get_contents("/etc/artica-postfix/exec.squid-clamav-tail.php.pid"));
	
	$version=trim(@implode("",$results));
	$l[]="[APP_SQUID_CLAMAV_TAIL]";
	$l[]="service_name=APP_SQUID_CLAMAV_TAIL";
 	$l[]="master_version=".GetVersionOf("artica");
 	$l[]="service_cmd=squidclamav-tail";	
	$l[]="service_disabled=$EnableSquidClamav";
	$l[]="watchdog_features=1";

	
	if($EnableSquidClamav==0){$l[]="running=0\ninstalled=1";return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_SQUID_CLAMAV_TAIL","squidclamav-tail");
		$l[]="running=0\ninstalled=1";
		$l[]="";
		return implode("\n",$l);return;
	}
	$l[]="running=1";
    $l[]=GetMemoriesOf($master_pid);
	
	$l[]="";
	return implode("\n",$l);return;	
	
}
// ========================================================================================================================================================
function WATCHDOG($APP_NAME,$cmd){
		if($GLOBALS["DISABLE_WATCHDOG"]){return null;}
		if(!isset($GLOBALS["ArticaWatchDogList"][$APP_NAME])){$GLOBALS["ArticaWatchDogList"][$APP_NAME]=1;}
		if($GLOBALS["ArticaWatchDogList"][$APP_NAME]==null){$GLOBALS["ArticaWatchDogList"][$APP_NAME]=1;}
		if($GLOBALS["ArticaWatchDogList"][$APP_NAME]==1){
			$cmd="{$GLOBALS["NICE"]} {$GLOBALS["PHP5"]} ".dirname(__FILE__)."/exec.watchdog.php --start-process \"$APP_NAME\" \"$cmd\" >/dev/null 2>&1 &";
			events("WATCHDOG: running $APP_NAME ($cmd)",basename(__FILE__));
			shell_exec($cmd);
		
		}	
	
}

function c_icap_master_status(){
	
	
		
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){return null;}
	if(!$GLOBALS["CLASS_USERS"]->C_ICAP_INSTALLED){return null;}
	
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;$GLOBALS["CLASS_SOCKETS"]->SET_INFO("SQUIDEnable",1);}
	$CicapEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("CicapEnabled");
	if($SQUIDEnable==0){$CicapEnabled=0;}
	
	$master_pid=trim(@file_get_contents("/var/run/c-icap.pid"));

	$l[]="[C-ICAP]";
	$l[]="service_name=APP_C_ICAP";
 	$l[]="master_version=".GetVersionOf("c-icap");
 	$l[]="service_cmd=cicap";	
	$l[]="service_disabled=$CicapEnabled";
	$l[]="pidpath=/var/run/c-icap.pid";
	$l[]="explain=enable_c_icap_text";
	$l[]="family=squid";
	
	
	
	if($CicapEnabled==0){
		return implode("\n",$l);return;
	}
	
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program("c-icap");
	$l[]="binpath=$binpath";
	
	
	if($master_pid==null){$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$l[]="running=0\ninstalled=1";
		$l[]="";
		return implode("\n",$l);return;
	}	
	
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	
	$l[]="";	
	return implode("\n",$l);return;	
	
}

// ========================================================================================================================================================
function dansguardian_master_status(){
	
	
		
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){return null;}
	if(!$GLOBALS["CLASS_USERS"]->DANSGUARDIAN_INSTALLED){return null;}
	
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DansGuardianEnabled");
	if($SQUIDEnable==0){$enabled=0;}
	if($enabled==null){$enabled=0;}
	
	
	$l[]="[DANSGUARDIAN]";
	$l[]="service_name=APP_DANSGUARDIAN";
 	$l[]="master_version=".GetVersionOf("dansguardian");
 	$l[]="service_cmd=dansguardian";
 	$l[]="service_disabled=$enabled";
 	$l[]="remove_cmd=--dansguardian-remove";	
 	$l[]="explain=enable_dansguardian_text";
 	$l[]="family=squid";	
 	
	if($enabled==0){return implode("\n",$l);return;}
	
	$master_pid=trim(@file_get_contents("/var/run/dansguardian.pid"));
	if($master_pid==null){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($GLOBALS["CLASS_UNIX"]->find_program("dansguardian"));
	}
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";	
	return implode("\n",$l);return;	
	
}

// ========================================================================================================================================================
function dansguardian_tail_status(){
	
	
		
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){return null;}
	if(!$GLOBALS["CLASS_USERS"]->DANSGUARDIAN_INSTALLED){return null;}
	
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DansGuardianEnabled");
	if($SQUIDEnable==0){$enabled=0;}
	
	$master_pid=trim(@file_get_contents("/etc/artica-postfix/exec.dansguardian-tail.php.pid"));
	
	$l[]="[ARTICA_DANS_TAIL]";
	$l[]="service_name=APP_ARTICA_DANSGUARDIAN_TAIL";
 	$l[]="master_version=".GetVersionOf("artica");
 	$l[]="service_cmd=dansguardian";
 	$l[]="service_disabled=$enabled";
 	//$l[]="remove_cmd=--dansguardian-remove";	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";	
	return implode("\n",$l);return;	
	
}
// ========================================================================================================================================================
function kav4Proxy_status(){
	
	
	if(!$GLOBALS["CLASS_USERS"]->KASPERSKY_WEB_APPLIANCE){
		if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){return null;}
		if(!$GLOBALS["CLASS_USERS"]->KAV4PROXY_INSTALLED){return null;}
		$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
		if($SQUIDEnable==null){$SQUIDEnable=1;}
		$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("kavicapserverEnabled");
		if($SQUIDEnable==0){$enabled=0;}		
	}else{
		$enabled=1;
	}
	
	
	
	
	
	
	$master_pid=trim(@file_get_contents("/var/run/kav4proxy/kavicapserver.pid"));
	
	$l[]="[KAV4PROXY]";
	$l[]="service_name=APP_KAV4PROXY";
 	$l[]="master_version=".GetVersionOf("kav4proxy");
 	$l[]="service_cmd=kav4proxy";	
 	$l[]="service_disabled=$enabled";
 	$l[]="remove_cmd=--kav4Proxy-remove";
 	$l[]="explain=enable_kavproxy_text";
 	$l[]="family=squid";
 	
 	$unix=new unix();
 	$licenseManager=$unix->PIDOF("/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager");
 	if($unix->process_exists($licenseManager)){
 		if($unix->PROCCESS_TIME_MIN($licenseManager)>1){
 			events("Killing /opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager $licenseManager",__FUNCTION__,__LINE__);
 			shell_exec("/bin/kill -9 $licenseManager >/dev/null 2>&1");
 		}
 	}
 	
	
 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";	
	return implode("\n",$l);return;	
	
}

// ========================================================================================================================================================
function proxy_pac_status(){
	
	
		
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){return null;}
	
	
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SquidEnableProxyPac");
	if($enabled==null){$enabled=0;}
	if($SQUIDEnable==0){$enabled=0;}
	
	$master_pid=trim(@file_get_contents("/var/run/proxypac.pid"));
	
	$l[]="[APP_PROXY_PAC]";
	$l[]="service_name=APP_PROXY_PAC";
 	$l[]="master_version=1.00";
 	$l[]="service_cmd=proxy-pac";	
 	$l[]="service_disabled=$enabled";
 	
	if($enabled==0){return implode("\n",$l);return;}
 	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";	
	return implode("\n",$l);return;	
	
}

// ========================================================================================================================================================
function wpa_supplicant(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->WPA_SUPPLIANT_INSTALLED){return null;}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("WpaSuppliantEnabled");
	if($enabled==null){$enabled=1;}
	$eth=trim($GLOBALS["CLASS_UNIX"]->GET_WIRELESS_CARD());
	if(trim($eth)==null){$enabled=0;}
	$master_pid=trim(@file_get_contents("/var/run/wpa_supplicant.$eth.pid"));
	$WifiAPEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("WifiAPEnable");
	if($WifiAPEnable<>1){$WifiAPEnable=0;}
	if($WifiAPEnable==0){$enabled=0;}
	
	$l[]="[APP_WPA_SUPPLIANT]";
	$l[]="service_name=APP_WPA_SUPPLIANT";
 	$l[]="master_version=".GetVersionOf("wpa_suppliant");
 	$l[]="service_cmd=wifi";	
 	$l[]="service_disabled=$enabled";
 	$l[]="family=network";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";	
	return implode("\n",$l);return;	
	}
// ========================================================================================================================================================
function fetchmail(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->fetchmail_installed){return null;}
	$EnablePostfixMultiInstance=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePostfixMultiInstance");
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableFetchmail");
	if($enabled==null){$enabled=0;}
	
	
	
	if($EnablePostfixMultiInstance<>1){
		if(!is_file("/etc/fetchmailrc")){$enabled=0;}
		$master_pid=trim(@file_get_contents("/var/run/fetchmail.pid"));
		if(preg_match("#^([0-9]+)#",$master_pid,$re)){$master_pid=$re[1];}
		$l[]="[FETCHMAIL]";
		$l[]="service_name=APP_FETCHMAIL";
	 	$l[]="master_version=".GetVersionOf("fetchmail");
	 	$l[]="service_cmd=fetchmail";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=mailbox";
	 	
	 	if($enabled==1){
 			$fetchmail_count_server=fetchmail_count_server();
 			if($GLOBALS["VERBOSE"]){echo "fetchmail_count_server: $fetchmail_count_server\n";}
	 			 		
	 		if($fetchmail_count_server>0){	
	 			if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
					WATCHDOG("APP_FETCHMAIL","fetchmail");
					$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);
					return;
				}
	 		}
	 	}

	 	
	 	
		if($enabled==0){return implode("\n",$l);return;}
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			$l[]="running=0";}else{$l[]="running=1";$l[]=GetMemoriesOf($master_pid);}
		
		
		
		$l[]="";
		
		
		
	}else{
		$enabled=1;
	}
	
	$master_pid=trim(@file_get_contents("/etc/artica-postfix/exec.fetmaillog.php.pid"));
	$l[]="[FETCHMAIL_LOGGER]";
	$l[]="service_name=APP_FETCHMAIL_LOGGER";
 	$l[]="master_version=".GetVersionOf("fetchmail");
 	$l[]="service_cmd=fetchmail-logger";	
 	$l[]="service_disabled=$enabled";
 	$l[]="watchdog_features=1";
 	
	if($enabled==1){
	 		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
	 			$fetchmail_count_server=fetchmail_count_server();
	 			if($GLOBALS["VERBOSE"]){echo "fetchmail_count_server: $fetchmail_count_server\n";}
	 			if($fetchmail_count_server>0){
					WATCHDOG("APP_FETCHMAIL_LOGGER","fetchmail-logger");
					$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);
					return;
	 			}else{
	 				return implode("\n",$l);return;
	 			}
			}
	 	}		 	 	
 	
	if($enabled==0){return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";	
	return implode("\n",$l);return;	
	}
	
function fetchmail_count_server(){
	$f=explode("\n",@file_get_contents("/etc/fetchmailrc"));
	$count=0;
	while (list ( $i,$line) = each ($f)){if(preg_match("#^poll\s+(.+)#",$line)){$count=$count+1;}}
	return $count;
}
	
//========================================================================================================================================================	
function milter_greylist(){
	
	if(!$GLOBALS["CLASS_USERS"]->MILTERGREYLIST_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:milter_greylist(): Not installed\n";}
		return null;
	
	
	}
	$EnablePostfixMultiInstance=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePostfixMultiInstance");
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("MilterGreyListEnabled");
	$EnableASSP=$GLOBALS["CLASS_SOCKETS"]->GET_INFO('EnableASSP');
	if($enabled==null){$enabled=0;}
	if($EnablePostfixMultiInstance==1){$enabled=0;}
	if($EnableASSP==1){$enabled=0;}
	
	
	if($GLOBALS["VERBOSE"]){echo "DEBUG: EnablePostfixMultiInstance: $EnablePostfixMultiInstance\n";}
	if($GLOBALS["VERBOSE"]){echo "DEBUG: EnableASSP: $EnableASSP\n";}
	if($GLOBALS["VERBOSE"]){echo "DEBUG: enabled: $enabled\n";}
	$pid_path=trim(GetVersionOf("milter-greylist-pid"));
	if($GLOBALS["VERBOSE"]){echo "DEBUG: pid path: $pid_path\n";}
	
	if($pid_path==null){
		$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_MILTER_GREYLIST_PID();
		if($GLOBALS["VERBOSE"]){echo "DEBUG: ->LOCATE_MILTER_GREYLIST_PID()= pid path: $pid_path\n";}
		}
		$master_pid=trim(@file_get_contents($pid_path));
		if($GLOBALS["VERBOSE"]){echo "DEBUG: ->LOCATE_MILTER_GREYLIST_PID()= master pid: $master_pid\n";}
		$l[]="[MILTER_GREYLIST]";
		$l[]="service_name=APP_MILTERGREYLIST";
	 	$l[]="master_version=".GetVersionOf("milter-greylist");
	 	$l[]="service_cmd=mgreylist";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="remove_cmd=--milter-grelist-remove";
	 	$l[]="family=postfix";
	 	if($enabled==0){return implode("\n",$l);return;}
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_MILTERGREYLIST","mgreylist");
			$l[]="running=0";
			$l[]="installed=1\n";
			return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function assp(){
	$users=new settings_inc();

	if(!$GLOBALS["CLASS_USERS"]->ASSP_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:assp(): Not installed\n";}
		return null;
	
	
	}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO('EnableASSP');
	if($enabled==null){$enabled=0;}

	if($GLOBALS["VERBOSE"]){echo "DEBUG: EnableASSP..: $enabled\n";}
	$pid_path="/usr/share/assp/pid";
	if($GLOBALS["VERBOSE"]){echo "DEBUG: pid path....: $pid_path\n";}
	$master_pid=trim(@file_get_contents($pid_path));
	if($GLOBALS["VERBOSE"]){echo "DEBUG: master pid..: $master_pid\n";}
		$l[]="[ASSP]";
		$l[]="service_name=APP_ASSP";
	 	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ASSP_VERSION();
	 	$l[]="service_cmd=assp";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	if($enabled==0){return implode("\n",$l);return;}
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_ASSP","assp");
			$l[]="running=0";
			$l[]="installed=1\n";
			return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function framework(){
		$pid_path="/var/run/lighttpd/framework.pid";
		$master_pid=trim(@file_get_contents($pid_path));
		if($master_pid==null){
			$lighttpd=$GLOBALS["CLASS_UNIX"]->find_program("lighttpd");
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN("lighttpd -f /etc/artica-postfix/framework.conf");
			if($master_pid<>null){@file_put_contents("/var/run/lighttpd/framework.pid",$master_pid);}
		}
		
		
		$l[]="[FRAMEWORK]";
		$l[]="service_name=APP_FRAMEWORK";
	 	$l[]="master_version=".GetVersionOf("lighttpd");
	 	$l[]="service_cmd=apache";	
	 	$l[]="service_disabled=1";
	 	$l[]="watchdog_features=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="family=system";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_FRAMEWORK","apache");
			$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function squidguardweb(){
		$pid_path="/var/run/lighttpd/squidguard-lighttpd.pid";
		$master_pid=trim(@file_get_contents($pid_path));
		if($master_pid==null){
			$lighttpd=$GLOBALS["CLASS_UNIX"]->find_program("lighttpd");
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN("lighttpd -f /etc/artica-postfix/squidguard-lighttpd.conf");
			if($master_pid<>null){@file_put_contents("/var/run/lighttpd/squidguard-lighttpd.pid",$master_pid);}
		}
		
		$squidGuardEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("squidGuardEnabled");
		$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
		$EnableUfdbGuard=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableUfdbGuard");
		$EnableSquidClamav=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSquidClamav");
		if($EnableUfdbGuard==1){$squidGuardEnabled=1;}
		if($EnableSquidClamav==1){$squidGuardEnabled=1;}
		if($SQUIDEnable==null){$SQUIDEnable=1;}
		$EnableSquidGuardHTTPService=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSquidGuardHTTPService");
		if($EnableSquidGuardHTTPService==null){$EnableSquidGuardHTTPService=1;}
		if($EnableSquidGuardHTTPService<>1){$squidGuardEnabled=0;}
		
		
		
		if($SQUIDEnable==0){$squidGuardEnabled=0;}
		if($squidGuardEnabled==null){$squidGuardEnabled=0;}
	
		$l[]="[APP_SQUIDGUARD_HTTP]";
		$l[]="service_name=APP_SQUIDGUARD_HTTP";
	 	$l[]="master_version=".GetVersionOf("lighttpd");
	 	$l[]="service_cmd=squidguard-http";	
	 	$l[]="service_disabled=$squidGuardEnabled";
	 	$l[]="watchdog_features=1";
	 	$l[]="pid_path=$pid_path";
	 	
		if($squidGuardEnabled==0){
	 		return implode("\n",$l);
	 		return;
	 		}	 	
	 		 	
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_SQUIDGUARD_HTTP","squidguard-http");
			$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================

function ufdbguardd(){

	
	if(!$GLOBALS["CLASS_USERS"]->APP_UFDBGUARD_INSTALLED){return;}
	
	
		$pid_path="/var/tmp/ufdbguardd.pid";
		if(!is_dir("/var/tmp")){@mkdir("/var/tmp");}
		$ufdbguardd_path=$GLOBALS["CLASS_UNIX"]->find_program("ufdbguardd");
		
		$master_pid=trim(@file_get_contents($pid_path));
		$EnableUfdbGuard=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableUfdbGuard");
		if($GLOBALS["VERBOSE"]){echo "EnableUfdbGuard=$EnableUfdbGuard\n";}

		$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
		if($SQUIDEnable==null){$SQUIDEnable=1;}	
		if($EnableUfdbGuard==null){$EnableUfdbGuard=0;}	
		if($SQUIDEnable==0){$SQUIDEnable=0;}
		
		if($GLOBALS["VERBOSE"]){echo "SQUIDEnable=$SQUIDEnable\n";}
		
		$l[]="[APP_UFDBGUARD]";
		$l[]="service_name=APP_UFDBGUARD";
	 	$l[]="master_version=".GetVersionOf("ufdbguardd");
	 	$l[]="service_cmd=ufdb";	
	 	$l[]="service_disabled=$EnableUfdbGuard";
	 	$l[]="watchdog_features=1";
	 	$l[]="pid_path=$pid_path";
	 	
		if($EnableUfdbGuard==0){
	 		return implode("\n",$l);
	 		return;
	 		}	 	
	 		 	
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
	 		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($ufdbguardd_path);
	 		@file_put_contents($pid_path,$master_pid);
	 	}
	 		
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
	 		WATCHDOG("APP_UFDBGUARD","ufdb");
			$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;	
	
}
//========================================================================================================================================================

function ufdbguardd_tail(){

	
		if(!$GLOBALS["CLASS_USERS"]->APP_UFDBGUARD_INSTALLED){return;}
	
	
		$pid_path="/etc/artica-postfix/exec.ufdbguard-tail.php.pid";
		$master_pid=trim(@file_get_contents($pid_path));
		$EnableUfdbGuard=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableUfdbGuard");
		if($GLOBALS["VERBOSE"]){echo "EnableUfdbGuard=$EnableUfdbGuard\n";}

		$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
		if($SQUIDEnable==null){$SQUIDEnable=1;}	
		if($EnableUfdbGuard==null){$EnableUfdbGuard=0;}	
		if($SQUIDEnable==0){$SQUIDEnable=0;}
		
		if($GLOBALS["VERBOSE"]){echo "SQUIDEnable=$SQUIDEnable\n";}
		
		$l[]="[APP_UFDBGUARD_TAIL]";
		$l[]="service_name=APP_UFDBGUARD_TAIL";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=ufdb-tail";	
	 	$l[]="service_disabled=$EnableUfdbGuard";
	 	$l[]="watchdog_features=1";
	 	$l[]="pid_path=$pid_path";
	 	
	 	$filetime=file_time_min("/var/log/artica-postfix/ufdbguard-tail.debug");
	 	events("ufdbguard-tail.debug {$filetime}Mn ",__FUNCTION__,__LINE__);
	 	if($filetime>5){
	 		events("-> restart ufdb-tail",__FUNCTION__,__LINE__);
	 		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ufdb-tail");
	 	}
	 	
		if($EnableUfdbGuard==0){
	 		return implode("\n",$l);
	 		return;
	 		}	 	
	 		 	
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_UFDBGUARD_TAIL","ufdb-tail");
			$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;	
	
}




function pdns_server(){
	$verbose=$GLOBALS["VERBOSE"];
	if(!$GLOBALS["CLASS_USERS"]->POWER_DNS_INSTALLED){if($verbose){echo "POWER_DNS_INSTALLED -> FALSE, return\n";}}
	
	
	if(!$GLOBALS["CLASS_USERS"]->POWER_DNS_INSTALLED){return null;}
	
	
	$enabled=1;
	$DisablePowerDnsManagement=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DisablePowerDnsManagement");
	
	
	$EnablePDNS=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePDNS");
	$PDNSRestartIfUpToMB=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("PDNSRestartIfUpToMB");
	if($DisablePowerDnsManagement==1){$enabled=0;}
	if(!is_numeric($EnablePDNS)){$EnablePDNS=1;}
	if(!is_numeric($PDNSRestartIfUpToMB)){$PDNSRestartIfUpToMB=700;}
	

	
	$pdns_server=$GLOBALS["CLASS_UNIX"]->find_program("pdns_server");
	
	if($verbose){echo "DisablePowerDnsManagement=$DisablePowerDnsManagement\n";}
	if($verbose){echo "EnablePDNS=$EnablePDNS\n";}
	if($verbose){echo "PDNSRestartIfUpToMB=$PDNSRestartIfUpToMB\n";}
	if($verbose){echo "pdns_server=$pdns_server\n";}
	
	if($pdns_server==null){
		if($verbose){echo "pdns_server no such binary\n";}
		return null;
	}
	
		$pid_path="/var/run/pdns/pdns.pid";
		$master_pid=trim(@file_get_contents($pid_path));
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($pdns_server);
			if($master_pid<>null){@file_put_contents($pid_path,$master_pid);}
		}
		
		if($enabled==1){
			if($EnablePDNS==0){$enabled=0;}
		}
		$version=GetVersionOf("pdns");
		$GLOBALS["PDNS_VERSION"]=$version;
		if($verbose){echo "version=$version Enabled=$enabled\n";}
		
		$l[]="[APP_PDNS]";
		$l[]="service_name=APP_PDNS";
	 	$l[]="master_version=$version";
	 	$l[]="service_cmd=pdns";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=network";
	 	
	 	if($enabled==0){
	 		if($verbose){echo "-> pdns_instance()";}
	 		$instance=pdns_instance();
	 		return implode("\n",$l).$instance;
	 	}		 	
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			if($verbose){echo "-> pid: [$master_pid] failed -> watchdog";}
			WATCHDOG("APP_PDNS","pdns");
			$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	if($verbose){echo "-> pdns_instance()";}
	$instance=pdns_instance();
	return implode("\n",$l).$instance;return;		
	
}

function pdns_instance(){
	$verbose=$GLOBALS["VERBOSE"];
	$master_pid=null;
	$PDNSRestartIfUpToMB=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("PDNSRestartIfUpToMB");
	$pdns_server=$GLOBALS["CLASS_UNIX"]->find_program("pdns_server");
	if($pdns_server==null){if($verbose){echo "pdns_server no such binary\n";}return null;}	
	
		$pidof=$GLOBALS["CLASS_UNIX"]->find_program("pidof");
		$cmd="$pidof $pdns_server-instance 2>&1";
		exec($cmd,$results);
		if($verbose){echo "$cmd return ". count($results)." rows\n";}
		while (list ($num, $ligne) = each ($results) ){
			if(trim($ligne)==null){continue;}
			if(preg_match("#^([0-9]+)#",$ligne,$re)){
				if($GLOBALS["CLASS_UNIX"]->process_exists($re[1])){$master_pid=$re[1];break;}
				}
			}
		

		if(!is_numeric($master_pid)){
			$results=array();
			$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
			$cmd="$pgrep -l -f $pdns_server-instance 2>&1";
			exec($cmd,$results);
			if($verbose){echo "$cmd return ". count($results)." rows\n";}
			while (list ($num, $ligne) = each ($results) ){
				if(trim($ligne)==null){continue;}
				if(preg_match("#^([0-9]+)\s+.+?pdns#",$ligne,$re)){	
					if($GLOBALS["CLASS_UNIX"]->process_exists($re[1])){$master_pid=$re[1];break;}
				}
			}
		}
		
	
	
		if($GLOBALS["VERBOSE"]){echo "$pdns_server-instance -> $master_pid\n";}
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){return;}
		
		$PROCESS_MEMORY=$GLOBALS["CLASS_UNIX"]->PROCESS_MEMORY($master_pid,true);
		$PROCESS_CACHE_MEMORY=$GLOBALS["CLASS_UNIX"]->PROCESS_CACHE_MEMORY($master_pid,true);
		$PDNSRestartIfUpToMBOrg=$PDNSRestartIfUpToMB;
		if($PDNSRestartIfUpToMB>0){
		$PDNSRestartIfUpToMB=$PDNSRestartIfUpToMB*1024;
		
		if($verbose){echo "PROCESS_MEMORY:{$PROCESS_MEMORY}KB against {$PDNSRestartIfUpToMB}KB\n";}
		
		if($PROCESS_MEMORY>$PDNSRestartIfUpToMB){
			$PROCESS_MEMORY_EX=round($PROCESS_MEMORY/1024,2);
			$GLOBALS["CLASS_UNIX"]->send_email_events("Watchdog: PowerDNS reach Max memory !!! ({$PROCESS_MEMORY_EX}M/{$PDNSRestartIfUpToMBOrg}M)","PowerDNS service was restarted","system");
			$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
			shell_exec(trim("$nohup /etc/init.d/pdns restart >/dev/null 2>&1 &"));
		}}	
		$l[]="";
		$l[]="";
		$l[]="[APP_PDNS_INSTANCE]";
		$l[]="service_name=APP_PDNS_INSTANCE";
	 	$l[]="master_version={$GLOBALS["PDNS_VERSION"]}";
	 	$l[]="service_cmd=pdns";	
	 	$l[]="service_disabled=1";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=network";		
		$l[]="running=1";
		$l[]="master_memory=$PROCESS_MEMORY";
		$l[]="master_cached_memory=$PROCESS_CACHE_MEMORY";
		$l[]="processes_number=1";
		$l[]="master_pid=$master_pid";
		$l[]="running=1\ninstalled=1";
		$l[]="";	
		return implode("\n",$l);
}

//========================================================================================================================================================
function pdns_recursor(){
	
	
	if(!$GLOBALS["CLASS_USERS"]->POWER_DNS_INSTALLED){return null;}
	$enabled=1;
	$DisablePowerDnsManagement=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DisablePowerDnsManagement");
	if($DisablePowerDnsManagement==1){$enabled=0;}	
	$pdns_server=$GLOBALS["CLASS_UNIX"]->find_program("pdns_recursor");
	if($pdns_server==null){return null;}
	if(!is_file($pdns_server)){return null;}
	$EnablePDNS=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePDNS");
	if(!is_numeric($EnablePDNS)){$EnablePDNS=1;}	
	
	$pid_path="/var/run/pdns/pdns_recursor.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($pdns_server);
		if($master_pid<>null){@file_put_contents($pid_path,$master_pid);}
	}
	
		
		if($enabled==1){
			if($EnablePDNS==0){$enabled=0;}
		}
		
		$l[]="[PDNS_RECURSOR]";
		$l[]="service_name=APP_PDNS_RECURSOR";
	 	$l[]="master_version=".GetVersionOf("pdns");
	 	$l[]="service_cmd=pdns";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=network";
	 	
	 	if($enabled==0){
	 		return implode("\n",$l);
	 		return;
	 	}		 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_PDNS_RECURSOR","pdns");
			$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}

//========================================================================================================================================================
function cyrus_imap(){
	if(!$GLOBALS["CLASS_USERS"]->cyrus_imapd_installed){return null;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_CYRUS_PIDPATH();
	$master_pid=trim(@file_get_contents($pid_path));
	$enabled=1;
	if($GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){$enabled=0;}
		$l[]="[CYRUSIMAP]";
		$l[]="service_name=APP_CYRUS";
	 	$l[]="master_version=".GetVersionOf("cyrus-imap");
	 	$l[]="service_cmd=imap";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=mailbox";
	 	$l[]="service_disabled=$enabled";
		if($enabled==0){
	 		return implode("\n",$l);
	 		return;
	 	}		 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_CYRUS",'imap');
			$l[]="running=0\ninstalled=1";$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}


function mysql_watchdog(){
	$mysqladmin=$GLOBALS["CLASS_UNIX"]->find_program("mysqladmin");
	$zarafa_enabled=0;
	if($GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		$zarafa_enabled=1;
		$pid_path="/var/run/zarafa-server.pid";	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists(@file_get_contents($pid_path))){
			events("Zarafa is installed but did not running...",__FUNCTION__,__LINE__);
			$zarafa_enabled=0;
		}
	}	
	
	$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
	$countq=array();
	if(!is_file($mysqladmin)){
		events("mysqladmin no such file",__FUNCTION__,__LINE__);
		return;
	}
	
	exec("$mysqladmin processlist 2>&1",$results);
	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#\|\s+([0-9]+)\s+\|.+?\|.+?\|\s+(.+?)\s+\|.+?\|.+?\|\s+(.+?)\s+|(.+?)\|#",$line,$re)){
			$ID=$re[1];
			$DB=$re[2];
			$State=$re[3];
			$QUERY=$re[4];
			if($QUERY==null){continue;}
			$notifs[]="$ID db:$DB ($State) query:$QUERY";
			$md5=md5("$DB$State$QUERY");
			if(!isset($countq[$md5])){$countq[$md5]=1;}else{$countq[$md5]=$countq[$md5]+1;}
			events("$ID db:$DB ($State) $QUERY count({$countq[$md5]}) zarafa:$zarafa_enabled",__FUNCTION__,__LINE__);
			if($countq[$md5]>10){
				events("Too many same processes",__FUNCTION__,__LINE__);
				$text="It seems that the mysql server using many threads.
				this is what artica has detected:
				
				".@implode("\n",$notifs)."
				--------------------------------------------------------------------------
				Process dump :
				" .@implode("\n",$results)."\n";
			
				if($zarafa_enabled==0){
					$GLOBALS["CLASS_UNIX"]->send_email_events("Mysql too many queries (restarting mysql)",$text."\nMysql has been restarted","system");
					shell_exec(trim("$nohup /etc/init.d/artica-postfix restart mysql >/dev/null 2>&1 &"));
				}else{
					$GLOBALS["CLASS_UNIX"]->send_email_events("Mysql many queries (information)",$text,"system");
				}
			}
		}
	}
}




//========================================================================================================================================================
function mysql_server(){
		
		
		if(!$GLOBALS["CLASS_USERS"]->mysql_installed ){return;}
		$program_path=$GLOBALS["CLASS_UNIX"]->find_program("mysqld");
		if($program_path==null){
			if(is_file("/usr/sbin/mysqld")){$program_path="/usr/sbin/mysqld";}		
		}
		$pid_path=GetVersionOf("mysql-pid");
		if($pid_path==null){
			if($GLOBALS["VERBOSE"]){echo "Pid path is null -> PIDOF($program_path)";}
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($program_path);
		}else{
			$master_pid=trim(@file_get_contents($pid_path));
		}
		
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			if($GLOBALS["VERBOSE"]){echo "Pid $master_pid not in memory -> PIDOF($program_path)\n";}
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($program_path);
		}
		
		
		$l[]="[ARTICA_MYSQL]";
		$l[]="service_name=APP_MYSQL_ARTICA";
	 	$l[]="master_version=".GetVersionOf("mysql-ver");
	 	$l[]="service_cmd=mysql";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="bin_path=$program_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_MYSQL_ARTICA",'mysql');
			$l[]="";return implode("\n",$l);return;
		}else{
			mysql_watchdog();
		}
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
		
		
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function mysql_mgmt(){
	if(!$GLOBALS["CLASS_USERS"]->mysql_installed ){return;}
	$program=$GLOBALS["CLASS_UNIX"]->find_program("ndb_mgmd");
	if($program==null){return;}
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($program);
	$EnableMysqlClusterManager=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableMysqlClusterManager");
	if(!is_numeric($EnableMysqlClusterManager)){$EnableMysqlClusterManager=0;}
	if($EnableMysqlClusterManager==0){return;}
	$l[]="[MYSQL_CLUSTER_MGMT]";
	$l[]="service_name=APP_MYSQL_CLUSTER_MGMT";
	$l[]="master_version=".GetVersionOf("mysql-ver");
	$l[]="service_cmd=mysql-cluster";	
	$l[]="service_disabled=$EnableMysqlClusterManager";
	$l[]="pid_path=$pid_path";
	$l[]="family=system";
	
	if($EnableMysqlClusterManager==0){
	 		return implode("\n",$l);
	 		return;
	 	}	
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function mysql_replica(){
	if(!$GLOBALS["CLASS_USERS"]->mysql_installed ){return;}
	$program=$GLOBALS["CLASS_UNIX"]->find_program("ndbd");
	if($program==null){return;}
	$EnableMysqlClusterReplicat=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableMysqlClusterReplicat");
	if(!is_numeric($EnableMysqlClusterReplicat)){$EnableMysqlClusterReplicat=0;}	
	
	
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($program);
		$l[]="[MYSQL_CLUSTER_REPLICA]";
		$l[]="service_name=APP_MYSQL_CLUSTER_REPLICA";
	 	$l[]="master_version=".GetVersionOf("mysql-ver");
	 	$l[]="service_cmd=mysql-cluster";	
	 	$l[]="service_disabled=$EnableMysqlClusterReplicat";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="family=system";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="";return implode("\n",$l);return;}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function openldap(){
	$users=new settings_inc();
	if(!$GLOBALS["CLASS_USERS"]->openldap_installed){return;}
	$pid_path=GetVersionOf("openldap-pid");
	$master_pid=trim(@file_get_contents($pid_path));
	$bin_path=$GLOBALS["CLASS_UNIX"]->LOCATE_SLPAD_PATH();
	
	if($GLOBALS["VERBOSE"]){
		echo "pid_path = $pid_path\n";
		echo "master_pid = $master_pid\n";
		echo "bin_path = $bin_path\n";
	}
	
	$l[]="[LDAP]";
	$l[]="service_name=APP_LDAP";
	$l[]="master_version=".GetVersionOf("openldap");
	$l[]="service_cmd=ldap";	
	$l[]="service_disabled=1";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=1";
	$l[]="family=system";
	$l[]="bin_path=$bin_path";
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			$master_pid=trim($GLOBALS["CLASS_UNIX"]->PIDOF($bin_path));
			if($master_pid>3){
				@file_put_contents($pid_path,$master_pid);
			}
		}

		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){	
			WATCHDOG("APP_LDAP","ldap");
			$l[]="";return implode("\n",$l);return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function saslauthd(){
		$pid_path=GetVersionOf("saslauthd-pid");
		$master_pid=trim(@file_get_contents($pid_path));
		$l[]="[SASLAUTHD]";
		$l[]="service_name=APP_SASLAUTHD";
	 	$l[]="master_version=".GetVersionOf("saslauthd");
	 	$l[]="service_cmd=saslauthd";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="family=system";
	 	$l[]="watchdog_features=1";
	 	
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
	 		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($GLOBALS["CLASS_UNIX"]->find_program("saslauthd"));
	 	}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_SASLAUTHD","saslauthd");
			$l[]="";return implode("\n",$l);return;
		}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function syslogger(){
	CheckCallable();
	$pid_path="/etc/artica-postfix/exec.syslog.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	$service_disabled=1;
	
	
	$l[]="[APP_SYSLOGER]";
	$l[]="service_name=APP_SYSLOGER";
	$l[]="master_version=".trim(@file_get_contents(dirname(__FILE__)."/VERSION"));
	$l[]="service_cmd=sysloger";	
	$l[]="service_disabled=1";
	$l[]="watchdog_features=1";
	$l[]="family=system";
	$l[]="pid_path=$pid_path";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_SYSLOGER","sysloger");
			$l[]="";return implode("\n",$l);
			events("done",__FUNCTION__,__LINE__);
			return;
	}
	
		
	if(!is_file("/var/log/artica-postfix/syslogger.debug")){
		events("restart sysloger",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart sysloger");
	}
		
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
		
	if(!$GLOBALS["DISABLE_WATCHDOG"]){
		$time=file_time_min("/var/log/artica-postfix/syslogger.debug");
		writelogs("LOG TIME: $time",__FUNCTION__,__FILE__,__LINE__);
		if($time>5){
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart sysloger");
		}
	}		
	events("done",__FUNCTION__,__LINE__);
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function squid_tail(){
	if(!$GLOBALS["CLASS_USERS"]->SQUID_INSTALLED){return;}
	$DansGuardianEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DansGuardianEnabled");
	if($DansGuardianEnabled==1){return;}
	$SQUIDEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SQUIDEnable");
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	$RAM=$GLOBALS["MEMORY_INSTALLED"]["ram"]["total"];
	$service_disabled=1;
	if($RAM<512000){$SQUIDEnable=0;}		
	
	if($SQUIDEnable==0){return;}
	

	
	$pid_path="/etc/artica-postfix/exec.squid-tail.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
		$l[]="[APP_ARTICA_SQUID_TAIL]";
		$l[]="service_name=APP_ARTICA_SQUID_TAIL";
	 	$l[]="master_version=".trim(@file_get_contents(dirname(__FILE__)."/VERSION"));
	 	$l[]="service_cmd=squid-tail";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=squid";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_ARTICA_SQUID_TAIL","squid-tail");
			$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//==//========================================================================================================================================================
function auth_tail(){
	
	$EnableSSHD=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSSHD");
	if(strlen($GLOBALS["CLASS_USERS"]->LOCATE_AUTHLOG_PATH)==0){return;}
	if(!is_numeric($EnableSSHD)){$EnableSSHD=1;}
	if($EnableSSHD==0){return;}
	

	
	$pid_path="/etc/artica-postfix/exec.auth-tail.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
		$l[]="[APP_ARTICA_AUTH_TAIL]";
		$l[]="service_name=APP_ARTICA_AUTH_TAIL";
	 	$l[]="master_version=".trim(@file_get_contents(dirname(__FILE__)."/VERSION"));
	 	$l[]="service_cmd=auth-logger";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=squid";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_ARTICA_AUTH_TAIL","auth-logger");
			$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//==//========================================================================================================================================================
function amavis(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("amavisd");
	if($bin_path==null){return null;}
	$pid_path="/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	$GLOBALS["DEBUG_LOGS"][]="$pid_path = $master_pid";

	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAmavisDaemon");
		$l[]="[AMAVISD]";
		$l[]="service_name=APP_AMAVISD_NEW";
	 	$l[]="master_version=".GetVersionOf("amavis");
	 	$l[]="service_cmd=amavis";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="family=postfix";
	 	$l[]="master_pid=$master_pid";
	 	$l[]="watchdog_features=1";
	 	
	 	if($enabled==0){
	 		return implode("\n",$l);
	 		return;
	 	}
	 	
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
	 			$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
	 			exec("$pgrep -l -f \"amavisd\s+\(master\)\" 2>&1",$results);
	 			while (list ($num, $line) = each ($results) ){
	 				$GLOBALS["DEBUG_LOGS"][]="$pgrep = $line";
	 				if(preg_match("#([0-9]+)\s+amavis#", $line,$re)){
	 					$GLOBALS["DEBUG_LOGS"][]="$pgrep = PID:{$re[1]}";
	 					if($GLOBALS["CLASS_UNIX"]->process_exists($re[1])){$master_pid=$re[1];break;}
	 				}
	 			}
	 	}
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			$GLOBALS["CLASS_UNIX"]->send_email_events("Amavisd-new stopped (watchdog)",
			"Artica will try to start it\n".@implode("\n", $GLOBALS["DEBUG_LOGS"]),"postfix");
			unset($GLOBALS["DEBUG_LOGS"]);
			WATCHDOG("APP_AMAVISD_NEW","amavis");
			$l[]="";return implode("\n",$l);
			return;
		}	
		unset($GLOBALS["DEBUG_LOGS"]);
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
		AmavisWatchdog();
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function amavis_milter(){
	
	
	
	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("amavisd-milter");
	$EnableAmavisDaemon=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAmavisDaemon");
	$EnableAmavisInMasterCF=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAmavisInMasterCF");
	$EnablePostfixMultiInstance=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePostfixMultiInstance");
	
	if($bin_path==null){return null;}
	$pid_path="/var/spool/postfix/var/run/amavisd-milter/amavisd-milter.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	if($EnableAmavisInMasterCF==1){$EnableAmavisDaemon=0;}
	if($EnablePostfixMultiInstance==1){$EnableAmavisDaemon=0;}
	
		$l[]="[AMAVISD_MILTER]";
		$l[]="service_name=APP_AMAVISD_MILTER";
	 	$l[]="master_version=".GetVersionOf("amavis");
	 	$l[]="service_cmd=amavis-milter";	
	 	$l[]="service_disabled=$EnableAmavisDaemon";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	
	 	if($EnableAmavisDaemon==0){
	 		return implode("\n",$l);
	 		return;
	 	}	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_AMAVISD_MILTER","amavis-milter");
			$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function lighttpd(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("lighttpd");
	$EnableLighttpd=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableLighttpd");
	$ApacheArticaEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ApacheArticaEnabled");
	$LighttpdArticaDisabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("LighttpdArticaDisabled");
	if($ApacheArticaEnabled==1){$EnableLighttpd=0;}
	if(!is_numeric($LighttpdArticaDisabled)){$LighttpdArticaDisabled=0;}
	if(!is_numeric($EnableLighttpd)){$EnableLighttpd=1;}
	if($bin_path==null){return null;}
	
		if($LighttpdArticaDisabled==1){$EnableLighttpd=0;}

	
		$l[]="[LIGHTTPD]";
		$l[]="service_name=APP_LIGHTTPD";
	 	$l[]="master_version=".GetVersionOf("lighttpd-version");
	 	$l[]="service_cmd=apache";	
	 	$l[]="service_disabled=$EnableLighttpd";
	 	
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
	 	
	 	if($EnableLighttpd==0){
	 		return implode("\n",$l);
	 		return;
	 	}		 	
	 	
		$pid_path=GetVersionOf("lighttpd-pid");
		$master_pid=trim(@file_get_contents($pid_path));
		if($master_pid==null){
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN("$bin_path -f /etc/lighttpd/lighttpd.conf");
		}	

		$l[]="pid_path=$pid_path";
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_LIGHTTPD","apache");
			$l[]="";return implode("\n",$l);return;
		}
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function boa(){
	$pid_path="/etc/artica-postfix/boa.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[BOA]";
		$l[]="service_name=APP_BOA";
	 	$l[]="master_version=0.94.13";
	 	$l[]="service_cmd=boa";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_BOA","boa");
			$l[]="";return implode("\n",$l);return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function fcron1(){
	$pid_path="/var/run/artica-postfix.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[ARTICA]";
		$l[]="service_name=APP_ARTICA";
	 	$l[]="master_version=".GetVersionOf("fcron");
	 	$l[]="service_cmd=fcron";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
	 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
	 		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF("/usr/share/artica-postfix/bin/artica-cron");
	 	}
	 	
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_ARTICA","fcron");
			$l[]="";return implode("\n",$l);return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function fcron2(){
	$pid_path="/etc/artica-cron/artica-watchdog.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[ARTICA_WATCHDOG]";
		$l[]="service_name=APP_ARTICA_WATCHDOG";
	 	$l[]="master_version=".GetVersionOf("fcron");
	 	$l[]="service_cmd=watchdog";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_ARTICA_WATCHDOG","watchdog");
			$l[]="";return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function clammilter(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("clamav-milter");
	$ClamavMilterEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ClamavMilterEnabled");
	if($ClamavMilterEnabled==null){$ClamavMilterEnabled=0;}
	if($bin_path==null){return null;}
	$pid_path="/var/spool/postfix/var/run/clamav/clamav-milter.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[CLAMAV_MILTER]";
		$l[]="service_name=APP_CLAMAV_MILTER";
	 	$l[]="master_version=".GetVersionOf("clamav");
	 	$l[]="service_cmd=clammilter";	
	 	$l[]="service_disabled=$ClamavMilterEnabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	
		if($ClamavMilterEnabled==0){$l[]="";$l[]="";return implode("\n",$l);return;}
		
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_CLAMAV_MILTER","clammilter");
			$l[]="";return implode("\n",$l);
			return;
		}			
	 	
		
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function clamscan(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("clamscan");
	if($bin_path==null){return null;}
	$master_pid=1;
	
	
		$l[]="[CLAMSCAN]";
		$l[]="service_name=APP_CLAMSCAN";
	 	$l[]="master_version=".GetVersionOf("clamav");
	 	$l[]="service_cmd=";	
	 	$l[]="service_disabled=1";
	 	$l[]="family=system";
	 	$l[]="pid_path=";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="";return implode("\n",$l);return;}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function clamd(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("clamd");
	$EnableClamavDaemon=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableClamavDaemon");
	if($bin_path==null){return null;}
	$pid_path=GetVersionOf("clamd-pid");
	$master_pid=trim(@file_get_contents($pid_path));
	if($EnableClamavDaemon==null){$EnableClamavDaemon=1;}
	if(is_file("/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE")){$EnableClamavDaemon=0;}
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
		$l[]="[CLAMAV]";
		$l[]="service_name=APP_CLAMAV";
	 	$l[]="master_version=".GetVersionOf("clamav");
	 	$l[]="service_cmd=clamd";	
	 	$l[]="service_disabled=$EnableClamavDaemon";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="binpath=$bin_path";
	 	$l[]="family=system";
	 	$l[]="watchdog_features=1";
		$l[]="";
		
		if($EnableClamavDaemon==0){
			if($GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.artica-postfix stop clamd");
			}
		}
		
		if($EnableClamavDaemon==0){$l[]="";return implode("\n",$l);return;}
		
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_CLAMAV","clamd");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
		$ClamavRefreshDaemonTime=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ClamavRefreshDaemonTime");
		if(!is_numeric($ClamavRefreshDaemonTime)){$ClamavRefreshDaemonTime=60;}
		$ClamavRefreshDaemonTime=$ClamavRefreshDaemonTime-1;
		$unix=new unix();
		$time=$unix->PROCCESS_TIME_MIN($master_pid);
		if($time>$ClamavRefreshDaemonTime){
			events("Reboot clamd daemon");
			$cmd=trim("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix restart clamd >/dev/null 2>&1 &");
			shell_exec($cmd);
		}
		
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================

function NetAdsWatchdog(){
	
	$GLOBALS["PHP5"]=LOCATE_PHP5_BIN2();
	$EnableSambaActiveDirectory=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){return;}
	if($EnableSambaActiveDirectory<>1){return;}
	$net=$GLOBALS["CLASS_UNIX"]->LOCATE_NET_BIN_PATH();
	if(!is_file($net)){return;}
	exec("$net ads info 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^(.+?):(.+)#",trim($line),$re)){
			events($line,__FUNCTION__,__LINE__);
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	
	$log=@implode("\n",$results);
	unset($results);
	if($array["KDC server"]==null){
		exec("{$GLOBALS["PHP5"]} /usr/share/artica-postfix/exec.samba.php --build 2>&1",$results);
		
		$text="Artica Watchdog has detected an unlinked AD connection.:
		$log
		This is the result of re-connect operation:
		".@implode("\n",$results);
		
		$GLOBALS["CLASS_UNIX"]->send_email_events(
		"Connection to Active Directory Failed (Action reconnect)",
		$text,
		"system"
		
		);
	}
	
}
//========================================================================================================================================================

function freshclam(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("freshclam");
	$EnableFreshClam=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableFreshClam");
	if($bin_path==null){return null;}
	$pid_path=GetVersionOf("freshclam-pid");
	$master_pid=trim(@file_get_contents($pid_path));
	if($EnableFreshClam==null){$EnableFreshClam=1;}
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}	
	
	if(is_file("/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE")){$EnableFreshClam=0;}
	
		$l[]="[FRESHCLAM]";
		$l[]="service_name=APP_FRESHCLAM";
	 	$l[]="master_version=".GetVersionOf("clamav");
	 	$l[]="service_cmd=freshclam";	
	 	$l[]="service_disabled=$EnableFreshClam";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="family=system";
		$l[]="watchdog_features=1";

		$l[]="";
		
		if($EnableFreshClam==0){
			if($GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.artica-postfix stop freshclam");
			}
		}
		
		if($EnableFreshClam==0){$l[]="";return implode("\n",$l);return;}
		
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_FRESHCLAM","freshclam");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";		
		
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function retranslator_httpd(){
	
	
	
	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("lighttpd");
	$RetranslatorHttpdEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("RetranslatorHttpdEnabled");
	if($bin_path==null){return null;}
	$pid_path="/var/run/lighttpd/lighttpd-retranslator.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	if($RetranslatorHttpdEnabled==null){$RetranslatorHttpdEnabled=0;}
	if($RetranslatorHttpdEnabled==0){return ;}
	
		$l[]="[KRETRANSLATOR_HTTPD]";
		$l[]="service_name=APP_KRETRANSLATOR_HTTPD";
	 	$l[]="master_version=".GetVersionOf("lighttpd-version");
	 	$l[]="service_cmd=retranslator";	
	 	$l[]="service_disabled=$RetranslatorHttpdEnabled";
	 	$l[]="family=system";
	 	$l[]="pid_path=$pid_path";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="";return implode("\n",$l);return;}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function spamassassin_milter(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("spamass-milter");
	$SpamAssMilterEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SpamAssMilterEnabled");
	if($SpamAssMilterEnabled==null){$SpamAssMilterEnabled=0;}
	if($GLOBALS["CLASS_USERS"]->AMAVIS_INSTALLED){
		$EnableAmavisDaemon=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAmavisDaemon");
		if($EnableAmavisDaemon==1){$SpamAssMilterEnabled=0;}
	}
	if($bin_path==null){return null;}
	$pid_path="/var/run/spamass/spamass.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[SPAMASS_MILTER]";
		$l[]="service_name=APP_SPAMASS_MILTER";
	 	$l[]="master_version=".GetVersionOf("spamassmilter-version");
	 	$l[]="service_cmd=spamd";	
	 	$l[]="service_disabled=$SpamAssMilterEnabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="family=postfix";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="";return implode("\n",$l);return;}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function spamassassin(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("spamd");
	$SpamdEnabled=GetVersionOf("spamass-enabled");
	
	if($GLOBALS["CLASS_USERS"]->AMAVIS_INSTALLED){
		$EnableAmavisDaemon=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAmavisDaemon");
		if($SpamdEnabled==1){$SpamdEnabled=0;}
	}		
	
	if(!is_numeric($SpamdEnabled)){$SpamdEnabled=0;}
	if($bin_path==null){return null;}
	$pid_path="/var/run/spamd.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[SPAMASSASSIN]";
		$l[]="service_name=APP_SPAMASSASSIN";
	 	$l[]="master_version=".GetVersionOf("spamass");
	 	$l[]="service_cmd=spamd";	
	 	$l[]="service_disabled=$SpamdEnabled";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	
	 	if($SpamdEnabled==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_SPAMASSASSIN","spamd");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function tomcat(){
	if(!$GLOBALS["CLASS_USERS"]->TOMCAT_INSTALLED){return;}
	
	$TomcatEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("TomcatEnable");
	if(!is_numeric($TomcatEnable)){$TomcatEnable=1;}
	if($GLOBALS["CLASS_USERS"]->OPENEMM_INSTALLED){
		$OpenEMMEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("OpenEMMEnable");
		if(!is_numeric($OpenEMMEnable)){$OpenEMMEnable=1;}
		if($OpenEMMEnable==1){$TomcatEnable=0;}
	}
	
	$pid_path="/opt/openemm/tomcat/temp/tomcat.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[APP_TOMCAT]";
		$l[]="service_name=APP_TOMCAT";
	 	$l[]="master_version=".$GLOBALS["CLASS_USERS"]->TOMCAT_VERSION;
	 	$l[]="service_cmd=spamd";	
	 	$l[]="service_disabled=$TomcatEnable";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=web";
	 	
	 	if($TomcatEnable==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_TOMCAT","tomcat");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
function openemm(){
	if(!$GLOBALS["CLASS_USERS"]->OPENEMM_INSTALLED){return;}
	$OpenEMMEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("OpenEMMEnable");
	if(!is_numeric($OpenEMMEnable)){$OpenEMMEnable=1;}
	$grep=$GLOBALS["CLASS_UNIX"]->find_program("grep");
	$ps=$GLOBALS["CLASS_UNIX"]->find_program("ps");
	$awk=$GLOBALS["CLASS_UNIX"]->find_program("awk");
	
	$cmd="$ps -eo pid,command|$grep -E \"\/home\/openemm.*?org\.apache\.catalina\"|$grep -v grep|$awk '{print $1}' 2>&1";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	exec($cmd,$results);
	$master_pid=trim(@implode("", $results));
	
	
	
	
	
		$l[]="[APP_OPENEMM]";
		$l[]="service_name=APP_OPENEMM";
	 	$l[]="master_version=".$GLOBALS["CLASS_USERS"]->OPENEMM_VERSION;
	 	$l[]="service_cmd=spamd";	
	 	$l[]="service_disabled=$OpenEMMEnable";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=smtp";
	 	
	 	if($OpenEMMEnable==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_OPENEMM","openemm");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
function openemm_sendmail(){
	if(!$GLOBALS["CLASS_USERS"]->OPENEMM_INSTALLED){return;}
	$OpenEMMEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("OpenEMMEnable");
	if(!is_numeric($OpenEMMEnable)){$OpenEMMEnable=1;}
	if(!is_file("/home/openemm/sendmail/sbin/sendmail")){return;}
	
		
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file("/home/openemm/sendmail/run/sendmail.pid");
		
		$l[]="[APP_OPENEMM_SENDMAIL]";
		$l[]="service_name=APP_OPENEMM_SENDMAIL";
	 	$l[]="master_version=".$GLOBALS["CLASS_USERS"]->OPENEMM_SENDMAIL_VERSION;
	 	$l[]="service_cmd=smtp";	
	 	$l[]="service_disabled=$OpenEMMEnable";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=smtp";
	 	
	 	if($OpenEMMEnable==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_OPENEMM_SENDMAIL","openemm-sendmail");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}



function iscsi_pid_path(){
	if(is_file("/var/run/ietd.pid")){return "/var/run/ietd.pid";}
	if(is_file("/var/run/iscsi_trgt.pid")){return "/var/run/iscsi_trgt.pid";}
	
}     
     
function iscsi(){
	if(!$GLOBALS["CLASS_USERS"]->ISCSI_INSTALLED){return;}
	$EnableISCSI=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableISCSI");
	if($EnableISCSI==null){$EnableISCSI=0;}	
	$pid_path=iscsi_pid_path();
	$master_pid=trim(@file_get_contents($pid_path));
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("ietd");
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	
		$l[]="[APP_IETD]";
		$l[]="service_name=APP_IETD";
	 	$l[]="master_version=".GetVersionOf("ietd");
	 	$l[]="service_cmd=iscsi";	
	 	$l[]="service_disabled=$EnableISCSI";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";	
		if($EnableISCSI==0){$l[]="";return implode("\n",$l);return;}
		

		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_IETD","iscsi");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;			
	
	
}
function smartd_version(){
	if(isset($GLOBALS["smartd_version"])){return $GLOBALS["smartd_version"];}
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("smartd");
	exec("$bin_path -V 2>&1",$results);
	if(preg_match("#release\s+([0-9\.]+)#",@implode("",$results),$re)){$GLOBALS["smartd_version"]=$re[1];return $re[1];}
}

function smartd(){
	if(!$GLOBALS["CLASS_USERS"]->SMARTMONTOOLS_INSTALLED){return;}
	$EnableSMARTDisk=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSMARTDisk");
	if(!is_numeric($EnableSMARTDisk)){$EnableSMARTDisk=1;}	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("smartd");
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	
	
	
	
		$l[]="[SMARTD]";
		$l[]="service_name=APP_SMARTMONTOOLS";
	 	$l[]="master_version=".smartd_version();
	 	$l[]="service_cmd=iscsi";	
	 	$l[]="service_disabled=$EnableSMARTDisk";
	 	$l[]="pid_path=none";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";	
		if($EnableSMARTDisk==0){$l[]="";return implode("\n",$l);return;}
		

		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_IETD","iscsi");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;			
		
}

function postfwd2(){
	exec($GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.postfwd2.php --all-status",$results);
	return @implode("\n",$results);
}

function opendkim(){
	if(!$GLOBALS["CLASS_USERS"]->OPENDKIM_INSTALLED){return;}
	$EnableDKFilter=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableDKFilter");
	if($EnableDKFilter==null){$EnableDKFilter=0;}
	
	
	$pid_path="/var/run/opendkim/opendkim.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[APP_OPENDKIM]";
		$l[]="service_name=APP_OPENDKIM";
	 	$l[]="master_version=".GetVersionOf("opendkim");
	 	$l[]="service_cmd=dkfilter";	
	 	$l[]="service_disabled=$EnableDKFilter";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	
	 	if($EnableDKFilter==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_OPENDKIM","dkfilter");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================

function watchdog_yorel(){
	$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
	$kill=$GLOBALS["CLASS_UNIX"]->find_program("kill");
	if(!is_file($pgrep)){
		if($GLOBALS["VERBOSE"]){echo "pgrep, no such file\n";}
		return;
	}
	$cmd="$pgrep -l -f rrd/yorel-upd 2>&1";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	$max=0;
	exec("$cmd",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+#",$ligne,$re)){
			if($GLOBALS["VERBOSE"]){echo "PID:{$re[1]}\n";}
			if($GLOBALS["CLASS_UNIX"]->process_exists($re[1])){
				$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($re[1]);
				if($GLOBALS["VERBOSE"]){echo "PID:{$re[1]} -> $time minutes TTL\n";}
				
				if($time>3){
					events("Killing process {$re[1]}: $time minutes TTL",__FUNCTION__,__FILE__);
					shell_exec("/bin/rm -rf /opt/artica/var/rrd/yorel/*");
					shell_exec("/bin/rm -rf /opt/artica/share/www/system/rrd/*");
					shell_exec("$kill -9 $re[1]} >/dev/null 2>&1");
					continue;
				}else{
					if($GLOBALS["VERBOSE"]){echo "PID:{$re[1]} -> $time minutes TTL -> results=keep\n";}
				}
				
				
				if($max>1){
					events("No more than one process allowed",__FUNCTION__,__FILE__);
					shell_exec("$kill -9 $re[1]} >/dev/null 2>&1");
					shell_exec("/bin/rm -rf /opt/artica/var/rrd/yorel/*");
					shell_exec("/bin/rm -rf /opt/artica/share/www/system/rrd/*");					
					continue;
				}
				$max++;
				events("Found process {$re[1]}: $time minutes TTL Process number $max",__FUNCTION__,__FILE__);
				
			}
		}else{
			if($GLOBALS["VERBOSE"]){echo "$ligne no match\n";}
		}
	}
}
//========================================================================================================================================================

function milter_dkim(){
	
	
	

	if(!$GLOBALS["CLASS_USERS"]->MILTER_DKIM_INSTALLED){return;}
	$EnableDKFilter=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableDkimMilter");
	if($EnableDKFilter==null){$EnableDKFilter=0;}
	
	
	$pid_path="/var/run/dkim-milter/dkim-milter.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[APP_MILTER_DKIM]";
		$l[]="service_name=APP_MILTER_DKIM";
	 	$l[]="master_version=".GetVersionOf("milterdkim");
	 	$l[]="service_cmd=dkim-milter";	
	 	$l[]="service_disabled=$EnableDKFilter";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	
	 	if($EnableDKFilter==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_MILTER_DKIM","dkim-milter");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================

function dropbox(){
	
	
	

	if(!$GLOBALS["CLASS_USERS"]->DROPBOX_INSTALLED){return;}
	$EnableDropBox=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableDropBox");
	if($EnableDropBox==null){$EnableDropBox=0;}
	
	
	$pid_path="/root/.dropbox/dropbox.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[APP_DROPBOX]";
		$l[]="service_name=APP_DROPBOX";
	 	$l[]="master_version=".GetVersionOf("dropbox");
	 	$l[]="service_cmd=dropbox";	
	 	$l[]="service_disabled=$EnableDropBox";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=storage";
	 	
	 	if($EnableDropBox==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_DROPBOX","dropbox");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function virtualbox_webserv(){
	
	
	

	if(!$GLOBALS["CLASS_USERS"]->VIRTUALBOX_INSTALLED){return;}
	$EnableVirtualBox=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableVirtualBox");
	if($EnableVirtualBox==null){$EnableVirtualBox=1;}
	
	
	$pid_path="/var/run/virtualbox/vboxwebsrv.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[APP_VIRTUALBOX_WEBSERVICE]";
		$l[]="service_name=APP_VIRTUALBOX_WEBSERVICE";
	 	$l[]="master_version=".GetVersionOf("virtualbox");
	 	$l[]="service_cmd=virtualbox-web";	
	 	$l[]="service_disabled=$EnableVirtualBox";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=virtual";
	 	
	 	if($EnableVirtualBox==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_VIRTUALBOX_WEBSERVICE","virtualbox-web");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function crossroads(){
	
	
	

	if(!$GLOBALS["CLASS_USERS"]->crossroads_installed){return;}
	$EnableCrossRoads=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableCrossRoads");
	if($EnableCrossRoads==null){$EnableCrossRoads=0;}
	
	

		$master_pid=trim($GLOBALS["CLASS_UNIX"]->PIDOF($GLOBALS["CLASS_UNIX"]->find_program("xr")));
	
	
		$l[]="[APP_CROSSROADS]";
		$l[]="service_name=APP_CROSSROADS";
	 	$l[]="master_version=".GetVersionOf("crossroads");
	 	$l[]="service_cmd=crossroads";	
	 	$l[]="service_disabled=$EnableCrossRoads";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=network";
	 	
	 	if($EnableCrossRoads==0){$l[]="";return implode("\n",$l);return;}
	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_CROSSROADS","crossroads");
			$l[]="";
			return implode("\n",$l);
			return;
		}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function pptpd(){
	if(!$GLOBALS["CLASS_USERS"]->PPTPD_INSTALLED){return;}
	$EnablePPTPDVPN=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePPTPDVPN");
	if($EnablePPTPDVPN==null){$EnablePPTPDVPN=0;}
	$pid_path="/var/run/pptpd.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	$l[]="[APP_PPTPD]";
	$l[]="service_name=APP_PPTPD";
	$l[]="master_version=".GetVersionOf("pptpd");
	$l[]="service_cmd=pptpd";	
	$l[]="service_disabled=$EnablePPTPDVPN";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=1";
	$l[]="family=network";
	if($EnablePPTPDVPN==0){$l[]="";return implode("\n",$l);return;}
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_PPTPD","pptpd");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function apt_mirror(){
	if(!$GLOBALS["CLASS_USERS"]->APT_MIRROR_INSTALLED){return;}
	$EnableAptMirror=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAptMirror");
	if($EnableAptMirror==null){$EnableAptMirror=0;}
	$master_pid=trim($GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN($GLOBALS["CLASS_UNIX"]->find_program("apt-mirror")));

	$l[]="[APP_APT_MIRROR]";
	$l[]="service_name=APP_APT_MIRROR";
	$l[]="master_version=".GetVersionOf("apt-mirror");
	$l[]="service_cmd=apt-mirror";	
	$l[]="service_disabled=$EnableAptMirror";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=0";
	$l[]="family=network";
	if($EnableAptMirror==0){$l[]="";return implode("\n",$l);return;}
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================

function ddclient(){
	
	$EnableDDClient=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableDDClient");
	if($EnableDDClient==null){$EnableDDClient=0;}
	$pid_path="/var/run/ddclient.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_DDCLIENT]";
	$l[]="service_name=APP_DDCLIENT";
	$l[]="master_version=".GetVersionOf("ddclient");
	$l[]="service_cmd=apt-mirror";	
	$l[]="service_disabled=$EnableDDClient";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=1";
	$l[]="family=network";
	if($EnableDDClient==0){$l[]="";return implode("\n",$l);return;}
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_DDCLIENT","ddclient");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function apachesrc(){
	$EnableFreeWeb=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableFreeWeb");
	if($EnableFreeWeb==null){$EnableFreeWeb=0;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_APACHE_PID_PATH();
	if(!is_file($pid_path)){$pid_path="/var/run/httpd/httpd.pid";}
	$master_pid=trim(@file_get_contents($pid_path));
	$binpath=$GLOBALS["CLASS_UNIX"]->LOCATE_APACHE_BIN_PATH();
	

	
	$l[]="[APP_APACHE_SRC]";
	$l[]="service_name=APP_APACHE_SRC";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->LOCATE_APACHE_VERSION();
	$l[]="service_cmd=apachesrc";	
	$l[]="service_disabled=$EnableFreeWeb";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=1";
	$l[]="family=www";
	if($EnableFreeWeb==0){$l[]="";return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);
	}	
	
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_APACHE_SRC","apachesrc");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================



function cluebringer(){
	if(!$GLOBALS["CLASS_USERS"]->CLUEBRINGER_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." CLUEBRINGER_INSTALLED = FALSE\n";}
		return;
	}
	$EnableCluebringer=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableCluebringer");
	if($EnableCluebringer==null){$EnableCluebringer=0;}
	$pid_path="/var/run/cbpolicyd.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_CLUEBRINGER]";
	$l[]="service_name=APP_CLUEBRINGER";
	$l[]="master_version=".GetVersionOf("cluebringer");
	$l[]="service_cmd=cluebringer";	
	$l[]="service_disabled=$EnableCluebringer";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=1";
	$l[]="family=postfix";
	if($EnableCluebringer==0){$l[]="";return implode("\n",$l);return;}
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_CLUEBRINGER","cluebringer");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function sabnzbdplus(){
	
	if(!$GLOBALS["CLASS_USERS"]->APP_SABNZBDPLUS_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." APP_SABNZBDPLUS_INSTALLED = FALSE\n";}
		return;
	}	
	$EnableSabnZbdPlus=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSabnZbdPlus");
	if($EnableSabnZbdPlus==null){$EnableSabnZbdPlus=0;}
	
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." EnableSabnZbdPlus = $EnableSabnZbdPlus\n";}
	if(is_file("/usr/share/sabnzbdplus/SABnzbd.py")){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN("SABnzbd.py");
		$binary="SABnzbd.py";
	}else{
		$binary=$GLOBALS["CLASS_UNIX"]->find_program("sabnzbdplus");
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN($binary);
	}
	
	$l[]="[APP_SABNZBDPLUS]";
	$l[]="service_name=APP_SABNZBDPLUS";
	$l[]="master_version=".GetVersionOf("sabnzbdplus");
	$l[]="service_cmd=sabnzbdplus";	
	$l[]="service_disabled=$EnableSabnZbdPlus";
	$l[]="pid_path=pidof $binary";
	$l[]="watchdog_features=1";
	$l[]="family=samba";
	if($EnableSabnZbdPlus==0){$l[]="";return implode("\n",$l);return;}
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_SABNZBDPLUS","sabnzbdplus");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
}
//========================================================================================================================================================
function greensql_version(){
	if(isset($GLOBALS["greensql_version"])){return $GLOBALS["greensql_version"];}
	$f=explode("\n", @file_get_contents("/usr/share/greensql-console/config.php"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#version.+?([0-9\.]+)#", $ligne,$re)){
			$GLOBALS["greensql_version"]=$re[1];
			return $GLOBALS["greensql_version"];
		}else{
			if($GLOBALS["VERBOSE"]){echo "\"$ligne\" ->NO MATCH\n";}
		}
	}
	
}

function nscd(){
	if(!$GLOBALS["CLASS_USERS"]->NSCD_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." NSCD_INSTALLED = FALSE\n";}
		return;
	}
	$bin=$GLOBALS["CLASS_UNIX"]->find_program("nscd");
	$EnableNSCD=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableNSCD");
	if(!is_numeric($EnableNSCD)){$EnableNSCD=1;}
	$pid_path="/var/run/nscd/nscd.pid";
	$master_pid=trim(@file_get_contents($pid_path));	
	$version=nscd_version($bin);
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin);}
	if($EnableNSCD==0){
		if($GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){shell_exec("/etc/init.d/nscd stop");}
	}
	
	
	$l[]="[APP_NSCD]";
	$l[]="service_name=APP_NSCD";
	$l[]="master_version=$version";

	$l[]="service_disabled=$EnableNSCD";
	$l[]="watchdog_features=1";
	$l[]="family=system";
	if($EnableNSCD==0){$l[]="";return implode("\n",$l);return;}
	
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		shell_exec("/etc/init.d/nscd start");
		$l[]="";
		return implode("\n",$l);
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
	
}

function nscd_version($bin){
	if(isset($GLOBALS[__FUNCTION__])){return $GLOBALS[__FUNCTION__];}
	exec("$bin -V 2>&1",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#nscd.+?([0-9\.]+)#", $line)){$GLOBALS[__FUNCTION__]=$re[1];return $re[1];}
	}
	
}

function greensql(){
	
	if(!$GLOBALS["CLASS_USERS"]->APP_GREENSQL_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." APP_GREENSQL_INSTALLED = FALSE\n";}
		return;
	}	
	$EnableGreenSQL=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableGreenSQL");
	if(!is_numeric($EnableGreenSQL)){$EnableGreenSQL=1;}
	$bin=$GLOBALS["CLASS_UNIX"]->find_program("greensql-fw");
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." EnableGreenSQL = $EnableGreenSQL\n";}
	
	
	$l[]="[APP_GREENSQL]";
	$l[]="service_name=APP_GREENSQL";
	$l[]="master_version=".greensql_version();
	$l[]="service_cmd=greensql";	
	$l[]="service_disabled=$EnableGreenSQL";
	$l[]="watchdog_features=1";
	$l[]="family=samba";
	if($EnableGreenSQL==0){$l[]="";return implode("\n",$l);return;}
	
	$master_pid=@file_get_contents("/var/run/greensql-fw.pid");
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." /var/run/greensql-fw.pid = $master_pid\n";}
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin,true);
	}
	
	
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_GREENSQL","greensql");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
}
//========================================================================================================================================================

function stunnel(){
	
	if(!$GLOBALS["CLASS_USERS"]->stunnel4_installed){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." stunnel4_installed = FALSE\n";}
		return;
	}	
	$sTunnel4enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("sTunnel4enabled");
	if($sTunnel4enabled==null){$sTunnel4enabled=0;}
	$binary=$GLOBALS["CLASS_UNIX"]->LOCATE_STUNNEL_BIN();
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binary);
	
	if($GLOBALS["VERBOSE"]){echo "binary............: $binary\n";}
	if($GLOBALS["VERBOSE"]){echo "PID...............: $master_pid\n";}
	
	$l[]="[STUNNEL]";
	$l[]="service_name=APP_STUNNEL";
	$l[]="master_version=".GetVersionOf("stunnel");
	$l[]="service_cmd=stunnel";	
	$l[]="service_disabled=$sTunnel4enabled";
	$l[]="pid_path=pidof $binary";
	$l[]="watchdog_features=1";
	$l[]="family=system";
	if($sTunnel4enabled==0){$l[]="";return implode("\n",$l);return;}
	 if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_STUNNEL","stunnel");
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
}

//========================================================================================================================================================


function pptp_clients(){
	if(!$GLOBALS["CLASS_USERS"]->PPTP_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." PPTP_INSTALLED = FALSE\n";}
		return;
	}
	$version=GetVersionOf("pptpd");
	$array=unserialize(base64_decode($GLOBALS["CLASS_SOCKETS"]->GET_INFO("PPTPVpnClients")));	
	if(!is_array($array)){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not an array PPTPVpnClients\n";}
		return;
	}
	if(count($array)==0){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." PPTPVpnClients\n";}
		return;
	}
	$reload=false;
	while (list ($connexionname, $PPTPDConfig) = each ($array) ){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." $connexionname...:{$PPTPDConfig["ENABLED"]}\n";}
		if($PPTPDConfig["ENABLED"]<>1){continue;}	
		$arrayPIDS=pptp_client_is_active($connexionname);
		$l[]="[PPTPDCLIENT_$connexionname]";
		$l[]="service_name=$connexionname";
		$l[]="master_version=$version";
		$l[]="service_cmd=pptpd-clients";	
		$l[]="service_disabled=1";
		$l[]="pid_path=";
		$l[]="watchdog_features=1";
		$l[]="family=network";
		
		if(!is_array($arrayPIDS)){$reload=true;}else{
			$l[]=GetMemoriesOf($arrayPIDS[0]);
			$l[]="";	
		}
	}
	
	$l[]="";
	if(!$GLOBALS["DISABLE_WATCHDOG"]){
	if($reload){
		$cmd="{$GLOBALS["PHP5"]} ". dirname(__FILE__)."/exec.pptpd.php --clients-start &";
		events("START PPTP Clients -> $cmd",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
	}}
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function pptp_client_is_active($connexionname){
	if($GLOBALS["PGREP"]==null){
		$unix=new unix();
		$GLOBALS["PGREP"]=$unix->find_program("pgrep");
	}
	
	$cmd="{$GLOBALS["PGREP"]} -l -f \"pptp.+?call $connexionname\" 2>&1";
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->$cmd\n";}
	exec($cmd,$results);
	
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#^([0-9]+).+?pptp#",$line,$re)){
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->PID: {$re[1]}\n";}
			if($unix->PID_IS_CHROOTED($re[1])){continue;}
			$arr[]=$re[1];
		}else{
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." NO MATCH \"$line\"\n";}	
		}
		
	}
	
	return $arr;	
	
	
}



function tftpd(){
	if(!$GLOBALS["CLASS_USERS"]->TFTPD_INSTALLED){return;}
	$EnableTFTPD=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableTFTPD");
	if($EnableTFTPD==null){$EnableTFTPD=1;}
		$bin=$GLOBALS["CLASS_UNIX"]->find_program("inetd");
		if(!is_file($bin)){
			$bin=$GLOBALS["CLASS_UNIX"]->find_program("xinetd");
			if(is_file("/var/run/xinetd.pid")){
				$master_pid=trim(@file_get_contents("/var/run/xinetd.pid"));
			}
		}
		if(!is_numeric($master_pid)){$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin);}
	
	
		$l[]="[APP_TFTPD]";
		$l[]="service_name=APP_TFTPD";
	 	$l[]="master_version=".GetVersionOf("tftpd");
	 	$l[]="service_cmd=tftpd";	
	 	$l[]="service_disabled=$EnableTFTPD";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=0";
	 	$l[]="family=storage";
	 	if($EnableTFTPD==0){$l[]="";return implode("\n",$l);return;}
	 	$l[]=GetMemoriesOf($master_pid);
		$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function postfix_multi_status(){
	if(!is_array($GLOBALS["MULTI-INSTANCES-LIST"])){$calc=true;}
	if($GLOBALS["MULTI-INSTANCES-TIME"]==null){$cacl=true;}
	if(calc_time_min($GLOBALS["MULTI-INSTANCES-TIME"])>5){$cacl=true;}
	$version=GetVersionOf("postfix");
	
	if($GLOBALS["VERBOSE"]){echo "calc=\"$cacl\" postfix v$version\n";}
	
	if($calc){
	 	$GLOBALS["MULTI-INSTANCES-LIST"]=$GLOBALS["CLASS_UNIX"]->POSTFIX_MULTI_INSTANCES_LIST();
      	$GLOBALS["MULTI-INSTANCES-TIME"]=time();
	}
	if(is_array($GLOBALS["MULTI-INSTANCES-LIST"])){
		while (list ($num, $instance) = each ($GLOBALS["MULTI-INSTANCES-LIST"]) ){
			if($instance==null){continue;}
			$l[]="[POSTFIX-MULTI-$instance]";
			$l[]="service_name=$instance";
		 	$l[]="master_version=".GetVersionOf("postfix");
		 	$l[]="service_cmd=postfix-multi";	
		 	$l[]="service_disabled=1";
		 	$l[]="remove_cmd=--postfix-remove";
		 	$l[]="family=postfix";
		 	$master_pid=$GLOBALS["CLASS_UNIX"]->POSTFIX_MULTI_PID($instance);
		 	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		 		$l[]="";return implode("\n",$l);return;
		 	}
		 	
			$l[]=GetMemoriesOf($master_pid);
			$l[]="";	 	
		}
	}
	if(is_array($l)){return implode("\n",$l);}		
	
	
	
}




function postfix(){
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("postconf");
	if($bin_path==null){return null;}
	$EnablePostfixMultiInstance=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePostfixMultiInstance");
	if($GLOBALS["VERBOSE"]){echo "EnablePostfixMultiInstance=\"$EnablePostfixMultiInstance\"\n";}
	if($EnablePostfixMultiInstance==1){
		$l[]=postfix_multi_status();
	}
	$sendmail_pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_SENDMAIL_PID_PATH();
	if(strlen($sendmail_pid_path)>3){
		$sendmail_pid=file_get_contents($sendmail_pid_path);
		if(is_numeric($sendmail_pid)){
			events("Sendmail pid detected $sendmail_pid_path ($sendmail_pid)",__FUNCTION__,__LINE__);
			if($GLOBALS["CLASS_UNIX"]->process_exists($sendmail_pid)){
				$kill=$GLOBALS["CLASS_UNIX"]->find_program("kill");
				$postfix=$GLOBALS["CLASS_UNIX"]->find_program("postfix");
				shell_exec("$kill -9 $sendmail_pid  >/dev/null 2>&1");
				@unlink($sendmail_pid);
				$GLOBALS["CLASS_UNIX"]->send_email_events("SendMail (pid $sendmail_pid) is running, kill it !!","This action has been performed to avoid ports conflicts","smtp");
				shell_exec("$postfix start  >/dev/null 2>&1 &");
			}
		}
	}
	
		$postfix_path=$GLOBALS["CLASS_UNIX"]->find_program("postfix");
		$master_pid=$GLOBALS["CLASS_UNIX"]->POSTFIX_PID();
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			exec("$postfix_path status 2>&1",$status_results);
			while (list ($num, $line) = each ($status_results) ){
				if(preg_match("#PID:.+?([0-9]+)#", $line)){
					$GLOBALS["DEBUG_LOGS"][]="postfix status: $line";
					$master_pid=$re[1];
				}
			}	
			
		}
			
		
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			$master_bin_path=$GLOBALS["CLASS_UNIX"]->POSTFIX_MASTER_BIN_PATH();
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($master_bin_path,true);
			
		}
		
		
	
	
		$l[]="[POSTFIX]";
		$l[]="service_name=APP_POSTFIX";
	 	$l[]="master_version=".GetVersionOf("postfix");
	 	$l[]="service_cmd=postfix-single";	
	 	$l[]="service_disabled=1";
	 	$l[]="remove_cmd=--postfix-remove";
	 	$l[]="family=postfix";
	 	
		$l[]="watchdog_features=1";
		if($GLOBALS["ArticaWatchDogList"]["APP_POSTFIX"]==null){$GLOBALS["ArticaWatchDogList"]["APP_POSTFIX"]=1;}	 	
	 	
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			$GLOBALS["DEBUG_LOGS"][]="$master_pid does not exists";
			if($GLOBALS["ArticaWatchDogList"]["APP_POSTFIX"]==1){
				$postfix_path=$GLOBALS["CLASS_UNIX"]->find_program("postfix");
				$GLOBALS["DEBUG_LOGS"][]="Postfix bin = $postfix_path";
				exec("$postfix_path start -v 2>&1",$pstfix_start);				
				$GLOBALS["CLASS_UNIX"]->send_email_events("APP_POSTFIX stopped (watchdog)",
				"Artica will try to start it\n".@implode("\n",$pstfix_start)."\n".@implode("\n", $GLOBALS["DEBUG_LOGS"]),"postfix");
				unset($GLOBALS["DEBUG_LOGS"]);

			}
			$l[]="";return implode("\n",$l);return; 
		
			}	
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
		unset($GLOBALS["DEBUG_LOGS"]);
		
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function postfix_logger(){
	
	
	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("postconf");
	if($bin_path==null){return null;}
	$pid_path="/etc/artica-postfix/exec.maillog.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[ARTICA_MYSQMAIL]";
		$l[]="service_name=APP_ARTICA_MYSQMAIL";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=postfix-logger";	
	 	$l[]="service_disabled=1";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	$l[]="installed=1";
	
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ARTICA_MYSQMAIL","postfix-logger");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	 	

	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	
	if(!$GLOBALS["DISABLE_WATCHDOG"]){
		$time=file_time_min("/var/log/artica-postfix/postfix-logger.debug");
		
		if($time>5){
			writelogs("LOG TIME: $time -> restart postfix-logger",__FUNCTION__,__FILE__,__LINE__);
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-logger");
		}
	}
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function squidguard_logger(){
	
	
	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("squidGuard");
	if($bin_path==null){return null;}
	$pid_path="/etc/artica-postfix/exec.squidguard-tail.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
	
		$l[]="[APP_ARTICA_SQUIDGUARDTAIL]";
		$l[]="service_name=APP_ARTICA_SQUIDGUARDTAIL";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=squiguard-tail";	
	 	$l[]="service_disabled=1";
	 	$l[]="family=squid";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="installed=1";
	
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ARTICA_SQUIDGUARDTAIL","squiguard-tail");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	 	

	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function artica_policy(){
	$pid_path="/etc/artica-postfix/exec.artica-filter-daemon.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	$EnableArticaPolicyFilter=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArticaPolicyFilter");
	if($EnableArticaPolicyFilter==null){$EnableArticaPolicyFilter=0;}
		$l[]="[APP_ARTICA_POLICY]";
		$l[]="service_name=APP_ARTICA_POLICY";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=artica-policy";	
	 	$l[]="service_disabled=$EnableArticaPolicyFilter";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=postfix";
	 	$l[]="installed=1";
	 	if($EnableArticaPolicyFilter<>1){
	 		$l[]="";$l[]="";
			return implode("\n",$l);
			return;
	 	}
	
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ARTICA_POLICY","artica-policy");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	 	

	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
function artica_status(){
	if($GLOBALS["TOTAL_MEMORY_MB"]<400){return;}
	$pid_path="/etc/artica-postfix/exec.status.php.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	$EnableArticaStatus=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArticaStatus");
	if($EnableArticaStatus==null){$EnableArticaStatus=1;}
		$l[]="[APP_ARTICA_STATUS]";
		$l[]="service_name=APP_ARTICA_STATUS";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=artica-status";	
	 	$l[]="service_disabled=$EnableArticaStatus";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
	 	$l[]="installed=1";
	 	if($EnableArticaStatus<>1){
	 		$l[]="";$l[]="";
			return implode("\n",$l);
			return;
	 	}
	
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ARTICA_STATUS","artica-status");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	 	

	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;		
	
}


function artica_executor(){
	if($GLOBALS["TOTAL_MEMORY_MB"]<400){return;}
	$pid_path="/etc/artica-postfix/exec.executor.php.daemon.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	$EnableArticaExecutor=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArticaExecutor");
	if(!is_numeric($EnableArticaExecutor)){$EnableArticaExecutor=1;}
		$l[]="[APP_ARTICA_EXECUTOR]";
		$l[]="service_name=APP_ARTICA_EXECUTOR";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=artica-exec";	
	 	$l[]="service_disabled=$EnableArticaExecutor";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
	 	$l[]="installed=1";
	 	if($EnableArticaExecutor<>1){
	 		$l[]="";$l[]="";
			return implode("\n",$l);
			return;
	 	}
	
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		//$bin=$GLOBALS["CLASS_UNIX"]->find_program("inetd");
		exec($GLOBALS["PHP5"]." ".dirname(__FILE__)."/exec.executor.php --all --verbose 2>&1",$results);
		$GLOBALS["CLASS_UNIX"]->send_email_events("Artica Executor report",
		"This is the debug report when executing artica-executor
		Is disabled ?:$EnableArticaExecutor
		pid:$pid_path
		Pid found:$master_pid
		------------------------------------
		".@implode("\n", $results),"system");
		
		WATCHDOG("APP_ARTICA_EXECUTOR","artica-exec");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	 	

	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;	
	
}


function artica_background(){
	if($GLOBALS["TOTAL_MEMORY_MB"]<400){return;}
	$pid_path="/etc/artica-postfix/exec.parse-orders.php.damon.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	$EnableArticaBackground=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArticaBackground");
	if($EnableArticaBackground==null){$EnableArticaBackground=1;}
		$l[]="[APP_ARTICA_BACKGROUND]";
		$l[]="service_name=APP_ARTICA_BACKGROUND";
	 	$l[]="master_version=".GetVersionOf("artica");
	 	$l[]="service_cmd=artica-back";	
	 	$l[]="service_disabled=$EnableArticaBackground";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";
	 	$l[]="installed=1";
	 	if($EnableArticaBackground<>1){$l[]="";$l[]="";return implode("\n",$l);}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	events("artica-background: $master_pid PID",__FUNCTION__,__LINE__);
		
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		events("artica-background: Not running",__FUNCTION__,__LINE__);
		WATCHDOG("APP_ARTICA_BACKGROUND","artica-back");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$time=file_time_min("/var/log/artica-postfix/parse.orders.log");
	events("artica-background: /var/log/artica-postfix/parse.orders.log -> {$time}Mn ",__FUNCTION__,__LINE__);
	if($time>5){
		$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
		$cmd=trim("$nohup /etc/init.d/artica-postfix restart artica-back >/dev/null 2>&1");
		events("Restart artica-background (exec.parse.orders.php)");
		shell_exec($cmd);
	}
	
	

	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;	
	
	
}


function mailman(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->MAILMAN_INSTALLED){return null;}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("MailManEnabled");
	if($enabled==null){$enabled=0;}
	$pid_path=trim(GetVersionOf("mailman-pid"));
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[MAILMAN]";
	$l[]="service_name=APP_MAILMAN";
	$l[]="master_version=".GetVersionOf("mailman");
	$l[]="service_cmd=mailman";	
	$l[]="service_disabled=$enabled";
	$l[]="family=postfix";
	$l[]="pid_path=$pid_path";
	//$l[]="remove_cmd=--milter-grelist-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
	
function kas3_milter(){
	
	
	
	if(!is_file("/usr/local/ap-mailfilter3/bin/kas-milter")){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("KasxFilterEnabled");
	if($enabled==null){$enabled=0;}
	$pid_path="/usr/local/ap-mailfilter3/run/kas-milter.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[KAS_MILTER]";
	$l[]="service_name=APP_KAS3_MILTER";
	$l[]="master_version=".GetVersionOf("kas3");
	$l[]="service_cmd=kas3";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--kas3-remove";
	$l[]="family=postfix";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}
//========================================================================================================================================================
	
function kas3_ap(){
	
	
	
	if(!is_file("/usr/local/ap-mailfilter3/bin/kas-milter")){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("KasxFilterEnabled");
	if($enabled==null){$enabled=0;}
	$pid_path="/usr/local/ap-mailfilter3/run/ap-process-server.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[KAS3]";
	$l[]="service_name=APP_KAS3";
	$l[]="master_version=".GetVersionOf("kas3");
	$l[]="service_cmd=kas3";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--kas3-remove";
	$l[]="family=postfix";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function smbd(){
	$smbd_bin=$GLOBALS["CLASS_UNIX"]->find_program("smbd");
	if($smbd_bin==null){return;}

	if(!$GLOBALS["CLASS_USERS"]->SAMBA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SambaEnabled");
	if($enabled==null){$enabled=1;}
	
	if($enabled==1){
		$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_SMBD_PID();
		if($pid_path==null){$pid_path="/var/run/samba/smbd.pid";}
		$master_pid=trim(@file_get_contents($pid_path));
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			if($GLOBALS["VERBOSE"]){echo "pid path \"$pid_path\" no pid found\n";}
			$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($smbd_bin);
			if($GLOBALS["VERBOSE"]){echo "pid:$master_pid after pidof\n";}
			if(is_file($pid_path)){
				if($GLOBALS["VERBOSE"]){echo "write $master_pid in \"$pid_path\"\n";}
				if($master_pid>1){@file_put_contents($pid_path,$master_pid);}
			}
		}	}

	$l[]="[SAMBA_SMBD]";
	$l[]="service_name=APP_SAMBA_SMBD";
	$l[]="master_version=".GetVersionOf("samba");
	$l[]="service_cmd=samba";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--samba-remove";
	$l[]="family=storage";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function nmbd(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->SAMBA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SambaEnabled");
	if($enabled==null){$enabled=1;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_NMBD_PID();
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[SAMBA_NMBD]";
	$l[]="service_name=APP_SAMBA_NMBD";
	$l[]="master_version=".GetVersionOf("samba");
	$l[]="service_cmd=samba";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--samba-remove";
	$l[]="family=storage";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function winbindd(){
	
	if(!$GLOBALS["CLASS_USERS"]->SAMBA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}

	if(!$GLOBALS["CLASS_USERS"]->WINBINDD_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;	
	}
	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("SambaEnabled");
	$DisableWinbindd=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DisableWinbindd");
	$DisableSambaFileSharing=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DisableSambaFileSharing");
	$EnableSambaActiveDirectory=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	
	shell_exec("{$GLOBALS["PHP5"]} /usr/share/artica-postfix/exec.samba.php --ping-ads &");
	if($EnableSambaActiveDirectory==1){$DisableWinbindd=0;}
	
	
	if($DisableWinbindd==1){$enabled=0;}
	if($enabled==null){$enabled=1;}
	if($DisableSambaFileSharing==1){if($EnableSambaActiveDirectory==0){$enabled=0;}}

	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_WINBINDD_PID();
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[SAMBA_WINBIND]";
	$l[]="service_name=APP_SAMBA_WINBIND";
	$l[]="master_version=".GetVersionOf("samba");
	$l[]="service_cmd=samba";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--samba-remove";
	$l[]="family=storage";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function scanned_only(){

	if(!$GLOBALS["CLASS_USERS"]->SAMBA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableScannedOnly");
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/scannedonly.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[SAMBA_SCANNEDONLY]";
	$l[]="service_name=APP_SCANNED_ONLY";
	$l[]="master_version=unknown";
	$l[]="service_cmd=samba";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	//$l[]="remove_cmd=--samba-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}	
//========================================================================================================================================================
function roundcube(){
	if(!$GLOBALS["CLASS_USERS"]->roundcube_installed){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	
	$users=new settings_inc();
		if(!$GLOBALS["CLASS_USERS"]->POSTFIX_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." postfix not installed\n";}
		return null;
	}
	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("RoundCubeHTTPEngineEnabled");
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/lighttpd/lighttpd-roundcube.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[ROUNDCUBE]";
	$l[]="service_name=APP_ROUNDCUBE";
	$l[]="master_version=".GetVersionOf("roundcube");
	$l[]="service_cmd=roundcube";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="family=mailbox";
	//$l[]="remove_cmd=--samba-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}	
//========================================================================================================================================================
function cups(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->CUPS_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$enabled=1;
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/cups/cupsd.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[CUPS]";
	$l[]="service_name=APP_CUPS";
	$l[]="master_version=".GetVersionOf("cups");
	$l[]="service_cmd=cups";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="family=storage";
	//$l[]="remove_cmd=--samba-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================

function apache_groupware(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->APACHE_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$ApacheGroupware=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ApacheGroupware");
	$DisableFollowServiceHigerThan1G=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("DisableFollowServiceHigerThan1G");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	if($DisableFollowServiceHigerThan1G==null){$DisableFollowServiceHigerThan1G=0;}
	
	if($DisableFollowServiceHigerThan1G==0){
		if(is_file("/etc/artica-postfix/MEMORY_INSTALLED")){
			$MEMORY_INSTALLED=@file_get_contents("/etc/artica-postfix/MEMORY_INSTALLED");
			if($MEMORY_INSTALLED>0){if($MEMORY_INSTALLED<526300){$ApacheGroupware=0;}}
		}
	}
	
	$pid_path="/var/run/apache-groupware/httpd.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_GROUPWARE_APACHE]";
	$l[]="service_name=APP_GROUPWARE_APACHE";
	$l[]="master_version=".GetVersionOf("apache");
	$l[]="service_cmd=apache-groupware";	
	$l[]="service_disabled=$ApacheGroupware";
	$l[]="pid_path=$pid_path";
	$l[]="family=www";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--samba-remove";
	
	if($ApacheGroupware==0){return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_GROUPWARE_APACHE","apache-groupware");
		$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================
function apache_ocsweb(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->APACHE_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." apache not installed\n";}
		return null;
	}
	
	if(!is_file("/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php")){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	
	$OCSNGEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("OCSNGEnabled");
	if($OCSNGEnabled==null){$OCSNGEnabled=1;}
	$pid_path="/var/run/apache-ocs/httpd.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_OCSI]";
	$l[]="service_name=APP_OCSI";
	$l[]="master_version=".GetVersionOf("ocsi");
	$l[]="service_cmd=ocsweb";	
	$l[]="service_disabled=$OCSNGEnabled";
	$l[]="pid_path=$pid_path";
	$l[]="family=computers";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--samba-remove";
	
	if($OCSNGEnabled==0){return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_OCSI","ocsweb");
		$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================
function apache_ocsweb_download(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->APACHE_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." apache not installed\n";}
		return null;
	}
	
	if(!is_file("/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php")){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	$UseFusionInventoryAgents=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("UseFusionInventoryAgents");
	$OCSNGEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("OCSNGEnabled");
	if($OCSNGEnabled==null){$OCSNGEnabled=1;}
	if($UseFusionInventoryAgents==null){$UseFusionInventoryAgents=1;}
	$pid_path="/var/run/apache-ocs/httpd-download.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	
   $f[]='cacert.pem';
   $f[]='server.crt';
   $f[]='server.key';	
   
   while (list ($num, $file) = each ($f) ){
   		if(!is_file("/etc/ocs/cert/$file")){
   			$OCSNGEnabled=0;
   		}
   }
   if($UseFusionInventoryAgents==1){$OCSNGEnabled=0;}

	$l[]="[APP_OCSI_DOWNLOAD]";
	$l[]="service_name=APP_OCSI_DOWNLOAD";
	$l[]="master_version=".GetVersionOf("ocsi");
	$l[]="service_cmd=ocsweb";	
	$l[]="service_disabled=$OCSNGEnabled";
	$l[]="pid_path=$pid_path";
	$l[]="family=computers";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--samba-remove";
	
	if($OCSNGEnabled==0){return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_OCSI_DOWNLOAD","ocsweb");
		$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================
function ocs_agent(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->OCS_LNX_AGENT_INSTALLED){return null;}
	
		
	$OCSNGEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableOCSAgent");
	if($OCSNGEnabled==null){$OCSNGEnabled=1;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN("/usr/local/bin/ocsinventory-agent");
	
  
   

	$l[]="[APP_OCSI_LINUX_CLIENT]";
	$l[]="service_name=APP_OCSI_LINUX_CLIENT";
	$l[]="master_version=".GetVersionOf("ocsagent");
	$l[]="service_cmd=ocsagent";	
	$l[]="service_disabled=$OCSNGEnabled";
	$l[]="pid_path=$pid_path";
	$l[]="family=computers";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--samba-remove";
	
	if($OCSNGEnabled==0){return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_OCSI_LINUX_CLIENT","ocsagent");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}	

//========================================================================================================================================================	
function openssh(){
	
	
	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program('sshd');
	if($bin_path==null){return;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_SSHD_PID_PATH();
	$master_pid=trim(@file_get_contents($pid_path));
	$EnableSSHD=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSSHD");
	if($EnableSSHD==null){$EnableSSHD=1;}
	$l[]="[APP_OPENSSH]";
	$l[]="service_name=APP_OPENSSH";
	$l[]="master_version=".GetVersionOf("openssh");
	$l[]="service_cmd=openssh";	
	$l[]="service_disabled=1";
	$l[]="pid_path=$pid_path";
	$l[]="family=network";
	$l[]="watchdog_features=1";
	if($EnableSSHD==0){return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
		
		
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){	
		WATCHDOG("APP_OPENSSH","openssh");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	}
	

	
//========================================================================================================================================================	
function gdm(){
	
	
	
	$gdm_path=$GLOBALS["CLASS_UNIX"]->find_program('gdm');
	if($gdm_path==null){return;}
	$pid_path="/var/run/gdm.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[GDM]";
	$l[]="service_name=APP_GDM";
	$l[]="master_version=".GetVersionOf("gdm");
	//$l[]="service_cmd=apache-groupware";	
	$l[]="service_disabled=1";
	$l[]="pid_path=$pid_path";
	$l[]="family=system";
	//$l[]="remove_cmd=--samba-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================
function consolekit(){
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('console-kit-daemon');
	if($binpath==null){return;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);
	if($master_pid==null){return;}
	$l[]="[CONSOLEKIT]";
	$l[]="service_name=APP_CONSOLEKIT";
	$l[]="master_version=0.00";
	$l[]="binpath=$binpath";
	//$l[]="service_cmd=apache-groupware";	
	$l[]="service_disabled=1";
	$l[]="family=system";
	$l[]="pid_path=$pid_path";
	//$l[]="remove_cmd=--samba-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================
function xfce(){
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('xfdesktop');
	if($binpath==null){return;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);

	$l[]="[XFCE]";
	$l[]="service_name=APP_XFCE";
	$l[]="master_version=".GetVersionOf("xfce");
	$l[]="family=system";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$l[]="running=0";
		$l[]="installed=1";
		$l[]="service_disabled=0";
		$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]="running=1";
	$l[]="service_disabled=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}		
//========================================================================================================================================================

		

		
		
		

function _zarafa_checkExtension($name, $version=""){
		$result = true;
		$help_msg=null;
		if (extension_loaded($name)){
			if (version_compare(phpversion($name), $version) == -1){
				$GLOBALS["ZARAFA_ERROR"]=_zarafa_error_version("PHP ".$name." extension",phpversion($name), $version, $help_msg);
				$result = false;
			}
		}else{
			
			$GLOBALS["ZARAFA_ERROR"]=_zarafa_error_notfound("PHP ".$name." extension", $help_msg);
			$result = false;
		}
		return $result;
	}

	
function zarafa_mapi(){
	if(!_zarafa_checkExtension("mapi", "5.0-4688", "Mapi error, please contact Artica support team.")){
		if($GLOBALS["VERBOSE"]){echo "Warning Zarafa mapi php extension error {$GLOBALS["ZARAFA_ERROR"]}\n";}
		$GLOBALS["CLASS_UNIX"]->send_email_events("Warning Zarafa mapi php extension error",$GLOBALS["ZARAFA_ERROR"],"mailbox");
		
	}
}

function _zarafa_error_version($name, $needed, $found, $help){return sprintf("Version error: %s %s found, but %s needed.\n",$name, $needed, $found);}
function _zarafa_error_notfound($name, $help){return sprintf("Not Found: %s not found", $name);}	


	
function zarafa_web(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->APACHE_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	
	if($enabled==null){$enabled=1;}
	$pid_path="/var/run/zarafa-web/httpd.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_WEB]";
	$l[]="service_name=APP_ZARAFA_WEB";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa-web";	
	$l[]="service_disabled=$enabled";
	$l[]="family=mailbox";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";
	
	if($enabled==0){return implode("\n",$l);return;}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_WEB","zarafa-web");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}	
//========================================================================================================================================================
function zarafa_ical(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ZarafaiCalEnable");
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/zarafa-ical.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_ICAL]";
	$l[]="service_name=APP_ZARAFA_ICAL";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="family=mailbox";	
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";
	
	if($enabled==0){return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_ICAL","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}		
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function vps_servers(){
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("LXCEnabled");
	if(!is_numeric($enabled)){$enabled=0;}
	if($enabled==0){return;}
	if($GLOBALS["VERBOSE"]){$verbs=" --verbose";}
	exec("{$GLOBALS["PHP5"]} ". dirname(__FILE__)."/exec.vservers.php --status$verbs 2>&1",$results);
	return implode("\n",$results);return;	
	
	
}

function crossroads_multiple(){
	if($GLOBALS["VERBOSE"]){$verbs=" --verbose";}
	exec("{$GLOBALS["PHP5"]} ". dirname(__FILE__)."/exec.crossroads.php --multiples-status$verbs 2>&1",$results);
	return implode("\n",$results);return;		
}
	
	
function zarafa_dagent(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=1;
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/zarafa-dagent.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_DAGENT]";
	$l[]="service_name=APP_ZARAFA_DAGENT";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="family=mailbox";	
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_DAGENT","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}			
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function zarafa_monitor(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=1;
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/zarafa-monitor.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_MONITOR]";
	$l[]="service_name=APP_ZARAFA_MONITOR";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="family=mailbox";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_MONITOR","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}			
		
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function zarafa_gateway(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=1;
	$ZarafaPop3Enable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ZarafaPop3Enable");
	$ZarafaPop3sEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ZarafaPop3sEnable");
	$ZarafaIMAPEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ZarafaIMAPEnable");
	$ZarafaIMAPsEnable=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("ZarafaIMAPsEnable");
	
	if(!is_numeric($ZarafaPop3Enable)){$ZarafaPop3Enable=1;}
	if(!is_numeric($ZarafaPop3sEnable)){$ZarafaPop3sEnable=0;}
	if(!is_numeric($ZarafaIMAPEnable)){$ZarafaIMAPEnable=1;}
	if(!is_numeric($ZarafaIMAPsEnable)){$ZarafaIMAPsEnable=0;}
	$ZarafaPop3Enable=intval($ZarafaPop3Enable);
	$ZarafaPop3sEnable=intval($ZarafaPop3sEnable);
	$ZarafaIMAPEnable=intval($ZarafaIMAPEnable);
	$ZarafaIMAPsEnable=intval($ZarafaIMAPsEnable);
  
	$total=$ZarafaIMAPsEnable+$ZarafaPop3Enable+$ZarafaPop3sEnable+$ZarafaIMAPEnable;
	if($total==0){$enabled=0;}
	$pid_path="/var/run/zarafa-gateway.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_GATEWAY]";
	$l[]="service_name=APP_ZARAFA_GATEWAY";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";
	$l[]="family=mailbox";
	if($enabled==0){return implode("\n",$l);return;}

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_GATEWAY","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}			
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================
function zarafa_spooler(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=1;
	if($enabled==null){$enabled=0;}
	$pid_path="/var/run/zarafa-spooler.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_SPOOLER]";
	$l[]="service_name=APP_ZARAFA_SPOOLER";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="family=mailbox";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_SPOOLER","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}		
	
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================

function zarafa_licensed(){
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=1;
	if(!is_file("/etc/zarafa/license/base")){$enabled=0;}
	$pid_path="/var/run/zarafa-licensed.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_LICENSED]";
	$l[]="service_name=APP_ZARAFA_LICENSED";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";
	$l[]="family=mailbox";
	if($enabled==0){return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_LICENSED","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		
	}else{
		$l[]="running=1";		
	}		
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================	
function zarafa_indexer(){
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableZarafaIndexer");
	if(!is_numeric($enabled)){$enabled=0;}
	$pid_path="/var/run/zarafa-indexer.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_INDEXER]";
	$l[]="service_name=APP_ZARAFA_INDEXER";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";
	$l[]="family=mailbox";
	if($enabled==0){return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA_INDEXER","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		
	}else{
		$l[]="running=1";		
	}		
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	}
//========================================================================================================================================================	
	
	
	
function zarafa_watchdog(){
	$pid_path="/var/run/zarafa-server.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	$text[]="This is watchdog the report for Zarafa server ";
	$text[]="Pid: $master_pid";
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$text[]="Running No : -> running watchdog";
		$text[]="Process report: ".zarafa_server();
	}else{
		$text[]="Running Yes :";
	}
	
	$text[]="Mysql server status:\n---------------\n".mysql_server();
	
	$GLOBALS["CLASS_UNIX"]->send_email_events("Zarafa watchdog report",@implode("\n",$text),"mailbox");
	
}		
	
function zarafa_server(){
	
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->ZARAFA_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." not installed\n";}
		return null;
	}	
	
	$enabled=1;
	$pid_path="/var/run/zarafa-server.pid";
	$master_pid=trim(@file_get_contents($pid_path));

	$l[]="[APP_ZARAFA_SERVER]";
	$l[]="service_name=APP_ZARAFA_SERVER";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";
	$l[]="family=mailbox";
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		
	}else{
		$l[]="running=1";		
	}			
	
	

	$meme=GetMemoriesOf($master_pid);
	$l[]=$meme;
	$l[]="";
	$l[]="[APP_ZARAFA]";
	$l[]="service_name=APP_ZARAFA";
	$l[]="master_version=".$GLOBALS["CLASS_UNIX"]->ZARAFA_VERSION();
	$l[]="family=mailbox";
	$l[]="service_cmd=zarafa";	
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	$l[]="remove_cmd=--zarafa-remove";
	$l[]="watchdog_features=1";

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ZARAFA","zarafa");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}		
	
	if(!$GLOBALS["DISABLE_WATCHDOG"]){
		$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup")." ";
		if($GLOBALS["PHP5"]==null){$GLOBALS["PHP5"]=LOCATE_PHP5_BIN2();}
		$cmd=trim($nohup.$GLOBALS["PHP5"]." ".dirname(__FILE__)."/exec.zarafa.build.stores.php --export-hash >/dev/null 2>&1 &");
		events("running $cmd",__FUNCTION__,__LINE__);
		shell_exec($cmd);
	}
	zarafa_mapi();
	$l[]="running=1";
	$l[]=$meme;
	$l[]="";	
	return implode("\n",$l);return;		
	}	
//========================================================================================================================================================
function vmtools(){
	$binpath=_vmtools_bin_path();
	if($binpath==null){return null;}
	
	$enabled=1;
	$pid_path="/var/run/vmware-guestd.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	if($master_pid==null){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);
	}

	$l[]="[APP_VMTOOLS]";
	$l[]="service_name=APP_VMTOOLS";
	$l[]="master_version=".GetVersionOf("vmtools");
	$l[]="service_cmd=vmtools";	
	$l[]="service_disabled=$enabled";
	
	$l[]="family=system";
	$l[]="pid_path=$pid_path";
	$l[]="binpath=$binpath";
	//$l[]="remove_cmd=--zarafa-remove";

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_VMTOOLS","vmtools");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}		
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
}

function _vmtools_bin_path(){
	if(is_file("/usr/sbin/vmtoolsd")){return "/usr/sbin/vmtoolsd";}
	if(is_file("/usr/sbin/vmware-guestd")){return "/usr/sbin/vmware-guestd";}
	if(is_file("/usr/lib/vmware-tools/bin32/vmware-user-loader")){return "/usr/lib/vmware-tools/bin32/vmware-user-loader";}
}

//========================================================================================================================================================
function hamachi(){
	if(!is_file("/usr/bin/hamachi")){return null;}
	
		
	
	$enabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableHamachi");
	$pid_path="/var/run/vmware-guestd.pid";
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF("/usr/bin/hamachi");

	$l[]="[APP_AMACHI]";
	$l[]="service_name=APP_AMACHI";
	$l[]="master_version=".GetVersionOf("amachi");
	$l[]="service_cmd=amachi";	
	$l[]="family=network";
	$l[]="service_disabled=$enabled";
	$l[]="pid_path=$pid_path";
	//$l[]="remove_cmd=--zarafa-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";return implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;
}
//========================================================================================================================================================
function artica_notifier(){
		
	
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('emailrelay');
	if($binpath==null){return;}
	
	
	$l[]="[APP_ARTICA_NOTIFIER]";
	$l[]="service_name=APP_ARTICA_NOTIFIER";
	$l[]="service_cmd=artica-notifier";	
	$l[]="master_version=".GetVersionOf("emailrelay");
	
	if(!is_file("/etc/artica-postfix/smtpnotif.conf")){
		$l[]="service_disabled=0";
		return implode("\n",$l);
		return;
	}
	
	$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
	if($ini->_params["SMTP"]["enabled"]<>1){
		$l[]="service_disabled=0";
		return implode("\n",$l);
		return;	
	}
	
	$l[]="service_disabled=1";
	$pid_path="/var/run/artica-notifier.pid";
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	$l[]="service_cmd=artica-notifier";	
	$l[]="service_disabled=1";
	$l[]="family=system";
	$l[]="pid_path=$pid_path";
	$l[]="watchdog_features=1";
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_ARTICA_NOTIFIER","artica-notifier");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	//$l[]="remove_cmd=--zarafa-remove";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);return;
	}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;
}
//========================================================================================================================================================

function autofs(){
	if(!$GLOBALS["CLASS_USERS"]->autofs_installed){
		if($GLOBALS["VERBOSE"]){echo "autofs_installed FALSE\n";}
		return;
	}
	if(!is_file('/etc/init.d/autofs')){
		if($GLOBALS["VERBOSE"]){echo "/etc/init.d/autofs no such file.\n";}
		return;
	}
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('automount');
	if($binpath==null){
		if($GLOBALS["VERBOSE"]){echo "automount no such binary.\n";}
		return;
	}
	if(is_file("/var/run/autofs-running")){$pid_path="/var/run/autofs-running";}
	if($pid_path==null){if(is_file("/var/run/automount.pid")){$pid_path="/var/run/automount.pid";}}
	
	
	$Enabled=1;
	$AutoFSCountDirs=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("AutoFSCountDirs");
	if(!is_numeric($AutoFSCountDirs)){$AutoFSCountDirs=0;}
	if($AutoFSCountDirs==0){$Enabled=0;}
	
	
	if($pid_path<>null){$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);}
	if(!is_numeric($master_pid)){$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);
	}
	$l[]="[APP_AUTOFS]";
	$l[]="service_name=APP_AUTOFS";
	$l[]="service_cmd=autofs";	
	$l[]="master_version=".GetVersionOf("autofs");
	$l[]="service_disabled=$Enabled";
	$l[]="family=network";
	$l[]="watchdog_features=1";	
	
	if($Enabled==0){return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_AUTOFS","autofs");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}




function greyhole(){
	
	if(!$GLOBALS["CLASS_USERS"]->GREYHOLE_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo "GREYHOLE_INSTALLED FALSE\n";}
		return;
	}
	
	$EnableGreyhole=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableGreyhole");
	if(!is_numeric($EnableGreyhole)){$EnableGreyhole=1;}
	
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('greyhole');
	if($binpath==null){
		if($GLOBALS["VERBOSE"]){echo "automount no such binary.\n";}
		return;
	}
	if(is_file("/var/run/greyhole.pid")){$pid_path="/var/run/greyhole.pid";}
	
	

	if($pid_path<>null){$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);}
	if(!is_numeric($master_pid)){$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN($binpath);}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);
	}
	
	if(!is_file("/etc/greyhole.conf")){$EnableGreyhole=0;}
	
	$l[]="[APP_GREYHOLE]";
	$l[]="service_name=APP_GREYHOLE";
	$l[]="service_cmd=greyhole";	
	$l[]="master_version=".GetVersionOf("greyhole");
	$l[]="service_disabled=$EnableGreyhole";
	$l[]="family=network";
	$l[]="watchdog_features=1";	
	if($EnableGreyhole==0){return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_GREYHOLE","greyhole");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
}


function greyhole_watchdog(){
	
	$greyhole=$GLOBALS["CLASS_UNIX"]->find_program('greyhole');
	$pgrep=$GLOBALS["CLASS_UNIX"]->find_program('pgrep');
	if(!is_file($greyhole)){
		events("greyhole is not installed",__FUNCTION__,__LINE__);
		return;
	}
	$kill=$GLOBALS["CLASS_UNIX"]->find_program('kill');
	events("$pgrep -l -f \"$greyhole --fsck\" 2>&1",__FUNCTION__,__LINE__);
	exec("$pgrep -l -f \"$greyhole --fsck\"",$results);
	if(count($results)==0){return;}
	while (list ($key, $value) = each ($results) ){
		events("$value",__FUNCTION__,__LINE__);
		if(!preg_match("#^([0-9]+)\s+#",$value,$re)){continue;}
		$pid=$re[1];
		if($GLOBALS["CLASS_UNIX"]->PID_IS_CHROOTED($pid)){continue;}
		$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($pid);
		events("Found pid $pid, $time minutes",__FUNCTION__,__LINE__);
		if(!is_file("/etc/greyhole.conf")){
			events("/etc/greyhole.conf no such file, kill process",__FUNCTION__,__LINE__);
			shell_exec("$kill -9 $pid >/dev/null 2>&1");
			continue;
		}
		if($time>120){
			events("killing PID $pid",__FUNCTION__,__LINE__);
			shell_exec("$kill -9 $pid >/dev/null 2>&1");
			$GLOBALS["CLASS_UNIX"]->send_email_events("greyhole process $pid was killed after {$time}Mn execution",
			"It reach max execution time : 120Mn ","system"
			);
		}
		
	}
}

function snort(){
	if(!$GLOBALS["CLASS_USERS"]->SNORT_INSTALLED){
		if($GLOBALS["VERBOSE"]){echo "SNORT_INSTALLED FALSE\n";}
		return;
	}
	
	$EnableSnort=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableSnort");
	if(!is_numeric($EnableSnort)){$EnableSnort=0;}	
	$snortInterfaces=unserialize(base64_decode($GLOBALS["CLASS_SOCKETS"]->GET_INFO("SnortNics")));
	if(count($snortInterfaces)==0){$EnableSnort=0;}
		
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('snort');
	if($binpath==null){
		if($GLOBALS["VERBOSE"]){echo "snort no such binary.\n";}
		return;
	}
	
	if($EnableSnort==0){
		$l[]="[APP_SNORT]";
		$l[]="service_name=APP_SNORT";
		$l[]="service_cmd=snort";	
		$l[]="master_version="._snort_version();
		$l[]="service_disabled=$EnableSnort";
		$l[]="family=network";
		$l[]="watchdog_features=1";	
		return implode("\n",$l);		
	}
	
	
	
	while (list ($eth, $ligne) = each ($snortInterfaces) ){
		
		$l[]="[APP_SNORT:$eth]";
		$l[]="service_name=APP_SNORT";
		$l[]="service_cmd=snort";	
		$l[]="master_version="._snort_version();
		$l[]="service_disabled=$EnableSnort";
		$l[]="family=network";
		$l[]="watchdog_features=1";			
		
		
		$pidpath="/var/run/snort_$eth.pid";
		$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pidpath);
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			WATCHDOG("APP_SNORT (Nic:$eth)","snort");
			$l[]="running=0\ninstalled=1";$l[]="";
		}else{
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";	
			
		}
	}	
	
	return implode("\n",$l);return;	
	
	
}

function _snort_pid(){
	if(is_file("/var/run/snort_eth0.pid")){return "/var/run/snort_eth0.pid";}
}
function _snort_version(){
	if(!isset($GLOBALS["SNORT_PATH"])){$GLOBALS["CLASS_UNIX"]=new unix();$GLOBALS["SNORT_PATH"]=$GLOBALS["CLASS_UNIX"]->find_program("snort");}
	exec("{$GLOBALS["SNORT_PATH"]} -V 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#Version\s+([0-9\.]+)#",$line,$re)){return $re[1];}  
		
	}
	return 0;
}


function dnsmasq(){
		
	if(!$GLOBALS["CLASS_USERS"]->dnsmasq_installed){
		if($GLOBALS["VERBOSE"]){echo "dnsmasq_installed FALSE\n";}
		return;
	}
	
	$EnableDNSMASQ=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableDNSMASQ");
	if(!is_numeric($EnableDNSMASQ)){$EnableDNSMASQ=1;}	
	
	if($GLOBALS["CLASS_USERS"]->POWER_DNS_INSTALLED){
		$EnablePDNS=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePDNS");
		if(!is_numeric($EnablePDNS)){$EnablePDNS=1;}
	}

	if($GLOBALS["CLASS_USERS"]->BIND9_INSTALLED){$EnableDNSMASQ=0;}	
	

	
	$binpath=$GLOBALS["CLASS_UNIX"]->find_program('dnsmasq');
	if($binpath==null){
		if($GLOBALS["VERBOSE"]){echo "dnsmasq no such binary.\n";}
		return;
	}
	$pid_path=_dnsmasq_pid();
	
	

	if($pid_path<>null){$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);}
	if(!is_numeric($master_pid)){$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN($binpath);}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($binpath);
	}
	if($EnablePDNS==1){$EnableDNSMASQ=0;}
	
	
	$l[]="[DNSMASQ]";
	$l[]="service_name=APP_DNSMASQ";
	$l[]="service_cmd=dnsmasq";	
	$l[]="master_version=".GetVersionOf("dnsmasq");
	$l[]="service_disabled=$EnableDNSMASQ";
	$l[]="family=network";
	$l[]="watchdog_features=1";	
	if($EnableDNSMASQ==0){return implode("\n",$l);return;}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_DNSMASQ","dnsmasq");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}

function _dnsmasq_pid(){
if(is_file("/var/run/dnsmasq.pid")){return "/var/run/dnsmasq.pid";}
if(is_file("/var/run/dnsmasq/dnsmasq.pid")){return "/var/run/dnsmasq/dnsmasq.pid";}
	
}


function dhcpd_server(){
	if(!$GLOBALS["CLASS_USERS"]->dhcp_installed){return;}
	$EnableDHCPServer=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableDHCPServer");
	if($EnableDHCPServer==null){$EnableDHCPServer=0;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_DHCPD_PID_PATH();
	
	
	$l[]="[DHCPD]";
	$l[]="service_name=APP_DHCP";
	$l[]="service_cmd=dhcp";	
	$l[]="master_version=".GetVersionOf("dhcpd");
	$l[]="service_disabled=$EnableDHCPServer";
	$l[]="family=network";
	$l[]="watchdog_features=1";
	if($EnableDHCPServer==0){$l[]="";return implode("\n",$l);return;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	$l[]="watchdog_features=1";
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_DHCP","dhcp");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	

	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);return;
	}	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}

function openvpn(){
	
	if(!$GLOBALS["CLASS_USERS"]->OPENVPN_INSTALLED){return;}
	
	$clientsDir=$GLOBALS["CLASS_UNIX"]->dirdir("/etc/artica-postfix/openvpn/clients");
	writelogs(count($clientsDir)." openvpn client session(s)",__FUNCTION__,__FILE__,__LINE__);
	if(count($clientsDir)>0){
		$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
		$cmd="$nohup {$GLOBALS["PHP5"]} /usr/share/artica-postfix/exec.openvpn.php --wakeup-clients >/dev/null 2>&1 &";
		shell_exec(trim($cmd));	
	}
	
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("openvpn");
	$EnableOPenVPNServerMode=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableOPenVPNServerMode");
	if($EnableOPenVPNServerMode==null){$EnableOPenVPNServerMode=0;}
	$pid_path="/var/run/openvpn/openvpn-server.pid";
	
	$l[]="[OPENVPN_SERVER]";
	$l[]="service_name=APP_OPENVPN";
	$l[]="service_cmd=openvpn";	
	$l[]="master_version=".GetVersionOf("openvpn");
	$l[]="service_disabled=$EnableOPenVPNServerMode";
	//$l[]="remove_cmd=--pureftpd-remove";
	$l[]="family=vpn";
	$l[]="watchdog_features=1";
	if($EnableOPenVPNServerMode==0){return implode("\n",$l);return;}
	

	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_OPENVPN","openvpn");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}
	
	$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
	$cmd="$nohup {$GLOBALS["PHP5"]} /usr/share/artica-postfix/exec.openvpn.php --wakeup-server >/dev/null 2>&1 &";
	shell_exec(trim($cmd));	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
	
}

function vnstat(){
if(!$GLOBALS["CLASS_USERS"]->APP_VNSTAT_INSTALLED){return;}
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("vnstatd");
	$EnableVnStat=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableVnStat");
	if($EnableVnStat==null){$EnableVnStat=1;}
	$pid_path="/var/run/vnstat.pid";
	
	$l[]="[APP_VNSTAT]";
	$l[]="service_name=APP_VNSTAT";
	$l[]="service_cmd=vnstat";	
	$l[]="master_version=".GetVersionOf("vnstat");
	$l[]="service_disabled=$EnableVnStat";
	//$l[]="remove_cmd=--pureftpd-remove";
	$l[]="family=network";
	$l[]="watchdog_features=1";
	if($EnableVnStat==0){return implode("\n",$l);return;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_VNSTAT","vnstat");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;			
	
}
function munin(){
if(!$GLOBALS["CLASS_USERS"]->MUNIN_CLIENT_INSTALLED){return;}
	$enabled=1;
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("munin-node");
	$MuninDisabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("MuninDisabled");
	if($MuninDisabled==null){$MuninDisabled=0;}
	$pid_path="/var/run/munin/munin-node.pid";
	if($MuninDisabled==1){$enabled=0;}
	$l[]="[APP_MUNIN]";
	$l[]="service_name=APP_MUNIN";
	$l[]="service_cmd=munin";	
	$l[]="master_version=".GetVersionOf("munin");
	$l[]="service_disabled=$enabled";
	$l[]="family=network";
	$l[]="watchdog_features=1";
	if($enabled==0){return implode("\n",$l);return;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_MUNIN","munin");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;			
	
}


function vboxguest(){
	if(!$GLOBALS["CLASS_USERS"]->APP_VBOXADDINTION_INSTALLED){return;}
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("VBoxService");
	if(!is_file($bin_path)){return;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_VBOX_ADDITIONS_PID();
	
	$l[]="[APP_VBOXADDITIONS]";
	$l[]="service_name=APP_VBOXADDITIONS";
	$l[]="service_cmd=vboxguest";	
	$l[]="master_version=".GetVersionOf("vboxguest");
	$l[]="service_disabled=1";
	$l[]="pid_path=$pid_path";
	//$l[]="remove_cmd=--pureftpd-remove";
	$l[]="family=system";
	$l[]="watchdog_features=1";
	
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_VBOXADDITIONS","vboxguest");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;		
	
	
}
//========================================================================================================================================================


	function pure_ftpd(){
		
	
	
	if(!$GLOBALS["CLASS_USERS"]->PUREFTP_INSTALLED){return;}
	
	$PureFtpdEnabled=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("PureFtpdEnabled");
	if($PureFtpdEnabled==null){$PureFtpdEnabled=0;}
	$pid_path=$GLOBALS["CLASS_UNIX"]->LOCATE_PURE_FTPD_PID_PATH();
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("pure-ftpd");
	
	$l[]="[PUREFTPD]";
	$l[]="service_name=APP_PUREFTPD";
	$l[]="service_cmd=ftp";	
	$l[]="master_version=".GetVersionOf("pure-ftpd");
	$l[]="service_disabled=$PureFtpdEnabled";
	$l[]="remove_cmd=--pureftpd-remove";
	$l[]="family=storage";
	$l[]="watchdog_features=1";
	if($PureFtpdEnabled==0){return implode("\n",$l);return;}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	$l[]="watchdog_features=1";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_PUREFTPD","ftp");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}	
//========================================================================================================================================================
	function mldonkey(){
		
	
	
	if(!$GLOBALS["CLASS_USERS"]->MLDONKEY_INSTALLED){return;}
	
	$EnableMLDonKey=trim($GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableMLDonKey"));
	if($EnableMLDonKey==null){$EnableMLDonKey=1;}
	$pid_path="/var/run/mlnet.pid";
	$bin_path=$GLOBALS["CLASS_UNIX"]->find_program("mlnet");
	
	$l[]="[APP_MLDONKEY]";
	$l[]="service_name=APP_MLDONKEY";
	$l[]="service_cmd=mldonkey";	
	$l[]="family=storage";
	$l[]="master_version=".GetVersionOf("mldonkey");
	$l[]="service_disabled=$EnableMLDonKey";
	//$l[]="remove_cmd=--pureftpd-remove";
	
	if($EnableMLDonKey==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	$l[]="watchdog_features=1";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF($bin_path);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_MLDONKEY","mldonkey");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}
//========================================================================================================================================================
	function policyd_weight(){
		
	
	
	
	
	$EnablePolicydWeight=trim($GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnablePolicydWeight"));
	if($EnablePolicydWeight==null){$EnablePolicydWeight=0;}
	
	$l[]="[POLICYD_WEIGHT]";
	$l[]="service_name=APP_POLICYD_WEIGHT";
	$l[]="service_cmd=policydw";	
	$l[]="family=postfix";
	$l[]="master_version=".GetVersionOf("policydw");
	$l[]="service_disabled=$EnablePolicydWeight";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--pureftpd-remove";
	
	if($EnablePolicydWeight==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$pid_path=$GLOBALS["CLASS_UNIX"]->POLICYD_WEIGHT_GET("PIDFILE");
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	$l[]="watchdog_features=1";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF('policyd-weight');
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("POLICYD_WEIGHT","policydw");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}
//========================================================================================================================================================
	function kav4fs(){
		
	
	
	if(!$GLOBALS["CLASS_USERS"]->KAV4FS_INSTALLED){return null;}
	
	$EnableKav4FS=trim($GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableKav4FS"));
	if($EnableKav4FS==null){$EnableKav4FS=1;}
	
	$l[]="[APP_KAV4FS]";
	$l[]="service_name=APP_KAV4FS";
	$l[]="family=system";	
	$l[]="service_cmd=kav4fs";	
	$l[]="master_version=".GetVersionOf("kav4fs");
	$l[]="service_disabled=$EnableKav4FS";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--pureftpd-remove";
	
	if($EnableKav4FS==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$pid_path="/var/run/kav4fs/supervisor.pid";
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	$l[]="watchdog_features=1";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF('kav4fs-supervisor');
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_KAV4FS","kav4fs");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}
//========================================================================================================================================================

	function gluster(){
		
	
	
	if(!$GLOBALS["CLASS_USERS"]->GLUSTER_INSTALLED){return null;}
	
	if(is_file("/etc/artica-cluster/glusterfs-server.vol")){$EnableGluster=1;}else{$EnableGluster=0;}
	
	
	$l[]="[GLUSTER]";
	$l[]="service_name=APP_GLUSTER";
	$l[]="service_cmd=gluster";	
	$l[]="family=storage";	
	$l[]="master_version=".GetVersionOf("gluster");
	$l[]="service_disabled=$EnableGluster";
	$l[]="watchdog_features=1";
	//$l[]="remove_cmd=--pureftpd-remove";
	
	if($EnableGluster==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$pid_path="/var/run/glusterfsd";
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_GLUSTER","gluster");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}
//========================================================================================================================================================
	function auditd(){
		
	
	
	if(!$GLOBALS["CLASS_USERS"]->APP_AUDITD_INSTALLED){return null;}
	
	
	$EnableAuditd=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableAuditd");
	if($EnableAuditd==null){$EnableAuditd=1;}
	
	
	$l[]="[APP_AUDITD]";
	$l[]="service_name=APP_AUDITD";
	$l[]="service_cmd=auditd";	
	$l[]="master_version=".GetVersionOf("auditd");
	$l[]="service_disabled=$EnableAuditd";
	$l[]="watchdog_features=1";
	$l[]="family=system";	
	//$l[]="remove_cmd=--pureftpd-remove";
	
	if($EnableAuditd==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$pid_path="/var/run/auditd.pid";
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_AUDITD","auditd");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}
//========================================================================================================================================================

function kav4fsavs(){
	
	
	
	if(!$GLOBALS["CLASS_USERS"]->KAV4FS_INSTALLED){return null;}
	
	$EnableKav4FS=trim($GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableKav4FS"));
	if($EnableKav4FS==null){$EnableKav4FS=1;}
	
	$l[]="[APP_KAV4FS_AVS]";
	$l[]="service_name=APP_KAV4FS_AVS";
	$l[]="service_cmd=kav4fs";	
	$l[]="master_version=".GetVersionOf("kav4fs");
	$l[]="service_disabled=$EnableKav4FS";
	$l[]="family=system";	
	//$l[]="remove_cmd=--pureftpd-remove";
	
	if($EnableKav4FS==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF('/opt/kaspersky/kav4fs/libexec/avs');
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
	
}


function backuppc(){
		
	
	
	$binpath="/usr/share/backuppc/bin/BackupPC";
	if(!is_file("/usr/share/backuppc/bin/BackupPC")){return;}
	
	$EnableBackupPc=trim($GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableBackupPc"));
	if($EnableBackupPc==null){$EnableBackupPc=1;}
	
	$l[]="[APP_BACKUPPC]";
	$l[]="service_name=APP_BACKUPPC";
	$l[]="service_cmd=backuppc";	
	$l[]="master_version=".GetVersionOf("backuppc");
	$l[]="service_disabled=$EnableBackupPc";
	$l[]="family=storage";	

	
	if($EnableBackupPc==0){
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	
	
	$pid_path="/var/run/backuppc/BackupPC.pid";
	$master_pid=$GLOBALS["CLASS_UNIX"]->get_pid_from_file($pid_path);
	
	$l[]="watchdog_features=1";
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		writelogs("$master_pid, process not exists",__FUNCTION__,__FILE__,__LINE__);
		$master_pid=$GLOBALS["CLASS_UNIX"]->PIDOF_PATTERN($binpath);
		writelogs("first, process not exists pidof return $master_pid",__FUNCTION__,__FILE__,__LINE__);
	}
	
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
		WATCHDOG("APP_BACKUPPC","backuppc");
		$l[]="running=0\ninstalled=1";$l[]="";
		return implode("\n",$l);
		return;
	}	

	$l[]="running=1";
	$l[]=GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;	
	
}


function GetMemoriesOf($pid){
	
	return $GLOBALS["CLASS_UNIX"]->GetMemoriesOf($pid);
	
}

function CheckCallable(){
	include_once("ressources/class.os.system.tools.inc");
	$methodVariable=array($GLOBALS["CLASS_UNIX"], 'GetVersionOf');
	if(!is_callable($methodVariable, true, $callable_name)){
		events("Loading unix class",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_UNIX"]=new unix();	
	}
	
	$methodVariable=array($GLOBALS["CLASS_UNIX"], 'find_program');
	if(!is_callable($methodVariable, true, $callable_name)){
		events("Loading unix class",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_UNIX"]=new unix();	
	}
	$methodVariable=array($GLOBALS["CLASS_SOCKETS"], 'GET_INFO');
	if(!is_callable($methodVariable, true, $callable_name)){
		events("Loading socket class",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_SOCKETS"]=new sockets();	
	}	
	
	
	$methodVariable=array($GLOBALS["CLASS_USERS"], 'BuildLeftMenus');
	if(!is_callable($methodVariable, true, $callable_name)){
		events("Loading usersMenus class",__FUNCTION__,__LINE__);
		$GLOBALS["CLASS_USERS"]=new settings_inc();	
	}

		$os=new os_system();
		$GLOBALS["MEMORY_INSTALLED"]=$os->memory();
		$GLOBALS["AMAVIS_WATCHDOG"]=unserialize(@file_get_contents("/etc/artica-postfix/amavis.watchdog.cache"));
		$os=null;	
	
	
}


function meta_checks(){
	$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
	$kill=$GLOBALS["CLASS_UNIX"]->find_program("kill");
	if(!is_file($pgrep)){
		events("pgrep no such file",__FUNCTION__,__LINE__);
		return;
	}
	events("$pgrep -f \"exec.artica.meta.users.php\" 2>&1",__FUNCTION__,__LINE__);
	exec("$pgrep -f \"exec.artica.meta.users.php\" 2>&1",$results);
	
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#([0-9]+)#",$line,$re)){
			events("checking process time of {$re[1]}",__FUNCTION__,__LINE__);
			if($GLOBALS["CLASS_UNIX"]->PID_IS_CHROOTED($re[1])){continue;}
			$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($re[1]);
			events("checking pid {$re[1]} {$time}Mn",__FUNCTION__,__LINE__);
			if($time>30){
				events("Killing pid {$re[1]} {$time}Mn",__FUNCTION__,__LINE__);
				shell_exec("$kill -9 {$re[1]} >/dev/null 2>&1");
			}
		}
	}
	
}


function getmem(){
		include_once("ressources/class.os.system.tools.inc");
		$os=new os_system();
		$GLOBALS["MEMORY_INSTALLED"]=$os->memory();
		$os=null;
		print_r($GLOBALS["MEMORY_INSTALLED"]);
	
}

function CheckCurl(){
	$results=array();
	$pidof=$GLOBALS["CLASS_UNIX"]->find_program("pidof");
	if($pidof==null){
		events("pidof no such file",__FUNCTION__,__LINE__);
		return;
	}
	$curl=$GLOBALS["CLASS_UNIX"]->find_program("curl");
	if($curl==null){
		events("curl binary no such file",__FUNCTION__,__LINE__);
		return;
	}	
	
	exec("$pidof $curl 2>&1",$results);
	if(count($results)==0){
		events("no curl instance in memory",__FUNCTION__,__LINE__);
		return;
	}
	
	while (list ($index, $pid) = each ($results) ){
		$pid=trim($pid);
		if(!is_numeric($pid)){continue;}
		if($pid<5){continue;}
		if($GLOBALS["CLASS_UNIX"]->process_exists($pid)){
			$time=$GLOBALS["CLASS_UNIX"]->PROCCESS_TIME_MIN($pid);
			events("$curl: $pid {$time}Mn",__FUNCTION__,__LINE__);
			if($time>60){
				events("$curl: too long time for $pid, kill it",__FUNCTION__,__LINE__);
				shell_exec("/bin/kill -9 $pid >/dev/null 2>&1");
			}
		}
	}
	
}

function GetVersionOf($name){
	if(isset($GLOBALS["GetVersionOf"][$name])){return $GLOBALS["GetVersionOf"][$name];}
	CheckCallable();
	$GLOBALS["GetVersionOf"][$name]=$GLOBALS["CLASS_UNIX"]->GetVersionOf($name);
	return $GLOBALS["GetVersionOf"][$name];	
}
function events($text,$function=null,$line=0){
		$filename=basename(__FILE__);
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $function:: $text (L.$line)","/usr/share/artica-postfix/ressources/logs/launch.status.task");	
		}	
		
function events_Loadavg($text,$function=null,$line=0){
		$filename=basename(__FILE__);
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $function:: $text (L.$line)","/var/log/artica-postfix/xLoadAvg.debug");	
		}			
		
function bandwith(){
	$EnableBandwithCalculation=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableBandwithCalculation");
	if(!is_numeric($EnableBandwithCalculation)){$EnableBandwithCalculation=0;}
	if($EnableBandwithCalculation==0){return;}
	$cmd="{$GLOBALS["NICE"]} {$GLOBALS["PHP5"]} ".dirname(__FILE__)."/exec.watchdog.php --bandwith >/dev/null 2>&1 &";
	events($cmd,__FUNCTION__,__LINE__);
	shell_exec($cmd);
}
	
function phpmyadmin_perms(){
	
	if(is_file("/usr/share/artica-postfix/mysql/config.inc.php")){@chmod("/usr/share/artica-postfix/mysql/config.inc.php",0600);}
}




?>
