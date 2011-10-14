<?php
$GLOBALS["OUTPUT_START"]=false;
$GLOBALS["SILENT"]=false;
$GLOBALS["NOWATCHDOG"]=false;
$GLOBALS["NOWCONF"]=false;


if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.postfix-multi.inc');
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.assp-multi.inc');
include_once(dirname(__FILE__) . '/ressources/class.maincf.multi.inc');


$_GET["LOGFILE"]="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--nowachdog#",implode(" ",$argv))){$GLOBALS["NOWATCHDOG"]=true;}
if(preg_match("#--noconf#",implode(" ",$argv))){$GLOBALS["NOWCONF"]=true;}

if($argv[1]=="--start"){startInstances();die();}
if($argv[1]=="--stop"){stopAllINstances();die();}
if($argv[1]=="--restart"){stopAllINstances();startInstances();die();}
if($argv[1]=="--start-instance"){startSingleInstance($argv[2]);die();}
if($argv[1]=="--stop-instance"){stopSingleInstance($argv[2]);die();}
if($argv[1]=="--reload-instance"){ReloadSingleInstance($argv[2]);die();}
if($argv[1]=="--restart-instance"){RestartSingleInstance($argv[2]);die();}
if($argv[1]=="--config-instance"){Buildconfig($argv[2]);die();}
if($argv[1]=="--single-status"){StatusInstance($argv[2]);die();}
if($argv[1]=="--all-status"){$GLOBALS["SILENT"]=true;StatusAllInstances($argv[2]);die();}

function startInstances(){
	checksConfigs();
	reset($GLOBALS["postfwd2_instances"]);
	@mkdir("/etc/postfwd2");
	
	while (list ($instance, $nth) = each ($GLOBALS["postfwd2_instances"]) ){
		if($GLOBALS["VERBOSE"]){echo "start: $instance\n";}
		startSingleInstance($instance);
	}
		
}

function stopAllINstances(){
	checksConfigs();
	reset($GLOBALS["postfwd2_instances"]);
	while (list ($instance, $nth) = each ($GLOBALS["postfwd2_instances"]) ){
		stopSingleInstance($instance);
	}	
}

function StatusAllInstances(){
	checksConfigs();
	if(!is_array($GLOBALS["postfwd2_instances"])){return;}
	reset($GLOBALS["postfwd2_instances"]);
	while (list ($instance, $nth) = each ($GLOBALS["postfwd2_instances"]) ){
		StatusInstance($instance);
	}	
}

function stopSingleInstance($instance){
	$pidfile="/var/run/postfwd2/$instance.pid";
	$unix=new unix();
	
	$master_pid=@file_get_contents("/var/run/postfwd2/$instance.pid");
	
	if(!$unix->process_exists($master_pid)){
		echo "Stopping postfwd2............: Already stopped\n";
		return;
	}
	
	$cmd=buildcmdlines($instance);
	shell_exec($cmd." --stop");
	sleep(1);
	echo "Stopping postfwd2............: PID $master_pid\n";
	
	for($i=0;$i<10;$i++){
		if($unix->process_exists($master_pid)){
			sleep(1);
		}
		
	}
	
	if($unix->process_exists($master_pid)){
		$master_pid=trim($master_pid);
		echo "Stopping postfwd2............: PID $master_pid failed\n";	
	}else{
		echo "Stopping postfwd2............: PID $master_pid success\n";
	}
	
}


function startSingleInstance($instance){
	$GLOBALS["OUTPUT_START"]=true;
	$unix=new unix();
	@mkdir("/var/run/postfwd2");
	@chmod("/var/run/postfwd2",755);
	@chown("/var/run/postfwd2","postfix");
	echo "Starting......: postfwd2 instance:$instance\n";
	$pidfile="/var/run/postfwd2/$instance.pid";
	$master_pid=@file_get_contents("/var/run/postfwd2/$instance.pid");
	if($unix->process_exists($master_pid)){
		echo "Starting......: postfwd2 instance:$instance already running PID $master_pid\n";
		return;
	}

	if(!$GLOBALS["NOWCONF"]){Buildconfig($instance);}
	$cmd=buildcmdlines($instance);
	shell_exec($cmd);
	for($i=0;$i<10;$i++){
		$master_pid=@file_get_contents("/var/run/postfwd2/$instance.pid");
		if(!$unix->process_exists($master_pid)){
			sleep(1);
		}
		
	}	
	$master_pid=@file_get_contents("/var/run/postfwd2/$instance.pid");
	if(!$unix->process_exists($master_pid)){
		echo "Starting......: postfwd2 failed\n";
		echo "Starting......: postfwd2 \"$cmd\"\n";
	}else{
		echo "Starting......: postfwd2 success with new PID $master_pid\n";
	}
	
	
	
}

