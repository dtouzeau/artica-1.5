<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.rtmm.tools.inc');

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
if(isset($_GET["oldorg"])){ChangeOrg();exit;}
if(isset($_GET["servername"])){Upload();exit;}
if(isset($_GET["OUlanguage"])){OUSettings();exit;}
if(isset($_GET["export"])){EXPORT_ORG();exit;}

if(isset($_GET["picture"])){popup_picture();exit;}
if(isset($_GET["picture-iframe"])){popup_picture_iframe();exit;}
if(isset($_FILES["ou-photo"])){popup_picture_save();exit;}
if(isset($_POST["passwd_length"])){OUPwd();exit;}

js();



function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$prefix=str_replace("-","_",$prefix);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{ORG_SETTINGS}',"domains.manage.org.index.php");
$ou=$_GET["ou"];
$ou_encrypted=base64_encode($ou);	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		$func="SwitchMoveOrgUser();";
		if(!$users->AsOrgAdmin){
			$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			echo "alert('$error')";
			die();
		}
	}
	if($users->AsArticaAdministrator){$func="SwitchMoveOrgAdmin();";}
	
	$start="{$prefix}Loadpage();";
	if(isset($_GET["js-export"])){
		$title=$tpl->_ENGINE_parse_body("{EXPORT_ORG}::$ou");
		$start="{$prefix}export();";
	}
	
	

$html="
var {$prefix}timeout=0;

	function {$prefix}Loadpage(){
		RTMMail('670','$page?popup=yes&ou=$ou_encrypted','$title::$ou');
	}
	
	function {$prefix}export(){
		RTMMail('550','$page?export=yes&ou=$ou_encrypted','$title::$ou');
	}
	
	function OrgPictureChange(){
		YahooWin5('550','$page?picture=yes&ou=$ou_encrypted','LOGO::$ou');
	}
	
	var x_RenameOrganization=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		$func
	}	
	
	function RenameOrganization(){	
		var oldorg='$ou';
		var neworg=document.getElementById('organization_name').value;
		if(oldorg==neworg){return;}
		var XHR = new XHRConnection();
		XHR.appendData('oldorg',oldorg);
		XHR.appendData('neworg',neworg);		
		XHR.sendAndLoad('$page', 'GET', x_RenameOrganization);				
	}
	
	function SwitchMoveOrgUser(){
		MyHref('logoff.php');
	}
	
	function SwitchMoveOrgAdmin(){
		Loadjs('domains.index.php?js=yes');
		RTMMailHide();
	}
	
	var x_UploadOrganization=function (obj) {
		var results=obj.responseText;
		document.getElementById('uploadinfos').innerHTML=results;
		
	}

	var x_SaveOUDefSettings=function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		{$prefix}Loadpage();
		
	}	
	
	function SaveOUDefSettings(){
	 var language=document.getElementById('OUlanguage').value;
		var XHR = new XHRConnection();
		XHR.appendData('OUlanguage',language);
		XHR.appendData('ou','$ou');		
		AnimateDiv('{$ou}_div');
		XHR.sendAndLoad('$page', 'GET', x_SaveOUDefSettings);			 
	 
	}


	
	function UploadOrganization(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','$ou');
		XHR.appendData('servername',document.getElementById('servername').value);
		XHR.appendData('port',document.getElementById('port').value);
		XHR.appendData('username',document.getElementById('username').value);	
		XHR.appendData('password',document.getElementById('password').value);
		AnimateDiv('uploadinfos');
		XHR.sendAndLoad('$page', 'GET', x_UploadOrganization);			
	}


$start
";
	
	echo $html;
}

