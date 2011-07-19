<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if($argv[1]=="--build"){build();die();}
if($argv[1]=="--list"){print_r(buildlist());die();}
if($argv[1]=="--maintenance"){maintenance();die();}
if($argv[1]=="--nat-ports"){nat_ports();die();}
if($argv[1]=="--nat-rebuild"){nat_ports($argv[2]);die();}



function build(){

$conf[]="<?php";
$conf[]="define('artica_vbox_lang',PhpVirtualBoxDetectLang());";
$conf[]="include_once('/usr/share/artica-postfix/ressources/class.templates.inc');";
$conf[]="include_once('/usr/share/artica-postfix/ressources/class.ldap.inc');";
$conf[]="include_once('/usr/share/artica-postfix/ressources/class.users.menus.inc');";
$conf[]="\$user=new usersMenus();";
$conf[]="if(!\$user->AsVirtualBoxManager){";
$conf[]="	die();exit();";
$conf[]="	}";
$conf[]="/*";
$conf[]=" * phpVirtualBox configuration";
$conf[]=" *";
$conf[]=" */";
$conf[]="class phpVBoxConfig {";
$conf[]="";
$conf[]="/* Username / Password for system user that runs Virtualbox */";
$conf[]="var \$username = '';";
$conf[]="var \$password = '';";
$conf[]="var \$location = 'http://127.0.0.1:18083/';";
$conf[]="";
$conf[]="";
$conf[]="";
$conf[]="/* See languages folder for more language options */";
$conf[]="var \$language = artica_vbox_lang;";
$conf[]="";
$conf[]="";
$conf[]="";
$conf[]="/*";
$conf[]=" *";
$conf[]=" * Not-so-common options / tweeking";
$conf[]=" *";
$conf[]=" */";
$conf[]="";
$conf[]="// Default host/ip to use for RDP";
$conf[]="//var \$rdpHost = '192.168.1.40';";
$conf[]="";
$conf[]="/*";
$conf[]="Allow to prompt deletion harddisk files on removal from Virtual Media Manager.";
$conf[]="If this is not set, files are always kept. If this is set, you will be PROMPTED";
$conf[]="to decide whether or not you would like to delete the harddisk file(s) when you";
$conf[]="remove a harddisk from virtual media manager. You may still choose not to delete";
$conf[]="the file when prompted.";
$conf[]="*/";
$conf[]="var \$deleteOnRemove = true;";
$conf[]="";
$conf[]="/*";
$conf[]=" * File / Folder browser settings";
$conf[]=" */";
$conf[]="";
$conf[]="// Restrict file types";
$conf[]="var \$browserRestrictFiles = '.iso,.vdi,.vmdk,.img,.bin,.vhd,.hdd,.ovf,.ova';";
$conf[]="";
$conf[]="// Restrict locations / folders";
$conf[]="var \$browserRestrictFolders = '/home,/mnt,/media,/root/.VirtualBox';";
$conf[]="";
$conf[]="// Force use of local, webserver based file browser instead of going through vboxwebsrv";
$conf[]="#var \$browserLocal = true;";
$conf[]="";
$conf[]="// Disable file / folder browser.";
$conf[]="#var \$browserDisable = true;";
$conf[]="";
$conf[]="/*";
$conf[]=" * Misc";
$conf[]=" */";
$conf[]="";
$conf[]="/* Disable any of phpVirtualBox's main tabs */";
$conf[]="#var \$disableTabVMSnapshots = true; // Snapshots tab";
$conf[]="#var \$disableTabVMConsole = true; // Console tab";
$conf[]="#var \$disableTabVMDescription = true; // Description tab";
$conf[]="";
$conf[]="/* Custom screen resolutions for console tab */";
$conf[]="#var \$consoleResolutions = '640x480,800x600,1024x768';";
$conf[]="";
$conf[]="/* Max number of network cards per VM. Do not set above VirtualBox's limit (typically 8) or below 1 */";
$conf[]="var \$nicMax = 4;";
$conf[]="";
$conf[]="/* Enable Acceleration configuration (normally hidden in the VirtualBox GUI) */";
$conf[]="var \$enableAccelerationConfig = true;";
$conf[]="";
$conf[]="/* Custom VMList sort function in JavaScript */";
$conf[]="/* This places running VMs at the top of the list";
$conf[]="var \$vmListSort = 'function(a,b) {";
$conf[]="	if(a.state == \"Running\" && b.state != \"Running\") return -1;";
$conf[]="	if(b.state == \"Running\" && a.state != \"Running\") return 1;";
$conf[]="	return strnatcasecmp(a.name,b.name);";
$conf[]="}';";
$conf[]="*/";
$conf[]="";
$conf[]="";
$conf[]="/*";
$conf[]=" * Cache tweeking.";
$conf[]=" *";
$conf[]=" * NOT a good idea to set any of these unless asked to do so.";
$conf[]=" */";
$conf[]="#var \$cachePath = '/tmp';";
$conf[]="";
$conf[]="/*";
$conf[]=" * Cache timings";
$conf[]="";
$conf[]="var \$cacheExpireMultiplier = 1;";
$conf[]="var \$cacheSettings = array(";
$conf[]="		'getHostDetails' => 86400, // \"never\" changes";
$conf[]="		'getGuestOSTypes' => 86400,";
$conf[]="		'getSystemProperties' => 86400,";
$conf[]="		'getInternalNetworks' => 86400,";
$conf[]="		'getMediums' => 600,";
$conf[]="		'getVMs' => 2,";
$conf[]="		'__getMachine' => 7200,";
$conf[]="		'__getNetworkAdapters' => 7200,";
$conf[]="		'__getStorageControllers' => 7200,";
$conf[]="		'__getSharedFolders' => 7200,";
$conf[]="		'__getUSBController' => 7200,";
$conf[]=");";
$conf[]="*/";
$conf[]="";
$conf[]="";
$conf[]="}";
$conf[]="";
$conf[]="function PhpVirtualBoxDetectLang(){";
$conf[]="\$array=array(\"fr\"=>\"fr_fr\",\"en\"=>\"en_us\",\"de\"=>\"de_de\");";
$conf[]="if(\$array[\$_COOKIE[\"artica-language\"]]<>null){return \$array[\$_COOKIE[\"artica-language\"]];}else{return \"en_us\";}";
$conf[]="}";
$conf[]="?>";
@file_put_contents("/usr/share/artica-postfix/virtualbox/config.php",@implode("\n",$conf));
echo "Starting......: VirtualBox Web service set phpVirtualBox config done\n";

}


