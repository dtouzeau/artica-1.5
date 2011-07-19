<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["enforce-https-with-hostname"])){save_ssl();exit;}
	
	
	js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_UFDBGUARD}");
	$html="YahooWin3('600','$page?popup=yes','$title');";
	echo $html;
	}
	
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ufdbguardConfig")));
	if($datas["enforce-https-with-hostname"]==null){$datas["enforce-https-with-hostname"]=0;}
	if($datas["enforce-https-official-certificate"]==null){$datas["enforce-https-official-certificate"]=0;}
	if($datas["https-prohibit-insecure-sslv2"]==null){$datas["https-prohibit-insecure-sslv2"]=0;}
	
	
	$html="
	<div id='GuardSSL'>
	<table style='width:100%'>
	<tr>
	<td colspan=2><span style='font-size:16px'>{ssl}:</span>
	</tr>
	<tr>
	<td colspan=2><div class=explain>{UFDBGUARD_SSL_OPTS}</div></td>
	</tr>	
	<tr>
	<td class=legend>{enforce-https-with-hostname}:</td>
	<td>". Field_checkbox("enforce-https-with-hostname",1,$datas["enforce-https-with-hostname"])."</td>
	</tr>
	<tr>
	<td class=legend>{enforce-https-official-certificate}:</td>
	<td>". Field_checkbox("enforce-https-official-certificate",1,$datas["enforce-https-official-certificate"])."</td>
	</tr>
	<tr>
	<td class=legend>{https-prohibit-insecure-sslv2}:</td>
	<td>". Field_checkbox("https-prohibit-insecure-sslv2",1,$datas["https-prohibit-insecure-sslv2"])."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveufdbGuardSSL()")."</td>
	</tr>	
	</table>
	</div>
	<script>
	var x_SaveufdbGuardSSLl=function (obj) {
		YahooWin3Hide();
	}		
	
	function SaveufdbGuardSSL(){
		var XHR = new XHRConnection();
		if(document.getElementById('enforce-https-with-hostname').checked){
    		XHR.appendData('enforce-https-with-hostname',1);}else{
    		XHR.appendData('enforce-https-with-hostname',0);}

		if(document.getElementById('enforce-https-official-certificate').checked){
    		XHR.appendData('enforce-https-official-certificate',1);}else{
    		XHR.appendData('enforce-https-official-certificate',0);}    		

		if(document.getElementById('https-prohibit-insecure-sslv2').checked){
    		XHR.appendData('https-prohibit-insecure-sslv2',1);}else{
    		XHR.appendData('https-prohibit-insecure-sslv2',0);}     		
    		
 		document.getElementById('GuardSSL').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveufdbGuardSSLl);
	}	
	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function save_ssl(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("ufdbguardConfig")));
	while (list ($key, $line) = each ($_GET) ){
		$datas[$key]=$line;
		
	}
	$sock->SaveConfigFile(base64_encode(serialize($datas)),"ufdbguardConfig");
	$sock->getFrameWork("cmd.php?reload-squidguard=yes");
}

