<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
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
	
	if(isset($_GET["scripts"])){echo scripts();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["databases"])){databases_status();exit;}
	if(isset($_GET["ufdbg-community"])){community_status();exit;}
	if(isset($_GET["maintenance"])){maintenance_status();exit;}
	if(isset($_GET["CompileMissingdb"])){CompileMissingdb();exit;}
	if(isset($_GET["MaintenanceReCompileDB"])){maintenance_status_list_compile();exit;}
	if(isset($_GET["CompileAlldbs"])){CompileAlldbs();exit;}
	if(isset($_GET["schedule"])){schedule();exit;}
	if(isset($_GET["EnableSchedule"])){schedule_save();exit;}
	if(isset($_GET["maintenance-status-list"])){maintenance_status_list();exit;}
	if(isset($_GET["config-file"])){config_file();exit;}
	
	if(isset($_GET["events"])){events();exit;}
	popup();
	
function popup(){
	$page=CurrentPageName();
	$html="<div id='ufdbguard-tabs'></div>
	<script>LoadAjax('ufdbguard-tabs','$page?tabs=yes');</script>";echo $html;
}
function tabs(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["databases"]='{databases}';
	$array["maintenance"]='{maintenance}';
	$array["events"]='{events}';
	
	
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	echo "
	<div id=ufdbguard-tabs-all style='width:100%;height:670px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#ufdbguard-tabs-all').tabs();
				});
		</script>";			
}

function databases_status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<div class=explain>{ufdbgdb_explain}</div>
	<div style='font-size:16px;margin:10px'>{artica_community}:</div>
	<div id='ufdbg-community' style='width:100%'></div>
	
	
	<script>
		LoadAjax('ufdbg-community','$page?ufdbg-community=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function community_status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$all_count=FormatNumber($q->COUNT_ROWS("dansguardian_community_categories","artica_backup"),0,'.',' ',3);
	$artica_community=Paragraphe('webfilter-community-64.png','{community}','{webfilter_community_text}',"javascript:Loadjs('webfilter.community.php')");
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html="	
	
	<table style='width:100%'>
	<tr>
		<td width=99% valign='top'>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th colspan=2></th>
		<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>
	<tr class=$classtr>
		<td style='font-size:14px' colspan=2><i>{websites_number}:</i>&nbsp;<strong>$all_count</i></td>
	</tr>
	";
	
	$array=unserialize(@file_get_contents("ressources/logs/web.community.db.status.txt"));
	
	while (list ($num, $ligne) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$f=FormatNumber($ligne,0,'.',' ',3);
		$html=$html."<tr class=$classtr>
		<td style='font-size:14px;text-align:right' width=1% nowrap><strong>$num:</td>
		<td style='font-size:14px' width=99%><strong>$f</strong></td>
	</tr>";
	}
	
	$html=$html."</table>
	</td>
	<td valign='top' width=1%>$artica_community</td>
	</tr>
	</table>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function FormatNumber($number, $decimals = 0, $thousand_separator = '&nbsp;', $decimal_point = '.'){ 
	$tmp1 = round((float) $number, $decimals);
  while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1)
    $tmp1 = $tmp2;
  return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
} 


function maintenance_status(){
	$arraydb=unserialize(@file_get_contents("ressources/logs/ufdbguard.db.status.txt"));
	$countDeToCompile=count($arraydb);
	if($countDeToCompile>0){
		$compile_missing=Paragraphe("database-spider-compile-64.png","{compile_missing_db}",
		"{compile_missing_db_www_text}","javascript:CompileMissingdb()");
		$warn="
		<table style='width:100%'>
		<tr>
		<td width=1% valign='top'><img src='img/database-error-48.png'></td>
		<td width=99% valign='top'><div class=explain style='color:#8D0707'><strong style='font-size:16px'>$countDeToCompile !:</strong> {databases_are_not_compiled_warn}</div>
		<td width=1% valign='top'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('ufdbguard-tabs-all')")."</td>
		</td>
		</tr>
		</table>";
		
	}
	
	$recompile_all_database=Paragraphe("database-spider-compile2-64.png",
	"{recompile_all_db}","{recompile_all_db_ww_text}","javascript:ReCompiledb()");
	
	$compile_schedule=Paragraphe("clock-gold-64.png","{compilation_schedule}","{compilation_schedule_text}","javascript:ReCompileScheduleudbg()");
	
	$tr[]=$compile_missing;
	$tr[]=$recompile_all_database;
	$tr[]=$compile_schedule;
	
	$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
	
	$html="$html
	<div id='action-div'></div>
	$warn".implode("\n",$tables);		  

	 
$tpl=new templates();
$THINCLIENT_REBUILDED_TEXT=$tpl->javascript_parse_text("{THINCLIENT_REBUILDED_TEXT}");

$page=CurrentPageName();
$script="
<div id='dbguard-list-size' style='height:250px;overflow:auto'></div>

<script>
	
". scripts()."
		
	maintenance_status_list();	
	

</script>";

$html=$tpl->_ENGINE_parse_body("$html$script","squid.newbee.php,squid.index.php");

echo $html;		
	
	
}

