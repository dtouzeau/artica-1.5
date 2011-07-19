<?php
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.cron.inc');

class squid_bandwith_builder{
	var $rules_array=array();
	var $delay_pools=array();
	var $delay_class=array();
	var $delay_parameters=array();
	var $rules_name=array();
	var $acls=array();
	var $delay_access=array();
	var $rules_explain=array();
	
	function squid_bandwith_builder(){
		$this->loadRules();
		
		
	}
	
	function loadrules(){
		$sql="SELECT * FROM squid_pools WHERE enable=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$count=$count+1;
			$this->rules_array[$count]=$ligne["ID"];
			$this->rules_name[$count]=$ligne["rulename"];
			$this->delay_pools[]="delay_pools $count";
			$this->delay_class[]="delay_class $count {$ligne["rule_class"]}";
			$this->delay_parameters[]="delay_parameters $count {$ligne["total_net"]} {$ligne["total_users"]}";
		}
	}
	
	function compile(){
		if(count($this->rules_array)==0){return;}
	
		
		while (list ($index, $pool_id) = each ($this->rules_array) ){
			if($index=="0"){continue;}
			if($index==null){continue;}
			$this->src($pool_id,$index);
			$this->filetype($pool_id,$index);
			$this->dstdomain($pool_id,$index);
			$this->work_time($pool_id,$index);
			$this->delay_access[]="delay_access $index deny all";
		}	

		$conf[]="";
		$conf[]="#--------- Bandwith limitations";
		$conf[]="#Acls";
		$conf[]=@implode("\n",$this->acls);
		$conf[]="";
		$conf[]="#Rules definitions";
		$conf[]=@implode("\n",$this->delay_pools);
		$conf[]=@implode("\n",$this->delay_class);
		$conf[]=@implode("\n",$this->delay_parameters);
		$conf[]="";
		$conf[]="#Rules Access definitions";		
		$conf[]=@implode("\n",$this->delay_access);
		$conf[]="";
		return @implode("\n",$conf);
		

	}
	
	private function src($pool_id,$index){
		$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='SRC_RESTRICT' AND enabled=1";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]<1){return;}
		$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));
		while (list ($num, $nets) = each ($ACL_DATAS) ){
			if(trim($nets)==null){continue;}
			$nets=trim($nets);
			$dd[]=$nets;
			$this->acls[]="acl BAND_SRC_$pool_id src $nets";	
		}
		if(count($dd)==0){return;}
		$this->delay_access[]="delay_access $index allow BAND_SRC_$pool_id";	
		
		$this->rules_explain[$pool_id][]="<i style='font-weight:bold'>{limit_computers_scope_to}: ".@implode(", ",$dd)."</i>";
		
		
	}		
	
	private function dstdomain($pool_id,$index){
		$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='DOMAIN_RESTRICT' AND enabled=1";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]<1){return;}
		$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));
		while (list ($num, $nets) = each ($ACL_DATAS) ){
			if(trim($nets)==null){continue;}
			$nets=trim($nets);
			$dd[]=$nets;
			$this->acls[]="acl BAND_DSTDOM_$pool_id dstdomain $nets";	
		}
		if(count($dd)==0){return;}
		$this->delay_access[]="delay_access $index allow BAND_DSTDOM_$pool_id";	
		
		$this->rules_explain[$pool_id][]="{squid_band_www_restriction_text}: <b>".count($dd)." {websites}</b>";
		
		
	}

	
	private function filetype($pool_id,$index){
		$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='FILE_RESTRICT' AND enabled=1";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]<1){return;}
		$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));
		while (list ($num, $nets) = each ($ACL_DATAS) ){
			if(trim($nets)==null){continue;}
			$nets=trim($nets);
			$dd[]=$nets;
			$this->acls[]="acl BAND_FILE_$pool_id url_regex -i \.$nets\$";	
		}
		if(count($dd)==0){return;}
		$this->delay_access[]="delay_access $index allow BAND_FILE_$pool_id";	
		
		$this->rules_explain[$pool_id][]="{squid_band_file_restriction_text}: <b>".count($dd)." {files}</b>";
		
		
	}		
	
	private function work_time($pool_id,$index){
		$sql="SELECT * FROM squid_pools_acls WHERE pool_id=$pool_id AND ACL_TYPE='TIME_RESTRICT' AND enabled=1";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]<1){return;}
		$ACL_DATAS=unserialize(base64_decode($ligne["ACL_DATAS"]));
		$cron=new cron_macros();
		unset($dd);
		while (list ($dayIndex, $day) = each ($cron->cron_squid) ){
			if($ACL_DATAS[$dayIndex]==1){$d[]=$dayIndex;$dd[]=$day;}
			
			
		}
		if(count($d)==0){return;}
		$f=@implode("",$d);
		if(strlen($ACL_DATAS["min1"])<10){$ACL_DATAS["min1"]="0{$ACL_DATAS["min1"]}";}
		if(strlen($ACL_DATAS["min2"])<10){$ACL_DATAS["min2"]="0{$ACL_DATAS["min2"]}";}
		$this->acls[]="acl BAND_TIME_$pool_id time $f {$ACL_DATAS["hour1"]}:{$ACL_DATAS["min1"]}-{$ACL_DATAS["hour2"]}:{$ACL_DATAS["min2"]}";
		$this->delay_access[]="delay_access $index allow BAND_TIME_$pool_id";
		$this->rules_explain[$pool_id][]="{squid_band_time_restriction_text} ".@implode(", ",$dd) . " 
		{from} {$ACL_DATAS["hour1"]}:{$ACL_DATAS["min1"]} {to} {$ACL_DATAS["hour2"]}:{$ACL_DATAS["min2"]}";
		
		
	}
	
	
	
}

?>