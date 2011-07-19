<?php
include_once(dirname(__FILE__).'/class.system.network.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.templates.inc');

class qos_net{
	var $master_service_name; 
	var $master_service_bandwidth;
	var $master_service_eth;
	var $master_service_enabled;
	
	function qos_net($ID=null){
		if($ID>0){$this->master_service_load($ID);}
	}
	
	public function getSavedNics(){
		$sql="SELECT NIC FROM qos_eth";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,'artica_backup');
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){$arr[$ligne["NIC"]]=$ligne["NIC"];}
		return $arr;
	}
	
	
	private function master_service_load($ID){
		$sql="SELECT * FROM qos_eth WHERE ID=$ID";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		$this->master_service_name=$ligne["name"];
		$this->master_service_bandwidth=$ligne["bandwith"];
		$this->master_service_eth=$ligne["NIC"];
		$this->master_service_enabled=$ligne["enabled"];
	}
	
	
}

class qos_class{
	var $prio;
	var $rate;
	var $name;
	var $service_id;
	var $ceil;
	var $enabled;
	var $class_id=0;
	
	
	function qos_class($ID){
		if($ID>0){$this->load($ID);$this->class_id=$ID;}
		$this->defaults();
		
	}
	
private function load($ID){
		$sql="SELECT * FROM qos_class WHERE ID=$ID";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		$this->prio=$ligne["prio"];
		$this->rate=$ligne["rate"];
		$this->service_id=$ligne["service_id"];
		$this->ceil=$ligne["ceil"];
		$this->enabled=$ligne["enabled"];
		$this->name=$ligne["name"];
		
	}
	
	private function defaults(){
		if($this->prio==null){$this->prio=100;}
		if($this->rate==null){$this->rate=0;}
		if($this->service_id==null){$this->service_id=0;}
		if($this->ceil==null){$this->ceil=0;}
		if($this->enabled==null){$this->enabled=1;}
		if($this->name==null){$this->name="New container";}	
	}
	
	public function delete_class(){
		$sql="DELETE FROM qos_class WHERE ID=$this->class_id";
		$q=new mysql();
		$q->QUERY_SQL($sql,'artica_backup');
		if(!$q->ok){echo "$q->mysql_error\n\n$sql";return;}
		$sql="DELETE FROM qos_rules WHERE class_id=$this->class_id";
		$q->QUERY_SQL($sql,'artica_backup');
		if(!$q->ok){echo "$q->mysql_error\n\n$sql";return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?qos-compile=yes");
		
	}
		
	
}