function scripts(){
$page=CurrentPageName();
$tpl=new templates();	
$compilation_schedule=$tpl->javascript_parse_text("{compilation_schedule}");
$WARNING_UFDBGUARD_COMPILES_RULES_ASK=$tpl->javascript_parse_text("WARNING_UFDBGUARD_COMPILES_RULES_ASK");
if($_GET["scripts"]=="recompile"){$start="ReCompiledb()";}
if($_GET["scripts"]=="compile-schedule"){$start="ReCompileScheduleudbg()";}
if($_GET["scripts"]=="config-file"){$start="ConfigFile()";}




	
return "var x_CompileMissingdb= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			if(document.getElementById('ufdbguard-tabs-all')){RefreshTab('ufdbguard-tabs-all');}
		}	
		
	function ReCompileScheduleudbg(){
		YahooWin(265,'$page?schedule=yes','$compilation_schedule');
	}
	
	function CompileMissingdb(){
			var XHR = new XHRConnection();
			XHR.appendData('CompileMissingdb','yes');
			document.getElementById('action-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_CompileMissingdb);	
		}	
	function ReCompiledb(){
			if(confirm('$WARNING_UFDBGUARD_COMPILES_RULES_ASK')){
				var XHR = new XHRConnection();
				XHR.appendData('CompileAlldbs','yes');
				if(document.getElementById('action-div')){AnimateDiv('action-div');}
				XHR.sendAndLoad('$page', 'GET',x_CompileMissingdb);	
			}
		}

	function maintenance_status_list(){
		LoadAjax('dbguard-list-size','$page?maintenance-status-list=yes');
	
	}
	
	function ConfigFile(){
		YahooWin5(880,'$page?config-file=yes','ufdbguard.conf');
	}
	
	$start;";	
	
	
}

