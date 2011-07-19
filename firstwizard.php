<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.user.inc');
	
	include_once('ressources/class.milter.greylist.inc');
	include_once('ressources/class.sqlgrey.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.milter.greylist.inc');	
	include_once('ressources/class.mailfromd.inc');	
	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){echo "DIE";die();}
if(isset($_GET["op"])){switch_op();}	
if(isset($_GET["cancel"])){cancel();exit;}
	
switch ($_GET["start"]) {
	case 0:
		Acceuil();
		break;
	case 1:
		org();
		break;
	case 2:
		group();
		break;

case 3:
	   domain();
		break;					
case 4:
	   domain_type();
		break;	
case 5:
	   user();
		break;	

case 7:
	build();	
			
	default:
		break;
}



function Acceuil(){
	
	
	$html="<H5>{welcome}</H5>
	<p class=caption>{welcome_text}</p>
	<hr>
	<table style='width:100%'>
	<tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_Cancel();\" value='&laquo;&nbsp;{cancel}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:firstwizard_1();\" value='{next}&nbsp;&raquo;'></td>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function org(){
	
	
	$html="<H5>{organization}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/chiffre1.png'></td>
	<td>
	<p class=caption>{org_text}</p>
	" . Field_text('ou',$_GET["ou"],'width:150px')."
	<hr>
	<table style='width:100%'>
	<tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_1();\" value='&laquo;&nbsp;{back}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:firstwizard_2();\" value='{next}&nbsp;&raquo;'></td>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function group(){
	if($_GET["ou"]==null){org();exit;}
	
	$html="<H5>{$_GET["ou"]}:&nbsp;{group}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/chiffre2.png'></td>
	<td>
	<p class=caption>{group_text}</p>
	<input type='hidden' id='ou' value='{$_GET["ou"]}'>
	" . Field_text('group',$_GET["group"],'width:150px')."
	<hr>
	<table style='width:100%'>
	<tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_1();\" value='&laquo;&nbsp;{back}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:firstwizard_3();\" value='{next}&nbsp;&raquo;'></td>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function domain(){
	if($_GET["ou"]==null){org();exit;}
	if($_GET["group"]==null){group();exit;}
	
	$html="<H5>{$_GET["ou"]}:&nbsp;{$_GET["group"]}:&nbsp;{domain}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/chiffre3.png'></td>
	<td>
	<p class=caption>{domain_text}</p>
	<input type='hidden' id='ou'value='{$_GET["ou"]}'>
	<input type='hidden' id='group' value='{$_GET["group"]}'>
	" . Field_text('domain',$_GET["domain"],'width:150px')."
	<hr>
	<table style='width:100%'>
	<tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_2();\" value='&laquo;&nbsp;{back}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:firstwizard_4();\" value='{next}&nbsp;&raquo;'></td>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function domain_type(){
	if($_GET["ou"]==null){org();exit;}
	if($_GET["group"]==null){group();exit;}
	if($_GET["domain"]==null){domain();exit;}	
	
	$html="<H5>{$_GET["ou"]}:&nbsp;{$_GET["group"]}:&nbsp;{$_GET["domain"]}:&nbsp;{relay_type}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/chiffre4.png'></td>
	<td>
	<p class=caption>{domain_iptext}</p>
	<input type='hidden' id='ou'value='{$_GET["ou"]}'>
	<input type='hidden' id='group' value='{$_GET["group"]}'>
	<input type='hidden' id='domain' value='{$_GET["domain"]}'>
	" . Field_text('domain_ip',$_GET["domain_ip"],'width:150px')."
	<hr>
	<table style='width:100%'>
	<tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_3();\" value='&laquo;&nbsp;{back}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:firstwizard_5();\" value='{next}&nbsp;&raquo;'></td>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function user(){
	if($_GET["ou"]==null){org();exit;}
	if($_GET["group"]==null){group();exit;}
	if($_GET["domain"]==null){domain();exit;}	

	
	$html="<H5>{$_GET["ou"]}:&nbsp;{$_GET["group"]}:&nbsp;{$_GET["domain"]}:&nbsp;{user}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/chiffre5.png'></td>
	<td>
	<p class=caption>{user_text}</p>
	<input type='hidden' id='ou'value='{$_GET["ou"]}'>
	<input type='hidden' id='group' value='{$_GET["group"]}'>
	<input type='hidden' id='domain' value='{$_GET["domain"]}'>
	<input type='hidden' id='domain_ip' value='{$_GET["domain_ip"]}'>
	<table style=width:100%>
	<tr>
	<td align='right'>{uid}</td>
	<td>
	" . Field_text('uid',$_GET["uid"],'width:150px')."
	</td>
	</tr>
	<tr>
	<td align='right'>{password}</td>
	<td>
	" . Field_text('password',$_GET["password"],'width:150px')."
	</td>
	</tr>	
	</table>
	<br>
	<hr>
	<table style='width:100%'>
	<tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_4();\" value='&laquo;&nbsp;{back}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:Build();\" value='{build}&nbsp;&raquo;'></td>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function cancel(){
	$art=new artica_general();
	$art->ArticaFirstWizard=$_GET["cancel"];
	$art->Save();
	
}


