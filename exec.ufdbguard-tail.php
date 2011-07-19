<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["CLASS_UNIX"]=new unix();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
events("Found old PID $oldpid");
sleep(1);
if($GLOBALS["CLASS_UNIX"]->process_exists($oldpid)){
	events("Already executed PID: $oldpid.. aborting the process");
	die();
}

if($argv[1]=='--date'){echo date("Y-m-d H:i:s")."\n";}

$pid=getmypid();

@mkdir("/var/log/artica-postfix/squid-stats",0666,true);

$GLOBALS["PHP5_BIN"]=$GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN();
@mkdir("/var/log/artica-postfix/ufdbguard-queue",0666,true);
if(is_file("/var/log/artica-postfix/ufdbguard-tail.debug")){@unlink("/var/log/artica-postfix/ufdbguard-tail.debug");}
events("Running new $pid ");
events_ufdb_exec("Artica ufdb-tail running $pid");
file_put_contents($pidfile,$pid);
$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
	$buffer .= fgets($pipe, 4096);
	Parseline($buffer);
	$buffer=null;
}

fclose($pipe);
events_ufdb_exec("Artica ufdb-tail shutdown");
events("Shutdown...");
die();



function Parseline($buffer){
$buffer=trim($buffer);
if($buffer==null){return null;}

if(strpos($buffer,"ufdbGenTable should be called with the")>0){return ;}
if(strpos($buffer,"is deprecated and ignored")>0){return ;}
if(strpos($buffer,"init domainlist")>0){return ;}
if(strpos($buffer,"is empty !")>0){return ;}
if(strpos($buffer,"init expressionlist")>0){return ;}
if(strpos($buffer,"is optimised to one expression")>0){return ;}
if(strpos($buffer,"be analysed since there is no proper database")>0){return ;}
if(strpos($buffer,"REDIRECT 302")>0){return ;}
if(strpos($buffer,"close fd")>0){return ;}
if(strpos($buffer,": open fd ")>0){return ;}
if(strpos($buffer,"acl {")>0){return ;}
if(strpos($buffer,"must be part of the security")>0){return ;}
if(strpos($buffer,"}")>0){return ;}
if(strpos($buffer,"{")>0){return ;}
if(strpos($buffer,"] category \"")>0){return ;}
if(strpos($buffer,"]    domainlist     \"")>0){return ;}
if(strpos($buffer,"]       pass ")>0){return ;}
if(strpos($buffer,"configuration file")>0){return ;}
if(strpos($buffer,'expressionlist "')>0){return ;}
if(strpos($buffer,'is newer than')>0){return ;}
if(strpos($buffer,'source "')>0){return ;}
if(trim($buffer)==null){return;}
if(strpos($buffer,'max-logfile-size')>0){return ;}
if(strpos($buffer,'check-proxy-tunnels')>0){return ;}
if(strpos($buffer,'seconds to allow worker')>0){return ;}


	if(preg_match('#FATAL\*\s+table\s+"(.+?)"\s+could not be parsed.+?14#',$buffer,$re)){
		events("Table on {$re[1]} crashed");
		events_ufdb_exec("$buffer");
		$GLOBALS["CLASS_UNIX"]->send_email_events("ufdbguard: {$re[1]} could not be parsed","Ufdbguard claim: $buffer\n
		You need to compile this database","proxy");
		return;		
	}
	
	if(strpos($buffer,"HUP signal received to reload the configuration")>0){
		events_ufdb_exec("service was reloaded, wait 15 seconds");
		$GLOBALS["CLASS_UNIX"]->send_email_events("ufdbguard: service was reloaded, wait 15 seconds","Ufdbguard 
		: $buffer\n","proxy");
		return;
	}
	
	
	if(preg_match('#\*FATAL\*\s+cannot read from\s+"(.+?)"#',$buffer,$re)){
		events("Problem on {$re[1]}");
		events_ufdb_exec("$buffer");
		$GLOBALS["CLASS_UNIX"]->send_email_events("ufdbguard: {$re[1]} Not compiled..","Ufdbguard claim: $buffer\nYou need to compile your databases");
		return;		
	}
	
	if(preg_match("#\*FATAL\*\s+cannot read from\s+\"(.+?)\.ufdb\".+?No such file or directory#",$buffer,$re)){
		events("UFDB database missing : Problem on {$re[1]}");
		if(!is_file($re[1])){
			@mkdir(dirname($re[1]),666,true);
			shell_exec("/bin/touch {$re[1]}");
		}
		
		$GLOBALS["CLASS_UNIX"]->send_email_events("ufdbguard: {$re[1]} Not compiled..","Ufdbguard claim: $buffer\nYou need to compile your databases","proxy");
		return;		
	}
	
	
	if(preg_match("#FATAL\*\s+expression list\s+(.+?):\s+No such file or directory#",$buffer,$re)){
		events("Expression list: Problem on {$re[1]}");
		shell_exec("/bin/touch {$re[1]}");
		$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.squidguard.php --build";
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
		return;
	}

	if(preg_match("#the new configuration and database are loaded for ufdbguardd ([0-9\.]+)#",$buffer,$re)){
		$GLOBALS["CLASS_UNIX"]->send_email_events("UfdbGuard v{$re[1]} has reloaded new configuration and database",null,"proxy");
		return;
	}
	
	if(preg_match("#BLOCK (.*?)\s+(.+?)\s+(.+?)\s+(.+?)\s+(|http|https|ftp|ftps)://(.+?)myip=(.+)$#",$buffer,$re)){
		$user=trim($re[1]);
		$local_ip=$re[2];
		$rulename=$re[3];
		$category=$re[4];
		$www=$re[6];
		$public_ip=$re[7];
		if(strpos($www,"/")>0){$tb=explode("/",$www);$www=$tb[0];}
		$date=time();
		if($user<>"-"){$local_ip=$user;}
		$md5=md5("$date,$local_ip,$rulename,$category,$www,$public_ip");
		$sql="INSERT INTO `blocked_websites` (client,website,category,rulename,public_ip) VALUES";
		$sql="('$local_ip','$www','$category','$rulename','$public_ip')";
		
		@file_put_contents("/var/log/artica-postfix/ufdbguard-queue/$md5.sql",$sql);
		events("$www ($public_ip) blocked by rule $rulename/$category from $local_ip ".@filesize("/var/log/artica-postfix/ufdbguard-queue/$md5.sql")." bytes");
		return;
		
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
		$logFile="/var/log/artica-postfix/ufdbguard-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid]:: ".basename(__FILE__)." $text\n");
		@fclose($f);	
		}
function events_ufdb_exec($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/ufdbguard-compilator.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$textnew="$date [$pid]:: ".basename(__FILE__)." $text\n";
		
		@fwrite($f,$text );
		@fclose($f);	
		}		
	

?>