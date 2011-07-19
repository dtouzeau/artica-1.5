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
	$title=$tpl->_ENGINE_parse_body("{system_perfomances}");
	
	$html="
	function main_yorel_statistics_start(){
			YahooWin2(790,'$page?popup=yes','$title');
		}
	
	main_yorel_statistics_start();";
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
	<div id=main_stats_yorel_index style='width:100%;height:800px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_stats_yorel_index').tabs({
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
<p class='caption'>{system_perfomances_text}</p>
<table style='width:600px' align=center>
<tr>
<td valign='top'>
	<center style='margin:4px'>
		<H5>{hd_stat}</H5>
		<img src='images.listener.php?uri=system/rrd/04hddio-$t.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{hdFree_stat}</H5>
		<img src='images.listener.php?uri=system/rrd/05hdd-$t.png&md$md'>
	</center>
	<center style='margin:4px'>
		<H5>{net_statistic} (eth0)</H5>
		<img src='images.listener.php?uri=system/rrd/10net-eth0-$t.png'>
	</center>					
</td>
</tr>
</table>	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
	
	
?>	

