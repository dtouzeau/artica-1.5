<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.backup.inc');
	include_once('ressources/class.autofs.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["automount-list"])){popup_resource_automount_list();exit;}
	if(isset($_GET["popup-ressource"])){popup_resource();exit;}
	if(isset($_GET["popup-schedule"])){popup_schedule();exit;}
	if(isset($_GET["W_RESOURCE"])){$_SESSION["WIZARD"]["W_RESOURCE"]=$_GET["W_RESOURCE"];exit;}
	if(isset($_GET["W_UUID"])){$_SESSION["WIZARD"]["W_UUID"]=$_GET["W_UUID"];exit;}
	if(isset($_GET["W_LOCALDIR"])){$_SESSION["WIZARD"]["W_LOCALDIR"]=$_GET["W_LOCALDIR"];exit;}
	
	
	
	if(isset($_GET["W_SMB_SERVER"])){
		$_SESSION["WIZARD"]["W_SMB_SERVER"]=$_GET["W_SMB_SERVER"];
		$_SESSION["WIZARD"]["W_SMB_USERNAME"]=$_GET["W_SMB_USERNAME"];
		$_SESSION["WIZARD"]["W_SMB_PASSWORD"]=$_GET["W_SMB_PASSWORD"];
		$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]=$_GET["W_SMB_SHAREDDIR"];
		exit;
		}
		
	if(isset($_GET["W_AUTOMOUNT_DIR"])){
		$_SESSION["WIZARD"]["W_AUTOMOUNT_DIR"]=$_GET["W_AUTOMOUNT_DIR"];
		exit;
	}
		
	if(isset($_GET["CRON_DAYS"])){
		$_SESSION["WIZARD"]["CRON_DAYS"]=$_GET["CRON_DAYS"];
		$_SESSION["WIZARD"]["CRON_HOURS"]=$_GET["CRON_HOURS"];
		$_SESSION["WIZARD"]["CRON_MIN"]=$_GET["CRON_MIN"];
		$_SESSION["WIZARD"]["CRON_CONTAINER"]=$_GET["CRON_CONTAINER"];
		
		
		exit;
		}		
		
	
	if(isset($_GET["WIZARD_CANCEL"])){WIZARD_CANCEL();exit;}
	if(isset($_GET["BACKUP_COMPILE"])){BACKUP_COMPILE();exit;}
	
	if(isset($_GET["SMTP_DOM"])){
		$_SESSION["WIZARD"]["SMTP_DOM"]=$_GET["SMTP_DOM"];
		$_SESSION["WIZARD"]["MAILBOX_IP"]=$_GET["MAILBOX_IP"];
		exit;
	}
	
	if(isset($_GET["SMTP_NET"])){$_SESSION["WIZARD"]["SMTP_NET"]=$_GET["SMTP_NET"];exit;}
	if(isset($_GET["popup-finish"])){popup_finish();exit;}
	if(isset($_GET["COMPILE"])){COMPILE();exit;}
	
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{WIZARD_BACKUP}");
	$WIZARD_CONFIGURE_RESOURCE=$tpl->_ENGINE_parse_body("{WIZARD_BACKUP_CHOOSE_STORAGE}");
	$WIZARD_COMPILE=$tpl->_ENGINE_parse_body("{WIZARD_COMPILE}");
	$WIZARD_CONFIGURE_SCHEDULE=$tpl->_ENGINE_parse_body("{WIZARD_CONFIGURE_SCHEDULE}");
	$WIZARD_FINISH=$tpl->_ENGINE_parse_body("{WIZARD_FINISH}");
	
	$html="
	
		function WizardBackupLoad(){YahooWin(650,'$page?popup=yes','$title');}
		function WizardRessourceShow(){YahooWin(650,'$page?popup-ressource=yes','$WIZARD_CONFIGURE_RESOURCE');}
		function WizardScheduleShow(){YahooWin(650,'$page?popup-schedule=yes','$WIZARD_CONFIGURE_SCHEDULE');}
		function WizardFinish(){YahooWin(650,'$page?popup-finish=yes','$WIZARD_FINISH');}
		

		
		function CancelBackupWizard(){
			YahooWinHide();
			var XHR = new XHRConnection();
			XHR.appendData('WIZARD_CANCEL','yes');
			XHR.sendAndLoad('$page', 'GET');			
		}
		
	var x_WizardRessource= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		WizardRessourceShow();
	 }	
	 
	var x_WizardUSBSaveRessource= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		WizardScheduleShow();
	 }	

	var x_WizardBackupScheduleSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		WizardFinish();
	 }

	 var x_WizardBackupCompile= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){
			alert(tempvalue);
			return;
			}
		WizardBackupLoad();
		if(document.getElementById('main_config_backup_tasks')){RefreshTab('main_config_backup_tasks');}
	 }



