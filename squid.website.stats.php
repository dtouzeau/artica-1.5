<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_line.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie3d.php');
	

	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["today"])){today();exit;}
	if(isset($_GET["week"])){today();exit;}
	if(isset($_GET["month"])){today();exit;}
	if(isset($_GET["users-table"])){users_table();exit;}
	if(isset($_GET["top-users"])){top_users();exit;}
	if(isset($_GET["top-hits"])){top_hits();exit;}
	
	
	js();
	
	
	
function js(){
	$page=CurrentPageName();
	$domain=$_GET["domain"];
	
	$html="
	
		function WebSiteStatisticsLoad(){
			YahooWin4('750','$page?popup=$domain','$domain');
		}
	
	
	WebSiteStatisticsLoad();";
	
	echo $html;
	
}


function top_hits(){
$sql="SELECT COUNT( ID ) AS tcount, uri FROM dansguardian_events WHERE 
sitename = '{$_GET["top-hits"]}' GROUP BY uri ORDER BY tcount DESC LIMIT 0 , 50";

$html="
<p style='font-size:14px'>{SQUID_TOP_HITS_STAT_EXPLAIN}</p>

<table style='width:100%'>
<tr>
	<th colspan=2>{uri}</th>
	<th colspan=2>{hits}</th>
</tr>
";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$js="s_PopUp('{$ligne["uri"]}',800,800);";
	$html=$html."
	<tr ". CellRollOver($js).">
	<td width=1%><img src='img/icon-link.png'></td>
	<td><strong style='font-size:13px'>{$ligne["uri"]}</td>
	<td width=1%><strong style='font-size:13px'>{$ligne["tcount"]}</td>
	</tr>
	";
		
		
	}

$html=$html."</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$domain=$_GET["popup"];
	$array["today"]='{today}';
	$array["week"]='{this_week}';
	$array["month"]='{this_month}';
	$array["top-users"]='{top_users}';
	$array["categories"]='{categories}';
	$array["top-hits"]='{top_hits}';

	

	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="categories"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"squid.categorize.php?load-js=$domain\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=$domain\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_squid_domain_stats style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_squid_domain_stats').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";	
	
}

function top_users(){
	
	
$sql="SELECT COUNT( ID ) AS tcount, CLIENT FROM dansguardian_events WHERE 
sitename = '{$_GET["top-users"]}' GROUP BY CLIENT ORDER BY tcount DESC LIMIT 0 , 50";

$html="
<p style='font-size:14px'>{SQUID_TOP_USERS_STAT_EXPLAIN}</p>

<table style='width:100%'>
<tr>
	<th colspan=2>{members}</th>
	<th colspan=2>{hits}</th>
</tr>
";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$html=$html."
	<tr ". CellRollOver().">
	<td width=1%><img src='img/base.gif'></td>
	<td><strong style='font-size:13px'>{$ligne["CLIENT"]}</td>
	<td width=1%><strong style='font-size:13px'>{$ligne["tcount"]}</td>
	</tr>
	";
		
		
	}

$html=$html."</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);



	
}


function today(){
	$page=CurrentPageName();
	if(isset($_GET["today"])){
		$domain=$_GET["today"];
		$graph1=courbe_today($_GET["today"]);
		$t="today";
	}
	
	if(isset($_GET["week"])){
		$domain=$_GET["week"];
		$graph1=courbe_week($_GET["week"]);
		$t="week";
	}	
	
	if(isset($_GET["month"])){
		$domain=$_GET["month"];
		$graph1=courbe_month($_GET["month"]);
		$t="month";
	}		
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<center><img src='$graph1'></center>
			<div id='users-table-$t'></div>
		</td>
		<td valign='top'>
			<table style='width:100%'>
			". implode("\n",$GLOBALS["stats-array-$domain"])."
			</table>
		</td>
	</tr>
	</table>
	
	<script>
		LoadAjax('users-table-$t','$page?users-table=$domain&t=$t');
	</script>
	
	
	";
	
	echo $html;
	
}

