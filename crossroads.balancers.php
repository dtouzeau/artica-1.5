<?php
$GLOBALS["ICON_FAMILY"]="NETWORK";
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.computers.inc');
	
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["xr-events-search"])){events_search();exit;}
	
	if(isset($_GET["xr-toolbox"])){xr_toolbox();exit;}
	if(isset($_GET["xr-list"])){xr_list_form();exit;}
	if(isset($_GET["xr-list-search"])){xr_list_search();exit;}
	
	if(isset($_GET["add-load-balance-js"])){load_balance_form_js();exit;}
	if(isset($_GET["load-balance-form"])){load_balance_form();exit;}
	if(isset($_GET["load-balance-form-parameters"])){load_balance_form_parameters();exit;}
	if(isset($_GET["load-balance-form-backend"])){load_balance_form_backend();exit;}
	
	if(isset($_GET["load-balance-form-status"])){load_balance_form_status();exit;}
	if(isset($_GET["load-balance-form-status-service"])){load_balance_form_status_service();exit;}
	if(isset($_GET["load-balance-form-status-service-stats"])){load_balance_form_status_service_stats();exit;}
	
	if(isset($_GET["start-instance-js"])){start_instance_js();exit;}
	if(isset($_GET["stop-instance-js"])){stop_instance_js();exit;}
	if(isset($_GET["restart-instance-js"])){restart_instance_js();exit;}
	if(isset($_GET["reload-all-instances-js"])){reloadall_instance_js();exit;}
	if(isset($_GET["reconfigure-all-instances-js"])){reconfigure_all_instance_js();exit;}
	
	
	
	if(isset($_POST["start-instance"])){start_instance();exit;}
	if(isset($_POST["stop-instance"])){stop_instance();exit;}
	if(isset($_POST["restart-instance"])){restart_instance();exit;}
	if(isset($_POST["delete-instance"])){delete_instance();exit;}
	if(isset($_POST["reload-all-instances"])){reloadall_instances();exit;}
	if(isset($_POST["reconfigure-all-instances"])){reconfigure_all_instances();exit;}
	
	
	
	
	if(isset($_GET["backend-list-search"])){load_balance_form_backend_search();exit;}
	if(isset($_POST["backend-add"])){load_balance_form_backend_add();exit;}
	if(isset($_POST["backend-del"])){load_balance_form_backend_del();exit;}
	if(isset($_GET["backend-server-edit-js"])){load_balance_form_backend_js();exit;}
	if(isset($_GET["backend-server-edit"])){load_balance_form_backend_edit();exit;}
	if(isset($_POST["backendmd"])){load_balance_form_backend_save();exit;}
	if(isset($_POST["find-newport"])){find_newport();exit;}
	if(isset($_POST["listen_ip"])){load_balance_form_save();exit;}
	
page();


function start_instance_js(){
	$page=CurrentPageName();
	$ID=$_GET["ID"];
	$html="
	
	var x_StartInstanceBalance= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('main_config_crossroads_bler')){RefreshTab('main_config_crossroads_bler');}
			if(document.getElementById('main_balance_form')){RefreshTab('main_balance_form');}
		}		
	
		function StartInstanceBalance(){
			if(document.getElementById('cross-status-{$_GET["ID"]}')){AnimateDiv('cross-status-{$_GET["ID"]}');}		
			var XHR = new XHRConnection();
			XHR.appendData('start-instance','{$_GET["ID"]}');
			XHR.sendAndLoad('$page', 'POST',x_StartInstanceBalance);
			
		}	
	StartInstanceBalance();
	
	";
	echo $html;
}

function reloadall_instance_js(){
	$page=CurrentPageName();
	
	$html="
	
	var x_StartInstanceBalance2= function (obj) {
			var results=obj.responseText;
			if(document.getElementById('main_config_crossroads_bler')){RefreshTab('main_config_crossroads_bler');}
			if(document.getElementById('main_balance_form')){RefreshTab('main_balance_form');}
		}		
	
		function ReloadAllInstanceBalance(){	
			var XHR = new XHRConnection();
			XHR.appendData('reload-all-instances','yes');
			AnimateDiv('xr-list');
			XHR.sendAndLoad('$page', 'POST',x_StartInstanceBalance2);
			
		}	
	ReloadAllInstanceBalance();
	
	";
	echo $html;	
	
}

function reconfigure_all_instance_js(){
	$page=CurrentPageName();
	
	$html="
	
	var x_StartInstanceBalance= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('main_config_crossroads_bler')){RefreshTab('main_config_crossroads_bler');}
			if(document.getElementById('main_balance_form')){RefreshTab('main_balance_form');}
		}		
	
		function ReconfigureAllInstanceBalance(){	
			var XHR = new XHRConnection();
			XHR.appendData('reconfigure-all-instances','yes');
			AnimateDiv('xr-list');
			XHR.sendAndLoad('$page', 'POST',x_StartInstanceBalance);
			
		}	
	ReconfigureAllInstanceBalance();
	
	";
	echo $html;		
	
}

function restart_instance_js(){
	$page=CurrentPageName();
	$ID=$_GET["ID"];
	$html="
	
	var x_StartInstanceBalance= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('main_config_crossroads_bler')){RefreshTab('main_config_crossroads_bler');}
			if(document.getElementById('main_balance_form')){RefreshTab('main_balance_form');}
		}		
	
		function ReStartInstanceBalance(){
			if(document.getElementById('cross-status-{$_GET["ID"]}')){AnimateDiv('cross-status-{$_GET["ID"]}');}		
			var XHR = new XHRConnection();
			XHR.appendData('restart-instance','{$_GET["ID"]}');
			XHR.sendAndLoad('$page', 'POST',x_StartInstanceBalance);
			
		}	
	
	ReStartInstanceBalance();
	";
	echo $html;	
}

