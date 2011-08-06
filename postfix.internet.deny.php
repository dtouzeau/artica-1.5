<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["EnableBlockUsersTroughInternet"])){save();exit;}

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	$title=$tpl->_ENGINE_parse_body('{INTERNET_DENY}',"postfix.index.php");
	
	$normal="YahooWin(500,'$page?popup=yes','$title');";
	
	
	$html="
	
	function StartInternetDeny(){
		$normal
	}
	
	
	
var x_EnableBlockUsersTroughInternetSave= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	StartInternetDeny();
}
	
	function EnableBlockUsersTroughInternetSave(){
		var EnableBlockUsersTroughInternet=document.getElementById('EnableBlockUsersTroughInternet').value;
		document.getElementById('EnableBlockUsersTroughInternetDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('EnableBlockUsersTroughInternet',EnableBlockUsersTroughInternet);
		XHR.sendAndLoad('$page', 'GET',x_EnableBlockUsersTroughInternetSave);	
	
	}
	
	StartInternetDeny();
	";
	echo $html;
	}
	
	
function popup(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	
	$sock=new sockets();
	$EnableBlockUsersTroughInternet=$sock->GET_INFO("EnableBlockUsersTroughInternet");
	$form=Paragraphe_switch_img("{ENABLE_INTERNET_DENY}","{ENABLE_INTERNET_DENY_TEXT}",'EnableBlockUsersTroughInternet',$EnableBlockUsersTroughInternet,"{enable_disable}",400);
	
	$html="
	<div id='EnableBlockUsersTroughInternetDiv'>
	$form
	<div style='width:100%;text-align:right'>
	<hr>
		<input type='button' OnClick=\"javascript:EnableBlockUsersTroughInternetSave()\" value='{edit}&nbsp;&raquo;&raquo;'>
	</div>
	</div>";
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO('EnableBlockUsersTroughInternet',$_GET["EnableBlockUsersTroughInternet"]);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}




?>