<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mimedefang.inc');	
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.lvm.org.inc');
	include_once('ressources/class.user.inc');


	
	if(!VerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
if(isset($_GET["groupware-list"])){organization_groupwares_list();exit;}	
if(isset($_GET["ajaxmenu"])){echo "<div id='org_main'>";organization_sections();echo "</div>";exit;}
if(isset($_GET["Tree_group_Add_New"])){Tree_group_Add_New();exit;}
if(isset($_GET["ChangeOrg"])){echo groupslist($_GET["ChangeOrg"]);exit;}
if(isset($_GET["LoadMembers"])){echo LoadMembers();exit();}
if(isset($_GET["LoadMembersNotAffected"])){echo LoadMembersNotAffected();exit;}
if(isset($_GET["DeleteGroup"])){echo DeleteGroup();exit;}
if(isset($_GET["ReloadOrgTable"])){echo groupslist($_GET["ReloadOrgTable"]);exit;}
if(isset($_GET["LoadDomainSettings"])){echo PopUp_DomainTransport();exit;}
if(isset($_GET["AddNewInternetDomain"])){AddNewInternetDomain();exit;}
if(isset($_GET["AddTransportToDomain"])){AddTransportToDomain();exit;}
if(isset($_GET["DeleteInternetDomain"])){DeleteInternetDomain();exit;}
if(isset($_GET["SaveTransportDomain"])){SaveTransportDomain();exit;}
if(isset($_GET["LoadAjaxGroup"])){LoadGroupsAjax($_GET["LoadAjaxGroup"]);exit();}
if(isset($_GET["FillGroupTable"])){FillGroupTable();exit;}
if(isset($_GET["LoadDomainList"])){echo LoadDomainsOu($_GET["LoadDomainList"]);exit();}
if(isset($_GET["AddNewInternetDomainMTA"])){AddNewInternetDomainMTA();exit;}
if(isset($_GET["SaveAddNewInternetDomainMTA"])){SaveAddNewInternetDomainMTA();exit;}
if(isset($_GET["DeleteOU"])){DeleteOU();exit;}
if(isset($_GET["org_section"])){organization_sections();exit;}
if(isset($_GET["js"])){js();exit;}
if(isset($_GET["js-pop"])){popup_tabs();exit;}
if(isset($_GET["ORG_VHOSTS_LIST"])){echo organization_vhostslist($_GET["ORG_VHOSTS_LIST"]);exit;}
if(isset($_GET["finduser-js"])){organization_users_find_member_js();exit;}
if(isset($_GET["finduser-popup"])){organization_users_list();exit;}
if(isset($_GET["finduser"])){organization_users_find_member();exit;}
if(isset($_GET["EnableApacheGroupware"])){EnableApacheGroupware();exit;}
if(isset($_GET["DeleteAllUsersInOu"])){DeleteAllUsersInOu();exit;}
if(isset($_GET["count-de-users"])){COUNT_DE_USERS();exit;}
js();	


function js(){
	if(isset($_GET["encoded"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
	$ou_encoded=base64_encode($_GET["ou"]);
	if(GET_CACHED(__FILE__,__FUNCTION__,"js:$ou_encoded")){return;}
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{organization}:: {$_GET["ou"]}");
	$page=CurrentPageName();
	$prefix=str_replace(".","_",$page);
	$js1=file_get_contents("js/artica_organizations.js");
	$js2=file_get_contents("js/artica_domains.js");
	$LoadSingleOrg="LoadWinORG2('760','$page?js-pop=yes&ou=$ou_encoded','$title');";
	
	if(isset($_GET["in-front-ajax"])){
		$LoadSingleOrg="
		document.getElementById('BodyContent').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		$('#BodyContent').load('$page?js-pop=yes&ou=$ou_encoded');";	
	}
	
	if(isset($_GET["groupwares-in-front-ajax"])){
		$LoadSingleOrg="
		document.getElementById('BodyContent').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		$('#BodyContent').load('$page?org_section=groupwares&SwitchOrgTabs={$_GET["ou"]}&ou={$_GET["ou"]}&mem=yes');";
	}
	
	$start_function="LoadSingleOrg()";
	
	if(isset($_GET["panel"])){
		$start_function="LoadPanel()";
	}
	
	$html="
	var {$prefix}timeout=0;
	
	function LoadSingleOrg(){
		$LoadSingleOrg
		
	}
	
	function LoadPanel(){
		LoadAjax('main-org-panel','$page?js-pop=yes&ou=$ou_encoded');
	}	
	$js1
	$js2
	$start_function;";
	
	
	SET_CACHED(__FILE__,__FUNCTION__,"js:$ou_encoded",$html);
	
	echo $html;
	}
	
function organization_users_find_member_js(){
	if(isset($_GET["encoded"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
	$ou=$_GET["ou"];
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{organization}:: {$_GET["ou"]}");
	$page=CurrentPageName();
	$ou_encoded=base64_encode($ou);
	$start="organization_users_find_member_js_start()";
	if(isset($_GET["in-front-ajax"])){$start="organization_users_find_member_js_InFrontAjax()";}
	
	$html="
	
	function organization_users_find_member_js_InFrontAjax(){
		$('#BodyContent').load('$page?finduser-popup=&ou=$ou_encoded');
		
	}	
	
	function organization_users_find_member_js_start(){
		YahooSearchUser(770,'$page?finduser-popup=&ou=$ou_encoded','$title');
	}
	$start
	";
	echo $html;
	
	
}

function popup(){

$html="
<H1>{organization}:&nbsp;&laquo;{$_GET["ou"]}&raquo;</H1>
<input type='hidden' value='{$_GET["ou"]}' id='ouselected'>
<input type='hidden' value='{delete_ou_text}' id='delete_ou_text'>
<div id='org_main' style='width:100%;height:450px;overflow:auto'>";
$tpl=new templates();
writelogs("Building popup done...",__FUNCTION__,__FILE__);
$page=$tpl->_ENGINE_parse_body($html);
echo $page;
}
	

function LoadGroupsAjax($ou){
	$ldap=new clladp();
	echo $ldap->hash_groups($ou,2);
	}





function FillGroupTable(){
		$gid=$_GET["FillGroupTable"];
		$tpl=new templates();
		$ldap=new clladp();
		$user=new usersMenus();
		$link_edit="PageEditGroup($gid)";
		$count=$count+1;
		$hashGP=$ldap->GroupDatas($gid);
		$groupname=$hashGP["cn"];
		$count_members=count($hashGP["members"]);
		$description=$hashGP["description"];
		$description=nl2br($description);
		$description=str_replace("\n",'',$description);
		
		$edit=imgtootltip('edit.jpg','{edit}',"PageEditGroup($gid)");
		$delete=imgtootltip('x.gif','{delete}:'.$groupname,"DeleteGroup($gid)");
		if($user->AllowAddGroup==false){$edit=null;$link_edit=null;$delete=null;}
		$img=imgtootltip('folder-group-29.jpg',$description,$link_edit);

		$selectOver="OnMouseOver=\"javascript:this.style.backgroundColor='#fcfdd1';\" OnMouseOut=\"javascript:this.style.backgroundColor='#ffffff';\"";
		$html = "
			<table style='width:630px;border:1px dotted #8e8785;margin-left:10px' >
				<tr $selectOver OnClick=\"javascript:LoadMembers('$gid')\">
					<td>&nbsp;</td>
					<td class='caption'>{group}:&laquo;$groupname&raquo;</td>
					<td class='caption'>{members}</td>
					<td class='caption' align='center'>{add}</td>
					<td class='caption' align='center'>{edit}</td>
					<td class='caption' align='center'>{delete}</td>
				</tr>
				<tr $selectOver OnClick=\"javascript:LoadMembers('$gid')\">
					<td width='1%'>$img</td>
					<td width='90%'>". texttooltip($groupname,'{members}',"LoadMembers('$gid')")."</td>
					<td align='center' width='10px'>$count_members</td>
					<td align='center' width='10px'>" . imgtootltip('add-18.gif','{add_member}',"AddMemberIntoGroup('$gid');")."</td>
					<td align='center' width='10px'>$edit</td>
					<td align='center' width='10px'>$delete</td>
				</tr>
				<tr $selectOver>
				<td colspan=6><div id='members_$gid'></div></td>
				</tr>
			</table>
			<br>";
		
		echo $tpl->_ENGINE_parse_body($html);
	
}

function OrgTableNotAffected($ou){
	
	$ldap=new clladp();
	$tpl=new templates();
	$hash_users=$ldap->hash_get_users_Only_ou($ou);
	if(count($hash_users)>0){
		$img=imgtootltip('folder-group-29.jpg','{no_group_affected}','');
		$count_members=count($hash_users);
		$num="{no_group_affected}";
		$html=$html . "<tr>
			<td width=1%>$img</td>
			<td>". texttooltip($num,'{members}',"LoadMembersNotAffected()")."</td>
			<td align='center'>$count_members</td>
			<td align='center'>&nbsp;</td>
			<td align='center'>&nbsp;</td>
			<td align='center'>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><div id='members_not_affected'></div></td>
				<td></td>
				<td></td>
				
				<td>&nbsp;</td>
			</tr>";	
	return  $tpl->_ENGINE_parse_body($html);
	}	
	
}

function Tree_group_Add_New(){
	$group=$_GET["Tree_group_Add_New"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$ldap->AddGroup($group,$ou);
	LoadGroupsAjax($ou);
}
function LoadMembers(){
	$gid=$_GET["LoadMembers"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	if(!is_array($hash["members"])){return null;}
	$html="
	
	<table style='width:400px;margin-left:10px'>";
	while (list ($num, $ligne) = each ($hash["members"]) ){
		$arr=$ldap->UserDatas($num);
		$mail=$arr["mail"];
		$domain=$arr["domainName"];
		$html=$html . "
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><a href=\"javascript:LoadUsersDatas('$num');\">$ligne</a></td>
		<td>$mail</td>
		<td>$domain</td>
		<td>" . imgtootltip('x.gif','{delete}',"javascript:DeleteMember('$num','$gid')")."</td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}



function LoadMembersNotAffected(){
	$ou=$_GET["LoadMembersNotAffected"];
	$ldap=new clladp();
	$hash_users=$ldap->hash_get_users_Only_ou($ou);
	if(!is_array($hash_users)){return null;}
	$html="
	
	<table style='width:400px;margin-left:10px'>";
	while (list ($num, $ligne) = each ($hash_users) ){
		$arr=$ldap->UserDatas($num);
		$mail=$arr["mail"];
		$domain=$arr["domainName"];
		$html=$html . "
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><a href=\"javascript:LoadUsersDatas('$ligne');\">$ligne</a></td>
		<td>$mail</td>
		<td>$domain</td>
		<td>" . imgtootltip('x.gif','{delete}',"javascript:DeleteMember('$ligne','0')")."</td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
	
}

	
function PopUp_DomainTransport(){
     		$ldap=new clladp();
     		$tpl=new templates();
     		$domain=$_GET["LoadDomainSettings"];
     		$ou=$_GET["LoadDomainSettingsOu"];
     		
		$hash_transport=$ldap->hash_load_transport();


		if(isset($hash_transport[$domain])){
			
			$service=array("smtp"=>"smtp","relay"=>"relay","lmtp"=>"lmtp");	
			$m=new DomainsTools($hash_transport[$domain]);
			$transport="
			<fieldset><legend>$domain {relay} </legend>
			<span style='font-size:9px;'><i>{relay explain}</i></span>
			<table style='width:100%'>
				<tr>
					<td width=1% valign='top'><img src='img/alias-64.gif'></td>
					<td valign='top'>
						<table style='width:100%'>
						<tr>
							<td align='right'><strong>{transport_ip}:</td>" . Field_text('transport_ip',$m->transport_ip) ."</td>
						</tr>
						<tR>
							<td align='right'><strong>{transport_type}:</td>" . Field_array_Hash($service,'transport_type',$m->transport_type,null,null,0,'width:99%') ."</td>				
						</tr>
						<tr>
							<td align='right'><strong>{transport_port}:</td>" . Field_text('transport_port',$m->transport_port) ."</td>
						</tr>	
						<tr>
							<td align='right' colspan=2><input type='button' value='{label_edit_transport}' OnClick=\"javascript:SaveTransportDomain('$domain','$ou');\"></td>
						</tr>									
						</table>
					</td>
				</tr>
			</table>
			</fieldset>";
			}else{
				$transport="<fieldset><legend>$domain {relay} </legend><img src='img/alias-64.gif' align='left' style='margin:5px'>
				<span style='font-size:9px;'><i>{relay explain}<br>{relay explain 2}</i></span>
				<br><center>
					<input type='hidden' id='relay explain 3' value=\"{relay explain 3}\">
					<input type='button' value='{label_add_transport}' OnClick=\"javascript:AddTransportToDomain('$domain','$ou','$ldap->suffix');\">
				</center>
				</fieldset>
				";
				
			}
     	
     		
		return $tpl->_ENGINE_parse_body($transport);      	
     }	

function AddTransportToDomain(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AllowChangeDomains==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$domain=$_GET["AddTransportToDomain"];
	$ou=$_GET["ou"];
	$ldap=new clladp();	
	$tpl=new templates();
	$tool=new DomainsTools();
	$line=$tool->transport_maps_implode($_GET["transport_ip"],'25','smtp');
	$ldap->AddDomainTransport($ou,$domain,$_GET["transport_ip"],'25',$_GET["transport_type"],'smtp');	
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;return;}
}
	
	
function SaveAddNewInternetDomainMTA(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AllowChangeDomains==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}			
	
	$domain=$_GET["domain_name"];
	$ou=$_GET["SaveAddNewInternetDomainMTA"];

	$ldap=new clladp();
	$ldap->AddDomainTransport($ou,$domain,$_GET["transport_ip"],$_GET["transport_port"],$_GET["transport_type"]);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;return;}
	
	}
	

function SaveTransportDomain(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AllowChangeDomains==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$domain=$_GET["SaveTransportDomain"];
	$ou=$_GET["ou"];
	if($_GET["transport_port"]==null){$_GET["transport_port"]=25;}
	if($_GET["transport_type"]==null){$_GET["transport_type"]="smtp";}
	$tool=new DomainsTools();
	$ldap=new clladp();
	$ldap->AddDomainTransport($ou,$domain,$_GET["transport_ip"],$_GET["transport_port"],$_GET["transport_type"]);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;return;}
	
	
	}

function AddNewInternetDomainMTA(){
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$service=array("smtp"=>"smtp","relay"=>"relay","lmtp"=>"lmtp");	
	$html="<div style='padding:5px;margin:5px'>
	<H4>{add_new_domain}</H4>
	<p>{add_new_domain_mta_text}</p>
	<form name='ffm1DOMAIN'>
	<input type='hidden' name='SaveAddNewInternetDomainMTA' value='$ou'>
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap><strong>{domain_name}:</td>
	<td >" .Field_text('domain_name',null) . "</td>
	</tr>
	<tr>
		<td align='right'><strong>{transport_ip}:</td>" . Field_text('transport_ip',$m->transport_ip) ."</td>
	</tr>
	<tR>
		<td align='right' nowrap><strong>{transport_type}:</td>" . Field_array_Hash($service,'transport_type',$m->transport_type,null,null,0,'width:99%') ."</td>				
	</tr>
	<tr>
		<td align='right' nowrap><strong>{transport_port}: </td>" . Field_text('transport_port',$m->transport_port) ."</td>
	</tr>	
	<tr>
		<td align='right' colspan=2><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1DOMAIN','$page',true);ReloadOrgTable('$ou');\"></td>
	</tr>				
	</table>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function DeleteOU(){
	$user=new usersMenus();
	if($user->AsArticaAdministrator==true){
		$ldap=new clladp();
		$ldap->ldap_delete("ou={$_GET["DeleteOU"]},dc=organizations,$ldap->suffix",true);
	}
	
}

function popup_tabs(){
	$_GET["ou"]=base64_decode($_GET["ou"]);
	if(GET_CACHED(__FILE__,__FUNCTION__,"js:{$_GET["ou"]}")){return null;}
	$sock=new sockets();
	
	$page=CurrentPageName();
	$lvm_g=new lvm_org($_GET["ou"]);
	$usersmenus=new usersMenus();
	$user=$usersmenus;	
	$ldap=new clladp();
	
	
	if(!isset($_GET["ou"])){
		if(isset($_COOKIE["SwitchOrgTabs"])){$_GET["SwitchOrgTabs"]=$_COOKIE["SwitchOrgTabs"];}
		if(isset($_COOKIE["SwitchOrgTabsOu"])){$_GET["ou"]=$_COOKIE["SwitchOrgTabsOu"];}
		}
	
	$usersmenus->LoadModulesEnabled();
	if(!isset($_GET["SwitchOrgTabs"])){$_GET["tab"]=0;};
	$time=time();
	$array["management"]='{management}';
	$array["users"]="{users} (<span id='count-user-$time'></span>)";
	
	
	writelogs("AseMailCampaignsAdmin=$usersmenus->AseMailCampaignsAdmin",__FUNCTION__,__FILE__,__LINE__);
	if($usersmenus->EMAILRELAY_INSTALLED){
		if($sock->GET_INFO("EnableInterfaceMailCampaigns")==1){
		 if($usersmenus->AseMailCampaignsAdmin){
			$array["emailings"]="{email_campaigns}";
			}
		}
	}	
	
	
	if($usersmenus->AsOrgAdmin){
		$ShowApacheGroupware=$sock->GET_INFO("ShowApacheGroupware");
		if($ShowApacheGroupware==null){$ShowApacheGroupware=1;}
		$array["groupwares"]='{groupwares}';
		if(count($lvm_g->disklist)>0){$array["storage"]='{storage}';}
	}
	
	if($user->POSTFIX_INSTALLED){
		$sock=new sockets();
		if($user->AsMessagingOrg){
			$array["postfix"]='{messaging}';
		}

		if($sock->GET_INFO("EnablePostfixMultiInstance")==1){
			if($user->AsOrgPostfixAdministrator){$array["postfix-multi"]='{messaging_servers}';}
		}
		
		
	}
	
	$ou_encrypted=base64_encode("{$_GET["ou"]}");
	
	if($usersmenus->EnableManageUsersTroughActiveDirectory){
		unset($array["groupwares"]);
	}
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="postfix-multi"){
			$a[]="<li><a href=\"domains.postfix.multi.php?org={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";	
			continue;
		}
		
		if($num=="emailings"){
			$a[]="<li><a href=\"domains.emailings.php?popup=yes&ou=$ou_encrypted\"><span>$ligne</span></a></li>\n";	
			continue;
		}	

		if($num=="users"){
			$a[]="<li><a href=\"$page?org_section=$num&SwitchOrgTabs=$ou_encrypted&ou=$ou_encrypted&mem=yes\"><span>$ligne</span></a></li>\n";	
			continue;
		}		
		
		$a[]="<li><a href=\"$page?org_section=$num&SwitchOrgTabs={$_GET["ou"]}&ou={$_GET["ou"]}&mem=yes\"><span>$ligne</span></a></li>\n";
			
		}	
	
	
	$html="
	<div id='org_main' style='background-color:white;width:100%;height:590px;overflow:auto'>
	<ul>
		".implode("\n",$a)."
	</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#org_main').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
			
			LoadAjaxHidden('count-user-$time','$page?count-de-users={$_GET["ou"]}&ou={$_GET["ou"]}');
			
		</script>
	
	";
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,"js:{$_GET["ou"]}",$html);
	echo $html;
	
}

function COUNT_DE_USERS(){
	$ldap=new clladp();
	echo $ldap->CountDeUSerOu($_GET["count-de-users"]);
}



function organization_storage(){

	$disk=Paragraphe('64-hd.png','{your_storage}','{your_storage_text}',"javascript:Loadjs('domains.edit.hd.php?&ou={$_GET["ou"]}')",null,210,null,0,true);
	$backup=Paragraphe("64-backup.png","{ACTIVATE_BACKUP_AGENT}","{ACTIVATE_BACKUP_AGENT_TEXT}","javascript:Loadjs('domains.edit.backup.php?ou=?&ou={$_GET["ou"]}')",null,210,null,0,true);
	$html="<div style='width:700px'>
	$disk
	$backup</div>
	";
	
	return $html;	
	
	
}

function organization_messaging(){
	$ou=$_GET["ou"];
	$ou_encoded=base64_encode($ou);
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	if(trim($ou==null)){
		if(isset($_COOKIE["SwitchOrgTabsOu"])){$_GET["ou"]=$_COOKIE["SwitchOrgTabsOu"];}
	}
	$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
	if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
	
	$tpl=new templates();

	
	$usersmenus=new usersMenus();
	$usersmenus->LoadModulesEnabled();	
	

	

	$network=Paragraphe('folder-network-64.png','{postfix_network}','{postfix_network_text}',"javascript:Loadjs('domains.postfix.network.php?ou=$ou_encoded')",null,210,null,0,true);
	$restrictions=Paragraphe('64-sender-check.png',
	'{smtpd_client_restrictions}','{smtpd_client_restrictions_text}',"javascript:Loadjs('domains.postfix.smtpd.restrictions.php?ou=$ou_encoded')",null,210,null,0,true);
	$whitelists=Paragraphe("routing-domain-relay.png","{PostfixAutoBlockDenyAddWhiteList}","{PostfixAutoBlockDenyAddWhiteList_explain}",
	"javascript:Loadjs('domains.postfix.conwhitelist.php?ou=$ou_encoded')",null,210,null,0,true);
	$dnsbl=Paragraphe("64-cop-acls-dnsbl.png","{DNSBL_settings}","{DNSBL_settings_text}","javascript:Loadjs('domains.postfix.rbl.php?ou=$ou_encoded')",null,210,null,0,true);
	
	
	if($usersmenus->ASSP_INSTALLED){
		$assp=Paragraphe("64-spam.png","{APP_ASSP}","{APP_ASSP_TEXT}","javascript:Loadjs('domains.assp.php?ou=$ou_encoded')",null,210,null,0,true);
		
	}
	

	$text_disbaled="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	if($usersmenus->KAV_MILTER_INSTALLED==true){
		if($usersmenus->KAVMILTER_ENABLED==1){
				$kavmilter=Paragraphe('icon-antivirus-64.png','{antivirus}','{antivirus_text}',"javascript:Loadjs('domains.edit.kavmilter.ou.php?ou=$ou_encoded')",null,210,null,0,true);
				$extensions_block=Paragraphe("bg_forbiden-attachmt-64.png","{attachment_blocking}","{attachment_blocking_text}","javascript:Loadjs('domains.edit.attachblocking.ou.php?ou=$ou_encoded')",null,210,null,0,true);
			}
		}
		

	if($usersmenus->kas_installed){
		if($usersmenus->KasxFilterEnabled){
			$kas3x=Paragraphe('folder-caterpillar.png','{as_plugin}','{kaspersky_anti_spam_text}',"javascript:Loadjs('domains.edit.kas.php?ou=$ou_encoded')",null,210,null,0,true);
		}
	}
	
if(($usersmenus->AsPostfixAdministrator) OR ($usersmenus->AsMessagingOrg) OR ($usersmenus->AsOrgAdmin) OR ($usersmenus->AllowEditOuSecurity)){
	$quarantine=icon_quarantine($usersmenus);
	$quarantine_admin=Paragraphe("64-banned-regex.png","{all_quarantines}","{all_quarantines_text}","javascript:Loadjs('domains.quarantine.php?js=yes&Master=yes')",null,210,100,0,true);
	
	$rttm="<div style='float:left'>".Buildicon64('DEF_ICO_EVENTS_RTMMAIL')."</div>";
	$stats=Paragraphe('64-milterspy.png','{statistics}','{statistics_ou_text}',"javascript:Loadjs('statistics.ou.php?ou=$ou')",null,210,100,0,true);
	$quarantine_robot=Paragraphe("folder-64-denycontries.png","{quarantine_robots}","{quarantine_robots_text}","javascript:Loadjs('domains.white.list.robots.php?ou=$ou');",null,210,100,0,true);
	if($sock->GET_INFO("EnableWhiteListAndBlackListPostfix")==1){
		$whitelistrobots=Paragraphe('bg_chess-64.png','{enable_artica_wbl_robots}','{enable_artica_wbl_robots_icon}',"javascript:Loadjs('domains.white.list.robots.php?ou=$ou')",null,210,0,0,true);
	}		
}

	if(($usersmenus->AllowChangeDomains) OR ($usersmenus->AsPostfixAdministrator)  OR ($usersmenus->AsMessagingOrg)){
		$transport=Paragraphe('folder-transport-64.png','{localdomains}',
		'{localdomains_text}',"javascript:Loadjs('domains.edit.domains.php?js=yes&ou=$ou')",null,210,null,0,true);
	}

if($usersmenus->cyrus_imapd_installed){
	$buildAllmailboxes=	Paragraphe("rebuild-mailboxes-64.png","{rebuild_mailboxes_org}","{rebuild_mailboxes_org_text}",
	"javascript:Loadjs('domains.rebuild-mailboxes.php?ou=$ou_encoded')",null,210,100,0,true);
}

if($usersmenus->AsQuarantineAdministrator){
			$quarantine_query=Paragraphe('folder-quarantine-extrainfos-64.png','{quarantine_manager}',
			'{quarantine_manager_text}',"javascript:Loadjs(\"domains.quarantine.php?js=$ou\")",null,210,null,0,true);
}
if($usersmenus->ZARAFA_INSTALLED){
	if($usersmenus->AsMailBoxAdministrator){
		$zarafa=Paragraphe("zarafa-logo-64.png","{APP_ZARAFA}","{ZARAFA_OU_ICON_TEXT}",
		"javascript:Loadjs('domains.edit.zarafa.php?ou=$ou_encoded')",null,210,100,0,true);
	}
}

if($usersmenus->ZARAFA_INSTALLED){
	if($usersmenus->cyrus_imapd_installed){
		$zarafa_migration=Paragraphe("database-migration-64.png","{CYRUS_TO_ZARAFA}","{CYRUS_TO_ZARAFA_TEXT}",
		"javascript:Loadjs('domains.migr.zarafa.php?ou=$ou_encoded')",null,210,100,0,true);
	}
}

if($usersmenus->AllowEditOuSecurity){
	$director_report=Paragraphe("postmaster-identity.png","{global_director_report}","{global_director_report_text}",
		"javascript:Loadjs('domains.director.report.php?ou=$ou_encoded')",null,210,100,0,true);
}

if($usersmenus->MAILMAN_INSTALLED){
	if($sock->GET_INFO('MailManEnabled')==1){
		if($usersmenus->AsMailManAdministrator){
			$mailman=Paragraphe("mailman-64.png","{APP_MAILMAN}",'{user_mailman_explain}',
			"javascript:Loadjs('domains.mailman.lists.php?ou=$ou_encoded')",null,210,100,0,true);
			}
	}
}

if($usersmenus->cyrus_imapd_installed){
	if(!$usersmenus->EnableManageUsersTroughActiveDirectory){
		$migration=Paragraphe("group-migration-64.png","{MAILBOXES_MIGRATION}",'{MAILBOXES_MIGRATION_TEXT}',
		"javascript:Loadjs('domains.mailboxes.migration.php?ou=$ou_encoded')",null,210,100,0,true);
	}
}
	
if($usersmenus->ZARAFA_INSTALLED){
	if(!$usersmenus->EnableManageUsersTroughActiveDirectory){
		$migration=Paragraphe("group-migration-64.png","{MAILBOXES_MIGRATION}",'{MAILBOXES_MIGRATION_TEXT}',
		"javascript:Loadjs('domains.mailboxes.migration.php?ou=$ou_encoded')",null,210,100,0,true);
	}
}
	
	if($EnableArticaSMTPStatistics==0){$stats=null;}
	
		$network=null;
		$restrictions=null;
		$whitelists=null;
		$dnsbl=null;
		$assp=null;
	
	
	$html="<div style='width:700px'>
	$transport$network$zarafa$zarafa_migration$buildAllmailboxes$restrictions$whitelists$dnsbl$director_report
	$mailman$assp$kavmilter$extensions_block$kas3x$whitelistrobots$quarantine_robot$rttm$quarantine$quarantine_admin$quarantine_report$quarantine_query$stats
	$ArticaHtml$icon_backuphtml$migration
	</div>
	";
	

	
	
	
	
	
	return $html;	
}

function organization_management(){
	$ou=$_GET["ou"];
	$ou_encoded=base64_encode($ou);
	$sock=new sockets();
	if(trim($ou==null)){
		if(isset($_COOKIE["SwitchOrgTabsOu"])){$_GET["ou"]=$_COOKIE["SwitchOrgTabsOu"];}
	}
	$usersmenus=new usersMenus();
	$usersmenus->LoadModulesEnabled();	
	
	if(($usersmenus->AllowAddUsers) OR ($usersmenus->AsOrgAdmin) OR ($usersmenus->AsMessagingOrg)){	
		$add_user=Paragraphe('folder-useradd-64.png','{create_user}','{create_user_text}',"javascript:Loadjs('domains.add.user.php?ou=$ou')",null,210,null,0,true);	
		$groups=Paragraphe('folder-group-64.png','{manage_groups}','{manage_groups_text}',"javascript:Loadjs('domains.edit.group.php?ou=$ou_encoded&js=yes')",null,210,100,0,true);
		
	}
	

	
		

	$find_members=Paragraphe('find-members-64.png','{find_members}','{find_members_text}',"javascript:Loadjs('domains.find.user.php?ou=$ou')",null,210,null,0,true);		
	

	if($usersmenus->AsArticaAdministrator){$delete=Paragraphe('64-cancel.png','{delete_ou}','{delete_ou_text}',"javascript:DeleteOU(\"$ou\");",null,210,100,0,true);}
	
	
if($usersmenus->AsOrgAdmin){
	$ldap=new clladp();
	$img=$ldap->get_organization_picture($ou,64);
	$ad_import=Paragraphe('folder-import-ad-64.png','{ad_import}','{ad_import_text}',"javascript:Loadjs('domains.ad.import.php?ou=$ou')",null,210,0,0,true);
	$ldap_import=Paragraphe('database-restore-64.png','{ldap_importation}','{ldap_importation_text}',"javascript:Loadjs('domains.ldap.import.php?ou=$ou')",null,210,0,0,true);
	$orgsettings=Paragraphe($img,'{ORG_SETTINGS}','{ORG_SETTINGS_TEXT}',"javascript:Loadjs('domains.organization-settings.php?ou=$ou')",null,210,0,0,true);
	$orgsduplicate=Paragraphe('org-duplicate-64.png','{EXPORT_ORG}','{duplicate_to_remote_server}',"javascript:Loadjs('domains.organization-settings.php?ou=$ou&js-export=yes')",null,210,0,0,true);
	}

if(($usersmenus->AsOrgAdmin) OR ($usersmenus->AsMessagingOrg)){
	$transport=Paragraphe('folder-transport-64.png','{localdomains}','{localdomains_text}',"javascript:Loadjs('domains.edit.domains.php?js=yes&ou=$ou')",null,210,null,0,true);
	if($usersmenus->POSTFIX_INSTALLED){$sendmail="<div style='float:left'>".Buildicon64('DEF_ICO_SENDTOALL',210,100,"?ou=$ou")."</div>";}
}	

if($usersmenus->ARTICA_META_ENABLED){
	if($sock->GET_INFO("AllowArticaMetaAddUsers")<>1){$add_user=null;}
}
if($usersmenus->POSTFIX_INSTALLED){$transport=null;	}

if($usersmenus->EnableManageUsersTroughActiveDirectory){
	$ad_import=Paragraphe('folder-import-ad-64-grey.png','{ad_import}','{ad_import_text}');
	$orgsduplicate=Paragraphe('org-duplicate-64-grey.png','{EXPORT_ORG}','{duplicate_to_remote_server}');
	$delete=Paragraphe('64-cancel-grey.png','{delete_ou}','{delete_ou_text}');
	$add_user=Paragraphe('folder-useradd-64-grey.png','{create_user}','{create_user_text}');
}

$tr[]=$add_user ;
$tr[]=$groups; 
$tr[]=$find_members ;
$tr[]=$transport; 
$tr[]=$ad_import;
$tr[]=$ldap_import;
$tr[]=$orgsettings; 
$tr[]=$orgsduplicate;
$tr[]=$sendmail;
$tr[]=$delete;

$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	
	
$html="<div style='width:700px'>". implode("\n",$tables)."</div>";
return $html;
}

function icon_artica_filters_rules($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-rules-64-grey.jpg";
	$ok="folder-rules-64.jpg";
	$link="global-filters.ou.php?ou=$ou";
	$good="{artica_filters_rules_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="artica_filters_rules";
	$uri=null;
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AllowEditOuSecurity==true){
			if($usersmenus->ArticaFilterEnabled==1){
			$show=true;
			$uri=$link;
			$img=$ok;
			$text=$good;
			}
		}
	}
	
return Paragraphe($img,"{{$title}}",$text,$uri);

}

function icon_backuphtml($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-64-backup-grey.png";
	$ok="folder-64-backup.png";
	$link="backuphtml.ou.php?ou=$ou";
	$good="{filter_backup_rules}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="backup_rules";
	$uri=null;
	
	$enable=false;
	$usersmenus->LoadModulesEnabled();
	if($usersmenus->MIMEDEFANG_INSTALLED){
		if($usersmenus->MimeDefangEnabled==1){
			$enable=true;}
	}
		
	if($usersmenus->AMAVIS_INSTALLED){
		if($usersmenus->EnableAmavisDaemon==1){
		$enable=true;}
		}
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AllowEditOuSecurity==true){
			if($enable){
					if($usersmenus->MHONARC_INSTALLED){
						include_once('ressources/class.mimedefang.inc');
						$mime=new mimedefang();
						if($mime->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]==1){
								$show=true;
								$uri=$link;
								$img=$ok;
								$text=$good;
							}
					}
			}
	}
	}
