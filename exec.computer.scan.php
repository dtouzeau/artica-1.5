<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/ressources/class.mount.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');

if(is_file("/etc/artica-postfix/AS_KIMSUFFI")){echo "AS_KIMSUFFI!\n";die();}	
if($argv[1]=="--schedules"){set_computer_schedules();exit;}
if($argv[1]=="--import-list"){importcomputersFromList();exit;}		
$computer=$argv[1];
$cmdlines=implode(' ',$argv);
if(preg_match("#--verbose#",$cmdlines)){$_GET["DEBUG"]=true;}

LaunchScan($computer);

function LaunchScan($host){
	$debug=$_GET["D"];
	if(strpos($host,'$')==0){$host=$host.'$';}
	events("LaunchScan(): Scanning $host");
	
	$computer=new computers($host);
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->KasperkyAVScanningDatas);
	$commandline=BuildOptionCommandLine($ini,$computer->ComputerRealName);
	
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	$username=$ini->_params["ACCOUNT"]["USERNAME"];
	$password=$ini->_params["ACCOUNT"]["PASSWORD"];
	$ping=new ping($computer->ComputerIP);
	if(!$ping->Isping()){
		events("LaunchScan(): unable to ping computer...");
		return false;
	}
	events("LaunchScan(): to ping computer OK...");
	
	if(smbmount($computer->ComputerIP,$username,$password,$commandline)){
		
	}else{
	events("LaunchScan(): unable to mount $computer->ComputerIP");
	}
	
}

function importcomputersFromList(){
	cpulimit(20);
	$sock=new sockets();
	$tbl=explode("\n",$sock->GET_INFO("ComputerListToImport"));
	writelogs("ComputerListToImport=" . count($tbl)." values",__FUNCTION__,__FILE__,__LINE__);
	$i=0;
	$max=count($tbl);
	while (list ($num, $computername) = each ($tbl)){
		$z=$z+1;
		$computername=trim($computername);
		$ip=null;
		$mac=null;
		if($computername==null){continue;}
		$ip_arp=unserialize(base64_decode($sock->getFrameWork("cmd.php?arp-ip=".$_GET["arp-ip"])));
		if(is_array($ip_arp)){
			$ip=$ip_arp[0];
			$mac=$ip_arp[1];
			unset($ip_arp);
		}
		$pourc=round(($z/$max)*100);
		writelogs("$pourc) $computername",__FUNCTION__,__FILE__,__LINE__);
		
		WriteCOmputerBrowseProgress($pourc,"{import}: $computername ($ip/$mac)");
		
		if($mac<>null){$uid=$cmp->ComputerIDFromMAC($mac);}else{$uid="$computername$";}
		if($uid==null){$uid="$computername$";}
		
		$cmp=new computers($uid);
		if($ip<>null){$cmp->ComputerIP=$ip;}
		if($mac<>null){$cmp->ComputerMacAddress=$mac;}
		$cmp->ComputerRealName=$computername;
		$cmp->Add();
		$i=$i+1;
		}
		WriteCOmputerBrowseProgress(0,"{waiting}");
	
}
function WriteCOmputerBrowseProgress($pourc,$text){
	$ini=new Bs_IniHandler();
	$ini->set('NMAP','pourc',$pourc);
	$ini->set('NMAP','text',$text);
	$ini->saveFile('/usr/share/artica-postfix/ressources/logs/nmap.progress.ini');
	@chmod("/usr/share/artica-postfix/ressources/logs/nmap.progress.ini",0755);
}



function smbmount($host,$username,$password,$cmdline){

	if($username==null){
		events("smbmount(): using no user and password for remote connection is not supported");
		return false;
	}
	@mkdir("/opt/artica/mount/$host");
	$mount_point="/opt/artica/mount/$host";
	$f=new mount("/var/log/artica-postfix/computer-scan.debug");
	
	if(!$f->ismounted($mount_point)){
		events("smbmount(): not mounted...");
		$password_hidden=preg_replace("#.*#","*",$password);
		events("smbmount(): using $username $password_hidden\n");
	
		if($username<>null){$options=" -o username=$username,password=$password";}
		$cmd="mount -t smbfs$options //$host/c$ /opt/artica/mount/$host";
		events("smbmount(): $cmd");
		exec($cmd);
		
	}
	if($f->ismounted($mount_point)){
			events("smbmount(): $cmdline $mount_point");
			system("$cmdline $mount_point &");
			return true;
		}	
	
}


function BuildOptionCommandLine($ini,$computername){
	$debug=$_GET["D"];
	@mkdir("/usr/share/artica-postfix/ressources/logs/manual-scan");
	@chmod("/usr/share/artica-postfix/ressources/logs/manual-scan",0755);
	$cure=$ini->_params["scanner.options"]["cure"];
	if($ini->_params["scanner.options"]["Packed"]==1){$packed='P';}else{$packed='p';}
	if($ini->_params["scanner.options"]["Archives"]==1){$Archives='A';}else{$Archives='a';}
	if($ini->_params["scanner.options"]["SelfExtArchives"]==1){$SelfExtArchives='S';}else{$SelfExtArchives='s';}	
	if($ini->_params["scanner.options"]["MailBases"]==1){$MailBases='B';}else{$MailBases='b';}
	if($ini->_params["scanner.options"]["MailPlain"]==1){$MailPlain='M';}else{$MailPlain='m';}
	if($ini->_params["scanner.options"]["Heuristic"]==1){$Heuristic='E';}else{$Heuristic='e';}
	if($ini->_params["scanner.options"]["Recursion"]==1){$Recursion='-R';}else{$Recursion='-r';}
	$cmd="/opt/kaspersky/kav4samba/bin/kav4samba-kavscanner -f -e $packed$Archives$SelfExtArchives$MailBases$MailPlain$Heuristic $Recursion $cure";
	$cmd=$cmd . " -q -o /usr/share/artica-postfix/ressources/logs/manual-scan/$computername.results.log";
	if($debug){echo "BuildOptionCommandLine():: \"$cmd\"\n";}
	
	return $cmd;
	}
	
	
function set_computer_schedules(){
	if(is_file("/etc/artica-postfix/KASPERSKY_WEB_APPLIANCE")){die();}
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){
		writelogs("set_computer_schedules:: already $pid running, die",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	
	writelogs("set_computer_schedules:: starting",__FUNCTION__,__FILE__,__LINE__);
	$ldap=new clladp();
	$pattern="(&(objectClass=ArticaComputerInfos)(ComputerScanSchedule=*))";
	$attr=array("cn","ComputerScanSchedule","uid");
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	if(!$sr){
		events("set_computer_schedules():: $ldap->ldap_last_error line: ".__LINE__);
		return false;
	}
	
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);

	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		$computerscanschedule=$hash[$i]["computerscanschedule"][0];
		$filename="$uid";
		$filename=str_replace('.','',$filename);
		$filename=str_replace('$','',$filename);
		$filename=str_replace(' ','',$filename);
		$filename=str_replace('-','',$filename);
		$filename=str_replace('_','',$filename);
		sys_CRON_CREATE_SCHEDULE($computerscanschedule,LOCATE_PHP5_BIN()." ".__FILE__." $uid","artica-av-$filename");
		}
}

function events($text){
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/computer-scan.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="[$pid] $date $text\n";
		if($_GET["DEBUG"]){echo $line;}
		@fwrite($f,$line);
		@fclose($f);
}

	

?>