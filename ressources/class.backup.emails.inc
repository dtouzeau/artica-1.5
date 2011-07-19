<?php
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	include_once(dirname(__FILE__).'/class.artica.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');	
	include_once(dirname(__FILE__).'/class.user.inc');	
	
	




class backup_email{
	var $ou;
	var $dn;
	var $BackupEnabled;
	var $ArticaBackupRules=array();
	
	function backup_email($ou=null){
		$this->ou=$ou;
		$this->TestMysqlsettings();
		$this->verifyBranch();
		$this->LoadSettings();
		
		
	}
	
	
	function verifyBranch(){
	$ldap=new clladp();
	$this->dn="cn=backup-config,ou=$this->ou,$ldap->suffix";
	if(!$ldap->ExistsDN($this->dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='OrganizationBackupSettings';
		$upd["cn"]="backup-config";
		$upd["BackupEnabled"][]=0;
		$ldap->ldap_add($this->dn,$upd);
	}
	unset($upd);
	}
	
function LoadSettings(){
		$ldap=new clladp();
		$res=@ldap_read($ldap->ldap_connection,$this->dn,"(objectClass=OrganizationBackupSettings)",array());
		if($res){
			$hash=ldap_get_entries($ldap->ldap_connection,$res);
			$this->BackupEnabled=$hash[0][strtolower('BackupEnabled')][0];
			for($i=0;$i<$hash[0][strtolower('ArticaBackupRules')]["count"];$i++){
				$this->ArticaBackupRules[]=$hash[0][strtolower('ArticaBackupRules')][$i];
			}
			
		}
		
		
	}		
		

	
	function TestMysqlsettings(){
		$s=new mysql();
		$s->BuildTables();
	}

  
    
    function add_rule($line){
    	$tpl=new templates();
    	WriteLogs("$upd[ArticaBackupRules]=$line in $this->dn",__CLASS__.'/'.__FUNCTION__,__FILE__);
    	$upd["ArticaBackupRules"]=$line;
    	$ldap=new clladp();
    	if($ldap->Ldap_add_mod($this->dn,$upd)){
    		echo $tpl->_ENGINE_parse_body('{success}');
    	}else{
    		echo $ldap->ldap_last_error;
    	}
    	
    }
    
    function SaveToLdap(){
    	$upd["BackupEnabled"][0]=$this->BackupEnabled;
    	reset($this->ArticaBackupRules);
    	while (list ($num, $ligne) = each ($this->ArticaBackupRules) ){$upd["ArticaBackupRules"][]=$ligne;}
    	$ldap=new clladp();
    	$tpl=new templates();
    	if($ldap->Ldap_modify($this->dn,$upd)){
    		echo $tpl->_ENGINE_parse_body('{success}');
    	}else{
    		echo $ldap->ldap_last_error;
    	}
    }
    
    
    function MessageNumber(){
    	
    	if($this->ou==null){
    		$sql="SELECT COUNT(storage.MessageID) as tcount
			FROM storage";
    	}else{
    		$sql="SELECT COUNT(storage.MessageID) as tcount
			FROM storage WHERE storage.organization='$this->ou'";
    	}
    	$s=new mysql();
    	$ligne=@mysql_fetch_array($s->QUERY_SQL($sql,"artica_backup"));
    	return $ligne["tcount"];
    	}

}


class backup_query{
	var $AllMails=array();
	var $sql_mymails;
	
	
	function backup_query($uid){
		$user=new user($uid);
		$this->AllMails=$user->aliases;
		$this->AllMails[]=$user->mail;
		
		
		while (list ($num, $array) = each ($this->AllMails) ){
			if($array<>null){
				$recieve[]="OR storage_recipients.recipient='{$array}'";
			}
		}
		if(is_array($recieve)){
			$a=implode(" ",$recieve);
			$this->sql_mymails=substr($a,2,strlen($a));
		}
	}
	
	function sql_last_backuped_mails($start,$end){
		$sql="SELECT `storage`.MessageID,`storage`.zDate,`storage`.subject ,`storage`.mailfrom
		FROM `storage`,storage_recipients 
		WHERE `storage`.MessageID=storage_recipients.MessageID AND ($this->sql_mymails) 
		ORDER BY `storage`.zDate DESC 
		LIMIT $start,$end";
		writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__);
		$s=new mysql();
		return $s->QUERY_SQL($sql,"artica_backup");
		
	}
		
	
	
	
	
	
}


?>