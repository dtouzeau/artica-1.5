<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.system.nics.inc');			
	
	if(posix_getuid()==0){die();}
	
	
	if(!CheckVpsRight()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["EnableLXCINLeftMenus"])){EnableLXCINLeftMenusSave();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["status-templates-list"])){status_template_list();exit;}
	if(isset($_GET["params"])){Parameters();exit;}
	if(isset($_GET["template-delete"])){template_delete();exit;}
	if(isset($_GET["events-service"])){	main_events();exit;}
	if(isset($_GET["LXCInstall"])){install_bridge();exit;}
	if(isset($_GET["LXCRemove"])){uninstall_bridge();exit;}
	if(isset($_GET["LXCVpsDir"])){save_dir_section();exit;}
	if(isset($_GET["lxc-checkconfig"])){lxc_checkconfig();exit;}
	if(isset($_GET["vps"])){vps_section();exit;}
	if(isset($_GET["vps-list"])){vps_list();exit;}
	if(isset($_GET["form-vps-js"])){vps_edit_js();exit;}
	if(isset($_GET["form-vps-tab"])){vps_edit_tab();exit;}
	if(isset($_GET["form-vps-params"])){vps_edit_params();exit;}
	if(isset($_GET["form-vps-edit"])){vps_edit_save();exit;}
	if(isset($_GET["form-vps-status"])){vps_status();exit;}
	if(isset($_GET["form-vps-status-service"])){vps_status_service();exit;}
	if(isset($_GET["form-vps-ps"])){vps_ps();exit;}
	if(isset($_GET["form-vps-ps-service"])){vps_ps_service();exit;}
	if(isset($_GET["form-vps-events-service"])){vps_events_service();exit;}
	if(isset($_GET["form-vps-restart"])){vps_restart_service();exit;}
	if(isset($_GET["form-vps-start"])){vps_start_service();exit;}
	if(isset($_GET["form-vps-stop"])){vps_stop_service();exit;}
	
	if(isset($_GET["form-vps-freeze"])){vps_freeze_service();exit;}
	if(isset($_GET["form-vps-unfreeze"])){vps_unfreeze_service();exit;}
	
	
	if(isset($_GET["form-vps-toolbox"])){vps_toolbox();exit;}
	if(isset($_GET["form-vps-events"])){vps_events();exit;}
	if(isset($_GET["form-vps-duplicate"])){vps_duplicate();exit;}
	if(isset($_GET["form-vps-artica"])){vps_artica_install();exit;}
	if(isset($_GET["form-vps-perf"])){vps_performances();exit;}
	if(isset($_GET["lxc_cgroup_memory_limit_in_bytes"])){vps_performances_save();exit;}
	
	
	if(isset($_GET["form-vps-delete"])){vps_delete();exit;}
	if(isset($_GET["status-templates-buttons"])){status_templates_button();exit;}
	if(isset($_GET["fedora-template"])){install_fedora();exit;}
	if(isset($_GET["debian-template"])){install_debian();exit;}
	
	js();
	
function CheckVpsRight(){
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager){return true;}
	if(!is_numeric($_REQUEST["ID"])){return false;}
	$sql="SELECT uid FROM lxc_machines WHERE ID={$_REQUEST["ID"]}";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["uid"]==$_SESSION["uid"]){return true;}
	return false;
		
}
	
function js(){
	$page=CurrentPageName();
	$q=new mysql();
	$q->check_vps_tables();	
	echo "$('#BodyContent').load('$page?tabs=yes');
	
	
var x_VpsDelete= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		vpsSearch();
	}		
	
	
	function vpsSartFront(ID){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-start','yes');
		XHR.appendData('ID',ID);
		document.getElementById('vps-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_VpsDelete);	
		}
		
	function vpsStopFront(ID){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-stop','yes');
		XHR.appendData('ID',ID);
		document.getElementById('vps-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_VpsDelete);	
		}		
	
	
	";
	
}	

function vps_edit_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="{add}::{vps_server}";
	if($_GET["ID"]>0){
		$q=new mysql();
		$sql="SELECT machine_name FROM lxc_machines WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$title="{vps_server}&raquo:&raquo;{$ligne["machine_name"]}";
	}
	
	$title=$tpl->_ENGINE_parse_body($title);
	echo "YahooWin('650','$page?form-vps-tab=yes&ID={$_GET["ID"]}','$title');";	
	
	
}

function vps_edit_tab(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();

	
	if($_GET["ID"]>0){
		$array["form-vps-status"]='{status}';
	}
	
	$array["form-vps-params"]='{parameters}';
	
	if($_GET["ID"]>0){
		if($users->AsVirtualBoxManager){$array["form-vps-perf"]='{performances}';}
		$array["form-vps-ps"]='{processes}';
		$array["form-vps-events"]='{events}';
	}
		
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_vps{$_GET["ID"]} style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_vps{$_GET["ID"]}').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	
}

function vps_toolbox(){
	$running=false;
	$frozen=false;
	$sock=new sockets();
	$page=CurrentPageName();	
	$tpl=new templates();	
	$run=trim($sock->getFrameWork("lxc.php?vps-running={$_GET["ID"]}"));
	if($run=="TRUE"){$running=true;}
	if($run=="FROZEN"){$frozen=true;}
	$restart=Paragraphe("restart-64.png","{VPS_RESTART}","{VPS_RESTART_TEXT}","javascript:vpsRestart{$_GET["ID"]}()");
	$start=Paragraphe("64-run.png","{VPS_START}","{VPS_START_TEXT}","javascript:vpsSart{$_GET["ID"]}()");
	$stop=Paragraphe("shutdown-64.png","{VPS_STOP}","{VPS_STOP_TEXT}","javascript:vpsStop{$_GET["ID"]}()");
	$freeze=Paragraphe("pause-64.png","{VPS_FREEZE}","{VPS_FREEZE_TEXT}","javascript:vpsFreeze{$_GET["ID"]}()");
	
	if($running){
		$start=Paragraphe("64-run-grey.png","{VPS_START}","{VPS_START_TEXT}");
	}else{
		$restart=Paragraphe("restart-64-grey.png","{VPS_RESTART}","{VPS_RESTART_TEXT}");
		$stop=Paragraphe("shutdown-computer-64-grey.png","{VPS_STOP}","{VPS_STOP_TEXT}");
		$freeze=Paragraphe("pause-64-grey.png","{VPS_FREEZE}","{VPS_FREEZE_TEXT}","");
	}
	
	if($frozen){
		$start=Paragraphe("64-run.png","{unfreeze}","{VPS_UNFREEZE_TEXT}","javascript:vpsUnfreeze{$_GET["ID"]}()");
		
	}
	
	
	echo $tpl->_ENGINE_parse_body("$start<br>$restart<br>$stop<br>$freeze");
}

