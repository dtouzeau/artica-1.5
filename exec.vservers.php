<?php
$GLOBALS["VERBOSE"]=false;$GLOBALS["DEBUG"]=false;$GLOBALS["NORELOAD"]=false;$GLOBALS["LXCVERSION"]="";
$GLOBALS["NOWATCHDOG"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');
include_once(dirname(__FILE__).'/ressources/class.system.nics.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');


if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--no-reload#",implode(" ",$argv))){$GLOBALS["NORELOAD"]=true;}
	if(preg_match("#--nowatchdog#",implode(" ",$argv))){$GLOBALS["NOWATCHDOG"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set_verbosed();}
	writelogs(implode(" ",$argv),"MAIN",__FILE__,__LINE__);
	getversion();
}

if($argv[1]=="--install-bridge"){install_bridge();die();}
if($argv[1]=="--uninstall-bridge"){uninstall_bridge();die();}
if($argv[1]=="--debian-array"){debian_array();die();}
if($argv[1]=="--isroute"){_IfRouteExists($argv[2],$argv[3]);die();}
if($argv[1]=="--check"){CheckLxcMaster();die();}
if($argv[1]=="--vps-servers"){vps_servers_check();die();}
if($argv[1]=="--vps-server-mod"){vps_server_check_single($argv[2]);die();}
if($argv[1]=="--status"){vps_servers_status();die();}
if($argv[1]=="--status-single"){echo vps_servers_status_single($argv[2]);die();}
if($argv[1]=="--lxc-ps"){lxc_ps($argv[2]);die();}
if($argv[1]=="--lxc-restart"){vps_restart($argv[2]);die();}
if($argv[1]=="--lxc-stop"){vps_stop($argv[2]);die();}
if($argv[1]=="--lxc-start"){vps_start($argv[2]);die();}
if($argv[1]=="--lxc-freeze"){vps_freeze($argv[2]);die();}
if($argv[1]=="--lxc-unfreeze"){vps_unfreeze($argv[2]);die();}



if($argv[1]=="--watchdog"){vps_servers_watchdogs();die();}
if($argv[1]=="--interfaces-list"){print_r(ifconfiglisteth());die();}
if($argv[1]=="--checkbridge"){preup_checkbridgre($argv[2],$argv[3]);die(0);}
if($argv[1]=="--lxc-config"){buildconfig($argv[2]);die();}
if($argv[1]=="--retraduct"){retraduct($argv[2],$argv[3]);die();}



//lxc-start -n vps-1 -l DEBUG -o $(tty)


help();
die();


function debian_array(){
	
	$unix=new unix();
	print_r($unix->NETWORK_DEBIAN_PARSE_ARRAY());
	
}

function getversion(){
	$unix=new unix();
	$lxc_version=$unix->find_program("lxc-version");
	exec("$lxc_version 2>&1",$results);
	if(preg_match("#([0-9\.]+)#",@implode("",$results),$re)){$GLOBALS["LXCVERSION"]=$re[1];return;}
	if($GLOBALS["VERBOSE"]){echo2("$lxc_version -> ".@implode("",$results)." unable to detect");}
}

function help(){
	$sock=new sockets();
	$LXCINterface=$sock->GET_INFO("LXCInterface");
	$breth="br5";	
	$basename=basename(__FILE__);
	echo "Script that handle LXC (Virtual servers)\nmain configurations\nUsage:\n\n";
	echo "$basename --install-bridge....: setup bridge network for $LXCINterface -> $breth\n";
	echo "$basename --uninstall-bridge..: setup bridge network for $breth -> $LXCINterface\n";
	echo "$basename --isroute gw dev....: verify if route exists on gateway (gw) and nic (dev)\n";
	echo "$basename --vps-servers.......: Check vps maintenance queue\n";
	echo "$basename --vps-server-mod....: Execute scheduled task\n";
	echo "$basename --status............: get all VPS server status in ini format\n";
	echo "$basename --status-single ID..: get single VPS server status in ini format by giving the db Id\n";
	echo "$basename --lxc-restart ID....: restart single VPS by giving the db Id\n";
	echo "$basename --lxc-stop ID.......: stop single VPS server by giving the db Id\n";
	echo "$basename --lxc-start ID......: start single VPS server by giving the db Id\n";
	echo "$basename --lxc-config ID.....: reconfigure VPS server by giving the db Id\n";
	echo "\n\nDebug:\n";
	echo "$basename --debian-array......: output array of /etc/network/interfaces\n";
	echo "Notice: Use --verbose after command line for output debugging\n";
}


function retraduct($from,$to){
	echo2("Starting......: VPS server: upgrading virtual networks (if set)");
	$sql="UPDATE nics_virtuals SET nic='$to' WHERE nic='$from'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	$q=new mysql();
	$sql="UPDATE nics_vlan SET nic='$to' WHERE nic='$from'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo2("Starting......: VPS server: mysql error $q->mysql_error");}	
	
}

function uninstall_bridge(){
	@unlink("/usr/share/artica-postfix/ressources/logs/vserver.daemon.log");
	echo2("Starting......: VPS server: Installing bridge...");
	shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/vserver.daemon.log");
	$sock=new sockets();
	$unix=new unix();
	$users=new usersMenus();
	
	$dhcp=false;
	$ifconfig=$unix->find_program("ifconfig");
	$LXCEnabled=$sock->GET_INFO("LXCEnabled");
	$routebin=$unix->find_program("route");
	$brctl=$unix->find_program("brctl");	
	$ifdown=$unix->find_program("ifdown");	
	if(!is_numeric($LXCEnabled)){$LXCEnabled=0;}
	$LXCINterface=$sock->GET_INFO("LXCInterface");
	$breth="br5";
	
	
	echo2("Starting......: VPS server: check interfaces $breth");
	
	
	
	
		
	echo2("Starting......: VPS server: upgrading virtual networks");
	
	while (list ($ethIndex, $array) = each ($interfaces)){
		if(preg_match("#$breth:[0-9]+#",$ethIndex)){
			$array_eth=array();
			$array_eth["UNSET"]=true;
			echo2("Starting......: VPS server: Deleting $ethIndex");
			shell_exec("$ifconfig $ethIndex down >/dev/null 2>&1");
			$unix->NETWORK_DEBIAN_SAVE($ethIndex,$array_eth,true);
		}
	}

	echo2("Starting......: VPS server: upgrading virtual networks (if set)");
	$sql="UPDATE nics_virtuals SET nic='$LXCINterface' WHERE nic='$breth'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");

	echo2("Starting......: VPS server: upgrading VLANs (if set)");	
	$sql="UPDATE nics_vlan SET nic='$LXCINterface' WHERE nic='$breth'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo2("Starting......: VPS server: mysql error $q->mysql_error");}
	
	
	echo2("Starting......: VPS server: Removing $breth config in memory");
	shell_exec2("$brctl delif $breth $LXCINterface");
	shell_exec2("$ifconfig $breth 0.0.0.0");
	shell_exec2("$ifconfig $breth down");
	shell_exec2("$brctl delbr $brctl");
	$nics=new system_nic();
	$nics->RemoveLXCBridge($LXCINterface,$breth);
	
	echo2("Starting......: VPS server: Unlock Artica Interface");
	$sock->SET_INFO("LXCEthLocked",0);
	$sock->SET_INFO("LXCBridged",0);
	echo2("Starting......: VPS server: Scheduling to restart network");	
	
	$nicsAll=ifconfiglisteth();
	echo2("Starting......: VPS server: ".count($nicsAll)." Listed interface(s)");
	while (list ($ethL, $eth2) = each ($nicsAll)){
		if(preg_match("#$breth#",$ethL)){
			echo2("Starting......: VPS server: shutdown $ethL");
			shell_exec("$ifconfig $ethL down >/dev/null 2>&1");
			continue;
		}
		
		if(preg_match("#vlan#",$ethL)){
			echo2("Starting......: VPS server: shutdown $ethL");
			shell_exec("$ifconfig $ethL down >/dev/null 2>&1");
			continue;
		}	

		echo2("Starting......: VPS server: keep $ethL");
		
	}
	
	$ip=$unix->find_program("ip");
	exec("$ip route 2>&1",$results);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#dev $breth#",$line)){
			echo2("Starting......: VPS server: remove route $line");
			shell_exec("$ip route del $line");
		}
	}
	
	$nets=new system_nic();
	if($users->AS_DEBIAN_FAMILY){
		echo2("Starting......: VPS server: reconfigure network (debian mode)");
		$datas=$nets->root_build_debian_config();
		@file_put_contents("/etc/network/interfaces",$datas);	
		$unix->NETWORK_DEBIAN_RESTART();
		return;	
	}
	echo2("Starting......: VPS server: reconfigure network (redhat mode)");
	$nets->root_build_redhat_config();
	$unix->NETWORK_REDHAT_RESTART();
	
	
	
}

