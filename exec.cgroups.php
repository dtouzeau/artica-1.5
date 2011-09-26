<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["AS_ROOT"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}


if($argv[1]=='--build'){build();die();}
if($argv[1]=='--start'){start();die();}
if($argv[1]=='--restart'){restart();die();}
if($argv[1]=='--stop'){stop();die();}
if($argv[1]=='--reload'){reload();die();}
if($argv[1]=='--defaultP'){defaultProcesses();die();}
if($argv[1]=='--services-check'){services_check();exit();}
if($argv[1]=="--cgred-start"){cgred_start();exit;}
if($argv[1]=="--cgred-stop"){cgred_stop();exit;}
if($argv[1]=="--ismounted"){ismounted();exit;}
if($argv[1]=="--stats"){buildstats();exit;}
if($argv[1]=="--tasks"){TaskSave();exit;}



function restart(){stop();start();}

function is_cgroup_mounted($path){
	$path=str_replace("/", "\/", $path);
	$f=explode("\n",@file_get_contents("/proc/mounts"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#\/cgroups\/$path\s+#",$ligne)){return true;}
		if($GLOBALS["VERBOSE"]){echo "is_cgroup_mounted:: $ligne NO MATCH \/cgroups\/$path\s+\n";}
	}
	return false;
}

function ismounted(){
	load_family();
	reset($GLOBALS["CGROUPS_FAMILY"]);
		while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			if(!is_cgroup_structure_mounted($structure)){
				echo "Starting......: cgroups: structure:$structure is not mounted\n";
			}else{
				echo "Starting......: cgroups: structure:$structure is mounted\n";
			}
		}	
}

function is_old_debian_mounted(){
	$array=array();
	$f=explode("\n",@file_get_contents("/proc/mounts"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^cgroup\s+(.+?)\s+cgroup#",$ligne,$re)){
			$array[]=$re[1];
		}
		
	}
	
	return $array;
	
}

function is_cgroup_structure_mounted($structure){
	$structure=str_replace("/", "\/", $structure);
	$f=explode("\n",@file_get_contents("/proc/mounts"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#\/cgroups\/$structure\s+#",$ligne)){
			if($GLOBALS["VERBOSE"]){echo "is_cgroup_structure_mounted:: $ligne \$MATCH$ \/cgroups\/$structure\s+\n";}
			return true;}
		if($GLOBALS["VERBOSE"]){echo "is_cgroup_structure_mounted:: $ligne NO MATCH \/cgroups\/$structure\s+\n";}
	}
	return false;
}

function load_family(){
	$f=explode("\n", @file_get_contents("/proc/cgroups"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^([a-z\_]+)\s+#", $ligne,$re)){
			if($re[1]=="net_cls"){continue;}
			if($re[1]=="freezer"){continue;}
			if($re[1]=="devices"){continue;}
			$GLOBALS["CGROUPS_FAMILY"][$re[1]]=true;
		}
	}
}

function testStructure($group,$structure,$MyPid){
	if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	echo "Starting......: cgroups: testing structure $structure on group $group for my PID:$MyPid\n";
	if(!is_dir("/cgroups/$structure/$group")){
		echo "Starting......: cgroups: testing structure /cgroups/$structure/$group no such directory...\n";
		return false; 
	}
	
	if(!is_file("/cgroups/$structure/$group/tasks")){
		echo "Starting......: cgroups: testing structure /cgroups/$structure/$group/tasks no such file...\n";
		return false; 		
	}
	
	
	$echobin=$GLOBALS["CLASS_UNIX"]->find_program("echo");
	if(!is_file($echobin)){
		echo "Starting......: cgroups: testing structure 'echo' no such binary...\n";
		return false; 
	}

	exec("$echobin $MyPid >/cgroups/$structure/$group/tasks 2>&1",$results);
	$line=trim(@implode("", $results));
	if(strlen($line)>5){
		echo "Starting......: cgroups: testing structure failed \"$line\"...\n";
		return false;
	}
	return true;
	
}