function vps_status(){
$page=CurrentPageName();	
$tpl=new templates();
$duplicate_vserver_text_action=$tpl->javascript_parse_text("{duplicate_vserver_text_action}");
$restart=Paragraphe("restart-64.png","{VPS_RESTART}","{VPS_RESTART_TEXT}","javascript:vpsRestart{$_GET["ID"]}()");
$start=Paragraphe("64-run.png","{VPS_START}","{VPS_START_TEXT}","javascript:vpsSart{$_GET["ID"]}()");
$duplicate=Paragraphe("64-computer-alias.png","{duplicate_vserver}","{duplicate_vserver_text}","javascript:vpsDuplicate{$_GET["ID"]}()",null,340);
$artica_install=Paragraphe("artica-logo-64.png","{VPS_INSTALL_ARTICA}","{VPS_INSTALL_ARTICA_TEXT}","javascript:vpsArticaInstall{$_GET["ID"]}()",null,340);



$VPS_INSTALL_ARTICA_TEXT=$tpl->javascript_parse_text("{VPS_INSTALL_ARTICA_TEXT}");
$html="
<table style='width:100%'>
<tr>
	<td style='width:22Opx' valign='top'><div id='status-vps-{$_GET["ID"]}'></div><br>$duplicate<br>$artica_install</td>
	<td valign='top'><div id='toolbox-{$_GET["ID"]}'></div></td>
</tr>
</table>
<script>
	function RefreshVPSStatus{$_GET["ID"]}(){
		LoadAjax('status-vps-{$_GET["ID"]}','$page?form-vps-status-service=yes&ID={$_GET["ID"]}');
	}
	
	
var x_vpsRestart{$_GET["ID"]}= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('main_config_vps{$_GET["ID"]}');
		if(document.getElementById('main_config_vpssrv')){RefreshTab('main_config_vpssrv');}
	}		


	function vpsRestart{$_GET["ID"]}(){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-restart','yes');
		XHR.appendData('ID','{$_GET["ID"]}');
		document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});		
	}

	function vpsSart{$_GET["ID"]}(){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-start','yes');
		XHR.appendData('ID','{$_GET["ID"]}');
		document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});	
		}
		
	function vpsStop{$_GET["ID"]}(){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-stop','yes');
		XHR.appendData('ID','{$_GET["ID"]}');
		document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});	
		}

	function vpsFreeze{$_GET["ID"]}(){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-freeze','yes');
		XHR.appendData('ID','{$_GET["ID"]}');
		document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});		
	}
	
	function vpsUnfreeze{$_GET["ID"]}(){
		var XHR = new XHRConnection();
		XHR.appendData('form-vps-unfreeze','yes');
		XHR.appendData('ID','{$_GET["ID"]}');
		document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});		
	}

	function vpsDuplicate{$_GET["ID"]}(){
		var newip=prompt('$duplicate_vserver_text_action','');
		if(newip){
			var XHR = new XHRConnection();
			XHR.appendData('form-vps-duplicate','yes');
			XHR.appendData('ID','{$_GET["ID"]}');
			XHR.appendData('IP',newip);
			document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});			
		
		}
	
	}
	
	function vpsArticaInstall{$_GET["ID"]}(){
		if(confirm('$VPS_INSTALL_ARTICA_TEXT')){
			var XHR = new XHRConnection();
			XHR.appendData('form-vps-artica','yes');
			XHR.appendData('ID','{$_GET["ID"]}');
			document.getElementById('status-vps-{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_vpsRestart{$_GET["ID"]});		
		}
	
	}
	
	
	RefreshVPSStatus{$_GET["ID"]}()
	
</script>
";

	echo $tpl->_ENGINE_parse_body($html);		
	
	
}


