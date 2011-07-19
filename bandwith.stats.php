<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.rtmm.tools.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}	
	if(isset($_GET["popup"])){popup();exit;}
	

	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{today}:{bandwith}");
	
	$htm="YahooWin2(750,'$page?popup=yes','$title')";
	
	echo $htm;
	
	
}

function popup(){
	$tpl=new templates();
	
	
	$sql="SELECT DATE_FORMAT(zDate,'%H:%i') as tdate,AVG(bandwith) as tbandwith FROM bandwith_stats 
	WHERE DATE_FORMAT(zDate,'%Y-%m-%d %H')=DATE_FORMAT(NOW(),'%Y-%m-%d %H') 
	GROUP BY DATE_FORMAT(zDate,'%H:%i')
	ORDER BY ID";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo ("<H2>$q->mysql_error</H2><code>$sql</code>");return;}
	$fileName="ressources/logs/web/bandwith-hour.png";
	$g=new artica_graphs($fileName,10);
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$g->ydata[]=round($ligne["tbandwith"],0);
			$g->xdata[]=$ligne["tdate"];
	}	
	

$g->title=$tpl->_ENGINE_parse_body("{hour}: {bandwith}");
$g->x_title="hours";
$g->y_title="KB/s";
$g->width=700;
$g->line_green();
@chmod($fileName,0777);		
	
	
	
	$sql="SELECT DATE_FORMAT(zDate,'%H') as tdate,AVG(bandwith) as tbandwith FROM bandwith_stats 
	WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') 
	GROUP BY DATE_FORMAT(zDate,'%H')
	ORDER BY ID";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo ("<H2>$q->mysql_error</H2><code>$sql</code>");return;}
	$fileName="ressources/logs/web/bandwith-day.png";
	$g=new artica_graphs($fileName,10);
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$g->ydata[]=round($ligne["tbandwith"],0);
			$g->xdata[]=$ligne["tdate"];
	}	
	

$g->title=$tpl->_ENGINE_parse_body("{today}: {bandwith}");
$g->x_title="hours";
$g->y_title="KB/s";
$g->width=700;
$g->line_green();
@chmod($fileName,0777);	

$sql="SELECT YEARWEEK(zDate) as tweek,AVG(bandwith) as tbandwith,DAYOFMONTH(zDate) as tdate 
FROM bandwith_stats WHERE YEARWEEK(zDate)=YEARWEEK(NOW()) GROUP BY DAYOFMONTH(zDate) ORDER BY DAYOFMONTH(zDate) ";

	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo ("<H2>$q->mysql_error</H2><code>$sql</code>");return;}
	$fileName="ressources/logs/web/bandwith-week.png";
	$g=new artica_graphs($fileName,10);
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$g->ydata[]=round($ligne["tbandwith"],0);
			$g->xdata[]=$ligne["tdate"];
	}	
	

$g->title=$tpl->_ENGINE_parse_body("{this_week}: {bandwith}");
$g->x_title="day";
$g->y_title="KB/s";
$g->width=700;
$g->line_green();
@chmod($fileName,0777);
$time=time();
echo "<center style='margin:5px'><img src='ressources/logs/web/bandwith-hour.png?$time'</center>";	
echo "<center style='margin:5px'><img src='ressources/logs/web/bandwith-day.png?$time'</center>";	
echo "<center style='margin:5px'><img src='ressources/logs/web/bandwith-week.png?$time'</center>";
	
		
}





?>