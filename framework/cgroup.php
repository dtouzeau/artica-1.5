<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["get-cgroups-family"])){extract_family();exit;}
if(isset($_GET["ProcessExplore"])){ProcessExplore();exit;}
if(isset($_GET["set-backup-server"])){save_client_server();exit;}
if(isset($_GET["runingplist"])){ProcessExploreCgroups();exit;}
if(isset($_GET["ApplyCgroupConf"])){ApplyCgroupConf();exit;}
if(isset($_GET["restart"])){restart();exit;}
if(isset($_GET["kill-proc"])){kill_proc();exit;}
if(isset($_GET["mv-def-proc"])){mv_def_proc();exit;}
if(isset($_GET["mv-all-def-proc"])){mv_all_def_proc();exit;}
if(isset($_GET["kill-all-procs"])){kill_proc_all();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["is-cpu-rt"])){is_cpu_rt();exit;}




while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}
writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();

function extract_family(){
	$f=explode("\n", @file_get_contents("/proc/cgroups"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^([a-z\_]+)\s+#", $ligne,$re)){
			$array[$re[1]]=true;
		}
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
}


function is_cpu_rt(){
	if(!is_file("/cgroups/cpu/cpu.rt_period_us")){
		echo "<articadatascgi>FALSE</articadatascgi>";
		return;	
	}
	echo "<articadatascgi>TRUE</articadatascgi>";
}

function ProcessExplore(){
	$unix=new unix();
	$ps=$unix->find_program("ps");
	$cmd=trim("$ps -eo user,vsize,pcpu,%mem,cgroup,args --no-headers -w --sort -pcpu 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	
}
function ProcessExploreCgroups(){
	$unix=new unix();
	$ps=$unix->find_program("ps");
	$grep=$unix->find_program("grep");
	$filepath=ProcessExploreCgroupsGetPath($_GET["runingplist"]);
	if(!is_file($filepath)){
		writelogs_framework("ProcessExploreCgroupsGetPath:: return no path for {$_GET["runingplist"]} \"$filepath\" no such file",__FUNCTION__,__FILE__,__LINE__);
		echo "<articadatascgi>". base64_encode(serialize(array()))."</articadatascgi>";
		return ;
	}
	$f=explode("\n", @file_get_contents($filepath));
	if(count($f)>0){
		$cmd="$ps u -p  \"".trim(@implode(" ", $f))."\" --no-headers -w --sort -pcpu";
		exec($cmd,$results);
		writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	}else{
		echo "<articadatascgi>". base64_encode(serialize(array()))."</articadatascgi>";
	}	
	
}

function ProcessExploreCgroupsGetPath($groupname){
	if(is_file("/cgroups/cpuacct/$groupname/tasks")){return "/cgroups/cpuacct/$groupname/tasks";}
	writelogs_framework("/cgroups/cpuacct/$groupname/task no such file",__FUNCTION__,__FILE__,__LINE__);
	
	if(is_file("/cgroups/cpuset/$groupname/tasks")){return "/cgroups/cpuset/$groupname/tasks";}
	writelogs_framework("/cgroups/cpuset/$groupname/task no such file",__FUNCTION__,__FILE__,__LINE__);
	
	
}

function kill_proc(){
	$unix=new unix();
	$kill=$unix->find_program("kill");
	$cmd="$kill -9 {$_GET["kill-proc"]}";
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}

function ApplyCgroupConf(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.cgroups.php --reload >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}

function restart(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.cgroups.php --restart >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function kill_proc_all(){
	$kill=$unix->find_program("kill");
	$group=$_GET["kill-all-procs"];
	$tasks=explode("\n", @file_get_contents("/cgroups/cpuset/{$_GET["mv-all-def-proc"]}/tasks"));
	writelogs_framework(count($tasks)." tasks for /cgroups/cpuset/{$_GET["mv-all-def-proc"]}/tasks",__FUNCTION__,__FILE__,__LINE__);
	if(count($tasks)>0){
		$cmd="$kill -9 ".trim(@implode(" ", $tasks));
		writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);			
	}
	
}

function mv_all_def_proc(){
	$unix=new unix();
	$echo=$unix->find_program("echo");
	$f=explode("\n", @file_get_contents("/proc/cgroups"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^([a-z\_]+)\s+#", $ligne,$re)){
			if($re[1]=="net_cls"){continue;}
			if($re[1]=="freezer"){continue;}
			if($re[1]=="devices"){continue;}
			$GLOBALS["CGROUPS_FAMILY"][$re[1]]=true;
		}
	}
	
	$tasks=explode("\n", @file_get_contents("/cgroups/cpuset/{$_GET["mv-all-def-proc"]}/tasks"));
	writelogs_framework(count($tasks)." tasks for /cgroups/cpuset/{$_GET["mv-all-def-proc"]}/tasks",__FUNCTION__,__FILE__,__LINE__);
	
	
	while (list ($num, $pid) = each ($tasks)){
		if(!is_numeric($pid)){continue;}
		reset($GLOBALS["CGROUPS_FAMILY"]);
		while (list ($structure, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
			if(is_file("/cgroups/$structure/tasks")){
				$cmd="$echo $pid >  /cgroups/$structure/tasks";
				shell_exec($cmd);
				writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
			}
		}
	}
	
	
	
}

function status() {
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.status.php --cgroups";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
	
	
}



function mv_def_proc(){
	$unix=new unix();
	$echo=$unix->find_program("echo");
	$f=explode("\n", @file_get_contents("/proc/cgroups"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^([a-z\_]+)\s+#", $ligne,$re)){
			if($re[1]=="net_cls"){continue;}
			if($re[1]=="freezer"){continue;}
			if($re[1]=="devices"){continue;}
			$GLOBALS["CGROUPS_FAMILY"][$re[1]]=true;
		}
	}

	while (list ($num, $ligne) = each ($GLOBALS["CGROUPS_FAMILY"])){
		if(is_file("/cgroups/$num/tasks")){
			$cmd="$echo {$_GET["mv-def-proc"]} >  /cgroups/$num/tasks";
			shell_exec($cmd);
			writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
		}
	}
	
}

