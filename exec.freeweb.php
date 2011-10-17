<?php
$GLOBALS["FORCE"]=false;$GLOBALS["REINSTALL"]=false;
$GLOBALS["NO_HTTPD_CONF"]=false;
$GLOBALS["NO_HTTPD_RELOAD"]=false;
if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
	if(preg_match("#--reinstall#",implode(" ",$argv))){$GLOBALS["REINSTALL"]=true;}
	if(preg_match("#--no-httpd-conf#",implode(" ",$argv))){$GLOBALS["NO_HTTPD_CONF"]=true;}
	if(preg_match("#--noreload#",implode(" ",$argv))){$GLOBALS["NO_HTTPD_RELOAD"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["posix_getuid"]=0;
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.freeweb.inc');





$GLOBALS["SSLKEY_PATH"]="/etc/ssl/certs/apache";	

if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
}

CheckLibraries();
$GLOBALS["a2enmod"]=$GLOBALS["CLASS_UNIX"]->find_program("a2enmod");


if($GLOBALS["VERBOSE"]){
	echo "Debug mode TRUE for ". @implode(" ",$argv)."\n";
	echo "LOCATE_APACHE_BIN_PATH.....:".$GLOBALS["CLASS_UNIX"]->LOCATE_APACHE_BIN_PATH()."\n";
	echo "LOCATE_APACHE_CONF_PATH....:".$GLOBALS["CLASS_UNIX"]->LOCATE_APACHE_CONF_PATH()."\n";
	echo "a2enmod....................:{$GLOBALS["a2enmod"]}\n";
	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);
}


if($argv[1]=="--all-status"){mod_status_all();die();}
if($argv[1]=="--httpd"){CheckHttpdConf();reload_apache();die();}
if($argv[1]=="--build"){build();reload_apache();die();}
if($argv[1]=="--apache-user"){apache_user();die();}
if($argv[1]=="--sitename"){
		buildHost(null,$argv[2]);
		if(!$GLOBALS["NO_HTTPD_CONF"]){CheckHttpdConf();}
		if(!$GLOBALS["NO_HTTPD_RELOAD"]){reload_apache();}
		die();
}
if($argv[1]=="--remove-host"){remove_host($argv[2]);reload_apache();die();}
if($argv[1]=="--perms"){FDpermissions($argv[2]);die();}
if($argv[1]=="--failed-start"){CheckFailedStart();die();exit;}
if($argv[1]=="--install-groupware"){install_groupware($argv[2]);die();exit;}
if($argv[1]=="--resolv"){resolv_servers();die();exit;}
if($argv[1]=="--drupal"){createdupal($argv[2]);die();exit;}
if($argv[1]=="--drupal-infos"){drupal_infos($argv[2]);die();exit;}
if($argv[1]=="--drupal-uadd"){drupal_add_user($argv[2],$argv[3]);die();exit;}
if($argv[1]=="--drupal-udel"){drupal_deluser($argv[2],$argv[3]);die();exit;}
if($argv[1]=="--drupal-uact"){drupal_enuser($argv[2],$argv[3],$argv[4]);die();exit;}
if($argv[1]=="--drupal-upriv"){drupal_privuser($argv[2],$argv[3],$argv[4]);die();exit;}
if($argv[1]=="--drupal-cron"){drupal_cron();die();exit;}
if($argv[1]=="--drupal-modules"){drupal_dump_modules($argv[2]);die();exit;}
if($argv[1]=="--drupal-modules-install"){drupal_install_modules($argv[2]);die();exit;}
if($argv[1]=="--drupal-reinstall"){drupal_reinstall($argv[2]);die();exit;}
if($argv[1]=="--drupal-schedules"){drupal_schedules();die();exit;}
if($argv[1]=="--status"){mod_status($argv[2]);die();exit;}
if($argv[1]=="--listwebs"){listwebs();die();exit;}





help();

// mod_pagespeed ! ! 
//mod_evasive_
//mod_deflate.so

//http://www.tux-planet.fr/installation-et-configuration-de-modsecurity/

function help(){
	echo "Usage : \t(use --verbose for more infos)\n";
	echo "--build............................: Configure apache\n";
	echo "--apache-user --verbose............: Set Apache account in memory\n";
	echo "--sitename 'webservername'.........: Build vhost for webservername\n";
	echo "--remove-host 'webservername'......: Remove vhost for webservername\n";
	echo "--install-groupware 'webservername': Install the predefined groupware\n";
	echo "--httpd............................: Rebuild main configuration and modules\n";
	echo "--perms............................: Check files and folders permissions\n";
	echo "--failed-start.....................: Verify why Apache daemon did not want to run\n";
	echo "--resolv...........................: Verify if hostnames are in DNS\n";
	echo "--drupal...........................: Install drupal site for [servername]\n";
	echo "--drupal-infos.....................: Populate drupal informations in Artica database for [servername]\n";
	echo "--drupal-uadd......................: Create new drupal [user] for [servername]\n";
	echo "--drupal-udel......................: Delete  [user] for [servername]\n";
	echo "--drupal-uact......................: Activate  [user] 1/0 for [servername]\n";
	echo "--drupal-upriv.....................: set privileges  [user] administrator|user|anonym for [servername]\n";
	echo "--drupal-cron......................: execute necessary cron for all drupal websites\n";
	echo "--drupal-modules...................: dump drupal modules for [servername]\n";
	echo "--drupal-modules-install...........: install pre-defined modules [servername]\n";
	echo "--drupal-schedules.................: Run artica orders on the servers\n";
	echo "--listwebs.........................: List websites currently sets\n";
}

function create_cron_task(){
	
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nice=$unix->EXEC_NICE();
	$f[]="MAILTO=\"\"";
	$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
	$f[]="0,10,20,30,40,50 * * * * root $nice$php5 ".__FILE__." --resolv >/dev/null 2>&1";
	$f[]="";
	
	@file_put_contents("/etc/cron.d/iptaccount", @implode("\n", $f));
	shell_exec("/bin/chmod 640 /etc/cron.d/freeweb_resolv >/dev/null 2>&1");	
	
}

function listwebs(){
	$unix=new unix();
	$sql="SELECT * FROM freeweb ORDER BY servername";
	$httpdconf=$unix->LOCATE_APACHE_CONF_PATH();
	$apacheusername=$unix->APACHE_SRC_ACCOUNT();
	$GLOBALS["apacheusername"]=$apacheusername;
	$DAEMON_PATH=$unix->getmodpathfromconf($httpdconf);
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	$d_path=$unix->APACHE_DIR_SITES_ENABLED();
	$mods_enabled=$DAEMON_PATH."/mods-enabled";
	
	
	echo "Starting......: Apache daemon path: $d_path\n";
	echo "Starting......: Apache mods path..: $mods_enabled\n";
	
	if(!is_dir($d_path)){@mkdir($d_path,666,true);}
	if(!is_dir($mods_enabled)){@mkdir($mods_enabled,666,true);}
	
	$count=mysql_num_rows($results);
	echo "Starting......: Apache checking virtual web sites count:$count\n";
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$hostname=$ligne["servername"];
		echo "Starting......: available $hostname\n";
		
	}
	
}



function apache_user(){
	$unix=new unix();
	$apacheusername=$unix->APACHE_SRC_ACCOUNT();
	$sock=new sockets();
	if($GLOBALS["VERBOSE"]){echo "Starting......: Apache APACHE_SRC_ACCOUNT: $apacheusername\n";}
	$sock->SET_INFO('APACHE_SRC_ACCOUNT',"$apacheusername");
}

function reload_apache(){
	$apache2ctl=$GLOBALS["CLASS_UNIX"]->find_program("apache2ctl");
	$LOCATE_APACHE_CONF_PATH=$GLOBALS["CLASS_UNIX"]->LOCATE_APACHE_CONF_PATH();
	if(is_file($apache2ctl)){
		$cmd="$apache2ctl -f $LOCATE_APACHE_CONF_PATH -k restart 2>&1";
		echo "Starting......: Apache reloading \"$cmd\"\n";
		
		exec($cmd,$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Cannot load .+?mod_dav_fs.+?into server#",$ligne)){
				echo "Starting......: Apache $ligne\n";
				echo "Starting......: Apache mod_dav_fs failed, disable it\n";
				$sock=new sockets();
				$sock->SET_INFO("ApacheDisableModDavFS",1);
				CheckHttpdConf();
				continue;
			}
			
			echo "Starting......: Apache reloading $ligne\n";
		}
	}
}





function remove_files(){
	if(is_file("/etc/httpd/conf.d/README")){@unlink("/etc/httpd/conf.d/README");}
}

function patch_suse_default_server(){
		$tmp123=@file_get_contents("/etc/apache2/default-server.conf");
		$tmp123=str_replace("/srv/www/htdocs","/var/www",$tmp123);
		$tmp123=str_replace("/srv/www/","/var/www/",$tmp123);
		$tmp123=str_replace("Options None","Options Indexes FollowSymLinks MultiViews",$tmp123);
		$tmp123=str_replace("Include /etc/apache2/conf.d/*.conf","",$tmp123);
		$tmp123=str_replace("Include /etc/apache2/mod_userdir.conf","",$tmp123);
		@file_put_contents("/etc/apache2/default-server.conf", $tmp123);$tmp123=null;	
}



function build(){
	$unix=new unix();
	$mef=basename(__FILE__);
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid,$mef)){
		echo "Starting......: Apache building : Process Already exist pid $oldpid line:".__LINE__."\n";
		return;
	}	
	@file_put_contents($pidfile, getmypid());		
	
	CheckHttpdConf();
	RemoveAllSites();
	create_cron_task();
	$sock=new sockets();
	$php5=$unix->LOCATE_PHP5_BIN();
	$varWwwPerms=$sock->GET_INFO("varWwwPerms");
	if($varWwwPerms==null){$varWwwPerms=755;}
	
	remove_files();
	$sql="SELECT * FROM freeweb ORDER BY servername";
	$httpdconf=$unix->LOCATE_APACHE_CONF_PATH();
	$apacheusername=$unix->APACHE_SRC_ACCOUNT();
	$GLOBALS["apacheusername"]=$apacheusername;
	$DAEMON_PATH=$unix->getmodpathfromconf($httpdconf);
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	$d_path=$unix->APACHE_DIR_SITES_ENABLED();
	$mods_enabled=$DAEMON_PATH."/mods-enabled";
	
	
	echo "Starting......: Apache daemon path: $d_path\n";
	echo "Starting......: Apache mods path..: $mods_enabled\n";
	
	if(!is_dir($d_path)){@mkdir($d_path,666,true);}
	if(!is_dir($mods_enabled)){@mkdir($mods_enabled,666,true);}
	
	$count=mysql_num_rows($results);
	echo "Starting......: Apache checking virtual web sites count:$count\n";
	if($count==0){
		$users=new usersMenus();
		echo "Starting......: Apache building default $users->hostname...\n";
		
		buildHost($unix->LIGHTTPD_USER(),$users->hostname,0,$d_path);
	}
	
	if($GLOBALS["VERBOSE"]){$add_plus=" --verbose";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$uid=$ligne["uid"];
		$hostname=$ligne["servername"];
		$ssl=$ligne["useSSL"];	
		
		echo "Starting......: Apache \"$hostname\" starting\n";
		
		$cmd="$php5 ".__FILE__." --sitename \"$hostname\" --no-httpd-conf --noreload$add_plus";
		if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$cmd\"\n";}
		shell_exec($cmd);
	}
	
	$users=$GLOBALS["CLASS_USERS_MENUS"];
	$APACHE_MOD_AUTHNZ_LDAP=$users->APACHE_MOD_AUTHNZ_LDAP;
	if(is_file($GLOBALS["a2enmod"])){
		if($APACHE_MOD_AUTHNZ_LDAP){
			if($GLOBALS["VERBOSE"]){echo "Starting......: Apache {$GLOBALS["a2enmod"]} authnz_ldap\n";} 
			shell_exec("{$GLOBALS["a2enmod"]} authnz_ldap >/dev/null 2>&1");
		}
	} 
	
	

	$sock=$GLOBALS["CLASS_SOCKETS"];
	if($sock->GET_INFO("ArticaMetaEnabled")==1){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-freewebs");
	}

	
}