if($text==$good){
return Paragraphe($img,"{{$title}}",$text,$uri,null,210,null,0,true);
}

}


function icon_html_size_blocker($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="icon-html-64-grey.png";
	$ok="icon-html-64.png";
	$link="html.blocker.ou.php?ou=$ou";
	$good="{htmlSizeBlocker_intro}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$uri=null;
	
	$usersmenus=new usersMenus();
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AsPostfixAdministrator==true){
			if($usersmenus->MIMEDEFANG_INSTALLED){
				if($usersmenus->MimeDefangEnabled==1){
				$mime=new mimedefang();
				if($mime->ScriptConf_array["BUILD"]["BIGHTML_ENABLED"]==1){
					$show=true;
					$uri=$link;
					$img=$ok;
					$text=$good;
				}
			  }
			}
		}
	}
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AsPostfixAdministrator==true){
			if($usersmenus->AMAVIS_INSTALLED){
				if($usersmenus->EnableAmavisDaemon==1){
					$show=true;
					$uri=$link;
					$img=$ok;
					$text=$good;
				}
			}
		}
	}	
        if($text==$good){
			return Paragraphe($img,'{htmlSizeBlocker}',$text,$uri,null,210,null,0,true);
        }
}
function icon_quarantine($usersmenus){
	return null;
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-quarantine-0-64-grey.jpg";
	$ok="folder-quarantine-0-64.jpg";
	$link="quarantine.ou.php?ou=$ou";
	$good="{global_quarantine_rules_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="global_quarantine_rules";
	$uri=null;
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AllowEditOuSecurity==true){
			if($usersmenus->ArticaFilterEnabled==1){
			$show=true;
			$uri=$link;
			$img=$ok;
			$text=$good;
			}
		}
	}

