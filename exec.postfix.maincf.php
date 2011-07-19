<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.maincf.multi.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf_filtering.inc');
include_once(dirname(__FILE__).'/ressources/class.policyd-weight.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
$GLOBALS["RELOAD"]=false;
$_GET["LOGFILE"]="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
$unix=new unix();

$pidfile="/etc/artica-postfix/".basename(__FILE__)." ". md5(implode("",$argv)).".pid";
if($unix->process_exists(@file_get_contents($pidfile))){
	echo "Starting......: Postfix configurator already executed PID ". @file_get_contents($pidfile)."\n";
	die();
}


$pid=getmypid();
echo "Starting......: Postfix configurator running $pid\n";
file_put_contents($pidfile,$pid);



$users=new usersMenus();
$GLOBALS["CLASS_USERS_MENUS"]=$users;
if(!$users->POSTFIX_INSTALLED){
	echo("Postfix is not installed\n");
	die();
}


if(!$unix->IS_OPENLDAP_RUNNING()){
	echo "Starting......: Postfix openldap is not running, start it\n";
	system("/etc/init.d/artica-postfix start ldap");
}
if(!$unix->IS_OPENLDAP_RUNNING()){
	echo "Starting......: Postfix openldap is not running, aborting\n";
	die();
}


$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
$GLOBALS["EnableBlockUsersTroughInternet"]=$sock->GET_INFO("EnableBlockUsersTroughInternet");
$GLOBALS["postconf"]=$unix->find_program("postconf");
$GLOBALS["postmap"]=$unix->find_program("postmap");
$GLOBALS["postfix"]=$unix->find_program("postfix");

if($argv[1]=='--networks'){mynetworks();ReloadPostfix(true);die();}
if($argv[1]=='--headers-check'){headers_check();die();}
if($argv[1]=='--headers-checks'){headers_check();die();}
if($argv[1]=='--assp'){ASSP_LOCALDOMAINS();die();}
if($argv[1]=='--artica-filter'){MasterCFBuilder(true);die();}
if($argv[1]=='--ldap-branch'){BuildDefaultBranchs();die();}
if($argv[1]=='--ssl'){MasterCFBuilder(true);die();}
if($argv[1]=='--ssl-on'){MasterCFBuilder(true);die();}
if($argv[1]=='--ssl-off'){MasterCFBuilder(true);die();}
if($argv[1]=='--imap-sockets'){imap_sockets();die();}
if($argv[1]=='--policyd-reconfigure'){policyd_weight_reconfigure();die();}
if($argv[1]=='--restricted'){RestrictedForInternet(true);die();}
if($argv[1]=='--others-values'){OthersValues();CleanMyHostname();ReloadPostfix(true);die();}
if($argv[1]=='--mime-header-checks'){mime_header_checks();ReloadPostfix(true);die();}
if($argv[1]=='--interfaces'){inet_interfaces();exec("{$GLOBALS["postfix"]} stop");exec("{$GLOBALS["postfix"]} start");ReloadPostfix(true);die();}
if($argv[1]=='--mailbox-transport'){MailBoxTransport();ReloadPostfix(true);die();}
if($argv[1]=='--disable-smtp-sasl'){disable_smtp_sasl();ReloadPostfix(true);die();}
if($argv[1]=='--perso-settings'){perso_settings();die();}
if($argv[1]=='--luser-relay'){luser_relay();die();}
if($argv[1]=='--smtp-sender-restrictions'){smtp_cmdline_restrictions();ReloadPostfix(true);die();}
if($argv[1]=='--postdrop-perms'){fix_postdrop_perms();exit;}
if($argv[1]=='--smtpd-restrictions'){smtp_cmdline_restrictions();die();}
if($argv[1]=='--repair-locks'){repair_locks();exit;}
if($argv[1]=='--smtp-sasl'){SetSALS();SetTLS();smtpd_recipient_restrictions();smtp_sasl_security_options();MasterCFBuilder();ReloadPostfix(true);exit;}
if($argv[1]=='--memory'){memory();exit;}
if($argv[1]=='--postscreen'){postscreen($argv[2]);ReloadPostfix(true);exit;}
if($argv[1]=='--freeze'){ReloadPostfix(true);exit;}
if($argv[1]=='--body-checks'){BodyChecks();ReloadPostfix(true);exit;}
if($argv[1]=='--amavis-internal'){amavis_internal();ReloadPostfix(true);exit;}
if($argv[1]=='--notifs-templates'){postfix_templates();ReloadPostfix(true);exit;}
if($argv[1]=='--restricted-domains'){restrict_relay_domains();exit;}




if($argv[1]=='--reconfigure'){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){
		shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-reconfigure");
	}
	$main=new main_cf();
	$main->save_conf_to_server(1);
	if(!is_file("/etc/postfix/hash_files/header_checks.cf")){@file_put_contents("/etc/postfix/hash_files/header_checks.cf","#");}
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	echo "Starting......: Postfix Building main.cf ". strlen($main->main_cf_datas). " bytes done line ". __LINE__."\n";
	_DefaultSettings();
	die();
}


function smtp_cmdline_restrictions(){
	
	    $sock=new sockets();
	    $disable_vrfy_command=$sock->GET_INFO("disable_vrfy_command");
	    if(!is_numeric($disable_vrfy_command)){$disable_vrfy_command=0;}
	    if($disable_vrfy_command==1){postconf("disable_vrfy_command","yes");}else{postconf("disable_vrfy_command","no");}
	
	
		if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_recipient_restrictions() function\n";}
		smtpd_recipient_restrictions();
		if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_client_restrictions() function\n";}
		smtpd_client_restrictions();
		if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_sender_restrictions() function\n";}
		smtpd_sender_restrictions();
		if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_end_of_data_restrictions() function\n";}
		smtpd_end_of_data_restrictions();
		if($GLOBALS["RELOAD"]){
			if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> ReloadPostfix() function\n";}
			ReloadPostfix(true);
		
		}	
	
}


