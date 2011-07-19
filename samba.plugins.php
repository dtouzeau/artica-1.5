<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');


$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["enable_as_modules"])){save();exit;}


js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_SAMBA} {plugins}');
	$prefix='SambaPluginsIndex_';
	
	$html="
	
	
	function {$prefix}StartPage(){
		YahooWin5(650,'$page?popup=yes','$title');
		//setTimeout(\"{$idmd}WaitToStart();\",500);	
		
	}
	
var X_SaveSambaPlugins= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	{$prefix}StartPage();
	
	}	
	
	function SaveSambaPlugins(){
		var XHR = new XHRConnection();
	
		if(document.getElementById('EnableScannedOnly')){
			XHR.appendData('EnableScannedOnly',document.getElementById('EnableScannedOnly').value);
			document.getElementById('img_EnableScannedOnly').src='img/wait_verybig.gif';
			
		}
		
		if(document.getElementById('EnableKav4Samba')){
			XHR.appendData('EnableKav4Samba',document.getElementById('EnableKav4Samba').value);
			document.getElementById('img_EnableKav4Samba').src='img/wait_verybig.gif';
			
		}		
		
		if(document.getElementById('EnableSambaXapian')){
			XHR.appendData('EnableSambaXapian',document.getElementById('EnableSambaXapian').value);
			document.getElementById('img_EnableSambaXapian').src='img/wait_verybig.gif';
			
		}			

		if(document.getElementById('EnableGreyhole')){
			XHR.appendData('EnableGreyhole',document.getElementById('EnableGreyhole').value);
			document.getElementById('img_EnableGreyhole').src='img/wait_verybig.gif';
			
		}			
		
		
		
		XHR.appendData('enable_as_modules','yes');	
		XHR.sendAndLoad('$page', 'GET',X_SaveSambaPlugins);			
		
	}
	
	
	{$prefix}StartPage()";
	
	echo $html;
}


function popup(){
	
	$sock=new sockets();
	$EnableKav4Samba=$sock->GET_INFO('EnableKav4Samba');
	$EnableScannedOnly=$sock->GET_INFO('EnableScannedOnly');
	$EnableSambaXapian=$sock->GET_INFO('EnableSambaXapian');
	if($EnableKav4Samba==null){$EnableKav4Samba=1;}
	if($EnableScannedOnly==null){$EnableScannedOnly=1;}
	if($EnableSambaXapian==null){$EnableSambaXapian=1;}
	
	$EnableGreyhole=$sock->GET_INFO('EnableGreyhole');
	if(!is_numeric($EnableGreyhole)){$EnableGreyhole=1;}
	
	$users=new usersMenus();
	if($users->KAV4SAMBA_INSTALLED){
		$kav=Paragraphe_switch_img('{enable_kaspersky_samba}','{enable_kaspersky_samba_text}','EnableKav4Samba',$EnableKav4Samba,'{enable_disable}',290);
	}else{
		$kav=Paragraphe_switch_disable('{enable_kaspersky_samba}','{feature_not_installed}','{feature_not_installed}',290);
	}
	
	if($users->SCANNED_ONLY_INSTALLED){
		$SCANNED_ONLY_INSTALLED=Paragraphe_switch_img('{enable_scanned_only}','{enable_scanned_only_text}','EnableScannedOnly',$EnableScannedOnly,'{enable_disable}',290);
	}else{
		$SCANNED_ONLY_INSTALLED=Paragraphe_switch_disable('{enable_scanned_only}','{feature_not_installed}','{feature_not_installed}',290);
	}

	if($users->XAPIAN_PHP_INSTALLED){
		$XAPIAN_INSTALLED=Paragraphe_switch_img('{enable_xapian_indexing}','{enable_xapian_indexing_text}','EnableSambaXapian',$EnableSambaXapian,'{enable_disable}',290);
	}else{
		$XAPIAN_INSTALLED=Paragraphe_switch_disable('{enable_xapian_indexing}','{feature_not_installed}','{feature_not_installed}',290);
		
	}
	
	if($users->GREYHOLE_INSTALLED){
		$GREYHOLE=Paragraphe_switch_img('{enable_grehole}','{enable_grehole_text}','EnableGreyhole',$EnableGreyhole,'{enable_disable}',290);
	}else{
		$GREYHOLE=Paragraphe_switch_disable('{enable_grehole}','{feature_not_installed}','{feature_not_installed}',290);
		
	}
	
	
	
	
	$html="
	<div class=explain>{vfs_modules_disabled_text}</div>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$kav</TD>
		<td valign='top'>$SCANNED_ONLY_INSTALLED</TD>
	</TR>
	<tr>
		<td valign='top'>$GREYHOLE</TD>
		<td valign='top'>$XAPIAN_INSTALLED</TD>
		
	</TR>	
	<tr>
		<td colspan=2 align='right'><hr>
		". button("{edit}","SaveSambaPlugins()")."
		
	</tr>
	</TABLE>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	
	$sock=new sockets();
	if(isset($_GET["EnableScannedOnly"])){$sock->SET_INFO("EnableScannedOnly",$_GET["EnableScannedOnly"]);}
	if(isset($_GET["EnableKav4Samba"])){$sock->SET_INFO("EnableKav4Samba",$_GET["EnableKav4Samba"]);}
	if(isset($_GET["EnableSambaXapian"])){$sock->SET_INFO("EnableSambaXapian",$_GET["EnableSambaXapian"]);}
	if(isset($_GET["EnableGreyhole"])){$sock->SET_INFO("EnableGreyhole",$_GET["EnableGreyhole"]);}
	
	
	$samab=new samba();
	$samab->SaveToLdap();
	
}


?>