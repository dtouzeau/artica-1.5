<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.os.system.inc');		
	
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["machines"])){machines_section();exit;}
	if(isset($_GET["virtualbox-machines-list"])){machines_list();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["virtual-box-status"])){status_service();exit;}
	if(isset($_GET["thinclient"])){thinclient_popup();exit;}
	if(isset($_GET["thinclients-rebuild"])){thinclient_rebuild();exit;}
	
	if(isset($_GET["addthinclient"])){add_thinclient();exit;}
	if(isset($_GET["thinclient-list"])){thinclient_list();exit;}
	if(isset($_GET["delthinclient"])){del_thinclient();exit;}
	if(isset($_GET["STOP_VBOX"])){STOP_VBOX();exit;}
	if(isset($_GET["SATRT_VBOX"])){START_VBOX();exit;}
	if(isset($_GET["SNAP_VBOX"])){SNAP_VBOX();exit;}
	if(isset($_GET["tftp-infos"])){TFTP_INFOS();exit;}
	if(isset($_GET["dhcp-howto-js"])){DHCPD_HOWTO_JS();exit;}
	if(isset($_GET["dhcp-howto"])){DHCPD_HOWTO();exit;}
	if(isset($_GET["thinclient_compile_logs"])){thinclient_events();exit;}
	if(isset($_GET["thinclient_events_popup"])){thinclient_events_popup();exit;}
	if(isset($_GET["ShowThinClientLogsDetails"])){thinclient_events_details();exit;}
	if(isset($_GET["ShowThinClientLogsDetailsPopup"])){thinclient_events_details_id();exit;}
	
	if(isset($_GET["virtualizer-status"])){Virtualizer_status();exit;}
	
	
js();

function DHCPD_HOWTO_JS(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$title=$tpl->_ENGINE_parse_body("{HOWTO_PXE_DHCP}");
	
	echo "YahooWin2('650','$page?dhcp-howto=yes','$title')";
	
	
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$load="VirtualBoxIndexLoadpage()";
	if(isset($_GET["by-thinclients"])){$load="VirtualBoxIndexThinLoadpage()";}
		
	
$html="
	function VirtualBoxIndexLoadpage(){
			document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			$('#BodyContent').load('$page?popup=yes');
		}
		
	function VirtualBoxIndexThinLoadpage(){
			document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			$('#BodyContent').load('$page?thinclient=yes');
		}		
		
	var x_addthinclient= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;}
		thinclient_list();
	}		
	
	
	function addthinclient(uid){
		var XHR = new XHRConnection();
		XHR.appendData('addthinclient',uid);
		XHR.sendAndLoad('$page', 'GET',x_addthinclient);	
	}		

	function thinclient_list(){
		LoadAjax('thinclient-list','$page?thinclient-list=yes');
	}
	
	function RebuildThinClients(){
		var XHR = new XHRConnection();
		XHR.appendData('thinclients-rebuild','yes');
		XHR.sendAndLoad('$page', 'GET',x_addthinclient);
	}
	
	function ThinclientDelete(uid){
		var XHR = new XHRConnection();
		XHR.appendData('delthinclient',uid);
		XHR.sendAndLoad('$page', 'GET',x_addthinclient);		
	}
		

	$load";

echo $html;
	
}

function status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$html="<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><img src='img/virtualbox-256.png'><div class=explain>{VIRTUALBOX_ABOUT}</div>
	<td valign='top'>
		<div id='virtualbox-status'></div>
		
	</tr>
	</table>
	
	<script>
		function RefreshVirtualBoxStatus(){
			LoadAjax('virtualbox-status','$page?virtual-box-status=yes');
		}
		RefreshVirtualBoxStatus();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function status_service(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?virtualbox-ini-all-status=yes')));
	$tpl=new templates();
	if(!isset($_GET["without-virtualbox"])){
		$vbox=DAEMON_STATUS_ROUND("APP_VIRTUALBOX_WEBSERVICE",$ini,null,1)."<br>";
	}
	$status=$vbox.DAEMON_STATUS_ROUND("APP_TFTPD",$ini,null,1)."<br>".DAEMON_STATUS_ROUND("DHCPD",$ini,null,1);
	echo $tpl->_ENGINE_parse_body($status);
	
	
	
}


