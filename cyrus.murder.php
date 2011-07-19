<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["wizard"])){wizard();exit;}
	if(isset($_GET["test_connection"])){test_connection();exit;}
	if(isset($_GET["murder-link"])){murder_link();exit;}
	if(isset($_GET["EnableImapMurderedFrontEnd"])){EnableImapMurderedFrontEnd();exit;}
	
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_CYRUS_MURDER}');
$CYRUS_MURDER_CONNECT_BACKEND=$tpl->_ENGINE_parse_body('{CYRUS_MURDER_CONNECT_BACKEND}');
	
	$users=new usersMenus();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	
	
$html="

	function CyrusMurderLoadPage(){
		YahooWin(550,'$page?popup=yes','$title');
	
	}
	
	function StartMurderWizard(){
		YahooWin2(650,'$page?wizard=yes','$CYRUS_MURDER_CONNECT_BACKEND');
	}
	
var X_MurderVerifyOne= function (obj) {
	var results=obj.responseText;
	document.getElementById('form_murder_div').innerHTML=results;
	}
	
var X_MurderLink= function (obj) {
	var results=obj.responseText;
	document.getElementById('murderlink').innerHTML=results;
	}	
		
	function MurderVerifyOne(){
		var XHR = new XHRConnection();
		XHR.appendData('test_connection','yes');
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('artica_port',document.getElementById('artica_port').value);
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',document.getElementById('password').value);
		document.getElementById('form_murder_div').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_MurderVerifyOne);				
	}
	
	function MurderLink(){
		var XHR = new XHRConnection();
		XHR.appendData('murder-link','yes');
		document.getElementById('murderlink').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_MurderLink);		
	
	}
	
var X_EnableImapMurderedFrontEnd= function (obj) {
	var results=obj.responseText;
	CyrusMurderLoadPage();
	}		
	
	function EnableImapMurderedFrontEnd(){
		var XHR = new XHRConnection();
		var CyrusEnableImapMurderedFrontEnd=document.getElementById('CyrusEnableImapMurderedFrontEnd').value;
		XHR.appendData('EnableImapMurderedFrontEnd',CyrusEnableImapMurderedFrontEnd);
		document.getElementById('wizardone').innerHTML='<img src=img/wait_verybig.gif>';
		XHR.sendAndLoad('$page', 'GET',X_EnableImapMurderedFrontEnd);	
	
	
	}
	
	
	CyrusMurderLoadPage();

";
	
echo $html;	
	
}

function popup(){
	
	$tpl=new templates();
	
	$wizard=Paragraphe('64-wizard.png','{CYRUS_MURDER_CONNECT_BACKEND}','{CYRUS_MURDER_CONNECT_BACKEND_TEXT}',"javascript:StartMurderWizard()");
	$sock=new sockets();
	$CyrusEnableImapMurderedFrontEnd=$sock->GET_INFO("CyrusEnableImapMurderedFrontEnd");
	if($CyrusEnableImapMurderedFrontEnd==1){
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_INFO("CyrusMurderBackendServer"));
		
		$wizard=Paragraphe_switch_img("{MURDER_HAS_FRONTEND}","{MURDER_HAS_FRONTEND_TEXT}","CyrusEnableImapMurderedFrontEnd",$CyrusEnableImapMurderedFrontEnd);
		$wizard="
		<table style='width:100%'>
		<tr>
		<td class=legend>{MURDER_BACKEND_SERVER}:</td>
		<td><strong>{$ini->_params["MURDER_BACKEND"]["servername"]}</td>
		</tr>
		<tr>
			<td colspan=2>&nbsp;</td>
		</tr>		
		<tr>
			<td colspan=2>$wizard</td>
		</tr>
		<tr><td colspan=2 align='right'><hr><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EnableImapMurderedFrontEnd();\"></td></tr>
		</table>
		";
	}
	
	
	$html="<H1>{APP_CYRUS_MURDER}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>" . RoundedLightWhite("<img src='img/murder-256.png'>")."</td>
		<td valign='top'>
			<p class=caption>{APP_CYRUS_MURDER_TEXT}</p>
			<div id='wizardone'>
			$wizard
			</div>
		</td>
	</tr>
	</table>
	
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function EnableImapMurderedFrontEnd(){
	$sock=new sockets();
	$sock->SET_INFO('CyrusEnableImapMurderedFrontEnd',$_GET["EnableImapMurderedFrontEnd"]);
	$sock->getFrameWork('cmd.php?cyrus-reconfigure=yes');
	
}

