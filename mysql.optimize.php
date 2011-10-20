<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
$GLOBALS["ICON_FAMILY"]="SYSTEM";
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

	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["dbname"])){dbname_save();exit;}
	if(isset($_POST["EnableMysqlOptimize"])){EnableMysqlOptimizeSave();exit;}
	if(isset($_GET["launch-opimize-js"])){launch_opimize_js();exit();}
	if(isset($_POST["launch-opimize"])){launch_opimize_perform();exit;}
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{mysql_defrag}");
	$html="YahooWin3('795','$page?tabs=yes','$title');";
	echo $html;
	
	
}

function launch_opimize_perform(){
	$tpl=new templates();
	$sock=new sockets();
	$sock->getFrameWork("services.php?optimize-mysql-db=yes");
	echo $tpl->javascript_parse_text("{apply_upgrade_help}");
}

function launch_opimize_js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$error_want_operation=$tpl->javascript_parse_text('{error_want_operation}');
$html="

	var x_LaunchOptimize= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_mysql_optimize');
	}	
 	
 function LaunchOptimize(){
 	var value=0;
 	if(confirm('$error_want_operation')){
		var XHR = new XHRConnection();
		XHR.appendData('launch-opimize','yes');
		XHR.sendAndLoad('$page', 'POST',x_LaunchOptimize); 
	}	
 
 }
LaunchOptimize();
";	

echo $html;
}

function tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$array["popup"]='{parameters}';
	$array["events"]='{events}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="events"){
			$html[]= "<li><a href=\"mysql.admin.events.php?$num=yes\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
	}
	
	
	$html= "
	<div id=main_config_mysql_optimize style='width:100%;height:630px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_mysql_optimize').tabs();
			});
		</script>";		
	echo $tpl->_ENGINE_parse_body($html);
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$q=new mysql();
	
	
	$EnableMysqlOptimize=$sock->GET_INFO("EnableMysqlOptimize");
	$MysqlOptimizeSchedule=$sock->GET_INFO("MysqlOptimizeSchedule");
	if(!is_numeric("$EnableMysqlOptimize")){$EnableMysqlOptimize=0;}
	$ARRAY=unserialize(base64_decode($sock->GET_INFO("MysqlOptimizeDBS")));
	
	
	
	
	$html="
	<div id='x_SaveDBEnableOptimize2ID'>
	<input type='hidden' id='MysqlOptimizeSchedule' value='$MysqlOptimizeSchedule'>
	<table style='width:100%'>
	<tbody>
	<tr>
	<td valign='top' width=1%>". Paragraphe("database-restore-64.png", "{launch_optimize}", "{launch_optimize_text}","javascript:Loadjs('$page?launch-opimize-js=yes')")."</td>
	<td valign='top'><div class=explain>{mysql_defrag_explain}</div></td>
	</tr>
	</tbody>
	</table>

	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable} : {mysql_defrag}:</td>
		<td>". Field_checkbox("EnableMysqlOptimize",1,$EnableMysqlOptimize,"EnableMysqlOptimizeSave()")."</td>
	</tr>
	<tr>
		<td class=legend>{schedule}:</td>
		<td><input type=button OnClick=\"javascript:Loadjs('cron.php?field=MysqlOptimizeSchedule&function2=MysqlOptimizeScheduleUpdateForm');\" value='{browse}&nbsp;&raquo;&raquo;'></td>
	</tr>	
	
	</table>
	</div>
	<div style='margin-top:10px;font-size:16px'>{databases}:</div>
	<table style='width:100%' class=form>
	";
	
	
	$DATABASE_LIST=$q->DATABASE_LIST();
	while (list ($num, $ligne) = each ($DATABASE_LIST) ){
		$jsdb[]="if(!document.getElementById('EnableMysqlOptimize').checked){document.getElementById('database-$num').disabled=true;}else{document.getElementById('database-$num').disabled=false;}";
		
		
$html=$html."<tr>
		<td class=legend>$num:</td>
		<td>". Field_checkbox("database-$num",1,$ARRAY[$num],"SaveDBEnableOptimize('$num')")."</td>
	</tr>
	";	
		
		
	}
	
	
$html=$html."</tbody></table>
<script>
 function EnableMysqlOptimizeCheck(){
 	". @implode("\n", $jsdb)."
 	
 	}
	var x_SaveDBEnableOptimize= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		EnableMysqlOptimizeCheck();
	}

	var x_SaveDBEnableOptimize2= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_mysql_optimize');
	}	
 	
 function SaveDBEnableOptimize(database){
 	var value=0;
 	if(document.getElementById('database-'+database).checked){value=1;}else{value=0;}
	var XHR = new XHRConnection();
	XHR.appendData('value',value);
	XHR.appendData('dbname',database);
	XHR.sendAndLoad('$page', 'POST',x_SaveDBEnableOptimize); 	
 
 }
 
 function MysqlOptimizeScheduleUpdateForm(){
  		var value=0;
 		if(document.getElementById('EnableMysqlOptimize').checked){value=1;}else{value=0;}
 		var XHR = new XHRConnection();
		XHR.appendData('EnableMysqlOptimize',value);
		XHR.appendData('MysqlOptimizeSchedule',document.getElementById('MysqlOptimizeSchedule').value);
		AnimateDiv('x_SaveDBEnableOptimize2ID');
		XHR.sendAndLoad('$page', 'POST',x_SaveDBEnableOptimize2); 
 
 }
 
 function EnableMysqlOptimizeSave(){
 		var value=0;
 		if(document.getElementById('EnableMysqlOptimize').checked){value=1;}else{value=0;}
 		var XHR = new XHRConnection();
		XHR.appendData('EnableMysqlOptimize',value);
		XHR.sendAndLoad('$page', 'POST',x_SaveDBEnableOptimize); 	 		
 }

EnableMysqlOptimizeCheck(); 	
 </script>
 


";	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function EnableMysqlOptimizeSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableMysqlOptimize", $_POST["EnableMysqlOptimize"]);
	if(isset($_POST["MysqlOptimizeSchedule"])){
		$sock->SET_INFO("MysqlOptimizeSchedule", $_POST["MysqlOptimizeSchedule"]);	
	}
	$sock->getFrameWork("services.php?optimize-mysql-cron=yes");
}


function dbname_save(){
	
	$sock=new sockets();
	$ARRAY=unserialize(base64_decode($sock->GET_INFO("MysqlOptimizeDBS")));
	$ARRAY[$_POST["dbname"]]=$_POST["value"];
	$sock->SaveConfigFile(base64_encode(serialize($ARRAY)), "MysqlOptimizeDBS");
	$sock->getFrameWork("services.php?optimize-mysql-cron=yes");
	
}
