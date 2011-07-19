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
	
	js();
	
	
function js(){

$tpl=new templates();
$page=CurrentPageName();
$idmd="dstatpostfix";

$title=$tpl->_ENGINE_parse_body('{APP_DSTAT}');

$html="var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;

	function {$idmd}demarre(){
		if(!YahooWin4Open()){return false;}
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
		var memid=document.getElementById('postfix-memid').value;
		LoadAjax('dstat_postfix_div','$page?mem=yes&d='+memid);
	}	
	
	function postfix_refresh_services(){
		{$idmd}ChargeLogs();
	}

	function {$idmd}LoadmemGraph(){
		YahooWin4('600','$page?index=yes','$title');
		setTimeout(\"{$idmd}demarre()\",1000);
	}
	
	
{$idmd}LoadmemGraph();
";
	echo $html;
	
}	
	
function dstat_index(){
	
$img=img();

$html="<H1>{APP_POSTFIX} {statistics}</H1><br>
<div style='text-align:right;float:right'>" .imgtootltip("refresh-24.png","{refresh}","postfix_refresh_services()")."</div>
<div id='dstat_postfix_div'>
$img
</div>";	
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
	
}


function img(){
$sock=new sockets();
$date=date('YmdHis');
$sock->getfile("dstatpostfix:/usr/share/artica-postfix/ressources/logs/web/dstat-postfix-$date.png");

$img=RoundedLightWhite(

"
<input type='hidden' id='postfix-memid' value='$date'>
<center>
<img src='ressources/logs/web/dstat-postfix-$date.png' style='margin:3px;border:1px solid #CCCCCC'></center>");
return $img;	
	
}
	
?>