function status_template_list(){
$page=CurrentPageName();	
$tpl=new templates();	
	$sock=new sockets();
	$templates=unserialize(base64_decode($sock->getFrameWork("lxc.php?lxc-templates=yes")));
	$refresh=imgtootltip("refresh-32.png","{refresh}","VPSRefreshTemplates()");
	$delete=$tpl->javascript_parse_text("{delete}");
	$html="
	<p>&nbsp;</p>
	<table style='width:100%'>
	<tr>
		<td width=100%'>
			<div style='font-size:16px;width:100%'>{templates}</div>
		</td>
		<td width=1%>$refresh</td>
	</tr>
	</table>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{template}</th>
		<th>{type}</th>
		<th>CPU</th>
		<th>{version}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($filename, $arraytpl) = each ($templates) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$delete=imgtootltip("delete-32.png","{delete}","DeleteTemplate('$filename')");
	
	$html=$html."
		<tr class=$classtr>
		<td style='font-size:14px'width=99% nowrap>$filename</td>
		<td style='font-size:14px' width=1% nowrap>{$arraytpl["TYPE"]}</td>
		<td style='font-size:14px' width=1% nowrap>{$arraytpl["PROC"]}</td>
		<td style='font-size:14px' width=1% nowrap>{$arraytpl["VERSION"]}</td>
		<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>
	
	<script>
	
var x_DeleteTemplate= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		if(document.getElementById('main_config_vpssrv')){RefreshTab('main_config_vpssrv');}
	}		


	function DeleteTemplate(filename){
		if(confirm('$delete '+filename+' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('template-delete',filename);
			document.getElementById('vps-templates-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_DeleteTemplate);
		}		
	}	
	
	VPSRefreshTemplatesButton();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function template_delete(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?tpl-delete={$_GET["template-delete"]}");
}


function vps_ps(){
$page=CurrentPageName();	
$tpl=new templates();

$html="
<div id='status-vps-ps-{$_GET["ID"]}' style='width:100%;height:450px'></div>
<script>
	function RefreshVPSPS{$_GET["ID"]}(){
		LoadAjax('status-vps-ps-{$_GET["ID"]}','$page?form-vps-ps-service=yes&ID={$_GET["ID"]}');
	}
	RefreshVPSPS{$_GET["ID"]}()
	
</script>
";

	echo $tpl->_ENGINE_parse_body($html);		
		
	
}

function vps_events(){
$page=CurrentPageName();	
$tpl=new templates();

$html="
<div id='status-vps-events-{$_GET["ID"]}' style='width:100%;height:450px'></div>
<script>
	function RefreshVPSEvents{$_GET["ID"]}(){
		LoadAjax('status-vps-events-{$_GET["ID"]}','$page?form-vps-events-service=yes&ID={$_GET["ID"]}');
	}
	RefreshVPSEvents{$_GET["ID"]}()
	
</script>
";

	echo $tpl->_ENGINE_parse_body($html);
}

function vps_events_service(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$data=unserialize(base64_decode($sock->getFrameWork("lxc.php?lxc-events={$_GET["ID"]}")));		
	$refesh=imgtootltip("32-refresh.png","{refresh}","RefreshVPSEvents{$_GET["ID"]}()");
	$html="

	<div style='width:100%;text-align:right'>$refesh</div>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";		
	
	while (list ($num, $ligne) = each ($data) ){
		if(trim($ligne)==null){continue;}
		if(strlen($ligne)<3){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."<tr class=$classtr>
			<td style='font-size:12px' width=100%><code>".htmlspecialchars($ligne)."</td>	
			</tr>";
	}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);	
}

function vps_ps_service(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$data=unserialize(base64_decode($sock->getFrameWork("lxc.php?lxc-ps={$_GET["ID"]}")));	
	
	$refesh=imgtootltip("32-refresh.png","{refresh}","RefreshVPSPS{$_GET["ID"]}()");
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>$refesh</th>
		<th>PID</th>
		<th>%CPU</th>
		<th>%MEM</th>
		<th>COMMAND</th>
	</tr>
</thead>
<tbody class='tbody'>";	

	while (list ($num, $ligne) = each ($data) ){
		if(!preg_match("#.+?\s+(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9\.]+).+?([0-9:]+)\s+([0-9:]+)\s+(.+?)$#",$ligne,$re)){
		
			continue;
		}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$VSZ=$re[5];
		$RSS=$re[6];
		$START=$re[7];
		$TIME=$re[8];
		$html=$html."<tr class=$classtr>
			<td style='font-size:12px' width=1%>{$re[1]}</td>
			<td style='font-size:12px' width=1% align='center'>{$re[2]}</td>
			<td style='font-size:12px' width=1% align='center'>{$re[3]}</td>
			<td style='font-size:12px' width=1% align='center'>{$re[4]}</td>
			<td style='font-size:11px' width=99%>{$re[9]}</td>
		</tr>";
		}	
		
	$html=$html."</table>";

	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function vps_status_service(){
		$q=new mysql();
		$sql="SELECT machine_name FROM lxc_machines WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		
	$sock=new sockets();
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshVPSStatus{$_GET["ID"]}()")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();
	$data=base64_decode($sock->getFrameWork("lxc.php?single-status={$_GET["ID"]}"));
	$ini->loadString($data);
	$status=DAEMON_STATUS_ROUND("APP_LXC:vps-{$_GET["ID"]}",$ini,"vps-{$_GET["ID"]}:{$ligne["machine_name"]}",null,0).$refresh;
	echo $tpl->_ENGINE_parse_body($status)."
	<script>
		LoadAjax('toolbox-{$_GET["ID"]}','$page?form-vps-toolbox=yes&ID={$_GET["ID"]}');
	</script>
	";
	
}

function vps_edit_params(){
		$sock=new sockets();
		$LXCBridged=$sock->GET_INFO("LXCBridged");
		$LXCInterface=$sock->GET_INFO("LXCInterface");
		if(!is_numeric($LXCBridged)){$LXCBridged=0;}
		$page=CurrentPageName();
		$users=new usersMenus();
		$tpl=new templates();
		$q=new mysql();
		if($users->AsVirtualBoxManager){$AsVirtualBoxManager=1;}else{$AsVirtualBoxManager=0;}
		$sql="SELECT * FROM lxc_machines WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$button_text="{apply}";
		if($_GET["ID"]==0){$button_text="{add}";}	
		$machine_name=$ligne["machine_name"];
		$hostname=$ligne["hostname"];
		$rootpwd=$ligne["rootpwd"];
		$ChangeMac=$ligne["ChangeMac"];
		if($ligne["MacAddr"]==null){
			$mc=new MACAddress();
			$mc->separator=":";
			$ligne["MacAddr"]=$mc->_generateMAC2();	
		}
		$MacAddr=explode(":",$ligne["MacAddr"]);
		$templates=unserialize(base64_decode($sock->getFrameWork("lxc.php?lxc-templates=yes")));
		$UsePhys=$ligne["UsePhys"];
		$PhysNic=$ligne["PhysNic"];
		$uid=$ligne["uid"];
		$net=new networking();
		$interfaces=$net->Local_interfaces(true);
		while (list ($iet, $iet2) = each ($interfaces) ){
			$sql="SELECT PhysNic FROM lxc_machines WHERE ID!={$_GET["ID"]} AND PhysNic='$iet'";
				if($iet==$LXCInterface){continue;}
				if($iet=="eth0"){continue;}
				if($iet=="br5"){continue;}
				$lignePhysNic=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
				if($lignePhysNic["PhysNic"]<>null){continue;}
				$interfacesR[$iet]=$iet;
		}
		$interfacesR[$PhysNic]=$PhysNic;
		$interfacesR[null]="{select}";
		
		$physCardTable="
		<tr>
			<td class=legend>{UsePhysCard}:</td>
			<td>". Field_checkbox("UsePhys",1,$UsePhys,"ChangeMacCheck()")."</td>
		</tr>
		<tr>
			<td class=legend>{nic}:</td>
			<td>". Field_array_Hash($interfacesR,"PhysNic",$PhysNic,"style:font-size:14px;padding:3px")."</td>
		</tr>		
		
		";
		
		
		
		
		//AA:86:D7:23:0E:51
		$macaddrtable="
		<table>
		<tr>
			<td class=legend>{change_mac_address}:</td>
			<td>". Field_checkbox("ChangeMac",1,$ChangeMac,"ChangeMacCheck()")."</td>
		</tr>
		</tr>
		</table>
			
		<table style='width:1%'>
		<tr>
		<td>
			<td>". Field_text("MAC1",$MacAddr[0],"font-size:14px;padding:3px;width:26px")."</td>
			<td style='font-size:14px'>:</td>
			<td>". Field_text("MAC2",$MacAddr[1],"font-size:14px;padding:3px;width:26px")."</td>
			<td style='font-size:14px'>:</td>
			<td>". Field_text("MAC3",$MacAddr[2],"font-size:14px;padding:3px;width:26px")."</td>
			<td style='font-size:14px'>:</td>
			<td>". Field_text("MAC4",$MacAddr[3],"font-size:14px;padding:3px;width:26px")."</td>
			<td style='font-size:14px'>:</td>
			<td>". Field_text("MAC5",$MacAddr[4],"font-size:14px;padding:3px;width:26px")."</td>
			<td style='font-size:14px'>:</td>
			<td>". Field_text("MAC6",$MacAddr[5],"font-size:14px;padding:3px;width:26px")."</td>
			
		</tr>														
		</table>";
		
		while (list ($filename, $arraytpl) = each ($templates) ){
			$TPLR[$filename]="{$arraytpl["TYPE"]} {$arraytpl["PROC"]} - v{$arraytpl["VERSION"]}";
			
		}
		
		$TPLR[null]="{select}";
		$field_template=Field_array_Hash($TPLR,'lxc_template',$ligne["template"],"CheckTemplate()",null,0,"font-size:13px;padding:3px");
		if($_GET["ID"]==0){
			$template="
			<tr>
				<td class=legend>{template}:</td>
				<td>$field_template</td>
			</tr>";
		}
		
$ipaddr="
<tr>
	<td class=legend>{ipaddr}:</td>
	<td>". Field_text("ipaddr",$ligne["ipaddr"],"font-size:14px;padding:3px;width:120px")."</td>
</tr>";		

 if($LXCBridged==0){
 	$sql="SELECT nic,ID,ipaddr FROM nics_virtuals WHERE org='LXC-INTERFACES' ORDER BY ID";
 	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne3=mysql_fetch_array($results,MYSQL_ASSOC)){
		$sql="SELECT VirtualInterface FROM lxc_machines WHERE VirtualInterface='{$ligne3["nic"]}:{$ligne3["ID"]}'";
		$ligne2=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["VirtualInterface"]<>null){continue;}
		$Interfaces["{$ligne3["nic"]}:{$ligne3["ID"]}"]="{$ligne3["nic"]}:{$ligne3["ID"]} ({$ligne3["ipaddr"]})";
		
	}
	
if(count($Interfaces)>0){	
	$ipaddr="<tr>
		<td class=legend>{ipaddr}:</td>
		<td colspan=2>". Field_array_Hash($Interfaces,"VirtualInterface",$ligne["VirtualInterface"],"style:font-size:14px;padding:3px")."</td>
	</tr>";
}else{
	if(preg_match("#(.+?):(.+)#",$ligne["VirtualInterface"],$re)){
		$sql="SELECT nic,ID,ipaddr FROM nics_virtuals WHERE ID='{$re[2]}'";
		$ligne2=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	}
	$ipaddr="<tr>
		<td class=legend>{ipaddr}:</td>
		<td colspan=2>". Field_hidden("VirtualInterface",$ligne["VirtualInterface"])."<span style='font-size:14px'>{$ligne["VirtualInterface"]} ({$ligne2["ipaddr"]})</span></td>
		
	</tr>";
}		
	
 	
 }		
		
$BrowseUser="<input type='button' OnClick=\"javascript:Loadjs('MembersBrowse.php?field-user=lxc-member&OnlyUsers=yes');\" value='{browse}...'>";
if($AsVirtualBoxManager==0){$BrowseUser="&nbsp;";}
		
$html="
<div id='lxc_host_{$_GET["ID"]}'>
<table style='width:100%' class=form>
$template
<tr>
	<td class=legend>{member}:</td>
	<td>". Field_text("lxc-member",$uid,"font-size:14px;padding:3px;width:210px")."</td>
	<td>$BrowseUser</td>
</tr>
<tr>
	<td class=legend>{computer_name}:</td>
	<td colspan=2>". Field_text("machine_name",$machine_name,"font-size:14px;padding:3px;width:210px")."</td>
	
</tr>
<tr>
	<td class=legend>{hostname}:</td>
	<td colspan=2>". Field_text("hostname",$hostname,"font-size:14px;padding:3px;width:210px")."</td>
	
</tr>
$ipaddr
$physCardTable
<tr>
	<td class=legend>{ComputerMacAddress}:</td>
	<td colspan=2>$macaddrtable</td>
</tr>
<tr>
	<td class=legend>{rootpwd}:</td>
	<td colspan=2>". Field_password("rootpwd",$rootpwd,"font-size:14px;padding:3px;width:210px")."</td>
</tr>




<tr>
	<td colspan=3 align='right'><hr>". button($button_text,"SaveVPSServerSingle{$_GET["ID"]}()")."</td>
</tR>
</table>



<hr>
<div style='font-size:16px'>{start_options}</div>
<div class=explain>{lxc_start_options_text}</div>
<table style='width:100%' class=form>
<tr>
	<td class=legend>{autostart}:</td>
	<td>". Field_checkbox("autostart",1,$ligne["autostart"])."</td>
	<td width=1%>". help_icon("{lxc_autostart_explain}")."</td>
</tr>
<tr>
	<td class=legend>{enable_service}:</td>
	<td>". Field_checkbox("enabledvps",1,$ligne["enabled"])."</td>
	<td width=1%>&nbsp;</td>
</tr>
<tr>
	<td colspan=2 align='right'><hr>". button($button_text,"SaveVPSServerSingle{$_GET["ID"]}()")."</td>
</tR>
</table>


<script>
var x_SaveVPSServerSingle{$_GET["ID"]}= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		var ID={$_GET["ID"]};
		if(ID==0){YahooWinHide();}else{RefreshTab('main_config_vps{$_GET["ID"]}');}
		RefreshTab('main_config_vpssrv');
	}		


	function SaveVPSServerSingle{$_GET["ID"]}(){
			CheckTemplate();
			var XHR = new XHRConnection();	
			var ID={$_GET["ID"]};
			if(ID==0){
				var tpl=document.getElementById('lxc_template').value;
				if(tpl.length==0){return;}
				XHR.appendData('template',tpl);
			}
			var rootpwd=document.getElementById('rootpwd').value;
			XHR.appendData('form-vps-edit','yes');
			XHR.appendData('machine_name',document.getElementById('machine_name').value);
			if(document.getElementById('ipaddr')){
				XHR.appendData('ipaddr',document.getElementById('ipaddr').value);
			}
			
			if(document.getElementById('VirtualInterface')){
				XHR.appendData('VirtualInterface',document.getElementById('VirtualInterface').value);
			}			
			
			if(document.getElementById('ChangeMac').checked){XHR.appendData('ChangeMac',1);}else{XHR.appendData('ChangeMac',0);}
			if(document.getElementById('UsePhys').checked){XHR.appendData('UsePhys',1);}else{XHR.appendData('UsePhys',0);}
			
			
			var mcaddr=document.getElementById('MAC1').value+':'+document.getElementById('MAC2').value+':'+document.getElementById('MAC3').value+':';
			mcaddr=mcaddr+document.getElementById('MAC4').value+':'+document.getElementById('MAC5').value+':'+document.getElementById('MAC6').value
			
			XHR.appendData('MacAddr',mcaddr);
			XHR.appendData('rootpwd',rootpwd);
			XHR.appendData('hostname',document.getElementById('hostname').value);
			XHR.appendData('PhysNic',document.getElementById('PhysNic').value);
			XHR.appendData('member',document.getElementById('lxc-member').value);
			
			
			
			if(document.getElementById('autostart').checked){XHR.appendData('autostart',1);}else{XHR.appendData('autostart',0);}
			if(document.getElementById('enabledvps').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
			XHR.appendData('ID','{$_GET["ID"]}');
			document.getElementById('lxc_host_{$_GET["ID"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_SaveVPSServerSingle{$_GET["ID"]});		
	}

	function CheckTemplate(){
		var ID={$_GET["ID"]};
		ChangeMacCheck();
		if(ID>0){CheckTemplateRights();return;}
		document.getElementById('machine_name').disabled=true;
		if(document.getElementById('ipaddr')){document.getElementById('ipaddr').disabled=true;}
		if(document.getElementById('VirtualInterface')){document.getElementById('VirtualInterface').disabled=true;}
		document.getElementById('rootpwd').disabled=true;
		document.getElementById('hostname').disabled=true;
		document.getElementById('autostart').disabled=true;
		document.getElementById('enabledvps').disabled=true;
		var tpl=document.getElementById('lxc_template').value;
		if(tpl.length==0){
			CheckTemplateRights();
			return;
		}
		if(document.getElementById('ipaddr')){document.getElementById('ipaddr').disabled=false;}
		if(document.getElementById('VirtualInterface')){document.getElementById('VirtualInterface').disabled=false;}
		document.getElementById('rootpwd').disabled=false;
		document.getElementById('hostname').disabled=false;
		document.getElementById('autostart').disabled=false;
		document.getElementById('enabledvps').disabled=false;
		document.getElementById('machine_name').disabled=false;
		document.getElementById('lxc_template').disabled=false;	
		CheckTemplateRights();
		
	
	}
	
	function CheckTemplateRights(){
		var AsVirtualBoxManager=$AsVirtualBoxManager;
		if(document.getElementById('ipaddr')){document.getElementById('ipaddr').disabled=true;}
		document.getElementById('lxc-member').disabled=true;
		document.getElementById('UsePhys').disabled=true;
		document.getElementById('ChangeMac').disabled=true;
		if(AsVirtualBoxManager==0){return;}
		if(document.getElementById('ipaddr')){document.getElementById('ipaddr').disabled=false;}
		document.getElementById('lxc-member').disabled=false;
		document.getElementById('UsePhys').disabled=false;
		document.getElementById('ChangeMac').false;
	}
	
	
		function ChangeMacCheck(){
			if(!document.getElementById('UsePhys').checked){
				document.getElementById('PhysNic').disabled=true;
			}else{
				document.getElementById('PhysNic').disabled=false;
			}
		
		
		
		
			document.getElementById('MAC1').disabled=true;
			document.getElementById('MAC2').disabled=true;
			document.getElementById('MAC3').disabled=true;
			document.getElementById('MAC4').disabled=true;
			document.getElementById('MAC5').disabled=true;
			document.getElementById('MAC6').disabled=true;
			if(!document.getElementById('ChangeMac').checked){return;}
			document.getElementById('MAC1').disabled=false;
			document.getElementById('MAC2').disabled=false;
			document.getElementById('MAC3').disabled=false;
			document.getElementById('MAC4').disabled=false;
			document.getElementById('MAC5').disabled=false;
			document.getElementById('MAC6').disabled=false;			
			
		
		}	
	
	CheckTemplate();

</script>

";


	echo $tpl->_ENGINE_parse_body($html);
	
}

function vps_edit_save(){
	$ID=$_GET["ID"];
	if($_GET["machine_name"]==null){$_GET["machine_name"]=time();}
	if($_GET["hostname"]==null){$_GET["hostname"]=time().".localhost.localdomain";}
	if($_GET["rootpwd"]==null){$_GET["rootpwd"]=base64_encode("root");}
	$sql="UPDATE lxc_machines SET 
		machine_name='{$_GET["machine_name"]}',
		VirtualInterface='{$_GET["VirtualInterface"]}',
		ipaddr='{$_GET["ipaddr"]}',
		uid='{$_GET["member"]}',
		autostart='{$_GET["autostart"]}',
		enabled='{$_GET["enabled"]}',
		rootpwd='{$_GET["rootpwd"]}',
		state='update',
		MacAddr='{$_GET["MacAddr"]}',
		ChangeMac='{$_GET["ChangeMac"]}',
		hostname='{$_GET["hostname"]}',
		UsePhys='{$_GET["UsePhys"]}',
		PhysNic='{$_GET["PhysNic"]}'
		WHERE ID='{$_GET["ID"]}'";
	

	
	$sqladd="INSERT INTO lxc_machines (machine_name,ipaddr,uid,state,autostart,rootpwd,hostname,enabled,template,VirtualInterface,MacAddr,ChangeMac,UsePhys,PhysNic)
	VALUES('{$_GET["machine_name"]}','{$_GET["ipaddr"]}','{$_GET["member"]}','create',
	'{$_GET["autostart"]}','{$_GET["rootpwd"]}','{$_GET["hostname"]}',1,'{$_GET["template"]}','{$_GET["VirtualInterface"]}','{$_GET["MacAddr"]}',
	'{$_GET["ChangeMac"]}',
	'{$_GET["UsePhys"]}',
	'{$_GET["PhysNic"]}'
	)";
	if($ID==0){$sql=$sqladd;}
	$q=new mysql();
	$q->check_vps_tables();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		echo $q->mysql_error;
		return;
	}
	$sock=new sockets();
	writelogs("-> lxc.php?vps-reconfig={$_GET["ID"]}",__FUNCTION__,__FILE__,__LINE__);
	$sock->getFrameWork("lxc.php?vps-reconfig={$_GET["ID"]}");	
	}

	

function main_events(){
	$tpl=new templates();
	$f=explode("\n",@file_get_contents("ressources/logs/vserver.daemon.log"));
$html="
<div style='text-align:right;width:100%'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_vpssrv')")."</div>
<p>&nbsp;</p>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	

while (list ($num, $ligne) = each ($f) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:12px'>$ligne</td>
		</tr>
		
		";
}
		
echo $tpl->_ENGINE_parse_body($html."</table>");
		
		
}	
	
function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["params"]='{parameters}';
	$array["vps"]='{VPS_SERVERS}';
	$array["events-service"]='{events}';
	

		
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_vpssrv style='width:100%;height:950px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_vpssrv').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
}	


