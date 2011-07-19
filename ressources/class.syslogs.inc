<?php

include_once(dirname(__FILE__) . '/class.mysql.inc');

class syslogs{
	
	var $database="artica_events";
	var $limit_start=0;
	var $limit_end=50;
	var $q_daemons="";
	var $q_search='';
	var $query;
	
	
	function syslogs(){
		$this->BuildNecessaryTables();
		
	}
	
	
	
	function build_query(){
		
		$q=new mysql();
		
		if($this->q_daemons<>null){
			$q_daemons=" AND program='$this->q_daemons' ";
		}
		
		if($this->q_search<>null){
			$q_search=" AND msg LIKE '%$this->q_search%' ";
		}
		
		if($this->limit_end==null){$this->limit_end=50;}
		
		$sql="SELECT *  FROM syslogs WHERE 1 $q_daemons$q_search ORDER BY `date` DESC LIMIT $this->limit_start,$this->limit_end";
		$this->query=$sql;
		return $q->QUERY_SQL($sql,$this->database);
		
		
		
	}
	
	function GetDaemons(){
		$q=new mysql();
		$sql="SELECT program FROM syslogs GROUP BY program ORDER BY program";
		$r=$q->QUERY_SQL($sql,$this->database);
		while($ligne=@mysql_fetch_array($r,MYSQL_ASSOC)){
			$arr[$ligne["program"]]=$ligne["program"];
		}
	$arr[null]="{all}";
	return $arr;}
	
	function count_fatal_errors(){
		$q=new mysql();
		$sql="SELECT count(zMD5) as tcount FROM notify";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["tcount"]==null){$ligne["tcount"]=0;}
		return $ligne["tcount"];
		}
		
	function get_warnings(){
		$q=new mysql();
		$sql="SELECT * FROM notify LIMIT 0,50";
		return $q->QUERY_SQL($sql,$this->database);
		}
		
	function delete_warnings($md5){
		$q=new mysql();
		$sql="DELETE FROM notify WHERE zMD5='$md5'";
		$q->QUERY_SQL($sql,$this->database);
	}
	
	
	function BuildNecessaryTables(){
		$q=new mysql();

		
		
		$sql="TRUNCATE TABLE `sys_events`"; 
		$q->QUERY_SQL($sql,"artica_events");
		

		
		
	}
	
	
	
	
	
	
	
	
	
}
?>