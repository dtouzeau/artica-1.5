<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');	
include_once('ressources/class.sockets.inc');	

if(isset($_GET["page"])){page();exit;}
if(isset($_GET["events"])){events();exit;}

js();

function js(){
$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{UPDATE_CLAMAV}');
	$page=CurrentPageName();
	$html="
var FreshClam_timerID  = null;
var FreshClam_tant=0;
var FreshClam_reste=0;


function FreshClam_demarre(){
	   FreshClam_tant = FreshClam_tant+1;
	   FreshClam_reste=FreshClam_tant;
	   if(!YahooWin5Open()){return false;}
		if (FreshClam_tant <10 ) {                           
	      	FreshClam_timerID = setTimeout(\"FreshClam_demarre()\",1000);
	      } else {
	               FreshClam_tant = 0;
	               FreshClam_ChargeLogs();
	               FreshClam_demarre();                                //la boucle demarre !
	   }
}


function FreshClam_ChargeLogs(){
	LoadAjax('freshclamevents','$page?events=yes');
	}
	
	function LoadFreshClam(){
		YahooWin5(750,'$page?page=yes','$title');
		setTimeout(\"FreshClam_Start()\",1000);
	}
	
	function FreshClam_Start(){
		if(document.getElementById('dialog5').innerHTML.length==0){
			setTimeout(\"FreshClam_Start()\",1000);
			return false;
			}
	setTimeout(\"FreshClam_ChargeLogs()\",1000);			
	FreshClam_demarre();
	}
		

	
		
	
	LoadFreshClam();
	
	";
	
	echo $html;
}


function page(){
	
	$html="<H1>{UPDATE_CLAMAV}</H1>
	<div style='width:100%;text-align:right;float:right'><input type='button' OnClick=\"javascript:FreshClam_ChargeLogs();\" value='{refresh}&nbsp;&raquo;'></div><p class=caption>{UPDATE_CLAMAV_EXPLAIN}</p>
	
	<div style='width:100%;height:250px;overflow:auto' id='freshclamevents'></div>";
	$sock=new sockets();
	$sock->getfile('FreshClamStartDebug');
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function events(){
	
	$sock=new sockets();
	$datas=$sock->getfile('FreshClamStartLoadDebug');
	$tbl=explode("\n",$datas);
	$html="<table style='width:100%'>";
	$count=0;
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(trim($ligne)==null){continue;}
		$count=$count+1;
		$ligne=htmlentities($ligne);
		$html=$html . "<tr><td width=1%>$count.&nbsp;</td><td><div style='font-size:11px'><code>$ligne</code></div></td></tr>\n";
		
	}
	$html=$html . "</table>";
	echo RoundedLightWhite($html);
	
}

?>