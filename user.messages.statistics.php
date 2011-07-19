<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/charts.php');
session_start();

$users=new usersMenus();

INDEX();


function INDEX(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$mail=$hash["mail"];	
	$Graph=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?weekMessagesPerDay=$mail",600,250,"FFFFFF",true,$usermenus->ChartLicence);
	$Graph1=InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?QuarMessagesPerDay=$mail",600,250,"FFFFFF",true,$usermenus->ChartLicence);
	
	
$html="
<H4>{graph_week_receive}</H4>
$Graph
<H4>{graph_week_quarantine}</H4>
$Graph1
<H4>{quarantine_domains}</h4>
" . BigDomainsReceive($mail);
$JS["JS"][]="js/user.quarantine.js";
$tpl=new template_users('{messages_performance}',$html,0,0,0,0,$JS);
echo $tpl->web_page;		
	
}

function BigDomainsReceive($email){
	
$sql="SELECT count( ID ) as tcount ,mailfrom_domain,filter_action,mail_to  FROM `messages` GROUP BY mailfrom_domain,filter_action,mail_to HAVING filter_action = 'quarantine' AND count( ID )>0 AND mail_to='$email'  ORDER BY count( ID ) DESC
limit 0,20";
	$results=QUERY_SQL($sql);

	$html="<table style='width:100%'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($ligne["mailfrom_domain"]<>null){
			$html=$html . "<tr>
			<td class='bottom'><strong>{$ligne["tcount"]}</td>
			<td class='bottom'><strong>{$ligne["mailfrom_domain"]}</td>
			</tr>
			";
		}
		}
		
	return $html . "</table>";	
	
}