function _DefaultSettings(){
if($GLOBALS["EnablePostfixMultiInstance"]==1){shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-null");return;}

	cleanMultiplesInstances();
	SetSALS();
	SetTLS();
	inet_interfaces();
	headers_check(1);
	MasterCFBuilder();
	mime_header_checks();
	smtp_sasl_auth_enable();
	smtpd_recipient_restrictions();
	smtpd_client_restrictions_clean();
	smtpd_client_restrictions();
	smtpd_sasl_exceptions_networks();
	sender_bcc_maps();
	CleanMyHostname();
	OthersValues();
	MailBoxTransport();
	mynetworks();
	luser_relay();
	smtpd_sender_restrictions();
	smtpd_end_of_data_restrictions();
	perso_settings();
	remove_virtual_mailbox_base();
	postscreen();
	smtp_sasl_security_options();
	BodyChecks();
	postfix_templates();
	ReloadPostfix();	
	
}



if($argv[1]=='--write-maincf'){
	$unix=new unix();
	if($GLOBALS["EnablePostfixMultiInstance"]==1){shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-write-maincf");return;}
	echo "Starting......: Postfix Postfix Multi Instance disabled, single instance mode\n";
	$main=new main_cf();
	$main->save_conf_to_server(1);
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	echo "Starting......: Postfix Building main.cf ". strlen($main->main_cf_datas). "line ". __LINE__." bytes done\n";
	if(!is_file("/etc/postfix/hash_files/header_checks.cf")){@file_put_contents("/etc/postfix/hash_files/header_checks.cf","#");}
	_DefaultSettings();
	if($argv[2]=='no-restart'){appliSecu();die();}
	echo "Starting......: restarting postfix\n";
	$unix->send_email_events("Postfix will be restarted","Line: ". __LINE__."\nIn order to apply new configuration file","postfix");
	shell_exec("/etc/init.d/artica-postfix restart postfix-single");
	die();
}

if($argv[1]=='--maincf'){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --from-main-maincf");return;}	
	$main=new main_cf();
	$main->save_conf_to_server(1);
	file_put_contents('/etc/postfix/main.cf',$main->main_cf_datas);
	_DefaultSettings();
	if($GLOBALS["DEBUG"]){echo @file_get_contents("/etc/postfix/main.cf");}
	die();
}





function ASSP_LOCALDOMAINS(){
	if($GLOBALS["EnablePostfixMultiInstance"]==1){return null;}
	if(!is_dir("/usr/share/assp/files")){return null;}
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	while (list ($num, $ligne) = each ($domains) ){
		$conf=$conf."$ligne\n";
	}
	echo "Starting......: ASSP ". count($domains)." local domains\n"; 
	@file_put_contents("/usr/share/assp/files/localdomains.txt",$conf);
	
}

function SetSALS(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$PostFixSmtpSaslEnable=$sock->GET_INFO("PostFixSmtpSaslEnable");
	$main=new main_cf();
	if($main->main_array["smtpd_tls_session_cache_timeout"]==null){$main->main_array["smtpd_tls_session_cache_timeout"]='3600s';}
	if($PostFixSmtpSaslEnable==1){
		echo "Starting......: SASL authentication is enabled\n";
		
		$cmd["smtpd_sasl_auth_enable"]="yes";
		$cmd["smtpd_use_tls"]="yes";
		$cmd["smtpd_sasl_path"]="smtpd";
		$cmd["smtpd_sasl_authenticated_header"]="yes";
		$cmd["smtpd_tls_session_cache_database"]="btree:\\\$data_directory/smtpd_tls_cache";
		$cmd["smtpd_tls_key_file"]="/etc/ssl/certs/postfix/ca.key";
		$cmd["smtpd_tls_cert_file"]="/etc/ssl/certs/postfix/ca.crt";
		$cmd["smtpd_tls_CAfile"]="/etc/ssl/certs/postfix/ca.csr";
		$cmd["smtpd_delay_reject"]="yes";
		$cmd["smtpd_tls_session_cache_timeout"]=$main->main_array["smtpd_tls_session_cache_timeout"];
		echo "Starting......: SASL authentication running ". count($cmd)." commands\n";
		while (list ($num, $ligne) = each ($cmd) ){
			postconf($num,$ligne);
			
		}
		
	}else{
		echo "Starting......: SASL authentication is disabled\n";
		postconf("smtpd_sasl_auth_enable","no");
		postconf("smtpd_sasl_authenticated_header","no");
		postconf("smtpd_use_tls","no");
		postconf("smtpd_tls_auth_only" ,"no");
	}
	

}

function BodyChecks(){
	$main=new maincf_multi("master","master");
	$datas=$main->body_checks();
	if($datas<>null){
		postconf("body_checks","regexp:/etc/postfix/body_checks");
	}else{
		postconf("body_checks",null);
	}
	
}

function smtp_sasl_security_options(){
	$main=new maincf_multi("master","master");
	$datas=unserialize($main->GET_BIGDATA("smtp_sasl_security_options"));
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	if($datas["noanonymous"]==1){$f[]="noanonymous";}
	if($datas["noplaintext"]==1){$f[]="noplaintext";}
	if($datas["nodictionary"]==1){$f[]="nodictionary";}
	if($datas["mutual_auth"]==1){$f[]="mutual_auth";}
	if(count($f)==0){$f[]="noanonymous";}
	postconf("smtp_sasl_security_options",@implode(", ",$f));
	postconf("smtp_sasl_tls_security_options",@implode(", ",$f));
	postconf("smtpd_delay_reject","yes");	

	$EnableMechSMTPCramMD5=$sock->GET_INFO("EnableMechSMTPCramMD5");
	$EnableMechSMTPDigestMD5=$sock->GET_INFO("EnableMechSMTPDigestMD5");
	$EnableMechSMTPLogin=$sock->GET_INFO("EnableMechSMTPLogin");
	$EnableMechSMTPPlain=$sock->GET_INFO("EnableMechSMTPPlain");
	if(!is_numeric($EnableMechSMTPCramMD5)){$EnableMechSMTPCramMD5=1;}
	if(!is_numeric($EnableMechSMTPDigestMD5)){$EnableMechSMTPDigestMD5=1;}
	if(!is_numeric($EnableMechSMTPLogin)){$EnableMechSMTPLogin=1;}
	if(!is_numeric($EnableMechSMTPPlain)){$EnableMechSMTPPlain=1;}	
	
	if($EnableMechSMTPLogin==1){$d[]="login";}
	if($EnableMechSMTPPlain==1){$d[]="plain";}
	if($EnableMechSMTPDigestMD5==1){$d[]="digest-md5";}
	if($EnableMechSMTPCramMD5==1){$d[]="cram-md5";}
	$EnableMechSMTPText=$sock->GET_INFO("EnableMechSMTPText");
	if($EnableMechSMTPText==null){$d[]="!gssapi, !external, static:all";}else{$d[]=$EnableMechSMTPText;}	
	postconf("smtp_sasl_mechanism_filter",@implode(", ",$d));
	 
	
}




function SetTLS(){
	
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$smtpd_tls_security_level=trim($sock->GET_INFO('smtpd_tls_security_level'));
	if($smtpd_tls_security_level<>null){
		shell_exec("{$GLOBALS["postconf"]} -e \"smtpd_tls_security_level = $smtpd_tls_security_level\" >/dev/null 2>&1");
	}
	
if($sock->GET_INFO('smtp_sender_dependent_authentication')==1){
	postconf("smtp_sender_dependent_authentication","yes");
	postconf("smtp_sasl_auth_enable","yes");
	
	}
	
	$main=new main_cf();
		postconf("broken_sasl_auth_clients","$main->broken_sasl_auth_clients");
		postconf("smtpd_sasl_local_domain","$main->smtpd_sasl_local_domain");
		postconf("smtpd_sasl_authenticated_header","$main->smtpd_sasl_authenticated_header");
		postconf("smtpd_tls_security_level","$main->smtpd_tls_security_level");
		postconf("smtpd_tls_auth_only","$main->smtpd_tls_auth_only");
		postconf("smtpd_tls_received_header","$main->smtpd_tls_received_header");
	}

function mynetworks(){
	
	if($GLOBALS["EnablePostfixMultiInstance"]==1){
		echo "Starting......: Building mynetworks multiple-instances, enabled\n";
		postconf("mynetworks","127.0.0.0/8");
		shell_exec(LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.exec.postfix-multi.php --reload-all");
		return;
	}
	
	$ldap=new clladp();
	$nets=$ldap->load_mynetworks();
	if(!is_array($nets)){
		if($GLOBALS["DEBUG"]){echo "No networks sets\n";}
		postconf("mynetworks","127.0.0.0/8");
		return;
	}
	$nets[]="127.0.0.0/8";

	while (list ($num, $network) = each ($nets) ){$cleaned[$network]=$network;}
	unset($nets);
	while (list ($network, $network2) = each ($cleaned) ){$nets[]=$network;}
	
	
	
	$inline=@implode(", ",$nets);
	$inline=str_replace(',,',',',$inline);
	$config_net=@implode("\n",$nets);
	echo "Starting......: Postfix Building mynetworks ". count($nets)." Networks ($inline)\n";
	@file_put_contents("/etc/artica-postfix/mynetworks",$config_net);
	postconf("mynetworks",$inline);
}

function remove_virtual_mailbox_base(){
	$f=@explode("\n",@file_get_contents("/etc/postfix/main.cf"));
	$found=false;
	while (list ($num, $line) = each ($f) ){
		if(preg_match("#virtual_mailbox_base#",$line)){
			echo "Starting......: Postfix remove virtual_mailbox_base entry\n";
			unset($f[$line]);
			$found=true;
		}
		
	}
	if($found){@file_put_contents("/etc/postfix/main.cf",@implode("\n",$f));}
	
}

function headers_check($noreload=0){
	
	$main=new maincf_multi("master","master");
	$headers=$main->header_checks();
	$headers=str_replace("header_checks =","",$headers); 
	
	if($headers<>null){
		postconf("header_checks",$headers);
	}else{
		postconf("header_checks",null);
	}
	
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.white-black-central.php");
	if($noreload==0){ReloadPostfix(true);}
}


function ReloadPostfix($nohastables=false){
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table();
	$unix=new unix();
	if(is_array($domains)){
		while (list ($num, $ligne) = each ($domains) ){
			$dom[]=$num;
		}
	$myOrigin=$dom[0];}
	
	if($myOrigin==null){
		if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$user=new usersMenus();}else{$user=$GLOBALS["CLASS_USERS_MENUS"];}
		$myOrigin=$user->hostname;
	}
	
	if($myOrigin==null){$myOrigin="localhost.localdomain";}
	$postfix=$unix->find_program("postfix");
	$daemon_directory=$unix->LOCATE_POSTFIX_DAEMON_DIRECTORY();
	echo "Starting......: Postfix daemon directory \"$daemon_directory\"\n";
	postconf("daemon_directory",$daemon_directory);
	
	
	if($myOrigin==null){$myOrigin="localhost.localdomain";}
	
	if(!$nohastables){
		echo "Starting......: Postfix Compiling tables...\n";
		system(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php");
		echo "Starting......: Postfix Compiling tables done.\n";
	}
	
	postconf("myorigin","$myOrigin");
	postconf("smtpd_delay_reject","yes");
	$main=new maincf_multi("master","master");
	$freeze_delivery_queue=$main->GET("freeze_delivery_queue");
	if($freeze_delivery_queue==1){
		postconf("master_service_disable","qmgr.fifo");
		postconf("in_flow_delay","0");
	}else{
		postconf("master_service_disable","");
		$in_flow_delay=$main->GET("in_flow_delay");
		if($in_flow_delay==null){$in_flow_delay="1s";}
		postconf("in_flow_delay",$in_flow_delay);		
	}
	
	
	
	
	
	echo "Starting......: Postfix Apply securities issues\n"; 
	appliSecu();
	echo "Starting......: Postfix Reloading ASSP\n"; 
	system("/usr/share/artica-postfix/bin/artica-install --reload-assp");
	echo "Starting......: Postfix reloading postfix master with \"$postfix\"\n";
	if(is_file($postfix)){shell_exec("$postfix reload >/dev/null 2>&1");return;}
	
	
	
}

function appliSecu(){
	if(is_file("/var/lib/postfix/smtpd_tls_session_cache.db")){shell_exec("/bin/chown postfix:postfix /var/lib/postfix/smtpd_tls_session_cache.db");}
	if(is_file("/var/lib/postfix/master.lock")){@chown("/var/lib/postfix/master.lock","postfix");}
}


function cleanMultiplesInstances(){
	foreach (glob("/etc/postfix-*",GLOB_ONLYDIR ) as $dirname) {
	    echo "Starting......: Postfix removing old instance ". basename($dirname)."\n";
	    shell_exec("/bin/rm -rf $dirname");
	}
	postconf("multi_instance_directories",null);
	
}


	
	
function BuildDefaultBranchs(){
	
	$main=new main_cf();
	$main->BuildDefaultWhiteListRobots();
	
	$sender=new sender_dependent_relayhost_maps();
	
	if($GLOBALS["RELOAD"]){
		$unix=new unix();
		$postfix=$unix->find_program("postfix");
		shell_exec("$postfix stop && $postfix start");
	}
}



function imap_sockets(){
	if(!is_file("/etc/imapd.conf")){
		echo "Starting......: cyrus transport no available\n";
		return;
	}
	
	shell_exec("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus");
	
	
	
	$f=explode("\n",@file_get_contents("/etc/imapd.conf"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#lmtpsocket:(.+)#",$ligne,$re)){
			$socket=trim($re[1]);
		}
	}
	
	$f=explode("\n",@file_get_contents("/etc/cyrus.conf"));
	while (list ($num, $ligne) = each ($f) ){
		if(substr($ligne,0,1)=="#"){continue;}
		if(preg_match("#lmtpunix\s+(.+)#",$ligne,$re)){
			echo "Starting......: cyrus lmtpunix: $ligne\n";
			$f[$num]="  lmtpunix	cmd=\"lmtpd\" listen=\"$socket\" prefork=1";
			$write=true;
		}
	}	
	
	if($write){
		@file_put_contents("/etc/cyrus.conf",implode("\n",$f));
		shell_exec("/etc/init.d/artica-postfix restart imap");
	}
	if(!is_file($socket)){
		if(is_file("$socket=")){$socket="$socket=";}
	}
	
	echo "Starting......: cyrus transport: unix: $socket\n";
	if($socket<>null){
		postconf("mailbox_transport","lmtp:unix:$socket");
		shell_exec("postfix stop");
		shell_exec("postfix start");
		shell_exec("postqueue -f");
	}
	
	
	
}

function policyd_weight_reconfigure(){
	$pol=new policydweight();
	$conf=$pol->buildConf();
	@file_put_contents("/etc/artica-postfix/settings/Daemons/PolicydWeightConfig",$conf);
	echo "Starting......: policyd-weight building first config done\n";
}

function mime_header_checks(){
	
	$main=new maincf_multi("master","master");
	$enable_attachment_blocking_postfix=$main->GET("enable_attachment_blocking_postfix");
	if(!is_numeric($enable_attachment_blocking_postfix)){$enable_attachment_blocking_postfix=0;}	
	
	if($enable_attachment_blocking_postfix==1){
		$sql=new mysql();
		$sql="SELECT * FROM smtp_attachments_blocking WHERE ou='_Global' ORDER BY IncludeByName";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		writelogs("-> Qyery",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			writelogs("Error mysql $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
			return null;}
			
		writelogs("-> loop",__FUNCTION__,__FILE__,__LINE__);
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["IncludeByName"]==null){continue;}
			$f[]=$ligne["IncludeByName"];
			
		}

	}else{
		echo "Starting......: Blocking extensions trough postfix is disabled\n";
	}
	
	
	if(!is_array($f)){
		echo "Starting......: No extensions blocked\n";
		postconf("mime_header_checks",null);
		return;
	}
	
	$strings=implode("|",$f);
	echo "Starting......: ". count($f)." extensions blocked\n";
	$pattern[]="/^\s*Content-(Disposition|Type).*name\s*=\s*\"?(.+\.($strings))\"?\s*$/\tREJECT file attachment types is not allowed. File \"$2\" has the unacceptable extension \"$3\"";
	$pattern[]="";
	@file_put_contents("/etc/postfix/mime_header_checks",implode("\n",$pattern));
	postconf("mime_header_checks","regexp:/etc/postfix/mime_header_checks");
	
}

function smtp_sasl_auth_enable(){
	$ldap=new clladp();
	if($ldap->ldapFailed){
		echo "Starting......: SMTP SALS connection to ldap failed\n";
		return;
	}

	$suffix="dc=organizations,$ldap->suffix";
	$filter="(&(objectclass=SenderDependentSaslInfos)(SenderCanonicalRelayPassword=*))";
	$res=array();
	$search = @ldap_search($ldap->ldap_connection,$suffix,"$filter",array());
	$count=0;		
	if ($search) {
			$hash=ldap_get_entries($ldap->ldap_connection,$search);	
			$count=$hash["count"];
		}
	
	echo "Starting......: SMTP SALS $count account(s)\n"; 	
	if($count>0){
		postconf("smtp_sasl_auth_enable","yes");
		postconf("smtp_sender_dependent_authentication","yes");
		
		
	}else{
		postconf("smtp_sender_dependent_authentication","no");
		
	}

}

function smtpd_client_restrictions_clean(){
	$f=@explode("\n",@file_get_contents("/etc/postfix/main.cf"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#smtpd_client_restrictions_#",$ligne)){continue;}
		if(preg_match("#smtpd_helo_restrictions_#",$ligne)){continue;}
		if(preg_match("#check_client_access ldap_#",$ligne)){continue;}
		$ligne=str_replace("check_client_access ldap:smtpd_client_restrictions_check_client_access","",$ligne);
		$ligne=str_replace("main.cf=\'my_domain\'=","",$ligne);
		
		$newarray[]=$ligne;
		
	}
	@file_put_contents("/etc/postfix/main.cf",@implode("\n",$newarray));
	
}


function smtpd_client_restrictions(){
	exec("{$GLOBALS["postconf"]} -h smtpd_client_restrictions",$datas);
	$tbl=explode(",",implode(" ",$datas));
	
if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$EnablePostfixAntispamPack=$sock->GET_INFO("EnablePostfixAntispamPack");
	$EnableArticaPolicyFilter=$sock->GET_INFO("EnableArticaPolicyFilter");
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');		

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		$ligne=trim($ligne);
		if(trim($ligne)==null){continue;}
		if($ligne=="Array"){continue;}
		$newHash[$ligne]=$ligne;
		}
	}

	
	
	
	unset($newHash["check_client_access hash:/etc/postfix/check_client_access"]);
	unset($newHash["check_client_access \"hash:/etc/postfix/postfix_allowed_connections\""]);
	unset($newHash["check_client_access hash:/etc/postfix/postfix_allowed_connections"]);
	unset($newHash["reject_non_fqdn_hostname"]);
	unset($newHash["reject_unknown_sender_domain"]);
	unset($newHash["reject_non_fqdn_sender"]);
	unset($newHash["reject_unauth_pipelining"]);
	unset($newHash["reject_invalid_hostname"]);
	unset($newHash["reject_unknown_client_hostname"]);
	unset($newHash["reject_unknown_reverse_client_hostname"]);
	unset($newHash["reject_invalid_hostname"]);
	unset($newHash["reject_rbl_client zen.spamhaus.org"]);
	unset($newHash["reject_rbl_client sbl.spamhaus.org"]);
	unset($newHash["reject_rbl_client cbl.abuseat.org"]);
	unset($newHash["reject_unauth_pipelining"]);
	unset($newHash["reject_unauth_pipelining"]);
	unset($newHash["reject_rbl_client=zen.spamhaus.org"]);
	unset($newHash["reject_rbl_client=sbl.spamhaus.org"]);
	unset($newHash["reject_rbl_client=sbl.spamhaus.org"]);
	
	unset($newHash["check_client_access hash:/etc/postfix/amavis_internal"]);
	
	
	if($GLOBALS["VERBOSE"]){
		echo "Starting......: smtpd_client_restrictions: origin:".@implode(",",$newHash)."\n";
	}
	
	$main=new maincf_multi("master","master");
	$check_client_access=$main->check_client_access();
	if($check_client_access<>null){
		$newHash[$check_client_access]=$check_client_access;
	}
	$smtpd_client_restrictions=array();
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){
			if(preg_match("#hash:(.+)$#",$ligne,$re)){
				$path=trim($re[1]);
				if(!is_file($path)){
					echo "Starting......: smtpd_client_restrictions: bungled \"$ligne\"\n"; 
					continue;
				}
			}
			
			if(preg_match("#reject_rbl_client=(.+?)$#",$ligne,$re)){
				$rbl=trim($re[1]);
					echo "Starting......: reject_rbl_client: bungled \"$ligne\" fix it\n"; 
					$num="reject_rbl_client $rbl";
					continue;
				}
			}			
			$smtpd_client_restrictions[]=$num;
		}
	
