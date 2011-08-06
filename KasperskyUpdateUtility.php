<?php
	if(isset($_GET["debug-page"])){	ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.retranslator.inc');
	include_once('ressources/class.ini.inc');
	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$tpl=new templates();
		$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$ERROR_NO_PRIVS');";return;
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["settings"])){settings();exit;}
	if(isset($_GET["EnableKUpdateUtility"])){SaveEnable();exit;}
	if(isset($_GET["UseProxyServer"])){SaveConnx();exit;}
	if(isset($_GET["products"])){products();exit;}
	if(isset($_POST["KasperskyAntiVirus_7_0_0124_1325"])){SaveProducts();exit;}
	
	js();
	
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_KASPERSKY_UPDATE_UTILITY}");
	echo "YahooWin3(570,'$page?popup=yes','$title');";
}	

function SaveEnable(){
	$sock=new sockets();
	$sock->SET_INFO("EnableKUpdateUtility",$_GET["EnableKUpdateUtility"]);
}


function popup(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();
	$array["index"]='{index}';
	$array["settings"]="{parameters}";
	$array["products"]="{kaspersky_products}";

// Total downloaded: 100%, Result: Retranslation successful and update is not requested
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		$tab[]="<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
			
	}

	$html="
		<div id='main_upateutility_config' style='background-color:white'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_upateutility_config').tabs();
				});
		</script>
	
	";
		
	
	echo $tpl->_ENGINE_parse_body($html);
	
}




function index(){
	$sock=new sockets();
	$EnableKUpdateUtility=$sock->GET_INFO("EnableKUpdateUtility");
	if($EnableKUpdateUtility==null){$EnableKUpdateUtility=0;}
	$page=CurrentPageName();
	$tpl=new templates();
	
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'><div class=explain>{APP_KASPERSKY_UPDATE_UTILITY_EXPLAIN}</div></td>
		<td valign='top'><div id='kupdsets'>
		". Paragraphe_switch_img("{ENABLE_KUPDUTILITY}","{ENABLE_KUPDUTILITY_TEXT}","EnableKUpdateUtility",$EnableKUpdateUtility).
		"<hr>
			<div style='text-align:right'>". button("{apply}","SaveEnableKUpdte()")."</div>
			</div>
		
		</td>
	</tr>
	</table>
	
	<script>
	
	var x_SaveEnableKUpdte= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_upateutility_config');
	}	
	
		function SaveEnableKUpdte(){
		var XHR = new XHRConnection();
		XHR.appendData('EnableKUpdateUtility',document.getElementById('EnableKUpdateUtility').value);
		document.getElementById('kupdsets').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveEnableKUpdte);
		
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function products(){
	$updt=new UpdateUtility();
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	
	while (list ($num, $ligne) = each ($updt->main_array) ){
		while (list ($a, $value) = each ($ligne) ){
			if($value=="true"){$updt->main_array[$num][$a]=1;}
			if($value=="false"){$updt->main_array[$num][$a]=0;}
		}
		
	}

$EnableKUpdateUtility=$sock->GET_INFO("EnableKUpdateUtility");
	if($EnableKUpdateUtility==null){$EnableKUpdateUtility=0;}

	$html="
	<div id='NavigationForms2'>
		<h3><a href=\"#\">{home_products}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>Kaspersky AntiVirus 7.0 0124-1325:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_7_0_0124_1325",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_7_0_0124_1325"])."</td>
			</tr>		
			<tr>
				<td class=legend>Kaspersky AntiVirus 8.0.0.357-523:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_8_0_0_357_523",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_8_0_0_357_523"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus 8.0.2.460:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_8_0_2_460",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_8_0_2_460"])."</td>
			</tr>			
			<tr>
				<td class=legend> Kaspersky AntiVirus 9.0.0-459:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_9_0_0_459",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_9_0_0_459"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus 9.0.0-463:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_9_0_0_463",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_9_0_0_463"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus 9.0.0-736:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_9_0_0_736",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_9_0_0_736"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus 11.0.0-232:</td>
				<td>". Field_checkbox("KasperskyAntiVirus_11_0_0_232",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirus_11_0_0_232"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Internet Security 7.0.0.0-124-1325:</td>
				<td>". Field_checkbox("KasperskyInternetSecurrity_7_0_0_0124_1325",1,$updt->main_array["ComponentSettings"]["KasperskyInternetSecurrity_7_0_0_0124_1325"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky Internet Security 8.0.0.357-523:</td>
				<td>". Field_checkbox("KasperskyInternetSecurrity_8_0_0_357_523",1,$updt->main_array["ComponentSettings"]["KasperskyInternetSecurrity_8_0_0_357_523"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Internet Security 9.0.0.459:</td>
				<td>". Field_checkbox("KasperskyInternetSecurrity_9_0_0_459",1,$updt->main_array["ComponentSettings"]["KasperskyInternetSecurrity_9_0_0_459"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Internet Security 9.0.0.463:</td>
				<td>". Field_checkbox("KasperskyInternetSecurrity_9_0_0_463",1,$updt->main_array["ComponentSettings"]["KasperskyInternetSecurrity_9_0_0_463"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky Internet Security 9.0.0.736:</td>
				<td>". Field_checkbox("KasperskyInternetSecurrity_9_0_0_736",1,$updt->main_array["ComponentSettings"]["KasperskyInternetSecurrity_9_0_0_736"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Internet Security 11.0.0.232:</td>
				<td>". Field_checkbox("KasperskyInternetSecurrity_11_0_0_232",1,$updt->main_array["ComponentSettings"]["KasperskyInternetSecurrity_11_0_0_232"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky Pure 9.0.0.192-199:</td>
				<td>". Field_checkbox("KasperskyPure_9_0_0_192_199",1,$updt->main_array["ComponentSettings"]["KasperskyPure_9_0_0_192_199"])."</td>
			</tr>
	
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtProducts()")."</td>
			</tr>			
		</table>
	</div>			
	
	

