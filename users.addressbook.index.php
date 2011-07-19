<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/class.user.inc');


if(isset($_GET["address-book-front"])){addressbook_front();exit;}


addressbook_js();



function addressbook_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{your_address_book}');
	
	$html="
	
	function Loadaddressbook(){
		YahooWin3('700','$page?address-book-front=yes','$title');
	
	}
	Loadaddressbook();
	";
	echo $html;
	
}


function addressbook_front(){
	$server=$_SERVER['SERVER_NAME'];
	$ldap=new clladp();
	if(preg_match('#^(.+?):#',$server,$re)){
		$server=$re[1];
	}
	
	$usr=new usersMenus();
	$usr->LoadModulesEnabled();
	
	if($usr->EnableNonEncryptedLdapSession==1){
		$portadd=",389";
	}
	
	$users=new user($_SESSION["uid"]);
	
	$settings="
		<table style='width:100%'>
		<tr>
			<td colspan=2><H3>{your_parameters}</h3><p class=caption>{your_parameters_text}</p></td>
		</tr>
		<tr>
			<td class=legend>{hostname}:</td>
			<td style='font-size:13px'><strong><code>$server</code></strong></td>
		</tr>
		<tr>
			<td class=legend>{listen_port}:</td>
			<td style='font-size:13px'><strong><code>636$portadd</code></strong></td>
		</tr>	
		<tr>
			<td class=legend valign='top'>{base_dn}:</td>
			<td style='font-size:13px'><strong><code>ou=$users->cn,ou=People,dc=$users->ou,dc=NAB,$ldap->suffix</code></strong></td>
		</tr>
		<tr>
			<td class=legend nowrap valign='top'>{bind_dn}:</td>
			<td style='font-size:13px'><strong><code>$users->dn</code></strong></td>
		</tr>						
		</table>
	
	
	
	";
		$settings=RoundedLightWhite($settings);
	
	
	$html="<H1>{your_address_book}</H1>
	<p class=caption>{your_address_book_text}</p>
	$settings
	
	
	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}



?>