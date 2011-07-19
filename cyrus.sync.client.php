<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}


	
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{REPLICATE_YOUR_MAILBOXES}',"cyrus.clusters.php");
$prefix="CyrusSync_";
	
	$users=new usersMenus();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	
$html="
	var timeoutpage=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;

function {$prefix}demarre(){
	if(!YahooWin3Open()){return false;}
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=10-{$prefix}tant;
	if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ChargeLogs();
		{$prefix}demarre();
   }
}



function {$prefix}ChargeLogs(){
	LoadAjax('CyrsSynchroDiv','$page?status=yes');
	}	
	
	function CyrusCLusterReplicateLoadPage(){
		YahooWin3(750,'$page?popup=yes','$title');
		timeoutpage=0;
		setTimeout(\"StartReplicaLogs()\",900);
	
	}
	
	function StartReplicaLogs(){
		timeoutpage=timeoutpage+1;
		if(timeoutpage>10){alert('fatal error');return;}
		if(!document.getElementById('CyrsSynchroDiv')){
			setTimeout(\"StartReplicaLogs()\",900);
			return;
		}
		{$prefix}demarre();
		{$prefix}ChargeLogs();
	}
	

	
	CyrusCLusterReplicateLoadPage();

";
	
echo $html;	
	
}


function popup(){
	
	$html="<H1>{REPLICATE_YOUR_MAILBOXES}</H1>
	
	
	". RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto' id='CyrsSynchroDiv'></div>");
	
	$tpl=new templates();
	$sock=new sockets();
	$sock->getfile('CyrusMasterSyncClient');
	echo $tpl->_ENGINE_parse_body($html,"cyrus.clusters.php");
	
}

function status(){
	$logfile="/usr/share/artica-postfix/ressources/logs/sync_client.log";
	$tpl=new templates();
	if(!is_file($logfile)){
		echo $tpl->_ENGINE_parse_body("{scheduled}");
		exit;
	}
	
	$datas=file_get_contents($logfile);
	$tbl=explode("\n",$datas);
	
	$tbl=array_reverse($tbl,true);
	
while (list ($num, $ligne) = each ($tbl) ){
	if(trim($ligne)==null){continue;}
	echo "<div><code style='font-size:11px'>$ligne</code></div>\n";
	
}	
	
}

?>