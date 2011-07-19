<?php

class ldapAD{
	
	var $ldapbind;
	var $ldap_connection;
	var $ldap_admin="";
	var $ldap_password="";
	var $suffix="";
	var $ldap_host="";
	var $ldap_port=389;
	var $ldap_last_error="";
	var $ldapFailed=false;
	
	function ldapAD(){
		
		if(isset($GLOBALS["LDAP_AD_FAILED"])){
			if($GLOBALS["LDAP_AD_FAILED"]){return;}
		}
		
		if(!isset($GLOBALS["SETTINGS_INC"]["ActiveDirectoryCredentials"])){
			$users=new usersMenus();
			$GLOBALS["SETTINGS_INC"]["ActiveDirectoryCredentials"]=$users->ActiveDirectoryCredentials;
		}
		$creds=unserialize($GLOBALS["SETTINGS_INC"]["ActiveDirectoryCredentials"]);
		$this->ldap_admin=$creds["bind_dn"];
		$this->ldap_password=$creds["password"];
		$this->suffix=$creds["suffix"];
		$this->ldap_host=$creds["host"];
		$this->ldap_port=389;
		
		$fp=@fsockopen($this->ldap_host, $this->ldap_port, $errno, $errstr, 2);
		if(!$fp){
			writelogs("ERROR unable to connect to ldap server in line ". __LINE__." $this->ldap_host, $this->ldap_port \"$errstr\"",
			__CLASS__."/" .__FUNCTION__,__FILE__);
			$this->ldap_last_error=$errstr;$this->ldapFailed=true;
			@fclose($fp);
			$GLOBALS["LDAP_AD_FAILED"]=true;
			return false;
		}		
		
		
		$this->ldap_connection=ldap_connect($this->ldap_host, $this->ldap_port );
		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 		 
		$this->ldapbind=@ldap_bind($this->ldap_connection, "$this->ldap_admin,$this->suffix",$this->ldap_password);

			if(!$this->ldapbind){
					$errornumber= ldap_errno($this->ldap_connection);
					$error= "ERROR number : $errornumber\nWarning\n unable to connect with following\n $this->ldap_admin,$this->suffix ($this->ldap_password) \n";
					writelogs("ERROR unable to connect to ldap server ldap://{$this->ldap_host}:{$this->ldap_port} right username $this->ldap_admin,$this->suffix"
					,__CLASS__."/" .__FUNCTION__,__FILE__);	
				switch (ldap_errno($this->ldap_connection)) {
					case 0x31:
						$error=$error . "\nBad username or password. Please try again.";
						break;
					case 0x32:
						$error=$error . "\nInsufficient access rights.";
						break;
					case 81:
						$error=$error . "\nUnable to connect to the LDAP server\n
						$this->ldap_host:$this->ldap_port\nplease,\nverify if ldap daemon is running\n or the ldap server address";
						break;						
					case -1:
						
						break;
					default:
						$error=$error . "\nCould not bind to the LDAP server." ."\n". ldap_err2str($this->ldap_connection);
 				}					
					
					
				$this->ldap_last_error=$error;
				$this->ldapFailed=true;
				return false;
			}
			$GLOBALS["LDAP_AD_FAILED"]=false;
			return true;
		
	}
	
