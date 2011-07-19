<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ini.inc');	

if(isset($_GET["EnableBackupAccount"])){backup_save();exit;}
if(isset($_GET["popup-index"])){backup_index();exit;}
if(isset($_GET["addComputerShareFolder"])){addComputerShareFolder();exit;}
if(isset($_GET["shareslist"])){getSharelist();exit;}
if(isset($_GET["SaveSchedule"])){SaveSchedule();exit;}
if(isset($_GET["DelComputerShareFolder"])){DelComputerShareFolder();exit;}


js();

function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$uid=$_GET["uid"];
$title=$tpl->_ENGINE_parse_body('{BACKUP}::'.$uid);
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="


function {$prefix}Loadpage(){
	YahooWin3('650','$page?popup-index=$uid','$title');
	setTimeout('{$prefix}shareslist()',900);
	}
	
var x_SaveComputerBackupInfo= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}Loadpage()		
}		

function {$prefix}shareslist(){
	LoadAjax('shareslist','$page?shareslist=yes&uid={$_GET["uid"]}');
}
	
	
function SaveComputerBackupInfo(){
	var uid='{$_GET["uid"]}';
	var EnableBackupAccount=document.getElementById('EnableBackupAccount').value;
	var XHR = new XHRConnection();
	XHR.appendData('uid',uid);
	XHR.appendData('EnableBackupAccount',EnableBackupAccount);
	XHR.appendData('enable_smb',document.getElementById('enable_smb').value);
	XHR.appendData('username',document.getElementById('username').value);
	XHR.appendData('password',document.getElementById('password').value);
	document.getElementById('backupcomputerform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
	XHR.sendAndLoad('$page', 'GET',x_SaveComputerBackupInfo);
	}
	
function addComputerShareFolder(){
	var uid='{$_GET["uid"]}';
	var XHR = new XHRConnection();
	XHR.appendData('uid',uid);
	XHR.appendData('addComputerShareFolder',document.getElementById('share_folder').value);
	document.getElementById('backupcomputerform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
	XHR.sendAndLoad('$page', 'GET',x_SaveComputerBackupInfo);	
}

function SaveSchedule(schedule){
	var uid='{$_GET["uid"]}';
	var XHR = new XHRConnection();
	XHR.appendData('uid',uid);
	XHR.appendData('SaveSchedule',schedule);
	document.getElementById('backupcomputerform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
	XHR.sendAndLoad('$page', 'GET',x_SaveComputerBackupInfo);	
}

function ComputerDeleteBackupPath(path){
	var uid='{$_GET["uid"]}';
	var XHR = new XHRConnection();
	XHR.appendData('uid',uid);
	XHR.appendData('DelComputerShareFolder',path);
	document.getElementById('backupcomputerform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
	XHR.sendAndLoad('$page', 'GET',x_SaveComputerBackupInfo);	
}
	

	
 {$prefix}Loadpage();

";
	
	echo $html;
}


function backup_index(){
	
	
	$ini=new Bs_IniHandler();
	
	
$computer=new computers($_GET["popup-index"].'$');
$ini->loadString($computer->ComputerCryptedInfos);


$text="<div><code>$computer->ComputerIP</code></div>
	<div><code>$computer->ComputerMacAddress</code></div>
	<div><code>$computer->ComputerOS</code></div>
	<div><code>$computer->ComputerMachineType</code></div>";
$computer_icon=Paragraphe("64-computer.png","$computer->DisplayName",$text);
$schedule_icon=Paragraphe("64-planning.png",'{SET_SCHEDULE}','{SET_SCHEDULE_TEXT}',"javascript:Loadjs('cron.php?function=SaveSchedule&field=schedule')");

$enable_backup=Paragraphe_switch_img_32('{enable_backup}','{enable_backup_text}','EnableBackupAccount',$computer->EnableBackupAccount,"{enable_disable}",300);

$browse=button_browse_computer($_GET["popup-index"],"share_folder");


$html="
<H1>{BACKUP}::&nbsp;$computer->DisplayName</H1>
<input type='hidden' id='schedule' value='{$ini->_params["SCHEDULE"]["cron"]}'>
<table style='width:100%'>
<tr>
	<td valign='top'>
		$computer_icon
		$schedule_icon
		
		<div id='shareslist' style='width:100%;height:120px;overflow:auto'></div>	
	</td>
	<td valign='top'>
	<div id='backupcomputerform'>
		$enable_backup
		
<table style='width:100%' class=table_form>
		<tr>
			<tr>
			<td class=legend>{add_share}:</td>
			<td>". Field_text("share_folder",null,"width:120px")."&nbsp;$browse</td>
			<td><input type='button' OnClick=\"addComputerShareFolder();\" value='{add}&nbsp;&raquo;'></td>
		</tr>		
		</table>	
		
		
		
		<table style='width:100%' class=table_form>
		<tr>
			<td colspan=2><h3>{remote_smb_share}</h3></td>			
		</tr>
		</tr>
		<tr>
			<td class=legend>{enable}:</td>
			<td>" . Field_checkbox("enable_smb",1,$ini->_params["BACKUP_PROTO"]["enable_smb"])."</td>
		</tr>		
		<tr>
			<td class=legend>{username}:</td>
			<td>". Field_text("username",$ini->_params["ACCOUNT"]["USERNAME"],"width:120px")."</td>
		</tr>
		<tr>
			<td class=legend>{password}:</td>
			<td>". Field_password("password",$ini->_params["ACCOUNT"]["PASSWORD"],"width:120px")."</td>
		</tr>		
		</table>
		
	<hr>
	<div style='width:100%;text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveComputerBackupInfo();\"></div>
	</div>
	</td>
</tr>
</table>

";


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function backup_save(){
	$EnableBackupAccount=$_GET["EnableBackupAccount"];
	$computer=new computers($_GET["uid"].'$');
	$computer->EnableBackupAccount=$EnableBackupAccount;
	$computer->save_EnableBackupAccount();
	
	$ini=new Bs_IniHandler();
	$ini->_params["BACKUP_PROTO"]["enable_smb"]=$_GET["enable_smb"];
	$ini->_params["ACCOUNT"]["USERNAME"]=$_GET["username"];
	$ini->_params["ACCOUNT"]["PASSWORD"]=$_GET["password"];
	$computer->ComputerCryptedInfos=$ini->toString();
	$computer->SaveCryptedInfos();
	
	
}

function addComputerShareFolder(){
		$computer=new computers($_GET["uid"].'$');
		$ini=new Bs_IniHandler();
		$ini->loadString($computer->ComputerCryptedInfos);
		$ini->_params["share:".md5($_GET["addComputerShareFolder"])]["path"]=$_GET["addComputerShareFolder"];
		$computer->ComputerCryptedInfos=$ini->toString();
		$computer->SaveCryptedInfos();
	
}

function DelComputerShareFolder(){
		$computer=new computers($_GET["uid"].'$');
		$ini=new Bs_IniHandler();
		$ini->loadString($computer->ComputerCryptedInfos);
		unset($ini->_params["share:".md5($_GET["DelComputerShareFolder"])]);
		$computer->ComputerCryptedInfos=$ini->toString();
		$computer->SaveCryptedInfos();	
	
}

function SaveSchedule(){
		$computer=new computers($_GET["uid"].'$');
		$ini=new Bs_IniHandler();
		$ini->loadString($computer->ComputerCryptedInfos);
		$ini->_params["SCHEDULE"]["cron"]=$_GET["SaveSchedule"];
		$computer->ComputerCryptedInfos=$ini->toString();
		$computer->SaveCryptedInfos();	
	
}

function getSharelist(){
	$computer=new computers($_GET["uid"].'$');
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	if(!is_array($ini->_params)){return null;}
		while (list ($num, $line) = each ($ini->_params)){
			
			
			if(preg_match("#share:(.+)#",$num)){
				
				$path=$line["path"];
				$src_path=$path;
				if(strlen($path)>30){$path=texttooltip(substr($path,0,27),$path,null,null,0,"font-size:10px")."...";}
				
				
				$html=$html . 
				
				"<tr ". CellRollOver().">
				<td valign='top' width=1%><img src='img/fw_bold.gif'>
				<td><code style='font-size:10px'>$path</code></td>
				<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","ComputerDeleteBackupPath('$src_path')")."</td>
				</tr>";
				
			}
			
			
		}

$html="<table class=table_form>$html</table>";		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}




?>