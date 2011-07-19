<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.autofs.inc');
	include_once('ressources/class.computers.inc');

	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["virusevents"])){echo viruslist();exit;}
	if(isset($_GET["VirsEventsAsRead"])){VirsEventsAsRead();exit;}
	if(isset($_GET["VirusEventsAllAsRead"])){VirusEventsAllAsRead();exit;}
	

js();
function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{VIRUSES_FOUND}');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		RTMMail(850,'$page?popup=yes','$title');
	}
	
	
	

	
	
var x_VirsEventsAsRead=function (obj) {
	LoadAjax('virusevents','$page?virusevents=yes');
	}	
	
	function VirusEventsAsRead(id){
    	var XHR = new XHRConnection();
    	XHR.appendData('VirsEventsAsRead',id);
 		document.getElementById('virusevents').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_VirsEventsAsRead);
	}
	
	function VirusEventsAllAsRead(){
	var XHR = new XHRConnection();
    	XHR.appendData('VirusEventsAllAsRead','yes');
 		document.getElementById('virusevents').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_VirsEventsAsRead);
	}
	


	{$prefix}LoadPage();";

	echo $html;
	}
	

	
	
function popup(){

	
	$virslist=viruslist();
	$html="<H1>{VIRUS_EVENTS}</H1>
	<div style='text-align:right;margin-top:-25px'><input type='button' OnClick=\"javascript:VirusEventsAllAsRead();\" value=\"{mark_all_events_has_read}\"></div>
	". RoundedLightWhite("
	<div id='virusevents' style='width:100%;height:400px;overflow:auto'>$virslist</div>
	");
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function VirsEventsAsRead(){
		$q=new mysql();
		$sql="UPDATE antivirus_events SET email=1 WHERE ID={$_GET["VirsEventsAsRead"]}";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			echo $q->mysql_error;
		}
	
}

function VirusEventsAllAsRead(){
		$q=new mysql();
		$sql="UPDATE antivirus_events SET email=1 WHERE email=0";
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			echo $q->mysql_error;
		}	
}
	
function viruslist(){

	$q=new mysql();
	$sql="SELECT * FROM antivirus_events WHERE email=0 ORDER BY zDate DESC";
	$results=$q->QUERY_SQL($sql,"artica_events");	
	
	$html="<table style='width:99%'>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{virusname}</th>
		<th>{path}</th>
		<th>{taskname}</th>
		<th>&nbsp;</th>
	</tr>
		";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$len=strlen("{$ligne["InfectedPath"]}");
		if($len>65){$ligne["InfectedPath"]=texttooltip(substr($ligne["InfectedPath"],0,62).'...',$ligne["InfectedPath"],null,null,1);}
	
		$html=$html . "<tr . " . CellRollOver().">
			<td width=1%><img src='img/danger16.png'></td>
			<td nowrap width=1%><strong>{$ligne["zDate"]}</strong></td>
			<td nowrap><strong>{$ligne["VirusName"]}</strong></td>
			<td nowrap><strong>{$ligne["InfectedPath"]}</strong></td>
			<td nowrap width=1%><strong>{$ligne["TaskName"]}</strong></td>
			<td width=1%>" . imgtootltip("goodmail.gif","{mark_has_read}","VirusEventsAsRead('{$ligne["ID"]}')")."</td>
			</tr>
			
			";
		
	}
		
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}
?>