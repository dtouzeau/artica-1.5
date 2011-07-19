<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if(preg_match("#--simule#",implode(" ",$argv))){$GLOBALS["SIMULE"]=true;$GLOBALS["SIMULE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;$GLOBALS["FORCE"]=true;}

events(implode(" ",$argv),"MAIN",__FILE__,__LINE__);

if($argv[1]=="--build"){build();die();}
if($argv[1]=="--chap"){chapsecrets();die();}
if($argv[1]=="--clients"){clients_connexions();die();}
if($argv[1]=="--client"){clients_connexions();die();}
if($argv[1]=="--ifup"){client_net_config($argv[2],$argv[3],$argv[4],$argv[5]);die();}
if($argv[1]=="--ifdown"){client_net_config_down($argv[2],$argv[3],$argv[4],$argv[5]);die();}
if($argv[1]=="--clients-start"){clients_start();}
if($argv[1]=="--clients-stop"){clients_stop();}
if($argv[1]=="--hook"){pptp_hook();}



function build(){
	
	
	$sock=new sockets();
	$users=new usersMenus();
	$unix=new unix();
	$PPTPDConfig=unserialize(base64_decode($sock->GET_INFO("PPTPDConfig")));
	$EnablePPTPDVPN=$sock->GET_INFO("EnablePPTPDVPN");
	if($EnablePPTPDVPN==null){$EnablePPTPDVPN=0;}
	$sysctl=$unix->find_program("sysctl");
	shell_exec("$sysctl -w net.ipv4.ip_forward=1 >/dev/null 2>&1");
	if(!is_file("/var/log/ppp-ipupdown.log")){shell_exec("/bin/touch /var/log/ppp-ipupdown.log");}
	
	if(trim($PPTPDConfig["SERVER_IP"])==null){$PPTPDConfig["SERVER_IP"]="192.168.25.1";}
	if(trim($PPTPDConfig["NETMASK"])==null){$PPTPDConfig["NETMASK"]="255.255.255.0";}
	if(trim($PPTPDConfig["SERVER_NAME"])==null){$PPTPDConfig["SERVER_NAME"]=$users->hostname;}
	
	if($PPTPDConfig["SERVER_IP_FROM"]==null){
		preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP"],$rI);
		$end=$rI[4]+1;
		if($end>255){$end=254;}		
		$newip="{$rI[1]}.{$rI[2]}.{$rI[3]}.".$end;
		$PPTPDConfig["SERVER_IP_FROM"]=$newip;
	}
	
	if($PPTPDConfig["SERVER_IP_TO"]==null){
		preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP"],$re);
		$end=$re[4]+50;
		if($end>255){$end=254;}
		$PPTPDConfig["SERVER_IP_TO"]="{$re[1]}.{$re[2]}.{$re[3]}.".$end;
	}
	
	@mkdir("/etc/ppp",0666,true);
	$conf[]="option /etc/ppp/pptpd-options";
	$conf[]="logwtmp";
	if($PPTPDConfig["bcrelay"]<>null){$conf[]="bcrelay {$PPTPDConfig["bcrelay"]}";}
	preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$PPTPDConfig["SERVER_IP_TO"],$re);
	$conf[]="remoteip {$PPTPDConfig["SERVER_IP_FROM"]}-{$re[4]}";
	$conf[]="localip {$PPTPDConfig["SERVER_IP"]}";
	$conf[]="";
	echo "Starting......: PPTP VPN connexion name \"{$PPTPDConfig["SERVER_NAME"]}\"\n";
	echo "Starting......: PPTP VPN {$PPTPDConfig["SERVER_IP"]} <- {$PPTPDConfig["SERVER_IP_FROM"]}-{$re[4]}/{$PPTPDConfig["NETMASK"]}\n";
	@file_put_contents("/etc/pptpd.conf",@implode($conf,"\n"));
	unset($conf);
	
	$conf[]="################################################################################";
	$conf[]="# \$Id: pptpd-options 4643 2006.-11-06 18:42:43Z rene \$";
	$conf[]="#";
	$conf[]="# Sample Poptop PPP options file /etc/ppp/pptpd-options";
	$conf[]="# Options used by PPP when a connection arrives from a client.";
	$conf[]="# This file is pointed to by /etc/pptpd.conf option keyword.";
	$conf[]="# Changes are effective on the next connection.  See \"man pppd\".";
	$conf[]="#";
	$conf[]="# You are expected to change this file to suit your system.  As";
	$conf[]="# packaged, it requires PPP 2.4.2 and the kernel MPPE module.";
	$conf[]="# updated by Artica on ". date("Y-m-d H:i:s");
	$conf[]="###############################################################################";
	$conf[]="";
	$conf[]="";
	$conf[]="# Authentication";
	$conf[]="";
	$conf[]="# Name of the local system for authentication purposes";
	$conf[]="# (must match the second field in /etc/ppp/chap-secrets entries)";
	$conf[]="name {$PPTPDConfig["SERVER_NAME"]}";
	$conf[]="# domain mydomain.net";
	$conf[]="#chapms-strip-domain";

	if($PPTPDConfig["DNS_1"]<>null){$conf[]="ms-dns {$PPTPDConfig["DNS_1"]}";}
	if($PPTPDConfig["DNS_2"]<>null){$conf[]="ms-dns {$PPTPDConfig["DNS_2"]}";}
	
	$conf[]="#ms-wins 10.0.0.3";
	$conf[]="#ms-wins 10.0.0.4";
	
	if($PPTPDConfig["CRYPT"]==1){
		$conf[]="refuse-pap";
		$conf[]="refuse-chap";
		$conf[]="refuse-mschap";
		$conf[]="require-mschap-v2";
		$conf[]="require-mppe-128";	
		$conf[]="lcp-echo-failure 30";
		$conf[]="lcp-echo-interval 5";
		$conf[]="ipcp-accept-local";
		$conf[]="ipcp-accept-remote";
		$conf[]="asyncmap 0";
		$conf[]="nobsdcomp";
		
	}else{
		$conf[]="refuse-chap";
		$conf[]="refuse-pap";
		$conf[]="refuse-eap";
		$conf[]="refuse-mschap";
		$conf[]="nopcomp";
	}
	
	$conf[]="proxyarp";
	$conf[]="nodefaultroute";
	$conf[]="debug";
	$conf[]="lock";
	$conf[]="netmask {$PPTPDConfig["NETMASK"]}";
	$conf[]="noipx";
	
	//http://www.maclive.net/sid/132
//   1. TCP Port 1723 should be passed through to your server
//   2. IP Protocol ID of 47 (0x2F) (not port 47) needs to be passed. This is not TCP or UDP it's a packet type used to support GRE (Generic Routing Encapsulation)	
	
	events("WRITING:: /etc/ppp/pptpd-options",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("/etc/ppp/pptpd-options",@implode($conf,"\n"));
	unset($conf);
	chapsecrets();
	pptp_hook();
	}
	
	
function chapsecrets(){
	
	$file="/etc/ppp/chap-secrets";
	$sock=new sockets();
	$PPTPDConfig=unserialize(base64_decode($sock->GET_INFO("PPTPDConfig")));
	if(trim($PPTPDConfig["SERVER_NAME"])==null){
		$users=new usersMenus();
		$PPTPDConfig["SERVER_NAME"]=$users->hostname;
	}
	
	
	
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDMembers")));	
	if(!is_array($array)){return;}
	if(count($array)==0){return;}
	if(is_array($array)){
		while (list ($uid, $conf) = each ($array) ){
			$ct=new user($uid);
			if($conf["ASSIGN_IP"]==null){$conf["ASSIGN_IP"]="*";}
			if(!preg_match("#[0-9\.\:]+#",$conf["ASSIGN_IP"])){$conf["ASSIGN_IP"]="*";}			
			$f[]="$uid\t{$PPTPDConfig["SERVER_NAME"]}\t$ct->password\t\"{$conf["ASSIGN_IP"]}\"";
		}
	}	
	events("WRITING:: PPTP VPN ". count($f). " user(s)",__FUNCTION__,__FILE__,__LINE__);
	echo "Starting......: PPTP VPN ". count($f). " user(s)\n";
	$f[]="";
	@file_put_contents($file,@implode($f,"\n"));
	
	
}	


function clients_connexions(){
	if($GLOBALS["VERBOSE"]){echo "DEBUG:clients_connexions(): START\n";}
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));
	if(!is_array($array)){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:clients_connexions(): PPTPVpnClients -> Not an array\n";}
	}
	if(count($array)==0){
		if($GLOBALS["VERBOSE"]){echo "DEBUG:clients_connexions(): PPTPVpnClients = 0 Not an array\n";}
		return;}
	@mkdir("/etc/ppp/peers",666,true);
	if($GLOBALS["VERBOSE"]){echo "DEBUG:clients_connexions(): -> chapsecrets()\n";}
	chapsecrets();
	$chap=explode("\n",@file_get_contents("/etc/ppp/chap-secrets"));
	while (list ($connexionname, $PPTPDConfig) = each ($array) ){
		if($PPTPDConfig["ENABLED"]<>1){continue;}
		echo "Starting......: PPTP VPN Client connection $connexionname\n";
		$chap[]="{$PPTPDConfig["username"]}\t\"{$PPTPDConfig["username"]}\t{$PPTPDConfig["password"]}\t\*";
		$peers[]="# PPTP Tunnel configuration for tunnel $connexionname";
		$peers[]="# Server IP: {$PPTPDConfig["vpn_servername"]}";
		$peers[]="name {$PPTPDConfig["username"]}";
		$peers[]="file /etc/ppp/options.pptp";
		$peers[]="require-mppe-128";	
		$peers[]="ipparam $connexionname";
		$peers[]="remotename $connexionname";
		$peers[]="persist";		
		$peers[]="";
		@file_put_contents("/etc/ppp/peers/$connexionname",@implode("\n",$peers));
		unset($peers);
		$cons["$connexionname"]=$PPTPDConfig["vpn_servername"];
		
	}
	
	$chap[]="";
	@file_put_contents("/etc/ppp/chap-secrets",@implode("\n",$chap));
	pptp_hook();
	
	
	
	//sudo route add -net 10.8.0.0 netmask 255.255.0.0 dev ppp0
	//pptp tougeron.eu call florent
}