function install_bridge(){
	@unlink("/usr/share/artica-postfix/ressources/logs/vserver.daemon.log");
	echo2("Starting......: VPS server: install bridge");
	
	
	
	shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/vserver.daemon.log");
	$sock=new sockets();
	$users=new usersMenus();
	$unix=new unix();
	$breth="br5";
	
	
	$LXCINterface=$sock->GET_INFO("LXCInterface");
	$routebin=$unix->find_program("route");
	$ifconfig=$unix->find_program("ifconfig");
	
	$brctl=$unix->find_program("brctl");
	if(!is_file($brctl)){echo2("Starting......: VPS server:$breth brctl no such binary");return;}
	$ifdown=$unix->find_program("ifdown");
	$lxcstart=$unix->find_program("lxc-start");
	if(!is_file($lxcstart)){echo2("Starting......: VPS server:$breth lx-start no such binary");return;}
	if($LXCINterface==null){echo2("Starting......: VPS server:$breth no interface set...");return;}
	$nics=new system_nic();
	
	echo2("Starting......: VPS server: $breth interface \"$LXCINterface\" set...");
	if(!$nics->CreateLXCBridge($LXCINterface,$breth)){
		echo2("Starting......: VPS server: $breth CreateLXCBridge() failed");
		return;
	}
		
		
	echo2("Starting......: VPS server: lock Artica Interface");
	$sock->SET_INFO("LXCEthLocked",1);
	$sock->SET_INFO("LXCEnabled",1);
	$sock->SET_INFO("LXCBridged",1);
	$q=new mysql();
	$sql="UPDATE nics_virtuals SET nic='$breth' WHERE nic='$LXCINterface'";
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="UPDATE nics_vlan SET nic='$breth' WHERE nic='$LXCINterface'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo2("Starting......: VPS server: mysql error $q->mysql_error");}
	echo2("Starting......: VPS server: $breth linking bridge");
	preup_checkbridgre($breth,$LXCINterface);
    echo2("Starting......: VPS server: $breth linking bridge done..");
	$nicsAll=ifconfiglisteth();
	echo2("Starting......: VPS server: $breth ".count($nicsAll)." Listed interface(s)");
	while (list ($ethL, $eth2) = each ($nicsAll)){
		if(preg_match("#$LXCINterface#",$ethL)){
			echo2("Starting......: VPS server: shutdown $ethL");
			shell_exec("$ifconfig $ethL down >/dev/null 2>&1");
			continue;
		}
		if(preg_match("#vlan#",$ethL)){
			echo2("Starting......: VPS server: shutdown $ethL");
			shell_exec("$ifconfig $ethL down >/dev/null 2>&1");
			continue;
		}

		
		echo2("Starting......: VPS server: keep $ethL");
		
	}		
	
	
	
	$nets=new system_nic();
	if($users->AS_DEBIAN_FAMILY){
		echo2("Starting......: VPS server: Building network configuration (debian mode)");
		$datas=$nets->root_build_debian_config();
		@file_put_contents("/etc/network/interfaces",$datas);
	}else{
		echo2("Starting......: VPS server: Building network configuration (redhat mode)");
		$nets->root_build_redhat_config();
	}
	
	$ip=$unix->find_program("ip");
	exec("$ip route 2>&1",$results);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#dev $LXCINterface#",$line)){
			echo2("Starting......: VPS server: remove route $line");
			shell_exec("$ip route del $line");
		}
	}
	shell_exec2("$ifconfig $LXCINterface down");
	shell_exec2("$ifconfig $LXCINterface 0.0.0.0 up");
	
	if($users->AS_DEBIAN_FAMILY){
		$unix->NETWORK_DEBIAN_RESTART();
	}else{
		$unix->NETWORK_REDHAT_RESTART();
	}
	
	
	
}

