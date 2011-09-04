<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.artica.graphs.inc');
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if($_GET["tabs"]){tabs();exit;}
	if(isset($_GET["title-caches-perf"])){title_caches_perf();exit;}
	if($_GET["period"]=="howto"){howto();exit;}
	if($_GET["period"]=="today"){today();exit;}
	if($_GET["period"]=="week"){week();exit;}
	if($_GET["period"]=="month"){month();exit;}
	
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(isset($_GET["inline"])){echo "$('#BodyContent').load('$page?tabs=yes');";return;}
	$title=$tpl->_ENGINE_parse_body("{cache_performance}::{statistics}");	
	$html="YahooWin4('750','$page?tabs=yes','$title')";
	echo $html;
}

function tabs(){
	
	$tpl=new templates();
	$array["howto"]='{cache_performance}';
	$array["today"]='{last_24h}';
	$array["week"]='{last_7_days}';
	$array["month"]='{last_6_months}';
	
	
	
	
	
	$page=CurrentPageName();

	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?buildgraph=yes&period=$num&t=$time\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_squidcachperfs style='width:100%;height:680px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_squidcachperfs').tabs();
			
			
			});
		</script>";	
	
	
}

function howto(){
$squid=new squidbee();
$page=CurrentPageName();
	$tpl=new templates();
 $html="
 <div id='title-caches-perf'></div>
 
 <div class=explain>{squidcacheperf_howto}</div>
 <table style='width:100%'>
 <tr>
 	<td width=1% valign='top'><img src='img/webpage-settings-32.png'></td>
 	<td width=99% valign='top'>
	 	<table style='width:100%'>
	 	<tr>
	 		<td class=legend valign='top' width=1% nowrap>{minimum_object_size} <br>and {maximum_object_size}:</td>
	 		<td>
	 			<strong style='font-size:14px'>{current}:{$squid->global_conf_array["minimum_object_size"]} {and} {$squid->global_conf_array["maximum_object_size"]}
	 			</strong><div class=explain>{minimum_object_size_squid_howto}</div>
	 			<div style='text-align:right;width:100%'>
	 			<a href=\"javascript:blur();\" OnClick=\"Loadjs('squid.caches.php?parameters-js=yes');\" style='text-decoration:underline;font-size:14px;color:#0088CC'>{edit} {minimum_object_size} and {maximum_object_size}</a>
	 			</div>
	 		</td>
	 	</tr>
	 	</table>
	 </td>
 </tr>
 
 <tr>
 	<td width=1% valign='top'><img src='img/caches-rebuild-48.png'></td>
 	<td width=99% valign='top'>
	 	<table style='width:100%'>
	 	<tr>
	 		<td class=legend valign='top' width=1% nowrap>{caches_storage_resources}:</td>
	 		<td>
	 			<strong style='font-size:14px'>
	 			</strong><div class=explain>{caches_storage_resources_howto}</div>
	 			<div style='text-align:right;width:100%'>
	 			<a href=\"javascript:blur();\" OnClick=\"Loadjs('squid.caches.php?caches-js=yes');\" style='text-decoration:underline;font-size:14px;color:#0088CC'>{edit} {caches_storage_resources}</a>
	 			</div>
	 		</td>
	 	</tr>
	 	</table>
	 </td>
 </tr> 

  <tr>
 	<td width=1% valign='top'><img src='img/script-48.png'></td>
 	<td width=99% valign='top'>
	 	<table style='width:100%'>
	 	<tr>
	 		<td class=legend valign='top' width=1% nowrap>{cache_storage_rules}:</td>
	 		<td>
	 			<strong style='font-size:14px'>
	 			</strong><div class=explain>{cache_storage_rules_explain}</div>
	 			<div style='text-align:right;width:100%'>
	 			<a href=\"javascript:blur();\" OnClick=\"Loadjs('squid.cached.sitesinfos.php?js=yes');\" style='text-decoration:underline;font-size:14px;color:#0088CC'>{edit} {cache_storage_rules}</a>
	 			</div>
	 		</td>
	 	</tr>
	 	</table>
	 </td>
 </tr> 
 
 
 </table>
 
 <script>LoadAjax('title-caches-perf','$page?title-caches-perf=yes');</script>
 
 ";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function today(){
	
	
	$tpl=new templates();
	$sql="SELECT DATE_FORMAT( zDate, '%d-%H' ) AS ttime2,DATE_FORMAT( zDate, '%H' ) AS ttime, SUM( size ) as tsize, cached FROM squid_cache_perfs 
	WHERE zDate > DATE_SUB( NOW( ) , INTERVAL 24 HOUR ) GROUP BY ttime,ttime2, cached ORDER BY ttime2";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$count=mysql_num_rows($results);
	
	if(mysql_num_rows($results)==0){return;}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		if($ligne["cached"]==1){
			$ydata2[]=$size;
		}else{
			$ydata[]=$size;
		}
		$time[$ligne["ttime"]]=$ligne["ttime"];
		
	}
	
	while (list ($path, $array) = each ($time) ){
		$xdata[]=$path;
	}
	
	$gp=new artica_graphs();
	$gp->width=650;
	$gp->height=500;
	$gp->filename="ressources/logs/squid-caches-hour.png";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->ydata2=$ydata2;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{hours}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$tpl=new templates();
	$gp->LineLegend=$tpl->javascript_parse_text("{not_cached}");
	$gp->LineLegend2=$tpl->javascript_parse_text("{cached}");
	//$gp->SetFillColor('green'); 
	
	$gp->line_green_double();	
	echo "<center><img src='ressources/logs/squid-caches-hour.png'></center>";
	
}

