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
	if(isset($_GET["ApacheGroupware"])){save();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_GROUPWARE_APACHE}");
	$html="YahooWin4('575','$page?popup=yes','$title');";
	echo $html;
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	
	$p=Paragraphe_switch_img("{ENABLE_APACHE_GROUPWARE}","{APP_GROUPWARE_APACHE_TEXT}","ApacheGroupware",$ApacheGroupware);

	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/apache-groupeware-128.png'>
		<td>$p<hr>
		<div style='width:100%;text-align:right'>". button("{apply}","EnableDisableApacheGroupWare()")."</div>
		</td>
	</tr>
	</table>
	<script>
	var x_EnableDisableApacheGroupWare=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
			YahooWin4Hide();
		}	
		
		function EnableDisableApacheGroupWare(){
			var XHR = new XHRConnection();
    		XHR.appendData('ApacheGroupware',document.getElementById('ApacheGroupware').value);
 			document.getElementById('img_ApacheGroupware').src='img/wait_verybig.gif';
    		XHR.sendAndLoad('$page', 'GET',x_EnableDisableApacheGroupWare);
			
		}	
	
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("ApacheGroupware",$_GET["ApacheGroupware"]);
	$sock->SET_INFO("ShowApacheGroupware",$_GET["ApacheGroupware"]);
	$sock->getFrameWork("cmd.php?RestartApacheGroupwareForce=yes");
	}
