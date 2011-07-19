<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');
include_once(dirname(__FILE__) . '/ressources/class.os.system.inc');
include_once(dirname(__FILE__) . '/ressources/class.sockets.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);die();}



$sql="SELECT * FROM computers_deploy_tasks WHERE task_type=1 AND status=1";
$q=new mysql();

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$GLOBALS[__FILE__]["TASKID"]=$ligne["ID"];
		$GLOBALS[__FILE__]["storage_id"]=$ligne["storage_id"];
		$GLOBALS[__FILE__]["group_id"]=$ligne["group_id"];
		echo "Find {$GLOBALS[__FILE__]["TASKID"]} task id\n";	
		if(EXPLODE_COMPUTERS()){
			setStatus(2);
			setProgress(1);
		}else{
			setStatus(-1);
			setProgress(0);
		}
	}

ScanComputers();

function setProgress($pource){
	$sql="UPDATE computers_deploy_tasks SET progress=$pource WHERE ID={$GLOBALS[__FILE__]["TASKID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql $q->mysql_error\n";}
	}
function setStatus($status){
	$sql="UPDATE computers_deploy_tasks SET status=$status WHERE ID={$GLOBALS[__FILE__]["TASKID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql $q->mysql_error\n";}
	}
function task_events($text){
	$sql="SELECT events FROM computers_deploy_tasks WHERE ID={$GLOBALS[__FILE__]["TASKID"]}";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$datas=unserialize(base64_decode($ligne["events"]));
	$text=date('m-d H:i:s')." $text";	
	echo $text."\n";	
	$text=$q->mysql_real_escape_string2($text);
	$text=htmlentities($text);	
	$datas[]="<div>$text</div>";
	$translate=base64_encode(serialize($datas));
	$sql="UPDATE computers_deploy_tasks SET events='$translate' WHERE ID={$GLOBALS[__FILE__]["TASKID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql $q->mysql_error\n";}
	}	

	
function EXPLODE_COMPUTERS(){
	$group=new groups($GLOBALS[__FILE__]["group_id"]);
	$q=new mysql();
	while (list ($num, $line) = each ($group->computers_array) ){
		$sql="INSERT INTO computers_deploy_tasks_sub
		(taskid,package_id,status,progress,uid)
		VALUES('{$GLOBALS[__FILE__]["TASKID"]}',
		'{$GLOBALS[__FILE__]["storage_id"]}',
		'1','5','$line');
		";
		echo "Find add new computer $line\n";	
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			task_events("Mysql error: $q->mysql_error $sql");
			return false;
		}
		
	}
	return true;
}


function ScanComputers(){
	$sql="SELECT * FROM computers_deploy_tasks WHERE task_type=1 AND status=2";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		echo "Starting remote install on computers for task {$GLOBALS[__FILE__]["TASKID"]}\n";
		ScanComputers_sub();
	}
	
}

function ScanComputers_sub(){
	$sql="SELECT * FROM computers_deploy_tasks_sub WHERE taskid={$GLOBALS[__FILE__]["TASKID"]} AND status=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$GLOBALS[__FILE__]["SUBTASKID"]=$ligne["ID"];
		$GLOBALS[__FILE__]["TASKID"]=$ligne["taskid"];
		$GLOBALS[__FILE__]["uid"]=$ligne["uid"];
		$GLOBALS[__FILE__]["PACKAGEID"]=$ligne["package_id"];
		task_events("Starting remote install on {$ligne["uid"]} subtask={$ligne["ID"]}");
		ScanComputers_deploy();
	}	
	
}

