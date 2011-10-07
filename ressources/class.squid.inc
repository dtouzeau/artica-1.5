<?php
include_once(dirname(__FILE__).'/class.dansguardian.inc');
include_once(dirname(__FILE__).'/class.kav4proxy.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.sockets.inc');
include_once(dirname(__FILE__).'/class.samba.inc');	
include_once(dirname(__FILE__)."/class.c-icap-filter.inc");
include_once(dirname(__FILE__)."/class.icap.inc");		
include_once(dirname(__FILE__)."/class.squid.bandwith.inc");
include_once(dirname(__FILE__)."/class.computers.inc");



class squid{
	

}



class squidbee{
	var $dn;
	var $network_array=array();
	var $dns_array=array();
	var $squid_conf;
	var $ArticaSquidParameters;
	var $ldap_error;
	var $listen_port;
	var $visible_hostname;
	var $enable_kavproxy;
	var $enable_cicap;
	var $enable_dansguardian;
	var $enable_squidguard;
	var $enable_UfdbGuard;
	var $LDAP_AUTH=0;
	var $NTLM_AUTH=0;
	var $SSL_BUMP=0;
	var $EnableClamavInCiCap=1;
	var $CACHE_SIZE=2000;
	var $CACHE_PATH;
	var $CACHE_TYPE="ufs";
	var $alt_listen_port;
	var $kav_accept=false;
	var $kav_accept_why='';
	var $SquidBlockSites='';
	var $array_block_istes=array();
	var $global_conf_array=array();
	var $cache_list=array();
	var $acl_times=array();
	var $hasProxyTransparent;
	var $ASROOT=false;
	var $SQUID_BIN_VERSION=0;
	var $SQUID_ICAP_ENABLED=false;
	var $SQUID_VERSION="";
	var $KAV4PROXY_INSTALLED=false;
	var $EnableParentProxy=0;
	var $LDAP_EXTERNAL_AUTH=0;
	var $EXTERNAL_LDAP_AUTH_PARAMS=array();
	var $IS_30=false;
	var $IS_31=false;
	var $IS_32=false;
	var $intvalVersion=false;
	var $ICAP_SERVICES_COUNT=0;
	var $FTP_PARAMS=array();
	var $wccp2_enabled=0;
	var $wccp2_router=null;
	var $wccp2_forwarding_method=1;
	var $wccp2_return_method=1;
	var $wccp2_assignment_method="hash";
	var $enable_ftp_restrictions=0;
	var $EnableUserAgentBanAll=0;
	var $enable_adzapper=0;
	var $enable_squidclamav=0;
	var $ACL_ARP_ENABLED=false;
	var $EnableChangeRequestSize=0;
	var $allow_squid_localhost=0;
	var $ignore_expect_100=0;
	var $EnableKerbAuth=0;
	var $EnableKavICAPRemote=0;
	var $KavICAPRemoteAddr=null;
	var $KavICAPRemotePort=0;
	
	function squidbee(){
		if(posix_getuid()==0){$this->ASROOT=true;}
		$this->VerifyLdapBranch();
		$this->ParseConfig();
		$this->kav_accept=$this->isicap();
		$sock=new sockets();
		
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		$this->SQUID_BIN_VERSION=$users->SQUID_BIN_VERSION;
		if($this->ASROOT){$this->SQUID_VERSION=$this->root_squid_version();}else{$this->SQUID_VERSION=$users->SQUID_VERSION;}
		$this->SQUID_ICAP_ENABLED=$users->SQUID_ICAP_ENABLED;
		$this->KAV4PROXY_INSTALLED=$users->KAV4PROXY_INSTALLED;
		$this->SquidAcountUpdate();
		$EnableClamavInCiCap=$sock->GET_INFO("EnableClamavInCiCap");
		if($EnableClamavInCiCap==null){$EnableClamavInCiCap=1;$sock->SET_INFO("EnableClamavInCiCap","1");}
		$this->enable_UfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
		$this->enable_adzapper=$sock->GET_INFO("EnableAdZapper");
		$this->enable_squidclamav=$sock->GET_INFO("EnableSquidClamav");
		$this->EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
		$this->EnableKavICAPRemote=$sock->GET_INFO("EnableKavICAPRemote");
		$this->KavICAPRemoteAddr=$sock->GET_INFO("KavICAPRemoteAddr");
		$this->KavICAPRemotePort=$sock->GET_INFO("KavICAPRemotePort");
		
		if($this->KavICAPRemoteAddr==null){$this->EnableKavICAPRemote=0;}
		if(!is_numeric($this->KavICAPRemotePort)){$this->EnableKavICAPRemote=0;}
		if(!is_numeric($this->EnableKavICAPRemote)){$this->EnableKavICAPRemote=0;}
		if(!is_numeric($this->EnableKerbAuth)){$this->EnableKerbAuth=0;}
		
		$this->CheckVersion();
		$this->hasProxyTransparent=$sock->GET_INFO("hasProxyTransparent");
		if($this->hasProxyTransparent==null){$this->hasProxyTransparent=0;}
		
	}
	
	private function root_squid_version(){
		$unix=new unix();
		$squidbin=$unix->find_program("squid");
		if($squidbin==null){$squidbin=$unix->find_program("squid3");}
		exec("$squidbin -v 2>&1",$results);
		while (list ($num, $val) = each ($results)){
			if(preg_match("#Squid Cache: Version.*?([0-9\.]+)#", $val,$re)){
				echo "Starting......: Squid : Version (as root) '{$re[1]}'\n";
				return $re[1];
			}
		}
		echo "Warning !!!!!! cannot find version in $squidbin ! !!\n";
	}
	
