<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.autofs.inc');
	
	//if(count($_POST)>0)
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
		writelogs("Wrong account : no AllowAddUsers privileges",__FUNCTION__,__FILE__);
			$tpl=new templates();
			$error="{ERROR_NO_PRIVS}";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();
		}
		
		
	if(!$usersmenus->autofs_installed){
			$tpl=new templates();
			$error="{ERROR_NO_AUTOFS}";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();	
	}
		
		
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["shared-add"])){SHARED_FOLDERS_ADD();exit;}	
if(isset($_GET["shared-list"])){echo SHARED_FOLDERS_LIST($_GET["shared-list"]);exit;}	
if(isset($_GET["shared-rebuild"])){SHARED_REBUILD();exit;}	
if(isset($_GET["shared-delete"])){SHARED_FOLDERS_DEL();exit;}
		
js();


function js(){
	//s&uuid=$uuid&dev=$usb->path&type=$usb->TYPE
	$gpid=$_GET["gpid"];
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page."shared".$gpid);
	$prefix=str_replace('-','',$prefix);
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{shared_folders}","domains.edit.group.php");
	
	$html="
		function {$prefix}LoadPage(){
			YahooWin6(550,'$page?popup=$gpid','$title');
		
		}
		

		
	var x_SharedPathAdd=function (obj) {
		var results=obj.responseText;
		if (results.length>0){
			alert(results);
			
		}	
		LoadAjax('SharedDiv','$page?shared-list=$gpid');
	}
		
		function SharedPathAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('shared-add',document.getElementById('SharedPath').value);
			XHR.appendData('gpid','$gpid');
			document.getElementById('SharedDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SharedPathAdd);		
		}
		
		
		function SharedFolderRebuild(){
			var XHR = new XHRConnection();
			XHR.appendData('shared-rebuild','$gpid');
			document.getElementById('SharedDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SharedPathAdd);		
		}
		
		function SharedDelete(path){
			var XHR = new XHRConnection();
			XHR.appendData('shared-delete','$gpid');
			XHR.appendData('shared-path',path);
			document.getElementById('SharedDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SharedPathAdd);	
		}
		
		
	{$prefix}LoadPage();
	";
	
	echo $html;		
	}

function popup(){
	$list=SHARED_FOLDERS_LIST($_GET["popup"]);
	$gp=new groups($_GET["popup"]);
	$html="<H1>{shared_folders} $gp->groupName</H1>
	<p class=caption>{add_shared_folder_text}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend>{path}:</td>
		<td valign='top'>". Field_text('SharedPath',null)."</td>
		<td valign='top'><input type='button' style='margin:0px' OnClick=\"javascript:Loadjs('SambaBrowse.php?t=&homeDirectory=&no-shares=yes&field=SharedPath&without-start=no')\" value='{browse}...'></td>
	</tr>
	<tr>
		<td colspan=3 align='right'><input type='button' OnClick=\"javascript:SharedPathAdd();\" value='{add_shared_folder}&nbsp;&raquo;'>
	</tr>
	</table>
	<br>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>" . Paragraphe('64-refresh.png','{rebuild_shared}','{rebuild_shared_text}',"javascript:SharedFolderRebuild()")."
		<br>
		" . Buildicon64('DEF_ICO_AUTOFS_RESTART')."
		</td>
		<td valign='top'>
			". RoundedLightWhite("<div style='width:100%;height:200px;overflow:auto' id='SharedDiv'>$list</div>")."
		</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"domains.edit.group.php");
}

function SHARED_FOLDERS_ADD(){
	$gp=new groups($_GET["gpid"]);
	$gp->add_sharedfolder($_GET["shared-add"]);
	
	
}

function SHARED_FOLDERS_LIST($gid){
	$group=new groups($gid);
	$res=$group->SharedFolders_list;

	$html="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($res) ){
		$html=$html . "<tr ". CellRollOver().">
		<td width=1%>
			<img src='img/folder.gif'>
		</td>
		<td style='font-size:12px;font-weight:bold'>$ligne</td>
		<td width=1%>" . imgtootltip('x.gif','{delete}',"SharedDelete($num)")."</td>
		</tr>";
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,"domains.edit.group.php");
}
function SHARED_FOLDERS_DEL(){
	$g=new groups($_GET["shared-delete"]);
	$g->del_sharedfolder($_GET["shared-path"]);
	
}

function SHARED_REBUILD(){
	 $autofs=new autofs();
	 $autofs->AutofsSharedDir($_GET["shared-rebuild"]);
	}
	

		
		
		


?>