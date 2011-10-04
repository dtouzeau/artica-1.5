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


		writelogs("Generate graphs for index page",__FUNCTION__,__FILE__,__LINE__);
		$page=CurrentPageName();
		$tpl=new templates();
		$GLOBALS["CPU_NUMBER"]=intval($users->CPU_NUMBER);
		$cpunum=$GLOBALS["CPU_NUMBER"]+1;
		writelogs("Checking load avg CPU:$cpunum",__FUNCTION__,__FILE__,__LINE__);
		
		
		$q=new mysql();
		$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%i' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 15 MINUTE ) GROUP BY ttime ORDER BY `ttime` ASC";
		$results=$q->QUERY_SQL($sql,"artica_events");

	
	
	
	if(!$q->ok){
		if(preg_match("#Unknown database#", $q->mysql_error)){
			writelogs("Unknown database detected, -> q->BuildTables();",__FUNCTION__,__FILE__,__LINE__);
			$q->BuildTables();
			return;
		}
		
		
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
	echo "<center style='margin-bottom:10px'>";
	if(!$q->TABLE_EXISTS("loadavg", "artica_events")){$q->BuildTables();}
	
// --------------------------------------------------------------------------------------	
	$mysql_num_rows=mysql_num_rows($results);
	if($mysql_num_rows==0){
		$mysql_num_rows=$q->COUNT_ROWS('loadavg', "artica_events");
		writelogs("mysql return no rows from a table of $allrows rows ",__FUNCTION__,__FILE__,__LINE__);
		if($mysql_num_rows>10){
			$sql="SELECT AVG( `load` ) AS sload, DATE_FORMAT( stime, '%h:%i' ) AS ttime FROM `loadavg` WHERE `stime` > DATE_SUB( NOW( ) , INTERVAL 200 MINUTE ) GROUP BY ttime ORDER BY `ttime` ASC";
			$results=$q->QUERY_SQL($sql,"artica_events");
			$mysql_num_rows=mysql_num_rows($results);
			
		}
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	writelogs("Checking load avg Rows:$mysql_num_rows",__FUNCTION__,__FILE__,__LINE__);
	if($mysql_num_rows>0){
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
			writelogs("Checking load avg -> $targetedfile",__FUNCTION__,__FILE__,__LINE__);
			echo "<div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
			this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed58\" OnClick=\"javascript:Loadjs('admin.index.loadvg.php?all=yes')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;\">
			<h3 style='text-transform: none;margin-bottom:5px'>$computer_load</h3>
			<img src='$targetedfile'>
			</div>";
	}
// --------------------------------------------------------------------------------------
	$gp=new artica_graphs();
	$memory_average=$tpl->_ENGINE_parse_body("{memory_use} {today} (GB)");
	if($GLOBALS["VERBOSE"]){echo "<hr>";}
	$sql="SELECT DATE_FORMAT(zDate,'%Y-%m-%d') as tday,HOUR(zDate) as thour,AVG(mem) as tmem FROM ps_mem_tot GROUP BY tday,thour HAVING tday=DATE_FORMAT(NOW(),'%Y-%m-%d') ORDER BY thour";
	if($GLOBALS["VERBOSE"]){echo "<code>$sql</code><br>";}
	$results=$q->QUERY_SQL($sql,"artica_events");
	$mysql_num_rows=mysql_num_rows($results);
	$xtitle=$tpl->javascript_parse_text("{hours}");
	
	if($mysql_num_rows<2){
		$sql="SELECT DATE_FORMAT(zDate,'%h') as thour2,DATE_FORMAT(zDate,'%i') as thour, AVG(mem) as tmem FROM ps_mem_tot GROUP BY DATE_FORMAT(zDate,'%H-%i')
		HAVING thour2=DATE_FORMAT(NOW(),'%h') ORDER BY DATE_FORMAT(zDate,'%H-%i') ";
		if($GLOBALS["VERBOSE"]){echo "<code>$sql</code><br>";}
		$results=$q->QUERY_SQL($sql,"artica_events");
		$memory_average=$tpl->_ENGINE_parse_body("{memory_use} {this_hour} (GB)");
		$mysql_num_rows=mysql_num_rows($results);
		$xtitle=$tpl->javascript_parse_text("{minutes}");		
	}
	
	$targetedfile="ressources/logs/".basename(__FILE__).".ps-mem.png";
	$xdata=array();
	$ydata[]=array();
	writelogs("mysql return no rows from a table of $mysql_num_rows rows ",__FUNCTION__,__FILE__,__LINE__);
	if($mysql_num_rows>0){
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$size=$ligne["tmem"];
				$size=$size/1024;
				$size=$size/1000;
				$size=$size/1000;
				$size=round($size/1000,0);
				$gp->xdata[]=$ligne["thour"];
				$gp->ydata[]=$size;
				$c++;
				if($GLOBALS["VERBOSE"]){echo "<li>ps_mem $hour -> $size</li>";};
			}
			if($c==0){writelogs("Fatal \"$targetedfile\" no items",__FUNCTION__,__FILE__,__LINE__);return;}
			if(is_file($targetedfile)){@unlink($targetedfile);}
			
			$gp->width=300;
			$gp->height=120;
			$gp->filename="$targetedfile";
			$gp->y_title=null;
			$gp->x_title=$xtitle;
			$gp->title=null;
			$gp->margin0=true;
			$gp->Fillcolor="blue@0.9";
			$gp->color="146497";
			$tpl=new templates();
			
			//$gp->SetFillColor('green'); 
			
			$gp->line_green();
			if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file! ($c items)",__FUNCTION__,__FILE__,__LINE__);return;}
			writelogs("Checking ps_mem -> $targetedfile",__FUNCTION__,__FILE__,__LINE__);
			echo "<center><div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
			this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed58\" OnClick=\"javascript:Loadjs('admin.index.psmem.php?all=yes')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;\">
			<h3 style='text-transform: none;margin-bottom:5px'>$memory_average</h3>
			<img src='$targetedfile'>
			</div></center>";		
		
	}
	
// --------------------------------------------------------------------------------------	
	
	
	
	
	
	
	$sock=new sockets();
	$users=new usersMenus();
	
	
writelogs("Checking milter-greylist",__FUNCTION__,__FILE__,__LINE__);	
// --------------------------------------------------------------------------------------
	if($users->MILTERGREYLIST_INSTALLED){
		$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
		if(!is_numeric($EnablePostfixMultiInstance)){$EnablePostfixMultiInstance=0;}
		if($EnablePostfixMultiInstance==0){
			$APP_MILTERGREYLIST=$tpl->_ENGINE_parse_body("{APP_MILTERGREYLIST}");
			if(is_file("ressources/logs/greylist-count-master.tot")){
			$datas=unserialize(@file_get_contents("ressources/logs/greylist-count-master.tot"));
			if(is_array($datas)){
				@unlink("ressources/logs/web/mgreylist.master1.db.png");
				$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/mgreylist.admin.index.db.png",0);
				$gp->xdata[]=$datas["GREYLISTED"];
				$gp->ydata[]="greylisted";	
				$gp->xdata[]=$datas["WHITELISTED"];
				$gp->ydata[]="whitelisted";				
				$gp->width=300;
				$gp->height=120;
				$gp->PieExplode=5;
				
				$gp->ViewValues=false;
				$gp->x_title=null;
				$gp->pie();	
				
				if(is_file("ressources/logs/web/mgreylist.admin.index.db.png")){	
				echo "<div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
				this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed5898\" OnClick=\"javascript:Loadjs('milter.greylist.index.php?js=yes&in-front-ajax=yes')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;\">
				<h3 style='text-transform: none;margin-bottom:5px'>$APP_MILTERGREYLIST</h3>
				<img src='ressources/logs/web/mgreylist.admin.index.db.png'>
				</div>";
				}
				
				
			}
			}
		}
		
	}
	
	
// --------------------------------------------------------------------------------------	

	if($users->SQUID_INSTALLED){
		$SQUIDEnable=trim($sock->GET_INFO("SQUIDEnable"));
		if(!is_numeric($SQUIDEnable)){$SQUIDEnable=1;}
		if($SQUIDEnable==1){
			writelogs("Checking squid perf",__FUNCTION__,__FILE__,__LINE__);	
			$cachedTXT=$tpl->_ENGINE_parse_body("{cached}");
			$NOTcachedTXT=$tpl->_ENGINE_parse_body("{not_cached}");
			$today=$tpl->_ENGINE_parse_body("{today}");
			$sql="SELECT SUM( size ) as tsize, cached FROM squid_cache_perfs WHERE DATE_FORMAT( zDate, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) GROUP BY cached LIMIT 0 , 30";
			$results=$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				
				if($ligne["cached"]==1){$cached_size=$ligne["tsize"];}
				if($ligne["cached"]==0){$not_cached_size=$ligne["tsize"];}
			}
				writelogs("Cached: $cached_size not cached: $not_cached_size bytes",__FUNCTION__,__FILE__,__LINE__);
			
			if(($cached_size>0) &&  ($not_cached_size>0)){
				
				
				$sum=$cached_size+$not_cached_size;
				$pourcent=round(($cached_size/$sum)*100);
				$title=$tpl->_ENGINE_parse_body("{cache_performance} $pourcent%");
				$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/squid.cache.perf.today.png",0);
				$gp->xdata[]=$cached_size;
				$gp->ydata[]="$cachedTXT ".FormatBytes($cached_size/1024);	
				$gp->xdata[]=$not_cached_size;
				$gp->ydata[]="$NOTcachedTXT ".FormatBytes($not_cached_size/1024);					
				$gp->width=300;
				$gp->height=120;
				$gp->PieExplode=5;
				$gp->PieLegendHide=true;
				$gp->ViewValues=false;
				$gp->x_title=null;
				$gp->pie();	
				
				if(is_file("ressources/logs/web/squid.cache.perf.today.png")){	
					echo "<div onmouseout=\"javascript:this.className='paragraphe';this.style.cursor='default';\" onmouseover=\"javascript:this.className='paragraphe_over';
					this.style.cursor='pointer';\" id=\"6ce2f4832d82c6ebaf5dfbfa1444ed58910\" OnClick=\"javascript:Loadjs('squid.cache.perf.stats.php')\" class=\"paragraphe\" style=\"width: 300px; min-height: 112px; cursor: default;\">
					<h3 style='text-transform: none;margin-bottom:5px'>$title</h3>
					<div style='font-size:11px;margin-top:-8px'>$today: $cachedTXT: ".FormatBytes($cached_size/1024)." - $NOTcachedTXT ".FormatBytes($not_cached_size/1024)."</div>
					<img src='ressources/logs/web/squid.cache.perf.today.png'>
					</div>";
				}else{
					writelogs("ressources/logs/web/squid.cache.perf.today.png no such file",__FUNCTION__,__FILE__,__LINE__);
				}			
			
			}
			
		}
	}
	
// --------------------------------------------------------------------------------------	

	
echo "</center>
<div id='notifs-part'></div>
<script>LoadAjax('notifs-part','admin.left.php?partall=yes');</script>

";
	
	
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
	
	
	</script>";	
	
	
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