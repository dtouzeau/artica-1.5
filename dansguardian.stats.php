<?php
include_once (dirname(__FILE__)."/ressources/class.templates.inc");
include_once (dirname(__FILE__) .'/ressources/class.mysql.inc');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_line.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie3d.php');

JpGraphError::SetImageFlag(false);
JpGraphError::SetLogFile('ressources/logs/web/jpgraph.log');

$_GET["BASEPATH"]=dirname(__FILE__).'/ressources/logs/jpgraph/dansg';
$_GET["IMGPATH"]="ressources/logs/jpgraph/dansg";

	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSquidAdministrator){
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{ERROR_NO_PRIVS}');
		exit;
	}


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["popup-filters"])){popup_filters();exit;}
if(isset($_GET["days"])){popup_select_days();exit;}
js();


function popup_select_days(){
	
$q=new mysql();		
$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday ,DATE_FORMAT(zDate,'%W %d %M %Y') as tdayS
FROM `dansguardian_events`  GROUP BY tday,tdayS ORDER BY tday DESC";


$html="
<h1>{days}</H1>
<div style='width:100%;height:250px;overflow:auto'>";


$t="
<table style='width:100%'>";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$cel=CellRollOver("DansGuardianStatsLoadMainPageDay('{$ligne["tday"]}');");

	$t=$t."
	<tr $cel>
		<td><code style='font-size:12px'>{$ligne["tday"]}</td>
		<td><code style='font-size:12px'>{$ligne["tdayS"]}</td>
	</tr>
	";
	
	
}

$t=RoundedLightWhite($t."</table>");
$html=$html.$t;
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);

	
}


function js(){
	
$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body('{dansguardian_statistics}');
	$prefix=str_replace('.','_',$page);
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;
	
	
	
	function {$prefix}LoadMainPage(){
		YahooWin('800','$page?popup=yes','$title');
		
		}
		
	function DansGuardianStatsLoadMainPageDay(day){
		YahooWin('800','$page?popup=yes&DAY='+day,'$title');
		
		}		
		
	function dans_stats_switch_tab(query,day){
		if(query=='days'){
			{$prefix}LoadDays(day);
			return;
		}
		if(query=='filters'){
			{$prefix}LoadfiltersDays(day);
			return;
		}		
		
		
	}
	
	function {$prefix}LoadDays(day){
		YahooWin2('400','$page?days=yes&current='+day,'$title');
		
		}	
		
	function {$prefix}LoadfiltersDays(day){
		YahooWin('800','$page?popup-filters=yes&DAY='+day,'$title');
		
		}			
		
	
		{$prefix}LoadMainPage();";
		
echo $html;		
	
}

function tabs(){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
	$array["days"]="$day";
	$array["filters"]="{filter_stats}";
	
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:dans_stats_switch_tab('$num','$day')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div id=tablist>$html</div><br>");		
	
}

function popup(){
	
	$image_top_websites=camWebSitesDay();
	$image_top_clients=camWebUserDay();
	$tabs=tabs();
	$html="<H1>{dansguardian_statistics}</H1>
	<div id='main-dans-page'>
	$tabs
	
	". RoundedLightWhite("<div style='width:100%;height:450px;overflow:auto'>
	<table style='width:100%'>
		<tr>
			<td align='center'>" . RoundedBlack("<img src='".CourbeParHeure()."'></a>")."</td>
		</tr>
		<tr><td>&nbsp;</td></tR>
		<tr>
		<td align='center'>". RoundedLightGrey("<img src='$image_top_websites'></a>")."</td>
		</tr>
		<tr>
		<td align='center'>". RoundedLightGrey("<img src='$image_top_clients'></a>")."</td>
		</tr>
	</table></div>")."</div>";
	$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);			
}
function popup_filters(){
	
	$image_top_filtered=camWebSitesFilteredDay();
	$image_top_clients_filtered=camWebUserFilteredDay();
	$tabs=tabs();
	$html="<H1>{dansguardian_statistics}</H1>
	<div id='main-dans-page'>
	$tabs
	
	". RoundedLightWhite("<div style='width:100%;height:450px;overflow:auto'>
	<table style='width:100%'>
		<tr>
			<td align='center'>" . RoundedBlack("<img src='$image_top_filtered'></a>")."</td>
		</tr>
		<tr><td>&nbsp;</td></tR>
		<tr>
		<td align='center'>". RoundedLightGrey("<img src='$image_top_clients_filtered'></a>")."</td>
		</tr>
		
	</table></div>")."</div>";
	$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);			
}

