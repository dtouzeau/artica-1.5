<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__).   "/framework/frame.class.inc");
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');
include_once(dirname(__FILE__) . '/ressources/class.ocs.inc');

if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}}
if(is_array($argv)){if(preg_match("#--rebuild#",implode(" ",$argv))){$GLOBALS["REBUILD"]=true;}}
if(is_array($argv)){if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}}
if($GLOBALS["VERBOSE"]){echo "Debug mode TRUE for {$argv[1]}\n";}


if($argv[1]=='--mysql'){mysqlCheck();die();}
if($argv[1]=='--mysqllist'){mysql_table_list();die();}
if($argv[1]=='--certificate-self'){CreateSelfSignedCertificate();}
if($argv[1]=='--certificate'){CreateCertificate();}
if($argv[1]=='--final-cert'){CreateFinalCertificate();exit;}
if($argv[1]=='--injection'){AutomaticInjection();exit;}
if($argv[1]=='--builddbinc'){builddbinc();exit;}


function build_certificate($hostname){
	$unix=new unix();
	if($GLOBALS["REBUILD"]){
		if(is_file("{$GLOBALS["SSLKEY_PATH"]}/$hostname.crt")){
			@unlink("{$GLOBALS["SSLKEY_PATH"]}/$hostname.crt");
		}
	}
	
	$sock=new sockets();
	$sock->SET_INFO("ApacheCertificatesLocations",$GLOBALS["SSLKEY_PATH"]);
	
	$unix->vhosts_BuildCertificate($hostname);
	
}

function MysqlCheck(){
$db_file = "/usr/share/ocsinventory-reports/ocsreports/files/ocsbase.sql";
if(!is_file($db_file)){
	echo "Starting......: OCS web Engine unable to stat $db_file\n";
	return;
}

$q=new mysql();
if(!$q->DATABASE_EXISTS("ocsweb")){
	echo "Starting......: OCS web Engine creating ocsweb\n";
	$q->CREATE_DATABASE("ocsweb");
	if(!$q->DATABASE_EXISTS("ocsweb")){
		echo "Starting......: OCS web Engine unable to create ocsweb mysql database\n";
		return;
	}
}



if(CheckTables()){
	$sock=new sockets();
	$users=new usersMenus();
	$q=new mysql();
	$ocswebservername=$sock->GET_INFO("ocswebservername");
	$OCSWebPort=$sock->GET_INFO("OCSWebPort");	
	if($OCSWebPort==null){$OCSWebPort=9080;}
	if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}	
	if($ocswebservername==null){$ocswebservername=$users->hostname;}
	$sql="UPDATE config SET IVALUE=1 WHERE NAME='DOWNLOAD'";
	$q->QUERY_SQL($sql,"ocsweb");
	$sql="UPDATE config SET IVALUE=1 WHERE NAME='REGISTRY'";
	$q->QUERY_SQL($sql,"ocsweb");
	$sql="UPDATE config SET IVALUE='http://$ocswebservername:$OCSWebPort' WHERE NAME='LOCAL_SERVER'";
	$q->QUERY_SQL($sql,"ocsweb");	
	return;
}




if($dbf_handle = @fopen($db_file, "r")) {
	$sql_query = fread($dbf_handle, filesize($db_file));
	fclose($dbf_handle);
	
}



$array_commands=explode(";", "$sql_query");
while (list ($num, $sql) = each ($array_commands) ){
	if(trim($sql)==null){continue;}
	
	$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){
	echo "Starting......: OCS web Engine $q->mysql_error $sql\n";	
	}
}

}

function mysql_table_list(){
	$q=new mysql();
	$bd=@mysql_connect("$q->mysql_server:$q->mysql_port",$q->mysql_admin,$q->mysql_password);
	
		if(!$bd){
			$errnum=mysql_error();
	    	$des=mysql_error();
	    	echo "$errnum) $des\n";
	    	return;
		}	
	$ok=@mysql_select_db("ocsweb");
  	$mysql_result = mysql_query("SHOW TABLES;",$bd);
  	if(!$mysql_result){
  					$errnum=mysql_error();
	    	$des=mysql_error();
	    	echo "$errnum) $des\n";
	    	return;
  	}
 	while ($ligne = mysql_fetch_row($mysql_result)){
 			$table_name=$ligne[0];
 			echo "\$f[]=\"$table_name\";\n";
     	}
}

