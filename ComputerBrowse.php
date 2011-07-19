<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');
include_once('ressources/class.computers.inc');	
	
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		writelogs("No privileges on this account..",__FUNCTION__,__FILE__);
		die();exit();
		}
	if(isset($_POST["dir"])){json_root();exit;}
	if(isset($_GET["init"])){echo init();exit;}
	if(isset($_GET["main_discover"])){echo main_discover();exit;}
	if(isset($_GET["folder_selected_start"])){echo folder_selected_start();exit;}
	if(isset($_GET["BrowseDirs"])){exit;}
	if(isset($_GET["ComputerFolderSelected"])){TreeRightInfos();exit;}
	if(isset($_GET["rmdirp"])){rmdirp();exit;}
	if(isset($_GET["mainform"])){mainform();exit;}
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{browse}');
$html="

function Browse(){
	YahooWinBrowse(550,'$page?main_discover=yes&t={$_GET["t"]}&computer={$_GET["computer"]}&field={$_GET["field"]}&format-artica={$_GET["format-artica"]}','$title::{$_GET["computer"]}');
	}
	
Browse();

function BrowseDir(dir){
	Loadjs('$page?init='+dir+'&computer={$_GET["computer"]}&field={$_GET["field"]}&format-artica={$_GET["format-artica"]}');
}

function BrowseComputerFillForm(){
	YahooWinBrowse(550,'$page?mainform=yes&t={$_GET["t"]}&computer={$_GET["computer"]}&field={$_GET["field"]}&format-artica={$_GET["format-artica"]}','$title::{$_GET["computer"]}');
}
function SelectDirectoryComputer(dir){
	var artica=document.getElementById('format-artica').value;
	var pattern='';
  	var username=document.getElementById('username').value;
	var password=document.getElementById('password').value;
	var computer=document.getElementById('computer').value;
	var TargetField='{$_GET["field"]}';
	if(dir.length==0){
	 if(document.getElementById('targetfolderfilled')){
	 	dir=document.getElementById('targetfolderfilled').value;
	 }
	}

	
  	if(document.getElementById('{$_GET["field"]}')){
  		if(artica=='yes'){
  			if(username.length>0){
  				pattern='smb://'+username+':'+password+'@'+computer+'/'+dir;
			}else{
				alert('$error_credentials');
				pattern='smb://'+computer+'/'+dir;
			}
			RTMMailHide();
		}else{
			pattern=dir;
		}
		document.getElementById('{$_GET["field"]}').value=pattern;
		if(TargetField=='dar-external-storage-formulaire'){
			ExternalUsbSelect(pattern);
		}
		
  		
  		
	}else{
	alert('{$_GET["field"]} unavailable');
	}
	YahooWinBrowseHide();
}


";
	
	
echo $html;

function json_root($path=null){
	
	$path=$_POST["dir"];
	if(substr($path,strlen($path)-1,1)=='/'){$path=substr($path,0,strlen($path)-1);}
	writelogs("Requested:$path on computer {$_GET["computer"]}",__FUNCTION__,__FILE__);
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$settings=$tpl->_ENGINE_parse_body('{settings}');
	$directory=$tpl->_ENGINE_parse_body('{directory}');	
	if($path==null){
		$datas=$sock->getfile("smbclientStartBrowseComputer:{$_GET["computer"]}");}
	else{
		$datas=$sock->getfile("smbclientBrowseComputer:{$_GET["computer"]};$path");
	}	
	
	
	$tbl=explode("\n",@file_get_contents($datas));
	
	
	
	while (list($num,$val)=each($tbl)){
		if(preg_match("#<folder>(.+)</folder>#",$val,$re)){
			$folder_array[]=$re[1];
		}
		
	}
	
if(!is_array($folder_array)){
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
	echo "<li class=\"file ext_settings\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir']) . "\">". htmlentities("$directory ".basename($_POST['dir'])." - $settings")."</a></li>";
	echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] ) . "/\">" . htmlentities($val) . "</a></li>";	
	return null;
	}
	
	
	writelogs("Requested:$path ". count($folder_array)." rows",__FUNCTION__,__FILE__);
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
	
	
	while (list($num,$val)=each($folder_array)){
		if(trim($val)==null){continue;}
			$val=str_replace("Menu D Mes documents","Mes documents",$val);
			$newpath="$path/$val";
			$newpathsmb=str_replace('//','/',$newpath);
			$newpathsmb=addslashes($newpathsmb);
			echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($path .'/'. $val) . "/\">" . htmlentities($val) . "</a></li>";	
			
			
		}
}


