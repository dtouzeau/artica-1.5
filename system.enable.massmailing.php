<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableInterfaceMailCampaigns"])){save();exit;}
	
	js();
	
	
function js(){

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{email_campaigns}");
	$page=CurrentPageName();
	
	$html="
		function enable_massmailing_start(){
			YahooWin2('550','$page?popup=yes','$title');
		
		}
		
		
	var x_enable_massmailing_save= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			document.getElementById('enable_massmailing_id').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/mass-mailing-128.png\"></center>';
			YahooWin2Hide();	
		}		
		
		
		function enable_massmailing_save(){
				var XHR = new XHRConnection();
				XHR.appendData('EnableInterfaceMailCampaigns',document.getElementById('EnableInterfaceMailCampaigns').value);
				document.getElementById('enable_massmailing_id').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_enable_massmailing_save);
			
		}		
	
	enable_massmailing_start()";
	echo $html;
	
}


function popup(){
	
	$users=new usersMenus();
	if(!$users->EMAILRELAY_INSTALLED){
		popup_not_installed();exit;
	}
	
	$sock=new sockets();
	$EnableInterfaceMailCampaigns=$sock->GET_INFO("EnableInterfaceMailCampaigns");
	
	$field=Paragraphe_switch_img("{ENABLE_MASSMAILING}","{ENABLE_MASSMAILING_TEXT}","EnableInterfaceMailCampaigns",$EnableInterfaceMailCampaigns,null,380);
	
	
	$html="<table style='width:100%'>
	<tr>
		<td width=1%>
		<div id='enable_massmailing_id'>
		<img src='img/mass-mailing-128.png'>
		</div>
		</td>
		<td>$field
		<hr>
		<div style='text-align:right'>". button("{apply}","enable_massmailing_save()")."</div>
		</td>
	</tr>
	</table>
		
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function save(){
	
	$sock=new sockets();
	$sock->SET_INFO("EnableInterfaceMailCampaigns",$_GET["EnableInterfaceMailCampaigns"]);
	$sock->getFrameWork("cmd.php?emailing-build-emailrelays=yes");
	
}

function popup_not_installed(){
	
	$html="
	
	<center style='font-size:16px;color:red'>{APP_EMAILRELAY_NOT_INSTALLED}</center>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

?>