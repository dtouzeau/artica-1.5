<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');



if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}




if($argv[1]=="--db-maintenance"){dbMaintenance();exit;}
if($argv[1]=="--maint-schedule"){dbMaintenanceSchedule();exit;}
if($argv[1]=="--build"){build();exit;}


function build(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		echo "Starting......: c-icap ". __FUNCTION__."() already running PID:$oldpid\n";
		return;
	}
	@file_put_contents($pidfile,getmypid());
	
	
	$cicap=new cicap();
	$cicap->buildconf();
	
	$squid=new squidbee();
	$conf=$squid->BuildSquidConf();
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	echo "Starting......: c-icap reconfigure squid done...\n";
	@file_put_contents($SQUID_CONFIG_PATH,$conf);
	@mkdir("/usr/etc",0755,true);
	CicapMagic("/usr/etc/c-icap.magic");
	
	dbMaintenanceSchedule();
	
}


function dbMaintenance(){
	$sock=new sockets();
	$unix=new unix();
	$users=new usersMenus();
	$verbose=$GLOBALS["VERBOSE"];
	$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
	if(!$users->SQUIDGUARD_INSTALLED){
		if(!$users->APP_UFDBGUARD_INSTALLED){
			if($verbose){echo "SQUIDGUARD_INSTALLED  =  FALSE\n";}
		}
		return; 
	}
	
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	
	if($unix->process_exists(@file_get_contents($pidfile))){
		echo "Already instance ".@file_get_contents($pidfile)." exists\n";
		return;
	}
	@file_put_contents($pidfile,getmypid());	
	
	
	$db_recover=$unix->LOCATE_DB_RECOVER();
	$db_stat=$unix->LOCATE_DB_STAT();
	
	if(strlen($db_recover)<3){
		echo "db_recover no such file\n";
		return;
	}
	
if($verbose){echo "db_recover:$db_recover\n";}
if($verbose){echo "db_stat:$db_stat\n";}
	
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";

	
	echo "Stopping c-icap\n";
	shell_exec("/etc/init.d/artica-postfix stop cicap");
		
	echo "Checking databases used\n";
	
	$datas=explode("\n",@file_get_contents("/etc/c-icap.conf"));
	while (list ($num, $line) = each ($datas)){
		if(preg_match("#url_check\.LoadSquidGuardDB\s+(.+?)\s+(.+)#",$line,$re)){
			$dir=trim($re[2]);
			
			if(substr($dir,strlen($dir)-1,1)=='/'){$dir=substr($dir,0,strlen($dir)-1);}
			$array[$dir]=$re[1];
		}
		
		
	}
	
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	while (list ($num, $line) = each ($datas)){
		if(preg_match("#domainlist\s+(.+)#",$line,$re)){
			$re[1]=trim($re[1]);
			$re[1]=dirname($re[1]);
			$dir="/var/lib/squidguard/".trim($re[1]);
			if(substr($dir,strlen($dir)-1,1)=='/'){$dir=substr($dir,0,strlen($dir)-1);}
			$array[$dir]="SquidGuard DB {$re[1]}";
		}
		
		
	}	
	
	if(!is_array($array)){
		echo "No databases, aborting\n";
		return;
	}
	
	while (list ( $directory,$dbname) = each ($array)){
		echo "\nChecking DB $dbname in $directory\n==============================\n";
		$cmd="$db_recover -h $directory/ -v 2>&1";
		if($verbose){echo "$cmd\n";}
		exec($cmd,$results);
		if($verbose){$LOGS[]=$cmd;}
		$LOGS[]="\nmaintenance on $dbname\n==============================\n".@implode("\n",$results);
		unset($results);
		if(is_file("$directory/urls.db")){
			$cmd="$db_stat -d $directory/urls.db 2>&1";
			if($verbose){echo "$cmd\n";}
			if($verbose){$LOGS[]=$cmd;}
			exec($cmd,$results);
			$LOGS[]="\nstatistics on $directory/urls.db\n============================================================\n".@implode("\n",$results);
			unset($results);
		}else{
			$LOGS[]="\nstatistics on $directory/urls.db no such file";		
		}
		
		if(is_file("$directory/domains.db")){
			$cmd="$db_stat -d $directory/domains.db 2>&1";
			if($verbose){echo "$cmd\n";}
			if($verbose){$LOGS[]=$cmd;}
			exec($cmd,$results);
			$LOGS[]="\nstatistics on $directory/domains.db\n============================================================\n".@implode("\n",$results);
			unset($results);
		}else{
			$LOGS[]="\nstatistics on $directory/domains.db no such file";		
		}
		
		if(is_file("$directory/expressions.db")){
			$cmd="$db_stat -d $directory/expressions.db 2>&1";
			if($verbose){echo "$cmd\n";}
			if($verbose){$LOGS[]=$cmd;}
			exec($cmd,$results);
			$LOGS[]="\nstatistics on $directory/expressions.db\n============================================================\n".@implode("\n",$results);
			unset($results);
		}else{
					
		}		
		
	}

	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart cicap");
	
	
	send_email_events("Maintenance on Web Proxy urls Databases: ". count($array)." database(s)",@implode("\n",$LOGS)."\n","system");
	if($verbose){echo @implode("\n",$LOGS)."\n";}	

	
}