function build(){
		if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
		$catBin=$GLOBALS["CLASS_UNIX"]->find_program("cat");
		$MyPid=getmypid();
		services_check();
		load_family();
			
		$f[]="\nmount {";
		
		while (list ($num, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			echo "Starting......: cgroups: supported structure:$num\n";
			$f[]="\t$num = /cgroups/$num;";
		}
		$f[]="}\n";
		reset($GLOBALS["CGROUPS_FAMILY"]);
		$DirMounts[]="cpu";
		$DirMounts[]="memory";
		$DirMounts[]="cpuacct";

		if(!is_dir("/cgroups")){@mkdir("/cgroups",755,true);}

		$q=new mysql();
		$sql="SELECT *  FROM cgroups_groups ORDER BY cpu_shares,groupname";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "$q->mysql_error\n";}
		
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	
			if($ligne["memory_soft_limit_in_bytes"]<1){$ligne["memory_soft_limit_in_bytes"]=-1;}
			if($ligne["memory_limit_in_bytes"]<1){$ligne["memory_limit_in_bytes"]=-1;}
			if($ligne["memory_memsw_limit_in_bytes"]<1){$ligne["memory_memsw_limit_in_bytes"]=-1;}	
			if($ligne["memory_swappiness"]<1){$ligne["memory_swappiness"]=60;}
			if($ligne["cpuset_cpus"]==null){$ligne["cpuset_cpus"]="0,1,2,3,4,5,6,7,8";}	
			echo "Starting......: cgroups: Group \"{$ligne["groupname"]}\"\n";				
			writerules($ligne["ID"],$ligne["groupname"]);
			
					
			$f[]="group {$ligne["groupname"]} {";
			
			if(testStructure($ligne["groupname"],"cpu",$MyPid)){		
				$f[]="\tcpu {";
				if($ligne["cpu_shares"]>0){
					$f[]="\t\tcpu.shares = {$ligne["cpu_shares"]};";
					$GLOBALS["ArrayRULES"][$ligne["groupname"]]["cpu"]["cpu.shares"]=$ligne["cpu_shares"];
				
				}
				if($ligne["cpu_rt_runtime_us"]>10000){
					$f[]="\t\tcpu.rt_runtime_us = {$ligne["cpu_rt_runtime_us"]};";
					$GLOBALS["ArrayRULES"][$ligne["groupname"]]["cpu"]["cpu.rt_runtime_us"]=$ligne["cpu_rt_runtime_us"];
				}
				if($ligne["cpu_rt_runtime_us"]>10000){
					$f[]="\t\tcpu.rt_period_us = {$ligne["cpu_rt_period_us"]};";
					$GLOBALS["ArrayRULES"][$ligne["groupname"]]["cpu"]["cpu.rt_period_us"]=$ligne["cpu_rt_period_us"];
				
				}
			$f[]="\t}";
		}
		
		
		
		if($GLOBALS["CGROUPS_FAMILY"]["memory"]){
			$GLOBALS["ArrayRULES"][$ligne["groupname"]]["memory"]["memory.soft_limit_in_bytes"]="{$ligne["memory_soft_limit_in_bytes"]}M";
			$GLOBALS["ArrayRULES"][$ligne["groupname"]]["memory"]["memory.limit_in_bytes"]="{$ligne["memory_limit_in_bytes"]}M";
			$GLOBALS["ArrayRULES"][$ligne["groupname"]]["memory"]["memory.memsw.limit_in_bytes"]="{$ligne["memory_memsw_limit_in_bytes"]}M";
			$GLOBALS["ArrayRULES"][$ligne["groupname"]]["memory"]["memory.swappiness"]="{$ligne["memory_swappiness"]}M";
			if(testStructure($ligne["groupname"],"memory",$MyPid)){
				$f[]="\tmemory {";
				$f[]="\t\tmemory.soft_limit_in_bytes = {$ligne["memory_soft_limit_in_bytes"]}M;";
				$f[]="\t\tmemory.limit_in_bytes = {$ligne["memory_limit_in_bytes"]}M;";
				$f[]="\t\tmemory.memsw.limit_in_bytes = {$ligne["memory_memsw_limit_in_bytes"]}M;";
				$f[]="\t\tmemory.swappiness = {$ligne["memory_swappiness"]};";
				$f[]="\t}";
			}
		}	
		if($GLOBALS["CGROUPS_FAMILY"]["cpuset"]){
			if(testStructure($ligne["groupname"],"cpuset",$MyPid)){
				$GLOBALS["ArrayRULES"][$ligne["groupname"]]["cupset"]["cpuset.cpus"]="{$ligne["cpuset_cpus"]}M";
				$f[]="\tcupset {";
				$f[]="\t\tcpuset.cpus = {$ligne["cpuset_cpus"]};";
				//$f[]="\t\tcpuset.mems = 0-16;";
				$f[]="\t}";
			}
		}
		
		$f[]="}\n";
		}
		
		echo "Starting......: cgroups: Writing /etc/cgconfig.conf\n";
		@file_put_contents("/etc/cgconfig.conf", @implode("\n", $f));
		echo "Starting......: cgroups: Writing /etc/cgrules.conf\n";
		@file_put_contents("/etc/cgrules.conf", @implode("\n", $GLOBALS["CGRULES_CONF"]));
		if(file_exists("/etc/sysconfig/cgconfig")){@file_put_contents("/etc/sysconfig/cgconfig", @implode("\n", $f));}


	if(is_file("/etc/sysconfig/cgred.conf")){
		$u[]="CONFIG_FILE=\"/etc/cgrules.conf\"";
		$u[]="LOG_FILE=\"/var/log/cgrulesengd.log\"";
		$u[]="NODAEMON=\"\"";
		$u[]="SOCKET_USER=\"\"";
		$u[]="SOCKET_GROUP=\"cgred\"";
		$u[]="LOG=\"\"";
		@file_put_contents("/etc/sysconfig/cgred.conf", @implode("\n", $f));
		
	}
	if(is_file("/etc/default/cgred.conf")){
		$u[]="CONFIG_FILE=\"/etc/cgrules.conf\"";
		$u[]="LOG_FILE=\"/var/log/cgrulesengd.log\"";
		$u[]="NODAEMON=\"\"";
		$u[]="SOCKET_USER=\"\"";
		$u[]="SOCKET_GROUP=\"cgred\"";
		$u[]="LOG=\"\"";
		@file_put_contents("/etc/default/cgred.conf", @implode("\n", $f));
		
	}


	$p[]="CREATE_DEFAULT=yes";
	@file_put_contents("/etc/default/cgconfig", @implode("\n", $p));
	
	
}

