<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.samba.aliases.inc');

	
	if(isset($_GET["browse-folder-list"])){folders_list();exit;}
	if(isset($_GET["folder-main-params"])){folder_params();exit;}
	if(isset($_GET["folder-main-access"])){folder_access();exit;}
	if(isset($_POST["share_path"])){folder_save();exit;}
	if(isset($_POST["browsable"])){params_save();exit;}
	if(isset($_GET["folder-id"])){folder_edit();exit;}
	
	
	
	start();
function start(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$html="
	<center>
		<table style='width:100%' class=form>
			<tr>
				<td class=legend>{folders}:</td>
				<td>". Field_text("browse-sambaF-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseSambaFSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSambaFSearch()")."</td>
			</tr>
			</table>
	</center>
	<div id='browse-sambaF-list' style='width:100%;height:420px;overflow:auto;text-align:center'></div>
		
<script>
		function BrowseSambaFSearchCheck(e){
			if(checkEnter(e)){BrowseSambaFSearch();}
		}
		
		function BrowseSambaFSearch(){
			var se=escape(document.getElementById('browse-sambaF-search').value);
			LoadAjax('browse-sambaF-list','$page?browse-folder-list=yes&hostname={$_GET["hostname"]}&search='+se+'&field={$_GET["field"]}');
		}
		
			
	BrowseSambaFSearch();
</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}	

