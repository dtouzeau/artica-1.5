<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');

	
	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}

	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){echo "alert('$ERROR_NO_PRIVS');";die();}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["mask_lock_options"])){Save();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{shared_folders}::{default_settings}");
	$html="YahooWin5('460','$page?popup=yes','$title')";
	echo $html;
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$SambaDefaultFolderSettings=unserialize(base64_decode($sock->GET_INFO("SambaDefaultFolderSettings")));
	$time=time();
	
	
	if($SambaDefaultFolderSettings["create_mask"]==null){$SambaDefaultFolderSettings["create_mask"]= "0775";}
	if($SambaDefaultFolderSettings["samba_directory_mask"]==null ){$SambaDefaultFolderSettings["samba_directory_mask"]= "0777";}
	if($SambaDefaultFolderSettings["force_create_mode"]==null){$SambaDefaultFolderSettings["force_create_mode"] = "0775";}
	
	
	
	
	$html="
	<div class=explain>{samba_default_settings_explain}</div>
	<div id='$time'>
	<center>
	<table style='width:90%' class=form>
	<tbody>
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{mask_lock_options}:</td>
			<td valign='top'>" . Field_checkbox('mask_lock_options',1,$SambaDefaultFolderSettings["mask_lock_options"])."</td>
			<td valign='top'>&nbsp;</td>
		</tr>	
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{samba_create_mask}:</td>
			<td valign='top'>" . Field_text('default_create_mask',$SambaDefaultFolderSettings["create_mask"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_create_mask_text}")."</td>
		</tr>
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{force_create_mode}:</td>
			<td valign='top'>" . Field_text('default_force_create_mode',$SambaDefaultFolderSettings["force_create_mode"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_create_mask_text}")."</td>
		</tr>			
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{samba_directory_mask}:</td>
			<td valign='top'>" . Field_text('default_samba_directory_mask',$SambaDefaultFolderSettings["samba_directory_mask"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_directory_mask_text}")."</td>
		</tr>	
		<tr>
			<td colspan=3 align=right><hr>". button("{apply}","SaveSambaDefaultSharedSettings()")."</td>
		</tr>
		</tbody>
		</table>	
		</center>
		</div>
		<script>
			function x_SaveSambaDefaultSharedSettings(obj) {
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}	
				Loadjs('$page');		
			}			
			
			
			function SaveSambaDefaultSharedSettings(){
				var XHR = new XHRConnection();
				
				if(document.getElementById('mask_lock_options').checked){XHR.appendData('mask_lock_options',1);}else{XHR.appendData('mask_lock_options',0);}
				XHR.appendData('create_mask',document.getElementById('default_create_mask').value);
				XHR.appendData('force_create_mode',document.getElementById('default_force_create_mode').value);
				XHR.appendData('directory_mask',document.getElementById('default_samba_directory_mask').value);
				AnimateDiv('$time');
				XHR.sendAndLoad('$page', 'POST',x_SaveSambaDefaultSharedSettings);			
			
			}
		
		</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function Save(){
	$sock=new sockets();
	$SambaDefaultFolderSettings=unserialize(base64_decode($sock->GET_INFO("SambaDefaultFolderSettings")));
	while (list ($num, $ligne) = each ($_POST) ){
		$SambaDefaultFolderSettings[$num]=$ligne;
	}
	$data=base64_encode(serialize($SambaDefaultFolderSettings));
	$sock->SaveConfigFile($data, "SambaDefaultFolderSettings");
}