function stop(){
	if($GLOBALS["VERBOSE"]){echo "Starting......: cgroups: DEBUG:: ". __FUNCTION__. " START\n";}
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){echo "Starting......: cgroups: Already pid $pid is running, aborting\n";return;}
	@file_put_contents($pidfile, getmypid());
	
	
	$GLOBALS["CGROUPS_FAMILY"]=array();
	load_family();
	echo "Starting......: cgroups: stopping daemons\n";
	echo "Starting......: cgroups: stopping cgred\n";
	if(is_file("/etc/init.d/cgred")){shell_exec("/etc/init.d/cgred stop");}
	
		
		$umount=$unix->find_program("umount");
		$mount=$unix->find_program("mount");
		$rm=$unix->find_program("rm");
		$echo=$unix->find_program("echo");

	
		while (list ($num, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			if(is_cgroup_structure_mounted($num)){
				echo "Starting......: cgroups: unmount structure:$num\n";
				$results=array();
				exec("$umount -l /cgroups/$num  2>&1",$results);
				if(count($results)>1){while (list ($a, $b) = each ($results)){ echo "Starting......: cgroups: $b\n";}}
			}else{
				echo "Starting......: cgroups: structure:$num already dismounted\n";
			}
		}
		
		$arrayDEB=is_old_debian_mounted();
		while (list ($num, $mounted) = each ($arrayDEB)){
			if(trim($mounted)==null){continue;}
			echo "Starting......: cgroups: unmount $mounted\n";
			$results=array();
			exec("$umount -l $mounted  2>&1",$results);
			if(count($results)>1){while (list ($a, $b) = each ($results)){ echo "Starting......: cgroups: $b\n";}}
		}
		
		reset($GLOBALS["CGROUPS_FAMILY"]);
		while (list ($num, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			if(is_cgroup_structure_mounted($num)){
				echo "Starting......: cgroups: unmount structure:$num failed\n";
			}
		}		
	$results=array();	
	exec("$rm -rf /cgroups/* 2>&1",$results);	
	if(count($results)>1){while (list ($a, $b) = each ($results)){ echo "Starting......: cgroups: $b\n";}}
	sleep(2);
	cgred_stop();
	
}

function reload(){
	build();
	cgred_stop();
	cgred_start();
}

function start(){
	if($GLOBALS["VERBOSE"]){echo "Starting......: cgroups: DEBUG:: ". __FUNCTION__. " START\n";}
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){echo "Starting......: cgroups: Already pid $pid is running, aborting\n";return;}
	@file_put_contents($pidfile, getmypid());
	$GLOBALS["CGROUPS_FAMILY"]=array();
	
	$sock=new sockets();
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	if(!is_numeric($cgroupsEnabled)){$cgroupsEnabled=0;}
	if($cgroupsEnabled==0){echo "Starting......: cgroups: cgroups is disabled\n";stop();cgred_stop(true);return;}
	
	load_family();
	echo "Starting......: cgroups: starting daemons\n";
	
		$unix=new unix();
		$umount=$unix->find_program("umount");
		$mount=$unix->find_program("mount");
		$rm=$unix->find_program("rm");
		$echo=$unix->find_program("echo");	
	
		
		while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			if(!is_cgroup_structure_mounted($structure)){
				echo "Starting......: cgroups: mounting structure:$structure\n";
				@mkdir("/cgroups/$structure",775,true);
				$results=array();
				exec("$mount -t cgroup -o\"$structure\" none \"/cgroups/$structure\" 2>&1",$results);
				if(count($results)>1){while (list ($a, $b) = each ($results)){ echo "Starting......: cgroups: $b\n";}}
			}else{
				echo "Starting......: cgroups: structure:$structure already mounted\n";
			}
		}		
	
	build();
	
	reset($GLOBALS["CGROUPS_FAMILY"]);
	if(is_array($GLOBALS["ArrayRULES"])){
		while (list ($groupname, $array) = each ($GLOBALS["ArrayRULES"])){
			echo "Starting......: cgroups: mounting group:$groupname\n";
			while (list ($structure, $array2) = each ($array)){
				if(!isset($GLOBALS["CGROUPS_FAMILY"][$structure])){continue;}
				echo "Starting......: cgroups: create :/cgroups/$structure/$groupname\n";
				@mkdir("/cgroups/$structure/$groupname",775,true);
				while (list ($key, $value) = each ($array2)){
					echo "Starting......: cgroups:$groupname:$structure  $key = $value\n";
					@file_put_contents("/cgroups/$structure/$groupname/$key", $value);
				}
			}
				
		}	
	}else{
	 echo "Starting......: cgroups: No rules...\n";	
	}
	
	if(count($GLOBALS["PROCESSES"])>0){
		echo "Starting......: cgroups checking processes\n";
		while (list ($process, $groupname) = each ($GLOBALS["PROCESSES"])){
			$pid=intval($unix->PIDOF($process));
			if($pid>0){
				reset($GLOBALS["CGROUPS_FAMILY"]);
				while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
					$directory="/cgroups/$structure/$groupname";
					if(is_dir($directory)){
						shell_exec("$echo $pid >$directory/tasks");
						$c++;
					}else{
						if($GLOBALS["VERBOSE"]){echo "Starting......: cgroups $directory no such directory\n";}
					}
				}
			}
			
		}
	}	
	
	
	echo "Starting......: cgroups: starting daemons and $c attached processes\n";
	cgred_start();
}