function shell_exec2($cmd){
	if($GLOBALS["VERBOSE"]){echo2("Starting......: VPS server: executing \"$cmd\"");}
	shell_exec($cmd);
	
}

function _IfRouteExists($GATEWAY,$interface){
	$unix=new unix();
	$routebin=$unix->find_program("route");	
	
	$GATEWAY_PATTERN=str_replace(".","\.",$GATEWAY);
	exec("$routebin 2>&1",$results);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#$GATEWAY_PATTERN.+?$interface$#",trim($line))){
			echo2("Starting......: VPS server: Route to $GATEWAY on $interface exists");
			return true;
		}
		
	}
	echo2("Starting......: VPS server: Route to $GATEWAY on $interface does not exists");
	return false;
}

function preup_checkbridgre($breth,$nic){
	$unix=new unix();
	$brctl=$unix->find_program("brctl");
	$ifconfig=$unix->find_program("ifconfig");	
	
	if($GLOBALS["VERBOSE"]){echo "Starting......: VPS server:$brctl,$ifconfig\n";}
	$cmd="$brctl show 2>&1";
	if($GLOBALS["VERBOSE"]){echo "Starting......: $cmd\n";}
	exec("$cmd",$results);
	while (list ($index, $line) = each ($results)){
			if(preg_match("#^$breth\s+#",$line)){
				if($GLOBALS["VERBOSE"]){echo "Starting......: $line\n";}
				shell_exec2("$ifconfig $nic up >/dev/null 2>&1");
				shell_exec2("$brctl addif $breth $nic >/dev/null 2>&1");			
				return;
			}
;
			
	}

	shell_exec2("$brctl addbr $breth  >/dev/null 2>&1");	
	shell_exec2("$ifconfig $nic up >/dev/null 2>&1");
	shell_exec2("$brctl addif $breth $nic >/dev/null 2>&1");		
}


function CheckBridge($breth,$noloop=false,$restart_interfaces=false){
	$found=false;
	$unix=new unix();
	$brctl=$unix->find_program("brctl");
	if(!is_file($brctl)){
		echo2("Starting......: VPS server: brctl no such file");
		return false;
	}
	
	exec("$brctl show 2>&1",$results);
	
		while (list ($index, $line) = each ($results)){
			if(preg_match("#^$breth\s+#",$line)){
				echo2("Starting......: VPS server: $breth OK");
				$found=true;
			}
		}
		
	if($found){return true;}
	shell_exec("$brctl addbr $breth");
	if($restart_interfaces){$unix->NETWORK_DEBIAN_RESTART();CheckLxcMaster();}
	echo2("Starting......: VPS server: Adding $breth");
	if(!$noloop){return CheckBridge($breth,true);}
	
	
}

function CheckLxcMaster(){
	$unix=new unix();
	$mount=$unix->find_program("mount");	
	if(!is_dir("/cgroup")){@mkdir("/cgroup");}
	shell_exec("$mount none -t cgroup /cgroup");
	vps_servers_check();
	
	//net.ipv4.conf.default.rp_filter=1
//net.ipv4.conf.default.forwarding=1

	//http://blog.foaa.de/2010/05/lxc-on-debian-squeeze/#limit-memory-and-swap
	
}

function vps_servers_watchdogs(){
	$sock=new sockets();
	$q=new mysql();
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}

	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($GLOBALS["CLASS_UNIX"]->process_exists($pid)){echo2("Starting......: VPS server: Watchdog, Already instance executed $pid");return;}	
	$pid=getmypid();
	@file_put_contents($pidfile,$pid);		
	
	$LXCEnabled=$sock->GET_INFO("LXCEnabled");
	if(!is_numeric($LXCEnabled)){$LXCEnabled=0;}
	if($LXCEnabled==0){return;}
	$lxcstop=$GLOBALS["CLASS_UNIX"]->find_program("lxc-stop");
	if(!is_file($lxcstop)){return;}

	$sql="SELECT ID FROM lxc_machines WHERE enabled=1 and autostart=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!function_exists("mysql_fetch_array")){
		if($GLOBALS["VERBOSE"]){echo2("Starting......: VPS server: mysql_fetch_array no such function");}
		return;
	}	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["status"]=="installed"){vps_start($ligne["ID"]);}
	}
	
}

function vps_servers_status(){
	$sock=new sockets();
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	$LXCEnabled=$sock->GET_INFO("LXCEnabled");
	if(!is_numeric($LXCEnabled)){$LXCEnabled=0;}
	if($LXCEnabled==0){return;}
	$lxcstop=$GLOBALS["CLASS_UNIX"]->find_program("lxc-stop");
	if(!is_file($lxcstop)){return;}
	
	$stat=array();
	$sql="SELECT * FROM lxc_machines";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!function_exists("mysql_fetch_array")){
		if($GLOBALS["VERBOSE"]){echo2("Starting......: VPS server: mysql_fetch_array no such function");}
		return;
	}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$stat[]=vps_servers_status_single($ligne["ID"],$ligne["enabled"]);
		
		
	}
	
	echo @implode("\n",$stat);
	
}

