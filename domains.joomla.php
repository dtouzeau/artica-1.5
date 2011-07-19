<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.joomla.php');
	
	$access=true;
	$users=new usersMenus();
	if(!isset($_GET["ou"])){$access=false;}
	if(!$users->AllowEditOuSecurity){$access=false;}
	if($_SESSION["uid"]<>-100){if($_SESSION["ou"]<>$_GET["ou"]){$access=false;}}
	
	if(!$access){
		$tpl=new templates();
		echo "alert('".$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}')."');";
		die();
		}
		
	if(isset($_GET["joomla-index"])){joomla_index();exit;}
	if(isset($_GET["joomla-install"])){joomla_install();exit;}		
	if(isset($_GET["ldap_connection_user"])){joomla_save();exit;}
	js();

	
	
	
function js(){
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_JOOMLA}');
$page=CurrentPageName();	
$html="



function JoomlaStartPage(){
	YahooWinS(700,'$page?joomla-index=yes&ou={$_GET["ou"]}','$title');
	}
	
var x_JoomlaInstall= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	JoomlaStartPage();
	}

function JoomlaInstall(){
	var XHR = new XHRConnection();
	document.getElementById('logojoom').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.appendData('joomla-install','{$_GET["ou"]}');
	XHR.appendData('ou','{$_GET["ou"]}');	
	XHR.sendAndLoad('$page', 'GET',x_JoomlaInstall);
}

function SaveJoomlaForm(){
	var XHR = new XHRConnection();
	XHR.appendData('ldap_connection_user',document.getElementById('ldap_connection_user').value);
	XHR.appendData('joomlaservername',document.getElementById('joomlaservername').value);
	XHR.appendData('joomlaadminpassword',document.getElementById('joomlaadminpassword').value);
	
	
	XHR.appendData('ou','{$_GET["ou"]}');	
	document.getElementById('joomforms').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_JoomlaInstall);

}


JoomlaStartPage();";
	
	echo $html;
	
}

function joomla_index(){
	
	$sock=new sockets();
	
	$joomlainstalled=trim($sock->getfile("IsJoomlaInstalled:{$_GET["ou"]}"));
	if($joomlainstalled<>1){
		echo joomla_must_install();exit;
	}
	
	$p=joomla_form1($_GET["ou"]);
	
	$sock=new sockets();
	$version=$sock->getfile("JoomlaOuVersion:{$_GET["ou"]}");
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$joomla=new joomla($_GET["ou"]);
	
	if($joomla->params["CONF"]["joomlaservername"]<>null){
		$www="<a href='#' 
		OnClick=\"javascript:s_PopUpFull('http://{$joomla->params["CONF"]["joomlaservername"]}:$ApacheGroupWarePort/administrator',800,600)\"
		style='font-size:12px;font-weight:bold'
		>
		{administrator}:http://{$joomla->params["CONF"]["joomlaservername"]}:$ApacheGroupWarePort/administrator</a>
		";
		
	}
	
	
	$html="
	<H1>{APP_JOOMLA}</H1>
	<table style='width:100%'>
	<tr>
		<td>
			<div id='logojoom'><img src='img/mw_joomla_logo.png'></div>
		</td>
		<td width=99% valign='top'>
			<div style='font-size:18px;font-weight:bold;padding-left:5px;'>Joomla v$version</div>$www
		</td>
	</tr>
	<tr>
		<td colspan=2><div id='joomforms' style='margin-top:5px;width:100%;height:300px;overflow:auto'>$p</div></td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function joomla_must_install(){
	
	
	$p=Paragraphe("64-folder-install.png","{install_your_joomla}","{install_your_joomla_text}","javascript:JoomlaInstall();");
	
$html="
	
<H1>{APP_JOOMLA_NOT_INSTALLED}</H1>
	<table style='width:100%'>
	<tr>
		<td>
			<div id='logojoom'><img src='img/mw_joomla_logo.png'></div>
		</td>
		<td>
		<div id='joom-content' >$p</div>
		</td>
	</tr>
	</table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function joomla_form1($ou){
	$ldap=new clladp();
	$joomla=new joomla($ou);
	
	
	if($joomla->params["CONF"]["ldap_connection_user"]==null){
			if($_SESSION["uid"]==-100){
				$ldap_connection_user=$ldap->ldap_admin;
			}else{
				$ldap_connection_user=$_SESSION["uid"];
			}
	}else{
		$ldap_connection_user=$joomla->params["CONF"]["ldap_connection_user"];
	}
	
	if($joomla->params["CONF"]["joomlaadminpassword"]==null){$joomla->params["CONF"]["joomlaadminpassword"]="secret";}
	
	$html="<table style='width:100%'>
	<tr>
		<td class=legend>{joomlaservername}:</td>
		<td>" . Field_text('joomlaservername',$joomla->params["CONF"]["joomlaservername"],'width:99%')."</td>
		<td>" . help_icon('{joomlaservername_help}')."</td>
	</tr>	
	<tr>
		<td class=legend>{connection_user}:</td>
		<td>" . Field_text('ldap_connection_user',$ldap_connection_user,'width:120px')."</td>
		<td>" . help_icon('{connection_user_help}')."</td>
	</tr>
	<tr>
		<td class=legend>{admin_password}:</td>
		<td>" . Field_password('joomlaadminpassword',$joomla->params["CONF"]["joomlaadminpassword"])."</td>
		<td>" . help_icon('{admin_password_help}')."</td>
	</tr>	
	<tr><td colspan=3><hr></tr>
	<tr><td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveJoomlaForm();\"></td></tr>
	</table>";
	
	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}



function joomla_install(){
	$ou=$_GET["joomla-install"];
	$sock=new sockets();
	$datas=$sock->getfile("JoomlaInstall:$ou");
	
}

function joomla_save(){
	$ou=$_GET["ou"];
$joomla=new joomla($ou);

while (list ($num, $ligne) = each ($_GET) ){
	$joomla->params["CONF"][$num]=$ligne;
	$joomla->SaveParams();
	
}
	
	
}
	
	
	

?>