<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){
	$GLOBALS["VERBOSE"]=true;
	ini_set('html_errors',0);
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);
}

if($argv[1]=="--server"){build_server();exit;}
if($argv[1]=="--node"){build_node();exit;}

function build_server(){
	
	
$users=new usersMenus();

@mkdir("/usr/share/artica-postfix/munin",0755,true);
shell_exec("/bin/chown munin:munin /usr/share/artica-postfix/munin >/dev/null 2>&1");

$conf[]="dbdir	/var/lib/munin";
$conf[]="htmldir /usr/share/artica-postfix/munin";
$conf[]="logdir /var/log/munin";
$conf[]="rundir  /var/run/munin";
$conf[]="tmpldir	/etc/munin/templates";
$conf[]="includedir /etc/munin/munin-conf.d";
$conf[]="#graph_period minute";
$conf[]="#graph_strategy cgi";
$conf[]="#munin_cgi_graph_jobs 6";
$conf[]="#cgiurl_graph /cgi-bin/munin-cgi-graph";
$conf[]="#max_graph_jobs 6";
$conf[]="";
$conf[]="[$users->fqdn]";
$conf[]="    address 127.0.0.1";
$conf[]="    use_node_name yes";
$conf[]="";	

@file_put_contents("/etc/munin/munin.conf",@implode("\n",$conf));
echo "Starting......: munin server /etc/munin/munin.conf done\n";	
}

function build_node(){
$users=new usersMenus();	
$conf[]="log_level 4";
$conf[]="log_file /var/log/munin/munin-node.log";
$conf[]="pid_file /var/run/munin/munin-node.pid";
$conf[]="";
$conf[]="background 1";
$conf[]="setsid 1";
$conf[]="";
$conf[]="user root";
$conf[]="group root";
$conf[]="";
$conf[]="# Regexps for files to ignore";
$conf[]="";
$conf[]="ignore_file ~$";
$conf[]="#ignore_file [#~]$  # FIX doesn't work. '#' starts a comment";
$conf[]="ignore_file DEADJOE$ ";
$conf[]="ignore_file \.bak$";
$conf[]="ignore_file %$";
$conf[]="ignore_file \.dpkg-(tmp|new|old|dist)$";
$conf[]="ignore_file \.rpm(save|new)$";
$conf[]="ignore_file \.pod$";
$conf[]="";
$conf[]="# Set this if the client doesn't report the correct hostname when";
$conf[]="# telnetting to localhost, port 4949";
$conf[]="#";
$conf[]="host_name $users->fqdn";
$conf[]="";
$conf[]="# A list of addresses that are allowed to connect.  This must be a";
$conf[]="# regular expression, since Net::Server does not understand CIDR-style";
$conf[]="# network notation unless the perl module Net::CIDR is installed.  You";
$conf[]="# may repeat the allow line as many times as you'd like";
$conf[]="";
$conf[]="allow ^127\.0\.0\.1$";
$conf[]="";
$conf[]="# If you have installed the Net::CIDR perl module, you can use";
$conf[]="# multiple cidr_allow and cidr_deny address/mask patterns.  A";
$conf[]="# connecting client must match any cidr_allow, and not match any";
$conf[]="# cidr_deny.  Example:";
$conf[]="";
$conf[]="# cidr_allow 127.0.0.1/32";
$conf[]="# cidr_allow 192.0.2.0/24";
$conf[]="# cidr_deny  192.0.2.42/32";
$conf[]="";
$conf[]="# Which address to bind to;";
$conf[]="#host *";
$conf[]="host 127.0.0.1";
$conf[]="";
$conf[]="# And which port";
$conf[]="port 4949";
$conf[]="";	

@file_put_contents("/etc/munin/munin-node.conf",@implode("\n",$conf));
echo "Starting......: munin-node /etc/munin/munin-node.conf done\n";	
}