function pptp_hook(){
	if($GLOBALS["VERBOSE"]){echo "DEBUG:pptp_hook(): START\n";}
	$f[]="#!/bin/sh";
	$f[]=LOCATE_PHP5_BIN2()." " .__FILE__." --ifup \$4 \$5 \${IFNAME} \${PPP_IPPARAM}";
	$f[]="";
	file_put_contents("/etc/ppp/ip-up.d/detectnet",@implode("\n",$f));
	chmod("/etc/ppp/ip-up.d/detectnet",0755);
	if($GLOBALS["VERBOSE"]){echo "DEBUG:pptp_hook(): /etc/ppp/ip-up.d/detectnet done\n";}
	unset($f);	
	
	$f[]="#!/bin/sh";
	$f[]=LOCATE_PHP5_BIN2()." " .__FILE__." --ifdown \$4 \$5 \${IFNAME} \${PPP_IPPARAM}";
	$f[]="";
	file_put_contents("/etc/ppp/ip-down.d/detectnet",@implode("\n",$f));
	chmod("/etc/ppp/ip-down.d/detectnet",0755);
	if($GLOBALS["VERBOSE"]){echo "DEBUG:pptp_hook(): /etc/ppp/ip-down.d/detectnet done\n";}
	unset($f);		
}


// $4 ip of IFNAME
// $5 server ip

