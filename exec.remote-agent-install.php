<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.os.system.inc');
include_once(dirname(__FILE__) . '/ressources/class.sockets.inc');
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}


if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}



ParseTasks();

function ParseTasks(){
	$sql="SELECT  ID,computer_id FROM deploy_tasks WHERE executed=0 AND TASK_TYPE='OCS_AGENT'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$uid=$ligne["computer_id"];
		writelogs("Running task {$ligne["ID"]}",__FUNCTION__,__FILE__,__LINE__);
		$sql="UPDATE deploy_tasks SET executed=1 WHERE ID={$ligne["ID"]}";
		$q->QUERY_SQL($sql,"artica_backup");
		$runed=runtask($uid,$ligne["ID"]);
		
	}
	
}

function runtask($uid,$ID){
	Levents("Starting deploy remote agent on $uid, task id $ID",$ID,5,"executing",__FUNCTION__,__LINE__);
	$sock=new sockets();
	$unix=new unix();
	$winexe=$unix->find_program('winexe');
	$winexe_no_errors["smb_raw_read_recv"]=true;
	$winexe_no_errors["on_ctrl_pipe_error"]=true;
	$ocs=new ocs();
	if($winexe==null){
		Levents("unable to stat winexe program",$ID,110,"failed",__FUNCTION__,__LINE__);
		return false;	
	}
	
	$ocsInventoryagntWinVer=$sock->getFrameWork("cmd.php?ocsInventoryagntWinVer=yes");
	Levents("Remote agent version $ocsInventoryagntWinVer",$ID,6,"executing",__FUNCTION__,__LINE__);
	
	if($ocsInventoryagntWinVer==null){
		Levents("unable to stat OCS agent windows version",$ID,110,"failed",__FUNCTION__,__LINE__);
		return false;
	}
	
	$package_source="/opt/artica/install/sources/fusioninventory/fusioninventory-agent_windows-i386-$ocsInventoryagntWinVer.exe";
	Levents("Remote agent package fusioninventory-agent_windows-i386-$ocsInventoryagntWinVer.exe",$ID,7,"executing",__FUNCTION__,__LINE__);
	
	$cmp=new computers($uid);
	Levents("runtask:: Execute remote task for $cmp->ComputerRealName ($cmp->ComputerIP)",$ID,7,"executing",__FUNCTION__,__LINE__);
	
	//if(!PREG_BACKTRACK_LIMIT_ERROR)
	
	if(!preg_match("#^[0-9]+\.[0-9]+\.[0-9]+#",$cmp->ComputerIP)){$cmp->ComputerIP=null;}
	$ipsrc=$cmp->ComputerIP;
	
	
	$cmp->ComputerIP=$unix->HostToIp($cmp->ComputerRealName);
	
	if($cmp->ComputerIP==null){$cmp->ComputerIP=$ipsrc;}
	
	if($cmp->ComputerIP==null){
		Levents("Failed to resolve computer name  $cmp->ComputerRealName aborting process.",$ID,110,"failed",__FUNCTION__,__LINE__);
		return false;
	}
	
	
	$pp=new ping($cmp->ComputerIP);
	if(!$pp->Isping()){
		Levents("Failed to ping  $cmp->ComputerRealName ($cmp->ComputerIP), aborting process.",$ID,110,"failed",__FUNCTION__,__LINE__);
		return false;		
	}
	
	Levents("pinging $cmp->ComputerIP OK",$ID,15,"starting",__FUNCTION__,__LINE__);
	
	$hash=unserialize(base64_decode($sock->GET_INFO("GlobalNetAdmin")));
	$page=CurrentPageName();
	$global_user=$hash["GLOBAL"]["username"];
	$global_password=$hash["GLOBAL"]["password"];
	
	$ini=new Bs_IniHandler();
	$ini->loadString($cmp->ComputerCryptedInfos);
	$username_standard=$ini->_params["ACCOUNT"]["USERNAME"];
	$password_standard=$ini->_params["ACCOUNT"]["PASSWORD"];	
	$MountComputerRemote=false;
	if($global_user<>null){	
		Levents("mounting using $global_user",$ID,20,"connect",__FUNCTION__,__LINE__);
		$MountComputerRemote=MountComputerRemote($cmp->uid,$cmp->ComputerIP,$global_user,$global_password,$package_source);
		if(!$MountComputerRemote){
			if($username_standard<>null){
				$global_user=$username_standard;
				$global_password=$password_standard;
			}
		}
	}
		
	if(!$MountComputerRemote){
		if($username_standard<>null){
			Levents("mounting using $username_standard",$ID,20,"connect",__FUNCTION__,__LINE__);
			$MountComputerRemote=MountComputerRemote($cmp->uid,$cmp->ComputerIP,$username_standard,$password_standard,$package_source);
		}
	}
	
	while (list ($index, $line) = each ($GLOBALS[$cmp->uid]["EVENTS"]) ){
		if(trim($line)==null){continue;}
		Levents($line,$ID,50,"connecting",__FUNCTION__,__LINE__);
	}
	
	if(!$MountComputerRemote){
		Levents("MountComputerRemote() -> failed",$ID,50,"failed",__FUNCTION__,__LINE__);
		return false;
	}
	
	unset($GLOBALS[$cmp->uid]["EVENTS"]);
	
	$uri=$ocs->GET_OCSSERVER_URI();
	Levents("OCS servers is set has $uri, working path={$GLOBALS["LOCAL_FILE_PATH"]}",$ID,50,"installing",__FUNCTION__,__LINE__);
	$execute_path=$GLOBALS["LOCAL_FILE_PATH"]." /S /server=$uri /no-ssl-check /rpc-trust-localhost /runnow";
	
	
	Levents("$execute_path on target computer",$ID,80,"remote_install",__FUNCTION__,__LINE__);
	$logfile="/tmp/".md5($cmp->ComputerIP);
	$cmd="$winexe -d 2 --user=$global_user --password=$global_password --interactive=0 --runas=$global_user%$global_password --uninstall //$cmp->ComputerIP \"$execute_path\" >$logfile &";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	system($cmd);	
	if(!WaitWinexeForSecondes(5,$cmp->ComputerIP)){
			Levents("Execute time-out after 5 minutes on target computer",$ID,110,"failed",__FUNCTION__,__LINE__);
			return false;
	}
	
	$results=@explode("\n",@file_get_contents($logfile));
	$type="installed";
	$mypourc=100;
	while (list ($index, $line) = each ($results) ){
		if(trim($line)==null){continue;}
		
		if(preg_match("#Sending command: set runas#",$line)){continue;}
		
		if(preg_match("#ERROR:\s+(.+?)\s+#",$line,$re)){
			if(!$winexe_no_errors[trim($re[1])]){$type=failed;$mypourc=110;}
			}
		Levents($line,$ID,$mypourc,$type,__FUNCTION__,__LINE__);
	}	
	@unlink($logfile);
	unset($results);
	exec("umount -l {$GLOBALS["MOUNT_POINT"]}",$results);
	while (list ($index, $line) = each ($results) ){
		if(trim($line)==null){continue;}
		Levents($line,$ID,$mypourc,$type,__FUNCTION__,__LINE__);
	}	
	
	return true;	
}


