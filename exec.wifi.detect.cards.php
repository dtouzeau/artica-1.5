<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.sockets.inc");


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

if($argv[1]=="--detect"){detectCards();die();}
if($argv[1]=="--iwlist"){iwlist();die();}
if($argv[1]=="--ap"){ConnectToAccessPoint();die();}
if($argv[1]=="--checkap"){CheckConnection();die();}



function detectCards(){
	$unix=new unix();
	$detect=false;
	$sock=new sockets();
	$lspci=$unix->find_program("lspci");
	$iwlist=$unix->find_program("iwlist");
	if($iwlist==null){
		if($GLOBALS["VERBOSE"]){echo "Unable to stat iwlist\n";}
		$sock->SET_INFO("WifiCardOk",0);
		exit;
	}
	
	exec("$lspci -mm",$results);
	while (list ($num, $ligne) = each ($results) ){
			if(preg_match('#[0-9\:\.]+\s+".+?"\s+".+?"\s+"(.+?)"#',$ligne,$re)){				
				if(SupportedCards($re[1])){$detect=true;}
			}
	}
	
	
	if($detect){
		echo "Starting......: WIFI Network Card detected\n";
		$sock=new sockets();
		$sock->SET_INFO("WifiCardOk",1);
		exit;
	}
	$sock->SET_INFO("WifiCardOk",0);
}



function SupportedCards($pattern){
	
	$array[]="PRO/Wireless 4965 AG or AGN [Kedron] Network Connection";
	$array[]="BCM4312 802.11b/g";
	$array[]="RTL8187SE Wireless LAN Controller";
	$array[]="DWL-520+ 22Mbps PCI Wireless Adapter";
	$array[]="AirPlus G DWL-G510 Wireless Network Adapter (Rev.C)";
	$array[]="RT2561/RT61 rev B 802.11g";
	
	while (list ($num, $ligne) = each ($array) ){
		if(strtolower($ligne)==trim(strtolower($pattern))){
			echo "Starting......: WIFI $pattern\n";
			return true;
		
		}
	}
	
	if($GLOBALS["VERBOSE"]){
		echo "Starting......: WIFI \"$pattern\" (NOT supported)\n";
	}
}

function iwlist(){
	$unix=new unix();
	$eth=$unix->GET_WIRELESS_CARD();
	if($eth==null){ 
		if($GLOBALS["VERBOSE"]){echo "Unable to get nic name...\n";}
		return;}
	$iwlist=$unix->find_program("iwlist");
	$iwgetid=$unix->find_program("iwgetid");
	$ifconfig=$unix->find_program("ifconfig");
	shell_exec("$ifconfig $eth up");
	exec("$iwgetid $eth -s -r",$ares);
	$SELECTED_POINT=trim(@implode("",$ares));
	
	
	
	exec("$iwlist $eth scan",$results);
	while (list ($num, $ligne) = each ($results) ){
		
		if(preg_match("#Network is down#",$ligne)){
			
		}
		
		if(preg_match("#Cell\s+([0-9]+).+?Address:\s+(.+)#",$ligne,$re)){
			$mac=$re[2];
			$index=$re[1];
			$array[$index]["MAC"]=$mac;
			continue;
		}
		
		if(preg_match("#Quality[=:]([0-9\.]+)\/([0-9\.]+)#",$ligne,$re)){
			$purc=$re[1]/$re[2];
			$purc=$purc*100;
			$array[$index]["QUALITY"]=round($purc,1);
			continue;
		}
		if(preg_match("#ESSID:[='\"](.*?)['\"]#",$ligne,$re)){
			if($SELECTED_POINT==trim($re[1])){
				$array[$index]["ESSID_SELECTED"]=true;
			}
			if(trim($re[1])==null){$re[1]=$array[$index]["MAC"];}
			$array[$index]["ESSID"]=trim($re[1]);
			continue;
		}
		
		if(preg_match("#Encryption key:(.+)#",$ligne,$re)){
			$re[1]=strtolower(trim($re[1]));
			$array[$index]["KEY"]=false;
			if($re[1]=="on"){$array[$index]["KEY"]=true;}
			continue;
		}
		if(preg_match("#Bit Rates:(.+)#",$ligne,$re)){
			 $array[$index]["RATES"]=$array[$index]["RATES"].";".$re[1];
			 continue;
		}
		
		
		 
		
	}
	if($GLOBALS["VERBOSE"]){print_r($array);}
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/iwlist.scan",@serialize($array));
	@chmod("/usr/share/artica-postfix/ressources/logs/iwlist.scan",0775);
}

