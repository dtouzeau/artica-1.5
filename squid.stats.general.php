<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.artica.graphs.inc');

	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	if(isset($_GET["display-graphs-todayhists"])){popup_today_hits();exit;}
	if(isset($_GET["display-graphs-todaysize"])){popup_today_size();exit;}
	if(isset($_GET["display-graphs-monthhits"])){popup_month_hits();exit;}
	if(isset($_GET["display-graphs-monthsize"])){popup_month_size();exit;}

	popup();
	
function popup(){
$page=CurrentPageName();
	$html="
	<center style='padding:5px'>
		<div id='todayhists'></div>
		<div id='todaysize'></div>
		<div id='monthhits'></div>
		<div id='monthsize'></div>
	</center> 
	
	
	<script>LoadAjax('todayhists','$page?display-graphs-todayhists=yes');</script>
	";
	
	echo $html;
}
function popup_today_hits(){
	$page=CurrentPageName();
	if(!is_file("ressources/logs/day-squid-hits.png")){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
	}else{
		echo "<img src='ressources/logs/day-squid-size.png' style='margin:5px'>";
	}
	echo "<script>LoadAjax('todaysize','$page?display-graphs-todaysize=yes');</script>";
}
function popup_today_size(){
	$page=CurrentPageName();
if(!is_file("ressources/logs/day-squid-size.png")){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
	}else{
	
	echo "<img src='ressources/logs/day-squid-size.png' style='margin:5px'>";
	}
	echo "<script>LoadAjax('monthhits','$page?display-graphs-monthhits=yes');</script>";;
}
function popup_month_hits(){
	$page=CurrentPageName();
	if(!is_file("ressources/logs/month-squid-hits.png")){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
	}else{
	
	echo "<img src='ressources/logs/month-squid-hits.png' style='margin:5px'>";
	}	
	
	echo "<script>LoadAjax('monthsize','$page?display-graphs-monthsize=yes');</script>";
}	
function popup_month_size(){
	$page=CurrentPageName();
	
	if(!is_file("ressources/logs/month-squid-size.png")){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
	}else{
	
	echo "<img src='ressources/logs/month-squid-size.png' style='margin:5px'>";
	}		
}



	
	

?>