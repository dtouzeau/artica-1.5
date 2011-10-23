<?php
	$GLOBALS["title_array"]["size"]="{downloaded_flow}";
	$GLOBALS["title_array"]["req"]="{requests}";	
	
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	

	
	if(!isset($_GET["day"])){$_GET["day"]=date("Y-m-d");}
	if(!isset($_GET["type"])){$_GET["type"]="size";}
	if($_GET["type"]==null){$_GET["type"]="size";}
	if($_GET["day"]==null){$q=new mysql_squid_builder();$_GET["day"]=date("Y-m-d");}		
	
	if(isset($_GET["day-right-infos"])){right();die();}
	if(isset($_GET["days-left-menus"])){left();die();}
	if(isset($_GET["today-zoom"])){today_zoom_js();exit;}
	if(isset($_GET["today-zoom-popup"])){today_zoom_popup();exit;}
	if(isset($_GET["statistics-days-left-status"])){left_status();exit;}
	
page();

function today_zoom_js(){
	$page=CurrentPageName();
	$tpl=new templates();			
	$html="YahooWin2(710,'$page?today-zoom-popup=yes&type={$_GET["type"]}&familysite={$_GET["familysite"]}&day={$_GET["day"]}','{$_GET["familysite"]}')";
	echo $html;
}

function today_zoom_popup(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$q=new mysql_squid_builder();	
	

	$type=$_GET["type"];
	$field_query="size";
	$field_query2="SUM(size)";	
	$table_field="{size}";
	$hour_table=date('Ymd',strtotime($_GET["day"]))."_hour";
	
	$title="<div style='font-size:16px;width:100%;font-weight:bold'>{$_GET["familysite"]}:&nbsp;". strtolower(date('{l} d {F} Y',strtotime($_GET["day"])))." ({$GLOBALS["title_array"][$type]})</div>";
	if(!$q->TABLE_EXISTS($hour_table)){echo $tpl->_ENGINE_parse_body("
	$title
	<center style='margin:50px'>
		<H2>{$_GET["day"]} table:$hour_table</H2>
		<H2>{error_no_datas}$hour_table</H2>
	</center>");
	return;}
	
	
	if($type=="req"){
		$field_query="hits";
		$field_query2="COUNT(zMD5)";
		$table_field="{hits}";	
	}
	
	$sql="SELECT $field_query2 as totalsize,hour as tdate,familysite FROM $hour_table GROUP BY hour,familysite HAVING familysite='{$_GET["familysite"]}' ORDER BY hour";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
	if(mysql_num_rows($results)==0){echo $tpl->_ENGINE_parse_body("$title<center style='margin:50px'><H2>{error_no_datas}</H2>$sql</center>");return;}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		if($_GET["type"]=="size"){$ydata[]=round(($ligne["totalsize"]/1024)/1000);}else{$ydata[]=$ligne["totalsize"];}
	}	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".day.$hour_table.{$_GET["familysite"]}.$type.png";
	$gp=new artica_graphs();
	$gp->width=550;
	$gp->height=220;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{hours}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	
	$image="<center style='margin-top:10px'><img src='$targetedfile'></center>";
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$image=null;}
	
	$table="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:350px'>
<thead class='thead'>
	<tr>
	<th width=1%>{member}</th>
	<th>{ipaddr}</th>
	<th>{website}</th>
	<th>$table_field</th>
	</tr>
</thead>
<tbody>";	
	
	$sql="SELECT $field_query2 as thits, uid,client,sitename,category,familysite FROM $hour_table GROUP BY uid,client,sitename,category,familysite HAVING familysite='{$_GET["familysite"]}' ORDER BY thits DESC LIMIT 0,100";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
	if(mysql_num_rows($results)==0){echo $tpl->_ENGINE_parse_body("$title<center style='margin:50px'><H2>{error_no_datas}</H2></center>");return;}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($type<>"req"){$ligne["thits"]=FormatBytes($ligne["thits"]/1024);}
		
		$categorize="Loadjs('squid.categorize.php?www={$ligne["sitename"]}')";
		if(trim($ligne["category"])==null){$ligne["category"]="<span style='color:#D70707'>{categorize_this_website}</span>";}
		
		$table=$table.
				"
				<tr class=$classtr>
					<td width=1%  style='font-size:14px' nowrap><strong>{$ligne["uid"]}</strong></td>
					<td width=1%  style='font-size:14px' nowrap><strong>{$ligne["client"]}</strong></td>
					<td width=1%  style='font-size:12px' nowrap><a href=\"javascript:blur()\" OnClick=\"javascript:$categorize\" style='font-size:14px;text-decoration:underline'>{$ligne["sitename"]}</a><div style='font-size:10px'>{$ligne["category"]}</div></strong></td>
					<td width=1%  style='font-size:14px' nowrap><strong>{$ligne["thits"]}</strong></td>
				</tr>
				";		
				
			}	
		$table=$table."</tbody></table>";
		
		
		
			

	$html="$title$image<p>&nbsp;</p><div style='height:250px;overflow:auto'>$table</div>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
		
	
	//SELECT SUM(size) as totalsize,count(zMD5) as hits ,familysite,client,uid FROM 20100503_hour GROUP BY familysite,client,uid ORDER BY totalsize,hits DESC 
	
	
}



