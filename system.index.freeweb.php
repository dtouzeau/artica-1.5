<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	
	

	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["EnableFreeWeb"])){Save();exit;}
	
js();


function js(){
	
	$page=CurrentPageName();
	$html="YahooWin4('425','$page?popup=yes','Freewebs');";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$EnableFreeWeb=$sock->GET_INFO("EnableFreeWeb");
	$FreeWebLeftMenu=$sock->GET_INFO("FreeWebLeftMenu");
	$EnableGroupWareScreen=$sock->GET_INFO("EnableGroupWareScreen");
	
	if(!is_numeric($EnableFreeWeb)){$EnableFreeWeb=0;}
	if(!is_numeric($FreeWebLeftMenu)){$FreeWebLeftMenu=1;}
	if(!is_numeric($EnableGroupWareScreen)){$EnableGroupWareScreen=1;}
	$p=Paragraphe_switch_img("{enable_freeweb}","{enable_freeweb_text}","EnableFreeWeb",$EnableFreeWeb,null,400);
	$p2=Paragraphe_switch_img("{add_to_left_menu}","{EnableFreeWebInLeftMenuText}","FreeWebLeftMenu",$FreeWebLeftMenu,null,400);
	$p3=Paragraphe_switch_img("{EnableGroupWareScreen}","{EnableGroupWareScreenText}","EnableGroupWareScreen",$EnableGroupWareScreen,null,400);
	
	$html="
	$p
	<p>&nbsp;</p>
	$p2
	<p>&nbsp;</p>
	$p3
	<div style='width:100%;text-align:right'><hr>". button("{apply}","EnableFreeWebSect()")."</div>
	
	<script>
	var x_EnableFreeWebSect=function (obj) {
			var results=obj.responseText;
			CacheOff();
			if(document.getElementById('main_system_settings')){
				RefreshTab('main_system_settings');
			}
			
			YahooWin4Hide();
		}	
		
		function EnableFreeWebSect(){
			var XHR = new XHRConnection();
    		XHR.appendData('EnableFreeWeb',document.getElementById('EnableFreeWeb').value);
    		XHR.appendData('FreeWebLeftMenu',document.getElementById('FreeWebLeftMenu').value);
    		XHR.appendData('EnableGroupWareScreen',document.getElementById('EnableGroupWareScreen').value);
 			document.getElementById('img_EnableFreeWeb').src='img/wait_verybig.gif';
 			document.getElementById('img_FreeWebLeftMenu').src='img/wait_verybig.gif';
 			document.getElementById('img_EnableGroupWareScreen').src='img/wait_verybig.gif';
    		XHR.sendAndLoad('$page', 'POST',x_EnableFreeWebSect);
			
		}
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function Save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableFreeWeb", $_POST["EnableFreeWeb"]);
	$sock->SET_INFO("FreeWebLeftMenu", $_POST["FreeWebLeftMenu"]);
	$sock->SET_INFO("EnableGroupWareScreen", $_POST["EnableGroupWareScreen"]);
	 
}