if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$reject_unknown_client_hostname=$sock->GET_INFO('reject_unknown_client_hostname');
	$reject_unknown_reverse_client_hostname=$sock->GET_INFO('reject_unknown_reverse_client_hostname');
	
	$reject_invalid_hostname=$sock->GET_INFO('reject_invalid_hostname');
	if($reject_unknown_client_hostname==1){$smtpd_client_restrictions[]="reject_unknown_client_hostname";}
	if($reject_unknown_reverse_client_hostname==1){$smtpd_client_restrictions[]="reject_unknown_reverse_client_hostname";}
	if($reject_invalid_hostname==1){$smtpd_client_restrictions[]="reject_invalid_hostname";}
	
	if($EnablePostfixAntispamPack==1){
		echo "Starting......: smtpd_client_restrictions:Anti-spam Pack is enabled\n";
		if(!is_file("/etc/postfix/postfix_allowed_connections")){@file_put_contents("/etc/postfix/postfix_allowed_connections","#");}
		$smtpd_client_restrictions[]="check_client_access \"hash:/etc/postfix/postfix_allowed_connections\"";
		$smtpd_client_restrictions[]="reject_non_fqdn_hostname";
		$smtpd_client_restrictions[]="reject_invalid_hostname";
		$smtpd_client_restrictions[]="reject_rbl_client zen.spamhaus.org";
		$smtpd_client_restrictions[]="reject_rbl_client sbl.spamhaus.org";
		$smtpd_client_restrictions[]="reject_rbl_client cbl.abuseat.org";		
	}	
	
	
	
	if($EnableArticaPolicyFilter==1){
		array_unshift($smtpd_client_restrictions,"check_policy_service inet:127.0.0.1:54423");
	}

	echo "Starting......: smtpd_client_restrictions: ". count($smtpd_client_restrictions)." rule(s)\n";
	
	
	if($EnableAmavisInMasterCF==1){
		if($EnableAmavisDaemon==1){
			$count=amavis_internal();
			if($count>0){
				echo "Starting......: $count addresses bypassing amavisd new\n";
				$amavis_internal="check_client_access hash:/etc/postfix/amavis_internal,";
			}
		}
	}	
	
	if(is_array($smtpd_client_restrictions)){
		
		
		//CLEAN engine ---------------------------------------------------------------------------------------
		while (list ($num, $ligne) = each ($smtpd_client_restrictions) ){
			$array_cleaned[trim($ligne)]=trim($ligne);
		}
		
		
		
		unset($array_cleaned["permit_mynetworks"]);
		unset($array_cleaned["permit_sasl_authenticated"]);
		
		unset($smtpd_client_restrictions);
		$smtpd_client_restrictions=array();
		
		
		if(is_array($smtpd_client_restrictions)){
			while (list ($num, $ligne) = each ($smtpd_client_restrictions) ){
				echo "Starting......: smtpd_client_restrictions : $ligne\n";
				$smtpd_client_restrictions[]=trim($ligne);}
		}
	   //CLEAN engine ---------------------------------------------------------------------------------------
	}else{
		echo "Starting......: smtpd_client_restrictions: Not an array\n";
	}	
	
	$newval=null;
	
	

	if(count($smtpd_client_restrictions)>1){
			$newval=implode(",",$smtpd_client_restrictions);
			$newval="{$amavis_internal}permit_mynetworks,permit_sasl_authenticated,reject_unauth_pipelining,$newval";
	}else{
		
		if($amavis_internal<>null){
			echo "Starting......: smtpd_client_restrictions: adding amavis internal\n";
			$newval="check_client_access hash:/etc/postfix/amavis_internal";
		}
	}
	
			
	postconf("smtpd_client_restrictions",$newval);
	
	
	
}

function restrict_relay_domains(){
	$ldap=new clladp();
	$dn="dc=organizations,$ldap->suffix";
	$attr=array("cn");
	$pattern="(&(objectclass=PostfixRelayRecipientMaps)(cn=@*))";
	$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	$relaysdomains=$ldap->hash_get_relay_domains();
	if($GLOBALS["postmap"]==null){$unix=new unix();$GLOBALS["postmap"]=$unix->find_program("postmap");}
	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		if(preg_match("#^@(.+)#",$domain,$re)){$domain=$re[1];}
		unset($relaysdomains[$domain]);
	}
	
	unset($relaysdomains["localhost.localdomain"]);
	if(is_array($relaysdomains)){
		while (list ($num, $ligne) = each ($relaysdomains) ){
			$f[]="$num\tartica_restrict_relay_domains";
			}
	}
	
	echo "Starting......: Postfix ". count($f)." restricted relayed domains\n"; 
	@file_put_contents("/etc/postfix/relay_domains_restricted",implode("\n",$f));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/relay_domains_restricted >/dev/null 2>&1");
		
	
}



function smtpd_recipient_restrictions(){
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$newHash=array();
	$EnableCluebringer=$sock->GET_INFO("EnableCluebringer");
	$EnablePostfixAntispamPack=$sock->GET_INFO("EnablePostfixAntispamPack");
	$EnableArticaPolicyFilter=$sock->GET_INFO("EnableArticaPolicyFilter");
	if($GLOBALS["DEBUG"]){echo "EnableCluebringer=$EnableCluebringer\n";}
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');	
	
	exec("{$GLOBALS["postconf"]} -h smtpd_recipient_restrictions",$datas);
	$tbl=explode(",",implode(" ",$datas));
	$permit_mynetworks_remove=false;

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$newHash[trim($ligne)]=trim($ligne);
		}
	}
	
	unset($newHash["check_client_access hash:/etc/postfix/amavis_internal"]);
	unset($newHash["check_recipient_access hash:/etc/postfix/relay_domains_restricted"]);
	unset($newHash["permit"]);
	unset($newHash["check_sender_access hash:/etc/postfix/disallow_my_domain"]);
	unset($newHash["check_sender_access hash:/etc/postfix/unrestricted_senders"]);
	unset($newHash["check_recipient_access hash:/etc/postfix/amavis_bypass_rcpt"]);
	unset($newHash["reject_unauth_destination"]);
	unset($newHash["permit_mynetworks"]);
	unset($newHash["check_client_access pcre:/etc/postfix/fqrdns.pcre"]);
	unset($newHash["check_policy_service inet:127.0.0.1:54423"]);
	
	
	
	
	
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){
		if(preg_match("#hash:(.+)$#",$ligne,$re)){
				$path=trim($re[1]);
				if(!is_file($path)){
					echo "Starting......: smtpd_recipient_restrictions: bungled \"$ligne\"\n"; 
					continue;
				}
			}
			$smtpd_recipient_restrictions[]=$num;
		}
	}
	
	if($GLOBALS["DEBUG"]){echo "CLUEBRINGER_INSTALLED=$users->CLUEBRINGER_INSTALLED\n";}
	
	if($users->CLUEBRINGER_INSTALLED){
		if($EnableCluebringer==1){$smtpd_recipient_restrictions[]="check_policy_service inet:127.0.0.1:13331";}
	}
					
	postconf("smtpd_restriction_classes","artica_restrict_relay_domains");
	postconf("artica_restrict_relay_domains","reject_unverified_recipient");
	
	
	$smtpd_recipient_restrictions[]="permit_mynetworks";
	$smtpd_recipient_restrictions[]="permit_sasl_authenticated";
	$smtpd_recipient_restrictions[]="check_recipient_access hash:/etc/postfix/relay_domains_restricted";
	$smtpd_recipient_restrictions[]="check_recipient_access hash:/etc/postfix/amavis_bypass_rcpt";
	
	
	
	amavis_bypass_byrecipients();
	restrict_relay_domains();
	
	
	postconf("auth_relay",null);
	
	
	
	if($GLOBALS["EnableBlockUsersTroughInternet"]==1){
		echo "Starting......: Restricted users are enabled\n"; 	
		if(RestrictedForInternet()){
 			postconf("auth_relay","check_recipient_access hash:/etc/postfix/local_domains, reject");
			 array_unshift($smtpd_recipient_restrictions,"check_sender_access hash:/etc/postfix/unrestricted_senders");
			__ADD_smtpd_restriction_classes("auth_relay");
		}else{__REMOVE_smtpd_restriction_classes("auth_relay");}
	}
	else{__REMOVE_smtpd_restriction_classes("auth_relay");}
		
		
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$reject_forged_mails=$sock->GET_INFO("reject_forged_mails");
	if($reject_forged_mails==1){
		if(smtpd_recipient_restrictions_reject_forged_mails()){
			echo "Starting......: Reject Forged mails enabled\n"; 	
			$smtpd_recipient_restrictions[]="check_sender_access hash:/etc/postfix/disallow_my_domain";
		}
	}else{
		echo "Starting......: Reject Forged mails disabled\n"; 			
	}
	
	$EnableGenericrDNSClients=$sock->GET_INFO("EnableGenericrDNSClients");
	if(!$users->POSTFIX_PCRE_COMPLIANCE){$EnableGenericrDNSClients=0;}
	
	if($EnableGenericrDNSClients==1){
		echo "Starting......: Reject Public ISP reverse DNS patterns enabled\n"; 
		$smtpd_recipient_restrictions[]="check_client_access pcre:/etc/postfix/fqrdns.pcre";
		shell_exec("/bin/cp /usr/share/artica-postfix/bin/install/postfix/fqrdns.pcre /etc/postfix/fqrdns.pcre");
	}else{
		echo "Starting......: Reject Public ISP reverse DNS patterns disabled\n";
	}
	
	
	
	if($EnableArticaPolicyFilter==1){
		array_unshift($smtpd_recipient_restrictions,"check_policy_service inet:127.0.0.1:54423");
	}
	

	
	$smtpd_recipient_restrictions[]="reject_unauth_destination";
	


	
	
	
	
	//CLEAN engine ---------------------------------------------------------------------------------------
	while (list ($num, $ligne) = each ($smtpd_recipient_restrictions) ){
		$smtpd_recipient_restrictions_cleaned[trim($ligne)]=trim($ligne);
	}
	
	
	
	unset($smtpd_recipient_restrictions);
	while (list ($num, $ligne) = each ($smtpd_recipient_restrictions_cleaned) ){$smtpd_recipient_restrictions[]=trim($ligne);}

   //CLEAN engine ---------------------------------------------------------------------------------------
	
	
	if(is_array($smtpd_recipient_restrictions)){$newval=implode(",",$smtpd_recipient_restrictions);}
	if($GLOBALS["DEBUG"]){echo "smtpd_recipient_restrictions = $newval\n";}
	postconf("smtpd_recipient_restrictions",$newval);
	
	
	}
	
