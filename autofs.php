<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');

if(posix_getuid()<>0){
	$users=new usersMenus();
	if((!$users->AsSambaAdministrator) OR (!$users->AsSystemAdministrator)){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}

if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["autofs-status"])){status_service();exit;}
if(isset($_GET["EnableAutoFSDebug"])){EnableAutoFSDebugSave();exit;}


if(isset($_GET["mounts"])){mounts_list();exit;}
if(isset($_GET["form-add-js"])){form_add_js();exit;}
if(isset($_GET["form-add-popup"])){form_add_popup();exit;}
if(isset($_GET["form-add-details"])){form_add_details();exit;}
if(isset($_GET["form-add-usblist"])){usblist();exit;}

if(isset($_GET["FTP_SERVER"])){PROTO_FTP_ADD();exit;}
if(isset($_GET["CIFS_SERVER"])){PROTO_CIFS_ADD();exit;}
if(isset($_GET["NFS_SERVER"])){PROTO_NFS_ADD();exit;}
if(isset($_GET["HTTP_SERVER"])){PROTO_WEBDAVFS_ADD();exit;}



if(isset($_GET["USB_UUID"])){PROTO_USB_ADD();exit;}
if(isset($_GET["AutoFSDeleteDN"])){mounts_delete();exit;}
if(isset($_GET["logs"])){mounts_events();exit;}
if(isset($_GET["syslog-table"])){mounts_events_query();exit;}


js();


function form_add_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{add_mount_point}");
	echo "YahooWin4('650','$page?form-add-popup=yes&dn={$_GET["dn"]}','$title');";
	
}


function form_add_popup(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();	
	if($users->CURLFTPFS_INSTALLED){$protos["FTP"]="{ftp_directory}";}
	if($users->CIFS_INSTALLED){$protos["CIFS"]="{windows_network_share}";}
	if($users->DAVFS_INSTALLED){$protos["DAVFS"]="{TAB_WEBDAV}";}
	$protos["NFSV3"]="NFS v3";
	$protos["NFSV4"]="NFS v4";
	$protos["USB"]="{external_device}";
	$protos[null]="{select}";
	
	$html="
	<div id='form-autofs-add-div'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{filesystem_type}:</td>
		<td>". Field_array_Hash($protos,"proto",null,"ChangeFS()",null,0,"font-size:16px;padding:3px")."</td>
	</tr>
	</table>
	<hr>
	<div id='autofs-details'></div>
	</div>
	<script>
		function ChangeFS(){
			var proto=document.getElementById('proto').value;
			LoadAjax('autofs-details','$page?form-add-details=yes&dn={$_GET["dn"]}&proto='+proto);
		}
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
	
}

function form_add_details(){
switch ($_GET["proto"]) {
		case 'FTP':form_add_details_FTP();break;
		case 'CIFS':form_add_details_CIFS();break;
		case 'NFSV3':form_add_details_NFS();break;
		case 'NFSV4':form_add_details_NFS();break;
		case 'NFSV4':form_add_details_NFS();break;
		case 'DAVFS':form_add_details_DAVFS();break;
		case 'Start':StartApplyConfig();break;
		default:
			break;
	}
	
}

function EnableAutoFSDebugSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableAutoFSDebug",$_GET["EnableAutoFSDebug"]);
	$sock->getFrameWork("cmd.php?autofs-restart=yes");
	
	
}
function form_add_details_USB(){
	
	$dn=$_GET["dn"];
	$page=CurrentPageName();
	$tpl=new templates();		
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{local_directory_name}:</td>
		<td>". Field_text("USB_LOCAL_DIR",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>			
	</table>
	<div style='width:100%;text-align:right;margin-bottom:5px'>". imgtootltip("refresh-32.png","{refresh}","refreshUsbList()")."</div>
	<div id='local-sub-list' style='height:250px;overflow:auto'></div>
	
	<script>


	function refreshUsbList(){
		LoadAjax('local-sub-list','$page?form-add-usblist=yes');
	}
	refreshUsbList();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function usblist(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	if(!file_exists('ressources/usb.scan.inc')){return ;}
	include("ressources/usb.scan.inc");
	include_once("ressources/class.os.system.tools.inc");
	$os=new os_system();
	$count=0;
	//print_r($_GLOBAL["usb_list"]);
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{type}</th>
		<th>{label}</th>
		<th>{size}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($UUID, $ligne) = each ($_GLOBAL["usb_list"]) ){
		$TYPE=$ligne["TYPE"];
		$ID_MODEL=$ligne["ID_MODEL"];
		$LABEL=$ligne["LABEL"];
		$DEV=$ligne["DEV"];
		if($LABEL==null){$LABEL=$ID_MODEL;}
		$SIZE=explode(";",$ligne["SIZE"]);
		
		$real_size=$SIZE[0];
		if($DEV<>null){$LABEL=$LABEL." ($DEV)";}
		
		
		if($real_size==null){$real_size="{unknown}";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$selected=imgtootltip("plus-24.png","{select}","AutoFSUSB('$UUID')");
		$html=$html . "
		<tr  class=$classtr>
			<td width=1%><img src='img/usb-32-green.png'></td>
			<td width=1% align='center' nowrap><strong style='font-size:14px'><code style='color:$color'>$TYPE</code></td>
			<td width=99% align='left'><strong style='font-size:14px'>$LABEL</strong></td>
			<td width=1% align='left' nowrap><strong style='font-size:14px'>$real_size</strong></td>
			<td width=1%>$selected</td>
		</tr>";		
		
		
	}
	
	$html=$html."</tbody></table>
	<script>

	var x_AutoFSUSB= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;}	
		YahooWin4Hide();
		if(document.getElementById('main_config_autofs')){RefreshTab('main_config_autofs');}
		if(document.getElementById('BackupTaskAutoFSMountedList')){RefreshAutoMountsBackup();}
	}		
	
	function AutoFSUSB(key){
		var XHR = new XHRConnection();
		XHR.appendData('USB_UUID',key);	
		var localdir=document.getElementById('USB_LOCAL_DIR').value;
		if(localdir.length==0){alert('LOCAL DIR !');return;}
		XHR.appendData('USB_LOCAL_DIR',document.getElementById('USB_LOCAL_DIR').value);
		XHR.sendAndLoad('$page', 'GET',x_AutoFSUSB);
		}	

	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function form_add_details_FTP(){
	$dn=$_GET["dn"];
	$page=CurrentPageName();
	$tpl=new templates();		
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{remote_server_name}:</td>
		<td>". Field_text("FTP_SERVER",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{ftp_user}:</td>
		<td>". Field_text("FTP_USER",null,"font-size:16px;padding:3px;width:190px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{password}:</td>
		<td>". Field_password("FTP_PASSWORD",null,"font-size:16px;padding:3px;width:190px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{local_directory_name}:</td>
		<td>". Field_text("FTP_LOCAL_DIR",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>			
	<tr>
		<td colspan=2 align='right' style='font-size:16px'>
		<hr>". button("{apply}","SaveAutoFsFTP()")."</td>
	</tr>
	</table>
	
	<script>
		
var x_SaveAutoFsFTP= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);return;}
	YahooWin4Hide();
	if(document.getElementById('main_config_autofs')){RefreshTab('main_config_autofs');}
	if(document.getElementById('BackupTaskAutoFSMountedList')){RefreshAutoMountsBackup();}
	}	
		
	function SaveAutoFsFTP(){
		var XHR = new XHRConnection();
		XHR.appendData('FTP_SERVER',document.getElementById('FTP_SERVER').value);
		XHR.appendData('FTP_USER',document.getElementById('FTP_USER').value);
		XHR.appendData('FTP_PASSWORD',document.getElementById('FTP_PASSWORD').value);
		XHR.appendData('FTP_LOCAL_DIR',document.getElementById('FTP_LOCAL_DIR').value);
		//document.getElementById('form-autofs-add-div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
		XHR.sendAndLoad('$page', 'GET',x_SaveAutoFsFTP);
	}		
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function form_add_details_DAVFS(){
$dn=$_GET["dn"];
	$page=CurrentPageName();
	$tpl=new templates();		
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{acl_dst}:</td>
		<td>". Field_text("HTTP_SERVER",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{web_user}:</td>
		<td>". Field_text("HTTP_USER",null,"font-size:16px;padding:3px;width:190px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{password}:</td>
		<td>". Field_password("HTTP_PASSWORD",null,"font-size:16px;padding:3px;width:190px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{local_directory_name}:</td>
		<td>". Field_text("HTTP_LOCAL_DIR",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>			
	<tr>
		<td colspan=2 align='right' style='font-size:16px'>
		<hr>". button("{apply}","SaveAutoWebDavfs()")."</td>
	</tr>
	</table>
	
	<script>
		
var x_SaveAutoWebDavfs= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);return;}
	YahooWin4Hide();
	if(document.getElementById('main_config_autofs')){RefreshTab('main_config_autofs');}
	if(document.getElementById('BackupTaskAutoFSMountedList')){RefreshAutoMountsBackup();}
	}	
		
	function SaveAutoWebDavfs(){
		var XHR = new XHRConnection();
		XHR.appendData('HTTP_SERVER',document.getElementById('HTTP_SERVER').value);
		XHR.appendData('HTTP_USER',document.getElementById('HTTP_USER').value);
		XHR.appendData('HTTP_PASSWORD',document.getElementById('HTTP_PASSWORD').value);
		XHR.appendData('HTTP_LOCAL_DIR',document.getElementById('HTTP_LOCAL_DIR').value);
		//document.getElementById('form-autofs-add-div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
		XHR.sendAndLoad('$page', 'GET',x_SaveAutoWebDavfs);
	}		
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
		
	
}

