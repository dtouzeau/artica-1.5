<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_line.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie.php');
include_once (dirname(__FILE__) .'/ressources/jpgraph-3/src/jpgraph_pie3d.php');
	

	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["today"])){today();exit;}
	if(isset($_GET["week"])){today();exit;}
	if(isset($_GET["month"])){today();exit;}
	if(isset($_GET["users-table"])){users_table();exit;}
	
	js();
	
	
	
function js(){
	$page=CurrentPageName();
	$usr=$_GET["user"];
	
	$html="
	
		function WebUserStatisticsLoad(){
			YahooWin4('750','$page?popup=$usr','$usr');
		}
	
	
	WebUserStatisticsLoad();";
	
	echo $html;
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$user=$_GET["popup"];
	$array["today"]='{today}';
	$array["week"]='{this_week}';
	$array["month"]='{this_month}';

	

	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=$user\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_squid_user_stats style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_squid_user_stats').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";	
	
}

function today(){
	
	if(isset($_GET["today"])){
		$user=$_GET["today"];
		$t="today";
		$Q1=" AND DATE_FORMAT( zdate, '%Y-%m-%d' )=DATE_FORMAT( NOW(), '%Y-%m-%d' )";
	}
	
	if(isset($_GET["week"])){
		$t="week";
		$user=$_GET["week"];
		$Q1=" AND WEEK( zdate)=WEEK( NOW()) AND YEAR(zdate)=YEAR(NOW())";
	}

	if(isset($_GET["month"])){
		$t="month";
		$user=$_GET["month"];
		$Q1=" AND MONTH( zdate)=MONTH( NOW()) AND YEAR(zdate)=YEAR(NOW())";
	}		
	$dansguardian_events="dansguardian_events_".date('Ym');	
	$sql="SELECT SUM(QuerySize) as tsize, sitename FROM $dansguardian_events WHERE CLIENT='$user'{$Q1} GROUP BY sitename ORDER BY SUM(QuerySize) DESC";
	
	$html="
	<br>
	<center>
	<table style='width:500px;border:1px solid #CCCCCC'>
	<tr>
		<th colspan=2>{website}</th>
		<th>size</th>
	</tr>
	";
	$q=new mysql();
	
	$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$js="Loadjs('squid.uris.stats.php?domain={$ligne["sitename"]}&user=$user&t=$t')";
	$html=$html."
	<tr". CellRollOver().">
		<td width=1%><img src='img/web-22.png'></td>
		<td style='font-size:13px' ". CellRollOver($js,"{display_queries_list}").">{$ligne["sitename"]}</td>
		<td style='font-size:13px'>". FormatBytes($ligne["tsize"]/1024)."</td>
	</tr>
	";
	}
$html=$html."</table></center>";
	echo $html;
	
	
}

?>