function amavis_bypass_byrecipients(){
	
	$users=new usersMenus();
	$q=new mysql();
	$unix=new unix();
	$sock=new sockets();
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	if($EnableAmavisDaemon==1){
		if($EnableAmavisInMasterCF==1){
			$sql="SELECT * FROM amavis_bypass_rcpt ORDER BY `pattern`";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";return 0;}	
			$count=0;
			$f=array();
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$ligne["pattern"]=trim($ligne["pattern"]);
				$ip=trim($ligne["pattern"]);
				if($ip==null){continue;}
				if(is_array($ip)){continue;}
				$count++;
				$f[]="{$ligne["pattern"]}\tFILTER smtp:[127.0.0.1]:10025";
			}
		}
	}
	$postmap=$unix->find_program("postmap");
	echo "Starting......: ". count($f) ." bypass recipient(s) for amavisd new\n"; 	
	
	$f[]="";
	@file_put_contents("/etc/postfix/amavis_bypass_rcpt",@implode("\n",$f));
	shell_exec("$postmap hash:/etc/postfix/amavis_bypass_rcpt");
	return $count;
	}	
	
function amavis_internal(){
	$users=new usersMenus();
	$q=new mysql();
	$unix=new unix();
	$sock=new sockets();
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	if($EnableAmavisDaemon==1){
		if($EnableAmavisInMasterCF==1){
			$sql="SELECT * FROM amavisd_bypass ORDER BY ip_addr";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";return 0;}	
			$count=0;
			$f=array();
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$ligne["ip_addr"]=trim($ligne["ip_addr"]);
				$ip=trim($ligne["ip_addr"]);
				if($ip==null){continue;}
				if(is_array($ip)){continue;}
				$count++;
				$f[]="{$ligne["ip_addr"]}\tFILTER smtp:[127.0.0.1]:10025";
			}
		}
	}
	
	$postmap=$unix->find_program("postmap");
	$f[]="";
	@file_put_contents("/etc/postfix/amavis_internal",@implode("\n",$f));
	shell_exec("$postmap hash:/etc/postfix/amavis_internal");
	return $count;
	
}	




	
function __ADD_smtpd_restriction_classes($classname){
exec("{$GLOBALS["postconf"]} -h smtpd_restriction_classes",$datas);
	$tbl=explode(",",implode(" ",$datas));
	

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$newHash[$ligne]=$ligne;
		}
	}
	
	unset($newHash[$classname]);
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){	
			$smtpd_restriction_classes[]=$num;
		}
	}
	
	$smtpd_restriction_classes[]=$classname;
	if(is_array($smtpd_restriction_classes)){$newval=implode(",",$smtpd_restriction_classes);}
	
	postconf("smtpd_restriction_classes",$newval);
		
	
}

function __REMOVE_smtpd_restriction_classes($classname){
	exec("{$GLOBALS["postconf"]} -h smtpd_restriction_classes",$datas);
	$tbl=explode(",",implode(" ",$datas));
	$newHash=array();

	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$newHash[$ligne]=$ligne;
		}
	}
	
	unset($newHash[$classname]);
	
	if(is_array($newHash)){	
		while (list ($num, $ligne) = each ($newHash) ){	
			$smtpd_restriction_classes[]=$num;
		}
	}
	
	if(is_array($smtpd_restriction_classes)){$newval=implode(",",$smtpd_restriction_classes);}
	postconf("smtpd_restriction_classes",$newval);
}
	
	
function smtpd_recipient_restrictions_reject_forged_mails(){
	$ldap=new clladp();
	$unix=new unix();
	$postmap=$unix->find_program("postmap");
	$hash=$ldap->hash_get_all_domains();
	if(!is_array($hash)){return false;}
	while (list ($domain, $ligne) = each ($hash) ){
		$f[]="$domain\t 554 $domain FORGED MAIL"; 
		
	}
	
	if(!is_array($f)){return false;}
	@file_put_contents("/etc/postfix/disallow_my_domain",@implode("\n",$f));
	echo "Starting......: compiling domains against forged messages\n";
	shell_exec("$postmap hash:/etc/postfix/disallow_my_domain");
	return true;
}

function RestrictedForInternet($reload=false){
	$main=new main_cf();
	$unix=new unix();
	$GLOBALS["postmap"]=$unix->find_program("postmap");
	$restricted_users=$users=$main->check_sender_access();
	if(!$reload){echo "Starting......: Restricted users ($restricted_users)\n";}
	if($restricted_users>0){
		@copy("/etc/artica-postfix/settings/Daemons/unrestricted_senders","/etc/postfix/unrestricted_senders");
		@copy("/etc/artica-postfix/settings/Daemons/unrestricted_senders_domains","/etc/postfix/local_domains");
		echo "Starting......: Compiling unrestricted users ($restricted_users)\n";
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/unrestricted_senders");
		echo "Starting......: Compiling local domains\n";
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/local_domains");
		if($reload){shell_exec("{$GLOBALS["postfix"]} reload");}
		return true;
		}
	return false;
	
}

function CleanMyHostname(){
	exec("{$GLOBALS["postconf"]} -h myhostname",$results);
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$myhostname=trim(implode("",$results));
	$myhostname=str_replace("header_checks =","",$myhostname);
	exec("{$GLOBALS["postconf"]} -h relayhost",$results);
	
	if(is_array($results)){
		$relayhost=trim(@implode("",$results));
	}
	
	if($myhostname=="Array.local"){
		if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
		$myhostname=$users->hostname;
	}
	
	if($relayhost<>null){
		if($myhostname==$relayhost){
			$myhostname="$myhostname.local";
		}
	}
	
	//fix bug with extension.
	
	$myhostname=str_replace(".local.local.",".local",$myhostname);
	$myhostname=str_replace(".locallocal.locallocal.",".",$myhostname);
	$myhostname=str_replace(".locallocal",".local",$myhostname);
	$myhostname=str_replace(".local.local",".local",$myhostname);
	
	$myhostname2=trim($sock->GET_INFO("myhostname"));
	if(strlen($myhostname2)>0){
		$myhostname=$myhostname2;
	}
	
	echo "Starting......: Hostname \"$myhostname\"\n";
	postconf("myhostname",$myhostname);
	
}

function smtpd_sasl_exceptions_networks(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$smtpd_sasl_exceptions_networks_list=unserialize(base64_decode($sock->GET_INFO("smtpd_sasl_exceptions_networks")));
	$smtpd_sasl_exceptions_mynet=$sock->GET_INFO("smtpd_sasl_exceptions_mynet");	
	if($smtpd_sasl_exceptions_mynet==1){
		$nets[]="\\\$mynetworks";
	}
	
	if(is_array($smtpd_sasl_exceptions_networks_list)){
		while (list ($num, $val) = each ($smtpd_sasl_exceptions_networks_list) ){
			if($val==null){continue;}
			$nets[]=$val;
		}
	}
	
	if(is_array($nets)){
		$final_nets=implode(",",$nets);
		echo "Starting......: SASL exceptions enabled\n";
		postconf("smtpd_sasl_exceptions_networks",$final_nets);
		
	}else{
		echo "Starting......: SASL exceptions disabled\n";
		postconf("smtpd_sasl_exceptions_networks",null);
		
	}
}

function sender_bcc_maps(){
if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$sender_bcc_maps_path=$sock->GET_INFO("sender_bcc_maps_path");
	if(is_file($sender_bcc_maps_path)){
		echo "Starting......: Sender BCC \"$sender_bcc_maps_path\"\n";
		postconf("sender_bcc_maps","hash:$sender_bcc_maps_path");
		shell_exec("{$GLOBALS["postmap"]} hash:$sender_bcc_maps_path");
	}
	
}

function OthersValues(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return;}	
	$main=new main_cf();
	$mainmulti=new maincf_multi("master","master");
	$main->FillDefaults();	
	echo "Starting......: Fix others settings\n";
	
	$message_size_limit=$sock->GET_INFO("message_size_limit");
	if(!is_numeric($message_size_limit)){
		$message_size_limit=0;
		
	}
	$main->main_array["message_size_limit"]=$sock->GET_INFO("message_size_limit");
	
	
	$minimal_backoff_time=$mainmulti->GET("minimal_backoff_time");
	$maximal_backoff_time=$mainmulti->GET("maximal_backoff_time");
	$bounce_queue_lifetime=$mainmulti->GET("bounce_queue_lifetime");
	$maximal_queue_lifetime=$mainmulti->GET("maximal_queue_lifetime");
	
	

	
	
	$main->main_array["default_destination_recipient_limit"]=$sock->GET_INFO("default_destination_recipient_limit");
	$main->main_array["smtpd_recipient_limit"]=$sock->GET_INFO("smtpd_recipient_limit");
	$main->main_array["mime_nesting_limit"]=$sock->GET_INFO("mime_nesting_limit");
	$main->main_array["header_address_token_limit"]=$sock->GET_INFO("header_address_token_limit");
	$main->main_array["virtual_mailbox_limit"]=$sock->GET_INFO("virtual_mailbox_limit");
	
	if($main->main_array["message_size_limit"]==null){$main->main_array["message_size_limit"]=102400000;}
	if($main->main_array["virtual_mailbox_limit"]==null){$main->main_array["virtual_mailbox_limit"]=102400000;}
	if($main->main_array["default_destination_recipient_limit"]==null){$main->main_array["default_destination_recipient_limit"]=50;}
	if($main->main_array["smtpd_recipient_limit"]==null){$main->main_array["smtpd_recipient_limit"]=1000;}
	if($main->main_array["mime_nesting_limit"]==null){$main->main_array["mime_nesting_limit"]=100;}
	if($main->main_array["header_address_token_limit"]==null){$main->main_array["header_address_token_limit"]=10240;}
	
	echo "Starting......: message_size_limit={$main->main_array["message_size_limit"]}\n";
	echo "Starting......: default_destination_recipient_limit={$main->main_array["default_destination_recipient_limit"]}\n";
	echo "Starting......: smtpd_recipient_limit={$main->main_array["smtpd_recipient_limit"]}\n";
	echo "Starting......: mime_nesting_limit={$main->main_array["mime_nesting_limit"]}\n";
	echo "Starting......: header_address_token_limit={$main->main_array["header_address_token_limit"]}\n";
	echo "Starting......: minimal_backoff_time=$minimal_backoff_time\n";
	echo "Starting......: maximal_backoff_time=$maximal_backoff_time\n";
	echo "Starting......: maximal_queue_lifetime=$maximal_queue_lifetime\n";
	echo "Starting......: bounce_queue_lifetime=$bounce_queue_lifetime\n";
	
	
	if($minimal_backoff_time==null){$minimal_backoff_time="300s";}
	if($maximal_backoff_time==null){$maximal_backoff_time="4000s";}
	if($bounce_queue_lifetime==null){$bounce_queue_lifetime="5d";}
	if($maximal_queue_lifetime==null){$maximal_queue_lifetime="5d";}		
	
	
	postconf("message_size_limit","$message_size_limit");
	postconf("virtual_mailbox_limit","$message_size_limit");
	postconf("mailbox_size_limit","$message_size_limit");
	postconf("default_destination_recipient_limit","{$main->main_array["default_destination_recipient_limit"]}");
	postconf("smtpd_recipient_limit","{$main->main_array["smtpd_recipient_limit"]}");
	postconf("mime_nesting_limit","{$main->main_array["mime_nesting_limit"]}");
	postconf("minimal_backoff_time","$minimal_backoff_time");
	postconf("maximal_backoff_time","$maximal_backoff_time");
	postconf("maximal_queue_lifetime","$maximal_queue_lifetime");
	postconf("bounce_queue_lifetime","$bounce_queue_lifetime");
	
	
	
	if(!isset($GLOBALS["POSTFIX_HEADERS_CHECK_BUILDED"])){headers_check(1);}
	
	
	$HashMainCf=unserialize(base64_decode($sock->GET_INFO("HashMainCf")));
	if(is_array($HashMainCf)){
		while (list ($key, $val) = each ($HashMainCf) ){
			system("{$GLOBALS["postconf"]} -e \"$key = $val\" >/dev/null 2>&1");
		}
	}
	
	
	perso_settings();
}

