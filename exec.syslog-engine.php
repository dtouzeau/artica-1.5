<?php
$GLOBALS["VERBOSE"]=false;
$GLOBALS["DEBUG"]=false;;
$GLOBALS["FORCE"]=false;
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.sockets.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');



if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}



if($argv[1]=='--build-server'){build_server_mode();die();}
if($argv[1]=='--build-client'){build_client_mode();die();}
if($argv[1]=='--auth-logs'){authlogs();sessions_logs();ipblocks();clamd_mem();admin_logs();crossroads();udfbguard_admin_events();dhcpd_logs();die();}
if($argv[1]=='--authfw'){authfw();sessions_logs();die();ipblocks();}
if($argv[1]=='--authfw-compile'){compile_sshd_rules();sessions_logs();ipblocks();die();}
if($argv[1]=='--snort'){snort_logs();sessions_logs();ipblocks();clamd_mem();crossroads();udfbguard_admin_events();die();}
if($argv[1]=='--sessions'){sessions_logs();die();}
if($argv[1]=='--loadavg'){loadavg_logs();clamd_mem();crossroads();udfbguard_admin_events();die();}
if($argv[1]=='--ipblocks'){ipblocks();die();}
if($argv[1]=='--adminlogs'){admin_logs();squid_tasks();crossroads();udfbguard_admin_events();dhcpd_logs();die();}
if($argv[1]=='--psmem'){ps_mem(true);squid_tasks();crossroads();udfbguard_admin_events();dhcpd_logs();die();}
if($argv[1]=='--squid-tasks'){squid_tasks(true);die();}





function build_server_mode(){
	$sock=new sockets();
	$ActAsASyslogServer=$sock->GET_INFO("ActAsASyslogServer");
	if(!is_numeric($ActAsASyslogServer)){
		echo "Starting......: syslog server parameters not defined, aborting tasks\n";
	}
	
	if(is_file("/etc/default/syslogd")){
		echo "Starting......: syslog old syslog mode\n";
		build_server_mode_debian();
		return;
	}
	
	if(is_dir("/etc/rsyslog.d")){
		echo "Starting......: syslog rsyslog mode\n";
		build_server_mode_ubuntu();
	}
}
function build_client_mode(){
	$sock=new sockets();
	$ActAsASyslogClient=$sock->GET_INFO("ActAsASyslogClient");
	if(!is_numeric($ActAsASyslogClient)){
		echo "Starting......: syslog client parameters not defined, aborting tasks\n";
	}
	
	if(is_file("/etc/default/syslogd")){
		echo "Starting......: syslog client old syslog mode\n";
		build_client_mode_debian();
		return;
	}
	
	if(is_dir("/etc/rsyslog.d")){
		echo "Starting......: syslog client rsyslog mode\n";
		build_server_mode_ubuntu();
	}
}


function build_client_mode_debian(){
	
	$sock=new sockets();
	$ActAsASyslogServer=$sock->GET_INFO("ActAsASyslogServer");
	$ActAsASyslogClient=$sock->GET_INFO("ActAsASyslogClient");
	if(!is_numeric($ActAsASyslogClient)){$ActAsASyslogClient=0;}
	if(!is_numeric($ActAsASyslogServer)){$ActAsASyslogServer=0;}	
	$serversList=unserialize(base64_decode($sock->GET_INFO("ActAsASyslogClientServersList")));
	$f=explode("\n",@file_get_contents("/etc/syslog.conf"));
	while (list ($num, $line) = each ($f) ){
		if(preg_match("#\*\.\*\s+@#",$line,$re)){
			$f[$num]=null;
			echo "Starting......: syslog client removing $line\n";
		}
	}
	
	reset($f);
	while (list ($num, $line) = each ($f) ){
		if(trim($line)==null){continue;}
		$g[]=$line;
	}
$g[]="";	
if($ActAsASyslogClient==1){
	if(count($serversList)>0){
		while (list ($num, $server) = each ($serversList) ){
			if($server==null){continue;}
			if(preg_match("#(.+?):([0-9]+)#",$server,$re)){$server=$re[1];}
			echo "Starting......: syslog client $server (forced to 514 port)\n";
			$s[]="*.*\t@$server";
		}
	}
}


$g[]="";

if(is_array($s)){
	$final=@implode("\n",$s)."\n".@implode("\n",$g);
}else{
	$final=@implode("\n",$g);
}

@file_put_contents("/etc/syslog.conf",$final);
echo "Starting......: syslog client /etc/syslog.conf done\n";
restart_syslog();	
	
	
}

