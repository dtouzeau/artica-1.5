<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.cyrus.cluster.php');
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableCyrusMasterCluster"])){SaveMasterInfos();exit;}
	if(isset($_GET["ConfigureReplicat"])){ConfigureReplicat();exit;}
	if(isset($_GET["SaveReplicaInfos"])){SaveReplicaInfos();exit;}
	if(isset($_GET["DelteReplica"])){DelteReplica();exit;}
	if(isset($_GET["DisconnectSlave"])){DisconnectSlave();exit;}
	
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{CYRUS_CLUSTER}');
$SET_REPLICA_SERVER=$tpl->_ENGINE_parse_body('{SET_REPLICA_SERVER}');
	
	$users=new usersMenus();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	
$html="

	function CyrusCLusterLoadPage(){
		YahooWin(750,'$page?popup=yes','$title');
	
	}
	
	function AddCyrusReplica(){
		YahooWin2(650,'$page?ConfigureReplicat=yes','$SET_REPLICA_SERVER');
	}
	
var X_SaveReplicaInfos= function (obj) {
	var results=obj.responseText;
	alert(results);
	AddCyrusReplica();
	}	
		
	function SaveReplicaInfos(){
		var XHR = new XHRConnection();
		XHR.appendData('SaveReplicaInfos','yes');
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('artica_port',document.getElementById('artica_port').value);
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',document.getElementById('password').value);
		XHR.appendData('master_ip',document.getElementById('master_ip').value);
		document.getElementById('form_replica_div').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_SaveReplicaInfos);				
	}
	
	
var X_EnableCyrusMasterCluster= function (obj) {
	var results=obj.responseText;
	CyrusCLusterLoadPage();
	}		
	
	function EnableCyrusMasterCluster(){
		var XHR = new XHRConnection();
		var EnableCyrusMasterCluster=document.getElementById('EnableCyrusMasterCluster').value;
		var CyrusClusterPort=document.getElementById('CyrusClusterPort').value;
		var CyrusClusterID=document.getElementById('CyrusClusterID').value;
		XHR.appendData('EnableCyrusMasterCluster',EnableCyrusMasterCluster);
		XHR.appendData('CyrusClusterPort',CyrusClusterPort);
		XHR.appendData('CyrusClusterID',CyrusClusterID);		
		document.getElementById('wizard_zone').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_EnableCyrusMasterCluster);	
	
	
	}
	
var X_DisconnectSlave= function (obj) {
	var results=obj.responseText;
	alert(results);
	CyrusCLusterLoadPage();
	}		
	
function DisconnectSlave(){
	var XHR = new XHRConnection();
	XHR.appendData('DisconnectSlave','yes');
	document.getElementById('form_replica_div').innerHTML='<img src=img/wait_verybig.gif>';
	XHR.sendAndLoad('$page', 'GET',X_DisconnectSlave);	
}
	
var X_DeleteReplica= function (obj) {
	var results=obj.responseText;
	alert(results);
	AddCyrusReplica();
	}	
	
	function DeleteReplica(){
	var XHR = new XHRConnection();
		XHR.appendData('DelteReplica','yes');
		document.getElementById('form_replica_div').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_DeleteReplica);			
	}
	
	
	CyrusCLusterLoadPage();

";
	
echo $html;	
	
}