function maintenance(){
	
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".maintenance.pid";
	$unix=new unix();
	if($unix->process_exists(@file_get_contents($pidfile))){die();}
	@file_put_contents($pidfile,getmypid());	
	
	$vms=buildlist();
	if(!is_array($vms)){return null;}
	$unix=new unix();
	$manage=$unix->find_program("VBoxManage");
	
	$vboxs=buildMinimalListName();
	if(is_array($vboxs)){
		while (list($uuids,$arr)=each($vboxs)){
			echo "$manage metrics setup -period 10 -samples 100 $uuids\n";
			shell_exec("$manage metrics setup -period 10 -samples 100 $uuids");
		
		
		}
	}
	shell_exec("$manage metrics enable >/dev/null 2>&1");
	
	
	$sql="SELECT uuid FROM virtualbox_watchdog";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	
	$array=buildMinimalList();
	
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($array[$ligne["uuid"]]["UUID"]==null){
			echo "Delete old uuid in watchdog list\n";
			DeleteFromWatchdog($ligne["uuid"]);
			continue;
		}
		
		if($array[$ligne["uuid"]]["STATE"]==0){
			echo "{$ligne["uuid"]} is powered off, start it\n";
			startvm($ligne["uuid"],$array[$ligne["uuid"]]["NAME"]);
			continue;
			
		}
		echo "{$ligne["uuid"]} state inchanged\n";
		
		
	}
}

function startvm($uuid,$name){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	$nohup=$unix->find_program("nohup");
	if($manage==null){
		if($GLOBALS["VERBOSE"]){echo "VBoxManage no such tool\n";}
		return;
	}	
	
	$VBoxHeadless=$unix->LOCATE_VBoxHeadless();
	if(is_file($VBoxHeadless)){
		$cmd="$VBoxHeadless --startvm $uuid --vrdp on >/tmp/start-$uuid 2>&1 &";
	}else{
		$cmd="$manage startvm $uuid --type headless >/tmp/start-$uuid 2>&1 &";
	}
	echo "$cmd\n";
	shell_exec($cmd);
	sleep(5);
	send_email_events("Virtual Machines: Watchdog for $name","$name Virtual machine was powered off,\nartica automatically started it:\n".@file_get_contents("/tmp/start-$uuid"),"virtual_machines");
}



function DeleteFromWatchdog($uuid){
		$sql="DELETE FROM virtualbox_watchdog WHERE uuid='$uuid'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n";}	
	
}

function buildMinimalList(){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		if($GLOBALS["VERBOSE"]){echo "VBoxManage no such tool\n";}
		return;
	}	
	exec("$manage list -l vms",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			if($key=="UUID"){$VirtualBoxName=$data;}
			if(strtoupper($key)=="STATE"){
				if(preg_match("#powered off#",$data)){$data=0;}
				if(preg_match("#running#",$data)){$data=1;}
				
			}
		}	
		
		if($VirtualBoxName<>null){
			$array[$VirtualBoxName][strtoupper($key)]=$data;
		}		
	}
	
	return $array;
}

function buildReferences(){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		if($GLOBALS["VERBOSE"]){echo "VBoxManage no such tool\n";}
		return;
	}	
	exec("$manage list -l vms",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			if($key=="UUID"){$VirtualBoxUUID=$data;}
			if($key=="Name"){
				$array[$VirtualBoxUUID]=$data;
				
			}
		}	
	
	}
	
	return $array;
}


