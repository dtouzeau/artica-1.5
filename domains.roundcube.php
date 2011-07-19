<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.roundcube.inc');
	
	$access=true;
	$users=new usersMenus();
	if(!isset($_GET["ou"])){$access=false;}
	if(!$users->AllowEditOuSecurity){$access=false;}
	if($_SESSION["uid"]<>-100){if($_SESSION["ou"]<>$_GET["ou"]){$access=false;}}
	
	if(!$access){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
		die();
		}
		
	if(isset($_GET["roundcube-index"])){roundcube_index();exit;}
	if(isset($_GET["roundcubeservername"])){roudncube_save();exit;}
	js();

	
	
	
function js(){
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_ROUNDCUBE}');
$page=CurrentPageName();	
$html="



function RoundCubeOrgStartPage(){
	YahooWinS(550,'$page?roundcube-index=yes&ou={$_GET["ou"]}','$title');
	}
	
var x_SaveRoundcubeForm= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	RoundCubeOrgStartPage();
	}

function sugarInstall(){
	var XHR = new XHRConnection();
	document.getElementById('logojoom').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'
	XHR.appendData('sugar-install','{$_GET["ou"]}');
	XHR.appendData('ou','{$_GET["ou"]}');	
	XHR.sendAndLoad('$page', 'GET',x_sugarInstall);
}

function SaveRoundcubeForm(){
	var XHR = new XHRConnection();
	XHR.appendData('roundcubeservername',document.getElementById('roundcubeservername').value);
	XHR.appendData('ou','{$_GET["ou"]}');	
	document.getElementById('roundcubeform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveRoundcubeForm);

}


RoundCubeOrgStartPage();";
	
	echo $html;
	
}

function roundcube_index(){
	
	$sock=new sockets();
	
	$roundcube=new roundcube();
	$users=new usersMenus();
	$sock=new sockets();
	$version=$users->roundcube_version;
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$p=roundcube_form1($_GET["ou"]);
	
	if($roundcube->roundcubeWebsites["{$_GET["ou"]}"]["servername"]<>null){
		$www="<a href='#' 
		OnClick=\"javascript:s_PopUpFull('http://{$roundcube->roundcubeWebsites["{$_GET["ou"]}"]["servername"]}:$ApacheGroupWarePort',800,600)\"
		style='font-size:12px;font-weight:bold'
		>
		{website}:http://{$roundcube->roundcubeWebsites["{$_GET["ou"]}"]["servername"]}:$ApacheGroupWarePort</a>
		";
		
	}
	
	
	$html="
	<H1>{APP_ROUNDCUBE}</H1>
	<table style='width:100%'>
	<tr>
		<td>
			<div id='logojoom'><img src='img/roundcube3logo.png'></div>
		</td>
		<td width=99% valign='top'>
			<div style='font-size:18px;font-weight:bold;padding-left:5px;'>Roundcube v$version</div>$www
		</td>
	</tr>
	<tr>
		<td colspan=2><div id='roundcubeform' style='margin-top:5px;width:100%;height:300px;overflow:auto'>$p</div></td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


	

function roundcube_form1($ou){
	$ldap=new clladp();
	$roundcube=new roundcube();
	
	
	
	$html="<table style='width:100%'>
	<tr>
		<td class=legend>{roundcubeservername}:</td>
		<td>" . Field_text('roundcubeservername',$roundcube->roundcubeWebsites[$ou]["servername"],'width:99%')."</td>
		<td>" . help_icon('{joomlaservername_help}',false,"domains.joomla.php")."</td>
	</tr>		
	<tr><td colspan=3><hr></tr>
	<tr><td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveRoundcubeForm();\"></td></tr>
	</table>";
	
	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,"domains.joomla.php");
	
}





function roudncube_save(){
	$ou=$_GET["ou"];
	$roundcube=new roundcube($ou);
	$roundcube->roundcubeWebsites[$ou]["servername"]=$_GET["roundcubeservername"];
	$roundcube->Save();
	$sock=new sockets();
	$sock->getfile('ApacheGroupWareRestart');
	
}
	
	
	

?>