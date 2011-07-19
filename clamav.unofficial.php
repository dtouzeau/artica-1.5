<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');



	
	if(!GetRigths()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["enable_clamav_unofficial"])){save();exit;}
js();	
	
	
function js(){

	$tpl=new templates();
	$clamav_unofficial=$tpl->_ENGINE_parse_body("{clamav_unofficial}");
	$page=CurrentPageName();
	$html="
	
		function clamav_unofficial(){
			YahooWin2('400','$page?popup=yes','$clamav_unofficial');
		}
	
		clamav_unofficial();
		

		";
		
	echo $html;
	
	
	
}

function popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableClamavUnofficial=$sock->GET_INFO("EnableClamavUnofficial");
	$tpl=new templates();
	$level=Paragraphe_switch_img('{enable_clamav_unofficial}',"{clamav_unofficial_text}",
	"enable_clamav_unofficial",$EnableClamavUnofficial,null,550);
	$html="
	
	<table style='widht:100%'>
	<tr>
		<td valign='top'>
			<div id='enable_clamav_unofficial_id'>
			$level
			</div>
		</td>
	</tr>
	<tr>
		<td valign='top' align='right'>
		<hr>". button("{apply}","clamav_unofficial_save()")."
		</td>
		
	</tr>
	</table>
<script>
var x_clamav_unofficial_save= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
    YahooWin2Hide();  
	}	
	
function clamav_unofficial_save(){
	var XHR = new XHRConnection();
	XHR.appendData('enable_clamav_unofficial',document.getElementById('enable_clamav_unofficial').value);
	document.getElementById('enable_clamav_unofficial_id').innerHTML='<img src=\"img/wait_verybig.gif\">';
	XHR.sendAndLoad('$page', 'GET',x_clamav_unofficial_save);	
	
	}
</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableClamavUnofficial",$_GET["enable_clamav_unofficial"]);
	if($_GET["enable_clamav_unofficial"]==1){
		$sock->getFrameWork("cmd.php?update-clamav=yes");
	}
}

function GetRigths(){
	$user=new usersMenus();
	if($user->AsPostfixAdministrator){return true;}
	if($user->AsSquidAdministrator){return true;}
	if($user->AsSambaAdministrator){return true;}
	return false;
}

?>