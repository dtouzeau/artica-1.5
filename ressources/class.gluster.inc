<?php
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.templates.inc');
	
	
class gluster{
	var $clients=array();
	var $PARAMS_CLIENTS=array();
	var $STATUS_CLIENTS=array();
	var $CLUSTERED_DIRECTORIES=array();
	
	public function gluster(){
		$this->ClientList();
		$this->BuildBricks();
	}
	
	
	private function ClientList(){
		$sock=new sockets();
		
		$datas=$sock->getFrameWork("cmd.php?cluster-client-list=yes");
		$datas=explode("\n",$datas);
		$this->PARAMS_CLIENTS=$ini->_params;
			while (list ($num, $ligne) = each ($datas) ){
				if($ligne==null){continue;}
				if($ligne=="127.0.0.1"){continue;}
				if(preg_match("#=#",$ligne)){continue;}
				$this->clients[$ligne]=$ligne;
				$this->client_status($ligne);
			}
			
			
		}
		
		
	
	
	private function client_status($name){
		$sock=new sockets();
		$datas=$sock->GET_CLUSTER("clusters-$name");
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$this->STATUS_CLIENTS[$name]=$ini->_params["CLUSTER"];
		$datas=$sock->GET_CLUSTER("notify-$name");
		
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$this->PARAMS_CLIENTS[$name]=$ini->_params["PARAMS"];		
		
		
	}
	
	
	public function Save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->PARAMS_CLIENTS;
		$sock=new sockets();
		$sock->SaveClusterConfigFile($ini->toString(),"ClusterClientsList");
		
	}
	
	private function BuildBricks(){
	if(!is_array($this->clients)){return null;}
	$clients=$this->clients;
	while (list ($num, $name) = each ($clients) ){
			$briks=explode(",",$this->STATUS_CLIENTS[$name]["bricks"]);
			if(is_array($briks)){
				while (list ($index, $brick) = each ($briks) ){
					$folder=$this->STATUS_CLIENTS[$name][$brick];
					$this->CLUSTERED_DIRECTORIES[$folder][]=array("CLIENT"=>$name,"BRICK"=>$brick);
				}
			}
		
		}
	
	}
	
	
	
	
	
	public function BuildDispatcher(){
		
		$directories=$this->CLUSTERED_DIRECTORIES;
		shell_exec("/bin/rm -f /etc/artica-cluster/dispatcher*");
		$count=0;
		while (list ($folder, $array) = each ($directories)){			
			$count++;
			echo "Starting......: Gluster folder $folder\n";
			echo "Starting......: Gluster replicator configuration file /etc/artica-cluster/dispatcher-$count.vol\n";
			$this->BuildDispatcherClient($folder,$array,"dispatcher-$count.vol");
		}
		
		
		
	}
			
			
		private function BuildDispatcherClient($folder,$array,$fileconf){	
			
			
			$array_conf[]="\n#-----------------------------------------------------"; 
			$array_conf[]="#bricks on folder $folder"; 
			
			while (list ($index, $bricks_array) = each ($array) ){
					$count++;
					$brick=$bricks_array["BRICK"];
					$computer=$bricks_array["CLIENT"];
					$volume="$brick-$count";
					$array_conf[]="volume $volume";
					$array_conf[]="\t type protocol/client";
		    		$array_conf[]="\t option transport-type tcp/client";
		    		$array_conf[]="\t option remote-host $computer  # IP of storage node $computer";
		    		$array_conf[]="\t option remote-subvolume $brick # $folder";
					$array_conf[]="end-volume\n\n";	
					$bb_array[$folder][]="$volume";
					
				}	
			
			
			$count=0;
			
			while (list ($folder, $listvols) = each ($bb_array) ){
				$count++;
				$implode=implode(" ",$listvols);
				$array_conf[]="# <DIR>$folder-clustered</DIR>";
				$array_conf[]="\n#-----------------------------------------------------"; 
				$array_conf[]="#bind bricks $implode together for folder $folder"; 
				$array_conf[]="volume cluster-$count"; 
			    $array_conf[]="\t type cluster/replicate"; 
			    $array_conf[]="\t subvolumes $implode"; 
			    $array_conf[]="\t option replicate *:2"; 
				$array_conf[]="end-volume\n\n"; 
				$afrs[]="cluster-$count";
				
				
			}
			
				
				$array_conf[]="#Create a mirror of ". implode(",",$afrs);
				$array_conf[]="volume distribute"; 
			    $array_conf[]="\t type cluster/replicate"; 
			    $array_conf[]="\t subvolumes ".implode(" ",$afrs);  
				$array_conf[]="end-volume\n\n"; 
							
				$array_conf[]="volume writebehind"; 
				$array_conf[]="  type performance/write-behind"; 
				$array_conf[]="  option window-size 4MB"; 
				$array_conf[]="  subvolumes distribute"; 
				$array_conf[]="end-volume\n\n"; 
				
				$array_conf[]="volume readahead"; 
				$array_conf[]="  type performance/read-ahead"; 
				$array_conf[]="  option page-count 4"; 
				$array_conf[]="  subvolumes writebehind"; 
				$array_conf[]="end-volume\n\n";	
				
				$array_conf[]="volume iocache"; 
				$array_conf[]="  type performance/io-cache"; 
				$array_conf[]="  option cache-size 1GB"; 
				$array_conf[]="  subvolumes readahead"; 
				$array_conf[]="end-volume\n\n";					

				$array_conf[]="volume quickread"; 
				$array_conf[]="  type performance/quick-read"; 
				$array_conf[]="  option max-file-size 64kB"; 
				$array_conf[]="  subvolumes iocache"; 
				$array_conf[]="end-volume\n\n";					

				$array_conf[]="volume statprefetch"; 
				$array_conf[]="  type performance/stat-prefetch"; 
 				$array_conf[]="  subvolumes quickread"; 
				$array_conf[]="end-volume\n\n"; 
				
				$conf=implode("\n",$array_conf);
				@file_put_contents("/etc/artica-cluster/$fileconf",$conf);
			}



}