function dbMaintenanceSchedule(){
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		echo "Starting......: c-icap ". __FUNCTION__."() already running PID:$oldpid\n";
		return;
	}
	@file_put_contents($pidfile,getmypid());	
	
	@unlink("/etc/crond.d/artica-cron-squidguarddb");
	$users=new usersMenus();
	if(!$users->SQUIDGUARD_INSTALLED){
		if(!$users->APP_UFDBGUARD_INSTALLED){
			writelogs("SQUIDGUARD_INSTALLED -> FALSE",__FUNCTION__,__FILE__,__LINE__);
			return null;
	}}
	$sock=new sockets();
	$time=unserialize(base64_decode($sock->GET_INFO("SquidGuardMaintenanceTime")));	
	if($time["DBH"]==null){$time["DBH"]=23;}
	if($time["DBM"]==null){$time["DBM"]=45;}	
	
	$h[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
	$h[]="MAILTO=\"\"";
	$h[]="{$time["DBM"]} {$time["DBH"]} * * *  root ". LOCATE_PHP5_BIN2()." ".__FILE__." --db-maintenance";
	$h[]="";
	@file_put_contents("/etc/cron.d/artica-cron-squidguarddb",@implode("\n",$h));
	writelogs("/etc/crond.d/artica-cron-squidguarddb DONE",__FUNCTION__,__FILE__,__LINE__);
	@chmod("/etc/crond.d/artica-cron-squidguarddb",640);
	shell_exec("/bin/chown root:root /etc/cron.d/artica-cron-squidguarddb");
}

function CicapMagic($path){
$f[]="# In this file defined the types of files and the groups of file types. ";
$f[]="# The predefined data types, which are not included in this file, ";
$f[]="# are ASCII, ISO-8859, EXT-ASCII, UTF (not implemented yet), HTML ";
$f[]="# which are belongs to TEXT predefined group and BINARY which ";
$f[]="# belongs to DATA predefined group.";
$f[]="#";
$f[]="# The line format of magic file is:";
$f[]="#";
$f[]="# offset:Magic:Type:Short Description:Group1[:Group2[:Group3]...]";
$f[]="#";
$f[]="# CURRENT GROUPS are :TEXT DATA EXECUTABLE ARCHIVE GRAPHICS STREAM DOCUMENT";
$f[]="";
$f[]="0:MZ:MSEXE:DOS/W32 executable/library/driver:EXECUTABLE";
$f[]="0:LZ:DOSEXE:MS-DOS executable:EXECUTABLE";
$f[]="0:\177ELF:ELF:ELF unix executable:EXECUTABLE";
$f[]="0:\312\376\272\276:JavaClass:Compiled Java class:EXECUTABLE";
$f[]="";
$f[]="#Archives";
$f[]="0:Rar!:RAR:Rar archive:ARCHIVE";
$f[]="0:PK\003\004:ZIP:Zip archive:ARCHIVE";
$f[]="0:PK00PK\003\004:ZIP:Zip archive:ARCHIVE";
$f[]="0:\037\213:GZip:Gzip compressed file:ARCHIVE";
$f[]="0:BZh:BZip:BZip compressed file:ARCHIVE";
$f[]="0:SZDD:Compress.exe:MS Copmress.exe'd compressed data:ARCHIVE";
$f[]="0:\037\235:Compress:UNIX compress:ARCHIVE";
$f[]="0:MSCF:MSCAB:Microsoft cabinet file:ARCHIVE";
$f[]="257:ustar:TAR:Tar archive file:ARCHIVE";
$f[]="0:\355\253\356\333:RPM:Linux RPM file:ARCHIVE";
$f[]="#Other type of Archives";
$f[]="0:ITSF:MSCHM:MS Windows Html Help:ARCHIVE";
$f[]="0:!<arch>\012debian:debian:Debian package:ARCHIVE";
$f[]="";
$f[]="# Graphics";
$f[]="0:GIF8:GIF:GIF image data:GRAPHICS";
$f[]="0:BM:BMP:BMP image data:GRAPHICS";
$f[]="0:\377\330:JPEG:JPEG image data:GRAPHICS";
$f[]="0:\211PNG:PNG:PNG image data:GRAPHICS";
$f[]="0:\000\000\001\000:ICO:MS Windows icon resource:GRAPHICS";
$f[]="0:FWS:SWF:Shockwave Flash data:GRAPHICS";
$f[]="0:CWS:SWF:Shockwave Flash data:GRAPHICS";
$f[]="";
$f[]="#STREAM";
$f[]="0:\000\000\001\263:MPEG:MPEG video stream:STREAM";
$f[]="0:\000\000\001\272:MPEG::STREAM";
$f[]="0:RIFF:RIFF:RIFF video/audio stream:STREAM";
$f[]="0:OggS:OGG:Ogg Stream:STREAM";
$f[]="0:ID3:MP3:MP3 audio stream:STREAM";
$f[]="0:\377\373:MP3:MP3 audio stream:STREAM";
$f[]="0:\377\372:MP3:MP3 audio stream:STREAM";
$f[]="0:\060\046\262\165\216\146\317:ASF:WMA/WMV/ASF:STREAM";
$f[]="0:.RMF:RMF:Real Media File:STREAM";
$f[]="";
$f[]="#Responce from stream server :-)";
$f[]="0:ICY 200 OK:ShouthCast:Shouthcast audio stream:STREAM";
$f[]="";
$f[]="#Documents";
$f[]="0:\320\317\021\340\241\261\032\341:MSOFFICE:MS Office Document:DOCUMENT";
$f[]="0:\208\207\017\224\161\177\026\225\000:MSOFFICE::DOCUMENT";
$f[]="4:Standard Jet DB:MSOFFICE:MS Access Database:DOCUMENT";
$f[]="0:%PDF-:PDF:PDF document:DOCUMENT";
$f[]="0:%!:PS:PostScript document:DOCUMENT";
$f[]="";
$f[]="";
@file_put_contents($path,@implode("\n",$f));
}


?>