<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_POST["enabled"])){SavefcgidCache();exit;}
	if(isset($_POST["CacheDisable"])){SaveCacheDisable();exit;}
	if(isset($_GET["CacheDisableList"])){CacheDisableList();exit;}
	if(isset($_POST["CacheDisableDel"])){CacheDisableDel();exit;}
page();	


function page(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$freeweb=new freeweb($_GET["servername"]);
	
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["enabled"])){$freeweb->Params["MOD_FCGID"]["enabled"]=0;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"])){$freeweb->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"]=5000;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"])){$freeweb->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"]=8;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["FcgidBusyTimeout"])){$freeweb->Params["MOD_FCGID"]["FcgidBusyTimeout"]=300;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["FcgidIdleTimeout"])){$freeweb->Params["MOD_FCGID"]["FcgidIdleTimeout"]=300;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["FcgidMaxRequestInMem"])){$freeweb->Params["MOD_FCGID"]["FcgidMaxRequestInMem"]=65536;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"])){$freeweb->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"]=100;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["FcgidMaxRequestLen"])){$freeweb->Params["MOD_FCGID"]["FcgidMaxRequestLen"]=131072;}
	if(!is_numeric($freeweb->Params["MOD_FCGID"]["FcgidOutputBufferSize"])){$freeweb->Params["MOD_FCGID"]["FcgidOutputBufferSize"]=65536;}
	
	
	

	
	$html="
	<div id='mod_fcgi_div'>
	<div class=explain>{apache_mod_fcgid_explain}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{enable}:<td>
		<td>". Field_checkbox("Modfcgid_enabled", 1,$freeweb->Params["MOD_FCGID"]["enabled"],"MOD_FCGIDEnableCheck()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{php_values}:<td>
		<td><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('freeweb.edit.fcgid.php.php?servername={$_GET["servername"]}');\"
		style='font-size:13px;font-weight:bold;text-decoration:underline'>{edit}</a>
		</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{PHP_FCGI_MAX_REQUESTS}:<td>
		<td>". Field_text("PHP_FCGI_MAX_REQUESTS", $freeweb->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"],"font-size:13px;padding:3px;width:90px")."&nbsp;</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{PHP_FCGI_CHILDREN}:<td>
		<td>". Field_text("PHP_FCGI_CHILDREN", $freeweb->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"],"font-size:13px;padding:3px;width:90px")."&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{FcgidMaxProcessesPerClass}:<td>
		<td>". Field_text("FcgidMaxProcessesPerClass", $freeweb->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"],"font-size:13px;padding:3px;width:90px")."&nbsp;</td>
		<td>". help_icon("{FcgidMaxProcessesPerClass_explain}")."</td>
	</tr>
	
	<tr>
		<td class=legend>{FcgidBusyTimeout}:<td>
		<td>". Field_text("FcgidBusyTimeout", $freeweb->Params["MOD_FCGID"]["FcgidBusyTimeout"],"font-size:13px;padding:3px;width:90px")."&nbsp;{seconds}</td>
		<td>". help_icon("{FcgidBusyTimeout_explain}")."</td>
	</tr>		
	<tr>
		<td class=legend>{FcgidIdleTimeout}:<td>
		<td>". Field_text("FcgidIdleTimeout", $freeweb->Params["MOD_FCGID"]["FcgidIdleTimeout"],"font-size:13px;padding:3px;width:90px")."&nbsp;{seconds}</td>
		<td>". help_icon("{FcgidIdleTimeout_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{FcgidMaxRequestInMem}:<td>
		<td>". Field_text("FcgidMaxRequestInMem", $freeweb->Params["MOD_FCGID"]["FcgidMaxRequestInMem"],"font-size:13px;padding:3px;width:90px")."&nbsp;bytes</td>
		<td>". help_icon("{FcgidMaxRequestInMem_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{FcgidMaxRequestLen}:<td>
		<td>". Field_text("FcgidMaxRequestLen", $freeweb->Params["MOD_FCGID"]["FcgidMaxRequestLen"],"font-size:13px;padding:3px;width:90px")."&nbsp;bytes</td>
		<td>". help_icon("{FcgidMaxRequestLen_explain}")."</td>
	</tr>		
	<tr>
		<td class=legend>{FcgidOutputBufferSize}:<td>
		<td>". Field_text("FcgidOutputBufferSize", $freeweb->Params["MOD_FCGID"]["FcgidOutputBufferSize"],"font-size:13px;padding:3px;width:90px")."&nbsp;bytes</td>
		<td>". help_icon("{FcgidOutputBufferSize_explain}")."</td>
	</tr>	
	
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveModFCGID()")."</td>
	</tr>
	</tbody>
	</table>	
	</div>

	
	
	
	<script>
		var x_SaveModFCGID=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshTab('main_config_freewebedit');	
		}		
	
		function SaveModFCGID(){
			var XHR = new XHRConnection();
			if(document.getElementById('Modfcgid_enabled').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}		
			XHR.appendData('PHP_FCGI_MAX_REQUESTS',document.getElementById('PHP_FCGI_MAX_REQUESTS').value);
			XHR.appendData('PHP_FCGI_CHILDREN',document.getElementById('PHP_FCGI_CHILDREN').value);
			XHR.appendData('FcgidMaxProcessesPerClass',document.getElementById('FcgidMaxProcessesPerClass').value);
			XHR.appendData('FcgidBusyTimeout',document.getElementById('FcgidBusyTimeout').value);
			
			XHR.appendData('FcgidBusyTimeout',document.getElementById('FcgidBusyTimeout').value);
			XHR.appendData('FcgidIdleTimeout',document.getElementById('FcgidIdleTimeout').value);
			XHR.appendData('FcgidMaxRequestInMem',document.getElementById('FcgidMaxRequestInMem').value);
			XHR.appendData('FcgidMaxRequestLen',document.getElementById('FcgidMaxRequestLen').value);
			XHR.appendData('FcgidOutputBufferSize',document.getElementById('FcgidOutputBufferSize').value);
			
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('mod_fcgi_div');
    		XHR.sendAndLoad('$page', 'POST',x_SaveModFCGID);
		}	
		
		function MOD_FCGIDEnableCheck(){
			document.getElementById('PHP_FCGI_MAX_REQUESTS').disabled=true;
			document.getElementById('PHP_FCGI_CHILDREN').disabled=true;
			document.getElementById('FcgidMaxProcessesPerClass').disabled=true;
			document.getElementById('FcgidBusyTimeout').disabled=true;
			document.getElementById('FcgidIdleTimeout').disabled=true;
			document.getElementById('FcgidMaxRequestInMem').disabled=true;
			document.getElementById('FcgidMaxRequestLen').disabled=true;
			document.getElementById('FcgidOutputBufferSize').disabled=true;
			
			if(document.getElementById('Modfcgid_enabled').checked){
				document.getElementById('PHP_FCGI_MAX_REQUESTS').disabled=false;
				document.getElementById('PHP_FCGI_CHILDREN').disabled=false;
				document.getElementById('FcgidMaxProcessesPerClass').disabled=false;
				document.getElementById('FcgidBusyTimeout').disabled=false;
				document.getElementById('FcgidIdleTimeout').disabled=false;
				document.getElementById('FcgidMaxRequestInMem').disabled=false;
				document.getElementById('FcgidMaxRequestLen').disabled=false;
				document.getElementById('FcgidOutputBufferSize').disabled=false;	
			}
		
		}
		
	MOD_FCGIDEnableCheck();
	
	</script>
	
	";
	
echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SavefcgidCache(){
	$freeweb=new freeweb($_POST["servername"]);
	while (list ($num, $ligne) = each ($_POST) ){$freeweb->Params["MOD_FCGID"][$num]=$ligne;}	
	$freeweb->SaveParams();
}