function CloseTimeOut(){
		Loadjs('domains.manage.org.index.php?js=yes&ou='+document.getElementById('ou').value);
	 	YahooWinHide();
	 	
	}

	function WizardRessource(){
			var XHR = new XHRConnection();
			var storage=document.getElementById('storage').value;
			if(storage.length>1){
				XHR.appendData('W_RESOURCE',storage);
				XHR.sendAndLoad('$page', 'GET',x_WizardRessource);
			}
		}
		
	function WizardUSBSaveRessource(){
		var XHR = new XHRConnection();
		var UUID=document.getElementById('UUID').value;
		if(UUID.length>1){
				XHR.appendData('W_UUID',UUID);
				XHR.sendAndLoad('$page', 'GET',x_WizardUSBSaveRessource);
			}
	}
	
	function WizardSMBSaveRessource(){
		var XHR = new XHRConnection();
		var W_SMB_SERVER=document.getElementById('W_SMB_SERVER').value;
		if(W_SMB_SERVER.length>1){
				XHR.appendData('W_SMB_SERVER',W_SMB_SERVER);
				XHR.appendData('W_SMB_USERNAME',document.getElementById('W_SMB_USERNAME').value);
				XHR.appendData('W_SMB_PASSWORD',document.getElementById('W_SMB_PASSWORD').value);
				XHR.appendData('W_SMB_SHAREDDIR',document.getElementById('W_SMB_SHAREDDIR').value);
				XHR.sendAndLoad('$page', 'GET',x_WizardUSBSaveRessource);
			}
	}	
	
	
	function WizardAutomountSaveRessource(dir){
		var XHR = new XHRConnection();
		XHR.appendData('W_AUTOMOUNT_DIR',dir);
		XHR.sendAndLoad('$page', 'GET',x_WizardUSBSaveRessource);
	}		

	function WizardBackupScheduleSave(){
		var XHR = new XHRConnection();
		var CRON_DAYS=document.getElementById('CRON_DAYS').value;
		if(CRON_DAYS.length>0){
				XHR.appendData('CRON_DAYS',CRON_DAYS);
				XHR.appendData('CRON_HOURS',document.getElementById('CRON_HOURS').value);
				XHR.appendData('CRON_MIN',document.getElementById('CRON_MIN').value);
				XHR.appendData('CRON_CONTAINER',document.getElementById('CRON_CONTAINER').value);
				
				
				XHR.sendAndLoad('$page', 'GET',x_WizardBackupScheduleSave);
			}
	}

	
	function WizardBackupCompile(){
		var XHR = new XHRConnection();
		XHR.appendData('BACKUP_COMPILE','yes');
		XHR.sendAndLoad('$page', 'GET',x_WizardBackupCompile);
	}
	
	

		  
	
	
	WizardBackupLoad();";
	
	echo $html;
}	


