<?php
if(!isset($GLOBALS["DEBUG_PRIVS"])){$GLOBALS["DEBUG_PRIVS"]=false;}
class privileges{
	var $privs=array();
	var $uid=null;
	var $content=null;
	var $ldap;
	var $webpage=null;
	var $privilegesuid=null;
	
	function privileges($uid=null,$privilegesuid=null){
		$this->privilegesuid=$privilegesuid;
		$this->uid=$uid;
		if(function_exists("CurrentPageName")){$this->webpage=CurrentPageName();}
		
		
		$trace=debug_backtrace();
		if(isset($trace[1])){
				$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			
		}
		
		if(isset($_SESSION[__CLASS__][$uid])){
			if(count($_SESSION[__CLASS__][$uid]["PRIVS"])==0){
				if($GLOBALS["DEBUG_PRIVS"]){writelogs("->SearchPrivileges($uid,$privilegesuid) $called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				$this->SearchPrivileges();
				
			}
			if($GLOBALS["DEBUG_PRIVS"]){
				$this->DebugPrivs();
				writelogs("Return $uid cached object ".count($_SESSION[__CLASS__][$uid]["PRIVS"])." $called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			
			$this->content=$_SESSION[__CLASS__][$uid]["CONTENT"];
			$this->privs=$_SESSION[__CLASS__][$uid]["PRIVS"];
			return;
		}
		
		
		
		
		if($uid<>null){
			$this->ldap=new clladp();
			$this->uid=$uid;
			$this->SearchPrivileges();
		}
		
		
	}
	private function DebugPrivs(){
		$array=$_SESSION[__CLASS__][$this->uid]["PRIVS"];
		while (list ($num, $ligne) = each ($array) ){
			if($ligne=="yes"){
				writelogs("$num = [$ligne]",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
	}
	
	
	public function PolicyPassword($password,$replaceArray=null){
		$policiespwd=unserialize(base64_decode($this->privs["PasswdPolicy"]));
		$tpl=new templates();
		if(!is_array($replaceArray)){
			writelogs("replaceArray is not an array....",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
		if(is_array($replaceArray)){$policiespwd=$replaceArray;}
		
		
		if(!is_array($policiespwd)){
			writelogs("policiespwd is not an array....",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return true;	
		}
		if($policiespwd["passwd_length"]>0){
			if(strlen($password)<$policiespwd["passwd_length"]-1){
				echo $tpl->javascript_parse_text("ERROR: {password_no_match_policy}
{minimum_length}: {$policiespwd["passwd_length"]}",1);
				return false;
			}
		}
		
		
		if($policiespwd["UpperCase"]==1){
			if(!preg_match("#[A-Z]+#",$password)){
				echo $tpl->javascript_parse_text("ERROR: {password_no_match_policy}
{at_least_one_upper_case_letter}",1);
				return false;	
			}
		}
		
		if($policiespwd["LowerCase"]==1){
			if(!preg_match("#[a-z]+#",$password)){
				echo $tpl->javascript_parse_text("ERROR: {password_no_match_policy}
{at_least_one_lower_case_letter}",1);
				return false;	
			}
		}

		if($policiespwd["DigitCase"]==1){
			if(!preg_match("#[0-9]+#",$password)){
				echo $tpl->javascript_parse_text("ERROR: {password_no_match_policy}
{at_least_one_digit_letter}",1);
				return false;	
			}
		}

		if($policiespwd["SpecialCase"]==1){
			$spec=utf8_decode("$ % ! * # ( ) [ ] § ; :  , / ~ & < > . - _ ²");
			if(!preg_match("#[\$\%\!\*\#\(\)\[\]\,\?\§\;\:\/\~\&\<\>\.\_\-\²]+#",$password)){
				echo $tpl->javascript_parse_text("ERROR: {password_no_match_policy}
{at_least_one_special_letter}
$spec",1);
				return false;	
			}
		}

		return true;
		
		
	}
	
	private function SearchPrivileges(){
			if($GLOBALS["DEBUG_PRIVS"]){writelogs("SearchPrivileges -> $this->uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$this->ldap=new clladp();
			$this->privileges_groups();	
			$this->ParsePrivilegesOU();
			$this->GetContent();
			$this->SinglePrivileges();
			$_SESSION[__CLASS__][$this->uid]["PRIVS"]=$this->privs;
			$_SESSION[__CLASS__][$this->uid]["CONTENT"]=$this->content;	
		
	}
	
	
	public function EmptyCache(){
		unset($_SESSION[__CLASS__][$this->uid]);
		
	}
	
	
	private function privileges_groups(){
			
			
			$ldap=new clladp();
			$filters=array("gidNumber","ArticaGroupPrivileges","cn");
			$dr =ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=posixGroup)(memberUid=$this->uid))",$filters);	
			if(!$dr){
				$error=ldap_err2str(ldap_errno($ldap->ldap_connection));
				writelogs("Fatal Error: $error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			
			$result = ldap_get_entries($ldap->ldap_connection, $dr);
			if($result){
				for($i=0;$i<$result["count"];$i++){
					$gidnumber=$result[$i]["gidnumber"][0];
					if($GLOBALS["DEBUG_PRIVS"]){writelogs("[$this->uid] is a part of {$result[$i]["cn"][0]} ($gidnumber)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
					
					if(isset($result[$i]["articagroupprivileges"][0])){
						$this->ParsePrivileges($result[$i]["articagroupprivileges"][0]);
					}
				}
			}else{
				$error=ldap_err2str(ldap_errno($ldap->ldap_connection));
				if($error<>"Success"){writelogs("Fatal Error: $error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			}
				
			
	}

	
	private function ParsePrivileges($content=null){
		if(preg_match_all('#\[([a-zA-Z]+)\]="(.+?)"#is',$content,$reg)){
				while (list ($num, $ligne) = each ($reg[1]) ){
						if($this->webpage=="logon.php"){writelogs("PRIV:$ligne={$reg[2][$num]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
						if($ligne=="ForceLanguageUsers"){$this->privs[$ligne]=$ligne;}
						$this->privs[$ligne]=$reg[2][$num];
					}
			}
	}

	private function ParsePrivilegesOU(){
			$ldap=new clladp();
			$userdn=$ldap->_Get_dn_userid($this->uid);
			if(!preg_match("#ou=users,ou=(.+?),dc#",$userdn,$re)){return ;}
			$ou=$re[1];
			$hash=$this->ldap->OUDatas($ou);
			if($this->webpage=="logon.php"){writelogs("[$this->uid] parsing $ou organization privileges ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			if(isset($hash["ArticaGroupPrivileges"])){
				$privs=$this->ParsePrivileges($hash["ArticaGroupPrivileges"]);
			}
		}

	private function GetContent(){
		while (list ($num, $ligne) = each ($this->privs) ){
			$f[]="[$num]=\"$ligne\"";
		}
		
		$this->content=@implode("\n",$f);
		
	}
	
	private function SinglePrivileges(){
		if($GLOBALS["AS_ROOT"]){return null;}
		if($this->uid==-100){return;}
		if($this->uid==null){return;}
		if($this->privilegesuid==null){
			if($GLOBALS["DEBUG_PRIVS"]){writelogs("[$this->uid] parsing  user privileges ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$ArticaGroupPrivileges=$this->UserPrivs();
		}else{
			$ArticaGroupPrivileges=$this->privilegesuid;
		}
		if(preg_match_all('#\[([a-zA-Z]+)\]="(.+?)"#is',$ArticaGroupPrivileges,$reg)){
			if($this->webpage=="logon.php"){writelogs("Privileges hase been defined for this user...this is now in priority",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			unset($this->privs);$this->privs=array();
				while (list ($num, $ligne) = each ($reg[1]) ){
					if($this->webpage=="logon.php"){writelogs("PRIV:$ligne={$reg[2][$num]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
					if($ligne=="ForceLanguageUsers"){$this->privs[$ligne]=$ligne;}
					$this->privs[$ligne]=$reg[2][$num];
				}			
		}
		
	}
	
	
	private function UserPrivs(){
		if($GLOBALS["AS_ROOT"]){return null;}
		$ldap=new clladp();
		$searchdn="dc=organizations,$ldap->suffix";
		$sr =@ldap_search($ldap->ldap_connection,$searchdn,"(uid=$this->uid)");
		if(!$sr){
			writelogs("Unable to find : $this->uid stamp DoesNotExists has TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		
		$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
		if(!$entry_id){
			writelogs( "INFOS: bad value $entry_id: ($entry_id)  find: (uid=$this->uid) -> aborting function search engine doesn`t found the pattern",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return null;
		}
		$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);		
		return $attrs["ArticaGroupPrivileges"][0];
	}
	
	
	
}