function vps_servers_status_single($ID,$enabled=null){
	
		if(!is_numeric($ID)){return;}
		if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
		if(!is_numeric($enabled)){
			$q=new mysql();
			$sql="SELECT enabled FROM lxc_machines WHERE ID=$ID";
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			$enabled=$ligne["enabled"];
		}
		
		$vps_name="vps-$ID";
		$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
		$lxc_start=$GLOBALS["CLASS_UNIX"]->find_program("lxc-start");
		
		$cmd="$pgrep -l -f \"lxc-start.+$vps_name\" 2>&1";
		if($GLOBALS["VERBOSE"]){echo2($cmd);}
		exec($cmd,$pgrep_array);
		while (list ($index, $line) = each ($pgrep_array)){
			if(preg_match("#pgrep#",$line)){continue;}
			if(preg_match("#^([0-9]+)#",$line,$re)){$master_pid=$re[1];break;}
			if($GLOBALS["VERBOSE"]){echo2("pgrep $line: NO MATCH");}
		}
		
		$l[]="[APP_LXC:$vps_name]";
		$l[]="service_name=APP_LXC";
	 	$l[]="master_version={$GLOBALS["LXCVERSION"]}";
	 	$l[]="service_cmd=lxc";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=null";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";	
	 	
	 	
		if($enabled==0){
			if($GLOBALS["VERBOSE"]){echo2("Disabled....");}
			$l[]="";echo implode("\n",$l);
			return;
		}
		

		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			if($GLOBALS["VERBOSE"]){echo2("$master_pid not running watchdog ?");}
			$unix=new unix();
			if(!$ligne["autostart"]==1){
				if(!$GLOBALS["NOWATCHDOG"]){
					exec($GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN()." ".__FILE__." --lxc-start $ID 2>&1",$results);
					$GLOBALS["CLASS_UNIX"]->send_email_events("VPS server:{$ligne["machine_name"]} is not running (try to start it)",@implode("\n",$results),"system");
				}
			}
			$l[]="";
			echo implode("\n",$l);
			return;
		}else{
			if($GLOBALS["VERBOSE"]){echo2("Pid: $master_pid RUNNING");}
		}	
		
		if($GLOBALS["VERBOSE"]){echo2("unix->GetMemoriesOf($master_pid)");}
		$l[]=vps_servers_status_single_mem_usage($ID,$master_pid);
		$l[]="";
	
	return implode("\n",$l)."\n";return;		
}

function vps_servers_status_single_mem_usage($ID,$master_pid){
	
	$lxc_ps=$GLOBALS["CLASS_UNIX"]->find_program("lxc-ps");
	$wc=$GLOBALS["CLASS_UNIX"]->find_program("wc");
if($GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){$l[]="installed=1";$l[]="running=1";}
	
	if(!is_file("/cgroup/vps-$ID/memory.usage_in_bytes")){
		if($GLOBALS["VERBOSE"]){echo "/cgroup/vps-$ID/memory.stat no such file";}
		return;
	}
	$f=explode("\n",file_get_contents("/cgroup/vps-$ID/memory.stat"));
	while (list ($index, $line) = each ($f)){
		if(preg_match("#^rss\s+([0-9]+)#",$line,$re)){$bytes=$re[1];}
		if(preg_match("#^cache\s+([0-9]+)#",$line,$re)){$vm=intval($re[1])/1024;}
		
	}
	
	$rss=$bytes/1024;
	
	

	$l[]="application_installed=1";
	$l[]="master_pid=$master_pid";	
    $l[]="master_memory=$rss";
    $l[]="master_cached_memory=$vm";
    exec("$lxc_ps --name vps-$ID aux|$wc -l 2>&1",$results);
    $count=intval(trim(@implode("",$results)));
    $l[]="processes_number=$count";	
    return @implode("\n",$l);
}



function vps_servers_check(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid)){
		echo2("Starting......: VPS server: Already instance executed $pid");
		return;
	}
	
	$lxccreate=$unix->find_program("lxc-create");
	if(!is_file($lxccreate)){echo2("Starting......: VPS server: lxc-create no such file");return;}
	
	$pid=getmypid();
	@file_put_contents($pidfile,$pid);
	
	
	$sock=new sockets();
	$LXCVpsDir=$sock->GET_INFO("LXCVpsDir");
	if($LXCVpsDir==null){$LXCVpsDir="/home/vps-servers";}
	echo2("Starting......: VPS server: directory $LXCVpsDir");
	$sql="SELECT * FROM lxc_machines WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo2("Starting......: VPS server: $q->mysql_error");}
	
	echo2("Starting......: VPS server: ". mysql_num_rows($results). " server(s) to check");
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ID=$ligne["ID"];
		vps_server_check_single($ID);
		
	}

}

function echo2($text){
	echo $text."\n";
	$file="/usr/share/artica-postfix/ressources/logs/vserver.daemon.log";
	@mkdir(dirname($file));
	$logFile=$file;
	if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   	if (is_file($logFile)) { 
   		$size=filesize($logFile);
	  	if($size>100000){unlink($logFile);}
   		}
	$logFile=str_replace("//","/",$logFile);
	$f = @fopen($logFile, 'a');
	@fwrite($f, "$text\n");
	@fclose($f);
	@chmod($file,0777);	
	if(!$GLOBALS["VERBOSE"]){
		writelogs($text,__FUNCTION__,__FILE__,__LINE__);
	}
	
}