class GlusterClient{
	var $PARAMS;
	var $CLUSTERED_FOLDERS=array();
	var $CLUSTERED_BRIKS=array();
	
	
public function glusterClient(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_CLUSTER('ClusterCLientSettings'));
		$this->PARAMS=$ini->_params["CONF"];	
		$this->GetCLusteredFolders();
		
		
	}
	
	public function Save(){
		$ini=new Bs_IniHandler();
		$this->DeleteReplicator();
		$ini->_params["CONF"]=$this->PARAMS;
		$sock=new sockets();
		if($GLOBALS["VERBOSE"]){echo "Save conf files..\n";}
		$sock->SaveClusterConfigFile($ini->toString(),"ClusterCLientSettings");
		$sock->SaveClusterConfigFile($this->BuildServerConf(),"glusterfs-server.vol");
		
	}
	
	
	private function DeleteReplicator(){
		if($this->PARAMS["delete_replicator"]==1){
			$sock=new sockets();
			$sock->SET_CLUSTER('EnableReplicator',0);
			$this->PARAMS["delete_replicator"]=0;
		}
		
		if($this->PARAMS["add_replicator"]==1){
			$sock=new sockets();
			$sock->SET_CLUSTER('EnableReplicator',1);
		}		
		
	}
	

	private function GetCLusteredFolders(){
		$users=new usersMenus();
		//$folders[]="/var/lib/ldap";
		//$folders[]=dirname($users->SLAPD_CONF_PATH);
		//$folders[]="/etc/artica-postfix";
			
		//if($users->cyrus_imapd_installed){
			//$folders[]='/var/spool/cyrus/mail';
			//$folders[]='/var/lib/cyrus';
		//}
		
		
		
		while (list ($num, $ligne) = each ($folders) ){
			if($ligne==null){continue;}
			$name="brick-".basename($ligne)."-$num";
			$this->CLUSTERED_BRIKS[]=$name;
			$this->CLUSTERED_FOLDERS[$name]=$ligne;
			
		}
		
		writelogs(count($this->CLUSTERED_FOLDERS) ." bricks for this server",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
	}
	
	
	private function BuildServerConf(){
		$folders=$this->CLUSTERED_FOLDERS;
		if(!is_array($folders)){return null;}
		$count=0;
		
		while (list ($name, $path) = each ($folders) ){
				$count++;
				$conf[]="volume posix-$count";
				$conf[]="	type storage/posix";
				$conf[]="	option directory $path";
				$conf[]="end-volume";
 				$conf[]="\n";
 				$conf[]="\n";
 				
   				$conf[]="volume locks-$count";
  				$conf[]="	type features/locks";
  				$conf[]="	subvolumes posix-$count";
				$conf[]="end-volume";
				$conf[]="\n";
 				$conf[]="\n";				

				$conf[]="volume $name";
				$conf[]="	type performance/io-threads";
  				$conf[]="	option thread-count 8";
  				$conf[]="	subvolumes locks-$count";
				$conf[]="end-volume";
 				$conf[]="\n"; 
 				$conf[]="\n"; 
 				
				$conf[]="volume ns-$count";
				$conf[]="	type storage/posix";
				$conf[]="	option directory $path";
				$conf[]="end-volume";
 				$conf[]="\n";
 				$conf[]="\n"; 				
				
 								
 				$briks[]="$name";
 				
 				 
		}

		$conf[]="volume server";
	    $conf[]="	type protocol/server";
	    $conf[]="	subvolumes ".implode(" ",$briks);
	    $conf[]="	option transport-type tcp/server # For TCP/IP transport";
	     $conf[]="	option transport.socket.nodelay on";
	    while (list ($num, $ligne) = each ($briks) ){
	    	$conf[]="	option auth.ip.$ligne.allow * # access to \"$ligne\" volume";
	    }
		$conf[]="end-volume";
		$conf[]="\n";
			
		return implode("\n",$conf);
		
		
	}
}


class glusterCurl{
	var $uri;
	var $parms=array();
	var $data='';
	function glusterCurl($uri){
		$this->uri=$uri;
	}
	
	function get(){
				
			while (list ($num, $ligne) = each ($this->parms)){
				$curlPost .='&'.$num.'=' . urlencode($ligne);
			}		
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$this->uri");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
			$this->data=curl_exec($ch);
			$error=curl_errno($ch);		
			writelogs("datas: ". strlen($this->data)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			if($error==6){
				writelogs("error 6 ($this->uri) in protocol",__CLASS__.'/'.__FUNCTION__,__FILE__);
				curl_close($ch);
				return false;
			}
			
			
			curl_close($ch);
			return true;
	}
}


?>