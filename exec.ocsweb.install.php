<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');


if($argv[1]=="--install-client"){
	InstallClient($argv[2]);
	die();
}


MysqlCheck();


function MysqlCheck(){


$db_file = "/usr/share/ocsinventory-reports/ocsreports/files/ocsbase.sql";
if(!is_file($db_file)){die();}
if(CheckTables()){die();}

if($dbf_handle = @fopen($db_file, "r")) {
	$sql_query = fread($dbf_handle, filesize($db_file));
	fclose($dbf_handle);
	
}

$q=new mysql();
if(!$q->DATABASE_EXISTS("ocsweb")){$q->CREATE_DATABASE("ocsweb");}

$array_commands=explode(";", "$sql_query");
while (list ($num, $sql) = each ($array_commands) ){
	if(trim($sql)==null){continue;}
	
	$q->QUERY_SQL($sql,"ocsweb");
}

}


function CheckTables(){
	$q=new mysql();
	$tables=array("accesslog","accountinfo","bios","blacklist_macaddresses","blacklist_serials","config","conntrack","controllers","deleted_equiv","deploy","devices","devicetype","dico_ignored","dico_soft","download_affect_rules","download_available","download_enable","download_history","download_servers","drives","engine_mutex","engine_persistent","files","groups","groups_cache","hardware","hardware_osname_cache","inputs","javainfo","locks","memories","modems","monitors","netmap","networks","network_devices","operators","ports","printers","prolog_conntrack","regconfig","registry","registry_name_cache","registry_regvalue_cache","slots","softwares","softwares_name_cache","sounds","storages","subnet","tags","videos","virtualmachines");
	while (list ($num, $table) = each ($tables) ){
		if(!$q->TABLE_EXISTS($table,"ocsweb")){
			return false;
		}
	}
	
	return true;
}


function InstallClient($filepath){
	
	if(!is_file($filepath)){
		echo "Unable to stat $filename\n";
		return null;
	}
	
	shell_exec("/bin/mv $filepath /var/lib/ocsinventory-reports/");
	return;
	
	
	
	$filename=basename($filepath);
	$size=filesize($filepath);
	$txtDescription="OCS Client for Microsoft Windows";
	$type="	application/zip";
	$q=new mysql();
	
	
	$sql="SELECT id_files FROM files_storage WHERE filename='$filename' AND OCS_PACKAGE=1";
	echo "Find if package exists for $filename\n";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(intval($ligne["id_files"])>0){
		echo "DELETE old mysql entry for $filename\n";
		$sql="DELETE FROM files_storage WHERE id_files={$ligne["id_files"]}";
		$q->QUERY_SQL($sql,'artica_backup');
	}
	
	
	$ExecuteAfter=addslashes('"C:\Program Files\OCS Inventory Agent\Ocs_contact.exe" /S');
	$commandline="OCS";
	$MinutesToWait=5;
	$filename=str_replace(" ","-",$filename);
	
	

    $data = addslashes(fread(fopen($filepath, "r"), filesize($filepath)));
    if($data==null){
    	echo "Failed to get datas from $filepath\n";
    	return null;
    }
    $strDescription = addslashes(nl2br($txtDescription));
    $sql = "INSERT INTO files_storage ";
    $sql .= "(description, bin_data, filename, filesize, filetype,commandline,ExecuteAfter,MinutesToWait,OCS_PACKAGE) ";
    $sql .= "VALUES ('$strDescription', '$data', ";
    $sql .= "'$filename', '$size', '$type','$commandline','$ExecuteAfter','$MinutesToWait','1')";
    echo "INSERT $filename into mysql \n";
    $q->QUERY_SQL($sql,"artica_backup");
    if($q->ok){
    	echo "success $filename\n";

    }else{
    	echo "failed $filename with error $q->mysql_error\n";
    }
    
    @unlink($filepath);
}



?>