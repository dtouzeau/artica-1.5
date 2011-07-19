<?php
$GLOBALS["BYPASS"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.gluster.samba.php');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

writelogs("Executed","MAIN",__FILE__,__LINE__);
if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
}

if($GLOBALS["VERBOSE"]){echo "Debug mode TRUE for {$argv[1]}\n";}


// récupère les répertoires à clusteriser.
if($argv[1]=='--sources'){MASTERS_GET_SOURCES();die();}

// monte les répertoires vers les serveurs clusters.
if($argv[1]=='--mount'){CLIENT_MOUNT_FOLDERS();die();}

// Démonte tous les répertoires, synchronise depuis les maîtres et remonte les répertoires.
if($argv[1]=='--remount'){CLIENT_REMOUNT_FOLDERS();die();}




if($argv[1]=='--master'){NotifyMaster();exit;}

// écrit la config du serveur.
if($argv[1]=='--conf'){BuildLocalConf();exit;}

// notifie les nouveaux clients
if($argv[1]=='--notify-all-clients'){NotifyAllClients();die();}

// notifie les clients pour qu'ils soient supprimés.
if($argv[1]=='--delete-clients'){DeleteAllClients();die();}



//notifie les anciens clients du changement, les force à démonter et remonter...
if($argv[1]=='--update-all-clients'){UpdateAllClients();die();}

//notifie les maitres du statut des montages 
if($argv[1]=='--notify-server'){NotifyAllServersStatus();die();}

if(isset($_POST["notify"])){ReceiveParams();exit;}
if(isset($_POST["events"])){ReceiveClientEvents();exit;}
if(isset($_POST["update-mounts"])){ReceiveServerUpdateMount();exit;}
if(isset($_POST["delete-server"])){ReceiveServerDelete();exit;}




if(isset($_POST["bricks"])){export_bricks();exit;}
if(isset($_POST["NTFY_STATUS"])){server_receive_status();exit;}
if(isset($_POST["CLIENT_NTFY_SRV_INFO"])){server_request_client_info();die();}


if(isset($_POST)){
	while (list ($num, $ligne) = each ($_POST) ){
		writelogs("unable to understand $num= $ligne",__FUNCTION__,__FILE__,__LINE__);
	}
	die();
}
if(isset($_GET)){
	while (list ($num, $ligne) = each ($_GET) ){
		writelogs("unable to understand $num= $ligne",__FUNCTION__,__FILE__,__LINE__);
	}
	die();
}


writelogs("no posts notify clients by default...",__FUNCTION__,__FILE__,__LINE__);
NotifyClients();
die();


function NotifyAllClients(){
	$sql="SELECT * FROM glusters_clients WHERE client_notified=0 ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		NotifyClient("{$ligne["client_ip"]}:{$ligne["client_port"]}",$ligne["ID"]);
	}
}


function DeleteAllClients(){
	$q=new mysql();
	$sql="DELETE FROM glusters_clients WHERE client_notified=0 AND NotifToDelete=1";
	$q->QUERY_SQL($sql,"artica_backup");
	
	
	$sql="SELECT * FROM glusters_clients WHERE client_notified=1 AND NotifToDelete=1";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		NotifyDeleteClient("{$ligne["client_ip"]}:{$ligne["client_port"]}",$ligne["ID"]);
	}	
	
}

function UpdateAllClients(){
	$sql="SELECT * FROM glusters_clients WHERE client_notified=1 ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		NotifyUpdateClient("{$ligne["client_ip"]}:{$ligne["client_port"]}",$ligne["ID"]);
	}	
	
}


function NotifyEvents($text,$ID){
	$q=new mysql();
	$sql="SELECT parameters FROM glusters_clients WHERE ID=$ID";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$array=unserialize(base64_decode($ligne["parameters"]));
	if(count($array["LOGS"])>300){unset($array["LOGS"]);}
	$array["LOGS"][time()]=date("m-d H:i:s")." $text";
	$sql="UPDATE glusters_clients SET parameters='".base64_encode(serialize($array))."' WHERE ID=$ID";
	$q->QUERY_SQL($sql,"artica_backup");
}

function NotifyUpdateClient($server,$ID){
	$curl=new ccurl("https://$server/exec.gluster.php");
	$curl->parms["update-mounts"]="yes";
	if(!$curl->get()){
		NotifyEvents("Failed to update status",$ID);
		return;	
	}
	
	if(!preg_match("#<ANSWER>OK</ANSWER>#s",$curl->data)){
		NotifyEvents("Protocol error",$ID);
		return ;
	}
	
	NotifyEvents("Update settings success...",$ID);
	
	
}

