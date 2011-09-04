<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--includes#",implode(" ",$argv))){$GLOBALS["DEBUG_INCLUDES"]=true;}
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.templates.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.ini.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.squid.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.squid.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::framework/class.unix.inc\n";}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::frame.class.inc\n";}
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');



if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('display_errors', 1);	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if($GLOBALS["VERBOSE"]){echo " commands= ".implode(" ",$argv)."\n";}

	$unix=new unix();
	$squidbin=$unix->find_program("squid3");
	if(!is_file($squidbin)){$squidbin=$unix->find_program("squid");}
	$GLOBALS["SQUIDBIN"]=$squidbin;
	if($GLOBALS["VERBOSE"]){echo "squid binary=$squidbin\n";}
	
	
if($argv[1]=="--reload-squid"){if($GLOBALS["VERBOSE"]){echo "reload in debug mode\n";} Reload_Squid();die();}
if($argv[1]=="--templates"){SQUID_TEMPLATES();die();}
if($argv[1]=="--retrans"){retrans();die();}
if($argv[1]=="--certificate"){certificate_generate();die();}
if($argv[1]=="--caches"){BuildCaches();die();}
if($argv[1]=="--caches-reconstruct"){ReconstructCaches();die();}
if($argv[1]=="--compilation-params"){compilation_params();die();}



if($argv[1]=="--wrapzap"){wrapzap();die();}
if($argv[1]=="--wrapzap-compile"){wrapzap_compile();die();}
if($argv[1]=="--change-value"){change_value($argv[2],$argv[3]);die();}

//request_header_max_size

function change_value($key,$val){
	$squid=new squidbee();
	$squid->global_conf_array[$key]=$val;
	$squid->SaveToLdap();
	echo "Starting......: Squid change $key to $val (squid will be restarted)\n";
	
}


