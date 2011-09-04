<?php

function WriteToSyslogMail($text,$file,$error=false){
	$file=basename($file);
	if(!$error){$LOG_SEV=LOG_INFO;}else{$LOG_SEV=LOG_ERR;}
	openlog($file, LOG_PID , LOG_MAIL);
	if(function_exists("syslog")){ syslog($LOG_SEV, $text);}
	closelog();
	
	
	}


function writeToFile($text,$filepath){
	$f = @fopen($filepath, 'w');
	@fwrite($f,$text);
	@fclose($f);
}
function writelogs($text=null,$function=null,$file_source=null,$line=null){
	$name=null;
	if(!isset($GLOBALS["VERBOSE"])){$GLOBALS["VERBOSE"]=false;}
	if(isset($_SESSION["uid"])){$name="[{$_SESSION["uid"]}]";}
	if($file_source==null){$file_source= __FILE__ ;}
	$prefix="[CONSOLE]::";
	if(function_exists("posix_getuid")){if(posix_getuid()==0){$prefix="[DAEMON]::";}}
	if(function_exists("getmypid")){$pid=getmypid();}
	$class=null;
	if(preg_match("#(.+?)=>(.+)#",$function,$re)){$function="{$re[1]}/{$re[2]}";}
	
	$p=dirname(__FILE__);
	$p=str_replace("/ressources","",$p);
	$file_source=basename($file_source);
	$file_source=str_replace("$p/","",$file_source);
	
	if($line<>null){$line=" line $line";}
	$file_source=str_replace("/usr/share/artica-postfix/","",$file_source);
	$file_source=str_replace("/home/dtouzeau/developpement/artica-postfix/","",$file_source);
	if($GLOBALS["VERBOSE"]){echo "[$pid] $prefix::$function:: $text in $file_source$line\n";}
	$GLOBALS["MEMORY_LOGS"][]="[$pid] $prefix::$function:: $text in $file_source$line";
	if(!isset($_SERVER["REMOTE_ADDR"])){$_SERVER["REMOTE_ADDR"]="LOCAL";}
	$IP=$_SERVER["REMOTE_ADDR"];
	$SCRIPT_FILENAME=basename($_SERVER["SCRIPT_FILENAME"]);
	if(function_exists("posix_getuid")){if(posix_getuid()>0){if($_SESSION["uid"]==-100){$name="[SuperAdmin]";}}}
	
	if(!isset($name)){$name=null;}
	$text2=date('H:i:s')." $file_source [$IP]::$name $function:: $text line: $line source file:$SCRIPT_FILENAME";
	$text="[$pid] $prefix::$name:$function:: $text in $file_source$line";
	writeOtherlogs(dirname(__FILE__)."/logs/web/interface.debug",$text2);
	if(isset($GLOBALS["ADDLOG"])){writeOtherlogs($GLOBALS["ADDLOG"],$text2);}
	phpxlog("$text");
	if(basename($file_source)=="exec.backup.php"){writeOtherlogs("/var/log/artica-postfix/exec.backup.".date("Y-m-d").".log",$text);}
	$arr=explode("/",$function);
	if(is_array($arr)){if(count($arr)>0){if(isset($arr[0])){$class=$arr[0];if(isset($arr[1])){$function=$arr[1];}}}}
	writeClasslogs($text,$function,$class);

}
function phpxlog($text){
	$f = @fopen("/var/log/php.log", 'a');
	@fwrite($f, "$text\n");
	@fclose($f);	
	
}

function writeOtherlogs($file,$text=null){
	@mkdir(dirname($file));
	$logFile=$file;
	if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   	if (is_file($logFile)) { 
   		$size=filesize($logFile);
   		if($size>1000000){unlink($logFile);}
   	}
	$logFile=str_replace("//","/",$logFile);
	$f = @fopen($logFile, 'a');
	@fwrite($f, "$text\n");
	@fclose($f);
}function writeClasslogs($text=null,$function=null,$class=null){
	
	@mkdir('/usr/share/artica-postfix/ressources/logs/web');
	$logFile="/usr/share/artica-postfix/ressources/logs/web/class.$class.log";
	if($class==null){$logFile="/usr/share/artica-postfix/ressources/logs/web/artica.log";}
	if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   	if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date: [$function()]:: $text\n");
		@fclose($f);
}function writepostfixlogs($text=null,$function=null,$file_source=null){
	
if($file_source==null){$file_source= __FILE__ ;}
		$file_source=basename($file_source);
			@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		    $logFile="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		$text="$date:($file_source) [$function()][{$_SERVER['REMOTE_ADDR']}]:: $text\n";
		if($GLOBALS["DEBUG"]){echo $text;}
		@fwrite($f,$text );
		@fclose($f);}function writesquidlogs($text=null,$function=null,$file_source=null){
	
if($file_source==null){$file_source= __FILE__ ;}
		$file_source=basename($file_source);
			@mkdir('/usr/share/artica-postfix/ressources/logs/web');
		    $logFile="/usr/share/artica-postfix/ressources/logs/web/interface-squid.log";
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>1000000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date:($file_source) [$function()][{$_SERVER['REMOTE_ADDR']}]:: $text\n");
		@fclose($f);}function write_syslog($text,$file){$file=basename($file);if(!function_exists('syslog')){return null;}openlog($file, LOG_PID | LOG_PERROR, LOG_LOCAL0);syslog(LOG_INFO, $text);closelog();}
		
?>
