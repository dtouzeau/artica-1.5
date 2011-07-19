<?php
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.ldap.inc');

class domains_disclaimer{
	public $ou;
	
	public $error;
	public $DisclaimerOutbound="FALSE";
	public $DisclaimerInbound="FALSE";
	public $DisclaimerUserOverwrite="FALSE";
	public $DisclaimerActivate="FALSE";
	public $DisclaimerContent="";
	public $DisclaimerExample= "<p style=\"font-size:12px\"><i>This email and its attachments may be confidential and are intended solely for the use of the individual to whom it is addressed.<br>
		 Any views or opinions expressed are solely those of the author and do not necessarily represent those of &laquo;[business name]&raquo;.<br>
		If you are not the intended recipient of this email and its attachments, you must take no action based upon them, nor must you copy or show them to anyone.<br>
		<br><br>Please contact the sender if you believe you have received this email in error.</i></p>";
	
	function domains_disclaimer($ou=null,$domain){
		$this->ou=$ou;
		$this->domain=$domain;
		$this->LoadDatas();
		if(trim($this->DisclaimerContent)==null){$this->DisclaimerContent=$this->DisclaimerExample;}
	}
	
	private function LoadDatas(){
		if($this->ou==null){$this->LoadDatasSearch();return;}
		$ldap=new clladp();
		$dn="cn=$this->domain,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			$this->DisclaimerInbound=$hash[0][strtolower("DisclaimerInbound")][0];
			$this->DisclaimerOutbound=$hash[0][strtolower("DisclaimerInbound")][0];
			$this->DisclaimerUserOverwrite=$hash[0][strtolower("DisclaimerUserOverwrite")][0];
			$this->DisclaimerContent=$hash[0][strtolower("DisclaimerContent")][0];
			$this->DisclaimerActivate=$hash[0][strtolower("DisclaimerActivate")][0];
			}
	}
	
	private function LoadDatasSearch(){
		$ldap=new clladp();
		$dn="dc=organizations,$ldap->suffix";
		$pattern="(&(objectClass=DisclaimerDomain)(cn=$this->domain))";
		$attr=array();
		$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
		if(!$sr){
			$this->error="Unable to search (&(objectClass=DisclaimerDomain)(cn=$this->domain))";
			return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==0){$this->error="No domain set";return null;}
		$this->DisclaimerInbound=$hash[0][strtolower("DisclaimerInbound")][0];
		$this->DisclaimerOutbound=$hash[0][strtolower("DisclaimerInbound")][0];
		$this->DisclaimerUserOverwrite=$hash[0][strtolower("DisclaimerUserOverwrite")][0];
		$this->DisclaimerContent=$hash[0][strtolower("DisclaimerContent")][0];
		$this->DisclaimerActivate=$hash[0][strtolower("DisclaimerActivate")][0];
		}
	
	private function BuildBranchs(){
		$ldap=new clladp();
		$dn="cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"]="disclaimers";
			if(!$ldap->ldap_add($dn,$upd)){return false;}
			unset($upd);
		}
		
		$dn="cn=$this->domain,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="DisclaimerDomain";
			$upd["cn"]="$this->domain";
			if(!$ldap->ldap_add($dn,$upd)){return false;}
		}		
		
	}
	
	public function SaveDislaimerParameters(){
		$this->BuildBranchs();
		$ldap=new clladp();
		$dn="cn=$this->domain,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		$upd["DisclaimerOutbound"][0]=$this->DisclaimerOutbound;
		$upd["DisclaimerInbound"][0]=$this->DisclaimerInbound;
		$upd["DisclaimerUserOverwrite"][0]=$this->DisclaimerUserOverwrite;
		$upd["DisclaimerActivate"][0]=$this->DisclaimerActivate;

		if(!$ldap->Ldap_modify($dn,$upd)){
			echo "$ldap->ldap_last_error\nFile:" .basename(__FILE__)."\nLine:".__LINE__;
		}
		
	}
	
	public function SaveDisclaimer(){
		$this->BuildBranchs();
		$ldap=new clladp();
		$dn="cn=$this->domain,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		$upd["DisclaimerContent"][0]=$this->DisclaimerContent;	
		if(!$ldap->Ldap_modify($dn,$upd)){
			echo "$ldap->ldap_last_error\nFile:" .basename(__FILE__)."\nLine:".__LINE__;
		}
	}
	
	
	
}

