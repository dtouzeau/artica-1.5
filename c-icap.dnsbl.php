<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.tcpip.inc');
	
	$user=new usersMenus();
		
	if($user->SQUID_INSTALLED==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["adduri"])){adduri();exit;}

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{CICAP_DNSBL}");
	
	$html="
	function cicap_dnsbl_load(){
			YahooWin(600,'$page?popup=yes','$title');
		}
		
	 var x_cicap_dnsbl_enable= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			
		}		
		
	function cicap_dnsbl_enable(md,uri){
		var XHR = new XHRConnection();
		XHR.appendData('adduri',uri);
		if(document.getElementById(md).checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		XHR.sendAndLoad('$page', 'GET',x_cicap_dnsbl_enable);
	}
		

	cicap_dnsbl_load();";
	echo $html;
	
}


function popup(){
	
	$f=@file_get_contents("ressources/databases/db.surbl.txt");
	
$sock=new sockets();
$datas=explode("\n",$sock->GET_INFO("CicapDNSBL"));
while (list ($num, $line) = each ($datas)){
	$array_DNSBL[$line]=true;
}	
	

	
	
	$html="<div style='width:100%;height:650px;overflow:auto'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{service}</th>
		<th>{uri_dnsbl}</th>
		<th colspan=2>{enabled}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	if(preg_match_all("#<server>(.+?)</server>#is",$f,$servers)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		while (list ($num, $line) = each ($servers[0])){
			if(preg_match("#<item>(.+?)</item>#",$line,$re)){$server_uri=$re[1];}
			if(preg_match("#<name>(.+?)</name>#",$line,$re)){$name=$re[1];}
			if(preg_match("#<uri>(.+?)</uri>#",$line,$re)){$info=$re[1];}
			$md=md5($server_uri);
			if($array_DNSBL[$server_uri]==true){$enabled=1;}else{$enabled=0;}
			
			if($GLOBALS[$md]==true){continue;}
			$html=$html."
			<tr ". CellRollOver().">
			<td><strong style='font-size:14px'>$name</strong></td>
			<td><code style='font-size:12p'x>$server_uri</strong></td>
			<td>". imgtootltip("22-infos.png","{infos}","s_PopUp('$info',800,800)")."</td>
			<td>". Field_checkbox("$md",1,$enabled,"cicap_dnsbl_enable('$md','$server_uri')")."</td>
			</tr>
			";
			$GLOBALS[$md]=true;
		}
	}
	
	$html=$html."</table></div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function adduri(){
$sock=new sockets();
$datas=explode("\n",$sock->GET_INFO("CicapDNSBL"));
while (list ($num, $line) = each ($datas)){
	if(strlen($line)<4){continue;}
	$array[$line]=$line;
}

if($_GET["enabled"]==1){
	$array[$_GET["adduri"]]=$_GET["adduri"];
}else{
	unset($array[$_GET["adduri"]]);
}
while (list ($num, $line) = each ($array)){
	$new[]=$line;
}

$sock->SaveConfigFile(implode("\n",$new),"CicapDNSBL");
$sock->getFrameWork("cmd.php?cicap_reconfigure=yes");
}

?>