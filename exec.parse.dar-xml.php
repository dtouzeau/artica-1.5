<?php
include_once(dirname(__FILE__)."/ressources/class.charset.inc");
include_once(dirname(__FILE__)."/ressources/class.ini.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.users.menus.inc");
include_once(dirname(__FILE__)."/framework/class.unix.inc");

cpulimit();


$_GET["CMD_LINE"]=implode(' ',$argv);
if(preg_match("#--verbose#",$_GET["CMD_LINE"])){$_GET["DEBUG"]=true;}

if(posix_getuid()<>0){
	events("Cannot be used in web server mode");
	die();
}

if($argv[1]=="-stat"){
	echo filesize($argv[2]);
	die();
}

if($argv[1]=="-cyrus"){
	updateCyrus();
	die();
}


$usersn=new usersMenus();
$_GET["LOGS_PATH"]="/var/log/artica-postfix/increment-queue";
$_GET["CACHE"]=array();
$_GET["STORAGES"]=array();
$_GET["MY-HOSTNAME"]=$usersn->hostname;
$files=DirList($_GET["LOGS_PATH"]);
$count_de_files=count($files);
if(is_file("/etc/artica-postfix/settings/Daemons/DarBackupStoragesList")){
	$DarBackupStoragesList=file_get_contents("/etc/artica-postfix/settings/Daemons/DarBackupStoragesList");
	$tb2=explode("\n",$DarBackupStoragesList);
	if(is_array($tb2)){
		while (list ($num, $pattern) = each ($tb2) ){
			$_GET["STORAGES"][md5($pattern)]=$pattern;
		}
	}
}


events(".");
events("Parsing $count_de_files files in queue");


	while (list ($num, $file) = each ($files) ){
		events("-> {$_GET["LOGS_PATH"]}/$file");
		if(readlsfile("{$_GET["LOGS_PATH"]}/$file")){
			events("DELETE -> {$_GET["LOGS_PATH"]}/$file");
			@unlink("{$_GET["LOGS_PATH"]}/$file");
		}
	}
if($count_de_files>0){
	updateCyrus();	
}
events(".");
function ParseFile($path){
	
	
	if(preg_match('#(.+?)\.[a-z0-9]+$#',$path,$re)){
		$database_path=$re[1];
	
	}
	
	
	$source_path=GetSources($database_path);
	$database_name=basename($database_path);
	
	$datas=file_get_contents($path);
	$tbl=explode("\n",$datas);
	
	events("Parsing \"$path\" ".count($tbl)." rows");
	
	
	while (list ($num, $ligne) = each ($tbl) ){	
		if(preg_match("#^\[.+?\]\s+\[.*?\]\s+[a-z\-]+\s+[a-zA-Z0-9]+\s+[a-zA-Z0-9]+\s+([0-9]+)\s+([a-zA-Z0-9\s\:]+)\s+(.+)#",$ligne,$re)){
			if(!Insert($re[3],$re[1],$re[2],$source_path,$database_name)){
				events("Failed line $num...");
				return false;
			}
		}
	}
	
	return true;
	
}
	