function popup(){
	$ou=base64_decode($_GET["ou"]);
	
	$tpl=new templates();
	$page=CurrentPageName();
	$array["popup-index"]='{parameters}';
	$array["privs"]='{privileges}';
	$md=md5($ou);
	
	while (list ($num, $ligne) = each ($array) ){
		
		
		if($num=="privs"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"domains.edit.group.php?GroupPriv=-1&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}

	
	echo "
	<div id=main_config_ou_params_$md style='width:100%;height:595px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_ou_params_$md\").tabs();});
		</script>";		
	
	
	
}


function popup_index(){
	$ou=base64_decode($_GET["ou"]);
	$users=new usersMenus();
	$page=CurrentPageName();
	$ldap=new clladp();
	$hash=$ldap->OUDatas($ou);
	$GLOBALS["DEBUG_PRIVS"]=true;
	writelogs("Parsing privileges of \"$ou\"\n{$hash["ArticaGroupPrivileges"]}",__FUNCTION__,__FILE__,__LINE__);
	
	
	$privs=$ldap->_ParsePrivieleges($hash["ArticaGroupPrivileges"],array(),true);
	$langdef=$privs["ForceLanguageUsers"];
	
	
	$lang["en"]="English";
	$lang["fr"]="Francais";
	$lang["po"]="Portugues";
	$lang["br"]="Brazilian";
	$lang["es"]="Espanol";
	$lang["it"]="Italiano";
	$lang["de"]="Deutsch";
	unset($lang["language"]);
	$lang[null]="{default}";
	$language=Field_array_Hash($lang,'OUlanguage',$langdef,null,null,0,"font-size:13px;padding:5px");	
	
	
	$FAVOURITE_ORG=$_COOKIE["MyFavouriteOrganization"];
	
	
	if($FAVOURITE_ORG==$ou){$FAVOURITE_ORG_ENABLED=1;}
	
	//ArticaGroupPrivileges
	
	$form="
	<table style='width:100%' class=form>
	<tr>
	<td valign='top' class=legend nowrap style='font-size:13px'>{rename_org}:</td>
	<td valign='top'>". Field_text('organization_name',$ou,"font-size:13px;padding:3px")."</td>
	<td valign='top'>". button("{rename}","RenameOrganization()")."</td>
	</tr>
	</table>
	
	";
	
	$form3="
	<table style='width:100%' class=form>
	<tr>
	<td valign='top' class=legend nowrap style='font-size:13px'>{default_language}:</td>
	<td valign='top'>$language</td>
	<td valign='top'>". button("{apply}","SaveOUDefSettings()")."</td>
	</tr>
	</table>";
	
	$policiespwd=unserialize(base64_decode($privs["PasswdPolicy"]));
	
	
	$passwordComlex="
	
	<div style='font-size:16px'>{password_policy}</div>
	<table style='width:100%' class=form>
	<tr>
		<td valign='top' class=legend>{minimum_length}:</td>
		<td>". Field_text("passwd_length",$policiespwd["passwd_length"],"width:50px;font-size:13px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{at_least_one_upper_case_letter}:</td>
		<td>". Field_checkbox("UpperCase",1,$policiespwd["UpperCase"])."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{at_least_one_lower_case_letter}:</td>
		<td>". Field_checkbox("LowerCase",1,$policiespwd["LowerCase"])."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{at_least_one_digit_letter}:</td>
		<td>". Field_checkbox("DigitCase",1,$policiespwd["DigitCase"])."</td>
	</tr>		
	<tr>
		<td valign='top' class=legend>{at_least_one_special_letter}:</td>
		<td>". Field_checkbox("SpecialCase",1,$policiespwd["SpecialCase"])."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>".button("{apply}","SavePwdPol()")."</td>
	</tr>
	</table>
	<script>
	
		function SavePwdPol(){
			var XHR = new XHRConnection();
			XHR.appendData('ou','$ou');
			XHR.appendData('passwd_length',document.getElementById('passwd_length').value);
			if(document.getElementById('UpperCase').checked){XHR.appendData('UpperCase',1);}else{XHR.appendData('UpperCase',0);}
			if(document.getElementById('LowerCase').checked){XHR.appendData('LowerCase',1);}else{XHR.appendData('LowerCase',0);}
			if(document.getElementById('DigitCase').checked){XHR.appendData('DigitCase',1);}else{XHR.appendData('DigitCase',0);}
			if(document.getElementById('SpecialCase').checked){XHR.appendData('SpecialCase',1);}else{XHR.appendData('SpecialCase',0);}
			AnimateDiv('{$ou}_div');
			XHR.sendAndLoad('$page', 'POST', x_SaveOUDefSettings);				
		}
	
	</script>
		
	";
	
	
if($users->EnableManageUsersTroughActiveDirectory){
	$form=null;
	$form3=null;
	$passwordComlex=null;
}
	
	
	$ldap=new clladp();
	$img=$ldap->get_organization_picture(base64_decode($_GET["ou"]),128);

	$fav="<table style='width:100%'>
	<tr>
		<td class=legend>{preferred_org}:</td>
		<td>". Field_checkbox("preferred_org",1,$FAVOURITE_ORG_ENABLED,"PreferredOrg()")."</td>
	</tr>
	</table>";
	
	if(!$users->AsAnAdministratorGeneric){$fav=null;}
	if(!$users->AsOrgAdmin){$passwordComlex=null;}
	
	$button=button("{change}","OrgPictureChange()");
	if($users->EnableManageUsersTroughActiveDirectory){$button=null;}
	
	$html="
	<div class=explain style='font-size:13px'>{ORG_SETTINGS_TEXT}</div>
	$fav
	
	
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<H3>LOGO</H3>
		<div style='width:135px;height:135px;margin:3px;border:1px solid #CCCCCC;padding:5px;margin:5px'>
			<img src='$img'>
		</div>
		<center>$button</center>
		</td>
	<td valign='top'>
		<div id='{$ou}_div'>
			<div style='padding:5px;margin:5px'>$form3</div>
			<br>
			<div style='padding:5px;margin:5px'>$form</div>
			<br>
			<div style='padding:5px;margin:5px'>$passwordComlex</div>			
			
			
		</div>
	</td>
	</table>
	
	<script>
		function PreferredOrg(){
			if(document.getElementById('preferred_org').checked){
				Set_Cookie('MyFavouriteOrganization', '$ou', '3600', '/', '', '');
			}else{
				Set_Cookie('MyFavouriteOrganization', '', '3600', '/', '', '');
			}
			
			CacheOff();
		}
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"domains.manage.org.index.php");
	
}

function OUPwd(){
	$policiespwd=base64_encode(serialize($_POST));
	$ldap=new clladp();
	$ou=$_POST["ou"];
	$upd=array();
	$hash=$ldap->OUDatas($ou);
	writelogs("[$ou]:: ldap->_ParsePrivieleges({$hash["ArticaGroupPrivileges"]});",__FUNCTION__,__FILE__,__LINE__);
	$privs=$ldap->_ParsePrivieleges($hash["ArticaGroupPrivileges"]);
	$privs["PasswdPolicy"]=$policiespwd;
	while (list ($num, $val) = each ($privs) ){
		$values=$values . "[$num]=\"$val\"\n";
	}	

	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["ArticaGroupPrivileges"][0]=$values;
	if(!$ldap->Ldap_modify($dn,$upd)){echo $ldap->ldap_last_error;}	
}

function EXPORT_ORG(){
$ou=base64_decode($_GET["ou"]);	
$session="CopyOrgServerDatas".md5($_SESSION["uid"]);
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO($session));
	$users=new usersMenus();
	$button=button("{upload}","UploadOrganization()");
	if($users->EnableManageUsersTroughActiveDirectory){$button=null;}
	$form2="
	<table style='width:100%'>
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_SERVER}:</td>
	<td valign='top' >". Field_text('servername',$ini->_params["CONF"]["servername"],"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_SERVER_PORT}:</td>
	<td valign='top' >". Field_text('port',$ini->_params["CONF"]["port"],"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_USERNAME}:</td>
	<td valign='top' >". Field_text('username',$ini->_params["CONF"]["username"],"font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
	<td valign='top' class=legend nowrap>{REMOTE_ARTICA_PASSWORD}:</td>
	<td valign='top'>". Field_password('password',$ini->_params["CONF"]["password"],"font-size:13px;padding:3px")."</td>
	</tr>		
	<tr>
		<td colspan=2 align='right'>
		<hr>$button
		</td>
	</tr>
	</table>
	
	";
		
	$tpl=new templates();
	if($users->CURL_PATH==null){$error="<hr>{ERROR_CURL_NOT_INSTALLED}<hr>";echo $tpl->_ENGINE_parse_body($error);exit;}

	$html="<H1>{duplicate_to_remote_server}</H1>
	$form2
	<br>
	<div style='width:100%;height:200px;overflow:auto;font-size:11px;;padding:3px;border:1px solid #CCCCCC' id='uploadinfos'>$error</div>
	
	";
	

	echo $tpl->_ENGINE_parse_body($html,"domains.manage.org.index.php");
		
}

