<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.sockets.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.nfs.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.user.inc');	
	include_once('ressources/class.crypt.php');	
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.auditd.inc');		
	if(!class_exists("unix")){include_once(dirname(__FILE__).'framework/class.unix.inc');}
		
		if(!IsRights()){
			$tpl=new templates();
			$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
			echo "alert('$error')";
			die();
		}
	
		if(isset($_GET["popup"])){popup();exit;}
		if(isset($_GET["browse-folder"])){browse_folder();exit;}
		if(isset($_GET["folder-infos"])){folder_infos();exit;}
		if(isset($_GET["top-bar"])){top_bar();exit;}
		if(isset($_GET["file-info"])){file_info();exit;}
		if(isset($_GET["download-file"])){download_file();exit;}
		if(isset($_GET["create-folder"])){create_folder();exit;}
		if(isset($_GET["delete-folder"])){delete_folder();exit;}
		if(isset($_GET["share-folder"])){share_folder();exit;}
		if(isset($_GET["unshare-rsync"])){rsync_unshare();exit;}
		if(isset($_GET["upload-file"])){upload_file_popup();exit;}
		if(isset($_GET["form-upload"])){upload_file_iframe();exit;}
		if( isset($_POST['target_path']) ){upload_form_perform();exit();}
		js();
		
		


function IsPriv(){
		if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
		$users=new usersMenus();
		if($users->AsArticaAdministrator){return "/";}
		if($users->AsSambaAdministrator){return "/";}
		if($users->AsSystemAdministrator){return "/";}
		
		$ct=new user($_SESSION["uid"]);
		
		if($users->AsOrgStorageAdministrator){
			
			$lmv=new lvm_org($ct->ou);
			
			$path=$lvm->storage_enabled;
			if($path==null){$path=$ct->homeDirectory;}
			writelogs("AsOrgStorageAdministrator=TRUE, storage_enabled:$path",__FUNCTION__,__FILE__,__LINE__);
			return $path;
		}
		writelogs("user:$ct->homeDirectory",__FUNCTION__,__FILE__,__LINE__);
		return $ct->homeDirectory;
		
}
function IsRights(){
		if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
		$users=new usersMenus();
		if($users->AsArticaAdministrator){return true;}
		if($users->AsSambaAdministrator){return true;}
		if($users->AsSystemAdministrator){return true;}
		return true;
	
		
}

