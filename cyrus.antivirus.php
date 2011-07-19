<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.autofs.inc');
	include_once('ressources/class.computers.inc');

	
	$user=new usersMenus();
	if($user->AsMailBoxAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["CyrusEnableAV"])){SaveConf();exit;}

	

js();
function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{cyrus_scan_antivirus}','cyrus.index.php');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		RTMMail(550,'$page?popup=yes','$title');
	}

	
	
var x_SaveScanCyrusInfos=function (obj) {
	{$prefix}LoadPage();
	}	
	
	function SaveScanCyrusInfos(){
    	var XHR = new XHRConnection();
    	XHR.appendData('CyrusEnableAV',document.getElementById('CyrusEnableAV').value);
    	XHR.appendData('ProcessNice',document.getElementById('ProcessNice').value);
    	XHR.appendData('schedule',document.getElementById('schedule').value);
 		document.getElementById('ScanCyrusInfos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveScanCyrusInfos);
	}
	


	{$prefix}LoadPage();";

	echo $html;
	}
	
function SaveConf(){

	$sock=new sockets();
	$sock->SET_INFO("CyrusEnableAV",$_GET["CyrusEnableAV"]);
	$ini=new Bs_IniHandler();
	while (list ($num, $val) = each ($_GET) ){
		$ini->_params["SCAN"][$num]=trim($val);
	}
	
	$sock->SaveConfigFile($ini->toString(),"CyrusAVConfig");
	$tpl=new templates();
	$sock->getFrameWork('cmd.php?RestartDaemon=yes');
	echo html_entity_decode($tpl->_ENGINE_parse_body('{success}'));
}
	
	
function popup(){

	$sock=new sockets();
	$enable=Paragraphe_switch_img('{enable_cyrus_antivirus_scan}','{enable_cyrus_antivirus_scan_text}','CyrusEnableAV',$sock->GET_INFO("CyrusEnableAV"),"enable_cyrus_antivirus_scan",370);
	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("CyrusAVConfig"));
	if($ini->_params["SCAN"]["ProcessNice"]==null){$ini->_params["SCAN"]["ProcessNice"]=-15;}
	
	$arrp=array(10=>"{default}",-15=>"{high}",10=>"{medium}",12=>"{low}",19=>'{very_low}');
	$arrp=Field_array_Hash($arrp,'ProcessNice',$ini->_params["SCAN"]["ProcessNice"],"style:width:120px;padding:3px;font-size:13px");	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/virus-server-128.png'>
		</td>
		<td valign='top'>
		<div id='ScanCyrusInfos'>
			<input type='hidden' id='ProcessNice' value='{$ini->_params["SCAN"]["ProcessNice"]}'>
			<div class=explain>{cyrus_scan_antivirus_text}</div>
			$enable<br>
				<table style='width:100%'>
				
				<tr>
					<td valign='middle' class=legend nowrap>{schedule}:</td>
					<td>". Field_text('schedule',$ini->_params["SCAN"]["schedule"],'width:120px;padding:3px;font-size:13px')."&nbsp;<input type='button' OnClick=\"javascript:Loadjs('cron.php?field=schedule');\" value='{schedule}...'></td>
				</tr>
				<tr>
					<td colspan=2 align='right'>
						<hr>". button("{apply}","SaveScanCyrusInfos()")."
							
					</td>
				</td>
				
				</table>
				
		</div></td>
	</tr>
	</table>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'cyrus.index.php,artica.performances.php');
	}
?>