function Build(){
$html="<H5>{build}</H5>
	<input type='hidden' id='ou'value='{$_GET["ou"]}'>
	<input type='hidden' id='group' value='{$_GET["group"]}'>
	<input type='hidden' id='domain' value='{$_GET["domain"]}'>
	<input type='hidden' id='domain_ip' value='{$_GET["domain_ip"]}'>
	<input type='hidden' id='uid' value='{$_GET["uid"]}'>
	<input type='hidden' id='password' value='{$_GET["password"]}'>	

	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/system-64.png'></td>
	<td valign='top'>
	<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td><td><div id=message_1>{build_org}</td>
	</tr><tr>
	<td width=1%><img src='img/fw_bold.gif'></td><td><div id=message_2>{build_group}</td>	
	</tr><tr>
	<td width=1%><img src='img/fw_bold.gif'></td><td><div id=message_3>{build_domain}</td>	
	</tr><tr>
	<td width=1%><img src='img/fw_bold.gif'></td><td><div id=message_4>{build_user}</td>	
	</tr><tr>
	<td width=1%><img src='img/fw_bold.gif'></td><td><div id=message_5>{rebuild_modules}</td>	
	</tr><tr>		
	<td width=1%><img src='img/fw_bold.gif'></td><td><div id=message_6>{rebuild_postfix}</td>	
	</tr>
	<td><input type='button' OnClick=\"javascript:firstwizard_5();\" value='&laquo;&nbsp;{back}'></td>
	<td align='right'><input type='button' OnClick=\"javascript:BuildAction();\" value='{launch}&nbsp;&raquo;'></td></tr>
	</tr>
	</table>
	</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function switch_op(){
	
	switch ($_GET["op"]) {
		case 1:
			CreateOU();exit;
			break;
		case 2:
			CreateGroup();exit;
			break;
		case 3:
			CreateDomain();exit;
			break;
		case 4:
			CreateUser();exit;
			break;	
		case 5:
			RebuildModules();exit;
			break;	
		case 6:
			RebuildPostfix();exit;
			break;								
		default:
			break;
	}
	
}

function RebuildPostfix(){
	$tpl=new templates();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();	

	$artica=new artica_general();
	$artica->ArticaFirstWizard=1;
	$artica->Save();
	
	echo $tpl->_ENGINE_parse_body('main.cf/master.cf {added}');
}

function CreateOU(){
	$ldap=new clladp();
	$ldap->AddOrganization($_GET["ou"]);
	$tpl=new templates();
	if($ldap->ldap_last_error<>null){
		if($ldap->ldap_last_error_num<>68){
			echo $ldap->ldap_last_error;exit;}
	}
	echo $tpl->_ENGINE_parse_body( "{$_GET["ou"]} {added}");
	
	
}

function CreateGroup(){
	$tpl=new templates();
	$group=$_GET["group"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	
	if(!$ldap->AddGroup($group,$ou)){
		if($ldap->ldap_last_error<>null){
			echo "err:$ldap->ldap_last_error";}
	}else{echo $tpl->_ENGINE_parse_body("{$_GET["group"]} {added}");}
			
	
}

function CreateDomain(){
	$tpl=new templates();
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	if($_GET["domain_ip"]<>null){CreateDomainIP();return null;}
	$ldap->AddDomainEntity($ou,$domain);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}else{ echo $tpl->_ENGINE_parse_body($domain. ' {added}');}
	
	$main=new main_cf();
	$main->main_array["myorigin"]=$domain;
	$main->save_conf();	

}	


function CreateDomainIP(){
	$ldap=new clladp();
	$tpl=new templates();
	$ou=$_GET["ou"];
	$dn="cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}
	$domain_name=trim($_GET["domain"]);
	$relayIP=$_GET["domain_ip"];
	$relayPort=25;
	$mx="yes";
	
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";	
	
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='PostFixRelayDomains';
	$upd['objectClass'][1]='top';
	$ldap->ldap_add($dn,$upd);	
	
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	
	
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="@$domain_name";
	$upd['objectClass'][0]='PostfixRelayRecipientMaps';
	$upd['objectClass'][1]='top';
	$ldap->ldap_add($dn,$upd);		
	
	$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	
if($relayIP<>null){
	if($mx=="no"){$relayIP="[$relayIP]";}
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]="relay:$relayIP:$relayPort";
	if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}else{ echo $tpl->_ENGINE_parse_body($domain. ' {added}');}
	}
	
	$main=new main_cf();
	$main->main_array["myorigin"]=$domain;
	$main->save_conf();
	
}

