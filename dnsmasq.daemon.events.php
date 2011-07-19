<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');



	if(posix_getuid()<>0){
		$user=new usersMenus();
		if($user->AsDnsAdministrator==false){
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
			die();exit();
		}
	}	

ParseLogs();
function ParseLogs(){
	
	
	$sock=new sockets();
	$datas=$sock->getfile('dnsmasqlogs');
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	$tbl=array_reverse($tbl);
	$html="<table style='width:100%'>";
	while (list ($index, $line) = each ($tbl) ){
		if($line<>null){
			$html=$html  . "
			<tr>
			<td width='1%' valign='middle' class=bottom><img src='img/fw_bold.gif'><td>
			<td class=bottom>$line</td>
			</tr>";
		}
		
	}
	
	echo $html . "</table>";
	
}