function ConnectToAccessPoint(){
	$sock=new sockets();
	$unix=new unix();
	$WifiAPEnable=$sock->GET_INFO("WifiAPEnable");
	if($WifiAPEnable<>1){return null;}
	
	
	wpa_removenetworks();
	$array=unserialize(base64_decode($sock->GET_INFO("WifiAccessPoint")));
	if(is_array($array)){
		while (list ($ssid, $array2) = each ($array) ){
			if($array2["ENABLED"]==1){
				$CONFIG=$array2;
				$ESSID=$ssid;
				break;	
			}
		}
	}	
	if($ESSID==null){return;}
	echo "Starting......: WIFI Access Point: \"$ESSID\"\n";
	$password=$CONFIG["ESSID_PASSWORD"];
	$eth=$unix->GET_WIRELESS_CARD();
	$wpa_cli=$unix->find_program("wpa_cli");
	$ifconfig=$unix->find_program("ifconfig");
	$dhclient=$unix->find_program("dhclient");
	$route=$unix->find_program("route");
	$nohup=$unix->find_program("nohup");
	
	if($GLOBALS["VERBOSE"]){
		echo "using NIC:$eth\n";
		echo "using wpa_cli:$wpa_cli\n";
		echo "using ifconfig:$ifconfig\n";
		echo "using dhclient:$dhclient\n";
		echo "using route:$route\n";
		echo "using nohup:$nohup\n";
		
	}
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} remove_network 0",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="add_network: $echo";}
	unset($results);	
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} add_network 0",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="add_network: $echo";}
	unset($results);
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 ssid \\\"\"$ESSID\"\\\"",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="ssid: $echo";}
	unset($results);
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 key_mgmt WPA-PSK",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="key_mgmt: $echo";}
	unset($results);
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 pairwise TKIP",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="pairwise: $echo";}
	unset($results);
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 group TKIP",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="group: $echo";}
	unset($results);
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 proto WPA",$results);	
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="WPA: $echo";}
	unset($results);
	
	if($GLOBALS["VERBOSE"]){echo "$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 psk \\\"\"$password\"\\\""."\n";}
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} set_network 0 psk \\\"\"$password\"\\\"",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="psk (password): $echo";}	
	unset($results);
	
	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} enable_network 0",$results);	
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="enable_network: $echo";}
	unset($results);	
	
	exec("$wpa_cli -p/var/run/wpa_supplicant save_config",$results);	
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="save_config: $echo";}
	unset($results);

	exec("$wpa_cli -p/var/run/wpa_supplicant -i{$eth} reconnect",$results);	
	$echo=implode("",$results);
	if(trim($echo)<>null){$r[]="reconnect: $echo";}
	unset($results);
	while (list ($index, $a) = each ($r) ){
		echo "Starting......: WIFI Access Point: $a\n";
	}
	
	system("$ifconfig $eth up");
	
	if($CONFIG["UseDhcp"]==1){
		echo "Starting......: WIFI Access Point using DHCP\n";
		system("$nohup $dhclient $eth >/dev/null 2>&1 &");
		return;
	}
	
	if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$CONFIG["ip_address"],$re)){
		echo "Starting......: WIFI Access Point bad IP address format\n";
		return;
	}	
	$sub="{$re[1]}.{$re[2]}.{$re[3]}.0";
	
	system("$ifconfig $eth {$CONFIG["ip_address"]} netmask {$CONFIG["mask"]}");
	system("$route add default gw {$CONFIG["gateway"]} $eth");
}

function wpa_removenetworks(){
	$unix=new unix();
	$wpa_cli=$unix->find_program("wpa_cli");
	$cmd="$wpa_cli -p/var/run/wpa_supplicant list_networks";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	exec($cmd,$results);
	if($GLOBALS["VERBOSE"]){echo count($results)." lines\n";}
	while (list ($index, $line) = each ($results) ){
		if($GLOBALS["VERBOSE"]){echo "$line\n";}
		if(preg_match("#^([0-9]+)\s+#",$line,$re)){
			echo "Starting......: WIFI Access Point remove {$re[1]} Access Point\n";
			shell_exec("$wpa_cli -p/var/run/wpa_supplicant remove_network {$re[1]}");
		}
	}
	unset($results);
	exec("$wpa_cli -p/var/run/wpa_supplicant save_config",$results);
	$echo=implode("",$results);
	if(trim($echo)<>null){if($GLOBALS["VERBOSE"]){echo "$echo\n";}}
}

function CheckConnection(){
	$sock=new sockets();
	$unix=new unix();
	$ifconfig=$unix->find_program("ifconfig");
	$array=unserialize(base64_decode($sock->GET_INFO("WifiAccessPoint")));
	$WifiAPEnable=$sock->GET_INFO("WifiAPEnable");
	$eth=trim($unix->GET_WIRELESS_CARD());
	if(!is_array($array)){return false;}	
	if($eth==null){return false;}	
	if($WifiAPEnable<>1){return false;}
	$wpa_cli=$unix->find_program("wpa_cli");
	$ifconfig=$unix->find_program("ifconfig");
	
	exec("$wpa_cli -p/var/run/wpa_supplicant status -i{$eth}",$results);
	$conf="[IF]\n".implode("\n",$results);
	
	
	$ini=new Bs_IniHandler();
	$ini->loadString($conf);
	$ip=$ini->_params["IF"]["ip_address"];
	$status=$ini->_params["IF"]["wpa_state"];
	writelogs_framework("$eth: state= $status",__FUNCTION__,__FILE__,__LINE__);
	switch ($status) {
		
		case "SCANNING":
			writelogs("$eth: up interface...",__FUNCTION__,__FILE__,__LINE__);
			shell_exec("$ifconfig $eth up");break;
		
		case "COMPLETED":return;break;
		
		case "INACTIVE":
			writelogs_framework("$eth: -> ConnectToAccessPoint()",__FUNCTION__,__FILE__,__LINE__);
			ConnectToAccessPoint();
			return;break;
		default:ConnectToAccessPoint();
			writelogs_framework("$eth: -> ConnectToAccessPoint()",__FUNCTION__,__FILE__,__LINE__);
			return;
			break;
		
	}	
}
	
	



?>