function RemoveAllSites(){
	$unix=new unix();	
	$httpdconf=$unix->LOCATE_APACHE_CONF_PATH();
	$DAEMON_PATH=$unix->getmodpathfromconf($httpdconf);
	$sites_enabled="$DAEMON_PATH/sites-enabled";
	if(!is_dir("$sites_enabled")){@mkdir($sites_enabled,666,true);}
	
	foreach (glob("$sites_enabled/artica-*.conf") as $filename) {
		$file=basename($filename);
		@unlink($filename);
		echo "Starting......: Apache remove $file done\n";
	}		
}

function CheckHttpdConf(){
	EnableMods();
	apache_user();
	$sock=$GLOBALS["CLASS_SOCKETS"];
	$unix=new unix();
	$users=new usersMenus();
	$freeweb=new freeweb();
	$httpdconf=$unix->LOCATE_APACHE_CONF_PATH();
	if(!is_file($httpdconf)){echo "Starting......: Apache unable to stat configuration file\n";return;}
	$d_path=$unix->APACHE_DIR_SITES_ENABLED();
	$DAEMON_PATH=$unix->getmodpathfromconf($httpdconf);
	$APACHE_SRC_ACCOUNT=$unix->APACHE_SRC_ACCOUNT();
	$APACHE_SRC_GROUP=$unix->APACHE_SRC_GROUP();	
	
	if(is_file("/etc/apache2/sites-available/default-ssl")){@unlink("/etc/apache2/sites-available/default-ssl");}
	echo "Starting......: Apache daemon path: \"$DAEMON_PATH\" run has \"$APACHE_SRC_ACCOUNT:$APACHE_SRC_GROUP\"\n";
	if($APACHE_SRC_ACCOUNT==null){echo "Starting......: Apache daemon unable to determine user that will run apache\n";die();}
	if(!is_dir("/var/log/apache2")){@mkdir("/var/log/apache2",755,true);}
	
	$ApacheDisableModDavFS=$sock->GET_INFO("ApacheDisableModDavFS");
	$FreeWebListen=trim($sock->GET_INFO("FreeWebListen"));
	$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
	$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");
	$FreeWebListen=$sock->GET_INFO("FreeWebListen");
	$FreeWebsEnableModSecurity=$sock->GET_INFO("FreeWebsEnableModSecurity");
	$FreeWebsEnableModEvasive=$sock->GET_INFO("FreeWebsEnableModEvasive");
	$FreeWebsEnableModQOS=$sock->GET_INFO("FreeWebsEnableModQOS");
	$FreeWebsEnableOpenVPNProxy=$sock->GET_INFO("FreeWebsEnableOpenVPNProxy");
	$FreeWebsOpenVPNRemotPort=trim($sock->GET_INFO("FreeWebsOpenVPNRemotPort"));
	$FreeWebDisableSSL=trim($sock->GET_INFO("FreeWebDisableSSL"));
	
	$TomcatEnable=$sock->GET_INFO("TomcatEnable");
	if($FreeWebListen==null){$FreeWebListen="*";}
	if($FreeWebListen<>"*"){$FreeWebListenApache="$FreeWebListen";}
	if(!isset($FreeWebListenApache)){$FreeWebListenApache="*";}
	if(!is_numeric($FreeWebDisableSSL)){$FreeWebDisableSSL=0;}
	if(!is_numeric($FreeWebListenSSLPort)){$FreeWebListenSSLPort=443;}
	if(!is_numeric($FreeWebListenPort)){$FreeWebListenPort=80;}
	if(!is_numeric($ApacheDisableModDavFS)){$ApacheDisableModDavFS=0;}
	if(!is_numeric($FreeWebsEnableModSecurity)){$FreeWebsEnableModSecurity=0;}
	if(!is_numeric($FreeWebsEnableModEvasive)){$FreeWebsEnableModEvasive=0;}
	if(!is_numeric($FreeWebsEnableModQOS)){$FreeWebsEnableModQOS=0;}		
	if(!is_numeric($FreeWebsEnableOpenVPNProxy)){$FreeWebsEnableOpenVPNProxy=0;}
	if(!is_numeric($TomcatEnable)){$TomcatEnable=1;}
	
	$users=new usersMenus();
	$APACHE_MODULES_PATH=$users->APACHE_MODULES_PATH;	
	
	$toremove[]="mod-status.init";
	$toremove[]="status.conf";
	$toremove[]="fcgid.load";
	$toremove[]="fcgid.conf";
	$toremove[]="log_sql.load";
	$toremove[]="log_sql_mysql.load";

	
	if(is_file("/etc/apache2/sites-enabled/000-default")){@unlink("/etc/apache2/sites-enabled/000-default");}
	if(is_file("/etc/apache2/sites-available/default")){@unlink("/etc/apache2/sites-available/default");}
	if(is_file("/etc/apache2/conf.d/zarafa-webaccess.conf")){@unlink("/etc/apache2/conf.d/zarafa-webaccess.conf");}
	if(is_file("/etc/apache2/conf.d/zarafa-webaccess-mobile.conf")){@unlink("/etc/apache2/conf.d/zarafa-webaccess-mobile.conf");}
	if(is_file("/etc/httpd/conf/extra/httpd-info.conf")){@unlink("/etc/httpd/conf/extra/httpd-info.conf");}
	while (list ($num, $file) = each ($toremove) ){
		shell_exec("/bin/rm -f $DAEMON_PATH/mods-enabled/$file >/dev/null 2>&1");
		shell_exec("/bin/rm -f $DAEMON_PATH/mods-available/$file >/dev/null 2>&1");
		
	}
	
	$sql="SELECT ServerPort FROM freeweb WHERE ServerPort>0 GROUP BY ServerPort";
	$q=new mysql();
	$conf[]="NameVirtualHost {$FreeWebListenApache}:$FreeWebListenPort";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){$conf[]="NameVirtualHost {$FreeWebListenApache}:{$ligne["ServerPort"]}";}	
	
	$conf[]="Listen $FreeWebListenPort";
	if($FreeWebDisableSSL==0){
		$conf[]="<IfModule mod_ssl.c>";
		$conf[]="\tListen $FreeWebListenSSLPort";
		$conf[]="\tNameVirtualHost $FreeWebListen:$FreeWebListenSSLPort";
		$conf[]="</IfModule>";
		$conf[]="";
		$conf[]="<IfModule mod_gnutls.c>";
		$conf[]="\tNameVirtualHost $FreeWebListen:$FreeWebListenSSLPort";
		$conf[]="\tListen $FreeWebListenSSLPort";
		$conf[]="</IfModule>";
		$conf[]="<IfModule mod_fcgid.c>";
	}
	$conf[]="\tPHP_Fix_Pathinfo_Enable 1";
	
	$conf[]="";
	if(!is_dir("$DAEMON_PATH/sites-available")){@mkdir("$DAEMON_PATH/sites-available",666,true);}
	@file_put_contents("$DAEMON_PATH/ports.conf",@implode("\n",$conf));
	
	echo "Starting......: Apache $DAEMON_PATH/ports.conf for NameVirtualHost $FreeWebListen:$FreeWebListenPort done\n";
	if($FreeWebsEnableModSecurity==1){
			$f[]="<IfModule security2_module>";
			$f[]="   SecRuleEngine On";
			$f[]="   #SecServerSignature";
			//$f[]="   #SecFilterCheckURLEncoding {$Params["SecFilterCheckURLEncoding"]}";
			//$f[]="   #SecFilterCheckUnicodeEncoding {$Params["SecFilterCheckUnicodeEncoding"]}";
			//$f[]="   SecFilterForceByteRange 1 255";
			//$f[]="   SecAuditEngine RelevantOnly";
			$f[]="   SecAuditEngine RelevantOnly";
			$f[]="   SecAuditLog /var/log/apache2/modsec_audit_log";
			$f[]="   SecDebugLog /var/log/apache2/modsec_debug_log";
			$f[]="   SecDebugLogLevel 0";
			$f[]="   SecRequestBodyAccess Off";
			$f[]="   SecDefaultAction \"phase:2,deny,log,status:'Hello World!'\"";
			$f[]="</IfModule>\n\n";
			echo "Starting......: Apache $DAEMON_PATH/mod_security.conf\n";
			@file_put_contents("$DAEMON_PATH/mod_security.conf",@implode("\n",$f));
			unset($f);
	}
	
	if($FreeWebsEnableModEvasive==1){
		$Params=unserialize(base64_decode($sock->GET_INFO("modEvasiveDefault")));
			if(!is_numeric($Params["DOSHashTableSize"])){$Params["DOSHashTableSize"]=1024;}
			if(!is_numeric($Params["DOSPageCount"])){$Params["DOSPageCount"]=10;}
			if(!is_numeric($Params["DOSSiteCount"])){$Params["DOSSiteCount"]=150;}
			if(!is_numeric($Params["DOSPageInterval"])){$Params["DOSPageInterval"]=1.5;}
			if(!is_numeric($Params["DOSSiteInterval"])){$Params["DOSSiteInterval"]=1.5;}
			if(!is_numeric($Params["DOSBlockingPeriod"])){$Params["DOSBlockingPeriod"]=10.7;}		
			$f[]="   LoadModule evasive20_module modules/mod_evasive20.so";
			$f[]="   ExtendedStatus On";
			$f[]="   DOSHashTableSize {$Params["DOSHashTableSize"]}";
			$f[]="   DOSPageCount {$Params["DOSPageCount"]}";
			$f[]="   DOSSiteCount {$Params["DOSSiteCount"]}";
			$f[]="   DOSPageInterval {$Params["DOSPageInterval"]}";
			$f[]="   DOSSiteInterval {$Params["DOSSiteInterval"]}";
			$f[]="   DOSBlockingPeriod {$Params["DOSBlockingPeriod"]}";
			$f[]="   DOSLogDir  \"/var/log/apache2/mod_evasive.log\"";
			$f[]="   DOSSystemCommand \"/bin/echo `date '+%F %T'` apache2  %s >> /var/log/apache2/dos_evasive_attacks.log\"";
			$f[]="";
			echo "Starting......: Apache $DAEMON_PATH/mod_evasive.conf\n";
			@file_put_contents("$DAEMON_PATH/mod_evasive.conf",@implode("\n",$f));
			unset($f);		
		
	}
	
	
	apache_security($DAEMON_PATH);
	$httpdconf_data=@file_get_contents($httpdconf);
	if(preg_match("#<Location \/server-status>(.+?)<\/Location>#is",$httpdconf_data,$re)){$httpdconf_data=str_replace($re[0], "", $httpdconf_data);}
	
	
	
	$f=explode("\n",$httpdconf_data);
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^Include\s+#",$ligne)){echo "Starting......: Apache removing {$f[$num]}\n";$f[$num]=null;}
		if(preg_match("#\#.*?Include\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#Listen\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#ProxyRequests#",$ligne)){$f[$num]=null;}
		if(preg_match("#ProxyVia#",$ligne)){$f[$num]=null;}
		if(preg_match("#AllowCONNECT#",$ligne)){$f[$num]=null;}
		if(preg_match("#KeepAlive#",$ligne)){$f[$num]=null;}
		if(preg_match("#Timeout\s+[0-9]+#",$ligne)){$f[$num]=null;}
		if(preg_match("#MaxKeepAliveRequests\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#KeepAliveTimeout\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#MinSpareServers\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#MaxSpareServers\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#StartServers\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#MaxClients\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#MaxRequestsPerChild\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#LoadModule\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#ErrorLog\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#LogFormat\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#User\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#Group\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#CustomLog\s+#",$ligne)){$f[$num]=null;}
		if(preg_match("#LogLevel#",$ligne)){$f[$num]=null;}
		if(trim($ligne)=="Loglevel info"){$f[$num]=null;}
		
	}
	
	$FreeWebPerformances=unserialize(base64_decode($sock->GET_INFO("FreeWebPerformances")));
	if(!isset($FreeWebPerformances["Timeout"])){$FreeWebPerformances["Timeout"]=300;}
	if(!isset($FreeWebPerformances["KeepAlive"])){$FreeWebPerformances["KeepAlive"]=0;}
	if(!isset($FreeWebPerformances["MaxKeepAliveRequests"])){$FreeWebPerformances["MaxKeepAliveRequests"]=100;}
	if(!isset($FreeWebPerformances["KeepAliveTimeout"])){$FreeWebPerformances["KeepAliveTimeout"]=15;}
	if(!isset($FreeWebPerformances["MinSpareServers"])){$FreeWebPerformances["MinSpareServers"]=5;}
	if(!isset($FreeWebPerformances["MaxSpareServers"])){$FreeWebPerformances["MaxSpareServers"]=10;}
	if(!isset($FreeWebPerformances["StartServers"])){$FreeWebPerformances["StartServers"]=5;}
	if(!isset($FreeWebPerformances["MaxClients"])){$FreeWebPerformances["MaxClients"]=50;}
	if(!isset($FreeWebPerformances["MaxRequestsPerChild"])){$FreeWebPerformances["MaxRequestsPerChild"]=10000;}	
	if(!is_numeric($FreeWebPerformances["Timeout"])){$FreeWebPerformances["Timeout"]=300;}
	if(!is_numeric($FreeWebPerformances["KeepAlive"])){$FreeWebPerformances["KeepAlive"]=0;}
	if(!is_numeric($FreeWebPerformances["MaxKeepAliveRequests"])){$FreeWebPerformances["MaxKeepAliveRequests"]=100;}
	if(!is_numeric($FreeWebPerformances["KeepAliveTimeout"])){$FreeWebPerformances["KeepAliveTimeout"]=15;}
	if(!is_numeric($FreeWebPerformances["MinSpareServers"])){$FreeWebPerformances["MinSpareServers"]=5;}
	if(!is_numeric($FreeWebPerformances["MaxSpareServers"])){$FreeWebPerformances["MaxSpareServers"]=10;}
	if(!is_numeric($FreeWebPerformances["StartServers"])){$FreeWebPerformances["StartServers"]=5;}
	if(!is_numeric($FreeWebPerformances["MaxClients"])){$FreeWebPerformances["MaxClients"]=50;}
	if(!is_numeric($FreeWebPerformances["MaxRequestsPerChild"])){$FreeWebPerformances["MaxRequestsPerChild"]=10000;}

	
	
	
	reset($f);
	while (list ($num, $ligne) = each ($f) ){
		if(trim($ligne)==null){continue;}
		if(substr($ligne,0,1)=="#"){continue;}
		$httpd[]=$ligne;
	}
	
	if($FreeWebPerformances["KeepAlive"]==1){$FreeWebPerformances["KeepAlive"]="On";}else{$FreeWebPerformances["KeepAlive"]="Off";}
	$httpd[]="User				   {$APACHE_SRC_ACCOUNT}";
	$httpd[]="Group				   {$APACHE_SRC_GROUP}";
	$httpd[]="Timeout              {$FreeWebPerformances["Timeout"]}";
	$httpd[]="KeepAlive            {$FreeWebPerformances["KeepAlive"]}";
	$httpd[]="KeepAliveTimeout     {$FreeWebPerformances["KeepAliveTimeout"]}";
	$httpd[]="StartServers         {$FreeWebPerformances["StartServers"]}";
	$httpd[]="MaxClients           {$FreeWebPerformances["MaxClients"]}";
	$httpd[]="MinSpareServers      {$FreeWebPerformances["MinSpareServers"]}";
	$httpd[]="MaxSpareServers      {$FreeWebPerformances["MaxSpareServers"]}"; 
	$httpd[]="MaxRequestsPerChild  {$FreeWebPerformances["MaxRequestsPerChild"]}";
	$httpd[]="MaxKeepAliveRequests {$FreeWebPerformances["MaxKeepAliveRequests"]}";
	
	
	if($FreeWebsEnableOpenVPNProxy==1){
		if($FreeWebsOpenVPNRemotPort<>null){
			$httpd[]="ProxyRequests On";
			$httpd[]="ProxyVia On";
			$httpd[]="AllowCONNECT $FreeWebsOpenVPNRemotPort";
			$httpd[]="KeepAlive On";
		}
	}
	//$dir_master=$unix->getmodpathfromconf();
	$httpd[]="Include $DAEMON_PATH/mods-enabled/*.load";
	$httpd[]="Include $DAEMON_PATH/mods-enabled/*.conf";
	$httpd[]="Include $DAEMON_PATH/mods-enabled/*.init";
	if(basename($httpdconf)<>"httpd.conf"){$httpd[]="Include $DAEMON_PATH/httpd.conf";}
	$httpd[]="Include $DAEMON_PATH/ports.conf";
	if($FreeWebsEnableModSecurity==1){$httpd[]="Include $DAEMON_PATH/mod_security.conf";}
	if($FreeWebsEnableModEvasive==1){$httpd[]="Include $DAEMON_PATH/mod_evasive.conf";}
	
	$httpd[]='Loglevel info';
	$httpd[]='ErrorLog /var/log/apache2/error.log';
	$httpd[]='LogFormat "%h %l %u %t \"%r\" %<s %b" common';
	$httpd[]='CustomLog /var/log/apache2/access.log common';  	
	
	
	$mod_status=$freeweb->mod_status();
	if($mod_status<>null){
		$status[]="<IfModule mod_status.c>";
		$status[]="\tExtendedStatus On";
		$status[]="$mod_status";
		$status[]="</IfModule>";
		@file_put_contents("$DAEMON_PATH/mods-enabled/mod-status.init", @implode("\n", $status));
	}
	
	
	@unlink("$DAEMON_PATH/mods-enabled/pagespeed.conf");
	
	if($users->APACHE_MOD_PAGESPEED){
		if(!is_dir("/var/cache/apache2/mod_pagespeed/default/files")){@mkdir("/var/cache/apache2/mod_pagespeed/default/files",644,true);}
		$pspedd[]="<IfModule pagespeed_module>";
 		$pspedd[]="\tModPagespeedFileCachePath            \"/var/cache/apache2/mod_pagespeed/default\"";
		$pspedd[]="\tModPagespeedGeneratedFilePrefix      \"/var/cache/apache2/mod_pagespeed/files/\"";
		$pspedd[]="\tSetOutputFilter MOD_PAGESPEED_OUTPUT_FILTER";
    	$pspedd[]="\tAddOutputFilterByType MOD_PAGESPEED_OUTPUT_FILTER text/html";
    	$pspedd[]="</IfModule>";
    	@file_put_contents("$DAEMON_PATH/mods-enabled/pagespeed.conf", @implode("\n", $pspedd));
	}
	
	if($users->APACHE_MOD_LOGSSQL){
			$q=new mysql();
			if(!$q->DATABASE_EXISTS("apachelogs")){$q->CREATE_DATABASE("apachelogs");}
			$APACHE_MOD_LOGSSQL[]="<IfModule log_sql_mysql_module>";
			$APACHE_MOD_LOGSSQL[]="\tLogSQLLoginInfo mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server:$q->mysql_port/apachelogs";
			$APACHE_MOD_LOGSSQL[]="\tLogSQLMassVirtualHosting On";
			$APACHE_MOD_LOGSSQL[]="\tLogSQLmachineID $users->hostname";
			$APACHE_MOD_LOGSSQL[]="\tLogSQLTransferLogFormat AbcHhmMpRSstTUuvz";
			$APACHE_MOD_LOGSSQL[]="</IfModule>";	
			@file_put_contents("$DAEMON_PATH/mods-enabled/log_sql_module.conf", @implode("\n", $APACHE_MOD_LOGSSQL));
	}
	
	
	
	
	if(is_file("/etc/apache2/sysconfig.d/loadmodule.conf")){$httpd[]="Include /etc/apache2/sysconfig.d/loadmodule.conf";}
	if(is_file("/etc/apache2/uid.conf")){$httpd[]="Include /etc/apache2/uid.conf";}
	if(is_file("/etc/apache2/default-server.conf")){patch_suse_default_server();$httpd[]="Include /etc/apache2/default-server.conf";}
	$httpd[]="Include $DAEMON_PATH/conf.d/";
	$httpd[]="Include $DAEMON_PATH/sites-enabled/";
	if(is_file("$APACHE_MODULES_PATH/mod_php5.so")){$httpd[]="LoadModule php5_module $APACHE_MODULES_PATH/mod_php5.so";}
	if(is_file("$APACHE_MODULES_PATH/mod_ldap.so")){$httpd[]="LoadModule ldap_module $APACHE_MODULES_PATH/mod_ldap.so";}
	
	
	
	
	if($ApacheDisableModDavFS==0){
			if(is_file("$APACHE_MODULES_PATH/mod_dav.so")){echo "Starting......: Apache module 'dav_module' enabled\n";$httpd[]="LoadModule dav_module $APACHE_MODULES_PATH/mod_dav.so";}		
			if(is_file("$APACHE_MODULES_PATH/mod_dav_lock.so")){echo "Starting......: Apache module 'dav_lock_module' enabled\n";$httpd[]="LoadModule dav_lock_module $APACHE_MODULES_PATH/mod_dav_lock.so";}
			if(is_file("$APACHE_MODULES_PATH/mod_dav_fs.so")){echo "Starting......: Apache module 'dav_fs_module' enabled\n";$httpd[]="LoadModule dav_fs_module $APACHE_MODULES_PATH/mod_dav_fs.so";}			
	}		
	
	$httpd[]="";
	echo "Starting......: Apache $httpdconf done\n";
	@file_put_contents($httpdconf,@implode("\n",$httpd));
	
	
	
	
	// MODULES -----------------------------------------------------------------------
	
	
	if(!is_dir("$DAEMON_PATH/mods-enabled")){@mkdir("$DAEMON_PATH/mods-enabled",666,true);}
	if(!is_file("$DAEMON_PATH/httpd.conf")){@file_put_contents("$DAEMON_PATH/httpd.conf", "#");}
	
	
	@unlink("/etc/libapache2-mod-jk/workers.properties");
	@unlink("/etc/apache2/workers.properties");	
	@unlink("$DAEMON_PATH/conf.d/jk.conf");
	
	
	$array["php5_module"]="libphp5.so";
	//$array["access_module"]="mod_access.so";
	$array["qos_module"]="mod_qos.so";
	$array["rewrite_module"]="mod_rewrite.so";
	$array["cache_module"]="mod_cache.so";
	$array["disk_cache_module"]="mod_disk_cache.so";
	$array["mem_cache_module"]="mod_mem_cache.so";
	$array["expires_module"]="mod_expires.so";
	$array["status_module"]="mod_status.so";
	$array["geoip_module"]="mod_geoip.so";
	$array["info_module"]="mod_info.so";
	$array["suexec_module"]="mod_suexec.so";
	$array["fcgid_module"]="mod_fcgid.so";
	$array["authz_host_module"]="mod_authz_host.so";
	$array["dir_module"]="mod_dir.so";
	$array["mime_module"]="mod_mime.so";
	$array["log_config_module"]="mod_log_config.so";
	$array["alias_module"]="mod_alias.so";
	$array["autoindex_module"]="mod_autoindex.so";
	$array["negotiation_module"]="mod_negotiation.so";
	$array["setenvif_module"]="mod_setenvif.so";
	$array["logio_module"]="mod_logio.so";
	$array["auth_basic_module"]="mod_auth_basic.so";
	$array["authn_file_module"]="mod_authn_file.so";
	$array["vhost_alias_module"]="mod_vhost_alias.so";
	$array["ssl_module"]="mod_ssl.so";
	$array["log_sql_module"]="mod_log_sql.so";
	$array["log_sql_mysql_module"]="mod_log_sql_mysql.so";
	
	
	 
	
	
	
	 
	
	if(is_file("$APACHE_MODULES_PATH/mod_pagespeed.so")){
		echo "Starting......: Apache module 'mod_pagespeed' enabled\n";
		$ppsped[]="LoadModule pagespeed_module $APACHE_MODULES_PATH/mod_pagespeed.so";
		if(is_file("$APACHE_MODULES_PATH/mod_deflate.so")){
			$ppsped[]="# Only attempt to load mod_deflate if it hasn't been loaded already.";
			$ppsped[]="<IfModule !mod_deflate.c>";
			$ppsped[]="\tLoadModule deflate_module $APACHE_MODULES_PATH/mod_deflate.so";
			$ppsped[]="</IfModule>";
		}
		@file_put_contents("$DAEMON_PATH/mods-enabled/mod_pagespeed.load",@implode("\n", $ppsped));
	}else{
		echo "Starting......: Apache module 'mod_pagespeed' $APACHE_MODULES_PATH/mod_pagespeed.so no such file\n";
	}
	
	if($users->TOMCAT_INSTALLED){
		if($TomcatEnable==1){
			if(is_dir($users->TOMCAT_DIR)){
				if(is_dir($users->TOMCAT_JAVA)){
					$array["jk_module"]="mod_jk.so";
					$ftom[]="workers.tomcat_home=$users->TOMCAT_DIR";
					$ftom[]="workers.java_home=$users->TOMCAT_JAVA";
					$ftom[]="ps=/";
					$ftom[]="worker.list=ajp13_worker";
					$ftom[]="worker.ajp13_worker.port=8009";
					$ftom[]="worker.ajp13_worker.host=127.0.0.1";
					$ftom[]="worker.ajp13_worker.type=ajp13";
					$ftom[]="worker.ajp13_worker.lbfactor=1";
					$ftom[]="worker.loadbalancer.type=lb";
					$ftom[]="worker.loadbalancer.balance_workers=ajp13_worker";
					$ftom[]="";		
					@file_put_contents("/etc/apache2/workers.properties", @implode("\n", $ftom));
					@mkdir("/etc/libapache2-mod-jk",644);
					@file_put_contents("/etc/libapache2-mod-jk/workers.properties", @implode("\n", $ftom));	
					$faptom[]="<ifmodule mod_jk.c>";
					$faptom[]="\tJkWorkersFile /etc/apache2/workers.properties";
					$faptom[]="\tJkLogFile /var/log/apache2/mod_jk.log";
					$faptom[]="\tJkLogLevel error";
					$faptom[]="</ifmodule>";
					@file_put_contents("$DAEMON_PATH/conf.d/jk.conf", @implode("\n", $faptom));	
				}
			}			
		}
		
	}

	@unlink("$DAEMON_PATH/mods-enabled/mod-security.load");
	@unlink("$DAEMON_PATH/mods-enabled/mod_security.load");
	@unlink("$DAEMON_PATH/mods-enabled/mod-evasive.load");
	@unlink("$DAEMON_PATH/mods-enabled/mod_evasive.load");
	@unlink("$DAEMON_PATH/mods-enabled/geoip.load");
	@unlink("$DAEMON_PATH/mods-enabled/status.conf");
	@unlink("$DAEMON_PATH/mods-enabled/status.load");
	@unlink("$DAEMON_PATH/mods-enabled/php5.load");
	@unlink("$DAEMON_PATH/mods-enabled/jk.load");
	@unlink("$DAEMON_PATH/mods-enabled/dav_lock_module.load");
	@unlink("$DAEMON_PATH/mods-enabled/dav_module.load");
	@unlink("$DAEMON_PATH/mods-enabled/dav_fs_module.load");
	@unlink("$DAEMON_PATH/mods-enabled/pagespeed.load");	
	
	$sock=new sockets();
	$FreeWebsDisableMOdQOS=$sock->GET_INFO("FreeWebsDisableMOdQOS");
	if(!is_numeric($FreeWebsDisableMOdQOS)){$FreeWebsDisableMOdQOS=0;}
	if($FreeWebsEnableModQOS==0){$FreeWebsDisableMOdQOS=1;}
	
	
	if($FreeWebsDisableMOdQOS==1){
		unset($array["qos_module"]);
		@unlink("$DAEMON_PATH/mods-enabled/qos_module.load");
	}
	
