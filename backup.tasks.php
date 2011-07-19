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

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["show_backuped_folders"])){FOLDERS_BACKUPED();exit;}
	if(isset($_GET["ExecBackupTemporaryPath"])){BACKUP_OPTIONS_SAVE();exit;}
	if(isset($_GET["BACKUP_TASKS_LISTS"])){tasks_list();exit;}
	if(isset($_GET["show_tasks"])){show_tasks();exit;}
	if(isset($_GET["BACKUP_TASK_RUN"])){BACKUP_TASK_RUN();exit;}
	if(isset($_GET["backup-sources"])){BACKUP_SOURCES();exit;}
	if(isset($_GET["DeleteBackupSource"])){BACKUP_SOURCES_DELETE();exit;}
	if(isset($_GET["DeleteBackupTask"])){BACKUP_TASK_DELETE();exit;}
	if(isset($_GET["adv-options"])){BACKUP_OPTIONS();exit;}
	if(isset($_GET["backup-tests"])){BACKUP_TASK_TEST();exit;}
	if(isset($_GET["backup_stop_imap"])){BACKUP_SOURCES_SAVE_OPTIONS();exit;}
	if(isset($_GET["FOLDER_BACKUP"])){FOLDER_BACKUP_JS();exit;}
	if(isset($_GET["FOLDER_BACKUP_POPUP"])){FOLDER_BACKUP_POPUP();exit;}
	if(isset($_GET["BACKUP_FOLDER_ENABLE"])){BACKUP_FOLDER_ENABLE();exit;}
	if(isset($_GET["FOLDER_BACKUP_DELETE"])){FOLDERS_BACKUPED_DELETE();exit;}
	if(isset($_GET["TASK_EVENTS_DETAILS"])){TASK_EVENTS_DETAILS();exit;}
	if(isset($_GET["TASK_EVENTS_DETAILS_INFOS"])){TASK_EVENTS_DETAILS_INFOS();exit;}
	if(isset($_GET["BACKUP_TASK_MODIFY_RESSOURCES"])){BACKUP_TASK_MODIFY_RESSOURCES();exit;}
	if(isset($_POST["BACKUP_TASK_MODIFY_RESSOURCES_APPLY"])){BACKUP_TASK_MODIFY_RESSOURCES_SAVE();exit;}
	
	if(isset($_GET["events"])){BACKUP_EVENTS();exit;}
js();



function FOLDER_BACKUP_JS(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{backup_a_folder}");
	$folder=$_GET["FOLDER_BACKUP"];
	$html="
		function FOLDER_BACKUP_START(){
			YahooWin(600,'$page?FOLDER_BACKUP_POPUP=yes&folder=$folder','$title');
		
		}

	var x_BACKUP_FOLDER_ENABLE= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
	 }			
		
	function BACKUP_FOLDER_ENABLE(taskid){
		var XHR = new XHRConnection();
		XHR.appendData('BACKUP_FOLDER_ENABLE',taskid);
		if(document.getElementById('task_folder_'+taskid).checked){
			XHR.appendData('ENABLE',1);
			}else{
			XHR.appendData('ENABLE',0);
			}
		if(document.getElementById('recursive_folder_'+taskid).checked){
			XHR.appendData('RECURSIVE',1);
			}else{
			XHR.appendData('RECURSIVE',0);
			}			
			
		XHR.appendData('folder','$folder');
		XHR.sendAndLoad('$page', 'GET',x_BACKUP_FOLDER_ENABLE);
		
	}
	
	FOLDER_BACKUP_START();
	";
	echo $html;
}

