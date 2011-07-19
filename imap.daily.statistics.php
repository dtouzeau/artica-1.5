<?php
include_once (dirname(__FILE__)."/ressources/class.templates.inc");
include_once (dirname(__FILE__) .'/ressources/class.mysql.inc');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_line.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie3d.php');



$_GET["BASEPATH"]=dirname(__FILE__).'/ressources/logs/jpgraph/mbx';
$_GET["IMGPATH"]="ressources/logs/jpgraph/mbx";
	if(posix_getuid()<>0){
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	}


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["Zoom"])){Zoom();exit;}


js();


function js(){
	
$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body('{daily_mailboxes_connections_statistics}');
	$prefix=str_replace('.','_',$page);
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;
	
	
	
	function {$prefix}LoadMainPage(){
		YahooWin('650','$page?popup=yes','$title');
		
		}
		
	
		{$prefix}LoadMainPage();";
		
echo $html;		
	
}

function ArrayDays(){
	
@mkdir($_GET["BASEPATH"],0755,true);
$fileName = "{$_GET["BASEPATH"]}/day-global-dropdown.html";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<120){
		return @file_get_contents("$fileName");
	}
}		
@unlink($fileName);	
	$q=new mysql();	
	$sql="SELECT DATE_FORMAT(`day`,'%Y-%m') as tday FROM `smtp_logs_day` GROUP BY DATE_FORMAT(`day`,'%Y-%m') ORDER BY DATE_FORMAT(`day`,'%Y-%m')";
	$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	
	$arr[$ligne["tday"]]="{select}: {$ligne["tday"]}";
	
}

$html= Field_array_Hash($arr,'MONTH',$_GET["MONTH"],"SelectSMTPJpGraphDay()",null);
@file_put_contents($fileName,$html);
return $html;
}


function Zoom(){
	
	if($_GET["Zoom"]=="CamParMois"){
		echo RoundedLightGrey("<img src='".camparmois(true)."'>");
		exit;
	}
	
	echo RoundedBlack("<img src='".courbeparmois(true)."'>");
	
}


function popup(){
	
	
	$html="<h1>{daily_mailboxes_connections_statistics}</H1>
	
	". RoundedLightWhite("<div style='width:100%;height:450px;overflow:auto'>
	<table style='width:100%'>
		<tr>
			<td align='center'>" . RoundedBlack("<img src='".CourbeParHeure()."'></a>")."</td>
		</tr>
		<tr><td>&nbsp;</td></tR>
		<tr>
		<td align='center'>". RoundedLightGrey("
			<img src='".camTodayCon()."'></a>
			<hr>
			<div style='width:100%;height:220px;overflow:auto'>". tableauTodayUsersCon()."</div>
			
			")."</td>
		</tr>
		<tr><td>&nbsp;</td></tR>
		<td align='center'>". RoundedLightGrey("
			<img src='".camTodayIP()."'></a>
			<hr>
			
			
			")."</td>
		</tr>
		
		
		
		<tr>
			<td valign='top'>
			<hr>
			
			<div style='width:100%;height:300px;overflow;auto'></div></td>
		</tR>
	</table></div>");
	
	

$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);			
}


function tableaudomains(){
	
@mkdir($_GET["BASEPATH"],0755,true);
$fileName = "{$_GET["BASEPATH"]}/day-global-$month.html";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<120){
		return @file_get_contents("$fileName");
	}
}	

@unlink($fileName);
$q=new mysql();		
$sql="SELECT SUM(emails) as tcount ,delivery_domain, DATE_FORMAT(`day`,'%d') as tday FROM `smtp_logs_day`  WHERE DATE_FORMAT(`day`,'%Y-%m')='{$_GET["MONTH"]}' GROUP BY tday,delivery_domain ORDER BY SUM(emails) DESC";	
$results=$q->QUERY_SQL($sql,"artica_events");

$html="<table style='width:98%'>";
$count=0;
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	
	$count=$count+1;
	$html=$html . "
	<tr " . CellRollOver().">
		<td valign='top'><code style='font-size:12px'>{$ligne["delivery_domain"]}</code></td>
		<td valign='top'><code style='font-size:12px'>{$ligne["tcount"]}</code></td>
	</tr>
	
	";
	}
$html="<H3>$count {recipients_domains}</H3>$html</table>";

@file_put_contents($fileName,$html);
	return $html;
	
}


function tableauTodayUsersCon(){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
	$f_name="day-global-$day-pie.html";
	$fileName = "{$_GET["BASEPATH"]}/$f_name";
	if(is_file($fileName)){
		if(file_get_time_min($fileName)<20){
			return file_get_contents("{$_GET["IMGPATH"]}/$f_name");
		}
	}	
	
$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, uid FROM `mbx_con`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY uid ORDER BY tcount DESC LIMIT 0,20";
$results=$q->QUERY_SQL($sql,"artica_events");
$html="<table style='width:90%' class=table_form>";	
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$html=$html.
	"<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code style='font-size:12px'>{$ligne["uid"]}</td>
		<td><code style='font-size:12px'>{$ligne["tcount"]} {connections}</td>
	</tr>
	";
	
}
$html=$html . "</table>";
@file_put_contents($fileName,$html);
return $html;
	
}

function camTodayCon($zoom=false){
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
$sql="SELECT COUNT(ID) as tcount, uid FROM `mbx_con`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY uid ORDER BY tcount DESC LIMIT 0,10";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["uid"])>20){$ligne["uid"]=substr($ligne["uid"],0,17)."...";}
	$xdata[] =$ligne["uid"] ." ". $ligne["tcount"];
	
	
	
}

$width = 550; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("Top users ");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}

//------------------------------------

function camTodayIP($zoom=false){
	$day=$_GET["DAY"];
	if($day==null){$day=date('Y-m-d');}
@mkdir($_GET["BASEPATH"],0755,true);
$f_name="day-global-$day-".__FUNCTION__.".png";
if($zoom){$f_name="day-global-$day-".__FUNCTION__."-zoom.png";}
$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<20){
		return "{$_GET["IMGPATH"]}/$f_name";
	}
}

@unlink($fileName);

$q=new mysql();		
$sql="SELECT COUNT(ID) as tcount, client_ip FROM `mbx_con`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY client_ip ORDER BY tcount DESC LIMIT 0,10";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["client_ip"])>20){$ligne["uid"]=substr($ligne["uid"],0,17)."...";}
	$xdata[] =$ligne["client_ip"] ." ". $ligne["tcount"];
	
	
	
}

$width = 550; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("Top Public TCP/IP ");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
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
FROM `mbx_con`  WHERE DATE_FORMAT(zDate,'%Y-%m-%d')='$day' GROUP BY thour ORDER BY thour";



$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["thour"];
}



if(count($ydata)<2){
	$ydata[]=1;
	$xdata[]=date('d');
}
$width = 500; $height = 200;
if($zoom){
	$width=720;
	$height=400;
}

$graph = new Graph($width,$height);
$graph->SetScale('textlin');
$graph->title->Set("Connexions numbers $day");
$graph->title->SetColor('white');
$graph->xaxis->title->Set('hours');
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set('(connexions)');
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


?>