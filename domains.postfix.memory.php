<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["PostFixEnableQueueInMemory"])){save_enable();exit;}
	if(isset($_GET["mem-status"])){mem_status();exit;}
	
js();


function js(){
	
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{postfix_tmpfs}');

$html="

function postfixtmpfs_load(){
	YahooWin4('600','$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
	
	}
	

postfixtmpfs_load();
";


echo $html;	
	
}

function save_enable(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$main->SET_VALUE("PostFixEnableQueueInMemory",$_GET["PostFixEnableQueueInMemory"]);
	$main->SET_VALUE("PostFixQueueInMemory",$_GET["PostFixQueueInMemory"]);	
	$sock=new sockets();
	
	if($_GET["hostname"]=="master"){
		$sock->SET_INFO("PostFixEnableQueueInMemory",$_GET["PostFixEnableQueueInMemory"]);
		$sock->SET_INFO("PostFixQueueInMemory",$_GET["PostFixQueueInMemory"]);
		$sock->getFrameWork("cmd.php?restart-postfix-single=yes");	
	}else{
		$sock->getFrameWork("cmd.php?postfix-multi-perform-reconfigure={$_GET["hostname"]}");	
	}
	
	
	
}

function popup(){
	
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$tpl=new templates();
	$PostFixEnableQueueInMemory=$main->GET("PostFixEnableQueueInMemory");
	$PostFixQueueInMemory=$main->GET("PostFixQueueInMemory");
	
	$enable=Paragraphe_switch_img("{PostFixEnableQueueInMemory}","{PostFixEnableQueueInMemory_text}",
	"PostFixEnableQueueInMemory",$PostFixEnableQueueInMemory,null,350);
	
	if($PostFixQueueInMemory==null){$PostFixQueueInMemory=0;}
	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-mem-disk-status={$_GET["hostname"]}")));
	$MOUTED=$datas["MOUTED"];
	$TOTAL_MEMORY_MB=$datas["TOTAL_MEMORY_MB"];
	$TOTAL_MEMORY_MB_FREE=$datas["TOTAL_MEMORY_MB_FREE"];
	
$html="
<table style='width:100%'>
<td valign='top' width=1%>
<center>
	<img src='img/bg_memory-150.png' id='bg_memory-150'>
	<div id='postfix-mem-status'></div>
</center>
</td>
<td valign='top' width=99%>
$enable
<hr>

<table style='width:100%'>
<tr>
<td class=legend style='font-size:16px'>{memory}:</td>
<td style='font-size:16px'>". 
Field_text("PostFixQueueInMemory",$PostFixQueueInMemory,"font-size:16px;padding:5px;width:120px").
"&nbsp;MB/{$TOTAL_MEMORY_MB_FREE}MB</td>
</tr>
</table>

<div style='margin:12px;text-align:right'>	
	". button("{apply}","PostFixEnableQueueInMemorySave()")."<hr>
</div>
<script>

var x_PostFixEnableQueueInMemorySave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	document.getElementById('bg_memory-150').src='img/bg_memory-150.png';
	mem_status();
	}
	
	
	function PostFixEnableQueueInMemorySave(){
		var TOTAL_MEMORY_MB_FREE=$TOTAL_MEMORY_MB_FREE;
		var mem=document.getElementById('PostFixQueueInMemory').value;
		if(mem>TOTAL_MEMORY_MB_FREE){
			alert(mem+'M >'+TOTAL_MEMORY_MB_FREE+' M');
			return;
		}
		var XHR = new XHRConnection();
		XHR.appendData('PostFixEnableQueueInMemory',document.getElementById('PostFixEnableQueueInMemory').value);
		XHR.appendData('PostFixQueueInMemory',document.getElementById('PostFixQueueInMemory').value);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		document.getElementById('bg_memory-150').src='img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',x_PostFixEnableQueueInMemorySave);
	
	}
	
	function mem_status(){
		LoadAjax('postfix-mem-status','$page?mem-status&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
	}
	mem_status();
</script>
";	

echo $tpl->_ENGINE_parse_body($html);
}

function mem_status(){
	$tpl=new templates();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-mem-disk-status={$_GET["hostname"]}")));
	$MOUTED=$datas["MOUTED"];
	$TOTAL_MEMORY_MB=$datas["TOTAL_MEMORY_MB"];
	$TOTAL_MEMORY_MB_FREE=$datas["TOTAL_MEMORY_MB_FREE"];
	
	$pourc=round(($TOTAL_MEMORY_MB_FREE/$TOTAL_MEMORY_MB)*100);
	$pourc_used=pourcentage($pourc);
	
	if($MOUTED>0){
		$pourc=round(($MOUTED/$TOTAL_MEMORY_MB_FREE)*100);
		$purc_mounted=pourcentage($pourc);
	}
	
	$html="
	<table style='width:100%'>
	<tr>
		<td style='font-size:11px'>{free}: </td>
	</tr>
	<td valign='top'>$pourc_used</td>
	</tr>
	<tr>
		<td valign='top' align='right'><i>{$TOTAL_MEMORY_MB_FREE}MB/{$TOTAL_MEMORY_MB}MB</i></td>
	</tr>	
	<tr>
		<td colspan=2><hr></td>
	</tr>
	<tr>
		<td style='font-size:11px'>{postfix_tmpfs}:</td>
	</tr>
		<td valign='top'>$purc_mounted</td>
	</tr>
	</tr>
		<td valign='top' align='right'><i>{$MOUTED}MB/{$TOTAL_MEMORY_MB_FREE}MB</td>
	</tr>	
	</table>
		<div style='width:100%;text-align:right'>". imgtootltip("refresh-24.png","{refresh}","mem_status()")."</div>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}








?>