function BACKUP_FOLDER_ENABLE(){
	$taskid=$_GET["BACKUP_FOLDER_ENABLE"];
	$folder=$_GET["folder"];
	$RECURSIVE=$_GET["RECURSIVE"];
	
	$sql="SELECT ID FROM backup_folders WHERE path='$folder' AND taskid='$taskid'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ID=$ligne["ID"];
	
	
	if($ID==0){
		if($_GET["ENABLE"]==0){return;}
		if($_GET["ENABLE"]==1){
			$sql="INSERT INTO backup_folders (path,taskid,recursive) VALUES('$folder',$taskid,$RECURSIVE)";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error;}
			return;
		}
	}
	
	if($_GET["ENABLE"]==0){
		$sql="DELETE FROM backup_folders WHERE ID=$ID";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;}
		return;
	}
	
	$sql="UPDATE backup_folders SET recursive=$RECURSIVE WHERE ID=$ID";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
}

function FOLDERS_BACKUPED(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$q=new mysql();
	$cron=new cron_macros();
	
	$sql="SELECT * FROM backup_schedules ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$backup=new backup_protocols();	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$typeBck=$backup->backuptypes[$ligne["resource_type"]];
		if($typeBck==null){$typeBck=$ligne["resource_type"];}
		$TASK_EX[$ligne["ID"]]=$typeBck."&nbsp;(".$cron->cron_human($ligne["schedule"]).")";
	}
	
	
	
	$html=$html."
	<div id='FOLDER_BACKUPED_DIV'>
	<table style='width:99%'>
	<th>{task}</th>
	<th>&nbsp;</th>
	<th>{path}</th>
	<th>{recursive}</th>
	<th>&nbsp;</th>
	</tr>";	
	
	$sql="SELECT * FROM backup_folders ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["recursive"]==1){$ligne["recursive"]="{enabled}";}else{$ligne["recursive"]="{disabled}";}
		
		$html=$html.
		"<tr ". CellRollOver().">
		<td width=1% align='center'><strong style='font-size:12px'>{$ligne["taskid"]}</strong></td>
		<td style='font-size:12px' nowrap>". $TASK_EX[$ligne["taskid"]]."</td>
		<td style='font-size:12px' width=98%><code>". base64_decode($ligne["path"])."</code></td>
		<td width=1% style='font-size:12px' align='left'>{$ligne["recursive"]}</td>
		<td width=1% style='font-size:12px' align='left'>". imgtootltip("ed_delete.gif","{delete}","FOLDER_BACKUP_DELETE({$ligne["ID"]})")."</td>
		</tr>";
		
	}
$html=$html."</table></div>

<script>
var x_FOLDER_BACKUP_DELETE= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		
	 }			
		
	function FOLDER_BACKUP_DELETE(ID){
		var XHR = new XHRConnection();
		XHR.appendData('FOLDER_BACKUP_DELETE',ID);
		document.getElementById('FOLDER_BACKUPED_DIV').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_FOLDER_BACKUP_DELETE);
		RefreshTab('main_config_backup_tasks');
	}
	
</script>

";
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function FOLDERS_BACKUPED_DELETE(){
	$ID=$_GET["FOLDER_BACKUP_DELETE"];
	$q=new mysql();
	$sql="DELETE FROM backup_folders WHERE ID='$ID'";
	$q->QUERY_SQL($sql,"artica_backup");
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	if(!$q->ok){echo $q->mysql_error;}
}