function stop_instance_js(){
	$page=CurrentPageName();
	$ID=$_GET["ID"];
	$html="
	
	var x_StartInstanceBalance= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('main_config_crossroads_bler')){RefreshTab('main_config_crossroads_bler');}
			if(document.getElementById('main_balance_form')){RefreshTab('main_balance_form');}
		}		
	
		function StopInstanceBalance(){
			if(document.getElementById('cross-status-{$_GET["ID"]}')){AnimateDiv('cross-status-{$_GET["ID"]}');}		
			var XHR = new XHRConnection();
			XHR.appendData('stop-instance','{$_GET["ID"]}');
			XHR.sendAndLoad('$page', 'POST',x_StartInstanceBalance);
			
		}	
	
	StopInstanceBalance();
	";
	echo $html;
}
function start_instance(){
	$sock=new sockets();
	echo $sock->getFrameWork("xr.php?start-instance=yes&ID={$_POST["start-instance"]}");
	
}

function stop_instance(){
	$sock=new sockets();
	echo $sock->getFrameWork("xr.php?stop-instance=yes&ID={$_POST["stop-instance"]}");	
}
function restart_instance(){
	$sock=new sockets();
	echo $sock->getFrameWork("xr.php?restart-instance=yes&ID={$_POST["restart-instance"]}");	
}


function load_balance_form_backend_js(){
		$page=CurrentPageName();
		$q=new mysql();
		$sql="SELECT * FROM crossroads_backend WHERE backendmd='{$_GET["backend-server-edit-js"]}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$cmp=new computers($ligne["uid"]);
		$title=$cmp->ComputerRealName;
		$html="YahooWin4('380','$page?backend-server-edit=yes&backendmd={$_GET["backend-server-edit-js"]}','$title');";
		echo $html;
}