function status(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$version=$sock->getFrameWork("lxc.php?lxc-version=yes");
	$UPDATE_DEBIAN_TEMPLATE_TEXT=$tpl->javascript_parse_text("{UPDATE_DEBIAN_TEMPLATE_TEXT}");
	$UPDATE_FEDORA_TEMPLATE_TEXT=$tpl->javascript_parse_text("{UPDATE_FEDORA_TEMPLATE_TEXT}");
	$mysql=new mysql();
	$error="<div style='color:red'>".$mysql->check_vps_tables()."</div>";
	$EnableLXCINLeftMenus=$sock->GET_INFO("EnableLXCINLeftMenus");
	if(!is_numeric($EnableLXCINLeftMenus)){$EnableLXCINLeftMenus=1;}
	
	$html="
	<div style='font-size:16px' style='text-align:right'>LXC V$version</div>$error
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><div id='lxc-checkconfig'></div></td>
	<td width=99% valign='top'>
	
		<div class=explain>{LXC_ABOUT}</div>
		<table style='width:100%'>
		<tr>
			<td class=legend>{enable_in_left_menus}:</td>
			<td>". Field_checkbox("EnableLXCINLeftMenus",1,$EnableLXCINLeftMenus,"EnableLXCINLeftMenusCheck()")."</td>
		</tr>
		</table>
	
	</td>
	</tr>
	</table>
	
	<div id='vps-templates-buttons'></div>
	
	<div id='vps-templates-list' style='width:100%;height:250px;overflow:auto'></div>
	
	<script>

var x_EnableLXCINLeftMenusCheck= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		CacheOff();
		
	}	
	
	function EnableLXCINLeftMenusCheck(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableLXCINLeftMenus').checked){
		XHR.appendData('EnableLXCINLeftMenus','1');}else{XHR.appendData('EnableLXCINLeftMenus','0');}
		XHR.sendAndLoad('$page', 'GET',x_EnableLXCINLeftMenusCheck);	
	}
	
	
