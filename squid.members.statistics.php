<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["squid-general-status"])){general_status();exit;}
	if(isset($_GET["squid-status-stats"])){squid_status_stats();exit;}
	
	if(isset($_GET["squid-status-graphs"])){general_status_graphs();exit;}
	if(isset($_GET["squid-cache-flow-performance"])){general_status_cache_graphs();exit;}
	
	if(isset($_GET["day-consumption"])){day_consumption();exit;}
	
	
	
tabs();


function tabs(){
	
	$page=CurrentPageName();
	
	$tpl=new templates();
	$array["status"]='{status}';
	$array["day-consumption"]='{days}';
	$array["week-consumption"]='{week}';
	$array["month-consumption"]='{month}';
	
	
	

while (list ($num, $ligne) = each ($array) ){
		if($num=="day-consumption"){
			$html[]= "<li><a href=\"squid.traffic.statistics.days.php?$num\"><span>$ligne</span></a></li>\n";
			continue;
		}
	
	
		$html[]= "<li><a href=\"$page?$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_stats_consumption style='width:100%;font-size:14px'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_stats_consumption').tabs();
			
			
			});
		</script>");		
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' width=1%><div id='squid-general-status'></div></td>
		<td valign='top' width=99%><div id='squid-status-graphs'></div></td>
	</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('squid-general-status','$page?squid-general-status=yes');
	
	</script>
	";
	
	echo $html;
	
	
	
	
}

