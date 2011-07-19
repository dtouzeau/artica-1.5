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
	if($user->AsMailBoxAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		die();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}	
	if(isset($_GET["cyrus-brows-comp"])){list_ressources2();exit;}
	if(isset($_GET["RestoreFromRsyncPath"])){RestoreFromRsyncPath_popup();exit;}
	if(isset($_GET["viewlogs"])){viewlogs();exit;}
	if(isset($_GET["LaunExportNow"])){LaunExportNow();exit;}

js();
function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{perform_restore}','artica.backup.index.php');
	$settings=$tpl->_ENGINE_parse_body('{settings}');
	$CYR_BACKUP_NOW=$tpl->_ENGINE_parse_body('{CYR_BACKUP_NOW}');
	$load="{$prefix}LoadPage();";
	
	$mount=$_GET["automount"];
	
	if(!isset($_GET["automount"])){return null;}
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;
	var f_md='';	


	function {$prefix}LoadPage(){
		YahooLogWatcher(550,'$page?popup=yes&automount=$mount','$title');
	}
	
	function SelectMountRestoreLevel2(md,path){
		rempli=document.getElementById(md).innerHTML;
		if(rempli.length>100){
			document.getElementById(md).innerHTML='<hr>';
			return;
		}
	
		var XHR = new XHRConnection();
		f_md=md;
		XHR.appendData('cyrus-brows-comp',path);
		document.getElementById(md).innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SelectMountRestoreLevel2);
	}
	
		var x_SelectMountRestoreLevel2= function (obj) {
			var results=obj.responseText;
			document.getElementById(f_md).innerHTML=results;
			}		
	
	function CyrusBackupAddResourceFormWebPages(){
		var XHR = new XHRConnection();
		var default_schedule='0 0,3,12,19 * * *';
    	XHR.appendData('cyrus-ressource','{$_GET["add-automount"]}');
    	XHR.appendData('cyrus-schedule',default_schedule);
    	XHR.sendAndLoad('$page', 'GET',x_CyrusBackupAddResourceFormWebPages);
	}	
	
	function RestoreFromRsyncPath(path){
		RTMMail(600,'$page?RestoreFromRsyncPath=yes&path='+path,'$title');
		{$prefix}demarre();
		 setTimeout(\"{$prefix}ChargeLogs()\",1000);
	}
	
	function {$prefix}demarre(){
		if(!RTMMailOpen()){return;}
		
		{$prefix}tant = {$prefix}tant+1;
		if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",3000);
	      } else {
			{$prefix}tant = 0;
			{$prefix}ChargeLogs();
			{$prefix}demarre();                               
	   }
	}

var x_{$prefix}ChargeLogs= function (obj) {
			var results=obj.responseText;
			document.getElementById('cyrus-restore-events').innerHTML=results;
			}	
	
	function {$prefix}ChargeLogs(){
		if(!document.getElementById('cyrus-restore-events')){return;}
		var XHR = new XHRConnection();
		XHR.appendData('viewlogs',document.getElementById('cyrpath').value);
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs);
	}
	
	function {$prefix}ChargeLogsManu(){
		document.getElementById('cyrus-restore-events').innerHTML='';
		{$prefix}ChargeLogs();
	}
	
	var x_LaunchInmportNow= function (obj) {
		var results=obj.responseText;
		alert(results);
		{$prefix}ChargeLogsManu();
	}		
	
	function LaunchInmportNow(path){
		var XHR = new XHRConnection();
		alert('ok: '+path); 
		XHR.appendData('LaunExportNow',path);
		XHR.sendAndLoad('$page', 'GET',x_LaunchInmportNow);
		}
	
	
{$prefix}LoadPage();";
echo $html;
}

