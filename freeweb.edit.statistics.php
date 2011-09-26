<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.artica.graphs.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["traffic-today"])){traffic_today();exit;}
	if(isset($_GET["memory-today"])){memory_today();exit;}
	if(isset($_GET["today"])){stats_today();exit;}
tabs_start();

function tabs(){
	$tpl=new templates();	
	$page=CurrentPageName();

	$array["status"]="{events}";
	//$array["today"]="{last_24h}";
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_freewebstats style='width:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_freewebstats\").tabs();});
		</script>";		
	
	
}


function tabs_start(){
	$tpl=new templates();	
	$page=CurrentPageName();	
	$md=md5($_GET["servername"]);
	$html="
	<div id='statistics-$md'></div>
	<script>
		function RfreshTabsFreewbsTats(){
			LoadAjax('statistics-$md','$page?tabs=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}');
		}	
		RfreshTabsFreewbsTats();	
	</script>
	";
	
	echo $html;
	
}

function status(){
	$servername=$_GET["servername"];
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$table_name=$q->APACHE_TABLE_NAME($_GET["servername"]);
	$sql="SELECT request_uri,remote_host,DATE_FORMAT(from_unixtime(time_stamp),'%H:%i') as zDate FROM $table_name 
	WHERE from_unixtime(time_stamp)> DATE_SUB(NOW(),INTERVAL 8 HOUR) ORDER BY time_stamp DESC LIMIT 0,90";
	$results=$q->QUERY_SQL($sql,"apachelogs");
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{date}</th>
	<th>{client}</th>
	<th>URI</th>	
	</tr>
</thead>
<tbody class='tbody'>";	

	$pdns=new pdns();
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$html=$html."
			<tr class=$classtr>
			<td width=1% nowrap style='font-size:14px'>{$ligne["zDate"]}</td>
			<td width=1% align='left' nowrap style='font-size:14px'>{$ligne["remote_host"]}</td>
			<td width=99% align='left' style='font-size:12px'>{$ligne["request_uri"]}</td>
			</tr>
			";
		
	}
	
	
	$html=$html."</tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
