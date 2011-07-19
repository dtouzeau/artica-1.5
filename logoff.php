<?php
ini_set('memory_limit', '64M');
session_start();


/*	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	*/

if(isset($_GET["shutdown-js"])){shutdown_js();exit;}


if(isset($_GET["menus"])){
	echo menus();
	exit;
}

if(isset($_GET["perform"])){
	perform();
	exit;
}


function perform(){
	include_once(dirname(__FILE__) . "/class.sockets.inc");
	include_once('ressources/class.templates.inc');
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){die();}
	
	$sock=new sockets();
	$DisableRebootOrShutDown=$sock->GET_INFO('DisableRebootOrShutDown');		
	if($DisableRebootOrShutDown==1){return;}
	
	if($_GET["perform"]=="reboot"){
		$sock->getFrameWork("cmd.php?system-reboot=yes");
	}
	
	if($_GET["perform"]=="shutdown"){
		$sock->getFrameWork("cmd.php?system-shutdown=yes");
	}	
}

function shutdown_js(){
	include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
	include_once(dirname(__FILE__) . "/ressources/class.users.menus.inc");
	include_once(dirname(__FILE__) . "/ressources/class.templates.inc");	
	$users=new usersMenus();
	$page=CurrentPageName();
	if(!$users->AsSystemAdministrator){die();}
	$tpl=new templates();
	$warn=$tpl->javascript_parse_text("{warn_shutdown_computer}");
	$html="
var x_turnoff= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				window.location ='$page';
				
			}	
	
	
	function turningoff(){
		if(confirm('$warn')){
			var XHR = new XHRConnection();
			XHR.appendData('perform','shutdown');
			XHR.sendAndLoad('$page', 'GET',x_turnoff);
		}
	}
	
	
	turningoff();
	";
	echo $html;
	
}


function menus(){
	include_once('ressources/class.templates.inc');
	if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return null;}
	
	
	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$html="
		<input type='hidden' id='isanuser' name ='isanuser' value='1'>
		<center><H2 style='color:red'>{logoff}</H2></center>
		";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		exit;}
	
	$sock=new sockets();
	$AllowShutDownByInterface=$sock->GET_INFO('AllowShutDownByInterface');
	$DisableRebootOrShutDown=$sock->GET_INFO('DisableRebootOrShutDown');		
		
	if($AllowShutDownByInterface==1){
		$AllowShutDownByInterface_tr="
		<td align='center'>
			".imgtootltip('shutdown-computer-64.png','{shutdown}',"Loadjs('logoff.php?shutdown-js=yes')")."
		</td>		
		";
	}
	$reboot=imgtootltip('reboot-computer-64.png','{restart_computer}','RestartComputer()');
	
	if($DisableRebootOrShutDown==1){
		$reboot=imgtootltip('reboot-computer-64-grey.png','{restart_computer}');
		if($AllowShutDownByInterface_tr<>null){
			$AllowShutDownByInterface_tr=
			"<td align='center'>".imgtootltip('shutdown-computer-64-grey.png','{shutdown}')."</td>";
		}
	}
	
	
	
	
	
	$html="
	<input type='hidden' id='shutdown_computer_text' value='{shutdown_computer_text}'>
	<input type='hidden' id='restart_computer_text' value='{restart_computer_text}'>
	<table style='width:100%'>
	<tr>
		<td align='center'>
			".imgtootltip('64-disconnect.png','{logoff}',"MyHref('logoff.php')")."
		</td>		
		<td align='center'>
			$reboot
		</td>
		$AllowShutDownByInterface_tr		
	</tr>
	</table>
	";
	$tpl=new templates();
	$page=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$page);
	echo $page;
}

unset($_SESSION["uid"]);
unset($_SESSION["privileges"]);
unset($_SESSION["qaliases"]);
unset($_SERVER['PHP_AUTH_USER']);
unset($_SESSION["ARTICA_HEAD_TEMPLATE"]);
unset($_SESSION['smartsieve']['authz']);
unset($_SESSION["passwd"]);
unset($_SESSION["LANG_FILES"]);
unset($_SESSION["TRANSLATE"]);
unset($_SESSION["__CLASS-USER-MENUS"]);
unset($_SESSION["translation"]);
$_COOKIE["username"]="";
$_COOKIE["password"]="";


while (list ($num, $ligne) = each ($_SESSION) ){
	unset($_SESSION[$num]);
}



echo "
<html>
<head>
<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=logon.php\"> 
	<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />

</head>
<body style='background-color:#005447;padding:100px'>
<center style='padding:15px;border:3px solid white;background-color:#005447'>
	<a style='font-size:22px;font-family:arial,tahoma;font-weight:bold;color:white' href='logon.php'>
	Waiting please, redirecting to logon page</a></center>
</body>
</html>




";
exit;
	

$html="
<center>
				<form>
				
				<div style='float:right;margin-right:65px;margin-top:60px'>
				<table >
				<tr>
				<td align='right'><strong>{username}:</strong></td>
				<td><input type='text' id='username' value='' style='border:1px solid black;width:130px' OnKeyPress=\"javascript:logon(event);\"></td>
				</tr>
				<tr>
				<td align='right'><strong>{password}:</strong></td>
				<td><input type='password'  id='password' value=''style='border:1px solid black;width:130px' OnKeyPress=\"javascript:logon(event)\"></td>
				</tr>	
				<tr>
				<td colspan=2 align='right' style='padding-right:10px'>
					<input type='button' OnClick=\"javascript:logon();\" value='{logon}&nbsp;&raquo;'>
				</td>
				</tr>
				</table>
				</div>
				
				

				
				
				</form>
				</div>
				</center>";

$tpl=new template_users('{disconnected}',$html,1,0,0,0);
echo $tpl->web_page;


?>