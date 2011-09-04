<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.milter.greylist.inc');


include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/ressources/class.fetchmail.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.maincf.multi.inc");


$GLOBALS["ROOT"]=true;
parsecmdlines($argv);
$unix=new unix();
$sock=new sockets();
$_GLOBAL["miltergreylist_bin"]=$unix->find_program("milter-greylist");
if(!is_file($_GLOBAL["miltergreylist_bin"])){die();}
$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
if($EnablePostfixMultiInstance==1){
	if($GLOBALS["STATUS"]){	MultiplesInstances_status();die();}
	if($GLOBALS["START_ONLY"]==1){	MultiplesInstances_start($GLOBALS["hostname"],$GLOBALS["ou"]);die();}
	if($GLOBALS["STOP_ONLY"]==1){	MultiplesInstances_stop($GLOBALS["hostname"],$GLOBALS["ou"]);die();}
	
	if($argv[1]=="--database"){parse_multi_databases();die();}
	MultiplesInstances($GLOBALS["hostname"],$GLOBALS["ou"]);exit;
}


if($argv[1]=="--database"){parse_database("/var/milter-greylist/greylist.db","master");die();}


SingleInstance();

function parsecmdlines($argv){
	$GLOBALS["COMMANDLINE"]=implode(" ",$argv);
	if(strpos($GLOBALS["COMMANDLINE"],"--verbose")>0){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;}	
	while (list ($num, $ligne) = each ($argv) ){
		
		if(preg_match("#--verbose#",$ligne)){
			$GLOBALS["DEBUG"]=true;
			$GLOBALS["VERBOSE"]=true;
			ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);
		}
		
	if(preg_match("#--norestart#",$ligne)){
			$GLOBALS["NORESTART"]=true;
		}		
	
		
		if(preg_match("#--hostname=(.+)#",$ligne,$re)){
			$GLOBALS["hostname"]=$re[1];
			continue;
		}
		
		if(preg_match("#--ou=(.+)#",$ligne,$re)){
			$GLOBALS["ou"]=base64_decode($re[1]);
			continue;
		}	

		if(preg_match("#--start#",$ligne)){
			$GLOBALS["START_ONLY"]=1;
			continue;
		}	

		if(preg_match("#--stop#",$ligne)){
			$GLOBALS["STOP_ONLY"]=1;
			continue;
		}	

		if(preg_match("#--status#",$ligne)){
			$GLOBALS["STATUS"]=true;
		}		
		
	}
	
if($GLOBALS["DEBUG"]){echo "parsecmdlines ou={$GLOBALS["ou"]} hostname={$GLOBALS["hostname"]} STOP={$GLOBALS["STOP_ONLY"]} START={$GLOBALS["START_ONLY"]}\n";}
}

function SingleInstance(){
	$sock=new sockets();
	$MilterGreyListEnabled=$sock->GET_INFO("MilterGreyListEnabled");
	if(!is_numeric($MilterGreyListEnabled)){$MilterGreyListEnabled=0;}
	if($MilterGreyListEnabled==0){
		echo "Starting......: Milter-greylist is not enabled\n";
		return;
	}
	
	$mg=new milter_greylist(false,"master","master");
	$datas=$mg->BuildConfig();
	if($datas<>null){
		$conf_path=SingleInstanceConfPath();
		@mkdir(dirname($conf_path),0666,true);
		echo "Starting......: single instance $conf_path\n";
		echo "Starting......: cleaning $conf_path\n";
		
		$tbl=explode("\n",$datas);
		while (list ($num, $ligne) = each ($tbl) ){
			$ligne=trim($ligne);
			if($ligne==null){continue;}
			$newf[]=$ligne;
		}
		$newf[]="";
		echo "Starting......: writing $conf_path\n";
		@file_put_contents($conf_path,@implode("\n",$newf));
	}
	if(!$GLOBALS["NORESTART"]){
		shell_exec("/etc/init.d/artica-postfix restart mgreylist --noconfig >/tmp/start.miltergreylist.tmp 2>&1");
		if($GLOBALS["DEBUG"]){echo "\n".@file_get_contents("/tmp/start.miltergreylist.tmp")."\n";}
		@unlink("/tmp/start.miltergreylist.tmp");
	}
	
}

function SingleInstanceConfPath(){
if(is_file('/etc/milter-greylist/greylist.conf')){return '/etc/milter-greylist/greylist.conf';}
if(is_file('/etc/mail/greylist.conf')){return '/etc/mail/greylist.conf';}
if(is_file('/opt/artica/etc/milter-greylist/greylist.conf')){return '/opt/artica/etc/milter-greylist/greylist.conf';}
return '/etc/mail/greylist.conf';
}

