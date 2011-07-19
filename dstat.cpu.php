<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');


	if(isset($_GET["gnuplot-png-error"])){dstat_gnuplot_png();exit;}
	$usersmenus=new usersMenus();
	if(!$usersmenus->GNUPLOT_PNG){dstat_gnuplot_png_js();exit;}
	if(!$usersmenus->AsAnAdministratorGeneric){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");
		die();		
	}	
	if(isset($_GET["index"])){dstat_index();exit;}
	if(isset($_GET["mem"])){echo img();exit;}
	if(isset($_GET["top-ressources"])){top_ressources_cpu();exit;}
	if(isset($_GET["top-cpu-list"])){echo top_ressources_cpu_generate();exit;}
	
	js();
	
	
function dstat_gnuplot_png_js(){
$tpl=new templates();
$page=CurrentPageName();
$idmd="dstatcpu";

$title=$tpl->_ENGINE_parse_body('{APP_DSTAT}');

$html="
{$idmd}LoadmemGraph();

	function {$idmd}LoadmemGraph(){
		YahooWin5('650','$page?gnuplot-png-error=yes','$title');
		
	}
	


";	
	
echo $html;	
}
	
function js(){

$tpl=new templates();
$page=CurrentPageName();
$idmd="dstatcpu";

$title=$tpl->_ENGINE_parse_body('{APP_DSTAT}');

$html="var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;

	function {$idmd}demarre(){
		if(!YahooWin5Open()){return;}
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
		var memid=document.getElementById('cpu-memid').value;
		LoadAjax('dstat_cpu_div','$page?mem=yes&d='+memid);
		
	}	
	
	function cpu_refresh_services(){
		{$idmd}ChargeLogs();
	}

	function {$idmd}LoadmemGraph(){
		YahooWin5('650','$page?index=yes','$title');
		setTimeout(\"{$idmd}demarre()\",1000);
	}
	
	function TopRessourcesCPUGraph(){
		YahooWin6('650','$page?top-ressources=yes','$title');
	}	
	
	function cpu_refresh_topcpu(){
	if(document.getElementById('topcpudiv')){
		LoadAjax('topcpudiv','$page?top-cpu-list=yes');
	}
	}
	
	
{$idmd}LoadmemGraph();
";
	echo $html;
	
}	
	
function dstat_index(){
$page=CurrentPageName();
$img=img();

$html="<H1>{cpu_usage} & System</H1><br>
<table style='width:100%'>
<tr>
	<td valign='top'>
	" .RoundedLightWhite(imgtootltip("refresh-32.png","{refresh}","cpu_refresh_services()"))."<br>
	". RoundedLightWhite(imgtootltip('32-charts-plus.png','{top_ressources}',"TopRessourcesCPUGraph()"))."</td>
<td valign='top'><div id='dstat_cpu_div'>
$img
</div>
</td>
</tr>
</table>";	
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
	
}


function img(){
$sock=new sockets();
$date=date('YmdHis');
$sock->getfile("dstatcpu:/usr/share/artica-postfix/ressources/logs/web/dstat-cpu-$date.png");

$img=RoundedLightWhite(

"
<input type='hidden' id='cpu-memid' value='$date'>
<center>
<img src='ressources/logs/web/dstat-cpu-$date.png' style='margin:3px;border:1px solid #CCCCCC'></center>");
return $img;	
	
}

function top_ressources_cpu(){

	$images=top_ressources_cpu_generate();
	$html="<H1>{top_ressources} CPU</H1><br>
	<table style='width:100%'>
	<tr>
	<td valign='top'>" .RoundedLightWhite(imgtootltip("refresh-32.png","{refresh}","cpu_refresh_topcpu()"))."<br></td>
	<td valign='top'>
		<div style='width:99%;height:450px;overflow:auto' id='topcpudiv'>$images</div>
	</td>
	</tr>
	</table>";
		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function top_ressources_cpu_generate(){
	$sock=new sockets();
	$datas=$sock->getfile('dstattopressourcescpu');
	$tbl=explode("\n",$datas);
	if(is_array($tbl)){
		while (list ($num, $file) = each ($tbl)){
			if($file==null){continue;}
			if(preg_match('#dstat\.topcpu\.([0-9]+)\.(.+?)\.png#',$file,$re)){
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
function dstat_gnuplot_png(){
$html="<H1>{GNUPLOT_NOT_PNG}</H1><br>
	<div style='font-size:15px;background-color:white;color:red;padding:10px;margin:5px;border:1px solid red'>{GNUPLOT_NOT_PNG_EXPLAIN}</div>";
		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);

}	
	
	
	
	
	

	
?>