//php5 /usr/share/artica-postfix/exec.pptpd.php $4 $5 ${IFNAME} ${PPP_IPPARAM}

function client_net_config_down($localip,$serverip,$IFNAME,$PPP_IPPARAM){
	events("LOCAL=$localip,SERVER=$serverip,NIC=$IFNAME,CON:$PPP_IPPARAM",__FUNCTION__,__FILE__,__LINE__);
	vpn_msql_events("VPN Tunnel stopped ","LOCAL IP:<b>$localip</b><br>SERVER=<strong>$serverip</strong><br>INTERFACE=<strong>$IFNAME</strong><br>CONNECTION:<strong>$PPP_IPPARAM</strong>",$PPP_IPPARAM);
	
	if(preg_match("#[0-9\.]+\.[0-9\.]+\.[0-9\.]+\.[0-9\.]+#",$PPP_IPPARAM)){
		send_email_events("VPN (PPTPD) connection $localip closed for remote $PPP_IPPARAM","LOCAL=$localip,SERVER=$serverip,NIC=$IFNAME,CON:$PPP_IPPARAM",'VPN');
		events("PPP_IPPARAM \"$PPP_IPPARAM\", server detected delete bridge for $IFNAME if it was created on server...",__FUNCTION__,__FILE__,__LINE__);
		DeleteBridge($IFNAME);
		ServerRoutesDel($IFNAME,$serverip,$PPP_IPPARAM);
		events("IF-DOWN:: $PPP_IPPARAM:: STOPPING SCRIPT",__FUNCTION__,__FILE__,__LINE__);
		return;		
	}	
	
	events("IF-UP:: $PPP_IPPARAM:: client detected $IFNAME deleted on client...",__FUNCTION__,__FILE__,__LINE__);
	send_email_events("VPN (PPTP) connection $PPP_IPPARAM closed for remote $serverip","LOCAL=$localip,SERVER=$serverip,NIC=$IFNAME,CON:$PPP_IPPARAM",'VPN');
	
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));
		if(is_array($array[$PPP_IPPARAM]["ROUTES"])){
			events("IF-DOWN:: $PPP_IPPARAM:: ". count($array[$PPP_IPPARAM]["ROUTES"]). " routes",__FUNCTION__,__FILE__,__LINE__);
			if(count($array[$PPP_IPPARAM]["ROUTES"])>0){
				client_routes_del($array[$PPP_IPPARAM]["ROUTES"],$PPP_IPPARAM,$IFNAME);
			}
		}

	DeleteBridge($IFNAME);
}