var x_UpdateFedoraTemplate= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		VPSRefreshTemplatesButton();
		
	}		


	function UpdateFedoraTemplate(){
		if(confirm('$UPDATE_FEDORA_TEMPLATE_TEXT ?')){
			var XHR = new XHRConnection();
			XHR.appendData('fedora-template','yes');
			XHR.sendAndLoad('$page', 'GET',x_UpdateFedoraTemplate);		
		}
	}

	function UpdateDebianTemplate(){
		if(confirm('$UPDATE_DEBIAN_TEMPLATE_TEXT ?')){
			var XHR = new XHRConnection();
			XHR.appendData('debian-template','yes');
			XHR.sendAndLoad('$page', 'GET',x_UpdateFedoraTemplate);		
		}
	}

	function VPSRefreshTemplates(){
		LoadAjax('vps-templates-list','$page?status-templates-list=yes');
	
	}
	
	function VPSRefreshTemplatesButton(){
		LoadAjax('vps-templates-buttons','$page?status-templates-buttons=yes');
	}
	
	
	function VPSRefreshCheckConfig(){
		LoadAjax('lxc-checkconfig','$page?lxc-checkconfig=yes');
	}	
	
	VPSRefreshTemplates();
	VPSRefreshCheckConfig();
</script>	
	";;
		
	echo $tpl->_ENGINE_parse_body($html);
}

