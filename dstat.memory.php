<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');



	$usersmenus=new usersMenus();
	if(!$usersmenus->GNUPLOT_PNG){header('location:dstat.cpu.php?gnuplot-false=yes');die();}
	if(!$usersmenus->AsAnAdministratorGeneric){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");
		die();		
	}	
	if(isset($_GET["index"])){dstat_index();exit;}
	if(isset($_GET["mem"])){echo img();exit;}
		if(isset($_GET["top-ressources"])){top_ressources_mem();exit;}
	if(isset($_GET["top-mem-list"])){echo top_ressources_mem_generate();exit;}	
	
	js();
	
	
function js(){

$tpl=new templates();
$page=CurrentPageName();
$idmd="dstatmem";

$title=$tpl->_ENGINE_parse_body('{APP_DSTAT}');

$html="var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;

	function {$idmd}demarre(){
		if(!YahooWin6Open()){return false;}
		{$idmd}tant = {$idmd}tant+1;
		{$idmd}reste=10-{$idmd}tant;
		if ({$idmd}tant < 10 ) {                           
			{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",1000);
	      } else {
			{$idmd}tant = 0;
			{$idmd}ChargeLogs();
			{$idmd}demarre();                                
	   }
	}


	function {$idmd}ChargeLogs(){
		var memid=document.getElementById('memid').value;
		LoadAjax('dstat_mem_div','$page?mem=yes&d='+memid);
		
	}	
	
	function mem_refresh_services(){
		{$idmd}ChargeLogs();
	}

	function LoadmemGraph(){
		YahooWin6('600','$page?index=yes','$title');
		setTimeout(\"{$idmd}demarre()\",1000);
	}
	
	function TopRessourcesMEMGraph(){
		YahooWin3('650','$page?top-ressources=yes','$title');
	}	
	
	function mem_refresh_topmem(){
		if(document.getElementById('topmemdiv')){
			LoadAjax('topmemdiv','$page?top-mem-list=yes');
		}
	}	
	
	
LoadmemGraph();
";
	echo $html;
	
}	
	
function dstat_index(){
	
$img=img();

$html="<H1>Physical Memory statistics</H1><br>
<table style='width:100%'>
<tr>
	<td valign='top'>
	" .RoundedLightWhite(imgtootltip("refresh-32.png","{refresh}","mem_refresh_services()"))."<br>
	". RoundedLightWhite(imgtootltip('32-charts-plus.png','{top_ressources}',"TopRessourcesMEMGraph()"))."</td>
<td valign='top'><div id='dstat_mem_div'>
$img
</div>
</td>
</tr>
</table>";
	
echo $html;
	
}


function img(){
$sock=new sockets();
$date=date('YmdHis');
$sock->getfile("dstatmem:/usr/share/artica-postfix/ressources/logs/web/dstat-mem-$date.png");

$img=RoundedLightWhite(

"
<input type='hidden' id='memid' value='$date'>
<img src='ressources/logs/web/dstat-mem-$date.png' style='margin:3px;border:1px solid #CCCCCC'>");
return $img;	
	
}
function top_ressources_mem(){

	$images=top_ressources_mem_generate();
	$html="<H1>{top_ressources} {memory}</H1><br>
	<table style='width:100%'>
	<tr>
	<td valign='top'>" .RoundedLightWhite(imgtootltip("refresh-32.png","{refresh}","mem_refresh_topmem()"))."<br></td>
	<td valign='top'>
		<div style='width:99%;height:450px;overflow:auto' id='topmemdiv'>$images</div>
	</td>
	</tr>
	</table>";
		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function top_ressources_mem_generate(){
	$sock=new sockets();
	$datas=$sock->getfile('dstattopressourcesmem');
	$tbl=explode("\n",$datas);
	if(is_array($tbl)){
		while (list ($num, $file) = each ($tbl)){
			if($file==null){continue;}
			if(preg_match('#dstat\.topmem\.([0-9]+)\.(.+?)\.png#',$file,$re)){
				$arr[$re[1]]="ressources/logs/$file";
			}else{
				echo "<br>$file !!";
			}
			
		}
	}
	if(!is_array($arr)){return null;}
	rsort($arr);
	while (list ($num, $file) = each ($arr)){
		$images=$images ."<div style='width:525px;padding:3px;margin:3px border:1px solid #CCCCCC;'>".RoundedLightWhite("<img src='$file'>")."</div><br>";
		
	}

	return $images;
}
	
?>