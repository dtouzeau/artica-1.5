<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.kav4samba.inc');
	
	
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;die();
	}
	if(isset($_GET["SambaRoamingEnabled"])){SambaRoamingEnabled();exit;}
	if(isset($_GET["popup"])){popup();exit;}

	js();
	
	

function js(){
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{roaming_profiles}','fileshares.index.php');
	
	
$html="
	var {$prefix}timeout=0;
	


	function {$prefix}LoadPage(){
		YahooWin2(450,'$page?popup=yes','$title');
	
	}
	
var X_EnableProfileSamba= function (obj) {
	var results=trim(obj.responseText);
	if(results.length>0){alert(results);}
	{$prefix}LoadPage();
	}	
	
	function EnableProfileSamba(){
		var XHR = new XHRConnection();
		XHR.appendData('SambaRoamingEnabled',document.getElementById('SambaRoamingEnabled').value);
		document.getElementById('roamingdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_EnableProfileSamba);	
	}
	
	{$prefix}LoadPage();
";	

	echo $html;
	
}

function popup(){
	
	$sock=new sockets();
	$SambaRoamingEnabled=$sock->GET_INFO('SambaRoamingEnabled');
	$enable=Paragraphe_switch_img('{enable_roaming}','{enable_roaming_text}','SambaRoamingEnabled',$SambaRoamingEnabled);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/logon-profiles-128.png'></td>
		<td valign='top'><div class=explain>{roaming_profiles_text}</div>
		<div id='roamingdiv'>
		$enable
		<div style='text-align:right'><hr>". button("{apply}","EnableProfileSamba()")."</div>
		</div>
		</td>
	</tr>
	</table>";
		
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'fileshares.index.php');	
	
	
}

function SambaRoamingEnabled(){
	
	$sock=new sockets();
	$sock->SET_INFO('SambaRoamingEnabled',$_GET["SambaRoamingEnabled"]);
	$samba=new samba();
	$samba->SaveToLdap();
}
	
	
	

?>