function load_balance_form_backend_edit(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$q=new mysql();
	$sql="SELECT * FROM crossroads_backend WHERE backendmd='{$_GET["backendmd"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:14px;'>{enabled}:</td>
		<td>". Field_checkbox("{$_GET["backendmd"]}enabled", 1,$ligne["enabled"])."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px;'>{max_connections}:</td>
		<td>". Field_text("max_connections",$ligne["max_connections"],"font-size:14px;width:90px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:14px;'>{weight}:</td>
		<td>". Field_text("backend_weight",$ligne["backend_weight"],"font-size:14px;width:90px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveBackendXR()")."</td>
	</tr>	
	</table>
	<script>
	var x_SaveBackendXR= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);return;}
			if(document.getElementById('crossroads-backend-list-table')){SearchBackendList();}
			if(document.getElementById('xr-list')){SearchXRList();}
			YahooWin4Hide();
		}	
		
	
		function SaveBackendXR(){
				var XHR = new XHRConnection();
				XHR.appendData('backend_weight',document.getElementById('backend_weight').value);
				XHR.appendData('max_connections',document.getElementById('max_connections').value);
				if(document.getElementById('{$_GET["backendmd"]}enabled').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
				XHR.appendData('backendmd','{$_GET["backendmd"]}');
				XHR.sendAndLoad('$page', 'POST',x_SaveBackendXR);				
			}
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function load_balance_form_backend_save(){
	$q=new mysql();
	
	$sql="SELECT crossroads_id FROM crossroads_backend WHERE backendmd='{$_POST["backend-del"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$crossroads_id=$ligne["crossroads_id"];	
	
	$sql="UPDATE crossroads_backend SET `backend_weight`='{$_POST["backend_weight"]}',`max_connections`='{$_POST["max_connections"]}',`enabled`='{$_POST["enabled"]}'
	WHERE `backendmd`='{$_POST["backendmd"]}'";

	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	$sock=new sockets();
	$sock->getFrameWork("xr.php?build-instance=yes&ID=$crossroads_id");	
	
}

function page(){
	$page=CurrentPageName();
	if(isset($_GET["newinterface"])){$newinterface="&newinterface=yes";$width="100%";}
	$html="<div id='crossroads-balancer-id'></div>
	<script>LoadAjax('crossroads-balancer-id','$page?tabs=yes$newinterface');</script>
	";
	echo $html;
	
}

function load_balance_form_js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	if(!isset($_GET["ID"])){$_GET["ID"]=0;}
	if($_GET["ID"]>0){
		$q=new mysql();
		$sql="SELECT name,ipaddrport FROM crossroads_main WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["name"]==null){$ligne["name"]="Load balancer on {$ligne["ipaddrport"]} port";}
		$title=$tpl->_ENGINE_parse_body("{service}::{$ligne["name"]}");
	}else{
		$title=$tpl->_ENGINE_parse_body("{add_loadbalance_service}");
	}
	$html="YahooWin3('700','$page?load-balance-form=yes&ID={$_GET["ID"]}','$title');";
	echo $html;
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<center style='margin:5px'><div style='width:220px' id='xr-toolbox'></div></center>
	<div class=explain>{balancers_service_section_explain}</div>
	
	
	<div id='xr-list'></div>
	
	<script>
		LoadAjax('xr-toolbox','$page?xr-toolbox=yes');
		LoadAjax('xr-list','$page?xr-list=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function xr_toolbox(){
	$page=CurrentPageName();
	$p1=Paragraphe("64-widget-add.png", "{add_loadbalance_service}", "{add_loadbalance_service_text}","javascript:Loadjs('$page?add-load-balance-js=yes')");
	$tr[]=imgtootltip("plus-48.png","{add_loadbalance_service_text}","Loadjs('$page?add-load-balance-js=yes')");
	$tr[]=imgtootltip("reload-48.png","{restart_all_instances_text}","Loadjs('$page?reload-all-instances-js=yes')");
	$tr[]=imgtootltip("reconfigure-48.png","{reconfigure_all_instances_text}","Loadjs('$page?reconfigure-all-instances-js=yes')");
	
	
	
	$tpl=new templates();
	$html=CompileTr3($tr);
	
	echo $tpl->_ENGINE_parse_body($html);
}

function xr_list_form(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{service}:</td>
				<td>". Field_text("xr-search",null,"font-size:16px",null,null,null,false,"SearchXRListCheck(event)")."</td>
				<td>". button("{search}","SearchXRList()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='xr-list-table' style='width:100%;height:350px;overflow:auto;background-color:white'></div>
	
	<script>
		function SearchXRListCheck(e){
			if(checkEnter(e)){SearchXRList();}
		}
	
		function SearchXRList(){
			var se=escape(document.getElementById('xr-search').value);
			LoadAjax('xr-list-table','$page?xr-list-search=yes&search='+se);
		}
	
	SearchXRList();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function load_balance_form_backend(){
		$tpl=new templates();
		$page=CurrentPageName();
		$main_id=$_GET["ID"];
	
	$html="
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{backend}:</td>
				<td>". Field_text("backend-search",null,"font-size:16px",null,null,null,false,"SearchBackendListCheck(event)")."</td>
				<td>". button("{search}","SearchBackendList()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='crossroads-backend-list-table' style='width:100%;height:350px;overflow:auto;background-color:white'></div>
	
	<script>
		function SearchBackendListCheck(e){
			if(checkEnter(e)){SearchBackendList();}
		}
	
		function SearchBackendList(){
			var se=escape(document.getElementById('backend-search').value);
			LoadAjax('crossroads-backend-list-table','$page?backend-list-search=yes&search='+se+'&main_id=$main_id');
		}
	
	SearchBackendList();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function load_balance_form_backend_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$main_id=$_GET["main_id"];
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$emailing_campain_linker_delete_confirm=$tpl->javascript_parse_text("{emailing_campain_linker_delete_confirm}");
	

	$add=imgtootltip("plus-24.png","{add}","Loadjs('computer-browse.php?callback=CrossroadsAddBackend&OnlyOCS=1')");
	
	$sql="SELECT * FROM crossroads_backend WHERE ((`uid` LIKE '$search' AND crossroads_id=$main_id) OR (`listen_port` LIKE '$search' AND crossroads_id=$main_id)) ORDER BY backend_weight DESC LIMIT 0,50";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{backends}&nbsp;|&nbsp;$search</th>
		<th>{listen_port}</th>
		<th>{ipaddr}</th>
		<th>{max_connections}</th>
		<th>{weight}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$cs=0;
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$uid=$ligne["uid"];
		$cmp=new computers($uid);
		$jsfiche=MEMBER_JS($uid,1,1);
		$select=imgtootltip("32-parameters.png","{edit}","Loadjs('$page?add-load-balance-js=yes&ID={$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","DeleteBalancerBackend('{$ligne["backendmd"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		$style="style='font-size:12px;font-weight:bold;color:$color'";
		$selectUri="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?add-load-balance-js=yes&ID={$ligne["ID"]}')\" $style>";
		if($ligne["name"]==null){$ligne["name"]="Load balancer on {$ligne["ipaddrport"]} port";}
		$uri=texttooltip($cmp->ComputerRealName,"{view}",$jsfiche,null,0,"font-size:16px;text-decoration:underline");
		if($ligne["max_connections"]==0){$ligne["max_connections"]="{unlimited}";}
		$cs++;
		$select=imgtootltip("32-network-server.png","{edit}","Loadjs('$page?backend-server-edit-js={$ligne["backendmd"]}')");
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>$select<input type='hidden' id='ipaddrCross-$cs' value='$cmp->ComputerIP'></td>
			<td width=99% $style nowrap><strong style='font-size:16px'>$uri</strong></td>
			<td width=1% $style nowrap><strong style='font-size:16px'>{$ligne["listen_port"]}</strong></td>
			<td width=1% $style nowrap><strong style='font-size:16px'>($cmp->ComputerIP)</strong></td>
			<td width=1% $style nowrap align=center><strong style='font-size:16px'>{$ligne["max_connections"]}</strong></td>
			<td width=1% $style nowrap align=center><strong style='font-size:16px'>{$ligne["backend_weight"]}</strong></td>
			<td width=1%>$delete</td>
			<td width=1%><div id='cmpCross-$cs'><img src='img/unknown24.png'></div></td>
		</tr>
		";
	}
	$html=$html."</tbody></table>
	
	<script>
	var x_CrossroadsAddBackend= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			SearchBackendList();
			if(document.getElementById('xr-list')){SearchXRList();}
		}	
		
	
		function CrossroadsAddBackend(uid){
			var listen_port=prompt('{give_the_remote_listen_port}');
			if(listen_port){
				var XHR = new XHRConnection();
				XHR.appendData('listen_ip',document.getElementById('listen_ip').value);
				XHR.appendData('backend-add',uid);
				XHR.appendData('backend-port',listen_port);
				XHR.appendData('main_id',$main_id);
				AnimateDiv('crossroads-backend-list-table');
				XHR.sendAndLoad('$page', 'POST',x_CrossroadsAddBackend);				
			}		
			
		}
		
	function CheckIpConfigCross2(i){
		if(document.getElementById('ipaddrCross-'+i)){
			var ipaddr=document.getElementById('ipaddrCross-'+i).value;
			LoadAjaxPreload('cmpCross-'+i,'ocs.search.php?compt-status='+ipaddr);
			i=i+1;
			setTimeout('CheckIpConfigCross2('+i+')',800);	
		}
	}
	
	function DeleteBalancerBackend(md){
		if(confirm('$emailing_campain_linker_delete_confirm')){
			var XHR = new XHRConnection();
			XHR.appendData('backend-del',md);
			AnimateDiv('crossroads-backend-list-table');
			XHR.sendAndLoad('$page', 'POST',x_CrossroadsAddBackend);				
		}
	}
	CheckIpConfigCross2(1);		
		
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function load_balance_form_backend_add(){
	$backendmd=md5("{$_POST["main_id"]}{$_POST["backend-add"]}{$_POST["backend-port"]}");
	$sql="INSERT IGNORE INTO crossroads_backend (crossroads_id,backendmd,uid,listen_port)
	VALUES('{$_POST["main_id"]}','$backendmd','{$_POST["backend-add"]}','{$_POST["backend-port"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("xr.php?restart-instance-silent=yes&ID={$_POST["main_id"]}");
	
}
function load_balance_form_backend_del(){
	$q=new mysql();
	$sql="SELECT crossroads_id FROM crossroads_backend WHERE backendmd='{$_POST["backend-del"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$crossroads_id=$ligne["crossroads_id"];
	
	
	$sql="DELETE FROM crossroads_backend WHERE backendmd='{$_POST["backend-del"]}'";
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	$sock=new sockets();
	$sock->getFrameWork("xr.php?restart-instance-silent=yes&ID=$crossroads_id");
	
}


function xr_list_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$action_delete_rule=$tpl->javascript_parse_text("{action_delete_rule}");
	
	$type[1]="{web_servers_type}";
	$type[2]="{other_tcp_proxymail}";
	
	$algo["first-available"]="{first_available}";
	$algo["strict-hashed-ip"]="{strict-hashed-ip}";
	$algo["lax-hashed-ip"]="{lax-hashed-ip}";
	$algo["least-connections"]="{least-connections}";
	$algo["round-robin"]="{round-robin}";		
	$add=imgtootltip("plus-24.png","{add_loadbalance_service}","Loadjs('$page?add-load-balance-js=yes')");
	
	$sql="SELECT * FROM crossroads_main WHERE ((`name` LIKE '$search') OR (`ipaddrport` LIKE '$search')) ORDER BY listen_ip LIMIT 0,50";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th nowrap>{dispatch_method}</th>
		<th>{type}</th>
		<th colspan=2>{services}&nbsp;|&nbsp;$search</th>
		<th>{backends}</th>
		<th>{status}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
		$q=new mysql();
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$select=imgtootltip("32-parameters.png","{edit}","Loadjs('$page?add-load-balance-js=yes&ID={$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","DeleteBalancerService('{$ligne["ID"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#CCCCCC";}
		$style="style='font-size:12px;font-weight:bold;color:$color;text-decoration:underline'";
		$selectUri="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?add-load-balance-js=yes&ID={$ligne["ID"]}')\" $style>";
		if($ligne["name"]==null){$ligne["name"]="Load balancer on {$ligne["ipaddrport"]} port";}
		$ligneTOT=mysql_fetch_array($q->QUERY_SQL("SELECT COUNT(*) as tcount FROM crossroads_backend WHERE crossroads_id={$ligne["ID"]}","artica_backup"));
		if(!$q->ok){$ligneTOT["tcount"]=$q->mysql_error;}
		
		$status=base64_decode($sock->getFrameWork("xr.php?status-instance=yes&ID={$ligne["ID"]}"));
		$ini=new Bs_IniHandler();
		$ini->loadString($status);
		if(DAEMON_STATUS_IS_OK("APP_CROSSROADS", $ini)){
			$img_status="ok32.png";
		}else{
			$img_status="danger32.png";
		}
		
		if($ligne["enabled"]==0){$img_status="ok32-grey.png";}
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>$select</td>
			<td width=1% $style nowrap>$selectUri{$algo[$ligne["dispatch_mode"]]}</a></td>
			<td width=1% $style nowrap>{$type[$ligne["loadbalancetype"]]}</td>
			<td $style>$selectUri{$ligne["name"]}</a></td>
			<td $style>$selectUri{$ligne["ipaddrport"]}</a></td>
			<td width=1% style='font-size:16px;font-weight:bold;color:$color;' nowrap align='center'>{$ligneTOT["tcount"]}</td>
			<td width=1% align='center'><img src='img/$img_status'></td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	$html=$html."</tbody></table>
	<script>
	var x_DeleteBalancerService= function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}
			if(document.getElementById('xr-list')){SearchXRList();}
			
		}		
	
		function DeleteBalancerService(ID){
		if(confirm('$action_delete_rule')){			
			YahooWin3Hide();
			var XHR = new XHRConnection();
			XHR.appendData('delete-instance',ID);
			if(document.getElementById('xr-list')){AnimateDiv('xr-list');}
			XHR.sendAndLoad('$page', 'POST',x_DeleteBalancerService);
			}
			
		}
	
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function load_balance_form_save(){
	$ID=$_POST["ID"];
	unset($_POST["ID"]);
	$q=new mysql();
	while (list ($num, $ligne) = each ($_POST) ){
		$fields[]="`$num`";
		$value[]="'".addslashes($ligne)."'";
		$edit[]="`$num`='".addslashes($ligne)."'";
	}
	
	$fields[]="ipaddrport";
	$value[]="'{$_POST["listen_ip"]}:{$_POST["listen_port"]}'";
	$edit[]="`ipaddrport`='".addslashes("{$_POST["listen_ip"]}:{$_POST["listen_port"]}")."'";
	
	if($ID>0){
		$sql="UPDATE crossroads_main SET ".@implode(",\n", $edit)." WHERE ID=$ID";
	}else{
		$sql="INSERT INTO crossroads_main (".@implode(",", $fields).") VALUES (".@implode(",\n", $value).")";
	}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql\n";return;}
	
	if($ID>0){
		$sock=new sockets();
		$sock->getFrameWork("xr.php?build-instance=yes&ID=$ID");
	}	
	
}


function load_balance_form(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$ID=$_GET["ID"];
	if(!is_numeric($ID)){$ID=0;}
	$array["status"]='{status}';
	$array["parameters"]='{parameters}';
	$array["backend"]='{backends}';
	$tpl=new templates();
	if($ID==0){
		unset($array["status"]);
		unset($array["backend"]);
	}
	$fontsize="style='font-size:14px'";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
	
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?load-balance-form-$num=yes&ID=$ID\"><span $fontsize>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_balance_form style='width:100%;height:100%;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_balance_form').tabs();
			});
		</script>";		
	
	
}

