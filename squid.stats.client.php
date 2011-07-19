<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	
	if(isset($_GET["popup"])){tabs();exit;}
	
	if(isset($_GET["hits"])){user_hits();exit;}
	if(isset($_GET["hits-webistes-list"])){user_hits_web_list();exit;}
	
	
	if(isset($_GET["size"])){user_size();exit;}
	if(isset($_GET["size-webistes-list"])){user_size_web_list();exit;}
	
	
	
	if(isset($_GET["category"])){user_category();exit;}
	if(isset($_GET["websites"])){user_websites();exit;}
	
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	echo "YahooWin4(650,'$page?popup=yes&time={$_GET["time"]}&client=".urlencode($_GET["client"])."','{$_GET["client"]}')";
}


function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	
	
	$array["hits"]='{hits}';
	$array["size"]='{size}';
	$array["category"]='{category}';
	$array["websites"]='{websites}';
	
	
	

while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes&time={$_GET["time"]}&client=".urlencode($_GET["client"])."\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body( "
	<div id=squid_stats_user style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#squid_stats_user').tabs({
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

function user_hits(){
	$page=CurrentPageName();
	if($_GET["time"]==0){$time="week";}
	if($_GET["time"]==1){$time="month";}
	$tpl=new templates();
	$md=md5("{$_GET["client"]}{$_GET["time"]}");
	$html="<H2 style='color:black'>{$_GET["client"]} - {{$time}} - {hits_by_days}</H2>
	<hr>".user_hits_graph()."<hr>
	
	<div id='$md'> </div>
	
	
	<script>
		LoadAjax('$md','$page?hits-webistes-list=yes&time={$_GET["time"]}&client={$_GET["client"]}');
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function user_size(){
	$page=CurrentPageName();
	if($_GET["time"]==0){$time="week";}
	if($_GET["time"]==1){$time="month";}
	$tpl=new templates();
	$md=md5("size{$_GET["client"]}{$_GET["time"]}");
	$html="<H2 style='color:black'>{$_GET["client"]} - {{$time}} - {size_by_days}</H2>
	<hr>".user_size_graph()."<hr>
	
	<div id='$md'> </div>
	
	
	<script>
		LoadAjax('$md','$page?size-webistes-list=yes&time={$_GET["time"]}&client={$_GET["client"]}');
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}

function user_category(){
	$page=CurrentPageName();
	if($_GET["time"]==0){$time="week";}
	if($_GET["time"]==1){$time="month";}
	$tpl=new templates();
	$html="<H2 style='color:black'>{$_GET["client"]} - {{$time}} - {categories}</H2>
	<hr>".user_category_graph();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function user_websites(){
	$page=CurrentPageName();
	if($_GET["time"]==0){$time="week";}
	if($_GET["time"]==1){$time="month";}
	$tpl=new templates();
	$html="<H2 style='color:black'>{$_GET["client"]} - {{$time}} - {websites}</H2>
	<hr>".user_websites_table();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function user_websites_table(){
	$tpl=new templates();
	$page=CurrentPageName();
	if($_GET["time"]==0){
		$d=date("W");
		$sqlq="WEEK(days)=WEEK(NOW())";
	}
	
	if($_GET["time"]==1){
		$d=date("Y-m");
		$sqlq="MONTH(days)=MONTH(NOW())";
	}	

$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{websites}</th>
			<th>{size}</th>
			<th>{hits}</th>
			<th>{category}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	

	$sql="SELECT COUNT(hits) as thits,SUM(size) as tsize,websites,category FROM squid_events_clients_sites 
	WHERE $sqlq 
	AND YEAR(days)=YEAR(NOW()) 
	AND client='{$_GET["client"]}' 
	GROUP BY websites,category ORDER BY tsize 
	DESC LIMIT 0,100";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2 style='color:black'>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		
		$size=$ligne["tsize"]/1024;
		$size=FormatBytes($size);
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$js="Loadjs('squid.stats.client.php?client-js=yes&time=$xtime&client=".urlencode($ligne["CLIENT"])."')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["websites"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["thits"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			</tr>";
		}		

	return $tpl->_ENGINE_parse_body($html."</table>");
}


function user_category_graph(){
	$tpl=new templates();
	if($_GET["time"]==0){
		$d=date("W");
		$sqlq="WEEK(days)=WEEK(NOW())";
	}
	
	if($_GET["time"]==1){
		$d=date("Y-m");
		$sqlq="MONTH(days)=MONTH(NOW())";
	}	
	
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{size}</th>
			<th>{members}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";
	
	$sql="SELECT COUNT(hits) as thits,category FROM squid_events_clients_sites 
	WHERE $sqlq 
	AND YEAR(days)=YEAR(NOW()) 
	AND client='{$_GET["client"]}' 
	GROUP BY category ORDER BY thits 
	DESC LIMIT 0,10";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2 style='color:black'>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/squid.$d.user.{$_GET["client"]}.category.png",0);
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["category"])==null){$ligne["category"]="unknown";}
		
		
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$js="Loadjs('squid.stats.client.php?client-js=yes&time=$xtime&client=".urlencode($ligne["CLIENT"])."')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["thits"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			</tr>";
		$gp->xdata[]=$ligne["thits"];
		$gp->ydata[]=$ligne["category"];		

		
	}		
	
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{categories}";
	$gp->pie();	

	$html="<img src='ressources/logs/web/squid.$d.user.{$_GET["client"]}.category.png'  border=0>
	<hr>$html</table>";
	
	return $tpl->_ENGINE_parse_body($html);
}	
	