function form_add_details_NFS(){
	$dn=$_GET["dn"];
	$page=CurrentPageName();
	$tpl=new templates();	

switch ($_GET["proto"]) {
		case 'NFSV3':$type="nfs3";break;
		case 'NFSV4':$type="nfs4";break;
		default:
			break;
	}	
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{remote_server_name}:</td>
		<td>". Field_text("NFS_SERVER",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{target_directory}:</td>
		<td>". Field_text("NFS_FOLDER",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{local_directory_name}:</td>
		<td>". Field_text("NFS_LOCAL_DIR",null,"font-size:16px;padding:3px;width:220px")."</td>
	</tr>			
	<tr>
		<td colspan=2 align='right' style='font-size:16px'>
		<hr>". button("{apply}","SaveAutoFsNFS()")."</td>
	</tr>
	</table>
	
	<script>
		
var x_SaveAutoFsNFS= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);return;}
	YahooWin4Hide();
	if(document.getElementById('main_config_autofs')){RefreshTab('main_config_autofs');}
	if(document.getElementById('BackupTaskAutoFSMountedList')){RefreshAutoMountsBackup();}
	}	
		
	function SaveAutoFsNFS(){
		var XHR = new XHRConnection();
		XHR.appendData('NFS_SERVER',document.getElementById('NFS_SERVER').value);
		XHR.appendData('NFS_FOLDER',document.getElementById('NFS_FOLDER').value);
		XHR.appendData('NFS_LOCAL_DIR',document.getElementById('NFS_LOCAL_DIR').value);
		XHR.appendData('NFS_PROTO','$type');
		//document.getElementById('form-autofs-add-div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
		XHR.sendAndLoad('$page', 'GET',x_SaveAutoFsNFS);
	}		
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function form_add_details_CIFS(){
	$dn=$_GET["dn"];
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();	

	
	if($users->SAMBA_INSTALLED){
		$button_browes="<input type='button' 
		OnClick=\"javascript:Loadjs('samba.smbtree.php?server-field=CIFS_SERVER&folder-field=CIFS_FOLDER')\" 
		value=\"{browse}&nbsp;&raquo;&raquo\">";
	}
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{remote_server_name}:</td>
		<td>". Field_text("CIFS_SERVER",null,"font-size:16px;padding:3px;width:220px")."</td>
		<td>$button_browes</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{target_directory}:</td>
		<td>". Field_text("CIFS_FOLDER",null,"font-size:16px;padding:3px;width:220px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{username}:</td>
		<td>". Field_text("CIFS_USER",null,"font-size:16px;padding:3px;width:190px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{password}:</td>
		<td>". Field_password("CIFS_PASSWORD",null,"font-size:16px;padding:3px;width:190px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{local_directory_name}:</td>
		<td>". Field_text("CIFS_LOCAL_DIR",null,"font-size:16px;padding:3px;width:220px")."</td>
		<td>&nbsp;</td>
	</tr>			
	<tr>
		<td colspan=3 align='right' style='font-size:16px'>
		<hr>". button("{apply}","SaveAutoFsCIFS()")."</td>
	</tr>
	</table>
	
	<script>
		
var x_SaveAutoFsCIFS= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);return;}
	YahooWin4Hide();
	if(document.getElementById('main_config_autofs')){RefreshTab('main_config_autofs');}
	if(document.getElementById('BackupTaskAutoFSMountedList')){RefreshAutoMountsBackup();}
	
	}	
		
	function SaveAutoFsCIFS(){
		var XHR = new XHRConnection();
		XHR.appendData('CIFS_SERVER',document.getElementById('CIFS_SERVER').value);
		XHR.appendData('CIFS_USER',document.getElementById('CIFS_USER').value);
		XHR.appendData('CIFS_FOLDER',document.getElementById('CIFS_FOLDER').value);
		XHR.appendData('CIFS_PASSWORD',document.getElementById('CIFS_PASSWORD').value);
		XHR.appendData('CIFS_LOCAL_DIR',document.getElementById('CIFS_LOCAL_DIR').value);
		//document.getElementById('form-autofs-add-div').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
		XHR.sendAndLoad('$page', 'GET',x_SaveAutoFsCIFS);
	}		
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function PROTO_USB_ADD(){
	$ldap=new clladp();
	$sock=new sockets();
	if($_GET["USB_LOCAL_DIR"]==null){$_GET["USB_LOCAL_DIR"]=$_GET["USB_UUID"];}
	$_GET["USB_LOCAL_DIR"]=strtolower($ldap->StripSpecialsChars($_GET["USB_LOCAL_DIR"]));
	
	$upd=array();
	$dn="cn={$_GET["USB_LOCAL_DIR"]},ou=auto.automounts,ou=mounts,$ldap->suffix";	
	
	$pattern="-fstype=auto\tUUID=\"{$_GET["USB_UUID"]}\"";	
	
	if(!$ldap->ExistsDN($dn)){
		$upd["ObjectClass"][]='top';
		$upd["ObjectClass"][]='automount';
		$upd["cn"][]="{$_GET["USB_LOCAL_DIR"]}";
		$upd["automountInformation"][]=$pattern;
		if(!$ldap->ldap_add($dn,$upd)){echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;return;}
		$sock->getFrameWork("cmd.php?autofs-reload=yes");
		return;
	}
	
	
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
		return false;
	}	
	
	$sock->getFrameWork("cmd.php?autofs-reload=yes");	
	
}

