<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.user.inc');

class fdm{
	 var $dn;
	 var $uid;
	 var $FDMArticaSets;
	 var $FDMConf;
	 var $main_array=array();
	 var $type_array=array(null=>"{select}","imap"=>"imap","imaps"=>"imap ssl","pop3"=>"pop3","pop3s"=>"pop3 ssl");
	
	function fdm($uid){
		$ldap=new clladp();
		$this->uid=$uid;
		$this->dn="cn=$uid,cn=fdm,cn=artica,$ldap->suffix";
		$this->FixTree();
		$this->ParseConfig(); 
		
		
		
	}
	
	function FixTree(){
		$ldap=new clladp();
		
		if(!$ldap->ExistsDN("cn=fdm,cn=artica,$ldap->suffix")){
			$upd["cn"]='fdm';
			$upd["objectClass"][]='top';
			$upd["objectClass"][]="FDMClass";
			$ldap->ldap_add("cn=fdm,cn=artica,$ldap->suffix",$upd);
			}		
		
		
		if(!$ldap->ExistsDN($this->dn)){
			$upd["cn"]=$this->uid;
			$upd["objectClass"][]='top';
			$upd["objectClass"][]="FDMClass";
			$upd["FDMArticaSets"][0]="NONE";
			$ldap->ldap_add($this->dn,$upd);
			}
		
		
		
	}
	
	function ParseConfig(){
				$ldap=new clladp();
				$count=0;
				$dn=$this->dn;
				$res=@ldap_read($ldap->ldap_connection,$dn,"(objectClass=*)",array());
				if($res){
						$hash=ldap_get_entries($ldap->ldap_connection,$res);
						$this->FDMArticaSets=$hash[0][strtolower('FDMArticaSets')][0];
						$this->FDMConf=$hash[0][strtolower('FDMConf')][0];
						
						
				}
				
				$ini=new Bs_IniHandler();
				$ini->loadString($this->FDMArticaSets);
				$this->main_array=$ini->_params;
				
	}	
	
	
	function SaveToLdap(){
		$this->FixTree();
		$ini=new Bs_IniHandler();
		$ini->_params=$this->main_array;
		$upd["FDMArticaSets"][0]=$ini->toString();
		$upd["FDMConf"][0]=$this->BuildRealconfig();
		
		$ldap=new clladp();
		if(!$ldap->Ldap_modify($this->dn,$upd)){echo $ldap->ldap_last_error;}else{
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body('{success}');
		}
		
		
	}
	
	
	function BuildRealconfig(){
		$rules=$this->main_array;
		$user=new user($this->uid);
		$conf=$conf . "#uid=$this->uid\n";
		$conf=$conf . "set maximum-size 128M\n";
		$conf=$conf . "set queue-high 5\n";
		$conf=$conf . "set lock-file \"/tmp/lock.$this->uid\"\n";
		$conf=$conf . "\$db = \"/etc/fdm/$this->uid.db\"\n";
		$conf=$conf . "\$key = \"%[message_id]\"\n";
		$conf=$conf . "cache \$db\n";
		
		
		$conf=$conf . "match not string \$key to \"\" {\n";
		$conf=$conf . "\tmatch in-cache \$db key \$key action drop\n";
		$conf=$conf . "\tmatch all action add-to-cache \$db key \$key continue\n";
		$conf=$conf . "}\n";


		
		
		$conf=$conf . "\n";
		
		while (list ($num, $ligne) = each ($rules) ){
			$noverif=null;
			$keep=null;
			$type=$ligne["server_type"];
			$server=$ligne["server_name"];
			if($ligne["keep"]=="yes"){$keep=" keep ";}
			if($ligne["no-apop"]=="yes"){$noapop=" no-apop ";}
			if($type==null){continue;}
			if($server==null){continue;}
			
			if($type=='imaps'){$noverif=" no-verify ";}
			if($type=='pop3s'){$noverif=" no-verify ";}
			
			$conf=$conf ."account \"$num\" $type server \"$server\" user \"{$ligne["username"]}\" pass \"{$ligne["password"]}\"$noverif$keep$noapop\n";
			}
			
			$conf=$conf ."\naction \"action_{$this->uid}\" smtp server \"127.0.0.1\" to \"$user->mail\"\n";
			$conf=$conf . "match all action \"action_{$this->uid}\"\n";
		
			return $conf;
		
		
		
		
	}
	
	
	
}