	private function CheckVersion(){
		if(isset($GLOBALS["SQUID_IS_VERSION"])){
			$this->IS_30=$GLOBALS["SQUID_IS_VERSION"][30];
			$this->IS_31=$GLOBALS["SQUID_IS_VERSION"][31];
			$this->IS_32=$GLOBALS["SQUID_IS_VERSION"][32];
			return;
		}
		
		
		
		
		if(preg_match('#^([0-9]+)\.([0-9]+)#',$this->SQUID_VERSION,$re)){
			$this->intvalVersion=intval($re[1]).intval($re[2])."0";
		if($re[1]==3){
			if($this->ASROOT){echo "Starting......: Squid : Is a 3.{$re[2]} version ($this->intvalVersion)\n";}
			
			if($re[2]==0){
				$this->IS_30=true;
				$this->IS_31=false;
				$this->IS_32=false;
				$GLOBALS["SQUID_IS_VERSION"][30]=true;
				$GLOBALS["SQUID_IS_VERSION"][31]=false;
				$GLOBALS["SQUID_IS_VERSION"][32]=false;
				return;
			}
			
			
			if($re[2]==1){
				$this->IS_30=false;
				$this->IS_31=true;
				$this->IS_32=false;
				$GLOBALS["SQUID_IS_VERSION"][30]=false;
				$GLOBALS["SQUID_IS_VERSION"][31]=true;
				$GLOBALS["SQUID_IS_VERSION"][32]=false;				
				return;
			}
			if($re[2]>=2){
				$this->IS_30=false;
				$this->IS_31=true;
				$this->IS_32=true;
				$GLOBALS["SQUID_IS_VERSION"][30]=false;
				$GLOBALS["SQUID_IS_VERSION"][31]=true;
				$GLOBALS["SQUID_IS_VERSION"][32]=true;				
				return;
			}
		}
		
		if(preg_match('#^([0-9]+)\.([0-9]+)\.([0-9]+)#',$this->SQUID_VERSION,$re)){
			$this->intvalVersion=intval($re[1]).intval($re[2]).intval($re[3]);
		}		
		
	}
			
		
	}
	
	
	function isicap(){
		if(trim($this->SQUID_VERSION)==null){
			if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
			$users=$GLOBALS["CLASS_USERS"];
			if($this->ASROOT){$this->SQUID_VERSION=$this->root_squid_version();}else{$this->SQUID_VERSION=$users->SQUID_VERSION;}
			$this->SQUID_ICAP_ENABLED=$users->SQUID_ICAP_ENABLED;
			$this->KAV4PROXY_INSTALLED=$users->KAV4PROXY_INSTALLED;
		}
		writelogs("SQUID:: SQUID_VERSION::=$this->SQUID_VERSION",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(preg_match('#([0-9\.]+)#',$this->SQUID_VERSION,$re)){$this->SQUID_VERSION=$re[1];}
		if($this->SQUID_VERSION>=3){
			if($this->SQUID_ICAP_ENABLED){
				if($this->EnableKavICAPRemote==1){return true;}
				
				if($this->KAV4PROXY_INSTALLED){
					return true;
				}else{
					writelogs("SQUID:: error_kavproxy_not_installed::",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$this->kav_accept_why='{error_kavproxy_not_installed}';
				}
			}else{
				writelogs("SQUID:: error_squid_icap_not_compliance::",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->kav_accept_why='{error_squid_icap_not_compliance}';
			}
		}else{
			writelogs("SQUID:: error_squid_upto_three::version=$this->SQUID_VERSION",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->kav_accept_why='{error_squid_upto_three}<br><strong>'.$this->SQUID_VERSION."</strong>";
		}
		
		writelogs("SQUID:: version=$this->SQUID_VERSION::",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
	}
	
	
	private function KavICAPremote(){
		if($this->EnableKavICAPRemote==0){return false;}
		
		
	}
	
	function VerifyLdapBranch(){
		if(isset($GLOBALS["SQUID_MEMORYCONF"])){
			$this->squid_conf=$GLOBALS["SQUID_MEMORYCONF"]["GlobalSquidConf"];
			$this->ArticaSquidParameters=$GLOBALS["SQUID_MEMORYCONF"]["ArticaSquidParameters"];
			$this->SquidBlockSites=$GLOBALS["SQUID_MEMORYCONF"]["SquidBlockSites"];
			$this->EnableKav4Proxy=$GLOBALS["SQUID_MEMORYCONF"]["ArticaEnableKav4ProxyInSquid"];
			return;
		}
		
		$sock=new sockets();
		$this->squid_conf=$sock->GET_INFO('GlobalSquidConf');
		if($this->squid_conf==null){$this->squid_conf=$this->LOAD_CONF_ROOT();}
			
		$this->ArticaSquidParameters=$sock->GET_INFO('ArticaSquidParameters');
		$this->SquidBlockSites=$sock->GET_INFO('SquidBlockSites');
		$this->EnableKav4Proxy=$sock->GET_INFO('ArticaEnableKav4ProxyInSquid');
		
		$GLOBALS["SQUID_MEMORYCONF"]["GlobalSquidConf"]=$this->squid_conf;
		$GLOBALS["SQUID_MEMORYCONF"]["ArticaSquidParameters"]=$this->ArticaSquidParameters;
		$GLOBALS["SQUID_MEMORYCONF"]["SquidBlockSites"]=$this->SquidBlockSites;
		$GLOBALS["SQUID_MEMORYCONF"]["ArticaEnableKav4ProxyInSquid"]=$this->EnableKav4Proxy;
		
		}
		
		private function LOAD_CONF_ROOT(){
			if(!$GLOBALS["AS_ROOT"]){$sock=new sockets();return base64_decode($sock->getFrameWork('cmd.php?squid-GetOrginalSquidConf'));}
			if(class_exists("unix")){$unix=new unix();return @file_get_contents($unix->LOCATE_SQUID_CONF());}
		}		
	
	
	function ParseConfig(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		$this->ACL_ARP_ENABLED=$users->SQUID_ARP_ACL_ENABLED;
		$network=null;
		$dns=null;
		if($this->ArticaSquidParameters==null){$this->ArticaSquidParameters=$sock->GET_INFO('ArticaSquidParameters');}
		
		if(!isset($GLOBALS["SQUID_MEMORYCONF"]["INI_ARRAY"])){
			$ini->loadString($this->ArticaSquidParameters);
			if(isset($ini->_params)){
				$GLOBALS["SQUID_MEMORYCONF"]["INI_ARRAY"]=$ini->_params;
			}
		}else{
			$ini->_params=$GLOBALS["SQUID_MEMORYCONF"]["INI_ARRAY"];
		}
		if(isset($ini->_params)){
			$network=explode(';',$ini->_params["NETWORK"]["cdir"]);
			$dns=explode(';',$ini->_params["NETWORK"]["dns_servers"]);
			$this->listen_port=$ini->_params["NETWORK"]["LISTEN_PORT"];
			$this->visible_hostname=$ini->_params["NETWORK"]["visible_hostname"];
			$this->LDAP_AUTH=$ini->_params["NETWORK"]["LDAP_AUTH"];
			$this->NTLM_AUTH=$ini->_params["NETWORK"]["NTLM_AUTH"];
			$this->LDAP_EXTERNAL_AUTH=$ini->_params["NETWORK"]["LDAP_EXTERNAL_AUTH"];
			$this->EnableParentProxy=$ini->_params["NETWORK"]["EnableParentProxy"];
	
			
			$this->enable_kavproxy=$ini->_params["KAV"]["enabled"];
			$this->enable_dansguardian=$ini->_params["DANSGUARDIAN"]["enable_dansguardian"];
			$this->alt_listen_port=$ini->_params["NETWORK"]["ALT_PORT"];
			$this->CACHE_SIZE=$ini->_params["CACHE"]["CACHE_SIZE"];
			$this->CACHE_PATH=$ini->_params["CACHE"]["CACHE_PATH"];
			$this->CACHE_TYPE=$ini->_params["CACHE"]["CACHE_TYPE"];
			$this->enable_cicap=$sock->GET_INFO('CicapEnabled');
			$this->enable_squidguard=$sock->GET_INFO('squidGuardEnabled');
			$this->EXTERNAL_LDAP_AUTH_PARAMS=unserialize(base64_decode($sock->GET_INFO("SquidExternalAuth")));
			$this->FTP_PARAMS=unserialize(base64_decode($sock->GET_INFO("SquidFTPParams")));
			$this->SSL_BUMP=$ini->_params["NETWORK"]["SSL_BUMP"];
			
			$this->wccp2_enabled=$ini->_params["NETWORK"]["wccp2_enabled"];
			$this->wccp2_router=$ini->_params["NETWORK"]["wccp2_router"];
			$this->wccp2_forwarding_method=$ini->_params["NETWORK"]["wccp2_forwarding_method"];
			$this->wccp2_return_method=$ini->_params["NETWORK"]["wccp2_return_method"];
			$this->wccp2_assignment_method=$ini->_params["NETWORK"]["wccp2_assignment_method"];
			$this->enable_ftp_restrictions=$ini->_params["NETWORK"]["enable_ftp_restrictions"];
			$this->EnableUserAgentBanAll=$ini->_params["NETWORK"]["EnableUserAgentBanAll"];
			$this->EnableChangeRequestSize=$ini->_params["NETWORK"]["EnableChangeRequestSize"];
			$this->allow_squid_localhost=$ini->_params["NETWORK"]["allow_squid_localhost"];
			if(isset($ini->_params["NETWORK"]["ignore_expect_100"])){$this->ignore_expect_100=$ini->_params["NETWORK"]["ignore_expect_100"];}
			
				if(is_array($ini->_params)){
					while (list ($num, $val) = each ($ini->_params)){
						if(preg_match('#cache:(.+)#',$num,$re)){
							$this->cache_list[$re[1]]=array(
							"cache_type"=>$ini->_params[$num]["cache_type"],
							"cache_dir_level1"=>$ini->_params[$num]["cache_dir_level1"],
							"cache_dir_level2"=>$ini->_params[$num]["cache_dir_level2"],
							"cache_size"=>$ini->_params[$num]["cache_size"],
							);
						}
						
						if(preg_match('#time:([0-9]+):(.+)#',$num,$re)){
							while (list ($a, $b) = each ($ini->_params[$num])){
								$this->acl_times[$num][$a]=$b;
							}
						}
					}
				}			
			
		}
		
		if($this->ignore_expect_100==null){$this->ignore_expect_100="0";}
		if($this->wccp2_enabled==null){$this->wccp2_enabled="0";}
		if($this->wccp2_forwarding_method==null){$this->wccp2_forwarding_method="1";}
		if($this->wccp2_return_method==null){$this->wccp2_return_method="1";}
		if($this->wccp2_assignment_method==null){$this->wccp2_assignment_method="hash";}
		if($this->enable_ftp_restrictions==null){$this->enable_ftp_restrictions="0";}
		if($this->EnableChangeRequestSize==null){$this->EnableChangeRequestSize="0";}
		if($this->enable_cicap==null){$this->enable_cicap=0;}
		if($this->listen_port==null){$this->listen_port=3128;}
		if($this->visible_hostname==null){$this->visible_hostname="proxyweb";}
		if($this->LDAP_AUTH==null){$this->LDAP_AUTH=0;}
		if($this->NTLM_AUTH==null){$this->NTLM_AUTH=0;}
		if($this->enable_kavproxy==null){$this->enable_kavproxy=0;}
		if($this->enable_dansguardian==null){$this->enable_dansguardian=0;}
		if($this->CACHE_SIZE==null){$this->CACHE_SIZE=2000;}
		if($this->CACHE_PATH==null){$this->CACHE_PATH="/var/cache/squid";}
		if($this->CACHE_TYPE==null){$this->CACHE_TYPE="ufs";}
		if($this->SSL_BUMP==null){$this->SSL_BUMP=0;}
		
		if(!preg_match("#([0-9]+)\.([0-9]+)#",$users->SQUID_VERSION,$re)){
    		if($re[1]<3){$this->SSL_BUMP=0;}else{if($re[2]<1){$this->SSL_BUMP=0;}}
		}
		
		

		if(is_array($network)){
			while (list ($num, $cidr) = each ($network)){
					if(trim($cidr)==null){continue;}
					$this->network_array[]=$cidr;
				}
		}
		
		if(is_array($dns)){
			while (list ($num, $serv) = each ($dns)){
				if(trim($serv)<>null){
					$this->dns_array[]=$serv;
				}
				
			}	
		}
		
		if($this->squid_conf==null){$this->squid_conf=$sock->GET_INFO("GlobalSquidConf");}
		
		$this->global_conf_array=unserialize(base64_decode($sock->GET_INFO("SquidGlobalConfArray")));
		if(!is_array($this->global_conf_array)){
			$tb=explode("\n",$this->squid_conf);
			writelogs("global_conf_array is not an array, parsing ". count($tb).
		 " lines.. ". strlen($this->squid_conf)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			while (list ($num, $val) = each ($tb)){
			if(preg_match('#^([a-z\_]+)\s+(.+)#',$val,$re)){
				if($re[1]=="acl"){continue;}
				if($re[1]=="http_access"){continue;}
				if($re[1]=="auth_param"){continue;}
				$this->global_conf_array[$re[1]]=$re[2];
				}
			}
		}	
		
		if(!isset($this->global_conf_array["connect_timeout"])){$this->global_conf_array["peer_connect_timeout"]="3 minutes";}
		if(!isset($this->global_conf_array["request_body_max_size"])){$this->global_conf_array["request_body_max_size"]="5000";}
		if(!isset($this->global_conf_array["request_header_max_size"])){$this->global_conf_array["request_header_max_size"]="64 KB";}
		if(!isset($this->global_conf_array["reply_header_max_size"])){$this->global_conf_array["reply_header_max_size"]="64 KB";}
		if(!isset($this->global_conf_array["client_request_buffer_max_size"])){$this->global_conf_array["client_request_buffer_max_size"]="512 KB";}
		if(!isset($this->global_conf_array["request_body_max_size"])){$this->global_conf_array["request_body_max_size"]=0;}
		if(!isset($this->global_conf_array["peer_connect_timeout"])){$this->global_conf_array["peer_connect_timeout"]="3 minutes";}
		if(!isset($this->global_conf_array["reply_body_max_size"])){$this->global_conf_array["reply_body_max_size"]=0;}
		
		
	
		
		if($this->global_conf_array["dead_peer_timeout"]==null){$this->global_conf_array["dead_peer_timeout"]="10 seconds";}
		if($this->global_conf_array["dns_timeout"]==null){$this->global_conf_array["dns_timeout"]="2 minutes";}
		if($this->global_conf_array["connect_timeout"]==null){$this->global_conf_array["connect_timeout"]="1 minutes";}
		if($this->global_conf_array["peer_connect_timeout"]==null){$this->global_conf_array["peer_connect_timeout"]="3 minutes";}
		if(!is_numeric($this->global_conf_array["request_body_max_size"])){$this->global_conf_array["request_body_max_size"]="5000";}
		if($this->global_conf_array["maximum_object_size"]==null){$this->global_conf_array["maximum_object_size"]="300 MB";}
		if($this->global_conf_array["cache_mem"]==null){$this->global_conf_array["cache_mem"]="8 MB";}
		if($this->global_conf_array["cache_swap_high"]==null){$this->global_conf_array["cache_swap_high"]="90";}
		if($this->global_conf_array["cache_swap_low"]==null){$this->global_conf_array["cache_swap_low"]="95";}
		if($this->global_conf_array["minimum_object_size"]==null){$this->global_conf_array["minimum_object_size"]="0 KB";}
		if($this->global_conf_array["maximum_object_size_in_memory"]==null){$this->global_conf_array["maximum_object_size_in_memory"]="8 KB";}
		if($this->global_conf_array["ipcache_size"]==null){$this->global_conf_array["ipcache_size"]=1024;}
		if($this->global_conf_array["ipcache_low"]==null){$this->global_conf_array["ipcache_low"]=90;}
		if($this->global_conf_array["ipcache_high"]==null){$this->global_conf_array["ipcache_high"]=95;}
		if($this->global_conf_array["fqdncache_size"]==null){$this->global_conf_array["fqdncache_size"]=1024;}
		if($this->global_conf_array["request_header_max_size"]==null){$this->global_conf_array["request_header_max_size"]="64 KB";}
		if(!is_numeric($this->global_conf_array["request_body_max_size"])){$this->global_conf_array["request_body_max_size"]="0";}
		if($this->global_conf_array["client_request_buffer_max_size"]==null){$this->global_conf_array["client_request_buffer_max_size"]="512 KB";}
		if($this->global_conf_array["reply_header_max_size"]==null){$this->global_conf_array["reply_header_max_size"]="64 KB";}
		
		
		
		if($this->global_conf_array["reply_body_max_size"]==null){$this->global_conf_array["reply_body_max_size"]="0";}
		writelogs("Parsing squid.conf ". count($this->global_conf_array)." parameters",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
		
	}
	
	
	function SquidAcountUpdate(){
		$ldap=new clladp();
		$upd=array();
		$dn="cn=squidinternalauth,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="userAccount";
			$upd["cn"][0]="squidinternalauth";
			$upd["userid"][0]="squidinternalauth";
			$upd["accountActive"][0]="TRUE";
			$upd["accountgroup"][0]="1000";
			$upd["domainname"][0]="nodomain";
			$upd["domainname"][0]="nodomain";
			$upd["homedirectory"][0]="/dev/null";
			$upd["maildir"][0]="/dev/null";
			$upd["sn"][0]="squidinternalauth";	
			$upd["userpassword"][0]=$ldap->ldap_password;
			if(!$ldap->ldap_add($dn,$upd)){
				if(posix_getuid()==0){
					echo "SquidAcountUpdate():: error $ldap->ldap_last_error\n";
				}
			}
		}else{
			$upd["userpassword"][0]=$ldap->ldap_password;
			$ldap->Ldap_modify($dn,$upd);
		}
	}
	
	
	
	function BuildBlockedSites(){
			$sql="SELECT uri FROM squid_block ORDER BY ID DESC";
			$q=new mysql();
			$tb=array();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				if(posix_getuid()==0){echo "Starting......: Squid Mysql database, error, skip blocked sites\n";}
			}
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if(trim($ligne["uri"])==null){continue;}
				$pattern=$ligne["uri"];
				$pattern=str_replace(".","\.",$pattern);
				$pattern=str_replace("*",".*",$pattern);
				
				if(!preg_match("#^(http|ftp|ftps)\:#",$pattern)){
					$pattern="^.+?$pattern";
				}else{
					$pattern="^$pattern";
				}			
				$tb[]=$pattern;
			}
			
			$countdesite=count($tb);
			writelogs("$countdesite rows",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(posix_getuid()==0){echo "Starting......: Squid $countdesite blocked site(s)\n";}
			
			if(is_array($tb)){$conf=implode("\n",$tb);}else{$conf="";}
			@file_put_contents("/etc/squid3/squid-block.acl",$conf);
	}
		
function SaveToLdap($norestart=false){
			if(!$this->kav_accept){
				writelogs("SQUID:: kavaccept = FALSE",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->enable_kavproxy=0;
			}
			if($this->hasProxyTransparent==1){$this->LDAP_AUTH=0;}else{
				$this->wccp2_enabled=0;
			}
			$sock=new sockets();
			$ini=$ini."[NETWORK]\n";
			$ini=$ini."cdir=".implode(";",$this->network_array)."\n";
			$ini=$ini."dns_servers=".implode(";",$this->dns_array)."\n";
			$ini=$ini."LISTEN_PORT=$this->listen_port\n";
			$ini=$ini."EnableParentProxy=$this->EnableParentProxy\n";
			if($this->enable_dansguardian==1){
					$this->alt_listen_port=$sock->RandomPort();
					$dansguardian=new dansguardian();
					$dansguardian->SaveSettings();
			}
			$ini=$ini."wccp2_enabled=$this->wccp2_enabled\n";
			$ini=$ini."wccp2_router=$this->wccp2_router\n";
			$ini=$ini."wccp2_forwarding_method=$this->wccp2_forwarding_method\n";
			$ini=$ini."wccp2_return_method=$this->wccp2_return_method\n";
			$ini=$ini."wccp2_assignment_method=$this->wccp2_assignment_method\n";
			$ini=$ini."enable_ftp_restrictions=$this->enable_ftp_restrictions\n";
			$ini=$ini."EnableUserAgentBanAll=$this->EnableUserAgentBanAll\n";
			$ini=$ini."EnableChangeRequestSize=$this->EnableChangeRequestSize\n";
			$ini=$ini."allow_squid_localhost=$this->allow_squid_localhost\n";
			
			if($this->IS_30){if($GLOBALS["VERBOSE"]){echo "Starting......: Squid : is 3.x OK\n";}}
			if(!$this->IS_32){if($GLOBALS["VERBOSE"]){echo "Starting......: Squid : is NOT 3.2.x OK\n";}}
			if($this->IS_32){if($GLOBALS["VERBOSE"]){echo "Starting......: Squid : is 3.2.x OK\n";}}
			
			if(($this->IS_30) && (!$this->IS_32)){
				$ini=$ini."ignore_expect_100=$this->ignore_expect_100\n";
			}
			
			
			
			
			$ini=$ini."ALT_PORT=$this->alt_listen_port\n";
			$ini=$ini."visible_hostname=$this->visible_hostname\n";
			$ini=$ini."LDAP_AUTH=$this->LDAP_AUTH\n";
			$ini=$ini."NTLM_AUTH=$this->NTLM_AUTH\n";
			$ini=$ini."LDAP_EXTERNAL_AUTH=$this->LDAP_EXTERNAL_AUTH\n";
			$ini=$ini."SSL_BUMP=$this->SSL_BUMP\n";
			
			
			
			$ini=$ini."[KAV]\n";
			$ini=$ini."enabled=$this->enable_kavproxy\n";
			$ini=$ini."[DANSGUARDIAN]\n";
			$ini=$ini."enable_dansguardian=$this->enable_dansguardian\n";
			$ini=$ini."[CACHE]\n";
			$ini=$ini."CACHE_SIZE=$this->CACHE_SIZE\n";
			$ini=$ini."CACHE_PATH=$this->CACHE_PATH\n";
			$ini=$ini."CACHE_TYPE=$this->CACHE_TYPE\n";
			
			
							
	

			if(is_array($this->cache_list)){
				reset($this->cache_list);
				$num=null;
				$val=null;
				while (list ($num, $val) = each ($this->cache_list)){
					$ini=$ini."[cache:$num]\n";
					$ini=$ini."cache_type={$val["cache_type"]}\n";
					$ini=$ini."cache_dir_level1={$val["cache_dir_level1"]}\n";
					$ini=$ini."cache_dir_level2={$val["cache_dir_level2"]}\n";
					$ini=$ini."cache_size={$val["cache_size"]}\n";
				}
			}
			
			if(is_array($this->acl_times)){
				reset($this->acl_times);
				while (list ($num, $array) = each ($this->acl_times)){
					$ini=$ini."[$num]\n";
					while (list ($a, $b) = each ($array)){
						$ini=$ini."$a=$b\n";
					}
					
				}
				
			}
			$sock=new sockets();
			writelogs("Save SquidGlobalConfArray ".count($this->global_conf_array)." parameters",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sock->SaveConfigFile(base64_encode(serialize($this->global_conf_array)),"SquidGlobalConfArray");
			$sock->SET_INFO("DansGuardianEnabled",$this->enable_dansguardian);
			$sock->SET_INFO('kavicapserverEnabled',$this->enable_kavproxy);
			$sock->SET_INFO("hasProxyTransparent",$this->hasProxyTransparent);
			$sock->SaveConfigFile($ini,"ArticaSquidParameters");
			//$sock->SaveConfigFile($this->BuildSquidConf(),"GlobalSquidConf");
			$sock->SET_INFO("ArticaEnableKav4ProxyInSquid",$this->enable_kavproxy);
			$sock->SET_INFO("CicapEnabled",$this->enable_cicap);
			$sock->SET_INFO("squidGuardEnabled",$this->enable_squidguard);
			$sock->SET_INFO("EnableUfdbGuard",$this->enable_UfdbGuard);
			$sock->SET_INFO("EnableAdZapper",$this->enable_adzapper);
			$sock->SET_INFO("EnableSquidClamav",$this->enable_squidclamav);

			$sock->SaveConfigFile(base64_encode(serialize($this->EXTERNAL_LDAP_AUTH_PARAMS)),"SquidExternalAuth");
			$sock->SaveConfigFile(base64_encode(serialize($this->FTP_PARAMS)),"SquidFTPParams");
			

			
				
			
			
			if($this->enable_dansguardian==1){
				writelogs("",__FUNCTION__,__FILE__);
				writelogs("Save Dansguardian configuration...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$dans=new dansguardian();
				$dans->SaveSettings();
			}
			
			if($this->enable_kavproxy==1){
				$sock->getFrameWork("cmd.php?kav4proxy-reconfigure=yes");
			}
			
			if(!$norestart){
				$sock->getFrameWork("cmd.php?squidnewbee=yes");
			}
			return true;
			
		}
		
		function SaveToServer($norestart=false){
			if(!$norestart){
				$sock=new sockets();
				$sock->getFrameWork("cmd.php?squidnewbee=yes");	
			}
		}
		
		

		
public function SquidGuardDatabasesStatus($all=0){
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	while (list ($a, $b) = each ($datas)){
		if(preg_match("#domainlist.+?(.+)#",$b,$re)){
			$file="/var/lib/squidguard/{$re[1]}";
			if(filesize($file)==0){continue;}
			$f[]=$file;
			continue;
		}
				
		if(preg_match("#urllist.+?(.+)#",$b,$re)){
			$file="/var/lib/squidguard/{$re[1]}";
			if(filesize($file)==0){continue;}
			$f[]=$file;
			continue;
		}
		
		
	}
	
	if($all==0){while (list ($a, $b) = each ($f)){
		if(!is_file("$b.db")){
			$array[]="$b.db";
		}
		
	}}else{
		while (list ($a, $b) = each ($f)){
			$array[]="$b.db";
		}
	}
	
	
	return $array;
	
}		
		
		
		private function ldap_auth_conf(){
			if(!is_array($this->global_conf_array)){$this->ParseConfig();}
			if($GLOBALS["VERBOSE"]){if(!$this->ASROOT){$GLOBALS["VERBOSE"]=false;}}
			if($this->EnableKerbAuth==1){return "#--------- LDAP AUTH settings\n#squid_kerb_auth enabled\n";}
			if($GLOBALS["VERBOSE"]){
				echo "DEBUG : LDAP_AUTH:$this->LDAP_AUTH\n";
				echo "DEBUG : LDAP_EXTERNAL_AUTH:$this->LDAP_EXTERNAL_AUTH\n";
			}
			
			if($this->LDAP_AUTH==0){
				if($this->LDAP_EXTERNAL_AUTH<>1){
					if($this->ASROOT){echo "Starting......: Squid : LDAP authentication method is disabled\n";}
					return null;
				}
			}
			
			
			if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
			$users=$GLOBALS["CLASS_USERS"];
			$ldap=new clladp();
			if($ldap->ldap_host==null){$ldap->ldap_host="127.0.0.1";}
			
			$ldap_host=$ldap->ldap_host;
			$ldap_port=$ldap->ldap_port;
			$suffix=$ldap->suffix;
			$ldap_admin="cn=$ldap->ldap_admin,$ldap->suffix";
			$ldap_password=$ldap->ldap_password;
			$user_filter="(&(objectClass=userAccount)(uid=%s))";
			$group_filter="(&(objectClass=posixGroup)(gidNumber=%a)(memberUid=%v))";
			$auth_banner="Squid proxy-caching web server";
			
			
			if($this->LDAP_EXTERNAL_AUTH==1){
				if($this->ASROOT){echo "Starting......: Squid : Using remote LDAP database\n";}
				$ldap_host=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_server"];
				$ldap_port=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_port"];	
				$ldap_password=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_password"];
				$ldap_admin=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_user"];		
				$suffix=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_suffix"];
				$user_filter=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_filter_users"];
				$group_filter=$this->EXTERNAL_LDAP_AUTH_PARAMS["ldap_filter_group"];
				$auth_banner=$this->EXTERNAL_LDAP_AUTH_PARAMS["auth_banner"];					
			}else{
				if($this->ASROOT){echo "Starting......: Squid : Using local LDAP database\n";}
			}
					
			
			$conf[]= "#--------- LDAP AUTH settings\n";
			$conf[]= "#Authentification mode, building using squid compiled for $ldap_host:$ldap_port";
			if(trim($users->SQUID_LDAP_AUTH)<>null){
				$conf[]= "auth_param basic program $users->SQUID_LDAP_AUTH -b \"$suffix\" -D \"$ldap_admin\" -w \"$ldap_password\" -f \"$user_filter\" -v 3 -h $ldap_host -p $ldap_port";
				$conf[]= "#--------- GLOBAL";
				$conf[]= "external_acl_type ldap_group %LOGIN " . dirname($users->SQUID_LDAP_AUTH)."/squid_ldap_group -D \"$ldap_admin\" -w \"$ldap_password\" -b \"$suffix\"  -f \"$group_filter\" -S -v 3 -h $ldap_host -p $ldap_port";
				$conf[]= "auth_param basic children 5";
				$conf[]= "auth_param basic realm $auth_banner";
				$conf[]= "acl ldapauth proxy_auth REQUIRED";
				$conf[]=$this->WHITELISTED_AUTH();
				$conf[]=$this->WHITELISTED_AUTH_BROWSERS();
				$GLOBALS["HTTP_ACCESS"]["LDAP_AUTH"]="http_access allow ldapauth";
				
				$conf[]="";
			}else{
				$conf[]= "#No LDAP auth_param basic program found !";
				$conf[]="";
			}

			return implode("\n",$conf);
			
		}
		
		private function ACL_FTP_RESTRICTIONS(){
			if($this->enable_ftp_restrictions<>1){return;}
			return "acl clients_ftp src \"/etc/squid3/clients_ftp.acl\"\n";
		}
		
		
		
		
		
		private function ACL_BANNED_COMPUTERS_IP(){
			$f=array();
			$MC=array();
			if(isset($GLOBALS["ACL_BANNED_COMPUTERS_IP"])){return $GLOBALS["ACL_BANNED_COMPUTERS_IP"];}
			$returned=null;
			$sock=new sockets();
			if(!$this->ACL_ARP_ENABLED){
				echo "Starting......: Squid : acl ARP are disabled\n";
			}else{
				echo "Starting......: Squid : acl ARP are enabled\n";
			}
			
			
			$sql="SELECT ID,pattern,uid FROM dansguardian_files WHERE filename='bannediplist' AND RuleID=1 ORDER BY ID DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$MAC=null;
				if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne["pattern"])){continue;}
				if(trim($ligne["pattern"])==null){continue;}
				if($this->ACL_ARP_ENABLED){
					if(trim($ligne["uid"])<>null){
						$cmp=new computers($ligne["uid"]);
						$MAC=$cmp->ComputerMacAddress;
						$ligne["pattern"]=$cmp->ComputerIP;
					}
					
					if(!IsPhysicalAddress($MAC)){$MAC=$sock->getFrameWork("cmd.php?ip-to-mac={$ligne["pattern"]}");if(!IsPhysicalAddress($MAC)){$MAC=null;}}				
					if($MAC<>null){$MC[]=$MAC;continue;}
					echo "Starting......: Squid : NO Mac address for {$ligne["pattern"]}\n";
				}
				
				$f[]=trim($ligne["pattern"]);
				
			}
			
			if(count($f)>0){
				$f[]="";
				$GLOBALS["HTTP_ACCESS"]["BANNED_COMPUTERS"]="http_access deny banned_computers";
				@file_put_contents("/etc/squid3/banned-computers.acl",@implode("\n",$f));
				$returned="acl banned_computers src \"/etc/squid3/banned-computers.acl\"\n";
			}
			
			if(count($MC)>0){
				$GLOBALS["HTTP_ACCESS"]["BANNED_COMPUTERS_MAC"]="http_access deny banned_mac_computers";
				@file_put_contents("/etc/squid3/banned-computers-by-mac.acl",@implode("\n",$MC));
				$returned=$returned."acl banned_mac_computers arp \"/etc/squid3/banned-computers-by-mac.acl\"\n";
			}
			
			$GLOBALS["ACL_BANNED_COMPUTERS_IP"]=$returned;
			return $returned;
			
		}
		
		private function ACL_WHITE_COMPUTERS_IP(){
			$f=array();
			$MC=array();
			echo "Starting......: Squid : Checking whitelisted addresses\n";
			if(isset($GLOBALS["ACL_WHITE_COMPUTERS_IP"])){
				echo "Starting......: Squid : Checking whitelisted addresses Already done.\n";
				return $GLOBALS["ACL_WHITE_COMPUTERS_IP"];
			}
			
			
			$returned=null;
			$sock=new sockets();
			if(!$this->ACL_ARP_ENABLED){
				echo "Starting......: Squid : acl ARP are disabled\n";
			}else{
				echo "Starting......: Squid : acl ARP are enabled\n";
			}
			
			$sql="SELECT ID,pattern,uid FROM dansguardian_files WHERE filename='exceptioniplist' AND RuleID=1 ORDER BY ID DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			$numnum=mysql_num_rows($results);
			echo "Starting......: Squid : Checking whitelisted $numnum entries\n";
			
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$MAC=null;
				if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne["pattern"])){continue;}
				if(trim($ligne["pattern"])==null){continue;}
				
				
				if($this->ACL_ARP_ENABLED){
					if(trim($ligne["uid"])<>null){
						$cmp=new computers($ligne["uid"]);
						$MAC=$cmp->ComputerMacAddress;
						$ligne["pattern"]=$cmp->ComputerIP;
						
					}
				
					if(!IsPhysicalAddress($MAC)){
						$MAC=$sock->getFrameWork("cmd.php?ip-to-mac={$ligne["pattern"]}");
						if(!IsPhysicalAddress($MAC)){$MAC=null;}
					}				
					if($MAC<>null){$MC[]=$MAC;continue;}
					echo "Starting......: Squid : NO Mac address for {$ligne["pattern"]}\n";
				}
				
				$f[]=trim($ligne["pattern"]);
				
			}
			
			echo "Starting......: Squid : ". count($f)." whitelisted addresse(s)\n";
			
			if(count($f)>0){
				$f[]="";
				$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS"]="http_access allow whitelisted_computers";
				$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"][]="url_rewrite_access deny whitelisted_computers";
				@file_put_contents("/etc/squid3/whitelisted-computers.acl",@implode("\n",$f));
				$returned="\nacl whitelisted_computers src \"/etc/squid3/whitelisted-computers.acl\n";
			}
			
			if(count($MC)>0){
				$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_MAC"]="http_access allow whitelisted_mac_computers";
				@file_put_contents("/etc/squid3/whitelisted-computers-by-mac.acl",@implode("\n",$MC));
				$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"][]="url_rewrite_access deny whitelisted_mac_computers";
				$returned=$returned."acl whitelisted_mac_computers arp \"/etc/squid3/whitelisted-computers-by-mac.acl\n";
			}
			
			$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"][]="url_rewrite_access allow all";
			$GLOBALS["ACL_WHITE_COMPUTERS_IP"]=$returned;
			return $returned;
			
		}		
		
		private function WCCP(){
			if($this->hasProxyTransparent<>1){return;}
			if($this->wccp2_enabled<>1){return;}
			$conf[]="";	
			$conf[]="#Cisco's Web Cache Coordination Protocol";
			$conf[]="wccp2_router $this->wccp2_router";
			$conf[]="wccp2_forwarding_method $this->wccp2_forwarding_method";
			$conf[]="wccp2_return_method $this->wccp2_return_method";
			$conf[]="wccp2_assignment_method $this->wccp2_assignment_method";
			$conf[]="";	
			return implode("\n",$conf);
		}
		
		
		private function ACL_MESSENGERS(){
			$sock=new sockets();
			$SquidMessengers=unserialize(base64_decode($sock->GET_INFO("SquidMessengers")));
			if($SquidMessengers["AOL"]==1){
				$conf[]="";
				$conf[]="# AOL Instant Messenger to connect to oscar.aol.com";
				$conf[]="acl AIM_ports port 5190 9898";
				$conf[]="acl AIM_domains dstdomain .oscar.aol.com .blue.aol.com";
				$conf[]="acl AIM_domains dstdomain .messaging.aol.com .aim.com";
				$conf[]="acl AIM_hosts dstdomain login.oscar.aol.com login.glogin.messaging.aol.com toc.oscar.aol.com";
				$conf[]="acl AIM_nets dst 64.12.0.0/255.255.0.0";
				$conf[]="acl AIM_methods method CONNECT";
				$GLOBALS["HTTP_ACCESS"]["AOL_MESSENGERS"]="http_access allow AIM_methods AIM_ports AIM_nets\nhttp_access allow AIM_methods AIM_ports AIM_hosts";				
			}
			
			if($SquidMessengers["IRC"]==1){	
				$conf[]="";		
				$conf[]="# Permit IRC";
				$conf[]="acl IRC_ports port 6667";
				$conf[]="acl IRC_domains dstdomain .freenode.net";
				$conf[]="acl IRC_hosts dstdomain  irc.freenode.net";
				$conf[]="acl IRC_methods method CONNECT";		
				$GLOBALS["HTTP_ACCESS"]["IRC_MESSENGERS"]="http_access allow IRC_methods IRC_ports IRC_hosts\nhttp_access allow IRC_methods IRC_ports IRC_domains";
				}
		
			if($SquidMessengers["YAHOO"]==1){
				$conf[]="";				
				$conf[]="# Permit Yahoo Messenger";
				$conf[]="acl YIM_ports port 5050";
				$conf[]="acl YIM_domains dstdomain .yahoo.com .yahoo.co.jp";
				$conf[]="acl YIM_hosts dstdomain scs.msg.yahoo.com cs.yahoo.co.jp";
				$conf[]="acl YIM_methods method CONNECT";
				$GLOBALS["HTTP_ACCESS"]["YAHOO_MESSENGERS"]="http_access allow YIM_methods YIM_ports YIM_hosts\nhttp_access allow YIM_methods YIM_ports YIM_domains";
			}
			
			if($SquidMessengers["GOOGLE"]==1){
				$conf[]="";
				$conf[]="# Permit Google Talk";
				$conf[]="acl GTALK_ports port 5222 5050 443";
				$conf[]="acl GTALK_domains dstdomain .google.com";
				$conf[]="acl GTALK_hosts dstdomain talk.google.com";
				$conf[]="acl GTALK_methods method CONNECT";		

				
				
				$GLOBALS["HTTP_ACCESS"]["GOOGLE_MESSENGERS"]="http_access allow  GTALK_ports GTALK_hosts GTALK_methods\n
				http_access allow GTALK_methods GTALK_ports GTALK_domains";
			}
			if($SquidMessengers["MSN"]==1){
				$conf[]="";
				$conf[]="# Permit MSN";
				$conf[]="acl MSN_ports port 1863 443 1503";
				$conf[]="acl MSN_domains dstdomain .microsoft.com .hotmail.com .live.com .msft.net .msn.com .passport.com";
				//$conf[]="acl MSN_hosts dstdomain messenger.hotmail.com";
				//$conf[]="acl MSN_nets dst 207.46.111.0/255.255.255.0";
				$conf[]="acl MSN_methods method CONNECT";	
				//$SquidMessengers_httpaccess[]="http_access allow MSN_methods MSN_ports MSN_hosts";
				//$SquidMessengers_httpaccess[]="http_access allow MSN_hosts";
				$SquidMessengers_httpaccess[]="http_access allow MSN_ports MSN_domains MSN_methods";
				//$SquidMessengers_httpaccess[]="http_access allow MSN_domains";
				//$SquidMessengers_httpaccess[]="http_access allow MSN_methods";
				//$SquidMessengers_httpaccess[]="http_access allow MSN_methods MSN_ports MSN_domains";
				//$SquidMessengers_httpaccess[]="http_access allow MSN_methods MSN_ports MSN_nets";
				$SquidMessengers_httpaccess[]="";
				
				$GLOBALS["HTTP_ACCESS"]["MSN_MESSENGERS"]=@implode("\n",$SquidMessengers_httpaccess);
			}
			
			if(!isset($conf)){return null;}
			if(!is_array($conf)){return null;}
			return @implode("\n",$conf)."\n\n";
			
		}
			
			
		
		private function WHITELISTED_AUTH(){
		$q=new mysql();
		if($this->ASROOT){echo "Starting......: Squid check whitelisted site(s)\n";}
		$sql="SELECT * FROM squid_white WHERE task_type='AUTH' ORDER BY uri";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$www=trim($ligne["uri"]);
			if($www==null){continue;}
			$l[]="acl WhiteListAuth dstdomain $www";}
		
			
		
		if(count($l)>0){
			if($this->ASROOT){echo "Starting......: Squid ". count($l)." whitelisted site(s)\n";}
			$GLOBALS["HTTP_ACCESS"]["WHITELISTED_AUTH"]="http_access allow WhiteListAuth";}
			return @implode("\n",$l);
		}
		
		private function WHITELISTED_AUTH_BROWSERS(){
			$sql="SELECT uri FROM squid_white WHERE task_type='AUTH_WL_USERAGENTS'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				if(trim($ligne["uri"])==null){continue;}
				$arrayUserAgents[$ligne["uri"]]=1;
			}
			
			if(count($arrayUserAgents)>0){
				$l[]="acl WhiteListedUserAgents browser \"/etc/squid3/white-listed-user-agents.acl\"";
				$GLOBALS["HTTP_ACCESS"]["WHITELISTED_BROWSERS"]="http_access allow WhiteListedUserAgents";
				return @implode("\n",$l);
			}
			
			
		}
		
