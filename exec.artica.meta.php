<?php
include(dirname(__FILE__).'/ressources/class.qos.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.http.pear.inc');
include_once(dirname(__FILE__).'/ressources/class.artica-meta.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
	
	
$GLOBALS["MAXTTL"]=15;	
	
if($argv[1]=="--register"){register();exit;}
if($argv[1]=="--register-server"){register_server();exit;}
if($argv[1]=="--join"){register_server();exit;}
if($argv[1]=="--unjoin"){unregister_server();exit;}
if($argv[1]=="--status"){SendStatus();exit;}
if($argv[1]=="--ps"){pasmoinsaux();exit;}
if($argv[1]=="--net"){Networks();exit;}
if($argv[1]=="--settingsinc"){settings_inc();exit;}
if($argv[1]=="--top"){top10cpumem();exit;}
if($argv[1]=="--virtualbox"){VirtualBoxList();exit;}
if($argv[1]=="--ports"){OpenPorts();exit;}
if($argv[1]=="--checknet"){CheckNetwork();exit;}
if($argv[1]=="--connect"){connect($argv[2],$argv[3],$argv[4]);exit;}
if($argv[1]=="--ping"){SendPing();exit;}
if($argv[1]=="--system-check"){APTCHECK();exit;}
if($argv[1]=="--emergency"){emergency();exit;}
if($argv[1]=="--nets"){Networks();exit;}




function emergency($NotExecuteStatus=false){
	$unix=new unix();
	$framework_path="/var/run/lighttpd/framework.pid";
	if(!$unix->process_exists(@file_get_contents($framework_path))){
		events("Framework did not running in memory, start it",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/etc/init.d/artica-postfix start framework");
	}
	
	$status_pid_path="/etc/artica-postfix/exec.status.php.pid";
	if(!$unix->process_exists(@file_get_contents($status_pid_path))){
		events("status did not running in memory, start it",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/etc/init.d/artica-postfix start artica-status");
	}	
	$sock=new sockets();
	$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
	if($ArticaMetaEnabled<>1){
		events("Arica-Meta is not enabled has an agent, aborting",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	if(!$NotExecuteStatus){SendStatus();}
}

	
function Checks(){
	include_once("HTTP/Request.php");
	if(!class_exists("HTTP_Request")){
		$unix=new unix();
		$pear_bin=$unix->find_program("pear");
		if($pear_bin==null){
			writelogs("Fatal 'pear' no such file",__FUNCTION__,__FILE__,__LINE__);
			$p=Paragraphe('danger64.png',"{PEAR_NOT_INSTALLED}","{PEAR_NOT_INSTALLED_TEXT}",300,80);
			 @file_put_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",$p);
   			 @chmod("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",775);
			 return;
		
		}
		shell_exec("$pear_bin install HTTP_Request");
	}	
}	


function connect($username,$password,$serial){
	@file_put_contents("/etc/artica-postfix/settings/Daemons/ArticaMetaSerial",$serial);
	$array=unserialize(base64_decode(@file_get_contents("/etc/artica-postfix/settings/Daemons/ArticaMetaRegisterDatas")));
	if(!is_array($array)){$array=array();}
	
	$array["serial"]=$serial;
	$array["email"]=$username;
	$array["password"]=$password;
	$final=serialize($array);
	@file_put_contents("/etc/artica-postfix/settings/Daemons/ArticaMetaRegisterDatas",base64_encode($final));
	register_server(true);	
}


function register(){
	$sock=new sockets();
	$meta=new artica_meta();
	$datas=$sock->GET_INFO("ArticaMetaRegisterDatas");
	$http=new httpget();
	$body=$http->send("$meta->ArticaMetaHostname/lic.register.php","post",array("DATAS"=>$datas));
	
	if(preg_match("#<SERIAL>(.+?)</SERIAL>#s",$body,$re)){
		$sock=new sockets();
		$sock->SET_INFO("ArticaMetaSerial",$re[1]);
		$datas=unserialize(base64_decode($datas));
		$datas["LOCK"]=0;
		$sock->SaveConfigFile(base64_encode(serialize($datas)),"ArticaMetaRegisterDatas");
		register_server();
		
	}else{
		echo "Unable to get serial....\n";
	}
	
}

function register_server($cmdline=false){
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta(true);
	writelogs("perform registration",__FUNCTION__,__FILE__,__LINE__);
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.register.server.php","post",array("DATAS"=>$datasToSend));

	if($GLOBALS["VERBOSE"]){echo $body;}
	writelogs("perform registration body was \n$body\n",__FUNCTION__,__FILE__,__LINE__);
	$error=$meta->DetectError($body);
	if($error<>null){
		if($cmdline){echo "$error\n";}
		$meta->RegisterDatas["ERROR"]=true;
		$meta->RegisterDatas["ERROR_TEXT"]=$error;
		$meta->Save();
		return;
	}
	
	$meta->RegisterDatas["ERROR"]=false;
	$meta->RegisterDatas["REGISTERED"]=true;
	if($cmdline){echo "SUCCESS !!\n";}
	$sock->SET_INFO("ArticaMetaEnabled",1);
	$meta->Save();
	if($cmdline){echo "Send first status to the management console\n";}
	SendStatus();
	if($cmdline){echo "Done....\n";}
	
}

function unregister_server(){
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.unegister.server.php","post",array("DATAS"=>$datasToSend));
	events("perform unregistration",__FUNCTION__,__FILE__,__LINE__);
	
	$sock->SET_INFO("ArticaMetaEnabled",0);
	$sock->SET_INFO("DisableArticaMetaAgentInformations",0);	
	if($GLOBALS["VERBOSE"]){echo $body;}
	
	$error=$meta->DetectError($body);
	
	if($error<>null){
		writelogs("Error: $error",__FUNCTION__,__FILE__,__LINE__);
		$meta->RegisterDatas["ERROR"]=true;
		$meta->RegisterDatas["ERROR_TEXT"]=$error;
		$meta->RegisterDatas["REGISTERED"]=false;
		$meta->Save();
		return;
	}
	
	$meta->RegisterDatas["ERROR"]=false;
	$meta->RegisterDatas["REGISTERED"]=false;
	$meta->Save();
	
}


function TestsCron($pidfile){
	if($GLOBALS["VERBOSE"]){return false;}
	if($GLOBALS["FORCE"]){return false;}
	$sock=new sockets();
	$ArticaMetaPoolTimeMin=$sock->GET_INFO("ArticaMetaPoolTimeMin");
	if(!is_numeric($ArticaMetaPoolTimeMin)){$ArticaMetaPoolTimeMin=15;}
	if($ArticaMetaPoolTimeMin<2){$ArticaMetaPoolTimeMin=15;}
	$minutes=file_time_min($pidfile);
	
	if($minutes<$ArticaMetaPoolTimeMin){
		events("Stopping status, currently {$minutes}Mn waits {$ArticaMetaPoolTimeMin}Mn",__FUNCTION__,__FILE__,__LINE__);
		
		if(is_file("/etc/artica-postfix/artica-meta.tasks")){
			events("/etc/artica-postfix/artica-meta.tasks cache file exists",__FUNCTION__,__FILE__,__LINE__);
			$unix=new unix();
			$nohup=$unix->find_program("nohup");
			$EXEC_NICE=EXEC_NICE();
			$cmd=trim($nohup." ".$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.tasks.php >/dev/null 2>&1 &");
			events("$cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);
			events("Execution done....",__FUNCTION__,__FILE__,__LINE__);
		}		
		
		return true;
	
	}
	events("{$minutes}Mn since last execution waits {$ArticaMetaPoolTimeMin}Mn",__FUNCTION__,__FILE__,__LINE__);
}

function isDhcpDebian(){
	if(!is_file("/etc/network/interfaces")){return false;}
	$f=@explode("\n",@file_get_contents("/etc/network/interfaces"));
	while (list ($index, $ligne) = each ($f) ){
		if(preg_match("#iface.+?inet.+?dhcp#",$ligne)){return true;	}
	}
	
	
}

function CheckNetwork(){
	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?ifconfig-all-ips=yes")));
	unset($array["127.0.0.1"]);
	
	
	
	while (list ($index, $ligne) = each ($array) ){
		if(trim($index)==null){continue;}
		events("Found ip address $index",__FUNCTION__,__FILE__,__LINE__);
		$m[]=$index;
	}
	
	events(count($m)." interface(s)",__FUNCTION__,__FILE__,__LINE__);
	if(count($m)==0){
		events("No interfaces !!!",__FUNCTION__,__FILE__,__LINE__);
		if(isDhcpDebian()){
			events("Running dhclient",__FUNCTION__,__FILE__,__LINE__);
			$unix=new unix();
			if(is_file($unix->find_program("dhclient3"))){shell_exec($unix->find_program("dhclient3"));return true;}
			if(is_file($unix->find_program("dhclient"))){shell_exec($unix->find_program("dhclient"));return true;}	
		}else{
			events("No DHCP network style...",__FUNCTION__,__FILE__,__LINE__);
		}
	}
}

function SendPing(){
	emergency(true);
	$cmd=LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.tasks.php >/dev/null 2>&1 &";
	if(is_file("/etc/artica-postfix/artica-meta.tasks")){
		$meta=new artica_meta();
		$meta->events("artica-meta.tasks exists, execute tasks...",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		return;
	}
	$http=new httpget();
 	$meta=new artica_meta();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));	
	$ArticaMetaHostname=$meta->ArticaMetaHostname;
	$meta->events("Send Ping to meta console...",__FUNCTION__,__FILE__,__LINE__);
	$metaconsole=$http->send("$ArticaMetaHostname/lic.status.notifs.php","post",array("DATAS"=>$datasToSend,"PING"=>"yes"));
	if($metaconsole=="FAILED_CONNECT"){
		$meta->events("Result:\"$metaconsole\"",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
		
	if(preg_match("#<TASKS>(.+?)</TASKS>#is",$metaconsole,$re)){
		$meta->events("Save tasks to /etc/artica-postfix/artica-meta.tasks",__FUNCTION__,__FILE__,__LINE__);
		@file_put_contents("/etc/artica-postfix/artica-meta.tasks",$re[1]);
		$meta->events("TASKS ->$cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		return;
	}
	
	$meta->events("<results>\n$metaconsole\n</results>",__FUNCTION__,__FILE__,__LINE__);
		
	
}



function SendStatus(){
	emergency(true);
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$sock=new sockets();
	$EnableSargGenerator=$sock->GET_INFO("EnableSargGenerator");
	if(TestsCron($pidfile)){
		$ArticaMetaPingEnable=$sock->GET_INFO("ArticaMetaPingEnable");
		events("SendPing=$ArticaMetaPingEnable",__FUNCTION__,__FILE__,__LINE__);
		if($ArticaMetaPingEnable==1){SendPing();}
		return true;
	}
	
	$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
	
	if($ArticaMetaEnabled<>1){return;}
	
	$t1=time();
	if(!is_file("/usr/share/artica-postfix/ressources/logs/global.status.ini")){
		events("Unable to stat /usr/share/artica-postfix/ressources/logs/global.status.ini",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$unix=new unix();
	
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		$ptime=$unix->PROCESS_TTL($oldpid);
		if($ptime>$GLOBALS["MAXTTL"]){
			events("killing process $oldpid ttl:$ptime minutes",__FUNCTION__,__FILE__,__LINE__);
			shell_exec("/bin/kill -9 $oldpid");
		}else{
			events("Already executed, process $oldpid",__FUNCTION__,__FILE__,__LINE__);
			die();
		}
	}
	events("Running pid ".getmypid(),__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents($pidfile,getmypid());	
	CheckNetwork();
	$http=new httpget();
	$meta=new artica_meta();
	$filecache="/etc/artica-postfix/artica-meta-files.cache";
	events("My uuid=\"$meta->uuid\"",__FUNCTION__,__FILE__,__LINE__);
	
	
	$memCache="/usr/share/artica-postfix/ressources/logs/status.memory.hash";
	$cpu_graphs="/opt/artica/share/www/system/rrd/01cpu-1day.png";
	$server_status="/usr/share/artica-postfix/ressources/logs/status.right.1.html";
	$squid_realtime="/etc/artica-postfix/squid-realtime.cache";
	
	
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$ini=new Bs_IniHandler();
	$ini->loadFile("/usr/share/artica-postfix/ressources/logs/global.status.ini");
	$ArrayFileCache=unserialize(@file_get_contents($filecache));
	

	if(is_file($memCache)){
		$MEM_CACHE=base64_encode(@file_get_contents($memCache));
	}else{
		include_once("ressources/class.os.system.tools.inc");
		$os=new os_system();
		$os->html_Memory_usage();
		$MEM_CACHE=base64_encode(serialize($os->meta_array));
	}
	
	
	if(is_file($cpu_graphs)){
		if($ArrayFileCache["STATS_DAY"]<>filemtime($cpu_graphs)){
			$http->uploads["STATS_DAY"]=$cpu_graphs;
			$ArrayFileCache["STATS_DAY"]=filemtime($cpu_graphs);
			@file_put_contents($filecache,serialize($ArrayFileCache));
		}
	}
	
	if(is_file($server_status)){
		if($ArrayFileCache["SERVER_STATUS"]<>filemtime($server_status)){
			$http->uploads["SERVER_STATUS"]=$server_status;
			$ArrayFileCache["SERVER_STATUS"]=filemtime($server_status);
			@file_put_contents($filecache,serialize($ArrayFileCache));
		}
	}

	if(is_file($squid_realtime)){
		if($ArrayFileCache["SQUID_REALTIME"]<>filemtime($squid_realtime)){
			$http->uploads["SQUID_REALTIME"]=$squid_realtime;
			$ArrayFileCache["SQUID_REALTIME"]=filemtime($squid_realtime);
			@file_put_contents($filecache,serialize($ArrayFileCache));
		}
	}
	
	if($EnableSargGenerator==1){
	$push_sarg=false;
	if(is_file("/usr/share/artica-postfix/squid/index.html")){
		if(!is_file("/etc/artica-postfix/sarg.tgz")){
			shell_exec("cd /usr/share/artica-postfix/squid && tar -cjf /etc/artica-postfix/sarg.tgz ./*");
			$push_sarg=true;
		}else{
			if($ArrayFileCache["SQUID_SARG"]<>filemtime("/usr/share/artica-postfix/squid/index.html")){
				@unlink("/etc/artica-postfix/sarg.tgz");
				shell_exec("cd /usr/share/artica-postfix/squid && tar -cjf /etc/artica-postfix/sarg.tgz ./*");
				$push_sarg=true;
			}
		}
		if($push_sarg){
			$http->uploads["SQUID_SARG"]="/etc/artica-postfix/sarg.tgz";
		}
	}}
	
	
	
	
	$users=new usersMenus();
	$status=base64_encode(serialize($ini->_params));
	
	$pasmoinsaux=pasmoinsaux();
	
	if($users->VMWARE_HOST){$VMWARE_HOST=1;}else{$VMWARE_HOST=0;}
	shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.dmidecode.php");
	$dmidecode=base64_encode(@file_get_contents("/etc/artica-postfix/dmidecode.cache"));
	
	//SQUID
	if($users->SQUID_INSTALLED){
		$sock=new sockets();
		$SQUIDEnable=$sock->GET_INFO("SQUIDEnable");
		if($SQUIDEnable==null){$SQUIDEnable=1;}
		if($SQUIDEnable==1){$SQUID_CACHES=base64_encode(serialize($unix->squid_get_cache_infos()));}
		if(is_array($SQUID_CACHES)){
			$squid=new squidbee();
			$cacheconf=$squid->cache_list;
			$cacheconf[$squid->CACHE_PATH]["cache_type"]=$squid->CACHE_TYPE;
			$cacheconf[$squid->CACHE_PATH]["cache_size"]=$squid->CACHE_SIZE;
			$cacheconf[$squid->CACHE_PATH]["cache_dir_level1"]=16;
			$cacheconf[$squid->CACHE_PATH]["cache_dir_level2"]=256;
			events("Caches: ".count($cacheconf),__FILE__,__LINE__);
			$SQUID_CACHES_CONFIG=base64_encode(serialize($cacheconf));
		}
		
	}
	if($users->SAMBA_INSTALLED){_CheckSambaConfig();}
	if($users->ZARAFA_INSTALLED){
		if(is_file("/etc/artica-postfix/settings/Daemons/ZarafaLicenseInfos")){
			$ZARAFA_LICENSE=@file_get_contents("/etc/artica-postfix/settings/Daemons/ZarafaLicenseInfos");
		}else{
			$ZARAFA_LICENSE="Free edition";
		}
	}
	
	
	if(is_file("/etc/artica-postfix/zarafa-export.db")){$ZARAFA_DB=@file_get_contents("/etc/artica-postfix/zarafa-export.db");}
	$body=$http->send("$meta->ArticaMetaHostname/lic.status.server.php","post",array(
		"DATAS"=>$datasToSend,
		"STATUS"=>$status,
		"MEMORIES"=>$MEM_CACHE,
		"VERSION"=>$users->ARTICA_VERSION,
		"DISTRI"=>$users->LinuxDistriCode,
		"UPTIME"=>getUptime(),
		"DISTRINAME"=>$users->LinuxDistriFullName,
		"MAIN_PRODUCTS"=>base64_encode(serialize(array("ZARAFA"=>$users->ZARAFA_INSTALLED,
								"POSTFIX"=>$users->POSTFIX_INSTALLED,
								"SQUID"=>$users->SQUID_INSTALLED,
								"SAMBA"=>$users->SAMBA_INSTALLED,
								"CYRUS"=>$users->cyrus_imapd_installed,
								"OPENVPN"=>$users->OPENVPN_INSTALLED))),
		"PROCESSES"=>base64_encode($pasmoinsaux),
		"TOP_PROCESSES"=>top10cpumem(),
		"NETS"=>Networks(),
		"VMWARE_HOST"=>$VMWARE_HOST,
		"SETTINGS_INC"=>base64_encode(serialize(settings_inc())),
		"LOCAL_VERSIONS"=>LocalVersions(),
		"VBOXGUESTS"=>VirtualBoxList(),
		"APTCHECK"=>APTCHECK(),
		"DMIDECODE"=>$dmidecode,
		"SQUID_CACHES"=>$SQUID_CACHES,
		"SQUID_CACHES_CONFIG"=>$SQUID_CACHES_CONFIG,
		"OPENPORTS"=>OpenPorts($meta->serial,$meta->uuid),
		"OPENVPN_CLIENTS_STATUS"=>@file_get_contents("/usr/share/artica-postfix/ressources/logs/openvpn-clients.status"),
		"ZARAFA_DB"=>$ZARAFA_DB,
		"ZARAFA_LICENSE"=>$ZARAFA_LICENSE
	));
	
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}
	if(preg_match("#NOTIFY_DISCONNECT#is",$body)){
		events("NOTIFY_DISCONNECT detected -> unregister_server()",__FUNCTION__,__FILE__,__LINE__);
		unregister_server();return;
		}			
	if(preg_match("#NOTIFY_EXPORT_USERS#is",$body)){
		events("NOTIFY_EXPORT_USERS -> $nohup{$EXEC_NICE}exec.artica.meta.users.php --export-all",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all >/dev/null 2>&1 &");
		}		
	if(preg_match("#NOTIFY_EXPORT_DOMAINS#is",$body)){
		events("NOTIFY_EXPORT_DOMAINS -> $nohup{$EXEC_NICE}exec.artica.meta.users.php --export-all-domains",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-domains >/dev/null 2>&1 &");
		}
	
	if(preg_match("#NOTIFY_EXPORT_OU#is",$body)){
		events("NOTIFY_EXPORT_OU -> $nohup{$EXEC_NICE}exec.artica.meta.users.php --export-all-ou",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-ou >/dev/null 2>&1 &");
		}	
	
	if(preg_match("#NOTIFY_EXPORT_GROUPS#is",$body)){
		events("NOTIFY_EXPORT_GROUPS -> $nohup{$EXEC_NICE}exec.artica.meta.users.php --export-all-groups",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-groups >/dev/null 2>&1 &");
		}
	if(preg_match("#NOTIFY_EXPORT_SETTINGS#is",$body)){
		$cmd=$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-settings >/dev/null 2>&1 &";
		events("NOTIFY_EXPORT_SETTINGS -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	if(preg_match("#NOTIFY_EXPORT_COMPUTERS#is",$body)){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-computers >/dev/null 2>&1 &";
		events("NOTIFY_EXPORT_SETTINGS -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	if(preg_match("#NOTIFY_EXPORT_DNS_ENTRIES#is",$body)){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-dns >/dev/null 2>&1 &";
		events("NOTIFY_EXPORT_DNS_ENTRIES -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	if(preg_match("#NOTIFY_EXPORT_GROUPWARES#is",$body)){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-groupwares >/dev/null 2>&1 &";
		events("NOTIFY_EXPORT_GROUPWARES -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	if(preg_match("#NOTIFY_EXPORT_FETCHMAIL_RULES#is",$body)){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-fetchmail-rules >/dev/null 2>&1 &";
		events("NOTIFY_EXPORT_FETCHMAIL_RULES -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}



	if(preg_match("#<TASKS>(.+?)</TASKS>#is",$body,$re)){
		events("Save tasks to /etc/artica-postfix/artica-meta.tasks",__FUNCTION__,__FILE__,__LINE__);
		@file_put_contents("/etc/artica-postfix/artica-meta.tasks",$re[1]);
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.tasks.php >/dev/null 2>&1 &";
		events("TASKS ->$cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
	}else{
		events("No tasks ordered for me...",__FUNCTION__,__FILE__,__LINE__);
		@unlink("/etc/artica-postfix/artica-meta.tasks");
	}
	if(preg_match("#<HOST_CONF>(.+?)</HOST_CONF>#is",$body,$re)){
		ParseMyConf($re[1]);}else{
		events("No configuration for me...",__FUNCTION__,__FILE__,__LINE__);
	}
	
	shell_exec($nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --socks >/dev/null 2>&1 &");
	
	if(users_queue()){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --user-queue >/dev/null 2>&1 &";
		events("users settings queue is not empty -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	
	if(computer_queue()){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --computer-queue >/dev/null 2>&1 &";
		events("computer settings queue is not empty -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);	
		}
	
	if($users->OPENVPN_INSTALLED){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-logs >/dev/null 2>&1 &";
		events("OpenVpn is installed -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);	
		}
		
	$time_iptables=file_time_min("/etc/artica-postfix/artica.meta.iptables.time");
	if($time_iptables>180){
		$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --iptables >/dev/null 2>&1 &";
		events("iptables -> $cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		$cmd=$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-settings >/dev/null 2>&1 &";
		events("$cmd",__FUNCTION__,__FILE__,__LINE__);	
		@unlink("/etc/artica-postfix/artica.meta.iptables.time");
		@file_put_contents("/etc/artica-postfix/artica.meta.iptables.time","#");
		}
		
		
			
	
	$t2=time();
	$time_duration=distanceOfTimeInWords($t1,$t2);
	events("Send status to $meta->ArticaMetaHostname DONE ($time_duration)",__FUNCTION__,__FILE__,__LINE__);	

}

function ParseMyConf($encoded){
	$array=unserialize(base64_decode($encoded));
	if(!is_array($array)){
		events("ParseMyConf():It is not an array !!!",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$PoolTimeMin=$array["PoolTimeMin"];
	$sock=new sockets();
	events("PoolTimeMin:{$array["PoolTimeMin"]}",__FUNCTION__,__FILE__,__LINE__);
	events("PingEnable:{$array["PingEnable"]}",__FUNCTION__,__FILE__,__LINE__);
	$sock->SET_INFO("ArticaMetaPingEnable",$array["PingEnable"]);
	$sock->SET_INFO("ArticaMetaPoolTimeMin",$array["PoolTimeMin"]);
	
}

function _CheckSambaConfig(){
	if(!is_file("/etc/artica-postfix/settings/Daemons/SambaSMBConf")){
		@copy("/etc/samba/smb.conf","/etc/artica-postfix/settings/Daemons/SambaSMBConf");
		$EXEC_NICE=EXEC_NICE();
		shell_exec("$EXEC_NICE".LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-settings");	
	}
}


function DetectSubNics($array){
	$unix=new unix();
	$ifconfig=$unix->find_program("ifconfig");
	exec($ifconfig,$results);
	while (list ($index, $ligne) = each ($results) ){
		usleep(35000);
		if(preg_match("#^(.+?)\s+Link#",$ligne,$re)){
			$array[trim($re[1])]=trim($re[1]);
		}
		
	}
	return $array;
}

function Networks(){	
	$nics=new networking();
	$array=$nics->Local_interfaces();
	$array=DetectSubNics($array);
	
	while (list ($nic, $null) = each ($array) ){
		usleep(35000);
		$nics->ifconfig($nic);
		$res=$nics->GetNicInfos($nic);
		$array_returned[]=$res;
	}
	if($GLOBALS["VERBOSE"]){print_r($array_returned);}
	return base64_encode(serialize($array_returned));
}

function LocalVersions(){
	$filecache="/etc/artica-postfix/artica-meta-files.cache";
	$ArrayFileCache=unserialize(@file_get_contents($filecache));	
	$src_file="/usr/share/artica-postfix/ressources/logs/global.versions.conf";
	$timedest=filemtime($src_file);
	$minuteslivs=file_time_min($src_file);
	events("LOCAL_VERSIONS:{$ArrayFileCache["LOCAL_VERSIONS"]} ".basename($src_file).":{$timedest} TTL:{$minuteslivs}Mn",__FUNCTION__,__FILE__,__LINE__);
	if($ArrayFileCache["LOCAL_VERSIONS"]==$timedest){
		events("indentical time, aborting...",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$ArrayFileCache["LOCAL_VERSIONS"]=$timedest;
	
	$array=explode("\n",@file_get_contents($src_file));
	while (list ($nic, $null) = each ($array) ){
		usleep(35000);
		if(preg_match('#\[(.+?)\]\s+"(.*?)\"#',$null,$re)){
			$array_returned[$re[1]]=trim($re[2]);
		}
		
	}
	@file_put_contents($filecache,serialize($ArrayFileCache));
	events("Return ".count($array_returned)." items",__FUNCTION__,__FILE__,__LINE__);
	return base64_encode(serialize($array_returned));
	
	
}

function settings_inc(){
	$users=new usersMenus();
	foreach($users as $key => $value) {usleep(35000);$userArray[$key]=$value;}
	if($GLOBALS["VERBOSE"]){print_r($userArray);}
	return $userArray;
	
	
}


function pasmoinsaux(){
	$array=array();
	$unix=new unix();
	$cache_file="/etc/artica-postfix/ps.cache";
	if(file_time_min($cache_file)<10){return null;}
	$nice=EXEC_NICE();
	sleep(1);
	$ps=$unix->find_program("ps");
	
	exec("$nice$ps aux",$results);
	while (list ($index, $line) = each ($results) ){
	usleep(55000);
	if(!preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9\.]+)\s+.+?\s+.+?\s+([0-9\:]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
		if(preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9]+)\s+.+?\s+.+?\s+([a-zA-Z0-9]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
		$user=$re[1];$pid=$re[2];$pourcCPU=$re[3];$purcMEM=$re[4];$VSZ=$re[5];$RSS=$re[6];$START=$re[7];$TIME=$re[8];$cmd=$re[9];
		$array[]=array("PID"=>$pid,"CPU"=>$pourcCPU,"MEM"=>$purcMEM,"START"=>$START,"TIME"=>$TIME,"CMD"=>$cmd);				
		continue;		
			
		}
		continue;
	}	
	$user=$re[1];
	$pid=$re[2];
	$pourcCPU=$re[3];
	$purcMEM=$re[4];
	$VSZ=$re[5];
	$RSS=$re[6];
	$START=$re[7];
	$TIME=$re[8];
	$cmd=$re[9];
	
	$pourcCPU=str_replace("0.0","0",$pourcCPU);
	$purcMEM=str_replace("0.0","0",$purcMEM);
	
	$key="$pourcCPU$purcMEM";
	$key=str_replace(".",'',$key);
	$array[]=array("PID"=>$pid,"CPU"=>$pourcCPU,"MEM"=>$purcMEM,"START"=>$START,"TIME"=>$TIME,"CMD"=>$cmd);
	}

	@file_put_contents($cache_file,serialize($array));
	return  @file_get_contents($cache_file);
	
}

function users_queue(){
	$c=0;
	foreach (glob("/etc/artica-postfix/artica-meta-queue-socks/*.usr") as $filename) {$c++;}
	if($c>0){return true;}
	return false;
	}
function computer_queue(){
	$c=0;
	foreach (glob("/etc/artica-postfix/artica-meta-queue-socks/*.comp") as $filename) {$c++;}
	if($c>0){return true;}
	return false;
	}	
	
	
function ZYPPER_CHECK(){
	if(!is_file("/usr/bin/zypper")){
		if($GLOBALS["VERBOSE"]){echo "/usr/bin/zypper no such file \n";}
		return ;
	}
	$cache_file="/etc/artica-postfix/artica-meta-apt-check.cache";
	$timen=file_time_min($cache_file);
	if(!$GLOBALS["VERBOSE"]){if($timen<30){return @file_get_contents($cache_file);}}
	$EXEC_NICE=EXEC_NICE();
	$cmd="$EXEC_NICE/usr/bin/zypper list-updates 2>&1";
	exec($cmd,$results);
	$array["UPDATE_PACKAGES"]=0;
while (list ($index, $line) = each ($results) ){
		usleep(35000);
		if(preg_match("#v\s+\|(.+?)\s+\|\s+(.+?)\s+\|#",$line,$re)){
			$array["UPDATE_PACKAGES"]++;
			$pkg[]=$re[2];
		}
	}
	unset($results);
	$cmd="$EXEC_NICE/usr/bin/zypper list-patches 2>&1";
	exec($cmd,$results);
	$array["SECURITY_PACKAGES"]=0;
	while (list ($index, $line) = each ($results) ){
		usleep(35000);
		if(preg_match("#.+?\|\s+(.+?)\s+\|\s+[0-9]+\s+\|\s+[a-zA-Z]+#",$line,$re)){
			$array["SECURITY_PACKAGES"]++;
			$pkg[]=$re[1];
		}
	}	
	
	
	if(is_array($pkg)){$array["UPDATE_PACKAGES_LIST"]=@implode(";",$pkg);}	
	
	events("UPDATE_PACKAGES_LIST: {$array["UPDATE_PACKAGES_LIST"]}",__FUNCTION__,__FILE__,__LINE__);
	events("UPDATE_PACKAGES.....: {$array["UPDATE_PACKAGES"]}",__FUNCTION__,__FILE__,__LINE__);
	events("SECURITY_PACKAGES...: {$array["SECURITY_PACKAGES"]}",__FUNCTION__,__FILE__,__LINE__);
	
	@file_put_contents($cache_file,base64_encode(serialize($array)));
	return base64_encode(serialize($array));	
	
	
}	

function APTCHECK(){
	if(!is_file("/usr/lib/update-notifier/apt-check")){
		if($GLOBALS["VERBOSE"]){echo "/usr/lib/update-notifier/apt-check no such file \n";}
		events("/usr/lib/update-notifier/apt-check no such file ",__FUNCTION__,__FILE__,__LINE__);
		$datas=ZYPPER_CHECK();
		if(strlen($datas)>10){return $datas;}
		
		return ;
	}
	$cache_file="/etc/artica-postfix/artica-meta-apt-check.cache";
	
	$timen=file_time_min($cache_file);
	if(!$GLOBALS["VERBOSE"]){if($timen<30){return @file_get_contents($cache_file);}}
	$EXEC_NICE=EXEC_NICE();
	$cmd="$EXEC_NICE/usr/lib/update-notifier/apt-check 2>&1";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		usleep(35000);
		events("Found: $line",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#([0-9]+);([0-9]+)#",$line,$re)){
			$array["UPDATE_PACKAGES"]=$re[1];
			$array["SECURITY_PACKAGES"]=$re[2];
		}
	}
	
	if($array["UPDATE_PACKAGES"]>0){
		exec("$EXEC_NICE/usr/lib/update-notifier/apt-check -p 2>&1",$results2);
		while (list ($index, $line) = each ($results2) ){
			usleep(35000);
			if(trim($line)==null){continue;}
			if(preg_match("#Reading package lists#",$line)){continue;}
			if(preg_match("#Building dependency#",$line)){continue;}
			if(preg_match("#Reading state information#",$line)){continue;}
			$pkg[]=$line;
		}
	}
	if(is_array($pkg)){
		$array["UPDATE_PACKAGES_LIST"]=@implode(";",$pkg);
	}
	
	events("UPDATE_PACKAGES_LIST: {$array["UPDATE_PACKAGES_LIST"]}",__FUNCTION__,__FILE__,__LINE__);
	events("UPDATE_PACKAGES.....: {$array["UPDATE_PACKAGES"]}",__FUNCTION__,__FILE__,__LINE__);
	events("SECURITY_PACKAGES...: {$array["SECURITY_PACKAGES"]}",__FUNCTION__,__FILE__,__LINE__);
	
	@file_put_contents($cache_file,base64_encode(serialize($array)));
	return base64_encode(serialize($array));
	}


function events($text,$function,$file=null,$line=0){
		$file=basename(__FILE__);
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-meta-agent.log";
		$size=@filesize($logFile);
		if($size>100000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$file][$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}	

function getUptime () {
  $fd = fopen('/proc/uptime', 'r');
  $ar_buf = explode(' ', fgets($fd, 4096));
  fclose($fd);
   
  $sys_ticks = trim($ar_buf[0]); 
  return $sys_ticks;
}

function top10cpumem(){
	$unix=new unix();
	$ps=$unix->find_program("ps");
	$sort=$unix->find_program("sort");
	$pr=$unix->find_program("pr");
	$head=$unix->find_program("head");
	$tmpfile=$unix->FILE_TEMP();
	$date=date("Y-m-d H:i:s");
	$cmd="ps aux 2>&1| sort -nrk 3 2>&1| head -10 >$tmpfile 2>&1";
	$line=null;
	$index=0;
	shell_exec($cmd);
	$results=array();
	$results=explode("\n",@file_get_contents($tmpfile));
	
	while (list ($index, $line) = each ($results) ){
			usleep(55000);
			if(!preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9\.]+)\s+.+?\s+.+?\s+([0-9\:]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
				if(preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9]+)\s+.+?\s+.+?\s+([a-zA-Z0-9]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
				$user=$re[1];$pid=$re[2];$pourcCPU=$re[3];$purcMEM=$re[4];$VSZ=$re[5];$RSS=$re[6];$START=$re[7];$TIME=$re[8];$cmd=$re[9];
				$array_cpu[]=array("PID"=>$pid,"CPU"=>$pourcCPU,"MEM"=>$purcMEM,"START"=>$START,"TIME"=>$TIME,"CMD"=>$cmd);				
				continue;		
			}
			continue;
		}	
	$user=$re[1];$pid=$re[2];$pourcCPU=$re[3];$purcMEM=$re[4];$VSZ=$re[5];$RSS=$re[6];$START=$re[7];$TIME=$re[8];$cmd=$re[9];
	$pourcCPU=str_replace("0.0","0",$pourcCPU);
	$purcMEM=str_replace("0.0","0",$purcMEM);
	if($pourcCPU==0){continue;}
	$array_cpu[]=array("DATE"=>$date,"PID"=>$pid,"CPU"=>$pourcCPU,"MEM"=>$purcMEM,"START"=>$START,"TIME"=>$TIME,"CMD"=>$cmd);
	}

	

	$date=date("Y-m-d H:i:s");	
	$cmd="ps aux 2>&1|sort -nrk 4 2>&1|head -10 >$tmpfile 2>&1";
	shell_exec($cmd);
	$results=explode("\n",@file_get_contents($tmpfile));
	while (list ($index, $line) = each ($results) ){
			usleep(55000);
			if(!preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9\.]+)\s+.+?\s+.+?\s+([0-9\:]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
				if(preg_match("#(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9]+)\s+.+?\s+.+?\s+([a-zA-Z0-9]+)\s+([0-9\:]+)\s+(.+?)$#",$line,$re)){
				$user=$re[1];$pid=$re[2];$pourcCPU=$re[3];$purcMEM=$re[4];$VSZ=$re[5];$RSS=$re[6];$START=$re[7];$TIME=$re[8];$cmd=$re[9];
				$array_mem[]=array("DATE"=>$date, "PID"=>$pid,"CPU"=>$pourcCPU,"MEM"=>$purcMEM,"START"=>$START,"TIME"=>$TIME,"CMD"=>$cmd,"VSZ"=>$VSZ,"RSS"=>$RSS);				
				continue;		
				}
			continue;
			}	
		$user=$re[1];$pid=$re[2];$pourcCPU=$re[3];$purcMEM=$re[4];$VSZ=$re[5];$RSS=$re[6];$START=$re[7];$TIME=$re[8];$cmd=$re[9];
		$pourcCPU=str_replace("0.0","0",$pourcCPU);
		$purcMEM=str_replace("0.0","0",$purcMEM);
		if($purcMEM==0){continue;}
		$array_mem[]=array("PID"=>$pid,"CPU"=>$pourcCPU,"MEM"=>$purcMEM,"START"=>$START,"TIME"=>$TIME,"CMD"=>$cmd,"VSZ"=>$VSZ,"RSS"=>$RSS,);
		}
	
		
	$array["TOP_CPU"]=$array_cpu;
	$array["TOP_MEM"]=$array_mem;
	return base64_encode(serialize($array));

}

function VirtualBoxList(){
	if($GLOBALS["VERBOSE"]){echo "starting analyze VirtualBox machines...\n";}
	$users=new usersMenus();
	if(!$users->VIRTUALBOX_INSTALLED){
		events("Virtualbox not installed...",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["VERBOSE"]){echo "terminated... (".__LINE__.")\n";}
		return null;
	}
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-list-vms=yes")));
	if(!is_array($array)){
		events("Not an array",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["VERBOSE"]){echo "terminated... (".__LINE__.")\n";}
		return;
	}
	
	while (list ($vboxname, $vboxArray) = each ($array) ){
		if($GLOBALS["VERBOSE"]){echo "check $vboxname (".__LINE__.")\n";}
		$CPUSTATS=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showcpustats=yes&virtual-machine=".base64_encode($vboxname))));
		$array[$vboxname]["METRICS"]=$CPUSTATS;
	}
	
	
	reset($array);
	
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}
	$cmd=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-virtualbox-logs >/dev/null 2>&1 &";
	shell_exec($cmd);
	return base64_encode(serialize($array));
}


function OpenPorts($serial,$uuid){
	$unix=new unix();
	$lsof=$unix->find_program("lsof");
	if(strlen($lsof)<4){return null;}
	exec("$lsof -Pnl +M -i4 2>&1",$results);
	
	$intro="INSERT INTO `hosts_ports`(`serial`,`uuid`,`process`,`cmdline`,`pid`,`proto`,`infos`)";
	$suffix=array();
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#(.+?)\s+([0-9]+)\s+.+?\s+.+?\s+.+?\s+[0-9]+\s+(.+?)\s+(.+?)$#",$line,$re)){
			$ri=array();
			$cmd=$re[1];
			$pid=$re[2];
			$proto=$re[3];
			$infos=addslashes($re[4]);
			$cmdline=exec("/bin/cat -v /proc/$pid/cmdline",$ri);
			$cmdline=trim(@implode("",$ri));
			$cmdline=addslashes(str_replace("^@"," ",$cmdline));
			$suffix[]="('$serial','$uuid','$cmd','$cmdline','$pid','$proto','$infos')";
			//if($GLOBALS["VERBOSE"]){echo "$cmdline ($cmd) [$pid] $proto:$infos\n";}
		}
		
	}

	if(is_array($suffix)){
		return base64_encode("$intro\nVALUES ".@implode(",",$suffix));
	}
	
	
}


		
function ConnextionsLogs(){
	$cmd="perl -we '@type=(\"Empty\",\"Run Lvl\",\"Boot\",\"New Time\",\"Old Time\",\"Init\",\"Login\",\"Normal\",\"Term\",\"Account\");\$recs = \"\"; while (<>) {\$recs .= \$_};foreach (split(/(.{384})/s,\$recs)) {next if length(\$_) == 0;my (\$type,\$pid,\$line,\$inittab,\$user,\$host,\$t1,\$t2,\$t3,\$t4,\$t5) = \$_ =~/(.{4})(.{4})(.{32})(.{4})(.{32})(.{256})(.{4})(.{4})(.{4})(.{4})(.{4})/s;if (defined \$line && \$line =~ /\w/) {\$line =~ s/\x00+//g;\$host =~ s/\x00+//g;\$user =~ s/\x00+//g;printf(\"%s %-8s %-12s 10s %-45s \n\",scalar(gmtime(unpack(\"I4\",\$t3))),\$type[unpack(\"I4\",\$type)],\$user,\$line,\$host)}}print\"\n\"' < /var/log/wtmp";
	$cmd="ps -eorss,args | sort -nr | pr -TW\$COLUMNS | head"; //MEMORY
	$cmd="ps -eo pcpu,user,pid,cmd | sort -r | head -10"; //CPU
	$cmd="sync; echo 3 | sudo tee /proc/sys/vm/drop_caches";
	}