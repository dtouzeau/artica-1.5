<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.syslogs.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');
include_once('ressources/class.user.inc');

if(isset($_GET["list"])){cnx_list();exit;}

$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}
$page=CurrentPageName();

$html="
<div id='admin-cnx' style='width:100%;height:650px;overflow:auto'></div>

<script>
	function RefreshCNX(){
		LoadAjax('admin-cnx','$page?list=yes');
	}
	RefreshCNX();
	
</script>

";

echo $html;


function cnx_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>$refresh</th>
		<th>{date}</th>
		<th>{server}</th>
		<th>{member}</th>
		<th>{panel}</th>
		<th>{ipaddr}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$sql="SELECT * FROM admin_cnx ORDER BY connected DESC LIMIT 0,100";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_events");
		
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["uid"]==-100){$uid="Manager";}else{
			$u=new user($ligne["uid"]);
			$uid=$u->DisplayName;
		}
		
		$date=date('H:i:s',strtotime($ligne["connected"]));
		
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/user-32.png'></td>
		<td style='font-size:11px'>$date</td>
		<td style='font-size:11px'>{$ligne["webserver"]}</td>
		<td style='font-size:11px'>$uid</td>
		<td style='font-size:11px'>{$ligne["InterfaceType"]}</td>
		<td style='font-size:11px'>{$ligne["ipaddr"]} ({$ligne["hostname"]})</td>
		</tr>
		
		";
		
}
				
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}