function popup(){
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["machines"]='{virtual_machines}';
	if($users->THINSTATION_INSTALLED){
		$array["thinclient"]='{thinclients}';
		$array["thinclient_compile_logs"]='{thinclient_compile_logs}';
		
		
		
	}


	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_virtualbox style='width:100%;height:950px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_virtualbox').tabs({
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

function machines_section(){
	$page=CurrentPageName();
	$tpl=new templates();
	$phpVirtualBox=Paragraphe("virtualbox-64.png","{virtualbox_manager}","{virtualbox_manager_text}","javascript:s_PopUp('virtualbox/index.html','1024','768')");
	$refresh=Paragraphe("64-refresh.png","{refresh_virtual_machines}","{refresh_virtual_machines_text}","javascript:RefreshVirtualBoxList()");
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>$phpVirtualBox<br>$refresh<br><div id='server-status-virtbox'></div></td>
		<td valign='top' width=99%><div id='virtualbox-machines-list' style='background-image:url(img/virtualbox-bg-512.png);height:550px;overflow:auto'></div></td>
	</tr>
	</table>
	
	<script>
		function RefreshVirtualBoxList(){
			LoadAjax('virtualbox-machines-list','$page?virtualbox-machines-list=yes');
		}
	
		RefreshVirtualBoxList();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function machines_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-list-vms=yes")));
	if(!is_array($array)){echo "<center style='margin:50px'><H3>". $tpl->_ENGINE_parse_body("{NO_VMS_HERE}")."</H3></center>";return null;}
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th width=1%>&nbsp;</th>
			<th>{name}</th>
			<th width=1%>CPU</th>
			<th width=1%>MEM</th>
			<th width=1%>RDP</th>
			<th width=1%>Mem</th>
			<th width=1%>OS</th>
			<th width=1%>&nbsp;</th>
			<th width=1%>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";
	
	
	while (list($computername,$array_conf)=each($array)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$os=$array_conf["GUEST OS"];
		$uuid=$array_conf["UUID"];
		$memory=$array_conf["MEMORY SIZE"];
		// running,powered off
		$state=$array_conf["STATE"];

		$first_nic=$array_conf["NIC 1"];
		if(preg_match("#Attachment: NAT,#",$first_nic)){
			$CONFIGNAT=true;
		}
		
		
		
		
		$img=imgtootltip("status_service_wait.png","Unknown ($state)");
		$cpu_user="-";
		$mem_user="-";
		if(preg_match("#running#",$state)){
			$stats=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showcpustats=yes&virtual-machine=".base64_encode($computername))));
			$cpu_user=$stats["CPU_LOAD_KERNEL"]."%";
			$mem_user=FormatBytes($stats["RAM_USAGE"]);
			$img=imgtootltip("status_service_run2.png","{stop_virtual_machine}","VirtualBoxStop('$uuid')");
		}
		
		if(preg_match("#powered off#",$state)){
			$img=imgtootltip("status_service_removed.png","{start_virtual_machine}","VirtualBoxStart('$uuid')");
		}
		
		if(preg_match("#meditation#",$state)){
			$img=imgtootltip("status_warning.gif","{stop_virtual_machine}<br>Gnu Meditation","VirtualBoxStop('$uuid')");
		}		
		
		if(preg_match("#aborted\s+\(#",$state)){
			$img=imgtootltip("status_warning.gif","{stop_virtual_machine}<br>Aborted!","VirtualBoxStop('$uuid')");
		}				
		
		
		
		//VRDP:            enabled (Address 0.0.0.0, Ports 3391, MultiConn: off, ReuseSingleConn: off, Authentication type: null)
		$rdp_port="-";
		$VRDP=$array_conf["VRDP"];
		if(preg_match("#enabled#",$VRDP)){
			if(preg_match("#Ports\s+(.+?),#",$VRDP,$re)){
				$rdp_port=$re[1];
			}
		}
		
$computername_length=strlen($computername);
if($computername_length>13){$computername=texttooltip(substr($computername,0,10)."...",$computername);}
if($memory=="1000MB"){$memory="1G";}
$html=$html."
	<tr class=$classtr>
		<td width=1%><span id='img_$uuid'>$img</span></td>
		<td nowrap><strong style='font-size:13px;font-weight:bold'>$computername</strong></td>
		<td nowrap width=1% align='center'><strong style='font-size:13px;font-weight:bold'>$cpu_user</strong></td>
		<td nowrap width=1% align='center'><strong style='font-size:13px;font-weight:bold'>$mem_user</strong></td>
		<td nowrap width=1% align='center'><strong style='font-size:13px;font-weight:bold'>$rdp_port</strong></td>
		<td nowrap><strong style='font-size:13px;font-weight:bold'>$memory</strong></td>
		<td><strong style='font-size:13px;font-weight:bold'>$os</strong></td>
		<td width=1%>". imgtootltip("settings-20.gif","{tools}","Loadjs('virtualbox.computer.php?uuid=$uuid')")."</td>
		<td width=1%>". imgtootltip("22-backup.png","{take_snapshot}","VirtualBoxSnap('$uuid')")."</td>
</tr>";		
		
		
		
	}
	$html=$html."</tbody></table>
	
	<script>
		var X_VirtualBoxStartStop= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshVirtualBoxList();
		}		
	
	
		function VirtualBoxStop(uuid){
			var XHR = new XHRConnection();
			XHR.appendData('STOP_VBOX','yes');
			XHR.appendData('uuid',uuid);
			document.getElementById('img_'+uuid).innerHTML='<img src=img/wait.gif>';
			XHR.sendAndLoad('$page', 'GET',X_VirtualBoxStartStop);			
		}
		
		function VirtualBoxStart(uuid){
			var XHR = new XHRConnection();
			XHR.appendData('SATRT_VBOX','yes');
			XHR.appendData('uuid',uuid);
			document.getElementById('img_'+uuid).innerHTML='<img src=img/wait.gif>';
			XHR.sendAndLoad('$page', 'GET',X_VirtualBoxStartStop);			
		}		
		
		function VirtualBoxSnap(uuid){
			var XHR = new XHRConnection();
			XHR.appendData('SNAP_VBOX','yes');
			XHR.appendData('uuid',uuid);
			document.getElementById('img_'+uuid).innerHTML='<img src=img/wait.gif>';
			XHR.sendAndLoad('$page', 'GET',X_VirtualBoxStartStop);			
		}
		
		function RefreshVirtBoxstatus(){
			LoadAjax('server-status-virtbox','$page?virtualizer-status=yes');
		
		}
	RefreshVirtBoxstatus();
	</script>";

	echo $tpl->_ENGINE_parse_body($html);
	
}

function add_thinclient(){
	$uid=$_GET["addthinclient"];
	$cmp=new computers($uid);
	$tpl=new templates();
	if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$cmp->ComputerRealName)){
		echo $tpl->javascript_parse_text("{could_not_with_computername}:$cmp->ComputerRealName");
		return;
	}
	
	$sql="INSERT INTO thinclient_computers (`uid`) VALUES('$uid')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql;}	
	
	
}