return Paragraphe($img,"{{$title}}",$text,$uri,null,210,null,0,true);
	
}

function icon_denyattach($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-denyattach-64-grey.jpg";
	$ok="folder-denyattach-64.jpg";
	$link="global-ext-filters.ou.php?ou=$ou";
	$good="{artica_filtersext_rules_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="artica_filtersext_rules";
	$uri=null;
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AllowEditOuSecurity==true){
			if($usersmenus->ArticaFilterEnabled==1){
			$show=true;
			$uri=$link;
			$img=$ok;
			$text=$good;
			}
		}
	}
	
	return Paragraphe($img,"{{$title}}",$text,$uri);
}

function icon_surbl($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="surbl-64-grey.png";
	$ok="surbl-64.png";
	$link="global-countries-surbl.ou.php?ou=$ou";
	$good="{surbl_rules_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="surbl_rules";
	$uri=null;
	return null;	
}

function icon_rbl($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-64-dnbl-grey.png";
	$ok="folder-64-dnbl.png";
	$link="global-countries-rbl.ou.php?ou=$ou";
	$good="{rbl_check_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="rbl_check";
	$uri=null;
return null;		
}

function icon_dbl($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-domains-blacklist-64-grey.jpg";
	$ok="folder-domains-blacklist-64.jpg";
	$link="global-blacklist.ou.php?ou=$ou";
	$good="{global_blacklist_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="global_blacklist";
	$uri=null;
	return null;
}

