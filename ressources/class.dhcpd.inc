<?php
include_once(dirname(__FILE__).'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.system.network.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.ini.inc');
include_once(dirname(__FILE__).'/class.bind9.inc');
include_once(dirname(__FILE__) .'/class.tcpip.inc');
include_once(dirname(__FILE__) .'/class.mysql.inc');


class dhcpd_hosts{
	var $uid;
	var $dn;
	var $dhcpHWAddress;
	var $dhcpStatements;

	var $IP;
	var $MAC;
	
	function dhcpd_hosts($uid=null){
		
		if($uid==null){
			writelogs("DHCP:: Resquest an uid null, aborting",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
		$this->uid=$uid;	
		$this->load();
		
	}
	
	private function load(){
		if(substr($this->uid,0,1)=='$'){$this->uid=substr($this->uid,1,strlen($this->uid));}
		if(substr($this->uid,strlen($this->uid)-1,1)=='$'){$this->uid=substr($this->uid,0,strlen($this->uid)-1);}
		$ldap=new clladp();
		$this->dn="cn=$this->uid,cn=DhcpConfig,ou=dhcp,$ldap->suffix";
		if(!$ldap->ExistsDN($this->dn)){return null;}
		$hash=$ldap->Ldap_read($this->dn,"(objectclass=*)",array());
		$this->dhcpHWAddress=$hash[0][strtolower("dhcpHWAddress")][0];
		$this->dhcpStatements=$hash[0][strtolower("dhcpStatements")][0];
	
		
	}
	
function DeleteFixed(){
	$ldap=new clladp();
	if(!$ldap->ExistsDN($this->dn)){return true;}
	$tpl=new templates();
	if(!$ldap->ldap_delete($this->dn)){
		echo $tpl->_ENGINE_parse_body("DHCP:: {error} function:".__FUNCTION__."\nfile: ".__FILE__."\nline:".__LINE__."\n");
		echo $ldap->ldap_last_error;
		return false;
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?apply-dhcpd=yes");
	
	return true;
	
}	
function AddFixed(){
	$ldap=new clladp();
	$tpl=new templates();
	$tcp=new IP();
	if($this->dn==null){
		$this->dn="cn=$this->uid,cn=DhcpConfig,ou=dhcp,$ldap->suffix";
	}
	
	if(!$tcp->isValid($this->IP)){
		echo $tpl->_ENGINE_parse_body("DHCP:: {error} IP: $this->IP {invalid}\n");
		return false;
	}	
	
	
	$this->LDAPBranch();
	if(!$ldap->ExistsDN($this->dn)){
		$upd=array();
		$upd["objectClass"][]="top";
		$upd["objectClass"][]="dhcpHost";
		$upd["cn"][]="$this->uid";
		if(!$ldap->ldap_add($this->dn,$upd)){
			echo $tpl->_ENGINE_parse_body("DHCP:: {error} function:".__FUNCTION__."\nfile: ".__FILE__."\nline:".__LINE__."\n");
			echo $ldap->ldap_last_error;
			return false;
		}
		
		unset($upd);
	}
	
	$upd["dhcpHWAddress"][0]="ethernet $this->MAC";
	$upd["dhcpStatements"][0]="fixed-address $this->IP";
	if(!$ldap->Ldap_modify($this->dn,$upd)){
	   echo $tpl->_ENGINE_parse_body("DHCP:: {error} function:".__FUNCTION__."\nfile: ".__FILE__."\nline:".__LINE__."\n");
	   echo $ldap->ldap_last_error;
	   return false;
		
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?apply-dhcpd=yes");
	return true;	
	
}
	
function LDAPBranch(){
	$ldap=new clladp();
	$users=new usersMenus();
	$dn="ou=dhcp,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd=array();
		$upd["ou"][]="dhcp";
		$upd["objectClass"][]="organizationalUnit";
		$upd["objectClass"][]="top";
		if(!$ldap->ldap_add($dn,$upd)){
			echo __FUNCTION__.' line '.__LINE__."\n".__FILE__;
			echo "Please try to re-index your LDAP server\n";
			echo "Under the troubleshoot section\n";			
			echo $ldap->ldap_last_error;
			return;
		}
		
	}
	
	
	$dn="cn=$users->hostname,ou=dhcp,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]="dhcpServer";
		$upd["objectClass"][]="top";
		$upd["cn"][]="$users->hostname";
		$upd["dhcpServiceDN"][]="cn=dhcpConfig,ou=dhcp,$ldap->suffix";
		
		if(!$ldap->ldap_add($dn,$upd)){
			echo __FUNCTION__.' line '.__LINE__."\n".__FILE__;
			echo "Please try to re-index your LDAP server\n";
			echo "Under the troubleshoot section\n";			
			echo $ldap->ldap_last_error;
			return;
		}
		
	}	
	
	$dn="cn=DhcpConfig,ou=dhcp,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]="dhcpService";
		$upd["objectClass"][]="dhcpOptions";
		$upd["dhcpStatements"][]="ddns-update-style ad-hoc";
		$upd["dhcpStatements"][]="default-lease-time 86400";
		$upd["dhcpStatements"][]="max-lease-time 604800";
		$upd["dhcpPrimaryDN"][]="ou=dhcp,$ldap->suffix";
		if(!$ldap->ldap_add($dn,$upd)){
			echo __FUNCTION__.' line '.__LINE__."\n".__FILE__;
			echo "Please try to re-index your LDAP server\n";
			echo "Under the troubleshoot section\n";
			echo $ldap->ldap_last_error;
			return;
		}
		
	}	
	
}	
}


class dhcpd{
	var $ddns_domainname="";
	var $max_lease_time;
	var $array_tcp;
	var $listen_nic;
	var $netmask;
	var $range2;
	var $range1;
	var $DNS_1;
	var $DNS_2;
	var $EnableArticaAsGateway=0;
	var $EnableArticaAsDNSFirst=0;
	var $gateway;
	var $pxe_enable=0;
	var $pxe_server=null;
	var $pxe_file=null;
	var $conf;
	var $routes=array();
	var $ntp_server=null;
	var $OnlySetGateway=0;
	var $subnet;
	var $broadcast=null;
	var $WINS=null;
	var $noload=0;
	