<h3><a href=\"#\">{workstations}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>Kaspersky AntiVirus For Windows Workstation 6.0.2.678-690:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsWorkstation_6_0_2_678_690",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsWorkstation_6_0_2_678_690"])."</td>
			</tr>		
			<tr>
				<td class=legend>Kaspersky AntiVirus For Windows Workstation 6.0.3.830-837:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsWorkstation_6_0_3_830_837",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsWorkstation_6_0_3_830_837"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Windows Workstation 6.0.4.1212:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsWorkstation_6_0_4_1212",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsWorkstation_6_0_4_1212"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Windows Workstation 6.0.4.1424:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsWorkstation_6_0_4_1424",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsWorkstation_6_0_4_1424"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus SOS 5.0.712:</td>
				<td>". Field_checkbox("KasperskyAntiVirusSOS_5_0_712",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusSOS_5_0_712"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus SOS 6.0.3.837:</td>
				<td>". Field_checkbox("KasperskyAntiVirusSOS_6_0_3_837",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusSOS_6_0_3_837"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus SOS 6.0.4.1212:</td>
				<td>". Field_checkbox("KasperskyAntiVirusSOS_6_0_4_1212",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusSOS_6_0_4_1212"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus SOS 6.0.4.1424:</td>
				<td>". Field_checkbox("KasperskyAntiVirusSOS_6_0_4_1424",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusSOS_6_0_4_1424"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus Linux Workstation 5.7.17-26:</td>
				<td>". Field_checkbox("KasperskyAntiVirusLinuxFileServerWorkstation_5_7_17_26",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusLinuxFileServerWorkstation_5_7_17_26"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Endpoint Security For Mac OSX 8:</td>
				<td>". Field_checkbox("KasperskyEndpointSecurityForMacOSX_8",1,$updt->main_array["ComponentSettings"]["KasperskyEndpointSecurityForMacOSX_8"])."</td>
			</tr>
																																																																						<tr>
				<td class=legend>Kaspersky Endpoint Security For Linux 8:</td>
				<td>". Field_checkbox("KasperskyEndpointSecurityForLinux_8",1,$updt->main_array["ComponentSettings"]["KasperskyEndpointSecurityForLinux_8"])."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtProducts()")."</td>
			</tr>			
		</table>
	</div>
	
	
