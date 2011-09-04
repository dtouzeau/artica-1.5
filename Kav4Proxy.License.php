<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.kav4proxy.inc');

$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["Kav4proxy-license-popup"])){echo kav4proxy_license_popup();exit;}
	if(isset($_GET["Kav4proxy-license-delete"])){echo kav4proxy_license_delete();exit;}
	if(isset($_GET["kav4proxy-license-iframe"])){echo kav4proxy_license_iframe();exit;}	
	if(isset($_GET["Kav4ProxyLicenseInfos"])){license_info();exit;}
	
	if( isset($_POST['upload']) ){kav4proxy_license_upload();exit();}

kav4proxy_license_js();

function kav4proxy_license_delete(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?Kav4ProxyLicenseDelete&type='.$_GET["license-type"]));	
}

function kav4proxy_license_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_KAV4PROXY}::{license_info}');
	if($_GET["license-type"]=="milter"){$title=$tpl->_ENGINE_parse_body('{APP_KAVMILTER}::{license_info}','squid.index.php');}
	if($_GET["license-type"]=="kas"){$title=$tpl->_ENGINE_parse_body('{APP_KAS3}::{license_info}','squid.index.php');}
	
	$html="
	function Kav4ProxyLicenseStart(){
		YahooWin5('700','$page?Kav4proxy-license-popup=yes&license-type={$_GET["license-type"]}','$title');
	}
	
	
	var x_Kav4ProxyDeleteKey= function (obj) {
		Kav4ProxyLicenseStart();
	}
		
	function Kav4ProxyDeleteKey(){
		var XHR = new XHRConnection();
		document.getElementById('kav4licenseDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.appendData('Kav4proxy-license-delete','yes');
		XHR.appendData('license-type','{$_GET["license-type"]}');
		XHR.sendAndLoad('$page', 'GET',x_Kav4ProxyDeleteKey);
	}
	
	Kav4ProxyLicenseStart();
";
	echo $html;	
	}
	
function kav4proxy_license_popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?Kav4ProxyLicense&type='.$_GET["license-type"]));
	
	
	
	
	$html="
	<table style='width:100%' class=form>
	<tr>
	<td width=100%>".imgtootltip("refresh-32.png","{refresh}","LicenseInfos()")."</td>
	<td width=1%>".imgtootltip("delete-32.png","{delete}","Kav4ProxyDeleteKey()")."</td>
	</tr>
	</table>
	
	
	<div style='width:100%;height:240px;overflow:auto' id='kav4licenseDiv'>
		<div id='Kav4ProxyLicenseInfos'></div>
	</div>
<center>
	<iframe SRC='$page?kav4proxy-license-iframe=yes&license-type={$_GET["license-type"]}' WIDTH=99% FRAMEBORDER=0 MARGINWIDTH=0 MARGINHEIGHT=0 SCROLLING=no></iframe>
</center>

<script>
	function LicenseInfos(){
		LoadAjax('Kav4ProxyLicenseInfos','$page?Kav4ProxyLicenseInfos=yes&license-type={$_GET["license-type"]}');
	}
	
	LicenseInfos();
</script>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function license_info(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?Kav4ProxyLicense&type='.$_GET["license-type"]));	
	$tp=explode("\n",$datas);
	$html="<table style='width:99%' class=form>
	<tbody>";
	while (list ($num, $val) = each ($tp)){
			if(trim($val)==null){continue;}
			$val=htmlspecialchars($val);
			if(strlen($val)>89){$val=texttooltip(substr($val,0,86).'...',$val,null,null,1);}
			$html=$html . "
			<tr>
				<td style='font-size:12px'>
					<code>$val</code>
				</td>
			</tr>";
				
	}
	
	$html=$html . "</tbody>
	</table>";
	
	echo $html;
}
function kav4proxy_license_iframe($error=null){
	$page=CurrentPageName();
	$html="
	<span style='color:red;font-weight:bold;font-size:12px;padding-left:5px'>$error</span>
	<input type=\"hidden\" name=\"upload\" value=\"1\">
	<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
	<table style='width:99%' class=form align='center'>
	<tr>
		<td class=legend valign='middle'>{upload_new_license}:</td>
		<td>
			<input type=\"file\" name=\"fichier\" size=\"30\">
			<input type=\"hidden\" name=\"license-type\" value='{$_GET["license-type"]}'>
		</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>
			<input type='submit' name='upload' value='{upload file}&nbsp;&raquo;' style='width:220px'>
		</td>
	</tr>
</table>
</form>	
	";
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo iframe($html,0);
	
}

function kav4proxy_license_upload(){
	$tmp_file = $_FILES['fichier']['tmp_name'];
	if($_SESSION[$tmp_file]["up"]){
		writelogs("Uploading license $tmp_file already sended",__FUNCTION__,__FILE__);
		kav4proxy_license_iframe($_SESSION[$tmp_file]["results"]);
		exit;
		}
	$_SESSION[$tmp_file]["up"]=true;
	
	writelogs("Uploading license $tmp_file",__FUNCTION__,__FILE__);
	$content_dir=dirname(__FILE__)."/ressources/logs";
	if(!is_dir($content_dir)){mkdir($content_dir);}
	if( !is_uploaded_file($tmp_file) ){
		writelogs("not uploaded $tmp_file",__FUNCTION__,__FILE__);
		kav4proxy_license_iframe('{error_unable_to_upload_file}');exit();
	}
	
	 $type_file = $_FILES['fichier']['type'];
	 if( !strstr($type_file, 'key')){kav4proxy_license_iframe('{error_file_extension_not_match} :key');	exit();}
	 $name_file = $_FILES['fichier']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){kav4proxy_license_upload("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);exit();}
     
    $_GET["moved_file"]=$content_dir . "/" .$name_file;
    $socket=new sockets();
    writelogs("Kav4ProxyUploadLicense:$content_dir/$name_file",__FUNCTION__,__FILE__);
 	$res=base64_decode($socket->getFrameWork("cmd.php?Kav4ProxyUploadLicense=$content_dir/$name_file&type={$_POST["license-type"]}"));
	$tp=explode("\n",$res);
	$tp[]="ltp:{$_POST["license-type"]}";
	
	while (list ($num, $val) = each ($tp)){
		if(trim($val)==null){continue;}
		$val=htmlspecialchars($val);
		
		$html=$html . "<div><code>$val</code></div>";
		}
  	writelogs("$html",__FUNCTION__,__FILE__);
$_SESSION[$tmp_file]["results"]=$html;    
kav4proxy_license_upload($html);
}
