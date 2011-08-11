<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
if($argv[1]=="--retrans"){ParseRetranslatorLogs();die();}

ParseKas3Logs();
ParseKav4ProxyLogs();
av_stats();
ParseKavmilterLogs();
ParseRetranslatorLogs();

function ParseKas3Logs(){
	$dir="/var/log/artica-postfix/kaspersky/kas3";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofKas3FilesUpdated=NumberofKas3FilesUpdated("$dir/$file");
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Anti-Spam: $NumberofKas3FilesUpdated pattern file(s) updated";
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
					
		}
		
		@unlink("$dir/$file");
	}
}

function ParseKavmilterLogs(){
	$dir="/var/log/artica-postfix/kaspersky/kavmilter";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofKas3FilesUpdated=NumberofKavFilesUpdated("$dir/$file");
		if($NumberofKas3FilesUpdated<0){
			$subject="Kaspersky Antivirus Mail: update failed";	
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			@unlink("$dir/$file");
			continue;
		}
		
		
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Antivirus Mail: $NumberofKas3FilesUpdated new viruses in databases";
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
					
		}
		
		@unlink("$dir/$file");
	}
}

function ParseKav4ProxyLogs(){
	$dir="/var/log/artica-postfix/kaspersky/kav4proxy";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+)#",$file,$re)){continue;}
		
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofKas3FilesUpdated=NumberofKavFilesUpdated("$dir/$file");
		
		
		if($NumberofKas3FilesUpdated<0){
			$subject="Kaspersky Antivirus Proxy: update failed";	
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			ParseKav4ProxyLogsMysql($date,$subject,"$dir/$file");
			continue;
		}
		
		
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Antivirus Proxy: $NumberofKas3FilesUpdated new viruses in databases";
			ParseKav4ProxyLogsMysql($date,$subject,"$dir/$file");
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			continue;		
		}
		
		if(AllAreUp2date("$dir/$file")){
			ParseKav4ProxyLogsMysql($date,"All files are up-to-date","$dir/$file");
			continue;
		}
		
		if(completed("$dir/$file")){
			ParseKav4ProxyLogsMysql($date,"Update completed successfully","$dir/$file");
			continue;
		}
		
		ParseKav4ProxyLogsMysql($date,"Updates launched...","$dir/$file");
	}
}

function AllAreUp2date($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	if(preg_match("#All files are up-to-date#",$line)){return true;}
	}
	return false;
}
function completed($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	if(preg_match("#Update 'Kaspersky Anti-Virus for Proxy Server' completed successfully#",$line)){return true;}
	}
	return false;
}


function ParseKav4ProxyLogsMysql($date,$subject,$filename){
	$datas=@file_get_contents($filename);
	$datas=addslashes($datas);
	$sql="INSERT IGNORE INTO kav4proxy_updates (zDate,subject,content) VALUES('$date','$subject','$datas')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		$unix=new unix();
		$unix->send_email_events("Mysql error ".__FUNCTION__.",".basename(__FILE__), $q->mysql_error, "system");
	}
	@unlink("$filename");
}


function NumberofKas3FilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^File updated.+#",$line)){$count++;continue;}
	}
	
	return $count;
}

function NumberofKavFilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	$line=trim($line);
	if(preg_match("#^Extended AV bases are OK, latest update.+?,.+?([0-9]+)#",$line)){
		$count=$re[1];
		break;
	}
	
	
	if(preg_match("#^Update.+?Kaspersky.+?failed$#",$line)){
		$count=-1;
	}
	
	
	}
	
	return $count;
}
function NumberofRestransFilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	$line=trim($line);
	if(preg_match("#file installed\s+'(.+?)\.(avc|set|ini|lst|dt)#",$line,$re)){
		$fi[]=$re[1].".".$re[2];
		continue;
		}
	if(preg_match("#File updated\s+'(.+?)\.(avc|set|ini|lst|dt)#",$line,$re)){
		$fi[]=$re[1].".".$re[2];
		continue;
		}		
	}



if(is_array($fi)){
	reset($fi);
	while (list ($a, $path_local) = each ($fi) ){
		
		if(is_file($path_local)){
			$size=@filesize($path_local);
			$bgsize=$bgsize+$size;
		}else{
			echo "FAILED $path_local\n";
		}
		
	}
}
if($bgsize>0){
	$bgsize=$bgsize/1024;
	$bgsize=str_replace("&nbsp;"," ",FormatBytes($bgsize));
}

return array(count($fi),$bgsize);
}


