<?php
	include_once(dirname(__FILE__).'/class.mysql.inc');
	include_once(dirname(__FILE__).'/class.sockets.inc');

class system_nic{
	var $eth;
	var $NETMASK;
	var $GATEWAY;
	var $BROADCAST;
	var $IPADDR;
	var $DNS1;
	var $DNS2;
	var $dhcp=0;
	var $ROUTES=array();
	var $NETWORK;
	var $unconfigured=true;
	var $enabled=0;
	
	function system_nic($eth=null){
		if($eth<>null){
			$this->eth=$eth;
			$this->loadEthConfig();
		}
		
		$this->CheckUnconfigured();
		
	}
	
	private function loadEthConfig(){
		$q=new mysql();
		writelogs("Loading nic infos from $this->eth",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sql="SELECT * FROM `nics` WHERE `Interface`='$this->eth'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$this->dhcp=0;
		if($ligne["Interface"]==null){
			$ip=new networking();
			$ligne=$ip->GetNicInfos($this->eth);
			$this->ROUTES=$ligne["ROUTES"];
			if(($ligne["BOOTPROTO"]=='dhcp') OR ($ligne["BOOTPROTO"]==null)){$this->dhcp=1;}
			$ligne["enabled"]=1;
		
		}else{
			$this->dhcp=$ligne["dhcp"];
			writelogs("Routes \"{$ligne["routes"]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->ROUTES=unserialize($ligne["routes"]);
		}
		$this->eth=$ligne["Interface"];
		$this->NETMASK=$ligne["NETMASK"];
		$this->GATEWAY=$ligne["GATEWAY"];
		$this->BROADCAST=$ligne["BROADCAST"];
		$this->IPADDR=$ligne["IPADDR"];
		$this->DNS1=$ligne["DNS1"];
		$this->DNS2=$ligne["DNS2"];
		$this->NETWORK=$ligne["NETWORK"];
		$this->enabled=$ligne["enabled"];
		

	}
	
	private function CheckUnconfigured(){
		$q=new mysql();
		$sql="SELECT COUNT(Interface) as tcount FROM `nics` WHERE enabled=1";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["tcount"]>0){$this->unconfigured=false;}
	}
	
	public function SaveNic(){
		$routes=addslashes(serialize($this->ROUTES));
		
		$sql_add="
		INSERT INTO `nics` (`Interface`,`IPADDR`,`NETMASK`,`GATEWAY`,`BROADCAST`,`DNS1`,`DNS2`,`dhcp`,`ROUTES`,`NETWORK`,`enabled`)
		VALUES('$this->eth','$this->IPADDR','$this->NETMASK','$this->GATEWAY','$this->BROADCAST','$this->DNS_1',
		'$this->DNS_2','$this->dhcp','$routes','$this->NETWORK',1);
		";
	
		$sql_upd="UPDATE `nics` SET 
		`IPADDR`='$this->IPADDR',
		`NETMASK`='$this->NETMASK',
		`GATEWAY`='$this->GATEWAY',
		`BROADCAST`='$this->BROADCAST',
		`NETWORK`='$this->NETWORK',
		`DNS1`='$this->DNS1',
		`DNS2`='$this->DNS2',
		`dhcp`='$this->dhcp',
		`enabled`='$this->enabled',
		`ROUTES`='$routes'
		 WHERE `Interface`='$this->eth'";
		
		writelogs("save nic infos : $this->eth;$this->IPADDR;$this->NETMASK;$this->GATEWAY;$this->dhcp;$this->BROADCAST",__FUNCTION__,__FILE__);
		
		$q=new mysql();
		$q->check_networks_tables();
		$sql="SELECT Interface FROM `nics` WHERE `Interface`='$this->eth'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$sql=$sql_add;
		if($ligne["Interface"]<>null){$sql=	$sql_upd;}
		
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n\nsql command:\n\n$sql\n\n";return false;}
		return true;
		
		
	}
	
	private function root_build_redhat_remove_scripts($dir){
		if(!is_dir($dir)){echo "Starting......: IP mode RedHat $dir no such directory\n";return;}
		$unix=new unix();
		$ifconfig=$unix->find_program("ifconfig");
		
		foreach (glob("$dir/ifcfg-*") as $filename) {
			$fileconf=basename($filename);
			if($fileconf=="ifcfg-lo"){continue;}
			if(preg_match("#ifcfg-(.*)#",$fileconf,$re)){$nic=$re[1];}
			if($GLOBALS["VERBOSE"]){echo "Starting......: IP mode RedHat;$fileconf\n";}
			if($GLOBALS["VERBOSE"]){echo "Starting......: $ifconfig $nic down\n";}
			system("$ifconfig $nic down");
			echo "Starting......: Network remove {$re[1]}\n";
			@unlink($filename);
			if(is_file("/etc/sysconfig/networking/devices/$fileconf")){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Delete /etc/sysconfig/networking/devices/$fileconf\n";}
				@unlink("/etc/sysconfig/networking/devices/$fileconf");
			}
			if(is_file("/etc/sysconfig/networking/profiles/default/$fileconf")){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Delete /etc/sysconfig/networking/profiles/default/$fileconf\n";}
				@unlink("/etc/sysconfig/networking/profiles/default/$fileconf");
			}
			
			if(is_file("/etc/sysconfig/network/scripts/ifup-$fileconf")){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Delete /etc/sysconfig/network/scripts/ifup-$fileconf\n";}
				@unlink("/etc/sysconfig/network/scripts/ifup-$fileconf");	
			}
				
		}		
		
	}
	
	private function root_build_redhat_ifcfg($eth,$datas){
		if(is_dir("/etc/sysconfig/network")){
			echo "Starting......: IP mode RedHat $eth writing /etc/sysconfig/network/ifcfg-$eth\n";
			@file_put_contents("/etc/sysconfig/network/ifcfg-$eth",$datas);
		}
		
		if(is_dir("/etc/sysconfig/network-scripts")){
			echo "Starting......: IP mode RedHat $eth writing /etc/sysconfig/network-scripts/ifcfg-$eth\n";
			@file_put_contents("/etc/sysconfig/network-scripts/ifcfg-$eth",$datas);
		}
		
		if(is_dir("/etc/sysconfig/networking/profiles/default")){
			echo "Starting......: IP mode RedHat $eth writing profiles/default/ifcfg-$eth\n";
			@file_put_contents("/etc/sysconfig/networking/profiles/default/ifcfg-$eth",$datas);
		}		
		if(is_dir("/etc/sysconfig/networking/devices")){
			echo "Starting......: IP mode RedHat $eth writing devices/ifcfg-$eth\n";
			@file_put_contents("/etc/sysconfig/networking/devices/ifcfg-$eth",$datas);
		}			
		
	}
	
	
	public function root_build_redhat_config(){
		$sock=new sockets();
		$NetWorkBroadCastAsIpAddr=$sock->GET_INFO("NetWorkBroadCastAsIpAddr");
		
		$unix=new unix();
		$ifconfig=$unix->find_program("ifconfig");
		$sql="SELECT * FROM `nics` WHERE enabled=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "Starting......: Mysql error : $q->mysql_error\n";return;}
		$count=mysql_num_rows($results);
		if($count==0){echo "Starting......: Network unconfigured\n";return;}
		$ROUTESCFG=array();
		$this->root_build_redhat_remove_scripts("/etc/sysconfig/network-scripts");
		$this->root_build_redhat_remove_scripts("/etc/sysconfig/network");
		

		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$eth=$ligne["Interface"];
			if($eth==null){
				echo "Starting......: no nic name !! \"{$ligne["IPADDR"]}\"\n";
				continue;
			}
			echo "Starting......: $eth \"{$ligne["IPADDR"]}\"\n";
			$dns=array();
			$array=array();
			if(trim($ligne["GATEWAY"])==null){$ligne["GATEWAY"]="0.0.0.0";}
					
			if($ligne["Bridged"]==1){
				if($GLOBALS["VERBOSE"]){echo "Starting......: $eth bridged to {$ligne["BridgedTo"]}\n";}	
				$array[]="DEVICE=$eth";
				$array[]="STARTMODE='auto'";
				$array[]="ONBOOT=yes";
				$array[]="BRIDGE={$ligne["BridgedTo"]}";
				$array[]="NM_CONTROLLED=no";
				$array[]="IPV6INIT=no";
				$this->root_build_redhat_ifcfg($eth,@implode("\n",$array));
				continue;		
			}	
			
			$array[]="DEVICE=$eth";
			$array[]="STARTMODE='auto'";
			$array[]="IPADDR={$ligne["IPADDR"]}";
			$array[]="NETMASK={$ligne["NETMASK"]}";
			if($NetWorkBroadCastAsIpAddr==1){$ligne["BROADCAST"]=$ligne["IPADDR"];}
			if($ligne["BROADCAST"]<>"0.0.0.0"){
				$array[]="BROADCAST={$ligne["BROADCAST"]}";
			}
			
			if($ligne["GATEWAY"]<>"0.0.0.0"){
				$array[]="GATEWAY={$ligne["GATEWAY"]}";
			}	

			
			
			if($ligne["IsBridge"]==1){
				$unix=new unix();
				$php5=$unix->LOCATE_PHP5_BIN();
				$chmod=$unix->find_program("chmod");
				if($GLOBALS["VERBOSE"]){echo "Starting......: $eth is a bridge\n";}	
				$array[]="BOOTPROTO=static";
				$array[]="NM_CONTROLLED=no";
				$array[]="DELAY=5";
				$array[]="TYPE=Bridge";
				$array[]="MTU=1500";
				$array[]="BRIDGE_PORTS='{$ligne["BRIDGE_PORTS"]}'";
				$array[]="BRIDGE='yes'";
				$array[]="BRIDGE_AGEINGTIME='300'";
				$array[]="BRIDGE_FORWARDDELAY='0'";
				$array[]="BRIDGE_HELLOTIME='2'";
				$array[]="BRIDGE_MAXAGE='20'";
				$array[]="BRIDGE_PATHCOSTS='19'";
				$array[]="BRIDGE_PORTPRIORITIES=";
				$array[]="BRIDGE_PRIORITY=";
				$array[]="BRIDGE_STP='on'";
				$ROUTESCFG["-"][]=array("GW"=>$ligne["GATEWAY"],"MASK"=>"-","IP"=>"default");
				
				//$array[]="PRE_UP_SCRIPT=\"ifup-$eth\"";
				$PRE_UP_SCRIPT[]="#! /bin/bash";
				$PRE_UP_SCRIPT[]="$php5 /usr/share/artica-postfix/exec.vservers.php --checkbridge $eth {$ligne["BRIDGE_PORTS"]}\n";
				if(is_dir("/etc/sysconfig/network/scripts")){
					@file_put_contents("/etc/sysconfig/network/scripts/ifup-$eth",@implode("\n",$PRE_UP_SCRIPT));
					shell_exec("$chmod 777 /etc/sysconfig/network/scripts/ifup-$eth >/dev/null 2>&1");
				}
				if(is_dir("/etc/sysconfig/network-scripts")){
					@file_put_contents("/etc/sysconfig/network-scripts/ifup-$eth",@implode("\n",$PRE_UP_SCRIPT));
					shell_exec("$chmod 777 /etc/sysconfig/network-scripts/ifup-$eth >/dev/null 2>&1");
				}
			}
			
			
			$iptble=explode(".",$ligne["IPADDR"]);
			$firstip="{$iptble[0]}.{$iptble[1]}.{$iptble[2]}.0";
			$ROUTESCFG[$eth][]=array("GW"=>$ligne["GATEWAY"],"MASK"=>$ligne["NETMASK"],"IP"=>$firstip);	
			$routes=unserialize($ligne["ROUTES"]);
			if(count($routes)>0){
				while (list ($ip, $ip_array) = each ($routes) ){
					$ROUTESCFG[$eth][]=array("GW"=>$ip_array["GATEWAY"],"MASK"=>$ip_array["NETMASK"],"IP"=>$ip);
				}	
			}			

			$array[]="ONBOOT=yes";
			
			if($ligne["DNS1"]<>null){$array[]="DNS1={$ligne["DNS1"]}";}
			if($ligne["DNS2"]<>null){$array[]="DNS2={$ligne["DNS2"]}";}			
			
			if($GLOBALS["VERBOSE"]){echo "Starting......: {$ligne["IPADDR"]}/{$ligne["NETMASK"]}:=> ifcfg-$eth\n";}	
			$this->root_build_redhat_ifcfg($eth,@implode("\n",$array));		
		}
		
		
		// Fin des cartes standard....
		if(count($ROUTESCFG)>0){
			while (list ($ETHROUTE, $ROUTESGLOBALARRAY) = each ($ROUTESCFG) ){
				if(trim($ETHROUTE)==null){continue;}
				while (list ($INDEX, $ROUTESARRAY) = each ($ROUTESGLOBALARRAY) ){
					if($ROUTESARRAY["IP"]==null){$ROUTESARRAY["IP"]="default";}
					if($ROUTESARRAY["GW"]==null){$ROUTESARRAY["GW"]="-";}
					if($ROUTESARRAY["MASK"]==null){$ROUTESARRAY["MASK"]="-";}
					echo "Starting......: $ETHROUTE route {$ROUTESARRAY["IP"]}/{$ROUTESARRAY["MASK"]} via {$ROUTESARRAY["GW"]}\n";
					$routesDatas[]="{$ROUTESARRAY["IP"]}  {$ROUTESARRAY["GW"]} {$ROUTESARRAY["MASK"]}  $ETHROUTE";
				}
			}
			
			if(is_dir("/etc/sysconfig/network")){
				@file_put_contents("/etc/sysconfig/network/routes",@implode("\n",$routesDatas));
			}
		}
		
		
		
		
		$NoGatewayForVirtualNetWork=$sock->GET_INFO("NoGatewayForVirtualNetWork");	
		if(!is_numeric($NoGatewayForVirtualNetWork)){$NoGatewayForVirtualNetWork=0;}
		$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
				
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			
			$array=array();
			$arr=explode(".",$ligne["ipaddr"]);
			$arr[3]="255";
			$brd=implode(".",$arr);			
			$eth="{$ligne["nic"]}:{$ligne["ID"]}";
			echo "Starting......: $eth \"{$ligne["ipaddr"]}\"\n";
			if(trim($ligne["GATEWAY"])==null){$ligne["GATEWAY"]="0.0.0.0";}
			$array[]="DEVICE=$eth";
			$array[]="STARTMODE='auto'";
			$array[]="IPADDR={$ligne["ipaddr"]}";
			$array[]="NETMASK={$ligne["netmask"]}";
			if($NetWorkBroadCastAsIpAddr==1){$brd=$ligne["ipaddr"];}
			$array[]="BROADCAST=$brd";
			if($ligne["GATEWAY"]<>"0.0.0.0"){
				if($NoGatewayForVirtualNetWork==0){$array[]="GATEWAY={$ligne["gateway"]}";}
			}
			$array[]="ONBOOT=yes";
			$array[]="USERCTL=yes";	
			
			if($GLOBALS["VERBOSE"]){echo "Starting......: {$ligne["ipaddr"]}/{$ligne["netmask"]}:=> ifcfg-$eth\n";}	
			$this->root_build_redhat_ifcfg($eth,@implode("\n",$array));			
		}

