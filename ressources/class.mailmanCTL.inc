<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.templates.inc");
include_once(dirname(__FILE__)."/class.sockets.inc");
class mailman_control{
	var $ou=null;
	var $list_domain;
	var $list_name;
	var $admin_email;
	var $admin_password;
	var $webservername;
	var $aliases_array=array("admin","bounces","confirm","join","leave","owner","request","subscribe","unsubscribe");
	
	
	public function mailman_control($ou=null,$listname=null){
		$this->ou=$ou;
		if($listname<>null){
			$this->loadList($listname);
		}
		$this->BuildBranch();
		
	}
	
	public function GetLists_array(){
	$filter="(&(Objectclass=ArticaMailManRobots)(cn=*))";
	$ldap=new clladp();
	$sr = @ldap_search($ldap->ldap_connection,"cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix",$filter,array());
	if(!$sr){return null;}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	for($i=0;$i<$hash["count"];$i++){
		if($hash[$i][strtolower("mailManOwner")][0]==null){continue;}
		$cn=$hash[$i][strtolower("cn")][0];
		if(preg_match("#(.+?)@#",$cn,$re)){
			$listname=$re[1];
		}
		
		$array[$listname]=$hash[$i][strtolower("MailManWebServerName")][0];
		}
	return $array;
	}
	
	private function LoadList($listname){
		$ldap=new clladp();
		writelogs("Search $listname",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$dn="cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
		$filter="(&(Objectclass=ArticaMailManRobots)(cn=$listname))";
		$sr = @ldap_search($ldap->ldap_connection,"cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix",$filter,array());
		if(!$sr){
			writelogs("could not find  mailman list $listname",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
			}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash[0]["mailmanowner"][0]==null){
			writelogs("could not find  mailman list $listname",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$this->admin_email=$hash[0]["mailmanowner"][0];
		$this->admin_password=$hash[0]["mailmanownerpassword"][0];
		$this->webservername=$hash[0][strtolower("MailManWebServerName")][0];
		if(preg_match('#(.+?)@(.+)#',$listname,$re)){
			$this->list_name=$re[1];
			$this->list_domain=$re[2];
		}
		
	}
	
	public function delete(){
		$array=$this->aliases_array;
		$ldap=new clladp();
		while (list ($num, $ligne) = each ($array) ){
				$dn="cn=$this->list_name-$ligne@$this->list_domain,cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
				writelogs("checking $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if($ldap->ExistsDN($dn)){
					writelogs("delete $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					if(!$ldap->ldap_delete($dn,false)){
						echo $ldap->ldap_last_error."\nFile: ".__FILE__."\nline:".__LINE__;return;
					}
				}
			
		}
		
		$dn="cn=$this->list_name@$this->list_domain,cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
		if($ldap->ExistsDN($dn)){
			if(!$ldap->ldap_delete($dn,false)){
					echo $ldap->ldap_last_error."\nFile: ".__FILE__."\nline:".__LINE__;return;
				}
		}
		
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?restart-groupware-server=yes");
		$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
	
	}
	
	private function BuildBranch(){
		$ldap=new clladp();
		$dn="cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="mailman";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		}
		
		$dn="cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='PostFixStructuralClass';
			$upd["cn"][]="mailman";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		}		
		
		
		
	}
	
	
	public function EditList(){
		
		$array[]="admin";
		$array[]="bounces";
		$array[]="confirm";
		$array[]="join";
		$array[]="leave";
		$array[]="owner";
		$array[]="request";
		$array[]="subscribe";
		$array[]="unsubscribe";
		
		$ldap=new clladp();
		$dn="cn=$this->list_name@$this->list_domain,cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]='top';
			$upd["objectClass"][]='ArticaMailManRobots';
			$upd["MailManAliasPath"][]="\"|/var/lib/mailman/mail/mailman post $this->list_name\"";
			$upd["TransportMailmanMaps"][]='mailman:';
			$upd["cn"][]="$this->list_name@$this->list_domain";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;return false;}
			unset($upd);
		}
		
		$upd["mailManOwner"][0]=$this->admin_email;
		$upd["MailManOwnerPassword"][0]=$this->admin_password;
		$upd["MailManWebServerName"][0]=$this->webservername;
		if(!$ldap->Ldap_modify($dn,$upd)){echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;return false;}
		unset($upd);
		
		while (list ($num, $ligne) = each ($array) ){
			$dn="cn=$this->list_name-$ligne@$this->list_domain,cn=mailman,ou=$this->ou,dc=organizations,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				$upd["objectClass"][]='top';
				$upd["objectClass"][]='ArticaMailManRobots';
				$upd["MailManAliasPath"][]="\"|/var/lib/mailman/mail/mailman $ligne $this->list_name\"";
				$upd["cn"][]="$this->list_name-$ligne@$this->list_domain";
				$upd["TransportMailmanMaps"][]='mailman:';
				if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;return false;}
				unset($upd);
			}

		}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?syncro-mailman=yes");
		$sock->getFrameWork("cmd.php?restart-groupware-server=yes");
		$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
		return true;
		
	}
	
}
?>