function popup_finish(){
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'></td>
		<td valign='top'>
		<div style='color:rgb(194, 51, 2);font-size:13px'>{WIZARD_BACKUP_CHOOSE_STORAGE}: {$_SESSION["WIZARD"]["W_RESOURCE"]}</div>";
		
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{usb_external_drive}: {$_SESSION["WIZARD"]["W_UUID"]}</div>";break;
		case "smb":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{remote_smb_server}: \\\\{$_SESSION["WIZARD"]["W_SMB_SERVER"]}\\{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		case "rsync":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{remote_smb_server}: rsync://{$_SESSION["WIZARD"]["W_SMB_SERVER"]}/{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		case "automount":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{automount_ressource}: {$_SESSION["WIZARD"]["W_AUTOMOUNT_DIR"]}</div>";break;
		case "local":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{local_directory}: {$_SESSION["WIZARD"]["W_LOCALDIR"]}</div>";break;
		default:
			;
		break;
	}

	$cron=new cron_macros();
	$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{run_every_day}:{$cron->cron_days[$_SESSION["WIZARD"]["CRON_DAYS"]]} {day}; {time} {$cron->cron_hours[$_SESSION["WIZARD"]["CRON_HOURS"]]}:{$cron->cron_mins[$_SESSION["WIZARD"]["CRON_MIN"]]}</div>";
	$html=$html."<hr>
<H3 style='color:rgb(194, 51, 2);font-size:16px'>{wizardCompileButton}</H3>
	<div class=explain>{wizardCompileButton_text}</div>
	<hr>
		<center>
			". button("{wizardCompileButton}","WizardBackupCompile()")."
		</center>
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardScheduleShow()")."</td>
			<td width=50% align='right'>". button("{wizardCompileButton}","WizardBackupCompile()")."</td>
		</tr>
	</table>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"wizard.kaspersky.appliance.php");	
}	
	

