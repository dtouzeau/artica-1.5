<?php

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if($argv[1]=='--free'){FreeSync();}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

include_once("ressources/class.os.system.tools.inc");
$os=new os_system();
$mem=$os->memory();


$swap_percent=$mem["swap"]["percent"];
$swap_used=$mem["swap"]["used"];
$ram_free=$mem["ram"]["free"];
$ram_total=$mem["ram"]["total"];
$operation_disponible=$ram_free-$swap_used;


$max=str_replace("&nbsp;"," ",FormatBytes(round($ram_total/2)));

$swap_used=$mem["swap"]["used"];



$swap_used_mo=str_replace("&nbsp;"," ",FormatBytes($swap_used));
$ram_free_mo=FormatBytes($ram_free);
$log="swap used: $swap_percent% ({$swap_used_mo}) , Max $max ; free memory=$ram_free_mo, cache fore back=$operation_disponible";
echo $log."\n";


print_r($mem);

function events($text){
	$d=new debuglogs();
	$logFile="/var/log/artica-postfix/artica-swap-monitor.debug";
	$d->events(basename(__FILE__)." $text",$logFile);
}



function FreeSync(){
	if(!Build_pid_func(__FILE__,__FUNCTION__)){return null;}
	$unix=new unix();
	$sync=$unix->find_program("sync");
	@file_put_contents("/proc/sys/vm/drop_cache","1");
	shell_exec($sync);
	@file_put_contents("/proc/sys/vm/drop_cache","2");
	shell_exec($sync);	
	@file_put_contents("/proc/sys/vm/drop_cache","3");
	shell_exec($sync);		
	shell_exec("swapoff -a && swapon -a");
	@file_put_contents("/proc/sys/vm/drop_cache","0");
	
}


?>