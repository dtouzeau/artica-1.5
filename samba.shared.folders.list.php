<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.acls.inc');
	
	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["sharedlist"])){shared_folders_list();exit;}
	if(isset($_GET["acldisks"])){acldisks();exit;}
	if(isset($_GET["aclline"])){aclsave();exit;}
	if(isset($_GET["quotaline"])){quotasave();exit;}
	if(isset($_GET["acls-folders-list"])){aclfolders();exit;}
	if(isset($_GET["AclsFoldersRebuild"])){aclfolders_rebuild();exit;}
	if(isset($_GET["DeleteAclFolder"])){aclfolders_delete();exit;}
	
js();
//fstablist

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{shared_folders}","samba.index.php");
	$page=CurrentPageName();
	$html="
		function shared_folders_start(){
			YahooWin5('600','$page?popup=yes','$title');
		}
	
	
	shared_folders_start();";
	
echo $html;	
	
}

function popup(){
	
	$array["sharedlist"]="{shared_folders}";
	$array["acldisks"]="{acl_disks}";
	$tpl=new templates();
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($array) ){
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		$ligne_text= html_entity_decode($ligne,ENT_QUOTES,"UTF-8");
		if(strlen($ligne_text)>17){
			$ligne_text=substr($ligne_text,0,14);
			$ligne_text=htmlspecialchars($ligne_text)."...";
			$ligne_text=texttooltip($ligne_text,$ligne,null,null,1);
			}
		//$html=$html . "<li><a href=\"javascript:ChangeSetupTab('$num')\" $class>$ligne</a></li>\n";
		
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne_text</span></a></li>\n";
			
		}
	$tpl=new templates();
	
	echo "
	<div id=main_samba_shared_folders style='width:100%;height:550px;overflow:auto;background-color:white;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_samba_shared_folders').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>";			
}