if($FreeWebsEnableModSecurity==1){
		if(is_file("$APACHE_MODULES_PATH/mod_security2.so")){
			$a[]="LoadFile /usr/lib/libxml2.so.2";
			$a[]="LoadModule security2_module $APACHE_MODULES_PATH/mod_security2.so";
			echo "Starting......: Apache module 'mod_security2' enabled\n";
			@file_put_contents("$DAEMON_PATH/mods-enabled/mod_security.load",@implode("\n",$a));
			unset($a);
		}else{
			echo "Starting......: Apache $APACHE_MODULES_PATH/mod_security2.so no such file\n";
		}
	}else{echo "Starting......: Apache module 'mod_security2' disabled\n";}
	
if($FreeWebsEnableModEvasive==1){
		if(is_file("$APACHE_MODULES_PATH/mod_evasive20.so")){
			$a[]="LoadModule evasive20_module $APACHE_MODULES_PATH/mod_evasive20.so";
			echo "Starting......: Apache module 'mod_evasive2' enabled\n";
			@file_put_contents("$DAEMON_PATH/mods-enabled/mod_evasive.load",@implode("\n",$a));
		}else{
			echo "Starting......: Apache $APACHE_MODULES_PATH/mod_evasive20.so no such file\n";
		}
	}else{echo "Starting......: Apache module 'mod_evasive2' disabled\n";}


	$sql="SELECT COUNT(servername) as tcount FROM freeweb WHERE UseReverseProxy=1";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
		$proxys_mods["proxy_module"]="mod_proxy.so";
		$proxys_mods["proxy_http_module"]="mod_proxy_http.so";
		$proxys_mods["proxy_ftp_module"]="mod_proxy_ftp.so";
		$proxys_mods["proxy_connect_module"]="mod_proxy_connect.so";
		$proxys_mods["headers_module"]="mod_headers.so";
		$proxys_mods["deflate_module"]="mod_deflate.so";
		$proxys_mods["xml2enc_module"]="mod_xml2enc.so";
		$proxys_mods["proxy_html_module"]="mod_proxy_html.so";
		
		$proxys_orgs[]="proxy_ajp.load";  
		$proxys_orgs[]="proxy_balancer.load";   
		$proxys_orgs[]="proxy.conf";   
		$proxys_orgs[]="proxy_connect.load";   
		$proxys_orgs[]="proxy_ftp.load";   
		$proxys_orgs[]="proxy_html.conf";  
		$proxys_orgs[]="proxy_html.load";   
		$proxys_orgs[]="proxy_http.load";   
		$proxys_orgs[]="proxy.load";   
		$proxys_orgs[]="proxy_scgi.load"; 
		
		if(is_file("/etc/httpd/conf.d/proxy_ajp.conf")){@unlink("/etc/httpd/conf.d/proxy_ajp.conf");}
		
		while (list ($module, $lib) = each ($proxys_orgs) ){if(is_file("$DAEMON_PATH/mods-enabled/$lib")){@unlink("$DAEMON_PATH/mods-enabled/$lib");}}
		while (list ($module, $lib) = each ($proxys_mods) ){if(is_file("$DAEMON_PATH/mods-enabled/$module.load")){@unlink("$DAEMON_PATH/mods-enabled/$module.load");}}
			
	echo "Starting......: Apache {$ligne["tcount"]} reverse proxy(s)\n";
	$countDeProxy=$ligne["tcount"];
	if($FreeWebsEnableOpenVPNProxy==1){if($FreeWebsOpenVPNRemotPort<>null){$countDeProxy=$countDeProxy+1;}}
	
	
	if($countDeProxy>0){
		reset($proxys_mods);
		while (list ($module, $lib) = each ($proxys_mods) ){
			if(!is_file("$APACHE_MODULES_PATH/$lib")){echo "Starting......: Apache module '$module' '$lib' no such file\n";continue;}
			echo "Starting......: Apache module '$module' enabled\n";
			$final_proxys[]="LoadModule $module $APACHE_MODULES_PATH/$lib";
		}
		
		@file_put_contents("$DAEMON_PATH/mods-enabled/proxy_module.load", @implode("\n", $final_proxys));
	}		
	
	
	while (list ($module, $lib) = each ($array) ){
		if(!is_file("$APACHE_MODULES_PATH/$lib")){echo "Starting......: Apache module '$module' '$lib' no such file\n";continue;}
		echo "Starting......: Apache module '$module' enabled\n";
		@file_put_contents("$DAEMON_PATH/mods-enabled/$module.load","LoadModule $module $APACHE_MODULES_PATH/$lib");
		
	}
		
}	