function status_templates_button(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$install_debian=Paragraphe("DEBIAN_mirror-64.png","{DEBIAN_TEMPLATE}","{UPDATE_DEBIAN_TEMPLATE_TEXT}","javascript:UpdateDebianTemplate()",null,300);
	$install_fedora=Paragraphe("FEDORA.png","{FEDORA_TEMPLATE}","{UPDATE_FEDORA_TEMPLATE_TEXT}","javascript:UpdateFedoraTemplate()",null,300);
	
	$sock=new sockets();
	$status_debian=$sock->getFrameWork("lxc.php?artica-make-status=APP_LXC_DEBIAN_TEMPLATE");
	$status_fedora=$sock->getFrameWork("lxc.php?artica-make-status=APP_LXC_FEDORA_TEMPLATE");
	
	if($status_debian=="TRUE"){
		$install_debian=Paragraphe("DEBIAN_mirror-64-grey.png","{DEBIAN_TEMPLATE}","{downloading} $pourc...",null,null,300);
		
	}
	
	if($status_fedora=="TRUE"){
		$install_fedora=Paragraphe("FEDORA-grey.png","{FEDORA_TEMPLATE}","{downloading}...",null,null,300);
	}	
	
	
	$html="
	<div style='width:100%;text-align:right'>". imgtootltip("refresh-32.png","{refresh}","VPSRefreshTemplatesButton()")."</div>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$install_debian</td>
		<td  valign='top'>$install_fedora</td>
	</tr>
	</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function install_fedora(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?artica-make=APP_LXC_FEDORA_TEMPLATE");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{UPDATE_LXC_TEMPLATE_PERFOMED}");
	}
function install_debian(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?artica-make=APP_LXC_DEBIAN_TEMPLATE");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{UPDATE_LXC_TEMPLATE_PERFOMED}");
	}



function Parameters(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$LXCEnabled=$sock->GET_INFO("LXCEnabled");
	$LXCInterface=$sock->GET_INFO("LXCInterface");
	$LXCVpsDir=$sock->GET_INFO("LXCVpsDir");
	if($LXCVpsDir==null){$LXCVpsDir="/home/vps-servers";}
	$tcp=new networking();
	$Local_interfaces=$tcp->Local_interfaces();
	unset($Local_interfaces["br5"]);
	unset($Local_interfaces["lo"]);
	if($LXCInterface<>null){$Local_interfaces[$LXCInterface]=$LXCInterface;}
	
	$LXCEthLocked=$sock->GET_INFO("LXCEthLocked");
	if(!is_numeric($LXCEthLocked)){$LXCEthLocked=0;}
	$button=button("{install}","SaveLXCNet()");
	if($LXCEthLocked==1){$button=button("{uninstall}","UnlinkLXCNet()");}
	$LXC_NET_RESTART_ASK=$tpl->javascript_parse_text("{LXC_NET_RESTART_ASK}");
	$lock_install=0;
	$nicClass=new system_nic();
	if($nicClass->unconfigured){
		$lock_install=1;
		$error="<div class=explain style='color:red'>{NIC_UNCONFIGURED_ERROR}</div>";
		
	}	
	
	
$html="	
$error
<div style='text-align:right;width:100%'>". imgtootltip("refresh-32.png","{refresh}","RefreshTab('main_config_vpssrv')")."</div>
<div style='font-size:16px'>{bridge_setup}</div>
<div class=explain>{bridge_setup_lxc_explain}</div>
<div id='lxcnets'>
<table style='width:100%' class=form>
	<tr>
		<td class=legend>{local_interface_bridge}:</td>
		<td>". Field_array_Hash($Local_interfaces,"LXCInterface",$LXCInterface,null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>$button</td>
	</tr>
</table>

<hr>

<div id='lxcDirsDiv'>
<table style='width:100%'>
<tr>
	<td class=legend>{vps_directory}:</td>
	<td>". Field_text("LXCVpsDir",$LXCVpsDir,"font-size:13px;padding:3px;width:210px")."</td>
</tr>
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SaveLXCDir()")."</td>
	</tr>
</table>
</div>

</div>
<script>
var x_SaveLXCNet= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('main_config_vpssrv');
	}		


	function SaveLXCNet(){
		var lock_install=$lock_install;
		if(lock_install==1){return;}
		if(confirm('$LXC_NET_RESTART_ASK')){
			var XHR = new XHRConnection();
			XHR.appendData('LXCEnabled','1');
			XHR.appendData('LXCInstall','1');
			XHR.appendData('LXCInterface',document.getElementById('LXCInterface').value);
			document.getElementById('lxcnets').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_SaveLXCNet);		
		}
	}
	
	function UnlinkLXCNet(){
		if(confirm('$LXC_NET_RESTART_ASK')){
			var XHR = new XHRConnection();
			XHR.appendData('LXCEnabled','0');
			XHR.appendData('LXCRemove','yes');
			document.getElementById('lxcnets').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_SaveLXCNet);	
		}		
	}
	
	function CheckLXCNetForm(){
		var LXCEthLocked=$LXCEthLocked;
		document.getElementById('LXCInterface').disabled=true;
		if(LXCEthLocked==0){
			document.getElementById('LXCInterface').disabled=false;		
		}
	}
	
	function SaveLXCDir(){
		var XHR = new XHRConnection();
		XHR.appendData('LXCVpsDir',document.getElementById('LXCVpsDir').value);
		document.getElementById('lxcDirsDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',x_SaveLXCNet);		
	}
	
CheckLXCNetForm();
</script>

";

echo $tpl->_ENGINE_parse_body($html);


}