		private function krb_auth_conf(){
			if(!$this->ASROOT){return;}
			$unix=new unix();
			$hostname_bin=$unix->find_program("hostname");
			exec("$hostname_bin -f 2>&1",$results);
			$myFullHostname=trim(@implode("", $results));
			if($this->EnableKerbAuth==0){return null;}
			if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
			$users=$GLOBALS["CLASS_USERS"];
			$binary=$users->squid_kerb_auth_path;
			if(strlen($binary)<2){
				return "#No squid_kerb_auth in compilation !\n";
			}
			
			echo "Starting......: Kerberos Authentification enabled\n";
			if(is_file("/etc/krb5.keytab")){
				$chown=$unix->find_program("chown");
				$chmod=$unix->find_program("chmod");
				shell_exec("$chown squid:squid /etc/krb5.keytab");
				shell_exec("$chmod 666 /etc/krb5.keytab");
			}
	
			$conf[]= "#--------- kerberos AUTH settings myhostname $myFullHostname\n";
			$conf[]= "auth_param negotiate program $users->squid_kerb_auth_path -d -s HTTP/$myFullHostname";
			$conf[]= "auth_param negotiate children 10";
			$conf[]= "auth_param negotiate keep_alive on";
			$conf[]= "auth_param basic realm Squid proxy-caching web server";
			$conf[]= "authenticate_cache_garbage_interval 10 seconds";
			$conf[]= "authenticate_ttl 0 hour";
			$conf[]= "#--------- kerberos ACL settings";
			$conf[]= "acl AUTHENTICATED proxy_auth REQUIRED";
			$conf[]=$this->WHITELISTED_AUTH();	
			$conf[]=$this->WHITELISTED_AUTH_BROWSERS();	
			$conf[]="";	
			return @implode("\n", $conf);		
		}
		
		
		private function ntlm_auth_conf(){
			// voir permissions on /var/lib/samba/winbindd_privileged
			// http://www.cyberciti.biz/faq/squid-ntlm-authentication-configuration-howto/
			if($this->EnableKerbAuth==1){return "#--------- NTLM AUTH settings\n#squid_kerb_auth enabled\n";}
			if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
			$users=$GLOBALS["CLASS_USERS"];
			if($this->NTLM_AUTH==0){return null;}
			if(!$users->SQUID_NTLM_ENABLED){
				$this->NTLM_AUTH=0;
				return "#No NTLM in compilation !\n";
			}
			
			if(trim($users->SQUID_NTLM_AUTH)==null){
				$this->NTLM_AUTH=0;
				return "#No NTLM auth_param basic program found !\n";
			}
			
			$smb=new samba();
			$domain=$smb->main_array["global"]["workgroup"];
			$server=$smb->main_array["global"]["netbios name"];
			if($server==null){$server=$users->hostname;}
			
			$conf[]= "#--------- NTLM AUTH settings\n";
			
			
			$conf[]= "\n#Authentification mode, building using squid compiled for artica";
			$conf[]= "auth_param ntlm program $users->SQUID_NTLM_AUTH --helper-protocol=squid-2.5-ntlmssp";
			$conf[]= "auth_param basic program $users->SQUID_NTLM_AUTH --helper-protocol=squid-2.5-basic";
			$conf[]= "auth_param ntlm children 15";
			$conf[]= "auth_param basic children 15";
			$conf[]= "auth_param basic credentialsttl 5 hours";	
			$conf[]= "auth_param basic casesensitive off";	
			$conf[]= "auth_param basic realm Squid proxy-caching web server";
			$conf[]= "authenticate_cache_garbage_interval 10 seconds";
			$conf[]= "authenticate_ttl 0 hour";
			$conf[]= "#--------- NTLM ACL settings";
			$conf[]= "acl ntlm_users proxy_auth REQUIRED";
			$conf[]=$this->WHITELISTED_AUTH();		
			$conf[]="";
			return implode("\n",$conf);
			if($this->ASROOT){
				if(is_dir("/var/lib/samba/winbindd_privileged")){
					shell_exec("/bin/chown root:squid /var/lib/samba/winbindd_privileged");
				}
				
				if(is_dir("/var/lib64/samba/winbindd_privileged")){
					shell_exec("/bin/chown root:squid /var/lib64/samba/winbindd_privileged");
				}				
			}
			
			}
			
			
	private function ISFiltersEnabled(){
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		if($users->DANSGUARDIAN_INSTALLED){if($this->enable_dansguardian==1){return true;}}
		if($users->SQUIDGUARD_INSTALLED){if($this->enable_squidguard==1){return true;}}
	}
	
