<?php
include_once(dirname(__FILE__)."/class.dansguardian.inc");
include_once(dirname(__FILE__)."/class.groups.inc");
$GLOBALS["guarddb"]="/var/lib/squidguard";

class cicap_filter{
	var $rules=array();
	var $groups=array();
	var $hash_groups=array();
	var $hash_rules_acls=array();
	var $hash_all_cats=array();
	var $hash_all_profiles=array();
	var $hash_profiles=array();
	var $hash_ProfileAccess=array();
	function cicap_filter(){
		$this->rules=$this->init_rules();
		
	}
	
	public function Buildacls(){
		$this->BuildUsersAndGroups();
		$conf[]="";
		$conf[]="GroupSourceByGroup hash:/etc/c-icap/c-icap-groups.txt";
		$conf[]="GroupSourceByUser hash:/etc/c-icap/c-icap-user-groups.txt";
		$conf[]="";
		if(!is_array($this->rules)){return;}
		reset($this->rules);
		if(is_array($this->groups)){reset($this->groups);}
		
		
		while (list ($RuleID, $hash) = each ($this->groups)){
			if($GLOBALS["VERBOSE"]){echo "Starting......: C-ICAP Building ACL for $RuleID rulename {$this->rules[$RuleID]}\n";}
			$conf[]="";
			$conf[]="#ACLS FOR SQUIDGUARD RULE ". $this->rules[$RuleID];
			$conf[]="";
			$conf[]="#IP Addresses";
			if(is_array($hash["net"])){
				$ipname="ACLFORRULE{$RuleID}";
				while (list ($index, $ip) = each ($hash["net"])){
					if(preg_match("#([0-9\.]+)\/([0-9\.]+)#",$ip)){
						//$this->hash_rules_acls[$RuleID][]="ACLFORRULE{$RuleID}NET$index";
						//$conf[]="acl ACLFORRULE{$RuleID}NET$index src $ip";
						continue;
					}
					$ip=str_replace(".","\.",$ip);
					$ipsToadd[]="/$ip/";					
				}
				$conf[]="acl $ipname icap_header{X-Client-IP} ".@implode(" ",$ipsToadd);
				unset($ipsToadd);
				$this->hash_rules_acls[$RuleID][]=$ipname;
			}
			$conf[]="";
			$conf[]="#Groups and users";
			
			if(is_array($hash["groups"])){
				while (list ($groupid, $members) = each ($hash["groups"])){
					if(count($members)==0){continue;}
					if($GLOBALS["VERBOSE"]){echo "Starting......: C-ICAP adding group $groupid\n";}
					$this->hash_rules_acls[$RuleID][]="group_$groupid";
					$conf[]="acl GROUP_{$groupid} group group_{$groupid}";
				}
			}else{
				$conf[]="#no groups set";
			}
			
		}

		$conf[]="";
		$GLOBALS["C-ICAP_ACLS"]=$this->hash_rules_acls;
		return implode("\n",$conf);
		
		
	}
	
	
	
