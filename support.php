<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	
	if(isset($_GET["popup"])){popup();exit;}
	js();
function js(){
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{SUPPORT}');

$page=CurrentPageName();
$html="
	function SupportPage(){
		RTMMail(720,'$page?popup=yes','$title');
		}

	
	SupportPage();

";
	
echo $html;
	
	
}

function popup(){
	$users=new usersMenus();
	$img="artica4.png";
	
	$comu=RoundedLightWhite("
<table style='width:100%'>
				<tr>
				<td width=1% valign='top'><img src='img/chiffre2.png'></td>
				<td valign='top'>				
				<h2>{Community_support}</H2>
				<p class=caption>{Community_support_text}</p>
				<div style='font-size:13px;text-align:right'><a href='http://forum.artica.fr/' target=_new>http://forum.artica.fr/</a></div></td>
				</tr>
				</table>");
	
	if($users->KASPERSKY_SMTP_APPLIANCE){$img="artica4k.png";$comu=null;}
	
	
	
	$html="<h1>{SUPPORT} Artica v.".$users->ARTICA_VERSION."</H1>
	<table style='width:100%'>
		<tr>
			<td valign='top'>" . RoundedLightWhite("<img src='img/$img'>")."</td>
			<td valign='top'>
				".RoundedLightWhite("
				<table style='width:100%'>
				<tr>
				<td width=1% valign='top'><img src='img/chiffre1.png'></td>
				<td valign='top'>
				<h2>{PROFESSIONAL_SUPPORT}</H2>
				<p class=caption>{ARTICA_TECHNO}</p>
				<div style='font-size:13px;text-align:right'><a href='http://www.artica-technology.com/' target=_new>http://www.artica-technology.com</a></div>
				</td>
				</tr>
				</table>")."
				<br>
				$comu
			</td>
		</tr>
	</table>
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'support.php');
}

?>