function inet_interfaces(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return;}
	$table=explode("\n",$sock->GET_INFO("PostfixBinInterfaces"));	
	if(!is_array($table)){$table[]="all";}
	
	while (list ($num, $val) = each ($table) ){
		if($val==null){continue;}
		$newarray[]=$val;
	}
	
	if(!is_array($newarray)){$newarray[]="all";}
	$finale=implode(",",$newarray);
	$finale=str_replace(',,',',',$finale);
	echo "Starting......: Postfix Listen interface(s) \"$finale\"\n";
	
	
	postconf("inet_interfaces",$finale);
	postconf("artica-filter_destination_recipient_limit",1);
	postconf("inet_protocols","ipv4");
	postconf("smtp_bind_address6","");
	
	
	 
	
	$smtp_bind_address6=$sock->GET_INFO("smtp_bind_address6");
	$PostfixEnableIpv6=$sock->GET_INFO("PostfixEnableIpv6");
	if($PostfixEnableIpv6==null){$PostfixEnableIpv6=0;}
	if($PostfixEnableIpv6=1){
		if(trim($smtp_bind_address6)<>null){
			echo "Starting......: Postfix Listen ipv6 \"$smtp_bind_address6\"\n";
			postconf("inet_protocols","all");
			postconf("smtp_bind_address6",$smtp_bind_address6);
		}
	}
	
	
	
}

function MailBoxTransport(){
	$main=new maincf_multi();
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}

	$default=$main->getMailBoxTransport();
	postconf("zarafa_destination_recipient_limit",1);
	postconf("mailbox_transport",$default);
	system("{$GLOBALS["postconf"]} -e \"zarafa_destination_recipient_limit = 1\" >/dev/null 2>&1");

	
	if(preg_match("#lmtp:(.+?):[0-9]+#",$default)){
		if(!$users->ZARAFA_INSTALLED){
			if(!$users->cyrus_imapd_installed){
				disable_lmtp_sasl();
				return null;
			}
			echo "Starting......: Postfix LMTP is enabled $default\n";
			$ldap=new clladp();
			$CyrusLMTPListen=trim($sock->GET_INFO("CyrusLMTPListen"));
			$cyruspass=$ldap->CyrusPassword();
			if($CyrusLMTPListen<>null){
				@file_put_contents("/etc/postfix/lmtpauth","$CyrusLMTPListen\tcyrus:$cyruspass");
				shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/lmtpauth");
				postconf("lmtp_sasl_auth_enable","yes");
				postconf("lmtp_sasl_password_maps","hash:/etc/postfix/lmtpauth");
				postconf("lmtp_sasl_mechanism_filter","plain, login");
				postconf("lmtp_sasl_security_options",null);	
			}		
		}
	}else{
		disable_lmtp_sasl();
	}
	
	
	}
	
function disable_lmtp_sasl(){
	echo "Starting......: Postfix LMTP is disabled\n";
	postconf("lmtp_sasl_auth_enable","no");
	
			
}
	
function disable_smtp_sasl(){
	postconf("smtp_sasl_password_maps","");
	postconf("smtp_sasl_auth_enable","no");
	
}

function perso_settings(){
	$main=new main_perso();
	if(!is_array($main->main_array)){
		echo "Starting......: Postfix no main.cf tokens defined by admin\n";
		return;
	}
	while (list ($key, $array) = each ($main->main_array) ){
		echo "Starting......: Postfix Added by administrator: $key = {$array["VALUE"]}\n";
		postconf($key,$array["VALUE"]);
		
	}
	
	if($GLOBALS["RELOAD"]){exec("{$GLOBALS["postfix"]} reload");}
	
}

function luser_relay(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$luser_relay=trim($sock->GET_INFO("luser_relay"));
	if($luser_relay==null){
		echo "Starting......: Postfix no Unknown user recipient set\n";
		system("{$GLOBALS["postconf"]} -e \"luser_relay = \" >/dev/null 2>&1");
		return;
	}
	echo "Starting......: Postfix Unknown user set to $luser_relay\n";
	postconf("luser_relay",$luser_relay);
	postconf("local_recipient_maps",null);
	if($GLOBALS["RELOAD"]){shell_exec("{$GLOBALS["postfix"]} reload");}
	
}
function smtpd_sender_restrictions(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$main=new maincf_multi("master","master");
	$smtpd_sender_restrictions_black=$main->Blacklist_generic();
	
	
	$RestrictToInternalDomains=$sock->GET_INFO("RestrictToInternalDomains");
	$EnablePostfixInternalDomainsCheck=$sock->GET_INFO("EnablePostfixInternalDomainsCheck");
	$reject_non_fqdn_sender=$sock->GET_INFO('reject_non_fqdn_sender');	
	$reject_unknown_sender_domain=$sock->GET_INFO('reject_unknown_sender_domain');
	
	if($EnablePostfixInternalDomainsCheck==1){
			$smtpd_sender_restrictions[]="reject_unknown_sender_domain";
			$reject_unknown_sender_domain=0;
	
	}
	
	
	
	if($RestrictToInternalDomains==1){
		BuildAllWhitelistedServer();
		BuildAllMyDomains();
		$smtpd_sender_restrictions[]="check_client_access hash:/etc/postfix/all_whitelisted_servers";
		$smtpd_sender_restrictions[]="check_sender_access hash:/etc/postfix/all_internal_domains";
		if($reject_unknown_sender_domain==1){$smtpd_sender_restrictions[]="reject_unknown_sender_domain";}
		if($reject_non_fqdn_sender==1){$smtpd_sender_restrictions[]="reject_non_fqdn_sender";}
		if($smtpd_sender_restrictions_black<>null){$smtpd_sender_restrictions[]=$smtpd_sender_restrictions_black;}
		$smtpd_sender_restrictions[]="reject";
	}else{
		if($reject_unknown_sender_domain==1){$smtpd_sender_restrictions[]="reject_unknown_sender_domain";}
		if($reject_non_fqdn_sender==1){$smtpd_sender_restrictions[]="reject_non_fqdn_sender";}
		if($smtpd_sender_restrictions_black<>null){$smtpd_sender_restrictions[]=$smtpd_sender_restrictions_black;}
	}
	
	if(!is_array($smtpd_sender_restrictions)){
		postconf("smtpd_sender_restrictions");
		return;
	}
	$final=@implode(",",$smtpd_sender_restrictions);
	postconf("smtpd_sender_restrictions",$final);
	postconf("smtpd_helo_restrictions",$final);
	
	
	
}

function smtpd_end_of_data_restrictions(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	if(!isset($GLOBALS["CLASS_USERS_MENUS"])){$users=new usersMenus();$GLOBALS["CLASS_USERS_MENUS"]=$users;}else{$users=$GLOBALS["CLASS_USERS_MENUS"];}
	$EnableArticaPolicyFilter=$sock->GET_INFO("EnableArticaPolicyFilter");
	$EnableCluebringer=$sock->GET_INFO("EnableCluebringer");
	
	$main=new maincf_multi("master");
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	$ENABLE_POSTFWD2=$array_filters["APP_POSTFWD2"];
	if(!is_numeric($ENABLE_POSTFWD2)){$ENABLE_POSTFWD2=0;}
	
	if($ENABLE_POSTFWD2==1){
		echo "Starting......: Postfix Postfwd2 is enabled\n";
		$smtpd_end_of_data_restrictions[]="check_policy_service inet:127.0.0.1:10040";
	}
	
	
	
	if($users->CLUEBRINGER_INSTALLED){
		if($EnableCluebringer==1){
			echo "Starting......: Postfix ClueBringer is enabled\n";
			$smtpd_end_of_data_restrictions[]="check_policy_service inet:127.0.0.1:13331";
		}
	}
	
	
	if($EnableArticaPolicyFilter==1){
		$smtpd_end_of_data_restrictions[]="check_policy_service inet:127.0.0.1:54423";
		
	}
	if(isset($smtpd_end_of_data_restrictions)){	
		if(!is_array($smtpd_end_of_data_restrictions)){
			system("{$GLOBALS["postconf"]} -e \"smtpd_end_of_data_restrictions =\" >/dev/null 2>&1");
			return;
		}
	}
	$final=@implode(",",$smtpd_end_of_data_restrictions);
	postconf("smtpd_end_of_data_restrictions",$final);
	
}

function BuildAllMyDomains(){
	$ldap=new clladp();
	$hash=$ldap->AllDomains();
	while (list ($num, $ligne) = each ($hash) ){	
		$ligne=trim($ligne);
		if($ligne==null){continue;}
		$doms[]="$ligne\tOK";
	}
	
	@file_put_contents("/etc/postfix/all_internal_domains",@implode("\n",$doms));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/all_internal_domains");
	
	
}
function BuildAllWhitelistedServer(){
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$f[]="{$ligne["ipaddr"]}\tOK";
		$f[]="{$ligne["hostname"]}\tOK";
		
		
	}		
	
	@file_put_contents("/etc/postfix/all_whitelisted_servers",@implode("\n",$f));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/all_whitelisted_servers");

}

function fix_postdrop_perms(){
	$unix=new unix();
	$postfix_bin=$unix->find_program("postfix");
	$chgrp_bin=$unix->find_program("chgrp");
	$killall_bin=$unix->find_program("killall");
	shell_exec("$postfix_bin stop 2>&1");
	shell_exec("$killall_bin -9 postdrop 2>&1");
	shell_exec("$chgrp_bin -R postdrop /var/spool/postfix/public 2>&1");
	shell_exec("$chgrp_bin -R postdrop /var/spool/postfix/maildrop/ 2>&1");
	shell_exec("$postfix_bin check 2>&1");
	shell_exec("$postfix_bin start 2>&1");
	
	
}

