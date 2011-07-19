<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.donkey.inc');	

			
	if(!CheckRights){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["WebDavUser"])){SaveInfos();exit;}

	js();
	
function popup(){
	
	$uid=$_GET["uid"];
	$user=new user($uid);
	$sock=new sockets();
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	$field=Paragraphe_switch_disable("{ACTIVATE_THIS_USER_WEBDAV}","{APP_GROUPWARE_APACHE_DISABLED_TEXT}",null,330);
	$field=Paragraphe_switch_img("{ACTIVATE_THIS_USER_WEBDAV}","{ACTIVATE_THIS_USER_WEBDAV_TEXT}","WebDavUser",$user->WebDavUser,330);
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/webdav-128.png' id='webdav-image'></td>
	<td valing='top'>
	
	<table style='width:100%'>
	<tr>
		<td>$field</td>
	</tr>
	<tr>
	<tr>
		<td align='right'><hr>". button("{apply}","SaveWebDavEnabledUser()")."</td>
	</tr>
	</table>	
	</td>
	</tr>
	</table>
	
	";
				
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}	
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$uid=$_GET["uid"];
	$title=$tpl->_ENGINE_parse_body($uid.'::{USER_WEBDAV}');	
	
	
	
$html="

function user_webdav_load(){
	YahooWin4('600','$page?popup=yes&uid={$_GET["uid"]}','$title');
	}


function SaveWebDavEnabledUser(){
	var XHR = new XHRConnection();
	document.getElementById('webdav-image').src='img/wait_verybig.gif';
    XHR.appendData('WebDavUser',document.getElementById('WebDavUser').value);
    XHR.appendData('uid','{$_GET["uid"]}');
    XHR.sendAndLoad('$page', 'GET',x_SaveWebDavEnabledUser); 

}

	
var x_SaveWebDavEnabledUser= function (obj) {
	var results=trim(obj.responseText);
	if(results.length>1){alert('<'+results+'>');}
	document.getElementById('webdav-image').src='img/webdav-128.png';
	}
		


user_webdav_load();";	
	
	
echo $html;	
}	

function SaveInfos(){
	$users=new user($_GET["uid"]);
	$users->WebDavUser=$_GET["WebDavUser"];
	$users->SaveWebDav();
	
}
	
	
function CheckRights(){
	if(!$_GET["uid"]){return false;}
	$usersprivs=new usersMenus();
	if($usersprivs->AsAnAdministratorGeneric){return true;}
	if($usersprivs->AllowAddGroup){return true;}
	if($usersprivs->AllowAddUsers){return true;}
	return false;
}	

?>