<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.retranslator.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-wget"])){popup_wget();exit;}
	if(isset($_GET["popup-apache"])){popup_apache();exit;}
	if(isset($_GET["popup-finish"])){popup_finish();exit;}
	if(isset($_GET["WizardRetransCompile"])){compile();exit;}

	if(isset($_GET["W_WKS"])){
			$_SESSION["WIZARD_RETRANS"]["W_WKS"]=$_GET["W_WKS"];
			$_SESSION["WIZARD_RETRANS"]["W_AK"]=$_GET["W_AK"];
			$_SESSION["WIZARD_RETRANS"]["W_LNX"]=$_GET["W_LNX"];
			exit;
			}

	if(isset($_GET["PROXY_PASS"])){
			$_SESSION["WIZARD_RETRANS"]["PROXY_PASS"]=$_GET["PROXY_PASS"];
			$_SESSION["WIZARD_RETRANS"]["PROXY_USR"]=$_GET["PROXY_USR"];
			$_SESSION["WIZARD_RETRANS"]["PROXY_PORT"]=$_GET["PROXY_PORT"];
			$_SESSION["WIZARD_RETRANS"]["PROXY_NAME"]=$_GET["PROXY_NAME"];
			$_SESSION["WIZARD_RETRANS"]["RetranslatorRegionSettings"]=$_GET["RetranslatorRegionSettings"];
			exit;
			}		

	if(isset($_GET["RetranslatorHttpdPort"])){$_SESSION["WIZARD_RETRANS"]["RetranslatorHttpdPort"]=$_GET["RetranslatorHttpdPort"];exit;}
	
	
js();	


