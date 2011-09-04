<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ocs.inc');
	
	
	
	if(!Isright()){$tpl=new templates();echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";die();}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-index"])){popup_index();exit;}
	if(isset($_GET["popup-users"])){popup_users();exit;}
	if(isset($_GET["users-list"])){popup_users_list();exit;}
	if(isset($_GET["popup-disks"])){popup_disks();exit;}
	if(isset($_GET["popup-file"])){popup_file();exit;}
	if(isset($_GET["popup-events"])){popup_events();exit;}
	if(isset($_GET["popup-events-list"])){popup_events_list();exit;}
	
	
	if(isset($_GET["popup-backup-dir"])){popup_backup_dir();exit;}
	if(isset($_GET["NewBackupTask"])){popup_backup_save();exit;}
	if(isset($_GET["ChangeEnableTask"])){popup_task_enable();exit;}
	
	if(isset($_GET["popup-tasks"])){popup_tasks();exit;}
	if(isset($_GET["popup-tasks-list"])){popup_tasks_list();exit;}
	if(isset($_GET["popup-tasks-lists"])){popup_tasks_lists();exit;}
	
	
	if(isset($_GET["popup-ocs-soft"])){popup_ocs_softs();exit;}
	if(isset($_GET["popup-ocs-soft-list"])){popup_ocs_softs_list();exit;}
	
	if(isset($_GET["edit-task-id"])){popup_task_edit();exit;}
	if(isset($_GET["edit-task-popup"])){popup_task_edit_form();exit;}
	if(isset($_GET["SaveTaskID"])){popup_task_save();exit;}
	if(isset($_GET["RunComputerTaskID"])){popup_task_run();exit;}
	if(isset($_GET["DeleteComputerTaskID"])){popup_task_delete();exit;}
	if(isset($_GET["computer-task-list"])){popup_tasks_lists();exit;}
	if(isset($_GET["popup-parameters"])){popup_parameters();exit;}
	if(isset($_GET["popup-parameters-form"])){popup_parameters_form();exit;}
	if(isset($_GET["popup-shared"])){popup_shared();exit;}
	if(isset($_GET["popup-services"])){popup_services();exit;}
	if(isset($_GET["ScanComputer"])){popup_scan_computer();exit;}
	
	
	
	if(isset($_POST["dir"])){echo DirectoryListing();exit;}
	js();
	
	
function js(){
	
	$page=CurrentPageName();
	$uid=$_GET["uid"];
	$title=str_replace('$','',$_GET["uid"]);
	$pose=strpos($_GET["uid"],'$');
	if($pose==0){$uid=$uid.'$';}
	
	$html="
	function LoadComputerInfos(){
		YahooWin4(700,'$page?popup=yes&uid=$uid','$title');
	
	}
	
	function ComputerTasks(file){
		YahooWin5(500,'$page?popup-file=yes&uid=$uid&file='+file,'$title');
	}
	
	function ComputerAgentBackupDir(file_encoded){
		YahooWin6(500,'$page?popup-backup-dir=yes&uid=$uid&file='+file_encoded,'$title');
	}
	
	var x_RunComputerTaskID= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('computer_tasks_list','$page?computer-task-list=yes&uid=$uid');
			
			}
			
	var x_SaveComputerAgentParameters=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('form_comp_parameters','$page?popup-parameters-form=yes&uid=$uid');
			
			}

		
	
	function RunComputerTaskID(ID,MAC){
			var XHR = new XHRConnection();
			XHR.appendData('RunComputerTaskID',ID);
			XHR.appendData('uid','$uid');
			XHR.appendData('MAC',MAC);
			XHR.sendAndLoad('$page', 'GET',x_RunComputerTaskID);
			}	
			
	function DeleteComputerTaskID(ID,MAC){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteComputerTaskID',ID);
			XHR.appendData('uid','$uid');
			XHR.appendData('MAC',MAC);
			XHR.sendAndLoad('$page', 'GET',x_RunComputerTaskID);	
	}
	
	function SaveComputerAgentParameters(MAC){
			var XHR = new XHRConnection();
			XHR.appendData('SendNoop',document.getElementById('SendNoop').value);
			XHR.appendData('SendDirs',document.getElementById('SendDirs').value);
			XHR.appendData('MAC',MAC);
			XHR.appendData('uid','$uid');
			document.getElementById('form_comp_parameters').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_SaveComputerAgentParameters);
			}
	
	function initBrowserComp(disk){
			$(document).ready( function() {
			    $('#comp_browser').fileTree(
			    	{ root: disk ,multiFolder:false,script: '$page?uid=$uid&drive='+disk,
					folderEvent: 'click', 
					expandSpeed: 750, 
					collapseSpeed: 750, 
					expandEasing: 'easeOutBounce', 
					collapseEasing: 'easeOutBounce', 
					loadMessage: 'Un momento...' 
					},
			    	function(file) {ComputerTasks(file);}
			    	);
			});
		}	
	
	LoadComputerInfos();
	";
	
	echo $html;
	
}