function ScanComputers_deploy(){
	$cmp=new computers($GLOBALS[__FILE__]["uid"]);
	$sock=new sockets();
	$unix=new unix();
	setComputerProgress(10);
	$hash=unserialize(base64_decode($sock->GET_INFO("GlobalNetAdmin")));
	$global_user=$hash["GLOBAL"]["username"];
	$global_password=$hash["GLOBAL"]["password"];	
	if($MinutesToWait==0){$MinutesToWait=5;}
	
	
	$sql="SELECT * FROM computers_storage WHERE ID='{$GLOBALS[__FILE__]["PACKAGEID"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	task_computers_events("Deploy {$ligne["PUBLISHER"]} {$ligne["NAME"]} {$ligne["version"]}");
	$source_path=$ligne["local_path"];
	$commandline=$ligne["command_line"];
	
	if(!is_file($source_path)){
		task_computers_events("Source $source_path no such file");
		task_events("{$ligne["uid"]} Failed");
		setComputerStatus(-1);
		setComputerProgress(100);
		return;
	}

	if($global_user<>null){
		$users[]=array($global_user,$global_password);
	}
	
	$ini=new Bs_IniHandler();
	$ini->loadString($cmp->ComputerCryptedInfos);
	$global_user=$ini->_params["ACCOUNT"]["USERNAME"];
	$global_password=$ini->_params["ACCOUNT"]["PASSWORD"];	

	if($global_user<>null){
		$users[]=array($global_user,$global_password);
	}	
	
	task_computers_events("using ". count($users)." potentials users");
	if(count($users)==0){
		task_computers_events("no administrator account set for this computer.");
		task_events("{$ligne["uid"]} Failed");
		setComputerStatus(-1);
		setComputerProgress(100);
		return;
	}
	
	if(!preg_match("#^[0-9]+\.[0-9]+\.[0-9]+#",$cmp->ComputerIP)){$cmp->ComputerIP=null;}
	$ip_src=$cmp->ComputerIP;
	$realname=$cmp->ComputerRealName;
	$cmp->ComputerIP=$unix->HostToIp($realname);
	
	if($cmp->ComputerIP==null){$cmp->ComputerIP=$ip_src;}
	task_computers_events("$realname resolved to $cmp->ComputerIP");
	
	setComputerProgress(30);
	$pp=new ping($cmp->ComputerIP);
	if(!$pp->Isping()){
		task_computers_events("Failed to ping  $realname ($cmp->ComputerIP), aborting process.");
		return false;		
	}	
	setComputerProgress(40);
	task_computers_events("Mount the admin$ to targeted $realname computer...");
	$mount=new mount();
	
	$mounted=false;
	while (list ($index, $hash_credentials) = each ($users) ){
		if($mount->MountComputerRemote($realname,$cmp->ComputerIP,$hash_credentials[0],$hash_credentials[1])){
			task_computers_events("Success connecting to the $realname computer");
			$mounted=true;
			$username=$hash_credentials[0];
			$password=$hash_credentials[1];
		}else{
			task_computers_events("Failed connecting to the $realname computer using {$hash_credentials[0]}");
		}
		
	}
	
	if(!$mounted){
		task_events("{$ligne["uid"]} Failed");
		setComputerStatus(-1);
		setComputerProgress(100);
		return false;
	}
	
	$mounted_path=$mount->mount_point;
	@mkdir($mounted."/artica_remote_install",null,true);
	if(!is_dir($mounted."/artica_remote_install")){
		task_computers_events("$mounted/artica_remote_install permission denied");
		shell_exec("umount -l $mounted_path");
		setComputerStatus(-1);
		setComputerProgress(100);
		return false;	
	}
	
	if(!@copy($source_path,$mounted."/artica_remote_install/".basename($source_path))){
		task_computers_events("$mounted/artica_remote_install permission denied will copy source file");
		shell_exec("umount -l $mounted_path");
		setComputerStatus(-1);
		setComputerProgress(100);
		return false;			
	}
	
	$execute_path="C:\\tmp\\artica_remote_install\\".basename($source_path)." $commandline";
	
	
	task_computers_events("runtask:: execute $execute_path on target computer");
	$logfile="/tmp/".md5($cmp->ComputerIP);
	$cmd="/usr/bin/winexe -d 2 --user=$username --password=$password --interactive=1 --runas=$username%$password --uninstall //$cmp->ComputerIP \"$execute_path\" >$logfile &";
	exec($cmd);
	
	if(!WaitWinexeForSecondes($MinutesToWait,$cmp->ComputerIP)){
		events("runtask:: Time-out !!");
		winexe_umount($workingDirectory);
		return false;
	}
	
	if($ExecuteAfter<>null){
		events("runtask:: execute $ExecuteAfter on target computer");
		$cmd="/usr/bin/winexe -d 2 --user=$username --password=$password --interactive=1 --runas=$username%$password --uninstall //$cmp->ComputerIP $ExecuteAfter >$logfile &";
		exec($cmd);
		if(!WaitWinexeForSecondes($MinutesToWait,$cmp->ComputerIP)){
			events("runtask:: execute $ExecuteAfter time-out on target computer");
		}
	}
	
	winexe_umount($workingDirectory);
	events("runtask:: Done...");
	return true;	
	
}
function WaitWinexeForSecondes($maxsecondes,$computerIP,$ID){
	
	if($maxsecondes<60){
		if($maxsecondes>1){
			$maxsecondes=$maxsecondes*60;
		}
	}
	
	$computerIP=str_replace(".","\.",$computerIP);
	$pattern="winexe\s+-d\s+[0-9]+\s+--user.+$computerIP";
	if($_GET["DEBUG"]){writelogs("WaitWinexeForSecondes:: search pattern $pattern for max $maxsecondes seconds",__FUNCTION__,__FILE__,_);}

	$t=1;
	for($i=1;$i<=$maxsecondes;$i++){
		if(!ProcessExistsBypattern($pattern)){
			if($_GET["DEBUG"]){writelogs("WaitWinexeForSecondes -> ProcessExistsBypattern $pattern not found in pgrep",__FUNCTION__,__FILE__,__LINE__);}
			return true;
		}
		sleep(1);
		$t=$t+1;
		if($t>60){
			$displaymin=($maxsecondes/60);
			writelogs("WaitWinexeForSecondes:: 1mn passed... waiting.. max=$displaymin",__FUNCTION__,__FILE__,__LINE__);
			$t=1;
		}
	}
	
	if(ProcessExistsBypattern($pattern)){
		writelogs("WaitWinexeForSecondes:: Stopping execution...",__FUNCTION__,__FILE__,__LINE__);
		$array=ProcessListBypattern($pattern);
		while (list ($index, $pid) = each ($array) ){
			if(trim($pid)==null){continue;}
			writelogs("WaitWinexeForSecondes:: stopping remote task PID $pid",__FUNCTION__,__FILE__,__LINE__);
			if($pid>0){exec("/bin/kill -9 $pid");}
		}
		
		return false;
	}
	
}

function setComputerProgress($pource){
	$sql="UPDATE computers_deploy_tasks_sub SET progress=$pource WHERE ID={$GLOBALS[__FILE__]["SUBTASKID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql $q->mysql_error\n";}
	}
function setComputerStatus($status){
	$sql="UPDATE computers_deploy_tasks_sub SET status=$status WHERE ID={$GLOBALS[__FILE__]["SUBTASKID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql $q->mysql_error\n";}
	}
function task_computers_events($text){
	$sql="SELECT events FROM computers_deploy_tasks_sub WHERE ID={$GLOBALS[__FILE__]["SUBTASKID"]}";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$datas=unserialize(base64_decode($ligne["events"]));
	$text=date('m-d H:i:s')." $text";	
	echo $text."\n";	
	$text=$q->mysql_real_escape_string2($text);
	$text=htmlentities($text);	
	$datas[]="<div>$text</div>";
	$translate=base64_encode(serialize($datas));
	$sql="UPDATE computers_deploy_tasks_sub SET events='$translate' WHERE ID={$GLOBALS[__FILE__]["SUBTASKID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$sql $q->mysql_error\n";}
	}		

	
?>