function Insert($filepath,$filesize,$datetime,$source_path,$database_name,$external_cource){
	if(!preg_match("#[A-Za-z]+\s+([A-Za-z]+)\s+([0-9]+)\s+([0-9\:]+)\s+([0-9]+)#",$datetime,$re)){return false;}
	
	if($database_name==null){
		event("Insert:: database name is null !");
		return false;
	}
	
	$month=_MonthToInteger($re[1]);
	$day=$re[2];
	$time=$re[3];
	$year=$re[4];
	$date="$year-$month-$day $time";
	
	$filekey=md5($date.$source_path.$filepath.$database_name.$_GET["MY-HOSTNAME"]);
	if($_GET["CACHE"][$database_name][$filekey]){return true;}
	
	
	$_GET["CACHE"][$database_name][$filekey]=true;
	$basepath=dirname($filepath);
	$filepath=addslashes($filepath);
	$basepath=addslashes($basepath);
	$source_path=addslashes($source_path);
	
	$sql="INSERT INTO dar_index (filekey,filedate,filepath,basepath,source_path,database_name,filesize,mount_md5,servername)
	VALUES('$filekey','$date','$filepath','$basepath','$source_path','$database_name','$filesize','$external_cource','{$_GET["MY-HOSTNAME"]}')
	";
	
	
	$q=new mysql();
	if(!$q->QUERY_SQL($sql,"artica_backup")){
		if(preg_match("#Unknown column\s+#",$q->mysql_error)){
			events("Insert:: Build table error reported \"Unknown column\"");
			if(!$q->CheckTables_dar()){
				events("Insert:: Build table error: $q->mysql_error");
			}
		}
		events("Insert:: Mysql error ". $q->mysql_error);
		return false;
		
	}
	$_GET["COUNT"][$database_name]=$_GET["COUNT"][$database_name]+1;
	
	return true;
	
	
}
function _MonthToInteger($month){
  $month=strtoupper($month);
  $zText=$month;	
  $zText=str_replace('JAN', '01',$zText);
  $zText=str_replace('FEB', '02',$zText);
  $zText=str_replace('MAR', '03',$zText);
  $zText=str_replace('APR', '04',$zText);
  $zText=str_replace('MAY', '05',$zText);
  $zText=str_replace('JUN', '06',$zText);
  $zText=str_replace('JUL', '07',$zText);
  $zText=str_replace('AUG', '08',$zText);
  $zText=str_replace('SEP', '09',$zText);
  $zText=str_replace('OCT', '10',$zText);
  $zText=str_replace('NOV', '11',$zText);
  $zText=str_replace('DEC', '12',$zText);
  return $zText;	
}


function GetSources($database_path){
	
	$dir=dirname($database_path);
	$database_name=basename($database_path);
	
	$ini=new Bs_IniHandler("{$_GET["LOGS_PATH"]}/user_defined.conf");
	if(!is_array($ini->_params)){return null;}
	while (list ($num, $ligne) = each ($ini->_params) ){
		
		if($num==$database_path){
			return $ligne["TargetFolder"];
		}
			
		if($num==$database_name){
			return $ligne["TargetFolder"];
		}
		
	}
	
	
}
function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		events("Unable to open \"$path\"");
		return array();
	}
$count=0;	
while ($file = readdir($dir_handle)) {
  if($file=='.'){continue;}
  if($file=='..'){continue;}
  if(!is_file("$path/$file")){
  	events("$path/$file does not exists");
  	continue;
  }
	if(preg_match("#\.ls$#",$file)){
		events("$path/$file  exists");
		$array[$file]=$file;
		continue;
		}
		
if(preg_match("#\.html$#",$file)){
		if(!@unlink("$path/$file")){
			events("ERROR removing $path/$file");
		}
		continue;
		}		
}
if(!is_array($array)){return array();}
@closedir($dir_handle);
return $array;
}

