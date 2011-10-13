<?php
	$GLOBALS["WATCHDOG"]=false;
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	if(preg_match("#--watchdog#",implode(" ",$argv))){$GLOBALS["WATCHDOG"]=true;}
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__) . '/ressources/class.spamassassin.inc');
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	include_once(dirname(__FILE__).  '/framework/class.unix.inc');
	include_once(dirname(__FILE__).  '/framework/frame.class.inc');
	include_once(dirname(__FILE__).  '/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).  '/ressources/class.system.network.inc');
	include_once(dirname(__FILE__).  '/ressources/class.computers.inc');	

	$unix=new unix();
	$GLOBALS["CLASS_UNIX"]=$unix;
	if($argv[1]=="--build"){build();die();}
	if($argv[1]=="--start"){start();die();}
	if($argv[1]=="--stop"){stop();die();}
	if($argv[1]=="--status"){status();die();}
	if($argv[1]=="--reload"){reload();die();}
	if($argv[1]=="--status-instance"){echo status_instance($argv[2]);die();}
	if($argv[1]=="--start-instance"){echo start_instance($argv[2]);die();}
	if($argv[1]=="--stop-instance"){echo stop_instance($argv[2]);die();}
	if($argv[1]=="--restart-instance"){echo restart_instance($argv[2]);die();}
	if($argv[1]=="--build-instance"){echo instance_build($argv[2]);die();}
	


function reload(){
	stop();
	build();
	start();
}	
	
function build_backends($crossroads_id){
	$sql="SELECT * FROM crossroads_backend WHERE enabled=1 and `crossroads_id`='$crossroads_id'";
	if($GLOBALS["VERBOSE"]){echo "Starting......: Crossroads instance {$crossroads_id} [DEBUG] -> $sql\n";}
	$q=new mysql();
	$cd=array();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "Starting......: Crossroads $q->mysql_error\n";return array();}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$comp=new computers($ligne["uid"]);
		$cd[]="--backend $comp->ComputerIP:{$ligne["listen_port"]}:{$ligne["max_connections"]}:{$ligne["backend_weight"]}";
		
	}
	
	return $cd;
	
}
	
function build(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid,(basename(__FILE__)))){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());	
	
	$unix=new unix();
	$xrbin=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	$sql="SELECT ID FROM crossroads_main";
	$q=new mysql();
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	echo "Starting......: Crossroads ". mysql_num_rows($results)." instance(s)\n";
	if(!$q->ok){echo "Starting......: Crossroads $q->mysql_error\n";return;}
	if(!is_dir("/var/run/crossroads")){@mkdir("/var/run/crossroads",755,true);}
	if(!is_dir("/var/log/crossroads")){@mkdir("/var/log/crossroads",755,true);}
	if(!is_dir("/etc/artica-postfix/crossroads")){@mkdir("/etc/artica-postfix/crossroads",755,true);}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){instance_build($ligne["ID"]);}
}

function CleanInstances(){
	$sql="SELECT ID FROM crossroads_main WHERE enabled=1";
	$q=new mysql();
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["ID"]]=true;
		
	}
	
	foreach (glob("/etc/artica-postfix/crossroads/*.cmd") as $filename) {
		$base=basename($filename);
		if(!preg_match("#([0-9]+)\.#", $base,$re)){continue;}
		$ID=$re[1];
		if(!isset($array[$ID])){@unlink($filename);}
	}
	
}

function restart_instance($crossroads_id){
	stop_instance($crossroads_id);
	instance_build($crossroads_id);
	start_instance($crossroads_id);
	
}

