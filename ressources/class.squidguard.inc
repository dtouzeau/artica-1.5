<?php

include_once(dirname(__FILE__)."/class.dansguardian.inc");
include_once(dirname(__FILE__)."/class.groups.inc");
include_once(dirname(__FILE__)."/class.c-icap-filter.inc");


class squidguard{
	var $categories=array();
	var $rules=array();
	var $groupsofRule=array();
	var $DefaultRules=array();
	var $DestOfRules=array();
	var $CategoriesDefinitions=array();
	var $rulesOK=array();
	var $ldap_server;
	var $ldap_port;
	var $ldap_suffix;
	var $EnableWhiteList=false;
	var $EnableBlackList=false;
	var $SquidGuardIPWeb=null;
	var $EnableUfdbGuard=0;
	var $UfdbGuard_conf=null;
	var $EnableSquidFilterWhiteListing=0;
	function squidguard(){
		$GLOBALS["guarddb"]="/var/lib/squidguard";
		$this->rules=$this->GetRules();
		$sock=new sockets();
		$this->EnableSquidFilterWhiteListing=$sock->GET_INFO("EnableSquidFilterWhiteListing");
	}
	
	
	function BuildConf(){
		
		$ldap=new clladp();
		$sock=new sockets();
		$usrs=new usersMenus();
		$this->ldap_port=$ldap->ldap_port;
		$this->ldap_server=$ldap->ldap_host;
		$this->ldap_suffix=$ldap->suffix;
		$this->SquidGuardIPWeb=$sock->GET_INFO("SquidGuardIPWeb");
		$this->EnableUfdbGuard=$sock->GET_INFO('EnableUfdbGuard');
		if($this->SquidGuardIPWeb==null){$this->SquidGuardIPWeb="http://$usrs->hostname:9020/exec.squidguard.php";}
		$this->SquidGuardIPWeb=str_replace('.(none)',"",$this->SquidGuardIPWeb);
		echo "Starting......: squidGuard verbose mode={$GLOBALS["VERBOSE"]}\n";
		
	
		$ldapbinddn="cn=$ldap->ldap_admin,$ldap->suffix";
		$conf[]="#Ufdbguard enabled=$this->EnableUfdbGuard";
		$conf[]="dbhome /var/lib/squidguard";
		$conf[]="logdir /var/log/squid";
	
		if($this->EnableUfdbGuard==1){
			$ufdbguardConfig=unserialize(base64_decode($sock->GET_INFO("ufdbguardConfig")));
			if($ufdbguardConfig["enforce-https-with-hostname"]==null){$ufdbguardConfig["enforce-https-with-hostname"]=0;}
			if($ufdbguardConfig["enforce-https-official-certificate"]==null){$ufdbguardConfig["enforce-https-official-certificate"]=0;}
			if($ufdbguardConfig["https-prohibit-insecure-sslv2"]==null){$ufdbguardConfig["https-prohibit-insecure-sslv2"]=0;}			
			
			while (list ($key, $line) = each ($ufdbguardConfig) ){
				if($line==0){$ufdbguardConfig[$key]="off";}
				if($line==1){$ufdbguardConfig[$key]="on";}
				if($line==null){$ufdbguardConfig[$key]="off";}
			}
			
			$conf[]="logblock on";
			$conf[]="#LOGGING";
			$conf[]="logall off";
			$conf[]="max-logfile-size  20000000";
			$conf[]="";
			$conf[]="analyse-uncategorised-urls on";
			$conf[]="check-proxy-tunnels queue-checks";
			$conf[]="safe-search on";
			$conf[]="#SSL enforce:";
			$conf[]="enforce-https-with-hostname {$ufdbguardConfig["enforce-https-with-hostname"]}";
			$conf[]="enforce-https-official-certificate {$ufdbguardConfig["enforce-https-official-certificate"]}";
			$conf[]="https-prohibit-insecure-sslv2 {$ufdbguardConfig["https-prohibit-insecure-sslv2"]}";
			$conf[]="";
 			$conf[]="";			
		}
		
		
 		$conf[]=$this->BUILD_SRC();
 		$conf[]="";
 		$conf[]="";
 		$conf[]=$this->BUILD_DEST();
 		$conf[]="";
 		$conf[]="";
 		$conf[]=$this->BUILD_ACLS(); 		
 		
 		
 		
 		return implode("\n",$conf);
		
		
	}
	
	private function GetRules(){
		if(is_array($GLOBALS["RULES_DG"])){return $GLOBALS["RULES_DG"];}
		$cicap=new cicap_filter();
		$array=$cicap->rules;
		if(!is_array($array)){return array();}
		$GLOBALS["RULES_DG"]=$array;
		return $array;
	}
	
