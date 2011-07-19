<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.openvpn.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.tcpip.inc');

$GLOBALS["server-conf"]=false;
$GLOBALS["IPTABLES_ETH"]=null;
$GLOBALS["CLASS_SOCKETS"]=new sockets();

if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}
if($GLOBALS["VERBOSE"]){echo "Debug mode TRUE for {$argv[1]}\n";}
$openvpn=new openvpn();
if(isset($openvpn->main_array["GLOBAL"]["IPTABLES_ETH"])){$GLOBALS["IPTABLES_ETH"]=$openvpn->main_array["GLOBAL"]["IPTABLES_ETH"];}
if($GLOBALS["IPTABLES_ETH"]==null){$GLOBALS["IPTABLES_ETH"]=IPTABLES_ETH_FIX();}

if($argv[1]=='--server-conf'){$GLOBALS["server-conf"]=true;writelogs("Starting......: OpenVPN building settings...","main",__FILE__,__LINE__);BuildTunServer();die();}
if($argv[1]=="--iptables-server"){BuildIpTablesServer();die();}
if($argv[1]=="--iptables-delete"){iptables_delete_rules();die();}
if($argv[1]=="--client-conf"){BuildOpenVpnClients();die();}
if($argv[1]=="--client-start"){StartOPenVPNCLients();die();}
if($argv[1]=="--client-stop"){StopOpenVPNCLients();die();}
if($argv[1]=="--client-configure"){BuildOpenVpnSingleClient($argv[2]);die();}
if($argv[1]=="--client-configure-start"){BuildOpenVpnSingleClient($argv[2]);OpenVPNCLientStart($argv[2]);die();}



if($argv[1]=="--server-stop"){StopServer();die();}
if($argv[1]=="--default-eth"){OpenVpnClientGetDefaultethLink();die();}
if($argv[1]=="--ipof"){echo GetIpaddrOf($argv[2])."\n";die();}
if($argv[1]=="--bridges"){print_r(GetBridgeExists($argv[2]))."\n";die();}
if($argv[1]=="--fix-routes"){BuildClientRoute($argv[2])."\n";die();}
if($argv[1]=="--schedule"){ServerScheduledTTL()."\n";die();}
if($argv[1]=="--windows-client"){windows_client($argv[2])."\n";die();}
if($argv[1]=="--argvs"){LoadArgvs()."\n";die();}
if($argv[1]=="--wakeup-server"){wakeup_server_mode()."\n";die();}
if($argv[1]=="--wakeup-clients"){wakeup_client_mode()."\n";die();}
if($argv[1]=="--remove-clients"){remove_client($argv[2])."\n";die();}



if($argv[1]=="--client-connect"){client_connect($argv)."\n";die(0);}



if($argv[1]=="--client-restart"){
	StopOpenVPNCLients();
	StartOPenVPNCLients();
	die();
}

writelogs("Starting......: OpenVPN Unable to understand this command-line (" .implode(" ",$argv).")","main",__FILE__,__LINE__);	
	
	

function BuildIpTablesServer(){
	iptables_delete_rules();
	$IPTABLES_ETH=$GLOBALS["IPTABLES_ETH"];
	
	if($IPTABLES_ETH==null){
		echo "Starting......: OpenVPN no prerouting set (IPTABLES_ETH)\n";
		return false;
	}
	if($GLOBALS["VERBOSE"]){echo "Starting......: OpenVPN: hook the $IPTABLES_ETH nic\n";}
	shell_exec2("/sbin/iptables -A INPUT -i tun0 -j ACCEPT -m comment --comment \"ArticaOpenVPN\"");
	shell_exec2("/sbin/iptables -A FORWARD -i tun0 -j ACCEPT -m comment --comment \"ArticaOpenVPN\"");
	shell_exec2("/sbin/iptables -A OUTPUT -o tun0 -j ACCEPT -m comment --comment \"ArticaOpenVPN\"");
	shell_exec2("/sbin/iptables -t nat -A POSTROUTING -o $IPTABLES_ETH -j MASQUERADE -m comment --comment \"ArticaOpenVPN\"");

	shell_exec2("/sbin/iptables -A INPUT -i $IPTABLES_ETH -j ACCEPT -m comment --comment \"ArticaOpenVPN\"");
	shell_exec2("/sbin/iptables -A FORWARD -i $IPTABLES_ETH -j ACCEPT -m comment --comment \"ArticaOpenVPN\"");
	shell_exec2("/sbin/iptables -A OUTPUT -o $IPTABLES_ETH -j ACCEPT -m comment --comment \"ArticaOpenVPN\"");
	shell_exec2("/sbin/iptables -t nat -A POSTROUTING -o tun0 -j MASQUERADE -m comment --comment \"ArticaOpenVPN\"");
	
	
	
	echo "Starting......: OpenVPN prerouting success from tun0 -> $IPTABLES_ETH...\n";
	
}

function shell_exec2($cmd){
	if($GLOBALS["VERBOSE"]){echo "Starting......: OpenVPN: executing \"$cmd\"\n";}
	shell_exec($cmd);
	
}


function StopServer(){
	$unix=new unix();
    $openvpn=$unix->find_program("openvpn");
    $brctl=$unix->find_program("brctl");
    $ifconfig=$unix->find_program("ifconfig");
    $ip_tools=$unix->find_program("ip");	
    $pgrep=$unix->find_program("pgrep");
    $kill=$unix->find_program("kill");
	$ini=new Bs_IniHandler();
    $sock=new sockets();
    $ini->loadString($sock->GET_INFO("ArticaOpenVPNSettings"));
    $BRIDGE_ETH=$ini->_params["GLOBAL"]["BRIDGE_ETH"]; 
    echo "Stopping OpenVPN......................: stopping server bridged on=$BRIDGE_ETH\n";   
	if(preg_match("#(.+?):([0-9]+)#",$BRIDGE_ETH,$re)){$original_eth=$re[1];}
	if($original_eth<>null){
		$array_ip=BuildBridgeServer_eth_infos($BRIDGE_ETH);
		echo "Stopping OpenVPN......................: checking bridges and $original_eth\n";
		$array=GetBridgeExists("br0");
	
		if(is_array($array)){
			echo "Stopping OpenVPN......................: Bridge br0 exists\n";
			system("$ifconfig br0 down");
			while (list ($num, $ligne) = each ($array) ){
				echo "Stopping OpenVPN......................: remove $ligne from br0\n";
				system("brctl delif br0 $ligne");
			}
			
			echo "Stopping OpenVPN......................: remove br0\n";
			system("brctl delbr br0");
			system("$ifconfig $original_eth down");
			}
			
			echo "Stopping OpenVPN......................: rebuild $original_eth settings\n";
			system("$ifconfig $original_eth up");
			if(GetIpaddrOf($original_eth)==null){
				if(preg_match("#^(.+?)\.([0-9]+)$#",$array_ip["IPADDR"],$re)){$eth_broadcast="broadcast {$re[1]}.255";}
				system("$ifconfig $original_eth {$array_ip["IPADDR"]} netmask {$array_ip["NETMASK"]} $eth_broadcast");
			}
			system("$ip_tools route add default via {$array_ip["GATEWAY"]} dev $original_eth  proto static");
	}
	
	
	echo "Stopping OpenVPN......................: find ghost processes\n";
	exec("$pgrep -l -f \"$openvpn --port.+?--dev\" 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+#", $ligne,$re)){
			if($unix->process_exists($re[1])){
				echo "Stopping OpenVPN......................: {$re[1]} PID\n"; 
				shell_exec("$kill -9 {$re[1]} >/dev/null 2>&1");
			}
		}
		
	}
	
	
	
	iptables_delete_rules();

}





function iptables_delete_rules(){
shell_exec("/sbin/iptables-save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaOpenVPN#";	
$count=0;
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){$count++;continue;}
		$conf=$conf . $ligne."\n";
		}

