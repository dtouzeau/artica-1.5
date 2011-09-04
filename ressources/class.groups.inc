<?php
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc")){include_once(dirname(__FILE__) . '/class.templates.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.templates.inc");}
if(!is_file(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc")){include_once(dirname(__FILE__) . '/class.ldap.inc');}else{include_once(dirname($_SERVER['SCRIPT_FILENAME'])."/ressources/class.ldap.inc");}

include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.active.directory.inc');
//sambaGroupType: 2 - domain group (global group)
//sambaGroupType: 5 - local group (built-in group)
 
class groups{
	var $group_id;
	var $hash;
	var $objectClass;
	var $SharedFolders_list=array();
	var $main_array=array();
	var $SharedFolderPrefix='shared_';
	var $members=array();
	var $members_array=array();
	var $computers_array=array();
	var $ARRAY_MEMBERS=array();
	var $KasperkyASDatas;
	var $KasperskyASGroupNumber;
	var $KasperkyASDatasRules;
	var $Privileges_array=array();
	var $groupName;
	var $ou;
	var $dn;
	var $ldap_error;
	var $generated_id;
	var $sambaSID;
	var $sambaGroupType;
	var $KavMilterSettings;
	var $DefaultGroupPassword;
	var $objectClasses=array();
	var $LOCAL_SID;
	var $SAMBA_ENABLED=true;
	var $SAMBA_INSTALLED=false;
	var $objectSid=null;
	var $description=null;
	var $ldapClass;
	var $EnableManageUsersTroughActiveDirectory=false;
	var $searchPattern=null;
	function groups($groupid=null,$searchPattern=null){
		$this->searchPattern=$searchPattern;
		$users=new usersMenus();
		$sock=new sockets();
		$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
		if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}
		if($EnableManageUsersTroughActiveDirectory==1){$this->EnableManageUsersTroughActiveDirectory=true;}
		$this->SAMBA_INSTALLED=$users->SAMBA_INSTALLED;
		if($users->EnableManageUsersTroughActiveDirectory){
				writelogs(" -> LoadFromAd($groupid,$searchPattern)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->LoadFromAd($groupid,$searchPattern);
				writelogs(" -> LoadFromAd END",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return;
		}
		
		
		$sock=new sockets();
		
		if($this->SAMBA_INSTALLED){
			$SambaEnabled=trim($sock->GET_INFO("SambaEnabled"));
			if(strlen($SambaEnabled)==0){$SambaEnabled=1;}
			if($SambaEnabled<>1){$this->SAMBA_ENABLED=false;}
		}else{
			$this->SAMBA_ENABLED=false;
		}		
		
		$this->group_id=$groupid;
		$this->ldapClass=new clladp();
		if($this->ldapClass->ldapFailed){return null;}
		$this->LOCAL_SID=$this->ldapClass->LOCAL_SID();
		if($groupid<>null){
			$this->LoadDatas();
			$this->ParseClasses();
			$this->ParseSharedFolders();
			$this->BuildOrdinarySambaGroups();
		}
		
	}
	
	private function LoadFromAd($dn,$pattern=null){
		$ldap=new ldapAD();
		if($dn==null){
			$ldap->find_members($pattern);
			$this->ARRAY_MEMBERS=$ldap->ARRAY_MEMBERS;
			$this->members=$ldap->members;
			return;
		}else{
			$ldap->find_members_groups($dn,$pattern);
			$this->ARRAY_MEMBERS=$ldap->ARRAY_MEMBERS;
			$this->members=$ldap->members;			
		}
		return;
		
		$pattern=trim($pattern);
		//$this->seems_utf8($dn)
		$query="objectClass=*";
		if(trim($pattern)<>null){
			if(strpos(" $pattern", '*')==0){$pattern="*$pattern*";}
			$pattern=str_replace("**", "*", $pattern);
			$pattern=str_replace("**", "*", $pattern);
			$pattern=str_replace("*", ".*?", $pattern);
		}
		
		if($pattern<>null){
			$query="(&(objectclass=*)(|(member=$pattern)(cn=$pattern)(mailNickName=$pattern)(samAccountName=$pattern) )";
			$query="(&(objectClass=*)(|(cn=$pattern)(mail=$pattern)(displayName=$pattern)(samaccountname=$pattern) (givenname=$pattern)))";
			$query="objectClass=*";
		}
		writelogs("$query",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		ldap_set_option($ldap->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		/*$sr =@ldap_read($ldap->ldap_connection,$dn,"objectclass=*",array(), null, null, 0,0);
		if(!$sr){
			$errornumber= ldap_errno($ldap->ldap_connection);
			$error_text=ldap_err2str($errornumber);
			echo "<H1>$error_text</H1>";
			
		}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		print_r($hash);
		return;
		*/
		
		
		
		
		$sr =@ldap_search($ldap->ldap_connection,utf8_encode($dn),$query,array(), null, null, 100,0);
		if(!$sr){
			$errornumber= ldap_errno($ldap->ldap_connection);
			$error_text=ldap_err2str($errornumber);
		
			writelogs("Error $errornumber:UTF8: $error_text Query failed false query $query in ".utf8_encode($dn),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sr =@ldap_search($ldap->ldap_connection,$dn,$query,array(), null, null, 100,0);
			if(!$sr){
				$errornumber= ldap_errno($ldap->ldap_connection);
				$error_text=ldap_err2str($errornumber);	
				writelogs("Error $errornumber:NORM: $error_text Query $query failed false query in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return array();
			}
		}
		
		
		if ($sr) {
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				$hash=$hash[0];
				
				while (list ($key, $arrayGost) = each ($hash) ){
					if(preg_match("#member;range=.+?#", $key)){
						writelogs("change member key to $key",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						$hash["member"]=$arrayGost;
					}	
				}
				
				
				
				$cc=0;
				$this->groupName=$hash["samaccountname"][0];
				$this->objectSid=$this->bin_to_str_sid($hash["objectsid"][0]);
				$membersMAX=$hash["member"]["count"];
				if($membersMAX>50){$membersMAX=50;}
				
				
				for($i=0;$i<$hash["member"]["count"];$i++){
					if(!isset($_SESSION["LDAPUSERS"][$hash["member"][$i]])){
						$member=$this->MemberSamAcountFromAd($hash["member"][$i]);
						$_SESSION["LDAPUSERS"][$hash["member"][$i]]=$member;
					}else{
						$member=$_SESSION["LDAPUSERS"][$hash["member"][$i]];
					}
					
					
					
					if($member==null){continue;}
					if($pattern<>null){if(!preg_match("#$pattern#", $member)){continue;}}
					writelogs(count($this->members)."] \"$pattern\" Found {$hash["member"][$i]} = $member",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);					
					$this->ARRAY_MEMBERS[$member]=$member;
					$this->members[]=$member;
					if(count($this->members)>$membersMAX){
						writelogs("Max reached ".count($this->members)."/".$hash["member"]["count"],__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
						break;}
					}
					
				for($i=0;$i<$hash["memberof"]["count"];$i++){
					writelogs("Search members in {$hash["memberof"][$i]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$gp=new groups($hash["memberof"][$i],$pattern);
					if(count($gp->members)>0){
						while (list ($num, $ligne) = each ($gp->members) ){$this->members[]=$ligne;}
					}
				}
			}else{
				writelogs("Ressource false query (objectClass=group) in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return;
			}
		
			$f=explode("-",$this->objectSid);
			$this->group_id=$f[count($f)-1];
			$this->UsersAdByGpid($this->group_id);
			
			
			$this->dn=$dn;
			if(preg_match('#ou=(.+?),dc#i',$dn,$reg)){$res["ou"]=$reg[1];}
			if(preg_match("#,CN=Users,$ldap->suffix#i",$dn,$reg)){$res["ou"]="Domain Controllers";}
			$this->ou=$res["ou"];
			
			//clean
			
			$final_members=$this->members;
			$this->members=array();
			while (list ($num, $ligne) = each ($final_members)){
				if($ligne==null){continue;}
				$this->members_array[$ligne]=true;
				$this->members[]=$ligne;
			}
			
			writelogs("[".count($this->members)."] FINISH",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	}
	
private function seems_utf8($Str) { 
	$length = strlen($Str);
	for ($i=0; $i < $length; $i++) {
		if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
		elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($Str[$i]) & 0xC0) != 0x80))
			return false;
		}
	}
	return true;
}	
	
	private function UsersAdByGpid($gpid){
		$ldap=new ldapAD();
		$filter=array("sAMAccountName");
		$pattern="(&(primaryGroupID=$gpid)(objectclass=user))";
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern);
		if($sr){
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			for($i=0;$i<$hash["count"];$i++){			
				$array=$hash[$i];
				$this->ARRAY_MEMBERS[$array["samaccountname"][0]]=$array["samaccountname"][0];
			}
		}
	}
	
	
	private function bin_to_str_guid($object_guid) {
	    $hex_guid = bin2hex($object_guid);
	    $hex_guid_to_guid_str = '';
	    for($k = 1; $k <= 4; ++$k) {
	        $hex_guid_to_guid_str .= substr($hex_guid, 8 - 2 * $k, 2);
	    }
	    $hex_guid_to_guid_str .= '-';
	    for($k = 1; $k <= 2; ++$k) {
	        $hex_guid_to_guid_str .= substr($hex_guid, 12 - 2 * $k, 2);
	    }
	    $hex_guid_to_guid_str .= '-';
	    for($k = 1; $k <= 2; ++$k) {
	        $hex_guid_to_guid_str .= substr($hex_guid, 16 - 2 * $k, 2);
	    }
	    $hex_guid_to_guid_str .= '-' . substr($hex_guid, 16, 4);
	    $hex_guid_to_guid_str .= '-' . substr($hex_guid, 20);
	
	    return strtoupper($hex_guid_to_guid_str);
	}

	private function bin_to_str_sid($binsid) {
	    $hex_sid = bin2hex($binsid);
	    $rev = hexdec(substr($hex_sid, 0, 2));
	    $subcount = hexdec(substr($hex_sid, 2, 2));
	    $auth = hexdec(substr($hex_sid, 4, 12));
	    $result    = "$rev-$auth";
	    for ($x=0;$x < $subcount; $x++) {
	        $subauth[$x] =hexdec($this->little_endian(substr($hex_sid, 16 + ($x * 8), 8)));
	        $result .= "-" . $subauth[$x];
	    }
	    return 'S-' . $result;
	}	

	private function little_endian($hex) {
	    for ($x = strlen($hex) - 2; $x >= 0; $x = $x - 2) {$result .= substr($hex, $x, 2);}
	    return $result;
	}
	
	private function MemberSamAcountFromAd($dn){
		$ldap=new ldapAD();
				
		$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*',array("sAMAccountName","name","cn","userprincipalname"));
		//$sr =@ldap_read($ldap->ldap_connection,$dn,'objectClass=*',array());
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			
			writelogs($hash["count"]." items in $dn (objectClass=user)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$hash=$hash[0];
			if($hash["samaccountname"][0]<>null){return $hash["samaccountname"][0];}
			if($hash["name"][0]<>null){return $hash["name"][0];}
			if($hash["cn"][0]<>null){return $hash["cn"][0];}
			if($hash["userprincipalname"][0]<>null){return $hash["userprincipalname"][0];}
			
		}else{
			$errornumber= ldap_errno($ldap->ldap_connection);
			$error_text=ldap_err2str($errornumber);	
			writelogs("Error:$errornumber  $error_text in $dn (objectClass=user)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
	}
	
	
	function LoadDatas(){
		$ldap=new clladp();
		$ld =$ldap->ldap_connection;
		$bind =$ldap->ldapbind;
		$suffix=$ldap->suffix;
		
			
		$arr=array('description');
		$sr =@ldap_search($ldap->ldap_connection,$suffix,"(&(gidnumber=$this->group_id)(objectclass=posixGroup))");
		if(!$sr){writelogs("Ressource false query (gidnumber=$this->group_id)",__CLASS__.'/'.__FUNCTION__,__FILE__);return null;}
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){writelogs("entry_id false query (gidnumber=$this->group_id)",__CLASS__.'/'.__FUNCTION__,__FILE__);return null;}
			$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
			$this->hash=$attrs;
			$dn = ldap_get_dn($ldap->ldap_connection,$entry_id);
			
			if(preg_match('#,dc=samba,#',$dn)){
				$hasSambaGroup=true;
			}
			

			
			
			$this->dn=$dn;
			if(preg_match('#ou=groups,ou=(.+?),dc#',$dn,$reg)){$res["ou"]=$reg[1];}
			//writelogs("Group ID $this->group_id is in the root \"$dn\" and ou=\"{$res["ou"]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
			
			$res["dn"]=$dn;
			$res["cn"]=$attrs["cn"][0];
			$this->groupName=$res["cn"];
			$res["dn_ou"]=str_replace("cn={$res["cn"]},","",$res["dn"]);
			$res["gid"]=$attrs["gidNumber"][0];
			$res["description"]=stripslashes($attrs["description"][0]);
			$res["ArticaDansGuardianGroupRuleEnabled"]=null;
			
			if(isset($attrs["KasperkyAVScanningDatas"][0])){$res["KasperkyAVScanningDatas"]=$attrs["KasperkyAVScanningDatas"][0];}
			if(isset($attrs["DefaultGroupPassword"][0])){$this->DefaultGroupPassword=$attrs["DefaultGroupPassword"][0];}
			if(isset($attrs["KasperkyASDatas"][0])){$res["KasperkyASDatas"]=$attrs["KasperkyASDatas"][0];}
			if(isset($attrs["KasperkyASDatas"][0])){$this->KasperkyASDatas=$attrs["KasperkyASDatas"][0];}
			if(isset($attrs["KasperskyASGroupNumber"][0])){$this->KasperskyASGroupNumber=$attrs["KasperskyASGroupNumber"][0];}
			if(isset($attrs["KasperskyASGroupNumber"][0])){$res["KasperskyASGroupNumber"]=$attrs["KasperskyASGroupNumber"][0];}
			if(isset($attrs["KasperkyASDatasRules"][0])){$this->KasperkyASDatasRules=$attrs["KasperkyASDatasRules"][0];}
			if(isset($attrs["KasperkyASDatasRules"][0])){$res["KasperkyASDatasRules"]=$attrs["KasperkyASDatasRules"][0];}
			if(isset($attrs["ArticaDansGuardianGroupRuleEnabled"][0])){$res["ArticaDansGuardianGroupRuleEnabled"]=$attrs["ArticaDansGuardianGroupRuleEnabled"][0];}
			
			
			
			if($res["ArticaDansGuardianGroupRuleEnabled"]==null){
				$upd["ArticaDansGuardianGroupRuleEnabled"]="0";
				if(!$hasSambaGroup){$ldap->Ldap_add_mod($dn,$upd);}
				unset($upd);
				$res["ArticaDansGuardianGroupRuleEnabled"]=0;
				}
			
			for($i=0;$i<$attrs["memberUid"]["count"];$i++){
				$res["ARRAY_MEMBERS"][]=$attrs["memberUid"][$i];
				$this->members[]=$attrs["memberUid"][$i];
				$this->members_array[$attrs["memberUid"][$i]]=true;
					if(substr($attrs["memberUid"][$i],strlen($attrs["memberUid"][$i])-1,1)=='$'){
						$this->computers_array[$attrs["memberUid"][$i]]=$attrs["memberUid"][$i];
					}
					$users[$attrs["memberUid"][$i]]=$attrs["memberUid"][$i];
				}
				
			if(isset($attrs["FiltersExtensionsGroupName"]["count"])){
				for($i=0;$i<$attrs["FiltersExtensionsGroupName"]["count"];$i++){$res["FiltersExtensionsGroupName"][$attrs["FiltersExtensionsGroupName"][$i]]="yes";}
			}				
				
			for($i=0;$i<$attrs["objectClass"]["count"];$i++){
					$this->objectClasses[$attrs["objectClass"][$i]]=true;
				}		
				
				
				
			$res["SharedFolderPrefix"]=$attrs["SharedFolderPrefix"][0];
			$this->SharedFolderPrefix=$res["SharedFolderPrefix"];
			$res["members"]=$users;
			$priv=array();
			if(isset($attrs["ArticaGroupPrivileges"][0])){if(preg_match_all('#\[([a-zA-Z]+)\]="([a-z0-9]+)"#is',$attrs["ArticaGroupPrivileges"][0],$reg)){while (list ($num, $ligne) = each ($reg[1]) ){$priv[$ligne]=$reg[2][$num];}}}
			$res["ArticaGroupPrivileges"]=$priv;
			$this->Privileges_array=$priv;
			
			if(preg_match("#ou=groups,ou\=([a-zA-Z0-9\s\.\-\(\)_]+)#i",$dn,$pre)){$res["ou"]=trim($pre[1]);}
			
			$this->ou=$res["ou"];
			$this->sambaSID=$attrs["sambaSID"][0];
			$this->sambaGroupType=$attrs["sambaGroupType"][0];
			$this->description=$attrs["description"][0];
			$this->main_array=$res;
			$this->ARRAY_MEMBERS=$res["ARRAY_MEMBERS"];
			$this->KavMilterSettings=$this->LoadKavMilterGroup();
			
			return $res;		
		
		
	}
	
	function SavePrivileges(){
		$conf="#";
		if(is_array($this->Privileges_array)){
			while (list ($num, $ligne) = each($this->Privileges_array)){
				$conf=$conf . "[$num]=\"$ligne\"\n";
				}
			
		}
		$res["ArticaGroupPrivileges"][0]=$conf;
		$ldap=new clladp();
		if(!$ldap->Ldap_modify($this->dn,$res)){
			echo "$this->group_id::$this->ou\n".$tpl->_ENGINE_parse_body("{privileges}:\n$ldap->ldap_last_error");
		}else{
			$tpl=new templates();
			echo "$this->group_id::$this->ou\n".$tpl->_ENGINE_parse_body("{privileges}:{success}");
		}
		
		
	}
	
	function LoadKavMilterGroup(){
		$users=new usersMenus();
		if($users->KAV_MILTER_INSTALLED){
			$users->LoadModulesEnabled();
			if($users->KAVMILTER_ENABLED==1){
				include_once(dirname(__FILE__).'/class.kavmilterd.inc');
				$kav=new kavmilterd();
				$kav->LoadRule("$this->groupName.$this->ou");
				return $kav->RuleDatas;
			}
		}
		}
		
	function edit_DefaultGroupPassword(){
		$upd["DefaultGroupPassword"][0]=$this->DefaultGroupPassword;
		$ldap=new clladp();
		if(!$ldap->Ldap_modify($this->dn,$upd)){
			$this->ldap_error=$ldap->ldap_last_error;
			return false;
		}
		return true;
		}
		
	function changeAllMembersPassword(){
		$password=$this->DefaultGroupPassword;
		if(trim($password)==null){return false;}
		if(!is_array($this->members_array)){return false;}
		reset($this->members_array);
		while (list ($num, $line) = each ($this->members_array)){
			writelogs("Changing password for $num",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			$users=new user($num);
			$users->password=$password;
			$users->add_user();
			$users=null;
		}
		
		return true;
	}
	
	function add_new_group($group,$ou=null,$generated_id=null){
		$group=replace_accents($group);
		$update_array=array();
		if(!is_numeric($generated_id)){$generated_id=null;}
		
		if($generated_id==null){
			$generated_id=$this->_GenerateGUID();
			if($generated_id<1001){$generated_id=1001;}
			if($generated_id==1){$generated_id=1001;}
		}
		
		writelogs("Adding \"$group\" in organization \"$ou\" ($generated_id)",
		__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		$this->generated_id=$generated_id;
		$ldap=new clladp();
		
		if($ou==null){
			$dn="cn=$group,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
			writelogs("Adding \"$group\" in $dn",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		}
		else{$dn="cn=$group,ou=groups,ou=$ou,dc=organizations,$ldap->suffix";}
		
		if($ou<>null){
			$dn_master_branch="ou=groups,ou=$ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn_master_branch)){
			$update_array["objectClass"][]="top";
			$update_array["objectClass"][]="organizationalUnit";
			$update_array["ou"][]="groups";
			if(!$ldap->ldap_add($dn_master_branch,$update_array)){
				echo "Unable to add master branch\n$dn_master_branch\n$this->ldap_last_error";
				return false;
			}
			unset($update_array);
			
		}}
		
		
		
		$update_array["cn"][0]="$group";
		$update_array["gidNumber"][0]=$generated_id;
		$update_array["description"][0]="New posix group";
		$update_array["objectClass"][]='posixGroup';
		$update_array["objectClass"][]='ArticaSettings';
		$update_array["objectClass"][]='top';
		
		
		
		$dn=$ldap->dn_escape($dn);
		
		if($ldap->ExistsDN($dn)){
			writelogs("Adding \"$dn\" already exists...",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->generated_id=$this->GroupIDFromName($ou,$group);
			writelogs("Return back to generated_id has $this->generated_id",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return true;
		}
		
		writelogs("Adding \"$dn\" in $ldap->suffix",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		
		if(!$ldap->ldap_add($dn,$update_array)){
			$this->ldap_error=
				"Error: Adding {$update_array["gidNumber"][0]} gid 
				cn=$group\ndn:$dn\n".
				$ldap->ldap_last_error;
			return false;
		}
		
		return true;
	}	
	
  private function _GenerateGUID(){
  		$ldap=new clladp();
  		$ld =$ldap->ldap_connection;
		$bind =$ldap->ldapbind;
		
		$pattern="(&(objectClass=posixGroup)(gidNumber=*))";
		$attr[]="gidNumber";
		$a=0;
      	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
      		if ($sr){
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);
				if($hash["count"]>0){
					for($i=0;$i<$hash["count"];$i++){
						$g=$hash[$i]["gidnumber"][0];
						if($g>$a){$a=$g;}
					}
		      			
				}
				if($a==0){$a=5000;}
				if($a<5000){$a=5000;}
         		writelogs("Last group number was $a",__CLASS__.'/'.__FUNCTION__,__FILE__);
         		return $a+1;
      		}
     		 return 1001;
   		}	
	
	
	function user_add_to_group($uid){
		$ldap=new clladp();
		if($this->members_array[$uid]==true){return true;}
		
		writelogs("$uid is not a member of $this->group_id ($this->dn)",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$update_array["memberUid"]=$uid;
		writelogs("add memberUid=$uid",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($ldap->Ldap_add_mod($this->dn,$update_array)){
			writelogs("add memberUid=$uid failed...",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return false;
		}
		if(trim($ldap->ldap_last_error)<>null){echo $ldap->ldap_last_error;return false;}
		
		$this->UpdatePlugins();
		
		return true;
		}
		
	function user_delete_from_all_groups($uid){
		$ldap=new clladp();
		$dn="dc=organizations,$ldap->suffix";
		$pattern="(memberUid=$uid)";
		$sr =@ldap_search($ldap->ldap_connection,$dn,"$pattern",array('gidNumber'));
		if(!$sr){return null;}
		$hash = ldap_get_entries($ldap->ldap_connection,$sr);
		echo "<ul>\n";
		for($i=0;$i<$hash["count"];$i++){
			$group=new groups($hash[$i]["gidnumber"][0]);
			if($group->DeleteUserFromThisGroup($uid)){
				echo "<lI>$uid -> {$hash[$i]["gidnumber"][0]} OK</li>\n";
			}
			
		}
			
		echo "</ul>\nDone...\n<br>";
		$this->UpdatePlugins();
		
	}
	
	private function UpdatePlugins(){
		$users=new usersMenus();
		if($users->DANSGUARDIAN_INSTALLED){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?reload-dansguardian=yes");
		}
	}
		
		
	function GroupIDFromName($ou=null,$groupname){
		$ldap=new clladp();
		$dn="ou=groups,ou=$ou,dc=organizations,$ldap->suffix";
		if($ou==null){
			$dn="ou=groups,dc=samba,dc=organizations,$ldap->suffix";
		}
		
		$sr =@ldap_search($ldap->ldap_connection,$dn,"(&(cn=$groupname)(objectClass=posixGroup))",array('gidNumber'));
		if(!$sr){return null;}
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if($entry_id){
			$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
			return $attrs["gidNumber"][0];
		}		
		
	}		
		
		
	function TransformGroupToSmbGroup(){
		$users=new usersMenus();
		
		$upd["objectClass"][]="sambaGroupMapping";
		$upd["sambaGroupType"][]=5;
		$upd["sambaSID"][]=$this->LOCAL_SID. "-$this->group_id";
		$ldap=new clladp();
		$ldap->Ldap_add_mod($this->dn,$upd);
		}
	
	
	function ParseClasses(){
		if($this->dn==null){return null;}
		$i=0;
		$array=$this->hash["objectClass"];
		for($i==0;$i<$array["count"];$i++){
			$this->objectClass[$array[$i]]=true;
		}
		
		if(!$this->objectClass["SharedFolders"]){
			$ldap=new clladp();
			$res["objectClass"][]="SharedFolders";
			$res["SharedFolderPrefix"]='shared_';
			$res["SharedFolerConf"]="NONE";
			$ldap->Ldap_add_mod($this->dn,$res);
			unset($res);
		}
	 }
	 
	 function saveDescription($content){
	 	$content=utf8_encode($content);
	 	$upd["description"][0]=$content;
	 	$ldap=new clladp();
	 	if(!$ldap->Ldap_modify($this->dn, $upd)){
	 		$this->ldap_error=$ldap->ldap_last_error;
	 		return false;
	 	}
	 	return true;
	 }
	 
	 function ParseSharedFolders(){
	 	if(!isset($this->hash["SharedFolderList"])){$this->SharedFolders_list=array();return;}
	 	$array=$this->hash["SharedFolderList"];
	 	for($i=0;$i<$array["count"];$i++){
			$this->SharedFolders_list[]=$array[$i];
		}
	 }
	 
	 function add_sharedfolder($path){
	 	$ldap=new clladp();
	 	if($this->dn==null){$this->dn=$this->main_array["dn"];}
	 	if($path==null){return false;}
	 	$res["SharedFolderList"]=$path;
	 	writelogs("$this->dn add \"$path\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 	$ldap->Ldap_add_mod($this->dn,$res);
	 	$this->SaveCharedConf();
	 	}
	 	
	 function AddUsertoThisGroup($uid){
	 	$ldap=new clladp();
	 	$upd["memberUid"]=$uid;
	 	if($ldap->Ldap_add_mod($this->dn,$upd)){return true;}
	 	$this->ldap_error=$ldap->ldap_last_error;
	 	return false;
	 }
	 
	 function DeleteUserFromThisGroup($uid){
	 	$ldap=new clladp();
	 	$upd["memberUid"]=$uid;
	 	writelogs("$this->dn Del memberUid=\"$uid\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 	$this->UpdatePlugins();
	 	return $ldap->Ldap_del_mod($this->dn,$upd);
	 }
	 
	 public function Delete(){
	 	$ldap=new clladp();
	 	if($ldap->ExistsDN($this->dn)){
	 		$ldap->ldap_delete($this->dn);
	 	}
	 	
	 	$sql="DELETE FROM dansguardian_groups WHERE group_id=$this->group_id";
	 	$q=new mysql();
	 	$q->QUERY_SQL($sql,"artica_backup");
	 	
	 	$this->UpdatePlugins();
	 	$this->SaveCharedConf();
	 	
	 }
	 	
	 function del_sharedfolder($index){
	 	$ldap=new clladp();
	 	if($this->dn==null){$this->dn=$this->main_array["dn"];}
	 	$res["SharedFolderList"]=$this->SharedFolders_list[$index];
	 	writelogs("$this->dn del \"{$res["SharedFolderList"]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 	$ldap->Ldap_del_mod($this->dn,$res);
	 	$autofs=new autofs();
	 	$autofs->AutofsDeleteDir($this->group_id,$res["SharedFolderList"]);
	 	}
	 function edit_SharedFolderPrefix(){
		$ldap=new clladp();
	 	if($this->dn==null){$this->dn=$this->main_array["dn"];}
	 	$res["SharedFolderPrefix"][0]=$this->SharedFolderPrefix;
	 	if(!$ldap->Ldap_modify($this->dn,$res)){echo $ldap->ldap_last_error;}else{
	 		$tpl=new templates();
	 		$this->SaveCharedConf();
	 		echo $tpl->_ENGINE_parse_body('
	 		Prefix:{success}');
	 	}
	 }
	 
	 
	 //SharedConfDeleteFolderAll
	 
	 
	 function BuildSharedFolderConf(){
	 	
	 	$ldap=new clladp();
	 	$conf="[SHARED]\n";
	 	$conf=$conf . "key=" . md5(date('Y-m-d H:I:s'))."\n";
	 	$conf=$conf . "groupid=$this->group_id\n";
	 	$conf=$conf. "SharedFolderPrefix=$this->SharedFolderPrefix\n";
	 	$conf=$conf."[SHARED_FOLDERS]\n";
	 	$conf=$conf."sharedfolder_count=" . count($this->SharedFolders_list) ."\n";
	 	for($i=0;$i<count($this->SharedFolders_list);$i++){
			$conf=$conf."shared.$i={$this->SharedFolders_list[$i]}\n";
	 	}
		
	 	$conf=$conf."[members]\n";
	 	$conf=$conf."members_count=" . count($this->members) ."\n";	
	 	
	 	
	 	
	 	for($i=0;$i<count($this->members);$i++){
	 		$hash=$ldap->UserDatas($this->members[$i]);
	 		$homeDirectory=$hash["homeDirectory"];
	 		if($homeDirectory<>null){
	 			$conf=$conf."member.$i=$homeDirectory\n";
	 		}
	 	}
	 		
	 	return $conf;	
	 	}
	 	
	 	function SaveCharedConf(){
	 		$ldap=new clladp();
	 		include_once(dirname(__FILE__).'/class.autofs.inc');
	 		$autofs=new autofs();
	 		$autofs->AutofsSharedDir($this->group_id);
	 		$sock=new sockets();
	 		$sock->getfile('autofsReload');
	 		
	 	}
	 	
	 	
	 	function BuildWhitelistArray(){
	 		$ldap=new clladp();
	 		$domains=$ldap->hash_get_domains_ou($this->ou);
	 		if(!is_array($domains)){return null;}
	 		while (list ($num, $line) = each ($domains)){
	 			$hash=$ldap->WhitelistsFromDomain($line);
	 			if(is_array($hash)){
	 				while (list ($a, $b) = each ($hash)){
	 					$email=$a;
	 					$uid=$ldap->uid_from_email($email);
		 				if($this->members_array[$uid]){
		 					while (list ($c, $d) = each ($b)){
		 						$i[]=$d;
		 					}
		 				}
		 			}
	 			}
	 		}
	 	return $i;	
	 	}
	 	
	 	function BuildBlackListArray(){
	 		$ldap=new clladp();
	 		$domains=$ldap->hash_get_domains_ou($this->ou);
	 		if(!is_array($domains)){return null;}
	 		while (list ($num, $line) = each ($domains)){
	 			$hash=$ldap->BlackListFromDomain($line);
	 			if(is_array($hash)){
	 				while (list ($a, $b) = each ($hash)){
	 					$email=$a;
	 					$uid=$ldap->uid_from_email($email);
		 				if($this->members_array[$uid]){
		 					while (list ($c, $d) = each ($b)){
		 						$i[]=$d;
		 					}
		 				}
		 			}
	 			}
	 		}
	 	return $i;	
	 	}

	 	function BuildEmailsList(){
	 		if(!is_array($this->members)){return null;}
	 		$arr=$this->members;
	 		$ldap=new clladp();
	 		while (list ($num, $line) = each ($arr)){
	 			$hash=$ldap->UserDatas($line);
	 			if(is_array($hash["mailAlias"])){
	 				while (list ($a, $b) = each ($hash["mailAlias"])){
	 					$re[]=$b;
	 					
	 				}
	 			}
	 			if($hash["mail"]<>null){$re[]=$hash["mail"];}
	 		}
	 	 	
	 	return $re;
	 	}
	 	
	 	
	 	
	 	function BuildOrdinarySambaGroups(){
	 		$users=new usersMenus();
	 		
	 		$ldap=new clladp();
	 		if(!$this->SAMBA_ENABLED){return false;}
	 		$this->SambaGroupsBuild();
	 		$dn="cn=users,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 		$u=array();
	 		if(!$ldap->ExistsDN($dn)){
	 				writelogs("Creating $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 				$u["objectClass"][]="top";
	 				$u["objectClass"][]="posixGroup";
	 				$u["objectClass"][]="sambaGroupMapping";
	 				$u["gidNumber"][]=545;
	 				$u["description"][]="Netbios Domain Ordinary users";
	 				$u["sambaSID"][]="S-1-5-32-545";
	 				$u["sambaGroupType"][]="4";
	 				$u["displayName"][]="users";
	 				$u["cn"][]="users";
	 				$ldap->ldap_add($dn,$u);
	 				unset($u);
	 		}
					 		
	 		
	 	}
	 	

function EditSambaGroups(){
	 		$users=new usersMenus();
	 		$tpl=new templates();
	 		$ldap=new clladp();
	 		$u=array();
			if(!$this->SAMBA_ENABLED){return false;}
			
	 	$dn="cn=Domain Admins,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 		$this->SambaGroupsBuild();
	 		return null;
	 	}
	 	
	if($this->LOCAL_SID==null){
		writelogs("LOCAL_SID=NULL! Aborting",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return false;
	}

	writelogs("LOCAL_SID=$this->LOCAL_SID",__CLASS__.'/'.__FUNCTION__,__FILE__); 
	$u["sambaSID"][0]="$this->LOCAL_SID-512";	
	writelogs("Editing groups...$dn",__CLASS__.'/'.__FUNCTION__,__FILE__); 			
	if(!$ldap->Ldap_modify($dn,$u)){
		echo $tpl->_ENGINE_parse_body("$dn {failed}\n$ldap->ldap_last_error\n\n");
	 	unset($u);
	 }	

	 
	 $dn="cn=Domain Computers,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 $u["sambaSID"][0]="$this->LOCAL_SID-515";	

	if(!$ldap->Ldap_modify($dn,$u)){
			echo $tpl->_ENGINE_parse_body("$dn {failed}\n$ldap->ldap_last_error\n\n");
		 	unset($u);
		 }		 
	
	 $dn="cn=Domain Guests,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 $u["sambaSID"][0]="$this->LOCAL_SID-514";
	if(!$ldap->Ldap_modify($dn,$u)){
			echo $tpl->_ENGINE_parse_body("$dn {failed}\n$ldap->ldap_last_error\n\n");
		 	unset($u);
		 }		 
	
	 $dn="cn=Domain Users,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 $u["sambaSID"][0]="$this->LOCAL_SID-513";
	if(!$ldap->Ldap_modify($dn,$u)){
			echo $tpl->_ENGINE_parse_body("$dn {failed}\n$ldap->ldap_last_error\n\n");
		 	unset($u);
		 }	

     $dn="cn=domguests,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 $u["sambaSID"][0]="$this->LOCAL_SID-514";
	if(!$ldap->Ldap_modify($dn,$u)){
			echo $tpl->_ENGINE_parse_body("$dn {failed}\n$ldap->ldap_last_error\n\n");
		 	unset($u);
		 }	
	  
}
	 	
	 	
 function SambaGroupsBuild(){
 			$u=array();
	 		$users=new usersMenus();
	 		$ldap=new clladp();
	 		
	 		$dn="dc=samba,dc=organizations,$ldap->suffix";
	 		
	 		if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organization";
	 			$u["objectClass"][]="dcObject";
	 			$u["o"][]="samba";
	 			$u["dc"][]="samba";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 		}
	 		
	 		
	$dn="ou=users,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organizationalUnit";
	 			$u["ou"][]="users";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}
	 			
 	$dn="ou=idmap,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organizationalUnit";
	 			$u["ou"][]="idmap";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	 			
	 			
	$dn="ou=Computer,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organizationalUnit";
	 			$u["ou"][]="Computer";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}
	 			
	 	$dn="ou=People,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organizationalUnit";
	 			$u["ou"][]="People";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	 			
	 			
	 	$dn="ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="organizationalUnit";
	 			$u["ou"][]="groups";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}
	 			
	 	$dn="cn=Account Operators,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="548";
	 			$u["cn"][]="Account Operators";
	 			$u["description"][]="Netbios Domain Users to manipulate users accounts";
				$u["sambaSID"][]="S-1-5-32-548";	 			
				$u["sambaGroupType"][]="4";
				$u["displayName"][]="Account Operators";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	
	 			
	 	$dn="cn=Administrators,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="544";
	 			$u["cn"][]="Administrators";
	 			$u["description"][]="Netbios Domain Users to manipulate users accounts";
				$u["sambaSID"][]="S-1-5-32-544";	 			
				$u["sambaGroupType"][]="4";
				$u["displayName"][]="Administrators";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}

	

	 	$dn="cn=Backup Operators,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="551";
	 			$u["cn"][]="Backup Operators";
	 			$u["description"][]="Netbios Domain Members can bypass file security to back up file";
				$u["sambaSID"][]="S-1-5-32-551";	 			
				$u["sambaGroupType"][]="4";
				$u["displayName"][]="Backup Operators";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}

	 	$dn="cn=Guests,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="546";
	 			$u["cn"][]="Guests";
	 			$u["description"][]="Netbios Domain Users granted guest access to the computer/sambaDomainName";
				$u["sambaSID"][]="S-1-5-32-546";	 			
				$u["sambaGroupType"][]="5";
				$u["displayName"][]="Guests";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}

	 	$dn="cn=domguests,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="1999";
	 			$u["cn"][]="domguests";
	 			$u["description"][]="Netbios Domain Users granted guest access to the computer/sambaDomainName";
				$u["sambaSID"][]="$this->LOCAL_SID-514";	 			
				$u["sambaGroupType"][]="2";
				$u["displayName"][]="Domain Guests";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}		 			

 		if($this->SAMBA_INSTALLED==false){return null;}	
	 	$dn="cn=Domain Admins,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="512";
	 			$u["cn"][]="Domain Admins";
	 			$u["description"][]="Netbios Domain Administrators";
				$u["sambaSID"][]="$this->LOCAL_SID-512";	 			
				$u["sambaGroupType"][]="2";
				$u["displayName"][]="Domain Admins";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	 

	 	$dn="cn=Domain Computers,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 	if(!$ldap->ExistsDN($dn)){
	 			$u["objectClass"][]="top";
	 			$u["objectClass"][]="posixGroup";
	 			$u["objectClass"][]="sambaGroupMapping";
	 			$u["gidNumber"][]="515";
	 			$u["cn"][]="Domain Computers";
	 			$u["description"][]="Netbios Domain Computers accounts";
				$u["sambaSID"][]="$this->LOCAL_SID-515";	 			
				$u["sambaGroupType"][]="2";
				$u["displayName"][]="Domain Computers";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	 			 			

	 			
		$dn="cn=Domain Guests,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["gidNumber"][]="514";
				$u["cn"][]="Domain Guests";
				$u["description"][]="Netbios Domain Guests Users";
				$u["sambaSID"][]="$this->LOCAL_SID-514";
				$u["sambaGroupType"][]="2";
				$u["displayName"][]="Domain Guests";
	 			if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 	}	
		$dn="cn=Domain Users,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["gidNumber"][]="513";
				$u["cn"][]="Domain Users";
				$u["description"][]="Netbios Domain Users";
				$u["sambaSID"][]="$this->LOCAL_SID-513";
				$u["sambaGroupType"][]="2";
				$u["displayName"][]="Domain Users";
				if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 	}	

		$dn="cn=Print Operators,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["gidNumber"][]="550";
				$u["cn"][]="Print Operators";
				$u["description"][]="Netbios Domain Print Operators";
				$u["sambaSID"][]="S-1-5-32-550";
				$u["sambaGroupType"][]="4";
				$u["displayName"][]="Print Operators";
				if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 	}	

		$dn="cn=Replicators,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["gidNumber"][]="552";
				$u["cn"][]="Replicators";
				$u["description"][]="Netbios Domain Supports file replication in a sambaDomainName";
				$u["sambaSID"][]="S-1-5-32-552";
				$u["sambaGroupType"][]="4";
				$u["displayName"][]="Replicators"; 			
				if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 	}
	 	
		$dn="cn=root,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["sambaSID"][]="S-1-5-32-0";
				$u["sambaGroupType"][]="4";
				$u["gidNumber"][]="0";
				$u["cn"][]="root";
				$u["description"][]="Unix virtual system group";
				$u["displayName"][]="root"; 			
				if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}
	 			
	 $dn="cn=Everyone,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
 		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["sambaSID"][]="S-1-1-0";
				$u["sambaGroupType"][]="4";
				$u["gidNumber"][]="1998";
				$u["cn"][]="Everyone";
				$u["description"][]="Everyone";
				$u["displayName"][]="Everyone"; 			
				if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	 
	 			
	 $dn="cn=nobody,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
 		if(!$ldap->ExistsDN($dn)){
				$u["objectClass"][]="top";
				$u["objectClass"][]="posixGroup";
				$u["objectClass"][]="sambaGroupMapping";
				$u["sambaSID"][]="S-1-5-2";
				$u["sambaGroupType"][]="4";
				$u["gidNumber"][]="1997";
				$u["cn"][]="nobody";
				$u["description"][]="nobody";
				$u["displayName"][]="nobody"; 			
				if(!$ldap->ldap_add($dn,$u)){return null;}
	 			unset($u);
	 			}	
	 			

	 	} 	

	 	

	 	
	 	function samba_group_list(){
	 		$ldap=new clladp();
	 		$filter=array("gidnumber","cn");
	 		$dr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=sambaGroupMapping))",$filter);	
			if($dr){
				$result =@ldap_get_entries($ldap->ldap_connection, $dr);
				for($i=0;$i<$result["count"];$i++){
					$gt=$result[$i];
					$res[$gt["gidnumber"][0]]=$gt["cn"][0];
					
				}
			}
	 		
	 	if(isset($res)){	return $res;}
	 		
	 	}
	 	
	 	function ChangeSMBGroupsSID(){
	 		$ldap=new clladp();
	 		
	 		$u=array();
	 		if(!$this->SAMBA_ENABLED){return false;}
	 		$dn="cn=Domain Users,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 		if(!$ldap->ExistsDN($dn)){$this->SambaGroupsBuild();return null;}
	 		$u["sambaSID"][0]="$this->LOCAL_SID-513";
	 		$ldap->Ldap_modify($dn,$u);
	 		unset($u);
	 		
	 			 		
	 		$dn="cn=Domain Guests,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 		$u["sambaSID"][0]="$this->LOCAL_SID-514";
	 		$ldap->Ldap_modify($dn,$u);
	 		unset($u);
	 		
	 		$dn="cn=Domain Computers,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 		$u["sambaSID"][0]="$this->LOCAL_SID-515";	
			$ldap->Ldap_modify($dn,$u);
	 		unset($u);	 	

	 		$dn="cn=Domain Admins,ou=groups,dc=samba,dc=organizations,$ldap->suffix";
	 		$u["sambaSID"][0]="$this->LOCAL_SID-512";	
	 		$ldap->Ldap_modify($dn,$u); 	
	 		unset($u);	

	 		
			$ldap=new clladp();
			$filter_search="(&(objectClass=sambaGroupMapping)((cn=*))";
			$attrs=array("gidNumber","dn");
			$dn="dc=organizations,$ldap->suffix";
		//	$hash=$this->samba_group_list();	
			$dr =@ldap_search($ldap->ldap_connection,$dn,"(&(objectClass=sambaGroupMapping))",$attrs);	
			if($dr){
				$result =@ldap_get_entries($ldap->ldap_connection, $dr);
				for($i=0;$i<$result["count"];$i++){
				$gidnumber=$result[$i]["gidnumber"][0];
				$gpi=new groups($gidnumber);
				$gpi->EditAsSamba();
				}
			}
				
	 		
	 		
	 	}
	 	
	 	
	 	function samba_standard_groups(){
	 		$ldap=new clladp();
	 		$filter=array("gidnumber","cn");
	 		$dr =ldap_search($ldap->ldap_connection,"dc=samba,dc=organizations,$ldap->suffix","(objectClass=sambaGroupMapping)",$filter);	
			if($dr){
				$result = ldap_get_entries($ldap->ldap_connection, $dr);
				for($i=0;$i<$result["count"];$i++){
					$gt=$result[$i];
					$res[$gt["gidnumber"][0]]=$gt["cn"][0];
					
				}
			}
	 		
	 		return $res;
	 		
	 		
	 	}
	 	
	 	function samba_group_id_from_sid($sid){
	 		$ldap=new clladp();
	 		//writelogs("Search samba SID $sid",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 		$filter=array("gidNumber");
	 		$dr =ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix","(&(objectClass=sambaGroupMapping)(sambaSID=$sid))",$filter);
	 		if($dr){
				$result = ldap_get_entries($ldap->ldap_connection, $dr);
				//writelogs("gidNumber={$result[0]["gidnumber"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return $result[0]["gidnumber"][0];
	 		}else{
	 			writelogs("Query was unable to find gidNumber in query (&(objectClass=sambaGroupMapping)(sambaSID=$sid))",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 		}
	 		
	 	}
	 	
	 	function samba_group_sid_from_gid($gid){
	 		$ldap=new clladp();
	 		$filter=array("sambaSID");
	 		$dr =ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix","(&(objectClass=sambaGroupMapping)(gidNumber=$gid))",$filter);
	 		if($dr){
				$result = ldap_get_entries($ldap->ldap_connection, $dr);
				//writelogs("sambaSID($gid)={$result[0]["sambasid"][0]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return $result[0]["sambasid"][0];
				
	 		}else{
	 			writelogs("Query was unable to find sambaSID in query (&(objectClass=sambaGroupMapping)(gidNumber=$gid))",__CLASS__.'/'.__FUNCTION__,__FILE__);
	 		}
	 		
	 	}
	 	
	 	
	 	function CountMailingListes(){
	 		if($this->ou==null){return null;}
	 		$ldap=new clladp();
	 		$filter=array("cn");
	 		$dn="ou=$this->ou,dc=organizations,$ldap->suffix";
	 		$dr=ldap_search($ldap->ldap_connection,$dn,"(&(objectClass=MailingAliasesTable)(mailinglistaddress=*))",$filter);
	 		if($dr){
				$result =@ldap_get_entries($ldap->ldap_connection, $dr);
				return $result["count"];
	 		}else{
	 			writelogs("Failed search in $dn",__FUNCTION__.'/'.__CLASS__,__FILE__);
	 			return 0;
	 		}
	 		return 0;
	 	}
	 	
	 	function load_MailingList($ou){
			$ldap=new clladp();
	 		$filter=array();
	 		$res=array();
	 		$dr =ldap_search($ldap->ldap_connection,"ou=$ou,dc=organizations,$ldap->suffix","(&(objectClass=MailingAliasesTable)(mailinglistaddress=*))",$filter);
	 		if($dr){
				$result = ldap_get_entries($ldap->ldap_connection, $dr);
				for($i=0;$i<$result["count"];$i++){
					if($result[$i]["mailinglistaddress"]["count"]==0){continue;}
					$res[$result[$i]["cn"][0]]=$result[$i]["mailinglistaddress"]["count"];
				}
	 		}
	 		return $res;
	 	}
	 	
	 	
		function list_of_groups($ou,$fieldlist=0){
		$ldap=new clladp();
		$pos=strpos($ou,',');
		if($pos>0){$ou=substr($ou,0,$pos);}
		$ld =$ldap->ldap_connection;
		$bind =$ldap->ldapbind;
		$suffix=$ldap->suffix;	
		$cols=array("cn","description",'gidnumber');
		$sr = @ldap_search($ld,"ou=groups,ou=$ou,dc=organizations,$suffix",'objectclass=posixGroup',$cols);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["cn"][0]<>null){
					switch ($fieldlist) {
						case 1:$res[$ligne["gidnumber"][0]]=$ligne["cn"][0];break;
						case 2;$res=$res . "{$ligne["gidnumber"][0]};{$ligne["cn"][0]}\n";break;
						default:$res[$ligne["cn"][0]]=array("gid"=>$ligne["gidnumber"][0],"description"=>$ligne["description"][0]);break;
					}
				}
				
			}
		}
		
		
		$cols=array("cn","description",'gidnumber');
		$sr = @ldap_search($ld,"ou=groups,dc=samba,dc=organizations,$suffix",'objectclass=posixGroup',$cols);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				if($ligne["cn"][0]<>null){
					switch ($fieldlist) {
						case 1:$res[$ligne["gidnumber"][0]]=$ligne["cn"][0];break;
						case 2;$res=$res . "{$ligne["gidnumber"][0]};{$ligne["cn"][0]}\n";break;
						default:$res[$ligne["cn"][0]]=array("gid"=>$ligne["gidnumber"][0],"description"=>$ligne["description"][0]);break;
					}
				}
				
			}
		}		
		
		return $res;	
		}	

		
		
		function EditAsSamba(){
			if(!$this->SAMBA_ENABLED){return false;}
			$users=new usersMenus();
			$sid=$this->LOCAL_SID;
			$ldap=new clladp();
			if(!$this->objectClasses["sambaGroupMapping"]){
				$upd["objectClass"]="sambaGroupMapping";
				$upd["sambaSID"]="$sid-".(2 * $this->group_id + 1001 );
				$upd["sambaGroupType"]=$this->sambaGroupType;
				
				if(!$ldap->Ldap_add_mod($this->dn,$upd)){
					echo $ldap->ldap_last_error;
				}else{
					$tpl=new templates();
					echo $tpl->_ENGINE_parse_body('{success}');
				}
			return;}
			writelogs("DN=$this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$upd["sambaSID"][]="$sid-".(2 * $this->group_id + 1001 );
			$upd["sambaGroupType"][]=$this->sambaGroupType;
			
			if(!$ldap->Ldap_modify($this->dn,$upd)){
					echo $ldap->ldap_last_error;
				}else{
					$tpl=new templates();
					if(posix_getuid()==0){echo "groups::EditAsSamba()::$this->dn = $sid \n";}else{
					echo $tpl->_ENGINE_parse_body("EditAsSamba() $sid {success}\n");}
				}			
			
		}
	 	
	 	
	
}

?>