function client_net_config($localip,$serverip,$IFNAME,$PPP_IPPARAM){
	$sock=new sockets();
	events("IF-UP:: $PPP_IPPARAM:: LOCAL=$localip,SERVER=$serverip,NIC=$IFNAME,CON:$PPP_IPPARAM",__FUNCTION__,__FILE__,__LINE__);
	
	
	vpn_msql_events("VPN Tunnel started ","LOCAL IP:<b>$localip</b><br>SERVER=<strong>$serverip</strong><br>INTERFACE=<strong>$IFNAME</strong><br>CONNECTION:<strong>$PPP_IPPARAM</strong>",$PPP_IPPARAM);
	
	if(preg_match("#[0-9\.]+\.[0-9\.]+\.[0-9\.]+\.[0-9\.]+#",$PPP_IPPARAM)){
		send_email_events("VPN (PPTPD) connection $localip open for remote $PPP_IPPARAM","LOCAL=$localip,SERVER=$serverip,NIC=$IFNAME,CON:$PPP_IPPARAM",'VPN');
		events("IF-UP:: PPP_IPPARAM \"$PPP_IPPARAM\", server detected new $IFNAME created on server...",__FUNCTION__,__FILE__,__LINE__);
		$PPTPDConfig=unserialize(base64_decode($sock->GET_INFO("PPTPDConfig")));
		if($PPTPDConfig["bcrelay"]<>null){
			events("IF-UP:: $PPP_IPPARAM::Link $IFNAME to {$PPTPDConfig["bcrelay"]}",__FUNCTION__,__FILE__,__LINE__);
			CreateBridge($PPTPDConfig["bcrelay"],$IFNAME);
			if($PPTPDConfig["LINK_NET_FROM"]==1){
				events("Link {$PPTPDConfig["bcrelay"]}  to $IFNAME",__FUNCTION__,__FILE__,__LINE__);
				CreateBridge($IFNAME,$PPTPDConfig["bcrelay"]);
				ServerRoutesAdd($IFNAME,$serverip,$PPP_IPPARAM);
			}
		}
		
		events("IF-UP:: $PPP_IPPARAM:: STOPPING SCRIPT",__FUNCTION__,__FILE__,__LINE__);
		return;
	}else{
		send_email_events("VPN (PPTP) connection $PPP_IPPARAM open for remote $serverip","LOCAL=$localip,SERVER=$serverip,NIC=$IFNAME,CON:$PPP_IPPARAM",'VPN');
		events("IF-UP:: $PPP_IPPARAM:: client detected new $IFNAME created on client...",__FUNCTION__,__FILE__,__LINE__);
		$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));
		if(is_array($array[$PPP_IPPARAM]["ROUTES"])){
			events("IF-UP:: $PPP_IPPARAM:: ". count($array[$PPP_IPPARAM]["ROUTES"]). " routes",__FUNCTION__,__FILE__,__LINE__);
			if(count($array[$PPP_IPPARAM]["ROUTES"])>0){
				client_routes_add($array[$PPP_IPPARAM]["ROUTES"],$PPP_IPPARAM,$IFNAME);
			}
		}
		
		if($array[$PPP_IPPARAM]["LANTOLAN"]==1){
			events("IF-UP:: $PPP_IPPARAM:: Act has a gateway",__FUNCTION__,__FILE__,__LINE__);
			shell_exec("$sysctl -w net.ipv4.ip_forward=1 >/dev/null 2>&1");
			CreateBridge($array[$PPP_IPPARAM]["ETH_LINK"],$IFNAME);
			CreateBridge($IFNAME,$array[$PPP_IPPARAM]["ETH_LINK"]);
		}
			
		events("IF-UP:: $PPP_IPPARAM:: STOPPING SCRIPT",__FUNCTION__,__FILE__,__LINE__);
	}
	
}

