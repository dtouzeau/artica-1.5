<?php
if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}else{$GLOBALS["AS_ROOT"]=false;}}
class networking{
	var $array_conf;
	var $array_TCP;
	var $array_local_aliases;
	var $ldap_error;
	var $arrayNameServers;
	var $hostname;
	
	var $tcp_addr="0.0.0.0";
	var $wireless=false;
	var $netmask="0.0.0.0";
	var $gateway="0.0.0.0";
	var $linkup=false;
	
	function networking(){
			include_once(dirname(__FILE__) . '/class.tcpip.inc');
			if(isset($GLOBALS["__CLASS-networking"])){
			foreach (get_object_vars($GLOBALS["__CLASS-networking"]) as $key => $value){$this->$key = $value;}
			if($GLOBALS["DEBUG_MEM"]){events("Memory: class.system.network.inc -> networking class() -> ".round(((memory_get_usage()/1024)/1000),2) ." line:".__LINE__);}
			return null;
			}		
		
		
		$this->LoadNameServers();
		$this->LoadIPs();
		
		$GLOBALS["__CLASS-networking"]=$this;
		$trace=debug_backtrace();
		$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";
		writelogs("Loading success $called",__CLASS__.'/'.__FUNCTION__,__FILE__);
	}
	
  public function Local_interfaces($filter=false) {
    $results = array();
    if ($this->rfts('/proc/net/dev', $bufr)) {
      $bufe = explode("\n", $bufr);
      foreach($bufe as $buf) {
        if (preg_match('/:/', $buf)) {
          list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
          $stats = preg_split('/\s+/', trim($stats_list));
          if($filter){
          	if(preg_match("#^veth#",trim($dev_name))){continue;}
          	if(preg_match("#^sit#",trim($dev_name))){continue;}
          	if(preg_match("#^lo#",trim($dev_name))){continue;}
          	if(preg_match("#^br[0-9]+#",trim($dev_name))){continue;}
          	
          }
          $results[trim($dev_name)] = trim($dev_name);
        }
      }
    }
    
    return $results;
  }	
  
