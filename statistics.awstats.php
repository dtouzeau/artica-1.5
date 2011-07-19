<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AllowViewStatistics==false){header('location:users.index.php');exit;}


page();	
function page(){
$usersmenus=new usersMenus();	

if(isset($_GET["generate"])){
	$sock=new sockets();
	$error=$sock->getfile('awstats_generate');
	if($error<>null){
		$error="<center style='font-size:13px;border:1px dotted #CCCCCC;padding:10px;margin:10px'><strong><code>{generate}:<br>artica-install report:$error</code></strong></center>";
	}
}

if(!isset($_GET["page"])){$index_page='index';}else{$index_page=$_GET["page"];}

$page=dirname(__FILE__). "/ressources/logs/awstats.$index_page.tmp";

if(!file_exists($page)){
	$html="$error<p style='font-weight:bold;color:red;font-size:14px;border:1px dotted #CCCCCC;padding:10px;marging:10px'>
	{error_unable_to_find_path}:<br>$page<br>
	{infos_awstats_must_generate}
	<br><br><br>
	<center>
		<input type='button' OnClick=\"javascript:MyHref('" . CurrentPageName() . "?generate=yes&page=$index_page');\" value='{generate}&nbsp;&raquo;'>
	</center>
	<br></p>
	";
	$tpl=new templates();
	$tpl->_ENGINE_parse_body($html);
	$tpl=new template_users('{awstats_statistics_' .$index_page . '}',$html,0,0,0,60);
	echo $tpl->web_page;		
	exit;
	
}

$index=dirname(__FILE__). "/ressources/logs/awstats.$index_page.tmp";
$datas=file_get_contents($index);
$css=file_get_contents(dirname(__FILE__). "/ressources/databases/awstats.css.db");



if(preg_match('#<page_start>(.+)<page_end>#is',$datas,$regs)){
	$page_datas=ReplaceHtmlCode($regs[1]);
}



$html="$css
<div style='width:640px'>
$page_datas
</div>";

$tpl=new template_users('{awstats_statistics_' .$index_page . '}',$html,0,0,0,60);
echo $tpl->web_page;
	
	
	
}

function ReplaceHtmlCode($html){
	$page=CurrentPageName();
	$html=str_replace("<span style=\"font-size: 14px;\">","<span style='font-weight:bold'>",$html);
	$html=str_replace("<span style=\"font-size: 12px;\">","<span style='font-weight:bold'>",$html);
	$html=str_replace("<span style=\"font-size: 9px;\">","<span style='font-size:8px'>",$html);
	$html=str_replace("border=\"1\"",'border=0',$html);
	$html=str_replace("src=\"/icon","src=\"img/awstats",$html);
	$html=str_replace("target=\"awstatsbis\"",'',$html);
	$html=str_replace("<a href=\"javascript:parent.window.close();\">Close window</a>","<a href='$page'>{main}</a>",$html);
	
if(preg_match_all('#awstats\.mail\.(\w+)\.html#is',$html,$regs)){
	while (list ($index, $ligne) = each ($regs[1]) ){
		$action=$ligne;
		$pagetoreplace="awstats.mail.$action.html";
		$html=str_replace($pagetoreplace,"$page?page=$action",$html);
		
	}
}	
	
	return $html;
	
	
}


	
	
?>	