function PROTO_CIFS_ADD(){
	$ldap=new clladp();
	$sock=new sockets();
	$auto=new autofs();
	
	$_GET["CIFS_LOCAL_DIR"]=strtolower($ldap->StripSpecialsChars($_GET["CIFS_LOCAL_DIR"]));
	$upd=array();
	$dn="cn={$_GET["CIFS_LOCAL_DIR"]},ou=auto.automounts,ou=mounts,$ldap->suffix";	
	if($_GET["CIFS_USER"]<>null){
		$auth="user={$_GET["CIFS_USER"]},pass={$_GET["CIFS_PASSWORD"]}";
	}
	
	$pattern="-fstype=cifs,rw,noperm,$auth ://{$_GET["CIFS_SERVER"]}/{$_GET["CIFS_FOLDER"]}";	
	
	if(!$ldap->ExistsDN($dn)){
		$upd["ObjectClass"][]='top';
		$upd["ObjectClass"][]='automount';
		$upd["cn"][]="{$_GET["CIFS_LOCAL_DIR"]}";
		$upd["automountInformation"][]=$pattern;
		if(!$ldap->ldap_add($dn,$upd)){echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;return;}
		$sock->getFrameWork("cmd.php?autofs-reload=yes");
		return;
	}
	
	
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
		return false;
	}	
	
	$sock->getFrameWork("cmd.php?autofs-reload=yes");	
}

