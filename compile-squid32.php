<?php
include_once(dirname(__FILE__)."/framework/frame.class.inc");
$unix=new unix();
$GLOBALS["SHOW_COMPILE_ONLY"]=false;
$GLOBALS["NO_COMPILE"]=false;
if($argv[1]=='--compile'){$GLOBALS["SHOW_COMPILE_ONLY"]=true;}
if(preg_match("#--no-compile", @implode(" ", $argv))){$GLOBALS["NO_COMPILE"]=true;}
$wget=$unix->find_program("wget");
$tar=$unix->find_program("tar");
$rm=$unix->find_program("rm");
$cp=$unix->find_program("cp");



$v="squid-3.2.0.12-20110926-r11343.tar.gz";
$dirsrc="squid-3.2.0.12";
$Architecture=Architecture();

if(is_dir("/root/squid-builder")){shell_exec("$rm -rf /root/squid-builder");}
chdir("/root");
if(!$GLOBALS["NO_COMPILE"]){
	if(is_dir("/root/$dirsrc")){shell_exec("/bin/rm -rf /root/$dirsrc");}
	@mkdir("/root/$dirsrc");
	if(is_file("/root/$v")){@unlink("/root/$v");}
	echo "Downloading $v ...\n";
	shell_exec("$wget http://www.squid-cache.org/Versions/v3/3.2/$v");
	if(!is_file("/root/$v")){echo "Downloading failed...\n";die();}
	shell_exec("$tar -xf /root/$v -C /root/$dirsrc/");
	chdir("/root/$dirsrc");
	if(!is_file("/root/$dirsrc/configure")){
		echo "/root/$dirsrc/configure no such file\n";
		$dirs=$unix->dirdir("/root/$dirsrc");
		while (list ($num, $ligne) = each ($dirs) ){if(!is_file("$ligne/configure")){echo "$ligne/configure no such file\n";}else{chdir("$ligne");echo "Change to dir $ligne\n";break;}}
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
$cmds[]="--enable-storeio=aufs,diskd,ufs"; 
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

mkdir("/root/squid-builder/usr/share/squid3",755,true);
mkdir("/root/squid-builder/etc/squid3",755,true);
mkdir("/root/squid-builder/lib/squid3",755,true);
mkdir("/root/squid-builder/usr/sbin",755,true);
mkdir("/root/squid-builder/usr/bin",755,true);
mkdir("/root/squid-builder/usr/share/squid-langpack",755,true);

shell_exec("$cp -rf /usr/share/squid3/* /root/squid-builder/usr/share/squid3/");
shell_exec("$cp -rf /etc/squid3/* /root/squid-builder/etc/squid3/");
shell_exec("$cp -rf /lib/squid3/* /root/squid-builder/lib/squid3/");
shell_exec("$cp -rf /usr/share/squid-langpack/* /root/squid-builder//usr/share/squid-langpack/");
shell_exec("$cp -rf /usr/sbin/squid /root/squid-builder/usr/sbin/squid");
shell_exec("$cp -rf /usr/bin/purge /root/squid-builder/usr/bin/purge");
shell_exec("$cp -rf /usr/bin/squidclient /root/squid-builder/usr/bin/squidclient");


if($Architecture==62){$Architecture="x64";}
if($Architecture==32){$Architecture="i386";}

chdir("/root/squid-builder");
shell_exec("$tar -czf $dirsrc-$Architecture.tar.gz *");
echo "/root/squid-builder/$dirsrc-$Architecture.tar.gz is now ready to be uploaded\n";


function Architecture(){
	$unix=new unix();
	$uname=$unix->find_program("uname");
	exec("$uname -m 2>&1",$results);
	while (list ($num, $val) = each ($results)){
		if(preg_match("#i[0-9]86#", $val)){return 32;}
		if(preg_match("#x86_64#", $val)){return 64;}
	}
}