		$sql="SELECT * FROM nics_vlan ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	

		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$array=array();
			$arr=explode(".",$ligne["ipaddr"]);
			$arr[3]="255";
			$brd=implode(".",$arr);			
			$eth="vlan{$ligne["ID"]}";
			if(trim($ligne["GATEWAY"])==null){$ligne["GATEWAY"]="0.0.0.0";}
			echo "Starting......: VLAN $eth {$ligne["ipaddr"]}/{$ligne["netmask"]} gateway {$ligne["gateway"]} ({$ligne["org"]})\n";

			echo "Starting......: $eth \"{$ligne["ipaddr"]}\"\n";
			$array[]="DEVICE=$eth";
			$array[]="STARTMODE='auto'";
			$array[]="IPADDR={$ligne["ipaddr"]}";
			$array[]="NETMASK={$ligne["netmask"]}";
			if($NetWorkBroadCastAsIpAddr==1){$brd=$ligne["ipaddr"];}
			$array[]="BROADCAST=$brd";
			if($ligne["gateway"]<>"0.0.0.0"){
				$array[]="GATEWAY={$ligne["gateway"]}";
			}
			
			$array[]="ONBOOT=yes";
			$array[]="USERCTL=yes";	
			$array[]="VLAN_NAME_TYPE=VLAN_PLUS_VID_NO_PAD";
			$array[]="PHYSDEV={$ligne["nic"]}";
			if($GLOBALS["VERBOSE"]){echo "Starting......: {$ligne["ipaddr"]}/{$ligne["netmask"]}:=> ifcfg-$eth\n";}	
			$this->root_build_redhat_ifcfg($eth,@implode("\n",$array));			
			
		}			
	
	
}
	
	
	public function root_build_debian_config(){
		$sock=new sockets();
		$NetWorkBroadCastAsIpAddr=$sock->GET_INFO("NetWorkBroadCastAsIpAddr");
		$sql="SELECT * FROM `nics` WHERE enabled=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "Starting......: Mysql error : $q->mysql_error\n";return;}
		$count=mysql_num_rows($results);
		if($count==0){echo "Starting......: Network unconfigured\n";return;}
		
		$f[]="auto lo";
		$f[]="iface lo inet loopback";
		$f[]="";
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			
			$eth=$ligne["Interface"];
			if($count==0){echo "Starting......: $eth \"{$ligne["IPADDR"]}\"\n";return;}
			$dns=array();
			$f[]="";
			
			
			
			$f[]="auto $eth ".$this->auto_eth_virts($eth);		
			if($ligne["Bridged"]==1){$f[]="iface $eth inet manual\n";continue;}	
			if($ligne["dhcp"]==1){$f[]="iface $eth inet dhcp";continue;}
			$f[]="iface $eth inet static";
			$f[]="\taddress {$ligne["IPADDR"]}";
			$f[]="\tnetmask {$ligne["NETMASK"]}";
			if($NetWorkBroadCastAsIpAddr==1){$ligne["BROADCAST"]=$ligne["IPADDR"];}
			if($ligne["NETWORK"]<>null){$f[]="\tnetwork {$ligne["NETWORK"]}";}
			if(trim($ligne["GATEWAY"])==null){$ligne["GATEWAY"]="0.0.0.0";}
			
			if($ligne["BROADCAST"]<>null){
				if($ligne["GATEWAY"]<>"0.0.0.0"){
					$f[]="\tbroadcast {$ligne["BROADCAST"]}";
				}
			}
							
			
			if($ligne["GATEWAY"]<>null){
				if($ligne["GATEWAY"]<>"0.0.0.0"){
					$f[]="\tgateway {$ligne["GATEWAY"]}";
				}
			}			
			
			if($ligne["DNS1"]<>null){$dns[]=$ligne["DNS1"];}
			if($ligne["DNS2"]<>null){$dns[]=$ligne["DNS2"];}
			$unix=new unix();
			
			if($ligne["IsBridge"]==1){
				$php5=$unix->LOCATE_PHP5_BIN();
				$f[]="\tbridge_ports {$ligne["BRIDGE_PORTS"]}";
				$f[]="\tbridge_stp off";
				$f[]="\tbridge_maxwait 5";
  				$f[]="\tpre-up $php5 /usr/share/artica-postfix/exec.vservers.php --checkbridge $eth {$ligne["BRIDGE_PORTS"]}";
  				$f[]="\tpre-up ifconfig {$ligne["BRIDGE_PORTS"]} up";
  							
			}
					

			if(count($dns)>0){
				$f[]="\t# dns -* options are implemented by the resolvconf package, if installed";
				$f[]="\tdns-nameservers ". @implode(" ",$dns);	
			}

			$routes=unserialize($ligne["ROUTES"]);
			if(is_array($routes)){
				if(count($routes)>0){
	         		while (list ($ip, $ip_array) = each ($routes) ){
	         			
		         		if($ip=="default"){
		         			$f[]="\tup route add default gw {$ip_array["GATEWAY"]}";
		         			$f[]="\tdown route del default gw {$ip_array["GATEWAY"]}";
		         			continue;
		         		}
		         			
		         		if($ip_array["GATEWAY"]==null){
		         			if($ip_array["DEV"]<>null){
		         				$f[]="\tup route add -net $ip netmask {$ip_array["NETMASK"]} dev {$ip_array["DEV"]}";
		         				$f[]="\tdown route del -net $ip netmask {$ip_array["NETMASK"]} dev {$ip_array["DEV"]}";
		         				continue;
		         			}
		         		}
			    			
	   					$f[]="\tup route add -net $ip netmask {$ip_array["NETMASK"]} gw {$ip_array["GATEWAY"]}";
	   					$f[]="\tdown route del -net $ip netmask {$ip_array["NETMASK"]} gw {$ip_array["GATEWAY"]}";
	   				}	
	        	}
			}

	        $f[]="";
	        $f[]="";
		
		}
		
		$NoGatewayForVirtualNetWork=$sock->GET_INFO("NoGatewayForVirtualNetWork");	
		if(!is_numeric($NoGatewayForVirtualNetWork)){$NoGatewayForVirtualNetWork=0;}		
		$sql="SELECT * FROM nics_virtuals ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		$count=mysql_num_rows($results);
		if($count>0){
			$f[]="";
			$f[]="#Virtual interfaces...\n";
		}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$arr=explode(".",$ligne["ipaddr"]);
			$arr[3]="255";
			$brd=implode(".",$arr);			
			$eth="{$ligne["nic"]}:{$ligne["ID"]}";
			if($NetWorkBroadCastAsIpAddr==1){$brd=$ligne["ipaddr"];}
			echo "Starting......: Virtual $eth {$ligne["ipaddr"]}/{$ligne["netmask"]} gateway {$ligne["gateway"]} ({$ligne["org"]})\n";
			$f[]="";
			$f[]="iface $eth inet static";
			$f[]="\taddress {$ligne["ipaddr"]}";
			$f[]="\tnetmask {$ligne["netmask"]}";
			if($NoGatewayForVirtualNetWork==0){
				if($ligne["gateway"]<>"0.0.0.0"){
					$f[]="\tgateway {$ligne["gateway"]}";
				}
			}
			$f[]="\tbroadcast $brd";
    		$f[]="";
	        $f[]="";				
		}
		
		$sql="SELECT * FROM nics_vlan ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		$count=mysql_num_rows($results);
		if($count>0){
			$f[]="";
			$f[]="#VLANs interfaces...\n";
		}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$arr=explode(".",$ligne["ipaddr"]);
			$arr[3]="255";
			$brd=implode(".",$arr);			
			$eth="vlan{$ligne["ID"]}";
			echo "Starting......: VLAN $eth {$ligne["ipaddr"]}/{$ligne["netmask"]} gateway {$ligne["gateway"]} ({$ligne["org"]})\n";
			$f[]="";
			$f[]="auto $eth";
			$f[]="iface $eth inet static";
			$f[]="\taddress {$ligne["ipaddr"]}";
			$f[]="\tnetmask {$ligne["netmask"]}";
			if($NetWorkBroadCastAsIpAddr==1){$brd=$ligne["ipaddr"];}
			$f[]="\tbroadcast $brd";
			if($ligne["gateway"]<>"0.0.0.0"){
				$f[]="\tgateway {$ligne["gateway"]}";
			}
			$f[]="\tmtu 1500";
			$f[]="\tvlan_raw_device {$ligne["nic"]}";
    		$f[]="";
	        $f[]="";			
			
		}		
	
	
	return @implode("\n",$f);
	
	}
	
	public function CreateLXCBridge($interface,$BridgeName){
		if(!isset($interface)){return false;}
		if($interface==null){return false;}
		$nic=new system_nic($interface);
		$routes=addslashes(serialize($nic->ROUTES));
		$q=new mysql();
		
		$sql="DELETE FROM `nics` WHERE `Interface`='$BridgeName'";
		$q->QUERY_SQL($sql,"artica_backup");
		
		
		$sql="
		INSERT IGNORE INTO `nics` (`Interface`,`IPADDR`,`NETMASK`,`GATEWAY`,`BROADCAST`,`DNS1`,`DNS2`,`dhcp`,`ROUTES`,
		`NETWORK`,`enabled`,`BRIDGE_PORTS`,`IsBridge`)
		VALUES('$BridgeName','$nic->IPADDR','$nic->NETMASK','$nic->GATEWAY','$nic->BROADCAST','$nic->DNS_1',
		'$nic->DNS_2','$nic->dhcp','$routes','$nic->NETWORK',1,'$interface',1);
		";
		
		if($GLOBALS["VERBOSE"]){echo $sql;}
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return false;}
		
		$sql="UPDATE `nics` SET Bridged=1,BridgedTo='$BridgeName' WHERE `Interface`='$interface'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return false;}	
		return true;
	}
	
	public function RemoveLXCBridge($interface,$BridgeName){
		$q=new mysql();
		$sql="DELETE FROM `nics` WHERE `Interface`='$BridgeName'";
		$q->QUERY_SQL($sql,"artica_backup");	
		
		$sql="UPDATE `nics` SET Bridged=0,BridgedTo='' WHERE `Interface`='$interface'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return false;}	
		return true;		
		
	}
	
	
	private function auto_eth_virts($eth){
		$sql="SELECT ID FROM nics_virtuals WHERE `nic`='$eth' ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$f[]="$eth:{$ligne["ID"]}";
		}	
		return @implode(" ",$f);
	}
	
	
	
	
	function ConstructVirtsIP(){
		$sock=new sockets();
		$sock->getFrameWork("network.php?crossroads-restart=yes");
		$sock->getFrameWork("cmd.php?virtuals-ip-reconfigure=yes");
	}

}
?>