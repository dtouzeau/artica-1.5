<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{header('location:users.index.php');exit;}	
if(isset($_GET["save_mailgraph_options"])){save_mailgraph_options();exit();}
if(isset($_GET["remove_mailgraph"])){remove_mailgraph();exit;}
if(isset($_GET["install_mailgraph"])){install_mailgraph();exit;}
if(isset($_GET["install_queuegraph"])){install_queuegraph();exit();}
if(isset($_GET["remove_queuegraph"])){remove_queuegraph();exit;}
if(isset($_GET["remove_yorel"])){remove_yorel();exit;}
if(isset($_GET["install_yorel"])){install_yorel();exit;}
Statistics_index_page();	
function Statistics_index_page(){
$html=MailgraphSettings().queueGraphSettings().YorelSettings();
$tpl=new template_users('{statistics_engine}',$html);
echo $tpl->web_page;
}

function save_mailgraph_options(){
include_once('ressources/class.sockets.inc');
	$tpl=new templates();
$mailgraph_postfix_database=$_GET["mailgraph_postfix_database"];
$mailgraph_virus_database=$_GET["mailgraph_virus_database"];
$sock=new sockets();
$sock->getfile('mailgraph_database:'.$mailgraph_postfix_database);
$sock->getfile('mailgraph_virus_database:'.$mailgraph_virus_database);
if($sock->error==false){echo $tpl->_ENGINE_parse_body('{success}');}else{echo "error";}
}
function remove_mailgraph(){
include_once('ressources/class.sockets.inc');
	$tpl=new templates();
	$sock=new sockets();
	$sock->getfile('PerformAutoRemove:APP_MAILGRAPH');
	if($sock->error==false){echo $tpl->_ENGINE_parse_body('{success}');}else{echo "error";}	
	
}
function install_mailgraph(){
	include_once('ressources/class.sockets.inc');
	$tpl=new templates();
	$sock=new sockets();
	$err=$sock->getfile('PerformAutoInstall:APP_MAILGRAPH');
	if($sock->error==false){echo $tpl->_ENGINE_parse_body("{success}\n".$err);}else{echo "error";}		
	
}
function install_queuegraph(){
	include_once('ressources/class.sockets.inc');
	$tpl=new templates();
	$sock=new sockets();
	$err=$sock->getfile('PerformAutoInstall:APP_QUEUEGRAPH');
	if($sock->error==false){echo $tpl->_ENGINE_parse_body("{success}\n".$err);}else{echo "error";}		
	}
function remove_queuegraph(){
	include_once('ressources/class.sockets.inc');
	$tpl=new templates();
	$sock=new sockets();
	$sock->getfile('PerformAutoRemove:APP_QUEUEGRAPH');
	if($sock->error==false){echo $tpl->_ENGINE_parse_body('{success}');}else{echo "error";}		
	}
function remove_yorel(){
include_once('ressources/class.sockets.inc');
	$tpl=new templates();
	$sock=new sockets();
	$sock->getfile('PerformAutoRemove:APP_YOREL');
	if($sock->error==false){echo $tpl->_ENGINE_parse_body('{success}');}else{echo "error";}		
	}
function install_yorel(){
include_once('ressources/class.sockets.inc');
	$tpl=new templates();
	$sock=new sockets();
	$sock->getfile('PerformAutoInstall:APP_YOREL');
	if($sock->error==false){echo $tpl->_ENGINE_parse_body('{success}');}else{echo "error";}		
	}

function queueGraphSettings(){
	$usersmenus=new usersMenus();
	$html="<H4>{APP_QUEUEGRAPH}</H4>
	<div class=caption>{queuegraph_about}</div>
	<center>
	";
	
	if($usersmenus->queuegraph_installed==true){
		$button=
		"<form name='remove_queuegraph'>
	<input type='hidden' name='remove_queuegraph' value='yes'>
	<center>
		<input type='button' value='&laquo;&nbsp;{remove_queuegraph}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('remove_queuegraph','" . basename(__FILE__)."',true);window.location.reload();\">
	</center>
	</form>";
	}else{$button=
		"<form name='install_queuegraph'>
	<input type='hidden' name='install_queuegraph' value='yes'>
	<center>
		<input type='button' value='&laquo;&nbsp;{install_queuegraph}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('install_queuegraph','" . basename(__FILE__)."',true);window.location.reload();\">
	</center>
	</form>";
		
		
	}
		return $html . "</center>";
}
function YorelSettings(){
	$usersmenus=new usersMenus();
	$html="<H4>{APP_YOREL}</H4>
	<div class=caption>{yorel_about}</div>
	<center>
	";
	
	if($usersmenus->yorel_installed==true){
		$button=
		"<form name='remove_yorel'>
	<input type='hidden' name='remove_yorel' value='yes'>
	<center>
		<input type='button' value='&laquo;&nbsp;{remove_yorel}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('remove_yorel','" . basename(__FILE__)."',true);window.location.reload();\">
	</center>
	</form>";
	}else{$button=
		"<form name='install_yorel'>
	<input type='hidden' name='install_yorel' value='yes'>
	<center>
		<input type='button' value='&laquo;&nbsp;{install_yorel}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('install_yorel','" . basename(__FILE__)."',true);window.location.reload();\">
	</center>
	</form>";
		
		
	}
		return $html . "</center>";
}

function MailgraphSettings(){
	$usersmenus=new usersMenus();
	if($usersmenus->mailgraph_installed==false){return MailGraphNotInstalled();}
	
	$html="
	<H4>{APP_MAILGRAPH}</H4>
	<div class='caption'>{mailgraph_about}</div>
	<form name='mailgrap_uninstall'>
	<input type='hidden' name='remove_mailgraph' value='yes'>
	<center>
		<input type='button' value='&laquo;&nbsp;{remove_mailgraph}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('mailgrap_uninstall','" . basename(__FILE__)."',true);\">
	</center>
	</form>
	<form name='mailgraph'>
	<input type='hidden' name='save_mailgraph_options' value='yes'>		
	<table style='width:100%'>
	<tr>
	<td align='right'><strong>{mailgraph_postfix_database}:</strong></td>
	<td >" . Field_text('mailgraph_postfix_database',$usersmenus->mailgraph_postfix_database) . "</td>
	</tr>
	<tr>
	<td align='right'><strong>{mailgraph_virus_database}:</strong></td>
	<td>" . Field_text('mailgraph_virus_database',$usersmenus->mailgraph_virus_database) . "</td>
	</tr>	
	<tr><td colspan=2 style='padding-rigth:10px' align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('mailgraph','" . basename(__FILE__)."',true);window.location.reload();\"></td></tr>	
	</table>
	</form>
	
	";
	
	return $html;
}
function MailGraphNotInstalled(){
	$html="<H4>{APP_MAILGRAPH}</H4>
	<div class='caption'>{mailgraph_about}</div>
	<form name='mailgraph_install'>
	<input type='hidden' name='install_mailgraph' value='yes'>
	<center>
		<input type='button' value='&laquo;&nbsp;{mailgraph_install}&nbsp;&raquo;'  OnClick=\"javascript:ParseForm('mailgraph_install','" . basename(__FILE__)."',true);window.location.reload();\">
	</center>
	</form>";
		return $html;
	
}
	