function writerules($gpid,$gpname){
	$q=new mysql();
	$sql="SELECT * FROM cgroups_processes WHERE groupid=$gpid ORDER BY process_name";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";die();}
	echo "Starting......: cgroups: Group \"$gpname\" [$gpid] ". mysql_num_rows($results). " Processe(s)\n";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["process_name"]=trim($ligne["process_name"]);
		$ligne["user"]=trim($ligne["user"]);
		echo "Starting......: cgroups: attach {$ligne["user"]}:{$ligne["process_name"]}	to $gpname\n";
		$GLOBALS["CGRULES_CONF"][]="{$ligne["user"]}:\"{$ligne["process_name"]}\"\t*\t$gpname/";
		$GLOBALS["PROCESSES"][$ligne["process_name"]]=$gpname;
		
	}
	
}



function services_check(){
	if(is_file("/etc/init.d/cgconfig")){
		echo "Starting......: cgroups: checks cgconfig service...\n";
		if(!function_exists("is_link")){echo "Starting......: cgroups: is_link no such function\n";}
		if(!is_link("/etc/init.d/cgconfig")){
			echo "Starting......: cgroups: installing specific Artica init.d/cgconfig script\n";
			shell_exec("/bin/mv /etc/init.d/cgconfig /etc/init.d/cgconfig.bak");
			_write_cgconfig();
		}
	}else{
		echo "Starting......: cgroups: /etc/init.d/cgconfig no such file\n";
	}
	
	if(is_file("/etc/init.d/cgred")){
		if(!is_link("/etc/init.d/cgred")){
			shell_exec("/etc/init.d/cgred stop");
			echo "Starting......: cgroups: installing specific Artica init.d/cgred script\n";
			shell_exec("/bin/mv /etc/init.d/cgred /etc/init.d/cgred.bak");
			_write_cgredconfig();
		}
	}else{
		echo "Starting......: cgroups: /etc/init.d/cgred no such file\n";
	}
	
}