function vps_section(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$html="
	
	<center>
		<table class=form>
		<tr>
			<td class=legend>{search}:</td>
			<td>". Field_text("vps-search",null,"font-size:14px;padding:3px",null,null,null,false,"vpsSearchCheck(event)")."</td>
		</tr>
		</table>
	</center>
	<p>&nbsp;</p>
	<div id='vps-list' style='width:100%;height:450px;overflow:auto'></div>
	
	
	
	<script>
		function vpsSearchCheck(e){if(checkEnter(e)){vpsSearch();}}
		
		function vpsSearch(){
			var s=escape(document.getElementById('vps-search').value);
			LoadAjax('vps-list','$page?vps-list='+s);
		}
	vpsSearch();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function vps_list(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();	
	$search=$_GET["vps-list"];
	$search="*$search*";
	$search=str_replace("**","*",$search);
	$search=str_replace("*","%",$search);
	$sql="SELECT * FROM lxc_machines WHERE machine_name LIKE '$search' ORDER BY machine_name LIMIT 0,50";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$LXC_NET_DELETE_ASK=$tpl->_ENGINE_parse_body("{LXC_NET_DELETE_ASK}");
	
	$add=imgtootltip("32-plus.png","{add}","Loadjs('$page?form-vps-js=yes&ID=0')");
	$refresh=imgtootltip("32-refresh.png","{refresh}","vpsSearch()");
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>$add</th>
		<th>{hostname}</th>
		<th>{member}</th>
		<th>{state}</th>
		<th>$refresh</th>
	</tr>
</thead>
<tbody class='tbody'>";	

while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$running=false;
		$color="black";
		$isartica=null;
		$link="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?form-vps-js=yes&ID={$ligne["ID"]}')\" style='font-size:14px;text-decoration:underline'>";
		$delete=imgtootltip("delete-32.png","{delete}","VpsDelete('{$ligne["ID"]}')");
		$select=imgtootltip("30-computer.png","{edit}","Loadjs('$page?form-vps-js=yes&ID={$ligne["ID"]}')");
		$text_uid=$ligne["uid"];
		$org_state=$ligne["state"];
		if($ligne["state"]=="create"){$ligne["state"]="{vps_create} {$ligne["events"]}";$link=null;$color="#CCCCCC";$select="&nbsp;";}
		if($ligne["state"]=="delete"){$ligne["state"]="{deleting}";$link=null;$color="#CCCCCC";$delete="&nbsp;";$select="&nbsp;";}
		if($ligne["state"]=="configure"){$ligne["state"]="{vps_configure}";$link=null;$color="#CCCCCC";$delete="&nbsp;";$select="&nbsp;";}
		if($ligne["state"]=="artica-install"){$ligne["state"]="{VPS_INSTALL_ARTICA}";;$link=null;$color="#CCCCCC";$delete="&nbsp;";$select="&nbsp;";}
		if($ligne["state"]=="update"){if(!$running){$ligne["state"]="{stopped}";}}
		if($ligne["state"]=="updated"){if(!$running){$ligne["state"]="{stopped}";}}
		if($ligne["state"]=="updated"){if($running){$ligne["state"]="{running}";}}
		if($ligne["state"]=="update"){if(!$running){$ligne["state"]="{running}";}}
		if($ligne["state"]=="installed"){if(!$running){$ligne["state"]="{stopped}";}}
		if($ligne["state"]=="installed"){if($running){$ligne["state"]="{running}";}}
		
		// artica-logo-32.png
		if($ligne["state"]=="installed"){$ligne["state"]="{installed}";}
		if(preg_match("#dup:#",$ligne["state"])){$ligne["state"]="{duplicating}";$link=null;$color="#CCCCCC";$delete="&nbsp;";$select="&nbsp;";}
		
		
		
		
		
		
		
		if($ligne["uid"]==null){$text_uid="&nbsp;";}
		$img_state=imgtootltip("okdanger32.png","{start}","vpsSartFront({$ligne["ID"]})");
		$run=trim($sock->getFrameWork("lxc.php?vps-running={$ligne["ID"]}"));
		$isartica=trim($sock->getFrameWork("lxc.php?IsArtica=". urlencode($ligne["root_directory"])."&ID={$ligne["ID"]}"));
		if($isartica<>null){
			$isartica_text="&nbsp;&nbsp;|&nbsp;&nbsp;Artica v$isartica";
			if($select<>"&nbsp;"){$select=imgtootltip("artica-logo-32.png","{edit}","Loadjs('$page?form-vps-js=yes&ID={$ligne["ID"]}')");}
		}
		
		if($run=="TRUE"){
			$img_state=imgtootltip("ok32.png","{stop}","vpsStopFront({$ligne["ID"]})");
			$ligne["state"]="{running}";
		}
		
		if($run=="FROZEN"){
			$img_state=imgtootltip("pause-32.png","{suspend}","vpsStopFront({$ligne["ID"]})");
			$ligne["state"]="{running}";
		}		
		
		
		
		$html=$html."
		<tr class=$classtr>
		<td width=1%>$select</td>
		<td>$link<span style='color:$color;font-size:14px;font-weight:bold'>{$ligne["machine_name"]}</span></a>
			<div><i style='color:black;font-size:12px'>{$ligne["ipaddr"]}&nbsp;&nbsp;|&nbsp;&nbsp;$org_state ({$ligne["state"]})&nbsp;&nbsp;|&nbsp;&nbsp;vps-{$ligne["ID"]}$isartica_text</i></div>
		</td>
		<td><span style='color:$color;font-size:14px'>$text_uid</span></td>
		<td width=1% nowrap>$img_state</td>
		<td width=1%>$delete</td>
		</tr>
		";
}
//http://blog.bodhizazen.net/linux/lxc-configure-fedora-containers/
$html=$html."</table>

<script>



	function VpsDelete(ID){
		if(confirm('$LXC_NET_DELETE_ASK')){
			var XHR = new XHRConnection();
			XHR.appendData('form-vps-delete','yes');
			XHR.appendData('ID',ID);
			document.getElementById('vps-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
			XHR.sendAndLoad('$page', 'GET',x_VpsDelete);		
		}
	}
	
	
</script>	
";

echo $tpl->_ENGINE_parse_body($html);

}


function save_dir_section(){
	$sock=new sockets();
	$sock->SET_INFO("LXCVpsDir",$_GET["LXCVpsDir"]);	
	$sock->getFrameWork("lxc.php?check-master=yes");
}

function install_bridge(){
	$sock=new sockets();

	$sock->SET_INFO("LXCInterface",$_GET["LXCInterface"]);
	$sock->getFrameWork("lxc.php?install-bridge=yes");
	
}
function uninstall_bridge(){
	$sock=new sockets();
	$sock->SET_INFO("LXCEnabled",0);
	$sock->SET_INFO("LXCEthLocked",0);
	$sock->getFrameWork("lxc.php?uninstall-bridge=yes");	
	
}
function vps_restart_service(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?vps-restart={$_GET["ID"]}");
	
}

function vps_start_service(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?vps-start={$_GET["ID"]}");	
}

function vps_freeze_service(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?vps-freeze={$_GET["ID"]}");		
	
}
function vps_unfreeze_service(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?vps-unfreeze={$_GET["ID"]}");	
}

function vps_stop_service(){
	$sock=new sockets();
	$sock->getFrameWork("lxc.php?vps-stop={$_GET["ID"]}");		
}

function vps_duplicate(){
		$q=new mysql();
		$sql="SELECT * FROM lxc_machines WHERE ID={$_GET["ID"]}";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$machine_name=time();
		$hostname="$machine_name.localdomain";
		
		$sql="INSERT INTO lxc_machines (machine_name,ipaddr,uid,state,autostart,rootpwd,hostname)
		VALUES('$machine_name','{$_GET["IP"]}','{$ligne["uid"]}','dup:{$_GET["ID"]}',
		'{$ligne["autostart"]}','{$ligne["rootpwd"]}','$hostname')";
	
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		$sock=new sockets();
		$sock->getFrameWork("lxc.php?vps-reconfig={$_GET["ID"]}");				
}

function vps_artica_install(){
		$q=new mysql();
		$sql="UPDATE lxc_machines SET `state`='artica-install' WHERE ID={$_GET["ID"]}";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}	
		$sock=new sockets();
		$sock->getFrameWork("lxc.php?vps-reconfig={$_GET["ID"]}");		
}