function icon_country($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-64-denycontries-grey.png";
	$ok="folder-64-denycontries.jpg";
	$link="global-countries-filters.ou.php?ou=$ou";
	$good="{deny_countries_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="deny_countries";
	$uri=null;
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->AllowEditOuSecurity==true){
			if($usersmenus->ArticaFilterEnabled==1){
			$show=true;
			$uri=$link;
			$img=$ok;
			$text=$good;
			}
		}
	}
	
	return Paragraphe($img,"{{$title}}",$text,$uri);	
	
	
}

function icon_antispam($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-caterpillar-grey.jpg";
	$ok="folder-caterpillar.jpg";
	$link="kas.user.rules.php?ou=$ou";
	$good="{manage_groups_antispam_text}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="manage_groups_antispam";
	$uri=null;
	
	if($usersmenus->kas_installed){
		if($usersmenus->AllowChangeKas==true){
			if($usersmenus->ArticaFilterEnabled==1){
			$show=true;
			$uri=$link;
			$img=$ok;
			$text=$good;
			}
		}
	}
return Paragraphe($img,"{{$title}}",$text,$uri);

}

function icon_bogofilter($usersmenus){
	$show=false;
	$ou=$_GET["ou"];
	$img="folder-bogo-64-grey.png";
	$ok="folder-bogo-64.png";
	$link="bogofilter.ou.php?ou=$ou";
	$good="{APP_BOGOFILTER_TEXT}";
	$text="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$title="APP_BOGOFILTER";
	$uri=null;
	$usersmenus->LoadModulesEnabled();
	include_once("ressources/class.mimedefang.inc");
	
	if($usersmenus->POSTFIX_INSTALLED){
		if($usersmenus->MIMEDEFANG_INSTALLED){
			if($usersmenus->MimeDefangEnabled==1){
				if($usersmenus->BOGOFILTER_INSTALLED){
					$mime=new mimedefang();
					if($mime->ScriptConf_array["BUILD"]["ENABLE_BOGO"]==1){
								$show=true;
								$uri=$link;
								$img=$ok;
								$text=$good;
							}
					}
			}
	}
	}
	
return Paragraphe($img,"{{$title}}",$text,$uri);

}