function popup_schedule(){
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'></td>
		<td valign='top'>
		<div style='color:rgb(194, 51, 2);font-size:13px'>{WIZARD_BACKUP_CHOOSE_STORAGE}: {$_SESSION["WIZARD"]["W_RESOURCE"]}</div>";
		
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{usb_external_drive}: {$_SESSION["WIZARD"]["W_UUID"]}</div>";break;
		case "smb":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{remote_smb_server}: \\\\{$_SESSION["WIZARD"]["W_SMB_SERVER"]}\\{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		case "rsync":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{remote_smb_server}: rsync://{$_SESSION["WIZARD"]["W_SMB_SERVER"]}/{$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"]}</div>";break;
		case "automount":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{automount_ressource}: {$_SESSION["WIZARD"]["W_AUTOMOUNT_DIR"]}</div>";break;
		case "local":$html=$html."<div style='color:rgb(194, 51, 2);font-size:13px'>{local_directory}: {$_SESSION["WIZARD"]["W_LOCALDIR"]}</div>";break;
		default:;
		break;
	}		


	$cron=new cron_macros();
	$days=Field_array_Hash($cron->cron_days,"CRON_DAYS",$_SESSION["WIZARD"]["CRON_DAYS"],null,null,0,"font-size:14px;padding:5px;");	
	$hours=Field_array_Hash($cron->cron_hours,"CRON_HOURS",$_SESSION["WIZARD"]["CRON_HOURS"],null,null,0,"font-size:14px;padding:5px;");
	$mins=Field_array_Hash($cron->cron_mins,"CRON_MIN",$_SESSION["WIZARD"]["CRON_MIN"],null,null,0,"font-size:14px;padding:5px;");

$container=Field_array_Hash(array("daily"=>"{daily}","weekly"=>"{weekly}"),"CRON_CONTAINER",$_SESSION["WIZARD"]["CRON_CONTAINER"],null,null,0,"font-size:14px;padding:5px;");

$html=$html."<hr>
<H3 style='color:rgb(194, 51, 2);font-size:16px'>{WIZARD_CONFIGURE_SCHEDULE}</H3>
	<div class=explain>{WIZARD_CONFIGURE_SCHEDULE_EXPLAIN}</div>
<table style='width:100%'>
		<tr>
			<td style='font-size:13px' align='right'>{run_every_day}:</td>
			<td>$days</td>
		</tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td style='font-size:13px' align='right'>{time}:</td>
			<td nowrap>$hours:$mins</td>
		</tr>	
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2><div class=explain>{WIZARD_CONFIGURE_CONTAINER_EXPLAIN}</div></td></tr>
		<tr>
			<td style='font-size:13px' align='right'>{container}:</td>
			<td nowrap>$container</td>
		</tr>	
		
		
</table>	

<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardRessourceShow()")."</td>
			<td width=50% align='right'>". button("{next}","WizardBackupScheduleSave()")."</td>
		</tr>
	</table>	
	";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function popup_resource(){
	
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'></td>
		<td valign='top'>
	<div style='color:rgb(194, 51, 2);font-size:13px'>{WIZARD_BACKUP_CHOOSE_STORAGE}: &laquo;{$_SESSION["WIZARD"]["W_RESOURCE"]}&raquo;</div>
	<hr>";
	
	
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$html=$html.popup_resource_usb();break;
		case "smb":$html=$html.popup_resource_smb();break;
		case "rsync":$html=$html.popup_resource_smb();break;
		case "automount":$html=$html.popup_resource_automount();break;
		case "local":$html=$html.popup_resource_local();break;
		
		
		
		default:
			;
		break;
	}
	
	
	
	$html=$html."</td></tr></table>	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_resource_usb(){
	
	$usb=new usb();
	$hash=$usb->HASH_UUID_LIST();
	$hash[null]='{select}';
	
	$select=Field_array_Hash($hash,"UUID",$_SESSION["WIZARD"]["W_RESOURCE"],null,null,0,"font-size:14px;padding:5px;");
	$html="
	<H3 style='color:rgb(194, 51, 2);font-size:16px'>{usb_external_drive}</H3>
	<div class=explain>{WIZARD_BACKUP_USB_STORAGE_EXPLAIN}</div>
	$select
	<div style='text-align:right;width:100%'>".button("{refresh}","WizardRessourceShow()")."</center>
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardBackupLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardUSBSaveRessource()")."</td>
		</tr>
	</table>	
	";
	return $html;
}
function popup_resource_smb(){
	
	
	$html="
	<H3 style='color:rgb(194, 51, 2);font-size:16px'>{remote_smb_server}</H3>
	<div class=explain>{WIZARD_BACKUP_SMB_STORAGE_EXPLAIN}</div>
	<table style='width:100%'>
		<tr>
			<td style='font-size:13px' align='right'>{servername}:</td>
			<td>". Field_text("W_SMB_SERVER",$_SESSION["WIZARD"]["W_SMB_SERVER"],"font-size:13px;padding:5px")."</td>
		</tr>	
		<tr>
			<td style='font-size:13px' align='right'>{username}:</td>
			<td>". Field_text("W_SMB_USERNAME",$_SESSION["WIZARD"]["W_SMB_USERNAME"],"font-size:13px;padding:5px")."</td>
		</tr>
		<tr>
			<td style='font-size:13px' align='right'>{password}:</td>
			<td>". Field_password("W_SMB_PASSWORD",$_SESSION["WIZARD"]["W_SMB_PASSWORD"],"font-size:13px;padding:5px")."</td>
		</tr>	
		<tr>
			<td style='font-size:13px' align='right'>{shared_folder}:</td>
			<td>". Field_text("W_SMB_SHAREDDIR",$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"],"font-size:13px;padding:5px")."</td>
		</tr>	
	</table>			
		
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardBackupLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardSMBSaveRessource()")."</td>
		</tr>
	</table>	
	";
	return $html;
}

