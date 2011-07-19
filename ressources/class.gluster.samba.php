<?php
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');
	
class gluster_samba{
	var $clients=array();
	var $PARAMS_CLIENTS=array();
	var $STATUS_CLIENTS=array();
	var $CLUSTERED_DIRECTORIES=array();
	
	public function gluster_samba(){
		$this->GetDirectoryList();
		$this->GetClustersClientsList();
	}
	
	
	private function GetDirectoryList(){
		$sql="SELECT cluster_path FROM gluster_paths";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["cluster_path"]==null){continue;}
			if(!is_dir($ligne["cluster_path"])){continue;}
			$this->CLUSTERED_DIRECTORIES[]=$ligne["cluster_path"];
		}
		
	}
	private function GetClustersClientsList(){
		$sql="SELECT client_ip FROM glusters_clients WHERE client_notified=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["client_ip"]==null){continue;}
			
			$this->clients[]=$ligne["client_ip"];
		}
	}
	
	private function BuildVolumes(){
		if(!is_array($this->CLUSTERED_DIRECTORIES)){return null;}
		
		reset($this->CLUSTERED_DIRECTORIES);
		
		while (list ($index, $path) = each ($this->CLUSTERED_DIRECTORIES) ){
			$f[]="volume posix-$index";
			$f[]="\ttype storage/posix";
			$f[]="\toption directory $path";
			$f[]="end-volume";
			$f[]="";
		}
		
		reset($this->CLUSTERED_DIRECTORIES);
		while (list ($index, $path) = each ($this->CLUSTERED_DIRECTORIES) ){
			$f[]="volume locks-$index";
			$f[]="\ttype features/locks";
			$f[]="\tsubvolumes posix-$index";
			$f[]="end-volume";
			$f[]="";
		}
		
		reset($this->CLUSTERED_DIRECTORIES);
		while (list ($index, $path) = each ($this->CLUSTERED_DIRECTORIES) ){
			$bricks[]="brick-$index";
			$bricks_auth[]="\toption auth.addr.brick-$index.allow *";
			$bricks_sql[]="INSERT INTO gluster_clients_brick (brickname,source) VALUES('brick-$index','$path')";
			$f[]="volume brick-$index";
			$f[]="\ttype performance/io-threads";
			$f[]="\toption thread-count 8";
			$f[]="\tsubvolumes locks-$index";
			$f[]="end-volume";
			$f[]="";
		}
		$this->bricksql($bricks_sql);
		
		reset($this->CLUSTERED_DIRECTORIES);
		$f[]="volume server";
		$f[]="\ttype protocol/server";
		$f[]="\tsubvolumes ". @implode(" ",$bricks);
		$f[]=@implode("\n",$bricks_auth);
		$f[]="end-volume";
		$f[]="";
		
		
		return @implode("\n",$f);
				
		
	}
	
	private function bricksql($array){
		$q=new mysql();
		$q->QUERY_SQL("TRUNCATE TABLE `gluster_clients_brick`","artica_backup");
		while (list ($index, $sql) = each ($array) ){
			$q->QUERY_SQL($sql,"artica_backup");
		}
		
	}
	
	public function build(){
		$volumes=$this->BuildVolumes();
		return $volumes;
	}
	
	
}


class gluster_client{
	var $master_array=array();
	var $directories=array();
	
	
	function gluster_client(){
		
	}
	
