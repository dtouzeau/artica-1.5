<?php
include_once(dirname(__FILE__) . '/class.sieve.front.inc');
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.ini.inc');
include_once(dirname(__FILE__) . '/class.user.inc');
include_once(dirname(__FILE__) . '/class.cyrus.inc');
include_once(dirname(__FILE__) . '/class.smartsieve.engine.inc');

class clSieve{
	var $userid;
	var $username;
	var $password;
	var $server_name='localhost';
	var $server_port=2000;
	var $conn;
	var $_params;
	var $_array_scripts_name=array();
	var $_array_full=array();
	var $array_field_header;
	var $array_field_header_FULL;
	var $array_field_conditions_size_FULL;
	var $array_field_conditions_FULL;
	var $array_field_conditions;
	var $array_field_conditions_size;
	var $array_field_headers_message;
	var $array_field_actions;
	var $array_ini_sections;
	var $array_mails;
	var $rule_count;
	var $array_rules=array();
	var $error;
	var $ECHO_ERROR=true;
	var $sieve_rulename=null;
	var $sieve_ruleindex=null;
	
	function clSieve($userid=null){
		if($userid<>null){
			$this->userid=$userid;
			$user=new user($this->userid);
			$this->array_mails=$hash["mailAlias"];
			$this->array_mails[]=$hash["mail"];
			$this->username=$userid;
			$this->password=$user->password;
			$this->array_rules=$this->LoadSieveDatas();
		}
		

	}
	