	private function zapchain(){
		$redirect[]="\"/usr/bin/wrapzap\"";
		$squidguard=$this->zapchain_squidguard();
		$ufdbguard=$this->zapchain_ufdbguard();
		$squidclamav=$this->zapchain_squidclamav();
		if($squidclamav<>null){$redirect[]="\"$squidclamav\"";}
		if($squidguard<>null){$redirect[]="\"$squidguard\"";}
		if($ufdbguard<>null){$redirect[]="\"$ufdbguard\"";}
		
		if($this->ASROOT){echo "Starting......: zapchain: ". count($redirect)." redirector(s)\n";}
		
		if(count($redirect)>1){
			$conf[]= "url_rewrite_program /usr/bin/zapchain ". implode(" ",$redirect);
			$conf[]= "url_rewrite_children 20 startup=5 idle=1 concurrency=0\n";			
		}else{
			$conf[]= "url_rewrite_program ".implode("",$redirect);
			$conf[]= "url_rewrite_children 20 startup=5 idle=1 concurrency=0\n";					
		}
		
		return @implode("\n",$conf);
	}
	
	private function zapchain_ufdbguard(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
	$users=$GLOBALS["CLASS_USERS"];
		if(!$users->APP_UFDBGUARD_INSTALLED){return null;}
		if($this->enable_UfdbGuard==null){$this->enable_UfdbGuard=0;}
		if($this->enable_UfdbGuard==0){return null;}
		return "$users->ufdbgclient_path -l /var/log/squid";
	}
	
	private function zapchain_squidclamav(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
	$users=$GLOBALS["CLASS_USERS"];
		if(!$users->APP_SQUIDCLAMAV_INSTALLED){return null;}
		if($this->enable_squidclamav==null){$this->enable_squidclamav=0;}
		if($this->enable_squidclamav==0){return null;}
		return "$users->squidclamav_path /etc/squidclamav.conf";
	}	
	
	
	private function zapchain_squidguard(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
	$users=$GLOBALS["CLASS_USERS"];
		if(!$users->SQUIDGUARD_INSTALLED){
			if($this->ASROOT){echo "Starting......: zapchain: squidGuard Installed: FALSE\n";}
			return null;
		}
		if($this->enable_squidguard==0){
			if($this->ASROOT){echo "Starting......: zapchain: squidGuard enabled: FALSE\n";}
			return null;
		}
		
		
		$sock=new sockets();
		if($users->C_ICAP_INSTALLED){
			if($this->enable_cicap==1){
				$EnableSquidGuardInCiCAP=$sock->GET_INFO("EnableSquidGuardInCiCAP");
				if($EnableSquidGuardInCiCAP==null){$EnableSquidGuardInCiCAP=1;}
				if($EnableSquidGuardInCiCAP==1){$this->enable_squidguard=0;}
			}
		}

		if($this->ASROOT){echo "Starting......: zapchain: squidGuard in C-ICAP=$EnableSquidGuardInCiCAP\n";}
		if($this->ASROOT){echo "Starting......: zapchain: squidGuard Enabled=$this->enable_squidguard\n";}
		
		if($users->APP_UFDBGUARD_INSTALLED){if($this->enable_UfdbGuard==1){$this->enable_squidguard=0;}}
		if($this->enable_squidguard==0){return null;}
		$bin=trim($users->SQUIDGUARD_BIN_PATH);
		if($bin==null){
			$sock=new sockets();
			$bin=base64_decode($sock->getFrameWork("cmd.php?find-program=squidGard"));
			if(trim($bin)==null){
				if($this->ASROOT){echo "Starting......: Unable to activate squidGuard no path found\n";}
				return null;
			}
		}
		return "$bin -c /etc/squid/squidGuard.conf";
	}
	
	
	private function squidclamav(){
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		if($this->enable_adzapper==1){
			if($users->ADZAPPER_INSTALLED){
				if($this->ASROOT){echo "Starting......: Activate AdZapper\n";}
				return $this->zapchain();
				}
			}
		$conf[]= "\n#--------- squidClamAv";
		$conf[]= "\n#IS C-ICAP enabled = $this->enable_cicap";
		$conf[]= "url_rewrite_program $users->squidclamav_path /etc/squidclamav.conf";
		$conf[]= "url_rewrite_children 20 startup=5 idle=1 concurrency=0\n";
		if($this->ASROOT){echo "Starting......: Activate squidClamav \"$users->squidclamav_path\"\n";}
		return implode("\n",$conf);		
		}
	