function vps_server_check_single($ID){
	if(!is_numeric($ID)){return;}
	$sock=new sockets();
	$unix=new unix();
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid)){echo2("Starting......: VPS server: vps-$ID: Already instance executed $pid");return;}	
	$pid=getmypid();
	@file_put_contents($pidfile,$pid);
	
	
	$root=root_directory($ID);	
	$LXCVpsDir=root_directory();
	$rootfs="$root/rootfs";
	$lxccreate=$unix->find_program("lxc-create");
	if(!is_file($lxccreate)){echo2("Starting......: VPS server: lxc-create no such file");return;}	
	
	
	$q=new mysql();
	$sql="SELECT * FROM lxc_machines WHERE ID=$ID";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$computer=$ligne["machine_name"];
	$enabled=$ligne["enabled"];
	if($ligne["template"]==null){$ligne["template"]="squeeze-i386";}
	echo2("Starting......: VPS server: vps-$ID: state: \"{$ligne["state"]}\"");
	
		
	if($ligne["state"]=="create"){
		$q->QUERY_SQL("UPDATE lxc_machines SET `state`='configure' WHERE ID='$ID'","artica_backup");
		create_vps($ID);
		}
		
	if($ligne["state"]=="update"){
		$q->QUERY_SQL("UPDATE lxc_machines SET `state`='configure' WHERE ID='$ID'","artica_backup");
		buildconfig($ID);
		$sql="UPDATE lxc_machines SET `state`='installed' WHERE ID='$ID'";
		$q->QUERY_SQL($sql,"artica_backup");
	}
	
	if($ligne["state"]=="artica-install"){
		artica_install_vps($ID);
	}	
	
	if($ligne["state"]=="delete"){delete_vps($ID);}	
		
	if(preg_match("#dup:([0-9]+)#",$ligne["state"],$re)){
		$q->QUERY_SQL("UPDATE lxc_machines SET `state`='configure' WHERE ID='$ID'","artica_backup");
		echo2("Starting......: VPS server: vps-$ID: duplicating {$re[1]} -> $ID");
		duplicate_vps($re[1],$ligne["ID"]);
		
	}
	echo2("Starting......: VPS server: vps-$ID: {$ligne["state"]} Done...");
			
}

function artica_install_vps($ID){
		$q=new mysql();
		$unix=new unix();

		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
		$pid=@file_get_contents($pidfile);
		$unix=new unix();
		if($unix->process_exists($pid)){echo2("Starting......: VPS server: vps-$ID: Already instance executed $pid");return;}	
		$pid=getmypid();
		@file_put_contents($pidfile,$pid);		
		$chroot=$unix->find_program("chroot");
		$sql="SELECT * FROM lxc_machines WHERE ID=$ID";
		$LXCVpsDir=root_directory();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$workingdirectory=root_directory($ID);
		$rootfs="$workingdirectory/rootfs";	
		@mkdir("$rootfs/usr/share/artica-postfix",true,755);
		shell_exec("/bin/cp -rfv /usr/share/artica-postfix/* $rootfs/usr/share/artica-postfix/");
		@unlink("$rootfs/usr/share/artica-postfix/ressources/settings.inc");
		shell_exec("/bin/rm -rf $rootfs/usr/share/artica-postfix/ressources/logs/*");
		
		exec("$chroot $rootfs /usr/share/artica-postfix/bin/artica-install --init-from-repos 2>&1",$results);
		exec("$chroot $rootfs /usr/share/artica-postfix/bin/artica-install --perl-addons-repos 2>&1",$results);
		exec("$chroot $rootfs /usr/share/artica-postfix/bin/artica-install -awstats-reconfigure 2>&1",$results);
		
		$q->QUERY_SQL("UPDATE lxc_machines SET `state`='installed' WHERE ID='$ID'","artica_backup");
		$q->QUERY_SQL($sql);				
		
}