function _write_cgconfig(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$chmod=$unix->find_program("chmod");
	$ln=$unix->find_program("ln");
	if(!is_dir("/etc/artica-postfix/init.d")){@mkdir("/etc/artica-postfix/init.d",755,true);}
	$conf[]="#!/bin/bash";
	$conf[]="### BEGIN INIT INFO";
	$conf[]="# Provides:             cgconfig";
	$conf[]="# Required-Start:";
	$conf[]="# Required-Stop:";
	$conf[]="# Should-Start:";
	$conf[]="# Should-Stop:";
	$conf[]="# Default-Start:        2 3 4 5";
	$conf[]="# Default-Stop:         0 1 6";
	$conf[]="# Short-Description:    start and stop the WLM configuration";
	$conf[]="# Description:          This script allows us to create a default configuration";
	$conf[]="### END INIT INFO";
	$conf[]="";
	$conf[]="case \"\$1\" in";
	$conf[]=" start)";
	$conf[]="    $php /usr/share/artica-postfix/exec.cgroups.php --start \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]="  stop)";
	$conf[]="    $php /usr/share/artica-postfix/exec.cgroups.php --stop \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]=" restart)";
	$conf[]="     $php /usr/share/artica-postfix/exec.cgroups.php --stop \$1 \$2";
	$conf[]="     $php /usr/share/artica-postfix/exec.cgroups.php --start \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]=" reload)";
	$conf[]="     $php /usr/share/artica-postfix/exec.cgroups.php --reload \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]="";
	$conf[]="  *)";
	$conf[]="    echo \"Usage: \$0 {start|stop|restart|reload}\"";
	$conf[]="    exit 1";
	$conf[]="    ;;";
	$conf[]="esac";
	$conf[]="exit 0\n";	
	@file_put_contents("/etc/artica-postfix/init.d/cgconfig", @implode("\n", $conf));
	shell_exec("$chmod 755 /etc/artica-postfix/init.d/cgconfig");
	@symlink ( "/etc/artica-postfix/init.d/cgconfig" , "/etc/init.d/cgconfig" );
}