function popup_task_edit(){
	$ID=$_GET["edit-task-id"];
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{edit} {task}:$ID");
	
	$html="
		function LoadTaskToEdit(){
			YahooWin5('550','$page?edit-task-popup=$ID&uid={$_GET["uid"]}&MAC={$_GET["MAC"]}','$title');
		}
		
	var x_TaskEditSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		LoadTaskToEdit();	
		}	
			
		function TaskEditSave(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('SaveTaskID','$ID');
			XHR.appendData('schedule',document.getElementById('schedule').value);
			XHR.appendData('task_type',document.getElementById('task_type').value);
			XHR.appendData('path',document.getElementById('path').value);
			XHR.appendData('MAC',document.getElementById('MAC').value);
			document.getElementById('TASK_ID_$ID').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_TaskEditSave);
				
		}
	LoadTaskToEdit();";
	echo $html;
	
}

function popup_task_delete(){
	$ID=$_GET['DeleteComputerTaskID'];
	$sql="DELETE FROM computers_tasks WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__."\n$sql";}
	if($_GET["MAC"]==null){return;}
if($_SESSION["uid"]==-100){$username="Manager";}else{
		$u=new user($_SESSION["uid"]);
		$username=$u->DisplayName;
	}
	
	$text="{artica_agent_task_deleted} {task}:$ID {username}: $username";
	$text=addslashes($text);
	$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUE('{$_GET["MAC"]}',NOW(),'admin','$text')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;}	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}");	
}




function popup_task_run(){
	$ID=$_GET['RunComputerTaskID'];
	$sql="INSERT computers_orders (taskid,task_type,MAC) VALUES('$ID','runtask','{$_GET["MAC"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__."\n$sql";}
	if($_GET["MAC"]==null){return;}
if($_SESSION["uid"]==-100){$username="Manager";}else{
		$u=new user($_SESSION["uid"]);
		$username=$u->DisplayName;
	}
	
	$text="{artica_agent_task_ordered} {task}:$ID {username}: $username";
	$text=addslashes($text);
	$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUE('{$_GET["MAC"]}',NOW(),'admin','$text')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;}	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}");
	
}



function popup_task_save(){
	$ID=$_GET["SaveTaskID"];
	$path=base64_encode($_GET["path"]);
	
	$sql="UPDATE computers_tasks SET schedule='{$_GET["schedule"]}', task_type='{$_GET["task_type"]}',path='$path' WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;}
	if($_GET["MAC"]==null){return;}


	if($_SESSION["uid"]==-100){$username="Manager";}else{
		$u=new user($_SESSION["uid"]);
		$username=$u->DisplayName;
	}
	
	$text="{artica_agent_task_edited} {task}:$ID {username}: $username";
	$text=addslashes($text);
	$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUE('{$_GET["MAC"]}',NOW(),'admin','$text')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;}
}

function popup_task_edit_form(){
	$ID=$_GET["edit-task-popup"];
	$sql="SELECT * FROM computers_tasks WHERE ID=$ID";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$path=base64_decode($ligne["path"]);
	$path=stripslashes($path);
	$MAC=$_GET["MAC"];
	$array_tasks=array("backup"=>"{backup}");
	$time_array=array(null=>"{select}",5=>"5mn","10"=>"10mn","15"=>"15mn","30"=>"30mn","60"=>"1h","120"=>"2h",180=>"3h",240=>"4h");
	
	$html="
	<div id='TASK_ID_$ID'>
	<input type='hidden' id='MAC' value='$MAC'>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/folder-tasks2-64.png'>
	</td>
	<td valign='top'>
	<table class=table_form>
	<tr>
		<td class=legend>{task}:</td>
		<td>". Field_array_Hash($array_tasks,"task_type",$ligne["task_type"])."</td>
	</tr>
	<tr>
		<td class=legend>{folder}:</td>
		<td>". Field_text("path",$path)."</td>
	</tr>	
	<tr>
		<td class=legend>{every}:</td>
		<td>". Field_array_Hash($time_array,"schedule",$ligne["schedule"])."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>
			". button("{edit}","TaskEditSave()")."
		</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


	
function IsRight(){
	if(!isset($_GET["uid"])){return false;}
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsSambaAdministrator){return true;}
	if($users->AllowAddUsers){return true;}
	if($users->AllowManageOwnComputers){return true;}
	if($users->AsInventoryAdmin){return true;}
	return false;
	}
	
	function IsRightOnlyAdmin(){
	if(!isset($_GET["uid"])){return false;}
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsSambaAdministrator){return true;}
	if($users->AllowAddUsers){return true;}
	return false;
	}	

