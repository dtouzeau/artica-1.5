<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	
	$users=new usersMenus();
	if(!$users->AsOrgAdmin){
			$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			echo "alert('$error')";
			die();
		}
		
	if(isset($_GET["status"])){status();exit;}	
	if(isset($_GET["popup"])){popup();exit;}
	
	if(isset($_GET["task"])){task();exit;}
	if(isset($_GET["tasks-list"])){task_list();exit;}
	if(isset($_GET["IMPORTATION_FILE_PATH"])){MIGRATION_CREATE_USERS();exit;}
	if(isset($_GET["RELAUNCH_TASKS"])){MIGRATION_RELAUNCH_TASKS();exit;}
	if(isset($_GET["DELETE_TASK"])){MIGRATION_DELETE_TASK();exit;}
	if(isset($_GET["RELOAD_MEMBERS"])){MIGRATION_RELAUNCH_MEMBERS();exit;}
	
	if(isset($_GET["users"])){USERS_POPUP();exit;}
	if(isset($_GET["users-list"])){USERS_POPUP_LIST();exit;}
	if(isset($_GET["users-events"])){USERS_EVENTS();exit;}
	if(isset($_GET["RESTART_MEMBERS"])){MIGRATION_RESTART_MEMBERS();exit;}
js();




function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{MAILBOXES_MIGRATION}");
	$html="
		YahooWin3(650,'$page?popup=yes&ou={$_GET["ou"]}','$title');
		
		function MigrShowLogs(MD){
			YahooWin4(550,'$page?users-events='+MD+'&ou={$_GET["ou"]}',MD);
		}
		
	var x_MigrationImportDatas=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_migrmbx');
		
	}			
	
	";
	
	echo $html;
	}
	
function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	
	$array["task"]="{create_task}";
	$array["users"]="{users}";

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_migrmbx style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_migrmbx').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";			
}	

function status(){
	
}
	
function task(){
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();	
	$ldap=new clladp();
	$domains=$ldap->hash_get_domains_ou($ou);
	
	
	
	$html="
	<div class=explain>{MAILBOXES_MIGRATION_EXPLAIN}</div>
	<div id='import-task'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{domain}:</td>
		<td>". Field_array_Hash($domains,"domain",null,null,null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend nowrap>{file_path}:</td>
		<td width=99%>". Field_text("IMPORTATION_FILE_PATH","",'width:85%;font-size:13px;padding:3px'). " </td>
		<td width=1%><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('tree.php?select-file=txt&target-form=IMPORTATION_FILE_PATH');\"></td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button("{import_datas}","MigrationImportDatas()")."</td>
	</tr>
	</table>
	</div>
	
	<div id='taskslistMigr' style='height:200px;overflow:auto;margin-top:8px'></div>
	
	
	<script>

	
	function MigrationImportDatas(){
		var XHR = new XHRConnection();
		XHR.appendData('IMPORTATION_FILE_PATH',document.getElementById('IMPORTATION_FILE_PATH').value);
		XHR.appendData('domain',document.getElementById('domain').value);
		XHR.appendData('ou','{$_GET["ou"]}');		
		document.getElementById('import-task').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET', x_MigrationImportDatas);			 
	}
	
	function LauchTasks(){
		var XHR = new XHRConnection();
		XHR.appendData('RELAUNCH_TASKS','yes');
		document.getElementById('import-task').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET', x_MigrationImportDatas);			 
	}	
	
	function TaskListsRefresh(){
		LoadAjax('taskslistMigr','$page?tasks-list=yes&ou={$_GET["ou"]}');
	}
	
	function TaskMigrDelete(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DELETE_TASK',ID);
		XHR.appendData('ou','{$_GET["ou"]}');		
		document.getElementById('import-task').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET', x_MigrationImportDatas);	
	}
		
	function ReloadMembers(){
		var XHR = new XHRConnection();
		XHR.appendData('RELOAD_MEMBERS','yes');
		document.getElementById('users-popup-list').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET', x_MigrationImportDatas);			
	}	
	
	function RestartMembers(){
		var XHR = new XHRConnection();
		XHR.appendData('RESTART_MEMBERS','yes');
		XHR.appendData('ou','{$_GET["ou"]}');	
		document.getElementById('users-popup-list').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET', x_MigrationImportDatas);			
	}		
	
	

	
	TaskListsRefresh();
</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function task_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$ou=base64_decode($_GET["ou"]);
	$sql="SELECT * FROM mbx_migr WHERE ou='$ou'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H3>$q->mysql_error</h3>";}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=3>$ou</th>
	<th>{imported}</th>
	<th>{terminated}</th>
	<th>{members}</th>
	<th>&nbsp;</th>
	<th>{delete}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$local_domain=$ligne["local_domain"];
			$filename=basename($ligne["filepath"]);
			$delete=imgtootltip("delete-24.png","{delete}","TaskMigrDelete('{$ligne["ID"]}')");
			if($ligne["imported"]==0){$imported="danger24.png";}else{$imported="ok24.png";}
			if($ligne["finish"]==0){$finish="danger24.png";}else{$finish="ok24.png";}
			$relaunch=null;
			if($ligne["members_count"]<1){$relaunch=imgtootltip("task-run.gif","{run}","LauchTasks()");}else{$relaunch="&nbsp;";}
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td align='center' width=99%><strong style='font-size:14px'>$local_domain</td>
			<td align='center'  width=99%><strong style='font-size:14px'>$filename</td>
			<td align='center' width=1%><strong style='font-size:14px'><img src='img/$imported'></td>
			<td align='center' width=1%><strong style='font-size:14px'><img src='img/$finish'></td>
			<td align='center' width=1%><strong style='font-size:14px'>{$ligne["members_count"]}</strong></td>
			<td align='center' width=1%>$relaunch</td>
			<td align='center' width=1%>$delete</td>
			</tr>
			";
			
		}
	
	
	$html=$html."</tbody></table>
	<div style='text-align:right;margin-top:8px'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_migrmbx')")."</div>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function MIGRATION_CREATE_USERS(){
	$path=$_GET["IMPORTATION_FILE_PATH"];
	$ou=base64_decode($_GET["ou"]);
	if($ou==null){echo "Organization is null !\n";return;}
	$local_domain=$_GET["domain"];
	$sql="INSERT INTO mbx_migr (ou,filepath,imported,finish,local_domain) VALUES ('$ou','$path','0','0','$local_domain')";
	$q=new mysql();
	$q->BuildTables();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mbx-migr-add-file=yes");
}
function MIGRATION_RELAUNCH_TASKS(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mbx-migr-add-file=yes");	
}