function load_balance_form_parameters(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ip=new networking();
	$ID=$_GET["ID"];
	$ips=$ip->ALL_IPS_GET_ARRAY();
	unset($ips["127.0.0.1"]);
	$ips[null]="{select}";
	if(!is_numeric($_GET["ID"])){$_GET["ID"]=0;}
	$sql="SELECT * FROM crossroads_main WHERE ID={$_GET["ID"]}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$button_text="{apply}";
	if($_GET["ID"]==0){$button_text="{add}";}
	$listen_port_text=$tpl->javascript_parse_text("{listen_port}");
	$listen_ip_text=$tpl->javascript_parse_text("{listen_ip}");
	
	if($ligne["backend_timout_read"]==null){$ligne["backend_timout_read"]=30;}
	if($ligne["backend_timout_write"]==null){$ligne["backend_timout_write"]=5;}
	if($ligne["client_timout"]==null){$ligne["client_timout"]=30;}
	if($ligne["client_timout_write"]==null){$ligne["client_timout_write"]=5;}
	
	
	
	if($ligne["checkup_interval"]==null){$ligne["checkup_interval"]=10;}
	if($ligne["wakeup_interval"]==null){$ligne["wakeup_interval"]=5;}
	if(!is_numeric($ligne["listen_port"])){$ligne["listen_port"]=0;}
	if(!is_numeric($ligne["loadbalancetype"])){$ligne["loadbalancetype"]=1;}
	if(!is_numeric($ligne["enabled"])){$ligne["enabled"]=1;}
	if($ligne["dispatch_mode"]==null){$ligne["dispatch_mode"]="least-connections";}
	if($ligne["name"]==null){$ligne["name"]="New Load-balancer service";}
	
	
	$algo["first-available"]="{first_available}";
	$algo["strict-hashed-ip"]="{strict-hashed-ip}";
	$algo["lax-hashed-ip"]="{lax-hashed-ip}";
	$algo["least-connections"]="{least-connections}";
	$algo["round-robin"]="{round-robin}";	
	
	
	$type[1]="{web_servers_type}";
	$type[2]="{other_tcp_proxymail}";
	
	$form="
	<div id='xr-form2'></div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:13px'>{enable_service}:</td>
		<td style='font-size:13px'>". Field_checkbox("enabled",1,$ligne["enabled"],"EnableServiceCross2()")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{type}:</td>
		<td style='font-size:13px'>". Field_array_Hash($type,"loadbalancetype",$ligne["loadbalancetype"],null,null,0,"font-size:13px;padding:3px;")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{service_name}:</td>
		<td style='font-size:13px'>". Field_text("service_name",$ligne["name"],"font-size:16px;padding:3px;width:255px",null,null,null,false,null)."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	
	
	<tr>
		<td class=legend style='font-size:13px'>{listen_ip}:</td>
		<td style='font-size:13px'>". Field_array_Hash($ips,"listen_ip",$ligne["listen_ip"],null,null,0,"font-size:13px;padding:3px;")."</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td style='font-size:13px'>". Field_text("listen_port",$ligne["listen_port"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"null")."</td>
		<td width=1%></td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{dispatch_method}:</td>
		<td style='font-size:13px'>". Field_array_Hash($algo,"dispatch_mode",$ligne["dispatch_mode"],null,null,0,"font-size:13px;padding:3px;")."</td>
		<td width=1%>&nbsp;</td>
	</tr>			
	<tr>
		<td class=legend style='font-size:13px'>{backend-timout}: ({read})</td>
		<td style='font-size:13px'>". Field_text("backend_timout_read",$ligne["backend_timout_read"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{backend-timout-xr}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{backend-timout}: ({write})</td>
		<td style='font-size:13px'>". Field_text("backend_timout_write",$ligne["backend_timout_write"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{backend-timout-xr}")."</td>
	</tr>	
		<td class=legend style='font-size:13px'>{client-timout}: ({read})</td>
		<td style='font-size:13px'>". Field_text("client_timout",$ligne["client_timout"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{client-timout-xr}")."</td>
	</tr>
	</tr>
		<td class=legend style='font-size:13px'>{client-timout}: ({write})</td>
		<td style='font-size:13px'>". Field_text("client_timout_write",$ligne["client_timout_write"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{client-timout-xr}")."</td>
	</tr>	
	</tr>
		<td class=legend style='font-size:13px'>{checkup-interval}:</td>
		<td style='font-size:13px'>". Field_text("checkup_interval",$ligne["checkup_interval"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{xr_check_explain}")."</td>
	</tr>
	</tr>
		<td class=legend style='font-size:13px'>{wakeup-interval}:</td>
		<td style='font-size:13px'>". Field_text("wakeup_interval",$ligne["wakeup_interval"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{xr_wakeup_explain}")."</td>
	</tr>
	</tr>
		<td class=legend style='font-size:13px'>{web_interface_port}:</td>
		<td style='font-size:13px'>". Field_text("www_port",$ligne["www_port"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."</td>
		<td width=1%>". help_icon("{xr_web_interface_port}")."</td>
	</tr>	
	</tr>
		<td class=legend style='font-size:13px'>{username}:</td>
		<td style='font-size:13px'>". Field_text("www_username",$ligne["www_username"],"font-size:13px;padding:3px;width:120px",null,null,null,false,"BckGenFormCheck(event)")."</td>
		<td width=1%></td>
	</tr>		
	</tr>
		<td class=legend style='font-size:13px'>{password}:</td>
		<td style='font-size:13px'>". Field_text("www_password",$ligne["www_password"],"font-size:13px;padding:3px;width:120px",null,null,null,false,"BckGenFormCheck(event)")."</td>
		<td width=1%></td>
	</tr>	

	<tr>
		<td colspan=3 align='right'><hr>". button($button_text,"SaveXRConfig2()")."</td>
	</tr>	
	</table>
	
	<p>&nbsp;</p>
	<script>
		function BckGenFormCheck(e){
		
		}
		
		function EnableServiceCross2(){
			
			document.getElementById('listen_ip').disabled=true;
			document.getElementById('listen_port').disabled=true;
			document.getElementById('client_timout').disabled=true;
			document.getElementById('checkup_interval').disabled=true;
			document.getElementById('wakeup_interval').disabled=true;
			document.getElementById('client_timout_write').disabled=true;
			document.getElementById('backend_timout_write').disabled=true;
			document.getElementById('backend_timout_read').disabled=true;
			document.getElementById('dispatch_mode').disabled=true;
			document.getElementById('loadbalancetype').disabled=true;
			document.getElementById('service_name').disabled=true;
			document.getElementById('www_port').disabled=true;
			document.getElementById('www_username').disabled=true;
			document.getElementById('www_password').disabled=true;
			
			
			
			if(document.getElementById('enabled').checked){
				document.getElementById('listen_ip').disabled=false;
				document.getElementById('listen_port').disabled=false;
				document.getElementById('backend_timout_write').disabled=false;
				document.getElementById('client_timout').disabled=false;
				document.getElementById('checkup_interval').disabled=false;
				document.getElementById('wakeup_interval').disabled=false;
				document.getElementById('client_timout_write').disabled=false;	
				document.getElementById('backend_timout_write').disabled=false;
				document.getElementById('backend_timout_read').disabled=false;		
				document.getElementById('dispatch_mode').disabled=false;
				document.getElementById('loadbalancetype').disabled=false;	
				document.getElementById('service_name').disabled=false;
				document.getElementById('www_username').disabled=false;	
				document.getElementById('www_password').disabled=false;		
			}
		}
		