function build_server_mode_debian(){
	$sock=new sockets();
	$ActAsASyslogServer=$sock->GET_INFO("ActAsASyslogServer");
	$moinsr=null;
	if($ActAsASyslogServer==1){
		echo "Starting......: syslog turn to master syslog server\n";
		$moinsr="-r";
	}
	
	$f[]="";
	$f[]="SYSLOGD=\"$moinsr\"";
	$f[]="";
	@file_put_contents("/etc/default/syslogd",@implode("\n",$f));
	restart_syslog();
}

function build_server_mode_ubuntu(){
	

	
	if(!is_dir("/etc/rsyslog.d")){
		echo "Starting......: syslog /etc/rsyslog.d no such directory\n";
		return;
	}
	$sock=new sockets();
	$ActAsASyslogServer=$sock->GET_INFO("ActAsASyslogServer");
	$ActAsASyslogClient=$sock->GET_INFO("ActAsASyslogClient");
	if(!is_numeric($ActAsASyslogClient)){$ActAsASyslogClient=0;}
	if(!is_numeric($ActAsASyslogServer)){$ActAsASyslogServer=0;}
	$serversList=array();
	
	if(($ActAsASyslogServer==0) && ($ActAsASyslogClient==0)){
		echo "Starting......: syslog Client or server are disabled\n";
		@unlink("/etc/rsyslog.d/artica.conf");
		return;
	}	
	
	$libdir=locate_rsyslog_lib();
	echo "Starting......: syslog libdir: $libdir\n";
	if(!is_file("$libdir/imudp.so")){
		echo "Starting......: syslog $libdir/imudp.so no such file\n";
		return; 
	}

if($ActAsASyslogServer==1){
	echo "Starting......: syslog master mode enabled\n";
}
if($ActAsASyslogClient==1){
	echo "Starting......: syslog client mode enabled\n";
	$serversList=unserialize(base64_decode($sock->GET_INFO("ActAsASyslogClientServersList")));
}

if(($ActAsASyslogServer==1) OR ($ActAsASyslogClient=1)){
	echo "Starting......: syslog define communications settings\n";
	$f[]="\$WorkDirectory /var/spool/rsyslog # where to place spool files";
	$f[]="\$ActionQueueFileName uniqName # unique name prefix for spool files";
	$f[]="\$ActionQueueMaxDiskSpace 1g   # 1gb space limit (use as much as possible)";
	$f[]="\$ActionQueueSaveOnShutdown on # save messages to disk on shutdown";
	$f[]="\$ActionQueueType LinkedList   # run asynchronously";
	$f[]="\$ActionResumeRetryCount -1    # infinite retries if host is down";
	$f[]="\$ModLoad imudp.so  # provides UDP syslog reception";
	$f[]="";
}


if($ActAsASyslogClient==1){
	if(count($serversList)>0){
		while (list ($num, $server) = each ($serversList) ){
			if($server==null){continue;}
			$f[]="*.*\t@$server";
		}
	}
}

$f[]="";
$f[]="#\$ModLoad imtcp.so  # load module";

if(is_file("$libdir/imklog.so")){
	echo "Starting......: syslog set imklog module\n";
	$f[]="\$ModLoad imklog.so  # load module";
}
if(is_file("$libdir/immark.so")){
	echo "Starting......: syslog set immark module\n";
	$f[]="\$ModLoad immark.so  # load module";
}
   

if($ActAsASyslogServer==1){
	$f[]="\$UDPServerRun 514 # start a UDP syslog server at standard port 514";
}


$f[]="#\$DefaultNetstreamDriver gtls";
$f[]="#\$DefaultNetstreamDriverCAFile /etc/rsyslog.d/ca.pem";
$f[]="#\$DefaultNetstreamDriverCertFile /etc/rsyslog.d/server_cert.pem";
$f[]="#\$DefaultNetstreamDriverKeyFile /etc/rsyslog.d/server_key.pem";
$f[]="#\$ModLoad imtcp # load TCP listener";
$f[]="#\$InputTCPServerStreamDriverMode 1 # run driver in TLS-only mode";
$f[]="#\$InputTCPServerStreamDriverAuthMode anon # client is NOT authenticated";
$f[]="#\$InputTCPServerRun 10514 # start up listener at port 10514";
$f[]="#\$DefaultNetstreamDriverCAFile /etc/rsyslog.d/ca.pem";
$f[]="#\$DefaultNetstreamDriver gtls # use gtls netstream driver";
$f[]="#\$ActionSendStreamDriverMode 1 # require TLS for the connection";
$f[]="#\$ActionSendStreamDriverAuthMode anon # server is NOT authenticated";
$f[]="#*.* @@(o)server.example.net:10514 # send (all) messages";
$f[]="";

@file_put_contents("/etc/rsyslog.d/artica.conf",@implode("\n",$f));
restart_syslog();	
}


