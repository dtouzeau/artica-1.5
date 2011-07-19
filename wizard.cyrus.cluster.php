<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.cyrus.cluster.php');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["REMOTE_ARTICA_SERVER"])){
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER"]=$_GET["REMOTE_ARTICA_SERVER"];
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER_PORT"]=$_GET["REMOTE_ARTICA_SERVER_PORT"];
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_USR"]=$_GET["REMOTE_ARTICA_USR"];
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_PASS"]=$_GET["REMOTE_ARTICA_PASS"];
		exit;
	}
	
	if(isset($_GET["LOCAL_ARTICA_SERVER"])){build();exit;}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-cluster-infos"])){popup_cluster_infos();exit;}
	if(isset($_GET["popup-cluster-finish"])){popup_cluster_finish();exit;}
	
js();	
	
function js(){

	$page=CurrentPageName();
	$tpl=new templates();

	$title=$tpl->_ENGINE_parse_body("{WIZARD_BACKUP}");
	$WIZARD_CONFIGURE_RESOURCE=$tpl->_ENGINE_parse_body("{WIZARD_BACKUP_CHOOSE_STORAGE}");
	$WIZARD_COMPILE=$tpl->_ENGINE_parse_body("{WIZARD_COMPILE}");
	$WIZARD_CONFIGURE_SCHEDULE=$tpl->_ENGINE_parse_body("{WIZARD_CONFIGURE_SCHEDULE}");
	$WIZARD_FINISH=$tpl->_ENGINE_parse_body("{WIZARD_FINISH}");
	
	$html="
	
	function WizardCyrusClusterLoad(){YahooWin2(650,'$page?popup=yes','$title');}
	function WizardCyrusClusterInfos(){YahooWin2(650,'$page?popup-cluster-infos=yes','$title');}
	function WizardCyrusClusterFinish(){YahooWin2(650,'$page?popup-cluster-finish=yes','$title');}
	
	var x_WizardCyrusReplicaSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		WizardCyrusClusterInfos();
	 }	
	 
	var x_WizardClusterBuild= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);return;};
		Loadjs('cyrus.clusters.php');
		YahooWin2Hide();
	 }		 
	 
		
		
	function WizardCyrusReplicaSave(){
		var XHR = new XHRConnection();
		var REMOTE_ARTICA_SERVER=document.getElementById('REMOTE_ARTICA_SERVER').value;
		if(REMOTE_ARTICA_SERVER.length>1){
				XHR.appendData('REMOTE_ARTICA_SERVER',REMOTE_ARTICA_SERVER);
				XHR.appendData('REMOTE_ARTICA_SERVER_PORT',document.getElementById('REMOTE_ARTICA_SERVER_PORT').value);
				XHR.appendData('REMOTE_ARTICA_USR',document.getElementById('REMOTE_ARTICA_USR').value);
				XHR.appendData('REMOTE_ARTICA_PASS',document.getElementById('REMOTE_ARTICA_PASS').value);
				XHR.sendAndLoad('$page', 'GET',x_WizardCyrusReplicaSave);
			}
	}

	function WizardClusterBuild(){
		var XHR = new XHRConnection();
		var LOCAL_ARTICA_SERVER=document.getElementById('LOCAL_ARTICA_SERVER').value;
		if(LOCAL_ARTICA_SERVER.length>1){
				XHR.appendData('LOCAL_ARTICA_SERVER',LOCAL_ARTICA_SERVER);
				XHR.sendAndLoad('$page', 'GET',x_WizardClusterBuild);
			}
	}	
	
	 
		
		
		WizardCyrusClusterLoad();
		";
		
	echo $html;	
		
	
}


	
	
function popup(){
	
	if($_SESSION["WIZARD_RETRANS"]["REMOTE_ARTICA_SERVER_PORT"]==null){$_SESSION["WIZARD_RETRANS"]["REMOTE_ARTICA_SERVER_PORT"]=9000;}
	
	$users=new usersMenus();
	if(!$users->cyrus_sync_installed){
		$html="
		<H1>{WIZARD_CLUSTER_CYRUS_WELCOME}</H1>
		<p style='font-size:12px'>{WIZARD_CLUSTER_CYRUS_EXPLAIN}</p>
		<p style='font-size:12px;color:red'>{failed} {sync_tools_not_compiled}</p>
		";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}
	
	
	$html="
	<H1>{WIZARD_CLUSTER_CYRUS_WELCOME}</H1>
	<p style='font-size:12px'>{WIZARD_CLUSTER_CYRUS_EXPLAIN}</p>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_CLUSTER_CYRUS_REPLICA_INFOS}<hr></H3>
	
		<table style='width:100%'>
	<tr>
		<td style='font-size:13px' align='right'>{servername}:</td>
		<td>". Field_text("REMOTE_ARTICA_SERVER",$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER"],"font-size:13px;padding:5px")."</td>
	</tr>	
	<tr>
		<td style='font-size:13px' align='right'>{listen_port}:</td>
		<td>". Field_text("REMOTE_ARTICA_SERVER_PORT",$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER_PORT"],"font-size:13px;padding:5px")."</td>
	</tr>
	<tr>
		<td style='font-size:13px' align='right'>{username}:</td>
		<td>". Field_text("REMOTE_ARTICA_USR",$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_USR"],"font-size:13px;padding:5px")."</td>
	</tr>		
	<tr>
		<td style='font-size:13px' align='right'>{password}:</td>
		<td>". Field_password("REMOTE_ARTICA_PASS",$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_PASS"],"font-size:13px;padding:5px")."</td>
	</tr>	
	</table>
		<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{cancel}","WizardCyrusCancel()")."</td>
			<td width=50% align='right'>". button("{next}","WizardCyrusReplicaSave()")."</td>
		</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}