function buildcmdlines($instance){
	if($instance=="master"){$listen_ip="127.0.0.1";}else{
		$main=new maincf_multi($instance);
		$listen_ip=$main->ip_addr;
	}
	if($GLOBALS["OUTPUT_START"]){	
		echo "Starting......: postfwd2 instance:$instance listen $listen_ip:10040\n";
	}
	$pidfile="/var/run/postfwd2/$instance.pid";
	$parms[]="/usr/share/artica-postfix/bin/postfwd2.pl";
	$parms[]="--file /etc/postfwd2/$instance.conf";
	$parms[]="--interface $listen_ip";
	$parms[]="--proto tcp";
	$parms[]="--port 10040";
	$parms[]="--pidfile $pidfile";
	$parms[]="--user postfix";
	$parms[]="--group postfix";
	$parms[]="--logname postfwd2-$instance";
	$cmd=@implode(" ",$parms);
	return $cmd;	
}

function RestartSingleInstance($instance){
	
	Buildconfig($instance);
	$cmd=buildcmdlines($instance);
	$unix=new unix();
	
	$pidfile="/var/run/postfwd2/$instance.pid";
	$master_pid=@file_get_contents("/var/run/postfwd2/$instance.pid");
	if($unix->process_exists($master_pid)){
		echo "Starting......: postfwd2 instance:$instance reloading\n";
		$suffix=" --reload";}else{
			startSingleInstance($instance);
			return;
		}
	
	
	stopSingleInstance($instance);
	startSingleInstance($instance);
}
function ReloadSingleInstance($instance){
	
	Buildconfig($instance);
	$cmd=buildcmdlines($instance);
	$unix=new unix();
	
	$pidfile="/var/run/postfwd2/$instance.pid";
	$master_pid=@file_get_contents("/var/run/postfwd2/$instance.pid");
	if($unix->process_exists($master_pid)){
		echo "Starting......: postfwd2 instance:$instance reloading\n";
		$suffix=" --reload";}
		else{
			startSingleInstance($instance);
			return;
		}
	
	shell_exec("$cmd $suffix");
	
}


function BuildObjects($instance){
	$f=array();
	$ms["eq"]="=";
	$ms["eq2"]="==";
	$ms["noteq"]="!=";
	$ms["aboveeq"]=">";
	$ms["abovenot"]="!>";	
	$ms["lowereq"]=">";
	$ms["lowernot"]="!>";
	$ms["matches"]="=~";
	$ms["matchesnot"]="!=~";
	$ms["no"]="!=";		
	
	$sql="SELECT ID,ObjectName FROM postfwd2_objects WHERE instance='$instance'"; 
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');		
	if(!$q->ok){echo "Starting......: postfwd2 $q->mysql_error -> BuildObjects()\n";return;}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$sql="SELECT * FROM postfwd2_items WHERE objectID='{$ligne["ID"]}'";
		$results2=$q->QUERY_SQL($sql,'artica_backup');
		$items=array();	
		while($ligne2=mysql_fetch_array($results2,MYSQL_ASSOC)){
			if($ligne2["item"]=="object"){$items[]="&&OBJECT{$ligne2["item_data"]}";continue;}
			$items[]="\t".$ligne2["item"].$ms[$ligne2["operator"]].$ligne2["item_data"];
		}
		
		$f[]="&&OBJECT{$ligne["ID"]}{";
		$f[]=@implode("\n", $items);
		$f[]="}\n";
	}
	
	
	return @implode("\n", $f);
	
	
	
}