function Levents($event,$ID,$pourc,$progress,$function,$line){
	writelogs($event,$function,__FILE__,$ligne);
	$event=date("H:i:s").":: $event";
	
	$sql="SELECT results FROM deploy_tasks WHERE ID=$ID";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$event=$ligne["results"]."\n".$event;
	$sql="UPDATE deploy_tasks SET pourcent=$pourc,PROGRESS='$progress',`results`='$event' WHERE ID=$ID";
	$q->QUERY_SQL($sql,"artica_backup");
}

function MountComputerRemote($uid,$ip,$username,$password,$sourcefile){
	$uidPoint=str_replace("$","",$uid);
	$sourcefile_base=basename($sourcefile);
	$GLOBALS["MOUNT_POINT"]="/opt/artica/mounts/remote-$uidPoint";
	@mkdir($GLOBALS["MOUNT_POINT"],null,true);
	$unix=new unix();
	$mount_bin=$unix->find_program("mount");	
	if($mount_bin==null){
		$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: mount program, no such file";
		return false;
	}
	
	
	$mount=new mount("/var/log/artica-postfix/remote-install-$uidPoint.debug");
	if(!$mount->ismounted($GLOBALS["MOUNT_POINT"])){
		$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: Mount point is not mounted, mount it";
		$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: Mount c$ on $uidPoint [$ip]";
		$cmd="$mount_bin -t smbfs -o username=$username,password=$password //$ip/c$ {$GLOBALS["MOUNT_POINT"]} 2>&1";
		exec($cmd,$results);
		while (list ($index, $line) = each ($results) ){if(trim($line)==null){continue;}$GLOBALS[$uid]["EVENTS"][]=$line;}		
		
	}else{
		$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: Mount point is mounted";
		
	}
	
	if(!$mount->ismounted($GLOBALS["MOUNT_POINT"])){return false;}
	$GLOBALS["MOUNTED_PATH"]="{$GLOBALS["MOUNT_POINT"]}/tmp/ocs-agent";
	if(!is_dir($GLOBALS["MOUNTED_PATH"])){@mkdir($GLOBALS["MOUNTED_PATH"],null,true);	}
	if(!is_dir($GLOBALS["MOUNTED_PATH"])){
		$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: c:\tmp\ocs-agent, permission denied";
		exec("umount -l $mountpoint");
		return false;
	}
	
	unset($results);
	
	$GLOBALS["WORKING_FILE_PATH"]="{$GLOBALS["MOUNTED_PATH"]}/$sourcefile_base";
	writelogs("WORKING_FILE_PATH={$GLOBALS["WORKING_FILE_PATH"]}",__FUNCTION__,__FILE__,__LINE__);
	$cmd="/bin/cp -f $sourcefile {$GLOBALS["WORKING_FILE_PATH"]} 2>&1";
	writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){if(trim($line)==null){continue;}$GLOBALS[$uid]["EVENTS"][]=$line;}
	if(!is_file("$workingDirectory/$sourcefile_base")){
		$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: {$GLOBALS["MOUNTED_PATH"]}\".$sourcefile_base, permission denied";
		exec("umount -l {$GLOBALS["MOUNT_POINT"]}");
		return false;
	}
	
	$GLOBALS["LOCAL_FILE_PATH"]="c:\\tmp\\ocs-agent\\$sourcefile_base";
	$GLOBALS[$uid]["EVENTS"][]="[$username/$ip]:: {$GLOBALS["MOUNTED_PATH"]}\{$sourcefile_base}, success";
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











?>