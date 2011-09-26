<?php
include_once(dirname(__FILE__)."/class.mysql.inc");

class maincf_multi{
	var $uuid;
	var $ou;
	var $ip_addr;
	var $myhostname;
	var $q;
	
	function maincf_multi($servername=null,$ou=null,$ip_addr=null){
			$this->ou=$ou;
			$this->ip_addr=$ip_addr;
			$this->myhostname=$servername;
			$this->q=new mysql();
			$stlen=strlen($servername)+strlen($ou)+strlen($ip_addr);
			if(!isset($GLOBALS["SYSTEM_UUID"])){$GLOBALS["SYSTEM_UUID"]=null;}
			if($stlen>0){
				$sock=new sockets();
				if($GLOBALS["SYSTEM_UUID"]==null){
					$this->uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
					$GLOBALS["SYSTEM_UUID"]=$this->uuid;
				}else{$this->uuid=$GLOBALS["SYSTEM_UUID"];}
				
				$this->CheckRequiredValues();
			}
	}
	
	public function remove_instance(){
		if($this->ip_addr==null){return;}
		$sql="DELETE FROM postfix_multi WHERE `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' AND `ou`='$this->ou'";
		$this->q->QUERY_SQL($sql,"artica_backup");
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-multi-reconfigure-all=yes");
		$sock->getFrameWork('cmd.php?restart-fetchmail=yes');
		$sock->getFrameWork("cmd.php?restart-postfix-single-now=yes");		
		}
	
	private function CheckRequiredValues_byname(){
		if($this->myhostname<>null){
			if(trim($this->ou)==null){
				$sql="SELECT `ou`,`ip_address` FROM postfix_multi WHERE `uuid`='$this->uuid' AND `key`='myhostname' and `value`='$this->myhostname' GROUP BY ou LIMIT 0,1";
				$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));
				$this->ou=$ligne["ou"];
				$this->ip_addr=$ligne["ip_address"];
				//if($this->ou==null){echo "OU=NULL\n$sql\n";}
			}
		}


		
	}
	
	public function CheckRequiredValues(){
		if($this->ou==null){$this->CheckRequiredValues_byname();}
		if($this->ip_addr==null){$this->CheckRequiredValues_byname();}
		
		if($this->ip_addr==null){
			$sql="SELECT `ip_address` FROM postfix_multi WHERE `uuid`='$this->uuid' AND `key`='myhostname' and `value`='$this->myhostname' LIMIT 0,1";
			$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));
			$this->ip_addr=$ligne["ip_address"];
		}

		if(trim($this->myhostname)==null){
			if($this->ip_addr<>null){
				$sql="SELECT `value` FROM postfix_multi WHERE `uuid`='$this->uuid' AND `key`='myhostname' AND `ip_address`='$this->ip_addr'";
				if($this->ou<>null){$sql="SELECT `value` FROM postfix_multi WHERE `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' and `key`='myhostname' AND ou='$this->ou'";}
			}
			$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));
			$this->myhostname=trim($ligne["value"]);
			
			if($this->myhostname==null){
				if($this->ip_addr<>null){
					$sql="SELECT `value` FROM postfix_multi WHERE `uuid`='$this->uuid' AND `key`='myhostname' AND `ip_address`='$this->ip_addr'";
					$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));
					if($ligne["value"]<>null){$this->myhostname=$ligne["value"];}
				}
			}
			
			if($this->myhostname<>null){$this->SET_VALUE("myhostname",$this->myhostname);}
			
			if($this->myhostname==null){
				writelogs("myhostname NULL VALUE \"$sql\"",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
	}
	
	public function GET($key){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='$key' AND `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));
		if(!$this->q->ok){writelogs("{$this->q->mysql_error} => GET($key)",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);	}
		return $ligne["value"];
	}
	
	public function GET_BIGDATA($key){
		$sql="SELECT `ValueTEXT` FROM postfix_multi WHERE `key`='$key' AND `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));
		if(!$this->q->ok){writelogs("{$this->q->mysql_error} => GET_BIGDATA($key)",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);	}
		if(trim($ligne["ValueTEXT"])==null){
			writelogs("[$this->uuid]: $this->ip_addr Loading key=$key (". strlen($ligne["ValueTEXT"]).")",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}	
		return $ligne["ValueTEXT"];
	}

