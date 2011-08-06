<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__)."/framework/class.settings.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
$GLOBALS["EXEC_PID_FILE"]="/etc/artica-postfix/".basename(__FILE__).".pid";
$unix=new unix();

if($argv[1]=="--build"){build();die();}
if($argv[1]=="--ping"){ping_kdc();die();}



function build(){
	$sock=new sockets();
	$EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
	if(!is_numeric("$EnableKerbAuth")){$EnableKerbAuth=0;}
	if($EnableKerbAuth==0){echo "Starting......: Kerberos, disabled\n";return;}
	if(!checkParams()){echo "Starting......: Kerberos, misconfiguration failed\n";return;}
	
	$unix=new unix();
	$msktutil=$unix->find_program("msktutil");
	$hostname_bin=$unix->find_program("hostname");
	$kdb5_util=$unix->find_program("kdb5_util");
	$kadmin_bin=$unix->find_program("kadmin");
	$netbin=$unix->LOCATE_NET_BIN_PATH();
	if(!is_file("$msktutil")){echo "Starting......: Kerberos, msktutil no such binary\n";return;}
	if(!is_file("$hostname_bin")){echo "Starting......: Kerberos, hostname no such binary\n";return;}
	exec("$hostname_bin -d 2>&1",$results);
	$mydomain=trim(@implode("", $results));
	
	unset($results);
	exec("$hostname_bin -f 2>&1",$results);
	$myFullHostname=trim(@implode("", $results));
	unset($results);
	exec("$hostname_bin -s 2>&1",$results);
	$myNetBiosName=trim(@implode("", $results));
	$enctype=null;
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));	
	
	if($array["WINDOWS_SERVER_TYPE"]=="WIN_2003"){
		$t[]="# For Windows 2003:";
		$t[]=" default_tgs_enctypes = rc4-hmac des-cbc-crc des-cbc-md5";
		$t[]=" default_tkt_enctypes = rc4-hmac des-cbc-crc des-cbc-md5";
		$t[]=" permitted_enctypes = rc4-hmac des-cbc-crc des-cbc-md5";
		$t[]="";
		
	}
	
	if($array["WINDOWS_SERVER_TYPE"]=="WIN_2008AES"){
		$t[]="; for Windows 2008 with AES";
		$t[]=" default_tgs_enctypes = aes256-cts-hmac-sha1-96 rc4-hmac des-cbc-crc des-cbc-md5";
		$t[]=" default_tkt_enctypes = aes256-cts-hmac-sha1-96 rc4-hmac des-cbc-crc des-cbc-md5";
		$t[]=" permitted_enctypes = aes256-cts-hmac-sha1-96 rc4-hmac des-cbc-crc des-cbc-md5";
		$t[]="";
		$enctype=" --enctypes 28";
		
	}	
	
	$hostname=strtolower(trim($array["WINDOWS_SERVER_NETBIOSNAME"])).".".strtolower(trim($array["WINDOWS_DNS_SUFFIX"]));	
	echo "Starting......: Kerberos, $hostname\n";
	echo "Starting......: Kerberos, my domain: \"$mydomain\"\n";
	echo "Starting......: Kerberos, my hostname: \"$myFullHostname\"\n";
	echo "Starting......: Kerberos, my netbiosname: \"$myNetBiosName\"\n";
	
	
	$domainUp=strtoupper($array["WINDOWS_DNS_SUFFIX"]);
	$domaindow=strtolower($array["WINDOWS_DNS_SUFFIX"]);
	$kinitpassword=$array["WINDOWS_SERVER_PASS"];
	$kinitpassword=$unix->shellEscapeChars($kinitpassword);
	

	
$f[]=" [logging]";
$f[]=" default = FILE:/var/log/krb5libs.log";
$f[]=" kdc = FILE:/var/log/krb5kdc.log";
$f[]=" admin_server = FILE:/var/log/kadmind.log";
$f[]="";
$f[]="[libdefaults]";
$f[]=" default_realm = $domainUp";
$f[]=" dns_lookup_realm = true";
$f[]=" dns_lookup_kdc = true";
$f[]=" ticket_lifetime = 24h";
$f[]=" forwardable = yes";
$f[]="";
@implode("\n", $t);

