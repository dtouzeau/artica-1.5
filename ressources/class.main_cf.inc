<?php

include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.ssl.certificate.inc');
include_once(dirname(__FILE__) . '/class.artica.inc');
include_once(dirname(__FILE__) . '/class.mimedefang.inc');
include_once(dirname(__FILE__) . '/class.tcpip.inc');
include_once(dirname(__FILE__) . '/class.maincf.multi.inc');
if(!class_exists("unix")){include_once('/usr/share/artica-postfix/framework/class.unix.inc');}



class sender_dependent_relayhost_maps{
	var $dn;
	var $sender_dependent_relayhost_maps_hash=array();
	var $last_error;
	function sender_dependent_relayhost_maps(){
		$this->verifyBranch();
		$this->loadTable();

		
	}
	
function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixStructuralClass';
		$upd["cn"]="Sender_Dependent_Relay_host_Maps";
		$ldap->ldap_add($this->dn,$upd);
	}
}	
	
	
	function LoadTable(){
		$ldap=new clladp();
		$arr1=array("SenderRelayHost");
		$sr =@ldap_search($ldap->ldap_connection,$this->dn,'(&(objectclass=senderDependentRelayhostMaps)(SenderRelayHost=*))',$arr1);
		if ($sr) {
			
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		
			for($i=0;$i<$hash["count"];$i++){
				$ligne=$hash[$i];
				$dn=$ligne["dn"];
				$server=$ligne['senderrelayhost'][0];
				if($dn<>null){
					if(preg_match('#cn=(.+?),cn=#',$dn,$re)){
						$xdom=$re[1];
						if($xdom<>null){
							if(!is_array($xdom)){
								$this->sender_dependent_relayhost_maps_hash[$xdom]=$server;
							}
						}
					}
				}
			}
			
		}
		return $this->sender_dependent_relayhost_maps_hash;
	}
	
	
function Add($domainMail,$relay){
			$ldap=new clladp();
			if($domainMail==null){return null;}
			
			if(strpos($domainMail,'@')==0){$domainMail="@$domainMail";}

				
			$update_array["SenderRelayHost"][0]=$relay;
			$new_dn="cn=$domainMail,cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
			
			if($this->sender_dependent_relayhost_maps_hash[$domainMail]<>null){
				$res=$ldap->Ldap_modify($new_dn,$update_array);
			}else {
				$update_array['cn'][0]=$domainMail;
				$update_array['objectClass'][0]='senderDependentRelayhostMaps';
				$update_array['objectClass'][1]='top';
				$res=$ldap->ldap_add($new_dn,$update_array);		
				}
				
$this->last_error=$ldap->ldap_last_error;	
return $res;		

}
}
			
			

//-------------------------------------------------------------------------------------------------------------------------------------		
	
class Routing{
	var $ou;
	var $ldap_error;
	
	function Routing($ou=null){
	}
	
function create_localdomain($ou=null,$domain){
		$ldap=new clladp();
		$dn="ou=$ou,dc=organizations,$ldap->suffix";
		
		$object=$ldap->getobjectClass($ou,$dn);
		if(!isset($object["domainRelatedObject"])){
			$update_array["objectClass"][]="domainRelatedObject";
		}
		
		$update_array["associatedDomain"]=$domain;
		$dn=$ldap->dn_escape($dn);	
		if(!$ldap->Ldap_add_mod($dn,$update_array)){
			$this->ldap_error=$ldap->ldap_last_error;
			return false;
		}
		return true;
}	
	

function create_relay_server($domain,$server,$ou){
	$ldap=new clladp();
	$tpl=new templates();
	$ou=$_GET["ou"];
	$upd=array();
	$dn="cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}
	$domain_name=trim($domain);
	$relayIP=$server;
	$relayPort=25;
	$mx="yes";
	
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";	
	
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='PostFixRelayDomains';
	$upd['objectClass'][1]='top';
	if(!$ldap->ldap_add($dn,$upd)){
		$this->ldap_error=$ldap->ldap_last_error;
		return false;
	}
	
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){
			$this->ldap_error=$ldap->ldap_last_error;
			return false;
		}
		unset($upd);		
		}	
	
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="@$domain_name";
	$upd['objectClass'][0]='PostfixRelayRecipientMaps';
	$upd['objectClass'][1]='top';
	if(!$ldap->ldap_add($dn,$upd)){
			$this->ldap_error=$ldap->ldap_last_error;
			return false;
		}		
	
	$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){
			$this->ldap_error=$ldap->ldap_last_error;
			return false;
		}
		unset($upd);		
		}	
		
if($relayIP<>null){
	if($mx=="no"){$relayIP="[$relayIP]";}
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]="relay:$relayIP:$relayPort";
	if(!$ldap->ldap_add($dn,$upd)){
			$this->ldap_error=$ldap->ldap_last_error;
			return false;
		}
	}
	return true;
}	
	
	
}

//-------------------------------------------------------------------------------------------------------------------------------------	
class smtp_sasl_password_maps{
	
	var $smtp_sasl_password_hash=array();
	var $ldap_infos;
	
