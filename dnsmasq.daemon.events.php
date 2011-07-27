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
	
	if(isset($_GET["show"])){ParseLogs2();exit;}

ParseLogs();
function ParseLogs(){
	$page=CurrentPageName();
	$html="<div id='RefreshDNSMSQLogs-ev'></div>
	
	<script>
		function RefreshDNSMSQLogs(){
			LoadAjax('RefreshDNSMSQLogs-ev','$page?show=yes');
		}
		RefreshDNSMSQLogs();
	</script>";
	
	echo $html;
		
}



function ParseLogs2(){
	
	
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?syslog-query=$pattern&prefix=dnsmasq*")));
	
	if(is_array($tbl)){
		$tbl=array_reverse($tbl);
	}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>". imgtootltip("refresh-24.png","{refresh}","RefreshDNSMSQLogs()")."</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($index, $line) = each ($tbl) ){
			if(trim($line)==null){continue;}
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html  . "
			<tr class=$classtr>
			<td width='1%'><img src='img/fw_bold.gif'></td>
			<td style='font-size:13px'>$line</td>
			</tr>";
		
		
	}
	
	echo $html . "</table>";
	
}

