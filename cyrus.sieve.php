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
	if(isset($_GET["SieveListenIp"])){save();exit;}

	js();
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{sieve_service}');
	$page=CurrentPageName();

$html="
function cyrus_sieve_load(){
	YahooWin('400','$page?popup=yes','$title');
}
	
var x_cyrus_sieve_save=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	YahooWinHide();
    }


function cyrus_sieve_save(){
	var XHR = new XHRConnection();
	XHR.appendData('SieveListenIp',document.getElementById('SieveListenIp').value);
	document.getElementById('sieveid').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_cyrus_sieve_save);
}

cyrus_sieve_load();";
	
	echo $html;
	
	
}


function popup(){
	
	$ip=new networking();
	$sock=new sockets();
	$SieveListenIp=$sock->GET_INFO("SieveListenIp");
	
	if(is_array($ip->array_TCP)){
		while (list ($num, $ligne) = each ($ip->array_TCP) ){
			if($ligne==null){continue;}
			$array[$ligne]="$num $ligne";
		}
		
	}
	
	$array["127.0.0.1"]="lo:127.0.0.1";
	
	if($SieveListenIp==null){$SieveListenIp="127.0.0.1";}
	$ips=Field_array_Hash($array,"SieveListenIp",$SieveListenIp,null,null,0,"font-size:14px;padding:3px");
	
	$html="
	<div id='sieveid'>
	<p style='font-size:14px'>{sieve_service_options_text}</p>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:14px'>{listen_ip}:</td>
		<td>$ips</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","cyrus_sieve_save()")."</td>
	</tr>
	</table>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function save(){
	
	$sock=new sockets();
	$sock->SET_INFO("SieveListenIp",$_GET["SieveListenIp"]);
	$sock->getFrameWork("cmd.php?cyrus-reconfigure=yes");
}


?>