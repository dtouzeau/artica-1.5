<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["PostfixHideClientMua"])){SAVE();exit;}	

	
	
js();


function js(){
	
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{HIDE_CLIENT_MUA}');

$html="

function HIDE_CLIENT_MUA_LOAD(){
	YahooWin3('400','$page?popup=yes','$title');
	
	}
	
HIDE_CLIENT_MUA_LOAD();
";


echo $html;	
	
}

function popup(){
	
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$PostfixHideClientMua=$sock->GET_INFO("PostfixHideClientMua");
	
	$enable=Paragraphe_switch_img("{ENABLE_HIDE_CLIENT_MUA}","{HIDE_CLIENT_MUA_TEXT}","PostfixHideClientMua",$PostfixHideClientMua,null,330);

	$html="
	<div id='PostfixHideClientMuaDiv'>
		$enable
	<hr>
		<div style='text-align:right'>". button("{apply}","PostfixHideClientMuaSave()")."</div>	
		
	</div>
	
	<script>
var x_PostfixHideClientMua= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	Loadjs('$page');
	}
	
	
	function PostfixHideClientMuaSave(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixHideClientMua',document.getElementById('PostfixHideClientMua').value);
		XHR.sendAndLoad('$page', 'GET',x_PostfixHideClientMua);
	
	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("PostfixHideClientMua",$_GET["PostfixHideClientMua"]);
	$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
	
}
	