function apache_security($DAEMON_PATH){
	$sock=new sockets();
	$unix=new unix();
	if(!is_dir("/var/cache/apache2/mod_pagespeed")){@mkdir("/var/cache/apache2/mod_pagespeed",0755,true);}
	if(!is_dir("/etc/apache2/logs")){@mkdir("/etc/apache2/logs",0755,true);}
	$APACHE_SRC_ACCOUNT=$unix->APACHE_SRC_ACCOUNT();
	$APACHE_SRC_GROUP=$unix->APACHE_SRC_GROUP();
	shell_exec("/bin/chown $APACHE_SRC_ACCOUNT:$APACHE_SRC_GROUP /var/www");
	shell_exec("/bin/chown -R $APACHE_SRC_ACCOUNT:$APACHE_SRC_GROUP /etc/apache2");
	shell_exec("/bin/chown -R $APACHE_SRC_ACCOUNT:$APACHE_SRC_GROUP /var/cache/apache2");
	
	shell_exec("/bin/chmod 755 /var/www");
	shell_exec("/bin/chmod 755 /var/cache/apache2");
	shell_exec("/bin/chmod 755 /etc/apache2");
	
	$ApacheServerTokens=$sock->GET_INFO("ApacheServerTokens");
	$ApacheServerSignature=$sock->GET_INFO("ApacheServerSignature");
	if(!is_numeric($ApacheServerSignature)){$ApacheServerSignature=1;}
	if($ApacheServerTokens==null){$ApacheServerTokens="Full";}	
	if($ApacheServerSignature==1){$ServerSignature="On";}else{$ServerSignature="Off";}
	
	$httpd[]="ServerTokens $ApacheServerTokens";
	$httpd[]="ServerSignature $ServerSignature";
	$httpd[]="";
	@file_put_contents("$DAEMON_PATH/security",@implode("\n",$httpd));
	
}


function EnableMods(){
	if(is_file("/etc/apache2/mods-available/ssl.load")){
		shell_exec("/bin/ln -s /etc/apache2/mods-available/ssl.load /etc/apache2/mods-enabled/ssl.load >/dev/null 2>&1");
	}
	if(is_file("/etc/apache2/mods-available/ssl.conf")){
		shell_exec("/bin/ln -s /etc/apache2/mods-available/ssl.conf /etc/apache2/mods-enabled/ssl.conf >/dev/null 2>&1");
	}	
}

function CheckLibraries(){
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$GLOBALS["CLASS_USERS_MENUS"]=new usersMenus();}
	if(!isset($GLOBALS["CLASS_SOCKETS"])){$GLOBALS["CLASS_SOCKETS"]=new sockets();}
	if(!isset($GLOBALS["CLASS_LDAP"])){$GLOBALS["CLASS_LDAP"]=new clladp();}
}