	private function squidGuard(){
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		
		if($users->APP_SQUIDCLAMAV_INSTALLED){
			if($this->enable_squidclamav==1){
				return $this->squidclamav();
			}
		}
		
		
		if(!$users->SQUIDGUARD_INSTALLED){if(!$users->APP_UFDBGUARD_INSTALLED){if(!$users->ADZAPPER_INSTALLED){return null;}}}
		
		if($this->enable_adzapper==1){
			if($users->ADZAPPER_INSTALLED){
				if($this->ASROOT){echo "Starting......: Activate AdZapper\n";}
				return $this->zapchain();
			}
		}
		
		$sock=new sockets();
		$EnableSquidGuardInCiCAP=$sock->GET_INFO("EnableSquidGuardInCiCAP");
		if($EnableSquidGuardInCiCAP==null){$EnableSquidGuardInCiCAP=1;}
		if(!$users->C_ICAP_INSTALLED){$EnableSquidGuardInCiCAP=0;}
		if($this->enable_UfdbGuard==1){$this->enable_squidguard=0;$EnableSquidGuardInCiCAP=0;}
		
		if($EnableSquidGuardInCiCAP==1){
			if($this->enable_cicap==1){
				$conf[]= "\n#--------- squidGuard";
				$conf[]= "\n#transfered to C-ICAP -> EnableSquidGuardInCiCAP=1";
				return implode("\n",$conf);
			}
		}
		
		if($users->APP_UFDBGUARD_INSTALLED){
			if($this->enable_UfdbGuard<>1){
				$conf[]= "\n#--------- UfdbGuard";
				$conf[]= "\n#Disabled enable_UfdbGuard=$this->enable_UfdbGuard";
			}
			
			if($this->enable_UfdbGuard==1){
				$this->enable_dansguardian=0;
				if(strlen($users->ufdbgclient_path)==0){
					$conf[]= "\n#--------- UfdbGuard";
					$conf[]= "\n#Disabled unable to stat ufdbgclient";	
					return implode("\n",$conf);		
				}
				$conf[]= "\n#--------- UfdbGuard";
				$conf[]= "url_rewrite_program $users->ufdbgclient_path -l /var/log/squid";	
				$conf[]= "url_rewrite_children 20 startup=5 idle=1 concurrency=0\n";
				if($this->ASROOT){echo "Starting......: Activate UfdbGuard \"$users->ufdbgclient_path\"\n";}
				return implode("\n",$conf);	
				}
		}	
		
		if($this->enable_squidguard<>1){
			$conf[]= "\n#--------- squidGuard";
			$conf[]= "\n#Disabled enable_squidguard= $this->enable_squidguard";
			return implode("\n",$conf);			
			return null;
		
		}
		$this->enable_dansguardian=0;
		$bin=trim($users->SQUIDGUARD_BIN_PATH);
		if($bin==null){
			$sock=new sockets();
			$bin=base64_decode($sock->getFrameWork("cmd.php?find-program=squidGard"));
			if(trim($bin)==null){
				if($this->ASROOT){echo "Starting......: Unable to activate squidGuard no path found\n";}
				return null;
			}
		}
		
		if(posix_getuid()==0){
			$array=$this->SquidGuardDatabasesStatus();
			if(is_array($array)){
				$conf[]= "\n#--------- squidGard";
				while (list ($num, $val) = each ($array) ){
					$conf[]="#not compiled $val";
					
				}
				if($this->ASROOT){echo "Starting......: disable squidGuard \"". count($array)."\" databases not compiled\n";}
				return implode("\n",$conf);
				
			}
		}
			$conf[]= "\n#--------- squidGard";
			$conf[]= "\n#IS C-ICAP enabled = $this->enable_cicap";
			$conf[]= "url_rewrite_program $users->SQUIDGUARD_BIN_PATH";
			$conf[]= "url_rewrite_children 20 startup=5 idle=1 concurrency=0\n";
			if($this->ASROOT){echo "Starting......: Activate squidGuard \"$bin\"\n";}
			return implode("\n",$conf);
	}
	
	
	private function cache_peer(){
		if($this->EnableParentProxy==0){
			return "\n#--------- SQUID PARENTS (feature not enabled)\n";
		}
		
		$sql="SELECT * FROM squid_parents ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			return "\n#--------- SQUID PARENTS (ERROR $q->mysql_error)\n";
		}
		$f[]="";
		$f[]="";
		$f[]="#--------- SQUID PARENTS";
		$f[]="# using ssl ? http://hwoarang.silverarrow.org/2011/02/22/ssl-encryption-between-parent-and-child-squid-proxy/";
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(trim($ligne["servername"])==null){continue;}
			if(trim($ligne["server_port"])==null){continue;}
			if(trim($ligne["server_type"])==null){$ligne["server_type"]="parent";}
			if(strlen(trim($ligne["icp_port"]))==0){$ligne["icp_port"]=0;}
			if($ALREADY_CACHE_PEER[$ligne["servername"]]){continue;}
			$array=unserialize(base64_decode($ligne["options"]));
			if(!is_array($array)){$array=array("no-query"=>"" ,"no-digest"=>"");}
					
			while (list($num,$val)=each($array)){
				if(trim($num)==null){continue;}
				if(trim($val)<>null){$z[]="$num=$val";}
				else{$z[]=$num;}
			}
			$options=implode(" ",$z);
			unset($z);unset($array);
			$f[]="cache_peer {$ligne["servername"]} {$ligne["server_type"]} {$ligne["server_port"]} {$ligne["icp_port"]} $options";
			$ALREADY_CACHE_PEER[$ligne["servername"]]=true;
			}
		
		$f[]="";
		
		return implode("\n",$f);
		
	}
	
		
		
