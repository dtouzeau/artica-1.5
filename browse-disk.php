<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.samba.aliases.inc');
	include_once("ressources/class.harddrive.inc");

	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["startpath"])){browseFolder();exit;}
	if(isset($_GET["browser-infos"])){brower_infos();exit;}
	if(isset($_POST["create-folder"])){folder_create();exit;}
	if(isset($_POST["delete-folder"])){folder_delete();exit;}
	
	
	js();

function js(){
	if($_GET["replace-start-root"]=="yes"){$_GET["replace-start-root"]=1;}
	if($_GET["with-samba"]=="yes"){$_GET["with-samba"]=1;}
	$page=CurrentPageName();
	echo "
	function BrowsDiskNewStart(){
		YahooWinBrowse(550,'$page?popup=yes&root={$_GET["start-root"]}&with-samba={$_GET["with-samba"]}&field={$_GET["field"]}&replace-start-root={$_GET["replace-start-root"]}','Browse');
	}
	BrowsDiskNewStart();
		";
	echo $html;
	
	
}

function folder_delete(){
	$sock=new sockets();
	$tpl=new templates();
	echo $tpl->javascript_parse_text(base64_decode($sock->getFrameWork("cmd.php?folder-remove={$_POST["root"]}")));
	
}


function popup(){
	$page=CurrentPageName();
	if(!isset($_GET["root"])){$_GET["root"]=urlencode(base64_encode("disks"));}else{
		$js="BrowserExpand('".urlencode(base64_encode(base64_decode($_GET["root"])))."');";
		
	}

	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
			<ul id='browser' class='filetree'></ul>
	</td>
	<td valign='top' style='padding-left:3px;border-left:3px solid #CCCCCC'><div id='browser-infos'></div></td>
	</tr>
	</table>
	
	
	
	<script>
	function LoadTree(){
		$(document).ready(function(){
			$('#browser').treeview({
			url: '$page?startpath={$_GET["root"]}&field={$_GET["field"]}&org-root={$_GET["root"]}&replace-start-root={$_GET["replace-start-root"]}&with-samba={$_GET["with-samba"]}'
			
			})
		});
	
	}
	
	function ReloadTree(){
		BrowsDiskNewStart();
	}
	
	function BrowserExpand(encpath){
		LoadAjax('browser-infos','$page?browser-infos='+encpath+'&field={$_GET["field"]}&org-root={$_GET["root"]}&replace-start-root={$_GET["replace-start-root"]}&with-samba={$_GET["with-samba"]}');
	
	}
	
	$js
	LoadTree();
	</script>
	";
	
	echo $html;
}


