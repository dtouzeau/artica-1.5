<?php
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}


$hash=GetHomes();
if(count($hash)>0);
MountHomes($hash);
die();

function GetHomes(){
	
	$ldap=new clladp();
	$suffix="dc=organizations,$ldap->suffix";
	$attr=array("uid","homeDirectory","homeDirectoryBinded");
	$pattern="(&(objectclass=UserArticaClass)(homeDirectoryBinded=*))";
	$sr =@ldap_search($ldap->ldap_connection,$suffix,$pattern,$attr);
	if(!$sr){return null;}
	$hash=@ldap_get_entries($ldap->ldap_connection,$sr);	
	
	
	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		if($uid==null){continue;}
		if(substr($uid,strlen($uid)-1,1)=="$"){continue;}
		if($hash[$i]["homedirectory"][0]==null){continue;}
		
		for($t=0;$t<$hash[$i]["homedirectorybinded"]["count"];$t++){
			$homeDirectoryBinded[]=$hash[$i]["homedirectorybinded"][$t];
		}
		
		$array[$hash[$i]["uid"][0]]=array("homedirectory"=>$hash[$i]["homedirectory"][0],"homeDirectoryBinded"=>$homeDirectoryBinded);
		
	}
	
	if(!is_array($array)){return array();}
	return $array;
	}
	
	
function MountHomes($array){
	$sock=new sockets();
	if(!is_array($array)){return null;}
	while (list ($num, $val) = each ($array) ){
		if($num==null){continue;}
		$homedirectory=$val["homedirectory"];
		while (list ($index, $dir) = each ($val["homeDirectoryBinded"]) ){
			$datas=$sock->getfile("homeDirectoryBinded:$homedirectory;$dir");	
		}
	}
	
	
}
?>