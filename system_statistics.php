<?php
session_start();
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.html.pages.inc');
	include_once('ressources/charts.php');

$pages=new HtmlPages();
if ($pages->rrdtool_installed==false){header('location:users.index.php');exit;}

if(isset($_GET["SystemStatistics"])){echo SystemStatistics(1);exit;}
if(isset($_GET["MonthCourbeDay"])){MonthCourbeDay();exit();}
if(isset($_GET["BuildByHour"])){BuildByHour();exit;}
if(isset($_GET["report_minutes"])){report_minutes();exit;}
if(isset($_GET["LinesMessagesHour"])){report_hours();exit;}


SystemStatistics();

function SystemStatistics($nopage=0){
	$users=new usersMenus();
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?MonthsBlocketType=yes",600,180,"",true,$users->ChartLicence);		
	
	
	$js="<script>	
		
	function wait(){
		document.getElementById('blank').src='img/blank.gif';
		setTimeout(\"Refreshit()\",10000)
		
	}
	
	function Refreshit(){
		document.getElementById('blank').src='img/wait.gif';
		setTimeout(\"Send()\",2000);
	}
	
	function Send(){
		var XHR = new XHRConnection();
		XHR.appendData('SystemStatistics','yes');
		XHR.setRefreshArea('rrd');									
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		wait();
	}

</script>";
	
	$time=serialize(date('Y-m-d H:I:s'));
	$html="
	
	
	
	<div id='rrd'>
	<center style='border:1px dotted #CCCCCC;margin:4px;padding:4px'>
		<H3>{detected_this_month}</H3>
		$graph1
		</center>
		<div id='graph2'></div>
		<div id='graph3'></div>
	</div>
";
//	<script>wait()</script>	
if($nopage==0){
$tpl=new template_users("{more_stats}",$js.$html);
echo $tpl->web_page;			
}else{
	$tpl=new Templates();
	echo $tpl->_ENGINE_parse_body($html);
}
	
}

function MonthCourbeDay(){
	$users=new usersMenus();
	$datas=$_GET["datas"];
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?MonthCourbeDay=$datas",600,180,"",true,$users->ChartLicence);		
	$tpl=new templates();
	$html="<center style='border:1px dotted #CCCCCC;margin:4px;padding:4px'>
<H3>{trend_month}: $datas</H3>
		$graph1
		</center>";

	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function BuildByHour(){
	$filter=$_GET["BuildByHour"];
	$day=$_GET["datas"];
	
	$users=new usersMenus();
	$datas=$_GET["datas"];
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?MonthCourbeHour=$filter&date=$day",600,180,"",true,$users->ChartLicence);		
	$tpl=new templates();
	$html="<center style='border:1px dotted #CCCCCC;margin:4px;padding:4px'>
<H3>&laquo;&nbsp;$filter&nbsp;&raquo; {trend_day}: " . date('F') . " $datas</H3>
		$graph1
		</center>";

	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function report_hours(){
	$users=new usersMenus();
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?DayLine=" .date('Y-m-d'),600,180,"",true,$users->ChartLicence);	
	$html="
	<center style='border:1px dotted #CCCCCC;margin:4px;padding:4px'>
		<H3>{messages_by_hour}</H3>
		$graph1
		</center>
		<div id='graph2'></div>
		<div id='graph3'></div>
	</div>
	<script>LoadAjax('graph2','system_statistics.php?report_minutes={$_GET["data"]}&day=". date('Y-m-d') . "')</script>	
";
//	<script>wait()</script>	

	$tpl=new template_users("{today} ".date('l d F'),$js.$html);
	echo $tpl->web_page;			
}

function report_minutes(){
	$users=new usersMenus();
	if(isset($_GET["datas"])){
		$hour=$_GET["datas"];
		$day=$_GET["report_minutes"];
	}else{
		$hour=$_GET["report_minutes"];
		$day=date('Y-m-d');
	}
	
	
	$graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?HourLine=$day&hour=$hour",600,180,"",true,$users->ChartLicence);	
$html="	<center style='border:1px dotted #CCCCCC;margin:4px;padding:4px'>
		<H3>{messages_by_minutes} $hour ({hour})</H3>
		$graph1
		</center>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);


}
	
	
	



?>