function init(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$error_credentials=$tpl->_ENGINE_parse_body('{ERROR_SHARE_SELECTED_NO_CREDENTIALS}');
	$cmp=str_replace('.','_',$_GET["computer"]);
	$cmp=str_replace('-','_',$cmp);
	$cmp="ComputerBrowse_";
	$html="
	var mem_branch_id='';
	var mem_item='';

	
function {$cmp}_initTree(){
		$(document).ready( function() {
			$('#Tree_$cmp').fileTree({ 
					root: '{$_GET["init"]}', 
					script: '$page?BrowseDirs={$_GET["init"]}=&computer={$_GET["computer"]}&field={$_GET["field"]}&format-artica={$_GET["format-artica"]}', 
					folderEvent: 'click', 
					expandSpeed: 750, 
					collapseSpeed: 750, 
					expandEasing: 'easeOutBounce', 
					collapseEasing: 'easeOutBounce' ,
					multiFolder: false}, function(file) {ComputerClick(file);});
				
				
			});
}	
	

var {$cmp}_x_InitTree= function (obj) {
       document.getElementById('Browse_content').innerHTML=obj.responseText;
       {$cmp}_initTree();
      
}



function ComputerClick(branch){
     var branch_id=branch;
     if(document.getElementById('CompyterRightInfos')){
        LoadAjax('CompyterRightInfos','$page?ComputerFolderSelected='+branch_id+'&field={$_GET["field"]}&format-artica={$_GET["format-artica"]}&computer={$_GET["computer"]}');
     }
        
     return true;   
}

	var XHR = new XHRConnection();
	XHR.appendData('folder_selected_start','yes');
	XHR.appendData('computer','{$_GET["computer"]}');
	XHR.sendAndLoad('$page', 'GET',{$cmp}_x_InitTree); 
	
	";	
	
	echo $html;
	
}
function folder_selected_start(){
	$cmp=str_replace('.','_',$_GET["computer"]);
	$cmp=str_replace('-','_',$cmp);	
	$html="
	<H1>{$_GET["computer"]}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
	<div style='overflow-y:auto;height:300px;width:100%'><div id='Tree_ComputerBrowse_'></div></div>
	
	</td>
	<td valign='top'>
	
	".RoundedLightWhite("<div id='CompyterRightInfos'></div>")."</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);
	
}



function main_discover(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$Disks=$users->disks_size;
	$sock=new sockets();
	if($users->smbclient_installed){
			$datas=$sock->getfile('smbclientStartBrowseComputer:'.$_GET["computer"]);
			$tbl=explode("\n",@file_get_contents($datas));
		
			while (list ($num, $val) = each ($tbl) ){
				if(trim($val)==null){continue;}
				if(!preg_match("#<folder>(.+)</folder>#",$val,$re)){continue;}
				$img="64-share.png";
				
				$html=$html . "<div style='float:left;width:220px'>" . Paragraphe($img,$re[1],"{browse}","javascript:Loadjs('$page?init={$re[1]}&computer={$_GET["computer"]}&field={$_GET["field"]}&format-artica={$_GET["format-artica"]}')")."</div>";
				
			}
		}
		
		if(!$users->smbclient_installed){
			$html= 
			"<div style='float:left;width:220px'>
				" . Paragraphe("64-share.png","{fill_form}","{smbclient_not_installed_fill_form}",
				"javascript:BrowseComputerFillForm()").
			"</div>";
			
		}
			
		
$html=$html . "<div style='float:left;width:220px'>".
Paragraphe("64-network-user.png","{COMPUTER_ACCESS}","{COMPUTER_ACCESS_TEXT}","javascript:Loadjs('computer.passwd.php?uid={$_GET["computer"]}')")."</div>";		
		
	
	


	$html="<H1>{select}</H1><br>".RoundedLightWhite("
	<div style='width:100%;height:400px;overflow:auto'>
	$html
	</div><input type='hidden' id='FenetreComputerBrowse' value='field={$_GET["field"]}&format-artica={$_GET["format-artica"]}'>
	");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'fileshares.index.php');
	
	
}
function TreeRightInfos(){
	
	if(strpos($_GET["computer"],'$')==0){$uid=$_GET["computer"].'$';}else{$uid=$_GET["computer"];}
	$computer=new computers($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	$username=$ini->_params["ACCOUNT"]["USERNAME"];
	$password=$ini->_params["ACCOUNT"]["PASSWORD"];	
	
	$_GET["ComputerFolderSelected"]=str_replace("Menu D Mes documents","Mes documents",$_GET["ComputerFolderSelected"]);
	$folder=$_GET["ComputerFolderSelected"];
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/48-network-folder.png'></td>
	<td valign='top'>
	<H3>{$_GET["computer"]}</H3>
	<input type='hidden' id='computer' value='{$_GET["computer"]}'>
	<input type='hidden' id='format-artica' value='{$_GET["format-artica"]}'>
	
	
	<hr>
	<table style='width:100%'>
	<tr>
		<td class=legend>{folder}:</td>
		<td><code>$folder</code></td>
	</tr>
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text('username',$username,'width:120px')."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password('password',$password,'width:120px')."</td>
	</tr>			
	</table>
	<div style='width:100%;text-align:right'>
	<input type='button' OnClick=\"javascript:SelectDirectoryComputer('$folder')\" value='&nbsp;&nbsp;&nbsp;{select}&nbsp;&raquo;&raquo;&nbsp;&nbsp;&nbsp;'>
	</div>
	</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function mainform(){
if(strpos($_GET["computer"],'$')==0){$uid=$_GET["computer"].'$';}else{$uid=$_GET["computer"];}
	$computer=new computers($uid);
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	$username=$ini->_params["ACCOUNT"]["USERNAME"];
	$password=$ini->_params["ACCOUNT"]["PASSWORD"];	
	
	
	$html="
	<H1>{fill_form}::{$_GET["computer"]}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/48-network-folder.png'></td>
	<td valign='top'>
	<input type='hidden' id='computer' value='{$_GET["computer"]}'>
	<input type='hidden' id='format-artica' value='{$_GET["format-artica"]}'>
	
	
	<hr>
	<table style='width:100%'>
	<tr>
		<td class=legend>{folder}:</td>
		<td>". Field_text('targetfolderfilled',null,'width:120px')."</td>
	</tr>
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text('username',$username,'width:120px')."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password('password',$password,'width:120px')."</td>
	</tr>			
	</table>
	<div style='width:100%;text-align:right'>
	<input type='button' OnClick=\"javascript:SelectDirectoryComputer('$folder')\" value='&nbsp;&nbsp;&nbsp;{select}&nbsp;&raquo;&raquo;&nbsp;&nbsp;&nbsp;'>
	</div>
	</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


	
?>