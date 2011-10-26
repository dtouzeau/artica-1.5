<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){$tpl=new templates();echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";exit;}
	
	if(isset($_GET["tasks"])){tasks();exit;}
	if(isset($_GET["events"])){events_popup();exit;}
	if(isset($_GET["events-list"])){events_list();exit;}
	
	if(isset($_GET["re-categorize-js"])){re_categorize_js();exit;}
	if(isset($_GET["re-categorize-popup"])){re_categorize_popup();exit;}
	if(isset($_POST["RecategorizeProxyStats"])){re_categorize_save();exit;}
	tabs();
	
	
function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["tasks"]='{tasks}';
	$array["events"]='{events}';

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span style='font-size:14px'>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_squid_stats_tasks style='width:100%;height:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_squid_stats_tasks').tabs();
			
			
			});
		</script>";		
}	

function tasks(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$GLOBALS["ICON_FAMILY"]="STATISTICS";
	$schedule_recategorize=Paragraphe("64-categories-task.png", "{recategorize_schedule}", "{recategorizewww_schedule_text}","javascript:Loadjs('$page?re-categorize-js=yes')");
	
	$tr[]=$schedule_recategorize;
	
	echo $tpl->_ENGINE_parse_body("<table><tbody>".CompileTr2($tr)."</tbody></table>");
	
}

function re_categorize_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{recategorize_schedule}");
	$html="YahooWin2('650','$page?re-categorize-popup=yes','$title');";
	echo $html;
	}
function re_categorize_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$sock=new sockets();
	$RecategorizeProxyStats=$sock->GET_INFO("RecategorizeProxyStats");
	$RecategorizeSecondsToWaitOverload=$sock->GET_INFO("RecategorizeSecondsToWaitOverload");
	$RecategorizeMaxExecutionTime=$sock->GET_INFO("RecategorizeSecondsToWaitOverload");
	if(!is_numeric($RecategorizeSecondsToWaitOverload)){$RecategorizeSecondsToWaitOverload=30;}
	if(!is_numeric($RecategorizeMaxExecutionTime)){$RecategorizeMaxExecutionTime=210;}
	if(!is_numeric($RecategorizeProxyStats)){$RecategorizeProxyStats=1;}		
	$RecategorizeCronTask=$sock->GET_INFO("RecategorizeCronTask");
	if($RecategorizeCronTask==null){$RecategorizeCronTask="0 5 * * *";}
	
	$js="Loadjs('cron.php?field=RecategorizeCronTask')";
	
	$html="<div class=explain style='font-size:14px' id='www_recategorize_explain'>{www_recategorize_explain}</div>
	<input type='hidden' id='RecategorizeCronTask' value='$RecategorizeCronTask'>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend style='font-size:14px'>{enable}:</td>
		<td>". Field_checkbox("RecategorizeProxyStats", 1,$RecategorizeProxyStats,"RecategorizeProxyStatsCheck()")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:14px'>{schedule}:</td>
		<td>". texttooltip("{schedule}","{edit}:{schedule}",$js,null,0,"font-size:14px;font-weight:bold;text-decoration:underline")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{RecategorizeSecondsToWaitOverload}:</td>
		<td style='font-size:14px'>". Field_text("RecategorizeSecondsToWaitOverload",$RecategorizeSecondsToWaitOverload,"font-size:14px;width:90px")."&nbsp;{seconds}</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:14px'>{MaxExecutionTime}:</td>
		<td style='font-size:14px'>". Field_text("RecategorizeMaxExecutionTime",$RecategorizeMaxExecutionTime,"font-size:14px;width:90px")."&nbsp;{minutes}</td>
	</tr>		
	<tr>
	<td colspan=2 align='right'><hr>". button("{apply}", "SaveRecategoProxy()")."</td>
	</tr>
	
	</tbody>
	</table>
	
	<script>
		function SaveRecategorizeSchedule(value){
			document.getElementById('RecategorizeCronTask').value=value;
		}
		
	var x_SaveRecategoProxy= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();
			}			
		
		function RecategorizeProxyStatsCheck(){
			document.getElementById('RecategorizeCronTask').disabled=true;
			document.getElementById('RecategorizeSecondsToWaitOverload').disabled=true;
			document.getElementById('RecategorizeMaxExecutionTime').disabled=true;
			if(document.getElementById('RecategorizeProxyStats').checked){
				document.getElementById('RecategorizeCronTask').disabled=false;
				document.getElementById('RecategorizeSecondsToWaitOverload').disabled=false;
				document.getElementById('RecategorizeMaxExecutionTime').disabled=false;			
			}
		}
		
		function SaveRecategoProxy(){
			var XHR = new XHRConnection();
			if(document.getElementById('RecategorizeProxyStats').checked){XHR.appendData('RecategorizeProxyStats','1');}else{XHR.appendData('RecategorizeProxyStats','0');}
			XHR.appendData('RecategorizeCronTask',document.getElementById('RecategorizeCronTask').value);
			XHR.appendData('RecategorizeSecondsToWaitOverload',document.getElementById('RecategorizeSecondsToWaitOverload').value);
			XHR.appendData('RecategorizeMaxExecutionTime',document.getElementById('RecategorizeMaxExecutionTime').value);
			AnimateDiv('www_recategorize_explain');
			XHR.sendAndLoad('$page', 'POST',x_SaveRecategoProxy);
		}
		
		
		RecategorizeProxyStatsCheck();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function re_categorize_save(){
	$sock=new sockets();
	$sock->SET_INFO("RecategorizeProxyStats", $_POST["RecategorizeProxyStats"]);
	$sock->SET_INFO("RecategorizeCronTask", $_POST["RecategorizeCronTask"]);
	$sock->SET_INFO("RecategorizeSecondsToWaitOverload", $_POST["RecategorizeSecondsToWaitOverload"]);
	$sock->SET_INFO("RecategorizeMaxExecutionTime", $_POST["RecategorizeMaxExecutionTime"]);
	$sock->getFrameWork("squid.php?recategorize-task=yes");
	
}


