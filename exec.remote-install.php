<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');
include_once(dirname(__FILE__) . '/ressources/class.os.system.inc');
include_once(dirname(__FILE__) . '/ressources/class.sockets.inc');
include_once(dirname(__FILE__).'/ressources/class.mount.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}


if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


if($argv[1]=="--verbose"){$_GET["DEBUG"]=true;}

$array=ProcessListBypattern("winexe\s+-d\s+[0-9]+\s+--user");
if(is_array($array)){
		while (list ($index, $pid) = each ($array) ){
			if(trim($pid)==null){continue;}
			events("starting:: stopping winexe remote task PID $pid");
			if($pid>0){exec("/bin/kill -9 $pid");}
		}}
		

ParseTasks();

function ParseTasks(){
	$sql="SELECT  ID FROM deploy_tasks WHERE executed=0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		events("Running task {$ligne["ID"]}");
		
		$runed=runtask($ligne["ID"]);
		if(is_file($_GET["logcomputer"])){$logcomputer=@file_get_contents($_GET["logcomputer"]);}
		@unlink($_GET["logcomputer"]);
		$logcomputer=addslashes(nl2br(htmlspecialchars($logcomputer)));
		
		if($runed){
			$sql="UPDATE deploy_tasks SET executed=1, results='$logcomputer' WHERE ID={$ligne["ID"]}";
			$q->QUERY_SQL($sql,"artica_backup");
		}else{
			if(is_file($_GET["logcomputer"])){$logcomputer=@file_get_contents($_GET["logcomputer"]);}
			$sql="UPDATE deploy_tasks SET executed=2,results='$logcomputer' WHERE ID={$ligne["ID"]}";
			$q->QUERY_SQL($sql,"artica_backup");
		}
		
		events("Running task {$ligne["ID"]} finish");
		
	}
	
	
}

function events($text){
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/remote-install.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="[$pid] $date $text\n";
		if($_GET["DEBUG"]){echo $line;}
		@fwrite($f,$line);
		@fclose($f);

		if($_GET["logcomputer"]<>null){
			$f = @fopen($_GET["logcomputer"], 'a');
			@fwrite($f,$line);
			@fclose($f);
		}
		
		
		}