function NotifyDeleteClient($server,$ID){
	$curl=new ccurl("https://$server/exec.gluster.php");
	$curl->parms["delete-server"]="yes";
	if(!$curl->get()){
		NotifyEvents("Failed to send delete order",$ID);
		return;	
	}	
	
	if(!preg_match("#<ANSWER>OK</ANSWER>#s",$curl->data)){
		NotifyEvents("Protocol error",$ID);
		return ;
	}	
	
	$sql="DELETE FROM glusters_clients WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	
}


function NotifyClient($server,$ID){
	writelogs("Notify $server ($ID)",__FUNCTION__,__FILE__,__LINE__);
	$unix=new unix();
	$localport=$unix->LIGHTTPD_PORT();
	$notifed=false;
	if(!function_exists("curl_init")){
		writelogs("curl_init not detected",__FUNCTION__,__FILE__,__LINE__);
		NotifyEvents("{CURLPHP_NOT_INSTALLED}",$ligne["ID"]);
		return null;
	}
	
	$array["notify"]="yes";
	$array["localport"]=$localport;
	
	while (list ($num, $ligne) = each ($array)){
		$curlPost .='&'.$num.'=' . urlencode($ligne);
	}
	
	writelogs("https://$server/exec.gluster.php -> $curlPost",__FUNCTION__,__FILE__,__LINE__);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://$server/exec.gluster.php");
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	
	$data = curl_exec($ch);
	$error=curl_errno($ch);
	if($error>0){
		NotifyEvents("$error",$ID);
		writelogs("Connect to $server error $error",__FUNCTION__,__FILE__,__LINE__); 
	}

switch ($error) {
	case 6:
		NotifyEvents("{error_curl_resolve}",$ID);
		curl_close($ch);
		return null;
		break;
	
	default:break;
}

	if(curl_errno($ch)==false){
		if(preg_match("#404 Not Found#is",$data)){
			writelogs("Connect to $server error 404 Not Found",__FUNCTION__,__FILE__,__LINE__);
			NotifyEvents("404 Not Found: {error_wrong_artica_version}",$ID);
			curl_close($ch);
			return null;
			}
		
		if(preg_match("#GLUSTER_NOT_INSTALLED#is",$data)){
				writelogs("Connect to $server error GLUSTER_NOT_INSTALLED",__FUNCTION__,__FILE__,__LINE__);
				NotifyEvents("{error_gluster_not_installed}",$ID);
				curl_close($ch);
				return null;	
			}
			
		if(preg_match("#CURL_NOT_INSTALLED#is",$data)){
				writelogs("Connect to $server error CURL_NOT_INSTALLED",__FUNCTION__,__FILE__,__LINE__);
				NotifyEvents("{error_php_curl}",$ID);
				curl_close($ch);
				return null;	
			}
		
		if(preg_match("#GLUSTER_MYSQL_ERROR#is",$data)){
				preg_match("#<ERR>(.+?)</ERR>#is",$data,$re);
				writelogs("Connect to $server error GLUSTER_MYSQL_ERROR",__FUNCTION__,__FILE__,__LINE__);
				NotifyEvents("mysql error:{$re[1]}",$ID);
				curl_close($ch);
				return null;	
			}		
	}

if(preg_match("#GLUSTER_OK#is",$data)){
		writelogs("Connect to $server success",__FUNCTION__,__FILE__,__LINE__);
		NotifyEvents("{success}",$ID);
		writelogs("Set this server has notified",__FUNCTION__,__FILE__,__LINE__);
		curl_close($ch);
		$notifed=true;	
}	
if($notifed){
	$q=new mysql();
	$sql="UPDATE glusters_clients SET client_notified='1' WHERE ID=$ID";
	$q->QUERY_SQL($sql,"artica_backup");
}else{
	NotifyEvents("unknown error",$ID);
	writelogs("$server Not notified, unknown error\n$data\n",__FUNCTION__,__FILE__,__LINE__);
}

	
}

function client_restart_notify(){
	$master=@file_get_contents("/etc/artica-cluster/master");
	
	
	$gl=new gluster();
	$myname=@file_get_contents("/etc/artica-cluster/local.name");
	
	writelogs("MASTER=\"$master\"; me=\"$myname\"",__FUNCTION__,__FILE__,__LINE__);
	
	if($myname<>$master){
	if(is_array($gl->clients)){
		while (list ($num, $ligne) = each ($gl->clients) ){
			writelogs("Deleting clusters-$num",__FUNCTION__,__FILE__,__LINE__);
			@unlink("/etc/artica-cluster/clusters-$num");
		}
		}
	}
	writelogs("Notify master",__FUNCTION__,__FILE__,__LINE__);
	NotifyStatus();	
	BuildLocalConf();
	writelogs("Notify master...",__FUNCTION__,__FILE__,__LINE__);
	NotifyStatus();	
}