function organization_content_analyze(){
	$ou=$_GET["ou"];
	$usersmenus=new usersMenus();
	$kas=icon_antispam($usersmenus);
	
	$bogofilter=icon_bogofilter($usersmenus);
	$others_filters=icon_artica_filters_rules($usersmenus);
	
$html="<table style='width:100%'>
	<tr>
	<td valign='top'>$kas</td><td valign='top'>$bogofilter</td><td valign='top'>$others_filters</td>
	</tr>
	</table>
	";	

if($usersmenus->SMTP_LEVEL>0){
	$html="
	<p style='font-size:18px'>{features_disabled_smtp}</p>
	
	";
	}
	return $html;
}

function organization_groupwares(){
	$users=new usersMenus();
	$MEM_TOTAL_INSTALLEE=$users->MEM_TOTAL_INSTALLEE;
	$page=CurrentPageName();
	if($MEM_TOTAL_INSTALLEE<526300){
		$tpl=new templates();
		
		$html="
		<center>
		<table style='width:350px'>
		<tr>
		<td valign='top' width=1%>
		<img src='img/64-infos.png'>
		</td>
		<td valign='top'>
		<H2>{MEM_TOTAL_INF_512}</h2>
		<p style='font-size:15px'>{MEM_TOTAL_INF_512_TEXT}</p>
		</td>
		</tr>
		<Table>
		</center>
		";
		return $html;
		
	}
	
	
	$html="
	
	<div id='groupwares-list-div'></div>
	
	<script>
		LoadAjax('groupwares-list-div','$page?groupware-list=yes&SwitchOrgTabs={$_GET["ou"]}&ou={$_GET["ou"]}&mem=yes');
	</script>
	
	";
	
	return $html;
	
	
}



	
function organization_groupwares_list(){
	
$users=new usersMenus();
	$sock=new sockets();
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	
	
	//if($ApacheGroupware==0){return organization_vhostslist_disabled();}	

$ou=$_GET["ou"];
$tr=organization_vhostslist($ou);

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>";	
		
	$tpl=new templates();	
	echo $tpl->_ENGINE_parse_body(implode("\n",$tables));		
	
}

