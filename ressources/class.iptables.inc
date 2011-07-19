<?php
include_once(dirname(__FILE__) . '/class.ini.inc');
class iptables{
	var $ntpdConf;
	var $IptablesEnabled;
	var $nics_table;
	var $nics;
	var $rules;
	var $inirules;
	var $dn;
	
	function iptables($noparse=false){
		$this->BuildTree();
		$this->getNics();
		$this->Load();
		

		}
		
		
	
	
	
	function Load(){
		$ldap=new clladp();
		$sock=new sockets();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=iptables)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->inirules=$hash[0][strtolower("iptablesRules")][0];
			}

			$this->IptablesEnabled=$sock->GET_INFO("IptablesEnabled");
			if($this->IptablesEnabled==null){$this->IptablesEnabled=0;}
			$this->ParseLDAPRules();
			writelogs("Rules number ".count($this->rules),__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
	
	
	function ParseLDAPRules(){
		if(trim($this->inirules==null)){
			$this->Add_default_rules();
			$this->SaveToLdap();
			return null;
		}
		$ini=new Bs_IniHandler();
		$ini->loadString($this->inirules);
		if(!is_array($ini->_params)){
			$this->Add_default_rules();
			$this->SaveToLdap();
			return null;	
		}
		while (list ($num, $val) = each ($ini->_params) ){
			while (list ($a, $b)= each ($val) ){
				if(preg_match('#_([0-9]+)#',$num,$re)){
					$index=$re[1];
				}else{
					if(preg_match('#([0-9]+)#',$num,$re)){
						$index=$re[1];
					}
				}
				$this->rules[$val["CARTE"]][$index][$a]=$b;
			}
		}		
	
	}
	
	
	function SaveToLdap(){
		$ldap=new clladp();
		$upd["iptablesFile"][0]=$this->compile_rules();
		$upd["iptablesRules"][0]=$this->BuildRules();
		$ldap->Ldap_modify($this->dn,$upd);
		unset($upd);
		$sock=new sockets();
		$sock->SET_INFO("IptablesEnabled",$this->IptablesEnabled);
		}
	
	function Add_default_rules(){
		writelogs("add rules for nic " .count($this->nics_table) ." nics",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$nicks=$this->nics_table;
		if(!is_array($nicks)){return null;}
		while (list ($num, $val) = each ($nicks) ){
			$val=trim($val);
			$this->_Add_default_nics_rules($val);
			writelogs("Number of default rules added:" .count($this->rules[$val]),__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
	
	}
	
	
	function _Add_default_nics_rules($nic){
		if(trim($nic)==null){return null;}
		if(preg_match('#vmne#',$nic)){return null;}
		writelogs("make default rules...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>9000
			,"ENABLED"=>1
			);
			
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>443
			,"ENABLED"=>1
			);	
			
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>143
			,"ENABLED"=>1
			);	

		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>993
			,"ENABLED"=>1
			);	
			
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>22
			,"ENABLED"=>1
			);				
			
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>21
			,"ENABLED"=>1
			);				

		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"LOCAL_PORT"=>25
			,"ENABLED"=>1
			);	
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"STATE"=>"RELATED,ESTABLISHED,NEW",
			"PROTO"=>"TCP",
			"LOCAL_PORT"=>"135,137,138,139,445"
			,"ENABLED"=>1
			);	
			
		$this->rules[$nic][]=array(
			"REGLE"=>"INSERT",
			"CIBLE"=>"INPUT",
			"CARTE"=>$nic,
			"ACTION"=>"ACCEPT",
			"STATE"=>"ESTABLISHED,NEW",
			"PROTO"=>"UDP",
			"LOCAL_PORT"=>"135,137,138,139,445"
			,"ENABLED"=>1
			);	

			
					
			
			
							
		
	}
	
	function getNics(){
		$sock=new sockets();
		$nics=$sock->getfile('listnics');
		$n=explode(';',$nics);
		while (list ($num, $val) = each ($n) ){
			if(trim($val)<>null){
				if(!preg_match('#vmn#',$val)){
					$this->nics_table[trim($val)]=trim($val);
					$this->nics[]=trim($val);
					}
			}
		}
		
		}
	
	
	function BuildTree(){
		$ldap=new clladp();
		$this->dn="cn=iptables,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($this->dn)){
			$upd["objectClass"][]="iptables";
			$upd["objectClass"][]="top";
			$upd["iptablesRules"]=$this->BuildRules();
			$upd["cn"][]="iptables";
			$ldap->ldap_add($this->dn,$upd);
			unset($upd);
			}
		}	
		

	
	function BuildRules(){
		if(!is_array($this->rules)){$this->Add_default_rules();return null;}
		$ini=new Bs_IniHandler();
		while (list ($num, $val) = each ($this->rules) ){
			while (list ($a, $b)= each ($val) ){
				while (list ($c, $d)= each ($b) ){
					$ini->_params["{$num}_{$a}"][$c]=$d;
			}}
			
		}
			
	$r=$ini->toString();
	return $r;
	}
	
	
	function compile_rules(){
		if(!is_array($this->rules)){$this->Add_default_rules();}
		$arr=$this->rules;
		
		$conf="#GLOBAL VALUES\n";
		$conf=$conf ."iptables -A INPUT -m state --state NEW,ESTABLISHED,RELATED -j ACCEPT\n";
		$conf=$conf ."iptables -A INPUT -p icmp -m state --state ESTABLISHED,RELATED -j ACCEPT\n";
		$conf=$conf . "iptables -A INPUT -i lo -j ACCEPT\n";
		if(is_array($arr)){		
			while (list ($nic, $array) = each ($arr) ){	
				$conf=$conf . "\n#$nic\n";
	
				
				while (list ($num, $val) = each ($array) ){	
						if($val["FROM"]=='*'){$val["FROM"]=null;}
						if($val["MAC"]=='*'){$val["MAC"]=null;}
						if($val["PROTO"]==null){$val["PROTO"]='all';}
						if(trim($val["ENABLED"])==null){$val["ENABLED"]=1;}
						writelogs("compile line $num");
						
						if($val["ENABLED"]==0){
							writelogs("line $num disabled '{$val["ENABLED"]}'",__CLASS__.'/'.__FUNCTION__,__FILE__);
							continue;}
						
						if($val["REGLE"]=="GLOBAL"){
							continue;
						}
						
						if(trim($val["MAC"])<>null){
								$val["FROM"]=null;
								$source="-m mac --mac-source {$val["MAC"]} ";
							
						}
						
						if($val["FROM"]<>null){
							if(strpos($val["FROM"],'-')>0){
								$source="-m iprange --src-range {$val["FROM"]} ";
							}else{
								$source=" --source {$val["FROM"]}";
							}
							
						}
						
						if(strpos($val["LOCAL_PORT"],',')>0){
							$ports=" -m multiport --dports {$val["LOCAL_PORT"]}";
						}else{
							$val["PROTO"]='tcp';
							if($val["LOCAL_PORT"]<>null){
							$ports=" --dport {$val["LOCAL_PORT"]}";
							}
						}
						
						
						if($val["STATE"]<>null){
							$states=" -m state --state {$val["STATE"]}";
							
						}
						
						if($val["LOG"]==1){$conf=$conf ."iptables -A {$val["CIBLE"]} -p {$val["PROTO"]} -i {$val["CARTE"]} $source$ports$states {$val["LOCAL_PORT"]} -j LOG\n";}
						$conf=$conf ."iptables -A {$val["CIBLE"]} -p {$val["PROTO"]} -i {$val["CARTE"]} $source$ports$states -j {$val["ACTION"]}\n";
						writelogs($conf);
						
						$source=null;
						$ports=null;
					
					}
			}
		}
		$conf=$conf ."\n\n#Finish the rules log last events and block all others connections\n";
		$conf=$conf ."iptables -A INPUT -p tcp -j LOG --log-prefix [IPTABLES_BLOCKED]: --log-level 4\n";
		$conf=$conf ."iptables -P INPUT DROP\n";
		return $conf;
		
	}
	

	
	function SaveToServer(){
		$sock=new sockets();
		$sock->getfile('iptables_settings');
		}
	
}
//  /opt/artica/bin/ntpq -n -c rv 127.0.0.1
// /opt/artica/bin/ntpd -c /opt/artica/etc/ntpd.conf -p /var/run/ntpd.pid