function ReceiveParams(){
	$localport=$_POST["localport"];
	$master=$_SERVER['REMOTE_ADDR'];
	writelogs("$master:$localport",__FUNCTION__,__FILE__,__LINE__);
	
	
	echo "RECIEVE OK\n\n";
	$users=new usersMenus();
	if(!$users->GLUSTER_INSTALLED){
		echo "GLUSTER_NOT_INSTALLED\n\n";
		die();
	}
	
	if(!function_exists("curl_init")){
		echo "CURL_NOT_INSTALLED\n\n";
		die();
	}
	
	;
	
	$sql="INSERT INTO glusters_servers (server_ip,server_port) VALUES('$master','$localport')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo "GLUSTER_MYSQL_ERROR\n\n<ERR>$q->mysql_error</ERR>";
		die();	
	}
	
	
	echo "GLUSTER_OK";
	MASTERS_GET_SOURCES();
}

function ReceiveServerUpdateMount(){
	echo "\n\n<ANSWER>OK</ANSWER>\n\n";
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?gluster-remounts=yes");
}

function ReceiveServerDelete(){
	$localport=$_POST["localport"];
	$master=$_SERVER['REMOTE_ADDR'];
	writelogs("$master:Receive notification from $master to remove it",__FUNCTION__,__FILE__,__LINE__);
	
	$sql="DELETE FROM  glusters_servers WHERE server_ip='$master'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("$master:$q->mysql_error");
		return ;
	}
	writelogs("$master:Success",__FUNCTION__,__FILE__,__LINE__);
	$sock=new sockets();
	writelogs("$master:Notfy framework to remount",__FUNCTION__,__FILE__,__LINE__);
	$sock->getFrameWork("cmd.php?gluster-remounts=yes");	
	echo "\n\n<ANSWER>OK</ANSWER>\n\n";
	
}

function BuildLocalConf(){
	@mkdir("/etc/artica-cluster",null,true);
	$configfile="/etc/artica-cluster/glusterfs-server.vol";
	@unlink($configfile);
	$gluser=new gluster_samba();
	$conf=$gluser->build();
	if(trim($conf)==null){
		echo "Starting......: Gluster Daemon glusterfs-server.vol no settings...\n";
		return;
	}
	
	@file_put_contents($configfile,$conf);
	if(is_file($configfile)){
		echo "Starting......: Gluster Daemon glusterfs-server.vol done\n";
	}else{
		echo "Starting......: Gluster Daemon glusterfs-server.vol failed\n";
	}
	
}


function MASTERS_GET_SOURCES(){
	
	$sql="SELECT * FROM glusters_servers";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			writelogs("{$ligne["server_ip"]}:{$ligne["server_port"]}:: get sources from...");
			MASTER_GET_SOURCE_CONNECT("{$ligne["server_ip"]}:{$ligne["server_port"]}",$ligne["ID"]);
	}
	
	
}
/*
 * ce connect au host afin de recevoir la liste des dossiers à clusteriser.
 */
function MASTER_GET_SOURCE_CONNECT($host,$ID){
	
	$file="/etc/artica-postfix/croned.1/".md5($host).".vol";
	$timefile=file_time_sec($file);
	if(file_time_sec($file)<30){
		writelogs("MASTER::$host, $file $timefile seconds, need 30s aborting",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	@unlink($file);
	@file_put_contents($file,"#");
	
	$curl=new ccurl("https://$host/exec.gluster.php");
	$curl->parms["bricks"]="yes";
	if(!$curl->get()){return null;}
	
	if(!preg_match("#<SOURCES>(.+?)</SOURCES>#s",$curl->data,$re)){
		writelogs("MASTER::$host, unable to preg_match",__FUNCTION__,__FILE__,__LINE__);
		MASTER_SEND_LOGS($host,"Error parsing sources");
		return null;
	}
	writelogs($re[1],__FUNCTION__,__FILE__,__LINE__);
	$paths=unserialize(base64_decode($re[1]));
	writelogs("MASTER::$host, receive ". count($paths)." directories",__FUNCTION__,__FILE__,__LINE__);
	$array["PATHS"]=$paths;
	$based=base64_encode(serialize($array));
	$sql="UPDATE glusters_servers SET parameters='$based' WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("MASTER::$host, $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		MASTER_SEND_LOGS($host,"Mysql Error $q->mysql_error");
		return;
	}
	MASTER_SEND_LOGS($host,"success receive clustered directories");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?gluster-mounts=yes");
}

function MASTER_SEND_LOGS($host,$text){
	$curl=new ccurl("https://$host/exec.gluster.php");
	$curl->parms["events"]=base64_encode($text);
	if(!$curl->get()){return null;}
}
function ReceiveClientEvents(){
	$conn=$_SERVER['REMOTE_ADDR'];
	$sql="SELECT ID FROM glusters_clients WHERE client_ip='$conn'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	NotifyEvents(base64_decode($_POST["events"]),$ligne["ID"]);
}


function getStatus(){
	$glfs=new gluster_client();
	$mounts=$glfs->get_mounted();
	if(!is_array($mounts)){return "No paths set or mounted";}
	
	$f[]="<hr><strong style=font-size:12px>Client status:</strong><br>";
	while (list ($num, $vol) = each ($mounts) ){
		$path=$glfs->volToPath($vol);
		$f[]="<li><code style=font-size:13px>$path is mounted</code></li>";
	}
	$f[]="<hr>";
	return @implode("\n",$f);
	
}


function NotifyAllServersStatus(){
	$status=getStatus();
	$sql="SELECT * FROM glusters_servers";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		MASTER_SEND_LOGS("{$ligne["server_ip"]}:{$ligne["server_port"]}",$status);
		MASTER_GET_SOURCE_CONNECT("{$ligne["server_ip"]}:{$ligne["server_port"]}",$ligne["ID"]);
	}	
	
}





