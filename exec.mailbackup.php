<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.demime.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}



cpulimit();

if($argv[1]=="--date"){echo date('d M Y H:i:s')."\n";die();}
if($argv[1]=="--transfert"){transfert();die();}
$_GET["DOMAINS"]=null;
$_GET["FALSE_EMAILS"]=null;
$_GET["EMAILS"]=null;

include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__)." Already executed.. aborting the process");
	die();
}

$q=new mysql();
$q->check_storage_table();
$sock=new sockets();
if($sock->GET_INFO("KeepArticaMysqlError")<>1){DeleteMysqlError();}
$quarantine_dir="/tmp/savemail";
@mkdir("$quarantine_dir");
@chmod("$quarantine_dir",0777);

$files=DirList($quarantine_dir);
$count=0;
$pid=getmypid();
$max=count($files);
$date1=date('H:i:s');
events("Processing ".count($files)." files in $quarantine_dir");
	while (list ($num, $file) = each ($files) ){
		
		events("################################################################### $count/$max)");
		if(archive_process("$quarantine_dir/$file")){
			if(is_file("$quarantine_dir/$file")){
				WriteToSyslogMail("$quarantine_dir/$file removed",__FILE__,false);
				@unlink("$quarantine_dir/$file");
			}
			events("processing $quarantine_dir/$file success");
		}else{
			events("processing $quarantine_dir/$file failed");
		}
		
		$count=$count+1;
		$ini=new Bs_IniHandler();
		$ini->set("PROGRESS","current",$count);
		$ini->set("PROGRESS","total",$max);
		$ini->set("PROGRESS","pid",$pid);
		$ini->set("PROGRESS","quarantine","(spam)/virus *.gz,virus-");
		$ini->saveFile("/usr/share/artica-postfix/ressources/logs/mailarchive-archive-progress.ini");
		chmod("/usr/share/artica-postfix/ressources/logs/mailarchive-archive-progress.ini",0755);
		//if($count>50){break;}
		
	}
		$ini=new Bs_IniHandler();
		$ini->set("PROGRESS","pid","0");
		$date=date('H:i:s');
		$ini->set("PROGRESS","quarantine","Finish $date1 -> $date, next in 5mn");
		$ini->saveFile("/usr/share/artica-postfix/ressources/logs/mailarchive-archive-progress.ini");
		//system('/bin/rm /var/virusmails/*.eml >/dev/null 2>&1');
		
ASSP_QUAR("/usr/share/assp/okmail");
ASSP_QUAR("/usr/share/assp/notspam");

$unix=new unix();
$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.clean.logs.php --clean-tmp");	
	
@mkdir("$quarantine_dir");
@chmod("$quarantine_dir",0777);
die();