function week(){
	$tpl=new templates();
	$sql="SELECT DATE_FORMAT( zDate, '%d-%m' ) AS ttime2,DATE_FORMAT( zDate, '%d' ) AS ttime, SUM( size ) as tsize, cached FROM squid_cache_perfs 
	WHERE zDate > DATE_SUB( NOW( ) , INTERVAL 7 DAY ) GROUP BY ttime,ttime2, cached ORDER BY ttime2";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$count=mysql_num_rows($results);
	
	if(mysql_num_rows($results)==0){return;}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		if($ligne["cached"]==1){
			$ydata2[]=$size;
		}else{
			$ydata[]=$size;
		}
		$time[$ligne["ttime"]]=$ligne["ttime"];
		
	}
	
	while (list ($path, $array) = each ($time) ){
		$xdata[]=$path;
	}
	
	$gp=new artica_graphs();
	$gp->width=650;
	$gp->height=500;
	$gp->filename="ressources/logs/squid-caches-days.png";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->ydata2=$ydata2;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{days}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$tpl=new templates();
	$gp->LineLegend=$tpl->javascript_parse_text("{not_cached}");
	$gp->LineLegend2=$tpl->javascript_parse_text("{cached}");
	//$gp->SetFillColor('green'); 
	
	$gp->line_green_double();	
	echo "<center><img src='ressources/logs/squid-caches-days.png'></center>";	
	
}
function month(){
	$tpl=new templates();
	$sql="SELECT WEEK( zDate) AS ttime2, WEEK( zDate) AS ttime, SUM( size ) as tsize, cached FROM squid_cache_perfs 
	WHERE zDate > DATE_SUB( NOW( ) , INTERVAL 6 MONTH ) AND YEAR(zDate)=YEAR(NOW()) GROUP BY ttime,ttime2, cached ORDER BY ttime2";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}		
	$count=mysql_num_rows($results);
	
	if(mysql_num_rows($results)==0){return;}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		if($ligne["cached"]==1){
			$ydata2[]=$size;
		}else{
			$ydata[]=$size;
		}
		$time[$ligne["ttime"]]=$ligne["ttime"];
		
	}
	
	while (list ($path, $array) = each ($time) ){
		$xdata[]=$path;
	}
	
	$gp=new artica_graphs();
	$gp->width=650;
	$gp->height=500;
	$gp->filename="ressources/logs/squid-caches-months.png";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->ydata2=$ydata2;
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{week}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$tpl=new templates();
	$gp->LineLegend=$tpl->javascript_parse_text("{not_cached}");
	$gp->LineLegend2=$tpl->javascript_parse_text("{cached}");
	//$gp->SetFillColor('green'); 
	
	$gp->line_green_double();	
	echo "<center><img src='ressources/logs/squid-caches-months.png'></center>";	
	
}	

function title_caches_perf(){
	$tpl=new templates();
	$sql="SELECT SUM( size ) as tsize, cached FROM squid_cache_perfs GROUP BY cached LIMIT 0 , 3";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
				if($ligne["cached"]==1){$cached_size=$ligne["tsize"];}
				if($ligne["cached"]==0){$not_cached_size=$ligne["tsize"];}
			}
	$sum=$cached_size+$not_cached_size;
	$pourcent=round(($cached_size/$sum)*100);
	$cachedTXT=$tpl->_ENGINE_parse_body("{cached}");
	$NOTcachedTXT=$tpl->_ENGINE_parse_body("{not_cached}");
	$title="$cachedTXT: ".FormatBytes($cached_size/1024)." - $NOTcachedTXT ".FormatBytes($not_cached_size/1024);
	$html="<table style='width:100%'>
	<tr>
		<td width=99%><div style='font-size:16px'>$title</div></td>
		<td width=1%><div style='font-size:16px'>{performance}:</div></td>
		<td width=1%>". pourcentage($pourcent)."</td>
	</tr>
	</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}





