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
	
	if(isset($_POST["Modenabled"])){SaveWebCache();exit;}
	if(isset($_POST["CacheDisable"])){SaveCacheDisable();exit;}
	if(isset($_GET["CacheDisableList"])){CacheDisableList();exit;}
	if(isset($_POST["CacheDisableDel"])){CacheDisableDel();exit;}
page();	


function page(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$freeweb=new freeweb($_GET["servername"]);
	if(!is_numeric($freeweb->Params["MOD_CACHE"]["CacheDefaultExpire"])){$freeweb->Params["MOD_CACHE"]["CacheDefaultExpire"]=3600;}
	if(!is_numeric($freeweb->Params["MOD_CACHE"]["CacheMinExpire"])){$freeweb->Params["MOD_CACHE"]["CacheMinExpire"]=3600;}
	if(!is_numeric($freeweb->Params["MOD_CACHE"]["CacheMinFileSize"])){$freeweb->Params["MOD_CACHE"]["CacheMinFileSize"]=64;}
	if(!is_numeric($freeweb->Params["MOD_CACHE"]["CacheMaxFileSize"])){$freeweb->Params["MOD_CACHE"]["CacheMaxFileSize"]=64000;}
	
	$html="
	<div id='mod_cache_div'>
	<div class=explain>{apache_mod_cache_explain}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{enable}:<td>
		<td>". Field_checkbox("Modenabled", 1,$freeweb->Params["MOD_CACHE"]["Modenabled"],"CacheEnableCheck()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{CacheDefaultExpire}:<td>
		<td>". Field_text("CacheDefaultExpire", $freeweb->Params["MOD_CACHE"]["CacheDefaultExpire"],"font-size:13px;padding:3px;width:90px")."&nbsp;{seconds}</td>
		<td>". help_icon("{CacheDefaultExpire_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{CacheMinExpire}:<td>
		<td>". Field_text("CacheMinExpire", $freeweb->Params["MOD_CACHE"]["CacheMinExpire"],"font-size:13px;padding:3px;width:90px")."&nbsp;{seconds}</td>
		<td>". help_icon("{CacheMinExpire_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{CacheMinFileSize}:<td>
		<td>". Field_text("CacheMinFileSize", $freeweb->Params["MOD_CACHE"]["CacheMinFileSize"],"font-size:13px;padding:3px;width:90px")."&nbsp;bytes</td>
		<td>". help_icon("{CacheMinFileSize_explain}")."</td>
	</tr>		
	<tr>
		<td class=legend>{CacheMaxFileSize}:<td>
		<td>". Field_text("CacheMaxFileSize", $freeweb->Params["MOD_CACHE"]["CacheMaxFileSize"],"font-size:13px;padding:3px;width:90px")."&nbsp;bytes</td>
		<td>". help_icon("{CacheMinFileSize_explain}")."</td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveModCache()")."</td>
	</tr>
	</table>	
	</div>
	<div class=explain>{CacheDisable_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{url}:</td>
		<td>". Field_text("CacheDisableUriAdd",null,"font-size:14px;padding:3px",null,null,null,false,'CacheDisableUriAddCheck(event)')."</td>
		<td width=1%>". button("{add}","CacheDisableAdd()")."</td>
	</tr>
	</table>
	<center>
	<div id='CacheDisableList' style='width:60%;height:180px;overflow:auto'></div>
	</center>
	
	
	
	<script>
		var x_SaveModCache=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			RefreshTab('main_config_freewebedit');	
		}		
	
		function SaveModCache(){
			var XHR = new XHRConnection();
			if(document.getElementById('Modenabled').checked){XHR.appendData('Modenabled',1);}else{XHR.appendData('Modenabled',0);}		
			XHR.appendData('CacheDefaultExpire',document.getElementById('CacheDefaultExpire').value);
			XHR.appendData('CacheMinExpire',document.getElementById('CacheMinExpire').value);
			XHR.appendData('CacheMinFileSize',document.getElementById('CacheMinFileSize').value);
			XHR.appendData('CacheMaxFileSize',document.getElementById('CacheMaxFileSize').value);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('mod_cache_div');
    		XHR.sendAndLoad('$page', 'POST',x_SaveModCache);
		}	
		
		function CacheEnableCheck(){
			document.getElementById('CacheDefaultExpire').disabled=true;
			document.getElementById('CacheMinExpire').disabled=true;
			document.getElementById('CacheMinFileSize').disabled=true;
			document.getElementById('CacheMaxFileSize').disabled=true;
			if(document.getElementById('Modenabled').checked){
				document.getElementById('CacheDefaultExpire').disabled=false;
				document.getElementById('CacheMinExpire').disabled=false;
				document.getElementById('CacheMinFileSize').disabled=false;
				document.getElementById('CacheMaxFileSize').disabled=false;			
			}
		
		}
		
		var x_CacheDisableAdd=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			CacheDisableList();	
		}			
		
		function CacheDisableAdd(){
			var XHR = new XHRConnection();
			XHR.appendData('CacheDisable',document.getElementById('CacheDisableUriAdd').value);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('CacheDisableList');
    		XHR.sendAndLoad('$page', 'POST',x_CacheDisableAdd);			
		}
		
		function CacheDisableDelete(uri){
			var XHR = new XHRConnection();
			XHR.appendData('CacheDisableDel',uri);
			XHR.appendData('servername','{$_GET["servername"]}');
			AnimateDiv('CacheDisableList');
    		XHR.sendAndLoad('$page', 'POST',x_CacheDisableAdd);			
		}
		
		function CacheDisableUriAddCheck(e){
			if(checkEnter(e)){CacheDisableAdd();}
		}
		
		function CacheDisableList(){
			LoadAjax('CacheDisableList','$page?CacheDisableList=yes&servername={$_GET["servername"]}');
		}
		
	CacheEnableCheck();
	CacheDisableList();
	</script>
	
	";
	
echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SaveCacheDisable(){
	$freeweb=new freeweb($_POST["servername"]);
	$freeweb->Params["MOD_CACHE"]["CacheDisable"][$_POST["CacheDisable"]]=true;
	$freeweb->SaveParams();
}

function CacheDisableDel(){
	$_POST["CacheDisableDel"]=base64_decode($_POST["CacheDisableDel"]);
	$freeweb=new freeweb($_POST["servername"]);
	unset($freeweb->Params["MOD_CACHE"]["CacheDisable"][$_POST["CacheDisableDel"]]);
	$freeweb->SaveParams();	
}

function SaveWebCache(){
	$freeweb=new freeweb($_POST["servername"]);
	while (list ($num, $ligne) = each ($_POST) ){$freeweb->Params["MOD_CACHE"][$num]=$ligne;}	
	$freeweb->SaveParams();
}

function CacheDisableList(){
	$tpl=new templates();	
	$freeweb=new freeweb($_GET["servername"]);
	if(!isset($freeweb->Params["MOD_CACHE"]["CacheDisable"])){return;}
	$array=$freeweb->Params["MOD_CACHE"]["CacheDisable"];
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		
		<th>{urls}</th>
		<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	while (list ($num, $ligne) = each ($array) ){
		if($num==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","CacheDisableDelete('".base64_encode($num)."')");
		$html=$html."<tr class=$classtr>
		<td style='font-size:16px;'>$num</td>
		<td width=1%>$delete</td>
		</tr>
		";
	}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

