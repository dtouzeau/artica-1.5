<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;$GLOBALS["RESTART"]=true;}
if($argv[1]=="--kinit"){kinit_config();exit;}
if($argv[1]=="--DirectorySize"){DirectorySize();exit;}
if($argv[1]=="--cyrusadm-ad"){ExtractCyrusAdmAD();exit;}
if($argv[1]=="--imaps-failed"){cyrus_ssl_error();exit;}
if($argv[1]=="--DB_CONFIG"){DB_CONFIG();exit;}




function ExtractCyrusAdmAD(){
	$sock=new sockets();
	$CyrusToAD=$sock->GET_INFO("CyrusToAD");
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	if($CyrusToAD==null){$CyrusToAD=0;}
	@unlink("/etc/artica-postfix/CyrusAdmPlus");
	if($CyrusToAD==0){return;}
	$array=unserialize(base64_decode($sock->GET_INFO("CyrusToADConfig")));
	if($EnableSambaActiveDirectory==1){
		$newconf=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
		$array["domain"]=$newconf["ADDOMAIN"];
		$array["servername"]=$newconf["ADSERVER"];
		$array["admin"]=$newconf["ADADMIN"];
		$array["password"]=$newconf["PASSWORD"];
	}

	echo "Starting......: cyrus-imapd new Active Directory Administrator ({$array["admin"]})\n";
	@file_put_contents("/etc/artica-postfix/CyrusAdmPlus",$array["admin"]);
	
}


function kinit_config(){
	
	
	$sock=new sockets();
	$EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
	if(!is_numeric("$EnableKerbAuth")){$EnableKerbAuth=0;}	
	if($EnableKerbAuth==1){echo "Enable Kerberos authentification is enabled, Aborting\n";}
	$CyrusToAD=$sock->GET_INFO("CyrusToAD");
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if(!is_numeric($EnableSambaActiveDirectory)){$EnableSambaActiveDirectory=0;}
	if($CyrusToAD==null){$CyrusToAD=0;}
	if($CyrusToAD==0){DisablePamd();return;}
	EnablePamd();
	$array=unserialize(base64_decode($sock->GET_INFO("CyrusToADConfig")));
	if($EnableSambaActiveDirectory==1){
		$newconf=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
		$array["domain"]=$newconf["ADDOMAIN"];
		$array["servername"]=$newconf["ADSERVER"];
		$array["admin"]=$newconf["ADADMIN"];
		$array["password"]=$newconf["PASSWORD"];
	}
	
	
	
	
	$default_realm=strtoupper($array["domain"]);
	$servername=strtolower($array["servername"]);
	
$f[]="[logging]";
$f[]="	default = FILE:/var/log/krb5libs.log";
$f[]="	kdc = FILE:/var/log/krb5kdc.log";
$f[]="	admin_server = FILE:/var/log/kadmind.log";
$f[]="[libdefaults]";
$f[]="	clockskew = 300";
$f[]="	ticket_lifetime = 24h";
$f[]="	forwardable = yes";
$f[]="	default_realm = $default_realm";
$f[]="[realms]";
$f[]="	$default_realm = {";
$f[]="		kdc = $servername";
$f[]="		default_domain = $default_realm";
$f[]="		kpasswd_server = $servername";
$f[]="}";
$f[]="";
$f[]="[domain_realm]";
$f[]="	.$default_realm = $default_realm";
$f[]="[appdefaults]";
$f[]="pam = {";
$f[]="	debug = false";
$f[]="	ticket_lifetime = 36000";
$f[]="	renew_lifetime = 36000";
$f[]="	forwardable = true";
$f[]="	krb4_convert = false";
$f[]="}";
$f[]="";
	
@file_put_contents("/etc/krb5.conf",@implode("\n",$f));	
RunKinit($array["admin"]."@".strtoupper($array["domain"]),$array["password"]);
if($GLOBALS["RELOAD"]){
	shell_exec("/etc/init.d/artica-postfix restart saslauthd");
}
	
}

function EnablePamd(){
$f[]="# PAM configuration file for Cyrus IMAP service";
$f[]="# \$Id: imap.pam 5 2005-03-12 23:19:45Z sven $";
$f[]="#";
$f[]="# If you want to use Cyrus in a setup where users don't have";
$f[]="# accounts on the local machine, you'll need to make sure";
$f[]="# you use something like pam_permit for account checking.";
$f[]="#";
$f[]="# Remember that SASL (and therefore Cyrus) accesses PAM"; 
$f[]="# modules through saslauthd, and that SASL can only deal with";
$f[]="# plaintext passwords if PAM is used.";
$f[]="#";
$f[]="auth     sufficient pam_krb5.so no_user_check validate";
$f[]="account  sufficient pam_permit.so";
@file_put_contents("/etc/pam.d/imap",@implode("\n",$f));
@file_put_contents("/etc/pam.d/smtp",@implode("\n",$f));


}

function DisablePamd(){
	
$f[]="# PAM configuration file for Cyrus IMAP service";
$f[]="# \$Id: imap.pam 5 2005-03-12 23:19:45Z sven $";
$f[]="#";
$f[]="# If you want to use Cyrus in a setup where users don't have";
$f[]="# accounts on the local machine, you'll need to make sure";
$f[]="# you use something like pam_permit for account checking.";
$f[]="#";
$f[]="# Remember that SASL (and therefore Cyrus) accesses PAM"; 
$f[]="# modules through saslauthd, and that SASL can only deal with";
$f[]="# plaintext passwords if PAM is used.";
$f[]="#";
$f[]="@include common-auth";
$f[]="@include common-account";
@file_put_contents("/etc/pam.d/imap",@implode("\n",$f));
@unlink("/etc/pam.d/smtp");
}