	function dhcpd($noload=0){
		if($noload<>0){$this->noload=$noload;return ;}
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		

		
		$this->EnableArticaAsGateway=$sock->GET_INFO("EnableArticaAsGateway");
		$ini->loadString($sock->GET_INFO('ArticaDHCPSettings'));
		if($GLOBALS["VERBOSE"]){while (list ($num, $ligne) = each ($ini->_params["SET"]) ){echo "Starting......: DHCP server DEBUG: loading $num = $ligne\n";}}		
		
		$this->ddns_domainname=$ini->_params["SET"]["ddns_domainname"];
		$this->max_lease_time=$ini->_params["SET"]["max_lease_time"];
		$this->listen_nic=$ini->_params["SET"]["listen_nic"];
		$this->netmask=$ini->_params["SET"]["netmask"];
		$this->range1=$ini->_params["SET"]["range1"];
		$this->range2=$ini->_params["SET"]["range2"];
		$this->EnableArticaAsDNSFirst=trim($ini->_params["SET"]["EnableArticaAsDNSFirst"]);
		$this->gateway=$ini->_params["SET"]["gateway"];
		$this->DNS_1=$ini->_params["SET"]["DNS_1"];
		$this->DNS_2=$ini->_params["SET"]["DNS_2"];
		$this->pxe_server=$ini->_params["SET"]["pxe_server"];
		$this->pxe_file=$ini->_params["SET"]["pxe_file"];
		$this->pxe_enable=$ini->_params["SET"]["pxe_enable"];
		$this->ntp_server=$ini->_params["SET"]["ntp_server"];
		$this->subnet=$ini->_params["SET"]["subnet"];
		$this->broadcast=$ini->_params["SET"]["broadcast"];
		$this->WINS=$ini->_params["SET"]["WINS"];
		$this->OnlySetGateway=$sock->GET_INFO("DHCPOnlySetGateway");
		if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP server DEBUG: loading OnlySetGateway = [DHCPOnlySetGateway]  $this->OnlySetGateway\n";}
		$ip=new networking();
		$ip->LoadIPs();
		$this->array_tcp=$ip->Local_interfaces();
		if($this->pxe_file==null){$this->pxe_file="/tftpboot/pxelinux.0";}
		if($this->max_lease_time==null){$this->max_lease_time=3600;}
		if($this->netmask==null){$this->netmask="255.255.255.0";}
		//if($this->range1==null){$this->range1="192.168.1.55";}
		//if($this->range2==null){$this->range2="192.168.1.240";}
		if(!is_numeric($this->OnlySetGateway)){
			if($GLOBALS["VERBOSE"]){"Starting......: DHCP DEBUG OnlySetGateway is not numeric, set it to 0\n";}
			$this->OnlySetGateway=0;
		}
		$this->LoadRoutes();
		$this->RemoteVPN();
		if(!$GLOBALS["ASROOT"]){$this->conf=$this->BuildConf();}
		
	}
	