file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
shell_exec("/sbin/iptables-restore < /etc/artica-postfix/iptables.new.conf");
echo "Starting......: OpenVPN cleaning iptables $count rules\n";

}

function iptables_delete_client_rules($ID=0){
	echo "Starting......: OpenVPN cleaning iptables rules for ID $ID\n";
$conf=null;
shell_exec("/sbin/iptables-save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
if($ID==0){
	$pattern="#.+?ArticaVPNClient_[0-9]+#";
}else{
	$pattern='#.+?ArticaVPNClient_'.$ID.'"#';
}	
$count=0;
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){$count++;continue;}
		$conf=$conf . $ligne."\n";
		}

file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
shell_exec("/sbin/iptables-restore < /etc/artica-postfix/iptables.new.conf");
echo "Starting......: OpenVPN cleaning iptables $count rules\n";	
}

function BuildBridgeServer(){
	$unix=new unix();
	if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
    $openvpn=$unix->find_program("openvpn");
    $brctl=$unix->find_program("brctl");
    $ifconfig=$unix->find_program("ifconfig");
    $ip_tools=$unix->find_program("ip");
    $routess=array();
    if($openvpn==null){
    	echo "Starting......: OpenVPN bridge unable to stat openvpn binary\n";
    	@unlink("/etc/openvpn/cmdline.conf");
    	exit;
    }
    
    if($brctl==null){
    	echo "Starting......: OpenVPN bridge unable to stat brctl binary\n";
    	@unlink("/etc/openvpn/cmdline.conf");
    	exit;
    }  

    if($ifconfig==null){
    	echo "Starting......: OpenVPN bridge unable to stat ifconfig binary\n";
    	@unlink("/etc/openvpn/cmdline.conf");
    	exit;
    }

    if($ip_tools==null){
    	echo "Starting......: OpenVPN bridge unable to stat ip binary\n";
    	@unlink("/etc/openvpn/cmdline.conf");
    	exit;
    }      
    
    $servername=$unix->hostname_g();	
  	if(preg_match("#^(.+?)\.#",$servername,$re)){$servername=$re[1];}
    $servername=strtoupper($servername);    	
    $ini=new Bs_IniHandler();
    $sock=new sockets();
    $ini->loadString($sock->GET_INFO("ArticaOpenVPNSettings"));
    $BRIDGE_ETH=$ini->_params["GLOBAL"]["BRIDGE_ETH"];
    $BRIDGE_ADDR=$ini->_params["GLOBAL"]["BRIDGE_ADDR"];
    $array_ip=BuildBridgeServer_eth_infos($BRIDGE_ETH);
    
    
   $ca='/etc/artica-postfix/openvpn/keys/allca.crt';
   $dh='/etc/artica-postfix/openvpn/keys/dh1024.pem';
   $key="/etc/artica-postfix/openvpn/keys/vpn-server.key";
   $crt="/etc/artica-postfix/openvpn/keys/vpn-server.crt";    
    
    if(preg_match("#(.+?):([0-9]+)#",$BRIDGE_ETH,$re)){$original_eth=$re[1];}
    
    if($array_ip["IPADDR"]==null){
    	echo "Starting......: OpenVPN bridge for $BRIDGE_ETH (failed to get IP informations)...\n";	
    	return;
    }
    
if(preg_match("#^(.+?)\.([0-9]+)$#",$array_ip["IPADDR"],$re)){$eth_broadcast=$re[1].".255";}
    echo "Starting......: OpenVPN bridge for tap0 -> $original_eth {$array_ip["IPADDR"]}/$eth_broadcast...\n";
    $br0_ip=GetIpaddrOf("br0");
    echo "Starting......: OpenVPN bridge for br0=$br0_ip\n";
    
    if($br0_ip==null){
    	echo "Starting......: OpenVPN bridge creating tap0\n";
    	system("$openvpn --mktun --dev tap0");
    	system("$brctl addbr br0");
    	system("$brctl addif br0 tap0");
    	system("$brctl addif br0 $original_eth");
    	system("$ifconfig $original_eth 0.0.0.0 promisc up");
    	system("$ifconfig tap0 0.0.0.0 promisc up");
    	system("$ifconfig br0 {$array_ip["IPADDR"]} netmask {$array_ip["NETMASK"]} broadcast $eth_broadcast");
		$br0_ip=GetIpaddrOf("br0");  
		if($br0_ip==null){
			   echo "Starting......: OpenVPN failed to create bridge rolling back\n";
			   StopServer();
			   return; 
		}
		system("$ip_tools route add default via {$array_ip["GATEWAY"]} dev br0 proto static");
		
    }
		
		
    $OpenVpnPasswordCert=$sock->GET_INFO("OpenVpnPasswordCert");
	if($OpenVpnPasswordCert==null){$OpenVpnPasswordCert="MyKey";}
   
   	if(is_file("/etc/artica-postfix/openvpn/keys/password")){
   		$askpass=" --askpass /etc/artica-postfix/openvpn/keys/password ";
   	}		
	
   	$routess=$routess+GetRoutes();
   	if(is_array($routess)){$routes=implode(" ",$routess);}
		
   $port=$ini->_params["GLOBAL"]["LISTEN_PORT"];
   $server_bridge="--server-bridge $BRIDGE_ADDR {$array_ip["NETMASK"]} {$ini->_params["GLOBAL"]["VPN_DHCP_FROM"]} {$ini->_params["GLOBAL"]["VPN_DHCP_TO"]}";
   $cmd=" --port $port --dev tap0 $server_bridge --comp-lzo $local --ca $ca --dh $dh --key $key --cert $crt";
   $cmd=$cmd. " --ifconfig-pool-persist /etc/artica-postfix/openvpn/ipp.txt $routes";
   $cmd=$cmd. " $askpass--client-to-client -persist-tun -verb 5 --daemon --writepid /var/run/openvpn/openvpn-server.pid --log \"/var/log/openvpn/openvpn.log\"";
   $cmd=$cmd. " --status /var/log/openvpn/openvpn-status.log 10";
   @file_put_contents("/etc/openvpn/cmdline.conf",$cmd);

   
   
    
	
}



function GetBridgeExists($br){
	$unix=new unix();
	if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
    $brctl=$unix->find_program("brctl");	
	exec("$brctl showstp $br 2>&1",$results);	
	if($GLOBALS["VERBOSE"]){echo count($results)." lines for $brctl showstp $br\n";}
	
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^([a-zA-Z]+)([0-9]+)#",$line,$re)){
			
			if($re[1]=="br"){continue;}
			$array[]="{$re[1]}{$re[2]}";
		}else{
		
		}
	}

	return $array;
}

function LoadArgvs(){
	$unix=new unix();
	$openvpn=$unix->find_program("openvpn");
	exec("$openvpn --help 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^\-\-(.+?)[\s\:]+#", $line,$re)){
			$GLOBALS["OPENVPNPARAMS"][$re[1]]=1;
		}
	}
	
	if($GLOBALS["VERBOSE"]){
		print_r($GLOBALS["OPENVPNPARAMS"]);
	}
}


function GetIpaddrOf($eth){
	$unix=new unix();
	if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
    $ip_tools=$unix->find_program("ip");	
	exec("$ip_tools -f inet  address show $eth 2>&1",$results);
	
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#inet\s+([0-9\.]+)\/#",$line,$re)){
			return $re[1];
		}
	}
}

function BuildBridgeServer_eth_infos($BRIDGE_ETH){
		$eth=$BRIDGE_ETH;
		if(!preg_match("#(.+?):([0-9]+)#",$eth,$re)){return array();}
		$sql="SELECT * FROM nics_virtuals WHERE ID={$re[2]}";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if(!$q->ok){
			echo "Starting......: OpenVPN mysql error $q->mysql_error (". __FUNCTION__.")\n";	
		}
		if($ligne["ipaddr"]==null){
			echo "Starting......: OpenVPN $BRIDGE_ETH (has $eth ID={$re[2]}) has no information\n";
		}
		$array_ip["IPADDR"]=$ligne["ipaddr"];
		$array_ip["NETMASK"]=$ligne["netmask"];
		$array_ip["GATEWAY"]=$ligne["gateway"];	
		return $array_ip;
}


