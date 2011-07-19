<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.computers.inc');
	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-add-task"])){add_task();exit;}
	if(isset($_GET["EditTask"])){edit_task();exit;}
	if(isset($_GET["tasklist"])){echo task_list($_GET["uid"]);exit;}
	if(isset($_GET["DeleteTask"])){delete_task();exit;}
	if(isset($_GET["RetryTask"])){retry_task();exit;}
	if(isset($_GET["PackageSelected"])){select_package();exit;}

js();	

		
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{remote_install}',"storage.center.php");
	$uid=$_GET["uid"];
	$html="
	var mem_id='';
	
	function LoadMainRI(){
		YahooWin5('650','$page?popup=yes&uid=$uid','$title');
		}	
		
	function AddRemoteTask(){
		YahooWin2('500','$page?popup-add-task=yes&uid=$uid');
	}
	
	function EditRemoteTask(id){
		YahooWin2('500','$page?popup-add-task=yes&uid=$uid&taskid='+id);
	}	
	
	function RefreshTaskList(){
		LoadAjax('tk_list','$page?tasklist=yes&uid=$uid');
	
	}	
	
	function RestartTask(id){
		var XHR = new XHRConnection();
		XHR.appendData('RetryTask',id);
		XHR.appendData('uid','$uid');
		document.getElementById('taskdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveTask);		
	}
	
	
	var x_DelRemoteSoftware=function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		RefreshSoftwaresList();
		}

	function DelRemoteSoftware(id){
			var XHR = new XHRConnection();
			XHR.appendData('DelRemoteSoftware',id);
			document.getElementById('software_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_DelRemoteSoftware);
			}


	var x_SaveTask=function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		RefreshTaskList();
		YahooWin2Hide();
		}
		


function SaveTask(id){
		//commandline file_id username password 
		var XHR = new XHRConnection();
		XHR.appendData('EditTask',id);
		XHR.appendData('commandline',document.getElementById('commandline').value);
		XHR.appendData('file_id',document.getElementById('file_id').value);
		XHR.appendData('username',document.getElementById('username').value);		
		XHR.appendData('password',document.getElementById('password').value);
		XHR.appendData('debug_mode',document.getElementById('debug_mode').value);
		XHR.appendData('uid','$uid');
		document.getElementById('taskdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveTask);
}

function TaskRIDelete(id){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteTask',id);
		XHR.appendData('uid','$uid');
		document.getElementById('tk_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveTask);
		
}



	var x_TaskPackageSelected=function (obj) {
		var results=obj.responseText;
		if (results.length>0){
			document.getElementById('default_commandline').innerHTML=results;
		}
	}

function TaskPackageSelected(){
		var XHR = new XHRConnection();
		XHR.appendData('PackageSelected',document.getElementById('file_id').value);
		XHR.sendAndLoad('$page', 'GET',x_TaskPackageSelected);
		
}

 
	

LoadMainRI();
	
";

echo $html;
}


function popup(){
	
	$users=new usersMenus();
	if(!$users->winexe_installed){
		$warn=Paragraphe("64-infos.png","{APP_WINEXE_NOT_INSTALLED}",
		"{APP_WINEXE_NOT_INSTALLED_TEXT}","javascript:Loadjs('setup.index.progress.php?product=APP_WINEXE&start-install=yes');")."<br>";
	}
	
	$DEF_ICO_REMOTE_STORAGE=Buildicon64("DEF_ICO_REMOTE_STORAGE");
	
	$add=Paragraphe("software-task-64.png","{ADD_NEW_DEPLOY_TASK}","{ADD_NEW_DEPLOY_TASK_TEXT}","javascript:AddRemoteTask()");
	$refresh=Paragraphe("64-refresh.png","{refresh}","{resfresh_tasks_list}","javascript:RefreshTaskList()");
	
	$list=task_list($_GET["uid"]);
	
	$html="<H1>{remote_install}::{$_GET["uid"]}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>$warn$refresh<br>$add<br>$DEF_ICO_REMOTE_STORAGE</td>
	<td valign='top'>
		<p class=caption>{remote_install_text}</p>
		". RoundedLightWhite("<div id='tk_list' style='width:100%;height:250px;overflow:auto'>$list</div>")."
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'storage.center.php');	
	
}

function package_name($id){
	$sql="SELECT filename FROM files_storage WHERE id_files='$id'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return $ligne["filename"];
	
}

function select_package(){
		$q=new mysql();
		$sql="SELECT commandline FROM files_storage WHERE id_files={$_GET["PackageSelected"]}";
		
		$ligne2=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$default_commandline=$ligne2["commandline"];
		if($default_commandline==null){$default_commandline="none";}
		echo "<code style='font-size:11px;font-weight:bold'>$default_commandline</code>";
}