function organization_vhostslist_disabled(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	if($users->AsSystemAdministrator){
		$button="<hr>".button("{ACTIVATE_APP_GROUPWARE_APACHE}","EnableApacheGroupware()");
		
		
		
	}
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/software-remove-128.png'></td>
		<td valign='top'>
			<H2 style='color:red'>{APP_GROUPWARE_APACHE}:: {DISABLED}</H2>
			<div style='font-size:14px' id='EnableApacheGroupware'>
			{APP_GROUPWARE_APACHE_DISABLED_TEXT}
			</div>
			<p>&nbsp;</p>
			<div style='text-align:right'>$button</div>
			
		</td>
	</tr>
	</table>
	<script>
var x_EnableApacheGroupware= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
    RefreshTab('org_main');
	}	
	
	function EnableApacheGroupware(){
	var XHR = new XHRConnection();
	XHR.appendData('EnableApacheGroupware','yes');
	document.getElementById('EnableApacheGroupware').innerHTML='<img src=\"img/wait_verybig.gif\">';
	XHR.sendAndLoad('$page', 'GET',x_EnableApacheGroupware);	
	
	}	
	
	</script>
	";
	
	return $html;
	
}

function organization_vhostslist($ou){
	$sock=new sockets();
	$usersmenus=new usersMenus();
	if($usersmenus->AsOrgAdmin){
		$tr[]=Paragraphe('www-add-64.png','{ADD_WEB_SERVICE}','{ADD_WEB_SERVICE_TEXT}',"javascript:Loadjs('domains.www.php?ou=$ou&add=yes')",null,210,0,0,true);		
	}
	
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$apache=new vhosts();
	$hash=$apache->LoadVhosts($ou);
	$html="<table style='width:95%' class=table_form>";
	$server=$_SERVER['SERVER_NAME'];
	if(preg_match("#(.+?):#",$server,$re)){$server=$re[1];}
	while (list ($host, $wwwservertype) = each ($hash) ){
		$img=null;
		$suffix=null;
		$URI="http://$host:$ApacheGroupWarePort$suffix";
		$array=$apache->SearchHosts($host);

		if($array["wwwsslmode"]=="TRUE"){$URI="https://$host$suffix";}
		switch ($wwwservertype) {
			case "JOOMLA":$suffix="/administrator";break;
			case "DRUPAL":$img="drupal-logo-32.png";$suffix="/install.php";break;
			}
		
		$img=$apache->IMG_ARRAY_64[$wwwservertype];
		
		$warn=null;
		$ip=gethostbyname($host);
		if($ip==$host){$warn=imgtootltip("status_warning.gif","{could_not_find_iphost}:$host");}
		$link=imgtootltip("alias-32.gif","<b><u>$host</u></b><br>$URI","s_PopUpFull('$URI',800,800)");
		$js="Loadjs('domains.www.php?ou=$ou&add=yes&host=$host')";
		$tr[]=Paragraphe(array($img,$warn,$link),$host,'{'.$apache->TEXT_ARRAY[$wwwservertype]["TITLE"].'}<br>{'.$apache->TEXT_ARRAY[$wwwservertype]["TEXT"].'}',"javascript:$js");
	}
	
	return $tr;
		
}


