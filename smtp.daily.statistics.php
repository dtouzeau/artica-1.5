<?php
include_once (dirname(__FILE__)."/ressources/class.templates.inc");
include_once (dirname(__FILE__) .'/ressources/class.mysql.inc');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_line.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie3d.php');


JpGraphError::SetImageFlag(false);
JpGraphError::SetLogFile('ressources/logs/web/jpgraph.log');
$_GET["BASEPATH"]=dirname(__FILE__).'/ressources/logs/jpgraph/smtp';
$_GET["IMGPATH"]="ressources/logs/jpgraph/smtp";
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
	
	$title=$tpl->_ENGINE_parse_body('{monthly_statistics}');
	$prefix=str_replace('.','_',$page);
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;
	
	
	
	function {$prefix}LoadMainPage(){
		YahooWin('650','$page?popup=yes','$title');
		
		}
		
	function SelectSMTPJpGraphDay(){
			var daz=document.getElementById('MONTH').value;
			YahooWin('650','$page?popup=yes&MONTH='+daz,'$title '+ daz);
		}
		
	function ZoomCourbeParMois(){
		var daz=document.getElementById('MONTH').value;
		 YahooWin2('750','$page?Zoom=CourbeParMois&MONTH='+daz,'$title '+ daz);
	}

	function ZoomCamParMois(){
		var daz=document.getElementById('MONTH').value;
		 YahooWin2('800','$page?Zoom=CamParMois&MONTH='+daz,'$title '+ daz);
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
	if(trim($_GET["MONTH"])==null){
		if($_SESSION["ARTICA_SMTP_DAYS_GRAPH_MONTH"]<>null){
			$_GET["MONTH"]=$_SESSION["ARTICA_SMTP_DAYS_GRAPH_MONTH"];
		}else{
			$_GET["MONTH"]=calcul_month();
			$_SESSION["ARTICA_SMTP_DAYS_GRAPH_MONTH"]=$_GET["MONTH"];
		}
	}
			
	
	$field=ArrayDays();
	
	$html="<h1>{monthly_statistics} {$_GET["MONTH"]}</H1>
	<div style='text-align:right;padding-bottom:9px;'>$field</div>
	". RoundedLightWhite("<div style='width:100%;height:450px;overflow:auto'>
	<table style='width:100%'>
		<tr>
			<td align='center'>" . RoundedBlack("<a href='#' OnClick=\"javascript:ZoomCourbeParMois()\"><img src='".courbeparmois()."'></a>")."</td>
		</tr>
		<tr><td>&nbsp;</td></tR>
		<tr>
		<td align='center'>". RoundedLightGrey("<a href='#' OnClick=\"javascript:ZoomCamParMois()\"><img src='".camparmois()."'>")."</a></td>
		</tr>		
		<tr>
			<td valign='top'>
			<hr>
			
			<div style='width:100%;height:300px;overflow;auto'>".tableaudomains()."</div></td>
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

$html="<table style='width:100%'>";
$count=0;
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if(trim($ligne["delivery_domain"]==null)){continue;}
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

function camparmois($zoom=false){
	$month=$_GET["MONTH"];
@mkdir($_GET["BASEPATH"],0755,true);
$f_name="day-global-$month-pie.png";
if($zoom){$f_name="day-global-$month-pie-zoom.png";}
$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<120){
		return "{$_GET["IMGPATH"]}/$f_name";
	}
}

@unlink($fileName);

$q=new mysql();		
$sql="SELECT SUM(emails) as tcount ,bounce_error FROM `smtp_logs_day`  WHERE DATE_FORMAT(`day`,'%Y-%m')='{$_GET["MONTH"]}' GROUP BY bounce_error ORDER BY SUM(emails) DESC ";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	if(strlen($ligne["bounce_error"])>15){$ligne["bounce_error"]=substr($ligne["bounce_error"],0,11)."...";}
	$xdata[] =$ligne["bounce_error"] ." ". $ligne["tcount"];
}

$width = 500; $height = 200;
if($zoom){
	$width=750;
	$height=500;
}


$graph = new PieGraph($width,$height);
$graph->title->Set("emails status");
$p1 = new PiePlot3D($ydata);
$p1->SetLegends($xdata);
$p1->ExplodeSlice(1);


$graph->Add($p1);
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";

	
}


function courbeparmois($zoom=false){
	
$month=$_GET["MONTH"];
@mkdir($_GET["BASEPATH"],0755,true);


$f_name="day-global-$month.png";
if($zoom){$f_name="day-global-$month-zoom.png";}

$fileName = "{$_GET["BASEPATH"]}/$f_name";
if(is_file($fileName)){
	if(file_get_time_min($fileName)<120){
	return "{$_GET["IMGPATH"]}/$f_name";
	}
}


@unlink($fileName);
$q=new mysql();		
$sql="SELECT SUM(emails) as tcount , DATE_FORMAT(DAY,'%d') as tday FROM `smtp_logs_day`  WHERE DATE_FORMAT(DAY,'%Y-%m')='{$_GET["MONTH"]}' GROUP BY tday ORDER BY tday";

$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$ydata[]=$ligne["tcount"];
	$xdata[] =$ligne["DAY"];
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
$graph->title->Set("Received Mails {$_GET["MONTH"]}");
$graph->title->SetColor('white');
$graph->xaxis->title->Set('days');
$graph->xaxis->SetTickLabels($xdata);
$graph->yaxis->title->Set('(emails number)');
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

$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
$graph->img->Stream($fileName);
return "{$_GET["IMGPATH"]}/$f_name";
}

function calcul_month(){

$last_month=date('m')-1;
	$last_month=date('Y-').$last_month;
	
	$month=date('Y-m');
	$mm=date('M Y');
	

	$sql="SELECT count(*) as tcount FROM `smtp_logs_day`  WHERE DATE_FORMAT(DAY,'%Y-%m')='$month' ";
	$q=new mysql();	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$count_actual_month=$ligne["tcount"];
	if($count_actual_month==0){
		$month=$last_month;
	}

return $month;
	
}

?>