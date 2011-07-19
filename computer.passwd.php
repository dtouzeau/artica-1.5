<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
	
	if(!Isright()){$tpl=new templates();echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";die();}
	if(isset($_GET["username"])){save();exit;}
	
	if(isset($_GET["index"])){popup();exit;}
	if(isset($_GET["bdc_password"])){save_internal_password();exit;}
	
	js();
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{COMPUTER_ACCESS}');
	$prefix=str_replace(".","_",$page);
	$page=CurrentPageName();	
	$html="
	function {$prefix}Load(){
		YahooWin5(550,'$page?index={$_GET["uid"]}&uid={$_GET["uid"]}','$title');
	}
	
	{$prefix}Load();
	
	
var x_{$prefix}SaveComputerPasswd= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin5Hide();
	if(document.getElementById('FenetreComputerBrowse')){
		Loadjs('ComputerBrowse.php?&computer={$_GET["uid"]}&uid={$_GET["uid"]}&'+document.getElementById('FenetreComputerBrowse').value);
	}
	if(document.getElementById('container-computerinfos-tabs')){RefreshTab('container-computerinfos-tabs');}
	
		
}		
	
	function SaveComputerPasswd(){
		var XHR = new XHRConnection();
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',document.getElementById('password').value);
		document.getElementById('privscomputerform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}SaveComputerPasswd);
	
	}
	
	function SaveComputerInternalPasswd(){
		var XHR = new XHRConnection();
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.appendData('bdc_password',document.getElementById('bdc_password').value);
		document.getElementById('privscomputerform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}SaveComputerPasswd);	
	 
		}
		
	function SaveComputerInternalPasswdEnter(e){
		if(checkEnter(e)){SaveComputerInternalPasswd();}
	}
	
	function SaveComputerPasswdEnter(e){
		if(checkEnter(e)){SaveComputerPasswd();}
	}
	";
	
	echo $html;

	
	
	
}


function popup(){
	$uid=$_GET["index"];
	if(strpos($uid,'$')==0){
		$uid=$uid.'$';
	}
	
	$computer=new computers($uid);
	
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	
	$form="
	<table style='width:100%'>
	<tr>
		<td colspan=2><H5>{howto_access_computer}</H5>
		<p style='font-size:13px'>{COMPUTER_ACCESS_TEXT}</p>
		</td></tr>
		</tr>	
		<tr>
			<td class=legend>{username}:</td>
			<td>". Field_text("username",$ini->_params["ACCOUNT"]["USERNAME"],"width:120px",null,null,null,false,"SaveComputerPasswdEnter(event)")."</td>
		</tr>
		<tr>
			<td class=legend>{password}:</td>
			<td>". Field_password("password",$ini->_params["ACCOUNT"]["PASSWORD"],"width:120px",null,null,null,false,"SaveComputerPasswdEnter(event)")."</td>
		</tr>	
		<tr>
			<td colspan=2 align=right><hr>". button("{edit}","SaveComputerPasswd()")."
					
			</td>
		</tr>
		
		</table>";
	
	$form2="<table style='width:100%'>
	<tr>
		<td colspan=2><H5>{computer_password}</H5>
		<p style='font-size:13px'>{computer_password_text}</p>
		</td></tr>
		</tr>	
		<tr>
			<td class=legend>{password}:</td>
			<td>". Field_password("bdc_password",$computer->userPassword,"width:120px",null,null,null,false,"SaveComputerInternalPasswdEnter(event)")."</td>
		</tr>	
		<tr>
			<td colspan=2 align=right><hr>". button("{edit}","SaveComputerInternalPasswd()")."
					
			</td>
		</tr>
		
		</table>";
	
	$html="<H1>{COMPUTER_ACCESS}::&nbsp;$computer->ComputerRealName</H1>
	
	
	
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/128-network-user.png'></td>
		<td valign='middle'><div id='privscomputerform'>$form$form2</div></td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function save_internal_password(){
$uid=$_GET["uid"];
	if(strpos($uid,'$')==0){
		$uid=$uid.'$';
	}	
	
	$computer=new computers($uid);
	$computer->ComputerChangePassword($_GET["bdc_password"]);
	
}

function save(){
	
$uid=$_GET["uid"];
	if(strpos($uid,'$')==0){
		$uid=$uid.'$';
	}	
	
	$computer=new computers($uid);
	
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	$ini->_params["ACCOUNT"]["USERNAME"]=$_GET["username"];
	$ini->_params["ACCOUNT"]["PASSWORD"]=$_GET["password"];
	$computer->ComputerCryptedInfos=$ini->toString();
	$computer->SaveCryptedInfos();	
	
}
function IsRight(){
	if(!isset($_GET["uid"])){return false;}
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsSambaAdministrator){return true;}
	if($users->AllowAddUsers){return true;}
	if($users->AllowManageOwnComputers){return true;}
	return false;
	}
	
?>