<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__) . '/ressources/class.spamassassin.inc');
	include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	include_once(dirname(__FILE__).  '/framework/class.unix.inc');
	include_once(dirname(__FILE__).  '/framework/frame.class.inc');
	include_once(dirname(__FILE__).  '/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).  '/ressources/class.system.network.inc');	
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}	

	if($argv[1]=="--build"){build();die();}
	if($argv[1]=="--multiples-start"){multiples_start();die();}
	if($argv[1]=="--multiples-status"){echo multiples_status();die();}
	if($argv[1]=="--multiples-restart"){multiples_restart();die();}
	if($argv[1]=="--multiples-stop"){multiples_stop();die();}
	
	
	


function build(){
 
	$sock=new sockets();
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	
	@unlink("/etc/artica-postfix/croassroads.cmdline");

	if(!is_array($MAIN["BACKENDS"])){
		echo "Starting......: Crossroads Daemon no back end server\n";
		return;
	}
		
	
	if($MAIN["PARAMS"]["backend-timout"]==null){$MAIN["PARAMS"]["backend-timout"]=30;}
	if($MAIN["PARAMS"]["client-timout"]==null){$MAIN["PARAMS"]["client-timout"]=30;}
	if($MAIN["PARAMS"]["checkup-interval"]==null){$MAIN["PARAMS"]["checkup-interval"]=10;}
	if($MAIN["PARAMS"]["wakeup-interval"]==null){$MAIN["PARAMS"]["wakeup-interval"]=5;}
	if($MAIN["PARAMS"]["listen_port"]==null){$MAIN["PARAMS"]["listen_port"]=25;}
	if($MAIN["PARAMS"]["listen_ip"]==null){$MAIN["PARAMS"]["listen_ip"]=0;}  
	if($MAIN["PARAMS"]["client-timout-write"]==null){$MAIN["PARAMS"]["client-timout-write"]=5;}
	if($MAIN["PARAMS"]["backend-timout-write"]==null){$MAIN["PARAMS"]["backend-timout-write"]=5;}
	if($MAIN["PARAMS"]["dispatch-mode"]==null){$MAIN["PARAMS"]["dispatch-mode"]="least-connections";}

$cd[]="--server tcp:{$MAIN["PARAMS"]["listen_ip"]}:{$MAIN["PARAMS"]["listen_port"]}";	
//$cd[]="--pidfile /var/run/crossroads.pid";
$cd[]="--backend-timeout {$MAIN["PARAMS"]["backend-timout"]}:{$MAIN["PARAMS"]["backend-timout-write"]}";
$cd[]="--client-timeout {$MAIN["PARAMS"]["client-timout"]}:{$MAIN["PARAMS"]["client-timout-write"]}";
$cd[]="--checkup-interval {$MAIN["PARAMS"]["checkup-interval"]}";
$cd[]="--dispatch-mode {$MAIN["PARAMS"]["dispatch-mode"]}";
while (list ($servername, $ligne) = each ($MAIN["BACKENDS"]) ){	$cd[]="--backend $servername";}
@file_put_contents("/etc/artica-postfix/croassroads.cmdline",@implode(" ",$cd));
}

function multiples_start(){
	$GLOBALS["CLASS_UNIX"]=new unix();
	$xr=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	
	if(!is_file($xr)){
		if($GLOBALS["VERBOSE"]){echo "Starting......: Crossroads multiple xr no such binary\n";}
		return;
	} 
		
	
	$sql="SELECT * FROM crossroads_smtp";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "Starting......: Crossroads multiple $q->mysql_error\n";return;}
	if(mysql_num_rows($results)==0){echo "Starting......: Crossroads multiple no interfaces set\n";return;}
	
	
	$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$arrayConf=unserialize($ligne["parameters"]);
		$ipaddr=$ligne["ipaddr"];
		$pidsuffix=str_replace(".","",$ipaddr);
		$pidfile="/var/run/crossroads-$pidsuffix.pid";
		$instancesParams=$arrayConf["INSTANCES_PARAMS"];	
				
		
		$cd=array();
		$cd[]=$xr;
		if(count($arrayConf["INSTANCES"])==0){
			echo "Starting......: Crossroads multiple $ipaddr No clients set\n";
			continue;
		}		
		
		$pid=multiples_pid($ipaddr);
		if($pid==0){
					
			$cd[]="--server tcp:$ipaddr:25 --dispatch-mode round-robin";
			//$cd[]="--pidfile $pidfile";
			while (list ($ip, $none) = each ($arrayConf["INSTANCES"]) ){
				if(!is_numeric($instancesParams["MAXCONS"][$ip])){$instancesParams["MAXCONS"][$ip]=0;}
				if(!is_numeric($instancesParams["WEIGTH"][$ip])){$instancesParams["WEIGTH"][$ip]=1;}						
				echo "Starting......: Crossroads multiple round-robbin to $ip:25{$instancesParams["MAXCONS"][$ip]}:{$instancesParams["WEIGTH"][$ip]}...\n";
				$cd[]="--backend $ip:25:{$instancesParams["MAXCONS"][$ip]}:{$instancesParams["WEIGTH"][$ip]}";
			}
			echo "Starting......: Crossroads multiple $ipaddr...\n";
			$cmdline=trim($nohup." ".@implode(" ",$cd)." >/dev/null 2>&1 &");
			if($GLOBALS["VERBOSE"]){echo $cmdline."\n";}
			shell_exec($cmdline);
			for($i=0;$i<5;$i++){
				sleep(1);
				$pid=multiples_pid($ipaddr);
				if($pid>0){
					echo "Starting......: Crossroads multiple $ipaddr Success PID $pid...\n";
					break;
				}
			}
		
			if(multiples_pid($ipaddr)==0){
				echo "Starting......: Crossroads multiple $ipaddr Failed...\n";
				echo "Starting......: Crossroads multiple $cmdline\n";
			
			}
		}else{
			
			echo "Starting......: Crossroads multiple $ipaddr Already running PID: $pid\n";
		}

	}
	
	
	
}