function popup_cluster_infos(){
	
	$cyr=new cyrus_cluster();
	if(!$cyr->test_remote_server($_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER"],
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER_PORT"],
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_USR"],
		$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_PASS"])){
		$error=$cyr->error_text;

		$html="<p style='font-size:12px;color:red'>{WIZARD_CLUSTER_ERROR}</p>
		<p style='font-size:13px;color:red;font-weight:bold'>$error</p>	<hr>
		<table style='width:100%'>
			<tr>
				<td width=50%>". button("{back}","WizardCyrusClusterLoad()")."</td>
				<td width=50% align='right'>". button("{back}","WizardCyrusClusterLoad()")."</td>
			</tr>
		</table>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		return;				
		
	}
	
	$users=new usersMenus();
	if($_SESSION["WIZARD_CYRUS"]["LOCAL_ARTICA_SERVER"]==null){
		$_SESSION["WIZARD_CYRUS"]["LOCAL_ARTICA_SERVER"]=$users->hostname;
	}
	
	$html="<H3 style='color:#005447;font-size:16px'>{WIZARD_CLUSTER_CYRUS_REPLICA_SUCCESS}<hr></H3>
		<p style='font-size:13px'>{server}:&nbsp;<strong>{$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER"]}<br>{WIZARD_CLUSTER_CYRUS_REPLICA_SUCCESS_EXPLAIN}</p>
		</strong>
	<table style='width:100%'>
	<tr>
		<td style='font-size:13px' align='right'>{ip_address}:</td>
		<td>". Field_text("LOCAL_ARTICA_SERVER",$_SESSION["WIZARD_CYRUS"]["LOCAL_ARTICA_SERVER"],"font-size:13px;padding:5px")."</td>
	</tr>	
	</table>		
		
		<center>
		<hr>
		". button("{BUILD_CLUSTER}","WizardClusterBuild()")."
		<hr>
		</center>
	</table>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardCyrusClusterLoad()")."</td>
			<td width=50% align='right'>". button("{BUILD_CLUSTER}","WizardClusterBuild()")."</td>
		</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function popup_cluster_finish(){

$html="<H3 style='color:#005447;font-size:16px'>{WIZARD_CLUSTER_CYRUS_REPLICA_SUCCESS}<hr></H3>

	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardCyrusClusterInfos()")."</td>
			<td width=50% align='right'>&nbsp;</td>
		</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

	
function build(){
	$sock=new sockets();
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	
	$_SESSION["WIZARD_CYRUS"]["LOCAL_ARTICA_SERVER"]=$_GET["LOCAL_ARTICA_SERVER"];
	
	$ini->_params["REPLICA"]["servername"]=$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER"];
	$ini->_params["REPLICA"]["artica_port"]=$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_SERVER_PORT"];
	$ini->_params["REPLICA"]["username"]=$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_USR"];
	$ini->_params["REPLICA"]["password"]=$_SESSION["WIZARD_CYRUS"]["REMOTE_ARTICA_PASS"];
	$ini->_params["REPLICA"]["master_ip"]=$_SESSION["WIZARD_CYRUS"]["LOCAL_ARTICA_SERVER"];
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),"CyrusClusterReplicaInfos");
	$cyrus=new cyrus_cluster();
	if(!$cyrus->notify_replica()){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{failed}:$cyrus->error_text");
		$sock->SET_INFO("EnableCyrusMasterCluster",0);
		$sock->SET_INFO("CyrusClusterPort","2005");
		$sock->SET_INFO("CyrusClusterID",1);		
		return;	
		
	}
	
	$sock->SET_INFO("EnableCyrusMasterCluster",1);
	$sock->SET_INFO("CyrusClusterPort","2005");
	$sock->SET_INFO("CyrusClusterID",1);	
	
	
}


?>