var X_SaveXRConfig2= function (obj) {
	var results=obj.responseText;
	if(results.length>2){
		alert(results);
		document.getElementById('xr-form2').innerHTML='';
		return;
	}
	RefreshTab('main_balance_form');
	if(document.getElementById('xr-list')){SearchXRList();}
	}
		
	function SaveXRConfig2(){
		var XHR = new XHRConnection();
		if(document.getElementById('listen_port').value=='0'){
			alert('$listen_port_text = 0 !!');
			return;
		}
		
		if(document.getElementById('listen_ip').value==''){
			alert('$listen_ip_text = Null !!');
			return;
		}		
		
		XHR.appendData('listen_ip',document.getElementById('listen_ip').value);
		XHR.appendData('listen_port',document.getElementById('listen_port').value);
		XHR.appendData('backend_timout_read',document.getElementById('backend_timout_read').value);
		XHR.appendData('backend_timout_write',document.getElementById('backend_timout_write').value);
		XHR.appendData('checkup_interval',document.getElementById('checkup_interval').value);
		XHR.appendData('wakeup_interval',document.getElementById('wakeup_interval').value);
		XHR.appendData('client_timout_write',document.getElementById('client_timout_write').value);
		XHR.appendData('dispatch_mode',document.getElementById('dispatch_mode').value);
		XHR.appendData('loadbalancetype',document.getElementById('loadbalancetype').value);
		XHR.appendData('name',document.getElementById('service_name').value);
		XHR.appendData('www_port',document.getElementById('www_port').value);
		XHR.appendData('www_username',document.getElementById('www_username').value);
		XHR.appendData('www_password',document.getElementById('www_password').value);
		
		
		
		XHR.appendData('ID',{$_GET["ID"]});
		if(document.getElementById('enabled').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		AnimateDiv('xr-form2');
		XHR.sendAndLoad('$page', 'POST',X_SaveXRConfig2);				
	}

	function CheckStatusPort(){
		var www_port=document.getElementById('www_port').value;
		if(www_port.length==0){fillStatusPort();return;}
		if(www_port<30999){fillStatusPort();return;}
		
	}
	
var X_fillStatusPort= function (obj) {
	var results=obj.responseText;
	if(results.length>2){
		document.getElementById('www_port').value=results;
		return;
	}
}	
	
	function fillStatusPort(){
		var XHR = new XHRConnection();
		XHR.appendData('find-newport','yes');
		XHR.sendAndLoad('$page', 'POST',X_fillStatusPort);	
	}
	
	EnableServiceCross2();
	CheckStatusPort();
	</script>	
	";
	echo $tpl->_ENGINE_parse_body($form);	
}


