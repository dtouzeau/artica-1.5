<?php

include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.auth.tail.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events("Already executed.. aborting the process");
	die();
}

$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
$unix=new unix();
if($unix->process_exists($oldpid)){
	events("Already running $oldpid, aborting ");
	die();
}


events("running $pid ");
file_put_contents($pidfile,$pid);


$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
	$buffer .= fgets($pipe, 4096);
	Parseline($buffer);
	$buffer=null;
}

fclose($pipe);
events("Shutdown...");
die();



function Parseline($buffer){
$buffer=trim($buffer);
if($buffer==null){return null;}

$auth=new auth_tail();
if($auth->ParseLog($buffer)){return;}

events("Not Filtered \"$buffer\"");

}

function events($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/auth-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		}