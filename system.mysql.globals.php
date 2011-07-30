<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.os.system.inc');
	
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	if(isset($_GET["list-keys-search"])){slist();exit;}
	if(isset($_POST["mysqlSSL"])){mysqlSSLSave();exit;}
	if(isset($_POST["GenerateMysqlSSLKeys"])){GenerateMysqlSSLKeys();exit;}
page();


function page(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$t=time();	
$html="
	<center>
	<table style='width:100%' class=form style='width:50%'>
	<tr>
	<td class=legend>{search}:</td>
	<td>". Field_text("mysql-key-search",null,"font-size:14px;padding:3px",null,null,null,false,"MysqlSearchCheck(event)")."</td>
	</tr>
	</table>
	</center>
	<div id='$t'></div>
	<script>
		function MysqlSearchCheck(e){
			if(checkEnter(e)){MysqlsSearch();}
		}
		function MysqlsSearch(){
			var se=escape(document.getElementById('mysql-key-search').value);
			LoadAjax('$t','$page?list-keys-search=yes&search='+se);
		}
			
		MysqlsSearch();	
	</script>
	";
		echo $tpl->_ENGINE_parse_body($html);
}


function slist(){
	
	$search=trim($_GET["search"]);
	
	if($search<>null){
		$search=str_replace(".", "\.", $search);
		$search=str_replace("*", ".*?", $search);
		$search=str_replace(" ", "\s", $search);
	}
	$q=new mysql();
	$array=$q->SHOW_VARIABLES();
	$tpl=new templates();
	$page=CurrentPageName();	
		$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-24.png","{refresh}","MysqlsSearch()")."</th>
		<th width=1%>&nbsp;</th>
		<th width=99%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
		while (list ($num, $ligne) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne==null){$ligne="&nbsp;";}
		if($search<>null){if(!preg_match("#$search#", $num)){continue;}}
		if(strpos($ligne, ",")){
			$tbl=explode(",", $ligne);
			$ligne=@implode("<br>", $tbl);
		}
		
		
		$color="black";
		if($ligne=="OFF"){$color="#9B9999";}
		if($ligne=="NO"){$color="#9B9999";}
		if($ligne=="DISABLED"){$color="#9B9999";}
		if(is_numeric($ligne)){if($ligne<1){$color="#9B9999";}}
		
		
		$html=$html."
		<tr class=$classtr>
			
			<td width=1% style='font-size:14px;font-weight:bold;color:$color' colspan=2>$num</td>
			<td width=99% style='font-size:14px;font-weight:bold;color:$color'>$ligne</td>
		</tr>
		";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}