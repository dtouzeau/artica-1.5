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
	YahooWin('650','$page?popup-index=yes','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}


function popup_index(){

$html="
<h1>{performances_settings_text}</H1>
<table style='width:600px' align=center>
<tr>
<td valign='top'>" . RoundedLightWhite("<img src='img/bg_perf.jpg'>")."</td>
<td valign='top'>
	".Paragraphe('folder-cache-64.png','{smtp_connection_cache_destinations}','{smtp_connection_cache_destinations_minitext}',"javascript:Loadjs('postfix.performances.cache.php')") .
	Paragraphe('folder-cache-64.png','{address_verify_map}','{address_verify_map_minitext}',"javascript:Loadjs('postfix.performances.verify.map.php')") ."</td>

</td>
</tr>
</table>
<table>
	<tr>
	<td valign='top' >".Paragraphe('folder-equerre-64.png','{title_postfix_tuning}','{title_postfix_tuning_text}',"javascript:Loadjs('postfix.performances.tuning.php')") ."</td>
	<td valign='top' >".Paragraphe('folder-fallback-64.png','{smtp_fallback_relay}','{smtp_fallback_relay_tiny}',"javascript:Loadjs('postfix.fallback.relay.php')") ."</td>
	</tr>	
</table>";


$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html); 
}



?>