function CreateUser(){
	$ldap=new clladp();
	$tpl=new templates();
	$groupid=$ldap->GroupIDFromName($_GET["ou"],$_GET["group"]);
	$user=new user();
	$user->uid=$_GET["uid"];
	$user->ou=$_GET["ou"];
	$user->DisplayName=$_GET["uid"];
	$user->group_id=$groupid;
	$user->mail="{$_GET["uid"]}@{$_GET["domain"]}";
	$user->password=$_GET["password"];
	if(!$user->add_user()){echo $tpl->_ENGINE_parse_body("{$_GET["uid"]}@{$_GET["domain"]} {failed}");}else{ echo $tpl->_ENGINE_parse_body("{$_GET["uid"]}@{$_GET["domain"]} {added}");}
	}
	
	
function RebuildModules(){
$tpl=new templates();
$artica=new artica_general();
$artica->ArticaFilterEnabled=0;
$user=new usersMenus();
if($user->kas_installed){
	$artica->KasxFilterEnabled=1;
	echo $tpl->_ENGINE_parse_body('kaspersky anti-spam {added}<br>');
	}
if($user->MAILFROMD_INSTALLED){
	$artica->MailFromdEnabled=1;
	$mailfromd=new mailfromd();
	$mailfromd->SaveToLdap();
	$mailfromd->SaveToServer();
	echo $tpl->_ENGINE_parse_body('mailfromd {added}<br>');
	
	}
$artica->AmavisFilterEnabled=0;
if($user->KAV_MILTER_INSTALLED){
	$milter=new kavmilterd();
	$milter->milter_enabled='yes';
	$milter->SaveToLdap();
	$milter->SaveRuleToLdap();
	echo $tpl->_ENGINE_parse_body('kaspersky anti-virus {added}<br>');
}

$artica->Save();

$grey=new sqlgrey();
$grey->SqlGreyEnabled=0;
$grey->SaveToLdap();

if($user->MILTERGREYLIST_INSTALLED){
	echo $tpl->_ENGINE_parse_body('milter-greylist {added}<br>');
	$grey=new milter_greylist();
	$grey->MilterGreyListEnabled="TRUE";
	$grey->SaveToLdap();	
	}
	
}







?>