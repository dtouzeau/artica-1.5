<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	if(isset($_GET["SendCommandStopService"])){echo SendCommandStopService();exit;}
	if(isset($_GET["SendCommandStartService"])){echo SendCommandStartService();exit;}
	if(isset($_GET["StopNow"])){echo StopNow();exit;}
	if(isset($_GET["StartNow"])){echo StartNow();exit;}
	if(isset($_GET["ReloadTable"])){echo Table();exit;}
	if(isset($_GET["STATUS"])){echo STATUS();exit;}
	if(isset($_GET["daemon_start"])){echo daemon_start();exit;}
	if(isset($_GET["daemon_stop"])){echo daemon_stop();exit;}
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{header('location:users.index.php');exit;}	

services_page();
function services_page(){
	
	$table=Table();
	$page="
	<div id='tableServices'>$table</div>";
	$cfg["JS"][]='js/artica_services.js';
	$html=new template_users('{manage_services}',$page,0,0,0,0,$cfg);
	echo $html->web_page;
	
}

function Table(){
include_once('ressources/class.sockets.inc');
	include_once('ressources/class.ini.inc');
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?all-status'));
	//echo $datas;

	$ini=new Bs_IniHandler();
	$ini->loadString($datas);
	$applis=$ini->_sections;
	$styleTD="style='border-bottom:1px dotted #8e8785;'";
	$styleTall="style='border:1px dotted #8e8785;padding:4px;'";
	if(!is_array($applis)){return "<H2>{system error}<br>$page line " . __LINE__. "</H2>";}
	$html="
	<br><br>
	<table style='width:90%;padding-left:20px' align='center'>
	<tr>
		<td width=1% class='caption' align='center' $styleTall>{status}</td>
		<td width=1% class='caption' align='left' nowrap $styleTall>{process_name}</td>
		<td width=1% class='caption' align='left' nowrap $styleTall>{pid}</td>
		<td width=1% class='caption' align='left' nowrap $styleTall>{service_name}</td>
		<td width=1% class='caption' align='left' nowrap $styleTall>&nbsp;</td>
		
		<td></td>
	</tr>
	";
	while (list ($num, $ligne) = each ($applis) ){
		$status=$ini->_params[$ligne];
		
		while (list ($process, $etat) = each ($status)){
			
			if(DaemonStatus($etat,2)==0){$button="<input type='button' value='{start_daemon}&nbsp;&raquo;&raquo;' Onclick=\"javascript:DaemonStart('$ligne')\" style='width:100px'>";}
			if(DaemonStatus($etat,2)==1){$button="<input type='button' value='{stop_daemon}&nbsp;&raquo;&raquo;' Onclick=\"javascript:DaemonStop('$ligne')\" style='width:100px'>";}
			
			$html=$html ."<tr>
						<td width=1% align='center' $styleTD><img src='img/" . DaemonStatus($etat) ."'></td>
						<td $styleTD>$process</td>
						<td align='left' $styleTD>" . DaemonStatus($etat,1) ."</td>
						<td width=80% $styleTD>{"."$ligne}</td>
						<td width=1% $styleTD>$button</td>
						</tr>
						";
			
		}
		

		
	}
	$html=$html . "</table>";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function daemon_start(){
	$service=$_GET["daemon_start"];
	$sock=new sockets();
	$sock->getfile('start_service:'.$service);
	services_page();
	
}
function daemon_stop(){
	$service=$_GET["daemon_stop"];
	$sock=new sockets();
	$sock->getfile('stop_service:'.$service);
	services_page();
	
}
function STATUS(){
	$APP=$_GET["STATUS"];
	include_once('ressources/class.ini.inc');
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?all-status'));
	$ini=new Bs_IniHandler();
	$ini->loadString($datas);		
	$status=$ini->_params[$APP];
	while (list ($process, $etat) = each ($status)){
		return DaemonStatus($etat,2) . ";$APP";
	}
	
}
function DaemonStatus($line,$return_img=0){
	$arr=explode(';',$line);
	switch ($arr[1]) {
		case '-1':$img='status_service_removed.jpg';$stopped=true;break;
		case '0':$img='status_service_stop.jpg';$stopped=true;break;
		case '1':$img='status_service_ok.jpg';$stopped=false;break;
		default:$img='status_service_removed.jpg';$stopped=true;break;
		}
		
	if($return_img==0){return $img;}
	if($return_img==1){return $arr[0];}
	if($return_img==2){return $arr[1];}	
}

function SendCommandStopService(){
	$APPS=$_GET["SendCommandStopService"];
	
	$html="
	<center>
	<H4>{stop_daemon} {" . $APPS . "}</H4>
	<img src='img/frw8at_ajaxldr_7.gif'>
	</center>
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function SendCommandStartService(){
	$APPS=$_GET["SendCommandStartService"];
	
	$html="
	<center>
	<H4>{start_daemon} {" . $APPS . "}</H4>
	<img src='img/frw8at_ajaxldr_7.gif'>
	</center>
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}

function StopNow(){
	$APPS=$_GET["StopNow"];
	include_once('ressources/class.sockets.inc');
	$sock=new sockets();
	$return=$sock->getfile('stop_service:'.$APPS);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($return);	
}

?>