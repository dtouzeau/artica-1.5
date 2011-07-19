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
		
	if(isset($_GET["sugar-index"])){sugar_index();exit;}
	if(isset($_GET["sugar-install"])){sugar_install();exit;}		
	if(isset($_GET["sugaradminpassword"])){sugar_save();exit;}
	js();

	
	
	
function js(){
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_SUGARCRM}');
$page=CurrentPageName();	
$html="



function sugarStartPage(){
	YahooWinS(700,'$page?sugar-index=yes&ou={$_GET["ou"]}','$title');
	}
	
var x_sugarInstall= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	sugarStartPage();
	}

function sugarInstall(){
	var XHR = new XHRConnection();
	document.getElementById('logojoom').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'
	XHR.appendData('sugar-install','{$_GET["ou"]}');
	XHR.appendData('ou','{$_GET["ou"]}');	
	XHR.sendAndLoad('$page', 'GET',x_sugarInstall);
}

function SavesugarForm(){
	var XHR = new XHRConnection();
	XHR.appendData('sugaradminname',document.getElementById('sugaradminname').value);
	XHR.appendData('sugarservername',document.getElementById('sugarservername').value);
	XHR.appendData('sugaradminpassword',document.getElementById('sugaradminpassword').value);
	
	
	XHR.appendData('ou','{$_GET["ou"]}');	
	document.getElementById('joomforms').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_sugarInstall);

}


sugarStartPage();";
	
	echo $html;
	
}

function sugar_index(){
	
	$sock=new sockets();
	
	$sugarinstalled=trim($sock->getfile("IsSugarCRMInstalled:{$_GET["ou"]}"));
	if($sugarinstalled<>1){
		echo sugar_must_install();exit;
	}
	
	$p=sugar_form1($_GET["ou"]);
	
	$sock=new sockets();
	$version=$sock->getfile("SugarCRMOuVersion:{$_GET["ou"]}");
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$sugar=new joomla($_GET["ou"]);
	
	if($sugar->params["CONF"]["sugarservername"]<>null){
		$www="<a href='#' 
		OnClick=\"javascript:s_PopUpFull('http://{$sugar->params["CONF"]["sugarservername"]}:$ApacheGroupWarePort',800,600)\"
		style='font-size:12px;font-weight:bold'
		>
		{website}:http://{$sugar->params["CONF"]["sugarservername"]}:$ApacheGroupWarePort</a>
		";
		
	}
	
	
	$html="
	<H1>{APP_SUGARCRM}</H1>
	<table style='width:100%'>
	<tr>
		<td>
			<div id='logojoom'><img src='img/98-sugarcrm.png'></div>
		</td>
		<td width=99% valign='top'>
			<div style='font-size:18px;font-weight:bold;padding-left:5px;'>SugarCRM v$version</div>$www
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

function sugar_must_install(){
	
	
	$p=Paragraphe("64-folder-install.png","{install_your_sugar}","{install_your_sugar_text}","javascript:sugarInstall();");
	
$html="
	
<H1>{APP_sugar_NOT_INSTALLED}</H1>
	<table style='width:100%'>
	<tr>
		<td>
			<div id='logojoom'><img src='img/98-sugarcrm.png'></div>
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

function sugar_form1($ou){
	$ldap=new clladp();
	$sugar=new joomla($ou);
	
	
	if($sugar->params["CONF"]["ldap_connection_user"]==null){
			if($_SESSION["uid"]==-100){
				$ldap_connection_user=$ldap->ldap_admin;
			}else{
				$ldap_connection_user=$_SESSION["uid"];
			}
	}else{
		$ldap_connection_user=$sugar->params["CONF"]["ldap_connection_user"];
	}
	
	
	
	if($sugar->params["CONF"]["sugaradminname"]==null){$sugar->params["CONF"]["sugaradminname"]="admin";}
	if($sugar->params["CONF"]["sugaradminpassword"]==null){$sugar->params["CONF"]["sugaradminpassword"]="secret";}
	
	$html="<table style='width:100%'>
	<tr>
		<td class=legend>{sugarservername}:</td>
		<td>" . Field_text('sugarservername',$sugar->params["CONF"]["sugarservername"],'width:99%')."</td>
		<td>" . help_icon('{joomlaservername_help}',false,"domains.joomla.php")."</td>
	</tr>	
	<tr>
		<td class=legend>{admin_name}:</td>
		<td>" . Field_text('sugaradminname',$sugar->params["CONF"]["sugaradminname"],'width:120px')."</td>
		<td>" . help_icon('{connection_user_help}')."</td>
	</tr>
	<tr>
		<td class=legend>{admin_password}:</td>
		<td>" . Field_password('sugaradminpassword',$sugar->params["CONF"]["sugaradminpassword"])."</td>
		<td>" . help_icon('{admin_password_help}')."</td>
	</tr>	
	<tr><td colspan=3><hr></tr>
	<tr><td colspan=3 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SavesugarForm();\"></td></tr>
	</table>";
	
	
	$html=RoundedLightWhite($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html,"domains.joomla.php");
	
}



function sugar_install(){
	$ou=$_GET["sugar-install"];
	$sock=new sockets();
	$datas=$sock->getfile("sugarInstall:$ou");
	echo $datas;
	
}

function sugar_save(){
	$ou=$_GET["ou"];
	$name=str_replace('.','_',$ou);
	$name=str_replace('-','_',$name);	
	$ldap=new clladp();
	$ini=new Bs_IniHandler();
	$ini->_params["CONF"]["sugaradminname"]=$_GET["sugaradminname"];
	$ini->_params["CONF"]["sugaradminpassword"]=$_GET["sugaradminpassword"];
	$ini->_params["CONF"]["sugarservername"]=$_GET["sugarservername"];
	$ini->_params["CONF"]["ou"]=$_GET["ou"];
	$ini->_params["CONF"]["ldap_connection_user"]=$ldap->ldap_admin;
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),"JoomlaConfOrg_{$name}");
	
	$su=new SugarCRM($ou);
	$su->CreateAdminPassword();
	$su->UpdateLDAPConfig();
	
}
	
	
	

?>