function brower_infos(){
	$tpl=new templates();
	$page=CurrentPageName();
	$root=base64_decode($_GET["browser-infos"]);
	$RootTile=basename($root);
	$give_folder_name=$tpl->javascript_parse_text("{give_folder_name}");
	if(!is_numeric($_GET["replace-start-root"])){$_GET["replace-start-root"]=0;}
	if(!is_numeric($_GET["with-samba"])){$_GET["with-samba"]=0;}
	$orginal_root=base64_decode($_GET["org-root"]);
	$strippedroot=str_replace($orginal_root, "",$root);
	$share_this=$tpl->javascript_parse_text("{share_this}: $RootTile ?");
	$delete_text=$tpl->javascript_parse_text("{delete} ?: $root ?");
	//$root_url=urlencode($root);
	if($_GET["field"]<>null){
		$select="
		<tr>
			<td width=1% valign='top'>
		" . imgtootltip('folder-granted-properties-48.png','{select_this_folder}',"SelectFolder()")."</td>
			<td><a href=\"javascript:blur();\" OnClick=\"javascript:SelectFolder()\"  style='font-size:14px;text-decoration:underline'>{select_this_folder}</td>
		</tr>
		";
		
	}
	
	if($_GET["with-samba"]==1){
		include_once(dirname(__FILE__)."/ressources/class.samba.inc");
		$smb=new samba();
		if($smb->main_shared_folders[$root]==null){
			$samba="
			<tr>
				<td width=1% valign='top'>
			" . imgtootltip('folder-granted-48.png','{share_this}',"SmbShare2()")."</td>
				<td><a href=\"javascript:blur();\" OnClick=\"javascript:SmbShare2()\"  style='font-size:14px;text-decoration:underline'>{share_this}</td>
			</tr>
			";		
		}else{
			$samba="
			<tr>
				<td width=1% valign='top'>
			" . imgtootltip('folder-granted-48-grey.png','{share_this}')."</td>
				<td><a href=\"javascript:blur();\" OnClick=\"blur()\"  style='font-size:14px;text-decoration:underline'>{share_this}</td>
			</tr>
			";				
		}
		
	}
	
	$delete="<tr>
		<td width=1% valign='top'>
		" . imgtootltip('folder-delete-48.png','{del_sub_folder}',"DelSubFolder2()")."</td>
		<td><a href=\"javascript:blur();\" OnClick=\"javascript:DelSubFolder2()\"  style='font-size:14px;text-decoration:underline'>{del_sub_folder}</a></td>
		</tr>";
	
	
	if($root==null){
		$delete=null;$samba=null;
	}
	
	$html="<div style='font-size:16px' id='root-infos-title'>{directory}:&nbsp;&laquo;&nbsp;$RootTile&nbsp;&raquo;</div>
	<div id='BrowserDiskDiv'>
	<table style='width:100%' class=form>$select$samba
		<tr>
			<td width=1% valign='top'>
		" . imgtootltip('folder-48-add.png','{add_sub_folder}',"AddSubFolder()")."</td>
			<td><a href=\"javascript:blur();\" OnClick=\"javascript:AddSubFolder()\"  style='font-size:14px;text-decoration:underline'>{add_sub_folder}</td>
		</tr>
	$delete
	</table>	
	</div>
	
	<script>
	
		var x_AddSubFolder=function (obj) {
		 	text=obj.responseText;
		 	if(text.length>2){alert(text);}else{
		 		document.getElementById('browser-infos').innerHTML='';
		 	}
			ReloadTree();
			}

			
		function SelectFolder(){
			if(!document.getElementById('{$_GET["field"]}')){
				alert('{$_GET["field"]} No such field');
				return;
			}
			var stripped={$_GET["replace-start-root"]};
			if(stripped==0){document.getElementById('{$_GET["field"]}').value='$root';}else{document.getElementById('{$_GET["field"]}').value='$strippedroot';}
			YahooWinBrowseHide();
		}
	
	
	
		function AddSubFolder(){
			var newfolder=prompt('$give_folder_name:\"$RootTile\"','New folder');
      		if(newfolder){
        		var XHR = new XHRConnection();
        		XHR.appendData('root','{$_GET["browser-infos"]}');
        		XHR.appendData('create-folder',newfolder);
        		AnimateDiv('browser');
        		XHR.sendAndLoad('$page', 'POST',x_AddSubFolder);
        		}   
		
		}
		
		function DelSubFolder2(){
			if(confirm('$delete_text')){
      			var XHR = new XHRConnection();
        		XHR.appendData('root','{$_GET["browser-infos"]}');
        		XHR.appendData('delete-folder','yes');
        		AnimateDiv('browser');
        		XHR.sendAndLoad('$page', 'POST',x_AddSubFolder);			
			}
		}
		
		
	var x_SmbShare2= function (obj) {
	 	text=obj.responseText;
	 	if(text.length>3){alert(text);}
	 	YahooWinBrowseHide();
	 	if(document.getElementById('SharedFoldersList')){LoadAjax('SharedFoldersList','samba.index.php?SharedFoldersList=yes');}
		}
	
		
	function SmbShare2(){
	 	if(confirm('$share_this')){
		 		AnimateDiv('BrowserDiskDiv');
		        var XHR = new XHRConnection();
		        XHR.appendData('AddTreeFolders','$root');
		        XHR.sendAndLoad('samba.index.php', 'GET',x_SmbShare2);
	        }          
	 	}		
		
	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function folder_create(){
	$root=base64_decode($_POST["root"]);
	$newFolder=$_POST["create-folder"];
	$newpath="$root/$newFolder";
	$newpath=str_replace("//", "/", $newpath);
	$newpath=strip_path_accents($newpath);
	$newpath=utf8_encode($newpath);
	
	$users=new usersMenus();
	if($users->IfIsAnuser()){
		$perms="&perms=".base64_encode($_SESSION["uid"]);
	}

	$tpl=new templates();
	$sock=new sockets();
	echo $tpl->javascript_parse_text(base64_decode($sock->getFrameWork("cmd.php?create-folder=".base64_encode($newpath).$perms)));
	
}


function browseFolder(){
	$root=base64_decode($_GET["startpath"]);
	writelogs("Checking: $root \"{$_GET["root"]}\"",__FUNCTION__,__FILE__,__LINE__);
	$tpl=new templates();
	$used=$tpl->javascript_parse_text("{used}");
	$f[]="[";
	
	if(($root=="disks") && ($_GET["root"]=="source")){
		$harddrive=new harddrive();
		$disks=$harddrive->getDiskList();
		
		while (list ($disk, $ARRAY_FINAL) = each ($disks) ){	
				$acc=array();
				if(Folders_interdis($ARRAY_FINAL["MOUNTED"])){continue;}
				$path=base64_encode($ARRAY_FINAL["MOUNTED"]);
				$size=$ARRAY_FINAL["SIZE"];
				$pourc=$ARRAY_FINAL["POURC"];
				$pathencurl=urlencode($path);			
				$acc[]="{";
				$acc[]="\t\"text\": \"$disk $size $pourc% $used\",";
				$acc[]="\t\"classes\": \"disk\",";
				$acc[]="\t\"id\": \"{$path}\",";
				$acc[]="\t\"click\": \"BrowserExpand('$pathencurl')\",";
				$acc[]="\t\"hasChildren\": true";
				$acc[]="}";
				
				$accolades[]=@implode("\n", $acc);
				
		}
		
		$f[]=@implode(",", $accolades);
		$f[]="]";
		echo @implode("\n", $f);
		return;
	}
	if($_GET["root"]=="source"){$_GET["root"]=base64_encode($root);}
	
	
	$sock=new sockets();
	$_GET["root"]=base64_decode($_GET["root"]);
	$_GET["root"]=str_replace("//", "/", $_GET["root"]);
	$mount_point=$_GET["root"];
	writelogs("Checking: \"{$_GET["root"]}\"",__FUNCTION__,__FILE__,__LINE__);
	
	
	$start_root=urlencode($_GET["root"]);
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?dirdirEncoded=$start_root")));
	if(is_array($datas)){
			ksort($datas);
			while (list ($num, $val) = each ($datas) ){
				if(Folders_interdis($num)){continue;}
				$num=basename($num);
				$path="$mount_point/$num";
				$path=str_replace("//", "/", $path);
				$pathenc=base64_encode($path);
				$pathencurl=urlencode($pathenc);
				$acc=array();
				$acc[]="{";
				$acc[]="\t\"text\": \"$num\",";
				$acc[]="\t\"classes\": \"folder\",";
				$acc[]="\t\"id\": \"{$pathenc}\",";
				$acc[]="\t\"hasChildren\": true,";
				$acc[]="\t\"click\": \"BrowserExpand('$pathencurl')\"";
				$acc[]="}";
				$accolades[]=@implode("\n", $acc);
			}
			
		$f[]=@implode(",", $accolades);
		$f[]="]";
		echo @implode("\n", $f);
		return;			
	}else{
		writelogs("Checking: dirdir=$start_root not an array",__FUNCTION__,__FILE__,__LINE__);
	}
	
	echo "[]";
	
	
	
}

function Folders_interdis($folder){
	if(!isset($_SESSION[__FUNCTION__])){
		$disk=new harddrive();
		$array=$disk->Folders_interdis();
		$_SESSION[__FUNCTION__]=$array;
	}else{
		$array=$_SESSION[__FUNCTION__];
	}

	
	if(!$array[$folder]){return false;}else{return true;}
	
	
}