public function SET_BIGDATA($key,$value){
		$sql="SELECT `ID` FROM postfix_multi WHERE `key`='$key' AND `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){
			writelogs("$this->ou::$this->uuid:: add $key ". strlen($value)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="INSERT INTO postfix_multi  (`ou`,`key`,`ValueTEXT`,`uuid`,`ip_address`) VALUES('$this->ou','$key','$value','$this->uuid','$this->ip_addr')";
		}else{
			writelogs("$this->ou::$this->uuid:: edit $key ({$ligne["ID"]}) ". strlen($value)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="UPDATE postfix_multi SET `ValueTEXT`='$value' WHERE ID={$ligne["ID"]}";
		}
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nSET_VALUE($key,$value)";
			return false;
		}
		return true;
	}		
	
	
	public function SET_VALUE($key,$value){
		writelogs("Postfix-multi set $key = $value",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		$sql="SELECT `ID` FROM postfix_multi WHERE `key`='$key' AND `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){
			$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`,`uuid`,`ip_address`) VALUES('$this->ou','$key','$value','$this->uuid','$this->ip_addr')";
		}else{
			$sql="UPDATE postfix_multi SET `value`='$value' WHERE ID={$ligne["ID"]}";
		}
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nSET_VALUE($key,$value)";
			return false;
		}
		
		return true;
	}	
	
	public function DELETE_KEY($key){
		$sql="SELECT `ID` FROM postfix_multi WHERE `key`='$key' AND `uuid`='$this->uuid' AND `ip_address`='$this->ip_addr' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){return;}
		$sql="DELETE FROM postfix_multi WHERE ID={$ligne["ID"]}";
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nDELETE_KEY($key)";
		}
	}
	
	
	public function check_client_access(){
		
		$target_file="/etc/postfix-$this->myhostname/check_client_access";
		if($this->myhostname=="master"){
			$target_file="/etc/postfix/check_client_access";
		}
		
		$check_client_access=unserialize(base64_decode($this->GET_BIGDATA("check_client_access")));
		if(is_array($check_client_access)){
			while (list ($num, $ligne) = each ($check_client_access) ){
				if($num==null){continue;}
				$conf[]="$num\t$ligne";
			}
		}
		
		if(isset($conf)){
			if(is_array($conf)){
				echo "Starting......: Postfix \"$this->myhostname\" check_client_access: ". count($conf)." entries\n";
				@file_put_contents($target_file,@implode("\n",$conf));
				$unix=new unix();
				$postmap=$unix->find_program("postmap");
				
				shell_exec("$postmap hash:$target_file");
				
				return "check_client_access hash:$target_file";
			}
		}
		
		
	}
	
	public function Blacklist_generic(){
		$sql="SELECT * FROM postfix_global_blacklist WHERE `hostname`='$this->myhostname' AND enabled=1";
		$q=new mysql();
		if(strtolower($this->myhostname)=="master"){
			$target="/etc/postfix/blacklist";
		}else{
			$target="/etc/postfix-$this->myhostname/blacklist";
		}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$senders=array();
		if(!$q->ok){echo "Starting......: Postfix mysql error $q->mysql_error on Blacklist_generic() function\n";return;}
		$already=array();
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$sender=trim($ligne["sender"]);
			if($sender==null){continue;}
			if(preg_match("#(.*?)@(.+)#",$sender,$re)){
				$part1=$re[1];
				$part2=$re[2];
				$part1=str_replace("*","",$part1);
				if(trim($part1)==null){$sender=$part2;}
			}
			if(isset($already[$sender])){if($already[$sender]){continue;}}
			$senders[]="$sender\tREJECT blacklisted sender";
			$already[$sender]=true;
		}
		echo "Starting......: Postfix \"$this->myhostname\" ".count($senders)." blacklisted item(s)\n";
		
		if(count($senders)>0){
			@file_put_contents($target,@implode("\n",$senders));
			shell_exec("{$GLOBALS["postmap"]} hash:$target >/dev/null 2>&1");
			return "check_sender_access hash:$target";
		}
		return null;
	}
	
	
	
	private function smtpd_sender_restrictions(){
			$restrictions=unserialize(base64_decode($this->GET_BIGDATA("hash_smtp_restrictions")));
			$RestrictToInternalDomains=$restrictions["RestrictToInternalDomains"];
			$EnablePostfixInternalDomainsCheck=$restrictions["EnablePostfixInternalDomainsCheck"];
			$smtpd_sender_restrictions_black=$this->Blacklist_generic();
	
			if($RestrictToInternalDomains==1){
				echo "Starting......: Postfix \"$this->myhostname\" set to restrict sender domain to only known domains\n";
				$this->BuildAllWhitelistedServer();
				$this->BuildAllMyDomains();
				$smtpd_sender_restrictions[]="check_client_access hash:/etc/postfix-$this->myhostname/all_whitelisted_servers";
				$smtpd_sender_restrictions[]="check_sender_access hash:/etc/postfix-$this->myhostname/all_internal_domains";
				if($smtpd_sender_restrictions_black<>null){$smtpd_sender_restrictions[]=$smtpd_sender_restrictions_black;}
				$smtpd_sender_restrictions[]="reject";
			}else{
				if($smtpd_sender_restrictions_black<>null){$smtpd_sender_restrictions[]=$smtpd_sender_restrictions_black;}
			}
			
		if(!isset($restrictions["disable_vrfy_command"])){$restrictions["disable_vrfy_command"]=1;}
		if(!is_numeric($restrictions["disable_vrfy_command"])){$restrictions["disable_vrfy_command"]=0;}
		if($restrictions["disable_vrfy_command"]==1){
			$final[]="disable_vrfy_command = yes";
		}else{
			$final[]="disable_vrfy_command = no";
		}
		
	
		if(!is_array($smtpd_sender_restrictions)){return @implode("\n",$final);}
		$final[]="smtpd_sender_restrictions = ".@implode(",",$smtpd_sender_restrictions);
		return @implode("\n",$final);
	}
	
	function BuildAllMyDomains(){
		$ldap=new clladp();
		$hash=$ldap->Hash_domains_table($this->ou);
		while (list ($domain, $ligne) = each ($hash) ){	
			$domain=trim($domain);
			if($domain==null){continue;}
			$doms[]="$domain\tOK";
		}
		echo "Starting......: Postfix \"$this->myhostname\" store ". count($doms)." domains\n";
		@file_put_contents("/etc/postfix-$this->myhostname/all_internal_domains",@implode("\n",$doms));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/all_internal_domains >/dev/null 2>&1");
	
	}	

	private function BuildAllWhitelistedServer(){
		$q=new mysql();
		$sql="SELECT * FROM postfix_whitelist_con";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "$q->mysql_error\n";}
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){		
				if($ligne["ipaddr"]<>null){
					$f[]="{$ligne["ipaddr"]}\tOK";
				}
				if($ligne["hostname"]<>null){
					$f[]="{$ligne["hostname"]}\tOK";
				}
		}
				
		
		echo "Starting......: Postfix \"$this->myhostname\" store ". count($f)." whitelisted servers\n";
		@file_put_contents("/etc/postfix-$this->myhostname/all_whitelisted_servers",@implode("\n",$f));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/all_whitelisted_servers >/dev/null 2>&1");
	}

	

private function restrict_relay_domains(){
	$ldap=new clladp();
	$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	$attr=array("cn");
	$pattern="(&(objectclass=PostfixRelayRecipientMaps)(cn=@*))";
	$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	$relaysdomains=$ldap->hash_get_relay_domains();
	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		if(preg_match("#^@(.+)#",$domain,$re)){$domain=$re[1];}
		unset($relaysdomains[$domain]);
	}
	
	unset($relaysdomains["localhost.localdomain"]);
	if(is_array($relaysdomains)){
		while (list ($num, $ligne) = each ($relaysdomains) ){
			$f[]="$num\tartica_restrict_relay_domains";
		}
	}
	echo "Starting......: Postfix \"$this->myhostname\"". count($f)." restricted relayed domains\n"; 
	@file_put_contents("hash:/etc/postfix-$this->myhostname/relay_domains_restricted",implode("\n",$f));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/relay_domains_restricted >/dev/null 2>&1");
	
}	
		
		
	
	
	private function smtpd_client_restrictions(){
		$this->amavis_bypass_byrecipients();
		$this->amavis_internal();
		$EnableSasl=$this->GET("EnableSasl");
		
		$users=new usersMenus();
		$sock=new sockets();
		if($users->CLUEBRINGER_INSTALLED){
			$EnableCluebringer=$sock->GET_INFO("EnableCluebringer");
			if($EnableCluebringer==1){
				$array_filters=unserialize(base64_decode($this->GET_BIGDATA("PluginsEnabled")));
				if($array_filters["APP_CLUEBRINGER"]==1){
					echo "Starting......: Postfix \"$this->myhostname\" cluebringer is enabled.\n";
					$smtpd_recipient_restrictions[]="check_policy_service inet:127.0.0.1:13331";
				}
			}
		}		
		
		
		$smtpd_client_restrictions[]="permit_mynetworks";
		$smtpd_client_restrictions[]="check_client_access hash:/etc/postfix-$this->myhostname/amavis_internal";
		
		$smtpd_helo_restrictions[]="permit_mynetworks";
		$smtpd_recipient_restrictions[]="permit_mynetworks";
		$smtpd_recipient_restrictions[]="check_recipient_access hash:/etc/postfix-$this->myhostname/relay_domains_restricted";
		$smtpd_recipient_restrictions[]="check_recipient_access hash:/etc/postfix-$this->myhostname/amavis_bypass_rcpt";
		$this->restrict_relay_domains();
		
		if($EnableSasl==1){
			$smtpd_client_restrictions[]="permit_sasl_authenticated";
			$smtpd_helo_restrictions[]="permit_sasl_authenticated";
			$smtpd_recipient_restrictions[]="permit_sasl_authenticated";
		}
		$restrictions=unserialize(base64_decode($this->GET_BIGDATA("hash_smtp_restrictions")));
		
		if($restrictions["reject_unknown_client_hostname"]==1){$smtpd_client_restrictions[]="reject_unknown_client_hostname";}
		if($restrictions["reject_invalid_hostname"]==1){
			$smtpd_client_restrictions[]="reject_invalid_hostname";
			$smtpd_helo_restrictions[]="reject_invalid_hostname";
			$smtpd_helo_restrictions[]="reject_non_fqdn_hostname";
		}
		
		
		if($restrictions["reject_unknown_reverse_client_hostname"]==1){$smtpd_client_restrictions[]="reject_unknown_reverse_client_hostname";}
		if($restrictions["reject_unknown_sender_domain"]==1){$smtpd_client_restrictions[]="reject_unknown_sender_domain";}
		if($restrictions["reject_non_fqdn_sender"]==1){$smtpd_client_restrictions[]="reject_non_fqdn_sender";}		
		
		$smtpd_client_restrictions[]=$this->check_client_access();
		
		if($restrictions["EnablePostfixAntispamPack"]==1){
				$smtpd_client_restrictions[]="reject_rbl_client zen.spamhaus.org";
				$smtpd_client_restrictions[]="reject_rbl_client sbl.spamhaus.org";
				$smtpd_client_restrictions[]="reject_rbl_client cbl.abuseat.org";
		}

		$smtpd_recipient_restrictions[]="reject_unauth_destination";
		
		$smtpd_client_restrictions[]="permit";
		$smtpd_helo_restrictions[]="permit";
		$smtpd_recipient_restrictions[]="permit";

    
		$smtpd_client_restrictions=$this->CleanArraySMTD($smtpd_client_restrictions);
		$smtpd_recipient_restrictions=$this->CleanArraySMTD($smtpd_recipient_restrictions);
		$smtpd_helo_restrictions=$this->CleanArraySMTD($smtpd_helo_restrictions);
		

		$conf[]="smtpd_restriction_classes = ";
		if(count($smtpd_recipient_restrictions)>0){
			$conf[]="smtpd_recipient_restrictions = ". implode(",",$smtpd_recipient_restrictions);
		}
		if(count($smtpd_client_restrictions)>0){
			$conf[]="smtpd_client_restrictions = ". implode(",",$smtpd_client_restrictions);
		}
		
		if(count($smtpd_helo_restrictions)>0){
			$conf[]="smtpd_helo_restrictions = ". implode(", ",$smtpd_helo_restrictions);
		}
		return @implode("\n",$conf);
		}
		
	private function CleanArraySMTD($array){
		if(!is_array($array)){return;}
		while (list ($num, $params) = each ($array) ){if(trim($params)==null){continue;}$TEMPAR[$params]=$params;}
		if(!is_array($TEMPAR)){return;}
		unset($array);
		while (list ($num, $params) = each ($TEMPAR)){$array[]=$params;}
		return $array;
		
	}
		
	private function smtpd_end_of_data_restrictions(){
		$users=new usersMenus();
		$sock=new sockets();
		$EnableArticaPolicyFilter=$sock->GET_INFO("EnableArticaPolicyFilter");
		$EnableCluebringer=$sock->GET_INFO("EnableCluebringer");
		$array_filters=unserialize(base64_decode($this->GET_BIGDATA("PluginsEnabled")));
		if($array_filters["APP_POSTFWD2"]==1){
			$sql="SELECT count(*) as tcount FROM postfwd2 WHERE enabled=1 AND instance='$this->myhostname'";
			$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
			if($ligne["tcount"]>0){
				$smtpd_end_of_data_restrictions[]="check_policy_service inet:$this->ip_addr:10040";
			}
		}
		
		
		if($EnableArticaPolicyFilter==1){
			$smtpd_end_of_data_restrictions[]="check_policy_service inet:127.0.0.1:54423";
		}
		if($users->CLUEBRINGER_INSTALLED){
			if($EnableCluebringer==1){
				$array_filters=unserialize(base64_decode($this->GET_BIGDATA("PluginsEnabled")));
				if($array_filters["APP_CLUEBRINGER"]==1){
					$smtpd_end_of_data_restrictions[]="check_policy_service inet:127.0.0.1:13331";
				}
			}
		}		
			
		if(!is_array($smtpd_end_of_data_restrictions)){return;}
		$final=@implode(",",$smtpd_end_of_data_restrictions);
		return "smtpd_end_of_data_restrictions = $final";	
	}	

	

	
	
	public function header_checks(){
		$target_file="/etc/postfix-$this->myhostname/header_checks";
		$sock=new sockets();
		$users=new usersMenus();
		$EnableArticaPolicyFilter=$sock->GET_INFO("EnableArticaPolicyFilter");
		if(!is_numeric($EnableArticaPolicyFilter)){$EnableArticaPolicyFilter=0;}
		$EnableAmavisDaemon=$users->EnableAmavisDaemon;
		if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
		if(!is_numeric($EnableAmavisDaemon)){$EnableAmavisDaemon=0;}		
		
		$nets=unserialize($this->GET_BIGDATA("mynetworks"));
		if($this->myhostname=="master"){
			$target_file="/etc/postfix/header_checks";
			$ldap=new clladp();
			$nets=$ldap->load_mynetworks();
		}
		
		
				
		
		
		if(is_file($target_file)){@unlink($target_file);}
		$data=unserialize(base64_decode($this->GET_BIGDATA("header_check")));
		if(is_array($data)){
			while (list ($num, $ligne) = each ($data) ){
				if($ligne==null){continue;}
				$conf[]=$ligne;
			}
		}
		
		$PostfixHideClientMua=$this->GET("PostfixHideClientMua");
		$max_rcpt_to=$this->GET("max_rcpt_to");
		if(!is_numeric($max_rcpt_to)){$max_rcpt_to=0;}
		
		
		if($EnableAmavisDaemon==1){
			if($max_rcpt_to>0){
				echo "Starting......: Postfix \"$this->myhostname\" activate Maximum recipients in headers...\n";
				$conf[]="/^To:([^@]*@){".$max_rcpt_to.",}/	REJECT Sorry, your message has too many recipients $max_rcpt_to.";
				$conf[]="/^Cc:([^@]*@){".$max_rcpt_to.",}/	REJECT Sorry, your message has too many recipients $max_rcpt_to.";
				$conf[]="/^Bcc:([^@]*@){".$max_rcpt_to.",}/	REJECT Sorry, your message has too many recipients $max_rcpt_to.";
			}
		}
		
		
		
		if($PostfixHideClientMua==1){
			$conf[]="/^Received:.*\[127\.0\.0\.1/      IGNORE Make E-Mail Anonymous";
			$conf[]="/^Received:/    IGNORE  Make E-Mail Anonymous";
			$conf[]="/^User-Agent:/  IGNORE  Hide User-Agent";
			
			
			if(is_array($nets)){
				echo "Starting......: Postfix \"$this->myhostname\" activate hide MUA in headers...\n";
				while (list ($num, $network) = each ($nets) ){
					if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$network,$re)){
						$conf[]="/^Received:.*\[{$re[1]}\.{$re[2]}\.{$re[3]}.\.[0-9]/      IGNORE Make E-Mail Anonymous";
					}
				}				
			}
		}
		
		
		$GLOBALS["POSTFIX_HEADERS_CHECK_BUILDED"]=true;
		if(!is_array($conf)){return null;}
		@file_put_contents("$target_file",@implode("\n",$conf));
		echo "Starting......: Postfix \"$this->myhostname\" ". count($conf)." checks headers rules\n";
		return "header_checks = regexp:$target_file";
		
	}
	
private function amavis_bypass_byrecipients(){
	$users=new usersMenus();
	$q=new mysql();
	$unix=new unix();
	$sock=new sockets();
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	if($EnableAmavisDaemon==1){
		if($EnableAmavisInMasterCF==1){
			$sql="SELECT * FROM amavis_bypass_rcpt ORDER BY `pattern`";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";return 0;}	
			$count=0;
			$f=array();
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$ligne["pattern"]=trim($ligne["pattern"]);
				$ip=trim($ligne["pattern"]);
				if($ip==null){continue;}
				if(is_array($ip)){continue;}
				$count++;
				$f[]="{$ligne["pattern"]}\tFILTER smtp:[$this->ip_addr]:10026";
			}
		}
	}
	$postmap=$unix->find_program("postmap");
	echo "Starting......: ". count($f) ." bypass recipient(s) for amavisd new\n"; 	
	
	$f[]="";
	@file_put_contents("/etc/postfix-$this->myhostname/amavis_bypass_rcpt",@implode("\n",$f));
	shell_exec("$postmap hash:/etc/postfix-$this->myhostname/amavis_bypass_rcpt");
	return $count;
	}	

	
private function amavis_internal(){
	$users=new usersMenus();
	$q=new mysql();
	$unix=new unix();
	$sock=new sockets();
	$EnableAmavisDaemon=$sock->GET_INFO('EnableAmavisDaemon');
	$EnableAmavisInMasterCF=$sock->GET_INFO('EnableAmavisInMasterCF');
	if(!$users->AMAVIS_INSTALLED){$EnableAmavisDaemon=0;}
	if($EnableAmavisDaemon==1){
		if($EnableAmavisInMasterCF==1){
			$sql="SELECT * FROM amavisd_bypass ORDER BY ip_addr";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";return 0;}	
			$count=0;
			$f=array();
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$ligne["ip_addr"]=trim($ligne["ip_addr"]);
				$ip=trim($ligne["ip_addr"]);
				if($ip==null){continue;}
				if(is_array($ip)){continue;}
				$count++;
				$f[]="{$ligne["ip_addr"]}\tFILTER smtp:[$this->ip_addr]:10026";
			}
		}
	}
	
	$f[]="";
	@file_put_contents("/etc/postfix-$this->myhostname/amavis_internal",@implode("\n",$f));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/amavis_internal");
	return $count;
	
}		
	
	private function relais_domains(){
		if(is_file("/etc/postfix-$this->myhostname/relay_domains")){@unlink("/etc/postfix-$this->myhostname/relay_domains");}
		$ldap=new clladp();
		$filter="(&(objectClass=PostFixRelayDomains)(cn=*))";
		$attrs=array("cn");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		for($i=0;$i<$hash["count"];$i++){
			$relay_domains[]=$hash[$i]["cn"][0]."\tOK";
		}	
		if(!is_array($relay_domains)){return "relay_domains = \$mydestination";}
		echo "Starting......: Postfix \"$this->myhostname\" ". count($relay_domains)." relay domain(s)\n";
		@file_put_contents("/etc/postfix-$this->myhostname/relay_domains",implode("\n",$relay_domains));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/relay_domains >/dev/null 2>&1");
		return "relay_domains = \$mydestination, hash:/etc/postfix-$this->myhostname/relay_domains";
	}
	
private function recipient_bcc_domain_maps(){
	$sql="SELECT * FROM postfix_duplicate_maps WHERE ou='$this->ou'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["pattern"]==null){continue;}	
		
		$left="(.*)";
		$right='${1}';
		$leftNext="(.*)";
		$rightNext='${1}';
		$domain=$ligne["pattern"];
		$nextdomain=$ligne["nextdomain"];
		$nextdomain_transport=$ligne["nextdomain"];

		
		
		if(preg_match("#(.+?)@(.+)#",$ligne["pattern"],$re)){
			$nextHope_pattern=$ligne["pattern"];
			$domain=$re[2];
			$left=$re[1];
			$right=$re[1];
			$rightNext=$right;
			$left=str_replace(".","\.",$left);
			$right=str_replace(".","\.",$right);
			$leftNext=$left;
		}
		
		if(preg_match("#(.+?)@(.+)#",$ligne["nextdomain"],$re)){
			$right=$re[1];
			$nextdomain=$re[2];
			
		}		
		
		$md5=md5($domain);
		$domain_regex=str_replace(".","\.",$domain);
		$f[]="/^$left@$domain_regex$/   $right@$nextdomain";
		$t[]="$nextdomain_transport\tsmtp:[{$ligne["relay"]}]:{$ligne["port"]}";
		$c++;
	}
	echo "Starting......: ".count($f)." duplicated destination(s)\n"; 
	$f[]="";
	@file_put_contents("/etc/postfix-$this->myhostname/copy.pcre",implode("\n",$f));
	@file_put_contents("/etc/postfix-$this->myhostname/copy.transport",implode("\n",$t));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/copy.transport >/dev/null 2>&1");	
}	

		
	private function alias_maps(){
		if(is_file("/etc/postfix-$this->myhostname/virtual")){@unlink("/etc/postfix-$this->myhostname/virtual");}
		if(is_file("/etc/postfix-$this->myhostname/aliases")){@unlink("/etc/postfix-$this->myhostname/aliases");}
		$ldap=new clladp();
		$filter="(&(objectClass=userAccount)(mailAlias=*))";
		$attrs=array("mail","mailAlias");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		echo "Starting......: Postfix \"$this->myhostname\" mailAlias: {$hash["count"]} aliases in $this->ou organization\n";
		
		
		for($i=0;$i<$hash["count"];$i++){
			$mail=trim($hash[$i]["mail"][0]);
			for($t=0;$t<$hash[$i]["mailalias"]["count"];$t++){
				$hash[$i]["mailalias"][$t]=trim($hash[$i]["mailalias"][$t]);
				if($hash[$i]["mailalias"][$t]==null){continue;}
				$virtual_alias_maps[]="{$hash[$i]["mailalias"][$t]}\t$mail";
			}
		}

		$filter="(&(objectClass=MailingAliasesTable)(cn=*))";
		$attrs=array("cn","MailingListAddress","MailingListAddressGroup");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		
		for($i=0;$i<$hash["count"];$i++){
			$cn=$hash[$i]["cn"][0];
			$MailingListAddressGroup=$hash[$i]["mailinglistaddressgroup"][0];
			for($t=0;$t<$hash[$i]["mailinglistaddress"]["count"];$t++){
				$hash[$i]["mailinglistaddress"][$t]=trim($hash[$i]["mailinglistaddress"][$t]);
				if($hash[$i]["mailinglistaddress"][$t]==null){continue;}
				$mailinglistaddress[$hash[$i]["mailinglistaddress"][$t]]=$hash[$i]["mailinglistaddress"][$t];
			}
			
			if($MailingListAddressGroup==1){
				$uid=$ldap->uid_from_email($cn);
				$user=new user($uid);
				$array=$user->MailingGroupsLoadAliases();
				while (list ($num, $ligne) = each ($array) ){
	    			if($ligne==null){continue;}  	
	    			$mailinglistaddress[$ligne]=$ligne;
	    		}	
			}
			
			if(is_array($mailinglistaddress)){
					while (list ($num, $ligne) = each ($mailinglistaddress) ){
						$final[]=$num;
					}
					if(trim($cn)<>null){
						$virtual_alias_maps[]="$cn\t". implode(",",$final);
					}
				}	
				
			unset($final);
			unset($mailinglistaddress);
			$MailingListAddressGroup=0;
			}		

	
		$filter="(&(objectClass=AdditionalPostfixMaps)(cn=*))";
		$attrs=array("cn","CatchAllPostfixAddr");
		$dn="cn=$this->ou,cn=catch-all,cn=artica,$ldap->suffix";
		
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
		for($i=0;$i<$hash["count"];$i++){
			$cn=trim($hash[$i]["cn"][0]);
			if(trim($cn)==null){continue;}
			for($t=0;$t<$hash[$i][strtolower("CatchAllPostfixAddr")]["count"];$t++){
				echo "Starting......: catch-all {$hash[$i][strtolower("CatchAllPostfixAddr")][$t]} for $cn\n";
				if(substr($cn,0,1)<>"@"){$cn="@$cn";}
				$hash[$i][strtolower("CatchAllPostfixAddr")][$t]=trim($hash[$i][strtolower("CatchAllPostfixAddr")][$t]);
				if($hash[$i][strtolower("CatchAllPostfixAddr")][$t]==null){continue;}
				$virtual_alias_maps[]="$cn\t{$hash[$i][strtolower("CatchAllPostfixAddr")][$t]}";
			}
		}
	
		$filter="(&(objectClass=userAccount)(uid=*))";
		$attrs=array("uid","mail");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);

		for($i=0;$i<$hash["count"];$i++){
			$uid=trim($hash[$i]["uid"][0]);
			if($uid==null){continue;}
			for($t=0;$t<$hash[$i]["mail"]["count"];$t++){
				$hash[$i]["mail"][$t]=trim($hash[$i]["mail"][$t]);
				if($hash[$i]["mail"][$t]==null){continue;}
				$alias_maps[]="$uid:{$hash[$i]["mail"][$t]}";
				$virtual_mailbox[]="{$hash[$i]["mail"][$t]}\t$uid";
				$virtual_alias_maps[]="{$hash[$i]["mail"][$t]}\t{$hash[$i]["mail"][$t]}";
			}
		}

		$filter="(&(objectClass=transportTable)(cn=*@*))";
		$attrs=array("cn");
		$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		for($i=0;$i<$hash["count"];$i++){
			$cn=$hash[$i]["cn"][0];
			$alias_maps[]="$cn:x";
			}	
			
		if(is_array($GLOBALS["LDAPDBS"]["virtual_mailbox_maps"])){
			$virtual_mailbox_maps_cf=$GLOBALS["LDAPDBS"]["virtual_mailbox_maps"];
		}	

		if(is_array($GLOBALS["LDAPDBS"]["virtual_alias_maps"])){
			$virtual_alias_maps_cf=$GLOBALS["LDAPDBS"]["virtual_alias_maps"];
		}	

		if(is_array($GLOBALS["LDAPDBS"]["alias_database"])){
			$alias_database_cf=$GLOBALS["LDAPDBS"]["alias_database"];
		}	

		
		$sql="SELECT * FROM postfix_aliases_domains WHERE ou='$this->ou'";
		$q=new mysql();
		$pre='${1}';
		$li=array();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$aliases=str_replace(".","\.",$ligne["alias"]);
			$domain=$ligne["domain"];
			$li[]="/^(.*)@$aliases$/\t$pre@$domain";
			$virtual_alias_maps[]="{$ligne["alias"]}\tDOMAIN";
		}	

		$virtual_alias_maps_cf[]="pcre:/etc/postfix-$this->myhostname/virtual.domains";
		@file_put_contents("/etc/postfix-$this->myhostname/virtual.domains",@implode("\n",$li));
			
		if(is_array($virtual_alias_maps)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($virtual_alias_maps)." virtuals aliases\n";
			$virtual_mailbox_maps_cf[]="hash:/etc/postfix-$this->myhostname/virtual";
			$virtual_alias_maps_cf[]="hash:/etc/postfix-$this->myhostname/virtual";
			@file_put_contents("/etc/postfix-$this->myhostname/virtual",@implode("\n",$virtual_alias_maps));
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/virtual >/dev/null 2>&1");
		}
		

		

		if(is_array($alias_maps)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($alias_maps)." aliases\n";
			$alias_maps_cf[]="hash:/etc/postfix-$this->myhostname/aliases" .$this->mailman_aliases();
			$alias_database_cf[]="hash:/etc/postfix-$this->myhostname/aliases";
			@file_put_contents("/etc/postfix-$this->myhostname/aliases",@implode("\n",$alias_maps));
			//shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/aliases >/dev/null 2>&1");
			shell_exec("{$GLOBALS["postalias"]} -c /etc/postfix-$this->myhostname hash:/etc/postfix-$this->myhostname/aliases >/dev/null 2>&1");
			 	
		}
		
		if(is_array($virtual_mailbox_maps_cf)){$conf[]="virtual_mailbox_maps = ".@implode(",",$virtual_mailbox_maps_cf);}
		if(is_array($virtual_alias_maps_cf))  {$conf[]="virtual_alias_maps = ".@implode(",",$virtual_alias_maps_cf);}
		if(is_array($alias_maps_cf))  		  {$conf[]="alias_maps = ".@implode(",",$alias_maps_cf);}
		if(is_array($alias_database_cf))  	  {$conf[]="alias_database_cf = ".@implode(",",$alias_database_cf);}		
		if(!is_array($conf)){return null;}
		return @implode("\n",$conf);
		
		
	}
	
	private function mailman_aliases(){
		if(!is_file("/var/lib/mailman/bin/genaliases")){
			echo "Starting......: Unable to locate genaliases tool\n";
			return null;
		}
	
		shell_exec("/var/lib/mailman/bin/genaliases");
		if(is_file("/var/lib/mailman/data/aliases")){return ",hash:/var/lib/mailman/data/aliases";}
		if(is_file("/var/lib/mailman/data/virtual-mailman")){return ",hash:/var/lib/mailman/data/virtual-mailman";}
	}

	
private function transport_maps(){
	if(is_file("/etc/postfix-$this->myhostname/transport")){@unlink("/etc/postfix-$this->myhostname/transport");}
	if(!is_file("/etc/postfix-$this->myhostname/transport.throttle")){@file_put_contents("/etc/postfix-$this->myhostname/transport.throttle"," ");}
	
	
	$ldap=new clladp();
	$filter="(&(objectClass=transportTable)(cn=*))";
	$attrs=array("cn","transport");
	$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		$transport=$hash[$i]["transport"][0];
		
		if(substr($domain,0,1)=="@"){$domain=substr($domain,1,strlen($domain));}
		$transport_maps[$domain]="$transport";
		
		if(strpos("  $domain","@")==0){$domain="@$domain";}
		$transport_maps_AT[$domain]="$transport";
		}
		
	if(is_array($GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"])){
		while (list ($domain, $targeted_ip) = each ($GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"]) ){
			$transport="relay[$targeted_ip]:25";
			$transport_maps[$domain]=$transport;
			$transport_maps_AT["@$domain"]=$transport;
		}
		
	}		
		
	$dn="cn=artica_smtp_sync,cn=artica,$ldap->suffix";
  	$filter="(&(objectClass=InternalRecipients)(cn=*))";	
  	$attrs=array("cn","ArticaSMTPSenderTable");	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		
	for($i=0;$i<$hash["count"];$i++){
		$email=$hash[$i]["cn"][0];
		$transport=$hash[$i][strtolower("ArticaSMTPSenderTable")][0];
		$uid=$ldap->uid_from_email($email);
		if($uid<>null){continue;}
		$transport_maps["$email"]="$transport";
		}

	if(is_array($transport_maps)){
		while (list ($num, $ligne) = each ($transport_maps) ){
			if($ligne==null){continue;}
			$array[]="$num\t$ligne";
			}
	}
	
	if(is_array($transport_maps_AT)){
		while (list ($num, $ligne) = each ($transport_maps_AT) ){
			if($ligne==null){continue;}
			$array[]="$num\t$ligne";
			}
	}
	
	if(is_array($array)){
		echo "Starting......: Postfix \"$this->myhostname\" ". count($array)." transport rules\n";
		@file_put_contents("/etc/postfix-$this->myhostname/transport",implode("\n",$array));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/transport >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/transport.throttle >/dev/null 2>&1");
		return "transport_maps = hash:/etc/postfix-$this->myhostname/transport.throttle, hash:/etc/postfix-$this->myhostname/transport, hash:/etc/postfix-$this->myhostname/transport.banned,hash:/etc/postfix-$this->myhostname/copy.transport";
	}	
	$this->bann_destination_domains();
	
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/transport.throttle >/dev/null 2>&1");
	return "transport_maps = hash:/etc/postfix-$this->myhostname/transport.throttle,hash:/etc/postfix-$this->myhostname/transport.banned,hash:/etc/postfix-$this->myhostname/copy.transport";
	
}	

	public function bann_destination_domains(){
			$sql="SELECT * FROM postfix_bad_domains WHERE hostname='$this->myhostname' ORDER BY domain";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){return null;}
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$f[]="{$ligne["domain"]}\terror:5.1.2 Banned destination domain {$ligne["domain"]}";
				
			}

			if($this->myhostname=="master"){
				$path="/etc/postfix/transport.banned";
			}else{
				$path="/etc/postfix-$this->myhostname/transport.banned";
			}
			
			@file_put_contents("$path",@implode("\n",$f));
			shell_exec("{$GLOBALS["postmap"]} hash:$path >/dev/null 2>&1");
	}


	

	private function mydestination(){
		if(is_file("/etc/postfix-$this->myhostname/mydestination")){@unlink("/etc/postfix-$this->myhostname/mydestination");}
		$ldap=new clladp();
	
		$filter="(&(objectClass=organizationalUnit)(associatedDomain=*))";
		$attrs=array("associatedDomain");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		
		if($GLOBALS["VERBOSE"]){echo "$dn -> $filter\n";}
		
		$sr = @ldap_search($ldap->ldap_connection,$dn,$filter,$attrs);
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($GLOBALS["VERBOSE"]){print_r($hash);}
	
		for($i=0;$i<$hash["count"];$i++){
			for($t=0;$t<$hash[$i]["associateddomain"]["count"];$t++){
				$mydestination[]=$hash[$i][strtolower("associatedDomain")][$t]."\tOK";
			}
		}
		
		}else{
		if($GLOBALS["VERBOSE"]){echo $ldap->ldap_last_error."\n";}
		}
		
		if(is_array($mydestination)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($array)." local destination domains\n";
			@file_put_contents("/etc/postfix-$this->myhostname/mydestination",implode("\n",$mydestination));
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/mydestination >/dev/null 2>&1");
			return "mydestination = hash:/etc/postfix-$this->myhostname/mydestination";
		}else{
			echo "Starting......: Postfix \"$this->myhostname\" NO local destination domains\n";	
			return "#mydestination unable to stat associatedDomain in $dn";
		}
	}

	
	private function sender_canonical_maps(){
		if(is_file("/etc/postfix-$this->myhostname/sender_canonical")){@unlink("/etc/postfix-$this->myhostname/sender_canonical");}
		$ldap=new clladp();
		$filter="(&(objectClass=userAccount)(mail=*))";
		$attrs=array("mail","SenderCanonical");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["mail"][0];
			$canonical=$hash[$i][strtolower("SenderCanonical")][0];
			if($canonical==null){continue;}
			$sender_canonical_maps[]="$mail\t$canonical";
			$smtp_generic_maps[]="$mail\t$canonical";
		}

		$q=new mysql();
		$sql="SELECT * FROM smtp_generic_maps WHERE ou='$this->ou' ORDER BY generic_from";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(trim($ligne["generic_from"])==null){continue;}
			if(trim($ligne["generic_to"])==null){continue;}
			$smtp_generic_maps[]="{$ligne["generic_from"]}\t{$ligne["generic_to"]}";
		}
		
		
		if(is_array($sender_canonical_maps)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($sender_canonical_maps)." sender retranslation rule(s)\n"; 
			@file_put_contents("/etc/postfix-$this->myhostname/sender_canonical",implode("\n",$sender_canonical_maps));
			$f[]="sender_canonical_maps = hash:/etc/postfix-$this->myhostname/sender_canonical";
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/sender_canonical >/dev/null 2>&1");
		}
		if(is_array($smtp_generic_maps)){	
			@file_put_contents("/etc/postfix-$this->myhostname/smtp_generic_maps",implode("\n",$smtp_generic_maps));
			$f[]="smtp_generic_maps = hash:/etc/postfix-$this->myhostname/smtp_generic_maps";
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/smtp_generic_maps >/dev/null 2>&1");
		}
			
		if(is_array($f)){return @implode("\n",$f);}
	
	}

	

private	function sender_dependent_relayhost_maps(){
		if(is_file("/etc/postfix-$this->myhostname/sender_dependent_relayhost")){@unlink("/etc/postfix-$this->myhostname/sender_dependent_relayhost");}
		$ldap=new clladp();
		$filter="(&(objectClass=SenderDependentRelayhostMaps)(cn=*))";
		$attrs=array("cn","SenderRelayHost");
		$dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["cn"][0];
			$value=$hash[$i][strtolower("SenderRelayHost")][0];
			if($value==null){continue;}
			$sender_dependent_relayhost_maps[]="$mail\t$value";
		}
		
		$filter="(&(objectClass=userAccount)(mail=*))";
		$attrs=array("mail","AlternateSmtpRelay");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["mail"][0];
			$value=$hash[$i][strtolower("AlternateSmtpRelay")][0];
			if($value==null){continue;}
			$sender_dependent_relayhost_maps[]="$mail\t$value";
		}	
		
		$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
		$attrs=array("cn","SenderCanonicalRelayHost");
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["cn"][0];
			$value=$hash[$i][strtolower("SenderCanonicalRelayHost")][0];
			if($value==null){continue;}
			$sender_dependent_relayhost_maps[]="$mail\t$value";
		}
		
		if(is_array($sender_dependent_relayhost_maps)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($sender_dependent_relayhost_maps)." sender dependent relayhost rule(s)\n"; 
			@file_put_contents("/etc/postfix-$this->myhostname/sender_dependent_relayhost",implode("\n",$sender_dependent_relayhost_maps));
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/sender_dependent_relayhost >/dev/null 2>&1");
			return "sender_dependent_relayhost_maps = hash:/etc/postfix-$this->myhostname/sender_dependent_relayhost";
		}
	}

	private function recipient_canonical_maps(){
		if(is_file("/etc/postfix-$this->myhostname/recipient_canonical")){@unlink("/etc/postfix-$this->myhostname/recipient_canonical");}
		$ldap=new clladp();
		$filter="(&(objectClass=RecipientCanonicalMaps)(cn=*))";
		$attrs=array("cn","MailAlternateAddress");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["cn"][0];
			$canonical=$hash[$i][strtolower("MailAlternateAddress")][0];
			$recipient_canonical_maps[]="$mail\t$canonical";
		}
	
		if(is_array($recipient_canonical_maps)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($recipient_canonical_maps)." sender dependent relayhost rule(s)\n"; 
			@file_put_contents("/etc/postfix-$this->myhostname/recipient_canonical",implode("\n",$recipient_canonical_maps));
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/recipient_canonical >/dev/null 2>&1");
			return "recipient_canonical_maps = hash:/etc/postfix-$this->myhostname/recipient_canonical";
		}		
		
	}	
	
private	function recipient_bcc_maps(){
		if(is_file("/etc/postfix-$this->myhostname/recipient_bcc")){@unlink("/etc/postfix-$this->myhostname/recipient_bcc");}
		if(!is_file("/etc/postfix-$this->myhostname/copy.pcre")){@file_put_contents("/etc/postfix-$this->myhostname/copy.pcre"," ");}
		$ldap=new clladp();
		$filter="(&(objectClass=UserArticaClass)(RecipientToAdd=*))";
		$attrs=array("RecipientToAdd","mail");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["mail"][0];
			$RecipientToAdd=$hash[$i]["recipienttoadd"][0];
			$bcc_maps[]="$mail\t$RecipientToAdd";
			
		}	
		
		if(is_array($bcc_maps)){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($bcc_maps)." recipient(s) BCC\n"; 
			@file_put_contents("/etc/postfix-$this->myhostname/recipient_bcc",implode("\n",$bcc_maps));
			shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/recipient_bcc >/dev/null 2>&1");		
			return "recipient_bcc_maps = hash:/etc/postfix-$this->myhostname/recipient_bcc,pcre:/etc/postfix-$this->myhostname/copy.pcre";
		}
		
		return "recipient_bcc_maps = pcre:/etc/postfix-$this->myhostname/copy.pcre";
			
	}

	private function sender_bcc_maps(){
		if(is_file("/etc/postfix-$this->myhostname/sender_bcc")){@unlink("/etc/postfix-$this->myhostname/sender_bcc");}
		$ldap=new clladp();
		$filter="(&(objectClass=UserArticaClass)(SenderBccMaps=*))";
		$attrs=array("SenderBccMaps","mail");
		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		
		for($i=0;$i<$hash["count"];$i++){
			$mail=$hash[$i]["mail"][0];
			$senderbccmaps=$hash[$i]["senderbccmaps"][0];
			$sender_bcc_maps[]="$mail\t$senderbccmaps";
			
		}	
		
		if(is_array($sender_bcc_maps)){
				echo "Starting......: Postfix \"$this->myhostname\"". count($sender_bcc_maps)." sender(s) BCC\n"; 
				@file_put_contents("/etc/postfix-$this->myhostname/sender_bcc",implode("\n",$sender_bcc_maps));
				shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/sender_bcc >/dev/null 2>&1");		
				return "sender_bcc_maps = hash:/etc/postfix-$this->myhostname/sender_bcc";
		}		
	}
	
	
private function smtp_sasl_password_map(){
	$ldap=new clladp();
	echo "Starting......: Postfix \"$this->myhostname\" checks SASL Passwords maps\n"; 
	$filter="(&(objectClass=PostfixSmtpSaslPaswordMaps)(cn=*))";
	$attrs=array("cn","SmtpSaslPasswordString");
	$dn="cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=$hash[$i][strtolower("SmtpSaslPasswordString")][0];
		if($value==null){continue;}
		$smtp_sasl_password_maps[]="$mail\t$value";
	}

	$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
	$attrs=array("cn","SenderCanonicalRelayPassword");
	$dn="cn=sender_dependent_relayhost_maps,ou=$this->ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=$hash[$i][strtolower("SenderCanonicalRelayPassword")][0];
		if($value==null){continue;}
		$smtp_sasl_password_maps[]="$mail\t$value";
	}
	
	
		$auth=trim($this->GET("relayhost_authentication"));
		$relayhost=$this->GET("relayhost");
		if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$#",$relayhost)){$relayhost="[$relayhost]";}
		
		if($relayhost==null){@file_put_contents("/etc/postfix-$this->myhostname/sasl_service_relayhost","\n");}
		if(preg_match("#(.+?):(.+)#",$auth,$re)){
			echo "Starting......: Postfix \"$this->myhostname\" relayhost = $relayhost with autentication\n";
			@file_put_contents("/etc/postfix-$this->myhostname/sasl_service_relayhost","$relayhost\t$auth");
			$sssasltoadd[]="hash:/etc/postfix-$this->myhostname/sasl_service_relayhost";
		}
		
	$include=false;	
	if(is_array($smtp_sasl_password_maps)){
		@file_put_contents("/etc/postfix-$this->myhostname/sasl_relayhost",implode("\n",$smtp_sasl_password_maps));
		$include=true;
	}
	if(is_array($sssasltoadd)){$include=true;}
	

	if($include){
			echo "Starting......: Postfix \"$this->myhostname\" ". count($smtp_sasl_password_maps)." sender(s) relay(s) password(s)\n"; 
			if(is_array($sssasltoadd)){$adfiles=",".@implode(",",$sssasltoadd);}	
			$conf[]="smtp_sasl_password_maps = hash:/etc/postfix-$this->myhostname/sasl_passwd hash:/etc/postfix-$this->myhostname/sasl_relayhost$adfiles";
			$conf[]="smtp_sasl_auth_enable = yes";
			$conf[]="smtp_sasl_exceptions_networks = \$mynetworks";
			$conf[]="smtp_sasl_security_options = ";
			$conf[]="smtp_tls_mandatory_protocols=SSLv3,TLSv1";
	}else{
		echo "Starting......: Postfix \"$this->myhostname\" 0 sender(s) relay(s) password(s) and relayhost auth\n";
		@file_put_contents("/etc/postfix-$this->myhostname/sasl_relayhost"," ");
		@file_put_contents("/etc/postfix-$this->myhostname/sasl_passwd"," ");  
	}
	echo "Starting......: Postfix \"$this->myhostname\" compiling sasl_relayhost\n";
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/sasl_relayhost >/dev/null 2>&1");		
	echo "Starting......: Postfix \"$this->myhostname\" compiling sasl_passwd\n";
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/sasl_passwd >/dev/null 2>&1");	
	
	if(is_array($sssasltoadd)){
		reset($sssasltoadd);
		while (list ($key, $line) = each ($sssasltoadd) ){shell_exec("{$GLOBALS["postmap"]} $line >/dev/null 2>&1");}
	}
	
	if(is_array($conf)){return @implode("\n",$conf);}
}	

	
	public function getMailBoxTransport(){
		$users=new usersMenus();
		$sock=new sockets();
		$unixsocket=$users->cyrus_lmtp_path;
		if($unixsocket==null){$unixsocket="/var/spool/postfix/var/run/cyrus/socket/lmtp";}
		if($users->cyrus_imapd_installed){
		if(!$users->ZARAFA_INSTALLED){
				$CyrusEnableLMTPUnix=$sock->GET_INFO("CyrusEnableLMTPUnix");
				if($CyrusEnableLMTPUnix==null){$CyrusEnableLMTPUnix=1;}
				if($CyrusEnableLMTPUnix==1){
					$default="lmtp:unix:$unixsocket";
				}else{
					$CyrusLMTPListen=trim($sock->GET_INFO("CyrusLMTPListen"));
					if(preg_match("#(.+?):(.+)#",$CyrusLMTPListen,$re)){$ipaddr_listen=$re[1];$port=$re[2];}else{$ipaddr_listen="127.0.0.1";$port="2005";}
					$default="lmtp:$ipaddr_listen:$port";
				}
			}
	}
		if($users->ZARAFA_INSTALLED){$default="lmtp:127.0.0.1:2003";}	
		return $default;	
		
	}
	
	
	private function cyruslmtp(){
		$users=new usersMenus();
		if($users->ZARAFA_INSTALLED){return null;}
		if(!$users->cyrus_imapd_installed){return null;}
		$sock=new sockets();
		$CyrusEnableLMTPUnix=$sock->GET_INFO("CyrusEnableLMTPUnix");
		if($CyrusEnableLMTPUnix==null){$CyrusEnableLMTPUnix=1;}	
		if($CyrusEnableLMTPUnix==1){
			$conf[]="";
			$conf[]="#  LMP REMOTE ---------------------------------";
			$conf[]="lmtp_sasl_auth_enable = no";
			$conf[]="";
			return @implode("\n",$conf);
		}
		
		$ldap=new clladp();
		$CyrusLMTPListen=trim($sock->GET_INFO("CyrusLMTPListen"));
		if($CyrusLMTPListen==null){return;}
		$cyruspass=$ldap->CyrusPassword();
		@file_put_contents("/etc/postfix-$this->myhostname/lmtpauth","$CyrusLMTPListen\tcyrus:$cyruspass");
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/lmtpauth");
		$conf[]="";
		$conf[]="#  LMP REMOTE ---------------------------------";
		$conf[]="lmtp_sasl_auth_enable = yes";
		$conf[]="lmtp_sasl_password_maps = hash:/etc/postfix-$this->myhostname/lmtpauth";
		$conf[]="lmtp_sasl_mechanism_filter = plain, login";
		$conf[]="lmtp_sasl_security_options =";
		$conf[]="";
		return @implode("\n",$conf);
		}
		
	private function POSTSCREEN(){
	$EnablePostScreen=$this->GET("EnablePostScreen");
	if($EnablePostScreen<>1){echo "Starting......: PostScreen is not enabled\n";return null;}
	echo "Starting......: PostScreen configuring....\n";
	if(!is_file("/etc/postfix-$this->myhostname/postscreen_access.cidr")){@file_put_contents("/etc/postfix-$this->myhostname/postscreen_access.cidr","#");}
	if(!is_file("/etc/postfix-$this->myhostname/postscreen_access.hosts")){@file_put_contents("/etc/postfix-$this->myhostname/postscreen_access.hosts"," ");}
	
	
	
	
	$postscreen_bare_newline_action=$this->GET("postscreen_bare_newline_action");
	$postscreen_bare_newline_enable=$this->GET("postscreen_bare_newline_enable");
	
	$postscreen_bare_newline_ttl=$this->GET("postscreen_bare_newline_ttl");
	$postscreen_cache_cleanup_interval=$this->GET("postscreen_cache_cleanup_interval");
	$postscreen_cache_retention_time=$this->GET("postscreen_cache_retention_time");
	$postscreen_client_connection_count_limit=$this->GET("postscreen_client_connection_count_limit");
	$postscreen_pipelining_enable=$this->GET("postscreen_pipelining_enable");
	$postscreen_pipelining_action=$this->GET("postscreen_pipelining_action");
	$postscreen_pipelining_ttl=$this->GET("postscreen_pipelining_ttl");
	$postscreen_post_queue_limit=$this->GET("postscreen_post_queue_limit");
	$postscreen_pre_queue_limit=$this->GET("postscreen_pre_queue_limit");
	$postscreen_non_smtp_command_enable=$this->GET("postscreen_non_smtp_command_enable");
	$postscreen_non_smtp_command_action=$this->GET("postscreen_non_smtp_command_action");
	$postscreen_non_smtp_command_ttl=$this->GET("postscreen_non_smtp_command_ttl");
	$postscreen_forbidden_commands=$this->GET("postscreen_forbidden_command");
	$postscreen_dnsbl_action=$this->GET("postscreen_dnsbl_action");
	$postscreen_dnsbl_ttl=$this->GET("postscreen_dnsbl_ttl");
	$postscreen_dnsbl_threshold=$this->GET("postscreen_dnsbl_threshold");	
	
	
	if($postscreen_bare_newline_action==null){$postscreen_bare_newline_action="ignore";}
	if(!is_numeric($postscreen_bare_newline_enable)){$postscreen_bare_newline_enable="0";}
	if($postscreen_bare_newline_ttl==null){$postscreen_bare_newline_ttl="30d";}
	if($postscreen_cache_cleanup_interval==null){$postscreen_cache_cleanup_interval="12h";}
	if($postscreen_cache_retention_time==null){$postscreen_cache_retention_time="7d";}
	if($postscreen_client_connection_count_limit==null){$postscreen_client_connection_count_limit="50";}
	if($postscreen_pipelining_enable==null){$postscreen_pipelining_enable="0";}
	if($postscreen_pipelining_action==null){$postscreen_pipelining_action="ignore";}
	if($postscreen_pipelining_ttl==null){$postscreen_pipelining_ttl="30d";}			
	if($postscreen_post_queue_limit==null){$postscreen_post_queue_limit="100";}
	if($postscreen_pre_queue_limit==null){$postscreen_pre_queue_limit="100";}
	
	if($postscreen_non_smtp_command_enable==null){$postscreen_non_smtp_command_enable="0";}
	if($postscreen_non_smtp_command_action==null){$postscreen_non_smtp_command_action="drop";}
	if($postscreen_non_smtp_command_ttl==null){$postscreen_non_smtp_command_ttl="30d";}
	if($postscreen_forbidden_commands==null){$postscreen_forbidden_commands="CONNECT, GET, POST";}
	if($postscreen_dnsbl_action==null){$postscreen_dnsbl_action="ignore";}
	if($postscreen_dnsbl_action==null){$postscreen_dnsbl_action="ignore";}
	if($postscreen_dnsbl_ttl==null){$postscreen_dnsbl_ttl="1h";}
	if($postscreen_dnsbl_threshold==null){$postscreen_dnsbl_threshold="1";}
	
	if($postscreen_bare_newline_enable==1){$postscreen_bare_newline_enable="yes";}else{$postscreen_bare_newline_enable="no";}
	if($postscreen_pipelining_enable==1){$postscreen_pipelining_enable="yes";}else{$postscreen_pipelining_enable="no";}
	if($postscreen_non_smtp_command_enable==1){$postscreen_non_smtp_command_enable="yes";}else{$postscreen_non_smtp_command_enable="no";}
	
	
	$conf[]="postscreen_bare_newline_action= $postscreen_bare_newline_action";
	$conf[]="postscreen_bare_newline_enable= $postscreen_bare_newline_enable";
	$conf[]="postscreen_bare_newline_ttl= $postscreen_bare_newline_ttl";
	$conf[]="postscreen_cache_cleanup_interval= $postscreen_cache_cleanup_interval";
	$conf[]="postscreen_cache_retention_time= $postscreen_cache_retention_time";
	$conf[]="postscreen_client_connection_count_limit= $postscreen_client_connection_count_limit";
	$conf[]="postscreen_client_connection_count_limit= $postscreen_client_connection_count_limit";
	$conf[]="postscreen_pipelining_enable= $postscreen_pipelining_enable";
	$conf[]="postscreen_pipelining_action= $postscreen_pipelining_action";
	$conf[]="postscreen_pipelining_ttl= $postscreen_pipelining_ttl";
	$conf[]="postscreen_post_queue_limit= $postscreen_post_queue_limit";
	$conf[]="postscreen_pre_queue_limit= $postscreen_pre_queue_limit";
	$conf[]="postscreen_non_smtp_command_enable= $postscreen_non_smtp_command_enable";
	$conf[]="postscreen_non_smtp_command_action= $postscreen_non_smtp_command_action";
	$conf[]="postscreen_non_smtp_command_ttl= $postscreen_non_smtp_command_ttl";
	$conf[]="postscreen_forbidden_command= $postscreen_forbidden_commands";
	$conf[]="postscreen_dnsbl_action= $postscreen_dnsbl_action";
	$conf[]="postscreen_dnsbl_ttl= $postscreen_dnsbl_ttl";
	$conf[]="postscreen_dnsbl_threshold= $postscreen_dnsbl_threshold";
	$conf[]="postscreen_cache_map= btree:\$data_directory/postscreen_{$this->myhostname}_cache";
	
	
	
	$dnsbl_array=unserialize(base64_decode($this->GET_BIGDATA("postscreen_dnsbl_sites")));
	if(is_array($dnsbl_array)){
		while (list ($site, $threshold) = each ($dnsbl_array) ){if($site==null){continue;}$dnsbl_array_compiled[]="$site*$threshold";}
	}
		
	$final_dnsbl=null;
	if(is_array($dnsbl_array_compiled)){$final_dnsbl=@implode(",",$dnsbl_array_compiled);}
	$conf[]="postscreen_dnsbl_sites=$final_dnsbl";
	
	$sock=new sockets();
	
	
		$q=new mysql();
		$sql="SELECT * FROM postfix_whitelist_con";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "$q->mysql_error\n";}
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
			if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne["hostname"])){
				$hostsname[]="{$ligne["hostname"]}\tOK";
			}
			if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne["ipaddr"])){
				$nets[]="{$ligne["ipaddr"]}\tdunno";
			}
			
		}	
	
	$ldap=new clladp();
	$networks=$ldap->load_mynetworks();	
	if(is_array($networks)){
		while (list ($num, $ligne) = each ($networks) ){
			if($ligne==null){continue;}
			if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne)){
				$hostsname[]="$ligne\tOK";
			}else{
				$nets[]="$ligne\tdunno";
			}
		}
	}
	
	$networks=unserialize($this->GET_BIGDATA("mynetworks"));
		if(is_array($networks)){
		while (list ($num, $ligne) = each ($networks) ){
			if($ligne==null){continue;}
			if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne)){
				$hostsname[]="$ligne\tOK";
			}else{
				$nets[]="$ligne\tdunno";
			}
		}
	}	
	
	
	if(is_array($hostsname)){@file_put_contents("/etc/postfix-$this->myhostname/postscreen_access.hosts",@implode("\n",$hostsname));
	$postscreen_access=",hash:/etc/postfix-$this->myhostname/postscreen_access.hosts";}
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/postscreen_access.hosts");
	
	
	
	
	if(is_array($nets)){@file_put_contents("/etc/postfix-$this->myhostname/postscreen_access.cidr",@implode("\n",$nets));}
	$conf[]="postscreen_access_list=permit_mynetworks,cidr:/etc/postfix-$this->myhostname/postscreen_access.cidr$postscreen_access";
	return @implode("\n",$conf);	
		
	}
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY(){
		if(strlen($GLOBALS[__FUNCTION__])){return $GLOBALS[__FUNCTION__];}
		$locate1=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY_1();
		$locate2=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY_2();
		$locate3=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY_3();
		if($locate2<>null){if($locate1<>$locate2){$locate1=$locate2;}}
		if($locate3<>null){if($locate1<>$locate3){$locate1=$locate3;}}
		if($locate1==null){"Starting......: Postfix WARNING unable to find daemon directory\n";}
		$GLOBALS[__FUNCTION__]=$locate1;
		return $locate1; 
	}
	
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY_1(){
		if(is_file('/usr/lib/postfix/master')){return ('/usr/lib/postfix');}
		if(is_file('/usr/libexec/postfix/master')){return ('/usr/libexec/postfix');}
		if(is_file('/usr/local/libexec/postfix/master')){return ('/usr/local/libexec/postfix');}
		}
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY_2(){
		if(is_file('/usr/lib/postfix/postscreen')){return ('/usr/lib/postfix');}
		if(is_file('/usr/libexec/postfix/postscreen')){return ('/usr/libexec/postfix');}
		if(is_file('/usr/local/libexec/postfix/postscreen')){return ('/usr/local/libexec/postfix');}
		
	}	
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY_3(){
		if(is_file('/usr/lib/postfix/postmulti-script')){return ('/usr/lib/postfix');}
		if(is_file('/usr/libexec/postfix/postmulti-script')){return ('/usr/libexec/postfix');}
		if(is_file('/usr/local/libexec/postfix/postmulti-script')){return ('/usr/local/libexec/postfix');}
	}	
	
	
	private function smtp_sasl_security_options(){
		$datas=unserialize($this->GET_BIGDATA("smtp_sasl_security_options"));
		if($datas["noanonymous"]==1){$f[]="noanonymous";}
		if($datas["noplaintext"]==1){$f[]="noplaintext";}
		if($datas["nodictionary"]==1){$f[]="nodictionary";}
		if($datas["mutual_auth"]==1){$f[]="mutual_auth";}
		if(count($f)==0){$f[]="noanonymous";}
		return "smtp_sasl_security_options = ".@implode(", ",$f)."\nsmtp_sasl_tls_security_options = ".@implode(", ",$f)."\n";	
	}

	
	private function notifications_templates(){
		include_once(dirname(__FILE__)."/class.main_cf.inc");
		$mainTPL=new bounces_templates();
		$sock=new sockets();
		$PostfixPostmaster=$sock->GET_INFO("PostfixPostmaster");
		if(trim($PostfixPostmaster)==null){$PostfixPostmaster="postmaster";}	
		$conf=null;
		
		$double_bounce_sender=$this->GET("double_bounce_sender");
		$address_verify_sender=$this->GET("address_verify_sender");
		$twobounce_notice_recipient=$this->GET("2bounce_notice_recipient");
		$error_notice_recipient=$this->GET("error_notice_recipient");
		$delay_notice_recipient=$this->GET("delay_notice_recipient");
		$empty_address_recipient=$this->GET("empty_address_recipient");
		
	
		
		if($double_bounce_sender==null){$double_bounce_sender="double-bounce";};
		if($address_verify_sender==null){$address_verify_sender="\$double_bounce_sender";}
		if($twobounce_notice_recipient==null){$twobounce_notice_recipient="postmaster";}
		if($error_notice_recipient==null){$error_notice_recipient=$PostfixPostmaster;}
		if($delay_notice_recipient==null){$delay_notice_recipient=$PostfixPostmaster;}
		if($empty_address_recipient==null){$empty_address_recipient=$PostfixPostmaster;}	
		if(is_array($mainTPL->templates_array)){
		while (list ($template, $nothing) = each ($mainTPL->templates_array) ){
			$array=unserialize(base64_decode($this->GET_BIGDATA($template)));
			if(!is_array($array)){$array=$mainTPL->templates_array[$template];}
				$tp=explode("\n",$array["Body"]);
				$Body=null;
				while (list ($a, $line) = each ($tp) ){if(trim($line)==null){continue;}$Body=$Body.$line."\n";}
				$conf=$conf ."\n$template = <<EOF\n";
				$conf=$conf ."Charset: {$array["Charset"]}\n";
				$conf=$conf ."From:  {$array["From"]}\n";
				$conf=$conf ."Subject: {$array["Subject"]}\n";
				$conf=$conf ."\n";
				$conf=$conf ."$Body";
				$conf=$conf ."\n\n";
				$conf=$conf ."EOF\n";
				
			}
			@file_put_contents("/etc/postfix-$this->myhostname/bounce.template.cf",$conf);	
			$f[]="bounce_template_file = /etc/postfix-$this->myhostname/bounce.template.cf";
		}	

		$f[]="double_bounce_sender = $double_bounce_sender";
		$f[]="address_verify_sender = $address_verify_sender";
		$f[]="2bounce_notice_recipient = $twobounce_notice_recipient";
		$f[]="error_notice_recipient = $error_notice_recipient";	
		$f[]="delay_notice_recipient = $delay_notice_recipient";
		$f[]="empty_address_recipient = $empty_address_recipient";		
		return @implode("\n",$f);
	}

	

		
	
	public function buildconf(){
		$EnableSasl=$this->GET("EnableSasl");
		$ssl_path="/etc/postfix-$this->myhostname/ssl";
		$message_size_limit=$this->GET("message_size_limit");
		$default_destination_recipient_limit=$this->GET("default_destination_recipient_limit");
		$smtpd_recipient_limit=$this->GET("smtpd_recipient_limit");
		$mime_nesting_limit=$this->GET("mime_nesting_limit");
		$header_address_token_limit=$this->GET("header_address_token_limit");
		$virtual_mailbox_limit=$this->GET("virtual_mailbox_limit");
		
		$message_size_limit=$this->GET($message_size_limit/1024)/1000;
		$virtual_mailbox_limit=$this->GET($virtual_mailbox_limit/1024)/1000;	
		
		if($message_size_limit==null){$message_size_limit=102400000;}
		if($virtual_mailbox_limit==null){$virtual_mailbox_limit=102400000;}
		if($default_destination_recipient_limit==null){$default_destination_recipient_limit=50;}
		if($smtpd_recipient_limit==null){$smtpd_recipient_limit=1000;}
		if($mime_nesting_limit==null){$mime_nesting_limit=100;}
		if($header_address_token_limit==null){$header_address_token_limit=10240;}
			
			$f[]="$ssl_path/ca.csr";
			$f[]="$ssl_path/ca.crt";
			$f[]="$ssl_path/ca.key";
			
			while (list ($index, $file) = each ($f) ){
				if(!is_file($file)){
					$this->certificate_generate();
					break;
				}
			}
		
			$myhostname=$this->myhostname;
			echo "Starting......: Postfix \"$myhostname\" is a member of $this->ou IP $this->ip_addr\n";
			$users=new usersMenus();		
			
			$mailbox_transport=$this->getMailBoxTransport();
			$nets=unserialize($this->GET_BIGDATA("mynetworks"));
			if(is_array($nets)){
				while (list ($d, $z) = each ($nets) ){if(trim($z)==null){continue;}$newnets[$z]=$z;}
				$nets=array();
				while (list ($d, $z) = each ($newnets) ){$nets[]=$z;}
			}
			
			
			if(is_array($nets)){$mynetworks=",".@implode(",",$nets);}	
			$mynetworks=str_replace(",,", ",", $mynetworks);
			$myhostname_maincf=$myhostname;
			
			$VirtualHostnameToChange=trim($this->GET("VirtualHostNameToChange"));
			if($VirtualHostnameToChange<>null){
				$myhostname_maincf=$VirtualHostnameToChange;
				echo "Starting......: Postfix $myhostname is now virtualy called $VirtualHostnameToChange\n";
			}
			
			
			$conf[]="smtpd_banner=\$myhostname ESMTP \$mail_name";
			$conf[]="biff=no";
			$conf[]="";
			$conf[]="#  NETWORK ---------------------------------";			
			$conf[]="myhostname = $myhostname_maincf";
			if($this->ip_addr==null){echo "Starting......: Postfix $myhostname warning inet_interfaces is null !\n";}
			
			$conf[]="inet_interfaces = $this->ip_addr";
			$conf[]="mynetworks=127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128,$this->ip_addr,{$mynetworks}";
			$conf[]="myorigin=\$mydomain";
			$conf[]="";
			$conf[]="append_dot_mydomain=no";
			$conf[]="readme_directory=no";
			
			
			$conf[]="recipient_delimiter=+";
			
			$conf[]="";
			$conf[]="#  RESTRICTIONS ---------------------------------";					
			$conf[]="message_size_limit = $message_size_limit";
			$conf[]="mime_nesting_limit = $mime_nesting_limit";
			$conf[]="header_address_token_limit = $header_address_token_limit";	
			$conf[]="default_destination_recipient_limit = $default_destination_recipient_limit";
			$conf[]="smtpd_recipient_limit = $smtpd_recipient_limit";
			$conf[]="smtpd_delay_reject = yes";			
			$conf[]="mailbox_size_limit=$virtual_mailbox_limit";
			$conf[]="virtual_mailbox_limit = $virtual_mailbox_limit";
			$conf[]="artica-filter_destination_recipient_limit = 1";
			$conf[]="artica_destination_recipient_limit = 1";
			$conf[]="zarafa_destination_recipient_limit = 1";
			$conf[]="smtpd_restriction_classes = artica_restrict_relay_domains";
			$conf[]="artica_restrict_relay_domains = reject_unverified_recipient";
			
			
			
			echo "Starting......: Postfix \"$this->myhostname\" checking restrictions\n";
			$conf[]=$this->smtpd_sender_restrictions();
			echo "Starting......: Postfix \"$this->myhostname\" checking smtpd_client_restrictions\n";
			$conf[]=$this->smtpd_client_restrictions();
			echo "Starting......: Postfix \"$this->myhostname\" checking smtpd_end_of_data_restrictions\n";
			$conf[]=$this->smtpd_end_of_data_restrictions();
			echo "Starting......: Postfix \"$this->myhostname\" checking body_checks\n";
			$conf[]=$this->body_checks();
			echo "Starting......: Postfix \"$this->myhostname\" checking restrictions done.\n";
			$conf[]="";
			$conf[]="bounce_service_name=bounce";
			$conf[]="bounce_size_limit=50000";
			$conf[]="bounce_notice_recipient=postmaster";
			$conf[]="double_bounce_sender=double-bounce";
			
			$smtp_connection_cache_on_demand=$this->GET("smtp_connection_cache_on_demand");
			$smtp_connection_cache_time_limit=$this->GET("smtp_connection_cache_time_limit");
			$smtp_connection_reuse_time_limit=$this->GET("smtp_connection_reuse_time_limit");
			$connection_cache_ttl_limit=$this->GET("connection_cache_ttl_limit");
			$connection_cache_status_update_time=$this->GET("connection_cache_status_update_time");
			$smtp_connection_cache_destinations=unserialize(base64_decode($this->GET_BIGDATA("smtp_connection_cache_destinations")));	
			
			$address_verify_map=$this->GET("address_verify_map");
			$address_verify_negative_cache=$this->GET("address_verify_negative_cache");
			$address_verify_poll_count=$this->GET("address_verify_poll_count");
			$address_verify_poll_delay=$this->GET("address_verify_poll_delay");
			$address_verify_sender=$this->GET("address_verify_sender");
			$address_verify_negative_expire_time=$this->GET("address_verify_negative_expire_time");
			$address_verify_negative_refresh_time=$this->GET("address_verify_negative_refresh_time");
			$address_verify_positive_expire_time=$this->GET("address_verify_positive_expire_time");
			$address_verify_positive_refresh_time=$this->GET("address_verify_positive_refresh_time");
			if($address_verify_map==null){$address_verify_map="btree:/var/lib/postfix-$this->myhostname/verify";}	

			$smtpd_error_sleep_time=$this->GET("smtpd_error_sleep_time");
			$smtpd_soft_error_limit=$this->GET("smtpd_soft_error_limit");
			$smtpd_hard_error_limit=$this->GET("smtpd_hard_error_limit");
			$smtpd_client_connection_count_limit=$this->GET("smtpd_client_connection_count_limit");
			$smtpd_client_connection_rate_limit=$this->GET("smtpd_client_connection_rate_limit");
			$smtpd_client_message_rate_limit=$this->GET("smtpd_client_message_rate_limit");
			$smtpd_client_recipient_rate_limit=$this->GET("smtpd_client_recipient_rate_limit");
			$smtpd_client_new_tls_session_rate_limit=$this->GET("smtpd_client_new_tls_session_rate_limit");
			$smtpd_client_event_limit_exceptions=$this->GET("smtpd_client_event_limit_exceptions");
			$in_flow_delay=$this->GET("in_flow_delay");
			$smtp_connect_timeout=$this->GET("smtp_connect_timeout");
			$smtp_helo_timeout=$this->GET("smtp_helo_timeout");
			$initial_destination_concurrency=$this->GET("initial_destination_concurrency");
			$default_destination_concurrency_limit=$this->GET("default_destination_concurrency_limit");
			$local_destination_concurrency_limit=$this->GET("local_destination_concurrency_limit");
			$smtp_destination_concurrency_limit=$this->GET("smtp_destination_concurrency_limit");
			$default_destination_recipient_limit=$this->GET("default_destination_recipient_limit");
			$smtpd_recipient_limit=$this->GET("smtpd_recipient_limit");
			$queue_run_delay=$this->GET("queue_run_delay");  
			$minimal_backoff_time =$this->GET("minimal_backoff_time");
			$maximal_backoff_time =$this->GET("maximal_backoff_time");
			$maximal_queue_lifetime=$this->GET("maximal_queue_lifetime"); 
			$bounce_queue_lifetime =$this->GET("bounce_queue_lifetime");
			$qmgr_message_recipient_limit =$this->GET("qmgr_message_recipient_limit");
			$default_process_limit=$this->GET("default_process_limit");	
			$qmgr_message_active_limit=$this->GET("qmgr_message_active_limit");
			$qmgr_message_recipient_minimum=$this->GET("qmgr_message_recipient_minimum");		
			$smtp_fallback_relay=$this->GET("smtp_fallback_relay");
	
			
			if(!is_numeric($qmgr_message_active_limit)){$qmgr_message_active_limit=20000;}
			if(!is_numeric($qmgr_message_recipient_limit)){$qmgr_message_recipient_limit=20000;}
			if(!is_numeric($qmgr_message_recipient_minimum)){$qmgr_message_recipient_minimum=10;}				
			if(!is_numeric($smtp_connection_cache_on_demand)){$smtp_connection_cache_on_demand=1;}
			if($smtp_connection_cache_time_limit==null){$smtp_connection_cache_time_limit="2s";}
			if($smtp_connection_reuse_time_limit==null){$smtp_connection_reuse_time_limit="300s";}
			if($connection_cache_ttl_limit==null){$connection_cache_ttl_limit="2s";}
			if($connection_cache_status_update_time==null){$connection_cache_status_update_time="600s";}	
			if($smtp_connection_cache_on_demand==1){$smtp_connection_cache_on_demand="yes";}else{$smtp_connection_cache_on_demand="no";}	
			
			if(!is_numeric($address_verify_negative_cache)){$address_verify_negative_cache=1;}
			if(!is_numeric($address_verify_poll_count)){$address_verify_poll_count=3;}
			if($address_verify_poll_delay==null){$address_verify_poll_delay="3s";}
			if($address_verify_sender==null){$address_verify_sender="double-bounce";}
			if($address_verify_negative_expire_time==null){$address_verify_negative_expire_time="3d";}
			if($address_verify_negative_refresh_time==null){$address_verify_negative_refresh_time="3h";}
			if($address_verify_positive_expire_time==null){$address_verify_positive_expire_time="31d";}
			if($address_verify_positive_refresh_time==null){$address_verify_positive_refresh_time="7d";}
			if($address_verify_negative_cache==1){$address_verify_negative_cache="yes";}else{$address_verify_negative_cache="no";}	

			if($smtpd_error_sleep_time==null){$smtpd_error_sleep_time="1s";}
			if(!is_numeric($smtpd_soft_error_limit)){$smtpd_soft_error_limit=10;}
			if(!is_numeric($smtpd_hard_error_limit)){$smtpd_hard_error_limit=20;}
			if(!is_numeric($smtpd_client_connection_count_limit)){$smtpd_client_connection_count_limit=50;}
			if(!is_numeric($smtpd_client_connection_rate_limit)){$smtpd_client_connection_rate_limit=0;}
			if(!is_numeric($smtpd_client_message_rate_limit)){$smtpd_client_message_rate_limit=0;}
			if(!is_numeric($smtpd_client_recipient_rate_limit)){$smtpd_client_recipient_rate_limit=0;}
			if(!is_numeric($smtpd_client_new_tls_session_rate_limit)){$smtpd_client_new_tls_session_rate_limit=0;}
			if(!is_numeric($initial_destination_concurrency)){$initial_destination_concurrency=5;}
			if(!is_numeric($default_destination_concurrency_limit)){$default_destination_concurrency_limit=20;}
			if(!is_numeric($smtp_destination_concurrency_limit)){$smtp_destination_concurrency_limit=20;}
			if(!is_numeric($local_destination_concurrency_limit)){$local_destination_concurrency_limit=2;}
			if(!is_numeric($default_destination_recipient_limit)){$default_destination_recipient_limit=50;}
			if(!is_numeric($smtpd_recipient_limit)){$smtpd_recipient_limit=1000;}
			if(!is_numeric($default_process_limit)){$default_process_limit=100;}
			if(!is_numeric($qmgr_message_recipient_limit)){$qmgr_message_recipient_limit=20000;}
			if($smtpd_client_event_limit_exceptions==null){$smtpd_client_event_limit_exceptions="\$mynetworks";}
			if($in_flow_delay==null){$in_flow_delay="1s";}
			if($smtp_connect_timeout==null){$smtp_connect_timeout="30s";}
			if($smtp_helo_timeout==null){$smtp_helo_timeout="300s";}
			if($bounce_queue_lifetime==null){$bounce_queue_lifetime="5d";}
			if($maximal_queue_lifetime==null){$maximal_queue_lifetime="5d";}
			if($maximal_backoff_time==null){$maximal_backoff_time="4000s";}
			if($minimal_backoff_time==null){$minimal_backoff_time="300s";}
			if($queue_run_delay==null){$queue_run_delay="300s";}			

			if(is_array($smtp_connection_cache_destinations)){
				if(count($smtp_connection_cache_destinations)>0){
					while (list ($host, $none) = each ($smtp_connection_cache_destinations) ){$smtp_connection_cache_destinationsR[]=$host;}
					$smtp_connection_cache_destinationsF=@implode(",", $smtp_connection_cache_destinationsR);
				}
			}			
			
			

			$conf[]="smtpd_reject_unlisted_recipient=yes";
			$conf[]="smtp_connection_cache_on_demand=$smtp_connection_cache_on_demand";
			$conf[]="smtp_connection_cache_time_limit=$smtp_connection_cache_time_limit";
			$conf[]="smtp_connection_reuse_time_limit=$smtp_connection_reuse_time_limit";
			$conf[]="connection_cache_ttl_limit=$connection_cache_ttl_limit";
			$conf[]="connection_cache_status_update_time=$connection_cache_status_update_time";
			$conf[]="smtp_connection_cache_destinations=$smtp_connection_cache_destinationsF";
			$conf[]="address_verify_map=$address_verify_map";
			$conf[]="address_verify_sender=$address_verify_sender";
			$conf[]="address_verify_negative_cache=$address_verify_negative_cache";
			$conf[]="address_verify_negative_expire_time=$address_verify_negative_expire_time";
			$conf[]="address_verify_negative_refresh_time=$address_verify_negative_refresh_time";
			$conf[]="address_verify_poll_count=$address_verify_poll_count";
			$conf[]="address_verify_poll_delay=$address_verify_poll_delay";
			
			$conf[]="address_verify_positive_expire_time=$address_verify_positive_expire_time";
			$conf[]="address_verify_positive_refresh_time=$address_verify_positive_refresh_time";
			
			

			$conf[]="smtpd_error_sleep_time=$smtpd_error_sleep_time";
			$conf[]="smtpd_hard_error_limit=$smtpd_hard_error_limit";
			$conf[]="smtpd_soft_error_limit=$smtpd_soft_error_limit";
			$conf[]="smtpd_client_connection_count_limit=$smtpd_client_connection_count_limit";
			$conf[]="smtpd_client_connection_rate_limit=$smtpd_client_connection_rate_limit";
			$conf[]="smtpd_client_message_rate_limit=$smtpd_client_message_rate_limit";
			$conf[]="smtpd_client_recipient_rate_limit=$smtpd_client_recipient_rate_limit";
			$conf[]="smtpd_client_event_limit_exceptions=$smtpd_client_event_limit_exceptions";
			
			
			
			
			$in_flow_delay=$this->GET("in_flow_delay");
			$freeze_delivery_queue=$this->GET("freeze_delivery_queue");
			if($freeze_delivery_queue==1){
				echo "Starting......: Postfix delivery queue is frozen..\n";
				$in_flow_delay="0";
				$conf[]="master_service_disable=qmgr.fifo";
			}	
			

			if($in_flow_delay<>null){$conf[]="in_flow_delay=$in_flow_delay";}
			if($minimal_backoff_time<>null){$conf[]="minimal_backoff_time=$minimal_backoff_time";}
			if($maximal_backoff_time<>null){$conf[]="maximal_backoff_time=$maximal_backoff_time";}
			if($bounce_queue_lifetime<>null){$conf[]="bounce_queue_lifetime=$bounce_queue_lifetime";}
			if($default_process_limit<>null){$conf[]="default_process_limit=$default_process_limit";}
			if($maximal_queue_lifetime<>null){$conf[]="maximal_queue_lifetime=$maximal_queue_lifetime";}
			if($queue_run_delay==null){$queue_run_delay="5m";}
			
			$conf[]="smtp_helo_timeout=$smtp_helo_timeout";
			$conf[]="smtp_connect_timeout=$smtp_connect_timeout";
			if($smtp_fallback_relay<>null){
				$conf[]="smtp_fallback_relay=$smtp_fallback_relay";	
			}
			$conf[]="queue_run_delay=$queue_run_delay";
			$conf[]="qmgr_message_active_limit=$qmgr_message_active_limit";
			$conf[]="qmgr_message_recipient_limit=$qmgr_message_recipient_limit";
			$conf[]="qmgr_message_recipient_minimum=$qmgr_message_recipient_minimum";
			
			
			$conf[]="smtpd_timeout=300";
			$conf[]="enable_original_recipient=yes";
			$conf[]="ignore_mx_lookup_error=no";
			$conf[]="disable_dns_lookups=no";

			$conf[]="smtp_sender_dependent_authentication = yes";
			$conf[]="undisclosed_recipients_header=To: undisclosed-recipients:;";
			$conf[]="initial_destination_concurrency=$initial_destination_concurrency";
			$conf[]="default_destination_concurrency_limit=$default_destination_concurrency_limit";
			$conf[]="local_destination_concurrency_limit=$local_destination_concurrency_limit";
			$conf[]="smtp_destination_concurrency_limit=$smtp_destination_concurrency_limit";
			$conf[]="";
			$conf[]="# Templates and notifications ---------------------------------";
			$conf[]=$this->notifications_templates();
			$conf[]="";
			$conf[]="smtp_send_xforward_command=yes";
			$conf[]="";
			
			$conf[]="#  SASL / TLS ---------------------------------";
			$conf[]="broken_sasl_auth_clients = yes";			
			if($EnableSasl==1){	
				$conf[]="smtpd_sasl_auth_enable = yes";
				$conf[]=$this->smtp_sasl_security_options();
				$conf[]="smtpd_sasl_authenticated_header = yes";
				$conf[]="smtpd_sasl_path=smtpd";
				$conf[]="smtpd_sasl_local_domain = ";
				$conf[]="smtpd_sasl_exceptions_networks = \$mynetworks";
			}

			$conf[]=$this->cyruslmtp();
			
			$conf[]="smtpd_tls_auth_only = no";
			$conf[]="smtpd_tls_ask_ccert=no";
			$conf[]="smtpd_tls_security_level = may";
			$conf[]="smtpd_tls_req_ccert=no";
			$conf[]="smtpd_tls_received_header = yes";
			$conf[]="smtpd_tls_CAfile=$ssl_path/ca.csr";
			$conf[]="smtpd_tls_cert_file=$ssl_path/ca.crt";
			$conf[]="smtpd_tls_key_file=$ssl_path/ca.key";
			$conf[]="smtpd_use_tls=yes";
			$conf[]="smtpd_tls_session_cache_database=btree:\${data_directory}/smtpd_scache";
			$conf[]="smtp_tls_session_cache_database=btree:\${data_directory}/smtpd_tls_session_cache";						
			$conf[]="smtpd_client_new_tls_session_rate_limit=$smtpd_client_new_tls_session_rate_limit";
			$conf[]="";
			$conf[]="#  SMTP CLIENT SASL ---------------------------------";
			$conf[]=$this->smtp_sasl_password_map();
			
			
			$conf[]="virtual_uid_maps=static:5000";
			$conf[]="virtual_gid_maps=static:5000";
			$conf[]="bounce_template_file=/etc/postfix/bounce.template.cf";
			$conf[]="";
			$conf[]="#  PostScreen ---------------------------------";
			$conf[]=$this->POSTSCREEN();
			$conf[]="";
			
			$conf[]="";
			$conf[]="#  DATABASES ---------------------------------";
			$conf[]="smtpd_reject_unlisted_recipient=yes";
			$conf[]="artica_destination_recipient_limit = 1";
			$conf[]="artica-filter_destination_recipient_limit = 1";
			$this->LoadLDAPDBs();
			$this->recipient_bcc_domain_maps();
			$db[]=$this->relais_domains();
			$db[]=$this->alias_maps();
			$db[]=$this->transport_maps();
			$db[]=$this->mydestination();
			$db[]=$this->recipient_canonical_maps();
			$db[]=$this->sender_canonical_maps();
			$db[]=$this->sender_dependent_relayhost_maps();
			$db[]=$this->sender_bcc_maps();
			echo "Starting......: Postfix \"$this->myhostname\" check recipient bcc\n";
			$db[]=$this->recipient_bcc_maps();	
			
			while (list ($num, $ligne) = each ($db) ){
				if($ligne==null){continue;}
				$conf[]=$ligne;
			}
			
			if(trim($mailbox_transport)<>null){
				$conf[]="";
				$conf[]="#  MAILBOXES ---------------------------------";
				$conf[]="mailbox_transport = $mailbox_transport";
				$conf[]="virtual_transport=\$mailbox_transport";
			}
			$conf[]="";
					
			$conf[]="disable_vrfy_command=yes";
			$conf[]="smtpd_delay_reject=yes";
			$conf[]="smtpd_helo_required=yes";
			
			
			$relayhost=$this->GET("relayhost");
			
			echo "Starting......: Postfix \"$this->myhostname\" relayhost ?? ($relayhost)\n";
			
			if($relayhost<>null){
				if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$#",$relayhost)){
					$relayhost="[$relayhost]";
				}
				$conf[]="#  RELAYHOST ---------------------------------";
				$conf[]="relayhost = $relayhost";
				$conf[]="";
			}
			
			$conf[]="";	
			$conf[]="#Checks headers and bodys ---------------------------------";
			$conf[]="";	
			if(is_file("/etc/postfix-$this->myhostname/mime_header_checks")){
				$conf[]="mime_header_checks = regexp:/etc/postfix-$this->myhostname/mime_header_checks";
			}
			$conf[]=$this->header_checks();
			//$conf[]="content_filter = artica-filter:";
			$conf[]="receive_override_options=";
			$conf[]="auth_relay = ";
			$conf[]="";
			$conf[]=$this->milters();		
			$conf[]="";
			$conf[]="";	
			$conf[]="#Multi instances parameters ---------------------------------";
			$conf[]="";	
			$conf[]="queue_directory = /var/spool/postfix-$this->myhostname";	
			$conf[]="data_directory = /var/lib/postfix-$this->myhostname";
			
			$daemon_directory=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY();
			echo "Starting......: Postfix daemon directory \"$daemon_directory\"\n";
			$conf[]="daemon_directory = ". $this->LOCATE_POSTFIX_DAEMON_DIRECTORY();
			
			$conf[]="multi_instance_name = postfix-$this->myhostname";
			$conf[]="multi_instance_wrapper=\${command_directory}/postmulti -p --";
			$conf[]="multi_instance_enable = yes";
			$conf[]="";		
			
			$this->CheckDirectories($this->myhostname);
			if(!is_dir("/etc/postfix-$this->myhostname")){
				echo "Starting......: Postfix creating dir /etc/postfix-$this->myhostname\n";
				@mkdir("/etc/postfix-$this->myhostname",null,true);
			}
			
			
			
			@file_put_contents("/etc/postfix-$this->myhostname/main.cf",@implode("\n",$conf));
			echo "Starting......: Postfix \"$this->myhostname\" main.cf done\n";
			$this->buildmaster();
			$this->PostfixMainCfDefaultInstance();
		
	}
	private function LoadLDAPDBs(){
		$databases_list=unserialize(base64_decode($this->GET_BIGDATA("ActiveDirectoryDBS")));	
		if(is_array($databases_list)){
			while (list ($dbindex, $array) = each ($databases_list) ){
				if($array["enabled"]<>1){continue;}
				$targeted_file=$this->buidLdapDB($this->myhostname,$dbindex,$array);
				if(!is_file($targeted_file)){continue;}
				if($array["resolv_domains"]==1){$this->buidLdapDBDomains($array);}
				$GLOBALS["LDAPDBS"][$array["database_type"]][]="ldap:$targeted_file";
			}	
		}
	}	
	
	private function milters($onlymilter_config=false){
		$users=new usersMenus();
		$sock=new sockets();
		$KasxFilterEnabled=$sock->GET_INFO("KasxFilterEnabled");
		$kavmilterEnable=$sock->GET_INFO("kavmilterEnable");		
		$array_filters=unserialize(base64_decode($this->GET_BIGDATA("PluginsEnabled")));
		$EnableDKFilter=$sock->GET_INFO("EnableDKFilter");
		$EnableDkimMilter=$sock->GET_INFO("EnableDkimMilter");
		
		
		if($users->OPENDKIM_INSTALLED){
			if($EnableDKFilter=1){
				if($array_filters["APP_OPENDKIM"]==1){
					echo "Starting......: Postfix \"$this->myhostname\" OpenDKIM mail filter Enabled\n";
					$smtpd_milters[]="unix:/var/run/opendkim/opendkim.sock";
					$nosmtpd_milters[]="unix:/var/run/opendkim/opendkim.sock";
				}
				
			}
		}
		
		
		if($users->MILTER_DKIM_INSTALLED){
			if($EnableDkimMilter=1){
				if($array_filters["APP_MILTER_DKIM"]==1){
					echo "Starting......: Postfix \"$this->myhostname\" Milter-DKIM mail filter Enabled\n";
					$smtpd_milters[]="unix:/var/run/dkim-milter/dkim-milter.sock";
					$nosmtpd_milters[]="unix:/var/run/dkim-milter/dkim-milter.sock";
				}
				
			}
		}		
		
		
		if($users->KAV_MILTER_INSTALLED){
			if($KasxFilterEnabled==1){
				if($array_filters["APP_KAS3"]==1){
					echo "Starting......: Postfix \"$this->myhostname\" Kaspersky AntiSpam Enabled\n";
					$smtpd_milters[]="unix:/var/run/kas-milter.socket";
				}
			}
		}
		
		
		if($users->KAV_MILTER_INSTALLED){
			if($kavmilterEnable==1){
				if($array_filters["APP_KAVMILTER"]==1){
					echo "Starting......: Postfix \"$this->myhostname\" Kaspersky antivirus Enabled\n";
					$smtpd_milters[]="inet:127.0.0.1:1052";
				}
			}
		}
		
		if($users->MILTERGREYLIST_INSTALLED){
			if($array_filters["APP_MILTERGREYLIST"]==1){
				echo "Starting......: Postfix \"$this->myhostname\" milter-greylist Enabled\n";
				$smtpd_milters[]="unix:/var/spool/postfix/var/run/milter-greylist/$this->myhostname/greylist.sock";
			}
		}

		if(!is_array($smtpd_milters)){return null;}
			$conf[]="smtpd_milters=" . implode(" ",$smtpd_milters);
			
			if($onlymilter_config){
				return implode(" ",$smtpd_milters);
			}
			
			$conf[]="milter_connect_macros = j _ {daemon_name} {if_name} {if_addr} {client_name} {client_addr} {client_resolve} {client_ptr}";
			$conf[]="milter_helo_macros = {tls_version} {cipher} {cipher_bits} {cert_subject} {cert_issuer}";
			$conf[]="milter_mail_macros = i {auth_type} {auth_authen} {auth_ssf} {auth_author} {mail_mailer} {mail_host} {mail_addr} {client_addr} {if_addr}";
			$conf[]="milter_rcpt_macros = {rcpt_mailer} {rcpt_host} {rcpt_addr} {client_addr} {if_addr}";
			$conf[]="milter_default_action = accept";
			$conf[]="milter_protocol = 3";
			$conf[]="milter_connect_timeout=180";
			$conf[]="milter_command_timeout=180";
			$conf[]="milter_content_timeout=600";	
			if(count($nosmtpd_milters)>0){
				$conf[]="non_smtpd_milters =".implode(" ",$nosmtpd_milters)."\n";
			}
			
			$conf[]="";
			return implode("\n",$conf);
			
		
	}
	
	public function ConfigureMilters(){
		$milters=$this->milters(true);
		echo "Starting......: Postfix \"$this->myhostname\" configure milters\n";
		if($GLOBALS["postconf"]==null){$unix=new unix();$GLOBALS["postconf"]=$unix->find_program("postconf");}
		if($GLOBALS["postmulti"]==null){$unix=new unix();$GLOBALS["postmulti"]=$unix->find_program("postmulti");}
		shell_exec("{$GLOBALS["postconf"]} -c \"/etc/postfix-$this->myhostname\" -e \"smtpd_milters = $milters\"");
		shell_exec("{$GLOBALS["postmulti"]} -i $this->myhostname -p reload");
		
		
	}
	
	
	public function CheckDirectories($hostname){
		
		$queues[]="active";
		$queues[]="public";
		$queues[]="maildrop";
		$queues[]="pid";
		$queues[]="corrupt";
		$queues[]="defer";
		$queues[]="deferred";
		$queues[]="dev";
		$queues[]="etc";
		$queues[]="flush";
		$queues[]="hold";
		$queues[]="incoming";
		$queues[]="lib";
		$queues[]="private";
		$queues[]="public";
		$queues[]="saved";
		$queues[]="spamass";
		$queues[]="trace";
		$queues[]="usr";
		$queues[]="var";
		
		while (list ($num, $dir) = each ($queues) ){
			if(!is_dir("/var/spool/postfix-$hostname/$dir")){
				echo "Starting......: Postfix $hostname Create directory $dir\n";
				@mkdir("/var/spool/postfix-$hostname/$dir",0600,true);
			}
		}
	
		$queues_postdrop[]="maildrop";
		$queues_postdrop[]="public";
		
		$queues_postfix[]="active";
		$queues_postfix[]="bounce"; 
		$queues_postfix[]="corrupt";
		$queues_postfix[]="defer";
		$queues_postfix[]="deferred";
		$queues_postfix[]="flush";
		$queues_postfix[]="hold";
		$queues_postfix[]="incoming";
		$queues_postfix[]="private";
		$queues_postfix[]="saved";
		$queues_postfix[]="trace";
		$queues_postfix[]="var";
		
		while (list ($num, $dir) = each ($queues_postdrop) ){
				shell_exec("/bin/chown -R postfix:postdrop /var/spool/postfix-$hostname/$dir");
		}
		
		while (list ($num, $dir) = each ($queues_postfix) ){
				shell_exec("/bin/chown -R postfix:root /var/spool/postfix-$hostname/$dir");
		}		
		
		
		@mkdir("/var/lib/postfix-$hostname",0600,true);
		@mkdir("/etc/postfix-$hostname",0600,true);
		shell_exec("/bin/chown -R postfix:postfix /var/lib/postfix-$hostname");
		shell_exec("/bin/chown -R root:root /etc/postfix-$hostname");		

		$mandatories[]="/etc/postfix-$hostname/sasl_passwd";
		$mandatories[]="/etc/postfix-$hostname/sasl_relayhost";
		$mandatories[]="/etc/postfix-$hostname/dynamicmaps.cf";
		
		while (list ($num, $filename) = each ($mandatories) ){
			if(!is_file($filename)){
				@file_put_contents($filename,"\n");
			}
		}
		
		$unix=new unix();
		$postfix=$unix->find_program("postfix");
		if(!is_file("/etc/postfix-$hostname/postfix-script")){
			if(is_file("/etc/postfix/postfix-script")){
				@copy("/etc/postfix/postfix-script","/etc/postfix-$hostname/postfix-script");
				@chmod("/etc/postfix-$hostname/postfix-script",0755);
			}
		}
		system("$postfix -c /etc/postfix-$hostname set-permissions");
		
		
	}
	

	

	
	public function amavis_local_port(){
		$port=$this->GET("amavis_local_port");
		if($port==null){
			$sql="SELECT `value` FROM postfix_multi WHERE `key`='amavis_local_port' AND uuid='$this->uuid'";
			$port=1024;
			$results=$this->q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
				$nextport=$ligne["value"];
				if($nextport>$port){$port=$nextport;}
			}
			
		$port=$port+1;
		$this->SET_VALUE("amavis_local_port",$port);
		}
		return $port;
	}
	
	
	function body_checks(){
		$EnableBodyChecks=$this->GET("EnableBodyChecks");
		if(!is_numeric($EnableBodyChecks)){return;}
		if($EnableBodyChecks==0){return;}		
		$sql="SELECT * FROM postfix_regex_words WHERE `hostname`='$this->myhostname' AND enabled=1";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "Starting......: Postfix Mysql Error on body_checks() function\n";
			return;
		}
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$pattern=trim($ligne["words"]);
			if($pattern==null){continue;}
			$pattern=str_replace(" ","\s+",$pattern);
			$pattern=str_replace("$","\$",$pattern);
			$pattern=str_replace("(","\(",$pattern);
			$pattern=str_replace(")","\)",$pattern);
			$pattern=str_replace("[","\[",$pattern);
			$pattern=str_replace("]","\]",$pattern);
			$pattern=str_replace(".","\.",$pattern);
			$pattern=str_replace("/","\/",$pattern);
			$pattern=str_replace("?","\?",$pattern);
			$pattern=str_replace("*",".*?",$pattern);
			$f[]="/$pattern/ REJECT Message Body rejected [{$ligne["ID"]}]";
			
		}
		
		if(strtolower($this->myhostname=="master")){
			$target="/etc/postfix/body_checks";
		}else{
			$target="/etc/postfix-$this->myhostname/body_checks";
		}
		echo "Starting......: Postfix \"$this->myhostname\" ". count($f). " Regex Body rules\n";
		@file_put_contents($target,@implode("\n",$f));
		if(count($f)>0){return "body_checks = regexp:$target";}
		
	}
	
	
	
	public function buildmaster(){
			
			$ADD_PRECLEANUP=false;
			$sock=new sockets();
			$users=new usersMenus();
			$EnableSubmission=$this->GET("EnableSubmission");
			$EnablePostScreen=$this->GET("EnablePostScreen");
			$PostfixMasterEnableSSL=$this->GET("PostfixMasterEnableSSL");
			$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
			$array_filters=unserialize(base64_decode($this->GET_BIGDATA("PluginsEnabled")));
			$PostFixEnableAiguilleuse=$this->GET("PostFixEnableAiguilleuse");
			$amavis[]="";
			$postfix_listen_port="smtp";
			$postscreen_listen_port="smtp";
			$smtp_in_proto="inet";
			$smtp_private="n";
			
			if($users->AMAVIS_INSTALLED){$array["APP_AMAVIS"]=$array_filters["APP_AMAVIS"];}else{
				echo "Starting......: Postfix \"$this->myhostname\" amavisd-new is not installed\n";
				$amavis[]="#amavis not installed";
			}
			
			if($EnableArticaSMTPFilter==1){$array["APP_ARTICA_FILTER"]=$array_filters["APP_ARTICA_FILTER"];}
			if($array["APP_ARTICA_FILTER"]==null){$array["APP_ARTICA_FILTER"]=0;}
			if($PostFixEnableAiguilleuse==1){$array["APP_ARTICA_FILTER"]=1;}
				
			
			
			$artica[]="";
			$artica[]="$this->ip_addr:33559	inet	n	-	n	-	-	smtpd";
			$artica[]=" -o notify_clases=protocol,resource,software";
			$artica[]=" -o header_checks=";
			$artica[]=" -o content_filter=";
			$artica[]=" -o smtpd_restriction_classes=";
			$artica[]=" -o smtpd_delay_reject=no";
			$artica[]=" -o smtpd_client_restrictions=permit_mynetworks,reject";
			$artica[]=" -o smtpd_helo_restrictions=";
			$artica[]=" -o smtpd_sender_restrictions=";
			$artica[]=" -o smtpd_end_of_data_restrictions=";
			$artica[]=" -o smtpd_recipient_restrictions=permit_mynetworks,reject";
			$artica[]=" -o smtpd_data_restrictions=reject_unauth_pipelining";
			$artica[]=" -o smtpd_end_of_data_restrictions=";
			$artica[]=" -o mynetworks=127.0.0.0/8,$this->ip_addr";
			$artica[]=" -o strict_rfc821_envelopes=yes";
			$artica[]=" -o smtpd_error_sleep_time=0";
			$artica[]=" -o smtpd_soft_error_limit=1001";
			$artica[]=" -o smtpd_hard_error_limit=1000";
			$artica[]=" -o smtpd_client_connection_count_limit=0";
			$artica[]=" -o smtpd_client_connection_rate_limit=0";
			$artica[]=" -o receive_override_options=no_header_body_checks,no_unknown_recipient_checks";
			$artica[]=" -o smtp_send_xforward_command=yes";
			$artica[]=" -o disable_dns_lookups=yes";
			$artica[]=" -o local_header_rewrite_clients=";
			$artica[]=" -o smtpd_milters=";
			$artica[]="	-o smtpd_sasl_auth_enable=no"; 
			$artica[]="	-o smtpd_use_tls=no";
			$artica[]="";
			$artica_smtp=@implode("\n",$artica);
			$artica_filter_smtp_option=" -o content_filter=artica-filter:";
			$artica_filter_amavis_option=" -o content_filter=artica-filter:";
			
			if($array["APP_ARTICA_FILTER"]==0){
				echo "Starting......: Postfix \"$this->myhostname\" artica-filter is not enabled\n";
				$artica_smtp=null;
				$artica_filter_smtp_option=null;
				$artica_filter_amavis_option=" -o content_filter=";
			}else{
				echo "Starting......: Postfix \"$this->myhostname\" artica-filter is enabled\n";
				$ADD_PRECLEANUP=true;
				$amavis_cleanup_infos  =" -o cleanup_service_name=pre-cleanup";
			}
		
			
			if($users->AMAVIS_INSTALLED){
				$port=$this->amavis_local_port();
				$amavis_smtp="\n -o content_filter=smtp-amavis:127.0.0.1:$port\n";
				$amavis[]="smtp-amavis\tunix\t-\t-\t-\t-\t4\tsmtp";
				if($amavis_cleanup_infos<>null){$amavis[]=$amavis_cleanup_infos;}
				$amavis[]=" -o smtp_data_done_timeout=1200";
				$amavis[]=" -o smtp_send_xforward_command=yes";
				$amavis[]=" -o disable_dns_lookups=yes";
				$amavis[]=" -o smtp_generic_maps=";
				$amavis[]="	-o smtpd_sasl_auth_enable=no"; 
				$amavis[]="	-o smtpd_use_tls=no";	
				$amavis[]=" -o max_use=20";				
				$amavis[]="";
				$amavis[]="";
				$amavis[]="$this->ip_addr:10026\tinet\tn\t-\tn\t-\t-\tsmtpd";
				if($artica_filter_amavis_option<>null){$amavis[]=$artica_filter_amavis_option;}
				$amavis[]=" -o local_recipient_maps=";
				$amavis[]=" -o relay_recipient_maps=";
				$amavis[]=" -o smtpd_restriction_classes=";
				$amavis[]=" -o smtpd_client_restrictions=";
				$amavis[]=" -o smtpd_helo_restrictions=";
				$amavis[]=" -o smtpd_sender_restrictions=";
				$artica[]=" -o smtpd_end_of_data_restrictions=";
				$amavis[]=" -o smtp_generic_maps=";
				$amavis[]=" -o smtpd_recipient_restrictions=permit_mynetworks,reject";
				$amavis[]=" -o mynetworks=127.0.0.0/8,$this->ip_addr";
				$amavis[]=" -o mynetworks_style=host";
				$amavis[]=" -o strict_rfc821_envelopes=yes";
				$amavis[]=" -o smtpd_error_sleep_time=0";
				$amavis[]=" -o smtpd_soft_error_limit=1001";
				$amavis[]=" -o smtpd_hard_error_limit=1000";
				$amavis[]=" -o receive_override_options=no_header_body_checks";	
				$amavis[]="	-o smtpd_sasl_auth_enable=no"; 
				$amavis[]="	-o smtpd_use_tls=no";								
				

			if($array["APP_AMAVIS"]==1){
				$ADD_PRECLEANUP=true;
				$artica_filter_smtp_option=null;
			}else{
				echo "Starting......: Postfix \"$this->myhostname\" amavisd-new is not enabled\n";
				unset($amavis);$amavis[]="#amavis not enabled";$amavis_smtp=null;}
			}
			
			if($ADD_PRECLEANUP){
				echo "Starting......: Enable pre-cleanup service...\n";
				$pre_cleanup_addons=" -o smtp_generic_maps= -o canonical_maps= -o sender_canonical_maps= -o recipient_canonical_maps= -o masquerade_domains= -o recipient_bcc_maps= -o sender_bcc_maps=";
				$re_cleanup_infos  =" -o cleanup_service_name=pre-cleanup";
			}	

			
			if($EnablePostScreen==1){
				$EnableSubmission=1;
				echo "Starting......: PostScreen $this->myhostname is enabled, users should use 587 port to send mails internally\n"; 
				$smtp_in_proto="pass";
				$smtp_private="-";
				if($postfix_listen_port=="smtp"){$postfix_listen_port="smtpd";}
				$postscreen_line="$postscreen_listen_port\tinet\tn\t-\tn\t-\t1\tpostscreen -o soft_bounce=yes";
				$tlsproxy="tlsproxy\tunix\t-\t-\tn\t-\t0\ttlsproxy";
				$dnsblog="dnsblog\tunix\t-\t-\tn\t-\t0\tdnsblog";				
				
			}
			
			
			$conf[]="# Postfix master process configuration file.  For details on the format";
			$conf[]="# of the file, see the master(5) manual page (command: \"man 5 master\").";
			$conf[]="#";
			$conf[]="# ==========================================================================";
			$conf[]="# service type  private unpriv  chroot  wakeup  maxproc command + args";
			$conf[]="#               (yes)   (yes)   (yes)   (never) (100)";
			$conf[]="# ==========================================================================";
			$conf[]=$postscreen_line;
			$conf[]=$tlsproxy;
			$conf[]=$dnsblog;
			$conf[]="$postfix_listen_port	$smtp_in_proto	$smtp_private	-	n	-	-	smtpd$re_cleanup_infos$amavis_smtp$artica_filter_smtp_option";
			if($EnableSubmission==1){
				$conf[]="submission	inet	n	-	n	-	-	smtpd";
				if($re_cleanup_infos<>null){$conf[]=$re_cleanup_infos;}
   				$conf[]=" -o smtpd_etrn_restrictions=reject";
   				$conf[]=" -o smtpd_enforce_tls=yes";
   				$conf[]=" -o smtpd_sasl_auth_enable=yes";
   				$conf[]=" -o smtp_generic_maps=";
   				$conf[]=" -o smtpd_client_restrictions=permit_sasl_authenticated,reject"; 
   				$conf[]=$artica_filter_smtp_option;
   				$conf[]="";				
			}
			
			if($PostfixMasterEnableSSL==1){
				$conf[]="smtps	inet	n	-	n	-	-	smtpd";
				if($re_cleanup_infos<>null){$conf[]=$re_cleanup_infos;}
   				$conf[]=" -o smtpd_tls_wrappermode=yes";
   				$conf[]=" -o smtpd_sasl_auth_enable=yes";
   				$conf[]=" -o smtp_generic_maps=";
   				$conf[]=" -o smtpd_client_restrictions=permit_sasl_authenticated,reject";
   				$conf[]=$artica_filter_smtp_option;
   				$conf[]=""; 				
			}			
			$conf[]="";
			$conf[]=implode("\n",$amavis);
			$conf[]="";
			$conf[]=$artica_smtp;
			$conf[]="";
			$conf[]="pickup	fifo	n	-	n	60	1	pickup ";
			$conf[]="cleanup	unix	n	-	n	-	0	cleanup ";
			$conf[]="pre-cleanup	unix	n	-       n       -       0   cleanup$pre_cleanup_addons";
			$conf[]="qmgr	fifo	n	-	n	300	1	qmgr ";
			$conf[]="tlsmgr	unix	-	-	n	1000?	1	tlsmgr ";
			$conf[]="rewrite	unix	-	-	n	-	-	trivial-rewrite ";
			$conf[]="bounce	unix	-	-	n	-	0	bounce ";
			$conf[]="defer	unix	-	-	n	-	0	bounce ";
			$conf[]="trace	unix	-	-	n	-	0	bounce ";
			$conf[]="verify	unix	-	-	n	-	1	verify ";
			$conf[]="flush	unix	n	-	n	1000?	0	flush ";
			$conf[]="proxymap	unix	-	-	n	-	-	proxymap ";
			$conf[]="proxywrite	unix	-	-	n	-	1	proxymap ";
			$conf[]="smtp	unix	-	-	n	-	-	smtp";
			$conf[]="relay	unix	-	-	n	-	-	smtp";
			$conf[]=$this->DOMAINS_THROTTLE();
			$conf[]=" -o fallback_relay=";
			$conf[]="showq	unix	n	-	n	-	-	showq ";
			$conf[]="error	unix	-	-	n	-	-	error ";
			$conf[]="discard	unix	-	-	n	-	-	discard ";
			$conf[]="local	unix	-	n	n	-	-	local ";
			$conf[]="virtual	unix	-	n	n	-	-	virtual ";
			$conf[]="lmtp	unix	-	-	n	-	-	lmtp ";
			$conf[]="anvil	unix	-	-	n	-	1	anvil ";
			$conf[]="scache	unix	-	-	n	-	1	scache ";
			$conf[]="scan	unix	-	-	n	-	10	sm -v";
			$conf[]="maildrop	unix	-	n	n	-	-	pipe ";
			$conf[]="retry	unix	-	-	n	-	-	error ";	
			$conf[]="uucp	unix	-	n	n	-	-	pipe ";
			$conf[]=" flags=Fqhu user=uucp argv=uux -r -n -z -a\$sender - \$nexthop!rmail (\$recipient)";
			$conf[]="ifmail	unix	-	n	n	-	-	pipe ";
			$conf[]=" flags=F user=ftn argv=/usr/lib/ifmail/ifmail -r \$nexthop (\$recipient)";
			$conf[]=""; 
			$conf[]="bsmtp	unix	-	n	n	-	-	pipe ";
			$conf[]=" flags=Fq. user=bsmtp argv=/usr/lib/bsmtp/bsmtp -t\$nexthop -f\$sender \$recipient";
			$conf[]=""; 
			$conf[]="scalemail-backend	unix	-	n	n	-	2	pipe ";
			$conf[]=" flags=R user=scalemail argv=/usr/lib/scalemail/bin/scalemail-store \${nexthop} \${user} \${extension}";
			$conf[]=""; 
			$conf[]="mailman 		unix 	- 	n 	n 	- 	- 	pipe";
			$conf[]=" flags=FR user=mail:mail argv=/etc/mailman/postfix-to-mailman.py \${nexthop} \${mailbox}";
			$conf[]=""; 
			$conf[]="artica-whitelist    unix  -       n       n       -       -       pipe";
			$conf[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --white";
			$conf[]=""; 
			$conf[]="artica-blacklist    unix  -       n       n       -       -       pipe";
			$conf[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --black";
			$conf[]=""; 
			$conf[]="artica-reportwbl    unix  -       n       n       -       -       pipe";
			$conf[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --report";
			$conf[]=""; 
			$conf[]="artica-reportquar    unix  -       n       n       -       -       pipe";
			$conf[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --quarantines";
			$conf[]=""; 
			$conf[]="artica-spam    unix  -       n       n       -       -       pipe";
			$conf[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --spam";
			$conf[]=""; 
			$conf[]="zarafa    unix  -       n       n       -       -       pipe";
			$conf[]="  user=mail argv=/usr/local/bin/zarafa-dagent \${user}";
			$conf[]=""; 
			if($PostFixEnableAiguilleuse==1){$aiguilleusecmd=" -z yes";}
			$conf[]="artica-filter    unix  -       n       n       -       20       pipe";
			$conf[]="  flags=FOh  user=www-data argv=/usr/share/artica-postfix/exec.artica-filter.php -f \${sender} --  -s \${sender} -r \${recipient} -c \${client_address} -h $this->ip_addr -i $this->myhostname$aiguilleusecmd";
			$conf[]="";
			
			
			
			
			$conf[]="";
			@file_put_contents("/etc/postfix-$this->myhostname/master.cf",@implode("\n",$conf));	
			echo "Starting......: Postfix \"$this->myhostname\" master.cf done\n";	
		
	}

private function DOMAINS_THROTTLE_SMTP_CONNECTION_CACHE_DESTINATIONS($uuid){	
	$array=unserialize(base64_decode($this->GET_BIGDATA("domain_throttle_daemons_list")));
	$caches=$array[$uuid]["smtp-instance-cache-destinations"];
	if(count($caches)==0){return null;}
	while (list ($domain, $none) = each ($caches) ){
		$f[]="$domain\tOK";
		
	}
	
	@file_put_contents("/etc/postfix-$this->myhostname/{$uuid}_CONNECTION_CACHE_DESTINATIONS", implode("\n", $f));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->myhostname/{$uuid}_CONNECTION_CACHE_DESTINATIONS >/dev/null 2>&1");
	return "smtp_connection_cache_destinations=hash:/etc/postfix-$this->myhostname/{$uuid}_CONNECTION_CACHE_DESTINATIONS";
}
	
private function DOMAINS_THROTTLE(){
	if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: START\n";}
	$array=unserialize(base64_decode($this->GET_BIGDATA("domain_throttle_daemons_list")));

	$f=explode("\n",@file_get_contents("/etc/postfix-$this->myhostname/main.cf"));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#^[0-9]+_destination#",$line)){continue;}
		if(preg_match("#^[0-9]+_delivery_#",$line)){continue;}
		if(preg_match("#^[0-9]+_initial_#",$line)){continue;}
		$new[]=$line;
	}
	@file_put_contents("/etc/postfix-$this->myhostname/main.cf",@implode("\n",$new));
	unset($new);	
	
	
	if(!is_array($array)){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: NOT AN ARRAY\n";}
		return null;}
	
	while (list ($uuid, $conf) = each ($array) ){
		if($conf["ENABLED"]<>1){
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $uuid DISABLED\n";}
			continue;
		}
		if(count($conf["DOMAINS"])==0){
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $uuid NO TARGETED DOMAINS\n";}
			continue;
		}
		$maps=array();
		if($conf["transport_destination_concurrency_failed_cohort_limit"]==null){$conf["transport_destination_concurrency_failed_cohort_limit"]=1;}
		if($conf["transport_delivery_slot_loan"]==null){$conf["transport_delivery_slot_loan"]=3;}
		if($conf["transport_delivery_slot_discount"]==null){$conf["transport_delivery_slot_discount"]=50;}
		if($conf["transport_delivery_slot_cost"]==null){$conf["transport_delivery_slot_cost"]=5;}
		if($conf["transport_extra_recipient_limit"]==null){$conf["transport_extra_recipient_limit"]=1000;}
		if($conf["transport_initial_destination_concurrency"]==null){$conf["transport_initial_destination_concurrency"]=5;}
		if($conf["transport_destination_recipient_limit"]==null){$conf["transport_destination_recipient_limit"]=50;}		
		if($conf["transport_destination_concurrency_limit"]==null){$conf["transport_destination_concurrency_limit"]=20;}
		if($conf["transport_destination_rate_delay"]==null){$conf["transport_destination_rate_delay"]="0s";}
		if($conf["transport_destination_concurrency_positive_feedback"]==null){$conf["transport_destination_concurrency_positive_feedback"]="1/5";}
		if($conf["transport_destination_concurrency_negative_feedback"]==null){$conf["transport_destination_concurrency_negative_feedback"]="1/5";}		
		if(!is_numeric($conf["default_process_limit"])){$conf["default_process_limit"]=100;}
		$moinso[]="{$uuid}_destination_concurrency_failed_cohort_limit={$conf["transport_destination_concurrency_failed_cohort_limit"]}";
		$moinso[]="{$uuid}_delivery_slot_loan={$conf["transport_delivery_slot_loan"]}";
		$moinso[]="{$uuid}_delivery_slot_discount={$conf["transport_delivery_slot_discount"]}";
		$moinso[]="{$uuid}_delivery_slot_cost={$conf["transport_delivery_slot_cost"]}";
		$moinso[]="{$uuid}_initial_destination_concurrency={$conf["transport_initial_destination_concurrency"]}";
		$moinso[]="{$uuid}_destination_recipient_limit={$conf["transport_destination_recipient_limit"]}";
		$moinso[]="{$uuid}_destination_concurrency_limit={$conf["transport_destination_concurrency_limit"]}";
		$moinso[]="{$uuid}_destination_rate_delay={$conf["transport_destination_rate_delay"]}";
		$moinso[]="{$uuid}_extra_recipient_limit={$conf["transport_extra_recipient_limit"]}";
		$moinso[]="{$uuid}_destination_concurrency_positive_feedback={$conf["transport_destination_concurrency_positive_feedback"]}";
		$moinso[]="{$uuid}_destination_concurrency_negative_feedback={$conf["transport_destination_concurrency_negative_feedback"]}";
		
		
		$moinsoMasterText=null;
		if(is_numeric($conf["smtp_connection_cache_on_demand"])){
			if($conf["smtp_connection_cache_on_demand"]==0){
				$moinsoMaster[]="smtp_connection_cache_on_demand=no";
			}else{
				$moinsoMaster[]="smtp_connection_cache_on_demand=yes";
				$moinsoMaster[]="smtp_connection_cache_time_limit={$conf["smtp_connection_cache_time_limit"]}";
				$moinsoMaster[]="smtp_connection_reuse_time_limit={$conf["smtp_connection_reuse_time_limit"]}";
				$cache_destinations=$this->DOMAINS_THROTTLE_SMTP_CONNECTION_CACHE_DESTINATIONS($uuid);
				if($cache_destinations<>null){$moinsoMaster[]=$cache_destinations;}
			}
			
		}else{
			if($GLOBALS["VERBOSE"]){echo "DOMAINS_THROTTLE:: smtp_connection_cache_on_demand \"{$conf["smtp_connection_cache_on_demand"]}\" is not a numeric\n";}
		}
		
		if($GLOBALS["VERBOSE"]){echo "DOMAINS_THROTTLE:: smtp_connection_cache_on_demand \"". count($moinsoMaster)." value(s)";}
		if(count($moinsoMaster)>0){$moinsoMasterText=" -o ".@implode(" -o ", $moinsoMaster);}
		$instances[]="\n# THROTTLE {$conf["INSTANCE_NAME"]}\n$uuid\tunix\t-\t-\tn\t-\t{$conf["default_process_limit"]}\tsmtp$moinsoMasterText";
		while (list ($domain, $null) = each ($conf["DOMAINS"]) ){$maps[$domain]="$uuid:";}
		while (list ($a, $b) = each ($maps) ){$maps_final[]="$a\t$b";}
	}
	
	
	
	if(is_array($moinso)){
		while (list ($num, $cmd) = each ($moinso) ){
			shell_exec("{$GLOBALS["postconf"]} -c \"/etc/postfix-$this->myhostname\" -e \"$cmd\"");
		}
	}
	
	if(!is_array($instances)){return null;}
	@file_put_contents("/etc/postfix-$this->myhostname/transport.throttle",@implode("\n",$maps_final)."\n");
	return @implode("\n",$instances)."\n";
	
	
}	
	
	
	
	
	function PostfixMainCfDefaultInstance(){
		$users=new usersMenus();
		if($this->uuid==null){
			$sock=new sockets();
			$this->uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
		}
		
		if($this->uuid==null){
			echo "Starting......: Postfix warning function ". __FUNCTION__." uuid=null !\n";
			return;
		}
		
		$mailbox_transport=$this->getMailBoxTransport();
		$sql="SELECT `value` FROM postfix_multi WHERE `uuid`='$this->uuid' AND `key`='myhostname'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$myhostname=trim($ligne["value"]);
			if($myhostname==null){continue;}
			if($myhostname=="master"){continue;}
			$myhostnames[]="/etc/postfix-$myhostname";
			$myhosts[]=$myhostname;
			echo "Starting......: Postfix inserting $myhostname in master done\n";
		}
		
		if(is_array($myhostnames)){
			$multi_instance_directories=implode(", ",$myhostnames);
			while (list ($num, $host) = each ($myhosts) ){
				if($host=="master"){continue;}
				echo "Starting......: Postfix default instance check databases for $host\n";
				if(is_file("/etc/postfix-$host/relay_domains")){
					$relay_domains[]="hash:/etc/postfix-$host/relay_domains";
				}
				
				if(is_file("/etc/postfix-$host/virtual")){
					$virtual_mailbox_base[]="/var/spool/postfix-$host/virtual";
					$virtual_alias_maps[]="hash:/etc/postfix-$host/virtual";
				}

				if(is_file("/etc/postfix-$host/transport")){
					$transport_maps[]="hash:/etc/postfix-$host/transport";
				}	

				if(is_file("/etc/postfix-$host/aliases")){
					$alias_database[]="hash:/etc/postfix-$host/aliases";
					$alias_maps[]="hash:/etc/postfix-$host/aliases";
				}

				if(is_file("/etc/postfix-$host/mydestination")){
					$mydestination[]="hash:/etc/postfix-$host/mydestination";
				}	

				if(is_file("/etc/postfix-$host/sender_canonical")){
					$sender_canonical_maps[]="hash:/etc/postfix-$host/sender_canonical";
				}	

				if(is_file("/etc/postfix-$host/sender_dependent_relayhost")){
					$sender_dependent_relayhost_maps[]="hash:/etc/postfix-$host/sender_dependent_relayhost";
				}	

				if(is_file("/etc/postfix-$host/recipient_canonical")){
					$recipient_canonical_maps[]="hash:/etc/postfix-$host/recipient_canonical";
				}	

				if(is_file("/etc/postfix-$host/recipient_bcc")){
					$recipient_bcc_maps[]="hash:/etc/postfix-$host/recipient_bcc";
				}

				if(is_file("/etc/postfix-$host/sender_bcc")){
					$sender_bcc_maps[]="hash:/etc/postfix-$host/sender_bcc";
				}					
				
			}
		}

		$main=new maincf_multi("master","master");
		$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));	
		if(is_array($databases_list)){
			while (list ($dbindex, $array) = each ($databases_list) ){
				if($array["enabled"]<>1){continue;}
				$targeted_file=$this->buidLdapDB("master",$dbindex,$array);
				if(!is_file($targeted_file)){continue;}
				switch ($array["database_type"]) {
					case "alias_maps":$alias_database[]="ldap:$targeted_file";$alias_maps[]="ldap:$targeted_file";break;
					case "virtual_alias_maps":$virtual_alias_maps[]="ldap:$targeted_file";break;
					case "virtual_mailbox_maps":$virtual_mailbox_maps[]="ldap:$targeted_file";break;
					default:;break;
				}
			}	
			
		}
			
		if(is_array($relay_domains)){$conf[]="relay_domains = " .implode(",",$relay_domains);}
		//if(is_array($virtual_mailbox_base)){$conf[]="virtual_mailbox_base = " .implode(",",$virtual_mailbox_base);}
		if(is_array($virtual_alias_maps)){$conf[]="virtual_alias_maps = " .implode(",",$virtual_alias_maps);}
		if(is_array($transport_maps)){$conf[]="transport_maps = " .implode(",",$transport_maps);}
		if(is_array($alias_database)){$conf[]="alias_database = " .implode(",",$alias_database);}
		if(is_array($alias_maps)){$conf[]="alias_maps = " .implode(",",$alias_maps);}
		if(is_array($virtual_mailbox_maps)){$conf[]="virtual_mailbox_maps = " .implode(",",$virtual_mailbox_maps);}
		if(is_array($mydestination)){$conf[]="mydestination = " .implode(",",$mydestination);}
		if(is_array($sender_canonical_maps)){$conf[]="sender_canonical_maps = " .implode(",",$sender_canonical_maps);}
		if(is_array($sender_dependent_relayhost_maps)){$conf[]="sender_dependent_relayhost_maps = " .implode(",",$sender_dependent_relayhost_maps);}
		if(is_array($recipient_canonical_maps)){$conf[]="recipient_canonical_maps = " .implode(",",$recipient_canonical_maps);}
		if(is_array($recipient_bcc_maps)){$conf[]="recipient_bcc_maps = " .implode(",",$recipient_bcc_maps);}
		if(is_array(sender_bcc_maps)){$conf[]="sender_bcc_maps = " .implode(",",$sender_bcc_maps);}
		
		//$conf[]="virtual_mailbox_base=/var/spool/postfix"
		$conf[]="queue_directory = /var/spool/postfix";
		$conf[]="data_directory = /var/lib/postfix";
		$conf[]="daemon_directory=".$this->LOCATE_POSTFIX_DAEMON_DIRECTORY();
		
		$conf[]="mail_owner = postfix";
		$conf[]="#default_privs = nobody";
		$conf[]="myhostname = $users->hostname";
		$conf[]="#mydomain = domain.tld";
		$conf[]="myorigin = \$myhostname";
		$conf[]="inet_interfaces=127.0.0.1";
		$conf[]="local_recipient_maps =";
		$conf[]="mynetworks = 127.0.0.0/8";
		$conf[]="mailbox_transport = $mailbox_transport";
		$conf[]="fallback_transport = $mailbox_transport";
		$conf[]="smtpd_banner = \$myhostname ESMTP \$mail_name";
		
		$freeze_delivery_queue=$main->GET("freeze_delivery_queue");
			if($freeze_delivery_queue==1){
				echo "Starting......: Postfix delivery queue is frozen..\n";
				$conf[]="in_flow_delay=0";
				$conf[]="master_service_disable=qmgr.fifo";
			}	
		
		
		$conf[]="";
		$conf[]="";
		$unix=new unix();
		if(is_file($GLOBALS["postmulti"])){
			$conf[]="multi_instance_wrapper={$GLOBALS["postmulti"]} -p --";
		}else{
			$conf[]="multi_instance_wrapper=\${command_directory}/postmulti -p --";
		}
		$conf[]="multi_instance_enable=yes";
		$conf[]="multi_instance_directories=$multi_instance_directories\n";	
		$conf[]="";
		$conf[]="";
		@file_put_contents("/etc/postfix/main.cf",@implode("\n",$conf));
		
		if(!is_file("/usr/share/man/man1/postmulti.1")){@file_put_contents("/usr/share/man/man1/postmulti.1","#");}
		if(!is_file("/usr/share/man/man5/postfix-wrapper.5")){@mkdir("/usr/share/man/man5");@file_put_contents("/usr/share/man/man5/postfix-wrapper.5","#");}
		
		
		echo "Starting......: Postfix default main.cf done\n";
		}
		
		public function buidLdapDB($hostname,$dbindex,$array){
			if($hostname=="master"){
				$main_path="/etc/postfix";
			}else{
				$main_path="/etc/postfix-$hostname";
			}
			$database_type=trim($array["database_type"]);
			$server_host=trim($array["server_host"]);
			$search_base=trim($array["search_base"]);
			$bind_dn=trim($array["bind_dn"]);
			$bind_password=trim($array["bind_password"]);
			$query_filter=trim($array["query_filter"]);
			$scope=trim($array["scope"]);
			$result_attribute=trim($array["result_attribute"]);
			$leaf_result_attribute=trim($array["leaf_result_attribute"]);
			$special_result_attribute=trim($array["special_result_attribute"]);
			$database_type=trim($array["database_type"]);
			
			if($server_host<>null)     		   {$f[]="server_host = $server_host";}
			if($search_base<>null)     		   {$f[]="search_base = $search_base";}
			if($bind_dn<>null)         		   {$f[]="bind_dn = $bind_dn";}
			if($bind_password<>null)  		   {$f[]="bind_pw = $bind_password";}
			if($query_filter<>null)    		   {$f[]="query_filter = $query_filter";}
			if($scope<>null) 		   		   {$f[]="scope = $scope";}
			if($result_attribute<>null)		   {$f[]="result_attribute = $result_attribute";}
			if($special_result_attribute<>null){$f[]="special_result_attribute = $special_result_attribute";}
			if($leaf_result_attribute<>null)   {$f[]="leaf_result_attribute = $leaf_result_attribute";}
			
			$targeted_file="$main_path/{$database_type}_ldap.$dbindex.cf";
			@file_put_contents($targeted_file,@implode("\n",$f));
			if(!is_file($targeted_file)){
				if($GLOBALS["DEBUG"]){echo __CLASS__."/".__FUNCTION__."::LDAP:: $targeted_file no such file\n";}
			}else{
				if($GLOBALS["DEBUG"]){echo __CLASS__."/".__FUNCTION__."::LDAP:: $targeted_file exists\n";}
			}
			return $targeted_file;			        
			
		}
		
		
		public function buidLdapDBDomains($array){
			$server_host=trim($array["server_host"]);
			$search_base=trim($array["search_base"]);
			$bind_dn=trim($array["bind_dn"]);
			$bind_password=trim($array["bind_password"]);
			$md5=md5("$server_host$search_base");
			if($GLOBALS["REMOTE_SMTP_LDAPDB_SERVERS"][$md5]==true){
				if($GLOBALS["DEBUG"]){echo __CLASS__."/".__FUNCTION__."::LDAP:: $server_host $search_base already parsed\n";}
				return;
			}
			$con=@ldap_connect($server_host, 389) ;
			if(!$con){
				if($GLOBALS["DEBUG"]){echo __CLASS__."/".__FUNCTION__."::LDAP:: $server_host $search_base failed to connect\n";}
				return;
			}
			
			ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
			ldap_set_option($con, LDAP_OPT_REFERRALS, 0); 		 
			$ldapbind=@ldap_bind($con,$bind_dn,$bind_password);
			$filter="(&(objectClass=person))";
			$attr=array("ProxyAddresses","mail");
			$sr =@ldap_search($con,$search_base,$filter,$attr);
			if(!$sr){
				if($GLOBALS["DEBUG"]){echo __CLASS__."/".__FUNCTION__."::LDAP:: $server_host $search_base failed to search\n";}
				return;	
			}
			
			$hash=ldap_get_entries($con,$sr);
			if($GLOBALS["DEBUG"]){echo __CLASS__."/".__FUNCTION__."::LDAP:: Parsing {$hash["count"]} entries\n";}	
			
			for($i=0;$i<$hash["count"];$i++){
				for($z=0;$z<$hash[$i]["proxyaddresses"]["count"];$z++){
					$mail=$hash[$i]["proxyaddresses"][$z];
					if(preg_match("#.+?@(.+)#",$mail,$re)){
						$GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"][$re[1]]=$server_host;
					} 
				}
				
			for($z=0;$z<$hash[$i]["mail"]["count"];$z++){
					$mail=$hash[$i]["mail"][$z];
					if(preg_match("#.+?@(.+)#",$mail,$re)){
						$GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"][$re[1]]=$server_host;
					} 
				}
								
			}
	}
	
	
private function certificate_openssl_conf(){
		$datas=$this->GET_BIGDATA("certificate_smtp_parameters");
		$country_code="US";
		$contryname="Delaware";
		$locality="Wilmington";
		$organizationalUnitName=$this->ou;
		$organizationName=$this->ou;
		$emailAddress="root@$this->myhostname";
		if(preg_match("#(.+?)_(.+?)$#",$datas["countryName"],$re)){
			$contryname=$re[1];
			$country_code=$re[2];
		}
		if($datas["localityName"]<>null){$locality=$datas["localityName"];}
		if($datas["organizationalUnitName"]<>null){$organizationalUnitName=$datas["organizationalUnitName"];}
		if($datas["emailAddress"]<>null){$emailAddress=$datas["emailAddress"];}
		if($datas["organizationName"]<>null){$organizationName=$datas["organizationName"];}
	
		
	
		$conf[]="[ca]";
		$conf[]="default_ca=default_db";
		$conf[]="unique_subject=no";
		$conf[]="";
		$conf[]="[default_db]";
		$conf[]="dir=.";
		$conf[]="certs=.";
		$conf[]="new_certs_dir=/etc/postfix-$this->myhostname/ssl/new";
		$conf[]="database= /etc/postfix-$this->myhostname/ssl/ca.index";
		$conf[]="serial = /etc/postfix-$this->myhostname/ssl/ca.serial";
		$conf[]="RANDFILE=.rnd";
		$conf[]="certificate=/etc/postfix-$this->myhostname/ssl/key.pem";
		$conf[]="private_key=/etc/postfix-$this->myhostname/ssl/ca.key";
		$conf[]="default_days= 730";
		$conf[]="default_crl_days=30";
		$conf[]="default_md=md5";
		$conf[]="preserve=no";
		$conf[]="name_opt=ca_default";
		$conf[]="cert_opt=ca_default";
		$conf[]="unique_subject=no";
		$conf[]="policy=policy_match";
		$conf[]="";
		$conf[]="[server_policy]";
		$conf[]="countryName=supplied";
		$conf[]="stateOrProvinceName=supplied";
		$conf[]="localityName=supplied";
		$conf[]="organizationName=supplied";
		$conf[]="organizationalUnitName=supplied";
		$conf[]="commonName=supplied";
		$conf[]="emailAddress=supplied";
		$conf[]="";
		$conf[]="[server_cert]";
		$conf[]="subjectKeyIdentifier=hash";
		$conf[]="authorityKeyIdentifier=keyid:always";
		$conf[]="extendedKeyUsage=serverAuth,clientAuth,msSGC,nsSGC";
		$conf[]="basicConstraints= critical,CA:false";
		$conf[]="";
		$conf[]="[user_policy]";
		$conf[]="commonName=supplied";
		$conf[]="emailAddress=supplied";
		$conf[]="";
		$conf[]="[user_cert]";
		$conf[]="subjectAltName=email:copy";
		$conf[]="basicConstraints= critical,CA:false";
		$conf[]="authorityKeyIdentifier=keyid:always";
		$conf[]="extendedKeyUsage=clientAuth,emailProtection";
		$conf[]="";
		$conf[]="[req]";
		$conf[]="default_bits=1024";
		$conf[]="default_keyfile=ca.key";
		$conf[]="distinguished_name=default_ca";
		$conf[]="x509_extensions=extensions";
		$conf[]="string_mask=nombstr";
		$conf[]="req_extensions=req_extensions";
		$conf[]="input_password=secret";
		$conf[]="output_password=secret";
		$conf[]="";
		$conf[]="[default_ca]";
		$conf[]="countryName=Country Code";
		$conf[]="countryName_value=$country_code";
		$conf[]="countryName_min=2";
		$conf[]="countryName_max=2";
		$conf[]="stateOrProvinceName=State Name";
		$conf[]="stateOrProvinceName_value=$contryname";
		$conf[]="localityName=Locality Name";
		$conf[]="localityName_value=$locality";
		$conf[]="organizationName=Organization Name";
		$conf[]="organizationName_value=$organizationName";
		$conf[]="organizationalUnitName=Organizational Unit Name";
		$conf[]="organizationalUnitName_value=$organizationalUnitName";
		$conf[]="commonName=Common Name";
		$conf[]="commonName_value=$this->myhostname";
		$conf[]="commonName_max=128";
		$conf[]="emailAddress=Email Address";
		$conf[]="emailAddress_value=$emailAddress";
		$conf[]="emailAddress_max=128";
		$conf[]="unique_subject=no";
		$conf[]="";
		$conf[]="[extensions]";
		$conf[]="subjectKeyIdentifier=hash";
		$conf[]="authorityKeyIdentifier=keyid:always";
		$conf[]="basicConstraints=critical,CA:false";
		$conf[]="";
		$conf[]="[req_extensions]";
		$conf[]="nsCertType=objsign,email,server";
		$conf[]="";
		$conf[]="[CA_default]";
		$conf[]="policy=policy_match";
		$conf[]="";
		$conf[]="[policy_match]";
		$conf[]="countryName=match";
		$conf[]="stateOrProvinceName=match";
		$conf[]="organizationName=match";
		$conf[]="organizationalUnitName=optional";
		$conf[]="commonName=match";
		$conf[]="emailAddress=optional";
		$conf[]="";
		$conf[]="[policy_anything]";
		$conf[]="countryName=optional";
		$conf[]="stateOrProvinceName=optional";
		$conf[]="localityName=optional";
		$conf[]="organizationName=optional";
		$conf[]="organizationalUnitName=optional";
		$conf[]="commonName=optional";
		$conf[]="emailAddress=optional";
		$conf[]="";
		$conf[]="[v3_ca]";
		$conf[]="subjectKeyIdentifier=hash";
		$conf[]="authorityKeyIdentifier=keyid:always,issuer:always";
		$conf[]="basicConstraints=critical,CA:false";
		@mkdir("/etc/postfix-$this->myhostname/ssl",0666,true);
		file_put_contents("/etc/postfix-$this->myhostname/ssl/openssl.conf",@implode("\n",$conf));		
	}

	public function certificate_generate(){
		 echo "Starting......: Postfix \"$this->myhostname\" building TLS certificate\n";
		 $this->certificate_openssl_conf();
		 $unix=new unix();
		 $openssl=$unix->find_program("openssl");
		 $config="/etc/postfix-$this->myhostname/ssl/openssl.conf";
		 $ssl_path="/etc/postfix-$this->myhostname/ssl";
		 system("$openssl genrsa -out /etc/postfix-$this->myhostname/ssl/server.key 1024");
	     system("$openssl req -new -key $ssl_path/server.key -batch -config $config -out $ssl_path/server.csr");
	     system("$openssl genrsa -out $ssl_path/ca.key 1024 -batch -config $config");
	     system("$openssl req -new -x509 -days 730 -key $ssl_path/ca.key -batch -config $config -out $ssl_path/ca.csr");
	     system("$openssl x509 -extfile $config -x509toreq -days 730 -in $ssl_path/ca.csr -signkey $ssl_path/ca.key -out $ssl_path/ca.req");
	     system("$openssl x509 -extfile $config -req -days 730 -in $ssl_path/ca.req -signkey $ssl_path/ca.key -out $ssl_path/ca.crt");

	}
}

function PostFixMultiVerifyRights(){
	if($_REQUEST["hostname"]=="master"){$_GET["ou"]="master";}
	if(!isset($_GET["ou"])){return false;}
	$ou=$_REQUEST["ou"];
	if(is_base64_encoded($ou)){$ou=base64_decode($ou);}
	
	$usersmenus=new usersMenus();
	if($usersmenus->AsPostfixAdministrator){return true;}
	
	
	if($usersmenus->AsOrgPostfixAdministrator){
		if($_SESSION["ou"]<>$ou){return false;}
		return TRUE;
	}
	if($_SESSION["uid"]<>-100){if($_SESSION["ou"]<>$ou){return false;}}
	return true;
	}

?>