function popup_resource_automount(){
	$page=CurrentPageName();
	$tpl=new templates();
	$auto=new autofs();
	$hash=$auto->automounts_Browse();
	$add=imgtootltip("32-plus.png","{add}","Loadjs('autofs.php?form-add-js=yes')");
	
	$html="<H3 style='color:rgb(194, 51, 2);font-size:16px'>{automount_ressource}</H3>
	<div class=explain>{WIZARD_BACKUP_AUTOMOUNT_STORAGE_EXPLAIN}</div>
	<div id='BackupTaskAutoFSMountedList' style='width:100%;height:250px'></div>
	<script>
	
	function RefreshAutoMountsBackup(){
		LoadAjax('BackupTaskAutoFSMountedList','$page?automount-list=yes');
	
	}
	

	var x_AutoFSDeleteDN= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('main_config_autofs');
	}		
	
	function AutoFSDeleteDN(key){
		var XHR = new XHRConnection();
		XHR.appendData('AutoFSDeleteDN',key);	
		document.getElementById('AutoFSMountedList').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_AutoFSDeleteDN);
		}	
	RefreshAutoMountsBackup();
	</script>	
	
	";
	
	return $tpl->_ENGINE_parse_body($html);		
	
}

function popup_resource_local(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="<H3 style='color:rgb(194, 51, 2);font-size:16px'>{local_directory}</H3>
	<div class=explain>{WIZARD_BACKUP_LOCAL_STORAGE_EXPLAIN}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("LOCALDIR",$_SESSION["WIZARD"]["W_LOCALDIR"],"font-size:16px;padding:3px;width:230px")."</td>
		<td><input type='button' OnClick=\"javascript:Loadjs('SambaBrowse.php?field=LOCALDIR&no-shares=yes');\" value='{browse}...'></td>
	</tr>
	</table>
	
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardBackupLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardLocalSaveRessource()")."</td>
		</tr>
	</table>	
<script>
	function WizardLocalSaveRessource(){
		var XHR = new XHRConnection();
		var LOCALDIR=document.getElementById('LOCALDIR').value;
		if(LOCALDIR.length>1){
				XHR.appendData('W_LOCALDIR',LOCALDIR);
				XHR.sendAndLoad('$page', 'GET',x_WizardUSBSaveRessource);
			}
	}
</script>
	";
	
	return $tpl->_ENGINE_parse_body($html);		
	
}