function delete_task(){
	$sql="DELETE FROM deploy_tasks WHERE ID={$_GET["DeleteTask"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}

function retry_task(){
	$sql="UPDATE deploy_tasks set executed=0 WHERE ID={$_GET["RetryTask"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return false;}
	$sock=new sockets();
	$sock->getfile('LaunchRemoteInstall');	
	
}

function task_list($computerid){
	$sql="SELECT * FROM deploy_tasks WHERE computer_id='$computerid' ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$html="<table style='width:99%'>";
	
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	
	switch ($ligne["executed"]) {
		case 0:$img="status_service_wait.png";break;
		case 1:$img="status_service_run.png";break;
		case 2:$img="status_service_removed.png";break;
		case 3:$img="status_service_removed.png";break;
		}
		
		$package_name=package_name($ligne["files_id"]);
		$js="EditRemoteTask({$ligne["ID"]})";
	
		$html=$html . "
		<tr ". CellRollOver($js).">
			<td width=1%><img src='img/$img'></td>
			<td width=99%><strong style='font-size:12px'>$package_name</strong></td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","TaskRIDelete({$ligne["ID"]})")."</td>
		</tr>
		
		";
	}
	$html=$html."</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function add_task(){
	//$list=software_list();
	$add=Paragraphe("software-task-64.png","{ADD_NEW_DEPLOY_TASK}","{ADD_NEW_DEPLOY_TASK_TEXT}","javascript:AddRemoteTask()");
	$refresh=Paragraphe("64-refresh.png","{refresh}","{resfresh_tasks_list}","javascript:RefreshSoftwaresList()");
	
	
	
	if(!isset($_GET["taskid"])){$_GET["taskid"]=0;}
	if($_GET["taskid"]==0){
		$comp=new computers($_GET["uid"]);
		$ini=new Bs_IniHandler();
		$ini->loadString($comp->ComputerCryptedInfos);
		$username=$ini->_params["ACCOUNT"]["USERNAME"];
		$password=$ini->_params["ACCOUNT"]["PASSWORD"];
		$title="{ADD_NEW_DEPLOY_TASK}:: {$_GET["uid"]}";
	}else{
		$sql="SELECT * FROM deploy_tasks WHERE ID='{$_GET["taskid"]}'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$username=$ligne["username"];
		$password=$ligne["password"];
		$commandline=$ligne["commandline"];
		$debug_mode=$ligne["debug_mode"];
		$files_id=$ligne["files_id"];
		$title="{task}:: {$_GET["taskid"]}";
		$results=$ligne["results"];
		$executed=$ligne["executed"];
		if($executed>0){
			$change_status=Paragraphe32("restart_task","restart_task_text","RestartTask({$_GET['taskid']})","32-redo.png",190);
		}
	}
	
	$tbl=explode("\n",$results);
	if(is_array($tbl)){
		while (list ($num, $line) = each ($tbl) ){
			$logs=$logs."<code style='font-size:10px'>$line</code>";
		
		}
	}
	
	$list=task_dropdown($files_id);
	if($files_id>0){
		$sql="SELECT commandline,description FROM files_storage WHERE id_files=$files_id";
		$ligne2=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$default_commandline=$ligne2["commandline"];
		$description=htmlspecialchars(utf8_decode($ligne2["description"]));
	}
	
	
	$form="
	<div id='taskdiv'>
	<table style='width:99%' class=table_form>
	<tr>
	<td valign='top' class=legend>{package}:</td>
	<td>$list p.$files_id</td>
	<tr>
	<td valign='top' class=legend></td>
		<td ><p class=caption>$description</p></td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{commandline}:</td>
	<td>". Field_text("commandline",$commandline)."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{default}:</td>
	<td><span id='default_commandline'><code>$default_commandline</code></span></td>
	</tr>	
	<tr>
	<td valign='top' class=legend nowrap>{runas}:</td>
	<td>". Field_text("username",$username)."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{password}:</td>
	<td>". Field_password("password",$password)."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{debug_mode}:</td>
	<td>". Field_checkbox("debug_mode",1,$debug_mode)."</td>
	</tr>		
	<tr>
	
	
	
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:SaveTask('{$_GET["taskid"]}');\" value=\"{edit}&nbsp;&raquo;\"></td>
	</tr>		
	</table>	
	</div>
	";
	
	$html="<H1>$title</H1>
	<table style='width:99%'>
	<tr>
	<td valign='top'>
		
		<table style='width:99%'>
			<tr><td valign='top' width=99%><p class=caption>{ADD_NEW_DEPLOY_TASK_TEXT}</p><p class=caption>{remote_install_text}</p></td>
			<td valign='top'>$change_status</td>
			</tr>
		</table>
		". RoundedLightWhite("<div id='ri_list' style='width:100%;'>$form</div>")."
		<hr>
		". RoundedLightWhite("<div id='ri_logs' style='width:100%;height:250px;overflow:auto'>$logs</div>")."
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'storage.center.php');	
	
}

function task_dropdown($files_id=null){
	
$q=new mysql();
	$sql="SELECT * FROM files_storage ORDER BY filesize DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["id_files"]]=$ligne["filename"];
	}
	$array[null]="{select}"; 
	return Field_array_Hash($array,'file_id',$files_id,"TaskPackageSelected()");
	
}

function edit_task(){
	$taskid=$_GET["EditTask"];
	$commandline=$_GET["commandline"];
	$file_id=$_GET["file_id"];
	$username=$_GET["username"];
	$password=$_GET["password"];
	$uid=$_GET["uid"];
	$debug_mode=$_GET["debug_mode"];
	
	$sql_insert="INSERT INTO deploy_tasks (files_id,computer_id,commandline,username,password,debug_mode)
	VALUES('$file_id','$uid','$commandline','$username','$password','$debug_mode');
	";
	
	$sql_update="UPDATE deploy_tasks SET files_id='$file_id',commandline='$commandline',username='$username',
	password='$password',debug_mode='$debug_mode' WHERE ID=$taskid";
	
	if($taskid>0){$sql=$sql_update;}else{$sql=$sql_insert;}
	
	$sock=new sockets();
	$sock->getfile('LaunchRemoteInstall');
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	}



?>