function FOLDER_BACKUP_POPUP(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$backup_decoded=base64_decode($_GET["folder"]);
	$cron=new cron_macros();
	$html="<H3>{backup_this_directory}: $backup_decoded</H3>
	<p style='font-size:13px'>{backup_this_directory_explain}</p>
	";
	
	$sql="SELECT taskid,recursive FROM backup_folders WHERE path='{$_GET["folder"]}'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$tasks[$ligne["taskid"]]=1;
		$recursive[$ligne["taskid"]]=$ligne["recursive"];
	}
	
	$sql="SELECT * FROM backup_schedules ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$backup=new backup_protocols();
	
	
	$html=$html."<table style='width:99%'>
	<th>{task}</th>
	<th>{STORAGE_TYPE}</th>
	<th>{schedule}</th>
	<th>{enabled}</th>
	<th>{recursive}</th>
	</tr>";
		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		
		$enable=Field_checkbox("task_folder_{$ligne["ID"]}",1,$tasks[$ligne["ID"]],"BACKUP_FOLDER_ENABLE({$ligne["ID"]})");
		$recursive=Field_checkbox("recursive_folder_{$ligne["ID"]}",1,$recursive[$ligne["ID"]],"BACKUP_FOLDER_ENABLE({$ligne["ID"]})");
		$STORAGE_TYPE=$backup->backuptypes[trim($ligne["resource_type"])];
		if($STORAGE_TYPE==null){$STORAGE_TYPE=$ligne["resource_type"];}
		
		
		$html=$html.
		"<tr ". CellRollOver().">
		<td width=1% align='center'><strong style='font-size:12px'>{$ligne["ID"]}</strong></td>
		<td style='font-size:12px'>{$backup->backuptypes[$ligne["resource_type"]]}</td>
		<td style='font-size:12px'>". $cron->cron_human($ligne["schedule"])."</td>
		<td style='font-size:12px' align='center'>$enable</td>
		<td style='font-size:12px' align='center'>$recursive</td>
		</tr>";
		
		
	}	
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}




function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{BACKUP_TASKS}");
	$sources=$tpl->_ENGINE_parse_body("{sources}");	
	$BACKUP_TASK_CONFIRM_DELETE=$tpl->javascript_parse_text("{BACKUP_TASK_CONFIRM_DELETE}");
	$BACKUP_TASK_CONFIRM_DELETE_SOURCE=$tpl->javascript_parse_text("{BACKUP_TASK_CONFIRM_DELETE_SOURCE}");
	$tests=$tpl->javascript_parse_text("{test}");
	$backupTaskRunAsk=$tpl->javascript_parse_text("{backupTaskRunAsk}");
	$apply_upgrade_help=$tpl->javascript_parse_text("{apply_upgrade_help}");
	$events=$tpl->_ENGINE_parse_body("{events}");
	$resources=$tpl->_ENGINE_parse_body("{resources}");
	
	$start="BACKUP_TASKS_LOAD();";
	
	if(isset($_GET["in-front-ajax"])){
		$start="BACKUP_TASKS_LOAD2();";
	}
	
	$html="
	mem_taskid='';	
	
		function BACKUP_TASKS_LOAD(){
			YahooWin2('785','$page?popup=yes','$title');
		}
		
		function BACKUP_TASKS_LOAD2(){
			$('#BodyContent').load('$page?popup=yes');
		}		
	
		function BACKUP_TASKS_LISTS(){
			LoadAjax('taskslists','$page?BACKUP_TASKS_LISTS=yes');
		}
		
		function BACKUP_TASKS_SOURCE(ID){
			YahooWin3('500','$page?backup-sources=yes&ID='+ID,'$sources');
		}
		
		function TASK_EVENTS_DETAILS(ID){
			YahooWin3('700','$page?TASK_EVENTS_DETAILS='+ID,ID+'::$events');
		}
		
		function TASK_EVENTS_DETAILS_INFOS(ID){
			YahooWin4('700','$page?TASK_EVENTS_DETAILS_INFOS='+ID,ID+'::$events');
		}
		
		function BACKUP_TASK_MODIFY_RESSOURCES(ID){
			YahooWin3('500','$page?BACKUP_TASK_MODIFY_RESSOURCES='+ID,ID+'::$resources');
		}
		
		
var x_DeleteBackupTask= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		BACKUP_TASKS_LISTS();
		if(document.getElementById('wizard-backup-intro')){
			WizardBackupLoad();
		}
	 }	