function PROTO_NFS_ADD(){
	$auto=new autofs();
	$ldap=new clladp();
	$sock=new sockets();
	$_GET["NFS_LOCAL_DIR"]=strtolower($ldap->StripSpecialsChars($_GET["NFS_LOCAL_DIR"]));
	$upd=array();
	$dn="cn={$_GET["NFS_LOCAL_DIR"]},ou=auto.automounts,ou=mounts,$ldap->suffix";	
	
	$pattern="-fstype={$_GET["NFS_PROTO"]},rw,soft,intr,rsize=8192,wsize=8192\t{$_GET["NFS_SERVER"]}/{$_GET["NFS_FOLDER"]}/&";	
	
	if(!$ldap->ExistsDN($dn)){
		$upd["ObjectClass"][]='top';
		$upd["ObjectClass"][]='automount';
		$upd["cn"][]="{$_GET["NFS_LOCAL_DIR"]}";
		$upd["automountInformation"][]=$pattern;
		if(!$ldap->ldap_add($dn,$upd)){echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;return;}
		$sock->getFrameWork("cmd.php?autofs-reload=yes");
		return;
	}
	
	
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
		return false;
	}	
	
	$sock->getFrameWork("cmd.php?autofs-reload=yes");	
}

function PROTO_WEBDAVFS_ADD(){
	$auto=new autofs();
	$ldap=new clladp();
	$sock=new sockets();
	$_GET["HTTP_LOCAL_DIR"]=strtolower($ldap->StripSpecialsChars($_GET["HTTP_LOCAL_DIR"]));
	$upd=array();
	$uri=$_GET["HTTP_SERVER"];
	
	if(!preg_match("#^http.*?:\/\/#",$_GET["HTTP_SERVER"],$re)){
		echo "{$_GET["HTTP_SERVER"]}: Bad format\nuse https://... or http://...";
		return;
	}
	
	
	$_GET["HTTP_SERVER"]=str_replace(":","\:",$_GET["HTTP_SERVER"]);
	
	
	if(substr($_GET["HTTP_SERVER"],strlen($_GET["HTTP_SERVER"])-1,1)<>"/"){
		$_GET["HTTP_SERVER"]=$_GET["HTTP_SERVER"]."/";
	}
	
	
	
	$dn="cn={$_GET["HTTP_LOCAL_DIR"]},ou=auto.automounts,ou=mounts,$ldap->suffix";	
	$pattern="-fstype=davfs,rw,nosuid,nodev,user :{$_GET["HTTP_SERVER"]}";
	
	$password=addslashes($_GET["HTTP_PASSWORD"]);
	$user=addslashes($_GET["HTTP_USER"]);
	$local_dir=addslashes($_GET["HTTP_LOCAL_DIR"]);
	$q=new mysql();
	
if(!$ldap->ExistsDN($dn)){
	$sql="INSERT IGNORE INTO automount_davfs(local_dir,user,password,uri) VALUES('$local_dir','$user','$password','$uri')";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$upd["ObjectClass"][]='top';
	$upd["ObjectClass"][]='automount';
	$upd["cn"][]="{$_GET["HTTP_LOCAL_DIR"]}";
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->ldap_add($dn,$upd)){echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;return;}
	$sock->getFrameWork("cmd.php?autofs-reload=yes");
	return;
	}
	
	
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
		return false;
	}	
	
	$sql="UPDATE automount_davfs SET `user`='$user',`password`='$password',`uri`='$uri' WHERE `local_dir`='$local_dir'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	$sock->getFrameWork("cmd.php?autofs-reload=yes");	
}