function ASSP_QUAR($baseDir){
//""	
	if(!is_dir($baseDir)){
		events("Processing unable to stat $baseDir");	
		return null;
	}
	
	$files=DirEML($baseDir);
	events("Processing ".count($files)." files in $baseDir");
	while (list ($num, $file) = each ($files) ){
		if(archive_process("$baseDir/$file")){
			
			WriteToSyslogMail("$baseDir/$file removed",__FILE__,false);
			@unlink("$baseDir/$file");
			events("processing $baseDir/$file success");
		}else{
			events("processing $baseDir/$file failed");
		}
	}
	
}
function DirEML($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		events("Unable to open \"$path\"",__FILE__);
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		if(preg_match("#\.eml$#",$file)){
			$array[$file]=$file;
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
		$logFile="/var/log/artica-postfix/artica-mailarchive.debug";
		$size=filesize($logFile);
		if($size>5000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($GLOBALS["DEBUG"]){echo "$date mailarchive[$pid]:[BACKUP] $text\n";}
		@fwrite($f, "$date mailarchive[$pid]:[BACKUP] $text\n");
		@fclose($f);	
		}
		
function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		writelogs("Unable to open \"$path\"",__FUNCTION__,__FILE__,__LINE__);
		@mkdir($path,null,true);
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
	if(preg_match("#\.msg$#",$file)){
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

function archive_process($file){
	$fullmessagesdir="/opt/artica/share/www/original_messages";
	$target_file=$file;
	$filename=basename($target_file);

	
	$ldap=new clladp();
	$q=new mysql();

	events("Unpack $target_file");
	$mm=new demime($target_file);
	if(!$mm->unpack()){
		events("Failed unpack with error \"$mm->error\"");
		if($mm->MustkillMail){@unlink($target_file);}
		return false;
	}
	
	
	$message_html=$mm->ExportToHtml($target_file);
	if(strlen($message_html)==0){return false;}
	
	
	if(count($mm->mailto_array)==0){events("No recipients Aborting");return true;}
	
	
	$filesize=filesize($target_file);
	events("Message with ".count($mm->mailto_array)." recipients html file:".strlen($message_html)." bytes");
	
	if(preg_match("#(.+?)@(.+)#",$mm->mailfrom,$re)){$domain_from=$re[2];}
	$message_html=addslashes($message_html);
	
	while (list ($num, $recipient) = each ($mm->mailto_array) ){
		if(preg_match("#(.+?)@(.+)#",$recipient,$re)){$recipient_domain=$re[2];}
			$ou=$mm->GetOuFromEmail($recipient);
			$sql_source_file=$target_file;
			events("(New message)time=$mm->message_date message-id=<$mm->message_id> from=<$mm->mailfrom> to=<$recipient> size=$filesize");
			$newmessageid=md5($mm->message_id.$recipient);
			
			$sqlfilesize=@filesize($target_file);
			$BinMessg = addslashes(fread(fopen($target_file, "r"), $sqlfilesize));
			
			$sql="INSERT IGNORE INTO storage (
				MessageID,
				zDate,
				mailfrom,
				mailfrom_domain,
				subject,
				MessageBody,
				organization,
				mailto,
				file_path,
				original_messageid,
				message_size,
				BinMessg,filename,filesize
				)
			VALUES(
				'$newmessageid',
				'$mm->message_date',
				'$mm->mailfrom',
				'$domain_from',
				'$mm->subject',
				'$message_html',
				'$ou',
				'$recipient',
				'$sql_source_file',
				'$mm->message_id',
				'$filesize','$BinMessg','$filename','$sqlfilesize')";
				
				if(!$q->QUERY_SQL($sql,"artica_backup")){
					events($q->mysql_error);
					file_put_contents("/var/log/artica-postfix/mysql-error.".md5($sql).".err","$sql\n\n$q->mysql_error");
					events("error saved into  /var/log/artica-postfix/mysql-error.".md5($sql).".err");
					return false;
				}else{
					events("Success saved in mysql...");
				}
			
			
		}
		
		events("Analyze sender $mm->mailfrom...");
		$ou=$mm->GetOuFromEmail($mm->mailfrom);
		if($ou==null){
			events("Not organization found for $mm->mailfrom...");
			return true;
			}
		
			$recipients=$mm->mailto_array;
			$impled_rctp=implode(";",$recipients);
		
		
$sql="INSERT IGNORE INTO storage (
				MessageID,
				zDate,
				mailfrom,
				mailfrom_domain,
				subject,
				MessageBody,
				organization,
				mailto,
				file_path,
				original_messageid,
				message_size,BinMessg,filename,filesize
				)
			VALUES(
				'$newmessageid',
				'$mm->message_date',
				'$mm->mailfrom',
				'$domain_from',
				'$mm->subject',
				'$message_html',
				'$ou',
				'$impled_rctp',
				'$sql_source_file',
				'$mm->message_id',
				'$filesize','$BinMessg','$filename','$sqlfilesize')";
				
				if(!$q->QUERY_SQL($sql,"artica_backup")){
					events($q->mysql_error);
					file_put_contents("/var/log/artica-postfix/mysql-error.".md5($sql).".err","$sql\n\n$q->mysql_error");
					WriteToSyslogMail("error saved into  /var/log/artica-postfix/mysql-error.".md5($sql).".err",__FILE__);
					return false;
				}		
	
		WriteToSyslogMail("$mm->message_id: <$mm->mailfrom> to: <$impled_rctp> size=$filesize bytes (saved into backup area)",__FILE__);		
		events("time=$mm->message_date message-id=<$mm->message_id> from=<$mm->mailfrom> to=<$impled_rctp> size=$filesize");
		return true;
		
		
	}
	
	
	
function ForceDirectories($dir){
	if(is_dir($dir)){return true;}
	@mkdir($dir,null,true);
	if(is_dir($dir)){return true;}
	}
	
	function transfert(){

	$sql="SELECT file_path,MessageID FROM storage WHERE filesize=0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["file_path"])==null){
			continue;
		}
		
		if(!is_file($ligne["file_path"])){
			echo "Unable to find \"{$ligne["file_path"]}\"";
			DeleteLine($msgid);
		}
		$filename=basename($ligne["file_path"]);
		$sqlfilesize=@filesize($ligne["file_path"]);
		$BinMessg = addslashes(fread(fopen($ligne["file_path"], "r"), $sqlfilesize));
		$sql="UPDATE storage SET filesize=$sqlfilesize, filename='$filename',BinMessg='$BinMessg' WHERE MessageID='{$ligne["MessageID"]}'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "failed {$ligne["MessageID"]}\n";	
			continue;	
		}
		
		echo "success {$ligne["MessageID"]} $sqlfilesize bytes message \n";	
		@unlink($ligne["file_path"]);
	}
	
	
	
}

function DeleteLine($msgid){
	echo "Deleting message $msgid\n";
	$sql="DELETE FROM storage WHERE MessageID='$msgid'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
}

function DeleteMysqlError(){
foreach (glob("/var/log/artica-postfix/mysql-error.*.err") as $filename) {if(file_time_min($filename)>5){@unlink($filename);}}
}




?>