function users_table(){
	$domain=$_GET["users-table"];
	
	switch ($_GET["t"]) {
		case "today":
		$sql="SELECT SUM(QuerySize) as tsize,CLIENT
		FROM dansguardian_events WHERE sitename='$domain' AND DATE_FORMAT( zdate, '%Y-%m-%d' )=DATE_FORMAT( NOW(), '%Y-%m-%d' ) GROUP BY CLIENT ";
		break;
		
		case "week":
		$sql="SELECT SUM(QuerySize) as tsize,CLIENT 
		FROM dansguardian_events WHERE sitename='$domain' AND WEEK( zdate)=WEEK( NOW()) AND YEAR(zdate)=YEAR(NOW()) GROUP BY CLIENT";
		break;	

		case "month":
		$sql="SELECT SUM(QuerySize) as tsize,CLIENT,MONTH(zdate) AS tmonth,YEAR(zdate) AS tyear
		FROM dansguardian_events WHERE sitename='$domain' AND MONTH(zdate)=MONTH( NOW()) AND YEAR(zdate)=YEAR(NOW()) GROUP BY CLIENT";
		break;			
		
	}
	
	$html="
	<br>
	<center>
	<table style='width:500px;border:1px solid #CCCCCC'>";
	$q=new mysql();
	
	$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$html=$html."
	<tr>
		<td width=1%><img src='img/base.gif'></td>
		<td style='font-size:13px'>{$ligne["CLIENT"]}</td>
		<td style='font-size:13px'>". FormatBytes($ligne["tsize"]/1024)."</td>
	</tr>
	";
	}
	
	
	$html=$html."</table></center>";	
	
	echo $html;

}


function courbe_month($domain){
$tpl=new templates();	

$q=new mysql();		
$sql="SELECT COUNT( ID ) AS tcount, sitename, MONTH(zdate) AS tmonth,YEAR(zdate) as tyear,
DATE_FORMAT( zdate, '%d' ) as tday
FROM dansguardian_events
WHERE sitename = '$domain'
GROUP BY tmonth,tyear,tday
HAVING tmonth = MONTH(NOW( )) AND tyear=YEAR(NOW())
ORDER BY tday";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$GLOBALS["stats-array-$domain"][]="<tr>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["tday"]}/{$ligne["tmonth"]}</td>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["tcount"]} hits</td>
	</tr>
	";
	
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["wday"];
}

	$f_name="month-squid-$domain.png";
	$fileName = "ressources/logs/$f_name";
	if(is_file($fileName)){
		if(file_get_time_min($fileName)<120){return $fileName;}
	}
@unlink($fileName);
$title="$domain ". $tpl->_ENGINE_parse_body('{this_month}');

$width = 500; $height = 200;
if($zoom){
	$width=720;
	$height=400;
}

$graph = new Graph($width,$height);
$graph->SetScale('textlin');
$graph->title->Set($title);
$graph->title->SetColor('white');
$graph->xaxis->title->Set('hours');
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set('(hits number)');
 $graph->yaxis->scale->SetGrace(10);
$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
$graph->SetPlotGradient('black','darkred:0.8', 2);
$graph->SetMargin(55,20,60,20);
 //$graph->img->SetMargin(50,30,30,100);

$graph->xaxis->SetColor('lightgray');
$graph->yaxis->SetColor('lightgray');
$graph->xgrid->Show();

$lineplot=new LinePlot($ydata);
$lineplot->SetWeight(2);
$lineplot->SetColor('orange:0.9');
$lineplot->SetFillColor('white@0.7');
$lineplot->SetFillFromYMin();
$lineplot->SetWeight ( 2 ); 
$lineplot->SetFilled(true);
$lineplot->SetFillFromYMin(true);
$graph->Add($lineplot);

JpGraphError::SetImageFlag(false);

try{
	$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
} catch ( JpGraphException $e ) {
    // .. do necessary cleanup
 
    // Send back error message
   // $e->Stroke();
}
$graph->img->Stream($fileName);
return $fileName;	
}



function courbe_week($domain){
$tpl=new templates();	

$q=new mysql();		
$sql="SELECT COUNT( ID ) AS tcount, sitename, DATE_FORMAT( zdate, '%d' ) AS tday,DATE_FORMAT( zdate, '%W' ) AS wday,WEEK(zdate) as tweek
FROM dansguardian_events
WHERE sitename = '$domain'
GROUP BY tday,tweek
HAVING tweek = WEEK( NOW( ))
ORDER BY tday";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$GLOBALS["stats-array-$domain"][]="<tr>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["wday"]}</td>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["tcount"]} hits</td>
	</tr>
	";
	
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["wday"];
}

	$f_name="week-squid-$domain.png";
	$fileName = "ressources/logs/$f_name";
	if(is_file($fileName)){
		if(file_get_time_min($fileName)<120){return $fileName;}
	}
