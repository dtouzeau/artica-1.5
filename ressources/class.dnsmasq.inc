<?php



class dnsmasq{
	var $configuration_file_datas;
	var $main_array;
	var $array_address;
	var $array_interface;
	var $array_listenaddress;
	var $array_mxhost;

	function dnsmasq(){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		include_once(dirname(__FILE__).'/class.sockets.inc');
		$sock=new sockets();
		$this->configuration_file_datas=$sock->GET_INFO("DnsMasqConfigurationFile");
		if(trim($this->configuration_file_datas)==null){
			$sock=new sockets();
			$this->configuration_file_datas=base64_decode($sock->getFrameWork('cmd.php?LoaddnsmasqConf=yes'));
			writelogs("DnsMasqConfigurationFile = null -> original conf = ".strlen($this->configuration_file_datas)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$this->ParseConf();
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
			writelogs("Parsing $line",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(preg_match('#^([a-zA-Z\-\_]+)=(.+)#',$line,$regs)){
				if(trim($regs[1])=="EnableDNSMASQ"){continue;}
				$this->main_array[$regs[1]]=$regs[2];
			}else{
				if(preg_match('#^([a-zA-Z\-\_]+)#',$line,$regs)){
					$this->main_array[$regs[1]]="justkey";
				}
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

	
	function SaveConf(){
		while (list ($key, $value) = each ($this->main_array) ){
			if($value=="EnableDNSMASQ"){continue;}
			if($value<>'nokey'){
				if($value=='justkey'){
					$conf=$conf . "$key\n";
				}else{
					$conf=$conf . "$key=$value\n";
				}
				
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
				$conf=$conf . "address=/$index/$value\n";
			
		}}
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
		$sock->getFrameWork("cmd.php?restart-dnsmasq=yes");
		
	}
	
	function SaveConfToServer(){
		$this->SaveConf();
		
	}
	
	
	
	
	
	
	
}

?>