<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.artica.graphs.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}
if(isset($_GET["all"])){js();exit;}
if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["hour"])){hour();exit;}
if(isset($_GET["today"])){today();exit;}
if(isset($_GET["week"])){week();exit;}
if(isset($_POST["LoadAvgClean"])){LoadAvgClean();exit;}

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{computer_load}");
	
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
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&t=$time\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_loadavgtabs style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_loadavgtabs').tabs();
			
			
			});
		</script>";	
	
	
}	
	




		$page=CurrentPageName();
		$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
		$cpunum=$GLOBALS["CPU_NUMBER"]+1;
		$q=new mysql();
		$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%i' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 15 MINUTE ) GROUP BY ttime ORDER BY `ttime` ASC";
		$results=$q->QUERY_SQL($sql,"artica_events");

	
	
	
	if(!$q->ok){
		if(preg_match("#Access denied for user#", $q->mysql_error)){
			$error=urlencode(base64_encode($q->mysql_error));
			echo "
			<script>
				Loadjs('admin.mysql.error.php?error=$error');
			</script>
			";
			return;
		}
		
		echo $q->mysql_error;return;
	
	
	}		
	$count=mysql_num_rows($results);
	
	
	if(mysql_num_rows($results)==0){
		$allrows=$q->COUNT_ROWS('loadavg', "artica_events");
		writelogs("mysql return no rows from a table of $allrows rows ",__FUNCTION__,__FILE__,__LINE__);
		if($allrows>10){
			$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%h:%i' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 200 MINUTE ) GROUP BY ttime ORDER BY `ttime` ASC";
			$results=$q->QUERY_SQL($sql,"artica_events");
			if(mysql_num_rows($results)==0){
				writelogs("mysql return no rows from a table of $allrows rows (200mns)",__FUNCTION__,__FILE__,__LINE__);
				return;
			}
		}
		
		
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		$xdata[]=$ligne["ttime"];
		$ydata[]=$ligne["sload"];
		$c++;
		if($ligne["sload"]>$cpunum){
			if($GLOBALS["VERBOSE"]){echo "<li>!!!! {$ligne["stime"]} -> $c</LI>";};
			if(!isset($red["START"])){$red["START"]=$c;}
		}else{
			if(isset($red["START"])){
				$area[]=array($red["START"],$c);
				unset($red);
			}
		}
		if($GLOBALS["VERBOSE"]){echo "<li>{$ligne["stime"]} -> {$ligne["ttime"]} -> {$ligne["sload"]}</LI>";};
	}
	if($c==0){writelogs("Fatal \"$targetedfile\" no items",__FUNCTION__,__FILE__,__LINE__);return;}
	
	if(isset($red["START"])){$area[]=array($red["START"],$c);}
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
	$computer_load=$tpl->_ENGINE_parse_body("{computer_load}");
	//$gp->SetFillColor('green'); 
	
	$gp->line_green();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file! ($c items)",__FUNCTION__,__FILE__,__LINE__);return;}
	echo "<div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
	this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed58\" OnClick=\"javascript:Loadjs('admin.index.loadvg.php?all=yes')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;\">
	<h3 style='text-transform: none;margin-bottom:5px'>$computer_load</h3>
	<img src='$targetedfile'></div>";

	
function hour(){

$page=CurrentPageName();
$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
$cpunum=$GLOBALS["CPU_NUMBER"]+1;
$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%i' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 60 MINUTE ) GROUP BY ttime ORDER BY `ttime` ASC";

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
		if($ligne["sload"]>$cpunum){
			if($GLOBALS["VERBOSE"]){echo "<li>!!!! {$ligne["stime"]} -> $c</LI>";};
			if(!isset($red["START"])){$red["START"]=$c;}
		}else{
			if(isset($red["START"])){
				$area[]=array($red["START"],$c);
				unset($red);
			}
		}
		
	

		
		
		if($GLOBALS["VERBOSE"]){echo "<li>{$ligne["stime"]} -> {$ligne["ttime"]} -> {$ligne["sload"]}</LI>";};
	}
	if(isset($red["START"])){$area[]=array($red["START"],$c);}

	$file=time();
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=350;
	$gp->filename="ressources/logs/loadavg-hour.png";
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
	
	echo "
	<div id='loadavg-clean'>
	<img src='ressources/logs/loadavg-hour.png'></div></div>
	<div style='text-align:right'><hr>".$tpl->_ENGINE_parse_body(button("{clean_datas}","LoadAvgClean()"))."</div>
	<script>
	
	var x_LoadAvgClean=function(obj){
      var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
      YahooWin3Hide();
      document.getElementById('loadavggraph').innerHTML='';
      }	
	
	function LoadAvgClean(){
		var XHR = new XHRConnection();
		XHR.appendData('LoadAvgClean','yes');
		
		AnimateDiv('loadavg-clean');
		XHR.sendAndLoad('$page', 'POST',x_LoadAvgClean);		
		}	
	
	
	";	
	
	
}

function today(){


$page=CurrentPageName();
$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
$cpunum=$GLOBALS["CPU_NUMBER"]+1;
$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%H' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 24 HOUR ) GROUP BY ttime ORDER BY `ttime` ASC";

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
		if($ligne["sload"]>$cpunum){
			if($GLOBALS["VERBOSE"]){echo "<li>!!!! {$ligne["stime"]} -> $c</LI>";};
			if(!isset($red["START"])){$red["START"]=$c;}
		}else{
			if(isset($red["START"])){
				$area[]=array($red["START"],$c);
				unset($red);
			}
		}
		
	

		
		
		if($GLOBALS["VERBOSE"]){echo "<li>{$ligne["stime"]} -> {$ligne["ttime"]} -> {$ligne["sload"]}</LI>";};
	}
	if(isset($red["START"])){$area[]=array($red["START"],$c);}

	$file=time();
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=350;
	$gp->filename="ressources/logs/loadavg-24h.png";
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
	
	echo "<img src='ressources/logs/loadavg-24h.png'></div>";	
	
	
	
}
function week(){


$page=CurrentPageName();
$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
$cpunum=$GLOBALS["CPU_NUMBER"]+1;
$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%d' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 7 DAY ) GROUP BY ttime ORDER BY `ttime` ASC";

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
		if($ligne["sload"]>$cpunum){
			if($GLOBALS["VERBOSE"]){echo "<li>!!!! {$ligne["stime"]} -> $c</LI>";};
			if(!isset($red["START"])){$red["START"]=$c;}
		}else{
			if(isset($red["START"])){
				$area[]=array($red["START"],$c);
				unset($red);
			}
		}
		
	

		
		
		if($GLOBALS["VERBOSE"]){echo "<li>{$ligne["stime"]} -> {$ligne["ttime"]} -> {$ligne["sload"]}</LI>";};
	}
	$tpl=new templates();
	
	if($c<2){echo "<H2>". $tpl->_ENGINE_parse_body("{error_no_datas}")."</H2>";return;}
	
	if(isset($red["START"])){$area[]=array($red["START"],$c);}
	
	$file=time();
	$gp=new artica_graphs();
	$gp->RedAreas=$area;
	$gp->width=650;
	$gp->height=350;
	$gp->filename="ressources/logs/loadavg-7d.png";
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
	echo "<img src='ressources/logs/loadavg-7d.png'></div>";	
	
	
	
}

function LoadAvgClean(){
	$q=new mysql();
	$q->DELETE_TABLE("loadavg", "artica_events");
	$q->BuildTables();
	
}

?>