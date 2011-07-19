<?php
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.openvpn.inc");


if(isset($_GET["config-js"])){config_js();exit;}
if(isset($_GET["config-popup"])){config_popup();exit;}
if(isset($_POST["ComputerOS"])){config_popup_save();exit;}

$page=CurrentPageName();
$tpl=new templates();
//users.openvpn.index.php

$downloadvinvin=Paragraphe("setup-icon-64.png",
		"{DOWNLOAD_OPENVPN_CLIENT}",
		"{DOWNLOAD_OPENVPN_CLIENT_TEXT}",
		"javascript:s_PopUp('http://www.artica.fr/download/openvpn-2.1.4-install.exe')",
		"{DOWNLOAD_OPENVPN_CLIENT_TEXT}");

$downloadapple=Paragraphe("apple-logo-64.png",
		"{DOWNLOAD_OPENVPN_CLIENT_APPLE}",
		"{DOWNLOAD_OPENVPN_CLIENT_TEXT}",
		"javascript:s_PopUp('http://www.artica.fr/download/Tunnelblick_3.1.7.dmg')",
		"{DOWNLOAD_OPENVPN_CLIENT_TEXT}");

$build=Paragraphe("user-config-download-64.png",
		"{DOWNLOAD_CONFIG_FILES}",
		"{DOWNLOAD_CONFIG_FILES_TEXT}",
		"javascript:Loadjs('$page?config-js=yes')",
		"{DOWNLOAD_CONFIG_FILES}");

	
	

$html="
<div style='font-size:18px'>{WELCOME_USER_OPENVPN}</div>
<p>&nbsp;</p>
<center>
<table style='width:80%' class=form>
			<tr>
				<td width=50px align='center'><p>&nbsp;</p>$downloadvinvin<p>&nbsp;</p></td>
				<td width=50px align='center'><p>&nbsp;</p>$downloadapple<p>&nbsp;</p></td>
			</tr>
			<tr>
				<td width=50px align='center'><p>&nbsp;</p>$build<p>&nbsp;</p></td>
				<td>&nbsp;</td>
			</tr>

		</table>
</center>

<script>
	LoadAjax('tool-map','miniadm.toolbox.php?script=". urlencode($page)."');
</script>

";

echo $tpl->_ENGINE_parse_body($html);

function config_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{DOWNLOAD_CONFIG_FILES}');
	$html="YahooWin('600','$page?config-popup=yes','$title');";
	echo $html;
}

function config_popup(){
	$tpl=new templates();
	$page=CurrentPageName();	
	
	$os["windowsXP"]="Windows XP";
	$os["windows2003"]="Windows 2003/7";
	$os["linux"]="Linux";
	$os["mac"]="OS X 10.4, 10.5, 10.6";
	$os[null]="{select}";
	
	$os=Field_array_Hash($os,"ComputerOS",null,"style:font-size:18px;padding:3px");
	
	$html="<div class=explain id='buildclientconfigdiv'>{BUILD_OPENVPN_CLIENT_CONFIG_TEXT}</div>
	
	<center>
		<table style='width:75%' class=form>
		<tr>
		<tr>
			<td class=legend style='font-size:16px'>{ComputerOS}:</td>
			<td class=legend style='font-size:16px'>$os</td>
		</tr>		
		<tr>
			<td colspan=2 align='right'><hr>". button("{generate_parameters}","GenerateVPNConfig()")."</td>
		</tr>
		</table>
	</center>
	<div id='generate-vpn-events'></div>
	<script>
			
	var x_GenerateVPNConfig= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){document.getElementById('generate-vpn-events').innerHTML=tempvalue;}
			}

	function GenerateVPNConfig(){
		var XHR = new XHRConnection();
		XHR.appendData('ComputerOS',document.getElementById('ComputerOS').value);		
		AnimateDiv('generate-vpn-events');
		XHR.sendAndLoad('$page', 'POST',x_GenerateVPNConfig);				
	}
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function config_popup_save(){
	$vpn=new openvpn();
	$vpn->ComputerOS=$_POST["ComputerOS"];
	$config=$vpn->BuildClientconf($_SESSION["uid"]);
	$tbconfig=explode("\n",$config);
	$html_logs[]=htmlentities("VPN config -> ". strlen($config)." bytes length (".count($tbconfig)." lines)");
	$uid=$_SESSION["uid"];
	writelogs("VPN config -> ". strlen($config)." bytes length (".count($tbconfig)." lines)",__FUNCTION__,__FILE__,__LINE__);
	$sock=new sockets();
	if(!$sock->SaveConfigFile($config,"$uid.ovpn")){
		$html_logs[]=htmlentities("Framework error while saving  -> $uid.ovpn;". strlen($config)." bytes length (".count($tbconfig)." lines)");
	}
	
	writelogs("sockets() OK",__FUNCTION__,__FILE__,__LINE__);
	
	//$datas=$sock->getfile('OpenVPNGenerate:'.$uid);	
	$datas=$sock->getFrameWork("openvpn.php?build-vpn-user={$_SESSION["uid"]}&basepath=".dirname(__FILE__));
	$tbl=explode("\n",$datas);
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
		$html_logs[]="<div><code style='font-size:10px;color:black;'>" . htmlentities($line)."</code></div>";
		
	}
	
	if(is_file('ressources/logs/'.$uid.'.zip')){
		$download="
		<hr>
		<p>&nbsp;</p>
		<center>
			<div style='font-size:14px'>{DOWNLOAD_CONFIG_FILES}</div>
			<div style='font-size:12px'>{click_here}</div>
			<a href='ressources/logs/".$uid.".zip'><img src='img/zip-icon-64.png' title=\"{DOWNLOAD_CONFIG_FILES}\" style='padding:8Px;border:1px solid #055447;margin:3px'></a>
		</center>
		<p>&nbsp;</p>
		<hr>
		";
		
	}
	
	$html="
	
	$download
	<H3>{events}</H3>
	". ParagrapheTXT("<div style='width:100%;height:200px;overflow:auto'>". implode("\n",$html_logs)."</div>");
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function ParagrapheTXT($text=null){
	
	$html="<div class='p_head'></div>
	<div class=t_head>
	$text</div>	
	";
	return $html;
}