function GetRoutes(){
   $routess=array();
   $cleaned_routes=array();
   
   $ini=new Bs_IniHandler();
   if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
   $ini->loadString($sock->GET_INFO("ArticaOpenVPNSettings"));
   if(!isset($ini->_params["GLOBAL"]["ENABLE_BRIDGE_MODE"])){$ini->_params["GLOBAL"]["ENABLE_BRIDGE_MODE"]=0;}
   
   
   if($ini->_params["GLOBAL"]["ENABLE_BRIDGE_MODE"]==1){
   		$BRIDGE_ETH=$ini->_params["GLOBAL"]["BRIDGE_ETH"];
   		$array_ip=BuildBridgeServer_eth_infos($BRIDGE_ETH);
   		echo "Starting......: OpenVPN binding $BRIDGE_ETH to \"{$array_ip["IPADDR"]}/{$array_ip["NETMASK"]}\"\n";
   		if(preg_match("#(.+?)\.[0-9]+$#",trim($array_ip["IPADDR"]),$re)){
   			$LOCAL_ROUTE="{$re[1]}.0 {$array_ip["NETMASK"]}";
   			$LOCAL_ROUTE=str_replace("Array","",$LOCAL_ROUTE);
   		    echo "Starting......: OpenVPN default Local route \"$LOCAL_ROUTE\"\n";
   			if(trim($LOCAL_ROUTE<>null)){$routess[]="--push \"route $LOCAL_ROUTE\"";}
   		}
   }

if (is_file('/etc/artica-postfix/settings/Daemons/OpenVPNRoutes')){
   $routes=(explode("\n",@file_get_contents("/etc/artica-postfix/settings/Daemons/OpenVPNRoutes")));
   while (list ($num, $ligne) = each ($routes) ){
   	if(!preg_match("#(.+?)\s+(.+)#",$ligne,$re)){continue;}
   	$re[1]=trim($re[1]);
   	$re[2]=trim($re[2]);
   	$routess[]="--push \"route {$re[1]} {$re[2]}\"";
   }
}
if(count($routess)>0){while (list ($index, $route) = each ($routess) ){$cleaned_routes[$route]=$route;}}
if(count($cleaned_routes)>0){ 
	$c=array();
	while (list ($a, $b) = each ($cleaned_routes) ){
		 echo "Starting......: OpenVPN route $b\n";
		$c[]=$b;}

	}

if(isset($c)){return $c;}else{return array();}

	
}

