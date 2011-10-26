<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(is_file("/etc/artica-postfix/AS_KIMSUFFI")){echo "AS_KIMSUFFI!\n";die();}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if($argv[1]=='--parse'){parsefile("/etc/artica-postfix/{$argv[2]}.map",$argv[3]);die();}
if($argv[1]=="--scan-nets"){scannetworks();exit;}
if($argv[1]=="--scan-results"){nmap_scan_results();exit;}

$GLOBALS["COMPUTER"]=$argv[1];
$GLOBALS["COMPUTER"]=str_replace('$',"",$GLOBALS["COMPUTER"]);

if($GLOBALS["COMPUTER"]==null){
	echo "no computer name set {$argv[1]}!\n";
	die();
}

$users=new usersMenus();
$sock=new sockets();
$ComputersAllowNmap=$sock->GET_INFO("ComputersAllowNmap");
if($ComputersAllowNmap==null){$ComputersAllowNmap=1;}
if($ComputersAllowNmap==0){die();}


if(!is_file($users->NMAP_PATH)){
	echo "Unable to stat nmap binary file...\n";
	exit;
}

$computer=new computers($GLOBALS["COMPUTER"].'$');

echo "Scanning \"{$GLOBALS["COMPUTER"]}\":[$computer->ComputerIP]\n";

if($computer->ComputerIP=="0.0.0.0"){$computer->ComputerIP=null;}
if($computer->ComputerIP==null){$computer->ComputerIP=gethostbyname($GLOBALS["COMPUTER"]);}
if($computer->ComputerIP<>null){$cdir=$computer->ComputerIP;}else{$cdir=$GLOBALS["COMPUTER"];}

echo "Scanning $cdir and save results to /etc/artica-postfix/$cdir.map\n";

$cmd=$users->NMAP_PATH." -v -F -PE -O $cdir -oN /etc/artica-postfix/$cdir.map --system-dns --version-light >/dev/null 2>&1";

echo "Executing $cmd\n";

system($cmd);
echo "Parsing results for $cdir\n";

if(!is_file("/etc/artica-postfix/$cdir.map")){
	echo "Unable to stat /etc/artica-postfix/$cdir.map\n";
	exit;
}

parsefile("/etc/artica-postfix/$cdir.map",$GLOBALS["COMPUTER"]);   


function parsefile($filename,$uid){
	
	$datas=file_get_contents($filename);
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(preg_match("#([0-9]+).+?open\s+(.+)#",$ligne,$re)){
			$array[]=$re[1].":".$re[2];
			continue;
			
		}
		
		if(preg_match("#^Running:(.+)#",$ligne,$re)){
			$ComputerRunning=$re[1];
			continue;
		}
		
		if(preg_match("#^OS details:(.+)#",$ligne,$re)){
			$ComputerOS=$re[1];
			continue;
		}	
	if(preg_match("#^MAC Address:(.+).+?\((.+?)\)#",$ligne,$re)){
			$ComputerMacAddress=$re[1];
			$ComputerMachineType=$re[2];
			continue;
		}				
		
		
		
	if(preg_match("#^MAC Address:(.+)#",$ligne,$re)){
			$ComputerMacAddress=$re[1];
			continue;
		}		
		
		 
		
		
	}
	
	
	if($ComputerMacAddress<>null){
		$computer=new computers();
		$cpid=$computer->ComputerIDFromMAC($ComputerMacAddress);
		
	}
if($cpid==null){$cpid=$uid;}
	echo "Save infos for $cpid\n";
	echo "ComputerMacAddress: $ComputerMacAddress\n";
	echo "ComputerOS: $ComputerOS\n";
	
	
	$computer=new computers($cpid."$");
	if($ComputerMacAddress<>null){$computer->ComputerMacAddress=$ComputerMacAddress;}
	if($ComputerOS<>null){$computer->ComputerOS=$ComputerOS;}
	if($ComputerRunning<>null){$computer->ComputerRunning=$ComputerRunning;}
	if($ComputerMachineType<>null){$computer->ComputerMachineType=$ComputerMachineType;}
	if(is_array($array)){
		$computer->ComputerOpenPorts=implode("\n",$array);
	}
	$computer->Edit();
	echo $datas;
	
}