function instance_build($crossroads_id){
	$q=new mysql();
	$xrbin=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	if(is_file("/etc/artica-postfix/crossroads/$crossroads_id.cmd")){@unlink("/etc/artica-postfix/crossroads/$crossroads_id.cmd");}
	$sql="SELECT * FROM crossroads_main WHERE ID=$crossroads_id";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["enabled"]==0){echo "Starting......: Crossroads instance $crossroads_id is disabled\n";stop_instance($crossroads_id);remove_initd($crossroads_id);return;}
	$cd=array();
	
	if($GLOBALS["VERBOSE"]){echo "Starting......: Crossroads instance $crossroads_id [DEBUG] -> build_backends({$crossroads_id})\n";}
	
	$backends=build_backends($ligne["ID"]);
	if(count($backends)==0){
		crossroads_events("This instance have no backend",$crossroads_id,__FUNCTION__,__LINE__);
		echo "Starting......: Crossroads instance $crossroads_id no backend\n";
		remove_initd($crossroads_id);
		return;
	}
	$proto="tcp";
	$loadbalancetype=$ligne["loadbalancetype"];
	if($loadbalancetype==1){
		$proto="http";
		$cd[]="--sticky-http --add-x-forwarded-for";
	}		
		
	$cd[]="--server $proto:{$ligne["listen_ip"]}:{$ligne["listen_port"]}";	
	$cd[]="--pidfile /var/run/crossroads/cross_{$ligne["ID"]}.pid";
	$cd[]="--backend-timeout {$ligne["backend_timout_read"]}:{$ligne["backend_timout_write"]}";
	$cd[]="--checkup-interval {$ligne["checkup_interval"]}";
	$cd[]="--client-timeout {$ligne["client_timout"]}:{$ligne["client_timout_write"]}";
	$cd[]="--dispatch-mode {$ligne["dispatch_mode"]}";	
	$cd[]=@implode(" ", $backends);
	$cd[]="--web-interface {$ligne["listen_ip"]}:{$ligne["www_port"]}:\"{$ligne["name"]}\"";
	if($ligne["www_username"]<>null){
		$ligne["www_password"]=$unix->shellEscapeChars($ligne["www_password"]);
		$cd[]="--web-interface-auth {$ligne["www_username"]}:{$ligne["www_password"]}";
	}
	$cmdline=$xrbin." ". @implode(" ", $cd)." >/var/log/crossroads/cross_{$ligne["ID"]}.log 2>&1 &";
	crossroads_events("Instance successfully reconfigured",$crossroads_id,__FUNCTION__,__LINE__);
	if($GLOBALS["VERBOSE"]){echo "Starting......: Crossroads instance {$ligne["ID"]} cmdline=$cmdline\n";} 
	@file_put_contents("/etc/artica-postfix/crossroads/{$ligne["ID"]}.cmd", $cmdline);
	build_init_d($ligne["ID"]);
	
}

function start(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid,(basename(__FILE__)))){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());	
	
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	foreach (glob("/etc/artica-postfix/crossroads/*.cmd") as $filename) {
		$basename=basename($filename);
		if(!preg_match("#^([0-9]+)\.#", $basename,$re)){echo "Starting......: Crossroads $basename ?? aborting\n";continue;}
		$ID=$re[1];
		echo "Starting......: Crossroads ID:$ID\n";
		start_instance($ID);
	}
	echo "Starting......: Crossroads finish\n";
}
function stop(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid,(basename(__FILE__)))){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());	
	
	$sql="SELECT * FROM crossroads_main";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		echo "Stopping Crossroads Daemon...: instance id {$ligne["ID"]}\n";
		stop_instance($ligne["ID"]);
		
	}
	$results=array();
	$pgrep=$unix->find_program("pgrep");
	$kill=$unix->find_program("kill");
	echo "Stopping Crossroads Daemon...: search ghost processes\n";
	$cmdline="$pgrep -f \"\/xr.+?pidfile.+?crossroads\/cross_[0-9]+\.pid\" 2>&1";
	if($GLOBALS["VERBOSE"]){echo $cmdline."\n";}
	exec($cmdline,$results);
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#([0-9]+)#", $line,$re)){
			echo "Stopping Crossroads Daemon...: Ghost instance PID {$re[1]}\n";
			shell_exec("$kill -9 {$re[1]}");
		}
		
	}
	
	CleanInstances();
	
	
}
function status(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid,(basename(__FILE__)))){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());


	$sql="SELECT * FROM crossroads_main WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){$f[]=status_instance($ligne["ID"]);}	
	echo @implode("\n", $f);
	
}


function stop_instance($ID){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid,(basename(__FILE__)))){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());	
	
	$unix=new unix();
	$q=new mysql();

	
	$kill=$unix->find_program("kill");
	$pid=cross_pid($ID);
	if(!$unix->process_exists($pid)){
		echo "Stopping Crossroads Daemon...: instance ID:$ID already stopped\n";
		return;
	}
	echo "Stopping Crossroads Daemon...: instance ID:$ID PID $pid\n";
	if(is_numeric($pid)){shell_exec("$kill $pid");}
	for($i=0;$i<5;$i++){
		sleep(1);
		if(!$unix->process_exists($pid)){
			echo "Stopping Crossroads Daemon...: instance ID:$ID stopped\n";
			crossroads_events("Instance ID:$ID success to stop",$ID,__FUNCTION__,__LINE__);
			return;
		}
		
		$pid=cross_pid($ID);
		if(is_numeric($pid)){shell_exec("$kill $pid");}
	}
	$pid=cross_pid($ID);
	if($unix->process_exists($pid)){
		echo "Stopping Crossroads Daemon...: instance ID:$ID Force to kill it !\n";
		if(is_numeric($pid)){shell_exec("$kill -9 $pid");}
	}
	$pid=cross_pid($ID);
	if($unix->process_exists($pid)){
		echo "Stopping Crossroads Daemon...: instance ID:$ID failed\n";
		crossroads_events("Unable to stop instance",$ID,__FUNCTION__,__LINE__);
	}else{
		echo "Stopping Crossroads Daemon...: instance ID:$ID success\n";
	}	
	
}


