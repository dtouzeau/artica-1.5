<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.artica.graphs.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}

if(isset($_GET["hour-tab"])){byhour_popup();exit;}
if(isset($_GET["today-tab"])){h24_popup();exit;}
if(isset($_GET["week-tab"])){week_popup();exit;}
if(isset($_GET["hour"])){byhour();exit;}
if(isset($_GET["today"])){today();exit;}
if(isset($_GET["tabs"])){tabs();exit;}
js();
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{memory_use}");
	$html="YahooWin3(750,'$page?tabs=yes','$title');";
	echo $html;
}

function tabs(){
	$tpl=new templates();
	$array["hour"]='{last_hour}';
	$array["today"]='{last_24h}';
	$array["week"]='{last_7_days}';
	$page=CurrentPageName();

	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num-tab=yes&t=$time\"><span>$ligne</span></a></li>\n");
	}
	echo "
	<div id=main_psmemtabs style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_psmemtabs').tabs();
			
			
			});
		</script>";	
}

function byhour_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$memory_average=$tpl->_ENGINE_parse_body("{memory_use} {last_hour} (KB)");
	$html="
	<div style='font-size:16px'>$memory_average</div>
	<div id='byhour-div' style='width:100%;height:500px;overflow:auto'></div>
	
	<script>
		LoadAjax('byhour-div','$page?hour=yes&t=$time');
	</script>
	";
	
	echo $html;
	
}

function week_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$memory_average=$tpl->_ENGINE_parse_body("{memory_use} {last_7_days} (KB)");
	$html="<H2>Under construction</H2>
	<div style='font-size:16px'>$memory_average</div>
	<div id='byweek-div' style='width:100%;height:500px;overflow:auto'></div>
	
	<script>
		//LoadAjax('byweek-div','$page?today=yes&t=$time');
	</script>
	";
	
	echo $html;	
	
}
function h24_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$memory_average=$tpl->_ENGINE_parse_body("{memory_use} {last_24h} (KB)");
	$html="
	<div style='font-size:16px'>$memory_average</div>
	<div id='by24hour-div' style='width:100%;height:500px;overflow:auto'></div>
	
	<script>
		LoadAjax('by24hour-div','$page?today=yes&t=$time');
	</script>
	";
	
	echo $html;	
	
}

function byhour(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$gp=new artica_graphs();
	
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d %H') as tday,DATE_FORMAT(zDate,'%i') as thour,AVG(mem) as tmem
	FROM ps_mem_tot GROUP BY tday,thour HAVING tday=DATE_FORMAT(NOW(),'%Y-%m-%d %H') ORDER BY thour";	
	
	if($_GET["process"]<>null){
		echo $tpl->_ENGINE_parse_body("<span style='font-size:13px'>{$_GET["process"]} (KB)</span>");
		$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d %H') as tday,DATE_FORMAT(zDate,'%i') as thour,AVG(`memory`) as tmem FROM ps_mem 
			GROUP BY tday,thour,`process`
			 HAVING tday=DATE_FORMAT(NOW(),'%Y-%m-%d %H') AND `process`='{$_GET["process"]}'
			ORDER BY thour";	
		
	}
	
	if($GLOBALS["VERBOSE"]){echo "<code style='font-size:14px'>$sql</code><hr>";}
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:14px'>$sql</code>";}
	
	echo "<script>
	function ChangeProcessesName(){
		var process=document.getElementById('process-query-day').value;
		LoadAjax('byhour-div','$page?hour=yes&process='+process);
	}
	</script>
	";	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".ps-mem-byhour{$_GET["process"]}.png";

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$size=$ligne["tmem"];
				if(!is_numeric($size)){continue;}
				if($size<1){continue;}
				$size=$size/1024;
				$h=date("H");
				$arrayMEM[$ligne["tday"].":{$ligne["thour"]}:00"]=FormatBytes($size);
				$size=$size/1000;
				$hour=$ligne["thour"];
				if($GLOBALS["VERBOSE"]){echo "<li>$hour -> $size KB</li>";}
				$gp->xdata[]=$hour;
				$gp->ydata[]=$size;
		}
		
		$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d %H') as tday,`process` FROM ps_mem GROUP BY `process`,tday HAVING tday=DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 MINUTE),'%Y-%m-%d %H')";
		$results=$q->QUERY_SQL($sql,"artica_events");
		$processes[null]="{select}";
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){$processes[$ligne["process"]]=$ligne["process"];}		
		
		echo $tpl->_ENGINE_parse_body("
		<center>
		<table style='width:50%' class=form>
		<tbody>
		<tr>
			<td class=legend>{process}:</td>
			<td>". Field_array_Hash($processes, "process-query-day",$_GET["process"],"ChangeProcessesName()",null,0,"font-size:14px")."</td>
		</tr>
		</tbody>
		</table>
		</center>");
		
		if(is_file($targetedfile)){@unlink($targetedfile);}
			
		$gp->width=650;
		$gp->height=250;
		$gp->filename="$targetedfile";
		$gp->y_title=null;
		$gp->x_title=$tpl->javascript_parse_text("{minutes}");
		$gp->title=null;
		$gp->margin0=true;
		$gp->Fillcolor="blue@0.9";
		$gp->color="146497";
		$gp->line_green();
		if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file! ($c items)",__FUNCTION__,__FILE__,__LINE__);return;}
		writelogs("Checking ps_mem -> $targetedfile",__FUNCTION__,__FILE__,__LINE__);
		echo $tpl->_ENGINE_parse_body("<center>
					<img src='$targetedfile'>
				</center>");		
		
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% nowrap>{date}</th>
		<th>{memory}</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($date, $mem) = each ($arrayMEM) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$html=$html."
		<tr class=$classtr>
			<td width=1% style='font-size:14px;font-weight:bold;color:$color' nowrap>$date</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>$mem</td>
		</tr>
		";		
		
	}
	
	echo $tpl->_ENGINE_parse_body($html."</tbody></table>");
	
}

