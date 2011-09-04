<?php
ini_set("memory_limit","400M");
if(!isset($GLOBALS["DEBUG_INCLUDES"])){$GLOBALS["DEBUG_INCLUDES"]=false;}
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::".dirname(__FILE__)."/class.semaphores.php\n";}
include_once(dirname(__FILE__)."/class.semaphores.php");
include_once(dirname(__FILE__)."/logs.inc");

error_reporting(E_ALL & ~E_NOTICE);
if(function_exists("posix_getuid")){if(posix_getuid()<>0){if(!$GLOBALS["KAV4PROXY_NOSESSION"]){session_start();}}}
class sockets{
	var $remote_ip;
	var $remote_port;
	var $error=false;
	var $semaphore_key=1376880653;
	var $semaphore_memory=9024000;
	var $ArticaMetaEnabled=0;
	
	function sockets(){
		if(!isset($GLOBALS["VERBOSE_MASTER"])){$GLOBALS["VERBOSE_MASTER"]=false;}
		if(!isset($GLOBALS["DEBUG"])){$GLOBALS["DEBUG"]=false;}
		$GLOBALS["posix_getuid"]=1000;
		if(function_exists("posix_getuid")){
			$GLOBALS["posix_getuid"]=posix_getuid();
			$name=posix_getpwuid($GLOBALS["posix_getuid"]);
		}
		if($GLOBALS["DEBUG"]){
			$trace=debug_backtrace();
			if(isset($trace[1])){
				$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			
				echo "Instanciate sockets() {$GLOBALS["posix_getuid"]}={$name["name"]} $called\n";
			}
		}
		
		
		/*$trace=debug_backtrace();
		$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";	
		include_once(dirname(__FILE__)."/logs.inc");
		writelogs("Instanciate sockets() {$GLOBALS["posix_getuid"]}={$name["name"]} $called",__CLASS__.'/'.__FUNCTION__,__FILE__);
		*/
		
		$this->remote_ip=$this->APC_GET("ARTICA_DAEMON_IP");
		$this->remote_port=$this->APC_GET("ARTICA_DAEMON_PORT");
		$this->ArticaMetaEnabled=$this->APC_GET("ArticaMetaEnabled");
		
		if($this->remote_ip==null){
			if(!file_exists(dirname(__FILE__) . '/settings.inc')){
			if(function_exists("writelogs")){writelogs("ERROR: unable to stat " . dirname(__FILE__) . '/settings.inc',__CLASS__ . "=>" . __FUNCTION__,__FILE__);};return false;}
			include(dirname(__FILE__) . '/settings.inc');
			if($_GLOBAL["ARTICA_DAEMON_IP"]=='0.0.0.0'){$this->remote_ip='127.0.0.1';}else{$this->remote_ip=$_GLOBAL["ARTICA_DAEMON_IP"];}
			$this->remote_port=$_GLOBAL["ARTICA_DAEMON_PORT"];
			if($_GLOBAL["ARTICA_SECOND_PORT"]>0){$this->remote_port=$_GLOBAL["ARTICA_SECOND_PORT"];}
			if($this->remote_ip==null){$this->remote_ip="127.0.0.1";}
			$this->APC_SAVE("ARTICA_DAEMON_IP",$this->remote_ip);
			$this->APC_SAVE("ARTICA_DAEMON_PORT",$this->remote_port);
			}
	}
		

function file_ext($filename){
        return strtolower(str_replace(".", "", strrchr($filename, ".")));
}		
		
function download_attach($folder,$filename){
	$url_stuff="http://127.0.0.1:$this->remote_port/queue/bightml/$folder/$filename";
	$fileext=$this->file_ext($filename);
	
	$filemime=file_get_contents(dirname(__FILE__) . '/databases/extentions-mime.db');
	$tb=explode("\n",$filemime);
	while (list ($num, $ligne) = each ($tb) ){
		if(preg_match("#$fileext\s+(.+)#i",$ligne,$re)){
			$mime=$re[1];
		}
	}
	if($mime==null){$mime="application/octet-stream";}
	header("Content-type: $mime");
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	$binary_data = file_get_contents($url_stuff);
	echo $binary_data;
	
}

function download_srvfile($path){
	include(dirname(__FILE__) . '/settings.inc');
	$md=md5($_GLOBAL["ldap_password"].$_GLOBAL["ldap_root_database"]);
	$url_stuff="http://127.0.0.1:$this->remote_port/$md$path";
	$pathbase=basename($path);
	$fileext=$this->file_ext($pathbase);
	
	$filemime=file_get_contents(dirname(__FILE__) . '/databases/extentions-mime.db');
	$tb=explode("\n",$filemime);
	while (list ($num, $ligne) = each ($tb) ){
		if(preg_match("#$fileext\s+(.+)#i",$ligne,$re)){
			$mime=$re[1];
		}
	}
	if($mime==null){$mime="application/octet-stream";}
	header("Content-type: $mime");
	header('Content-Disposition: attachment; filename="'.$pathbase.'"');
	$binary_data = file_get_contents($url_stuff);
	if(function_exists("writelogs")){writelogs("$url_stuff ->$mime (".strlen($binary_data).')',__CLASS__.'/'.__FUNCTION__,__FILE__);}
	echo $binary_data;
	
}
		
function downloadFile($url,$hostname)  {
   $url_stuff="http://$hostname:$this->remote_port/$url";
   header("Content-type: image/png");
   $binary_data=@file_get_contents($url_stuff);
   if(strlen($binary_data)==0){
   	$binary_data=@file_get_contents("/usr/share/artica-postfix/img/kas-graph-no-datas.png");
   }
   
   
   echo $binary_data;
}

