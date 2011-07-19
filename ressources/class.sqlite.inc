<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
class lib_sqlite{
	var $db_path="";
	var $ok=true;
	var $sql_error;
	
	function lib_sqlite(){
		$this->db_path=dirname(__FILE__)."/databases/SQLite";
		
	}
	
	function CheckDatabases(){
	$this->IF_DATABASE_EXISTS("identities");
	$this->IF_DATABASE_EXISTS("artica_events");
	
	if(!$this->IF_TABLE_EXISTS("events","artica_events")){
		$sql="CREATE TABLE \"events\" (
			id INTEGER PRIMARY KEY,
		  	zDate datetime NOT NULL,
  		  	event_id INTEGER NOT NULL,
 			hostname TEXT NOT NULL,
  			process TEXT NOT NULL,
  			\"text\" LONGTEXT NOT NULL,
  			context TEXT NOT NULL DEFAULT 'unknown',
  			sended INTEGER NOTLONGTEXT NULL DEFAULT '0',
  			content text NOT NULL,
  			attached_files text NOT NULL,
  			recipient TEXT NOT NULL)";
			$this->QUERY_SQL($sql,"artica_events");
			$sql="CREATE INDEX \"index_events\" on events (zDate ASC, id ASC, sended ASC)";
			$this->QUERY_SQL($sql,"artica_events");
		}

	
	
	if(!$this->IF_TABLE_EXISTS("members","identities")){
				$sql="CREATE TABLE \"members\" (
					\"ou\" TEXT,
					\"uid\" TEXT,
					\"email\" TEXT,
					\"DisplayName\" TEXT
				)";
				writelogs("create table members",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				$this->QUERY_SQL($sql,"identities");
				$sql="CREATE INDEX \"index\" on members (ou ASC, uid ASC, email ASC)";
				$this->QUERY_SQL($sql,"identities");
				
			}
			
		
		
		if(!$this->IF_TABLE_EXISTS("groups","identities")){
				$sql="CREATE TABLE \"groups\" (
					\"ou\" TEXT,
					\"guid\" INTEGER,
					\"Name\" TEXT,
					\"ArticaGroupPrivileges\" LONGTEXT
				)";
				writelogs("create table groups",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				$this->QUERY_SQL($sql,"identities");
				$sql="CREATE INDEX \"index_groups\" on groups (ou ASC, guid ASC, Name ASC)";
				$this->QUERY_SQL($sql,"identities");
			}
			
		if(!$this->IF_TABLE_EXISTS("members_link","identities")){
				$sql="CREATE TABLE \"members_link\" (
					\"guid\" INTEGER,
					\"uid\" TEXT
					)";
				writelogs("create table members_link",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				$this->QUERY_SQL($sql,"identities");
				$sql="CREATE INDEX \"index_members_link\" on members_link (guid ASC, uid ASC)";
				$this->QUERY_SQL($sql,"identities");
		}

		if(!$this->IF_TABLE_EXISTS("organizations","identities")){
				$sql="CREATE TABLE \"organizations\" (\"name\" TEXT)";
				writelogs("create table organizations",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				$this->QUERY_SQL($sql,"identities");
				$sql="CREATE UNIQUE INDEX \"name\" on organizations (ou ASC)";
				$this->QUERY_SQL($sql,"identities");
			}

		if(!$this->IF_TABLE_EXISTS("relay_domains","identities")){
				$sql="CREATE TABLE \"relay_domains\" (
					\"ou\" TEXT,
					\"domain\" TEXT,
					\"transport\" TEXT
					)";
				writelogs("create table relay_domains",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				$this->QUERY_SQL($sql,"identities");
				$sql="CREATE UNIQUE INDEX \"relay_domains_key\" on relay_domains (ou ASC)";
				$this->QUERY_SQL($sql,"identities");
				$sql="CREATE UNIQUE INDEX \"relay_domains_unique\" on relay_domains (domain ASC)";
				$this->QUERY_SQL($sql,"identities");
			}			
	
	}
	
	
	
