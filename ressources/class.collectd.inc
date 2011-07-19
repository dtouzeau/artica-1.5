<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');


class collectd{
	
	var $rrd_array=array();
	var $timestamp_start=86400;
	var $graphs_array=array();
	var $field_h=array();
	function collectd(){
		
		$users=new usersMenus();
		$this->rrd_array=$users->COLLECTD_RRD;
		$this->ParseArray();
		
	}
	
	
	
	function ParseArray(){
		
		while (list ($num, $ligne) = each ($this->rrd_array) ){
			
			
			if(preg_match('#(.+?)-(.+)#',$num,$re)){
				$this->field_h[$re[1]]="{{$re[1]}}";
				$this->graphs_array[$re[1]][]=array(
				"plugin_instance"=>$re[2],
				"type"=>$this->ParseSubarray($ligne)
					);
				continue;
				}
			
			$this->field_h[$num]="{{$num}}";
				$this->graphs_array[$num][]=array(
				"plugin_instance"=>null,
				"type"=>$this->ParseSubarray($ligne)
					);			
				
				
			}
		}
	
	function ParseSubarray($array){
		while (list ($num, $ligne) = each ($array) ){
			//echo $ligne."\n";
		if(preg_match('#(.+?)-(.+?)\.rrd#',$ligne,$re)){
			$arr[$ligne]=array("type"=>$re[1],"type_instace"=>$re[2]);
			continue;
		}
		
		if(preg_match('#(.+?)\.rrd#',$ligne,$re)){
			$arr[$ligne]=array(
					"type"=>$re[1],
					"type_instance"=>null
					);
			continue;
		}
		
		}
		return $arr;
	}
	
	
	
}

?>