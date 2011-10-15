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
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["status-service"])){status_service();exit;}
	if(isset($_GET["settings"])){settings();exit;}
	if(isset($_GET["backend-form-add"])){backend_form_add();exit;}
	if(isset($_GET["servername"])){backend_add();exit;}
	if(isset($_GET["delete-servername"])){backend_delete();exit;}
	if(isset($_GET["backends-servers"])){backend_list();exit;}
	if(isset($_GET["EnableCrossRoads"])){SAVECONF();exit;}
	if(isset($_GET["cross-events"])){events_details();exit;}
	if(isset($_GET["cross-notify"])){cross_notify();exit;}
js();	
	
	
function js(){
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_CROSSROADS}');
$add_backend_server=$tpl->_ENGINE_parse_body('{add_backend_server}');
if(isset($_GET["newinterface"])){$newinterface="&newinterface=yes";}
$html="

function CrossRoadsIndexLoadpage(){
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes$newinterface');
	}	
	
	
function AddBackend(){
	YahooWin2('350','$page?backend-form-add=yes','$add_backend_server');

}

function RefreshBackendServers(){
	LoadAjax('backends_server','$page?backends-servers=yes');
}

var X_AddBckForm= function (obj) {
	var results=obj.responseText;
	if(results.length>1){alert(results);}
	YahooWin2Hide();
	RefreshBackendServers(); 
	}
		
	function AddBckForm(){
		var XHR = new XHRConnection();
		var servername=document.getElementById('servername').value;
		var server_port=document.getElementById('server_port').value;
		if(servername.length==0){return;}
		if(server_port.length==0){return;}
		XHR.appendData('servername',servername+':'+server_port);
		document.getElementById('backnddiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_AddBckForm);				
	}
	
	function AddBckFormCheck(e){
		if(checkEnter(e)){AddBckForm();}
	}
	
function BackendDelete(servername){
		var XHR = new XHRConnection();
		XHR.appendData('delete-servername',servername);
		document.getElementById('backends_server').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_AddBckForm);	

}
	
	CrossRoadsIndexLoadpage();";
	echo $html;
	
}	


function popup(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$array["status"]='{status}';
	if($users->LOAD_BALANCE_APPLIANCE){
		$array["loadbalance"]='{balancers}';
	}else{
		$array["settings"]='{settings}';
	}
	$array["events"]='{events}';
	$tpl=new templates();
	
	if(isset($_GET["newinterface"])){$fontsize="style='font-size:14px'";$width="100%";$newinterface="?newinterface=yes";}
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="loadbalance"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"crossroads.balancers.php$newinterface\"><span $fontsize>$ligne</span></a></li>\n");
			continue;
			
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span $fontsize>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_crossroads style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_crossroads').tabs();
			});
		</script>";	
	
}


function backend_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	
	
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th width=1%>&nbsp;</th>
			<th>{servername}</th>
			<th width=1%>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	$sock=new sockets();
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	
	if(is_array($MAIN["BACKENDS"])){
		while (list ($servername, $ligne) = each ($MAIN["BACKENDS"]) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/computer-32.png'></td>
			<td style='font-size:14px'><code>$servername</code></td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","BackendDelete('$servername')")."</td>
			</tr>";
			
			
		}
	}
	
	$html=$html."</tbody></table>
	<script>LoadAjax('cross-notify','$page?cross-notify=yes');</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
		<img src='img/load-blancing-256.png'>
		</td>
		<td valign='top' width=99%>
			<H1>{APP_CROSSROADS}</H1>
			<div class=explain>{crossroads_explain}</div>
			<div style='margin-top:20px' id='crossroads_status'></div>
		</td>
	</tr>
	</table>
	<script>
		LoadAjax('crossroads_status','$page?status-service=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function status_service(){
	$ini=new Bs_IniHandler();
	$tpl=new templates();
	$sock=new sockets();
	$q=new mysql();
	
	$sql="SELECT ID,name FROM crossroads_main WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$status=base64_decode($sock->getFrameWork("xr.php?status-instance=yes&ID={$ligne["ID"]}"));
		$ini=new Bs_IniHandler();
		$ini->loadString($status);		
		$f[]=$tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("APP_CROSSROADS", $ini,$ligne["name"]));
		
	}	
	
	
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?crossroads-ini-status=yes")));
	$f[]=$tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("APP_CROSSROADS",$ini,null,0));
	echo @implode("\n", $f)	;
}