function user_size_graph(){
$xtime=$_GET["time"];
	
	
	if($xtime==0){$timesql="AND WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";}
	if($xtime==1){$timesql="AND MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";}
	$sql="SELECT SUM(size) as tsize,DATE_FORMAT(days,'%d') as tday FROM squid_events_clients_sites WHERE client='{$_GET["client"]}' $timesql 
	GROUP BY days ORDER BY UNIX_TIMESTAMP(days)";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
		
	}		
	
	$count=mysql_num_rows($results);
	
	writelogs($count." rows",__FUNCTION__,__FILE__,__LINE__);
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body("<H2 style='color:black'>{NO_DATA_COME_BACK_LATER}</H2>");
		
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=$ligne["tsize"]/1024;
		$size=$size/1000;
		$xdata[]=$ligne["tday"];
		$ydata[]=$size;
		
	}	
	
	$md=md5("{$_GET["client"]}$xtime");
	$cachefile="ressources/logs/web/squid.$md.client.size.png";
	$gp=new artica_graphs(dirname(__FILE__)."/$cachefile",0);
	
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title="MB";
	$gp->x_title="day";
	$gp->title=null;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	return "<img src='$cachefile'>";
		
	
}

function user_hits_graph(){
	$xtime=$_GET["time"];
	
	
	if($xtime==0){$timesql="AND WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";}
	if($xtime==1){$timesql="AND MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";}
	$sql="SELECT SUM(hits) as thits,DATE_FORMAT(days,'%d') as tday FROM squid_events_clients_sites WHERE client='{$_GET["client"]}' $timesql GROUP BY days ORDER BY UNIX_TIMESTAMP(days)";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
		
	}		
	
	$count=mysql_num_rows($results);
	
	writelogs($count." rows",__FUNCTION__,__FILE__,__LINE__);
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body("<H2 style='color:black'>{NO_DATA_COME_BACK_LATER}</H2>");
		
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tday"];
		$ydata[]=$ligne["thits"];
		
	}	
	
	$md=md5("{$_GET["client"]}$xtime");
	$cachefile="ressources/logs/web/squid.$md.client.hits.png";
	$gp=new artica_graphs(dirname(__FILE__)."/$cachefile",0);
	
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title="hits";
	$gp->x_title="days";
	$gp->title=null;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	return "<img src='$cachefile'>";
	
}
function user_size_web_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$xtime=$_GET["time"];
	if($xtime==0){$timesql="AND WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";}
	if($xtime==1){$timesql="AND MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";}
	$sql="SELECT SUM(size) as thits,websites,category FROM squid_events_clients_sites WHERE client='{$_GET["client"]}' $timesql 
	GROUP BY websites,category ORDER BY thits DESC LIMIT 0,100";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("
		<code style='font-size:11px'>$sql</code>
		<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{size}</th>
			<th>{websites}</th>
			<th>{category}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	//javascript:;
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$www=$ligne["websites"];
		$size=$ligne["thits"]/1024;
		$size=FormatBytes($size);
		$js="Loadjs('squid.categorize.php?www=$www')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$size</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$www</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			</tr>";
	
		}		
	
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);
	
}

function user_hits_web_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$xtime=$_GET["time"];
	if($xtime==0){$timesql="AND WEEK(days)=WEEK(NOW()) AND YEAR(days)=YEAR(NOW())";}
	if($xtime==1){$timesql="AND MONTH(days)=MONTH(NOW()) AND YEAR(days)=YEAR(NOW())";}
	$sql="SELECT SUM(hits) as thits,websites,category FROM squid_events_clients_sites WHERE client='{$_GET["client"]}' $timesql 
	GROUP BY websites,category ORDER BY thits DESC LIMIT 0,100";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("
		<code style='font-size:11px'>$sql</code>
		<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
			<th>{hits}</th>
			<th>{websites}</th>
			<th>{category}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	//javascript:;
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$www=$ligne["websites"];
		
		$js="Loadjs('squid.categorize.php?www=$www')";
		$html=$html."
			<tr class=$classtr>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["thits"]}</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">$www</a></td>
			<td style='font-size:15px'><a href=\"javascript:blur();\" OnClick=\"javascript:$js\">{$ligne["category"]}</a></td>
			</tr>";
	
		}		
	
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);
	
}

