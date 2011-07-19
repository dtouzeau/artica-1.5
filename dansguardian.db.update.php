<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["refresh"])){refresh();exit;}
	
js();



function js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{DANSGUARDIAN_BLACKLISTS_UPDATE}');

$html="
	var DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE_TANT=0;

	function DANSGUARDIAN_BLACKLISTS_UPDATE(){
	YahooWin2(770,'$page?popup=yes','$title',''); 
	}	
	
	function DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE(){
		if(!document.getElementById('DANSGUARDIAN_BLACKLISTS_UPDATE')){return;}
		if(!YahooWin2Open()){document.getElementById('DANSGUARDIAN_BLACKLISTS_UPDATE').innerHTML='<H3>END</H3>';return;}
		 DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE_TANT = DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE_TANT+1;
		
		 if (DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE_TANT < 5 ) {                           
			setTimeout(\"DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE()\",1000);
			return;
		}
		      
				DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE_TANT = 0;
				DANSGUARDIAN_BLACKLISTS_UPDATE_REFRESH();
				DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE();
		   
	}
	
var x_DANSGUARDIAN_BLACKLISTS_UPDATE_REFRESH= function (obj) {
	var tempvalue=obj.responseText;
		if(tempvalue.length>0){
    		document.getElementById('DANSGUARDIAN_BLACKLISTS_UPDATE').innerHTML=tempvalue;
		}
	}		

	function DANSGUARDIAN_BLACKLISTS_UPDATE_REFRESH(){
		var XHR = new XHRConnection();
		XHR.appendData('refresh','yes');
		XHR.sendAndLoad('$page', 'GET',x_DANSGUARDIAN_BLACKLISTS_UPDATE_REFRESH);
	}
	
	
	DANSGUARDIAN_BLACKLISTS_UPDATE();";
	
	echo $html;
	
}


function popup(){
	
	$html="
	<div id='DANSGUARDIAN_BLACKLISTS_UPDATE' style='width:100%;height:350px;overflow:auto'></div>
	
	<script>
		DANSGUARDIAN_BLACKLISTS_UPDATE_REFRESH();
		DANSGUARDIAN_BLACKLISTS_UPDATE_ROTATE();
	</script>
	";
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?dansguardian-update=yes");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function refresh(){
	$tpl=new templates();
	if(!is_file("/usr/share/artica-postfix/ressources/logs/DANSUPDATE")){return null;}
	$d=explode("\n",@file_get_contents("/usr/share/artica-postfix/ressources/logs/DANSUPDATE"));
	while (list ($num, $line) = each ($d) ){
		echo $tpl->_ENGINE_parse_body("<div><code style='font-size:11px'>$line</code></div>");
	}
	
}


?>