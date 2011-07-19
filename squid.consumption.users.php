<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	
	if(isset($_GET["popup"])){tabs();exit;}
	if(isset($_GET["week-consumption"])){week_consumption();exit;}
	if(isset($_GET["week-consumption-graphs"])){week_consumption_graph();exit;}
	if(isset($_GET["month-consumption"])){month_consumption();exit;}
	if(isset($_GET["month-consumption-graphs"])){week_consumption_graph(1);exit;}
	if(isset($_GET["day-consumption"])){day_consumption();exit;}
	if(isset($_GET["day-consumption-graphs"])){week_consumption_graph(-1);exit;}
	
	
	
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	echo "$('#content').load('$page?popup=yes');";
}


function tabs(){
	
	$page=CurrentPageName();
	
	$tpl=new templates();
	
	$array["week-consumption"]='{week}';
	$array["month-consumption"]='{month}';
	
	
	

while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_stats_consumption style='width:100%;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_stats_consumption').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>");		
}


function week_consumption(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{consumption}: {week}</h1>
	<div id='visited-consumption'></div>
	
	
	
	<script>
		LoadAjax('visited-consumption','$page?week-consumption-graphs=yes');
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function month_consumption(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{consumption}: {month}</h1>
	<div id='visited-consumption-month'></div>
	
	
	
	<script>
		LoadAjax('visited-consumption-month','$page?month-consumption-graphs=yes');
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}


function day_consumption(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{consumption}: {today}</h1>
	<div id='visited-consumption-day'></div>
	
	
	
	<script>
		LoadAjax('visited-consumption-day','$page?day-consumption-graphs=yes');
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function week_consumption_graph($xtime=0){
	
	$cache_file="/usr/share/artica-postfix/ressources/logs/web/".basename(__FILE__).".".md5(serialize($_GET)).".".__FUNCTION__.'.cache';
	$time=file_time_min_Web($cache_file);
	//if($time<360){echo @file_get_contents($cache_file);return;}
	
	if($xtime==-1){
		$d=date("Y-m-d");
		$sqlq="DATE_FORMAT(days,'%Y-%m-%d')='$d'";
	}
	
	if($xtime==0){
		$d=date("W");
		$sqlq="WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";
	}
	
	if($xtime==1){
		$d=date("Y-m");
		$sqlq="MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";
	}	
	
	$page=CurrentPageName();
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/squid.$d.consumption.png",0);
	$tpl=new templates();
	
	
	//$gp->ImageMap="javascript:Loadjs('squid.stats.category.php?category-js=yes&time=week&category=";
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{size}</th>
	<th>{members}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	$sql="SELECT SUM(size) as tsum,CLIENT FROM squid_events_clients_day WHERE 
	$sqlq  GROUP BY CLIENT ORDER BY tsum DESC LIMIT 0,10";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2><hr>$sql</hr>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["CLIENT"])==null){$ligne["CLIENT"]="unknown";}
		$ligne["tsum"]=FormatBytes($ligne["tsum"]/1024);
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$js="Loadjs('squid.stats.client.php?client-js=yes&time=$xtime&client=".urlencode($ligne["CLIENT"])."')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["tsum"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["CLIENT"]}</a></td>
			</tr>";
		$gp->xdata[]=$ligne["tsum"];
		$gp->ydata[]=$ligne["CLIENT"];		

		
	}		
	
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{categories}";
	$gp->pie();	

	$html=$tpl->_ENGINE_parse_body("<img src='ressources/logs/web/squid.$d.consumption.png'  border=0><hr>$html</table>");
	@file_put_contents($cache_file,$html);
	echo $html;
}

