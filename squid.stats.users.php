<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	
	if(isset($_GET["popup"])){tabs();exit;}
	if(isset($_GET["week-categories"])){week_categories();exit;}
	if(isset($_GET["week-categories-graphs"])){week_categories_graph();exit;}
	if(isset($_GET["month-categories"])){month_categories();exit;}
	if(isset($_GET["month-categories-graphs"])){week_categories_graph(1);exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	echo "$('#content').load('$page?popup=yes');";
}


function tabs(){
	
	$page=CurrentPageName();
	
	$tpl=new templates();
	$array["week-categories"]='{week}';
	$array["month-categories"]='{month}';
	
	
	

while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_stats_userdo style='width:100%;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_stats_userdo').tabs({
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


function week_categories(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{what_users_do}? {week}</h1>
	<div id='visited-categories'></div>
	
	
	
	<script>
		LoadAjax('visited-categories','$page?week-categories-graphs=yes');
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function month_categories(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{what_users_do}? {month}</h1>
	<div id='visited-categories-month'></div>
	
	
	
	<script>
		LoadAjax('visited-categories-month','$page?month-categories-graphs=yes');
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}



function week_categories_graph($xtime=0){
	$tpl=new templates();
	$cache_file="/usr/share/artica-postfix/ressources/logs/web/".basename(__FILE__).".".md5(serialize($_GET)).".".__FUNCTION__.'.cache';
	$time=file_time_min_Web($cache_file);
	//if($time<360){echo @file_get_contents($cache_file);return;}
	if($xtime==0){
		$d=date("W");
		$sqlq="WEEK(days)=WEEK(NOW())";
	}
	
	if($xtime==1){
		$d=date("Y-m");
		$sqlq="MONTH(days)=MONTH(NOW())";
	}	
	
	$page=CurrentPageName();
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/squid.$d.categories.png",0);
	//$gp->ImageMap="javascript:Loadjs('squid.stats.category.php?category-js=yes&time=week&category=";
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{hits}</th>
	<th>{websites}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	$sql="SELECT SUM(website_hits) as tsum,category FROM squid_events_sites_day WHERE 
	$sqlq AND YEAR(days)=YEAR(NOW()) GROUP BY category ORDER BY tsum DESC LIMIT 0,10";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["category"])==null){$ligne["category"]="unknown";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$js="Loadjs('squid.stats.category.php?category-js=yes&time=$xtime&category=".urlencode($ligne["category"])."')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["tsum"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			</tr>";
		$gp->xdata[]=$ligne["tsum"];
		$gp->ydata[]=$ligne["category"];		

		
	}		
	
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{categories}";
	$gp->pie();	

	$html=$tpl->_ENGINE_parse_body("<img src='ressources/logs/web/squid.$d.categories.png'  border=0><hr>$html</table>");
	@file_put_contents($cache_file,$html);
	echo $html;
	




}