	 private function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true) {
	  $error = "";
	  $strFile = "";
	  $intCurLine = 1;
	  if (is_file($strFileName)) {
	    if ($fd = fopen($strFileName, 'r')) {
	      while (!feof($fd)) {
	        $strFile.= fgets($fd, $intBytes);
	        if ($intLines <= $intCurLine && $intLines != 0) {
	          break;
	        } else {
	          $intCurLine++;
	        }
	      }
	      fclose($fd);
	      $strRet = $strFile;
	    } else {
	      if ($booErrorRep) {
	        writelogs('fopen(' . $strFileName . ') file can not read',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	      }
	      return false;
	    }
	  } else {
	    if ($booErrorRep) {
	      writelogs('file_exists(' . $strFileName . ') the file does not exist on your machine',__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	    }
	    return false;
	  }
	  return true;
	}  
	

	function ALL_IPS_GET_ARRAY(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		if(is_array($GLOBALS["ALL_IPS_GET_ARRAY"])){return $GLOBALS["ALL_IPS_GET_ARRAY"];}
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?ifconfig-all-ips=yes")));
		
		if(is_array($array)){
			while (list ($ip, $line) = each ($array) ){
				if(trim($line)==null){continue;}
				$ARRAY_IP[trim($line)]=trim($line);
			}
		}
		$GLOBALS["ALL_IPS_GET_ARRAY"]=$ARRAY_IP;
		return $ARRAY_IP;
	}
	
	
	function LoadIPs(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		$users=new usersMenus();
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$this->array_TCP=$users->TCP_ARRAY;
		if($GLOBALS["AS_ROOT"]){
			if(class_exists('unix')){
				$unix=new unix();
				$this->hostname=$unix->FULL_HOSTNAME();
			}
		}else{
			$this->hostname=trim($sock->getFrameWork('cmd.php?full-hostname=yes'));
		}
		
	}
	
	function SaveResolvconf($targetFile){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		if($targetFile=='/etc/resolv.conf'){return null;}
		writelogs("Send order to copy \"$targetFile\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		writelogs("-> cmd.php?copyresolv='.$targetFile",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sock->getFrameWork('cmd.php?copyresolv='.$targetFile);
		
	}
	
	public function checkIP($ip){
		$cIP = ip2long($ip);
		$fIP = long2ip($cIP);
		if($fIP=="0.0.0.0"){return false;}
		return true;
		}	
	
	function LoadNameServers(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$dns=$sock->getFrameWork('cmd.php?dnslist=yes');
		if($GLOBALS["VERBOSE"]){echo "cmd.php?dnslist=yes -> $dns in ".__CLASS__.'/'.__FUNCTION__. " Line:".__LINE__."\n";}
		$dnslist=explode(";",$dns);
		$ip=new IP();
		if(is_array($dnslist)){
			while (list ($num, $val) = each ($dnslist) ){
				$val=trim(strtolower($val));
				if($val<>null){
					//Check if is a valid tcp/ip address.
					if($ip->isValid($val)){
						$arr[$val]=trim($val);
					}
					
				}}
			}
			
			if(!isset($arr)){return;}			
		    if(is_array($arr)){
				while (list ($num, $val) = each ($arr) ){
					if($ip->isValid($val)){
					$this->arrayNameServers[]=$val;
					}
				}
		   	}
		}
	
	
	function ifconfig($nic){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$nicinfos=$sock->getFrameWork("cmd.php?nicstatus=$nic");
		
		$tbl=explode(";",$nicinfos);
		$this->tcp_addr=$tbl[0];
		$this->mac_addr=$tbl[1];
		$this->netmask=$tbl[2];
		$this->gateway=$tbl[4];
		if(trim($tbl[5])=='yes'){$this->wireless=true;}else{$this->wireless=false;}
		if(trim($tbl[6])=='yes'){$this->linkup=true;}else{$this->linkup=false;}		
		
		}
		
		function BuildResolvConf(){
			include_once(dirname(__FILE__) . '/class.sockets.inc');
			$users=new usersMenus();
			reset($this->arrayNameServers);
			$resolv=array();
			if($users->BIND9_INSTALLED){
				writelogs("bind9 is installed...",__CLASS__.'/'.__FUNCTION__,__FILE__);
				include_once(dirname(__FILE)."/class.bind9.inc");
				$bind=new bind9();
				while (list ($index, $ligne) = each ($this->arrayNameServers) ){
					$bind->AddNewForwarder($ligne);
				}
			}			
			
			reset($this->arrayNameServers);
			$ip=new IP();
			while (list ($index, $ligne) = each ($this->arrayNameServers) ){
				$ligne=trim($ligne);
				if(!$ip->isValid($ligne)){continue;}
				$newar[$ligne]=$ligne;
				
				writelogs("Compiling $ligne has name server",__CLASS__.'/'.__FUNCTION__,__FILE__);
				
			}
			
			if($users->BIND9_INSTALLED){
				writelogs("BIND9_INSTALLED -> 127.0.0.1",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$newar["127.0.0.1"]="127.0.0.1";
			}
			if($users->dnsmasq_installed){
				$newar["127.0.0.1"]="127.0.0.1";
			}
			if($users->POWER_DNS_INSTALLED){
				$newar["127.0.0.1"]="127.0.0.1";
				
			}
			while (list ($index, $ligne) = each ($newar) ){
				$resolv[]="nameserver $ligne";
			}
			
			@mkdir(dirname(__FILE__).'/conf');
			@file_put_contents(dirname(__FILE__).'/conf/resolv.conf',@implode("\n",$resolv));
			if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
			$this->SaveResolvconf(dirname(__FILE__).'/conf/resolv.conf');
			
			
			
			
			if($users->dnsmasq_installed){
				include_once(dirname(__FILE__) . '/class.dnsmasq.inc');
				$dnsmasq=new dnsmasq();
				if($dnsmasq->main_array["no-resolv"]=='justkey'){
					$dnsfile="/etc/resolv.conf";
					}else{
						$dnsfile=$dnsmasq->main_array["resolv-file"];
					}
					if($dnsfile==null){$dnsfile="/etc/resolv.conf";}
					$this->SaveResolvconf($dnsfile);
				}
			}
		
	function nameserver_add($name){
		$ip=new IP();
		if(!$ip->isValid($name)){
			writelogs("$name is not a valid ip address",__CLASS__.'/'.__FUNCTION__,__FILE__);
			echo "No a valid ip address!\n";
			return;
		}
		reset($this->arrayNameServers);
		while (list ($index, $ligne) = each ($this->arrayNameServers) ){
			if(trim(strtolower($name))==trim(strtolower($ligne))){
				writelogs("$name already exists in dns list, aborting",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return;
			}
		}
		reset($this->arrayNameServers);
		$this->arrayNameServers[]=$name;
		$this->BuildResolvConf();
		}
		
	function nameserver_delete($name){
		$users=new usersMenus();
		writelogs("Delete $name ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		while (list ($index, $ligne) = each ($this->arrayNameServers) ){
			if(trim(strtolower($name))==trim(strtolower($ligne))){
				writelogs("remove entry $index",__CLASS__.'/'.__FUNCTION__,__FILE__);		
				unset($this->arrayNameServers[$index]);
				break;
			}
		}
		reset($this->arrayNameServers);
		if($users->BIND9_INSTALLED){
				include_once(dirname(__FILE__)."/class.bind9.inc");
				writelogs("BIND9 is installed update it for delete $name",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$bind=new bind9();
				$bind->DeleteForwarder($name);
				
		}
		$this->BuildResolvConf();
	}
		
	
	
	function GetNicInfos($nic){
			include_once(dirname(__FILE__) . '/class.sockets.inc');
			if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
			$datas=$sock->getFrameWork("cmd.php?nic-infos=$nic");
			
			$tbl=explode("\n",$datas);
			if(!is_array($tbl)){return array();}
			$array=array();
			while (list ($index, $ligne) = each ($tbl) ){
				$ligne=str_replace("'","",$ligne);
			    if(preg_match("#route add -net\s+([0-9\.]+)\s+netmask\s+([0-9\.]+)\s+gw\s+([0-9\.]+)#",$ligne,$re)){
			    	$array["ROUTES"][$re[1]]=array("NETMASK"=>$re[2],"GATEWAY"=>$re[3]);
			    	continue;
			    }
			    if(preg_match("#route add -net\s+([0-9\.]+)\s+netmask\s+([0-9\.]+)\s+dev\s+(.+)#",$ligne,$re)){
			    	$array["ROUTES"][$re[1]]=array("NETMASK"=>$re[2],"DEV"=>$re[3]);
			    	continue;
			    	
			    }
			    
			    if(preg_match("#route add default gw ([0-9\.]+)#",$ligne,$re)){
			    	$array["ROUTES"]["default"]=array("GATEWAY"=>$re[1]);
			    	continue;
			    }
			    
			    
				if(preg_match('#([a-zA-Z0-9\-\_]+)=(.+)#',$ligne,$reg)){
					$array[$reg[1]]=$reg[2];
				}
				
			}
			
		return $this->GetInfosReturnDefault($array,$nic);
		}
		
	function default_network(){
		if($this->array_TCP){
		while (list ($nic, $ip) = each ($this->array_TCP) ){
			if(preg_match("#^vmnet#",$nic)){continue;}
			if($ip==null){continue;}
			if($ip=="0.0.0.0"){continue;}
			return $ip;
		}}
		
	}
	
	function GetInfosReturnDefault($array,$nic=null){
		if(count($this->arrayNameServers)==0){$this->LoadNameServers();}
		if($nic<>null){
			include_once(dirname(__FILE__) . '/class.sockets.inc');
			if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
			$nicinfos=$sock->getFrameWork("cmd.php?nicstatus=$nic");
			$tbl=explode(";",$nicinfos);
			$default_addr=$tbl[0];
			$default_netmask=$tbl[2];
			$default_gateway=$tbl[4];
			$bradcast_default=$tbl[3];
		}
				
		if(trim($default_addr)==null){$default_addr="0.0.0.0";}
		if(trim($default_netmask)==null){$default_netmask="0.0.0.0";}
		if(trim($default_gateway)==null){$default_gateway="0.0.0.0";}
		
		if($bradcast_default==null){
			if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$default_addr,$re)){
				$bradcast_default="{$re[1]}.{$re[2]}.{$re[3]}.255";
			}
		}
		
		if(!isset($array["ONBOOT"])){$array["ONBOOT"]=null;}
		if(!isset($array["METRIC"])){$array["METRIC"]=null;}
		if(!isset($array["NEEDHOSTNAME"])){$array["NEEDHOSTNAME"]=null;}
		if(!isset($array["DHCP_CLIENT"])){$array["DHCP_CLIENT"]=null;}
		if(!isset($array["PEERDNS"])){$array["PEERDNS"]=null;}
		if(!isset($array["PEERYP"])){$array["PEERYP"]=null;}
		if(!isset($array["PEERNTPD"])){$array["PEERNTPD"]=null;}
		if(!isset($array["GATEWAY"])){$array["GATEWAY"]=null;}
		if(!isset($array["BROADCAST"])){$array["BROADCAST"]=null;}
		
		
		
		if($array["ONBOOT"]==null){$array["ONBOOT"]="yes";}
		if($array["METRIC"]==null){$array["METRIC"]="10";}
		if($array["NEEDHOSTNAME"]==null){$array["NEEDHOSTNAME"]="yes";}	
		if($array["DHCP_CLIENT"]==null){$array["DHCP_CLIENT"]="dhclient";}	
		if($array["PEERDNS"]==null){$array["PEERDNS"]="yes";}	
		if($array["PEERYP"]==null){$array["PEERYP"]="no";}	
		if($array["PEERNTPD"]==null){$array["PEERNTPD"]="no";}		
		if($array["GATEWAY"]==null){$array["GATEWAY"]=$default_gateway;}
		if($array["IPADDR"]==null){$array["IPADDR"]=$default_addr;}
		if($array["NETMASK"]==null){$array["NETMASK"]=$default_netmask;}
		if($array["BOOTPROTO"]==null){$array["BOOTPROTO"]="static";}
		if($array["BROADCAST"]==null){$array["BROADCAST"]=$bradcast_default;}
		
		if(preg_match("#(.+?)\/([0-9]+)#",$array["IPADDR"],$re)){
			$array["IPADDR"]=$re[1];
			if($re[2]==24){$array["NETMASK"]="255.255.255.0";}
		}
		if(isset($array["dns-nameservers"])){
			if($array["dns-nameservers"]<>null){
				$t=explode(" ",$array["dns-nameservers"]);
				$array["NAMESERVERS"]=$t;
			}else{
				$array["NAMESERVERS"]=$this->arrayNameServers;;
			}
		}
		return $array;		
		
	}
	
	
function route_shouldbe($ip_start,$mask=null){
	
	$ip=$ip_start;
	if(preg_match("#([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".0.0.0";
		$calc_ip_end=$re[1].".255.255.255";
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.0.0";
		$calc_ip_end=$re[1].".{$re[2]}.255.255";
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.0.0";
		$calc_ip_end=$re[1].".{$re[2]}.255.255";
	}	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}	
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$ip,$re)){
		$calc_ip=$re[1].".{$re[2]}.{$re[3]}.0";
		$calc_ip_end=$re[1].".{$re[2]}.{$re[3]}.255";
	}		

	
	
	if($mask<>null){
		if(preg_match("#([0-9]+)$#",$mask,$re)){
			$newmask=$re[1].".0.0.0";
		}
	
	if(preg_match("#([0-9]+)\.([0-9]+)$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.0.0";
	}
	
		if(preg_match("#([0-9]+)\.([0-9]+)\.$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.0.0";
	}	

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.{$re[3]}.0";
	}

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.{$re[3]}.0";
	}		
		

	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$#",$mask,$re)){
		$newmask=$re[1].".{$re[2]}.{$re[3]}.{$re[4]}";
	}		
		
	}
	
	$ip=new IP();
	
	if($newmask<>null){
		return $ip->maskTocdir($calc_ip, $newmask);
	}
	
	
	
	$cdir=$ip->ip2cidr($calc_ip,$calc_ip_end);
	$arr=$ip->parseCIDR($cdir);
	$rang=$arr[0];
	$netbit=$arr[1];
	$ipv=new ipv4($calc_ip,$netbit);
	return "<strong>$cdir {$ipv->address()} - {$ipv->netmask()}</strong>"; 	
	
}

	

	
	
	
	
	
}


