<?php
	include_once(dirname(__FILE__).'/class.ldap.inc');
class articaSMTPSync{
	var $serverList=array();
	function articaSMTPSync(){
		$this->BuildBranch();
		$this->serverList=$this->ListServer();
	}
	
	private function BuildBranch(){
		$ldap=new clladp();
		$dn="cn=artica_smtp_sync,cn=artica,$ldap->suffix";
		
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"][]="artica_smtp_sync";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error."\nFile: ".__FILE__."\nLine:".__LINE__;return;}
		}
		
	}
	
	public function Add($servername,$port,$username,$password){
		$ldap=new clladp();
		$dn="cn={$servername}:{$port},cn=artica_smtp_sync,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="ArticaSMTPSyncDB";
			$upd["cn"][]="{$servername}:{$port}";
			$upd["ArticaSMTPSyncCreds"][0]="$username:$password";
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error."\nFile: ".__FILE__."\nLine:".__LINE__;return false;}
			return true;
		}
		
		$upd["ArticaSMTPSyncCreds"][0]="$username:$password";
		if(!$ldap->Ldap_modify($dn,$upd)){echo $ldap->ldap_last_error."\nFile: ".__FILE__."\nLine:".__LINE__;return false;}
		return true;
	}
	
	public function Delete($server){
		$ldap=new clladp();
		$dn="cn=$server,cn=artica_smtp_sync,cn=artica,$ldap->suffix";
		$ldap=new clladp();
		if(!$ldap->ldap_delete($dn,true)){echo $ldap->ldap_last_error."\nFile: ".__FILE__."\nLine:".__LINE__;return false;}
		
	}
	
	
	private function ListServer(){
		$ldap=new clladp();
		$pattern="(&(objectClass=ArticaSMTPSyncDB)(cn=*))";
		$attr=array();
		$sr =@ldap_search($ldap->ldap_connection,"cn=artica,$ldap->suffix",$pattern,$attr);
		if(!$sr){return array();}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==0){return array();}
		for($i=0;$i<$hash["count"];$i++){
			preg_match("#(.+?):(.+)#",$hash[$i]["cn"][0],$re);
			preg_match("#(.+?):(.+)#",$hash[$i]["articasmtpsynccreds"][0],$ri);
			$array[$re[1]]=array("PORT"=>$re[2],"user"=>$ri[1],"password"=>$ri[2],"users"=>$this->CountUsers($hash[$i]["cn"][0]));
			
		}
		
		return $array;
		}
		
	private function CountUsers($server){
		$ldap=new clladp();
		$pattern="(&(objectClass=InternalRecipients)(cn=*))";
		$attr=array("cn");
		$dn="cn=$server,cn=artica_smtp_sync,cn=artica,$ldap->suffix";
		$sr =@ldap_search($ldap->ldap_connection,"$dn",$pattern,$attr);
		if(!$sr){return 0;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==null){$hash["count"]=0;}
		return $hash["count"];
	}
		
	public function GetUsers(){
		$ldap=new clladp();
		$attr=array("mail","SenderCanonical","mailAlias");
		$pattern="(&(objectClass=userAccount)(cn=*))";
		$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$pattern,$attr);
		if(!$sr){writelogs("Error SR $ldap->ldap_last_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		writelogs("{$hash["count"]} entries",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($hash["count"]==0){return array();}
		for($i=0;$i<$hash["count"];$i++){
			$array[]=$hash[$i]["mail"][0];
			if($hash[$i]["sendercanonical"][0]<>null){$array[]=$hash[$i]["sendercanonical"][0];}
			for($t=0;$t<$hash[$i]["mailalias"]["count"];$t++){
				$array[]=$hash[$i]["mailalias"][$t];
			}
		}
		return $array;
	}
	
	public function import($array,$server){
		if(!is_array($array)){
			writelogs("Not an array ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		
		
		if(!preg_match("#(.+?):(.+)#",$server,$re)){
			writelogs("unable to preg_match $server",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;}
			$serverip=$re[1];
			$serverport=$re[2];
			$ldap=new clladp();
			$dn="cn=$server,cn=artica_smtp_sync,cn=artica,$ldap->suffix";
			if(!$ldap->ExistsDN($dn)){
				writelogs("$dn does not exists",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return null;}
				
			$dn="cn=table,$dn";
			if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn,true);}
		
		
		
		if(!$ldap->ExistsDN($dn)){
			writelogs("Create new $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$upd["objectClass"][]="top";
			$upd["objectClass"][]="PostFixStructuralClass";
			$upd["cn"][]="table";
			if(!$ldap->ldap_add($dn,$upd)){writelogs($ldap->ldap_last_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
			unset($upd);
		}
		
		writelogs("$server: receive ". count($array). " entries",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		reset($array);
		
		
		for($t=0;$t<count($array);$t++){
			$mail=$array[$t];
			writelogs("$server:[$t] <$mail>",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($ARR[$mail]){
				writelogs("$server: $mail SKIP",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				continue;
			}
			
			$dnu="cn=$mail,$dn";
			
			if($ldap->uid_from_email($mail)<>null){
				writelogs("$mail -> already exists, skip it",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$ARR[$mail]=true;
				continue;
			}
			
			if(!$ldap->ExistsDN($dnu)){
				$ARR[$mail]=true;
				$upd["objectClass"][]="top";
				$upd["objectClass"][]="InternalRecipients";
				$upd["cn"][]="$mail";
				$upd["ArticaSMTPSenderTable"][0]="smtp:[$serverip]";
				writelogs("$mail -> smtp:[$serverip]",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(!$ldap->ldap_add($dnu,$upd)){writelogs($ldap->ldap_last_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				unset($upd);
			}
		}
		
		
	}
	
	
	
}
?>