function _write_cgredconfig(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$chmod=$unix->find_program("chmod");
	$ln=$unix->find_program("ln");
	if(!is_dir("/etc/artica-postfix/init.d")){@mkdir("/etc/artica-postfix/init.d",755,true);}
	$conf[]="#!/bin/bash";
	$conf[]="### BEGIN INIT INFO";
	$conf[]="# Provides:             cgconfig";
	$conf[]="# Required-Start:";
	$conf[]="# Required-Stop:";
	$conf[]="# Should-Start:";
	$conf[]="# Should-Stop:";
	$conf[]="# Default-Start:        2 3 4 5";
	$conf[]="# Default-Stop:         0 1 6";
	$conf[]="# Short-Description:    start and stop the WLM configuration";
	$conf[]="# Description:          This script allows us to create a default configuration";
	$conf[]="### END INIT INFO";
	$conf[]="";
	$conf[]="case \"\$1\" in";
	$conf[]=" start)";
	$conf[]="    $php /usr/share/artica-postfix/exec.cgroups.php --cgred-start \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]="  stop)";
	$conf[]="    $php /usr/share/artica-postfix/exec.cgroups.php --cgred-stop \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]=" restart)";
	$conf[]="     $php /usr/share/artica-postfix/exec.cgroups.php --cgred-stop \$1 \$2";
	$conf[]="     $php /usr/share/artica-postfix/exec.cgroups.php --cgred-start \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]=" reload)";
	$conf[]="     $php /usr/share/artica-postfix/exec.cgroups.php --cgred-reload \$1 \$2";
	$conf[]="    ;;";
	$conf[]="";
	$conf[]="";
	$conf[]="  *)";
	$conf[]="    echo \"Usage: \$0 {start|stop|restart|reload}\"";
	$conf[]="    exit 1";
	$conf[]="    ;;";
	$conf[]="esac";
	$conf[]="exit 0\n";	
	@file_put_contents("/etc/artica-postfix/init.d/cgred", @implode("\n", $conf));
	shell_exec("$chmod 755 /etc/artica-postfix/init.d/cgred");
	@symlink ( "/etc/artica-postfix/init.d/cgred" , "/etc/init.d/cgred" );
}