function buildHost($uid=null,$hostname,$ssl=null,$d_path=null,$Params=array()){
	echo "Starting......: Apache building \"$hostname\"\n";
	create_cron_task();
	CheckLibraries();
	$unix=$GLOBALS["CLASS_UNIX"];
	$sock=$GLOBALS["CLASS_SOCKETS"];
	$users=$GLOBALS["CLASS_USERS_MENUS"];
	$AuthLDAP=0;$mod_pagespedd=null;
	$EnableLDAPAllSubDirectories=0;
	$APACHE_MOD_AUTHNZ_LDAP=$users->APACHE_MOD_AUTHNZ_LDAP;
	$APACHE_MOD_PAGESPEED=$users->APACHE_MOD_PAGESPEED;
	$freeweb=new freeweb($hostname);
	$Params=$freeweb->Params;
	if($freeweb->servername==null){
		echo "Starting......: Apache \"$hostname\" freeweb->servername no such servername \n";
		return;
	
	}
	
	$FreeWebsEnableOpenVPNProxy=$sock->GET_INFO("FreeWebsEnableOpenVPNProxy");
	$FreeWebsOpenVPNRemotPort=trim($sock->GET_INFO("FreeWebsOpenVPNRemotPort"));
	$FreeWebDisableSSL=trim($sock->GET_INFO("FreeWebDisableSSL"));

	if(!is_numeric($FreeWebsEnableOpenVPNProxy)){$FreeWebsEnableOpenVPNProxy=0;}
	if(!is_numeric($FreeWebDisableSSL)){$FreeWebDisableSSL=0;}
	if($FreeWebDisableSSL==1){
		if($freeweb->SSL_enabled){echo "Starting......: Apache \"$hostname\" SSL is globally disabled \n";}
		$freeweb->SSL_enabled=false;
	}

	
	$d_path=$freeweb->APACHE_DIR_SITES_ENABLED;
	
	
	if(isset($Params["LDAP"]["enabled"])){$AuthLDAP=$Params["LDAP"]["enabled"];}
	if(isset($Params["LDAP"]["EnableLDAPAllSubDirectories"])){$EnableLDAPAllSubDirectories=$Params["LDAP"]["EnableLDAPAllSubDirectories"];}

	
	//server signature.
	if(!isset($Params["SECURITY"])){$Params["SECURITY"]["ServerSignature"]=null;}
	$ServerSignature=$Params["SECURITY"]["ServerSignature"];
	if($ServerSignature==null){$ServerSignature=$sock->GET_INFO("ApacheServerSignature");}
	if(!is_numeric($ServerSignature)){$ServerSignature=1;}
	if($ServerSignature==1){$ServerSignature="On";}else{$ServerSignature="Off";}
	
	
	
	
	if(!$APACHE_MOD_AUTHNZ_LDAP){$AuthLDAP=0;}
	
	$apache_usr=$unix->APACHE_SRC_ACCOUNT();
	$apache_group=$unix->APACHE_SRC_GROUP();
	$FreeWebListen=$sock->GET_INFO("FreeWebListen");
	$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
	$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");
	$FreeWebListen=$sock->GET_INFO("FreeWebListen");
	$FreeWebsDisableSSLv2=$sock->GET_INFO("FreeWebsDisableSSLv2");
	
	
	if($FreeWebListen==null){$FreeWebListen="*";}
	if($FreeWebListen<>"*"){$FreeWebListenApache="$FreeWebListen";}	
	if($FreeWebListenSSLPort==null){$FreeWebListenSSLPort=443;}
	
	if(!is_numeric($FreeWebListenSSLPort)){$FreeWebListenSSLPort=443;}
	if(!is_numeric($FreeWebListenPort)){$FreeWebListenPort=80;}
	if(!is_numeric($FreeWebsDisableSSLv2)){$FreeWebsDisableSSLv2=0;}		

	$port=$FreeWebListen;
	if($uid<>null){
		$u=new user($uid);
		$ServerAdmin=$u->mail;
	}
	if(!isset($ServerAdmin)){$ServerAdmin="webmaster@$hostname";}
	$DirectoryIndex=$freeweb->DirectoryIndex();
	if($hostname=="_default_"){$FreeWebListen="_default_";}
	
	
	if($freeweb->SSL_enabled){
		$unix->vhosts_BuildCertificate($hostname);
		$port=$FreeWebListenSSLPort;
		if($freeweb->ServerPort>0){$FreeWebListenPort=$freeweb->ServerPort;}
		$conf[]="<VirtualHost $FreeWebListen:$FreeWebListenPort>";
		if($hostname<>"_default_"){$conf[]="\tServerName $hostname";}
		$conf[]="\tServerSignature $ServerSignature";
		$conf[]="\tRewriteEngine On";
		if($freeweb->Forwarder==0){$conf[]="\tRewriteCond %{HTTPS} off";}
		if($freeweb->Forwarder==0){$conf[]="\tRewriteRule (.*) https://%{HTTP_HOST}:$FreeWebListenSSLPort";}
		if($freeweb->Forwarder==1){$conf[]="\tRewriteRule (.*) $freeweb->ForwardTo";}
		$conf[]="</VirtualHost>";
		$conf[]="";
		$FreeWebListenPort=$FreeWebListenSSLPort;
	}
	
	$freeweb->CheckDefaultPage();
	$freeweb->CheckWorkingDirectory();
	$ServerAlias=$freeweb->ServerAlias();
	if($freeweb->ServerPort>0){$FreeWebListenPort=$freeweb->ServerPort;}
	echo "Starting......: Apache \"$hostname\" Listen $FreeWebListen:$FreeWebListenPort\n";
	echo "Starting......: Apache \"$hostname\" Directory $freeweb->WORKING_DIRECTORY\n";
	$conf[]="<VirtualHost $FreeWebListen:$FreeWebListenPort>";
	
	
	
	if($freeweb->SSL_enabled){
		$conf[]="\tSetEnvIf User-Agent \".*MSIE.*\" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0";
		$conf[]="\tSSLEngine on";
		$conf[]="\tSSLCertificateFile {$GLOBALS["SSLKEY_PATH"]}/$hostname.crt";
		$conf[]="\tSSLCertificateKeyFile {$GLOBALS["SSLKEY_PATH"]}/$hostname.key";	
		if($FreeWebsDisableSSLv2==1){
			$conf[]="\tSSLProtocol -ALL +SSLv3 +TLSv1";
			$conf[]="\tSSLCipherSuite ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:RC4+RSA:+HIGH:+MEDIUM";
		}			
		
	}
	
	
	
	if($hostname<>"_default_"){
		$conf[]="\tServerName $hostname";
		if($ServerAlias<>null){$conf[]=$ServerAlias;}
		$sock=new sockets();
		$FreeWebsEnableOpenVPNProxy=$sock->GET_INFO("FreeWebsEnableOpenVPNProxy");
		$FreeWebsOpenVPNRemotPort=trim($sock->GET_INFO("FreeWebsOpenVPNRemotPort"));
		if(!is_numeric($FreeWebsEnableOpenVPNProxy)){$FreeWebsEnableOpenVPNProxy=0;}
		if(!is_numeric($FreeWebsOpenVPNRemotPort)){$FreeWebsOpenVPNRemotPort=0;}
		if($FreeWebsEnableOpenVPNProxy==1){
			if($FreeWebsOpenVPNRemotPort>0){
				$conf[]="\tProxyRequests On";
				$conf[]="\tProxyVia On";
				$conf[]="\tAllowCONNECT 1194";
				$conf[]="\tKeepAlive On";
			}
		}
	}
		$php_open_base_dir=$freeweb->open_basedir();
		$geoip=$freeweb->mod_geoip();
		$mod_status=$freeweb->mod_status();
		$mod_evasive=$freeweb->mod_evasive();
		$Charsets=$freeweb->Charsets();
		$php_values=$freeweb->php_values();
		$WebdavHeader=$freeweb->WebdavHeader();
		$QUOS=$freeweb->QUOS();
		$Aliases=$freeweb->Aliases();
		$mod_cache=$freeweb->mod_cache();
		$mod_fcgid=$freeweb->mod_fcgid();
		$RewriteEngine=$freeweb->RewriteEngine();
		
		if($APACHE_MOD_PAGESPEED){$mod_pagespedd=$freeweb->mod_pagespeed();}
		$conf[]="\tServerAdmin $ServerAdmin";
		$conf[]="\tServerSignature $ServerSignature";
		$conf[]="\tDocumentRoot $freeweb->WORKING_DIRECTORY";
		
		if($mod_evasive<>null){  $conf[]=$mod_evasive;}
		if($Charsets<>null){     $conf[]=$Charsets;}
		if($php_values<>null){   $conf[]=$php_values;}
		if($WebdavHeader<>null){ $conf[]=$WebdavHeader;}
		if($QUOS<>null){	     $conf[]=$QUOS;}
		if($QUOS<>null){	     $conf[]=$QUOS;}
		if($Aliases<>null){	     $conf[]=$Aliases;}
		if($mod_cache<>null){	 $conf[]=$mod_cache;}
		if($geoip<>null){	     $conf[]=$geoip;}
		if($mod_pagespedd<>null){$conf[]=$mod_pagespedd;shell_exec("/bin/chown -R $apache_usr:$apache_group /var/cache/apache2/mod_pagespeed/$hostname");}
		if($mod_status<>null){   $conf[]=$mod_status;}
		
		
		
		if($RewriteEngine<>null){$conf[]=$RewriteEngine;}
		
		$ldapRule=null;
		
			if($freeweb->groupware=="ZARAFA"){
				$ZarafaWebNTLM=$sock->GET_INFO("ZarafaWebNTLM");	
				if(!is_numeric($ZarafaWebNTLM)){$ZarafaWebNTLM=0;}
				if($ZarafaWebNTLM==1){$AuthLDAP=1;}
			}		
		
		
		if($AuthLDAP==1){
			echo "Starting......: Apache \"$hostname\" ldap authentication enabled\n";
			$ldap=$GLOBALS["CLASS_LDAP"];
			$dn_master_branch="dc=organizations,$ldap->suffix";
			if($uid<>null){
				$usr=new user($uid);
				$dn_master_branch="ou=users,ou=$usr->ou,dc=organizations,$ldap->suffix";
			}
			
		    $ldapAuth[]="\t\tAuthName \"". base64_decode($Params["LDAP"]["authentication_banner"])."\"";
		    $ldapAuth[]="\t\tAuthType Basic";
		    $ldapAuth[]="\t\tAuthLDAPURL ldap://$ldap->ldap_host:$ldap->ldap_port/$dn_master_branch?uid";
		   	$ldapAuth[]="\t\tAuthLDAPBindDN cn=$ldap->ldap_admin,$ldap->suffix";
		   	$ldapAuth[]="\t\tAuthLDAPBindPassword $ldap->ldap_password";
			$ldapAuth[]="\t\tAuthLDAPGroupAttribute memberUid";
			$ldapAuth[]="\t\tAuthBasicProvider ldap";
		    $ldapAuth[]="\t\tAuthzLDAPAuthoritative off";
		    $AuthUsers=$freeweb->AuthUsers();
		    if($AuthUsers<>null){$ldapAuth[]=$AuthUsers;}else{$ldapAuth[]="\t\trequire valid-user";}	
		    $ldapAuth[]="";	
		    $ldapRule=@implode("\n", $ldapAuth);
		}		
	
	
	//DIRECTORY
	$OptionExecCGI=null;
	$allowFrom=$freeweb->AllowFrom();
	$JkMount=$freeweb->JkMount();	
	if($JkMount<>null){$conf[]=$JkMount;}
	$WebDav=$freeweb->WebDav();
	$AllowOverride=$freeweb->AllowOverride();
	$mod_rewrite=$freeweb->mod_rewrite();
	if($mod_fcgid<>null){$OptionExecCGI=" +ExecCGI";}
	
	
		$conf[]="\n\t<Directory \"$freeweb->WORKING_DIRECTORY/\">";
			$conf[]="\t\tDirectoryIndex $DirectoryIndex";
	    	$conf[]="\t\tOptions Indexes +FollowSymLinks MultiViews$OptionExecCGI";
	    	$conf[]="\t\tAllowOverride All";
	    	if($WebDav<>null){$conf[]=$WebDav;}
			if($AllowOverride<>null){$conf[]=$AllowOverride;}
			$conf[]="\t\tOrder allow,deny";
			if($allowFrom<>null){$conf[]=$allowFrom;}
			if($geoip<>null){$conf[]="\t\tDeny from env=BlockCountry";}
			if($mod_rewrite<>null){$conf[]=$mod_rewrite;}
			if($ldapRule<>null){$conf[]=$ldapRule;}
		$conf[]="\t</Directory>\n";
	
 if($mod_fcgid<>null){    $conf[]=$mod_fcgid;}
	
	
	if($freeweb->UseReverseProxy==1){
	
		$conf[]=$freeweb->ReverseProxy();
		$conf[]="\t<Proxy *>";
			$conf[]="\t\tOrder allow,deny";
			$conf[]=$freeweb->AllowFrom();		
			if($AuthLDAP==1){
				echo "Starting......: Apache \"$hostname\" ldap authentication enabled\n";
				$ldap=$GLOBALS["CLASS_LDAP"];
				$dn_master_branch="dc=organizations,$ldap->suffix";
				if($uid<>null){
					$usr=new user($uid);
					$dn_master_branch="ou=users,ou=$usr->ou,dc=organizations,$ldap->suffix";
				}
				$conf[]="";
			    $conf[]="\t\tAuthName \"". base64_decode($Params["LDAP"]["authentication_banner"])."\"";
			    $conf[]="\t\tAuthType Basic";
			    $conf[]="\t\tAuthLDAPURL ldap://$ldap->ldap_host:$ldap->ldap_port/$dn_master_branch?uid";
			   	$conf[]="\t\tAuthLDAPBindDN cn=$ldap->ldap_admin,$ldap->suffix";
			   	$conf[]="\t\tAuthLDAPBindPassword $ldap->ldap_password";
			   	$conf[]="\t\tAuthLDAPGroupAttributeIsDN off";
			   	$conf[]="\t\tAuthLDAPGroupAttribute memberUid";
			    $conf[]="\t\tAuthBasicProvider ldap";
			    $conf[]="\t\tAuthzLDAPAuthoritative off";
		    	$AuthUsers=$freeweb->AuthUsers();
		    	if($AuthUsers<>null){$conf[]=$AuthUsers;}else{$conf[]="\t\trequire valid-user";}	
			    $conf[]="";	
		}
		$conf[]="\t</Proxy>";
	
	}
	$conf[]=$freeweb->FilesRestrictions();	
	$conf[]=$freeweb->mod_security();
	
	
	if(!is_dir("/var/log/apache2/$hostname")){@mkdir("/var/log/apache2/$hostname",755,true);}
	$conf[]=$freeweb->ScriptAliases();
	$conf[]="\tLogFormat \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %V\" combinedv";
	$conf[]="\tCustomLog /var/log/apache2/$hostname/access.log combinedv";
	$conf[]="\tErrorLog /var/log/apache2/$hostname/error.log";
	$conf[]="\tLogLevel warn";
	$conf[]="</VirtualHost>";
	$conf[]="";
	
	
	
	$prefix_filename="artica-";
	$suffix_filename=".conf";
	$middle_filename=$hostname;
	if($hostname=="_default_"){$prefix_filename="000-";$middle_filename="default";$suffix_filename=null;}
	
	if($GLOBALS["VERBOSE"]){
		echo "Starting......: Apache saving *** $d_path/$prefix_filename$middle_filename$suffix_filename *** line ".__LINE__."\n";
	}
	
	
	@file_put_contents("$d_path/$prefix_filename$middle_filename$suffix_filename",@implode("\n",$conf));
	echo "Starting......: Apache \"$hostname\" filename: '". basename("$d_path/$prefix_filename$middle_filename$suffix_filename")."' done\n";
	$freeweb->phpmyadmin();
	@mkdir("$freeweb->WORKING_DIRECTORY",666,true);
	
	if($freeweb->groupware=="EYEOS"){install_EYEOS($hostname);}
	if($freeweb->groupware=="GROUPOFFICE"){group_office_install($hostname,true);}
	if($freeweb->groupware=="PIWIK"){install_PIWIK($hostname,true);}
	if($freeweb->groupware=="DRUPAL"){
		$unix=new unix();
		$nohup=$unix->find_program("nohup");
		shell_exec("$nohup ". $unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-infos \"$hostname\" >/dev/null 2>&1 &");
	}
	
}

