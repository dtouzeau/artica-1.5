<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	

	
	if(posix_getuid()<>0){
	$users=new usersMenus();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SaveCyrusPassword"])){SaveCyrusPassword();exit;}
	js();
	
function js(){
		
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{cyrus password}');
	$prefix=str_replace('.','_',$page);
	$html="
	function {$prefix}LoadMainPage(){
		YahooWin3('550','$page?popup=yes','$title');
		
		}
		
	var x_SaveCyrusPassword= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}	
		{$prefix}LoadMainPage();
		}

	
	function SaveCyrusPassword(){
			var XHR = new XHRConnection();
			XHR.appendData('SaveCyrusPassword',document.getElementById('cyruspassword').value);
			document.getElementById('change_cyrus_password').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveCyrusPassword);
		}
		
	{$prefix}LoadMainPage();
	";
	
	echo $html;
}

function popup(){
	
	
	$ldap=new clladp();
	$cyruspass=$ldap->CyrusPassword();
	
	$html="<H1>{cyrus password}</H1>
	<div id='change_cyrus_password'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/cyrus-password-120.png'></td>
		<td valign='top'><p class=caption>{change_cyrus_password}</p>
		<br>" . RoundedLightWhite("
			<table style='width:99%'>
			<tr>
				<td class=legend>{password}:</td>
				<td>" . Field_password('cyruspassword',$cyruspass)."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr><input type='button' Onclick=\"javascript:SaveCyrusPassword();\" value='{edit}&nbsp;'></td>
			</tr>
		</table>")."
		</td>
		</tr>
		</table></div>";
	
	$tp=new templates();
	echo $tp->_ENGINE_parse_body($html,'cyrus.index.php');
	
}

function SaveCyrusPassword(){
	$ldap=new clladp();
	if($_GET["SaveCyrusPassword"]==null){return null;}
	
	if(strpos($_GET["SaveCyrusPassword"],'@')>0){
		echo "@: denied character\n";
		return;
	}
	if(strpos($_GET["SaveCyrusPassword"],':')>0){
		echo "@: denied character\n";
		return;
	}	
	
	$attrs["userPassword"][0]=$_GET["SaveCyrusPassword"];
	$dn="cn=cyrus,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		if(!$ldap->Ldap_modify($dn,$attrs)){echo $ldap->ldap_last_error;exit;}
	}
	
	$dn="cn=cyrus,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		if(!$ldap->Ldap_modify($dn,$attrs)){echo $ldap->ldap_last_error;exit;}
	}	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cyrus-change-password=".base64_encode($_GET["SaveCyrusPassword"]));
	
	
}
 
	

?>