function restart_syslog(){
	echo "Starting......: syslog restart daemon\n";
	$unix=new unix();
	$sysloginit=$unix->LOCATE_SYSLOG_INITD();
	if(!is_file($sysloginit)){echo "Starting......: syslog init.d/*? no such file\n";return;}
	exec("$sysloginit restart 2>&1",$results);
	while (list ($num, $line) = each ($results)){
		if(trim($line)==null){continue;}
		echo "Starting......: syslog $line\n";
	}
		
}

function locate_rsyslog_lib(){
	if(is_file("/usr/lib/rsyslog/imudp.so")){return "/usr/lib/rsyslog";}
	if(is_file("/usr/lib64/rsyslog/imudp.so")){return "/usr/lib64/rsyslog";}
	if(is_file("/lib/rsyslog/imudp.so")){return "/lib/rsyslog";}
	if(is_file("/lib64/rsyslog/imudp.so")){return "/lib64/rsyslog";}
	
}


function admin_logs(){
	
	if(system_is_overloaded()){return;}
	
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
	$t=0;
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	$q=new mysql();
	foreach (glob("/var/log/artica-postfix/adminevents/*") as $filename) {
		$sql=@file_get_contents($filename);
		if(trim($sql)==null){@unlink($filename);}
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){writelogs("Fatal, $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
			if(strpos($q->mysql_error,"Column count doesn't match value count")>0){@unlink($filename);}
			if(strpos($q->mysql_error,"nknown column")>0){writelogs("Fatal -> DROP TABLE ",__FUNCTION__,__FILE__,__LINE__);$q->QUERY_SQL("DROP TABLE adminevents","artica_events");$q->BuildTables();}
		continue;}
		@unlink($filename);
	}
	
	ps_mem();
		
}


function udfbguard_admin_events($nopid=false){
	$f=array();
	if($nopid){
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;		
		
	}	
	
$q=new mysql();	
	if(!$q->TABLE_EXISTS('ufdbguard_admin_events','artica_events')){$q->BuildTables();}
	if(!$q->TABLE_EXISTS('ufdbguard_admin_events','artica_events',true)){return;mysql_admin_events_check();}
	$prefix="INSERT IGNORE INTO ufdbguard_admin_events (`zDate`,`function`,`filename`,`line`,`description`,`category`) VALUES ";
	foreach (glob("/var/log/artica-postfix/ufdbguard_admin_events/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){
			$array["text"]=basename($filename)." is not an array, skip event ".@file_get_contents($filename);
			$array["date"]=date('Y-m-d H:i:s');
			$array["pid"]=getmypid();
			$array["function"]=__FUNCTION__;
			$array["category"]="parser";
			$array["file"]=basename(__FILE__);
			$array["line"]=__LINE__;
		}			
			
			
			
		$array["text"]=addslashes($array["text"]);
		$f[]="('{$array["zdate"]}','{$array["function"]}','{$array["file"]}','{$array["line"]}','{$array["text"]}','{$array["category"]}')";
		@unlink($filename);
	}
	
	if(count($f)>0){$sql=$prefix.@implode(",", $f);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		}
	
	}
	
	$num=$q->COUNT_ROWS("ufdbguard_admin_events","artica_events");
	if($num>4000){$q->QUERY_SQL("DELETE FROM ufdbguard_admin_events ORDER BY zDate LIMIT 4000","artica_events");}
	mysql_admin_events_check();
	
	
}
function mysql_admin_events_check($nopid=false){
	$f=array();
	if($nopid){
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;		
		
	}	
	
$q=new mysql();	
	if(!$q->TABLE_EXISTS('mysql_events','artica_events')){$q->BuildTables();}
	if(!$q->TABLE_EXISTS('mysql_events','artica_events',true)){return;}
	$users=new usersMenus();
	$hostname=$users->hostname;
	$prefix="INSERT IGNORE INTO mysql_events (`zDate`,`function`,`process`,`line`,`description`,`category`,`servername`) VALUES ";
	foreach (glob("/var/log/artica-postfix/mysql_admin_events/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){
			$array["text"]=basename($filename)." is not an array, skip event ".@file_get_contents($filename);
			$array["date"]=date('Y-m-d H:i:s');
			$array["pid"]=getmypid();
			$array["function"]=__FUNCTION__;
			$array["category"]="parser";
			$array["file"]=basename(__FILE__);
			$array["line"]=__LINE__;
		}			
			
			
			
		$array["text"]=addslashes($array["text"]);
		$f[]="('{$array["zdate"]}','{$array["function"]}','{$array["file"]}','{$array["line"]}','{$array["text"]}','{$array["category"]}','$hostname')";
		@unlink($filename);
	}
	
	if(count($f)>0){$sql=$prefix.@implode(",", $f);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		}
	
	}
	
	$num=$q->COUNT_ROWS("mysql_events","artica_events");
	if($num>4000){$q->QUERY_SQL("DELETE FROM mysql_events ORDER BY zDate LIMIT 4000","artica_events");}

	
	
}
function dhcpd_logs($nopid=false){
	$f=array();
	if($nopid){
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;		
		
	}
	$q=new mysql();	
	if(!$q->TABLE_EXISTS('dhcpd_logs','artica_events')){$q->BuildTables();}
	if(!$q->TABLE_EXISTS('dhcpd_logs','artica_events',true)){return;}
	$prefix="INSERT IGNORE INTO dhcpd_logs (`zDate`,`description`) VALUES ";
	foreach (glob("/var/log/artica-postfix/dhcpd/*") as $filename) {
		$sqlcontent=@file_get_contents($filename);
		if(trim($sqlcontent)==null){@unlink($filename);continue;}
		
		$f[]=$sqlcontent;
		@unlink($filename);
	}
	
	if(count($f)>0){$sql=$prefix.@implode(",", $f);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		}
	
	}
	
	$num=$q->COUNT_ROWS("dhcpd_logs","artica_events");
	if($num>400000){$q->QUERY_SQL("DELETE FROM dhcpd_logs ORDER BY zDate LIMIT 400000","artica_events");}	
	
	
}