function ChangeOrg(){
	$tpl=new templates();
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		if(!$users->AsOrgAdmin){
			$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			echo html_entity_decode("$error");
			die();
		}
	}
	
	$ldap=new clladp();
	$dn="ou={$_GET["neworg"]},dc=organizations,$ldap->suffix";

	if($ldap->ExistsDN($dn)){
		echo html_entity_decode($tpl->_ENGINE_parse_body("{$_GET["neworg"]} {error_domain_exists}"));
		exit;
	}
	
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?move-ldap-ou=yes&to=".base64_encode($_GET["neworg"])."&from=".base64_encode($_GET["oldorg"])));
	
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		echo html_entity_decode($ligne)."\n";
	}

}
function Upload(){
	$ou=$_GET["ou"];
	$ini=new Bs_IniHandler();
	while (list ($num, $ligne) = each ($_GET) ){
		$ini->_params["CONF"][$num]=$ligne;
		
	}
	
	$session="CopyOrgServerDatas".md5($_SESSION["uid"]);
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),$session);
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?upload-organization=". base64_encode($ou)."&config-file=$session")));
	
	$tpl=new templates();
	if(!is_array($tbl)){echo $tpl->_ENGINE_parse_body("{failed}");return;}

	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne==null)){continue;}
		echo "<div><code>".$tpl->_ENGINE_parse_body(htmlspecialchars($ligne))."</code></div>";
		
	}
}