function maintenance_status_list(){
	$sock=new sockets();
	$sock->getFrameWork("ufdbguard.php?db-size=yes");
	$page=CurrentPageName();
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=99%>". imgtootltip("refresh-32.png","{refresh}","maintenance_status_list()")."</th>
		<th width=1%>{size}</th>
		<th width=1%>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>	
	";
	
	$datas=unserialize(@file_get_contents("ressources/logs/ufdbguard.db.size.txt"));
	while (list ($filepath, $size) = each ($datas) ){
		preg_match("#\/var\/lib/squidguard\/(.+?)\/domains\.ufdb#",$filepath,$re);
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$size=$size/1024;
		$size=FormatBytes($size);
		$html=$html."<tr class=$classtr>
			<td style='font-size:14px;text-align:right' width=99% nowrap><strong>{$re[1]}</strong></td>
			<td style='font-size:14px' width=1%><strong>$size</strong></td>
			<td style='font-size:14px' width=1% nowrap>". button("{compile}","MaintenanceReCompileDB('{$re[1]}')")."</td>
		</tr>";			
		}
		
		
	$html=$html."</table>
	<script>
		function MaintenanceReCompileDB(db){
			var XHR = new XHRConnection();
			XHR.appendData('MaintenanceReCompileDB',db);
			document.getElementById('action-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_CompileMissingdb);	
		
		}
		
	</script>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
		
		
	}
	
function maintenance_status_list_compile(){
	$tpl=new templates();
	$THINCLIENT_REBUILDED_TEXT=$tpl->javascript_parse_text("{THINCLIENT_REBUILDED_TEXT}");
	echo $THINCLIENT_REBUILDED_TEXT;	
	$sock=new sockets();
	$sock->getFrameWork("ufdbguard.php?recompile=".urlencode($_GET["MaintenanceReCompileDB"]));	
	
}
	
	




function CompileMissingdb(){
	$tpl=new templates();
	$THINCLIENT_REBUILDED_TEXT=$tpl->javascript_parse_text("{THINCLIENT_REBUILDED_TEXT}");
	echo $THINCLIENT_REBUILDED_TEXT;	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ufdbguard-recompile-missing-dbs=yes");
}
function CompileAlldbs(){
	$tpl=new templates();
	$THINCLIENT_REBUILDED_TEXT=$tpl->javascript_parse_text("{THINCLIENT_REBUILDED_TEXT}");
	echo $THINCLIENT_REBUILDED_TEXT;	
	$sock=new sockets();
	$sock->getFrameWork("ufdbguard.php?recompile-dbs=yes");
}


function events(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?ufdbguard-compilator-events=yes")));
	$html="	
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('ufdbguard-tabs-all')")."</th>
		</tr>
	</thead>
	<tbody class='tbody'>
	";	
	krsort($datas);
	while (list ($key, $line) = each ($datas) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if(trim($line)==null){continue;}
		$line=htmlspecialchars($line);
		$line=str_replace("exec.squidguard.php","",$line);
		$line=str_replace("exec.squidguard.php","",$line);
		$line=str_replace(":: :",":",$line);
		$line=str_replace("#STRONG#","<strong>",$line);
		$line=str_replace("#!STRONG#","</strong>",$line);
		
		
		$html=$html."<tr class=$classtr>
			<td style='font-size:11px;' width=100% nowrap><code>$line</code></td>
		</tr>";
	}
	
	$html=$html."</table>";
	echo $html;
	
}

function schedule(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$UfdbGuardSchedule=unserialize(base64_decode($sock->GET_INFO("UfdbGuardSchedule")));
	
	for($i=0;$i<24;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$hours[$i]=$t;
	}
	for($i=0;$i<60;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$mins[$i]=$t;
	}	
	
	$html="
	<div id='ufdb-schedule-form'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable}:</td>
		<td colspan=3>". Field_checkbox("EnableSchedule",1,$UfdbGuardSchedule["EnableSchedule"])."</td>
	</tr>
	<tr>
		<td class=legend>{every_day_at}:</td>
		<td width=1%>". Field_array_Hash($hours,"ufdb-h",$UfdbGuardSchedule["H"],"style:font-size:16px;padding:3px")."</td>
		<td width=1%style='font-size:16px'>&nbsp;:&nbsp;</td>
		<td width=1%>". Field_array_Hash($mins,"ufdb-m",$UfdbGuardSchedule["M"],"style:font-size:16px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=4 align='right'><hr>". button("{apply}","SaveUfdbdbSched()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_SaveUfdbdbSched= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('ufdbguard-tabs-all');
			YahooWinHide();
		}	
		

	
	function SaveUfdbdbSched(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableSchedule').checked){XHR.appendData('EnableSchedule','1');}else{XHR.appendData('EnableSchedule','0');}
			XHR.appendData('H',document.getElementById('ufdb-h').value);
			XHR.appendData('M',document.getElementById('ufdb-m').value);
			document.getElementById('ufdb-schedule-form').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveUfdbdbSched);	
		}		
	
</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function schedule_save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"UfdbGuardSchedule");
	$sock->getFrameWork("cmd.php?ufdbguard-compile-schedule=yes");
}

function config_file(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("squid.php?ufdbguardconf=yes")));
	$html="<div style='width:100%;height:660px;overflow:auto'>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	while (list ($key, $line) = each ($datas) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	$line=htmlentities($line);
	$line=str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $line);
	$line=str_replace(" ", "&nbsp;", $line);
		$html=$html."<tr class=$classtr style='height:auto'>
		<td width=1% style='font-size:13px;height:auto'>$key</td>
		<td width=99% style='font-size:13px;height:auto'><code style='font-size:13px;height:auto'>$line</code></td>
	</tR>
		";
	}
	
	$html=$html."</tbody></table></div>";
	echo $tpl->_ENGINE_parse_body($html);
}