function popup(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$sock=new sockets();
	$EnableArticaRemoteAgent=$sock->GET_INFO("EnableArticaRemoteAgent");
	if($EnableArticaRemoteAgent==null){$EnableArticaRemoteAgent=0;}
	
	$a[]="<li><a href=\"$page?popup-index=yes&uid={$_GET["uid"]}\"><span>{index}</span></a></li>";
	if($users->AllowAddUsers){
		$a[]="<li><a href=\"$page?popup-users=yes&uid={$_GET["uid"]}\"><span>{users}</span></a></li>";
	}
	$a[]="<li><a href=\"$page?popup-services=yes&uid={$_GET["uid"]}\"><span>{services}</span></a></li>";
	
	
	if($EnableArticaRemoteAgent==1){
		$a[]="<li><a href=\"$page?popup-parameters=yes&uid={$_GET["uid"]}\"><span>{parameters}</span></a></li>";
		$a[]="<li><a href=\"$page?popup-tasks=yes&uid={$_GET["uid"]}\"><span>{tasks}</span></a></li>";
		$a[]="<li><a href=\"$page?popup-events=yes&uid={$_GET["uid"]}\"><span>{events}</span></a></li>";
		$a[]="<li><a href=\"$page?popup-disks=yes&uid={$_GET["uid"]}\"><span>{browse}</span></a></li>";
	}
	
	$a[]="<li><a href=\"$page?popup-shared=yes&uid={$_GET["uid"]}\"><span>{shared_folders}</span></a></li>";
	
	
	
	$html="
	<div id='container-computerinfos-tabs' style='background-color:white'>
	<ul>
		". implode("\n",$a)."
	</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#container-computerinfos-tabs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>
	
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_index(){
	$computer=new computers($_GET["uid"]);
	$computer->ComputerMacAddress=str_replace("-",":",$computer->ComputerMacAddress);
	$page=CurrentPageName();
	
	$html="<H1>$computer->DisplayName</H1>
	
	<table style='width:100%' class=table_form>
	<tr>
	<td valign='top' width=1%><img src='img/computer-tour-128.png' id='computer-logo'></td>
	<td valign='top'>
		<table style='width:100%'>
				<tr>
					<td class=legend nowrap>{computer_ip}:</strong></td>
					<td align=left style='font-size:11px'>$computer->ComputerIP</strong></td>
				</tr>			
				<tr>
					<td class=legend>{ComputerMacAddress}:</strong></td>
					<td align=left style='font-size:11px'>$computer->ComputerMacAddress</strong></td>
				</tr>	
				<tr>
					<td class=legend>{uid_number}:</strong></td>
					<td align=left style='font-size:11px'><strong>$computer->uidNumber</strong></td>
				</tr>			
				<tr>
					<td class=legend>{ComputerMachineType}:</strong></td>
					<td align=left style='font-size:11px'><strong>$computer->ComputerMachineType</strong></td>
				</tr>									
				<tr>
					<td class=legend>{ComputerOS}:</strong></td>
					<td align=left style='font-size:11px'><strong>$computer->ComputerOS</strong></td>
				</tr>				
				<tr>
					<td class=legend>{ComputerRunning}:</strong></td>
					<td align=left style='font-size:11px'><strong>$computer->ComputerRunning</strong></td>
				</tr>			
				<tr>
					<td class=legend>{ComputerUpTime}:</strong></td>
					<td align=left style='font-size:11px'><strong>$computer->ComputerUpTime</strong></td>
				</tr>
				<tr>
					<td colspan=2 align='right'>
					<hr>
					". button("{scan_this_computer}","ScanComputer()")."</td>
				</tr>
				
				
		</table>		
		
	</td>
	</tr>
	</table>
	<script>
		
	var x_ScanComputer= function (obj) {
		var results=obj.responseText;
		document.getElementById('computer-logo').src='img/computer-tour-128.png';
		if(results.length>0){alert(results);}
		RefreshTab('container-computerinfos-tabs');
		}	
			
		function ScanComputer(){
			var XHR = new XHRConnection();
			XHR.appendData('ScanComputer','{$_GET["uid"]}');
			XHR.appendData('uid','{$_GET["uid"]}');
			document.getElementById('computer-logo').src='img/wait_verybig.gif';	
			XHR.sendAndLoad('$page', 'GET',x_ScanComputer);
				
		}
	</script>	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_scan_computer(){
	$sock=new sockets();
	$datas = $sock->getFrameWork("cmd.php?nmap-scan={$_GET["uid"]}");
	echo $datas;
	
}


function popup_users(){
	$computer=new computers($_GET["uid"]);
	$page=CurrentPageName();
	
	
	$html="<H1>$computer->DisplayName::{users}</H1>
	<div style='width:100%;height:250px;overflow:auto' id='users-list-comp'></div>
	
	<script>
		function RefreshCompInfosListUser(){
			LoadAjax('users-list-comp','$page?users-list=yes&uid={$_GET["uid"]}');
		}
		
		function DeleteComputerDN(base){
			LoadAjax('users-list-comp','$page?users-list=yes&uid={$_GET["uid"]}&deletedn='+base);
		}
		
		
		
		RefreshCompInfosListUser();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function popup_users_list(){
$computer=new computers($_GET["uid"]);	
$tpl=new templates();
if($computer->ComputerMacAddress==null){$computer->ComputerMacAddress=$_GET["uid"];}

if(isset($_GET["deletedn"])){
	if(IsRightOnlyAdmin()){
		$basedn=base64_decode($_GET["deletedn"]);
		$ldap=new clladp();
		$ldap->ldap_delete($basedn);
	}
}

echo $tpl->_ENGINE_parse_body("<H3 style='margin-bottom:8px'>{ComputerMacAddress}:$computer->ComputerMacAddress</H3>");
$dn=$userid->dn;
		$ldap=new clladp();
		$pattern="(&(objectClass=ComputerAfectation)(cn=$computer->ComputerMacAddress))";
		$attr=array();
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
		if(!$sr){return null;}
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		if($hash["count"]==0){return;}
		
		
		
		for($i=0;$i<$hash["count"];$i++){
			$basedn=base64_encode($hash[$i]["dn"]);
			$js="javascript:DeleteComputerDN('$basedn');";
			if(preg_match("#cn=hosts,cn=(.+?),ou=users,ou=(.+?),dc=organizations#",$hash[$i]["dn"],$re)){
				$tbl[]="<div style='float:left'>".Paragraphe("user-server-64-delete.png",$re[1],"{organization}: {$re[2]}",$js)."</div>";
				
			}
			
		}

		
		
		
		
		
			
		if(!is_array($tbl)){return null;}
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(implode("\n",$tbl));
}


function popup_disks(){
	$page=CurrentPageName();
	$computer=new computers($_GET["uid"]);	
	$MAC=$computer->ComputerMacAddress;
	$MAC=str_replace("-",":",$MAC);
	$sql="SELECT letter from computers_drives WHERE MAC='$MAC' GROUP BY letter";
	$q=new mysql();
	$menus=array();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$menus[]=Paragraphe("disk-64.png","{disk}:{$ligne["letter"]}","{browse} {disk}:{$ligne["letter"]}","javascript:initBrowserComp('{$ligne["letter"]}')");
	}
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>". implode("\n",$menus)."</td>
		<td valign='top' width=100%><div id='comp_browser' style='height:450px;overflow:auto'></td>
	</tR>
	</table>
";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function DirectoryListing(){
	$dir=$_POST["dir"];
	$dir=urldecode($dir);
	if(preg_match("#^[A-Z]$#",$dir)){
		DirectoryListing_disk($dir);
		exit;
	}
	
	if(preg_match("#^[A-Za-z]:\\\#",$dir)){
		DirectoryListing_dir($dir);
		exit;
	}
	
	echo "<H1>FAILED $dir</h1>";
	
}
function DirectoryListing_disk($drive){
	$computer=new computers($_GET["uid"]);
	$tpl=new templates();	
	$MAC=$computer->ComputerMacAddress;
	$MAC=str_replace("-",":",$MAC);	
	$sql="SELECT letter,path from computers_drives WHERE MAC='$MAC' AND letter='{$_GET["drive"]}'";
	$patern="#^$drive.+?\\\(.+?)\\\#";
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$settings=$tpl->_ENGINE_parse_body("{settings}");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$path=$ligne["path"];
		//echo $path;
		if(preg_match($patern,$path,$re)){
			$array[$re[1]]=true;
		}
	}
	
	while (list($num,$val)=each($array)){
		
		echo "<li class=\"directory collapsed\"><a href=\"#\" rel='" . htmlentities("$drive:\\$num/") . "'>" . htmlentities($num) . "</a></li>";
		echo "<li class=\"file ext_settings\"><a href=\"#\" rel=\"" . base64_encode("$drive:\\$num") . "\">". htmlentities("$num - $settings")."</a></li>";
	}
	
	echo "</ul>";
}
function DirectoryListing_dir($drive){
	$drive=str_replace("/",'\\',$drive);
	$drive_pattern=str_replace("\\","\\\\",$drive);
		$tpl=new templates();	
	$computer=new computers($_GET["uid"]);	
	$MAC=$computer->ComputerMacAddress;
	$MAC=str_replace("-",":",$MAC);	
	$sql="SELECT letter,path from computers_drives WHERE MAC='$MAC' AND letter='{$_GET["drive"]}'";
	$settings=$tpl->_ENGINE_parse_body("{settings}");
	
	$patern="#^$drive_pattern(.+?)\\\#";
	$patern_end="#^$drive_pattern(.+?)$#";
	//echo $patern;
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$path=trim($ligne["path"]);
		
		//echo $path;
		if(preg_match($patern,$path,$re)){
			$array[$re[1]]=true;
			continue;
			}
		if(preg_match($patern_end,$path,$re)){
			$array[$re[1]]=true;
			continue;
			}			
		
	}
	
	while (list($num,$val)=each($array)){
		echo "<li class=\"directory collapsed\"><a href=\"#\" rel='" . htmlentities("$drive$num/") ."'>". htmlentities($num) . "</a></li>\n";
		echo "<li class=\"file ext_settings\"><a href=\"#\" rel=\"" . base64_encode("$drive$num") . "\">". htmlentities("$num - $settings")."</a></li>";
	}
	
	echo "</ul>";
}