$f[]="[realms]";
$f[]=" $domainUp = {";
$f[]="  kdc = $hostname";
$f[]="  admin_server = $hostname";
$f[]="  default_domain = $domainUp";
$f[]=" }";
$f[]="";
$f[]="[domain_realm]";
$f[]=" .$domaindow = $domainUp";
$f[]=" $domaindow = $domainUp";
$f[]="";
$f[]="[appdefaults]";
$f[]=" pam = {";
$f[]="   debug = false";
$f[]="   ticket_lifetime = 36000";
$f[]="   renew_lifetime = 36000";
$f[]="   forwardable = true";
$f[]="   krb4_convert = false";
$f[]="}";
$f[]="";	
@file_put_contents("/etc/krb.conf", @implode("\n", $f));
echo "Starting......: Kerberos, /etc/krb.conf done\n";
@file_put_contents("/etc/krb5.conf", @implode("\n", $f));
echo "Starting......: Kerberos, /etc/krb5.conf done\n";	
unset($f);
$f[]="lhs=.ns";
$f[]="rhs=.$mydomain";
$f[]="classes=IN,HS";
@file_put_contents("/etc/hesiod.conf", @implode("\n", $f));
echo "Starting......: Kerberos, /etc/hesiod.conf done\n";


unset($f);
$f[]="[libdefaults]";
$f[]="\t\tdebug = true";
$f[]="[kdcdefaults]";
//$f[]="\tv4_mode = nopreauth";	
$f[]="\tkdc_ports = 88,750";	
//$f[]="\tkdc_tcp_ports = 88";	
$f[]="[realms]";	
$f[]="\t$domainUp = {";	
$f[]="\t\tdatabase_name = /etc/krb5kdc/principal";
$f[]="\t\tacl_file = /etc/kadm.acl";	
$f[]="\t\tdict_file = /usr/share/dict/words";	
$f[]="\t\tadmin_keytab = FILE:/etc/krb5.keytab";
$f[]="\t\tkey_stash_file = /etc/krb5kdc/.k5.$domainUp";
$f[]="\t\tmaster_key_type = des3-hmac-sha1";
$f[]="\t\tsupported_enctypes = des3-hmac-sha1:normal des-cbc-crc:normal des:normal des:v4 des:norealm des:onlyrealm des:afs3";	
$f[]="\t\tdefault_principal_flags = +preauth";
$f[]="\t}";
$f[]="";
if(!is_dir("/usr/share/krb5-kdc")){@mkdir("/usr/share/krb5-kdc",644,true);}
@file_put_contents("/usr/share/krb5-kdc/kdc.conf", @implode("\n", $f));
@file_put_contents("/etc/kdc.conf", @implode("\n", $f));
echo "Starting......: Kerberos, /usr/share/krb5-kdc/kdc.conf done\n";
echo "Starting......: Kerberos, /etc/kdc.conf done\n";

unset($f);







$config="*/admin *\n";
@file_put_contents("/etc/kadm.acl"," ");
@file_put_contents("/usr/share/krb5-kdc/kadm.acl"," ");
@file_put_contents("/etc/krb5kdc/kadm5.acl"," ");
echo "Starting......: Kerberos, /etc/kadm.acl done\n";


RunKinit($array["WINDOWS_SERVER_ADMIN"],$array["WINDOWS_SERVER_PASS"]);

unset($results);
if($GLOBALS["VERBOSE"]){$mskutilverb=" --verbose";}
 

$cmd="$msktutil -c -b \"CN=COMPUTERS\" -s HTTP/$myFullHostname -h $myFullHostname --keytab /etc/krb5.keytab";
$cmd=$cmd." --computer-name $myNetBiosName --upn HTTP/$myFullHostname --server $hostname$enctype$mskutilverb 2>&1";
echo "Starting......: msktutil, $cmd\n";
exec($cmd,$results);