function organization_security_analyze(){
	$ou=$_GET["ou"];
	$usersmenus=new usersMenus();


$attachs=icon_denyattach($usersmenus);
$html="<table style='width:100%'>
	<tr>
	<td valign='top'>$attachs</td><td valign='top'>$kav</td><td valign='top'>&nbsp;</td>
	</tr>
	</table>
	";	

if($usersmenus->SMTP_LEVEL>0){
	$html="
	<p style='font-size:18px'>{features_disabled_smtp}</p>
	
	";
	}
	return $html;	
	
}

function organization_blacklist(){
	$ou=$_GET["ou"];
	$usersmenus=new usersMenus();
	

	
$country=icon_country($usersmenus);
$surbl=icon_surbl($usersmenus);
$rbl=icon_rbl($usersmenus);
$dbl=icon_dbl($usersmenus);

$html="<table style='width:100%'>
	<tr>
	<td valign='top'>$surbl</td><td valign='top'>$rbl</td><td valign='top'>$dbl</td>
	</tr>
	<tr>
	<td valign='top'>$country</td><td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td>
	</tr>	
	</table>
	";	

if($usersmenus->SMTP_LEVEL>0){
	$html="
	<p style='font-size:18px'>{features_disabled_smtp}</p>
	
	";
	}

	return $html;	
	
}

function organization_sections(){
	
	
	
	
	$tpl=new templates();
	
	if(isset($_GET["ajaxmenu"])){
		echo $tpl->_ENGINE_parse_body("<input type='hidden' value='{$_GET["ou"]}' id='ouselected'><input type='hidden' value='{delete_ou_text}' id='delete_ou_text'>");
	}
	
	$keychached="sec:{$_GET["ou"]}:{$_GET["ajaxmenu"]}:::{$_GET["org_section"]}";
	if(GET_CACHED(__FILE__,__FUNCTION__,$keychached)){return;}
	
	switch ($_GET["org_section"]) {
		case "management":
			
			$html=$tpl->_ENGINE_parse_body(organization_management());
			SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
			echo $html;
			break;
		
		case "groupwares":
			if(GET_CACHED(__FILE__,__FUNCTION__,"sec:{$_GET["ou"]}:{$_GET["org_section"]}")){return null;}
			$html=$tpl->_ENGINE_parse_body(organization_groupwares());
			SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
			echo $html;			
			break;
			
		case "storage":
			
			$html= $tpl->_ENGINE_parse_body(organization_storage());
			SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
			echo $html;
			break;	
			
		case "users":
			$html=$tpl->_ENGINE_parse_body(organization_users_list());
			SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
			echo $html;			
			break;
			
		case "postfix":
			$html=$tpl->_ENGINE_parse_body(organization_messaging());
			SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
			echo $html;
			break;	
		case 3:
			$html=$tpl->_ENGINE_parse_body(organization_blacklist());
			SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
			echo $html;
			break;							
	
			default:
				$html=$tpl->_ENGINE_parse_body(organization_management());	
				SET_CACHED(__FILE__,__FUNCTION__,$keychached,$html);
				echo $html;				
				break;
	}
	
	
}

