<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	$user=new usersMenus();
	if($user->AsMailBoxAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["zarafaMigrate"])){zarafaMigrate();exit;}
	js();
	
	
	
function js(){
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{CYRUS_TO_ZARAFA}');
$ou_decrypted=base64_decode($_GET["ou"]);
$html="

function ZARAFA_OU_MIGRLOAD(){
	YahooWin3('400','$page?popup=yes&ou=$ou_decrypted','$title');
	
	}
	
var X_ZARAFA_MIGRATE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin3Hide();
	}	
	
function ZARAFA_MIGRATE(){
	var XHR = new XHRConnection();
	XHR.appendData('zarafaMigrate','yes');
	XHR.appendData('ou','$ou_decrypted');
	document.getElementById('migrbutt').innerHTML='<img src=\"img/wait_verybig.gif\">';
	XHR.sendAndLoad('$page', 'GET',X_ZARAFA_MIGRATE);	
}
	
ZARAFA_OU_MIGRLOAD();
";

echo $html;	
	
}


function popup(){
	
	$html="
	<p style='font-size:14px'>{CYRUS_TO_ZARAFA_EXPLAIN}</p>
	<center>
		<div id='migrbutt'>". button("{start_migration}","ZARAFA_MIGRATE()")."</div>
	
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function zarafaMigrate(){
	$tpl=new templates();
	$_GET["ou"]=base64_encode($_GET["ou"]);
	echo $tpl->javascript_parse_text("{PROCESS_STARTED_SEE_ARTICA_EVENTS_LOGS_MAILBOX}");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?zarafa-migrate={$_GET["ou"]}");	
	
}

	
?>