function tabs(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["events"]='{events}';
	$tpl=new templates();
	
	if(isset($_GET["newinterface"])){$fontsize="style='font-size:14px'";$width="100%";}
	
	
	
	while (list ($num, $ligne) = each ($array) ){
	
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span $fontsize>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_crossroads_bler style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_crossroads_bler').tabs();
			});
		</script>";		
	
	
}	


function backend_form_add(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();

	
	$html="
	<div id='backnddiv'>
	<table style='width:100%'>
	<tr>	
	<td class=legend style='font-size:13px'>{ip_addr}:</td>
	<td>". field_ipv4("servername",null,"font-size:14px",null,null,null,false,"")."</td>
	</tr>
	<tr>	
	<td class=legend style='font-size:13px'>{listen_port}:</td>
	<td>". Field_text("server_port",25,"font-size:14px;padding:3px",null,null,null,false,"")."</td>
	</tr>
	<tr>
		<TD colspan=2 align='right'><hr>". button("{add}","AddBckForm()")."</td>
	</tr>
	</table>
	</div>
";
	
	echo $tpl->_ENGINE_parse_body($html);

	
}

function find_newport(){
	$q=new mysql();
	$sql="SELECT www_port FROM crossroads_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$alrready=array();
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$alrready[$ligne["www_port"]]=true;
	}	
	
	for($i=40000;$i<65535;$i++){
		$socket = @socket_create(AF_INET, SOCK_STREAM, 0);
		writelogs("connect 127.0.0.1 -> $i",__FUNCTION__,__FILE__,__LINE__);
		if(socket_connect($socket, "127.0.0.1", $i)){
			@socket_close($socket);
			continue;
		}
		if(!isset($alrready[$ligne["www_port"]])){echo $i;return;}
		
	}
	
	
}
function load_balance_form_status(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$q=new mysql();
	$ID=$_GET["ID"];
	$sql="SELECT www_port,listen_ip FROM crossroads_main WHERE ID={$_GET["ID"]}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$html="
	<div id='cross-status-{$_GET["ID"]}'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<div style='margin:5px'><a href=\"javascript:blur();\" OnClick=\"s_PopUpScroll('http://{$ligne["listen_ip"]}:{$ligne["www_port"]}/','800','800');\"
		style='font-size:16px;text-decoration:underline;font-weight:bolder'>{web_interface}</a></div>
		<div id='status1'></div></td>
		<td valign='top'><div id='status2'></div></td>
	</tr>
	</table>
	</div>
	<hr>
	<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","LaunchXRStatus()")."</div>
	<script>
		function LaunchXRStatus(){
			LoadAjax('status1','$page?load-balance-form-status-service=yes&ID=$ID');
		
		}
		
		LaunchXRStatus();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function load_balance_form_status_service(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$status=base64_decode($sock->getFrameWork("xr.php?status-instance=yes&ID={$_GET["ID"]}"));
	$ini=new Bs_IniHandler();
	$ini->loadString($status);
	if(DAEMON_STATUS_IS_OK("APP_CROSSROADS", $ini)){
		$tr[]=imgtootltip("32-run-grey.png","{start}","");
		$tr[]=imgtootltip("32-stop.png","{stop}","Loadjs('$page?stop-instance-js=yes&ID={$_GET["ID"]}')");
	}else{
		$tr[]=imgtootltip("32-run.png","{start}","Loadjs('$page?start-instance-js=yes&ID={$_GET["ID"]}')");
		$tr[]=imgtootltip("32-stop-grey.png","{stop}","");
		
		
	}
	$tr[]=imgtootltip("restart-32.png","{restart}","Loadjs('$page?restart-instance-js=yes&ID={$_GET["ID"]}')");
	
	$table=CompileTr3($tr);
	
	echo $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("APP_CROSSROADS", $ini)
	."
	<center><div style='text-align:center;width:150px'><center>$table</center></div></center>
	<script>
		LoadAjax('status2','$page?load-balance-form-status-service-stats=yes&ID={$_GET["ID"]}');
	</script>"
	
	);
	
}

