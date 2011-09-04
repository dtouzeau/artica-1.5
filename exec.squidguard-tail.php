<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events("Already executed.. aborting the process");
	die();
}

if($argv[1]=='--date'){echo date("Y-m-d H:i:s")."\n";}

$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
@mkdir("/var/log/artica-postfix/squid-stats",0666,true);
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

if(strpos($buffer,"init urllist")>0){return ;}
if(strpos($buffer,"init expressionlist")>0){return ;}
if(strpos($buffer,"init domainlist")>0){return ;}


	if(preg_match('#INFO: loading dbfile (.+)#',$buffer,$re)){
		events("LOADING $re[1]");
	  	$GLOBALS[__FILE__]["DBFILE"]=trim($re[1]);
		return null;
		}	
		
		
		
	if(preg_match("#FATAL: Error db_open: Unknown error#",$buffer,$re)){
	  	events("ERROR ON {$GLOBALS[__FILE__]["DBFILE"]} : $buffer");
	  	if(basename($GLOBALS[__FILE__]["DBFILE"])=="urls.db"){
	  		events("urls.db -> create ".dirname($GLOBALS[__FILE__]["DBFILE"])."/urls it and recompile it");
	  		@file_put_contents(dirname($GLOBALS[__FILE__]["DBFILE"])."/urls","www.". md5(time()).".bv");
	  	}
	  	$file="/etc/artica-postfix/croned.1/squidguard.". md5($GLOBALS[__FILE__]["DBFILE"]).".error";
		if(IfFileTime($file)){
			$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --compile-single \"{$GLOBALS[__FILE__]["DBFILE"]}\" &";
			events("$cmd");
	  		shell_exec($cmd);
	  		WriteFileCache($file);
		}
		return null;
		}
		
		
		
if(preg_match("#\]\s+(.+?):\s+Cannot allocate memory#",$buffer,$re)){
	  	events("ERROR ON {$re[1]} : Cannot allocate memory -> create it");
	  	@file_put_contents($re[1],"www.". md5(time()).".bv");
		shell_exec("squid -k reconfigure");
		return null;
		}			
		
if(preg_match("#\]\s+(.+?):\s+No such file or directory#",$buffer,$re)){
	  	events("ERROR ON {$re[1]} : No such file or directory -> create it");
	  	@file_put_contents($re[1],"www.nodomain.bv");
		shell_exec("squid -k reconfigure");
		return null;
		}		

	if(strpos($buffer,"ERROR: Going into emergency mode")>0){
		events("ERROR: Going into emergency mode");
		send_email_events("squidguard: squidguard turn to emergency mode","SquidGuard claim\n$buffer\nPlease contact your support to fix this problem\ncurrently, no filtering urls will be enabled","proxy");
		return ;
	}
		
		

	events("Not filtered: $buffer");

}

function IfFileTime($file,$min=10){
	if(file_time_min($file)>$min){return true;}
	return false;
}
function WriteFileCache($file){
	@unlink("$file");
	@unlink($file);
	@file_put_contents($file,"#");	
}
function events($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/squidguard-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid]:: ".basename(__FILE__)." $text\n");
		@fclose($f);	
		}
	

?>