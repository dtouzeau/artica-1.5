<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.nfs.inc");



if(isset($_GET["TESTCONNECTION"])){TESTCONNECTION();exit;}
if(isset($_GET["LINKING"])){LINKING();exit;}




function TESTCONNECTION(){
	
	echo "[MASTER]\n";
	
	$users=new usersMenus();
	if(!$users->NFS_SERVER_INSTALLED){
		echo "ERROR=No filesystem installed\n";
		return false;
	}
	
	$nfs=new nfs();
	
	if(trim($nfs->SanClusterBasePath)==null){
		echo "ERROR=No STORAGE configured\n";
		return false;
	}
	
	echo "ERROR=SUCCESS\n";
	echo "\n\n";
}

function LINKING(){
	
	echo "[MASTER]\n";
	$ip=$_SERVER['REMOTE_ADDR'];
	
	$nfs=new nfs();
	$nfs->NFS_CLUSTER_ADD_CLIENT($ip);
	$nfs->SaveToServer();
	
	echo "directory=$nfs->SanClusterBasePath\n";
	
	
	
	
	
}

?>