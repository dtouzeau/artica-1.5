<?php
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

cpulimit();

$unix=new unix();
$GLOBALS["omindex"]=$unix->find_program("omindex");
$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$GLOBALS["INDEXED"]=0;
$GLOBALS["SKIPPED"]=0;
if(file_exists($pidfile)){
	$currentpid=trim($pidfile);
	echo date('Y-m-d h:i:s')." NewPID PID: $pid\n";
	echo date('Y-m-d h:i:s')." Current PID: $currentpid\n";
	if($currentpid<>$pid){
		if(is_dir('/proc/'.$currentpid)){
			die(date('Y-m-d h:i:s')." Already instance executed");
	}else{
		echo date('Y-m-d h:i:s')." $currentpid is not executed continue...\n";
	}
		
	}
}
ScanQueue();
die();

function ScanQueue(){
	$users=new usersMenus();
	$GLOBALS["SAMBA_INSTALLED"]=$users->SAMBA_INSTALLED;
	$unix=new unix();
	$path="/var/log/artica-postfix/xapian";
	$SartOn=time();
	$files=$unix->DirFiles($path);
	if(count($files)==0){return;}
	cpulimitProcessName("omindex");
	while (list ($num, $file) = each ($files) ){
		$toScan="$path/$file";
		if(ScanFile($toScan)){
			@unlink($toScan);
		}
	}
$SartOff=time();
$time=distanceOfTimeInWords($SartOn,$SartOff);
$countdir=count($GLOBALS["DIRS"]);
cpulimitProcessNameKill("omindex");

$echo="InstantSearch {items}: {skipped}: {$GLOBALS["SKIPPED"]} {files}<br>{indexed}: {$GLOBALS["INDEXED"]} {files}<br>{duration}:$time";
if($GLOBALS["INDEXED"]>0){
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/xapian.results",$echo);
	@chmod("/usr/share/artica-postfix/ressources/logs/xapian.results",0777);
}
echo($echo."\n");	
	

}

function ScanFile($toScan){
	if(!$GLOBALS["SAMBA_INSTALLED"]){return true;}
	$localdatabase="/usr/share/artica-postfix/LocalDatabases";
	$file=@file_get_contents($toScan);
	$ext=Get_extension($file);
	$nice=EXEC_NICE();	
	$database="$localdatabase/samba.db";
	if(!is_file($GLOBALS["omindex"])){return true;}
	$directory=dirname($file);
	if($GLOBALS["DIRS"]["$directory"]){return true;}
	$basename=basename($file);
	
	$cmd="$nice{$GLOBALS["omindex"]} -l 1 --follow -D $database -U \"$directory\" \"$directory\"";
	$GLOBALS["DIRS"]["$directory"]=true;
	exec($cmd,$results);
	ParseLogs($results);
	return true;
	}

//xls2csv,antiword

function ParseLogs($array){
	if(!is_array($array)){return null;}
	while (list ($num, $ligne) = each ($array) ){
		if(trim($ligne)==null){continue;}
		if(preg_match('#^Indexing.+?\.\.\.\s+updated\.$#',trim($ligne))){
			$GLOBALS["INDEXED"]=$GLOBALS["INDEXED"]+1;
			continue;
		}
		
	if(preg_match('#.+skipping$#',$ligne)){
			$GLOBALS["SKIPPED"]=$GLOBALS["SKIPPED"]+1;
			continue;
		}	

	if(preg_match('#^Indexing.+#',trim($ligne))){
			$GLOBALS["INDEXED"]=$GLOBALS["INDEXED"]+1;
			continue;
		}

if(preg_match('#Skipping empty file#',trim($ligne))){
			$GLOBALS["SKIPPED"]=$GLOBALS["SKIPPED"]+1;
			continue;
		}			
		
	

	if(preg_match('#Entering directory#',$ligne)){
		continue;
	}
		
		
		
	}
	
	
}


function TransFormToHtml($file){
	if(!is_file($file)){return false;}
	$original_file=trim(file_get_contents("$file"));
	
 $attachmentdir=dirname($file);
 $fullmessagesdir=dirname($file);
 $attachmenturl='images.listener.php?mailattach=';   
   $cmd='/usr/bin/mhonarc ';
   $cmd=$cmd."-attachmentdir $attachmentdir ";
   $cmd=$cmd."-attachmenturl $attachmenturl ";
   $cmd=$cmd.'-nodoc ';
   $cmd=$cmd.'-nofolrefs ';
   $cmd=$cmd.'-nomsgpgs ';
   $cmd=$cmd.'-nospammode ';
   $cmd=$cmd.'-nosubjectthreads ';
   $cmd=$cmd.'-idxfname storage ';
   $cmd=$cmd.'-nosubjecttxt "no subject" ';
   $cmd=$cmd.'-single ';
   $cmd=$cmd.$original_file . ' ';
   $cmd=$cmd. ">$attachmentdir/message.html 2>&1";
   system($cmd);
   $size=filesize("$attachmentdir/message.html");
	write_syslog("Creating html  $attachmentdir/message.html ($size bytes)",__FILE__);
	
}




?>