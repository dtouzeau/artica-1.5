<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("content-type:text/html");
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');

if(posix_getuid()<>0){
	$users=new usersMenus();
	if((!$users->AsPostfixAdministrator) OR (!$users->AsSystemAdministrator)){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["MailGraphEnabled"])){MailGraphEnabled();exit;}
if(isset($_GET["mailgraphs"])){echo getGraphs();exit;}
if(isset($_GET["status"])){echo MailGraphStatus();exit;}

js();
exit;

function js(){
	
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=strip_tags($tpl->_ENGINE_parse_body('{APP_MAILGRAPH}'));
	
	
	
	$start="{$prefix}LoadPage()";
	
	
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;

	
	function {$prefix}demarre(){
		if(!RTMMailOpen()){return;}
		if(!document.getElementById('mailgraphs')){return;}
		{$prefix}tant = {$prefix}tant+1;
		if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",3000);
	      } else {
			{$prefix}tant = 0;
			{$prefix}ChargeLogs();
			{$prefix}demarre();                               
	   }
	}	
		
		function {$prefix}LoadPage(){
			RTMMail(780,'$page?popup=yes','$title');
			{$prefix}demarre();
		}	

		function {$prefix}ChargeLogs(){
			if(!RTMMailOpen()){return;}
			LoadAjax('mailgraph-status','$page?status=yes');
			setTimeout(\"{$prefix}graphs()\",1500);
			
		}
		
		function {$prefix}graphs(){
			LoadAjax('mailgraphs','$page?mailgraphs=yes');
		}
		
		
		var x_EnableMailGraph= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			{$prefix}LoadPage()
			}	
		
		function EnableMailGraph(){
			var XHR = new XHRConnection();
			XHR.appendData('MailGraphEnabled',document.getElementById('MailGraphEnabled').value);
			document.getElementById('EnableMailGraphDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_EnableMailGraph);
		}
		
		$start
";

echo $html;

}

function popup(){
	$tpl=new templates();
	$title=strip_tags($tpl->_ENGINE_parse_body('{APP_MAILGRAPH}'));
	$sock=new sockets();
	$MailGraphEnabled=$sock->GET_INFO('MailGraphEnabled');
	$enable=Paragraphe_switch_img('{enable}','{enable_mailgraph_text}','MailGraphEnabled',$MailGraphEnabled,null,285);
	$status=MailGraphStatus();
	
	$html="
	<H1>$title</H1>
	<table style=width:100%'>
	<tr>
		<td valign='top'><img src='img/statistics-128.png'></td>
		<td valign='top'>
		<table style='with:100%'>
		<tr>
		<td valign='top'>
			<p class=caption>{APP_MAILGRAPH_TEXT}</p>		
			<div id='EnableMailGraphDiv'>
			$enable
			<hr>
			<div style='width:100%;text-align:right'>
			<input type='button' OnClick=\"javascript:EnableMailGraph()\" value='{edit}&nbsp;&raquo;'>
			</div>
		</td>
		<td valign='top'><div id='mailgraph-status'>$status</div>
			
		</div>
		</td>
		</tr>
		</table>
	</tr>
	</table>
	". RoundedLightWhite("<div id='mailgraphs' style='width:100%;height:350px;overflow:auto'>". getGraphs()."</div>")."
	
	";
	
	echo $tpl->_ENGINE_parse_body($html,'postfix.index.php');	
	
}

function MailGraphEnabled(){
	$sock=new sockets();
	$sock->SET_INFO('MailGraphEnabled',$_GET["MailGraphEnabled"]);
	$sock->getFrameWork('cmd.php?restart-mailgraph=yes');
}


function getGraphs(){
	$tpl=new templates();
		$html="
<input type='hidden' id='t' value='$t'>
<p class='caption'>{emails_flow_text}</p>
<table style='width:600px' align=center>
<tr>
<td valign='top'>
	<center style='margin:4px'>
		<H5>{emails_flow_hour}</H5>
		<img src='images.listener.php?uri=mailgraph/mailgraph_0.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{emails_flow_day}</H5>
		<img src='images.listener.php?uri=mailgraph/mailgraph_1.png&md=$md'>
	</center>
	<center style='margin:4px'>
		<H5>{emails_flow_month}</H5>
		<img src='images.listener.php?uri=mailgraph/mailgraph_3.png&md=$md'>
	</center>					
</td>
</tr>
</table>	";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html,'statistics.mailgraph.php');
	
}
function MailGraphStatus(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadFile("ressources/logs/global.status.ini");
	$status=DAEMON_STATUS_ROUND("MAILGRAPH",$ini,null);
	return  $tpl->_ENGINE_parse_body($status);
	}	
	
?>