function folders_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("***","*",$search);
	$search=str_replace("**","*",$search);
	$search_sql=str_replace("*","%",$search);
	$search_sql=str_replace("%%","%",$search_sql);
	$search_regex=str_replace(".","\.",$search);	
	$search_regex=str_replace("*",".*?",$search);
	$sure_delete_smb_vrt=$tpl->javascript_parse_text("{sure_delete_smb_vrt}");
	
	
	$add=imgtootltip("plus-24.png","{add}","SambaVirtalServerEditShare(0,'')");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=2>{shared_folders}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";

		$q=new mysql();
		$sql="SELECT ID,share_name FROM samba_hosts_share WHERE hostname='{$_GET["hostname"]}' AND share_name LIKE '$search_sql' ORDER BY share_name";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	
		
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$select=imgtootltip("folder-granted-properties-32.png","{edit}","SambaVirtalServerEditShare('{$ligne["ID"]}','{$ligne["share_name"]}')");
				$delete=imgtootltip("delete-32.png","{delete}","SambaVirtalServerDeleteShare('{$ligne["ID"]}')");
				$color="black";
				$html=$html."
				<tr class=$classtr>
					<td width=1%>$select</td>
					<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["share_name"]}</a></td>
					<td width=1%>$delete</td>
				</tr>
				";
	}
	
	
	$html=$html."
	</table>
	<script>
		function SambaVirtalServerEditShare(ID,s){
			if(!s){s='New';}
			YahooWin4('485','$page?folder-id='+ID+'&hostname={$_GET["hostname"]}',s);
		}
	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function folder_edit(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	if(!is_numeric($_GET["folder-id"])){$_GET["folder-id"]=0;}
	$samba=new samba_virtuals_folders($_GET["folder-id"]);
	if($_GET["folder-id"]==0){
		$array["folder-main-params"]="{add}";
	}else{
		$array["folder-main-params"]="{parameters}";
		$array["folder-main-access"]="{share_access}";
		
	}
	
	while (list ($num, $ligne) = each ($array) ){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&folder-id={$_GET["folder-id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_folders_samba style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_folders_samba').tabs();
			});
		</script>";		
	
	
}



function folder_params(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$samba=new samba_aliases($_GET["hostname"]);
	if(!is_numeric($_GET["folder-id"])){$_GET["folder-id"]=0;}
	if($samba->RootDir<>null){$startroot=urlencode(base64_encode($samba->RootDir));}
	$button="{apply}";
	writelogs("Loading ID:{$_GET["folder-id"]} Folder",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	$samba_virtuals_folders=new samba_virtuals_folders($_GET["folder-id"]);
	if($_GET["folder-id"]==0){$button="{add}";}
	
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("smb_path",$samba_virtuals_folders->share_path,"width:240px;font-size:13px")."&nbsp;<input type='button' OnClick=\"javascript:Loadjs('browse-disk.php?start-root=$startroot&field=smb_path');\" value='{browse}...'></td>
	</tr>
	<tr>
		<td class=legend>{share_name}:</td>
		<td>". Field_text("smb_share_name",$samba_virtuals_folders->share_name,"width:240px;font-size:13px")."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><hr>". button($button,"SaveSMBFolder()")."</td></tr>
	</table>
	
	<script>
	var x_SaveSMBFolder=function (obj) {
			var folder_id={$_GET["folder-id"]};
			tempvalue=obj.responseText;
			if(tempvalue.length>2){alert(tempvalue);return;}
			if(document.getElementById('browse-samba-search')){BrowseSambaSearch();}
			if(folder_id==0){YahooWin3Hide();}
	}	

	function SaveSMBFolder(share){
		var XHR = new XHRConnection();
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('folder-id','{$_GET["folder-id"]}');
		XHR.appendData('share_path',document.getElementById('smb_path').value);
		XHR.appendData('share_name',document.getElementById('smb_share_name').value);
		XHR.sendAndLoad('$page', 'POST',x_SaveSMBFolder);
	}	
	
	
	</script>
		
	";
	
	echo $tpl->_ENGINE_parse_body($html); 
	}
	
function folder_save(){
	$samba=new samba_virtuals_folders($_GET["folder-id"]);
	$samba->hostname=$_POST["hostname"];
	$samba->share_name=$_POST["share_name"];
	$samba->share_path=$_POST["share_path"];
	if($samba->Save()){return;}

}

function folder_access(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$smb=new samba_virtuals_folders($_GET["folder-id"]);
	
	
	
	$html="<div id='folder_access_vrtis'>
			<table class=form style='width:100%'>	
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{browseable}:</td>
			<td valign='top'>" . Field_checkbox('browsable',1,$smb->share_params["browsable"])."</td>
			<td valign='top'>" . help_icon("{browseable_text}")."</td>
		</tr>
		
		<tr>	
			<td  align='right' nowrap valign='top' class=legend>{writeable}:</td>
			<td  valign='top'>" . Field_checkbox('writable',1,$smb->share_params["writable"])."</td>
			<td  valign='top'>" . help_icon("{writeable_text}")."</td>
		</tr>	
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{public}:</td>
			<td valign='top'>" . Field_checkbox('public',1,$smb->share_params["public"])."</td>
			<td valign='top'>" . help_icon("{public_text}")."</td>
		</tr>	
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{hide_unreadable}:</td>
			<td valign='top'>" . Field_checkbox('hide_unreadable',1,$smb->share_params["hide_unreadable"])."</td>
			<td valign='top'>" . help_icon("{hide_unreadable_text}")."</td>
		</tr>
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{hide_unwriteable_files}:</td>
			<td valign='top'>" . Field_checkbox('hide_unwriteable_files',1,$smb->share_params["hide_unwriteable_files"])."</td>
			<td valign='top'>" . help_icon("{hide_unwriteable_files_text}")."</td>
		</tr>
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{samba_create_mask}:</td>
			<td valign='top'>" . Field_text('create_mask',$smb->share_params["create_mask"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_create_mask_text}")."</td>
		</tr>
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{force_create_mode}:</td>
			<td valign='top'>" . Field_text('force_create_mode',$smb->share_params["force_create_mode"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_create_mask_text}")."</td>
		</tr>			
		<tr>	
			<td align='right' nowrap valign='top' class=legend>{samba_directory_mask}:</td>
			<td valign='top'>" . Field_text('directory_mask',$smb->share_params["directory_mask"],"font-size:13px;width:38px")."</td>
			<td valign='top'>" . help_icon("{samba_directory_mask_text}")."</td>
		</tr>
		<tr>
			<td colspan=3 align='right'><hr>
			". button("{apply}","SaveFolderAccess()")."
		</tr>
		</table>
		</div>
		<script>
		
		var x_SaveFolderAccess=function (obj) {
		 	text=obj.responseText;
		 	if(text.length>2){alert(text);}
			RefreshTab('main_config_folders_samba');
			}		
		
		function SaveFolderAccess(){
			var XHR = new XHRConnection();
			if(document.getElementById('browsable').checked){XHR.appendData('browsable',1);}else{XHR.appendData('browsable',0);}
			if(document.getElementById('public').checked){XHR.appendData('public',1);}else{XHR.appendData('public',0);}
			if(document.getElementById('writable').checked){XHR.appendData('writable',1);}else{XHR.appendData('writable',0);}
			if(document.getElementById('hide_unreadable').checked){XHR.appendData('hide_unreadable',1);}else{XHR.appendData('hide_unreadable',0);}
			if(document.getElementById('hide_unwriteable_files').checked){XHR.appendData('hide_unwriteable_files',1);}else{XHR.appendData('hide_unwriteable_files',0);}
			XHR.appendData('folder-id','$smb->ID');		
			XHR.appendData('create_mask',document.getElementById('create_mask').value);		
			XHR.appendData('force_create_mode',document.getElementById('force_create_mode').value);
			XHR.appendData('directory_mask',document.getElementById('directory_mask').value);
			XHR.sendAndLoad('$page', 'POST',x_SaveFolderAccess);
			}
		</script>
		
		
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function params_save(){
	
	$tpl=new templates();	
	$smb=new samba_virtuals_folders($_POST["folder-id"]);	
	
	while (list ($num, $ligne) = each ($_POST) ){
		$smb->share_params[$num]=$ligne;
		
	}
	
	$smb->Save();
	
}




