<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
		if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableInterfaceMailCampaigns"])){save();exit;}
	
js();	
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{ENABLE_MASSMAILING}');
	$page=CurrentPageName();
	
$html="

function POSTFIX_MASSMAILS(){
	YahooWin('660','$page?popup=yes','$title');
}
POSTFIX_MASSMAILS();";
	
	echo $html;
}	

function popup(){
	
	
	$page=CurrentPageName();
	$sock=new sockets();
	$enable=Paragraphe_switch_img("{ENABLE_MASSMAILING}","{ENABLE_MASSMAILING_TEXT}",
	"EnableInterfaceMailCampaigns",$sock->GET_INFO("EnableInterfaceMailCampaigns"),null,350);

	
	
	
	$html="
	<div id='massmails'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/mass-mailing-postfix-256.png'></td>
		<td valign='top'>
		$enable
			<div style='text-align:right'><hr>". button("{apply}","SaveMassMail()")."</div>
			
			
		</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	var x_SaveMassMail= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		YahooWinHide();
	}	
	
	function SaveMassMail(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableInterfaceMailCampaigns',document.getElementById('EnableInterfaceMailCampaigns').value);
		document.getElementById('massmails').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SaveMassMail);
	}
		
	
	</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableInterfaceMailCampaigns",$_GET["EnableInterfaceMailCampaigns"]);
	}


?>