function popup_picture(){
	
	$html="<iframe style='width:100%;height:400px' src='domains.organization-settings.php?picture-iframe=yes&ou={$_GET["ou"]}'></iframe>";
	echo $html;
	
}

function popup_picture_iframe($error=null){
	$page=CurrentPageName();
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	$ou=$_GET["ou"];
	
	$ldap=new clladp();
	$img=$ldap->get_organization_picture(base64_decode($ou),64);
	
$html="<p>&nbsp;</p>
<div id='content' style='width:400px'>
<table style='width:100%'>
<tr>
<td valign='top'>
<h3>{edit_photo_title_org}</h3>
<p>{edit_photo_title_org_text}</p>
<div style='color:red'>$error</div>
<div style='font-size:11px'><code>$error</code></div>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
<input type='hidden' name='ou' value='$ou'>
<p>
<input type=\"file\" name=\"ou-photo\" size=\"30\">
<div style='width:100%;text-align:right'>
	<input type='submit' name='upload' value='{upload_a_file}&nbsp;&raquo;' style='width:190px'>
</div>
</p>
</form>
</td>
<td valign='top'><img src='$img'></td>
</div>

";	
$tpl=new templates();
$html= $tpl->_ENGINE_parse_body($html,"domains.manage.org.index.php");
echo iframe($html,0,400);
	
}

function popup_picture_save(){
	$tmp_file = $_FILES['ou-photo']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){@mkdir($content_dir);}
	if( !@is_uploaded_file($tmp_file) ){
		echo popup_picture_iframe('{error_unable_to_upload_file} '.$tmp_file);
		exit;
	}
	$name_file = $_FILES['ou-photo']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){
 	echo popup_picture_iframe("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
    $file=$content_dir . "/" .$name_file;
    
    
    if(isset($_POST["ou"])){
		$_GET["ou"]=$_POST["ou"];
		$ldap=new clladp();
		if(!$ldap->set_organization_picture(base64_decode($_GET["ou"]),@file_get_contents("$file"))){
			echo popup_picture_iframe($ldap->ldap_last_error);
			return null;	
		}
    	
    }
    
    echo popup_picture_iframe();
    
}


function OUSettings(){
$ldap=new clladp();
	$ou=$_GET["ou"];
	$upd=array();
	$hash=$ldap->OUDatas($ou);
	writelogs("[$ou]:: ldap->_ParsePrivieleges({$hash["ArticaGroupPrivileges"]});",__FUNCTION__,__FILE__,__LINE__);
	$privs=$ldap->_ParsePrivieleges($hash["ArticaGroupPrivileges"]);
	$privs["ForceLanguageUsers"]=$_GET["OUlanguage"];
	while (list ($num, $val) = each ($privs) ){
		writelogs("[$ou]:: Save $num = $val",__FUNCTION__,__FILE__,__LINE__);
		$values=$values . "[$num]=\"$val\"\n";
	}	
	
	
	
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["ArticaGroupPrivileges"][0]=$values;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo $ldap->ldap_last_error;
	}
	
	
}


?>