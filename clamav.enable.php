<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableClamavDaemon"])){save();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ENABLE_CLAMAV}");
	$html="YahooWin4('575','$page?popup=yes','$title');";
	echo $html;
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$EnableClamavDaemon=$sock->GET_INFO("EnableClamavDaemon");
	if($EnableClamavDaemon==null){$EnableClamavDaemon=1;}
	
	$p=Paragraphe_switch_img("{ENABLE_CLAMAV}","{ENABLE_CLAMAV_TEXT}","EnableClamavDaemon",$EnableClamavDaemon);

	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/clamav-104.png'>
		<td>$p<hr>
		<div style='width:100%;text-align:right'>". button("{apply}","EnableClamavDaemonSave()")."</div>
		</td>
	</tr>
	</table>
	<script>
	var x_EnableClamavDaemonSave=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
			YahooWin4Hide();
		}	
		
		function EnableClamavDaemonSave(){
			var XHR = new XHRConnection();
    		XHR.appendData('EnableClamavDaemon',document.getElementById('EnableClamavDaemon').value);
 			document.getElementById('img_EnableClamavDaemon').src='img/wait_verybig.gif';
    		XHR.sendAndLoad('$page', 'GET',x_EnableClamavDaemonSave);
			
		}	
	
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableClamavDaemon",$_GET["EnableClamavDaemon"]);
	$sock->getFrameWork("cmd.php?clamd-restart=yes");
	}
