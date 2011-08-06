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

	if(isset($_POST["EnableZarafaTuning"])){Save();exit;}
	
	
page();


function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$users=new usersMenus();
	$EnableZarafaTuning=$sock->GET_INFO("EnableZarafaTuning");
	if(!is_numeric($EnableZarafaTuning)){$EnableZarafaTuning=0;}
	$ZarafTuningParameters=unserialize(base64_decode($sock->GET_INFO("ZarafaTuningParameters")));
	$zarafa_innodb_buffer_pool_size=$ZarafTuningParameters["zarafa_innodb_buffer_pool_size"];
	$zarafa_query_cache_size=$ZarafTuningParameters["zarafa_query_cache_size"];
	$zarafa_innodb_log_file_size=$ZarafTuningParameters["zarafa_innodb_log_file_size"];
	$zarafa_innodb_log_buffer_size=$ZarafTuningParameters["zarafa_innodb_log_buffer_size"];
	$zarafa_max_allowed_packet=$ZarafTuningParameters["zarafa_max_allowed_packet"];
	$zarafa_max_connections=$ZarafTuningParameters["zarafa_max_connections"];
	
	$memory=$users->MEM_TOTAL_INSTALLEE/1000;
	
	
	if(!is_numeric($zarafa_max_connections)){$zarafa_max_connections=500;}
	if(!is_numeric($zarafa_innodb_buffer_pool_size)){$zarafa_innodb_buffer_pool_size=round($memory/2);}
	if(!is_numeric($zarafa_innodb_log_file_size)){$zarafa_innodb_log_file_size=round($zarafa_innodb_buffer_pool_size*0.25);}
	if(!is_numeric($zarafa_innodb_log_buffer_size)){$zarafa_innodb_log_buffer_size=32;}
	if(!is_numeric($zarafa_max_allowed_packet)){$zarafa_max_allowed_packet=16;}
	if(!is_numeric($zarafa_query_cache_size)){$zarafa_query_cache_size=8;}
	
	
	
	
	
	
	$html="
	<div class=explain id='zarafa_mysql_tuning_text'>{zarafa_mysql_tuning_text}</div>
	
	<table style='width:100%' class=form>
	<tR>
		<td class=legend>{enable_tuning_mysql_server}:</td>
		<td>". Field_checkbox("EnableZarafaTuning", 1,$EnableZarafaTuning,"EnableZarafaTuningCheck()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{innodb_buffer_pool_size}:</td>
		<td style='font-size:14px'>". Field_text("zarafa_innodb_buffer_pool_size",$zarafa_innodb_buffer_pool_size,"font-size:14px;width:90px")."&nbsp;M</td>
		<td width=1%>". help_icon("{zarafa_innodb_buffer_pool_size}")."</td>
	</tr>
	<tr>
		<td class=legend>{query_cache_size}:</td>
		<td style='font-size:14px'>". Field_text("zarafa_query_cache_size",$zarafa_query_cache_size,"font-size:14px;width:90px")."&nbsp;M</td>
	</tr>	
	<tr>
		<td class=legend>{innodb_log_file_size}:</td>
		<td style='font-size:14px'>". Field_text("zarafa_innodb_log_file_size",$zarafa_innodb_log_file_size,"font-size:14px;width:90px")."&nbsp;M</td>
		<td width=1%>". help_icon("{zarafa_innodb_log_file_size}")."</td>
	</tr>	
	<tr>
		<td class=legend>{innodb_log_buffer_size}:</td>
		<td style='font-size:14px'>". Field_text("zarafa_innodb_log_buffer_size",$zarafa_innodb_log_buffer_size,"font-size:14px;width:90px")."&nbsp;M</td>
		<td width=1%>". help_icon("{zarafa_innodb_log_buffer_size}")."</td>
		
	</tr>	
	<tr>
		<td class=legend>{max_allowed_packet}:</td>
		<td style='font-size:14px'>". Field_text("zarafa_max_allowed_packet",$zarafa_max_allowed_packet,"font-size:14px;width:90px")."&nbsp;M</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{max_connections}:</td>
		<td style='font-size:14px'>". Field_text("zarafa_max_connections",$zarafa_max_connections,"font-size:14px;width:90px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=3 align=right><hr>". button("{apply}","ZarafaTunIngApply()")."</td>
	</tr>	
</table>
<script>
	function EnableZarafaTuningCheck(){
		document.getElementById('zarafa_innodb_buffer_pool_size').disabled=true;
		document.getElementById('zarafa_query_cache_size').disabled=true;
		document.getElementById('zarafa_innodb_log_file_size').disabled=true;
		document.getElementById('zarafa_innodb_log_buffer_size').disabled=true;
		document.getElementById('zarafa_max_allowed_packet').disabled=true;
		document.getElementById('zarafa_max_connections').disabled=true;
		if(document.getElementById('EnableZarafaTuning').checked){
			document.getElementById('zarafa_innodb_buffer_pool_size').disabled=false;
			document.getElementById('zarafa_query_cache_size').disabled=false;
			document.getElementById('zarafa_innodb_log_file_size').disabled=false;
			document.getElementById('zarafa_innodb_log_buffer_size').disabled=false;
			document.getElementById('zarafa_max_allowed_packet').disabled=false;
			document.getElementById('zarafa_max_connections').disabled=false;		
		
		}
	
	}
	var x_ZarafaTunIngApply= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_config_zarafa');
		}	
	
	function ZarafaTunIngApply(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableZarafaTuning').checked){
			XHR.appendData('EnableZarafaTuning',1);}else{XHR.appendData('EnableZarafaTuning',0);}
			XHR.appendData('zarafa_innodb_buffer_pool_size',document.getElementById('zarafa_innodb_buffer_pool_size').value);
			XHR.appendData('zarafa_query_cache_size',document.getElementById('zarafa_query_cache_size').value);
			XHR.appendData('zarafa_innodb_log_file_size',document.getElementById('zarafa_innodb_log_file_size').value);
			XHR.appendData('zarafa_innodb_log_buffer_size',document.getElementById('zarafa_innodb_log_buffer_size').value);
			XHR.appendData('zarafa_max_allowed_packet',document.getElementById('zarafa_max_allowed_packet').value);
			XHR.appendData('zarafa_max_connections',document.getElementById('zarafa_max_connections').value);
			AnimateDiv('zarafa_mysql_tuning_text');
			XHR.sendAndLoad('$page', 'POST',x_ZarafaTunIngApply);
	
	}
	
EnableZarafaTuningCheck();
</script>
	";
	
echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function Save(){
	$sock=new sockets();
	$ZarafTuningParametersSrcMD=md5(unserialize(base64_decode($sock->GET_INFO("ZarafaTuningParameters"))));
	$newparamas=md5(serialize($_POST));
	if($newparamas<>$ZarafTuningParametersSrcMD){
		$sock->SET_INFO("MysqlRemoveidbLogs", 1);
	}
	
	$sock->SET_INFO("EnableZarafaTuning", $_POST["EnableZarafaTuning"]);
	$sock->SaveConfigFile(base64_encode(serialize($_POST)), "ZarafaTuningParameters");
	$sock->getFrameWork("services.php?restart-mysql=yes");
	}
	
	
	
	