function popup_resource_automount_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$auto=new autofs();
	$hash=$auto->automounts_Browse();
	$add=imgtootltip("32-plus.png","{add}","Loadjs('autofs.php?form-add-js=yes')");	
	
	$html="
			<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
			<thead class='thead'>
				<tr>
					<th>$add</th>
					<th>{proto}</th>
					<th>{source}</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody class='tbody'>";
	
	while (list ($localmount, $array) = each ($hash) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		if(preg_match("#\{device\}:(.+)#",$array["SRC"],$re)){
			$uuid=$re[1];
			$ligne=$_GLOBAL["usb_list"][$uuid];
			$TYPE=$ligne["TYPE"];
			$ID_MODEL=$ligne["ID_MODEL"];
			$LABEL=$ligne["LABEL"];
			$DEV=$ligne["DEV"];
			if($LABEL==null){$LABEL=$ID_MODEL;}
			$SIZE=explode(";",$ligne["SIZE"]);	
			$array["SRC"]="{device}: $LABEL ({$SIZE[0]})";
		}	
		
		$select=imgtootltip("arrow-left-32.png","{select}","WizardAutomountSaveRessource('/automounts/$localmount')");
		
		$html=$html . "
		<tr  class=$classtr>
			<td width=1%><img src='img/net-drive-32.png'></td>
			<td width=1% align='center' nowrap><strong style='font-size:14px'><code style='color:$color'>{$array["FS"]}</code></td>
			<td width=99% align='left'><strong style='font-size:14px'>{$array["SRC"]}</strong><div style='font-size:11px'><i>/automounts/$localmount</i></div></td>
			<td width=1%>$select</td>
		</td>
		</tr>";		
		
		
	}
	
	$html=$html."</tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function popup(){
	
	$sql="SELECT COUNT(*) as tcount FROM backup_schedules";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]>0){
		$intro="<hr><div id='wizard-backup-intro'>". texttooltip("[{$ligne["tcount"]}] {WIZARD_BACKUP_TASKS_ALREADY_SCHEDULED}",
		"[{$ligne["tcount"]}] {WIZARD_BACKUP_TASKS_ALREADY_SCHEDULED}",
		"Loadjs('backup.tasks.php')",null,0,"font-size:13px;color:#C23302")."</div>";
		
	}
	
	
	$storages["automount"]="{APP_AUTOFS}";
	$storages["usb"]="{usb_external_drive}";
	$storages["smb"]="{remote_smb_server}";
	$storages["rsync"]="{remote_rsync_server}";
	$storages["local"]="{local_directory}";
	$select=Field_array_Hash($storages,"storage",null,null,null,0,"font-size:16px;padding:5px;color: rgb(194, 51, 2);");
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/backup-128-bg.png'>$intro</td>
		<td valign='top'>
	
	<div class=explain>{WIZARD_BACKUP_EXPLAIN}</div>
	<p style='font-size:18px;color: rgb(194, 51, 2)'>{WIZARD_BACKUP_CHOOSE_STORAGE}</p>
	<div class=explain>{WIZARD_BACKUP_CHOOSE_STORAGE_EXPLAIN}</div>
	$select
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{cancel}","CancelBackupWizard()")."</td>
			<td width=50% align='right'>". button("{next}","WizardRessource()")."</td>
		</tr>
	</table>
	</td></tr></table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function BACKUP_COMPILE(){
	$backup=new backup_protocols();
	switch ($_SESSION["WIZARD"]["W_RESOURCE"]) {
		case "usb":$pattern="usb://{$_SESSION["WIZARD"]["W_UUID"]}";break;
		case "smb":$pattern=$backup->build_smb_protocol($_SESSION["WIZARD"]["W_SMB_SERVER"],
		$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"],
		$_SESSION["WIZARD"]["W_SMB_USERNAME"],
		$_SESSION["WIZARD"]["W_SMB_PASSWORD"]);break;

		case "rsync":$pattern=$backup->build_rsync_protocol($_SESSION["WIZARD"]["W_SMB_SERVER"],
		$_SESSION["WIZARD"]["W_SMB_SHAREDDIR"],
		$_SESSION["WIZARD"]["W_SMB_USERNAME"],
		$_SESSION["WIZARD"]["W_SMB_PASSWORD"]);break;
		case "automount":$pattern="automount:{$_SESSION["WIZARD"]["W_AUTOMOUNT_DIR"]}";
		case "local":$pattern="local:{$_SESSION["WIZARD"]["W_LOCALDIR"]}";
		
		default:;break;
	}
	$cron=new cron_macros();
	
	$ressources_array[0]="all";
	$ressources_array["OPTIONS"]["STOP_IMAP"]=0;
	
	
	$schedule=$cron->cron_compile_eachday($_SESSION["WIZARD"]["CRON_DAYS"],$_SESSION["WIZARD"]["CRON_HOURS"],$_SESSION["WIZARD"]["CRON_MIN"]);
	$datasbackup=base64_encode(serialize($ressources_array));
	$resource_type=$_SESSION["WIZARD"]["W_RESOURCE"];
	$CRON_CONTAINER=$_SESSION["WIZARD"]["CRON_CONTAINER"];
	$md5=md5($schedule.$pattern);
	
	$q=new mysql();
	$sql="INSERT INTO  backup_schedules(`zMD5`,`resource_type`,`pattern`,`schedule`,`datasbackup`,`container`)
	VALUES('$md5','$resource_type','$pattern','$schedule','$datasbackup','$CRON_CONTAINER')";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return ;
	}
	$sock=new sockets();
	$sock->SET_INFO("WizardBackupSeen",1);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?backup-build-cron=yes");
	
}

function WIZARD_CANCEL(){
	$sock=new sockets();
	$sock->SET_INFO("WizardBackupSeen",1);
	
}





?>