function shared_folders_list(){
	
	
	
	
	$samba=new samba();
	$folders=$samba->main_folders;
	if(!is_array($folders)){return null;}
	
	
	$html="
	<input type='hidden' id='del_folder_name' value='{del_folder_name}'>
	<table style='width:100%'>
	<tr>
	<th>&nbsp;</th>
	<th>{name}</th>
	<th>{path}</th>
	<th>&nbsp;</th>
	</tr>";
	
	
	while (list ($FOLDER, $ligne) = each ($folders) ){
		if($FOLDER=="netlogon"){continue;}
		if($FOLDER=="homes"){continue;}
		if($FOLDER=="printers"){continue;}
		if($FOLDER=="print$"){continue;}
		$properties="FolderProp('$FOLDER')";
		$delete=imgtootltip('ed_delete.gif','{delete}',"FolderDelete('$FOLDER')");
		if($samba->main_array[$FOLDER]["path"]=="/home/netlogon"){continue;}
		if($samba->main_array[$FOLDER]["path"]=="/home/export/profile"){continue;}

					
		
		
		
	$html=$html . "
	<tr " . CellRollOver($properties) . ">
	<td width=1%><img src='img/shared20x20.png'></td>
	<td><strong style='font-size:12px' width=1% nowrap>$FOLDER</td>
	<td><strong style='font-size:12px' width=99%>{$samba->main_array[$FOLDER]["path"]}</td>
	<td width=1%>$delete</td>
	</tr>
	";
	}
	
	$html=$html ."</table>";
	
	$html="<div style='width:99%;height:250px;overflow:auto'>$html</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function acldisks(){
	$sock=new sockets();
	$SAMBA_HAVE_POSIX_ACLS=base64_decode($sock->getFrameWork("samba.php?SAMBA-HAVE-POSIX-ACLS=yes"));
	
	if($SAMBA_HAVE_POSIX_ACLS<>"TRUE"){
		$acl_samba_not="<strong style='color:red;font-size:14px;padding:4px;margin:10Px'>{acl_samba_not}</strong>";
	}
	$fstab=unserialize(base64_decode($sock->getFrameWork("cmd.php?fstablist=yes")));
	$page=CurrentPageName();
	$html="
	<div class=explain>{acl_feature_about}</div>$acl_samba_not
	<div id='acltable'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2>{disk}</th>
		<th>{mounted}</th>
		<th>{acl_enabled}</th>
		<th>{quota_disk}</th>
	</tr>
</thead>
<tbody class='tbody'>	
	";
	
	while (list ($num, $ligne) = each ($fstab) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if(substr($ligne,0,1)=="#"){continue;}
		if(preg_match("#(.+?)\s+(.+?)\s+(.*?)\s+(.*?)\s+#",$ligne,$re)){
			$enableacl=0;
			$quota=0;
			if($re[1]=="proc"){continue;}
			if($re[2]=="none"){continue;}
			if(preg_match("#cdrom#",$re[2])){continue;}
			if(preg_match("#floppy#",$re[2])){continue;}
			if(preg_match("#\/boot$#",$re[2])){continue;}
			if($re[3]=="tmpfs"){continue;}
			
			
			if(preg_match("#acl#",$re[4])){$acl=1;}else{$acl=0;}
			if(preg_match("#usrjquota#",$re[4])){$quota=1;}else{$quota=0;}
			
			
			
			$dev=base64_encode(trim($re[1]));
			$enableacl=Field_checkbox("acl_$num",1,$acl,"FdiskEnableAcl('acl_$num','$dev');");
			$enablequeota=Field_checkbox("quota_$num",1,$quota,"FdiskEnableQuota('quota_$num','$dev');");
			$strlen=strlen($re[1]);
			if($strlen>30){$re[1]=texttooltip(substr($re[1],0,27)."...({$re[3]})",$re[1],null,null,1,"font-size:13px",0);}else{
			$re[1]=$re[1]." ({$re[3]})";}
			
			
			$html=$html."
			<tr class=$classtr>
				<td width=1%><img src='img/mailbox_hd.gif'></td>
				<td><code style='font-size:13px'>$re[1]</code></td>
				<td><code style='font-size:13px' nowrap>$re[2]</code></td>
				<td width=1% valign='top'>$enableacl</td>
				<td width=1% valign='top'>$enablequeota</td>
			</tr>
			";
		}
	}
	
	$html=$html."</table>
	</div>

	<div id='acls-folders-list' style='width:100%;height:230px;overflow:auto'></div>
	
	
	<script>
	
	var x_FdiskEnableAcl=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			if(document.getElementById('main_samba_shared_folders')){RefreshTab('main_samba_shared_folders');}
			if(document.getElementById('main_config_samba')){RefreshTab('main_config_samba');}
			if(document.getElementById('main_config_internal_disks')){RefreshTab('main_config_internal_disks');}			
			
	    }
	
		function FdiskEnableAcl(id,dev){
			var XHR = new XHRConnection();
			XHR.appendData('aclline',dev);
			if(document.getElementById(id).checked){XHR.appendData('acl','1');}else{XHR.appendData('acl','0');}
			document.getElementById('acltable').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_FdiskEnableAcl);
		}
		
		function FdiskEnableQuota(id,dev){
			var XHR = new XHRConnection();
			XHR.appendData('quotaline',dev);
			if(document.getElementById(id).checked){XHR.appendData('quota','1');}else{XHR.appendData('quota','0');}
			document.getElementById('acltable').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
			XHR.sendAndLoad('$page', 'GET',x_FdiskEnableAcl);
		}
		
		function RefreshAclsFolders(){
			LoadAjax('acls-folders-list','$page?acls-folders-list=yes');
		}
		
		RefreshAclsFolders();
	
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function aclsave(){
	$dev=$_GET["aclline"];
	$acl=$_GET["acl"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?fstab-acl=yes&acl=$acl&dev=$dev");
}
function quotasave(){
	$dev=$_GET["quotaline"];
	$acl=$_GET["quota"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?fstab-quota=yes&quota=$acl&dev=$dev");	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{need_reboot}");
}

function aclfolders(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sql="SELECT `directory` FROM acl_directories";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	$acls_folders_rebuild_text=$tpl->javascript_parse_text("{acls_folders_rebuild_text}");
	$count=mysql_num_rows($results);
	$sock=new sockets();
$html="

<table style='width:100%;'>
<tr>
<td align='right'>". imgtootltip("rebuild-32.png","{rebuild_acls}","AclsFoldersRebuild()")."</td>
</tr>
</table>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=100% colspan=2>{folders}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>"	;
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$path=base64_encode($ligne["directory"]);
		$info="&nbsp;";
		$color="black";
		$js="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('samba.acls.php?path=$path');\" style='font-size:14px;text-decoration:underline;color:black'>";
		$delete=imgtootltip("delete-24.png","{delete_permissions}","DeleteAclFolder('". base64_encode("{$ligne["directory"]}")."')");
		$aclsTests=unserialize(base64_decode($sock->getFrameWork("cmd.php?path-acls=$path&justdirectoryTests=yes")));
		$info=imgtootltip("32-parameters.png","<b>{$ligne["directory"]}</b><hr>{parameters}","Loadjs('samba.acls.php?path=$path');");
		
		
		if($aclsTests[0]=="NO_SUCH_DIR"){
			$info=imgtootltip("warning-panneau-32.png","<b>{$ligne["directory"]}</b><hr>{acls_no_such_dir_text}");
			$color="#CCCCCC";
			$js=null;
		}		
		
		
		$html=$html."
		<tr class=$classtr>
		<td width=1%>$info</td>
		<td width=99%>$js<code style='font-size:14px;color:$color'>{$ligne["directory"]}</a></code></td>
		<td width=1%>$delete</td>
		</tr>
		";
		
	}

	$html=$html."</table>
	
	<script>
		function AclsFoldersRebuild(){
			if(confirm('$acls_folders_rebuild_text')){
				var XHR = new XHRConnection();
				XHR.appendData('AclsFoldersRebuild','yes');
				XHR.sendAndLoad('$page', 'GET');
			
			}
		
		}
		
	   var x_DeleteAclFolder=function (obj) {
			results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshAclsFolders();
	    }			
		
		function DeleteAclFolder(path){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteAclFolder',path);
			XHR.sendAndLoad('$page', 'GET',x_DeleteAclFolder);		
		}
	
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function aclfolders_delete(){
	$path=base64_decode($_GET["DeleteAclFolder"]);
	$acls=new aclsdirs($path);
	$acls->DeleteAllacls();
}

function aclfolders_rebuild(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?acls-rebuild=yes");
	
}


?>