<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";

echo "save $pid in file $pidfile\n";
file_put_contents($pidfile,$pid);
$users=new usersMenus();
$_GET["server"]=$users->hostname;

$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
$buffer .= fgets($pipe, 4096);
Parseline($buffer);
$buffer=null;
}
fclose($pipe);

function Parseline($buffer){
	
	
if(preg_match("#ignor.+?non.+?limin.+?#",$buffer)){return null;}	
	
if(preg_match("#connection to (.+?):(.+?)\s+\[(.+?)\] failed: Connection timed out.#",$buffer,$re)){
	$server=$re[1];
	$port=$re[2];
	$ip=$re[2];
	fetchtimeout($server,$port,$ip,$buffer);
	return null;
}

if(preg_match("#reading message (.+?)@(.+?)@(.+?):.+?\(([0-9]+).+?\(([0-9]+)#",$buffer,$re)){
	$user="{$re[1]}@{$re[2]}";
	$server=$re[3];
	$octets=$re[4]+$re[5];
	AddFetchEv($user,$server,$octets);
	return null;
}
if(preg_match("#lecture du message (.+?)@(.+?)@(.+?):.+?\(([0-9]+).+?\(([0-9]+)#",$buffer,$re)){
	$user="{$re[1]}@{$re[2]}";
	$server=$re[3];
	$octets=$re[4]+$re[5];
	AddFetchEv($user,$server,$octets);
	return null;
}
if(preg_match("#lecture du message\s+(.+?)@(.+?):.+\(([0-9]+).+?limin.+?#",$buffer)){
	$user=$re[1];
	$server=$re[2];
	$octets=$re[3];
	AddFetchEv($user,$server,$octets);
	return null;
}

if(preg_match("#reading message\s+(.+?)@(.+?):.+\(([0-9]+).+?flushed#",$buffer)){
	$user=$re[1];
	$server=$re[2];
	$octets=$re[3];
	AddFetchEv($user,$server,$octets);
	return null;
}


fetchevents("Not Filtered:\"".trim($buffer)."\"");	
}

function fetchtimeout($server,$port,$ip,$buffer){
$file="/etc/artica-postfix/cron.1/".md5(__FILE__)."-".md5("$server,$port,$ip");
	if(file_time_min($file)<15){return null;}	
	send_email_events("fetchmail network error on  $server $port","fetchmail claim \"$buffer\", please set the right server for fetching messages",'system');
	@unlink($file);
	@file_put_contents("#",$file);		
}

function AddFetchEv($user,$server,$octets){
	@mkdir("/var/log/artica-postfix/fetchmail",0755,true);
	$date=date('Y-m-d H:i:s');
	$md5=md5("$user,$server,$octets,$date");
	$file="/var/log/artica-postfix/fetchmail/$md5.sql";
	$sql="INSERT INTO fetchmail_events (zDate,server,account,size) VALUES('$date','$server','$user','$octets')";
	@file_put_contents($file,$sql);
}
function fetchevents($text){
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/fetchmail-logger.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "[$pid] fetchmail: $date $text\n");
		@fclose($f);	
		}


?>