	private function BuildUsersAndGroups(){
		if(is_array($this->hash_groups)){
			reset($this->hash_groups);
			while (list ($groupid, $members) = each ($this->hash_groups)){
				$g[]="group_$groupid:".implode(",",$members);
				while (list ($index, $member) = each ($members)){
					$m[]="$member:group_$groupid";
				}
		}}
		@mkdir("/etc/c-icap",null,TRUE);
		@file_put_contents("/etc/c-icap/c-icap-groups.txt",@implode("\n",$g));
		@file_put_contents("/etc/c-icap/c-icap-user-groups.txt",@implode("\n",$m));
		echo "Starting......: C-ICAP ". count($g)." groups and ". count($m)." users\n";
	}
	
	
	private function init_rules(){
		$q=new mysql();
		$sql="SELECT RuleID,RuleName FROM `dansguardian_rules`";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			
		if(strtolower(trim($ligne["RuleName"]))=="default rule"){$ligne["RuleName"]="default";}	
		$ligne["RuleName"]=str_replace("(","",$ligne["RuleName"]);
		$ligne["RuleName"]=str_replace(")","",$ligne["RuleName"]);
		$ligne["RuleName"]=str_replace("[","",$ligne["RuleName"]);
		$ligne["RuleName"]=str_replace("]","",$ligne["RuleName"]);
		$ligne["RuleName"]=str_replace(" ","_",$ligne["RuleName"]);
		$ligne["RuleName"]=str_replace(".","",$ligne["RuleName"]);
		
		if($GLOBALS["VERBOSE"]){echo "init_rules():: RuleID {$ligne["RuleID"]} ". strtolower(trim($ligne["RuleName"]))."\n";}
		    $this->rules[$ligne["RuleID"]]=strtolower(trim($ligne["RuleName"]));
			$array[$ligne["RuleID"]]=strtolower(trim($ligne["RuleName"]));
			$this->InitUsersGroups($ligne["RuleID"]);
			$this->getCategories($ligne["RuleID"]);
		}
		$this->getCategories(1);
		if(!is_array($array)){return array();}
		$GLOBALS["RULES_DG"]=$array;
		return $array;
		
	}
	
	private function InitUsersGroups($RuleID){
		$sql="SELECT RuleID,group_id FROM `dansguardian_groups` WHERE RuleID=$RuleID";
		if($GLOBALS["VERBOSE"]){echo "InitUsersGroups():: $sql\n";}
		
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$groupid=trim($ligne["group_id"]);
			if($GLOBALS["VERBOSE"]){echo "InitUsersGroups():: group $groupid\n";}
			if($groupid==null){continue;}
			$group=new groups($groupid);
			if(is_array($group->members)){
				$this->hash_groups[$groupid]=$group->members;
				$this->groups[$RuleID]["groups"][$groupid]=$group->members;
			}else{
				if($GLOBALS["VERBOSE"]){echo "InitUsersGroups():: group $groupid NO MEMBERS\n";}
			}
		}
		
		$sql="SELECT RuleID,pattern FROM `dansguardian_ipgroups` WHERE RuleID=$RuleID";
		$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["pattern"]==null){continue;}
				$this->groups[$RuleID]["net"][]="{$ligne["pattern"]}";
			
		}		
		
	}
	
	public function GetSquidGuarddbs(){
		
		if(!is_array($this->hash_all_cats)){return null;}
		reset($this->hash_all_cats);
		
		$conf[]="";
		$conf[]="#Preload squidGuard databases#";
		while (list ($category, $path) = each ($this->hash_all_cats)){
			$conf[]="url_check.LoadSquidGuardDB $category $path/";
		}
		
		$conf[]="";
		
		
		if(is_array($this->hash_all_profiles)){
			while (list ($RuleID, $ProfilesArray) = each ($this->hash_all_profiles)){
				while (list ($ProfileName, $ProfilesContent) = each ($ProfilesArray)){
					$conf[]="#Define profiles for rule $RuleID ($ProfileName)";
					while (list ($index, $line) = each ($ProfilesContent)){
						$conf[]="url_check.Profile $ProfileName $line";
					}
				$conf[]="";
				}
			}
		}
		$conf[]="";
		$conf[]="#Maps access groups and IP from profiles";
		$this->Buildacls();
		if(is_array($this->hash_rules_acls)){
			while (list ($RuleID, $groupes) = each ($this->hash_rules_acls)){
				$rulename=$this->rules[$RuleID];
				if($RuleID==1){$rulename="default";}		
				while (list ($index, $groupid) = each ($groupes)){$matched_group[strtoupper($groupid)]=true;}
				while (list ($a, $b) = each ($matched_group)){$c[]=$a;}
				$conf[]="url_check.ProfileAccess $rulename ".implode(" ",$c);
				unset($matched_group);
				unset($c);
			}
			
		}
		
		return implode("\n",$conf);
	}
	
	
