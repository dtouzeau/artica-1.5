<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.os.system.inc');
	
	if(!Isright()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["devHD"])){SaveDevHD();exit;}
	js();
	
	
	
function popup(){
	$page=CurrentPageName();
	$lvm=new lvm_org($_GET["ou"]);
	$array=$lvm->disklist;
	
	while (list ($num, $ligne) = each ($array) ){
		$devs[]=$num;
		$list[$num]=basename($num);
	}
	$default=basename($lvm->storage_enabled);
	
	$list[null]="{none}";
	$select=Field_array_Hash($list,"devHD",$default,null,null,0,"font-size:14px;padding:5px");	
	if($lvm->OuBackupStorageSubDir==null){$lvm->OuBackupStorageSubDir="users";}
	$html="
	<div id='div-savebackup'>
	<p style='font-size:12px;padding:3px'>
	{ACTIVATE_BACKUP_AGENT_EXPLAIN}
	</p>
	<p style='font-size:12px;padding:3px'>
	<i style='font-size:12px;padding:3px;font-weight:bold'></strong>{ACTIVATE_HD_ORG_DEFAULT_EXPLAIN}</strong></i>
	</p>
	<table style='width:100%'>
	<tr>
		<td class=legend>{your_storage}:</td>
		<td>$select</td>
	</tr>
	<tr>
		<td class=legend>{subdirectory}:</td>
		<td>". Field_text("OuBackupStorageSubDir",$lvm->OuBackupStorageSubDir,"font-size:14px;padding:5px",null)."</td>
	</tr>	
	
	
	<tr>
		<td colspan=2 align='right'>". button("{edit}","SaveBackupHDEnable()")."</td>
	</tr>
	</table>
	</div>
	<script>
	
		var x_SaveBackupHDEnable= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWinHide();	
		}	
	
		function SaveBackupHDEnable(){
			var XHR = new XHRConnection();
				XHR.appendData('ou','{$_GET["ou"]}');
				XHR.appendData('devHD',document.getElementById('devHD').value);
				XHR.appendData('OuBackupStorageSubDir',document.getElementById('OuBackupStorageSubDir').value);
				document.getElementById('div-savebackup').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
				XHR.sendAndLoad('$page', 'GET',x_SaveBackupHDEnable);
		
		}
	</script>
		
	
	
	";	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function SaveDevHD(){
	$lvm=new lvm_org($_GET["ou"]);
	$lvm->SaveEnabledStorage($_GET["devHD"]);
	
}
	
function js(){
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{ACTIVATE_BACKUP_AGENT}');
	$html="
		var mem_dev='';
		function BCK_OU_LOAD(){
			mem_dev='';
			YahooWin('450','$page?popup=yes&ou=$ou','$title')
		}
		
	BCK_OU_LOAD();";	
	
	echo $html;
}
	
	
function Isright(){
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if(!$users->AsOrgStorageAdministrator){return false;}
	if(isset($_GET["ou"])){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	
	return true;
	
	}
?>