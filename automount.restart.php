<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.os.system.inc");


	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["logs"])){echo showlogs();exit;}

	
	
js();



function js(){
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{restart_autofs}');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		RTMMail(550,'$page?popup=yes','$title');
		{$prefix}ChargeTimeout();
	
	}
	
	function {$prefix}demarre(){
		if(!RTMMailOpen()){return false;}
		{$prefix}tant = {$prefix}tant+1;
			if ({$prefix}tant <10 ) {                           
				{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
		      } else {
						if(!RTMMailOpen()){return false;}
						{$prefix}tant = 0;
						{$prefix}ChargeLogs();
						{$prefix}demarre();
		   }
	}
	
var x_{$prefix}ChargeLogs=function (obj) {
 	text=obj.responseText;
	document.getElementById('AutofsDiv159').innerHTML=text;

	}	
	

	function {$prefix}ChargeLogs(){
	var currentTime = new Date()
	    var XHR = new XHRConnection();
        XHR.appendData('logs','yes');
        XHR.appendData('s',currentTime.getTime());
       	XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs);
	}
	
	function {$prefix}ChargeTimeout(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>20){
			alert('time-out $page');
			return false;
		}
		
		if(!document.getElementById('AutofsDiv159')){
			setTimeout(\"{$prefix}ChargeTimeout()\",900);
			return false;
			}
		
		{$prefix}timeout=0;
		{$prefix}ChargeLogs();
		{$prefix}demarre();
		
	}
	
	{$prefix}LoadPage();
";	

	echo $html;
	
}

function popup(){
	
	
	$sock=new sockets();
	$sock->getfile('ServiceAutofsRestart');
	$logs=showlogs();
	$html="<H1>{restart_autofs}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/service-restart-64.png'></td>
		<td valign='top'><p class=caption>{restart_autofs_text}</p>
		". RoundedLightWhite("<div style='width:100%;height:150px;overflow:auto' id='AutofsDiv159'>$logs</div>")."<td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function showlogs(){
	
	$tpl=new templates();
	if(!is_file('ressources/logs/autofs.restart.log')){
		return $tpl->_ENGINE_parse_body('{scheduled}: {please_wait} (nofile)');
	}
	$text=file_get_contents('ressources/logs/autofs.restart.log');
	$datas=explode("\n",$text);
	

	
	if(!is_array($datas)){
		return $tpl->_ENGINE_parse_body('{scheduled}: {please_wait} (noarray)');
	}
	$ct=0;
	while (list($num,$val)=each($datas)){
		if(trim($val)==null){continue;}
		$ct=$ct+1;
		$html=$html . "<div><code>" .htmlspecialchars($val)."</code></div>\n";
		
	}
	
	return $tpl->_ENGINE_parse_body($html);
	}


?>