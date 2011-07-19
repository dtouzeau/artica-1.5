<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.backup.inc');


	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	if(!permissions()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["ListDirectory"])){ListDirectory();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["tasks-list"])){tasks_list();exit;}
	if(isset($_GET["connect"])){connect();exit;}
	if(isset($_GET["TasksListConnect"])){TasksListConnect();exit;}
	if(isset($_GET["BrowseBackupDirDate"])){BrowseBackupDirDate();exit;}
	if(isset($_GET["BrowseContener"])){BrowseContener();exit;}
	if(isset($_GET["RestoreMailbox"])){RestoreMailbox();exit;}
	
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{restore_mailbox}");
	$task=$tpl->_ENGINE_parse_body("{task}");
	$listing_content=$tpl->_ENGINE_parse_body("{listing_content}");
	$connection_to_backup_failed=$tpl->javascript_parse_text("{connection_to_backup_failed}");
	$confirm_restore_mbx=$tpl->javascript_parse_text("{confirm_restore_mbx}");
	$apply_upgrade_help=$tpl->javascript_parse_text("{apply_upgrade_help}");
	$page=CurrentPageName();
	$md=md5($_GET["uid"]);
	$html="
		var date_mem_backup='';
		var mem_div='';
	
	
		function restore_mailbox_start(){
			YahooWin3('600','$page?popup=yes&uid={$_GET["uid"]}','$title');
			}
	
		function TasksListRestoreRefresh(){
			LoadAjax('tasksRestoreList','$page?tasks-list=yes');
			}
		
		function TasksListConnectSource(id){
			YahooWin3('600','$page?connect=yes&uid={$_GET["uid"]}&task-id='+id,'$task '+id);
			}	
		
var x_TasksListConnect= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length==0){
			alert('$connection_to_backup_failed');
			restore_mailbox_start();
			return;
		}
		document.getElementById('TasksConnectInfo').innerHTML=tempvalue; 
	 }			

		
		function TasksListConnect(id){
				var XHR = new XHRConnection();
				XHR.appendData('TasksListConnect',id);
				XHR.appendData('uid','{$_GET["uid"]}');
				XHR.sendAndLoad('$page', 'GET',x_TasksListConnect);
		}
		
		
	var x_BrowseBackupDirDate= function (obj) {
		var tempvalue=obj.responseText;
		if(!document.getElementById('date-'+date_mem_backup)){alert('date-'+date_mem_backup+'!');}
		document.getElementById('date-'+date_mem_backup).innerHTML=tempvalue; 
	 }			
		
		function BrowseBackupDirDate(id,dir){
				var XHR = new XHRConnection();
				date_mem_backup=dir;
				XHR.appendData('BrowseBackupDirDate',id);
				XHR.appendData('uid','{$_GET["uid"]}');
				XHR.appendData('dir',dir);
				document.getElementById('date-'+date_mem_backup).innerHTML='<img src=img/ajax-menus-loader.gif>';
				XHR.sendAndLoad('$page', 'GET',x_BrowseBackupDirDate);		
			}
			
			
	var x_BrowseContener= function (obj) {
		var tempvalue=obj.responseText;
		if(!document.getElementById(mem_div)){alert(mem_div+'!');}
		document.getElementById(mem_div).innerHTML=tempvalue; 
		
	 }				
		
		
	function BrowseContener(id,date,computer,divid){
				mem_div=divid;
				var XHR = new XHRConnection();
				XHR.appendData('BrowseContener',id);
				XHR.appendData('uid','{$_GET["uid"]}');
				XHR.appendData('date',date);
				XHR.appendData('computer',computer);
				document.getElementById(mem_div).innerHTML='<center><img src=img/wait_verybig.gif></center>';	
				XHR.sendAndLoad('$page', 'GET',x_BrowseContener);			
		}
		
		
	function ListDirectory(taskid,path,divid){
				mem_div=divid;
				var XHR = new XHRConnection();
				XHR.appendData('ListDirectory','yes');
				XHR.appendData('taskid',taskid);
				XHR.appendData('uid','{$_GET["uid"]}');
				XHR.appendData('path',path);
				if(!document.getElementById(mem_div)){alert(mem_div+'?');}
				document.getElementById(mem_div).innerHTML='<center><img src=img/wait_verybig.gif></center>';	
				XHR.sendAndLoad('$page', 'GET',x_BrowseContener);		
		}
		
		
		
	var x_RestoreMailbox=function (obj) {
		alert('$apply_upgrade_help');
		YahooWin3Hide();
		}			
		
	function RestoreMailbox(id,path,mailbox){
		if(confirm('$confirm_restore_mbx\\n'+mailbox)){
				var XHR = new XHRConnection();
				XHR.appendData('RestoreMailbox',id);
				XHR.appendData('uid','{$_GET["uid"]}');
				XHR.appendData('path',path);
				XHR.appendData('mailbox',mailbox);
				XHR.sendAndLoad('$page', 'GET',x_RestoreMailbox);			
				}
		}
		
		
		
	
	restore_mailbox_start()";
	
		echo $html;
	
}

