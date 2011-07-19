<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["add-path"])){addpath();exit;}
if(isset($_GET["delete-path"])){delpath();exit;}
js();



function js(){
	$page=CurrentPageName();
	$path_decode=base64_decode($_GET["path"]);
	$start="AddPathInCluster();";
	if($_GET["del-path"]<>null){
		$path_decode=base64_decode($_GET["del-path"]);
		$start="DelPathInCluster()";
	}
	
	$html="
	
	var x_AddPathInCluster= function (obj) {
		var response=obj.responseText;
		if(response.length>3){alert(response);}
	   BrowserInfos('$path_decode');
	}		
	
	function AddPathInCluster(){
		var XHR = new XHRConnection();
		XHR.appendData('add-path','{$_GET["path"]}');
		if(document.getElementById('browser-infos')){
			document.getElementById('browser-infos').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		}
		XHR.sendAndLoad('$page', 'GET',x_AddPathInCluster);		
	}
	function DelPathInCluster(){
		var XHR = new XHRConnection();
		XHR.appendData('delete-path','{$_GET["del-path"]}');
		if(document.getElementById('browser-infos')){
			document.getElementById('browser-infos').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		}
		XHR.sendAndLoad('$page', 'GET',x_AddPathInCluster);		
	}	
	
	$start";
	
	echo $html;
	
}

function addpath(){
	$p=base64_decode($_GET["add-path"]);
	$md5=md5($p);
	$sql="INSERT INTO gluster_paths (cluster_path,zmd) VALUES('$p','$md5')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	
	$sock->getFrameWork("cmd.php?gluster-restart=yes");	
	$sock->getFrameWork("cmd.php?gluster-update-clients=yes");	
}
function delpath(){
	$p=base64_decode($_GET["delete-path"]);
	$md5=md5($p);
	$idclustered=idclustered($md5);
	if($idclustered<1){
		echo "$md5 ($idclustered)\nNo such path\n";
		return ;
	}
	$sql="DELETE FROM gluster_paths WHERE zmd='$md5'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?gluster-restart=yes");	
	$sock->getFrameWork("cmd.php?gluster-update-clients=yes");
	
}
function idclustered($md5){
	$sql="SELECT ID FROM gluster_paths WHERE zmd='$md5'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return $ligne["ID"];
	
}
?>