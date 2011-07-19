<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.user.inc");


class nfs{
	
	var $main_array;
	var $SanClusterBasePath;
	var $SanClusterClients;
	
	function nfs(){
		$this->loadExport();
		$this->SanClusterClients=$this->LOAD_NFS_CLUSTER_CLIENTS();
		$sock=new sockets();
		$this->SanClusterBasePath=$sock->GET_INFO('SanClusterBasePath');		
	}
	
	
	private function loadExport(){
		$sock=new sockets();
		$datas=$sock->GET_INFO('NFSExportConfig');
		$tbl=explode("\n",$datas);
		if(!is_array($tbl)){return null;}
		while (list ($num, $line) = each ($tbl) ){
			if($line==null){continue;}
			if(preg_match("#^(.+?)\s+(.+)#",$line,$re)){
				if($this->SanClusterBasePath==$re[1]){continue;}
				$this->main_array[$re[1]]["C"]=$this->ExplodeAcls($re[2]);
			}
			
			
		}
	}
	
	
	private function SAVE_NFS_CLUSTER_CLIENTS(){
		reset($this->SanClusterClients);
		while (list ($num, $line) = each ($this->SanClusterClients) ){
			$conf=$conf ."$num\n";
		}
		$sock=new sockets();
		$sock->SaveConfigFile($conf,"SanClusterClients");
	}
	
	private function LOAD_NFS_CLUSTER_CLIENTS(){
		$sock=new sockets();
		$datas=$sock->GET_INFO("SanClusterClients");
		$tbl=explode("\n",$datas);
		if(!is_array($tbl)){return array();}
		while (list ($num, $line) = each ($tbl) ){
			if(trim($line)==null){continue;}
			$array[$line]=true;
		}
		
		if(!is_array($array)){return array();}
		return $array;
	}
	
	
	function NFS_CLUSTER_ADD_CLIENT($serverip){
		if($serverip==null){return null;}
		$this->SanClusterClients[$serverip]=true;
		$this->SAVE_NFS_CLUSTER_CLIENTS();
	}
	
	function AddFolder($folder){
		if(!is_array($this->main_array[$folder])){
			$this->main_array[$folder]=array();
		}
		
	}
	
	public function SaveToServer(){
		$conf=$this->Build();
		$sock=new sockets();
		$sock->SaveConfigFile($conf,"NFSExportConfig");
		$sock->getFrameWork('cmd.php?reload-nfs=yes');
		}
	
	public function AddACLComputer($folder,$uid,$r=false,$w=false){
		if($r){$acl="ro";}
		if($w){$acl="rw";}
		$this->main_array[trim($folder)]["C"][trim($uid)]=$acl;
		$this->SaveToServer();
	}
	
	private function ExplodeAcls($line){
		$tbl=explode(" ",$line);
		if(!is_array($tbl)){return array();}
		while (list ($num, $line) = each ($tbl) ){
			if(preg_match("#(.+?)\((.+?)\)#",$line,$re)){
				if(preg_match("#ro#",$re[2])){$acl="ro";}
				if(preg_match("#rw#",$re[2])){$acl="rw";}
				
				$array[$re[1]]=$acl;
			}
			
		}
		return $array;
		
	}
	

	
	private function Build(){
		if(is_array($this->main_array)){
			reset($this->main_array);
			while (list ($folder, $array) = each ($this->main_array) ){
				$conf=$conf . "$folder " .$this->BuildAcls($array["C"])."\n";
				}
		}
		
		if($this->SanClusterBasePath<>null){
			if(is_array($this->SanClusterClients)){
				reset($this->SanClusterClients);
				while (list ($client, $null) = each ($this->SanClusterClients) ){
					if(trim($client)==null){continue;}
					$clientrows[]="$client(rw,fsid=0,sync,insecure,no_subtree_check,no_root_squash)";
						
				}
				
				if(is_array($clientrows)){
					$conf=$conf . "$this->SanClusterBasePath ".implode(" ",$clientrows);
				}
				
			}
		}
		
		
		return $conf;
		}
	
	private function BuildAcls($array){
		while (list ($user, $acl) = each ($array) ){
			if(trim($user)==null){continue;}
			$conf[]="$user($acl,no_root_squash,no_subtree_check)";
			}
	
	
			if(is_array($conf)){
				$v=implode(" ",$conf);
				return $v;
			}
	}

}


?>