if($argv[1]=="--reconfigure"){
		$EXEC_PID_FILE="/etc/artica-postfix/".basename(__FILE__).".reconfigure.pid";
		$unix=new unix();
		if($unix->process_exists(@file_get_contents($EXEC_PID_FILE))){
			$timefile=$unix->file_time_min($EXEC_PID_FILE);
			if($timefile<15){
				print "Starting......: Checking squid this program is already executed pid ". @file_get_contents($EXEC_PID_FILE)." {$timefile}Mn...\n";
				die();
			}
		}	
	@file_put_contents($EXEC_PID_FILE, posix_getpid());
	ApplyConfig();
	certificate_generate();
	Reload_Squid();
	CheckFilesAndSecurity();
	exec("/usr/share/artica-postfix/bin/artica-install --squid-reload");
	writelogs("reload Dansguardian (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Reloading Dansguardian (if enabled)\n";
	exec("/usr/share/artica-postfix/bin/artica-install --reload-dansguardian");
	writelogs("reload c-icap (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Reloading c-icap (if enabled)\n";
	exec("/usr/share/artica-postfix/bin/artica-install --c-icap-reload");
	writelogs("reload Kav4Proxy (if enabled)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: Reloading Kaspersky (if enabled)\n";
	exec("/usr/share/artica-postfix/bin/artica-install --reload-kav4proxy");	
	die();
}


if($argv[1]=="--build"){
		$EXEC_PID_FILE="/etc/artica-postfix/".basename(__FILE__).".build.pid";
		$unix=new unix();
		if($unix->process_exists(@file_get_contents($EXEC_PID_FILE))){
			print "Starting......: Checking squid Already executed pid ". @file_get_contents($EXEC_PID_FILE)."...\n";
			die();
		}
	$childpid=posix_getpid();
	$sock=new sockets();
	@file_put_contents($EXEC_PID_FILE,$childpid);
	if(is_file("/etc/squid3/mime.conf")){shell_exec("/bin/chown squid:squid /etc/squid3/mime.conf");}
	$EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
	if(!is_numeric("$EnableKerbAuth")){$EnableKerbAuth=0;}	
	echo "Starting......: Checking squid kerberos authentification is set to $EnableKerbAuth\n";
	
	echo "Starting......: Checking squid certificate\n";
	certificate_generate();
	echo "Starting......: Instanciate squid library..\n";
	$squid=new squidbee();
	$squidbin=$unix->find_program("squid3");
	echo "Starting......: checking squid binaries..\n";
	if(!is_file($squidbin)){$squidbin=$unix->find_program("squid");}
	echo "Starting......: Binary: $squidbin\n";
	echo "Starting......: Checking blocked sites\n";
	$squid->BuildBlockedSites();
	echo "Starting......: Checking FTP ACLs\n";
	acl_clients_ftp();
	echo "Starting......: Checking Whitelisted browsers\n";
	acl_whitelisted_browsers();
	acl_allowed_browsers();
	echo "Starting......: Checking wrapzap\n";
	wrapzap();
	$squid_user=SquidUser();
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	
	echo "Starting......: Building master configuration\n";
	$squid->ASROOT=true;		
	$conf=$squid->BuildSquidConf();
	@file_put_contents($SQUID_CONFIG_PATH,$conf);
	echo "Starting......: Check files and security\n";
	CheckFilesAndSecurity();
	echo "Starting......: Check SquidClamAV\n";
	squidclamav();
	Reload_Squid();
		
	echo "Starting......: scheduling Building templates\n";
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ". __FILE__." --templates");
	echo "Starting......: Done...\n";
	die();
}

function CheckFilesAndSecurity(){
	$squid_user=SquidUser();
	$unix=new unix();	
	shell_exec("/bin/chown -R $squid_user /etc/squid3/* >/dev/null 2>&1");
	if(!is_file("/var/log/squid/squidGuard.log")){@file_put_contents("/var/log/squid/squidGuard.log","#");}
	@mkdir("/var/log/squid/squid",755,true);
	shell_exec("/bin/chown -R $squid_user /var/log/squid/* >/dev/null 2>&1");	
	if(!is_file("/etc/squid3/squid-block.acl")){@file_put_contents("/etc/squid3/squid-block.acl","");}
	if(!is_file("/etc/squid3/clients_ftp.acl")){@file_put_contents("/etc/squid3/clients_ftp.acl","");}
	if(!is_file("/etc/squid3/allowed-user-agents.acl")){@file_put_contents("/etc/squid3/allowed-user-agents.acl","");}	
	if(is_file("/var/lib/samba/winbindd_privileged")){
		$setfacl=$unix->find_program("setfacl");
		if(is_file($setfacl)){shell_exec("$setfacl -m u:squid:rx /var/lib/samba/winbindd_privileged >/dev/null 2>&1");}
	}
	
	
	
	
}
function Reload_Squid(){
	echo "Starting......: Reloading Squid\n";
	exec("{$GLOBALS["SQUIDBIN"]} -k reconfigure 2>&1",$results);
	while (list ($num, $val) = each ($results)){
		
		if(preg_match("#ERROR: No running copy#",$val)){
			echo "Starting......: stopping squid instances in memory\n";
			KillSquid();
			shell_exec("/etc/init.d/artica-postfix start squid-cache --without-compile");
			return;
		}
		
		echo "Starting......: $val\n";
	}	
}

function KillSquid(){
	$unix=new unix();
	$pidof=$unix->find_program("pidof");
	$kill=$unix->find_program("kill");
	if(strlen($pidof)<4){return;}
	exec("$pidof {$GLOBALS["SQUIDBIN"]}",$results);
	$f=explode(" ",@implode("",$results));
	while (list ($num, $val) = each ($f)){
		$val=trim($val);
		if(!is_numeric($val)){continue;}
		echo "Starting......: stopping pid $val\n";
		shell_exec("$kill -9 $val");
		usleep(10000);
	}
	
	
}


function squidclamav(){
	$squid=new squidbee();
	$sock=new sockets();
	$unix=new unix();
	$users=new usersMenus();
	$SquidGuardIPWeb=$sock->GET_INFO("SquidGuardIPWeb");
	if($SquidGuardIPWeb==null){$SquidGuardIPWeb="http://$users->hostname:9020/exec.squidguard.php";}
	
	
	$conf[]="squid_ip 127.0.0.1";
	$conf[]="squid_port $squid->listen_port";
	$conf[]="logfile /var/log/squid/squidclamav.log";
	$conf[]="debug 0";
	$conf[]="stat 0";
	$conf[]="clamd_local ".$unix->LOCATE_CLAMDSOCKET();
	$conf[]="#clamd_ip 192.168.1.5";
	$conf[]="#clamd_port 3310";
	$conf[]="maxsize 5000000";
	$conf[]="redirect $SquidGuardIPWeb";
	if($squid->enable_squidguard==1){
		$conf[]="squidguard $users->SQUIDGUARD_BIN_PATH";
	}else{
		if($squid->enable_UfdbGuard==1){
			$conf[]="squidguard $users->ufdbgclient_path";
		}
	}
	$conf[]="maxredir 30";
	$conf[]="timeout 60";
	$conf[]="useragent Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
	$conf[]="trust_cache 1";
	$conf[]="";
	$conf[]="# Do not scan standard HTTP images";
	$conf[]="abort ^.*\.(ico|gif|png|jpg)$";
	$conf[]="abortcontent ^image\/.*$";
	$conf[]="# Do not scan text and javascript files";
	$conf[]="abort ^.*\.(css|xml|xsl|js|html|jsp)$";
	$conf[]="abortcontent ^text\/.*$";
	$conf[]="abortcontent ^application\/x-javascript$";
	$conf[]="# Do not scan streaming videos";
	$conf[]="abortcontent ^video\/mp4";
	$conf[]="abortcontent ^video\/x-flv$";
	$conf[]="# Do not scan pdf and flash";
	$conf[]="#abort ^.*\.(pdf|swf)$";
	$conf[]="";
	$conf[]="# Do not scan sequence of framed Microsoft Media Server (MMS)";
	$conf[]="abortcontent ^.*application\/x-mms-framed.*$";
	$conf[]="";
	$conf[]="# White list some sites";
	$conf[]="whitelist .*\.clamav.net";	
	@file_put_contents("/etc/squidclamav.conf",@implode("\n",$conf));
	echo "Starting......: Squid building squidclamav.conf configuration done\n";
}

function GetLocalCaches(){
	$unix=new unix();	
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	
	$f=explode("\n",@file_get_contents($SQUID_CONFIG_PATH));
	while (list ($num, $line) = each ($f)){
		if(preg_match("#cache_dir\s+([a-z]+)\s+(.+?)\s+[0-9]+#",$line,$re)){
			writelogs("Directory: {$re[2]} type={$re[1]}",__FUNCTION__,__FILE__,__LINE__);
			$array[trim($re[2])]=$re[1];
		}
		
	}

	return $array;
	
}

function ReconstructCaches(){
	$squid=new squidbee();
	$unix=new unix();	
	$main_cache=$squid->CACHE_PATH;
	echo "Starting......:  reconstruct caches\n";
	$squid->cache_list[$squid->CACHE_PATH]=$squid->CACHE_PATH;
	while (list ($num, $val) = each ($squid->cache_list)){
		if(is_dir($num)){
			echo "Starting......:  removing directory $num\n";
			shell_exec("/bin/rm -rf $num");
		}
	}
	echo "Starting......:  Building caches\n";
	BuildCaches();
	
}


function BuildCaches(){
	$squid=new squidbee();
	$unix=new unix();	
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	$conf=$squid->BuildSquidConf();
	@file_put_contents($SQUID_CONFIG_PATH,$conf);
	$unix=new unix();
	$su_bin=$unix->find_program("su");
	
	writelogs("Reconfigure squid",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("{$GLOBALS["SQUIDBIN"]} -k reconfigure");
	writelogs("Stopping squid",__FUNCTION__,__FILE__,__LINE__);
	
	
	$squid_user=SquidUser();
	writelogs("Using squid user: \"$squid_user\"",__FUNCTION__,__FILE__,__LINE__);
	writelogs("chown cache directories...",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/chown -R $squid_user /etc/squid3/* >/dev/null 2>&1");
	
	$main_cache=$squid->CACHE_PATH;
	writelogs("Main cache: \"$main_cache\"",__FUNCTION__,__FILE__,__LINE__);
	$squid->cache_list[$squid->CACHE_PATH]=$squid->CACHE_PATH;
	writelogs(count($squid->cache_list)." caches to check",__FUNCTION__,__FILE__,__LINE__);
	
	if(count($squid->cache_list)==0){
		writelogs("No caches has been set, verify squid configuration file...",__FUNCTION__,__FILE__,__LINE__);
		$squid->cache_list=GetLocalCaches();
	}
	
	
	writelogs(count($squid->cache_list)." caches to check",__FUNCTION__,__FILE__,__LINE__);
	
	reset($squid->cache_list);
	while (list ($num, $val) = each ($squid->cache_list)){
		writelogs("Directory \"$num\"",__FUNCTION__,__FILE__,__LINE__);
		if(trim($num)==null){continue;}
		if(!is_dir($num)){@mkdir($num,755,true);}
		writelogs("chown cache directory \"$num\"...",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/bin/chown -R $squid_user $num");
		shell_exec("/bin/chmod -R 0755 $num");
	}
	
	if(preg_match("#(.+?):#",$squid_user,$re)){$squid_uid=$re[1];}else{$squid_uid="squid";}
	writelogs("stopping squid...",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/etc/init.d/artica-postfix stop squid-cache");
	writelogs("Building caches with user: \"$squid_uid\"",__FUNCTION__,__FILE__,__LINE__);
	writelogs("$su_bin $squid_uid -c \"{$GLOBALS["SQUIDBIN"]} -z\" 2>&1",__FUNCTION__,__FILE__,__LINE__);
	exec("$su_bin $squid_uid -c \"{$GLOBALS["SQUIDBIN"]} -z\" 2>&1",$results);	
	
	while (list ($agent, $val) = each ($results) ){
			writelogs("$val",__FUNCTION__,__FILE__,__LINE__);
	}
	
	
	writelogs("Send Notifications",__FUNCTION__,__FILE__,__LINE__);
	send_email_events("Squid Cache: reconfigure caches","Here it is the results\n",@implode("\n",$results),"proxy");
	writelogs("Starting squid",__FUNCTION__,__FILE__,__LINE__);
	
	unset($results);
	exec("/etc/init.d/artica-postfix start squid-cache 2>&1",$results);
	
	while (list ($agent, $val) = each ($results) ){
			writelogs("$val",__FUNCTION__,__FILE__,__LINE__);
	}	
	
	writelogs("Flush tasks",__FUNCTION__,__FILE__,__LINE__);
	if(!is_file("/etc/artica-postfix/settings/Daemons/SquidCacheTask")){
		writelogs("/etc/artica-postfix/settings/Daemons/SquidCacheTask No such file",__FUNCTION__,__FILE__,__LINE__);
	}
	@unlink("/etc/artica-postfix/settings/Daemons/SquidCacheTask");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.php --status --force");
	
	
	
}

function ApplyConfig(){
	$unix=new unix();
	
	$squid=new squidbee();
	writelogs("->BuildBlockedSites",__FUNCTION__,__FILE__,__LINE__);
	$squid->BuildBlockedSites();
	acl_clients_ftp();
	acl_whitelisted_browsers();
	acl_allowed_browsers();
	
	$SQUID_CONFIG_PATH=$unix->SQUID_CONFIG_PATH();
	if(!is_file($SQUID_CONFIG_PATH)){
		writelogs("Unable to stat squid configuration file \"$SQUID_CONFIG_PATH\"",__FUNCTION__,__FILE__,__LINE__);
		return;
	}

	echo "Starting......: Squid building main configuration done\n";
	$squid=new squidbee();
	$conf=$squid->BuildSquidConf();
	@file_put_contents("/etc/artica-postfix/settings/Daemons/GlobalSquidConf",$conf);
	@file_put_contents($SQUID_CONFIG_PATH,$conf);
	
	if($squid->EnableKerbAuth){
		shell_exec($unix->LOCATE_PHP5_BIN(). " ". dirname(__FILE__)."/exec.kerbauth.php --build");
		
	}
			
	
	squidclamav();
	wrapzap();
	certificate_generate();
	SQUID_TEMPLATES();
	CheckFilesAndSecurity();
	
}

function acl_clients_ftp(){
	$q=new mysql();
	$sql="SELECT * FROM squid_white WHERE task_type='FTP_RESTR' ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){return;}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(!preg_match("#FTP_RESTR:(.+)#",$ligne["uri"],$re)){continue;}	
		$f[]=$re[1];
	}
	@file_put_contents("/etc/squid3/clients_ftp.acl",@implode("\n",$f));
	
}

function acl_allowed_browsers(){
	$sql="SELECT uri FROM squid_white WHERE task_type='USER_AGENT_BAN_WHITE' ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$string=trim($ligne["uri"]);
		if($string==null){continue;}
		$string=str_replace(".","\.",$string);
		$string=str_replace("(","\(",$string);
		$string=str_replace(")","\)",$string);
		$string=str_replace("/","\/",$string);
		$f[]=$string;
	}	
	@file_put_contents("/etc/squid3/allowed-user-agents.acl",@implode("\n",$f));
}

function acl_whitelisted_browsers(){
	$sql="SELECT uri FROM squid_white WHERE task_type='AUTH_WL_USERAGENTS'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$arrayUserAgents[$ligne["uri"]]=1;
	}
	if(!is_array($arrayUserAgents)){
		echo "Starting......: Whitelisted User-Agents: 0\n";
		@file_put_contents("/etc/squid3/white-listed-user-agents.acl","");
		return;
		
	}
		

		while (list ($agent, $val) = each ($arrayUserAgents) ){
		    		$sql="SELECT unique_key,`string` FROM `UserAgents` WHERE browser='$agent' ORDER BY string";
		    		$q=new mysql();
					$results=$q->QUERY_SQL($sql,"artica_backup");
					while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
						$string=trim($ligne["string"]);
						if($string==null){continue;}
						$string=str_replace(".","\.",$string);
						$string=str_replace("(","\(",$string);
						$string=str_replace(")","\)",$string);
						$string=str_replace("/","\/",$string);
						$f[]=$string;
					}
				}
	echo "Starting......: Whitelisted User-Agents: ". count($arrayUserAgents)." (". count($f)." patterns)\n";		
	@file_put_contents("/etc/squid3/white-listed-user-agents.acl",@implode("\n",$f));		
		
	
}


function retrans(){
	$unix=new unix();
	$array=$unix->getDirectories("/tmp");
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#(.+?)\/temporaryFolder\/bases\/av#",$ligne,$re)){
			$folder=$re[1];
		}
	}
	if(is_dir($folder)){
		$cmd=$unix->find_program("du")." -h -s $folder 2>&1";
		exec($cmd,$results);
		$text=trim(implode(" ",$results));
		if(preg_match("#^([0-9\.\,A-Z]+)#",$text,$re)){
			$dbsize=$re[1];
		}
	}else{
		$dbsize="0M";
	}
	
	echo $dbsize;
}


function certificate_conf(){
	include_once('ressources/class.ssl.certificate.inc');
	$ssl=new ssl_certificate();
	$array=$ssl->array_ssl;
	$users=new usersMenus();
	$sock=new sockets();	
	$cc=$array["artica"]["country"]."_".$array["default_ca"]["countryName_value"];
	

	
	
		$country_code="US";
		$contryname="Delaware";
		$locality="Wilmington";
		$organizationalUnitName="Artica Web Proxy Unit";
		$organizationName="Artica";
		$emailAddress="root@$users->hostname";
		$commonName=$users->hostname;
		
		
		
		if(preg_match("#(.+?)_(.+?)$#",$cc,$re)){
			$contryname=$re[1];
			$country_code=$re[2];
		}
		if($array["server_policy"]["localityName"]<>null){$locality=$array["server_policy"]["localityName"];}
		if($array["server_policy"]["organizationalUnitName"]<>null){$organizationalUnitName=$array["server_policy"]["organizationalUnitName"];}
		if($array["server_policy"]["emailAddress"]<>null){$emailAddress=$array["server_policy"]["emailAddress"];}
		if($array["server_policy"]["organizationName"]<>null){$organizationName=$array["server_policy"]["organizationName"];}
		if($array["server_policy"]["commonName"]<>null){$commonName=$array["server_policy"]["commonName"];}
	
		@mkdir("/etc/squid3/ssl/new",0666,true);
		
		$conf[]="[ca]";
		$conf[]="default_ca=default_db";
		$conf[]="unique_subject=no";
		$conf[]="";
		$conf[]="[default_db]";
		$conf[]="dir=.";
		$conf[]="certs=.";
		$conf[]="new_certs_dir=/etc/squid3/ssl/new";
		$conf[]="database= /etc/squid3/ssl/ca.index";
		$conf[]="serial = /etc/squid3/ssl/ca.serial";
		$conf[]="RANDFILE=.rnd";
		$conf[]="certificate=/etc/squid3/ssl/key.pem";
		$conf[]="private_key=/etc/squid3/ssl/ca.key";
		$conf[]="default_days= 730";
		$conf[]="default_crl_days=30";
		$conf[]="default_md=md5";
		$conf[]="preserve=no";
		$conf[]="name_opt=ca_default";
		$conf[]="cert_opt=ca_default";
		$conf[]="unique_subject=no";
		$conf[]="policy=policy_match";
		$conf[]="";
		$conf[]="[server_policy]";
		$conf[]="countryName=supplied";
		$conf[]="stateOrProvinceName=supplied";
		$conf[]="localityName=supplied";
		$conf[]="organizationName=supplied";
		$conf[]="organizationalUnitName=supplied";
		$conf[]="commonName=supplied";
		$conf[]="emailAddress=supplied";
		$conf[]="";
		$conf[]="[server_cert]";
		$conf[]="subjectKeyIdentifier=hash";
		$conf[]="authorityKeyIdentifier=keyid:always";
		$conf[]="extendedKeyUsage=serverAuth,clientAuth,msSGC,nsSGC";
		$conf[]="basicConstraints= critical,CA:false";
		$conf[]="";
		$conf[]="[user_policy]";
		$conf[]="commonName=supplied";
		$conf[]="emailAddress=supplied";
		$conf[]="";
		$conf[]="[user_cert]";
		$conf[]="subjectAltName=email:copy";
		$conf[]="basicConstraints= critical,CA:false";
		$conf[]="authorityKeyIdentifier=keyid:always";
		$conf[]="extendedKeyUsage=clientAuth,emailProtection";
		$conf[]="";
		$conf[]="[req]";
		$conf[]="default_bits=1024";
		$conf[]="default_keyfile=ca.key";
		$conf[]="distinguished_name=default_ca";
		$conf[]="x509_extensions=extensions";
		$conf[]="string_mask=nombstr";
		$conf[]="req_extensions=req_extensions";
		$conf[]="input_password=secret";
		$conf[]="output_password=secret";
		$conf[]="";
		$conf[]="[default_ca]";
		$conf[]="countryName=Country Code";
		$conf[]="countryName_value=$country_code";
		$conf[]="countryName_min=2";
		$conf[]="countryName_max=2";
		$conf[]="stateOrProvinceName=State Name";
		$conf[]="stateOrProvinceName_value=$contryname";
		$conf[]="localityName=Locality Name";
		$conf[]="localityName_value=$locality";
		$conf[]="organizationName=Organization Name";
		$conf[]="organizationName_value=$organizationName";
		$conf[]="organizationalUnitName=Organizational Unit Name";
		$conf[]="organizationalUnitName_value=$organizationalUnitName";
		$conf[]="commonName=Common Name";
		$conf[]="commonName_value=$commonName";
		$conf[]="commonName_max=64";
		$conf[]="emailAddress=Email Address";
		$conf[]="emailAddress_value=$emailAddress";
		$conf[]="emailAddress_max=40";
		$conf[]="unique_subject=no";
		$conf[]="";
		$conf[]="[extensions]";
		$conf[]="subjectKeyIdentifier=hash";
		$conf[]="authorityKeyIdentifier=keyid:always";
		$conf[]="basicConstraints=critical,CA:false";
		$conf[]="";
		$conf[]="[req_extensions]";
		$conf[]="nsCertType=objsign,email,server";
		$conf[]="";
		$conf[]="[CA_default]";
		$conf[]="policy=policy_match";
		$conf[]="";
		$conf[]="[policy_match]";
		$conf[]="countryName=match";
		$conf[]="stateOrProvinceName=match";
		$conf[]="organizationName=match";
		$conf[]="organizationalUnitName=optional";
		$conf[]="commonName=match";
		$conf[]="emailAddress=optional";
		$conf[]="";
		$conf[]="[policy_anything]";
		$conf[]="countryName=optional";
		$conf[]="stateOrProvinceName=optional";
		$conf[]="localityName=optional";
		$conf[]="organizationName=optional";
		$conf[]="organizationalUnitName=optional";
		$conf[]="commonName=optional";
		$conf[]="emailAddress=optional";
		$conf[]="";
		$conf[]="[v3_ca]";
		$conf[]="subjectKeyIdentifier=hash";
		$conf[]="authorityKeyIdentifier=keyid:always,issuer:always";
		$conf[]="basicConstraints=critical,CA:false";
		@mkdir("/etc/squid3/ssl",0666,true);
		file_put_contents("/etc/squid3/ssl/openssl.conf",@implode("\n",$conf));		
	}

function certificate_generate(){
		$ssl_path="/etc/squid3/ssl";
		
		if(is_certificate()){
			echo "Starting......: Squid SSL certificate OK\n";
			return;
		}
		
		
		@unlink("$ssl_path/privkey.cp.pem");
		@unlink("$ssl_path/cacert.pem");
		@unlink("$ssl_path/privkey.pem");
		
		
		 echo "Starting......: Squid building SSL certificate\n";
		 certificate_conf();
		 $ldap=new clladp();
		 $sock=new sockets();
		 $unix=new unix();
		$CertificateMaxDays=$sock->GET_INFO('CertificateMaxDays');
		if($CertificateMaxDays==null){$CertificateMaxDays='730';}
		 echo "Starting......: Squid Max Days are $CertificateMaxDays\n";		 
		 $password=$unix->shellEscapeChars($ldap->ldap_password);
		 
		 $openssl=$unix->find_program("openssl");
		 $config="/etc/squid3/ssl/openssl.conf";
		 
		 
		 system("$openssl genrsa -des3 -passout pass:$password -out $ssl_path/privkey.pem 2048 1024");
		 system("$openssl req -new -x509 -nodes -passin pass:$password -key $ssl_path/privkey.pem -batch -config $config -out $ssl_path/cacert.pem -days $CertificateMaxDays");
		 system("/bin/cp $ssl_path/privkey.pem $ssl_path/privkey.cp.pem");
		 system("$openssl rsa -passin pass:$password -in $ssl_path/privkey.cp.pem -out $ssl_path/privkey.pem"); 
		 
	     
	}
	
function is_certificate(){
	$ssl_path="/etc/squid3/ssl";;
	if(!is_file("$ssl_path/cacert.pem")){return false;}
	if(!is_file("$ssl_path/privkey.pem")){return false;}
	if(!is_file("$ssl_path/privkey.cp.pem")){return false;}
	return true;
	
}

function wrapzap_compile(){
	$sql="SELECT * FROM squid_adzapper WHERE enabled=1";
	$q=new mysql();
	$tpl=new templates();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);return;}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[]="{$ligne["uri_type"]} {$ligne["uri"]}";
	}
	
	echo "Starting......: adZapper ". count($f)." rows\n"; 
	@file_put_contents("/etc/squid3/zapper.post-database.txt",@implode("\n",$f));
	$squiduser=SquidUser();
	shell_exec("/bin/chown $squiduser /etc/squid3/zapper.pre-database.txt");
	shell_exec("/bin/chown $squiduser /etc/squid3/zapper.post-database.txt");

	if($GLOBALS["RELOAD"]){
		$unix=new unix();
		shell_exec("{$GLOBALS["SQUIDBIN"]} -k reconfigure");
	}
}


function wrapzap(){
	$usrs=new usersMenus();
	$sock=new sockets();
	$SquidGuardIPWeb=$sock->GET_INFO("SquidGuardIPWeb");
	if($SquidGuardIPWeb==null){$SquidGuardIPWeb="http://$usrs->hostname:9020/zaps";}
	$SquidGuardIPWeb=str_replace('.(none)',"",$SquidGuardIPWeb);
	
	if(preg_match("#http:\/\/(.+?)\/#",$SquidGuardIPWeb,$re)){
		$SquidGuardIPWeb="http://{$re[1]}/zaps";
	}
	
	if(!is_file("/etc/squid3/zapper.pre-database.txt")){@file_put_contents("/etc/squid3/zapper.pre-database.txt","#");}
	if(!is_file("/etc/squid3/zapper.post-database.txt")){@file_put_contents("/etc/squid3/zapper.post-database.txt","#");}
	
	wrapzap_compile();
	
	
	echo "Starting......: adZapper redirector to \"$SquidGuardIPWeb\"\n"; 
	
$f[]="#!/bin/sh";
$f[]="#";
$f[]="# Wrapper to set environment variables then exec the real zapper.";
$f[]="# The reasons for this are twofold:";
$f[]="#	- for some reason squid doesn't preserve the original environment";
$f[]="#	  when you do a restart (or SIGHUP)";
$f[]="#	- to avoid having to hack the squid startup script (if you have";
$f[]="#	  a presupplied one, such as ships with some linux distributions)";
$f[]="#";
$f[]="# Install in the same directory you put the zapper (just for convenience) and";
$f[]="# hack the pathnames below to suit.";
$f[]="# Note that you can skip this script and run the zapper with no environment";
$f[]="# settings at all and it will work fine; the variables are all set here merely";
$f[]="# for completeness so that customisation is easy for you.";
$f[]="#	- Cameron Simpson <cs@zip.com.au> 21apr2000";
$f[]="#";
$f[]="";
$f[]="# modify this to match your install";
$f[]="zapper=/usr/bin/squid_redirect";
$f[]="";
$f[]="ZAP_MODE=				# or \"CLEAR\"";
$f[]="ZAP_BASE=$SquidGuardIPWeb	# a local web server will be better";
$f[]="ZAP_BASE_SSL=https://adzapper.sourceforge.net/zaps # this can probably be ignored";
$f[]="";
$f[]="ZAP_PREMATCH=/etc/squid3/zapper.pre-database.txt";
$f[]="ZAP_POSTMATCH=/etc/squid3/zapper.post-database.txt";
$f[]="ZAP_MATCH=				# pathname of extra pattern file";
$f[]="					# for patterns to use instead of the";
$f[]="					# inbuilt pattern list";
$f[]="ZAP_NO_CHANGE=				# set to \"NULL\" is your proxy is Apache2 instead of Squid";
$f[]="";
$f[]="STUBURL_AD=\$ZAP_BASE/ad.gif";
$f[]="STUBURL_ADSSL=\$ZAP_BASE_SSL/ad.gif";
$f[]="STUBURL_ADBG=\$ZAP_BASE/adbg.gif";
$f[]="STUBURL_ADJS=\$ZAP_BASE/no-op.js";
$f[]="STUBURL_ADJSTEXT=";
$f[]="STUBURL_ADHTML=\$ZAP_BASE/no-op.html";
$f[]="STUBURL_ADHTMLTEXT=";
$f[]="STUBURL_ADMP3=\$ZAP_BASE/ad.mp3";
$f[]="STUBURL_ADPOPUP=\$ZAP_BASE/closepopup.html";
$f[]="STUBURL_ADSWF=\$ZAP_BASE/ad.swf";
$f[]="STUBURL_COUNTER=\$ZAP_BASE/counter.gif";
$f[]="STUBURL_COUNTERJS=\$ZAP_BASE/no-op-counter.js";
$f[]="STUBURL_COUNTERHTML=\$ZAP_BASE/no-op-counter.html";
$f[]="STUBURL_WEBBUG=\$ZAP_BASE/webbug.gif";
$f[]="STUBURL_WEBBUGJS=\$ZAP_BASE/webbug.js";
$f[]="STUBURL_WEBBUGHTML=\$ZAP_BASE/webbug.html";
$f[]="";
$f[]="STUBURL_PRINT=				# off by default, set to 1";
$f[]="";
$f[]="export ZAP_MODE ZAP_BASE ZAP_BASE_SSL ZAP_PREMATCH ZAP_POSTMATCH ZAP_MATCH ZAP_NO_CHANGE";
$f[]="export STUBURL_AD STUBURL_ADSSL STUBURL_ADJS STUBURL_ADHTML STUBURL_ADMP3 \ ";
$f[]="	STUBURL_ADPOPUP STUBURL_ADSWF STUBURL_COUNTER STUBURL_COUNTERJS \ ";
$f[]="	STUBURL_COUNTERHTML STUBURL_WEBBUG STUBURL_WEBBUGJS STUBURL_WEBBUGHTML \ ";
$f[]="	STUBURL_PRINT STUBURL_ADHTMLTEXT STUBURL_ADJSTEXT";
$f[]="";
$f[]="# Here, having arranged the environment, we exec the real zapper.";
$f[]="# If you're chaining redirectors then comment out the direct exec below and";
$f[]="# uncomment (and adjust) the exec of zapchain which takes care of running";
$f[]="# multiple redirections.";
$f[]="";
$f[]="exec \"\$zapper\"";
$f[]="# exec /path/to/zapchain \"\$zapper\" /path/to/another/eg/squirm";	
@file_put_contents("/usr/bin/wrapzap",@implode("\n",$f));
@chmod("/usr/bin/wrapzap",0755);
echo "Starting......: adZapper wrapzap done...\n"; 

}


function SquidUser(){
	$unix=new unix();
	$squidconf=$unix->SQUID_CONFIG_PATH();
	if(!is_file($squidconf)){
		echo "Starting......: squidGuard unable to get squid configuration file\n";
		return "squid:squid";
	}
	
	writelogs("Open $squidconf");
	$array=explode("\n",@file_get_contents($squidconf));
	while (list ($index, $line) = each ($array)){
		if(preg_match("#cache_effective_user\s+(.+)#",$line,$re)){
			$user=trim($re[1]);
			$user=trim($re[1]);
		}
		if(preg_match("#cache_effective_group\s+(.+)#",$line,$re)){
			$group=trim($re[1]);
		}
	}
	
	return "$user:$group";
}


function SQUID_TEMPLATES(){
	if(system_is_overloaded(__FILE__)){return null;}
	$unix=new unix();
	$EXEC_PID_FILE="/etc/artica-postfix/".basename(__FILE__).".".__FUNCTION__.".pid";
	

	if($unix->process_exists(@file_get_contents($EXEC_PID_FILE))){
		print "Starting......: Checking squid Already executed pid ". @file_get_contents($EXEC_PID_FILE)."...\n";
		die();
	}
	
	$childpid=posix_getpid();
	@file_put_contents($EXEC_PID_FILE,$childpid);	
	
	
	if($GLOBALS["VERBOSE"]){echo "Search DataDir from compiled environments\n";}
	$document_root=$unix->SQUID_GET_DATADIR();
	echo "Starting......: squid DataDir: $document_root\n";
	if(!is_dir("$document_root/errors/English")){
		echo "Starting......: squid DataDir: $document_root/errors/English no such directory\n";
		return;
	}
	
	
	$q=new mysql();
	$q->CheckTable_dansguardian();
	foreach (glob("$document_root/errors/English/*") as $filename) {
		$file=basename($filename);
		$sql="SELECT TEMPLATE_DATA,TEMPLATE_DATA_SOURCE FROM squid_templates WHERE TEMPLATE_NAME='$file'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["TEMPLATE_DATA_SOURCE"]==null){
			echo "Starting......: squid importing template $file\n";
			$datas=addslashes(@file_get_contents($filename));
			$sql="INSERT INTO squid_templates (`TEMPLATE_DATA`,`TEMPLATE_DATA_SOURCE`,`TEMPLATE_NAME`) 
			VALUES ('$datas','$datas','$file')";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo "Starting......: squid failed $q->mysql_error\n";}
			$TEMPLATES[$file]["MODIFIED"]=$datas;
			$TEMPLATES[$file]["SRC"]=$datas;
		}else{
			$TEMPLATES[$file]["MODIFIED"]=$ligne["TEMPLATE_DATA"];
			$TEMPLATES[$file]["SRC"]=$ligne["TEMPLATE_DATA_SOURCE"];
		}
		
	}
	
	if(!is_array($TEMPLATES)){
		echo "Starting......: squid no templates found...\n";
		return;
	}
	
	$langsDirs=array('Armenian','Azerbaijani','Bulgarian','Catalan','Danish','Dutch','English','Estonian','Finnish','French','German','Greek',
	'Hebrew','Hungarian','Italian','Japanese','Korean','Lithuanian','Portuguese','Romanian','Russian-1251','Russian-koi8-r',
	'Serbian','Simplify_Chinese','Slovak','Spanish','Swedish','Traditional_Chinese','Turkish','Ukrainian-1251',
	'Ukrainian-koi8-u','Ukrainian-utf8','af','ar','ar-ae','ar-bh','ar-dz','ar-eg','ar-iq','ar-jo','ar-kw','ar-lb','ar-ly',
	'ar-ma','ar-om','ar-qa','ar-sa','ar-sy','ar-tn','ar-ye','az','az-az','bg','bg-bg','ca','cs','cs-cz','da','da-dk','de','de-at',
	'de-ch','de-de','de-li','de-lu','el','el-gr','en','en-au','en-bz','en-ca','en-gb','en-ie','en-in','en-jm','en-nz','en-ph','en-sg',
	'en-tt','en-uk','en-us','en-za','en-zw','es','es-ar','es-bo','es-cl','es-co','es-cr','es-do','es-ec','es-es','es-gt','es-hn','es-mx',
	'es-ni','es-pa','es-pe','es-pr','es-py','es-sv','es-uy','es-ve','et','et-ee','fa','fa-fa','fa-ir','fi','fi-fi','fr','fr-be','fr-ca',
	'fr-ch','fr-fr','fr-lu','fr-mc','he','he-il','hu','hu-hu','hy','hy-am','id','id-id','it','it-ch','it-it','ja','ja-jp','ko','ko-kp',
	'ko-kr','lt','lt-lt','lv','lv-lv','ms','ms-my','nl','nl-nl','pl','pl-pl','pt','pt-br','pt-pt','ro','ro-md','ro-ro','ru','ru-ru','sk',
	'sk-sk','sr','sr-latn','sr-latn-cs','sr-sp','sv','sv-fi','sv-se','templates','th','th-th','tr','tr-tr','uk','uk-ua','uz','zh-cn',
	'zh-hk','zh-mo','zh-sg','zh-tw');
	
	$css=SQUID_TEMPLATES_COMPILING_CSS();
	
	while (list ($template_name, $content) = each ($TEMPLATES)){
		if(preg_match("#<title>(.+?)</title>#is",$content["SRC"],$re)){
			$title=$re[1];
		}
		
		
		if(preg_match("#<body>(.+?)</body>#is",$content["MODIFIED"],$re)){$body=$re[1];}else{$body=$content["MODIFIED"];}
		$html="<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
		<html>
		<head>
		<title>$title</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"> 
		<style type=\"text/css\">
		<!--
		$css
		-->
		</style>
		</head>
		<body>
			$body
		</body>
		</html>
		";
		
		$TEMPLATES_NEW[$template_name]=$html;
		
	}
	reset($langsDirs);
	echo "Starting......: squid building templates...\n";
	while (list ($index, $subdir) = each ($langsDirs)){
		reset($TEMPLATES_NEW);
		@mkdir("$document_root/errors/$subdir",0755,true);
			while (list ($template_name, $template_data) = each ($TEMPLATES_NEW)){
				if(!@file_put_contents("$document_root/errors/$subdir/$template_name",$template_data)){
					echo "Starting......: squid $document_root/errors/$subdir/$template_name permission denied\n";
				}
			}
	}
	
	
	echo "Starting......: squid replace ". count($TEMPLATES). " templates in ". count($langsDirs)." languages done..\n";
	
}

function SQUID_TEMPLATES_COMPILING_CSS(){
	foreach (glob("/usr/share/artica-postfix/css/*.css") as $filename) {
		$datas[]=@file_get_contents($filename);
		
	}	
	
	foreach (glob("/usr/share/artica-postfix/ressources/templates/default/*.css") as $filename) {
		$datas[]=@file_get_contents($filename);
		
	}
	return @implode("\n",$datas);
	
}

function compilation_params(){
	
	exec($GLOBALS["SQUIDBIN"]." -v",$results);
	$text=@implode("\n", $results);
	if(preg_match("#configure options:\s+(.+)#is", $text,$re)){$text=$re[1];}
	if(preg_match_all("#'(.+?)'#is", $text, $re)){
		while (list ($index, $line) = each ($re[1])){
			if(preg_match("#(.+?)=(.+)#", $line,$ri)){
				$key=$ri[1];
				$value=$ri[2];
				$key=str_replace("--", "", $key);
				$array[$key]=$value;
				continue;
			}
			$key=$line;
			$value=1;
			$key=str_replace("--", "", $key);
			$array[$key]=$value;
					
			
		}

		@file_put_contents("/usr/share/artica-postfix/ressources/logs/squid.compilation.params", base64_encode(serialize($array)));
		shell_exec("/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/squid.compilation.params");
	}
}




// /etc/init.d/artica-postfix restart squid &



?>