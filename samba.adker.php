<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["settings"])){settings();exit;}
	if(isset($_POST["EnableKerbAuth"])){settingsSave();exit;}
	if(isset($_GET["kerbchkconf"])){kerbchkconf();exit;}
	if(isset($_GET["js"])){js();exit;}

	popup();
	

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_SAMBAKERAUTH}");
	$html="YahooWin4(550,'$page?popup=yes','$title');";
	echo $html;
	}
	
function popup(){
$page=CurrentPageName();
	$html="<div id='serverkerb-popup'></div>
	
	<script>
	function RefreshServerKerb(){
		LoadAjax('serverkerb-popup','squid.adker.php?settings=yes&switch-template=samba');
		}
	
		RefreshServerKerb();
	</script>
	";
		
echo $html;		
}	
	