function BuildTunServer(){
LoadArgvs();
   $unix=new unix();
   if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
   $servername=$unix->hostname_g();	
   $routess=array();
   $duplicate_cn=null;
 
   
  if(preg_match("#^(.+?)\.#",$servername,$re)){$servername=$re[1];}
   $servername=strtoupper($servername);       
   echo "Starting......: OpenVPN building settings for $servername...\n";
   
   
   
   $ini=new Bs_IniHandler();
   
   $ini->loadString($sock->GET_INFO("ArticaOpenVPNSettings"));
   if(!isset($ini->_params["GLOBAL"]["ENABLE_BRIDGE_MODE"])){$ini->_params["GLOBAL"]["ENABLE_BRIDGE_MODE"]=0;}
   if(!isset($ini->_params["GLOBAL"]["IP_START"])){$ini->_params["GLOBAL"]["IP_START"]="10.8.0.0";}
   if(!isset($ini->_params["GLOBAL"]["NETMASK"])){$ini->_params["GLOBAL"]["NETMASK"]="255.255.255.0";}
   
   
   if($ini->_params["GLOBAL"]["ENABLE_BRIDGE_MODE"]==1){
   		echo "Starting......: OpenVPN building settings mode bridge enabled...\n";
   		BuildBridgeServer();
   		return;
   }
   
   
   $IPTABLES_ETH=$GLOBALS["IPTABLES_ETH"];
   $DEV_TYPE=$ini->_params["GLOBAL"]["DEV_TYPE"];
   $port=$ini->_params["GLOBAL"]["LISTEN_PORT"];
   $IP_START=$ini->_params["GLOBAL"]["IP_START"];
   $NETMASK=$ini->_params["GLOBAL"]["NETMASK"];
   $bind_addr=$ini->_params["GLOBAL"]["LOCAL_BIND"];
   $LISTEN_PROTO=$ini->_params["GLOBAL"]["LISTEN_PROTO"];
   if($LISTEN_PROTO==null){$LISTEN_PROTO="udp";}
   if($LISTEN_PROTO=="udp"){$proto="--proto udp";}else{$proto="--proto tcp-server";}
   
    
   if(trim($port)==null){$port=1194;}
   if(trim($IP_START)==null){$IP_START="10.8.0.0";}
   if(trim($NETMASK)==null){$NETMASK="255.255.255.0";}
   
$nic=new networking();

while (list ($num, $ligne) = each ($nic->array_TCP) ){
	if($ligne==null){continue;}
		$eths[][$num]=$num;
		$ethi[$num]=$ligne;
	} 

if($IPTABLES_ETH<>null){
		echo "Starting......: OpenVPN linked to $IPTABLES_ETH ({$ethi[$IPTABLES_ETH]})...\n";
		$IPTABLES_ETH_ROUTE=IpCalcRoute($ethi[$IPTABLES_ETH]);
}else{
	echo "Starting......: OpenVPN no local NIC linked...\n";
}
	
   $ca='/etc/artica-postfix/openvpn/keys/allca.crt';
   $dh='/etc/artica-postfix/openvpn/keys/dh1024.pem';
   $key="/etc/artica-postfix/openvpn/keys/vpn-server.key";
   $crt="/etc/artica-postfix/openvpn/keys/vpn-server.crt";
   $route='';
   
   //$IPTABLES_ETH_IP=

if (is_file('/etc/artica-postfix/settings/Daemons/OpenVPNRoutes')){
   $routes=(explode("\n",@file_get_contents("/etc/artica-postfix/settings/Daemons/OpenVPNRoutes")));
   while (list ($num, $ligne) = each ($routes) ){
   	if(!preg_match("#(.+?)\s+(.+)#",$ligne,$re)){continue;}
   	$routess[]="--push \"route {$re[1]} {$re[2]}\"";
   }
}
$GetRoutes=GetRoutes();
$routess=$routess+$GetRoutes;



if(count($routess)==0){
	if($IPTABLES_ETH_ROUTE<>null){
		echo "Starting......: OpenVPN IP adding default route \"$IPTABLES_ETH_ROUTE\"\n";
		$routess[]="--push \"route $IPTABLES_ETH_ROUTE\"";
	}
  }else{
  	echo "Starting......: OpenVPN IP adding ".count($routess)." routes\n";
  }
   

	
   if(trim($bind_addr)<>null){
   	$local=" --local $bind_addr";
   	echo "Starting......: OpenVPN IP bind $bind_addr\n";
   }
   
   $IP_START=FIX_IP_START($IP_START,$local);
   $ini->set("GLOBAL","IP_START",$IP_START); 	
  
   if(preg_match("#(.+?)\.([0-9]+)$#",$IP_START,$re)){
   	$calc_ip=" {$re[1]}.0";
   	$calc_ip_end="{$re[1]}.254";
   	echo "Starting......: OpenVPN IP pool from {$re[1]}.2 to {$re[1]}.254 mask:$NETMASK\n";
   	$server_ip="{$re[1]}.1";
   	$IP_START_PREFIX=$re[1];
   }

   if($NETMASK==null){
			$ip=new IP();
			$cdir=$ip->ip2cidr($calc_ip,$calc_ip_end);
			$arr=$ip->parseCIDR($cdir);
			$rang=$arr[0];
			$netbit=$arr[1];
			$ipv=new ipv4($calc_ip,$netbit);
			$NETMASK=$ipv->netmask();	   
			if($NETMASK=="255.255.255.255"){$NETMASK="255.255.255.0";}		
   			echo "Starting......: OpenVPN Netmask is null for the range $calc_ip, assume $NETMASK\n";
   			$ini->set("GLOBAL","NETMASK",$NETMASK);
   	}
   	
	$OpenVpnPasswordCert=$sock->GET_INFO("OpenVpnPasswordCert");
	if($OpenVpnPasswordCert==null){$OpenVpnPasswordCert="MyKey";}
   
	$askpass=null;
   	if(is_file("/etc/artica-postfix/openvpn/keys/password")){
   		$askpass=" --askpass /etc/artica-postfix/openvpn/keys/password ";
   	}
   	
   	$ifconfig_pool_persist=" --ifconfig-pool-persist /etc/artica-postfix/openvpn/ipp.txt ";
   	
 	if(isset($GLOBALS["OPENVPNPARAMS"]["duplicate-cn"])){
 		echo "Starting......: OpenVPN duplicate-cn is enabled\n";
 		$duplicate_cn=" --duplicate-cn ";
 		$ifconfig_pool_persist=null;
 	}
 	
 	if(isset($GLOBALS["OPENVPNPARAMS"]["script-security"])){
 		echo "Starting......: OpenVPN script-security is enabled\n";
 		$script_security=" --script-security 2";
 	} 	
 	
	if(!is_dir("/etc/openvpn/cdd")){@mkdir("/etc/openvpn/cdd");}
	$already=array();
 	echo "Starting......: OpenVPN get remote sites routes...\n";
 	$sql="SELECT sitename,IP_START,netmask,remote_site_routes,FixedIPAddr FROM vpnclient WHERE connexion_type=1";
 	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo2("Starting......: OpenVPN : $q->mysql_error");}
 	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
 		$iroute=array();
 		$sitename=$ligne["sitename"];
 		$FixedIPAddr=$ligne["FixedIPAddr"];
 		if(!is_numeric($FixedIPAddr)){$FixedIPAddr=0;}
 		
 		if($IP_START_PREFIX<>null){
 			if($FixedIPAddr>2){
 				if($FixedIPAddr<255){
 					echo "Starting......: OpenVPN $sitename $IP_START_PREFIX.$FixedIPAddr fixed IP address\n";
 					$iroute[]="ifconfig-push $IP_START_PREFIX.$FixedIPAddr $IP_START_PREFIX.2";
 				}
 			}
 		}
 		
 		if(!isset($already[$ligne["IP_START"]])){
 			echo "Starting......: OpenVPN $sitename ({$ligne["IP_START"]} {$ligne["netmask"]})\n";
 			$rou[]=" --route {$ligne["IP_START"]} {$ligne["netmask"]}";
 			$iroute[]="iroute {$ligne["IP_START"]} {$ligne["netmask"]}";
 			$already[$ligne["IP_START"]]=true;
 		}
 		
 		$remote_site_routes=unserialize(base64_decode($ligne["remote_site_routes"]));
		while (list ($num, $site_mask) = each ($remote_site_routes) ){
			if(!isset($already[$num])){
				echo "Starting......: OpenVPN $sitename ($num $site_mask)\n";
				$rou[]=" --route $num $site_mask";
				$iroute[]="iroute $num $site_mask";
				$already[$num]=true;
			}
		}
 		echo "Starting......: OpenVPN cdd $sitename\n";
 		@file_put_contents("/etc/openvpn/cdd/$sitename",@implode("\n", $iroute) );
 		
 	}
 	if(count($rou)>0){
 		$localroutes=@implode(" ", $rou);
 		$client_config_dir=" --client-config-dir /etc/openvpn/cdd";
 	}
 	
 	 
 	
 	$LDAP_AUTH=$ini->_params["GLOBAL"]["LDAP_AUTH"];
 	if($LDAP_AUTH==1){
 		if(is_file("/usr/lib/openvpn/openvpn-auth-pam.so")){
 		$plugin=" --plugin /usr/lib/openvpn/openvpn-auth-pam.so common-auth";
 		echo "Starting......: OpenVPN auth is enabled\n";
 		shell_exec("/usr/share/artica-postfix/bin/artica-install --nsswitch");
 		}
 	}
   
   @mkdir("/etc/openvpn/ccd",0666,true);
   $php5=$unix->LOCATE_PHP5_BIN();
   $me=__FILE__;
   $cmd=" --port $port --dev tun $proto --server $IP_START $NETMASK$localroutes$client_config_dir --comp-lzo $local --ca $ca --dh $dh --key $key --cert $crt";
   $cmd=$cmd. "$ifconfig_pool_persist " . implode(" ",$routess);
   $cmd=$cmd. " $askpass$duplicate_cn--client-to-client$script_security$plugin --learn-address \"$php5 $me --client-connect\" --keepalive 10 60 --persist-tun --verb 5 --daemon --writepid /var/run/openvpn/openvpn-server.pid --log \"/var/log/openvpn/openvpn.log\"";
   $cmd=$cmd. " --status /var/log/openvpn/openvpn-status.log 10";
   echo "Starting......: OpenVPN building /etc/openvpn/cmdline.conf done\n";
   @file_put_contents("/etc/openvpn/cmdline.conf",$cmd);
  
   
   $sock->SaveConfigFile($ini->toString(),"ArticaOpenVPNSettings");
   send_email_events("OpenVPN was successfully reconfigured",$cmd,"VPN");
   echo "Starting......: OpenVPN building settings done.\n";
   if($GLOBALS["VERBOSE"]){writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);}
}

function FIX_IP_START($ip,$local){
	$original_ip=$ip;
	if(preg_match("#(.+?)\/#",$ip,$re)){$ip=$re[1];}
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$ip,$re)){
		$ip=$re[1].".".$re[2].".".$re[3].".0";
		$ip_1=$re[1];
		$ip_2=$re[2];
		$ip_3=$re[3];
		
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$local,$re)){
		$local=$re[1].".".$re[2].".".$re[3].".0";
	}

	if($ip==$local){
		if($ip_1<255){$ip_1=$ip_1+1;}
		if($ip_2<255){$ip_2=$ip_2+1;}
		if($ip_3<255){$ip_3=$ip_3+1;}
		
		echo "Starting......: OpenVPN bad server parameters $original_ip.\n";
		echo "Starting......: OpenVPN vpn dhcp parameters ($ip) must no reflect local network ($local).\n";
		echo "Starting......: OpenVPN change automatically to $ip_1.$ip_2.$ip_3.0.\n";
		$ip="$ip_1.$ip_2.$ip_3.0";
	}
	
	if($ip=="255.255.255.0"){
		
		if("10.8.0.0"<>$local){
			echo "Starting......: OpenVPN $ip seems to be a netmask not an IP address change to 10.8.0.0\n";
			return "10.8.0.0";
		}
	}
	
	return $ip;
	
}

function IPTABLES_ETH_FIX(){
$nic=new networking();

while (list ($num, $ligne) = each ($nic->array_TCP) ){
	if($ligne==null){continue;}
		$eths[][$num]=$num;
		$ethi[$num]=$ligne;
	} 

if($GLOBALS["IPTABLES_ETH"]==null){
	while (list ($num, $ligne) = each ($ethi) ){
		if(preg_match("#^eth[0-9]+#",$num)){
			if($GLOBALS["server-conf"]){echo "Starting......: OpenVPN no local NIC linked: assume $num ($ligne)...\n";}
			return $num;
		}
	}
}	
}


function IpCalcRoute($ipsingle){
	 if(!preg_match("#(.+?)\.([0-9]+)$#",$ipsingle,$re)){
	 	writelogs("Unable to match $ipsingle",__FUNCTION__,__FILE__,__LINE__);
	 	return null;
	 	}

	 $unix=new unix();
	 $tmp=$unix->FILE_TEMP();
	 
	 shell_exec("/usr/share/artica-postfix/bin/ipcalc {$re[1]}.0 >$tmp 2>&1");
	 
	 $arr=explode("\n",@file_get_contents($tmp));
	 @unlink($tmp);
	
	 
		while (list ($num, $ligne) = each ($arr) ){
			
			if(preg_match("#^Netmask:\s+(.+?)\s+#",$ligne,$ri)){
				return "{$re[1]}.0 {$ri[1]}";
			}
			 
			
		}

}


