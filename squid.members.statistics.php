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
	
	if(isset($_GET["squid-members-graphs"])){members_first_graph();exit;}
	if(isset($_GET["month-list-members"])){month_list_members();exit;}
	
	
	
tabs();


function tabs(){
	
	$page=CurrentPageName();
	
	$tpl=new templates();
	$array["status"]='{status}';
	$array["day-consumption"]='{days}';

	
	
	

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
		<td valign='top' width=99%><div id='squid-members-graphs' style='text-align:center'></div></td>
	</tr>
	</tbody>
	</table>
	<script>
		LoadAjax('squid-general-status','$page?squid-general-status=yes');
	
	</script>
	";
	
	echo $html;
	
	
	
	
}

function month_list_members(){
	$q=new mysql_squid_builder();
	$array=$q->LIST_TABLES_MEMBERS();
	while (list ($num, $ligne) = each ($array) ){
		$ligne=trim($ligne);
		if(!preg_match("#([0-9]+)_members#", $ligne,$re)){continue;}
		$len=strlen($re[1]);
		if($len>6){continue;}
		$year=substr($re[1], 0,4);
		$month=substr($re[1], 4,2);
		if($year<>date('Y')){continue;}
		$tr=$tr."<td style='font-size:14px'>$month</td>";
		
		
	}
	
	$html="
<center>
		<table class=form>
		<tbody>
			<tr>$tr</tr>
		</tbody>
	</table>
</center>";
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
		LoadAjax('squid-members-graphs','$page?squid-members-graphs=yes');
		
	</script>
	</div>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function members_first_graph(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql_squid_builder();
	$gp=new artica_graphs();

	$currentmonth=date('Y-m');
	$table=date("Ym")."_members";
	$sql="SELECT COUNT(zMD5) as tcount,`day` FROM `$table` GROUP BY `day` ORDER BY `day`";
	$results=$q->QUERY_SQL($sql);
	
	$table="
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:10%'>
<thead class='thead'>
	<tr>
	<th width=1%>{days}</th>
	<th>{members}</th>
	</tr>
</thead>
<tbody>";	
	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$table=$table."
		<tr class=$classtr>
			<td width=1%  style='font-size:14px' nowrap align=center><strong>{$ligne["day"]}</strong></td>
			<td  style='font-size:14px' nowrap width=99% align=center><strong>{$ligne["tcount"]}</td>
		</tr>
		";		
		
		$gp->xdata[]=$ligne["day"];
		$gp->ydata[]=$ligne["tcount"];
	}
	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".$file_prefix.$type.png";
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{days}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);$targetedfile="img/kas-graph-no-datas.png";}

	$html="
	<center style='font-size:14px;font-weight:bold'>{months}</center>
	<div id='month-list-members'></div>
	<div style='font-size:16px'>{number_of_users} $currentmonth</div>
	<center>
		<img src='$targetedfile'>
	</center>
	$table</tbody></table>
	</center>

	
	<script>
		LoadAjax('month-list-members','$page?month-list-members=yes');
	</script>
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




