<?php
include_once(dirname(__FILE__).'/class.dansguardian.inc');
include_once(dirname(__FILE__).'/class.kav4proxy.inc');
include_once(dirname(__FILE__).'/class.sockets.inc');
include_once(dirname(__FILE__)."/class.c-icap-filter.inc");	




class icap{
	var $ASROOT=false;
	var $IS_30=false;
	var $IS_31=false;
	var $IS_32=false;
	var $ICAP_SERVICES_COUNT=0;
	var $enable_kavproxy=0;
	var $enable_cicap=0;
	var $enable_squidguard=0;
	var $EnableClamavInCiCap=1;
	var $icap_final_string;
	var $EnableSquidGuardInCiCAP=1;
	var $EnableKavICAPRemote=0;
	var $KavICAPRemoteAddr=null;
	var $KavICAPRemotePort=0;	
	
	function icap(){
		$users=new usersMenus();
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$SQUID_ICAP_ENABLED=$users->SQUID_ICAP_ENABLED;
		$KAV4PROXY_INSTALLED=$users->KAV4PROXY_INSTALLED;
		$ArticaSquidParameters=$sock->GET_INFO('ArticaSquidParameters');
		$ini->loadString($ArticaSquidParameters);
		$this->enable_cicap=$sock->GET_INFO('CicapEnabled');
		$this->enable_kavproxy=$ini->_params["KAV"]["enabled"];
		$this->enable_squidguard=$sock->GET_INFO('squidGuardEnabled');
		
		
		$this->EnableClamavInCiCap=$sock->GET_INFO("EnableClamavInCiCap");
		$this->EnableSquidGuardInCiCAP=$sock->GET_INFO("EnableSquidGuardInCiCAP");
		$this->EnableKavICAPRemote=$sock->GET_INFO("EnableKavICAPRemote");
		$this->KavICAPRemoteAddr=$sock->GET_INFO("KavICAPRemoteAddr");
		$this->KavICAPRemotePort=$sock->GET_INFO("KavICAPRemotePort");
		
		if($this->KavICAPRemoteAddr==null){$this->EnableKavICAPRemote=0;}
		if(!is_numeric($this->KavICAPRemotePort)){$this->EnableKavICAPRemote=0;}
		if(!is_numeric($this->EnableKavICAPRemote)){$this->EnableKavICAPRemote=0;}		
		
		
		if($this->EnableClamavInCiCap==null){$this->EnableClamavInCiCap=1;$sock->SET_INFO("EnableClamavInCiCap","1");}
		if($this->EnableSquidGuardInCiCAP==null){$this->EnableSquidGuardInCiCAP=1;$sock->SET_INFO("EnableSquidGuardInCiCAP","1");}
		
		echo "Starting......: Squid ClamAV in ICAP:$this->EnableClamavInCiCap\n";
		echo "Starting......: Squid squidGuard in ICAP:$this->EnableSquidGuardInCiCAP\n";
		echo "Starting......: Squid Remote Kaspersky ICAP Server:$this->EnableKavICAPRemote ($this->KavICAPRemoteAddr:$this->KavICAPRemotePort)\n";  
		
		if(posix_getuid()==0){$this->ASROOT=true;}
		if(preg_match('#^([0-9]+)\.([0-9]+)#',$users->SQUID_VERSION,$re)){
		if($re[1]==3){
			$this->IS_30=true;
			if($re[2]==1){$this->IS_31=true;}
			if($re[2]>=2){$this->IS_32=true;}
			}
		}
		
		if(!$users->KAV4PROXY_INSTALLED){$this->enable_kavproxy=0;}
		if(!$users->C_ICAP_INSTALLED){$this->enable_cicap=0;}
		if(!$users->SQUIDGUARD_INSTALLED){$this->EnableSquidGuardInCiCAP=0;}
		if($this->EnableKavICAPRemote==1){$this->enable_kavproxy=1;}
		
		$this->buildCicapConfig();
		
		
	}
	
