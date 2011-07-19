<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');
	$user=new usersMenus();
	
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SpamAssassinVirusBounceEnabled"])){SAVE();exit;}
	
	
	
	js();
	
function js(){

$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{Virus_Bounce_Ruleset}");
	
	
	$html="
	
		function Virus_Bounce_Ruleset_load(){
			YahooWin3(550,'$page?popup=yes','$title');
		
		}
		
var X_SpamAssassinVirusBounceEnabledSave= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		Virus_Bounce_Ruleset_load();
	}		
function SpamAssassinVirusBounceEnabledSave(){
		var XHR = new XHRConnection();
		XHR.appendData('SpamAssassinVirusBounceEnabled',document.getElementById('SpamAssassinVirusBounceEnabled').value);
		document.getElementById('SpamAssassinVirusBounceEnabledDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_SpamAssassinVirusBounceEnabledSave);
		}		
		
		
		Virus_Bounce_Ruleset_load();";
		
	echo $html;
	
}

function popup(){
	
	$sock=new sockets();
	$enable=$sock->GET_INFO("SpamAssassinVirusBounceEnabled");
	
	$penabled=Paragraphe_switch_img("{SpamAssassinVirusBounceEnabled}","{Virus_Bounce_Ruleset_text}<br>{SpamAssassinVirusBounceEnabled_text}"
	,"SpamAssassinVirusBounceEnabled",$enable,"{enable_disable}","500");
	
	$html="
	<div id='SpamAssassinVirusBounceEnabledDiv'>
	$penabled
	<div style='text-align:right'>
	". button("{apply}","SpamAssassinVirusBounceEnabledSave()")."</div>
	</div>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
function SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("SpamAssassinVirusBounceEnabled",$_GET["SpamAssassinVirusBounceEnabled"]);
	$sock->getFrameWork("cmd.php?SpamAssassin-Reload=yes");	
	
	
}

?>