	private function IF_DATABASE_EXISTS($dbname){
		if(!is_dir($this->db_path)){
			writelogs("Create path $this->db_path",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			@mkdir($this->db_path,0755,true);
		
		}
		if(!is_file("$this->db_path/$dbname")){
			$db = @sqlite_open("$this->db_path/$dbname", 0666, $sqliteerror);
			if(!$db){
				writelogs("Unable to create Database $dbname $sqliteerror",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				return false;
			}else{
				sqlite_close($db);
				return true;
			}
		}
		
	}
	
	
	function AddOrganization($ou){
		$this->CheckDatabases();
		$sql="INSERT INTO organizations (name) VALUES('$ou');";
		$this->QUERY_SQL($sql,"identities");
		if(!$this->ok){echo $this->sql_error;}
		
	}
	
	
	function AddRelayDomain($ou,$domain_name,$relayIP,$relayPort,$mx){
		$this->CheckDatabases();
		if($mx=="no"){$relayIP="[$relayIP]";}
		$transport="relay:$relayIP:$relayPort";
		$sql="INSERT INTO relay_domains (ou,domain,transport) VALUES('$ou','$domain_name','$transport')";
		$this->QUERY_SQL($sql,"identities");
		if(!$this->ok){echo $this->sql_error;}		
	}
	
	function hash_get_ou($has_key=false){
		$sql="SELECT name FROM organizations ORDER BY name";
		$results=$this->QUERY_SQL($sql,"identities");
		while ($ligne=sqlite_fetch_array($results, SQLITE_ASSOC)) { if(!$has_key){$gpid[]=$ligne["name"];}else{$gpid[$ligne["name"]]=$ligne["name"];}}		
		return $gpid;
	}
	
	
	function _Get_privileges_userid($uid){
		$this->CheckDatabases();
		$sql="SELECT guid FROM members_link WHERE uid='$uid'";
		$results=$this->QUERY_SQL($sql,"identities");
		while ($ligne=sqlite_fetch_array($results, SQLITE_ASSOC)) { 
			$gpid[]=$ligne["guid"];
			
		}
		
		if(!is_array($gpid)){return null;}
		$ldap=new clladp();
		while (list ($num, $groupid) = each ($gpid) ){
			$sql="SELECT ArticaGroupPrivileges FROM groups WHERE guid='$groupid'";
			$ligne=@sqlite_fetch_array($this->QUERY_SQL($sql,'identities'));
			$privs=$ldap->_ParsePrivieleges($ligne["ArticaGroupPrivileges"]);
			if(is_array($privs)){
				while (list ($num, $ligne) = each ($privs) ){
					if($ligne=="yes"){$org_priv[$num]="yes";}
					if($ligne=="1"){$org_priv[$num]="1";}
					if($num=="ForceLanguageUsers"){$org_priv[$num]=$ligne;}							
				}
				unset($privs);
			}			
		}
		
		if(is_array($org_priv)){
			while (list ($num, $ligne) = each ($org_priv) ){
				$conf=$conf."[$num]=\"$ligne\"\n";
			}
		}
		return $conf;		
		
	}
	
	
	private function IF_TABLE_EXISTS($tablename,$database){
		
		if(!isset($GLOBALS["TABLES_$database"])){
			$sql="Select name,sql FROM sqlite_master where type='table'";
			$results=$this->QUERY_SQL($sql,$database);
			while ($ligne=sqlite_fetch_array($results, SQLITE_ASSOC)) {
				writelogs("table:{$ligne["name"]} ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$GLOBALS["TABLES_$database"][$ligne["name"]]=true;
			}
			
		}
		
		return $GLOBALS["TABLES_$database"][$tablename];
		
	}
	
	
	function QUERY_SQL($sql,$database){
		$sql=str_replace("`","\"",$sql);
		$this->ok=true;
		$this->sql_error=null;
		$db = @sqlite_open("$this->db_path/$database", 0666, $sqliteerror);
		if(!$db){
			$this->ok=false;
			$this->sql_error=$sqliteerror;
			writelogs("sqlite_open:: $sqliteerror ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs("sqlite_open:: $sql ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		
		$result =sqlite_query($db,$sql,SQLITE_ASSOC,$sqliteerror);
		if(trim($sqliteerror)<>null){
			$this->ok=false;
			$this->sql_error=$sqliteerror;
			writelogs("sqlite_query:: $sqliteerror ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs("sqlite_query:: $sql ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			@sqlite_close($db);
			
		}
		return $result;	
		@sqlite_close($db);
		
	}
	
	
	
}