<?php
$GLOBALS["VERBOSE"]=false;
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.user.inc');
include_once ('ressources/class.users.menus.inc');

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["perform"])){perform();exit;}
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{WHAT_USER_SEE}::'.$_GET["uid"]);
	$page=CurrentPageName();
	$html="YahooWin4('550','$page?popup=yes&uid={$_GET["uid"]}','$title')";
	echo $html;
}
	
	





function popup(){
$page=CurrentPageName();
$tpl=new templates();	
	$html="<div class=explain>{WHAT_USER_SEE_SMB_TEXT}</div>
	<div id='WHAT_USER_SEE_SMB_ID' style='width:100%;height:450px;overflow:auto'></div>
	
	<script>
		function WHAT_USER_SEE_SMB(){
			LoadAjax('WHAT_USER_SEE_SMB_ID','$page?perform=yes&uid={$_GET["uid"]}');
		
		}
		WHAT_USER_SEE_SMB();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function perform(){
$tpl=new templates();	
$page=CurrentPageName();	
	$user=new user($_GET["uid"]);
	$sock=new sockets();
	$array=array($_GET["uid"],$user->password);
	$datas=urlencode(base64_encode(serialize($array)));
	$array=unserialize(base64_decode($sock->getFrameWork("samba.php?SmblientBrowse=$datas")));
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-24.png","{refresh}","WHAT_USER_SEE_SMB()")."</th>
		<th width=25%>{resource}</th>
		<th width=25% colspan=2>{infos}</th>
		
	</tr>
</thead>
<tbody class='tbody'>";	
	
	
	while (list ($num, $infos) = each ($array)){
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$type=$infos[0];
		$resource=$infos[1];
		$inf=$infos[2];
		if($type=="IPC"){$img="32-network-folder.png";}
		if($type=="Disk"){$img="32-network-folder.png";$disk[$resource]=true;if($_GET["uid"]==$resource){$inf="{homeDirectory}";}}
		if($type=="Server"){$img="32-network-server.png";}
		if($type=="Workgroup"){$img="domain-32.png";}
		if($inf==null){$inf="$type";}
		
		
		
		
	$color="black";
		$tb=$tb."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;color:$color' nowrap width=1%><img src='img/$img'></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=50% nowrap>$resource</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color' width=1% nowrap>&nbsp;</a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50%>$inf</a></td>
		</tr>
		";
	}
	
	if(!$disk[$_GET["uid"]]){
		$diskuid="
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;color:$color' nowrap width=1%><img src='img/32-network-folder-grey.png'></td>
			<td style='font-size:14px;font-weight:bold;color:#CCCCCC' width=50% nowrap>{$_GET["uid"]}</a></td>
			<td style='font-size:14px;font-weight:bold;color:#CCCCCC' width=1% nowrap><img src='img/warning-panneau-32.png'></a></td>
			<td style='font-size:12px;font-weight:bold;color:$color' width=50%>{homeDirectory}</a></td>
		</tr>
		";
	}
	
	$html=$html."$diskuid$tb</tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
}