function popup(){
	
	
	$html="<div style='font-size:13px'>{restore_mailbox_explain}</div>
	
	<div id='tasksRestoreList' style='width:100%height:250px;overflow:auto'></div>
	
	
	<script>
		TasksListRestoreRefresh();
	</script>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function connect(){
$taskid=$_GET["task-id"];	
$html="<H5>{task} $taskid</h5>
	
	<div style='width:100%;height:550px;overflow:auto' id='TasksConnectInfo'>
	<center style='font-size:18px;font-weight:bold;color:#005447;padding:5px' >
		{connecting}...<img src='img/ajax-menus-loader.gif' style='margin:5px'>
	</center>
	
	</div>
	
	
	<script>
		TasksListConnect($taskid);
	</script>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
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
	
	$html="<table style='width:99%'>
	<th>&nbsp;</th>
	<th>{task}</th>
	<th>{STORAGE_TYPE}</th>
	<th>{resource}</th>
	<th>{schedule}</th>
	</tr>";
		
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$ressources=unserialize(base64_decode($ligne["datasbackup"]));
		$sources=count($ressources)." {sources}";
		$html=$html.
		"<tr ". CellRollOver("TasksListConnectSource({$ligne["ID"]})","{choose}:{task} {$ligne["ID"]}").">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td width=1% nowrap><strong>{task} {$ligne["ID"]}</strong></td>
		<td style='font-size:12px'>{$storages[$ligne["resource_type"]]}</td>
		<td style='font-size:12px'>". $backup->extractFirsRessource($ligne["pattern"])."</td>
		<td style='font-size:12px'>". $cron->cron_human($ligne["schedule"])."</td>
		</tr>";
		
		
	}
	
	$html=$html."</table>
	<hr>
	<div style='width:100%;text-align:right'>". imgtootltip('32-refresh.png',"{refresh}","BACKUP_TASKS_LISTS()")."</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function TasksListConnect(){
 $id=$_GET["TasksListConnect"];
 $uid=$_GET["uid"];
 $sock=new sockets();
 $array=unserialize(base64_decode($sock->getFrameWork("cmd.php?cyr-restore=$id")));
 if(!is_array($array)){
	writelogs("Not an array ",__FUNCTION__,__FILE__,__LINE__);
 	exit;
 
 }
 	
 $html="<table style='width:100%'>";
 while (list ($path, $dir) = each ($array) ){
 	if(preg_match("#backup\.(.+)#",$dir,$re)){
 		$index=str_replace("-","",$re[1]);
 		$array1[$index]=$re[1];
	}
 }
 
 rsort($array1);
  while (list ($index, $dir) = each ($array1) ){
 		$html=$html."
 		<tr>
 			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
 			<td valign='top' style='font-size:13px' ". CellRollOver("BrowseBackupDirDate($id,'backup.$dir')")."><strong>{backup} $dir</strong></td>
 			<td valign='top' style='border:1px solid #CCCCCC'><div id='date-backup.$dir'></div></td>
 		</tr>";
 		
 	}
 $html=$html."</table>";
 $tpl=new templates();
 echo $tpl->_ENGINE_parse_body("
  
 <div style='width:100%;height:450px;overflow:auto'>$html</div>
 <hr>
<div style='width:100%'>". button("{back}","restore_mailbox_start()")."</div>
 ");
 
 
}


function BrowseBackupDirDate(){
	$taskid=$_GET["BrowseBackupDirDate"];
	$DirDate=$_GET["dir"];
 	$sock=new sockets();
 	$tpl=new Templates();
 	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?cyr-restore-computer=$taskid&dir=$DirDate")));
 	if(!is_array($array)){exit;}

 $html="<table style='width:100%'>";
 while (list ($path, $dir) = each ($array) ){$array1[$dir]=$dir;}
  ksort($array1);	
  while (list ($path, $computer) = each ($array1) ){
  		$md=md5($DirDate.$computer);
 		$html=$html."
 		<tr>
 			<td width=1%><img src='img/30-computer.png'></td>
 			<td valign='middle' style='font-size:16px' ". CellRollOver("BrowseContener($taskid,'$DirDate','$computer','comp-$md')")."><strong>$computer</strong></td>
 		</tr>
 		<tr>
 			<td colspan=2 valign='top'><div id='comp-$md'></div></td>
 		</tr>";
 		
 	} 	
 echo $tpl->_ENGINE_parse_body("$html</table>");	
 	
}

function BrowseContener(){
	$uid=$_GET["uid"];
	$taskid=$_GET["BrowseContener"];
	$date=$_GET["date"];
	$computer=$_GET["computer"];
 	$sock=new sockets();
 	$tpl=new Templates();
 	$uid=str_replace(".","^",$uid);
 	$firstletter=substr($uid,0,1);
 	$target_dir="$date/$computer/cyrus-imap/partitiondefault/mail/$firstletter/user/$uid";
 	$target_dir_src=$target_dir;
 	$target_dir=base64_encode($target_dir);
 	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?cyr-restore-container=$taskid&dir=$target_dir")));
	writelogs(count($array)." directories",__FUNCTION__,__FILE__);
	if(!is_array($array)){echo $tpl->_ENGINE_parse_body("<H3>{no_backup}</H3>");exit;}
	while (list ($path, $dir) = each ($array) ){if($dir==null){continue;};$array1[$dir]=$dir;}
	if(!is_array($array1)){echo $tpl->_ENGINE_parse_body("<H3>{no_backup}</H3>");exit;}
	ksort($array1);	
	
	 $html="
	 <table style='width:100%;padding:3px;'>
	 <tr ".CellRollOver().">
 			<td width=1%><img src='img/mailbox.gif'></td>
 			<td valign='top' style='font-size:13px'><strong>INBOX</strong></td>
 			<td>". imgtootltip("22-backup.png","{restore} $path_1","RestoreMailbox($taskid,'$target_dir','INBOX')")."</td>
 		</tr>
 	</table>
	 
	 ";
  	while (list ($path, $path_1) = each ($array1) ){
  		$divid=md5("INBOX/$path_1");
  		//ListDirectory(taskid,path,divid)
  		$path_1_text=texttooltip($path_1,"{browse}","ListDirectory('$taskid','$target_dir_src/$path_1','$divid')");
  		
	$html=$html."
		<table style='width:100%;padding:3px;'>
 		<tr >
 			<td>&nbsp;&nbsp;</td>
 			<td width=1%><img src='img/mailbox.gif'></td>
 			<td ".CellRollOver()." width=99% valign='top' style='font-size:11px'><strong>$path_1_text</strong></td>
 			<td>". imgtootltip("22-backup.png","{restore} $path_1","RestoreMailbox($taskid,'$target_dir','INBOX/$path_1')")."</td>
 		</tr>
 		</table>
		<div id='$divid' style='padding-left:20px'></div>";
  		
  		
  	}
	
 echo $tpl->_ENGINE_parse_body("$html");		
	
}

function ListDirectory(){
	$uid=$_GET["uid"];
	$taskid=$_GET["taskid"];
 	$sock=new sockets();
 	$tpl=new Templates();
 	$uid=str_replace(".","^",$uid);
 	$firstletter=substr($uid,0,1);
 	$target_dir=$_GET["path"];
 	$target_dir=base64_encode($target_dir);
 	writelogs("Send receive",__FUNCTION__,__FILE__);
 	$data=$sock->getFrameWork("cmd.php?cyr-restore-container=$taskid&dir=$target_dir");
 	writelogs(strlen($data)." bytes receive",__FUNCTION__,__FILE__);
 	$array=unserialize(base64_decode($data));
 	if(!is_array($array)){return;}
	while (list ($path, $dir) = each ($array) ){if($dir==null){continue;};$array1[$dir]=$dir;}
	ksort($array1);	
	
  	while (list ($path, $path_1) = each ($array1) ){
  		$divid=md5("INBOX/$path_1");
  		//ListDirectory(taskid,path,divid)
  		$path_1_text=texttooltip($path_1,"{browse}","ListDirectory('$taskid','$target_dir_src/$path_1','$divid')");
  		
	$html=$html."
		<table style='width:100%;padding:3px;'>
 		<tr >
 			<td>&nbsp;&nbsp;</td>
 			<td width=1%><img src='img/mailbox.gif'></td>
 			<td ".CellRollOver()." width=99% valign='top' style='font-size:11px'><strong>$path_1_text</strong></td>
 			<td>". imgtootltip("22-backup.png","{restore} $path_1","RestoreMailbox($taskid,'$target_dir','INBOX/$path_1')")."</td>
 		</tr>
 		</table>
		<div id='$divid' style='padding-left:15px'></div>";
  		
  		
  	}
	
 echo $tpl->_ENGINE_parse_body("$html");		
 	
}



function RestoreMailbox(){
	$uid=$_GET["uid"];
	$taskid=$_GET["RestoreMailbox"];
	$path=$_GET["path"];
	$mailbox=$_GET["mailbox"];
 	$sock=new sockets();
	$array["taskid"]=$taskid;
	$array["uid"]=$uid;
	$array["path"]=base64_decode($path);
	$array["mailbox"]=$mailbox;
	$data=base64_encode(serialize($array));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cyr-restore-mailbox=$data");
	}
	

function permissions(){
$usersprivs=new usersMenus();
if(!$usersprivs->AsAnAdministratorGeneric){
		if(!$usersprivs->AllowFetchMails){
			return false;
			}
		if($_SESSION["uid"]<>$_GET["userid"]){return false;}
	}

	return true;
	
}
?>