function Synchronize_clients(){
	$main_path="/etc/artica-postfix/openvpn/clients";
	$unix=new unix();
	shell_exec("sysctl -w net.ipv4.ip_forward=1");
	$tbl=$unix->dirdir($main_path);
	$q=new mysql();
	if(!is_array($tbl)){return;}
	while (list ($num, $id) = each ($tbl) ){
		if(!preg_match("#/etc/artica-postfix/openvpn/clients/([0-9]+)#",$num,$re)){continue;}
		$id=$re[1];
		echo "Starting......: OpenVPN checking client ID:$id\n";
		$id=trim($id);
		$mustkill=false;
		$sql="SELECT ID,enabled FROM vpnclient WHERE connexion_type=2 AND ID=$id";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if(!$q->ok){continue;}
		if($ligne["ID"]==null){$mustkill=true;}
		if($ligne["enabled"]==0){$mustkill=true;}
		if($mustkill){
			$pid=$unix->process_exists(vpn_client_pid($id));
			if($unix->process_exists(vpn_client_pid($id))){
				shell_exec("/bin/kill $pid >/dev/null 2>&1");
			}
			shell_exec("/bin/rm -rf $main_path/$id >/dev/null");	
		}
		
	}	
	
}


function BuildOpenVpnClients(){
	chdir("/root");
	$main_path="/etc/artica-postfix/openvpn/clients";
	$sql="SELECT * FROM vpnclient WHERE connexion_type=2 and enabled=1 ORDER BY ID";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "Starting......: OpenVPN client, mysql database error, starting from cache\n";return null;}
	@mkdir("/etc/artica-postfix/openvpn/clients",0666,true);
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$subpath="$main_path/{$ligne["ID"]}";
		@mkdir("$subpath",0666,true);
		$password=base64_decode($ligne["keypassword"]);
		if($password==null){$password="MyKey";}
		echo "Starting......: OpenVPN client, building configuration for {$ligne["connexion_name"]}\n";
		@file_put_contents("$subpath/ca.crt",$ligne["ca_bin"]);
		@file_put_contents("$subpath/certificate.crt",$ligne["cert_bin"]);
		@file_put_contents("$subpath/master-key.key",$ligne["key_bin"]);
		@file_put_contents("$subpath/settings.ovpn",$ligne["ovpn"]);
		@file_put_contents("$subpath/ethlink",$ligne["ethlisten"]);
		@file_put_contents("$subpath/keypassword",$password);
		@file_put_contents("$subpath/routes_additionnal",$ligne["routes_additionnal"]);
		@unlink("$subpath/auth-user-pass");
		if($ligne["EnableAuth"]==1){
			echo "Starting......: OpenVPN client, remote authentication is enabled for {$ligne["connexion_name"]}\n";
			@file_put_contents("$subpath/auth-user-pass","{$ligne["AuthUsername"]}\n{$ligne["AuthPassword"]}");
		}

		
		
		
		BuildOpenVpnClients_changeConfig($subpath,"{$ligne["ID"]}");
		}
		
}


function remove_client($ID){
	$sql="DELETE * FROM vpnclient WHERE ID=$ID";
	$q=new mysql();		
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	
}


function BuildOpenVpnSingleClient($ID){
	chdir("/root");
	$main_path="/etc/artica-postfix/openvpn/clients";
	$sql="SELECT * FROM vpnclient WHERE ID=$ID";
	$q=new mysql();	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	if(!$q->ok){echo "Starting......:  OpenVPN client ID $ID, mysql database error, starting from cache\n";return null;}
	$subpath="$main_path/$ID";
	@mkdir("$subpath",0666,true);
	$password=base64_decode($ligne["keypassword"]);
	if($password==null){$password="MyKey";}
	echo "Starting......: OpenVPN client ID $ID, building configuration for {$ligne["connexion_name"]}\n";
	@file_put_contents("$subpath/ca.crt",$ligne["ca_bin"]);
	@file_put_contents("$subpath/certificate.crt",$ligne["cert_bin"]);
	@file_put_contents("$subpath/master-key.key",$ligne["key_bin"]);
	@file_put_contents("$subpath/settings.ovpn",$ligne["ovpn"]);
	@file_put_contents("$subpath/ethlink",$ligne["ethlisten"]);
	@file_put_contents("$subpath/keypassword",$password);
	@file_put_contents("$subpath/routes_additionnal",$ligne["routes_additionnal"]);
	@unlink("$subpath/auth-user-pass");
	if($ligne["EnableAuth"]==1){
		echo "Starting......: OpenVPN client ID $ID, remote authentication is enabled for {$ligne["connexion_name"]}\n";
		@file_put_contents("$subpath/auth-user-pass","{$ligne["AuthUsername"]}\n{$ligne["AuthPassword"]}");
	}	
	BuildOpenVpnClients_changeConfig($subpath,$ID);	
	
	
}

function vpn_client_pid($id){
	
	if(is_file("/etc/artica-postfix/openvpn/clients/$id/pid")){
		return trim(@file_get_contents("/etc/artica-postfix/openvpn/clients/$id/pid"));
	}
	
	$unix=new unix();
	exec($unix->find_program("pgrep"). " -f \"openvpn.+?\/etc\/artica-postfix\/openvpn\/clients\/$id\/settings\.ovpn\" -l",$re);
	
	while (list ($num, $ligne) = each ($re) ){
		if(!preg_match("#^([0-9]+)\s+(.+)#",$ligne,$ri)){continue;}
		if(!preg_match("#pgrep -f#",$ri[2])){
			$cmdline=$ri[2];
			$pid=$ri[1];
			break;
		}
		
	}
	
	return $pid;
	
}
function vpn_client_pids($id){
	$unix=new unix();
	exec($unix->find_program("pgrep"). " -f \"openvpn.+?\/etc\/artica-postfix\/openvpn\/clients\/$id\/settings\.ovpn\"",$re);
	return $re;
	
}
function vpn_client_allpids(){
	$unix=new unix();
	exec($unix->find_program("pgrep"). " -f \"openvpn.+?\/etc\/artica-postfix\/openvpn\/clients\/[0-9]+\/settings\.ovpn\"",$re);
	while (list ($num, $pid) = each ($re) ){
		$pid=trim($pid);
		if($pid==null){continue;}
		if($pid==0){continue;}
		$rr[]=$pid;
	}
	
	return $rr;
	
}

