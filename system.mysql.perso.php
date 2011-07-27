<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.os.system.inc');
	
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["mysqld-perso"])){save();exit;}
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->javascript_parse_text("{mysql_perso_conf}");
	
	$html="YahooWin4('550','$page?popup=yes','$title');";
	echo $html;
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	
	$perso=base64_decode($sock->getFrameWork("services.php?mysqld-perso=yes"));
	
	
	
	$html="
	<span id='mysqld-animate'></span>
	<div class=explain>{mysql_perso_conf_text}</div>
	<div style='font-size:14px;font-weight:bolder;margin-bottom:10px;font-family:Courier New, Courier, Prestige, monospace;'>[mysqld]</div>
	<center>
	<textarea id='mysqld-perso' style='height:350px;width:450px;overflow:auto;font-size:14px;font-family:Courier New, Courier, Prestige, monospace;color:black'>$perso</textarea>
	</center>
	<div style='text-align:right'><hr>". button("{apply}","SaveMysqldPerso()")."</div>
	
	<script>
	var x_SaveMysqldPerso= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		document.getElementById('mysqld-animate').innerHTML='';
		Loadjs('$page');
		if(document.getElementById('main_config_mysql')){RefreshTab('main_config_mysql');}
	}		
	
	function SaveMysqldPerso(){
			var XHR = new XHRConnection();
			XHR.appendData('mysqld-perso',document.getElementById('mysqld-perso').value);
			AnimateDiv('mysqld-animate');
			XHR.sendAndLoad('$page', 'POST',x_SaveMysqldPerso);
	}	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function save(){
	
	$datas=base64_encode($_POST["mysqld-perso"]);
	$sock=new sockets();
	$sock->getFrameWork("services.php?mysqld-perso-save=".urlencode($datas));
	
	
	
}