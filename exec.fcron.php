<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}


if($argv[1]=="--schedules-mldonkey"){schedules_mldonkey();die();}
if($argv[1]=="--postmaster-cron"){postmaster_cron();die();}
if($argv[1]=="--artica-schedule"){artica_schedule();die();}



function artica_schedule(){
		@unlink("/etc/crond.d/arscheduler");
		$sock=new sockets();
		$unix=new unix();
		$EnableScheduleUpdates=$sock->GET_INFO("EnableScheduleUpdates");
		if(!is_numeric($EnableScheduleUpdates)){$EnableScheduleUpdates=0;}
		if($EnableScheduleUpdates==0){return;}
		$ArticaScheduleUpdates=$sock->GET_INFO("ArticaScheduleUpdates");
		if($ArticaScheduleUpdates==null){$sock->SET_INFO("EnableScheduleUpdates", 0);return;}
 		
 		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="MAILTO=\"\"";
		$f[]="$ArticaScheduleUpdates  root /usr/share/artica-postfix/bin/artica-update --bycron";
		$f[]="";	
		@file_put_contents("/etc/crond.d/arscheduler",implode("\n",$f));
		$chmod=$unix->find_program("chmod");
		shell_exec("$chmod 640 /etc/crond.d/arscheduler");
		unset($f);	
}


function schedules_mldonkey(){
	
	@unlink("/etc/artica-postfix/mldonkey.tasks");
	$sql="SELECT * FROM mldonkey ORDER BY schedule_time DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");

	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$params=unserialize(base64_decode($ligne["parameters"]));
		$params["hours"];
		$params["minutes"];
		if($params["minutes"]==null){$params["minutes"]="59";}
		if($params["hours"]==null){$params["hours"]="0";}
		$f[]="{$params["minutes"]} {$params["hours"]} * * * ".LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.mldonkey.php --settings {$ligne["ID"]}";
	}
	
	if(is_array($f)){
		@file_put_contents("/etc/artica-postfix/mldonkey.tasks",@implode("\n",$f));	
	}
}


function postmaster_cron(){
	
	$sock=new sockets();
	$PostfixPostmaster=$sock->GET_INFO("PostfixPostmaster");
	$ar[]="/etc/cron.d";
	
	while (list ($index, $directory) = each ($ar) ){
	foreach (glob("$directory/*") as $filename) {
		postmaster_cron_check($PostfixPostmaster,$filename);
		}
	}

}

function postmaster_cron_check($email,$filename){
	$dirname=dirname($filename);
	$f=explode("\n",@file_get_contents($filename));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#MAILTO=(.*)#",$line,$re)){
			$orgmail=trim($re[1]);
			$orgmail=str_replace('"',"",$orgmail);
			$orgmail=str_replace("'","",$orgmail);
			if($orgmail<>$email){
				if($GLOBALS["VERBOSE"]){echo "$line Found \"$orgmail\" <> \"$email\"\n";}
				$f[$index]="MAILTO=\"$email\"";
				@file_put_contents($filename,@implode("\n",$f));
				echo "Starting......: Artica patching $dirname/".basename($filename)." $email\n";
				return;
			}
			return;
		}else{
			if($GLOBALS["VERBOSE"]){echo "$line no match #MAILTO=(.*)#\n";}
		}
		
	}
	$final="MAILTO=\"$email\"\n".@implode("\n",$f);
	@file_put_contents($filename,$final);
	echo "Starting......: Artica patching $dirname/".basename($filename)." $email\n";
	
	
}

?>