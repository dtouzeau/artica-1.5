<?php
include_once(dirname(__FILE__).'/class.user.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');


class GroupOffice{
	var $database;
	var $uid;
	var $ClassUser;
	var $ClassSQL;
	
	function GroupOffice($database){
		$this->database=$database;
		
	}
	
	public function UpdateUser($uid){
		writelogs("Loading $uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$ct=new user($uid);
		$this->ClassUser=$ct;
		if(!$this->UserCheck()){
			$this->UserAdd();
		}else{
			$this->UserUpd();
		}
		
		$this->UserCheckGroup();
		
	}
	
	private function UserCheck(){
		if($this->ClassUser->uidNumber==null){
			writelogs("No uidNumber for {$this->ClassUser->uid}" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$sql="SELECT * FROM go_users WHERE id={$this->ClassUser->uidNumber}";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		
		writelogs("{$this->ClassUser->uid}={$ligne["password"]}" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($ligne["password"]==null){return false;}
			while (list ($num, $val) = each ($ligne) ){
					if(is_numeric($num)){continue;}
					$this->ClassSQL[$num]=$val;
			}		
		
		return true;
	}
	
	private function UserAdd(){
		$array=$this->ArrayMatchTOSql();

		while (list ($num, $ligne) = each ($array) ){
				$fields[]="`$num`";
				$datas[]="'$ligne'";
			}
		$sql="INSERT INTO go_users (".implode(",",$fields).") VALUES(".implode(",",$datas).")";
		$q=new mysql();
		writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){
				writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
				return;			
			}		
		$this->go_db_sequence_update('go_users',$this->ClassUser->uidNumber+1);
	}
	
	private function UserUpd(){
		$array=$this->ArrayMatchTOSql();
		while (list ($num, $ligne) = each ($array) ){
				$fields[]="`$num`='$ligne'";
				
			}
			
		$sql="UPDATE go_users SET ".implode(",",$fields)." WHERE id={$this->ClassUser->uidNumber}";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){
				writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
			}			
		
	}
	
	private function UserCheckGroup(){
		$q=new mysql();
		$sql="INSERT INTO go_users_groups (user_id,group_id) VALUES({$this->ClassUser->uidNumber},2)";
		$q->QUERY_SQL($sql,$this->database);
		$sql="INSERT INTO go_users_groups (user_id,group_id) VALUES({$this->ClassUser->uidNumber},3)";
		$q->QUERY_SQL($sql,$this->database);

		$sql="INSERT INTO `go_state` (`user_id`, `name`, `value`) VALUES
		({$this->ClassUser->uidNumber}, 'summary-calendar-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Asummary-calendar-name-heading%25255Ewidth%25253Dn%2525253A694%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A140%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC'),
		({$this->ClassUser->uidNumber}, 'list-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A51%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC'),
		({$this->ClassUser->uidNumber}, 'open-modules', 's%3A%5B%22summary%22%2C%22email%22%2C%22calendar%22%2C%22tasks%22%2C%22addressbook%22%2C%22files%22%2C%22notes%22%2C%22tools%22%2C%22users%22%5D'),
		({$this->ClassUser->uidNumber}, 'summary-active-portlets', 's%3A%5B%7B%22id%22%3A%22portlet-announcements%22%2C%22col%22%3A0%7D%2C%7B%22id%22%3A%22portlet-calendar%22%2C%22col%22%3A0%7D%2C%7B%22id%22%3A%22portlet-note%22%2C%22col%22%3A1%7D%5D');
		";$q->QUERY_SQL($sql,$this->database);
		
		
		
			$id=$this->GetAclItemID("calendar");
			$this->InsertAclItemUserID($id);
			$next=$this->go_db_sequence("go_acl_items")+1;
			$sql="INSERT INTO go_acl_items (id,user_id,description) VALUES($next,{$this->ClassUser->uidNumber},'calendar {$this->ClassUser->DisplayName})'";
			$q->QUERY_SQL($sql,$this->database);
			$this->go_db_sequence_update("go_acl_items",$next);
		
			
			
		$sql="SELECT id FROM cal_calendars WHERE user_id='{$this->ClassUser->uidNumber}'";
		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		if($ligne["id"]<1){
			$next_calendar=$this->go_db_sequence("cal_calendars")+1;
			$sql="INSERT INTO `cal_calendars` (id,`group_id`, `user_id`, `acl_id`, `name`, `start_hour`, `end_hour`, `background`, `time_interval`, `public`, `shared_acl`, `show_bdays`) VALUES
			($next_calendar, 1, {$this->ClassUser->uidNumber}, $next, '{$this->ClassUser->DisplayName}', 0, 0, NULL, 1800, '0', 0, 0);";
			$this->go_db_sequence_update("cal_calendars",$next_calendar);	
		}else{
			$sql="UPDATE cal_calendars SET acl_id=$next WHERE id={$ligne["id"]}";
		}
		$q->QUERY_SQL($sql,$this->database);
		
		$id=$this->GetAclItemID("email");
		$this->InsertAclItemUserID($id);
		$next=$this->go_db_sequence("go_acl_items");
		$sql="INSERT INTO go_acl_items (id,user_id,description) VALUES($next,{$this->ClassUser->uidNumber},'calendar {$this->ClassUser->DisplayName})";
		$q->QUERY_SQL($sql,$this->database);
		$this->go_db_sequence_update("go_acl_items",$next);		
		
		
	}
	
	
	
	private function GetAclItemID($item="calendar"){
		$sql="SELECT acl_id FROM go_modules WHERE id='$item'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));		
		return $ligne["acl_id"];
	}
	
	private function InsertAclItemUserID($item_id){
		$sql="INSERT INTO go_acl (acl_id,user_id,group_id,level) VALUES($item_id,{$this->ClassUser->uidNumber},0,4)";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
	}

	
	
	private function fs_folder_add($item,$acl_id,$parent_id){
		$next=$this->go_db_sequence("fs_folders")+1;
		$array["user_id"]=$this->ClassUser->uidNumber;
		$array["parent_id"]=$parent_id;
		$array["name"]=$item;
		$array["visible"]=1;
		$array["acl_id"]=$acl_id;
		$array["ctime"]=time();
		$array["mtime"]=time();
		$array["readonly"]=1;
			while (list ($num, $ligne) = each ($array) ){
				$fields[]="`$num`";
				$datas[]="'$ligne'";
			}
		
		$sql="INSERT INTO fs_folders (".implode(",",$fields).") VALUES(".implode(",",$datas).")";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){
			writelogs("ERROR ".$q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return ;		
		}
		$this->go_db_sequence_update("fs_folders",$next);
		
	}
	
	
	private function AddAclItem($item){
		$next=$this->go_db_sequence("go_acl_items")+1;
		writelogs("add item: '$item','{$this->ClassUser->uidNumber}'",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sql="INSERT INTO go_acl_items (id,description,user_id) VALUES($next,'$item','{$this->ClassUser->uidNumber}')";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){
			writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return ;		
		}
		$this->go_db_sequence_update("go_acl_items",$next);
		
	}
	
	private function AddStandardsAcls(){
		$array=array(1,2,3,5,6,10,14,16,17,19);
		while (list ($num, $ligne) = each ($array) ){
			$sql="INSERT INTO go_acl (acl_id,user_id ,group_id,level) VALUES($ligne,'{$this->ClassUser->uidNumber}',0,1)";
			$q=new mysql();
			$q->QUERY_SQL($sql,$this->database);			
		}
	}
	
	private function go_db_sequence($seq_name){
		$sql="SELECT nextid FROM go_db_sequence WHERE seq_name='$seq_name'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));	
		return $ligne["nextid"];
	}
	
	private function go_db_sequence_update($seq_name,$next){
		$sql="UPDATE go_db_sequence SET nextid=$next WHERE seq_name='$seq_name'";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);	
		
	}	
	
	
	private function ArrayMatchTOSql(){
		
		$this->ClassSQL["id"]=$this->ClassUser->uidNumber;
		$this->ClassSQL["username"]=$this->ClassUser->uid;
		$this->ClassSQL["password"]=md5($this->ClassUser->password);
		$this->ClassSQL["email"]=$this->ClassUser->mail;
		$this->ClassSQL["enabled"]=1;
		if($this->ClassSQL["last_name"]==null){$this->ClassSQL["last_name"]=$this->ClassUser->sn;}
		if($this->ClassSQL["first_name"]==null){$this->ClassSQL["first_name"]=$this->ClassUser->givenName;}
		if($this->ClassSQL["title"]==null){$this->ClassSQL["title"]=$this->ClassUser->title;;}
		if($this->ClassSQL["work_phone"]==null){$this->ClassSQL["work_phone"]=$this->ClassUser->telephoneNumber;;}
		if($this->ClassSQL["cellular"]==null){$this->ClassSQL["cellular"]=$this->ClassUser->mobile;;}
		if($this->ClassSQL["address"]==null){$this->ClassSQL["address"]=$this->ClassUser->postalAddress;}
		if($this->ClassSQL["zip"]==null){$this->ClassSQL["zip"]=$this->ClassUser->postalCode;}
		if($this->ClassSQL["city"]==null){$this->ClassSQL["city"]=$this->ClassUser->town;}
		return $this->ClassSQL;
		}
}

?>