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
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["fdperms-list"])){popup_list();exit;}
	if(isset($_GET["rule"])){popup_rule();exit;}
	if(isset($_GET["FreeWebFDPerms"])){FreeWebFDPerms();exit;}
	if(isset($_GET["SaveFDGenralsParams"])){SaveFDGenralsParams();exit;}
	if(isset($_GET["FreeWebPermsDelRule"])){FreeWebPermsDelRule();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{files_and_folders_permissions}");
	$html="YahooWin3('650','$page?tabs=yes&servername={$_GET["servername"]}','{$_GET["servername"]}::$title');";
	echo $html;
	
}
function tabs(){
	
	$tpl=new templates();	
	$page=CurrentPageName();
	
	
	$array["popup"]='{parameters}';
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_fdperms style='width:100%;height:670px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_fdperms\").tabs();});
		</script>";		
	
}
function popup(){
	$sql="SELECT EnbaleFDPermissions,FDPermissions FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(!$q->ok){echo $q->mysql_error;}
	$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));
	$rule=$tpl->_ENGINE_parse_body("{rule}");
	
	$freq[10]="10mn";
	$freq[20]="20mn";
	$freq[30]="30mn";
	$freq[60]="1h";
	$freq[120]="2h";
	$freq[300]="5h";
	$freq[600]="10h";
	$freq[1440]="24h";
	$freq[2880]="48h";
	$freq[4320]="3 {days}";
	$freq[10080]="1 {week}";
	
	if(!is_numeric($FDPermissions["SCHEDULE"])){$FDPermissions["SCHEDULE"]=60;}
	
	$html="
	<div class=explain>{freeweb_files_perms_explain}</div>
	<table style='width:100%;' class=form>
	<tr>
		<td class=legend>{enable}:</td>
		<td>". Field_checkbox("EnbaleFDPermissions",1,$ligne["EnbaleFDPermissions"])."</td>
	</tr>
	<tr>
		<td class=legend>{frequency}:</td>
		<td>". Field_array_Hash($freq,"fdperms-schedule",$FDPermissions["SCHEDULE"],"style:font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveFDGenralsParams()")."</td>
	</tr>
	</table>
	<div id='freewebs-fdperms-list' style='width:100%;height:250px;overflow:auto'></div>
	
	
	<script>
		function RefreshFreewebsFdperms(){
			LoadAjax('freewebs-fdperms-list','$page?fdperms-list=yes&servername={$_GET["servername"]}');
		
		}
		
		function FreeWebAddRule(id){
			YahooWin4(440,'$page?rule='+id+'&servername={$_GET["servername"]}','$rule::'+id);
		}
		
	var x_SaveFDGenralsParams=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}			
		RefreshTab('main_config_fdperms');
	}	
	
	function SaveFDGenralsParams(){
		var XHR = new XHRConnection();
		XHR.appendData('SaveFDGenralsParams','yes');
		XHR.appendData('servername','{$_GET["servername"]}');
		if(document.getElementById('EnbaleFDPermissions').checked){XHR.appendData('EnbaleFDPermissions',1);}else{XHR.appendData('EnbaleFDPermissions',0);}
		XHR.appendData('schedule',document.getElementById('fdperms-schedule').value);
    	XHR.sendAndLoad('$page', 'GET',x_SaveFDGenralsParams);		
	}	

	function FreeWebPermsDelRule(num){
		var XHR = new XHRConnection();
		XHR.appendData('FreeWebPermsDelRule',num);
		XHR.appendData('servername','{$_GET["servername"]}');
    	XHR.sendAndLoad('$page', 'GET',x_SaveFDGenralsParams);		
	}
		
		RefreshFreewebsFdperms();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function SaveFDGenralsParams(){
	$q=new mysql();	
	$sql="SELECT EnbaleFDPermissions,FDPermissions FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));	
	$FDPermissions["SCHEDULE"]=$_GET["schedule"];
	$newval=base64_encode(serialize($FDPermissions));
	$sql="UPDATE freeweb SET FDPermissions='$newval',EnbaleFDPermissions={$_GET["EnbaleFDPermissions"]} WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
}