class ifconfig{
	
	var $main_array=array();
	
	function ifconfig(){
		include_once(dirname(__FILE__) . '/class.sockets.inc');
		include_once(dirname(__FILE__) . '/class.ini.inc');
		if(isset($GLOBALS["CLASS_SOCKETS"])){$sock=$GLOBALS["CLASS_SOCKETS"];}else{$GLOBALS["CLASS_SOCKETS"]=new sockets();$sock=$GLOBALS["CLASS_SOCKETS"];}
		$ini=new Bs_IniHandler();
		$datas=$sock->getfile('netcardsinfo');
		$ini->loadString($datas);
		$this->main_array=$ini->_params;
		
		
	}
	
	
	
	
}

function VirtualNicInfosIPaddr($ID){
	if(!is_numeric($ID)){return;}
	$sql="SELECT * FROM nics_virtuals WHERE ID=$ID";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$eth="{$ligne["nic"]}:{$ligne["ID"]}";
	return array("IPADDR"=>$ligne["ipaddr"],"ETH"=>$eth);
	
}

class MACAddress {
    private $_macPrefix = '00-';
    private $_macORGPrefix = '00-50-56-';
    private $_macAddress;
    private $_macChars;
    public $separator="-";
    public $MAC1;
    public $MAC2;
    public $MAC3;
    public $MAC4;
    public $MAC5;
    public $MAC6;

