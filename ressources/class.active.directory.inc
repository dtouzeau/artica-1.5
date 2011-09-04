<?php
include_once(dirname(__FILE__).'/class.mysql.inc');
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
	var $members=array();
	var $ARRAY_MEMBERS=array();
	
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
					$called=null;
					if(function_exists("debug_backtrace")){$trace=debug_backtrace();$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";}
					$errornumber= ldap_errno($this->ldap_connection);
					$error= "ERROR number : $errornumber\nWarning\n unable to connect with following\n $this->ldap_admin,$this->suffix ($this->ldap_password) \n";
					writelogs("ERROR unable to connect to ldap server ldap://{$this->ldap_host}:{$this->ldap_port} right username $this->ldap_admin,$this->suffix $called"
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
			}else{

				//writelogs("Connection success on ldap://{$this->ldap_host}:{$this->ldap_port} $called", __CLASS__ . "/" . __FUNCTION__,__FILE__,__LINE__);
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

	function Ldap_list($dn,$filter,$attrs,$maxRow=0){
		if(!$this->ldap_connection){
			writelogs("Could not search without proper ldap connection", __CLASS__ . "/" . __FUNCTION__,__FILE__);
			return ;
			}
		if(!is_array($attrs)){
			$sr =@ldap_list($this->ldap_connection,$dn,$filter,array(),0,$maxRow);
			}else{$sr =@ldap_list($this->ldap_connection,$dn,$filter,$attrs,0,$maxRow);}
		if ($sr) {
			return ldap_get_entries($this->ldap_connection,$sr);
		}else{
			$this->ldap_last_error='Error LDAP search number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP search\ndn:$dn\n$filter\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   			writelogs("ERROR Ldap_search : $filter in $dn" . ldap_err2str(ldap_errno($this->ldap_connection)),__CLASS__ . "/" . __FUNCTION__,__FILE__);return false;}
		return array();}		
	
	function UserSearch($ou=null,$tofind){
			$uid=$_SESSION["uid"];
			if($tofind==null){$tofind='*';}else{$tofind="$tofind*";}
			$filter="(&(objectClass=user)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(samaccountname=$tofind) (givenname=$tofind)))";
			$attrs=array("displayName","samaccountname","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber");
			if($ou==null){$dn=$this->suffix;}else{$dn="ou=$ou,$this->suffix";}
			//writelogs("Ldap_search : $filter in $dn", __CLASS__ . "/" . __FUNCTION__,__FILE__);
			$hash=$this->find_users($ou,$tofind);
			
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
		$res=$this->hash_get_ou_mysql($has_key);
		if(count($res)>0){return $res;}
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
				$ou=trim($ligne["ou"][0]);
				if($ou==null){continue;}
				if($has_key){$res[$ou]=$ou;}else{$res[]=$ou;}
			}
		}
			
			
		
		return $res;
		
		}
		
	public function find_members($tofind){
		$hash=$this->find_users(null,$tofind);
		for($i=0;$i<$hash["count"];$i++){
			$this->members[]=$hash[$i]["samaccountname"][0];
			$this->ARRAY_MEMBERS[$hash[$i]["samaccountname"][0]]=$hash[$i]["samaccountname"][0];
		}
		
	}		
		
	public function find_users($ou,$tofind){
			$hash=$this->find_users_mysql($ou,$tofind);
			writelogs("search $tofind in $dn Mysql return {$hash["count"]} elements ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($hash["count"]>0){return $hash;}
		
			$tofind=str_replace("***", "*", $tofind);
			$tofind=str_replace("**", "*", $tofind);
			$cc=new ldapAD();
			$dn="$cc->suffix";
			
			$filter="(&(objectClass=user)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(sAMAccountName=$tofind) (givenname=$tofind)))";
			$attrs=array("displayName","samaccountname","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber");
			if(trim($ou)=="Domain Controllers"){$dn="CN=Users,$cc->suffix";}
			writelogs("search $tofind in $dn ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$hash=$cc->Ldap_search($dn,$filter,$attrs,150);	
			if(!is_array($hash)){return array();}
			return $hash;
			
		}
		
	private function find_users_mysql($ou,$tofind){
		if($tofind=="*"){$tofind=null;}
		$tofind=str_replace("*", "%", $tofind);
		if($ou<>null){$sql_ou=" AND ou='$ou'";}
		
		
		$sql="SELECT * FROM activedirectory_users WHERE ((samaccountname LIKE '$tofind' $sql_ou) 
		OR (displayName LIKE '$tofind' $sql_ou)
		OR (userprincipalname LIKE '$tofind' $sql_ou)
		OR (displayname LIKE '$tofind' $sql_ou))
		ORDER BY samaccountname
		LIMIT 0,150";
		
		if($tofind==null){
			if($ou<>null){
				$sql="SELECT * FROM activedirectory_users WHERE ou='$ou' LIMIT 0,150";
			}
		}
		
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		if(!$q->ok){	writelogs("\n\n$sql\n\n$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$hash["count"]=mysql_num_rows($results);
		if(mysql_num_rows($results)==0){
			writelogs("0 elements for \n\n$sql\n\n",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$c=0;
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$hash[$c]["samaccountname"][0]=$ligne["samaccountname"];
			$hash[$c]["displayname"][0]=$ligne["displayname"];	
			$hash[$c]["telephonenumber"][0]=$ligne["telephonenumber"];
			$hash[$c]["mail"][0]=$ligne["mail"];
			$hash[$c]["givenname"][0]=$ligne["givenname"];	
			$hash[$c]["title"][0]=$ligne["title"];	
			$hash[$c]["sn"][0]=$ligne["sn"];			
			$hash[$c]["dn"]=$ligne["dn"];	
		
			
			$c++;
			
		}
		
		return $hash;
	}
		
	
	
	
public function hash_get_groups_from_ou_mysql($ou,$pattern){
	$oupattern=str_replace("**", "*", $pattern);
	$oupattern=str_replace("*", "%", $oupattern);
	
	
	if($oupattern<>null){$sql_ou="AND activedirectory_groupsNames.groupname LIKE '$oupattern'";}
		$sql="SELECT activedirectory_groups.groupdn,
		activedirectory_groupsNames.groupname,
		activedirectory_users.ou
		FROM activedirectory_groups,activedirectory_users,activedirectory_groupsNames
		WHERE
		activedirectory_users.dn=activedirectory_groups.userdn AND activedirectory_groups.groupdn=activedirectory_groupsNames.dn
		AND activedirectory_users.ou='$ou'
		$sql_ou
		GROUP BY activedirectory_groups.groupdn,
		activedirectory_groupsNames.groupname,
		activedirectory_users.ou
		ORDER BY activedirectory_groupsNames.groupname LIMIT 0,50";
		
		
		
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("\n\n$sql\n\n$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		if(mysql_num_rows($results)==0){writelogs("\n\n$sql\n\n",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}else{
			writelogs(mysql_num_rows($results)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$res[$ligne["groupdn"]]=$ligne["groupname"];
			
		}

		return $res;	
}	

public function find_members_groups($dn,$pattern){
	$sql="SELECT activedirectory_users.samaccountname FROM activedirectory_users,activedirectory_groups
	WHERE activedirectory_users.dn=activedirectory_groups.userdn AND activedirectory_groups.groupdn='$dn'
	";
	
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){	writelogs("\n\n$sql\n\n$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		writelogs("$dn -> " .mysql_num_rows($result),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$this->members[]=$ligne["samaccountname"];
			$this->ARRAY_MEMBERS[$ligne["samaccountname"]]=$ligne["samaccountname"];
		}	
	
	
	
}

public function COUNT_DE_USERS(){
	$q=new mysql();
	return $q->COUNT_ROWS("activedirectory_users", "artica_backup");
}


public function CountDeUSerOu($ou){
		$ou=utf8_encode($ou);
		$sql="SELECT COUNT(samaccountname) as tcount FROM activedirectory_users WHERE ou='$ou'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if(!$q->ok){writelogs("$sql -> $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		writelogs("$sql -> {$ligne["tcount"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $ligne["tcount"];
}
	
	
	

private function hash_get_ou_mysql($has_key=false){		
		$res=array();
		$sql="SELECT ou,dn FROM activedirectory_orgs WHERE enabled=1 ORDER BY ou";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ou=utf8_decode($ligne["ou"]);
			if($has_key){$res[$ou]=$ou;}else{$res[]=$ou;}
			
		}

		return $res;
}
		
	
	
}