function vps_delete(){
		$q=new mysql();
		$sql="UPDATE lxc_machines SET `state`='delete' WHERE ID={$_GET["ID"]}";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}	
		$sock=new sockets();
		$sock->getFrameWork("lxc.php?vps-reconfig={$_GET["ID"]}");		
	
}

function lxc_checkconfig(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("lxc.php?checkconfig=yes")));
	
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#pts instances#",$ligne)){continue;}
		if(!$ligne){$wy[]=$num;}
	}
	
	if(count($wy)>0){
		$p=Paragraphe("danger64.png","{incompatible_system}","{missing} :".@implode(", ",$wy));	
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($p);
		
	}
	
	
	
}




function vps_performances(){
	//http://theqvd.com/en/documentation/installation/linux-containers-documentation
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$sql="SELECT cgroup FROM lxc_machines WHERE ID={$_GET["ID"]}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$CGROUPS=unserialize($ligne["cgroup"]);
	
	if(!is_numeric($CGROUPS["lxc.cgroup.memory.limit_in_bytes"])){$CGROUPS["lxc.cgroup.memory.limit_in_bytes"]="128";}
	if(!is_numeric($CGROUPS["lxc.cgroup.memory.memsw.limit_in_bytes"])){$CGROUPS["lxc.cgroup.memory.memsw.limit_in_bytes"]="512";}	
	if(!is_numeric($CGROUPS["lxc.cgroup.cpu.shares"])){$CGROUPS["lxc.cgroup.cpu.shares"]="1024";}
	if(!is_array($CGROUPS["lxc.cgroup.cpuset.cpus"])){$CGROUPS["lxc.cgroup.cpuset.cpus"][0]=1;}
	
	
	
	$users=new usersMenus();
	$MEM_TOTAL_INSTALLEE=$users->MEM_TOTAL_INSTALLEE;
	if(!is_numeric($MEM_TOTAL_INSTALLEE)){$MEM_TOTAL_INSTALLEE=1048576;}
	$MEM_TOTAL_INSTALLEE=round($MEM_TOTAL_INSTALLEE/1024);
	
	$CPU_NUMBER=$users->CPU_NUMBER;
	$z=0;
	for($i=0;$i<$CPU_NUMBER;$i++){
		$z++;
		$cps[]="
		<tr>
			<td class=legend>CPU $z:</td>
			<td>". Field_checkbox("CPU-$i",1,$CGROUPS["lxc.cgroup.cpuset.cpus"][$i])."</td>
		</tr>
		
		";
		$cpujs[]="if(document.getElementById('CPU-$i').checked){XHR.appendData('CPU-$i',1);}else{XHR.appendData('CPU-$i',0);}";
	}
	
	
	$html="
	<div class=explain>{lxc_performances_explain}</div>
	
	<div style='font-size:16px'><strong>{memory}</strong></div>
	<table class=form width=100%>
	<tr>
	<td class=legend>{limit_memory}:</td>
	<td>". Field_text("lxc.cgroup.memory.limit_in_bytes",
	$CGROUPS["lxc.cgroup.memory.limit_in_bytes"],"font-size:14px;paddind:3px;width:90px;text-align:right")."&nbsp;<span style='font-size:14px'>M</span></td>
	</tr>
	<tr>
	<td class=legend>{limit_swap}:</td>
	<td>". Field_text("lxc.cgroup.memory.memsw.limit_in_bytes",$CGROUPS["lxc.cgroup.memory.memsw.limit_in_bytes"],"font-size:14px;paddind:3px;width:90px;text-align:right")."&nbsp;<span style='font-size:14px'>M</span></td>
	</tr>	
	</table>
	<p>&nbsp;</p>
	<div style='font-size:16px'><strong>{cpu}</strong>
	<table class=form width=100%>
	<tr>
		<td class=legend>{lxc_cpu_shares}:</td>
		<td>". Field_text("lxc.cgroup.cpu.shares",$CGROUPS["lxc.cgroup.cpu.shares"],"font-size:14px;paddind:3px;;width:90px")."&nbsp;</td>
		<td>". help_icon("{lxc_performances_cpu_shares_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{CPU_ASSIGN}:</td>
		<td>
			<table>". @implode("\n",$cps)."</table>
		</td>
		<td>&nbsp;</td>
	</tr>		
	</table>
	<div style='text-align:right'><hr>". button("{apply}","SaveLXCCPU()")."</div>
	
	<script>
	
	 var x_SaveLXCCPU= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_vps{$_GET["ID"]}');
		}		
	
		function SaveLXCCPU(){
			var MEM_TOTAL_INSTALLEE=$MEM_TOTAL_INSTALLEE-256;
			var cpumem=document.getElementById('lxc.cgroup.memory.limit_in_bytes').value;
			if(cpumem>MEM_TOTAL_INSTALLEE){
				alert('Memory cannot exceed :'+MEM_TOTAL_INSTALLEE+'M');
				return;
			}
			var XHR = new XHRConnection();
			XHR.appendData('ID','{$_GET["ID"]}');
			XHR.appendData('lxc.cgroup.memory.limit_in_bytes',document.getElementById('lxc.cgroup.memory.limit_in_bytes').value);
			XHR.appendData('lxc.cgroup.memory.memsw.limit_in_bytes',document.getElementById('lxc.cgroup.memory.memsw.limit_in_bytes').value);
			XHR.appendData('lxc.cgroup.cpu.shares',document.getElementById('lxc.cgroup.cpu.shares').value);
			". @implode("\n",$cpujs)."
			XHR.sendAndLoad('$page', 'GET',x_SaveLXCCPU);
		}
	</script>
	" ;
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function vps_performances_save(){
	$array["lxc.cgroup.memory.limit_in_bytes"]=$_GET["lxc_cgroup_memory_limit_in_bytes"];
	$array["lxc.cgroup.memory.memsw.limit_in_bytes"]=$_GET["lxc_cgroup_memory_memsw_limit_in_bytes"];
	$array["lxc.cgroup.cpu.shares"]=$_GET["lxc_cgroup_cpu_shares"];
	
	while (list ($num, $line) = each ($_GET)){
		if(preg_match("#CPU-([0-9]+)#",$num,$re)){
			if($line==1){
				$array["lxc.cgroup.cpuset.cpus"][$re[1]]=1;
			}
		}
		
	}
	
	$data=addslashes(serialize($array));
	$sql="UPDATE lxc_machines SET cgroup='$data' WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{VPS_SETTINGS_REBOOT}");
	
	
}
function EnableLXCINLeftMenusSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableLXCINLeftMenus",$_GET["EnableLXCINLeftMenus"]);
	
}
?>