<h3><a href=\"#\">{fileservers}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server 6.0.2.678-690:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServer_6_0_2_678_690",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServer_6_0_2_678_690"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server 6.0.3.830-837:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServer_6_0_3_830_837",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServer_6_0_3_830_837"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server 6.0.4.1212:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServer_6_0_4_1212",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServer_6_0_4_1212"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server 6.0.4.1424:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServer_6_0_4_1424",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServer_6_0_4_1424"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server EE 6.0.0.454:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServerEE_6_0_0_454",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServerEE_6_0_0_454"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server EE 6.0.1.511:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServerEE_6_0_1_511",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServerEE_6_0_1_511"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server EE 6.0.2.551-555:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServerEE_6_0_2_551_555",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServerEE_6_0_2_551_555"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus Windows Server EE 8.0:</td>
				<td>". Field_checkbox("KasperskyAntiVirusWindowsServerEE_8_0",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusWindowsServerEE_8_0"])."</td>
			</tr>																								
			<tr>
				<td class=legend>Kaspersky AntiVirus Linux File Server 5.7.17-26:</td>
				<td>". Field_checkbox("KasperskyAntiVirusLinuxFileServerWorkstation_5_7_17_26",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusLinuxFileServerWorkstation_5_7_17_26"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Anti Virus Linux FileServer 8:</td>
				<td>". Field_checkbox("KasperskyAntiVirusLinuxFileServerWorkstation_8",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusLinuxFileServerWorkstation_8"])."</td>
			</tr>			
			<tr>
				<td class=legend>Kaspersky AntiVirus For Samba Server 5.5.9-14:</td>
				<td>". Field_checkbox("KasperskyAntiVirusSambaServer_5_5_9_14",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusSambaServer_5_5_9_14"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Unix FileServer 5.5.27-4:</td>
				<td>". Field_checkbox("KasperskyAntiVirusUnixFileServer_5_5_27_4",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusUnixFileServer_5_5_27_4"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Anti Virus For Novell NetWare 5.7.34-38:</td>
				<td>". Field_checkbox("KasperskyAntiVirusNovellNetWare_5_7_34_38",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusNovellNetWare_5_7_34_38"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Novell NetWare_5.7.5:</td>
				<td>". Field_checkbox("KasperskyAntiVirusNovellNetWare_5_7_5",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusNovellNetWare_5_7_5"])."</td>
			</tr>	
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtProducts()")."</td>
			</tr>			
		</table>
	</div>																																								