function MIGRATION_DELETE_TASK(){
	$ou=base64_decode($_GET["ou"]);
	if(!is_numeric($_GET["DELETE_TASK"])){echo "Not numeric!";return;}
	$sql="DELETE FROM mbx_migr WHERE ID='{$_GET["DELETE_TASK"]}' AND ou='$ou'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	$sql="DELETE FROM mbx_migr_users WHERE 	mbx_migr_id='{$_GET["DELETE_TASK"]}' AND ou='$ou'";
	$q->QUERY_SQL($sql,"artica_backup");
}

function USERS_POPUP(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$ou=base64_decode($_GET["ou"]);
	$html="
	<div id='users-popup-list' style='height:400px;overflow:auto'></div>
	
	
	<script>
		LoadAjax('users-popup-list','$page?users-list=yes&ou={$_GET["ou"]}');
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function USERS_POPUP_LIST(){
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();		
	$ou=base64_decode($_GET["ou"]);
	$sql="SELECT * FROM mbx_migr_users WHERE ou='$ou'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H3>$q->mysql_error</h3>";}
	
	$html="
	<center>
	<table style='margin:5px'>
	<tr>
		<td width=50%>". button("{restart_task}","RestartMembers('{$_GET["ou"]}')")."</td>
		<td width=50%>". button("{run_task}","ReloadMembers()")."</td>
	</tr>
	</table>
	<hr style='width:70%'>
	</center>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{member}</th>
	<th>{imap_server}</th>
	<th>{account}</th>
	<th>{terminated}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$over="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$PID=$ligne["PID"];
			$status="<br><strong style='color:#7C7777;font-size:11px'><i>{stopped} PID:$PID</i></strong>";
			$showuser=MEMBER_JS($ligne["uid"],1,1);
			if($ligne["imported"]==0){$imported="danger24.png";}else{$imported="ok24.png";}
			
			if($sock->getFrameWork("cmd.php?ProcessExists=yes&PID=$PID")){
				$status="<br><strong style='color:red;font-size:11px'><i>{running} PID $PID</i></strong>";
			}
			
			$html=$html."
			<tr class=$classtr>
			<td width=1%>". imgtootltip("user-32.png",$ligne["uid"],$showuser)."</td>
			<td  width=99%><strong style='font-size:14px;text-decoration:underline' $over OnClick=\"javascript:MigrShowLogs('{$ligne["zmd5"]}')\">{$ligne["uid"]}</td>
			<td  width=99%><strong style='font-size:14px;' $over OnClick=\"javascript:MigrShowLogs('{$ligne["zmd5"]}')\">{$ligne["imap_server"]}$status</td>
			<td  width=99%><strong style='font-size:14px;' $over OnClick=\"javascript:MigrShowLogs('{$ligne["zmd5"]}')\">{$ligne["username"]}</td>
			<td align='center' width=1%><strong style='font-size:14px'><img src='img/$imported'></td>
			</tr>
			";
			
		}
	
	
	$html=$html."</tbody></table>
	<div style='text-align:right;margin-top:8px'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_migrmbx')")."</div>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function USERS_EVENTS(){
	$sql="SELECT * FROM mbx_migr_users WHERE zmd5='{$_GET["users-events"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$tbl=explode("\n",$ligne["events"]);
	if(!is_array($tbl)){return;}
	krsort($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if($line==null){continue;}
		$html=$html."<div><code>$line</code></div>";
		
	}
	
	echo "<div style='height:450px;overflow:auto'>$html</div>";
}


function MIGRATION_RELAUNCH_MEMBERS(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mbx-migr-reload-members=yes");
	
}
function MIGRATION_RESTART_MEMBERS(){
	$ou=base64_decode($_GET["ou"]);
	$sql="UPDATE mbx_migr_users SET imported=0 WHERE ou='$ou'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?mbx-migr-reload-members=yes");	
}