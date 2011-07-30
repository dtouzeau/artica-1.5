<?php



class dnsmasq{
	var $configuration_file_datas;
	var $main_array;
	var $array_address;
	var $array_interface;
	var $array_listenaddress;
	var $array_ldap_addresses=array();
	var $array_mxhost;
	var $EnableDNSMASQLDAPDB=0;

	function dnsmasq(){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		include_once(dirname(__FILE__).'/class.sockets.inc');
		$sock=new sockets();
		$this->EnableDNSMASQLDAPDB=$sock->GET_INFO("EnableDNSMASQLDAPDB");
		if(!is_numeric($this->EnableDNSMASQLDAPDB)){$this->EnableDNSMASQLDAPDB=0;}
		$this->configuration_file_datas=$sock->GET_INFO("DnsMasqConfigurationFile");
		if(trim($this->configuration_file_datas)==null){
			$sock=new sockets();
			$this->configuration_file_datas=base64_decode($sock->getFrameWork('cmd.php?LoaddnsmasqConf=yes'));
			writelogs("DnsMasqConfigurationFile = null -> original conf = ".strlen($this->configuration_file_datas)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$this->ParseConf();
		$this->ldap_addesses();
		$this->ParseAddress();
		$this->ParseListenAddress();
		$this->ParseInterfaces();
		$this->Parsemxhost();
		$this->Mainunset();
	}
	
	function ParseConf(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){
			writelogs("tpl not an Array() !",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		while (list ($index, $line) = each ($tpl) ){
			if(trim($line)==null){continue;}
			if(substr(trim($line),0,1)=="#"){continue;}
			if(preg_match("#^server=#", $line)){continue;}
			if(preg_match("#^local=#", $line)){continue;}
			if(preg_match("#^ptr-record=#", $line)){continue;}
			writelogs("Parsing $line",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(preg_match('#^([a-zA-Z\-\_]+)=(.+)#',$line,$regs)){
				if(trim($regs[1])=="EnableDNSMASQ"){continue;}
				$this->main_array[$regs[1]]=$regs[2];
				continue;
			}
			if(preg_match('#^([a-zA-Z\-\_]+)#',$line,$regs)){
					$this->main_array[$regs[1]]="yes";
			}
			
			
		}
		writelogs("Parsing ". count($tpl)." lines done and get an array of ".count($this->main_array)." items",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($this->main_array["resolv-file"]==null){$this->main_array["resolv-file"]="/etc/dnsmasq.resolv.conf";}
		if($this->main_array["cache-size"]==null){$this->main_array["cache-size"]="1500";}
		

	}
	function Mainunset(){
		unset($this->main_array["mxrecdomainfrom"]);
		unset($this->main_array["mxrecdomainto"]);
		unset($this->main_array["mxheight"]);
		unset($this->main_array["address"]);
		unset($this->main_array["listen-address"]);
		unset($this->main_array["interface"]);
		unset($this->main_array["mx-host"]);	
		
	}
	
	function ParseAddress(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^address=/(.+)?/(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				if(isset($this->array_ldap_addresses[$regs[1]])){continue;}
				$this->array_address[$regs[1]]=$regs[2];
			}
			
		}
		
	}
	
	function Parsemxhost(){
	$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^mx-host=(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$list=$regs[1];
				$this->array_mxhost[]=$regs[1];	
				}
			}
		}			

	
	function ParseListenAddress(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^listen-address=(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$list=$regs[1];
				if(strpos($list,",")>0){
					$this->array_listenaddress=explode(',',$list);
				}else{$this->array_listenaddress[]=$list;}
			}
		}	
	}
	function ParseInterfaces(){
		$tpl=explode("\n",$this->configuration_file_datas);
		if(!is_array($tpl)){return null;}
		$filter='#^interface=(.+)?#';
		while (list ($index, $line) = each ($tpl) ){
			if(preg_match($filter,$line,$regs)){
				$this->array_interface[]=$regs[1];
			}
			
		}
	}
	
	private function KeyOnlyArray(){
		$f["domain-needed"]=true;
		$f["expand-hosts"]=true;
		$f["bogus-priv"]=true;
		$f["filterwin2k"]=true;
		$f["filterwin"]=true;
		$f["strict-order"]=true;
		$f["no-resolv"]=true;
		$f["no-poll"]=true;
		$f["no-hosts"]=true;
		$f["no-negcache"]=true;
		$f["log-queries"]=true;
		return $f;
	}

	
	function SaveConf(){
		$onlykey=$this->KeyOnlyArray();
		while (list ($key, $value) = each ($this->main_array) ){
			if($value=="EnableDNSMASQ"){continue;}
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__."() parsing \"$key\" value=$value\n";}
			if($key=="filterwin"){$key="filterwin2k";}
			if($value<>'nokey'){
				if($value=='justkey'){
					$conf=$conf . "$key\n";
					continue;
				}
				
				if(isset($onlykey[$key])){
					
					if($value=="no"){
						if($GLOBALS["VERBOSE"]){echo __FUNCTION__."() $key is a single key, value=$value\n";}
						continue;
					}
					$conf=$conf . "$key\n";
					continue;
				}
				
				$conf=$conf . "$key=$value\n";				
				
				
			}
			
		}
		
		//listen-address ---------------------------------------------------------------
		if(is_array($this->array_listenaddress)){
				$conf=$conf . "listen-address=" . implode(",",$this->array_listenaddress) . "\n";
			}
		
		//interfaces ---------------------------------------------------------------
		if(is_array($this->array_interface)){
		while (list ($index, $value) = each ($this->array_interface) ){
			if($value==null){continue;}
			$conf=$conf . "interface=$value\n";
			
		}}		
		//address ---------------------------------------------------------------
		if(is_array($this->array_address)){
		while (list ($index, $value) = each ($this->array_address) ){
			if($value==null){continue;}
			if($index==null){continue;}
			if(isset($GLOBALS["ARRAY_ADRESSES_DONE"][$index])){continue;}
			$GLOBALS["ARRAY_ADRESSES_DONE"][$index]=true;
			$re=explode(".", $value);
			$conf=$conf . "address=/$index/$value\n";
			$conf=$conf . "ptr-record={$re[3]}.{$re[2]}.{$re[1]}.{$re[0]}.in-addr.arpa,\"$index\"\n";
		}}
		
		$ldapAddresses=$this->ldap_addesses();
		if($ldapAddresses<>null){
			$conf=$conf . "$ldapAddresses\n";
		}
		
		//mx-host ---------------------------------------------------------------
		if(is_array($this->array_mxhost)){
			
		while (list ($index, $value) = each ($this->array_mxhost) ){
			if($value==null){continue;}
			if($index==null){continue;}
			$array[$value]=$value;
		}
		while (list ($index, $value) = each ($array) ){	
		if($value==null){continue;}
			$conf=$conf . "mx-host=$value\n";
		}}else{writelogs("mx-host : no array...",__CLASS__ . '/'. __FUNCTION__, __FILE__);	}
		//-----------------------------------------------------------------------------
		
		$sock=new sockets();
		
		$sock->SaveConfigFile($conf."\n","DnsMasqConfigurationFile");
		if(!$GLOBALS["AS_ROOT"]){
			$sock->getFrameWork("cmd.php?restart-dnsmasq=yes");
		}
		
	}
	
	private function ldap_addesses(){

		
		
		if($this->EnableDNSMASQLDAPDB==0){return;}
		
		if(count($this->array_interface)==0){
			include_once(dirname(__FILE__)."/class.system.network.inc");
			$net=new networking();
			while (list ($eth, $ip_addr) = each ($net->array_TCP) ){
				if($ip_addr=="127.0.0.1"){continue;}
				if($ip_addr=="0.0.0.0"){continue;}
				$array_interface[]=$ip_addr;
			}
		}else{
			$array_interface=$this->array_interface;
		}	
		
		$reverse_ip=$array_interface[0];
		
		if(count($this->array_ldap_addresses)>0){
			reset($this->array_ldap_addresses);
			while (list ($hostname, $arecord) = each ($this->array_ldap_addresses) ){
				if(trim($hostname)==null){continue;}
				$f[]="address=/$hostname/$arecord";	
				$re=explode(".", $arecord);
				if($GLOBALS["VERBOSE"]){echo "PTR {$re[3]}.{$re[2]}.{$re[1]}.{$re[0]}.in-addr.arpa\n";}
				$reverse["{$re[3]}.{$re[2]}.{$re[1]}.{$re[0]}.in-addr.arpa"]=$hostname;
				
			}
			
			if(count($f)>0){
				
				if(isset($reverse)){
					if(count($reverse)>0){
						while (list ($a, $b) = each ($reverse) ){
							$f[]="ptr-record=$a,\"$b\"";
						}
					}
				}
				return @implode("\n", $f);}
		}
		$ldap=new clladp();
		$pattern="(&(objectclass=dNSDomain2)(|(aRecord=*)(associatedDomain=*)(dc=*)))";
		$attr=array("associatedDomain","MacRecord","aRecord","sOARecord");
		$sr =ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		if($sr){
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				for($i=0;$i<$hash["count"];$i++){
					$dn=$hash[$i]["dn"];
					$arecord=$hash[$i]["arecord"][0];
					$macrecord=$hash[$i]["macrecord"][0];
					$sOARecord=$hash[$i]["soarecord"][0];
					if($arecord=="127.0.0.1"){continue;}
					if($sOARecord<>null){continue;}
					if($arecord==null){continue;}
						
					for($z=0;$z<$hash[$i]["associateddomain"]["count"];$z++){
						$hostname=$hash[$i]["associateddomain"][$z];
						if(isset($GLOBALS["ARRAY_ADRESSES_DONE"][$hostname])){continue;}
						$this->array_ldap_addresses[$hostname]=$arecord;
						$GLOBALS["ARRAY_ADRESSES_DONE"][$hostname]=true;
						$rz=explode(".", $arecord);
						$reverse["{$re[3]}.{$rz[2]}.{$rz[1]}.{$rz[0]}.in-addr.arpa"]=$hostname;
						$f[]="address=/$hostname/$arecord";				
					}
				}

			if(isset($reverse)){
				if(count($reverse)>0){
					while (list ($a, $b) = each ($reverse) ){
						$f[]="ptr-record=$a,\"$b\"";
					}
				}
			}	
			
			
			
			
		}else{return null;}
		if(count($f)>0){return @implode("\n", $f);}
		
	}
	
	
	public function SaveConfToServer(){$this->SaveConf();}
	
	
	
	
	
	
	
}

?>