	public function implode_bricks(){
		$sql="SELECT * FROM glusters_servers";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$serverip=$ligne["server_ip"];
			writelogs("parameters={$ligne["parameters"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$array=unserialize(base64_decode($ligne["parameters"]));
			if(!is_array($array["PATHS"])){
				writelogs("NO PATHS for $serverip ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				continue;
			}
			while (list ($brickname, $path) = each ($array["PATHS"]) ){
				if(trim($path)==null){continue;}
				writelogs("$path -> $serverip:$brickname",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$VOLS[$path][]=array("BRICKNAME"=>$brickname,"SERVER"=>$serverip);
			}
			
		}
		
		return $VOLS;
	}
	
	function buildconf(){
		$vols=$this->implode_bricks();
		if($GLOBALS["VERBOSE"]){print_r($vols);}
		shell_exec("/bin/rm /etc/artica-cluster/glusterfs-client/* >/dev/null 2>&1");
		if(!is_array($vols)){return null;}
		while (list ($path, $array_infos) = each ($vols) ){
			$path_count=$path_count+1;
			writelogs("Check servers for $path ($path_count)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			unset($subvolumes);
			while (list ($index, $infos) = each ($array_infos) ){
				$count=$count+1;
				$subvolumes[]="remote{$count}";
				$f[]="#LOCAL_PATH:$path";
				$f[]="#replicate{$path_count} -> {$infos["SERVER"]}:{$infos["BRICKNAME"]}";
				$f[]="volume remote{$count}";
				$f[]="\ttype protocol/client";
				$f[]="\toption transport-type tcp";
 				$f[]="\toption remote-host {$infos["SERVER"]}";
 				$f[]="\toption remote-subvolume {$infos["BRICKNAME"]}";
				$f[]="end-volume";
				$f[]="";
				
				
			}
			$f[]="";
			$f[]="volume replicate{$path_count}";
  			$f[]="\ttype cluster/replicate";
  			$f[]="\tsubvolumes ". implode(" ",$subvolumes);
			$f[]="end-volume";
			$f[]="";
			
			$f[]="volume writebehind";
			$f[]="\ttype performance/write-behind";
			$f[]="\toption window-size 1MB";
			$f[]="\tsubvolumes replicate{$path_count}";
			$f[]="end-volume";
			$f[]="";
			$f[]="volume cache";
			$f[]="\ttype performance/io-cache";
			$f[]="\toption cache-size 512MB";
			$f[]="\tsubvolumes writebehind";
			$f[]="end-volume";	
			$f[]="";
			@mkdir("/etc/artica-cluster/glusterfs-client",null,true);
			@file_put_contents("/etc/artica-cluster/glusterfs-client/$path_count.vol",@implode("\n",$f));
			echo "Starting......: Gluster clients $path_count.vol configuration done..\n";
			unset($f);
			
		}
		
		
		
	}
	
	function volToPath($path){
		$f=explode("\n",@file_get_contents($path));
		while (list ($index, $line) = each ($f) ){
			if(preg_match("#LOCAL_PATH:(.+)#",$line,$re)){
				return trim($re[1]);
			}
		}
	}
	function ismounted($path){
		$pathString=str_replace("/","\/",$path);
		$pathString=str_replace(".","\.",$pathString);
		$f=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($index, $line) = each ($f) ){
			if(preg_match("#$pathString\s+fuse\.glusterfs#",$line)){
				if($GLOBALS["VERBOSE"]){
					echo "Found line $line OK \"#$pathString\s+fuse\.glusterfs#\"\n";
				}
				return true;
			}
			
		}
		
	}
	
	function CheckPath($path){
		if(!is_dir($path)){
			@mkdir($path,null,true);
			return true;
		}
		
		$array=glob("$path/*");
		if(count($array)>0){
			writelogs("$path is not empty ! aborting",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		
		return true;
	}
	
	function mount($path,$volfile){
		$unix=new unix();
		$mount=$unix->find_program("mount");
		exec("$mount -t glusterfs $volfile $path",$results);
		while (list ($index, $line) = each ($results) ){
			if(trim($line)==null){continue;}
			echo "Starting......: Gluster clients $line\n";
		}
	}
	
	function get_mounted(){
		$array=array();
		$f=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($index, $line) = each ($f) ){
			if(preg_match("#^(.+?)\s+.+?\s+fuse\.glusterfs#",$line,$re)){
				$array[]=$re[1];
			}
		}
		return $array;
	}
	
}



?>