function PROTO_FTP_ADD(){
	$auto=new autofs();
	$ldap=new clladp();
	$sock=new sockets();
	$_GET["FTP_LOCAL_DIR"]=strtolower($ldap->StripSpecialsChars($_GET["FTP_LOCAL_DIR"]));
	$upd=array();
	$dn="cn={$_GET["FTP_LOCAL_DIR"]},ou=auto.automounts,ou=mounts,$ldap->suffix";
	
	if($_GET["FTP_USER"]<>null){
		$auth="{$_GET["FTP_USER"]}\:{$_GET["FTP_PASSWORD"]}\@";
	}
	
	$pattern="-fstype=curl,allow_other :ftp\://$auth{$_GET["FTP_SERVER"]}/";
	

if(!$ldap->ExistsDN($dn)){
	$upd["ObjectClass"][]='top';
	$upd["ObjectClass"][]='automount';
	$upd["cn"][]="{$_GET["FTP_LOCAL_DIR"]}";
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->ldap_add($dn,$upd)){echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;return;}
	$sock->getFrameWork("cmd.php?autofs-reload=yes");
	return;
	}
	
	
	$upd["automountInformation"][]=$pattern;
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;
		return false;
	}	
	
	$sock->getFrameWork("cmd.php?autofs-reload=yes");
}


function js(){
		$page=CurrentPageName();
		$html="
		
		
		function AutofsConfigLoad(){
			$('#BodyContent').load('$page?tabs=yes');
			}
			
		AutofsConfigLoad();
		";
		echo $html;
		
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$add=Paragraphe("net-disk-add-64.png","{add_mount_point}","{add_mount_point_text}",
	"javascript:Loadjs('$page?form-add-js=yes')");
	$EnableAutoFSDebug=$sock->GET_INFO("EnableAutoFSDebug");
	if(!is_numeric($EnableAutoFSDebug)){$EnableAutoFSDebug=1;}
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<div id='autofs-status'></div>
		</td>
		<td valign='top' width=99%'>
			<div class=explain>{autofs_about}</div>
		<center>
			$add
		</center>

		<table class=form style='width:100%'>
			<tr>
				<td class=legend>{debug_mode}:</td>
				<td>". Field_checkbox("EnableAutoFSDebug",1,$EnableAutoFSDebug,"EnableAutoFSDebugCheck()")."
			</td>
		</table>
	</tr>
	</table>
	<script>
	
	
		function EnableAutoFSDebugCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableAutoFSDebug').checked){
				XHR.appendData('EnableAutoFSDebug','1');
			}else{
				XHR.appendData('EnableAutoFSDebug','0');
			}
	
			XHR.sendAndLoad('$page', 'GET');
		}	
	
		
	
	
		LoadAjax('autofs-status','$page?autofs-status=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function status_service(){
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?autofs-ini-status=yes")));
	
	
	
	$status=DAEMON_STATUS_ROUND("APP_AUTOFS",$ini,null,1);
	echo $tpl->_ENGINE_parse_body($status);		
	
}


function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["mounts"]='{mounts_list}';
	$array["logs"]='{events}';
	
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_autofs style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_autofs\").tabs();});
		</script>";		
		
	
}