function backend_add(){
	$sock=new sockets();
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	$MAIN["BACKENDS"][$_GET["servername"]]["STATUS"]="ENABLED";	
	$sock->SaveConfigFile(base64_encode(serialize($MAIN)),"CrossRoadsParams");
	$sock->getFrameWork("cmd.php?crossroads-restart=yes");
}

function backend_delete(){
	$sock=new sockets();
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	unset($MAIN["BACKENDS"][$_GET["delete-servername"]]);
	$sock->SaveConfigFile(base64_encode(serialize($MAIN)),"CrossRoadsParams");
	$sock->getFrameWork("cmd.php?crossroads-restart=yes");
}
function SAVECONF(){
	$sock=new sockets();
	$sock->SET_INFO("EnableCrossRoads",$_GET["EnableCrossRoads"]);
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	$MAIN["PARAMS"]=$_GET;
	$sock->SaveConfigFile(base64_encode(serialize($MAIN)),"CrossRoadsParams");
	$sock->getFrameWork("cmd.php?crossroads-restart=yes");
	}

function settings(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();
	$users=new usersMenus();
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	$ip=new networking();
	$ips=$ip->ALL_IPS_GET_ARRAY();
	unset($ips["127.0.0.1"]);
	$ips[null]="{all}";
	
	if($MAIN["PARAMS"]["backend-timout"]==null){$MAIN["PARAMS"]["backend-timout"]=30;}
	if($MAIN["PARAMS"]["backend-timout-write"]==null){$MAIN["PARAMS"]["backend-timout-write"]=5;}
	if($MAIN["PARAMS"]["client-timout"]==null){$MAIN["PARAMS"]["client-timout"]=30;}
	if($MAIN["PARAMS"]["client-timout-write"]==null){$MAIN["PARAMS"]["client-timout-write"]=5;}
	
	
	
	if($MAIN["PARAMS"]["checkup-interval"]==null){$MAIN["PARAMS"]["checkup-interval"]=10;}
	if($MAIN["PARAMS"]["wakeup-interval"]==null){$MAIN["PARAMS"]["wakeup-interval"]=5;}
	if(!is_numeric($MAIN["PARAMS"]["listen_port"])){$MAIN["PARAMS"]["listen_port"]=25;}
	if($MAIN["PARAMS"]["dispatch-mode"]==null){$MAIN["PARAMS"]["dispatch-mode"]="least-connections";}
	
	$algo["first-available"]="{first_available}";
	$algo["strict-hashed-ip"]="{strict-hashed-ip}";
	$algo["lax-hashed-ip"]="{lax-hashed-ip}";
	$algo["least-connections"]="{least-connections}";
	$algo["round-robin"]="{round-robin}";
	
	$LOAD_BALANCE_APPLIANCE=0;
	if($users->LOAD_BALANCE_APPLIANCE){$LOAD_BALANCE_APPLIANCE=1;}

	
	
	$form="<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:13px'>{enable_loadblancing}:</td>
		<td style='font-size:13px'>". Field_checkbox("EnableCrossRoads",1,$sock->GET_INFO("EnableCrossRoads"),"EnableServiceCross()")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{listen_ip}:</td>
		<td style='font-size:13px'>". Field_array_Hash($ips,"listen_ip",$MAIN["PARAMS"]["listen_ip"],null,null,0,"font-size:13px;padding:3px;")."</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td style='font-size:13px'>". Field_text("listen_port",$MAIN["PARAMS"]["listen_port"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."</td>
		<td width=1%></td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{dispatch_method}:</td>
		<td style='font-size:13px'>". Field_array_Hash($algo,"dispatch-mode",$MAIN["PARAMS"]["dispatch-mode"],null,null,0,"font-size:13px;padding:3px;")."</td>
		<td width=1%>&nbsp;</td>
	</tr>			
	<tr>
		<td class=legend style='font-size:13px'>{backend-timout}: ({read})</td>
		<td style='font-size:13px'>". Field_text("backend-timout",$MAIN["PARAMS"]["backend-timout"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{backend-timout-xr}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{backend-timout}: ({write})</td>
		<td style='font-size:13px'>". Field_text("backend-timout-write",$MAIN["PARAMS"]["backend-timout-write"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{backend-timout-xr}")."</td>
	</tr>	
		<td class=legend style='font-size:13px'>{client-timout}: ({read})</td>
		<td style='font-size:13px'>". Field_text("client-timout",$MAIN["PARAMS"]["client-timout"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{client-timout-xr}")."</td>
	</tr>
	</tr>
		<td class=legend style='font-size:13px'>{client-timout}: ({write})</td>
		<td style='font-size:13px'>". Field_text("client-timout-write",$MAIN["PARAMS"]["client-timout-write"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{client-timout-xr}")."</td>
	</tr>	
	</tr>
		<td class=legend style='font-size:13px'>{checkup-interval}:</td>
		<td style='font-size:13px'>". Field_text("checkup-interval",$MAIN["PARAMS"]["checkup-interval"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{xr_check_explain}")."</td>
	</tr>
	</tr>
		<td class=legend style='font-size:13px'>{wakeup-interval}:</td>
		<td style='font-size:13px'>". Field_text("wakeup-interval",$MAIN["PARAMS"]["wakeup-interval"],"font-size:13px;padding:3px;width:45px",null,null,null,false,"BckGenFormCheck(event)")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{xr_wakeup_explain}")."</td>
	</tr>

	<tr>
		<td colspan=3 align='right'><hr>". button('{apply}',"SaveXRConfig()")."</td>
	</tr>	
	</table>
	<p>&nbsp;</p>";
	
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			". Paragraphe("cluster-replica-add.png","{add_backend_server}","{add_backend_server_loadb_text}","javascript:AddBackend()")."
			<br>
			<span id='cross-notify'></span>
		</td>
		<td valign='top'><div id='xr-form'>$form</div>
			<div id='backends_server' style='width:100%;height:220px;overflow:auto'></div>
		</td>
	</tr>
	</table>
	
	<script>
		RefreshBackendServers();
		EnableServiceCross();
		
		
		
		function EnableServiceCross(){
			var LOAD_BALANCE_APPLIANCE=$LOAD_BALANCE_APPLIANCE;
			if(LOAD_BALANCE_APPLIANCE==1){
				document.getElementById('EnableCrossRoads').checked=true;
				document.getElementById('EnableCrossRoads').disabled=true;
			}
		
			document.getElementById('listen_ip').disabled=true;
			document.getElementById('listen_port').disabled=true;
			document.getElementById('backend-timout').disabled=true;
			document.getElementById('client-timout').disabled=true;
			document.getElementById('checkup-interval').disabled=true;
			document.getElementById('wakeup-interval').disabled=true;
			document.getElementById('client-timout-write').disabled=true;
			document.getElementById('backend-timout-write').disabled=true;
			document.getElementById('dispatch-mode').disabled=true;
			
			
			
			if(document.getElementById('EnableCrossRoads').checked){
				document.getElementById('listen_ip').disabled=false;
				document.getElementById('listen_port').disabled=false;
				document.getElementById('backend-timout').disabled=false;
				document.getElementById('client-timout').disabled=false;
				document.getElementById('checkup-interval').disabled=false;
				document.getElementById('wakeup-interval').disabled=false;
				document.getElementById('client-timout-write').disabled=false;	
				document.getElementById('backend-timout-write').disabled=false;	
				document.getElementById('dispatch-mode').disabled=false;	
			}
		}
		
