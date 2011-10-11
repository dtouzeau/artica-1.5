<?php
$GLOBALS["VERBOSE"]=false;
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.users.menus.inc');
include_once ('ressources/class.artica.inc');
include_once ('ressources/class.pure-ftpd.inc');
include_once ('ressources/class.user.inc');
include_once ('ressources/charts.php');
include_once ('ressources/class.mimedefang.inc');
include_once ('ressources/class.computers.inc');
include_once ('ressources/class.ini.inc');
include_once ('ressources/class.ocs.inc');
include_once (dirname ( __FILE__ ) . "/ressources/class.cyrus.inc");

if ((!isset ($_GET["uid"] )) && (isset($_POST["uid"]))){$_GET["uid"]=$_POST["uid"];}
if ((isset ($_GET["uid"] )) && (! isset ($_GET["userid"] ))) {$_GET["userid"] = $_GET["uid"];}

//permissions	
$usersprivs = new usersMenus ( );
$change_aliases = GetRights_aliases();
$modify_user = 1;
if ($_SESSION ["uid"] != $_GET["userid"]) {$modify_user = 0;}

if ($change_aliases == 1) {
	if(isset($_GET["AddAliases"])){AddAliases ();exit ();}
	if(isset($_GET["DeleteAliases"])){DeleteAliases ();exit ();}
	if(isset($_GET["AddAliasesMailing"])){AddAliasesMailing ();exit ();}
	if(isset($_GET["DeleteAliasesMailing"])){DeleteAliasesMailing ();exit ();}
	if(isset($_GET["aliases-users-list"])){USER_ALIASES ($_GET["uid"]);exit ();}
	if(isset($_GET["delete-aliases"])){USER_ALIASES_DELETE_JS ();exit ();}
	$modify_user = 1;
}

//FTP
if(isset ($_GET["pureftpd-js"])){USER_FTP_JS ();exit ();}
if(isset ($_GET["UserAddressSubmitedForm"])){AddressInfosSave();exit();}
if(isset($_GET["userid-warning"])){AJAX_USER_WARNING();exit;}
if(isset($_GET["ComputerMacAddressFindUid"])){COMPUTER_CHECK_MAC();exit;}
if(isset($_REQUEST["ChangeUserPasswordSave"])){USER_CHANGE_PASSWORD_SAVE();exit ();}
if ($modify_user == 0) {die ( 'No permissions ' . $_SESSION ["uid"] . "\nchange_aliases=$usersprivs->AllowEditAliases\n" );}

if(isset($_GET["ChangeUserPassword"])){USER_CHANGE_PASSWORD();exit ();}
if(isset($_GET["UserChangeEmailAddr"])){USER_CHANGE_EMAIL ();exit ();}
if(isset($_GET["UserChangeEmailAddrSave"])){USER_CHANGE_EMAIL_SAVE ();exit ();}
if(isset($_GET["zarafa-mailbox-edit"])){ZARAFA_MAILBOX_EDIT_JS ();exit ();}
if(isset($_GET["zarafaQuotaWarn"])){ZARAFA_MAILBOX_SAVE ();exit ();}
if(isset($_POST["user_zarafa_enable_pop3"])){ZARAFA_DISABLE_FEATURES_SAVE();exit;}
if(isset($_POST["zarafaSharedStoreOnly"])){zarafaSharedStoreOnly();exit;}



if(isset($_GET["UserEndOfLIfe"])){USER_ENDOFLIFE ();exit ();}

// inbound parameters
if(isset($_GET["RecipientToAdd"])){USER_BBC_MAP_ADD ();exit ();}
if(isset($_GET["RecipientToAdd_delete"])){USER_BBC_MAP_DEL ();exit ();}
if(isset($_GET["USER_BBC_MAP_LIST"])){echo USER_BBC_MAP_LIST ();exit ();}

if(isset($_GET["sync_next_user"])){TOOL_SYNC_STEP2 ();exit ();}
if(isset($_GET["imapsync_events"])){TOOL_SYNC_EVENTS ();exit ();}
if(isset($_GET["sync_find_user"])){TOOL_SYNC_FIND_MAILBOX ();exit ();}
if(isset($_GET["sendparams"])){USER_SENDER_PARAM ($_GET["userid"]);exit ();}
if(isset($_GET["ComputerAddAlias"])){COMPUTER_ADD_ALIAS ();exit ();}
if(isset($_REQUEST["new_userid"])){USER_ADD ();exit ();}

if(isset($_GET["DotClearUserEnabled"])){SaveLdapUser ();exit ();}
if(isset($_GET["SaveLdapUser"])){SaveLdapUser ();exit ();}
if(isset($_GET["SaveUserInfos"])){SaveUserInfos ();exit ();}
if(isset($_GET["Cyrus_mailbox_apply_settings"])){Cyrus_mailbox_apply_settings ();exit ();}

//mailbox

if(isset($_GET["ZARAFA_MAILBOX_INFOS"])){ZARAFA_MAILBOX_INFOS_JS();exit;}
if(isset($_GET["ZARAFA_MAILBOX_INFOS_POPUP"])){ZARAFA_MAILBOX_INFOS_POPUP();exit;}

if(isset($_GET["create-mailbox-wizard"])){USER_MAILBOX_WIZARD_JS ();exit ();}
if(isset($_GET["create-mailbox-step1"])){USER_MAILBOX_WIZARD_STEP1 ();exit ();}
if(isset($_GET["UserMailBoxEdit"])){UserMailBoxEdit ();exit ();}
if(isset($_GET["debug-mailbox-js"])){UserMailBoxDebugJs ();exit ();}
if(isset($_GET["debug-mailbox-user"])){UserMailBoxDebugEvents ();exit ();}
if(isset($_GET["DeleteUserGroup"])){DeleteUserGroup ();exit ();}
if(isset($_GET["section"])){AJAX_USER_STARTER();exit ();}
if(isset($_GET["DeleteThisUser"])){USER_DELETE();exit ();}
//FTP
if(isset($_POST["UserFTPEdit"])){UserFTPEdit();exit ();}
if(isset($_GET["SambaUid"])){USER_SAMBA_EDIT();exit ();}
if(isset($_GET["RebuildSambaFields"])){USER_SAMBA_REBUILD_NULL();exit ();}
if(isset($_GET["smb-section"])){echo USER_SAMBA ($_GET["userid"]);exit ();}
if(isset($_GET["AJAX_COMPUTER_MATERIAL_OS_SAVE"])){AJAX_COMPUTER_MATERIAL_OS_SAVE ();exit ();}
if(isset($_GET["SaveComputerInfo"])){COMPUTER_SAVE_INFOS ();exit ();}
if(isset($_GET["NmapScanComputer"])){COMPUTER_NMAP ();exit ();}
if(isset($_GET["DeleteComputer"])){COMPUTER_DELETE ();exit ();}
if(isset($_GET["DeletComputerAliases"])){COMPUTER_DELETE_ALIAS ();exit ();}
if(isset($_GET["DeleteSenderCanonical"])){USER_DELETE_CANONICAL ();exit ();}
if(isset($_GET["script"])){SWITCH_SCRIPTS ();exit ();}
if(isset($_GET["TOOLS_REPAIR"] )){TOOLS_REPAIR ();exit ();}
if(isset($_GET["TOOLS_SYNC"])){TOOLS_SYNC ();exit ();}
if(isset($_GET["TOOLS_IMPORT"])){TOOLS_IMPORT ();exit ();}
if(isset($_GET["RepairThisMailbox"])){
	TOOLS_REPAIR_OP ();
	exit ();
}
if(isset($_GET["ShowMbxRepair"])){
	TOOLS_REPAIR_LOGS ();
	exit ();
}
if(isset($_GET["applypureftpd"])){
	USER_FTP_APPLY_SAVE ();
	exit ();
}
if(isset($_GET["LaunchExportOperation"])){
	TOOL_SYNC_LAUNCH ();
	exit ();
}
if(isset($_GET["SaveAllowedSMTP"])){SaveAllowedSMTP ();exit ();}
if(isset($_GET["user_transport"])){USER_TRANSPORT ();exit ();}
if(isset($_GET["relay_address"])){USER_TRANSPORT_SAVE ();exit ();}
if(isset($_GET["DeleteAlternateSmtpRelay"])){USER_TRANSPORT_DELTE ();exit ();}
if(isset($_GET["remote_imap_server"])){TOOLS_IMPORT_SAVE ();exit ();}
if(isset($_GET["smtp-sasl"])){USER_TRANSPORT_SALS_JS ();exit ();}
if(isset($_GET["smtp-sasl-popup"])){USER_TRANSPORT_SALS_POPUP ();exit ();}
if(isset($_GET["sasl_username"])){USER_TRANSPORT_SALS_SAVE ();	exit ();}
if(isset($_GET["sender-email-js"])){	USER_CANONICAL_JS ();exit ();}
if(isset($_GET["sender-email-popup"])){USER_CANONICAL_POPUP ();exit ();}
if(isset($_GET["ImportMbxTestConnection"])){TOOLS_IMPORT_TESTS ();exit ();}
if(isset($_GET["TOOLS_IMPORT_LOGS"])){
	TOOLS_IMPORT_LOGS ();
	exit ();
}
if(isset($_GET["LauchMbxImport"])){
	TOOLS_IMPORT_LAUNCH ();
	exit ();
}
if(isset($_GET["CalendarPickup"])){
	CalendarPickup ();
	exit ();
}

if(isset($_GET["changeuid"])){USER_CHANGE_UID();exit ();}
if(isset($_GET["changeuidFrom"])){
	USER_CHANGE_UID_SAVE ();
	exit ();
}
if(isset($_GET["EnableUserSpamLearning-js"])){
	USER_JUNK_LEARNING_JS ();
	exit ();
}
if(isset($_GET["EnableUserSpamLearning-popup"])){
	USER_JUNK_LEARNING_POPUP ();
	exit ();
}
if(isset($_GET["EnableUserSpamLearning"])){
	USER_JUNK_LEARNING_SAVE ();
	exit ();
}

if(isset($_GET["USER_ALIASES_FORM_ADD_JS"])){
	USER_ALIASES_FORM_ADD_JS ();
	exit ();
}
if(isset($_GET["USER_ALIASES_FORM_ADD"])){
	USER_ALIASES_FORM_ADD ();
	exit ();
}

//SAMBA
if(isset($_GET["enable-shared"])){USER_SAMBA_ENABLE_JS ();exit ();}
if(isset($_GET["SAMBA_PRIVILEGES"])){
	USER_SAMBA_PRIVILEGES ();
	exit ();
}
if(isset($_GET["SAMBA_PRIVILEGES_PAGE"])){
	USER_SAMBA_PRIVILEGES_PAGE ();
	exit ();
}
if(isset($_GET["SAMBA_SET_PRIVILEGES_GROUP"])){
	USER_SAMBA_SET_PRIVILEGES_GROUP ();
	exit ();
}
if(isset($_GET["SAMBADISPLAYPDBEDIT"])){
	USER_SAMBA_DISPLAY_PDBEDIT ();
	exit ();
}
if(isset($_GET["SAMBADISPLAYPDBEDIT_STANDARD"])){
	echo USER_SAMBA_INFOS ($_GET["SAMBADISPLAYPDBEDIT_STANDARD"]);
	exit ();
}
if(isset($_GET["SeMachineAccountPrivilege"])){
	echo USER_SAMBA_SET_LOCAL_PRIVS ();
	exit ();
}
if(isset($_GET["USER_SAMBA_FORM"])){
	echo USER_SAMBA_FORM ($_GET["userid"]);
	exit ();
}
if(isset($_GET["USER_SAMBA_ENABLE_PERFORM"])){
	USER_SAMBA_ENABLE_PERFORM ();
	exit ();
}
if(isset($_GET["USER_SAMBA_FORM"])){
	USER_SAMBA_FORM ($_GET["userid"]);
	exit ();
}

//groups
if(isset($_GET["load_user_section_group"])){
	echo USER_GROUP ($_GET["load_user_section_group"]);
	exit ();
}
if(isset($_GET["AddMemberGroup"])){
	AddMemberGroup ();
	exit ();
}

if(isset($_GET["POPUP_MEMBER_GROUP_ID"])){
	echo USER_GROUP_CONTENT ($_GET["userid"]);
	exit ();
}
if(isset($_GET["USER_GROUP_LIST"])){
	echo USER_GROUP_LIST ($_GET["USER_GROUP_LIST"]);
	exit ();
}

//clean
if(isset($_GET["USER_CLEAN_JS"])){
	echo USER_CLEAN_JS ();
	exit ();
}
if(isset($_GET["USER_CLEAN_POPUP"])){
	echo USER_CLEAN_POPUP ();
	exit ();
}
if(isset($_GET["USER_CLEAN_GROUPS"])){
	echo USER_CLEAN_GROUPS ();
	exit ();
}

if(isset($_GET["VolatileIPForm"])){
	AJAX_COMPUTER_DNS_JS ();
	exit ();
}
if(isset($_GET["dhcpfixedForm"])){
	AJAX_COMPUTER_DHCP_JS ();
	exit ();
}

if(isset($_GET["VolatileIPSHOW"])){
	echo AJAX_COMPUTER_DNS_FORM ($_GET["VolatileIPSHOW"]);
	exit ();
}
if(isset($_GET["VolatileIPAddressSave"])){
	AJAX_COMPUTER_DNS_JS_SAVE ();
	exit ();
}
if(isset($_GET["dhcpfixedSave"])){
	AJAX_COMPUTER_DHCP_JS_SAVE ();
	exit ();
}

//safebox
if(isset($_GET["safebox"])){
	USER_SAFEBOX ();
	exit ();
}

INDEX ();



function INDEX() {
	if (! isset ($_GET["userid"])){exit ();}
	if(isset($_GET["ajaxmode"])){AJAX_USER_FORM ();exit ();}
	USER_FORM ();

}

function AJAX_USER_STARTER() {
	
	switch ($_GET["section"]) {
		case "account" :
			echo USER_ACCOUNT($_GET["userid"]);
			break;
		case "account-popup" :
			echo USER_ACCOUNT_POPUP($_GET["userid"]);
			break;			
		
			
		case "address" :
			echo USER_ADDRESS ($_GET["userid"]);
			break;
		case "mailbox" :
			echo USER_MAILBOX ($_GET["userid"]);
			break;
		case "aliases" :
			echo USER_ALIASES ($_GET["userid"]);
			break;
		case "mailing_list" :
			echo USER_ALIASES_MAILING_LIST ($_GET["userid"]);
			break;
		case "fetchmail" :
			echo USER_FETCHMAIL ($_GET["userid"]);
			break;
		case "groups" :
			echo USER_GROUP ($_GET["userid"]);
			break;
		case "email" :
			echo USER_MESSAGING ($_GET["userid"]);
			break;
		case "ftp_access" :
			echo USER_FTP ($_GET["userid"]);
			break;
		case "file_share" :
			echo USER_SAMBA ($_GET["userid"]);
			break;
		case "computer" :
			echo AJAX_COMPUTER ($_GET["userid"]);
			break;
		case "openports" :
			echo AJAX_COMPUTER_OPENPORTS ($_GET["userid"]);
			break;
		case "ocs" :
			echo AJAX_COMPUTER_OCS ($_GET["userid"]);
			break;
		case "ressources" :
			echo AJAX_COMPUTER_RESSOURCES ($_GET["userid"]);
			break;
		case "material" :
			echo AJAX_COMPUTER_MATERIAL_OS ($_GET["userid"]);
			break;
		case "computer_aliases" :
			echo AJAX_COMPTER_ALIASES ($_GET["userid"]);
			break;
		case "safebox" :
			echo USER_SAFEBOX ();
			break;
		case "privs" :
			echo USER_PRIVILEGES ();
			break;
		
		default :
			echo AJAX_USER_TAB ();
			break;
	
	}

}