var x_DELETE_BACKUP_SOURCES= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		BACKUP_TASKS_LISTS();
		YahooWin3Hide();
	 }		 
		
		function DeleteBackupTask(ID){
			if(confirm('$BACKUP_TASK_CONFIRM_DELETE')){
				var XHR = new XHRConnection();
				XHR.appendData('DeleteBackupTask',ID);
				XHR.sendAndLoad('$page', 'GET',x_DeleteBackupTask);
			}
		}
		
		function DELETE_BACKUP_SOURCES(ID,INDEX){
			if(confirm('$BACKUP_TASK_CONFIRM_DELETE_SOURCE')){
				var XHR = new XHRConnection();
				XHR.appendData('DeleteBackupSource','yes');
				XHR.appendData('ID',ID);
				XHR.appendData('INDEX',INDEX);
				XHR.sendAndLoad('$page', 'GET',x_DELETE_BACKUP_SOURCES);
			}
		}
		
		
	var x_BACKUP_SOURCES_SAVE_OPTIONS= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			BACKUP_TASKS_SOURCE(mem_taskid);
			
		 }		
		
		
	function BACKUP_SOURCES_SAVE_OPTIONS(taskid){
		mem_taskid=taskid;
		var XHR = new XHRConnection();
		if(document.getElementById('backup_stop_imap').checked){
		XHR.appendData('backup_stop_imap',1);}else{
		XHR.appendData('backup_stop_imap',0);}
		XHR.appendData('taskid',taskid);
		document.getElementById('BACKUP_SOURCES_OPTIONS').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET',x_BACKUP_SOURCES_SAVE_OPTIONS);
		}	

	function BACKUP_TASK_TEST(ID){
			YahooWin3('790','$page?backup-tests=yes&ID='+ID,'$tests');
		}
		
	var x_BACKUP_TASK_RUN= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			alert('$apply_upgrade_help');
			BACKUP_TASKS_LISTS();
		 }		
		
		
		
		function BACKUP_TASK_RUN(ID){
			if(confirm('$backupTaskRunAsk')){
				var XHR = new XHRConnection();
				XHR.appendData('BACKUP_TASK_RUN',ID);
				XHR.sendAndLoad('$page', 'GET',x_BACKUP_TASK_RUN);
			}
		}
		
	
	$start";
	
	
	echo $html;
}


function popup(){
	
	$tpl=new templates();
	$array["show_tasks"]='{tasks}';
	$array["show_backuped_folders"]='{backuped_folders}';
	$array["adv-options"]='{advanced_options}';
	$array["events"]='{events}';
	
	
	
	
	$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_backup_tasks style='width:100%;height:350px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_backup_tasks').tabs();
			
			
			});
		</script>";	
	
	
}

function show_tasks(){
	$html="
	
	<div id='taskslists'></div>
	
	<script>
	BACKUP_TASKS_LISTS();
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function FOLDER_BACKUPED_NUMBER($taskid){
	$q=new mysql();
	$sql="SELECT COUNT(ID) AS tcount FROM backup_folders WHERE taskid=$taskid";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["tcount"]==null){$ligne["tcount"]=0;}
	return $ligne["tcount"];
}

