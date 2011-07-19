<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.artica-smtp-sync.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/ressources/class.httpd.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=="--sync"){
	SyncServers();
	die();
}

if(isset($_POST["credentials"])){receive_synchro();}



function SyncServers(){
	$sync=new articaSMTPSync();
	if(!is_array($sync->serverList)){writelogs("No servers aborting",__FUNCTION__,__FILE__,__LINE__);return;}
	while (list ($server, $array) = each ($sync->serverList) ){
		$port=$array["PORT"];
		$user=$array["user"];
		$password=$array["password"];
		Connect($server,$port,$user,$password);
	}
}


function Connect($server,$port,$user,$pass){
	writelogs("synchronize $server:$port",__FUNCTION__,__FILE__,__LINE__);
	$sync=new articaSMTPSync();
	$ldap=new clladp();
	$http=new httpd();
	$array=$sync->GetUsers();
	$field=base64_encode(serialize($array));
	$cred["user"]=$user;
	$cred["pass"]=$pass;
	$curl=new ccurl("https://$server:$port/exec.smtp.export.users.php");
	$curl->parms["credentials"]=base64_encode(serialize($cred));
	$curl->parms["users"]=$field;
	$curl->parms["local_port"]=$http->https_port;
	$curl->parms["mycred"]=base64_encode(serialize(array($ldap->ldap_admin,$ldap->ldap_password)));
	if(!$curl->get()){
		writelogs("synchronize $server:$port failed",__FUNCTION__,__FILE__,__LINE__);
	}
	if(preg_match("#<datas>(.+?)</datas>#is",$curl->data,$re)){
		$array=unserialize(base64_decode($re[1]));
		$sync->import($array,"$server:$port");
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
	}
	
	
}

function receive_synchro(){
	$cred=unserialize(base64_decode($_POST["credentials"]));
	$ldap=new clladp();
	$check=false;
	if($cred["user"]==$ldap->ldap_admin){
		if($cred["pass"]==$ldap->ldap_password){
			$check=true;
		}
	}
	if(!$check){die();}
	$users=unserialize(base64_decode($_POST["users"]));
	if(!is_array($users)){return null;}
	$sync=new articaSMTPSync();
	$array=$sync->GetUsers();
	$servername=$_SERVER['REMOTE_ADDR'];
	$port=$_POST["local_port"];
	$itscred=unserialize(base64_decode($_POST["mycred"]));
	$sync->Add($servername,$port,$itscred[0],$itscred[1]);
	$sync->import($users,"$servername:$port");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");	
	echo "<datas>". base64_encode(serialize($array))."</datas>";
	}







?>