function general_status(){
	$page=CurrentPageName();
	$tpl=new templates();		

	$stylehref="style='font-size:14px;font-weight:bold;text-decoration:underline'";
	$img="img/server-256.png";
	$html="
	<div class=form>
	<center style='margin:5px'>
	<img src='$img'>
	</center>
	<div id='squid-status-stats'></div>
	
	<p>&nbsp;</p>
	<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' style='font-size:14px'><a href=\"javascript:blur();\" OnClick=\"javascript:SquidFlowSizeQuery('size')\"$stylehref>{downloaded_flow}</a></td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><a href=\"javascript:blur();\" OnClick=\"javascript:SquidFlowSizeQuery('req')\"$stylehref>{requests}</a></td>
	</tr>		
	</tbody>
	</table>	
	
	<script>
		LoadAjax('squid-status-stats','$page?squid-status-stats=yes');	
		LoadAjax('squid-status-graphs','$page?squid-status-graphs=yes');
		
	</script>
	</div>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function squid_status_stats(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	
	$websitesnums=$q->COUNT_ROWS("visited_sites");
	$websitesnums=numberFormat($websitesnums,0,""," ");	
	
	
	$categories=$q->COUNT_CATEGORIES();
	$categories=numberFormat($categories,0,""," ");

	$q=new mysql_squid_builder();
	$requests=$q->EVENTS_SUM();
	$requests=numberFormat($requests,0,""," ");	
	
	$DAYSNumbers=$q->COUNT_ROWS("tables_day");
	
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT SUM(totalsize) as tsize FROM tables_day"));
	$totalsize=FormatBytes($ligne["tsize"]/1024);
	
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT AVG(cache_perfs) as pourc FROM tables_day"));
	$pref=round($ligne["pourc"]);	

$html="
<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' style='font-size:14px'><b>$DAYSNumbers</b> {daysOfStatistics}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$requests</b> {requests}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$websitesnums</b> {visited_websites}</td>
	</tr>		
	<tr>
		<td valign='top' style='font-size:14px'><b>$categories</b> {websites_categorized}</td>
	</tr>			
	<tr>
		<td valign='top' style='font-size:14px'><b>$totalsize</b> {downloaded_flow}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$pref%</b> {cache_performance}</td>
	</tr>	
	</tbody>
	</table>";

echo $tpl->_ENGINE_parse_body($html);
	
}

function general_status_graphs(){
	$page=CurrentPageName();
	$tpl=new templates();		
	
	if($_GET["month"]==null){$_GET["month"]=date('m');
	if($_GET["year"]==null){$_GET["year"]=date('Y');
	
	
	$q=new mysql_squid_builder();	
	$table="{$_GET["year"]}{$_GET["month"]}_day";
	$sql="SELECT $field,DATE_FORMAT(zDate,'%d') as tdate FROM tables_day WHERE $filter ORDER BY zDate";
	
	$results=$q->QUERY_SQL($sql);

	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		if($hasSize){$ydata[]=round(($ligne["totalsize"]/1024)/1000);}else{$ydata[]=$ligne["totalsize"];}
	}
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$file_prefix.$type.png";
	$gp=new artica_graphs();
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{days}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";

	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);return;}
	
	if($default_from_date==null){
		$sql="SELECT DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 30 DAY),'%Y-%m-%d') as tdate";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		$default_from_date=$ligne["tdate"];
	}
	
	if($default_to_date==null){
		$sql="SELECT DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d') as tdate";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		$default_to_date=$ligne["tdate"];
	}	
	
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tdate FROM tables_day ORDER BY zDate LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	$mindate=$ligne["tdate"];

	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tdate FROM tables_day ORDER BY zDate DESC LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	$maxdate=$ligne["tdate"];		
	
	echo $tpl->_ENGINE_parse_body("<div ><h3> $prefix_title/{days} - $selected_date</h3>
	<center>
	<img src='$targetedfile'>
	</center>
	</div>
	<table style='margin-top:10px' class=form>
	<tbody>
	<tr>
		<td class=legend nowrap>{from_date}:</td>
		<td>". field_date('from_date1',$default_from_date,"font-size:16px;padding:3px;width:95px","mindate:$mindate;maxdate:$maxdate")."</td>
		
		<td class=legend nowrap>{to_date}:</td>
		<td>". field_date('to_date1',$default_to_date,"font-size:16px;padding:3px;width:95px","mindate:$mindate;maxdate:$maxdate")."</td>
		<td width=1%>". button("{apply}","SquidFlowSizeQuery('$type')")."</td>
	</tr>
	</table>
	<p>&nbsp;</p>
	<div id='squid-cache-flow-performance'></div>
	
	<script>
		function SquidFlowSizeQuery(type){
			if(!type){type='';}
			var from=document.getElementById('from_date1').value;
			var to=document.getElementById('to_date1').value;
			LoadAjax('squid-status-graphs','$page?squid-status-graphs=yes&from='+from+'&to='+to+'&type='+type);
		
		}
		
		LoadAjax('squid-cache-flow-performance','$page?squid-cache-flow-performance=yes&from=$default_from_date&to=$default_to_date&type=$type');
		
	</script>
	
	");
	
}



function general_status_cache_graphs(){
	$page=CurrentPageName();
	$tpl=new templates();		
	
	
	
	$q=new mysql_squid_builder();	
	$selected_date="{last_30days}";
	$filter="zDate>DATE_SUB(NOW(),INTERVAL 30 DAY) AND zDate<DATE_SUB(NOW(),INTERVAL 1 DAY)";
	$file_prefix="default";
	
	if($_GET["from"]<>null){
		$filter="zDate>='{$_GET["from"]}' AND zDate<='{$_GET["to"]}'";
		$selected_date="{from_date} {$_GET["from"]} - {to_date} {$_GET["to"]}";
		$default_from_date=$_GET["from"];
		$default_to_date=$_GET["to"];
		$file_prefix="$default_from_date-$default_to_date";
	}
	
	if($_GET["type"]<>null){
		if($_GET["type"]=="req"){
			$field="requests as totalsize";
			$prefix_title="{requests}";
			$hasSize=false;
		}
	}	
	
	
	$sql="SELECT size_cached as totalsize,DATE_FORMAT(zDate,'%d') as tdate FROM tables_day WHERE $filter ORDER BY zDate";
	
	$results=$q->QUERY_SQL($sql);

	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		$ydata[]=round(($ligne["totalsize"]/1024)/1000);
	}
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".cache-perf.$file_prefix.png";
	$gp=new artica_graphs();
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{days}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";

	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);return;}
	echo $tpl->_ENGINE_parse_body("<div ><h3>{cache} (MB) /{days} - $selected_date</h3>
	<center>
	<img src='$targetedfile'>
	</center>
	</div>");
	
}