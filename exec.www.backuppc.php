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
	
	if($argv[1]=="--vhosts"){vhosts();die();}
	

function vhosts(){

$ldap=new clladp();
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*)(wwwservertype=BACKUPPC))";
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


@file_put_contents("/usr/local/apache-groupware/conf/backuppc-vhosts.conf",@implode("\n",$conf));
	
	
}


function vhosts_users_ou($array){
	$unix=new unix();
	$ldap=new clladp();
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
	$ou=$array["OU"][0];
	$apacheservername=trim($array["apacheservername"][0]);
	$wwwservertype=trim($array["wwwservertype"][0]);
	$wwwsslmode=$array["wwwsslmode"][0];
	$root=$array["apachedocumentroot"][0];
	$index_cgi=$unix->BACKUPPC_GET_CGIBIN_PATH();
	$img_dir=$unix->BACKUPPC_GET_IMG_DIR();
	if($index_cgi==null){
		echo "Starting Apache..............: BackupPC Unable to stat index.cgi\n";
		return;
		
	}
	
	if($img_dir==null){
		echo "Starting Apache..............: BackupPC Unable to images path\n";
		return;
		
	}	
	
	@mkdir($root,0755,true);
	shell_exec("/bin/cp $index_cgi $root/index.cgi");
	shell_exec("/bin/ln -s $img_dir $root/image >/dev/null 2>&1");
	shell_exec("chmod 4755 $root/index.cgi");
	
	patchIndex($root);
	$apacheuser=$unix->APACHE_GROUPWARE_ACCOUNT();
	if(preg_match("#(.+?):#",$apacheuser,$re)){$apacheuser=$re[1];}
	shell_exec("chown -R backuppc:$apacheuser $root");
	system("chmod 4755 $root/index.cgi");
	
	$ApacheGroupWarePort_WRITE=$ApacheGroupWarePort;
	echo "Starting Apache..............: BackupPC checking host $apacheservername in $root for $apacheuser:backuppc\n";	
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
	
	
	echo "Starting Apache..............: BackupPC \"$apacheservername:$ApacheGroupWarePort_WRITE\"\n";
	$conf[]="\n<VirtualHost *:$ApacheGroupWarePort_WRITE>";
	$conf[]="\tServerName $apacheservername";
	//$conf[]="\tSuexecUserGroup backuppc backuppc";
	$conf[]="\tServerAdmin webmaster@$apacheservername";
	$conf[]="\tDocumentRoot $root";	
	$conf[]=@implode("\n",$ssl);
	$conf[]="\tBrowserMatch \"Microsoft Data Access Internet Publishing Provider\" redirect-carefully";
	$conf[]="\tBrowserMatch \"MS FrontPage\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^WebDrive\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^WebDAVFS/1.[0123]\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^gnome-vfs/1.0\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^XML Spy\" redirect-carefully";
	$conf[]="\tBrowserMatch \"^Dreamweaver-WebDAV-SCM1\" redirect-carefully";	
	$conf[]="\tAlias /backuppc $root";
	$conf[]="\t<Directory \"$root\">";
	$conf[]="\tAllowOverride None";
	$conf[]="\tAllow from all";
	$conf[]="\tOptions ExecCGI FollowSymlinks";
	$conf[]="\tAddHandler cgi-script .cgi";
	$conf[]="\tDirectoryIndex index.cgi";
	$conf[]="\t\tAuthType Basic";
	$conf[]="\t\tAuthBasicProvider ldap";
	$conf[]="\t\tAuthzLDAPAuthoritative off";
	$conf[]="\t\tAuthUserFile /dev/null";
	$conf[]="\t\tAuthLDAPBindDN \"cn=$ldap->ldap_admin,$ldap->suffix\"";
	$conf[]="\t\tAuthLDAPBindPassword $ldap->ldap_password";
	$conf[]="\t\tAuthLDAPUrl ldap://$ldap->ldap_host:$ldap->ldap_port/ou=$ou,dc=organizations,$ldap->suffix?uid";
	$conf[]="\t\tAuthName \"Authorization required\"";
	$conf[]="\t\trequire ldap-filter &(uid=*)";
	$conf[]="\t\trequire valid-user";
	$conf[]="\t</Directory>";		
    $conf[]="</VirtualHost>\n";
	
	return @implode("\n",$conf);
	
	
}

function patchIndex($root){
	
	$f=explode("\n",@file_get_contents("$root/index.cgi"));
	
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#editConfig.+?=>.+?EditConfig#",$line,$re)){
			unset($f[$index]);
			break;
		}
	}
	
	@file_put_contents("$root/index.cgi",@implode("\n",$f));
	
	
}


?>