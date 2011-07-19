<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.sockets.inc");

$user=new usersMenus();
$tpl=new Templates();
if($user->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no privileges}');exit;}
$sock=new sockets();
$datas=$sock->getfile('procmail_logs');
if($sock->error==true){echo  $tpl->_ENGINE_parse_body('{socket_error}');exit;}

	$datas=htmlentities($datas);
	$tbl=explode("\n",$datas);
	$tbl=array_reverse ($tbl, TRUE);
	$html="<table style='width:100%'>";
while (list ($num, $val) = each ($tbl) ){
		if(trim($val)<>null){
		if($class=="rowA"){$class="rowB";}else{$class="rowA";}
		$html=$html . "<tr>
		<td width=1% style='border-bottom:1px dotted #CCCCCC' align='center'><img src='" . statusLogs($val) . "'></td>
		<td width=99%' style='border-bottom:1px dotted #CCCCCC;font-size:10px;'>$val</td>
		</tr>
		";
		}
		
	}
	
$html= $html . "</table>";	
 $tplusr=new template_users('{procmail_events}',$html,0,0,0,10);
echo $tplusr->web_page;