	private function BUILD_SRC(){
		$rules=$this->GetRules();
		$this->loadusers();
		if(is_array($this->groupsofRule)){
			echo "Starting......: squidGuard no source rules set\n";
			reset($this->groupsofRule);
			while (list ($ruleID, $sources) = each ($this->groupsofRule)){
				while (list ($a, $b) = each ($sources)){
					if(trim($b)==null){continue;}
					$c[trim($b)]=trim($b);
				}
				if(count($c)>0){	
					$this->rulesOK[$ruleID]=true;
					$t[]="\nsrc {$rules[$ruleID]} {";
					$t[]=implode("\n",$c);
					$t[]="}\n";
				}
				unset($c);
			}
		}
		
		$whitelisted=$this->WhiteList();
		$whitelisted[]="\tip 127.0.0.1";
		echo "Starting......: squidGuard ". count($whitelisted)." whitelisted IP(s)\n";
		if(is_array($whitelisted)){
			
		while (list ($a, $b) = each ($whitelisted)){
				if(trim($b)==null){continue;}
				if($this->CHECK_IP_PATTERN($b)){continue;}
				$c[trim($b)]=trim($b);
			}
			if(count($c)>0){			
				$this->EnableWhiteList=true;
				$t[]="\nsrc whitelisted {";
				$t[]=implode("\n",$c);
				$t[]="}\n";
			}
			unset($c);
		}
		
		
		$blacklisted=$this->Blacklist();
		echo "Starting......: squidGuard ". count($blacklisted)." blacklisted IP(s)\n";
		if(is_array($blacklisted)){
			while (list ($a, $b) = each ($blacklisted)){
					if(trim($b)==null){continue;}
					if($this->CHECK_IP_PATTERN($b)){continue;}
					$c[trim($b)]=trim($b);
				}
			if(count($c)>0){					
				$this->EnableBlackList=true;
				$t[]="\nsrc blacklisted {";
				$t[]=implode("\n",$c);
				$t[]="}\n";
			}
				unset($c);
		}		
		
		
		if(is_array($t)){
			return implode("\n",$t);
		}
	}
	