		function smtp_sasl_password_maps(){
			$update_array=array();
			$ldap=new clladp();
			if(!$ldap->ExistsDN("cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix")){
				$update_array['cn'][0]='smtp_sasl_password_maps';
				$update_array['objectClass'][0]='PostFixStructuralClass';
				$update_array['objectClass'][1]='top';
				$ldap->ldap_add("cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix",$update_array);		
				}
			$arr=array("SmtpSaslPasswordString");
			$sr =@ldap_search($ldap->ldap_connection,"cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix",'(&(objectclass=PostfixSmtpSaslPaswordMaps)(cn=*))',$arr);
			if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				for($i=0;$i<$hash["count"];$i++){
					$sets=$hash[$i]["smtpsaslpasswordstring"][0];
					if(preg_match("#cn=(.+?),cn=smtp_sasl_password_maps,cn=artica#",$hash[$i]["dn"],$regs)){
						$res[$regs[1]]=$sets;
					}
				}
			}
			$this->smtp_sasl_password_hash=$res;
			
		}
		
		
		function add($server,$username,$password){
			$ldap=new clladp();
			$dn="cn=$server,cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
			writelogs("Update/add $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd=array();
			if($ldap->ExistsDN($dn)){
				$upd["SmtpSaslPasswordString"][]="$username:$password";
				$return=$ldap->Ldap_modify($dn,$upd);
				$this->ldap_infos=$ldap->ldap_last_error;
				if($return){
					$sock=new sockets();
					$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");	
				}else{
					writelogs("Error $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}
				return $return;
			}
			
		
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostfixSmtpSaslPaswordMaps';
			$upd["cn"][]="$server";
			$upd["SmtpSaslPasswordString"][]="$username:$password";
			if($ldap->ldap_add($dn,$upd)){
				$this->ldap_infos=$ldap->ldap_last_error;
				$sock=new sockets();
				$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");
				return true;}
			return false;
		}
		
		
		
		function delete($server){
			$xldap=new clladp();
			
			$dn="cn=$server,cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
			if(!$xldap->ExistsDN($dn)){
				writelogs("Error $dn does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return true;
			}
			if(!$xldap->ldap_delete($dn,true)){
					writelogs("Error $xldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->ldap_infos=$xldap->ldap_last_error;
					return false;
			}
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");
			
			return true;
			
		}		
		
	
	
}



class main_cf{
	var $myorigin;
	var $main_array;
	var $services_lists;
	var $main_full_array;
	var $array_mynetworks;
	var $array_inet_interfaces;
	var $array_field_tls_logging=array(null=>'{tls_log_field_0}',0=>'{tls_log_field_0}',1=>'{tls_log_field_1}',2=>'{tls_log_field_2}',3=>'{tls_log_field_3}',4=>'{tls_log_field_4}');
	var $filepath;
	var $main_cf_datas;
	var $array_hashs;
	var $array_field_relay_tls=array("none"=>"{none}","may"=>"{Opportunistic_TLS}","encrypt"=>"{Mandatory_TLS_encryption}","verify"=>"{Mandatory_TLS_verification}","secure"=>"{Secure_channel_TLS}");
	
	var $array_regexp;
	var $mail_version;
	var $SmtpdClientRestrictionsRules;
	var $SmtpdSenderRestrictionsRules;
	var $smtp_sasl_password_maps=false;
	var $smtp_sasl_password_datas;
	var $smtp_sasl_password_hash=array();
	var $smtpd_client_restrictions_table=array();
	var $smtpd_sender_restrictions_table=array();
	var $dnsrbl_database=array();
	var $PostFixBounceTemplate;
	var $bounce_templates=array();
	var $org_maincf;
	var $error=false;
	var $PostfixMiltersBehavior;
	var $broken_sasl_auth_clients="yes";
	var $smtpd_sasl_security_options="noanonymous";
	var $smtpd_sasl_local_domain=null;
	var $smtpd_sasl_authenticated_header="yes";
	var $smtpd_tls_security_level="may";
	var $smtpd_tls_auth_only="no";
	var $smtpd_tls_received_header="yes";
	
	function main_cf($noload=0){
				
		if(isset($GLOBALS["__CLASS-MAIN-CF"])){
			writelogs("Loading class postfix in cache",__CLASS__.'/'.__FUNCTION__,__FILE__);
			foreach (get_object_vars($GLOBALS["__CLASS-MAIN-CF"]) as $key => $value){$this->$key = $value;}return null;
		}

		if($noload==0){
			writepostfixlogs('**** initialize postfix class',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->loadLdapConfig();
			$this->LoadSaslOptions();
			writepostfixlogs('**** parse_conf',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->parse_conf();
			writepostfixlogs('**** Parse_hashs',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->Parse_hashs();
			writepostfixlogs('**** FillDefaults',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->FillDefaults();
			writepostfixlogs('**** parse_my_networks',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->parse_my_networks();
			writepostfixlogs('**** parse_inet_interfaces',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->parse_inet_interfaces();
			writepostfixlogs('**** ParseServicesNames',__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->ParseServicesNames();
			writepostfixlogs('**** initialize postfix end...',__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			
			$GLOBALS["__CLASS-MAIN-CF"]=$this;
			
			
			}
		}
		
		
	private function LoadSaslOptions(){
	$sock=new sockets();
	$this->broken_sasl_auth_clients=$sock->GET_INFO("broken_sasl_auth_clients");
	$this->smtpd_sasl_security_options=$sock->GET_INFO("smtpd_sasl_security_options");
	$this->smtpd_sasl_local_domain=$sock->GET_INFO("smtpd_sasl_local_domain");
	$this->smtpd_sasl_authenticated_header=$sock->GET_INFO("smtpd_sasl_authenticated_header");
	$this->smtpd_tls_security_level=$sock->GET_INFO("smtpd_tls_security_level");
	$this->smtpd_tls_auth_only=$sock->GET_INFO("smtpd_tls_auth_only");
	$this->smtpd_tls_received_header=$sock->GET_INFO("smtpd_tls_received_header");
	
	
	
	
	if($this->smtpd_sasl_security_options==null){$this->smtpd_sasl_security_options="noanonymous";}
	if($this->broken_sasl_auth_clients==null){$this->broken_sasl_auth_clients="yes";}
	if($this->smtpd_sasl_authenticated_header==null){$this->smtpd_sasl_authenticated_header="yes";}
	if($this->smtpd_tls_security_level==null){$this->smtpd_tls_security_level="may";}
	if($this->smtpd_tls_auth_only==null){$this->smtpd_tls_auth_only="no";}
	if($this->smtpd_tls_received_header==null){$this->smtpd_tls_received_header="yes";}					
		
	}
	
	function HashGetHeadersList(){
		$datas=file_get_contents(dirname(__FILE__).'/databases/db.headers.txt');
		return explode("\n",$datas);
	}
	
	function fix_relayhost(){
		$relay=trim($this->main_array["relayhost"]);
		if($relay==null){return null;}
		if(preg_match('#^(smtp|relay):(.+)#',$relay,$re)){
			$this->main_array["relayhost"]=$re[2];
		}
		
		if(preg_match("#(.+?):(.+)$#",$this->main_array["relayhost"],$re)){
			if(strpos($this->main_array["relayhost"],']')==0){
				$this->main_array["relayhost"]="[{$re[1]}]:{$re[2]}";
			}
		}
		
	}
	
	

	function check_sender_access(){
		$sock=new sockets();
		$users=new usersMenus();
		if(!$users->POSTFIX_INSTALLED){return null;}
		$tpl=new templates();
		include_once(dirname(__FILE__) . '/class.templates.inc');
		if($sock->GET_INFO("EnableBlockUsersTroughInternet")<>1){return null;}
		
		$ldap=new clladp();
		$attr=array("mail","AllowedSMTPTroughtInternet");
		$pattern="(&(objectclass=userAccount)(AllowedSMTPTroughtInternet=*))";
		$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$pattern,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$AllowedSMTPTroughtInternet=$hash[$i][strtolower("AllowedSMTPTroughtInternet")][0];
			if($AllowedSMTPTroughtInternet==0){
				$conf=$conf."{$hash[$i]["mail"][0]}\tauth_relay\n";	
			}else{
				$conf=$conf."{$hash[$i]["mail"][0]}\tOK\n";
			}
		}
		$usersnumber=$hash["count"];
		
		$hash=$ldap->hash_get_all_domains();
		while (list ($num, $ligne) = each ($hash) ){
			if(trim($num)==null){continue;}
			$domains=$domains ."$num\t OK\n";
		}
		$sock->SaveConfigFile($domains,"unrestricted_senders_domains");
		$sock->SaveConfigFile($conf,"unrestricted_senders");
		return $usersnumber;
		}

	
	function smtp_sasl_password_maps_enable(){
		$this->main_array["smtp_sasl_auth_enable"]='yes';
		$this->main_array["smtp_sasl_exceptions_networks"]='$mynetworks';
		$this->save_conf();
		}

	function smtp_sasl_password_maps_enable_2(){
		$this->smtp_sasl_password_maps_enable();
	}


		
	function smtp_sasl_password_maps_disable(){
		unset($this->main_array["smtp_sasl_auth_enable"]);
		unset($this->main_array["smtp_sasl_password_maps"]);
		unset($this->main_array["smtp_sasl_mechanism_filter"]);
		unset($this->main_array["smtp_sasl_exceptions_networks"]);
		$this->save_conf();
		}	
		
		
	private function _load_ldap_main(){
		$sock=new sockets();
		
		if(posix_getuid()==0){
			$this->PostfixMiltersBehavior=$sock->GET_INFO("PostfixMiltersBehavior");
			if($this->PostfixMiltersBehavior==null){$this->PostfixMiltersBehavior=1;}
			$datas=@file_get_contents("/etc/postfix/main.cf");
			$this->org_maincf=$datas;
			$this->main_cf_datas=$datas;
			return $datas;
			}
		
		
		$this->PostfixMiltersBehavior=$sock->GET_INFO("PostfixMiltersBehavior");
		$datas=trim($sock->GET_INFO("PostfixMainCfFile"));
		
		writepostfixlogs("PostfixMainCfFile=".strlen($datas)." bytes",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(strlen(trim($datas))==0){
				writepostfixlogs("it's seems that main.cf content is not saved in \"PostfixMainCfFile\" attribute.",__CLASS__.'/'.__FUNCTION__,__FILE__);
				writepostfixlogs("I'm getting content directly on the disk",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$datas=$this->GetLocalMainCf();
				if(strlen($datas)==0){
					writepostfixlogs("I'm unable to main.cf directly from disk, there is a real problem...",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}
			}
			$this->org_maincf=$datas;
			$this->main_cf_datas=$datas;
			if($this->PostfixMiltersBehavior==null){$this->PostfixMiltersBehavior=1;}
			return $datas;
			}
			
			
	public function CheckMyNetwork(){		
		if(!is_array($this->array_mynetworks)){
				writepostfixlogs('main->array_mynetworks is not an array, seems that postfix has no network set',
				__CLASS__.'/'.__FUNCTION__,__FILE__);
				return false;
			}
			
			while (list ($num, $ligne) = each ($this->array_mynetworks) ){
				writepostfixlogs("main->array_mynetworks[$num]=$ligne",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if($ligne==null){continue;}
				if($ligne<>"127.0.0.1"){
					$rr[]=$ligne;
				}
			}
			
			if(count($rr)==0){
				writepostfixlogs('rr is not an array, seems that postfix has no network set',__FUNCTION__,__FILE__);				
				return false;
			}		
		
		writepostfixlogs("return success, networks set",__CLASS__.'/'.__FUNCTION__,__FILE__);	
		return true;
	}
	
	
private function loadLdapConfig(){
		$ldap=new clladp();
	
		$this->_load_ldap_main();
		$hash=$ldap->ArticaDatas();
		$this->smtp_sasl_password_datas=$hash["PostfixHashSmtpSaslAuth"];
		$sock=new sockets();
		$this->PostFixBounceTemplate=$sock->GET_INFO("PostfixBounceTemplateFile");
		$this->ParseBounceTemplates();
		
		}
		
		
		function HashGetMasterCfServices(){
			include_once(dirname(__FILE__). '/class.sockets.inc');
			$sockets=new sockets();
			$file=$sockets->getfile('master.cf');
			if(preg_match_all('#([a-z0-9]+)\s+(unix|fifo|inet)#is',$file,$regs)){
				while (list ($num, $val) = each ($regs[1]) ){
					if(!is_numeric($val)){
						if($val<>'smtp'){
							$hash[$val]=$val;
						}
					}
				}
			}
			
			return $hash;
		}
	
	
	
	function ParseBounceTemplates(){
		$arr=explode("\n",$this->PostFixBounceTemplate);
		if(count($arr)>1000){
			writelogs("PostFixBounceTemplate corrupted",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->PostFixBounceTemplate=file_get_contents(dirname(__FILE__). "/databases/bounce.template.default.cf");
			$sock=new sockets();
			$sock->SET_INFO("PostfixBounceTemplateFile",$this->PostFixBounceTemplate);
		}
		
		
		
		if($this->PostFixBounceTemplate==null){
			$this->PostFixBounceTemplate=file_get_contents(dirname(__FILE__). "/databases/bounce.template.default.cf");
		}
		
		
		
		if(preg_match_all('#([a-z\_]+) = <<EOF\s+(.+?)\s+EOF#is',$this->PostFixBounceTemplate,$regs)){
			while (list ($num, $val) = each ($regs[1]) ){
				$this->bounce_templates[$val]=$regs[2][$num];
			}
		}
	}
	
	function save_bounce_templates(){
	if(!is_array($this->bounce_templates)){
		unset($this->main_array["bounce_template_file"]);
		return null;
	}
	while (list ($num, $val) = each ($this->bounce_templates) ){$text=$text . "$num = <<EOF\n$val\nEOF\n";}
		$this->main_array["bounce_template_file"]="/etc/postfix/bounce.template.cf";
		return $text;
	}
		

	
	private function _readfile($filepath){
		$file = @fopen($filepath, "r");
		if ($file<>null){
			return @fread($file,filesize($filepath));
			fclose($file); 
			exit;
		}
		
	}
	
	private function GetLocalMainCf(){
		$sock=new sockets();
		return $sock->getFrameWork('cmd.php?getMainCF=yes');
		}	
	
	private function ImportToLdapMainCf(){
		$this->main_cf_datas=$this->GetLocalMainCf();
		}
	
	
	private function parse_inet_interfaces(){
		$inet_interfaces=$this->main_array["inet_interfaces"];
		writepostfixlogs("inet_interfaces data -> $inet_interfaces",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->array_inet_interfaces=explode(',',$inet_interfaces);
	}
	
	function add_my_networks($network){
		$upd=array();
		writepostfixlogs("#$network#",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$ldap=new clladp();
		$this->Create_mynetworks_maps_ldap();
		$dn="cn=mynetworks_maps,cn=artica,$ldap->suffix";
		$this->array_mynetworks[]=$network;
		writepostfixlogs(implode(",",$this->array_mynetworks),__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list ($num, $val) = each ($this->array_mynetworks) ){
			$upd["mynetworks"][]=$val;
		}
		
		if($ldap->Ldap_modify($dn,$upd)){return $ldap->ldap_last_error;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-networks=yes");
		
	}
	function delete_my_networks($index){
		$ldap=new clladp();
		$dn="cn=mynetworks_maps,cn=artica,$ldap->suffix";
		writepostfixlogs("Delete index $index \"{$this->array_mynetworks[$index]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__);	
		unset($this->array_mynetworks[$index]);
		if(count($this->array_mynetworks)==0){$this->array_mynetworks[]="127.0.0.0/24";}
		
		while (list ($num, $val) = each ($this->array_mynetworks) ){
			$upd["mynetworks"][]=$val;
		}
		
		$ldap->Ldap_modify($dn,$upd);
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-networks=yes");	
	}

private	function Create_mynetworks_maps_ldap(){
		$ldap=new clladp();
		if(!$ldap->ExistsDN("cn=artica,$ldap->suffix")){$ldap->ArticaCreate();}
		$dn="cn=mynetworks_maps,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			writepostfixlogs("Create the new branch $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			$upd["cn"][0]="mynetworks_maps";
			$upd["ObjectClass"][]="top";
			$upd["ObjectClass"][]="PostfixMynetworks";
			$ldap->ldap_add($dn,$upd);
			}
		
	}
	
	
 private function parse_my_networks(){
		include_once(dirname(__FILE__) . '/class.ldap.inc');
		$ldap=new clladp();
		$this->Create_mynetworks_maps_ldap();
		$Nets=$ldap->load_mynetworks();
		if(!is_array($Nets)){
			$this->build_default_my_networks();
			$Nets=$ldap->load_mynetworks();
			}
		$this->array_mynetworks=$Nets;
		}
		
private function build_default_my_networks(){
	
	
	include_once(dirname(__FILE__).'/class.system.network.inc');
	writepostfixlogs("Get current interfaces...",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$interfaces=new networking();
	if(!is_array($interfaces->array_TCP)){
		writepostfixlogs("There is a real problem getting interfaces eth0,eth1... using articcgi...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		writepostfixlogs("is framework web server is loaded ???",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return null;
	}
	
	$ip=new IP();
	while (list ($num, $val) = each ($interfaces->array_TCP) ){
		if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$val,$re)){continue;}
		$ipfirst="{$re[1]}.{$re[2]}.{$re[3]}.0";
		$cdir=$ip->ip2cidr($ipfirst,"{$re[1]}.{$re[2]}.{$re[3]}.255");
		writepostfixlogs("Scanning $num for $val ($cdir)",__CLASS__.'/'.__FUNCTION__,__FILE__);
	    if($cdir){
	    	$this->add_my_networks($cdir);
	    	$newCDIR[]=$cdir;
	    }
	    
	}
	if(is_array($newCDIR)){
		$newCDIR[]="127.0.0.0/8";
		$hash[0]["mynetworks"]["count"]=count($newCDIR);
		while (list ($num, $val) = each ($newCDIR) ){
			
			$hash[0]["mynetworks"][]=$val;
		}
	
	return $hash;}
	
	
}
		

	
	function parse_conf(){
		$datas=$this->main_cf_datas;	
		$conf=explode("\n",$datas);
		writepostfixlogs('receive from postconf ' . count($conf) . " parameters",__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list ($num, $val) = each ($conf) ){
		if(preg_match('#(.+?)=(.+)#',$val,$regs)){
			$this->main_array[trim($regs[1])]=trim($regs[2]);
			$this->main_full_array[trim($regs[1])]=trim($regs[2]);
			}
		}
	}

	function FillDefaults(){
		
		if($this->main_array["bounce_service_name"]==null){$this->main_array["bounce_service_name"]='bounce';}
		if($this->main_array["bounce_size_limit"]==null){$this->main_array["bounce_size_limit"]='50000';}
		if($this->main_array["bounce_notice_recipient"]==null){$this->main_array["bounce_notice_recipient"]='postmaster';}
		if($this->main_array["double_bounce_sender"]==null){$this->main_array["double_bounce_sender"]='double-bounce';}
		if($this->main_array["message_size_limit"]==null){$this->main_array["message_size_limit"]='10240000';}
		if($this->main_array["mime_nesting_limit"]==null){$this->main_array["mime_nesting_limit"]='100';}
		if($this->main_array["header_address_token_limit"]==null){$this->main_array["header_address_token_limit"]='10240';}
		if($this->main_array["smtpd_reject_unlisted_recipient"]==null){$this->main_array["smtpd_reject_unlisted_recipient"]="yes";}
		if($this->main_array["smtp_connection_cache_on_demand"]==null){$this->main_array["smtp_connection_cache_on_demand"]="yes";}
		if($this->main_array["smtp_connection_cache_time_limit"]==null){$this->main_array["smtp_connection_cache_time_limit"]="2s";}
		if($this->main_array["smtp_connection_reuse_time_limit"]==null){$this->main_array["smtp_connection_reuse_time_limit"]="300s";}
		if($this->main_array["connection_cache_ttl_limit"]==null){$this->main_array["connection_cache_ttl_limit"]="2s";}
		if($this->main_array["connection_cache_status_update_time"]==null){$this->main_array["connection_cache_status_update_time"]="600s";}
		if($this->main_array["address_verify_sender"]==null){$this->main_array["address_verify_sender"]=$this->main_array["double_bounce_sender"];}
		if($this->main_array["address_verify_negative_cache"]==null){$this->main_array["address_verify_negative_cache"]="yes";}
		if($this->main_array["address_verify_negative_expire_time"]==null){$this->main_array["address_verify_negative_expire_time"]="3d";}
		if($this->main_array["address_verify_negative_refresh_time"]==null){$this->main_array["address_verify_negative_refresh_time"]="3h";}
		if($this->main_array["address_verify_poll_count"]==null){$this->main_array["address_verify_poll_count"]="3";}
		if($this->main_array["address_verify_poll_delay"]==null){$this->main_array["address_verify_poll_delay"]="3s";}
		if($this->main_array["address_verify_positive_expire_time"]==null){$this->main_array["address_verify_positive_expire_time"]="31d";}
		if($this->main_array["address_verify_positive_refresh_time"]==null){$this->main_array["address_verify_positive_refresh_time"]="7d";}
		
		if($this->main_array["virtual_mailbox_limit"]==null){$this->main_array["virtual_mailbox_limit"]=$this->main_array["message_size_limit"];}
		
		//Postfix Performance Tuning
		 
		if($this->main_array["smtpd_error_sleep_time"]==null){$this->main_array["smtpd_error_sleep_time"]="1s";}
		if($this->main_array["smtpd_soft_error_limit"]==null){$this->main_array["smtpd_soft_error_limit"]="10";}
		if($this->main_array["smtpd_hard_error_limit"]==null){$this->main_array["smtpd_hard_error_limit"]="20";}
		if($this->main_array["smtpd_client_connection_count_limit"]==null){$this->main_array["smtpd_client_connection_count_limit"]="50";}
		if($this->main_array["smtpd_client_connection_rate_limit"]==null){$this->main_array["smtpd_client_connection_rate_limit"]="0";}
		if($this->main_array["smtpd_client_message_rate_limit"]==null){$this->main_array["smtpd_client_message_rate_limit"]="0";}
		if($this->main_array["smtpd_client_recipient_rate_limit"]==null){$this->main_array["smtpd_client_recipient_rate_limit"]="0";}
		if($this->main_array["smtpd_client_new_tls_session_rate_limit"]==null){$this->main_array["smtpd_client_new_tls_session_rate_limit"]="0";}
		if($this->main_array["smtpd_client_event_limit_exceptions"]==null){$this->main_array["smtpd_client_event_limit_exceptions"]='$mynetworks';}
		if($this->main_array["in_flow_delay"]==null){$this->main_array["in_flow_delay"]='1s';}
		if($this->main_array["smtp_helo_timeout"]==null){$this->main_array["smtp_helo_timeout"]='300s';}
		if($this->main_array["smtp_connect_timeout"]==null){$this->main_array["smtp_connect_timeout"]='30s';}
		if($this->main_array["default_destination_recipient_limit"]==null){$this->main_array["default_destination_recipient_limit"]='50';}
		if($this->main_array["smtpd_recipient_limit"]==null){$this->main_array["smtpd_recipient_limit"]='1000';}
		
		if($this->main_array["queue_run_delay"]==null){$this->main_array["queue_run_delay"]='300s';}
		if($this->main_array["minimal_backoff_time"]==null){$this->main_array["minimal_backoff_time"]='300s';}
		if($this->main_array["maximal_backoff_time"]==null){$this->main_array["maximal_backoff_time"]='4000s';}
		if($this->main_array["maximal_queue_lifetime"]==null){$this->main_array["maximal_queue_lifetime"]='5d';}
		if($this->main_array["bounce_queue_lifetime"]==null){$this->main_array["bounce_queue_lifetime"]='5d';}
		if($this->main_array["qmgr_message_recipient_limit"]==null){$this->main_array["qmgr_message_recipient_limit"]='20000';}
		if($this->main_array["qmgr_message_recipient_minimum"]==null){$this->main_array["qmgr_message_recipient_minimum"]='10';}
		if($this->main_array["default_process_limit"]==null){$this->main_array["default_process_limit"]='100';}
		if($this->main_array["myorigin"]==null){$this->main_array["myorigin"]='$mydomain';}
		if($this->main_array["smtpd_timeout"]==null){$this->main_array["smtpd_timeout"]='300';}
		if($this->main_array["enable_original_recipient"]==null){$this->main_array["enable_original_recipient"]='yes';}
		
		
		
		  
		
		if($this->main_array["ignore_mx_lookup_error"]==null){$this->main_array["ignore_mx_lookup_error"]='no';}
		if($this->main_array["disable_dns_lookups"]==null){$this->main_array["disable_dns_lookups"]='no';}
		
		if($this->main_array["smtpd_tls_ask_ccert"]==null){$this->main_array["smtpd_tls_ask_ccert"]='no';}
		if($this->main_array["smtpd_tls_security_level"]==null){$this->main_array["smtpd_tls_security_level"]='none';}
		if($this->main_array["smtpd_tls_req_ccert"]==null){$this->main_array["smtpd_tls_req_ccert"]='no';}
		if($this->main_array["smtpd_tls_received_header"]==null){$this->main_array["smtpd_tls_received_header"]='no';}
		
		if($this->main_array["smtp_sender_dependent_authentication"]==null){$this->main_array["smtp_sender_dependent_authentication"]='no';}		
		
		if($this->main_array["undisclosed_recipients_header"]==null){$this->main_array["undisclosed_recipients_header"]="To: undisclosed-recipients:;";}
		if($this->main_array["initial_destination_concurrency"]==null){$this->main_array["initial_destination_concurrency"]='5';}
		if($this->main_array["default_destination_concurrency_limit"]==null){$this->main_array["default_destination_concurrency_limit"]='20';}
		if($this->main_array["local_destination_concurrency_limit"]==null){$this->main_array["local_destination_concurrency_limit"]='2';}
		if($this->main_array["smtp_destination_concurrency_limit"]==null){$this->main_array["smtp_destination_concurrency_limit"]='$default_destination_concurrency_limit';}
		if($this->main_array["smtp_tls_mandatory_protocols"]==null){$this->main_array["smtp_tls_mandatory_protocols"]='SSLv3,TLSv1';}
		
		
		if($this->main_array["mailbox_size_limit"]<$this->main_array["message_size_limit"]){$this->main_array["mailbox_size_limit"]=$this->main_array["message_size_limit"];}
		
		if($this->main_array["virtual_transport_maps"]==null){$this->main_array["virtual_transport_maps"]='ldap:/etc/postfix/ldap-virtual_transport_maps.cf';}
		if($this->main_array["transport_maps"]==null){$this->main_array["transport_maps"]='ldap:/etc/postfix/ldap-virtual_transport_maps.cf';}
		$this->main_array["virtual_mailbox_limit"]=$this->main_array["message_size_limit"];
		
		if($this->main_array["double_bounce_sender"]==null){$this->main_array["double_bounce_sender"]='double-bounce';}
		if($this->main_array["2bounce_notice_recipient"]==null){$this->main_array["2bounce_notice_recipient"]='postmaster';}
		if($this->main_array["error_notice_recipient"]==null){$this->main_array["error_notice_recipient"]='postmaster';}
		if($this->main_array["delay_notice_recipient"]==null){$this->main_array["delay_notice_recipient"]='postmaster';}
		if($this->main_array["address_verify_sender"]==null){$this->main_array["address_verify_sender"]='double-bounce';}
		if($this->main_array["empty_address_recipient"]==null){$this->main_array["empty_address_recipient"]='MAILER-DAEMON';}
		if($this->main_array["smtp_send_xforward_command"]==null){$this->main_array["smtp_send_xforward_command"]='yes';}
		if($this->main_array["smtpd_authorized_xforward_hosts"]==null){$this->main_array["smtpd_authorized_xforward_hosts"]='$mynetworks';}
		if($this->main_array["smtpd_sasl_path"]==null){$this->main_array["smtpd_sasl_path"]="smtpd";}
		if(strlen($this->main_array["smtpd_tls_session_cache_timeout"]>8)){$this->main_array["smtpd_tls_session_cache_timeout"]="3600s";}
		$this->main_array["smtp_tls_session_cache_database"]="btree:".'/var/lib/postfix/smtpd_tls_session_cache';
		if($this->main_array["virtual_uid_maps"]==null){$this->main_array["virtual_uid_maps"]='static:5000';}
		if($this->main_array["virtual_gid_maps"]==null){$this->main_array["virtual_gid_maps"]='static:5000';}
		if($this->main_array["myhostname"]==null){
			writepostfixlogs("Get default hostname for myhostname value",__CLASS__.'/'.__FUNCTION__,__FILE__);
			include_once(dirname(__FILE__).'/class.system.network.inc');
			$nic=new networking();
			$this->main_array["myhostname"]=$nic->hostname;
			writepostfixlogs("myhostname=$nic->hostname",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
			
			
		$sock=new sockets();
		if($this->main_array["relayhost"]==null){
			$this->main_array["relayhost"]=$sock->GET_INFO("PostfixRelayHost");
		}

		$main=new maincf_multi("master","master");
		$in_flow_delay=$main->GET("in_flow_delay");
		if($in_flow_delay<>null){$this->main_array["in_flow_delay"]=$in_flow_delay;}
		
	}
	
	function BuildDefaultWhiteListRobots(){
	$ldap=new clladp();
	$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd['cn'][0]="PostfixRobots";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
			unset($upd);		
		}
		
		
	$dn="cn=catch-all,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
			if(!$ldap->ExistsDN($dn)){
			$upd['cn'][0]="catch-all";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return null;}
			unset($upd);		
		}
		
	}		

	}
	

	
	function Parse_hashs(){
		
		if(strlen($this->main_cf_datas)==0){
			writepostfixlogs("WARNING: main_cf_datas is empty !!",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return null;
		}
			
		if(preg_match_all('#([a-z_]+)[\s=]+regexp:([a-z0-9A-Z\/_\.]+)#is',$this->main_cf_datas,$regs)){
			while (list ($num, $val) = each ($regs[1]) ){
				$this->array_regexp[$val]=$regs[2][$num];
				}
			}
			
		}
		

private function CleanMyNetworks(){
		if(!is_array($this->array_mynetworks)){return null;}
		reset($this->array_mynetworks);
		while (list ($num, $val) = each ($this->array_mynetworks) ){$res[$val]=$val;}
		unset($this->array_mynetworks);
		while (list ($num, $val) = each ($res) ){
				$this->array_mynetworks[]=$val;
			}
	}
	
	
	function relay_clientcerts_conf(){
		$c=new relay_clientcerts();
		$hash=$c->ParseTable();
		if(count($hash)==0){
			writepostfixlogs("relay_clientcerts: no datas.. ",__CLASS__ . '/' . __FUNCTION__,__FILE__);
			return null;	
		}
		$ldap=new clladp();
		writepostfixlogs("relay_clientcerts: Building LDAP settings... ",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		$conf=$conf. "\n#LDAP relay_clientcerts --------------------------------------------------------------------\n";
		$conf=$conf . "relay_clientcerts = ldap:ldaprelcert\n\n";
		$conf=$conf . "ldaprelcert_server_host = $ldap->ldap_host\n";
		$conf=$conf . "ldaprelcert_server_port = $ldap->ldap_port\n";
		$conf=$conf . "ldaprelcert_bind = yes\n";
		$conf=$conf . "ldaprelcert_bind_dn =cn=$ldap->ldap_admin,$ldap->suffix\n";
	    $conf=$conf . "ldaprelcert_bind_pw =$ldap->ldap_password\n";
		$conf=$conf . "ldaprelcert_timeout = 10\n";
		$conf=$conf . "ldaprelcert_search_base = cn=relay_clientcerts_maps,cn=artica,$ldap->suffix\n";
		$conf=$conf . "ldaprelcert_query_filter = (&(objectclass=PostfixRelayClientCerts)(cn=%s))\n";
		$conf=$conf . "ldaprelcert_result_attribute = relayClientcert\n";
		$conf=$conf . "ldaprelcert_version =3\n\n";
		$conf=$conf. "#-------------------------------------------------------------------------------------------\n\n";
		return $conf;
		
	}	
	
	function CleanArray(){
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
		$this->CleanLdapArrayKeys('ArticaSynMapsTable');
		$this->CleanLdapArrayKeys('VirtualAliasMailingTable');
		$this->CleanLdapArrayKeys('AliasMapsTable');
		$this->CleanLdapArrayKeys('VirtualAliasMapsTable');
		$this->CleanLdapArrayKeys('RecipientCanonicalTable');
		
		
		
		
		 
		
		
		
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
	
	function CleanLdapArrayKeys($key){
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
	
		
	function save_conf(){
		writepostfixlogs("Save main.cf configuration in LDAP ############################################");
		$this->CleanMyNetworks();
		$this->BuildDefaultWhiteListRobots();
		if(is_array($this->array_mynetworks)){
			$nets=implode(',',$this->array_mynetworks);
			writepostfixlogs("mynetworks=$nets",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->main_array["mynetworks"]=implode(',',$this->array_mynetworks);
			}
		$this->main_array["inet_interfaces"]=implode(',',$this->array_inet_interfaces);
		writepostfixlogs("inet_interfaces=" . $this->main_array["inet_interfaces"],__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(trim($this->main_array["inet_interfaces"])==null){$this->main_array["inet_interfaces"]='$myhostname, localhost';}
		
		$PostFixBounceTemplate=$this->save_bounce_templates();
		$this->header_checks();
		$this->mailbox_transport();
		$this->FillDefaults();
		$this->CleanArray();
		$this->cyrus_transport();
		$this->fix_relayhost();
		
		$sock=new sockets();
		if($sock->GET_INFO("EnableAmavisInMasterCF")==1){
			writepostfixlogs("Change to pre-queue mode amavis rows",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			$this->main_array["content_filter"] = "amavis:[127.0.0.1]:10024";
			$this->main_array["receive_override_options"] = "no_address_mappings";
					
		}else{
			$this->main_array["content_filter"]="";
			$this->main_array["receive_override_options"] = "";
		}
		
		if($sock->GET_INFO("EnablePostfixAntispamPack")==1){
			$this->main_array["smtpd_error_sleep_time"]="1s";
			$this->main_array["smtpd_soft_error_limit"]="10";
			$this->main_array["smtpd_hard_error_limit"]="20";
			$this->main_array["disable_vrfy_command"]="yes";
			$this->main_array["smtpd_delay_reject"]="yes";
			$this->main_array["smtpd_helo_required"]="yes";
			}
		
		
		
		writepostfixlogs("main array count " . count($this->main_array) . ' rows',__CLASS__.'/'.__FUNCTION__,__FILE__);
		reset($this->main_array);
		while (list ($num, $val) = each ($this->main_array) ){
			 $conf=$conf . "$num=$val\n";
			}
		
		$this->main_cf_datas=$conf;
		$this->_SaveMainCf($conf);


		if($PostFixBounceTemplate<>null){
			$sock=new sockets();
			$sock->SaveConfigFile($PostFixBounceTemplate,'PostfixBounceTemplateFile');
			writepostfixlogs("PostFixBounceTemplate " . strlen($PostFixBounceTemplate) . " lenght",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
		
			writepostfixlogs("Save Postfix settings into Artica file storage",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		
				
	}
	
	
	function header_checks(){
		include_once(dirname(__FILE__) . "/class.main_cf_filtering.inc");
		$headers=new main_header_check();
		writepostfixlogs("number of rules of header_checks:" . count($headers->main_table),__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(is_array($headers->main_table)){
			$this->main_array["header_checks"]="regexp:/etc/postfix/hash_files/header_checks.cf";
		}else{
			unset($this->main_array["header_checks"]);
		}
		
	}
	
	function cyrus_transport(){
		$user=new usersMenus();
		if($user->cyrus_imapd_installed){
			$unix=new unix();
			
			if($user->cyrus_lmtp_path==null){
				$queue_directory=$unix->POSTCONF_GET("queue_directory");
				$user->cyrus_lmtp_path="$queue_directory/var/run/cyrus/socket/lmtp";
			}
			
			$this->main_array["mailbox_transport"]="lmtp:unix:$user->cyrus_lmtp_path";
			$this->main_array["virtual_transport"]="\$mailbox_transport";
		}
		
	}
	
	
	function mailbox_transport(){
		$user=new usersMenus();
		
		unset($this->main_array["virtual_alias_maps"]);
		if($user->procmail_installed==true){
		$this->main_array["mailbox_transport"]="procmail";
		$this->main_array["virtual_transport"]="procmail";		
		$this->main_array["mailbox_command"]="/usr/bin/procmail -t -a \"$"."EXTENSION\"";
		}else{
			unset($this->main_array["mailbox_command"]);
			if($p->cyrus_imapd_installed==true){
				$this->main_array["mailbox_transport"]="lmtp:unix:$user->cyrus_lmtp_path";
				$this->main_array["virtual_transport"]="\$mailbox_transport";
			}
		}
	}
	
	function DisableTLS(){
		$this->main_array["smtpd_use_tls"]='no';
		$this->save_conf();		
	}
	
	function EnableTLS(){
		$cert=new ssl_certificate();
		if($this->main_array["smtpd_tls_security_level"]<>'none'){
			$keys_dir=$cert->array_ssl["default_db"]["dir"];
			$this->main_array["smtpd_tls_key_file"]=$keys_dir . '/' . $cert->array_ssl["postfix"]["smtpd_tls_key_file"];
			$this->main_array["smtpd_tls_cert_file"]=$keys_dir . '/' . $cert->array_ssl["postfix"]["smtpd_tls_cert_file"];
			$this->main_array["smtpd_tls_CAfile"]=$keys_dir . '/' . $cert->array_ssl["postfix"]["smtpd_tls_CAfile"];
			$this->main_array["smtpd_use_tls"]="yes";
			$this->main_array["smtpd_tls_session_cache_timeout"]="3600s";
			$this->main_array["smtpd_tls_session_cache_database"]="btree:\$data_directory/smtpd_tls_cache ";
			$this->main_array["broken_sasl_auth_clients"]="yes";
			$this->main_array["smtpd_tls_received_header"] = "yes";
			$this->main_array["smtp_tls_note_starttls_offer"]="yes"; 
			$this->main_array["tls_random_exchange_name"]='/var/lib/postfix/prng';
			$this->main_array["tls_random_source"]="dev:/dev/urandom";
			$this->save_conf();
			}else{
				unset($this->main_array["smtpd_tls_key_file"]);
				unset($this->main_array["smtpd_tls_cert_file"]);
				unset($this->main_array["smtpd_tls_CAfile"]);
				$this->save_conf();
			}
	}
	
	function ParseServicesNames(){
		$file=dirname(__FILE__) . "/databases/master.cf";
		
		if(!is_file($file)){return array();}
		$datas=file_get_contents($file);
		$datasT=explode("\n",$datas);
		
		while (list ($num, $val) = each ($datasT) ){
			
			if(preg_match('#^([a-zA-Z0-9\.\:]+)\s+(unix|inet|fifo)#',$val,$regs)){
				$this->services_lists[$regs[1]]=$regs[1];
			}
			
		}
		
	}
	
	function BuildLdapSettings($ldap_key,$searchBaseWithoutSuffix=null,$queryfilter,$result_attribute,$special_result_attribute=null){
				$ldap=new clladp();
				if(trim($searchBaseWithoutSuffix)==null){
					$searchBaseWithoutSuffix="dc=organizations,$ldap->suffix";}
					else{
						if(trim($searchBaseWithoutSuffix)<>$ldap->suffix){$searchBaseWithoutSuffix="$searchBaseWithoutSuffix,$ldap->suffix";}
					}
				$ldap_host=$ldap->ldap_host;
				$ldap_port=$ldap->ldap_port;
				if($ldap_host=='*'){$ldap_host='127.0.0.1';}
				if($ldap_port==null){$ldap_port='389';}
				
				$conf=$conf ."\n\n#LDAP $ldap_key --------------------------------------------------------------------\n\n";
				$conf=$conf."{$ldap_key}_server_host=$ldap_host\n";
				$conf=$conf."{$ldap_key}_server_port =$ldap_port\n";
				$conf=$conf."{$ldap_key}_bind = yes\n";
				$conf=$conf."{$ldap_key}_bind_dn =cn=$ldap->ldap_admin,$ldap->suffix\n";
				$conf=$conf."{$ldap_key}_bind_pw =$ldap->ldap_password\n";
				$conf=$conf."{$ldap_key}_search_base =$searchBaseWithoutSuffix\n";
				$conf=$conf."{$ldap_key}_timeout = 10\n";
				$conf=$conf."{$ldap_key}_query_filter =$queryfilter\n";
				$conf=$conf."{$ldap_key}_version =3\n";
				if($special_result_attribute<>null){
					$conf=$conf."{$ldap_key}_special_result_attribute =$special_result_attribute\n";
					 
				}
				$conf=$conf."{$ldap_key}_result_attribute =$result_attribute\n#----------------------------------------------------------------------\n\n\n";	
				return $conf;	
	}
	
	function BuildMilters(){
		$sock=new sockets();
		if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return null;}
		$user=new usersMenus();
		$user->LoadModulesEnabled();
		if($sock->GET_INFO("EnableAmavisInMasterCF")==null){$sock->SET_INFO("EnableAmavisInMasterCF","0");}
		$KasxFilterEnabled=$sock->GET_INFO("KasxFilterEnabled");
		$ClamavMilterEnabled=$sock->GET_INFO("ClamavMilterEnabled");
		$SpamAssMilterEnabled=$sock->GET_INFO("SpamAssMilterEnabled");
		$EnableDKFilter=$sock->GET_INFO("EnableDKFilter");
		$EnableDkimMilter=$sock->GET_INFO("EnableDkimMilter");
		$EnableASSP=$sock->GET_INFO("EnableASSP");
		if(!$user->ASSP_INSTALLED){$EnableASSP=0;}
		$artica=new artica_general();
		$milter_protcol=0;
		$postfix_version=$user->POSTFIX_VERSION;
		if(preg_match('#([0-9]+)\.([0-9]+)#',$postfix_version,$re)){
			$postfix_version=$re[1].$re[2];
		}
		
		if($user->MILTER_SPY_INSTALLED){
				if($user->EnableMilterSpyDaemon==1){
					$smtpd_milters[]="unix:/var/spool/postfix/var/run/mailspy/mailspy.sock";
				}
			}

		if($user->spfmilter_installed==true){
				writepostfixlogs("spfmilter_installed=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);
				writepostfixlogs("spfmilterEnabled=$user->spfmilterEnabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if($user->spfmilterEnabled==1){
					$smtpd_milters[]="unix:/var/run/spfmilter/spfmilter.sock";
				}
			}
		if($user->OPENDKIM_INSTALLED){	
		if($EnableDKFilter==1){
			$smtpd_milters[]="unix:/var/run/opendkim/opendkim.sock";
			$nosmtpd_milters[]="unix:/var/run/opendkim/opendkim.sock";
			
		}}
		
		
		if($user->MILTER_DKIM_INSTALLED){	
		if($EnableDkimMilter==1){
			$smtpd_milters[]="unix:/var/run/dkim-milter/dkim-milter.sock";
			$nosmtpd_milters[]="unix:/var/run/dkim-milter/dkim-milter.sock";
			
		}}		
			
			
			writepostfixlogs("KasxFilterEnabled=$KasxFilterEnabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if($user->kas_installed==true){
					if($KasxFilterEnabled==1){
						
						$smtpd_milters[]="unix:/var/run/kas-milter.socket";
				}
			}
					
	
	
		if($EnableASSP==0){
			if($user->MILTERGREYLIST_INSTALLED){
				$sock->DeleteCache();
				$MilterGreyListEnabled=$sock->GET_INFO('MilterGreyListEnabled');
				
				if($MilterGreyListEnabled==1){
					if(trim($user->MILTERGREYLIST_SOCKET)<>null){$smtpd_milters[]="unix:$user->MILTERGREYLIST_SOCKET";}
					}else{
						writepostfixlogs("[$user->MilterGreyListEnabled] milter-greylist is not enabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
					}
				}else{
					writepostfixlogs("[$user->MILTERGREYLIST_INSTALLED] milter-greylist is not installed",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}
		}			
				
	//------------------------------------------------------------------------------
			if($user->DKIMFILTER_INSTALLED){
				if($user->DkimFilterEnabled){
					$nosmtpd_milters[]="unix:$user->DKIMFILTER_SOCKET";
					$smtpd_milters[]="unix:$user->DKIMFILTER_SOCKET";
				}
			}		
	//------------------------------------------------------------------------------			
			if($user->MAILFROMD_INSTALLED){
				if($MailFromdEnabled==1){
					if(strlen($user->MAILFROMD_SOCKET)>0){
						$smtpd_milters[]="unix:$user->MAILFROMD_SOCKET";
						}
					}
				}				
			
	
	//------------------------------------------------------------------------------			
			if($user->KAV_MILTER_INSTALLED==true){
				if($user->KAVMILTER_ENABLED==1){
						$milter_protcol=3;
						$smtpd_milters[]="inet:127.0.0.1:1052";
					}
			}
			
	//------------------------------------------------------------------------------		
			if($user->AMAVIS_INSTALLED){
				if ($user->EnableAmavisDaemon==1){
					if($sock->GET_INFO("EnableAmavisInMasterCF")==0){
						$smtpd_milters[]="unix:/var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock";		
					}
				}
			}
	//------------------------------------------------------------------------------		
			if($user->CLAMAV_MILTER_INSTALLED){
				if($ClamavMilterEnabled==1){
					$smtpd_milters[]="unix:$user->CLAMAV_MILTER_SOCKET";
				}
			}
	//------------------------------------------------------------------------------		
			if($user->SPAMASS_MILTER_INSTALLED){
				if($user->MimeDefangEnabled<>1){
				if($SpamAssMilterEnabled==1){
					$smtpd_milters[]="unix:$user->SPAMASS_MILTER_SOCKET";
				}
				}
			}
	
	//------------------------------------------------------------------------------		
			if($user->MIMEDEFANG_INSTALLED==true){
				writepostfixlogs("[$user->MIMEDEFANG_INSTALLED] MimeDefang is installed",__CLASS__.'/'.__FUNCTION__,__FILE__);
				if($user->MimeDefangEnabled==1){
					writepostfixlogs("[$user->MimeDefangEnabled] MimeDefang is enabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$smtpd_milters[]="unix:$user->MIMEDEFANG_SOCKET";
				}else{
					writepostfixlogs("[$user->MimeDefangEnabled] MimeDefang is not enabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}
			}else{
				writepostfixlogs("[$user->MIMEDEFANG_INSTALLED] MimeDefang is not installed",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
	//------------------------------------------------------------------------------		
			
			if($user->EnableMilterBogom==1){
				$smtpd_milters[]="unix:/var/spool/postfix/bogom/bogom.sock";
				$smtpd_milters[]="unix:/var/run/bogo-dump/bogo-dump.socket";	
			}
			
			
			
		
		if($user->EnableMysqlFeatures==1){
			if($user->EnableAmavisDaemon<>1){
				if($sock->GET_INFO("MailArchiverEnabled")==1){$smtpd_milters[]="unix:/var/run/maildump/maildump.socket";}
			}
		}
			
		
		if(count($smtpd_milters)>0){
				switch ($this->PostfixMiltersBehavior) {
						case 1:
							$milter_default_action='accept';
							break;
						case 0:
							$milter_default_action='tempfail';
							break;
						default:
							$milter_default_action='accept';
							break;
					}
				
					writepostfixlogs("milter_default_action=$this->PostfixMiltersBehavior ($milter_default_action)",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$conf=$conf . "smtpd_milters=" .implode(" ",$smtpd_milters)."\n";
					$conf=$conf . "milter_connect_macros = j _ {daemon_name} {if_name} {if_addr} {client_name} {client_addr} {client_resolve} {client_ptr}\n";
					$conf=$conf . "milter_helo_macros = {tls_version} {cipher} {cipher_bits} {cert_subject} {cert_issuer}\n";
					$conf=$conf . "milter_mail_macros = i {auth_type} {auth_authen} {auth_ssf} {auth_author} {mail_mailer} {mail_host} {mail_addr} {client_addr} {if_addr}\n";
					$conf=$conf . "milter_rcpt_macros = {rcpt_mailer} {rcpt_host} {rcpt_addr} {client_addr} {if_addr}\n";
					$conf=$conf . "milter_default_action = $milter_default_action\n";


					if($milter_protcol<3){
						if($postfix_version>=26){
							if(posix_getuid()<>0){write_syslog("Change milter protocol to level 6",__FILE__);}
							$conf=$conf . "milter_protocol = 6\n";
						}else{
							write_syslog("Change milter protocol to level 3",__FILE__);
							$conf=$conf . "milter_protocol = 3\n\n";
						}
					}else{
						$conf=$conf . "milter_protocol = $milter_protcol\n\n";
					}
					
					$conf=$conf . "milter_connect_timeout=180\n";
					$conf=$conf . "milter_command_timeout=180\n";
					$conf=$conf . "milter_content_timeout=600\n";
					if(is_array($nosmtpd_milters)){
						$conf=$conf . "non_smtpd_milters =".implode(" ",$nosmtpd_milters)."\n";
					}
					
		
					
			}	
	
			
			return $conf;
					
	}
	
	
	function save_conf_to_server($NoRestartDaemon=0){
		writepostfixlogs("##################### SAVE CONF TO SERVER #######################",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->FillDefaults();
		$this->save_conf();
		
		$ldap=new clladp();
		$sock=new sockets();
		$user=new usersMenus();
		$user->LoadModulesEnabled();			
		
		
		
		$EnableBlockUsersTroughInternet=$sock->GET_INFO("EnableBlockUsersTroughInternet");
		if($EnableBlockUsersTroughInternet==null){$EnableBlockUsersTroughInternet=0;$sock->SET_INFO("EnableBlockUsersTroughInternet","0");}
		
		$artica=new artica_general();
		
		
	
		$conf=$this->main_cf_datas;
		
		
		
		writepostfixlogs("Current configuration in memory as " .strlen($conf) . " caracters",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
	    if(strlen($conf)==0){
			writepostfixlogs("Warning, unable to read main.cf in ldap storage!!: " .strlen($conf) . " caracters",__CLASS__.'/'.__FUNCTION__,__FILE__);
			writepostfixlogs("Please check your ldap settings by access to server ldap://$ldap->ldap_host:$ldap->ldap_port cn=$ldap->ldap_admin,$ldap->suffix",
			__CLASS__.'/'.__FUNCTION__,__FILE__);
			return false;
		}		
		
		
		writepostfixlogs("Cleaning main.cf file",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$tbl=explode("\n",$conf);
		
		while (list ($num, $val) = each ($tbl) ){
			if(substr($val,0,1)=="#"){unset($tbl[$num]);}
			if(preg_match("#mynetworks#",$val)){unset($tbl[$num]);}
			if(preg_match("#virtual_mailbox_domains#",$val)){unset($tbl[$num]);}
			if(preg_match("#smtpd_milters#",$val)){unset($tbl[$num]);}			
			if(preg_match("#milter_connect_macros#",$val)){unset($tbl[$num]);}			
			if(preg_match("#milter_helo_macros#",$val)){unset($tbl[$num]);}
			if(preg_match("#milter_mail_macros#",$val)){unset($tbl[$num]);}	
			if(preg_match("#milter_rcpt_macros#",$val)){unset($tbl[$num]);}
			if(preg_match("#milter_default_action#",$val)){unset($tbl[$num]);}
			if(preg_match("#non_smtpd_milters#",$val)){unset($tbl[$num]);}
			if(preg_match("#milter_protocol#",$val)){unset($tbl[$num]);}
			if(preg_match("#milter_connect_timeout#",$val)){unset($tbl[$num]);}
			if(preg_match("#milter_command_timeout#",$val)){unset($tbl[$num]);}
			if(preg_match("#milter_content_timeout#",$val)){unset($tbl[$num]);}
		}

		
		$conf=implode("\n",$tbl);
		
		
		
	    if(strlen($conf)==0){
			writepostfixlogs("Warning, it seems there are no values after clean configuration : " .count($tbl) . " master table rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return false;
		}			
		
		if(!is_dir(dirname(__FILE__) . '/conf')){
			writepostfixlogs("creating directoty " . dirname(__FILE__) . '/conf',__CLASS__ . '/' . __FUNCTION__,__FILE__);
			if(!@mkdir(dirname(__FILE__) . '/conf')){
				writepostfixlogs("WARNING:: Unable to create directoty " . dirname(__FILE__) . '/conf',__CLASS__ . '/' . __FUNCTION__,__FILE__);	
				return false;
			}
			}
		
		// RULES................................................................................................................................................................................
			
			writepostfixlogs("***************** restrictions classes *****************",__CLASS__.'/'.__FUNCTION__,__FILE__);
		    $rules=new smtpd_restrictions();
			writepostfixlogs("Save rules restrictions",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$restrictions=$rules->Build();
			writepostfixlogs("rules->Build() return : $restrictions",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			$conf=$conf . "\n############ RESTRICTIONS ############\n$restrictions\n\n";
			$conf=$conf . $this->relay_clientcerts_conf();
			
		//...........................................................................................................................................................................................
		
		//Servers cache list ...........................................................................................................................................................
			$cach_list=$ldap->hash_get_smtp_connection_cache_destinations();
			if(is_array($cach_list)){
			writepostfixlogs("smtp_connection_cache_destinations :". count($cach_list) . " entrie(s)",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(!$cach_list){
				writepostfixlogs("SET smtp_connection_cache_destinations",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$ldap_key="ConnectionCacheDestinations";
				$conf=$conf . "smtp_connection_cache_destinations=ldap:ConnectionCacheDestinations\n";
				$conf=$conf . $this->BuildLdapSettings($ldap_key,"cn=smtp_connection_cache_destinations,cn=artica","(&(objectclass=PostFixSmtpConnectionCacheDestinations)(cn=%s))","cn");
				}
			}

			$VirtualMailManMaps=",ldap:VirtualMailManMaps";
			$virtual_transport_maps[]="ldap:TransportMailmanMaps";
			$virtual_transport_maps[]="ldap:SyncArticaMaps";

	
		   if(is_array($this->array_mynetworks)){
		     reset($this->array_mynetworks);
		     $nets=implode(',',$this->array_mynetworks);
		     writepostfixlogs("mynetworks=$nets",__CLASS__."/".__FUNCTION__,__FILE__);	
		     $conf=$conf . "mynetworks=$nets\n";
		    }else{
		    	writepostfixlogs("no array so mynetworks=127.0.0.0/8",__CLASS__."/".__FUNCTION__,__FILE__);
		    	$conf=$conf . "mynetworks=127.0.0.0/8\n";	
		    }
		    
	
		
		$conf=$conf . "artica_destination_recipient_limit = 1\n";
		$conf=$conf.$this->BuildMilters();
		
			
		$newtable=explode("\n",$conf);
		while (list ($num, $val) = each ($newtable) ){
			$new_arr[$val]=$num;
		}
		$conf='';
		while (list ($num, $val) = each ($new_arr) ){
			$conf=$conf .$num ."\n";
		}
		
		
		$this->_FixTree();	
		
		$tmp=new bounces_templates();
		$tmp->Save();	

		$sock=new sockets();
		writepostfixlogs("Save configuration file into Artica storage...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$this->main_cf_datas=$conf;
		$sock->SaveConfigFile($conf,"PostfixMainCfFile");
		$master=new master_cf();

		$master->PostfixMasterCfFile=$master->Build();
		$master->SaveToLdap(1);

		if($NoRestartDaemon==0){
			$datas=$sock->getFrameWork('cmd.php?SaveMaincf=yes');
			writepostfixlogs("Send order to restart daemon done...");	
		}
	  	
	  	$sock->DeleteCache();
	  	writepostfixlogs("###################### END ##############################",__CLASS__.'/'.__FUNCTION__,__FILE__);
	  	return true;
		}
	
	

	
  /**
  * Ensure that the tree is corrected builded to prevent errors.
  * warning: dict_ldap_lookup: ...
  * @access public
  * @param  none
  * @return void
  */	
  
  function _SaveMainCf($conf){
  	$ldap=new clladp();
  	writepostfixlogs("main.cf " . strlen($conf) . " lenght",__CLASS__.'/'.__FUNCTION__,__FILE__);
  	
  	
  	$sp=explode("\n",$conf);
  	if(is_array($sp)){
  		$conf='';
  		writepostfixlogs("cleaning main.cf builder",__CLASS__.'/'.__FUNCTION__,__FILE__);
  		while (list ($num, $val) = each ($sp) ){
  			if(trim($val)==null){continue;}
  			if(preg_match('#^\##',$val)){continue;}
  				$conf=$conf."$val\n";
  		
  		}
  	}
  	
  	writepostfixlogs("main.cf after cleaning " . strlen($conf) . " lenght,PostfixMiltersBehavior=$this->PostfixMiltersBehavior",__CLASS__.'/'.__FUNCTION__,__FILE__);
  	$sock=new sockets();
  	$sock->SaveConfigFile($conf,"PostfixMainCfFile");
	$sock->SET_INFO("PostfixMiltersBehavior",$this->PostfixMiltersBehavior);
	}
  
	private function _FixTree(){
		$ldap=new clladp();
		$dn="cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN("cn=smtp_sasl_password_maps,cn=artica,")){
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["objectClass"][]="top";
			$upd["cn"][]="smtp_sasl_password_maps";
			if(!$ldap->ldap_add($dn,$upd)){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error";}
			unset($upd);
			}
		
		$dn="cn=mailman,cn=artica,$ldap->suffix";
		writepostfixlogs("testing branch :$dn");
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["objectClass"][]="top";
			$upd["cn"][]="mailman";
			if(!$ldap->ldap_add($dn,$upd)){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error";}
			unset($upd);
			}

		
				
		
		 	$dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
		 	writepostfixlogs("testing branch :$dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(!$ldap->ExistsDN($dn)){
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='PostFixStructuralClass';
				$upd["cn"]="Sender_Dependent_Relay_host_Maps";
				$ldap->ldap_add($dn,$upd);
				unset($upd);
				}					

			$dn="cn=PostfixFilesStorage,cn=artica,$ldap->suffix";
			writepostfixlogs("testing branch :$dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(!$ldap->ExistsDN($dn)){
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='PostfixStoreFiles';
				$upd["cn"][]='PostfixFilesStorage';
				$upd["PostfixMasterCfFile"][]="#";
				if(!$ldap->ldap_add($dn,$upd)){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error";}
				unset($upd);
				}
			$dn="cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
			writepostfixlogs("testing branch :$dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if(!$ldap->ExistsDN($dn)){
				$upd['cn'][0]='smtp_sasl_password_maps';
				$upd['objectClass'][0]='PostFixStructuralClass';
				$upd['objectClass'][1]='top';
				if(!$ldap->ldap_add($dn,$upd)){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error";}
				unset($upd);	
				}				
		
		$this->BuildDefaultWhiteListRobots();
	}
	

	
	
}


class master_cf{
	var $filepath;
	var $master_array;
	var $PostfixMasterCfFile;
	var $ldap_error;
	var $error;
	var $dn;
	var $array_services=array();
	var $array_full=array();
	var $PostfixEnableMasterCfSSL;
	var $hostname;
	var $MasterCFUseDefaults=1;
	var $noService=false;
	
	var $array_type=array("fifo"=>"FIFO","inet"=>"INET","unix"=>"UNIX","pass"=>"PASS");
	
	var $standard_services=array(
		"pickup"=>true,
		"smtp"=>true,
		"cleanup"=>true,
		"qmgr"=>true,
		"tlsmgr"=>true,
		"rewrite"=>true,
		"bounce"=>true,
		"defer"=>true,
		"trace"=>true,
		"verify"=>true,
		"flush"=>true,
		"proxymap"=>true,
		"relay"=>true,
		"showq"=>true,
		"error"=>true,
		"discard"=>true,
		"local"=>true,
		"virtual"=>true,
		"lmtp"=>true,
		"anvil"=>true,
		"scache"=>true,
		"maildrop"=>true,
		"retry"=>true
	
	);
	
	function master_cf($noload=0,$hostname=null){
		include_once(dirname(__FILE__)."/class.mysql.inc");
		if($hostname==null){$hostname="master";}
		$this->hostname=$hostname;
		$sock=new sockets();
		$this->PostfixEnableMasterCfSSL=$sock->GET_INFO("PostfixEnableMasterCfSSL");
		$this->MasterCFUseDefaults=$sock->GET_INFO("MasterCFUseDefaults$this->hostname");
		if(!is_numeric($this->MasterCFUseDefaults)){$this->MasterCFUseDefaults=1;}
		$q=new mysql();
		if(!$q->TABLE_EXISTS("master_cf", "artica_backup")){$q->BuildTables();}
		if($noload==0){$this->CheckDefaults();}
	}
	
	function master_delete_all(){
		$q=new mysql();
		$sql="DELETE FROM master_cf WHERE hostname='$this->hostname'";
		$q->QUERY_SQL($sql,"artica_backup");
	}
		
	function SaveOptions(){
		$sock=new sockets();
		$sock->SET_INFO("PostfixEnableMasterCfSSL",$this->PostfixEnableMasterCfSSL);
		
	}
	
	
	private function CheckDefaults(){
		$sql="SELECT COUNT(zmd5) as tcount FROM master_cf WHERE hostname='$this->hostname'";
		$q=new mysql(); 
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["tcount"]==0){$this->add_defaults();}
	}
	
	
	function GetArray(){
		if($this->MasterCFUseDefaults==1){return array();}
		$sql="SELECT * FROM master_cf WHERE hostname='$this->hostname'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(mysql_num_rows($results)==0){return array();}
		if(!$q->ok){echo "$q->mysql_error\n";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$KEY=$ligne["zmd5"];
		$SERVICE=$ligne["SERVICE_NAME"];
		$TYPE=$ligne["TYPE"];
		$options=array();
		$optionsTXT=null;
		$MAXPROC=$ligne["MAXPROC"];
		$PRIVATE=$ligne["PRIVATE"];
		$UNIPRIV=$ligne["UNIPRIV"];
		$CHROOT=$ligne["CHROOT"];
		$WAKEUP=$ligne["WAKEUP"];
		
		if($MAXPROC==null){$MAXPROC="-";}
		if($PRIVATE==null){$PRIVATE="-";}
		if($UNIPRIV==null){$UNIPRIV="-";}
		if($CHROOT==null){$CHROOT="-";}
		if($WAKEUP==null){$WAKEUP="-";}
		
		$options=unserialize($ligne["COMMAND"]);
		$optionsF=array();
		$count=0;
		if (is_array($options)){
			while (list ($index, $opt) = each ($options) ){
				if(trim($opt)==null){continue;}
				if(trim($opt)=="Array"){continue;}
				if(is_array($opt)){continue;}
				$count++;
				$optionsF[]=$opt;
			}
		}else{continue;}	
		
		if(count($optionsF)==0){continue;}
		
		$array[$SERVICE]=array(
			"TYPE"=>$TYPE,
			"MAXPROC"=>$MAXPROC,
			"PRIVATE"=>$PRIVATE,
			"UNIPRIV"=>$UNIPRIV,
			"CHROOT"=>$CHROOT,
			"WAKEUP"=>$WAKEUP,
			"COMMAND"=>@implode(" ", $optionsF));
		
		
		
	}
	return $array;
	}
	



	
	//calibri
	private function add_defaults(){
		$this->noService=true;
		$this->edit_service("smtp    ","inet","n","-","n","-",   "-",array("smtpd"));
		$this->edit_service("pickup  ","fifo","n","-","n","60",   "1",array("pickup"));
		$this->edit_service("cleanup ","unix","n","-","n","-",    "0",array("cleanup"));
		$this->edit_service("qmgr    ","fifo","n","-","n","300",  "1",array("qmgr"));
		$this->edit_service("tlsmgr  ","unix","-","-","n","1000?","1",array("tlsmgr"));
		$this->edit_service("rewrite ","unix","-","-","n",    "-","-",array("trivial-rewrite"));
		$this->edit_service("bounce  ","unix","-","-","n",    "-","0",array("bounce"));		
		$this->edit_service("defer   ","unix","-","-","n",    "-","0",array("bounce"));
		$this->edit_service("trace   ","unix","-","-","n",    "-","0",array("bounce"));		
		$this->edit_service("verify  ","unix","-","-","n",    "-","1",array("verify"));			
		$this->edit_service("flush   ","unix","n","-","n",    "1000?","0",array("flush"));			
		$this->edit_service("proxymap","unix","-","-","n",        "-","-",array("proxymap"));	
		$this->edit_service("proxywrite","unix","-","-","n",        "-","1",array("proxymap"));			
		$this->edit_service("smtp    ","unix","-","-","n",        "-","-",array("smtp"));		
		$this->edit_service("relay   ","unix","-","-","n",        "-","-",array("smtp","-o fallback_relay="));		
		$this->edit_service("showq   ","unix","n","-","n",        "-","-",array("showq"));		
		$this->edit_service("error   ","unix","-","-","n",        "-","-",array("error"));		
		$this->edit_service("discard ","unix","-","-","n",        "-","-",array("discard"));		
		$this->edit_service("local   ","unix","-","n","n",        "-","-",array("local"));		
		$this->edit_service("virtual ","unix","-","n","n",        "-","-",array("virtual"));				
		$this->edit_service("lmtp    ","unix","-","-","n",        "-","-",array("lmtp"));		
		$this->edit_service("anvil   ","unix","-","-","n",        "-","1",array("anvil"));			
		$this->edit_service("scache  ","unix","-","-","n",        "-","1",array("scache"));			
		$this->edit_service("scan    ","unix","-","-","n",        "-","10",array("smtp"));
		$this->edit_service("maildrop","unix","-","n","n",        "-","-",array("pipe"));	
		$this->edit_service("retry   ","unix","-","-","n",        "-","-",array("error"));
		$this->edit_service("uucp    ","unix","-","n","n",        "-","-",array("pipe",'flags=Fqhu user=uucp argv=uux -r -n -z -a$sender - $nexthop!rmail ($recipient)'));
		$this->edit_service("ifmail  ","unix","-","n","n",        "-","-",array("pipe",'flags=F user=ftn argv=/usr/lib/ifmail/ifmail -r $nexthop ($recipient)'));
		$this->edit_service("bsmtp   ","unix","-","n","n",        "-","-",array("pipe",'flags=Fq. user=bsmtp argv=/usr/lib/bsmtp/bsmtp -t$nexthop -f$sender $recipient'));	
		$this->edit_service("scalemail-backend","unix","-","n","n","-","2",array("pipe",
		'flags=R user=scalemail argv=/usr/lib/scalemail/bin/scalemail-store ${nexthop} ${user} ${extension}'));		
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance=$this->hostname");
		
		
	}
	
	
	function edit_service($service_name,$type,$private,$unpriv,$chroot,$wakeup,$maxproc,$command,$key=null){
		
		$service_name=trim(strtolower($service_name));
		
		if($key==null){$key=md5($service_name.$type.$this->hostname);}
		$q=new mysql();
		$edit=false;
		$sql="SELECT zmd5,COMMAND FROM master_cf WHERE zmd5='$key'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["zmd5"]<>null){$edit=true;$commandz=unserialize($ligne["COMMAND"]);$command_text=addslashes(serialize($commandz));}
		writelogs("edit=$edit $service_name type=$type",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(is_array($command)){
			$command_text=addslashes(serialize($command));
		}
		
		$sql="INSERT IGNORE INTO master_cf (`hostname`,`SERVICE_NAME`,`TYPE`,`PRIVATE`,`UNIPRIV`,`CHROOT`,`WAKEUP`,`MAXPROC`,`COMMAND`,`zmd5`)
		VALUES('$this->hostname','$service_name','$type','$private','$unpriv','$chroot','$wakeup','$maxproc','$command_text','$key')
		";
		
		$sql_upd="UPDATE master_cf SET `PRIVATE`='$private',
		`TYPE`='$type',
		`UNIPRIV`='$unpriv',
		`CHROOT`='$chroot',
		`WAKEUP`='$wakeup',
		`MAXPROC`='$maxproc',
		`COMMAND`='$command_text'
		WHERE zmd5='$key'
		";
		
		if($edit){$sql=$sql_upd;}
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$q->mysql_error\n<H2>$sql</H2>";return false;}
		if(!$this->noService){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance=$this->hostname");
		}
		return true;
		}
	
	function add_command_options($zmd5,$option){
		$q=new mysql();
		$sql="SELECT zmd5,COMMAND FROM master_cf WHERE zmd5='$zmd5'";
		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		writelogs("$sql={$ligne["zmd5"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$commandz=array();
		$array=unserialize($ligne["COMMAND"]);
		if(is_array($array)){
			$commandz=$array;
			$commandz[]=$option;
		}else{
			$commandz[]=$option;
		}
		$options=addslashes(serialize($commandz));
		$sql="UPDATE master_cf SET `COMMAND`='$options' WHERE zmd5='$zmd5'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$q->mysql_error\n<H2>$sql</H2>";return false;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance=$this->hostname");		
		return true;
	}
	function del_command_options($zmd5,$index){
		$q=new mysql();
		$sql="SELECT zmd5,COMMAND FROM master_cf WHERE zmd5='$zmd5'";
		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		writelogs("$sql={$ligne["zmd5"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$commandz=array();
		$array=unserialize($ligne["COMMAND"]);
		if(is_array($array)){
			unset($array[$index]);
			$commandz=$array;
			
		}else{
			return;
		}
		$options=addslashes(serialize($commandz));
		$sql="UPDATE master_cf SET `COMMAND`='$options'";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$q->mysql_error\n<H2>$sql</H2>";return false;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance=$this->hostname");			
		return true;		
	}
	
	function delete_service($zdm5){
		$q=new mysql();
		$sql="DELETE FROM master_cf WHERE zmd5='$zdm5' AND `hostname`='$this->hostname'";
		writelogs("$sql=$zdm5",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$q->mysql_error\n<H2>$sql</H2>";return false;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance=$this->hostname");			
		return true;			
	}
		
	


	function LoadMastercfInDisk(){
		$sock=new sockets();
		return $sock->getFrameWork('master.cf');
		}
		
	function StripLines(){
		$datas=explode("\n",$this->PostfixMasterCfFile);
		while (list ($num, $val) = each ($datas) ){
			if(trim($val)==null){
				unset($datas[$num]);
			}
		}
		
		$this->PostfixMasterCfFile=implode("\n",$datas);
		
	}
	
	function _readfileArray(){
		$sock=new sockets();
		$datas=explode("\n",$this->PostfixMasterCfFile);
		return $datas;
		}
		
		
	function SaveToLdap($NoRestartDaemon=0){
			$ldap=new clladp();
			if(!$ldap->ExistsDN($this->dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostfixStoreFiles';
			$upd["cn"][]='PostfixFilesStorage';
			$upd["PostfixMasterCfFile"][]=$this->PostfixMasterCfFile;
			$upd["PostfixTimeCode"][]=date('YmdhIs');
			if(!$ldap->ldap_add($this->dn,$ldap->suffix,$upd)){
				echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error\n";
				echo $ldap->ldap_last_error;
				return false;
			}
			}else{
				$upd["PostfixMasterCfFile"][]=$this->PostfixMasterCfFile;
				$upd["PostfixTimeCode"][]=date('YmdhIs');
				if(!$ldap->Ldap_modify($this->dn,$upd)){
					echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error\n";
					echo $ldap->ldap_last_error;
					return false;
				}
			}
			
			$sock=new sockets();
			$sock->SET_INFO("MasterCFEnabled",1);
			if($NoRestartDaemon==0){$datas=$sock->getFrameWork('cmd.php?SaveMaincf=yes');}
	  		$tpl=new templates();
	  		
	  		return true;
		
		
	}
		
		
	}
	
class systeminfos{
	var $error;
	var $array_datas;
	var $array_ip;
	var $array_tcp_addr;
	var $array_tcp_addr_num;
	var $array_interfaces;
	var $array_dns_servers;
	var $array_applis;
	var $array_applis_line;
	var $ditribution_name;
	var $kernel_version;
	var $libc_version;
	var $memory_total;
	var $memory_used;
	var $memory_free;
	var $memory_shared;
	var $memory_buffer;
	var $memory_cached;
	var $swap_total;
	var $swap_used;
	var $swap_free;
	var $hostname;
	function systeminfos($hostname=null){
		if($hostname==null){
			$users=new usersMenus();
			$this->hostname=$users->hostname;
		}else{
			$this->hostname=$hostname;
		}		
		
		$this->ParseIP();
		$this->ParseResolv_conf();
		$this->Distri();
		$this->LoadMemory();
			
	}
	
	function ParseIP(){
		$sock=new sockets();
		$tpl=new templates();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?ifconfig-all=yes")));
		$this->array_datas=$datas;

		while (list ($num, $val) = each ($datas) ){
				$filter="#^([a-zA-Z\:\-0-9]+)#is";
				if(preg_match($filter,$val,$regs)){
					$this->array_interfaces[$regs[1]]=$regs[1];
					$array[$regs[1]]=$this->_ParseInterface($regs[1]);
				}
		}
			
		$this->array_ip=$array;	
		
	}
	
	function ParseResolv_conf(){
		$sock=new sockets();
		$tpl=new templates();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?resolv-conf")));
		while (list ($num, $val) = each ($datas) ){
			if(preg_match('#^nameserver\s+([a-zA-Z0-9\.\_]+)#',$val,$re)){
				
				$this->array_dns_servers[$re[1]]=$re[1];
			}
			
		}
	}
	
	function Distri(){
		$sock=new sockets();
		$tpl=new templates();
		$tbr=explode(';',trim($sock->getFrameWork('cmd.php?myos=yes')));
		
		$this->ditribution_name=$tbr[0];
		$this->kernel_version=$tbr[1];
		$this->libc_version=$tbr[2];
		
		}
		
	function lspci(){
		$sock=new sockets();
		$tpl=new templates();
		$tbr=unserialize(base64_decode($sock->getFrameWork("cmd.php?lspci=yes")));
		while (list ($num, $val) = each ($tbr) ){
			if(preg_match('#([0-9a-z\:\.]+)\s+([a-zA-Z0-9\s]+):(.+)#',$val,$re)){
				$res[$re[3]]=$re[2];
			}
			
		}
		return $res;
	}
	
	function LoadMemory(){
		$sock=new sockets();
		$tpl=new templates();
		$tbr=unserialize(base64_decode($sock->getFrameWork("cmd.php?freemem=yes")));
		if(!is_array($tbr)){return null;}
		while (list ($num, $val) = each ($tbr) ){if($val<>null){$array[]=$val;}}
		if(is_array($array)){
			while (list ($num, $val) = each ($array) ){	
				if(preg_match('#Mem:\s+([0-9]+)\s+([0-9]+)\s+([0-9+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#',$val,$reg)){
					//total       used       free     shared    buffers     cached
					$this->memory_total=$reg[1];
					$this->memory_used=$reg[2];
					$this->memory_free=$reg[3];
					$this->memory_shared=$reg[4];
					$this->memory_buffer=$reg[5];
					$this->memory_cached=$reg[6];
				}
			    
				if(preg_match('#Swap:\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#',$val,$reg)){
			        	$this->swap_total=$reg[1];
			        	$this->swap_used=$reg[2];
			        	$this->swap_free=$reg[3];
			        }
			}
		}			
	}
	
	
	function GetCpuTemp(){
		$sock=new sockets();
		$tpl=new templates();
		return trim($sock->getfile('systemtemp'));
		
	}
	
	function DiskUsages(){
		$sock=new sockets();
		$tpl=new templates();
		$tbr=unserialize(base64_decode($sock->getFrameWork("cmd.php?dfmoinsh=yes")));	
		if(!is_array($tbr)){return null;}
		while (list ($num, $val) = each ($tbr) ){if($val<>null){$array[]=$val;}}
		if(is_array($array)){
			
		while (list ($num, $val) = each ($array) ){
				if(preg_match('#([a-zA-Z0-9\/\.\-\_]+)\s+([0-9A-Z]+)\s+([0-9A-Z]+)\s+([0-9A-Z]+)\s+([0-9\%]+)\s+([a-z0-9\/\.\-\_]+)#',$val,$reg)){
					$arr[]=array($reg[1],$reg[2],$reg[2],$reg[3],$reg[4],$reg[5],$reg[6]);
					}
				}
		}
		
		return $arr;
	}
	
	function environements(){
		$sock=new sockets();
		$tpl=new templates();
		$datas=$sock->getFrameWork('cmd.php?printenv=yes');
		return $datas;
		
	}
	
	function ParseAPPs(){
		$sock=new sockets();
		$tpl=new templates();	
		$datas=$sock->getFrameWork('cmd.php?Global-Applications-Status=yes');
		
		
		if(preg_match_all('#<(.+?)>(.+?)</(.+?)>#is',$datas,$regs)){
			while (list ($a, $b) = each ($regs) ){
				$title=$regs[1][$a];
				$table=explode("\n",$regs[2][$a]);
				if(is_array($table)){
					while (list ($num, $val) = each ($table) ){
						if(preg_match('#\[(.+)\]\s+#',$val,$reg)){
								if(preg_match('#"(.+)"#',$val,$reg1)){$version=$reg1[1];}else{$version=null;}
								$this->array_applis[$title][$reg[1]]=$version;
								$this->array_applis_line[$reg[1]]=$version;
								
							}
						}
					}
			}
		}
	}
	
	
	function _ParseInterface($interface){
		$sock=new sockets();
		$datas=explode("\n",$sock->getfile('InetInfos:' . $interface));
		while (list ($num, $val) = each ($datas) ){
			if(preg_match('#HWaddr\s+([0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2}:[0-9A-Z]{1,2})#i',$val,$regs)){
				$array["MAC"]=$regs[1];
			}
			
	

			if(preg_match('#(Masque|Mask):([0-9\.]+)#i',$val,$regs)){
				$array["NETMASK"]=$regs[2];
			}
			
			if(preg_match('#inet (adr|addr):([0-9\.]+)#i',$val,$regs)){
				$array["IP"]=$regs[2];
				$this->array_tcp_addr[$regs[2]]=$regs[2];
				$this->array_tcp_addr_num[]=$regs[2];
			}
	
			
			
		}
		if($array["IP"]==null){$array["IP"]="0.0.0.0";}
	return $array;}
	


}

class smtpd_restrictions{

	var $smtpd_client_restrictions_table;
	var $smtpd_sender_restrictions_table;
	var $smtpd_recipient_restrictions_table;
	var $smtpd_helo_restrictions_table;
	var $smtpd_all_restrictions_table;
	var $smtpd_hash_restrictions_table;
	var $main_array=array();
	
	var $standard_classes_array=array(
		"smtpd_client_restrictions"=>"smtpd_client_restrictions",
		"smtpd_sender_restrictions"=>"smtpd_sender_restrictions",
		"smtpd_helo_restrictions"=>"smtpd_helo_restrictions",
		"smtpd_recipient_restrictions"=>"smtpd_recipient_restrictions"
		);
	
	var $restriction_rules_key=array(
		"check_recipient_access"=>"yes",
		"check_recipient_mx_access"=>"yes",
		"check_recipient_ns_access"=>"yes",
		"check_helo_access"=>"yes",
		"check_helo_mx_access"=>"yes",
		"check_helo_ns_access"=>"yes",
		"check_sender_access"=>"yes",
		"check_sender_mx_access"=>"yes",
		"check_sender_ns_access"=>"yes"
	);
	
	var $dnsrbl_database;
	
	
	function smtpd_restrictions(){
		$this->IfPolicyServerExists();
		$this->IfPolicyServerSqlGreyExists();
		//$this->load_smtpd_client_restrictions_db();
		$this->Load();
		}
		
	function Load(){
		/*$this->main_array["smtpd_recipient_restrictions"]=$this->LoadRestrictionClassData("smtpd_recipient_restrictions");
		$this->main_array["smtpd_sender_restrictions"]=$this->LoadRestrictionClassData("smtpd_sender_restrictions");
		$this->main_array["smtpd_helo_restrictions"]=$this->LoadRestrictionClassData("smtpd_helo_restrictions");
		$this->main_array["smtpd_client_restrictions"]=$this->LoadRestrictionClassData("smtpd_client_restrictions");
		*/
	}
	
	
	function IfRestrictionsKeysExists($mainrestriction=null,$keysToFind=array()){
		if(!is_array($this->main_array)){return false;}
		reset($this->main_array);
		if(!is_array($this->main_array[$mainrestriction]["rules"])){return false;}
		while (list ($index, $array) = each ($this->main_array[$mainrestriction]["rules"]) ){$restriction[$array["KEY"]]=true;}
		
		
		for($i=0;$i<count($keysToFind);$i++){
			if(!$restriction[$keysToFind[$i]]){
				writelogs(" unable to find {$keysToFind[$i]} in $mainrestriction class",__CLASS__ . "/".__FUNCTION__,__FILE__);
				return false;
			}
			
		}
		writelogs(implode(",",$keysToFind)." has been found in $mainrestriction class",__CLASS__ . "/".__FUNCTION__,__FILE__);
		return true;
		
	}
	
	
function LoadRestrictionClassData($class_name,$debug=0){
	
	$ldap=new clladp();
	$path="cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	$sr=@ldap_search($ldap->ldap_connection,$path,"(&(ObjectClass=*)(cn=$class_name))",array());
	if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			if($hash[0]["postfixrestrictionclassdescription"][0]==null){
				$update_array["PostFixRestrictionClassDescription"][]="no description";
				$ldap->Ldap_add_mod($path,$update_array);
				return array("description"=>"no description");
				}
				
			$count=$hash[0]['postfixrestrictionclasslist']["count"];	
			if($count==0){return array();}
			writelogs("$class_name, return $count rows in $path",__CLASS__ . "/".__FUNCTION__,__FILE__);	
			if($hash[0]['postfixrestrictionclasslist']["count"]>0){
				for($i=0;$i<=$count;$i++){
					$rule_single=$hash[0]['postfixrestrictionclasslist'][$i];
					$rulessaved[$rule_single]=$rule_single;
					
					if(preg_match('#(.+)?="(.*)?"#',$rule_single,$reg)){
						writelogs("$class_name, return {$reg[1]}",__CLASS__ . "/".__FUNCTION__,__FILE__);	
						$rules_list[]=array("KEY"=>$reg[1],"datas"=>$reg[2]);
					}
				}
				
			}
	
		return array("description"=>$hash[0]["postfixrestrictionclassdescription"][0],"rules"=>$rules_list,"rules_source"=>$rulessaved);
	}
}	
	
		
	function IfPolicyServerExists(){
		include_once(dirname(__FILE__).'/class.artica.inc');
		$ldap=new clladp();
		$users=new usersMenus();
		if($users->SMTP_LEVEL>0){
			writepostfixlogs("Check policy services disabled by artica global settings",__CLASS__ . '/'.__FUNCTION__,__FILE__);
			return true;}
		writepostfixlogs("Check policy services for artica and/or sqlgrey",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		$hash=$ldap->RestrictionClassData("smtpd_helo_restrictions");
		if(is_array($hash["rules"])){
			while (list ($index, $array) = each ($hash["rules"]) ){
				if($array["KEY"]=='check_policy_service'){
					if($array["datas"]=="inet:127.0.0.1:29001"){
						return true;
						}
				}
			}
		}
		
	
	}
	
	
	function IfPolicyServerSqlGreyExists(){
		
		$ldap=new clladp();
		$users=new usersMenus();
		if($users->SMTP_LEVEL>0){
			writepostfixlogs("Check policy services disabled by artica global settings",__CLASS__ . '/'.__FUNCTION__,__FILE__);
			return true;}
			
	   include_once(dirname(__FILE__) . '/class.sqlgrey.inc');
		$sqlgrey=new sqlgrey();
		$sqlgrey->SqlGreyEnabled=0;
		
		writepostfixlogs("SqlGrey enabled ??? =$sqlgrey->SqlGreyEnabled",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		
		if($sqlgrey->SqlGreyEnabled==1){
			if(!$this->IfKeyExistsInRestriction("smtpd_recipient_restrictions","check_policy_service","inet:127.0.0.1:23675")){
				writepostfixlogs("SqlGrey is enabled but there is no entry in LDAP database, add it",__CLASS__ . '/'.__FUNCTION__,__FILE__);
				$this->AddKey("reject_unauth_destination","smtpd_recipient_restrictions");
				$this->AddKey("check_policy_service","smtpd_recipient_restrictions","inet:127.0.0.1:23675");
				
			}else{
				if(!$this->IfKeyExistsInRestriction("smtpd_recipient_restrictions","reject_unauth_destination")){
					$this->AddKey("reject_unauth_destination","smtpd_recipient_restrictions");
				}
			}
		}else{
			writepostfixlogs("SqlGrey is disabled, remove it from the array list",__CLASS__ . '/'.__FUNCTION__,__FILE__);
			return true;
		}
	}
	

	
	function IfKeyExistsInRestriction($restriction_key,$key,$value=null){
		$ldap=new clladp();
		$users=new usersMenus();
		
		writepostfixlogs("verify in $restriction_key if exists $key=$value",__CLASS__ . '/'.__FUNCTION__,__FILE__);
		
		$hash=$ldap->RestrictionClassData($restriction_key);
		
		if(is_array($hash["rules"])){
			while (list ($index, $array) = each ($hash["rules"]) ){
				writepostfixlogs("if {$array["KEY"]}=$key and {$array["datas"]}=\"$value\" ?",__CLASS__ . '/'.__FUNCTION__,__FILE__);
				if($array["KEY"]==$key){
					
					if($array["datas"]==$value){
						writepostfixlogs("return TRUE",__CLASS__ . '/'.__FUNCTION__,__FILE__);
						return true;
						}
				}
			}
		}

	writepostfixlogs("it seems that $restriction_key=$key  $value does not exists",__CLASS__ . '/'.__FUNCTION__,__FILE__);		
		
	}	
	
	

	function HashGetHeadersList(){
		$datas=file_get_contents(dirname(__FILE__).'/databases/db.headers.txt');
		return explode("\n",$datas);
	}
	
	function CreateFormDependOnKey($restriction_key){
		$array=$this->smtpd_hash_restrictions_table;
		$settings=$array[1][$restriction_key]["datas"];
		
		
		
		$value="<strong>{value}</strong>&nbsp;:<br>";
		if($settings=="ACTIONS_datas"){
			switch ($restriction_key) {
				case "OK":$field="<input type='hidden' name='datas' value=''>";$value=null;break;
				case "DUNNO":$field="<input type='hidden' name='datas' value=''>";$value=null;break;
				case "REJECT":$field=Field_text('datas');break;
				case "BCC":$field=Field_text('datas');break;
				case "REDIRECT":$field=Field_text('datas');break;
				case "HOLD":$field=Field_text('datas');break;
				case "WARN":$field=Field_text('datas');break;
				case "DISCARD":$field=Field_text('datas');break;
				case "PREPEND":
					$h1=$this->HashGetHeadersList();
					$field="<strong>Header:&nbsp;</strong>".Field_array_Hash($h1,'value_1',null) . "<br><strong>{value}</strong>:&nbsp;" . Field_text('value_2');$value=null;
					break;
				case "FILTER":$field=Field_text('datas');break;
				default:$field="<strong>Unknown&nbsp;$settings:&nbsp;</strong>";$value=null;break;
			}
		}else{
			writepostfixlogs("$restriction_key => settings=$settings",__CLASS__ . '/' . __FUNCTION__,__FILE__);
			switch ($settings) {
				case 'no_datas':$field="<input type='hidden' name='datas' value=''>";$value=null;break;
				case 'rhsbl':$field=Field_array_Hash($this->dnsrbl_database["RHSBL"],'datas');;break;
				case 'rbl':$field=Field_array_Hash($this->dnsrbl_database["RBL"],'datas');;break;
				case 'ldap':$field="<input type='hidden' name='datas' value='ldap'><code>ldap://$restriction_key</code>";break;
				case 'class':$field="<input type='hidden' name='datas' value='class'><code>ldap://{$_GET["class_name"]};{$_GET["PostfixSelectedRestriction"]}</code>";break;
				default:$field="<strong>$restriction_key:&nbsp;</strong>";$value=null;break;
				
				}
		}
	
	return $value.$field;
		
	}
	
	function DeleteKey($keyname,$class_name,$value=null){
		$ldap=new clladp();
		$hash=$ldap->RestrictionClassData($class_name);
		if(!is_array($hash["rules"])){return null;}
		$dn="cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
		$update_array["PostFixRestrictionClassList"]="$keyname=\"$value\"";
		$ldap->Ldap_del_mod($dn,$update_array);
		}
	
	function AddKey($keyname,$class_name,$value=null){
		$ldap=new clladp();
		$ldap->IFcn_notexists_CreateIt('artica');
		
		$dn0="cn=restrictions_classes,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn0)){
			writepostfixlogs("ADDING NEW BRANCH $dn0",__CLASS__.'/' .__FUNCTION__,__FILE__);
			$upd["cn"][0]="restrictions_classes";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			if(!$ldap->ldap_add($dn0,$upd)){echo __CLASS__.'/'.__FUNCTION__ ." (".__LINE__.") failed\n$ldap->ldap_last_error\n";}	
			unset($upd);
			}else{writepostfixlogs("\"$dn0\" EXISTS",__CLASS__.'/' .__FUNCTION__,__FILE__);}
		
		
		$dn="cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	
	if(!$ldap->ExistsDN($dn)){
		if($this->standard_classes_array[$class_name]=$class_name){$obecjtClass="PostFixRestrictionStandardClasses";}else{$obecjtClass="PostFixRestrictionClasses";}
		writepostfixlogs("Add  $obecjtClass ObJectClass",__CLASS__.'/' .__FUNCTION__,__FILE__);
		$upd["cn"][0]=$class_name;
		$upd['objectClass'][0]=$obecjtClass;
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		}else{writepostfixlogs("$dn Exists...",__CLASS__.'/' .__FUNCTION__,__FILE__);}
		$add=false;
		$hash=$ldap->RestrictionClassData($class_name);
		if(is_array($hash["rules"])){
			while (list ($index, $array) = each ($hash["rules"]) ){
				$KEY=$array["KEY"];
				$DATAS=$array["datas"];
				if($KEY==$keyname && $DATAS==$value){return null;}
				
				}
		}
	writepostfixlogs("$keyname=\"$value\"",__CLASS__.'/' .__FUNCTION__,__FILE__);	
	$value="$keyname=\"$value\"";
	$update_array["PostFixRestrictionClassList"][]=$value;
	$dn="cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	if(!$ldap->Ldap_add_mod($dn,$update_array)){echo $ldap->ldap_last_error;};
		
	}
		
		
	
	
	function Build(){
		$ldap=new clladp();
		$table=$ldap->Hash_get_restrictions_classes();
		
	if(count($table)>0){
			while (list ($class_name, $nulll) = each ($table) ){
				$rules=$this->BuildClassRules($class_name);
				if($rules==null){
					unset($table[$class_name]);
					$class_deleted[]=$class_name;
				}else{
					$class_datas=$ldap->RestrictionClassData($class_name);	
					writepostfixlogs("Building class $class_name=$rules",__CLASS__ . '/' . __FUNCTION__,__FILE__);
					$classes=$classes. "#{$class_datas["description"]}\n$class_name=" . $rules . "\n";
					}
				}
			
			$conf="\nsmtpd_restriction_classes=" . implode(',',$table) . "\n\n";
			

			
		}
		

		
		while (list ($index, $standard_classes_name) = each ($this->standard_classes_array) ){
			$rules=$this->BuildClassRules($standard_classes_name,1);
			if($rules<>null){
				$standard_classes=$standard_classes  . "$standard_classes_name=$rules\n";
			}
		}
		
		if(is_array($class_deleted)){
			while (list ($index, $class_sup) = each ($class_deleted) ){
				$classes=str_replace(",$class_sup",'',$classes);
				writepostfixlogs("Delete references on $class_sup",__CLASS__ . '/' . __FUNCTION__,__FILE__);
				$standard_classes=str_replace("$class_sup,",'',$standard_classes);	
				
			}
		}

		$standard_classes=str_replace(",,",',',$standard_classes);	
		writepostfixlogs("return -----------------------",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		writepostfixlogs("$conf\n$classes\n$standard_classes",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		writepostfixlogs("------------------------------",__CLASS__ . '/' . __FUNCTION__,__FILE__);
		return  $conf.$classes.$standard_classes;
	}
	
	function BuildClassRules($class_name,$debug=0){
		$ldap=new clladp();
		include_once(dirname(__FILE__).'/class.artica.inc');
		$artica=new artica_general();
		
		if($users->SMTP_LEVEL>0){$artica=true;}
		
		
		
		$users=new usersMenus();
		include_once(dirname(__FILE__) . '/class.sqlgrey.inc');
		$sqlgrey=new sqlgrey();
		$Hash=$ldap->RestrictionClassData($class_name,$debug);
		if(!is_array($Hash["rules"])){return null;}
		while (list ($index, $restrictions) = each ($Hash["rules"]) ){
			$keyname=$restrictions["KEY"];
			$key_value=$restrictions["datas"];
			if($key_value==null){
				$table_res[]=$keyname;
			}
			else{
				writepostfixlogs("Found:: \"$keyname=$key_value\"",__CLASS__ . '/' . __FUNCTION__,__FILE__);
				switch (trim($key_value)) {
						case "ldap":$ldap_config=$ldap_config . $this->BuildLDapConfig($keyname,$class_name);
							writepostfixlogs("$keyname =>LDAP for $class_name",__CLASS__ . '/' . __FUNCTION__,__FILE__);
							default:$table_res[]=$keyname . " ldap:$class_name".'_'.$keyname;
							break;
						case "class":;break;
						
						case "inet:127.0.0.1:29001":
							
							if(!$artica){
							 	writepostfixlogs("$keyname=$key_value disabled by global rule that disable artica addons",__CLASS__ . '/' . __FUNCTION__,__FILE__);	
								}else{
							 		$table_res[]=$keyname . " ". $key_value;	
								}
							break;
						
						case "inet:127.0.0.1:23675":
							if($users->SMTP_LEVEL>0){
							}else{
							 writepostfixlogs("$key_value verif if Sqlgrey is enabled...",__CLASS__ . '/' . __FUNCTION__,__FILE__);	
							 if($sqlgrey->SqlGreyEnabled==1){
							 	$table_res[]=$keyname . " ". $key_value;
							 }else{
							 	writepostfixlogs("$keyname=$key_value sqlgrey disabled",__CLASS__ . '/' . __FUNCTION__,__FILE__);	
							 }
							}
							break;	
							
						default:$table_res[]=$keyname . " ". $key_value;
							break;
					}	
			  	}
			  }
	         if(count($table_res)>0){
					$results=implode(',',$table_res) . $ldap_config;
					return $results;
	         }
		}
		
	function BuildLDapConfig($keyname,$class_name){
		$ldap_key=$class_name.'_'.$keyname;
		$ldap=new clladp();
		$ldap_conf="\n\n{$ldap_key}_server_host=$ldap->ldap_host\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_server_port =$ldap->ldap_port\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_bind = yes\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_bind_dn =cn=$ldap->ldap_admin,$ldap->suffix\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_bind_pw =$ldap->ldap_password\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_search_base =cn=$keyname,cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_timeout = 10\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_query_filter =(&(cn=%s)(objectClass=PostFixRestrictionCheckAccess))\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_result_attribute = PostFixRestrictionTableAction\n";
		$ldap_conf=$ldap_conf."{$ldap_key}_version =3\n";
		return $ldap_conf;
	}
	
	

	
	

	function load_smtpd_client_restrictions_db(){
		return;
		$file=explode("\n",file_get_contents(dirname(__FILE__). '/smtpd_client_restrictions.db'));
		
		
	//load saved class...
		$ldap=new clladp();
		$saved_class=$ldap->Hash_get_restrictions_classes();
		if(is_array($saved_class)){
			while (list ($class_name, $nulll) = each ($saved_class) ){
				$file[]="$class_name;class;class;class;";
			}
		}			
		
		while (list ($num, $val) = each ($file) ){
			if($val<>null){
			$arr=explode(';',$val);
		if($arr[3]<>"hash"){	
			$smtpd_client_restrictions_all_1[]=$arr[0];
			$smtpd_client_restrictions_all_2[$arr[0]]=array("mode"=>$arr[1],"datas"=>$arr[2]);
		}
			switch ($arr[3]) {
				case "sender":
					$array_sender1[]=$arr[0];
					$array_sender[$arr[0]]=array("mode"=>$arr[1],"datas"=>$arr[2]);
					break;
					
				case "client":
					$array1[]=$arr[0];
					$array[$arr[0]]=array("mode"=>$arr[1],"datas"=>$arr[2]);
					break;
					
				case "recipient":
					$array2[]=$arr[0];
					$array_recipient[$arr[0]]=array("mode"=>$arr[1],"datas"=>$arr[2]);
					break;
					
				case "helo":
					$arrayHelo[]=$arr[0];
					$array_Helo[$arr[0]]=array("mode"=>$arr[1],"datas"=>$arr[2]);
					break;
				case "hash":
					$arrayHash[]=$arr[0];
					$array_Hash[$arr[0]]=array("mode"=>$arr[1],"datas"=>$arr[2]);
					break;					
														
				default:
					
					break;
				}
			}
			
		}
			
		
		$this->smtpd_client_restrictions_table=array($array1,$array);
		$this->smtpd_sender_restrictions_table=array($array_sender1,$array_sender);
		$this->smtpd_recipient_restrictions_table=array($array2,$array_recipient);
		$this->smtpd_helo_restrictions_table=array($arrayHelo,$array_Helo);
		$this->smtpd_all_restrictions_table=array($smtpd_client_restrictions_all_1,$smtpd_client_restrictions_all_2);
		$this->smtpd_hash_restrictions_table=array($arrayHash,$array_Hash);
		
		
		$arr_0=array_merge($this->smtpd_all_restrictions_table[0],$this->smtpd_hash_restrictions_table[0]);
		$arr_1=array_merge($this->smtpd_all_restrictions_table[1],$this->smtpd_hash_restrictions_table[1]);
		$this->smtpd_hash_restrictions_table=array($arr_0,$arr_1);
		unset($arr_0);unset($arr_1);


	
		
		$file=explode("\n",file_get_contents(dirname(__FILE__). '/dnsrbl.db'));
		while (list ($num, $val) = each ($file) ){
			if(preg_match('#RBL:([a-zA-Z0-9\-_\.]+)#',$val,$regs)){$this->dnsrbl_database["RBL"][$regs[1]]=$regs[1];}
			if(preg_match('#RHSBL:([a-zA-Z0-9\-_\.]+)#',$val,$regs)){$this->dnsrbl_database["RHSBL"][$regs[1]]=$regs[1];}
		}
		
		
	}			
		
		
}

class PostFixLogs{
	
	function PostFixLogs(){
		
		
	}
	

	
	
	function GetErrorLogs($max=10){
		
		$sock=new sockets();
		$datas=explode("\n",$sock->getfile('PostfixErrorsLogs'));
		if (!is_array($datas)){
			writepostfixlogs("no datas receive from framework",__FUNCTION__,__FILE__);
			return null;}
		
		array_reverse($datas);
		$count=count($datas);
		writepostfixlogs($count . ' lines',__FUNCTION__,__FILE__);
		if($count<5){return "<H5>{no_errors}</H5>";}
		if($count>$max){$count=$max;}else{$count=count($datas);}

		return "<H5>{postfix_last_errors}</H5><br>" . $this->ParsePostfixLogs($datas,$count);
		
	}
	
	function ParsePostfixLogs($array,$count,$justdiv=0){
		
		for($i=0;$i<$count+1;$i++){
			$line=$array[$i];
			
			if(preg_match('#^(.+?)\s+([0-9+\:]+)\s+(.+?)\s+(.+?)\s+(.+?)\[([0-9]+)\]:(.+)#',$line,$reg)){
			$lines=$lines+1;
			$reg[7]=htmlentities($reg[7]);
			if(preg_match('#([A-Z0-9]+):#',$reg[7],$re)){
				$message_id=$re[1];
				$md=md5($reg[7].$i);
				$reg[7]=str_replace($message_id,texttooltip($message_id,"{more_infos}<br><strong>$message_id</strong>","LoadPostfixHistoryMsgID('$message_id','$md')"),$reg[7]);
			}
			
			$reg[3]=texttooltip($reg[3],"{more_infos}<br><strong>{$reg[3]}</strong>","LoadPostfixHistoryMsgID('{$reg[3]}','$md')");
			
			if($justdiv==1){
				$html=$html ."<div style='padding:2px;padding-left:10px;width:95%'><strong>{$reg[5]}</strong> {$reg[3]} {$reg[7]}</div><div id='$md'></div>";
			}else{
			$html=$html . RoundedLightGrey("
			<table style='width:100%'>
			<tr>
			<td width=1%  valign='top'><img src='img/icon_mini_off.jpg'></td>
			<td valign='top' >{$reg[5]}</td>
			<td valign='top' >{$reg[3]}</td>
			<td valign='top' style='font-size:10px;text-align:justify'>{$reg[7]}</td>
			</tr>
			</table>
			<div id='$md'></div>") . "<br>";
			}}else{
				writepostfixlogs('not match=>'.$line,__FUNCTION__,__FILE__);
			}
		}
		
		
		if($lines==0){return null;}
		
		return $html;
	}
	
	
}



class relay_clientcerts{
	var $dn;
	
	
	function relay_clientcerts(){
		$this->verifyBranch();
		
	}
	
function ParseTable(){
	$ldap=new clladp();
	$pattern="(objectclass=PostfixRelayClientCerts)";
	$sr =@ldap_search($ldap->ldap_connection,$this->dn,$pattern,array());
	if(!$sr){return array();}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	for ($i=0;$i<$hash["count"];$i++){
		$res[$hash[$i]["cn"][0]]=$hash[$i]["relayclientcert"][0];
	}
	
	return $res;
	
}
	
	
function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=relay_clientcerts_maps,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
			$upd['objectClass'][]='top';
			$upd['objectClass'][]='PostFixStructuralClass';
			$upd["cn"][]="relay_clientcerts_maps";
			$ldap->ldap_add($this->dn,$upd);
			}
	}

function add($fingerprint,$host){
	$tpl=new templates();
	$ldap=new clladp();
	$cn=md5("$fingerprint$host");
	$dn="cn=" . $fingerprint .",$this->dn";
	if(!$ldap->ExistsDN($dn)){
			$upd['objectClass'][]='PostfixRelayClientCerts';
			$upd['objectClass'][]='top';
			$upd['relayClientcert'][]=$host;
			$upd['relayClientcertFingerprint'][]=$fingerprint;
			$upd["cn"][]="$fingerprint";
			if($ldap->ldap_add($dn,$upd)){
				echo $tpl->_parse_body("{success} Add fingerprint;\n");
			}else{echo $ldap->ldap_last_error;}
		
	}
	
}

function del($cn){
	$ldap=new clladp();
	$dn="cn=" . $cn .",$this->dn";
	$ldap->ldap_delete($dn,true);
}
	
	
	
}

class maincf_reports{
	var $maincf;
	var $main_array=array();
	var $must2Fields=array();
	
	
		function maincf_reports(){
				$sock=new sockets();
				$this->maincf=$sock->getfile('main.cf');
				$this->ParseConfig();
				
//virtual_alias_maps
			$this->must2Fields["virtual_alias_maps"]=true;
			$this->must2Fields["virtual_mailbox_maps"]=true;
			$this->must2Fields["alias_maps"]=true;
			$this->must2Fields["sender_canonical_maps"]=true;
			$this->must2Fields["recipient_canonical_maps"]=true;
			$this->must2Fields["transport_maps"]=true;
			$this->must2Fields["relay_recipient_maps"]=true;
			$this->must2Fields["local_recipient_maps"]=true;
			$this->must2Fields["relay_clientcerts"]=true;
			$this->must2Fields["smtp_sasl_password_maps"]=true;
			

			
			
		}
		
		function ParseConfig(){
			$tbl=explode("\n",$this->maincf);
			while (list ($num, $val) = each ($tbl) ){
				if(trim($val)==null){continue;}
				if(preg_match("#(.+?)[\s+=]+(.+)#",$val,$re)){
					$this->main_array[trim($re[1])]=$re[2];
				}
			}
			
		}
		
		function ParseInfos($key){
			if($this->main_array[$key]==null){return "<strong style='color:red'>No datas</strong>";}
			$tbl=explode(",",$this->main_array[$key]);
				while (list ($num, $val) = each ($tbl) ){
					if(trim($val)==null){continue;}
					
					if(preg_match("#ldap:(.+)#",$val,$re)){
						$val=$this->ParseLdap($re[1],$key);
					}
					$res[]=$val;
					
				}
				
			return implode(',',$res); 
				
			}
			
			
		
		
		function ParseLdap($key,$field){
			$host=$this->main_array["{$key}_server_host"];
			$port=$this->main_array["{$key}_server_port"];
			$adm=$this->main_array["{$key}_bind_dn"];
			$pass=$this->main_array["{$key}_bind_pw"];
			$suffix=$this->main_array["{$key}_search_base"];
			$query=$this->main_array["{$key}_query_filter"];
			$query=str_replace('%s',"*",$query);
			$query=str_replace('%u',"*",$query);
			$query=str_replace('%d',"*",$query);
			$return=$this->main_array["{$key}_result_attribute"];
			
			if($this->must2Fields[$field]){
				if(preg_match("#\(([a-zA-Z]+)=\*\)#",$query,$r)){
					//$res[]="$query<br>";
					$first_field=$r[1];
					$attr[]=$return;
					$attr[]=$first_field;
					$res[]="<table width=1%>
					<tr style='background-color:#CCCCCC'>
						<td nowrap><strong>$first_field</strong></td>
						<td>&nbsp;</td>
						<td nowrap><strong>$return</strong>
						</td>
					</tr>";
					}
				}else{
			 	$attr[]=$return;
			}
			
			
			
			
			$ldap_connection=ldap_connect($host,$port) ;
			ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
			ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); 		 	
			$ldapbind=ldap_bind($ldap_connection, $adm, $pass);		
			$sr =@ldap_search($ldap_connection,$suffix,$query,$attr);
			if(!$sr){
				return "failed $query=>$return";
			}
			
			$hash=ldap_get_entries($ldap_connection,$sr);
			if($hash["count"]==0){return null;}
			for($i=0;$i<$hash["count"];$i++){
				
					if($this->must2Fields[$field]){
						$res[]=MultipleFields($first_field,$return,$hash[$i]);
					}else{
				
						for($z=0;$z<$hash[$i][strtolower($return)]["count"];$z++){
							$res[]=$hash[$i][strtolower($return)][$z];
							}
					}
				
			}
			if($this->must2Fields[$field]){
				$res[]="</table>";
				return implode("\n",$res);
			}
			return implode(',',$res);
			
		}
	
	
	
	
	
}


function MultipleFields($firstField,$secondfield,$array){

	$first="<table style='width:100%'>";
	for($i=0;$i<$array[strtolower($firstField)]["count"];$i++){
		$first=$first."
		<tr>
		<td nowrap>{$array[strtolower($firstField)][$i]}</td>
		</tr>";
		
	}
	
	$first=$first."</table>";
	
	$html="<tr>
	<td nowrap style='border-bottom:1px solid #CCCCCC' valign='top'>$first</td>
	<td width=1% style='border-bottom:1px solid #CCCCCC' valign='top'>&nbsp;</td>
	<td nowrap style='border-bottom:1px solid #CCCCCC' valign='top'>
		<table style='width:100%'>";
	
	if($array[strtolower($secondfield)]["count"]==0){return null;}
	for($i=0;$i<$array[strtolower($secondfield)]["count"];$i++){
		$html=$html . "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td nowrap>{$array[strtolower($secondfield)][$i]}</td></tr>";
		
	}
	$html=$html."</table>";
	
	return $html;
	
}


class main_perso{
	var $main_array=array();
	var $dn;
	var $list;
	
	function main_perso(){
		if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}else{$GLOBALS["AS_ROOT"]=false;}
		$this->load_array();
	}
	
	
	
	
	function add($key,$value,$type){
		$q=new mysql();
		$key=strtolower(trim($key));
		$sql="SELECT `postfix_key` FROM postfix_perso_settings WHERE postfix_key='$key'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["postfix_key"]<>null){
			$sql="UDPATE postfix_perso_settings SET postfix_value='$value',postfix_type='$type' WHERE postfix_key='$key'";
		}else{
			$sql="INSERT INTO postfix_perso_settings (postfix_key,postfix_type,postfix_value) VALUES('$key','$type','$value')";
		}
		
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\nfile: ".__FILE__."\nline:".__LINE__;return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-perso-settings=yes");
		
		
	}
	
	
	function load_array(){
		
		$sql="SELECT * FROM postfix_perso_settings ORDER BY postfix_key";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$res[$ligne["postfix_key"]]=array("VALUE"=>$ligne["postfix_value"],"TYPE"=>$ligne["postfix_type"]);
			
		}
		
		$this->main_array=$res;
		
		
	}
	
	function Delete($key){
		$sql="DELETE FROM postfix_perso_settings WHERE postfix_key='$key'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){echo $q->mysql_error."\nfile: ".__FILE__."\nline:".__LINE__;return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-perso-settings=yes");
		
	}
	
function replace_conf($conf){
		if(!is_array($this->main_array)){
			if($GLOBALS["AS_ROOT"]){echo "Starting......: Postfix [PERSO]: No array, no config to replace, aborting\n";}
			writepostfixlogs("MAINPERSO:: No array, no config to replace, aborting");
			return $conf;}
			
		if(count($this->main_array)==0){
			if($GLOBALS["AS_ROOT"]){echo "Starting......: Postfix [PERSO]: No array, no config to replace, aborting\n";}
			return;
		}
		
		if(!isset($GLOBALS["postconf"])){
			$unix=new unix();
			$GLOBALS["postconf"]=$unix->find_program("postconf");
		}
		
		if(is_array($this->main_array)){
			if(count($this->main_array)>0){		
				reset($this->main_array);
				while (list ($num, $val) = each ($this->main_array) ){
					if(isset($ALREADY[$num])){continue;}
					$ALREADY[$num]=true;
					if($val["VALUE"]==null){
						if($GLOBALS["AS_ROOT"]){echo "Starting......: Postfix [PERSO]: No value for $num, skip\n";}
						continue;
					}
					if($GLOBALS["AS_ROOT"]){echo "Starting......: Postfix [PERSO]: $num = {$val["VALUE"]}\n";}
					system("{$GLOBALS["postconf"]} -e \"$num = {$val["VALUE"]}\" >/dev/null 2>&1");
				}
			}
		}
	}

}

function bouce_template_file_default(){}


class bounces_templates{
	var $templates_array;

	
	function bounces_templates(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_INFO('PostfixBounceTemplatesIniConf'));
		$this->templates_array=$ini->_params;
		$this->templates_array["failure_template"]["Body"]=$sock->GET_INFO("postfix_failure_template");
		$this->templates_array["delay_template"]["Body"]=$sock->GET_INFO("postfix_delay_template");
		$this->templates_array["success_template"]["Body"]=$sock->GET_INFO("postfix_success_template");
		$this->templates_array["verify_template"]["Body"]=$sock->GET_INFO("postfix_verify_template");
		$this->BuildDefaults();
		
		
	}
	
	
	function BuildDefaults(){
		if($this->templates_array["failure_template"]["Charset"]==null){$this->templates_array["failure_template"]["Charset"]="us-ascii";}
		if($this->templates_array["failure_template"]["From"]==null){$this->templates_array["failure_template"]["From"]="MAILER-DAEMON (Mail Delivery System)";}
		if($this->templates_array["failure_template"]["Subject"]==null){$this->templates_array["failure_template"]["Subject"]="Undelivered Mail Returned to Sender";}
		if($this->templates_array["failure_template"]["Postmaster-Subject"]==null){$this->templates_array["failure_template"]["Postmaster-Subject"]=$this->templates_array["failure_template"]["Subject"];}
		
		
		if($this->templates_array["failure_template"]["Body"]==null){
			$this->templates_array["failure_template"]["Body"]=
			"This is the mail system at host \$myhostname.\n\nI'm sorry to have to inform you that your message could not\nbe delivered to one or more recipients. It's attached below.\n\nFor further assistance, please send mail to postmaster.\n\nIf you do so, please include this problem report. You can\ndelete your own text from the attached returned message.\n\n";
		}
		
		
		
		if($this->templates_array["delay_template"]["Charset"]==null){$this->templates_array["delay_template"]["Charset"]="us-ascii";}
		if($this->templates_array["delay_template"]["From"]==null){$this->templates_array["delay_template"]["From"]="MAILER-DAEMON (Mail Delivery System)";}
		if($this->templates_array["delay_template"]["Subject"]==null){$this->templates_array["delay_template"]["Subject"]="Delayed Mail (still being retried)";}
		if($this->templates_array["delay_template"]["Postmaster-Subject"]==null){$this->templates_array["delay_template"]["Postmaster-Subject"]=$this->templates_array["failure_template"]["Subject"];}
		
		
		if($this->templates_array["delay_template"]["Body"]==null){
			$this->templates_array["delay_template"]["Body"]=
			"\nThis is the mail system at host \$myhostname.\n\n####################################################################\n# THIS IS A WARNING ONLY.  YOU DO NOT NEED TO RESEND YOUR MESSAGE. #\n####################################################################\n\nYour message could not be delivered for more than \$delay_warning_time_hours hour(s).\nIt will be retried until it is \$maximal_queue_lifetime_days day(s) old.\n\nFor further assistance, please send mail to postmaster.\n\nIf you do so, please include this problem report. You can\ndelete your own text from the attached returned message.\n\n";
		}

		
		if($this->templates_array["success_template"]["Charset"]==null){$this->templates_array["success_template"]["Charset"]="us-ascii";}
		if($this->templates_array["success_template"]["From"]==null){$this->templates_array["success_template"]["From"]="MAILER-DAEMON (Mail Delivery System)";}
		if($this->templates_array["success_template"]["Subject"]==null){$this->templates_array["success_template"]["Subject"]="Successful Mail Delivery Report";}
		if($this->templates_array["success_template"]["Postmaster-Subject"]==null){$this->templates_array["success_template"]["Postmaster-Subject"]=$this->templates_array["failure_template"]["Subject"];}
		
		
		if($this->templates_array["success_template"]["Body"]==null){
			$this->templates_array["success_template"]["Body"]=
			"\nThis is the mail system at host \$myhostname.\n\nYour message was successfully delivered to the destination(s)\nlisted below. If the message was delivered to mailbox you will\nreceive no further notifications. Otherwise you may still receive\nnotifications of mail delivery errors from other systems.\n\n                   The mail system\n;";
		}

		if($this->templates_array["verify_template"]["Charset"]==null){$this->templates_array["verify_template"]["Charset"]="us-ascii";}
		if($this->templates_array["verify_template"]["From"]==null){$this->templates_array["verify_template"]["From"]="MAILER-DAEMON (Mail Delivery System)";}
		if($this->templates_array["verify_template"]["Subject"]==null){$this->templates_array["verify_template"]["Subject"]="Mail Delivery Status Report";}
		if($this->templates_array["verify_template"]["Postmaster-Subject"]==null){$this->templates_array["verify_template"]["Postmaster-Subject"]=$this->templates_array["failure_template"]["Subject"];}
		
		
		if($this->templates_array["verify_template"]["Body"]==null){
			$this->templates_array["verify_template"]["Body"]=
			"\nThis is the mail system at host \$myhostname.\n\nEnclosed is the mail delivery report that you requested.\n\n                   The mail system\n";
		}			
		
		
	}
	
	
	function Save(){
		$sock=new sockets();
		$conf="#Bounce templates for postfix saved ".date('Y-m-d H:i:s')."\n";
		while (list ($num, $ligne) = each ($this->templates_array) ){
			
			$tp=explode("\n",$ligne["Body"]);
			$Body=null;
			while (list ($a, $line) = each ($tp) ){
				if(trim($line)==null){continue;}
				$Body=$Body.$line."\n";
			}
			
			$sock->SaveConfigFile($Body,"postfix_$num");
			$conf=$conf ."\n$num = <<EOF\n";
			$conf=$conf ."Charset: {$ligne["Charset"]}\n";
			$conf=$conf ."From:  {$ligne["From"]}\n";
			$conf=$conf ."Subject: {$ligne["Subject"]}\n";
			$conf=$conf ."\n";
			$conf=$conf ."$Body";
			$conf=$conf ."\n\n";
			$conf=$conf ."EOF\n";
			unset($this->templates_array[$num]["Body"]);
		}
		
		
		reset($this->templates_array);
		$ini=new Bs_IniHandler();
		$ini->_params=$this->templates_array;
		$PostfixBounceTemplatesIniConf=$ini->toString();
		$sock->SaveConfigFile($PostfixBounceTemplatesIniConf,"PostfixBounceTemplatesIniConf");
		$sock->SaveConfigFile($conf,"PostfixBounceTemplatesFileContent");
		
		
	}
	
	
}







	
	
	

	
	
	
?>