<?php

class usb{
	var $uuid;
	var $path;
	var $LABEL;
	var $TYPE;
	var $SEC_TYPE;
	var $mounted;
	var $vendor;
	var $model;
	var $product;
	var $manufacturer;
	var $id;
	var $speed;
	var $size;
	var $pourc;
	var $ID_MODEL;
	var $ID_VENDOR;
	var $img;
	var $ID_FS_TYPE;
	
public function usb($uuid=null){
		include_once(dirname(__FILE__).'/class.sockets.inc');
		
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
		if($uuid<>null){
			$this->uuid=$uuid;
			$this->lOADINFOS();
		}
		
		
	}
	
public function HASH_UUID_LIST(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	$tpl=new templates();
	if(!file_exists('ressources/usb.scan.inc')){return array();}		
	include(dirname(__FILE__)."/usb.scan.inc");	
	while (list ($uid, $array) = each ($_GLOBAL["usb_list"]) ){
		if(preg_match('#swap#',$array["TYPE"])){continue;}
		if(trim($array["mounted"])=='/'){continue;}
		$VENDOR=$array["ID_VENDOR"];
		if($mounted=="/"){continue;}
		if($TYPE=="swap"){continue;}
		$UUID=$array["UUID"];
		if($UUID==null){continue;}
		$TYPE=$array["TYPE"];
		$ID_MODEL=$array["ID_MODEL"];
		$ss=explode(";",$array["SIZE"]);
		$ARR[$UUID]="{$ss[0]}: $ID_MODEL ($TYPE)";
	}	
	
	return $ARR;
}
	
	
function getPartypename($id){
		if(strlen($id)==1){$id="0$id";}
		include_once(dirname(__FILE__)."/class.os.system.datas.inc");
		$p=new os_system_datas();
		$part_type=$p->part_type_array();
		return $part_type[$id];
	}
	
	
	public function isMounted($dev){
		if($dev==null){return false;}
		$sock=new sockets();
		$dev=$sock->getFrameWork("cmd.php?check-dev=$dev");
		$dev=str_replace("/","\/",$dev);
		//echo $dev;
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($num, $val) = each ($datas) ){
			if(preg_match("#$dev\s+(.+?)\s+(.+?)\s+#",$val,$re)){return $re[1];}
		}
		return false;
		
	}
	
	
	private function lOADINFOS(){
		if(empty($_GLOBAL["usb_list"])){
			if(!file_exists(dirname(__FILE__).'/usb.scan.inc')){return null;}
			include_once(dirname(__FILE__).'/usb.scan.inc');
			if(!is_array($_GLOBAL["usb_list"])){return null;}	
		}
		
		if(!is_array($_GLOBAL["disks_list"])){
			if(is_file(dirname(__FILE__).'/usb.scan.inc')){
				include dirname(__FILE__).'/usb.scan.inc';
				if(is_array($_GLOBAL["disks_list"]["$path"])){
					$this->ID_MODEL=$_GLOBAL["disks_list"]["$path"]["ID_MODEL"];
				}
			}	
		}
		unset($array);
		$array=$_GLOBAL["usb_list"][$this->uuid];
		if($GLOBALS["VERBOSE"]){echo "USB::lOADINFOS() $this->uuid: label:{$array["LABEL"]}\n";}
		if($GLOBALS["VERBOSE"]){echo "USB::lOADINFOS() $this->uuid: TYPE:{$array["TYPE"]}\n";}
		if($GLOBALS["VERBOSE"]){echo "USB::lOADINFOS() $this->uuid: SEC_TYPE:{$array["SEC_TYPE"]}\n";}
		if(isset($array["mounted"])){if($GLOBALS["VERBOSE"]){echo "USB::lOADINFOS() $this->uuid: mounted:{$array["mounted"]}\n";}}
		if($GLOBALS["VERBOSE"]){echo "USB::lOADINFOS() $this->uuid: ID_VENDOR:{$array["ID_VENDOR"]}\n";}
		if($GLOBALS["VERBOSE"]){echo "USB::lOADINFOS() $this->uuid: ID_FS_TYPE:{$array["ID_FS_TYPE"]}\n";}
		
		$this->path=trim($array["PATH"]);
		$this->img="usb-128.png";
	
		$this->LABEL=trim($array["LABEL"]);
		$this->TYPE=trim($array["TYPE"]);
		$this->SEC_TYPE=trim($array["SEC_TYPE"]);
		if(isset($array["mounted"])){$this->mounted=trim($array["mounted"]);}
		$this->ID_MODEL=trim($array["ID_MODEL"]);
		$this->ID_VENDOR=trim($array["ID_VENDOR"]);
		$this->ID_FS_TYPE=trim($array["ID_FS_TYPE"]);
	
	if(preg_match("#(.+?)[0-9]+$#",$this->path,$ri)){
		if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"])){
			if(is_array($_GLOBAL["disks_list"]["{$ri[1]}"]["PARTITIONS"])){
			$array2=$_GLOBAL["disks_list"]["{$ri[1]}"];
			$this->model=trim($array2["ID_MODEL_1"]);
			if($this->model==null){$this->model=trim($array2["ID_MODEL_2"]);}
			$this->size=trim($array2["SIZE"]);
			$this->img="disk-128.png";
			
			}
		}
	}		
		
	$tbl=explode(";",$array["model"]);
	
		if(is_array($tbl)){
			if(count($tbl)>3){
			if(trim($tbl[0])<>null){$this->vendor=trim($tbl[0]);}
			$this->model=trim($tbl[1]);
			$this->product=trim($tbl[2]);
			$this->manufacturer=trim($tbl[3]);
			$this->id=trim($tbl[4]);
			$this->speed=trim($tbl[5]);
			if($this->speed=="Mbps"){$this->speed="?? Mbps";}
			}
		}
		
	if(preg_match("#(.+?);(.+?);(.+?);([0-9]+)%#",$array["SIZE"],$re)){
	 $this->size=$re[1];
	 $this->pourc=$re[4];
	}


	if($this->LABEL==null){$this->LABEL=$this->path;}
	if(trim($this->model)==null){if($this->ID_MODEL<>null){$this->model=$this->ID_MODEL;}}
	if(trim($this->vendor)==null){if($this->ID_VENDOR<>null){$this->vendor=$this->ID_VENDOR;}}
	
		
		
	}
	
}




