<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.cyrus.cluster.php');


if(isset($_GET["test-authenticate"])){test_authenticate();exit;}
if(isset($_GET["enable-frontend"])){enable_frontend();exit;}
if(isset($_GET["create-mbx"])){create_mbx();exit;}
if(isset($_GET["user-infos"])){UserInfos();exit;}
if(isset($_GET["exists-mbx"])){UserExists();exit;}
if(isset($_GET["MbxStat"])){MbxStat();exit;}
if(isset($_GET["DelMbx"])){DelMbx();exit;}
if(isset($_GET["cyrquota"])){cyrquota();exit;}
if(isset($_GET["MailboxQuota"])){MailboxQuota();exit;}
if(isset($_GET["cluster-master-enable"])){EnableReplica();exit;}
if(isset($_GET["delete-replicat"])){DeleteReplicat();exit;}
if(isset($_GET["export-ou"])){export_ou();exit;}
if(isset($_GET["export-ldap"])){export_ldap();exit;}
if(isset($_GET["disable-replica"])){DisableMasterCluster();exit;}
if(isset($_POST["cyrus-cluster"])){cyrus_cluster_receive();exit;}

writelogs("Unable to understand your query GET=".implode("&",$_GET),__FUNCTION__,__FILE__,__LINE__);
echo "\nUnable to understand your query\n\n";
debug_post();
die();


function debug_post(){
	
	if(is_array($_POST)){
		while (list ($num, $val) = each ($_POST) ){
			writelogs("POST: $num = \"$val\"",__FUNCTION__,__FILE__,__LINE__);
		}
	}
	
}

function test_authenticate(){
	$ldap=new clladp();
	$users=new usersMenus();
	
	$me=$_GET["requestedback"];
	
	if($me==null){
		echo("$users->hostname (Artica v$users->ARTICA_VERSION):: {failed} {SERVER_REQUESTED} IS NULL");
		die();
	}	
	
	$me_name=$users->hostname;
	if(strtolower($me)<>strtolower($me_name)){
		echo("$users->hostname (Artica v$users->ARTICA_VERSION):: {failed} {SERVER_REQUESTED} \"$me\" {IS_DIFFERENT_THAN} $me_name {REQUIRE_FULL_HOSTNAME}");
		die();
	}
	
	
	if(!islogged()){return false;}
	echo("SUCCESS");
	die();
	
	
}

function islogged($nomurder=0,$noecho=0){
	$users=new usersMenus();
	$ldap=new clladp();
	
	if($_GET["admin"]<>$ldap->ldap_admin){
		if($noecho==0){
			echo("$users->hostname (Artica v$users->ARTICA_VERSION):: {failed} {NT_STATUS_LOGON_FAILURE}");
			die();
		}
		return false;
	}
	
	if($_GET["pass"]<>$ldap->ldap_password){
		if($noecho==0){
			echo("$users->hostname (Artica v$users->ARTICA_VERSION):: {failed} {NT_STATUS_LOGON_FAILURE} ");
			die();
		}
		return false;
	}
		
	if($nomurder==0){
		$users=new usersMenus();
		if(!$users->cyrus_murder_installed){
			echo("$users->hostname (Artica v$users->ARTICA_VERSION):: {failed} {error_module_not_installed}: {APP_CYRUS_MURDER} ");
			die();
		}
	}
	
	return true;
}

function enable_frontend(){
	$ldap=new clladp();
	$sock=new sockets();
	$users=new usersMenus();
	
	
	if(!islogged()){return false;}


	
	$sock=new sockets();
	$sock->SET_INFO('CyrusEnableBackendMurder',1);
	$datas=explode("\n",$sock->GET_INFO('CyrusFrondEndServers'));
	
	if(is_array($datas)){
		while (list ($num, $val) = each ($datas) ){
			if($val==null){continue;}
			$SERVS[$val]=$val;
		}
	}
	
	$SERVS[$_GET["hostname"]]=$_GET["hostname"];
	
	if(is_array($SERVS)){
	while (list ($num, $val) = each ($datas) ){
			if($val==null){continue;}
			$newdatas[]=$val;
		}
		if(is_array($newdatas)){
			$sock->SET_INFO("CyrusFrondEndServers",implode("\n",$newdatas));
		}else{
			$sock->SET_INFO("CyrusFrondEndServers",$newdatas);
		}
	}
	
	
	echo "[BACKEND]\n";
	echo "suffix=$ldap->suffix\n";
	echo "success=1\n";
$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes');
	
}