	function Save(){
		if($this->noload<>0){return;}
		$ini=$ini."[SET]\n";
		$ini=$ini."ddns_domainname=$this->ddns_domainname\n";
		$ini=$ini."max_lease_time=$this->max_lease_time\n";
		$ini=$ini."listen_nic=$this->listen_nic\n";
		$ini=$ini."netmask=$this->netmask\n";
		$ini=$ini."range1=$this->range1\n";
		$ini=$ini."range2=$this->range2\n";
		$ini=$ini."gateway=$this->gateway\n";
		$ini=$ini."DNS_1=$this->DNS_1\n";
		$ini=$ini."DNS_2=$this->DNS_2\n";
		$ini=$ini."pxe_server=$this->pxe_server\n";
		$ini=$ini."pxe_file=$this->pxe_file\n";
		$ini=$ini."pxe_enable=$this->pxe_enable\n";
		$ini=$ini."EnableArticaAsDNSFirst=$this->EnableArticaAsDNSFirst\n";
		$ini=$ini."ntp_server=$this->ntp_server\n";
		$ini=$ini."subnet=$this->subnet\n";
		$ini=$ini."broadcast=$this->broadcast\n";
		$ini=$ini."WINS=$this->WINS\n";
		
		$sock=new sockets();
		$sock->SaveConfigFile($ini,'ArticaDHCPSettings');
		$sock->SaveConfigFile($this->BuildConf(),'DHCP3ConfigurationFile');
		$sock->SET_INFO('DHCP3ListenNIC',$this->listen_nic);
		if($this->OnlySetGateway==0){if($this->EnableArticaAsDNSFirst==1){$sock->getFrameWork('cmd.php?apply-bind=yes');}}
		$sock->getFrameWork('cmd.php?restart-dhcpd=yes');
		
		
		
	}
	
function DeleteFixed($COMPUTER_NAME){
	$dhcp=new dhcpd_hosts($COMPUTER_NAME);
	return $dhcp->DeleteFixed();
	
}

function AddFixed($COMPUTER_NAME,$MAC,$IP){
	$dhcp=new dhcpd_hosts($COMPUTER_NAME);
	$dhcp->MAC=$MAC;
	$dhcp->IP=$IP;
	return $dhcp->AddFixed();
	
}

function AddRoute($ip,$netmask,$gateway,$info=null){
	$this->LoadRoutes();
	if(!is_array($this->routes[$ip])){
		$this->routes[$ip]=array($netmask,$gateway,$info);
		$this->SaveRoutes();
		$this->Save();
	}
}

function DelRoute($ip){
	$this->LoadRoutes();
	while (list ($ipr, $array) = each ($this->routes) ){
		if($ipr==$ip){
			writelogs("Delete Gateway $ip (".$this->routes[$ip][0].")",__CLASS__.'/'.__FUNCTION__,__FILE__);
			continue;
		}
		$arrayF[]="$ipr;{$array[0]};{$array[1]}";
	}
	
	if(is_array($arrayF)){$conf=implode("\n",$arrayF);}
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"DHCPDRoutesInfos");
}

private function LoadRoutes(){
	$sock=new sockets();
	$datas=explode("\n",$sock->GET_INFO("DHCPDRoutesInfos"));
	while (list ($num, $ligne) = each ($datas) ){
		$tbl=explode(";",$ligne);
		if(count($tbl)>1){
			$this->routes[$tbl[0]]=array($tbl[1],$tbl[2]);
		}
		
	}
}

private function SaveRoutes(){
	if(is_array($this->routes)){
	reset($this->routes);
	$conf=null;
	while (list ($ip, $array) = each ($this->routes) ){
		$ll="$ip;{$array[0]};{$array[1]};{$array[2]}";
		writelogs("$ll",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$arrayF[]="$ip;{$array[0]};{$array[1]};{$array[2]}";
	}
		if(is_array($arrayF)){
			$conf=implode("\n",$arrayF);
		}
	}
	
	
	
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"DHCPDRoutesInfos");
}


private function GetMyDNS(){
		$ip=new networking();
		if($this->listen_nic==null){return null;}
		$mip=$ip->GetNicInfos($this->listen_nic);	
		$eth_IP=$mip["IPADDR"];
		if($eth_IP=="0.0.0.0"){$eth_IP=$ip->default_network();}
	
		if($this->EnableArticaAsDNSFirst==1){$arrayNameServers_tmp[$eth_IP]=$eth_IP;}
		if($this->DNS_2<>null){$arrayNameServers_tmp[$this->DNS_2]=$this->DNS_2;}
		if($this->DNS_1<>null){$arrayNameServers_tmp[$this->DNS_1]=$this->DNS_1;}
		
		if($GLOBALS["VERBOSE"]){echo "DEBUG: DNS server in array of ". count($arrayNameServers_tmp)."\n";}
		
		if(is_array($ip->arrayNameServers)){
			while (list ($index, $ipaddr) = each ($ip->arrayNameServers) ){
				if($ipaddr=="0.0.0.0"){continue;}
				$arrayNameServers_tmp[$ipaddr]=$ipaddr;
				if($GLOBALS["VERBOSE"]){echo "DEBUG: DNS: $ipaddr\n";}
			}
		}

			$arrayNameServers=array();
			while (list ($index, $ipaddr) = each ($arrayNameServers_tmp) ){
				if($ipaddr=="0.0.0.0"){continue;}
				$arrayNameServers[]=$ipaddr;
			}			
		if($GLOBALS["VERBOSE"]){echo "DEBUG: DNS server return array of ". count($arrayNameServers_tmp)." element(s)\n";}	
		return $arrayNameServers;
	
}

