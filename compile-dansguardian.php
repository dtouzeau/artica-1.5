<?php
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if($argv[1]=="mac"){
	echo "00:1f:3b:b3:a4:3b -> ". strlen("00:1f:3b:b3:a4:3b")."\n";
	die();
}

$unix=new unix();
// netfilter error="http://muhdzamri.blogspot.com/2011/01/usrincludelinuxnetfilteripv4h53-error.html"

$wget=$unix->find_program("wget");
$tar=$unix->find_program("tar");
$rm=$unix->find_program("rm");
$cp=$unix->find_program("cp");
$v="dansguardian-2.12.0.0.tar.gz";
$dirsrc="dansguardian-2.12.0.0";

if(is_dir("/usr/share/dansguardian")){shell_exec("$rm -rf /usr/share/dansguardian");}
if(is_dir("/etc/dansguardian")){shell_exec("$rm -rf /etc/dansguardian");}
if(is_dir("/root/dansguardian-builder")){shell_exec("$rm -rf /root/dansguardian-builder");}

chdir("/root");
shell_exec("$wget http://dansguardian.org/downloads/2/Alpha/$v");

if(!is_file("/root/$v")){echo "Downloading failed...\n";die();}
shell_exec("$tar -xf /root/$v");
chdir("/root/$dirsrc");
$configure="./configure --enable-orig-ip=yes --enable-lfs=yes --enable-clamd=no --enable-icap=yes --with-proxyuser=squid --with-proxygroup=squid --with-piddir=/var/run/dansguardian.pid --with-logdir=/var/log/dansguardian --prefix=/usr --mandir=\${prefix}/share/man --infodir=\${prefix}/share/info --sysconfdir=/etc --localstatedir=/var --enable-trickledm=yes --enable-ntlm=yes";
echo "configuring...\n";
shell_exec($configure);

echo "Patching...\n";
if(!patchnetfilter()){echo "patchnetfilter() Failed\n";die();}

echo "make...\n";
shell_exec("make");
echo "make install...\n";
shell_exec("make install");

if(!is_file("/usr/sbin/dansguardian")){echo "Failed\n";}

mkdir("/root/dansguardian-builder/usr/share/dansguardian",755,true);
mkdir("/root/dansguardian-builder/etc/dansguardian",755,true);
mkdir("/root/dansguardian-builder/usr/sbin",755,true);

shell_exec("$cp -rf /usr/share/dansguardian/* /root/dansguardian-builder/usr/share/dansguardian/");
shell_exec("$cp -rf /etc/dansguardian/* /root/dansguardian-builder/etc/dansguardian/");
shell_exec("$cp -rf /usr/sbin/dansguardian /root/dansguardian-builder/usr/sbin/dansguardian");

chdir("/root/dansguardian-builder");
shell_exec("$tar -czf $dirsrc.tar.gz *");
echo "/root/dansguardian-builder/$dirsrc.tar.gz is now ready to be uploaded\n";


function patchnetfilter(){
	$netfilter="/usr/include/linux/netfilter_ipv4.h";
	if (!is_file($netfilter)){
		echo "$netfilter no such file\n";
		return;
	}
	
	$f=explode("\n",@file_get_contents($netfilter));
	while (list ($num, $val) = each ($f)){
		if(preg_match("#include <limits\.h>#", $val)){
			echo "patchnetfilter() limits\.h found....\n";
			return true;
		}
		
	}
	
	reset($f);
	while (list ($num, $val) = each ($f)){
		if(preg_match("#include#", $val)){
			echo "patchnetfilter() patching limits.h line $num\n";
			$f[$num]=$f[$num]."\n#include <limits.h>";
			@file_put_contents($netfilter, @implode("\n", $f));
			return true;
		}
		
	}	
	
	return false;
	
}