     public function _generateMAC(){
     	$this->MAC1=str_replace("-",$this->_macPrefix,$this->_macPrefix);
        $this->_macChars = md5(uniqid(mt_rand(), TRUE));
        $this->MAC2=substr($this->_macChars, 0,2);
        $this->MAC3=substr($this->_macChars, 8,2);
        $this->MAC4=substr($this->_macChars, 12,2);
        $this->MAC5=substr($this->_macChars, 16,2);
        $this->MAC6=substr($this->_macChars, 20,2);
            
        
        $this->_macAddress = substr($this->_macChars, 0,2) . '-';
        $this->_macAddress .= substr($this->_macChars, 8,2) . '-';
        $this->_macAddress .= substr($this->_macChars, 12,2) . '-';
        $this->_macAddress .= substr($this->_macChars, 16,2) . '-';
        $this->_macAddress .= substr($this->_macChars, 20,2);
        return "$this->MAC1$this->MAC2$this->separator$this->MAC3$this->separator$this->MAC4$this->separator$this->MAC5$this->separator$this->MAC6";
     }

     public function _generateMAC2(){
     	$this->_macORGPrefix=str_replace("-",$this->separator,$this->_macORGPrefix);
        $this->_macChars = md5(uniqid(mt_rand(), TRUE));
        $this->MAC4=substr($this->_macChars, 0,2);
        $this->MAC5=substr($this->_macChars, 8,2);
        $this->MAC6=substr($this->_macChars, 12,2);
        $this->_macAddress = "$this->_macORGPrefix$this->MAC4$this->separator$this->MAC5$this->separator$this->MAC6";
        return $this->_macAddress;
     }
} 

