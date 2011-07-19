<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.dhcpd.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.iptables-chains.inc');
include_once(dirname(__FILE__) . '/ressources/class.baseunix.inc');
include_once(dirname(__FILE__) . '/ressources/class.bind9.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
$GLOBALS["ASROOT"]=true;
if($argv[1]=='--bind'){compile_bind();die();}

if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--no-reload#",implode(" ",$argv))){$GLOBALS["NORELOAD"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set_verbosed();}
}

BuildDHCP();

function BuildDHCP(){
	$dhcpd=new dhcpd();
	$conf=$dhcpd->BuildConf();
	$confpath=dhcp3Config();
	$unix=new unix();
	@mkdir(dirname($confpath),null,true);
	@file_put_contents($confpath,$conf);
	echo "Starting......: DHCP SERVER saving \"$confpath\" (". strlen($conf)." bytes) done\n";
	
	if(is_dir("/var/lib/dhcp3")){
		shell_exec("/bin/chown -R dhcpd:dhcpd /var/lib/dhcp3");
		shell_exec("/bin/chmod 755 /var/lib/dhcp3");
	
	}
	$complain=$unix->find_program("aa-complain");
	
	if(is_file($complain)){
		$dhcpd3=$unix->find_program("dhcpd3");
		if(is_file($dhcpd3)){shell_exec("$complain $dhcpd3 >/dev/null 2>&1");}
	}
	
}

function compile_bind(){
	$bind=new bind9();
	$bind->Compile();
	$bind->SaveToLdap();
}


function dhcp3Config(){
	
	$f[]="/etc/dhcp3/dhcpd.conf";
	$f[]="/etc/dhcpd.conf";
	$f[]="/etc/dhcpd/dhcpd.conf";
	while (list ($index, $filename) = each ($f) ){
		if(is_file($filename)){return $filename;}
	} 
	return "/etc/dhcp3/dhcpd.conf";
	
}
?>