<?php
include_once(dirname(__FILE__)."/class.ping.inc");
class mount{
	var $eventspath=null;
	var $events=array();
	var $mount_point="";
	
	
	public function mount($eventspath=null){
		$this->eventspath=$eventspath;
	}
	
	public function ismounted($mount_point){
		$mount_point=str_replace("/",'\/',$mount_point);
		$mount_point=str_replace(".",'\.',$mount_point);
		$mount_point=str_replace("$",'\$',$mount_point);
		
		$tbl=explode("\n",@file_get_contents("/proc/mounts"));
		
		if(!is_array($tbl)){
			$this->events("ismounted:: not an array in /proc/mounts");
			return false;
		}
		while (list ($index, $line) = each ($tbl) ){
			if(preg_match("#$mount_point#",$line)){return true;}
		}
		//$this->events("ismounted() failed found \"$mount_point\"");	
	}
	
	
	public function smb_mount($mountpoint,$server,$username,$password,$path){
		
		if($this->ismounted($mountpoint)){
			$this->events("smb_mount() already mounted");
			return true;
		}
		
		if($username<>null){
			$credentials=" -o username=$username,password=$password ";
		}
		$ip=gethostbyname($server);
		if($ip==null){
			$this->events("smb_mount() failed while resolving \"$server\"");	
			return false;
		}
		$pp=new ping($ip);
		if(!$pp->Isping()){
			$this->events("smb_mount() Failed to ping $ip, aborting process.");
			return false;	
		}	
		$this->events("smb_mount() ping ok");	
		
		$cmd="/bin/mount -t smbfs$credentials//$server/$path $mountpoint";
		
		if(is_file("/sbin/mount.cifs")){
			$cmd="/sbin/mount.cifs //$server/$path $mountpoint$credentials";
			if(function_exists("writelogs")){writelogs("$cmd",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		$this->events("smb_mount() mounting //$server/$path -> $mountpoint");
		if(!is_dir("$mountpoint")){@mkdir($mountpoint,null,true);}
		//$this->events("$cmd");
		exec($cmd,$results);
		while (list ($index, $line) = each ($results) ){
			if(function_exists("writelogs")){writelogs("$line",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			$this->events("LOG:\"$line\"");
		}
		
		if($this->ismounted($mountpoint)){return true;}
		
	}
	
	public function usb_mount($mountpoint,$type,$dev){	
		
	if($this->ismounted($mountpoint)){
			$this->events("usb_mount() already mounted");
			return true;
		}
		$cmd="/bin/mount -t $type $dev $mountpoint";	
		$this->events("usb_mount() mounting $dev -> $mountpoint");
		if(!is_dir("$mountpoint")){@mkdir($mountpoint,null,true);}
		exec($cmd,$results);
		while (list ($index, $line) = each ($results) ){
			$this->events("LOG:\"$line\"");
		}
		
		if($this->ismounted($mountpoint)){return true;}		
			
			
	}
	
	
public function MountComputerRemote($computername,$ip,$username,$password){
	$computername=str_replace("$","",$computername);
	$sourcefile_base=basename($sourcefile);
	$this->mount_point="/opt/artica/mounts/remote-$computername";
	@mkdir($this->mount_point,null,true);
	$mount_bin=$this->find_program("mount");	
	if($mount_bin==null){
		$this->events[]="[$username/$ip]:: mount program, no such file";
		return false;
	}
	
	

	if(!$this->ismounted($this->mount_point)){
		$this->events[]="[$username/$ip]:: Mount point is not mounted, mount it";
		$this->events[]="[$username/$ip]:: Mount c$ on $uidPoint [$ip]";
		$cmd="$mount_bin -t smbfs -o username=$username,password=$password //$ip/c$ $this->mount_point 2>&1";
		exec($cmd,$results);
		while (list ($index, $line) = each ($results) ){if(trim($line)==null){continue;}$this->events[]=$line;}		
		
	}else{
		$this->events[]="[$username/$ip]:: Mount point is mounted";
		
	}
	
	if(!$mount->ismounted($this->mount_point)){return false;}
	$t=time();
	$tests_path="$this->mount_point/tmp/$t";
	
	
	if(!is_dir($tests_path)){@mkdir($tests_path,null,true);	}
	if(!is_dir($tests_path)){
		$this->events[]="[$username/$ip]:: c:\tmp\{$t}, permission denied";
		exec("umount -l $mountpoint");
		return false;
	}
	@rmdir($tests_path);
	unset($results);
	
	/*
	$GLOBALS["LOCAL_FILE_PATH"]="c:\\tmp\\ocs-agent\\$sourcefile_base";
	$this->events[]="[$username/$ip]:: {$GLOBALS["MOUNTED_PATH"]}\{$sourcefile_base}, success";
	*/
	return true;
	
}

	private function find_program($strProgram) {
		$key=md5($strProgram);
		if($_SESSION["find_program"]["$strProgram"]<>null){return $_SESSION["find_program"]["$strProgram"];}
		if($GLOBALS["find_program"]["$strProgram"]<>null){return $GLOBALS["find_program"]["$strProgram"];}	
		$value=$this->internal_find_program($strProgram);
		$_SESSION["find_program"]["$strProgram"]=$value;
		$GLOBALS["find_program"]["$strProgram"]=$value;
	    return $value;
	}
	
	private function internal_find_program($strProgram){
	  global $addpaths;	
	  $arrPath = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
	  if (addPaths !== false) {
	    $addpaths = explode(',', addPaths);
	    $arrPath = array_merge($arrPath, $addpaths);
	  }
	  if (function_exists("is_executable")) {
	    foreach($arrPath as $strPath) {
	      $strProgrammpath = $strPath . "/" . $strProgram;
	      if (is_executable($strProgrammpath)) {
	      	  return $strProgrammpath;
	      }
	    }
	  } else {
	   	return strpos($strProgram, '.exe');
	  }
	}
	
private function events($text){
		$GLOBALS["MOUNT_EVENTS"][]=$text;
		if(function_exists("writelogs")){
			writelogs("$text",__CLASS__,__FILE__,__LINE__);
			return;
		}
	
		if($this->eventspath<>null){
			$pid=getmypid();
			$date=date("H:i:s");
			$logFile=$this->eventspath;
			$size=filesize($logFile);
			if($size>1000000){unlink($logFile);}
			$f = @fopen($logFile, 'a');
			$line="$date $text\n";
			if($_GET["DEBUG"]){echo $line;}
			@fwrite($f,$line);
			@fclose($f);
		}

		if($_GET["logcomputer"]<>null){
			$f = @fopen($_GET["logcomputer"], 'a');
			@fwrite($f,$line);
			@fclose($f);
		}
		
		
		}	
	
	
}
?>