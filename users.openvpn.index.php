<?php

session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.user.inc');
include_once('ressources/class.openvpn.inc');
$users=new usersMenus();
if(!$users->AllowOpenVPN){die("alert('Not allowed!')");}

if(isset($_GET["index"])){index();exit;}
if(isset($_GET["generate-files"])){generatefiles();exit;}

js();



function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_OPENVPN}");
	$page=CurrentPageName();
	$html="
	
		function LoadOpenUvpn(){
			YahooWin2('750','$page?index=yes','$title');
		
		}
		
		LoadOpenUvpn();
	";
		
		echo $html;
	
}


function index(){
	
	$users=new user($_SESSION["uid"]);
	$name=$users->DisplayName;
	$page=CurrentPageName();
	//openvpn-2.0.9-gui-1.0.3-install.exe
	$downloadvinvin=Paragraphe('64-vinvin.png','{DOWNLOAD_OPENVPN_CLIENT}','{DOWNLOAD_OPENVPN_CLIENT_TEXT}',
	'http://www.artica.fr/download/openvpn-2.0.9-gui-1.0.3-install.exe','{DOWNLOAD_OPENVPN_CLIENT}',null,210,null,false,true);

	$build=Paragraphe('64-openvpn-ext.png','{DOWNLOAD_CONFIG_FILES}','{DOWNLOAD_CONFIG_FILES_TEXT}',
	"javascript:YahooWin3(500,'$page?generate-files=yes','{DOWNLOAD_CONFIG_FILES}')",'{DOWNLOAD_CONFIG_FILES}',null,210,null,false,false);
	
	
	
	$html="<H1>$name: {WELCOME_USER_OPENVPN}</H1>
	<table style='width:100%'>
		<tr>
			<td valign='top'>
				$downloadvinvin
			</td>
			<td valign='top'>
				$build
			</td>
		</tr>
	</table>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function generatefiles(){
	$vpn=new openvpn();
	$config=$vpn->BuildClientconf($_SESSION["uid"]);
	$uid=$_SESSION["uid"];
	$sock=new sockets();
	$sock->SaveConfigFile($config,"$uid.ovpn");
	$datas=$sock->getFrameWork("openvpn.php?build-vpn-user={$_SESSION["uid"]}&basepath=".dirname(__FILE__));
	$tbl=explode("\n",$datas);
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line)==null){continue;}
		$html=$html . "<div><code style='font-size:11px'>" . htmlentities($line)."</code></div>";
		
	}
	
	if(is_file('ressources/logs/'.$uid.'.zip')){
		$download="
		<center>
			<a href='ressources/logs/".$uid.".zip'><img src='img/disk-save-64.png'></a>
		</center>
		";
		
	}
	
	$html="
	<H1>{DOWNLOAD_CONFIG_FILES}</H1>
	$download
	" . RoundedLightWhite("
	<div style='width:100%;height:200px;overflow:auto'>
	
	$html</div>")."
	
	
	";
	
		$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


?>