function mounts_list(){
		$autofs=new autofs();
	$page=CurrentPageName();
	$tpl=new templates();		
	$hash=$autofs->automounts_Browse();
	if(file_exists('ressources/usb.scan.inc')){include("ressources/usb.scan.inc");}
	$add=imgtootltip("32-plus.png","{add}","Loadjs('$page?form-add-js=yes')");
	$html="
	<div id='AutoFSMountedList'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>$add</th>
		<th>{proto}</th>
		<th>{source}</th>
		<th>{local_directory_name}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		

	while (list ($localmount, $array) = each ($hash) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$delete=imgtootltip("delete-32.png","{delete}","AutoFSDeleteDN('{$array["DN"]}')");
		if(preg_match("#\{device\}:(.+)#",$array["SRC"],$re)){
			$uuid=$re[1];
			$ligne=$_GLOBAL["usb_list"][$uuid];
			$TYPE=$ligne["TYPE"];
			$ID_MODEL=$ligne["ID_MODEL"];
			$LABEL=$ligne["LABEL"];
			$DEV=$ligne["DEV"];
			if($LABEL==null){$LABEL=$ID_MODEL;}
			if($LABEL==null){$LABEL=$uuid;}
			$SIZE=explode(";",$ligne["SIZE"]);	
			$array["SRC"]="{device}: $LABEL ({$SIZE[0]})";
		}	
		
		
		$html=$html . "
		<tr  class=$classtr>
			<td width=1%><img src='img/net-drive-32.png'></td>
			<td width=1% align='center' nowrap><strong style='font-size:14px'><code style='color:$color'>{$array["FS"]}</code></td>
			<td width=99% align='left'><strong style='font-size:14px'>{$array["SRC"]}</strong></td>
			<td width=1% align='left' nowrap><strong style='font-size:11px'>/automounts/$localmount</strong></td>
			<td width=1%>$delete</td>
		</tr>";		
	}
	
	$html=$html."</tbody></table></div>
	<script>

	var x_AutoFSDeleteDN= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('main_config_autofs');
	}		
	
	function AutoFSDeleteDN(key){
		var XHR = new XHRConnection();
		XHR.appendData('AutoFSDeleteDN',key);	
		document.getElementById('AutoFSMountedList').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_AutoFSDeleteDN);
		}	

	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function mounts_delete(){
	$ldap=new clladp();
	
	$auto=new autofs();
	$auto->automounts_Browse();
	
	$INFOS=$auto->hash_by_dn[$_GET["AutoFSDeleteDN"]];
	$FOLDER=$INFOS["FOLDER"];
	$FOLDER=addslashes($FOLDER);
	$q=new mysql();
	$sql="DELETE FROM automount_davfs WHERE local_dir='$FOLDER'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	
	if(!$ldap->ldap_delete($_GET["AutoFSDeleteDN"])){
		echo "function: ".__FUNCTION__."\n"."file: ".__FILE__."\nline: ".__LINE__."\n" .$ldap->ldap_last_error;return;
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?autofs-reload=yes");	
	
}

function mounts_events(){
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend valign='middle'>{search}:</td>
		<td>". Field_text("syslog-search",null,"font-size:13px;padding:3px;",null,null,null,false,"SyslogSearchPress(event)")."</td>
		<td align='right' width=1%>". imgtootltip("32-refresh.png","{refresh}","SyslogRefresh()")."</td>
	</tr>
	</table>
	
	<div style='widht:99%;height:600px;overflow:auto;margin:5px' id='syslog-table'></div>
	<script>
		function SyslogSearchPress(e){
			if(checkEnter(e)){SearchSyslog();}
		}
	
	
		function SearchSyslog(){
			var pat=escape(document.getElementById('syslog-search').value);
			LoadAjax('syslog-table','$page?syslog-table=yes&search='+pat);
		
		}
		
		function SyslogRefresh(){
			SearchSyslog();
		}
	
	SearchSyslog();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function mounts_events_query(){
	
	$pattern=base64_encode($_GET["search"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?syslog-query=$pattern&prefix=automount")));
	if(!is_array($array)){return null;}
	
	$html="<table class=TableView>";
	
	while (list ($key, $line) = each ($array) ){
		if($line==null){continue;}
		if($tr=="class=oddrow"){$tr=null;}else{$tr="class=oddrow";}
		
			$html=$html."
			<tr $tr>
			<td><code>$line</cod>
			</tr>
		
		";
		
	}
	
	
	$html=$html."</table>";

	echo $html;
}