function popup_file(){
	$_GET["file"]=base64_decode($_GET["file"]);
	$file=base64_encode($_GET["file"]);
	$backup=Paragraphe("storage-64.png",'{backup}','{backup_artica_agent_text}',"javascript:ComputerAgentBackupDir('$file')");	
	$html="
	<div style='font-size:16px;color:black'>{$_GET["file"]}</div>
	<table style='width:100%'>
		<tr>
			<td valign='top'>$backup</td>
		</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightWhite($html));
	
}
function popup_backup_dir(){
$_GET["file"]=base64_decode($_GET["file"]);
	$file=base64_encode($_GET["file"]);
	
	$time_array=array(null=>"{select}",5=>"5mn","10"=>"10mn","15"=>"15mn","30"=>"30mn","60"=>"1h","120"=>"2h",180=>"3h",240=>"4h");
	$md=md5($file);	
	$page=CurrentPageName();
	$html="
	<script>
	
	var x_AddTask$md= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		YahooWin6Hide();	
		}	
			
		function AddTask$md(){
			var XHR = new XHRConnection();
			XHR.appendData('NewBackupTask','$file');
			XHR.appendData('schedule',document.getElementById('pool').value);
			XHR.appendData('uid','{$_GET["uid"]}');
			document.getElementById('$md').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_AddTask$md);
				
		}
	
	</script>
	<div style='font-size:16px;color:black'>{new_task}::{$_GET["file"]}</div>
	<div id='$md'>
	<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='img/storage-128.png'></td>
			<td valign='top'><p class=caption>{backup_agent_dir_explain}</p>
				<table style='width:100%' class=table_form>
				<tr>	
					<td valign='top' class=legend>{run_every}</td>
					<td valign='top'>". Field_array_Hash($time_array,"pool",null)."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'>". button("{save_task}","AddTask$md()")."
					</td>
				</tr>
				</table>
			</td>
			
		</tr>
		<tr>
		
	</table>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(RoundedLightWhite($html));	
}
function popup_backup_save(){
	$cmp=new computers($_GET["uid"]);
	$cmp->ComputerMacAddress=str_replace("-",":",$cmp->ComputerMacAddress);
	$sql="INSERT INTO computers_tasks(MAC,task_type,schedule,task_enabled,path) 
	VALUE('$cmp->ComputerMacAddress','backup','{$_GET["schedule"]}',1,'{$_GET["NewBackupTask"]}');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\nFile:".basename(__FILE__)."\nLine:".__LINE__;}
	
	
}