function tasks_list(){
	$sql=new mysql();
	$sock=new sockets();
	$sql="SELECT * FROM backup_schedules ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$backup=new backup_protocols();
	$cron=new cron_macros();
	$storages["usb"]="{usb_external_drive}";
	$storages["smb"]="{remote_smb_server}";
	$storages["rsync"]="{remote_rsync_server}";
	$storages["automount"]="{automount_ressource}";
	$storages["local"]="{local_directory}";		
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th>{task}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>{test}</th>
	<th>{STORAGE_TYPE}</th>
	<th>{resource}</th>
	<th>{schedule}</th>
	<th>{sources}</th>
	<th>&nbsp;</th>
</thead>
<tbody class='tbody'>";
		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ressources=unserialize(base64_decode($ligne["datasbackup"]));
		$sources=(count($ressources)-1)+FOLDER_BACKUPED_NUMBER($ligne["ID"])." {sources}";
		$sources=texttooltip($sources,"{show}","BACKUP_TASKS_SOURCE({$ligne["ID"]})",null,0,"font-size:12px");
		
		$run=imgtootltip("run-24.png","{run_task}","BACKUP_TASK_RUN({$ligne["ID"]})");
		if($ligne["pid"]>5){
			$array_pid=unserialize(base64_decode($sock->getFrameWork("cmd.php?procstat={$ligne["pid"]}")));
			if($array_pid["since"]<>null){
				$run=imgtootltip("ajax-menus-loader.gif","{running}: {since} {$array_pid["since"]}","");
			}
		}
		
		
		$html=$html.
		"<tr class=$classtr>
		<td width=1% align='center'><strong style='font-size:12px'>{$ligne["ID"]}</strong></td>
		<td width=1% style='font-size:12px' align='left'>". imgtootltip("events-24.png","{events}","TASK_EVENTS_DETAILS({$ligne["ID"]})")."</td>
		<td width=1%>$run</td>
		<td width=1%>". imgtootltip("eclaire-24.png","{BACKUP_TASK_TEST}","BACKUP_TASK_TEST({$ligne["ID"]})")."</td>
		<td style='font-size:12px' nowrap><a href=\"javascript:BACKUP_TASK_MODIFY_RESSOURCES('{$ligne["ID"]}')\" style='text-decoration:underline'>[{$storages[$ligne["resource_type"]]}]</a></td>
		<td style='font-size:12px'>". $backup->extractFirsRessource($ligne["pattern"])."</td>
		<td style='font-size:12px' nowrap>". $cron->cron_human($ligne["schedule"])."</td>
		<td style='font-size:12px'>$sources</td>
		<td style='font-size:12px'>". imgtootltip("delete-24.png","{delete}","DeleteBackupTask({$ligne["ID"]})")."</td>
		</tr>";
		
		
	}
	
	$html=$html."</tbody></table>
	<hr>
	<table style='width:100%'>
	<tr>
	<td align='left'>". button("{add_task}","Loadjs('wizard.backup-all.php');")."</td>
	<td align='right'><div style='width:100%;text-align:right'>". imgtootltip('32-refresh.png',"{refresh}","BACKUP_TASKS_LISTS()")."</div></td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function BACKUP_SOURCES(){
	$sql="SELECT datasbackup FROM backup_schedules WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ressources=unserialize(base64_decode($ligne["datasbackup"]));
	$html="
	<div style='width:100%;height:150px;overflow:auto;'>
	<table style='width:99%'>";
	if(is_array($ressources)){
		while (list ($num, $val) = each ($ressources) ){
			if(is_array($val)){continue;}
			$val=str_replace("all","{BACKUP_ALL_MEANS}",$val);
			$html=$html.
			
			"
			<tr><td colspan=4 style='border-top:1px solid #005447'>&nbsp;</td></tr>
			<tr ". CellRollOver().">
				<td widh=1% valign='top'><img src='img/fw_bold.gif'></td>
				<td widh=1% valign='top' style='font-size:12px'><STRONG>{source}&nbsp;$num</STRONG></td>
				<td width=99%><code style='font-size:12px;font-weight:bold'>$val</td>
				<td widh=1% valign='top'>". imgtootltip("ed_delete.gif","{delete}","DELETE_BACKUP_SOURCES({$_GET["ID"]},$num)")."</td>
			</tr>
			";
			
		}
	}
	
	$sql="SELECT * FROM backup_folders WHERE taskid={$_GET["ID"]}";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["recursive"]==1){$ligne["recursive"]="{enabled}";}else{$ligne["recursive"]="{disabled}";}
		
		$html=$html.
		"<tr><td colspan=4 style='border-top:1px solid #005447'>&nbsp;</td></tr>
		<tr ". CellRollOver().">
		<td widh=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td style='font-size:12px' nowrap><strong>{folder}</strong></td>
		<td style='font-size:12px;font-weight:bold' width=98%><code>". base64_decode($ligne["path"])."</code></td>
		<td width=1% style='font-size:12px' align='left'>&nbsp;</td>
		</tr>";
		
	}	
	
	
	$html=$html."
	</table>
	</div>
	<BR>
	<div id='BACKUP_SOURCES_OPTIONS'>
	<H3>{options}</H3>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{backup_stop_imap}:</td>
		<td valign='top'>". Field_checkbox("backup_stop_imap",1,$ressources["OPTIONS"]["STOP_IMAP"])."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{apply}","BACKUP_SOURCES_SAVE_OPTIONS({$_GET["ID"]})")."
		</td>
	</tr>
	</table>
	</div>";
	






