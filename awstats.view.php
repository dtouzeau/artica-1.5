<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.awstats.inc');

	
	if(isset($_GET["start"])){startpage();exit;}
	if(isset($_GET["awstats_file"])){view_page();exit;}
	
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{statistics}:: {$_GET["servername"]}");
	
	$html="YahooWin2('850','$page?start=yes&awstats_file=index&servername={$_GET["servername"]}','$title');";
	echo $html;
	
}

function startpage(){
	$tpl=new templates();	
	$page=CurrentPageName();	
	$html="
	<div style='text-align:right'><a href='#' OnClick=\"javascript:Goback();\">{go_back}</a></div>
	<div class=form style='height:550px;overflow:auto' id='awstats-id'></div>
	<script>
		function LoadContentPage(filename){
			LoadAjax('awstats-id','$page?awstats_file='+filename+'&servername={$_GET["servername"]}&uuid={$_GET["uuid"]}');
		
		}
		
		function Goback(){
			LoadContentPage('{$_GET["awstats_file"]}');
		}
		
		Goback();
	</script>";
		echo $tpl->_ENGINE_parse_body($html);
	
}



function view_page(){
	if($_SESSION["AWSTATS"][$_GET["servername"]][$_GET["awstats_file"]]<>null){echo $_SESSION["AWSTATS"][$_GET["servername"]][$_GET["awstats_file"]];return;}
	$page=$_GET["awstats_file"];
	$sql="SELECT `content` FROM awstats_files WHERE `servername`='{$_GET["servername"]}' AND `awstats_file`='$page'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$content=$ligne["content"];
	if(preg_match_all("#<a href=\"awstats\.(.+?)\.([a-z0-9]+)\.html#",$content,$re)){
		while (list ($num, $filename) = each ($re[2]) ){
			$content=str_replace("<a href=\"awstats.{$_GET["servername"]}.$filename.html","<a href=\"#\" OnClick=\"javascript:LoadContentPage('$filename')",$content);
			
		}
	}
	
	if(preg_match("#<head>(.+?)</head>#is",$content,$re)){$content=str_replace($re[0],"",$content);}
	$content=str_replace('<body style="margin-top: 0px">','',$content);
	$content=str_replace('</body>','',$content);
	$content=str_replace('</html>','',$content);
	$content=str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">','',$content);
	if(preg_match('#<html lang=".*?">#',$content,$re)){
		$content=str_replace($re[0],"",$content);
	}
	$f[]="perl: warning: Setting locale failed.";
	$f[]="perl: warning: Please check that your locale settings:";
	$f[]="LANGUAGE = (unset),";
	$f[]="LC_ALL = (unset),";
	$f[]="LANG = \"fr_FR.UTF-8\"";
	$f[]="are supported and installed on your system.";
	$f[]="perl: warning: Falling back to the standard locale (\"C\").";
	while (list ($num, $ligne) = each ($f) ){$content=str_replace($ligne,"",$content);}	
	
	$_SESSION["AWSTATS"][$_GET["servername"]][$_GET["awstats_file"]]=$content;
	echo $content; 
	
	
	}