function parse_multi_databases(){
		$sock=new sockets();
		$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));	
	
	$sql="SELECT ValueTEXT,ip_address,ou FROM postfix_multi WHERE `key`='PluginsEnabled' AND uuid='$uuid'";
	if($GLOBALS["DEBUG"]){echo "$sql\n";}
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo __FUNCTION__. " $q->mysql_error\n";}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$array_filters=unserialize(base64_decode($ligne["ValueTEXT"]));
		if($GLOBALS["DEBUG"]){echo "{$ligne["ip_address"]} APP_MILTERGREYLIST ->{$array_filters["APP_MILTERGREYLIST"]}  \n";}
		if($array_filters["APP_MILTERGREYLIST"]==null){continue;}
		if($array_filters["APP_MILTERGREYLIST"]==0){continue;}
		$hostname=MultiplesInstancesGetmyhostname($ligne["ip_address"]);
		$ou=$ligne["ou"];
		if($GLOBALS["DEBUG"]){echo "$hostname -> $ou\n";}
		$GLOBALS["hostnames"][$hostname]=$ou;
		$file="/var/milter-greylist/$hostname/greylist.db";
		parse_database($file,$hostname);
	}	
	
}

function MultiplesInstances($hostname=null,$ou=null){
	
	if(($ou==null) && ($hostname==null)){MultiplesInstancesFound();return;}
	if($ou==null){echo __FUNCTION__." unable to get ou name\n";return;}
	if($hostname==null){echo __FUNCTION__." unable to get hostname name\n";return;}	
	$mg=new milter_greylist(false,$hostname,$ou);
	$datas=$mg->BuildConfig();
	@mkdir("/etc/milter-greylist/$hostname",0666,true);
	@mkdir("/var/spool/$hostname/run/milter-greylist",0666,true);
	
		$tbl=explode("\n",$datas);
		while (list ($num, $ligne) = each ($tbl) ){
			$ligne=trim($ligne);
			if($ligne==null){continue;}
			$newf[]=$ligne;
		}
		$newf[]="";
		echo "Starting......: writing $conf_path\n";
		$datas=@implode("\n",$newf);	
	
	@file_put_contents("/etc/milter-greylist/$hostname/greylist.conf",$datas);
	echo "Starting......: milter-greylist $hostname or=$ou START_ONLY={$GLOBALS["START_ONLY"]},STOP_ONLY={$GLOBALS["STOP_ONLY"]}\n";
	if($GLOBALS["STOP_ONLY"]==1){MultiplesInstances_stop($hostname,$ou);}
	if($GLOBALS["START_ONLY"]==1){MultiplesInstances_start($hostname,$ou);}

}

function MultiplesInstancesGetmyhostname($ip_address){
		$sock=new sockets();
		$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));	
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' AND uuid='$uuid' AND ip_address='$ip_address'";
		if($GLOBALS["DEBUG"]){echo "$sql\n";}
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if(!$q->ok){
			echo __FUNCTION__. " $q->mysql_error\n";
		}
		if($GLOBALS["DEBUG"]){echo "$ip_address -> {$ligne["value"]}\n";}
		return $ligne["value"];
}


function MultiplesInstancesFound(){
	
		$sock=new sockets();
		$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));	
	
	$sql="SELECT ValueTEXT,ip_address,ou FROM postfix_multi WHERE `key`='PluginsEnabled' AND uuid='$uuid'";
	if($GLOBALS["DEBUG"]){echo "$sql\n";}
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo __FUNCTION__. " $q->mysql_error\n";}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$array_filters=unserialize(base64_decode($ligne["ValueTEXT"]));
		if($GLOBALS["DEBUG"]){echo "{$ligne["ip_address"]} APP_MILTERGREYLIST ->{$array_filters["APP_MILTERGREYLIST"]}  \n";}
		if($array_filters["APP_MILTERGREYLIST"]==null){continue;}
		if($array_filters["APP_MILTERGREYLIST"]==0){continue;}
		$hostname=MultiplesInstancesGetmyhostname($ligne["ip_address"]);
		$ou=$ligne["ou"];
		if($GLOBALS["DEBUG"]){echo "$hostname -> $ou\n";}
		$GLOBALS["hostnames"][$hostname]=$ou;
		MultiplesInstances($hostname,$ou);
		
	}
	
	
	
}


