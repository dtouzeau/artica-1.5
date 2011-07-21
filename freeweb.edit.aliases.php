<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["freeweb-aliases-list"])){alias_list();exit;}
	if(isset($_POST["Alias"])){alias_save();exit;}
	if(isset($_POST["DelAlias"])){alias_del();exit;}
	
	
	page();	
	
	
	
function page(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$free=new freeweb($_GET["servername"]);
	if($free->groupware<>null){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{freeweb_is_groupware_feature_disabled}</div>");
		return;
	}
	
	
	$free->CheckWorkingDirectory();
	$direnc=urlencode(base64_encode($free->WORKING_DIRECTORY));
	
	$html="<div class=explain>{freeweb_alias_explain}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{alias}:</td>
		<td>". Field_text("alias_freeweb",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("alias_dir",null,"font-size:16px;padding:3px;width:320px",null,null,null,false,"FreeWebAddAliasCheck(event)").
		"&nbsp;<input type='button' OnClick=\"javascript:Loadjs('browse-disk.php?start-root=$direnc&field=alias_dir&replace-start-root=1');\" value='{browse}...'></td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{add} {alias}","FreeWebAddAlias()")."</td>
	</tr>
	</table>
	<p>&nbsp;</p>
	<div id='freeweb-aliases-list' style='width:100%;heigth:350px;overflow:auto'></div>
	
	
	
	<script>
		var x_FreeWebAddAlias=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			FreeWebAliasList();	
		}			
		
		function FreeWebAddAliasCheck(e){
			if(checkEnter(e)){FreeWebAddAlias();}
		}
		
		function FreeWebAddAlias(){
			var XHR = new XHRConnection();
			var Alias=document.getElementById('alias_freeweb').value;
			if(Alias.length<2){return;}
			var directory=document.getElementById('alias_dir').value;
			if(directory.length<2){return;}			
			XHR.appendData('Alias',document.getElementById('alias_freeweb').value);
			XHR.appendData('directory',document.getElementById('alias_dir').value);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('freeweb-aliases-list');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebAddAlias);			
		}
		
		function FreeWebDelAlias(id){
			var XHR = new XHRConnection();
			XHR.appendData('DelAlias',id);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('freeweb-aliases-list');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebAddAlias);			
		}		
		
		function FreeWebAliasList(){
			LoadAjax('freeweb-aliases-list','$page?freeweb-aliases-list=yes&servername={$_GET["servername"]}');
		}
	FreeWebAliasList();
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}	

function alias_save(){
	$sql="INSERT INTO freewebs_aliases (alias,directory,servername) VALUES('{$_POST["Alias"]}','{$_POST["directory"]}','{$_POST["servername"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_POST["servername"]}");
	
}

function alias_del(){
	if(!is_numeric($_POST["DelAlias"])){return;}
	$sql="DELETE FROM freewebs_aliases WHERE ID={$_POST["DelAlias"]} AND servername='{$_POST["servername"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_POST["servername"]}");	
}


function alias_list(){
	$tpl=new templates();	

	$sql="SELECT * FROM freewebs_aliases WHERE servername='{$_GET["servername"]}' ORDER BY alias";
	$q=new mysql();
	
	
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code>$sql</code>";}
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		
		<th>{alias}</th>
		<th>{directory}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","FreeWebDelAlias('{$ligne["ID"]}')");
		$html=$html."<tr class=$classtr>
		<td style='font-size:16px;'>{$ligne["alias"]}</td>
		<td style='font-size:16px;'>{$ligne["directory"]}</td>
		<td width=1%>$delete</td>
		</tr>
		";
	}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}
