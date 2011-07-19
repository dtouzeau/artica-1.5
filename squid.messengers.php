<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');
	
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["AOL"])){Save();exit;}
	
js();



function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{instant_messengers}");
	$page=CurrentPageName();
	echo "YahooWin3('330','$page?popup=yes','$title');";
	
}

function popup(){
	
	$sock=new sockets();
	$SquidMessengers=unserialize(base64_decode($sock->GET_INFO("SquidMessengers")));
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<div id='messengers-div'>
	<div class=explain>{squid_instant_messengers_explain}</div>
	<table class=form>
	<tr>
		<td class=legend>AOL Instant Messenger:</td>
		<td>". Field_checkbox("AOL",1,$SquidMessengers["AOL"])."</td>
	</tr>
	<tr>
		<td class=legend>IRC:</td>
		<td>". Field_checkbox("IRC",1,$SquidMessengers["IRC"])."</td>
	</tr>	
	<tr>
		<td class=legend>Yahoo Messenger:</td>
		<td>". Field_checkbox("YAHOO",1,$SquidMessengers["YAHOO"])."</td>
	</tr>	
	<tr>
		<td class=legend>Google Talk:</td>
		<td>". Field_checkbox("GOOGLE",1,$SquidMessengers["GOOGLE"])."</td>
	</tr>	
	<tr>
		<td class=legend>MSN:</td>
		<td>". Field_checkbox("MSN",1,$SquidMessengers["MSN"])."</td>
	</tr>		
	<tr>
		<td colspan=2 align='right'><hR>". button("{apply}","SaveMessengersConf()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
		var x_SaveMessengersConf= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin3Hide();
			RefreshTab('squid_main_config');					
		}		
		
		function SaveMessengersConf(){
		 	var XHR = XHRParseElements('messengers-div');
		 	document.getElementById('messengers-div').innerHTML='<center><img src=img/wait_verybig.gif></center>';		
			XHR.sendAndLoad('$page', 'GET',x_SaveMessengersConf);	
		}
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function Save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"SquidMessengers");
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
}
		
		