function MultiplesInstances_start($hostname,$ou){
	$unix=new unix();
	$main=new maincf_multi($hostname,$ou);
	echo "Starting......: milter-greylist $hostname ($ou)\n";
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	if($array_filters["APP_MILTERGREYLIST"]==0){$enabled=false;}	
	
	$pid=MultiplesInstancesPID($hostname);
	if($unix->process_exists($pid)){echo "Starting......: milter-greylist $hostname already running PID $pid\n";
		return;
	}
	echo "Starting......: milter-greylist hostname \"$hostname\"\n";
	$bin_path=$unix->find_program("milter-greylist");
	
	@mkdir("/var/spool/postfix/var/run/milter-greylist/$hostname",666,true);
	@mkdir("/var/milter-greylist/$hostname",666,true);
	if(!is_file("/var/milter-greylist/$hostname/greylist.db")){@file_put_contents("/var/milter-greylist/$hostname/greylist.db"," ");}
	shell_exec("/bin/chmod 644 /var/milter-greylist/$hostname/greylist.db");
	
	
	if(!is_file("/etc/milter-greylist/$hostname/greylist.conf")){
		echo "Starting......: milter-greylist $hostname /etc/milter-greylist/$hostname/greylist.conf does not exists\n";
		MultiplesInstances($hostname,$ou);return ;
	}

	
	$cmdline="$bin_path -P /var/spool/postfix/var/run/milter-greylist/$hostname/greylist.pid";
	$cmdline=$cmdline." -p /var/spool/postfix/var/run/milter-greylist/$hostname/greylist.sock";
	$cmdline=$cmdline." -d /var/milter-greylist/$hostname/greylist.db";
	$cmdline=$cmdline." -f /etc/milter-greylist/$hostname/greylist.conf";
	
	if($GLOBALS["VERBOSE"]){echo $cmdline."\n";}
	
	system($cmdline);
	
	for($i=0;$i<20;$i++){
		$pid=MultiplesInstancesPID($hostname);
		if($unix->process_exists($pid)){
			echo "Starting......: milter-greylist $hostname started PID $pid\n";
			break;
		}
		sleep(1);	
	}
	
	$pid=MultiplesInstancesPID($hostname);
		if($unix->process_exists($pid)){
			shell_exec("/bin/chown -R postfix:postfix /var/spool/postfix/var/run");
			shell_exec("/bin/chmod -R 755 /var/spool/postfix/var/run");
			$main->ConfigureMilters();	
		}
	
}

function MultiplesInstances_stop($hostname){
	$unix=new unix();
	$pid=MultiplesInstancesPID($hostname);
	
	if(!$unix->process_exists($pid)){
		echo "Stopping milter-greylist.....: $hostname already stopped\n";
		return;
	}
	
	echo "Stopping milter-greylist.....: $hostname stopping pid $pid\n";
	system("/bin/kill $pid");

	for($i=0;$i<20;$i++){
		$pid=MultiplesInstancesPID($hostname);
		if(!$unix->process_exists($pid)){
			echo "Stopping milter-greylist.....: $hostname stopped\n";
			break;
		}
		echo "Stopping milter-greylist.....: $hostname waiting pid $pid\n";
		if($unix->process_exists($pid)){
			exec("/bin/kill $pid 2>&1",$results);
			if(preg_match("#No such process#",@implode(" ",$results))){
				echo "Stopping milter-greylist.....: $hostname stopped\n";
				break;
			}
			}
		sleep(1);	
	}
	
}
function MultiplesInstancesPID($hostname){$unix=new unix();return $unix->get_pid_from_file("/var/spool/postfix/var/run/milter-greylist/$hostname/greylist.pid");}

function MultiplesInstances_status(){
	$unix=new unix();
	$users=new usersMenus();
	$sock=new sockets();

	
	if(!$users->MILTERGREYLIST_INSTALLED){
	if($GLOBALS["DEBUG"]){echo __FUNCTION__ ." NoT installed\n";}
		return null;
	}
	$main=new maincf_multi($GLOBALS["hostname"],$GLOBALS["ou"]);
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	$enabled=$array_filters["APP_MILTERGREYLIST"];
	$pid_path="/var/spool/postfix/var/run/milter-greylist/{$GLOBALS["hostname"]}/greylist.pid";
	if($GLOBALS["DEBUG"]){echo __FUNCTION__ ."{$GLOBALS["hostname"]} ({$GLOBALS["ou"]}) -> enabled=$enabled\n";}
	$master_pid=trim(@file_get_contents($pid_path));
	if($GLOBALS["DEBUG"]){echo __FUNCTION__ ."master_pid=$master_pid\n";}
		
		$l[]="[MILTER_GREYLIST]";
		$l[]="service_name=APP_MILTERGREYLIST";
	 	$l[]="master_version=".GetVersionOf("milter-greylist");
	 	$l[]="service_cmd=mgreylist";	
	 	$l[]="service_disabled=$enabled";
	 	$l[]="pid_path=$pid_path";
	 	
	 	$l[]="remove_cmd=--milter-grelist-remove";
		if(!$unix->process_exists($master_pid)){$l[]="running=0";$l[]="";echo implode("\n",$l);exit;}	
		$l[]="running=1";
		$l[]=GetMemoriesOf($master_pid);
		$l[]="";
		if($GLOBALS["DEBUG"]){echo __FUNCTION__ ."FINISH\n";}
	echo implode("\n",$l);	
	
}
function GetVersionOf($name){
	exec("/usr/share/artica-postfix/bin/artica-install --export-version $name",$results);
	$version=trim(implode("",$results));
	$version=trim(implode("",$results));
	return $version;	
}
function GetMemoriesOf($pid){
	$unix=new unix();
	$rss=$unix->PROCESS_MEMORY($pid,true);
	$vm=$unix->PROCESS_CACHE_MEMORY($pid,true);
	exec("pgrep -P $pid",$results);
	$count=0;
	while (list ($num, $ligne) = each ($results) ){
		$ligne=trim($ligne);
		if($ligne<1){continue;}
		$count=$count+1;
		$rss=$rss+$unix->PROCESS_MEMORY($ligne,true);
		$vm=$vm+$unix->PROCESS_CACHE_MEMORY($ligne,true);		
		
	}
	if($count==0){$count=1;}
	$l[]="master_pid=$pid";	
    $l[]="master_memory=$rss";
    $l[]="master_cached_memory=$vm";
    $l[]="processes_number=$count";
	return implode("\n",$l);
	
}

