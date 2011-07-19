<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["start"])){restart();exit;}
	if(isset($_GET["logs"])){logs();exit;}
js();


function js(){
	
		$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_SQUID}::{restart_all_services}");
	$page=CurrentPageName();
	$html="
	
var tantS=0;


	function demarreSsquid(){
	
	   tantS = tantS+1;
	   if(!YahooWin3Open()){return;}
		if (tantS < 10 ) {                           
	     setTimeout(\"demarreSsquid()\",1000);
	      } else {
	               tantS = 0;
	               SquidChargeLogs();
	               demarreSsquid();
	   }
	}
	
		function squid_restart_proxy_load(){
			YahooWin3('600','$page?popup=yes','$title');
		
		}
		
	function SquidChargeLogs(){
		LoadAjax('squid-restart','$page?logs=yes');
	}
		
	squid_restart_proxy_load();";
	
	echo $html;
}


function popup(){
	$page=CurrentPageName();
	$html="
	<H3>{PLEASE_WAIT_RESTARTING_ALL_SERVICES}</H3>
	<div style='margin:5px;padding:3px;border:1px solid #CCCCCC;width:95%;height:450px;overflow:auto' id='squid-restart'>
	</div>
	
	<script>
		LoadAjax('squid-restart','$page?start=yes');
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function restart(){
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?force-restart-squid=yes");
	
	echo "
	<center><img src=\"img/wait_verybig.gif\"></center>
	<script>demarreSsquid();</script>";
	
	
}

function logs(){
	
	$f=explode("\n", @file_get_contents("ressources/logs/web/restart.squid"));
	while (list ($num, $val) = each ($f) ){
		echo "<div><code style='font-size:12px'>$val</code></div>";
	}
}




?>