function BuildSquidConf(){
		$sock=new sockets();
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		$conf=null;
		$acl_access_time=null;
		$transparent=null;
		
		if($this->EnableUserAgentBanAll==1){
			$conf=$conf. "acl AllowedBrowsers browser \"/etc/squid3/allowed-user-agents.acl\"\n";
		}
		
		$SquidActHasReverse=$sock->GET_INFO("SquidActHasReverse");
		$EnableMalwarePatrol=$sock->GET_INFO("EnableMalwarePatrol");
		$EnableOpenDNSInProxy=$sock->GET_INFO("EnableOpenDNSInProxy");
		if($this->ASROOT){
			echo "Starting......: Squid Version $this->SQUID_BIN_VERSION\n";
			if($this->IS_30){if($GLOBALS["VERBOSE"]){echo "Starting......: Squid : is 3.x OK\n";}}
			if(!$this->IS_32){if($GLOBALS["VERBOSE"]){echo "Starting......: Squid : is NOT 3.2.x OK\n";}}
			if($this->IS_32){if($GLOBALS["VERBOSE"]){echo "Starting......: Squid : is 3.2.x OK\n";}}
		}
		if($this->IS_30){$conf=$conf . "# IS 3.0 YES\n";}
		if($this->IS_32){$conf=$conf . "# IS 3.2 YES\n";}
		if($this->IS_31){$conf=$conf . "# IS 3.1 YES\n";}  
		
		if($SquidActHasReverse<>1){
			$squidGuard=$this->squidGuard();
		}
		
		if(!$this->IS_31){
			$conf=$conf . "#Squid version is under 3.1x or 3.2x version...\n";
			if($this->enable_kavproxy==1){
				if($this->enable_cicap==1){
					$conf=$conf . "#C-ICAP was removed, this squid version did not accept more that 1 ICAP server\n";
					$this->enable_cicap=0;
		}}}else{
			if($this->enable_kavproxy==1){$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;}
			if($this->enable_cicap==1){$this->ICAP_SERVICES_COUNT=$this->ICAP_SERVICES_COUNT+1;}
		}
		
		
		
		$kav_acl="acl acl_kav_GET method GET\n";
		$conf=$conf ."acl localhost src 127.0.0.1/8 0.0.0.0/32\n";
		$conf=$conf ."acl to_localhost dst 127.0.0.1/8 0.0.0.0/32\n";	
		/*if($this->SQUID_BIN_VERSION>310000000000){
			//if($this->ASROOT){echo "Starting......: Add IPv6 address $this->SQUID_BIN_VERSION>310000000000\n";}
			//$conf=$conf ."acl localhost src ::1/128\n";
			//$conf=$conf ."acl to_localhost dst ::1/128\n";
		}else{
				
		}*/
		
		$kav_acl=$this->acls_multimedias();
		if(($this->IS_30) && (!$this->IS_32)){$conf=$conf. "acl manager proto cache_object\n";}
		if(($this->IS_31) && (!$this->IS_32)){$conf=$conf. "acl manager proto cache_object\n";}		
		
		$acl_blocked_sites="acl blockedsites url_regex \"/etc/squid3/squid-block.acl\"\n";
			
		if($this->hasProxyTransparent==1){$this->LDAP_AUTH=0;$this->NTLM_AUTH=0;}
		$conf=$conf . "auth_param basic credentialsttl 2 hour\n";
		$conf=$conf . "authenticate_ttl 1 hour\n";
		$conf=$conf . "authenticate_ip_ttl 60 seconds\n";
		$conf=$conf . $this->ntlm_auth_conf();
		$conf=$conf . $this->ldap_auth_conf();
		$conf=$conf . $this->krb_auth_conf();
		$conf=$conf . "#--------- TWEEKS PERFORMANCES\n";
		$conf=$conf . "# http://blog.last.fm/2007/08/30/squid-optimization-guide\n";
		$conf=$conf . "memory_pools off\n";
		$conf=$conf . "quick_abort_min 0 KB\n";
		$conf=$conf . "quick_abort_max 0 KB\n";
		$conf=$conf . "log_icp_queries off\n";
		$conf=$conf . "client_db off\n";
		$conf=$conf . "buffered_logs on\n";
		$conf=$conf . "half_closed_clients off\n";
		
		$GLOBALS["HTTP_ACCESS"]["BLOCKEDSITES"]="http_access deny blockedsites";
		$conf=$conf.$squidGuard;
		$conf=$conf . $this->cache_peer();
		
		

		$conf=$conf  ."\n#--------- acls\n";
		if($SquidActHasReverse<>1){
			if($EnableMalwarePatrol==1){
				$conf=$conf  ."acl malware_block_list url_regex -i \"/etc/squid3/malwares.acl\"\n";
			}
		}
		
		
		$conf=$conf  .$acl_blocked_sites;
		$conf=$conf. "acl CONNECT method CONNECT\n";
		$conf=$conf. "acl purge method PURGE\n";
		$conf=$conf. "acl FTP proto FTP\n";
		
		
		
		
		if(trim($users->SQUID_LDAP_AUTH)==1){
				if(is_array($this->acl_times)){
					$conf=$conf."#--------- TIME RESTRICTON\n";
					reset($this->acl_times);
					while (list ($num, $val) = each ($this->acl_times) ){
						if(!preg_match("#time:([0-9]+):(.+)#",$num,$re)){continue;}
							$re[2]=trim($re[2]);
							while (list ($a, $b) = each ($val)){
								$c[]="$a $b"; 
							}
							$conf=$conf."acl {$re[1]}_{$re[2]}_time time ". implode(" " ,$c)."\n";
							$http_access_time_array[]="http_access deny ldap_{$re[1]}_{$re[2]} !{$re[1]}_{$re[2]}_time";
							
							$acgroup="acl ldap_{$re[1]}_{$re[2]} external ldap_group {$re[1]}";
							$acl_groups[$acgroup]=$acgroup;
							
							}
							
					if(is_array($http_access_time_array)){
						$http_access_time=implode("\n",$http_access_time_array)."\n";
					}
				}
		}

		$conf=$conf . $kav_acl;
		$conf=$conf . "\n".$this->SQUID_LOCAL_NETWORKS()."\n";
		
	
		if($this->LDAP_AUTH==1){
			$conf=$conf ."acl group_password external ldap_group\n";
			if(is_array($acl_groups)){
				while (list ($a, $b) = each ($acl_groups)){
					$gprs[]=$b;
				}
				$conf=$conf ."\n#--------- GROUPS definition\n";
				$conf=$conf .implode("\n",$gprs);
			}else{
				$conf=$conf ."\n#--------- GROUPS definition\n";
				$conf=$conf ."#no groups";
			}
		}

		$conf=$conf."\n\n#--------- MAIN RULES...\n";

		$conf=$conf."always_direct allow FTP\n";		
		if($this->EnableParentProxy==1){
			$conf=$conf."\n#--------- RULES THAT FORCE TO PASS trough parent proxies except FTP\n";
			if($this->SSL_BUMP==0){
				$conf=$conf."always_direct deny all\n";
				$conf=$conf."prefer_direct off\n\n";
			}else{
				$conf=$conf."\n#--------- SSL bump is enabled always_direct is not set\n";
			}
		}		
		
		if($this->LDAP_AUTH==1){
				$acl_access_time=$http_access_time;
				$GLOBALS["HTTP_ACCESS"]["LDAP_GROUP"]="http_access allow group_password";
				$GLOBALS["HTTP_ACCESS"]["LDAP_AUTH"]="http_access allow ldapauth\n";
		}
		
		if($this->NTLM_AUTH==1){
				$acl_access_time=$http_access_time;
				$GLOBALS["HTTP_ACCESS"]["LDAP_AUTH"]="http_access allow ntlm_users\n";
				@mkdir("/var/cache/samba/winbindd_privileged",0755,true);
				if(posix_getuid()==0){shell_exec("chgrp squid /var/cache/samba/winbindd_privileged/");}
				$office_network=null;
		}	

		if($this->EnableKerbAuth==1){
				$acl_access_time=$http_access_time;
				$GLOBALS["HTTP_ACCESS"]["LDAP_AUTH"]="http_access allow AUTHENTICATED\n";
				$office_network=null;
		}		
		
		
		if($SquidActHasReverse<>1){
			if($this->enable_dansguardian==1){
				$office_network=null;
			}
		}		
		

		if($this->hasProxyTransparent==1){$transparent=" transparent";}
		$listen_port=$this->listen_port;
		if($this->enable_dansguardian==1){
			$listen_port=$this->alt_listen_port;	
		}
		
		$conf=$conf."# --------- SAFE ports\n";
		$conf=$conf.$this->SAFE_PORTS();
		
		$conf=$conf."#\n";

		if($EnableMalwarePatrol==1){
			$GLOBALS["HTTP_ACCESS"]["MALWARE_PATROL"]="http_access deny malware_block_list";
		}
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["request_header_max_size"],$re)){
				$request_header_max_size=$re[1];
				if(!is_numeric($request_header_max_size)){$request_header_max_size=0;}
				if($request_header_max_size>0){
					if($request_header_max_size<256){$request_header_max_size=256;}
					
				}
		}
		
	if(preg_match("#([0-9+])#",$this->global_conf_array["client_request_buffer_max_size"],$re)){
				$client_request_buffer_max_size=$re[1];
				if(!is_numeric($client_request_buffer_max_size)){$client_request_buffer_max_size=512;}
				if($client_request_buffer_max_size>0){
					if($client_request_buffer_max_size<512){$client_request_buffer_max_size=512;}
				}
		}		
		
		
		
		
		if($this->ASROOT){echo "Starting......: Squid request header max size {$this->global_conf_array["request_header_max_size"]} $request_header_max_size:KB\n";}			
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["reply_body_max_size"],$re)){
				$reply_body_max_size=$re[1];
				if($request_header_max_size==0){$reply_body_max_size=0;}
				if(!is_numeric($reply_body_max_size)){$reply_body_max_size=0;}
				if($reply_body_max_size<$request_header_max_size){$reply_body_max_size=$reply_body_max_size*2;}
			}
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["request_body_max_size"],$re)){
			$request_body_max_size=$re[1];
			if(!is_numeric($request_body_max_size)){$request_body_max_size=0;}			
		}
		if(preg_match("#([0-9+])#",$this->global_conf_array["reply_header_max_size"],$re)){
			$reply_header_max_size=$re[1];
			if(!is_numeric($reply_header_max_size)){$reply_header_max_size=64;}			
		}
		
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["maximum_object_size_in_memory"],$re)){
			$maximum_object_size_in_memory=$re[1];
			if(preg_match("#([A-Z]+)#",$this->global_conf_array["maximum_object_size_in_memory"],$re)){$maximum_object_size_in_memory_unit=$re[1];}
			if($maximum_object_size_in_memory_unit==null){$maximum_object_size_in_memory_unit="KB";}
			if($maximum_object_size_in_memory_unit=="KB"){
				if($maximum_object_size_in_memory<512){$maximum_object_size_in_memory=1024;}
			}
		}
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["minimum_object_size"],$re)){
			$minimum_object_size=$re[1];
			if(preg_match("#([A-Z]+)#",$this->global_conf_array["minimum_object_size"],$re)){$minimum_object_size_unit=$re[1];}
			if($minimum_object_size_unit==null){$minimum_object_size_unit="KB";}
			if(!is_numeric($minimum_object_size)){$minimum_object_size=0;}
		}		
		
		
		
		if(preg_match("#([0-9+])#",$this->global_conf_array["maximum_object_size"],$re)){
			$maximum_object_size=$re[1];
			if(preg_match("#([A-Z]+)#",$this->global_conf_array["maximum_object_size"],$re)){$maximum_object_size_unit=$re[1];}
			if($maximum_object_size_unit==null){$maximum_object_size_unit="KB";}
			if($maximum_object_size_unit=="KB"){
				if($maximum_object_size<4096){$maximum_object_size=4096;}
			}
				if($maximum_object_size_unit=="MB"){
					if($maximum_object_size<4){$maximum_object_size=4;}
				}
		}		
		
		$this->CheckVersion();
		
		if($this->ignore_expect_100==1){$ignore_expect_100="on";}else{$ignore_expect_100="off";}
			
		$conf=$conf .$acl_access_time;
		$conf=$conf. $this->SSL_PORTS();
		$conf=$conf .$this->ACL_FTP_RESTRICTIONS();
		$conf=$conf .$this->ACL_WHITE_COMPUTERS_IP();
		$conf=$conf .$this->ACL_BANNED_COMPUTERS_IP();
		$conf=$conf .$this->ACL_MESSENGERS();
		
		
		$conf=$conf."acl MULTIMEDIA rep_mime_type -i ^(audio\/x-mpegurl|audio\/mpeg|video\/flv|video\/x-flv|application\/x-shockwave-flash|audio\/ogg|video\/ogg|application\/ogg)$\n";
		$conf=$conf ."\n\n";
		$bandwith=new squid_bandwith_builder();
		$conf=$conf. $bandwith->compile()."\n";
		
		$conf=$conf."# ---------  RULES DEFINITIONS";
		$conf=$conf.$this->http_access();
		
		$icap=new icap();
		
		$conf=$conf."# --------- ICAP Services.($icap->ICAP_SERVICES_COUNT service(s))\n";
		$conf=$conf.$icap->icap_final_string;
		$conf=$conf."\n";
		$conf=$conf.$this->WCCP();
		$conf=$conf."# --------- ident_lookup_access\n";
		$conf=$conf."hierarchy_stoplist cgi-bin ?\n";
		$conf=$conf."\n";
		$conf=$conf."# --------- General settings \n";
		$conf=$conf."visible_hostname $this->visible_hostname\n";
		if(($this->IS_30) && (!$this->IS_32)){
			$conf=$conf."ignore_expect_100 $ignore_expect_100\n";
		}
		
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."# --------- time-out \n";
		$conf=$conf."dead_peer_timeout {$this->global_conf_array["dead_peer_timeout"]}\n";
		$conf=$conf."dns_timeout {$this->global_conf_array["dns_timeout"]}\n";
		$conf=$conf."connect_timeout 1600 seconds\n";
		$conf=$conf."persistent_request_timeout 3 minutes\n";
		$conf=$conf."pconn_timeout 1600 seconds\n"; 		
		$conf=$conf."\n";
		$conf=$conf."\n";
		if($this->EnableChangeRequestSize==1){
			$conf=$conf."# --------- Objects limits \n";
			$conf=$conf."request_body_max_size $request_body_max_size KB\n";
			$conf=$conf."reply_body_max_size $reply_body_max_size KB\n";
			$conf=$conf."request_header_max_size $request_header_max_size KB\n";
			$conf=$conf."reply_header_max_size $reply_header_max_size KB\n";
			$conf=$conf."client_request_buffer_max_size $client_request_buffer_max_size KB";
			
		}
		
		$conf=$conf."maximum_object_size {$this->global_conf_array["maximum_object_size"]}\n";
		$conf=$conf."minimum_object_size $minimum_object_size $minimum_object_size_unit\n";
		$conf=$conf."maximum_object_size_in_memory $maximum_object_size_in_memory $maximum_object_size_in_memory_unit\n";

		
		$conf=$conf."\n";
		$conf=$conf."\n";
		
		if($SquidActHasReverse==1){
			$conf=$conf."\n# -> Reverse Proxy is enabled <-\n";
			$transparent=" accel vhost";
			$listen_port=$sock->GET_INFO("SquidActHasReverseListenPort");
			if($listen_port==null){$listen_port=80;}
			$conf=$conf.$this->squid_reverse_websites();
		}
		
		$http_port_sslbump=$this->ssl_bump_port();
		
		
		$conf=$conf."#http/https ports\n";
		$conf=$conf."http_port $listen_port$transparent$http_port_sslbump\n";
		$conf=$conf.$this->ssl_bump_tranparent($listen_port);
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf ."# --------- SSL Rules \n";
		$conf=$conf .$this->ssl_bump_access();
		$conf=$conf."\n";
		
		$conf=$conf ."# --------- Caches \n";
		$conf=$conf . "cache_effective_user squid\n";
		$conf=$conf . "cache_effective_group squid\n";		
		$conf=$conf ."#cache_replacement_policy heap LFUDA\n";
		$conf=$conf .$this->CacheManager();
		
		$conf=$conf ."cache_mem {$this->global_conf_array["cache_mem"]}\n";
		$conf=$conf ."cache_swap_high {$this->global_conf_array["cache_swap_high"]}\n";
		$conf=$conf ."cache_swap_low {$this->global_conf_array["cache_swap_low"]}\n";
	
		$conf=$conf."# --------- DNS and ip caches \n";
		$conf=$conf ."ipcache_size {$this->global_conf_array["ipcache_size"]}\n";
		$conf=$conf ."ipcache_low {$this->global_conf_array["ipcache_low"]}\n";		
		$conf=$conf ."ipcache_high {$this->global_conf_array["ipcache_high"]}\n";				
		$conf=$conf ."fqdncache_size {$this->global_conf_array["fqdncache_size"]}\n";	

		writelogs("dns server: ".count($this->dns_array) . ' rows',__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("OpenDNS: $EnableOpenDNSInProxy",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($EnableOpenDNSInProxy==1){
			$conf=$conf ."\n\n# --------- OpenDNS DNS SERVERS \n";
			$arrayOpenDNS=unserialize(base64_decode($sock->GET_INFO("OpenDNSConfig")));
			if($arrayOpenDNS["OpenDNS1"]==null){$arrayOpenDNS["OpenDNS1"]="208.67.222.222";}
			if($arrayOpenDNS["OpenDNS2"]==null){$arrayOpenDNS["OpenDNS2"]="208.67.220.220";}
			writelogs("OpenDNS: {$arrayOpenDNS["OpenDNS2"]},{$arrayOpenDNS["OpenDNS2"]} ",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$conf=$conf . "dns_nameservers " . $arrayOpenDNS["OpenDNS1"] . " #OpenDNS server 1\n";
			$conf=$conf . "dns_nameservers " . $arrayOpenDNS["OpenDNS2"] . " #OpenDNS server 2\n";
			
		}else{
			if(is_array($this->dns_array)){
				reset($this->dns_array);
				$conf=$conf ."\n\n# --------- SPECIFIC DNS SERVERS \n";
				while (list ($num, $val) = each ($this->dns_array) ){
					$conf=$conf . "dns_nameservers " . $val . "\n";
					
				}
			}
		}

		$conf=$conf.$this->ftp_parameters();
		$conf=$conf."debug_options ALL,1\n";
		//$conf=$conf."cache deny QUERY\n";
		$conf=$conf."refresh_pattern ^ftp:		1440	20%	10080\n";
		$conf=$conf."refresh_pattern ^gopher:	1440	0%	1440\n";
		$conf=$conf.$this->refresh_pattern_list();
		$conf=$conf."refresh_pattern .		   0	20%	4320\n";
		$conf=$conf ."refresh_pattern -i (/cg-bin/|\?) 0 0% 0\n";
		$conf=$conf."icp_port 3130\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."#Logs-------------------------------------------------\n";
		//$conf=$conf."emulate_httpd_log on\n";
		
		if(($this->IS_30) && (!$this->IS_32)){
			if($this->ISFiltersEnabled()){
				$conf=$conf."#fqdn is disabled to provide IP addresses to filters\n";
				$conf=$conf."log_fqdn off\n";
			}else{
				$conf=$conf."#fqdn is disabled For sarg.\n";
				$conf=$conf."log_fqdn off\n";
			}
		}
		
		
		$conf=$conf."coredump_dir	/var/squid/cache\n";
		
		$conf=$conf."cache_log	/var/log/squid/cache.log\n";
		$conf=$conf."pid_filename	/var/run/squid.pid\n";
		
		if(($this->IS_30) && (!$this->IS_32)){
			$conf=$conf."cache_store_log	/var/log/squid/store.log\n";
			$conf=$conf."access_log	none manager\n";
			$conf=$conf."access_log /var/log/squid/access.log common\n";
			$conf=$conf."access_log /var/log/squid/sarg.log squid\n";
		}
		
		if(($this->IS_31) && (!$this->IS_32)){
			$conf=$conf."cache_store_log	/var/log/squid/store.log\n";
			$conf=$conf."access_log	none manager\n";
			$conf=$conf."access_log /var/log/squid/access.log common\n";
			$conf=$conf."access_log /var/log/squid/sarg.log squid\n";
		}		
		
		if($this->IS_32){
			$conf=$conf."logformat common MAC:%>eui %>a %[ui %[un [%tl] \"%rm %ru HTTP/%rv\" %>Hs %<st %Ss:%Sh\n"; 
			//$conf=$conf."access_log	none manager\n";
			$conf=$conf."cache_store_log	stdio:/var/log/squid/store.log\n";
			$conf=$conf."access_log daemon:/var/log/squid/access.log common\n";
			$conf=$conf."access_log daemon:/var/log/squid/sarg.log squid\n";			
		}
		
		
		
		if($this->enable_cicap==1){
			$conf=$conf."icap_log /var/log/squid/icap_access.log\n";
		}
		
		if($this->ASROOT){echo "Starting......: Squid Main cache \"{$this->CACHE_PATH}\" ({$this->CACHE_TYPE})\n";}
		$conf=$conf."\n";
		$conf=$conf."cache_dir	{$this->CACHE_TYPE} {$this->CACHE_PATH} {$this->CACHE_SIZE} 16 256\n";
		$conf=$conf."# --------- OTHER CACHES\n";
		if(is_array($this->cache_list)){
			reset($this->cache_list);
			while (list ($num, $val) = each ($this->cache_list)){
				if($this->ASROOT){echo "Starting......: other cache \"{$num}\" ({$this->cache_list[$num]["cache_type"]})\n";}
				$conf=$conf."cache_dir {$this->cache_list[$num]["cache_type"]} $num {$this->cache_list[$num]["cache_size"]} {$this->cache_list[$num]["cache_dir_level1"]} {$this->cache_list[$num]["cache_dir_level2"]}\n";
			}
		}
		
		$conf=$conf."\n";
		return $conf;	
		
		}
		
		private function CacheManager(){
			$sock=new sockets();
			$cache_mgr_user=$sock->GET_INFO("cache_mgr_user");
			$cachemgr_passwd=$sock->GET_INFO("cachemgr_passwd");
			if($cache_mgr_user<>null){
				if($this->ASROOT){echo "Starting......: Cache Manager set has $cache_mgr_user\n";} 
				$conf[]="";
				$conf[]="#Cache Manager Authentication";
				$conf[]="cache_mgr $cache_mgr_user";
				$conf[]="cachemgr_passwd $cachemgr_passwd all";
				$conf[]="";
			}else{
				if($this->ASROOT){echo "Starting......: Cache Manager is not set\n";} 
			}
			
		}
		
		private function http_access(){
			if(!isset($GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"])){$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"]=array();}
			
			$conf[]="url_rewrite_access deny localhost";
			if(count($GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"])>0){
				$conf[]=implode("\n",$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_REDIRECTOR"]);
			}
			
			if(isset($GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_MAC"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS_MAC"];}			
			if(isset($GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["WHITE_COMPUTERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["BANNED_COMPUTERS_MAC"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["BANNED_COMPUTERS_MAC"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["BANNED_COMPUTERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["BANNED_COMPUTERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["AOL_MESSENGERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["AOL_MESSENGERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["IRC_MESSENGERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["IRC_MESSENGERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["YAHOO_MESSENGERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["YAHOO_MESSENGERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["GOOGLE_MESSENGERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["GOOGLE_MESSENGERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["MSN_MESSENGERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["MSN_MESSENGERS"];}
			
			if($this->enable_ftp_restrictions){
				$conf[]="http_access allow FTP clients_ftp";
				$conf[]="http_access allow CONNECT clients_ftp";
			}			

			$conf[]="http_access deny !Safe_ports";
			$conf[]="http_access deny CONNECT !SSL_ports";
			$conf[]="http_access allow localhost";
			$conf[]="http_access allow manager localhost";
			$conf[]="http_access allow purge localhost";
			$conf[]="http_access deny purge";			
			if($this->EnableUserAgentBanAll==1){$conf[]="http_access deny !AllowedBrowsers";}		
			if(isset($GLOBALS["HTTP_ACCESS"]["BLOCKEDSITES"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["BLOCKEDSITES"];}	
			if(isset($GLOBALS["HTTP_ACCESS"]["MALWARE_PATROL"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["MALWARE_PATROL"];}			
			if(isset($GLOBALS["HTTP_ACCESS"]["WHITELISTED_AUTH"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["WHITELISTED_AUTH"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["WHITELISTED_BROWSERS"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["WHITELISTED_BROWSERS"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["LDAP_AUTH"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["LDAP_AUTH"];}
			if(isset($GLOBALS["HTTP_ACCESS"]["LDAP_GROUP"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["LDAP_GROUP"];}		
			if(isset($GLOBALS["HTTP_ACCESS"]["OFFICE_NETWORK"])){$conf[]=$GLOBALS["HTTP_ACCESS"]["OFFICE_NETWORK"];}
				
			if($this->allow_squid_localhost<>1){
				$conf[]="http_access deny to_localhost";
			}else{
				$conf[]="http_access allow to_localhost";
			}
			$conf[]="http_access deny all";
			while (list ($num, $val) = each ($conf) ){if(trim($val)==null){continue;}$f[]=$val;}
			return "\n".@implode("\n",$f)."\n";
		}
		
		
		
		private function ssl_bump_port(){
			if($this->SSL_BUMP==null){return null;}
			if($this->SSL_BUMP==0){return null;}
			if($this->hasProxyTransparent==1){return null;}
			writelogs("SQUID INTVAL: $this->intvalVersion",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($this->intvalVersion>316){
				return " ssl-bump cert=/etc/squid3/ssl/cacert.pem key=/etc/squid3/ssl/privkey.pem";
			}
			
			return " sslBump cert=/etc/squid3/ssl/cacert.pem key=/etc/squid3/ssl/privkey.pem";
		}
		
		private function ssl_bump_tranparent($listen_port){
			if($this->SSL_BUMP==null){return null;}
			if($this->SSL_BUMP==0){return null;}
			$listen_port=$listen_port+1;
			if($this->hasProxyTransparent==0){return null;}
			writelogs("SQUID INTVAL: $this->intvalVersion",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($this->intvalVersion>316){
				return "https_port $listen_port transparent ssl-bump cert=/etc/squid3/ssl/cacert.pem key=/etc/squid3/ssl/privkey.pem";
			}
			return "https_port $listen_port transparent sslBump cert=/etc/squid3/ssl/cacert.pem key=/etc/squid3/ssl/privkey.pem\n";  
		}
		
		private function ssl_bump_access(){
			if($this->SSL_BUMP==0){return null;}
			$sql="SELECT website_name FROM squid_ssl WHERE `type`='ssl-bump-wl' AND enabled=1 ORDER BY website_name LIMIT 0,50";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			$c=0;
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["website_name"]==null){continue;}
				$c=$c+1;
				$ligne["website_name"]=str_replace("https://","",$ligne["website_name"]);
				$www=$ligne["website_name"];
				$www_tbl=explode(".",$ligne["website_name"]);
				if(count($www_tbl)>2){
					unset($www_tbl[0]);
					$www=".".@implode(".",$www_tbl);
				}else{
					$www=".".$www;
				}
				
				$array[]="acl no_ssl_bump dstdomain {$www}";
			}
			
			if($c>0){
				$array[]="ssl_bump deny no_ssl_bump";
				
			}
			$array[]="ssl_bump allow all";
			$array[]="always_direct allow all";
			$array[]="";
			$array[]="";
			return @implode("\n",$array);
		}
		

		
		
		
		private function ftp_parameters(){	
				if(!isset($this->FTP_PARAMS["ftp_list_width"])){$this->FTP_PARAMS["ftp_list_width"]=32;}
				if(!isset($this->FTP_PARAMS["ftp_sanitycheck"])){$this->FTP_PARAMS["ftp_sanitycheck"]='yes';}
				if(!isset($this->FTP_PARAMS["ftp_epsv"])){$this->FTP_PARAMS["ftp_epsv"]='yes';}
				if(!isset($this->FTP_PARAMS["ftp_epsv_all"])){$this->FTP_PARAMS["ftp_epsv_all"]='no';}
				if(!isset($this->FTP_PARAMS["ftp_telnet_protocol"])){$this->FTP_PARAMS["ftp_telnet_protocol"]='yes';}		
				if(!isset($this->FTP_PARAMS["ftp_user"])){$this->FTP_PARAMS["ftp_user"]=null;}
				if(!isset($this->FTP_PARAMS["ftp_passive"])){$this->FTP_PARAMS["ftp_passive"]=1;}						
				
				
				if(!is_numeric($this->FTP_PARAMS["ftp_list_width"])){$this->FTP_PARAMS["ftp_list_width"]=32;}
				if($this->FTP_PARAMS["ftp_sanitycheck"]==null){$this->FTP_PARAMS["ftp_sanitycheck"]='yes';}
				if($this->FTP_PARAMS["ftp_epsv"]==null){$this->FTP_PARAMS["ftp_epsv"]='yes';}
				if($this->FTP_PARAMS["ftp_epsv_all"]==null){$this->FTP_PARAMS["ftp_epsv_all"]='no';}
				if($this->FTP_PARAMS["ftp_telnet_protocol"]==null){$this->FTP_PARAMS["ftp_telnet_protocol"]='yes';}		
				if(!isset($this->FTP_PARAMS["ftp_user"])){$this->FTP_PARAMS["ftp_user"]=null;}			
				
				$ftp_passive=$this->FTP_PARAMS["ftp_passive"];
				$ftp_sanitycheck=$this->FTP_PARAMS["ftp_sanitycheck"];
				$ftp_epsv=$this->FTP_PARAMS["ftp_sanitycheck"];
				$ftp_epsv_all=$this->FTP_PARAMS["ftp_epsv_all"];
				$ftp_telnet_protocol=$this->FTP_PARAMS["ftp_telnet_protocol"];
				
				if($ftp_passive==null){$ftp_passive=1;}	
				if($ftp_passive==1){$ftp_passive="on";}else{$ftp_passive="off";}
				
				if($ftp_sanitycheck==null){$ftp_sanitycheck=1;}	
				if($ftp_sanitycheck==1){$ftp_sanitycheck="on";}else{$ftp_sanitycheck="off";}				
				
				if($ftp_epsv==null){$ftp_epsv=1;}	
				if($ftp_epsv==1){$ftp_epsv="on";}else{$ftp_epsv="off";}				
				
				if($ftp_epsv_all==null){$ftp_epsv_all=0;}	
				if($ftp_epsv_all==1){$ftp_epsv_all="on";}else{$ftp_epsv_all="off";}				
								
				if($ftp_telnet_protocol==null){$ftp_telnet_protocol=0;}	
				if($ftp_telnet_protocol==1){$ftp_telnet_protocol="on";}else{$ftp_telnet_protocol="off";}		

				
				$conf[]="";
				$conf[]="#--------- FTP specific parameters";
				if(preg_match("#(.+?)@#",$this->FTP_PARAMS["ftp_user"])){
					$conf[]="ftp_user {$this->FTP_PARAMS["ftp_user"]}";
				}
				
				if(($this->IS_30) && (!$this->IS_32)){
					$conf[]="ftp_list_width {$this->FTP_PARAMS["ftp_list_width"]}";
				}
				$conf[]="ftp_passive $ftp_passive";
				$conf[]="ftp_sanitycheck $ftp_sanitycheck";
				if($this->IS_31){
					$conf[]="ftp_epsv $ftp_epsv";
					$conf[]="ftp_epsv_all $ftp_epsv_all";
				}
				$conf[]="ftp_telnet_protocol $ftp_telnet_protocol";
				$conf[]="";
				$conf[]="";
				return @implode("\n",$conf);
		}
		
		
		
	private function squid_reverse_websites(){
		
			$sql="SELECT * FROM squid_accel ORDER BY ID DESC";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
				$website_name=$ligne["website_name"];
				$website_name=str_replace("http://","",$website_name);
				$website_name=str_replace("https://","",$website_name);
				if(preg_match("#^.+?\.(.+?)\.(.+?)$#",$website_name,$re)){$acl_addwww=" .{$re[1]}.{$re[2]}";}else{$acl_addwww=$website_name;}
				$ip=$ligne["website_ip"];
				$port=$ligne["website_port"];
				if($port==null){$port=80;}
				if($website_name==null){continue;}
				if($ip==null){continue;}
				$website_name_name=str_replace(".","_",$website_name);
				$conf[]="cache_peer $ip parent $port 0 no-query no-digest originserver name=$website_name_name";
				$acls[]="acl acl_$website_name_name dstdomain $acl_addwww";
				$cache_peer_access[]="cache_peer_access $website_name_name allow acl_$website_name_name";
				$http_access[]="http_access allow acl_$website_name_name all";
				
			}
		
			if(is_array($conf)){
				$ii[]=implode("\n",$conf);
				$ii[]="";
				$ii[]=implode("\n",$acls);
				$ii[]="";
				$ii[]=implode("\n",$cache_peer_access);	
				$ii[]="";
				$ii[]=implode("\n",$http_access);		
				return "\n".implode("\n",$ii)."\n";						
			}
		
	}
		
private function refresh_pattern_list(){
	$sql="SELECT * FROM `squid_speed` WHERE `domain` IS NOT NULL";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["domain"]);
		if($pattern=='.'){
			$conf[]="refresh_pattern $pattern  {$ligne["refresh_pattern_min"]}    {$ligne["refresh_pattern_perc"]}%     {$ligne["refresh_pattern_max"]} {$ligne["refresh_pattern_options"]}";
			continue;
		}
		
		
		
		$pattern=str_replace(".","\.",$pattern);
		$pattern=str_replace("*",".*",$pattern);
		$conf[]="refresh_pattern $pattern  {$ligne["refresh_pattern_min"]}    {$ligne["refresh_pattern_perc"]}%     {$ligne["refresh_pattern_max"]}  {$ligne["refresh_pattern_options"]}";
		
	}
	if(!isset($conf)){return null;}
	if(is_array($conf)){return implode("\n",$conf)."\n";}
	
}

public function SQUID_LOCAL_NETWORKS(){
		$sock=new sockets();
		$AllowAllNetworksInSquid=$sock->GET_INFO("AllowAllNetworksInSquid");
		if(!is_numeric($AllowAllNetworksInSquid)){$AllowAllNetworksInSquid=1;}
		if($AllowAllNetworksInSquid==1){
			$GLOBALS["HTTP_ACCESS"]["OFFICE_NETWORK"]="http_access allow office_network";
			$conf[]="acl office_network src all";
			return @implode("\n",$conf);
		}
		$NetworkScannerMasks=$sock->GET_INFO('NetworkScannerMasks');
		$tbl=explode("\n",$NetworkScannerMasks);	
		if(is_array($tbl)){
			while (list ($num, $cidr) = each ($tbl)){
				if(trim($cidr)==null){continue;}
				$k[$cidr]=$cidr;
			}
		}

	if(count($this->network_array)>0){
			while (list ($num, $val) = each ($this->network_array)){
				if($val==null){continue;}
				$k[$val]=$val;
			}
		}
		
	if(!is_array($k)){return null;}
	while (list ($m, $l) = each ($k)){$s[]=$l;}
	$GLOBALS["HTTP_ACCESS"]["OFFICE_NETWORK"]="http_access allow office_network";
	$conf[]="acl office_network src " . implode(" ",$s);
	return @implode("\n",$conf);
}
		
		
		
function SAFE_PORTS(){
	$sock=new sockets();
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsList")));
	if(!is_array($ports)){$add=true;}
	if(count($ports)<2){$add=true;}
	if($add){
		$ports["80"]="http";
		$ports["22"]="ssh";
		$ports["443 563"]="https, snews";
		$ports["1863"]="msn";
		$ports["70"]="gopher";
		$ports["210"]="wais";
		$ports["1025-65535"]="unregistered ports";
		$ports["280"]="http-mgmt";
		$ports["488"]="gss-http";
		$ports["591"]="filemaker";
		$ports["777"]="multiling http";
		$ports["631"]="cups";
		$ports["873"]="rsync";
		$ports["901"]="SWAT";		
	}		
	
	
	if($this->enable_ftp_restrictions<>1){
		$ports["20"]="ftp-data";
		$ports["21"]="ftp";
	}

	while (list ($num, $val) = each ($ports) ){
		$conf[]="acl Safe_ports port $num\t#$val";		
	}
	
	return implode("\n",$conf);
	
}


private function SSL_PORTS(){
	$sock=new sockets();
	$ports=unserialize(base64_decode($sock->GET_INFO("SquidSafePortsSSLList")));
	if(!is_array($ports)){
		$ports["9000"]="Artica";
		$ports["443"]="HTTPS";
		$ports["563"]="https, snews";
		$ports["6667"]="tchat";
	}
	
	if(count($ports)==0){
		$ports["9000"]="Artica";
		$ports["443"]="HTTPS";
		$ports["563"]="https, snews";
		$ports["6667"]="tchat";	
	}
	
	while (list ($num, $val) = each ($ports) ){
		$conf[]="acl SSL_ports port $num\t#$val";		
	}
	$conf[]="";
	return implode("\n",$conf);
	
}

		
function acls_multimedias(){
		$conf=null;
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^video/x-ms-asf$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/vnd.ms.wms-hdr.asfv1$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/x-mms-framed$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^image/\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^video\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^audio\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/x-dvi$\n";
		$conf=$conf."acl multimedia_rep rep_mime_type -i ^application/x-isoview\n";
		$conf=$conf."acl multimedia_browsers browser -i ^.*player\n";
		$conf=$conf."acl bigfiles_types urlpath_regex -i \.(deb|rpm|iso|tar\.gz|gz|bz|tar|cue|nrg|crf|bwi|bwt|lcd|ccd|mdf|mds|vcd|cif|vdi|img)((\?|&).*)?$";

		return $conf;
		}
		
function external_ldap_ou($path){
	$ldap=new clladp();
	$ou_arr=$ldap->hash_get_ou();
	
if(is_array($ou_arr)){
	while (list ($num, $val) = each ($ou_arr)){		
		$conf=$conf . "external_acl_type ldap_{$val} %LOGIN " . dirname($path)."/squid_ldap_group -D \"cn=$ldap->ldap_admin,$ldap->suffix\"";
		$conf=$conf . " -w $ldap->ldap_password -b \"ou=$val,$ldap->suffix\"  -f \"(&(objectClass=posixGroup)(gidNumber=%a)(memberUid=%v))\" -S -v 3 -h 127.0.0.1\n";
		}
	return "$conf\n";
	}
}
}


class cicap{
	var $main_array=array();
	var $EnableClamavInCiCap=1;
	var $EnableCicapToSyslog=0;
	var $EnableSquidGuardInCiCAP=1;
	var $EnableUfdbGuard=0;
	
	function cicap(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$ini->loadString($sock->GET_INFO('CicapInternalConfiguration'));
		$this->main_array=$ini->_params;
		$this->BuildDefaults();
		$this->EnableClamavInCiCap=$sock->GET_INFO("EnableClamavInCiCap");
		$this->EnableCicapToSyslog=$sock->GET_INFO("EnableCicapToSyslog");
		$this->EnableSquidGuardInCiCAP=$sock->GET_INFO("EnableSquidGuardInCiCAP");
		$this->EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
		
		if($this->EnableSquidGuardInCiCAP==null){$this->EnableSquidGuardInCiCAP=1;}
		
		if($this->EnableClamavInCiCap==null){$this->EnableClamavInCiCap=1;}
		if($this->EnableCicapToSyslog==null){$this->EnableCicapToSyslog=0;}
		
		if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
		$users=$GLOBALS["CLASS_USERS"];
		if(!$users->SQUIDGUARD_INSTALLED){$this->EnableSquidGuardInCiCAP=0;}
		if($users->APP_UFDBGUARD_INSTALLED){
			if($this->EnableUfdbGuard==1){
				echo "Starting......: c-icap disabling squidGuard databases, UfdbGuard is enabled\n";
				$this->EnableSquidGuardInCiCAP=0;
				}
			}
		}
	
	function BuildDefaults(){
		if($this->main_array["CONF"]["Timeout"]==null){$this->main_array["CONF"]["Timeout"]=300;}
		if($this->main_array["CONF"]["KeepAlive"]==null){$this->main_array["CONF"]["KeepAlive"]="on";}
		if($this->main_array["CONF"]["MaxKeepAliveRequests"]==null){$this->main_array["CONF"]["MaxKeepAliveRequests"]="100";}
		if($this->main_array["CONF"]["KeepAliveTimeout"]==null){$this->main_array["CONF"]["KeepAliveTimeout"]="600";}
		if($this->main_array["CONF"]["StartServers"]==null){$this->main_array["CONF"]["StartServers"]="3";}
		if($this->main_array["CONF"]["MaxServers"]==null){$this->main_array["CONF"]["MaxServers"]="10";}
		if($this->main_array["CONF"]["MinSpareThreads"]==null){$this->main_array["CONF"]["MinSpareThreads"]="10";}
		if($this->main_array["CONF"]["MaxSpareThreads"]==null){$this->main_array["CONF"]["MaxSpareThreads"]="20";}
		if($this->main_array["CONF"]["ThreadsPerChild"]==null){$this->main_array["CONF"]["ThreadsPerChild"]="0";}
		if($this->main_array["CONF"]["MaxRequestsPerChild"]==null){$this->main_array["CONF"]["MaxRequestsPerChild"]="0";}
		if($this->main_array["CONF"]["srv_clamav.SendPercentData"]==null){$this->main_array["CONF"]["srv_clamav.SendPercentData"]="5";}
		if($this->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"]==null){$this->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"]="2";}
		if($this->main_array["CONF"]["srv_clamav.MaxObjectSize"]==null){$this->main_array["CONF"]["srv_clamav.MaxObjectSize"]="5";}
		if($this->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"]==null){$this->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"]="0";}
		if($this->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"]==null){$this->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"]="100";}		
		if($this->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"]==null){$this->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"]="5";}
		if($this->main_array["CONF"]["ThreadsPerChild"]==0){$this->main_array["CONF"]["ThreadsPerChild"]=10;}
		if($this->main_array["CONF"]["VirSaveDir"]==null){$this->main_array["CONF"]["VirSaveDir"]="/opt/artica/share/www/squid-attachments";}
		if($this->main_array["CONF"]["VirHTTPServer"]==null){$this->main_array["CONF"]["VirHTTPServer"]="https:///exec.cicap.php?usename=%f&remove=1&file=";}
		if($this->main_array["CONF"]["DebugLevel"]==null){$this->main_array["CONF"]["DebugLevel"]="3";}
		if($this->main_array["CONF"]["ViralatorMode"]==null){$this->main_array["CONF"]["ViralatorMode"]="0";}
		
		
		
	
	}
	
	private function LOCATE_CLAMD_CONF(){
		if(is_file("/etc/clamav/clamd.conf")){return "/etc/clamav/clamd.conf";}
		if(is_file("/etc/clamd.conf")){return "/etc/clamd.conf";}
	}
	
	public function LOCATE_MODULES_DIR(){
		if(is_file("/usr/lib/c_icap/sys_logger.so")){return "/usr/lib/c_icap";}
		if(is_file("/var/lib/c_icap/sys_logger.so")){return "/var/lib/c_icap";}
	}
	
	private function CLAMAV_USER(){
		$tbl=@explode("\n",@file_get_contents($this->LOCATE_CLAMD_CONF()));
		while (list ($num, $val) = each ($tbl)){	
			if(preg_match("#User\s+(.+)#",$val,$re)){return trim($re[1]);}
		}
	}
	
	private function DNBSL_LIST(){
		$sock=new sockets();
		$datas=explode("\n",$sock->GET_INFO("CicapDNSBL"));
		while (list ($num, $line) = each ($datas)){
			if(strlen($line)<4){continue;}
			$servicename=str_replace(".","",$line);
			$conf[]="url_check.LookupTableDB $servicename  domain dnsbl:$line";
			$servicenames[]=$servicename;
			}
		if(!is_array($conf)){return array();}
		
		$conf[]="url_check.Profile default block ". implode(" ",$servicenames);
		
		return $conf;
	}
	
	
	public function buildconf(){
		$this->BuildDefaults();
		$sock=new sockets();
		$SquidActHasReverse=$sock->GET_INFO("SquidActHasReverse");

			if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();}
			$users=$GLOBALS["CLASS_USERS"];
			$user="squid";
			$modules_path=$this->LOCATE_MODULES_DIR();
			echo "Starting......: c-icap using modules path in $modules_path\n";
			if(is_file("$modules_path/srv_clamav.so")){
				echo "Starting......: c-icap using ClamAV antivirus\n";
				$user=$this->CLAMAV_USER();
				
			}
			
			if(is_dir($GLOBALS["guarddb"])){shell_exec("/bin/chown -R $user:$user {$GLOBALS["guarddb"]}/*");}
			
			echo "Starting......: c-icap using User $user ($usermenus->hostname)\n";
			$conf[]="PidFile /var/run/c-icap.pid";
			$conf[]="CommandsSocket /var/run/c-icap/c-icap.ctl";
			$conf[]="Timeout {$this->main_array["CONF"]["Timeout"]}";
			//$conf[]="KeepAlive Off";
			$conf[]="MaxKeepAliveRequests {$this->main_array["CONF"]["MaxKeepAliveRequests"]}";
			$conf[]="KeepAliveTimeout {$this->main_array["CONF"]["KeepAliveTimeout"]}";
			$conf[]="StartServers {$this->main_array["CONF"]["StartServers"]}";
			$conf[]="MaxServers {$this->main_array["CONF"]["MaxServers"]}";
			$conf[]="MinSpareThreads     {$this->main_array["CONF"]["MinSpareThreads"]}";
			$conf[]="MaxSpareThreads     {$this->main_array["CONF"]["MaxSpareThreads"]}";
			$conf[]="ThreadsPerChild     {$this->main_array["CONF"]["ThreadsPerChild"]}";
			$conf[]="MaxRequestsPerChild  {$this->main_array["CONF"]["MaxRequestsPerChild"]}";
			$conf[]="MaxMemObject 131072";
			$conf[]="Port 1345 ";
			$conf[]="User $user";
			$conf[]="Group $user";
			$conf[]="ServerAdmin you@your.address";
			$conf[]="ServerName $usermenus->hostname";
			$conf[]="TmpDir /var/lib/c_icap/temporary";
			$conf[]="DebugLevel {$this->main_array["CONF"]["DebugLevel"]}"; #11 whas very verbose
			$conf[]="ModulesDir $modules_path";
			$conf[]="ServicesDir $modules_path";
			$conf[]="TemplateDir /usr/share/c_icap/templates/";		
			$conf[]="LoadMagicFile /etc/c-icap.magic";
			$conf[]="TemplateDefaultLanguage en";
			$conf[]="#TemplateReloadTime 360";
			$conf[]="#TemplateCacheSize 20";
			$conf[]="#TemplateMemBufSize 8192";
			
			$conf[]="";
			$conf[]="acl all src 0.0.0.0/0.0.0.0";
			//$conf[]="acl loopback src 127.0.0.1";
			$conf[]="";
			
			
			$conf[]="RemoteProxyUsers on";
			$conf[]="RemoteProxyUserHeader X-Authenticated-User";
			$conf[]="RemoteProxyUserHeaderEncoded on";
			$conf[]="LogFormat allFormat \"%tl;%a;%un;%iu;%is;%huo\""; 	
			//$conf[]="echo.PreviewSize 512";
			//$conf[]="echo.TransferIgnore gif, jpeg";
			$filelogger=true;
			if($this->EnableSquidGuardInCiCAP==1){
				echo "Starting......: c-icap using squidGuard databases\n";
				$icc=new cicap_filter();
				$conf[]=$icc->Buildacls();
			}else{
				echo "Starting......: c-icap disable squidGuard databases\n";
			}
			
			
			
			if($this->EnableCicapToSyslog==1){
			if(is_file("$modules_path/sys_logger.so")){
				$filelogger=false;
				echo "Starting......: c-icap using Sysloger\n";
				$conf[]="#Sysloger";
				$conf[]="Module logger sys_logger.so\n";
				$conf[]="Logger sys_logger";
				$conf[]="sys_logger.server_priority debug";				
				$conf[]="sys_logger.access_priority debug";
				$conf[]="sys_logger.Prefix \"C-ICAP:\"";
				$conf[]="sys_logger.Facility local1";
				$conf[]="sys_logger.LogFormat \"%tl;%a;%un;%iu;%is;%huo\"";
				$conf[]="";	
			}}
			if($filelogger){
				$conf[]="Logger file_logger";
				$conf[]="ServerLog /var/log/c-icap/server.log";
				$conf[]="AccessLog /var/log/c-icap/access.log allFormat all";				
				
			}
			
			if($SquidActHasReverse<>1){
				if(is_file("$modules_path/dnsbl_tables.so")){$conf[]="Module common bdb_tables.so";}
				$conf[]="Module common dnsbl_tables.so";
				$conf[]="Service url_check_module srv_url_check.so";
				$conf[]="";	
				
				if(is_file("$modules_path/dnsbl_tables.so")){
					if($sock->GET_INFO("DisableCicapDNBSL")<>1){
						$array=$this->DNBSL_LIST();
						echo "Starting......: c-icap using ". count($array) ." DNSBL services\n";
						if(count($array)>0){
							$this->cicap_whitelisted_servers();
							echo "Starting......: c-icap using DNSBL\n";
							$conf[]="#DNSBL";
							$conf[]="url_check.LookupTableDB whitelist domain hash:/etc/squid3/c-icap.whitelist.txt";
							$conf[]="url_check.Profile default pass whitelist";
							$conf[]=implode("\n",$this->DNBSL_LIST());
							$conf[]="";	
						}
					}else{
						echo "Starting......: c-icap Disabled DNSBL service\n";
					}
				}			
		
				if($this->EnableSquidGuardInCiCAP==1){$conf[]=$icc->GetSquidGuarddbs();}
			}
			
			
			echo "Starting......: c-icap clamav library: $modules_path/srv_clamav.so\n";
			if(is_file("$modules_path/srv_clamav.so")){
				if($this->EnableClamavInCiCap==1){
						if($this->main_array["CONF"]["VirSaveDir"]<>null){
							if(!is_dir($this->main_array["CONF"]["VirSaveDir"])){
								@mkdir($this->main_array["CONF"]["VirSaveDir"],0755,true);
							}
						}
						$conf[]="";
						$conf[]="#Clamav";
						$conf[]="Service antivirus_module srv_clamav.so srv_url_check.so";
						$conf[]="ServiceAlias  avscan srv_clamav?allow204=off&sizelimit=off&mode=simple";
						$conf[]="srv_clamav.ScanFileTypes TEXT DATA EXECUTABLE ARCHIVE MSOFFICE";
						$conf[]="srv_clamav.TransferIgnore flv, f4v, f4p, f4a, f4b, mpeg, mp2, mp3";
						$conf[]="srv_clamav.SendPercentData {$this->main_array["CONF"]["srv_clamav.SendPercentData"]}";
						$conf[]="srv_clamav.StartSendPercentDataAfter {$this->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"]}M";
						$conf[]="srv_clamav.Allow204Responces off";
						$conf[]="srv_clamav.MaxObjectSize  {$this->main_array["CONF"]["srv_clamav.MaxObjectSize"]}M";
						$conf[]="srv_clamav.ClamAvTmpDir /var/tmp";
						$conf[]="srv_clamav.ClamAvMaxFilesInArchive {$this->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"]}";
						$conf[]="srv_clamav.ClamAvMaxFileSizeInArchive {$this->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"]}M";
						$conf[]="srv_clamav.ClamAvMaxRecLevel {$this->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"]}";
						
						
						if($this->main_array["CONF"]["ViralatorMode"]==1){
							$sock->SET_INFO("CiCapViralatorMode",1);
							echo "Starting......: c-icap VirSaveDir: \"{$this->main_array["CONF"]["VirSaveDir"]}\"\n";
							$conf[]="srv_clamav.VirSaveDir {$this->main_array["CONF"]["VirSaveDir"]}";
							$conf[]="srv_clamav.VirHTTPServer  \"{$this->main_array["CONF"]["VirHTTPServer"]}\"";
							$conf[]="srv_clamav.VirUpdateTime   15";
							$conf[]="srv_clamav.VirScanFileTypes ARCHIVE EXECUTABLE";
						}else{
							$sock->SET_INFO("CiCapViralatorMode",0);	
						}
					}
				}
				
				
				
				
				@mkdir("/var/log/c-icap",0666,true);
				@mkdir("/var/lib/c_icap/temporary",0666,true);
				@mkdir("/var/run/c-icap",0666,true);
				@mkdir("/var/tmp",0666,true);
				@mkdir("/usr/share/c_icap/templates",0666,true);
				//if(!is_file("/var/log/c-icap/server.log")){@file_put_contents("/var/log/c-icap/server.log"," ");}
				//if(!is_file("/var/log/c-icap/access.log")){@file_put_contents("/var/log/c-icap/access.log"," ");}
				
				shell_exec("/bin/chmod -R 0755 /var/log/c-icap");
				shell_exec("/bin/chmod -R 0755 /var/run/c-icap");
				shell_exec("/bin/chmod -R 0755 /var/lib/c_icap");
				shell_exec("/bin/chmod -R 0755 /var/tmp");
				shell_exec("/bin/chmod -R 0755 /usr/share/c_icap");
				
				shell_exec("/bin/chown -R $user:$user /var/log/c-icap");
				shell_exec("/bin/chown -R $user:$user /var/run/c-icap");
				shell_exec("/bin/chown -R $user:$user /var/lib/c_icap");
				shell_exec("/bin/chown -R $user:$user /var/tmp");
				shell_exec("/bin/chown -R $user:$user /usr/share/c_icap");
				if(is_dir($this->main_array["CONF"]["VirSaveDir"])){
					shell_exec("/bin/chown -R $user:$user {$this->main_array["CONF"]["VirSaveDir"]}");
				}
				
				echo "Starting......: c-icap apply securities on user \"$user\" done\n";
				@file_put_contents("/etc/c-icap.conf",@implode("\n",$conf));
		
	}
	
	
	
	private function cicap_whitelisted_servers(){
		$sql="SELECT * FROM dansguardian_files WHERE filename='exceptionsitelist' AND RuleID=1 AND enabled=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
	
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$t[]=$ligne["pattern"];
		}
		
		@file_put_contents("/etc/squid3/c-icap.whitelist.txt",@implode("\n",$t));
		
	}
	
	
	function Save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->main_array;
		$confArtica=$ini->toString();
		$sock=new sockets();
		$sock->SaveConfigFile($confArtica,'CicapInternalConfiguration');
		$sock->getFrameWork("cmd.php?cicap-reconfigure=yes");
		$sock->getFrameWork("cmd.php?cicap-reload=yes");
		}
	
}
?>