function parse_database($filename,$hostname){
	if(!is_file($filename)){writelogs("Failed to open $filename no such file",__FUNCTION__,__FILE__,__LINE__);return ;}
	$users=new usersMenus();
	$handle = @fopen($filename, "r"); // Open file form read.
	if (!$handle) {writelogs("Fatal errror while open $filename",__FUNCTION__,__FILE__,__LINE__);return ;}
	$sqlA="DELETE FROM greylist_turples WHERE hostname='$hostname'";
	$prefix="INSERT IGNORE INTO greylist_turples(zmd5,ip_addr,mailfrom,mailto,stime,hostname) VALUES ";
	$q=new mysql();
	$q->QUERY_SQL($sqlA,"artica_events");
	$sql=array();
	while (!feof($handle)){
		$buffer = fgets($handle, 4096);
		if(trim($buffer)==null){continue;}
		if(preg_match("#(.+?)\s+(.*?)\s+(.+?)\s+([0-9]+)#", $buffer,$re)){
			$ip=$re[1];
			$from=$re[2];
			$from=str_replace("<", "", $from);
			$from=str_replace(">", "", $from);
			$to=$re[3];
			$to=str_replace("<", "", $to);
			$to=str_replace(">", "", $to);			
			$time=$re[4];
			$md5=md5("$ip$from$to$time$hostname");
			$sql[]="('$md5','$ip','$from','$to','$time','$hostname')";
			if(count($sql)>500){
				if($GLOBALS["VERBOSE"]){echo "Finally save ".count($sql)." events\n";}
				$newsql=$prefix." ".@implode(",", $sql);
				$q->QUERY_SQL($newsql,"artica_events");
				if(!$q->ok){echo $q->mysql_error."\n";return ;}
				$sql=array();
			}
			
			continue;
		}else{
			if($GLOBALS["VERBOSE"]){echo "no match $buffer\n";}
		}
		

		
		
	}
		
if(count($sql)>0){
	if($GLOBALS["VERBOSE"]){echo "Finally save ".count($sql)." events\n";}
	$newsql=$prefix." ".@implode(",", $sql);$q->QUERY_SQL($newsql,"artica_events");$sql=array();}
if(!$q->ok){echo $q->mysql_error."\n";return ;}	
$unix=new unix();
$tail=$unix->find_program("tail");
$chmod=$unix->find_program("chmod");
exec("$tail -n 2 $filename 2>&1",$tails);

while (list ($num, $ligne) = each ($tails) ){
		if(preg_match("#Summary:\s+([0-9]+)\s+records,\s+([0-9]+)\s+greylisted,\s+([0-9]+)\s+whitelisted,\s+([0-9]+)\s+tarpitted#", $ligne,$re)){
			$array["RECORDS"]=$re[1];
			$array["GREYLISTED"]=$re[2];
			$array["WHITELISTED"]=$re[3];
			$array["TARPITED"]=$re[4];
			if($GLOBALS["VERBOSE"]){print_r($array);}
			@file_put_contents("/usr/share/artica-postfix/ressources/logs/greylist-count-$hostname.tot", serialize($array));
			shell_exec("$chmod 755 /usr/share/artica-postfix/ressources/logs/greylist-count-$hostname.tot");
		}else{
			if($GLOBALS["VERBOSE"]){echo "no match $ligne\n";}
		}
}
			
}

// -P pidfile


?>