function popup_apache(){
		$html="
	<table style='width:100%'> 
	<tr>
		<td valign='top'><img src='img/96-wizard.png'>$intro</td>
		<td valign='top'>
	<H1>{WIZARD_HTTPD_ENGINE}</H1>
	<p style='font-size:14px'>{WIZARD_HTTPD_ENGINE_RETRANS_EXPLAIN}</p>
	<table style='width:100%'>
	
	<tr>
		<td style='font-size:13px' align='right'>{RetranslatorHttpdPort}:</td>
		<td>". Field_text("W_RetranslatorHttpdPort",$_SESSION["WIZARD_RETRANS"]["RetranslatorHttpdPort"],"font-size:13px;padding:5px")."</td>
	</tr>	
		<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardRetransWgetShow()")."</td>
			<td width=50% align='right'>". button("{next}","WizardRetranSaveApache()")."</td>
		</tr>
	</table>
	</td></tr></table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


function popup_wget(){
	
	$regions=array("am","ar","at","az","be","bg","br","by","ca","cl","cn","cs","cz","de","ee","es","fr","gb","ge","gr","hk","hu","it","jp","kg","kr","kz","lt","lv","md","mx","nl","pl","ro","ru","th","tj","tm","tr","tw","ua","uk","us","uz");
	while (list ($num, $ligne) = each ($regions) ){
	$hash_regions[$ligne]=$ligne;
	}	
	
	$RetranslatorRegionSettings=Field_array_Hash($hash_regions,'w_RetranslatorRegionSettings',
	$_SESSION["WIZARD_RETRANS"]["RetranslatorRegionSettings"],null,null,0,"font-size:14px;padding:5px;");
	
	$html="
	<table style='width:100%'> 
	<tr>
		<td valign='top'><img src='img/96-wizard.png'>$intro</td>
		<td valign='top'>
	<H1>{WIZARD_CONFIGURE_DOWNLOAD_ENGINE}</H1>
	<p style='font-size:14px'>{WIZARD_WGET_EXPLAIN}</p>
	<table style='width:100%'>
	<tr>
		<td style='font-size:14px' align=right>{RetranslatorRegionSettings}:</td>
		<td>$RetranslatorRegionSettings</td>
	</tr>
	<tr>
		<td colspan=2><hr><H3 style='color:#005447;font-size:16px'>{RetranslatorProxyAddress}</H3></td>
	</tr>
	<tr>
		<td style='font-size:13px' align='right'>{servername}:</td>
		<td>". Field_text("PROXY_NAME",$_SESSION["WIZARD_RETRANS"]["PROXY_NAME"],"font-size:13px;padding:5px")."</td>
	</tr>	
	<tr>
		<td style='font-size:13px' align='right'>{listen_port}:</td>
		<td>". Field_text("PROXY_PORT",$_SESSION["WIZARD_RETRANS"]["PROXY_PORT"],"font-size:13px;padding:5px")."</td>
	</tr>
	<tr>
		<td style='font-size:13px' align='right'>{username}:</td>
		<td>". Field_text("PROXY_USR",$_SESSION["WIZARD_RETRANS"]["PROXY_USR"],"font-size:13px;padding:5px")."</td>
	</tr>		
	<tr>
		<td style='font-size:13px' align='right'>{password}:</td>
		<td>". Field_password("PROXY_PASS",$_SESSION["WIZARD"]["PROXY_PASS"],"font-size:13px;padding:5px")."</td>
	</tr>	
	</table>
		<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardRetransLoad()")."</td>
			<td width=50% align='right'>". button("{next}","WizardHTTPEngineSave()")."</td>
		</tr>
	</table>
	</td></tr></table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}


function popup(){
	if($_SESSION["WIZARD_RETRANS"]["W_WKS"]+$_SESSION["WIZARD_RETRANS"]["W_AK"]+$_SESSION["WIZARD_RETRANS"]["W_LNX"]==0){$_SESSION["WIZARD_RETRANS"]["W_LNX"]=1;}
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/96-wizard.png'>$intro</td>
		<td valign='top'>
	<H1>{WIZARD_RETRANSLATOR_WELCOME}</H1>
	<p style='font-size:14px'>{WIZARD_RETRANSLATOR_EXPLAIN}</p>
	
	<H3 style='color:#005447;font-size:16px'>{WIZARD_RETRANSLATOR_CHOOSE_COMPONENTS}</H3>
	<p style='font-size:14px'>{WIZARD_RETRANSLATOR_CHOOSE_COMPONENTS_EXPLAIN}</p>
	<table style='width:100%'>
	<tr>
		<td width=1%>". Field_checkbox("WKS",1,$_SESSION["WIZARD_RETRANS"]["W_WKS"])."</td>
		<td style='font-size:13px'>Kaspersky For Windows Workstation</td>
	</tr>
	<tr>
		<td width=1%>". Field_checkbox("AK",1,$_SESSION["WIZARD_RETRANS"]["W_AK"])."</td>
		<td style='font-size:13px'>Kaspersky Administration KIT</td>
	</tr>	
	<tr>
		<td width=1%>". Field_checkbox("LNX",1,$_SESSION["WIZARD_RETRANS"]["W_LNX"])."</td>
		<td style='font-size:13px'>Kaspersky Unix gateways</td>
	</tr>		
	</table>
	
	<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{cancel}","CancelRetransWizard()")."</td>
			<td width=50% align='right'>". button("{next}","WizardFamily()")."</td>
		</tr>
	</table>
	</td></tr></table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
		
	
	
}


	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$title=$tpl->_ENGINE_parse_body("{HELP_ME_RETRANSLATOR}");
	$WIZARD_CONFIGURE_DOWNLOAD_ENGINE=$tpl->_ENGINE_parse_body("{WIZARD_CONFIGURE_DOWNLOAD_ENGINE}");
	$WIZARD_COMPILE=$tpl->_ENGINE_parse_body("{WIZARD_COMPILE}");
	$WIZARD_HTTPD_ENGINE=$tpl->_ENGINE_parse_body("{WIZARD_HTTPD_ENGINE}");
	$WIZARD_FINISH=$tpl->_ENGINE_parse_body("{WIZARD_FINISH}");
	
	$html="
	
		function WizardRetransLoad(){YahooWin2(650,'$page?popup=yes','$title');}
		function WizardRetransWgetShow(){YahooWin2(650,'$page?popup-wget=yes','$WIZARD_CONFIGURE_DOWNLOAD_ENGINE');}
		function WizardApache(){YahooWin2(650,'$page?popup-apache=yes','$WIZARD_HTTPD_ENGINE');}
		function WizardFinish(){YahooWin2(650,'$page?popup-finish=yes','$WIZARD_FINISH');}
		

		
		function CancelBackupWizard(){
			YahooWinHide();
			var XHR = new XHRConnection();
			XHR.appendData('WIZARD_CANCEL','yes');
			XHR.sendAndLoad('$page', 'GET');			
		}
		
		var x_WizardFamily= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			WizardRetransWgetShow();
		 }

		var x_WizardHTTPEngineSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			WizardApache();
		 }
		var x_WizardRetranSaveApache= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			WizardFinish();
		 }	
		var x_WizardRetransCompile= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			RefreshTab('retranslator_main');
			YahooWin2Hide();
		 }
		 
		 
	 




