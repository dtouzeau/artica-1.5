<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");

$user=new usersMenus();
if($user->AsPostfixAdministrator==false){die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_POST["OpenEMMServerURL"])){SaveMasterConf();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	echo "AnimateDiv('BodyContent');
	$('#BodyContent').load('$page?popup=yes');";
	
}


function popup() {
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$OpenEMMServerURL=$sock->GET_INFO("OpenEMMServerURL");
	if($OpenEMMServerURL==null){$OpenEMMServerURL="http://{$_SERVER["SERVER_NAME"]}:8080";}
	$OpenEMMMailErrorRecipient=$sock->GET_INFO("OpenEMMMailErrorRecipient");
	if($OpenEMMMailErrorRecipient==null){$OpenEMMMailErrorRecipient="openemm@localhost";}
	
	$OpenEMMUserAgent=$sock->GET_INFO("OpenEMMUserAgent");
	if($OpenEMMUserAgent==null){$OpenEMMUserAgent="OpenEMM V2011";}
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><div id='openemm-status'></div></td>
		<td valign='top' width=100%><div class=explain>{OPENEMM_ABOUT}</div></td>
	</tr>
	</table>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{server_url}:</td>
		<td>". Field_text("OpenEMMServerURL",$OpenEMMServerURL,"font-size:14px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend>SMTP&nbsp;{useragent}:</td>
		<td>". Field_text("OpenEMMUserAgent",$OpenEMMUserAgent,"font-size:14px;padding:3px;width:180px")."</td>
	</tr>	
	<tr>
		<td class=legend>{mailerror_recipient}:</td>
		<td>". Field_text("OpenEMMMailErrorRecipient",$OpenEMMMailErrorRecipient,"font-size:14px;padding:3px;width:180px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveOpenEMMConfig()")."</td>
	</tR>
	
	
	</table>
	
	<script>
	var x_SaveOpenEMMConfig= function (obj) {
		var results=obj.responseText;
		$('#BodyContent').load('$page?popup=yes'); 
		}
		
	function SaveOpenEMMConfig(){
		var XHR = new XHRConnection();
		XHR.appendData('OpenEMMServerURL',document.getElementById('OpenEMMServerURL').value);
		XHR.appendData('OpenEMMUserAgent',document.getElementById('OpenEMMUserAgent').value);
		XHR.appendData('OpenEMMMailErrorRecipient',document.getElementById('OpenEMMMailErrorRecipient').value);
		AnimateDiv('BodyContent');
		XHR.sendAndLoad('$page', 'POST',x_SaveOpenEMMConfig);				
	}	
	
	function RefreshStatus(){
		LoadAjax('openemm-status','$page?status=yes');
	
	}
	RefreshStatus();
	
	</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function status(){
	
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshStatus()")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$datas=$sock->getFrameWork("services.php?openemm-status=yes");
	writelogs(strlen($datas)." bytes for openemm status",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	$ini->loadString(base64_decode($datas));
	$status=DAEMON_STATUS_ROUND("APP_OPENEMM",$ini,null,0).$refresh;
	echo $tpl->_ENGINE_parse_body($status);		
	
}

function SaveMasterConf(){
	$sock=new sockets();
	$sock->SET_INFO("OpenEMMServerURL", $_POST["OpenEMMServerURL"]);
	$sock->SET_INFO("OpenEMMMailErrorRecipient", $_POST["OpenEMMMailErrorRecipient"]);
	$sock->SET_INFO("OpenEMMUserAgent", $_POST["OpenEMMUserAgent"]);
	$sock->getFrameWork("services.php?restart-openemm=yes");
}


