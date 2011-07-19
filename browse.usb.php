<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.os.system.inc');	

	if(isset($_GET["index"])){external_storage_usb();exit;}
	if(isset($_GET["external-storage-usb-list"])){echo external_storage_usb_list();exit;}
	
	js();
	
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{use_usb_storage}',"dar.index.php");
	$prefix=str_replace(".","_",$page);
	$page=CurrentPageName();	
	$html="
	function {$prefix}Load(){
		RTMMail(650,'$page?index=yes','$title');
	}
	
	{$prefix}Load();
	
	function BrowseUsbSelect(uid){
	var stick_folder='';
	var stick_mounted='';
	var tmp='';
	var TargetField='{$_GET["set-field"]}';
	
	
	if(document.getElementById(uid+'_stick_mounted')){
		stick_mounted=document.getElementById(uid+'_stick_mounted').value;
		var re = new RegExp(document.getElementById(uid+'_stick_mounted').value+'/', 'g');
	}
		if(document.getElementById(uid+'_stick_folder')){
			tmp=document.getElementById(uid+'_stick_folder').value;
			if(tmp.length>0){
				if(re){
					tmp=tmp.replace(re,'');
				}
				stick_folder='/'+tmp;
			}
		}
	
	
	   if(document.getElementById('{$_GET["set-field"]}')){
	   
	   	document.getElementById('{$_GET["set-field"]}').value='usb:'+uid+stick_folder;
	   	RTMMailHide();
	   	
	   	if(TargetField=='dar-external-storage-formulaire'){
	   		ExternalUsbSelect(document.getElementById('{$_GET["set-field"]}').value);
		}
	   	
		}else{
			alert('Cannot find {$_GET["set-field"]} field');
		}
	}
	
	";
	
	echo $html;
	}


function external_storage_usb(){
	$list=external_storage_usb_list();
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<H1>{use_usb_storage}</H1>
	<table style='width:99%'>
	<tr>
	<td valign='top' with=70%>
	<p class=caption>{use_usb_storage_text}</p>
	<strong style='font-size:12px'>{use_usb_storage_explain}</strong>
	</td>
	<td valign='top' align='center' width=30%>
	" . Paragraphe32("refresh","refresh_text_usb","LoadAjax('usblistp','$page?external-storage-usb-list=yes')","64-usb-refresh.png"). "
	</td>
	</tr>
	</table>
	<hr>
	<div style='width:100%;height:300px;overflow:auto;padding:3px;' id='usblistp'>
		$list
	</div>
	";

echo  $tpl->_ENGINE_parse_body($html,"dar.index.php");	
	
}
function external_storage_usb_list(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	$tpl=new templates();
	if(!file_exists('ressources/usb.scan.inc')){return $tpl->_ENGINE_parse_body("<H3 style=color:red'>{error_no_socks}</H3>");}		
	include("ressources/usb.scan.inc");	
	include_once("ressources/class.os.system.tools.inc");
		
	
	
	$html="<table style='width:100%'><tr>";
	$count=0;
	$os=new os_system();
	while (list ($uid, $array) = each ($_GLOBAL["usb_list"]) ){
		if(preg_match('#swap#',$array["TYPE"])){continue;}
		if(trim($array["mounted"])=='/'){continue;}
		
		$start=null;
		$end=null;
		if($count==2){
			$start="<tr>";
			$end="</tr>";
			$count=0;
		}
		
		$VENDOR=$array["ID_VENDOR"];
		if(strtoupper($VENDOR)=="TANDBERG"){$img="tandberg-64.png";}else{$img="64-external-drive.png";}
		
		if($mounted=="/"){return "disk-64.png";}
		if($TYPE=="swap"){return "disk-64.png";}
		
		$html=$html.$start;
		$html=$html."<td valign='top'>";
		$html=$html .RoundedLightWhite("
				<table style='width:280px;margin:5px;background-image:url(img/$img);background-repeat:no-repeat'>
				<tr>
					<td valign='top'><div style='width:36px'>&nbsp;</div></td>
					<td valign='top'>".	$os->usb_parse_array($array)."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'><input type='button' OnClick=\"javascript:BrowseUsbSelect('$uid');\" value='{select} {this_device}&nbsp;&raquo;'></td>
				</tr>
			</table>");
		
		$html=$html ."</td>";
		$html=$html.$stop;
		$count=$count+1;
	}
	$html=$html."</tr>
	</table>";
	
	return  $tpl->_ENGINE_parse_body($html,"dar.index.php");
	
}
?>