<h3><a href=\"#\">{messaging}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>Kaspersky Security Microsoft Exchange Server 2003 5.5.1354.0:</td>
				<td>". Field_checkbox("KasperskySecurityMicrosoftExchangeServer2003_5_5_1354_0",1,$updt->main_array["ComponentSettings"]["KasperskySecurityMicrosoftExchangeServer2003_5_5_1354_0"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky Security Microsoft Exchange Server 2003 5.5.1388.0:</td>
				<td>". Field_checkbox("KasperskySecurityMicrosoftExchangeServer2003_5_5_1388_0",1,$updt->main_array["ComponentSettings"]["KasperskySecurityMicrosoftExchangeServer2003_5_5_1388_0"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Security Microsoft Exchange Server 2007 6.0.715:</td>
				<td>". Field_checkbox("KasperskySecurityMicrosoftExchangeServer2007_6_0_715",1,$updt->main_array["ComponentSettings"]["KasperskySecurityMicrosoftExchangeServer2007_6_0_715"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Security Microsoft Exchange Server 2007 6.0.744-1512:</td>
				<td>". Field_checkbox("KasperskySecurityMicrosoftExchangeServer2007_6_0_744_1512",1,$updt->main_array["ComponentSettings"]["KasperskySecurityMicrosoftExchangeServer2007_6_0_744_1512"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Security Microsoft Exchange Server 5.5.1185.0:</td>
				<td>". Field_checkbox("KasperskySecurityMicrosoftExchangeServer_5_5_1185_0",1,$updt->main_array["ComponentSettings"]["KasperskySecurityMicrosoftExchangeServer_5_5_1185_0"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky Security Microsoft Exchange Server 8.0:</td>
				<td>". Field_checkbox("KasperskySecurityMicrosoftExchangeServer_8_0",1,$updt->main_array["ComponentSettings"]["KasperskySecurityMicrosoftExchangeServer_8_0"])."</td>
			</tr>			
			<tr>
				<td class=legend>Kaspersky AntiVirus Lotus Notes Domino 5.5.1392:</td>
				<td>". Field_checkbox("KasperskyAntiVirusLotusNotesDomino_5_5_1392",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusLotusNotesDomino_5_5_1392"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus Lotus Notes Domino 5.5.1392:</td>
				<td>". Field_checkbox("KasperskyAntiVirusLotusNotesDomino_5_5_1392",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusLotusNotesDomino_5_5_1392"])."</td>
			</tr>					
			<tr>
				<td class=legend>Kaspersky AntiVirus Lotus Notes Domino 8.0:</td>
				<td>". Field_checkbox("KasperskyAntiVirusLotusNotesDomino_8_0",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusLotusNotesDomino_8_0"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus For Unix Mail Servers 5.5.33:</td>
				<td>". Field_checkbox("KasperskyAntiVirusUnixMailServers_5_5_33",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusUnixMailServers_5_5_33"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky SendMail Milter Api 5.5.20-2:</td>
				<td>". Field_checkbox("KasperskySendMailMilterApi_5_5_20_2",1,$updt->main_array["ComponentSettings"]["KasperskySendMailMilterApi_5_5_20_2"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky MailGateway 5.6.28.0:</td>
				<td>". Field_checkbox("KasperskyMailGateway_5_6_28_0",1,$updt->main_array["ComponentSettings"]["KasperskyMailGateway_5_6_28_0"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiSpam 3.0.278.4:</td>
				<td>". Field_checkbox("KasperskyAntiSpam_3_0_278_4",1,$updt->main_array["ComponentSettings"]["KasperskyAntiSpam_3_0_278_4"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiSpam 3.0.284.1:</td>
				<td>". Field_checkbox("KasperskyAntiSpam_3_0_284_1",1,$updt->main_array["ComponentSettings"]["KasperskyAntiSpam_3_0_284_1"])."</td>
			</tr>	
		<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtProducts()")."</td>
			</tr>	
					
		</table>
	</div>	
	
	
<h3><a href=\"#\">{proxiesandFirewall}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>Kaspersky AntiVirus For Microsoft Isa Servers 2000 SE EE:</td>
				<td>". Field_checkbox("KasperskyAntiVirusMicrosoftIsaServers_2000_SE_EE",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusMicrosoftIsaServers_2000_SE_EE"])."</td>
			</tr>	
			<tr>
				<td class=legend>Kaspersky AntiVirus For Microsoft Isa Servers 2004-2006 SE EE:</td>
				<td>". Field_checkbox("KasperskyAntiVirusMicrosoftIsaServers_2004_2006_SE_EE",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusMicrosoftIsaServers_2004_2006_SE_EE"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Microsoft Isa Servers 2006 TMG SE:</td>
				<td>". Field_checkbox("KasperskyAntiVirusMicrosoftIsaServers_2006_TMG_SE",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusMicrosoftIsaServers_2006_TMG_SE"])."</td>
			</tr>			
			<tr>
				<td class=legend>Kaspersky AntiVirus For CheckPoint Firewall1 5.5.161:</td>
				<td>". Field_checkbox("KasperskyAntiVirusCheckPointFirewall1_5_5_161",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusCheckPointFirewall1_5_5_161"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Proxy Server 5.5.41-51:</td>
				<td>". Field_checkbox("KasperskyAntiVirusProxyServer_5_5_41_51",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusProxyServer_5_5_41_51"])."</td>
			</tr>
			<tr>
				<td class=legend>Kaspersky AntiVirus For Proxy Server 5.5.62:</td>
				<td>". Field_checkbox("KasperskyAntiVirusProxyServer_5_5_62",1,$updt->main_array["ComponentSettings"]["KasperskyAntiVirusProxyServer_5_5_62"])."</td>
			</tr>	

		<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtProducts()")."</td>
			</tr>	
					
		</table>
	</div>	
	
