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
if(isset($_GET["graph"])){graphs();exit;}
js();

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{queue_flow}");
	
	$html="
	function main_queue_statistics_start(){
			YahooWin2(790,'$page?popup=yes','$title');
		}
	
	main_queue_statistics_start();";
	echo $html;	
	
	
}

function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["day"]="{queue_flow}";

	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?graph=yes&t=$num&\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_stats_yorels_index style='width:100%;height:800px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_stats_yorels_index').tabs({
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


	

function graphs(){
$t=$_GET["t"];
/*
if($t=='day'){$day='id=tab_current';$title=$t;$t="1$t";}
if($t=='week'){$week='id=tab_current';$t="2$t";}
if($t=='month'){$month='id=tab_current';$t="3$t";}	

<div id=tablist>
<li><a href=\"javascript:LoadAjax2('graphs','$page?graph=yes&hostname={$_GET["hostname"]}&t=day');\">{day}</a></li>
<li><a href=\"javascript:LoadAjax2('graphs','$page?graph=yes&hostname={$_GET["hostname"]}&t=week');\">{week}</a></li>
<li><a href=\"javascript:LoadAjax2('graphs','$page?graph=yes&hostname={$_GET["hostname"]}&t=month');\">{month}</a></li>
</div>*/



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
<p class='caption'>{queue_flow_text}</p>
<table style='width:600px' align=center>
<tr>
<td valign='top'>
	<center style='margin:4px'>
		<H5>{queue_flow_day}</H5>
		<img src='images.listener.php?uri=mailgraph/queuegraph_0.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{queue_flow_week}</H5>
		<img src='images.listener.php?uri=mailgraph/queuegraph_1.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{queue_flow_month}</H5>
		<img src='images.listener.php?uri=mailgraph/queuegraph_2.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{queue_flow_year}</H5>
		<img src='images.listener.php?uri=mailgraph/queuegraph_3.png&md=$md'>
	</center>						
</td>
</tr>
</table>	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	

	
	
?>	

