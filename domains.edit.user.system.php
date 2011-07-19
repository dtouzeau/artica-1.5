<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.lvm.org.inc');	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	
//permissions	
	if(!CheckRights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").'");';
	}
	
	if(isset($_GET["loginShell"])){SaveSystem();exit;}
	
	if(isset($_GET["popup"])){popup();exit;}
	
	
	js();
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{UserSystemInfos}");
	$page=CurrentPageName();
	$html="
	function UserSystemStart(){	
		YahooWin2(600,'$page?popup={$_GET["uid"]}','$title');
		
	}
	var x_UserSystemInfosSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		UserSystemStart();
		}			
		
function UserSystemInfosSave(){
	var loginShell=document.getElementById(\"loginShell\").value;
	var homeDirectory=document.getElementById(\"homeDirectory\").value;
	var UidNumber=document.getElementById(\"UidNumber\").value;
	var XHR = new XHRConnection();
	XHR.appendData('UidNumber',UidNumber);
	XHR.appendData('homeDirectory',homeDirectory);
	XHR.appendData('loginShell',loginShell);
	XHR.appendData('uid','{$_GET["uid"]}');
	document.getElementById('ChangeUserPasswordID').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_UserSystemInfosSave);
	}		
		
	UserSystemStart();";
	
	echo $html;
	
}

function SaveSystem(){
	$user=new user($_GET["uid"]);
	$user->homeDirectory=$_GET["homeDirectory"];
	$user->loginShell=$_GET["loginShell"];
	$user->uid=$_GET["uid"];
	$user->uidNumber=$_GET["UidNumber"];
	$user->edit_system();
	}	
	



function popup(){
	$priv=new usersMenus();
	$ct=new user($_GET["popup"]);
	$sock=new sockets();
	
	
	$lvm=new lvm_org($ct->ou);
	$array=$lvm->disklist;	
	$paths["/home/$ct->uid"]="/home/$ct->uid ({system_disk})";
	if(is_array($array)){
		while (list ($num, $val) = each ($array) ){
			$sock=new sockets();
			$results=$sock->getFrameWork("cmd.php?fstab-get-mount-point=yes&dev=$num");
			$array2=unserialize(base64_decode($results));
			while (list ($num, $mounted) = each ($array2) ){
				$paths[trim("$mounted/$ct->uid")]=dirname(trim($mounted.'/'.$ct->uid) . "({dedicated_storage})");
			}
			
		}
	}
	
	
	
	
	$loginShell_hidden=Field_hidden('loginShell',$us->loginShell).Field_hidden('uidNumber',$us->uidNumber);
	$loginShell="<tr>
					<td align='right' nowrap class=legend $styleTDRight>{loginShell}:</strong>
					<td $styleTDLeft>
							<table style='width:100%;margin-left:-4px;'>
							<tr>
							<td align=left width=1%>" . Field_text('loginShell',$us->loginShell,'width:90px')."</td>
							<td align=left>" . help_icon('{loginShellText}',true)."</td>
							<td class=legend nowrap>{UidNumber}:</td>
							<td align=left width=1%>" . Field_text('uidNumber',$us->uidNumber,'width:90px')."</td>
							</tr>
							</table>
					</td>
					</tr>";	
							
	$dotclear="<tr>
		<td class=legend nowrap>{DotClearUserEnabled}:</td>
		<td align=left width=1%>" . Field_numeric_checkbox_img('DotClearUserEnabled',$us->DotClearUserEnabled) ."</td>
		<td align=left>" . help_icon('{DotClearUserEnabledText}',true)."</td>
	</tr>";				
	
	$form=Field_hidden('USER_SYSTEM_INFOS_UID',$ct->uid). "
	<div style='text-align:right;margin-top:-5px;margin-bottom:9px;'><code>{home}:$ct->homeDirectory</code></div>
	<div id='ChangeUserPasswordID'>
	<table style='width:100%'>
	
	<tr>
		<td class=legend nowrap>{loginShell}:</td>
		<td align=left width=1%>" . Field_text('loginShell',$ct->loginShell,'width:190px')."</td>
		<td align=left>" . help_icon('{loginShellText}',true)."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{homeDirectory}:</td>
		<td align=left width=1%>" . Field_array_Hash($paths,'homeDirectory',$ct->homeDirectory)."</td>
		<td align=left>" . help_icon('{homeDirectoryText}',true)."</td>
	</tr>		
	<tr>
		<td class=legend nowrap>{UidNumber}:</td>
		<td align=left width=1%>" . Field_text('UidNumber',$ct->uidNumber,'width:90px')."</td>
		<td align=left>" . help_icon('{UidNumberText}',true)."</td>
	</tr>
		
		
	<tr><td colspan=3><hr></td></tr>
	<tr>
		<td colspan=3 align='right'>".button("{edit}","UserSystemInfosSave()")."
	</tr>
	</table>
	</div>
	";
	
	$form=RoundedLightWhite($form);
	$safebox=Paragraphe("safe-box-64.png","{coffrefort}","{coffrefort_create_user}","javascript:Loadjs('domains.edit.user.safebox.php?uid=$ct->uid')");
	if(!$priv->CRYPTSETUP_INSTALLED){$safebox=null;}
	$html="<H1>{UserSystemInfos}</H1>
	<div style='margin-top:-35px;text-align:right;margin-bottom:30px;width:95%'>
		<i style='font-size:16px;font-weight:bold;padding-bottom:4px;color:white'>$ct->DisplayName</i>
	</div>
	<div id='UserSystemInfosSave'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$safebox
		</td>
		<td valign='top'>
			$form
		</td>
	</tr>
	</table>
	
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function CheckRights(){
$usersprivs=new usersMenus();
if($usersprivs->AsAnAdministratorGeneric){return true;}
if($usersprivs->AsOrgAdmin){return true;}
if($usersprivs->AllowAddUsers){return true;}	
	
}
?>