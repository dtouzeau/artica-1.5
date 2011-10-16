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
	if(isset($_GET["zarafaEnabled"])){zarafaEnabled();exit;}
	js();
	
	
	
function js(){
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_ZARAFA}');
$ou_decrypted=base64_decode($_GET["ou"]);
$html="

function ZARAFA_OU_LOAD(){
	YahooWin3('415','$page?popup=yes&ou=$ou_decrypted','$title');
	
	}
	
var X_ENABLE_ZARAFA_COMPANY= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	if(document.getElementById('organization-find')){SearchOrgs();YahooWin3Hide();return;}
	ZARAFA_OU_LOAD();
	
	}	
	
function ENABLE_ZARAFA_COMPANY(){
	var XHR = new XHRConnection();
	XHR.appendData('zarafaEnabled',document.getElementById('zarafaEnabled').value);
	XHR.appendData('ou','$ou_decrypted');
	document.getElementById('img_zarafaEnabled').src='img/wait_verybig.gif';
	XHR.sendAndLoad('$page', 'GET',X_ENABLE_ZARAFA_COMPANY);	
}
	
ZARAFA_OU_LOAD();
";

echo $html;	
	
}


function popup(){
	
	$ldap=new clladp();
	$sock=new sockets();
	$info=$ldap->OUDatas($_GET["ou"]);
	$zarafaEnabled=1;
	if(!$info["objectClass"]["zarafa-company"]){$zarafaEnabled=0;}
	$ZarafaUserSafeMode=$sock->GET_INFO("ZarafaUserSafeMode");
	
	
		if($ZarafaUserSafeMode==1){
			$warn="
			<hr>
			<table style='width:100%'>
			<tr>
			<td valign='top'><img src='img/error-64.png'></td>
			<td valign='top'><strong style='font-size:14px'>{ZARAFA_SAFEMODE_EXPLAIN}</td>
			</tr>
			</table>
			
			";
		}

	$enable=Paragraphe_switch_img("{ENABLE_ZARAFA_COMPANY}","{ENABLE_ZARAFA_COMPANY_TEXT}","zarafaEnabled",$zarafaEnabled,null,400);
	
	$html="
	$enable
	<hr>
		<div style='text-align:right'>". button("{apply}","ENABLE_ZARAFA_COMPANY()")."</div>
	$warn
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function zarafaEnabled(){
	$ldap=new clladp();
	$dn="ou={$_GET["ou"]},dc=organizations,$ldap->suffix";
	$upd["objectClass"]="zarafa-company";
	$upd["cn"]=$_GET["ou"];
	if($_GET["zarafaEnabled"]==1){
		if(!$ldap->Ldap_add_mod("$dn",$upd)){
			echo $ldap->ldap_last_error;
			return;
		}
	}else{
	if(!$ldap->Ldap_del_mod("$dn",$upd)){
			echo $ldap->ldap_last_error;
			return;
		}
	}
	
$sock=new sockets();
$sock->getFrameWork("cmd.php?zarafa-admin=yes");	
	
}

	
?>