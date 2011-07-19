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
	if(isset($_GET["OURBLLIST"])){xlist();exit;}
	if(isset($_GET["RBL_ADD"])){add();exit;}
	if(isset($_GET["OURBLDEL"])){delete();exit;}
	
js();



function js(){
		
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{DNSBL_settings}',"postfix.index.php");
	$PostfixAutoBlockDenyAddWhiteList_explain=$tpl->_ENGINE_parse_body('{PostfixAutoBlockDenyAddWhiteList_explain}');
	
	$html="
		function OU_RBL_POSTFIX(){
			YahooWin4('600','$page?popup=yes&ou=$ou','$title');
		
		}
		
var x_OURBLADD= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	LoadAjax('OURBLLIST','$page?OURBLLIST=yes&ou=$ou');
}
	
	function OURBLADD(){
			var XHR = new XHRConnection();
			XHR.appendData('RBL_ADD',document.getElementById('RBL_ADD').value);
			XHR.appendData('ou','$ou');
			XHR.sendAndLoad('$page', 'GET',x_OURBLADD);
		
	}
	
	function OURBLDEL(ID){
		var XHR = new XHRConnection();
		XHR.appendData('OURBLDEL',ID);
		XHR.appendData('ou','$ou');
		XHR.sendAndLoad('$page', 'GET',x_OURBLADD);
	
	}		
	
	OU_RBL_POSTFIX();";
	
	echo $html;
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$PostfixAutoBlockDenyAddWhiteList=$tpl->_ENGINE_parse_body("{PostfixAutoBlockDenyAddWhiteList}","postfix.index.php");
	
	$field=Field_array_Hash(RBL_LIST(),"RBL_ADD",null,null,null,0,"font-size:14px;font-weight:bold;padding:5px;width:95%");
	
	
	$html="
	
	<div style='font-size:13px;margin:5px'>{DNSBL_settings_text}</div>
	<hr>
	
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	$field
	</td>
	<td valing='top'>". button("{add}","OURBLADD()")."</td>
	</tR>
	</table>
	<p>&nbsp;</p>
	<div style='width:100%;height:300px;overflow:auto' id='OURBLLIST'></div>
		
	
	<script>
		LoadAjax('OURBLLIST','$page?OURBLLIST=yes&ou=$ou');
	</script>
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
	
}


function RBL_LIST(){
	$data=file_get_contents("ressources/dnsrbl.db");
	$tr=explode("\n",$data);
	while (list ($num, $val) = each ($tr) ){
		if(preg_match("#RBL:(.+)#",$val,$re)){
			$RBL[$re[1]]=$re[1];
		}
	if(preg_match("#RHSBL:(.+)#",$val,$re)){
			$RHSBL[$re[1]]=$re[1];
		}		
	}
	$RBL[null]="{select}";
	return $RBL;
}


function add(){
	$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`) VALUES('{$_GET["ou"]}','RBL','{$_GET["RBL_ADD"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou={$_GET["ou"]}");		
	
}

function xlist(){
	$sql="SELECT * FROM postfix_multi WHERE `key`='RBL' AND `ou`='{$_GET["ou"]}' ORDER BY ID DESC;";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$html="<table style='width:100%'>";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$html=$html . "<tr ". CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:14px'><code>{$ligne["value"]}</code></td>
		<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","OURBLDEL('{$ligne["ID"]}')")."</td>
	</tr>";
		
		
	}
	$html=$html."</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function delete(){
	$id=$_GET["OURBLDEL"];
	$ou=$_GET["ou"];
	$sql="DELETE FROM postfix_multi WHERE ID=$id";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou=$ou");		
}


?>