function isAnUser(){
		if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
		if($users->AsArticaAdministrator){return false;}
		if($users->AsSambaAdministrator){return false;}
		if($users->AsSystemAdministrator){return false;}
		return true;	
}
		
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{explorer}");
	$give_folder_name=$tpl->javascript_parse_text("{give_folder_name}","samba.index.php");
	$are_you_sure_to_delete=$tpl->javascript_parse_text("{are_you_sure_to_delete} ?","fileshares.index.php");
	$unshare_this=$tpl->javascript_parse_text("{unshare_this} ?","fileshares.index.php");
	if(trim($_GET["mount-point"])==null){$_GET["mount-point"]=IsPriv();}
	$upload_a_file=$tpl->_ENGINE_parse_body("{upload_a_file}");
	
	$page=CurrentPageName();
	$html="
		var mem_id='';
		var mem_path='';
		var old_path='';
		var mem_parent_id;
		var mem_parent;
		function start(){
			YahooWinBrowse(900,'$page?popup=yes&mount-point={$_GET["mount-point"]}&select-file={$_GET["select-file"]}&target-form={$_GET["target-form"]}','$title');
			Loadjs('js/samba.js');
		}
		
		var X_TreeArticaExpand= function (obj) {
			var results=obj.responseText;
				$('#'+mem_id).removeClass('collapsed');
				if($('#'+mem_id).hasClass('directorys')){\$('#'+mem_id).addClass('expandeds');}
				if($('#'+mem_id).hasClass('directory')){\$('#'+mem_id).addClass('expanded');}
				$('#'+mem_id).append(results);
				BrowserInfos(mem_path);
				
			}

		var X_BrowserInfos= function (obj) {
				var results=obj.responseText;
				document.getElementById('browser-infos').innerHTML=results;
				top_bar(mem_path);
			}	

		var X_top_bar= function (obj) {
				var results=obj.responseText;
				document.getElementById('top-bar').innerHTML=results;
			}			
		
		function TreeArticaExpand(id,path){
			mem_id=id;
			mem_path=path;
			var expanded=false;
			if($('#'+mem_id).hasClass('expanded')){expanded=true;}
			if(!expanded){if($('#'+mem_id).hasClass('expandeds')){expanded=true;}}
			
			if(!expanded){
				var XHR = new XHRConnection();
				XHR.appendData('browse-folder',path);
				XHR.appendData('select-file','{$_GET["select-file"]}');
				XHR.appendData('target-form','{$_GET["target-form"]}');
				XHR.sendAndLoad('$page', 'GET',X_TreeArticaExpand);
			}else{
				$('#'+mem_id).children('ul').empty();
				if($('#'+mem_id).hasClass('expanded')){\$('#'+mem_id).removeClass('expanded');}
				if($('#'+mem_id).hasClass('expandeds')){\$('#'+mem_id).removeClass('expandeds');}				
				$('#'+mem_id).addClass('collapsed');
				
			}
		}
		
 	function NFSShare2(path){
 	  Loadjs('nfs.index.php?share-dir='+path);
 
	}	
	
 	function RsyncShare(path){
 	  Loadjs('rsync.shares.php?share-dir='+path);
 
	}		

	function FileInfo(path){
		YahooWin2(665,'$page?file-info='+path,'$title');
	}
	
	function FileUpload(path){
		YahooWin2(580,'$page?upload-file='+path+'&select-file={$_GET["select-file"]}&target-form={$_GET["target-form"]}','$upload_a_file');
	}
		
		
		function BrowserInfos(path){
			var XHR = new XHRConnection();
			XHR.appendData('folder-infos',path);
			XHR.appendData('id',mem_id);
			XHR.appendData('select-file','{$_GET["select-file"]}');
			XHR.appendData('target-form','{$_GET["target-form"]}');			
			document.getElementById('browser-infos').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',X_BrowserInfos);
		}
		
		function top_bar(path){
			var XHR = new XHRConnection();
			XHR.appendData('top-bar',path);
			XHR.appendData('select-file','{$_GET["select-file"]}');
			XHR.appendData('target-form','{$_GET["target-form"]}');					
			XHR.sendAndLoad('$page', 'GET',X_top_bar);
		}
		
		

		var x_CFSShare= function (obj) {
		 	text=obj.responseText;
		 	if(text.length>0){alert(text);}
		 	RefreshFolder(mem_path,mem_id);
			}		
			
		var x_CreateSubFolder=function (obj) {
		 	text=obj.responseText;
		 	if(text.length>0){
		 		alert(text);
				BrowserInfos(old_path);
				return;
				}
			setTimeout(RefreshFolder(old_path,mem_id),1000);
			}
		

		
		function CFSShare(path){
			mem_path=path;
			mem_id=document.getElementById('mem_id').value;
			document.getElementById('picture-title').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	        var XHR = new XHRConnection();
	        XHR.appendData('share-folder',path);
	        XHR.sendAndLoad('$page', 'GET',x_CFSShare);
			}
			
		function UnshareRsync(path){
			mem_path=path;
			mem_id=document.getElementById('mem_id').value;
			document.getElementById('picture-title').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
	        var XHR = new XHRConnection();
	        XHR.appendData('unshare-rsync',path);
	        XHR.sendAndLoad('$page', 'GET',x_CFSShare);
			}			
		
		function CreateSubFolder(path){
			 old_path=path;	
			 mem_id=document.getElementById('mem_id').value;
			 
		     var newfolder=prompt('$give_folder_name:\"'+path+'\"','New folder');
      		if(newfolder){
 				document.getElementById('browser-infos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';  
        		var XHR = new XHRConnection();
        		mem_path=path + '/'+newfolder;
        		XHR.appendData('create-folder',mem_path);
        		XHR.sendAndLoad('$page', 'GET',x_CreateSubFolder);
        		}   
		
		}
		
		function PutFileInform(filepath){
			document.getElementById('{$_GET["target-form"]}').value=filepath;
			YahooWinBrowseHide();
		}

		
		var x_DeleteSubFolder=function (obj) {
		 	text=obj.responseText;
		 	if(text.length>0){
		 		alert(text);
				BrowserInfos(mem_path);
				return;
			}
			RefreshFolder(mem_parent,mem_parent_id);
			
		}

	function DeleteSubFolder(path,parent,parent_id){
			if(!parent){alert('no parent');return;}
			if(!parent_id){alert('no parent_id');return;}
			mem_path=path;
        	mem_parent_id=parent_id;
        	mem_parent=parent;
			
			if(confirm('$are_you_sure_to_delete\\n'+path)){
				 	var XHR = new XHRConnection();
         			document.getElementById('browser-infos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
        			XHR.appendData('delete-folder',path);
        			XHR.sendAndLoad('$page', 'GET',x_DeleteSubFolder);	
			}
		}
		
		
		
		
		function RefreshFolder(path,id){
		var expanded=false;
			if(!id){
				if(!document.getElementById('mem_id')){alert('no mem_id');return;}
				id=document.getElementById('mem_id').value;
				}
			if($('#'+id).hasClass('expanded')){expanded=true;}
			if(!expanded){if($('#'+id).hasClass('expandeds')){expanded=true;}}	
				
			if(!expanded){
				mem_id=id;
				mem_path=path;
				TreeArticaExpand(id,path);
			}else{
				mem_id=id;
				mem_path=path;
				$('#'+mem_id).children('ul').empty();
				if($('#'+mem_id).hasClass('expanded')){\$('#'+mem_id).removeClass('expanded');}
				if($('#'+mem_id).hasClass('expandeds')){\$('#'+mem_id).removeClass('expandeds');}				
				$('#'+mem_id).addClass('collapsed');
				var XHR = new XHRConnection();
				XHR.appendData('browse-folder',path);
				XHR.appendData('select-file','{$_GET["select-file"]}');
				XHR.appendData('target-form','{$_GET["target-form"]}');						
				XHR.sendAndLoad('$page', 'GET',X_TreeArticaExpand);
			}		
		}
		
function CFSUnShare(head,path){
	  var base=path;
      mem_id=document.getElementById('mem_id').value;
	  mem_path=path;      
      
 	if(confirm('$unshare_this')){
        var XHR = new XHRConnection();
        XHR.appendData('FolderDelete',head);
        document.getElementById('picture-title').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
        XHR.sendAndLoad('samba.index.php', 'GET',x_CFSShare);
        }          
 }		
		
		
	start();";
		
	echo $html;
}

//$('#mydiv').hasClass('foo')
function popup(){
$users=new usersMenus();
$sock=new sockets();
$tpl=new templates();
	if($users->SAMBA_INSTALLED){
		if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}
		$samba_here=true;
	}	
	
	$datas=unserialize($sock->getFrameWork("cmd.php?dirdir={$_GET["mount-point"]}"));
	
	if(isAnUser()){
		$ct=new user($_SESSION["uid"]);
		$sock=new sockets();
		$creds[0]=$ct->uid;
		$creds[1]=$ct->password;
		$array2=unserialize(base64_decode($sock->getFrameWork("cmd.php?smblient=yes&computer=127.0.0.1&creds=".base64_encode(serialize($creds)))));
		$array2[$ct->uid]=$ct->homeDirectory;
		unset($datas);
	}
	
	
	
	$global_path=$_GET["mount-point"];
	
	$ul="
	
	<ul id='root' class='jqueryFileTree'>
		<li class=root>Root: {$_GET["mount-point"]}
			<ul id='mytree' class='jqueryFileTree'>\n";
	
			
			
	
	if($_GET["mount-point"]=='/'){$_GET["mount-point"]=null;}
	$style=" OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
		if(is_array($datas)){
			ksort($datas);
			while (list ($num, $val) = each ($datas) ){
				$num=basename($num);
				$path="{$_GET["mount-point"]}/$num";
				$path_link="$global_path/$num";
				$path_link=str_replace("//","/",$path_link);
				$id=md5($path_link);
				$CLASS="directory";
				if($smb->main_shared_folders[$path_link]<>null){$CLASS="directorys";}		
				$js=texttooltip($num,"$num" ,"TreeArticaExpand('$id','$path_link');");
				$ul=$ul."\t<li class=$CLASS collapsed id='$id' $style>$js</li>\n";
			}
		}
	
	if(is_array($array2)){
		while (list ($foldername, $path) = each ($array2) ){
			$path_link=$path;
			$id=md5($path_link);
			
			$CLASS="directory";
			if($smb->main_shared_folders[$path_link]<>null){$CLASS="directorys";}		
			$js=texttooltip($foldername,"$foldername" ,"TreeArticaExpand('$id','$path_link');");
			$ul=$ul."\t<li class=$CLASS collapsed id='$id' $style>$js</li>\n";			
		}
	}
	
	
if($samba_here){
	if($users->AsSambaAdministrator){
		$text=texttooltip("{shared_folders}","{shared_folders}","Loadjs('samba.shared.folders.list.php')",null,0,"font-size:14px");
		$shared_links=$tpl->_ENGINE_parse_body("<span style='font-size:14px'>$text</span>","samba.index.php");	
	}
}
	
	$ul=$ul."</ul>
	</li>
	</ul>";
	
	$html="
	<div id='top-bar' style='text-align:right'></div>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=350px>
			<div id='tree' style='width:100%;height:550px;overflow:auto'>
				$ul
			</div>
		</td>
		<td valign='top' width=550px>
			<div id='browser-infos' style='border-left:1px solid #CCCCCC;height:550px;overflow:auto'>&nbsp;</div>
		</td>
	</tr>
	<tr>
		<td colspan=2>$shared_links</td>
	</tr>
	</table>
	
	<script>
		BrowserInfos('$global_path');
	</script>
	";
	
	
	
	echo $html;
}





