<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.demime.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/ressources/class.artica-meta.inc');

cpulimit();


if($argv[1]=="--transfert"){transfert();die();}

$_GET["DOMAINS"]=null;
$_GET["FALSE_EMAILS"]=null;
$_GET["EMAILS"]=null;

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


$quarantine_dir="/var/virusmails";
@mkdir("/var/log/artica-postfix/artica-meta-msgs",666,true);
$files=DirList($quarantine_dir);
$count=0;
$pid=getmypid();
$max=count($files);
$date1=date('H:i:s');
if(count($files)>0){write_syslog("Processing ".count($files),__FILE__);}

	while (list ($num, $file) = each ($files) ){
		
		events("################################################################### $count/$max)");
		if(quarantine_process("$quarantine_dir/$file")){
			if(is_file("$quarantine_dir/$file")){@unlink("$quarantine_dir/$file");}
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
		$ini->saveFile("/usr/share/artica-postfix/ressources/logs/mailarchive-quarantine-progress.ini");
		chmod("/usr/share/artica-postfix/ressources/logs/mailarchive-quarantine-progress.ini",0755);
		//if($count>50){break;}
		
	}
		$ini=new Bs_IniHandler();
		$ini->set("PROGRESS","pid","0");
		$date=date('H:i:s');
		$ini->set("PROGRESS","quarantine","Finish $date1 -> $date, next in 5mn");
		$ini->saveFile("/usr/share/artica-postfix/ressources/logs/mailarchive-quarantine-progress.ini");
		system('/bin/rm /var/virusmails/*.eml >/dev/null 2>&1');
		
		
	ASSP_QUAR("/usr/share/assp/spam");	
	ASSP_QUAR("/usr/share/assp/discarded");	
	ASSP_QUAR("/usr/share/assp/quarantine");	
	ASSP_QUAR("/usr/share/assp/errors/notspam");	
	ASSP_QUAR("/usr/share/assp/errors/spam");
	ASSP_QUAR("/var/spam-mails");	
	
	
die();


function ASSP_QUAR($baseDir){
//""	
	if(!is_dir($baseDir)){return null;}
	$files=DirEML($baseDir);
	events("Processing ".count($files)." files in $baseDir");
	while (list ($num, $file) = each ($files) ){
		if(quarantine_process("$baseDir/$file")){
			WriteToSyslogMail("$baseDir/$file removed",__FILE__,false);
			@unlink("$baseDir/$file");
			events("processing $baseDir/$file success");
		}else{
			events("processing $baseDir/$file failed");
		}
	}
	
}

function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile="/var/log/artica-postfix/artica-mailarchive.debug";
		$size=filesize($logFile);
		if($size>5000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date mailarchive[$pid]: $text\n");
		@fclose($f);	
		}
		
function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		if(preg_match("#\.gz$#",$file)){
			$array[$file]=$file;
			continue;
			}
		if(preg_match("#^virus-.+?#",$file)){
			$array[$file]=$file;
			continue;
			}
	
		if(preg_match("#^banned-.+?#",$file)){
			$array[$file]=$file;
			continue;
			}			
	  	
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
}
function DirEML($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		write_syslog("Unable to open \"$path\"",__FILE__);
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

function quarantine_process($file){
	
	if(!is_numeric($GLOBALS["ArticaMetaEnabled"])){
			$sock=new sockets();
			$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
			if($ArticaMetaEnabled==null){$ArticaMetaEnabled=0;}
			$GLOBALS["ArticaMetaEnabled"]=$ArticaMetaEnabled;
			$meta=new artica_meta();
			$GLOBALS["UUID"]=$meta->uuid;
			$GLOBALS["SERIAL"]=$meta->serial;
	}
	
	
	$fullmessagesdir="/opt/artica/share/www/original_messages";
	$target_file=$file.".eml";
	$decompress=true;
	
	if(preg_match("#\.gz$#",$file)){
		events("gunzip compressed, decompressing $file to $target_file");
		@mkdir("/tmp/amavis-quar");
		$cmd="/bin/gunzip -d -c \"$file\" >$target_file 2>&1";
		$values=system($cmd);
		
		if(!is_file($target_file)){
			events("Failed decompress $file \"$values\"");
			return false;
		}		
		
	}else{
		$target_file=$file;	
		$decompress=false;
	}
	
	$ldap=new clladp();
	$q=new mysql();

	events("Unpack $target_file " .@filesize($target_file)." bytes");
	$mm=new demime($target_file);
	if(!$mm->unpack()){
		events("Failed unpack with error \"$mm->error\"");
		@unlink($target_file);
		return false;
	}
	
	
	$message_html=$mm->ExportToHtml($target_file);
	if(strlen($message_html)==0){
		if($decompress){@unlink($target_file);}
		return false;
		}
	
	
	if(count($mm->mailto_array)==0){events("No recipients Aborting");
		if($decompress){@unlink($target_file);}
		return true;
	}
	
	
	$filesize=filesize($target_file);
	events("Message with ".count($mm->mailto_array)." recipients html file:".strlen($message_html)." bytes");
	if(preg_match("#(.+?)@(.+)#",$mm->mailfrom,$re)){$domain_from=$re[2];}
	$message_html=addslashes($message_html);
	
	$filename=basename($target_file);
	$newmessageid=md5($mm->message_id.$recipient);
	$sqlfilesize=@filesize($target_file);
	
	if($sqlfilesize==0){
		events("error \"$target_file\" filesize=0!!!");
		WriteToSyslogMail("message-id=<$mm->message_id> from=<$mm->mailfrom> to=<$impled_rctp> size=$filesize filesize error",__FILE__,true);
		return false;}
	
	$BinMessg = addslashes(fread(fopen($target_file, "r"), $sqlfilesize));
	
	if(strlen($BinMessg)==0){
		events("error \"$target_file\" BinMessg=0!!!");
		WriteToSyslogMail("message-id=<$mm->message_id> from=<$mm->mailfrom> to=<$impled_rctp> size=$filesize filesize error",__FILE__,true);
		return false;}	
				
	if(count($mm->mailto_array)==0){
		WriteToSyslogMail("message-id=<$mm->message_id> from=<$mm->mailfrom> size=$filesize recipient error",__FILE__,true);
		return false;
	}
	
	
	
	reset($mm->mailto_array);
	while (list ($num, $recipient) = each ($mm->mailto_array) ){
		if(preg_match("#(.+?)@(.+)#",$recipient,$re)){$recipient_domain=$re[2];}
			
			$ou=$mm->GetOuFromEmail($recipient);
			
			if($ou==null){
				events("Failed to get organization name from  \"$recipient_domain\"");
				
			}
			
		
			
			$sql="INSERT IGNORE INTO quarantine (
				MessageID,
				zDate,
				mailfrom,
				mailfrom_domain,
				subject,
				MessageBody,
				organization,
				mailto,
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
				'$recipient',
				'$mm->message_id',
				'$filesize','$BinMessg','$filename','$sqlfilesize')";
				
				if(!$q->QUERY_SQL($sql,"artica_backup")){
					events($q->mysql_error);
					file_put_contents("/var/log/artica-postfix/mysql-error.".md5($sql).".err","$sql\n\n$q->mysql_error");
					events("error saved into  /var/log/artica-postfix/mysql-error.".md5($sql).".err");
					if($decompress){@unlink($target_file);}
					return false;
				}else{
					if($GLOBALS["ArticaMetaEnabled"]==1){
						if($GLOBALS["SERIAL"]<>null){
							$md5=md5("$newmessageid$mm->message_date");
							$sqlmeta="('{$GLOBALS["UUID"]}','{$GLOBALS["SERIAL"]}','$newmessageid','$mm->message_date',";
							$sqlmeta=$sqlmeta."'$mm->mailfrom','$domain_from','$recipient','$mm->subject','$ou','$mm->message_id',";
							$sqlmeta=$sqlmeta."'$filesize','$sqlfilesize')";
							@file_put_contents("/var/log/artica-postfix/artica-meta-msgs/$md5.quar",$sqlmeta);
						}
					}
					WriteToSyslogMail("$mm->message_id: <$mm->mailfrom> to: <$recipient> size=$filesize bytes (saved into quarantine area)",__FILE__);
					events("time=$mm->message_date message-id=<$mm->message_id> from=<$mm->mailfrom> to=<$recipient> size=$filesize");
				}
		}
		
		
		if($decompress){@unlink($target_file);}
		return true;
		
		
	}
	
	
function transfert(){

	$sql="SELECT file_path,MessageID FROM quarantine WHERE filesize=0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["file_path"])==null){
			continue;
		}
		
		
		if(!is_file($ligne["file_path"])){
			echo "Unable to find {$ligne["file_path"]}";
			DeleteLine($msgid);
		}
		$filename=basename($ligne["file_path"]);
		$sqlfilesize=@filesize($ligne["file_path"]);
		$BinMessg = addslashes(fread(fopen($ligne["file_path"], "r"), $sqlfilesize));
		$sql="UPDATE quarantine SET filesize=$sqlfilesize, filename='$filename',BinMessg='$BinMessg' WHERE MessageID='{$ligne["MessageID"]}'";
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
	$sql="DELETE FROM quarantine WHERE MessageID='$msgid'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
}
	
function ForceDirectories($dir){
	if(is_dir($dir)){return true;}
	@mkdir($dir,null,true);
	if(is_dir($dir)){return true;}
	}




?>