while (list ($num, $a) = each ($results) ){echo "Starting......: msktutil, $a\n";}

if(is_file("$kdb5_util")){
	$cmd="$kdb5_util create -r $domainUp -s -P $kinitpassword";
	if($GLOBALS["VERBOSE"]){echo "Starting......:  $cmd\n";}
	unset($results);
	exec($cmd,$results);
	while (list ($num, $a) = each ($results) ){echo "Starting......: kdb5_util, $a\n";}
}

if(is_file("$kadmin_bin")){
	
}

 //kadmin -p Administrateur "addprinc -randkey cifs/bdc.touzeau.com" -w DavidTouzeau180872

if(is_file("$netbin")){JOIN_ACTIVEDIRECTORY();}
	
	
	



}

function JOIN_ACTIVEDIRECTORY(){
$unix=new unix();	
$user=new settings_inc();
$netbin=$unix->LOCATE_NET_BIN_PATH();
	
if(!is_file($netbin)){echo "Starting......:  net, no such binary\n";return;}
if(!$user->SAMBA_INSTALLED){echo "Starting......:  Samba, no such software\n";return;}
$NetADSINFOS=$unix->SAMBA_GetNetAdsInfos();
$KDC_SERVER=$NetADSINFOS["KDC server"];
$sock=new sockets();
$array=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));
$domainUp=strtoupper($array["WINDOWS_DNS_SUFFIX"]);
$domain_lower=strtolower($array["WINDOWS_DNS_SUFFIX"]);
$adminpassword=$array["WINDOWS_SERVER_PASS"];
$adminpassword=$unix->shellEscapeChars($adminpassword);
$adminname=$array["WINDOWS_SERVER_ADMIN"];
$ad_server=$array["WINDOWS_SERVER_NETBIOSNAME"];
echo "Starting......:  Samba, [$adminname]: Kdc server ads : $KDC_SERVER\n";
if($KDC_SERVER==null){
		$cmd="$netbin ads join -W $ad_server.$domain_lower -S $ad_server -U $adminname%$adminpassword 2>&1";
		if($GLOBALS["VERBOSE"]){echo "Starting......:  Samba, $cmd\n";}
		exec("$cmd",$results);
		while (list ($index, $line) = each ($results) ){echo "Starting......:  Samba,ads join [$adminname]: $line\n";}	
		$NetADSINFOS=$unix->SAMBA_GetNetAdsInfos();
		$KDC_SERVER=$NetADSINFOS["KDC server"];
	}
	
echo "Starting......:  Samba, [$adminname]: setauthuser..\n";
$cmd="$netbin setauthuser -U $adminname%$adminpassword";	
shell_exec($cmd);	


	if($KDC_SERVER==null){
		echo "Starting......:  Samba, [$adminname]: unable to join the domain $domain_lower\n";
		
	}

	echo "Starting......:  Samba, [$adminname]: Kdc server ads : $KDC_SERVER\n";
	
	unset($results);
	$cmd="$netbin ads keytab create -P -U $adminname%$adminpassword 2>&1";
	if($GLOBALS["VERBOSE"]){echo "Starting......:  Samba, $cmd\n";}
	exec("$cmd",$results);
	while (list ($index, $line) = each ($results) ){echo "Starting......:  Samba,ads keytab: [$adminname]: $line\n";}		

}



