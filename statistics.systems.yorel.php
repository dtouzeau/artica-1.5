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
if(isset($_GET["graph"])){graphs();exit;}
if(isset($_GET["popup"])){popup();exit;}


js();

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{system_perfomances}");
	
	$html="
	function main_yorels_statistics_start(){
			YahooWin2(790,'$page?popup=yes','$title');
		}
	
	main_yorels_statistics_start();";
	echo $html;	
	
	
}


function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["day"]="{day}";
	$array["week"]="{week}";
	$array["month"]="{month}";
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?graph=yes&t=$num&\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_stats_yorels_index style='width:100%;height:auto;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_stats_yorels_index').tabs();
			
			
			});
		</script>";


	
	
}


function graphs(){
$t=$_GET["t"];
if($t=='day'){$day='id=tab_current';$title=$t;$t="1$t";}
if($t=='week'){$week='id=tab_current';$t="2$t";}
if($t=='month'){$month='id=tab_current';$t="3$t";}	



header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("content-type:text/html");

$md=md5(date('Ymdhis'));
	$users=new usersMenus(nul,0,$_GET["hostname"]);
	
	
	
	$html="
<input type='hidden' id='t' value='$t'>
<div class='explain'>{system_perfomances_text}</div
<table style='width:600px' align=center>
<tr>
<td valign='top'>
	<center style='margin:4px'>
		<H5>{cpu_stat}</H5>
		<img src='images.listener.php?uri=system/rrd/01cpu-$t.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{mem_stat}</H5>
		<img src='images.listener.php?uri=system/rrd/03mem-$t.png&md$md'>
	</center>
	<center style='margin:4px'>
		<H5>{load_stat}</H5>
		<img src='images.listener.php?uri=system/rrd/02loadavg-$t.png'>
	</center>
	<center style='margin:4px'>
		<H5>{proc_stat}</H5>
		<img src='images.listener.php?uri=system/rrd/06proc-$t.png'>
	</center>					
</td>
</tr>
</table>	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}
	
	
?>	

