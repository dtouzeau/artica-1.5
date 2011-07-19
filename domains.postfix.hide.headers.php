<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
	
	if(!PostFixMultiVerifyRights()){
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

function HIDE_CLIENT_MULTI_MUA_LOAD(){
	YahooWin3('400','$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
	
	}
	
HIDE_CLIENT_MULTI_MUA_LOAD();
";


echo $html;	
	
}

function popup(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$tpl=new templates();
	$PostfixHideClientMua=$main->GET("PostfixHideClientMua");
	
	$enable=Paragraphe_switch_img("{ENABLE_HIDE_CLIENT_MUA}","{HIDE_CLIENT_MUA_TEXT}","PostfixHideClientMua",$PostfixHideClientMua,null,330);

	$html="
	<div id='PostfixHideClientMuaDiv'>
		$enable
	<hr>
		<div style='text-align:right'>". button("{apply}","PostfixHideClientMuaMultiSave()")."</div>	
		
	</div>
	
	<script>
var x_PostfixHideClientMua= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	Loadjs('$page?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}');
	}
	
	
	function PostfixHideClientMuaMultiSave(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixHideClientMua',document.getElementById('PostfixHideClientMua').value);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.sendAndLoad('$page', 'GET',x_PostfixHideClientMua);
	
	}		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function SAVE(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);	
	$main->SET_VALUE("PostfixHideClientMua",$_GET["PostfixHideClientMua"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");	
	
}
	