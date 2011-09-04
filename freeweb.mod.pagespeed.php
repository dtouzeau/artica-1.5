<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
	if(isset($_GET["countries-list"])){countries_list();exit;}
	if(isset($_POST["ModPagespeedFileCacheSizeKb"])){save();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_MOD_PAGESPEED}");
	$html="YahooWin3('600','$page?popup=yes&servername={$_GET["servername"]}','{$_GET["servername"]}::$title');";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$free=new freeweb($_GET["servername"]);
	$CONF=$free->Params["PageSpeedParams"];
	
	$ModPagespeedFileCacheSizeKb=$CONF["ModPagespeedFileCacheSizeKb"];
	$ModPagespeedFileCacheCleanIntervalMs=$CONF["ModPagespeedFileCacheCleanIntervalMs"];
	$ModPagespeedLRUCacheKbPerProcess=$CONF["ModPagespeedLRUCacheKbPerProcess"];
	$ModPagespeedLRUCacheByteLimit=$CONF["ModPagespeedLRUCacheByteLimit"];
	
	if(!is_numeric($ModPagespeedFileCacheSizeKb)){$ModPagespeedFileCacheSizeKb=102400;}
	if(!is_numeric($ModPagespeedFileCacheCleanIntervalMs)){$ModPagespeedFileCacheCleanIntervalMs=3600000;}
	if(!is_numeric($ModPagespeedLRUCacheKbPerProcess)){$ModPagespeedLRUCacheKbPerProcess=1024;}
	if(!is_numeric($ModPagespeedLRUCacheByteLimit)){$ModPagespeedLRUCacheByteLimit=16384;}
	

	$html="
	<div id='modpagespeeddiv'>
	<div class=explain style='margin-top:10px'>{mod_pagespeed_about}</div>
	<table style='width:100%' class=form>
	<tbody>
	<tr>
		<td class=legend>{ModPagespeedFileCacheSizeKb}</td>
		<td>". Field_text("ModPagespeedFileCacheSizeKb", $ModPagespeedFileCacheSizeKb ,"font-size:14px;padding:3px;width:90px")."</td>
	</tr>
	<tr>
		<td class=legend>{ModPagespeedFileCacheCleanIntervalMs}</td>
		<td>". Field_text("ModPagespeedFileCacheCleanIntervalMs", $ModPagespeedFileCacheCleanIntervalMs ,"font-size:14px;padding:3px;width:90px")."</td>
	</tr>
	<tr>
		<td class=legend>{ModPagespeedLRUCacheKbPerProcess}</td>
		<td>". Field_text("ModPagespeedLRUCacheKbPerProcess", $ModPagespeedLRUCacheKbPerProcess ,"font-size:14px;padding:3px;width:90px")."</td>
	</tr>
	<tr>
		<td class=legend>{ModPagespeedLRUCacheByteLimit}</td>
		<td>". Field_text("ModPagespeedLRUCacheByteLimit", $ModPagespeedLRUCacheByteLimit ,"font-size:14px;padding:3px;width:90px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","ModPageSpeedSave()")."</td>
	</tr>		
	</tbody>
	</table>	
	</div>
	<script>
		var x_ModPageSpeedSave=function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);}	
			Loadjs('$page?servername={$_GET["servername"]}');
		}	
	
	
		function ModPageSpeedSave(geo){
			var XHR = new XHRConnection();
			
			XHR.appendData('servername','{$_GET["servername"]}');			
			XHR.appendData('ModPagespeedFileCacheSizeKb',document.getElementById('ModPagespeedFileCacheSizeKb').value);
			XHR.appendData('ModPagespeedFileCacheCleanIntervalMs',document.getElementById('ModPagespeedFileCacheCleanIntervalMs').value);
			XHR.appendData('ModPagespeedLRUCacheKbPerProcess',document.getElementById('ModPagespeedLRUCacheKbPerProcess').value);
			XHR.appendData('ModPagespeedLRUCacheByteLimit',document.getElementById('ModPagespeedLRUCacheByteLimit').value);
			AnimateDiv('modpagespeeddiv');
			XHR.sendAndLoad('$page', 'POST',x_ModPageSpeedSave);
		}
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$free=new freeweb($_POST["servername"]);
	$CONF=$free->Params["PageSpeedParams"];
	while (list ($num, $ligne) = each ($_POST) ){
		$free->Params["PageSpeedParams"][$num]=$ligne;
	}

	$free->SaveParams();
}
