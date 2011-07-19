<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if(!$user->AsPostfixAdministrator){
		$tpl=new templates();
		$ERROR_NO_PRIVS=$tpl->javascript_parse_text('{ERROR_NO_PRIVS}');
		echo "alert('$ERROR_NO_PRIVS');";
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["DisclaimerOutbound"])){save();exit;}
js();


function js(){

$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{parameters}');	




$html="
	function {$prefix}Load(){
			YahooWin3(650,'$page?popup=yes','$title');
		
		}
		
var X_DisclaimerGlobalConfSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
		{$prefix}Load();
	}			
	
	function DisclaimerGlobalConfSave(){
			var XHR = new XHRConnection();
			XHR.appendData('DisclaimerOutbound',document.getElementById('DisclaimerOutbound').value);
			XHR.appendData('DisclaimerInbound',document.getElementById('DisclaimerInbound').value);
			XHR.appendData('DisclaimerOrgOverwrite',document.getElementById('DisclaimerOrgOverwrite').value);
			document.getElementById('disclaimerGlobalDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',X_DisclaimerGlobalConfSave);	
	
	}
	
	{$prefix}Load();";
	
echo $html;
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("DisclaimerOutbound",$_GET["DisclaimerOutbound"]);
	$sock->SET_INFO("DisclaimerInbound",$_GET["DisclaimerInbound"]);
	$sock->SET_INFO("DisclaimerOrgOverwrite",$_GET["DisclaimerOrgOverwrite"]);
	$sock->getFrameWork("cmd.php?artica-filter-reload=yes");
}


function popup(){
	$sock=new sockets();
	$DisclaimerOutbound=$sock->GET_INFO("DisclaimerOutbound");
	$DisclaimerInbound=$sock->GET_INFO("DisclaimerInbound");
	$DisclaimerOrgOverwrite=$sock->GET_INFO("DisclaimerOrgOverwrite");
	if($DisclaimerOrgOverwrite==null){$DisclaimerOrgOverwrite=0;}
	if($DisclaimerOutbound==null){$DisclaimerOutbound=1;}
	if($DisclaimerInbound==null){$DisclaimerInbound=0;}
	
	
	$form="<table style='width:100%'>
	<tr>
		<td class=legend>{enable_outbound}:</td>
		<td>".Field_numeric_checkbox_img("DisclaimerOutbound",$DisclaimerOutbound,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td class=legend>{enable_inbound}:</td>
		<td>".Field_numeric_checkbox_img("DisclaimerInbound",$DisclaimerInbound,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td class=legend>{overwritten_by_org}:</td>
		<td>".Field_numeric_checkbox_img("DisclaimerOrgOverwrite",$DisclaimerOrgOverwrite,"{enable_disable}")."</td>
	</tr>		
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:DisclaimerGlobalConfSave();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>";
	
	$form=RoundedLightWhite($form);
	
	$html="<H1>{disclaimer} {parameters}</H1>
	<div id='disclaimerGlobalDiv'>$form</div>
	
	
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function altermime_disclaimer(){
	
	
	$sock=new sockets();
	if(isset($_POST["AlterMimeHTMLDisclaimer"])){
		writelogs("Saving disclaimer size=". strlen($_POST["AlterMimeHTMLDisclaimer"]),__FUNCTION__,__FILE__);
		$sock->SaveConfigFile($_POST["AlterMimeHTMLDisclaimer"],"AlterMimeHTMLDisclaimer");
		$AlterMimeHTMLDisclaimer=$_POST["AlterMimeHTMLDisclaimer"];
	}else{
		$AlterMimeHTMLDisclaimer=$sock->GET_INFO("AlterMimeHTMLDisclaimer");
	}
	

		
	
	if($AlterMimeHTMLDisclaimer==null){
		$AlterMimeHTMLDisclaimer=$DisclaimerExample;
		$sock->SaveConfigFile($DisclaimerExample,"AlterMimeHTMLDisclaimer");
	}
	
	$tpl=new templates();
	$tiny=TinyMce('AlterMimeHTMLDisclaimer',$AlterMimeHTMLDisclaimer);
	$page=CurrentPageName();
	
	$html="
	<H1>{edit_disclaimer}</H1>
	<p class=caption>{edit_disclaimer_text}</p>
	<form name='tinymcedisclaimer' method='post' action=\"$page\">
	$tiny
	</form>
	
	
	";
	$tpl=new template_users('{edit_disclaimer}',$html,0,1,1);
echo $tpl->web_page;	
}

?>