function remove_host($hostname){
	$freeweb=new freeweb($hostname);
	if(is_dir("/var/www/$hostname")){shell_exec("/bin/rm -rf /var/www/$hostname");}
	if($freeweb->IsGroupWareFromArtica()){
		$freeweb->delete();
		return;
	}
	
	$mysql_database=$freeweb->mysql_database;
	$q=new mysql();
	if($q->DATABASE_EXISTS($mysql_database)){$q->DELETE_DATABASE($mysql_database);}
	
	
	
	if($freeweb->groupware=="Z-PUSH"){$freeweb->delete();return;}
	if($freeweb->groupware=="POWERADMIN"){$freeweb->delete();return;}
	if($hostname=="_default_"){$freeweb->delete();return;}
	if($freeweb->Forwarder==0){$freeweb->delete();return;}
	
	if(is_dir($freeweb->WORKING_DIRECTORY)){shell_exec("/bin/rm -rf $freeweb->WORKING_DIRECTORY");}
	$freeweb->delete();
	
}

function FDpermissions($servername=null){
	$servername=trim($servername);
	if($servername<>null){
		$pidfile="/usr/share/artica-postfix/pids/" .basename(__FILE__).".".__FUNCTION__.".$servername.pid";
		$sqq=" AND servername='$servername'";
		
	}else{
		$pidfile="/usr/share/artica-postfix/pids/" .basename(__FILE__).".".__FUNCTION__.".pid";
	}
	$unix=new unix();
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		echo "Already exists $oldpid\n";
		return;
	}
	@file_put_contents($pidfile,getmypid());
	
	
	if($GLOBALS["VERBOSE"]){echo "\n";}
	
	$alreadydir=array();
	$alreadyFiles=array();
	$sql="SELECT servername,EnbaleFDPermissions,FDPermissions FROM freeweb WHERE EnbaleFDPermissions=1$sqq";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	$count=mysql_num_rows($results);
	echo "Starting......: Apache checking permission web sites count:$count\n";
	if($count==0){return;}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));	
		if(!is_numeric($FDPermissions["SCHEDULE"])){$FDPermissions["SCHEDULE"]=60;}
		$servername=$ligne["servername"];
		if(!is_array($FDPermissions)){continue;}
		$timefile="/usr/share/artica-postfix/pids/" .basename(__FILE__).".".__FUNCTION__.".$servername.time";
		if(!$GLOBALS["FORCE"]){
			$time=$unix->file_time_min($timefile);
			if($GLOBALS["VERBOSE"]){echo "$servername::Timefile: $timefile -> $time minutes/{$FDPermissions["SCHEDULE"]} minutes\n";}
			if($time<$FDPermissions["SCHEDULE"]){
				if($GLOBALS["VERBOSE"]){echo "$servername::Timefile: -> NEXT;\n";}
				continue;
			}
		}
		
		
		@unlink($timefile);
		@file_put_contents($timefile,"#");
		$freeweb=new freeweb($servername);
		$basePath=$freeweb->WORKING_DIRECTORY;
		if($GLOBALS["VERBOSE"]){echo "$servername::WORKING_DIRECTORY -> $basePath\n";}
		while (list ($index, $array) = each ($FDPermissions["PERMS"])){
		
			$ruleid=$index;
			$array["directory"]=trim($array["directory"]);
			if(substr($array["directory"],strlen($array["directory"]),1)=='/'){$array["directory"]=substr($array["directory"],0,strlen($array["directory"])-1);}
			$array["directory"]=str_replace("./","",$array["directory"]);
			$array["directory"]=str_replace("../","",$array["directory"]);
			if(trim($array["directory"])==null){$array["directory"]=$basePath;}else{$array["directory"]="$basePath/{$array["directory"]}";}
			if(!is_dir($array["directory"])){
				if($GLOBALS["VERBOSE"]){echo "$servername::{$array["directory"]} -> no such directory\n";}
				continue;
			}
			
			if($array["ext"]==null){$array["ext"]="*";}		
			$array["ext"]=str_replace("*.","",$array["ext"]);
			$array["ext"]=str_replace(".","",$array["ext"]);
			
			if(!is_numeric($array["chmoddir"])){$array["chmoddir"]="2570";}
			if(!is_numeric($array["chmodfile"])){$array["chmodfile"]="0460";}
			
			
			if(!isset($alreadydir[$array["directory"]])){
				if($GLOBALS["VERBOSE"]){echo "$servername::{$array["directory"]} -> chmod({$array["chmoddir"]})\n";}
				chmod_directories($array["directory"],$array["chmoddir"]);
			}
			
			if(!isset($alreadyFiles["{$array["directory"]}/*.{$array["ext"]}"])){
				if(strpos($array["ext"],",")>0){
						$newExts=@explode(",",$array["ext"]);
						while (list ($i, $ext2) = each ($newExts)){
							if($GLOBALS["VERBOSE"]){echo "$servername::{$array["directory"]}/*.$ext2 -> chmod({$array["chmodfile"]})\n";}
							chmod_files($array["directory"],$ext2,$array["chmodfile"]);
							$alreadyFiles["{$array["directory"]}/*.$ext2"]=true;
						}
				}else{
					if($GLOBALS["VERBOSE"]){echo "$servername::{$array["directory"]}/*.{$array["ext"]} -> chmod({$array["chmodfile"]})\n";}
					chmod_files($array["directory"],$array["ext"],$array["chmodfile"]);
					$alreadyFiles["{$array["directory"]}/*.{$array["ext"]}"]=true;
				}
			}
			$alreadydir[$array["directory"]]=true;
			
		
		}
		
	}
}	


function chmod_directories($path, $filemode=755) {
    
	if(!is_dir($path)){return;}
	if($GLOBALS["VERBOSE"]){echo "DIR: $path -> chmod:$filemode\n";}
	chmod($path,$filemode);
    $dh = opendir($path);
    while (($file = readdir($dh)) !== false) {
        if($file != '.' && $file != '..') {
        	$fullpath = $path.'/'.$file;
        	if(!is_dir($fullpath)){continue;}
        	if(is_link($fullpath)){continue;}
        	if(is_file($fullpath)){continue;}
        	if($GLOBALS["VERBOSE"]){echo "DIR: $fullpath -> chmod:$filemode\n";}
        	shell_exec("/bin/chmod $filemode $fullpath");
        	chmod_directories($fullpath,$filemode);
          }
    }

    closedir($dh);
	return TRUE;
	
    
}
function chmod_files($path, $ext="*",$filemode=755) {
    if (!is_dir($path)){
    	if(is_link($path)){return;}
    	if(is_file($path)){
    		$info=pathinfo($path);
    		if($ext<>"*"){
            	if(!isset($info["extension"])){return;}
            	if(strtolower($ext)==$info["extension"]){
            		if($GLOBALS["VERBOSE"]){echo "FILE:".__LINE__.":$ext $path -> chmod:$filemode\n";}
            		shell_exec("/bin/chmod $filemode $path");
            		return;
            	}
            	
            }else{
            	if($GLOBALS["VERBOSE"]){echo "FILE:".__LINE__.":$ext $path -> chmod:$filemode\n";}
            	shell_exec("/bin/chmod $filemode $path");
            	return;
            }
    	}
    return;}

    $dh = opendir($path);
    while (($file = readdir($dh)) !== false) {
        if($file != '.' && $file != '..') {
        	
            $fullpath = $path.'/'.$file;
        	if(is_dir($fullpath)){
        		if($GLOBALS["VERBOSE"]){echo "chmod_files($fullpath,$ext,$filemode);\n";}
        		chmod_files($fullpath,$ext,$filemode);
        		continue;
        	}
        	
            
            if($ext=="*"){
            	if(!is_file($fullpath)){continue;}
            	if($GLOBALS["VERBOSE"]){echo "FILE:".__LINE__.":$ext $fullpath -> chmod:$filemode (*)\n";}
            	shell_exec("/bin/chmod $filemode $fullpath");
            	
            	continue;
            }
            
            
            
            if(is_link($fullpath)){continue;}
           	if(is_file($fullpath)){
           		if(!preg_match("#.+?\.(.+?)$#",basename($fullpath),$re)){continue;}
           		$extr=$re[1];
           		if($ext<>$extr){continue;}
           		if($GLOBALS["VERBOSE"]){echo "FILE:".__LINE__.":$ext $fullpath -> chmod:$filemode ($extr)\n";}
           		shell_exec("/bin/chmod $filemode $fullpath");
				continue;
           	}     
           	
            
           	
        }
    }

    closedir($dh);

}

function CheckFailedStart(){
	$unix=new unix();
	$sock=new sockets();
	$apache2ctl=$unix->find_program("apache2ctl");
	if(!is_file($apache2ctl)){$apache2ctl=$unix->find_program("apachectl");}
	if(!is_file($apache2ctl)){echo "Starting......: Apache apache2ctl no such file\n";}
	exec("$apache2ctl -k start 2>&1",$results);
	while (list ($index, $line) = each ($results)){
		
		if(preg_match("#Cannot load .+?mod_qos\.so#", $line)){
			echo "Starting......: Apache error on qos module, disable it..\n";
			echo "Starting......: Apache error \"$line\"\n";
			$sock->SET_INFO("FreeWebsDisableMOdQOS",1);
			CheckHttpdConf();
			$unix->send_email_events("FreeWebs: QOS is disabled, cannot be loaded on your server","Apache claim $line,using this module is disabled","system");
			shell_exec("/etc/init.d/artica-postfix start apachesrc --no-repair");
			return;
		}
		
		if(preg_match("#Could not open configuration file (.+?)sites-enabled#",$line,$re)){
			echo "Starting......: Apache error {$re[1]}/sites-enabled\n";
			echo "Starting......: Apache error \"$line\"\n";
			$apacheusername=$unix->APACHE_SRC_ACCOUNT();
			echo "Starting......: Apache creating directory {$re[1]}/sites-enabled\n";
			@mkdir("{$re[1]}/sites-enabled");
			
			echo "Starting......: Apache checking permissions on {$re[1]}/sites-enabled with user $apacheusername\n";
			@chown("{$re[1]}/sites-enabled",$apacheusername);
			@chmod("{$re[1]}/sites-enabled",755);
			shell_exec("/etc/init.d/artica-postfix start apachesrc --no-repair");
			return;
		}
		
	 echo "Starting......: Apache $line\n";	
	}
	
}