function buildMinimalListName(){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		if($GLOBALS["VERBOSE"]){echo "VBoxManage no such tool\n";}
		return;
	}	
	exec("$manage list -l vms",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			if($key=="Name"){$VirtualBoxName=$data;}
			if(strtoupper($key)=="STATE"){
				if(preg_match("#powered off#",$data)){$data=0;}
				if(preg_match("#running#",$data)){$data=1;}
				
			}
		}	
		
		if($VirtualBoxName<>null){
			$array[$VirtualBoxName][strtoupper($key)]=$data;
		}		
	}
	
	return $array;
}


function buildlist(){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		if($GLOBALS["VERBOSE"]){echo "VBoxManage no such tool\n";}
		return;
	}
	if($GLOBALS["VERBOSE"]){echo "$manage list -l vms\n";}
	exec("$manage list -l vms",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			if($key=="Name"){
				if(!preg_match("#\(UUID:\s+#",$data)){$VirtualBoxName=$data;}
			}
			
		}
		
			if($VirtualBoxName<>null){
			if(strtoupper($key)=="NAME"){
				if($array[$VirtualBoxName]["NAME"]<>null){
					
					if(!$GLOBALS["VBXSNAPS"][$data]){
						$array[$VirtualBoxName]["SNAPS"][]=$data;
						$GLOBALS["VBXSNAPS"][$data]=true;
						continue;
					}
					
				}
			}
			$array[$VirtualBoxName][strtoupper($key)]=$data;
		}
	}
	
	return $array;
}

function nat_ports($uuid=null){
	$unix=new unix();
	$references=buildReferences();
	$VBoxManage=$unix->find_program("VBoxManage");
	if(strlen($uuid)>5){$addsql=" WHERE `vboxid`='$uuid'";}
	$sql="SELECT * FROM `virtualbox_nat`$addsql";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo $q->mysql_error."\n";return;}

	
	
	$nics["82540EM"]="e1000";
	$nics["82543GC"]="e1000";
	$nics["82545EM"]="e1000";
	$nics["Am79C970A"]="pcnet";
	$nics["Am79C973"]="pcnet";
	$array_infos=array();

	$arrayresults=array();
	$sock=new sockets();
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if(!is_array($array_infos[$ligne["vboxid"]])){
				$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($ligne["vboxid"]));
				$array_infos[$ligne["vboxid"]]=unserialize(base64_decode($infos));
			}	
			$nic1=$array_infos[$ligne["vboxid"]]["NIC 1"];
			if(!preg_match("#Type: (.+?),#",$nic1,$re)){
				if($references[$ligne["vboxid"]]==null){
					$sql="DELETE FROM `virtualbox_nat` WHERE `vboxid`='{$ligne["vboxid"]}'";
					echo "{$ligne["vboxid"]} no references, delete it\n";
					$q->QUERY_SQL($sql,"artica_backup");
				}
				echo "$nic1 (unable to preg_match)\n";
				continue;
			}
			$nic1=$re[1];
			$Type=$nics[$nic1];
			if($Type==null){echo "$nic1 (unable to get type of $nic1)\n";continue;}
			$cmd="$VBoxManage setextradata {$ligne["vboxid"]} \"VBoxInternal/Devices/$Type/0/LUN#0/Config/ArticaNat{$ligne["localport"]}To{$ligne["vboxport"]}/HostPort\" {$ligne["localport"]} 2>&1";
			exec($cmd,$results2);
			$cmd="$VBoxManage setextradata {$ligne["vboxid"]} \"VBoxInternal/Devices/$Type/0/LUN#0/Config/ArticaNat{$ligne["localport"]}To{$ligne["vboxport"]}/GuestPort\" {$ligne["vboxport"]} 2>&1";
			exec($cmd,$results2);
			$cmd="$VBoxManage setextradata {$ligne["vboxid"]} \"VBoxInternal/Devices/$Type/0/LUN#0/Config/ArticaNat{$ligne["localport"]}To{$ligne["vboxport"]}/Protocol\" TCP 2>&1";
			exec($cmd,$results2);
			
			$arrayresults[$references[$ligne["vboxid"]]][]="$Type: NIC 1 {$ligne["localport"]} -> {$ligne["vboxport"]} TCP".@implode("\n",CleanArrayPub($results2));
			
		
		
	}
	
	while (list($index,$line)=each($arrayresults)){
		echo "\n$index\n---------------------------\n".@implode("\n",$line)."\n\n";
		
	}
	
	echo "\n";
}

function CleanArrayPub($array){
	while (list($index,$line)=each($results)){
		if(strpos($line,"VirtualBox Command Line Management")>0){continue;}
		if(strpos($line,"Oracle Corporation")>0){continue;}
		if(strpos($line,"rights reserved.")>0){continue;}
		if(trim($line)==null){continue;}
		$returned[]=$line;
	}
	
	return $returned;
}
