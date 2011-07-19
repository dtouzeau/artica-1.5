<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["uri"])){test();exit;}
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squidguard_testrules}");
	$html="
	
	function squidguard_tests_rules_start(){
		YahooWin3(650,'$page?popup=yes','$title');
	
	}
	
	var x_squidguard_tests_rules_perfom= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('squidguard_tests_rules_div').innerHTML=tempvalue;
	}		
	
	function squidguard_tests_rules_perfom(){
			var XHR = new XHRConnection();
			XHR.appendData('uri',document.getElementById('uri').value);
			XHR.appendData('client',document.getElementById('client').value);
			document.getElementById('squidguard_tests_rules_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_squidguard_tests_rules_perfom); 
	}
	
	function squidguard_tests_rules_press(e){
		if(checkEnter(e)){	squidguard_tests_rules_perfom();}
	}
	
	squidguard_tests_rules_start();";
	
	echo $html;
	
}

function popup(){
	
	$html="
	<p style='font-size:13px;margin:5px'>{squidguard_testrules_explain}</p>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:14px'>{uri_test}:</td>
		<td>". Field_text("uri",null,'width:100%;font-size:14px;padding:3px',null,null,null,false,"squidguard_tests_rules_press(event)")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{usernanmeorip}:</td>
		<td>". Field_text("client",null,'width:100%;font-size:14px;padding:3px',null,null,null,false,"squidguard_tests_rules_press(event)")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{test}","squidguard_tests_rules_perfom()")."</td>
	</tr>
	</table>
	<div id='squidguard_tests_rules_div' style='height:300px;overflow:auto'></div>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function test(){
	$uri=base64_encode($_GET["uri"]);
	$client=base64_encode($_GET["client"]);
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?squidguard-tests=yes&uri=$uri&client=$client")));
	if(!is_array($datas)){
		echo "<H1>???</H1>";exit;
	}
	
	rsort($datas);
	while (list ($num, $val) = each ($datas) ){
		if(preg_match("#source not found#",$val)){$error="{user_not_found}";}
		$html=$html. "<div style='font-size:11px'><code>$val</code></div>\n";
		
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<H2>$error</H2>$html");
	
}

?>
