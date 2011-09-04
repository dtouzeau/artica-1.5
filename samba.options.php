<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.samba.aliases.inc');

	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["settings"])){main_settings();exit;}
	if(isset($_POST["strict_allocate"])){main_settings_save();exit;}
	

	js();
function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title="{file_sharing_behavior}&nbsp;&raquo;&nbsp;{$_GET["hostname"]}";
	$hostname=$_GET["hostname"];
	if($hostname==null){$title="{virtual_server}&nbsp;&raquo;&nbsp;{add}";}
	$title=$tpl->_ENGINE_parse_body($title);
	$html="YahooWin3(650,'$page?tabs=yes&hostname=$hostname','$title')";
	echo $html;	
	}
	
	function tabs(){
		$page=CurrentPageName();
		$tpl=new templates();
		$array["settings"]='{main_settings}';
		
		while (list ($num, $ligne) = each ($array) ){
			
			
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
			
			
		}
	
	
	echo "
	<div id=main_config_smboptions style='width:100%;height:400px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_smboptions').tabs();
			});
		</script>";	
	
}	

function main_settings(){
	
	$page=CurrentPageName();
	$tpl=new templates();	
	$hostname=$_GET["hostname"];
	$smb=new samba_aliases($hostname);

	
	
	$html="
	<div id='smboptions2div'>
	<div class=explain>{smb_perf_explain}</div>
	<table style='width:100%' class=form>
	<tr>	
		<td align='right' nowrap valign='top' class=legend>{samba_strict_allocate}:</td>
		<td valign='top'>" . Field_checkbox('strict_allocate',1,$smb->main_array["global"]["strict_allocate"])."</td>
		<td valign='top'>" . help_icon("{samba_strict_allocate_text}")."</td>
	</tr>
	<tr>	
		<td align='right' nowrap valign='top' class=legend>{samba_strict_locking}:</td>
		<td valign='top'>" . Field_checkbox('strict_locking',1,$smb->main_array["global"]["strict_locking"])."</td>
		<td valign='top'>" . help_icon("{samba_strict_locking_text}")."</td>
	</tr>
	<tr>	
		<td align='right' nowrap valign='top' class=legend>{samba_sync_always}:</td>
		<td valign='top'>" . Field_checkbox('sync_always',1,$smb->main_array["global"]["sync_always"])."</td>
		<td valign='top'>" . help_icon("{samba_sync_always_text}")."</td>
	</tr>	
	<tr>	
		<td align='right' nowrap valign='top' class=legend>{samba_getwd_cache}:</td>
		<td valign='top'>" . Field_checkbox('getwd_cache',1,$smb->main_array["global"]["getwd_cache"])."</td>
		<td valign='top'>" . help_icon("{samba_getwd_cache_text}")."</td>
	</tr>	
	
	<tr>	
		<td align='right' nowrap valign='top' class=legend>{smb2_protocol}:</td>
		<td valign='top'>" . Field_checkbox('smb2_protocol','1',$smb->main_array["global"]["smb2_protocol"])."</td>
		<td valign='top'>" . help_icon("{smb2_protocol_text}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align=right><hr>". button("{apply}","SaveSambaOptions2()")."</td>
	</tr>
	</table>
	</div>
	<script>
	
	function CheckSMBOPT2Form(){
		var smbver='$smb->SAMBA_VERSION';
		var upTo357=$smb->upTo357;
		var upTo36=$smb->upTo36;
		document.getElementById('smb2_protocol').disabled=true;
		document.getElementById('strict_allocate').disabled=true;
		
		if(upTo357==1){document.getElementById('strict allocate').disabled=false;}
		if(upTo36==1){
			document.getElementById('smb2_protocol').disabled=false;
		}	
	
	}
	
		var x_SaveSambaOptions2=function (obj) {
		    var hostname='$hostname';
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshTab('main_config_smboptions');
		}	
	
	function SaveSambaOptions2(){
		var upTo357=$smb->upTo357;
		var upTo36=$smb->upTo36;	
		if(upTo36==0){document.getElementById('smb2_protocol').checked=false;}
		var XHR = new XHRConnection();
		XHR.appendData('hostname','$hostname');
		if(document.getElementById('strict_allocate').checked){XHR.appendData('strict_allocate','1');}else{XHR.appendData('strict_allocate','no');}
		if(document.getElementById('strict_locking').checked){XHR.appendData('strict_locking','1');}else{XHR.appendData('strict_locking','no');}
		if(document.getElementById('sync_always').checked){XHR.appendData('sync_always','1');}else{XHR.appendData('sync_always','no');}
		if(document.getElementById('getwd_cache').checked){XHR.appendData('getwd_cache','1');}else{XHR.appendData('getwd_cache','no');}
		if(document.getElementById('smb2_protocol').checked){XHR.appendData('smb2_protocol','1');}else{XHR.appendData('smb2_protocol','no');}
		AnimateDiv('smboptions2div');
		XHR.sendAndLoad('$page', 'POST',x_SaveSambaOptions2);
	
	}
	
	CheckSMBOPT2Form();
</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_settings_save(){
	$hostname=$_POST["hostname"];
	$smb=new samba_aliases($hostname);	
	while (list ($num, $ligne) = each ($_POST) ){
		$smb->main_array["global"][$num]=$ligne;
		
	}
	
	$smb->SaveGlobals();
	
	
}