	function GetRuleVacation(){
		$rules=$this->array_rules;
		if(is_array($rules)){
			while (list ($rulename,$activated) = each ($rules) ){
				if($activated){
					writelogs("$this->userid:: Found activated rule=$rulename",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$main_rule_name=$rulename;
					break;
				}
				
			}
			
			if($main_rule_name==null){return false;}
			$this->sieve_rulename=$main_rule_name;
			$rules=$this->GetRules($this->sieve_rulename);
			if(is_array($rules)){
				while (list ($index, $lines) = each ($rules) ){
					if($lines["special"]=="vacation"){
						$this->sieve_ruleindex=$index;
						break;
					}
					
				}
			}
			
			
		}
		
		
	}
	
	function GetRuleId($rulename){
		if($rulename==null){return md5(date('Y-M-d H:i:s'));}
		if(!is_array($this->array_ini_sections)){return md5(date('Y-M-d H:i:s'));}
		while (list ($num, $ligne) = each ($this->_params) ){
			if($ligne["RuleName"]==$rulename){return $num;}
		}
		
	}
	
	function LoadSieveDatas(){
		//$this->server_name, $this->server_port, $this->userid, $this->password
 		$sieve=new Managesieve();
 		if(!$sieve->open($this->server_name, $this->server_port)){
 			writelogs("Unable to connect",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			return array();
 		}
 		
 		if(!$sieve->authenticate($this->userid, $this->password)){
 			writelogs("Authentication failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			return array();
 		}
 		
 		$scripts=$sieve->listScripts();
 		if(!is_array($scripts)){
 			writelogs("No scripts...",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			return array();
 		}
 		$sieve->close();
 		return $scripts;
	}
	
	function EnableRule($rulename){
		$sieve=new Managesieve();
 		if(!$sieve->open($this->server_name, $this->server_port)){
 			writelogs("Unable to connect",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			echo "Unable to connect\n";
 			return false;
 		}
 		
		if(!$sieve->authenticate($this->userid, $this->password)){
 			writelogs("Authentication failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			echo "Authentication failed\n";
 			return false;
 		} 

 		$sieve->setActive($rulename);
		}
		
	function GetRules($rulename){
		$sieve=new Managesieve();
		if(!$sieve->open($this->server_name, $this->server_port)){
 			writelogs("Unable to connect",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			echo "Unable to connect\n";
 			return false;
 		}
 		
		if(!$sieve->authenticate($this->userid, $this->password)){
 			writelogs("Authentication failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			echo "Authentication failed\n";
 			return false;
 		} 
 		
 		$datas=$sieve->getScript($rulename);
		$sm=new SmartSieveEngine($rulename,$datas["raw"]);
		$rules=$sm->rules;
		
		return $rules;
	}
	
	function GetRulesHeaders($array_rules){
		$md=md5(serialize($this->ArticaRule()));
		$sm=new SmartSieveEngine();
		while (list ($num, $rule) = each ($array_rules) ){
			$serial=md5(serialize($rule));
			$line=$sm->getSummary($rule);
			if(!$found){
			if($serial==$md){$found=true;$line="Artica rule:$line";}}
			$rules[]=$line;
		}
		return $rules;
	}
	
	
	function CompileRule($array_rules,$rulename){
		$sm=new SmartSieveEngine($rulename);
		$sm->ECHO_ERROR=$this->ECHO_ERROR;
		if($sm->CompileScript($array_rules)){	
			writelogs("sieve: $this->userid:: compiled \n\n$sm->content\n\n",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return $sm->content;
		}else{
			writelogs("sieve: $this->userid:: Unable to compile rule $rulename",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->error=$sm->errstr;
		}
	}
	
	function SaveRule($rulename,$content){
		$sieve=new Managesieve();
		if(!$sieve->open($this->server_name, $this->server_port)){
 			writelogs("Unable to connect",__CLASS__.'/'.__FUNCTION__,__FILE__);
			if($this->ECHO_ERROR){echo "Unable to connect\n";}else{$this->error="Unable to connect\n";}
 			return false;
 		}
 		
		if(!$sieve->authenticate($this->userid, $this->password)){
			if($this->ECHO_ERROR){echo "$this->userid::Authentication failed\n";}else{$this->error="<br>$this->userid::Authentication failed\n";}
 			writelogs("sieve user $this->userid::Authentication failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			return false;
 		} 
 		
 	writelogs("sieve: putScript rule \"$rulename\"",__CLASS__.'/'.__FUNCTION__,__FILE__);	
 	if(!$sieve->putScript($rulename,$content)){
 		if($this->ECHO_ERROR){echo $sieve->_errstr;}else{$this->error=$sieve->_errstr;}
 		writelogs("sieve user $this->userid::unable to put \"$rulename\" rule content $content",__CLASS__.'/'.__FUNCTION__,__FILE__);
 	}else{
 		writelogs("sieve: putScript rule \"$rulename\" success",__CLASS__.'/'.__FUNCTION__,__FILE__);	
 		return true;
 	}
		
	}
	
	function deleteRule($rulename){
		$sieve=new Managesieve();
		if(!$sieve->open($this->server_name, $this->server_port)){
 			writelogs("Unable to connect",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			echo "Unable to connect\n";
 			return false;
 		}
 		
		if(!$sieve->authenticate($this->userid, $this->password)){
 			writelogs("Authentication failed",__CLASS__.'/'.__FUNCTION__,__FILE__);
 			echo "Authentication failed\n";
 			return false;
 		} 
 		if(!$sieve->deleteScript($rulename)){echo $sieve->_errstr;}
		
	}
	
	function AddAutoScript(){
		if(count($this->array_rules)==0){
			return $this->CreateArticaRule();
			}
			
		$pattern=md5(serialize($this->ArticaRule()));
		
		while (list ($num, $ligne) = each ($this->array_rules) ){
				if($num==null){continue;}
				$rulename=$num;
				if($ligne==1){
					$ARRAY_RULES[]=$rulename;
				}
				writelogs("($this->userid) Getting rules from \"$rulename\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$array=$this->GetRules($rulename);
				if(is_array($array)){
					while (list ($index, $rule) = each ($array) ){
						$serial=md5(serialize($rule));
						writelogs("($this->userid) rule: $index = \"$serial\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
						if($serial==$pattern){
							writelogs("($this->userid) Rule $rulename already exists",__CLASS__.'/'.__FUNCTION__,__FILE__);
							$this->EnableRule($rulename);
							$this->error="{ERROR_OBJECT_ALREADY_EXISTS} <span style='font-size:9px'>($rulename)</span>";
							return true;
							}
					}
				}
			
		}
		
		if(!is_array($ARRAY_RULES)){return $this->CreateArticaRule();}
		return $this->AddNewRule($ARRAY_RULES[0]);	
	}
	
	function AddNewRule($rulename){
		$rules=$this->GetRules($rulename);
		$rules[]=$this->ArticaRule();
		$content=$this->CompileRule($rules,$rulename);
		return $this->SaveRule($rulename,$content);
		
	}
	
	
	function ArticaRule(){
		return Array(
            "status" =>"ENABLED",
            "control" =>"if",
            "matchAny" =>"4",
            "conditions" =>Array(
                    0 =>Array("type" =>"header","header" =>"subject","matchStr" =>"***SPAM***","matchType" =>":contains"),
                    1 =>Array("type" =>"header","header" =>"X-Spam-Flag","matchStr" =>"YES","matchType" =>":contains"),
                    2 =>Array("type" =>"header","header" =>"X-Spam-Status","matchStr" =>"Yes","matchType" =>":contains"),
		    		3 =>Array("type" =>"header","header" =>"X-SpamTest-Status","matchStr" =>"SPAM","matchType" =>":contains")),
            "actions" =>Array(
                    0 =>Array("type" =>"fileinto","folder" =>"INBOX/Junk"),
		   			1 =>Array("type" =>"addflag","flag" =>"Junk"))
	);
		
	}
	
	function CreateArticaRule(){
	$rule=Array(0 =>$this->ArticaRule());
	$content=$this->CompileRule($rule,"AntiSpamJunk");
	writelogs("sieve: saving rule \"AntiSpamJunk\"",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$res=$this->SaveRule("AntiSpamJunk",$content);
	if($res){$this->EnableRule("AntiSpamJunk");}
	return $res;
	}
	
	
	function CreateNewRule($rulename){
		$rule=Array(0 =>$this->ArticaRule());
		$content=$this->CompileRule($rule,$rulename);
		$res=$this->SaveRule($rulename,$content);
		if($res){$this->EnableRule($rulename);}
		return $res;
		}
 		
 		
 		
        
		
	
	
	function _ArraySubRules($ruleid,$unset=false){
		if(!is_array($this->_params[$ruleid])){return array();}
		while (list ($num, $ligne) = each ($this->_params[$ruleid]) ){
			if(preg_match('#rule\.([0-9]+)#',$num,$reg)){
				$array[$reg[1]]=$ligne;
				if($unset==true){unset($this->_params[$ruleid][$num]);}
			}
		}
		return $array;
	}
	
	function _ArraySubActions($ruleid,$unset=false){
		
		if(!is_array($this->_params[$ruleid])){return array();}
		$array_global=$this->_params[$ruleid];
		while (list ($num, $ligne) = each ($array_global) ){
			
			if(preg_match('#action\.([0-9]+)#',$num,$reg)){
				$array_actions_matched[$reg[1]]=$ligne;
				if($unset==true){unset($this->_params[$ruleid][$num]);}
			}
		}
		return $array_actions_matched;
	}	
	
	function ParseSubRules($ruleid,$unset=false){
		$array=$this->_ArraySubRules($ruleid,$unset);
		if(!is_array($array)){return null;}
		while (list ($num, $ligne) = each ($array) ){
				
			if(preg_match_all('#<([a-z_]+)>(.+?)<\/([a-z_]+)>#s',$ligne,$hash)){
					while (list ($key, $value) = each ($hash[1])){
						$res[$num][$value]=$hash[2][$key];
						}}}
		return $res;
		}
	
	function ParseSubActions($ruleid,$unset=false){
		$array=$this->_ArraySubActions($ruleid,$unset);
		if(!is_array($array)){return null;}
		while (list ($num, $ligne) = each ($array) ){
			if(preg_match_all('#<([a-z_]+)>(.+?)<\/([a-z_]+)>#s',$ligne,$hash)){
					while (list ($key, $value) = each ($hash[1])){
						$res[$num][$value]=$hash[2][$key];
						}}}
		return $res;
		}
		
	
	function SaveToLDap(){
		
		$ini=new Bs_IniHandler();
		$ini->_params=$this->_params;
		$datas=$ini->toString();
		$ldap=new clladp();
		$hash=$ldap->UserDatas($this->userid);
		$array["SieveScriptsUser"][0]=$datas;		
		if($hash["SieveScriptsUser"]==null){$ldap->Ldap_add_mod($hash["dn"],$array);}else{
			$ldap->Ldap_modify($hash["dn"],$array);
		}
		echo $ldap->ldap_last_error;	
		
	}
		
	function _ParseFieldHeader(){
		$datas=file_get_contents(dirname(__FILE__) . '/databases/sieve_field_header.db');
		$datas_table=explode("\n",$datas);
		while (list ($num, $ligne) = each ($datas_table) ){
			if($ligne<>null){
				if(preg_match('#(.+?)=>(.+)#',$ligne,$reg));
				$this->array_field_header_FULL[trim($reg[1])]=trim($reg[2]);
				$this->array_field_header[trim($reg[1])]='{'.trim($reg[1]).'}';
			}
		}
		$this->array_field_header[null]='{'.trim('select').'}';
		
		$datas=file_get_contents(dirname(__FILE__) . '/databases/sieve_field_conditions.db');
		$datas_table=explode("\n",$datas);
		while (list ($num, $ligne) = each ($datas_table) ){
			if($ligne<>null){
				if(preg_match('#(.+?)=>(.+?)->(null|not)#',$ligne,$reg));{
				if(strtolower($reg[3])=='null'){$reg[3]=null;}
				$this->array_field_conditions_FULL[trim($reg[1])]=array('CON'=>trim($reg[2]),'NOT'=>$reg[3]);
				$this->array_field_conditions[trim($reg[1])]='{'.trim($reg[1]).'}';
				}
			}
		}
		$this->array_field_conditions[null]='{'.trim('select').'}';	
		
		$datas=file_get_contents(dirname(__FILE__) . '/databases/sieve_field_conditions_size.db');
		$datas_table=explode("\n",$datas);
		if(!is_array($datas_table)){return array();}
		while (list ($num, $ligne) = each ($datas_table) ){
			if($ligne<>null){
				if(preg_match('#(.+?)=>(.+)#',$ligne,$reg));
				$this->array_field_conditions_size_FULL[trim($reg[1])]=trim($reg[2]);
				$this->array_field_conditions_size[trim($reg[1])]='{'.trim($reg[1]).'}';
			}
		}
		$this->array_field_conditions_size[null]='{'.trim('select').'}';	

	
		
		$datas=file_get_contents(dirname(__FILE__) . '/databases/db.headers.txt');
		$datas_table=explode("\n",$datas);
		while (list ($num, $ligne) = each ($datas_table) ){
			if($ligne<>null){
				$this->array_field_headers_message[trim($ligne)]=trim($ligne);
			}
		}
		$this->array_field_headers_message[null]='{'.trim('select').'}';			
		
		$datas=file_get_contents(dirname(__FILE__) . '/databases/sieve_field_actions.db');
		$datas_table=explode("\n",$datas);
		while (list ($num, $ligne) = each ($datas_table) ){
			if($ligne<>null){
				$this->array_field_actions[trim($ligne)]='{'.trim($ligne).'}';
			}
		}
		$this->array_field_actions[null]='{'.trim('select').'}';			
		
			
		}
	
	function Connect(){
		if(!class_exists("sieve")){
			$this->error="{error_please_install_phpsieve}";
			return false;		
		}
		
		 $this->conn=new sieve($this->server_name, $this->server_port,$this->username,$this->password,'','LOGIN');
		 return $this->conn->sieve_login();
		}
	function GetScriptInfos($scriptname){
		if($this->conn->sieve_getscript($scriptname)){
			return $this->conn->response;
		}
	}
	
	function LoadImapFoldersList($userid){
		$cyrus=new cyrus();
		$usr=str_replace('.','\.',$userid);
		$hash=$cyrus->LoadMailBoxFolders($userid);
		if(is_array($hash)){
			while (list ($num, $imap) = each ($hash) ){
				if(preg_match('#user\/'.$usr.'\/(.+)#',$imap,$reg)){
					$res["INBOX/{$reg[1]}"]=$reg[1];
				}
				
			}
		}
			
		
		$res["INBOX"]="INBOX";
		return $res;
		
		
	}
	
	function GenerateScripts($ruleid){
		$hash_rules=$this->ParseSubRules($ruleid);
		$hash_actions=$this->ParseSubActions($ruleid);	
		$headersFull=$this->array_field_header_FULL;
		
		if(!is_array($hash_rules)){return null;}
		if(!is_array($hash_actions)){return null;}
		
		
		while (list ($key, $value) = each ($hash_rules) ){
			$field_header=$value["field_header"];
			$field_condition=$value["field_conditions"];
			$field_andor=$value["field_andor"];
			$string=$value["string"];
			$object=$headersFull[$field_header];
			$not=$this->array_field_conditions_FULL[$field_condition]["NOT"];
			$condition=$this->array_field_conditions_FULL[$field_condition]["CON"];
			
			
			if($string<>null){
				switch ($object) {
					case 'SIZE':
						$object=$this->array_field_conditions_size_FULL[$field_header];
						$line='size: ' ."$object $string";
						break;
					default:
						$line="$not address:$condition  $object \"$string\"";
						break;
				}
			
			$array[$field_andor][]=$line;
			}
			
		}
		
		$count_and=count($array["and"]);
		$count_or=count($array["or"]);
	
		if($count_or>0 && $count_and>0){
			$myline='if anyof('."allof(" .implode(",\n\t",$array["and"]) . "),\n\t" .implode(",\n\t",$array["or"]) . "\n\t)";
		}
		
		if($count_or==0 && $count_and>0){
			$myline="if allof(" .implode(",\n\t",$array["and"]) . ")";
		}
		
		if($count_or>0 && $count_and==0){
			$myline="if anyof(" .implode(",\n\t",$array["or"]) . ")";
		}

		while (list ($key, $value) = each ($hash_actions) ){
			$action=$value["action"];
			$operation=$value["operation"];
			
			if($operation<>"NULL"){	
				switch ($action) {
					case 'vacation':
						$tbl=explode('\|\|',$operation);
						$days=$tbl[0];
						$subject=$tbl[1];
						$text=$tbl[2];
						$text=str_replace('<br>',"\n",$text);
						while (list ($n1, $em) = each ($this->array_mails) ){
							$lm[]="\"$em\"";
						}
						
						$finals_actions=$finals_actions . "\t\tvacation :days $days :addresses [" . implode(",",$lm)."] :subject \"$subject\" \"$text\";\n";
						break;
					 default:
					 	$finals_actions=$finals_actions . "\t\t$action \"$operation\";\n";
					 	break;
				}
			}else{
				$finals_actions=$finals_actions . "\t\t$action;\n";}
		}
		
		return "$myline{"."\n".$finals_actions."\t}";
	}
	
	
	function GenerateAllrules(){
		
		$html="require [\"envelope\", \"fileinto\", \"reject\", \"notify\", \"vacation\", \"regex\", \"relational\", \"comparator-i;ascii-numeric\"];";
		while (list ($key, $value) = each ($this->_params) ){
			$count=$count+1;
			if($key<>null){
				$rules=$this->GenerateScripts($key,true);
				if($rules<>null){
					
					if($count>1){$esl="els";}else{$esl=null;}
					$html=$html . "\n\n##### Rule " . $this->_params[$key]["RuleName"] ." #####\n$esl$rules\n";
				}
			}
		
			
		}
		
	return $html;	
	}
	
	function SaveToSieve(){
		$script=$this->GenerateAllrules();
		//if($this->Connect()==false){echo "Error logon";return false;}
		$manage=new Managesieve();
		$manage->open('localhost','2000');
		if($manage->authenticate($this->username,$this->password)==false){
			echo $manage->_errstr;
			return false;
		}
		
		if($manage->putScript('artica_script',$script)==false){
			echo $manage->_errstr;
			return false;
		}
		
		if($manage->setActive('artica_script')){
			echo "success";
		}
		
		
		/*if($this->conn->sieve_sendscript('artica_script',$script)==false){
			echo "Error uploading script\nerr n." . $this->conn->error . "\n";
			print_r($this->conn->error_raw);
			return false;
		}
		$this->conn->sieve_setactivescript('artica_script');
		echo $sieve->error;
		$this->conn->sieve_logout();
		*/
		}
	
	
	
}

/*
Array
(
    [status] => ENABLED
    [control] => elseif
    [matchAny] => 0
    [conditions] => Array
        (
            [0] => Array
                (
                    [type] => address
                    [header] => from
                    [matchStr] => toto@titi.com
                    [matchType] => :contains
                )

        )

    [actions] => Array
        (
            [0] => Array
                (
                    [type] => reject
                    [message] => rejected
                )

            [] => Array
                (
                    [type] => fileinto
                    [address] => toot@tiit.com
                    [message] => rejected
                    [folder] => INBOX/Junk
                )

        )

)
*/
 
?>