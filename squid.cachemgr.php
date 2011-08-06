<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.squid.inc');


	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}
	if(isset($_GET["popup"])){popup();exit;}	
	if(isset($_GET["cache_mgr_user"])){SaveCacheManagerParams();exit;}
	if(isset($_GET["cachemgr_left_menu"])){cachemgr_left_menu_save();exit;}
	
	
	
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{cachemgr}");
	$html="YahooWin4('450','$page?popup=yes','$title');";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$cache_mgr_user=$sock->GET_INFO("cache_mgr_user");
	$cachemgr_passwd=$sock->GET_INFO("cachemgr_passwd");
	$cachemgr_left_menu=$sock->GET_INFO("cachemgr_left_menu");
	$sql="SELECT servername,useSSL FROM freeweb WHERE `groupware`='cachemgr' LIMIT 0,1";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["servername"]<>null){
		$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
		$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");
		if(!is_numeric($FreeWebListenPort)){$FreeWebListenPort=80;}
		if(!is_numeric($FreeWebListenSSLPort)){$FreeWebListenSSLPort=443;}
		$prefix="http";
		if($ligne["useSSL"]==1){$FreeWebListenPort=$FreeWebListenSSLPort;$prefix="https";}
		$link="$prefix://{$ligne["servername"]}:$FreeWebListenPort";
		$linkjs="javascript:s_PopUpFull('$link',800,800,'Squid Cache Manager')";
		$link="<a href=\"javascript:blur();\" OnClick=\"$linkjs\" style='font-size:14px;text-decoration:underline'>$link</a>";
		$edit_www="<a href=\"javascript:blur();\" OnClick=\"Loadjs('freeweb.edit.php?hostname={$ligne["servername"]}');\" style='font-size:14px;text-decoration:underline'>{edit}</a>";
	}
	
	
	$html="
	<div class=explain>{cachemgr_text}</div><div style='text-align:right;margin-bottom:5px'><i>$link</i></div>
	<div id='cachemgr-div'>
	<div style='font-size:16px'>{cachemgr}:: {authentication}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{add_to_left_menu}:</td>
		<td>". Field_checkbox("cachemgr_left_menu",1,$cachemgr_left_menu,"cachemgr_left_menu_save();")."</td>
	</tr>	
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text("cache_mgr_user","$cache_mgr_user","font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("cachemgr_passwd","$cachemgr_passwd","font-size:14px;padding:3px")."</td>
	</tr>	
	</table>
	<br>
	
	<div style='font-size:16px'>{cachemgr}:: {website_name}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{website}:</td>
		<td>". Field_text("website",$ligne["servername"],"font-size:14px;padding:3px;width:220px")."&nbsp;$edit_www</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{apply}","SaveCacheManagerParams()")."</td>
	</tr>
	</table>
	<br>	
	</div>
	<script>

	var x_SaveCacheManagerParams= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		
	}

	var x_cachemgr_left_menu_save= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		CacheOff();
	}	
	
	function cachemgr_left_menu_save(){
		var XHR = new XHRConnection();
		if(document.getElementById('cachemgr_left_menu').checked){
			XHR.appendData('cachemgr_left_menu',1)
		}else{
			XHR.appendData('cachemgr_left_menu',0)
		}
		XHR.sendAndLoad('$page', 'GET',x_cachemgr_left_menu_save);
		
	}
	
	
	function SaveCacheManagerParams(key){
		var XHR = new XHRConnection();
		var a=document.getElementById('cache_mgr_user').value;
		if(a.length<3){return;}
		a=document.getElementById('cachemgr_passwd').value;
		if(a.length<3){return;}		
		a=document.getElementById('website').value;
		if(a.length<3){return;}			
		
		XHR.appendData('cache_mgr_user',document.getElementById('cache_mgr_user').value);	
		XHR.appendData('cachemgr_passwd',document.getElementById('cachemgr_passwd').value);
		XHR.appendData('website',document.getElementById('website').value);
		document.getElementById('cachemgr-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveCacheManagerParams);
		}	

	</script>		
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
}

function cachemgr_left_menu_save(){
	$sock=new sockets();
	$sock->SET_INFO("cachemgr_left_menu",$_GET["cachemgr_left_menu"]);	
}

function SaveCacheManagerParams(){
	$sock=new sockets();
	$sock->SET_INFO("cache_mgr_user",$_GET["cache_mgr_user"]);
	$sock->SET_INFO("cachemgr_passwd",$_GET["cachemgr_passwd"]);
	
	$sql="INSERT IGNORE INTO freeweb (servername,groupware,useFTP,useMysql) VALUES('{$_GET["website"]}','cachemgr',0,0)";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	$sock=new sockets();
	$sock->GET_INFO("cmd.php?squid-rebuild=yes");
	$sock->GET_INFO("cmd.php?freeweb-website=yes&servername={$_GET["website"]}");

}
	