var X_SaveXRConfig= function (obj) {
	var results=obj.responseText;
	if(results.length>1){alert(results);}
	RefreshTab('main_config_crossroads');
	}
		
	function SaveXRConfig(){
		var XHR = new XHRConnection();
		XHR.appendData('listen_ip',document.getElementById('listen_ip').value);
		XHR.appendData('listen_port',document.getElementById('listen_port').value);
		XHR.appendData('backend-timout',document.getElementById('backend-timout').value);
		XHR.appendData('backend-timout-write',document.getElementById('backend-timout-write').value);
		XHR.appendData('checkup-interval',document.getElementById('checkup-interval').value);
		XHR.appendData('wakeup-interval',document.getElementById('wakeup-interval').value);
		XHR.appendData('client-timout-write',document.getElementById('client-timout-write').value);
		XHR.appendData('dispatch-mode',document.getElementById('dispatch-mode').value);
		if(document.getElementById('EnableCrossRoads').checked){XHR.appendData('EnableCrossRoads',1);}else{XHR.appendData('EnableCrossRoads',0);}
		
		
		AnimateDiv('xr-form');
		
		XHR.sendAndLoad('$page', 'GET',X_SaveXRConfig);				
	}		
	
	EnableServiceCross();
		
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function backend_form_add(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();

	
	$html="
	<div id='backnddiv'>
	<table style='width:100%'>
	<tr>	
	<td class=legend style='font-size:13px'>{servername}:</td>
	<td>". Field_text("servername",null,"font-size:13px;padding:3px",null,null,null,false,"AddBckFormCheck(event)")."</td>
	</tr>
	<tr>	
	<td class=legend style='font-size:13px'>{listen_port}:</td>
	<td>". Field_text("server_port",25,"font-size:13px;padding:3px",null,null,null,false,"AddBckFormCheck(event)")."</td>
	</tr>
	<tr>
		<TD colspan=2 align='right'><hr>". button("{add}","AddBckForm()")."</td>
	</tr>
	</table>
	</div>
";
	
	echo $tpl->_ENGINE_parse_body($html);

	
}

