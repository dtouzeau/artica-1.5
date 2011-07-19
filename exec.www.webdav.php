<?php
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.apache.inc');
	include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
	include_once(dirname(__FILE__).'/ressources/class.pdns.inc');
	include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/ressources/class.joomla.php');
	include_once(dirname(__FILE__).'/ressources/class.opengoo.inc');
	
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	$GLOBALS["SSLKEY_PATH"]="/etc/ssl/certs/apache";
	
	if($argv[1]=="--users"){vhosts_users();die();}
	

function vhosts_users(){

$ldap=new clladp();
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*)(wwwservertype=WEBDAV))";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
for($i=0;$i<$hash["count"];$i++){
	$dn=$hash[$i]["dn"];
	if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){
		$hash[$i]["OU"][0]=trim($re[1]);
		$ouexec=trim($re[1]);
	}
	
	$conf[]=vhosts_users_ou($hash[$i]);
	
}

	$sock=new sockets();
	$unix=new unix();
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	$d_path=$unix->APACHE_DIR_SITES_ENABLED();

	if($ApacheGroupware==0){
		echo "Starting......: Apache Groupware adding $d_path/webdav-artica-vhosts.conf\n";
		@file_put_contents("$d_path/groupware-artica-vhosts.conf",@implode("\n",$conf));
		$conf=null;
		$apache2ctl=$unix->LOCATE_APACHE_CTL();
		if(is_file($apache2ctl)){shell_exec("$apache2ctl -k restart");}		
	}	


@file_put_contents("/usr/local/apache-groupware/conf/webdav-vhosts.conf",@implode("\n",$conf));
	
	
}


function vhosts_users_ou($array){
	$unix=new unix();
	$ldap=new clladp();
	$sock=new sockets();
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	$ApacheGroupwareListenIP=$sock->GET_INFO("ApacheGroupwareListenIP");
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$ApacheGroupWarePortSSL=$sock->GET_INFO("ApacheGroupWarePortSSL");
	$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
	$d_path=$unix->APACHE_DIR_SITES_ENABLED();

	if($ApacheGroupware==0){
		$ApacheGroupwareListenIP=$sock->GET_INFO("FreeWebListen");
		$ApacheGroupWarePort=$sock->GET_INFO("FreeWebListenPort");
		$ApacheGroupWarePortSSL=$sock->GET_INFO("FreeWebListenSSLPort");
		echo "Starting......: Apache Webdav switch to Apache source\n";
	
		foreach (glob("$d_path/webdav-artica-*") as $filename) {
			echo "Starting......: Apache Webdav removing ".basename($filename)."\n";
		}
	}
	

if(!is_numeric($ApacheGroupWarePortSSL)){$ApacheGroupWarePortSSL=443;}
if(!is_numeric($ApacheGroupWarePort)){$ApacheGroupWarePort=80;}
if($ApacheGroupwareListenIP==null){$ApacheGroupwareListenIP="*";}	
	
	
	
	$ou=$array["OU"][0];
	$apacheservername=trim($array["apacheservername"][0]);
	$wwwservertype=trim($array["wwwservertype"][0]);
	$wwwsslmode=$array["wwwsslmode"][0];
	$root=$array["apachedocumentroot"][0];
	$ApacheGroupWarePort_WRITE=$ApacheGroupWarePort;
	$ww_account=$unix->APACHE_GROUPWARE_ACCOUNT();
	
	$users=loadWebDavUsers($ou);
	if(count($users)<1){return;}
	
	if($wwwsslmode=="TRUE"){
		$ssl[]="\tSSLEngine on";
		$ssl[]="\tSSLCertificateFile {$GLOBALS["SSLKEY_PATH"]}/$apacheservername.crt";
		$ssl[]="\tSSLCertificateKeyFile {$GLOBALS["SSLKEY_PATH"]}/$apacheservername.key";
		$unix->vhosts_BuildCertificate($apacheservername);
		$ApacheGroupWarePort_WRITE="443";
		$SSLMODE=true;
		$conf[]="\n<VirtualHost *:$ApacheGroupWarePort>";
		$conf[]="\tServerName $apacheservername";
		$conf[]="\tRedirect / https://$apacheservername";
		$conf[]="</VirtualHost>\n";
			
	}	
	
	
	echo "Starting Apache..............: WebDav \"$apacheservername:$ApacheGroupWarePort_WRITE\"\n";
	if(!is_dir($root)){
		echo "Starting Apache..............: WebDav creating directory $root\n";
		@mkdir("$root",755,true);
	}
	echo "Starting Apache..............: Apache user: $ww_account\n";
	system("/bin/chown -R $ww_account $root");
	
	$conf[]="\n<VirtualHost $ApacheGroupwareListenIP:$ApacheGroupWarePort_WRITE>";
	$conf[]="\tServerName $apacheservername";
	$conf[]="\tServerAdmin webmaster@$apacheservername";
	$conf[]="\tDocumentRoot /home";	
	$conf[]=@implode("\n",$ssl);
	$conf[]="\tDavLockDB \"$root/DavLock\"";
	$conf[]="\tBrowserMatch \"Microsoft Data Access Internet Publishing Provider\" redirect-carefully";
	$conf[]="\tBrowserMatch \"MS FrontPage\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^WebDrive\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^WebDAVFS/1.[0123]\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^gnome-vfs/1.0\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^XML Spy\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^Dreamweaver-WebDAV-SCM1\" redirect-carefully";	
	
		
	
	while (list ($uid, $home) = each ($users) ){
		$conf[]="\t<Directory \"$home\">";
		echo "Starting Apache..............: WebDav \"$uid\"\n";
		$conf[]="\tOptions Indexes FollowSymLinks Includes MultiViews";
		$conf[]="\t\tAllowOverride None";
		$conf[]="\t\tOrder allow,deny";
		$conf[]="\t\tAllow from all";
		$conf[]="\t\tDAV On";
		$conf[]="\t\tAuthType Basic";
		$conf[]="\t\tAuthBasicProvider ldap";
		$conf[]="\t\tAuthzLDAPAuthoritative off";
		$conf[]="\t\tAuthUserFile /dev/null";
		$conf[]="\t\tAuthLDAPBindDN \"cn=$ldap->ldap_admin,$ldap->suffix\"";
		$conf[]="\t\tAuthLDAPBindPassword $ldap->ldap_password";
		$conf[]="\t\tAuthLDAPUrl ldap://$ldap->ldap_host:$ldap->ldap_port/ou=$ou,dc=organizations,$ldap->suffix?uid";
		$conf[]="\t\tAuthName \"Authorization required\"";
		$conf[]="\t\trequire ldap-filter &(uid=$uid)";
		$conf[]="\t\trequire valid-user";
		
		$conf[]="\t\t<LimitExcept GET PUT HEAD OPTIONS POST>";
		$conf[]="\t\t\tRequire valid-user";
		$conf[]="\t\t</LimitExcept>";
		
        $conf[]="\t</Directory>";		
        $unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.samba.php --home $uid");
        
		}
	$conf[]="LogLevel debug";	
	$conf[]="</VirtualHost>\n";
	
	
	
	return @implode("\n",$conf);
	
	
}

function loadWebDavUsers($ou){
	$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(WebDavUser=1))";
	$attrs=array("uid","HomeDirectory");
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	$array=array();
	for($i=0;$i<$hash["count"];$i++){
		$home=$hash[$i]["homedirectory"][0];
		$uid=$hash[$i]["uid"][0];
		$array[$uid]=$home;
		
	}
	
	return $array;
	
}


	
	

?>