<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.active.directory.inc');
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
	$ldap=new ldapAD();
	$page=CurrentPageName();
	echo "
	function BrowsDiskNewStart(){
		YahooWinBrowse(650,'$page?popup=yes&root={$_GET["start-root"]}&function={$_GET["function"]}&field={$_GET["field"]}&replace-start-root={$_GET["replace-start-root"]}','Browse::$ldap->suffix');
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
	$ldap=new ldapAD();
	
	if(!isset($_GET["root"])){$_GET["root"]=urlencode(base64_encode($ldap->suffix));}else{
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
			url: '$page?startpath={$_GET["root"]}&field={$_GET["field"]}&org-root={$_GET["root"]}&replace-start-root={$_GET["replace-start-root"]}&function={$_GET["function"]}'
			
			})
		});
	
	}
	
	function ReloadTree(){
		BrowsDiskNewStart();
	}
	
	function BrowserExpand(encpath){
		LoadAjax('browser-infos','$page?browser-infos='+encpath+'&field={$_GET["field"]}&org-root={$_GET["root"]}&replace-start-root={$_GET["replace-start-root"]}&function={$_GET["function"]}');
	
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
	$RootTile=$root;
	$ldap=new ldapAD();
	if($RootTile==null){$RootTile=$ldap->suffix;}
	$RootTileS=explode(",", $RootTile);
	$titleOU=$RootTileS[0];
	
	if(trim($_GET["function"])<>null){
		$select="<table class=form><tbody>
		<tr>
		<td width=1%>". imgtootltip("32-plus.png","{select_this_ou}","{$_GET["function"]}('{$_GET["browser-infos"]}')")."</td>
		<td><a href=\"javascript:blur();\" OnClick=\"javascript:{$_GET["function"]}('{$_GET["browser-infos"]}')\"  style='font-size:14px;text-decoration:underline'>{select_this_ou}</td>
		</tr>
		</tbody>
		</table>
		";
		
	}
	
	$give_folder_name=$tpl->javascript_parse_text("{give_folder_name}");
	if(!is_numeric($_GET["replace-start-root"])){$_GET["replace-start-root"]=0;}

	$orginal_root=base64_decode($_GET["org-root"]);
	$strippedroot=str_replace($orginal_root, "",$root);
	$share_this=$tpl->javascript_parse_text("{share_this}: $RootTile ?");
	$delete_text=$tpl->javascript_parse_text("{delete} ?: $root ?");
	//$root_url=urlencode($root);
	if($_GET["field"]<>null){
		$select="
		<tr>
			<td width=1% valign='top'>
		" . imgtootltip('folder-granted-properties-48.png','{select_this_ou}',"SelectFolder()")."</td>
			<td><a href=\"javascript:blur();\" OnClick=\"javascript:SelectFolder()\"  style='font-size:14px;text-decoration:underline'>{select_this_ou}</td>
		</tr>
		";
		
	}
	
	if($ldap->suffix==$root){$select=null;}
	if($root==null){$select=null;}
	
$ldap=new ldapAD();
		if(count($res)>0){return $res;}
		$ld =$ldap->ldap_connection;
		$bind =$ldap->ldapbind;
		$suffix=$root;
		$res=array();
		$arr=array();
		
		$sr = @ldap_list($ld,$root,'(objectclass=group)',$arr);
		if ($sr) {
			
			$groups="
			<div id='groups-div' style='width:100%;height:250px;overflow:auto'>
			<table style='width:100%'>
			<tbody>
			";
			
			$hash=ldap_get_entries($ld,$sr);	
			writelogs("Checking: DN $root {$hash["count"]} group entries",__FUNCTION__,__FILE__,__LINE__);
			
			
			for($i=0;$i<$hash["count"];$i++){
				$groups=$groups."
					<tr>
						<td width=1%><img src='img/wingroup.png'></td>
						<td style='font-size:13px;font-weight:bold'>{$hash[$i]["cn"][0]}</td>
						<td style='font-size:13px;font-weight:bold'>{$hash[$i]["member"]["count"]}&nbsp;{users}</td>
					</tr>
					";
				
			}
			$groups=$groups."</tbody></table>";
			
			
		}else{
			writelogs("Checking: DN $root no entries of failed",__FUNCTION__,__FILE__,__LINE__);
		}
		
		$sr = @ldap_list($ld,$root,'(objectclass=user)',array('dn'));
		if ($sr) {$hash=ldap_get_entries($ld,$sr);}
		$countdeuser=$hash["count"];
		
	
	
	$rootBranchTitle=str_replace(',', ', ', $root);
	
	$html="<div style='font-size:16px' id='root-infos-title'>{ou}:&nbsp;&laquo;&nbsp;$titleOU&nbsp;&raquo;&nbsp;<span style='font-size:11px'>($countdeuser&nbsp;{members})</div>
	<div style='font-size:11px'>$rootBranchTitle</div>
	
	<div id='BrowserDiskDiv'>
	<table style='width:100%' class=form>$select
	
	
	</table>	
	$groups
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
	writelogs("Checking: DN $root \"{$_GET["root"]}\"",__FUNCTION__,__FILE__,__LINE__);
	$tpl=new templates();
	$used=$tpl->javascript_parse_text("{used}");
	$f[]="[";
	
	if(($root=="disks") && ($_GET["root"]=="source")){
		$ldap=new ldapAD();
		$pathencurl=urlencode($ldap->suffix);	
				
		$acc[]="{";
		$acc[]="\t\"text\": \"$ldap->suffix\",";
		$acc[]="\t\"classes\": \"disk\",";
		$acc[]="\t\"id\": \"{$ldap->suffix}\",";
		$acc[]="\t\"click\": \"BrowserExpand('$pathencurl')\",";
		$acc[]="\t\"hasChildren\": true";
		$acc[]="}";
				
		$accolades[]=@implode("\n", $acc);
				
		
		
		$f[]=@implode(",", $accolades);
		$f[]="]";
		echo @implode("\n", $f);
		return;
	}
	if($_GET["root"]=="source"){$_GET["root"]=base64_encode($root);}
	
	
	$sock=new sockets();
	$_GET["root"]=base64_decode($_GET["root"]);
	
	$mount_point=$_GET["root"];
	   writelogs("Browse: DN \"{$mount_point}\"",__FUNCTION__,__FILE__,__LINE__);
		$ldap=new ldapAD();
		if(count($res)>0){return $res;}
		$ld =$ldap->ldap_connection;
		$bind =$ldap->ldapbind;
		$suffix="$ldap->suffix";
		$res=array();
		$arr=array("ou");
		if($mount_point==null){$mount_point=$suffix;}
		$sr = @ldap_list($ld,$mount_point,'(&(objectclass=organizationalUnit)(ou=*))',$arr);
		if ($sr) {
			$hash=ldap_get_entries($ld,$sr);
			writelogs("Checking: $mount_point {$hash["count"]} entries",__FUNCTION__,__FILE__,__LINE__);
			for($i=0;$i<$hash["count"];$i++){
				//print_r($hash[$i]);
				$dn=$hash[$i]["dn"];
				$ouname=$hash[$i]["ou"][0];
				
				
				
				$path=$dn;
				$pathenc=base64_encode($path);
				$pathencurl=urlencode($pathenc);
				$acc=array();
				$acc[]="{";
				$acc[]="\t\"text\": \"$ouname\",";
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
		writelogs("Checking: dirdir=$mount_point no ous",__FUNCTION__,__FILE__,__LINE__);
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



