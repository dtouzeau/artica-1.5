<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dansguardian.inc');
	include_once('ressources/class.squid.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "<H1>".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."</H1>";
		exit;
		
	}
	
	if(isset($_POST["DansGuardianHTMLTemplate"])){save();}
	
	$sock=new sockets();
	$DansGuardianHTMLTemplate=$sock->GET_INFO("DansGuardianHTMLTemplate");	
	if(strlen($DansGuardianHTMLTemplate)<50){
		$DansGuardianHTMLTemplate=$sock->getFrameWork("cmd.php?dansguardian-get-template=yes");
	}
	
	
	$tpl=new templates();
	$button=$tpl->_ENGINE_parse_body("<input type='submit' value='{apply}'>");
	$tiny=TinyMce('DansGuardianHTMLTemplate',$DansGuardianHTMLTemplate);
	$html="
	<html>
	<head>
	<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
	<script type='text/javascript' language='JavaScript' src='mouse.js'></script>
	<script type='text/javascript' language='javascript' src='XHRConnection.js'></script>
	<script type='text/javascript' language='javascript' src='default.js'></script>
		
	</head>
	<body width=100%> 
	<form name='FFM1' METHOD=POST>
	<div style='text-align:center;width:100%;background-color:white;margin-bottom:10px;padding:5px;'>$squidguard_form$button<br></div>
	<center>
	<div style='width:750px;height:900px'>$tiny</div>
	</center>
	<div style='text-align:center;width:100%;background-color:white;maring-top:10px'>$button</div>
	
	</form>
	</body>
	
	</html>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
function save(){
$sock=new sockets();
$_POST["DansGuardianHTMLTemplate"]=stripslashes($_POST["DansGuardianHTMLTemplate"]);
$sock->SaveConfigFile($_POST["DansGuardianHTMLTemplate"],"DansGuardianHTMLTemplate");
$sock->getFrameWork("cmd.php?dansguardian-template=yes");
}

?>