function popup_parameters(){
	echo "<div id='form_comp_parameters'>".popup_parameters_form(false)."</div>";	
}

function popup_parameters_form($echo=true){
$cmp=new computers($_GET["uid"]);
	$cmp->ComputerMacAddress=str_replace("-",":",$cmp->ComputerMacAddress);
	$sql="SELECT parameters FROM computers_parameters WHERE MAC='$cmp->ComputerMacAddress' LIMIT 0,1;";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$ligne["parameters"]=base64_decode($ligne["parameters"]);
	$ini=new Bs_IniHandler();
	$ini->loadString($ligne["parameters"]);
	
	$page=CurrentPageName();
	$time=array(null=>"{select}","2"=>"2mn",5=>"5mn","10"=>"10mn","15"=>"15mn","30"=>"30mn","60"=>"1h","120"=>"2h",180=>"3h",240=>"4h");
	if($ini->_params["CONF"]["SendNoop"]==null){$ini->_params["CONF"]["SendNoop"]=15;}
	if($ini->_params["CONF"]["SendDirs"]==null){$ini->_params["CONF"]["SendDirs"]=180;}
	
	$form="
	<table class=table_form>
		<tr>
			<td class=legend>{synchronize_each}:</td>
			<td>". Field_array_Hash($time,"SendNoop",$ini->_params["CONF"]["SendNoop"])."</td>
		</tr>
		<tr>
			<td class=legend>{synchronize_directorylist_each}:</td>
			<td>". Field_array_Hash($time,"SendDirs",$ini->_params["CONF"]["SendDirs"])."</td>
		</tr>		
		<tr>
			<td colspan=2 align='right'>". button("{edit}","SaveComputerAgentParameters('$cmp->ComputerMacAddress')")."</td>
		</tr>
		</table>";
	
	$form=RoundedLightWhite($form);
	
	
	$html="<H3>$cmp->ComputerMacAddress {parameters}</H3>
	$form
	";
	$tpl=new templates();
	if(!$echo){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


function popup_tasks(){
	$cmp=new computers($_GET["uid"]);
	$cmp->ComputerMacAddress=str_replace("-",":",$cmp->ComputerMacAddress);
	$sql="SELECT * FROM computers_tasks WHERE MAC='$cmp->ComputerMacAddress' ORDER BY ID DESC;";
	$page=CurrentPageName();
	$md=md5(__FUNCTION__);
	$html="
	<script>
	
	var ChangeEnableTask$md= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		LoadAjax('$md','$page?popup-tasks-list=yes&uid={$_GET["uid"]}');
		}	
			
		function ChangeEnableTask(taskid){
			var XHR = new XHRConnection();
			XHR.appendData('ChangeEnableTask',taskid);
			XHR.appendData('enable',document.getElementById('enable_task_'+taskid).value);
			XHR.appendData('uid','{$_GET["uid"]}');
			document.getElementById('$md').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',ChangeEnableTask$md);
				
		}
	
		LoadAjax('$md','$page?popup-tasks-list=yes&uid={$_GET["uid"]}');
	</script>	
	
	<div id='$md'></div>";
		
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
		
	
}

function popup_events(){
	$page=CurrentPageName();
	$html="
	<div id='popup-events' style='width:100%;height:450px;overflow:auto'>
	
	</div>
	<script>
		function EmptyBackupEventsList(){
			LoadAjax('popup-events','$page?popup-events-list=yes&uid={$_GET["uid"]}&delete-all=yes');
		}
		
		function RefreshBackupEventsList(){
			LoadAjax('popup-events','$page?popup-events-list=yes&uid={$_GET["uid"]}');
		}		
	
	 LoadAjax('popup-events','$page?popup-events-list=yes&uid={$_GET["uid"]}');
		
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}


function popup_events_list(){
	$cmp=new computers($_GET["uid"]);
	$cmp->ComputerMacAddress=str_replace("-",":",$cmp->ComputerMacAddress);	
	
	if(isset($_GET["delete-all"])){
		$sql="DELETE FROM computers_events WHERE MAC='$cmp->ComputerMacAddress'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "$q->mysql_error";
		}
	}
	
	$sql="SELECT * ,DATE_FORMAT(zDate,'%D %H:%i:%s') as tdate FROM computers_events WHERE MAC='$cmp->ComputerMacAddress' ORDER BY ID DESC LIMIT 0,50";
	$page=CurrentPageName();
	
	$table_header="
	<H3>$cmp->ComputerIP ($cmp->ComputerMacAddress)</H3>
	<table style='width:100%'>
	<tr>
		<td>". button("{empty_list}","EmptyBackupEventsList()")."</td>
		<td>". button("{refresh}","RefreshBackupEventsList()")."</td>
	</tr>
	</table>
	
	<table class=table_form>
	<tr>
		<th>{date}</th>
		<th>{type}</th>
		<th>{events}</th>
	</tr>
	
	";
	
$q=new mysql();
	$tr=array();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	if($bg=="#cce0df"){$bg="#FFFFFF";}else{$bg="#cce0df";}
			//$ligne["events"]=stripslashes($ligne["events"]);
			$tr[]="
			<tr style='background-color:$bg'>
				<td style='padding:4px;font-size:11px' nowrap><strong style='text-transform:capitalize;'>{$ligne["tdate"]}</strong></td>	
				<td width=1% style='padding:4px;font-size:11px' ><strong style='text-transform:capitalize'>{$ligne["events_type"]}</strong></td>
				<td style='padding:4px;font-size:11px'><code style='font-size:10px'>{$ligne["events"]}</code></td>
			</tr>
			";
		}
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$table_header".implode("\n",$tr)."</table>");		
}