function load_balance_form_status_service_stats(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("xr.php?statrt=yes&ID={$_GET["ID"]}")));
	$backends=count($array);
	
	$html="<div style='font-size:16px'>$backends {backends}:</div>";
	while (list ($index, $arrays) = each ($array)){
		if(preg_match("#([0-9\+a-z\.]+)\s+bytes#", $arrays["FLOW"],$re)){
			$size=trim($re[1]);
			$arrays["FLOW"]=FormatBytes($size/1024);
		}
		$html=$html."<table style='width:100%;margin-top:5px' class=form>
		<tbody>
			<tr>
				<td class=legend style='font-size:14px'>{backend}:</td>
				<td style='font-size:14px'>{$arrays["NAME"]}</td>
			</tr>
			<tr>
				<td class=legend style='font-size:14px'>{weight}:</td>
				<td style='font-size:14px'>{$arrays["WEIGHT"]}</td>
			</tr>
			<tr>
				<td class=legend style='font-size:14px'>{status}:</td>
				<td style='font-size:14px'>{$arrays["STATUS"]}</td>
			</tr>
			<tr>
				<td class=legend style='font-size:14px'>{connections}:</td>
				<td style='font-size:14px'>{$arrays["CNX"]} ({$arrays["CLIENTS"]} {clients})</td>
			</tr>
			<tr>
				<td class=legend style='font-size:14px'>{flow}:</td>
				<td style='font-size:14px'>{$arrays["FLOW"]}</td>
			</tr>	
			</tbody>											
		</table>
		";
	}
	echo $tpl->_ENGINE_parse_body($html);
	
}