	private function buildCicapConfig(){
		if(!$this->IS_30){return null;}
		if(!$this->IS_31){
			
			$icap[]=$this->C_ICAP_30();
			$icap[]=$this->KAV_ICAP_30();
			$icap[]=$this->ICAP_HEADERS();
			$icap[]="";
			$icap[]="";
			$this->icap_final_string=@implode("\n\n",$icap);
			return;
			}
		
		$icap[]=$this->C_ICAP_31();
		$icap[]=$this->KAV_ICAP_31();
		$icap[]=$this->ADAPTATION_31();	
		$icap[]=$this->ICAP_HEADERS();
		$icap[]="";
		$icap[]="";
		$this->icap_final_string=@implode("\n\n",$icap);	
		
	}
	

	
	private function C_ICAP_30(){
		if($this->enable_cicap<>1){return null;}
		if($this->enable_kavproxy==1){
			$this->enable_cicap=0;
			return null;
		}
		$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;
		if($this->ASROOT){echo "Starting......: Squid activating C-ICAP 3.0.x\n";}
		
		
		$kav_rules[]="# --------- icap_service C-ICAP (mode 3.0.x)";
		
		if($this->EnableSquidGuardInCiCAP==1){
			$kav_rules[]="icap_service\tservice_req reqmod_precache O icap://127.0.0.1:1345/srv_url_check";
		}
		
		
		if($this->EnableClamavInCiCap==1){
			$kav_rules[]="icap_service\tservice_avi respmod_precache 1 icap://127.0.0.1:1345/srv_clamav";
		}
		$kav_rules[]="";
		if($this->EnableClamavInCiCap==1){$kav_rules[]="icap_class\tclass_antivirus service_avi";}
		if($this->EnableSquidGuardInCiCAP==1){$kav_rules[]="icap_class\tclass_antivirus_req service_req";}	
		if($this->EnableClamavInCiCap==1){$kav_rules[]="icap_access\tclass_antivirus allow all";}
		if($this->EnableSquidGuardInCiCAP==1){$kav_rules[]="icap_access\tclass_antivirus_req allow all";}
		$kav_rules[]="";
		return implode("\n",$kav_rules);
	}
	
		private function ICAP_HEADERS($kaspersky=0){
			if($this->ICAP_SERVICES_COUNT==0){return;}
			$conf[]="icap_enable on";
			$conf[]="icap_preview_size 128";
			$conf[]="icap_service_failure_limit -1";
			$conf[]="icap_preview_enable on";
			$conf[]="icap_send_client_ip on";
			$conf[]="icap_send_client_username on";
			$conf[]="icap_client_username_header X-Authenticated-User";
			$conf[]="icap_client_username_encode on";
			return implode("\n",$conf);
			}	
	
	private function KAV_ICAP_30(){
		if($this->enable_kavproxy<>1){
				if($this->ASROOT){echo "Starting......: Squid Kaspersky ICAP is disabled\n";}
				return;
			}
		
		$IpAddrr="127.0.0.1";$port=1344;
		if($this->EnableKavICAPRemote==1){$IpAddrr=$this->KavICAPRemoteAddr;$port=$this->KavICAPRemotePort;}
		if($this->EnableKavICAPRemote==0){$kav=new Kav4Proxy();if(preg_match("#(.+?):([0-9]+)#", $kav4->main_array["ListenAddress"],$re)){$IpAddrr=$re[1];$port=$re[2];}}
		
		$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;			
		if($this->ASROOT){echo "Starting......: Squid activating Kaspersky ICAP Mode\n";}
		$kav_rules[]="# --------- icap_service KASPERSKY mode 3.0.x";
		$kav_rules[]="";
		$kav_rules[]="icap_service\tis_kav_resp respmod_precache 0 icap://$IpAddrr:$port/av/respmod";
		$kav_rules[]="icap_service\tis_kav_req reqmod_precache 0 icap://$IpAddrr:$port/av/reqmod";
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_class";
		$kav_rules[]="icap_class\tic_kav_resp is_kav_resp";
		$kav_rules[]="icap_class\tic_kav_req is_kav_req"; 
		$kav_rules[]="";
		$kav_rules[]="# --------- icap_access";
		$kav_rules[]="icap_access\tic_kav_resp allow all";
		$kav_rules[]="icap_access\tic_kav_req allow all !acl_kav_GET";			
		$kav_rules[]="";	
		return implode("\n",$kav_rules);	
	}
	
	
	private function ADAPTATION_31(){
		if($this->ICAP_SERVICES_COUNT<2){
			$r[]=$this->C_ICAP_ADAPTATION();
			$r[]=$this->KAV_ADAPTATION();
			return @implode("\n",$r);
		}
		
		return $this->ICAP_CHAINS();
		
		
	}
	