function create_vps($ID){
		$q=new mysql();
		$unix=new unix();
		
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
		$pid=@file_get_contents($pidfile);
		$unix=new unix();
		if($unix->process_exists($pid)){echo2("Starting......: VPS server: vps-$ID: Already instance executed $pid");return;}	
		$pid=getmypid();
		@file_put_contents($pidfile,$pid);		
		
		$sql="SELECT * FROM lxc_machines WHERE ID=$ID";
		$LXCVpsDir=root_directory();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$workingdirectory=root_directory($ID);
		$rootfs="$workingdirectory/rootfs";
		@mkdir($rootfs,true,666);
		$template=$ligne["template"];
		$lxccreate=$unix->find_program("lxc-create");
		$tar=$unix->find_program("tar");
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='10%' WHERE ID='$ID'","artica_backup");
		if(!is_file($lxccreate)){
			echo2("Starting......: VPS server: lxc-create no such binary");
			$q->QUERY_SQL("UPDATE lxc_machines SET `state`='failed' WHERE ID='$ID'","artica_backup");
			$q->QUERY_SQL("UPDATE lxc_machines SET `events`='lxc-create no such binary' WHERE ID='$ID'","artica_backup");
			return;
		}	

		if(!is_file($tar)){
			echo2("Starting......: VPS server: tar no such binary");
			$q->QUERY_SQL("UPDATE lxc_machines SET `state`='failed' WHERE ID='$ID'","artica_backup");
			$q->QUERY_SQL("UPDATE lxc_machines SET `events`='tar no such binary' WHERE ID='$ID'","artica_backup");
			return;
		}			
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='50%' WHERE ID='$ID'","artica_backup");
		echo2("Starting......: VPS server: vps-$ID: Installing template $template");
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='Installing template $template' WHERE ID='$ID'","artica_backup");
		if(!is_file("$LXCVpsDir/templates/$template")){
			echo2("Starting......: VPS server: vps-$ID: $LXCVpsDir/templates/$template no such file");
			$q->QUERY_SQL("UPDATE lxc_machines SET `events`='$LXCVpsDir/templates/$template no such file' WHERE ID='$ID'","artica_backup");
			$q->QUERY_SQL("UPDATE lxc_machines SET `state`='failed' WHERE ID='$ID'","artica_backup");
			return;
		}
	
		
		@mkdir($rootfs,true,644);
		echo2("Starting......: VPS server: vps-$ID: extracting \"$LXCVpsDir/templates/$template to $rootfs\"");
		shell_exec("$tar -xf $LXCVpsDir/templates/$template -C $rootfs/");
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='80%' WHERE ID='$ID'","artica_backup");
		echo2("Starting......: VPS server: vps-$ID: extracting success..");
		
		$lxccreate_array=array();
		buildconfig($ID);
		
		if(!is_file("$workingdirectory/config")){
			echo2("Starting......: VPS server: vps-$ID: $workingdirectory/config no such file");
			$q->QUERY_SQL("UPDATE lxc_machines SET `events`='$workingdirectory/config no such file' WHERE ID='$ID'","artica_backup");
			$q->QUERY_SQL("UPDATE lxc_machines SET `state`='failed' WHERE ID='$ID'","artica_backup");
			return;
		}
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='90%' WHERE ID='$ID'","artica_backup");
		exec("$lxccreate -n vps-$ID -f $workingdirectory/config 2>&1",$lxccreate_array);
		if(preg_match("#created#",@implode(" ",$lxccreate_array))){
			$q->QUERY_SQL("UPDATE lxc_machines SET `state`='installed' WHERE ID='$ID'","artica_backup","artica_backup");
			return;
		}
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='100%' WHERE ID='$ID'","artica_backup");
		
		while (list ($num, $ligne) = each ($lxccreate_array) ){echo2("Starting......: VPS server: vps-$ID:$ligne");}
		$q->QUERY_SQL("UPDATE lxc_machines SET `state`='installed' WHERE ID='$ID'","artica_backup");
		$q->QUERY_SQL("UPDATE lxc_machines SET `events`='100%' WHERE ID='$ID'","artica_backup");
	
}

function delete_vps($ID){
	$workingdirectory=root_directory($ID);
	$unix=new unix();
	$lxc_destroy=$unix->find_program("lxc-destroy");
	echo2("Starting......: VPS server: vps-$ID: unlink");
	shell_exec("$lxc_destroy -n vps-$ID");
	if(is_dir($workingdirectory)){shell_exec("/bin/rm -rf $workingdirectory");}
	$q=new mysql();
	$q->QUERY_SQL("DELETE FROM lxc_machines WHERE ID='$ID'","artica_backup");
	
}