function StartOpenVPNCLients(){
	$main_path="/etc/artica-postfix/openvpn/clients";
	Synchronize_clients();
	$unix=new unix();
	$tbl=$unix->dirdir($main_path);
	if(!is_array($tbl)){return null;}
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match("#/etc/artica-postfix/openvpn/clients/([0-9]+)#",$num,$re)){
			echo "Starting......: OpenVPN client ID {$re[1]}\n";
			OpenVPNCLientStart($re[1]);
		}
	}
	
	
	
}
function StopOpenVPNCLients(){
	chdir("/root");
	$pids=vpn_client_allpids();
	if(!is_array($pids)){
		echo "Stopping OpenVPN clients..............: stopped\n"; 
		return;
	}
	
	echo "Stopping OpenVPN clients..............: ". implode(", ",$pids)."\n";
	
	while (is_array($pids)) {
		while (list ($num, $pid) = each ($pids) ){
			if($pid==null){continue;}
			if($pid==0){continue;}
			echo "Stopping OpenVPN clients..............: PID $pid\n";
			shell_exec("/bin/kill $pid >/dev/null 2>&1");
			sleep(1);
		}
		
		$pids=vpn_client_allpids();
	}
	
	iptables_delete_client_rules(0);
	BuildOpenVpnClients();
	
	
	
}
function BuildIpTablesClient($eth,$tun_id){
	
	
	
	if($eth==null){
		echo "Starting......: OpenVPN no prerouting set (IPTABLES_ETH)\n";
		return false;
	}
	
	echo "Starting......: OpenVPN tun$tun_id Enable IP Forwarding\n";
    shell_exec2("sysctl -w net.ipv4.ip_forward=1");         
   
	shell_exec2("/sbin/iptables -A INPUT -i tun$tun_id -j ACCEPT -m comment --comment \"ArticaVPNClient_$tun_id\"");
	shell_exec2("/sbin/iptables -A FORWARD -i tun$tun_id -j ACCEPT -m comment --comment \"ArticaVPNClient_$tun_id\"");
	shell_exec2("/sbin/iptables -A OUTPUT -o tun$tun_id -j ACCEPT -m comment --comment \"ArticaVPNClient_$tun_id\"");
	shell_exec2("/sbin/iptables -t nat -A POSTROUTING -o $eth -j MASQUERADE -m comment --comment \"ArticaVPNClient_$tun_id\"");
	
	//iptables -t nat -A POSTROUTING -i tun8 -o eth0 -j MASQUERADE
	//iptables -t nat -A POSTROUTING -i eth0 -o tun0 -j MASQUERADE
	

	shell_exec2("/sbin/iptables -A INPUT -i $eth -j ACCEPT -m comment --comment \"ArticaVPNClient_$tun_id\"");
	shell_exec2("/sbin/iptables -A FORWARD -i $eth -j ACCEPT -m comment --comment \"ArticaVPNClient_$tun_id\"");
	shell_exec2("/sbin/iptables -A OUTPUT -o $eth -j ACCEPT -m comment --comment \"ArticaVPNClient_$tun_id\"");
	shell_exec2("/sbin/iptables -t nat -A POSTROUTING -o tun$tun_id -j MASQUERADE -m comment --comment \"ArticaVPNClient_$tun_id\"");
	
	echo "Starting......: OpenVPN prerouting success for tun$tun_id...\n";
	
}


function OpenVPNCLientStartGetDev($id){
	$main_path="/etc/artica-postfix/openvpn/clients";
	$datas=explode("\n",@file_get_contents("$main_path/$id/settings.ovpn"));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^dev\s+tun([0-9]+)#",$line,$re)){
			return "tun{$re[1]}";
		}
	}		
}
function OpenVPNCLientStartGetTAPDev($id){
	$main_path="/etc/artica-postfix/openvpn/clients";
	$datas=explode("\n",@file_get_contents("$main_path/$id/settings.ovpn"));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^dev\s+tap([0-9]+)#",$line,$re)){
			return "tap{$re[1]}";
		}
	}		
}

function OpenVPNCLientIsOnTap($id){
$main_path="/etc/artica-postfix/openvpn/clients";
	$datas=explode("\n",@file_get_contents("$main_path/$id/settings.ovpn"));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^dev.+?tap#",$line,$re)){
			return true;
		}
	}		
	return false;
}




function OpenVPNCLientStart($id){
	$unix=new unix();
	$sock=new sockets();
	$main_path="/etc/artica-postfix/openvpn/clients";
	chdir("/root");
	$count=0;
	
	if(!is_numeric($id)){echo "Starting......: OpenVPN client $id is not numeric (".__LINE__.")\n";return;}
	if($id==0){echo "Starting......: OpenVPN client $id is not a valid integer (".__LINE__.")\n";return;}
	
	if(!is_file("$main_path/$id/settings.ovpn")){
		BuildOpenVpnSingleClient($id);
		if(!is_file("$main_path/$id/settings.ovpn")){
			echo "Starting......: OpenVPN client $id, unable to stat $main_path/$id/settings.ovpn (".__LINE__.")\n";
			return; 
		} 
	}
	
	$pid=vpn_client_pid($id);
	if($unix->process_exists($pid)){
		echo "Starting......: OpenVPN client $id, Already running PID $pid\n";
		return;
	}
	BuildOpenVpnSingleClient($id);
	$bridge=OpenVPNCLientIsOnTap($id);
	
	
	if(!$bridge){
		$tun=OpenVPNCLientStartGetDev($id);	
		if($tun<>null){
			if(!is_file("/dev/net/$tun")){
			echo "Starting......: OpenVPN client TUN $id,creating dev \"$tun\"\n";
			system($unix->find_program("mknod") ." /dev/net/$tun c 10 200 >/dev/null 2>&1");
			system($unix->find_program("chmod"). " 600 /dev/net/$tun >/dev/null 2>&1");
			}}
	}else{
		$tap=OpenVPNCLientStartGetTAPDev($id);
		echo "Starting......: OpenVPN client TAP $id,creating dev \"$tap\"\n";
		system("$openvpn --mktun --dev $tap");
	}
	
	if(is_file("$main_path/$id/auth-user-pass")){
		echo "Starting......: OpenVPN client [$id] authentication is enabled...\n";
		$EnableAuth=" --auth-user-pass $main_path/$id/auth-user-pass";
	}
	
	echo "Starting......: OpenVPN client [$id] log file will be $main_path/$id/openvpn-status.log\n";
	
	shell_exec("/bin/chmod -R 600 $main_path/$id");
	$cmd="openvpn --askpass $main_path/$id/keypassword$EnableAuth --config $main_path/$id/settings.ovpn --writepid $main_path/$id/pid --daemon --log $main_path/$id/log";
	$cmd=$cmd. " --status $main_path/$id/openvpn-status.log 10";
	if($GLOBALS["VERBOSE"]){echo "\n\n$cmd\n\n";}
	shell_exec($cmd);	
	$count=0;
	$pid=vpn_client_pid($id);
	for($i=0;$i<7;$i++){
		$count++;
		echo "Starting......: OpenVPN client [$id] (pid=$pid), waiting for pid $i/7\n";
		if($unix->process_exists($pid)){break;}
		if($count>5){echo "Starting......: OpenVPN client $id, time-out\n";break;}
		$pid=vpn_client_pid($id);
		if($pid==null){sleep(5);continue;}
		if($unix->process_exists($id)){break;}
		sleep(5);
	}
	
	
	$pid=vpn_client_pid($id);
	if(!$unix->process_exists($pid)){
		echo "Starting......: OpenVPN client $id, failed \"$cmd\"\n";
		iptables_delete_client_rules($id);
		return;
	}
	
	echo "Starting......: OpenVPN client $id, success running pid number $pid\n";
	if(!$bridge){
		$ethlink=trim(@file_get_contents("$main_path/$id/ethlink"));
		
		if(trim($ethlink)==null){
			$ethlink=OpenVpnClientGetDefaultethLink();
			echo "Starting......: OpenVPN client $id, no ethlink...create a default one for $ethlink\n";
			@file_put_contents("$main_path/$id/ethlink",$ethlink);
		}
		
		if($ethlink<>null){
			iptables_delete_client_rules($id);
			BuildIpTablesClient($ethlink,$id);
		}else{
			echo "Starting......: OpenVPN client $id, no ethlink...in $main_path/$id/ethlink\n";
		}
	}
	
	BuildClientRoute($id);
	
	
}