function del_thinclient(){
	$uid=$_GET["delthinclient"];
	$sql="DELETE FROM thinclient_computers WHERE `uid`='$uid'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql;}	
}


function thinclient_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$build=Paragraphe("thinclient-parameters-64.png","{thinclient_hardware}","{thinclient_hardware_text}","javascript:Loadjs('thinclient.hardware.php')");
	$softs=Paragraphe("thinclient-softwares-64.png","{services}","{thinclient_software_text}","javascript:Loadjs('thinclient.softs.php')");
	$add_thin=Paragraphe("thinclient-add-64.png","{add_thinclient}","{add_thinclient_text}",
	"javascript:Loadjs('computer-browse.php?callback=addthinclient&mode=selection')");
	$add_computer_js="javascript:YahooUser(780,'domains.edit.user.php?userid=newcomputer$&ajaxmode=yes','New computer');";
	$add_computer=Paragraphe("64-add-computer.png","{ADD_COMPUTER}","{ADD_COMPUTER_TEXT}",$add_computer_js);
	$builddistro=Paragraphe("compile-distri-64.png","{COMPILE_PXE_SYSTEM}","{THINCOMPILE_SYSTEM_TEXT}","javascript:RebuildThinClients()");
	
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		$build
		<br>
		$softs
		<br>
		$add_thin
		<br>
		$builddistro
		<br>
		$add_computer
		</td>
		<td valign='top'>
			<div id='thinclient-list' style='height:850px;overflow:auto'></div>
	</tr>
	</table>
	<script>
		thinclient_list();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function thinclient_list(){
	$cursor="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	$page=CurrentPageName();
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	$q=new mysql();
	$sql="SELECT * FROM thinclient_computers ORDER BY uid";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$cmp=new computers($ligne["uid"]);
		$js=MEMBER_JS($ligne["uid"],1,1);
		$js_thin="Loadjs('thinclient.parameters.php?uid={$ligne["uid"]}')";
		
		
		$html=$html."
	<tr class=$classtr>
		<td width=1%>". imgtootltip("computer-32.png",$cmp->ComputerRealName,$js)."</td>
		<td nowrap><span $cursor OnClick=\"javascript:$js_thin\" style='font-size:13px;text-decoration:underline'>$cmp->ComputerRealName</span></td>
		<td nowrap><span $cursor OnClick=\"javascript:$js_thin\" style='font-size:13px;text-decoration:underline'>$cmp->ComputerMacAddress</span></td>
		<td width=1%>". imgtootltip("delete-32.png","{delete}","ThinclientDelete('{$ligne["uid"]}')")."</td>
	</tr>";
	}
	$html=$html."</tbody></table>
	<div id='dhcptftpinfos'></div>
	
	<script>
		LoadAjax('dhcptftpinfos','$page?tftp-infos=yes');
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function STOP_VBOX(){
	$sock=new sockets();
	echo $sock->getFrameWork("cmd.php?virtualbox-stop={$_GET["uuid"]}");
	
}
function START_VBOX(){
	$sock=new sockets();
	echo $sock->getFrameWork("cmd.php?virtualbox-start={$_GET["uuid"]}");	
}
function SNAP_VBOX(){
	$sock=new sockets();
	echo $sock->getFrameWork("cmd.php?virtualbox-snapshot={$_GET["uuid"]}");	
}

