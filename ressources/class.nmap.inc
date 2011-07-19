<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__) . '/class.system.network.inc');
include_once(dirname(__FILE__) . '/class.tcpip.inc');


class nmap{
	var $dn;
	var $main_array=array();
	var $NmapRotateMinutes;
	var $NmapNetworkIP=array();
	var $Disclaimer;
	var $ldap_error;

	
	function nmap(){
		$this->verifyBranch();
		$this->LoadSettings();
		$this->BuildDefault();
		}
	
function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=nmap-scanner,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='NmapSettings';
		$upd["cn"]="nmap-scanner";
		$upd["NmapRotateMinutes"][]=60;
		$ldap->ldap_add($this->dn,$upd);
	}
}

function LoadSettings(){
		$ldap=new clladp();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=NmapSettings)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->NmapRotateMinutes=$hash[0][strtolower('NmapRotateMinutes')][0];
			
			for($i=0;$i<$hash[0]["nmapnetworkip"]["count"];$i++){
				
				$this->NmapNetworkIP[]=$hash[0]["nmapnetworkip"][$i];
			}
			
			
		}
		
		$this->BuildDefault();
		
		
		
}

function MyDefaultNetwork(){
	$sys=new ifconfig();
	writelogs("Building default netwoks....",__CLASS__.'/'.__FUNCTION__,__FILE__);
	while (list ($num, $ligne) = each ($sys->main_array) ){
		$ip=$ligne["ip"];
		$netmask=$ligne["mask"];
		
		if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)([0-9]+)#',$netmask,$re)){
			$netmask="{$re[1]}.{$re[2]}.{$re[3]}.{$re[4]}";
		}
		
		if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)([0-9]+)#',$ip,$re)){
			$ip="{$re[1]}.{$re[2]}.{$re[3]}.0";
		}	
		
		if(($ip<>null)&&($netmask<>null)){	
			if($ip<>"127.0.0.1"){
				writelogs("Building default networks SecondIP for ip=$ip, netmask=$netmask....",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$sip=new SecondIP($ip,$netmask);
				$cdir=$sip->CDR;
				$this->addIpToscan($cdir);
				}
			}
	}
}

function addIpToscan($iporcdir){
	$up["NmapNetworkIP"]=$iporcdir;
	$ldap=new clladp();
	writelogs("Adding  $iporcdir netwoks....",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$res=$ldap->Ldap_add_mod($this->dn,$up);
	$this->ldap_error=$ldap->ldap_last_error;
	return $res;
	}
	
function delIpToscan($array_num){
	$up["NmapNetworkIP"]=$this->NmapNetworkIP[$array_num];
	$ldap=new clladp();
	$res=$ldap->Ldap_del_mod($this->dn,$up);
	$this->ldap_error=$ldap->ldap_last_error;
	$i=new nmap();
	writelogs("Number of networks=".count($i->NmapNetworkIP),__CLASS__.'/'.__FUNCTION__,__FILE__);
	if(count($i->NmapNetworkIP)==0){$this->MyDefaultNetwork();}
	
	return $res;
	}	


function SaveConf(){
	$ldap=new clladp();
	$upd["NmapRotateMinutes"][]=$this->NmapRotateMinutes;
	if(!$ldap->Ldap_modify($this->dn,$upd)){
		echo $ldap->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("NMAP:{success}\n");
	}
}

function BuildDefault(){
	if($this->NmapRotateMinutes==null){$this->NmapRotateMinutes=60;}
	writelogs("Number of networks=".count($this->NmapNetworkIP),__CLASS__.'/'.__FUNCTION__,__FILE__);
	if(count($this->NmapNetworkIP)==0){$this->MyDefaultNetwork();}
	}


	
	function SaveToserver(){
		$users=new usersMenus();
		$sock=new sockets();
		//$sock->getfile('dkimfiltersave');
		}






}