function browse_folder(){
	$users=new usersMenus();
	if($users->SAMBA_INSTALLED){
		if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}
	}
	
	$path=$_GET["browse-folder"];
	$global_path=$path;
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?B64-dirdir=".base64_encode($path))));
	$id=md5($path);
	if(!is_array($datas)){return null;}
	$style=" OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	$html="<ul id='$id' class='jqueryFileTree'>\n";
	ksort($datas);
while (list ($num, $val) = each ($datas) ){
		$num=basename($num);
		$path_link=utf8_encode($global_path)."/".utf8_encode($num);
		writelogs("$path_link",__FUNCTION__,__FILE__);
		$path_link=str_replace("//","/",$path_link);
		$id=md5($path_link);
		$CLASS="directory";
		if($smb->main_shared_folders[$path_link]<>null){$CLASS="directorys";}		
		$js=texttooltip(utf8_encode($num),utf8_encode($num) ,"TreeArticaExpand('$id','$path_link');");
		$html=$html."\t<li class=$CLASS collapsed id='$id'>$js</li>\n";
	}
	$html=$html."</ul>";	
	
	echo $html;
	
}

function SambaInfos($path,$elements){
	$path=utf8_encode($path);
	if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}	
	if(substr($path,strlen($path)-1,1)=='/'){$path=substr($path,0,strlen($path)-1);}
	include_once("/usr/share/artica-postfix/framework/class.unix.inc");
	$unix=new unix();	
	$sock=new sockets();
	
	$ShareProperties_grey="<img src='img/folder-granted-properties-48-grey.png'>";
	$nfs_grey="<img src='img/folder-granted-add-48-nfs-grey.png'>";
	$share_rsync_properties_grey="<img src='img/folder-granted-properties-rsync-48-grey.png'>";
	$backup_grey="<img src='img/48-backup-grey.png'>";
	$acls_grey="<img src='img/folder-acls-48-grey.png'>";
	
	$ShareProperties=$ShareProperties_grey;
	$nfs=$nfs_grey;
	$share_rsync_properties=$share_rsync_properties_grey;
	$backup=$backup_grey;
	$acls=$acls_grey;
	
	$q=new mysql();
	
	$path_encoded=base64_encode($path);
	
	$sql="SELECT COUNT(ID) AS tcount FROM backup_schedules";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$schedules_number=$ligne["tcount"];
	
	if($users->GLUSTER_INSTALLED){
		$path_md5=md5($path);
		$cluster=imgtootltip('48-cluster.png','{path_in_cluster}',"Loadjs('gluster.path.php?path=$path_encoded')");
		if(isclustered($path_md5)){
			$cluster=imgtootltip('48-delete-cluster.png','{remove_path_in_cluster}',"Loadjs('gluster.path.php?del-path=$path_encoded')");
		}
	}
	

	if($users->NFS_SERVER_INSTALLED){
		$nfs=imgtootltip('folder-granted-add-48-nfs.png','{share_this_NFS}',"NFSShare2('$path')");
	}			
	
	if($users->SAMBA_INSTALLED){
		
		$shareit=imgtootltip('folder-granted-add-48.png','{share_this}',"CFSShare('$path')");
		if($smb->main_shared_folders[$path]<>null){
			$path_to_js=urlencode($smb->main_shared_folders[$path]);
			$txt="<div style='padding:2px;margin:5px;border:1px solid #CCCCCC'>{FOLDER_IS_SHARED}</div>";
			$removeShare=imgtootltip('folder-granted-remove-48.png','{delete_share}',"CFSUnShare('$path_to_js','$path')");
			$ShareProperties=imgtootltip('folder-granted-properties-48.png','{privileges_settings}',"FolderProp('$path_to_js')");
			$shareit=$removeShare;
			}
		}
		
	if($sock->GET_INFO("RsyncDaemonEnable")==1){
		$share_rsync=imgtootltip('folder-granted-add-rsync-48.png','{share_this_rsync}',"RsyncShare('$path')");
		include_once(dirname(__FILE__)."/ressources/class.rsync.inc");
		$rsync=new rsyncd_conf();
		if(is_array($rsync->main_array["$path"])){
			$share_rsync=imgtootltip('folder-granted-remove-rsync-48.png','{unshare_this_rsync}',"UnshareRsync('$path')");	
			$share_rsync_properties=imgtootltip('folder-granted-properties-rsync-48.png','{share_this_rsync}',"RsyncShare('$path')");
			}
		}

	if($schedules_number>0){
		$backup=imgtootltip('48-backup.png','{backup_this_directory}',"Loadjs('backup.tasks.php?FOLDER_BACKUP=$path_encoded')");
	}
	
	

		
	$id=md5($id);	
	$parent=str_replace("/". basename($path),"",$path);
	$parent_id=md5($parent);	
	$addfolder=imgtootltip('folder-48-add.png','{add_sub_folder}',"CreateSubFolder('$path')");
	$delfolder=imgtootltip('folder-delete-48.png','{del_sub_folder}',"DeleteSubFolder('$path','$parent','$parent_id')");
	$folder_refresh=imgtootltip('folder-refresh-48.png','{refresh}',"RefreshFolder('$path','$id')");		
	$acls=imgtootltip('folder-acls-48.png','{acls_directory}',"Loadjs('samba.acls.php?path=$path_encoded')");
	$upload=imgtootltip('folder-upload-48.png','{upload_a_file}',"FileUpload('$path_encoded')");
	$auditd_grey=imgtootltip('folder-watch-48-grey.png','{audit_this_directory}',"Loadjs('audit.directory.php?path=$path_encoded&id=$id')");
	$deduplication_grey=imgtootltip('folder-dedup-48-add-grey.png','{mount_has_deduplication_folder}');
	$deduplication_js="Loadjs('system.file.deduplication.php?tree-path=$path_encoded&id=$id')";
	
	if($users->APP_AUDITD_INSTALLED){
		$img="folder-watch-48-add.png";
		$au=new auditd();
		if($au->KeyAudited($path)<>null){
			$img="folder-watch-48-del.png";
		}
		
		$auditd=imgtootltip($img,
		'{audit_this_directory}',"Loadjs('audit.directory.php?path=$path_encoded&id=$id')");
	}else{
		$auditd=$auditd_grey;
	}
	
	if($unix->IsProtectedDirectory($path)){
			$delfolder=imgtootltip('folder-delete-48-grey.png','{del_sub_folder}',"");
			$addfolder=imgtootltip('folder-48-add-grey.png','{add_sub_folder}',"");
			$acls=$acls_grey;
			$auditd=$auditd_grey;
			}
			
	if($GLOBALS["USERMENUS"]->deduplication_installed){
		$mounted=unserialize(base64_decode($sock->getFrameWork("cmd.php?lessfs-mounts=yes")));
		$deduplication=imgtootltip('folder-dedup-48-add.png','{mount_has_deduplication_folder}',$deduplication_js);
		if($mounted[$path]){
			$delfolder=imgtootltip('folder-delete-48-grey.png','{del_sub_folder}',"");
			$deduplication=$deduplication_grey;
		}
	}	

	if($elements>0){$deduplication=$deduplication_grey;}
		
	if($users->IfIsAnuser()){
			$removeShare=null;
			$ShareProperties=$ShareProperties_grey;
			$shareit=null;
			$nfs=$nfs_grey;
			$acls=$acls_grey;
			$auditd=$auditd_grey;
			$deduplication=$deduplication_grey;
			$share_rsync=null;
			$share_rsync_properties=$share_rsync_properties_grey;
			$cluster=null;
			$ct=new user($_SESSION["uid"]);
			if($ct->homeDirectory==$path){
				$delfolder=imgtootltip('folder-delete-48-grey.png','{del_sub_folder}',"");
			}
		}
		
		
		

		if($backup==null){$backup=$acls;$acls="&nbsp;";}
		
		if($_GET["select-file"]<>null){
			$removeShare=null;
			$ShareProperties=$ShareProperties_grey;
			$shareit=null;
			$nfs=$nfs_grey;
			$acls=$acls_grey;
			$share_rsync=null;
			$share_rsync_properties=$share_rsync_properties_grey;
			$backup=$upload;
			$addfolder=null;
			$delfolder=null;
			$cluster=null;
			$auditd=null;
			$deduplication=null;
		}
		