function status_instance($ID){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($pid,(basename(__FILE__)))){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());	
	
	$unix=new unix();
	$q=new mysql();
	$sql="SELECT * FROM crossroads_main WHERE ID=$ID";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$xr=$unix->find_program("xr");
	if(!is_file($xr)){return;}
	if(!isset($GLOBALS["XR_VERSION"])){
		exec("$xr -V 2>&1",$results);
		while (list ($index, $line) = each ($results)){if(preg_match("#XR version\s+:.*?([0-9\.]+)#", $line,$re)){$GLOBALS["XR_VERSION"]=$re[1];break;}}
	}	
	
	$pid=cross_pid($ID);
	$l[]="[APP_CROSSROADS]";
	$l[]="service_name=APP_CROSSROADS";
	$l[]="master_version=".$GLOBALS["XR_VERSION"];
	$l[]="service_cmd=crossroads-src";
	$l[]="service_disabled={$ligne["enabled"]}";
	$l[]="pid_path=$pid_path";
	$l[]="pid=$pid";
	$l[]="watchdog_features=1";
	$l[]="family=network";	
	
	if($ligne["enabled"]==0){
		return @implode("\n", $l);
	}
	
	if(!$GLOBALS["CLASS_UNIX"]->process_exists($pid)){
		if($GLOBALS["WATCHDOG"]){
			crossroads_events("Instance was stopped from the watchdog -> start it",$ID,__FUNCTION__,__LINE__);	
			start_instance($ID);	
		}
	return @implode("\n", $l);
	}
	$l[]=$GLOBALS["CLASS_UNIX"]->GetMemoriesOf($pid);
	return @implode("\n", $l);
}


function start_instance($ID){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$ID.pid";
	$pid=@file_get_contents($pidfile);
	$unix=new unix();
	$xr=$unix->find_program("xr");
	if($unix->process_exists($pid,$xr)){echo "Starting......: load-balancer engine Already executed PID $pid...\n";return;}	
	@file_put_contents($pidfile, getmypid());	
	
	$unix=new unix();
	
	$pid=cross_pid($ID);

	$filename="/etc/artica-postfix/crossroads/$ID.cmd";
	if(!is_file($filename)){instance_build($ID);}
	if(!is_file($filename)){
		crossroads_events("Cannot start instance, it is not correctly set",$ID,__FUNCTION__,__LINE__);
		echo "Starting......: Crossroads ID:$ID Not correctly set...\n";
	
	return;}
	
	if($unix->process_exists($pid)){echo "Starting......: Crossroads ID:$ID already running pid $pid\n";return;}
	
	echo "Starting......: Crossroads ID:$ID executing daemon (1x)\n";
	
	$cmdline=@file_get_contents($filename);
	$cmdline=trim("$nohup $cmdline");
	shell_exec($cmdline);
	for($i=0;$i<6;$i++){
		sleep(2);
		$pid=cross_pid($ID);
		if($unix->process_exists($pid,$xr)){
			crossroads_events("Instance ID:$ID successfully started running pid $pid",$ID,__FUNCTION__,__LINE__);
			echo "Starting......: Crossroads ID:$ID success running pid $pid\n";
			return;
		}
		
	}
	$pid=cross_pid($ID);
	if(!$unix->process_exists($pid,$xr)){
		echo "Starting......: Crossroads ID:$ID executing daemon (2x)\n"; 	
		shell_exec($cmdline);
		for($i=0;$i<6;$i++){
			sleep(2);
			$pid=cross_pid($ID);
			if($unix->process_exists($pid,$xr)){
				crossroads_events("Instance ID:$ID successfully started running pid $pid",$ID,__FUNCTION__,__LINE__);
				echo "Starting......: Crossroads ID:$ID success running pid $pid\n";
				return;
			}
			
		}
	}	
	
	
	
	$pid=cross_pid($ID);
	if(!$unix->process_exists($pid)){
		$php5=$unix->LOCATE_PHP5_BIN();
		crossroads_events("Instance ID:$ID failed to start\n".@implode("\n", @file_get_contents("/var/log/crossroads/cross_$ID.log")),$ID,__FUNCTION__,__LINE__);
		crossroads_events("Schedule to start instance in few times",$ID,__FUNCTION__,__LINE__);
		$unix->THREAD_COMMAND_SET("$php5 ".__FILE__." --start-instance $ID");
		echo "Starting......: Crossroads ID:$ID failed\n";
		echo "Starting......: Crossroads ID:$ID with command line $cmdline\n";
		
	}	
	
	
}

