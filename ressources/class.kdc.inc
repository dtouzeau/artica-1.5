<?php

class kdc{
	var $suffix_domain=null;
	var $netbios_servername=null;
	var $administrator=null;
	var $password=null;
	var $wintype=null;
	function kdc(){
		
		
	}
	
	
	public function build(){
		$this->krb5_conf();
		$this->kdc_conf();
		$this->kadm5_acl();
		$this->klist();
	}
	
	
	private function krb5_conf(){
		$domain=strtoupper($this->suffix_domain);	
		$server=strtoupper($this->netbios_servername);
		$full_servername=strtolower(strtolower($server).".".strtolower($domain));
		$full_servernameUpper=strtoupper($full_servername);
		$conf[]="[libdefaults]";
		$conf[]="\tdefault_realm = $domain";
		$conf[]="\tdns_lookup_realm = false";
		$conf[]="\tdns_lookup_kdc = false";
		$conf[]="\tticket_lifetime = 24h";
		$conf[]="\tforwardable = yes";
		if($this->wintype=="WIN_2008AES"){
			$conf[]="\tdefault_tgs_enctypes = arcfour-hmac-md5 des-cbc-crc des-cbc-md5 des3-hmac-sha1";
  			$conf[]="\tdefault_tkt_enctypes = arcfour-hmac-md5 des-cbc-crc des-cbc-md5 des3-hmac-sha1";
		}else{
			$conf[]="\tdefault_tgs_enctypes = DES-CBC-CRC DES CBC-MD5 RC4-HMAC";
			$conf[]="\tdefault_tkt_enctypes = DES-CBC-CRC DES-CBC-MD5 RC4-HMAC";
			$conf[]="\tpreferred_enctypes = DES-CBC-CRC DES-CBC-MD5 RC4-HMAC";
		}
		$conf[]="";
		$conf[]="[realms]";
		$conf[]="\t$domain = {";
		$conf[]="\t\tkdc = $full_servername";
		$conf[]="\t\tadmin_server = $full_servername";
		$conf[]="\t\tdefault_domain = ".strtolower($domain);
		$conf[]="\t}";
		$conf[]="";
		$conf[]="[domain_realm]";
		$conf[]="\t.kerberos.server=".strtoupper($this->suffix_domain);
		$conf[]="\t.".strtolower("$this->suffix_domain")."=" .strtoupper($this->suffix_domain);
		$conf[]="";
		$conf[]="[kdc]";
		$conf[]="\tprofile = /etc/kdc.conf";
		$conf[]="";
		$conf[]="[appdefaults]";
		$conf[]="\tpam = {";
		$conf[]="\t\tdebug = false";
		$conf[]="\t\tticket_lifetime = 36000";
		$conf[]="\t\trenew_lifetime = 36000";
		$conf[]="\t\tforwardable = true";
		$conf[]="\t\tkrb4_convert = false";
		$conf[]="\t}";		
		
		$conf[]="";
		if($GLOBALS["AS_ROOT"]){echo "Starting......: Kerberos: saving /etc/krb5.conf\n";}
		@file_put_contents("/etc/krb5.conf",@implode("\n",$conf));		
		
	}
private function kdc_conf(){
	$domain=strtoupper($this->suffix_domain);		
	$conf[]="[kdcdefaults]";
	$conf[]="\tkdc_ports = 750,88";
	$conf[]="\tacl_file = /etc/kadm5.acl";
	$conf[]="\tdict_file = /usr/share/dict/words";
	$conf[]="\tadmin_keytab = /etc/kadm5.keytab";
	$conf[]="\tv4_mode = noreauth";
	$conf[]="[libdefaults]";
	$conf[]="\tdefault_realm = $domain.";
	$conf[]="[realms]";
	$conf[]="\t$domain. = {";
	$conf[]="\t\tmaster_key_type = des-cbc-crc";
	$conf[]="\t\tsupported_enctypes = des3-hmac-sha1:normal arcfour-hmac:normal des-hmac-sha1:normal des-cbc-md5:normal des-cbc-crc:normal des-cbc-crc:v4 des-cbc-crc:afs3";
	$conf[]="\t}";	
	$conf[]="";
	if($GLOBALS["AS_ROOT"]){echo "Starting......: Kerberos: saving /etc/kdc.conf\n";}
	@file_put_contents("/etc/kdc.conf",@implode("\n",$conf));
}

private function kadm5_acl(){
	$domain=strtoupper($this->suffix_domain);	
	$config="*/$this->administrator@$domain\n";
	@file_put_contents("/etc/kadm.acl",$config);
	if($GLOBALS["AS_ROOT"]){echo "Starting......: Kerberos: saving /etc/kadm.acl with $this->administrator\n";}
	
}
private function klist(){
	if(!$GLOBALS["AS_ROOT"]){return;}
	$arra=array();
	$unix=new unix();
	$net=$unix->LOCATE_NET_BIN_PATH();
	$cmd="$net ads keytab list 2>&1";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#([0-9]+)\s+(.+?)\s+(.+)#", $line,$re)){
			if(!isset($arra[$re[3]])){
				$arra[$re[3]]=true;
				echo "Starting......: Kerberos: keytab {$re[3]}\n";	
			}
			
		}
	}
	
	if(count($arra)==0){
		echo "Starting......: Kerberos: keytab, create new one\n";
		$cmd="$net ads keytab create -U $this->administrator%$this->password";
	}
	
	
	
}


	
	
	
}