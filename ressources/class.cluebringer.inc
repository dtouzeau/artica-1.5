<?php

class cluebringer{
	
	
	function cluebringer(){
		
	}

	function local_domain_add($domain){
		
		if($this->policy_groups_id($domain)>0){
			$this->policy_group_members_sync($domain);
			return null;
		}
		$q=new mysql();
		$sql="INSERT INTO policy_groups (Name) VALUES('$domain')";
		$q->QUERY_SQL($sql,"policyd");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
		$this->policy_group_members_sync($domain);
		
	}
	
	function local_domain_delete($domain){
		if($this->policy_groups_id($domain)<1){return null;}
		$q=new mysql();
		$sql="DELETE FROM policy_groups WHERE Name='$domain'";
		$q->QUERY_SQL($sql,"policyd");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
		$sql="DELETE FROM policy_group_members WHERE Name='@$domain'";
		
	}
	
	function policy_groups_id($domain){
		if($GLOBALS["policy_groups_id"][$domain]>0){return $GLOBALS["policy_groups_id"][$domain];}
		$q=new mysql();
		$sql="SELECT ID FROM policy_groups WHERE Name='$domain'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'policyd'));
		$GLOBALS["policy_groups_id"][$domain]=$ligne["ID"];
		return $GLOBALS["policy_groups_id"][$domain];
		
	}
	
	
	function policy_group_members_sync($domain){
		$policy_groups_id=$this->policy_groups_id($domain);
		$domain="@$domain";
		$sql="SELECT ID FROM policy_group_members WHERE PolicyGroupID=$policy_groups_id AND Member='$domain'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'policyd'));
		if($ligne["ID"]>0){return null;}
		$sql="INSERT INTO policy_group_members (PolicyGroupID,Member) VALUES($policy_groups_id,'$domain')";
		$q->QUERY_SQL($sql,"policyd");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		
		
	}
	
	
}