function CloseTimeOut(){
		Loadjs('domains.manage.org.index.php?js=yes&ou='+document.getElementById('ou').value);
	 	YahooWinHide();
	 	
	}

	function WizardFamily(){
			var XHR = new XHRConnection();
			if(document.getElementById('WKS').checked){XHR.appendData('W_WKS',1);}else{XHR.appendData('W_WKS',0);}
			if(document.getElementById('AK').checked){XHR.appendData('W_AK',1);}else{XHR.appendData('W_AK',0);}
			if(document.getElementById('LNX').checked){XHR.appendData('W_LNX',1);}else{XHR.appendData('W_LNX',0);}			
			XHR.sendAndLoad('$page', 'GET',x_WizardFamily);
			
		}
		
	function WizardHTTPEngineSave(){
			var XHR = new XHRConnection();
			XHR.appendData('PROXY_PASS',document.getElementById('PROXY_PASS').value);
			XHR.appendData('PROXY_USR',document.getElementById('PROXY_USR').value);
			XHR.appendData('PROXY_PORT',document.getElementById('PROXY_PORT').value);
			XHR.appendData('PROXY_NAME',document.getElementById('PROXY_NAME').value);
			XHR.appendData('RetranslatorRegionSettings',document.getElementById('w_RetranslatorRegionSettings').value);
			XHR.sendAndLoad('$page', 'GET',x_WizardHTTPEngineSave);
			
		}
		
	function WizardRetranSaveApache(){
			var XHR = new XHRConnection();
			XHR.appendData('RetranslatorHttpdPort',document.getElementById('W_RetranslatorHttpdPort').value);
			XHR.sendAndLoad('$page', 'GET',x_WizardRetranSaveApache);	
	}
	
	
	
	function WizardRetransCompile(){
		var XHR = new XHRConnection();
			XHR.appendData('WizardRetransCompile','yes');
			XHR.sendAndLoad('$page', 'GET',x_WizardRetransCompile);	
	}
	     

		  
	
	
	WizardRetransLoad();";
	
	echo $html;
}	

function popup_finish(){
	
	$tpl=new templates();
	if($_SESSION["WIZARD_RETRANS"]["PROXY_NAME"]<>null){
		$proxy="{$_SESSION["WIZARD_RETRANS"]["PROXY_NAME"]}:{$_SESSION["WIZARD_RETRANS"]["PROXY_PORT"]}";
	}else{
		$proxy="{no}";
	}
	
	if($_SESSION["WIZARD_RETRANS"]["W_WKS"]==1){$products[]="Kaspersky For Windows Workstation";}
	if($_SESSION["WIZARD_RETRANS"]["W_AK"]==1){$products[]="Kaspersky Administration KIT";}
	if($_SESSION["WIZARD_RETRANS"]["W_LNX"]==1){$products[]="Kaspersky Unix gateways";}

			
	$intro="
	<div style='color:#005447;font-size:13px'>{WIZARD_RETRANSLATOR_CHOOSE_COMPONENTS}: ". implode(", ",$products)."</div>
	<div style='color:#005447;font-size:13px'>{RetranslatorRegionSettings}: {$_SESSION["WIZARD_RETRANS"]["RetranslatorRegionSettings"]}</div>
	<div style='color:#005447;font-size:13px'>{RetranslatorProxyAddress}: {$_SESSION["WIZARD_RETRANS"]["RetranslatorRegionSettings"]}</div>
	<div style='color:#005447;font-size:13px'>{RetranslatorHttpdPort}: {$_SESSION["WIZARD_RETRANS"]["RetranslatorHttpdPort"]}</div>
	
	
	";

	if(isset($_GET["RetranslatorHttpdPort"])){$_SESSION["WIZARD_RETRANS"]["RetranslatorHttpdPort"]=$_GET["RetranslatorHttpdPort"];exit;}	
	
	
	$html="
		<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/96-wizard.png'></td>
		<td valign='top'>
	
<H3 style='color:#005447;font-size:16px'>{WIZARD_COMPILE}</H3>
	<p style='font-size:14px'>{WIZARD_COMPILE_EXPLAIN}</p>$intro
	<hr>
		<center>
			". button("{WIZARD_COMPILE}","WizardRetransCompile()")."
		</center>
<hr>
	<table style='width:100%'>
		<tr>
			<td width=50%>". button("{back}","WizardApache()")."</td>
			<td width=50% align='right'>". button("{WIZARD_COMPILE}","WizardRetransCompile()")."</td>
		</tr>
	</table>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"wizard.kaspersky.appliance.php");	
}

