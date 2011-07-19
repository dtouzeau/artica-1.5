<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.openssh.inc');
	include_once('ressources/class.user.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["events-js"])){events_js();exit;}
	if(isset($_POST['upload']) ){SSHD_KEYS_SERVER_UPLOAD();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["parameters"])){parameters();exit;}
	if(isset($_GET["ListenAddress-list"])){listen_address_list();exit;}
	if(isset($_GET["LoginGraceTime"])){saveconfig();exit;}
	if(isset($_GET["ListenAddressSSHDADD"])){ListenAddressADD();exit;}
	if(isset($_GET["ListenAddressSSHDDelete"])){ListenAddressDEL();exit;}
	if(isset($_GET["keys"])){popup_keys();exit;}
	if(isset($_GET["GenerateSSHDKeyPair"])){GenerateSSHDKeyPair();exit;}
	if(isset($_GET["GetSSHDFingerprint"])){GetSSHDFingerprint();exit;}
	if(isset($_GET["download-key-pub"])){SSHDKeyPair_download();exit;}
	if(isset($_GET["SSHD_KEYS_SERVER"])){SSHD_KEYS_SERVER_FORM();exit;}
	
	
	if(isset($_GET["events"])){events();exit;}
	if(isset($_GET["sshd-events"])){events_list();exit;}
	
	
	
js();	

function events_js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?events=yes');";
	
}


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_OPENSSH}");
	$start="OPENSSH_LOAD();";
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
		
	
	if(isset($_GET["in-front-ajax"])){
		$start="OPENSSH_LOAD2();";
	}
	
	$html="
	function OPENSSH_LOAD(){
			YahooWin2('600','$page?popup=yes','$title');
		}
		
		function OPENSSH_LOAD2(){
			$('#BodyContent').load('$page?popup=yes');
		}		
	
		function BACKUP_TASKS_LISTS(){
			LoadAjax('taskslists','$page?BACKUP_TASKS_LISTS=yes');
		}
		
		function BACKUP_TASKS_SOURCE(ID){
			YahooWin3('500','$page?backup-sources=yes&ID='+ID,'$sources');
		}
		
		function TASK_EVENTS_DETAILS(ID){
			YahooWin3('700','$page?TASK_EVENTS_DETAILS='+ID,ID+'::$events');
		}
		
		function TASK_EVENTS_DETAILS_INFOS(ID){
			YahooWin4('700','$page?TASK_EVENTS_DETAILS_INFOS='+ID,ID+'::$events');
		}
		
		function BACKUP_TASK_MODIFY_RESSOURCES(ID){
			YahooWin3('500','$page?BACKUP_TASK_MODIFY_RESSOURCES='+ID,ID+'::$resources');
		}
		
		
var x_DeleteBackupTask= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		BACKUP_TASKS_LOAD();
		if(document.getElementById('wizard-backup-intro')){
			WizardBackupLoad();
		}
	 }	

