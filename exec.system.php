<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__)." Already executed.. aborting the process");
	die();
}

if($argv[1]=="--proxy"){PROXY();die();}
if($argv[1]=="--sys-paquages"){SYNC_PACKAGES();die();}



function PROXY(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$ini->loadString($sock->GET_INFO("ArticaProxySettings"));
		$ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
		$ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
		$ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
		$ArticaProxyServerUsername=$ini->_params["PROXY"]["ArticaProxyServerUsername"];
		$ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];
		$ArticaCompiledProxyUri=$ini->_params["PROXY"]["ArticaCompiledProxyUri"];	
		
		if(trim($ArticaProxyServerEnabled)<>"yes"){PROXY_DELETE();return;}
		if(trim($ArticaProxyServerName==null)){PROXY_DELETE();return;}
		if(trim($ArticaProxyServerPort==null)){$ArticaProxyServerPort=80;}
		if(!is_numeric($ArticaProxyServerPort)){$ArticaProxyServerPort=80;}
		
		if($ArticaProxyServerUsername<>null){
			$pattern="$ArticaProxyServerUsername";
			if($ArticaProxyServerUserPassword<>null){
				$pattern=$pattern.":$ArticaProxyServerUserPassword";
			}
			$pattern=$pattern."@";
		}
		echo "Starting......: Using proxy $ArticaProxyServerName:$ArticaProxyServerPort\n";
		$proxypattern="http://$pattern$ArticaProxyServerName:$ArticaProxyServerPort";
		
	
	$f=explode("\n",@file_get_contents("/etc/environment"));
	while (list ($key, $line) = each ($f) ){
		if(preg_match("#^HTTP_PROXY#i",$line)){unset($f[$key]);}
		
	}		
	$f[]="http_proxy=$proxypattern";
		
	
	@file_put_contents("/etc/environment",@implode("\n",$f));
	if(is_dir("/etc/apt/apt.conf.d")){
		echo "Starting......: Using proxy with apt-get, apt-mirror...\n";
		@file_put_contents("/etc/apt/apt.conf.d/proxy","Acquire::http::Proxy \"$proxypattern\";");}
	
		
		
	
}

function PROXY_DELETE(){
	$save=false;
	$f=explode("\n",@file_get_contents("/etc/environment"));
	while (list ($key, $line) = each ($f) ){
		if(preg_match("#^HTTP_PROXY#i",$line)){unset($f[$key]);}
	}
	
	if($save){@file_put_contents("/etc/environment",@implode("\n",$f));}
	if(is_file("/etc/apt/apt.conf.d/proxy")){@unlink("/etc/apt/apt.conf.d/proxy");}
	
}

function SYNC_PACKAGES(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$unix=new unix();
	$aptget=$unix->find_program("apt-get");
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		echo "Already exists PID $pid\n";
		return;
	}
	
	@file_put_contents($pidfile,getmypid());
	$time=time();
	exec("/usr/share/artica-postfix/bin/setup-ubuntu --check-base-system 2>&1",$results);
	while (list ($key, $line) = each ($results) ){
		if(preg_match("#Use.+?apt-get autoremove.+?to remove them#")){
			exec("$aptget autoremove -y -q 2>&1",$autoremove);
		}
		
	}
	
	$message_text=	@implode("\n",$results);
	if(count($autoremove)>0){
		$message_text=$message_text." Auto-remove task:\n".@implode("\n",$autoremove);
	}	
	
	$time_text=$unix->distanceOfTimeInWords($time,time(),true);
	$unix->send_email_events("Synchronize paquages done ($time_text)",@implode("\n",$message_text),"system");
	 shell_exec('/bin/rm -f /usr/share/artica-postfix/ressources/logs/cache/*');
	 shell_exec('/bin/rm -f /usr/share/artica-postfix/ressources/logs/jGrowl-new-versions.txt');
	 shell_exec('/bin/rm -f /etc/artica-postfix/versions.cache');
	 shell_exec('/bin/rm -f /usr/share/artica-postfix/ressources/logs/global.versions.conf');
	 shell_exec('/usr/share/artica-postfix/bin/artica-install --write-versions');
	 shell_exec('/usr/share/artica-postfix/bin/process1 --force &');
	 shell_exec('/etc/init.d/artica-postfix restart artica-status &');
	 shell_exec('/etc/init.d/artica-postfix restart artica-exec &');
	 shell_exec('rm -rf /usr/share/artica-postfix/ressources/web/logs/*.cache');	
	
	
	
}


