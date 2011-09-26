<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');


	
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["EnableWebProxyStatsAppliance"])){Save();exit;}
	
js();

function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{statistics_appliance}");
	echo "YahooWin4('400','$page?popup=yes','$title')";
}

function Save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableWebProxyStatsAppliance",$_POST["EnableWebProxyStatsAppliance"]);
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$EnableWebProxyStatsAppliance=$sock->GET_INFO("EnableWebProxyStatsAppliance");
	if(!is_numeric($EnableWebProxyStatsAppliance)){$EnableWebProxyStatsAppliance=0;}
	$html=
	"<div id='stats-appliance-div'>".
	
	Paragraphe_switch_img("{webproxy_statistics_appliance}","{webproxy_statistics_appliance_text}","EnableWebProxyStatsAppliance",$EnableWebProxyStatsAppliance)."
	<hr>
	<div style='text-align:right'>". button("{apply}","SaveStatsApps()")."</div>
	
	</div>
	
	<script>
	
		var x_SaveStatsApps=function(obj){
     		var tempvalue=obj.responseText;
      		if(tempvalue.length>3){alert(tempvalue);}
      		YahooWin4Hide();
     
     	}	
      


	function SaveStatsApps(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableWebProxyStatsAppliance',document.getElementById('EnableWebProxyStatsAppliance').value);
			AnimateDiv('stats-appliance-div');
			XHR.sendAndLoad('$page', 'POST',x_SaveStatsApps);		
			
	}

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}