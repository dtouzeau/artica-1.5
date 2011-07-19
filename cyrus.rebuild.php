<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["rebuild"])){rebuild();exit;}
	if(isset($_GET["restart"])){rebuild();exit;}
	
	

	
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{REBUILD_CYRUS}');

	
	$users=new usersMenus();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	
$html="
	var rebuild_timeout=0;
	var tmpresrebuildcyrus='';
	function CyrusRebuildLoadPage(){
		RTMMail(700,'$page?popup=yes','$title');
		setTimeout(\"CyrusRebuilStart()\",500);
	
	}
	
var X_CyrusRestart= function (obj) {
	var results=obj.responseText;
	document.getElementById('cyrusRebuild').innerHTML=tmpresrebuildcyrus+results;
	tmpresrebuildcyrus=results;	
}
	
var X_CyrusRebuilStart= function (obj) {
	var results=obj.responseText;
	document.getElementById('cyrusRebuild').innerHTML=results;
	tmpresrebuildcyrus=results;
	var XHR = new XHRConnection();
	XHR.appendData('restart','yes');
	document.getElementById('cyrusRebuild').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',X_CyrusRestart);		
	}		
	
function CyrusRebuilStart(){
		rebuild_timeout=rebuild_timeout+1;
		if(rebuild_timeout>10){return;}
		if(!document.getElementById('cyrusRebuild')){setTimeout(\"CyrusRebuilStart()\",500);return;}
		var XHR = new XHRConnection();
		XHR.appendData('rebuild','yes');
		document.getElementById('cyrusRebuild').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_CyrusRebuilStart);	
		
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
		document.getElementById('wizard_zone').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_EnableCyrusMasterCluster);	
	
	
	}
	
	
	CyrusRebuildLoadPage();

";
	
echo $html;	
	
}

function popup(){
	
	$html="
	<H1>{REBUILD_CYRUS}</H1>
	<p class=caption>{REBUILD_CYRUS_TEXT}</p>
	" . RoundedLightWhite("<div id='cyrusRebuild' style='width:100%;height:300px;overflow:auto'></div>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function rebuild(){
	$sock=new sockets();
	if(isset($_GET["rebuild"])){$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?reconfigure-cyrus-debug=yes')));}
	if(isset($_GET["restart"])){$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?restart-cyrus-debug=yes')));}
	
	
	if(!is_array($tbl)){return null;}
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne==null)){continue;}
		$html=$html . "<div><code style='font-size:11px'>". htmlspecialchars($ligne)."</div>\n";
		
		
	}
	
	
	echo $html;
	
	
}


?>