<h3><a href=\"#\">{administration_tools}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>Kaspersky Administration Kit 6.0.1405-1710:</td>
				<td>". Field_checkbox("KasperskyAdministrationKit_6_0_1405_1710",1,$updt->main_array["ComponentSettings"]["KasperskyAdministrationKit_6_0_1405_1710"])."</td>
			</tr>	
			<tr>
				<td class=legend>KasperskyAdministrationKit 8.0.2048-2090:</td>
				<td>". Field_checkbox("KasperskyAdministrationKit_8_0_2048_2090",1,$updt->main_array["ComponentSettings"]["KasperskyAdministrationKit_8_0_2048_2090"])."</td>
			</tr>				
		<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtProducts()")."</td>
			</tr>	
					
		</table>
	</div>
</div>

	
<script>
		$(function() {
			$( \"#NavigationForms2\" ).accordion({autoHeight: false,navigation: true});});
			
	var x_SaveKUpdtProducts= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_upateutility_config');
	}				
			
	function SaveKUpdtProducts(){
		var XHR=XHRParseElements('NavigationForms2');
		document.getElementById('NavigationForms2').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_SaveKUpdtProducts);
	}

	function CheckProductsForm(){
		DisableFieldsFromId('NavigationForms2');
		var EnableKUpdateUtility=$EnableKUpdateUtility;
		if(EnableKUpdateUtility==0){return;}
		EnableFieldsFromId('NavigationForms2');
	
	}
CheckProductsForm();
</script>

";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function settings(){
	
	$updt=new UpdateUtility();
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	
	while (list ($num, $ligne) = each ($updt->main_array) ){
		while (list ($a, $value) = each ($ligne) ){
			if($value=="true"){$updt->main_array[$num][$a]=1;}
			if($value=="false"){$updt->main_array[$num][$a]=0;}
		}
		
	}
	
	$EnableKUpdateUtility=$sock->GET_INFO("EnableKUpdateUtility");
	if($EnableKUpdateUtility==null){$EnableKUpdateUtility=0;}
	$html="
	<div id='NavigationForms'>
		<h3><a href=\"#\">{proxy_settings}</a></h3>
		<div>	
		<table style='width:100%' class=form>
	
			<tr>
				<td class=legend>{UseProxyServer}:</td>
				<td>". Field_checkbox("UseProxyServer",1,$updt->main_array["ConnectionSettings"]["UseProxyServer"],'UpdateUtilityCheckEnabled()')."</td>
			</tr>		
			<tr>
				<td class=legend>{AddressProxyServer}:</td>
				<td>". Field_text("AddressProxyServer",$updt->main_array["ConnectionSettings"]["AddressProxyServer"],"width:120px;font-size:13px;padding:3px")."</td>
			</tr>	
			<tr>
				<td class=legend>{PortProxyServer}:</td>
				<td>". Field_text("PortProxyServer",$updt->main_array["ConnectionSettings"]["PortProxyServer"],"width:90px;font-size:13px;padding:3px")."</td>
			</tr>	
			<tr>
				<td class=legend>{UseAuthenticationProxyServer}:</td>
				<td>". Field_checkbox("UseAuthenticationProxyServer",1,$updt->main_array["ConnectionSettings"]["UseAuthenticationProxyServer"],"UpdateUtilityCheckEnabled()")."</td>
			</tr>
			<tr>
				<td class=legend>{username}:</td>
				<td>". Field_text("UserNameProxyServer",$updt->main_array["ConnectionSettings"]["UserNameProxyServer"],"width:120px;font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend>{password}:</td>
				<td>". Field_text("PasswordProxyServer",$updt->main_array["ConnectionSettings"]["PasswordProxyServer"],"width:120px;font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtConx()")."</td>
			</tr>
			
			</table>
			
			</div>
			
			<h3><a href=\"#\">{folders}</a></h3>
			<div>	
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{UpdatesFolder}:</td>
				<td>". Field_text("UpdatesFolder",$updt->main_array["DirectoriesSettings"]["UpdatesFolder"],"width:220px;font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend>{TempFolder}:</td>
				<td>". Field_text("TempFolder",$updt->main_array["DirectoriesSettings"]["TempFolder"],"width:220px;font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtConx()")."</td>
			</tr>			
			</table>
			</div>
			<h3><a href=\"#\">{sources}</a></h3>
			<div>				
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{UsePassiveFtpMode}:</td>
				<td>". Field_checkbox("UsePassiveFtpMode",1,$updt->main_array["ConnectionSettings"]["UsePassiveFtpMode"])."</td>
			</tr>			
			<tr>
				<td class=legend>{Connection timeout}:</td>
				<td>". Field_text("TimeoutConnection",$updt->main_array["ConnectionSettings"]["TimeoutConnection"],"width:60px;font-size:13px;padding:3px")."</td>
			</tr>			
			<tr>
				<td class=legend>{SourceKlabServer}:</td>
				<td>". Field_checkbox("SourceKlabServer",1,$updt->main_array["UpdatesSourceSettings"]["SourceKlabServer"],"UpdateUtilityCheckEnabled()")."</td>
			</tr>	
			
			<tr>
				<td class=legend>{SourceCustom}:</td>
				<td>". Field_checkbox("SourceCustom",1,$updt->main_array["UpdatesSourceSettings"]["SourceCustom"])."</td>
			</tr>			
			<tr>
				<td class=legend>{SourceCustomPath}:</td>
				<td>". Field_text("SourceCustomPath",$updt->main_array["UpdatesSourceSettings"]["SourceCustomPath"],"width:220px;font-size:13px;padding:3px")."</td>
			</tr>				
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveKUpdtConx()")."</td>
			</tr>			
			
			
			</table>
			</div>
		</div>
	
<script>
		$(function() {
			$( \"#NavigationForms\" ).accordion({autoHeight: false,navigation: true});});
			
	var x_SaveKUpdtConx= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_upateutility_config');
	}				
			
	function SaveKUpdtConx(){
		var XHR=XHRParseElements('NavigationForms');
		document.getElementById('NavigationForms').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveKUpdtConx);
	}
			
	function UpdateUtilityCheckEnabled(){
		DisableFieldsFromId('NavigationForms');
		var EnableKUpdateUtility=$EnableKUpdateUtility;
		if(EnableKUpdateUtility==0){return;}
		EnableFieldsFromId('NavigationForms');
		document.getElementById('SourceCustom').disabled=true;
		
		if(document.getElementById('SourceKlabServer').checked){
			document.getElementById('SourceCustom').checked=false;
			document.getElementById('SourceCustomPath').disabled=true;
		}else{
			document.getElementById('SourceCustom').checked=true;
			document.getElementById('SourceCustomPath').disabled=false;
		}
		
		document.getElementById('AddressProxyServer').disabled=true;
		document.getElementById('PortProxyServer').disabled=true;
		document.getElementById('UseAuthenticationProxyServer').disabled=true;
		document.getElementById('UserNameProxyServer').disabled=true;
		document.getElementById('PasswordProxyServer').disabled=true;
		if(document.getElementById('UseProxyServer').checked){
			document.getElementById('AddressProxyServer').disabled=false;
			document.getElementById('PortProxyServer').disabled=false;
			document.getElementById('UseAuthenticationProxyServer').disabled=false;
		}
		
		if(document.getElementById('UseAuthenticationProxyServer').checked){
			document.getElementById('UserNameProxyServer').disabled=false;
			document.getElementById('PasswordProxyServer').disabled=false;		
		}
	}
	
	UpdateUtilityCheckEnabled();
			
</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}


function SaveConnx(){
	
	$updt=new UpdateUtility();
	
	if(is_numeric($_GET["TimeoutConnection"])){
		$updt->main_array["ConnectionSettings"]["TimeoutConnection"]=$_GET["TimeoutConnection"];
	}	
	
	if(isset($_GET["UsePassiveFtpMode"])){
		if($_GET["UsePassiveFtpMode"]==1){$updt->main_array["ConnectionSettings"]["UsePassiveFtpMode"]="true";}else{$updt->main_array["ConnectionSettings"]["UsePassiveFtpMode"]="false";}
	}
	
	if(isset($_GET["SourceKlabServer"])){
		if($_GET["SourceKlabServer"]==1){$updt->main_array["UpdatesSourceSettings"]["SourceKlabServer"]="true";}else{$updt->main_array["UpdatesSourceSettings"]["SourceKlabServer"]="false";}
	}	
	
	if(isset($_GET["UseAuthenticationProxyServer"])){
		if($_GET["UseAuthenticationProxyServer"]==1){$updt->main_array["ConnectionSettings"]["UseAuthenticationProxyServer"]="true";}else{$updt->main_array["ConnectionSettings"]["UseAuthenticationProxyServer"]="false";}
	}	
	
	if(isset($_GET["UseProxyServer"])){
		if($_GET["UseProxyServer"]==1){
			$updt->main_array["ConnectionSettings"]["UseProxyServer"]="true";
			$updt->main_array["ConnectionSettings"]["UseSpecifiedProxyServerSettings"]="true";
		}else{
			$updt->main_array["ConnectionSettings"]["UseProxyServer"]="false";
			$updt->main_array["ConnectionSettings"]["UseSpecifiedProxyServerSettings"]="false";
			$updt->main_array["ConnectionSettings"]["ByPassProxyServer"]="true";
		}
	}

	if(isset($_GET["AddressProxyServer"])){$updt->main_array["ConnectionSettings"]["AddressProxyServer"]=$_GET["AddressProxyServer"];}	
	if(isset($_GET["UserNameProxyServer"])){$updt->main_array["ConnectionSettings"]["UserNameProxyServer"]=$_GET["UserNameProxyServer"];}
	if(isset($_GET["PasswordProxyServer"])){$updt->main_array["ConnectionSettings"]["PasswordProxyServer"]=$_GET["PasswordProxyServer"];}
	if(isset($_GET["UpdatesFolder"])){
		$updt->main_array["DirectoriesSettings"]["UpdatesFolder"]=$_GET["UpdatesFolder"];
		$updt->main_array["DirectoriesSettings"]["MoveToCustomFolder"]="true";
		$updt->main_array["DirectoriesSettings"]["MoveToCurrentFolder"]="false";
		$updt->main_array["DirectoriesSettings"]["ClearTempFolder"]="true";
	}
	if(isset($_GET["TempFolder"])){$updt->main_array["DirectoriesSettings"]["TempFolder"]=$_GET["TempFolder"];}
	if(is_numeric($_GET["PortProxyServer"])){$updt->main_array["ConnectionSettings"]["PortProxyServer"]=$_GET["PortProxyServer"];}

	$updt->Save();
	
}

function SaveProducts(){
	$updt=new UpdateUtility();
	
	while (list ($num, $ligne) = each ($_POST) ){
		if($_POST[$num]==1){$_POST[$num]="true";};
		if($_POST[$num]==0){$_POST[$num]="false";};
		if(isset($updt->main_array["ComponentSettings"][$num])){$updt->main_array["ComponentSettings"][$num]=$_POST[$num];}
	}
	$updt->Save();
	
}



	
	