function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile="/var/log/artica-postfix/artica-parse-dar.debug";
		$size=@filesize($logFile);
		if($size>5000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$towrite="$date ".basename(__FILE__)."[$pid]: $text\n";
		if($_GET["DEBUG"]){echo $towrite;}
		@fwrite($f, $towrite);
		@fclose($f);	
		}



 function readlsfile($path) {
 	
 	
 	if(!preg_match('#-md-(.+?)\.ls#',$path,$re)){
 		events("Unable to determine source from this file ".basename($path));
 		return true;
 	}else{
 		$external_source=$re[1];
 	}
 	
 	$file_name=basename($path);
 	$filename = $path;
 	$filesize=@filesize($filename);
 	events("Request for analyze file $path size $filesize bytes");
 	
 	if($filesize==0){
 		events("Obytes -> Abort but return true;");
 		return true;
 	}
 	
 	
 	
	if(preg_match('#(.+?)-md-.+?\.[a-z0-9]+$#',$path,$re)){$database_path=$re[1];}
	
	
	$database_name=basename($database_path);
 	if(preg_match('#(.+?)\-.+?\-diff#',$database_name,$re)){$database_name=$re[1];}
	if($filesize>400){DeleteDatabase($database_name);}	
    
	$source_path=GetSources($database_path);
	if($source_path==null){$source_path=GetSources($database_name);}
	
    
	$external_source_pattern=$_GET["STORAGES"][$external_source];
	
	if($external_source==null){
		events("Unable to find source for $external_source !!");
		return false;	
		
	}
	
    events("*********************************************************************");
    events("Analyzing cache from $filename...");
    events("MD5 source....: $external_source");
    events("Pattern source: $external_source_pattern");
    events("database name.: $database_name");
	events("database Path.: $database_path");
	events("Source Path...: $source_path");	    
    events("*********************************************************************");
    
    
    
    $content=file_get_contents($filename);
    $md5=md5($content);
    $ini=new Bs_IniHandler("/etc/artica-postfix/dar.cache.ini");
    if($ini->_params["$database_name"]["md5"]==$md5){
    	events("Analyzing $database_name cache:$md5 already set");
    	return true;}
    $ini->set($database_name,"md5",$md5);
    $ini->saveFile("/etc/artica-postfix/dar.cache.ini");
    
    
    
    $filesize=@filesize($filename);
    $filesize=round($filesize/1024,2) ." Ko";
    
    events("Analyzing ". basename($filename)." ($filesize) for database: $database_name cache:$md5");
    
    if ($fd = @fopen ($filename, "r")) {
      while ($fd && !feof ($fd)) {
        $line=trim(fgets($fd, 4096));
      		if(preg_match("#^\[.+?\]\s+\[.*?\]\s+[a-z\-]+\s+[a-zA-Z0-9\-\_\.]+\s+[a-zA-Z0-9\-\_\.]+\s+([0-9]+)\s+([a-zA-Z0-9\s\:]+)\s+(.+)#",$line,$re)){
			if(!Insert($re[3],$re[1],$re[2],$source_path,$database_name,$external_source_pattern)){
				events("Failed line \"$line\" aborting process");
				return false;
			}
		}else{
			events("Failed line $num \"$line\" -> continue reading file...");        
		}
      }
      fclose ($fd);
      
      events("{$_GET["COUNT"][$database_name]} files added...");
      events("Optimize table...");
      $sql="OPTIMIZE TABLE `dar_index`";
      $q=new mysql();
      $q->QUERY_SQL($sql,"artica_backup");
      return true;
    }

  }
  
  
function DeleteDatabase($database_name){
	return null;
	events("Delete old entries for dar database $database_name");
	$sql="DELETE FROM dar_index WHERE database_name='$database_name'";
	$q=new mysql();
	events($sql);
	$q->QUERY_SQL($sql,"artica_backup");
	events("Delete old entries done...");
	
}

function updateCyrus(){
	$users=new usersMenus();
	
	$cyrus_imap_datas=$users->cyr_config_directory;
	$cyrus_imap_mail=$users->cyr_partition_default;
	
	if($cyrus_imap_datas<>null){
		$sql="UPDATE dar_index SET source_path='$cyrus_imap_datas' WHERE database_name='cyrus_imap_datas'";
		$q=new mysql();
		events($sql);
		$q->QUERY_SQL($sql,"artica_backup");		
	}
	
	if($cyrus_imap_mail<>null){
		$sql="UPDATE dar_index SET source_path='$cyrus_imap_mail' WHERE database_name='cyrus_imap_mail'";
		$q=new mysql();
		events($sql);
		$q->QUERY_SQL($sql,"artica_backup");		
	}	
	
	
	
	
	
	
	
}
  



?>