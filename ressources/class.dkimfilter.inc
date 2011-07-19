<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");



class dkimfilter{
	var $dn;
	var $main_array=array();
	var $global_conf;
	var $Disclaimer;

	
	function dkimfilter(){
		$this->verifyBranch();
		$this->LoadSettings();
		$this->BuildDefault();
		}
	
function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=dkim-filter,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='DkimFilterConfig';
		$upd["cn"]="dkim-filter";
		$upd["DkimFilterConf"][]=$this->BuildConf();
		$ldap->ldap_add($this->dn,$upd);
	}
}

function LoadSettings(){
		$ldap=new clladp();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=DkimFilterConfig)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->global_conf=$hash[0][strtolower('DkimFilterConf')][0];
		}
		
		$tbl=explode("\n",$this->global_conf);
		while (list ($num, $ligne) = each ($tbl) ){
			if(preg_match('#(.+?)\s+(.+)#',$ligne,$re)){
				$this->main_array[$re[1]]=$re[2];
			}
		}
		
}

function SaveConf(){
	$ldap=new clladp();
	$upd["DkimFilterConf"][]=$this->BuildConf();
	if(!$ldap->Ldap_modify($this->dn,$upd)){
		echo $ldap->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}
}

function BuildDefault(){
	if($this->main_array["PidFile"]==null){$this->main_array["PidFile"]="/var/run/dkim-filter/dkim-filter.pid";}
	if($this->main_array["Socket"]==null){$this->main_array["Socket"]="local:/var/run/dkim-filter/dkim-filter.sock";}	
	if($this->main_array["On-BadSignature"]==null){$this->main_array["On-BadSignature"]="t";}		
	if($this->main_array["On-DNSError"]==null){$this->main_array["On-BadSignature"]="a";}			
	if($this->main_array["On-InternalError"]==null){$this->main_array["On-InternalError"]="a";}				
	if($this->main_array["On-NoSignature"]==null){$this->main_array["On-NoSignature"]="a";}					
	if($this->main_array["On-SignatureMissing"]==null){$this->main_array["On-SignatureMissing"]="a";}						
	if($this->main_array["On-Security"]==null){$this->main_array["On-Security"]="a";}							
	}


	function BuildConf(){
		$this->BuildDefault();
		while (list ($num, $ligne) = each ($this->main_array) ){
			$conf=$conf ."$num\t$ligne\n";
			
		}
		
		return $conf;
		
		
	}
	
	function SaveToserver(){
		$users=new usersMenus();
		$sock=new sockets();
		$sock->getfile('dkimfiltersave');
		}






}