function reloadall_instances(){
	$sock=new sockets();
	$sock->getFrameWork("xr.php?reload-all-instances=yes");
}
function reconfigure_all_instances(){
	$sock=new sockets();
	$sock->getFrameWork("xr.php?reconfigure-all-instances=yes");	
}

function delete_instance(){
	$ID=$_POST["delete-instance"];
	$sock=new sockets();
	$q=new mysql();
	
	$sock->getFrameWork("xr.php?stop-instance=yes&ID=$ID");
	$sql="DELETE FROM crossroads_main WHERE ID='$ID'";
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	$sql="DELETE FROM crossroads_backend WHERE crossroads_id='$ID'";
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error;return;}	
	
}


function xml2array($xml) {
    if(get_class($xml) != 'SimpleXMLElement') {
        if        (is_file($xml))    {    $xml = simplexml_load_file        ($xml);    }
        elseif    (is_string($xml))    {    $xml = simplexml_load_string    ($xml);    }
    }
    if(!$xml) {
        return false;
    }
    $main = $xml->getName();
    $arr  = array();
    $nodes = $xml->children();
    foreach($nodes as $node) {
        $nodeName        = $node->getName();
        $nodeAttributes  = $node->attributes();
        $attributesArray = array();
        foreach($nodeAttributes as $attributeName => $attributeValue) {
            $attributesArray[$attributeName] = (string) $attributeValue;
        }
        $nodeValue = sizeOf($node->children()) == 0 ? trim($node) : xml2array($node);
        if(!isSet($arr[$nodeName]['valeur'])) {
            $arr[$nodeName]['valeur']      = $nodeValue;
            $arr[$nodeName]['attributs'] = $attributesArray;
        } else {
            if(!is_array($arr[$nodeName]['valeur'])) {
                $arr[$nodeName]['valeur'][]      = array_shift($arr[$nodeName]);
                $arr[$nodeName]['attributs'][] = array_shift($arr[$nodeName]['attributs']);
            }
            $arr[$nodeName]['valeur'][]      = $nodeValue;
            $arr[$nodeName]['attributs'][] = $attributesArray;
        }
    }
    return($arr);
}

function events(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<table style='width:100%' class=form>
		<tbody>
			<tr>
				<td class=legend>{service}:</td>
				<td>". Field_text("xr-events-search",null,"font-size:16px",null,null,null,false,"SearchXREventsListCheck(event)")."</td>
				<td>". button("{search}","SearchXREventsList()")."</td>
			</tr>
		</tbody>
	</table>
	<div id='xr-events-table' style='width:100%;height:350px;overflow:auto;background-color:white'></div>
	
	<script>
		function SearchXREventsListCheck(e){
			if(checkEnter(e)){SearchXREventsList();}
		}
	
		function SearchXREventsList(){
			var se=escape(document.getElementById('xr-events-search').value);
			LoadAjax('xr-events-table','$page?xr-events-search=yes&search='+se);
		}
	
	SearchXREventsList();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function events_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	$sql="SELECT * FROM crossroads_events WHERE ((`description` LIKE '$search') OR (`function` LIKE '$search')) ORDER BY zDate DESC LIMIT 0,50";
	

	
		
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_events");
		$count=mysql_num_rows($results);
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		
		<th nowrap>{date}</th>
		<th>{instance}</th>
		<th>{function}</th>
		<th>{description} $count {events}</th>
	</tr>
</thead>
<tbody class='tbody'>";			
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$style="style='font-size:13px'";
		$sql="SELECT name FROM crossroads_main WHERE ID={$ligne["instance_id"]}";
		$ligne2=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$ligne["description"]=htmlentities($ligne["description"]);
		$ligne["description"]=nl2br($ligne["description"]);
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>{$ligne["zDate"]}</td>
			<td width=1% $style nowrap>{$ligne2["name"]}</td>
			<td width=1% $style nowrap>{$ligne["function"]}:{$ligne["line"]}</td>
			<td width=99% $style>{$ligne["description"]}</td>
			
		</tr>
		";
	}
	$html=$html."</tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
}