class user_disclaimer{
	public $ou;
	public $uid;
	public $error;
	public $DisclaimerOutbound="FALSE";
	public $DisclaimerInbound="FALSE";
	public $DisclaimerActivate="FALSE";
	public $DisclaimerContent="";
	private $DN;
	public $DisclaimerExample= "<p style=\"font-size:12px\"><i>This email and its attachments may be confidential and are intended solely for the use of the individual to whom it is addressed.<br>
		 Any views or opinions expressed are solely those of the author and do not necessarily represent those of &laquo;[business name]&raquo;.<br>
		If you are not the intended recipient of this email and its attachments, you must take no action based upon them, nor must you copy or show them to anyone.<br>
		<br><br>Please contact the sender if you believe you have received this email in error.</i></p>";
	
	function user_disclaimer($ou=null,$uid){
		$this->ou=$ou;
		$this->uid=$uid;
		$this->DN="cn=$this->uid,cn=disclaimers,ou=$this->ou,dc=organizations";
		$this->LoadDatas();
		if(trim($this->DisclaimerContent)==null){$this->DisclaimerContent=$this->DisclaimerExample;}
	}
	
	private function LoadDatas(){
		if($this->ou==null){$this->LoadDatasSearch();return;}
		$ldap=new clladp();
		if($this->uid==null){
			writelogs("UID is null",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$dn="cn=$this->uid,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		
		$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*');
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			$this->DisclaimerInbound=$hash[0][strtolower("DisclaimerInbound")][0];
			$this->DisclaimerOutbound=$hash[0][strtolower("DisclaimerInbound")][0];
			$this->DisclaimerContent=$hash[0][strtolower("DisclaimerContent")][0];
			$this->DisclaimerActivate=$hash[0][strtolower("DisclaimerActivate")][0];
			}
	}
	
	private function LoadDatasSearch(){
		$ldap=new clladp();
		$dn="dc=organizations,$ldap->suffix";
		$pattern="(&(objectClass=DisclaimerDomain)(cn=$this->uid))";
		$attr=array();
		$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
		if(!$sr){
			$this->error="Unable to search (&(objectClass=DisclaimerDomain)(cn=$this->uid))";
			return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==0){$this->error="No domain set";return null;}
		$this->DisclaimerInbound=$hash[0][strtolower("DisclaimerInbound")][0];
		$this->DisclaimerOutbound=$hash[0][strtolower("DisclaimerInbound")][0];
		$this->DisclaimerContent=$hash[0][strtolower("DisclaimerContent")][0];
		$this->DisclaimerActivate=$hash[0][strtolower("DisclaimerActivate")][0];
		}
	
	private function BuildBranchs(){
		$ldap=new clladp();
		$dn="cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"]="disclaimers";
			if(!$ldap->ldap_add($dn,$upd)){return false;}
			unset($upd);
		}
		
		$dn="cn=$this->uid,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="DisclaimerDomain";
			$upd["cn"]="$this->uid";
			if(!$ldap->ldap_add($dn,$upd)){return false;}
		}		
		
	}
	
	public function SaveDislaimerParameters(){
		$this->BuildBranchs();
		$ldap=new clladp();
		$dn="cn=$this->uid,cn=disclaimers,ou=$this->ou,dc=organizations,$ldap->suffix";
		$upd["DisclaimerOutbound"][0]=$this->DisclaimerOutbound;
		$upd["DisclaimerInbound"][0]=$this->DisclaimerInbound;
		$upd["DisclaimerActivate"][0]=$this->DisclaimerActivate;
		$upd["DisclaimerContent"][0]=$this->DisclaimerContent;	

		if(!$ldap->Ldap_modify($dn,$upd)){
			echo "$ldap->ldap_last_error\nFile:" .basename(__FILE__)."\nLine:".__LINE__;
		}
		
	}
	

	
	
	
}

?>