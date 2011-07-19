<?php
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.ldap.inc');
include_once ("ressources/jpgraph-3/src/jpgraph.php");
include_once ("ressources/jpgraph-3/src/jpgraph_pie.php");
include_once ("ressources/jpgraph-3/src/jpgraph_pie3d.php");
include_once ("ressources/jpgraph-3/src/jpgraph_line.php");
include_once ("ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.artica.graphs.inc");

if(isset($_GET["view-stats"])){view_stats();exit;}

js();



	
function js(){
$tpl=new templates();	


if(!privs()){
	$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	echo "alert('$error');";
	die();
}
	$ou=$_GET["ou"];
	$page=CurrentPageName();
	$tile=$tpl->_ENGINE_parse_body("{statistics}:$ou");
	$suffix=md5($ou);
	$html="
	function Stat$suffix(){
		YahooWin5(750,'$page?view-stats=$ou&ou=$ou','$tile');	
		
	}
	
	Stat$suffix();
	";
	
echo $html;	
	
}


function view_stats(){
	if(!privs()){die();}
	$ou=$_GET["ou"];
	$users=new usersMenus();
	sql_domain($ou);
	
	
$sql="SELECT COUNT( ID ) AS tcount, DATE_FORMAT( time_stamp, '%m-%d' ) AS tday
FROM smtp_logs
WHERE {$GLOBALS["SQL_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -7
DAY )
GROUP BY DATE_FORMAT( time_stamp, '%m-%d' )";
$q=new mysql();

//echo $sql;
$g=new artica_graphs($fileName,60);
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]  =$ligne["tday"];
}

//print_r($g->ydata);

$fileName="/usr/share/artica-postfix/ressources/logs/web/$ou-stats-7.png";

$g->title="Inbound messages";
$g->x_title="messages number";
$g->y_title="days-month";
$g->width=700;	
$g->filename=$fileName;
$g->line_green();	

	

echo "<img src='ressources/logs/web/$ou-stats-7.png'>";

	
}

function privs(){
$usersmenus=new usersMenus();
$ou=$_GET["ou"];
$niprov=false;
if(($usersmenus->AllowAddUsers) OR ($usersmenus->AsOrgAdmin)){	
	$niprov=true;
}

if(!$niprov){return false;}

if($_SESSION["uid"]<>-100){
		if($_SESSION["ou"]<>$ou){
		$niprov=false;
		}
	}
return $niprov;
}
	
	
function sql_domain($ou){
	
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($ou);
	if(!is_array($domains)){return null;}
	while (list ($domain,$nothing) = each ($domains) ){
		$array_domain[]="OR delivery_domain='$domain'";
		$GLOBALS["mydomains"][]=$domain;
	}
	
	$sql_domain=implode(" ",$array_domain);
	if(substr($sql_domain,0,2)=="OR"){$sql_domain=substr($sql_domain,2,strlen($sql_domain));}
	$sql_domain="(".trim($sql_domain).")";
	$GLOBALS["SQL_DOMAINS"]=$sql_domain;	
	
}	
	
	
	

	
?>	

