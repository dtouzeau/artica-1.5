<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}


if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["ADSERVER"])){save();exit;}

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ad_samba_member}");
	
	$html="
		function SambaAdLoad(){
			YahooWin('670','$page?popup=yes','$title');
		}
		
	var X_SaveSambaADInfos= function (obj) {
		var results=obj.responseText;
		if(results.length>1){alert(results);}
		SambaAdLoad();
		}			
		
		function SaveSambaADInfos(){
		var XHR = new XHRConnection();
	
		if(document.getElementById('EnableSambaActiveDirectory')){
			XHR.appendData('EnableSambaActiveDirectory',document.getElementById('EnableSambaActiveDirectory').value);
		}
		
		XHR.appendData('ADSERVER',document.getElementById('ADSERVER').value);
		XHR.appendData('ADDOMAIN',document.getElementById('ADDOMAIN').value);
		XHR.appendData('ADADMIN',document.getElementById('ADADMIN').value);
		XHR.appendData('PASSWORD',document.getElementById('PASSWORD').value);
		XHR.appendData('WORKGROUP',document.getElementById('WORKGROUP').value);
		document.getElementById('img_EnableSambaActiveDirectory').src='img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',X_SaveSambaADInfos);	
				
		}
	
	SambaAdLoad();";
	
	
	
	
	echo $html;
	
}

function save(){
	$tpl=new templates();
	
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$_GET["ADSERVER"])){
		echo $tpl->javascript_parse_text("{SAMBAD_NOT_IP_IN_SRVNAME}");
		return;
	}
	
	$sock=new sockets();
	$sock->SET_INFO("EnableSambaActiveDirectory",$_GET["EnableSambaActiveDirectory"]);
	$array=base64_encode(serialize($_GET));
	$sock->SaveConfigFile($array,"SambaAdInfos");
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
}


function popup(){
	
	$users=new usersMenus();
	if(!$users->WINBINDD_INSTALLED){
		$html="<center style='margin:10px;font-size:14px'>{WINBINDD_NOT_INSTALLED_TEXT}</center>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}
	
	$sock=new sockets();
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	$enable=Paragraphe_switch_img("{make_samba_ad}","{make_samba_ad_text}","EnableSambaActiveDirectory",$EnableSambaActiveDirectory);
	
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	
	$form="
	<table style='width:100%;padding:9px' class=form>
	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_server}:</td>
		<td>". Field_text("ADSERVER",$config["ADSERVER"],"font-size:12px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_domain}:</td>
		<td>". Field_text("ADDOMAIN",$config["ADDOMAIN"],"font-size:12px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{workgroup}:</td>
		<td>". Field_text("WORKGROUP",$config["WORKGROUP"],"font-size:12px;padding:3px")."</td>
	</tr>		
	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_admin}:</td>
		<td>". Field_text("ADADMIN",$config["ADADMIN"],"font-size:12px;padding:3px")."</td>
	</tr>		
		
	<tr>
		<td class=legend style='font-size:12px'>{password}:</td>
		<td>". Field_password("PASSWORD",$config["PASSWORD"],"width:100px;font-size:12px;padding:3px")."</td>
	</tr>		
	</table>
	
	";
	
	
	$html="
	<div id='sambaaddiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$enable
		</td>
		<td valign='top'>$form</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>
		". button("{apply}","SaveSambaADInfos()")."
		</td>
	</table>
	</div>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}

?>