function ProcessExistsBypattern($pattern){
	$pgrep=LOCATE_PGREP();
	$array=ProcessListBypattern($pattern);
	events_class("ProcessExistsBypattern:: found an array of ". count($array)." rows");
	if(count($array)>0){return true;}
	return false;
}

function ProcessListBypattern($pattern){
	if($_GET["LOCATE_PGREP"]==null){$_GET["LOCATE_PGREP"]=LOCATE_PGREP();}
	$pgrep=$_GET["LOCATE_PGREP"];
	$tmp=OS_FILE_TEMP();
	$cmd="$pgrep -f \"$pattern\" >$tmp 2>&1";
	events_class("ProcessListBypattern:: $cmd");
	system($cmd);
	$datas=explode("\n",@file_get_contents($tmp));
	@unlink($tmp);
	while (list ($index, $line) = each ($datas) ){
		
		if(preg_match("#^([0-9]+)#",$line,$re)){
			events_class("ProcessListBypattern:: found={$re[1]}");
			$array[]=$re[1];
		}
		
	}
	
	if(!is_array($array)){return array();}
	return $array;
}

	function OS_FILE_TEMP(){
		if(function_exists("sys_get_temp_dir")){
			return tempnam(sys_get_temp_dir(), 'artica-php');
		}
		return "/tmp/artica-php-".md5(date("Y-m-d H:i:s"));
		
	}



function LOCATE_PGREP(){
	if(is_file("/usr/bin/pgrep")){return "/usr/bin/pgrep";}
	if(is_file("/bin/pgrep")){return "/bin/pgrep";}
}

function ListOnlyDirectories($path){
	$dir_handle = @opendir($path);
	while ($file = readdir($dir_handle)) {
		if($file=='.'){continue;}
		if($file=='..'){continue;}
		
  		$results[]=$file;
	}

	closedir($dir_handle);
	if(!is_array($results)){return array();}
	return $results;
	
}

function events_class($text){
	if(!is_array($_GET["FILE_DEBUG"])){return ;}
	$f=new debuglogs();
	for($i=0;$i<count($_GET["FILE_DEBUG"]);$i++){
			$f->events($text,$_GET["FILE_DEBUG"][$i]);
		}
	}
		


