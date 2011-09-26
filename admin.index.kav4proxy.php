<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
$GLOBALS["ICON_FAMILY"]="KAV4PROXY";
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.artica.graphs.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}
if(isset($_GET["js"])){js();exit;}
if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["hour"])){hour();exit;}
if(isset($_GET["today"])){today();exit;}
if(isset($_GET["week"])){week();exit;}

if(isset($_GET["buildgraph"])){buildgraph();exit;}

kav4_traffic_per_min();
exit;



function kav4_traffic_per_min(){
	$page=CurrentPageName();
	$q=new mysql();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KAV4PROXY}:: {traffic_per_min} MB");
	$sql="SELECT DATE_FORMAT(zDate,'%h:%i') as tdate, traffic_per_min FROM kav4proxy_av_stats WHERE zDate>=DATE_SUB(NOW(),INTERVAL 2 HOUR) ORDER BY zDate";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)<2){return ;}
	
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$xdata[]=$ligne["tdate"];
		$ydata[]=$ligne["traffic_per_min"];
		$c++;	
}

	$targetedfile="ressources/logs/".basename(__FILE__).".png";
	if(is_file($targetedfile)){@unlink($targetedfile);}
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=300;
	$gp->height=120;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title="Mn";
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$tpl=new templates();
	
	//$gp->SetFillColor('green'); 
	
	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file! ($c items)",__FUNCTION__,__FILE__,__LINE__);return;}
	echo "
	<center><div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
	this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed58\" OnClick=\"javascript:Loadjs('$page?js=yes')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;margin-top:5px\">
	<h3 style='text-transform: none;margin-bottom:5px'>$title</h3>
	<img src='$targetedfile'></div></center>";
	
	$title=$tpl->_ENGINE_parse_body("{APP_KAV4PROXY}:: {kav4_total_requests}");
	$sql="SELECT DATE_FORMAT(zDate,'%h:%i') as tdate, total_requests FROM kav4proxy_av_stats WHERE zDate>=DATE_SUB(NOW(),INTERVAL 2 HOUR) ORDER BY zDate";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)<2){return ;}
	$xdata=array();
	$ydata=array();
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$xdata[]=$ligne["tdate"];
		$ydata[]=$ligne["total_requests"];
		$c++;	
}

	$targetedfile="ressources/logs/".basename(__FILE__).".2.png";
	if(is_file($targetedfile)){@unlink($targetedfile);}
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=300;
	$gp->height=120;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title="Mn";
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$tpl=new templates();
	
	//$gp->SetFillColor('green'); 
	
	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file! ($c items)",__FUNCTION__,__FILE__,__LINE__);return;}
	echo "<center><div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
	this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed58\" OnClick=\"javascript:Loadjs('$page?js=yes')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;margin-top:5px\">
	<h3 style='text-transform: none;margin-bottom:5px'>$title</h3>
	<img src='$targetedfile'></div></center>";	
	
	
	
}


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KAV4PROXY}::{statistics}");
	echo "YahooWin3('750','$page?tabs=yes','$title')";
	
	
}

function tabs(){
	$tpl=new templates();
	$array["hour"]='{last_hour}';
	$array["today"]='{last_24h}';
	$array["week"]='{last_7_days}';
	
	
	
	
	
	$page=CurrentPageName();

	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?buildgraph=yes&period=$num&t=$time\"><span>$ligne</span></a></li>\n");
	}
	
	$menus=GetMenus();
	
	echo "$menus
	<div id=main_statskav4tabs style='width:100%;height:500px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_statskav4tabs').tabs();
			
			
			});
		</script>";	
	
	
}

function buildgraph(){
	$page=CurrentPageName();
	$time=time();
	$html="

	<div id='kav4ProxyGraph-$time'></div>
<script>
	function kav4TypeGraphChoosen(){
		LoadAjax('kav4ProxyGraph-$time','$page?{$_GET["period"]}=yes&type='+document.getElementById('kav4TypeGraph').value+'&stime=$time');
	
	}
	kav4TypeGraphChoosen();
</script>";
	
	echo $html;
	
	
}
	

	
	
