<?php
include_once (dirname(__FILE__).'/class.unix.inc');
include_once(dirname(__FILE__)."/class.ini-frame.inc");
if(!$GLOBALS["BYPASS"]){
	if($_SERVER['REMOTE_ADDR']<>null){
		if($_SERVER['REMOTE_ADDR']<>"127.0.0.1"){die();}
	}}
session_start();

function sys_events($text){
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/framework.debug";
		$size=filesize($logFile);
		if($size>100000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="[$pid] $date $text\n";
		@fwrite($f,$line);
		@fclose($f);
		}
		
		
function file_time_min($path){
	if(!is_file($path)){
		if($GLOBALS["VERBOSE"]){echo "file_time_min() -> unable to stat $path\n";}
		return 100000;
		}
	 $last_modified = filemtime($path);
	 $data1 = $last_modified;
	 $data2 = time();
	$difference = ($data2 - $data1); 	 
	return round($difference/60);	 
}

function calc_time_min($time_source){
	$last_modified = $time_source;
	 $data1 = $last_modified;
	 $data2 = time();
	$difference = ($data2 - $data1); 	 
	return round($difference/60);	 	
	
}

function file_time_sec($path){
	if(!is_file($path)){return 100000;}
	$last_modified = filemtime($path);
	$data1 = $last_modified;
	$data2 = time();
	$difference = ($data2 - $data1); 	 
	return round($difference);	 
}


function Build_pid_func($file,$function){
	if(!isset($GLOBALS["CLASS_UNIX"])){ include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
   	if(!is_object($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	$mypid=getmypid();
	$base=basename($file);
	if(!is_dir("/etc/artica-postfix/pids.3")){@mkdir("/etc/artica-postfix/pids.3",null,true);}
	$pid_file="/etc/artica-postfix/pids.3/$base.$function.pid";
	if(!is_file($pid_file)){@file_put_contents($pid_file,$mypid);return true;}
	$pid=trim(@file_get_contents($pid_file));
	if($pid==null){@file_put_contents($pid_file,$mypid);return true;}
	if($GLOBALS["CLASS_UNIX"]->process_exists($pid)){writelogs_framework("[$base:$function] . PID $pid already executed",__FUNCTION__,__FILE__,__LINE__);return false;}
	@file_put_contents($pid_file,$mypid);
	return true;
	
}

function CheckPID($pid,$base,$function,$file){
	if(!isset($GLOBALS["CLASS_UNIX"])){ include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
   	if(!is_object($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
   	return $GLOBALS["CLASS_UNIX"]->process_exists($pid);
	}


function sys_THREAD_COMMAND_SET($zcommands){
	 if(!isset($GLOBALS["CLASS_UNIX"])){ include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	 if(!is_object($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	 $GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($zcommands);
 	}

function sys_exec($cmd){
	sys_events("Execute:: $cmd");
	exec($cmd);
}

function find_program($strProgram) {
   if(!isset($GLOBALS["CLASS_UNIX"])){ include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
   if(!is_object($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
   return $GLOBALS["CLASS_UNIX"]->find_program($strProgram);
   }


function execute_program($strProgramname, $strArgs = '', &$strBuffer, $booErrorRep = true) {
  $strBuffer = '';
  $strError = '';
  $strProgram = find_program($strProgramname);
  if (!$strProgram) {
    if ($booErrorRep) {
      events('execute_program:: find_program(' . $strProgramname . ') program not found on the machine');
    }
    return false;
  }
  
  if ($strArgs) {
    $arrArgs = explode(' ', $strArgs);
    for ($i = 0;$i < count($arrArgs);$i++) {
      if ($arrArgs[$i] == '|') {
        $strCmd = $arrArgs[$i+1];
        $strNewcmd = find_program($strCmd);
        $strArgs = ereg_replace("\| " . $strCmd, "| " . $strNewcmd, $strArgs);
      }
    }
  }

  $descriptorspec = array(0 => array("pipe", "r"), // stdin is a pipe that the child will read from
  1 => array("pipe", "w"), // stdout is a pipe that the child will write to
  2 => array("pipe", "w") // stderr is a pipe that the child will write to
  );

  $process = proc_open($strProgram . " " . $strArgs, $descriptorspec, $pipes);
  if (is_resource($process)) {
    while (!feof($pipes[1])) {
      $strBuffer.= fgets($pipes[1], 1024);
    }
    fclose($pipes[1]);
    while (!feof($pipes[2])) {
      $strError.= fgets($pipes[2], 1024);
    }
    fclose($pipes[2]);
  }
  $return_value = proc_close($process);
  $strError = trim($strError);
  $strBuffer = trim($strBuffer);

  if (!empty($strError)) {
    if ($booErrorRep) {
      events("execute_program::$strProgram, $strError Return value: $return_value");
    }
    return false;
  }
  return true;
}

function sys_hostname_g(){
	execute_program("hostname","-f",$results);
	return $results;
}
function sys_CRON_CREATE_SCHEDULE($ProgrammedTime,$Croncommand,$filename){
  $mailadmin=info_ADMIN_MAIL();
  $conf="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin\n";
  $conf=$conf."MAILTO=\"$mailadmin\"\n";
  $conf=$conf."$ProgrammedTime\troot\t$Croncommand >/dev/null 2>&1\n";
  sys_events("sys_CRON_CREATE_SCHEDULE:: create new entry $filename");
  file_put_contents("/etc/cron.d/$filename",$conf);
  chmod("/etc/cron.d/$filename",0640);
  chown("/etc/cron.d/$filename","root");
}

function info_ADMIN_MAIL(){
	if(!is_file('/etc/artica-postfix/smtpnotif.conf')){return null;}
	$ini=new iniFrameWork("/etc/artica-postfix/smtpnotif.conf");
	return $ini->get("SMTP","smtp_dest");
}
function LOCATE_PHP5_BIN2(){
  if(!isset($GLOBALS["CLASS_UNIX"])){ include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
  if(!is_object($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
  return $GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN();
	}
//############################################################################## 
//############################################################################# 

?>