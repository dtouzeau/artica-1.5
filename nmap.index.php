<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.nmap.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');

	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){header('location:users.index.php');exit();}
	
	if(isset($_GET["NmapScanEnabled"])){main_settings_edit();exit;}
	if(isset($_GET["nmap_delete_ip"])){main_delete_network();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["AddNmapNetwork"])){main_add_nework();exit;}
	if(isset($_GET["ScanNow"])){main_scan();exit;}

	
	
		main_page();
	
function main_page(){
	
$page=CurrentPageName();
	if($_GET["hostname"]==null){
		$user=new usersMenus();
		$_GET["hostname"]=$user->hostname;}
	
	$html=
"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=5-tant;
	if (tant < 5 ) {                           
      timerID = setTimeout(\"demarre()\",2000);
      } else {
               tant = 0;
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	if(document.getElementById('nmap_events')){
	LoadAjax('nmap_events','$page?main=nmap-evdetails');
	}
	LoadAjax('status','$page?main=status');
	}
</script>		
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_nmap.png'></td>
	<td valign='top'>
	<div id='status'></div></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();ChargeLogs();LoadAjax('main_config','$page?main=default');</script>
	
	";
	
	
	
	
	
	
	$cfg["JS"][]="js/nmap.js";
	$tpl=new template_users('{APP_NMAP}',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}



function main_switch(){
	
	switch ($_GET["main"]) {
		
		case "nmap-list":echo main_network_list();exit;break;
		case "nmap-add":echo main_form_add();exit;break;
		case "nmap-log":echo main_events();exit;break;
		case "status":echo main_status();exit;break;
		case "nmap-evdetails":echo main_events_fill();break;
		default:main_settings();break;
	}
	
	
	
}


function main_form_add(){
		$tmp=md5(date('YmdHis'));
$html="	<H5>{add_network}&nbsp;</H5>
	<p class=caption>{add_network_text}</p>
	<input type='hidden' name='tmp' id='tmp' value='$tmp'>
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
		<td valign='top' nowrap align='right'><strong>{ip}:</strong></td>
		<td valign='top' nowrap align='left'>" . Field_text('nmap_ip','','width:120px')."</td>
	</tr>
	<tr>
		<td valign='top' nowrap align='right'><strong>{mask}:</strong></td>
		<td valign='top' nowrap align='left'>" . Field_text('nmap_mask','','width:120px')."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><input type='button' OnClick=\"javascript:AddNmapNetwork();\" value='{add}&nbsp;&raquo;'></td>
	</tr>
	</table>");	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}


function main_settings(){
	
	$nmap=new nmap();
	$artica=new artica_general();
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<H5>{APP_NMAP}&nbsp;{settings}</H5>
	<p class=caption>{about}</p>
	" . RoundedLightGrey("
	<table style='width:100%'>
	<tr>
		<td valign='top' nowrap align='right'><strong>{NmapScanEnabled}:</strong></td>
		<td valign='top' nowrap align='left'>" . Field_numeric_checkbox_img('NmapScanEnabled',$artica->NmapScanEnabled,'{enable_disable}')."</td>
	</tr>
	<tr>
		<td valign='top' nowrap align='right'><strong>{NmapRotateMinutes}:</strong></td>
		<td valign='top' nowrap align='left'>" . Field_text('NmapRotateMinutes',$nmap->NmapRotateMinutes,'width:90px')."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><input type='button' OnClick=\"javascript:SaveNmapSettings();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>")."
	<br><div id='nmap_list'>".main_network_list()."</div>
	</td>
	<td valign='top'>
	" . RoundedLightGrey(Paragraphe("acl-add-64.png",'{add_network}','{add_network_text}',"javascript:nmap_add_network()"))."<br>
	" . RoundedLightGrey(Paragraphe("folder-logs-64.png",'{nmap_logs}','{nmap_logs_text}',"javascript:nmap_logs()"))."<br>
	" . RoundedLightGrey(Paragraphe("global-settings.png",'{perform_scan}','{perform_scan_text}',"javascript:nmap_scan()"))."<br>
	
	
	
	
	</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function main_settings_edit(){
	$nmap=new nmap();
	$nmap->NmapRotateMinutes=$_GET["NmapRotateMinutes"];
	$nmap->SaveConf();
	$artica=new artica_general();
	$artica->NmapScanEnabled=$_GET["NmapScanEnabled"];	
	
	$artica->Save();
	
}


function main_network_list(){
	$nmap=new nmap();
	
	$html="
	
	<table style='with:350px'>";
	
	while (list ($num, $ligne) = each ($nmap->NmapNetworkIP) ){
		
		$tmp=md5(date('YmdHis').$ligne);
		$html=$html . "
		<tr " . CellRollOver().">
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td><strong>$ligne</td>
			<td width=1% valign='top'>" . imgtootltip('x.gif','{delete}',"nmap_delete_ip('$num','$tmp')")."</td>
		</tr>
		
		
		";
		
		
		
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<H3>{networks_list}</h3>".RoundedLightGrey($html));
	
}

function main_delete_network(){
	$nmap=new nmap();
	$net=$nmap->NmapNetworkIP[$_GET["nmap_delete_ip"]];
	$tpl=new templates();
	
	if($nmap->delIpToscan($_GET["nmap_delete_ip"])){
		echo $tpl->_ENGINE_parse_body("$net\n{success}");
	}else{
		echo $tpl->_ENGINE_parse_body("$net\n{failed}\n$nmap->ldap_error");
	}
	
}

function main_add_nework(){
	$nmap=new nmap();
	$sip=new SecondIP($_GET["AddNmapNetwork"],$_GET["mask"]);
	$tpl=new templates();
	
	if($nmap->addIpToscan($sip->CDR)){
		echo $tpl->_ENGINE_parse_body("$sip->CDR\n{success}");
	}else{
		echo $tpl->_ENGINE_parse_body("$sip->CDR\n{failed}\n$nmap->ldap_error");
	}
	
}

function  main_status(){
	$sock=new sockets();
	$datas=$sock->getfile('nmapmem');
	
	if(strlen($datas)>2){
		$img="warning24.png";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
	}
	
$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_NMAP}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>$datas</strong></td>
		</tr>	
	</table>
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);	
	
}

function main_events(){
	
	
	$html="<H3>{nmap_logs}</H3><div id='nmap_events'>".main_events_fill()."</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function main_events_fill(){
	$datas=@explode("\n",file_get_contents(@dirname(__FILE__).'/ressources/logs/nmap.log'));
	if(is_array($datas)){
		
		$datas=array_reverse($datas);	
		while (list ($num, $ligne) = each ($datas) ){
			if(trim($ligne)<>null){
				$ev[]=$ligne;
			}
		
		}
	}
	
	return "<textarea style='border:0px;font-size:10px;width:100%' rows=50>".@implode("\n",$ev)."</textarea>";
	
}

function main_scan(){
	$sock=new sockets();
	$sock->getfile('NmapScanNow');
	}

?>