function popup(){
	
	$sock=new sockets();
	$EnableCyrusMasterCluster=$sock->GET_INFO("EnableCyrusMasterCluster");
	$CyrusClusterID=$sock->GET_INFO("CyrusClusterID");
	$EnableCyrusReplicaCluster=$sock->GET_INFO("EnableCyrusReplicaCluster");
	$CyrusClusterPort=$sock->GET_INFO("CyrusClusterPort");
	if($CyrusClusterPort==null){$CyrusClusterPort=2005;}
	if($CyrusClusterID==null){$CyrusClusterID=1;}

	if($EnableCyrusReplicaCluster==1){echo popup_as_replica();exit;}
	
	
	
	if($EnableCyrusMasterCluster){
		$addreplica=Paragraphe("64-net-server-add.png","{SET_REPLICA_SERVER}","{SET_REPLICA_SERVER_TEXT}","javascript:AddCyrusReplica()");
		$sync=Paragraphe("64-syncmbx.png","{REPLICATE_YOUR_MAILBOXES}","{REPLICATE_YOUR_MAILBOXES_TEXT}","javascript:Loadjs('cyrus.sync.client.php')");
		
		
	}
	
	$rebuild=Buildicon64("DEF_ICO_CYR_REBUILD");
	$help_me=Paragraphe("wizard-mail-64.png","{HELP_ME_RETRANSLATOR}","{CYRUS_REPLICA_WIZARD_HELP}","javascript:Loadjs('wizard.cyrus.cluster.php')");
	
	$enable=Paragraphe_switch_img('{ENABLE_CYRUS_CLUSTER_MASTER}','{ENABLE_CYRUS_CLUSTER_MASTER_TEXT}','EnableCyrusMasterCluster',$EnableCyrusMasterCluster,
	"ENABLE_CYRUS_CLUSTER_MASTER_TEXT",300);
	
	$form1="<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{listen_cluster_port}:</td>
		<td valign='top'>". Field_text('CyrusClusterPort',$CyrusClusterPort,'width:60px')."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{uid}:</td>
		<td valign='top'>". Field_text('CyrusClusterID',$CyrusClusterID,'width:20px')."</td>
		<td valign='top'>" . help_icon('{CyrusClusterID_TEXT}')."</td>
	</tr>	
	</table>";
	
	
	$form="<table style='width:100%'>
	<tr>
		<td valign='top'>$enable</td>
	</tr>
	<tr>
	<td valign='top'>$form1</td>
	</tr>
	<tr>
		<td valign='top' align='right'><hr>
		". button("{edit}","EnableCyrusMasterCluster()")."
			
		</td>
	</tr>
	</table>
	
	";
	

	$html="
	<div id='wizard_zone'>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/128-cluster.png'></td>
	<td valign='top'>
	<div class=explain>{CYRUS_CLUSTER_EXPLAIN}</div>
	$form
	</td>
	<td valign='top'>$addreplica<br>$rebuild<br>$sync<br>$help_me</td>
	</tr>
	</table>
	</div>
	<br>
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	//http://madole.net/pushmail/
	
}