function create_mbx(){
	
	if(!islogged(1)){return false;}
	$users=new usersMenus();
	$cyrus=new cyrus();
	$uid=$_GET["mbx"];
	if($cyrus->CreateMailbox($uid)==false){
		$error="{failed}:{creating_mailbox}:$uid\n$cyrus->cyrus_last_error\n";
		}
		else{$error="{success}:{creating_mailbox}:$uid\n";
			
		}
	echo "Message from: $users->hostname\n"; 	
	echo $error;
	echo "\n";
	echo $cyrus->cyrus_infos_cleaned;	
	
}

function UserInfos(){
if(!islogged(1)){return false;}
	$sock=new sockets();
	echo $sock->getfile("MailboxInfos:{$_GET["mbx"]}");
}

function UserExists(){
if(!islogged(1)){return false;}
	$sock=new sockets();
	echo $sock->getfile("MailboxExists:{$_GET["mbx"]}");	
	
}

function MbxStat(){
if(!islogged(1)){return false;}
	$sock=new sockets();
	echo "\n".$sock->getfile("MbxStat:{$_GET["mbx"]}")." OK\n";	
	
}

function DelMbx(){
if(!islogged(1)){return false;}
	$sock=new sockets();
	
	echo "\n".$sock->getFrameWork("cmd.php?DelMbx={$_GET["mbx"]}")	." OK\n";	
	
}

function MailboxQuota(){
if(!islogged(1)){return false;}
	$sock=new sockets();
	echo "\n".$sock->getfile("MailboxQuota:{$_GET["mbx"]}")." OK\n";	
	
}

function cyrquota(){
if(!islogged(1)){return false;}
	$sock=new sockets();
	echo $sock->getfile("cyrquota");		
}

function DisableMasterCluster(){
	if(!islogged(1)){return false;}
	$sock=new sockets();
	$sock->SET_INFO("EnableCyrusMasterCluster","0");
	$sock->getfile('ClusterDisableReplica');
	$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes');	
}

function EnableReplica(){
if(!islogged(1)){return false;}
$users=new usersMenus();
$ldap_admin=$_GET["ldap_admin"];
$ldap_password=$_GET["ldap_password"];
$suffix=$_GET["suffix"];
$master_ip=$_GET["master-ip"];
$master_port=$_GET["master-port"];
$artica_port=$_GET["https-port"];
$sock=new sockets();

$EnableCyrusMasterCluster=$sock->GET_INFO("EnableCyrusMasterCluster");
if($EnableCyrusMasterCluster==1){echo "{failed} {already_a_master}";exit;}
if(!$users->cyrus_sync_installed){echo "{failed} {sync_tools_not_compiled}";exit;}
if($sock->GET_INFO("CyrusEnableImapMurderedFrontEnd")==1){echo "{failed} {already_a_murder_frontend}";exit;}
	

$ini=new Bs_IniHandler();
$ini->set("REPLICA","servername",$master_ip);
$ini->set("REPLICA","username",$ldap_admin);
$ini->set("REPLICA","password",$ldap_password);
$ini->set("REPLICA","artica_port",$artica_port);
$ini->set("REPLICA","suffix",$suffix);
$sock->SaveConfigFile($ini->toString(),"CyrusReplicaLDAPConfig");
$sock->SET_INFO("EnableCyrusReplicaCluster",1);
$sock->SET_INFO("CyrusReplicaClusterPort",$master_port);
$sock->SET_INFO("CyrusReplicaClusterServer",$master_ip);
$ini2=new Bs_IniHandler();
$ini->set("ANSWER","response","SUCCESS");
echo $ini->toString();
$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes');
}



function DeleteReplicat(){
	$sock=new sockets();
	$sock->SET_INFO("EnableCyrusReplicaCluster",0);
	$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes');
	echo "\n{success}\n";
	die();	
	
}

