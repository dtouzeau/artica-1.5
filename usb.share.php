<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.samba.inc');

	
	$user=new usersMenus();
	$tpl=new templates();
	if(!$user->SAMBA_INSTALLED){echo "alert('".$tpl->javascript_parse_text("ERR:{APP_SAMBA}")."');";die();}
	if(!$user->blkid_installed){echo "alert('".$tpl->javascript_parse_text("{error_blkid_not_installed}")."');";die();}
	if((!$user->AsSystemAdministrator) OR (!$user->AsSambaAdministrator)){echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";die();}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ShareName"])){SaveGlobal();exit;}
js();

function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{usb_share}");
	$uuid=$_GET["uuid"];
	
	$html="
		YahooWin5('450','$page?popup=yes&uuid=$uuid','$title');";
		echo $html;
	
}


function popup(){
	$uuid=$_GET["uuid"];
	$usb=new usb($uuid);
	$samba=new samba();
	$folder_name=$samba->GetShareName("/media/{$_GET["uuid"]}");
	$page=CurrentPageName();
	
	$html="
		<div id='UsbShareMainform'>
		<table style='width:100%'>
		<tr>
			<td class=legend style='font-size:13px'>{label}:</td>
			<td style='font-size:13px'><strong>$usb->LABEL</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{share_name}:</td>
			<td style='font-size:13px'>". Field_text("ShareName",
			$folder_name,"font-size:13px;padding:3px",null,null,null,false,"SaveUsbSharePress(event)")."</td>
		</tr>	
		<tr>
			<td colspan=2 align='right'><hr>". button("{share}","SaveUsbShare()")."</td>
		</tr>	
		</table>
		</div>
		
		<script>

		var x_SaveUsbShare= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin5Hide();
			UUIDINDEXPOPREFRESH();
			}	
		
		function SaveUsbShare(){
			var XHR = new XHRConnection();
			XHR.appendData('uuid','{$_GET["uuid"]}');
			XHR.appendData('ShareName',document.getElementById('ShareName').value);
			document.getElementById('UsbShareMainform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_SaveUsbShare);
		}		
		
		function SaveUsbSharePress(e){if(checkEnter(e)){SaveUsbShare();}}
		
		</script>
		
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function SaveGlobal(){
	$samba=new samba();
	$shared_name=$_GET["ShareName"];
	$php5=LOCATE_PHP5_BIN();
	$folder_name=$samba->GetShareName("/media/{$_GET["uuid"]}");
	if($folder_name==null){
		$samba->main_array["$shared_name"]["path"]="/media/{$_GET["uuid"]}";
		$samba->main_array["$shared_name"]["create mask"]= "0777";
		$samba->main_array["$shared_name"]["directory mask"] = "0777";
		$samba->main_array["$shared_name"]["root preexec"] = "$php5 /usr/share/artica-postfix/exec.samba.php --usb-mount {$_GET["uuid"]} %u";
		$samba->main_array["$shared_name"]["root postexec"] = "$php5 /usr/share/artica-postfix/exec.samba.php --usb-umount {$_GET["uuid"]} %u";
		$samba->main_array["$shared_name"]["root preexec close"] = "yes";
		$samba->SaveToLdap();		
	}else{
		$samba->main_array["$folder_name"]["path"]="/media/{$_GET["uuid"]}";
		$samba->main_array["$folder_name"]["create mask"]= "0777";
		$samba->main_array["$folder_name"]["directory mask"] = "0777";
		$samba->main_array["$folder_name"]["root preexec"] = "$php5 /usr/share/artica-postfix/exec.samba.php --usb-mount {$_GET["uuid"]} %u";
		$samba->main_array["$folder_name"]["root postexec"] = "$php5 /usr/share/artica-postfix/exec.samba.php --usb-umount {$_GET["uuid"]} %u";
		$samba->main_array["$folder_name"]["root preexec close"] = "yes";
		$samba->SaveToLdap();		
	}
	
	
	
}
	
?>