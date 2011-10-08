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
	
	
	if(isset($_GET["popup"])){alias_start();exit;}
	if(isset($_GET["freeweb-aliases-list"])){alias_list();exit;}
	if(isset($_POST["Alias"])){alias_save();exit;}
	if(isset($_POST["DelAlias"])){alias_del();exit;}
	if(isset($_POST["AddAlias"])){alias_add();exit;}
	
	
	
	js();	
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$server=$_GET["servername"];
	$title=$tpl->_ENGINE_parse_body("{aliases}");
	echo "YahooWin3('375','$page?popup=yes&servername=$server','$server::$title')";
	
	
}

function alias_start(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div class=explain>{freeweb_aliasserver_explain}</div>
	<div id='freeweb-aliasesserver-list' style='width:100%;heigth:350px;overflow:auto'></div>
	<script>
		function FreeWebAliasList(){
			LoadAjax('freeweb-aliasesserver-list','$page?freeweb-aliases-list=yes&servername={$_GET["servername"]}');
		}
	FreeWebAliasList();
	</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);
}
	
	


function alias_del(){
	$free=new freeweb($_POST["servername"]);
	unset($free->Params["ServerAlias"][$_POST["DelAlias"]]);
	$free->SaveParams();	
	
}
function alias_add(){
	$free=new freeweb($_POST["servername"]);
	writelogs("Add ServerAlias {$_POST["AddAlias"]} -> {$_POST["servername"]}",__FUNCTION__,__FILE__,__LINE__);
	$free->Params["ServerAlias"][$_POST["AddAlias"]]=true;
	$free->SaveParams();
}


function alias_list(){
	$tpl=new templates();	
	$free=new freeweb($_GET["servername"]);
	$page=CurrentPageName();
	$txt=$tpl->javascript_parse_text("{add_serveralias_ask}");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{add}","FreeWebAddServerAlias()")."</th>
		<th width=99%>{alias}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
while (list ($host,$num) = each ($free->Params["ServerAlias"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","FreeWebDelServerAlias('{$host}')");
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/alias-32.gif'></td>
		<td style='font-size:16px;' width=99%>{$host}</td>
		<td width=1%>$delete</td>
		</tr>
		";
	}
	$html=$html."</table>
	<script>
		var x_FreeWebAddServerAlias=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			if(document.getElementById('main_config_freeweb')){RefreshTab('main_config_freeweb');}
			FreeWebAliasList();	
			WebServerAliasesRefresh();
		}	

		function WebServerAliasesRefresh(){
			if(document.getElementById('main_config_freeweb')){LoadAjaxTiny('webserver-aliases','freeweb.edit.php?webserver-aliases=yes&servername={$_GET["servername"]}');}
		}		
	
		function FreeWebDelServerAlias(id){
			var XHR = new XHRConnection();
			XHR.appendData('DelAlias',id);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('freeweb-aliasesserver-list');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebAddServerAlias);			
		}

		function FreeWebAddServerAlias(){
			var newserv=prompt('$txt');
			if(newserv){
				if(newserv.length<2){return;}
				var XHR = new XHRConnection();
				XHR.appendData('AddAlias',newserv);
				XHR.appendData('servername','{$_GET["servername"]}');
				AnimateDiv('freeweb-aliasesserver-list');
    			XHR.sendAndLoad('$page', 'POST',x_FreeWebAddServerAlias);
    		}			
		}			
	
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}
