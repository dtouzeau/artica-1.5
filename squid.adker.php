<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["settings"])){settings();exit;}
	if(isset($_POST["EnableKerbAuth"])){settingsSave();exit;}
	if(isset($_GET["kerbchkconf"])){kerbchkconf();exit;}
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_SQUIDKERAUTH}");
	$html="YahooWin4(550,'$page?popup=yes','$title');";
	echo $html;
	}
	
function popup(){
$page=CurrentPageName();
	$html="<div id='serverkerb-popup'></div>
	
	<script>
	function RefreshServerKerb(){
		LoadAjax('serverkerb-popup','$page?settings=yes');
		}
	
		RefreshServerKerb();
	</script>
	";
		
echo $html;		
}	
	
function settings(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$severtype["WIN_2003"]="Windows 2003";
	$severtype["WIN_2008AES"]="Windows 2008 with AES";
	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));
	$EnableKerbAuth=$sock->GET_INFO("EnableKerbAuth");
	if(!is_numeric("$EnableKerbAuth")){$EnableKerbAuth=0;}
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><span id='kerbchkconf'></span>
		<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","RefreshAll()")."</div></td>
	<td valign='top' width=99%'>
		<div class=explain>{APP_SQUIDKERAUTH_TEXT}<br>{APP_SQUIDKERAUTH_TEXT_REF}</div>
	</td>
	</table>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{EnableWindowsAuthentication}:</td>
		<td>". Field_checkbox("EnableKerbAuth",1,"$EnableKerbAuth","EnableKerbAuthCheck()")."</td>
	</tr>	
	<tr>
		<td class=legend>{WINDOWS_DNS_SUFFIX}:</td>
		<td>". Field_text("WINDOWS_DNS_SUFFIX",$array["WINDOWS_DNS_SUFFIX"],"font-size:14px;padding:3px;width:190px")."</td>
	</tr>
	<tr>
		<td class=legend>{WINDOWS_SERVER_NETBIOSNAME}:</td>
		<td>". Field_text("WINDOWS_SERVER_NETBIOSNAME",$array["WINDOWS_SERVER_NETBIOSNAME"],"font-size:14px;padding:3px;width:190px")."</td>
	</tr>	
	<tr>
		<td class=legend>{WINDOWS_SERVER_TYPE}:</td>
		<td>". Field_array_Hash($severtype,"WINDOWS_SERVER_TYPE",$array["WINDOWS_SERVER_TYPE"],"style:font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend>{administrator}:</td>
		<td>". Field_text("WINDOWS_SERVER_ADMIN",$array["WINDOWS_SERVER_ADMIN"],"font-size:14px;padding:3px;width:190px")."</td>
	</tr>		
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("WINDOWS_SERVER_PASS",$array["WINDOWS_SERVER_PASS"],"font-size:14px;padding:3px;width:190px")."</td>
	</tr>	
	
	<tr>
	<td colspan=2 align='right'><hr>". button("{apply}","SaveKERBProxy()")."</td>
	</tr>
	</table>
	
	<script>
		function EnableKerbAuthCheck(){
			document.getElementById('WINDOWS_DNS_SUFFIX').disabled=true;
			document.getElementById('WINDOWS_SERVER_NETBIOSNAME').disabled=true;
			document.getElementById('WINDOWS_SERVER_TYPE').disabled=true;
			document.getElementById('WINDOWS_SERVER_ADMIN').disabled=true;
			document.getElementById('WINDOWS_SERVER_PASS').disabled=true;
			
			if(document.getElementById('EnableKerbAuth').checked){
				document.getElementById('WINDOWS_DNS_SUFFIX').disabled=false;
				document.getElementById('WINDOWS_SERVER_NETBIOSNAME').disabled=false;
				document.getElementById('WINDOWS_SERVER_TYPE').disabled=false;
				document.getElementById('WINDOWS_SERVER_ADMIN').disabled=false;
				document.getElementById('WINDOWS_SERVER_PASS').disabled=false;							
			
			}
		
		}
		
		function RefreshAll(){
			RefreshServerKerb();
		}
		
	var x_SaveKERBProxy= function (obj) {
		RefreshServerKerb();
	}		
	
		function SaveKERBProxy(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableKerbAuth').checked){XHR.appendData('EnableKerbAuth',1);}else{XHR.appendData('EnableKerbAuth',0);}
			XHR.appendData('WINDOWS_DNS_SUFFIX',document.getElementById('WINDOWS_DNS_SUFFIX').value);
			XHR.appendData('WINDOWS_SERVER_NETBIOSNAME',document.getElementById('WINDOWS_SERVER_NETBIOSNAME').value);
			XHR.appendData('WINDOWS_SERVER_TYPE',document.getElementById('WINDOWS_SERVER_TYPE').value);
			XHR.appendData('WINDOWS_SERVER_ADMIN',document.getElementById('WINDOWS_SERVER_ADMIN').value);
			XHR.appendData('WINDOWS_SERVER_PASS',document.getElementById('WINDOWS_SERVER_PASS').value);
			AnimateDiv('serverkerb-popup');
			XHR.sendAndLoad('$page', 'POST',x_SaveKERBProxy);
		
		}
		
		
	EnableKerbAuthCheck();
	LoadAjax('kerbchkconf','$page?kerbchkconf=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}	

function settingsSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableKerbAuth", $_POST["EnableKerbAuth"]);
	$sock->SaveConfigFile(base64_encode(serialize($_POST)), "KerbAuthInfos");
	$sock->getFrameWork("services.php?kerbauth=yes");
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
}

function kerbchkconf(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("KerbAuthInfos")));
	if($array["WINDOWS_DNS_SUFFIX"]==null){echo $tpl->_ENGINE_parse_body(Paragraphe32("MISSING_PARAMETER", "WINDOWS_DNS_SUFFIX", null, "error-24.png"));return;}
	if($array["WINDOWS_SERVER_NETBIOSNAME"]==null){echo $tpl->_ENGINE_parse_body(Paragraphe32("MISSING_PARAMETER", "WINDOWS_SERVER_NETBIOSNAME", null, "error-24.png"));return;}
	if($array["WINDOWS_SERVER_TYPE"]==null){echo $tpl->_ENGINE_parse_body(Paragraphe32("MISSING_PARAMETER", "WINDOWS_SERVER_TYPE", null, "error-24.png"));return;}
	if($array["WINDOWS_SERVER_ADMIN"]==null){echo $tpl->_ENGINE_parse_body(Paragraphe32("MISSING_PARAMETER", "administrator", null, "error-24.png"));return;}
	if($array["WINDOWS_SERVER_PASS"]==null){echo $tpl->_ENGINE_parse_body(Paragraphe32("MISSING_PARAMETER", "password", null, "error-24.png"));return;}
	
	$hostname=strtolower(trim($array["WINDOWS_SERVER_NETBIOSNAME"])).".".strtolower(trim($array["WINDOWS_DNS_SUFFIX"]));
	$ip=gethostbyname($hostname);
	if($ip==$hostname){echo $tpl->_ENGINE_parse_body(Paragraphe32("WINDOWS_NAME_SERVICE_NOT_KNOWN", "noacco:<strong style='font-size:12px'>$hostname</strong>", null, "error-24.png"));return;}
	
	
	
	
}