function cross_pid($ID){
	$pidfile="/var/run/crossroads/cross_$ID.pid";
	return trim(@file_get_contents($pidfile));
	
}

function crossroads_events($text,$instance_id,$function,$line){
	if(!is_dir("/var/log/artica-postfix/crossroads")){@mkdir("/var/log/artica-postfix/crossroads",755,true);}
	
	$array["TIME"]=date("Y-m-d H:i:s");
	$array["ID"]=$instance_id;
	$array["FUNCTION"]=$function;
	$array["TEXT"]=$text;
	$array["LINE"]=$line;
	$serialize=serialize($array);
	@file_put_contents("/var/log/artica-postfix/crossroads/".md5($serialize).".sql", $serialize);
	
	
}

function build_init_d($ID){
	if(!is_numeric($ID)){return;}
	if(is_file("/etc/init.d/xr-$ID")){return;}
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$chmod=$unix->find_program("chmod");
	echo "Starting......: Crossroads ID:$ID adding init.d/xr-$ID\n";
	$f[]="#!/bin/bash";
	$f[]="#";
	$f[]="### BEGIN INIT INFO";
	$f[]="# Provides:          xr-$ID";
	$f[]="# Required-Start:    \$network \$syslog";
	$f[]="# Required-Stop:     \$network \$syslog";
	$f[]="# Should-Start:      \$network \$time";
	$f[]="# Should-Stop:       \$network \$time";
	$f[]="# Default-Start:     2 3 4 5";
	$f[]="# Default-Stop:      0 1 6";
	$f[]="# Short-Description: Start and stop load balancing ID $ID";
	$f[]="# Description:       Controls the main balancing ID $ID server daemon \"xr\"";
	$f[]="### END INIT INFO";
	$f[]="#";
	$f[]="export PATH=\"\${PATH:+\$PATH:}/usr/sbin:/sbin\"";
	
	$f[]="case \"\$1\" in";
	$f[]="\tstart)";
	$f[]="\t\t$php5 ". __FILE__." --start-instance $ID";
	$f[]="\t\t;;";
	$f[]="\tstop)";
	$f[]="\t\t$php5 ". __FILE__." --stop-instance $ID";	
	$f[]="\t\t;;";
	$f[]="\trestart)";
	$f[]="\t\t$php5 ". __FILE__." --restart-instance $ID";
	$f[]="\t\t;;";	
	$f[]="\treconfigure)";
	$f[]="\t\t$php5 ". __FILE__." --build-instance $ID";
	$f[]="\t\t;;";
	$f[]="*)";
	$f[]="\techo \"Usage: /etc/init.d/xr-$ID {start|stop|restart|reconfigure}\"";
	$f[]="\texit 1";
	$f[]="esac";
	$f[]="exit 0\n";

	$debianbin=$unix->find_program("update-rc.d");
	$redhatbin=$unix->find_program("chkconfig");
	@file_put_contents("/etc/init.d/xr-$ID", @implode("\n", $f));
	shell_exec("$chmod +x /etc/init.d/xr-$ID >/dev/null 2>&1");
	if(is_file($debianbin)){
		shell_exec("$debianbin -f xr-$ID defaults >/dev/null 2>&1");
	}
	if(is_file($redhatbin)){
		shell_exec("$redhatbin --add xr-$ID >/dev/null 2>&1");
		shell_exec("$redhatbin --level 2345 xr-$ID on >/dev/null 2>&1");
	}
	crossroads_events("/etc/init.d/xr-$ID was successfully added",$ID,__FUNCTION__,__LINE__);
}

function remove_initd($ID){
	if(!is_file("/etc/init.d/xr-$ID")){return;}
	$unix=new unix();
	$debianbin=$unix->find_program("update-rc.d");
	$redhatbin=$unix->find_program("chkconfig");
	echo "Starting......: Crossroads ID:$ID remove init.d/xr-$ID\n";	
	if(is_file($redhatbin)){
		shell_exec("$redhatbin --del xr-$ID >/dev/null 2>&1");
		shell_exec("$redhatbin --level 2345 xr-$ID off >/dev/null 2>&1");
	}
	if(is_file($debianbin)){
		shell_exec("$debianbin -n -f xr-$ID remove >/dev/null 2>&1");
	}	
	
	crossroads_events("/etc/init.d/xr-$ID was successfully removed",$ID,__FUNCTION__,__LINE__);
	
}