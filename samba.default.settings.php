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
	if(isset($_GET["admin-users"])){admin_users();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_POST["mask_lock_options"])){Save();exit;}
	if(isset($_POST["SambaAdminUserAdd"])){admin_users_add();exit;}
	if(isset($_POST["SambaAdminUserDel"])){admin_users_del();exit;}
	
	
	
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{shared_folders}::{default_settings}");
	$html="YahooWin5('600','$page?tabs=yes','$title')";
	echo $html;
}


function tabs(){
	$tpl=new templates();
	$array["popup"]='{parameters}';
	$array["admin-users"]='{admin_users}';
	
	$page=CurrentPageName();

	$t=time();
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&t=$time\"><span>$ligne</span></a></li>\n");
	}
	echo "
	<div id=main_smb_default_settings style='width:100%;height:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_smb_default_settings').tabs();
			
			
			});
		</script>";	
}

function admin_users(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$SambaDefaultFolderSettings=unserialize(base64_decode($sock->GET_INFO("SambaDefaultFolderSettings")));

	$add=imgtootltip("plus-24.png","{add}","Loadjs('MembersBrowse.php?field-user=&OnlyGroups=0&OnlyGUID=0&NOComputers=1&callback=SambaAdminUserAdd')");
	
	
	$html="<div class=explain>{samba_admin_users_explain}</div>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th width=100%>{members}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>"	;
	
	while (list ($uid, $ligne) = each ($SambaDefaultFolderSettings["ADMINSFOLDERS"]) ){	
		
 	if(preg_match("#^@(.+?)$#",$uid,$re)){
			$img="wingroup.png";
			$Displayname="{$re[1]}";
			$gid=$re[2];
		}else{
			$Displayname=$uid;
			$img="winuser.png";
		}
		
		if(substr($uid,strlen($uid)-1,1)=='$'){
			$Displayname=str_replace('$','',$Displayname);
			$img="base.gif";
		}
		if(trim($Displayname)==null){continue;}
		
		$delete=imgtootltip("delete-32.png","{delete}:$Displayname","SambaAdminUserDel('$uid')");
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$html=$html."
		<tr class=$classtr>
		<td width=1% align='center'><img src='img/$img'></td>
		<td width=99%>$js<code style='font-size:14px;color:$color;font-weight:bold'>{$Displayname}</a></code></td>
		<td width=1%>$delete</td>
		</tr>
		";		
		
		
	}
	
	$html=$html."</tbody></table>
	
	<script>
	var x_SambaAdminUserAdd= function (obj) {
		var response=obj.responseText;
		if(response.length>0){alert(response);}
		RefreshTab('main_smb_default_settings');
	}	
	
	
		function SambaAdminUserAdd(id,prependText,guid){
			var XHR = new XHRConnection();
			XHR.appendData('SambaAdminUserAdd',id);
			XHR.sendAndLoad('$page', 'POST',x_SambaAdminUserAdd);
		
		}
		
		function SambaAdminUserDel(id){
			var XHR = new XHRConnection();
			XHR.appendData('SambaAdminUserDel',id);
			XHR.sendAndLoad('$page', 'POST',x_SambaAdminUserAdd);
		
		}		
		
	</script>
		
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function admin_users_add(){
	$sock=new sockets();
	$SambaDefaultFolderSettings=unserialize(base64_decode($sock->GET_INFO("SambaDefaultFolderSettings")));
	$SambaDefaultFolderSettings["ADMINSFOLDERS"][$_POST["SambaAdminUserAdd"]]=true;	
	$data=base64_encode(serialize($SambaDefaultFolderSettings));
	$sock->SaveConfigFile($data, "SambaDefaultFolderSettings");	
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
}
function admin_users_del(){
	$sock=new sockets();
	$SambaDefaultFolderSettings=unserialize(base64_decode($sock->GET_INFO("SambaDefaultFolderSettings")));
	unset($SambaDefaultFolderSettings["ADMINSFOLDERS"][$_POST["SambaAdminUserDel"]]);	
	$data=base64_encode(serialize($SambaDefaultFolderSettings));
	$sock->SaveConfigFile($data, "SambaDefaultFolderSettings");		
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$SambaDefaultFolderSettings=unserialize(base64_decode($sock->GET_INFO("SambaDefaultFolderSettings")));
	$ScanTrashPeriod=$sock->GET_INFO("ScanTrashTime");
	$ScanTrashTTL=$sock->GET_INFO("ScanTrashTTL");
	if(!is_numeric($ScanTrashPeriod)){$ScanTrashPeriod=450;}
	if(!is_numeric($ScanTrashTTL)){$ScanTrashTTL=7;}
	if($ScanTrashPeriod<30){$ScanTrashPeriod=30;}
	if($ScanTrashTTL<1){$ScanTrashTTL=1;}	
	
	$time=time();
	
	
	if($SambaDefaultFolderSettings["create_mask"]==null){$SambaDefaultFolderSettings["create_mask"]= "0775";}
	if($SambaDefaultFolderSettings["directory_mask"]==null ){$SambaDefaultFolderSettings["directory_mask"]= "0777";}
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
			<td valign='top'>" . Field_text('default_samba_directory_mask',$SambaDefaultFolderSettings["directory_mask"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_directory_mask_text}")."</td>
		</tr>
		<tr>
			<td colspan=3><span style='font-size:16px'>{recycle}</td>
		</tr>
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{ScanPeriod}:</td>
			<td valign='top' style='font-size:13px'>" . Field_text('ScanTrashPeriod',$ScanTrashPeriod,"font-size:13px;width:60px")."&nbsp;{minutes}</td>
			<td valign='top'>" . help_icon("{ScanTrashPeriod_text}")."</td>
		</tr>		
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{ttl}:</td>
			<td valign='top' style='font-size:13px'>" . Field_text('ScanTrashTTL',$ScanTrashTTL,"font-size:13px;width:60px")."&nbsp;{days}</td>
			<td valign='top'>" . help_icon("{ScanTrashTTL_text}")."</td>
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
				XHR.appendData('ScanTrashPeriod',document.getElementById('ScanTrashPeriod').value);
				XHR.appendData('ScanTrashTTL',document.getElementById('ScanTrashTTL').value);
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
	$sock->SET_INFO("ScanTrashPeriod", $_POST["ScanTrashPeriod"]);
	$sock->SET_INFO("ScanTrashTTL", $_POST["ScanTrashTTL"]);
	
}