function RunKinit($username,$password){
$unix=new unix();
$kinit=$unix->find_program("kinit");
$klist=$unix->find_program("klist");
$echo=$unix->find_program("echo");
if(!is_file($kinit)){logskinit("Unable to stat kinit");return;}

exec("$klist 2>&1",$res);
$line=@implode("",$res);


if(strpos($line,"No credentials cache found")>0){
	unset($res);
	logskinit($line." -> initialize..");
	exec("$echo \"$password\"|$kinit {$username} 2>&1",$res);
	while (list ($num, $a) = each ($res) ){	
		if(preg_match("#Password for#",$a,$re)){unset($res[$num]);}
	}	
	$line=@implode("",$res);	
	if(strlen(trim($line))>0){
		logskinit($line." -> Failed..");
		return;
	}
	unset($res);
	exec("$klist 2>&1",$res);	
}

while (list ($num, $a) = each ($res) ){	if(preg_match("#Default principal:(.+)#",$a,$re)){logskinit(trim($re[1])." -> success");break;}}	
	

	
}

function logskinit($text=null){
	$file="/var/log/artica-postfix/kinit.log";
	@mkdir(dirname($file));
	$logFile=$file;
	if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   	if (is_file($logFile)) { 
   		$size=filesize($logFile);
   		if($size>1000000){unlink($logFile);}
   	}
   	echo "$text\n";
   	$logFile=str_replace("//","/",$logFile);
	$f = @fopen($logFile, 'a');
	$date=date("Y-m-d H:i:s");
	@fwrite($f, "$date $text\n");
	@fclose($f);
}


function cyrus_ssl_error(){
	$unix=new unix();
	$users=new usersMenus();
	$tail=$unix->find_program("tail");
	$grep=$unix->find_program("grep");
	
	if(!is_file($users->maillog_path)){return null;}
	
	exec("$tail -n 800 $users->maillog_path|$grep imaps 2>&1",$results);
	if(count($results)>1){
		$text="Artica has detected an error in cyrus when connecting to the SSL imap port\n";
		$text=$text."You should rebuild your ssl certificate or try to investigate on the events below:\n-------------------------\n";
		$text=$text.@implode("\n",$results); 
		echo $text."\n";
		$unix->send_email_events("cyrus-imap: IMAP SSL error");
		
	}
	
	
}

function DB_CONFIG(){
	$unix=new unix();
	$configdirectory=$unix->IMAPD_GET("configdirectory")."/db";
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("CyrusDBConfig")));
	$EnableCyrusDBConfig=$sock->GET_INFO("EnableCyrusDBConfig");
	
	if($datas["set_cachesize"]==null){$datas["set_cachesize"]="2 524288000 1";}
	if(!is_numeric($datas["set_lg_regionmax"])){$datas["set_lg_regionmax"]="1048576";}
	if(!is_numeric($datas["set_lg_bsize"])){$datas["set_lg_bsize"]="2097152";}
	if(!is_numeric($datas["set_lg_max"])){$datas["set_lg_max"]="4194304";}
	if($EnableCyrusDBConfig<>1){
		if(is_file("$configdirectory/DB_CONFIG")){@unlink("$configdirectory/DB_CONFIG");}
		return;
	}
	
$f[]="set_cachesize {$datas["set_cachesize"]}";
$f[]="set_lg_regionmax {$datas["set_lg_regionmax"]}";
$f[]="set_lg_bsize {$datas["set_lg_bsize"]}";
$f[]="set_lg_max {$datas["set_lg_max"]}";
$f[]="set_tx_max 200";

$f[]="";

echo "Starting......: cyrus-imapd define $configdirectory/DB_CONFIG\n";
@file_put_contents("$configdirectory/DB_CONFIG",@implode("\n",$f));
	
	
}

function DirectorySize(){
	$unix=new unix();
	$pid_path="/etc/artica-postfix/pids/".__FILE__.".".__FUNCTION__;
	$oldpid=@file_get_contents($pid_path);
	if($unix->process_exists($oldpid)){die();}
	$childpid=posix_getpid();
	@file_put_contents($pid_path,$childpid);
	
	$filetim=file_time_min("/etc/artica-postfix/croned.1/".__FILE__.".".__FUNCTION__);
	if($filetim<240){die();}
	
	
	$partition_default=$unix->IMAPD_GET("partition-default");
	
	artica_mysql_events("Starting calculate - $partition_default - disk size",null,__FILE__,"mailbox");
	
	if(strlen($partition_default)<3){return;}
	if(!is_dir($partition_default)){return;}
	
	$GLOBALS["NICE"]=EXEC_NICE();
	$du_bin=$unix->find_program("du");
	exec("{$GLOBALS["NICE"]}$du_bin -h -s $partition_default 2>&1",$results);
	$r=implode("",$results);
	if(preg_match("#^(.+?)\s+#",$r,$re)){
	$sock=new sockets();
		$sock->SET_INFO("CyrusImapPartitionDefaultSize",$re[1]);
		send_email_events("Mailboxes size on your server: $re[1]","Mailboxes size on your server: $re[1]","mailbox");
	
		if($partition_default=="/var/spool/cyrus/mail"){
			$sock->SET_INFO("CyrusImapPartitionDefaultDirSize",$re[1]);
			return;
		}
		unset($results);
		exec("{$GLOBALS["NICE"]}$du_bin -h -s /var/spool/cyrus/mail 2>&1",$results);
		$r=implode("",$results);
		if(preg_match("#^(.+?)\s+#",$r,$re)){
			$sock->SET_INFO("CyrusImapPartitionDefaultDirSize",$re[1]);
		}
	}
}
