<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.autofs.inc');
	include_once('ressources/class.computers.inc');

	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["nfs-add"])){popup_add();exit;}
	if(isset($_GET["nfs-server"])){add_nfs();exit;}
	if(isset($_GET["nfs-list"])){echo list_nfs();exit;}
	if(isset($_GET["nfs-delete"])){nfs_delete();exit;}
	
js();	


function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{NFS_CLIENT}','fileshares.index.php');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		YahooLogWatcher(650,'$page?popup=yes','$title');
	}

	
	
var x_NFSCLientAddForm=function (obj) {
	LoadAjax('nfs-client-right-panel','$page?nfs-list=yes');
	}	
	
	function NFSCLientAddForm(){
    	var XHR = new XHRConnection();
    	XHR.appendData('nfs-server',document.getElementById('nfs-server').value);
    	XHR.appendData('nfs-folder',document.getElementById('nfs-folder').value);
    	XHR.appendData('nfs-mount',document.getElementById('nfs-mount').value);
 		document.getElementById('nfs-client-right-panel').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_NFSCLientAddForm);
	}
	
	function NFSClientDelete(mount){
		var XHR = new XHRConnection();
    	XHR.appendData('nfs-delete',mount);
		document.getElementById('nfs-client-right-panel').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_NFSCLientAddForm);    	
	}
	
	function NFSClientAdd(){
		LoadAjax('nfs-client-right-panel','$page?nfs-add=yes');
	}

	
	{$prefix}LoadPage();";

	echo $html;
	}

function popup_add(){
	$html="
		<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>NFS {server}:</td>
			<td>" . Field_text('nfs-server')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>NFS {directory}:</td>
			<td>" . Field_text('nfs-folder')."</td>
		</tr>
		<tr>
			<td class=legend nowrap>{mount_name}:</td>
			<td>" . Field_text('nfs-mount')."</td>
		</tr>					
	<tr>
		<td colspan=2 align='right'><hr>
		<input type='button' OnClick=\"javascript:NFSCLientAddForm();\" value='{add}&nbsp;&raquo;'>
	</tr>
	</table>
	";
	
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html,'fileshares.index.php');		
	
}

function add_nfs(){
	$server=$_GET["nfs-server"];
	$folder=$_GET["nfs-folder"];
	$mount=$_GET["nfs-mount"];
	if(trim($mount)==null){$mount=$folder;}
	$mount=basename($mount);
	$auto=new autofs();
	$auto->nfs_add($folder,$server,$mount);
}

function list_nfs(){
$auto=new autofs();	
$array=$auto->list_nfs();

$html="<table style='width:100%'>";
$count=0;
while (list ($local_directory, $server) = each ($array) ){
	if($local_directory==null){continue;}
	$count=$count+1;
	$html=$html . 
	
	"<tr>
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td valign='top'><code style='font-size:13px'><b>$local_directory</b></code>
		<td valign='top'><code style='font-size:10px'>$server</code></td>
		<td width=1% valign='top'> " . imgtootltip('ed_delete.gif',"{delete}","NFSClientDelete('$local_directory')")."</td>
	</tr>
	
	";
	
	
}
if($count==0){$howto="<p class=caption>{NFS_CLIENT_HOWTO}</p>";}
$html=$html . "</table>";
$html="<div style='height:250px;overflow:auto;width:100%'>$html$howto</div>";
$html=RoundedLightWhite($html);
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html,'fileshares.index.php');	
	
}

function nfs_delete(){
	$mount=$_GET["nfs-delete"];
	$auto=new autofs();	
	$auto->nfs_delete($mount);
	
	
}

function popup(){
	
	
	$add=Paragraphe('database-network-add-64.png','{NFS_CLIENT_ADD}','{NFS_CLIENT_ADD_TEXT}',"javascript:NFSClientAdd()");
	$nfs=list_nfs();
	$html="<H1>{NFS_CLIENT}</H1>
	<p class=caption>{NFS_CLIENT_TEXT}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$add
		</td>
		<td valign='top'>
			<div id='nfs-client-right-panel'>$nfs</div>
		</td>
	</tr>
	</table>
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'fileshares.index.php');	
	
}

?>