function server_receive_status(){
	writelogs("Receive infos from {$_POST["NTFY_STATUS"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$gl=new gluster();
	
	if($gl->clients[$_POST["NTFY_STATUS"]]==null){
		writelogs("Depreciated server, send order to delete",__FUNCTION__,__FILE__,__LINE__);
		echo "DELETE_YOU";
		exit;
		}
	
	
	$ini=new Bs_IniHandler();
	while (list ($num, $ligne) = each ($_POST)){
		writelogs("Receive infos $num = $ligne from {$_POST["NTFY_STATUS"]}",__FUNCTION__,__FILE__,__LINE__);
		$ini->_params["CLUSTER"][$num]=$ligne;
	}
	
	$sock=new sockets();
	$sock->SaveClusterConfigFile($ini->toString(),"clusters-".$_POST["NTFY_STATUS"]);
	$cyrus_id=$sock->getFrameWork("cmd.php?idofUser=cyrus");
	echo "CYRUS-ID=$cyrus_id;\n";
	
	
	$gl=new gluster();
	if(is_array($gl->clients)){
		while (list ($num, $name) = each ($gl->clients) ){
			$cl[]=$name;
		}
	}
	
	$datas=implode(";",$cl);
	writelogs("Sending servers list ". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	echo $datas;
	
	
}


function export_bricks(){
	$conn=$_SERVER['REMOTE_ADDR'];
	$sql="SELECT * FROM gluster_clients_brick";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		writelogs("$conn:: {$ligne["brickname"]}={$ligne["source"]}",__FUNCTION__,__FILE__,__LINE__);
		$array[$ligne["brickname"]]=$ligne["source"];	
	}
	
	$serial=serialize($array);
	writelogs("$conn:: send $serial",__FUNCTION__,__FILE__,__LINE__);
	
	echo "<SOURCES>".base64_encode(serialize($array))."</SOURCES>";
}


function CLIENT_MOUNT_FOLDERS(){
	$glfs=new gluster_client();
	$glfs->buildconf();
	
	foreach (glob("/etc/artica-cluster/glusterfs-client/*.vol") as $filename) {
		$path=$glfs->volToPath($filename);
		if($path==null){continue;}
		if(!$glfs->ismounted($path)){
			if($glfs->CheckPath($path)){
				echo "Starting......: Gluster clients ".basename($filename)." mount it\n";
				$glfs->mount($path,$filename);
				if($glfs->ismounted($path)){
					NOTIFY_ALL_MASTERS("Success connect $path");
				}else{
					NOTIFY_ALL_MASTERS("Unable to mount $path");
				}
			}else{
				NOTIFY_ALL_MASTERS("Unable to mount $path");
			}
		}else{
			echo "Starting......: Gluster clients ".basename($filename)." already mounted\n";
		}
		
	}
}

function CLIENT_REMOUNT_FOLDERS(){
	$unix=new unix();
	$mount=$unix->find_program("umount");
	$glfs=new gluster_client();
	$array=$glfs->get_mounted();	
	if(is_array($array)){
		while (list ($index, $volfile) = each ($array) ){
			 echo "Stopping Gluster client......: ".$volfile."\n";
			 shell_exec("umount -l $volfile");
		}
	}else{
		echo "Stopping Gluster client......: no mounted path\n";
		
	}
	
	MASTERS_GET_SOURCES();
	CLIENT_MOUNT_FOLDERS();
	
}

function NOTIFY_ALL_MASTERS($text){
	$sql="SELECT * FROM glusters_servers";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			MASTER_SEND_LOGS("{$ligne["server_ip"]}:{$ligne["server_port"]}",$text);
			MASTER_GET_SOURCE_CONNECT("{$ligne["server_ip"]}:{$ligne["server_port"]}",$ligne["ID"]);
			
	}	
}



?>