function organization_users_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<input type='hidden' id='org_user_list_ou' value='{$_GET["ou"]}'>
	<table style='width:100%;margin:5px'>
	<tr>
		<td width=1% nowrap class=legend style='font-size:14px;padding:3px;font-weight:bold'>{search}:</td>
		<td>". Field_text("searchstring",null,"font-size:14px;padding:3px;font-weight:bold",null,null,null,false,"FindUserCheckEnter(event)")."</td>
	</tr>
	</table>
	<div id='org_user_list'></div>
	<script>
		function FindUserCheckEnter(e){
			if(checkEnter(e)){
				LoadAjax('org_user_list','$page?finduser='+document.getElementById('searchstring').value+'&ou={$_GET["ou"]}');
			}
		}
	
	
		LoadAjax('org_user_list','$page?finduser=&ou={$_GET["ou"]}');
	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function organization_users_find_member(){
	$keycached="{$_GET["finduser"]}{$_GET["ou"]}";
	if(GET_CACHED(__FILE__,__FUNCTION__,$keycached)){return true;}
	$sock=new sockets();
	$tofind=$_GET["finduser"];
	$tpl=new templates();
	$page=CurrentPageName();
	
	
	if(is_base64_encoded($_GET["ou"])){
		$ou_encoded=$_GET["ou"];
		$ou=base64_decode($_GET["ou"]);
		}else{
			$ou=$_GET["ou"];
			$ou_encoded=base64_encode($_GET["ou"]);
		}
	writelogs("Find users $tofind in $ou (encoded={$_GET["ou"]})",__FUNCTION__,__FILE__,__LINE__);
	
	if($_SESSION["uid"]<>-100){$ou=$_SESSION["ou"];}
	$ldap=new clladp();
	if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
	$filter="(&(objectClass=userAccount)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(uid=$tofind) (givenname=$tofind)))";
	$attrs=array("displayName","uid","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber","sAMAccountName");
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	
	if($ldap->EnableManageUsersTroughActiveDirectory){
		$cc=new ldapAD();
		$dn="$cc->suffix";
		$filter="(&(objectClass=user)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(sAMAccountName=$tofind) (givenname=$tofind)))";
		if(trim($ou)=="Domain Controllers"){$dn="CN=Users,$cc->suffix";}
	}
	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs,150);
	
	
	
	$users=new user();
	
	$number=$hash["count"];
	$bg="#FFFFFF";
	writelogs("Find users $tofind in ou $ou DN:$dn (encoded={$_GET["ou"]}) $number items",__FUNCTION__,__FILE__,__LINE__);
	$html="
	
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<div style='width:100%;height:560px;overflow:auto'>	
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
				<th colspan=4>$tofind</th>
			</tr>
		</thead>
		<tbody class='tbody'>
				
	";
		$add_user_disabled=Paragraphe('folder-useradd-64-grey.png','{create_user}','{create_user_text}');
		$add_user=Paragraphe('folder-useradd-64.png','{create_user}','{create_user_text}',"javascript:Loadjs('domains.add.user.php?ou=$ou')",null,210,null,0,true);	
		$groups=Paragraphe('folder-group-64.png','{manage_groups}','{manage_groups_text}',"javascript:Loadjs('domains.edit.group.php?ou=$ou_encoded&js=yes')",null,210,100,0,true);
		$delete_all_users=Paragraphe('member-64-delete.png','{delete_all_users}','{delete_all_users_text}',"javascript:DeleteAllusers()",null,210,100,0,true);
		
		
		
		
		$usermenus=new usersMenus();
		if($usermenus->ARTICA_META_ENABLED){
			if($sock->GET_INFO("AllowArticaMetaAddUsers")<>1){$add_user=$add_user_disabled;}
		}
		
		if($usermenus->EnableManageUsersTroughActiveDirectory){
			$add_user=$add_user_disabled;
		}
		
		$delete_all_users_warn=$tpl->javascript_parse_text("{delete_all_users_warn}");
		
		
	for($i=0;$i<$number;$i++){
		$userARR=$hash[$i];
		
		$uid=$userARR["uid"][0];
		if($ldap->EnableManageUsersTroughActiveDirectory){
			$uid=$userARR["samaccountname"][0];
		}
		
		if($uid=="squidinternalauth"){continue;}
		$js=MEMBER_JS($uid,1,1);
		
		if(($userARR["sn"][0]==null) && ($userARR["givenname"][0]==null)){
			$userARR["sn"][0]=$uid;
		}
		
		$sn=texttooltip($userARR["sn"][0],"{display}:$uid",$js,null,0,"font-size:13px");
		$givenname=texttooltip($userARR["givenname"][0],"{display}:$uid",$js,null,0,"font-size:13px");
		$title=texttooltip($userARR["title"][0],"{display}:$uid",$js,null,0,"font-size:13px");
		$mail=texttooltip($userARR["mail"][0],"{display}:$uid",$js,null,0,"font-size:13px");
		$telephonenumber=texttooltip($userARR["telephonenumber"][0],"{display}:$uid",$js,null,0,"font-size:13px");
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	
		$img=imgtootltip("contact-48.png","{edit}",$js);
		
		$html=$html ."<tr class=$classtr>
						<td width=1% style='font-size:12px'>$img</td>
						<td width=50% style='font-size:12px'>{$userARR["sn"][0]} {$userARR["givenname"][0]}<div><i>{$userARR["title"][0]}</i></div></td>
						<td width=1% style='font-size:12px'>{$userARR["telephonenumber"][0]}</td>
						<td width=50% style='font-size:12px' nowrap>{$userARR["mail"][0]}</td>
						</tr>
			
		";
		
	}
	$html=$html ."</table>
	</div>
	</td>
	<td valign='top' width=1%>$add_user<br>$groups<br>$delete_all_users</td>
	</tr>
	</table>
	
	<script>
	
	var X_DeleteAllusers= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		LoadAjax('org_user_list','$page?finduser=&ou='+escape('$ou'));
		}		

		function DeleteAllusers(){
			if(confirm('$delete_all_users_warn')){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteAllUsersInOu','yes');
			XHR.appendData('ou','$ou');
			document.getElementById('org_user_list').src='img/wait_verybig.gif';   	
			XHR.sendAndLoad('$page', 'GET',X_DeleteAllusers);
			}
		
		}
	
</script>	
	";

	
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,$keycached,$html);
	echo $html;
}


function organization_users_formatUser($hash){
	
	$html="<table style='width:100%'>
	<tr>
		<td colspan=2>
			<span style='font-size:14px;font-weight:bold;text-transform:capitalize'>{$hash["displayname"][0]}</span>&nbsp;-&nbsp;
			<span style='font-size:10px;font-weight:bold;text-transform:capitalize'>{$hash["sn"][0]}&nbsp;{$hash["givenname"][0]}</span>
			
			<hr style='border:1px solid #FFF;margin:3px'>
			</td>
	</tr>
	<tr>
		<td align='right'><span style='font-size:10px;font-weight:bold'>{$hash["title"][0]}</span>&nbsp;|&nbsp;{$hash["mail"][0]}&nbsp;|&nbsp;{$hash["telephonenumber"][0]}
	</table>
	
	";
	
	
	
	$html=RoundedLightGrey($html,$js,1);
	$html="<div style='margin:5px;padding-right:10px;padding-left:10px'>$html</div>";
	return $html;
	
	
}
function VerifyRights(){
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){$_GET["ou"]=$_SESSION["ou"];}
	if($usersmenus->AsOrgPostfixAdministrator){return true;}
	if($usersmenus->AsMessagingOrg){return true;}
	if(!$usersmenus->AllowChangeDomains){return false;}
}

function EnableApacheGroupware(){
	$tpl=new templates();

	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){
		echo $tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		return;
	}
	$sock=new sockets();
	$sock->SET_INFO("ApacheGroupware","1");
	$sock->getFrameWork("cmd.php?RestartApacheGroupwareForce=yes");	
}

function DeleteAllUsersInOu(){
	$ou=$_GET["ou"];
	$ldap=new clladp();
	if(!$ldap->DeleteAllUsersInOu($ou)){
		echo "Failed\n$ldap->ldap_last_error\n";
	}
	
	
}


?>	