function postscreen($hostname=null){
	
	if($GLOBALS["EnablePostfixMultiInstance"]==1){
		echo "Starting......: PostScreen multiple instances, running for -> $hostname\n";
		shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix-multi.php --postscreen $hostname");
	}	
	
	$user=new usersMenus();
	if(!$user->POSTSCREEN_INSTALLED){echo "Starting......: PostScreen is not installed, you should upgrade to 2.8 postfix version\n";return;}
	$main=new maincf_multi("master","master");
	$EnablePostScreen=$main->GET("EnablePostScreen");
	if($EnablePostScreen<>1){echo "Starting......: PostScreen is not enabled\n";return;}
	echo "Starting......: PostScreen configuring....\n";
	if(!is_file("/etc/postfix/postscreen_access.cidr")){@file_put_contents("/etc/postfix/postscreen_access.cidr","#");}
	if(!is_file("/etc/postfix/postscreen_access.hosts")){@file_put_contents("/etc/postfix/postscreen_access.hosts"," ");}
	postconf("postscreen_access_list","permit_mynetworks,cidr:/etc/postfix/postscreen_access.cidr");
	
	
	$postscreen_bare_newline_action=$main->GET("postscreen_bare_newline_action");
	$postscreen_bare_newline_enable=$main->GET("postscreen_bare_newline_enable");
	
	$postscreen_bare_newline_ttl=$main->GET("postscreen_bare_newline_ttl");
	$postscreen_cache_cleanup_interval=$main->GET("postscreen_cache_cleanup_interval");
	$postscreen_cache_retention_time=$main->GET("postscreen_cache_retention_time");
	$postscreen_client_connection_count_limit=$main->GET("postscreen_client_connection_count_limit");
	$postscreen_pipelining_enable=$main->GET("postscreen_pipelining_enable");
	$postscreen_pipelining_action=$main->GET("postscreen_pipelining_action");
	$postscreen_pipelining_ttl=$main->GET("postscreen_pipelining_ttl");
	$postscreen_post_queue_limit=$main->GET("postscreen_post_queue_limit");
	$postscreen_pre_queue_limit=$main->GET("postscreen_pre_queue_limit");
	$postscreen_non_smtp_command_enable=$main->GET("postscreen_non_smtp_command_enable");
	$postscreen_non_smtp_command_action=$main->GET("postscreen_non_smtp_command_action");
	$postscreen_non_smtp_command_ttl=$main->GET("postscreen_non_smtp_command_ttl");
	$postscreen_forbidden_commands=$main->GET("postscreen_forbidden_command");
	$postscreen_dnsbl_action=$main->GET("postscreen_dnsbl_action");
	$postscreen_dnsbl_ttl=$main->GET("postscreen_dnsbl_ttl");
	$postscreen_dnsbl_threshold=$main->GET("postscreen_dnsbl_threshold");	
	
	
	if($postscreen_bare_newline_action==null){$postscreen_bare_newline_action="ignore";}
	if(!is_numeric($postscreen_bare_newline_enable)){$postscreen_bare_newline_enable="0";}
	if($postscreen_bare_newline_ttl==null){$postscreen_bare_newline_ttl="30d";}
	if($postscreen_cache_cleanup_interval==null){$postscreen_cache_cleanup_interval="12h";}
	if($postscreen_cache_retention_time==null){$postscreen_cache_retention_time="7d";}
	if($postscreen_client_connection_count_limit==null){$postscreen_client_connection_count_limit="50";}
	if($postscreen_pipelining_enable==null){$postscreen_pipelining_enable="0";}
	if($postscreen_pipelining_action==null){$postscreen_pipelining_action="ignore";}
	if($postscreen_pipelining_ttl==null){$postscreen_pipelining_ttl="30d";}			
	if($postscreen_post_queue_limit==null){$postscreen_post_queue_limit="100";}
	if($postscreen_pre_queue_limit==null){$postscreen_pre_queue_limit="100";}
	
	if($postscreen_non_smtp_command_enable==null){$postscreen_non_smtp_command_enable="0";}
	if($postscreen_non_smtp_command_action==null){$postscreen_non_smtp_command_action="drop";}
	if($postscreen_non_smtp_command_ttl==null){$postscreen_non_smtp_command_ttl="30d";}
	if($postscreen_forbidden_commands==null){$postscreen_forbidden_commands="CONNECT, GET, POST";}
	if($postscreen_dnsbl_action==null){$postscreen_dnsbl_action="ignore";}
	if($postscreen_dnsbl_action==null){$postscreen_dnsbl_action="ignore";}
	if($postscreen_dnsbl_ttl==null){$postscreen_dnsbl_ttl="1h";}
	if($postscreen_dnsbl_threshold==null){$postscreen_dnsbl_threshold="1";}
	
	if($postscreen_bare_newline_enable==1){$postscreen_bare_newline_enable="yes";}else{$postscreen_bare_newline_enable="no";}
	if($postscreen_pipelining_enable==1){$postscreen_pipelining_enable="yes";}else{$postscreen_pipelining_enable="no";}
	if($postscreen_non_smtp_command_enable==1){$postscreen_non_smtp_command_enable="yes";}else{$postscreen_non_smtp_command_enable="no";}
	
	
	postconf("postscreen_bare_newline_action",$postscreen_bare_newline_action);
	postconf("postscreen_bare_newline_enable",$postscreen_bare_newline_enable);
	postconf("postscreen_bare_newline_ttl",$postscreen_bare_newline_ttl);
	postconf("postscreen_cache_cleanup_interval",$postscreen_cache_cleanup_interval);
	postconf("postscreen_cache_retention_time",$postscreen_cache_retention_time);
	postconf("postscreen_client_connection_count_limit",$postscreen_client_connection_count_limit);
	postconf("postscreen_client_connection_count_limit",$postscreen_client_connection_count_limit);
	postconf("postscreen_pipelining_enable",$postscreen_pipelining_enable);
	postconf("postscreen_pipelining_action",$postscreen_pipelining_action);
	postconf("postscreen_pipelining_ttl",$postscreen_pipelining_ttl);
	postconf("postscreen_post_queue_limit",$postscreen_post_queue_limit);
	postconf("postscreen_pre_queue_limit",$postscreen_pre_queue_limit);
	postconf("postscreen_non_smtp_command_enable",$postscreen_non_smtp_command_enable);
	postconf("postscreen_non_smtp_command_action",$postscreen_non_smtp_command_action);
	postconf("postscreen_non_smtp_command_ttl",$postscreen_non_smtp_command_ttl);
	postconf("postscreen_forbidden_command",$postscreen_forbidden_commands);
	postconf("postscreen_dnsbl_action",$postscreen_dnsbl_action);
	postconf("postscreen_dnsbl_ttl",$postscreen_dnsbl_ttl);
	postconf("postscreen_dnsbl_threshold",$postscreen_dnsbl_threshold);
	postconf("postscreen_cache_map","btree:\$data_directory/postscreen_master_cache");
	
	
	
	
	$dnsbl_array=unserialize(base64_decode($main->GET_BIGDATA("postscreen_dnsbl_sites")));
	if(is_array($dnsbl_array)){
		while (list ($site, $threshold) = each ($dnsbl_array) ){if($site==null){continue;}$dnsbl_array_compiled[]="$site*$threshold";}
	}
		
	$final_dnsbl=null;
	if(is_array($dnsbl_array_compiled)){$final_dnsbl=@implode(",",$dnsbl_array_compiled);}
	postconf("postscreen_dnsbl_sites",$final_dnsbl);
	
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$nets[]="{$ligne["ipaddr"]}\tdunno";
		$hostsname[]="{$ligne["hostname"]}\tOK";
		
		
	}		

	
	$ldap=new clladp();
	$networks=$ldap->load_mynetworks();	
	if(is_array($networks)){
		while (list ($num, $ligne) = each ($networks) ){
			if($ligne==null){continue;}
			if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne)){
				$hostsname[]="$ligne\tOK";
			}else{
				$nets[]="$ligne\tdunno";
			}
		}
	}
	
	if(is_array($hostsname)){@file_put_contents("/etc/postfix/postscreen_access.hosts",@implode("\n",$hostsname));
	$postscreen_access=",hash:/etc/postfix/postscreen_access.hosts";}
	
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/postscreen_access.hosts >/dev/null 2>&1");
	
	if(is_array($nets)){@file_put_contents("/etc/postfix/postscreen_access.cidr",@implode("\n",$nets));}
	postconf("postscreen_access_list","permit_mynetworks,cidr:/etc/postfix/postscreen_access.cidr$postscreen_access");
	
	MasterCFBuilder();
	}
	
function MasterCF_DOMAINS_THROTTLE(){
	$main=new maincf_multi("master","master");
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));	
	
	$f=explode("\n",@file_get_contents("/etc/postfix/main.cf"));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#^[0-9]+_destination#",$line)){continue;}
		if(preg_match("#^[0-9]+_delivery_#",$line)){continue;}
		if(preg_match("#^[0-9]+_initial_#",$line)){continue;}
		$new[]=$line;
	}
	if($GLOBALS["VERBOSE"]){echo "MasterCF_DOMAINS_THROTTLE():: Cleaning main.cf done..\n";}
	@file_put_contents("/etc/postfix/main.cf",@implode("\n",$new));
	unset($new);
	
	
	if(!is_array($array)){
		if($GLOBALS["VERBOSE"]){echo "MasterCF_DOMAINS_THROTTLE():: Not An Array line ". __LINE__."\n";}
		return null;
	}
	
	while (list ($uuid, $conf) = each ($array) ){
		if($conf["ENABLED"]<>1){continue;}
		if(count($conf["DOMAINS"])==0){continue;}
		$maps=array();
		if($conf["transport_destination_concurrency_failed_cohort_limit"]==null){$conf["transport_destination_concurrency_failed_cohort_limit"]=1;}
		if($conf["transport_delivery_slot_loan"]==null){$conf["transport_delivery_slot_loan"]=3;}
		if($conf["transport_delivery_slot_discount"]==null){$conf["transport_delivery_slot_discount"]=50;}
		if($conf["transport_delivery_slot_cost"]==null){$conf["transport_delivery_slot_cost"]=5;}
		if($conf["transport_extra_recipient_limit"]==null){$conf["transport_extra_recipient_limit"]=1000;}
		if($conf["transport_initial_destination_concurrency"]==null){$conf["transport_initial_destination_concurrency"]=5;}
		if($conf["transport_destination_recipient_limit"]==null){$conf["transport_destination_recipient_limit"]=50;}		
		if($conf["transport_destination_concurrency_limit"]==null){$conf["transport_destination_concurrency_limit"]=20;}
		if($conf["transport_destination_rate_delay"]==null){$conf["transport_destination_rate_delay"]="0s";}
		if(!is_numeric($conf["default_process_limit"])){$conf["default_process_limit"]=100;}
		$moinso["{$uuid}_destination_concurrency_failed_cohort_limit"]="{$conf["transport_destination_concurrency_failed_cohort_limit"]}";
		$moinso["{$uuid}_delivery_slot_loan"]="{$conf["transport_delivery_slot_loan"]}";
		$moinso["{$uuid}_delivery_slot_discount"]="{$conf["transport_delivery_slot_discount"]}";
		$moinso["{$uuid}_delivery_slot_cost"]="{$conf["transport_delivery_slot_cost"]}";
		$moinso["{$uuid}_initial_destination_concurrency"]="{$conf["transport_initial_destination_concurrency"]}";
		$moinso["{$uuid}_destination_recipient_limit"]="{$conf["transport_destination_recipient_limit"]}";
		$moinso["{$uuid}_destination_concurrency_limit"]="{$conf["transport_destination_concurrency_limit"]}";
		$moinso["{$uuid}_destination_rate_delay"]="{$conf["transport_destination_rate_delay"]}";
		
		
		$instances[]="\n# THROTTLE {$conf["INSTANCE_NAME"]}\n$uuid\tunix\t-\t-\tn\t-\t{$conf["default_process_limit"]}\tsmtp";
		while (list ($domain, $null) = each ($conf["DOMAINS"]) ){$maps[$domain]="$uuid:";}
		while (list ($a, $b) = each ($maps) ){$maps_final[]="$a\t$b";}
	}
	
	if($GLOBALS["VERBOSE"]){echo "MasterCF_DOMAINS_THROTTLE():: ". count($moinso)." main.cf command lines\n";}
	if(is_array($moinso)){
		while (list ($key, $val) = each ($moinso) ){
			postconf($key,$val);
		}
	}
	
	if(!is_array($instances)){return null;}
	@file_put_contents("/etc/postfix/transport.throttle",@implode("\n",$maps_final)."\n");
	return @implode("\n",$instances)."\n";
	
	
}