   function SaveConfigFile($datas,$keyConfig){
   	if(!function_exists("writelogs")){include(dirname(__FILE__)."/logs.inc");}
   	 $tmp=md5($datas.$keyConfig);
   	 if(!$this->WriteConfigToFile($tmp,$datas)){
   	 	if(function_exists("writelogs")){writelogs("Writing $tmp ". strlen($tmp)." bytes FAILED",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
   	 	echo "Unable to save $tmp";
   	 	return false;
   	 }
   	 $this->getFrameWork("cmd.php?SaveConfigFile=$tmp&key=$keyConfig");
   	 $this->SET_APC_STORE($keyConfig,$datas);
   		if($this->ArticaMetaEnabled==1){
   			$this->getFrameWork("cmd.php?artica-meta-push=$keyConfig");
   		}
   	 
   	 $this->DeleteCache();
   	 return true;
   }
   
   function SaveClusterConfigFile($datas,$keyConfig){
   	 $tmp=md5($datas.$keyConfig);
   	 if(!$this->WriteConfigToFile($tmp,$datas)){
   	 	echo "Unable to save $tmp";
   	 	return false;
   	 }
   	 $this->getFrameWork("cmd.php?SaveClusterConfigFile=$tmp&key=$keyConfig");
   	 $this->DeleteCache();
   	 return true;
   }   
   
   function SaveDansGuardianFile($datas,$filename){
   		$tmp=md5($datas.$filename);
   	 	if(!$this->WriteConfigToFile($tmp,$datas)){
   	 		echo "Unable to save $tmp";
   	 		return false;
   	 	}  

   	 	$this->getfile("SaveDansGuardianFile:$tmp;$filename");
   	
   }
   
   
   function WriteConfigToFile($myFile,$stringData){
   	if(!function_exists("writelogs")){include(dirname(__FILE__)."/logs.inc");}
   	if(!is_dir(dirname(__FILE__).'/conf')){mkdir(dirname(__FILE__).'/conf');}
   	if(!is_dir(dirname(__FILE__).'/conf')){
   		echo "Unable to create dir ".dirname(__FILE__).'/conf'."\n";
   		return false;
   	}
   	
   	$myFile=dirname(__FILE__).'/conf/'.$myFile;
   	file_put_contents($myFile,$stringData);
   	if(!is_file($myFile)){
   	if(function_exists("writelogs")){
   		echo "Writing $myFile ". strlen($stringData)." bytes failed\n";
		writelogs("Writing $myFile ". strlen($stringData)." bytes failed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
   		return false;
   	}
   	
   	
   	chmod($myFile,0755);
	//if(function_exists("writelogs")){
	//	writelogs("Writing $myFile ". strlen($stringData)." bytes ok",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	//}
	return true;
   	
   }

   function GET_INFO($key,$nocache=false){
   	if(!isset($GLOBALS["posix_getuid"])){$GLOBALS["posix_getuid"]=posix_getuid();}
   	$uid=null;
  	if(isset($_SESSION["uid"])){ $uid=$_SESSION["uid"];}
    

   	if(!$nocache){
			$data=$this->APC_GET($key);
   			if($data<>null){return $data;}
   			if(isset($GLOBALS["GET_INFO"][$key])){return $GLOBALS["GET_INFO"][$key];}
   			
   			if($GLOBALS["posix_getuid"]==0){
				$value=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/$key"));
   				if($key=="SystemV5CacheEnabled"){if($value==null){$value=0;}}
   				$GLOBALS["GET_INFO"][$key]=$value;
   				if($GLOBALS["LOGON-PAGE"]){error_log("->SET_APC_STORE($key,$value) ". basename(__FILE__). " line ". __LINE__);}
   				$this->SET_APC_STORE($key,$value);
   				return $value;
   				}
   		}
   			
   	
	if($GLOBALS["posix_getuid"]==0){
		$value=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/$key"));
		if($key=="SystemV5CacheEnabled"){if($value==null){$value=0;}}
   		$GLOBALS["GET_INFO"][$key]=$value;
		if($GLOBALS["VERBOSE_MASTER"]){echo "\$GLOBALS[\"GET_INFO\"][$key]=$value ". basename(__FILE__). " line ".__LINE__."\n";}
   		$this->SET_APC_STORE($key,$value);
   		return $value;
   	}   	
   			
    	
   	$value=trim($this->getFrameWork("getinfos.php?key=$key&uid=".$uid));
    $GLOBALS["GET_INFO"][$key]=$value;
   	if(!$nocache){
   		$this->SET_APC_STORE($key,$value);
   	}
   	return trim($value);
   
   	}
   	
   function GET_CLUSTER($key){
   		if(isset($GLOBALS["GET_CLUSTER"][$key])){
   			return $GLOBALS["GET_CLUSTER"][$key];
   		}
   	
   	
   		if(posix_getuid()==0){
   			$value=trim(@file_get_contents("/etc/artica-cluster/$key"));
   			$GLOBALS["GET_CLUSTER"][$key]=$value;
   			return $value;
   		}
   	

   	$value=trim($this->getFrameWork("getinfos.php?cluster-key=$key"));
    $GLOBALS["GET_CLUSTER"][$key]=$value;
   	return $value;
   
   	}   	
   	
   function SET_INFO($key,$value){
   	$trace=debug_backtrace();
	if(isset($trace[1])){$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";}
   	
   		if($key=="SQUIDEnable"){
   			if($value==1){
   				
					writelogs("SET SQUID $key $value $called",__CLASS__ . "/" . __FUNCTION__,__FILE__);				
					if(class_exists("unix")){
						$unix=new unix();
						$unix->send_email_events("SQUID was re-activated", $called, "squid");
					}
				}
   		}
   	
   	
   	    if(!isset($GLOBALS["posix_getuid"])){$GLOBALS["posix_getuid"]=1000;if(function_exists("posix_getuid")){$GLOBALS["posix_getuid"]=posix_getuid();}}
   		if($GLOBALS["posix_getuid"]==0){
   			@file_put_contents("/etc/artica-postfix/settings/Daemons/$key",$value);
   			$this->SET_APC_STORE($key,$value);
   			if($this->ArticaMetaEnabled==1){$this->getFrameWork("cmd.php?artica-meta-push=$key");}
   			return true;
   		}
   		
   		if(strlen($value)<10){
   			writelogs("SET \"$key\"=\"$value\" $called",__CLASS__ . "=>" . __FUNCTION__,__FILE__,__LINE__);
   			
   		}else{
   			writelogs("SET $key=".strlen($value)." bytes $called",__CLASS__ . "=>" . __FUNCTION__,__FILE__,__LINE__);
   		}
   	   	
   	   	$file=md5($value);
   	   	@mkdir(dirname(__FILE__)."/logs");
   	   	$targetedFile=dirname(__FILE__)."/logs/$key";
   	   	file_put_contents($targetedFile,$value);
   	   	if(!is_file($targetedFile)){
   	   		writelogs("SET $key $targetedFile no such file",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
   	   	}
   		$this->getFrameWork("setinfos.php?key=$key&path=$targetedFile");
   		if($value==null){$value=' ';}
   		$this->SET_APC_STORE($key,$value);
   		if($this->ArticaMetaEnabled==1){
   			$this->getFrameWork("cmd.php?artica-meta-push=$key");
   		}
		$this->DeleteCache();
   		
   		
   		
   }
   function SET_CLUSTER($key,$value){
   	if(strlen($value)>0){
   		if(posix_getuid()==0){
   			file_put_contents("/etc/artica-cluster/$key",$value);
   			return true;
   		}
   		
   	   	writelogs("SET $key=".strlen($value)." bytes",__CLASS__ . "=>" . __FUNCTION__,__FILE__);
   	   	$file=md5($value);
   	   	
   	   	@mkdir(dirname(__FILE__)."/logs/cluster",0755,true);
   	   	@file_put_contents(dirname(__FILE__)."/logs/cluster/$key",$value);
   		$this->getFrameWork("setinfos.php?cluster-key=$key");
   		$this->DeleteCache();
   		}
   }   
   
   function MYSQL_INFO($key){
   	if(isset($GLOBALS["MYSQL_INFO"])){
   		if($GLOBALS["MYSQL_INFO"][$key]<>null){return $GLOBALS["MYSQL_INFO"][$key];}
   	}
   	
   	if(posix_getuid()==0){return $this->GET_MYSQL_ROOT($key);}
   	$answer=$this->getfile("MSQLINFOS:$key");
   	if($answer==null){$answer='!nil';}
   	$GLOBALS["MYSQL_INFO"][$key]=$answer;
   	return $answer;
   	}
   
   function DeleteCache(){
   	unset($GLOBALS["GET_INFO"]);
   	unset($GLOBALS["MYSQL_INFO"]);
   	unset($_SESSION["APC"]);
   	if(!is_array($_SESSION)){return null;}
   	while (list ($num, $val) = each ($_SESSION) ){
   			if(preg_match('#CACHEINFOS_.+#',$num)){unset($_SESSION[$num]);}
			if(preg_match("#^cachepage_(.+)#",$num)){unset($_SESSION[$num]);}
		}   			
   		
   }
   
   
function GET_PERFS($key){
	$value=$this->APC_GET("PERFORMANCES_{$key}");
	if($value==null){
		if(!class_exists("Bs_IniHandler")){include_once(dirname(__FILE__)."/class.ini.inc");}
		$ini=new Bs_IniHandler("/etc/artica-postfix/performances.conf");
		$value=$ini->get("PERFORMANCES",$key);
		$this->SET_APC_STORE("PERFORMANCES_{$key}",$value);
	}
	return $value;
	
}
function GET_NOTIFS($key){
	$value=$this->APC_GET("NOTIFS_{$key}");
	if($value==null){
		if(!class_exists("Bs_IniHandler")){include_once(dirname(__FILE__)."/class.ini.inc");}
		$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
		$value=$ini->get("SMTP",$key);
		$this->SET_APC_STORE("NOTIFS_{$key}",$value);
	}
	return $value;
	
}
   
   
function GET_MYSQL_ROOT($key){
	if(isset($GLOBALS["MYSQL_INFO_ROOT"][$key])){
		if($GLOBALS["MYSQL_INFO_ROOT"][$key]<>null){return $GLOBALS["MYSQL_INFO_ROOT"][$key];}
	}
	if($key=='server'){$key='mysql_server';}
	if($key=='root'){ $key='database_admin';}
	if($key=='password'){ $key='database_password';}
	 if(is_file('/etc/artica-postfix/settings/Mysql/'.$key)){
	      $datas=trim(file_get_contents('/etc/artica-postfix/settings/Mysql/'.$key));
	   }else{
	   $ini=new Bs_IniHandler("/etc/artica-postfix/artica-mysql.conf");
	   $datas=trim($ini->get("MYSQL",$key));
	  }
	   
	if ($key=='mysql_server'){if($datas==null){$datas="127.0.0.1";}}
	if ($key=='port'){if($datas==null){$datas="3306";}}
	if ($key=='database_admin'){if($datas==null){$datas="root";}}
	$GLOBALS["MYSQL_INFO_ROOT"][$key]=$datas;
	return $datas;   
}

	function getFrameWork($uri){
		$line=null;
		if($uri=="cmd.php?uri=artica_version"){
			if(function_exists("apc_store")){
   				$value=apc_fetch("artica_version");
   				if($value<>null){return $value;}
   			}
		}
		

		$fp = @fopen ("http://127.0.0.1:47980/$uri", "r");

		if (!$fp) {
			error_log("ERROR: unable to open remote file http://127.0.0.1:47980/$uri $errno $errstr");
			if(function_exists("writelogs")){writelogs("ERROR: unable to open remote file http://127.0.0.1:47980/$uri",
			__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
			$this->error=true;
			return false;
		}
		
		while (!feof ($fp)) {
			$line =$line .  fgets ($fp, 1024) . "\n";
		}
		
		fclose($fp);
		if(trim($line)==null){return null;}
		if(trim($line)=="<articadatascgi></articadatascgi>"){return null;}

		if(preg_match('#<articadatascgi>(.+)</articadatascgi>#s',$line,$regs)){
			$results=trim($regs[1]);
			//writelogs("receive ".strlen($results),__FUNCTION__,__FILE__);
			if($uri=="cmd.php?uri=artica_version"){$this->SET_APC_STORE("artica_version",$results);}
			return $results;
		}
		
		if(preg_match('#<articadatascgi>(.+)</\s+articadatascgi>#s',$line,$regs)){
				$results=trim($regs[1]);
				return $results;
		}			
		
		if(preg_match('#<articadatascgi>(.+)</articadat#s',$line,$regs)){$results=trim($regs[1]);return $results;}
		if(preg_match('#<articadatascgi>(.+)</articada\s+tascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}
		if(preg_match('#<articadatascgi>(.+)</articad\s+atascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}	
		if(preg_match('#<articadatascgi>(.+)</artica\s+datascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}			
		if(preg_match('#<articadatascgi>(.+)</articada\s+scgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}			
		if(preg_match('#<articadatascgi>(.+)</artic\s+adatascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}
		if(preg_match('#<articadatascgi>(.+)</arti\s+cadatascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}
		if(preg_match('#<articadatascgi>(.+)</art\s+icadatascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}		
		if(preg_match('#<articadatascgi>(.+)</ar\s+ticadatascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}
		if(preg_match('#<articadatascgi>(.+)</a\s+rticadatascgi>#s',$line,$regs)){$results=trim($regs[1]);return $results;}
		writelogs("unable to preg_match \"$line\" uri=$uri",__FUNCTION__,__FILE__);
		
	}
	
	private function SYSTEMV5_ENABLED(){
		if(!isset($GLOBALS["LOGON-PAGE"])){$GLOBALS["LOGON-PAGE"]=false;}
		if(!isset($GLOBALS["VERBOSE_MASTER"])){$GLOBALS["VERBOSE_MASTER"]=false;}
		if(!isset($GLOBALS["SystemV5CacheEnabled"])){$GLOBALS["SystemV5CacheEnabled"]=0;}
		if($GLOBALS["posix_getuid"]>0){
			if($GLOBALS["VERBOSE_MASTER"]){echo "->SYSTEMV5_ENABLED() posix={$GLOBALS["posix_getuid"]} ". basename(__FILE__). " line ".__LINE__."\n";}
			if($GLOBALS["LOGON-PAGE"]){error_log("->SYSTEMV5_ENABLED() posix={$GLOBALS["posix_getuid"]}". basename(__FILE__). " line ". __LINE__);}
		}
		
		if($GLOBALS["SystemV5CacheEnabled"]<>null){
			if($GLOBALS["SystemV5CacheEnabled"]==1){
				if($GLOBALS["LOGON-PAGE"]){error_log("->SYSTEMV5_ENABLED() {$GLOBALS["SystemV5CacheEnabled"]} TRUE ". basename(__FILE__). " line ". __LINE__);}
				return true;
			
			}
			if($GLOBALS["VERBOSE_MASTER"]){echo "->SYSTEMV5_ENABLED() FALSE posix={$GLOBALS["posix_getuid"]} ". basename(__FILE__). " line \n";}
			return false;
		}
		
		
		
		if($GLOBALS["posix_getuid"]>0){
			if(strlen(trim($_SESSION["SystemV5CacheEnabled"]))==0){
				if($GLOBALS["VERBOSE_MASTER"]){echo "->this->GET_INFO(SystemV5CacheEnabled) posix={$GLOBALS["posix_getuid"]} ". basename(__FILE__). " line ".__LINE__."\n";}
				if($GLOBALS["LOGON-PAGE"]){error_log("->this->GET_INFO(SystemV5CacheEnabled) ". basename(__FILE__). " line ". __LINE__);}
				$_SESSION["SystemV5CacheEnabled"]=$this->GET_INFO("SystemV5CacheEnabled",true);
				if(strlen(trim($_SESSION["SystemV5CacheEnabled"]))==0){$_SESSION["SystemV5CacheEnabled"]=0;}
				$GLOBALS["SystemV5CacheEnabled"]=$_SESSION["SystemV5CacheEnabled"];
			}else{
				if($GLOBALS["VERBOSE_MASTER"]){echo "->return \$_SESSION[\"SystemV5CacheEnabled\"] posix={$GLOBALS["posix_getuid"]} ". basename(__FILE__). " line ".__LINE__."\n";}
				$GLOBALS["SystemV5CacheEnabled"]=$_SESSION["SystemV5CacheEnabled"];
			}
			
			
			
		}else{
			if($GLOBALS["VERBOSE_MASTER"]){echo "->\$this->GET_INFO(\"SystemV5CacheEnabled\",true) posix={$GLOBALS["posix_getuid"]} ". basename(__FILE__). " line ".__LINE__."\n";}
			$GLOBALS["SystemV5CacheEnabled"]=$this->GET_INFO("SystemV5CacheEnabled",true);
			if(strlen(trim($GLOBALS["SystemV5CacheEnabled"]))==0){$GLOBALS["SystemV5CacheEnabled"]=0;}
			
		}
		
		
		if($GLOBALS["SystemV5CacheEnabled"]<>null){
			if($GLOBALS["SystemV5CacheEnabled"]==1){return true;}
			return false;
		}	
		
	}
	
	
	function DATA_CACHE_SAVE($key,$value,$IsAnArray=false){
		if($GLOBALS["LOGON-PAGE"]){error_log("DATA_CACHE_SAVE() -> $key". basename(__FILE__). " line ". __LINE__);}
		if(!$this->SYSTEMV5_ENABLED()){
			$_SESSION["APC"][$key]=$value;
			$_SESSION["APC"]["{$key}-time"]=time();
			return;
		}
		if($GLOBALS["LOGON-PAGE"]){error_log("DATA_CACHE_SAVE() -> semaphores". basename(__FILE__). " line ". __LINE__);}
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory);
		$shm->SET($key,$value);
		$shm->SET("{$key}-time",time());
		$shm->CLOSE();
		}
		
	function DATA_CACHE($key,$IsAnArray=false,$maxtime=0){
			if($GLOBALS["LOGON-PAGE"]){error_log("DATA_CACHE() -> $key". basename(__FILE__). " line ". __LINE__);}
			if(!$this->SYSTEMV5_ENABLED()){
				if(isset($_SESSION["APC"][$key])){$value=$_SESSION["APC"][$key];}
				if(isset($_SESSION["APC"]["{$key}-time"])){$data1=$_SESSION["APC"]["{$key}-time"];}
				$data2 = time();
				if(isset($data1)){
					$difference = ($data2 - $data1); 
					$min=round($difference/60);	 
					if($min>$maxtime){$value=null;}
					return $value;
				}   			
   			}
		if($GLOBALS["LOGON-PAGE"]){error_log("->semaphores()". basename(__FILE__). " line ". __LINE__);}	
   		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory);
		$value=$shm->GET($key);
		$time=$shm->GET("{$key}-time");
		$shm->CLOSE();
   		if($maxtime>0){
	 		$data1 = $time;
			$data2 = time();
			$difference = ($data2 - $data1); 	 
			$min=round($difference/60);
			if($min>$maxtime){
				writelogs("Release $key time: $min exceed $maxtime",__CLASS__,__FILE__,__LINE__);
				$value=null;}   			
   			}

   		if(!$IsAnArray){return trim($value);}
   		return $value;	
		
	}		
	
	function LANGUAGE_CACHE_IMPORT($array_keys,$lang){
		if($GLOBALS["LOGON-PAGE"]){error_log("LANGUAGE_CACHE_IMPORT() ". basename(__FILE__). " line ". __LINE__);}
		if(!$this->SYSTEMV5_ENABLED()){return null;}
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,2);
		$shm->SET($lang,$array_keys);
		$shm->CLOSE();
	}	
	


	function LANGUAGE_DUMP($lang){
		if($lang=="undefined"){return;}
		if($GLOBALS["LOGON-PAGE"]){error_log("LANGUAGE_DUMP() ". basename(__FILE__). " line ". __LINE__);}
		if($this->SYSTEMV5_ENABLED()){
			$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,2);
			if($shm->ok){
				$data=$shm->GET($lang);
				$shm->CLOSE();
			}
		}
		
		if($GLOBALS["DEBUG"]){echo "LANGUAGE_DUMP Memory store ". count($data)." rows\n";}
		
		if(count($data)<100){
			writelogs("[$lang]:: now enough row for language $lang",__CLASS__."/".__FUNCTION__,__FILE__);
			$filepath=dirname(__FILE__)."/language/$lang.db";
			if(!is_file("$filepath")){
				if($GLOBALS["posix_getuid"]>0){
					$file=base64_decode($this->getFrameWork("cmd.php?GetLangagueFile=$lang"));
					writelogs("[$lang]:: Get language file from framework ->".strlen($file)." bytes length",__CLASS__."/".__FUNCTION__,__FILE__);
					if(strlen($file)<500){
						if($GLOBALS["DEBUG"]){echo "LANGUAGE_DUMP unable to stat $filepath\n";}
						}
					}
				}else{
					$file=@file_get_contents(dirname(__FILE__)."/language/$lang.db");
					if($GLOBALS["DEBUG"]){echo "LANGUAGE_DUMP File: $filepath ". strlen($file)." bytes\n";}					
				}
			
			$data=unserialize($file);
			if(!is_array($data)){
				writelogs("[$lang]:: Get language file from framework Not an array !",__CLASS__."/".__FUNCTION__,__FILE__);
			}
			
			
			if(count($data)<500){
				writelogs("[$lang]:: Get language file from framework Not an array !",__CLASS__."/".__FUNCTION__,__FILE__);
			}
			if($GLOBALS["DEBUG"]){echo "LANGUAGE_DUMP Serialised=". count($data)." rows\n";}
			$this->LANGUAGE_CACHE_IMPORT($lang,$data);
		}
		return $data;
		
	}	
	
	
	function DATA_CACHE_EMPTY(){
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,1);
		$shm->removekey(1);
		$shm->Delete();
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,2);
		$shm->removekey(2);
		$shm->Delete();
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,2);
		$shm->removekey(3);
		$shm->Delete();
		
	}
	

	
	public function SHARED_INFO_BYTES($numeric_key=1){
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,$numeric_key);
		$array=serialize($shm->DUMP());
		return strlen(trim($array));	
	}
	
	
	function SET_APC_STORE($key,$value){
		
		if($key<>"SystemV5CacheEnabled"){
			if($this->SYSTEMV5_ENABLED()){
				if($GLOBALS["LOGON-PAGE"]){error_log("semaphores($this->semaphore_key,$this->semaphore_memory,3)". basename(__FILE__). " line ". __LINE__);}
				$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,3);
				$shm->SET($key,$value);
				$shm->SET("{$key}-time",time());
				if($GLOBALS["LOGON-PAGE"]){error_log("shm->CLOSE(); ". basename(__FILE__). " line ". __LINE__);}
				$shm->CLOSE();
			}
		}
		
		$_SESSION["APC"][$key]=$value;
		$_SESSION["APC"]["{$key}-time"]=time();
		$GLOBALS["APC"][$key]=$value;
		$GLOBALS["APC"]["{$key}-time"]=time();		
		
		}
		
	function APC_CLEAN(){
		unset($_SESSION["APC"]);
		$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,3);
		$shm->removekey();
		$shm->Delete();
		$shm->CLOSE();
		
	}
	
	function APC_GET($key,$maxtime=0){
		if(!isset($GLOBALS["VERBOSE"])){$GLOBALS["VERBOSE"]=false;}
		$value=null;
		 if(isset($_SESSION["APC"][$key])){
				$value=$_SESSION["APC"][$key];
				$time=$_SESSION["APC"][$key];	
			}else{
				if(isset($GLOBALS["APC"][$key])){
					$value=$GLOBALS["APC"][$key];
					$time=$GLOBALS["APC"][$key];						
				}
			}
			
		if($maxtime>0){
	   			$md5=md5("{$key}-time");
		 		$data1 = $time;
				$data2 = time();
				$difference = ($data2 - $data1); 	 
				$min=round($difference/60);
				if($min>$maxtime){
					$value=null;
					unset($GLOBALS["APC"][$key]);
					unset($GLOBALS["APC"]["{$key}-time"]);
					unset($_SESSION["APC"][$key]);
					unset($_SESSION["APC"]["{$key}-time"]);
					}   			
	   			}				
			
	   		if($value<>null){return $value;}
			
			
			if(!isset($GLOBALS["DEBUG"])){$GLOBALS["DEBUG"]=false;}
			if($this->SYSTEMV5_ENABLED()){
				if(trim($value)==null){
	   				$shm=new semaphores($this->semaphore_key,$this->semaphore_memory,3);   				
					if($GLOBALS["posix_getuid"]){if($GLOBALS["DEBUG"]){echo "APC_GET($key,$maxtime) -> semaphores($sock->semaphore_key,$sock->semaphore_memory,3) \n";}}
					$value=$shm->GET($key);
	   				$time=$shm->GET("{$key}-time");
	   				$_SESSION["APC"][$key]=$value;
	   				$_SESSION["APC"]["{$key}-time"]=$time;
	   				
			}}

		if($maxtime>0){
	   			$md5=md5("{$key}-time");
		 		$data1 = $time;
				$data2 = time();
				$difference = ($data2 - $data1); 	 
				$min=round($difference/60);
				if($min>$maxtime){
					$value=null;
					unset($GLOBALS["APC"][$key]);
					unset($GLOBALS["APC"]["{$key}-time"]);
					unset($_SESSION["APC"][$key]);
					unset($_SESSION["APC"]["{$key}-time"]);
					}   			
	   			}	
   		
   		
   		return trim($value);
   	
	}
	
	function APC_SAVE($key,$value){
		$this->SET_APC_STORE($key,$value);
	}
   
		
	function getfile($uri,$hostname=null){
		$line=null;
		if($this->GetFileCache($uri)==true){
			if(isset($_SESSION["CACHEINFOS_{$uri}"])){
				if($_SESSION["CACHEINFOS_{$uri}"]<>null){return $_SESSION["CACHEINFOS_{$uri}"];}
			}
		}
		
			
		$array["GlobalApplicationsStatus"]=true;
		$array["artica_version"]=true;
		$array["pid"]=true;
		$array["daemons_status"]=true;
		$array["myhostname"]=true;
		if(isset($array[$uri])){if($array[$uri]){return $this->getFrameWork("cmd.php?uri=$uri");}}
		
		
		$uri=str_replace(" ","%20",$uri);
		if(!$this->TestArticaPort()){
			error_log("ERROR: unable to open remote file http://$this->remote_ip:$this->remote_port/$uri line ".__LINE__);
			if(function_exists("writelogs")){writelogs("ERROR: unable to open remote file http://$this->remote_ip:$this->remote_port/$uri ",__CLASS__ . "=>" . __FUNCTION__,__FILE__);};return null;$this->error=true;}
			if($this->error==true){return null;}
		
		if(function_exists("writelogsSocketsLogs")){writelogsSocketsLogs("INFOS: Load [$this->remote_ip:$this->remote_port/cgi/articacgi?URI=$uri]",
		__CLASS__ . "=>" . __FUNCTION__,__FILE__);};
		
		
		$file = @fopen ("http://$this->remote_ip:$this->remote_port/cgi/articacgi?URI=$uri", "r");
		
		
		if (!$file) {
			error_log("ERROR: unable to open remote file http://$this->remote_ip:$this->remote_port/$uri");
			if(function_exists("writelogs")){writelogs("ERROR: unable to open remote file http://$this->remote_ip:$this->remote_port/$uri ",
			__CLASS__ . "=>" . __FUNCTION__,__FILE__);}
			$this->error=true;
			return false;
		}
		while (!feof ($file)) {
			$line =$line .  fgets ($file, 1024) . "\n";
		}
		fclose($file);
		
		if(preg_match('#<articadatascgi>(.+)</articadatascgi>#s',$line,$regs)){
			return trim($regs[1]);
		}
	}
		
	function GetFileCache($uri){
		$arr["artica_version"]=true;
		if(isset($arr[$uri])){
			return $arr[$uri];
		}
	}
		
	function TestArticaPort(){
		$time=date('h:i');
		if(isset($_SESSION["CACHEINFOS_TestArticaPort"][$time])){return true;}
		
		unset($_SESSION["CACHEINFOS_TestArticaPort"]);		
		if($this->GET_INFO("IgnoreBoaErrors")==1){
			$_SESSION["CACHEINFOS_TestArticaPort"][$time]=true;
			return true;
		}
		
		$fp = @fsockopen($this->remote_ip, $this->remote_port, $errno, $errstr, 1);
		
		if(!$fp){
			$this->error=true;
			if(posix_getuid()==0){
				//write_syslog("BOA ERROR !!! Bind remote $this->remote_ip:$this->remote_port   $errstr ($errno)",__FILE__);
				return false;
			}
			
			if(function_exists("writelogs")){writelogs("BOA ERROR !!! Bind remote $this->remote_ip:$this->remote_port   $errstr ($errno) ",
			__CLASS__ . "=>" . __FUNCTION__,__FILE__);}		
			if(function_exists("writelogs")){writelogs("ERROR: unable to connect remote $this->remote_ip:$this->remote_port  $errstr ($errno)" ,
			__CLASS__ . "=>" . __FUNCTION__,__FILE__);}	
			$_SESSION["CACHEINFOS_TestArticaPort"][$time]=false;	
			return true;	
		}
		$_SESSION["CACHEINFOS_TestArticaPort"][$time]=true;
		return true;
		
	}
	function TestArticaFilterPort(){
		$fp = @fsockopen ($this->remote_ip,$this->remote_port , $errno, $errstr, 1);
		
		if(!$fp){
			$this->error=true;
			if(function_exists("writelogs")){writelogs("Bind remote LOCAL:29900  $errstr ($errno) ",__CLASS__ . "=>" . __FUNCTION__,__FILE__);}		
			if(function_exists("writelogs")){writelogs("ERROR: unable to connect remote remote LOCAL:29900  $errstr ($errno)" ,__CLASS__ . "=>" . __FUNCTION__,__FILE__);}		
			return false;
		}
		fclose($fp);
		return true;
		
	}
	
	function RandomPort(){
		$min='1024';
		$max='65534';
		while (true) {
			$port=rand($min,$max);
			if(!$this->PortExists($port)){
				return $port;
			}
		}
		
		
	}
	

	function PortExists($port_number){
		for($port = $from; $port <= $to; $port++){
		$fp =@fsockopen("127.0.0.1", $port);
		if ($fp){
			fclose($fp);
			return true;}
		
		}
		
	}
	
	

}
?>