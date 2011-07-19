<?php


function GET_INFO($key){
	if(!file_exists("/etc/artica-postfix/settings/Daemons/$key")){return null;}
	$data=file_get_contents("/etc/artica-postfix/settings/Daemons/$key");
	$data=trim($data);
	return $data;
	}

class mysql_cron{
	var $database;
	var $bd;
	
	
function mysql_cron(){
	$this->bd=$this->mysqlOpen();
}
	
function mysqlOpen(){
		include(dirname(__FILE__) . '/settings.inc');
		$mysql_server=$_GLOBAL["mysql_server"];
		$mysql_admin=$_GLOBAL["mysql_admin"];
		$mysql_password=$_GLOBAL["mysql_password"];
		$mysql_port=$_GLOBAL["mysql_port"];	
		$bd=@mysql_connect("$mysql_server:$mysql_port",$mysql_admin,$mysql_password);
		if(!$bd){
    		$des=mysql_error();
    		die("Unable to connect to mysql server $des");
		}
		
		return $bd;	
	
	
}	

function QUERY_SQL($sql){
		$database=$this->database;
		$ok=@mysql_select_db($database,$this->bd);
    	if (!$ok){
    		$errnum=mysql_error();
    		$des=mysql_error();
    		echo "Error Myslq Number ($errnum) ($des) ".__FUNCTION__.'/'.__FILE__."\n";
    		return null;
    
    	}	

    $results=mysql_query($sql,$this->bd);
	
		if(mysql_error()){
				$errnum=mysql_error();
				$des=mysql_error();
				echo "Error Myslq Number ($errnum) ($des) ".__FUNCTION__.'/'.__FILE__."\n";
				
		}	
	return $results;
}	
}