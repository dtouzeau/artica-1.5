<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');

	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["PopHackEnabled"])){save();exit;}
	js();
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{SQUATTER_SERVICE}');
	$page=CurrentPageName();

$html="

function ANTI_HACK_CYRUS_SERVICE(){
	YahooWin('600','$page?popup=yes','Anti-Hacks');
}

var x_ANTI_HACK_CYRUS_SAVE=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	ANTI_HACK_CYRUS_SERVICE();
    }

function ANTI_HACK_CYRUS_SAVE(){
		var XHR = new XHRConnection();
		XHR.appendData('PopHackEnabled',document.getElementById('PopHackEnabled').value);
		XHR.appendData('PopHackCount',document.getElementById('PopHackCount').value);
		document.getElementById('POP_HACK_ID').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_ANTI_HACK_CYRUS_SAVE);
	}
	


ANTI_HACK_CYRUS_SERVICE();";
	
	echo $html;
	
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("PopHackEnabled",$_GET["PopHackEnabled"]);
	$sock->SET_INFO("PopHackCount",$_GET["PopHackCount"]);
	$sock->getFrameWork("cmd.php?restart-artica-maillog=yes");
}


function popup(){
	
	$sock=new sockets();
	$PopHackEnabled=$sock->GET_INFO("PopHackEnabled");
	$PopHackCount=$sock->GET_INFO("PopHackCount");
	if($PopHackEnabled==null){$PopHackEnabled=1;}
	if($PopHackCount==null){$PopHackCount=10;}
	$POP_HACK=Paragraphe_switch_img('{POP_HACK_ARTICA}','{POP_HACK_ARTICA_TEXT}',"PopHackEnabled",$PopHackEnabled);
	
	$html="
	<div id=POP_HACK_ID>
	<p style='font-size:13px'>{ANI_HACK_CYRUS_EXPLAIN}</p>
	$POP_HACK
	<table>
	<tr>
		<td class='legend'>{events_number}</td>
		<td>". Field_text("PopHackCount",$PopHackCount,'width:45px;font-size:13px')."</td>
	</tr>
	</table>
	<hr>
	<div style='width:100%;text-align:right'>". button("{apply}","ANTI_HACK_CYRUS_SAVE()")."</div>
	</div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


?>