function multiples_restart(){
	multiples_stop();
	multiples_start();
}


function multiples_stop(){
	$GLOBALS["CLASS_UNIX"]=new unix();
	$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
	$xr=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	$kill=$GLOBALS["CLASS_UNIX"]->find_program("kill");
	$pattern="$pgrep -l -f \"$xr\" 2>&1";
	exec($pattern,$results);
	while (list ($index, $line) = each ($results) ){
		if($GLOBALS["VERBOSE"]){echo "Stopping Crossroads Daemon...: $line\n";}
		if(preg_match("#^([0-9]+).+?pgrep#",$line)){
			if($GLOBALS["VERBOSE"]){echo "Stopping Crossroads Daemon...: pgrep line, continue\n";}
			continue;}
		
		if(preg_match("#^([0-9]+).+?18501#",$line)){
			if($GLOBALS["VERBOSE"]){
				echo "Stopping Crossroads Daemon...: Match master instance, continue\n";}
				continue;
		}
		if(!preg_match("#^([0-9]+)\s+#",$line,$re)){continue;}
		if($GLOBALS["VERBOSE"]){echo "Stopping Crossroads Daemon...: found pid: {$re[1]}\n";}
		
		if($GLOBALS["CLASS_UNIX"]->process_exists($re[1])){
			echo "Stopping Crossroads Daemon...: multiple instance PID {$re[1]}\n";
			shell_exec("$kill {$re[1]} >/dev/null 2>&1");
			for($i=0;$i<5;$i++){
				sleep(1);
				if(!$GLOBALS["CLASS_UNIX"]->process_exists($re[1])){
					echo "Stopping Crossroads Daemon...: multiple success PID {$re[1]}...\n";
					break;
				}
			}
		}
	}

}

function multiples_pid($ipaddr){
	
	$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
	$xr=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	$pattern="$pgrep -l -f \"$xr --server tcp:$ipaddr:25\" 2>&1";
	exec($pattern,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+.+?pgrep#",$line)){continue;}
		if(preg_match("#^([0-9]+)\s+#",$line,$re)){
			if($GLOBALS["CLASS_UNIX"]->process_exists($re[1])){
				return $re[1];
			}
		}
		
	}
	
	return 0;
	
}

function multiples_status(){
	$GLOBALS["CLASS_UNIX"]=new unix();
	$xr=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	if(!is_file($xr)){echo "Starting......: Crossroads multiple xr no such binary\n";return;} 
	$version=crossroad_version();
	$sql="SELECT * FROM crossroads_smtp";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(mysql_num_rows($results)==0){echo "Starting......: Crossroads multiple no interfaces set\n";return;}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$arrayConf=unserialize($ligne["parameters"]);
		$ipaddr=$ligne["ipaddr"];
		
		$pid=multiples_pid($ipaddr);
		$l[]="[APP_CROSSROADS:$ipaddr]";
		$l[]="service_name=APP_CROSSROADS";
	 	$l[]="master_version=$version";
	 	$l[]="service_cmd=crossroad-multiple";	
		$l[]="service_disabled=1";
		$l[]="family=postfix";
		if(!$GLOBALS["CLASS_UNIX"]->process_exists($pid)){
			WATCHDOG("APP_CROSSROADS","crossroad-multiple");
			$l[]="running=0";
			$l[]="installed=1\n";
			return implode("\n",$l);
			
		}	
		$l[]="running=1";
		$l[]=$GLOBALS["CLASS_UNIX"]->GetMemoriesOf($pid);
		$l[]="";			
		
		
		
	}
	
	return @implode("\n",$l);
	
}

function crossroad_version(){
	$xr=$GLOBALS["CLASS_UNIX"]->find_program("xr");
	exec("$xr -V 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#version.+?([0-9\.]+)#",$line,$re)){
			return $re[1];
		}
	}
	
}

function WATCHDOG($APP_NAME,$cmd){
		if($GLOBALS["DISABLE_WATCHDOG"]){return null;}
		if($GLOBALS["ArticaWatchDogList"][$APP_NAME]==null){$GLOBALS["ArticaWatchDogList"][$APP_NAME]=1;}
		if($GLOBALS["ArticaWatchDogList"][$APP_NAME]==1){
			exec("/etc/init.d/artica-postfix start $cmd 2>&1",$results);
			if($GLOBALS["VERBOSE"]){echo "\n".@implode("\n",$results)."\n";return;}
			$GLOBALS["CLASS_UNIX"]->send_email_events("$APP_NAME stopped","Artica tried to start it:\n".@implode("\n",$results),"system");
		}	
	
}


?>