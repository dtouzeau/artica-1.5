<?php
include_once(dirname(__FILE__).'/class.ssl.certificate.inc');
include_once(dirname(__FILE__) . '/class.system.network.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');

class openvpn{
	
	var $main_array=array();
	var $array_country_codes=array();
	var $routes=array();
	var $events=array();
	var $site_id=0;
	var $ComputerOS;
	
	function openvpn(){
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_INFO('ArticaOpenVPNSettings'));
		$this->main_array=$ini->_params;
		$ssl=new ssl_certificate();
		$this->array_country_codes=$ssl->array_country_codes;
		$this->BuildDefault();
		$this->LoadRoutes();
		}
		
		
	function virtual_ip_lists(){
			$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
				if($ligne["org"]=="openvpn_service"){
					$eth="{$ligne["nic"]}:{$ligne["ID"]}";
					$array[$eth]=$eth;
				}
			}
			$array[null]="{select}";
			return  $array;
	}
	
	function virtual_ip_information(){
		$eth=$this->main_array["GLOBAL"]["BRIDGE_ETH"];
		if(!preg_match("#(.+?):([0-9]+)#",$eth,$re)){return array();}
		$sql="SELECT * FROM nics_virtuals WHERE ID={$re[2]}";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$array_ip["IPADDR"]=$ligne["ipaddr"];
		$array_ip["NETMASK"]=$ligne["netmask"];
		$array_ip["GATEWAY"]=$ligne["gateway"];
		
		return $array_ip;
		
	}
	
	
	function BuildDefault(){
		
		
		if(!isset($this->main_array["GLOBAL"]["ENABLE_BRIDGE"])){$this->main_array["GLOBAL"]["ENABLE_BRIDGE"]=null;}
		if(!isset($this->main_array["GLOBAL"]["KEY_COUNTRY"])){$this->main_array["GLOBAL"]["KEY_COUNTRY"]=null;}
		if(!isset($this->main_array["GLOBAL"]["KEY_PROVINCE"])){$this->main_array["GLOBAL"]["KEY_PROVINCE"]=null;}
		if(!isset($this->main_array["GLOBAL"]["KEY_CITY"])){$this->main_array["GLOBAL"]["KEY_CITY"]=null;}
		if(!isset($this->main_array["GLOBAL"]["KEY_ORG"])){$this->main_array["GLOBAL"]["KEY_ORG"]=null;}
		if(!isset($this->main_array["GLOBAL"]["KEY_EMAIL"])){$this->main_array["GLOBAL"]["KEY_EMAIL"]=null;}
		if(!isset($this->main_array["GLOBAL"]["LISTEN_PORT"])){$this->main_array["GLOBAL"]["LISTEN_PORT"]=null;}
		if(!isset($this->main_array["GLOBAL"]["LISTEN_PROTO"])){$this->main_array["GLOBAL"]["LISTEN_PROTO"]=null;}
		if(!isset($this->main_array["GLOBAL"]["DEV_TYPE"])){$this->main_array["GLOBAL"]["DEV_TYPE"]=null;}
		if(!isset($this->main_array["GLOBAL"]["LDAP_AUTH"])){$this->main_array["GLOBAL"]["LDAP_AUTH"]=null;}
		if(!isset($this->main_array["GLOBAL"]["BRIDGE_ETH"])){$this->main_array["GLOBAL"]["BRIDGE_ETH"]=null;}
		if(!isset($this->main_array["GLOBAL"]["KEY_COUNTRY_NAME"])){$this->main_array["GLOBAL"]["KEY_COUNTRY_NAME"]=null;}
		if(!isset($this->main_array["GLOBAL"]["WAKEUP_IP"])){$this->main_array["GLOBAL"]["WAKEUP_IP"]=null;}
		
		
		
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$this->main_array["GLOBAL"]["ENABLE_SERVER"]=$sock->GET_INFO('EnableOPenVPNServerMode');
		if($GLOBALS["VERBOSE"]){writelogs("EnableOPenVPNServerMode: {$this->main_array["GLOBAL"]["ENABLE_SERVER"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);}
		if($this->main_array["GLOBAL"]["KEY_COUNTRY"]==null){$this->main_array["GLOBAL"]["KEY_COUNTRY"]="US";}
		if($this->main_array["GLOBAL"]["KEY_COUNTRY_NAME"]==null){$this->main_array["GLOBAL"]["KEY_COUNTRY_NAME"]="UNITED STATES_US";}
		if($this->main_array["GLOBAL"]["KEY_PROVINCE"]==null){$this->main_array["GLOBAL"]["KEY_PROVINCE"]="New York";}
		if($this->main_array["GLOBAL"]["KEY_CITY"]==null){$this->main_array["GLOBAL"]["KEY_CITY"]="New York";}
		if($this->main_array["GLOBAL"]["KEY_ORG"]==null){$this->main_array["GLOBAL"]["KEY_ORG"]="Organization";}
		if($this->main_array["GLOBAL"]["KEY_EMAIL"]==null){$this->main_array["GLOBAL"]["KEY_EMAIL"]="yourmail@localhost.localdomain";}
		if($this->main_array["GLOBAL"]["LISTEN_PORT"]==null){$this->main_array["GLOBAL"]["LISTEN_PORT"]="1194";}
		if($this->main_array["GLOBAL"]["ENABLE_SERVER"]==null){$this->main_array["GLOBAL"]["ENABLE_SERVER"]="0";}
		if($this->main_array["GLOBAL"]["BRIDGE_ETH"]==null){$this->main_array["GLOBAL"]["BRIDGE_ETH"]="eth0:1";}
		if($this->main_array["GLOBAL"]["LISTEN_PROTO"]==null){$this->main_array["GLOBAL"]["LISTEN_PROTO"]="udp";}
		if($this->main_array["GLOBAL"]["DEV_TYPE"]==null){$this->main_array["GLOBAL"]["DEV_TYPE"]="tun";} //or tap0
		if($this->main_array["GLOBAL"]["ENABLE_BRIDGE"]==null){$this->main_array["GLOBAL"]["ENABLE_BRIDGE"]="0";}
		if($this->main_array["GLOBAL"]["LDAP_AUTH"]==null){$this->main_array["GLOBAL"]["LDAP_AUTH"]="0";}
		if($this->main_array["GLOBAL"]["REMOVE_SERVER_DEFAULT_ROUTE"]==null){$this->main_array["GLOBAL"]["REMOVE_SERVER_DEFAULT_ROUTE"]="0";}
		
		
		
		
		
		
		}
		
	function LoadRoutes(){
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$datas=$sock->GET_INFO('OpenVPNRoutes');
		$tbl=explode("\n",$datas);
		
		while (list ($num, $ligne) = each ($tbl) ){
			if(!preg_match('#([0-9\.]+)\s+([0-9\.]+)#',$ligne,$re)){continue;}
			$this->routes[$re[1]]=$re[2];	
		}
		
	}
		
	function Save(){
		writelogs("EnableOPenVPNServerMode: {$this->main_array["GLOBAL"]["ENABLE_SERVER"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ini=new Bs_IniHandler();
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		
		if(is_array($this->routes)){
		reset($this->routes);
					while (list ($num, $ligne) = each ($this->routes) ){
						if(trim($ligne)==null){continue;}
						$routes=$routes. "$num\t$ligne\n";
					}
				}		
		
		if(preg_match('#.+?_([A-Z0-9]+)$#',$this->main_array["GLOBAL"]["KEY_COUNTRY_NAME"],$re)){$this->main_array["GLOBAL"]["KEY_COUNTRY"]=$re[1];}
		$ini->_params=$this->main_array;
		$sock->SET_INFO("EnableOPenVPNServerMode",$this->main_array["GLOBAL"]["ENABLE_SERVER"]);
		if($this->main_array["GLOBAL"]["ENABLE_SERVER"]==1){$sock->SET_INFO('EnableArticaAsGateway',1);}
		$sock->SaveConfigFile($this->BuildServerConfig(),'ArticaOpenVPNServerSettings');
		$sock->SaveConfigFile($routes,'OpenVPNRoutes');
		$sock->SaveConfigFile($ini->toString(),'ArticaOpenVPNSettings');
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?restart-openvpn-server");
		}
	
	function BuildCertificate(){
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$sock->getfile('OpenVPNBuildCertificate');
		
		
	}
	
	function BuildServerConfig(){
			$users=new usersMenus();
			$servername=strtoupper($users->hostname);
				$conf=$conf . ";local a.b.c.d\n";
				if($this->main_array["GLOBAL"]["LOCAL_BIND"]<>null){
					$conf=$conf . "local {$this->main_array["GLOBAL"]["LOCAL_BIND"]}\n";
				}
				

				
				$conf=$conf . "port {$this->main_array["GLOBAL"]["CLIENT_NAT_PORT"]}\n";
				$conf=$conf . "proto {$this->main_array["GLOBAL"]["LISTEN_PORT"]}\n";
				$conf=$conf . "mode server\n";
				$conf=$conf . "dev {$this->main_array["GLOBAL"]["DEV_TYPE"]}\n";
				$conf=$conf . "ca /etc/artica-postfix/openvpn/keys/ca.crt\n";
				$conf=$conf . "cert /etc/artica-postfix/openvpn/keys/$servername.crt\n";
				$conf=$conf . "key /etc/artica-postfix/openvpn/keys/$servername.key  # This file should be kept secret\n";
				$conf=$conf . "dh /etc/artica-postfix/openvpn/keys/dh1024.pem\n";
				
				if($this->main_array["GLOBAL"]["DEV_TYPE"]='tun'){
					$conf=$conf . "server {$this->main_array["GLOBAL"]["IP_START"]} {$this->main_array["GLOBAL"]["NETMASK"]}\n";
				}
				else{
				
				$conf=$conf . "ifconfig {$this->main_array["GLOBAL"]["VPN_SERVER_IP"]} {$this->main_array["GLOBAL"]["VPN_SERVER_DHCP_MASK"]}\n";
				$conf=$conf . "server-bridge {$this->main_array["GLOBAL"]["VPN_SERVER_IP"]} {$this->main_array["GLOBAL"]["VPN_SERVER_DHCP_MASK"]} {$this->main_array["GLOBAL"]["VPN_DHCP_FROM"]} {$this->main_array["GLOBAL"]["VPN_DHCP_TO"]}\n";
				}
				
				$conf=$conf . "ifconfig-pool-persist ipp.txt\n";
				
				
				if(is_array($this->routes)){
					reset($this->routes);
					while (list ($num, $ligne) = each ($this->routes) ){
						$conf=$conf . "push \"route $num $ligne\"\n";
					}
				}
				if(trim($this->main_array["GLOBAL"]["VPN_DNS_DHCP_1"])<>null){
				$conf=$conf . "push \"dhcp-option DNS {$this->main_array["GLOBAL"]["VPN_DNS_DHCP_1"]}\"\n";}
				
				if(trim($this->main_array["GLOBAL"]["VPN_DNS_DHCP_2"])<>null){
				$conf=$conf . "push \"dhcp-option DNS {$this->main_array["GLOBAL"]["VPN_DNS_DHCP_2"]}\"\n";}				
				
				$conf=$conf . ";learn-address ./script\n";
				
				$conf=$conf . "client-to-client\n";
				$conf=$conf . ";duplicate-cn\n";;
				$conf=$conf . "keepalive 10 120\n";
				$conf=$conf . "comp-lzo\n";
				$conf=$conf . ";max-clients 100\n";
				$conf=$conf . ";user nobody\n";
				$conf=$conf . ";group nogroup\n";
				$conf=$conf . "persist-key\n";
				$conf=$conf . "persist-tun\n";
				$conf=$conf . "\n";
				$conf=$conf . "status /var/log/openvpn/openvpn-status.log\n";
				$conf=$conf . "log-append /var/log/openvpn/openvpn.log\n";
				$conf=$conf . "\n";
				$conf=$conf . ";log         openvpn.log\n";
				$conf=$conf . ";log-append  openvpn.log\n";
				$conf=$conf . "#\n";
				$conf=$conf . "# 9 is extremely verbose\n";
				$conf=$conf . "verb 4\n";
				$conf=$conf . ";mute 20\n";	
				return $conf;	
		
	}
	
	function BuildClientconf($uid){
				$script_secu=null;
				$SITE_IP=null;
				if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
				$ini=new Bs_IniHandler();
				$datas=$sock->GET_INFO("ArticaOpenVPNSettings");
				
				writelogs("ArticaOpenVPNSettings= ". strlen($datas)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$ini->loadString($sock->GET_INFO("ArticaOpenVPNSettings",true));
				
				
				
		
				$conf=$conf ."# ##############################################\r\n";
				$conf=$conf ."# Sample client-side OpenVPN 2.0 config file #\r\n";
				$conf=$conf ."# for connecting to multi-client server.     #\r\n";
				$conf=$conf ."#                                            #\r\n";
				$conf=$conf ."# This configuration can be used by multiple #\r\n";
				$conf=$conf ."# clients, however each client should have   #\r\n";
				$conf=$conf ."# its own cert and key files.                #\r\n";
				$conf=$conf ."#                                            #\r\n";
				$conf=$conf ."# On Windows, you might want to rename this  #\r\n";
				$conf=$conf ."# file so it has a .ovpn extension           #\r\n";
				$conf=$conf ."##############################################\r\n";
				$conf=$conf ."client\r\n";
				
				if($this->main_array["GLOBAL"]["ENABLE_BRIDGE_MODE"]==1){
				$conf=$conf ."dev tap\r\n";
				}else{
					$conf=$conf ."dev tun\r\n";	
				}
				
				writelogs("_params[GLOBAL][PUBLIC_IP]= {$ini->_params["GLOBAL"]["PUBLIC_IP"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(!is_numeric($ini->_params["GLOBAL"]["CLIENT_NAT_PORT"])){$ini->_params["GLOBAL"]["CLIENT_NAT_PORT"]=$ini->_params["GLOBAL"]["LISTEN_PORT"];}
				if($ini->_params["GLOBAL"]["CLIENT_NAT_PORT"]==0){$ini->_params["GLOBAL"]["CLIENT_NAT_PORT"]=$ini->_params["GLOBAL"]["LISTEN_PORT"];}
				
				$conf=$conf ."proto {$ini->_params["GLOBAL"]["LISTEN_PROTO"]}\r\n";
				$conf=$conf ."remote {$ini->_params["GLOBAL"]["PUBLIC_IP"]} {$ini->_params["GLOBAL"]["CLIENT_NAT_PORT"]}\r\n";
				$conf=$conf ."resolv-retry infinite\r\n";
				$conf=$conf ."nobind\r\n";
				$conf=$conf ."persist-key\r\n";
				$conf=$conf ."persist-tun\r\n";
				
				
				if($ini->_params["GLOBAL"]["USE_RPROXY"]==1){
					$conf=$conf ."http-proxy-retry\r\n";
					$conf=$conf ."http-proxy {$ini->_params["GLOBAL"]["PROXYADDR"]} {$ini->_params["GLOBAL"]["PROXYPORT"]}\r\n";
					$conf=$conf ."http-proxy-option AGENT Mozilla/5.0+(Windows;+U;+Windows+NT+5.0;+fr-FR;+rv:1.7.6)+Gecko/20050226+Firefox/1.0.1\r\n";
				}else{
					$conf=$conf .";http-proxy-retry # retry on connection failures\r\n";
					$conf=$conf .";http-proxy [proxy server] [proxy port #]\r\n";
				}
				$conf=$conf ."mute-replay-warnings\r\n";
				$conf=$conf ."\r\n";
				$conf=$conf ."ca $uid-ca.crt\r\n";
				$conf=$conf ."cert $uid.crt\r\n";
				$conf=$conf ."key $uid.key\r\n";
				$conf=$conf ."ns-cert-type server\r\n";
				$conf=$conf ."comp-lzo\r\n";
				$conf=$conf ."verb 3\r\n";
				
				if($ini->_params["GLOBAL"]["LDAP_AUTH"]==1){
					$conf=$conf ."auth-user-pass\r\n";
				}
				
				
				
				
				$ipwin32="ip-win32 netsh";
				$conf=$conf .";System OS: $this->ComputerOS\r\n";
				if($this->ComputerOS<>null){
					if($this->ComputerOS=="windows2003"){$ipwin32=null;}
					if($this->ComputerOS=="linux"){$ipwin32=null;}
					if($this->ComputerOS=="mac"){$ipwin32=null;}
					if($this->ComputerOS=="Windows7"){
						$ipwin32=null;$script_secu="script-security 2 system";
					}
					
					
				}
				
				$conf=$conf ."$ipwin32\r\n";
				if($script_secu<>null){$conf=$conf ."$script_secu\r\n";}
				
				if($this->site_id>0){
					$q=new mysql();
					$sql="SELECT * FROM vpnclient WHERE ID='$this->site_id'";
					$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
					writelogs("SITE id $this->site_id ={$ligne["IP_START"]} ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$SITE_IP=$ligne["IP_START"];
				}
				
				
				if(is_array($this->routes)){
					$tt=0;
					reset($this->routes);
					while (list ($num, $ligne) = each ($this->routes) ){
						if(trim($ligne)==null){continue;}
						if($num==$SITE_IP){continue;}
						writelogs("\"$num\" (warn to $SITE_IP)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						$tt++;
						$conf=$conf. "#ROUTE\t$num\t$ligne\r\n";
					}
				}
				
				if($tt==0){
					$default_route=$this->DefaultRoute();
					if($default_route<>$SITE_IP){
						writelogs("Default Route:\"$default_route\" (warn to $SITE_IP)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						$conf=$conf. "#ROUTE\t$default_route\r\n";	
					}			
				}
				
				$conf=$conf .";mute 20\r\n";
				$conf=$conf .";wakeup:\"{$this->main_array["GLOBAL"]["WAKEUP_IP"]}\"\r\n";
				$conf=$conf. $this->GetRemotesSitesRoutes($SITE_IP);
				return $conf;
		
		
	}
	
	private function GetRemotesSitesRoutes($siteip=null){
		
	$sql="SELECT * FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$routess[]="";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IP_START"]==null){continue;}
		$ligne["IP_START"]=trim($ligne["IP_START"]);
		if($ligne["IP_START"]==$siteip){continue;}
		$ligne["netmask"]=trim($ligne["netmask"]);
		$routess[]="# REMOTE-SITE: {$ligne["IP_START"]};{$ligne["netmask"]}";   
	}
	if(is_array($routess)){
		return implode("\r\n",$routess);
	}
		
	}
	
	
	
	private function IpCalcRoute($ipsingle){
		 if(!preg_match("#(.+?)\.([0-9]+)$#",$ipsingle,$re)){
		 	writelogs("Unable to match IP \"$ipsingle\"",__FUNCTION__,__FILE__,__LINE__);
		 	return null;
		 	}
	
		
		 $tmp="/tmp/openvpn_tmp";
		 
		 shell_exec("/usr/share/artica-postfix/bin/ipcalc {$re[1]}.0 >$tmp 2>&1");
		 
		 $arr=explode("\n",@file_get_contents($tmp));
		 @unlink($tmp);
		
		 
			while (list ($num, $ligne) = each ($arr) ){
				
				if(preg_match("#^Netmask:\s+(.+?)\s+#",$ligne,$ri)){
					return "{$re[1]}.0 {$ri[1]}";
				}
				 
				
			}
			
			writelogs("Unable to match Netmask in array of ". count($arr)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	}

	private function DefaultRoute(){
		if($this->main_array["GLOBAL"]["IPTABLES_ETH"]==null){return null;}
		
		$nic=new networking();
		
		while (list ($num, $ligne) = each ($nic->array_TCP) ){
			if($ligne==null){continue;}
				$ethi[$num]=$ligne;
			} 		
			writelogs("IPTABLES_ETH={$this->main_array["GLOBAL"]["IPTABLES_ETH"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return $this->IpCalcRoute($ethi[$this->main_array["GLOBAL"]["IPTABLES_ETH"]]);
		
	}
	
	
	public function ImportConcentrateur($filepath,$sitename=null){
		if(!is_file($filepath)){$this->events[]="$filepath no such file";return false;}
		$log=array();
		$dir=dirname($filepath);
		$datas=explode("\n",@file_get_contents($filepath));
	
	while (list ($num, $val) = each ($datas) ){
		if(preg_match("#^remote\s+(.+?)\s+([0-9]+)#",$val,$re)){
			$server=$re[1];
			$port=$re[2];
		}
		
		if(preg_match("#^ca\s+(.+)#",$val,$re)){
			$re[1]=trim($re[1]);
			$ca_path="$dir/{$re[1]}";
		}
		
		if(preg_match("#^cert\s+(.+)#",$val,$re)){
			$re[1]=trim($re[1]);$cert_path="$dir/{$re[1]}";
		}

		if(preg_match("#^key\s+(.+)#",$val,$re)){
			$re[1]=trim($re[1]);$key_path="$dir/{$re[1]}";
		}			
		
		if(preg_match("#ip-win32#",$val)){
			unset($datas[$num]);
		}
		
		if(preg_match("#\#ROUTE\s+([0-9\.]+)\s+([0-9\.]+)#",$val,$re)){
			$routes[]="{$re[1]}\t{$re[2]}";
		}
		
	}	
	
	@file_put_contents($filepath,implode("\n",$datas));	
	$password_path="$dir/password";
	if(($server==null) && ($port==null)){$log[]="missing server and port, it is not a openvpn artica script!";}
	if(!is_file($ca_path)){$log[]="unable to stat &laquo;$ca_path&raquo;";}
	if(!is_file($cert_path)){$log[]="unable to stat &laquo;$cert_path&raquo;";}
	if(!is_file($key_path)){$log[]="unable to stat &laquo;$key_path&raquo;";}
	if(!is_file($password_path)){$log[]="unable to stat &laquo;$password_path&raquo;";}		
	if(count($log)>0){$this->events=$log;return false;}
	$q=new mysql();
	$q->CheckTables_openvpn();
	
	
	$ca=$q->ReadBinaryFile($ca_path);
	$cert=$q->ReadBinaryFile($cert_path);
	$key=$q->ReadBinaryFile($key_path);
	$ovpn=$q->ReadBinaryFile($filepath);
	$password=base64_encode(trim(@file_get_contents($password_path)));
	
	
	
	if(is_array($routes)){
		$rr=implode("\n",$routes);
	}
	
	$sql="SELECT ID FROM vpnclient WHERE servername='$server' AND serverport='$port' AND connexion_type=2";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$ID=$ligne["ID"];
	$this->events[]="Found ID:$ID for servername:$server and serverport:$port";
	
	
	
	$sql_add="INSERT INTO vpnclient (servername,serverport,connexion_name,connexion_type,
	ca_bin,key_bin,cert_bin,ovpn,routes,enabled,keypassword,sitename)
	VALUES('$server','$port','$server:$port',2,'$ca','$key','$cert','$ovpn','$rr',1,'$password','$sitename');";
	
	$sql_edit="UPDATE vpnclient SET ca_bin='$ca',key_bin='$key',cert_bin='$cert',ovpn='$ovpn',routes='$rr',keypassword='$password',sitename='$sitename' WHERE ID=$ID;";
	
	if($ID>0){
		$q->QUERY_SQL($sql_edit,"artica_backup");
		$this->events[]="Editing existing config $ID";
	}else{
		$q->QUERY_SQL($sql_add,"artica_backup");
		$this->events[]="Adding new config $ID";
	}
	if(!$q->ok){
		$this->events[]=$q->mysql_error;
		return false;
	}
	
	$this->events[]="{success}";
	@unlink($ca_path);
	@unlink($cert_path);
	@unlink($key_path);
	@unlink($filepath);
	
	$sock=new sockets();
	$sock->getFrameWork("openvpn.php?restart-clients=yes");
	$sock->getFrameWork("cmd.php?artica-meta-openvpn-sites=yes");
	
	return true;		

	}
	
	
	
	
	
}

?>