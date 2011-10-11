<?php
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["xr-toolbox"])){xr_toolbox();exit;}
	if(isset($_GET["xr-list"])){xr_list_form();exit;}
	if(isset($_GET["xr-list-search"])){xr_list_search();exit;}
	
	if(isset($_GET["add-load-balance-js"])){load_balance_form_js();exit;}
	if(isset($_GET["load-balance-form"])){load_balance_form();exit;}
	if(isset($_GET["load-balance-form-parameters"])){load_balance_form_parameters();exit;}
	if(isset($_GET["load-balance-form-backend"])){load_balance_form_backend();exit;}
	
	if(isset($_GET["backend-list-search"])){load_balance_form_backend_search();exit;}
	if(isset($_POST["backend-add"])){load_balance_form_backend_add();exit;}
	
	
	if(isset($_POST["listen_ip"])){load_balance_form_save();exit;}
	
page();

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
	$html="YahooWin3('650','$page?load-balance-form=yes&ID={$_GET["ID"]}','$title');";
	echo $html;
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td valign='top' width=1%><div id='xr-toolbox'></div></td>
		<td valign='top' width=99%><div class=explain>{balancers_service_section_explain}</div></td>
	</tr>
	</tbody>	
	</table>
	
	
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
	$tpl=new templates();
	$html="<div style='background-color:white;margin:3px;padding:3px'>$p1</div>";
	
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
	
	

	$add=imgtootltip("plus-24.png","{add}","Loadjs('computer-browse.php?callback=CrossroadsAddBackend&OnlyOCS=1')");
	
	$sql="SELECT * FROM crossroads_backend WHERE  ((`uid` LIKE '$search' AND crossroads_id=$main_id) OR (`listen_port` LIKE '$search' AND crossroads_id=$main_id)) ORDER BY uid LIMIT 0,50";
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=5>{backends}&nbsp;|&nbsp;$search</th>
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
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>$select</td>
			<td width=1% $style nowrap>$selectUri{$algo[$ligne["dispatch_mode"]]}</a></td>
			<td width=1% $style nowrap>{$type[$ligne["loadbalancetype"]]}</td>
			<td $style>$selectUri{$ligne["name"]}</a></td>
			<td $style>$selectUri{$ligne["ipaddrport"]}</a></td>
			<td width=1%>$delete</td>
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
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function load_balance_form_backend_add(){
	$backendmd=md5("{$_POST["main_id"]}{$_POST["backend-add"]}{$_POST["backend-port"]}");
	$sql="INSERT IGNORE INTO crossroads_backend (crossroads_id,backendmd,uid,listen_port)
	VALUES('{$_POST["main_id"]}','{$_POST["main_id"]}','$backendmd','{$_POST["backend-add"]}','{$_POST["backend-port"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
}


function xr_list_search(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$search="*".$_GET["search"]."*";
	$search=str_replace("**", "*", $search);
	$search=str_replace("**", "*", $search);
	$search=str_replace("*", "%", $search);
	
	$type[1]="HTTP/Proxy";
	$type[2]="{messaging}";
	
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
		<th colspan=5>{services}&nbsp;|&nbsp;$search</th>
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
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>$select</td>
			<td width=1% $style nowrap>$selectUri{$algo[$ligne["dispatch_mode"]]}</a></td>
			<td width=1% $style nowrap>{$type[$ligne["loadbalancetype"]]}</td>
			<td $style>$selectUri{$ligne["name"]}</a></td>
			<td $style>$selectUri{$ligne["ipaddrport"]}</a></td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	$html=$html."</tbody></table>";
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
	
}


function load_balance_form(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$ID=$_GET["ID"];
	if(!is_numeric($ID)){$ID=0;}
	$array["parameters"]='{parameters}';
	$array["backend"]='{backends}';
	$tpl=new templates();
	
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
	
	
	$type[1]="HTTP/Proxy";
	$type[2]="{messaging}";
	
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

	<tr>
		<td colspan=3 align='right'><hr>". button($button_text,"SaveXRConfig2()")."</td>
	</tr>	
	</table>
	
	<p>&nbsp;</p>
	<script>
		
		
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
		
		
		
		XHR.appendData('ID',{$_GET["ID"]});
		if(document.getElementById('enabled').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		AnimateDiv('xr-form2');
		XHR.sendAndLoad('$page', 'POST',X_SaveXRConfig2);				
	}		
	
	EnableServiceCross2();
		
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