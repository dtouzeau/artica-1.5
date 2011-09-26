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
if(isset($_GET["with-tabs"])){echo withtabs();exit;}
if(isset($_GET["PostFixAddServerCache"])){PostFixAddServerCache();exit;}
if(isset($_POST["PostFixAddServerCacheSave"])){PostFixAddServerCacheSave();exit;}
if(isset($_POST["PostFixDeleteServerCache"])){PostFixDeleteServerCache();exit;}
if(isset($_GET["CacheReloadList"])){echo PostFixServerCacheList();exit;}
if(isset($_POST["smtp_connection_cache_on_demand"])){PostFixSaveServerCacheSettings();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

function withtabs(){
if($_GET["hostname"]==null){$_GET["hostname"]="master";}	
$id=time();
$page=CurrentPageName();
$html="<div id='$id'></div>
<script>
	Loadjs('js/postfix-cache.js');
	LoadAjax('$id','$page?popup-index=yes&hostname={$_GET["hostname"]}');
</script>
";
echo $html;
}

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
if($_GET["hostname"]==null){$_GET["hostname"]="master";}
$title=$tpl->_ENGINE_parse_body("{smtp_connection_cache_destinations}::{$_GET["hostname"]}");
	
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
	YahooWin2('650','$page?popup-index=yes&hostname={$_GET["hostname"]}','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}
function popup_index(){
$tpl=new templates();
$page=CurrentPageName();
$add_server_domain=$tpl->_ENGINE_parse_body("{add_server_domain}");	
$main=new maincf_multi($_GET["hostname"]);
$smtp_connection_cache_on_demand=$main->GET("smtp_connection_cache_on_demand");
$smtp_connection_cache_time_limit=$main->GET("smtp_connection_cache_time_limit");
$smtp_connection_reuse_time_limit=$main->GET("smtp_connection_reuse_time_limit");
$connection_cache_ttl_limit=$main->GET("connection_cache_ttl_limit");
$connection_cache_status_update_time=$main->GET("connection_cache_status_update_time");


if(!is_numeric($smtp_connection_cache_on_demand)){$smtp_connection_cache_on_demand=1;}
if($smtp_connection_cache_time_limit==null){$smtp_connection_cache_time_limit="2s";}
if($smtp_connection_reuse_time_limit==null){$smtp_connection_reuse_time_limit="300s";}
if($connection_cache_ttl_limit==null){$connection_cache_ttl_limit="2s";}
if($connection_cache_status_update_time==null){$connection_cache_status_update_time="600s";}



$html="
<div id='smtp_connection_cache_on_demand_div'></div>
<table style='width:99%' class=form>
<tbody>
	<tr>
		<td class=legend nowrap><strong>{smtp_connection_cache_on_demand}&nbsp;:</strong></td>
		<td align='left'>" . Field_checkbox('smtp_connection_cache_on_demand',1,$smtp_connection_cache_on_demand) ."</td>
		<td width=1%>". help_icon("{smtp_connection_cache_destinations_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{smtp_connection_cache_time_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_connection_cache_time_limit',$smtp_connection_cache_time_limit,'width:50px;font-size:14px',null,null,'') ."
		<td width=1%>". help_icon("{smtp_connection_cache_time_limit_text}")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{smtp_connection_reuse_time_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_connection_reuse_time_limit',$smtp_connection_reuse_time_limit,'width:50px;font-size:14px',null,null,'') ."
		<td width=1%>". help_icon("{smtp_connection_reuse_time_limit_text}")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{connection_cache_ttl_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('connection_cache_ttl_limit',$connection_cache_ttl_limit,'width:50px;font-size:14px',null,null,'') ."
		<td width=1%>". help_icon("{connection_cache_ttl_limit_text}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap valign='top' nowrap><strong>{connection_cache_status_update_time}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('connection_cache_status_update_time',$connection_cache_status_update_time,'width:50px;font-size:14px',null,null) ."
		<td width=1%>". help_icon("{connection_cache_status_update_time_text}")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap colspan='3' align='right'><hr>
		". button("{apply}","smtp_connection_cache_on_demand_save()")."
	</td>
		
	</tr>			
	</tbody>
</table>				

<table style='width:99%' class=form>
<tbody>
	<tr>
	
	
		<td class=legend nowrap><strong>{smtp_connection_cache_destinations_field}&nbsp;:</strong></td>
		<td align='left'><input type='button' value='{add_server_domain}&nbsp;&raquo;' OnClick=\"javascript:PostFixAddServerCache();\">
	</tr>
</tbody>
</table>	


</td>
</tr>
</table><div id='ServerCacheList'>"  .PostFixServerCacheList() . "</div>

<script>

	var x_smtp_connection_cache_on_demand_save=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		document.getElementById('smtp_connection_cache_on_demand_div').innerHTML='';
		}

function smtp_connection_cache_on_demand_save(){
	var XHR = new XHRConnection();	
	if(document.getElementById('smtp_connection_cache_on_demand').checked){XHR.appendData('smtp_connection_cache_on_demand','1');}else{XHR.appendData('smtp_connection_cache_on_demand','0');}
	XHR.appendData('smtp_connection_cache_time_limit',document.getElementById('smtp_connection_cache_time_limit').value);
	XHR.appendData('smtp_connection_reuse_time_limit',document.getElementById('smtp_connection_reuse_time_limit').value);
	XHR.appendData('connection_cache_ttl_limit',document.getElementById('connection_cache_ttl_limit').value);
	XHR.appendData('connection_cache_status_update_time',document.getElementById('connection_cache_status_update_time').value);
	XHR.appendData('hostname','{$_GET["hostname"]}');
	AnimateDiv('smtp_connection_cache_on_demand_div');
	XHR.sendAndLoad('$page', 'POST',x_smtp_connection_cache_on_demand_save);	
}
function PostFixAddServerCache(){
	YahooWin3(550,'$page?PostFixAddServerCache=yes&hostname={$_GET["hostname"]}','$add_server_domain');
	}
	
function CacheReloadList(){
	LoadAjax('ServerCacheList','$page?CacheReloadList=yes&hostname={$_GET["hostname"]}');
	}	
	CacheReloadList();
</script>


";	


$tpl=new Templates();

echo $tpl->_ENGINE_parse_body($html);
}


function PostFixServerCacheList(){
	$tpl=new templates();
	$page=CurrentPageName();
	$main=new maincf_multi($_GET["hostname"]);
	$smtp_connection_cache_destinations=unserialize(base64_decode($main->GET_BIGDATA("smtp_connection_cache_destinations")));
	$add=imgtootltip("plus-24.png","{add_server_domain}","PostFixAddServerCache()");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{hostname}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	while (list ($num, $ligne) = each ($smtp_connection_cache_destinations) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html . "<tr class=$classtr>
			<td colspan=2><strong style='font-size:14px'>$num</strong></td>
			<td width=1%>" . imgtootltip('delete-32.png','{delete}',"PostFixDeleteServerCache('$num')") . "</td>
			</tr>
			";
		
	}
	
	return $tpl->_ENGINE_parse_body($html . "</tbody></table>
	<script>
	var x_PostFixDeleteServerCache=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		CacheReloadList();
		}	
	
		function PostFixDeleteServerCache(value){
			var XHR = new XHRConnection();	
			XHR.appendData('PostFixDeleteServerCache',value);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			AnimateDiv('ServerCacheList');
			XHR.sendAndLoad('$page', 'POST',x_PostFixDeleteServerCache);				
		
		}
	
	</script>
	");
	
	
}

function PostFixAddServerCache(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="<div id='PostFixAddServerCacheDiv'></div>
	<input type='hidden' name='PostFixAddServerCacheSave' value='yes'>
	<table style='width:100%' class=form>
	<tr>
	<td class=legend nowrap><strong>{domain}:</strong></td>
	<td>" . Field_text('domain',$domainName,"font-size:14px;padding:3px;width:220px") . "</td>
	</tr>
	<td class=legend nowrap nowrap><strong>{or} {relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$relay_address,"font-size:14px;padding:3px;width:220px") . "</td>	
	</tr>
	</tr>
	<td class=legend nowrap nowrap><strong>{smtp_port}:</strong></td>
	<td>" . Field_text('relay_port',25,"font-size:14px;padding:3px;width:40px") . "</td>	
	</tr>	
	<tr>
	<td class=legend>{MX_lookups}</td>	
	<td>" . Field_checkbox('MX_lookups','1',0)."</td>
	</tr>

	<tr>
	<td colspan=2 align='right'><hr>". button("{add}","PostFixSaveServerCache()")."</td>
	</tr>		
	<tr>
	<td align='left' colspan=2><strong{MX_lookups}</strong><br><div class=explain>{MX_lookups_text}</div></td>
	</tr>			
		
	</table>
	<script>
	
	var x_PostFixSaveServerCache=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		document.getElementById('PostFixAddServerCacheDiv').innerHTML='';
		CacheReloadList();
		}	
	
		function PostFixSaveServerCache(){
		var XHR = new XHRConnection();	
			if(document.getElementById('MX_lookups').checked){XHR.appendData('MX_lookups','yes');}else{XHR.appendData('MX_lookups','no');}
			XHR.appendData('domain',document.getElementById('domain').value);
			XHR.appendData('smtp_connection_reuse_time_limit',document.getElementById('smtp_connection_reuse_time_limit').value);
			XHR.appendData('relay_address',document.getElementById('relay_address').value);
			XHR.appendData('relay_port',document.getElementById('relay_port').value);
			XHR.appendData('PostFixAddServerCacheSave','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			AnimateDiv('PostFixAddServerCacheDiv');
			XHR.sendAndLoad('$page', 'POST',x_PostFixSaveServerCache);				
		
		}
	</script>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function PostFixAddServerCacheSave(){
	$tool=new DomainsTools();
	$tpl=new templates();
	$relay_address=$_POST["relay_address"];
	$relay_port=$_POST["relay_port"];
	$MX_lookups=$_GET["MX_lookups"];
	$domain=$_POST["domain"];
	
	if($domain<>null && $relay_address<>null){
		echo $tpl->javascript_parse_text('{error_give_server_or_domain}');exit();
	}
	
	
	if($relay_address<>null){
		$line=$tool->transport_maps_implode($relay_address,$relay_port,null,$MX_lookups);
		$line=str_replace('smtp:','',$line);
		
	}else{$line=$domain;}
	
	$main=new maincf_multi($_POST["hostname"]);
	$smtp_connection_cache_destinations=unserialize(base64_decode($main->GET_BIGDATA("smtp_connection_cache_destinations")));
	$smtp_connection_cache_destinations[$line]="OK";
	$smtp_connection_cache_destinations_new=base64_encode(serialize($smtp_connection_cache_destinations));
	if(!$main->SET_BIGDATA("smtp_connection_cache_destinations", addslashes($smtp_connection_cache_destinations_new))){
		echo $main->$q->mysql_error;
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-others-values=yes&hostname={$_POST["hostname"]}");		
}

function PostFixDeleteServerCache(){
	$main=new maincf_multi($_POST["hostname"]);
	$smtp_connection_cache_destinations=unserialize(base64_decode($main->GET_BIGDATA("smtp_connection_cache_destinations")));	
	unset($smtp_connection_cache_destinations[$_POST["PostFixDeleteServerCache"]]);
	$smtp_connection_cache_destinations_new=base64_encode(serialize($smtp_connection_cache_destinations));
	$main->SET_BIGDATA("smtp_connection_cache_destinations", addslashes($smtp_connection_cache_destinations_new));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-others-values=yes&hostname={$_POST["hostname"]}");	
}
function PostFixSaveServerCacheSettings(){
	$main=new maincf_multi($_POST["hostname"]);
	$main->SET_VALUE("smtp_connection_cache_on_demand", $_POST["smtp_connection_cache_on_demand"]);
	$main->SET_VALUE("smtp_connection_cache_time_limit", $_POST["smtp_connection_cache_time_limit"]);
	$main->SET_VALUE("smtp_connection_reuse_time_limit", $_POST["smtp_connection_reuse_time_limit"]);
	$main->SET_VALUE("connection_cache_ttl_limit", $_POST["connection_cache_ttl_limit"]);
	$main->SET_VALUE("connection_cache_status_update_time", $_POST["connection_cache_status_update_time"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-others-values=yes&hostname={$_POST["hostname"]}");	
}


?>