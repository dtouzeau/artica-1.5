<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	
	$users=new usersMenus();
	if(!$users->AsSquidAdministrator){die();}	
	
	
BlockedSites();	

function BlockedSites(){
$page=CurrentPageName();
$tpl=new templates();		
$tableblock=date('Ymd')."_blocked";	
$q=new mysql_squid_builder();
$sql="SELECT * FROM $tableblock ORDER BY ID DESC LIMIT 0,150";



$results=$q->QUERY_SQL($sql,"artica_events");
if(!$q->ok){
	echo "<H2>$q->mysql_error</H2>";	
	
}	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th width=1%>{date}</th>
	<th>{member}</th>
	<th>{website}</th>
	<th>{category}</th>
	<th>{rule}</th>
	</tr>
</thead>
<tbody>";	


$today=date('Y-m-d');
$d+0;
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$ligne["zDate"]=str_replace($today,"{today}",$ligne["zDate"]);
	if(preg_match("#plus-(.+?)-artica#",$ligne["category"],$re)){$ligne["category"]=$re[1];}
	$html=$html."
	<tr class=$classtr>
		<td style='font-size:13px' nowrap width=1%>{$ligne["zDate"]}</td>
		<td style='font-size:13px' width=1%>{$ligne["client"]}</td>
		<td style='font-size:13px' width=99%><strong><code>
		<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('squid.categories.php?category={$ligne["category"]}&website={$ligne["website"]}')\" 
		style='font-weight:bold;text-decoration:underline;font-size:13px'>{$ligne["website"]}</a></code></strong></td>
		<td style='font-size:13px' width=1% align='center'>{$ligne["category"]}</td>
		<td style='font-size:13px' width=1% align='center'>{$ligne["rulename"]}</td>
	</tr>
	";
	
}
$html=$html."</tbody></table>";
echo $tpl->_ENGINE_parse_body($html);
}