	private function C_ICAP_ADAPTATION(){
		if($this->enable_cicap<>1){return null;}
		$kav_rules[]="# --------- adaptation for C-ICAP service";
		$kav_rules[]="";	
		
		if($this->enable_squidguard==1){	
			if($this->EnableSquidGuardInCiCAP==1){	
				$kav_rules[]="adaptation_service_set  class_url_check  service_url_check";
				$kav_rules[]="adaptation_access  class_url_check  allow all";
			}
		}
		
		if($this->EnableClamavInCiCap==1){
			$kav_rules[]="adaptation_service_set  class_antivirus service_antivir";
			$kav_rules[]="adaptation_access       class_antivirus deny MULTIMEDIA";
			$kav_rules[]="adaptation_access       class_antivirus allow all";
		}		
		return @implode("\n",$kav_rules);	
	}
	
	private function KAV_ADAPTATION(){
		if($this->enable_kavproxy<>1){return null;}
		$kav_rules[]="# --------- adaptation For Kaspersky Antivirus";
		$kav_rules[]="";
		$kav_rules[]="adaptation_service_set class_antivirus_kav_resp is_kav_resp";
		$kav_rules[]="adaptation_service_set class_antivirus_kav_req is_kav_req";	
		$kav_rules[]="adaptation_access class_antivirus_kav_req deny MULTIMEDIA";
		$kav_rules[]="adaptation_access class_antivirus_kav_req allow all";
		$kav_rules[]="adaptation_access class_antivirus_kav_resp allow all";
		$kav_rules[]="";			
		return @implode("\n",$kav_rules);	
	}	
	
	
	private function ICAP_CHAINS(){
		$f[]="# --------- adaptation chain services.";
		$kav_rules[]="";
		if($this->enable_cicap){
			if($this->EnableClamavInCiCap==1){
				$a[]="service_antivir";
			}
			if($this->enable_squidguard==1){
				if($this->EnableSquidGuardInCiCAP==1){
					$b[]="service_url_check";
				}
			}
		}
			
		if($this->enable_kavproxy){$a[]="is_kav_resp";$b[]="is_kav_req";}
		
		$f[]="adaptation_service_chain ANTIVIRUS_CHAINS ".@implode(" ",$a);
		$f[]="adaptation_access ANTIVIRUS_CHAINS deny MULTIMEDIA";
		$f[]="adaptation_access ANTIVIRUS_CHAINS allow all";
		
		if(is_array($b)){
			$f[]="";
			$f[]="adaptation_service_chain REQ_CHAINS ".@implode(" ",$b);
			$f[]="adaptation_access REQ_CHAINS allow all";
		}
		return @implode("\n",$f);	
	}

	
	private function C_ICAP_31(){
		$users=new usersMenus();
		if($this->enable_cicap<>1){return null;}
		
		if($this->ASROOT){echo "Starting......: Squid activating C-ICAP\n";}
		$kav_rules[]="# --------- icap_service C-ICAP mode 3.1.x";
		

		if($this->enable_squidguard==1){
			if($this->EnableSquidGuardInCiCAP==1){
				$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;
				$kav_rules[]="# --------- icap_service C-ICAP + SquidGuard";
				$kav_rules[]="icap_service\tservice_url_check reqmod_precache routing=on bypass=on icap://127.0.0.1:1345/url_check";
				}
			}
					
		if($this->EnableClamavInCiCap==1){
			$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;
			$kav_rules[]="icap_service  service_antivir respmod_precache routing=on bypass=on icap://127.0.0.1:1345/srv_clamav";
			}
			return implode("\n",$kav_rules);		
		}	

		
	private function KAV_ICAP_31(){
		if($this->enable_kavproxy<>1){
				if($this->ASROOT){echo "Starting......: Squid Kaspersky ICAP is disabled\n";}
				return;
			}		
		$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;
		if($this->ASROOT){echo "Starting......: Squid adaptation enabled\n";}
		
		$IpAddrr="127.0.0.1";$port=1344;
		if($this->EnableKavICAPRemote==1){$IpAddrr=$this->KavICAPRemoteAddr;$port=$this->KavICAPRemotePort;}
		if($this->EnableKavICAPRemote==0){$kav=new Kav4Proxy();if(preg_match("#(.+?):([0-9]+)#", $kav4->main_array["ListenAddress"],$re)){$IpAddrr=$re[1];$port=$re[2];}}
		
		$kav_rules[]="# --------- icap_service KASPERSKY mode 3.1.1";
		$kav_rules[]="";
		$kav_rules[]="icap_service\tis_kav_resp respmod_precache routing=on bypass=on icap://$IpAddrr:$port/av/respmod";
		$kav_rules[]="icap_service\tis_kav_req reqmod_precache routing=on bypass=on icap://$IpAddrr:$port/av/reqmod";

		return implode("\n",$kav_rules);	
	}		
	
	
	
}
?>