$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function BACKUP_TASK_RUN(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?backup-task-run={$_GET["BACKUP_TASK_RUN"]}");
	
}

function BACKUP_SOURCES_DELETE(){
	$sql="SELECT datasbackup FROM backup_schedules WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ressources=unserialize(base64_decode($ligne["datasbackup"]));
	unset($ressources[$_GET["INDEX"]]);
	$new_ressources=base64_encode(serialize($ressources));
	$sql="UPDATE backup_schedules SET datasbackup='$new_ressources' WHERE ID='{$_GET["ID"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?backup-build-cron=yes");	
	
}

function BACKUP_SOURCES_SAVE_OPTIONS(){
	$sql="SELECT datasbackup FROM backup_schedules WHERE ID='{$_GET["taskid"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ressources=unserialize(base64_decode($ligne["datasbackup"]));
	
	$ressources["OPTIONS"]["STOP_IMAP"]=$_GET["backup_stop_imap"];
	
	$new_ressources=base64_encode(serialize($ressources));
	$sql="UPDATE backup_schedules SET datasbackup='$new_ressources' WHERE ID='{$_GET["taskid"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	
}


function BACKUP_TASK_DELETE(){
	$sql="DELETE FROM backup_schedules WHERE ID='{$_GET["DeleteBackupTask"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="DELETE FROM backup_events WHERE task_id='{$_GET["DeleteBackupTask"]}'";
	$q->QUERY_SQL($sql,"artica_events");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?backup-build-cron=yes");	
	
}

function BACKUP_TASK_TEST(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?backup-sql-test={$_GET["ID"]}")));
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
	
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($num, $val) = each ($datas) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$html=$html . "
		<tr  class=$classtr>
			
			<td style='font-size:13px'>$val</td>
		</tr>";				
		
		
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function BACKUP_OPTIONS_SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("ExecBackupTemporaryPath",$_GET["ExecBackupTemporaryPath"]);
	$sock->SET_INFO("NoBzipForBackupDatabasesDump",$_GET["NoBzipForBackupDatabasesDump"]);
	$sock->SET_INFO("ExecBackupDeadAfterH",$_GET["ExecBackupDeadAfterH"]);
}


