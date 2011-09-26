<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");


if(isset($_GET["popup-index"])){popup_index();exit;}
if(isset($_GET["popup-tabs"])){tabs();exit;}
js();

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{performances_settings}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

	
$html="function {$prefix}Loadpage(){
	YahooWin('700','$page?popup-tabs=yes','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}


function tabs(){
	$tpl=new templates();
	$array["popup-index"]='{index}';
	$array["smtp_connection_cache_destinations"]='{smtp_connection_cache_destinations}';
	$array["address_verify_map"]='{address_verify_map}';
	$array["title_postfix_tuning"]='{title_postfix_tuning}';
	
	
	
	if($_GET["hostname"]==null){$_GET["hostname"]="master";}
	
	
	
	
	$page=CurrentPageName();

	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="smtp_connection_cache_destinations"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.performances.cache.php?with-tabs=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="address_verify_map"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.performances.verify.map.php?with-tabs=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}	

		if($num=="title_postfix_tuning"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.performances.tuning.php?with-tabs=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
	}
	
	echo "$menus
	<div id=main_post_perfs_tabs style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_post_perfs_tabs').tabs();
			
			
			});
		</script>";	
	
	
}



function popup_index(){

$html="
<div class=explain>{performances_settings_text}</div>
<table style='width:100%' align=center>
<tr>
<td valign='top'><img src='img/bg_perf.jpg'></td>
<td valign='top'>
".ParagrapheTEXT('cache-refresh-48.png','{smtp_connection_cache_destinations}','{smtp_connection_cache_destinations_minitext}',"javascript:Loadjs('postfix.performances.cache.php?hostname={$_GET["hostname"]}')") .
ParagrapheTEXT('cache-refresh-48.png','{address_verify_map}','{address_verify_map_minitext}',"javascript:Loadjs('postfix.performances.verify.map.php')") ."
".ParagrapheTEXT('folder-equerre-48.png','{title_postfix_tuning}','{title_postfix_tuning_text}',"javascript:Loadjs('postfix.performances.tuning.php')") ."
".ParagrapheTEXT('folder-fallback-48.png','{smtp_fallback_relay}','{smtp_fallback_relay_tiny}',"javascript:Loadjs('postfix.fallback.relay.php')") ."
</td>
</tr>
</table>
";


$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html); 
}



?>