function left(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();	
	
	
	
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tdate FROM tables_day ORDER BY zDate LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	$mindate=$ligne["tdate"];

	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tdate FROM tables_day ORDER BY zDate DESC LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
	$maxdate=date('Y-m-d');
	
	$html="
		<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend nowrap>{from_date}:</td>
		<td>". field_date('sdate',$_GET["day"],"font-size:16px;padding:3px;width:95px","mindate:$mindate;maxdate:$maxdate")."</td>	
		<td>". button("{go}","SquidFlowDaySizeQuery()")."</td>
	
	<tr>
		<td class=legend nowrap>{select}</td>
		<td valign='top' style='font-size:14px' nowrap><a href=\"javascript:blur();\" OnClick=\"javascript:SquidFlowDaySizeQuery('size')\"$stylehref>{downloaded_flow}</a></td>
	</tr>
	<tr>
		<td class=legend nowrap>{select}</td>
		<td valign='top' style='font-size:14px' nowrap><a href=\"javascript:blur();\" OnClick=\"javascript:SquidFlowDaySizeQuery('req')\"$stylehref>{requests}</a></td>
	</tr>		
	</tbody>
	</table>
	
	<div id='statistics-days-left-status'></div>
	
<script>
		function SquidFlowDaySizeQuery(type){
			if(!type){
				if(document.getElementById('squid-stats-day-hide-type')){type=document.getElementById('squid-stats-day-hide-type').value;}
			}
			if(!type){type='size';}
			
			var sdate=document.getElementById('sdate').value;
			LoadAjax('days-right-infos','$page?day-right-infos=yes&day='+sdate+'&type='+type);
		}
</script>
";
	echo $tpl->_ENGINE_parse_body($html);	
}


function page(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' width=1%><div id='days-left-menus'></div></td>
		<td valign='top' width=99%><div id='days-right-infos'></div></td>
	</tr>
	</tbody>
	</table>
	
	<script>
		LoadAjax('days-left-menus','$page?days-left-menus=yes');
		LoadAjax('days-right-infos','$page?day-right-infos=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function right(){
	$q=new mysql_squid_builder();
	$page=CurrentPageName();
	$tpl=new templates();	
	if(!isset($_GET["day"])){$_GET["day"]=$q->HIER();}
	if(!isset($_GET["type"])){$_GET["type"]="size";}
	if($_GET["type"]==null){$_GET["type"]="size";}
	$type=$_GET["type"];
	$field_query="size";
	

	
	$hour_table=date('Ymd',strtotime($_GET["day"]))."_hour";
	$title="<div style='font-size:16px;width:100%;font-weight:bold'>{statistics}:&nbsp;". strtolower(date('{l} d {F} Y',strtotime($_GET["day"])))." ({$GLOBALS["title_array"][$type]})</div>";
	if(!$q->TABLE_EXISTS($hour_table)){echo $tpl->_ENGINE_parse_body("<input type='hidden' id='squid-stats-day-hide-type' value='{$_GET["type"]}'>$title<center style='margin:50px'><H2>{error_no_datas}</H2></center>");return;}
	
	
	if($type=="req"){
		$field_query="hits";
	}
	
	$sql="SELECT SUM($field_query) as totalsize,hour FROM $hour_table GROUP BY hour ORDER BY hour";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
	if(mysql_num_rows($results)==0){echo $tpl->_ENGINE_parse_body("$title<center style='margin:50px'><H2>{error_no_datas}</H2></center>");return;}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		if($_GET["type"]=="size"){$ydata[]=round(($ligne["totalsize"]/1024)/1000);}else{$ydata[]=$ligne["totalsize"];}
	}	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".day.$hour_table.$type.png";
	$gp=new artica_graphs();
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{hours}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";

	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);return;}	
	
	
	$sql="SELECT SUM($field_query) as totalsize,familysite FROM $hour_table GROUP BY familysite ORDER BY totalsize DESC LIMIT 0,10";
	$results=$q->QUERY_SQL($sql);
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}	
	if(mysql_num_rows($results)==0){echo $tpl->_ENGINE_parse_body("$title<center style='margin:50px'><H2>{error_no_datas}</H2></center>");return;}	
	
	$table="
	<input type='hidden' id='squid-stats-day-hide-type' value='{$_GET["type"]}'>
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:350px'>
<thead class='thead'>
	<tr>
	<th width=1%>{size}</th>
	<th>{website}</th>
	</tr>