function scannetworks(){
	$unix=new unix();
	$sock=new sockets();
	$nmap=$unix->find_program("nmap");
	$cdir=array();
	if(!is_file($nmap)){return false;}
	$ComputersAllowNmap=$sock->GET_INFO('ComputersAllowNmap');
	$NmapRotateMinutes=$sock->GET_INFO("NmapRotateMinutes");
	if(!is_numeric($ComputersAllowNmap)){$ComputersAllowNmap=1;}
	if(!is_numeric($NmapRotateMinutes)){$NmapRotateMinutes=60;}
	if($NmapRotateMinutes<5){$NmapRotateMinutes=5;}
	if($ComputersAllowNmap==0){return;}
	if(!$GLOBALS["VERBOSE"]){
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
		$pidtime="/etc/artica-postfix/pids/".basename(__FILE__).".time";
		
		if($unix->file_time_min($pidtime)<$NmapRotateMinutes){
			if($GLOBALS["VERBOSE"]){echo "No time to be executed\n";}
			return;
		}
		
		
		$oldpid=$unix->get_pid_from_file($pidfile);
		if($unix->process_exists($oldpid)){
			if($GLOBALS["VERBOSE"]){echo "Already $oldpid running, aborting...\n";}
			return;
		}
		
		@file_put_contents($pidfile, getmypid());
		@file_put_contents($pidtime, time());
	}
	
	$net=new networkscanner();
	while (list ($num, $maks) = each ($net->networklist)){if(trim($maks)==null){continue;}$hash[$maks]=$maks;}	
	while (list ($num, $maks) = each ($hash)){if(!$net->Networks_disabled[$maks]){if($GLOBALS["VERBOSE"]){echo "Network: $maks OK\n";}$cdir[]=$maks;}}
	if(count($cdir)==0){if($GLOBALS["VERBOSE"]){echo "No network, aborting...";}return;}
	
	$cmd="$nmap -O ". trim(@implode(" ", $cdir))." -oN /etc/artica-postfix/nmap.map --system-dns -p1 2>&1"; 
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	exec($cmd,$results);
	
	while (list ($index, $ligne) = each ($results) ){
		if(preg_match("#\(([0-9]+).+?hosts.+?scanned in(.+)#", $ligne,$re)){
			$hosts=$re[1];
			$time=trim($re[2]);
			nmap_logs("$hosts scanned in $time",@implode("\n", $results));
			break;
		}
	}
	
	nmap_scan_results();
	
}