	private function CHECK_IP_PATTERN($value){
		if(preg_match("#[0-9\.]+\/(.+)#",$b,$re)){
			$re[1]=trim($re[1]);
			if(!is_numeric($re[1])){
				echo "Starting......: squidGuard $value pattern (prefix not a numeric) corrupted\n";
				return false;	
			}
			
			if(strlen($re[1])>2){
				echo "Starting......: squidGuard $value pattern (prefix >2 numbers) corrupted\n";
				return false;
			}
		}
		return true;
	}
	
	
	private function BUILD_ACLS(){
		$rules=$this->GetRules();
		echo "Starting......: squidGuard Wheitelisting is set to \"$this->EnableSquidFilterWhiteListing\"\n";
		$all="all";
		if($this->EnableSquidFilterWhiteListing){$all="none";}
		
		$redirect_pattern="clientaddr=%a&clientname=%n&clientuser=%i&clientgroup=%s&targetgroup=%t&url=%u";
		
		
		
		if(!preg_match("#^http.*?\/\/#",$this->SquidGuardIPWeb)){
			$this->SquidGuardIPWeb="https://$this->SquidGuardIPWeb";
			if(!preg_match("#squidguard\.php#",$this->SquidGuardIPWeb)){$this->SquidGuardIPWeb=$this->SquidGuardIPWeb."/exec.squidguard.php";}
		}
		
		
		
		$t[]="\nacl{";
			if(is_array($this->DestOfRules)){
				while (list ($ruleID, $categories) = each ($this->DestOfRules)){
					if(!$this->rulesOK[$ruleID]){continue;}
					$c=array();
					while (list ($a, $b) = each ($categories)){
						$b=trim($b);
						if(strpos($b,"/")>0){
							echo "Starting......: $b refused\n";
							continue;
						}
						if(strpos($b,"[")>0){
							echo "Starting......: $b refused\n";
							continue;
						}						
						
						if(!preg_match("#.*?F-[0-9]+#",$b)){
							if($this->EnableSquidFilterWhiteListing==1){
								echo "Rule:$b\n";
								$b=str_replace("!","",$b);
							}
						}
						$c[$b]=$b;
					}
					$categories_imploded=implode(" ",$c);
			
					
					$t[]=" {$rules[$ruleID]} {";
					$t[]="   pass $categories_imploded $all";
					$t[]="   redirect 302:$this->SquidGuardIPWeb?$redirect_pattern";
					$t[]=" }";
				}
			}
			
			
		if($this->EnableWhiteList){
			$t[]=" whitelisted {";
			$t[]="   pass all";
			$t[]=" }";
		}
		
		
		if($this->EnableBlackList){
			$t[]=" blacklisted {";
			$t[]="   pass none";
			$t[]="   redirect $this->SquidGuardIPWeb?$redirect_pattern";
			$t[]=" }";
		}		

		if(is_array($this->DefaultRules["rule"])){
			while (list ($a, $b) = each ($this->DefaultRules["rule"])){
				if(!preg_match("#.*?F-[0-9]+#",$b)){
						if($this->EnableSquidFilterWhiteListing==1){
							echo "replace Rule:[$b]\n";
							$b=str_replace("!","",$b);}
							}
					$c[$b]=$b;
				}
				$default_categories=implode(" ",$c);
			}
		
			if($GLOBALS["ADD_UFDBGUARD_SSL"]){$ufdbgard_ssl_cat="security ";}
		
			$t[]=" default {";
			$t[]="   pass $ufdbgard_ssl_cat$default_categories $all";
			$t[]="   redirect $this->SquidGuardIPWeb?$redirect_pattern";
			$t[]=" }";
			$t[]="}";
			
			return implode("\n",$t);
			
	}
	
	
	private function BUILD_DEST(){
		$rules=$this->GetRules();
		$GLOBALS["ADD_UFDBGUARD_SSL"]=false;
		$add_ufdbgard_secu=false;
		$this->loadusers();
		if(!is_array($this->DestOfRules)){
			echo "Starting......: squidGuard no dest rules set\n";
			return;
		}
		
		while (list ($name, $sources) = each ($this->CategoriesDefinitions)){
			
			while (list ($a, $b) = each ($sources)){
				
				$c[$b]=$b;
			}			
			if($this->EnableUfdbGuard==1){
				$t[]="\ncategory $name {";
			}else{
				$t[]="\ndest $name {";
			}
			$t[]=implode("\n",$c);
			$t[]="}\n";
			unset($c);
		}

		
		if($this->EnableUfdbGuard==1){
			$sock=new sockets();
			
			$ufdbguardConfig=unserialize(base64_decode($sock->GET_INFO("ufdbguardConfig")));
			if($ufdbguardConfig["enforce-https-with-hostname"]==null){$ufdbguardConfig["enforce-https-with-hostname"]=0;}
			if($ufdbguardConfig["enforce-https-official-certificate"]==null){$ufdbguardConfig["enforce-https-official-certificate"]=0;}
			if($ufdbguardConfig["https-prohibit-insecure-sslv2"]==null){$ufdbguardConfig["https-prohibit-insecure-sslv2"]=0;}			
			
			while (list ($key, $line) = each ($ufdbguardConfig) ){
				if($line==0){$ufdbguardConfig[$key]="off";}
				if($line==1){$ufdbguardConfig[$key]="on";}
				if($line==null){$ufdbguardConfig[$key]="off";}
			}
			
			if($ufdbguardConfig["enforce-https-with-hostname"]=="on"){$add_ufdbgard_secu=true;}
			if($ufdbguardConfig["enforce-https-official-certificate"]=="on"){$add_ufdbgard_secu=true;}
			if($ufdbguardConfig["https-prohibit-insecure-sslv2"]=="on"){$add_ufdbgard_secu=true;}
			if($add_ufdbgard_secu){$GLOBALS["ADD_UFDBGUARD_SSL"]=true;}
			if($add_ufdbgard_secu){
				$t[]="\ncategory security {";
				$t[]="\toption enforce-https-with-hostname {$ufdbguardConfig["enforce-https-with-hostname"]}";
				$t[]="\toption enforce-https-official-certificate {$ufdbguardConfig["enforce-https-official-certificate"]}";
				$t[]="\toption https-prohibit-insecure-sslv2 {$ufdbguardConfig["https-prohibit-insecure-sslv2"]}";
				$t[]="}\n";
				
			}
			
		
		}
		
		if(is_array($t)){
			return implode("\n",$t);
		}
		
		
		
		
	}	
	
	private function loadusers(){
		if(!is_array($this->rules)){return;}
		reset($this->rules);
		
		while (list ($num, $rulename) = each ($this->rules)){
			$this->searchgroups($num);
			$this->dump_exceptionSites($num);
			$this->dump_blockfiles($num);
			$this->SearchCategories($num);
		}
		$this->DefaultCategories();
	}
	
	private function searchgroups($RuleID){
		$sql="SELECT RuleID,group_id,dngroup FROM `dansguardian_groups` WHERE RuleID=$RuleID";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$group_id=$ligne["group_id"];
			$dngroup=trim($ligne["dngroup"]);
			echo "Starting......: squidGuard($RuleID) group $group_id \"$dngroup\"\n";
			if($dngroup<>null){$u=$this->BuildUserLists($dngroup);}else{$u=$this->BuildUserLists($ligne["group_id"]);}
			if(trim($u)<>null){
				$this->groupsofRule[$RuleID][]=$u;
			}
		}
		