function wizard(){
	$tpl=new templates();
	
	$form=form1($error);
	
$html="<H1>{APP_CYRUS_MURDER}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>" . RoundedLightWhite("<img src='img/murder-1-128.png'>")."</td>
		<td valign='top'>
			<p class=caption>{CYRUS_MURDER_CONNECT_BACKEND_TEXT}</p>
			" . RoundedLightWhite("<div id='form_murder_div'>$form</div>")."
		</td>
	</tr>
	</table>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}

function form1($error=null){
	$tpl=new templates();
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO('CyrusMurderBackendServer'));
	if($ini->_params["MURDER_BACKEND"]["artica_port"]==null){$ini->_params["MURDER_BACKEND"]["artica_port"]=9000;}
	if($ini->_params["MURDER_BACKEND"]["username"]==null){$ini->_params["MURDER_BACKEND"]["username"]="admin";}
	
	if($error<>null){$error="<hr><code style='color:red'>$error</code>";}
	
	$TEXT="<p style='font-size:12px;font-weight:bold'>{CYRUS_MURDER_CONNECT_BACKEND_EXPLAIN}$error</p>
	
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend>{servername}:</td>
		<td>" . Field_text('servername',$ini->_params["MURDER_BACKEND"]["servername"],'width:250px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{artica_console_port}:</td>
		<td>" . Field_text('artica_port',$ini->_params["MURDER_BACKEND"]["artica_port"],'width:40px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{username}:</td>
		<td>" . Field_text('username',$ini->_params["MURDER_BACKEND"]["username"],'width:120px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td>" . Field_password('password',$ini->_params["MURDER_BACKEND"]["password"],'width:120px')."</td>
	</tr>			
	<tr>
	<td colspan=2 align='right'><input type='button' value='{connect_and_next}&nbsp;&raquo;' OnClick=\"javascript:MurderVerifyOne();\"></td>
	</tr>
	</table>
	";
	return $tpl->_ENGINE_parse_body($TEXT);	
	
}

function form2($error){
	
	$html="
	<div id='murderlink'>
	<div style='font-size:12px;font-weight:bold;border-bottom:1px solid #CCCCCC;color:red'>$error</div>
	<p style='font-size:12px'>{MURDER_VERIFY_DONE}</p>
	<p style='font-size:12px'>{MURDER_VERIFY_GO}</p>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:MurderLink();\" value='{murder_link_now}&nbsp;&raquo;'></div>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function test_connection(){
	$sock=new sockets();
	$users=new usersMenus();
	$ini=new Bs_IniHandler();
	while (list ($num, $ligne) = each ($_GET) ){
		$ini->_params["MURDER_BACKEND"][$num]=$ligne;
	}
	$ini->_params["MURDER_BACKEND"]["host"]=$users->hostname;
	$sock->SaveConfigFile($ini->toString(),"CyrusMurderBackendServer");
	$datas=$sock->getfile('MurderTestBackend');
	$datas=strip_tags($datas);
	
	if(preg_match('#SUCCESS#s',$datas)){
		echo form2(null);
		exit;
	}
	echo form1($datas);
	
	
}

function murder_link(){
	$sock=new sockets();
	
	$datas=$sock->getfile('MurderBeABackend');
	
	$ini=new Bs_IniHandler();
	$ini->loadString($datas);
	if($ini->_params["BACKEND"]["success"]<>1){
			$datas=strip_tags($datas);
			echo form2($datas);
			exit;
	}
	

	
	
	$ini2=new Bs_IniHandler();
	$ini2->loadString($sock->GET_INFO("CyrusMurderBackendServer"));
	$ini2->_params["MURDER_BACKEND"]["suffix"]=$ini->_params["BACKEND"]["suffix"];
	
	writelogs("Receive new server {$ini2->_params["MURDER_BACKEND"]["suffix"]} suffix..",__FUNCTION__,__FILE__);
	
	$sock->SaveConfigFile($ini2->toString(),"CyrusMurderBackendServer");
	$datas=$sock->getfile('CyrusMurderChangeLDAPConfig');
	writelogs("Receive $datas",__FUNCTION__,__FILE__);
	if(!preg_match("#SUCCESS#",$datas)){
		writelogs("failed",__FUNCTION__,__FILE__);
		echo form2($datas);
		exit;
	}
	writelogs("success -> CyrusEnableImapMurderedFrontEnd",__FUNCTION__,__FILE__);
	$sock->SET_INFO('CyrusEnableImapMurderedFrontEnd',1);
	
	echo form3($datas);
	
	
}

function form3($datas){
$html="
	<div id='murderlink'>
	<p style='font-size:12px'>{MURDER_VERIFY_GO_SUCCESS}</p>
	</div>
	<div style='width:100%;height:120px;overflow:auto'>$datas</div>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
	
	
	
	
?>