function list_ressources2(){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?B64-dirdir=". base64_encode($_GET["cyrus-brows-comp"]));
	$files=unserialize( base64_decode(trim($datas)) );

$html="
<table style='width:80%;margin-left:20px'>";
	
	
	if(is_array($files)){
	while (list ($num, $ligne) = each ($files) ){
			$md5=md5($ligne);
			
			$js="RestoreFromRsyncPath('$num')";
			$html=$html."
			<tr ". CellRollOver($js,"{RestoreFromRsyncPath}").">
				<td with=1%><img src='img/30-computer.png'>
				<td width=99%><span style='font-size:14px'>$ligne</td>
			</tr>	
			";
		
	}}	
	
		$html=$html."</table>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html,"automount.php");
	
}

function list_ressource($automount){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?B64-dirdir=". base64_encode("/automounts/$automount"));
	$files=unserialize(base64_decode(trim($datas)) );		
	if(!is_array($files)){
		$_GET["cyrus-brows-comp"]="/automounts/$automount";
		list_ressources2();
		return;
	}
	
	$html="<table style='width:80%'>";
	
	
	if(is_array($files)){
	while (list ($num, $ligne) = each ($files) ){
		if(!preg_match("#backup\.[0-9\-]+#",$ligne)){		continue;}
			$md5=md5($num);
			$ligne=str_replace("backup.","",$ligne);
			$js="SelectMountRestoreLevel2('$md5','$num')";
			$html=$html."
			<tr ". CellRollOver($js,"{select_this_container}").">
				<td with=1%><img src='img/folder-32-sh	are.png'>
				<td width=99%><span style='font-size:14px'>$ligne</td>
			</tr>
			<tr>
				<td colspan=2><div id='$md5'><hr></div></td>
			</tR>
					
			";
		
	}}
	
	
	$html=$html."</table>";
	
	return $html;
	

}

function RestoreFromRsyncPath_popup(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);	
	$path=$_GET["path"];
	$dir=basename($path);
	$html="<H1>{perform_restore}::$dir</H1>
	<input type='hidden' id='cyrpath' value='{$path}'>
		<table style='width:100%'>
		<tr>	
			<td valign='top' width=1%><img src='img/64-infos.png'></td>
			<td valign='top'><p style='color:red;font-size:13px;font-weight:bold'>{RestoreFromRsyncPath_warn}</p></td>
		</tr>
		<tr>	
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:LaunchInmportNow('$path');\" value='{RestoreFromRsyncPathNow}&nbsp;&raquo;'></td>
		</tr>
		</table>
		<br>
		<div style='text-align:right'><code style='font-size:11px'>$path</code>&nbsp;|&nbsp;". texttooltip("{refresh}","{refresh}","{$prefix}ChargeLogsManu()")."</div>
		". RoundedLightWhite("<div id='cyrus-restore-events' style='width:100%;height:250px;overflow:auto'></div>")."
		";
	
			
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'artica.backup.index.php,index.troubleshoot.php,cyrus.backup.php');	
			
}	

	
function popup(){
	
	//$add=Paragraphe('disk-backup-64-add.png','{CYRUS_ADD_RESOURCES}','{CYRUS_ADD_RESOURCES_TEXT}',"javascript:Loadjs('automount.php?field=cyrus-ressource');");
	$resources=list_ressource($_GET["automount"]);
	
		
		$html="<H1>{perform_restore}::{$_GET["automount"]}</H1>
	<p class=caption>{import_artica_settings_text}</p>
		<table style='width:100%'>	
		<tr>
			<td valign='top' width=1%><img src='img/64-import.png'></td>
		<td valign='top'>
		
		". RoundedLightWhite("<div id='cyrus-li		st-res' style='width:100%;height:350px;overflow:auto'>$resources</div>")."
		
	</td>
	</tr>
	</table>";
		
	
	$tpl=new templates();	
	echo $tpl->_ENGINE_parse_body($html,'artica.backup.index.php,index.troubleshoot.php,automount.php');
	}
		
function viewlogs(){
	$path=$_GET["viewlogs"];
	$pf=md5($path);	
	$filename="artica-restore-$pf.debug";
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?view-file-logs=$filename");
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		echo "<div><code style='font-size:10px'>".htmlentities($ligne)."</code></div>";
		
	}
	
	
}

function LaunExportNow(){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?ExecuteImportationFrom={$_GET["LaunExportNow"]}");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{success}");
}
	
?>