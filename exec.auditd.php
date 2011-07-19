<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if(preg_match("#--simule#",implode(" ",$argv))){$GLOBALS["SIMULE"]=true;$GLOBALS["SIMULE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;$GLOBALS["FORCE"]=true;}


if($argv[1]=="--build"){build();	die();}
if($argv[1]=='--import'){build();import();die();}
if($argv[1]=='--conf'){conf();die();}



function build(){
	
	$unix=new unix();
	$auditctl=$unix->find_program("auditctl");
	if($GLOBALS["VERBOSE"]){echo "$auditctl -D\n";}
	shell_exec("$auditctl -D");
	
	// auditctl -a exit,always -F dir=/home/dtouzeau -F perm=wra
	$sql="SELECT * FROM auditd_dir";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("$sql $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$cmd="$auditctl -a exit,always -F dir=\"{$ligne["dir"]}\" -F perm=wra -k {$ligne["key"]}";
		if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
		system($cmd);
	}	
	
	$cmd="$auditctl -e 1";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	shell_exec("$auditctl -e 1");
}

function import(){
	$pidfile="/etc/artica-postfix/auditd.pid";
	$unix=new unix();
	if($unix->process_exists(@file_get_contents($pidfile))){die();}
	@file_put_contents($pidfile,getmypid());
	
	//date_default_timezone_set('Europe/Paris');
	//setlocale(LC_ALL, $lang,"en_US.utf8","fr_FR.utf8","nl_BE.utf8","nl_NL.utf8",'de_DE@euro', 'de_DE', 'deu_deu');

	$sock=new sockets();
	$EnableAuditd=$sock->GET_INFO("EnableAuditd");
	$AuditFrequency=$sock->GET_INFO("AuditFrequency");
	if($EnableAuditd==null){$EnableAuditd=1;}
	if($AuditFrequency==null){$AuditFrequency=10;}
	if($EnableAuditd<>1){die();}
	
	if(!$GLOBALS["FORCE"]){
		$timefile="/etc/artica-postfix/auditd.time";
		$Filetime=file_get_time_min($timefile);
		if($Filetime<$AuditFrequency){die();}
		@unlink($timefile);
		@file_put_contents($timefile,"#");		
	}	

	
	$ausearch=$unix->find_program("ausearch");
	$sock=new sockets();
	$AuditdTimeCode=$sock->GET_INFO("AuditdTimeCode");
	echo "LANG: $lang timecode=$AuditdTimeCode\n";
	if($AuditdTimeCode>0){
		$start=" -ts ".strftime("%D %T",$AuditdTimeCode);
	}
	
	$FinalAudiTimeCode=time();
	$end=" -te ".strftime("%D %T",$FinalAudiTimeCode);
	
	$sql="SELECT `key` FROM auditd_dir";
	$q=new mysql();
	$q->CheckTables_rsync();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("$sql $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		unset($results2);
		$key_path=$ligne["key"];
		echo "KEY:$key_path\n";
		$cmd="$ausearch -i -k $key_path $start $end";
		echo $cmd."\n";
		exec($cmd,$results2);
		while (list ($num, $line) = each ($results2) ){
			if(preg_match("#type=PATH\s+msg=audit\(([0-9\/\.]+)\s+([0-9:]+).+?name=(.+?)\s+inode=.+?mode=file.+?ouid=(.+?)\s+ogid=(.+?)\s+#",$line,$re)){
				$date=trim($re[1])." ".trim($re[2]);
				$time_h=$re[2];
				if(strlen($date)<3){if($GLOBALS["SIMULE"]){echo "Failed : $line\n";}}
				$file=trim($re[3]);
				$file=addslashes($file);
				$uid=trim($re[4]);
				$guid=trim($re[5]);
				continue;	
			}
		
			if(preg_match("#type=SYSCALL msg=audit.+?syscall=([a-zA-Z0-9]+)\s+success=.+exe=(.+?)key=#",$line,$re)){
				$syscall=trim($re[1]);
				$exe=trim($re[2]);
				//echo "$date $file ($access)\n";
				$date=str_replace("/","-",$date);
				$date=str_replace(".","-",$date);
				
				$timestamp=strtotime($date);
				$newdate=date('Y-m-d H:i:s',$timestamp);
				if($newdate=="1970-01-01 01:00:00"){$newdate=date("Y-m-d")." $time_h";}
				//writelogs("LANG:$lang::DATE:$date -> $timestamp -> $newdate",__FUNCTION__,__FILE__,__LINE__);
				if(strtotime($newdate)>time()){
					$year=date("Y");
					$month=date("m");
					$day=date("d")-1;
					$newdate="$year-$month-$day $time_h";
					
				}
				if($GLOBALS["SIMULE"]){echo "LANG:$lang::DATE:$date -> $timestamp -> $newdate FILE:$file\n";}
				if(trim($file)==null){continue;}
				if(trim($file)=="(null)"){continue;}
				$sqltOaDD="('$newdate','$file','$syscall','$uid','$guid','$exe','$key_path')\n";
				if($GLOBALS["SIMULE"]){echo "$sqltOaDD\n";}
				IF($GLOBALS["md"][md5($sqltOaDD)]==true){continue;}
				$VALUES[]=$sqltOaDD;
				$GLOBALS["md"][md5($sqltOaDD)]=true;
				$sqltOaDD=null;
				continue;
			}
		
		}
		echo "$key_path:: ". count($VALUES)." rows -> next\n";
		Purge($key_path);
		
		
	}
	
echo count($VALUES)." rows final\n";
writelogs(count($VALUES)." rows time code=$FinalAudiTimeCode",__FUNCTION__,__FILE__,__LINE__);
if(count($VALUES)>0){
	$sql="INSERT INTO auditd_files (`time`,`file`,`syscall`,`uid`,`gid`,`executable`,`key_path`)
	VALUES
	".implode(",",$VALUES);
	$q=new mysql();
	@file_put_contents("/tmp/sql.txt",$sql);
	if($GLOBALS["SIMULE"]){echo $sql."\n";}
	if(!$GLOBALS["SIMULE"]){$q->QUERY_SQL($sql,"artica_backup");}
	if($q->ok){
		writelogs("success ". count($VALUES)." rows time code=$FinalAudiTimeCode",__FUNCTION__,__FILE__,__LINE__);
		echo "success ". count($VALUES)." rows time code=$FinalAudiTimeCode\n";
		if(!$GLOBALS["SIMULE"]){$sock->SET_INFO("AuditdTimeCode","$FinalAudiTimeCode");}
	}else{
		send_email_events("APP_AUDITD error mysql","Error importing ".count($VALUES)." rows $q->mysql_error","audit");
		echo "failed $q->mysql_error\n";
	}
	
}else{
	
	if(!$GLOBALS["SIMULE"]){$sock->SET_INFO("AuditdTimeCode","$FinalAudiTimeCode");}
}
	
	
}

function conf(){
	
	$sock=new sockets();
	$datas=$sock->GET_INFO("AuditDDaemonConf");
	if(strlen($datas)<50){return;}
	@file_put_contents("/etc/audit/auditd.conf",$datas);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart auditd");
	
}

function Purge($key){
	$sock=new sockets();
	$AuditMaxEventsInDatabase=$sock->GET_INFO("AuditMaxEventsInDatabase");
	if($AuditMaxEventsInDatabase==null){$AuditMaxEventsInDatabase=1000000;}
	$sql="SELECT COUNT(ID) as tcount FROM auditd_files WHERE `key_path`='$key'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	writelogs("$key {$ligne["tcount"]} events MAX=$AuditMaxEventsInDatabase",__FUNCTION__,__FILE__,__LINE__);
	if($ligne["tcount"]>$AuditMaxEventsInDatabase){
		$sql="DELETE FROM auditd_files WHERE `key_path`='$key' LIMIT 0,$AuditMaxEventsInDatabase ORDER BY ID";
	}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		send_email_events("APP_AUDITD error mysql (unable to purge)","Error purge {$ligne["tcount"]} rows $q->mysql_error\n$sql","audit");
		echo "failed $q->mysql_error\n";
	}
}





?>