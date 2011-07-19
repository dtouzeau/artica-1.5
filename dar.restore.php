<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.backup.inc');
	
		$user=new usersMenus();
	if($user->AsAnAdministratorGeneric==false){die('alert("no privileges")');}	
	
	if(isset($_GET["restore-file-id"])){popup_findfile();exit;}
	if(isset($_GET["restore-file-step2"])){popup_storage();exit;}
	if(isset($_GET["restore-file"])){restore_file();exit;}
	if(isset($_GET["GetStatus"])){restore_status();exit;}
	
	
	js();
function js(){

	
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{restore}');
	$page=CurrentPageName();
	$prefix=str_replace(".","_",$page);

	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;
	
	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;

		
		if(document.getElementById('restore-progress-number')){
			if(document.getElementById('restore-progress-number').value>99){
				{$prefix}finish();
				return false;
			}
		}
		
		if ({$prefix}tant < 5 ) {                           
		{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",900);
	      } else {
			{$prefix}tant = 0;
			{$prefix}LoadStatus();
			{$prefix}demarre(); 
			                              
	   }
	}

	var x_{$prefix}ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('restore_body').innerHTML=tempvalue;
	}	

	function {$prefix}LoadStatus(){
		var XHR = new XHRConnection();
		XHR.appendData('GetStatus','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChangeStatus);
	}


	function {$prefix}finish(){
		if(document.getElementById('wait')){
			document.getElementById('wait').innerHTML='';
		}
		
	}
	
	function {$prefix}_load(){
			YahooWin5('600','$page?restore-file-id={$_GET["fileid"]}','$title');
		
		}
		
	function DarRestoreStep2(id,db){
			YahooWin5('600','$page?restore-file-step2=yes&id='+id+'&db='+db,'$title');
		
		}	
		
	var x_FinalRestore= function (obj) {
		document.getElementById('restore_body').innerHTML=obj.responseText;
		{$prefix}demarre();
	}
			

		
	function FinalRestore(){
		var db=document.getElementById('db').value;
		if(db.length==0){
			alert('No database found');
			return;
		}
		
		var storage=document.getElementById('dar-external-storage-restore').value;
		if(storage.length>0){
			document.getElementById('restore_body').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			var XHR = new XHRConnection();
			XHR.appendData('restore-file','{$_GET["fileid"]}');
			XHR.appendData('restore-resource',storage);
			XHR.appendData('restore-db',db);
			XHR.sendAndLoad('$page', 'GET',x_FinalRestore);							
			}else{
				alert('no target specified');
			}
		}
	
	
	{$prefix}_load();
	";	
	
	echo $html;
	
}

function restore_file(){
	
	$sock=new sockets();
	$sock->getfile("DarRestorefile:{$_GET["restore-file"]};{$_GET["restore-resource"]};{$_GET["restore-db"]}");	
	restore_status();
	
}
	
	
	
function popup_findfile(){
	$id=$_GET["restore-file-id"];
	$sock=new sockets();
	$sock->getfile("DarFindFiles:$id");
	$datas=file_get_contents("ressources/logs/dar.find.$id.txt");
	$tbl=explode("\n",$datas);
	
	$htmlt="<table style='width:100%' class=table_form>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		
		
		if(preg_match('#\s+([0-9]+)\s+(.+)#',$ligne,$re)){
			$js="DarRestoreStep2('$id','{$re[1]}');";
			$htmlt=$htmlt."<tr ". CellRollOver($js).">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td>$re[2]</td>
			</tr>";
		}else{
			
		}
		
	}
	
	$htmlt=$htmlt . "</table>";
	
	$html="<H1>{restore}</H1>
	<p class=caption>{restore_choose_date}</p>
	<hr>
	$htmlt
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"dar.index.php");
	
	
	
}
	
	
	
	
	
	
function popup_storage(){
	$tpl=new templates();
	$page=CurrentPageName();
	

	
	$fileid=$_GET["id"];
	$db=$_GET["db"];
	
	$html="<H1>{restore}</H1>
	
	
	<input type='hidden' id='id' value='$fileid'>
	<input type='hidden' id='db' value='$db'>
	<p class=caption>{restore_file_text}</p>
	<hr>
	<table style='width:100%' class=table_form>
	<tr>
	<td class=legend>{resource}:</td>
	<td><input type='text' id='dar-external-storage-restore' value=''></td>
	</tr>
	</table>
	<div id='restore_body'>
	<input type='hidden' id='restore-progress-number' value='100'>
	<table style='width:100%'>
		<tr>
			<td valign='top'>" . Paragraphe('64-external-drive.png','{use_usb_storage}','{use_usb_storage_text}',
			"javascript:Loadjs('browse.usb.php?set-field=dar-external-storage-restore');")."</td>
			<td valign='top'>" . Paragraphe('64-network_drive.png','{use_network_storage}','{use_network_storage_text}',
			"javascript:Loadjs('smb.browse.php?set-field=dar-external-storage-restore');")."</td>
		</tr>
		<tr>
			<td valign='top'>" . Paragraphe('64-hd.png','{use_local_storage}','{use_local_storage_text}',
			"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=dar-external-storage-restore&protocol=yes');")."</td>
			</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:FinalRestore();\" value='{restore}&nbsp;&raquo;&raquo;'></td>
		</tr>	

		
	</table>
	</div>
	
	
	
	";
echo $tpl->_ENGINE_parse_body($html,'dar.index.php');	
	
}	
function Status($pourc,$text=null){
	if($text==null){$text="{scheduled}";}
	if($text<>null){$text="&nbsp;$text";}
$color="#5DD13D";

if($pourc>100){$pourc=100;$color="red";}

$html="
<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
</div>
	<div style='font-size:12px;font-weight:bold;text-align:center;'>
	&laquo;&laquo;<span style='font-size:12px;font-weight:bold;text-decoration:underline;'>$text</span>&raquo;&raquo;</div>
	<input type='hidden' id='restore-progress-number' value='$pourc'>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:FinalRestore();\" value='{restart}&nbsp;&raquo;'></div>
";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function restore_status(){
	if(!is_file("ressources/logs/exec.dar.find.restore.ini")){
		echo Status(10,"{waiting}...");
		return null;
	}
	$ini=new Bs_IniHandler("ressources/logs/exec.dar.find.restore.ini");
	echo Status($ini->get('STATUS','progress'),$ini->get('STATUS','text'));
}

?>