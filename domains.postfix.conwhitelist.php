<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.postfix-multi.inc');
	
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["AutoBlockDenyAddWhiteList"])){AutoBlockDenyAddWhiteList();exit;}
	if(isset($_GET["BlockDenyAddWhiteList"])){BlockDenyWhiteList();exit;}
	if(isset($_GET["PostfixAutoBlockDenyDelWhiteList"])){delete();exit;}
	
js();



function js(){
		
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList}',"postfix.index.php");
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	
	$html="
		function OU_WHITE_POSTFIX(){
			YahooWin4('600','$page?popup=yes&ou=$ou','$title');
		
		}
		
var x_OU_AutoBlockDenyAddWhiteList= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	LoadAjax('OUBlockDenyAddWhiteList','$page?BlockDenyAddWhiteList=yes&ou=$ou');
}
	
	function OU_PostfixAutoBlockDenyAddWhiteList(){
		var server=prompt('$PostfixAutoBlockDenyAddWhiteList_explain');
		if(server){
			var XHR = new XHRConnection();
			XHR.appendData('AutoBlockDenyAddWhiteList',server);
			XHR.appendData('ou','$ou');
			XHR.sendAndLoad('$page', 'GET',x_OU_AutoBlockDenyAddWhiteList);
		}
	}
	
	function OUPostfixAutoBlockDenyDelWhiteList(ID){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAutoBlockDenyDelWhiteList',ID);
		XHR.appendData('ou','$ou');
		XHR.sendAndLoad('$page', 'GET',x_OU_AutoBlockDenyAddWhiteList);
	
	}		
	
	OU_WHITE_POSTFIX();";
	
	echo $html;
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$PostfixAutoBlockDenyAddWhiteList=$tpl->_ENGINE_parse_body("{PostfixAutoBlockDenyAddWhiteList}","postfix.index.php");
	$add_whitelist=Paragraphe("64-bind9-add-zone.png",
		"$PostfixAutoBlockDenyAddWhiteList",
		"{PostfixAutoBlockDenyAddWhiteList_explain}",
		"javascript:OU_PostfixAutoBlockDenyAddWhiteList();");
	$html="<table style='width:100%'>
	<tr>
	<td valign='top'>
	<div style='width:100%;height:300px;overflow:auto' id='OUBlockDenyAddWhiteList'></div>
		
	</td>
	<td valign='top' width=2%>
	$add_whitelist
	</td>
	</tr>
	</table>
	<script>
		LoadAjax('OUBlockDenyAddWhiteList','$page?BlockDenyAddWhiteList=yes&ou=$ou');
	</script>
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
	
}

function AutoBlockDenyAddWhiteList(){
	$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`) VALUES('{$_GET["ou"]}','ip_white_listed','{$_GET["AutoBlockDenyAddWhiteList"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou={$_GET["ou"]}");		
	
}

function BlockDenyWhiteList(){
	$sql="SELECT * FROM postfix_multi WHERE `key`='ip_white_listed' AND `ou`='{$_GET["ou"]}' ORDER BY ID DESC;";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$html="<table style='width:100%'>";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$html=$html . "<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:14px'><code>{$ligne["value"]}</code></td>
		<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","OUPostfixAutoBlockDenyDelWhiteList('{$ligne["ID"]}')")."</td>
	</tr>";
		
		
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function delete(){
	$id=$_GET["PostfixAutoBlockDenyDelWhiteList"];
	$ou=$_GET["ou"];
	$sql="DELETE FROM postfix_multi WHERE ID=$id";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou=$ou");		
}


?>