var x_DELETE_BACKUP_SOURCES= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		BACKUP_TASKS_LOAD();
		YahooWin3Hide();
	 }		 
		
		function DeleteBackupTask(ID){
			if(confirm('$BACKUP_TASK_CONFIRM_DELETE')){
				var XHR = new XHRConnection();
				XHR.appendData('DeleteBackupTask',ID);
				XHR.sendAndLoad('$page', 'GET',x_DeleteBackupTask);
			}
		}
		
		function DELETE_BACKUP_SOURCES(ID,INDEX){
			if(confirm('$BACKUP_TASK_CONFIRM_DELETE_SOURCE')){
				var XHR = new XHRConnection();
				XHR.appendData('DeleteBackupSource','yes');
				XHR.appendData('ID',ID);
				XHR.appendData('INDEX',INDEX);
				XHR.sendAndLoad('$page', 'GET',x_DELETE_BACKUP_SOURCES);
			}
		}
		
		
	var x_BACKUP_SOURCES_SAVE_OPTIONS= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			BACKUP_TASKS_SOURCE(mem_taskid);
			
		 }		
		
		
	function BACKUP_SOURCES_SAVE_OPTIONS(taskid){
		mem_taskid=taskid;
		var XHR = new XHRConnection();
		if(document.getElementById('backup_stop_imap').checked){
		XHR.appendData('backup_stop_imap',1);}else{
		XHR.appendData('backup_stop_imap',0);}
		XHR.appendData('taskid',taskid);
		document.getElementById('BACKUP_SOURCES_OPTIONS').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
		XHR.sendAndLoad('$page', 'GET',x_BACKUP_SOURCES_SAVE_OPTIONS);
		}	

	function BACKUP_TASK_TEST(ID){
			YahooWin3('500','$page?backup-tests=yes&ID='+ID,'$tests');
		}
		
	var x_BACKUP_TASK_RUN= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			alert('$apply_upgrade_help');
			BACKUP_TASKS_LOAD();
		 }		
		
		
		
		function BACKUP_TASK_RUN(ID){
			if(confirm('$backupTaskRunAsk')){
				var XHR = new XHRConnection();
				XHR.appendData('BACKUP_TASK_RUN',ID);
				XHR.sendAndLoad('$page', 'GET',x_BACKUP_TASK_RUN);
			}
		}
		
	
	$start";
	
	
	echo $html;
}


function popup(){
	
	
	$tpl=new templates();
	$array["status"]='{status}';
	$array["parameters"]='{parameters}';
	$array["keys"]='{automatic_login}';
	$array["antihack"]='anti-hack';
	$array["events"]='{events}';
	$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="antihack"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.iptables.php?tab-iptables-rules=yes&sshd=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_openssh style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_openssh').tabs({
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

function events(){
	$tpl=new templates();
	$page=CurrentPageName();	
	
	$html="
		<div class=explain>{sshd_events_explain}</div>
	
		<center>
		<table style='width:450px' class=form>
		<tr>
			<td class=legend valign='middle'>{search}:</td>
			<td valign='middle'>". Field_text("sshd-db-search",null,"font-size:14px;padding:3px;width:100%",null,null,null,false,
			"RefreshSSHDEventsCheck(event)")."</td>
			<td width=1% valign='middle'>". button("{search}","RefreshSSHDEvents()")."</td>
		</tr>
		<tr>
		
		</table>
	</center>
	
	
	<div id='sshd_events' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
		function RefreshSSHDEventsCheck(e){
			if(checkEnter(e)){RefreshSSHDEvents();}
		
		}
	
	
		function RefreshSSHDEvents(){
			var se=escape(document.getElementById('sshd-db-search').value);
			LoadAjax('sshd_events','$page?sshd-events=yes&search='+se);
		
		}
	RefreshSSHDEvents();
	</script>";
echo $tpl->_ENGINE_parse_body($html);return;	
}

function events_list(){
	include_once(dirname(__FILE__) . '/ressources/class.rtmm.tools.inc');
	$tpl=new templates();
	$page=CurrentPageName();
	$search=$_GET["search"];
	if(preg_match("#(.*?)isfailed#i",$search,$re)){$search=trim($re[1]);$success=" AND success=0";}
	
	
	$search="*$search*";
	$search=str_replace("**","*",$search);
	$search=str_replace("*","%",$search);	

	
$sql="SELECT * FROM auth_events WHERE 1
	AND (`ipaddr` LIKE '$search' $success) OR (`hostname` LIKE '$search' $success) OR (`uid` LIKE '$search' $success) ORDER BY ID DESC LIMIT 0,90";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	
$html="
<p>&nbsp;</p>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th width=1%>&nbsp;</th>
		<th width=1%>{date}</th>
		<th width=1%>{member}</th>
		<th>{hostname} $search</th>
		<th>{ipaddr}</th>
	</tr>
</thead>
<tbody class='tbody'>";


	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	
	if($ligne["success"]==1){$img='fleche-20-right.png';}else{$img='fleche-20-red-right.png';}
	$flag=imgtootltip(GetFlags($ligne["Country"]),$ligne["Country"],null);
	$html=$html."
	<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/$img></td>
	<td style='font-size:14px;font-weight:bold'>$flag</td>
	<td style='font-size:14px;font-weight:bold' nowrap>{$ligne["zDate"]}</a></td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["uid"]}</a></td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["hostname"]}</a></td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["ipaddr"]}</a></td>
	</tR>";

	}
	
	
	$html=$html."</table>\n";


