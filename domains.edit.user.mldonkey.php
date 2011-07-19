<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.donkey.inc');	

			
	if(!CheckRights()){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["MldonkeyActivate"])){SaveInfos();exit;}

js();


function CheckRights(){
	if(!$_GET["uid"]){return false;}
	$usersprivs=new usersMenus();
	if($usersprivs->AsAnAdministratorGeneric){return true;}
	if($usersprivs->AllowAddGroup){return true;}
	if($usersprivs->AllowAddUsers){return true;}
	return false;
}

function popup(){
	$emule=new EmuleTelnet();
	$uid=$_GET["uid"];
	if(!$emule->ok){
		echo "<center style='font-size:16px;color:red'>$emule->errstr</center>";
		return;
	}
	
	if(!$emule->UserIsActivated($uid)){$enabled=0;}else{$enabled=1;}
	$max_downloads=$emule->HASH_USERS[$uid]["MAX_DOWNLOADS"];
	
	$field=Paragraphe_switch_img("{ACTIVATE_THIS_MLDONKEY_USER}","{ACTIVATE_THIS_MLDONKEY_USER_TEXT}","MldonkeyActivate",$enabled,330);
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/128-emule.png' id='emule-image'></td>
	<td valing='top'>
	
	<table style='width:100%'>
	<tr>
		<td>$field</td>
	</tr>
	<tr>
	<td>
		<table>
			<tr>
				<td class=legend style='font-size:13px'>{max_downloads}:</td>
				<td>". Field_text("max_downloads",$max_downloads,"font-size:13px;padding:3px;width:60px")."</td>
			</tr>
		</table>
	</td>
	<tr>
		<td align='right'><hr>". button("{apply}","SaveeMuleEnabledUser()")."</td>
	</tr>
	</table>	
	</td>
	</tr>
	</table>
	
	";
				
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function SaveInfos(){
	$emule=new EmuleTelnet();
	$uid=$_GET["uid"];
	if(!$emule->ok){echo $emule->errstr;return;}
	
	$MldonkeyActivate=$_GET["MldonkeyActivate"];
	unset($_SESSION["MLDONKEY_$uid"]);
	if($MldonkeyActivate==0){
		$emule->UserDelete($uid);
		return;
	}else{
		$emule->UserAdd($uid,$_GET["max_downloads"]);
	}
	
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$uid=$_GET["uid"];
	$title=$tpl->_ENGINE_parse_body($uid.'::{MLDONKEY_USER}');	
	
	
	
$html="

function user_emule_load(){
	YahooWin4('600','$page?popup=yes&uid={$_GET["uid"]}','$title');
	}


function SaveeMuleEnabledUser(){
	var XHR = new XHRConnection();
	document.getElementById('emule-image').src='img/wait_verybig.gif';
    XHR.appendData('MldonkeyActivate',document.getElementById('MldonkeyActivate').value);
    XHR.appendData('max_downloads',document.getElementById('max_downloads').value);
    XHR.appendData('uid','{$_GET["uid"]}');
    XHR.sendAndLoad('$page', 'GET',x_SaveeMuleEnabledUser); 

}

	
var x_SaveeMuleEnabledUser= function (obj) {
	var results=trim(obj.responseText);
	if(results.length>1){alert('<'+results+'>');}
	document.getElementById('emule-image').src='img/128-emule.png';
	}
		


user_emule_load();";	
	
	
echo $html;	
}

?>