function buildconfig($ID){
	$sock=new sockets();
	$LXCBridged=$sock->GET_INFO("LXCBridged");
	if(!is_numeric($LXCBridged)){$LXCBridged=0;}
	$q=new mysql();
	$sql="SELECT * FROM lxc_machines WHERE ID=$ID";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$workingdirectory=root_directory($ID);
	$rootfs="$workingdirectory/rootfs";
	if(!is_dir($workingdirectory)){@mkdir($workingdirectory,true,666);}
	if(!is_dir($rootfs)){@mkdir($rootfs,true,666);}	
	$unix=new unix();
	$ChangeMac=$ligne["ChangeMac"];
	$MacAddr=strtolower($ligne["MacAddr"]);
	
	$f=array();
	$f[]="lxc.utsname =vps-{$ligne["ID"]}"; 
	$f[]="lxc.tty = 4";
	$f[]="lxc.pts = 1024";
	if($ligne["UsePhys"]==1){
		$f[]="lxc.network.type = phys";
		$f[]="lxc.network.link  = {$ligne["PhysNic"]}";
		$f[]="lxc.network.mtu = 1500";
	}else{
		$f[]="lxc.network.type = veth";
		$f[]="lxc.network.link = br5";
	}
	$f[]="lxc.network.ipv4 = {$ligne["ipaddr"]}";
	if($ChangeMac==1){$f[]="lxc.network.hwaddr = $MacAddr";	}	
	$f[]="lxc.network.name = eth0";
	$f[]="lxc.network.flags = up";
	
	$CGROUPS=unserialize($ligne["cgroup"]);
	
	if(!is_numeric($CGROUPS["lxc.cgroup.memory.limit_in_bytes"])){$CGROUPS["lxc.cgroup.memory.limit_in_bytes"]="128";}
	if(!is_numeric($CGROUPS["lxc.cgroup.memory.memsw.limit_in_bytes"])){$CGROUPS["lxc.cgroup.memory.memsw.limit_in_bytes"]="512";}	
	if(!is_numeric($CGROUPS["lxc.cgroup.cpu.shares"])){$CGROUPS["lxc.cgroup.cpu.shares"]="1024";}
	if(!is_array($CGROUPS["lxc.cgroup.cpuset.cpus"])){$CGROUPS["lxc.cgroup.cpuset.cpus"][0]=1;}	
	while (list ($num, $line) = each ($CGROUPS["lxc.cgroup.cpuset.cpus"])){$cpus[]=$num;}
	
	$f[]="lxc.cgroup.memory.limit_in_bytes = {$CGROUPS["lxc.cgroup.memory.limit_in_bytes"]}M";
	$f[]="lxc.cgroup.memory.memsw.limit_in_bytes = {$CGROUPS["lxc.cgroup.memory.memsw.limit_in_bytes"]}M";
	$f[]="lxc.cgroup.cpu.shares = {$CGROUPS["lxc.cgroup.cpu.shares"]}";
	$f[]="lxc.cgroup.cpuset.cpus = ".@implode(",",$cpus);
	$f[]="lxc.rootfs = $rootfs";
	$f[]="lxc.cgroup.devices.deny = a";
	$f[]="lxc.cgroup.devices.allow = c 1:3 rwm";
	$f[]="lxc.cgroup.devices.allow = c 1:5 rwm";
	$f[]="lxc.cgroup.devices.allow = c 5:1 rwm";
	$f[]="lxc.cgroup.devices.allow = c 5:0 rwm";
	$f[]="lxc.cgroup.devices.allow = c 4:0 rwm";
	$f[]="lxc.cgroup.devices.allow = c 4:1 rwm";
	$f[]="lxc.cgroup.devices.allow = c 1:9 rwm";
	$f[]="lxc.cgroup.devices.allow = c 1:8 rwm";
	$f[]="lxc.cgroup.devices.allow = c 136:* rwm";
	$f[]="lxc.cgroup.devices.allow = c 5:2 rwm";
	$f[]="lxc.cgroup.devices.allow = c 254:0 rwm";
	$f[]="lxc.mount.entry=proc $rootfs/proc proc nodev,noexec,nosuid 0 0";
	$f[]="lxc.mount.entry=devpts $rootfs/dev/pts devpts defaults 0 0";
	$f[]="lxc.mount.entry=sysfs $rootfs/sys sysfs defaults  0 0";		
	$f[]="";	
	@file_put_contents("$workingdirectory/config",@implode("\n",$f));
	if(!is_dir("/var/lib/lxc/vps-$ID")){@mkdir("/var/lib/lxc/vps-$ID",true,644);}
	if(!is_dir("/var/lib/lxc/vps-$ID")){@mkdir("/var/lib/lxc/vps-$ID");}
	@file_put_contents("/var/lib/lxc/vps-$ID/config",@implode("\n",$f));	
	
	
/*shell_exec("/bin/rm -rf $rootfs/dev");
shell_exec("mkdir $rootfs/dev");
shell_exec("mknod -m 666 $rootfs/dev/null c 1 3");
shell_exec("mknod -m 666 $rootfs/dev/zero c 1 5");
shell_exec("mknod -m 666 $rootfs/dev/random c 1 8");
shell_exec("mknod -m 666 $rootfs/dev/urandom c 1 9");
shell_exec("mkdir -m 755 $rootfs/dev/pts");
shell_exec("mkdir -m 1777 $rootfs/dev/shm");
shell_exec("mknod -m 666 $rootfs/dev/tty c 5 0");
shell_exec("mknod -m 666 $rootfs/dev/tty0 c 4 0");
shell_exec("mknod -m 666 $rootfs/dev/tty1 c 4 1");
shell_exec("mknod -m 666 $rootfs/dev/tty2 c 4 2");
shell_exec("mknod -m 666 $rootfs/dev/tty3 c 4 3");
shell_exec("mknod -m 666 $rootfs/dev/tty4 c 4 4");
shell_exec("mknod -m 600 $rootfs/dev/console c 5 1");
shell_exec("mknod -m 666 $rootfs/dev/full c 1 7");
shell_exec("mknod -m 600 $rootfs/dev/initctl p");
shell_exec("mknod -m 666 $rootfs/dev/ptmx c 5"); 	
*/	
	
	
	
	echo2("Starting......: VPS server: vps-$ID: changing hostname {$ligne["hostname"]}");
	@file_put_contents("$rootfs/etc/hostname",$ligne["hostname"]);
	
	$rootpwd=$ligne["rootpwd"];
	if(trim($rootpwd)==null){$rootpwd="root";}
	$rootpwd=$unix->shellEscapeChars($rootpwd);
	$chroot=$unix->find_program("chroot");
	echo2("Starting......: VPS server: vps-$ID: changing root password");
	$results=array();
	exec("echo \"root:$rootpwd\" | $chroot $rootfs chpasswd 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){echo2("Starting......: VPS server: vps-$ID: $ligne");}
	
	
	@mkdir("$rootfs/etc/artica-postfix",true,644);
	@file_put_contents("$rootfs/etc/artica-postfix/AS_VPS_CLIENT","~");
	$sftp_server=LOCATE_SFTP_SERVER($rootfs);
	$ssh[]="Port 22";
	$ssh[]="Protocol 2";
	$ssh[]="HostKey /etc/ssh/ssh_host_rsa_key";
	$ssh[]="HostKey /etc/ssh/ssh_host_dsa_key";
	$ssh[]="UsePrivilegeSeparation yes";
	$ssh[]="KeyRegenerationInterval 3600";
	$ssh[]="ServerKeyBits 768";
	$ssh[]="SyslogFacility AUTH";
	$ssh[]="LogLevel INFO";
	$ssh[]="LoginGraceTime 120";
	$ssh[]="PermitRootLogin yes";
	$ssh[]="StrictModes yes";
	$ssh[]="RSAAuthentication yes";
	$ssh[]="PubkeyAuthentication yes";
	$ssh[]="IgnoreRhosts yes";
	$ssh[]="RhostsRSAAuthentication no";
	$ssh[]="HostbasedAuthentication no";
	$ssh[]="PermitEmptyPasswords yes";
	$ssh[]="ChallengeResponseAuthentication no";
	if(strlen($sftp_server)>5){	$ssh[]="Subsystem   sftp  $sftp_server";} 
	writelogs("Writing $rootfs/etc/ssh/sshd_config",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("$rootfs/etc/ssh/sshd_config",@implode("\n",$ssh));
	echo2("Starting......: VPS server: vps-$ID: changing sshd_config done");	

	
echo2("Starting......: VPS server: vps-$ID: configuring done...");	
}

function LOCATE_SFTP_SERVER($rootfs){
	if(is_file("$rootfs/usr/libexec/openssh/sftp-server")){return "/usr/libexec/openssh/sftp-server";}
	if(is_file("$rootfs/usr/lib/sftp-server")){return "/usr/lib/sftp-server";}
	if(is_file("$rootfs/usr/lib64/sftp-server")){return "/usr/lib64/sftp-server";}
	
}

//minimal kernel 2.6.26

function root_directory($ID=null){
	
	if(!isset($GLOBALS["LXCVpsDir"])){
		$sock=new sockets();
		$LXCVpsDir=$sock->GET_INFO("LXCVpsDir");
		if($LXCVpsDir==null){$LXCVpsDir="/home/vps-servers";}	
		$GLOBALS["LXCVpsDir"]=$LXCVpsDir;
		}	

		if(!is_numeric($ID)){return $GLOBALS["LXCVpsDir"];}	
	
	
		$q=new mysql();
		$sql="SELECT root_directory FROM lxc_machines WHERE ID=$ID";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["root_directory"]<>null){return $ligne["root_directory"];}	
		return $GLOBALS["LXCVpsDir"]."/vps-$ID";
	}


function lxc_ps($ID){
	$unix=new unix();
	$lxcps=$unix->find_program("lxc-ps");
	system("$lxcps -n vps-$ID --lxc aux 2>&1");
	
}

function vps_running($ID){
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	$lxcps=$GLOBALS["CLASS_UNIX"]->find_program("lxc-info");
	exec("$lxcps -n vps-$ID 2>&1",$results);
	if(preg_match("#RUNNING#",@implode("",$results))){return true;}
	return false;
	
}

function vps_stop($ID){
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}		
	$lxcstop=$GLOBALS["CLASS_UNIX"]->find_program("lxc-stop");
	shell_exec("$lxcstop -n vps-$ID");
	for($i=0;$i<10;$i++){if(!vps_running($ID)){return;}sleep(1);}	
}