function popup_tasks_list(){
		$page=CurrentPageName();
	$html="
	<div id='computer_tasks_list'>
	". popup_tasks_lists(true)."
	</div>";
	echo $html;
}

function popup_tasks_lists($return=false){
	$cmp=new computers($_GET["uid"]);
	$cmp->ComputerMacAddress=str_replace("-",":",$cmp->ComputerMacAddress);
	$sql="SELECT * FROM computers_tasks WHERE MAC='$cmp->ComputerMacAddress' ORDER BY ID DESC;";
	$page=CurrentPageName();
	$md=md5(__FUNCTION__);	
	
	$html="
	<input type='hidden' id='MAC' value='$cmp->ComputerMacAddress'>

	<table class=table_form width=99.5%>
	<tr>
		<th>{type}</th>
		<th>{schedule}</th>
		<th>{path}</th>
		<th>{run}</th>
		<th>{edit}</th>
		<th width=1%>{enabled}</td>
		<th>{delete}</th>
	</tr>
	
	";
	$q=new mysql();
	$tr=array();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$path=base64_decode($ligne["path"]);
		$path=str_replace("\\\\","\\",$path);
		//$path=stripslashes($path);
			$tr[]="
			<tr>
				<td width=1% style=';font-size:11px'><strong style='text-transform:capitalize'>{$ligne["task_type"]}</strong></td>
				<td style=';font-size:11px' nowrap><strong style='text-transform:capitalize'>{every}: {$ligne["schedule"]} mn</strong></td>
				<td style=';font-size:11px'><code style='font-size:10px'>$path</code></td>
				
				
				<td width=1%>". imgtootltip("run-24.png","{run}","RunComputerTaskID({$ligne["ID"]},'$cmp->ComputerMacAddress')"). "</td>
				<td width=1%>". imgtootltip("24-administrative-tools.png","{edit}","Loadjs('$page?edit-task-id={$ligne["ID"]}&uid={$_GET["uid"]}&MAC=$cmp->ComputerMacAddress')"). "</td>
				<td width=1% align=center style=';font-size:11px'>". Field_checkbox("enable_task_{$ligne["ID"]}",1,$ligne["task_enabled"],"ChangeEnableTask({$ligne["ID"]})")."</td>
				<td width=1%>". imgtootltip("delete-24.png","{delete}","DeleteComputerTaskID({$ligne["ID"]},'$cmp->ComputerMacAddress')"). "</td>
			</tr>
			";
		}
	
	
	$tpl=new templates();
	if($return){return $tpl->_ENGINE_parse_body($html.implode("\n",$tr)."</table>");}
	echo $tpl->_ENGINE_parse_body($html.implode("\n",$tr)."</table>");		
}

