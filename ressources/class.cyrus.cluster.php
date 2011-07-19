<?php

include_once(dirname(__FILE__)."/class.ccurl.inc");
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.sockets.inc");
include_once(dirname(__FILE__)."/class.httpd.inc");

class cyrus_cluster{
	var $error=false;
	var $error_text="";
	var $replica_address="";
	var $replica_artica_port=9000;
	var $replica_username="";
	var $replica_password="";
	var $master_ip="";
	var $CyrusClusterPort;
	var $uri;
	
	public function cyrus_cluster(){
		$this->LoadReplicaInfos();
	}
	
	public function notify_disable_replica(){
		if($this->replica_address==null){
			$this->error=true;
			$this->error_text="{NO_NOTIFY_REPLICA_NO_ADRESS_SPECIFIED}";
			return false;
			}
		
		writelogs("Notify replica $this->replica_address:$this->replica_artica_port",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$array["admin"]=$this->replica_username;
		$array["pass"]=$this->replica_password;
		$array["cmd"]="disconnect";
		$datas=base64_encode(serialize($array));
		$curl=new ccurl($this->uri);
		$curl->parms["cyrus-cluster"]=$datas;		
		if(!$curl->get()){
			$this->error=true;
			$this->error_text=$curl->error;
			return false;
		}
		
		if(!$this->Is_error($curl->data)){return false;}	
		return true;	
	}
	
	public function is_a_replica(){
	if($this->replica_address==null){
			$this->error=true;
			$this->error_text="{NO_NOTIFY_REPLICA_NO_ADRESS_SPECIFIED}";
			return false;
			}		
		writelogs("Notify is replica ? $this->replica_address:$this->replica_artica_port",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		$array["admin"]=$this->replica_username;
		$array["pass"]=$this->replica_password;
		$array["cmd"]="isReplica";
		$datas=base64_encode(serialize($array));
		$curl=new ccurl($this->uri);
		$curl->parms["cyrus-cluster"]=$datas;		
		if(!$curl->get()){
			$this->error=true;
			$this->error_text=$curl->error;
			return false;
		}
		
		if(!$this->Is_error($curl->data)){return false;}	
		return true;			
		
		
	}
	
	
	public function notify_replica(){
		writelogs("Notify replica $this->replica_address:$this->replica_artica_port",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$uri="https://$this->replica_address:$this->replica_artica_port/cyrus.murder.listener.php";
		$ldap=new clladp();
		$apache=new httpd();
		$sock=new sockets();
		
		$array["admin"]=$this->replica_username;
		$array["pass"]=$this->replica_password;
		$array["master_ip"]=$this->master_ip;
		$array["suffix"]=$ldap->suffix;
		$array["ldap_admin"]=$ldap->ldap_admin;
		$array["ldap_password"]=$ldap->ldap_password;
		$array["master_artica_port"]=$apache->https_port;
		$array["master_cyrus_port"]=$this->CyrusClusterPort;
		$array["cmd"]="connect";
		$datas=base64_encode(serialize($array));
		$curl=new ccurl($uri);
		$curl->parms["cyrus-cluster"]=$datas;		
		if(!$curl->get()){
			$this->error=true;
			$this->error_text=$curl->error;
			return false;
		}
		
		if(!$this->Is_error($curl->data)){return false;}
		$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');
		return true;
	}
	
	private function LoadReplicaInfos(){
		$ini=new Bs_IniHandler();
		$user=new usersMenus();
		$sock=new sockets();
		$ini->loadString($sock->GET_INFO('CyrusClusterReplicaInfos'));
		$this->replica_address=$ini->_params["REPLICA"]["servername"];
		$this->replica_artica_port=$ini->_params["REPLICA"]["artica_port"];
		$this->replica_username=$ini->_params["REPLICA"]["username"];
		$this->replica_password=$ini->_params["REPLICA"]["password"];
		$this->master_ip=$ini->_params["REPLICA"]["master_ip"];
		if($this->replica_artica_port==null){$this->replica_artica_port=9000;}
		if($this->master_ip==null){$this->master_ip=$users->hostname;}		
		$this->CyrusClusterPort=$sock->GET_INFO("CyrusClusterPort");
		if($this->CyrusClusterPort==null){$this->CyrusClusterPort=2005;}
		$this->uri="https://$this->replica_address:$this->replica_artica_port/cyrus.murder.listener.php";
		
	}
	
	
	public function test_remote_server($ip,$port,$username,$password){
		writelogs("Testing remote server $ip:$port",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$uri="https://$ip:$port/cyrus.murder.listener.php";
		$array["admin"]=$username;
		$array["pass"]=$password;
		$array["cmd"]="tests";
		$datas=base64_encode(serialize($array));
		$curl=new ccurl($uri);
		$curl->parms["cyrus-cluster"]=$datas;
		if(!$curl->get()){
			$this->error=true;
			$this->error_text=$curl->error;
			return false;
		}
			return $this->Is_error($curl->data);
		}
	
	
	private function Is_error($http_datas){
		$datas=unserialize(base64_decode($http_datas));
		if(!is_array($datas)){
			$this->error=true;
			$this->error_text="{ARTICA_PROTOCOL_ERROR_OR_WRONG_VERSION}";
			return false;
		}
		
		writelogs("REPLY={$datas["REPLY"]} ({$datas["RESULT"]})",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!$datas["REPLY"]){
			$this->error=true;
			$this->error_text=$datas["RESULT"];
			return false;
		}
		
		return true;
	}
	
	
	
	
	
}
?>