function popup_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$add=imgtootltip("plus-24.png","{add_rule}","FreeWebAddRule('')");
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>$add</th>
	<th>{directory}</th>
	<th>{files}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	$sql="SELECT EnbaleFDPermissions,FDPermissions FROM freeweb WHERE servername='{$_GET["servername"]}'";

	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));	
	
	
	if(is_array($FDPermissions["PERMS"])){
	while (list ($index, $array) = each ($FDPermissions["PERMS"])){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ruleid=$index;
		if(trim($array["directory"])==null){$array["directory"]="{all_directories}";}
		if($array["ext"]==null){$array["ext"]="{all_files}";}			
		
		$html=$html."
			<tr class=$classtr>
			<td colspan=2><code style='font-size:12px'>{$array["directory"]} ({$array["chmoddir"]})</td>
			<td nowrap><code style='font-size:12px'>{$array["ext"]} ({$array["chmodfile"]})</strong></td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","FreeWebPermsDelRule($ruleid)")."</td>
			</tr>
			";		
		
	}}else{
		$html=$html."
			<tr class=$classtr>
			<td colspan=4><code style='font-size:12px'>{no_rules}</td>
			</tr>
			";		
	}
	
	
	
	$html=$html."</table>";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_rule(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$sql="SELECT EnbaleFDPermissions,FDPermissions FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));
	
	
	$ruleAR=$FDPermissions["PERMS"][$_GET["rule"]];
	if(!is_numeric($ruleAR["chmoddir"])){$ruleAR["chmoddir"]="2570";}
	if(!is_numeric($ruleAR["chmodfile"])){$ruleAR["chmodfile"]="0460";}

	$html="
	<div class=explain>{freeweb_perms_add_explain}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("fdpers-directory",$ruleAR["directory"],"font-size:14px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend>{permissions}:</td>
		<td>". Field_text("chmoddir",$ruleAR["chmoddir"],"font-size:14px;padding:3px;width:60px")."</td>
	</tr>	
	<tr>
		<td class=legend>{files_extension}:</td>
		<td>". Field_text("ext",$ruleAR["ext"],"font-size:14px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend>{permissions}:</td>
		<td>". Field_text("chmodfile",$ruleAR["chmodfile"],"font-size:14px;padding:3px;width:60px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveFreeWebFDPerms()")."</td>
	</tr>
	</table>

	<script>
	
	var x_SaveFreeWebFDPerms=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);return;}			
		RefreshTab('main_config_fdperms');
		YahooWin4Hide();
	}	
	
	function SaveFreeWebFDPerms(){
		var XHR = new XHRConnection();
		XHR.appendData('FreeWebFDPerms','{$_GET["rule"]}');
		XHR.appendData('directory',document.getElementById('fdpers-directory').value);
		XHR.appendData('chmoddir',document.getElementById('chmoddir').value);
		XHR.appendData('ext',document.getElementById('ext').value);
		XHR.appendData('chmodfile',document.getElementById('chmodfile').value);
		XHR.appendData('servername','{$_GET["servername"]}');
    	XHR.sendAndLoad('$page', 'GET',x_SaveFreeWebFDPerms);		
		}
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function FreeWebFDPerms(){
	$rule=$_GET["FreeWebFDPerms"];
	$q=new mysql();	
	$sql="SELECT EnbaleFDPermissions,FDPermissions FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));	
	if($rule>0){
		$FDPermissions["PERMS"][$_GET["rule"]]=$_GET;
	}else{
		$FDPermissions["PERMS"][]=$_GET;
	}
	
	$newval=base64_encode(serialize($FDPermissions));
	$sql="UPDATE freeweb SET FDPermissions='$newval' WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-permissions={$_GET["servername"]}");
}

function FreeWebPermsDelRule(){
	$rule=$_GET["FreeWebPermsDelRule"];
	$q=new mysql();	
	$sql="SELECT EnbaleFDPermissions,FDPermissions FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$FDPermissions=unserialize(base64_decode($ligne["FDPermissions"]));	
	
	unset($FDPermissions["PERMS"][$rule]);
	
	$newval=base64_encode(serialize($FDPermissions));
	$sql="UPDATE freeweb SET FDPermissions='$newval' WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}