function popup_task_enable(){
	$ID=$_GET["ChangeEnableTask"];
	$enable=$_GET["enable"];
	if($enable==1){$enable=0;}else{$enable=1;}
	$sql="UPDATE computers_tasks SET task_enabled=$enable WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql);
	}
	
function popup_shared(){
	$uid=$_GET["uid"];
	$computer=new computers($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	$username=base64_encode($ini->_params["ACCOUNT"]["USERNAME"]);
	$password=base64_encode($ini->_params["ACCOUNT"]["PASSWORD"]);
	$computername=$computer->ComputerRealName;
	$computer_ip=gethostbyname($computername);
	if(!preg_match("#[0-9\.]+#",$ip)){$ip=$computer->ComputerIP;}
	$sock=new sockets();
	if(!preg_match("#[0-9\.]+#",$ip)){$ip=$computername;}
	$users=new usersMenus();
	
	$receive=base64_decode($sock->getFrameWork("cmd.php?smbclientL=$ip&user=$username&password=$password"));
	
	$datas=unserialize($receive);
	$password = Paragraphe ( "cyrus-password-64.png", "{credentials_informations}", "{credentials_informations_text}", "javascript:Loadjs('computer.passwd.php?uid={$_GET["uid"]}')" );
	$html="
	<table style='width:100%'>
	<td valign='top'>
		<div id='computer-browser-start' style='height:250px;overflow:auto'>
	<table style='width:100%'>";
	if(is_array($datas)){
	while (list ($folder, $array) = each ($datas) ){
		$folder=trim($folder);
		$img="shared.png";
		$folderB64=base64_encode($folder);
		$backup=imgtootltip("32-backup.png","{backup_this_folder}","Loadjs('domains.computer.backuppc.php?uid=". base64_encode($uid)."&add-shared=$folderB64')");
		
		if($array["TYPE"]=="Printer"){
			$img="32-printer-connected.png";
			$backup=null;
		}
		if($folder=="print$"){$img="32-printer-connected.png";$backup=null;}
		if(!$users->BACKUPPC_INSTALLED){$backup=null;}
		
		$html=$html."
		<tr ". CellRollOver("","{$array["INFOS"]}").">
			<td width=1%><img src='img/$img'></td>
			<td nowrap style='font-size:14px'>$folder</td>
			<td nowrap style='font-size:14px'>{$array["TYPE"]}</td>
			<td width=1%>$backup</td>
		</tr>
		";
	}}
	$html=$html."</table>
	</div>
	</td>
	<td width=1% valign='top'>
	$password
	</td>
	</tr>
	</table>";
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function popup_services(){
	$uid=$_GET["uid"];
	if(strpos("$uid","$")==0){$uid=$uid."$";}
	$users=new usersMenus();
	$page=CurrentPageName();
	$sock=new sockets();
	$backuppc=Paragraphe("64-backup-grey.png","{APP_BACKUPPC}","{BACKUP_COMPUTER_TEXT}");
	$network=Paragraphe("64-computers-parameters-grey.png","{COMPUTER_NETWORK}","{COMPUTER_NETWORK_TEXT}");
	
	
	
	
	if($users->BACKUPPC_INSTALLED){
		$EnableBackupPc=$sock->GET_INFO("EnableBackupPc");
		if(!is_numeric($EnableBackupPc)){$EnableBackupPc=0;}
		if($EnableBackupPc==1){
			$backuppc=Paragraphe("64-backup.png","{APP_BACKUPPC}","{BACKUP_COMPUTER_TEXT}","javascript:Loadjs('domains.computer.backuppc.php?uid=". base64_encode($uid)."')");
		}
	}
	
	if($users->AllowAddUsers){
		$js="javascript:YahooUser(870,'domains.edit.user.php?userid=$uid&ajaxmode=yes','$uid');";
		$network=Paragraphe("64-computers-parameters.png","{COMPUTER_NETWORK}","{COMPUTER_NETWORK_TEXT}",$js);
	}
	
	
	$tr[]=$backuppc;
	$tr[]=$network;
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==1){$t=0;$tables[]="</tr><tr>";}
		}

if($t<1){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	


$page=CurrentPageName();
	$html="
	
	
	<div style='width:100%'>
		<table style='width:100%'>
			<tr>
				<td valign='top' width=1%>$backuppc<br>$network</td>
				<td valign='top'><div id='ocs_softs' style='width:100%;height:350px;overflow:auto'></div></td>
			</tr>
		</table>
	</div>
	
	<script>
		LoadAjax('ocs_softs','$page?popup-ocs-soft=yes&uid=$uid');
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function popup_ocs_softs(){
	$uid=$_GET["uid"];
	$comp=new computers($uid);
	$page=CurrentPageName();
	$ocs=new ocs();
	$tpl=new templates();
	$HARDWARE_ID=$ocs->GET_HARDWARE_ID_FROM_MAC($comp->ComputerMacAddress);
	if($HARDWARE_ID<1){
		echo $tpl->_ENGINE_parse_body("
		
		<H3 style='color:#C42626'>{NO_OCS_AGENT_INSTALLED_OR_NO_COMMUNICATION}</H3>
		
		");
		return;
		
	}
	
	
	$sql="SELECT PUBLISHER FROM softwares WHERE HARDWARE_ID=$HARDWARE_ID GROUP BY HARDWARE_ID ORDER BY PUBLISHER";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"ocsweb");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$arraysofts[$ligne["PUBLISHER"]]=$ligne["PUBLISHER"];
		
	}
	$arraysofts[null]="{select}";
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px' valign=middle>{PUBLISHER}:</td>
		<td valign='top'>". Field_array_Hash($arraysofts,"PUBLISHER",null,"OCS_PUBLISHER_SELECT()",null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	</table>	
	<div style='width:100%;height:250px;overflow:auto' id='PUBLISHER_LIST'></div>
	
	<script>
		function OCS_PUBLISHER_SELECT(){
			var PUBLISHER=escape(document.getElementById('PUBLISHER').value);
			LoadAjax('PUBLISHER_LIST','$page?popup-ocs-soft-list=yes&uid=$uid&HARDWARE_ID=$HARDWARE_ID&PUBLISHER='+PUBLISHER);
		}
		
		OCS_PUBLISHER_SELECT();
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}
function popup_ocs_softs_list(){
	$uid=$_GET["uid"];
	$page=CurrentPageName();
	$ocs=new ocs();
	$tpl=new templates();
	$PUBLISHER=$_GET["PUBLISHER"];
	$HARDWARE_ID=$_GET["HARDWARE_ID"];
	if($HARDWARE_ID<1){
		$comp=new computers($uid);
		$HARDWARE_ID=$ocs->GET_HARDWARE_ID_FROM_MAC($comp->ComputerMacAddress);
	}

	$sql="SELECT NAME,VERSION FROM softwares WHERE HARDWARE_ID=$HARDWARE_ID AND PUBLISHER='$PUBLISHER'";
	if($PUBLISHER==null){
		$sql="SELECT NAME,VERSION FROM softwares WHERE HARDWARE_ID=$HARDWARE_ID LIMIT 0,50";	
	}
	
	$html="
	<table class=tableView style='width:95%'>
		<thead class=thead>
			<tr>
				<th width=1% nowrap colspan=2>{software}:</td>
				<th width=1% nowrap>{version}:</td>
			</tr>
		</thead>";		
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){$html="<code style='color:red'>$q->mysql_error<br>$sql</code>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
			$ligne["NAME"]=utf8_encode($ligne["NAME"]);
		$html=$html."
		<tr class=$cl> 
			<td valign='middle' width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:12px'>{$ligne["NAME"]}</td>
			<td valign='middle' width=1% nowrap><strong>{$ligne["VERSION"]}</td>
		</tr>";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


	
	
?>