$tr[]=$addfolder;
$tr[]=$delfolder;
$tr[]=$backup;
$tr[]=$folder_refresh;
$tr[]=$shareit;
$tr[]=$ShareProperties;
$tr[]=$nfs;
$tr[]=$acls;
$tr[]=$share_rsync;
$tr[]=$share_rsync_properties;
$tr[]=$auditd;
$tr[]=$cluster;
$tr[]=$deduplication;
		
$tables[]="<table style='width:130px'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
	
$html=$txt. implode("\n",$tables)."</table>
<input type='hidden' id='RefreshSambaTreeFolderHidden_path' value='$path'>
<input type='hidden' id='RefreshSambaTreeFolderHidden_id' value='$id'>
";	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html,"samba.index.php");
	
	
}


function item_infos($path,$elements_array){
	if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}
	$title=basename($path);	
	$elements=count($elements_array);
	$img="folder-96.png";
	
	
	if($GLOBALS["USERMENUS"]->deduplication_installed){
		$sock=new sockets();
		$mounted=unserialize(base64_decode($sock->getFrameWork("cmd.php?lessfs-mounts=yes")));
		if($mounted[$path]){$img="folder-dedup-96.png";}
	}
	
	
if(strlen($title)>20){$title=substr($title,0,17)."...";}

