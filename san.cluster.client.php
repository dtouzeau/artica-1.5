<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.nfs.inc");


	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SaveCluster"])){SaveCluster();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["SaveClusterClientIMAP"])){SaveClusterClientIMAP();exit;}
	
js();

function js(){
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{SAN_CLUSTER_CLIENT}','san.cluster.php');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	

	
function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin2Open()){return false;}
	
	if ({$prefix}tant < 15 ) {                           
	{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",2000);
      } else {
		{$prefix}tant = 0;
		{$prefix}Status();
		{$prefix}demarre(); 
		                              
   }
}		

	function {$prefix}LoadPage(){
		YahooWin2(650,'$page?popup=yes','$title');
		{$prefix}demarre();
	}
	
	function {$prefix}Status(){
		LoadAjax('clientClusterLogs','$page?status=yes');
	}
	
var x_ConnectClusterMail= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}LoadPage();
	}	
	
	function ConnectClusterMail(){
	var XHR = new XHRConnection();
	XHR.appendData('SaveClusterClientIMAP',document.getElementById('imap-server').value);
	XHR.appendData('artica_listen_port',document.getElementById('artica_listen_port').value);
	document.getElementById('clusterconf').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_ConnectClusterMail);		
	}
	
	
		
	
	{$prefix}LoadPage();
";	

	echo $html;
}



function popup(){
	
	$sock=new sockets();
	$users=new usersMenus();
	
	$ClusterClientToArticaIMAPPort=$sock->GET_INFO("ClusterClientToArticaIMAPPort");
	if($ClusterClientToArticaIMAPPort==null){$ClusterClientToArticaIMAPPort="9000";}
	if($users->cyrus_imapd_installed){
		
		$imap="
		
		<table style='width:100%'>
			<tr><td valign='top'><img src='img/mail-flag-64.png'></td>
		<td valign='top'><H3>{APP_CYRUS_IMAP}</H3>
		<table style='width:100%'>
		<tr>
			<td class=legend nowrap valign='top'>{cluster_server}:</td>
			<td valign='top'>". Field_text('imap-server',$sock->GET_INFO("ClusterClientTo"))."</td>
		</tr>
		<tr>
			<td class=legend nowrap valign='top'>{artica_listen_port}:</td>
			<td valign='top'>". Field_text('artica_listen_port',$ClusterClientToArticaIMAPPort)."</td>
		</tr>		
		<tr>
			<td colspan=2 align='right'>	
				<hr><input type='button' OnClick=\"javascript:ConnectClusterMail();\" value='{connect}&nbsp;&raquo;'>
			</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		
		";
		$imap=RoundedLightWhite($imap);
	}
	
	
	$html="<H1>{SAN_CLUSTER_CLIENT}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/network-cluster-client-128.png'></td>
	<td valign='top'>
	<p class=caption>{SAN_CLUSTER_CLIENT_TEXT}</p>
	<div id='clusterconf'>
		$imap
		</div>
	</td>
	</tr>
	</table>
	<div id='clientClusterLogs'></div>
";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'san.cluster.php');
	
}

function status(){
	
}

function SaveClusterClientIMAP(){
	$sock=new sockets();
	$sock->SET_INFO('ClusterClientTo',$_GET["SaveClusterClientIMAP"]);
	$sock->SET_INFO('ClusterClientToArticaIMAPPort',$_GET["artica_listen_port"]);
}












?>