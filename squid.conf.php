<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');

	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_SQUID}::{configuration_file}");
	
	$html="
		YahooWin4('700','$page?popup=yes','$title');
	";
	echo $html;
	
	
}


function popup(){
	
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?squid-conf-view=yes"));
	
	
	$html="<textarea style='width:100%;height:450px;overflow:auto;border:1px solid #CCCCCC;font-size:12px;padding:3px'>$datas</textarea>";
	echo $html;
	
}

?>