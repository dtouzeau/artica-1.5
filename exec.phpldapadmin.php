<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--build'){build();exit;}




function build(){
	writelogs("Starting building phpldapadmin",__FUNCTION__,__FILE__,__LINE__);
	$ldap=new clladp();	
	$sock=new sockets();
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");


$f[]="<?php";
$f[]="\$session[\"blowfish\"]=\"5ebe2294ecd0e0f08eab7690d2a6ee69\";";
$f[]="\$config->custom->appearance[\"tree\"] = \"AJAXTree\";";
$f[]="\$config->custom->appearance[\"friendly_attrs\"] = array(";
$f[]="	\"facsimileTelephoneNumber\" => \"Fax\",";
$f[]="	\"gid\"                      => \"Group\",";
$f[]="	\"mail\"                     => \"Email\",";
$f[]="	\"telephoneNumber\"          => \"Telephone\",";
$f[]="	\"uid\"                      => \"User Name\",";
$f[]="	\"userPassword\"             => \"Password\"";
$f[]=");";
$f[]="";
$f[]="";
$f[]="\$servers = new Datastore();";
$f[]="\$servers->newServer(\"ldap_pla\");";
$f[]="\$servers->setValue(\"server\",\"name\",\"Local LDAP Server\");";
$f[]="\$servers->setValue(\"server\",\"host\",\"$ldap->ldap_host\");";
$f[]="\$servers->setValue(\"server\",\"port\",$ldap->ldap_port);";
$f[]="\$servers->setValue(\"server\",\"base\",array(\"$ldap->suffix\"));";
$f[]="\$servers->setValue(\"login\",\"auth_type\",\"session\");";
$f[]="\$servers->setValue(\"login\",\"bind_id\",\"cn=$ldap->ldap_admin,$ldap->suffix\");";
$f[]="\$servers->setValue(\"login\",\"bind_pass\",\"\");";
$f[]="\$servers->setValue(\"server\",\"tls\",false);";
$f[]="";
if($EnableSambaActiveDirectory==1){
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?net-ads-info=yes")));
	$ActiveDirectoryCredentials["suffix"]=$array["Bind Path"];
	$ActiveDirectoryCredentials["host"]=$array["LDAP server"];	
	if($ActiveDirectoryCredentials["host"]<>null){
		$f[]="\$servers->newServer(\"ldap_pla\");";
		$f[]="\$servers->setValue(\"server\",\"name\",\"ActiveDirectory {$ActiveDirectoryCredentials["host"]}\");";
		$f[]="\$servers->setValue(\"server\",\"host\",\"{$ActiveDirectoryCredentials["host"]}\");";
		$f[]="\$servers->setValue(\"server\",\"port\",389);";
		$f[]="\$servers->setValue(\"server\",\"base\",array(\"{$ActiveDirectoryCredentials["suffix"]}\"));";
		$f[]="\$servers->setValue(\"login\",\"auth_type\",\"session\");";
		$f[]="\$servers->setValue(\"login\",\"bind_id\",\"\");";
		$f[]="\$servers->setValue(\"login\",\"bind_pass\",\"\");";
		$f[]="\$servers->setValue(\"server\",\"tls\",false);";
		$f[]="";
	}
}
$pattern="(objectClass=AdLinker)";
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,array("dn"));
$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if($hash["count"]>0){
		include_once(dirname(__FILE__).'/ressources/class.activedirectory.inc');
		for($i=0;$i<$hash["count"];$i++){
			if(preg_match("#cn=adlinker,ou=(.+?),dc=organizations,#",$hash[$i]["dn"],$re)){
				echo "Starting lighttpd............: Build connexion for Active Directory Linker on \"{$re[1]}\" OU\n";
				$wad=new wad($re[1]);
				$f[]="\$servers->newServer(\"ldap_pla\");";
				$f[]="\$servers->setValue(\"server\",\"name\",\"ActiveDirectory {$wad->ldap_host}\");";
				$f[]="\$servers->setValue(\"server\",\"host\",\"{$wad->ldap_host}\");";
				$f[]="\$servers->setValue(\"server\",\"port\",389);";
				$f[]="\$servers->setValue(\"server\",\"base\",array(\"{$wad->suffix}\"));";
				$f[]="\$servers->setValue(\"login\",\"auth_type\",\"session\");";
				$f[]="\$servers->setValue(\"login\",\"bind_id\",\"\");";
				$f[]="\$servers->setValue(\"login\",\"bind_pass\",\"\");";
				$f[]="\$servers->setValue(\"server\",\"tls\",false);";
				$f[]="";				
				
			} 
		}
	}




$f[]="?>";
echo "Starting lighttpd............: success writing phpldapadmin configuration\n"; 
@file_put_contents("/usr/share/phpldapadmin/config/config.php",@implode("\n",$f));	
@chmod("/usr/share/phpldapadmin/config/config.php",0666);
}