function crossroads($nopid=false){
	$f=array();
	if($nopid){
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;		
		
	}
	$q=new mysql();	
	if(!$q->TABLE_EXISTS('crossroads_events','artica_events')){$q->BuildTables();}
	if(!$q->TABLE_EXISTS('crossroads_events','artica_events',true)){return;}
	$prefix="INSERT IGNORE INTO crossroads_events (`zDate`,`instance_id`,`function`,`line`,`description`) VALUES ";
	foreach (glob("/var/log/artica-postfix/crossroads/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){@unlink($filename);continue;}		
		$array["TEXT"]=addslashes($array["TEXT"]);
		$f[]="('{$array["TIME"]}','{$array["ID"]}','{$array["FUNCTION"]}','{$array["LINE"]}','{$array["TEXT"]}')";
		@unlink($filename);
	}
	
	if(count($f)>0){$sql=$prefix.@implode(",", $f);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		}
	
	}
	
	$num=$q->COUNT_ROWS("crossroads_events","artica_events");
	if($num>4000){$q->QUERY_SQL("DELETE FROM crossroads_events ORDER BY zDate LIMIT 4000","artica_events");}
	
}


function squid_tasks($nopid=false){
	$f=array();
	if($nopid){
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;		
		
	}

	
	
	$prefix="INSERT IGNORE INTO updateblks_events (`zDate`,`PID`,`function`,`text`,`category`) VALUES ";
	foreach (glob("/var/log/artica-postfix/artica-squid-events/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){@unlink($filename);continue;}
		$array["text"]=addslashes($array["text"]);
		$f[]="('{$array["date"]}','{$array["pid"]}','{$array["function"]}','{$array["text"]}','{$array["category"]}')";
		@unlink($filename);
	}
	
	if(count($f)>0){$q=new mysql_squid_builder();$sql=$prefix.@implode(",", $f);$q->QUERY_SQL($sql);}
	
	
	
}

function ps_mem($nopid=false){
	
	if($nopid){
		$unix=new unix();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){writelogs("Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);return;}	
		$t=0;		
		
	}
	
	$q=new mysql();
	$prefix="INSERT IGNORE INTO ps_mem (zmd5,zDate,process,memory) VALUES ";
	if($GLOBALS["VERBOSE"]){writelogs("Starting glob()...",__FUNCTION__,__FILE__,__LINE__);}
	foreach (glob("/var/log/artica-postfix/ps-mem/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){@unlink($filename);continue;}
		$md5=md5(serialize($array));
		$f[]="('$md5','{$array["time"]}','{$array["process"]}','{$array["mem"]}')";
		if(count($f)>500){
			$sql=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){writelogs("Fatal, $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		}
		@unlink($filename);
		
	}
	
	if(count($f)>0){
			$sql=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){writelogs("Fatal, $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		}	
		
// -------------------------------------------------------------------------

		
	$prefix="INSERT IGNORE INTO ps_mem_tot (zDate,mem) VALUES ";
	foreach (glob("/var/log/artica-postfix/ps-mem-tot/*") as $filename) {	
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){@unlink($filename);continue;}
		$md5=md5(serialize($array));
		$f[]="('{$array["time"]}','{$array["mem"]}')";
		if(count($f)>500){
			$sql=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){writelogs("Fatal, $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		}
		@unlink($filename);
		
	}
	
	if(count($f)>0){
			$sql=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){writelogs("Fatal, $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		}		
	
}

		
function snort_logs(){
	
	if(system_is_overloaded()){return;}
	
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		echo "Already running pid $pid\n";
		return;
	}	
	$t=0;
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	$q=new mysql();
	foreach (glob("/var/log/artica-postfix/snort-queue/*") as $filename) {
		$base=basename($filename);
		if(!preg_match("#([0-9]+)\..+?\.snort#",$base,$re)){@unlink($filename);continue;}
		$zDate=date("Y-m-d H:i:s",$re[1]);
		echo $zDate." -> {$re[1]}\n";
		$array=unserialize(@file_get_contents($filename));
		if(!is_array($array)){@unlink($filename);continue;}
		$local_ipaddr=$array[7];
		$port=$array[8];
		$ipaddr=$array[5];
		if(!isset($GLOBALS["RESOLV"][$ipaddr])){
			$hostname=gethostbyaddr($ipaddr);
			$GLOBALS["RESOLV"][$ipaddr]=$hostname;
		}else{
			$hostname=$GLOBALS["RESOLV"][$ipaddr]=$hostname;
		}
		if(!isset($GLOBALS["GEO"][$ipaddr])){
			if(function_exists("geoip_record_by_name")){
				$record = geoip_record_by_name($ipaddr);
				if ($record) {
					$country=$record["country_name"];
					$GLOBALS["GEO"][$ipaddr]=$country;
				}
			}
		}else{
			$country=$GLOBALS["GEO"][$ipaddr];
		}	
		$infos=$array[1];
		$classification=$array[2];
		if(preg_match("#SCAN.+?Port.+?attempt#",$infos)){$unix->send_email_events("$infos FROM $ipaddr","Country:$country\nHostname:$hostname\nclassification:$classification","security");}

		$proto=$array[4];
		$priority=$array[3];
		if($GLOBALS["VERBOSE"]){echo "$hostname\n";}
		
		
		
		$sql="INSERT IGNORE INTO `snort`
		(`zDate`,`hostname`,`ipaddr` ,`local_ipaddr`,`port`,`infos`,`classification`,`priority`,`proto` ,`country`)
  VALUES('$zDate','$hostname','$ipaddr' ,'$local_ipaddr','$port','$infos','$classification','$priority','$proto' ,'$country')";

		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";}
			continue;
		}
		$t++;
		@unlink($filename);
	}
	
	if($t>0){
		writelogs("Adding $t entries",__FUNCTION__,__FILE__,__LINE__);
	}
	
	$sock=new sockets();
	$SnortMaxMysqlEvents=$sock->GET_INFO("SnortMaxMysqlEvents");
	if(!is_numeric($SnortMaxMysqlEvents)){$SnortMaxMysqlEvents=700000;}
	$lockFileTime=$unix->file_time_min("/etc/artica-postfix/pids/snort.purge.lock");
	
	
	
	if($lockFileTime>300){
		writelogs("/etc/artica-postfix/pids/snort.purge.lock {$lockFileTime}Mn",__FUNCTION__,__FILE__,__LINE__);
		$tablecount=$q->COUNT_ROWS("snort","artica_events");
		if($tablecount>$SnortMaxMysqlEvents){
			$limit=$tablecount-$SnortMaxMysqlEvents;
			$sql="DELETE FROM snort ORDER BY zDate LIMIT $limit";
			writelogs("Delete first $limit entries ($tablecount/$SnortMaxMysqlEvents) \"$sql\"",__FUNCTION__,__FILE__,__LINE__);
			$q->QUERY_SQL($sql,"artica_events");
			@unlink("/etc/artica-postfix/pids/snort.purge.lock");
			
		}
		@file_put_contents("/etc/artica-postfix/pids/snort.purge.lock", time());
	}
	
}


function authlogs(){
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	include_once(dirname(__FILE__) . '/ressources/class.auth.tail.inc');
	include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){echo "Already running pid $pid\n";return;}
	
	$q=new mysql();
	foreach (glob("/var/log/artica-postfix/sshd-failed/*") as $filename) {
		events("Open $filename",__FUNCTION__,__FILE__,__LINE__);
		$array=unserialize(@file_get_contents($filename));
		$zdate=date("Y-m-d H:i:s",basename($filename));
		while (list ($ip, $uid) = each ($array)){
			$hostname=gethostbyaddr($ip);
			if(function_exists("geoip_record_by_name")){
				$record = geoip_record_by_name($ip);
				if (!$record) {ssh_events("Unable to detect country for $ip",__FUNCTION__,__FILE__,__LINE__);}else{
					$Country=$record["country_name"];
				}
			}
			$Country=addslashes($Country);
			ssh_events("SSH Failed $ip $hostname ($Country)",__FUNCTION__,__FILE__,__LINE__);
			$sql="INSERT IGNORE INTO auth_events (ipaddr,hostname,success,uid,zDate,Country) VALUES ('$ip','$hostname','0','$uid','$zdate','$Country')";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){ssh_events($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);}else{@unlink($filename);}}
	}
	
	foreach (glob("/var/log/artica-postfix/sshd-success/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		$zdate=date("Y-m-d H:i:s",basename($filename));
		while (list ($ip, $uid) = each ($array)){
			if(!isset($GLOBALS["HOSTNAME"][$ip])){$GLOBALS["HOSTNAME"][$ip]=gethostbyaddr($ip);}
			$hostname=$GLOBALS["HOSTNAME"][$ip];
			
			if(function_exists("geoip_record_by_name")){
					$record = geoip_record_by_name($ip);
					if (!$record) {ssh_events("Unable to detect country for $ip",__FUNCTION__,__FILE__,__LINE__);}else{
						$Country=$record["country_name"];
					}
				}	
			$Country=addslashes($Country);
			ssh_events("SSH Success $ip $hostname ($Country)",__FUNCTION__,__FILE__,__LINE__);		
			$sql="INSERT IGNORE INTO auth_events (ipaddr,hostname,success,uid,zDate,Country) VALUES ('$ip','$hostname','1','$uid','$zdate','$Country')";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){ssh_events($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);}else{@unlink($filename);}}
		}

		authfw();
		snort_logs();
		loadavg_logs();
		clamd_mem();
}

function clamd_mem(){
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	$f=array();
	$unix=new unix();
	$q=new mysql();
	if(!$q->TABLE_EXISTS("clamd_mem", "artica_events")){
		$q->QUERY_SQL("CREATE TABLE `artica_events`.`clamd_mem` (`zDate` TIMESTAMP NOT NULL ,`rss` INT( 10 ) NOT NULL ,`vm` INT( 10 ) NOT NULL ,PRIMARY KEY ( `zDate` ))","artica_events");
	}
	$prefix="INSERT IGNORE INTO clamd_mem (zDate,rss,vm) VALUES ";
	
	
	foreach (glob("/var/log/artica-postfix/clamd-mem/*") as $filename) {
		events("Open $filename",__FUNCTION__,__FILE__,__LINE__);
		$content=trim(@file_get_contents($filename));
		@unlink($filename);
		if($content==null){continue;}
		$f[]=$content;
		if(count($f)>100){
			$sql=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
		}
		
	}
	
		if(count($f)>0){
			$sql=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($sql,"artica_events");
		}	
	
	$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$time=$unix->file_time_min($timefile);
	if($time>380){
		@unlink($time);
		@file_put_contents($timefile, time());
		$sql="DELETE FROM clamd_mem WHERE zDate<DATE_SUB(NOW(),INTERVAL 7 DAY) ORDER BY zDate LIMIT 4000";
		$q->QUERY_SQL($sql,"artica_events");
	}
	
}

function sessions_logs(){
	if(system_is_overloaded()){return;}
	$q=new mysql();
	foreach (glob("/usr/share/artica-postfix/ressources/logs/web/queue/sessions/*") as $filename) {
		$base=basename($filename);
		if(!preg_match("#([0-9]+)\.(.+)#",$base,$re)){@unlink($filename);continue;}
		$array=unserialize(@file_get_contents($filename));
		@unlink($filename);
		if(!is_array($array)){
			writelogs("Not an array... $base",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		if(strlen($array["SESSION_ID"])<3){
			writelogs("SESSION_ID is null...({$array["SESSION_ID"]}) $base",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		$sql="DELETE FROM admin_cnx WHERE session_id='{$array["SESSION_ID"]}'";
		$connected=date('Y-m-d H:i:s',$re[1]);
		$q->QUERY_SQL($sql,"artica_events");
		if(!isset($GLOBALS["HOSTNAME"][$array["ipaddr"]])){$GLOBALS["HOSTNAME"][$array["ipaddr"]]=gethostbyaddr($array["ipaddr"]);}
		$hostname=$GLOBALS["HOSTNAME"][$array["ipaddr"]];
		$sql="INSERT IGNORE INTO admin_cnx(connected,session_id,ipaddr,InterfaceType,webserver,hostname,uid) VALUES
		('$connected','{$array["SESSION_ID"]}','{$array["ipaddr"]}','{$array["interface"]}','{$array["myname"]}',
		'$hostname','{$array["uid"]}')";
		
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		
	}
	
	$sql="DELETE FROM admin_cnx WHERE connected<DATE_SUB(NOW(), INTERVAL 3600 SECOND)";
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}	
	loadavg_logs();
}




function authfw(){
	if($GLOBALS["VERBOSE"]){echo "authfw()\n";}
	$unix=new unix();
	$iptablesClass=new iptables_chains();
	$iptables=$unix->find_program("iptables");
	$GLOBALS["IPTABLES_WHITELISTED"]=$iptablesClass->LoadWhiteLists();
	$unix=new unix();
	$c=0;
	foreach (glob("/etc/artica-postfix/sshd-fw/*") as $filename) {
		$array=unserialize(@file_get_contents($filename));
		$zdate=date("Y-m-d H:i:s",basename($filename));	
		while (list ($IP, $server_name) = each ($array)){
		if($iptablesClass->isWhiteListed($IP)<>null){@unlink($filename);continue;}
		
		$cmd="$iptables -A INPUT -s $IP -p tcp --destination-port 22 -j DROP -m comment --comment \"ArticaInstantSSH\"";
		$iptablesClass=new iptables_chains();
		$iptablesClass->serverip=$IP;
		$iptablesClass->servername=$server_name;
		$iptablesClass->rule_string=$cmd;
		$iptablesClass->EventsToAdd="Max SSHD connexions";
		if($iptablesClass->addSSHD_chain()){
			$unix->send_email_events("SSHD Hack!: $server_name [$IP] has been banned to your SSH",
			"Artica anti-hack SSH has banned this ip address","system");
			$c++;
			ssh_events("Add IP:Addr=<$IP>, servername=<{$server_name}> to mysql",__FUNCTION__,__FILE__,__LINE__);
			if($GLOBALS["VERBOSE"]){echo "Add IP:Addr=<$IP>, servername=<{$server_name}> to mysql\n";}
			@unlink($filename);
			}

		}
	}
	
	if($c>0){compile_sshd_rules();}
	loadavg_logs();
}

	function ssh_events($text,$function,$file,$line){
		writelogs($text,$function,$file,$line);
		$pid=@getmypid();
		$filename=basename(__FILE__);
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/auth-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		$logFile="/var/log/artica-postfix/syslogger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text",$logFile);
		}



function compile_sshd_rules(){
	include_once(dirname(__FILE__)."/ressources/class.openssh.inc");
	$q=new mysql();	
	$iptablesClass=new iptables_chains();
	$unix=new unix();
	$openssh=new openssh();
	$SSHDPort=$openssh->main_array["Port"];
	if(!is_numeric($SSHDPort)){$SSHDPort=22;}
	$iptables=$unix->find_program("iptables");
	$GLOBALS["IPTABLES_WHITELISTED"]=$iptablesClass->LoadWhiteLists();	
	$sql="SELECT * FROM iptables WHERE disable=0 AND flux='INPUT' AND local_port=22";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	iptables_delete_all();
	
	if($GLOBALS["VERBOSE"]){echo "OpenSSH port is $SSHDPort\n";}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=$ligne["serverip"];
		if($iptablesClass->isWhiteListed($ip)){continue;}
		events("ADD REJECT {$ligne["serverip"]} INBOUND PORT 22");
		ssh_events("ADD REJECT {$ligne["serverip"]} INBOUND PORT 22",__FUNCTION__,__FILE__,__LINE__);

		/*if($InstantIptablesEventAll==1){
			if($GLOBALS["VERBOSE"]){echo "$ip -> LOG\n";}
			$cmd="$iptables -A INPUT -s $ip -p tcp --destination-port 25 -j LOG --log-prefix \"SMTP DROP: \" -m comment --comment \"ArticaInstantPostfix\"";
			$commands[]=$cmd;
		}*/
		
		$cmd="$iptables -A INPUT -s $ip -p tcp --destination-port $SSHDPort -j DROP -m comment --comment \"ArticaInstantSSH\"";
		$commands[]=$cmd;
	}
	
	if($GLOBALS["VERBOSE"]){echo count($commands)." should be performed\n";}
	
	if(is_array($commands)){
		while (list ($index, $line) = each ($commands) ){
			writelogs($line,__FUNCTION__,__FILE__,__LINE__);
			if($GLOBALS["VERBOSE"]){echo $line."\n";}
			shell_exec($line);
		}
		
		$unix->send_email_events("SSHD Hack ".count($commands)." rules(s) added",null,"system");
		
	}	

	
	
	
}

function iptables_delete_all(){
$unix=new unix();
$iptables_restore=$unix->find_program("iptables-restore");
$iptables_save=$unix->find_program("iptables-save");	
events("Exporting datas iptables-save > /etc/artica-postfix/iptables.conf");
system("$iptables_save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaInstantSSH#";	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){continue;}
		events("skip rule $ligne from deletion");
		$conf=$conf . $ligne."\n";
		}

events("restoring datas $iptables_restore < /etc/artica-postfix/iptables.new.conf");
file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("$iptables_restore < /etc/artica-postfix/iptables.new.conf");


}

function events($text){
		$pid=@getmypid();
		$filename=basename(__FILE__);
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/auth-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		$logFile="/var/log/artica-postfix/syslogger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text",$logFile);
}

function loadavg_logs(){
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){echo "Already running pid $pid\n";return;}	
	$q=new mysql();
	if(!$q->DATABASE_EXISTS("artica_events")){
		events_Loadavg("loadavg_logs:: artica_events database does not exists... try to build one".__LINE__);
		$q->BuildTables();
	}
	
	if(!$q->DATABASE_EXISTS("artica_events")){
		events_Loadavg("loadavg_logs:: artica_events database cannot continue".__LINE__);
		return;
	}	
	
	foreach (glob("/var/log/artica-postfix/loadavg/*") as $filename) {
		$time=basename($filename);
		$load=@file_get_contents($filename);
		$date=date('Y-m-d H:i:s',$time);
		$sql="INSERT IGNORE INTO loadavg (`stime`,`load`) VALUES ('$date','$load');";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){events_Loadavg("loadavg_logs:: $q->mysql_error line:".__LINE__);continue;}
		events_Loadavg("loadavg_logs:: success $filename".__LINE__);
		@unlink($filename);
	}
	
	$file_time="/etc/artica-postfix/pids/". basename(__FILE__).".".__FUNCTION__.".time";
	if($unix->file_time_min($file_time)>300){
		$sql="DELETE FROM loadavg WHERE stime < DATE_SUB( NOW( ) , INTERVAL 7 DAY )";
		$q->QUERY_SQL($sql,"artica_events");
		@unlink($file_time);
		@file_put_contents($file_time, time());
	}
	
	
}

function events_Loadavg($text,$function=null,$line=0){
		$filename=basename(__FILE__);
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $function:: $text (L.$line)","/var/log/artica-postfix/xLoadAvg.debug");	
		}	

function ipblocks(){
	if(system_is_overloaded()){return;}
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nogup=$unix->find_program("nohup");
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pidtime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){echo "Already running pid $pid\n";return;}	
	$q=new mysql();
	if(!$q->TABLE_EXISTS('ipblocks_db','artica_backup')){$q->BuildTables();}
	if(!is_file($pidtime)){
		$count=$q->COUNT_ROWS("ipblocks_db", "artica_backup");
		if($count==0){shell_exec(trim("$nogup /usr/share/artica-postfix/bin/artica-update --ipblocks >/dev/null 2>&1 &"));}
		sleep(5);
		@file_put_contents($pidtime, time());
	}
	
	if($unix->file_time_min($pidtime)>480){
		shell_exec(trim("$nogup /usr/share/artica-postfix/bin/artica-update --ipblocks >/dev/null 2>&1 &"));
		sleep(5);
		@unlink($pidtime);
		@file_put_contents($pidtime, time());
		$unix->THREAD_COMMAND_SET("$php /usr/share/artica-postfix/exec.postfix.iptables.php --ipdeny");
	}
	
	@file_put_contents($pidfile, getmypid());
	
	foreach (glob("/var/log/artica-postfix/ipblocks/*.zone") as $filename) {
		$basename=basename($filename);
		if(!preg_match("#(.+?)\.zone#", $basename,$re)){continue;}
		$country=$re[1];
		$datas=explode("\n", @file_get_contents($filename));
		$f=true;
		
		while (list ($index, $line) = each ($datas) ){
			$line=trim($line);if($line==null){continue;}if($country==null){continue;}
			$sql="INSERT IGNORE INTO ipblocks_db (cdir,country) VALUES('$line','$country')";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){events("ipblocks:: $q->mysql_error line:".__LINE__);$f=false;break;}
		}
		if(!$f){continue;}
		@unlink($filename);
	}
	
	$file_time="/etc/artica-postfix/pids/". basename(__FILE__).".".__FUNCTION__.".time";
	if($unix->file_time_min($file_time)>300){
		$sql="DELETE FROM loadavg WHERE stime < DATE_SUB( NOW( ) , INTERVAL 7 DAY )";
		$q->QUERY_SQL($sql,"artica_events");
		@unlink($file_time);
		@file_put_contents($file_time, time());
	}
	
	
}

?>