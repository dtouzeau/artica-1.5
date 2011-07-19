<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.postfix-multi.inc');
	include_once('ressources/class.assp-multi.inc');
	
	
	
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableASSP"])){EnableASSP();exit;}
	if(isset($_GET["RBL_ADD"])){add();exit;}
	if(isset($_GET["OURBLDEL"])){delete();exit;}
	
js();



function js(){
		
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_ASSP}',"postfix.index.php");
	
	
	$html="
		function OU_ASSP(){
			YahooWin4('600','$page?popup=yes&ou=$ou','$title');
		
		}
		
var x_AssPEnable= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	OU_ASSP();
}
	
	function AssPEnable(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableASSP',document.getElementById('EnableASSP').value);
			XHR.appendData('ou','$ou');
			document.getElementById('img_EnableASSP').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_AssPEnable);
		
	}
	
	function OURBLDEL(ID){
		var XHR = new XHRConnection();
		XHR.appendData('OURBLDEL',ID);
		XHR.appendData('ou','$ou');
		XHR.sendAndLoad('$page', 'GET',x_OURBLADD);
	
	}		
	
	OU_ASSP();";
	
	echo $html;
	
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	
	$assp=new assp_multi($ou);
	
	$assp=Paragraphe_switch_img('{enable_assp}','{enable_assp_text}','EnableASSP',$assp->AsspEnabled,'{enable_disable}',450);
	
	
	
	$html="
	<div style='text-align:right;float:right'>". button("{edit}","AssPEnable()")."</div>
	$assp
	<hr>
	
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	
	</td>
	<td valing='top'>". button("{add}","OURBLADD()")."</td>
	</tR>
	</table>
	<p>&nbsp;</p>
	<div style='width:100%;height:300px;overflow:auto' id='OURBLLIST'></div>
		
	
	<script>
		
	</script>
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");	
	
}

function EnableASSP(){
	$assp=new assp_multi($_GET["ou"]);
	$assp->SET_VALUE("ASSPEnabled",$_GET["EnableASSP"]);
	
}


function popup_disabled(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	
		
}