class networkscanner{
	var $networklist=array();
	var $DefaultNetworkList=array();
	var $Networks_disabled=array();
	
	
	function networkscanner(){
		$sock=new sockets();
		$datas=$sock->GET_INFO('NetworkScannerMasks');
		$tbl=explode("\n",$datas);
		$disabled=$sock->GET_INFO('NetworkScannerMasksDisabled');
		
		
		while (list ($num, $maks) = each ($tbl) ){
		if(trim($maks)==null){continue;}
			$arr[trim($maks)]=trim($maks);
		}
		
	if(is_array($arr)){
			while (list ($num, $net) = each ($arr)){
				$this->networklist[]=$net;
			}
		}

	
	$tbl=explode("\n",$disabled);	
	if(is_array($tbl)){
		while (list ($num, $maks) = each ($tbl) ){
			if(trim($maks)==null){continue;}
			$this->Networks_disabled[$maks]=true;
		}
	}
		
		
		$this->builddefault();
		
	}
	
	function disable_net($net){
		$sock=new sockets();
		$disabled=$sock->GET_INFO('NetworkScannerMasksDisabled');
		$disabled=$disabled."\n".$net;
		$sock=new sockets();
		$sock->SaveConfigFile($disabled,"NetworkScannerMasksDisabled");
	}
	
	function enable_net($net){
		$sock=new sockets();
		$disabled=$sock->GET_INFO('NetworkScannerMasksDisabled');
		$tbl=explode("\n",$disabled);	
		if(is_array($tbl)){
			while (list ($num, $maks) = each ($tbl) ){
				if(trim($maks)==null){continue;}
				$Networks_disabled[$maks]=$maks;
			}
		}

		unset($Networks_disabled[$net]);
		if(is_array($Networks_disabled)){
			while (list ($num, $maks) = each ($Networks_disabled) ){
				if(trim($maks)==null){continue;}
				$conf=$conf.$maks."\n";
			}
		}
		
		$sock->SaveConfigFile($conf,"NetworkScannerMasksDisabled");
		
		
		
	}
	
	
	function save(){
		if(is_array($this->networklist)){
			reset($this->networklist);
			while (list ($num, $maks) = each ($this->networklist)){
				if(trim($maks)==null){continue;}
				$arr[trim($maks)]=trim($maks);
				}
			}
		
		if(is_array($arr)){
			
			while (list ($num, $net) = each ($arr)){
				$conf=$conf . "$net\n";
			}
		}
		echo $conf;
		$sock=new sockets();
		$sock->SaveConfigFile($conf,"NetworkScannerMasks");
		$sock->DeleteCache();
		
	}
	
	function builddefault(){
		
		$net=new networking();
		$cip=new IP();
		while (list ($num, $ip) = each ($net->array_TCP)){
			if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$ip,$re)){
				$ip_start="{$re[1]}.{$re[2]}.{$re[3]}.0";
				$ip_end="{$re[1]}.{$re[2]}.{$re[3]}.255";
				$cdir=$cip->ip2cidr($ip_start,$ip_end);
				if(trim($cdir)<>null){
					$this->DefaultNetworkList[trim($cdir)]=true;
					$this->networklist[]=$cdir;
				}
			}
			
		}
		
		
		
	}
	
	
		
	
}


?>