	private function GetDefaultNic(){
		$sock=new sockets();
		$devs=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics")));
		if(!is_array($devs)){return null;}
		return $devs[0];
	}
	
	
	function BuildConf(){
		if($GLOBALS["ASROOT"]){if(!is_file("/var/lib/dhcp3/dhcpd.other")){@file_put_contents("/var/lib/dhcp3/dhcpd.other","");}}
		$ip=new networking();
		$users=new usersMenus();
		$users->LoadModulesEnabled();
		$conf=null;
		if($this->listen_nic==null){$this->GetDefaultNic();}
		$mip=$ip->GetNicInfos($this->listen_nic);	
		$eth_gateway=$mip["GATEWAY"];
		$eth_IP=$mip["IPADDR"];
		if(!is_numeric($this->pxe_enable)){$this->pxe_enable=0;}
		$conf=$conf."include \"/var/lib/dhcp3/dhcpd.other\";\n";
		if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP broadcast =  $this->broadcast\n";}
		
		if($this->ddns_domainname<>null){
			$conf=$conf."ddns-domainname \"$this->ddns_domainname\";\n";
		}
		
		if($users->BIND9_INSTALLED){
			$sock=new sockets();
			$Bind9GlobalKey=$sock->GET_INFO('Bind9GlobalKey');
			$conf=$conf."\n";
			$conf=$conf.  "key globalkey {\n";
			$conf=$conf.  "\talgorithm hmac-md5;\n";
			$conf=$conf.  "\tsecret \"$Bind9GlobalKey\";\n";
			$conf=$conf.  "};\n";
			$conf=$conf."\n";
			
			$conf=$conf.  "ddns-update-style interim;\n";
			$conf=$conf . "update-static-leases on;\n";
			$conf=$conf . "do-forward-updates on;\n";
			$conf=$conf . "ddns-updates on;\n"; 
			$conf=$conf . "ignore client-updates;\n";
			$conf=$conf . "update-static-leases on;\n";
		}else{
			$conf=$conf."ddns-update-style none;\n";
		}
		
		$conf=$conf."allow unknown-clients;\n";
		$conf=$conf."authoritative;\n";
		$conf=$conf."max-lease-time $this->max_lease_time;\n";
		$conf=$conf."default-lease-time $this->max_lease_time;\n";
		$conf=$conf."get-lease-hostnames true;\n";
		

		
		if($this->gateway<>null){
			if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP DEBUG: gateway [". count($gateway_array)."] = $this->gateway set by admin\n";}
			$gateway_array[]=$this->gateway;
		}
		
		if($this->EnableArticaAsGateway==1){
			if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP DEBUG: gateway [". count($gateway_array)."] = $eth_IP (EnableArticaAsGateway = 1) \n";}
			$gateway_array[]=$eth_IP;
		}
		
		if($GLOBALS["VERBOSE"]){echo "DEBUG: ->BuildRoutes() Line ".__LINE__."\n";}
		$routes=$this->BuildRoutes();
		if(count($routes["ROUTERS"])>0){
			while (list ($index, $router) = each ($routes["ROUTERS"]) ){
			$gateway_array[]=$router;
			}
			$conf=$conf."option ms-classless-static-routes code 249 = array of unsigned integer 8;\n";
			$conf=$conf."option rfc3442-classless-static-routes code 121 = array of unsigned integer 8;\n";
			$conf=$conf."option ms-classless-static-routes ". implode(" ,",$routes["COMPILE"]).";\n";
			$conf=$conf."option rfc3442-classless-static-routes ". implode(" ,",$routes["COMPILE"]).";\n";
		}
		if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->WINS)){
			$conf=$conf."option netbios-name-servers $this->WINS;\n";
		}
		
		
		
		
		if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->subnet,$re)){
			if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->range1,$re)){
				preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->broadcast,$re);
			}
		}
			
		$range_suffix=0;
		if(is_numeric($re[4])){if($re[4]>0){$range_suffix=$re[4];}}
		$conf=$conf."\n";
		$conf=$conf."subnet {$re[1]}.{$re[2]}.{$re[3]}.$range_suffix netmask $this->netmask {\n";
		if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->range1,$re)){
			$conf=$conf."\trange $this->range1 $this->range2;\n";
		}
		
		
		if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->broadcast,$re)){
			if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP DEBUG: broadcast =  $this->broadcast -> {$re[1]}.{$re[2]}.{$re[3]}.{$re[4]}\n";}
			$conf=$conf."\toption broadcast-address {$re[1]}.{$re[2]}.{$re[3]}.{$re[4]};\n";
		}else{
			if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP DEBUG: broadcast =  no match skip it\n";}
		}
		if($this->ntp_server<>null){
			$conf=$conf."\toption ntp-servers $this->ntp_server;\n";
			
		}
		
		
		
		if($GLOBALS["ASROOT"]){echo "Starting......: DHCP server PXE enabled ? \"$this->pxe_enable\"\n";}
		if($this->pxe_enable==1){
			if($this->pxe_file<>null){
				$conf=$conf."\tfilename \"$this->pxe_file\";\n";
				if($this->pxe_server<>null){
					$conf=$conf."\tnext-server $this->pxe_server;\n";
				}
			}
		}
		if($GLOBALS["VERBOSE"]){echo "OnlySetGateway = $this->OnlySetGateway line ". __LINE__."\n";}
		if($this->OnlySetGateway==0){	
			 if($GLOBALS["VERBOSE"]){echo "DEBUG: OnlySetGateway is set to 0 , gateway_array() ->". count($gateway_array)." items  (OnlySetGateway 0 ) Line ".__LINE__."\n";}
			 if(is_array($gateway_array)){	
						reset($gateway_array);
						while (list ($index, $m_gw) = each ($gateway_array) ){
							if($m_gw==null){continue;}
							if($m_gw=="0.0.0.0"){continue;}
							$k[]=$m_gw;
						}
						$conf=$conf."\toption routers ". implode(",",$k). ";\n";
				}
		}else{
			if($GLOBALS["VERBOSE"]){echo "Starting......: DHCP OnlySetGateway = 1 adding only $this->gateway as gateway\n";}
			$conf=$conf."\toption routers $this->gateway;\n";	
		}
	
	
			
		
		$arrayNameServers=$this->GetMyDNS();
		if($GLOBALS["VERBOSE"]){echo "DEBUG: ->GetMyDNS() ->". count($arrayNameServers)." items Line ".__LINE__."\n";}	
		
		if(is_array($ip->arrayNameServers)){
			reset($ip->arrayNameServers);
			if($GLOBALS["ASROOT"]){echo "Starting......: DHCP server ". count($arrayNameServers)." domain name servers\n";}
			
			if(count($arrayNameServers)==0){
				if($GLOBALS["ASROOT"]){
					echo "Starting......: DHCP SERVER no name servers set, try to get defaults via resolv.conf\n";
					$resolv_conf=explode("\n",@file_get_contents("/etc/resolv.conf"));
					while (list ($index, $lines) = each ($resolv_conf) ){
						if(preg_match("#127\.0\.0\.1#",$lines)){continue;}
						if(preg_match("#^nameserver\s+(.+)#",$lines,$re)){
							$g=trim($re[1]);
							if($g=="127.0.0.1"){continue;}
							$arrayNameServers[]=$g;
							echo "Starting......: DHCP SERVER no name server: $g\n";
						}
					}
				}
			}
		}

		if($GLOBALS["VERBOSE"]){echo "DEBUG: Buiding domain-name-servers with array of ".count($arrayNameServers)." element(s)\n";}
		if(count($arrayNameServers)>0){
			if($GLOBALS["VERBOSE"]){echo "DEBUG: Buiding domain-name-servers ->option domain-name-servers ". implode(",",$arrayNameServers)."\n";}
			$conf=$conf ."\toption domain-name-servers ". implode(",",$arrayNameServers).";\n";
		}		
		
		if($this->ddns_domainname<>null){
			$conf=$conf."\toption domain-name \"$this->ddns_domainname\";\n";
		}		
		
		
		if($users->BIND9_INSTALLED){
			if($GLOBALS["VERBOSE"]){echo "DEBUG: ->Bind9 Installed -> Line ".__LINE__."\n";}
			if($this->ddns_domainname<>null){
			$conf=$conf."\tddns-rev-domainname \"in-addr.arpa\";\n\n";
			$conf=$conf."\tzone $this->ddns_domainname. {\n";
			$conf=$conf."\t\tprimary 127.0.0.1;\n";
			$conf=$conf."\t\tkey globalkey;\n";
			$conf=$conf."\t}\n";
			$conf=$conf."\n";
			if((is_numeric($re[3])) &&  (is_numeric($re[2])) && (is_numeric($re[1]))  ){
				$conf=$conf."\tzone {$re[3]}.{$re[2]}.{$re[1]}.in-addr.arpa. {\n";
				$conf=$conf."\t\tprimary 127.0.0.1;\n";
				$conf=$conf."\t\tkey globalkey;\n";
				$conf=$conf."\t}\n";	
			}
			
			$this->SetBind9Zone();
			}			
			
		}else{
			if($GLOBALS["VERBOSE"]){echo "DEBUG: ->Bind9 not Installed -> Line ".__LINE__."\n";}
			$conf=$conf."\tddns-updates off;\n"; 
			
		}
		$conf=$conf."}\n\n";
		if($GLOBALS["VERBOSE"]){echo "DEBUG: ->SetFixedAddresses() -> Line ".__LINE__."\n";}
		$conf=$conf.$this->SetFixedAddresses();
		$conf=$conf.$this->shared_networks();
		
		
		if($GLOBALS["VERBOSE"]){echo "DEBUG: ->END ->". strlen($conf)." bytes Line ".__LINE__."\n";}
		return $conf;
		
	}
	
	
	function LoadfixedAddresses(){
		$ldap=new clladp();
		$IP=new IP();	
		$filter=array("computerip","ComputerMacAddress","cn","uid","DnsZoneName");
		$query="(&(objectClass=ArticaComputerInfos)(DnsZoneName=$this->ddns_domainname))";
		$hash=$ldap->Ldap_search($ldap->suffix,$query,$filter);
		$count=$hash["count"];
		$ARR=array();
		writelogs("compile {$hash["count"]} computers for $this->ddns_domainname",__CLASS__.'/'.__FUNCTION__,__FILE__);
		for($i=0;$i<$hash["count"];$i++){
			if($GLOBALS["VERBOSE"]){echo "->LoadfixedAddresses() ->". $hash[$i]["uid"][0]." item Line ".__LINE__."\n";}
			$uid=str_replace('$','',$hash[$i]["uid"][0]);
			if($uid==null){
				if($GLOBALS["VERBOSE"]){echo "->LoadfixedAddresses() -> null item, aborting Line ".__LINE__."\n";}
				continue;
			}
			$ip_addr=$hash[$i]["computerip"][0];
			$ComputerMacAddress=$hash[$i][strtolower("ComputerMacAddress")][0];
			$DnsZoneName=$hash[$i][strtolower("DnsZoneName")][0];
			if($DnsZoneName<>$this->ddns_domainname){$DnsZoneName=$this->ddns_domainname;}
			
			
			if(!$IP->isIPAddress($ip_addr)){continue;}
			if($ComputerMacAddress==null){continue;}
			if(!$this->IsPhysicalAddress($ComputerMacAddress)){continue;}
			$ARR[$uid]=array("MAC"=>"hardware ethernet $ComputerMacAddress","IP"=>"fixed-address $ip_addr","domainname"=>$DnsZoneName);
		}
		
	$query="(&(objectClass=dhcpHost)(cn=*))";
	$dn="cn=DhcpConfig,ou=dhcp,$ldap->suffix";
	$filter=array();
	
	$hash=$ldap->Ldap_search($dn,$query,$filter);
	$count=$hash["count"];	
	
	for($i=0;$i<$count;$i++){
		$uid=$hash[$i]["cn"][0];	
		$MacAddress=$hash[$i][strtolower("dhcpHWAddress")][0];
		if(!$this->IsPhysicalAddress($MacAddress)){continue;}
		$ARR[$uid]=array("MAC"=>"hardware ".$hash[$i][strtolower("dhcpHWAddress")][0],"IP"=>$hash[$i][strtolower("dhcpStatements")][0]);
	}
	
	return $ARR;
	
	}
	
	private function  IsPhysicalAddress($address){
		$address=strtoupper(trim($address));
		if($address=="UNKNOWN"){return null;}
		$address=str_replace(":","-",$address);
		If(strlen($address) > 18){
			writelogs("$address too many caracters, exceed 18 ",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If($address == ""){
			writelogs("$address is null !",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If(!preg_match("#^[0-9A-Z]+(\-[0-9A-Z]+)+(\-[0-9A-Z]+)+(\-[0-9A-Z]+)+(\-[0-9A-Z]+)+(\-[0-9A-Z]+)$#i",$address)){
			writelogs("$address did not match pattern ",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		$Array=explode("-",$address);
		If(strlen($Array[0]) != 2){
			writelogs("$address: array 0 did not match 2",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If(strlen($Array[1]) != 2){
			writelogs("$address:array 1 did not match 2",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If(strlen($Array[2]) != 2){
			writelogs("$address:array 2 did not match 2",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If(strlen($Array[3]) != 2){
			writelogs("$address:array 3 did not match 2",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If(strlen($Array[4]) != 2){
			writelogs("$address:array 4 did not match 2",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		If(strlen($Array[5]) != 2){
			writelogs("$address:array 5 did not match 2",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		writelogs("$address ok",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		return true;
	}	
	
	
	private function SetFixedAddresses(){
		$sock=new sockets();
		$conf=null;
		$EnableDHCPUseHostnameOnFixed=$sock->GET_INFO("EnableDHCPUseHostnameOnFixed");
		$IncludeDHCPLdapDatabase=Field_checkbox("IncludeDHCPLdapDatabase",1,$IncludeDHCPLdapDatabase);
		if(!is_numeric($EnableDHCPUseHostnameOnFixed)){$EnableDHCPUseHostnameOnFixed=0;}
		if(!is_numeric($IncludeDHCPLdapDatabase)){$IncludeDHCPLdapDatabase=1;}
		if($IncludeDHCPLdapDatabase==0){return;}
		$array=$this->LoadfixedAddresses();
		if(!is_array($array)){return null;}
		while (list ($num, $ligne) = each ($array) ){
				if($num==null){continue;}
				if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$#", trim($num))){continue;}
				$IP=$ligne["IP"];
				$IP=trim(str_replace("fixed-address","",$IP));
				
				if($EnableDHCPUseHostnameOnFixed==1){
					$hostname=gethostbyaddr($IP);
					if($hostname<>$IP){
						$ligne["IP"]="fixed-address $hostname";
					}
				}
				$domainname=$ligne["domainname"];
				if(preg_match("#localhost#",$domainname)){$domainname=null;}
				$conf=$conf ."\thost $num{\n";
				$conf=$conf ."\t\t{$ligne["MAC"]};\n";
				$conf=$conf ."\t\t{$ligne["IP"]};\n";
				$conf=$conf ."\t\toption host-name \"$num\";\n";
				if($domainname<>null){$conf=$conf ."\t\toption domain-name \"$domainname\";\n";}
				$conf=$conf ."\t}\n";
		
			}
	
		return $conf;
		
	}
	
	
	function SetBind9Zone(){
	
		$bind=new Bind9Zone($this->ddns_domainname);
		$bind->SaveBind9Zone($this->range1);

	}
	
	
	function GetKey($domain){
		$sock=new sockets();
		$md=md5($domain);
		$datas=$sock->GET_INFO("Bind9DnsKey.$md.key");
		if($datas==null){
			$key=$sock->getfile("Bind9Key:$domain");
			$conf=$conf."[KEY]\n";
			$conf=$conf."";
		}
		
		
	}
	
	private function RemoteVPN(){
			$ip=new networking();
			$array=$ip->array_TCP;
			
			$gateway=$array[$this->listen_nic];
			$sql="SELECT IP_START,netmask,sitename  FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				writelogs("find route on vpn=>{$ligne["IP_START"]}=>{$ligne["netmask"]}=>$gateway",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->AddRoute($ligne["IP_START"],$ligne["netmask"],$gateway,"VPN ".$ligne["sitename"]);
			}
	}
	
	private function BuildRoutes(){
		if(!is_array($this->routes)){return array();}
		$datas=$this->routes;
		$line1=array();
		$routers=array();
		while (list ($ipaddr, $infos) = each ($datas) ){
			if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$ipaddr,$re)){continue;}
			unset($re[0]);
			if($re[4]==0){unset($re[4]);}
			if($re[3]==0){unset($re[3]);}
			if($re[2]==0){unset($re[2]);}
			$ip=new networking();
			$cdir=$ip->route_shouldbe($ipaddr,$infos[0]);
			if($cdir==null){continue;}
			if(preg_match("#\/([0-9]+)#",$cdir,$cdr)){$cdir=$cdr[1];}
			if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$infos[1],$router)){continue;}
			unset($router[0]);
			$line1[]="$cdir, ". implode(",",$re).", " .implode(",",$router);
			$routers[]=$infos[1];
			
		}
		
		return array("COMPILE"=>$line1,"ROUTERS"=>$routers);
	}
	
	
	private function shared_networks(){
		$q=new mysql();
		$sql="SELECT sharednet_name FROM dhcpd_sharednets GROUP BY sharednet_name ORDER BY sharednet_name";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(mysql_num_rows($results)==0){return null;}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$content=$this->shared_networks_sub($ligne["sharednet_name"]);
			if($content==null){continue;}
			$ligne["sharednet_name"]=trim($ligne["sharednet_name"]);
			if($ligne["sharednet_name"]==null){continue;}
			$ligne["sharednet_name"]=str_replace(" ", "-", $ligne["sharednet_name"]);
			$f[]="shared-network {$ligne["sharednet_name"]} {";
			$f[]=$content;
			$f[]="}\n";
		}
		
		return @implode("\n", $f);		
	}
	
	
	private function shared_networks_sub($mainet){
		if($GLOBALS["VERBOSE"]){echo "->shared_networks_sub() -> $mainet item Line ".__LINE__."\n";}
		$q=new mysql();
		$spacer="    ";
		$sql="SELECT * FROM dhcpd_sharednets WHERE sharednet_name='$mainet'";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(mysql_num_rows($results)==0){return null;}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$dns=array();
			$subnet=trim($ligne["subnet"]);
			$netmask=trim($ligne["netmask"]);
			$range1=trim($ligne["range1"]);
			$range2=trim($ligne["range2"]);
			if($subnet==null){continue;}
			if($netmask==null){continue;}
			if($range1==null){continue;}
			if($range2==null){continue;}	
			if(trim($ligne["domain-name-servers1"])<>null){$dns[]=trim($ligne["domain-name-servers1"]);}
			if(trim($ligne["domain-name-servers2"])<>null){$dns[]=trim($ligne["domain-name-servers2"]);}
			
			$f[]="#Scope \"{$ligne["scopename"]}\"";
			if($GLOBALS["VERBOSE"]){echo "->shared_networks_sub() -> [{$ligne["scopename"]}] subnet $subnet netmask $netmask Line ".__LINE__."\n";}
			$f[]="{$spacer}subnet $subnet netmask $netmask {";
			$f[]="{$spacer}{$spacer}range $range1 $range2;";
			if(trim($ligne["subnet-mask"])<>null){$f[]="{$spacer}{$spacer}option subnet-mask {$ligne["subnet-mask"]};";}
			if(trim($ligne["domain-name"])<>null){$f[]="{$spacer}{$spacer}option domain-name \"{$ligne["domain-name"]}\";";}
			if(trim($ligne["routers"])<>null){$f[]="{$spacer}{$spacer}option routers {$ligne["routers"]};";}
			
			if(trim($ligne["option-176"])<>null){
				$ligne["option-176"]=str_replace("\n", "", $ligne["option-176"]);
				$f[]="{$spacer}{$spacer}option option-176 \"{$ligne["option-176"]}\";";
			}
			if(count($dns)>0){$f[]="{$spacer}{$spacer}option domain-name-servers ". @implode(", ", $dns).";";}
			if(trim($ligne["wins-server"])<>null){$f[]="{$spacer}{$spacer}option netbios-name-servers {$ligne["wins-server"]};";}
			if(trim($ligne["tftp-server-name"])<>null){$f[]="{$spacer}{$spacer}option tftp-server-name \"{$ligne["tftp-server-name"]}\";";}
			if(trim($ligne["server-name"])<>null){$f[]="{$spacer}{$spacer}option server-name \"{$ligne["server-name"]}\";";}
			if(trim($ligne["next-server"])<>null){$f[]="{$spacer}{$spacer}option next-server {$ligne["next-server"]};";}
			if(trim($ligne["pxe_filename"])<>null){$f[]="{$spacer}{$spacer}option filename \"{$ligne["pxe_filename"]}\";";}
			if(trim($ligne["pxe_root-path"])<>null){$f[]="{$spacer}{$spacer}option root-path \"{$ligne["pxe_root-path"]}\";";}
			
			$f[]="{$spacer}}\n";
			}	
		
		return @implode("\n",$f);
	}
	
	
	
}

?>