function MasterCFBuilder($restart_service=false){
	$smtp_ssl=null;
	if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	if(!is_object($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');
	$PostfixEnableMasterCfSSL=$sock->GET_INFO("PostfixEnableMasterCfSSL");
	$ArticaFilterMaxProc=$sock->GET_INFO("ArticaFilterMaxProc");
	$PostfixEnableSubmission=$sock->GET_INFO("PostfixEnableSubmission");
	$EnableASSP=$sock->GET_INFO('EnableASSP');
	
	$user=new usersMenus();
	$main=new maincf_multi("master","master");
	$EnablePostScreen=$main->GET("EnablePostScreen");
	$postscreen_line=null;
	$tlsproxy=null;
	$dnsblog=null;
	$re_cleanup_infos=null;
	$smtp_submission=null;
	$pre_cleanup_addons=null;
	
	
	
	if($EnablePostScreen==null){$EnablePostScreen=0;}	
	if(!$user->POSTSCREEN_INSTALLED){$EnablePostScreen=0;}
	
	if($EnablePostScreen==1){$PostfixEnableSubmission=1;}
	
	
	$ADD_PRECLEANUP=false;
	$TLSSET=false;
	
	if($GLOBALS["EnablePostfixMultiInstance"]==1){
		$EnableAmavisDaemon=0;
		$PostfixEnableMasterCfSSL=0;
	}
	
	if($EnableAmavisInMasterCF==null){$EnableAmavisInMasterCF=0;}
	if($PostfixEnableSubmission==null){$PostfixEnableSubmission=0;}
	if($EnableAmavisDaemon==0){$EnableAmavisInMasterCF=0;}
	if($ArticaFilterMaxProc==null){$ArticaFilterMaxProc=20;}
	if($EnableASSP==null){$EnableASSP=0;}
	
	
	shell_exec("{$GLOBALS["postconf"]} -e \"artica-filter_destination_recipient_limit = 1\" >/dev/null 2>&1");
	if($EnableArticaSMTPFilter==0){shell_exec("{$GLOBALS["postconf"]} -e \"content_filter =\" >/dev/null 2>&1");}
		

	
	if($EnableAmavisInMasterCF==1){
		$MasterCFAmavisInstancesCount=$sock->GET_INFO("MasterCFAmavisInstancesCount");
		if(!is_numeric($MasterCFAmavisInstancesCount)){
				include_once(dirname(__FILE__).'/ressources/class.amavis.inc');
				$amavisClass=new amavis();
				$max_servers=$amavisClass->main_array["BEHAVIORS"]["max_servers"];
				$MasterCFAmavisInstancesCount=$max_servers-1;	
		}
		if($MasterCFAmavisInstancesCount==0){$MasterCFAmavisInstancesCount="-";}
		$ADD_PRECLEANUP=true;
		echo "Starting......: Amavis is enabled using post-queue mode\n";
		echo "Starting......: artica-filter enable=$EnableArticaSMTPFilter\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"content_filter = amavis:[127.0.0.1]:10024\" >/dev/null 2>&1");
		if($EnableArticaSMTPFilter==1){
			$artica_filter_amavis_option=" -o content_filter=artica-filter:";
			$amavis_cleanup_infos  =" -o cleanup_service_name=pre-cleanup";
			echo "Starting......: Artica-filter max process: $ArticaFilterMaxProc\n";	
		}
		if($EnableArticaSMTPFilter==0){$artica_filter_amavis_option=" -o content_filter=";}
		
		echo "Starting......: Amavis max process: $MasterCFAmavisInstancesCount\n";	
		
		$amavis[]="amavis\tunix\t-\t-\t-\t-\t$MasterCFAmavisInstancesCount\tsmtp";
		if($amavis_cleanup_infos<>null){$amavis[]=$amavis_cleanup_infos;}
		$amavis[]=" -o smtp_data_done_timeout=1200";
		$amavis[]=" -o smtp_send_xforward_command=yes";
		$amavis[]=" -o disable_dns_lookups=yes";
		$amavis[]=" -o smtp_generic_maps=";
		$amavis[]=" -o smtpd_sasl_auth_enable=no"; 
		$amavis[]=" -o smtpd_use_tls=no";
		$amavis[]=" -o max_use=20";				
		$amavis[]="";
		$amavis[]="";
		
		$amavis[]="127.0.0.1:10025\tinet\tn\t-\tn\t-\t-\tsmtpd";
		if($amavis_cleanup_infos<>null){$amavis[]=$amavis_cleanup_infos;}
		if($artica_filter_amavis_option<>null){$amavis[]=$artica_filter_amavis_option;}
		$amavis[]=" -o local_recipient_maps=";
		$amavis[]=" -o relay_recipient_maps=";
		$amavis[]=" -o smtpd_restriction_classes=";
		$amavis[]=" -o smtpd_client_restrictions=";
		$amavis[]=" -o smtpd_helo_restrictions=";
		$amavis[]=" -o smtpd_sender_restrictions=";
		$artica[]=" -o smtpd_end_of_data_restrictions=";
		$amavis[]=" -o smtp_generic_maps=";
		$amavis[]=" -o smtpd_recipient_restrictions=permit_mynetworks,reject";
		$amavis[]=" -o mynetworks=127.0.0.0/8";
		$amavis[]=" -o mynetworks_style=host";
		$amavis[]=" -o strict_rfc821_envelopes=yes";
		$amavis[]=" -o smtpd_error_sleep_time=0";
		$amavis[]=" -o smtpd_soft_error_limit=1001";
		$amavis[]=" -o smtpd_hard_error_limit=1000";
		$amavis[]=" -o receive_override_options=no_header_body_checks";	
		$amavis[]="	-o smtpd_sasl_auth_enable=no"; 
		$amavis[]="	-o smtpd_use_tls=no";
		$master_amavis=@implode("\n",$amavis);

	}ELSE{
		$master_amavis="";
		if($EnableArticaSMTPFilter==1){
			$ADD_PRECLEANUP=true;
			echo "Starting......: Enable Artica-filter globaly\n"; 
			echo "Starting......: Artica-filter max process: $ArticaFilterMaxProc\n";	
			shell_exec("{$GLOBALS["postconf"]} -e \"content_filter = artica-filter:\" >/dev/null 2>&1");
		}else{
			shell_exec("{$GLOBALS["postconf"]} -e \"content_filter =\" >/dev/null 2>&1");
		}
	}		
	
	if($ADD_PRECLEANUP){
		echo "Starting......: Enable pre-cleanup service...\n";
		$pre_cleanup_addons=" -o smtp_generic_maps= -o canonical_maps= -o sender_canonical_maps= -o recipient_canonical_maps= -o masquerade_domains= -o recipient_bcc_maps= -o sender_bcc_maps=";
		$re_cleanup_infos  =" -o cleanup_service_name=pre-cleanup";
	}	
	
	
	if($PostfixEnableMasterCfSSL==1){
		echo "Starting......: Enabling SSL (465 port)\n";
		SetTLS();
		$TLSSET=true;
		$SSL_INSTANCE[]="smtps\tinet\tn\t-\tn\t-\t-\tsmtpd";
		if($re_cleanup_infos<>null){$SSL_INSTANCE[]=$re_cleanup_infos;}
		$SSL_INSTANCE[]=" -o smtpd_tls_wrappermode=yes";
		$SSL_INSTANCE[]=" -o smtpd_delay_reject=yes";
		$SSL_INSTANCE[]=" -o smtpd_client_restrictions=permit_mynetworks,permit_sasl_authenticated,reject\n";
		$SSL_INSTANCE[]=" -o smtpd_sender_restrictions=permit_sasl_authenticated,reject";
		$SSL_INSTANCE[]=" -o smtpd_helo_restrictions=permit_sasl_authenticated,reject";
		$SSL_INSTANCE[]=" -o smtpd_recipient_restrictions=permit_sasl_authenticated,reject";		
		$smtp_ssl=@implode("\n",$SSL_INSTANCE);
	}else{
		echo "Starting......: SSL (465 port) Disabled\n";
	}

	if($PostfixEnableSubmission==1){
		echo "Starting......: Enabling submission (587 port)\n";
		if(!$TLSSET){SetTLS();}
		$TLSSET=true;
		$SUBMISSION_INSTANCE[]="submission\tinet\tn\t-\tn\t-\t-\tsmtpd";
		if($re_cleanup_infos<>null){$SUBMISSION_INSTANCE[]=$re_cleanup_infos;}
		$SUBMISSION_INSTANCE[]=" -o smtpd_etrn_restrictions=reject";
		$SUBMISSION_INSTANCE[]=" -o smtpd_enforce_tls=yes";
		$SUBMISSION_INSTANCE[]=" -o smtpd_sasl_auth_enable=yes";
		$SUBMISSION_INSTANCE[]=" -o smtpd_delay_reject=yes";
		$SUBMISSION_INSTANCE[]=" -o smtpd_client_restrictions=permit_sasl_authenticated,reject";
		$SUBMISSION_INSTANCE[]=" -o smtpd_sender_restrictions=permit_sasl_authenticated,reject";
		$SUBMISSION_INSTANCE[]=" -o smtpd_helo_restrictions=permit_sasl_authenticated,reject";
		$SUBMISSION_INSTANCE[]=" -o smtpd_recipient_restrictions=permit_sasl_authenticated,reject";
		$SUBMISSION_INSTANCE[]=" -o smtp_generic_maps=";
		$SUBMISSION_INSTANCE[]=" -o sender_canonical_maps=";
		$smtp_submission=@implode("\n",$SUBMISSION_INSTANCE);
		
	}else{
		echo "Starting......: submission (587 port) Disabled\n";
	}
	
	
	$postfix_listen_port="smtp";
	$postscreen_listen_port="smtp";
	$smtp_in_proto="inet";
	$smtp_private="n";
	
	
	if($EnableASSP==1){
		echo "Starting......: ASSP is enabled change postfix listen port to 127.0.0.1:26\n";
		$postfix_listen_port="127.0.0.1:6000";
		$postscreen_listen_port="127.0.0.1:6000";
	}
	
	
	if($EnablePostScreen==1){
		echo "Starting......: PostScreen is enabled, users should use 587 port to send mails internally\n"; 
		$smtp_in_proto="pass";
		$smtp_private="-";
		if($postfix_listen_port=="smtp"){$postfix_listen_port="smtpd";}
		$postscreen_line="$postscreen_listen_port\tinet\tn\t-\tn\t-\t1\tpostscreen -o soft_bounce=yes";
		$tlsproxy="tlsproxy\tunix\t-\t-\tn\t-\t0\ttlsproxy";
		$dnsblog="dnsblog\tunix\t-\t-\tn\t-\t0\tdnsblog";
		}else{
			echo "Starting......: PostScreen is disabled\n";
		}
	
if($GLOBALS["VERBOSE"]){echo "Starting......: run MasterCF_DOMAINS_THROTTLE()\n";}	
$smtp_throttle=MasterCF_DOMAINS_THROTTLE();

// http://www.ijs.si/software/amavisd/README.postfix.html	
$conf[]="#";
$conf[]="# Postfix master process configuration file.  For details on the format";
$conf[]="# of the file, see the master(5) manual page (command: \"man 5 master\").";
$conf[]="#";
$conf[]="# ==========================================================================";
$conf[]="# service type  private unpriv  chroot  wakeup  maxproc command + args";
$conf[]="#               (yes)   (yes)   (yes)   (never) (100)";
$conf[]="# ==========================================================================";
if($postscreen_line<>null){$conf[]=$postscreen_line;}
if($tlsproxy<>null){$conf[]=$tlsproxy;}
if($dnsblog<>null){$conf[]=$dnsblog;}
$conf[]="$postfix_listen_port\t$smtp_in_proto\t$smtp_private\t-\tn\t-\t-\tsmtpd$re_cleanup_infos";
if($smtp_ssl<>null){$conf[]=$smtp_ssl;}
if($smtp_submission<>null){$conf[]=$smtp_submission;}
if($smtp_throttle<>null){$conf[]=$smtp_throttle;}

$conf[]="pickup\tfifo\tn\t-\tn\t60\t1\tpickup$re_cleanup_infos";
$conf[]="cleanup\tunix\tn\t-\tn\t-\t0\tcleanup";
$conf[]="pre-cleanup\tunix\tn\t-\tn\t-\t0\tcleanup$pre_cleanup_addons";
$conf[]="qmgr\tfifo\tn\t-\tn\t300\t1\tqmgr";
$conf[]="tlsmgr\tunix\t-\t-\tn\t1000?\t1\ttlsmgr";
$conf[]="rewrite\tunix\t-\t-\tn\t-\t-\ttrivial-rewrite";
$conf[]="bounce\tunix\t-\t-\tn\t-\t0\tbounce";
$conf[]="defer\tunix\t-\t-\tn\t-\t0\tbounce";
$conf[]="trace\tunix\t-\t-\tn\t-\t0\tbounce";
$conf[]="verify\tunix\t-\t-\tn\t-\t1\tverify";
$conf[]="flush\tunix\tn\t-\tn\t1000?\t0\tflush";
$conf[]="proxymap\tunix\t-\t-\tn\t-\t-\tproxymap";
$conf[]="proxywrite\tunix\t-\t-\tn\t-\t1\tproxymap";
$conf[]="smtp\tunix\t-\t-\tn\t-\t-\tsmtp";

$conf[]="relay\tunix\t-\t-\tn\t-\t-\tsmtp -o fallback_relay=";
$conf[]="showq\tunix\tn\t-\tn\t-\t-\tshowq";
$conf[]="error\tunix\t-\t-\tn\t-\t-\terror";
$conf[]="discard\tunix\t-\t-\tn\t-\t-\tdiscard";
$conf[]="local\tunix\t-\tn\tn\t-\t-\tlocal";
$conf[]="virtual\tunix\t-\tn\tn\t-\t-\tvirtual";
$conf[]="lmtp\tunix\t-\t-\tn\t-\t-\tlmtp";
$conf[]="anvil\tunix\t-\t-\tn\t-\t1\tanvil";
$conf[]="scache\tunix\t-\t-\tn\t-\t1\tscache";
$conf[]="scan\tunix\t-\t-\tn\t\t-\t10\tsm -v";
$conf[]="maildrop\tunix\t-\tn\tn\t-\t-\tpipe ";
$conf[]="retry\tunix\t-\t-\tn\t-\t-\terror ";
$conf[]="uucp\tunix\t-\tn\tn\t-\t-\tpipe flags=Fqhu user=uucp argv=uux -r -n -z -a\$sender - \$nexthop!rmail (\$recipient)";
$conf[]="ifmail\tunix\t-\tn\tn\t-\t-\tpipe flags=F user=ftn argv=/usr/lib/ifmail/ifmail -r \$nexthop (\$recipient)";
$conf[]="bsmtp\tunix\t-\tn\tn\t-\t-\tpipe flags=Fq. user=bsmtp argv=/usr/lib/bsmtp/bsmtp -t\$nexthop -f\$sender \$recipient";
$conf[]="mailman\tunix\t-\tn\tn\t-\t-\tpipe flags=FR user=mail:mail argv=/etc/mailman/postfix-to-mailman.py \${nexthop} \${mailbox}";
$conf[]="artica-whitelist\tunix\t-\tn\tn\t-\t-\tpipe flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --white";
$conf[]="artica-blacklist\tunix\t-\tn\tn\t-\t-\tpipe flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --black";
$conf[]="artica-reportwbl\tunix\t-\tn\tn\t-\t-\tpipe flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --report";
$conf[]="artica-reportquar\tunix\t-\tn\tn\t-\t-\tpipe flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --quarantines";
$conf[]="artica-spam\tunix\t-\tn\tn\t-\t-\tpipe flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --spam";
$conf[]="zarafa\tunix\t-\tn\tn\t-\t-\tpipe	user=mail argv=/usr/local/bin/zarafa-dagent \${user}";
$conf[]="artica-filter\tunix\t-\tn\tn\t-\t$ArticaFilterMaxProc\tpipe flags=FOh  user=www-data argv=/usr/share/artica-postfix/exec.artica-filter.php -f \${sender} --  -s \${sender} -r \${recipient} -c \${client_address}";
$conf[]="";
$conf[]=$master_amavis;
$conf[]="";
$conf[]="127.0.0.1:33559\tinet\tn\t-\tn\t-\t-\tsmtpd";
$conf[]="    -o notify_clases=protocol,resource,software";
$conf[]="    -o header_checks=";
$conf[]="    -o content_filter=";
$conf[]="    -o smtpd_restriction_classes=";
$conf[]="    -o smtpd_delay_reject=no";
$conf[]="    -o smtpd_client_restrictions=permit_mynetworks,reject";
$conf[]="    -o smtpd_helo_restrictions=";
$conf[]="    -o smtpd_sender_restrictions=";
$conf[]="    -o smtpd_recipient_restrictions=permit_mynetworks,reject";
$conf[]="    -o smtpd_data_restrictions=reject_unauth_pipelining";
$conf[]="    -o smtpd_end_of_data_restrictions=";
$conf[]="    -o mynetworks=127.0.0.0/8";
$conf[]="    -o strict_rfc821_envelopes=yes";
$conf[]="    -o smtpd_error_sleep_time=0";
$conf[]="    -o smtpd_soft_error_limit=1001";
$conf[]="    -o smtpd_hard_error_limit=1000";
$conf[]="    -o smtpd_client_connection_count_limit=0";
$conf[]="    -o smtpd_client_connection_rate_limit=0";
$conf[]="    -o receive_override_options=no_header_body_checks,no_unknown_recipient_checks";
$conf[]="    -o smtp_send_xforward_command=yes";
$conf[]="    -o disable_dns_lookups=yes";
$conf[]="    -o local_header_rewrite_clients=";
$conf[]="    -o smtp_generic_maps=";
$conf[]="    -o sender_canonical_maps=";
$conf[]="    -o smtpd_milters=";
$conf[]="    -o smtpd_sasl_auth_enable=no";
$conf[]="    -o smtpd_use_tls=no";	
$conf[]="";	
$conf[]="";
@file_put_contents("/etc/postfix/master.cf",@implode("\n",$conf));
echo "Starting......: master.cf done\n";
if($GLOBALS["RELOAD"]){shell_exec("/usr/sbin/postfix reload");}	

if($restart_service){
	shell_exec("{$GLOBALS["postfix"]} stop");
	shell_exec("{$GLOBALS["postfix"]} start");
}

}


function postfix_templates(){
	$mainTPL=new bounces_templates();
	$main=new maincf_multi("master");
	$conf=null;
	
	$double_bounce_sender=$main->GET("double_bounce_sender");
	$address_verify_sender=$main->GET("address_verify_sender");
	$twobounce_notice_recipient=$main->GET("2bounce_notice_recipient");
	$error_notice_recipient=$main->GET("error_notice_recipient");
	$delay_notice_recipient=$main->GET("delay_notice_recipient");
	$empty_address_recipient=$main->GET("empty_address_recipient");
	
	$sock=new sockets();
	$PostfixPostmaster=$sock->GET_INFO("PostfixPostmaster");
	if(trim($PostfixPostmaster)==null){$PostfixPostmaster="postmaster";}
	
	if($double_bounce_sender==null){$double_bounce_sender="double-bounce";};
	if($address_verify_sender==null){$address_verify_sender="\$double_bounce_sender";}
	if($twobounce_notice_recipient==null){$twobounce_notice_recipient="postmaster";}
	if($error_notice_recipient==null){$error_notice_recipient=$PostfixPostmaster;}
	if($delay_notice_recipient==null){$delay_notice_recipient=$PostfixPostmaster;}
	if($empty_address_recipient==null){$empty_address_recipient=$PostfixPostmaster;}	
	if(is_array($main->templates_array)){
		while (list ($template, $nothing) = each ($main->templates_array) ){
			$array=unserialize(base64_decode($main->GET_BIGDATA($template)));
			if(!is_array($array)){$array=$mainTPL->templates_array[$template];}
				$tp=explode("\n",$array["Body"]);
				$Body=null;
				while (list ($a, $line) = each ($tp) ){if(trim($line)==null){continue;}$Body=$Body.$line."\n";}
				$conf=$conf ."\n$template = <<EOF\n";
				$conf=$conf ."Charset: {$array["Charset"]}\n";
				$conf=$conf ."From:  {$array["From"]}\n";
				$conf=$conf ."Subject: {$array["Subject"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."$Body";
				$conf=$conf ."\n\n";
				$conf=$conf ."EOF\n";
				
			}
	}


	@file_put_contents("/etc/postfix/bounce.template.cf",$conf);
	
	$notify_class=unserialize(base64_decode($main->GET_BIGDATA("notify_class")));
	if($notify_class["notify_class_software"]==1){$not[]="software";}
	if($notify_class["notify_class_resource"]==1){$not[]="resource";}
	if($notify_class["notify_class_policy"]==1){$not[]="policy";}
	if($notify_class["notify_class_delay"]==1){$not[]="delay";}
	if($notify_class["notify_class_2bounce"]==1){$not[]="2bounce";}
	if($notify_class["notify_class_bounce"]==1){$not[]="bounce";}
	if($notify_class["notify_class_protocol"]==1){$not[]="protocol";}
	
	
	postconf("notify_class",@implode(",",$not));
	postconf("double_bounce_sender","$double_bounce_sender");
	postconf("address_verify_sender","$address_verify_sender");	
	postconf("2bounce_notice_recipient",$twobounce_notice_recipient);	
	postconf("error_notice_recipient",$error_notice_recipient);	
	postconf("delay_notice_recipient",$delay_notice_recipient);
	postconf("empty_address_recipient",$empty_address_recipient);
	postconf("bounce_template_file","/etc/postfix/bounce.template.cf");				

	}


function memory(){
	$unix=new unix();
	$sock=new sockets();
	if($GLOBALS["VERBOSE"]){$cmd_verbose=" --verbose";}
	$PostFixEnableQueueInMemory=$sock->GET_INFO("PostFixEnableQueueInMemory");
	$PostFixQueueInMemory=$sock->GET_INFO("PostFixQueueInMemory");
	$directory="/var/spool/postfix";
	if($PostFixEnableQueueInMemory==1){
		echo "Starting......: Postfix Queue in memory is enabled for {$PostFixQueueInMemory}M\n";
		echo "Starting......: Postfix executing exec.postfix-multi.php\n";
		shell_exec(LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.postfix-multi.php --instance-memory master $PostFixQueueInMemory$cmd_verbose");
		return;
	}else{
		$MOUNTED_TMPFS_MEM=$unix->MOUNTED_TMPFS_MEM($directory);
		if($MOUNTED_TMPFS_MEM>0){
			shell_exec(LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.postfix-multi.php --instance-memory-kill master$cmd_verbose");
			return;
		}
		echo "Starting......: Postfix Queue in memory is not enabled\n"; 
	}	
	
}

function repair_locks(){
	echo "Starting......: Stopping postfix\n";
	shell_exec("{$GLOBALS["postfix"]} stop");
	$unix=new unix();
	$daemon_directory=$unix->POSTCONF_GET("daemon_directory");
	$queue_directory=$unix->POSTCONF_GET("queue_directory");
	echo "Starting......: Daemon directory: $daemon_directory\n";
	echo "Starting......: Queue directory.: $queue_directory\n";
	$pid=$unix->PIDOF("$daemon_directory/master",true);
	echo "Starting......: Process \"$daemon_directory/master\" PID:\"$pid\"\n";
	
	for($i=0;$i<10;$i++){
		if(is_numeric($pid)){
			if($pid>5){
				echo "Starting......: Killing bad pid $pid\n";
				shell_exec("/bin/kill -9 $pid");
				sleep(1);
				
			}
		}else{
			echo "Starting......: No $daemon_directory/master ghost process\n";
			break;
		}
		$pid=$unix->PIDOF("$daemon_directory/master");
		
		echo "Starting......: Process \"$daemon_directory/master\" PID:\"$pid\"\n";
	}
	
	if(file_exists("$daemon_directory/master.lock")){
		echo "Starting......: Delete $daemon_directory/master.lock\n";
		@unlink("$daemon_directory/master.lock");
	
	}
	if(file_exists("$queue_directory/pid/master.pid")){
		echo "Starting......: Delete $queue_directory/pid/master.pid\n";
		@unlink("$queue_directory/pid/master.pid");
	}
	
	if(file_exists("$queue_directory/pid/inet.127.0.0.1:33559")){
		echo "Starting......: $queue_directory/pid/inet.127.0.0.1:33559\n";
		@unlink("$queue_directory/pid/inet.127.0.0.1:33559");
	}
	
	
	echo "Starting......: Starting postfix\n";
	exec("{$GLOBALS["postfix"]} start -v 2>&1",$results);
	while (list ($template, $nothing) = each ($results) ){echo "Starting......: Starting postfix $nothing\n";}
}

function postconf($key,$value=null){
	system("{$GLOBALS["postconf"]} -e \"$key = $value\" >/dev/null 2>&1");
	
}


?>