function install_groupware($servername,$rebuild=false){
	
	$free=new freeweb($servername);
	if($free->groupware==null){
		 writelogs("Starting......: Apache \"$servername\" no groupware set",__FUNCTION__,__FILE__,__LINE__);
		 return;
	}
	
	writelogs("Starting......: Apache \"$servername\" -> \"$free->groupware\"",__FUNCTION__,__FILE__,__LINE__);
	
	switch ($free->groupware) {
		case "ARTICA_USR":
			install_groupware_ARTICA_USR($servername);
			return;
		break;
		
		case "ARTICA_ADM":
			install_groupware_ARTICA_ADM($servername);
			return;
		break;
		case "EYEOS":
			install_EYEOS($servername);
			return;
		break;
		
		case "GROUPOFFICE":
			writelogs("group_office_install($servername,false,$rebuild)",__FUNCTION__,__FILE__,__LINE__);
			if($rebuild){buildHost(null,$servername);};
			group_office_install($servername,false,$rebuild);
		break;
		
		case "JOOMLA17":
			writelogs("install_JOOMLA17($servername)",__FUNCTION__,__FILE__,__LINE__);
			install_JOOMLA17($servername);
		break;

		case "WORDPRESS":
			writelogs("install_wordpress($servername)",__FUNCTION__,__FILE__,__LINE__);
			install_wordpress($servername);
		break;		
		
		
		
		default:
			;
		break;
	}
	
	
	
}

function install_groupware_ARTICA_USR($hostname){
	$sql="SELECT * FROM freeweb WHERE servername='$hostname'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	echo "Starting......: Apache \"$hostname\" Rebuilding host configuration file\n";	
	buildHost($ligne["uid"],$hostname);
	reload_apache();
	shell_exec("/bin/ln -s /usr/share/artica-postfix/ressources/settings.inc /usr/share/artica-postfix/user-backup/ressources/settings.inc >/dev/null 2>&1");
}

function install_groupware_ARTICA_ADM($hostname){
	$sql="SELECT * FROM freeweb WHERE servername='$hostname'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	echo "Starting......: Apache \"$hostname\" Rebuilding host configuration file\n";
	buildHost($ligne["uid"],$hostname);
	reload_apache();
}

function install_EYEOS($hostname){
	echo "Starting......: Apache \"$hostname\" Checking eyeOS installation....\n";
	
	$freeweb=new freeweb($hostname);
	$freeweb->CheckWorkingDirectory();
	
	
	echo "Starting......: Apache \"$hostname\" Checking eyeOS installation....\n";
	if(!is_file(dirname(__FILE__)."/ressources/class.eyeos.inc")){echo "Fatal ".dirname(__FILE__)."/ressources/class.eyeos.inc no such file\n";}
	include_once(dirname(__FILE__)."/ressources/class.eyeos.inc");
	$eye=new eyeos($hostname);

	if($eye->ValidateInstallation25()){
		echo "Starting......: Apache \"$hostname\" Installing EyeOS (already installed)\n";
		$eye->Build_SettingsPHP();
		return;
	}
	echo "Starting......: Apache \"$hostname\" Installing EyeOS in $freeweb->WORKING_DIRECTORY\n";
	$unix=new unix();
	$cp=$unix->find_program("cp");
	shell_exec("$cp -rf /usr/local/share/artica/eyeos_src/* $freeweb->WORKING_DIRECTORY/");
	if($eye->ValidateInstallation25($freeweb->WORKING_DIRECTORY)){
		echo "Starting......: Apache \"$hostname\" Installing EyeOS (FAILED)\n";
	}	
	
}

function resolv_servers(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".__FUNCTION__.".".__FILE__.".pid";
	$filetime="/etc/artica-postfix/pids/".__FUNCTION__.".".__FILE__.".time";
	$pid=$unix->get_pid_from_file($pidfile);
	if($unix->process_exists($pid)){return;}
	@file_put_contents($pidfile, getmypid());
	if(!$GLOBALS["FORCE"]){
		$time=$unix->file_time_min($filetime);
		if($time<30){return;}
	}
	
	@unlink($filetime);
	@file_put_contents($filetime, time());
	$nohup=$unix->find_program("nohup");
	$drupal_cron=trim("$nohup ". $unix->LOCATE_PHP5_BIN()." " .__FILE__." --drupal-cron >/dev/null 2>&1 &");
	shell_exec($drupal_cron);
	
	$sql="SELECT servername,resolved_ipaddr FROM freeweb ORDER BY servername";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "ERROR IN QUERY \"$q->mysql_error\"\n";}}
	if(preg_match("#Unknown column#", $q->mysql_error)){$q->BuildTables();$results=$q->QUERY_SQL($sql,'artica_backup');}
	
	$count=mysql_num_rows($results);
	
	if($count==0){return;}
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["servername"]=='_default_'){continue;}
		if($GLOBALS["VERBOSE"]){echo "check {$ligne["servername"]}\n";}
		$ipaddr=gethostbyname($ligne["servername"]);
		if($GLOBALS["VERBOSE"]){echo "$ipaddr\n";}
		if($ipaddr==null){
			$unix->send_email_events("FreeWeb: http(s)://{$ligne["servername"]} unable to resolve","Artica tried to resolve the {$ligne["servername"]}, no ip address is returned, so it's means that this website will be not available", "system");
			continue;
		}
		
		if($ipaddr==$ligne["servername"]){
			$unix->send_email_events("FreeWeb: http(s)://{$ligne["servername"]} unable to resolve","Artica tried to resolve the {$ligne["servername"]}, no ip address is returned, so it's means that this website will be not available", "system");
			$sql="UPDATE freeweb SET `resolved_ipaddr`='' WHERE servername='{$ligne["servername"]}'";
			$q->QUERY_SQL($sql,"artica_backup");			
			continue;
		}		
		
		if($ipaddr<>$ligne["resolved_ipaddr"]){
			$sql="UPDATE freeweb SET `resolved_ipaddr`='$ipaddr' WHERE servername='{$ligne["servername"]}'";
			$q->QUERY_SQL($sql,"artica_backup");
			$unix->send_email_events("FreeWeb: http(s)://{$ligne["servername"]} resolved to $ipaddr","Artica tried to resolve the {$ligne["servername"]}, old ip was [{$ligne["resolved_ipaddr"]}] new ip is $ipaddr", "system");
		}
		
	}	
	
}

function createdupal($servername){
	if($servername==null){return;}
	$f=new drupal_vhosts($servername);
	$f->install();
	
}

function drupal_infos($servername){
	if($servername==null){return;}
	$f=new drupal_vhosts($servername);
	$f->populate_infos();	
}
function drupal_add_user($uid,$servername){
	if($servername==null){return;}
	if($uid==null){return;}
	$f=new drupal_vhosts($servername);
	$f->add_user($uid);	
}

function drupal_deluser($uid,$servername){
	if($servername==null){return;}
	if($uid==null){return;}	
	$f=new drupal_vhosts($servername);
	$f->del_user($uid);		
}

function drupal_enuser($uid,$enable,$servername){
	if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$servername\" drupal_enuser() $uid enable->[$enable]\n";}
	if($servername==null){return;}
	if($uid==null){return;}	
	$f=new drupal_vhosts($servername);
	$f->active_user($uid,$enable);	
}

function drupal_privuser($uid,$priv,$servername){
	if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$servername\" drupal_privuser() $uid enable->[$priv]\n";}
	if($servername==null){return;}
	if($uid==null){return;}	
	$f=new drupal_vhosts($servername);
	$f->priv_user($uid,$priv);	
}

function drupal_dump_modules($servername){
	if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$servername\" drupal_dump_modules()\n";}
	if($servername==null){return;}
	$f=new drupal_vhosts($servername);
	$f->dump_modules();
	
}

function drupal_cron(){
	$users=new usersMenus();
	if(!$users->DRUPAL7_INSTALLED){die();}
	$pidtime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	$drush7=$unix->find_program("drush7");
	if(!is_file($drush7)){die();}
	if($unix->process_exists($oldpid,basename(__FILE__))){die();}
	if($unix->file_time_min($pidtime)<60){die();}
	@file_put_contents($pidfile, getmypid());
	@unlink($pidtime);
	@file_put_contents($pidtime, time());
	$sql="SELECT servername FROM freeweb WHERE groupware='DRUPAL'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	$count=mysql_num_rows($results);
	echo "Starting......: Apache checking drupal cron web sites count:$count\n";
	if($count==0){return;}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$dd=new drupal_vhosts($ligne["servername"]);
		$dd->install_modules();	
		shell_exec("$drush7 --root=$dd->www_dir cron >/dev/null 2>&1");
	}
}

function drupal_install_modules($servername){
	if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$servername\" drupal_install_modules()\n";}
	if($servername==null){return;}
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".$servername.pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	$drush7=$unix->find_program("drush7");
	if(!is_file($drush7)){die();}
	if($unix->process_exists($oldpid,basename(__FILE__))){die();}	
	@file_put_contents($pidfile, getmypid());
	
	$f=new drupal_vhosts($servername);
	$f->install_modules();	
}

function drupal_reinstall($servername){
	if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$servername\" drupal_install_modules()\n";}
	if($servername==null){return;}	
	$unix=new unix();
	$drush7=$unix->find_program("drush7");
	if(!is_file($drush7)){die();}	
	$f=new drupal_vhosts($servername);
	$f->DrushInstall();
}

function drupal_schedules(){
	$q=new mysql();
	$sql="SELECT * FROM drupal_queue_orders ORDER BY ID";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$uid=null;$password=null;$value=null;
		if($ligne["value"]<>null){$data=unserialize(base64_decode($ligne["value"]));}
		$order=$ligne["ORDER"];
		writelogs("order:{$ligne["ORDER"]} ID:{$ligne["ID"]}",__FUNCTION__,__FILE__,__LINE__);
		$servername=$ligne["servername"];
		if(isset($data["USER"])){$uid=$data["USER"];}
		if(isset($data["PASSWORD"])){$password=$data["USER"];}
		if(isset($data["value"])){$value=$data["value"];}
		$ID=$ligne["ID"];
		writelogs("order:$order servername:$servername (uid=$uid)",__FUNCTION__,__FILE__,__LINE__);
		
		switch ($order){
			
			case "REFRESH_INFOS":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				if($servername<>null){
					$f=new drupal_vhosts($servername);
					$f->populate_infos();
				}
			break;
			
			case "REFRESH_MODULES":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				if($servername<>null){
					$f=new drupal_vhosts($servername);
					$f->dump_modules();
					$f->install_modules();	
				}
			break;			
			
			
			
			case "DELETE_USER":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				if($servername<>null){
					$f=new drupal_vhosts($servername);
					$f->del_user($uid);	
				}
			break;	

			case "CREATE_USER":
				if($servername<>null){
					$f=new drupal_vhosts($servername);
					$f->add_user($uid,$password);	
				}
			break;	

			case "ENABLE_USER":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				if($servername<>null){
					$f=new drupal_vhosts($servername);
					$f->active_user($uid,$value);	
				}
			break;			

			case "PRIV_USER":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				writelogs("PRIV_USER: servername:$servername (uid=$uid, value=$value)",__FUNCTION__,__FILE__,__LINE__);
				if($servername<>null){
					$f=new drupal_vhosts($servername);
					$f->priv_user($uid,$value);	
				}
			break;	

			case "DELETE_FREEWEB":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				writelogs("DELETE_FREEWEB: servername:$servername (uid=$uid, value=$value)",__FUNCTION__,__FILE__,__LINE__);
				remove_host($servername);
				break;
				
			case "INSTALL_GROUPWARE":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");					
				writelogs("INSTALL_GROUPWARE: servername:$servername (uid=$uid, value=$value)",__FUNCTION__,__FILE__,__LINE__);
				install_groupware($servername);
				break;
				
			case "REBUILD_GROUPWARE":
				$sql="DELETE FROM drupal_queue_orders WHERE ID=$ID";
				$q->QUERY_SQL($sql,"artica_backup");				
				writelogs("INSTALL_GROUPWARE: servername:\"$servername\" (uid=$uid, value=$value)",__FUNCTION__,__FILE__,__LINE__);
				install_groupware($servername,true);
				break;				
			
		}
		

		
	}
		
	
}