function popup_as_replica(){
	
$ini=new Bs_IniHandler();
$sock=new sockets();
$ini->loadString($sock->GET_INFO("CyrusReplicaLDAPConfig"));
	
$html="<H1>{CYRUS_CLUSTER}</H1>
	<div id='wizard_zone'>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/128-cluster.png'></td>
	<td valign='top'><div id='form_replica_div'>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/net-server-infos.png'></td>
	<td valign='top'>
		<p class=caption style='font-size:13px;font_weight:bold'>{CYRUS_AS_REPLICA_EXPLAIN}
		<br>
		<div><strong>{master_server_cluster}:</strong style='font-size:16px'>{$ini->_params["REPLICA"]["servername"]}</strong></div>
		<div style='text-align:right;margin:10px'>
		<hr>
		". button("{disconnect}","DisconnectSlave()")."
		</div>
		</p>
	</div></td>
	</tr>
	</table>
	
	</td>
	<td valign='top'></td>
	</tr>
	</table>
	</div>
	<br>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function SaveMasterInfos(){
	$EnableCyrusMasterCluster=$_GET["EnableCyrusMasterCluster"];
	$CyrusClusterPort=$_GET["CyrusClusterPort"];
	$sock=new sockets();
	$sock->SET_INFO("EnableCyrusMasterCluster",$EnableCyrusMasterCluster);
	$sock->SET_INFO("CyrusClusterPort",$CyrusClusterPort);
	$sock->SET_INFO("CyrusClusterID",$_GET["CyrusClusterID"]);
	
	if($EnableCyrusMasterCluster==0){
		$cyrus=new cyrus_cluster();
		if(!$cyrus->notify_disable_replica()){
			$tpl=new templates();
			echo $tpl->javascript_parse_text($cyrus->error_text);
			$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');	
		}
	}
	
	}
	
function IsreplicaEnabled(){
	$cyrus=new cyrus_cluster();
	return $cyrus->is_a_replica();
	}
	
function ConfigureReplicat(){
	$sock=new sockets();
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO('CyrusClusterReplicaInfos'));
	
	if($ini->_params["REPLICA"]["master_ip"]==null){$ini->_params["REPLICA"]["master_ip"]=$users->hostname;}
	if($ini->_params["REPLICA"]["artica_port"]==null){$ini->_params["REPLICA"]["artica_port"]="9000";}
	if($ini->_params["REPLICA"]["username"]==null){$ini->_params["REPLICA"]["username"]="admin";}
	
	if(IsreplicaEnabled()){
		$deletereplica=Paragraphe('64-delete-cluster.png','{DISABLE_REPLICA}','{DISABLE_REPLICA_TEXT}',"javascript:DeleteReplica();");
	}
	
	$html="
	<H1>{SET_REPLICA_SERVER}</H1>
	<p class=caption style='font-size:12px'>{SET_REPLICA_SERVER_EXPLAIN}</p>
	
	<table style='width:100%'>
	<tr>
	<td valign='top'>$deletereplica</td>
	<td valign='top'>
	<div id='form_replica_div'>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend nowrap>{replica_master_ip}:</td>
		<td>" . Field_text('master_ip',$ini->_params["REPLICA"]["master_ip"],'width:150px')."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{replica_ip}:</td>
		<td>" . Field_text('servername',$ini->_params["REPLICA"]["servername"],'width:150px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{artica_console_port}:</td>
		<td>" . Field_text('artica_port',$ini->_params["REPLICA"]["artica_port"],'width:40px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{username}:</td>
		<td>" . Field_text('username',$ini->_params["REPLICA"]["username"],'width:120px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td>" . Field_password('password',$ini->_params["REPLICA"]["password"],'width:120px')."</td>
	</tr>			
	<tr>
	<td colspan=2 align='right'>
	<hr>
	". button("{edit} {notify}","SaveReplicaInfos()")."
	</tr>
	</table>	
	</div>
	</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"cyrus.murder.php");
	
}
function SaveReplicaInfos(){
	
	$ini=new Bs_IniHandler();
	$ini->_params["REPLICA"]["servername"]=$_GET["servername"];
	$ini->_params["REPLICA"]["artica_port"]=$_GET["artica_port"];
	$ini->_params["REPLICA"]["username"]=$_GET["username"];
	$ini->_params["REPLICA"]["password"]=$_GET["password"];
	$ini->_params["REPLICA"]["master_ip"]=$_GET["master_ip"];
	$tpl=new templates();
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),"CyrusClusterReplicaInfos");
	$cyrus=new cyrus_cluster();
	if(!$cyrus->notify_replica()){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{failed}:$cyrus->error_text");
		return;
	}
	
	$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');
}

function DelteReplica(){
	$cyrus=new cyrus_cluster();
	if(!$cyrus->notify_disable_replica()){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{failed}:$cyrus->error_text");
		return;
	}
	
	$sock=new sockets();
	$sock->SET_INFO("EnableCyrusMasterCluster",0);
	$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');	
	
}

function DisconnectSlave(){
	$sock=new sockets();
	$tpl=new templates();
	$sock->SET_INFO('EnableCyrusReplicaCluster','0');
	echo $tpl->_ENGINE_parse_body($sock->getfile('ClusterDisableSlave'));
	$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes&force=yes');
}



?>