function today(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$gp=new artica_graphs();
	$globalMEM=true;
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday,DATE_FORMAT(zDate,'%H') as thour,AVG(mem) as tmem
	FROM ps_mem_tot GROUP BY tday,thour HAVING tday=DATE_FORMAT(NOW(),'%Y-%m-%d') ORDER BY thour";	
	
	if($_GET["process"]<>null){
		$globalMEM=false;
		echo $tpl->_ENGINE_parse_body("<span style='font-size:13px'>{$_GET["process"]} (KB)</span>");
		$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday,DATE_FORMAT(zDate,'%H') as thour,AVG(`memory`) as tmem FROM ps_mem 
			GROUP BY tday,thour,`process`
			 HAVING tday=DATE_FORMAT(NOW(),'%Y-%m-%d') AND `process`='{$_GET["process"]}'
			ORDER BY thour";	
		
	}
	
	if($GLOBALS["VERBOSE"]){echo "<code style='font-size:14px'>$sql</code><hr>";}
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code style='font-size:14px'>$sql</code>";}
	
	echo "<script>
	function ChangeProcessesName1(){
		var process=document.getElementById('process-query-today').value;
		LoadAjax('by24hour-div','$page?today=yes&process='+process);
	}
	</script>
	";	
	
	$targetedfile="ressources/logs/".basename(__FILE__).".ps-mem-bytoday{$_GET["process"]}.png";

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$ligne["tmem"]=round($ligne["tmem"]);
				$size=round($ligne["tmem"],0);
				if(!is_numeric($size)){continue;}
				if($size<1){continue;}
				$size=round($size/1024);
				$arrayMEM[$ligne["tday"]." {$ligne["thour"]}:00:00"]="<span style='font-size:12px'>". FormatBytes($ligne["tmem"]/1024)."</span>";
				$hour=$ligne["thour"];
				if($GLOBALS["VERBOSE"]){echo "<li>$hour -> $size KB</li>";}
				$gp->xdata[]=$hour;
				$gp->ydata[]=$size;
		}
		
		$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday,`process` FROM ps_mem GROUP BY `process`,tday HAVING tday=DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 MINUTE),'%Y-%m-%d')";
		$results=$q->QUERY_SQL($sql,"artica_events");
		$processes[null]="{select}";
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){$processes[$ligne["process"]]=$ligne["process"];}		
		
		echo $tpl->_ENGINE_parse_body("
		<center>
		<table style='width:50%' class=form>
		<tbody>
		<tr>
			<td class=legend>{process}:</td>
			<td>". Field_array_Hash($processes, "process-query-today",$_GET["process"],"ChangeProcessesName1()",null,0,"font-size:14px")."</td>
		</tr>
		</tbody>
		</table>
		</center>");
		
		if(is_file($targetedfile)){@unlink($targetedfile);}
			
		$gp->width=650;
		$gp->height=250;
		$gp->filename="$targetedfile";
		$gp->y_title=null;
		$gp->x_title=$tpl->javascript_parse_text("{hours}");
		$gp->title=null;
		$gp->margin0=true;
		$gp->Fillcolor="blue@0.9";
		$gp->color="146497";
		$gp->line_green();
		if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file! ($c items)",__FUNCTION__,__FILE__,__LINE__);return;}
		writelogs("Checking ps_mem -> $targetedfile",__FUNCTION__,__FILE__,__LINE__);
		echo $tpl->_ENGINE_parse_body("<center>
					<img src='$targetedfile'>
				</center>");		
		
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1% nowrap>{date}</th>
		<th>{memory}</th>
	</tr>
</thead>
<tbody class='tbody'>";

	while (list ($date, $mem) = each ($arrayMEM) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$html=$html."
		<tr class=$classtr>
			<td width=1% style='font-size:14px;font-weight:bold;color:$color' nowrap>$date</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>$mem</td>
		</tr>
		";		
		
	}
	
	echo $tpl->_ENGINE_parse_body($html."</tbody></table>");
	
}
