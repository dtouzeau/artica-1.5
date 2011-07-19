<?php

class smartd{
	var $dev;
	var $diskinfos_array=array();
	var $diskstats_array=array();
	var $disk_attributes=array();
	
	function smartd($dev=null){
		$this->dev=$dev;
		if($this->dev<>null){
			$this->smartctl_infos();
			$this->SelfTests();
			$this->SmartAttributes();
		}
		
		
		
	}
	
	function smartctl_infos(){
		$sock=new sockets();
		$datas=$sock->getfile("smartclinfos:$this->dev");
		$tbl=explode("\n",$datas);
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match("#Home page#",$val)){continue;}
			if(preg_match('#(.+?):(.+)#',$val,$re)){
				$this->diskinfos_array[trim($re[1])]=trim($re[2]);
			}
		}
		
	}
	
	function SelfTests(){
		$sock=new sockets();
		$datas=$sock->getfile("smartcltests:$this->dev");
		$tbl=explode("\n",$datas);
			while (list ($num, $val) = each ($tbl) ){
				if(preg_match("#Home page#",$val)){continue;}
				if(preg_match('#.+?([0-9]+)\s+(.+?)\s+\s+(.+?)([0-9]+).+?([0-9]+)\s+(.+)#',$val,$re)){
				$this->diskstats_array[trim($re[1])]=array(
						"test_description"=>trim($re[2]),
						"status"=>trim($re[3]),
						"remaining"=>trim($re[4]),
						"lifetime"=>trim($re[5]),
						"lba_of_first_error"=>trim($re[6])
						);
				}
			}		
	}
	
	function SmartAttributes(){
		$sock=new sockets();
		$datas=$sock->getfile("smartcltAttributes:$this->dev");
		$tbl=explode("\n",$datas);
			while (list ($num, $val) = each ($tbl) ){
				if(preg_match("#Home page#",$val)){continue;}
				if(preg_match('#([A-Za-z\_]+)\s+([0-9xf]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.+?)\s+(.+?)\s+(.+?)\s+([0-9]+)#',$val,$re)){
				$this->disk_attributes[]=array(
						"ATTRIBUTE_NAME"=>trim($re[1]),
						"FLAG"=>trim($re[2]),
						"VALUE"=>trim($re[3]),
						"WORST"=>trim($re[4]),
						"THRESH"=>trim($re[5]),
						"TYPE"=>trim($re[6]),
						"UPDATED"=>trim($re[7]),
						"WHEN_FAILED"=>trim($re[8]),
						"RAW_VALUE"=>trim($re[9]),
						);
				}
			}		
	}	
	
	
	
}
?>