<?php
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');

	if(!VerifyEmailingRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	
class emailings{
	var $ID;
	var $mysql_error;
	var $tablename;
	var $array_options=array();
	var $ou;
	var $error=false;
	function emailings($ID=null){
		
		if($ID>0){$this->ID=$ID;$this->error=$this->loadtable();}
		
	}
	
	private function loadtable(){
		$sql="SELECT * FROM emailing_db_paths WHERE ID=$this->ID";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		if(!$q->ok){$this->mysql_error="$q->mysql_error\n";return true;}	
		$this->tablename="emailing_".$ligne["databasename"];
		$this->ou=$ligne["ou"];
		$this->array_options=unserialize(base64_decode($ligne["options"]));
		return false;
	}
	
	public function SET_OPTIONS($array){
		$sql="UPDATE emailing_db_paths SET `options`='".base64_encode(serialize($array))."' WHERE ID=$this->ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){$this->mysql_error="$q->mysql_error\n";return false;}	
		return true;
	}
	
	
	
	
	
	
}


function emailing_get_database_name($db_id){
	$sql="SELECT * FROM emailing_db_paths WHERE ID=$db_id";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	return $ligne["databasename"];
}
function emailing_get_template_name($template_id){
	$sql="SELECT template_name FROM emailing_templates WHERE ID=$template_id";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	return $ligne["template_name"];
}
function emailing_get_template_parameters($template_id){
	$sql="SELECT * FROM emailing_templates WHERE ID=$template_id";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$parms=$ligne;
	
	$sql="SELECT ID,filename FROM emailing_tbl_files WHERE template_id='$template_id' ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$parms["ATTACHS"][$ligne["filename"]]=$ligne["ID"];
	}
	
	return $parms;
	
}


function emailing_builder_get_queue_number($database_id){
	$dbname=emailing_get_database_name($database_id);
	$dbname=str_replace(",","",$dbname);
	$q=new mysql();
	$sql="SELECT COUNT(ID) as tcount FROM emailing_$dbname";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){
		writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);		
	}
	return $ligne["tcount"];	
}
function emailing_builder_get_users_number($database_id){
	return emailing_builder_get_queue_number($database_id);
}


function emailing_save_attachment($ID,$path){
	$sql="SELECT bin_data FROM emailing_tbl_files WHERE ID=$ID";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	@file_put_contents($path,$ligne["bin_data"]);
}

	
	
function VerifyEmailingRights(){
	if(posix_getuid()==0){return true;}
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(!isset($_GET["ou"])){return false;}
	$users=new usersMenus();
	if($users->AseMailCampaignsAdmin){return true;}
	if($users->AsPostfixAdministrator){return true;}
	if($users->AsOrgAdmin){return true;}
	if($users->AsMessagingOrg){return true;}
	
}
?>