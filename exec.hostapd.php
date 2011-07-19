<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.sockets.inc");
include_once(dirname(__FILE__)."/ressources/class.users.menus.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");



if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}


if($argv[1]=="--build"){build();die();}

function build(){
	
//drivers : nl80211,madwifi,hostap
//ralink	
	
$users=new usersMenus();	
$conf[]="interface=nl80211";
$conf[]="#bridge=br0";
$conf[]="driver=hostap";
$conf[]="logger_syslog=-1";
$conf[]="logger_syslog_level=2";
$conf[]="logger_stdout=-1";
$conf[]="logger_stdout_level=2";
$conf[]="dump_file=/tmp/hostapd.dump";
$conf[]="ctrl_interface=/var/run/hostapd";
$conf[]="ctrl_interface_group=0";
$conf[]="ssid=test";
$conf[]="hw_mode=b";
$conf[]="channel=60";
$conf[]="beacon_int=100";
$conf[]="dtim_period=2";
$conf[]="max_num_sta=255";
$conf[]="rts_threshold=2347";
$conf[]="fragm_threshold=2346";
if($users->HOSTAPD_BINVER>60){
	$conf[]="preamble=1";
}
$conf[]="macaddr_acl=0";
$conf[]="accept_mac_file=/etc/hostapd.accept";
$conf[]="deny_mac_file=/etc/hostapd.deny";
$conf[]="auth_algs=3";
$conf[]="ignore_broadcast_ssid=0";
$conf[]="#wep_default_key=0";
$conf[]="# The WEP keys to use.";
$conf[]="# A key may be a quoted string or unquoted hexadecimal digits.";
$conf[]="# The key length should be 5, 13, or 16 characters, or 10, 26, or 32";
$conf[]="# digits, depending on whether 40-bit (64-bit), 104-bit (128-bit), or";
$conf[]="# 128-bit (152-bit) WEP is used.";
$conf[]="# Only the default key must be supplied; the others are optional.";
$conf[]="# default: not set";
$conf[]="#wep_key0=123456789a";
$conf[]="#wep_key1=\"vwxyz\"";
$conf[]="#wep_key2=0102030405060708090a0b0c0d";
$conf[]="#wep_key3=\".2.4.6.8.0.23\"";
$conf[]="ap_max_inactivity=300";
$conf[]="#bridge_packets=1";
$conf[]="";	

if(!is_file("/etc/hostapd.accept")){@file_put_contents("/etc/hostapd.accept","#");}
if(!is_file("/etc/hostapd.deny")){@file_put_contents("/etc/hostapd.deny","#");}



@file_put_contents("/etc/hostapd.conf",implode("\n",$conf));
echo "Starting......: Advanced IEEE 802.11 management binver:$users->HOSTAPD_BINVER\n";
echo "Starting......: Advanced IEEE 802.11 management build configuration done\n";

	
}



?>