function ParseRetranslatorLogs(){
	
	$unix=new unix();
	if($unix->PIDOF("/usr/share/artica-postfix/bin/retranslator.bin")>0){return ;}
	
$dir="/var/log/kretranslator";
	if(!is_dir($dir)){return null;}
	$unix=new unix();
	$files=$unix->DirFiles($dir);
	while (list ($num, $file) = each ($files) ){
		if(!preg_match("#retranslator-([0-9\-]+)_([0-9]+)-([0-9]+)-([0-9]+).debug#",$file,$re)){continue;}
		$date="{$re[1]} {$re[2]}:{$re[3]}:{$re[4]}";
		$NumberofFilesUpdated=NumberofRestransFilesUpdated("$dir/$file");
		if($NumberofFilesUpdated[0]>0){
			$subject="Kaspersky Retranslator: {$NumberofFilesUpdated[0]} files updated ({$NumberofFilesUpdated[1]})";	
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
			@unlink("$dir/$file");
			continue;
		}
		
		@unlink("$dir/$file");
	}	
	
}


function av_stats(){
	$users=new usersMenus();
	if(!$users->KAV4PROXY_INSTALLED){
		if($GLOBALS["VERBOSE"]){writelogs("Kav4Proxy is not installed...",__FUNCTION__,__FILE__,__LINE__);}
		return;
		
	}
	
	$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$unix=new unix();
	$minute=$unix->file_time_min($timefile);
	if(!$GLOBALS["FORCE"]){
		if($minute<15){
		if($GLOBALS["VERBOSE"]){writelogs("{$minute}Mn need 15, aborting",__FUNCTION__,__FILE__,__LINE__);}
		return;
		}
	}
	
	$pid=$unix->get_pid_from_file("/var/run/kav4proxy/kavicapserver.pid");
	if(!$unix->process_exists($pid)){
		if($GLOBALS["VERBOSE"]){writelogs("Process antivirus statistics failed, Kav4Proxy seems not running (PID:$pid)",__FUNCTION__,__FILE__,__LINE__);}
		$unix->send_email_events("Process antivirus statistics failed, Kav4Proxy seems not running (PID:$pid)", "/var/run/kav4proxy/kavicapserver.pid as no valid PID", "proxy");
		return;
	}
		
	@unlink($timefile);
	@file_put_contents($timefile, time());
	$kill=$unix->find_program("kill");
	if($GLOBALS["VERBOSE"]){writelogs("$kill -USR2 $pid",__FUNCTION__,__FILE__,__LINE__);}
	shell_exec("$kill -USR2 $pid");
	if(!is_file("/var/log/kaspersky/kav4proxy/counter.stats")){
		if($GLOBALS["VERBOSE"]){writelogs("/var/log/kaspersky/kav4proxy/counter.stats no such file",__FUNCTION__,__FILE__,__LINE__);}
		return;
	}
	$FileExploded=explode("\n", @file_get_contents("/var/log/kaspersky/kav4proxy/counter.stats"));
	
	if($GLOBALS["VERBOSE"]){writelogs("/var/log/kaspersky/kav4proxy/counter.stats ". count($FileExploded) . " items",__FUNCTION__,__FILE__,__LINE__);}
	
	$val=array();
	while (list ($num, $line) = each ($FileExploded) ){
		if(preg_match("#^(.+?)\s+([0-9\.]+)#", $line,$re)){
			if($GLOBALS["VERBOSE"]){writelogs("item: {$re[1]} = \"{$re[2]}\"",__FUNCTION__,__FILE__,__LINE__);}
			$val[trim($re[1])]=trim($re[2]);
		}else{
			if($GLOBALS["VERBOSE"]){writelogs("$line no match ^(.+?)\s+([0-9\.]+)",__FUNCTION__,__FILE__,__LINE__);}
		}
	}
	
	if(count($val)==0){
		if($GLOBALS["VERBOSE"]){writelogs("\$val no items, aborting",__FUNCTION__,__FILE__,__LINE__);}
		return;
	}
	$fields[]="`zDate`";
	$values[]="'". date('Y-m-d H:i:s')."'";
	
	
	while (list ($num, $line) = each ($val) ){
		if($num==null){continue;}
		$fields[]="`$num`";
		$values[]="'$line'";
		
	}	
	
	
	$sql="INSERT IGNORE INTO kav4proxy_av_stats (".@implode(",", $num).") VALUES(".@implode(",", $values).")";
	if($GLOBALS["VERBOSE"]){writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
		$unix->send_email_events("Process antivirus statistics failed, mysql errors", "Query was: $sql\nError was:$q->mysql_error\nData was\n".@file_get_contents("/var/log/kaspersky/kav4proxy/counter.stats"), "proxy");
		return;
	}
	@unlink("/var/log/kaspersky/kav4proxy/counter.stats");
	
	
}






?>