function nmap_scan_results(){
	if(!is_file("/etc/artica-postfix/nmap.map")){return;}
	$f=explode("\n", @file_get_contents("/etc/artica-postfix/nmap.map"));
	while (list ($index, $ligne) = each ($f) ){
		if(preg_match("#Nmap scan report for\s+(.+?)\s+\(([0-9\.]+)#", $ligne,$re)){
			$ipaddr=$re[2];
			$computer[$ipaddr]["IPADDR"]=$re[2];
			$computer[$ipaddr]["HOSTNAME"]=trim($re[1]);
			$LOGS[]="Found $ipaddr hostname= {$re[1]}";
			continue;
		}
		
		
		if(preg_match("#Nmap scan report for ([0-9\.]+)$#", trim($ligne),$re)){
			$ipaddr=$re[1];
			$computer[$ipaddr]["IPADDR"]=$re[1];
			$LOGS[]="Found $ipaddr without computername ";
			continue;
		}
		
		if(preg_match("#^MAC Address:\s+([0-9A-Z:]+)$#",trim($ligne),$re)){
			if(isset($MACSSCAN[trim($re[1])])){continue;}
			$computer[$ipaddr]["MAC"]=trim($re[1]);
			$LOGS[]="Found $ipaddr with mac {$re[1]} ";
			$MACSSCAN[trim($re[1])]=true;
			continue;
		}
		
		if(preg_match("#^MAC Address:(.+).+?\((.+?)\)#",$ligne,$re)){
			if(isset($MACSSCAN[trim($re[1])])){continue;}
			$MACSSCAN[trim($re[1])]=true;
			$computer[$ipaddr]["MAC"]=trim($re[1]);
			$computer[$ipaddr]["MACHINE_TYPE"]=trim($re[2]);
			$LOGS[]="Found $ipaddr with mac {$re[1]} and machine type {$re[2]}";
			continue;
		}

		if(preg_match("#^Running:(.+)#",$ligne,$re)){
			$computer[$ipaddr]["RUNNING"]=trim($re[1]);
			continue;
		}
		
		if(preg_match("#^OS details:(.+)#",$ligne,$re)){
			$LOGS[]="Found $ipaddr with OS {$re[1]}";
			$computer[$ipaddr]["OS"]=trim($re[1]);
			continue;
		}		
		
		
	}
	nmap_logs(count($f). " analyzed lines",@implode("\n", $LOGS));
	
	
	$c=0;
	while (list ($ipaddr, $array) = each ($computer) ){
		if(isset($already[$mac])){continue;}
		$mac=trim($array["MAC"]);
		if($mac==null){continue;}
		$c++;
		$already[$mac]=true;
		
		$ldap_ipaddr=null;
		$ComputerRealName=null;
		$uid=null;
		$RAISON=array();
		if(!isset($array["HOSTNAME"])){$array["HOSTNAME"]=null;}
		if(!isset($array["OS"])){$array["OS"]=null;}
		if(!isset($array["RUNNING"])){$array["RUNNING"]=null;}
		if(!isset($array["MACHINE_TYPE"])){$array["MACHINE_TYPE"]=null;}
	
		$cmp=new computers(null);
		$uid=$cmp->ComputerIDFromMAC($mac);
		if($uid<>null){
			if($GLOBALS["VERBOSE"]){echo "$mac = $uid\n";}
			$cmp=new computers($uid);
			
			$ldap_ipaddr=$cmp->ComputerIP;
			$ComputerRealName=$cmp->ComputerRealName;
			if($GLOBALS["VERBOSE"]){echo "$mac = $uid\nLDAP:$ldap_ipaddr<>NMAP:$ipaddr\nLDAP CMP:$ComputerRealName<>NMAP:{$array["HOSTNAME"]}";}
			if($array["HOSTNAME"]<>null){
				$EXPECTED_UID=strtoupper($array["HOSTNAME"])."$";
				if($EXPECTED_UID<>$uid){
					$RAISON[]="UID: $uid is different from $EXPECTED_UID";
					nmap_logs("EDIT UID: $mac:[{$array["HOSTNAME"]}] ($ipaddr)",@implode("\n", $array)."\n".@implode("\n", $RAISON),$uid);
					$cmp->update_uid($EXPECTED_UID);
				}
			}
			if($ldap_ipaddr<>$ipaddr){
				writelogs("Change $ldap_ipaddr -> to $ipaddr for  $cmp->uid",__FUNCTION__,__FILE__,__LINE__);
				$RAISON[]="LDAP IP ADDR: $ldap_ipaddr is different from $ipaddr";
				$RAISON[]="DN: $cmp->dn";
				$RAISON[]="UID: $cmp->uid";
				$RAISON[]="MAC: $cmp->ComputerMacAddress";
				if(!$cmp->update_ipaddr($ipaddr)){$RAISON[]="ERROR:$cmp->ldap_last_error";}
				nmap_logs("EDIT IP: $mac:[{$array["HOSTNAME"]}] ($ipaddr)",@implode("\n", $array)."\n".@implode("\n", $RAISON),$uid);
				
			}
			if($array["OS"]<>null){
				if(strtolower($cmp->ComputerOS=="Unknown")){$cmp->ComputerOS=null;}
				if($cmp->ComputerOS==null){
					$RAISON[]="LDAP OS: $cmp->ComputerOS is different from {$array["OS"]}";
					nmap_logs("EDIT OS: $mac:[{$array["HOSTNAME"]}] ($ipaddr)",@implode("\n", $array)."\n".@implode("\n", $RAISON),$uid);
					$cmp->update_OS($array["OS"]);
				}
			}
			
			
			
		}else{
			if($array["HOSTNAME"]<>null){$uid="{$array["HOSTNAME"]}$";}else{$uid="$ipaddr$";}
			nmap_logs("ADD NEW: $mac:[{$array["HOSTNAME"]}] ($ipaddr)",@implode("\n", $array)."\n".@implode("\n", $RAISON),"$uid");
			$cmp=new computers();
			$cmp->ComputerIP=$ipaddr;
			$cmp->ComputerMacAddress=$mac;
			$cmp->uid="$uid";
			$cmp->ComputerOS=$array["OS"];
			$cmp->ComputerRunning=$array["RUNNING"];
			$cmp->ComputerMachineType=$array["MACHINE_TYPE"];
			$cmp->Add();
		}
		
		
		
		
		
	}
	
	nmap_logs("$c hosts analyzed in databases");
	@unlink("/etc/artica-postfix/nmap.map");
	//print_r($computer);
	
}



function nmap_logs($subject,$text,$uid){
	
	
	$subject=addslashes($subject);
	$text=addslashes($text);
	if($GLOBALS["VERBOSE"]){echo $subject."\n";}
	$sql="INSERT INTO nmap_events (subject,text,uid) VALUES ('$subject','$text','$uid');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	
	
	
}



?>