function export_ldap(){
	$tmp_file = $_FILES['file']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	$uploadedfile=$content_dir.'/'.basename($tmp_file);
	if( !is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){echo "FAILED UPLOAD file $tmp_file\n";exit;}
	if( !move_uploaded_file($tmp_file, $uploadedfile) ){echo "FAILED MOVE\n";exit;}	
	$ldap=new clladp();
	$sock=new sockets();
	$ldap_server=$ldap->ldap_host;
	if($ldap_server<>'127.0.0.1'){
		$ldap_server='127.0.0.1';
		
		echo "Change LDAP server\n";
		$sock->getfile("ChangeLDPSSET:127.0.0.1;389;{$_GET["ldap-suffix"]};yes");
		$sock->getfile("ChangeUserAdmin:{$_GET["admin"]}ChangeUserPassword:{$_GET["pass"]}");
		
	}
	
	$sock->getfile('ReplicateLDAP:'.$uploadedfile);
	
	echo "Receive $uploadedfile for ldap:$ldap_server\n";
}

function export_ou(){
	$tmp_datas = $_POST["exported-datas"];
	
	$ldap=new clladp();
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	$uploadedfile=$content_dir.'/'.basename($tmp_file);
	$original_suffix=$_GET["original-suffix"];
	$orgininal_org=$_POST["exported-org"];
	$uploadedfile="$content_dir/$orgininal_org.ldif";
	
	
	if(!$ldap->ExistsDN("dc=organizations,$ldap->suffix")){
		echo "First branch dc=organizations,$ldap->suffix does not exists\n";
		exit;
	}
	
	
	
	$datas=base64_decode($tmp_datas);
	echo "Change ldap schema from \"$original_suffix\" to \"$ldap->suffix\" \n";
	writelogs("Change ldap schema from \"$original_suffix\" to \"$ldap->suffix\"",__FUNCTION__,__FILE__,__LINE__);
	$datas=CleanLDAPDatas(trim($original_suffix),$ldap->suffix,$datas);
	echo "Cleaned " . strlen($datas)." bytes...\n";
	writelogs("Cleaned " . strlen($datas)." bytes...",__FUNCTION__,__FILE__,__LINE__);
	
	
	if(!file_put_contents($uploadedfile,$datas)){echo "FAILED CLEAN DATAS\n";exit;}
	
$cmd="/usr/bin/ldapadd -v -D cn=$ldap->ldap_admin,$ldap->suffix -h $ldap->ldap_host -p $ldap->ldap_port -w $ldap->ldap_password -x -f $uploadedfile";
writelogs("$cmd",__FUNCTION__,__FILE__,__LINE__);
exec($cmd,$res);	
echo implode("\n",$res);
	

echo "\n\nSUCCESS\n";
}
///usr/bin/curl -H 'Expect: ' --sslv3 --insecure -F file=@/home/dtouzeau/Bureau/hostapd-0.6.9.tar.gz "https://192.168.1.131:9000/cyrus.murder.listener.php"

function CleanLDAPDatas($oldldap,$newldap,$datas){
$tb=explode("\n",$datas);

while (list ($num, $ligne) = each ($tb) ){
if(preg_match('#^structuralObjectClass#',$ligne)){continue;}
if(preg_match('#^entryUUID#',$ligne)){continue;}
if(preg_match('#^creatorsName#',$ligne)){continue;}
if(preg_match('#^createTimestamp#',$ligne)){continue;}
if(preg_match('#^entryCSN#',$ligne)){continue;}
if(preg_match('#^modifiersName#',$ligne)){continue;}
if(preg_match('#^modifyTimestamp#',$ligne)){continue;}	
$ligne=str_replace($oldldap,$newldap,$ligne);
$conf=$conf.$ligne."\n";	
}	
return $conf;
}

