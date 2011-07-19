<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.sockets.inc");
include_once(dirname(__FILE__) . '/ressources/kav4mailservers.inc');

$user=new usersMenus();
$tpl=new Templates();
if($user->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no privileges}');exit;}
$kav=new kav4mailservers(1);


	$datas=htmlentities($datas);
	$tbl=explode("\n",$datas);
	$tbl=$kav->DaemonLastLogs();
	$html=DaemonErrors().DaemonLastLogs();

 $tplusr=new template_users('{events}',$html,0,0,0,10);
echo $tplusr->web_page;
function DaemonLastLogs(){
$kav=new kav4mailservers(1);
$daemon_errors=$kav->DaemonLastLogs();

if(is_array($daemon_errors)){
			$html=$html."<H4>{events}</H4>
			<center>
			<table style='width:100%'>
			<tr style='background-color:#005447;'>
			<td>&nbsp;</td>
			<td style='color:white;font-weight:bold' width=5%>{date}</td>
			<td style='color:white;font-weight:bold'>{details}</td>
			</tr>";
			
			while (list ($key, $val) = each ($daemon_errors) ){
				
				if(preg_match('#\[([0-9\-:\s\/]+).+?\]#',$val,$reg)){
					$val=str_replace($reg[0],'',$val);
				$count=$coun+1;
				$html=$html . "<tr>
				<td width=1% class='bottom'><img src='img/status_ok.jpg'></td>
				<td class='bottom' nowrap><div style='color:black;font-size:10px;padding:3px;'>{$reg[1]}</div></td>
				<td class='bottom'><div style='color:black;font-size:10px;padding:3px;'>$val</div></td>
				</tR>";
				}
				
			}
			$html=$html . "</table></center>";
		}
		if($count>0){
		return $html;}		
	
}


function DaemonErrors(){
$kav=new kav4mailservers(1);
$daemon_errors=$kav->DaemonErrors();

if(is_array($daemon_errors)){
			$html=$html."<H4>{daemon_error}</H4>
			<center>
			<table style='width:100%'>
			<tr style='background-color:#005447;'>
			<td>&nbsp;</td>
			<td style='color:white;font-weight:bold' width=5%>{date}</td>
			<td style='color:white;font-weight:bold'>{details}</td>
			</tr>";
			
			while (list ($key, $val) = each ($daemon_errors) ){
				if(preg_match('#\[([0-9\-:\s\/]+).+?\]#',$val,$reg)){
					$val=str_replace($reg[0],'',$val);
					$count=$count+1;
				$html=$html . "<tr>
				<td width=1% class='bottom'><img src='img/status_warning.jpg'></td>
				<td class='bottom' nowrap><div style='color:red;font-size:10px;padding:3px;'>{$reg[1]}</div></td>
				<td class='bottom'><div style='color:red;font-size:11px;padding:3px;'>$val</div></td>
				</tR>";
				}
				
			}
			$html=$html . "</table></center>";
		}
	if($count>0){
		return $html;}	
	
}

?>