function ServerRoutesAdd($IFNAME,$ip_connect,$PPP_IPPARAM){
	$unix=new unix();
	$route=$unix->find_program("route");
	if(!is_file($route)){events("IF-UP:: $PPP_IPPARAM:: ip tool no such file",__FUNCTION__,__FILE__,__LINE__);return;}	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDConfigRoutes")));	
	if(count($array)==0){return;}
	while (list ($ip, $cdir) = each ($array) ){
		$pattern=str_replace(".",'\.',$ip);
		$pattern=str_replace("*",'.?',$ip);
		if(preg_match("#$pattern#",$ip_connect)){
			events("$PPP_IPPARAM:: Link $cdir to NIC $IFNAME",__FUNCTION__,__FILE__,__LINE__);
			vpn_msql_events("IF-UP:: $PPP_IPPARAM:: Link $cdir to NIC $IFNAME",null,$PPP_IPPARAM);
			$cmd="$route add -net $cdir dev $IFNAME";
			events("IF-UP:: $PPP_IPPARAM:: $cmd",__FUNCTION__,__FILE__,__LINE__);
			exec($cmd,$results);
			while (list ($a, $b) = each ($results) ){events("IF-UP:: $PPP_IPPARAM::$b",__FUNCTION__,__FILE__,__LINE__);}			
		}
	}
	
}
function ServerRoutesDel($IFNAME,$ip_connect,$PPP_IPPARAM){
	$unix=new unix();
	$route=$unix->find_program("route");
	if(!is_file($route)){events("IF-DOWN:: $PPP_IPPARAM:: ip tool no such file",__FUNCTION__,__FILE__,__LINE__);return;}	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPDConfigRoutes")));	
	if(count($array)==0){return;}
	while (list ($ip, $cdir) = each ($array) ){
		$pattern=str_replace(".",'\.',$ip);
		$pattern=str_replace("*",'.?',$ip);
		if(preg_match("#$pattern#",$ip_connect)){
			events("$PPP_IPPARAM:: unlink $cdir to NIC $IFNAME",__FUNCTION__,__FILE__,__LINE__);
			vpn_msql_events("IF-DOWN:: $PPP_IPPARAM:: Link $cdir to NIC $IFNAME",null,$PPP_IPPARAM);
			$cmd="$route del -net $cdir dev $IFNAME";
			events("IF-DOWN:: $PPP_IPPARAM:: $cmd",__FUNCTION__,__FILE__,__LINE__);
			exec($cmd,$results);
			while (list ($a, $b) = each ($results) ){events("IF-UP:: $PPP_IPPARAM::$b",__FUNCTION__,__FILE__,__LINE__);}			
		}
	}
	
}