function events_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	
	$sql="SELECT category FROM updateblks_events GROUP BY category ORDER BY category";
	$results=$q->QUERY_SQL($sql);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){$cat[$ligne["category"]]=$ligne["category"];}
	
	$sql="SELECT `function` FROM updateblks_events GROUP BY `function` ORDER BY `function`";
	$results=$q->QUERY_SQL($sql);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){$function[$ligne["function"]]=$ligne["function"];}	
	
	$function[null]="{select}";
	$cat[null]="{select}";
	
	$html="<div class=explain>{statistics_tasks_events_explain}</div>
	<center>
		<table style='width:70%' class=form>
			<tbody>
				<tr>
					<td class=legend style='font-size:14px'>{events}:<td>
					<td>". Field_text("stats-events-search",null,"font-size:14px;width:220px",null,null,null,false,"StatsEventsSearchcheck(event)")."</td>
					<td class=legend style='font-size:14px'>{category}:<td>
					<td>". Field_array_Hash($cat,"statscat",null,"StatsEventsSearch()",null,null,"font-size:14px")."</td>
					<td class=legend style='font-size:14px'>{function}:<td>
					<td>". Field_array_Hash($function,"statsfunc",null,"StatsEventsSearch()",null,null,"font-size:14px")."</td>					
					<td width=1%>". button("{search}","StatsEventsSearch()")."<td>
				</tr>
			</tbody>
		</table>
	</center>
	<div id='stats-events-list' style='width:100%;height:450px;overflow:auto'></div>
	
	<script>
		function StatsEventsSearchcheck(e){
			if(checkEnter(e)){StatsEventsSearch();}
		}
		
		function StatsEventsSearch(){
			var se=escape(document.getElementById('stats-events-search').value);
			var cat=escape(document.getElementById('statscat').value);
			var func=escape(document.getElementById('statsfunc').value);
			LoadAjax('stats-events-list','$page?events-list=yes&search='+se+'&cat='+cat+'&func='+func);
		
		}
	StatsEventsSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function events_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	$search=$_GET["search"];
	if($search<>null){$search="*$search*";$search=str_replace("**", "%", $search);$search=str_replace("**", "%", $search);$search=str_replace("*", "%", $search);}else{$search="%";}
	if($_GET["cat"]<>null){$cat=" AND category='{$_GET["cat"]}'";}
	if($_GET["func"]<>null){$func=" AND `function`='{$_GET["func"]}'";}
	
	$today=date("Y-m-d");
	$hier=$q->HIER();
	
	$sql="SELECT * FROM updateblks_events WHERE `text` LIKE '$search' $cat $func ORDER BY zDate DESC LIMIT 0,75";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
	
		$html="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>{date}</th>
		<th width=99%>{event}</th>
	</tr>
</thead>
<tbody>";
	
		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$ligne["zDate"]=str_replace($today, "{today}", $ligne["zDate"]);
			$ligne["zDate"]=str_replace($hier, "{yesterday}", $ligne["zDate"]);
			$soustext="<div style='font-size:11px;text-align:right'>PID:{$ligne["PID"]}&nbsp;|&nbsp;F:{$ligne["function"]}&nbsp;|&nbsp;{category}:{$ligne["category"]}</div>";	
			$ligne["text"]=nl2br($ligne["text"]);
			$html=$html."
				<tr class=$classtr>
					<td width=1%  style='font-size:12px;' nowrap valign='middle'>{$ligne["zDate"]}</td>
					<td width=99% style='font-size:13px;'><div>{$ligne["text"]}</div>$soustext</td>
				</tr>
				";	

		}			
$html=$html."</tbody></table></div>";
echo $tpl->_ENGINE_parse_body($html);	
	
	
}
