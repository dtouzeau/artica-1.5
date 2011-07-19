<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.dhcpd.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["ev"])){echo events();exit;}

js();
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$prefix=str_replace(".","_",$page);
	$title=$tpl->_ENGINE_parse_body('{APP_DHCP_EVENTS}','index.gateway.php');
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;
	
	function {$prefix}load(){
		RTMMail(650,'$page?popup=yes','$title');
		setTimeout(\"{$prefix}demarre()\",1000);
	}

		function {$prefix}demarre(){
			if(!RTMMailOpen()){return;}
			{$prefix}tant = {$prefix}tant+1;
			{$prefix}reste=10-{$prefix}tant;
			if ({$prefix}tant < 10 ) {                           //exemple:caler a une minute (60*1000) 
				{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
		   		} else {
						if(!RTMMailOpen()){return;}
					   {$prefix}tant = 0;
					   {$prefix}Chargelogs();
					   {$prefix}demarre();                                //la boucle demarre !
		   }
		}
		
		function {$prefix}Chargelogs(){
			LoadAjax('DHCPDL','$page?ev=yes');
		
		}
		
var x_{$prefix}Chargelogs= function (obj) {
	var tempvalue=obj.responseText;
	document.getElementById('DHCPDL').innerHTML=tempvalue;
	}

function {$prefix}Chargelogs(){
		var XHR = new XHRConnection();
		 XHR.appendData('ev','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}Chargelogs);
}		
		
		
	{$prefix}load()";
	
	echo $html;
}

function popup(){
	$tpl=new templates();
	$logs=events();
	$html="
	<H1>{APP_DHCP_EVENTS}</H1>
	". RoundedLightWhite("
	<div style='width:100%;height:350px;overflow:auto' id='DHCPDL'>$logs</div>");
	
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function events(){
	$sock=new sockets();
	$datas=$sock->getfile("DHCPDLogs");
	
		$tbl=explode("\n",$datas);
		if(!is_array($tbl)){return null;}
		$tbl=array_reverse ($tbl, TRUE);	
		$html="<table style='width:99%'>";	
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)==null){continue;}
				
			$html=$html . "
			
			
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><code style='font-size:10px'>". htmlentities($val)."</code></td>
			</tr>
			
			";
			
		}	
		
		return  $html."</table>";	
	
}



?>