function BACKUP_OPTIONS(){
	$sock=new sockets();
	$page=CurrentPageName();
	$temporarySourceDir=$sock->GET_INFO("ExecBackupTemporaryPath");
	$ExecBackupDeadAfterH=$sock->GET_INFO("ExecBackupDeadAfterH");
	
	
	if($temporarySourceDir==null){$temporarySourceDir="/home/mysqlhotcopy";}
	$NoBzipForBackupDatabasesDump=$sock->GET_INFO("NoBzipForBackupDatabasesDump");
	if($NoBzipForBackupDatabasesDump==null){$NoBzipForBackupDatabasesDump=1;}
	if(!is_numeric($ExecBackupDeadAfterH)){$ExecBackupDeadAfterH=2;}
	
	$html="
	<div id='backup-adv-options'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{ExecBackupTemporaryPath}:</td>
		<td>". Field_text("ExecBackupTemporaryPath",$temporarySourceDir,"font-size:13px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{ExecBackupDeadAfterH}:</td>
		<td style='font-size:13px'>". Field_text("ExecBackupDeadAfterH",$ExecBackupDeadAfterH,"font-size:13px;width:90px")."&nbsp;{hours}</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{NoBzipForDatabasesDump}:</td>
		<td>". Field_checkbox("NoBzipForBackupDatabasesDump",1,$NoBzipForBackupDatabasesDump)."</td>
	</tr>	
	
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SAVE_BACKUP_OPTIONS()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_SAVE_BACKUP_OPTIONS= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		RefreshTab('main_config_backup_tasks');
	 }	
	
		function SAVE_BACKUP_OPTIONS(){
			var XHR = new XHRConnection();
			XHR.appendData('ExecBackupTemporaryPath',document.getElementById('ExecBackupTemporaryPath').value);
			XHR.appendData('ExecBackupDeadAfterH',document.getElementById('ExecBackupDeadAfterH').value);
			if(document.getElementById('NoBzipForBackupDatabasesDump').checked){
				XHR.appendData('NoBzipForBackupDatabasesDump',1);
			}else{
				XHR.appendData('NoBzipForBackupDatabasesDump',0);
			}
			
			document.getElementById('backup-adv-options').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SAVE_BACKUP_OPTIONS);
		
		}
		
	</script>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function BACKUP_EVENTS(){
	
$html="<table style='width:99%'>
	<th>&nbsp;</th>
	<th>{date}</th>
	<th>{source}</th>
	<th>{resource}</th>
	<th>{status}</th>
	</tr>";
			
	$backup=new backup_protocols();
	$sql="SELECT * FROM `cyrus_backup_events` ORDER BY `cyrus_backup_events`.`ID` DESC LIMIT 0 , 200";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$img="status_ok.gif";
		if($ligne["success"]==0){$img="status_critical.gif";}
		$array=$backup->ParseProto($ligne["remote_ressource"]);
$html=$html.
		"
		<tr ". CellRollOver().">
		<td widh=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td style='font-size:12px' nowrap><strong>{$ligne["zDate"]}</strong></td>
		<td style='font-size:12px;font-weight:bold' width=98%><code>{$ligne["local_ressource"]}</code></td>
		<td style='font-size:12px' align='left' nowrap>{$array["SERVER"]}</td>
		<td width=1% style='font-size:12px' align='left'><img src='img/$img'></td>
		</tr>
		<tr><td colspan=5 style='border-bottom:1px solid #005447' align=right><i style='font-size:11px'>{$ligne["events"]}</i></td></tr>";		
		
		
	}
	
	
$html=$html."
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function TASK_EVENTS_DETAILS_INFOS(){
	$ID=$_GET["TASK_EVENTS_DETAILS_INFOS"];
	$sql="SELECT *  FROM backup_events WHERE ID=$ID";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$html="<H2>$ID)&nbsp;{$ligne["zdate"]}::{$ligne["backup_source"]}</H2>
	<div style='height:300px;overflow:auto;border:1px solid #CCCCCC;padding:5px;margin:5px'>";
	$events=@explode("\n",$ligne["event"]);
	

	while (list ($num, $line) = each ($events)){

		$html=$html. "<div style='padding:2px'><code>".htmlspecialchars($line)."</code></div>";
	}
	
	$html=$html."</div>";
	
	echo $html;
	
}


