<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");
$user=new usersMenus();
if($user->AsPostfixAdministrator==false){header('location:logon.php');}

if(isset($_GET["PostFixAddServerCache"])){PostFixAddServerCache();exit;}
if(isset($_GET["PostFixAddServerCacheSave"])){PostFixAddServerCacheSave();exit;}
if(isset($_GET["PostFixDeleteServerCache"])){PostFixDeleteServerCache();exit;}
if(isset($_GET["CacheReloadList"])){echo PostFixServerCacheList();exit;}
if(isset($_GET["smtp_connection_cache_on_demand"])){PostFixSaveServerCacheSettings();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{smtp_connection_cache_destinations}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}


$js=file_get_contents("js/postfix-cache.js");	
	
$html="
$js

function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}
function popup_index(){
$main=new main_cf();
$html="
<H1>{smtp_connection_cache_destinations}</H1>
<table style='width:600px' align=center>
<tr>
<td valign='top'>
". RoundedLightWhite("<div style='text-align:justify;font-size:12px;height:200px;overflow:auto;width:99%'>{smtp_connection_cache_destinations_text}</div>")."
<br>
". RoundedLightWhite("
<form name='FFMA'>
<table style='width:500px'>
	<tr>
		<td class=legend nowrap><strong>{smtp_connection_cache_on_demand}&nbsp;:</strong></td>
		<td align='left'>" . Field_yesno_checkbox_img('smtp_connection_cache_on_demand',$main->main_array["smtp_connection_cache_on_demand"],'{enable_disable}') ."</td>
	</tr>
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{smtp_connection_cache_time_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_connection_cache_time_limit',$main->main_array["smtp_connection_cache_time_limit"],'width:20%',null,null,'{smtp_connection_cache_time_limit_text}') ."
	</tr>	
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{smtp_connection_reuse_time_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_connection_reuse_time_limit',$main->main_array["smtp_connection_reuse_time_limit"],'width:20%',null,null,'{smtp_connection_reuse_time_limit_text}') ."
	</tr>	
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{connection_cache_ttl_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('connection_cache_ttl_limit',$main->main_array["connection_cache_ttl_limit"],'width:20%',null,null,'{connection_cache_ttl_limit_text}') ."
	</tr>
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{connection_cache_status_update_time}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('connection_cache_status_update_time',$main->main_array["connection_cache_status_update_time"],'width:20%',null,null,'{connection_cache_status_update_time_text}') ."
	</tr>	
	<tr>
		<td class=legend nowrap colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:PostFixSaveServerCacheSettings();\"></td>
		
	</tr>			
					
		
</form>	")."
	
	<tr>
	
	
		<td class=legend nowrap><strong>{smtp_connection_cache_destinations_field}&nbsp;:</strong></td>
		<td align='left'><input type='button' value='{add_server_domain}&nbsp;&raquo;' OnClick=\"javascript:PostFixAddServerCache();\">
	</tr>
</table>	


</td>
</tr>
</table><div id='ServerCacheList'>"  .PostFixServerCacheList() . "</div>";	


$tpl=new Templates();

echo $tpl->_ENGINE_parse_body($html);
}


function PostFixServerCacheList(){
	$ldap=new clladp();
	$array=$ldap->hash_get_smtp_connection_cache_destinations();
	if(!is_array($array)){return null;}
	
	$html="<H4>{smtp_connection_cache_destinations_field}&nbsp;{list}</H4>
	<center>
	<table style='width:300px'>";
	while (list ($num, $ligne) = each ($array) ){
		$html=$html . "<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td><strong>$ligne</strong></td>
			<td width=1%>" . imgtootltip('x.gif','{delete}',"PostFixDeleteServerCache('$ligne')") . "</td>
			</tr>
			";
		
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table>");
	
	
}

function PostFixAddServerCache(){
	
	$page=CurrentPageName();
	
	$html="<div style='padding:20px'>
	<H3>{add_server_domain}</H3>
	<p>&nbsp;</p>
	<form name='FFM3Cache'>
	<input type='hidden' name='PostFixAddServerCacheSave' value='yes'>
	<table style='width:100%'>
	<tr>
	<td class=legend nowrap><strong>{domain}:</strong></td>
	<td>" . Field_text('domain',$domainName) . "</td>
	</tr>
	<td class=legend nowrap nowrap><strong>{or} {relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$relay_address) . "</td>	
	</tr>
	</tr>
	<td class=legend nowrap nowrap><strong>{smtp_port}:</strong></td>
	<td>" . Field_text('relay_port',$smtp_port) . "</td>	
	</tr>	
	<tr>
	<td class=legend nowrap nowrap>" . Field_yesno_checkbox_img('MX_lookups','yes','{enable_disable}')."</td>
	<td>{MX_lookups}</td>	
	</tr>
	$sasl
	<tr>
	<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"PostFixSaveServerCache();\"></td>
	</tr>		
	<tr>
	<td align='left' class=caption colspan=2><strong>{MX_lookups}</strong><br>{MX_lookups_text}</td>
	</tr>			
		
	</table>
	</FORM>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function PostFixAddServerCacheSave(){
	$tool=new DomainsTools();
	$tpl=new templates();
	$relay_address=$_GET["relay_address"];
	$relay_port=$_GET["relay_port"];
	$MX_lookups=$_GET["MX_lookups"];
	$domain=$_GET["domain"];
	
	if($domain<>null && $relay_address<>null){
		echo $tpl->_ENGINE_parse_body('{error_give_server_or_domain}');exit;
	}
	
	
	if($relay_address<>null){
		$line=$tool->transport_maps_implode($relay_address,$relay_port,null,$MX_lookups);
		$line=str_replace('smtp:','',$line);
		
	}else{$line=$domain;}
		$ldap=new clladp();
		if(!$ldap->ExistsDN("cn=smtp_connection_cache_destinations,cn=artica,$ldap->suffix")){
			$dn="cn=smtp_connection_cache_destinations,cn=artica,$ldap->suffix";
			$upd["cn"][0]='smtp_connection_cache_destinations';
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
			unset($upd);
		}
		
		
		$dn="cn=$line,cn=smtp_connection_cache_destinations,cn=artica,$ldap->suffix";
		$upd["cn"][0]=$line;
		$upd['objectClass'][0]='PostFixSmtpConnectionCacheDestinations';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
}

function PostFixDeleteServerCache(){
	$ldap=new clladp();
	$dn="cn={$_GET["PostFixDeleteServerCache"]},cn=smtp_connection_cache_destinations,cn=artica,$ldap->suffix";
	if(!$ldap->ldap_delete($dn,false)){echo $ldap->ldap_last_error;}
}
function PostFixSaveServerCacheSettings(){
	$main=new main_cf();
while (list ($key, $datas) = each ($_GET) ){
	$main->main_array[$key]=$datas;
	}
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}


?>