function Buildconfig($instance){
		@mkdir("/etc/postfwd2");
		@unlink("/etc/postfwd2/$instance.conf");
		$main=new maincf_multi($instance);
		$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
		$ENABLE_POSTFWD2=$array_filters["APP_POSTFWD2"];
		if(!is_numeric($ENABLE_POSTFWD2)){$ENABLE_POSTFWD2=0;}
		if($ENABLE_POSTFWD2==0){echo "Starting......: postfwd2 $instance is disabled\n";return;}

		
		$GBRULES[]=BuildObjects($instance);
		
	
		$sql="SELECT * FROM postfwd2 WHERE enabled=1 AND instance='$instance' ORDER BY rank";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,'artica_backup');		
		if(!$q->ok){echo "Starting......: postfwd2 $q->mysql_error\n";return;}
		
		$ms["eq"]="=";
		$ms["eq2"]="==";
		$ms["noteq"]="!=";
		$ms["aboveeq"]=">";
		$ms["abovenot"]="!>";	
		$ms["lowereq"]=">";
		$ms["lowernot"]="!>";
		$ms["matches"]="=~";
		$ms["matchesnot"]="!=~";		
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$action=$ligne["action"];
				$action_text=null;
				$items_text=null;
				$items=array();
				if(preg_match("#(.+?):(.+?):(.+)#",$action,$re)){
					if($re[1]=="rate"){
						$action_text="action==rate(client_address/{$re[2]}/{$re[3]}/450 4.7.1 sorry, max {$re[2]} connections {$re[3]} seconds)";
					}
					if($re[1]=="size"){
						$action_text="action==size(client_address/{$re[2]}/{$re[3]}/450 4.7.1 sorry, max size {$re[2]} per {$re[3]} seconds)";
						if($GLOBALS["VERBOSE"]){echo "DEBUG: {$ligne["ID"]}/{$ligne["rank"]}:: END_OF_DATA {$ligne["ID"]} {$re[1]} -> max {$re[2]} per {$re[3]} seconds\n";}
						$items[]="state==END_OF_DATA";
					}	
		
					if($re[1]=="rcpt"){
						$action_text="action==rcpt(client_address/{$re[2]}/{$re[3]}/450 4.7.1 sorry, max {$re[2]} recipients {$re[3]} seconds)";
						if($GLOBALS["VERBOSE"]){echo "DEBUG: {$ligne["ID"]}/{$ligne["rank"]}:: END_OF_DATA {$re[1]} -> max {$re[2]} per {$re[3]} seconds\n";}
						$items[]="state==END_OF_DATA";
					}			
				}
				
				if(preg_match("#jump R-([0-9]+)#",$action,$re)){$action_text="action=jump(R{$re[1]})";}	
				if(preg_match("#score:(.+)#",$action,$re)){$action_text="action=score({$re[1]})";}
				if(preg_match("#throttle:(.+?):(.+)#",$action,$re)){$action_text="action=FILTER {$re[1]}:";}
				if($ligne["action"]=="reject"){$action_text="action=REJECT SMTP Firewall match rule {$ligne["ID"]}";}
				if($ligne["action"]=="BYPASSAMAVIS"){$action_text=_ByPassAmavis($instance);}
				
				
				
				if($action_text==null){$action_text="action={$ligne["action"]}";}

				$rules=unserialize(base64_decode($ligne["rule"]));	
		
				while (list ($num, $array) = each ($rules) ){
					if($array["item"]=="object"){$items[]="&&OBJECT{$array["item_data"]}";continue;}
					$items[]=$array["item"].$ms[$array["operator"]].$array["item_data"];
				}
				
				if(count($items)>0){
					if($GLOBALS["VERBOSE"]){while (list ($a, $b) = each ($items) ){echo "DEBUG: {$ligne["ID"]}/{$ligne["rank"]}:: [$a] \"$b\"\n";}reset($items);}
					$items_text=@implode("; ",$items).";";
				}else{
					if($GLOBALS["VERBOSE"]){echo "DEBUG: {$ligne["ID"]}/{$ligne["rank"]}:: no items\n";}
				}
				
				unset($items);
				$GBRULES[]="id=R{$ligne["ID"]};$items_text$action_text";
				
			
		}
		
		if(count($GBRULES)>0){
			echo "Starting......: postfwd2 $instance ".count($GBRULES)." rules\n";
			@file_put_contents("/etc/postfwd2/$instance.conf",@implode("\n",$GBRULES)."\n");
			@chown("/etc/postfwd2/$instance.conf","postfix");
		}
		
		
	
}

