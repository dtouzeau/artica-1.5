<?php
include_once(dirname(__FILE__)."/framework/frame.class.inc");
$unix=new unix();
$GLOBALS["SHOW_COMPILE_ONLY"]=false;
$GLOBALS["NO_COMPILE"]=false;
$GLOBALS["REPOS"]=false;
if($argv[1]=='--compile'){$GLOBALS["SHOW_COMPILE_ONLY"]=true;}
if(preg_match("#--no-compile#", @implode(" ", $argv))){$GLOBALS["NO_COMPILE"]=true;}
if(preg_match("#--verbose#", @implode(" ", $argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--repos#", @implode(" ", $argv))){$GLOBALS["REPOS"]=true;}

if($argv[1]=="--cross-packages"){crossroads_package();exit;}
if($argv[1]=="--factorize"){factorize($argv[2]);exit;}
if($argv[1]=="--serialize"){serialize_tests();exit;}
if($argv[1]=="--latests"){latests();exit;}
if($argv[1]=="--error-txt"){error_txt();exit;}



$wget=$unix->find_program("wget");
$tar=$unix->find_program("tar");
$rm=$unix->find_program("rm");
$cp=$unix->find_program("cp");

//http://www.squid-cache.org/Versions/v3/3.2/squid-3.2.0.13.tar.gz




$v=latests();
$dirsrc="squid-3.2.0.13";
if(preg_match("#squid-(.+?)-#", $v,$re)){$dirsrc=$re[1];}
echo "Downloading lastest file $v, working directory $dirsrc ...\n";
$Architecture=Architecture();
if(is_file("/root/$v")){if($GLOBALS["REPOS"]){echo "No updates...\n";die();}}

if(is_dir("/root/squid-builder")){shell_exec("$rm -rf /root/squid-builder");}
chdir("/root");
if(!$GLOBALS["NO_COMPILE"]){
	if(is_dir("/root/$dirsrc")){shell_exec("/bin/rm -rf /root/$dirsrc");}
	@mkdir("/root/$dirsrc");
	if(!is_file("/root/$v")){
		echo "Downloading $v ...\n";
		shell_exec("$wget http://www.squid-cache.org/Versions/v3/3.2/$v");
		if(!is_file("/root/$v")){echo "Downloading failed...\n";die();}
	}
	
	shell_exec("$tar -xf /root/$v -C /root/$dirsrc/");
	chdir("/root/$dirsrc");
	if(!is_file("/root/$dirsrc/configure")){
		echo "/root/$dirsrc/configure no such file\n";
		$dirs=$unix->dirdir("/root/$dirsrc");
		while (list ($num, $ligne) = each ($dirs) ){if(!is_file("$ligne/configure")){echo "$ligne/configure no such file\n";}else{
			chdir("$ligne");echo "Change to dir $ligne\n";
			$SOURCE_DIRECTORY=$ligne;
			break;}}
	}
	
}

$cmds[]="--prefix=/usr";
$cmds[]="--includedir=\${prefix}/include";
$cmds[]="--mandir=\${prefix}/share/man";
$cmds[]="--infodir=\${prefix}/share/info";
$cmds[]="--localstatedir=/var";
$cmds[]="--libexecdir=\${prefix}/lib/squid3";
$cmds[]="--disable-maintainer-mode";
$cmds[]="--disable-dependency-tracking";
$cmds[]="--srcdir=.";
$cmds[]="--datadir=/usr/share/squid3"; 
$cmds[]="--sysconfdir=/etc/squid3";
$cmds[]="--enable-gnuregex";
$cmds[]="--enable-forward-log"; 
$cmds[]="--enable-removal-policy=heap"; 
$cmds[]="--enable-follow-x-forwarded-for"; 
$cmds[]="--enable-cache-digests"; 
$cmds[]="--enable-http-violations"; 
$cmds[]="--enable-large-cache-files"; 
$cmds[]="--enable-removal-policies=lru,heap"; 
$cmds[]="--enable-err-languages=English"; 
$cmds[]="--enable-default-err-language=English"; 
$cmds[]="--with-maxfd=32000";
$cmds[]="--with-large-files";
$cmds[]="--disable-dlmalloc";
$cmds[]="--with-pthreads";
$cmds[]="--enable-esi"; 
$cmds[]="--enable-storeio=aufs,diskd,ufs,rock"; 
$cmds[]="--with-aufs-threads=10"; 
$cmds[]="--with-maxfd=16384"; 
$cmds[]="--enable-x-accelerator-vary";
$cmds[]="--with-dl";
$cmds[]="--enable-truncate"; 
$cmds[]="--enable-linux-netfilter"; 
$cmds[]="--with-filedescriptors=16384";
$cmds[]="--enable-wccpv2"; 
$cmds[]="--enable-eui"; 
$cmds[]="--enable-auth";
$cmds[]="--enable-auth-basic"; 
$cmds[]="--enable-auth-digest"; 
$cmds[]="--enable-auth-negotiate-helpers";
$cmds[]="--enable-log-daemon-helpers";
$cmds[]="--enable-url-rewrite-helpers";
$cmds[]="--enable-auth-ntlm";
$cmds[]="--with-default-user=squid";
$cmds[]="--enable-icap-client"; 
$cmds[]="--enable-cache-digests"; 
$cmds[]="--enable-icap-support"; 
$cmds[]="--enable-poll";
$cmds[]="--enable-epoll";
$cmds[]="--enable-async-io";
$cmds[]="--enable-delay-pools";
//$cmds[]="--enable-ssl"; 
//$cmds[]="--enable-ssl-crtd";
$cmds[]="CFLAGS=\"-DNUMTHREADS=60 -O3 -pipe -fomit-frame-pointer -funroll-loops -ffast-math -fno-exceptions\""; 

//CPPFLAGS="-I../libltdl"



$configure="./configure ". @implode(" ", $cmds);

if($GLOBALS["SHOW_COMPILE_ONLY"]){echo $configure."\n";die();}
if(!$GLOBALS["NO_COMPILE"]){
	echo "Remove /usr/share/squid3\n";
	shell_exec("/bin/rm -rf /usr/share/squid3");
	echo "Remove /lib/squid3\n";
	shell_exec("/bin/rm -rf /lib/squid3");
	echo "configuring...\n";
	shell_exec($configure);
	echo "make...\n";
	shell_exec("make");
	echo "make install...\n";
	
	$unix=new unix();
	$squid3=$unix->find_program("squid3");
	if(is_file($squid3)){@unlink($squid3);}
	
	@unlink("/usr/sbin/squid");
	@unlink("/usr/bin/purge");
	@unlink("/usr/bin/squidclient");
	shell_exec("make install");
}
if(!is_file("/usr/sbin/squid")){echo "Failed\n";}
@mkdir("/usr/share/squid3/errors/templates",755,true);
shell_exec("/bin/rm -rf /usr/share/squid3/errors/templates/*");
echo "Copy templates from $SOURCE_DIRECTORY/errors/templates...\n";
shell_exec("/bin/cp -rf $SOURCE_DIRECTORY/errors/templates/* /usr/share/squid3/errors/templates/");
shell_exec("/bin/chown -R squid:squid /usr/share/squid3");

shell_exec("wget http://www.artica.fr/download/anthony-icons.tar.gz -O /tmp/anthony-icons.tar.gz");
@mkdir("/usr/share/squid3/icons",755,true);
shell_exec("tar -xf /tmp/anthony-icons.tar.gz -C /usr/share/squid3/icons/");
shell_exec("/bin/chown -R squid:squid /usr/share/squid3/icons/");

mkdir("/root/squid-builder/usr/share/squid3",755,true);
mkdir("/root/squid-builder/etc/squid3",755,true);
mkdir("/root/squid-builder/lib/squid3",755,true);
mkdir("/root/squid-builder/usr/sbin",755,true);
mkdir("/root/squid-builder/usr/bin",755,true);
mkdir("/root/squid-builder/usr/share/squid-langpack",755,true);

shell_exec("$cp -rf /usr/share/squid3/* /root/squid-builder/usr/share/squid3/");
shell_exec("/bin/cp -rf $SOURCE_DIRECTORY/errors/templates/* /root/squid-builder/usr/share/squid3/errors/templates/");
shell_exec("$cp -rf /etc/squid3/* /root/squid-builder/etc/squid3/");
shell_exec("$cp -rf /lib/squid3/* /root/squid-builder/lib/squid3/");
shell_exec("$cp -rf /usr/share/squid-langpack/* /root/squid-builder/usr/share/squid-langpack/");
shell_exec("$cp -rf /usr/sbin/squid /root/squid-builder/usr/sbin/squid");
shell_exec("$cp -rf /usr/bin/purge /root/squid-builder/usr/bin/purge");
shell_exec("$cp -rf /usr/bin/squidclient /root/squid-builder/usr/bin/squidclient");


if($Architecture==64){$Architecture="x64";}
if($Architecture==32){$Architecture="i386";}

chdir("/root/squid-builder");

$version=squid_version();

shell_exec("$tar -czf squid32-$Architecture-$version.tar.gz *");
echo "/root/squid-builder/squid32-$Architecture-$version.tar.gz is now ready to be uploaded\n";
shell_exec("/etc/init.d/artica-postfix restart squid-cache");
if($GLOBALS["REPOS"]){
	if(is_file("/root/ftp-password")){
		shell_exec("curl -T /root/squid-builder/squid32-$Architecture-$version.tar.gz ftp://www.artica.fr/download/ --user ".@file_get_contents("/root/ftp-password"));
	}
	if(is_file("/root/rebuild-artica")){
		shell_exec("$wget \"".@file_get_contents("/root/rebuild-artica")."\" -O /tmp/rebuild.html");
	}
}

function Architecture(){
	$unix=new unix();
	$uname=$unix->find_program("uname");
	exec("$uname -m 2>&1",$results);
	while (list ($num, $val) = each ($results)){
		if(preg_match("#i[0-9]86#", $val)){return 32;}
		if(preg_match("#x86_64#", $val)){return 64;}
	}
}

function squid_version(){
	exec("/usr/sbin/squid -v 2>&1",$results);
	while (list ($num, $val) = each ($results)){
		if(preg_match("#Squid Cache: Version\s+(.+)#", $val,$re)){
			return trim($re[1]);
		}
	}
	
}

function latests(){
	$unix=new unix();
	$wget=$unix->find_program("wget");
	shell_exec("$wget http://www.squid-cache.org/Versions/v3/3.2/ -O /tmp/index.html");
	$f=explode("\n",@file_get_contents("/tmp/index.html"));
	while (list ($num, $line) = each ($f)){
		if(preg_match("#<a href=\"squid-(.+?)\.tar\.gz#", $line,$re)){
			$ve=$re[1];
			$ve=str_replace(".", "", $ve);
			$ve=str_replace("-", "", $ve);
			$file="squid-{$re[1]}.tar.gz";
			$versions[$ve]=$file;
		if($GLOBALS["VERBOSE"]){echo "$ve -> $file\n";}
		}else{
			
		}
		
	}
	
	krsort($versions);
	while (list ($num, $filename) = each ($versions)){
		$vv[]=$filename;
	}
	
	echo "Found latest file version: `{$vv[0]}`\n";
	return $vv[0];
}


function crossroads_package(){
$Architecture=Architecture();	
if($Architecture==64){$Architecture="x64";}
if($Architecture==32){$Architecture="i386";}
$unix=new unix();
$tar=$unix->find_program("tar");
$f[]="/usr/sbin/xrctl";
$f[]="/usr/share/man/man1/xr.1";
$f[]="/usr/share/man/man1/xrctl.1";
$f[]="/usr/share/man/man5/xrctl.xml.5";
$f[]="/usr/sbin/xr";
@mkdir("/root/crossroads",755,true);
while (list ($num, $file) = each ($f)){
	$dir=dirname($file);
	@mkdir("/root/crossroads$dir",755,true);
	@copy($file, "/root/crossroads$file");

}
	chdir("/root/crossroads");
	shell_exec("$tar -czf crossroads-$Architecture.tar.gz *");

	
}


function factorize($path){
	$f=explode("\n",@file_get_contents($path));
	while (list ($num, $val) = each ($f)){
		$newarray[$val]=$val;
		
	}
	while (list ($num, $val) = each ($newarray)){
		echo "$val\n";
	}
	
}

function serialize_tests(){
	$array["zdate"]=date("Y-m-d H:i:s");
	$array["text"]="this is the text";
	$array["function"]="this is the function";
	$array["file"]="this is the process";
	$array["line"]="this is the line";
	$array["category"]="this is the category";
	$serialize=serialize($array);
	echo $serialize;
	
}


function error_txt(){
$f[]="#rebuilded error template by script";	
$f[]="name: SQUID_X509_V_ERR_DOMAIN_MISMATCH";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Certificate does not match domainname\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT";
$f[]="detail: \"SSL Certficate error: certificate issuer (CA) not known: %ssl_ca_name\"";
$f[]="descr: \"Unable to get issuer certificate\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_GET_CRL";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Unable to get certificate CRL\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_DECRYPT_CERT_SIGNATURE";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Unable to decrypt certificate's signature\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_DECRYPT_CRL_SIGNATURE";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Unable to decrypt CRL's signature\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_DECODE_ISSUER_PUBLIC_KEY";
$f[]="detail: \"Unable to decode issuer (CA) public key: %ssl_ca_name\"";
$f[]="descr: \"Unable to decode issuer public key\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_SIGNATURE_FAILURE";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Certificate signature failure\"";
$f[]="";
$f[]="name: X509_V_ERR_CRL_SIGNATURE_FAILURE";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"CRL signature failure\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_NOT_YET_VALID";
$f[]="detail: \"SSL Certficate is not valid before: %ssl_notbefore\"";
$f[]="descr: \"Certificate is not yet valid\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_HAS_EXPIRED";
$f[]="detail: \"SSL Certificate expired on: %ssl_notafter\"";
$f[]="descr: \"Certificate has expired\"";
$f[]="";
$f[]="name: X509_V_ERR_CRL_NOT_YET_VALID";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"CRL is not yet valid\"";
$f[]="";
$f[]="name: X509_V_ERR_CRL_HAS_EXPIRED";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"CRL has expired\"";
$f[]="";
$f[]="name: X509_V_ERR_ERROR_IN_CERT_NOT_BEFORE_FIELD";
$f[]="detail: \"SSL Certificate has invalid start date (the 'not before' field): %ssl_subject\"";
$f[]="descr: \"Format error in certificate's notBefore field\"";
$f[]="";
$f[]="name: X509_V_ERR_ERROR_IN_CERT_NOT_AFTER_FIELD";
$f[]="detail: \"SSL Certificate has invalid expiration date (the 'not after' field): %ssl_subject\"";
$f[]="descr: \"Format error in certificate's notAfter field\"";
$f[]="";
$f[]="name: X509_V_ERR_ERROR_IN_CRL_LAST_UPDATE_FIELD";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Format error in CRL's lastUpdate field\"";
$f[]="";
$f[]="name: X509_V_ERR_ERROR_IN_CRL_NEXT_UPDATE_FIELD";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Format error in CRL's nextUpdate field\"";
$f[]="";
$f[]="name: X509_V_ERR_OUT_OF_MEM";
$f[]="detail: \"%ssl_error_descr\"";
$f[]="descr: \"Out of memory\"";
$f[]="";
$f[]="name: X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT";
$f[]="detail: \"Self-signed SSL Certificate: %ssl_subject\"";
$f[]="descr: \"Self signed certificate\"";
$f[]="";
$f[]="name: X509_V_ERR_SELF_SIGNED_CERT_IN_CHAIN";
$f[]="detail: \"Self-signed SSL Certificate in chain: %ssl_subject\"";
$f[]="descr: \"Self signed certificate in certificate chain\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT_LOCALLY";
$f[]="detail: \"SSL Certficate error: certificate issuer (CA) not known: %ssl_ca_name\"";
$f[]="descr: \"Unable to get local issuer certificate\"";
$f[]="";
$f[]="name: X509_V_ERR_UNABLE_TO_VERIFY_LEAF_SIGNATURE";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Unable to verify the first certificate\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_CHAIN_TOO_LONG";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Certificate chain too long\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_REVOKED";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Certificate revoked\"";
$f[]="";
$f[]="name: X509_V_ERR_INVALID_CA";
$f[]="detail: \"%ssl_error_descr: %ssl_ca_name\"";
$f[]="descr: \"Invalid CA certificate\"";
$f[]="";
$f[]="name: X509_V_ERR_PATH_LENGTH_EXCEEDED";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Path length constraint exceeded\"";
$f[]="";
$f[]="name: X509_V_ERR_INVALID_PURPOSE";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Unsupported certificate purpose\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_UNTRUSTED";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Certificate not trusted\"";
$f[]="";
$f[]="name: X509_V_ERR_CERT_REJECTED";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Certificate rejected\"";
$f[]="";
$f[]="name: X509_V_ERR_SUBJECT_ISSUER_MISMATCH";
$f[]="detail: \"%ssl_error_descr: %ssl_ca_name\"";
$f[]="descr: \"Subject issuer mismatch\"";
$f[]="";
$f[]="name: X509_V_ERR_AKID_SKID_MISMATCH";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Authority and subject key identifier mismatch\"";
$f[]="";
$f[]="name: X509_V_ERR_AKID_ISSUER_SERIAL_MISMATCH";
$f[]="detail: \"%ssl_error_descr: %ssl_ca_name\"";
$f[]="descr: \"Authority and issuer serial number mismatch\"";
$f[]="";
$f[]="name: X509_V_ERR_KEYUSAGE_NO_CERTSIGN";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Key usage does not include certificate signing\"";
$f[]="";
$f[]="name: X509_V_ERR_APPLICATION_VERIFICATION";
$f[]="detail: \"%ssl_error_descr: %ssl_subject\"";
$f[]="descr: \"Application verification failure\";\n";
@file_put_contents("/usr/share/squid3/errors/templates/error-details.txt", @implode("\n", $f));

}