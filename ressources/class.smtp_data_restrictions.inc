<?php

class smtpd_data_restrictions{
	var $hostname;
	var $error=false;
	var $restriction_array=array();
	var $config_dir=null;
	var $restriction_final=null;
	
	public function smtpd_data_restrictions($hostname=null){
		if($hostname<>null){
			$this->hostname=$hostname;
			if($this->hostname=="master"){$this->config_dir="/etc/postfix";}else{$this->config_dir="/etc/postfix-$this->hostname";}
		}
		
		$this->LoadGlobal();
		
	}
	
	public function LoadGlobal(){
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["check_client_access"]=array("TYPE"=>"TABLE","VERSION"=>"2.0");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["permit_mynetworks"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["permit_sasl_authenticated"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_unknown_client_hostname"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_unknown_reverse_client_hostname"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["defer"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["check_client_mx_access"]=array("TYPE"=>"TABLE","VERSION"=>"2.7");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["check_client_ns_access"]=array("TYPE"=>"TABLE","VERSION"=>"2.7");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["check_reverse_client_hostname_access"]=array("TYPE"=>"TABLE","VERSION"=>"2.7");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["check_reverse_client_hostname_mx_access"]=array("TYPE"=>"TABLE","VERSION"=>"2.7");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["check_reverse_client_hostname_ns_access"]=array("TYPE"=>"TABLE","VERSION"=>"2.7");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["permit_inet_interfaces"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["permit_sasl_authenticated"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_plaintext_session"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_rbl_client"]=array("TYPE"=>"VALUE","VERSION"=>"2.0");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["permit_dnswl_client"]=array("TYPE"=>"VALUE","VERSION"=>"2.0");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_rhsbl_client"]=array("TYPE"=>"VALUE","VERSION"=>"2.8");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["permit_rhswl_client"]=array("TYPE"=>"VALUE","VERSION"=>"2.8");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_rhsbl_helo"]=array("TYPE"=>"VALUE","VERSION"=>"2.8");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_rhsbl_reverse_client"]=array("TYPE"=>"VALUE","VERSION"=>"2.0");
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_unknown_client_hostname"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_unknown_reverse_client_hostname"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_unauth_pipelining"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_invalid_helo_hostname"]="";
			$GLOBALS["SMTP_DATA_RESTRICTIONS"]["reject_non_fqdn_helo_hostname"]="";			
	}
	
	
	public function compile(){
		$sql="SELECT * FROM smtpd_data_restrictions WHERE hostname='$this->hostname' and enabled=1 ORDER BY zorder";
		if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> compile() \"$sql\"\n";}
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){
			echo "Starting......: Postfix -> smtpd_data_restrictions() $q->mysql_error\n";}
			$this->error=true;
			return;
		}
		
		if(mysql_num_rows($results)==0){
			if($this->hostname=="master"){
				$sql="SELECT * FROM smtpd_data_restrictions WHERE hostname='' and enabled=1 ORDER BY zorder";
				$results=$q->QUERY_SQL($sql,"artica_backup");
			}
		}
		
		if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> compile() \"". mysql_num_rows($results)."\" entries\n";}
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			
			$arrayVal=unserialize(base64_decode($ligne["restrictions_datas"]));
			
			if(!is_array($GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]])){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_data_restrictions() \"{$ligne["restriction"]}\"\n";}
				$this->restriction_array[]=$ligne["restriction"];
				continue;
			}
		
			if($GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]]["TYPE"]=="VALUE"){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_data_restrictions() \"{$ligne["restriction"]}\"\n";}
				$VAL=$arrayVal["VALUE"];
				if(trim($VAL)==null){continue;}
				$this->restriction_array[]=$ligne["restriction"]." ".$VAL;
				continue;	
			}
			
			if($GLOBALS["SMTP_DATA_RESTRICTIONS"][$ligne["restriction"]]["TYPE"]=="TABLE"){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_data_restrictions() \"{$ligne["restriction"]}\"\n";}
				$this->restriction_array[]=$ligne["restriction"]." hash:$this->config_dir/{$ligne["restriction"]}_{$ligne["ID"]}";
				$this->CompileHASHTable($arrayVal,"$this->config_dir/{$ligne["restriction"]}_{$ligne["ID"]}");
				continue;
				
			}
			
			
		}
		
		if(count($this->restriction_array)>0){
			$this->restriction_final=@implode(", ", $this->restriction_array);
			if($GLOBALS["VERBOSE"]){echo "Starting......: Postfix -> smtpd_data_restrictions() \"$this->restriction_final\"\n";}
		}
		
			
	}
	
	private function CompileHASHTable($array,$filename){
		if(!isset($GLOBALS["postmap"])){$unix=new unix();$GLOBALS["postmap"]=$unix->find_program("postmap");}
		
		
		while (list ($num, $ARkey) = each ($array) ){
			$text=null;
			if(trim($ARkey["TXT"])<>null){$text="\t{$ARkey["TXT"]}";}
			$f[]="{$ARkey["KEY"]}\t{$ARkey["ACTION"]}$text";
			
		}
		
		@file_put_contents($filename, @implode("\n", $f));
		shell_exec("{$GLOBALS["postmap"]} hash:$filename");

	}
	
	
}