function TFTP_INFOS(){
	$users=new usersMenus();
	$cursor="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	$page=CurrentPageName();
	if(!$users->TFTPD_INSTALLED){
		$tftp=Paragraphe("64-info.png","{TFTP_IS_NOT_INSTALLED}","{THINCLIENT_TFTP_IS_NOT_INSTALLED}",null,null,510);
	}
	
	if(!$users->dhcp_installed){
		$dhcp=Paragraphe("64-info.png","{DHCP_THINCLIENT_NOT_INSTALLED}","{DHCP_THINCLIENT_NOT_INSTALLED_TEXT}",
		"javascript:Loadjs('$page?dhcp-howto-js=yes')",null,510);
	}else{
		$sock=new sockets();
		$EnableDHCPServer=$sock->GET_INFO('EnableDHCPServer');
		if($EnableDHCPServer==0){
			$dhcp=Paragraphe("64-info.png","{DHCPD_NOT_ENABLED}","{DHCP_THINCLIENT_NOT_INSTALLED_TEXT}",
			"javascript:Loadjs('$page?dhcp-howto-js=yes')",null,510);	
		}
	}
	
	$html="
	<div OnClick=\"javascript:PopupThinClientLogs();\" $cursor style='font-size:13px;margin:8px;text-decoration:underline;text-align:right'>{thinclient_compile_logs}</div>
	
	
	$tftp<br>$dhcp
	<div id='tftpstatus'></div>
	<script>
		function PopupThinClientLogs(){
			YahooWin4('650','$page?thinclient_compile_logs=yes','{thinclient_compile_logs}');
		}
	
		LoadAjax('tftpstatus','$page?virtual-box-status=yes&without-virtualbox=yes');
	</script>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function DHCPD_HOWTO(){
	$users=new usersMenus();
	$page=CurrentPageName();	
	$tpl=new templates();	
	
	$dhcp=Paragraphe("64-info.png","{ACTIVATE_ARTICA_ASPXE}","{ACTIVATE_ARTICA_ASPXE_TEXT}",
	"javascript:Loadjs('artica.has.pxe.php')",null,220);		
	
	$html="<table style='width:100%'>
		<td valign='top' width=1%>$dhcp</td>
		<td valign='top'><div style='font-size:14px'>{HOWTO_PXE_DHCP_TEXT}</div></td>
		</tr>
		</table>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function thinclient_rebuild(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?thinclients-rebuild-cd=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{THINCLIENT_REBUILDED_TEXT}");
	
}

function thinclient_events(){
$page=CurrentPageName();	
$html="<div style='width:100%;height:550px;overflow:auto' id='thinclient_events_div'></div>	
<script>
	function RefreshThinClientLogsDetails(){
		LoadAjax('thinclient_events_div','$page?thinclient_events_popup=yes');
		}
		
	RefreshThinClientLogsDetails();
</script>";

echo $html;
	
}


function thinclient_events_popup(){
	$page=CurrentPageName();	
	$cursor="OnMouseOver=\";this.style.cursor='pointer';\" OnMouseOut=\";this.style.cursor='default';\"";
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body("{thinclient_compile_logs}");	
	$sql="SELECT `ID`,`zdate`,`subject` FROM thinclient_compile_logs ORDER BY `zdate` DESC LIMIT 0,250";
	$q=new mysql();
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=3>$title</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H3>$q->mysql_error</H3>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td nowrap>
				<span OnClick=\"javascript:ShowThinClientLogsDetails('{$ligne["ID"]}');\" style='font-size:13px;text-decoration:underline' $cursor>
				{$ligne["zdate"]}</span></td>
			<td width=99%><span OnClick=\"javascript:ShowThinClientLogsDetails('{$ligne["ID"]}');\" $cursor style='font-size:13px;text-decoration:underline'>{$ligne["subject"]}</span></td>
			
		</tr>";
	}
	$html=$html."
	</tbody>
	</table>
	<script>
		function ShowThinClientLogsDetails(ID){
			YahooWin3('650','$page?ShowThinClientLogsDetails='+ID,'$title');
		}
		
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function thinclient_events_details(){
	$page=CurrentPageName();	
	if(!is_numeric($_GET["ShowThinClientLogsDetails"])){echo "hack";exit;}
	echo "
	<div id='thin_{$_GET["ShowThinClientLogsDetails"]}'></div>
	<script>
			LoadAjax('thin_{$_GET["ShowThinClientLogsDetails"]}','$page?ShowThinClientLogsDetailsPopup=yes&ID={$_GET["ShowThinClientLogsDetails"]}');
	</script>
	
	";
	
	
	
}
function thinclient_events_details_id(){
	$page=CurrentPageName();	
	if(!is_numeric($_GET["ID"])){echo "hack";exit;}
	$sql="SELECT * FROM thinclient_compile_logs WHERE `ID`={$_GET["ID"]}";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$sub=$ligne["subject"];
	$date=$ligne["zdate"];
	$BL=explode("\n",$ligne["event"]);
	if(is_array($BL)){
	while (list ($num, $ligne) = each ($BL) ){
		if(trim($ligne)==null){continue;}
		$ligne=htmlspecialchars($ligne);
		$t[]="<div><code style='font-size:13px'>$ligne</code></div>";
		}
	}
	
	$html="<H3>$date: $sub</H3>
	<div style='margin:5px;border:1px solid #CCCCCC;height:350px;overflow:auto;padding:3px'>
		". @implode("\n",$t)."
	</div>
	
	";
	
	echo $html;
}

function Virtualizer_status(){
	include_once("ressources/class.os.system.tools.inc");
	$tpl=new templates();
	$os=new os_system();
	echo $tpl->_ENGINE_parse_body($os->MinimalStatus());
	
}



// http://blog.nicoleau-fabien.net/index.php?post/2007/11/11/Config-NAT-pour-acceder-a-votre-machine-virtuelle-VirtualBox