function client_routes_add($array,$PPP_IPPARAM,$IFNAME){
	$unix=new unix();
	$route=$unix->find_program("route");
	if(!is_file($route)){events("IF-UP:: $PPP_IPPARAM:: ip tool no such file",__FUNCTION__,__FILE__,__LINE__);return;}
		
	while (list ($network, $config) = each ($array) ){
		$use_vpn_server=$config["use_vpn_server"];
		$gateway=$config["gateway"];
		events("IF-UP:: $PPP_IPPARAM:: $network gateway:$gateway, use_vpn_server=$use_vpn_server",__FUNCTION__,__FILE__,__LINE__);
		if($use_vpn_server==1){
			$cmd="$route add -net $network dev $IFNAME";
			events("IF-UP:: $PPP_IPPARAM:: $cmd",__FUNCTION__,__FILE__,__LINE__);
			exec($cmd,$results);
			while (list ($a, $b) = each ($results) ){events("IF-UP:: $PPP_IPPARAM::$b",__FUNCTION__,__FILE__,__LINE__);}
		}
	}
}
function client_routes_del($array,$PPP_IPPARAM,$IFNAME){
	$unix=new unix();
	$route=$unix->find_program("route");
	if(!is_file($route)){events("IF-UP:: $PPP_IPPARAM:: ip tool no such file",__FUNCTION__,__FILE__,__LINE__);return;}
		
	while (list ($network, $config) = each ($array) ){
		$use_vpn_server=$config["use_vpn_server"];
		$gateway=$config["gateway"];
		events("IF-UP:: $PPP_IPPARAM:: $network gateway:$gateway, use_vpn_server=$use_vpn_server",__FUNCTION__,__FILE__,__LINE__);
		if($use_vpn_server==1){
			$cmd="$route del -net $network dev $IFNAME";
			events("IF-UP:: $PPP_IPPARAM:: $cmd",__FUNCTION__,__FILE__,__LINE__);
			exec($cmd,$results);
			while (list ($a, $b) = each ($results) ){events("IF-UP:: $PPP_IPPARAM::$b",__FUNCTION__,__FILE__,__LINE__);}
		}
	}
}
function CreateBridge($localnic,$remotenic){
		$comment="PPTP:$localnic-$remotenic";
		
		events("BRIDGE:: $comment",__FUNCTION__,__FILE__,__LINE__);
		
		$unix=new unix();
		$iptables=$unix->find_program("iptables");
		$iptables_rules[]="$iptables -A FORWARD -i $remotenic -o $localnic -m state --state ESTABLISHED,RELATED -j ACCEPT -m comment --comment \"$comment\" 2>&1";
		$iptables_rules[]="$iptables -A FORWARD -i $remotenic -o $localnic -j ACCEPT -m comment --comment \"$comment\" 2>&1";
		$iptables_rules[]="$iptables -t nat -A POSTROUTING -o $localnic -j MASQUERADE -m comment --comment \"$comment\" 2>&1";		
	
		while (list ($index, $cmd) = each ($iptables_rules) ){
			events("$cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);
			
		}
	
}

function DeleteBridge($remotenic){
		$unix=new unix();
		events("IPTABLES_DELETE_REGEX_ENTRIES(\"PPTP:.*?$remotenic\")",__FUNCTION__,__FILE__,__LINE__);
		$unix->IPTABLES_DELETE_REGEX_ENTRIES("PPTP:.*?$remotenic");	
}



function clients_start(){
	if(!is_file("/var/log/ppp-ipupdown.log")){shell_exec("/bin/touch /var/log/ppp-ipupdown.log");}
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
	if(!is_array($array)){return;}
	if(count($array)==0){return;}

	while (list ($connexionname, $PPTPDConfig) = each ($array) ){
		if($PPTPDConfig["ENABLED"]<>1){continue;}
		events("con:$connexionname server:{$PPTPDConfig["vpn_servername"]}",__FUNCTION__,__FILE__,__LINE__);
		client_start_connexion($connexionname,$PPTPDConfig["vpn_servername"]);
		}
}

function clients_stop(){
	if(!is_file("/var/log/ppp-ipupdown.log")){shell_exec("/bin/touch /var/log/ppp-ipupdown.log");}
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));	
	if(!is_array($array)){return;}
	if(count($array)==0){return;}

	while (list ($connexionname, $PPTPDConfig) = each ($array) ){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->con:$connexionname server:{$PPTPDConfig["vpn_servername"]}\n";}
		client_stop_connexion($connexionname,$PPTPDConfig["vpn_servername"]);
	}	
	
}

