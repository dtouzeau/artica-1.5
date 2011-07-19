<?php
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ini.inc');
include_once(dirname(__FILE__).'/class.system.network.inc');

class crossroads{
	
	var $dn;
	var $PostfixMasterServerIdentity;
	var $PostfixSlaveServersIdentity;
	var $PostFixSynchronizeStatus;
	var $array_PostFixSynchronizeStatus;
	var $CrossRoadsBalancingServerIP;
	var $CrossRoadsBalancingServerName;
	var $CrossRoadsPoolingTime;
	
	function crossroads(){
		
		$ldap=new clladp();
		$this->dn="cn=crossroads,cn=artica,$ldap->suffix";
		$user=new usersMenus();
		if(!$ldap->ExistsDN($this->dn)){
			$sys=new systeminfos();
			$sys->ParseIP();
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='BalancePostfixServers';
			$upd["PostfixMasterServerIdentity"][]=$user->hostname;
			$upd["CrossRoadsBalancingServerName"][]=$user->hostname;
			$upd["PostFixSynchronizeStatus"][]="#";
			$upd["CrossRoadsBalancingServerIP"][]=$sys->array_tcp_addr_num[0];
			$upd["CrossRoadsPoolingTime"][]='300';
			$upd["cn"]="crossroads";
			$ldap->ldap_add($this->dn,$upd);
			
		}
		
		
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=BalancePostfixServers)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->PostfixMasterServerIdentity=$hash[0][strtolower('PostfixMasterServerIdentity')][0];
			$this->PostFixSynchronizeStatus=$hash[0][strtolower('PostFixSynchronizeStatus')][0];
			
			if(!isset($hash[0][strtolower('CrossRoadsBalancingServerIP')])){
				$sys=new systeminfos();
				$sys->ParseIP();
				$this->CrossRoadsBalancingServerIP=$sys->array_tcp_addr_num[0];
				writelogs("Loading default CrossRoadsBalancingServerIP '$this->CrossRoadsBalancingServerIP'",__CLASS__.'/'.__FUNCTION__,__FILE__);
				
				$upd["CrossRoadsBalancingServerIP"]=$this->CrossRoadsBalancingServerIP;
				$ldap->Ldap_add_mod($this->dn,$upd);
				unset($upd);
			}
			
			if(!isset($hash[0][strtolower('CrossRoadsBalancingServerName')])){
				$this->CrossRoadsBalancingServerName=$user->hostname;
				writelogs("Loading default CrossRoadsBalancingServerName '$this->CrossRoadsBalancingServerName'",__CLASS__.'/'.__FUNCTION__,__FILE__);
				
				$upd["CrossRoadsBalancingServerName"]=$this->CrossRoadsBalancingServerName;
				$ldap->Ldap_add_mod($this->dn,$upd);
				unset($upd);
			}			
			
			
			
			$this->CrossRoadsPoolingTime=$hash[0][strtolower('CrossRoadsPoolingTime')][0];
			$this->CrossRoadsBalancingServerIP=$hash[0][strtolower('CrossRoadsBalancingServerIP')][0];
			$this->CrossRoadsBalancingServerName=$hash[0][strtolower('CrossRoadsBalancingServerName')][0];
			
			$ini=new Bs_IniHandler();
			$ini->loadString($this->PostFixSynchronizeStatus);
			$this->array_PostFixSynchronizeStatus=$ini->_params;
		
			for($i=0;$i<$hash[0][strtolower('PostfixSlaveServersIdentity')]["count"];$i++){
						$this->PostfixSlaveServersIdentity[]=$hash[0][strtolower('PostfixSlaveServersIdentity')][$i];
					}
			}

		if($this->PostfixMasterServerIdentity==null){$this->PostfixMasterServerIdentity=$user->hostname;}
		if($this->CrossRoadsBalancingServerName==null){$this->CrossRoadsBalancingServerName=$user->hostname;}
		if($this->CrossRoadsPoolingTime==null){$this->CrossRoadsPoolingTime=300;}
		
		
		if($this->CrossRoadsBalancingServerIP==null){
			$sys=new systeminfos();
			$sys->ParseIP();
			
			$this->CrossRoadsBalancingServerIP=$sys->array_tcp_addr_num[0];
			$this->SaveToLdap();
		}
		
		writelogs("CrossRoadsBalancingServerIP='$this->CrossRoadsBalancingServerIP'",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
	}
	
	function SaveToLdap(){
		$ldap=new clladp();
		$upd["PostfixMasterServerIdentity"][0]=$this->PostfixMasterServerIdentity;
		$ini=new Bs_IniHandler();
		$ini->_params=$this->array_PostFixSynchronizeStatus;
		$upd["PostFixSynchronizeStatus"][]=$ini->toString();		
		$upd["CrossRoadsBalancingServerIP"][]=$this->CrossRoadsBalancingServerIP;
		$upd["CrossRoadsBalancingServerName"][]=$this->CrossRoadsBalancingServerName;
		$upd["CrossRoadsPoolingTime"][]=$this->CrossRoadsPoolingTime;
		
		writelogs("Save to ldap server",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(!$ldap->Ldap_modify($this->dn,$upd)){return $ldap->ldap_last_error;}
		else {$tpl=new templates();echo $tpl->_ENGINE_parse_body('{success}');}
		
	}
	
	function AddPostfixSlaveServer($server_ip){
		$this->PostfixSlaveServersIdentity[]=$server_ip;
		$upd["PostfixSlaveServersIdentity"]=$server_ip;
		
		$ldap=new clladp();
		if(!$ldap->Ldap_add_mod($this->dn,$upd)){echo $ldap->ldap_last_error;}else{
			$tpl=new templates();echo $tpl->_ENGINE_parse_body('{success}');
		}
		
		}
		
	function DeletePostfixSlaveServer($num){
		$server_ip=$this->PostfixSlaveServersIdentity[$num];
		$upd["PostfixSlaveServersIdentity"]=$server_ip;
		$ldap=new clladp();
		if(!$ldap->Ldap_del_mod($this->dn,$upd)){echo $ldap->ldap_last_error;}else{
			$tpl=new templates();echo $tpl->_ENGINE_parse_body('{success}');
		}
		
	}
		
		
function SynchronizeSlaves(){
	if(!is_array($this->PostfixSlaveServersIdentity)){return null;}
	$sock=new sockets();
	$sock->getfile('CrossRoadsSyncSlaves');
	}
	
	
}