function _ByPassAmavis($instance){
	$users=new usersMenus();
	if($users->AMAVIS_INSTALLED){return "action=dunno";}
	
		if($instance=="master"){
			if($users->EnableAmavisDaemon==0){return "action=dunno";}
			return "action=FILTER smtp:[127.0.0.1]:10025";
		}
			
		$main=new maincf_multi($_GET["instance"]);
		$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
		if(!isset($array_filters["APP_AMAVIS"])){$array_filters["APP_AMAVIS"]=0;}
		if($array_filters["APP_AMAVIS"]==0){return "action=dunno";}
		return "action=FILTER smtp:$main->ip_addr:10026";
	}


function checksConfigs(){
	
	$sql="SELECT * FROM postfwd2 WHERE enabled=1 ";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "Starting......: postfwd2 $q->mysql_error\n";return;}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$GLOBALS["postfwd2_instances"][$ligne["instance"]]=true;
	}
	
	if($GLOBALS["VERBOSE"]){$GLOBALS["SILENT"]=false;}
	if(!$GLOBALS["SILENT"]){echo "Starting......: postfwd2 ". count($GLOBALS["postfwd2_instances"]). " instance(s)\n";}
	
}

function postfwd2_version(){
	if(isset($GLOBALS["postfwd2_version"])){return $GLOBALS["postfwd2_version"];}
	exec("/usr/share/artica-postfix/bin/postfwd2.pl --version 2>&1",$results);
	$f=trim(@implode(" ",$results));
	if(preg_match("#postfwd2\s+([0-9\.]+)#",$f,$re)){$GLOBALS["postfwd2_version"]=$re[1];return $re[1];}	
}


function StatusInstance($instance){
	
	if($GLOBALS["VERBOSE"]){echo "StatusInstance($instance)\n";}
	if(!isset($GLOBALS["postfwd2_instances"])){checksConfigs();}
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
	if(!isset($GLOBALS["CLASS_SOCKETS"])){$GLOBALS["CLASS_SOCKETS"]=new sockets();}
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	
	$main=new maincf_multi($instance);
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	$ENABLE_POSTFWD2=$array_filters["APP_POSTFWD2"];
	if($GLOBALS["VERBOSE"]){echo "ENABLE_POSTFWD2=$ENABLE_POSTFWD2\n";}
	if(!is_numeric($ENABLE_POSTFWD2)){$ENABLE_POSTFWD2=0;}	
	
	$pid_path="/var/run/postfwd2/$instance.pid";
	$master_pid=trim(@file_get_contents($pid_path));
	if($GLOBALS["VERBOSE"]){echo "$pid_path=$master_pid\n";}
	$version=postfwd2_version();
	if($GLOBALS["VERBOSE"]){echo "version=$version\n";}
		$l[]="[APP_POSTFWD2:$instance]";
		$l[]="service_name=APP_POSTFWD2";
	 	$l[]="master_version=$version";
	 	$l[]="service_cmd=postfwd2";	
	 	$l[]="service_disabled=$ENABLE_POSTFWD2";
	 	$l[]="pid_path=$pid_path";
	 	$l[]="watchdog_features=1";
	 	$l[]="family=system";	
	 	
	 	
		if($ENABLE_POSTFWD2==0){
			if($GLOBALS["VERBOSE"]){echo "Disabled....\n";}
			$l[]="";echo implode("\n",$l);
			return;
		}
		

		if(!$GLOBALS["CLASS_UNIX"]->process_exists($master_pid)){
			if($GLOBALS["VERBOSE"]){echo "$master_pid not running watchdog ?\n";}
			$unix=new unix();
			if(!$GLOBALS["NOWATCHDOG"]){
				shell_exec($GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.status.php --watchdog-service APP_POSTFWD2 postfwd2 &");
			}
			$l[]="";
			echo implode("\n",$l);
			return;
		}else{
		if($GLOBALS["VERBOSE"]){echo "Pid: $master_pid RUNNING\n";}
		}	
		
		if($GLOBALS["VERBOSE"]){echo "unix->GetMemoriesOf($master_pid)\n";}
		$l[]=$GLOBALS["CLASS_UNIX"]->GetMemoriesOf($master_pid);
		$l[]="";
	
	echo implode("\n",$l)."\n";return;		
	
}
?>
