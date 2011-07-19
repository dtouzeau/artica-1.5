<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.assp-multi.inc');
include_once(dirname(__FILE__) . '/class.system.network.inc');


class multi_config{
	var $ou=null;
	
	
	
	var $q;
	function multi_config($ou){
		$this->ou=$ou;
		$this->q=new mysql();
	}
	

	public function GET($key){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		return $ligne["value"];
	}
	
	
	public function SET_VALUE($key,$value){
		$sql="SELECT `ID` FROM postfix_multi WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){
			$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`) VALUES('$this->ou','$key','$value')";
		}else{
			$sql="UPDATE postfix_multi SET `value`='$value' WHERE ID={$ligne["ID"]}";
		}
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nSET_VALUE($key,$value)";
		}
	}	
	
	
}


class main_multi{
	var $ou;
	var $instance;
	var $MAIN_ARRAY=array();
	var $DN;
	var $inet_interfaces=array();
	var $bcc_maps=array();
	var $senderbccmaps=array();
	var $mydestination_hash=array();
	var $virtual_alias_maps=array();
	var $alias_maps_hash=array();
	var $virtual_mailbox_hash=array();
	var $transport_maps_hash_AT=array();
	var $transport_maps_hash=array();
	
	function main_multi($ou,$nostart=0){
		$this->ou=$ou;
		$ldap=new clladp();
		$this->DN="ou=$ou,dc=organizations,$ldap->suffix";
		
		$this->instance=str_replace(" ","-",$ou);
		if($nostart==0){
			
			$this->LoadDatas();
			
			$this->inet_interfaces_load();
			$this->mydestination_maps();
			$this->transport_maps();
			$this->Aliases();
			$this->ArticaDefault();
			$this->mynetworks();
			$this->MyHostname_SET();
			$this->smtpd_client_restrictions_set();
			$this->postfix_allowed_connections();
			$this->SaveConf();
			$this->recipient_bcc_maps();
			$this->sender_bcc_maps();
			$this->aliases_users();
			
			
			
			}
		}
		
	private function LoadDatas(){
		$datas=explode("\n",@file_get_contents("/etc/postfix-$this->instance/main.cf"));
		while (list ($num, $line) = each ($datas) ){
			if(substr($line,0,1)=="#"){continue;}
			if(preg_match("#(.+?)=(.+)#",$line,$re)){$this->MAIN_ARRAY[trim($re[1])]=trim($re[2]);}
		}
	}
	
	private function SaveConf(){
		while (list ($key, $line) = each ($this->MAIN_ARRAY) ){$conf=$conf."$key = $line\n";}
		@file_put_contents("/etc/postfix-$this->instance/main.cf",$conf);
		$this->BuildMaps();
		$array=$this->BuildMasterCF();
		@file_put_contents("/etc/postfix-$this->instance/master.cf",implode("\n",$array));
		
		
	}
	
	
	private function CleanMainCf(){
		
		$f=explode("\n",@file_put_contents("/etc/postfix-$this->instance/main.cf"));
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match("#^mydestinationTable#",$ligne)){unset($f[$num]);}
			if(preg_match("#^TransportMapsTable#",$ligne)){unset($f[$num]);}
		}
		
		echo "Starting......: Cleaning /etc/postfix-$this->instance/main.cf ". count($f)." lines\n";
		@file_put_contents("/etc/postfix-$this->instance/main.cf",@implode("\n",$f));
		
	}
	
	
	private function BuildMaps(){
		
		$postconf_macro="{$GLOBALS["postmulti"]} -i postfix-$this->instance -x postconf -e";
		$instance_path="/etc/postfix-$this->instance";
		
		echo "Starting......: $this->ou:: Macro..........: $postconf_macro\n";
		echo "Starting......: $this->ou:: Path...........: $instance_path\n";  
		echo "Starting......: $this->ou:: queue directory: /var/spool/postfix/$this->instance\n"; 
		
		shell_exec("$postconf_macro \"relay_recipient_maps =\" >/dev/null 2>&1");
		
		
			if(!is_array($this->senderbccmaps)){
				shell_exec("$postconf_macro -e sender_bcc_maps=\"\"");
			}else{
				shell_exec("{$GLOBALS["postconf"]} -e \"sender_bcc_maps =hash:/etc/postfix/sender_bcc\" >/dev/null 2>&1");
				echo "Starting......: $this->ou:: Compiling Sender(s) BCC\n"; 
				@file_put_contents("$instance_path/sender_bcc",implode("\n",$this->senderbccmaps));
				shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix-$this->instance/sender_bcc >/dev/null 2>&1");
				shell_exec("$postconf_macro sender_bcc_maps=\"hash:/etc/postfix-$this->instance/sender_bcc\"");
			}
		
		
			if(!is_array($this->bcc_maps)){
				shell_exec("$postconf_macro recipient_bcc_maps=\"\"");
			}else{
				shell_exec("$postconf_macro recipient_bcc_maps=\"/etc/postfix-$this->instance/recipient_bcc\"");
				echo "Starting......: $this->ou:: Compiling Recipient(s) BCC\n";
				@file_put_contents("$instance_path/recipient_bcc",implode("\n",$this->bcc_maps));
				shell_exec("{$GLOBALS["postmap"]} hash:$instance_path/recipient_bcc >/dev/null 2>&1");
			}

			
		if(!is_array($this->mydestination_hash)){
			shell_exec("$postconf_macro \"mydestination = \" >/dev/null 2>&1");
			shell_exec("$postconf_macro  \"virtual_mailbox_domains = \" >/dev/null 2>&1");
		}else{
			shell_exec("$postconf_macro \"mydestination =hash:$instance_path/mydestination\" >/dev/null 2>&1");
			shell_exec("$postconf_macro \"virtual_mailbox_domains =\" >/dev/null 2>&1");
			@file_put_contents("$instance_path/mydestination",implode("\n",$GLOBALS["mydestination"]));
			shell_exec("{$GLOBALS["postmap"]} hash:$instance_path/mydestination >/dev/null 2>&1");		
		}
		
		
		if(!is_array($this->virtual_alias_maps)){
			shell_exec("$postconf_macro \"virtual_alias_maps = \" >/dev/null 2>&1");
			echo "Starting......: $this->ou:: No virtual aliases\n"; 
			}else{	
			echo "Starting......: $this->ou:: ". count($this->virtual_alias_maps)." virtual aliase(s)\n"; 		
			shell_exec("$postconf_macro \"virtual_alias_maps =hash:$instance_path/virtual\" >/dev/null 2>&1");		
			@file_put_contents("$instance_path/virtual",implode("\n",$this->virtual_alias_maps));
			echo "Starting......: $this->ou:: compiling virtual aliase database $instance_path/virtual\n"; 
			shell_exec("{$GLOBALS["postmap"]} hash:$instance_path/virtual >/dev/null 2>&1");		
		}	

		
		if(!is_array($this->alias_maps_hash)){
				shell_exec("$postconf_macro \"aliases_maps = \" >/dev/null 2>&1");
				shell_exec("$postconf_macro -e \"virtual_mailbox_maps = \" >/dev/null 2>&1");
				echo "Starting......: $this->ou:: No aliases\n"; 
		}else{
			if($GLOBALS["MAILMAN"]=true){
				echo "Starting......: Building mailman aliase(s)\n";
				if($this->mailman_aliases()){
					$hash_mailman=",hash:{$GLOBALS["MAILMAN_ALIASES"]}";
					$hash_mailman_virtual=",hash:/var/lib/mailman/data/virtual-mailman";
				}
			}
			
			echo "Starting......: $this->ou:: ". count($this->alias_maps_hash)." aliase(s)\n"; 
			shell_exec("$postconf_macro \"alias_maps =hash:$instance_path/aliases$hash_mailman\" >/dev/null 2>&1");
			shell_exec("$postconf_macro \"alias_database =hash:$instance_path/aliases\" >/dev/null 2>&1");
			@file_put_contents("$instance_path/aliases",implode("\n",$this->alias_maps_hash));
			shell_exec("{$GLOBALS["postmap"]} hash:$instance_path/aliases >/dev/null 2>&1");			
		}
		
		
		if(!is_array($this->transport_maps_hash)){
			shell_exec("$postconf_macro \"transport_maps =\" >/dev/null 2>&1");
		}else{
			while (list ($num, $ligne) = each ($this->transport_maps_hash) ){if($ligne==null){continue;}$array[]="$num\t$ligne";}	
			echo "Starting......: $this->ou:: ". count($array)." routings rules\n"; 
			
			if(is_array($this->transport_maps_hash_AT)){
				while (list ($num, $ligne) = each ($this->transport_maps_hash_AT) ){if($ligne==null){continue;}$array[]="$num\t$ligne";}
			}
			@file_put_contents("$instance_path/transport",implode("\n",$array));
			shell_exec("{$GLOBALS["postmap"]} hash:$instance_path/transport >/dev/null 2>&1");
			shell_exec("$instance_path \"transport_maps = hash:$instance_path/transport\" >/dev/null 2>&1");		
		}

		
		shell_exec("$postconf_macro \"queue_directory=/var/spool/postfix/$this->instance\" >/dev/null 2>&1");
		$this->CleanMainCf();
		
}


	private function mailman_aliases(){
		if(!is_file("/var/lib/mailman/bin/genaliases")){
			echo "Starting......: Unable to locate genaliases tool\n";
			return false;
		}
		
		shell_exec("/var/lib/mailman/bin/genaliases");
		
		if(is_file("/var/lib/mailman/data/aliases")){
			$GLOBALS["MAILMAN_ALIASES"]="/var/lib/mailman/data/aliases";
			
		
		if(is_file("/var/lib/mailman/data/virtual-mailman")){
			$GLOBALS["MAILMAN_VIRTUAL"]="/var/lib/mailman/data/virtual-mailman";
			return true;
		}
		}
	}
	
	
	
	
	private function inet_interfaces_load(){
		$sql="SELECT nic,ipaddr FROM nics_virtuals WHERE org='$this->ou'";
		$q=new mysql();
		$ip=new networking();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["ipaddr"]==null){
				if($ligne["nic"]<>null){
					$ligne["ipaddr"]=$net->array_TCP[$ligne["nic"]];
				}
			}
			if($ligne["ipaddr"]<>null){
				$array[]=$ligne["ipaddr"];
			}			
		}
		
		$this->inet_interfaces=$array;
		if(is_array($array)){
			$this->MAIN_ARRAY["inet_interfaces"]=implode(",",$array);
		}else{
			echo "Starting......: $this->ou:: unable to get inet_interface !!\n";
		}
	}
	
	
private function mydestination_maps(){
	$ldap=new clladp();
	$filter="(&(objectClass=organizationalUnit)(associatedDomain=*))";
	$attrs=array("associatedDomain");
	$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		for($t=0;$t<$hash[$i]["associateddomain"]["count"];$t++){
			$this->mydestination_hash[]=$hash[$i][strtolower("associatedDomain")][$t]."\tOK";
		}
		
	}
	echo "Starting......: $this->ou:: ".count($this->mydestination_hash)." Local domain(s)\n"; 	
}
	
private function recipient_bcc_maps(){
	
$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(RecipientToAdd=*))";
	$attrs=array("RecipientToAdd","mail");
	$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$RecipientToAdd=$hash[$i]["recipienttoadd"][0];
		$this->bcc_maps[]="$mail\t$RecipientToAdd";
		
	}	
	echo "Starting......: $this->ou:: ". count($this->bcc_maps)." recipient(s) BCC\n"; 	
}

private function sender_bcc_maps(){
$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(SenderBccMaps=*))";
	$attrs=array("SenderBccMaps","mail");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$this->senderbccmaps=$hash[$i]["senderbccmaps"][0];
		$GLOBALS["sender_bcc_maps"][]="$mail\t$senderbccmaps";
		
	}	
	echo "Starting......: $this->ou:: ". count($this->senderbccmaps)." Sender(s) BCC\n"; 	
}
	
private function transport_maps(){
	$ldap=new clladp();
	$filter="(&(objectClass=transportTable)(cn=*))";
	$attrs=array("cn","transport");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		$transport=$hash[$i]["transport"][0];
		//$transport=str_replace("relay:","smtp:",$transport);
		
		if(substr($domain,0,1)=="@"){$domain=substr($domain,1,strlen($domain));}
		$this->transport_maps_hash["$domain"]="$transport";
		
		if(strpos("  $domain","@")==0){$domain="@$domain";}
		$this->transport_maps_hash_AT["$domain"]="$transport";
	}
	
	
  	$dn="cn=artica_smtp_sync,cn=artica,$ldap->suffix";
  	$filter="(&(objectClass=InternalRecipients)(cn=*))";	
  	$attrs=array("cn","ArticaSMTPSenderTable");	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$email=$hash[$i]["cn"][0];
		$transport=$hash[$i][strtolower("ArticaSMTPSenderTable")][0];
		$this->transport_maps_hash["$email"]="$transport";

	} 		
			
	}
	
private function aliases_users(){
	$ldap=new clladp();
	$filter="(&(objectClass=userAccount)(uid=*))";
	$attrs=array("uid","mail");
	$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		for($t=0;$t<$hash[$i]["mail"]["count"];$t++){
			$this->alias_maps_hash[]="$uid\t{$hash[$i]["mail"][$t]}";
			$this->virtual_mailbox_hash="{$hash[$i]["mail"][$t]}\t$uid";
		}
	}

	$filter="(&(objectClass=transportTable)(cn=*@*))";
	$attrs=array("cn");
	$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		$this->alias_maps_hash[]="$cn\tx";
	}

}	
	
	private function Aliases(){
	$ldap=new clladp();
	$filter="(&(objectClass=userAccount)(mailAlias=*))";
	$attrs=array("mail","mailAlias");
	$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		for($t=0;$t<$hash[$i]["mailalias"]["count"];$t++){
			$this->virtual_alias_maps[]="{$hash[$i]["mailalias"][$t]}\t$mail";
		}
	}

	
	
	$filter="(&(objectClass=MailingAliasesTable)(cn=*))";
	$attrs=array("cn","MailingListAddress","MailingListAddressGroup");
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		$MailingListAddressGroup=$hash[$i]["mailinglistaddressgroup"][0];
		for($t=0;$t<$hash[$i]["mailinglistaddress"]["count"];$t++){
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
				$this->virtual_alias_maps[]="$cn\t". implode(",",$final);
			}	
			
		unset($final);
		unset($mailinglistaddress);
		$MailingListAddressGroup=0;	
	
		
	}
	
	
	$filter="(&(objectClass=ArticaMailManRobots)(cn=*))";
	$attrs=array("cn","MailManAliasPath");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	$sock=new sockets();
	if($sock->GET_INFO("MailManEnabled")==1){$GLOBALS["MAILMAN"]=true;}else{$GLOBALS["MAILMAN"]=false;return;}	
	if($hash["count"]>0){$GLOBALS["MAILMAN"]=true;}else{$GLOBALS["MAILMAN"]=false;}	
	
	}
	
	private function MyHostname_SET(){
		$this->MAIN_ARRAY["myhostname"]=$this->MyHostname();
	}
	
	public function MyHostname(){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' AND `ou`='$this->ou' LIMIT 0,1;";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		writelogs("\"{$ligne["value"]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		if(trim($ligne["value"])==null){
			$users=new usersMenus();
			writelogs("FQDN=\"{$users->fqdn}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			return $users->fqdn;
		}
		return $ligne["value"];
	}
	
	private function ArticaDefault(){
		$this->MAIN_ARRAY["artica_destination_recipient_limit"]=1;
	}
	
	private function mynetworks(){
		$sql="SELECT * FROM postfix_multi WHERE `key`='mynetworks' AND `ou`='$this->ou' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[]=$ligne["value"];			
		}
		
		if(is_array($array)){
			$this->MAIN_ARRAY["mynetworks"]=implode(", ",$array);
		}
		
	}
	

	
	private function smtpd_client_restrictions_set(){
		
		$assp=new assp_multi($this->ou);
		if($assp->AsspEnabled==1){$ASSP_ENABLED=true;}
		
		$array=$this->smtpd_client_restrictions_get();
		//smtpd_client_restrictions
		//reject_unknown_client_hostname
		//reject_invalid_hostname
		//reject_unknown_reverse_client_hostname
		//reject_unknown_sender_domain
		//reject_non_fqdn_sender
		if(is_array($array)){
			$EnablePostfixAntispamPack=$array["EnablePostfixAntispamPack"];
			unset($array["EnablePostfixAntispamPack"]);
			
			while (list ($key, $line) = each ($array) ){
				if($line==1){$smtpd_recipient_restrictions[]=$key;}
			}
			
			if(is_array($smtpd_recipient_restrictions)){$smtpd_recipient_restrictions_line=",".implode(",",$smtpd_recipient_restrictions);}
		
		}
		
		if($EnablePostfixAntispamPack==1){
			$RBL[]="reject_rbl_client bl.spamcop.net";
	        $RBL[]="reject_rbl_client b.barracudacentral.org";
	        $RBL[]="reject_rbl_client zen.spamhaus.org";
	        $RBL[]="reject_rbl_client psbl.surriel.com";
	        $RBL[]="reject_rbl_client ix.dnsbl.manitu.net";
		}
		
		$sql="SELECT * FROM postfix_multi WHERE `key`='RBL' AND `ou`='$this->ou' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["value"]<>null){$RBL[]="reject_rbl_client ".$ligne["value"];}
		}
		
		if($ASSP_ENABLED){unset($RBL);}
		if(is_array($RBL)){
			$RBL_TEXT=",".implode(",",$RBL);
		}
		
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]="permit_mynetworks,permit_sasl_authenticated";
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]=$this->MAIN_ARRAY["smtpd_recipient_restrictions"].",reject_unauth_destination";
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]=$this->MAIN_ARRAY["smtpd_recipient_restrictions"].",check_client_access hash:/etc/postfix-$this->instance/postfix_allowed_connections";
		$this->MAIN_ARRAY["smtpd_recipient_restrictions"]=$this->MAIN_ARRAY["smtpd_recipient_restrictions"]."$smtpd_recipient_restrictions_line$RBL_TEXT,permit";
		}
	
	
	function smtpd_client_restrictions_get(){
		$sql="SELECT ValueTEXT FROM postfix_multi WHERE `key`='smtpd_client_restrictions' AND `ou`='$this->ou'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		return unserialize(base64_decode($ligne["ValueTEXT"]));
		
	}
	
	
	private function postfix_allowed_connections(){
		$sql="SELECT * FROM postfix_multi WHERE `key`='ip_white_listed' AND `ou`='{$_GET["ou"]}' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["value"]<>null){
				$arr[]="{$ligne["value"]}\t OK";
			}
			
		}
		
		if(!is_array($arr)){
			$conf="";
		}else{
			$conf=implode("\n",$arr);
		}
		
		@file_put_contents("/etc/postfix-$this->instance/postfix_allowed_connections",$conf);
		shell_exec("postmap /etc/postfix-$this->instance/postfix_allowed_connections");
		
	}
	
	
	
	
	private function BuildLdapSettings($ldap_key,$searchBaseWithoutSuffix=null,$queryfilter,$result_attribute,$special_result_attribute=null){
				$ldap=new clladp();
				if(trim($searchBaseWithoutSuffix)==null){$searchBaseWithoutSuffix="dc=organizations,$ldap->suffix";}
				$ldap_host=$ldap->ldap_host;
				$ldap_port=$ldap->ldap_port;
				if($ldap_host=='*'){$ldap_host='127.0.0.1';}
				if($ldap_port==null){$ldap_port='389';}
				
				
				$this->MAIN_ARRAY["{$ldap_key}_server_host"]="$ldap_host";
				$this->MAIN_ARRAY["{$ldap_key}_server_port"]="$ldap_port";
				$this->MAIN_ARRAY["{$ldap_key}_bind"]="yes";
				$this->MAIN_ARRAY["{$ldap_key}_bind_dn"]="cn=$ldap->ldap_admin,$ldap->suffix";
				$this->MAIN_ARRAY["{$ldap_key}_bind_pw"]="$ldap->ldap_password";
				$this->MAIN_ARRAY["{$ldap_key}_search_base"]="$searchBaseWithoutSuffix";
				$this->MAIN_ARRAY["{$ldap_key}_timeout"]="10";
				$this->MAIN_ARRAY["{$ldap_key}_query_filter"]="$queryfilter";
				$this->MAIN_ARRAY["{$ldap_key}_version"]="3";
				if($special_result_attribute<>null){
					$this->MAIN_ARRAY["{$ldap_key}_special_result_attribute"]="$special_result_attribute";
					 
				}
				$this->MAIN_ARRAY["{$ldap_key}_result_attribute"]="$result_attribute";	
				
	}

	private function CleanArray(){
		$this->CleanLdapArrayKeys('ldaprelcert');
		$this->CleanLdapArrayKeys('ConnectionCacheDestinations');
		$this->CleanLdapArrayKeys('mydestinationTable');
		$this->CleanLdapArrayKeys('RelaisDomainsTable');
		$this->CleanLdapArrayKeys('RelaisRecipientTable');
		$this->CleanLdapArrayKeys('VirtualAliasMapsTable');
		$this->CleanLdapArrayKeys('VirtualMailboxMapsTable');
		$this->CleanLdapArrayKeys('mynetworksTable');
		$this->CleanLdapArrayKeys('senderCanonicalTable');
		$this->CleanLdapArrayKeys('TransportMapsTable');
		$this->CleanLdapArrayKeys('TransportUsersTable');
		$this->CleanLdapArrayKeys('SmtpSaslPasswordMaps');
		$this->CleanLdapArrayKeys('VirtualMailManMaps');
		$this->CleanLdapArrayKeys('smtpd_client_restrictions_check_client_access');
		$this->CleanLdapArrayKeys('recipient_canonical_maps');
		$this->CleanLdapArrayKeys('MailBoxTransportMaps');
		$this->CleanLdapArrayKeys('TransportMailmanMaps');
		$this->CleanLdapArrayKeys('dynalistTable');
		$this->CleanLdapArrayKeys('RecipientBccMaps');
		$this->CleanLdapArrayKeys('AliasWhiteListTable');
		$this->CleanLdapArrayKeys('WhiteBlackTransport');
		$this->CleanLdapArrayKeys('WhiteBlackMaps');
		$this->CleanLdapArrayKeys('CatchAllAliasMaps');
		$this->CleanLdapArrayKeys('SenderDependentRelayhostMaps');
		$this->CleanLdapArrayKeys('UserDependentRelayhostMaps');
		$this->CleanLdapArrayKeys('SenderCanonicalPassword');
		$this->CleanLdapArrayKeys('SenderCanonicalDependentRelayhostMaps');
		$this->CleanLdapArrayKeys('SyncArticaMaps');
		unset($this->main_array["relay_clientcerts"]);
		unset($this->main_array["smtp_connection_cache_destinations"]);
		unset($this->main_array["relais_domain"]);
		unset($this->main_array["virtual_mailbox_maps"]);
		unset($this->main_array["alias_maps"]);
		unset($this->main_array["relay_recipient_maps"]);
		unset($this->main_array["mydestination"]);
		unset($this->main_array["sender_canonical_maps"]);
		unset($this->main_array["smtp_generic_maps"]);
		unset($this->main_array["transport_maps"]);
		unset($this->main_array["smtp_sasl_password_maps"]);
		unset($this->main_array["artica_destination_recipient_limit"]);
		unset($this->main_array["smtpd_helo_restrictions"]);
		unset($this->main_array["smtpd_recipient_restrictions"]);
		unset($this->main_array["smtpd_client_restrictions"]);
		unset($this->main_array["virtual_transport_maps"]);
		unset($this->main_array["virtual_alias_domains"]);
		unset($this->main_array["sender_dependent_relayhost_maps"]);
	}

	private function CleanLdapArrayKeys($key){
		unset($this->main_array["{$key}_server_host"]);
		unset($this->main_array["{$key}_server_port"]);
		unset($this->main_array["{$key}_bind"]);
		unset($this->main_array["{$key}_bind_dn"]);
		unset($this->main_array["{$key}_bind_pw"]);
		unset($this->main_array["{$key}_search_base"]);
		unset($this->main_array["{$key}_timeout"]);
		unset($this->main_array["{$key}_query_filter"]);
		unset($this->main_array["{$key}_version"]);
		unset($this->main_array["{$key}_result_attribute"]);
		unset($this->main_array["{$key}_special_result_attribute"]);
		}	
		
		
		
	private function BuildMasterCF(){
		$ASSP=new assp_multi($this->ou);
		
		$MASTER[]="# Postfix master process configuration file.  For details on the format";
		$MASTER[]="# of the file, see the master(5) manual page (command: \"man 5 master\").";
		$MASTER[]="#";
		$MASTER[]="# Do not forget to execute \"postfix reload\" after editing this file.";
		$MASTER[]="#";
		$MASTER[]="# ==========================================================================";
		$MASTER[]="# service type  private unpriv  chroot  wakeup  maxproc command + args";
		$MASTER[]="#               (yes)   (yes)   (yes)   (never) (100)";
		$MASTER[]="# ==========================================================================";
		
		if($ASSP->AsspEnabled==1){
			$MASTER[]="6000      inet  n       -       n       -       -       smtpd";
		}else{
			$MASTER[]="smtp      inet  n       -       n       -       -       smtpd";
		}
		$MASTER[]="submission inet n       -       n       -       -       smtpd";
		$MASTER[]="  -o smtpd_tls_security_level=encrypt";
		$MASTER[]="  -o smtpd_sasl_auth_enable=yes";
		$MASTER[]="  -o smtpd_client_restrictions=permit_sasl_authenticated,reject";
		$MASTER[]="  -o milter_macro_daemon_name=ORIGINATING";
		$MASTER[]="smtps     inet  n       -       n       -       -       smtpd";
		$MASTER[]="  -o smtpd_tls_wrappermode=yes";
		$MASTER[]="  -o smtpd_sasl_auth_enable=yes";
		$MASTER[]="  -o smtpd_client_restrictions=permit_sasl_authenticated,reject";
		$MASTER[]="  -o milter_macro_daemon_name=ORIGINATING";
		$MASTER[]="628       inet  n       -       n       -       -       qmqpd";
		$MASTER[]="pickup    fifo  n       -       n       60      1       pickup";
		$MASTER[]="cleanup   unix  n       -       n       -       0       cleanup";
		$MASTER[]="qmgr      fifo  n       -       n       300     1       qmgr";
		$MASTER[]="#qmgr     fifo  n       -       n       300     1       oqmgr";
		$MASTER[]="tlsmgr    unix  -       -       n       1000?   1       tlsmgr";
		$MASTER[]="rewrite   unix  -       -       n       -       -       trivial-rewrite";
		$MASTER[]="bounce    unix  -       -       n       -       0       bounce";
		$MASTER[]="defer     unix  -       -       n       -       0       bounce";
		$MASTER[]="trace     unix  -       -       n       -       0       bounce";
		$MASTER[]="verify    unix  -       -       n       -       1       verify";
		$MASTER[]="flush     unix  n       -       n       1000?   0       flush";
		$MASTER[]="proxymap  unix  -       -       n       -       -       proxymap";
		$MASTER[]="proxywrite unix -       -       n       -       1       proxymap";
		$MASTER[]="smtp      unix  -       -       n       -       -       smtp";
		$MASTER[]="relay     unix  -       -       n       -       -       smtp";
		$MASTER[]="	-o smtp_fallback_relay=";
		$MASTER[]="        -o smtp_helo_timeout=5 -o smtp_connect_timeout=5";
		$MASTER[]="showq     unix  n       -       n       -       -       showq";
		$MASTER[]="error     unix  -       -       n       -       -       error";
		$MASTER[]="retry     unix  -       -       n       -       -       error";
		$MASTER[]="discard   unix  -       -       n       -       -       discard";
		$MASTER[]="local     unix  -       n       n       -       -       local";
		$MASTER[]="virtual   unix  -       n       n       -       -       virtual";
		$MASTER[]="lmtp      unix  -       -       n       -       -       lmtp";
		$MASTER[]="anvil     unix  -       -       n       -       1       anvil";
		$MASTER[]="scache    unix  -       -       n       -       1       scache";
		$MASTER[]="mailman   unix 	- 	n 	n 	- 	- 	pipe";
		$MASTER[]=" flags=FR user=mail:mail argv=/etc/mailman/postfix-to-mailman.py \${nexthop} \${mailbox}";
		$MASTER[]="artica-whitelist    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --white";
		$MASTER[]="artica-blacklist    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --black";
		$MASTER[]="artica-reportwbl    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --report";
		$MASTER[]="artica-reportquar    unix  -       n       n       -       -       pipe";
		$MASTER[]="  flags=F  user=mail argv=/usr/share/artica-postfix/bin/artica-whitelist -a \${nexthop} -s \${sender} --quarantines";
		$MASTER[]="artica-filter    unix  -       n       n       -       20       pipe";
		$MASTER[]="  flags=FOh  user=www-data argv=/usr/share/artica-postfix/exec.artica-filter.php -f \${sender} --  -s \${sender} -r \${recipient} -c \${client_address}";
		$MASTER[]="";
		return $MASTER;
		
		
	}
	
}
?>