function TASK_EVENTS_DETAILS(){
	$ID=$_GET["TASK_EVENTS_DETAILS"];
	
$html="<div style='height:300px;overflow:auto'>
<table style='width:99%'>
	<th>&nbsp;</th>
	<th>{date}</th>
	<th>{resource}</th>
	<th>{status}</th>
	<th>{events}</th>
	</tr>";	
	
$sql="SELECT *,DATE_FORMAT(zdate,'%W') as explainday,DATE_FORMAT(zdate,'%p') as tmorn,DATE_FORMAT(zdate,'%Hh%i') as ttime  FROM `backup_events` WHERE `task_id`='$ID' ORDER BY `backup_events`.`zdate` DESC LIMIT 0 , 200";
$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$img="info-18.png";
		$status=null;
		if(strlen($ligne["event"])>60){$ligne["event"]=substr($ligne["event"],0,57)."...";}
		
		if(preg_match("#^([A-Z]+).*?,#",$ligne["event"],$re)){
			$status=$re[1];
			$ligne["event"]=str_replace($re[1].',','',$ligne["event"]);
			
		}
		$ligne["explainday"]=strtolower($ligne["explainday"]);
		$date="{{$ligne["explainday"]}} {$ligne["ttime"]}";
		
		
		switch ($status) {
			case "INFO":$img="info-18.png";break;
			case "ERROR":$img="status_warning.gif";break;
			default:
				;
			break;
		}
		
		$display="TASK_EVENTS_DETAILS_INFOS({$ligne["ID"]})";
		
$html=$html.
		"
		<tr ". CellRollOver($display,"{display}").">
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td width=1%  style='font-size:12px' nowrap><strong>$date</strong></td>
		<td   style='font-size:12px' nowrap width=1%><strong>{$ligne["backup_source"]}</strong></td>
		<td width=1%  align='center' valign='middle'><img src='img/$img'></td>
		<td style='font-size:12px' width=99% nowrap><strong>{$ligne["event"]}</strong></td>		
		</tr>
		";
		
		
	}
	
$html=$html."
	</table>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}


function BACKUP_TASK_MODIFY_RESSOURCES(){
	$backup=new backup_protocols();	
	$page=CurrentPageName();
	$ID=$_GET["BACKUP_TASK_MODIFY_RESSOURCES"];
	$q=new mysql();
	$sql="SELECT resource_type,pattern FROM backup_schedules WHERE ID=$ID";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$backupfields=Field_array_Hash($backup->backuptypes,"BACKUP_TASK_MODIFY_RESSOURCES_TYPE",$ligne["resource_type"],null,null,0,"font-size:13px;padding:3px");
	
	$html="
	<div id='BACKUP_TASK_MODIFY_RESSOURCES_DIV'>
	<table style='width:100%'>
	<tr>
		<td valign='middle' class=legend>{STORAGE_TYPE}:</td>
		<td valign='top'>$backupfields</td>
	</tr>
	<tr>
	<td colspan=2><input type='text' id='BACKUP_TASK_MODIFY_RESSOURCES_PATTERN' style='width:100%;padding:5px;font-size:14px' value='{$ligne["pattern"]}'></td>
	</tr>
	<tr>
	<td colspan=2 align='right'><hr>". button("{apply}","BACKUP_TASK_MODIFY_RESSOURCES_APPLY()")."</td>
	</tr>
	
	</table>
	</div>
	<script>
	var x_BACKUP_TASK_MODIFY_RESSOURCES_APPLY= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		RefreshTab('main_config_backup_tasks');
		YahooWin3Hide();
	 }	
	
		function BACKUP_TASK_MODIFY_RESSOURCES_APPLY(){
			var XHR = new XHRConnection();
			XHR.appendData('BACKUP_TASK_MODIFY_RESSOURCES_APPLY',$ID);
			XHR.appendData('BACKUP_TASK_MODIFY_RESSOURCES_TYPE',document.getElementById('BACKUP_TASK_MODIFY_RESSOURCES_TYPE').value);
			XHR.appendData('BACKUP_TASK_MODIFY_RESSOURCES_PATTERN',document.getElementById('BACKUP_TASK_MODIFY_RESSOURCES_PATTERN').value);
			document.getElementById('BACKUP_TASK_MODIFY_RESSOURCES_DIV').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'POST',x_BACKUP_TASK_MODIFY_RESSOURCES_APPLY);
		
		}
	
</script>	";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function BACKUP_TASK_MODIFY_RESSOURCES_SAVE(){
	$sql="UPDATE backup_schedules 
		SET pattern='{$_POST["BACKUP_TASK_MODIFY_RESSOURCES_PATTERN"]}',
		resource_type='{$_POST["BACKUP_TASK_MODIFY_RESSOURCES_TYPE"]}'
		WHERE ID={$_POST["BACKUP_TASK_MODIFY_RESSOURCES_APPLY"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}
}



	
?>