function runtask($id){
	$sql="SELECT files_id,computer_id,commandline,username,password,debug_mode FROM deploy_tasks WHERE ID=$id";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ldap=new clladp();
	$commandline=trim($ligne["commandline"]);
	$username=$ligne["username"];
	$password=$ligne["password"];
	$computerid=$ligne["computer_id"];
	$files_id=$ligne["files_id"];
	$debug_mode=$ligne["debug_mode"];
	
	if(strpos($computerid,'$')==0){$computerid="$computerid$";}
	$cmp=new computers($computerid);
	
	events("runtask:: debug mode=$debug_mode computerid=$computerid $cmp->ComputerRealName"); 
	
	
	$sql="SELECT * FROM files_storage WHERE id_files='$files_id'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$filename=$ligne["filename"];	
	$bin_data=$ligne["bin_data"];
	$OCS_PACKAGE=$ligne["OCS_PACKAGE"];
	$MinutesToWait=$ligne["MinutesToWait"];
	if($MinutesToWait==0){$MinutesToWait=5;}
	$package_commandline=$ligne["commandline"];
	if($commandline==null){$commandline=$package_commandline;}
	$file_dir=str_replace(" ","",$filename);
	$file_dir=str_replace(".","",$file_dir);
	$ExecuteAfter=$ligne["ExecuteAfter"];
	
	$_GET["logcomputer"]=OS_FILE_TEMP();
	if($debug_mode==1){
		events("runtask:: Debug mode enabled");
		$_GET["DEBUG"]=true;
		$_GET["FILE_DEBUG"][]=$_GET["logcomputer"];
		$_GET["FILE_DEBUG"][]="/var/log/artica-postfix/remote-install.debug";
	
	}
	
	
	if(trim($username)==null){
		$ini=new Bs_IniHandler();
		$ini->loadString($cmp->ComputerCryptedInfos);
		$username=$ini->_params["ACCOUNT"]["USERNAME"];
		$password=$ini->_params["ACCOUNT"]["PASSWORD"];
	}
	
	
	if($OCS_PACKAGE==1){$commandline=OCS_COMMANDLINE();}


	
	if(!is_file("/usr/bin/winexe")){
		events("unable to stat /usr/bin/winexe");
		return false;
	}
	
	if($filename==null){
		events("runtask:: Execute remote task for $cmp->ComputerRealName ($cmp->ComputerIP) failed to get file name for $files_id ($filename)");
		return false;
	}
	
	
	if($bin_data==null){
		events("runtask:: failed to get binary data on the server for package number $files_id");
		return false;	
	}
	$cmp=new computers($computerid);
	events("runtask:: Execute remote task for $cmp->ComputerRealName ($cmp->ComputerIP)");
	
	
	$pp=new ping($cmp->ComputerIP);
	if(!$pp->Isping()){
		events("runtask:: Failed to ping  $cmp->ComputerRealName ($cmp->ComputerIP), aborting process.");
		return false;		
	}
	events("runtask:: pinging $cmp->ComputerIP OK");
	$mount=new mount("/var/log/artica-postfix/remote-install.debug");
	if(!$mount->ismounted("/opt/artica/mounts/remote-install")){
	@mkdir("/opt/artica/mounts/remote-install",0755,true);
		events("runtask:: mounting c$ on remote computer...");
		$cmd="/bin/mount -t smbfs -o username=$username,password=$password //$cmp->ComputerIP/c$ /opt/artica/mounts/remote-install";
		$cmd_logs=str_replace($password,"[password]",$cmd);
		events($cmd_logs);
		system("/bin/mount -t smbfs -o username=$username,password=$password //$cmp->ComputerIP/c$ /opt/artica/mounts/remote-install >{$_GET["logcomputer"]}.mount 2>&1");
		events("runtask:: mount logs {$_GET["logcomputer"]}.mount :" . filesize("{$_GET["logcomputer"]}.mount")." bytes");
		$tbl=explode("\n",@file_get_contents("{$_GET["logcomputer"]}.mount"));
		@unlink("{$_GET["logcomputer"]}.mount");
		if(is_array($tbl)){
			while (list ($index, $line) = each ($tbl) ){
				if(trim($line)==null){continue;}
				events("$line");
			}
		}
		
		
	}else{
		events("runtask:: Already mounted");
	}
	if(!$mount->ismounted("/opt/artica/mounts/remote-install")){
		events("runtask:: mount class report unmounted... Unable to mount on target computer");
		return false;
	}
	
	$workingDirectory="/opt/artica/mounts/remote-install/tmp/$file_dir";
	
	if(preg_match("#\.zip$#",$filename)){
		$zipped=true;
		events("runtask:: Zipped compressed, extract file and execute $commandline");
		$file_dir=ExtractFile($bin_data,$workingDirectory,$filename,$file_dir);
		if($file_dir==null){
			events("runtask:: Extracting failed...");
			return false;
		}
	}
	else{
		events("runtask:: Copy file...");
		if(!CopyFile($workingDirectory,$filename,$bin_data)){
		  events("runtask:: Unable to save //$cmp->ComputerIP/c$/tmp/$file_dir/$filename on target computer ");
		  return false;
		 }
	}
	
	if($zipped){
		$execute_path="C:\\tmp\\$file_dir\\$commandline";
	}else{
		$execute_path="C:\\tmp\\$file_dir\\$filename $commandline";
	}

	events("runtask:: execute $execute_path on target computer");
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

function winexe_umount($workingDirectory){
	events("unmounting and cleaning....");
	shell_exec("/bin/rm -rf $workingDirectory");
	events(@file_get_contents($logfile));
	@unlink($logfile);	
	exec("umount -l /opt/artica/mounts/remote-install");
	$mount=new mount();
	if(!$mount->ismounted("/opt/artica/mounts/remote-install")){
		events("unmounted ....");
	}	
	
}

function WaitWinexeForSecondes($maxsecondes,$computerIP){
	
	if($maxsecondes<60){
		if($maxsecondes>1){
			$maxsecondes=$maxsecondes*60;
		}
	}
	
	$computerIP=str_replace(".","\.",$computerIP);
	$pattern="winexe\s+-d\s+[0-9]+\s+--user.+$computerIP";
	if($_GET["DEBUG"]){events("WaitWinexeForSecondes:: search pattern $pattern for max $maxsecondes seconds");}

	$t=1;
	for($i=1;$i<=$maxsecondes;$i++){
		if(!ProcessExistsBypattern($pattern)){
			if($_GET["DEBUG"]){events("WaitWinexeForSecondes -> ProcessExistsBypattern $pattern not found in pgrep");}
			events("Execution stopped...");
			return true;
		}
		sleep(1);
		$t=$t+1;
		if($t>60){
			$displaymin=($maxsecondes/60);
			events("WaitWinexeForSecondes:: 1mn passed... waiting.. max=$displaymin");
			$t=1;
		}
	}
	
	if(ProcessExistsBypattern($pattern)){
		events("WaitWinexeForSecondes:: Stopping execution...");
		$array=ProcessListBypattern($pattern);
		while (list ($index, $pid) = each ($array) ){
			if(trim($pid)==null){continue;}
			events("WaitWinexeForSecondes:: stopping remote task PID $pid");
			if($pid>0){exec("/bin/kill -9 $pid");}
		}
		
		return false;
	}
	
}

function CopyFile($workingDirectory,$filename,$bin_data){
	
	@mkdir($workingDirectory,0755,true);
	events("writing directory $workingDirectory filename=$filename");
	@file_put_contents("$workingDirectory/$filename",$bin_data);
	if(!is_file("$workingDirectory/$filename")){
		events("could not stat $workingDirectory/$filename");
		return false;
	}

	return true;
	
}


function ExtractFile($bin_data,$workingDirectory,$filename,$file_dir){
	if(!is_file("/usr/bin/unzip")){
		events("Extract:: Unable to stat /usr/bin/unzip");
		return false;
	}
	
	@mkdir($workingDirectory,0755,true);
	$tmpfile=OS_FILE_TEMP();
	events("Extract:: save on file system $tmpfile");
	@file_put_contents("$tmpfile",$bin_data);
	events("Extract:: decompress $tmpfile");
	@mkdir($workingDirectory);
	system("unzip $tmpfile -d $workingDirectory");
	@unlink($tmpfile);
	$array=ListOnlyDirectories($workingDirectory);
	if($array[0]<>null){
		return "$file_dir\\{$array[0]}";
	}
	return $file_dir;
		
	
	
	
}

function OCS_COMMANDLINE(){
	$sock=new sockets();
	$ocswebservername=$sock->GET_INFO("ocswebservername");
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	if($ocswebservername==null){$ocswebservername="ocs.localhost.localdomain";}
	$cmdline="OcsAgentSetup.exe /S /NP /DEBUG /NOW /SERVER:$ocswebservername /PNUM:$ApacheGroupWarePort";
	return $cmdline;
	
}


?>