echo $tpl->_ENGINE_parse_body($html);return;
			
	
	
}


function status(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?openssh-ini-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_OPENSSH",$ini);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<H1>{APP_OPENSSH}</H1>
			<img src='img/openssh-256.png' style='margin:5px'>
		
		</td>
		<td valign='top'>
			$status
		<hr>
		<div class=explain>{OPENSSH_EXPLAIN}</div>
		</td>
	</tr>
	</table>
	
	
	";
	
	$tpl=new templates();
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function parameters(){
	
	$sshd=new openssh();
	$page=CurrentPageName();
	$users=new usersMenus();
	if(preg_match("#([0-9]+)\.([0-9]+)#",$users->OPENSSH_VER,$re)){$opensshver="{$re[1]}{$re[2]}";}
	
	
	if(is_array($sshd->HostKey)){
		while (list ($num, $line) = each ($sshd->HostKey)){
			$hostkey=$hostkey."<div><code>$line</code>&nbsp;</div>";
		}
	}
	
	
	if($opensshver<50){
		$disable_js="DisableMaxSessions();";
	}
	
	$html="
	<div id='sshdconfigid'>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{listen_port}:</td>
		<td style='font-size:13px'>". Field_text("Port",$sshd->main_array["Port"],"font-size:13px;padding:3x;width:60px")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{PermitRootLogin}:</td>
		<td style='font-size:13px'>". Field_checkbox("PermitRootLogin","yes",$sshd->main_array["PermitRootLogin"])."</td>
		<td width=1%>". help_icon("{PermitRootLogin_text}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{UsePAM}:</td>
		<td style='font-size:13px'>". Field_checkbox("UsePAM","yes",$sshd->main_array["UsePAM"])."</td>
		<td width=1%>". help_icon("{UsePAM_TEXT}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{ChallengeResponseAuthentication}:</td>
		<td style='font-size:13px'>". Field_checkbox("ChallengeResponseAuthentication","yes",$sshd->main_array["ChallengeResponseAuthentication"])."</td>
		<td width=1%>". help_icon("{ChallengeResponseAuthentication_text}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{PasswordAuthentication}:</td>
		<td style='font-size:13px'>". Field_checkbox("PasswordAuthentication","yes",$sshd->main_array["PasswordAuthentication"])."</td>
		<td width=1%>". help_icon("{PasswordAuthentication_text}")."</td>
	</tr>
	
	
	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{PermitTunnel}:</td>
		<td style='font-size:13px'>". Field_checkbox("PermitTunnel","yes",$sshd->main_array["PermitTunnel"])."</td>
		<td width=1%>". help_icon("{PermitTunnel_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{UseDNS}:</td>
		<td style='font-size:13px'>". Field_checkbox("UseDNS","yes",$sshd->main_array["UseDNS"])."</td>
		<td width=1%>". help_icon("{UseDNS_sshd_text}")."</td>
	</tr>		
	
	
	
	
	
	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{LoginGraceTime}:</td>
		<td style='font-size:13px'>". Field_text("LoginGraceTime",$sshd->main_array["LoginGraceTime"],"font-size:13px;padding:3x;;width:60px")."&nbsp;{seconds}</td>
		<td width=1%>". help_icon("{LoginGraceTime_text}")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{MaxSessions}:</td>
		<td style='font-size:13px'>". Field_text("MaxSessions",$sshd->main_array["MaxSessions"],"font-size:13px;padding:3x;width:60px")."&nbsp;{sessions}</td>
		<td width=1%>". help_icon("{MaxSessions_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{MaxAuthTries}:</td>
		<td style='font-size:13px'>". Field_text("MaxAuthTries",$sshd->main_array["MaxAuthTries"],"font-size:13px;padding:3x;width:60px")."&nbsp;</td>
		<td width=1%>". help_icon("{MaxAuthTries_text}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend style='font-size:13px'>{HostKey}:</td>
		<td style='font-size:13px'>$hostkey</td>
		<td width=1%>". help_icon("{HostKey_text}")."</td>
	</tr>	
		<td valign='top' class=legend style='font-size:13px'>{AuthorizedKeysFile}:</td>
		<td style='font-size:13px'>{$sshd->main_array["AuthorizedKeysFile"]}</td>
		<td width=1%>". help_icon("{AuthorizedKeysFile_text}")."</td>
	</tr>	
	
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveSSHDConfig()")."</td>
	</tr>
	</table>
	<div style='width:100%;heigth:250px;overflow:auto' id='sshd_nets'></div>
	</div>
	
	
	<script>
	
		var x_SaveSSHDConfig= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			RefreshTab('main_config_openssh');
			
		 }		
	
	
		function SaveSSHDConfig(){
			var XHR = new XHRConnection();
			XHR.appendData('Port',document.getElementById('Port').value);
			XHR.appendData('LoginGraceTime',document.getElementById('LoginGraceTime').value);
			XHR.appendData('MaxSessions',document.getElementById('MaxSessions').value);
			XHR.appendData('MaxAuthTries',document.getElementById('MaxAuthTries').value);
			if(document.getElementById('PermitRootLogin').checked){XHR.appendData('PermitRootLogin','yes');}else{XHR.appendData('PermitRootLogin','no');}
			if(document.getElementById('PermitTunnel').checked){XHR.appendData('PermitTunnel','yes');}else{XHR.appendData('PermitTunnel','no');}
			if(document.getElementById('UseDNS').checked){XHR.appendData('UseDNS','yes');}else{XHR.appendData('UseDNS','no');}
			if(document.getElementById('UsePAM').checked){XHR.appendData('UsePAM','yes');}else{XHR.appendData('UsePAM','no');}
			if(document.getElementById('ChallengeResponseAuthentication').checked){XHR.appendData('ChallengeResponseAuthentication','yes');}else{XHR.appendData('ChallengeResponseAuthentication','no');}
			if(document.getElementById('PasswordAuthentication').checked){XHR.appendData('PasswordAuthentication','yes');}else{XHR.appendData('PasswordAuthentication','no');}
			
			
			document.getElementById('sshdconfigid').innerHTML='<center><img src=img/wait_verybig.gif></center>';		
			XHR.sendAndLoad('$page', 'GET',x_SaveSSHDConfig);
		
		
		}
		
		function DisableMaxSessions(){
			document.getElementById('MaxSessions').disabled=true;
		}
	
	
		function RefreshListenAddress(){
			LoadAjax('sshd_nets','$page?ListenAddress-list=yes');
		}
	
	RefreshListenAddress();
	$disable_js	
	</script>";
	
	$tpl=new templates();
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function listen_address_list(){
	$sshd=new openssh();
	$page=CurrentPageName();
	$tcp=new networking();
	$arrayip=$tcp->ALL_IPS_GET_ARRAY();
	$arrayip["0.0.0.0"]="{all}";
	
	unset($arrayip[null]);
	unset($arrayip["127.0.0.1"]);
	$field=Field_array_Hash($arrayip,"ListenAddressSSHDADD","0.0.0.0",null,null,0,"font-size:13px;padding:3px");
	
	$html="
	<center>
	<table class='tableView' style='width:250px'>
	<tr>
		
		<td>$field</td>
		<td style='font-size:13px;' width=1%>:</td>
		<td style='font-size:13px;'>".Field_text("ListenAddressSSHDPort",22,"font-size:13px;padding:3px;width:40px")."</td>
		<td width=1%>". button("{add}","AddSSHDNet()")."</td>
		</tr>
	
	</table>
	
	<table class='tableView' style='width:240px'>
		<thead class='thead'>
		<tr>
		<th>&nbsp;</th>
		<th colspan=2>{listen_ip}</th>
		</tr>
		
		</thead>";
	if(is_array($sshd->ListenAddress)){
	while (list ($num, $line) = each ($sshd->ListenAddress)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:13px'>$line</strong></td>
			<td width=1%>". imgtootltip("ed_delete.gif","{delete}","ListenAddressSSHDDelete($num)")."</td>
		</tr>
		";
		
	}}
	
$html=$html."</table></center>
<script>
		var x_AddSSHDNet= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			RefreshListenAddress();
			
		 }		
	
	
		function AddSSHDNet(){
			var XHR = new XHRConnection();
			XHR.appendData('ListenAddressSSHDADD',document.getElementById('ListenAddressSSHDADD').value);
			XHR.appendData('ListenAddressSSHDPort',document.getElementById('ListenAddressSSHDPort').value);
			document.getElementById('sshd_nets').innerHTML='<center><img src=img/wait_verybig.gif></center>';		
			XHR.sendAndLoad('$page', 'GET',x_AddSSHDNet);
		}
		
		function ListenAddressSSHDDelete(INDEX){
			var XHR = new XHRConnection();
			XHR.appendData('ListenAddressSSHDDelete',INDEX);
			document.getElementById('sshd_nets').innerHTML='<center><img src=img/wait_verybig.gif></center>';		
			XHR.sendAndLoad('$page', 'GET',x_AddSSHDNet);
		}
		
		
</script>		

";	
	
	$tpl=new templates();
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function ListenAddressDEL(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}	
		
	$sshd=new openssh();
	unset($sshd->ListenAddress[$_GET["ListenAddressSSHDDelete"]]);
	$sshd->save();
}

function ListenAddressADD(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$sshd=new openssh();
	$sshd->ListenAddress[]=$_GET["ListenAddressSSHDADD"].":".$_GET["ListenAddressSSHDPort"];
	$sshd->save();
}

function saveconfig(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$sshd=new openssh();
	while (list ($num, $val) = each ($_GET)){
		$sshd->main_array[$num]=$val;
	}
	
	$sshd->save();
	
}

function popup_keys(){
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$page=CurrentPageName();
	$sock=new sockets();
	$ldap=new clladp();
	$hash=$ldap->Hash_GetALLUsers();
	$users=unserialize(base64_decode($sock->getFrameWork("cmd.php?unixLocalUsers=yes")));
	
	
	while (list ($uid, $mail) = each ($hash) ){
		if(strpos($uid,"$")>0){continue;}
		$users[$uid]=$uid;
	}
	$users[null]="{select}";
	ksort($users);
	
	$userF=Field_array_Hash($users,"user_key","root","GetSSHDFingerprint()",null,0,"font-size:13px;padding:3px");
	
	
	$html="
	<div class=explain id='idtofill'>{SSH_KEYS_WHY}</div>
	
	<H3>{SSH_KEYS_CLIENT}</H3>
	<div class=explain>{SSH_KEYS_CLIENT_EXPLAIN}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{ArticaProxyServerUsername}:</td>
		<td>$userF</td>
		<td width=1% nowrap>". button("{fingerprint}","GetSSHDFingerprint()")."</td>
		<td width=99%>". button("{generate_key}","GenerateSSHDKeyPair()")."</td>
		
	</tr>
	<tr>
		<td colspan=4><div id='fingerprint'></div></td>
	</tr>
	</table>
	
	<hr>
	<H3>{SSHD_KEYS_SERVER}</H3>
	<div class=explain>{SSHD_KEYS_SERVER_TEXT}</div>
	
	<iframe style='width:100%;height:250px;border:0px' src='$page?SSHD_KEYS_SERVER=yes'></iframe>
	
	
	<script>
		var x_GenerateSSHDKeyPair= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			GetSSHDFingerprint();
			
		 }		
		 
		var x_GetSSHDFingerprint= function (obj) {
			var tempvalue=obj.responseText;
			document.getElementById('fingerprint').innerHTML='';
			if(tempvalue.length>0){
				document.getElementById('fingerprint').innerHTML=tempvalue;
			}
		 }			 
			
		function GenerateSSHDKeyPair(){
			var XHR = new XHRConnection();
			XHR.appendData('GenerateSSHDKeyPair',document.getElementById('user_key').value);
			document.getElementById('fingerprint').innerHTML='<center><img src=img/wait_verybig.gif></center>';		
			XHR.sendAndLoad('$page', 'GET',x_GenerateSSHDKeyPair);
		}
		
		function GetSSHDFingerprint(){
			var XHR = new XHRConnection();
			XHR.appendData('GetSSHDFingerprint',document.getElementById('user_key').value);
			document.getElementById('fingerprint').innerHTML='<center><img src=img/wait_verybig.gif></center>';		
			XHR.sendAndLoad('$page', 'GET',x_GetSSHDFingerprint);		
		}
	GetSSHDFingerprint();
	</script>
	
	
	";
	
	$tpl=new templates();
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function GenerateSSHDKeyPair(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$uid=$_GET["GenerateSSHDKeyPair"];
	$sock=new sockets();
	$usersUNix=unserialize(base64_decode($sock->getFrameWork("cmd.php?unixLocalUsers=yes")));
	if($uid=="root"){
		$homepath="/root/.ssh";
	}else{
		if($usersUNix[$uid]<>null){$homepath="/home/$uid/.ssh";}
		if($homepath==null){
			$user=new user($uid);
			if($user->homeDirectory<>null){$homepath=$user->homeDirectory."/.ssh";}else{$homepath="/home/$uid/.ssh";}
			
		}
	}
	
	$sock=new sockets();
	$homepath_encoded=base64_encode($homepath);
	$datas=base64_decode($sock->getFrameWork("cmd.php?ssh-keygen=$homepath_encoded&uid=$uid"));
	echo $datas;
	
}

function GetSSHDFingerprint(){
	$uid=$_GET["GetSSHDFingerprint"];
	$page=CurrentPageName();
	$sock=new sockets();
	$usersUNix=unserialize(base64_decode($sock->getFrameWork("cmd.php?unixLocalUsers=yes")));
	if($uid=="root"){
		$homepath="/root/.ssh";
	}else{
		if($usersUNix[$uid]<>null){$homepath="/home/$uid/.ssh";}
		if($homepath==null){
			$user=new user($uid);
			if($user->homeDirectory<>null){$homepath=$user->homeDirectory."/.ssh";}else{$homepath="/home/$uid/.ssh";}
			
		}
	}	
	
	$tpl=new templates();
	$homepath_encoded=base64_encode($homepath);
	$datas=base64_decode($sock->getFrameWork("cmd.php?ssh-keygen-fingerprint=$homepath_encoded&uid=$uid"));
	
	if(trim($datas)==null){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{SSHD_NOFINGER_NEED_GENERATE}</div>");return ;
	}
	
	echo $tpl->_ENGINE_parse_body("
	
	<div style='margin:15px;padding:3px;border:1px dotted #CCCCCC'>
	<div style='float:right'>".imgtootltip("32-key.png","{download}","document.location='$page?download-key-pub=$homepath_encoded'")."</div>
	<span style='font-size:14px'>{fingerprint}</span><hr>
	<code style='font-size:14px'>$datas</code>
	
	
	</div>");
}

function SSHDKeyPair_download(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ssh-keygen-download=".$_GET["download-key-pub"]);
	$content=@file_get_contents("ressources/logs/web/id_rsa.pub");
	$size = filesize("ressources/logs/web/id_rsa.pub");
	header("Content-Type: application/force-download; name=\"id_rsa.pub\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: $size");
	header("Content-Disposition: attachment; filename=\"id_rsa.pub\"");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	echo $content; 	
	
}

function SSHD_KEYS_SERVER_FORM($error=null){
	$sock=new sockets();
	$ldap=new clladp();
	$hash=$ldap->Hash_GetALLUsers();
	$users=unserialize(base64_decode($sock->getFrameWork("cmd.php?unixLocalUsers=yes")));
	
	$page=CurrentPageName();
	while (list ($uid, $mail) = each ($hash) ){
		if(strpos($uid,"$")>0){continue;}
		$users[$uid]=$uid;
	}
	$users[null]="{select}";
	ksort($users);	
		$userF=Field_array_Hash($users,"uid",$_POST["uid"],null,null,0,"font-size:13px;padding:3px");
	$html="
	<div style='color:red;font-size:14px;font-weight:bold'>$error</div>
	<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{ArticaProxyServerUsername}:</td>
		<td>$userF</td>	
	</tr>	
	</table>
	$hidden
	<p>
	<input type=\"file\" name=\"id_rsa\" size=\"30\">
	<input type='submit' name='upload' value='{upload_a_file}&nbsp;&raquo;' style='width:190px'>
	</p>
	</form>
	
	";
	$tpl=new templates();
	echo iframe($tpl->_ENGINE_parse_body($html),0,0);
	
}

function SSHD_KEYS_SERVER_UPLOAD(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}		
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;return;}		
	
	$sock=new sockets();
	$page=CurrentPageName();
	if(!isset($_POST["uid"])){SSHD_KEYS_SERVER_FORM('{ArticaProxyServerUsername} not set');exit;}
	
	$uid=$_POST["uid"];
	$tmp_file = $_FILES['id_rsa']['tmp_name'];
	$content_dir=dirname(__FILE__)."/ressources/conf/upload";
	if(!is_dir($content_dir)){@mkdir($content_dir);}
	if( !@is_uploaded_file($tmp_file) ){
		SSHD_KEYS_SERVER_FORM('{error_unable_to_upload_file} '.$tmp_file);
		exit;
	}
	$name_file = $_FILES['id_rsa']['name'];

if(file_exists( $content_dir . "/" .$name_file)){@unlink( $content_dir . "/" .$name_file);}
 if( !move_uploaded_file($tmp_file, $content_dir . "/" .$name_file) ){
 	SSHD_KEYS_SERVER_FORM("{error_unable_to_move_file} : ". $content_dir . "/" .$name_file);
 	exit();
 	}
     
    $file=$content_dir . "/" .$name_file;
	$usersUNix=unserialize(base64_decode($sock->getFrameWork("cmd.php?unixLocalUsers=yes")));
	
	if($uid=="root"){
		$homepath="/root/.ssh";
	}else{
		if($usersUNix[$uid]<>null){$homepath="/home/$uid/.ssh";}
		if($homepath==null){
			$user=new user($uid);
			if($user->homeDirectory<>null){$homepath=$user->homeDirectory."/.ssh";}else{$homepath="/home/$uid/.ssh";}
			
		}
	}	
	writelogs("home=$homepath, source=$file",__FUNCTION__,__FILE__,__LINE__);
	$tpl=new templates();
	$homepath_encoded=base64_encode($homepath);
    $source_file_encoded=base64_encode($file);
    
    $datas=base64_decode($sock->getFrameWork("cmd.php?sshd-authorized-keys=yes&rsa=$source_file_encoded&home=$homepath_encoded&uid=$uid"));
	SSHD_KEYS_SERVER_FORM("$datas");

   
	
}






?>