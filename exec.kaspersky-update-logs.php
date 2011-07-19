<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
if($argv[1]=="--retrans"){ParseRetranslatorLogs();die();}

ParseKas3Logs();
ParseKav4ProxyLogs();
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
			@unlink("$dir/$file");
			continue;
		}
		
		
		if($NumberofKas3FilesUpdated>0){
			$subject="Kaspersky Antivirus Proxy: $NumberofKas3FilesUpdated new viruses in databases";
			send_email_events($subject,@file_get_contents("$dir/$file"),"KASPERSKY_UPDATES",$date);
					
		}
		
		@unlink("$dir/$file");
	}
}


function NumberofKas3FilesUpdated($path){
	$count=0;
	$datas=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($datas) ){
	if(preg_match("#^File updated.+#",$line)){
		$count=$count+1;
	}
	
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






?>