</thead>
<tbody>";
	$xdata=array();
	$ydata=array();
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["familysite"])==null){continue;}
		$ydata[]=$ligne["familysite"];
		if($_GET["type"]=="size"){$size=round(($ligne["totalsize"]/1024));
		$size_text=FormatBytes(($ligne["totalsize"]/1024));
		}else{$size=$ligne["totalsize"];$size_text=$size;}
		if($_GET["type"]=="size"){$xdata[]=round(($ligne["totalsize"]/1024));}else{$xdata[]=$ligne["totalsize"];}
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
$table=$table.
		"
		<tr class=$classtr>
			
			<td width=1%  style='font-size:14px' nowrap><strong>$size_text</strong></td>
			<td  style='font-size:14px' nowrap width=99%><strong><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?today-zoom=yes&type={$_GET["type"]}&familysite={$ligne["familysite"]}&day={$_GET["day"]}')\" style='font-size:14px;font-weight:bold;text-decoration:underline'>{$ligne["familysite"]}</a></strong></td>
		</tr>
		";		
		
	}	
$table=$table."</tbody></table>";
	$targetedfile2="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".day.top.10.websites.$hour_table.$type.png";
	$gp=new artica_graphs($targetedfile2);	
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;	
	$gp->width=550;
	$gp->height=550;
	$gp->ViewValues=true;
	$gp->x_title=$tpl->_ENGINE_parse_body("{top_websites}");
	$gp->pie();		
	
	$html="
	<input type='hidden' id='squid-stats-day-hide-type' value='$type'>
	$title
	<center style='margin:10px'><img src='$targetedfile'></center>
	<center style='margin:10px'><img src='$targetedfile2'></center>
	$table
	<script>
		LoadAjax('statistics-days-left-status','$page?statistics-days-left-status=yes&day={$_GET["day"]}');
	</script>
	
";
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}



function squid_status_stats(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$websitesnums=$q->COUNT_ROWS("dansguardian_sitesinfos","artica_backup");
	$websitesnums=numberFormat($websitesnums,0,""," ");	
	


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
		<td valign='top' style='font-size:14px'><b>$totalsize</b> {downloaded_flow}</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:14px'><b>$pref%</b> {cache_performance}</td>
	</tr>	
	</tbody>
	</table>";

echo $tpl->_ENGINE_parse_body($html);
	
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

function left_status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$day=$_GET["day"];
	$time=strtotime("$day 00:00:00");
	$table=date("Ymd",$time)."_hour";
	
	if($q->TABLE_EXISTS($table)){
		$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(sitename) as tcount FROM `$table` WHERE LENGTH(category)=0"));
		$notcategorized=$ligne["tcount"];
		if(!$q->ok){$err1=icon_mysql_error($q->mysql_error);}
		
		
		$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(sitename) as tcount FROM `$table` WHERE LENGTH(category)>2"));
		$categorized=$ligne["tcount"];	
		if(!$q->ok){$err2=icon_mysql_error($q->mysql_error);}
		
		$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(sitename) as tcount FROM `$table`"));
		$visited=$ligne["tcount"];	
		if(!$q->ok){$err3=icon_mysql_error($q->mysql_error);}		
		$notcategorized=texthref($notcategorized, "Loadjs('squid.visited.php?day=$day&onlyNot=yes')");
		$html="
		<table style='width:100%' class=form>
		<tbody>
		<tr>
			<td class=legend>{not_categorized}:</td>
			<td style='font-size:14px;font-weight:bold'>$notcategorized</td>
			<td width=1%>$err1</td>
		</tr>
		<tr>
			<td class=legend>{categorized}:</td>
			<td style='font-size:14px;font-weight:bold'>$categorized</td>
			<td width=1%>$err2</td>
		</tr>	
		<tr>
			<td class=legend>{visited}:</td>
			<td style='font-size:14px;font-weight:bold'>$visited</td>
			<td width=1%>$err2</td>
		</tr>			
		</tbody>
		</table>
		";
		
	}
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