private function getCategories($RuleID){
		$users=new usersMenus();
		
			echo "Starting......: C-ICAP Building categories for rule Number $RuleID ({$this->rules[$RuleID]})\n";
			$rulename=$this->rules[$RuleID];

			
			if($RuleID==1){$rulename="default";}

		if(is_file("{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/domains")){
			
			if(!is_file("{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/urls.db")){
				file_put_contents("{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/urls"," ");
				if(is_file($users->SQUIDGUARD_BIN_PATH)){
					echo "Starting......: C-ICAP Building {$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/urls\n";
					echo "Starting......: $users->SQUIDGUARD_BIN_PATH -P$verb -C {$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/urls\n";
					system("$users->SQUIDGUARD_BIN_PATH -P$verb -C {$GLOBALS["guarddb"]}/personal-categories/W-$RuleID/urls");
				}
			}
			

			
			
			
			$this->hash_all_cats["W-$RuleID"]="{$GLOBALS["guarddb"]}/personal-categories/W-$RuleID";
				$this->hash_all_profiles[$RuleID][$rulename][]="pass W-$RuleID";
			}
			
			
		if(is_file("{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}/expressions")){
			$this->hash_all_cats["F-$RuleID"]="{$GLOBALS["guarddb"]}/personal-categories/filesblock-{$this->rules[$RuleID]}";
			$this->hash_all_profiles[$RuleID][$rulename][]="block F-$RuleID";
			}		
		
		
		$q=new mysql();
		$sql="SELECT pattern FROM `dansguardian_files` WHERE filename = 'bannedsitelist' AND RuleID=$RuleID;";
		$results=$q->QUERY_SQL($sql,"artica_backup");
	
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				
				$addplus="plus-{$ligne["pattern"]}";
				$addplus=str_replace("/","-",$addplus);
				$addplus_com=$addplus."-artica";

				if(is_file("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/domains")){
					$this->hash_all_cats[$ligne["pattern"]]="{$GLOBALS["guarddb"]}/{$ligne["pattern"]}";
					if(!is_file("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/urls")){
						file_put_contents("{$GLOBALS["guarddb"]}/{$ligne["pattern"]}/urls","\n");
						system($users->SQUIDGUARD_BIN_PATH." -P$verb -C {$GLOBALS["guarddb"]}/{$ligne["pattern"]}/urls");
					}
					
					$this->hash_all_profiles[$RuleID][$rulename][]="block {$ligne["pattern"]}";
									
				}
				
				
				if(is_file("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/domains")){
					if($GLOBALS["VERBOSE"]){echo "Adding professional category [$addplus] => {$ligne["pattern"]}\n";}
					$this->hash_all_cats[$addplus]="{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}";
					
					if(!is_file("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/urls")){
						file_put_contents("{$GLOBALS["guarddb"]}/web-filter-plus/BL/{$ligne["pattern"]}/urls","\n");
						system($users->SQUIDGUARD_BIN_PATH." -P$verb -C {$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}/urls");
					}
					
					$this->hash_all_profiles[$RuleID][$rulename][]="block {$addplus}";
														
				}
				
				if(is_file("{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}/domains")){
					$this->hash_all_cats[$addplus_com]="{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}";
					
					if(!is_file("{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}/urls")){
						file_put_contents("{$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}/urls","\n");
						system($users->SQUIDGUARD_BIN_PATH." -P$verb -C {$GLOBALS["guarddb"]}/blacklist-artica/{$ligne["pattern"]}/urls");
					}					
					
					$this->hash_all_profiles[$RuleID][$rulename][]="block {$addplus_com}";										
				}					
				
			}
			
			$sql="SELECT category FROM `dansguardian_personal_categories` WHERE category_type = 'enabled' AND RuleID=$RuleID;";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$category=trim($ligne["category"]);
				$categoryPrint=$this->substrCategory($category);
				$this->dump_personals($category);				
				
				if(is_file("{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint/domains")){
					
					if(!is_file("{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint/urls")){
						file_put_contents("{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint/urls","\n");
						system($users->SQUIDGUARD_BIN_PATH." -P$verb -C {$GLOBALS["guarddb"]}/personal-categories/$categoryPrint/urls");
					}					
					
					echo "Starting......: squidGuard Personal category $category ($categoryPrint)\n";
					$this->hash_all_cats["P-$categoryPrint"]="{$GLOBALS["guarddb"]}/personal-categories/$categoryPrint";
					
					$this->hash_all_profiles[$RuleID][$rulename][]="block P-$categoryPrint";
						
				}
				
				
			}
		
	}
	
	private function substrCategory($name){
		$name=str_replace("(","",$name);
		$name=str_replace(")","",$name);
		$name=str_replace("[","",$name);
		$name=str_replace("]","",$name);
		$name=str_replace(" ","_",$name);
		$name=str_replace(".","",$name);
		return $name;
		
	}

	private function dump_personals($category_name){
		if($GLOBALS["CATEGORIES_SAVED"][$category_name]){return;}
		$categoryPrint=$this->substrCategory($category_name);
		$users=new usersMenus();
		
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

	
	
}

?>