function CheckTables(){
$f[]="accesslog";
$f[]="accountinfo";
$f[]="bios";
$f[]="blacklist_macaddresses";
$f[]="blacklist_serials";
$f[]="config";
$f[]="conntrack";
$f[]="controllers";
$f[]="deleted_equiv";
$f[]="deploy";
$f[]="devices";
$f[]="devicetype";
$f[]="dico_ignored";
$f[]="dico_soft";
$f[]="download_affect_rules";
$f[]="download_available";
$f[]="download_enable";
$f[]="download_history";
$f[]="download_servers";
$f[]="drives";
$f[]="engine_mutex";
$f[]="engine_persistent";
$f[]="files";
$f[]="groups";
$f[]="groups_cache";
$f[]="hardware";
$f[]="hardware_osname_cache";
$f[]="inputs";
$f[]="itmgmt_comments";
$f[]="javainfo";
$f[]="locks";
$f[]="memories";
$f[]="modems";
$f[]="monitors";
$f[]="netmap";
$f[]="network_devices";
$f[]="networks";
$f[]="operators";
$f[]="ports";
$f[]="printers";
$f[]="prolog_conntrack";
$f[]="regconfig";
$f[]="registry";
$f[]="registry_name_cache";
$f[]="registry_regvalue_cache";
$f[]="slots";
$f[]="softwares";
$f[]="softwares_name_cache";
$f[]="sounds";
$f[]="storages";
$f[]="subnet";
$f[]="tags";
$f[]="videos";
$f[]="virtualmachines";
$q=new mysql();
while (list ($num, $table) = each ($f) ){
		if(!$q->TABLE_EXISTS($table,"ocsweb")){
			echo "Starting......: OCS web Engine $table does not exists !!\n";	
			return false;
		}
	}
	echo "Starting......: OCS web Engine ". count($f)." tables OK\n";	
	return true;

}