function compile(){
	$retranslator=new retranslator();
	$retranslator->httpd_enabled=1;
	$retranslator->RetranslatorHttpdEnabled=1;
	$retranslator->RetranslatorHttpdPort=$_SESSION["WIZARD_RETRANS"]["RetranslatorHttpdPort"];
	$retranslator->RetranslatorEnabled=1;
	$retranslator->RetranslatorRegionSettings=$_SESSION["WIZARD_RETRANS"]["RetranslatorRegionSettings"];
	
	if(trim($_SESSION["WIZARD_RETRANS"]["PROXY_NAME"])<>null){
		if(trim($_SESSION["WIZARD_RETRANS"]["PROXY_USR"]<>null)){
			$user="{$_SESSION["WIZARD_RETRANS"]["PROXY_USR"]}:{$_SESSION["WIZARD_RETRANS"]["PROXY_PASS"]}@";
		}
		
		$retranslator->RetranslatorProxyAddress="http://$user{$_SESSION["WIZARD_RETRANS"]["PROXY_NAME"]}:{$_SESSION["WIZARD_RETRANS"]["PROXY_PORT"]}/";
		$retranslator->RetranslatorUseProxy="yes";
	}else{
		$retranslator->RetranslatorUseProxy="no";
	}
	
	$pr["AVS"]=true;
	$pr["CORE"]=true;
	$pr["BLST"]=true;
	$pr["UPDATER"]=true;
	
	

	if($_SESSION["WIZARD_RETRANS"]["W_WKS"]==1){
			$pr["UPDATER"]=true;
			$pr["AVS"]=true;
			$pr["KDB"]=true;
			$pr["ARK"]=true;
			$pr["ADB"]=true;
			$pr["ADBU"]=true;
			$pr["AH"]=true;
			$pr["APU"]=true;
			$pr["BLST2"]=true;
			$pr["WMUF"]=true;
			$pr["WA"]=true;
			$pr["EMU"]=true;
			$pr["PAS"]=true;
			$pr["ASTRM"]=true;
			$pr["AHI386"]=true;
			$pr["AHX64"]=true;
			$pr["AP"]=true;
			$pr["AS"]=true;
			$pr["BB"]=true;
			$pr["BB2"]=true;
			$pr["CORE"]=true;
			$pr["KAV2006EXEC"]=true;
			$pr["NEWS"]=true;
			$pr["RM"]=true;
			$pr["RT"]=true;
			$pr["WM"]=true;
			$pr["AK6"]=true;
			$pr["INDEX60"]=true;
	}
	
	if($_SESSION["WIZARD_RETRANS"]["W_AK"]==1){
			$pr["UPDATER"]=true;
			$pr["CORE"]=true;
			$pr["BLST"]=true;
			$pr["AKP8"]=true;
			$pr["RT"]=true;
			$pr["RTAK7"]=true;
			$pr["AK7"]=true;
			$pr["AK6"]=true;
			$pr["INDEX60"]=true;
		}

if($_SESSION["WIZARD_RETRANS"]["W_LNX"]==1){	

			$pr["UPDATER"]=true;
			$pr["AVS"]=true;
			$pr["AVS_OLD"]=true;
			$pr["CORE"]=true;
			$pr["BLST"]=true;
			$pr["RT"]=true;
			$pr["AK6"]=true;
			$pr["INDEX60"]=true;
			$pr["INDEX50"]=true;
	}
	
	
	$retranslator->MyRetranslateComponentsList=$pr;
	$retranslator->SaveToServer();
 
	
}
	
?>