if($users->SAMBA_INSTALLED){if($smb->main_shared_folders[$path]<>null){$img="folder-granted-96.png";}}

$smb=SambaInfos($path,$elements);

$title=utf8_encode($title);
$html="
<input type='hidden' value='{$_GET["id"]}' id='mem_id'>
<div id='picture-title'>
		<img src='img/$img'>
	</div>
	<span style='font-size:16px'>$title</span><br>
	$elements {items}
	<hr>
	$smb
	</div>
	";

return $html;
}

function folder_infos(){
		$_GET["folder-infos"]=str_replace("../","",$_GET["folder-infos"]);
		$_GET["folder-infos"]=str_replace("//","/",$_GET["folder-infos"]);
		$dir=$_GET["folder-infos"];
		$users=new usersMenus();
		$tpl=new templates();
		$sock=new sockets();
		

		$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		$DisableExplorer=$sock->GET_INFO("DisableExplorer");
		if($DisableExplorer==null){$DisableExplorer=0;}		
		if($DisableExplorer==1){echo "<center style='margin:30px'><span style='font-size:18px;letter-spacing:-1px;color:red'>$ERROR_NO_PRIVS</span></center>";return;}				
		
		
		if($users->IfIsAnuser()){
			$stat=unserialize(base64_decode($sock->getFrameWork("cmd.php?filestat=".base64_encode($dir))));
			//print_r($stat["owner"]["owner"]);
			if(strtolower($stat["owner"]["owner"]["name"])<>$_SESSION["uid"]){
				echo "<H2>".$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}")."</H2>";
				return;
			}
		}else{
			writelogs("{$_SESSION["uid"]} is not a single user",__FUNCTION__,__FILE__,__LINE__);
		}
		
		
		$dir=strip_path_accents($dir);
		$title=basename($dir);
		
		$f=base64_decode($sock->getFrameWork("cmd.php?Dir-Files=". base64_encode($dir)));
		$datas=unserialize($f);
		$elements=count($datas);
		if(is_array($datas)){
			ksort($datas);
			$ft="<table style='width:100%'>
			<tr style='background-color:#D6D3CE'>
			<td style='border:1px solid #848284;font-size:11px'>&nbsp;</td>
			<td style='border:1px solid #848284;font-size:11px'>{file}</td>
			<td style='border:1px solid #848284;font-weight:normal;font-size:11px'>{size}</td>
			<td style='border:1px solid #848284;font-weight:normal;font-size:11px'>{owner}</td>
			<td style='border:1px solid #848284;font-weight:normal;font-size:11px'>{modified}</td>
			</tr>
				
			
			";
			while (list ($num, $val) = each ($datas) ){
				$full_path=utf8_encode($dir."/$num");
				$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?filestat=". base64_encode($full_path))));
				$owner=$array["owner"]["owner"]["name"];
				//print_r($array);
				
				if(date('Y',$array["time"]["mtime"])==date('Y')){
					$modified=date('M D d H:i:s',$array["time"]["mtime"]);
				}else{
					$modified=date('Y-m-d H:i',$array["time"]["mtime"]);
				}
				if(date('Y-m-d',$array["time"]["mtime"])==date('Y-m-d')){$modified="{today} ".date('H:i:s',$array["time"]["mtime"]);}
				$size=$array["size"]["size"];
				$ext=Get_extension($num);
				if($_GET["select-file"]<>null){
					if($_GET["select-file"]<>'*'){
						if($ext<>$_GET["select-file"]){continue;}
					}
				}
				$img="img/ext/def_small.gif";
				if($ext<>null){
					if(isset($GLOBALS[$ext])){$img="img/ext/{$ext}_small.gif";}else{
						if(is_file("img/ext/{$ext}_small.gif")){
							$img="img/ext/{$ext}_small.gif";
							$GLOBALS[$ext]=true;
							}
					}}
				
				$size_new=FormatBytes($size/1024);
				if(strlen($num)>27){$text_file=substr($num,0,24)."...";}else{$text_file=$num;}
				
				$file_tool_tip=fileTooltip($array);
				$file_js="FileInfo('". base64_encode("$dir/$num")."')";
				if(trim($_GET["target-form"])<>null){
					$file_js="PutFileInform('$dir/$num')";
					$file_tool_tip="<span style=font-size:14px>{select_this_file}</span><hr>$file_tool_tip";
				}				
				$text_file=texttooltip($text_file,$file_tool_tip,$file_js);

				
				if($size_new==0){$size_new=$size." bytes";}
				//print_r($array);
				$ft=$ft."<tr ". CellRollOver().">
					<td width=1% style='font-weight:normal'><img src='$img'></td>
					<td width=1% nowrap style='font-weight:normal;font-size:10px'>$text_file</td>
					<td nowrap align='right' style='font-weight:normal;font-size:10px'>$size_new</td>
					<td nowrap style='font-weight:normal;font-size:10px'>$owner</td>
					<td nowrap align='right' style='font-weight:normal;font-size:10px'>$modified</td>
					
				</tr>";
				
			
			}
			$ft=$ft."</table>";
		}
		
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' align='center'>
			<div style='width:130px;height:544px;background-image:url(img/bg_tree1.png);background-position:bottom center;background-repeat:no-repeat'>
			". item_infos($dir,$datas)."</div>
		</td>
		<td valign='top' width=350px>$ft</td>
	</tr>
	</table>
	
	";
	
	
	$html=$tpl->_ENGINE_parse_body($html,"fileshares.index.php");
	echo $html;	

}

