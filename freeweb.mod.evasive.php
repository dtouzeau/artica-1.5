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
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["whitelist"])){whitelist();exit;}
	if(isset($_GET["whitelist-list"])){whitelist_list();exit;}
	if(isset($_POST["DeleteAllRules"])){whitelist_delete_all();exit;}
	if(isset($_POST["DeleteWhiteList"])){whitelist_delete();exit;}
	if(isset($_POST["DOSHashTableSize"])){DosEngineSave();exit;}
	if(isset($_POST["AddWhiteEvasive"])){whitelist_add();exit;}
	if(isset($_GET["rule-form"])){rule_form();exit;}
	if(isset($_POST["rule-edit"])){rule_edit();exit;}
	if(isset($_GET["events"])){events();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{DDOS_prevention}");
	$html="YahooWin3('650','$page?tabs=yes&servername={$_GET["servername"]}','{$_GET["servername"]}::$title');";
	echo $html;
	}


function tabs(){
	
	$tpl=new templates();	
	$page=CurrentPageName();
	
	
	$array["popup"]='{parameters}';
	$array["whitelist"]='{whitelist}';
	$array["events"]='{events}';
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_modeva style='width:100%;height:670px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_modeva\").tabs();});
		</script>";		
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$freeweb=new freeweb($_GET["servername"]);
	
	$Params=$freeweb->Params["mod_evasive"];
	if(!is_numeric($Params["DOSHashTableSize"])){$Params["DOSHashTableSize"]=1024;}
	if(!is_numeric($Params["DOSPageCount"])){$Params["DOSPageCount"]=10;}
	if(!is_numeric($Params["DOSSiteCount"])){$Params["DOSSiteCount"]=150;}
	if(!is_numeric($Params["DOSPageInterval"])){$Params["DOSPageInterval"]=1.5;}
	if(!is_numeric($Params["DOSSiteInterval"])){$Params["DOSSiteInterval"]=1.5;}
	if(!is_numeric($Params["DOSBlockingPeriod"])){$Params["DOSBlockingPeriod"]=10.7;}
	
	
	
	
	$html="
	<div class=explain>{mod_evasive_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{FreeWebsEnableModEvasive}:</td>
		<td>". Field_checkbox("DOSEnable",1,$Params["DOSEnable"],"EvasiveDisabled()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{DOSHashTableSize}:</td>
		<td>". Field_text("DOSHashTableSize",$Params["DOSHashTableSize"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSHashTableSize_explain}")."</td>
	</tr>	
	
	
	<tr>
		<td class=legend>{threshold}:</td>
		<td>". Field_text("DOSPageCount",$Params["DOSPageCount"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSPageCount_explain}")."</td>
	</tr>

	
	<tr>
		<td class=legend>{total_threshold}:</td>
		<td>". Field_text("DOSSiteCount",$Params["DOSSiteCount"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSSiteCount_explain}")."</td>
	</tr>

	<tr>
		<td class=legend>{page_interval}:</td>
		<td>". Field_text("DOSPageInterval",$Params["DOSPageInterval"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSPageInterval_explain}")."</td>
	</tr>	
	


	<tr>
		<td class=legend>{site_interval}:</td>
		<td>". Field_text("DOSSiteInterval",$Params["DOSSiteInterval"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSSiteInterval_explain}")."</td>
	</tr>

	<tr>
		<td class=legend>{Blocking_period}:</td>
		<td>". Field_text("DOSBlockingPeriod",$Params["DOSBlockingPeriod"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSBlockingPeriod_explain}")."</td>
	</tr>	
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveModEvasive()")."</td>
	</tr>
	</table>
	
	<script>
	var x_SaveModEvasive=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}			
		RefreshTab('main_config_modeva');
	}	
	
	function SaveModEvasive(){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		if(document.getElementById('DOSEnable').checked){XHR.appendData('DOSEnable',1);}else{XHR.appendData('DOSEnable',0);}
		XHR.appendData('DOSHashTableSize',document.getElementById('DOSHashTableSize').value);
		XHR.appendData('DOSPageCount',document.getElementById('DOSPageCount').value);
		XHR.appendData('DOSSiteCount',document.getElementById('DOSSiteCount').value);
		XHR.appendData('DOSPageInterval',document.getElementById('DOSPageInterval').value);
		XHR.appendData('DOSSiteInterval',document.getElementById('DOSSiteInterval').value);
		XHR.appendData('DOSBlockingPeriod',document.getElementById('DOSBlockingPeriod').value);
    	XHR.sendAndLoad('$page', 'POST',x_SaveModEvasive);		
	}
	
	function EvasiveDisabled(){
		document.getElementById('DOSHashTableSize').disabled=true;
		document.getElementById('DOSPageCount').disabled=true;
		document.getElementById('DOSSiteCount').disabled=true;
		document.getElementById('DOSPageInterval').disabled=true;
		document.getElementById('DOSSiteInterval').disabled=true;
		document.getElementById('DOSBlockingPeriod').disabled=true;
	
	
		if(document.getElementById('DOSEnable').checked){	
			document.getElementById('DOSHashTableSize').disabled=false;
			document.getElementById('DOSPageCount').disabled=false;
			document.getElementById('DOSSiteCount').disabled=false;
			document.getElementById('DOSPageInterval').disabled=false;
			document.getElementById('DOSSiteInterval').disabled=false;
			document.getElementById('DOSBlockingPeriod').disabled=false;	
			}		
	}
	EvasiveDisabled();
	</script>	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function DosEngineSave(){
	$freeweb=new freeweb($_POST["servername"]);
	while (list ($num, $ligne) = each ($_POST) ){
		$freeweb->Params["mod_evasive"][$num]=$ligne;
	}
	
	$freeweb->SaveParams();

}

