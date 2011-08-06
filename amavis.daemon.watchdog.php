<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.amavis.inc');
	include_once('ressources/class.sockets.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableAmavisWatchdog"])){Save();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_AMAVIS}::{watchdog_parameters}");
	$html="YahooWin5('550','$page?popup=yes','$title');";
	echo $html;
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$EnableAmavisWatchdog=$sock->GET_INFO("EnableAmavisWatchdog");
	$AmavisWatchdogMaxCPU=$sock->GET_INFO("AmavisWatchdogMaxCPU");
	$AmavisWatchdogKillProcesses=$sock->GET_INFO("AmavisWatchdogKillProcesses");
	if(!is_numeric($AmavisWatchdogMaxCPU)){$AmavisWatchdogMaxCPU=80;}
	if(!is_numeric($AmavisWatchdogKillProcesses)){$AmavisWatchdogKillProcesses=1;}
	if(!is_numeric($EnableAmavisWatchdog)){$EnableAmavisWatchdog=1;}
	
	$html="<div class=explain id='AmavisWatchdogSaveParamsId'>{amavis_watchdog_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td valign='top' class=legend>{enable_amavis_watchdog}:</div>
		<td valign='top'>". Field_checkbox("EnableAmavisWatchdog",1,$EnableAmavisWatchdog,"EnableAmavisWatchdogCheck()")."</td>
	</tR>
	<tr>
		<td valign='top' class=legend>{enable_kill_proc}:</div>
		<td valign='top'>". Field_checkbox("AmavisWatchdogKillProcesses",1,$AmavisWatchdogKillProcesses)."</td>
	</tR>		
	<tr>
		<td valign='top' class=legend>{MAX_CPU} %:</div>
		<td valign='top' style='font-size:14px'>". Field_text("AmavisWatchdogMaxCPU",$AmavisWatchdogMaxCPU,"style:font-size:14px;padding:3px;width:60px")."% CPU</td>
	</tR>			
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{apply}","AmavisWatchdogSaveParams()")."</td>
	</tr>
	</table>
	<script>
	
	var x_AmavisWatchdogSaveParams= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		document.getElementById('AmavisWatchdogSaveParamsId').innerHTML='';
		YahooWin5Hide();
	}		
	
	function AmavisWatchdogSaveParams(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableAmavisWatchdog').checked){XHR.appendData('EnableAmavisWatchdog',1);}else{XHR.appendData('EnableAmavisWatchdog',0);}
		if(document.getElementById('AmavisWatchdogKillProcesses').checked){XHR.appendData('AmavisWatchdogKillProcesses',1);}else{XHR.appendData('AmavisWatchdogKillProcesses',0);}
		
		XHR.appendData('max_servers',document.getElementById('AmavisWatchdogMaxCPU').value);
		document.getElementById('AmavisWatchdogSaveParamsId').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_AmavisWatchdogSaveParams());
		}		
		
	function EnableAmavisWatchdogCheck(){
		document.getElementById('AmavisWatchdogKillProcesses').disabled=true;
		document.getElementById('AmavisWatchdogMaxCPU').disabled=true;
		if(!document.getElementById('EnableAmavisWatchdog').checked){return;}
		document.getElementById('AmavisWatchdogKillProcesses').disabled=false;
		document.getElementById('AmavisWatchdogMaxCPU').disabled=false;	
	}
	
	</script>
	
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function Save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableAmavisWatchdog",$_GET["EnableAmavisWatchdog"]);
	$sock->SET_INFO("AmavisWatchdogKillProcesses",$_GET["AmavisWatchdogKillProcesses"]);
	$sock->SET_INFO("AmavisWatchdogMaxCPU",$_GET["AmavisWatchdogMaxCPU"]);
	
	
}