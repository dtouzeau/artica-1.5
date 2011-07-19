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
	
js();


function js(){
	
	$page=CurrentPageName();
	$usr=$_GET["user"];
	
	$html="
	
		function WebUrisStatisticsLoad(){
			YahooWin5('750','$page?popup=yes&domain={$_GET["domain"]}&user={$_GET["user"]}&t={$_GET["t"]}','{$_GET["domain"]}');
		}
		
		function WebUrisStatisticsNext(from){
			YahooWin5('750','$page?popup=yes&domain={$_GET["domain"]}&user={$_GET["user"]}&t={$_GET["t"]}&next='+from,'{$_GET["domain"]}');
		}
	
	
	WebUrisStatisticsLoad();";
	
	echo $html;	
	
}


function popup(){
	$tpl=new templates();
	if($_GET["user"]<>null){$_GET["user"]=" AND CLIENT='{$_GET["user"]}'";}
	if($_GET["t"]=='today'){$_GET["t"]=" AND DATE_FORMAT( zdate, '%Y-%m-%d' )=DATE_FORMAT( NOW(), '%Y-%m-%d' )";}
	if($_GET["t"]=='week'){$_GET["t"]=" AND WEEK( zdate)=WEEK( NOW()) AND YEAR(zdate)=YEAR(NOW())";}
	if($_GET["t"]=='month'){$_GET["t"]=" AND MONTH(zdate)=MONTH( NOW()) AND YEAR(zdate)=YEAR(NOW())";}
	
	if($_GET["next"]==null){$_GET["next"]=0;}
	if($_GET["next"]>0){$next=$_GET["next"]*100;}else{$next=0;}
	
	$nextnext=$next+100;
	$next_query=$_GET["next"]+1;
	$back_query=$_GET["next"]-1;
	$back=button("{back}","WebUrisStatisticsNext('{$back_query}')");
	
	if($next==100){$back=null;}
	
	
	$sql="SELECT zdate,uri FROM dansguardian_events WHERE sitename='{$_GET["domain"]}'{$_GET["user"]}{$_GET["t"]} ORDER BY zDate DESC LIMIT $next,$nextnext ";
	
	
	$html=$tpl->_ENGINE_parse_body("<center style='height:500px;overflow:auto'>
	
	
	<table style='width:100%'>
	<tr>
	<td width=50% align='left'>&nbsp;$back</td>
	<td width=50% align='right'>". button("{next}","WebUrisStatisticsNext('{$next_query}')")."</td>
	</tr>
	</table>
	
	
	<div style='width:90%;text-align:right'>
	</div>
	<table style='width:500px;border:1px solid #CCCCCC'>
	<tr>
		<th colspan=2>{date}</th>
		<th>URI</th>
	</tr>");
	$q=new mysql();
	
	$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$uri=$ligne["uri"];
	if(strlen($ligne["uri"])>50){$uri=texttooltip(substr($ligne["uri"],0,47).'...',$ligne["uri"],null,null,1);}
	
	$html=$html."
	<tr ". CellRollOver("s_PopUp('{$ligne["uri"]}',800,800)").">
		<td width=1%><img src='img/web-22.png'></td>
		<td style='font-size:11px' nowrap valign='top'>{$ligne["zdate"]}</td>
		<td style='font-size:10px' valign='top'>$uri</td>
	</tr>
	";
	}
$html=$html."</table>";
	echo $html;	
	
	
}

?>