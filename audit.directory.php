<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.auditd.inc');
if(posix_getuid()<>0){
	
	if(!checkrights()){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["WatchDogThis"])){save();exit;}
js();

function js(){
	$tpl=new templates();
	$users=new usersMenus();
	if(!$users->APP_AUDITD_INSTALLED){
		$APP_AUDITD_NOT_INSTALLED=$tpl->javascript_parse_text("{APP_AUDITD_NOT_INSTALLED}");
		echo "alert('$APP_AUDITD_NOT_INSTALLED');";
		return;
	}
	
	$page=CurrentPageName();
	
	$title=$tpl->_ENGINE_parse_body("{audit_this_directory}");
	
	$html="
		YahooWin4(550,'$page?popup=yes&path={$_GET["path"]}&id={$_GET["id"]}','$title');
	
	";
		
	echo $html;
	
	
}



function checkrights(){
	$users=new usersMenus();
	if($users->AsSambaAdministrator){return true;}
	if($users->AsSystemAdministrator){return true;}
	return false;
	
}

function popup(){
	
	$path_decrypted=base64_decode($_GET["path"]);
	$path_text=$path_decrypted;
	$md=md5($path_decrypted);
	$key=auditd::KeyAudited($path_decrypted);
	
	
	if($key<>null){
		$enabled=1;
		if($key<>$md){
			$path_text=auditd::GetPath($key);
		}
	}
	$enable=Paragraphe_switch_img("{enable_watch_this_directory}","{auditd_explain}","WatchDogThis",$enabled);
	
	$page=CurrentPageName();
	$html="
	
	<div style='margin:3px;font-weight:bold;text-align:right;padding:3px;border-bottom:1px solid #D61919'><code style='color:#D61919'>{path}:$path_text</code></div>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1% style='border-right:5px solid #CCCCCC'>
			<img src='img/folder-watch-128.png' id='audit-picture'>
		</td>
		<td valign='top' width=99% style='padding:8px'>
		$enable
		<div style='text-align:right'><hr>". button("{apply}","SaveAuditConfig()")."</div>
		</tr>
	</table>
<script>
		var x_SaveAuditConfig=function (obj) {
		 	text=obj.responseText;
		 	
		 	if(text.length>0){
		 		alert(text);
		 		document.getElementById('audit-picture').src='img/folder-watch-128.png';
		 		return;
				}
			RefreshFolder('$path_decrypted','{$_GET["id"]}');
			YahooWin4Hide();
			
			}
		

		
		function SaveAuditConfig(){
			document.getElementById('audit-picture').src='img/wait_verybig.gif';
	        var XHR = new XHRConnection();
	        XHR.appendData('path','{$_GET["path"]}');
	        XHR.appendData('key','$key');
	        XHR.appendData('WatchDogThis',document.getElementById('WatchDogThis').value);
	        XHR.sendAndLoad('$page', 'GET',x_SaveAuditConfig);
			}
</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$path=trim(base64_decode($_GET["path"]));
	$key=trim($_GET["key"]);
	
	if(strlen($key)>2){
		if($_GET["WatchDogThis"]==0){
			$sql="DELETE FROM auditd_dir WHERE `key`='$key';";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error;return;}	
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?auditd-rebuild=yes");
			return;
		}
	}
	
	$md5=md5($path);
	if($_GET["WatchDogThis"]==1){
		$sql="INSERT INTO auditd_dir (`key`,`dir`) VALUES('$md5','$path');";
		$q=new mysql();
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}		
	}
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?auditd-rebuild=yes");
	
	
	
}


?>