function cyrus_cluster_receive(){
	$array=unserialize(base64_decode($_POST["cyrus-cluster"]));
	$users=new usersMenus();
	$sock=new sockets();
	$fqdn=$users->fqdn;
	if($fqdn==null){$fqdn=$users->hostname;}
	$_GET["admin"]=$array["admin"];
	$_GET["pass"]=$array["pass"];
	if(!islogged(1,1)){
		writelogs("Bad username and password",__FUNCTION__,__FILE__,__LINE__);
		$return["RESULT"]="$fqdn: {$array["admin"]}: {username}/{password} {failed}";
		$return["REPLY"]=false;
		echo base64_encode(serialize($return));
		return;
	}
	writelogs("Username and password OK",__FUNCTION__,__FILE__,__LINE__);
	
	$users=new usersMenus();
	if(!$users->cyrus_sync_installed){
		$return["RESULT"]="$fqdn: {sync_tools_not_compiled}";
		$return["REPLY"]=false;
		echo base64_encode(serialize($return));
		return ;
	}
	
	
	
	if($sock->GET_INFO("CyrusEnableImapMurderedFrontEnd")==1){
		$return["RESULT"]="$fqdn: {already_a_murder_frontend}";
		$return["REPLY"]=false;
		echo base64_encode(serialize($return));
		return ;
	}
	
	if(!function_exists("curl_init")){
		$return["RESULT"]="$fqdn:{error_php_curl}";
		$return["REPLY"]=false;
		echo base64_encode(serialize($return));
		return ;
	}

	
	writelogs("Success pass tests...",__FUNCTION__,__FILE__,__LINE__);

	if($array["cmd"]=="tests"){
		$return["RESULT"]="$fqdn: {success}";
		$return["REPLY"]=true;
		echo base64_encode(serialize($return));
		return;
	}
	


	
	
	if($array["cmd"]=="connect"){
			writelogs("Try to be a replica {$array["master_ip"]}:{$array["master_artica_port"]}",__FUNCTION__,__FILE__,__LINE__);
			$cyrus_cluster=new cyrus_cluster();
			if(!$cyrus_cluster->test_remote_server($array["master_ip"],$array["master_artica_port"],$array["ldap_admin"],$array["ldap_password"])){
				writelogs("Unable to call master server {$array["master_ip"]}:$cyrus_cluster->error_text",__FUNCTION__,__FILE__,__LINE__);
				$return["RESULT"]=$cyrus_cluster->error_text;
				$return["REPLY"]=false;
				echo base64_encode(serialize($return));
				return ;
			}
			
			writelogs("Call master server {$array["master_ip"]}: success",__FUNCTION__,__FILE__,__LINE__);
			$ini=new Bs_IniHandler();
			$ini->set("REPLICA","servername",$array["master_ip"]);
			$ini->set("REPLICA","username",$array["ldap_admin"]);
			$ini->set("REPLICA","password",$array["ldap_password"]);
			$ini->set("REPLICA","artica_port",$array["master_artica_port"]);
			$ini->set("REPLICA","suffix",$array["suffix"]);
			$sock->SaveConfigFile($ini->toString(),"CyrusReplicaLDAPConfig");
			$sock->SET_INFO("EnableCyrusReplicaCluster",1);
			$sock->SET_INFO("EnableCyrusMasterCluster",0);			
			$sock->SET_INFO("CyrusReplicaClusterPort",$array["master_cyrus_port"]);
			$sock->SET_INFO("CyrusReplicaClusterServer",$array["master_ip"]);
			$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');	
			writelogs("Success Enable replica",__FUNCTION__,__FILE__,__LINE__);	
			$return["RESULT"]="{success}";
			$return["REPLY"]=true;
			echo base64_encode(serialize($return));
			writelogs("Success to be a replica",__FUNCTION__,__FILE__,__LINE__);
			$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');
			return;
	}
	
	if($array["cmd"]=="disconnect"){
		writelogs("Try to disable replica",__FUNCTION__,__FILE__,__LINE__);
		$sock->SET_INFO("EnableCyrusReplicaCluster",0);
		writelogs("Success disable replica",__FUNCTION__,__FILE__,__LINE__);
		$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');
	}
	
	if($array["cmd"]=="isReplica"){
			if($sock->GET_INFO("EnableCyrusReplicaCluster")==1){
				$return["RESULT"]="{success}";
				$return["REPLY"]=true;
			}else{
				$return["RESULT"]="{failed}: not a replica";
				$return["REPLY"]=false;				
			}
		echo base64_encode(serialize($return));
	}	
	
	
	

	
}






/*mupdate_config: standard
    The configuration of the mupdate servers in the Cyrus Murder. The "standard" config is one in which there are discreet frontend (proxy) and backend servers.
 The "unified" config is one in which a server can be both a frontend and backend. The "replicated" config is one in which multiple backend servers all share 
the same mailspool, but each have their own "replicated" copy of mailboxes.db.

    Allowed values: standard, unified, replicated 
http://www.mail-archive.com/info-cyrus@lists.andrew.cmu.edu/msg34331.html
http://cyrusimap.web.cmu.edu/imapd/install-replication.html
http://www.comfsm.fm/computing/cyrus-imapd/install-replication.html
http://www.getdropbox.com/pricing
* 
* 
* */


?>
