<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ini.inc');	

	
	if(isset($_GET["popup-index"])){index();exit;}
	if(isset($_GET["EnableBackupAccount"])){SaveInfos();exit;}

js();




function js(){
	$page=CurrentPageName();
	$prefix=str_replace(".","_",$page);
	$tpl=new templates();
	$uid=$_GET["uid"];
	$title=$tpl->_ENGINE_parse_body($uid.'::{backup_parameters}');	
	$allow=true;	
	$usersprivs=new usersMenus();
	if(!$usersprivs->AsAnAdministratorGeneric){
			if(!$usersprivs->AllowEditAliases){
				$allow=false;
				}
		}
	if(!$_GET["uid"]){$allow=false;}	
	
	$tpl=new templates();	
	if(!$allow){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	
$html="

function {$prefix}Loadpage(){
	YahooWin4('600','$page?popup-index=yes&uid=$uid','$title');
	}


function SaveBackupUserInfos(){
	var EnableBackupAccount=document.getElementById('EnableBackupAccount').value;
	var RsyncBackupTargetPath=document.getElementById('RsyncBackupTargetPath').value;
	var XHR = new XHRConnection();
	document.getElementById('{$uid}_divid').src='img/wait_verybig.gif';
    XHR.appendData('EnableBackupAccount',EnableBackupAccount);
    XHR.appendData('RsyncBackupTargetPath',RsyncBackupTargetPath);
    
    
    XHR.appendData('uid','$uid');
    XHR.sendAndLoad('$page', 'GET',x_SaveBackupUserInfos); 

}

	
var x_SaveBackupUserInfos= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}Loadpage();
	}
		


{$prefix}Loadpage();";	
	
	
echo $html;	
}

function SaveInfos(){
	$ct=new user($_GET["uid"]);
	$ct->EnableBackupAccount=$_GET["EnableBackupAccount"];
	$ct->RsyncBackupTargetPath=$_GET["RsyncBackupTargetPath"];
	$ct->save_EnableBackupAccount();
	}


function index(){
$ct=new user($_GET["uid"]);
$sock=new sockets();
$RsyncStoragePath=$sock->GET_INFO("RsyncStoragePath");
if($RsyncStoragePath==null){$RsyncStoragePath="/var/spool/rsync";}		
if($ct->RsyncBackupTargetPath==null){$ct->RsyncBackupTargetPath=$RsyncStoragePath;}


$RsyncBackupTargetPath=Field_text("RsyncBackupTargetPath",$ct->RsyncBackupTargetPath,"width:220px").button_browse("RsyncBackupTargetPath");
$EnableBackupAccount=Field_numeric_checkbox_img("EnableBackupAccount",$ct->EnableBackupAccount);




$html="<H1>{backup_parameters}</H1>
<div id='{$ct->uid}_divid'>
" . RoundedLightWhite("
<table style='width:100%'>
<tr>
<td valign='top'>
	<center><img src='img/bg_backup-org.png'></center><br>
	<table style='width:100%' class=table_form>
	<tr>
	<td class=legend nowrap valign='top'>{ENABLE_RSYNC_ACCOUNT}:</td>
	<td>$EnableBackupAccount<p class=caption>{ENABLE_RSYNC_ACCOUNT_TEXT}</p></td>
	</tr>
	<tr>
	<td class=legend>{storage_path}:</td>
	<td>$RsyncBackupTargetPath</td>
	</tr>	
	<tr>
		<td valign='top' align='right' colspan=2><hr>
			<input type='button' OnClick=\"javascript:SaveBackupUserInfos();\" value='&nbsp;&nbsp;{edit}&nbsp;&raquo;&nbsp;&nbsp;'>
		</td>
	</tr>
</table>
");

$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);	
	
}
?>