function RunKinit($username,$password){
$unix=new unix();
$kinit=$unix->find_program("kinit");
$klist=$unix->find_program("klist");
$echo=$unix->find_program("echo");
if(!is_file($kinit)){echo2("Unable to stat kinit");return;}

exec("$klist 2>&1",$res);
$line=@implode("",$res);


if(strpos($line,"No credentials cache found")>0){
	unset($res);
	echo2($line." -> initialize..");
	exec("$echo \"$password\"|$kinit {$username} 2>&1",$res);
	while (list ($num, $a) = each ($res) ){	
		if(preg_match("#Password for#",$a,$re)){unset($res[$num]);}
	}	
	$line=@implode("",$res);	
	if(strlen(trim($line))>0){
		echo2($line." -> Failed..");
		return;
	}
	unset($res);
	exec("$klist 2>&1",$res);	
}

while (list ($num, $a) = each ($res) ){	if(preg_match("#Default principal:(.+)#",$a,$re)){echo2(trim($re[1])." -> success");break;}}	
	

	
}

function echo2($content){
	echo "Starting......: Kerberos,$content\n";
	
}

function ping_kdc(){
	$sock=new sockets();
	$unix=new unix();
	$filetime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
	if(!is_numeric("$EnableKerbAuth")){$EnableKerbAuth=0;}
	if($EnableKerbAuth==0){echo "Starting......: [PING]: Kerberos, disabled\n";return;}
	if(!checkParams()){echo "Starting......: [PING]: Kerberos, misconfiguration failed\n";return;}
	
	
	$array=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));	
	$time=$unix->file_time_min($filetime);
	if($time<120){
		if(!$GLOBALS["VERBOSE"]){return;}
		echo "$filetime ({$time}Mn)\n";
	}
	
	$kinit=$unix->find_program("kinit");
	$echo=$unix->find_program("echo");
	$net=$unix->LOCATE_NET_BIN_PATH();
	$wbinfo=$unix->find_program("wbinfo");

	$domain=strtoupper($array["WINDOWS_DNS_SUFFIX"]);
	$domain_lower=strtolower($array["WINDOWS_DNS_SUFFIX"]);
	$ad_server=strtolower($config["WINDOWS_SERVER_NETBIOSNAME"]);
	$kinitpassword=$array["WINDOWS_SERVER_PASS"];
	$kinitpassword=$unix->shellEscapeChars($kinitpassword);
	$clock_explain="The clock on you system (Linux/UNIX) is too far off from the correct time.\nYour machine needs to be within 5 minutes of the Kerberos servers in order to get any tickets.\nYou will need to run ntp, or a similar service to keep your clock within the five minute window";
	
	
	$cmd="$echo $kinitpassword|$kinit {$array["WINDOWS_SERVER_ADMIN"]}@$domain -V 2>&1";
	echo "$cmd\n";
	exec("$cmd",$kinit_results);
	while (list ($num, $ligne) = each ($kinit_results) ){
		if(preg_match("#Clock skew too great while getting initial credentials#", $ligne)){$unix->send_email_events("Active Directory connection clock issue", "kinit program claim\n$ligne\n$clock_explain", "system");}
		if(preg_match("#Client not found in Kerberos database while getting initial credentials#", $ligne)){$unix->send_email_events("Active Directory authentification issue", "kinit program claim\n$ligne\n", "system");}
		if(preg_match("#Authenticated to Kerberos#", $ligne)){echo "starting......: [PING]: Kerberos, Success\n";}
		if($GLOBALS["VERBOSE"]){echo "kinit: $ligne\n";}
	}	
	
	@unlink($filetime);
	@file_put_contents($filetime, time());

}



function checkParams(){
	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));
	if($array["WINDOWS_DNS_SUFFIX"]==null){return false;}
	if($array["WINDOWS_SERVER_NETBIOSNAME"]==null){return false;}
	if($array["WINDOWS_SERVER_TYPE"]==null){return false;}
	if($array["WINDOWS_SERVER_ADMIN"]==null){return false;}
	if($array["WINDOWS_SERVER_PASS"]==null){return false;}
	
	
	
	
	$hostname=strtolower(trim($array["WINDOWS_SERVER_NETBIOSNAME"])).".".strtolower(trim($array["WINDOWS_DNS_SUFFIX"]));
	$ip=gethostbyname($hostname);
	if($ip==$hostname){return false;}
	return true;
}
