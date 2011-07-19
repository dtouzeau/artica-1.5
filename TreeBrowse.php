<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	$user=new usersMenus();
	if($user->AsArticaAdministrator==false){die();exit();}
	if(isset($_GET["jdisk"])){echo jdisk();exit;}
	if(isset($_GET["main_disks_discover"])){echo main_disks_discover();exit;}
	if(isset($_GET["browsedisk_start"])){echo browsedisk_start();exit;}
	if(isset($_POST["branch_id"])){json_root();exit;}
	if(isset($_GET["TreeRightInfos"])){TreeRightInfos();exit;}
	$page=CurrentPageName();
	
$html="
YahooWin4(450,'$page?main_disks_discover=yes&t={$_GET["t"]}');
";
	
	
echo $html;	
	

function Get_mounted_path($dev,$array){
$regex_pattern="#\/dev\/$dev#";
if(is_array($array)){
while (list ($num, $val) = each ($array) ){
		if(preg_match($regex_pattern,$val["PATH"])){
			return $val["mounted"];
			break;
		}
	}	
	
}}

	
function main_disks_discover(){
	$users=new usersMenus();
	$Disks=$users->disks_size;
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	if(!file_exists('ressources/usb.scan.inc')){
		return $tpl->_ENGINE_parse_body("<H1>{error_no_socks}</H1>");
		
	}
	
	include_once("ressources/usb.scan.inc");	
	
	if($Disks<>null){
		$tbl=explode(";",$Disks);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$values=explode(",",$val);
				if(is_array($values)){
					$dc=$dc+1;
					$disk=$values[0];
					$size=$values[1];
					$occ=$values[2];
					$disp=$values[3];
					$pourc=$values[4];
					$path=Get_mounted_path($disk,$_GLOBAL["usb_list"]);
					$html=$html . "
					
					
					<table style='width:220px;margin:3px'>
					<tr>
					<td width=1%>
						" . imgtootltip('scan-disk-48.png',$disk,"Loadjs('TreeBrowse.php?jdisk=$disk&mounted=$path&t={$_GET["t"]}');Loadjs('Tree.js');")."</td>
					<td style='font-size:11px' valign='top'>
						$disk ($pourc% {used}).
					</td>
					</tr>
					</table>	
					
					";
					
					
				}
			}
			
		}
	}
	


	$html="<H5>{select_disk}</H5><br>".RoundedLightGrey("
	<table style='width:100%'>
	$html
	</table>
	");
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function jdisk(){
	$page=CurrentPageName();
	$mounted=$_GET["mounted"];
	
	if($_GET["t"]<>null){
		$return_f=$_GET["t"];
	}else{
		$return_f="TreeSelectedFolder";
	}
	
	$html="
	
	var Folderstruct = [
	{
		'id':'$mounted',
		'txt':'{$_GET["jdisk"]}',
		'img':'database-16.png', // Image s'il n'a pas d'enfants
		'imgopen':'database-16.png', // Image s'il a des enfants et qu'il est ouvert
		'imgclose':'database-16.png', // Image s'il a des enfants et qu'il est ferm�
		'onopenpopulate' : TreeFoldersPopulate,
		'openlink' : '$page?mounted=$mounted&t={$_GET["t"]}',
		'canhavechildren' : true
	}
	]; 
	
var x_TreeFolders= function (obj) {
       document.getElementById('dialog4').innerHTML=obj.responseText;
       tree = new TafelTree('folderTree', Folderstruct, 'img/', '100%', 'auto');
       tree.generate();   
}

function TreeFoldersPopulate (branch, response) {
	if (response.length>0) {
		return response;
		}
		else {
        	return false;
        }
}

function TreeClick(branch,status){
     var branch_id=branch.getId();
     if(document.getElementById('TreeRightInfos')){
        LoadAjax('TreeRightInfos','$page?TreeRightInfos='+branch_id+'&t={$_GET["t"]}');
     }
        
     return true;   
}

function SelectPath(p){

  if(document.getElementById('restore_path')){
  	document.getElementById('restore_path').value=p;
  	YAHOO.example.container.dialog4.hide();
  	return false;
  }

  if(document.getElementById('$return_f')){
  	document.getElementById('$return_f').value=p;
  	YAHOO.example.container.dialog4.hide();
  }

}



	var XHR = new XHRConnection();
	XHR.appendData('browsedisk_start','yes');
	XHR.sendAndLoad('$page', 'GET',x_TreeFolders);   
	";
	
echo $html;	
	
}

function browsedisk_start(){
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<div style='overflow-y:auto;height:300px;width:100%'>
		<div id='folderTree'></div>
	</div>
	</td>
	<td valign='top'>
	
	<div id='TreeRightInfos'></div></td>
	</tr>
	</table>
	";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}

function json_root($path=null){
	$path=$_POST["branch_id"];
	$page=CurrentPageName();
	$sock=new sockets();
	if($path==null){
		$datas=$sock->getfile('dirdir:/');}
	else{
		$datas=$sock->getfile('dirdir:'.$path);	
	}
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	while (list($num,$val)=each($tbl)){
		if(trim($val)<>null){
			$newpath="$path/$val";
			$img='folder.gif';
			$pop="'onopenpopulate' : TreeFoldersPopulate,
			'openlink' : '$page',
			'onclick' : TreeClick,
			'canhavechildren' : true";
			

			
			$arr[]="{
    			'id':'$newpath',
    			'txt':'$val',
    			'img':'$img',
				'imgopen':'folderopen.gif', 
				'imgclose':'folder.gif', 
				$pop
				},";
		}
		
	}
	if(!is_array($arr)){return null;}
	$res=implode("\n",$arr);
	if(substr($res,strlen($res)-1,1)==','){
		$res=substr($res,0,strlen($res)-1);
	}
	
	echo "[$res]";
}

function TreeRightInfos(){
	$path=$_GET["TreeRightInfos"];
	$path=str_replace('//','/',$path);
	$html="
	<H5>{selected_folder}</H5>
	<strong>$path</strong>
	<div style='text-align:left'>
	<input type='button' OnClick=\"javascript:SelectPath('$path');\" value='{apply}&nbsp;&raquo;'>
	</div>
	
	";
	
$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);	
	
}
	
	


?>