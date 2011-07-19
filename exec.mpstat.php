<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) .'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");




if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
if(system_is_overloaded()){events(basename(__FILE__).":: die, overloaded");die();}

if($argv[1]=='email'){BuildWarning('100','0');exit;}

	$timef=file_get_time_min("/etc/artica-postfix/croned.2/".md5(__FILE__));
	if($timef<5){
		events("die, 5mn minimal current {$timef}mn");die();
	}
	
	@unlink("/etc/artica-postfix/croned.2/".md5(__FILE__));
	@file_put_contents("/etc/artica-postfix/croned.2/".md5(__FILE__),date('Y-m-d H:i:s'));


	$artica=new artica_general();
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();
	$sock=new sockets();
	$ini->loadString($sock->GET_INFO("SmtpNotificationConfig"));
	$filestatus="/etc/artica-postfix/mpstat.status";
	$page=CurrentPageName();
	

	$timestamp=mktime(date("H"),date("i"),0,date('m'),date('Y'));
	$timestamp_string=date("H").",".date("i").",".date('j');
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->GET_INFO("SmtpNotificationConfig"));


if($ini->_params["SMTP"]["SystemCPUAlarm"]==null){$ini->_params["SMTP"]["SystemCPUAlarm"]=1;}
if($ini->_params["SMTP"]["SystemCPUAlarmPourc"]==null){$ini->_params["SMTP"]["SystemCPUAlarmPourc"]=95;}
if($ini->_params["SMTP"]["SystemCPUAlarmMin"]==null){$ini->_params["SMTP"]["SystemCPUAlarmMin"]=5;}	

if($ini->_params["SMTP"]["enabled"]<>1){
	events("$page SMTP notification is not enabled");
	die();
}

if($ini->_params["SMTP"]["SystemCPUAlarm"]<>1){
	events("$page system monitor notification is not enabled");
	die();
}

$vals=trim(exec('/usr/share/artica-postfix/bin/cpu-alarm.pl'));
$cpu=intval($vals);

if(!is_file($filestatus)){
	file_put_contents($filestatus,"$timestamp_string;$cpu\n");
	events("$page CPU: $cpu%");
	die();
}
$cpu_total=0;
$count=0;
$file_datas=explode("\n",file_get_contents($filestatus));
events("$filestatus=". count($file_datas)." lines number");
$old_timestamp=0;


while (list ($num, $ligne) = each ($file_datas) ){
	if(trim($ligne==null)){continue;}
	usleep(300000);
	if(preg_match('#^([0-9,]+);(.+)#',$ligne,$re)){
		$newfileARRAY[]=$ligne;
		$count=$count+1;
		$t=explode(",",$re[1]);
		if($old_timestamp==0){
			$old_timestamp=mktime($t[0],$t[1],0,date('m'),date('Y'));
			events("old_timestamp=$old_timestamp line $num");
		}
		$cpu_total=$cpu_total+intval(trim($re[2]));
	}else{
		events("$page unable to preg_match $ligne");
	}
}
$cpu_total=$cpu_total+$cpu;
$cpuaverage=floor($cpu_total/($count+1));
$difference = ($timestamp - $old_timestamp);
$difference=str_replace("-",'',$difference);
$difference=intval($difference);	 
$filetime=floor($difference/60);
$newfileARRAY[]="$timestamp_string;$cpu";


events("$page CPU average: $cpuaverage% last cpu in ".$filetime." minute(s) \"$difference\" [must reach {$ini->_params["SMTP"]["SystemCPUAlarmMin"]}mn] cache file=$count line(s): current: $cpu%");

if($filetime<$ini->_params["SMTP"]["SystemCPUAlarmMin"]){
	file_put_contents($filestatus,implode("\n",$newfileARRAY));
	die();
}



		if($cpuaverage>=$ini->_params["SMTP"]["SystemCPUAlarmPourc"]){
			events("$page Build warning CPU overload $cpu%");
			BuildWarning($cpuaverage,$filetime);
			
			}
events("$page Clean cache...");			
unset($newfileARRAY);
$newfileARRAY[]="$timestamp_string;$cpu";
file_put_contents($filestatus,implode("\n",$newfileARRAY));




function BuildWarning($cpu,$time){
	$subject="CPU overload ($cpu%)";
	exec("/bin/ps -w axo ppid,pcpu,pmem,time,args --sort -pcpu,-pmem|/usr/bin/head --lines=20 >/tmp.top.txt 2>&1");
	$top=file_get_contents("/tmp.top.txt");
	@unlink("/tmp.top.txt");
	$text="Artica report that your $hostname server has reach $cpu% CPU average consumption in $time minute(s)\n
	You will find below a processes report:\n---------------------------------------------\n\n$top";
	send_email_events($subject,$text,'system');
}





function events($text){
		include_once(dirname(__FILE__)."/framework/class.unix.inc");
		$logFile="/var/log/artica-postfix/artica-status.debug";
		$f=new debuglogs();
		$f->debuglogs($text);
		}
?>