function BuildClientRoute($id){
	sleep(5);
	$unix=new unix();
	$main_path="/etc/artica-postfix/openvpn/clients";
	$ip_tool=$unix->find_program("ip");
	$bridge=OpenVPNCLientIsOnTap($id);
	if(!$bridge){
		$dev=OpenVPNCLientStartGetDev($id);
	}else{
		$dev=OpenVPNCLientStartGetTAPDev($id);		
	}
	
echo "Starting......: OpenVPN client $id, DEV:$dev\n";
exec("$ip_tool route",$results);
if($bridge){
	echo "Starting......: OpenVPN Tap $dev, cleaning bad route\n";
	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9\.]+)\/([0-9]+)\s+via\s+[0-9\.]+\s+dev\s+$dev#",$ligne,$re)){
			echo "Starting......: OpenVPN Tap {$re[0]} must be cleaned\n";
			system("$ip_tool route del {$re[0]}");
		}
	}
}

	echo "Starting......: OpenVPN dev:$dev, finding correct route\n";
	reset($results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9\.]+)\/([0-9]+)\s+dev\s+$dev\s+proto\s+kernel\s+scope\s+link\s+src\s+([0-9\.]+)#",$ligne,$re)){
			$IP_TO_ROUTE=$re[3];
			echo "Starting......: OpenVPN others routes match $dev $IP_TO_ROUTE\n";
			break;
		}
	
	}

	$routes=OpenVpnClientGetRoutes("$main_path/$id");
	$localnets=getLocalNets();
	//print_r($routes);
	//print_r($localnets);
	if(count($routes)==0){echo "Starting......: OpenVPN no routes to add\n";return;}
	while (list ($ip_start, $netmask) = each ($routes) ){
		if($localnets[$ip_start]<>null){
			echo "Starting......: OpenVPN skipping route $ip_start\n";
			continue;
		}
		
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ip_start)){
			echo "Starting......: OpenVPN skipping route $ip_start/$netmask\n";
			continue;
		}
		
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$netmask)){
			echo "Starting......: OpenVPN skipping route $ip_start/$netmask\n";
			continue;
		}		
		
		echo "Starting......: OpenVPN adding route $ip_start/$netmask\n";
		$cmd="$ip_tool route add $ip_start/$netmask dev $dev proto kernel scope link src $IP_TO_ROUTE >/dev/null 2>&1";
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." $cmd\n";}
		shell_exec2($cmd);
	}
	
	
}

function getLocalNets(){
	$unix=new unix();
	$main_path="/etc/artica-postfix/openvpn/clients";
	$ip_tool=$unix->find_program("ip");

	exec("$ip_tool addr show 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^[0-9]+:\s+(.+?):#",$ligne,$re)){
			$eth=$re[1];
			if(preg_match("#(.+?)\.[0-9]+$#",GetIpaddrOf($eth),$ri)){
				$ipof="{$ri[1]}.0";
			}
			$array[$ipof]=$eth;
		}else{
		
		}
	}
	return $array;
}



function OpenVpnClientGetDefaultethLink(){
$nic=new networking();

while (list ($num, $ligne) = each ($nic->array_TCP) ){
	if($ligne==null){continue;}
		if(!preg_match("#^eth[0-9]+#",$num)){continue;}
		$eths[]=$num;
		$ethi[$num]=$ligne;
	} 
	

	if(is_array($eths)){return $eths[0];}
	
}

function OpenVpnClientGetRoutes($mainpath){
	$datas=file_get_contents("$mainpath/settings.ovpn");
	$f=explode("\n",$datas);
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." open $mainpath/settings.ovpn ". count($f)." lines\n";}
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#REMOTE-SITE:\s+([0-9\.]+);([0-9\.]+)#",$ligne,$re)){
			$routes[$re[1]]=$re[2];
		}
	}
	
	$datas=file_get_contents("$mainpath/routes_additionnal");
	$f=explode("\n",$datas);
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__." open $mainpath/settings.ovpn ". count($f)." lines\n";}
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#([0-9\.]+)\s+([0-9\.]+)#",$ligne,$re)){$routes[$re[1]]=$re[2];}
			
	}
	if(isset($routes)){return $routes;}
}

function BuildOpenVpnClients_changeConfig($mainpath,$ethid){
	echo "Starting......: OpenVPN client $mainpath/settings.ovpn\n";
	
	
	$datas=file_get_contents("$mainpath/settings.ovpn");
	$f=explode("\n",$datas);
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^dev\s+tun(.*)#",$ligne,$re)){
			echo "Starting......: OpenVPN client tun dev=". trim($re[1])." change to tun$ethid\n";
			$f[$num]="dev tun$ethid";
		}
		
		if(preg_match("#^dev\s+tap(.*)#",$ligne,$re)){
			echo "Starting......: OpenVPN client tap dev=". trim($re[1])." change to tap$ethid\n";
			$f[$num]="dev tap$ethid";
		}		
		
		if(preg_match("#^ca\s+#",$ligne)){
			$f[$num]="ca $mainpath/ca.crt";
		}
		
		if(preg_match("#^cert\s+#",$ligne)){
			$f[$num]="cert $mainpath/certificate.crt";
		}

		if(preg_match("#^key\s+#",$ligne)){
			$f[$num]="key $mainpath/master-key.key";
		}			
		
		
	}
	
	@file_put_contents("$mainpath/settings.ovpn",implode("\n",$f));
	
	
}

function ServerScheduledTTL(){
	$sock=new sockets();
	$EnableOpenVPNServerSchedule=$sock->GET_INFO("EnableOpenVPNServerSchedule");
	$EnableOPenVPNServerMode=$sock->GET_INFO("EnableOPenVPNServerMode");
	if($EnableOpenVPNServerSchedule==null){$EnableOpenVPNServerSchedule=0;}
	if($EnableOPenVPNServerMode==null){$EnableOPenVPNServerMode=0;}
	if($EnableOPenVPNServerMode==0){return;}
	
	if($EnableOpenVPNServerSchedule==0){
		ServerScheduledTTLDelete();
		return;
	}
	
	
	$params=unserialize(base64_decode($sock->GET_INFO("EnableOpenVPNServerScheduleDatas")));
	
	$time_start=date('Y-m-d ')." {$params["hour_begin"]}:{$params["min_begin"]}:00";
	$time_end=date('Y-m-d ')." {$params["hour_end"]}:{$params["min_end"]}:00";
	
	$timecode_start=strtotime($time_start);
	$timecode_end=strtotime($time_end);
	$now=time();
	
	if($GLOBALS["VERBOSE"]){writelogs("Start in $timecode_start end at $timecode_end, now is $now",__FUNCTION__,__FILE__,__LINE__);}
	
	if($now>$timecode_end){$ban=true;}
	
	$rule_number=ServerScheduledTTLLineNumber();
	if($rule_number>0){ServerScheduledTTLDelete();}	
	
	if($ban){
   			$ini=new Bs_IniHandler();
 			$ini->loadString($sock->GET_INFO("ArticaOpenVPNSettings"));
    		$port=$ini->_params["GLOBAL"]["LISTEN_PORT"];	
			$proto=$ini->_params["GLOBAL"]["LISTEN_PROTO"];
			if($GLOBALS["VERBOSE"]){writelogs("$now>$timecode_end create the iptable rule",__FUNCTION__,__FILE__,__LINE__);}
			ServerScheduledTTLBan($port,$proto);
	}
    
    
}

function ServerScheduledTTLBan($port,$proto){
	if($port==null){$port=1194;}
	if($proto==null){$proto="udp";}
	$unix=new unix();
	$iptables=$unix->find_program("iptables");
	$cmd="$iptables -A INPUT -p $proto --destination-port $port -j DROP -m comment --comment \"ArticaVPNServerScheduled\"";
	shell_exec($cmd);
}

function ServerScheduledTTLLineNumber(){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");

	exec("$iptables -L -n --line-numbers",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#^([0-9]+).+?ArticaVPNServerScheduled#",$ligne,$re)){
				return $re[1];
			}
		}
		
	return 0;
	
}
function ServerScheduledTTLDelete(){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");

	exec("$iptables -L -n --line-numbers",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#^([0-9]+).+?ArticaVPNServerScheduled#",$ligne,$re)){
				if($GLOBALS["VERBOSE"]){writelogs("Delete the iptable rule:{$re[1]}",__FUNCTION__,__FILE__,__LINE__);}
				shell_exec("$iptables -D INPUT {$re[1]}");
				ServerScheduledTTLDelete();
				return;
			}
		}
		
	
	
}