function CreateOnpenSSLCOnf(){
	$path="/etc/ocs/cert";
	$conf="$path/openssl.conf";
	@mkdir($path,666,true);
	$sock=new sockets();
	$ldap=new clladp();
	
	$CertificateMaxDays=$sock->GET_INFO('CertificateMaxDays');
	if($CertificateMaxDays==null){$CertificateMaxDays=730;}
	$ini=new Bs_IniHandler("/etc/artica-postfix/ssl.certificate.conf");
	

	
	
	$sock=new sockets();
	$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));	
	
	if($OCSCertInfos["OCSCertServerName"]==null){
		$users=new usersMenus();
		$OCSCertInfos["OCSCertServerName"]=$users->hostname;
	}	
	
	if($OCSCertInfos["OCSCertDomainName"]==null){return;}
	
	$hostname=$OCSCertInfos["OCSCertServerName"].".".$OCSCertInfos["OCSCertDomainName"];
	$email=$OCSCertInfos["OCSCertEmail"];
	
	unset($ini->_params["HOSTS_ADDONS"]);
	$ini->_params["default_db"]["default_days"]=$CertificateMaxDays;
	$ini->_params["server_policy"]["commonName"]=$hostname;
	$ini->_params["user_policy"]["commonName"]=$hostname;
	$ini->_params["default_ca"]["commonName"]=$hostname;
	$ini->_params["default_ca"]["commonName_value"]=$hostname;
	$ini->_params["policy_match"]["commonName"]=$hostname;
	$ini->_params["policy_anything"]["commonName"]=$hostname;
	$ini->_params["req"]["default_keyfile"]="$path/privkey.key";
	$ini->_params["req"]["default_bits"]="1024";
	$ini->_params["req"]["distinguished_name"]="default_ca";
	$ini->_params["req"]["attributes"]="req_attributes";
	$ini->_params["req"]["x509_extensions"]="v3_ca";
	$ini->_params["default_db"]["dir"]="$path";
	$ini->_params["default_db"]["certs"]="$path";
	$ini->_params["default_db"]["new_certs_dir"]="$path";
	$ini->_params["default_db"]["database"]="$path/ca.index";
	$ini->_params["default_db"]["serial"]="$path/ca.serial";
	$ini->_params["default_db"]["RANDFILE"]="$path/.rnd";
	$ini->_params["default_db"]["certificate"]="$path/certificate.pem";
	$ini->_params["default_db"]["private_key"]="$path/privkey.pem";
	$ini->_params["default_db"]["name_opt"]="default_ca";
	$ini->_params["default_db"]["cert_opt"]="default_ca";
	$ini->_params["ca"]["default_ca"]["CA_default"];
	$ini->_params["CA_default"]["dir"]=$path;
	$ini->_params["CA_default"]["certs"]=		"$path/certs";		# Where the issued certs are kept
	$ini->_params["CA_default"]["crl_dir"]=		"$path/crl";		# Where the issued crl are kept
	$ini->_params["CA_default"]["database"]=	"$path/index.txt";	# database index file.
	$ini->_params["CA_default"]["new_certs_dir"]= "$path/newcerts";		# default place for new certs.
	$ini->_params["CA_default"]["certificate"]=	"$path/cacert.pem"; 	# The CA certificate
	$ini->_params["CA_default"]["serial"]=		"$path/serial"; 		# The current serial number
	$ini->_params["CA_default"]["crlnumber"]=	"$path/crlnumber";	# the current crl number
	$ini->_params["CA_default"]["crl"]=			"$path/crl.pem"; 		# The current CRL
	$ini->_params["CA_default"]["private_key"]=	"$path/cakey.pem";# The private key
	$ini->_params["CA_default"]["RANDFILE"]=	"$path/.rand";	# private random number file
	$ini->_params["CA_default"]["x509_extensions"]="usr_cert";		# The extentions to add to the cert
	$ini->_params["CA_default"]["name_opt"]= 	"ca_default";		# Subject Name options
	$ini->_params["CA_default"]["cert_opt"]= 	"ca_default";		# Certificate field options
	$ini->_params["CA_default"]["default_days"]	= "$CertificateMaxDays";			# how long to certify for
	$ini->_params["CA_default"]["default_crl_days"]= "30";			# how long before next CRL
	$ini->_params["CA_default"]["default_md"]	= "sha1";		# which md to use.
	$ini->_params["CA_default"]["policy"]		= "policy_match";	

	
	$ini->_params["req_attributes"]["challengePassword"]="$ldap->ldap_password";
	$ini->_params["req_attributes"]["challengePassword_min"]="4";
	$ini->_params["req_attributes"]["challengePassword_max"]="20";
	$ini->_params["req_attributes"]["unstructuredName"]="An optional company name";	
	
	$ini->_params["default_ca"]["commonName"]=$hostname;
	$ini->_params["default_ca"]["emailAddress"]=$email;


	
	$ini->saveFile($conf);
	echo "Starting......: OCS web Engine certificate, writing $conf done\n";
	
}