function events(){
	$page=CurrentPageName();
	$tpl=new templates();		

	$html="<div id='cross-events' style='width:100%;height:550px;overflow:auto'></div>
	<div style='float:right'>". imgtootltip("refresh-32.png",'{refresh}','RefreshCrossEvents()')."</div>
	<script>
		function RefreshCrossEvents(){
			LoadAjax('cross-events','$page?cross-events=yes');
		
		}
		RefreshCrossEvents();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function events_details(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();	
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?crossroads-events=yes")));
	if(!is_array($datas)){echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");return;}
	
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#^([0-9]+)\s+[A-Z]+#",$ligne,$re)){
			$ligne=str_replace($re[1],"",$ligne);
		}
		$html[]="<div><code>". htmlspecialchars($ligne)."</div></code>";
	}

	echo @implode("",$html);
	
}

function cross_notify(){
	$sock=new sockets();
	$users=new usersMenus();
	$tpl=new templates();
	$MAIN=unserialize(base64_decode($sock->GET_INFO("CrossRoadsParams")));
	if(!is_numeric($MAIN["PARAMS"]["listen_port"])){$MAIN["PARAMS"]["listen_port"]=25;}
	if(count($MAIN["BACKENDS"])==0){
		echo $tpl->_ENGINE_parse_body(Paragraphe("server-warning-64.png","{no_backend_server}","{no_backend_server_service_disabled}","javascript:AddBackend()"));
	}

	if($users->POSTFIX_INSTALLED){
		$PostfixBindInterfacePort=$sock->GET_INFO("PostfixBindInterfacePort");
		$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
		if(!is_numeric($EnablePostfixMultiInstance)){	$EnablePostfixMultiInstance=0;}
		if(!is_numeric($PostfixBindInterfacePort)){	$PostfixBindInterfacePort=25;}
		if($EnablePostfixMultiInstance==0){
		if($MAIN["PARAMS"]["listen_port"]==$PostfixBindInterfacePort){
			echo "<p>&nbsp;</p>";
			echo $tpl->_ENGINE_parse_body(Paragraphe("server_network_error-64.png","{port_conflicts}","{ports_conflicts_with_local_postfix}"));
			}	
		}	
		
	}
	
	
	
}



