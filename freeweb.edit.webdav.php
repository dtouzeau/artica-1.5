<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["EnableWebDav"])){SaveWebDav();exit;}
	if(isset($_GET["webdav-members-list"])){WebDavMembersList();exit;}
	if(isset($_GET["MembersDelete"])){WebDavMembersDelete();exit;}

page();	
	
	
function page(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize($ligne["Params"]);	
	
	$EnableWebDav=$Params["WEBDAV"]["ENABLED"];
	if(!is_numeric($EnableWebDav)){$EnableWebDav=0;}
	
	$html="
	<div style='font-size:16px'>{WEBDAV_ACCESS}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ACTIVATE_THIS_USER_WEBDAV}:</td>
		<td>". Field_checkbox("EnableWebDav",1,$EnableWebDav)."</td>
	</tr>
	<tr>
		<td class=legend>{add_members}:</td>
		<td>
		<table>
			<tr>
				<td>". Field_text("WebDavMember",null,"font-size:13px;padding:3px;width:220px")."</td>
				<td><input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('MembersBrowse.php?field-user=WebDavMember&prepend-guid=1')\"></td>
				<td>". button("{add}","SaveWebDavAccess()")."</td>
			</tr>
		</table>
		</td>
	</tr>
	
	</table>
	<p>&nbsp;</p>
	<div id='webdav-members-list' style='width:100%;height:350px;overflow:auto'></div>
	
	<script>
		function RefreshWebDavList(){
			LoadAjax('webdav-members-list','$page?webdav-members-list=yes&servername={$_GET["servername"]}');
			
		}
	
	
		var x_SaveWebDavAccess=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshWebDavList();	
		}		
	
		function SaveWebDavAccess(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableWebDav').checked){
				XHR.appendData('EnableWebDav',1);
			}else{
				XHR.appendData('EnableWebDav',0);
			}
			
			
			XHR.appendData('WebDavMember',document.getElementById('WebDavMember').value);
			XHR.appendData('servername','{$_GET["servername"]}');
			document.getElementById('webdav-members-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_SaveWebDavAccess);
		}
		RefreshWebDavList();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SaveWebDav(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize($ligne["Params"]);	

	$Params["WEBDAV"]["ENABLED"]=$_GET["EnableWebDav"];
	
	if(trim($_GET["WebDavMember"]<>null)){
		$Params["WEBDAV"]["MEMBERS"][$_GET["WebDavMember"]]=$_GET["WebDavMember"];
	}
	$data=addslashes(serialize($Params));
	$sql="UPDATE freeweb SET `Params`='$data' WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_GET["servername"]}");	
	
}

function WebDavMembersList(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize($ligne["Params"]);		
	
	$html="<center><table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:350px'>
<thead class='thead'>
	<tr>
		<th colspan=3>{members}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	if(is_array($Params["WEBDAV"]["MEMBERS"])){
		while (list ($member, $asnull) = each ($Params["WEBDAV"]["MEMBERS"]) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			
			if(preg_match("#@(.+):([0-9]+)$#",$member,$re)){
				$text_member=$re[1];
				$icon="wingroup.png";
			}else{
				$text_member=$member;
				$icon="winuser.png";
			}
			
			$delete=imgtootltip("delete-32.png","{delete}","WebDavMembersDelete('$member')");
			
			$html=$html . "
			<tr  class=$classtr>
				<td width=1%><img src='img/$icon'></td>
				<td width=99% align='left' nowrap><strong style='font-size:14px'>$text_member</strong></td>
				<td width=1%>$delete</td>
			</td>
			</tr>";				
			
		}
	}
	
		$html=$html."</tbody></table></center>
	<script>

	var x_WebDavMembersDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshWebDavList();
	}		
	
	function WebDavMembersDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('MembersDelete',key);	
		XHR.appendData('servername','{$_GET["servername"]}');
		document.getElementById('webdav-members-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_WebDavMembersDelete);
		}	

	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function WebDavMembersDelete(){
		$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize($ligne["Params"]);	

	unset($Params["WEBDAV"]["MEMBERS"][$_GET["MembersDelete"]]);
	
	
	$data=addslashes(serialize($Params));
	$sql="UPDATE freeweb SET `Params`='$data' WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_GET["servername"]}");	
	
}

	
	