function CreateSelfSignedCertificate(){
	$path="/etc/ocs/cert";
	$conf="$path/openssl.conf";	
	$unix=new unix();
	$openssl=$unix->find_program("openssl");
	$ldap=new clladp();
	$sock=new sockets();
	$CertificateMaxDays=$sock->GET_INFO('CertificateMaxDays');
	if($CertificateMaxDays==null){$CertificateMaxDays=730;}
	@mkdir($path,0666,true);
	CreateOnpenSSLCOnf();
	
/*
 * 
OLD 

	$cmd="$openssl req -new -passout pass:$ldap->ldap_password -batch -config  $conf -out $path/server.csr";
	echo $cmd."\n";
	system($cmd);
	$cmd="$openssl rsa -in /etc/ocs/cert/privkey.key -passin pass:$ldap->ldap_password -out $path/server.key";
	echo $cmd."\n";
	system($cmd);	
	$cmd="$openssl openssl x509 -in $path/server.csr -out $path/server.crt -req -signkey $path/server.key -days $CertificateMaxDays";
	echo $cmd."\n";
	system($cmd);	
	shell_exec("/bin/cp $path/server.crt $path/cacert.pem");
	shell_exec("/bin/cp $path/server.crt $path/server.key");


NEW

$cmd="$openssl genrsa -des3 -passout pass:$ldap->ldap_password -out $path/server.key 1024";
echo $cmd."\n";
system($cmd);

$cmd="$openssl rsa -passin pass:$ldap->ldap_password -in $path/server.key -out $path/server.key";
echo $cmd."\n";
system($cmd);

$cmd="$openssl req -new -batch -config  $conf -key server.key -x509 -out server.crt -days $CertificateMaxDays";
echo $cmd."\n";
system($cmd);

cd /usr/lib/ssl/misc
CA.sh -newca
Create a client key

openssl genrsa -out client.key 1024
Create a certificate request

openssl req -new -key client.key -out client.csr
Sign the certificate request

openssl ca -in client.csr -cert server.crt -keyfile server.key -out client.crt -days 1825
Create PKCS12 file for use in a webbrowser

openssl pkcs12 -export -in client.crt -inkey client.key -out client.p12
Add to /etc/apache/httpd.conf:

SSLVerifyClient require
SSLCACertificateFile /path/to/server.crt	-> cacert.pem
SSLCertificateFile /path/to/server.crt
SSLCertificateKeyFile /path/to/server.key
*/	
$cmd="$openssl genrsa -des3 -passout pass:$ldap->ldap_password -out $path/server.key 1024";
echo $cmd."\n";
system($cmd);

$cmd="$openssl rsa -passin pass:$ldap->ldap_password -in $path/server.key -out $path/server.key";
echo $cmd."\n";
system($cmd);

$cmd="$openssl req -new -batch -config  $conf -key $path/server.key -x509 -out $path/server.crt -days $CertificateMaxDays";
echo $cmd."\n";
system($cmd);
shell_exec("/bin/cp $path/server.crt $path/cacert.pem");
shell_exec("/bin/cp $path/server.crt /etc/artica-postfix/settings/Daemons/OCSServerDotCrt");
	
}




function CreateCertificate(){
	CreateOnpenSSLCOnf();
	if(!is_file($conf)){
		echo "Starting......: OCS web Engine certificate, unable to stat $conf\n";
		return;
	}
	$cmd=" openssl genrsa -out $path/server.key 1024";
	echo $cmd."\n";
	system($cmd);
	$cmd="$openssl req -new -key $path/server.key -batch -config $conf -out $path/server.csr";
	echo $cmd."\n";
	system($cmd);
}

function CreateFinalCertificate(){
	$sock=new sockets();
	$certs=$sock->GET_INFO("OCSServerDotCrt");
	if(strlen($certs)<50){
		return null;
	}
	shell_exec("/usr/share/artica-postfix/bin/artica-install wget \"http://www.cacert.org/certs/root.crt\" /etc/ocs/cert/cacert.pem");
	
	
	
}

