<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.kav4samba.inc');
	include_once('ressources/class.os.system.inc');
	
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["usblist"])){echo autmount_list();exit;}
	if(isset($_GET["ShareDevice"])){ShareDevice();exit;}
	if(isset($_GET["DeleteUsbShare"])){DeleteUsbShare();exit;}
	

	
	
	//usb-share-128.png
	
	js();
	
	

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_SAMBA}');
	$prefix=str_replace('.','',$page);
	$samba_js=file_get_contents(dirname(__FILE__).'/js/samba.js');
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	

	$samba_js
	
	function {$prefix}LoadPage(){
		YahooWin2(650,'$page?popup=yes','$title');
		{$prefix}ChargeTimeout();
		}
		
	function {$prefix}demarre(){
		if(!YahooWin2Open()){return false;}
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=5-{$prefix}tant;
			if ({$prefix}tant <15 ) {                           
				{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
		      } else {
						if(!YahooWin2Open()){return false;}
						{$prefix}tant = 0;
						{$prefix}FillPage();
						{$prefix}demarre();
		   }
	}			
	
	
	function {$prefix}ChargeTimeout(){
		{$prefix}timeout={$prefix}timeout+1;
		
		if({$prefix}timeout>20){
					alert('time-out $page');
					return false;
				}		
		
		if(!document.getElementById('usb-list')){
			setTimeout(\"{$prefix}ChargeTimeout()\",900);
			return false;
			}
		{$prefix}timeout=0;
		{$prefix}FillPage();
		{$prefix}demarre();	
	}
	
	function {$prefix}FillPage(){	
		LoadAjax('usb-list','$page?usblist=yes');
	}
	
	
	var X_ShareDevice= function (obj) {
		var results=obj.responseText;
		alert(results);
		{$prefix}FillPage();
		}	
	
	function ShareDevice(path){
		var XHR = new XHRConnection();
		XHR.appendData('ShareDevice',path);
		document.getElementById('usb-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_ShareDevice);	
		}
		
	function DeleteUsbShare(path){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteUsbShare',path);
		document.getElementById('usb-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_ShareDevice);	
		}	
	
	{$prefix}LoadPage();";	
	
echo $html;
}

function popup(){
	$dev=Buildicon64('DEF_ICO_DEVCONTROL');
	
	$html="
	<H1>{SAMBA_USB_SHARE}</H1>
			<table style='width:100%'>
		<tr>
			<td valign='top' width=1% align='right'>
				$dev<hr><img src='img/usb-share-128.png'>
			</td>
			<td valign='top' width=99%>
			<p class=caption>{usb_share_explain}</p>
			" . RoundedLightWhite("<div id='usb-list' style='height:200px;width:99%;overflow:auto'></div>")."
		</tr>
		</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'fileshares.index.php');
	
}


function autmount_list(){
	$samba=new samba();
	$ldap=new clladp();
	$dn="ou=auto.automounts,ou=mounts,$ldap->suffix";
	
	$filter="(&(ObjectClass=automount)(automountInformation=*))";
	$attrs=array("automountInformation","cn");
	
	$html="<table style='width:99%'>";
	
	
$sr =@ldap_search($ldap->ldap_connection,$dn,$filter,$attrs);
		if($sr){
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($hash["count"]>0){
				for($i=0;$i<$hash["count"];$i++){
					$path=$hash[$i]["cn"][0];
					$automountInformation=$hash[$i][strtolower("automountInformation")][0];
					$js="ShareDevice('$path');";
					$delete="&nbsp;";
					if(is_array($samba->main_array[$path])){
						$delete=imgtootltip('ed_delete.gif','{delete}',"DeleteUsbShare('$path')");
						$js="FolderProp('$path')";
					}	
					$html=$html."
					<tr ".CellRollOver($js).">
						<td width=1%><img src='img/fw_bold.gif'></td>
						<td colspan=2 ><code style='font-size:13px;font-weight:bold'>$path</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td ><code style='font-size:1Opx;font-weight:bold'>$automountInformation</td>
						<td width=1%>$delete</td>
					</tr>
					<tr><td colspan=3><hr></td></tr>	";
					}
			}	
		}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
}

function ShareDevice(){
	$samba=new samba();
	$samba->main_array[$_GET["ShareDevice"]]["path"]="/automounts/{$_GET["ShareDevice"]}";
	$samba->main_array[$_GET["ShareDevice"]]["create mask"]="0660";
	$samba->main_array[$_GET["ShareDevice"]]["directory mask"]="0770";
	$samba->main_array[$_GET["ShareDevice"]]["force user"]="root";
	$samba->main_array[$_GET["ShareDevice"]]["force group"]="root";
	$samba->main_array[$_GET["ShareDevice"]]["browsable"]="yes";
	$samba->main_array[$_GET["ShareDevice"]]["writable"]="yes";
	$samba->SaveToLdap();
	}
function DeleteUsbShare(){
	$samba=new samba();
	unset($samba->main_array[$_GET["DeleteUsbShare"]]);
	$samba->SaveToLdap();
	
}

	
	
	


?>	