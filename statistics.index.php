<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	


	$user=new usersMenus();
	if($user->AllowViewStatistics==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["main"])){popup_switch();exit;}


	js();
	
function js(){

	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{statistics}");
	
	$html="
	function main_statistics_start(){
			$('#BodyContent').load('$page?popup=yes');
			//YahooWin(750,'$page?popup=yes','$title');
		}
	
	main_statistics_start();";
	echo $html;
}

function popup_switch(){
switch ($_GET["main"]) {
		case "index":echo index();break;
		case "system":echo section_system();break;
		case "mail":echo section_messaging();;break;
		case "proxy":echo section_proxy();break;
		default:;break;
	}
	
}

function index(){
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/bg_stats.jpg' style='margin-right:5px'></td>
		<td valign='top'><H1>{statistics}</H1>
		<p style='font-size:16px'>{STATISTICS_EXPLAIN_INDEX}</p></td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function section_messaging(){
$usersmenus=new usersMenus();	
$usersmenus->LoadModulesEnabled();	
$tpl=new templates();
	


$emails_flow=Paragraphe('folder-mailgraph-64.png','{emails_flow}','{emails_flow_text}',"javascript:Loadjs('statistics.mailgraph.php')");
$queue_flow=Paragraphe('folder-queuegraph-64.png','{queue_flow}','{queue_flow_text}',"javascript:Loadjs('statistics.queuegraph.php')");

if($usersmenus->awstats_installed==true){
	if($usersmenus->POSTFIX_INSTALLED){
		$awstats_mail=Paragraphe('folder-awstats-64.png','{awstats} (email)','{awstats_text}',"javascript:s_PopUpFull('cgi-bin/awstats.pl?config=mail',800,800)");
	}
}



if($usersmenus->GRAPHDEFANG_INSTALLED){
	if($usersmenus->MimeDefangEnabled==1){
		$graph_defang=Paragraphe('folder-mailgraph-64.png','{APP_MIMEDEFANG} ({statistics})','{APP_GRAPHDEFANG_TEXT}','index.graphdefang.php');
	}
}
if($usersmenus->kas_installed){
	if($usersmenus->KasxFilterEnabled==1){
		$kas=Paragraphe('kas3-statistics-64.png','{APP_KAS3} ({statistics})','',"javascript:Loadjs('index.kas3-stats.php')");
	}
}




	
if($usersmenus->MILTER_SPY_INSTALLED){
	if($usersmenus->EnableMilterSpyDaemon==1){
		$mailspy=Paragraphe('64-charts.png','{APP_MAILSPY}','{mailspy_statistics}','mailspy.index.php?table=true');
		
	}
}





$tr[]=$emails_flow;
$tr[]=$queue_flow;
$tr[]=$kas;
$tr[]=$awstats_mail;
$tr[]=$mailspy;

$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if(strlen($line)<10){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";
$tables_formatted=$tpl->_ENGINE_parse_body(implode("\n",$tables));		
echo $tables_formatted;


	
}

function section_proxy(){
$usersmenus=new usersMenus();	
$usersmenus->LoadModulesEnabled();	
$tpl=new templates();
		
if($usersmenus->awstats_installed==true){
	if($usersmenus->SQUID_INSTALLED){
		$awstats_squid=Paragraphe('folder-awstats-64.png','{awstats} (squid)','{awstats_text}',"javascript:s_PopUpFull('cgi-bin/awstats.pl?config=squid',800,800)");
	}
}	

$tr[]=$awstats_squid;


$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if(strlen($line)<10){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";
$tables_formatted=$tpl->_ENGINE_parse_body(implode("\n",$tables));		
echo $tables_formatted;
	
}


function section_system(){
$usersmenus=new usersMenus();	
$usersmenus->LoadModulesEnabled();	
$tpl=new templates();
	
$cpustats=Paragraphe('folder-cpustats-64.png','{system_perfomances}','{system_perfomances_text}',"javascript:Loadjs('statistics.systems.yorel.php')");
$storage=Paragraphe('folder-cpustats-64.png','{system_perfomances}','{net_statistic}<hr>{hd_stat}',"javascript:Loadjs('statistics.systems-storage.yorel.php')");

if($usersmenus->collectd_installed){
		$collectd=Paragraphe('64-charts.png','{APP_COLLECTD}','{collectd_statistics_text}','collectd.index.php');
		if($usersmenus->EnableCollectdDaemon==0){$collectd=null;}
	}
	
if($usersmenus->BIND9_INSTALLED){
	if($usersmenus->bindrrd_installed){
		$bind=Paragraphe('folder-netstat-64.png','{APP_BIND9} ({statistics})','{APP_BIND9} ({statistics})','index.bind-stats.php');
	}
}	


$emails_flow=Paragraphe('folder-mailgraph-64.jpg','{emails_flow}','{emails_flow_text}','statistics.mailgraph.php');
$queue_flow=Paragraphe('folder-queuegraph-64.jpg','{queue_flow}','{queue_flow_text}','statistics.queuegraph.php');

$tr[]=$cpustats;
$tr[]=$storage;
$tr[]=$collectd;
$tr[]=$bind;

$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if(strlen($line)<10){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";
$tables_formatted=$tpl->_ENGINE_parse_body(implode("\n",$tables));		
echo $tables_formatted;


	
}


function popup(){

	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]="{index}";
	$array["system"]="{system}";
	
	
	if($users->POSTFIX_INSTALLED){
		$array["mail"]="{messaging}";
		
	}
	if($users->SQUID_INSTALLED){
		$array["proxy"]="{web_proxy}";
	}
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num&\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_stats_index style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_stats_index').tabs({
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
	
	
	
function page(){
$usersmenus=new usersMenus();	
$usersmenus->LoadModulesEnabled();


$html=$html . "
<table style='margin:0px;padding:0px;'>
<tr>
<td valign='top' style='margin:0px;padding:0px;'>$awstats_mail</td>
<td valign='top' style='margin:0px;padding:0px;'>$graph_defang</td>
<td valign='top' style='margin:0px;padding:0px;'>$collectd</td>
<tr>
<td valign='top' style='margin:0px;padding:0px;'>$kas</td>
<td valign='top' style='margin:0px;padding:0px;'>$mailspy</td>
<td valign='top' style='margin:0px;padding:0px;'>$awstats_squid</td>
</tr>
<tr>
<td valign='top' style='margin:0px;padding:0px;'>$bind<td>
<td valign='top' style='margin:0px;padding:0px;'></td>
<td valign='top' style='margin:0px;padding:0px;'>&nbsp;</td>
</tr>
</table>
";	

$tpl=new template_users('{statistics}',$html);
echo $tpl->web_page;
	
	
	
}
	
	
?>	