	function Ldap_search($dn,$filter,$attrs,$maxRow=0){
		if(!$this->ldap_connection){
			writelogs("Could not search without proper ldap connection", __CLASS__ . "/" . __FUNCTION__,__FILE__);
			return ;
			}
		if(!is_array($attrs)){
			$sr =@ldap_search($this->ldap_connection,$dn,$filter,array(),0,$maxRow);
			}else{$sr =@ldap_search($this->ldap_connection,$dn,$filter,$attrs,0,$maxRow);}
		if ($sr) {
			return ldap_get_entries($this->ldap_connection,$sr);
		}else{
			$this->ldap_last_error='Error LDAP search number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP search\ndn:$dn\n$filter\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   			writelogs("ERROR Ldap_search : $filter in $dn" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);return false;}
		return array();}	
	
	function UserSearch($ou=null,$tofind){
			$uid=$_SESSION["uid"];
			if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
			$filter="(&(objectClass=user)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(samaccountname=$tofind) (givenname=$tofind)))";
			$attrs=array("displayName","samaccountname","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber");
			if($ou==null){$dn=$this->suffix;}else{$dn="ou=$ou,$this->suffix";}
			writelogs("Ldap_search : $filter in $dn", __CLASS__ . "/" . __FUNCTION__,__FILE__);
			$hash=$this->Ldap_search($dn,$filter,$attrs,20);
			
			$hash2=array();	
			return array($hash,$hash2);

		}

		function _Get_dn_userid($userid){
			if($userid==null){return null;}
			$sr =@ldap_search($this->ldap_connection,$this->suffix,"(sAMAccountName=$userid)");
			if($sr){$result = ldap_get_entries($this->ldap_connection, $sr);return trim($result[0]["dn"]);}
			else{
				writelogs("Unable to find dn (sAMAccountName=$userid)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
		function hash_groups($ou,$fieldlist=0){
		
		if($ou==null){
			writelogs("OU NULL !",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return array();
		}
		$pos=strpos($ou,',');
		if($pos>0){$ou=substr($ou,0,$pos);}
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$cols=array("cn","description",'sAMAccountName',"dn");
		$dn="ou=$ou,$this->suffix";
		if($ou=="Domain Controllers"){$dn="CN=Users,$this->suffix";}
		
		
		$sr = @ldap_search($ld,"$dn",'objectclass=group',$cols);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			if(!is_array($hash)){return array();}
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["samaccountname"][0]<>null){
					switch ($fieldlist) {
						case 1:$res[$ligne["dn"]]=$ligne["samaccountname"][0];break;
						case 2;$res=$res . "{$ligne["dn"]};{$ligne["samaccountname"][0]}\n";break;
						default:$res[$ligne["cn"][0]]=array("gid"=>$ligne["dn"],"description"=>$ligne["description"][0]);break;
					}
				}
				
			}
		}else{
			writelogs("Unable to find groups in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		writelogs(count($res). " groups found for $ou ($dn)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $res;	
		}

		
	public function Hash_GetALLUsers(){
		$filter="(objectClass=user)";
		$attrs=array("samaccountname","mail","userprincipalname");
		$dn="$this->suffix";
		
	$sr =@ldap_search($this->ldap_connection,"$this->suffix",$filter,$attrs);
			if ($sr) {
				$hash=ldap_get_entries($this->ldap_connection,$sr);			
				for($i=0;$i<$hash["count"];$i++){
					$userid=$hash[$i]["samaccountname"][0];
					if(substr($userid,strlen($userid)-1,1)=='$'){continue;}
					$mail=$hash[$i]["mail"][0];
					$userprincipalname=$hash[$i]["userprincipalname"][0];
					if($mail==null){
						if(preg_match("#.+?@.+#",$userprincipalname)){$mail=$userprincipalname;}
					}
					$res[$userid]=$mail;
					}
				}else{
					return array();
				}
	
		return $res;	
		
	}		
		
		

function hash_get_ou($has_key=false){
		
		$ld =$this->ldap_connection;
		$bind =$this->ldapbind;
		$suffix="$this->suffix";
		$res=array();
		$arr=array("ou");
		$sr = @ldap_list($ld,$suffix,'(&(objectclass=organizationalUnit)(ou=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			if(!is_array($hash)){return array();}
			
			while (list ($num, $ligne) = each ($hash) ){
				$ou=$ligne["ou"][0];
				if($ou<>null){
					  	if($has_key){$res[$ou]=$ou;}else{$res[]=$ou;}
						}
					}
				}
			
			
		
		return $res;
		
		}		
	
}