function vps_start($ID){
	
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
		$pid=@file_get_contents($pidfile);
		$unix=new unix();
		if($unix->process_exists($pid)){echo2("Starting......: VPS server: vps-$ID: Already instance executed $pid");return;}	
		$pid=getmypid();
		@file_put_contents($pidfile,$pid);		
	
	
	if(vps_running($ID)){echo2("Starting......: VPS server: vps-$ID: Already running");return;}
	CheckLxcMaster();
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}	
	$lxcstart=$GLOBALS["CLASS_UNIX"]->find_program("lxc-start");
	$workingdirectory=root_directory($ID);
	echo2("Starting......: VPS server: vps-$ID:....");
	buildconfig($ID);
	if(is_file("$workingdirectory/start.log")){@unlink("$workingdirectory/start.log");}
	shell_exec("$lxcstart -n vps-$ID -d -o $workingdirectory/start.log");
	for($i=0;$i<10;$i++){if(vps_running($ID)){break;}sleep(1);}
	if(vps_running($ID)){echo2("Starting......: VPS server: vps-$ID: success");return;}
	echo2("Starting......: VPS server: vps-$ID: failed");
	
}
function vps_freeze($ID){
	if(!vps_running($ID)){echo2("Starting......: VPS server: vps-$ID: Not running");return;}
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}	
	$lxcfreeze=$GLOBALS["CLASS_UNIX"]->find_program("lxc-freeze");
	$workingdirectory=root_directory($ID);
	echo2("Starting......: VPS server: vps-$ID: freeze....");
	shell_exec("$lxcfreeze -n vps-$ID");
	
}
function vps_unfreeze($ID){
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}	
	$lxcfreeze=$GLOBALS["CLASS_UNIX"]->find_program("lxc-unfreeze");
	echo2("Starting......: VPS server: vps-$ID: unfreeze....");
	shell_exec("$lxcfreeze -n vps-$ID");	
}

function vps_restart($ID){
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}	
	if(!vps_running($ID)){vps_start($ID);return;}
	$workingdirectory=root_directory($ID);
	echo2("Starting......: VPS server: vps-$ID: restarting");
	$lxcrestart=$GLOBALS["CLASS_UNIX"]->find_program("lxc-restart");
	exec("$lxcrestart -n vps-$ID -d $workingdirectory  -o $workingdirectory/start.log 2>&1",$results);
	while (list ($index, $line) = each ($results)){echo2("Starting......: VPS server: $line");}

}

function duplicate_vps($from_id,$to_id){
	$q=new mysql();
	$unix=new unix();
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$from_id.$to_id.pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid)){echo2("Starting......: VPS server: vps-$to_id: Already instance executed $pid");return;}	
	$pid=getmypid();
	@file_put_contents($pidfile,$pid);		
	
	$q->QUERY_SQL("UPDATE lxc_machines SET `state`='configure' WHERE ID='$to_id'","artica_backup");
	$source_dir=root_directory($from_id);
	$destdir=root_directory($to_id);
	@mkdir($destdir);
	echo2("Starting......: VPS server: vps-$to_id: copying $source_dir to $source_dir");
	shell_exec("/bin/cp -rf $source_dir/* $destdir/");
	buildconfig($to_id);
	$q->QUERY_SQL("UPDATE lxc_machines SET `state`='installed' WHERE ID='$to_id'","artica_backup");
	vps_start($to_id);
}


function ifconfiglisteth(){
	$unix=new unix();
	$ifconfig=$unix->find_program("ifconfig");
	if($GLOBALS["VERBOSE"]){echo "$ifconfig -a 2>&1\n";}
	exec("$ifconfig -a 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if($GLOBALS["VERBOSE"]){echo "$ligne\n";}
		if(preg_match("#^(.+?)\s+Link encap#",$ligne,$re)){
			if($GLOBALS["VERBOSE"]){echo "Found {$re[1]}\n";}
			$eths[$re[1]]=$re[1];
		}
	}
	
	return $eths;
	
}
 



//https://lists.linux-foundation.org/pipermail/containers/2010-October/025609.html