@unlink($fileName);
$title="$domain ". $tpl->_ENGINE_parse_body('{this_week}');

$width = 500; $height = 200;
if($zoom){
	$width=720;
	$height=400;
}
JpGraphError::SetImageFlag(false);
$graph = new Graph($width,$height);
$graph->SetScale('textlin');
$graph->title->Set($title);
$graph->title->SetColor('white');
$graph->xaxis->title->Set('hours');
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set('(hits number)');
 $graph->yaxis->scale->SetGrace(10);
$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
$graph->SetPlotGradient('black','darkred:0.8', 2);
$graph->SetMargin(55,20,60,20);
 //$graph->img->SetMargin(50,30,30,100);

$graph->xaxis->SetColor('lightgray');
$graph->yaxis->SetColor('lightgray');
$graph->xgrid->Show();

$lineplot=new LinePlot($ydata);
$lineplot->SetWeight(2);
$lineplot->SetColor('orange:0.9');
$lineplot->SetFillColor('white@0.7');
$lineplot->SetFillFromYMin();
$lineplot->SetWeight ( 2 ); 
$lineplot->SetFilled(true);
$lineplot->SetFillFromYMin(true);
$graph->Add($lineplot);

JpGraphError::SetImageFlag(false);
try{$gdImgHandler = $graph->Stroke(_IMG_HANDLER);} catch ( JpGraphException $e ) {}
$graph->img->Stream($fileName);
return $fileName;	
}



function courbe_today($domain){
$tpl=new templates();	

$q=new mysql();		
$sql="SELECT COUNT( ID ) AS tcount, sitename, DATE_FORMAT( zdate, '%H' ) AS thour , DATE_FORMAT( zdate, '%Y-%m-%d' ) AS tday
FROM dansguardian_events
WHERE sitename = '$domain'
GROUP BY thour , tday
HAVING tday = DATE_FORMAT( NOW( ) , '%Y-%m-%d' )
ORDER BY thour";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$GLOBALS["stats-array-$domain"][]="<tr>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["thour"]}:00</td>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["tcount"]} hits</td>
	</tr>
	";
	
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["hour"];
}

	$f_name="day-squid-$domain.png";
	$fileName = "ressources/logs/$f_name";
	if(is_file($fileName)){
		if(file_get_time_min($fileName)<120){return $fileName;}
	}

$title="$domain ". $tpl->_ENGINE_parse_body('{today}');
@unlink($fileName);
$width = 500; $height = 200;
if($zoom){
	$width=720;
	$height=400;
}
JpGraphError::SetImageFlag(false);
$graph = new Graph($width,$height);
$graph->SetScale('textlin');
$graph->title->Set($title);
$graph->title->SetColor('white');
$graph->xaxis->title->Set('hours');
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set('(hits number)');
 $graph->yaxis->scale->SetGrace(10);
$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
$graph->SetPlotGradient('black','darkred:0.8', 2);
$graph->SetMargin(55,20,60,20);
 //$graph->img->SetMargin(50,30,30,100);

$graph->xaxis->SetColor('lightgray');
$graph->yaxis->SetColor('lightgray');
$graph->xgrid->Show();

$lineplot=new LinePlot($ydata);
$lineplot->SetWeight(2);
$lineplot->SetColor('orange:0.9');
$lineplot->SetFillColor('white@0.7');
$lineplot->SetFillFromYMin();
$lineplot->SetWeight ( 2 ); 
$lineplot->SetFilled(true);
$lineplot->SetFillFromYMin(true);
$graph->Add($lineplot);
JpGraphError::SetImageFlag(false);
try{
	$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
} catch ( JpGraphException $e ) {
    // .. do necessary cleanup
 
    // Send back error message
   // $e->Stroke();
}
	
$graph->img->Stream($fileName);
return $fileName;	
}

?>