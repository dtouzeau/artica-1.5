<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	if(isset($_GET["list"])){popup_list();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["zapp-add"])){add();exit;}
	if(isset($_GET["zapp-edit"])){edit();exit;}
	if(isset($_GET["zapp-del"])){delete();exit;}
js();	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{block_banner_advertisements}");
	
	$html="
		YahooWin5('600','$page?popup=yes','$title');
	";
	echo $html;
		
		
}
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$types=array(
		"ADHTML"=>"{ZAP_ADHTML}",
		"AD"=>"{ZAP_AD}",
		"ADJS"=>"{ZAP_ADJS}",
		"ADBG"=>"{ZAP_ADBG}",
		"ADSWF"=>"{ZAP_ADSWF}",
		"ADPOPUP"=>"{ZAP_ADPOPUP}",
		"COUNTER"=>"{ZAP_COUNTER}");
	
	
	
	
	
	$html="
	<div class=explain>{addzapper_block_banner_advertisements}</div>
	
	<div class=explain>{pattern}:<br>{addzapper_pattern_explain}</div>
	
	<table style='width:100%;margin-bottom:10px'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{advertisement_type}:</td>
		<td>". Field_array_Hash($types,"ZAP_TYPE",null,"ZapAddSelected()",null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{pattern}:</td>
		<td width=100%>". Field_text("uri",null,"font-size:13px;padding:3px;width:100%",null,null,null,false,"ZapAddAddPress(event)")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","ZapAddAdd()")."</td>
	</tr>	
	
	</table>
	<div id='zappadd_list' style='padding-top:5px;height:300px;overflow:auto'></div>
	
	<script>
		function ZapAddSelected(){
			var select=document.getElementById('ZAP_TYPE').value;
			LoadAjax('zappadd_list','$page?list=yes&selected='+select);
		}
		
		function ZapAddAddPress(e){
			if(checkEnter(e)){ZapAddAdd();return;}
			var select=document.getElementById('ZAP_TYPE').value;
			var uri=escape(document.getElementById('uri').value);
			LoadAjax('zappadd_list','$page?list=yes&selected='+select+'&search='+uri);
		}
		
	var x_ZapAddAdd= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			ZapAddSelected();
		}

	var x_ZapAddAddSilent= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			
		}			

	function ZapAddAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('zapp-add',document.getElementById('uri').value);
			XHR.appendData('zapp-type',document.getElementById('ZAP_TYPE').value);
			document.getElementById('zappadd_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_ZapAddAdd);	
		}
		
	function AddZapEnable(ID){
		var value=0;
		if(document.getElementById('addzapp_'+ID).checked){value=1;}else{value=0;}
		var XHR = new XHRConnection();
		XHR.appendData('zapp-edit',ID);
		XHR.appendData('zapp-value',value);
		XHR.sendAndLoad('$page', 'GET',x_ZapAddAddSilent);	
	}
	
	function AddZapDelete(ID){
			var XHR = new XHRConnection();
			XHR.appendData('zapp-del',ID);
			document.getElementById('zappadd_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_ZapAddAdd);	
	}
		
		ZapAddSelected();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function popup_list(){
	
	$search=trim($_GET["search"]);
	if($search<>null){
		$sqladd=" AND `uri` LIKE '$search%' ";
	}
	$sql="SELECT * FROM squid_adzapper WHERE `uri_type`='{$_GET["selected"]}' $sqladd ORDER BY ID DESC LIMIT 0,100";
	$q=new mysql();
	$tpl=new templates();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>{ZAP_{$_GET["selected"]}}: {ZAP_{$_GET["selected"]}_explain}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:13px'>{$ligne["uri"]}</code></td>
		<td width=1%>". Field_checkbox("addzapp_{$ligne["ID"]}",1,$ligne["enabled"],"AddZapEnable('{$ligne["ID"]}')")."</td>
		<td width=1%>". imgtootltip("delete-32.png","{delete}","AddZapDelete('{$ligne["ID"]}')")."</td>
		</tr>";
		
		
	}
		
	$html=$html."</tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
}

function edit(){
	if(!is_numeric($_GET["zapp-edit"])){return null;}
	if(!is_numeric($_GET["zapp-value"])){return null;}
	$sql="UPDATE squid_adzapper SET `enabled`='{$_GET["zapp-value"]}' WHERE ID='{$_GET["zapp-edit"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return; }	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?adzapper-compile=yes");	
}

function add(){
	
	$md5=md5($_GET["zapp-type"].$_GET["zapp-add"]);
	$sql="INSERT squid_adzapper (`uri_type`,`enabled`,`uri`,`zmd5`)
	VALUES('{$_GET["zapp-type"]}','1','{$_GET["zapp-add"]}','$md5')
	";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return; }
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?adzapper-compile=yes");
	
}

function delete(){
	if(!is_numeric($_GET["zapp-del"])){return null;}
	$sql="DELETE FROM squid_adzapper WHERE ID='{$_GET["zapp-del"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return; }	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?adzapper-compile=yes");		
}





?>