function AJAX_USER_TAB() {
	
	$users = new usersMenus ( );
	$users->LoadModulesEnabled ();
	$sock=new sockets();
	$as_connected_user = false;
	if(isset($_GET["userid"])){
		if (substr ($_GET["userid"], strlen ($_GET["userid"] ) - 1, 1 ) == '$') {
			$html = AJAX_COMPUTER_TAB ();
			SET_CACHED ( __FILE__, __FUNCTION__, $_GET["userid"], $html );
			return $html;
		}
	}
	
	if ($_GET["userid"] == $_SESSION ["uid"]) {
		$as_connected_user = true;
	}
	$page = CurrentPageName ();
	
	if ($_GET["hostname"] == null) {
		$hostname = $users->hostname;
		$_GET["hostname"] = $hostname;
	} else {
		$hostname = $_GET["hostname"];
	}
	$arr["account"] = "{account}";
	$userid = $_GET["userid"];
	
	if ($users->POSTFIX_INSTALLED) {$arr["email"] = "{messaging}";}
	if ($users->cyrus_imapd_installed) {$arr["mailbox"] = "{mailbox}";}
	if ($users->POSTFIX_INSTALLED) {$arr["aliases"] = "{aliases}";}
	if ($users->ZARAFA_INSTALLED) {$arr["mailbox"] = "{mailbox}";}
	
	$arr["groups"] = "{user_tab_groups}";
	
	writelogs("PUREFTP_INSTALLED=$users->PUREFTP_INSTALLED",__FUNCTION__,__FILE__,__LINE__);
	
	if ($users->PUREFTP_INSTALLED) {
		if($sock->GET_INFO("PureFtpdEnabled")==1){$arr["ftp_access"] = "{ftp_access}";}
	}
	
	if ($users->SAMBA_INSTALLED) {
		$arr["file_share"] = "{file_share}";
		if($users->CRYPTSETUP_INSTALLED) {$arr["safebox"]="{coffrefort}";}
	}
	

	
	if ($as_connected_user) {
		unset($arr["groups"]);
		unset($arr["file_share"]);
		unset($arr["ftp_access"]);
		unset($arr["mailbox"]);
		$arr["privs"] = "{privileges}";
	}else{
		
	}
	
	$arr["computer"] = "{computer}";
	
	if($users->EnableManageUsersTroughActiveDirectory){
		unset($arr["file_share"]);
		unset($arr["ftp_access"]);
		unset($arr["computer"]);
		unset($arr["privs"]);
		unset($arr["safebox"]);
		unset($arr["aliases"]);
	}
	
	
	while(list( $num, $ligne ) = each ($arr)){
		if ($num == "computer") {
			$toolbox [] = "<li><a href=\"domains.user.computer.php?userid=$userid&dn={$_GET["dn"]}\"><span>$ligne</span></a></li>";
			continue;
		}
	
		
		$toolbox[]="<li><a href=\"domains.edit.user.php?userid=$userid&ajaxmode=yes&section=$num&dn={$_GET["dn"]}\"><span>$ligne</span></a></li>";
		
		$html = $html . "<li><a href=\"javascript:LoadUserSectionAjax('$num','{$_GET["dn"]}')\" $class>$ligne</a></li>\n";
	}
	
	$html = "<div id=tablist style='margin-top:3px;margin-bottom:3px;'>$html</div>";
	$tpl = new templates ( );
	
	$html = "<div id='container-users-tabs' style='width:99%;margin:0px;background-color:white'>
			<ul>
				" . implode ( "\n\t", $toolbox ) . "
			</ul>
		</div>
		<script>
		 $(document).ready(function() {
			$(\"#container-users-tabs\").tabs();});
		</script>";
	
	$html = $tpl->_ENGINE_parse_body ( $html );
	SET_CACHED ( __FILE__, __FUNCTION__, $_GET["userid"], $html );
	return $html;

}

function AJAX_COMPUTER_DNS_JS() {
	$page = CurrentPageName ();
	$html = "
		var volatile;
		if(document.getElementById('VolatileIPAddress').checked){
			volatile=1;
		}else{
			volatile=0;
		}
		
var x_VolatileIPAddressSave= function (obj) {
		LoadAjax('computerdnsinfos','$page?VolatileIPSHOW='+document.getElementById('userid').value);
	}		
		
		var XHR = new XHRConnection();
	 	XHR.appendData('VolatileIPAddressSave',volatile);
		XHR.appendData('userid',document.getElementById('userid').value);
		XHR.sendAndLoad('$page', 'GET',x_VolatileIPAddressSave);  
	
	";
	echo $html;

}

function AJAX_COMPUTER_DHCP_JS() {
	$page = CurrentPageName ();
	
	$html = "
		var dhcpfixed;
		if(document.getElementById('dhcpfixed').checked){
			dhcpfixed=1;
		}else{
			dhcpfixed=0;
		}
		
var x_dhcpfixedSave= function (obj) {
		Loadjs('domains.edit.user.php?VolatileIPForm=yes');
	}			
		
		if(dhcpfixed==1){document.getElementById('VolatileIPAddress').checked=false;}
		
		var XHR = new XHRConnection();
	 	XHR.appendData('dhcpfixedSave',dhcpfixed);
		XHR.appendData('userid',document.getElementById('userid').value);
		XHR.sendAndLoad('$page', 'GET',x_dhcpfixedSave);  		
		
		
	
	";
	echo $html;

}

function AJAX_COMPUTER_DNS_JS_SAVE() {
	$comp = new computers ($_GET["userid"]);
	$comp->VolatileIPAddress = $_GET["VolatileIPAddressSave"];
	$comp->Edit();
}
function AJAX_COMPUTER_DHCP_JS_SAVE() {
	$comp = new computers ($_GET["userid"]);
	$comp->dhcpfixed = $_GET["dhcpfixedSave"];
	$comp->Edit ();
}

function AJAX_COMPUTER_TAB() {
	$users = new usersMenus ( );
	$users->LoadModulesEnabled ();
	$tpl = new templates ( );
	$page = CurrentPageName ();
	$as_connected_user = false;
	$cmp=new computers($_GET["userid"]);
	
	if ($_GET["section"] == null) {
		$_GET["section"] = "computer";
	}
	
	$arr["computer"] = "{computer}";
	$arr["material"] = "{materialos}";
	
	if ($users->BIND9_INSTALLED) {$arr["computer_aliases"] = "{alias}";}
	if ($users->OCSI_INSTALLED) {
		$ocs=new ocs();
		if($ocs->GET_HARDWARE_ID_FROM_MAC($cmp->ComputerMacAddress)>0){$arr["ocs"] = "{APP_OCSI}";}
	}
	
	$arr["openports"] = "{openports}";
	$arr["applications"]="{services}";
	$arr["ressources"] = "{netressources}";
	$arr["groups"] = "{groups}";
	
	
	
	if ($_GET["userid"] == 'newcomputer$') {unset ( $arr );$arr["computer"] = "{computer}";}
	
	while ( list ( $num, $ligne ) = each ( $arr ) ) {
		
		if($num=="applications"){
			$toolbox[]="<li><a href=\"computer.infos.php?popup-services=yes&uid={$_GET["userid"]}\"><span>$ligne</span></a></li>";
			continue;	
		}
		
		$toolbox[]="<li><a href=\"$page?userid={$_GET["userid"]}&ajaxmode=yes&section=$num\"><span>$ligne</span></a></li>";
	}
	
	$html = "<div id='container-computer-tabs' style='width:99%;margin:0px;background-color:white'>
			<ul>
				" . implode ( "\n\t", $toolbox ) . "
			</ul>
		</div>
		<script>
					$(document).ready(function(){
					$('#container-computer-tabs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			});
			
			
			
		</script>";
	
	return $tpl->_ENGINE_parse_body ( $html );

}

function AJAX_COMPUTER_RESSOURCES() {
	$computer = new computers ($_GET["userid"]);
	$sock = new sockets ( );
	$users = new usersMenus ( );
	$autofs = $users->autofs_installed;
	
	if ($computer->ComputerIP == '0.0.0.0') {
		$computer->ComputerIP = $computer->ComputerRealName;
	}
	
	$ini = new Bs_IniHandler ( );
	$ini->loadString ( $computer->ComputerCryptedInfos );
	$username = $ini->_params ["ACCOUNT"] ["USERNAME"];
	$password = $ini->_params ["ACCOUNT"] ["PASSWORD"];
	
	if ($username == null) {
		$username = 'nil';
	}
	if ($password == null) {
		$password = 'nil';
	}
	
	$datas = $sock->getFrameWork ( "cmd.php?ComputerRemoteRessources=$computer->ComputerIP&username=$username&password=$password" );
	$tbl = explode ( "\n", $datas );
	$tpl = new templates ( );
	$shareaccess = $tpl->_ENGINE_parse_body ( Paragraphe ( '64-credentials.png', '{shares_access}', '{shares_access_text}', "javascript:Loadjs('computer.passwd.php?uid={$_GET["userid"]}');" ), 'computer.scan.php' );
	
	$deploy = $tpl->_ENGINE_parse_body ( Paragraphe ( 'software-deploy-64.png', '{remote_install}', '{remote_install_text}', "javascript:Loadjs('computer.install.php?uid={$_GET["userid"]}');" ), 'storage.center.php' );
	
	if (is_array ( $tbl )) {
		$html = "<br>
		<table style='width:100%'>
		<tr>
		<td valign='top' width=1%>$deploy<hr>$shareaccess</td>
		<td valign='top'>
		<span style='font-size:16px'>$computer->ComputerRealName:: {netressources}</span>
		<br>
		<div style='width:100%;height:250px;overflow:auto'>
		<table style='width:99%' class=table_form>";
		while ( list ( $num, $ligne ) = each ( $tbl ) ) {
			
			if ($ligne == null) {
				continue;
			}
			if (preg_match ( "#(.+?);(.+)#", $ligne, $re )) {
				if ($re [2] == "clnt_create:") {
					continue;
				}
				if ($autofs) {
					switch ($re [1]) {
						case "NFS" :
							$autofs_script = "Loadjs('automount.php?src={$re[2]}&type=NFS&computer={$_GET["userid"]}');";
							break;
						
						case "SMB" :
							$re [2] = str_replace ( 'ADMIN$', 'c$', $re [2]);
							$autofs_script = "Loadjs('automount.php?src={$re[2]}&type=SMB&computer={$_GET["userid"]}');";
							break;
						default :
							;
							break;
					}
				
				}
				if ($autofs_script != null) {
					$autofs_script = "<input type='button' OnClick=\"javascript:$autofs_script\" value='{add_auto_connection}&nbsp;&raquo;'>";
				}
				$html = $html . "<tr " . CellRollOver () . ">
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td width=1%><strong style='font-size:12px'>{$re[1]}</strong></td>
					<td><strong style='font-size:12px'>{$re[2]}</strong></td>
					<td><strong style='font-size:12px'>$autofs_script</strong></td>
					</tr>
					";
			}
		
		}
	
	}
	
	$html = $html . "
			</table>
		</div>
	</td>
	
	</td>
	</tr>
	</table>";
	
	return $tpl->_ENGINE_parse_body ( $html, 'computer-browse.php' );

}

function AJAX_COMPUTER_OPENPORTS() {
	$computer = new computers ($_GET["userid"]);
	$sock=new sockets();
	$tbl = explode ( "\n", $computer->ComputerOpenPorts );
	$users = new usersMenus ( );
	if ($users->nmap_installed) {
		$button = Paragraphe ( "64-samba-find.png", "$computer->DisplayName", "{scan_it}", "javascript:NmapScanComputer('{$_GET["userid"]}')", "scan_your_network", 210 );
		$ComputersAllowNmap=$sock->GET_INFO("ComputersAllowNmap");
		if($ComputersAllowNmap==null){$ComputersAllowNmap=1;}
		if($ComputersAllowNmap==0){
			$button = Paragraphe ( "64-samba-find-grey.png", "$computer->DisplayName", "{scan_it}", "", "scan_your_network", 210 );
		}
	}
	
	
	
	$html = "
	<table style='width:100%'>
	<tr>
		<td valign='top'>
	<div  id='nmap'>
	<table style='width:100%'>";
	
	while ( list ( $num, $ligne ) = each ( $tbl ) ) {
		$html = $html . "<tr>
		<td><strong>$ligne</strong></td>
		</tr>";
	
	}
	$html = $html . "</table>
	</div>
	</td>
	<td valign='top' width=1%>$button</td>
	</tr>
	</table>";
	$html = "<H5>{openports}</H5>
	<div style='padding:20px;padding-top:0px;'>
	
	 " . RoundedLightGrey ( $html ) . "
	</div>";
	
	$tpl = new templates ( );
	
	return $tpl->_ENGINE_parse_body ( $html, 'computer-browse.php' );

}

function AJAX_COMPTER_ALIASES() {
	
	$_userid = str_replace ( '$', '', $_GET["userid"]);
	
	$add = RoundedLightGrey ( Paragraphe ( '96-computer-alias-add.png', '{add_alias}', '{add_alias_computer_text}', 'javascript:ComputerAddAlias()' ) );
	
	$html = "
	<input type='hidden' id='user_id' value='{$_GET["userid"]}'>
	<input type='hidden' id='ComputerAddAlias' value='{ComputerAddAlias}'>
	<br><H3>$_userid: {alias}</H3>
		<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/96-computer-alias.png' style='margin-top:15px'></td>
	<td valign='top' width=99%>
		<div id='computer_aliases'>" . AJAX_COMPTER_ALIASES_LIST () . "</div>
	</td>
	<td width=1% valign='top'>$add</td>
	</tr>
	</table>
	";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}
function AJAX_COMPTER_ALIASES_LIST($userid = null) {
	$computer = new computers ($_GET["userid"]);
	$html = "<table style='width:100%'>";
	while ( list ( $num, $ligne ) = each ( $computer->DNSCname ) ) {
		$html = $html . "
		<tr " . CellRollOver () . ">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:11px'>$ligne</td>
		<td width=1%>" . imgtootltip ( 'ed_delete.gif', '{delete}', "DeletComputerAliases('$ligne');" ) . "</td>
		</tr>";
	
	}
	
	$html = $html . "</table>";
	$tpl = new templates ( );
	return RoundedLightGreen ( $tpl->_ENGINE_parse_body ( $html ) );
}

function AJAX_COMPUTER_DNS_FORM($uid) {
	$computer = new computers ( $uid );
	if ($computer->dhcpfixed == 1) {
		$computer->VolatileIPAddress = 0;
	}
	
	if ($computer->VolatileIPAddress == 1) {
		$disabled = true;
	}
	$users = new usersMenus ( );
	if ($computer->DnsZoneName == null) {
		if(isset($_GET["zone-name"])){
			$computer->DnsZoneName = $_GET["zone-name"];
		}
	}
	if ($computer->DnsZoneName == "localhost") {
		if(isset($_GET["zone-name"])){
			$computer->DnsZoneName = $_GET["zone-name"];
		}
	}
	$ldap = new clladp ( );
	$domains = $ldap->hash_get_all_domains ();
	$DnsZoneName = Field_array_Hash ( $domains, "DnsZoneName", $computer->DnsZoneName, null, null, 0, null, $disabled );
	$dnstypeTable = array ("" => "{select}", "MX" => "{mail_exchanger}", "A" => "{dnstypea}" );
	$DnsType = Field_array_Hash ( $dnstypeTable, "DnsType", $computer->DnsType, null, null, 0, null, $disabled );
	
	$html = "			
<table style='width:100%'>
<tr>
					<td colspan=2><H5>{dns_information}</H5></td>
					
				</tr>
<tr>
					<td class=legend>{DnsZoneName}:</strong></td>
					<td align=left>$DnsZoneName</strong></td>
				</tr>	
				<tr>
					<td class=legend>{DnsType}:</strong></td>
					<td align=left>$DnsType</strong></td>
				</tr>	
				<tr>
					<td class=legend>{DnsMXLength}:</strong></td>
					<td align=left>" . Field_text ( 'DnsMXLength', $computer->DnsMXLength, 'width:50px', null, null, null, false, null, $disabled ) . "</strong></td>
				</tr>
</table>	";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );
}

function AJAX_COMPUTER_FORM() {
	if ($_GET["userid"] == "newcomputer$") {$add_computer = true;}
	$button_title="{apply}";
	if($add_computer){$button_title="{add}";}
	
	
	$_userid = str_replace ( '$', '', $_GET["userid"]);
	
	$html = "<H1>{$_userid} {computer}</H1>
	<input type='hidden' id='user_id' value='{$_GET["userid"]}'>
	
	<div  style='padding-left:20px;height:90%'>
		<div id='userform'>" . AJAX_COMPUTER () . "</div>
	</div>";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function AJAX_COMPUTER_MATERIAL_OS() {
	$computer = new computers ($_GET["userid"]);
	if ($_GET["userid"] == "newcomputer$") {$add_computer = true;}
	$button_title="{apply}";
	if($add_computer){$button_title="{add}";}
	
	
	$group = new groups ( $computer->gidNumber );
	$gpslist = $computer->Groups_list ();
	$users = new usersMenus ( );
	$sock = new sockets ( );
	$page = CurrentPageName ();
	$computerOS = utf8_encode($computer->ComputerOS);
	$computerOS_text = $computerOS;
	$computerOS2 = $computerOS;
	if (strlen ( $computerOS_text ) > 36) {
		$computerOS_text = texttooltip ( substr ( $computerOS_text, 0, 33 ) . '...', $computerOS_text, null, null, 1 );
		$computerOS2 = substr ( $computerOS_text, 0, 33 ) . '...';
	}
	$array = $computer->OSLIST ( true );
	$array [$computerOS] = $computerOS2;
	
	$computer_infos = "
<div style='font-size:13px;text-align:right;border-bottom:1px solid #005447;padding:5px;margin-bottom:5px'>
{ComputerOS}:$computerOS&nbsp;|&nbsp;$computer->ComputerMachineType&nbsp;|&nbsp;$computer->ComputerCPU</div>
<table style='width:100%'>
	<tr>
	<td colspan=2 valign='top'><img src='img/linux_cluster_install-128.png'></td>
	<td valign='top'>
			<table>
			
				<tr>
					<td class=legend>{ComputerCPU}:</strong></td>
					<td align=left>" . Field_text ( 'ComputerCPU', $computer->ComputerCPU, 'width:100%;font-size:13px' ) . "</strong></td>
				</tr>	
						
				<tr>
					<td class=legend>{ComputerMachineType}:</strong></td>
					<td align=left>
					" . Field_text ('ComputerMachineType', $computer->ComputerMachineType, 'width:100%;font-size:13px' ) . "
					</td>
				</tr>	
				<tr>
					<td class=legend>{ComputerOS}:</strong></td>
					<td align=left>
					" . Field_array_Hash ( $array, 'ComputerOS', $computerOS, 'width:100%;font-size:13px' ) . "
					</td>
				</tr>
				<tr>
					<td class=legend>{ComputerRunning}:</strong></td>
					<td align=left><strong>$computer->ComputerRunning</strong></td>
				</tr>			
				<tr>
					<td class=legend>{ComputerUpTime}:</strong></td>
					<td align=left><strong>$computer->ComputerUpTime</strong></td>
				</tr>			
				<tr>
					<td class=legend>{groupName}:</strong></td>
					<td align=left><strong>$group->groupName</strong></td>
				</tr>
				<tr>
				<td colspan=2 align='right'><hr>
					" . button ($button_title, 'ComputerSaveComputerInfosHard()' ) . "</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<script>
	var x_ComputerSaveComputerInfosHard= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);return;}
		Refreshtab('container-computer-tabs');
	}	
	function ComputerSaveComputerInfosHard(){
		var XHR = new XHRConnection();
		XHR.appendData('AJAX_COMPUTER_MATERIAL_OS_SAVE','yes');
		XHR.appendData('userid','{$_GET["userid"]}');
		XHR.appendData('ComputerCPU',document.getElementById('ComputerCPU').value);
		XHR.appendData('ComputerOS',document.getElementById('ComputerOS').value);
		XHR.appendData('ComputerMachineType',document.getElementById('ComputerMachineType').value);
		XHR.sendAndLoad('$page', 'GET',x_ComputerSaveComputerInfosHard);  
	}
</script>
";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $computer_infos );

}

function AJAX_COMPUTER_MATERIAL_OS_SAVE() {
	$computer = new computers ($_GET["userid"]);
	$computer->ComputerCPU = $_GET["ComputerCPU"];
	$computer->ComputerOS = $_GET["ComputerOS"];
	$computer->ComputerMachineType = $_GET["ComputerMachineType"];
	$computer->Edit ();
}

function AJAX_COMPUTER() {
	if ($_GET["userid"] == "newcomputer$") {$add_computer = true;}
	$button_title="{apply}";
	if($add_computer){$button_title="{add}";}	
	$computer = new computers ($_GET["userid"]);
	$group = new groups ( $computer->gidNumber );
	$gpslist = $computer->Groups_list ();
	$users = new usersMenus ( );
	$sock = new sockets ( );
	$page = CurrentPageName ();
	$tpl = new templates ( );
	$modify_js_text=$tpl->javascript_parse_text("{change}");
	
	$EnableDHCPServer = $sock->GET_INFO ( 'EnableDHCPServer' );
	
	if (is_array ( $gpslist )) {
		while ( list ( $num, $val ) = each ( $gpslist ) ) {
			$a_gpr [] = $val;
		}
	}
	
	$computer->uid = str_replace ( '$', '', $computer->uid );
	
	if ($users->KAV4SAMBA_INSTALLED) {
		$scan_computer = Paragraphe ( "64-find-virus.png", "{AV_REMOTE_SCAN}", "{AV_REMOTE_SCAN_TEXT}", "javascript:Loadjs('computer.scan.php?uid=$computer->uid');" );
	}
	
	if(trim($computer->uidNumber)==null){
		$field_dhcpfixed_disabled=true;
		$scan_computer=null;
		
	}
	
	
	$delete = Paragraphe ( 'delete-64.png', '{delete_this_computer}', "{delete_this_computer}",
	 "javascript:Loadjs('computer.delete.php?uid={$_GET["userid"]}')" );
	$bind9 = new bind9 ( );
	
	if ($EnableDHCPServer == 1) {
		$dhcp_fix = Field_checkbox("dhcpfixed",
		1, $computer->dhcpfixed, "Loadjs('$page?dhcpfixedForm=yes')", '{enable_disable}',$field_dhcpfixed_disabled);
	} else {
		$dhcp_fix = Field_checkbox( "dhcpfixed",
		1, $computer->dhcpfixed, null, '{no_feature_dhcp_server_not_enabled}',$field_dhcpfixed_disabled );
	}
	
	$VolatileIPAddress = Field_checkbox("VolatileIPAddress", 
		1, $computer->VolatileIPAddress, "Loadjs('$page?VolatileIPForm=yes')", '{enable_disable}',$field_dhcpfixed_disabled );
	

	
	$ini_USER = new Bs_IniHandler ( );
	$ini_USER->loadString ( $computer->ComputerCryptedInfos );
	$USERNAME_REMOTE = $ini_USER->_params ["ACCOUNT"] ["USERNAME"];
	
	if (! preg_match ( "#([0-9]+)\.([0-9]+)\.([0-9]+)#", $computer->DisplayName )) {
	if (preg_match ( "#(.+?)\.#", $computer->DisplayName, $re )) {$Diplayname = $re [1];} else {$Diplayname = $computer->DisplayName;}} else {$Diplayname = $computer->DisplayName;}
	
	$password = Paragraphe ( "cyrus-password-64.png", "{credentials_informations}", "{credentials_informations_text}", "javascript:Loadjs('computer.passwd.php?uid={$_GET["userid"]}')" );
	$computer_infos_services = Paragraphe ( "computer-tour-64.png", "{COMPUTER_INFOS_SERVICES}", "{COMPUTER_INFOS_SERVICES_TEXT}", 
	"javascript:Loadjs('computer.infos.php?uid=$computer->uid');" );
	
	
	
	$wakeonlan=Paragraphe("restart-64.png","{wakeup_computer}","{wakeup_computer_text}"
	,"javascript:Loadjs('computer.wakeonlan.php?uid={$_GET["userid"]}')" );
	
	//computer.wakeonlan.php
	
	
	
	
	
	if (is_array ( $a_gpr )) {
		$groups = "
	
			<tr>
				<td class=legend>{groups}:</strong></td>
				<td align=left><strong>" . implode ( ', ', $a_gpr ) . "</strong></td>
			</tr>	";
	}
	
	$MacField = Field_text ('ComputerMacAddress', 
	$computer->ComputerMacAddress, 'width:100%;font-size:14px;padding:3px;font-weight:bold',null,null,null,false,
	"ComputerFindByMac()" );
	
	if (IsPhysicalAddress($computer->ComputerMacAddress)) {
		$MacField = "<input type='hidden' name='ComputerMacAddress' id='ComputerMacAddress' value='$computer->ComputerMacAddress'>
		<code style='font-size:13px'>$computer->ComputerMacAddress</code>";
	}else{
		$mac_warn=imgtootltip("status_warning.gif","{WARNING_MAC_ADDRESS_CORRUPT}");
		$wakeonlan=Paragraphe("restart-64-grey.png","{wakeup_computer}","{wakeup_computer_text}","" );
	}
	
	$dns = AJAX_COMPUTER_DNS_FORM ($_GET["userid"]);
	
	if ($add_computer) {
		$scan_computer = null;
		$delete = null;
		$backup_icon = null;
		$computer_icon = null;
		$password = null;
		$computer_infos_services=null;
		$add_computer=1;
	}
	

	
	$html = "
	<input type='hidden' name='Yahoowin' id='Yahoowin' value='{$_GET["Yahoowin"]}'>
	<form name='FFM34567-{$_GET["userid"]}'>
	<input type='hidden' name='userid' id='userid' value='{$_GET["userid"]}'>
	<input type='hidden' name='gpid' value='{$_GET["gpid"]}'>
	<input type='hidden' name='SaveComputerInfo' value='yes'>
	<input type='hidden' name='add_computer_form' value='$add_computer'>
	
	
	<table style='width:100%'>
	<td width=1% valign='top'>
		<div id='computer_refresh_div'>$computer_icon</div>
		$password
		$computer_infos_services
		$scan_computer
		$wakeonlan
		$delete
		</td>
	<td valign='top' width=99%>
			<table style='width:100%'>
				<tr>
					<td colspan=3><H5>{network_information}</H5></td>
					
				</tr>				
				<tr>
					
					<td class=legend nowrap>{computer_name}:</strong></td>
					<td width=1%>&nbsp;</td>
					<td align=left>" . Field_text ( 'uid', $computer->uid, 'width:100%;font-size:14px;padding:3px;font-weight:bold;width:220px' ) . "</strong>&nbsp;<span id='modifyNameComp'></span></td>
				</tr>								
				<tr>
					
					<td class=legend nowrap>{computer_ip}:</strong></td>
					<td width=1%>&nbsp;</td>
					<td align=left>" . field_ipv4('ComputerIP', $computer->ComputerIP, 'font-size:14px;padding:3px;font-weight:bold' ) . "</strong></td>
				</tr>			
				<tr>
					
					<td class=legend nowrap>{ComputerMacAddress}:</strong></td>
					<td width=1%><span id='mac-warn'>$mac_warn</span></td>
					<td align=left>$MacField</strong></td>
				</tr>
				<tr>
					
					<td class=legend nowrap>{uid_number}:</strong></td>
					<td>&nbsp;</td>
					<td align=left><strong>$computer->uidNumber</strong></td>
				</tr>					
				<tr>
					
					<td class=legend nowrap>{dhcpfixed}:</strong></td>
					<td>&nbsp;</td>
					<td align=left>$dhcp_fix</td>
				</tr>	
				<tr>
					
					<td class=legend>{VolatileIPAddress}:</strong></td>
					<td>&nbsp;</td>
					<td align=left>$VolatileIPAddress</td>
				</tr>	
				</table>
				<div id='computerdnsinfos'>$dns</div>
				<table style='width:100%'>
											
				
				<tr>
					<td colspan=3 align='right'><hr style='border-color:#005447'>" . button ( $button_title, "SaveComputerForm('FFM34567-{$_GET["userid"]}');" ) . "
						
					</td>
				</tr>
				$computer_infos
				
				</table>
		</td>
		</tr>		
		</table>
	</form>
	<script>
	var m_userid;
var x_SaveComputerForm= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){
		alert(tempvalue);
		var re = new RegExp(/^ERROR/);
		m=re.exec(tempvalue);
        if(m){return false;}
	}
	
	if(document.getElementById('main_config_browse_computers')){RefreshTab('main_config_browse_computers');}
	if(document.getElementById('ZoneListComp')){Loadjs('index.bind9.php?script=yes');BindComputers(document.getElementById('ZoneListComp').value);}
	if(document.getElementById('browser-computers-list')){Loadjs('smb.browse.php?set-field='+document.getElementById('browser-computers-list').value);}
	if(document.getElementById('main-content')){Loadjs('start.php');}
	if(document.getElementById('crossroads-backend-list-table')){SearchBackendList();}
	setTimeout('ComputerRefresh()',1000);
}	

function ComputerRefresh(){
	if(document.getElementById('uid').value=='newcomputer'){return false;}
	var computer=document.getElementById('uid').value;
	var DnsZone=document.getElementById('DnsZoneName').value;
	YahooUser(870,'domains.edit.user.php?userid='+computer+'$&ajaxmode=yes',computer);
}
	
function SaveComputerForm(form_name){
	m_userid=document.getElementById('uid').value;
	//Form_name,pageToSend,return_box,noHidden,ReturnValues,idRefresh,uriRefresh,function_callback
	ParseForm(form_name,'domains.edit.user.php',false,false,false,'computer_refresh_div',null,x_SaveComputerForm);
}
	
var x_ComputerFindByMac= function (obj) {
	var results=obj.responseText;
	if(results.length>0){document.getElementById('mac-warn').innerHTML=results;}
	}		
	
	
	
	function ComputerFindByMac(){
		var ComputerMacAddress=document.getElementById('ComputerMacAddress').value;
		if(ComputerMacAddress.length==0){return;}
		var XHR = new XHRConnection();
		XHR.appendData('ComputerMacAddressFindUid',ComputerMacAddress);
		XHR.appendData('userid','{$_GET["userid"]}');
		XHR.sendAndLoad('$page', 'GET',x_ComputerFindByMac);	
		
	}
	
	
	function CheckUidComp(){
		var uid='$computer->uid';
		if(uid.length==0){return;}
		if(uid=='newcomputer'){return;}
		document.getElementById('uid').disabled=true;
		document.getElementById('modifyNameComp').innerHTML='&nbsp;&nbsp;<a href=javascript:blur(); OnClick=javascript:Loadjs(\"domains.computer.modifyname.php?userid={$_GET["userid"]}\"); style=\"font-size:14px;text-decoration:underline\">$modify_js_text</a>';
		
		
	}
CheckUidComp();
</script>	
	
	";
	
	
	return $tpl->_ENGINE_parse_body ( $html );
}


function AJAX_USER_WARNING(){
	$userid=$_GET["userid"];
	$page=CurrentPageName();
	$users=new usersMenus();
	$sock=new sockets();
	writelogs ( $userid, __FUNCTION__, __FILE__, __LINE__ );
	$user = new user ( $userid );
	$html="<table>";
	
	if($users->SAMBA_INSTALLED){
		if ($user->NotASambaUser) {
			$html=$html."
			<tr>
				<td width=1% valign='top'><img src='img/warning24.png'></td>
				<td style='font-size:13px;color:#D45D17'>
					<strong>{this_not_a_samba_user}</strong><br>
										
						
					<i style='font-size:10px;color:#D45D17'><input type='button' style='margin:0;font-size:10px;float:right' value='{activate}&nbsp;&raquo;&raquo;' OnClick=\"javascript:Loadjs('$page?enable-shared=yes&userid=$userid')\">{this_not_a_samba_user_explain}</i>
					

					</td>
			</tr>";
			
		}
		
		$datas=base64_decode($sock->getFrameWork("samba.php?idof={$_GET["userid"]}"));
		if(preg_match("#No such user#", $datas)){
			$html=$html."
			<tr>
				<td width=1% valign='top'><img src='img/warning24.png'></td>
				<td style='font-size:13px;color:#D45D17'>
					<strong>{id_no_such_user}</strong><br>
					<i style='font-size:10px;color:#D45D17'>{id_no_such_user_explain}</i>
					</td>
			</tr>";			
			
		}else{
			$html=$html."<tr>
				<td width=1% valign='top'><img src='img/24-green.png'></td>
				<td style='font-size:13px;color:black'>
				<div><strong style='font-size:13px'>{operating_system_user_has}</strong><br></div>
				<i style='font-size:13px;color:#black'>$datas</i>
				</td>
			</tr>";				
			
		}
		
	}
	
	if($users->ZARAFA_INSTALLED){
		$ZarafaUserSafeMode=$sock->GET_INFO("ZarafaUserSafeMode");
		if($ZarafaUserSafeMode==1){
			$html=$html."
			<tr>
				<td width=1% valign='top'><img src='img/error-24.png'></td>
				<td style='font-size:13px;color:#D45D17'>
					<strong>{this_not_a_samba_user}</strong><br>
					<i style='font-size:10px;color:#D45D17'>{ZARAFA_SAFEMODE_EXPLAIN}</i>
					</td>
			</tr>";	
			
		}
	}
	
	$html=$html."</table>";
	$tpl = new templates ( );
	echo  $tpl->_ENGINE_parse_body ( $html );	
}


function AJAX_USER_FORM() {
	
	if (substr ($_GET["userid"], strlen ($_GET["userid"] ) - 1, 1 ) == '$') {
		echo AJAX_COMPUTER_TAB ();
		exit ();
	}
	
	$user = new user ($_GET["userid"]);
	
	if ($user->gecos == 'Computer') {
		echo AJAX_COMPUTER_TAB ();
		exit ();
	}
	
	if ($_GET["userid"] == 'newcomputer$') {
		echo AJAX_COMPUTER_TAB ();
		exit ();
	}
	
	$tabs = AJAX_USER_TAB ();
	
	$html = "
	
	
	
	<div style='width:738px;background-image:url(img/bg_users.png);height:47px;margin-top:-9px;margin-left:-9px;margin-right:1px'>
		<div style='width:80%;padding-left:100px;padding-top:14px'><H3 style='color:#4C535C;font-size:22px;font-weight:normal'>{$_GET["userid"]}</H3></div>
	</div>
	<input type='hidden' id='user_id' value='{$_GET["userid"]}'>
	<div id='userform' style='background-color:#EDEDED;margin-left: -7px; width: 737px;'>" . AJAX_USER_TAB () . USER_ACCOUNT ($_GET["userid"] ) . "</div>
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $tabs );

}

function USER_CLEAN_JS() {
	$userid = $_GET["USER_CLEAN_JS"];
	$privilege = true;
	$page = CurrentPageName ();
	$tpl = new templates ( );
	$title = $tpl->_ENGINE_parse_body ( "{CLEAN_USER_DATAS}" );
	$no_privs = $tpl->_ENGINE_parse_body ( "{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}" );
	
	if (GetRights_aliases () == 0) {
		$privilege = false;
	}
	
	if (! $privilege) {
		echo ("alert('$no_privs')");
		exit ();
	}
	
	$html = "
			var USER_CLEAN_TIMEOUT=0;
			function LoadCleanUserForm(){
				YahooWin5(500,'$page?USER_CLEAN_POPUP=$userid','$title');
				LoadCleanUserFormWait();
			
			}
			
			function LoadCleanUserFormWait(){
				USER_CLEAN_TIMEOUT=USER_CLEAN_TIMEOUT+1;
				if(USER_CLEAN_TIMEOUT>10){alert('USER_CLEAN_POPUP: div failed');return;}
				if(!document.getElementById('USER_CLEAN_POPUP')){
					setTimeout(\"LoadCleanUserFormWait()\",1000);
					return;
				}
				USER_CLEAN_TIMEOUT=0;
				StartCleanUser();
				
			}
			
var X_StartCleanUser= function (obj) {
	var results=obj.responseText;
	document.getElementById('USER_CLEAN_POPUP').innerHTML=results;
	setTimeout(\"CleanUsersHide()\",2000);
	}

function CleanUsersHide(){
	YahooUserHide();
	YahooWinHide();
	LoadGroupSettings('config');
	YahooWin5Hide();
}
			
			function StartCleanUser(){
				var XHR = new XHRConnection();
				XHR.appendData('USER_CLEAN_GROUPS','$userid');
				document.getElementById('USER_CLEAN_POPUP').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET', X_StartCleanUser);		
			}			

			

LoadCleanUserForm();		";
	
	echo $html;

}

function USER_CLEAN_GROUPS() {
	$groups = new groups ( );
	$groups->user_delete_from_all_groups ($_GET["USER_CLEAN_GROUPS"]);

}

function USER_CLEAN_POPUP() {
	$uid = $_GET["USER_CLEAN_POPUP"];
	$html = "<H1>$uid:: {CLEAN_USER_DATAS}</H1>
	" . RoundedLightWhite ( "
	<div id='USER_CLEAN_POPUP'></div>" );
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_ALIASES_DELETE_JS() {
	$page = CurrentPageName ();
	$html = "

	var x_DelAliases= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		LoadAjax('aliases_list','$page?aliases-users-list=yes&uid={$_GET["uid"]}');
	}		
	var XHR = new XHRConnection();
	XHR.appendData('DeleteAliases','{$_GET["uid"]}');
	XHR.appendData('aliase','{$_GET["mail"]}');
	if(document.getElementById('ali')){document.getElementById('ali').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';}	
	XHR.sendAndLoad('$page', 'GET',x_DelAliases);		
	";
	echo $html;
}

function USER_ALIASES_FORM_ADD_JS() {
	$userid = $_GET["USER_ALIASES_FORM_ADD_JS"];
	$privilege = true;
	$page = CurrentPageName ();
	$tpl = new templates ( );
	$title = $tpl->_ENGINE_parse_body ( "{add_new_alias}" );
	$no_privs = $tpl->_ENGINE_parse_body ( "{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}" );
	if (GetRights_aliases () == 0) {
		$privilege = false;
	}
	
	if (! $privilege) {
		die ( "alert('$no_privs')" );
	}
	
	$html = "
		
			function LoadAddAliasForm(){
				YahooWin5(500,'$page?USER_ALIASES_FORM_ADD=$userid','$title');
			
			}
			
			var x_AddNewAliasesUser= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				LoadAjax('aliases_list','$page?aliases-users-list=yes&uid=$userid');
			}			
			
			
			function  AddNewAliases(){
				var uid='$userid';
				m_userid=uid;
				var aliase=document.getElementById('aliases').value;
				var aliase_domain=document.getElementById('user_domain').value;
				var fullaliase=document.getElementById('fullaliase').value;
				aliase=aliase+'@'+aliase_domain;
				if(fullaliase.length>0){aliase=fullaliase;}
				var XHR = new XHRConnection();
				XHR.appendData('AddAliases',uid);
				XHR.appendData('aliase',aliase);
				if(document.getElementById('ali')){
					document.getElementById('ali').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				}
				XHR.sendAndLoad('domains.edit.user.php', 'GET',x_AddNewAliasesUser);
				
			}
			
			
			LoadAddAliasForm();
		
		
		";
	
	echo $html;

}

function USER_ALIASES_FORM_ADD() {
	
	$userid = $_GET["USER_ALIASES_FORM_ADD"];
	$ldap = new clladp ( );
	$user = new user ( $userid );
	$domains = $ldap->hash_get_domains_ou ( $user->ou );
	$user_domains = Field_array_Hash ( $domains, 'user_domain',null,null,null,0,'font-size:14px;padding:3px' );
	
	$form_catech_all = 

	$form_add = "
    			<table style='width:100%;border:0px solid #005447'>
    				<tr>
    					<td nowrap colspan=2><strong style='font-size:12.5px;'>{add_new_alias}:&laquo;{in_the_same_organization}&raquo;</strong></td>
    				</tr>
    				<tr>
    					<td valign='top'>
	    					<table>
	    						<tr>
	    							<td>" . Field_text ( 'aliases', null, 'width:150px;font-size:14px;padding:3px',null,null,null,false,"AddNewAliasesCheckEnter(event)" ) . "</td>
	    							<td width=1%><strong style='font-size:14px;'>@</strong></td>
	    							<td width=99% align='left'>$user_domains</td>
	    						</tr>
	    					</table>
    					</td>
    				</tr>
   				<tr>
   						<td nowrap colspan=2>&nbsp;</td>
   				</tr>
   				<tr>
    				<td nowrap colspan=2><strong style='font-size:12.5px;'>{add_new_alias}:&laquo;{out_of_organization}&raquo;</strong></td>
    			</tr>
    			<tr>
    				<td valign='top'>
	    					<table>
	    						<tr>
	    							<td>" . Field_text ( 'fullaliase', null, 'width:250px;font-size:14px;padding:3px',null,null,null,false,"AddNewAliasesCheckEnter(event)"  ) . "</td>
	    						</tr>
	    					</table>
    				</td>
    			</tr>    				
    				<tr>
    					<td colspan=2 align='right'><hr>
    					" . button ( "{submit}", "AddNewAliases('$userid');" ) . "
    						
    						
    					</td>
    			</tr>
   				  			
    			</table>";
	
	$html = "
<div class=explain>{aliases_text}:&nbsp;&laquo;<b>{$user->mail}&raquo;</b></div>
$form_add



<script>
	function AddNewAliasesCheckEnter(e){
		if(!checkEnter(e)){return;}
		AddNewAliases('$userid');
	}
</script>
";
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function USER_ALIASES($userid) {
	if ($userid == null) {
		$userid = $_GET["userid"];
	}
	if ($_GET["aliases-section"] == 'mailing_list') {
		return USER_ALIASES_MAILING_LIST ( $userid );
	}
	$ldap = new clladp ( );
	$user = new user ( $userid );
	$tpl = new templates ( );
	if ($user->DoesNotExists) {
		return USER_NOTEXISTS ( $userid );
	}
	
	$page = CurrentPageName ();
	$aliases = $user->aliases;
	$boutton = button ( "{add_new_alias}", "Loadjs('$page?USER_ALIASES_FORM_ADD_JS=$userid');" );
	$no_priv = $tpl->javascript_parse_text ( "{ERROR_NO_PRIVS}" );
	$boutton_off = button ( "{add_new_alias}", "alert('$no_priv');" );
	$privilege = true;
	if (GetRights_aliases () == 0) {
		$privilege = false;
	}
	if (! $privilege) {
		$boutton = $boutton_off;
	}
	
	if (is_array ( $aliases )) {
		while ( list ( $num, $ligne ) = each ( $aliases ) ) {
			$delete = imgtootltip ( 'x.gif', '{delete aliase}', "Loadjs('$page?delete-aliases=yes&mail=$ligne&uid=$userid')" );
			if (! $privilege) {
				$delete = null;
			}
			$ali = $ali . "<tr " . CellRollOver () . ">
    		<td width=1%><img src='img/mailbox_storage.gif'></td>
    		<td style='padding:3px;font-size:14px;font-weight:bolder;color:#005447' width=91% nowrap align='left'>$ligne</td>
    		<td style='padding:3px;' width=1%>" . imgtootltip ( 'test-mail-22.png', '{send_a_test_mail_text}', "Loadjs('postfix.sendtest.mail.php?rcpt=$ligne')" ) . "</td>
    		
    		<td  style='padding:3px;' width=1%>$delete</td>
    		</tr>
    		";
		}
		$ali = $ali . "<tr><td colspan=3><hr></td></tr>";
	
	}
	
	$title = "
    	<table style='width:100%'>
    		<tr>
    		<td valign='top' width=80%>
    			<h1>{aliases}:&nbsp;&laquo;$user->uid&raquo;</h1>
    		</td>
    			<td valign='top'>$boutton</td>
    		</tr>
    	</table>";
	
	$aliases_list = "
    	<div style='width:99%;height:250px;overflow:auto' id='ali'>
			<table style='width:100%'>
		    	</tr>
					$ali
				<tr>
	    	</table>
    	</div>";
	if(isset($_GET["aliases-users-list"])){
		echo $tpl->_ENGINE_parse_body ( $aliases_list );
		exit ();
	}
	
	$html = "
    	
    	$title
    	<div class=explain>{aliases_text}:&nbsp;&laquo;<b>$user->mail&raquo;</b></div>
    	<table style='width:100%'>
    	<tr>
    		<td valign='top' width=1%><br><img src='img/96-bg_addresses.png' style='margin-right:30px'></td>
    		<td valign='top' width=98%><div id='aliases_list'>$aliases_list</div></td>
    		
    	</tr>
    	</table>";
	
	return $tpl->_ENGINE_parse_body ( $html );
}

function USER_FORM() {
	
	if (! isset ($_GET["ajaxmode"])){
		$ldap = new clladp ( );
		$hash = $ldap->UserDatas ($_GET["userid"]);
		$ou = $hash ["ou"];
		$title = "{create_user}:{$hash["displayName"]}";
		$html = "<div style='padding:50px;tect-align:center'>
		<input type='button' OnClick=\"javascript:LoadWindows(740,740,'domains.edit.user.php','userid={$_GET["userid"]}&ajaxmode=yes',true,true);\" 
		value='$title' style='width:450px;height:100px'></div>
		<script>LoadWindows(740,740,'domains.edit.user.php','userid={$_GET["userid"]}&ajaxmode=yes',true,true);</script>";
		$cfg ["JS"] [] = "js/edit.user.js";
		$tpl = new template_users ( $title, $html, 0, 0, 0, 0, $cfg );
		echo $tpl->web_page;
		exit ();
	}
	
	$ldap = new clladp ( );
	$hash = $ldap->UserDatas ($_GET["userid"]);
	$ou = $hash ["ou"];
	$title = "{create_user}:{$hash["displayName"]}";
	$priv = new usersMenus ( );
	$userid = $_GET["userid"];
	$html = "
	<table style='width:100%'>
	<td valign='top'>
		<table style='width:100%;border-right:1px solid #CCCCCC' >
			<tr>
				<td valign='top'> " . Paragraphe ( 'folder-org-64.jpg', '{organization}', '{back_to} ' . $ou, "domains.manage.org.index.php?ou=$ou" ) . "</td>
			</tr>
		
		
			<tr>
				<td valign='top'> " . Paragraphe ( 'folder-user-64.jpg', '{account}', '{manage_account_text}', "javascript:LoadUsersTab(\"$userid\",\"0\")" ) . "</td>
			</tr>
			<tr>
			<td valign='top'> " . Paragraphe ( 'folder-usermailbox-64.jpg', '{mailbox}', '{manage_mailbox_text}', "javascript:LoadUsersTab(\"$userid\",\"1\")" ) . "</td>		
			</tr>
			<tr>
			<td valign='top'> " . Paragraphe ( 'folder-useraliases-64.jpg', '{aliases}', '{manage_aliases_text}', "javascript:LoadUsersTab(\"$userid\",\"2\")" ) . "</td>		
			</tr>
			<tr>
			<td valign='top'> " . Paragraphe ( 'folder-address-64.jpg', '{address}', '{address_text}', "javascript:LoadUsersTab(\"$userid\",\"4\")" ) . "</td>		
			</tr>
			<tr>
			<td valign='top'> " . Paragraphe ( 'folder-usermove-64.jpg', '{move_member}', '{move_member_text}', "javascript:LoadUsersTab(\"$userid\",\"3\")" ) . "</td>		
			</tr>
			</table>	
		</td>
		<td valign='top'><div id='userdatas'>
		" . DatasTab ($_GET["userid"] ) . "</div>
	
	
		</td>
	</tr>
	</table>
	";
	
	$cfg ["JS"] [] = "js/edit.user.js";
	$tpl = new template_users ( $title, $html, 0, 0, 0, 0, $cfg );
	echo $tpl->web_page;

}

function DatasTab($userid) {
	if (! isset ($_GET["tab"])){
		return USER_ACCOUNT ( $userid );
	}
	switch ($_GET["tab"]) {
		case 0 :
			return USER_ACCOUNT ( $userid );
			break;
		case 1 :
			return USER_MAILBOX ( $userid );
			break;
		case 2 :
			return USER_ALIASES ( $userid );
			break;
		case 3 :
			return USER_GROUP ( $userid );
			break;
		case 4 :
			return USER_ADDRESS ( $userid );
			break;
		
		default :
			break;
	}
}

function USER_ADDRESS($userid) {
	$as_connected_user = false;
	if ($userid == $_SESSION ["uid"]) {
		$as_connected_user = true;
	}
	$page = CurrentPageName ();
	$user = new user ( $userid );
	
	$priv = new usersMenus ( );
	//ParseForm(Form_name,pageToSend,return_box,noHidden,ReturnValues,idRefresh,uriRefresh,function_callback){
	$uri_returned = "domains.edit.user.php?userid=$userid&ajaxmode=yes&section=address";
	$button = "<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform','" . basename ( __FILE__ ) . "',true,false,false,'userform','$uri_returned');\">";
	if (! $as_connected_user) {
		if ($priv->AllowAddUsers == false) {
			$button = null;
		}
	}
	
	$title = "<h5>{$user->DisplayName}&nbsp;$userid:{profile}</H5><br>";
	$form = "
	<form name='userLdapform'>
	<input type='hidden' name='UserAddressSubmitedForm' value='$userid'>
	<input type='hidden' name='DisplayName' id='DisplayName' value='$user->DisplayName'>
		<table style='width:100%'>		
			<tr>
				<td align='right' class=legend nowrap>{givenName}:</strong>
				<td>" . Field_text ( 'givenName', $user->givenName, 'width:150px' ) . "</td>
			</tr>
			<tr>
				<td align='right' class=legend nowrap>{sn}:</strong>
				<td>" . Field_text ( 'sn', $user->sn, 'width:150px' ) . "</td>
			</tr>
			<tr>
				<td colspan=2><hr></td>
			</tr>			
			<tr>
				<td align='right' class=legend nowrap>{phone}:</strong>
				<td>" . Field_text ( 'telephoneNumber', $user->telephoneNumber, 'width:150px' ) . "</td>
			</tr>
			<tr>
				<td align='right' class=legend nowrap>{mobile}:</strong>
				<td>" . Field_text ( 'mobile', $user->mobile, 'width:150px' ) . "</td>
			</tr>
			<tr><td colspan=2><hr></td></tr>
			<tr>
				<td align='right' class=legend nowrap>{street}:</strong>
				<td>" . Field_text ( 'street', $user->street, 'width:100%' ) . "</td>
			</tr>			
			<tr>
				<td align='right' class=legend nowrap>{postalAddress}:</strong>
				<td>" . Field_text ( 'postalAddress', $user->postalAddress, 'width:100%' ) . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{CP} & {town}</strong>
				<td>" . Field_text ( 'postalCode', $user->postalCode, 'width:50px' ) . "&nbsp;" . Field_text ( 'town', $user->town, 'width:180px' ) . "</td>
			</tr>	
			<tr>
				<td align='right'nowrap class=legend>{BP}:</strong>
				<td>" . Field_text ( 'postOfficeBox', $user->postOfficeBox, 'width:100px' ) . "</td>
			</tr>	
			<tr><td colspan=2 style='padding-right:10px' align='right'>$button</td></tr>		
					
		</table>
		</form>

	";
	
	$html = "
		<table style='width:100%'>
			<tr>
				<td valign='top' align='center'><br><img src='img/96-bg_mailbox.png'></td>
				<td valign='top'>
				$title
				<div id='useraddr'>
				$form
				</div><p>&nbsp;</p><p>&nbsp;</p></td>
			</tr>
		</table>
	";
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_SAMBA_PRIVILEGES() {
	
	$page = CurrentPageName ();
	$tpl = new templates ( );
	$title = $tpl->_ENGINE_parse_body ( '{SAMBA_GROUP_PRIVILEGES}' );
	$userid = $_GET["SAMBA_PRIVILEGES"];
	$html = "
	
	function USER_SAMBA_PRIVILEGES(){
		YahooWin4(400,'$page?SAMBA_PRIVILEGES_PAGE=$userid','$title::$userid');
	
	}
	
var x_SetSambaPrimaryGroupGID=function(obj){
     var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
	document.getElementById('dialog4').innerHTML='';
	YahooWin4Hide();
	LoadAjax('USER_SAMBA_FORM','$page?USER_SAMBA_FORM=yes&userid=$userid');
	
	}	
	
	function SetSambaPrimaryGroupGID(){
		var XHR = new XHRConnection();
	    XHR.appendData('SAMBA_SET_PRIVILEGES_GROUP',document.getElementById('sambaPrimaryGroupGID').value);
     	XHR.appendData('userid','$userid');
       	document.getElementById('USER_SAMBA_PRIVILEGES_PAGE').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
       	XHR.sendAndLoad('$page', 'GET',x_SetSambaPrimaryGroupGID);
	}
	
	USER_SAMBA_PRIVILEGES();
	";
	
	echo $html;
}

function USER_SAMBA_SET_PRIVILEGES_GROUP() {
	$group = new groups ( );
	$group->SambaGroupsBuild ();
	$user = new user ($_GET["userid"]);
	$user->accountGroup = $_GET["SAMBA_SET_PRIVILEGES_GROUP"];
	$user->sambaPrimaryGroupGID = $_GET["SAMBA_SET_PRIVILEGES_GROUP"];
	$user->Samba_edit_user ();

}

function USER_SAMBA_PRIVILEGES_PAGE() {
	$userid = $_GET["SAMBA_PRIVILEGES_PAGE"];
	$user = new user ( $userid );
	$groups = new groups ( );
	$page = CurrentPageName ();
	$hash = $groups->samba_standard_groups ();
	$field = Field_array_Hash ( $hash, 'sambaPrimaryGroupGID', $user->sambaPrimaryGroupGID,"style:font-size:16px;padding:3px" );
	
	$priv ["SeMachineAccountPrivilege"] = "0";
	$priv ["SeTakeOwnershipPrivilege"] = "0";
	$priv ["SeBackupPrivilege"] = "0";
	$priv ["SeRestorePrivilege"] = "0";
	$priv ["SeRemoteShutdownPrivilege"] = "0";
	$priv ["SePrintOperatorPrivilege"] = "0";
	$priv ["SeAddUsersPrivilege"] = "0";
	$priv ["SeDiskOperatorPrivilege"] = "0";
	
	$user = new user ( );
	$localprivs = $user->GetUsersSambaPrivileges ( $userid );
	
	while ( list ( $num, $val ) = each ( $priv ) ) {
		if ($localprivs [$num] == 1) {
			$priv [$num] = 1;
			unset ( $localprivs [$num]);
		}
	
	}
	
	if (is_array ( $localprivs )) {
		while ( list ( $num, $val ) = each ( $localprivs ) ) {
			if (trim ( $val ) == null) {
				continue;
			}
			$error = $error . "<div style='color:red'>$val</div>";
		}
	}
	reset ( $priv );
	while ( list ( $num, $val ) = each ( $priv ) ) {
		$privileges = $privileges . "
		<tr>
			<td class=legend nowrap>{{$num}}</td>
			<td>" . Field_checkbox ( $num, 1, $val ) . "</td>
		</tr>
		";
	}
	
	$html = "
	<div class=explain>{SAMBA_GROUP_PRIVILEGES_WIZARD}</div>
	<div id='USER_SAMBA_PRIVILEGES_PAGE'>
	<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>{SAMBA_GROUP_PRIVILEGES}</td>
			<td>$field</td>
		</tr>
		<tr>
			<td colspan=2 align='right'>
			<hr>
			" . button ( "{edit}", "SetSambaPrimaryGroupGID()" ) . "
		</tr>
	</table>
	<br>
	$error
	<form name='FFMPRIVS_$userid'>
	<input type='hidden' name='userid' value='$userid'>
	<table style='width:100%' class=table_form>
	$privileges
	<tr>
		<td colspan=2 align='right'>
		<hr>
		" . button ( "{edit}", "ParseForm('FFMPRIVS_$userid','$page',true);" ) . "
	
	</tr>
	</table>
	</div>
	
	
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function USER_SAMBA($userid) {
	
	$page = CurrentPageName ();
	
	if ($_GET["smb-section"] == null) {
		$_GET["smb-section"] = "parameters";
	}
	
	switch ($_GET["smb-section"]) {
		case "parameters" :
			$insert = USER_SAMBA_FORM ( $userid );
			break;
		case "smb-infos" :
			$insert = USER_SAMBA_INFOS ( $userid );
			break;
		default :
			$insert = USER_SAMBA_FORM ( $userid );
			break;
	}
	
	$error = USER_SAMBA_DISPLAY_STATUS ( $userid );
	
	$WHATHESEE=Paragraphe("user-folder-64.png", "{WHAT_USER_SEE}", "{WHAT_USER_SEE_SMB_TEXT}","javascript:Loadjs('domains.edit.user.smbsee.php?uid=$userid')");
	
	
	
	
	$html = "<div id='samba_div'>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=200px>
				" . Paragraphe ( '64-Folder-privileges.png', '{SAMBA_GROUP_PRIVILEGES}', '{SAMBA_GROUP_PRIVILEGES_TEXT}', "javascript:Loadjs('$page?SAMBA_PRIVILEGES=$userid')", '{SAMBA_GROUP_PRIVILEGES}', "" ) . "
				$error
				$WHATHESEE
				</td>
			<td valign='top' ><h5>$userid:{file_share}</H5>
			<div id='USER_SAMBA_FORM'>
					$insert
			</div>
	</td>
	</tr>
	</table>
	
	
	
		
	</div>";
	$tpl = new templates ( );
	return div_grey ( $tpl->_ENGINE_parse_body ( $html ) );

}

function USER_SAMBA_DISPLAY_STATUS($userid) {
	$tpl = new templates ( );
	$page = CurrentPageName ();
	$sock = new sockets ( );
	$datas = $sock->getfile ( "pdbedit:$userid" );
	$tb = explode ( "\n", $datas );
	while ( list ( $num, $ligne ) = each ( $tb ) ) {
		if (trim ( $ligne ) == null) {
			continue;
		}
		
		if (preg_match ( '#pdb_get_group_sid:(.+)#', $ligne, $re )) {
			if (preg_match ( "#ailed#", $re [1], $ri )) {
				$js = "javascript:YahooWin4(700,'$page?SAMBADISPLAYPDBEDIT=$userid','{SAMBA_ERROR_USER}');";
				return $tpl->_ENGINE_parse_body ( Paragraphe ( "danger64.png", "{SAMBA_ERROR_USER}", "{SAMBA_ERROR_REPORT}<br><strong style='color:red'>{$re[1]}</strong>{SAMBA_ERROR_CLICK}", $js, "{SAMBA_ERROR_CLICK}" ) );
			}
		}
	
	}
	$js = "javascript:YahooWin4(700,'$page?SAMBADISPLAYPDBEDIT_STANDARD=$userid','{smb_infos}','{smb_infos_text}');";
	return $tpl->_ENGINE_parse_body ( Paragraphe ( "64-info.png", "{smb_infos}", "{smb_infos_text}", $js, "{smb_infos}" ) );

}

function USER_SAMBA_DISPLAY_PDBEDIT() {
	$userid = $_GET["SAMBADISPLAYPDBEDIT"];
	writelogs ( $userid, __FUNCTION__, __FILE__, __LINE__ );
	$sock = new sockets ( );
	$tb = unserialize ( base64_decode ( $sock->getFrameWork ( "cmd.php?Debugpdbedit=$userid" ) ) );
	
	while ( list ( $num, $ligne ) = each ( $tb ) ) {
		if (trim ( $ligne ) == null) {
			continue;
		}
		$table = $table . "<div><code>" . htmlentities ( $ligne ) . "</code></div>";
	}
	
	$html = "<H1>{SAMBA_ERROR_REPORT}::$userid</H1>
	<div style='background-color:white;width:99%;height:300px;overflow:auto'>$table</div>
	
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_SAMBA_INFOS($userid) {
	writelogs ( $userid, __FUNCTION__, __FILE__, __LINE__ );
	$sock = new sockets ( );
	$tb = unserialize ( base64_decode ( $sock->getFrameWork ( "cmd.php?pdbedit=$userid" ) ) );
	
	$table="
	
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
	<tr>
		<th width=1%></th>
		<th>&nbsp;</th>
	</tr>
	</thead>
<tbody class='tbody'>";
	
	
	while ( list ( $num, $ligne ) = each ( $tb ) ) {
		if (trim ( $ligne ) != null) {
			if (preg_match ( '#(.+?):(.*)#', $ligne, $re )) {
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$table = $table . "
						<tr class=$classtr>
						<td nowrap width=1% class=legend  style='font-size:14px'>" . trim ( $re [1] ) . ":</td>
						<td style='font-size:14px;font-weight:bold'>{$re[2]}</td>
						</tr>
						
					\n";
			} else {
				$t[]="<div style='width:240px;font-size:14px'>$ligne</div>\n";
			}
		
		}
	
	}
	
	$html = "
	
	<div style='width:100%;height:500px;overflow:auto'>$table</table>". @implode("\n", $t)."<div>";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );
}

function USER_SAMBA_SET_LOCAL_PRIVS() {
	$tpl = new templates ( );
	$user = new user ( );
	$rootpassword = $user->GetRootPassword ();
	if ($rootpassword == null) {
		echo $tpl->_ENGINE_parse_body ( 'No root password set for Samba admin domain !' );
		exit ();
	}
	
	$uid = $_GET["userid"];
	unset ($_GET["userid"]);
	$sock = new sockets ( );
	while ( list ( $num, $ligne ) = each ($_GET ) ) {
		if ($ligne == 1) {
			$echo = $echo . "{{$num}}:" . $sock->getfile ( "SetNetUsePrivs:$rootpassword;$num;$uid;grant" ) . "\n";
		} else {
			$echo = $echo . "{{$num}}:" . $sock->getfile ( "SetNetUsePrivs:$rootpassword;$num;$uid;revoke" ) . "\n";
		}
	
	}
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $echo );

}

function USER_NOT_SAMBA($userid) {
	$page = CurrentPageName ();
	$html = "<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='shareAccessEnablePicture'><img src='img/not-samba-128.png'></div>
		</td>
		<td valign='top'><div style='font-size:16px;font-weight:bold;color:#DD2222'>{this_not_a_samba_user}</div>
		<hr>
		<div style='font-size:13px'>{this_not_a_samba_user_explain}</div>
		
		<center style='margin:35px'>" . button ( "{enable}", "Loadjs('$page?enable-shared=yes&userid=$userid')" ) . "</center>
		
	</tr>
	</table>
	
	";
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );
}

function USER_SAMBA_ENABLE_JS() {
	$page = CurrentPageName ();
	$userid = $_GET["userid"];
	$html = "
		
	var x_EnableSharedAccessStart=function(obj){
    	var tempvalue=obj.responseText;
	  	if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('container-users-tabs');
		}
	
		
	
	
		function EnableSharedAccessStart(){
			var img='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'
			if(document.getElementById('shareAccessEnablePicture')){
				document.getElementById('shareAccessEnablePicture').innerHTML=img;
			}
			if(document.getElementById('userid-warning')){
				document.getElementById('userid-warning').innerHTML=img;
			}
			var XHR = new XHRConnection(); 
			XHR.appendData('USER_SAMBA_ENABLE_PERFORM','yes');
			XHR.appendData('userid','$userid');
			XHR.sendAndLoad('$page', 'GET',x_EnableSharedAccessStart);
				 
		}
		
	EnableSharedAccessStart();
	
	";
	
	echo $html;
}
function USER_SAMBA_ENABLE_PERFORM() {
	$page = CurrentPageName ();
	$uid = $_GET["userid"];
	$firstGroup = 545;
	$user = new user ($_GET["userid"]);
	$user->accountGroup = $firstGroup;
	$user->sambaPrimaryGroupGID = $firstGroup;
	$user->Samba_edit_user ();
}

function USER_SAMBA_FORM($userid) {
	writelogs ( $userid, __FUNCTION__, __FILE__, __LINE__ );
	$user = new user ( $userid );
	if ($user->DoesNotExists) {return USER_NOTEXISTS ( $userid );}
	if ($user->NotASambaUser) {return USER_NOT_SAMBA ( $userid );}
	
	$page = CurrentPageName ();
	$priv = new usersMenus ( );
	$button = "<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform','" . basename ( __FILE__ ) . "',true,false);\">";
	$button = "<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseFormFileShare();\">";
	$groups = new groups ( );
	$hash = $groups->samba_standard_groups ();
	$samba_group_name = $hash ["$user->sambaPrimaryGroupGID"];
	$sock = new sockets ( );
	$SambaRoamingEnabled = $sock->GET_INFO ( 'SambaRoamingEnabled' );
	
	if ($SambaRoamingEnabled == 1) {
		$roaming_path = "	<tr>
				<td align='right' nowrap class=legend>{sambaProfilePath}:</strong>
				<td><code>$user->sambaProfilePath</code></td>
			</tr>";
	} else {
		$roaming_path = "	<tr>
						<td align='right' nowrap class=legend>{sambaProfilePath}:</strong>
						<td><code>{disabled}</code></td>
					</tr>";
	}
	
	if ($priv->AllowAddUsers == false) {
		$button = null;
	}
	
	$gps = $user->samba_groups;
	$gps [null] = "{select}";
	$sambaPrimaryGroupSID = Field_array_Hash ( $gps, 'sambaPrimaryGroupGID', $user->sambaPrimaryGroupGID );
	
	if ($user->AsAnSambaAccount == 1) {
		$enablesamba = "<img src='img/status_ok.gif'><input type='hidden' name='AsAnSambaAccount' id='AsAnSambaAccount' value='1'>";
	} else {
		$enablesamba = Field_numeric_checkbox_img ( 'AsAnSambaAccount', $user->AsAnSambaAccount, "{enable_disable}" );
	}
	
	$dn = $user->dn;
	if (strlen ( $dn ) > 70) {$dn = texttooltip ( substr ( $dn, 0, 67 ) . "...", $dn, null, null, 0, null );}
	
	$html = "
	<form name='userLdapform'>
	<input type='hidden' name='SambaUid' value='$userid'>
		<table style='width:99%' class=table_form>
			<tr>
				<td align='right'nowrap class=legend>dn:</strong>
				<td>$dn</td>
			</tr>
			<tr>
				<td align='right' class=legend nowrap>{SAMBA_GROUP_PRIVILEGES}:</strong>
				<td><span style='font-size:12px;font-weight:bold'>$samba_group_name&nbsp;</span></td>
			</tr>
			<tr>
				<td align='right' class=legend nowrap>SID:</strong>
				<td>$user->sambaPrimaryGroupSID&nbsp;</td>
			</tr>
			<tr>
				<td align='right' class=legend nowrap>{gidNumber}:</strong>
				<td>" . @implode ( ", ", $user->gidNumber_array ) . "</td>
			</tr>
			<tr>
				<td align='right' class=legend nowrap>User SID:</strong>
				<td>$user->sambaSID&nbsp;</td>
			</tr>						
		</table>
		</form>
		<div id='sambdirs'>
<table style='width:99%' class=table_form>
			<tr>
				<td align='right' nowrap class=legend>{SambaAdminServerDefined}:</strong>
				<td>" . Field_text ( "SambaAdminServerDefined", $user->SambaAdminServerDefined, "width:120px" ) . "</td>
			</tr>
		$roaming_path

			<tr>
				<td align='right' nowrap class=legend>{sambaHomeDrive}:</strong>
				<td><code>$user->sambaHomeDrive</code></td>
			</tr>

			<tr>
				<td align='right' class=legend nowrap>{sambaHomePath}:</strong>
				<td><code>$user->sambaHomePath</code></td>
			</tr>
			
		</table>	
		<div style='width:100%;text-align:right'><hr>
			" . button ( "{buildSambaSettings}", "RebuildSambaFields('$userid')" ) . "
			
		</div>	
	</div>


		
	
	";
	$tpl = new templates ( );
	return div_grey ( $tpl->_ENGINE_parse_body ( $html ) );

}

function USER_SAMBA_EDIT() {
	$user = new user ( $userid );
	writelogs ( $userid, __FUNCTION__, __FILE__, __LINE__ );
	$user = new user ($_GET["SambaUid"]);
	$tpl = new templates ( );
	$page = CurrentPageName ();
	$priv = new usersMenus ( );
	if ($priv->AllowAddUsers == false) {
		echo $tpl->_ENGINE_parse_body ( '{error}' );
	}
	if ($_GET["AsAnSambaAccount"] == 0) {
		return null;
	
	}
	
	$user->sambaPrimaryGroupGID = $_GET["sambaPrimaryGroupGID"];
	$user->Samba_edit_user ();

}

function USER_SAMBA_REBUILD_NULL() {
	$user = new user ($_GET["uid"]);
	$sock=new sockets();
	$sock->SET_INFO("SambaAdminServerDefined",$_GET["SambaAdminServerDefined"]);
	$user->SambaAdminServerDefined = $_GET["SambaAdminServerDefined"];
	$user->Samba_edit_user ();
}

function USER_SENDER_PARAM($userid) {
	$us = new user ( $userid );
	$page = CurrentPageName ();
	writelogs ( "USER_ACCOUNT::$userid", __FUNCTION__, __FILE__, __LINE__ );
	
	$ldap = new clladp ( );
	$userarr = $ldap->UserDatas ( $userid );
	$hash = $ldap->ReadDNInfos ( $userarr ["dn"]);
	$hash ["ou"] = $userarr ["ou"];
	$ou = $hash ["ou"];
	
	if (preg_match ( '#(.+?)@(.+)#', $hash ["mail"], $reg )) {
		$domain = $reg [2];
		$email = $reg [1];
	}
	
	$priv = new usersMenus ( );
	$button = button ( "{submit}", "ParseForm('userLdapform2','$page',true);" );
	$buttonSenderCanonical = button ( "{sender_canonical} {advanced_options}", "Loadjs('domains.edit.user.sender.php?uid=$userid')" );
	if ($priv->AllowAddUsers == false) {
		$button = null;
		$delete = null;
		$buttonSenderCanonical = null;
	}
	
	$styleTDLeft = "style='padding:5px;font-size:11px'";
	
	$main = new main_cf ( );
	
	if ($main->main_array ["smtp_sender_dependent_authentication"] == "yes") {
		$sasl = new smtp_sasl_password_maps ( );
		preg_match ( '#(.+?):(.+)#', $sasl->smtp_sasl_password_hash [$hash ["sendercanonical"]], $ath );
		
		$sasl = "
		<tr>
			<td colspan=2 style='font-size:12px;padding:4px;font-weight:bold;border-bottom:1px solid #CCCCCC'>{smtp_sender_dependent_authentication}</td>
		</tr>
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{username}:</strong>
			<td $styleTDLeft>" . Field_text ( 'smtp_sender_dependent_authentication_username', $ath [1] ) . "</td>
		</tr>
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{password}:</strong>
			<td $styleTDLeft>" . Field_password ( 'smtp_sender_dependent_authentication_password', $ath [2] ) . "</td>
		</tr>		
		";
	
	}
	
	$enable_internet = "
		<form name='userLdapform3'>
				<input type='hidden' name='ou' value='$ou'>
				<input type='hidden' name='SaveAllowedSMTP' value='yes'>
				<input type='hidden' name='dn' value='{$hash["dn"]}'>
				<input type='hidden' name='mail' value='$email'>
				<input type='hidden' name='user_domain' value='$domain'>
				<input type='hidden' name='uid' value='$userid'>	
		<table style='width:100%'>	
		<tr>
			<td colspan=2 style='font-size:12px;padding:4px;font-weight:bold;border-bottom:1px solid #CCCCCC'>{AllowedSMTPTroughtInternet}<p class=caption>{AllowedSMTPTroughtInternet_text}</p></td>
		</tr>				
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{AllowedSMTPTroughtInternet}:</strong>
			<td $styleTDLeft>" . Field_numeric_checkbox_img ( 'AllowedSMTPTroughtInternet', $us->AllowedSMTPTroughtInternet, '{AllowedSMTPTroughtInternet_text}' ) . "</td>
		</tr>
		<tr>
		<td colspan=2 align='right'>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform3','$page',true);\">
		</td>
		</tr>
		</table>
		</form>
		
		";
	
	if ($priv->AllowAddUsers == false) {
		$enable_internet = null;
	}
	
	$html = "
		
		<form name='userLdapform2'>
				<input type='hidden' name='ou' value='$ou'>
				<input type='hidden' name='SaveLdapUser' value='yes'>
				<input type='hidden' name='dn' value='{$hash["dn"]}'>
				<input type='hidden' name='mail' value='$email'>
				<input type='hidden' name='user_domain' value='$domain'>
				<input type='hidden' name='uid' value='$userid'>
		<table style='width:100%'>
		<tr>
			<td colspan=2 style='font-size:12px;padding:4px;font-weight:bold;border-bottom:1px solid #CCCCCC'>{sender_canonical}</td>
		</tr>		
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>" . Field_text ( 'SaveSenderCanonical', $hash ["sendercanonical"], 'width:70%' ) . "</strong>
			<td $styleTDLeft>" . imgtootltip ( 'ed_delete.gif', '{delete}', "DeleteSenderCanonical('{$_GET["userid"]}');" ) . "</td>
			
		</tr>
		<tr>
		<td colspan=2 align='right'>$buttonSenderCanonical</td>	
		</tr>
		$sasl
		<tr>
		<td colspan=2 align=right>$button</td>
		</tr>		
		</table>
		</form>
		<br>
		$enable_internet
		
		
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_CHANGE_PASSWORD_SAVE() {
	$priv = new usersMenus ( );
	$allowed=false;
	if($priv->AllowChangeUserPassword){$allowed=true;}
	if($priv->AllowAddUsers){$allowed=true;}
	if(!$priv->AllowAddUsers){if($_REQUEST["uid"]<>$_SESSION["uid"]){$allowed=false;}}
	
	
	
	if (!$allowed) {
		$tpl = new templates ( );
		echo $tpl->javascript_parse_text('{ERROR_NO_PRIVS}' );
		die ();
	}
	
	unset($_SESSION["privileges"][$_REQUEST["uid"]]);
	writelogs("privileges: {$_REQUEST["uid"]} password=".strlen($_REQUEST["ChangeUserPasswordSave"])." length",__FUNCTION__,__FILE__,__LINE__);
	$password = $_REQUEST["ChangeUserPasswordSave"];
	$GLOBALS["DEBUG_PRIVS"]=true;
	
	$userpriv=new privileges($_REQUEST["uid"]);
	
	if($userpriv->privs["AsComplexPassword"]=="yes"){
		if(!$userpriv->PolicyPassword($password)){return;}
		
	}
	
	$uid = $_REQUEST["uid"];
	$ct = new user( $uid );
	if($ct->SavePasswordUser($password)){
		$sql="SELECT ID FROM iscsi_params WHERE uid='$uid'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
		if($ligne["ID"]>0){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?restart-iscsi=yes");
		}	
	}else{
	 echo $ct->error;
	}

}

function USER_ENDOFLIFE() {
	$priv = new usersMenus ( );
	$ct = new user ($_GET["uid"]);
	
	$form = Field_hidden ( 'USER_SYSTEM_INFOS_UID', $ct->uid ) . "
	<div id='ChangeUserPasswordID'>
	<table style='width:100%'>
	<tr><td colspan=3 align='right'><i style='font-size:16px;font-weight:bold;padding-bottom:4px'>$ct->DisplayName</i></td></tr>
	<tr><td colspan=3><hr></td></tr>
	<tr>	
	<tr>
		<td class=legend nowrap>{FinalDateToLive}:</td>
		<td align=left width=1%>" . Field_text ( 'FinalDateToLive', $ct->FinalDateToLive, 'width:90px' ) . "</td>
		<td align=left><code>YYYY-MM-DD</code></td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	<tr>
		<td colspan=3 align='right'><input type='button' OnClick=\"javascript:UserSystemInfosSave();\" value='{edit}&nbsp;&raquo;'>
	</tr>
	</table>
	</div>
	";
	
	$form = RoundedLightWhite ( $form );
	if (! $priv->AllowAddUsers) {
		$form = "<H3>{ERROR_NO_PRIVS}</H3>";
	}
	
	$html = "<H1>{FinalDateToLive}</H1><p class=caption>{FinalDateToLive_text}</p>$form";
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function USER_CHANGE_EMAIL() {
	$priv = new usersMenus ( );
	$ct = new user ($_GET["uid"]);
	$ldap = new clladp ( );
	$domains = $ldap->hash_get_domains_ou ( $ct->ou );
	if (is_array ( $domains )) {
		while ( list ( $num, $ligne ) = each ( $domains ) ) {
			$fDomains [$ligne] = $ligne;
		}
	}
	
	if (preg_match ( '#(.+?)@(.+)#', $ct->mail, $re )) {
		$domain = $re [2];
		$email = $re [1];
	}
	
	if (! $priv->cyrus_imapd_installed) {
		$local_mailbox = Field_hidden ( 'MailboxActive', 'FALSE' );
	} else {
		$local_mailbox = "<tr>
			<td class=legend nowrap>{MailboxActive}:</td>
			<td>" . Field_TRUEFALSE_checkbox_img ( 'MailboxActive', $ct->MailboxActive ) . "</td>	
			<td width=1%>&nbsp;</td>
			<td width=99%>&nbsp;</td>
			</tr>";
	}
	
	$user_domain = Field_array_Hash ( $domains, 'UserChangeEmailDomain', $domain );
	$form = Field_hidden ( 'UserChangeEmailAddrUID', $ct->uid ) . "
	<div id='ChangeUserPasswordID'>
	<table style='width:100%'>
	<tr><td colspan=5 align='right'><i style='font-size:16px;font-weight:bold;padding-bottom:4px'>$ct->DisplayName</i></td></tr>
	<tr><td colspan=5><hr></td></tr>
	<tr>
		<td class=legend nowrap>{email}:</td>
		<td>" . Field_text ( 'email', $email, "width:120px" ) . "</td>	
		<td width=1%><strong style='font-size:13px;font-weight:normal'>@</strong></td>
		<td width=99%>$user_domain</td>
	</tr>
	$local_mailbox
	<tr><td colspan=5><hr></td></tr>
	<tr>
		<td colspan=5 align='right'><input type='button' OnClick=\"javascript:UserChangeEmailAddrSave();\" value='{edit}&nbsp;&raquo;'>
	</tr>
	</table>
	</div>
	";
	
	$form = RoundedLightWhite ( $form );
	if (! $priv->AllowAddUsers) {
		$form = "<H3>{ERROR_NO_PRIVS}</H3>";
	}
	
	$html = "<H1>{email}</H1>$form";
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_CHANGE_EMAIL_SAVE() {
	$priv = new usersMenus ( );
	
	$priv = new usersMenus ( );
	if (! $priv->AllowAddUsers) {
		$tpl = new templates ( );
		$tpl->_ENGINE_parse_body ( '{ERROR_NO_PRIVS}' );
		die ();
	}
	
	$ct = new user ($_GET["uid"]);
	if (! preg_match ( '#(.+?)@(.+)#', $_GET["UserChangeEmailAddrSave"], $re )) {
		$tpl = new templates ( );
		echo $tpl->_ENGINE_parse_body ( '{error_email_invalid}' );
		die ();
	}
	
	$ct->mail = $_GET["UserChangeEmailAddrSave"];
	$ct->MailboxActive = strtoupper ($_GET["MailboxActive"]);
	$ct->edit_mailbox();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");

}

function USER_CHANGE_PASSWORD() {
	$priv = new usersMenus();
	$ct = new user($_GET["uid"]);
	writelogs("$ct->uid password=".strlen($ct->password)." length",__FUNCTION__,__FILE__);
	$form = Field_hidden ( 'UserPasswordID', $ct->uid ) . "
	<div id='ChangeUserPasswordID'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:16px;'>{password}:</td>
		<td>" . Field_password ("UserPassword", $ct->password, "font-size:16px;padding:5px;width:110px" ) . "</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{apply}","ChangeUserPasswordSave()")."
	</tr>
	</table>
	</div>
	";
	
	
	if (! $priv->AllowChangeUserPassword && ! $priv->AllowAddUsers) {$form = "<H3>{ERROR_NO_PRIVS}</H3>";}
	
	$html = "$form";
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_NOTEXISTS($uid) {
	$page = CurrentPageName ();
	$clean = Paragraphe ( "clean-user-64.png", '{CLEAN_USER_DATAS}', '{CLEAN_USER_EXPLAIN}', "javascript:Loadjs('$page?USER_CLEAN_JS=$uid')" );
	
	$html = "<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/user-warn.png'>
		</td>
		<td valign='top'>" . RoundedLightWhite ( "
			<H2 style='color:red'>{USER_DOES_NOT_EXISTS}</H2>
			<p style='font-size:12px;font-weight:bold'>{USER_DOES_NOT_EXISTS_EXPLAIN}</p>
			<p style='font-size:12px;font-weight:bold'>{CLEAN_USER_EXPLAIN}</p>
			$clean
			
			" ) . "
		</td>
	</tr>
	</table>	
	";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_MESSAGING($userid) {
	$as_connected_user = false;
	$priv = new usersMenus ( );
	$usermenus = new usersMenus ( );
	$page = CurrentPageName ();
	
	if ($userid == null) {
		writelogs ( "USER_ACCOUNT::Userid is null !! fatal error!!", __FUNCTION__, __FILE__, __LINE__ );
		return false;
	}
	$us = new user ( $userid );
	if ($us->DoesNotExists) {
		return USER_NOTEXISTS ( $userid );
	}
	
	if ($_GET["userid"] == $_SESSION ["uid"]) {
		$as_connected_user = true;
	}
	
	$test_mail = Paragraphe ( "test-mail.png", "{send_a_test_mail}", "{send_a_test_mail_text}", "javascript:Loadjs('postfix.sendtest.mail.php?rcpt=$us->mail')" );
	$sender_settings = Paragraphe ( "64-export.png", '{sender_parameters}', '{sender_parameters_text}', "javascript:Loadjs('domains.edit.user.sender.php?uid=$userid')", "$userid:{sender_parameters}" );
	$button_recipient_features = Paragraphe ( "64-import.png", '{inbound_parameters}', '{recipient_translations}', "javascript:Loadjs('domains.edit.user.inbound.php?userid=$userid');" );	
	$AmavisSettings = Paragraphe ( "64-spam.png", 'anti-spam', '{amavis_as_settings_text}', "javascript:Loadjs('users.amavis.php?userid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	
	$AmavisSettings_disabled = Paragraphe ( "64-spam-grey.png", 'anti-spam', '{amavis_as_settings_text}', "", null, 210, 'font-size:12px;font-weight:bold' );
	$AmavisSettings_no_privs= Paragraphe ( "64-spam-grey.png", 'anti-spam', '{ERROR_NO_PRIVS}', "", null, 210, 'font-size:12px;font-weight:bold' );
	$AmavisSettings_not_installed= Paragraphe ( "64-spam-grey.png", 'anti-spam', '{feature_not_installed}', "", null, 210, 'font-size:12px;font-weight:bold' );
	
	
	
	
	$sender_settings_disabled = Paragraphe ( "64-export-grey.png", '{sender_parameters}', '{sender_parameters_text}', "", "$userid:{sender_parameters}" );
	$button_recipient_features_disabled = Paragraphe ( "64-import-grey.png", '{inbound_parameters}', '{recipient_translations}', "" );
	
	
	if ($priv->POSTFIX_INSTALLED) {
		if (($priv->fetchmail_installed) or ($priv->fdm_installed)) {
			if ($priv->AllowFetchMails) {
				$fetchmail = Paragraphe ( "fetchmail-rule-64.png", '{APP_FETCHMAIL}', '{fetchmail_user_text}', "javascript:Loadjs('wizard.fetchmail.newbee.php?script=yes&uid={$_GET["userid"]}')", null, 210, 'font-size:12px;font-weight:bold' );
			}
		}
		
		$quarantine_report=Paragraphe("64-administrative-tools.png","{quarantine_reports}","{quarantine_reports_text}","javascript:Loadjs('domains.edit.user.quarantine.report.php?uid={$_GET["userid"]}')",null,210,100,0,true);
		
		if ($priv->cyrus_imapd_installed) {
			if (! $priv->ZARAFA_INSTALLED) {
				if ($priv->spamassassin_installed) {
					$antispam_leraning = Paragraphe ( "anti-spam-learning.png", '{EnableUserSpamLearning}', '{EnableUserSpamLearning_text}', "javascript:Loadjs('domains.edit.user.sa.learn.php?uid={$_GET["userid"]}');", null, 210, 'font-size:12px;font-weight:bold' );
				}
			}
		}
	
	}
	
	$listdistri = Paragraphe ( "64-bg_addresses.png", '{mailing_list}', '{user_to_mailing_list}', "javascript:Loadjs('domains.edit.user.mailling-list.php?uid=$us->uid');", null, 210, 'font-size:12px;font-weight:bold' );
	$listdistri_disabled = Paragraphe ( "64-bg_addresses-grey.png", '{mailing_list}', '{user_to_mailing_list}', null, null, 210, 'font-size:12px;font-weight:bold' );
	
	$changeemail = Paragraphe ( "64-sendmail.png", '{change_email}', '{change_email_text}', "javascript:YahooWin5('500','domains.edit.user.php?UserChangeEmailAddr=yes&uid=$us->uid','{change_email}');", null, 210, 'font-size:12px;font-weight:bold' );
	$changeemail_disabled = Paragraphe ( "64-sendmail-grey.png", '{change_email}', '{change_email_text}', "", null, 210, 'font-size:12px;font-weight:bold' );
	
	
	if ($as_connected_user) {
		$delete = null;
		if (! $priv->AllowSenderCanonical) {
			$sender_settings = $sender_settings_disabled;
			$recipients_translations = null;
		}
		if (! $priv->AllowChangeUserPassword && ! $priv->AllowAddUsers) {
			$password = null;
			$button = null;
		}
		if (! $priv->AllowAddUsers) {
			$changeemail = $changeemail_disabled;
			$listdistri = $listdistri_disabled;
		}
		

	}

	
	if (!$usermenus->AllowChangeAntiSpamSettings) {$AmavisSettings = $AmavisSettings_no_privs;}
	if (!$usermenus->AMAVIS_INSTALLED) {$AmavisSettings = $AmavisSettings_not_installed;}
	if ($usermenus->imapsync_installed) {$imapsync = Paragraphe ( "sync-64.png", "{import_mailbox}", "{export_mailbox_text}", "javascript:Loadjs('mailsync.php?uid=$us->uid')" );}
	
	if ($usermenus->cyrus_imapd_installed) {
		if (! $usermenus->ZARAFA_INSTALLED) {
			$restore_mailbox = Paragraphe ( "database-restore-64.png", "{restore_mailbox}", "{restore_mailbox_text}", "javascript:Loadjs('user.restore.mailbox.php?uid=$us->uid')" );
		}
	}
	
	$tpl = new templates ( );
	writelogs ( "done", __FUNCTION__, __FILE__, __LINE__ );
	
	if($usermenus->EnableManageUsersTroughActiveDirectory){
		$changeemail=$changeemail_disabled;
		$listdistri=$listdistri_disabled;
		$AmavisSettings = $AmavisSettings_disabled;
		$sender_settings = $sender_settings_disabled;
		$button_recipient_features=$button_recipient_features_disabled;
	}
	
	$tr [] = $changeemail;
	$tr [] = $button_recipient_features;
	$tr [] = $sender_settings;
	$tr [] = $fetchmail;
	$tr [] = $imapsync;
	$tr [] = $restore_mailbox;
	$tr [] = $test_mail;
	$tr [] = $listdistri;
	$tr	[] = $quarantine_report;
	$tr [] = $antispam_leraning;
	$tr [] = $AmavisSettings;
	
	$tables [] = "<table style='width:100%'><tr>";
	$t = 0;
	while ( list ( $key, $line ) = each ( $tr ) ) {
		$line = trim ( $line );
		if ($line == null) {
			continue;
		}
		$t = $t + 1;
		$tables [] = "<td valign='top'>$line</td>";
		if ($t == 3) {
			$t = 0;
			$tables [] = "</tr><tr>";
		}
	
	}
	if ($t < 3) {
		for($i = 0; $i <= $t; $i ++) {
			$tables [] = "<td valign='top'>&nbsp;</td>";
		}
	}
	
	$tables [] = "</table>";
	return $tpl->_ENGINE_parse_body ( implode ( "\n", $tables ) );

}

function USER_ACCOUNT($userid){
	$page=CurrentPageName();
	$md=md5($userid);
	$html="
	
	<div id='account_$md'></div>
	<script>
		LoadAjax('account_$md','$page?userid=$userid&ajaxmode=yes&section=account-popup&dn={$_GET["dn"]}');
	</script>";
	
	return $html;
	
}

function USER_ACCOUNT_POPUP($userid) {
	$tpl = new templates ( );
	if ($userid == null) {writelogs ( "USER_ACCOUNT::Userid is null !! fatal error!!", __FUNCTION__, __FILE__, __LINE__ );return false;}
	
	if(strlen($_GET["dn"])>0){$userdn=base64_decode($_GET["dn"]);}
	
	$us = new user ($userid,$userdn);
	if ($us->DoesNotExists) {return USER_NOTEXISTS ( $userid );}
	$as_connected_user = false;
	if ($_GET["userid"] == $_SESSION ["uid"]) {$as_connected_user = true;}
	include_once (dirname ( __FILE__ ) . '/ressources/class.obm.inc');
	writelogs ( "USER_ACCOUNT::{$_GET["userid"]}/$userid", __FUNCTION__, __FILE__, __LINE__ );
	$ldap = new clladp ( );
	
	$usermenus = new usersMenus ( );
	$page = CurrentPageName ();
	$styleTDRight = "style='padding:5px;font-size:11px'";
	$styleTDLeft = "style='padding:5px;font-size:11px'";
	$cellRol = CellRollOver ();
	
	writelogs ( "USER_ACCOUNT::$us->uid checking OBM", __FUNCTION__, __FILE__, __LINE__ );
	$obm = new obm_export_single ( $us->uid );
	if ($obm->CheckOBM ()) {
		if ($obm->IsUserExists ( $us->uidNumber )) {
			$obm_info = "<p style='background-color:#FFFFFF;padding:3px;border:1px solid #CCCCCC;font-size:11px'>{user_is_an_obm_user}</p>";
		}
	}
	
	if ($usermenus->cyrus_imapd_installed == true) {
		$button_mailboxes = "<input type='button' value='{mailbox settings}&nbsp;&raquo;' OnClick=\"javascript:TreeUserMailBoxForm('$userid');\" style='margin-right:20px'>";
	}
	
	$priv = new usersMenus ( );
	$button = "<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform','$page',true,false,false,'userform',
	'domains.edit.user.php?userid=$us->uid&ajaxmode=yes&section=account');\">";
	
	if ($usermenus->SIMPLE_GROUPEWARE_INSTALLED) {
		include_once ("ressources/class.mysql.inc");
		$sql = new mysql ( );
		if ($sql->SIMPLE_GROUPWARE_ENABLED ( $userid )) {
			$SIMPLE_GROUPWARE_ENABLED = 1;
			$SIMPLE_GROUPWARE_TXT = "yes";
		} else {
			$SIMPLE_GROUPWARE_ENABLED = 0;
			$SIMPLE_GROUPWARE_TXT = "No";
		}
		
		$simple_groupware_activation_admin = "
				<tr $cellRol>
					<td align='right' nowrap class=legend $styleTDRight>{SimpleGroupWareActive}:</strong>
					<td $styleTDLeft>" . Field_numeric_checkbox_img ( 'SimpleGroupWareActive', $SIMPLE_GROUPWARE_ENABLED ) . "</td>
				</tr>	";
		
		$simple_groupware_activation_user = "<tr $cellRol>
					<td align='right' nowrap class=legend $styleTDRight>{SimpleGroupWareActive}:</strong>
					<td $styleTDLeft><strong>$SIMPLE_GROUPWARE_TXT</strong>
					<input type='hidden' name='SimpleGroupWareActive' id='SimpleGroupWareActive' value='$SIMPLE_GROUPWARE_ENABLED'></td>
				</tr>	";
	
	}
	
	if ($us->DisplayName == null) {
		$us->DisplayName = "unknown";
	}
	
	if (($usermenus->PUREFTP_INSTALLED) or ($usermenus->SAMBA_INSTALLED)) {

		$HomeBinding = Paragraphe ( "64-hand-user.png", '{HomeBinding}', '{HomeBinding_text}', "javascript:Loadjs('home.binding.php?userid=$userid')" );
	}
	
	$HomeBinding_grey=Paragraphe ( "64-hand-user-grey.png", '{HomeBinding}', '{HomeBinding_text}');
	
	$EndOfLife = "
	<td width=1%><img src='img/folder-tasks-32.png'></td>
	<td style='padding:5px' $cellRol>" . texttooltip ( '{FinalDateToLive}', '{FinalDateToLive_text}', "UserEndOfLIfe('$userid')", null, 0, 'font-size:12px;font-weight:bold' ) . "</td>";
	$EndOfLife = Paragraphe ( "time-64.png", '{FinalDateToLive}', '{FinalDateToLive_text}', "javascript:UserEndOfLIfe('$userid')" );
	
	$SystemInfoUser = "
	<td width=1%><img src='img/system-32.png'></td>
	<td style='padding:5px' $cellRol>" . texttooltip ( '{UserSystemInfos}', '{UserSystemInfos_text}', "UserSystemInfos('$userid')", null, 0, 'font-size:12px;font-weight:bold' ) . "</td>";
	$SystemInfoUser = Paragraphe ( "system-64.org.png", '{UserSystemInfos}', '{UserSystemInfos_text}', "javascript:Loadjs('domains.edit.user.system.php?uid=$userid')" );
	$SystemInfoUser_disabled=Paragraphe ( "system-64.org-grey.png", '{UserSystemInfos}', '{UserSystemInfos_text}');
	
	
	$PRIVILEGES=Paragraphe('members-priv-64.png','{privileges}','{privileges_text}',
	"javascript:YahooWin(650,'domains.edit.group.php?GroupPriv=-2&userid=$userid&start=yes')");
	
	$PRIVILEGES_DISABLED=Paragraphe('members-priv-64-grey.png','{privileges}','{privileges_text}',"");	
	
	
	
	$ChangeGuid = "
	<td width=1%><img src='img/change-identifiant-32.png'></td>
	<td style='padding:5px' $cellRol>" . texttooltip ( '{change_uid}', '{change_uid_text}', "YahooWin3(450,'domains.edit.user.php?changeuid=yes&userid=$userid','$userid:{change_uid}');", null, 0, 'font-size:12px;font-weight:bold' ) . "</td>";
	$ChangeGuid = Paragraphe ( "logon-profiles-64.png", '{change_uid}', '{change_uid_text}', "javascript:YahooWin3(450,'domains.edit.user.php?changeuid=yes&userid=$userid','$userid:{change_uid}');" );
	$ChangeGuid_disabled = Paragraphe ( "logon-profiles-64-grey.png", '{change_uid}', '{change_uid_text}');
	
	
	
	$joomla = "
	<td width=1%><img src='img/32-joomla.png'></td>
	<td style='padding:5px' $cellRol>" . texttooltip ( '{joomla_privileges}', '{joomla_privileges_text}', "Loadjs('users.joomla.php?userid=$userid');", null, 210, 'font-size:12px;font-weight:bold' ) . "</td>";
	
	$joomla = Paragraphe ( "64.joomla.png", '{joomla_privileges}', '{joomla_privileges_text}', "javascript:Loadjs('users.joomla.php?userid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	
	$usersinterface = Paragraphe ( "folder-interface-64.jpg", '{user_interface}', '{user_interface_text}', "Loadjs('users.tabs.php?uid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	
	$button_backup = "
	<td width=1%><img src='img/32-backup.png'></td>
	<td style='padding:5px' $cellRol>" . texttooltip ( '{backup_parameters}', '{backup_parameters_text}', "Loadjs('domains.edit.user.backup.php?uid=$userid');", null, 210, 'font-size:12px;font-weight:bold' ) . "</td>";
	
	$button_backup = Paragraphe( "64-backup.png", '{backup_parameters}',
	 '{backup_parameters_text}',
	 "javascript:Loadjs('domains.edit.user.backup.php?uid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	
	
	$button_webdav= Paragraphe( "webdav-64.png", '{USER_WEBDAV}',
	 '{USER_WEBDAV_TEXT}',
	 "javascript:Loadjs('domains.edit.user.webdav.php?uid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	$button_webdav_disabled= Paragraphe( "webdav-64-grey.png", '{USER_WEBDAV}','{USER_WEBDAV_TEXT}');
	
	

 	$emule=Paragraphe( "64-emule.png", '{MLDONKEY_USER}',
	 '{MLDONKEY_USER_TEXT}',
	 "javascript:Loadjs('domains.edit.user.mldonkey.php?uid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	
	$delete = BuildParagraphe ( "delete_this_user", "delete_this_user_text", "Loadjs('domains.delete.user.php?uid=$userid');", "32-cancel.png", true );
	$password = BuildParagraphe ( "change_password", "change_password_text", "ChangeUserPassword('$userid');", "32-key.png", true );
	$delete = Paragraphe ( "delete-64.png", '{delete}', '{delete_this_user}', "javascript:Loadjs('domains.delete.user.php?uid=$userid');", null, 210, 'font-size:12px;font-weight:bold' );
	$delete_disabled=Paragraphe ( "delete-64-grey.png", '{delete}', '{delete_this_user}');
	
	
	if(strlen($_GET["dn"])>0){$deletedn = Paragraphe ( "delete-64.png", '{delete_this_user_dn}', '{delete_this_user_dn_text}', "javascript:Loadjs('domains.delete.userdn.php?uid=$userid&dn={$_GET["dn"]}');", null, 210, 'font-size:12px;font-weight:bold' );}
	$deletedn_disabled=Paragraphe ( "delete-64-grey.png", '{delete_this_user_dn}', '{delete_this_user_dn_text}');
	
	$password = Paragraphe ( "64-ssl-key.png", '{change_password}', '{change_password_text}',
	 "javascript:YahooWin5('400','domains.edit.user.php?ChangeUserPassword=yes&uid=$userid','$userid::{change_password}');", null, 210, 'font-size:12px;font-weight:bold' );
	$password_disabled= Paragraphe ( "64-ssl-key-grey.png", '{change_password}', '{change_password_text}');
	
	
	
	$address = Paragraphe ( "64-addressbook.png", '{address}', '{address_user_text}', "javascript:Loadjs('contact.php?uidUser=$userid')", null, 210, 'font-size:12px;font-weight:bold' );
	$address_disabled = Paragraphe ( "64-addressbook-grey.png", '{address}', '{address_user_text}');
	
	
	if ($us->jpegPhotoError != null) {
		$imcontact = "contact-unknown-user-64.png";
		$text = "{error_image_missing}<br>$us->jpegPhotoError";
	} else {
		$imcontact = $us->img_identity;
		$imcontact = str_replace ( "img/", "", $imcontact );
	}
	
	$picture = $picture;
	$mots = strlen ( $us->mail );
	$size_text = 14;
	if ($mots > 42) {
		$size_text = 12;
	}
	$email_address_hidden = "<strong style='font-size:{$size_text}px;font-family:Arial, Helvetica, sans-serif. '>$us->mail</strong>";
	$email_address = "<span style='font-size:12px;font-family:Arial, Helvetica, sans-serif'>$us->mail</span>";
	
	
	

	$changeuid = Paragraphe ( "mysql-user-settings.png", '{change_uid}', '{change_uid_text}', "javascript:YahooWin3(450,'domains.edit.user.php?changeuid=yes&userid=$userid','$userid:{change_uid}');", null, 210, 'font-size:12px;font-weight:bold' );
	$changeuid_disabled = Paragraphe ( "mysql-user-settings-grey.png", '{change_uid}', '{change_uid_text}');
	
	
	
	if (!$usermenus->POSTFIX_INSTALLED) {
		$recipients_translations = null;
		$domainName = null;
		$ChangeGuid = null;
		$email_address = Field_hidden ( 'mail', "$userid@localhost" ) . Field_hidden ( 'SenderCanonical', "$us->SenderCanonical" );
		$AmavisSettings = null;
		$changeemail = null;
		$button_recipient_features = null;
		$sender_settings = $EndOfLife;
		$EndOfLife = null;
		$button_recipient_features = $button_backup;
		$button_backup = null;
	}
	
	if (! $usermenus->JOOMLA_INSTALLED) {$joomla = null;}
	
	if ($as_connected_user) {
		$emule=null;
		$delete = null;
		$button_webdav=null;
		$deletedn=null;
		$PRIVILEGES=$PRIVILEGES_DISABLED;
		if (! $priv->AllowSenderCanonical) {
			$sender_settings = null;
			$recipients_translations = null;
		}
		if (! $priv->AllowChangeUserPassword && ! $priv->AllowAddUsers) {
			$password = null;
			$button = null;
		}
		if (!$priv->AllowAddUsers) {
			$SystemInfoUser = $SystemInfoUser_disabled;
			$delete = $delete_disabled;
			$ChangeGuid = $changeuid_disabled;
			$joomla = null;
			$button_backup = null;
			$changeemail = null;
			$changeuid = $changeuid_disabled;
			$emule=null;
			$button_webdav=$button_webdav_disabled;
			$deletedn=$deletedn_disabled;
		}
		
		$loginShell = $loginShell_hidden;
		$domainName = $domainName_hidden;
		$EndOfLife = $EndOfLife_hidden;
		$simple_groupware_activation_admin = $simple_groupware_activation_user;
		$HomeBinding = null;
	
	}
	
	
	if(!$usermenus->MLDONKEY_INSTALLED){$emule=null;}else{
		$sock=new sockets();
		$EnableMLDonKey=trim($sock->GET_INFO("EnableMLDonKey"));
		if($EnableMLDonKey==null){$EnableMLDonKey=1;}
		if($EnableMLDonKey==0){$emule=null;}
	}
	
	if(!$priv->APACHE_MODE_WEBDAV){$button_webdav=null;}
	
	
	$moveorguser_grey=Paragraphe ( "user-move-64-grey.png", '{change_organization}', '{change_user_organization_text}',"");
	
	if($priv->AsSystemAdministrator){
		$moveorguser=Paragraphe ( "user-move-64.png", '{change_organization}', '{change_user_organization_text}',"javascript:Loadjs('domains.edit.user.moveorg.php?userid=$userid')");
	}
	
	
	if($usermenus->EnableManageUsersTroughActiveDirectory){
		$SystemInfoUser=$SystemInfoUser_disabled;
		$address=$address_disabled;
		$password=$password_disabled;
		$deletedn=$deletedn_disabled;
		$delete=$delete_disabled;
		$changeuid=$changeuid_disabled;
		$button_webdav=$button_webdav_disabled;
		$HomeBinding=$HomeBinding_grey;
		$moveorguser=$moveorguser_grey;
		$PRIVILEGES=$PRIVILEGES_DISABLED;
	}
	
	$tr[] = $PRIVILEGES;
	$tr[] = $SystemInfoUser;
	$tr[] = $loginShell;
	$tr[] = $domainName;
	$tr[] = $address;
	$tr[] = $HomeBinding;
	$tr[] = $delete;
	$tr[] = $deletedn;
	$tr[] = $password;
	$tr[] = $changeuid;
	$tr[] = $moveorguser;
	$tr[] = $EndOfLife;
	$tr[] = $button_webdav;
	$tr[] = $joomla;
	$tr[] = $simple_groupware_activation_admin;
	$tr[] = $button_backup;
	$tr[] = $emule;
	
	$tables [] = "<table style='width:100%'><tr>";
	$t = 0;
	while ( list ( $key, $line ) = each ( $tr ) ) {
		$line = trim ( $line );
		if (strlen ( $line ) < 10) {
			continue;
		}
		$t = $t + 1;
		$tables [] = "<td valign='top'>$line</td>";
		if ($t == 3) {
			$t = 0;
			$tables [] = "</tr><tr>";
		}
	
	}
	if ($t < 3) {
		for($i = 0; $i <= $t; $i ++) {
			$tables [] = "<td valign='top'>&nbsp;</td>";
		}
	}
	
	$tables [] = "</table>";
	$tables_formatted = $tpl->_ENGINE_parse_body ( implode ( "\n", $tables ) );
	
	$DisplayName = $us->DisplayName;
	if (strlen ( $DisplayName ) > 27) {
		$DisplayName = texttooltip ( substr ( $DisplayName, 0, 24 ), $DisplayName, null, 1 ) . "...";
	}
	
	if (strlen ( $us->jpegPhoto ) > 0) {$array ["img"] = $us->img_identity;} else {$array ["img"] = "img/contact-unknown-user.png";}
	$array["mail"] = $us->mail;
	$array["phone"] = $us->telephoneNumber;
	$array["sn"] = $ligne ["sn"] [0];
	$array["displayname"] = $us->DisplayName;
	$array["givenname"] = $us->givenName;
	$array["JS"] = "javascript:s_PopUp('edit.thumbnail.php?uid=$us->uid',600,300)";
	$array["title"] = $us->title;
	$array["mobile"] = $us->mobile;
	$array["ou"] = $us->ou;
	$array["uidNumber"] = $us->uidNumber;
	$useridentity = finduser_format ( $array );
	
	$html = "
		<input type='hidden' id='delete_this_user' value='{delete_this_user}'>
		<form name='userLdapform'>
		<input type='hidden' name='ou' value='$us->ou'>
		<input type='hidden' name='SaveLdapUser' value='yes'>
		<input type='hidden' name='dn' value='$us->dn'>
		<input type='hidden' name='uid' id='uid' value='{$us->uid}'>
		<table style='width:100%'>
			<tr>
			<td valign='top'>$useridentity</td>
			<td valign='top'><div id='userid-warning'></div></td>
			</tr>
		</table>
		<div style='width:100%;height:450px;overflow:auto'>
			$tables_formatted
		</div>";
	
	$html = "
	<div style='width:100%'>
	$obm_info
	$html
	</div>
	<script>
		LoadAjax('userid-warning','$page?userid-warning=yes&userid=$userid');
	</script>
	";
	
	writelogs ( "done", __FUNCTION__, __FILE__, __LINE__ );
	
	return $tpl->_ENGINE_parse_body ( $html );

}

function div_grey($content) {
	
	return "$content";

}

function USER_MAILBOX_WIZARD_JS() {
	//mail-wizard-128.png
	

	$page = CurrentPageName ();
	$tpl = new templates ( );
	$title = $tpl->_ENGINE_parse_body ( "{create_mailbox}" );
	
	$html = "
		function CreateMailBoxWizardStart(){
			YahooWin('650','$page?create-mailbox-step1=yes&uid={$_GET["uid"]}','$title');
		
		}
		
		function CreateMailBoxWizardStep2(){
			Loadjs('domains.edit.user.create.mbx.php?uid={$_GET["uid"]}');
		}
	
	CreateMailBoxWizardStart();";
	
	echo $html;
}

function USER_MAILBOX_WIZARD_STEP1() {
	
	$html = "
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/mail-wizard-128.png'></td>
		<td valign='top'>
	<div style='font-size:14px'>{USER_MAILBOX_WIZARD_STEP1}</div>
	<div style='font-size:12px'>{mailbox quota}:</div>
	<div>" . Field_text ( "MailBoxMaxSize", 0, "font-size:13px;padding:5px;width:210px" ) . "
	" . Field_hidden ( "mp_l", 1 ) . "
	" . Field_hidden ( "mp_r", 1 ) . "
	" . Field_hidden ( "mp_s", 1 ) . "
	" . Field_hidden ( "mp_w", 1 ) . "
	" . Field_hidden ( "mp_i", 1 ) . "
	" . Field_hidden ( "mp_p", 1 ) . "
	" . Field_hidden ( "mp_c", 1 ) . "
	" . Field_hidden ( "mp_d", 1 ) . "
	" . Field_hidden ( "mp_a", 1 ) . "
	" . Field_hidden ( "MailboxActive", "TRUE" ) . "
	
	<table style='width:100%'>
	<tr>
		<td style='width:50%' align='left'>" . button ( "{cancel}", "YahooWinHide()" ) . "</td>
		<td style='width:50%' align='right'>" . button ( "{create_mailbox}", "CreateMailBoxWizardStep2()" ) . "</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_MAILBOX_NONEXISTENT($uid,$error) {
	$page = CurrentPageName ();
	
	$html = "<center style='width:100%;'>
	
	" . Paragraphe ( "inbox-error-64.png", "{no_mailbox}", "{user_no_mailbox}",
	 "javascript:Loadjs('$page?create-mailbox-wizard=yes&uid=$uid')", "{create_mailbox}" ) . "
	 <div style='margin:10px'>
		 <span style='font-size:16px;color:#940404'>$error</span>
	 </div>
	</center>";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}

function ZARAFA_MAILBOX_EDIT_JS() {
	
	$page = CurrentPageName ();
	$uid = base64_decode ($_GET["zarafa-mailbox-edit"]);
	
	$html = "
var X_SAVE_ZARAFA_MAILBOX= function (obj) {
	var results=obj.responseText;
	if(results.length>3){alert(results);}
	RefreshTab('container-users-tabs');
	}	
	
function SAVE_ZARAFA_MAILBOX(){
	var XHR = new XHRConnection();
	XHR.appendData('zarafaQuotaWarn',document.getElementById('zarafaQuotaWarn').value);
	XHR.appendData('zarafaQuotaSoft',document.getElementById('zarafaQuotaSoft').value);
	XHR.appendData('zarafaQuotaHard',document.getElementById('zarafaQuotaHard').value);
	XHR.appendData('zarafaMbxLang',document.getElementById('zarafaMbxLang').value);
	
	
	
	if(document.getElementById('zarafaAdmin').checked){XHR.appendData('zarafaAdmin','1');}else{XHR.appendData('zarafaAdmin','0');}
	
	XHR.appendData('uid','$uid');
	document.getElementById('zfmbximg').src='img/wait_verybig.gif';
	XHR.sendAndLoad('$page', 'GET',X_SAVE_ZARAFA_MAILBOX);	
}	
SAVE_ZARAFA_MAILBOX();	";
	echo $html;
}

function ZARAFA_MAILBOX_SAVE() {
	$user = new user ($_GET["uid"]);
	$user->zarafaQuotaHard = $_GET["zarafaQuotaHard"];
	$user->zarafaQuotaSoft = $_GET["zarafaQuotaSoft"];
	$user->zarafaQuotaWarn = $_GET["zarafaQuotaWarn"];
	$user->zarafaAdmin = $_GET["zarafaAdmin"];
	$user->SaveZarafaMbxLang($_GET["zarafaMbxLang"]);
	$sock=new sockets();
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?zarafa-admin=yes");
	$sock->getFrameWork("zarafa.php?zarafa-user-create-store=$user->uid&lang={$_GET["zarafaMbxLang"]}");	
	$sock->getFrameWork("zarafa.php?foldersnames=yes&uid=$user->uid&lang={$_GET["zarafaMbxLang"]}");
	
	if (!$user->zarafaSaveInfos()) {echo $user->error;}

}

function ZARAFA_MAILBOX($uid) {
	
	$u = new user ( $uid );
	$page = CurrentPageName();
	$sock = new sockets();
	$status = unserialize(base64_decode ( $sock->getFrameWork("cmd.php?zarafa-user-details=$uid")));
	$languages=unserialize(base64_decode($sock->getFrameWork("zarafa.php?locales=yes")));
	while (list ($index, $data) = each ($languages) ){$langbox[$data]=$data;}
	$langbox[null]="{select}";
	$zarafa_version=$sock->getFrameWork("zarafa.php?getversion=yes");
	preg_match("#^([0-9]+)\.#", $zarafa_version,$re);
	$major_version=$re[1];	
	if(!is_numeric($major_version)){$major_version=6;}
	
	$mailbox_language=Field_array_Hash($langbox,"zarafaMbxLang",$u->zarafaMbxLang,"style:font-size:13px;padding:3px");
	
	$mailboxsize = $status ["Current store size"];
	if (preg_match ( "#([0-9]+)\s+KB#", $mailboxsize, $re )) {$mailboxsize = FormatBytes ( $mailboxsize );}
	
	if (preg_match ( "#([0-9]+)\/([0-9]+)\/([0-9]+)\s+(.+)#", $status ["Last logon"], $re )) {
		$status ["Last logon"] = date ( "D M", mktime ( 0, 0, 0, $re [1], $re [2], "20{$re[3]}" ) ) . " {$re[4]}";
	}
	if (preg_match ( "#([0-9]+)\/([0-9]+)\/([0-9]+)\s+(.+)#", $status ["Last logoff"], $re )) {
		$status ["Last logoff"] = date ( "D M", mktime ( 0, 0, 0, $re [1], $re [2], "20{$re[3]}" ) ) . " {$re[4]}";
	}
	
	$mailboxinfos=Paragraphe32("mailbox_infos","mailbox_zarafa_infos_text","Loadjs('$page?ZARAFA_MAILBOX_INFOS=yes&uid=$uid&userid=$uid')","32-infos.png");
	$ZarafaFeatures=$u->AnalyzeZarafaFeatures();
	
	
	$html = "<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/mailbox-zarafa-128.png' id='zfmbximg'><p>&nbsp;</p>$mailboxinfos</td>
		<td valign='top'><span style='font-size:18px'>$u->DisplayName {mailbox}</span>
				<table style='width:100%'>
					<tr>
						<td class=legend style='font-size:13px'>{mailbox_size}:</td>
						<td style='font-size:13px'><strong>$mailboxsize</strong></td>
					</tr>
					<tr>
						<td class=legend style='font-size:13px'>{last_logon}:</td>
						<td style='font-size:13px'><strong>{$status["Last logon"]}</strong></td>
					</tr>		
					<tr>
						<td class=legend style='font-size:13px'>{last_logoff}:</td>
						<td style='font-size:13px'><strong>{$status["Last logoff"]}</strong></td>
					</tr>
					<tr>
						<td class=legend style='font-size:13px'>{zarafaAdmin}:</td>
						<td style='font-size:13px'>" . Field_checkbox ( "zarafaAdmin", 1, $u->zarafaAdmin ) . "</td>
					</tr>
					<tr>
						<td class=legend style='font-size:13px'>{zarafaSharedStoreOnly}:</td>
						<td style='font-size:13px'>" . Field_checkbox ( "zarafaSharedStoreOnly", 1, $u->zarafaSharedStoreOnly,"zarafaSharedStoreOnlyCheck()" ) . "</td>
					</tr>					
					
					
					<tr>
						<td class=legend style='font-size:13px'>{enable_imap}:</td>
						<td style='font-size:13px'>" . Field_checkbox ( "user_zarafa_enable_imap", 1, $ZarafaFeatures["imap"],"UserZarafaFeatures()" ) . "</td>
					</tr>	
					<tr>
						<td class=legend style='font-size:13px'>{enable_pop3}:</td>
						<td style='font-size:13px'>" . Field_checkbox ( "user_zarafa_enable_pop3", 1, $ZarafaFeatures["pop3"],"UserZarafaFeatures()" ) . "</td>
					</tr>																
					<tr>
						<td class=legend style='font-size:13px'>{zarafaMbxLang}:</td>
						<td style='font-size:13px'>$mailbox_language</td>
					</tr>						
					
					<tr>
						<td colspan=2><span style='font-size:16px'>{zarfa_quota_title}</span>
						<div class='explain'>{zarfa_quota_title_explain}</div></td>
					</tr>
					<tr>
						<td class=legend style='font-size:13px'>{zarafaQuotaWarn}:</td>
						<td style='font-size:13px'>" . Field_text ( "zarafaQuotaWarn", $u->zarafaQuotaWarn, "font-size:13px;padding:3px;width:60px" ) . "&nbsp;MB</strong></td>
					</tr>
				<tr>
						<td class=legend style='font-size:13px'>{zarafaQuotaSoft}:</td>
						<td style='font-size:13px'>" . Field_text ( "zarafaQuotaSoft", $u->zarafaQuotaSoft, "font-size:13px;padding:3px;width:60px" ) . "&nbsp;MB</strong></td>
					</tr>
					<tr>
						<td class=legend style='font-size:13px'>{zarafaQuotaHard}:</td>
						<td style='font-size:13px'>" . Field_text ( "zarafaQuotaHard", $u->zarafaQuotaHard, "font-size:13px;padding:3px;width:60px" ) . "&nbsp;MB</strong></td>
					</tr>
				<tr>
						<td colspan=2 align='right'><hr>
							" . button ( "{apply}", "Loadjs('$page?zarafa-mailbox-edit=" . base64_encode ( $uid ) . "')" ) . "</td>
				</tR>														
																		
				</table>
		</td>
	</tr>
	</table>
	
	<script>
		function CheckFields(){
			var major_version=$major_version;
			document.getElementById('user_zarafa_enable_imap').disabled=true;
			document.getElementById('user_zarafa_enable_pop3').disabled=true;
			
			
			if(document.getElementById('zarafaSharedStoreOnly').checked){
				document.getElementById('zarafaAdmin').disabled=true;
				document.getElementById('zarafaQuotaWarn').disabled=true;
				document.getElementById('zarafaQuotaSoft').disabled=true;
				document.getElementById('zarafaQuotaHard').disabled=true;
				return;
			}else{
				document.getElementById('zarafaAdmin').disabled=false;
				document.getElementById('zarafaQuotaWarn').disabled=false;
				document.getElementById('zarafaQuotaSoft').disabled=false;
				document.getElementById('zarafaQuotaHard').disabled=false;			
			
			}
			
			
			
			
			if(major_version>6){
				document.getElementById('user_zarafa_enable_imap').disabled=false;
				document.getElementById('user_zarafa_enable_pop3').disabled=false;			
			}
		}
		
		var X_UserZarafaFeatures= function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);retutn;}
			CheckFields();
		}			
	
	function UserZarafaFeatures(){
		var XHR = new XHRConnection();
		if(document.getElementById('user_zarafa_enable_imap').checked){XHR.appendData('user_zarafa_enable_imap','1');}else{XHR.appendData('user_zarafa_enable_imap','0');}
		if(document.getElementById('user_zarafa_enable_pop3').checked){XHR.appendData('user_zarafa_enable_pop3','1');}else{XHR.appendData('user_zarafa_enable_pop3','0');}		
		XHR.appendData('uid','$uid');
		XHR.sendAndLoad('$page', 'POST',X_UserZarafaFeatures);		
	
	}
	
	function zarafaSharedStoreOnlyCheck(){
		var XHR = new XHRConnection();
		if(document.getElementById('zarafaSharedStoreOnly').checked){XHR.appendData('zarafaSharedStoreOnly','1');}else{XHR.appendData('zarafaSharedStoreOnly','0');}
		XHR.appendData('uid','$uid');
		XHR.sendAndLoad('$page', 'POST',X_UserZarafaFeatures);	
		
	}
	
	
	CheckFields();
	</script>";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_MAILBOX($uid) {
	$users = new usersMenus ( );
	if ($users->ZARAFA_INSTALLED) {
		return ZARAFA_MAILBOX ( $uid );
	}
	$page = CurrentPageName ();
	$RealMailBox = false;
	
	$page = CurrentPageName ();
	$user = new user ( $uid );
	
	$cyr = new cyrus ( );
	$RealMailBox=$cyr->IfMailBoxExists($uid);
	
	if (! $RealMailBox) {
		
		return USER_MAILBOX_NONEXISTENT ( $uid,nl2br($cyr->cyrus_infos));
		$no_mailbox = "<p class=caption style='color:red'>{user_no_mailbox} !!</p>";
	}
	
	if ($user->MailboxActive == 'TRUE') {
		$cyrus = new cyrus ( );
		$res = $cyrus->get_quota_array ( $uid );
		$size = $cyrus->MailboxInfosSize ( $uid );
		$orgfree = $cyrus->USER_STORAGE_LIMIT - $cyrus->USER_STORAGE_USAGE;
		$free = FormatBytes ( $orgfree );
		
		if ($cyrus->MailBoxExists ( $uid )) {
			$graph1 = InsertChart ( 'js/charts.swf', "js/charts_library", "listener.graphs.php?USER_STORAGE_USAGE=$cyrus->USER_STORAGE_USAGE&STORAGE_LIMIT=$cyrus->USER_STORAGE_LIMIT&FREE=$orgfree", 200, 167, "", true, $users->ChartLicence );
		} else {
			$graph1 = "<H3>{no_mailbox_user}</H3>";
		}
		$mailboxInfos = "<div>
			<i>" . FormatBytes ( $cyrus->USER_STORAGE_USAGE ) . "/" . FormatBytes ( $cyrus->USER_STORAGE_LIMIT ) . "<br>
			 ($free {free})</i><br><strong>" . FormatBytes ( $size ) . " used</strong>
			 </div>";
	
	}
	
	$tpl = new templates ( );
	$export_mailbox = $tpl->_ENGINE_parse_body ( '{export_mailbox}' );
	$import_mailbox = $tpl->_ENGINE_parse_body ( '{import_mailbox}' );
	if (strlen ( $import_mailbox ) > strlen ( $export_mailbox )) {
		$import_mailbox = substr ( $import_mailbox, 0, strlen ( $export_mailbox ) - 3 ) . "...";
	}
	
	//sudo -u cyrusimap /usr/bin/cyrus/bin/reconstruct -r -f user/shortname  	
	$repair = 

	"<br>
    <table style='width:100%;' class=form>
    <tr>
    	<td coslpan=2><H3 style='color:#005447'>{tools}</H3></td>
    </tr>
    	<tr " . CellRollOver () . ">
    			<td width=99% class=legend nowrap>" . texttooltip ( '{repair_mailbox}', '{repair_mailbox_text}', "javascript:Loadjs('$page?script=repair_mailbox&uid=$uid');" ) . "</td>
				<td width=1%>" . imgtootltip ( "icon_roles.gif", '{repair_mailbox_text}', "Loadjs('$page?script=repair_mailbox&uid=$uid');" ) . "</td>    			
    	</tr>
    	<tr " . CellRollOver () . ">
    			<td width=99% class=legend nowrap>" . texttooltip ( $export_mailbox, '{export_mailbox_text}', "javascript:Loadjs('$page?script=export_script&uid=$uid');" ) . "</td>
				<td width=1%>" . imgtootltip ( "icon_roles.gif", '{export_mailbox_text}', "Loadjs('$page?script=export_script&uid=$uid');" ) . "</td>    			
    	</tr> 
    	
		<tr " . CellRollOver () . ">
    			<td width=99% class=legend nowrap>" . texttooltip ( $import_mailbox, '{import_mailbox_text}', "javascript:Loadjs('mailsync.php?uid=$uid');" ) . "</td>
				<td width=1%>" . imgtootltip ( "icon_sync.gif", '{export_mailbox_text}', "Loadjs('mailsync.php?uid=$uid');" ) . "</td>    			
    	</tr>  
    	
    	<tr " . CellRollOver () . ">
    			<td width=99% class=legend nowrap>" . texttooltip ( '{empty_this_mailbox}', '{empty_this_mailbox_text}',
	 			"javascript:Loadjs('domains.edit.user.empty.mailbox.php?&userid=$uid');" ) . "</td>
				<td width=1%>" . imgtootltip ( "ed_delete.gif", '{delete_this_mailbox}', "Loadjs('domains.edit.user.empty.mailbox.php?&userid=$uid');" ) . "</td>    			
    	</tr>     	     	   	
    	
    	   	
    	<tr " . CellRollOver () . ">
    			<td width=99% class=legend nowrap>" . texttooltip ( '{delete_this_mailbox}', '{delete_this_mailbox_text}', "javascript:Loadjs('$page?script=delete_mailbox&uid=$uid');" ) . "</td>
				<td width=1%>" . imgtootltip ( "ed_delete.gif", '{delete_this_mailbox}', "Loadjs('$page?script=delete_mailbox&uid=$uid');" ) . "</td>    			
    	</tr> 	
    </table>";
	
	$img_left_mbx = imgtootltip ( 'folder-mailbox-96.png', "{debug}", "Loadjs('$page?debug-mailbox-js=$uid')" );
	
	if (! $RealMailBox) {$repair = null;}
	
	$priv = new usersMenus();
	$ini = new Bs_IniHandler();
	$ini->loadString ($user->MailboxSecurityParameters);
	
	$button = "    
      	<tr>
      		<td colspan=2 align='right'>
      		<hr>
      		" . button ( "{change}", "Loadjs('domains.edit.user.create.mbx.php?uid=$uid')" ) . "
      		</td>
      	</tr>
      	";
	if ($priv->AllowAddUsers == false) {
		$button = null;
		$img_left_mbx = "<img src='img/folder-mailbox-96.png'>";
	}
	$subtitle = "{user_quota}";
	$main_graph = "<div style='border:1px solid #005447;padding:5px;margin:3px'><span id='mailbox_graph'>$graph1</span></div>";
	
	if ($user->MailBoxMaxSize == 0) {
		$subtitle = "<i>{user_has_no_quota}</i>";
		$graph1 = null;
		$mailboxInfos = "<strong>" . FormatBytes ( $size ) . " used</strong>";
		$mailboxInfos = null;
		$main_graph = null;
	}
	
	if ($ldap->ldap_last_error != null) {
		return nl2br ( $ldap->ldap_last_error );
	}
	
	
	$ADDisable=0;
	if($priv->EnableManageUsersTroughActiveDirectory){
		$ADDisable=1;
		$button=null;
	}
	
	$html = "
	<div id='usermailboxformdiv'>
      	<table style='width:100%'>
      	<tr>
      		<td width=1% valign='top'>$img_left_mbx</td>
      	<td>
		 
		    <form name='FFUserMailBox'>
		     <input type='hidden' name='UserMailBoxEdit' value='$uid'>
		     <table style='width:100%'>
		      	<tr>
		      		<td colspan=2>
		      			<H3 style='font-size:18px;color:#005447'>{settings}</h3>
		      			<hr style='border-color:#005447'>
		      		</td>
		      	</tr>
		      		<td valign='top'>
		      		$no_mailbox
		      			<table style='width:100%'>
		      		      	<tr>
		      					<td  align='right' width=1%>" . Field_TRUEFALSE_checkbox_img ( 'MailboxActive', $user->MailboxActive ) . "</td>
			      				<td class=legend style='text-align:left' class=legend>{MailboxActive}</td>			      	
		      				</tr>
		      				<tr>
			      				<td class=legend>{mailbox account}:</td>
			      				<td><strong style='font-size:13px;font-weight:normal'>$uid</strong></td>
		      				</tr>      	
		      				<tr>
			      				<td  align='right' nowrap class=legend valign='top'>{mailbox quota}:</td>
			      				<td>
			      					<table style='width:100%'>
			      						<tr>
			      							<td width=1% nowrap>" . Field_text ( 'MailBoxMaxSize', $user->MailBoxMaxSize, 'width:45px' ) . "&nbsp;MB</td>
			      							<td align='left'>" . help_icon ( $mailboxInfos, true ) . "</td>
			      						</tr>
			      						<tr>
			      							<td colspan=2><strong>$subtitle</strong></td>
			      						</tr>
			      					</table>
			      				</td>
		      				</tr>
		      				<tr>
		      					<td colspan=2><br><H3 style='font-size:18px;color:#005447'>{mailbox_priv}</h3><hr style='border-color:#005447'></td>
		      				</tr>
		      				<tr>
		      					<td colspan=2 align='left'>
		      						<table style='width:60%' class=form>
			      						<tr>
					      					<td class=legend>{mplt}:</td> 
					      					<td>" . Field_checkbox ( 'mp_l', 1, $ini->_params["mailbox"] ["l"], null, '{mpl}' ) . "</td>
				      					</tr>  
							      			<tr>
								      			<td class=legend nowrap>{mprt}:</td>
								      			<td>" . Field_checkbox ( 'mp_r', 1, $ini->_params["mailbox"] ["r"], null, '{mpr}' ) . "</td>
							      			</tr> 
							      			<tr>
								      			<td class=legend nowrap>{mpst}:</td>
								      			<td>" . Field_checkbox ( 'mp_s', 1, $ini->_params["mailbox"] ["s"], null, '{mps}' ) . "</td>
							      			</tr> 
							      			<tr>
								      			<td class=legend nowrap>{mpwt}:</td>
								      			<td>" . Field_checkbox ( 'mp_w', 1, $ini->_params["mailbox"] ["w"], null, '{mpw}' ) . "</td>
							      			</tr> 	
							      			<tr>
								      			<td class=legend nowrap>{mpit}:</td>
								      			<td>" . Field_checkbox ( 'mp_i', 1, $ini->_params["mailbox"] ["i"], null, '{mpi}' ) . "</td>
							      			</tr> 	
							      			<tr>
								      			<td class=legend nowrap>{mppt}:</td>
								      			<td>" . Field_checkbox ( 'mp_p', 1, $ini->_params["mailbox"] ["p"], null, '{mpp}' ) . "</td>
							      			</tr>
							      			<tr>
								      			<td class=legend nowrap>{mpct}:</td>
								      			<td>" . Field_checkbox ( 'mp_c', 1, $ini->_params["mailbox"] ["c"], null, '{mpc}' ) . "</td>
							      			</tr>	
							      			<tr>
								      			<td class=legend nowrap>{mpdt}:</td>
								      			<td>" . Field_checkbox ( 'mp_d', 1, $ini->_params["mailbox"] ["d"], null, '{mpd}' ) . "</td>
							      			</tr>	
							      			<tr>
								      			<td class=legend nowrap><strong>{mpat}</strong>:</td>
								      			<td>" . Field_checkbox ( 'mp_a', 1, $ini->_params["mailbox"] ["a"], null, '{mpa}' ) . "</td>
							      			</tr>		      				      					      					      				      				      					      					      			
			      					</table>
		      					</td>
		      			</tr>
		      	
		      	
		 			$button
		      	</table>
		      	</td>
		      	<td valign='top' style='padding:5px'>
						$main_graph
      					$mailboxInfos
      					$repair
      			</td>
		      	</table>
		      	</form>
		      </td>
		  </tr>
		 </table>
		 </div>
		 <script>
		 	function MyAdDisable(){
		 		var disable=$ADDisable;
		 		if(disable==1){DisableFieldsFromId('usermailboxformdiv');}
		 	}
		 
		 MyAdDisable();
		 </script>
		 ";
	
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_DELETE() {
	
	//remove user
	$ldap = new clladp ( );
	$hash = $ldap->UserDatas ($_GET["DeleteThisUser"]);
	
	if ($hash ["dn"] != null) {
		if ($hash ["dn"] != $ldap->suffix) {
			if ($ldap->ExistsDN ( $hash ["dn"])){
				writelogs ( "delete dn {$hash["dn"]}", __FUNCTION__, __FILE__, __LINE__ );
				$ldap->ldap_delete ( $hash ["dn"], false );
			}
		}
	}
	$hash = $ldap->UserGetGroups ($_GET["DeleteThisUser"]);
	if (is_array ( $hash )) {
		while ( list ( $num, $ligne ) = each ( $hash ) ) {
			if (! $ldap->UserDeleteToGroup ($_GET["DeleteThisUser"], $ligne )) {
				echo $ldap->ldap_last_error;
				exit ();
			}
		}
	}

}

function USER_DELETE_ALL_GROUPS($userid) {
	$ldap = new clladp ( );
	$hash = $ldap->UserGetGroups ( $userid );
	writelogs ( "delete this user from " . count ( $hash ) . " groups ", __FUNCTION__, __FILE__, __LINE__ );
	if (is_array ( $hash )) {
		while ( list ( $num, $ligne ) = each ( $hash ) ) {
			writelogs ( "delete  user $userid from  group number $ligne", __FUNCTION__, __FILE__, __LINE__ );
			if (! $ldap->UserDeleteToGroup ( $userid, $ligne )) {
				echo $ldap->ldap_last_error;
				exit ();
			}
		}
	}

}

function USER_ADD() {
	$userid = $_REQUEST["new_userid"];
	$password = $_REQUEST["password"];
	$group_id = $_REQUEST["group_id"];
	$ou = $_REQUEST["ou"];
	$tpl = new templates ( );
	
	$email = $_REQUEST["email"] . "@" . $_REQUEST["user_domain"];
	$email=strtolower($email);
	
	$user = new usersMenus ( );
	if ($user->EnableVirtualDomainsInMailBoxes == 1) {
		writelogs ( "Adding change $userid to \"$email\" in group $group_id", __FUNCTION__, __FILE__, __LINE__ );
		$userid = $email;
	}
	
	
	if(is_numeric($group_id)){
		$gp=new groups($group_id);
		writelogs( "privileges: $group_id -> AsComplexPassword = \"{$gp->Privileges_array["AsComplexPassword"]}\"", __FUNCTION__, __FILE__, __LINE__ );
		if($gp->Privileges_array["AsComplexPassword"]=="yes"){
			$ldap=new clladp();		
			$hash=$ldap->OUDatas($ou);	
			$privs=$ldap->_ParsePrivieleges($hash["ArticaGroupPrivileges"],array(),true);
			$policiespwd=unserialize(base64_decode($privs["PasswdPolicy"]));
			if(is_array($policiespwd)){
				$priv=new privileges();
				if(!$priv->PolicyPassword($password,$policiespwd)){return false;}
			}
		}else{
			writelogs( "privileges: $group_id -> AsComplexPassword = \"No\" -> continue", __FUNCTION__, __FILE__, __LINE__ );
		}
	}
	
	$users = new user ( $userid );
	if ($users->UserExists) {
		echo ($tpl->javascript_parse_text( 'ERROR: {account_already_exists}' ));
		return false;
	}
	
	writelogs("Adding $userid in group $group_id", __FUNCTION__, __FILE__, __LINE__ );
	
	$email = $_REQUEST["email"] . "@" . $_REQUEST["user_domain"];
	
	
	if ($ou == null) {echo html_entity_decode ( $tpl->javascript_parse_text ( 'ERROR:{error_no_ou}' ) );exit ();}
	if ($userid == null) {echo html_entity_decode ( $tpl->javascript_parse_text ( 'ERROR:{error_no_userid}' ) );exit ();}
	if ($password == null) {echo html_entity_decode ( $tpl->javascript_parse_text ( 'ERROR:{error_no_password}' ) );exit ();}
	if ($email == null) {echo html_entity_decode ( $tpl->javascript_parse_text ( 'ERROR:{error_no_email}' ) );exit ();}
	
	$ldap = new clladp();
	
	if (!is_numeric($group_id)) {
		writelogs ( "Groupid is not numeric", __FUNCTION__, __FILE__, __LINE__ );
		$default_dn_group = "cn=nogroup,ou=$ou,dc=organizations,$ldap->suffix";
		if (! $ldap->ExistsDN ( $default_dn_group )) {
			$ldap->AddGroup ( "nogroup", $ou );
		}
		$group_id = $ldap->GroupIDFromName ( $ou, "nogroup" );
		if (!is_numeric($group_id)) {$group_id = 0;}
		
	}
	
	$emT = explode ( '@', $email );
	
	//Verify domains --------------------------------------------------------------- 2008 10 05,P3
	$hash_domains_table = $ldap->hash_get_domains_ou ( $ou );
	if ($hash_domains_table [$_GET["user_domain"]] == null) {
		writelogs ( "$userid have no domains", __FUNCTION__, __FILE__, __LINE__ );
		writelogs ( "Create a new local domain by default", __FUNCTION__, __FILE__, __LINE__ );
		$ldap->AddDomainEntity ( $ou, $_REQUEST["user_domain"]);
	}
	//------------------------------------------------------------------------------
	

	$domains = $ldap->domains_get_locals_domains ( $ou );
	
	$dn = "cn=$userid,ou=$ou,dc=organizations,$ldap->suffix";
	if ($ldap->ExistsDN ( $dn )) {
		writelogs ( "$userid ($dn) already exists", __FUNCTION__, __FILE__, __LINE__ );
		echo $userid;
		exit ();
	
	}
	
	$users = new user ( $userid );
	$users->mail = $email;
	$users->accountGroup = $group_id;
	$users->domainname = $_REQUEST["user_domain"];
	if ($password != null) {$users->password = $password;}
	$users->ou = $ou;
	
	if ($domains [$_REQUEST["user_domain"]] == true) {
		$upd=array();
		writelogs ( "is a local domain {$_REQUEST["user_domain"]}={$domains[$_REQUEST["user_domain"]]}", __FUNCTION__, __FILE__, __LINE__ );
		$upd ["ObjectClass"] [] = 'ArticaSettings';
		$users->MailboxActive = "TRUE";
	
	}
	
	if (! $users->add_user ()) {
		echo "ERROR:" . $users->ldap_error."\n".basename(__FILE__)."\nLine:".__LINE__;
		exit();
	}
	
	writelogs ( "Success adding user, now, add user $users->uid to group $group_id ", __FUNCTION__, __FILE__, __LINE__ );
	if ($group_id > 0) {
		$ldap->AddUserToGroup ( $group_id, $users->uid );
	}
	echo $users->uid;
}

function SaveUserInfos() {
	$user = new user ($_GET["userid"]);
	
	if(isset($_GET["MailAlternateAddress"])){
		$user->MailAlternateAddress = $_GET["MailAlternateAddress"];
	}
	if(isset($_GET["RecipientToAdd"])){
		$user->RecipientToAdd = $_GET["RecipientToAdd"];
	}
	
	$tpl = new templates ( );
	if (! $user->add_user ()) {
		echo $user->ldap_error;
	}
	$sock = new sockets ( );
	$sock->getFrameWork ( "cmd.php?postfix-hash-tables=yes" );
}

function SaveLdapUser() {
	$ldap = new clladp ( );
	$dn = $_GET["dn"];
	unset ($_GET["dn"]);
	unset ($_GET["ou"]);
	unset ($_GET["SaveLdapUser"]);
	
	$users = new usersMenus ( );
	
	$user = new user ($_GET["uid"]);
	if ($uid == null) {
		$uid = $user->_GetuidFromDn ( $dn );
	
	}
	$user = new user ($_GET["uid"]);
	writelogs ( "UID=$uid,DN=$dn", __FUNCTION__, __FILE__, __LINE__ );
	
	if(isset($_GET["SimpleGroupWareActive"])){
		writelogs ( "[$uid]:: SimpleGroupWareActive={$_GET["SimpleGroupWareActive"]}", __FUNCTION__, __FILE__, __LINE__ );
		$SimpleGroupWareActive = $_GET["SimpleGroupWareActive"];
		unset ($_GET["SimpleGroupWareActive"]);
	}
	
	$smtp_sender_dependent_authentication_password = $_GET["smtp_sender_dependent_authentication_password"];
	$smtp_sender_dependent_authentication_username = $_GET["smtp_sender_dependent_authentication_username"];
	
	unset ($_GET["smtp_sender_dependent_authentication_password"]);
	unset ($_GET["smtp_sender_dependent_authentication_username"]);
	
	if ($user->SenderCanonical != null)
		if ($smtp_sender_dependent_authentication_password != null) {
			if ($smtp_sender_dependent_authentication_username != null) {
				$sasl = new smtp_sasl_password_maps ( );
				$sasl->add ( $user->SenderCanonical, $smtp_sender_dependent_authentication_username, $smtp_sender_dependent_authentication_password );
			}
		}
	
	$hash = $ldap->getobjectDNClass ( $dn, 1 );
	
	writelogs ( "[{$_GET["uid"]}]:: Save object user email address is {$_GET["mail"]}", __FUNCTION__, __FILE__, __LINE__ );
	unset ($_GET["user_domain"]);
	unset ($_GET["SenderCanonical"]);
	unset ($_GET["SaveSenderCanonical"]);
	
	$tpl = new templates ( );
	while ( list ( $num, $ligne ) = each ($_GET ) ) {
		if ($ligne == 'true') {
			$ligne = 'TRUE';
		}
		if ($ligne == 'false') {
			$ligne = 'FALSE';
		}
		if ($ligne != null) {
			writelogs ( "[{$_GET["uid"]}]:: Save object user->$num=$ligne", __FUNCTION__, __FILE__, __LINE__ );
			$user->$num = $ligne;
		}
	
	}
	$user->FinalDateToLive = $_GET["FinalDateToLive"];
	$user->DotClearUserEnabled = $_GET["DotClearUserEnabled"];
	
	if ($user->add_user ()) {
		
		if (is_numeric ( $SimpleGroupWareActive )) {
			$users = new usersMenus ( );
			if ($users->SIMPLE_GROUPEWARE_INSTALLED) {
				include_once ("ressources/class.mysql.inc");
				$sql = new mysql ( );
				$sql->SET_SIMPLE_GROUPWARE_ACTIVE ( $user->uid, $SimpleGroupWareActive );
			}
		} else {
			writelogs ( "[{$user->uid}]:: warning \"$SimpleGroupWareActive\" is not numeric for SimpleGroupWareActive", __FUNCTION__, __FILE__, __LINE__ );
		}
		
		echo html_entity_decode ( $tpl->_ENGINE_parse_body ( "{edit} $uid:{success}\n" ) );
	} else {
		echo "ERROR $user->ldap_error";
	}
}

function Cyrus_mailbox_apply_settings() {
	$usr = new usersMenus ( );
	$tpl = new Templates ( );
	$uid = $_GET["Cyrus_mailbox_apply_settings"];
	if ($usr->AsMailBoxAdministrator == false) {
		echo $tpl->_ENGINE_parse_body ( '{no_privileges}' );
		exit ();
	}
	$cyrus = new cyrus ( );
	$ldap = new clladp ( );
	$hash = $ldap->UserDatas ( $uid );
	if ($hash ["MailboxActive"] == "TRUE") {
		$createMailbox = true;
		if ($cyrus->CreateMailbox ( $uid ) == false) {
			$createMailbox = false;
			$error = "{failed}:{creating_mailbox}:$uid\n$cyrus->cyrus_last_error\n";
		} else {
			$error = "{success}:{creating_mailbox}:$uid\n";
		}
	
	}
	
	echo html_entity_decode ( $tpl->_ENGINE_parse_body ( $error ) );
}

function UserMailBoxDebugJs() {
	$uid = $_GET["debug-mailbox-js"];
	$tpl = new templates ( );
	$page = CurrentPageName ();
	$title = $tpl->_ENGINE_parse_body ( "$uid {debug} {events}" );
	$html = "
function LoadDebugmbx(){
		YahooWin6('650','$page?debug-mailbox-user=$uid','$title');
	
	}
	
	LoadDebugmbx();

";
	echo $html;

}

function UserMailBoxDebugEvents() {
	$uid = $_GET["debug-mailbox-user"];
	$user = new user ( $uid );
	$sock = new sockets ( );
	$datas = $sock->getfile ( "DebugImapMbx:$uid;$user->password" );
	$tbl = explode ( "\n", $datas );
	
	$table = "<table style='width:99%'>";
	while ( list ( $num, $val ) = each ( $tbl ) ) {
		if (trim ( $val ) == null) {
			continue;
		}
		$table = $table . "<tr>
		<td width=1%><img src='img/fw_bold.gif'>
		<td><code>$val</code>
		</tr>";
	
	}
	
	$table = $table . "</table>";
	$table = RoundedLightWhite ( $table );
	$html = "<H1>IMAP:: $uid {events}</H1>
	<div style='width:100%;height:250px;overflow:auto'>$table</div>
	";
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function UserMailBoxEdit() {
	$usr = new usersMenus ( );
	$tpl = new templates ( );
	
	if ($usr->AsMailBoxAdministrator == false) {
		echo $tpl->_ENGINE_parse_body ( '{no_privileges}' );
		exit ();
	}
	$_GET["Cyrus_mailbox_apply_settings"] = $_GET["UserMailBoxEdit"];
	
	$acls = "[mailbox]\n";
	
	while ( list ( $num, $val ) = each ($_GET ) ) {
		if (preg_match ( '#mp_([a-zA-Z])#', $num, $re )) {
			writelogs ( "set acls {$re[1]}=$val on mailbox", __FUNCTION__, __FILE__, __LINE__ );
			$acls = $acls . "{$re[1]}=$val\n";
		}
	}
	
	$user = new user ($_GET["UserMailBoxEdit"]);
	$user->MailBoxMaxSize = $_GET["MailBoxMaxSize"];
	$user->MailboxActive = strtoupper ($_GET["MailboxActive"]);
	$user->MailboxSecurityParameters = $acls;
	
	if (! $user->add_user ()) {
		echo $user->ldap_error;
	}
	Cyrus_mailbox_apply_settings ();

}

function UserFTPEdit() {
	$usr = new usersMenus ( );
	
	$tpl = new templates ( );
	$userid = $_POST["UserFTPEdit"];
	$user = new user ( $userid );
	
	unset ($_POST["UserFTPEdit"]);
	while ( list ( $num, $val ) = each ($_POST ) ) {
		if (trim ( $val ) == null) {continue;}
		$user->$num = $val;
	}
	
	$user->FTPSettingsEdit();

}

function AddAliases() {
	$ldap = new clladp ( );
	$tpl = new templates ( );
	$_GET["aliase"]=trim($_GET["aliase"]);
	$_GET["aliase"]=str_replace(" ","",$_GET["aliase"]);
	writelogs ( "Adding a new alias \"{$_GET["aliase"]}\" for uid={$_GET["AddAliases"]}", __FUNCTION__, __FILE__, __LINE__ );
	$uid = $ldap->uid_from_email ($_GET["aliase"]);
	writelogs ( "\"{$_GET["aliase"]}\"=\"$uid\"", __FUNCTION__, __FILE__, __LINE__ );
	if (trim ( $uid ) != null) {
		writelogs ( "Error, this email already exists", __FUNCTION__, __FILE__, __LINE__ );
		echo $tpl->_ENGINE_parse_body ( '{error_alias_exists}' );
		exit ();
	}
	writelogs ( "OK, this email did not exists", __FUNCTION__, __FILE__, __LINE__ );
	$user = new user ($_GET["AddAliases"]);
	
	if (substr ($_GET["aliase"], 0, 1 ) == '*') {
		$_GET["aliase"] = str_replace ( '*', '', $_GET["aliase"]);
	} else {
		if (! $user->isEmailValid ($_GET["aliase"])){
			writelogs ( "Error, this email is invalid", __FUNCTION__, __FILE__, __LINE__ );
			echo $tpl->_ENGINE_parse_body ( '{error_email_invalid}' );
			exit ();
		}
	}
	
	writelogs ( "OK, this {$_GET["aliase"]} email is valid add it for uid=$user->uid", __FUNCTION__, __FILE__, __LINE__ );
	
	if (! $user->add_alias ($_GET["aliase"])){
		writelogs ( "Error, LDAP DATABASE $user->ldap_error", __FUNCTION__, __FILE__, __LINE__ );
		echo $user->ldap_error;
		exit ();
	}
	
	echo html_entity_decode ( $tpl->_ENGINE_parse_body ( '{success}' ) );

}

function AddAliasesMailing() {
	$user = new user ($_GET["AddAliasesMailing"]);
	$user->AddAliasesMailing ($_GET["aliase"]);
}

function DeleteAliases() {
	$ldap = new clladp ( );
	$hash = $ldap->UserDatas ($_GET["DeleteAliases"]);
	$updatearray ["mailAlias"] = $_GET["aliase"];
	if (! $ldap->Ldap_del_mod ( $hash ["dn"], $updatearray )) {
		echo $ldap->ldap_last_error;
	}
	$sock = new sockets ( );
	$sock->getFrameWork ( "cmd.php?postfix-hash-tables=yes" );
}

function DeleteAliasesMailing() {
	$user = new user ($_GET["DeleteAliasesMailing"]);
	$user->delete_AliasesMailing ($_GET["aliase"]);
}

function AddressInfosSave() {
	$userid = $_GET["UserAddressSubmitedForm"];
	unset ($_GET["UserAddressSubmitedForm"]);
	$user = new user ( $userid );
	while ( list ( $num, $ligne ) = each ($_GET ) ) {
		writelogs ( "Save address info user->$num=$ligne (DisplayName)", __FUNCTION__, __FILE__, __LINE__ );
		$user->$num = $ligne;
	}
	
	$tpl = new templates ( );
	if ($user->add_user () == false) {
		echo $user->error;
	} else {
		echo html_entity_decode ( $tpl->_ENGINE_parse_body ( "{profile}:{success}\n" ) );
	}

}

function USER_FTP_JS() {
	$page = CurrentPageName ();
	
	$html = "
	var x_USER_FTP_JS_START=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
      //document.getElementById('imgftp').src='img/folder-96-pure-ftpd-share.png';
      RefreshTab('container-users-tabs');
      
      }	

	function USER_FTP_JS_START(){
		var XHR = new XHRConnection();
		XHR.appendData('UserFTPEdit',document.getElementById('UserFTPEdit').value);
		XHR.appendData('userid',document.getElementById('UserFTPEdit').value);
		XHR.appendData('FTPQuotaMBytes',document.getElementById('FTPQuotaMBytes').value);
		XHR.appendData('FTPQuotaFiles',document.getElementById('FTPQuotaFiles').value);
		XHR.appendData('FTPDownloadBandwidth',document.getElementById('FTPDownloadBandwidth').value);
		XHR.appendData('FTPUploadBandwidth',document.getElementById('FTPUploadBandwidth').value);
		XHR.appendData('FTPUploadRatio',document.getElementById('FTPUploadRatio').value);
		XHR.appendData('homeDirectory',document.getElementById('homeDirectory').value);
		if(document.getElementById('FTPStatus').checked){XHR.appendData('FTPStatus','enabled');}else{XHR.appendData('FTPStatus','no');}
		
		document.getElementById('imgftp').src='img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',x_USER_FTP_JS_START);		
	
	}
	USER_FTP_JS_START();
	
	";
	echo $html;
}

function USER_FTP() {
	
	$user = new user ($_GET["userid"]);
	$ou = $user->ou;
	$priv = new usersMenus ( );
	$page = CurrentPageName ();
	$button = button ( "{apply}", "SaveFTPUserSettings()" );
	$browse="<input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('browse-disk.php?field=homeDirectoryFTP')\">";
	$homeLocked=0;
	if ($priv->AllowAddUsers == false) {
		$button = null;
		$delete = null;
		$browse = null;
		$homeLocked=1;
	}
	
	$title = "<div style='font-size:16px'>{$user->DisplayName} {ftp_access}</div>";
	$style_form = "font-size:13px;padding:3px";
	$time=time();
	$form = "<div id='$time'>
	 	
      	<input type='hidden' id='UserFTPEdit' name='UserFTPEdit' value='{$_GET["userid"]}'>
      	<table style='width:100%;' class=form>
      	
      	<tr>
	      	
      		<td  align='right' width=1%>" . Field_checkbox ( "FTPStatus", 'enabled',$user->FTPStatus,"CheckUserFTPField()" ) . "
      		
      		</td>
	      	<td style='font-size:14px'><strong>{FTPStatus}</strong>
	      	
      	</tr>     	
      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{FTPQuotaMBytes}:</strong></td>
	      	<td style='font-size:14px'>" . Field_text ( 'FTPQuotaMBytes', $user->FTPQuotaMBytes, 'width:60px;font-size:14px', $style_form, null ) . "&nbsp;MB</td>
      	</tr>
      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{FTPQuotaFiles}:</strong></td>
	      	<td style='font-size:14px'>" . Field_text ( 'FTPQuotaFiles', $user->FTPQuotaFiles, 'width:60px;font-size:14px', $style_form, null ) . "&nbsp;files</td>
      	</tr>      	
      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{FTPDownloadBandwidth}:</strong></td>
	      	<td style='font-size:14px'>" . Field_text ( 'FTPDownloadBandwidth', $user->FTPDownloadBandwidth, 'width:60px;font-size:14px', $style_form, null ) . "&nbsp;kb/s</td>
      	</tr>         	
      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{FTPUploadBandwidth}:</strong></td>
	      	<td style='font-size:14px'>" . Field_text ( 'FTPUploadBandwidth', $user->FTPUploadBandwidth, 'width:60px;font-size:14px', $style_form, null ) . "&nbsp;kb/s</td>
      	</tr>     

      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{FTPUploadRatio}:</strong></td>
	      	<td style='font-size:14px'>" . Field_text ( 'FTPUploadRatio', $user->FTPUploadRatio, 'width:60px;font-size:14px', $style_form, null ) . "&nbsp;</td>
      	</tr> 
      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{FTPDownloadRatio}:</strong></td>
	      	<td style='font-size:14px'>" . Field_text ( 'FTPDownloadRatio', $user->FTPDownloadRatio, 'width:60px;font-size:14px', $style_form, null ) . "&nbsp;</td>
      	</tr>
      	<tr>
	      	<td  align='right' class=legend nowrap  style='font-size:14px'>{homeDirectory}:</strong></td>
	      	<td>
	      		<table>
	      		<tr>
	      			<td  style='font-size:13px'>" . Field_text ( 'homeDirectoryFTP', $user->homeDirectory, 'width:190px;font-size:14px;paddong:3px', null, null ) . "&nbsp;</td>
	      			<td valign='top'>
	      			$browse
	      			
	      			</td>
	      		</tr>
	      		</table>
      	</tr>    
      	<tr>
      		<td colspan=2 align='right'><hr>$button</td>
      	</tr>      	
      	</table>
      	<script>
      	
	function x_SaveFTPUserSettings(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('container-users-tabs');	
	}	

	function LoadCommands(){
		LoadAjax('options_service','$page?commands-list=yes&hostname={$_GET["hostname"]}&key=$key');
	
	}
	
	
	function SaveFTPUserSettings(){
			var XHR = new XHRConnection();
			XHR.appendData('UserFTPEdit','{$_GET["userid"]}');
			if(document.getElementById('FTPStatus').checked){ XHR.appendData('FTPStatus','enabled');}else{ XHR.appendData('FTPStatus','disabled');}
			XHR.appendData('FTPQuotaMBytes',document.getElementById('FTPQuotaMBytes').value);
			XHR.appendData('FTPQuotaFiles',document.getElementById('FTPQuotaFiles').value);
			XHR.appendData('FTPDownloadBandwidth',document.getElementById('FTPDownloadBandwidth').value);
			XHR.appendData('FTPUploadBandwidth',document.getElementById('FTPUploadBandwidth').value);
			XHR.appendData('FTPUploadRatio',document.getElementById('FTPUploadRatio').value);
			XHR.appendData('FTPDownloadRatio',document.getElementById('FTPDownloadRatio').value);
			XHR.appendData('homeDirectory',document.getElementById('homeDirectoryFTP').value);
			AnimateDiv('$time');
			XHR.sendAndLoad('$page', 'POST',x_SaveFTPUserSettings);
		}    

	function CheckUserFTPField(){
		var homeLocked=$homeLocked;
		document.getElementById('FTPQuotaMBytes').disabled=true;
		document.getElementById('FTPQuotaFiles').disabled=true;
		document.getElementById('FTPDownloadBandwidth').disabled=true;
		document.getElementById('FTPUploadBandwidth').disabled=true;
		document.getElementById('FTPUploadRatio').disabled=true;
		document.getElementById('FTPDownloadRatio').disabled=true;
		document.getElementById('homeDirectoryFTP').disabled=true;
		if(!document.getElementById('FTPStatus').checked){return;}
		document.getElementById('FTPQuotaMBytes').disabled=false;
		document.getElementById('FTPQuotaFiles').disabled=false;
		document.getElementById('FTPDownloadBandwidth').disabled=false;
		document.getElementById('FTPUploadBandwidth').disabled=false;
		document.getElementById('FTPUploadRatio').disabled=false;
		document.getElementById('FTPDownloadRatio').disabled=false;
		if(homeLocked==0){
			document.getElementById('homeDirectoryFTP').disabled=false;
		}		
	
	}
      	
     CheckUserFTPField();
    </script> 	
      	
      	";
	$tpl = new templates ( );
	
	$apply = USER_FTP_APPLY ();
	$html = "
    $form
      	";
	
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_FTP_APPLY() {
	
	$priv = new usersMenus ( );
	if ($priv->AllowAddUsers == false) {
		return null;
	}
	
	$page = CurrentPageName ();
	$apply = "
	<table style='width:100%'>
	<tr>
		<td valign='top'>" . imgtootltip ( 'system-64.png', '{apply_pureftpd}', "javascript:ParseForm('FFTP','$page',true);LoadAjax('applypureftpd','$page?applypureftpd=yes');" ) . "</td>
		<td valign='top'><H5>{apply_pureftpd}</H5></td>
		</tr>
		<tr>
		<td colspan=2>{apply_pureftpd_text}</td>
		</tr>
	</tr>
	</table>";
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( RoundedLightWhite ( $apply ) );

}

function USER_FTP_APPLY_SAVE() {
	
	include_once ('ressources/class.pure-ftpd.inc');
	$ftp = new pureftpd ( );
	$ftp->SaveToLdap ();
	
	$user = new usersMenus ( );
	if (! isset ($_GET["hostname"])){
		$hostname = $user->hostname;
	} else {
		$hostname = $_GET["hostname"];
	}
	$sock = new sockets ( );
	writelogs ( 'Start pure-ftpd ->pureftpd_saveconf....', __FUNCTION__, __FILE__, __LINE__ );
	$sock->getfile ( "pureftpd_saveconf:$hostname" );
	echo USER_FTP_APPLY ();

}

function USER_GROUP($userid) {
	$html = "<div id='POPUP_MEMBER_GROUP_ID'>" . USER_GROUP_CONTENT ( $userid ) . "</div>";
	return $html;
}

function USER_GROUP_LIST($userid) {
	if (substr ( $userid, strlen ( $userid ) - 1, 1 ) == '$') {$users = new computers ( $userid );} else {$users = new user ( $userid );}
	$ou = $users->ou;
	$groups = $users->Groups_list();
	$priv = new usersMenus ( );
	$sambagroups = array ("515" => true, "548" => true, "544" => true, "551" => true, "512" => true, "514" => true, "513" => true, 550 => true, 552 => true );
	if (is_array ( $groups )) {
	$gp="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th colspan=3>{groups}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
		while ( list ( $num, $ligne ) = each ( $groups ) ) {
			$delete = imgtootltip ( '32-group-delete-icon.png', '{DISCONNECT_FROM_GROUP} ' . $ligne, "DeleteUserGroup($num,'$userid')" );
			$privileges = imgtootltip ( "members-priv-32.png", '{privileges}', "Loadjs('domains.edit.group.php?GroupPrivilegesjs=$num')" );
			
			if($priv->EnableManageUsersTroughActiveDirectory){
				$delete = imgtootltip ( '32-group-delete-icon-grey.png', '{DISCONNECT_FROM_GROUP} ' . $ligne);	
				$privileges = imgtootltip ( "members-priv-32-grey.png", '{privileges}' );
			}
			
			if(!is_numeric($num)){$num=urlencode($num);}
			$groupjs = "Loadjs('domains.edit.group.php?ou=$ou&js=yes&group-id=$num')";
			
			if ($sambagroups [$ligne]) {$privileges = null;$groupjs = null;}
			
			if ($priv->AllowAddUsers == false) {$delete = "&nbsp;";$groupjs = null;}
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$gp = $gp . "
			<tr class=$classtr>
			<td width=1%><img src='img/32-group-icon.png'></td>
			<td valign='middle' style='font-size:15px;font-weight:bold'><a href=\"javascript:blur();\" OnClick=\"javascript:$groupjs\" style='font-size:15px;font-weight:bold;text-decoration:underline'>$ligne</a></td>
			<td valign='middle' width=1%>$privileges</td>
			<td width=1% valign='top'>$delete</td>
			</tr>";
		
		}
		
		$gp = $gp . "
		</tbody>
		</table>";
	}
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $gp );
}

function USER_GROUP_CONTENT($userid) {
	
	if (substr ( $userid, strlen ( $userid ) - 1, 1 ) == '$') {
		$users = new computers ( $userid );
	} else {
		$users = new user ( $userid );
	}
	$ou = $users->ou;
	$sambagroups = array ("515" => true, "548" => true, "544" => true, "551" => true, "512" => true, "514" => true, "513" => true, 550 => true, 552 => true );
	
	$priv = new usersMenus ( );
	$button = "<input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddMemberGroup();\" style='margin-bottom:0px'>";
	if ($priv->AllowAddUsers == false) {
		$button = null;
	}
	
	$gp = "<h3>{member_of_group}:</H3>
		<center>
			<div id='USER_GROUP' style='margin:3px;padding:5px;height:200px;overflow:auto'>" . USER_GROUP_LIST ( $userid ) . "</div>
		</center>";
	
	
	$ou_encoded = base64_encode ( $ou );
	$groupjs="Loadjs('domains.group.user.affect.php?ou=$ou_encoded&uid=$userid')";
	$addgroup=Paragraphe ("64-folder-group-add.png", "{add_group}", "{ADD_USER_GROUP_TEXT}", "javascript:$groupjs" );
	
	if($priv->EnableManageUsersTroughActiveDirectory){
		$addgroup=Paragraphe ("64-folder-group-add-grey.png", "{add_group}", "{ADD_USER_GROUP_TEXT}");
	}
	
	$html = "
	<table style='width:100%'>
	<tr>
	<td valign='top'>$addgroup</td>
	<td valign='top'>
	
	<h5>$users->DisplayName:{move_member}</h5>
	<form name='ffm1'>
	<input type='hidden' name='userid' id='userid' value='$userid'>
	$gp
	</td>
	</tr>
	</table>";
	$tpl = new Templates ( );
	
	return $tpl->_ENGINE_parse_body ( $html );

}



function DeleteUserGroup() {
	$usr = new usersMenus ( );
	$tpl = new templates ( );
	if ($usr->AllowAddGroup == false) {
		echo $tpl->_ENGINE_parse_body ( '{no_privileges}' );
		exit ();
	}
	$ldap = new clladp ( );
	
	$userid = $_GET["user"];
	$groupid = $_GET["DeleteUserGroup"];
	if (! $ldap->UserDeleteToGroup ( $userid, $groupid )) {
		echo $ldap->ldap_last_error;
	}
}

function AddMemberGroup() {
	$usr = new usersMenus ( );
	$tpl = new templates ( );
	
	writelogs ( "Adding user {$_GET["user"]} to group {$_GET["AddMemberGroup"]}", __FUNCTION__, __FILE__, __LINE__ );
	
	if ($usr->AllowAddGroup == false) {
		writelogs ( "The administrator have no provileges to execute this operation....", __FUNCTION__, __FILE__, __LINE__ );
		echo $tpl->_ENGINE_parse_body ( '{no_privileges}' );
		echo Page ($_GET["user"]);
		exit ();
	}
	
	if (trim ($_GET["AddMemberGroup"] == null )) {
		return null;
	}
	$ldap = new clladp ( );
	$ldap->AddUserToGroup ($_GET["AddMemberGroup"], $_GET["user"]);
	if ($ldap->ldap_last_error != null) {
		echo $ldap->ldap_last_error;
	} else {
		$tpl = new templates ( );
		echo html_entity_decode ( $tpl->_ENGINE_parse_body ( "{success}: {$_GET["user"]} to group {$_GET["AddMemberGroup"]}" ) );
		writelogs ( "Adding user {$_GET["user"]} to group {$_GET["AddMemberGroup"]} => SUCCESS", __FUNCTION__, __FILE__, __LINE__ );
	}
	
	die ();
}

function COMPUTER_SAVE_INFOS() {
	$tpl = new templates ( );
	if (preg_match ( "#newcomputer#", $_GET["uid"])){
		echo $tpl->_ENGINE_parse_body ( 'ERROR:{give_computer_name}' );
		exit ();
	
	}
	
	if($_GET["add_computer_form"]){
		if($_GET["ComputerMacAddress"]<>null){
			$comp=new computers();
			$uidfound=$comp->ComputerIDFromMAC($_GET["ComputerMacAddress"]);
			if(trim($uidfound)<>null){
					if($uidfound<>$_GET["uid"] . '$'){
					echo $tpl->javascript_parse_text("{this_mac_address_is_already_used_by}:$uidfound");
					return;
				}
			}
		}
	}
	
	$computer = new computers ($_GET["userid"]);
	
	$computer->uid = $_GET["uid"] . '$';
	$computer->ComputerMacAddress = $_GET["ComputerMacAddress"];
	$computer->ComputerIP =$_GET["ComputerIP"];
	$computer->DnsZoneName =$_GET["DnsZoneName"];
	$computer->ComputerCPU = $_GET["ComputerCPU"];
	$computer->DnsType = $_GET["DnsType"];
	$computer->DnsMXLength = $_GET["DnsMXLength"];
	$computer->dhcpfixed = $_GET["dhcpfixed"];
	$computer->VolatileIPAddress = $_GET["VolatileIPAddress"];
	
	if ($_GET["userid"] == "newcomputer$") {
		
		if (! $computer->Add()) {
			echo "ERROR:$computer->ldap_error";
			exit ();
		} else {
			writelogs ( "Success updating/adding $computer->uid", __FUNCTION__, __FILE__, __LINE__ );
			if(isset($_GET["gpid"])){
				writelogs ( "adding computer to group {$_GET["gpid"]}", __FUNCTION__, __FILE__, __LINE__ );
				$group = new groups ($_GET["gpid"]);
				$group->AddUsertoThisGroup ( $computer->uid );
				exit ();
			}
			exit ();
		}
	}
	
	if (! $computer->Edit ()) {
		echo $computer->ldap_error;
	} else {
		writelogs ( "Success updating/adding $computer->uid", __FUNCTION__, __FILE__, __LINE__ );
		
	}

}

function COMPUTER_NMAP() {
	$sock = new sockets ( );
	$datas = $sock->getFrameWork("cmd.php?nmap-scan={$_GET["NmapScanComputer"]}");
	$tbl = explode ( "\n", $datas );
	while ( list ( $num, $ligne ) = each ( $tbl ) ) {
		if (trim ( $ligne != null )) {
			$html = $html . "<div>" . htmlentities ( $ligne ) . "</div>";
		}
	
	}
	
	$div = "<div style='width:100%;height:350px;overflow:auto'>$html</div>";
	
	echo $div;
}

function COMPUTER_DELETE() {
	$comp = new computers ($_GET["DeleteComputer"]);
	$comp->DeleteComputer ();

}

function COMPUTER_ADD_ALIAS() {
	writelogs ( "adding aliase for  to group {$_GET["userid"]}", __FUNCTION__, __FILE__, __LINE__ );
	$comp = new computers ($_GET["userid"]);
	$comp->ComputerAddAlias ($_GET["ComputerAddAlias"]);

}
function COMPUTER_DELETE_ALIAS() {
	writelogs ( "delete aliase for  to group {$_GET["userid"]}", __FUNCTION__, __FILE__, __LINE__ );
	$comp = new computers ($_GET["userid"]);
	$comp->ComputerDelAlias ($_GET["DeletComputerAliases"]);
}

function USER_FETCHMAIL($uid) {
	include_once (dirname ( __FILE__ ) . '/ressources/class.fdm.inc');
	$fdm = new fdm ( $uid );
	$users = new usersMenus ( );
	
	if (! $users->fdm_installed) {
		$warning = "<strong>{fdm_not_installed}</strong>";
	}
	
	$users->LoadModulesEnabled ();
	if ($users->EnableFDMFetch != 1) {
		$warning = $warning . "<br><strong>{fdm_not_enabled}</strong>";
	}
	
	if (! $users->fdm_cache) {
		$warning = $warning . "<br><strong>{fdm_cache_not_exists}</strong>";
	
	}
	if (strlen ( $warning ) > 0) {
		$warning = RoundedLightYellow ( $warning );
	}
	
	$html = "
	<hr>
	<H3>$uid::{fetch_mails}</H3>$warning
	
	<table style='width:100%'>
	<tr>
	<td valign='top'><br><div id='fdm_list' style='width:450px'>" . USER_FETCHMAIL_LIST ( $uid ) . "</div></td>
	<td valign='top' width=1%>
	
	<table style='width:100%'><tr>
	<td>" . imgtootltip ( 'add-fetchmail-48.png', '{add_rule}', "fdm_addrule('$uid')" ) . "</td>
	<td>" . imgtootltip ( '48-logs.png', '{all_events}', "fdm_events('$uid')" ) . "</td>
	
	</tr>
	</table>
	
	";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_FETCHMAIL_LIST($uid) {
	include_once (dirname ( __FILE__ ) . '/ressources/class.fdm.inc');
	$fdm = new fdm ( $uid );
	$rules = $fdm->main_array;
	$html = "<table style='width:350px;'>";
	if (! is_array ( $rules )) {
		return null;
	}
	while ( list ( $num, $ligne ) = each ( $rules ) ) {
		$html = $html . "<tr " . CellRollOver () . ">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:12px'>" . texttooltip ( $ligne ["server_name"], '{edit}', "fdm_ShowRule('$uid','$num')" ) . "</strong></td>
		<td width=1% nowrap><strong>{$ligne["server_type"]}</strong></td>
		<td width=1% nowrap><strong>{$ligne["username"]}</strong></td>
		<td width=1% nowrap>" . imgtootltip ( 'nsf_small.gif', '{see_config}', "fdm_ShowScript('$uid','$num')" ) . "</strong></td>
		<td width=1% nowrap>" . imgtootltip ( 'ed_delete.gif', '{delete}', "fdm_DeleteScript('$uid','$num')" ) . "</strong></td>
		</tr>
		<tr><td colspan=6><hr></td></tr>
		";
	
	}
	
	$html = $html . "</table>";
	return RoundedLightWhite ( $html );

}

function SWITCH_SCRIPTS() {
	
	switch ($_GET["script"]) {
		case "repair_mailbox" :
			echo js_MAILBOX_REPAIR ();
			exit ();
			break;
		case "delete_mailbox" :
			echo js_MAILBOX_DELETE ();
			exit ();
			break;
		case "export_script" :
			echo js_EXPORT_MAILBOX ();
			exit ();
			break;
		case "user_transport" :
			echo js_USER_TRANSPORT ();
			exit ();
			break;
		case "import_script" :
			echo js_MAILBOX_IMPORT ();
			exit ();
			break;
		default :
			break;
	}

}

function js_USER_TRANSPORT() {
	$page = CurrentPageName ();
	$uid = $_GET["uid"];
	$tpl = new templates ( );
	$c = $tpl->_ENGINE_parse_body ( '{user_transport}' );
	
	$html = "
	YahooWin3(500,'$page?user_transport=yes&uid=$uid','$c','');
	
	
var x_SaveUserTransport=function(obj){
	  var results=trim(obj.responseText);
	  if(results.length>0){alert(results);} 
      YahooWin3(500,'$page?user_transport=yes&uid=$uid','$c','');
	}	
	
	function SaveUserTransport(){
	  var XHR = new XHRConnection();
      XHR.appendData('relay_address',document.getElementById('relay_address').value);   
      XHR.appendData('relay_port',document.getElementById('relay_port').value);   
      XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);   
	  XHR.appendData('uid','$uid');        
      XHR.sendAndLoad('$page', 'GET',x_SaveUserTransport);       
	  }
	  
	  
	 function DeleteAlternateSmtpRelay(){
	 	 var XHR = new XHRConnection();
	 	 XHR.appendData('DeleteAlternateSmtpRelay','$uid'); 
	 	 XHR.sendAndLoad('$page', 'GET',x_SaveUserTransport);       
	 	}
	
	";
	echo $html;
}

function USER_CANONICAL_POPUP() {
	
	$uid = $_GET["uid"];
	$user = new user ( $uid );
	$canonical = $user->SenderCanonical;
	
	$html = "
<H1>{sender_canonical}</H1>
<table style='width:100%'>
<tr>
	<td valign='top'><img src='img/128-email-out.png'></td>
<td valign='top'>
<p class=caption>{sender_canonical_text}</p>
<div id='canonical_div'>
<table style='width:100%' class=table_form>		
		<tr>
			<td align='right' nowrap class=legend $styleTDRight nowrap>{sender_canonical}:</strong>
			<td $styleTDLeft>" . Field_text ( 'SaveSenderCanonical', $canonical, 'width:70%' ) . "&nbsp;" . imgtootltip ( 'ed_delete.gif', '{delete}', "USER_CANONICAL_DELETE()" ) . "</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><input type=button value='{edit}&nbsp;&raquo;' OnClick=\"javascript:USER_CANONICAL_ADD();\"></td>
		</tr>
</table>
</div>
</td>
</tr>
</table>";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_CANONICAL_JS() {
	$page = CurrentPageName ();
	$server = $_GET["smtp-sasl"];
	$uid = $_GET["uid"];
	$tpl = new templates ( );
	$title = $tpl->_ENGINE_parse_body ( '{sender_canonical}' );
	
	$html = "
	function USER_CANONICAL_LOAD(){
		YahooWin4('550','$page?sender-email-popup=yes&uid=$uid');
	
	}
	
var X_USER_CANONICAL_DELETE= function (obj) {
	var results=obj.responseText;
	if (results.length>0){
		alert(results);
	}
	USER_CANONICAL_LOAD();
}	
	
function USER_CANONICAL_DELETE(){
	m_userid=uid;
	var SenderCanonical;
	var XHR = new XHRConnection();
	XHR.appendData('DeleteSenderCanonical','$uid');
	SenderCanonical=document.getElementById('SaveSenderCanonical').value;
	document.getElementById('SaveSenderCanonical').value='';
	XHR.appendData('DeleteSenderCanonicalValue',SenderCanonical);
	document.getElementById('canonical_div').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait-clock.gif'></center></div>\";
	XHR.sendAndLoad('domains.edit.user.php', 'GET',X_USER_CANONICAL_DELETE);		
}

function USER_CANONICAL_ADD(){
	var SenderCanonical=document.getElementById('SaveSenderCanonical').value;
	var XHR = new XHRConnection();
	XHR.appendData('SaveSenderCanonical',SenderCanonical);
	XHR.appendData('uid','$uid');	
	document.getElementById('canonical_div').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait-clock.gif'></center></div>\";
	XHR.sendAndLoad('$page', 'GET',X_USER_CANONICAL_DELETE);	
	
}
	
	USER_CANONICAL_LOAD();";
	
	echo $html;

}

function USER_TRANSPORT_SALS_JS() {
	$page = CurrentPageName ();
	$server = $_GET["smtp-sasl"];
	$uid = $_GET["uid"];
	$tpl = new templates ( );
	$title = $tpl->_ENGINE_parse_body ( '{AUTH_SETTINGS}' );
	
	$html = "
	function USER_TRANSPORT_SASL_LOAD(){
		YahooWin4('450','$page?smtp-sasl-popup=$server&uid=$uid');
	
	}
	
var x_USER_TRANSPORT_SASL_SAVE=function(obj){
	  var results=trim(obj.responseText);
	  if(results.length>0){alert(results);} 
      USER_TRANSPORT_SASL_LOAD();
	}		
	
	function USER_TRANSPORT_SASL_SAVE(){
		var sasl_username=document.getElementById('sasl_username').value;
		var sasl_password=document.getElementById('sasl_password').value;
 		var XHR = new XHRConnection();
      	XHR.appendData('sasl_username',document.getElementById('sasl_username').value);   
      	XHR.appendData('sasl_password',document.getElementById('sasl_password').value);   
     	XHR.appendData('sasl_server','$server');  
	 	XHR.appendData('uid','$uid');        
	 	document.getElementById('sasl_div').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait-clock.gif'></center></div>\";
      	XHR.sendAndLoad('$page', 'GET',x_USER_TRANSPORT_SASL_SAVE);       		
	}
	
	USER_TRANSPORT_SASL_LOAD();
	";
	
	echo $html;

}

function USER_TRANSPORT_SALS_SAVE() {
	
	$smtp_sasl_password_maps = new smtp_sasl_password_maps ( );
	if (! $smtp_sasl_password_maps->add ($_GET["sasl_server"], $_GET["sasl_username"], $_GET["sasl_password"])){
		echo "ERROR: $smtp_sasl_password_maps->ldap_infos\nLine: " . __LINE__ . "\nPage: " . basename ( __FILE__ ) . "\n";
	}

}

function USER_TRANSPORT_SALS_POPUP() {
	
	$sasl = new smtp_sasl_password_maps ( );
	$usernamep = $sasl->smtp_sasl_password_hash [$_GET["smtp-sasl-popup"]];
	if (preg_match ( "#(.+?):(.+)#", $usernamep, $re )) {
		$username = $re [1];
		$password = $re [2];
	}
	
	$html = "
	<H1>{AUTH_SETTINGS}</H1>
	<strong style='font-size:13px;font-weight:normal'>{$_GET["smtp-sasl-popup"]}::{AUTH_SETTINGS}</strong><br>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/inboux-out-128.png'></td>
		<td valign='top'>
		<div id='sasl_div'>
			<table style='width:100%' class=table_form>
				<tr>
					<td valign='top' class=legend nowrap>{username}:</td>
					<td valign='top'>" . Field_text ( 'sasl_username', $username ) . "</td>
				</tr>
				<tr>
					<td valign='top' class=legend nowrap>{password}:</td>
					<td valign='top'>" . Field_password ( 'sasl_password', '******' ) . "</td>
				</tr>		
				<tr>
					<td colspan=2 ALIGN='RIGHT'>
					<hr>
					<input type='button' OnClick=\"javascript:USER_TRANSPORT_SASL_SAVE();\" value='{edit}&nbsp;&raquo;'>
					</td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	</table>";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function USER_TRANSPORT_SAVE() {
	$domain = new DomainsTools ( );
	$line = $domain->transport_maps_implode ($_GET["relay_address"], $_GET["relay_port"], null, $_GET["MX_lookups"]);
	$user = new user ($_GET["uid"]);
	$user->add_transport ( "$line" );
}
function USER_TRANSPORT_DELTE() {
	$user = new user ($_GET["DeleteAlternateSmtpRelay"]);
	$user->del_transport ();
}

function USER_CHANGE_UID() {
	$uid = $_GET["userid"];
	$html = "
	<div class=explain>{change_uid_explain}</div>
	<div id='chuiseriddiv'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend style='font-size:13px'>{original}:</td>
		<td><strong style='font-size:13px;font-weight:normal'>$uid</td>
	</tr>	
	<tr>
		<td class=legend nowrap style='font-size:13px'>{change_uid}:</td>
		<td>" . Field_text ( 'uid_to' ,null,'font-size:13px;padding:5px') . "</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{apply}","ChangeUniqueIdentifier('$uid')")."
		</td>
	</tr>
	</table>
	
	";
	$priv = new usersMenus ( );
	if (! $priv->AllowChangeUserPassword && ! $priv->AllowAddUsers) {
		$html = "<H3>{ERROR_NO_PRIVS}</H3>";
	}
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function USER_CHANGE_UID_SAVE() {
	$uid = $_GET["changeuidFrom"];
	$uidnext = $_GET["changeuidTo"];
	$user = new user ( $uid );
	$array ["uid"] [0] = $uidnext;
	
	$ldap = new clladp ( );
	if (! $ldap->Ldap_modify ( $user->dn, $array )) {
		echo $ldap->ldap_last_error;
		exit ();
	}
	
	$groups = $user->GetGroups ( $uid );
	$hash = $user->Groups_list ( $uid );
	if (is_array ( $hash )) {
		while ( list ( $num, $val ) = each ( $hash ) ) {
			$group = new groups ( $num );
			writelogs ( "Delete user ($uid) from $val", __CLASS__ . '/' . __FUNCTION__, __FILE__, __LINE__ );
			$group->DeleteUserFromThisGroup ( $uid );
			$group->AddUsertoThisGroup ( $uidnext );
		}
	}
	
	$users=new usersMenus();
	if($users->POSTFIX_INSTALLED){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
	}	

}

function js_MAILBOX_REPAIR() {
	$page = CurrentPageName ();
	$uid = $_GET["uid"];
	
	$tpl = new templates ( );
	$c = html_entity_decode ( $tpl->_ENGINE_parse_body ( '{confirm_repair}' ) );
	
	$html = "
	var timerID2  = null;
	var tant1=0;
	var reste1=0;
	
	if(document.getElementById('mailbox_graph')){document.getElementById('mailbox_graph').innerHTML='';}
	YahooWin2(450,'$page?TOOLS_REPAIR=yes&uid=$uid','$uid (repair)',''); 
	
	function repair_mailbox(uid){
		if(confirm('$c')){
			var XHR = new XHRConnection();
    		XHR.appendData('RepairThisMailbox','$uid');
    		XHR.sendAndLoad('$page','GET');
    		cyrdemarre();
			}
		}
		
		


function cyrdemarre(){
   tant1 = tant1+1;
   reste1=10-tant1;
	if (tant1 < 10 ) {                           
      timerID2 = setTimeout(\"cyrdemarre()\",2500);
      } else {
               tant1 = 0;
               if(document.getElementById('mailbox_logs')){
					LoadAjax('mailbox_logs','$page?ShowMbxRepair=$uid');               	
               		cyrdemarre(); 
               	}
               
   }
}		
		
	";
	
	return $html;

}

function js_MAILBOX_DELETE() {
	$page = CurrentPageName ();
	$uid = $_GET["uid"];
	$tpl = new templates ( );
	$mailbox_text = $tpl->_ENGINE_parse_body ( '{delete_this_mailbox_text}' );
	
	$html = "
	
	var delm= function (obj){
		var response=obj.responseText;
		RefreshTab('container-users-tabs');
	}
	
	if(confirm('$mailbox_text')){
			var XHR = new XHRConnection();
    		XHR.appendData('DeleteRealMailBox','$uid');
    		XHR.sendAndLoad('cyrus.index.php','GET',delm);
 			}
		";
	
	echo $html;
}

function TOOLS_REPAIR() {
	$uid = $_GET["uid"];
	$html = "
	
	<div class=explain>{repair_mailbox_infos}</div>
	<center>
	<input type='button' value='{repair_mailbox}&nbsp;&raquo;' 
	style='font-size:16px;padding:5px;margin:15px'
	OnClick=\"javascript:repair_mailbox('$uid');\">
	</center>
	<div id='mailbox_logs'></div>
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function TOOL_SYNC_FIND_MAILBOX() {
	$user = new user ($_GET["uid"]);
	$ou = $user->ou;
	$ldap = new clladp ( );
	$hash = $ldap->UserSearch ( $ou, $_GET["sync_find_user"]);
	
	$html = "
	<div style='width:100%;height:300px;overflow:auto'>
	<strong style='width:12px'>{search_in_orgnization}:$ou</strong>
	<table style='width:100%'>";
	//print_r($hash);
	while ( list ( $num, $ligne ) = each ( $hash ) ) {
		$id = $ligne ["uid"] [0];
		$displayname = $ligne ["displayname"] [0];
		$mail = $ligne ["mail"] [0];
		if (trim ( $mail ) == null) {
			continue;
		}
		$html = $html . "<tr " . CellRollOver () . ">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td style='font-size:11px'>$displayname</td>
			<td style='font-size:11px'>$mail</td>
			<td style='font-size:11px' width=1%><input type='button' OnClick=\"javascript:ExportMailBoxSelect2('$id')\" value='{select}&nbsp;&raquo;'></td>
			</tr>";
	}
	$tpl = new templates ( );
	$html = $html . "</table></div>";
	echo $tpl->_ENGINE_parse_body ( $html );

}

function js_EXPORT_MAILBOX() {
	$page = CurrentPageName ();
	$uid = $_GET["uid"];
	$tpl = new templates ( );
	$html = "
	
	var ExptimerID  = null;
	var Exptant=0;
	var Expreste=0;

function exp_demarre(){
   Exptant = Exptant+1;
   Expreste=10-Exptant;
	if (Exptant < 10 ) {                           
      	ExptimerID = setTimeout(\"exp_demarre()\",2000);
      	if(document.getElementById('wait')){
      		document.getElementById('wait').innerHTML='';
      		}
     	 } 
     	 else {
            Exptant = 0;
            exp_check();
            exp_demarre();
      		}
	}

	
	var x_ScannLogs=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('EXPORT_LOGS_IMAPSYNC').innerHTML=tempvalue;
      }	

function exp_check(){
	if(!document.getElementById('EXPORT_LOGS_IMAPSYNC')){return;}
		
		if(document.getElementById('export_launched').value=='1'){
			var mailbox_to=document.getElementById('mailbox_to').value;
			var mailbox_from=document.getElementById('mailbox_from').value;
			document.getElementById('wait').innerHTML='<img src=\"img/wait.gif\">';
			var XHR = new XHRConnection();
			XHR.appendData('imapsync_events',mailbox_from);
			XHR.appendData('t',mailbox_to);
			XHR.sendAndLoad('$page', 'GET',x_ScannLogs); 
			
			}
	}
	
	
	var x_ExportMailboxDo=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('export_launched').value='1';
      exp_check();
      }
  	
	
	
	function ExportMailboxDo(){
		
		var delete_messages=document.getElementById('delete_messages').value;
		var mailbox_to=document.getElementById('mailbox_to').value;
		var mailbox_from=document.getElementById('mailbox_from').value;
		var XHR = new XHRConnection();
      	XHR.appendData('LaunchExportOperation','yes');
     	XHR.appendData('mailbox_from',mailbox_from);
      	XHR.appendData('mailbox_to',mailbox_to);
      	XHR.appendData('delete_messages',delete_messages);
      	XHR.sendAndLoad('$page', 'GET',x_ExportMailboxDo);       
		}
	

	if(document.getElementById('mailbox_graph')){document.getElementById('mailbox_graph').innerHTML='';}
	YahooWin2(450,'$page?TOOLS_SYNC=yes&uid=$uid','$uid (sync)','');
	exp_demarre(); 
	
	function ExportFindUser(e){
		if(checkEnter(e)){
		ExpFindUser();
		}
	}
	
	function ExportMailBoxSelect2(uid){
		var orgin=document.getElementById('export_from_uid').value;
		LoadAjax('imapsync_logs','$page?sync_next_user='+uid+'&uid='+orgin);
	}
	
	function ExpFindUser(){
	var uid=document.getElementById('export_from_uid').value;
	var pattern=document.getElementById('sync_find_user').value;
	LoadAjax('imapsync_logs','$page?sync_find_user='+ pattern+'&uid='+uid);
	}
	
	";
	echo $html;

}

function TOOL_SYNC_STEP2() {
	$uid = $_GET["uid"];
	$next_uid = $_GET["sync_next_user"];
	
	$html = "
	<input type='hidden' id='export_launched' value='0'>
	<input type='hidden' id='mailbox_from' value='$uid'>
	<input type='hidden' id='mailbox_to' value='$next_uid'>
	<table style='width:100%;padding:3px;border:1px solid #CCCCCC'>
		<tr>
		<td colspan=4 align='right'></td>
	<tr>
	<tr>
		<td colspan=4 align='center' style='border-bottom:1px dotted #CCCCCC'><strong style='font-size:12px'>{export_mailbox}</strong></td>
	<tr>
		<td align='center'><img src='img/mailbox.gif'></td>
		<td align='center' width=1%><img src='img/fw_bold.gif'></td>
		<td align='center'><img src='img/mailbox.gif'></td>
	</tr>
	<tr>
		<td align='center'><strong>$uid</strong></td>
		<td align='center' width=1%>&nbsp;</td>
		<td align='center'><strong>$next_uid</strong></td>
	</tr>	
	</table>
	<br>
	<table style='width:100%;padding:3px;border:1px solid #CCCCCC'>
	<tr>
		<td class=legend nowrap>{delete_messages}:</td>
		<td>" . Field_numeric_checkbox_img ( 'delete_messages', 0, "{delete_messages_text}" ) . "</td>
	</tr>
	<tr>
		<td class=legend nowrap><span id='wait'></span></td>
		<td align='right'><input type='button' OnClick=\"javascript:ExportMailboxDo()\" value='{launch}&nbsp;&raquo;&raquo;'></td>
	</tr>	
	<tr>
		<td colspan=2 align='center'>
		<div id='EXPORT_LOGS_IMAPSYNC' style='width:97%;height:200px;overflow:auto;border:1px dotted #CCCCCC;padding:3px;margin:3px;background-color:white'></div>
		</td>
	</tr>
	</table>
	
	</div>
	
	";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}

function TOOL_SYNC_LAUNCH() {
	$sock = new sockets ( );
	$sock->getfile ( 'CheckDaemon' );
	$sock->getfile ( "MailBoxLocalSync:F={$_GET["mailbox_from"]};T={$_GET["mailbox_to"]};D={$_GET["delete_messages"]};A=perform" );
	exit ();
}

function TOOL_SYNC_EVENTS() {
	
	$sock = new sockets ( );
	$file = $sock->getfile ( "MailBoxLocalSyncLogs:F={$_GET["imapsync_events"]};T={$_GET["t"]}" );
	$datas = explode ( "\n", $file );
	$datas = array_reverse ( $datas, false );
	writelogs ( "Loading " . count ( $datas ) . " lines", __FUNCTION__, __FILE__, __LINE__ );
	$count = 0;
	while ( list ( $num, $val ) = each ( $datas ) ) {
		writelogs ( "Loading $val", __FUNCTION__, __FILE__, __LINE__ );
		$val = htmlentities ( $val );
		$count = $count + 1;
		$html = $html . "<div style='color:black;margin-bottom:3px;text-align:left'><code>$val</code></div>";
		if ($count > 100) {
			break;
		}
	
	}
	
	echo $html;

}

function TOOLS_SYNC() {
	$uid = $_GET["uid"];
	$user = new usersMenus ( );
	
	$content = Field_hidden ( 'export_from_uid', $uid ) . "

<table style='width:100%'>
<tr>
	<td class=legend nowrap>{sync_find_user}:</td>
	<td>" . Field_text ( 'sync_find_user', null, null, null ) . "</td>
	<td><input type='button' OnClick=\"javascript:ExpFindUser();\" value='{search}&nbsp;&raquo;'></td>
	</tr>
</table>

";
	if (! $user->mailsync_installed) {
		$content = Paragraphe ( 'add-remove-64.png', '{imapsync_not_installed}', '{imapsync_not_installed_text}', 'setup.index.php', null, 290 );
	}
	
	$html = "
	<H1>$uid {export_mailbox}</H1>
	<p class=caption>{export_mailbox_text}</p>
	<center>
	$content
	</center>
	<div id='imapsync_logs'></div>";
	
	$tpl = new templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );
}

function TOOLS_IMPORT_LAUNCH() {
	$uid = $_GET["uid"];
	$sock = new sockets ( );
	$sock->getfile ( "CheckDaemon" );
	$sock->getfile ( "MailBoxRemoteSync:$uid" );
}

function TOOLS_IMPORT_TESTS() {
	$uid = $_GET["uid"];
	$user = new usersMenus ( );
	$sock = new sockets ( );
	$conf = $sock->GET_INFO ( "{$uid}ImportMailBoxData" );
	$ini = new Bs_IniHandler ( );
	$ini->loadString ( $conf );
	
	if ($ini->_params ["INFO"] ["use_ssl"] == yes) {
		$dn = "{$ini->_params["INFO"]["remote_imap_server"]}:993/imap/ssl/novalidate-cert";
	} else {
		$dn = "{$ini->_params["INFO"]["remote_imap_server"]}:143";
	}
	
	$mbox = imap_open ( "{{$dn}}", $ini->_params ["INFO"] ["remote_imap_username"], $ini->_params ["INFO"] ["remote_imap_password"]);
	
	if (! $mbox) {
		
		$error = imap_last_error ();
		echo "
	<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/danger24.png'></td>
			<td><strong style='font-size:11px'>$error</td>
		</tr>
	</table>
	";
		return null;
	
	}
	
	$folders = imap_listmailbox ( $mbox, "{{$dn}}", "*" );
	if (! $folders) {
		$error = imap_last_error ();
		echo "
	<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/danger24.png'></td>
			<td><strong style='font-size:11px'>$error</td>
		</tr>
	</table>
	";
		imap_close ( $mbox );
		return null;
	}
	
	imap_close ( $mbox );
	$countfolder = count ( $folders );
	$tpl = new templates ( );
	
	echo $tpl->_ENGINE_parse_body ( "
	<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/ok24.png'></td>
			<td><strong style='font-size:11px'>{success} $countfolder {folder}(s)</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:LauchMbxImport();\" value='{import_now}&nbsp;&raquo;'>
		</tr>
	</table>
	" );

}

function TOOLS_IMPORT_LOGS() {
	
	$uid = $_GET["uid"];
	$file = "ressources/logs/imap_import/$uid.log";
	$datas = @file_get_contents ( $file );
	$tpl = new templates ( );
	echo "	<H1>" . $tpl->_ENGINE_parse_body ( "{see_events}" ) . "</H1>
	<textarea style='width:100%;height:300px;border:1px solid #CCCCCC;font-size:10px'>$datas</textarea>";

}

function TOOLS_IMPORT_SAVE() {
	$uid = $_GET["uid"];
	$ini = new Bs_IniHandler ( );
	$ini->_params ["INFO"] ["remote_imap_server"] = $_GET["remote_imap_server"];
	$ini->_params ["INFO"] ["remote_imap_username"] = $_GET["remote_imap_username"];
	$ini->_params ["INFO"] ["remote_imap_password"] = $_GET["remote_imap_password"];
	$ini->_params ["INFO"] ["use_ssl"] = $_GET["use_ssl"];
	$sock = new sockets ( );
	$sock->SaveConfigFile ( $ini->toString (), "{$uid}ImportMailBoxData" );
	$tpl = new templates ( );
	echo html_entity_decode ( $tpl->_ENGINE_parse_body ( '{success}' ) );
}

function TOOLS_REPAIR_OP() {
	$sock = new sockets ( );
	$datas = $sock->getFrameWork ( "cmd.php?repair-mailbox={$_GET["RepairThisMailbox"]}" );

}

function TOOLS_REPAIR_LOGS() {
	$uid = $_GET["ShowMbxRepair"];
	if (! file_exists ( "ressources/logs/cyr.repair.$uid" )) {
		echo "<p>wait...</p>";
	} else {
		echo "<textarea style='width:100%;height:300px'>" . file_get_contents ( "ressources/logs/cyr.repair.$uid" ) . "</textarea>";
	
	}
}

function SaveAllowedSMTP() {
	$user = new user ($_GET["uid"]);
	$user->AllowedSMTPTroughtInternet = $_GET["AllowedSMTPTroughtInternet"];
	if ($user->add_user ()) {
		$tpl = new templates ( );
		echo html_entity_decode ( $tpl->_ENGINE_parse_body ( "\n{AllowedSMTPTroughtInternet}\n{success}:\n" . $_GET["uid"] ) );
	}

}

function AJAX_COMPUTER_OCS() {
	
	$install = Paragraphe ( "software-deploy-64.png", "{OCS_DEPLOY_WINDOWS}", "{OCS_DEPLOY_WINDOWS_TEXT}", "javascript:Loadjs('ocs.ng.php?deploy-js={$_GET["userid"]}')" );
	$cmp = new computers ($_GET["userid"]);
	$ocs = new ocs ($cmp->ComputerMacAddress );
	$ocsinfos = $ocs->BuildFirstInfos ();
	if($ocsinfos<>null){$install=null;}
	$html = "
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>$install
		</td>
		<td valign='top' width=99%><div style='width:99%;height:350px;overflow:auto'>$ocsinfos</div></td>
	</tr>
	</table>
                       	
	";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( $html );

}

function USER_SAFEBOX() {
	$html = "<div id='safebox'></div>
	<script>
		Loadjs('domains.edit.user.safebox.php?uid={$_GET["userid"]}&main=yes');
	</script>
	
	
	";
	return $html;
}

function USER_PRIVILEGES() {
	$GLOBALS["DEBUG_PRIVS"]=true;
	$users = new usersMenus();
	$AllowEditOuSecurity = "status_critical.gif";
	$AsOrgPostfixAdministrator = "status_critical.gif";
	$AsQuarantineAdministrator = "status_critical.gif";
	$AsMailManAdministrator = "status_critical.gif";
	$AsOrgStorageAdministrator = "status_critical.gif";
	$AsMessagingOrg = "status_critical.gif";
	$AllowAddUsers = "status_critical.gif";
	$AsDansGuardianGroupRule = "status_critical.gif";
	$AsOrgAdmin = "status_critical.gif";
	$AsInventoryAdmin = "status_critical.gif";
	$AllowChangeAntiSpamSettings = "status_critical.gif";
	$AllowChangeUserPassword = "status_critical.gif";
	$AllowFetchMails = "status_critical.gif";
	$AllowChangeUserKas = "status_critical.gif";
	$AllowEditAliases = "status_critical.gif";
	$AllowChangeMailBoxRules = "status_critical.gif";
	$AllowSenderCanonical = "status_critical.gif";
	$AllowOpenVPN = "status_critical.gif";
	$AllowDansGuardianBanned = "status_critical.gif";
	$AllowXapianDownload = "status_critical.gif";
	$AllowManageOwnComputers = "status_critical.gif";
	$AllowEditAsWbl = "status_critical.gif";
	$AllowChangeDomains= "status_critical.gif";
	$OverWriteRestrictedDomains= "status_critical.gif";
	$AsWebMaster= "status_critical.gif";
	
	if ($users->AllowChangeAntiSpamSettings) {$AllowChangeAntiSpamSettings = "status_ok.gif";}
	if ($users->AllowChangeUserPassword) {$AllowChangeUserPassword = "status_ok.gif";}
	if ($users->AllowFetchMails) {$AllowFetchMails = "status_ok.gif";}
	if ($users->AllowChangeUserKas) {$AllowChangeUserKas = "status_ok.gif";}
	if ($users->AllowChangeMailBoxRules) {$AllowChangeMailBoxRules = "status_ok.gif";}
	if ($users->AllowSenderCanonical) {$AllowSenderCanonical = "status_ok.gif";}
	if ($users->AllowOpenVPN) {$AllowOpenVPN = "status_ok.gif";}
	if ($users->AllowDansGuardianBanned) {$AllowDansGuardianBanned = "status_ok.gif";}
	if ($users->AllowXapianDownload) {$AllowXapianDownload = "status_ok.gif";}
	if ($users->AllowEditAsWbl) {$AllowEditAsWbl = "status_ok.gif";}
	if ($users->AllowChangeDomains) {$AllowChangeDomains = "status_ok.gif";}
	if ($users->OverWriteRestrictedDomains) {$OverWriteRestrictedDomains = "status_ok.gif";}
	if ($users->AsWebMaster) {$AsWebMaster = "status_ok.gif";}			
	
	
	if($users->AllowEditOuSecurity) {$AllowEditOuSecurity = "status_ok.gif";}
	if($users->AsOrgPostfixAdministrator) {$AsOrgPostfixAdministrator = "status_ok.gif";}
	if($users->AsQuarantineAdministrator) {$AsQuarantineAdministrator = "status_ok.gif";}
	if($users->AsMailManAdministrator) {$AsMailManAdministrator = "status_ok.gif";}
	if($users->AsOrgStorageAdministrator) {$AsOrgStorageAdministrator = "status_ok.gif";}
	if($users->AsMessagingOrg) {$AsMessagingOrg = "status_ok.gif";}
	if($users->AllowAddUsers) {$AllowAddUsers = "status_ok.gif";}
	if($users->AsDansGuardianGroupRule) {$AsDansGuardianGroupRule = "status_ok.gif";}
	if($users->AsOrgAdmin) {$AsOrgAdmin = "status_ok.gif";}
	if($users->AsInventoryAdmin){$AsInventoryAdmin = "status_ok.gif";}
	if($users->AllowEditAliases){$AllowEditAliases = "status_ok.gif";}
	
	
	
	$group_allow = "<H3>{groups_allow}</H3><br>
		<table style='width:100%' class=table_form>
		
			<tr>
				<td align='right'><strong style='font-size:13px;font-weight:normal'>{AllowAddUsers}:</td><td width=1%><img src='img/$AllowAddUsers'></td>
			</tr>
			<tr>
				<td align='right'><strong style='font-size:13px;font-weight:normal'>{AsDansGuardianGroupRule}:</td><td width=1%><img src='img/$AsDansGuardianGroupRule'></td>
			</tr>			
		</table>
";
	
	$org_allow = "<H3>{organization_allow}</H3><br>
<table style='width:100%' class=table_form>	
	<tr><td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowEditOuSecurity}:</td><td width=1%><img src='img/$AllowEditOuSecurity'></td></tr>
	<tr><td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsInventoryAdmin}:</td><td width=1%><img src='img/$AsInventoryAdmin'></td></tr>	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsOrgPostfixAdministrator}:</td>
		<td width=1%><img src='img/$AsOrgPostfixAdministrator'></td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsQuarantineAdministrator}:</td>
		<td width=1%><img src='img/$AsQuarantineAdministrator'></td>
	</tr>
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{OverWriteRestrictedDomains}:</td>
		<td width=1%><img src='img/$OverWriteRestrictedDomains'></td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsMailManAdministrator}:</td>
		<td width=1%><img src='img/$AsMailManAdministrator'></td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsOrgStorageAdministrator}:</td>
		<td width=1%><img src='img/$AsOrgStorageAdministrator'></td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsWebMaster}:</td>
		<td width=1%><img src='img/$AsWebMaster'></td>
	</tr>
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsMessagingOrg}:</td>
		<td width=1%><img src='img/$AsMessagingOrg'></td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowChangeDomains}:</td>
		<td width=1%><img src='img/$AllowChangeDomains'></td>
	</tr>	
	
	<tr>
		<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AsOrgAdmin}:</td>
		<td width=1%><img src='img/$AsOrgAdmin'></td>
	</tr>	
</table>";
	

	
	
	$user_allow = "<H3>{users_allow}</H3><br>
					<table style='width:100%' class=table_form>
																	
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowChangeAntiSpamSettings}:</td>
							<td width=1%><img src='img/$AllowChangeAntiSpamSettings'></td>
						</tr>											
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowChangeUserPassword}:</td>
							<td width=1%><img src='img/$AllowChangeUserPassword'></td>
						</tr>
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowFetchMails}:</td>
							<td width=1%><img src='img/$AllowFetchMails'></td>
						</tr>
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowChangeUserKas}:</td>
							<td width=1%><img src='img/$AllowChangeUserKas'></td>
						</tr>												
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowEditAliases}:</td>
							<td width=1%><img src='img/$AllowEditAliases'></td>
						</tr>
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowChangeMailBoxRules}:</td>
							<td width=1%><img src='img/$AllowChangeMailBoxRules'></td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowSender_canonical}:</td>
							<td width=1%><img src='img/$AllowChangeMailBoxRules'></td>
						</tr>
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowOpenVPN}:</td>
							<td width=1%><img src='img/$AllowOpenVPN'></td>
						</tr>
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowDansGuardianBanned}:</td>
							<td width=1%><img src='img/$AllowDansGuardianBanned'></td>
						</tr>
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowXapianDownload}:</td>
							<td width=1%><img src='img/$AllowXapianDownload'></td>
						</tr>																									
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowManageOwnComputers}:</td>
							<td width=1%><img src='img/$AllowManageOwnComputers'></td>
						</tr>						
						
						
						<tr>
							<td align='right' nowrap><strong style='font-size:13px;font-weight:normal'>{AllowEditAsWbl}:</td>
							<td width=1%><img src='img/$AllowEditAsWbl'></td>
						</tr>									
					</table>";
	
	$tpl = new templates ( );
	return $tpl->_ENGINE_parse_body ( "$user_allow$group_allow$org_allow" );

}

