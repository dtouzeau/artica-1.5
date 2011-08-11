<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	$usersprivs=new usersMenus();
	if(!$usersprivs->AsSystemAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
		
	}
	if(isset($_GET["in-front-ajax"])){js();exit;}
	if(isset($_GET["mysql-events"])){page_events();exit;}
	
page();

function js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page');";
}

function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	
	$html="
	<div id='mysql_events_logs'></div>
	
	<script>
		function RfereshMysqlEvents(){
			LoadAjax('mysql_events_logs','$page?mysql-events=yes');
		
		}
	RfereshMysqlEvents();
	</script>
	";
	
	echo $html;
	
	
}

function page_events(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("services.php?mysql-events=yes")));

		$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-24.png","{refresh}","RfereshMysqlEvents()")."</th>
		<th >{APP_MYSQL}::{events}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
		while (list ($num, $ligne) = each ($datas) ){
	

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:normal;' colspan=2><code>$ligne</code></td>
		</tr>
		";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}