function cgred_start(){
	if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	if($GLOBALS["VERBOSE"]){echo "Starting......: cgroups: DEBUG:: ". __FUNCTION__. " START\n";}
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($GLOBALS["CLASS_UNIX"]->process_exists($pid,basename(__FILE__))){echo "Starting......: cgroups: cgred_start function Already running pid $pid is running, aborting\n";return;}
	@file_put_contents($pidfile, getmypid());	
	
	$cgrulesengd=$GLOBALS["CLASS_UNIX"]->find_program("cgrulesengd");
	$sock=new sockets();
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	if(!is_numeric($cgroupsEnabled)){$cgroupsEnabled=0;}
	if($cgroupsEnabled==0){
		echo "Starting......: cgroups: CGroup Rules Engine Daemon cgroups is disabled\n";return;
		if(is_file($cgrulesengd)){
			$pid=$GLOBALS["CLASS_UNIX"]->PIDOF($cgrulesengd);
			if($GLOBALS["CLASS_UNIX"]->process_exists($pid)){cgred_stop(true);return;}
		}
	}	
	
	
	if(!is_file($cgrulesengd)){
		echo "Starting......: cgroups: CGroup Rules Engine Daemon no such binary\n";
		return;
	}
	
	echo "Starting......: cgroups: CGroup Rules Engine Daemon\n";
	load_family();
	$catBin=$GLOBALS["CLASS_UNIX"]->find_program("cat");
	reset($GLOBALS["CGROUPS_FAMILY"]);
		while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			if(!is_cgroup_structure_mounted($structure)){
				echo "Starting......: cgroups: CGroup Rules Engine Daemon structure:$structure is not mounted, aborting\n";
				return;
			}
		}

	$pid=$GLOBALS["CLASS_UNIX"]->PIDOF($cgrulesengd);
	if($GLOBALS["CLASS_UNIX"]->process_exists($pid)){
		echo "Starting......: cgroups: CGroup Rules Engine Daemon already exists pid $pid\n";
		return;
	}
	
		$q=new mysql();
		$sql="SELECT *  FROM cgroups_groups ORDER BY cpu_shares,groupname";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "$q->mysql_error\n";}	
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$group=$ligne["groupname"];
			reset($GLOBALS["CGROUPS_FAMILY"]);
			echo "Starting......: cgroups: CGroup Rules Engine Daemon checking group $group\n";
			while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
				if(!is_dir("/cgroups/$structure/$group")){
					echo "Starting......: cgroups: CGroup Rules Engine Daemon create structure $structure\n";
					@mkdir("/cgroups/$structure/$group",755,true);
				}
			}
		}	
	if(is_file("/var/log/cgrulesend.log")){@unlink("/var/log/cgrulesend.log");}
	if(is_file("/var/log/cgrulesengd.log")){@unlink("/var/log/cgrulesengd.log");}
	$cmdline="$cgrulesengd  -f /etc/cgrules.conf --logfile=/var/log/cgrulesengd.log";
	shell_exec($cmdline);
	for($i=0;$i<6;$i++){
		$pid=$GLOBALS["CLASS_UNIX"]->PIDOF($cgrulesengd);
		if($GLOBALS["CLASS_UNIX"]->process_exists($pid)){
			break;
		}
	sleep(1);
	}
	$pid=$GLOBALS["CLASS_UNIX"]->PIDOF($cgrulesengd);
	if($unix->process_exists($pid)){
		echo "Starting......: cgroups: CGroup Rules Engine started pid $pid\n";
		TaskSave();
	}else{
		echo "Starting......: cgroups: CGroup Rules Engine failed to start with cmdline: $cmdline\n";
	}
	
	
}
function cgred_stop($nomypidcheck=false){
	
	if($GLOBALS["VERBOSE"]){echo "Starting......: cgroups: DEBUG:: ". __FUNCTION__. " START\n";}
	$unix=new unix();
	if(!$nomypidcheck){
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid,basename(__FILE__))){
			$trace=debug_backtrace();if(isset($trace[1])){$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			}
			echo "Starting......: cgroups: cgred_stop() function Already running pid $pid, aborting $called\n";return;}
		@file_put_contents($pidfile, getmypid());	
	}
	
	$cgrulesengd=$unix->find_program("cgrulesengd");
	$kill=$unix->find_program("kill");
	if(!is_file($cgrulesengd)){
		echo "Stopping cgroups.............: CGroup Rules Engine Daemon no such binary\n";
		return;
	}
	
	$pid=$unix->PIDOF($cgrulesengd);
	if(!$unix->process_exists($pid)){
		echo "Stopping cgroups.............: CGroup Rules Engine Daemon already stopped\n";
		return;
	}

	shell_exec("$kill $pid");
	for($i=0;$i<6;$i++){
		$pid=$unix->PIDOF($cgrulesengd);
		if(!$unix->process_exists($pid)){
			break;
		}
	sleep(1);
	}	
	
	$pid=$unix->PIDOF($cgrulesengd);
	if(!$unix->process_exists($pid)){
		echo "Stopping cgroups.............: CGroup Rules Engine successfully stopped\n";
	}else{
		echo "Stopping cgroups.............: CGroup Rules Engine failed to stop\n";
	}
		
}

function TaskPidof($cmdline){
		if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
		$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("pgrep");
		if(!is_file($pgrep)){return array();}
		exec("$pgrep -l -f \"$cmdline\" 2>&1",$results);
		while (list ($index, $ligne) = each ($results)){
			if($GLOBALS["VERBOSE"]){echo "TaskPidof::".__LINE__.":: $ligne\n";}
			if(!preg_match("#^([0-9]+)\s+#", $ligne,$re)){continue;}
			if(preg_match("#^[0-9]+\s+.+?pgrep#", $ligne)){continue;}
			$pidf=$re[1];
			if($GLOBALS["VERBOSE"]){echo "TaskPidof::".__LINE__.":: $cmdline -> $pidf\n";}
			$pidR[$GLOBALS["CLASS_UNIX"]->PPID_OF($pidf)]=true;
		}
		if(!isset($pidR)){return array();}
		if(!is_array($pidR)){return array();}
		return $pidR;
	}