function group_office_install($servername,$nobuildHost=false,$rebuild=false){
	$sources="/usr/local/share/artica/group-office";
	$unix=new unix();
	$cp=$unix->find_program("cp");
	$freeweb=new freeweb($servername);
	if(!is_dir($sources)){writelogs("[$servername] $sources no such directory",__FUNCTION__,__FILE__,__LINE__);return;}
	if(!is_dir($freeweb->WORKING_DIRECTORY)){writelogs("[$servername] $freeweb->WORKING_DIRECTORY no such directory",__FUNCTION__,__FILE__,__LINE__);return;}
	
	if(!is_file("$freeweb->WORKING_DIRECTORY/functions.inc.php")){$mustrebuild=true;}
	if(!$mustrebuild){$mustrebuild=$rebuild;}
	
	if($mustrebuild){
		writelogs("[$servername] copy sources...",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/bin/cp -rf $sources/* $freeweb->WORKING_DIRECTORY/");
		@file_put_contents("$freeweb->WORKING_DIRECTORY/config.php", "");
	}
	shell_exec("/bin/chmod 666 $freeweb->WORKING_DIRECTORY/config.php");
	
	$apacheusername=$unix->APACHE_SRC_ACCOUNT();
	$apachegroup=$unix->APACHE_SRC_GROUP();
	$freeweb->chown($freeweb->WORKING_DIRECTORY);
	if(!is_dir("/home/$servername")){@mkdir("/home/$servername");}
	include_once(dirname(__FILE__)."/ressources/class.group-office.php");
	$gpoffice=new group_office($servername);
	$gpoffice->www_dir=$freeweb->WORKING_DIRECTORY;
	$gpoffice->rebuildb=$rebuild;
	writelogs("[$servername] gpoffice->writeconfigfile() $freeweb->WORKING_DIRECTORY",__FUNCTION__,__FILE__,__LINE__);
	$gpoffice->writeconfigfile();
	
	$freeweb->chown("/home/$servername");

	
	
	
	//a la find chmod 644 /var/www/office.touzeau.com/group-office/config.php 
	
	if(!$nobuildHost){buildHost(null,$servername);}
	
	
}

function install_JOOMLA17($servername){
	include_once(dirname(__FILE__)."/ressources/class.joomla17.inc");
	$joom=new joomla17($servername);
	$joom->installsite();
	
}

function install_wordpress($servername){
	include_once(dirname(__FILE__)."/ressources/class.wordpress.inc");
	$word=new wordpress($servername);
	$word->CheckInstall();
}


function install_PIWIK($servername){
	$sources="/usr/share/piwik";
	$unix=new unix();
	$cp=$unix->find_program("cp");
	$freeweb=new freeweb($servername);	
	if(!is_dir($sources)){writelogs("[$servername] $sources no such directory",__FUNCTION__,__FILE__,__LINE__);return;}
	if(!is_dir($freeweb->WORKING_DIRECTORY)){writelogs("[$servername] $freeweb->WORKING_DIRECTORY no such directory",__FUNCTION__,__FILE__,__LINE__);return;}
	include_once(dirname(__FILE__)."/ressources/class.piwik.inc");
	$piwik=new piwik();
	if($piwik->checkWebsite($freeweb->WORKING_DIRECTORY)){return;}
	writelogs("[$servername] copy sources...",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cp -rf $sources/* $freeweb->WORKING_DIRECTORY/");
	@unlink("$freeweb->WORKING_DIRECTORY/config/config.ini.php");
	@mkdir('/usr/share/piwik/tmp/assets',0777,true);
    @mkdir('/usr/share/piwik/tmp/templates_c',0777,true);
    @mkdir('/usr/share/piwik/tmp/cache',0777,true);
    @mkdir('/usr/share/piwik/tmp/assets',0777,true);
    shell_exec('/bin/chmod 0777 /usr/share/piwik/tmp');
    shell_exec('/bin/chmod 0777 /usr/share/piwik/tmp/templates_c/');
    shell_exec('/bin/chmod 0777 /usr/share/piwik/tmp/cache/');
    shell_exec('/bin/chmod 0777 /usr/share/piwik/tmp/assets/');
    shell_exec('/bin/chmod a+w /usr/share/piwik/config'); 	
	$apacheusername=$unix->APACHE_SRC_ACCOUNT();
	$apachegroup=$unix->APACHE_SRC_GROUP();	
	$freeweb->chown($freeweb->WORKING_DIRECTORY);
	
	
}

function mod_status_htaccess($filename,$pattern){
	$exp=explode("\n", @file_get_contents("$filename"));
	while (list ($num, $ligne) = each ($exp) ){if(preg_match("#$pattern#",$ligne)){return;}}

	reset($exp);
	while (list ($num, $ligne) = each ($exp) ){	
		if(preg_match("#^RewriteRule#",$ligne)){
			if($GLOBALS["VERBOSE"]){echo "RewriteRule -> {$exp[$num]}\n";}
			$exp[$num]="RewriteCond %{REQUEST_URI} !$pattern\n".$exp[$num];
			@file_put_contents($filename, @implode("\n", $exp));
			return;
		}
	}	
	
}

function mod_status_all(){
	$unix=new unix();
	if(!$GLOBALS["VERBOSE"]){
		
		$pidfile="/etc/artica-postfix/".basename(__FILE__).".".__FUNCTION__.".pid";
		$pidtime="/etc/artica-postfix/".basename(__FILE__).".".__FUNCTION__.".time";
		if($unix->file_time_min($pidtime)<15){die();}
		$oldpid=@file_get_contents($pidfile);
		if($unix->process_exists($oldpid,basename(__FILE__))){return;}
		@unlink($pidtime);
		@file_put_contents($pidtime, time());
		@file_put_contents($pidfile, getmypid());
	}
	
	$table_name="apache_stats_".date('Ym');
	$q=new mysql();
	
	
	$sql="CREATE TABLE  IF NOT EXISTS `artica_events`.`$table_name` (
	`zDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	`servername` VARCHAR( 255 ) NOT NULL ,
	`UPTIME` VARCHAR( 255 ) NOT NULL ,
	`total_traffic` INT( 100 ) NOT NULL ,
	`total_memory` INT( 100 ) NOT NULL ,
	`requests_second` DOUBLE( 100, 2 ) NOT NULL ,
	`traffic_second` INT( 100 ) NOT NULL ,
	`traffic_request` INT( 100 ) NOT NULL ,
	 INDEX ( `zDate` , `total_traffic` , `total_memory` , `requests_second` , `traffic_second` , `traffic_request`),
	 KEY `servername` (`servername`))
	";
	$q->QUERY_SQL($sql,"artica_events");
	
	
	
	$sql="SELECT * FROM freeweb ORDER BY servername";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	
	$prefix="INSERT INTO $table_name (servername,total_traffic,total_memory,requests_second,traffic_second,traffic_request,`UPTIME` ) VALUES";
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$hostname=$ligne["servername"];
		if(trim($hostname)==null){continue;}
		mod_status($hostname);
	}

	if(count($GLOBALS["MODSTATUSQ"])==0){if($GLOBALS["VERBOSE"]){echo "No rows\n";}return;}
	$sql=$prefix.@implode(",", $GLOBALS["MODSTATUSQ"]);
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;}
}


function mod_status($servername){
	$servername=trim($servername);
	if($servername=="_default_"){return;}
	$freeweb=new freeweb($servername);
	$dir_www=$freeweb->WORKING_DIRECTORY;
	$unix=new unix();
	$q=new mysql();
	$pid=array();
	
	
	
	
	$dirMD=md5($servername);
	if($GLOBALS["VERBOSE"]){echo "Testing $dir_www/.htaccess\n";}
	if(is_file("$dir_www/.htaccess")){
	if($GLOBALS["VERBOSE"]){echo "mod_status_htaccess($dir_www/.htaccess,$dirMD)\n";}
		mod_status_htaccess("$dir_www/.htaccess",$dirMD);
		
	}

	
	
	$curl=new ccurl("http://$servername/$dirMD/$dirMD-status",true);
	$access=null;
	$total_traffic=null;
	$total_traffic_unit=null;
	$traffic_sec=0;
	$traffic_request=0;
	$request_s=0;
	$UPTIME=null;
	$total_mem=0;
	$datas=$curl->GetFile("/tmp/$servername.html");
	$datas=explode("\n",@file_get_contents("/tmp/$servername.html"));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#Server uptime:\s+(.+)#",$ligne,$re)){$UPTIME=trim($re[1]);continue;}
		if(preg_match("#Total accesses:\s+([0-9]+)\s+-\s+Total Traffic:\s+([0-9]+)\s+([a-zA-Z]+)#",$ligne,$re)){
			$access=$re[1];
			$total_traffic=$re[2];
			$total_traffic_unit=strtoupper($re[3]);
			if($total_traffic_unit=="KB"){$total_traffic=$total_traffic*1024;}
			if($total_traffic_unit=="MB"){$total_traffic=$total_traffic*1024000;}
			if($total_traffic_unit=="GB"){$total_traffic=$total_traffic*1024000000;}
			if($total_traffic_unit=="TB"){$total_traffic=$total_traffic*10240000000000;}
			continue;		
			
			
		}
		
		if(preg_match("#([0-9\.]+)\s+requests\/sec\s+-\s+([0-9]+)\s+(.+)\/second\s+-\s+([0-9]+)\s+(.+?)\/request#", $ligne,$re)){
			$request_s=$re[1];
			if(substr($request_s,0,1)=="."){$request_s="0$request_s";}
			$traffic_sec=$re[2];
			$traffic_sec_unit=strtoupper($re[3]);
			if($traffic_sec_unit=="KB"){$traffic_sec=$traffic_sec*1024;}
			if($traffic_sec_unit=="MB"){$traffic_sec=$traffic_sec*1024000;}
			if($traffic_sec_unit=="GB"){$traffic_sec=$traffic_sec*1024000000;}
			if($traffic_sec_unit=="TB"){$traffic_sec=$traffic_sec*10240000000000;}			
			
			
			$traffic_request=$re[4];
			$traffic_request_unit=strtoupper($re[5]);
			if($traffic_request_unit=="KB"){$traffic_request=$traffic_request*1024;}
			if($traffic_request_unit=="MB"){$traffic_request=$traffic_request*1024000;}
			if($traffic_request_unit=="GB"){$traffic_request=$traffic_request*1024000000;}
			if($traffic_request_unit=="TB"){$traffic_request=$traffic_request*10240000000000;}			
			continue;
		}
		
		if(preg_match("#<td><b>[0-9]+-[0-9]+</b></td><td>([0-9]+)</td><td>#", $ligne,$re)){
			$pid[$re[1]]=$re[1];
		}
		
		
		
	
		
	}
	
	if(count($pid)>0){
		while (list ($num, $ligne) = each ($pid) ){
		$mem=$unix->PROCESS_MEMORY($num,true)+$unix->PROCESS_CACHE_MEMORY($num,true);
		$total_mem=$total_mem+$mem;
		}	
	}
	
	if($GLOBALS["VERBOSE"]){
			echo "Access: $access total-traffic:$total_traffic bytes UPTIME=$UPTIME Total memory used: $total_mem Bytes\n";
			echo "Access: requests/seconds: $request_s traffic/sec:$traffic_sec trafic per request:$traffic_request bytes:\n";			

	}

	if(!is_numeric($total_traffic)){
		if($GLOBALS["VERBOSE"]){echo "No traffic return null\n";}
		return;}
		
	$UPTIME=str_replace("</td>", "", $UPTIME);
	$UPTIME=str_replace("</dt>", "", $UPTIME);
	
	$query="('$servername','$total_traffic','$total_mem','$request_s','$traffic_sec','$traffic_request','$UPTIME')";
	if($GLOBALS["VERBOSE"]){echo "$query\n";}
	$GLOBALS["MODSTATUSQ"][]=$query;
	// voir //http://www.apache.org/server-status

}


?>