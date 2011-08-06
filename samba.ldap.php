<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.kav4samba.inc');
	
	if(!CheckSambaUniqueRights()){die();}
	if($_GET["server_host"]){save();exit;}
	if(isset($_GET["js"])){js();exit;}
	page();
	
	
		
function CheckSambaUniqueRights(){
	$user=new usersMenus();
	if($user->AsArticaAdministrator){return true;}
	if($user->AsSambaAdministrator){return true;}	
}


function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body("{APP_LDAP}");
	$html="YahooWin5('550','$page','$title')";
	echo $html;
}




function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	
	$EnableSambaRemoteLDAP=$sock->GET_INFO("EnableSambaRemoteLDAP");
	if($EnableSambaRemoteLDAP==null){$EnableSambaRemoteLDAP=0;}
	$SambaRemoteLDAPInfos=unserialize(base64_decode($sock->GET_INFO("SambaRemoteLDAPInfos")));
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valing='top' width=1%>
			<img src='img/databases-search-net-128.png'>
		</td>
		<td valign='top'>
		
			<div class=explain>{SAMBA_LDAP_EXTERN_TEXT}</div>
			<div id='MainSambaLDAPConfigDiv'>
			<table style='width:100%'>
			<tr>
				<td class=legend>{enable}:</td>
				<td>". Field_checkbox("EnableSambaRemoteLDAP",1,$EnableSambaRemoteLDAP,"CheckDisbaled()")."</td>
			</tr>			
			<tr>
				<td class=legend>{server_host}:</td>
				<td>". Field_text("server_host",$SambaRemoteLDAPInfos["server_host"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend>{listen_port}:</td>
				<td>". Field_text("server_port",$SambaRemoteLDAPInfos["server_port"],"font-size:13px;padding:3px")."</td>
			</tr>	
			<tr>
				<td class=legend>{ldap_suffix}:</td>
				<td>". Field_text("suffix",$SambaRemoteLDAPInfos["suffix"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend>{ldap_suffix} {group}:</td>
				<td>". Field_text("ldap_group_suffix",$SambaRemoteLDAPInfos["ldap_group_suffix"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend>{ldap_suffix} {users}:</td>
				<td>". Field_text("ldap_user_suffix",$SambaRemoteLDAPInfos["ldap_user_suffix"],"font-size:13px;padding:3px")."</td>
			</tr>	
			<tr>
				<td class=legend>{ldap_suffix} {computers}:</td>
				<td>". Field_text("ldap_machine_suffix",$SambaRemoteLDAPInfos["ldap_machine_suffix"],"font-size:13px;padding:3px")."</td>
			</tr>									
			<tr>
				<td class=legend>{ldap admin}:</td>
				<td>". Field_text("user_dn",$SambaRemoteLDAPInfos["user_dn"],"font-size:13px;padding:3px")."</td>
			</tr>	
			<tr>
				<td class=legend>{password}:</td>
				<td>". Field_password("user_dn_password",$SambaRemoteLDAPInfos["user_dn_password"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend>{ssl}:</td>
				<td>". Field_checkbox("ssl",1,$SambaRemoteLDAPInfos["ssl"])."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'>". button("{apply}","SaveSambaLDAP()")."</td>
			</tr>
		</table>
		</div>	
	</td>
</tr>
</table>		

<script>

	function CheckDisbaled(){
		document.getElementById('server_host').disabled=true;
		document.getElementById('server_port').disabled=true;
		document.getElementById('suffix').disabled=true;
		document.getElementById('ldap_group_suffix').disabled=true;
		document.getElementById('ldap_user_suffix').disabled=true;
		document.getElementById('ldap_machine_suffix').disabled=true;
		document.getElementById('user_dn').disabled=true;
		document.getElementById('user_dn_password').disabled=true;
		document.getElementById('ssl').disabled=true;
		
		if(document.getElementById('EnableSambaRemoteLDAP').checked){
			document.getElementById('server_host').disabled=false;
			document.getElementById('server_port').disabled=false;
			document.getElementById('suffix').disabled=false;
			document.getElementById('ldap_group_suffix').disabled=false;
			document.getElementById('ldap_user_suffix').disabled=false;
			document.getElementById('ldap_machine_suffix').disabled=false;
			document.getElementById('user_dn').disabled=false;
			document.getElementById('user_dn_password').disabled=false;
			document.getElementById('ssl').disabled=false;	
		}	
		
		
	}
	
	function x_SaveSambaLDAP(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		if(document.getElementById('main_config_samba')){RefreshTab('main_config_samba');}else{Loadjs('$page?js=yes');}
	}			
	
	
	function SaveSambaLDAP(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableSambaRemoteLDAP').checked){XHR.appendData('EnableSambaRemoteLDAP',1)}else{XHR.appendData('EnableSambaRemoteLDAP',0);}
		if(document.getElementById('ssl').checked){XHR.appendData('ssl',1)}else{XHR.appendData('ssl',0);}
		XHR.appendData('server_host',document.getElementById('server_host').value);
		XHR.appendData('server_port',document.getElementById('server_port').value);
		XHR.appendData('suffix',document.getElementById('suffix').value);
		XHR.appendData('ldap_group_suffix',document.getElementById('ldap_group_suffix').value);
		XHR.appendData('ldap_user_suffix',document.getElementById('ldap_user_suffix').value);
		XHR.appendData('ldap_machine_suffix',document.getElementById('ldap_machine_suffix').value);
		XHR.appendData('user_dn',document.getElementById('user_dn').value);
		XHR.appendData('user_dn_password',document.getElementById('user_dn_password').value);
		
		document.getElementById('MainSambaLDAPConfigDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';			
		XHR.sendAndLoad('$page', 'GET',x_SaveSambaLDAP);		
		}
	
	CheckDisbaled();
</script>
";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableSambaRemoteLDAP",$_GET["EnableSambaRemoteLDAP"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"SambaRemoteLDAPInfos");
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
	
}

