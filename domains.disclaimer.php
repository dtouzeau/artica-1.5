<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.domains.diclaimers.inc');
	$user=new usersMenus();
	$tpl=new templates();
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(isset($_POST["domain"])){$_GET["domain"]=$_POST["domain"];}
	
	if(!$user->AsOrgAdmin){	
		$ERROR_NO_PRIVS=$tpl->javascript_parse_text('{ERROR_NO_PRIVS}');
		echo "alert('$ERROR_NO_PRIVS');";
		die();
	}
	
	if(!$user->AsAnAdministratorGeneric){
			if($_SESSION["ou"]<>$_GET["ou"]){
				echo "alert('$ERROR_NO_PRIVS');";
				die();
			}
	}

	
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["DisclaimerOutbound"])){save();exit;}
	if(isset($_GET["disclaimer-editor"])){altermime_disclaimer();exit;}
	if(isset($_POST["DisclaimerContent"])){altermime_disclaimer();exit;}
	
js();


function js(){

$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();

$title=$tpl->_ENGINE_parse_body('{disclaimer} {parameters}');	
$ou=$_GET["ou"];
$domain=$_GET["domain"];



$html="
	function {$prefix}Load(){
			YahooWin3(650,'$page?popup=yes&ou=$ou&domain=$domain','$title');
		
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
			XHR.appendData('DisclaimerUserOverwrite',document.getElementById('DisclaimerUserOverwrite').value);
			XHR.appendData('DisclaimerActivate',document.getElementById('DisclaimerActivate').value);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			document.getElementById('disclaimerGlobalDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',X_DisclaimerGlobalConfSave);	
	
	}
	
	function OrgLoadDisclaimer(){
	 s_PopUp('$page?disclaimer-editor=yes&ou=$ou&domain=$domain',700,600);
	}	
	
	{$prefix}Load();";
	
echo $html;
}

function popup(){
	
	$ldap=new clladp();
	$domain=$_GET["domain"];
	$ou=$_GET["ou"];
	$dd=new domains_disclaimer($ou,$domain);
	
	//DisclaimerContent
	
	$edit_disclaimer=Paragraphe("64-templates.png","{edit_disclaimer}","{edit_disclaimer_text}","javascript:OrgLoadDisclaimer()");
	
	$form="<table style='width:100%'>
	<tr>
		<td class=legend>{enable_disclaimer}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerActivate",$dd->DisclaimerActivate,"{enable_disable}")."</td>
	</tr>	
	<tr>
		<td class=legend>{enable_outbound}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerOutbound",$dd->DisclaimerOutbound,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td class=legend>{enable_inbound}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerInbound",$dd->DisclaimerInbound,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td class=legend>{overwritten_by_user}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerUserOverwrite",$dd->DisclaimerUserOverwrite,"{enable_disable}")."</td>
	</tr>		
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:DisclaimerGlobalConfSave();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>";
	
	$form=RoundedLightWhite($form);
	
	$html="<H1>{disclaimer} {parameters}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$edit_disclaimer</td>
		<td valign='top'>
			<div id='disclaimerGlobalDiv'>$form</div>
		</td>
	</tr>
	</table>
	
	
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"amavis.index.php");
}

function save(){
	$domain=$_GET["domain"];
	$ou=$_GET["ou"];
	$dd=new domains_disclaimer($ou,$domain);
	$dd->DisclaimerInbound=$_GET["DisclaimerInbound"];
	$dd->DisclaimerOutbound=$_GET["DisclaimerOutbound"];
	$dd->DisclaimerUserOverwrite=$_GET["DisclaimerUserOverwrite"];
	$dd->DisclaimerActivate=$_GET["DisclaimerActivate"];
	$dd->SaveDislaimerParameters();
	
	
	
}
function altermime_disclaimer(){
	$domain=$_GET["domain"];
	$ou=$_GET["ou"];
	$dd=new domains_disclaimer($ou,$domain);
	
	if(isset($_POST["DisclaimerContent"])){
		$dd->DisclaimerContent=$_POST["DisclaimerContent"];
		$dd->SaveDisclaimer();
	}
	
	
	
	$tpl=new templates();
	$tiny=TinyMce('DisclaimerContent',$dd->DisclaimerContent);
	$page=CurrentPageName();
	
	$html="
	<H1>{edit_disclaimer} $domain</H1>
	<p class=caption>{edit_disclaimer_text}</p>
	<form name='tinymcedisclaimer' method='post' action=\"$page\">
	<input type='hidden' name='ou' id='ou' value='$ou'>
	<input type='hidden' name='domain' id='domain' value='$domain'>
	$tiny
	
	</form>
	
	
	";
	$tpl=new template_users('{edit_disclaimer}',$html,0,1,1);
	echo $tpl->web_page;	
}


?>