function TaskSave(){
	if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	load_family();
	$q=new mysql();	
	$echo=$pgrep=$GLOBALS["CLASS_UNIX"]->find_program("echo");
	$sql="SELECT cgroups_processes.process_name,cgroups_groups.groupname  FROM cgroups_processes,cgroups_groups WHERE cgroups_processes.groupid=cgroups_groups.ID ORDER BY process_name";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";die();}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["process_name"]=trim($ligne["process_name"]);
		$pids=TaskPidof($ligne["process_name"]);
		$groupname=$ligne["groupname"];
		if(count($pids)==0){continue;}
		
		reset($GLOBALS["CGROUPS_FAMILY"]);
		while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			
			if(!is_dir("/cgroups/$structure")){continue;}
			if(!is_dir("/cgroups/$structure/$groupname")){@mkdir("/cgroups/$structure/$groupname");}
			
			
			if(is_file("/cgroups/$structure/$groupname/tasks")){
				reset($pids);
				while (list ($pid, $none) = each ($pids)){
					if(!is_numeric($pid)){continue;}
					if($GLOBALS["VERBOSE"]){echo "/cgroups/$structure/$groupname/tasks -> $pid\n";}
					shell_exec("$echo $pid >/cgroups/$structure/$groupname/tasks >/dev/null 2>&1");
				}
			}
			
		}
	
	}	
	
}



function buildstats(){
	// default structure.
	
	$sock=new sockets();
	$cgroupsEnabled=$sock->GET_INFO("cgroupsEnabled");
	if(!is_numeric($cgroupsEnabled)){$cgroupsEnabled=0;}
	if($cgroupsEnabled==0){
		if(is_dir("/cgroups")){@rmdir("/cgroups");}
		if(is_dir("/cgroup")){@rmdir("/cgroup");}
		return;
	}
	$unix=new unix();
	$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$int=$unix->file_time_min($timefile);
	if($int<15){return;}
	@unlink($int);
	@file_put_contents($timefile, time());
	TaskSave();

	$q=new mysql();
	$array[]=array("structure"=>"cpuacct" ,"key"=>"cpuacct.usage");
	$array[]=array("structure"=>"memory" ,"key"=>"memory.memsw.max_usage_in_bytes");
	$array[]=array("structure"=>"memory" ,"key"=>"memory.usage_in_bytes");

	
	
	$prefix="INSERT INTO cgroups_stats (zmd5,zDate,structure,groupname,`key`,`value`) VALUES ";
	$date=date("Y-m-d H:i:s");
	
	
	while (list ($index, $keyARRAY) = each ($array)){
		$structure=$keyARRAY["structure"];
		$key=$keyARRAY["key"];
		if(is_file("/cgroups/$structure/$key")){
		$datas=trim(@file_get_contents("/cgroups/$structure/$key"));
		$zmd5=md5("$date{$datas}$key$structure");
		$ql[]="('$zmd5','$date','$structure','system','$key','$datas')";
		}
	}
	
	$q=new mysql();
	$sql="SELECT groupname  FROM cgroups_groups";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
		
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
			reset($array);
			$groupname=$ligne["groupname"];
			while (list ($index, $keyARRAY) = each ($array)){
				$structure=$keyARRAY["structure"];
				$key=$keyARRAY["key"];				
				if(is_file("/cgroups/$structure/$key")){
					$datas=trim(@file_get_contents("/cgroups/$structure/$groupname/$key"));
					$zmd5=md5("$date{$datas}$key$structure");
					$ql[]="('$zmd5','$date','$structure','$groupname','$key','$datas')";
				}			
			}
		
		}
		
		
	if(count($ql)>0){
		$sql=$prefix." " .@implode(",", $ql);
		$q->QUERY_SQL($sql,"artica_events");
	}
	
}
	