function AutomaticInjection(){
	$users=new usersMenus();
	if(!$users->OCSI_INSTALLED){return ;}
	$file="/etc/artica-postfix/cron.2/AutomaticInjection.pid";
	$sock=new sockets();
	$OCSImportToLdap=$sock->GET_INFO("OCSImportToLdap");
	if($OCSImportToLdap==null){$OCSImportToLdap=60;}
	if($OCSImportToLdap==0){return;}
	if(!$GLOBALS["FORCE"]){
		$filetime=file_time_min($file);
	
		if($GLOBALS["VERBOSE"]){echo "$file=$filetime against $OCSImportToLdap minutes\n";}
		if(!$GLOBALS["VERBOSE"]){
			if($filetime<$OCSImportToLdap){return;}
		}
	}
	
	writelogs("Starting OCS injection from OCS database",__FUNCTION__,__FILE__,__LINE__);
	$ocs=new ocs();
	$sql=$ocs->COMPUTER_SEARCH_QUERY(null,1);
	if($GLOBALS["VERBOSE"]){echo $sql."\n";}
	$q=NEW mysql();
	$results=$q->QUERY_SQL($sql,"ocsweb");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IPADDRESS"]=="0.0.0.0"){continue;}
		if($ligne["MACADDR"]=="00:00:00:00:00:00"){continue;}
		if($already[$ligne["MACADDR"]]){continue;}
		
		$already[$ligne["MACADDR"]]=true;
		$f=new computers();
		$uid=$f->ComputerIDFromMAC($ligne["MACADDR"]);
		if($GLOBALS["VERBOSE"]){echo "Check {$ligne["MACADDR"]} against $uid\n";}
		if($uid==null){
			writelogs("uid is null for {$ligne["MACADDR"]}, add it into LDAP",__FUNCTION__,__FILE__,__LINE__);
			AutomaticInjectionAdd($ligne["MACADDR"]);
			continue;
		}
		
		$update=false;
		$f=new computers($uid);
		if($GLOBALS["VERBOSE"]){echo "Checking uid=$uid NAME=$f->ComputerRealName; IP=$f->ComputerIP; OS=$f->ComputerOS\n";}
		$ComputerIP=trim($f->ComputerIP);
		$ComputerOS=trim($f->ComputerOS);
		$OSNAME=trim(utf8_encode($ligne["OSNAME"]));
		$PROCESSORT=trim($ligne["PROCESSORT"]);
		if($GLOBALS["VERBOSE"]){echo "OCS SOURCE {$ligne["MACADDR"]} IP={$ligne["IPSRC"]}; OS=$OSNAME CPU=$PROCESSORT\n";}
		
		if($ComputerIP<>$ligne["IPSRC"]){
			$f->ComputerIP=$ligne["IPSRC"];
			if($GLOBALS["VERBOSE"]){echo "IPSRC not match ($ComputerIP)\n";}
			$update=true;
		}
		
		
		
		if($PROCESSORT<>null){
		if(trim($f->ComputerCPU)<>$ligne["PROCESSORT"]){
			if($GLOBALS["VERBOSE"]){echo "PROCESSORT not match\n";}
			$f->ComputerOS=$ligne["PROCESSORT"];
			$update=true;
			}		
		}
		if($update){
			echo "update {$ligne["MACADDR"]}\n";
			$f->Add();}
		
		
	}
	
	@unlink($file);
	@file_put_contents($file,getmypid());
	writelogs("Finish OCS injection from OCS database",__FUNCTION__,__FILE__,__LINE__);
	
}

function AutomaticInjectionAdd($MAC){
	echo "add $MAC\n";
	$cs=new ocs();
	if($cs->INJECT_COMPUTER_TOLDAP($MAC)){
		unset($GLOBALS["INJECT_COMPUTER_TOLDAP"]);
		$f=new computers();
		$uid=$f->ComputerIDFromMAC($ligne["MACADDR"]);
		$f=new computers($uid);
		$text[]="uid\t:$uid";
		$text[]="Computer\t:$f->ComputerRealName";
		$text[]="IP\t:$f->ComputerIP";
		$text[]="MAC\t:$MAC";
		send_email_events("New computer $f->ComputerRealName added into Database",@implode("\n",$text),"system");
	}else{
		$infos=@implode("\n",$GLOBALS["INJECT_COMPUTER_TOLDAP"])."\n\n".@implode("\n",$text);
		send_email_events("Failed to inject computer $f->ComputerRealName ($MAC)",$infos,"system");
	}
	
}

function builddbinc(){
	if(!is_dir("/usr/share/ocsinventory-reports/ocsreports")){
		echo "Starting......: OCS web Engine /usr/share/ocsinventory-reports/ocsreports no such directory\n";
		return;
	}
	$q=new mysql();
	$f[]="<?php";
	$f[]="\$_SESSION[\"SERVEUR_SQL\"]=\"$q->mysql_server:$q->mysql_port\";";
	$f[]="\$_SESSION[\"COMPTE_BASE\"]=\"$q->mysql_admin\";";
	$f[]="\$_SESSION[\"PSWD_BASE\"]=\"$q->mysql_password\";";
	$f[]="?>";
	@file_put_contents("/usr/share/ocsinventory-reports/ocsreports/dbconfig.inc.php", @implode("\n", $f));
	echo "Starting......: OCS web Engine dbconfig.inc.php done\n"; 	

}



?>