function COMPUTER_CHECK_MAC(){
	$tpl=new templates();
	$mac=$_GET["ComputerMacAddressFindUid"];
	$comp=new computers();
	$uid=trim($comp->ComputerIDFromMAC($mac));
	if($uid<>null){
		if($uid<>$_GET["userid"]){
			echo $tpl->_ENGINE_parse_body(imgtootltip("status_warning.gif","{this_mac_address_is_already_used_by}:$uid"));
			return;
		}
	}
	if (!IsPhysicalAddress($_GET["ComputerMacAddressFindUid"])) {
		echo $tpl->_ENGINE_parse_body(imgtootltip("status_warning.gif","{WARNING_MAC_ADDRESS_CORRUPT}"));
		return;
	}
	
	echo "<img src='img/icon_ok.gif'>";	
}

function ZARAFA_MAILBOX_INFOS_JS(){
	$page=CurrentPageName();
	$tpl=new templates();
	$u=new user($_GET["uid"]);
	$title=$tpl->_ENGINE_parse_body("{mailbox_infos}");
	echo "YahooWin4('550','$page?ZARAFA_MAILBOX_INFOS_POPUP=yes&uid={$_GET["uid"]}&userid={$_GET["uid"]}','$title::$u->mail')";
	
}
function ZARAFA_MAILBOX_INFOS_POPUP(){
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("zarafa.php?mbx-infos={$_GET["uid"]}")));
	
		$html[]="
		<div style='height:450px;width:100%;overflow:auto'>
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>&nbsp;</th>
	
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while (list ($num, $ligne) = each ($datas) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	if(preg_match("#(.+?):(.+)#",$ligne,$re)){
		$html[]="<tr class=$classtr>
		<td class=legend>{$re[1]}:</td>
		<td style='font-size:14px;font-weight:bold'>{$re[2]}</td>
		</tr>";
		continue;
	}		
	$ligne=htmlspecialchars($ligne);
	$ligne=str_replace("	","&nbsp;&nbsp;&nbsp;&nbsp;",$ligne);
	$ligne=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$ligne);
	
	if(trim($ligne==null)){continue;}
	$html[]="<tr class=$classtr>
		<td style='font-size:14px;font-weight:bold' colspan=2>$ligne</td>
		</tr>";


	}
	$html[]="</table></div>";
	echo @implode("\n",$html);
}

