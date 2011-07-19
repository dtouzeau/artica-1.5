<?php

include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.rsync.inc");

$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["RsyncDaemonEnable"])){save();exit;}
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC_SERVER_ENABLE}');


$html="
	YahooWin4(500,'$page?popup=yes','$title')
	
	
var X_SaveRsyncEnable= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin4Hide();
	}		
	
	function SaveRsyncEnable(){
		var XHR = new XHRConnection();
		if(document.getElementById('RsyncDaemonEnable')){
			XHR.appendData('RsyncDaemonEnable',document.getElementById('RsyncDaemonEnable').value);
			document.getElementById('img_RsyncDaemonEnable').src='img/wait_verybig.gif';
		}
		
				XHR.sendAndLoad('$page', 'GET',X_SaveRsyncEnable);	
	
	}
	
	
	
	";
	
	echo $html;

}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("RsyncDaemonEnable",$_GET["RsyncDaemonEnable"]);
	$sock->getFrameWork("cmd.php?RestartRsyncServer=yes");
}


function popup(){
	
		$sock=new sockets();
		$RsyncDaemonEnable=$sock->GET_INFO("RsyncDaemonEnable");
		
		$enbale=Paragraphe_switch_img('{APP_RSYNC_SERVER_ENABLE}','{APP_RSYNC_SERVER_ENABLE_EXPLAIN}','RsyncDaemonEnable',$RsyncDaemonEnable);
		
		$html="
		$enbale
		<div style='width:100%;text-align:right'>
			". button("{apply}","SaveRsyncEnable()")."</div>
		
		";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		
	
}


?>