function windows_client($uid){
	$vpn=new openvpn();
	$config=$vpn->BuildClientconf($uid);
	@file_put_contents("/etc/artica-postfix/settings/Daemons/$uid.ovpn",$config);	
	$sock=new sockets();
	@mkdir("/tmp/$uid",0666,true);
	if($GLOBALS["VERBOSE"]){$verbose="&verbose=yes";}
	if($GLOBALS["VERBOSE"]){echo "openvpn.php?build-vpn-user=$uid&basepath=/tmp/$uid$verbose\n";}
	echo $sock->getFrameWork("openvpn.php?build-vpn-user=$uid&basepath=/tmp/$uid$verbose");
	@unlink("/etc/artica-postfix/settings/Daemons/$uid.ovpn");
}

function client_connect($array){
	
	$action=$array[2];
	$ip=$array[3];
	$tbl=explode("\n", "/var/log/openvpn/openvpn-status.log");
while (list ($num, $ligne) = each ($tbl) ){
	if(preg_match("#([0-9\.]+),(.+?),([0-9\.\:]+),(.+)#", $ligne,$re)){$array[$re[2]]["LOCAL_IP"]=$re[1];}
	if(preg_match('#(.+?),([0-9\.\:]+),([0-9]+),([0-9]+),(.+)#',$ligne,$re)){
		if(preg_match("#(.+?):#", $re[2],$ri)){$re[2]=$ri[1];}
		$array[$re[1]]["REMOTE_IP"]=$re[2];
		$array[$re[1]]["b_received"]=$re[3];
		$array[$re[1]]["b_sent"]=$re[4];
		$array[$re[1]]["time"]=$re[5];
		
		
	}

}

while (list ($uid, $ligne) = each ($array) ){
		if($uid==null){continue;}
		if($ligne["REMOTE_IP"]==null){continue;}	
		$f[]="$uid\t{$ligne["REMOTE_IP"]}/{$ligne["LOCAL_IP"]}";
	
}

$unix=new unix();
$unix->send_email_events("OpenVPN server status $action $ip", "Last status\n".@implode("\n", $f), "system");
	
	
}

function wakeup_server_mode(){
	$unix=new unix();
	$pidpath="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pidtime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$oldpid=@file_get_contents($pidpath);
	if($unix->process_exists($oldpid)){writelogs("OpenVPN Already instance executed pid $oldpid",__FUNCTION__,__FILE__,__LINE__);return;}
	@file_put_contents($pidpath, posix_getpid());
	$time=$unix->file_time_min($pidtime);
	
	if($time<2){return;}
	writelogs("OpenVPN $pidtime -> {$time}Mn",__FUNCTION__,__FILE__,__LINE__);
	@unlink($pidtime);
	@file_put_contents($pidtime, time());
	
	
	$sql="SELECT ID,sitename,wakeupip,wakeupok FROM vpnclient WHERE connexion_type=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("Fatal,$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#Unknown column", $q->mysql_error)){$q->BuildTables();}
		return;
		}
	$ping=$unix->find_program("ping");
	if(!is_file($ping)){writelogs("Fatal,ping, no such binary",__FUNCTION__,__FILE__,__LINE__);return;}
	
	$tcp=new ip();
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(!$tcp->isValid($ligne["wakeupip"])){
			writelogs("OpenVPN {$ligne["sitename"]} \"{$ligne["wakeupip"]}\" is not a valid ip address",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		$wakeupok=$ligne["wakeupok"];
		$resultsPing=array();
		exec("$ping -c2 -i0.2 {$ligne["wakeupip"]} 2>&1",$resultsPing);
		writelogs("OpenVPN {$ligne["sitename"]} \"{$ligne["wakeupip"]}\" ".count($resultsPing)." rows",__FUNCTION__,__FILE__,__LINE__);
		$text=date("Y-m-d H:i:s")."\n".@implode("\n", $resultsPing);
		$note=0;
		while (list ($nimber, $l) = each ($resultsPing) ){
			if(preg_match("#,\s+([0-9]+)\%\s+#", $l,$re)){
				writelogs("OpenVPN {$ligne["sitename"]} \"{$re[1]}%\" Packets lost",__FUNCTION__,__FILE__,__LINE__);
				if($re[1]==100){
					$note=-1;
					writelogs("OpenVPN {$ligne["sitename"]} Ping failed",__FUNCTION__,__FILE__,__LINE__);
					$unix->send_email_events("[VPN]: {$ligne["sitename"]} wake up failed {$ligne["wakeupip"]}" , "It seems that this remote site did not respond\n$text", "vpn");
				}else{
					$note=1;
					if($wakeupok<>1){$unix->send_email_events("[VPN]: {$ligne["sitename"]} wake up success {$ligne["wakeupip"]}" , "It seems that the connection to this remote site is established\n$text", "vpn");}
				}
			break;}
		}
		
		$sql="UPDATE vpnclient SET wakeup_results='$text' ,wakeupok='$note' WHERE ID='{$ligne["ID"]}'";
		$q->QUERY_SQL($sql,"artica_backup");
	}
}

function wakeup_client_mode(){
	$main_path="/etc/artica-postfix/openvpn/clients";
	$unix=new unix();
	$pidpath="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidpath);
	if($unix->process_exists($oldpid)){
		writelogs("OpenVPN Already instance executed pid $oldpid",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	@file_put_contents($pidpath, posix_getpid());
	$ping=$unix->find_program("ping");
	if(!is_file($ping)){writelogs("Fatal,ping, no such binary",__FUNCTION__,__FILE__,__LINE__);return;}	
	$tbl=$unix->dirdir($main_path);
	if(count($tbl)==0){return;}
	while (list ($path, $id) = each ($tbl) ){
			if(!preg_match("#/etc/artica-postfix/openvpn/clients/([0-9]+)#",$path,$re)){if($GLOBALS["VERBOSE"]){echo "Starting......: $path NO MATCH\n";}continue;}
			$id=$re[1];
			if($GLOBALS["VERBOSE"]){echo "Starting......: OpenVPN wake up checking client ID:$id\n";}
			if(!is_file("$path/settings.ovpn")){if($GLOBALS["VERBOSE"]){echo "Starting......: $path/settings.ovpn no such file\n";}continue;}
			$ip=wakeup_client_mode_getWakeup("$path/settings.ovpn");
			if($ip==null){continue;}
			
			$resultsPing=array();
			exec("$ping -c2 -i0.2 $ip 2>&1",$resultsPing);
			writelogs("OpenVPN  \"$ip\" ".count($resultsPing)." rows",__FUNCTION__,__FILE__,__LINE__);
			$text=date("Y-m-d H:i:s")."\n".@implode("\n", $resultsPing);	
			$ping_results=@file_get_contents("$path/ping_results");
			while (list ($nimber, $l) = each ($resultsPing) ){
				if(preg_match("#,\s+([0-9]+)\%\s+#", $l,$re)){
					writelogs("OpenVPN  \"{$re[1]}%\" Packets lost",__FUNCTION__,__FILE__,__LINE__);
					if($re[1]==100){
						$note=-1;
						writelogs("OpenVPN $ip Ping failed",__FUNCTION__,__FILE__,__LINE__);
						$unix->send_email_events("[VPN]: wake up failed server link $ip" , "It seems that OpenVPN server did not respond\n$text", "vpn");
						
					}else{
						$note=1;
						if($ping_results<>1){$unix->send_email_events("[VPN]: wake up server success $ip" , "It seems that the connection to the server has been established\n$text", "vpn");}
					}
					@file_put_contents("$path/ping_results", $note);
					
					}
			}
	}

}

function wakeup_client_mode_getWakeup($path){
	$f=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($f) ){
		if(preg_match('#wakeup:"([0-9\.]+)#', $line,$re)){
			$ip=$re[1];
			$tcp=new ip();
			if(!$tcp->isValid($ip)){return null;}else{return $ip;}
		}
		
	}
	if($GLOBALS["VERBOSE"]){echo "Starting......: $path no wakeup ip addr\n";}
}




?>