function fileTooltip($array){
	
$permissions=$array["perms"]["human"];
$permissions_dec=$array["perms"]["octal1"];
$accessed=$array["time"]["accessed"];
$modified=$array["time"]["modified"];
$created=$array["time"]["created"];
$file=$array["file"]["basename"];
$permissions_g=$array["owner"]["group"]["name"].":". $array["owner"]["owner"]["name"];



$html="<strong style=font-size:13px>$file</strong><hr><table><tr><td class=legend>{permission}:</td><td><strong>$permissions $permissions_g ($permissions_dec)</td></tr>";
$html=$html."<tr><td class=legend>{accessed}:</td><td><strong>$accessed</td></tr>";
$html=$html."<tr><td class=legend>{modified}:</td><td><strong>$modified</td></tr>";
$html=$html."<tr><td class=legend>{created}:</td><td><strong>$created</td></tr>";
return $html."</table>";	
}

function top_bar(){
	$_GET["top-bar"]=str_replace("//","/",$_GET["top-bar"]);
	$_GET["top-bar"]=strip_path_accents($_GET["top-bar"]);
	$_GET["top-bar"]=utf8_encode($_GET["top-bar"]);
	$d=explode("/",$_GET["top-bar"]);
	while (list ($num, $val) = each ($d) ){
		$path="$path/$val";
		$path=str_replace("//","/",$path);
		$html=$html."<span style='font-size:12px;color:#005447'>
			<a href=\"#\" OnClick=\"javascript:BrowserInfos('$path')\">$val</a>&nbsp;&raquo;&nbsp;</span>";
	}
	echo $html;
}

