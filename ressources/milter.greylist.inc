<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.milter.greylist.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["main"])){main_switch();exit;}
	
	
	
	main_page();
	
function main_page(){
	

	
	$html=
	"<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_sqlgrey.jpg'>	<p class=caption>{about}</p></td>
	<td>
	" . applysettings("sqlgrey") . "<br>" . main_status() . "</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	$cfg["JS"][]='js/sqlgrey.js';
	$tpl=new template_users('{APP_MILTERGREYLIST}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{main_settings}';
	$array["logs"]='{events}';
	$array["fqdn"]='{fqdn_white_list}';
	$array["ipwl"]='{ip_white_list}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "fqdn":main_wl_fqdn();exit;break;
		case "ipwl":main_wl_ip();exit;break;
		case "ipwl_list":echo main_wl_ip_list();exit;break;
		case "fqdn_list";echo main_wl_fqdn_list();exit;
		default:
			break;
	}
	
	
}	