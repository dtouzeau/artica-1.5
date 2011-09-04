<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
	
	if(isset($_POST["ISOCanChangeLanguage"])){save();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{MENU_CONSOLE}");
	$html="YahooWin3('550','$page?popup=yes','ISO:$title');";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$ISOCanDisplayUserNamePassword=$sock->GET_INFO("ISOCanDisplayUserNamePassword");
	$ISOCanChangeIP=$sock->GET_INFO("ISOCanChangeIP");
	$ISOCanReboot=$sock->GET_INFO("ISOCanReboot");
	$ISOCanShutDown=$sock->GET_INFO("ISOCanShutDown");
	$ISOCanChangeRootPWD=$sock->GET_INFO("ISOCanChangeRootPWD");
	$ISOCanChangeLanguage=$sock->GET_INFO("ISOCanChangeLanguage");
		
	if(!is_numeric($ISOCanChangeLanguage)){$ISOCanChangeLanguage=1;}
	if(!is_numeric($ISOCanDisplayUserNamePassword)){$ISOCanDisplayUserNamePassword=1;}
	if(!is_numeric($ISOCanChangeIP)){$ISOCanChangeIP=1;}
	if(!is_numeric($ISOCanReboot)){$ISOCanReboot=1;}
	if(!is_numeric($ISOCanShutDown)){$ISOCanShutDown=1;}
	if(!is_numeric($ISOCanChangeRootPWD)){$ISOCanChangeRootPWD=1;}
	if(!is_numeric($ISOCanChangeLanguage)){$ISOCanChangeLanguage=1;}	

	
	
	
	
	$html="
	<div id='FROM_ISO_DIV'>
	<div class=explain style='margin-top:10px'>{MENU_CONSOLE_TEXT}</div>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{ISOCanChangeLanguage}</td>
		<td>". Field_checkbox("ISOCanChangeLanguage", 1,null,"$ISOCanChangeLanguage")."</td>
	</tr>
	<tr>
		<td class=legend>{ISOCanDisplayUserNamePassword}</td>
		<td>". Field_checkbox("ISOCanDisplayUserNamePassword", 1,null,"$ISOCanDisplayUserNamePassword")."</td>
	</tr>
	<tr>
		<td class=legend>{ISOCanChangeIP}</td>
		<td>". Field_checkbox("ISOCanChangeIP", 1,null,"$ISOCanChangeIP")."</td>
	</tr>
	<tr>
		<td class=legend>{ISOCanReboot}</td>
		<td>". Field_checkbox("ISOCanReboot", 1,null,"$ISOCanReboot")."</td>
	</tr>
	<tr>
		<td class=legend>{ISOCanShutDown}</td>
		<td>". Field_checkbox("ISOCanShutDown", 1,null,"$ISOCanShutDown")."</td>
	</tr>
	<tr>
		<td class=legend>{ISOCanChangeRootPWD}</td>
		<td>". Field_checkbox("ISOCanChangeRootPWD", 1,null,"$ISOCanChangeRootPWD")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","ISO_SAVESETS()")."</td></tr>					
	</tbody>
	</table>	
	</div>
	
	<script>
	
	var x_ISO_SAVESETS=function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);}	
			Loadjs('$page');
		}		
	
		function ISO_SAVESETS(){
			var XHR = new XHRConnection();
			var ISOCanChangeLanguage=0;
			var ISOCanDisplayUserNamePassword=0;
			var ISOCanChangeIP=0;
			var ISOCanReboot=0;
			var ISOCanShutDown=0;
			var ISOCanChangeRootPWD=0;
			if(document.getElementById('ISOCanChangeLanguage').checked){ISOCanChangeLanguage=1;}
			if(document.getElementById('ISOCanDisplayUserNamePassword').checked){ISOCanDisplayUserNamePassword=1;}
			if(document.getElementById('ISOCanChangeIP').checked){ISOCanChangeIP=1;}
			if(document.getElementById('ISOCanReboot').checked){ISOCanReboot=1;}
			if(document.getElementById('ISOCanShutDown').checked){ISOCanShutDown=1;}
			if(document.getElementById('ISOCanChangeRootPWD').checked){ISOCanChangeRootPWD=1;}
			XHR.appendData('ISOCanChangeLanguage',ISOCanChangeLanguage);
			XHR.appendData('ISOCanDisplayUserNamePassword',ISOCanDisplayUserNamePassword);	
			XHR.appendData('ISOCanChangeIP',ISOCanChangeIP);	
			XHR.appendData('ISOCanReboot',ISOCanReboot);	
			XHR.appendData('ISOCanShutDown',ISOCanShutDown);	
			XHR.appendData('ISOCanChangeRootPWD',ISOCanChangeRootPWD);	
			AnimateDiv('FROM_ISO_DIV');
			XHR.sendAndLoad('$page', 'POST',x_ISO_SAVESETS);	
			}
			
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$tpl=new templates();
	$sock=new sockets();
	while (list ($key, $line) = each ($_POST) ){
		$sock->SET_INFO($key,$line);
		
		
	}
	echo $tpl->javascript_parse_text("{settings_after_reboot}");
	
	
}