function CourbeParHeure($zoom=false){
	
$day=$_GET["DAY"];
if($day==null){$day=date('Y-m-d');}

@mkdir($_GET["BASEPATH"],0755,true);


$f_name="day-global-$day.png";
if($zoom){$f_name="day-global-$day-zoom.png";}

$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<20){
	return "{$_GET["IMGPATH"]}/$f_name";
	}
}


@unlink($fileName);
$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount ,DATE_FORMAT(zDate,'%h') as thour 
FROM `dansguardian_events`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY thour ORDER BY thour";



$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["thour"];
}



if(count($ydata)<2){
	$ydata[]=1;
	$xdata[]=date('d');
}
$width = 700; $height = 200;
if($zoom){
	$width=720;
	$height=400;
}
$tpl=new templates();
$hits_number=$tpl->_ENGINE_parse_body('{hits_number}');
$graph = new Graph($width,$height);
$graph->SetScale('textlin');
$graph->title->Set("$hits_number $day");
$graph->title->SetColor('white');
$graph->xaxis->title->Set('hours');
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set("($hits_number)");
$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
$graph->SetPlotGradient('black','darkred:0.8', 2);

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

$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}

function camWebSitesDay($zoom=false){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
@mkdir($_GET["BASEPATH"],0755,true);
$f_name="day-global-$day-pie.png";
if($zoom){$f_name="day-global-$day-pie-zoom.png";}
$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<20){
		return "{$_GET["IMGPATH"]}/$f_name";
	}
}

@unlink($fileName);

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, sitename FROM `dansguardian_events`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY sitename ORDER BY tcount DESC LIMIT 0,10";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["sitename"])>20){$ligne["sitename"]=substr($ligne["sitename"],0,17)."...";}
	$xdata[] =$ligne["sitename"] ." ". $ligne["tcount"];
	
	
	
}

$width = 700; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("TOP {websites}");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}
//-----------------------------------------------------------------------------------------------------------------------------
function camWebSitesFilteredDay($zoom=false){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
@mkdir($_GET["BASEPATH"],0755,true);
$f_name="day-global-".__FUNCTION__."-$day-pie.png";
if($zoom){$f_name="day-global-".__FUNCTION__."-$day-pie-zoom.png";}
$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<20){
		return "{$_GET["IMGPATH"]}/$f_name";
	}
}

@unlink($fileName);

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, TYPE FROM `dansguardian_events`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY TYPE ORDER BY tcount DESC LIMIT 0,10";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["TYPE"])>20){$ligne["TYPE"]=substr($ligne["TYPE"],0,17)."...";}
	$xdata[] =$ligne["TYPE"] ." ". $ligne["tcount"];
	
	
	
}

$width = 700; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("TOP {events} {websites}");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}
//-----------------------------------------------------------------------------------------------------------------------------



function camWebUserDay($zoom=false){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
@mkdir($_GET["BASEPATH"],0755,true);
$f_name="day-global-users-$day-".__FUNCTION__."-pie.png";
if($zoom){$f_name="day-global-users-$day-".__FUNCTION__."-pie-zoom.png";}
$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<20){
		return "{$_GET["IMGPATH"]}/$f_name";
	}
}

@unlink($fileName);

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, CLIENT FROM `dansguardian_events`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY CLIENT ORDER BY tcount DESC LIMIT 0,10";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["CLIENT"])>20){$ligne["CLIENT"]=substr($ligne["uid"],0,17)."...";}
	$xdata[] =$ligne["CLIENT"] ." ". $ligne["tcount"];
	
	
	
}

$width = 700; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("TOP {users}");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}
function camWebUserFilteredDay($zoom=false){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
@mkdir($_GET["BASEPATH"],0755,true);
$f_name="day-global-users-$day-".__FUNCTION__."-pie.png";
if($zoom){$f_name="day-global-users-$day-".__FUNCTION__."-pie-zoom.png";}
$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<20){
		return "{$_GET["IMGPATH"]}/$f_name";
	}
}

@unlink($fileName);

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, CLIENT,TYPE FROM `dansguardian_events`  
WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY CLIENT,TYPE HAVING TYPE!='PASS' ORDER BY tcount DESC LIMIT 0,10";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["CLIENT"])>20){$ligne["CLIENT"]=substr($ligne["uid"],0,17)."...";}
	$xdata[] =$ligne["CLIENT"] ." {$ligne["TYPE"]} " . $ligne["tcount"];
	
	
	
}

$width = 700; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("TOP {filter} {users}");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}
?>