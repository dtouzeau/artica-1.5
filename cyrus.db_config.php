<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.cron.inc');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["EnableCyrusDBConfig"])){SAVE();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="{database_configuration}";
	$html="YahooWin2(650,'$page?popup=yes','$title')";
	echo $html;
}

function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->GET_INFO("CyrusDBConfig")));
	$EnableCyrusDBConfig=$sock->GET_INFO("EnableCyrusDBConfig");
	
	if($datas["set_cachesize"]==null){$datas["set_cachesize"]="2 524288000 1";}
	if(!is_numeric($datas["set_lg_regionmax"])){$datas["set_lg_regionmax"]="1048576";}
	if(!is_numeric($datas["set_lg_bsize"])){$datas["set_lg_bsize"]="2097152";}
	if(!is_numeric($datas["set_lg_max"])){$datas["set_lg_max"]="4194304";}
	
	
	$html="
	<div id='CYRUS_DB_CONFIG'>
	<table style='width:100%' class=form>
	<tr>
		<td valign='top' class=legend>{enable}:</td>
		<td style='font-size:14px'>". Field_checkbox("EnableCyrusDBConfig",1,$EnableCyrusDBConfig,"ENABLE_DB_CONFIG_CHECK()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{set_cachesize}:</td>
		<td style='font-size:14px'>". Field_text("set_cachesize",$datas["set_cachesize"],"width:220px;font-size:14px")."&nbsp;Bytes</td>
		<td>". help_icon("{set_cachesize_text}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{set_lg_regionmax}:</td>
		<td style='font-size:14px'>". Field_text("set_lg_regionmax",$datas["set_lg_regionmax"],"width:90px;font-size:14px")."&nbsp;Bytes</td>
		<td>". help_icon("{set_lg_regionmax_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{set_lg_bsize}:</td>
		<td style='font-size:14px'>". Field_text("set_lg_bsize",$datas["set_lg_bsize"],"width:90px;font-size:14px")."&nbsp;Bytes</td>
		<td>". help_icon("{set_lg_bsize_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{set_lg_max}:</td>
		<td style='font-size:14px'>". Field_text("set_lg_max",$datas["set_lg_max"],"width:90px;font-size:14px")."&nbsp;Bytes</td>
		<td>". help_icon("{set_lg_max_text}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveDBCOnfig()")."</td>
	</tr>
	
	</table>
	</div>
	<script>
	function ENABLE_DB_CONFIG_CHECK(){
		document.getElementById('set_cachesize').disabled=true;
		document.getElementById('set_lg_regionmax').disabled=true;
		document.getElementById('set_lg_bsize').disabled=true;
		document.getElementById('set_lg_max').disabled=true;
		if(!document.getElementById('EnableCyrusDBConfig').checked){return;}
		document.getElementById('set_cachesize').disabled=false;
		document.getElementById('set_lg_regionmax').disabled=false;
		document.getElementById('set_lg_bsize').disabled=false;
		document.getElementById('set_lg_max').disabled=false;		
	
	}
	
	
	var x_SaveDBCOnfig=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			YahooWin2Hide();			
		}	
	
	
		function SaveDBCOnfig(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableCyrusDBConfig').checked){XHR.appendData('EnableCyrusDBConfig',1);}else{XHR.appendData('EnableCyrusDBConfig',0);}
			XHR.appendData('set_cachesize',document.getElementById('set_cachesize').value);
			XHR.appendData('set_lg_regionmax',document.getElementById('set_lg_regionmax').value);
			XHR.appendData('set_lg_bsize',document.getElementById('set_lg_bsize').value);
			XHR.appendData('set_lg_max',document.getElementById('set_lg_max').value);
			document.getElementById('CYRUS_DB_CONFIG').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveDBCOnfig);
		}	
	ENABLE_DB_CONFIG_CHECK();
	</script>	
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("EnableCyrusDBConfig",$_GET["EnableCyrusDBConfig"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"CyrusDBConfig");
	$sock->getFrameWork("cmd.php?cyrus-db-config=yes");
	
}
