<?php
include_once(dirname(__FILE__) . "/class.ldap.inc");	

	class mailfromd{
		var $dn;
		var $spamass_enabled=1;
		var $clamav_enabled=1;
		var $MailFromdUserUpdated=0;
		var $MailFromdUserScript;
		var $script;
		
		function mailfromd(){
			$this->VerifyLdapBranch();
			$this->ParseConfig();
			
			
		}
		
		
		
		function VerifyLdapBranch(){
		$ldap=new clladp();
		$ldap->ArticaCreate();
		$this->dn="cn=mailfromd,cn=artica,$ldap->suffix";
		
		
		if(!$ldap->ExistsDN($this->dn)){
			writelogs("building $dn it does not exists !!",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["ObjectClass"][]='top';
			$upd["ObjectClass"][]='mailfromd';
			$upd["cn"][]='mailfromd';
			$upd["MailFromdRC"][]=$this->BuildScript();
			$upd["MailFromdUserScript"]=$this->BuildScript();
			$upd["MailFromdUserUpdated"][]="0";
			$ldap->ldap_add($this->dn,$upd);
			unset($upd);
			}
		
		}
		
		function SaveToLdap(){
			$ldap=new clladp();
			$upd["MailFromdRC"][]=$this->BuildScript();
			$upd["MailFromdUserUpdated"][]=$this->MailFromdUserUpdated;
			$upd["MailFromdUserScript"][]=$this->MailFromdUserScript;
			$tpl=new templates();
			if($ldap->Ldap_modify($this->dn,$upd)){echo $tpl->_ENGINE_parse_body('{success}');}else{echo $ldap->ldap_last_error;}
			
		}
		
		
		function ParseConfig(){
				$ldap=new clladp();
				$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=mailfromd)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->script=$hash[0][strtolower('MailFromdRC')][0];
			$this->MailFromdUserScript=$hash[0][strtolower('MailFromdUserScript')][0];
			}
			
			if($this->MailFromdUserScript==null){$this->MailFromdUserScript=$this->BuildScript();}
			
			$tb=explode("\n",$this->script);
			while (list ($num, $val) = each ($tb) ){
				if(preg_match('#if\s+sa\("tcp#',$val)){$this->spamass_enabled=1;}
				if(preg_match('#if\s+clamav\("#',$val)){$this->clamav_enabled=1;}
				
			}

		}		
		
		function BuildScript(){
			
			$conf=$conf."#include_once <status.mfh>\n";
			$conf=$conf."#include_once <dns.mf>\n";
			$conf=$conf."#include_once <match_cidr.mf>\n";
			$conf=$conf."#pragma regex +extended +icase\n";
			$conf=$conf."   \n";
			$conf=$conf."\n";
			$conf=$conf."prog eom\n";
			$conf=$conf."\tdo\n";
			$conf=$conf.$this->clamav_script();
			$conf=$conf.$this->spamassassin_script();
			
			
			$conf=$conf."\n";
			
			$conf=$conf."done\n";
			
			return $conf;
			
			
		}
		
		
		function spamassassin_script(){
			if($this->spamass_enabled<>1){return null;}
			$conf=$conf."\tif sa(\"tcp://127.0.0.1:9031\", 3)\n";
			$conf=$conf."\t\treject 550 5.7.0\n";
			$conf=$conf."\t\t\"Spam detected, score %sa_score with threshold %sa_threshold\"\n";
			$conf=$conf."\tfi\n\n";
			
			return $conf;
			
			
		}
		
		
		function clamav_script(){
			$users=new usersMenus();
			if($users->CLAMAV_INSTALLED){
				if($this->clamav_enabled<>1){return null;}
				$conf=$conf."\tif clamav(\"$users->CLAMAV_SOCKET\")\n";
				$conf=$conf."\t\treject 550 5.7.0 \"Infected with %clamav_virus_name\"\n";
				$conf=$conf."\tfi\n\n";
				return $conf;
			}
			
		}
		
		function SaveToServer(){
			$socks=new sockets();
			$socks->getfile('mailfromd_settings');
			
		}
		
		
		
		

		
		
		
	}
	

?>