function whitelist(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	


	$html="
	<div class=explain>{DDOS_WHITE_EXPLAIN}</div>
<center>
<table style='width:95%' class=form>
<tr>
	<td class=legend>{whitelist}:</td>
	<td>". Field_text("white_area_search",null,"font-size:13px;padding:3px",null,null,null,false,"EvasiveWhiteSearchCheck(event)")."</td>
	<td width=1%>". button("{search}","SecModSearch()")."</td>
</tr>
</table>
</center>
<div id='EvasiveWhiteList' style='width:100%;height:550px;overflow:auto'></div>
	<script>
		function EvasiveWhiteSearchCheck(e){
			if(checkEnter(e)){EvasiveWhiteSearch();}
		}
	
	
		function EvasiveWhiteSearch(){
			var se=escape(document.getElementById('white_area_search').value);
			LoadAjax('EvasiveWhiteList','$page?whitelist-list=yes&servername={$_GET["servername"]}&search='+se);
		
		}
	
		EvasiveWhiteSearch();
	</script>
	";

	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function whitelist_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$rule_text=$tpl->_ENGINE_parse_body("{rule}");
	$DDOS_WHITE_ADD=$tpl->javascript_parse_text("{DDOS_WHITE_ADD}");
	$add=imgtootltip("plus-24.png","{add}","AddWhiteEvasive()");	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=1%>$add</th>
		<th >{whitelist}:{$_GET["servername"]}</th>
		<th width=1%>".imgtootltip("delete-32.png","{delete_all}","DeleteAllEvasiveRules()")."</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	if(strlen($_GET["search"])>0){
		$search="*{$_GET["search"]}*";
		$search=str_replace("*","%",$search);
		$search=str_replace("%%","%",$search);
		$qsearch="AND `ipaddr` LIKE '$search'";
	}
	
	$sql="SELECT * FROM freewebs_whitelist WHERE servername='{$_GET["servername"]}' $qsearch ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code>$sql</code>";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","DeleteWhiteList({$ligne["ID"]})");
		
		
		$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/rule-24.png'></td>
		<td style='font-size:14px'>{$ligne["ipaddr"]}</td>
		<td>$delete</td>
		</tr>
		";
			
	}
	
	$html=$html."
	</table>
	<script>
	
	var x_DeleteAllRules=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}			
		RefreshTab('main_config_modeva');
	}	
		
	
	function DeleteAllEvasiveRules(){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		XHR.appendData('DeleteAllRules','yes');
    	XHR.sendAndLoad('$page', 'POST',x_DeleteAllRules);		
	}
	
	function AddWhiteEvasive(){
		var ip=prompt('$DDOS_WHITE_ADD');
		if(ip){
			var XHR = new XHRConnection();
			XHR.appendData('servername','{$_GET["servername"]}');
			XHR.appendData('AddWhiteEvasive',ip);
    		XHR.sendAndLoad('$page', 'POST',x_DeleteAllRules);	
		}
	
	}
	
	function DeleteWhiteList(ID){
		var XHR = new XHRConnection();
		XHR.appendData('servername','{$_GET["servername"]}');
		XHR.appendData('DeleteWhiteList',ID);
    	XHR.sendAndLoad('$page', 'POST',x_DeleteAllRules);		
	}
	


	
</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);

	
}



function whitelist_delete_all(){
	$sql="DELETE FROM freewebs_whitelist WHERE servername='{$_REQUEST["servername"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	add_default_rules();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");
	
}

function whitelist_delete(){
	if(!is_numeric($_POST["DeleteWhiteList"])){return;}
	$sql="DELETE FROM freewebs_whitelist WHERE servername='{$_REQUEST["servername"]}' AND ID={$_POST["DeleteWhiteList"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");		
}
function whitelist_add(){
	$sql="INSERT IGNORE INTO freewebs_whitelist (`servername`,`ipaddr`) VALUES ('{$_REQUEST["servername"]}','{$_REQUEST["AddWhiteEvasive"]}');";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_REQUEST["servername"]}");
}



function events(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$add=imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_modsecu')");
		$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=1%>$add</th>
		<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("freeweb.php?mode-security-log=yes&servername={$_GET["servername"]}")));
	while (list ($num, $ligne) = each ($datas) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if(trim($ligne)==null){continue;}
		
		
		$html=$html."
		<tr class=$classtr>
		
		<td style='font-size:11px' colspan=2>$ligne</td>
		</tr>
		
		";
			
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

/*
 * CentOS
http://blog.penumbra.be/2009/12/apache-mod_evasive-ddos-prevention-on-a-centos-5-x-plesk-environment/

OpenSuse
http://www.novell.com/communities/node/3025/protecting-apache-against-dos-attack-modevasive

 */
?>