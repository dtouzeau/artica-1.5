<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=='--check'){check();die();}
if($argv[1]=='--exists'){InMemQUestion();die();}
if($argv[1]=='--rebuild'){run();die();}
if($argv[1]=='--pid'){echo getPID()."\n";die();}



function check(){
$EnablePhileSight=GET_INFO_DAEMON("EnablePhileSight");
if($EnablePhileSight==null){$EnablePhileSight=0;}

	if($EnablePhileSight==0){
		writelogs("feature disabled, aborting...",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	
	
	if(system_is_overloaded()){
		writelogs("System overloaded, aborting this feature for the moment",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	@mkdir("/opt/artica/philesight");

	$unix=new unix();
	$min=$unix->file_time_min("/opt/artica/philesight/database.db");
	$sock=new sockets();
	$rr=$sock->GET_INFO("PhileSizeRefreshEach");
	if($rr==null){$rr=120;}
	if($rr=="disable"){die();}
	writelogs("/opt/artica/philesight/database.db = $min minutes, $rr minutes to run",__FUNCTION__,__FILE__,__LINE__);
	if($min>=$rr){
		run();
	}
}


function InMemQUestion(){
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN("philesight --db");
	if($unix->process_exists($pid)){return true;}
	return false;
}
function run(){
	$sock=new sockets();
	$PhileSizeCpuLimit=$sock->GET_INFO("PhileSizeCpuLimit");
	if($PhileSizeCpuLimit==null){$PhileSizeCpuLimit=0;}	
	
if(InMemQUestion()){
		writelogs("Already running, aborting",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}	
	chdir("/usr/share/artica-postfix/bin");
	$unix=new unix();
	$tmpfile=$unix->FILE_TEMP();
	$cmd=$unix->find_program("nohup") ." /usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --index / >$tmpfile &";
	echo $cmd."\n";
	
	shell_exec($cmd);
	sleep(3);
	$f=explode("\n",@file_get_contents($tmpfile));
	@unlink($tmpfile);
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#run database recovery#",$ligne)){
			$corrupted=true;
		}
	}
	
	if($corrupted){
		@unlink("/opt/artica/philesight/database.db");
		shell_exec($cmd);
	}
	
	if($PhileSizeCpuLimit==0){return ;}
	sleep(3);
	$pid=getPID();
	echo "Pid=$pid\n";
	if($pid>2){
		$cpulimit=$unix->find_program("cpulimit");
		echo "cpulimit=$cpulimit\n";
		if(is_file($cpulimit)){
			$cmd_limit="$cpulimit -p $pid -l $PhileSizeCpuLimit -z >>/var/log/cpulimit 2>&1 &";
			
			sleep(1);
			echo $cmd_limit."\n";
			shell_exec($cmd_limit);
		}
	}
	

}

function getPID(){
	$unix=new unix();
	exec($unix->find_program("pgrep"). " -l -f \"/usr/share/artica-postfix/bin/philesight\"",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#pgrep#",$ligne)){continue;}
		if(preg_match("#^([0-9]+).+?philesight#",$ligne,$re)){return $re[1];}
	}	
}


?>