function file_info(){
	if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}	
	$ldap=new clladp();
	$path=base64_decode($_GET["file-info"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?filestat=". base64_encode($path))));
	$type=base64_decode($sock->getFrameWork("cmd.php?filetype=". base64_encode($path)));	
	
	

	
$permissions=$array["perms"]["human"];
$permissions_dec=$array["perms"]["octal1"];
$accessed=$array["time"]["accessed"];
$modified=$array["time"]["modified"];
$created=$array["time"]["created"];
$file=$array["file"]["basename"];
$permissions_g=$array["owner"]["group"]["name"].":". $array["owner"]["owner"]["name"];
$ext=Get_extension($file);
$page=CurrentPageName();

$cr=new SimpleCrypt($ldap->ldap_password);
$path_encrypted=base64_encode($cr->encrypt($path));


$download=Paragraphe("download-64.png","{download}","{download} $file<br>".FormatBytes($array["size"]["size"]/1024),"$page?download-file=$path_encrypted");

	if($users->IfIsAnuser()){
			$ct=new user($_SESSION["uid"]);
			if($array["owner"]["owner"]["name"]<>$_SESSION["uid"]){$download=null;}
			}		


$img="img/ext/def.jpg";
if(is_file("img/ext/$ext.jpg")){$img="img/ext/$ext.jpg";}

$html="<H1>$file</H1>
<code>$path</code>
<div style='font-size:11px;margin-top:3px;padding-top:5px;border-top:1px solid #CCCCCC;text-align:right;'><i>$type</i></div>
<table style='width:100%'>
<tr>
<td width=1% valign='top'><img src='$img' style='margin:15px'></td>
<td valign='top'>
<hr>
<table>
	<tr>
		<td class=legend>{permission}:</td>
		<td><strong>$permissions $permissions_g ($permissions_dec)</td>
	</tr>
	<tr>
		<td class=legend>{accessed}:</td>
		<td><strong>$accessed</td>
	</tr>
<tr><td class=legend>{modified}:</td><td><strong>$modified</td></tr>
<tr><td class=legend>{created}:</td><td><strong>$created</td></tr>
<tr>
	<td class=legend>{size}:</td>
	<td><strong>{$array["size"]["size"]} bytes (". FormatBytes($array["size"]["size"]/1024).")</td>
</tr>
<tr>
	<td class=legend>blocks:</td>
	<td><strong>{$array["size"]["blocks"]}</td>
</tr>	
<tr>
	<td class=legend>block size:</td>
	<td><strong>{$array["size"]["block_size"]}</td>
</tr>
</table>
</td>
<td valign='top'>del
$download
</td>
</tr>
</table>";
$tpl=new templates();	
echo $tpl->_ENGINE_parse_body($html);
}
function download_file(){
	$ldap=new clladp();
	$cr=new SimpleCrypt($ldap->ldap_password);
	$path=$cr->decrypt(base64_decode($_GET["download-file"]));
	$file=basename($path);
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?file-content=".base64_encode($path)));
	$content_type=base64_decode($sock->getFrameWork("cmd.php?mime-type=".base64_encode($path)));
header('Content-Type: '.$content_type);
header("Content-Disposition: attachment; filename=\"$file\""); 
echo $datas;	
	
}
function create_folder(){
	$path=$_GET["create-folder"];
	$path=strip_path_accents($path);
	$path=utf8_encode($path);
	if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	if(!is_object($GLOBALS["SMBCLASS"])){$smb=new samba();$GLOBALS["SMBCLASS"]=$smb;}else{$smb=$GLOBALS["SMBCLASS"];}		
	
	if($users->IfIsAnuser()){
		$perms="&perms=".base64_encode($_SESSION["uid"]);
	}

	$sock=new sockets();
	echo base64_decode($sock->getFrameWork("cmd.php?create-folder=".base64_encode($path).$perms));
	
	
}

function share_folder(){
	
	$folder=$_GET["share-folder"];
	$folder_name=basename($folder);
	$samba=new samba();
	
	if(is_array($samba->main_array[$folder_name])){
		$d=date('YmdHis');
		$folder_name="{$folder_name}_$d";
	}
	
	$samba->main_array["$folder_name"]["path"]=$folder;
	$samba->main_array["$folder_name"]["create mask"]= "0660";
	$samba->main_array["$folder_name"]["directory mask"] = "0770";
	$samba->SaveToLdap();
	}


function delete_folder(){
if(!is_object($GLOBALS["USERMENUS"])){$users=new usersMenus();$GLOBALS["USERMENUS"]=$users;}else{$users=$GLOBALS["USERMENUS"];}
	$path=$_GET["delete-folder"];	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?filestat=". base64_encode($path))));
	$permissions=$array["owner"]["owner"]["name"];
	if($users->IfIsAnuser()){
		if($permissions<>$_SESSION["uid"]){
			$tpl=new templates();
			echo $tpl->javascript_parse_text("{ERROR_NO_PRIVS}\n{owner}:$permissions");
			exit;		
		}
	}
	
	if($path<>null){
		echo base64_decode($sock->getFrameWork("cmd.php?folder-remove=".base64_encode($path)));
		$samba=new samba();
		$samba->RemoveShareFromFolder($path);
		
		
	}else{
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{ERROR_NO_PRIVS}");	
	}
	
	
	
}

function rsync_unshare(){
	include_once(dirname(__FILE__)."/ressources/class.rsync.inc");
	$rsync=new rsyncd_conf();
	unset($rsync->main_array[$_GET["unshare-rsync"]]);
	$rsync->save();
}

function upload_file_popup(){
	$page=CurrentPageName();
	$html="<iframe style='width:100%;height:250px;border:1px' src='$page?form-upload={$_GET["upload-file"]}&select-file={$_GET["select-file"]}'></iframe>";
	echo $html;
}
function upload_file_iframe($error=null){
	
	if(isset($_POST["select-file"])){$_GET["select-file"]=$_POST["select-file"];}
	if(isset($_POST["target_path"])){$_GET["form-upload"]=$_POST["target_path"];}
	
	if($error<>null){
		$error="<div style='font-size:12px;font-family:Arial;font-weight:bold;padding:3px;margin:3px;border:1px solid red;text-align:center;color:red'>$error</div>";
	}
	
	if($_GET["select-file"]<>null){
	}
	
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<p>&nbsp;</p>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
	<img src='img/folder-upload-90.png'>
	</td>
	<td valign='top'>
		
		<H3 style='font-family:arial'>{upload_a_file} ({$_GET["select-file"]})</H3>
		$error
		<form method=\"POST\" enctype=\"multipart/form-data\" action=\"$page\">
		<input type='hidden' name='target_path' value='{$_GET["form-upload"]}'>
		<input type='hidden' name='select-file' value='{$_GET["select-file"]}'>
		
		<table style='width:100%'>
			<td class=legend style='font-size:13px;font-family:arial'>{file_to_upload}:</td>
			<td><input type=\"file\" name=\"fichier\" size=\"20\" style='font-size:13px;border:1px'></td>
		</tr>
		<tr>
		<td colspan=2 align='right'><hr>
			<input type='submit' name='upload' value='{upload}&nbsp;&raquo;' style='width:120px;padding:5px;margin:5px;font-family:arial'>
		</td>
		</tr>
		</table>
		</form>
	</td>
	</tr>
	</table>
	";
		$html=iframe($html,0,"500px");	
echo $tpl->_ENGINE_parse_body($html);		
	
	
}


function upload_form_perform(){
while (list ($num, $line) = each ($_POST) ){
	writelogs("_POST:: receive $num=$line",__FUNCTION__,__FILE__,__LINE__);
	}
	
	while (list ($num, $line) = each ($_FILES['fichier']) ){
	writelogs("_FILES:: receive $num=$line",__FUNCTION__,__FILE__,__LINE__);
	}	
	reset($_FILES['fichier']);
	$error=$_FILES['fichier']['error'];
	$tmp_file = $_FILES['fichier']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){
		$error="<table style='width:100%'>";
		while (list ($num, $val) = each ($_FILES['fichier']) ){
			$error=$error."<tr><td align='right' style='font-size:11px'>$num:</td><td style='font-size:11px'><b>$val</b></td></tr>";
		}
		upload_file_iframe("{error_unable_to_upload_file}$error</table>");
		exit();
	}
	 
	$type_file = $_FILES['fichier']['type'];
	$name_file = $_FILES['fichier']['name'];
	$ext=file_ext($name_file);	 
	if($_POST["select-file"]<>null){
		if($_POST["select-file"]<>'*'){
		if( $ext<>$_POST["select-file"]){	
			upload_file_iframe("{error_file_extension_not_match} :\"$ext\" is not \"{$_POST["select-file"]}\"");
			return;
		}
	}}
	
	if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 	if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){upload_file_iframe("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    include_once("ressources/class.sockets.inc");
    $sock=new sockets();
    $rettun=$sock->getFrameWork("cmd.php?move_uploaded_file='{$_POST["target_path"]}&src=". base64_encode("$content_dir/$name_file"));
    
 	upload_file_iframe($_POST["database_name"]. "<br>{success}<br>$rettun");		
}

function isclustered($md5){
	$sql="SELECT ID FROM gluster_paths WHERE zmd='$md5'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["ID"]>0){return true;}
	
}






?>