function system_is_overloaded($file=null){
	if(!isset($GLOBALS["CPU_NUMBER"])){
			include_once(dirname(__FILE__).'/class.users.menus.inc');
			$users=new usersMenus();
			$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
	}
	
	if(function_exists("debug_backtrace")){
		try {
			$trace=@debug_backtrace();
			if(isset($trace[1])){$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";}
			} catch (Exception $e) {}
		}	
	
	$d=new debuglogs();
	
	$array_load=sys_getloadavg();
	$internal_load=$array_load[0];
	$cpunum=$GLOBALS["CPU_NUMBER"]+0.5;
	if($file==null){$file=basename(__FILE__);}
	$d->events("Load: $internal_load max=$cpunum  $called","/var/log/artica-postfix/syslog.log");

	if($internal_load>$cpunum){
		$GLOBALS["SYSTEM_INTERNAL_LOAD"]=$internal_load;
		$d->events("Load: OVERLOADED $called","/var/log/artica-postfix/syslog.log");
		if($GLOBALS["VERBOSE"]){echo "$file system_is_overloaded():: Load: $internal_load max=$cpunum SYSTEM OVERLOADED $called\n";}
		$d->events("$file system_is_overloaded():: Load: $internal_load max=$cpunum SYSTEM OVERLOADED $called","/var/log/artica-postfix/artica-status.debug");
		return true;
		
	}
	//$d->events("$file system_is_overloaded():: CPU: $cpunum, load: $internal_load, max=$cpunum GOOD","/var/log/artica-postfix/artica-status.debug");
	return false;

	
}


function systemMaxOverloaded(){
	$d=new debuglogs();
	$array_load=sys_getloadavg();
	$internal_load=$array_load[0];
	if(!isset($GLOBALS["systemMaxOverloaded"])){
			$sock=new sockets();
			$GLOBALS["systemMaxOverloaded"]=$sock->GET_INFO("systemMaxOverloaded");
	}
	if($GLOBALS["systemMaxOverloaded"]==null){$GLOBALS["systemMaxOverloaded"]=17;}
	if($internal_load>$GLOBALS["systemMaxOverloaded"]){
		if($GLOBALS["VERBOSE"]){echo "systemMaxOverloaded():: Load: $internal_load/{$GLOBALS["systemMaxOverloaded"]} SYSTEM REALLY OVERLOADED die all processes during this load\n";}
		$GLOBALS["SYSTEM_INTERNAL_LOAD"]=$internal_load;
		$d->events(basename(__FILE__)." systemMaxOverloaded():: Load: $internal_load/{$GLOBALS["systemMaxOverloaded"]} SYSTEM REALLY OVERLOADED die all processes during this load","/var/log/artica-postfix/artica-status.debug");
		return true;
	}
	
}

function LOCATE_PROGRAM($file){
	$array[]="/usr/sbin";
	$array[]="/usr/bin";
	$array[]="/bin";
	$array[]="/sbin";
	$array[]="/usr/local/bin";
	$array[]="/usr/local/sbin";
	$array[]="/usr/bin/X11";
	while (list ($num, $path) = each ($array) ){if(is_file("$path/$file")){return "$path/$file";}}
	}

function LOCATE_NOHUP(){
	return LOCATE_PROGRAM("nohup");
	}
	
function LOCATE_SYSLOG_CONF(){
	if(is_file('/etc/syslog.conf')){return '/etc/syslog.conf';}
	if(is_file('/etc/syslog-ng/syslog-ng.conf')){return '/etc/syslog-ng/syslog-ng.conf';}
	if(is_file('/etc/rsyslog.d/50-default.conf')){return '/etc/rsyslog.d/50-default.conf';}
	if(is_file('/etc/rsyslog.conf')){return '/etc/rsyslog.conf';}
}
function LOCATE_SYSLOG_NG_CONF(){
  if(is_file('/etc/syslog-ng/syslog-ng.conf')){ return '/etc/syslog-ng/syslog-ng.conf';}
  if(is_file('/etc/syslog-ng.conf')){return '/etc/syslog-ng.conf';}
}
//############################################################################## 
//############################################################################## 
function MAILLOG_FROM_SYSLOGNG(){
	$path=LOCATE_SYSLOG_NG_CONF();
	   if(!is_file($path)){return null;}
	   $l=explode("\n",@file_get_contents($path));
	   while (list ($index, $line) = each ($l) ){
	   		if(preg_match("#^destination.+?mail\s+.+?\((.+?)\)#",$line,$re)){
		   		$file=$re[1];
		   		$file=str_replace('"','',$file);
		   		$file=str_replace("'",'',$file);
		   		return $file;
	   		}
	   }
   }
//############################################################################## 
function LOCATE_PHP5_BIN(){
  if(isset($GLOBALS["LOCATE_PHP5_BIN"])){return $GLOBALS["LOCATE_PHP5_BIN"];}
  if(is_file('/usr/bin/php5')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php5';return '/usr/bin/php5';}
  if(is_file('/usr/bin/php')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php';return '/usr/bin/php';}
  if(is_file('/usr/local/apache-groupware/php5/bin/php')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php5';return '/usr/local/apache-groupware/php5/bin/php';}
  
}

function LOCATE_MAILLOG_PATH(){

if($_GET["CLASS_DEBUG"]){echo "MAILLOG_PATH()\n";}
	if(is_file(LOCATE_SYSLOG_NG_CONF())){
		if($_GET["CLASS_DEBUG"]){echo "RETURN MAILLOG_FROM_SYSLOGNG()\n";}
	   return MAILLOG_FROM_SYSLOGNG();
		}
	
	$logconfig=LOCATE_SYSLOG_CONF();
	$filedatas=@file_get_contents($logconfig);
	if($_GET["CLASS_DEBUG"]){echo "$logconfig=". strlen($filedatas)." bytes\n";}
	
	if(preg_match("#mail\.=info.+?-([\/a-zA-Z_0-9\.]+)?#is",$filedatas,$re)){
		return $re[1];
	}
	  
	if(preg_match("#mail\.\*.+?-([\/a-zA-Z_0-9\.]+)?#is",$filedatas,$re)){
		return $re[1];
	}
	if(preg_match('#destination mailinfo[\s\{a-z]+\("(.+?)"#is',$filedatas,$re)){
		return $re[1];
	}
	
if($_GET["CLASS_DEBUG"]){echo "RETURN FAILED FROM $logconfig\n";}

}  

?>