function client_stop_connexion($connexionname,$server){
	$unix=new unix();
	$kill=$unix->find_program("kill");
	$poff=$unix->find_program("poff");
		
	$arrayPIDS=client_is_active($connexionname);
	if(!is_array($arrayPIDS)){
		echo "Stopping PPTP................: connection $connexionname already stopped\n";
		return;
	}

	
	echo "Stopping PPTP................: connection $connexionname PIDs ". @implode(", ",$arrayPIDS)."\n";
	$cmd="$kill ".@implode(" ",$arrayPIDS)." >/dev/null 2>&1";
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->$cmd\n";}
	
	
	shell_exec($cmd);
	sleep(2);
	
	for($i=0;$i<5;$i++){
		$arrayPIDS=client_is_active($connexionname);
		if(!is_array($arrayPIDS)){break;}
		shell_exec($cmd);
		sleep(1);
	}
	
	$arrayPIDS=client_is_active($connexionname);
	if(is_array($arrayPIDS)){
		echo "Stopping PPTP................: connection $connexionname failed\n";
		return;
	}

	echo "Stopping PPTP................: connection $connexionname success\n";
	
}



function client_start_connexion($connexionname,$server){
	$unix=new unix();
	$pptp=$unix->find_program("pptp");
	$nohup=$unix->find_program("nohup");
	if(!is_file($pptp)){
		echo "Starting......: PPTP VPN Client pptp no such file\n";
		return null;
	}	
	$arrayPIDS=client_is_active($connexionname);
	if(is_array($arrayPIDS)){
		events("$connexionname already executed PIDs ". @implode(", ",$arrayPIDS)."",__FUNCTION__,__FILE__,__LINE__);
		echo "Starting......: PPTP VPN connection $connexionname already executed PIDs ". @implode(", ",$arrayPIDS)."\n";
		return;
	}
	
	echo "Starting......: PPTP VPN Client connection $connexionname@$server\n";
	$cmd="$nohup $pptp $server call $connexionname >/dev/null 2>&1 &";
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->$cmd\n";}
	shell_exec($cmd);
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->sleep(1)\n";}
	sleep(1);

	for($i=0;$i<5;$i++){
		$arrayPIDS=client_is_active($connexionname);
		if(is_array($arrayPIDS)){break;}
		sleep(1);
	}	
	
	
	if(is_array($arrayPIDS)){
		echo "Starting......: PPTP VPN connection $connexionname successfully started PIDs ". @implode(", ",$arrayPIDS)."\n";
		return;
	}

	echo "Starting......: PPTP VPN connection $connexionname failed\n";
	
}


function client_is_active($connexionname){
	if($GLOBALS["PGREP"]==null){
		$unix=new unix();
		$GLOBALS["PGREP"]=$unix->find_program("pgrep");
	}
	
	$cmd="{$GLOBALS["PGREP"]} -l -f \"pptp.+?call $connexionname\"";
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->$cmd\n";}
	exec($cmd,$results);
	
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#^([0-9]+).+?pptp#",$line,$re)){
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ->PID: {$re[1]}\n";}
			$arr[$re[1]]=$re[1];
		}else{
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." NO MATCH \"$line\"\n";}	
		}
		
	}
	
	return $arr;	
	
	
}


function ClientsStatus(){
	$unix=new unix();
	$pptp=$unix->find_program("pptp");
	if(!is_file($pptp)){return null;}
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("PPTPVpnClients")));
}


function vpn_msql_events($subject,$text,$IPPARAM){
	$subject=addslashes($subject);
	$text=addslashes($text);
	$time=time();
	$sql="INSERT INTO vpn_events (`stime`,`subject`,`text`,`IPPARAM`)
	VALUES('$time','$subject','$text','$IPPARAM')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){events($q->mysql_error ." $sql",__FUNCTION__,__FILE__,__LINE__);}
}


function events($text,$function,$file=null,$line=0){
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/pptp.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		
		if($GLOBALS["VERBOSE"]){writelogs($text,$function,$file,$line);}
		
		}






	
	