function GetMenus(){
$tpl=new templates();
$page=CurrentPageName();	
$f[]="total_requests";
$f[]="infected_requests";
$f[]="protected_requests";
$f[]="error_requests";
$f[]="requests_per_min";
$f[]="processed_traffic";
$f[]="clean_traffic";
$f[]="infected_traffic";
$f[]="traffic_per_min";
$f[]="engine_errors";
$f[]="total_connections";
$f[]="total_processes";
$f[]="idle_processes";

while (list ($num, $val) = each ($f) ){
	$array[$val]="{kav4_{$val}}";
	
}

$html="<center><table style='width:80%' class=form>
<tbody>
<tr>
	<td class=legend>{type}:</td>
	<td>". Field_array_Hash($array, "kav4TypeGraph",$_COOKIE["kav4TypeGraph"],"kav4TypeGraphChoosen()",null,"font-size:14px;padding:3px")."</td>
</tr>
</tbody>
</table>
</center>


";

return $tpl->_ENGINE_parse_body($html);
	
}	
	
function hour(){

$page=CurrentPageName();
$type=$_GET["type"];
$h=date('H');
$sql="SELECT AVG( `$type` ) AS sload, DATE_FORMAT( zDate, '$h:%i:00' ) AS ttime FROM `kav4proxy_av_stats` WHERE `zDate` > DATE_SUB( NOW( ) , INTERVAL 60 MINUTE ) GROUP BY ttime ORDER BY `ttime` ASC";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$count=mysql_num_rows($results);
	
	if(mysql_num_rows($results)==0){return;}	
	
	if(!$q->ok){echo $q->mysql_error;}
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		$xdata[]=$ligne["ttime"];
		$ydata[]=$ligne["sload"];
		$c++;
		
	}
	
	$file=time();
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=350;
	$gp->filename="ressources/logs/kav4-$type-hour.png";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title="Mn";
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$tpl=new templates();
	//$gp->SetFillColor('green'); 
	
	$gp->line_green();
	$tpl=new templates();echo $tpl->_ENGINE_parse_body("<div class=explain>{kav4_{$type}_text}</div>");
	echo "
	
	<center><img src='ressources/logs/kav4-$type-hour.png'></center>
	";	
	
	
}

function today(){


$page=CurrentPageName();

$type=$_GET["type"];
$sql="SELECT AVG( `$type` ) AS sload, DATE_FORMAT( zDate, '%H' ) AS ttime FROM `kav4proxy_av_stats` WHERE `zDate` > DATE_SUB( NOW( ) , INTERVAL 24 HOUR ) GROUP BY ttime ORDER BY `ttime` ASC";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$count=mysql_num_rows($results);
	
	if(mysql_num_rows($results)==0){return;}	
	
	if(!$q->ok){echo $q->mysql_error;}
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		$xdata[]=$ligne["ttime"];
		$ydata[]=$ligne["sload"];
		$c++;
		}
	

	$file=time();
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=350;
	$gp->filename="ressources/logs/kav4-$type-24h.png";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title="H";
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	
	//$gp->SetFillColor('green'); 
	
	$gp->line_green();
	$tpl=new templates();echo $tpl->_ENGINE_parse_body("<div class=explain>{kav4_{$type}_text}</div>");
	echo "<img src='ressources/logs/kav4-$type-24h.png'></div>";	
	
	
	
}
function week(){

$type=$_GET["type"];
$page=CurrentPageName();
$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
$cpunum=$GLOBALS["CPU_NUMBER"]+1;
$sql="SELECT AVG( `$type` ) AS sload, DATE_FORMAT( zDate, '%d' ) AS ttime FROM `kav4proxy_av_stats` WHERE `zDate` > DATE_SUB( NOW( ) , INTERVAL 7 DAY ) GROUP BY ttime ORDER BY `ttime` ASC";

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$count=mysql_num_rows($results);
	
	if(mysql_num_rows($results)==0){return;}	
	
	if(!$q->ok){echo $q->mysql_error;}
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		$xdata[]=$ligne["ttime"];
		$ydata[]=$ligne["sload"];
		$c++;
		}
	$tpl=new templates();
	
	
	
	
	$file=time();
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=350;
	$gp->filename="ressources/logs/kav4-$type-7d.png";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title=null;
	$gp->x_title=$tpl->javascript_parse_text("{days}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	
	//$gp->SetFillColor('green'); 
	
	$gp->line_green();
	$time=time();
	$tpl=new templates();echo $tpl->_ENGINE_parse_body("<div class=explain>{kav4_{$type}_text}</div>");
	echo "<img src='ressources/logs/kav4-$type-7d.png'></div>";	
	
	
	
}



?>