		$sql="SELECT RuleID,pattern FROM `dansguardian_ipgroups` WHERE RuleID=$RuleID";
		$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["pattern"]==null){continue;}
				if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$ligne["pattern"])){continue;}
				$this->groupsofRule[$RuleID][]="\tip\t{$ligne["pattern"]}";
			
		}
	}
	
	
	private function BuildUserLists($gpid){
		
		$group=new groups($gpid);
		echo "Starting......: squidGuard Group $gpid store ". count($group->members)." users\n";
		if(count($group->members)==0){return null;}
		if(count($group->members)<21){
				$imploded=implode(" ",$group->members);
				$imploded=str_replace("$","",$imploded);
				return "\tuser\t$imploded";
		}
		$conf=implode("\n",$group->members);
				
		if($GLOBALS["VERBOSE"]){echo "write {$GLOBALS["guarddb"]}/$gpid\n";}
		echo "Starting......: squidGuard write {$GLOBALS["guarddb"]}/$gpid user file\n";
		file_put_contents("{$GLOBALS["guarddb"]}/GroupNumberID$gpid",$conf);
		if(!is_file("{$GLOBALS["guarddb"]}/GroupNumberID$gpid")){
			echo "Starting......: squidGuard unable to write {$GLOBALS["guarddb"]}/$gpid file!\n";
		}
		return "\tuserlist\tGroupNumberID{$gpid}";
	}
	//
	
	
	private function CheckHostname($ip){
		if(trim($ip)==null){return null;}
		if($GLOBALS["VERBOSE"]){echo "CheckHostname($ip)\n";}
		if(substr($ip,strlen($ip)-1,1)=="$"){$ip=substr($ip,0,strlen($ip)-1);}
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$ip)){
			if($GLOBALS["VERBOSE"]){echo "CheckHostname($ip) is an hostname, try to resolv\n";}
			$ip=gethostbyname($ip);
		}
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$ip)){$ip=null;}
		return $ip;
	}
	
	
	
	private function WhiteList(){
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename ='exceptioniplist'";
		if($GLOBALS["VERBOSE"]){echo "WhiteList($sql)\n";}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ligne["pattern"]=$this->CheckHostname($ligne["pattern"]);
			if($ligne["pattern"]==null){continue;}
			$array[$ligne["pattern"]]="\tip\t{$ligne["pattern"]}";
		}
		return $array;
	}
	
	private function Blacklist(){
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename ='bannediplist'";
		if($GLOBALS["VERBOSE"]){echo "Blacklist($sql)\n";}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ligne["pattern"]=$this->CheckHostname($ligne["pattern"]);
			if($ligne["pattern"]==null){continue;}
			$array[$ligne["pattern"]]="\tip {$ligne["pattern"]}";
		}
		return $array;
	}		
		
		
	private function DefaultCategories(){
	if($GLOBALS["VERBOSE"]){echo "######################## DEFAULT CATEGORY ########################\n";}
		$RuleID=1;
		$commutator="!";
		if($this->EnableSquidFilterWhiteListing){$commutator=null;}
		$q=new mysql();
			
			$main_path="{$GLOBALS["guarddb"]}/personal-categories/W-1";
			$this->DefaultRules["rule"][]="W-1";
			$this->CategoriesDefinitions["W-1"][]="\tdomainlist personal-categories/W-1/domains";
			if($this->EnableUfdbGuard<>1){$this->CategoriesDefinitions["W-1"][]="\turllist personal-categories/W-1/urls";}
			$this->CategoriesDefinitions["W-1"][]="\texpressionlist personal-categories/W-1/expressions";
			@file_put_contents("$main_path/urls","www. ".md5(time()).".tg\n");
			@file_put_contents("$main_path/expressions","www. ".md5(time()).".tg\n");
			$this->dump_exceptionSites(1);
			
			$this->DefaultRules["rule"][]="!F-1";
			$this->CategoriesDefinitions["F-1"][]="\texpressionlist personal-categories/filesblock-{$this->rules[$RuleID]}/expressions";
			$fileBlock_path="{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}";
			
			
			if(!is_dir("$fileBlock_path")){
				@mkdir("$fileBlock_path",755,true);
			}
			
			if(!is_file("$fileBlock_path/expressions")){@file_put_contents("$fileBlock_path/expressions","");}
			if(!is_file("$fileBlock_path/urls")){@file_put_contents("$fileBlock_path/urls","");}
			if(!is_file("$fileBlock_path/domains")){@file_put_contents("$fileBlock_path/domains","");}
			
			if($this->EnableUfdbGuard<>1){ $this->CategoriesDefinitions["F-1"][]="\turllist personal-categories/filesblock-{$this->rules[$RuleID]}/urls";}
			$this->CategoriesDefinitions["F-1"][]="\tdomainlist personal-categories/filesblock-{$this->rules[$RuleID]}/domains";
			@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
			@file_put_contents("$main_path/domains","nowebsite.com");
			
			
		
		
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'bannedsitelist' AND RuleID=1;";
		
		if($GLOBALS["VERBOSE"]){echo "$sql\n";}
		
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$sql\nError $q->mysql_error\n";}}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			@mkdir("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}",755,true);
			$addplus="plus-".$this->substrCategory($ligne["pattern"]);
			$addplus=str_replace("/","-",$addplus);
			$addplus=str_replace("_","-",$addplus);
			$addplus_com=$addplus."-artica";
			
			$categoryName=$this->substrCategory($ligne["pattern"]);
			if($GLOBALS["VERBOSE"]){echo "Default rule cat -> \"{$ligne["pattern"]}\" ($categoryName) (default)\n";}
			
			
			
			$this->CategoriesDefinitions[$categoryName][]="\tdomainlist {$ligne["pattern"]}/domains";
			if($this->EnableUfdbGuard<>1){ $this->CategoriesDefinitions[$categoryName][]="\turllist {$ligne["pattern"]}/urls";}
			$this->CategoriesDefinitions[$categoryName][]="\texpressionlist {$ligne["pattern"]}/expressions";					
			$this->DefaultRules["rule"][]="!$categoryName";	
			file_put_contents("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/urls","www. ".md5(time()).".tg");
			file_put_contents("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/expressions","\n");
			
			$this->CategoriesDefinitions[$addplus][]="\tdomainlist web-filter-plus/BL/{$ligne["pattern"]}/domains";
			if($this->EnableUfdbGuard<>1){ $this->CategoriesDefinitions[$addplus][]="\turllist web-filter-plus/BL/{$ligne["pattern"]}/urls";}
			$this->CategoriesDefinitions[$addplus][]="\texpressionlist web-filter-plus/BL/{$ligne["pattern"]}/expressions";
			$this->DefaultRules["rule"][]="{$commutator}$addplus";
			
			file_put_contents("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/urls","www. ".md5(time()).".tg");
			file_put_contents("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/expressions","\n");

			$main_path="{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}";
			@mkdir("{$GLOBALS["guarddb"]}/blacklist-artica",755,true);
			
			$this->CategoriesDefinitions[$addplus_com][]="\tdomainlist blacklist-artica/{$ligne["pattern"]}/domains";
			if($this->EnableUfdbGuard<>1){ $this->CategoriesDefinitions[$addplus_com][]="\turllist blacklist-artica/{$ligne["pattern"]}/urls";}
			$this->CategoriesDefinitions[$addplus_com][]="\texpressionlist blacklist-artica/{$ligne["pattern"]}/expressions";
			$this->DefaultRules["rule"][]="{$commutator}$addplus_com";
			file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
			@file_put_contents("$main_path/expressions","\n");		

		}

		$count=count($this->DefaultRules["rule"]);
		echo "Starting......: squidGuard Default rule: $count categories\n";
		
		$sql="SELECT category FROM `dansguardian_personal_categories` WHERE category_type = 'enabled' AND RuleID=1;";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$category=trim($ligne["category"]);
			$categoryPrint=$this->substrCategory($category);
			$this->dump_personals($category);	
			if($GLOBALS["VERBOSE"]){echo "Default rule personal cat -> \"$category\" (default)\n";}			
				
			$main_path="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint";
			@mkdir($main_path,755,true);
			
			echo "Starting......: squidGuard Personal category $category ($categoryPrint) for Default rule\n";
			$this->CategoriesDefinitions["P-$categoryPrint"][]="\tdomainlist personal-categories/$categoryPrint/domains";
			if($this->EnableUfdbGuard<>1){ $this->CategoriesDefinitions["P-$categoryPrint"][]="\turllist personal-categories/$categoryPrint/urls";}
			$this->CategoriesDefinitions["P-$categoryPrint"][]="\texpressionlist personal-categories/$categoryPrint/expressions";
			$this->DefaultRules["rule"][]="{$commutator}P-$categoryPrint";
			@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
			@file_put_contents("$main_path/expressions","\n");					
		}		
		
		
		
		
	
	if($GLOBALS["VERBOSE"]){echo "##################################################################\n";}
		
	}
	
	
	private function SearchCategories($RuleID){
		$commutator="!";
		if($this->EnableSquidFilterWhiteListing){$commutator=null;}
		if($GLOBALS["VERBOSE"]){echo "SearchCategories($RuleID)\n";}
		$main_path="{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID";
		@mkdir($main_path,755,true);
		$this->DestOfRules[$RuleID]["W-$RuleID"]="W-$RuleID";
		$this->CategoriesDefinitions["W-$RuleID"][]="\tdomainlist personal-categories/W-$RuleID/domains";
		$this->CategoriesDefinitions["W-$RuleID"][]="\turllist personal-categories/W-$RuleID/urls";
		$this->CategoriesDefinitions["W-$RuleID"][]="\texpressionlist personal-categories/W-$RuleID/expressions";
		
		
		@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
		@file_put_contents("$main_path/expressions","\n");				
		
		
		$main_path="{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}";
		@mkdir($main_path,755,true);
		$this->DestOfRules[$RuleID]["F-$RuleID"]="!F-$RuleID";
		$this->CategoriesDefinitions["F-$RuleID"][]="\texpressionlist personal-categories/filesblock-{$this->rules[$RuleID]}/expressions";
		$this->CategoriesDefinitions["F-$RuleID"][]="\turllist personal-categories/filesblock-{$this->rules[$RuleID]}/urls";
		$this->CategoriesDefinitions["F-$RuleID"][]="\tdomainlist personal-categories/filesblock-{$this->rules[$RuleID]}/domains";
		@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
		if(!is_file("$main_path/domains")){@file_put_contents("$main_path/domains","\n");}			
		
		
		
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'bannedsitelist' AND RuleID=$RuleID;";
		if($GLOBALS["VERBOSE"]){echo "$sql\n";}
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$original_category=$ligne["pattern"];
				$addplus="plus-".$this->substrCategory($ligne["pattern"]);
				$addplus_com=$addplus."-artica";

				
				$main_path="{$GLOBALS["guarddb"]}/{$ligne["pattern"]}";
				@mkdir($main_path,755,true);
				$categoryName=$this->substrCategory($ligne["pattern"]);
				
				$this->CategoriesDefinitions[$categoryName][]="\tdomainlist {$ligne["pattern"]}/domains";
				$this->CategoriesDefinitions[$categoryName][]="\turllist {$ligne["pattern"]}/urls";
				$this->CategoriesDefinitions[$categoryName][]="\texpressionlist {$ligne["pattern"]}/expressions";
				$this->DestOfRules[$RuleID][$categoryName]="$commutator$categoryName";	
				file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
				file_put_contents("$main_path/expressions","\n");				
					
				$main_path="{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}";
				@mkdir($main_path,755,true);
				
				$this->CategoriesDefinitions[$addplus][]="\tdomainlist web-filter-plus/BL/{$ligne["pattern"]}/domains";
				$this->CategoriesDefinitions[$addplus][]="\turllist web-filter-plus/BL/{$ligne["pattern"]}/urls";
				$this->CategoriesDefinitions[$addplus][]="\texpressionlist web-filter-plus/BL/{$ligne["pattern"]}/expressions";
				$this->DestOfRules[$RuleID][$addplus]="$commutator$addplus";
				@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
				@file_put_contents("$main_path/expressions","\n");	
				if($GLOBALS["VERBOSE"]){echo "Adding professional category [$addplus] => {$ligne["pattern"]}\n";}
				
				
					
				$main_path="{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}";	
				@mkdir($main_path,755,true);
				if($GLOBALS["VERBOSE"]){echo "SearchCategories($RuleID):$original_category -> compile_artica_category()\n";}
				$this->compile_artica_category($RuleID,$original_category,$main_path,$addplus);
				$this->CategoriesDefinitions[$addplus_com][]="\tdomainlist blacklist-artica/{$ligne["pattern"]}/domains";
				$this->CategoriesDefinitions[$addplus_com][]="\turllist blacklist-artica/{$ligne["pattern"]}/urls";
				$this->CategoriesDefinitions[$addplus_com][]="\texpressionlist blacklist-artica/{$ligne["pattern"]}/expressions";
				$this->DestOfRules[$RuleID][$addplus_com]="$commutator$addplus_com";
				@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
				@file_put_contents("$main_path/expressions","\n");				
			}
			
			$sql="SELECT category FROM `dansguardian_personal_categories` WHERE category_type = 'enabled' AND RuleID=$RuleID;";
			if($GLOBALS["VERBOSE"]){echo "$sql\n";}
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$category=trim($ligne["category"]);
				$categoryPrint=$this->substrCategory($category);
				$this->dump_personals($category);			
				$main_path="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint";	
				if($GLOBALS["VERBOSE"]){echo "$category:: {$GLOBALS["guarddb"]}/personal-categories/$categoryPrint\n";}
				$this->compile_personal_category($RuleID,$category,$main_path,$categoryPrint);
				@mkdir($main_path,0666,true);
				echo "Starting......: squidGuard Personal category $category ($categoryPrint)\n";
				$this->CategoriesDefinitions["P-$categoryPrint"][]="\tdomainlist personal-categories/$categoryPrint/domains";
				$this->CategoriesDefinitions["P-$categoryPrint"][]="\turllist personal-categories/$categoryPrint/urls";
				$this->CategoriesDefinitions["P-$categoryPrint"][]="\texpressionlist personal-categories/$categoryPrint/expressions";
				@file_put_contents("$main_path/urls","www. ".md5(time()).".tg");
				@file_put_contents("$main_path/expressions","\n");						
				$this->DestOfRules[$RuleID]["P-$categoryPrint"]="{$commutator}P-$categoryPrint";
			}

	}
	
	
	private function dump_exceptionSites($RuleID){
		if($GLOBALS["dump_exceptionSites"]["$RuleID"]){return;}
		@mkdir("{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID",0755,true);
		$File="{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/domains";
		
		$count=0;
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'exceptionsitelist' 
		AND RuleID=$RuleID AND enabled=1;";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$fh = fopen($File, 'w');
		
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$datas=trim($ligne["pattern"]);
				
				if($datas==null){continue;}
				if(!$this->VerifyDomainCompiledPattern($datas)){
					$skipped++;
					echo "Starting......: squidGuard($RuleID) \"$datas\": refused\n";
					continue;
				}
			
				$count=$count+1;
				fwrite($fh, $datas."\n");	
			}
		$unix=new unix();
		$unix->send_email_events("category W-$RuleID was compiled with $count rows","This category was recompiled from Mysql Database","proxy");
		echo "Starting......: squidGuard($RuleID) Exception sites: {$this->rules[$RuleID]} $count rows\n";
		fclose($fh);
		$this->ufdbGenTable($RuleID,"{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID","W-$RuleID");
		$GLOBALS["dump_exceptionSites"]["$RuleID"]=true;
	}
	
	
	private function ufdbGenTable($RuleID,$main_path,$categoryPrint){
		if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
		$time=time();
		$ufdbGenTable=$GLOBALS["CLASS_UNIX"]->find_program("ufdbGenTable");
		if(!is_file("$ufdbGenTable")){echo "Starting......: ufdbGenTable($RuleID) $categoryPrint:ufdbGenTable is not installed\n";return;}
		echo "Starting......: ufdbGenTable($RuleID) $categoryPrint: compiling new pattern\n";
		$category_compile=$categoryPrint;
		if(strlen($category_compile)>15){
			$category_compile=str_replace("recreation_","recre_",$category_compile);
			$category_compile=str_replace("automobile_","auto_",$category_compile);
			$category_compile=str_replace("finance_","fin_",$category_compile);
			if(strlen($category_compile)>15){
				$category_compile=str_replace("_", "", $category_compile);
				if(strlen($category_compile)>15){
					$category_compile=substr($category_compile, strlen($category_compile)-15,15);
				}
			}
		}		
		
		
		
		if(is_file("$main_path/domains.ufdb")){@unlink("$main_path/domains.ufdb");}
		$cmd="$ufdbGenTable -n -D -W -t $category_compile -d $main_path/domains -u $main_path/urls";
		if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
		exec($cmd." 2>&1",$RESULTS);
		if(count($RESULTS)>0){while (list ($a, $b) = each ($RESULTS)){
			if(preg_match("#has optimised subdomains#",$b)){continue;}
			if(preg_match("#is not added because it was already matched#",$b)){continue;}
			if(preg_match("#is stripped for#",$b)){continue;}
			$log[]=$b;
			echo "Starting......: ufdbGenTable($RuleID) $categoryPrint: $b\n";}}

		
		$time=$GLOBALS["CLASS_UNIX"]->distanceOfTimeInWords($time,time());
		$GLOBALS["CLASS_UNIX"]->send_email_events("category Rule:$RuleID/$categoryPrint was compiled to ufdbguard ($time)",
		@implode("\n",$log),"proxy");			
			
	} 
	
	private function compile_personal_category($RuleID,$category,$main_path,$categoryPrint){
		if($GLOBALS["compile_personal_category"]["$RuleID$main_path"]){return;}

		
		$sql="SELECT pattern FROM `dansguardian_personal_categories` WHERE category_type = 'data' AND RuleID=$RuleID;";
		$q=new mysql();
		$unix=new unix();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		@mkdir($main_path,0755,true);
		$fh = fopen($main_path."/domains", 'w');
		$num=mysql_num_rows($results);
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($GLOBALS["VERBOSE"]){echo "writing {$ligne["pattern"]}\n";}
			fwrite($fh, "{$ligne["pattern"]}\n");
		}
		fclose($fh);
		
		if(!is_file("$main_path/urls")){shell_exec("/bin/touch $main_path/urls");}
		
		echo "Starting......: squidGuard($RuleID) $category ($categoryPrint) Personal websites store $num websites\n";
		//$this->ufdbGenTable($RuleID,$main_path,$categoryPrint);
		$GLOBALS["compile_personal_category"]["$RuleID$main_path"]=true;
		//ufdbGenTable
	}
	
	
	public function VerifyDomainCompiledPattern($pattern){
		if(!preg_match("#^(.+?)\.(.+)#",$pattern)){
			echo "Starting......: $pattern is refused\n";
			return false;
		}
		
		return true;
	} 
	
	function compile_artica_category($RuleID,$category,$main_path,$categoryPrint){
		if($GLOBALS["compile_artica_category"]["$RuleID$main_path"]){
			if($GLOBALS["VERBOSE"]){echo "compile_artica_category $RuleID $main_path already done\n";}
			return;
		}
		
		$sql="SELECT pattern FROM `dansguardian_community_categories` WHERE enabled=1 and category='$category'";
		if($GLOBALS["VERBOSE"]){echo $sql."\n";}
		
		$q=new mysql_squid_builder();
		$unix=new unix();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n";}
		@mkdir($main_path,0755,true);
		$fh = fopen($main_path."/domains", 'w');
		$num=mysql_num_rows($results);
		$skipped=0;
		$inserted=0;
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$datas=$ligne["pattern"];
			if($datas==null){continue;}
			if(!$this->VerifyDomainCompiledPattern($datas)){
					$skipped++;
					echo "Starting......: squidGuard($RuleID) $datas: refused\n";
					continue;
				}
				$inserted++;		
			fwrite($fh, "{$ligne["pattern"]}\n");
		}
		fclose($fh);
		
		if(!is_file("$main_path/urls")){shell_exec("/bin/touch $main_path/urls");}
		
		echo "Starting......: squidGuard($RuleID) $category ($categoryPrint) communities websites store $inserted websites and $skipped websites skipped\n";
		//$this->ufdbGenTable($RuleID,$main_path,$categoryPrint);
		$GLOBALS["compile_artica_category"]["$RuleID$main_path"]=true;
		//ufdbGenTable		
	}
	
	
	function categoryToDatabase($category){
		
		
		
	}
	
	
	private function substrCategory($name){
		if($name=="agressive"){$name="cat-agressive";}
		$name=str_replace("(","",$name);
		$name=str_replace(")","",$name);
		$name=str_replace("[","",$name);
		$name=str_replace("]","",$name);
		$name=str_replace(" ","-",$name);
		$name=str_replace("_","-",$name);
		$name=str_replace(".","",$name);
		$name=str_replace("/","-",$name);
		return $name;
		
	}
	
	
	private function dump_personals($category_name){
		if($GLOBALS["CATEGORIES_SAVED"][$category_name]){return;}
		$categoryPrint=$this->substrCategory($category_name);
		
		//echo "Starting......: squidGuard personal: base path {$GLOBALS["guarddb"]}\n";
		@mkdir("{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint",0755,true);
		$File="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint/domains";
		$sql="SELECT pattern FROM `dansguardian_personal_categories` WHERE category_type = 'data' AND category='$category_name';";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$fh = fopen($File, 'w');
		$count=0;
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$datas=trim($ligne["pattern"]);
			$count=$count+1;
			if($datas==null){continue;}
			if(!$this->VerifyDomainCompiledPattern($datas)){
					$skipped++;
					echo "Starting......: squidGuard personal $datas: refused\n";
					continue;
				}
			fwrite($fh, $datas."\n");
			}

		fclose($fh);
		if(!is_file($File)){
			echo "Starting......: squidGuard personal: $category_name ($categoryPrint) failed!\n";
			return;
		}
		echo "Starting......: squidGuard personal: $category_name $count ($categoryPrint) rows\n";
		$GLOBALS["CATEGORIES_SAVED"][$category_name]=true;
		
	}
	
	private function dump_blockfiles($RuleID){
		@mkdir("{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}",0755,true);
		$File="{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}/expressions";
		@unlink($File);
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE 
			filename ='bannedextensionlist'
			AND enabled=1 AND RuleID=$RuleID";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ext=trim($ligne["pattern"]);
			if($ext==null){continue;}
			if(preg_match("#\.(.+)#",$ext,$re)){$f[]=$re[1];}
		}
		$count=count($f);
		
		
		if(is_array($f)){
			echo "Starting......: squidGuard block files: {$this->rules[$RuleID]} $count rows\n";
			$expression="(/\.)(". implode("|",$f).")$\n";
			$expression=$expression."(/\.)(". implode("|",$f).")(/\.)(". implode("|",$f).")$";
			file_put_contents($File,$expression);
		}
		
		
	}
	
	
	
	
}

?>