function zarafaSharedStoreOnly(){
	$zarafaSharedStoreOnly=$_POST["zarafaSharedStoreOnly"];
	if(!is_numeric($zarafaSharedStoreOnly)){$zarafaSharedStoreOnly=0;}
	$u=new user($_POST["uid"]);
	$ldap=new clladp();	
	$upd["zarafaSharedStoreOnly"][0]=$zarafaSharedStoreOnly;
	if(!$ldap->Ldap_modify($u->dn, $upd)){
		echo "zarafaSharedStoreOnly = '$zarafaSharedStoreOnly'\nLDAP ERROR :\nFunction: ".__FUNCTION__."\nPage: ".basename(__FILE__)."\nLine:".__LINE__."\nError:\n".$ldap->ldap_last_error;
		return;
	}	
}

function ZARAFA_DISABLE_FEATURES_SAVE(){
	$zarafaEnabledFeatures=null;
	$zarafaDisabledFeatures=null;
	if($_POST["user_zarafa_enable_imap"]==1){$zarafaEnabledFeatures="imap";}
	if($_POST["user_zarafa_enable_pop3"]==1){$zarafaEnabledFeatures=$zarafaEnabledFeatures." pop3";}

	if($_POST["user_zarafa_enable_imap"]==0){$zarafaDisabledFeatures="imap";}
	if($_POST["user_zarafa_enable_pop3"]==0){$zarafaDisabledFeatures=$zarafaDisabledFeatures." pop3";}	
	
	$u=new user($_POST["uid"]);
	$ldap=new clladp();
	if($zarafaEnabledFeatures==null){
		if(!$ldap->Ldap_del_mod($u->dn, $array["zarafaEnabledFeatures"])){
			echo "zarafaEnabledFeatures = '$zarafaEnabledFeatures'\nzarafaDisabledFeatures = '$zarafaDisabledFeatures'\nLDAP ERROR :\nFunction: ".__FUNCTION__."\nPage: ".basename(__FILE__)."\nLine:".__LINE__."\nError:\n".$ldap->ldap_last_error;
		}
	}
	
	if($zarafaDisabledFeatures==null){
		if(!$ldap->Ldap_del_mod($u->dn, $array["zarafaDisabledFeatures"])){
			echo "zarafaEnabledFeatures = '$zarafaEnabledFeatures'\nzarafaDisabledFeatures = '$zarafaDisabledFeatures'\nLDAP ERROR :\nFunction: ".__FUNCTION__."\nPage: ".basename(__FILE__)."\nLine:".__LINE__."\nError:\n".$ldap->ldap_last_error;
		}
	}	
	
	if($zarafaEnabledFeatures<>null){$upd["zarafaEnabledFeatures"][0]=$zarafaEnabledFeatures;}
	if($zarafaDisabledFeatures<>null){$upd["zarafaDisabledFeatures"][0]=$zarafaDisabledFeatures;}
	if(!$ldap->Ldap_modify($u->dn, $upd)){
		echo "zarafaEnabledFeatures = '$zarafaEnabledFeatures'\nzarafaDisabledFeatures = '$zarafaDisabledFeatures'\nLDAP ERROR :\nFunction: ".__FUNCTION__."\nPage: ".basename(__FILE__)."\nLine:".__LINE__."\nError:\n".$ldap->ldap_last_error;
		return;
	}
	
	
}


?>