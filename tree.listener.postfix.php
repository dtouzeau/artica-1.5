<?php
	session_start();
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/kav4mailservers.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.menus.inc');
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}


	
if(isset($_GET["TreePostfixDeleteInterface"])){TreePostfixDeleteInterface();exit;}
if(isset($_GET["TreePostfixAddInetInterface"])){TreePostfixAddInetInterface();exit;}
if(isset($_GET["tab"])){LoadPageTab();}
if(isset($_GET["TreePostfixAddMyNetwork"])){TreePostfixAddMyNetwork();exit;}
if(isset($_GET["TreePostfixDeleteMyNetwork"])){TreePostfixDeleteMyNetwork();}
if(isset($_GET["TreePostfixAddHeaderCheckRule"])){TreePostfixAddHeaderCheckRule();}
if(isset($_GET["TreePostfixHeaderCheckInfoActions"])){TreePostfixHeaderCheckInfoActions();}
if(isset($_GET["fields_operator"])){TreePostfixHeaderSave();}
if(isset($_GET["TreePostfixDeleteHeaderCheckRule"])){TreePostfixDeleteHeaderCheckRule();}
if(isset($_GET["TreePostfixTLSCertificateInfos"])){TreePostfixTLSCertificateInfos();}
if(isset($_GET["TreePostfixTLSCertificateInfosSubmit"])){TreePostfixTLSCertificateInfosSubmit();}
if(isset($_GET["TreePostfixTLSCertificateGenerate"])){TreePostfixTLSCertificateGenerate();}
if(isset($_GET["TreePostfixTLSEnable"])){TreePostfixTLSEnable();}
if(isset($_GET["TreePostfixBuildConfiguration"])){TreePostfixBuildConfiguration();exit;}
if(isset($_GET["TreeAveServerLicenceDeleteKey"])){TreeAveServerLicenceDeleteKey();exit;}
if(isset($_GET["TreeEnableSMTPAuth"])){TreeEnableSMTPAuth();exit;}
if(isset($_GET["smtp_sasl_password_id"])){smtp_sasl_password_id();exit;}
if(isset($_GET["TreeSMTPAuthEdit"])){TreeSMTPAuthEdit();exit;}
if(isset($_GET["TreeSMTPSaslAuthDelete"])){TreeSMTPSaslAuthDelete();exit;}
if(isset($_GET["TreePostfixLoadSmtpd_client_restrictions"])){TreePostfixLoadSmtpd_client_restrictions();exit;}
if(isset($_GET["TreeSmtpd_client_restrictions_addrule"])){TreeSmtpd_client_restrictions_addrule();exit;}
if(isset($_GET["TreeSmtpd_client_restrictions_LoadruleForm"])){TreeSmtpd_client_restrictions_LoadruleForm();exit;}
if(isset($_GET["TreeSmtpd_client_restrictions_saverule"])){TreeSmtpd_client_restrictions_saverule();exit;}
if(isset($_GET["TreeSmtpd_client_restrictions_moverule"])){TreeSmtpd_client_restrictions_moverule();exit;}
if(isset($_GET["TreeSmtpd_client_restrictions_deleterule"])){TreeSmtpd_client_restrictions_deleterule();exit;}
if(isset($_GET["PopUpPostFixInterfaces"])){PopUpPostFixInterfaces();exit;}
if(isset($_GET["PostFixBounceTemplate"])){PostFixBounceTemplate();exit;}
if(isset($_GET["save_bounce_template"])){save_bounce_template();exit;}
if(isset($_GET["save_bounce_maincf"])){save_bounce_maincf();exit;}
if(isset($_GET["postfix_add_network_v2"])){postfix_add_network_v2();exit;}
if(isset($_GET["postfix_add_network_v2_save"])){postfix_add_network_v2_save();exit;}

if(isset($_GET["SmtpdRejectUnlistedRecipientLoad"])){SmtpdRejectUnlistedRecipientLoad();exit;}
if(isset($_GET["LoadSmtpdHeloRequired"])){LoadSmtpdHeloRequired();exit;}

if(isset($_GET["save_smtpd_reject_unlisted_recipient"])){SmtpdRejectUnlistedRecipientSave();exit;}
if(isset($_GET["save_smtpd_helo_required"])){SmtpdHeloRequiredSave();exit;}

if(isset($_GET["PostFixCheckHashTable"])){PostFixCheckHashTable();exit;}
if(isset($_GET["PostFixCheckHashTableSelectAction"])){PostFixCheckHashTableSelectAction();exit;}
if(isset($_GET["PostFixCheckHashTableSelectFilterAction"])){PostFixCheckHashTableSelectFilterAction();exit;}
if(isset($_GET["PostFixCheckHashTableSelectFilterActionSelect"])){PostFixCheckHashTableSelectFilterActionSelect();exit;}
if(isset($_GET["PostFixCheckHashTableSelectFilterActionSave"])){PostFixCheckHashTableSelectFilterActionSave();exit;}
if(isset($_GET["PostFixCheckHashTableSelectPrependAction"])){PostFixCheckHashTableSelectPrependAction();exit;}
if(isset($_GET["PostFixCheckHashTableSelectPrependActionSave"])){PostFixCheckHashTableSelectPrependActionSave();exit;}
if(isset($_GET["PostFixCheckHashTableSave"])){PostFixCheckHashTableSave();exit;}
if(isset($_GET["PostFixCheckHashTableDelete"])){PostFixCheckHashTableDelete();exit;}

function TreePostfixDeleteInterface(){
	$main=new main_cf();
	unset($main->array_inet_interfaces[$_GET["TreePostfixDeleteInterface"]]);
	$main->save_conf();
	$pages=new HtmlPages();
	echo $pages->PagePostfix_maincf_interfaces();
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	
	
	}
function TreePostfixDeleteMyNetwork(){
	$main=new main_cf();
	writelogs("Delete network interface {$_GET["TreePostfixDeleteMyNetwork"]} =>" . $main->array_mynetworks[$_GET["TreePostfixDeleteMyNetwork"]],__FUNCTION__,__FILE__);
	$main->delete_my_networks($_GET["TreePostfixDeleteMyNetwork"]);
	$main->save_conf();
	$pages=new HtmlPages();
	$_GET['tab']=1;
	echo $pages->PagePostfix_maincf_LocalNetwork();
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	
	}	
	
function TreePostfixAddInetInterface(){
	$main=new main_cf();
	$main->array_inet_interfaces[]=$_GET["TreePostfixAddInetInterface"];
	$main->save_conf();
	$pages=new HtmlPages();
	echo $pages->PagePostfix_maincf_interfaces();	
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
}

function TreePostfixAddMyNetwork(){
	$main=new main_cf();
	$main->array_mynetworks[]=$_GET["TreePostfixAddMyNetwork"];
	$main->save_conf();
	$pages=new HtmlPages();
	$_GET['tab']=1;
	echo $pages->PagePostfix_maincf_LocalNetwork();
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
}

function LoadPageTab(){
	$pages=new HtmlPages();
	switch ($_GET["tab"]) {
		case 0:$html=$pages->PagePostfix_maincf_interfaces();break;
		case 1:$html=$pages->PagePostfix_maincf_LocalNetwork();break;
		default:break;
	}
	if($_GET["CurrentPage"]<>"domains.php"){
		echo DIV_SHADOW($html,'windows');exit;
	}
	echo $html;
	
}
function TreePostfixAddHeaderCheckRule(){
	$html=new HtmlPages();
	echo $html->PagePostfixHeaderCheck_windows($_GET["TreePostfixAddHeaderCheckRule"]);
	}
function TreePostfixHeaderCheckInfoActions(){
	$tpl=new templates();
	echo $tpl->_parse_body("{INFOS_{$_GET["TreePostfixHeaderCheckInfoActions"]}_EXPLAIN}");
}
function TreePostfixHeaderSave(){
	include_once("ressources/class.main_cf_filtering.inc");
	$pages=new HtmlPages();
	$ldap=new clladp();
	$add=true;
	$filters=new main_header_check();
	
	
		if(!isset($_GET["id"])){$_GET["id"]=-1;}
		if(!is_numeric($_GET["id"])){$_GET["id"]=-1;}
		$id=$_GET["id"];
		unset($_GET["id"]);
		if($id>-1){$add=false;}
	
	while (list ($num, $ligne) = each ($_GET) ){$datas=$datas ."[$num]=\"$ligne\"\n";}
	$filters->array_ldap_source[$id]=$datas;
	if($add==false){
		while (list ($num, $ligne) = each ($filters->array_ldap_source) ){$update_array["PostfixHeadersRegex"][]=$filters->array_ldap_source[$num];}
		$ldap->Ldap_modify("cn=artica,$ldap->suffix",$update_array);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit();}
		if($pages->AutomaticConfig==true){$filters->SaveToDaemon();}
		echo "ok";		
		exit;
		}
	
	$update_array["PostfixHeadersRegex"][]=$datas;
	$ldap->Ldap_add_mod("cn=artica,$ldap->suffix",$update_array);
	if($ldap->ldap_last_error<>null){echo "ID=[$id]\n$ldap->ldap_last_error";exit();}
	if($pages->AutomaticConfig==true){$filters->SaveToDaemon();}
	echo "ok";
	
	
}
function TreePostfixDeleteHeaderCheckRule(){
	include_once("ressources/class.main_cf_filtering.inc");
	$id=$_GET["TreePostfixDeleteHeaderCheckRule"];
	$filters=new main_header_check();
	$datas=$filters->array_ldap_source[$id];
	unset($filters->array_ldap_source[$id]);

	if(count($filters->array_ldap_source)==0){
		$update_array["PostfixHeadersRegex"]=$datas;
		$ldap=new clladp();
		$ldap->Ldap_del_mod("cn=artica,$ldap->suffix",$update_array);
	}else{
	 while (list ($num, $ligne) = each ($filters->array_ldap_source) ){$update_array["PostfixHeadersRegex"][]=$filters->array_ldap_source[$num];}
	$ldap=new clladp();
	$ldap->Ldap_modify("cn=artica,$ldap->suffix",$update_array);
	if($ldap->ldap_last_error<>null){echo nl2br("ID=[{$_GET["TreePostfixDeleteHeaderCheckRule"]}]\n$ldap->ldap_last_error");}
	}

	$pages=new HtmlPages();
	if($pages->AutomaticConfig==true){$filters->SaveToDaemon();}
	echo $pages->PagePostfixRules();
}
function TreePostfixTLSCertificateInfos(){
	$pages=new HtmlPages();
	echo $pages->PagePostfix_certificate_form();
	}


function TreePostfixTLSEnable(){
	$mny=new usersMenus();
	$tpl=new templates();
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}	
	$main=new main_cf();
	$pages=new HtmlPages();
	if($_GET["TreePostfixTLSEnable"]==1){echo "OK\nTLS Enabled";$main->EnableTLS();}
	if($_GET["TreePostfixTLSEnable"]==0){echo "OK\nTLS Disabled";$main->DisableTLS();}
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
}
function TreePostfixBuildConfiguration(){
	$mny=new usersMenus();
	$tpl=new templates();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}	
	$main=new main_cf();
	$main->save_conf_to_server();
	include_once("ressources/class.main_cf_filtering.inc");
	$filters=new main_header_check();
	$filters->SaveToDaemon();
	echo "OK";
	
}
function TreeAveServerLicenceDeleteKey(){
	$keyfile=trim($_GET["TreeAveServerLicenceDeleteKey"]);
	$mny=new usersMenus();
	$tpl=new templates();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}		
	$sock=new sockets();
	echo($sock->getfile('aveserver_licencemanager_remove:'. $keyfile));
	}
	
function TreeEnableSMTPAuth(){
	$mny=new usersMenus();
	$tpl=new templates();	
	$main=new main_cf();
	$pages=new HtmlPages();
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}			
	if($_GET["TreeEnableSMTPAuth"]=="TRUE"){
		$main->smtp_sasl_password_maps_enable();
		if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	}else{
	     $main->smtp_sasl_password_maps_disable();
	     if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	}
	
	echo $pages->PagePostfixSMTPSaslAuth();
}
function smtp_sasl_password_id(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new main_cf();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}	
	
	$main->smtp_sasl_password_hash[$_GET["smtp_sasl_password_id"]]=array(
		"DOMAIN"=>$_GET["smtp_sasl_password_domain"],
		"USERNAME"=>$_GET["smtp_sasl_password_username"],
		"PASSWORD"=>$_GET["smtp_sasl_password_password"],
		);
		
	$main->save_conf();
	echo $tpl->_parse_body('{success}');
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	
}
function TreeSMTPAuthEdit(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new main_cf();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}	
	echo $pages->PagePostfixSMTPSaslAuth();	
}
function TreeSMTPSaslAuthDelete(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new main_cf();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	unset($main->smtp_sasl_password_hash[$_GET["TreeSMTPSaslAuthDelete"]]);	
	$main->save_conf();
	echo $pages->PagePostfixSMTPSaslAuth();		
}
function TreePostfixLoadSmtpd_client_restrictions(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new main_cf();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo DIV_SHADOW($tpl->_ENGINE_parse_body('{no_privileges'),'windows');exit();}	
	echo DIV_SHADOW($tpl->_ENGINE_parse_body($pages->PagePostfixsmtpd_client_restrictions_table(0,$_GET["Sender"])),'windows');
}

function TreeSmtpd_client_restrictions_addrule(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new smtpd_restrictions();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	
	switch ($_GET["Sender"]) {
		case 1:$HashTable=$main->smtpd_sender_restrictions_table[0];break;
		case 2:$HashTable=$main->smtpd_recipient_restrictions_table[0];break;
		case 3:$HashTable=$main->smtpd_helo_restrictions_table[0];break;		
		case 0:$HashTable=$main->smtpd_client_restrictions_table[0];break;
		default:$HashTable=$main->smtpd_client_restrictions_table[0];break;
		}
		
	while (list ($num, $ligne) = each ($HashTable)){
		$hash[$ligne]="{".$ligne."}";
	}
	$hash[""]="{add new rule}";
	echo $tpl->_ENGINE_parse_body(Field_array_Hash($hash,'RuleSelected',null,"TreeSmtpd_client_restrictions_LoadruleForm({$_GET["Sender"]})"));
}
function TreeSmtpd_client_restrictions_LoadruleForm(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new smtpd_restrictions();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	$rule=$_GET["TreeSmtpd_client_restrictions_LoadruleForm"];
	
	switch ($_GET["Sender"]) {
		case 1:$rule_datas=$main->smtpd_sender_restrictions_table[1][$rule]["datas"];break;
		case 0:$rule_datas=$main->smtpd_client_restrictions_table[1][$rule]["datas"];break;
		case 2:$rule_datas=$main->smtpd_recipient_restrictions_table[1][$rule]["datas"];break;
		case 3:$rule_datas=$main->smtpd_helo_restrictions_table[1][$rule]["datas"];break;		
		default:$rule_datas=$main->smtpd_client_restrictions_table[1][$rule]["datas"];break;
	}
	

	if($rule_datas=='no_datas'){$field="<input type='hidden' name='datas' value=''>no value";}
	if($rule_datas=='rhsbl'){$field=Field_array_Hash($main->dnsrbl_database["RHSBL"],'datas');}
	if($rule_datas=='rbl'){$field=Field_array_Hash($main->dnsrbl_database["RBL"],'datas');}
	if($rule_datas=='ldap'){$field='ldap:' . $rule;}
	
	$html="<br>
	<form name=formrule>
	<input type='hidden' name='TreeSmtpd_client_restrictions_saverule' value='$rule'>
	<input type='hidden' name='Sender' value='{$_GET["Sender"]}'>
	<table style='padding:3px;border:1px dotted #CCCCCC;width:100%'>
	<tr>
	<td valign='top' colspan=2>
		<H4>{".$rule."}:</H4>
		<div class=caption><i>{".$rule."_text}</i></div>
	</td>
	</tr>
	<tr>
	<td align='right'><strong>value:</strong></td>
	<td align='left'>$field</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('formrule','tree.listener.postfix.php',true);TreePostfixLoadSmtpd_client_restrictions({$_GET["Sender"]});Load_postfix_security_rules_table();\">
	</table>
	</form><br>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function TreeSmtpd_client_restrictions_saverule(){
$mny=new usersMenus();
	$tpl=new templates();	
	$main=new smtpd_restrictions();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	
	writelogs("Sender=" . $_GET["Sender"],__FUNCTION__,__FILE__);
	
	switch ($_GET["Sender"]) {
		case 1:
			$main->SenderArray[]=array("value"=>$_GET["TreeSmtpd_client_restrictions_saverule"],"datas"=>$_GET["datas"]);
			$main->save_smtpd_sender_restrictions();			
			break;
			
		case 2:
			$main->RecipientArray[]=array("value"=>$_GET["TreeSmtpd_client_restrictions_saverule"],"datas"=>$_GET["datas"]);
			$main->save_smtpd_recipient_restrictions();
			break;
			
		case 3:
			$main->HelloArray[]=array("value"=>$_GET["TreeSmtpd_client_restrictions_saverule"],"datas"=>$_GET["datas"]);
			$main->save_smtpd_helo_restrictions();
			break;			
			
		case 0:
			$main->ClientArray[]=array("value"=>$_GET["TreeSmtpd_client_restrictions_saverule"],"datas"=>$_GET["datas"]);
			$main->save_smtpd_client_restrictions();			
			break;			
	
		default:
			$main->ClientArray[]=array("value"=>$_GET["TreeSmtpd_client_restrictions_saverule"],"datas"=>$_GET["datas"]);
			$main->save_smtpd_client_restrictions();			
			break;
	}
	
	
	if($pages->AutomaticConfig==true){
		$main=new main_cf();
		$main->save_conf_to_server();
	}
	echo $tpl->_ENGINE_parse_body('{success}');
	
}
function TreeSmtpd_client_restrictions_moverule(){
	$mny=new usersMenus();
	$tpl=new templates();	
	$main=new smtpd_restrictions();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	

		switch ($_GET["Sender"]) {
			case 1:
				$main->SenderArray=array_move_element($main->SenderArray,$main->SenderArray[$_GET["TreeSmtpd_client_restrictions_moverule"]],$_GET["array_direction"]);
				$main->save_smtpd_sender_restrictions();				
				break;
			case 2:
				$main->RecipientArray=array_move_element($main->RecipientArray,$main->RecipientArray[$_GET["TreeSmtpd_client_restrictions_moverule"]],$_GET["array_direction"]);
				$main->save_smtpd_recipient_restrictions();
				
			case 3:
				$main->HelloArray=array_move_element($main->HelloArray,$main->HelloArray[$_GET["TreeSmtpd_client_restrictions_moverule"]],$_GET["array_direction"]);
				$main->save_smtpd_helo_restrictions();				
		
			default:
  				$main->ClientArray=array_move_element($main->ClientArray,$main->ClientArray[$_GET["TreeSmtpd_client_restrictions_moverule"]],$_GET["array_direction"]);
		      		$main->save_smtpd_client_restrictions();				
				break;
		}
	
	if($pages->AutomaticConfig==true){
		$main=new main_cf();
		$main->save_conf_to_server();
		}
	echo DIV_SHADOW($pages->PagePostfixsmtpd_client_restrictions_table(0,$_GET["Sender"]),'windows');
	}
	
function TreeSmtpd_client_restrictions_deleterule(){
	$mny=new usersMenus();
	$tpl=new templates();	
	$main=new smtpd_restrictions();
	$pages=new HtmlPages();	
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	
	switch ($_GET["Sender"]) {
		case 1:
			unset($main->SenderArray[$_GET["TreeSmtpd_client_restrictions_deleterule"]]);
			$main->save_smtpd_sender_restrictions();
			break;
		case 0:
			unset($main->ClientArray[$_GET["TreeSmtpd_client_restrictions_deleterule"]]);
			$main->save_smtpd_client_restrictions();
			break;
			
		case 2:
			writelogs("unset RecipientArray(" . $_GET["TreeSmtpd_client_restrictions_deleterule"].")",__FUNCTION__,__FILE__);
			unset($main->RecipientArray[$_GET["TreeSmtpd_client_restrictions_deleterule"]]);
			$main->save_smtpd_recipient_restrictions();
			break;

		case 3:
			writelogs("unset HelloArray(" . $_GET["TreeSmtpd_client_restrictions_deleterule"].")",__FUNCTION__,__FILE__);
			unset($main->HelloArray[$_GET["TreeSmtpd_client_restrictions_deleterule"]]);
			$main->save_smtpd_helo_restrictions();
			break;			
					
		default:
			unset($main->ClientArray[$_GET["TreeSmtpd_client_restrictions_deleterule"]]);
			$main->save_smtpd_client_restrictions();
			break;
	}
	
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	echo DIV_SHADOW($pages->PagePostfixsmtpd_client_restrictions_table(0,$_GET["Sender"]),'windows');	
}
function PopUpPostFixInterfaces(){
	$pages=new HtmlPages();
	$content=$pages->PagePostfix_maincf_LocalNetwork();
	$html="<div id='rightInfos'>$content</div>";
	echo DIV_SHADOW($html,'windows');
	}
	
function PostFixBounceTemplate(){
	$template=$_GET['PostFixBounceTemplate'];
	$mny=new usersMenus();
	$tpl=new templates();	
	$main=new main_cf();
	$pages=new HtmlPages();		
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	
	$html="<H2 style='margin:2px'>{"."$template}</H2>
	" . Field_hidden('bounce_template_type',$template) ."
	<form name='form_$template'>
	<input type='hidden' id='save_bounce_template' name='save_bounce_template' value='$template'>
	<textarea name='text' style='width:99%;height:190px;margin:5px'>{$main->bounce_templates[$template]}</textarea>
	<div style='text-align:right;padding-right:10px'><input type='button' value='{submit}&raquo;' OnClick=\"javascript:ParseForm('form_$template','tree.listener.postfix.php',true);\"></div>
		</form>
	<div class=caption>{bounce_messages_templates_infos}</div>
	
	";
	echo DIV_SHADOW($html,'windows');	
	}
function save_bounce_template(){
	$template=$_GET["save_bounce_template"];
	if($template==null){echo "Template name is null!";exit();}
	$mny=new usersMenus();
	$tpl=new templates();		
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	$main=new main_cf();
	$main->bounce_templates[$template]=$_GET["text"];
	$main->save_conf();
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	echo $tpl->_ENGINE_parse_body('{success}');
	}
function save_bounce_maincf(){
	$mny=new usersMenus();
	$tpl=new templates();		
	if($mny->AsPostfixAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges');exit();}
	$main=new main_cf();
	unset($_GET["save_bounce_maincf"]);
	while (list ($num, $val) = each ($_GET) ){
		$main->main_array[$num]=strtolower($val);
	}
	$main->save_conf();
	if($pages->AutomaticConfig==true){$main->save_conf_to_server();}
	echo $tpl->_ENGINE_parse_body('{success}');	
	}

	
function postfix_add_network_v2(){
	
	$page=CurrentPageName();
	$main=new main_cf();
		
	if(is_array($main->array_mynetworks)){
		$net="<H4>{using}:</H4><table style='width:100%'>";

		while (list ($num, $val) = each ($main->array_mynetworks) ){
			$net=$net . "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>" . texttooltip($val,'{delete}',"postfix_delete_network_v2($num)") . "</td>
			</tr>";}
		$net=$net . "</table>";
		}
	
	
	
	$html="<div style='padding:5px;margin:5px'>
	<p class=caption>{mynetworks_single_text}</p>
	<FORM NAME=postfixaddnetworkv2>
		<input type='hidden' name='postfix_add_network_v2_save' value='yes'>
		<table style='width:90%' align='center'>
			<tr>
				<td align='right' nowrap><strong>{from_ip_address}:</strong></td>
				<td >" .Field_text('ip_addr') . "</td>
			</tr>
				<td align='right'><strong>{to_ip_address}:</strong></td>
				<td >" .Field_text('ip_addr2') . "</td>
			</tr>


	
			<tr><td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;'  OnClick=\"javascript:postfix_new_network();\"></td></tr>
			
			<tr><td colspan=2 class='caption'>{example}<li>1.0.0.0 {to_ip_address} 1.0.255.255 </li>
			<li>1.0.0.0 {to_ip_address} 1.3.255.255</li>
			<li>192.168.0.0 {to_ip_address}  192.168.0.255</li></td></tr>	
			
			<tr><td colspan=2 class='caption'>$net</td></tr>						
		</table>
	</form>
	</div>
	
	";
$tpl=new templates();		
echo $tpl->_ENGINE_parse_body($html);		
}
function postfix_add_network_v2_save(){
	$tpl=new templates();
	if($_GET["ip_addr"]==null){echo $tpl->_ENGINE_parse_body('{error} :{address} -> Null! ');return null;}
	if($_GET["ip_addr2"]==null){echo $tpl->_ENGINE_parse_body('{error} :{address} -> Null! ');return null;}
	include_once('ressources/class.tcpip.inc');
	$ip=new IP();
	if(!$ip->isValid($_GET["ip_addr"])){echo $tpl->_ENGINE_parse_body('{error} :{address} {bad_format} ->  ' . $_GET["ip_addr"]);return null;}
	$cdir=$ip->ip2cidr($_GET["ip_addr"],$_GET["ip_addr2"]);
	if($cdir==null){echo $tpl->_ENGINE_parse_body('{error} :{address} {bad_format} ->  ' . $_GET["ip_addr"] . "/" . $_GET["ip_addr2"]);return null;}
	$main=new main_cf();
	writelogs("save new $cdir for mynetwork settings",__FUNCTION__,__FILE__);
	$response=$main->add_my_networks($cdir);
	if($response<>null){echo $tpl->_ENGINE_parse_body("{error} :$response");return null;}
	writelogs("save postfix configuration",__FUNCTION__,__FILE__);
	$main->save_conf();
	writelogs("save postfix configuration done",__FUNCTION__,__FILE__);
	echo $tpl->_ENGINE_parse_body('{success}');
	}
	
function SmtpdRejectUnlistedRecipientLoad(){
	
	$main=new main_cf();
	$enable=Field_yesno_checkbox_img('save_smtpd_reject_unlisted_recipient',$main->main_array["smtpd_reject_unlisted_recipient"],'{enable_disable}');
	$html="
	<div style='padding:20px'>
	<h4>{smtpd_reject_unlisted_recipient}</h4>
	<div class=caption>{smtpd_reject_unlisted_recipient_text}</div>
	<form name='RejectUnlistedRecipient'>
		<table style='width:70%'>
			<tr>
			<td width=1% align='center'>$enable</td>
			<td><strong>{smtpd_reject_unlisted_recipient}</strong>
			</tr>
			<tr>
			<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveEnableSasl();\"></td>
			</tr>
		</table>
			</form>	
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function LoadSmtpdHeloRequired(){
	$main=new main_cf();
	$enable=Field_yesno_checkbox_img('save_smtpd_helo_required',$main->main_array["smtpd_helo_required"],'{enable_disable}');
	$html="
	<div style='padding:20px'>
	<h4>{smtpd_helo_required}</h4>
	<div class=caption>{smtpd_helo_required_text}</div>
	<form name='SmtpdHeloRequired'>
		<table style='width:70%'>
			<tr>
			<td width=1% align='center'>$enable</td>
			<td><strong>{smtpd_helo_required}</strong>
			</tr>
			<tr>
			<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveEnableHeloRequired();\"></td>
			</tr>
		</table>
			</form>	
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}
	
function SmtpdRejectUnlistedRecipientSave(){
	$main=new main_cf();
	$main->main_array["smtpd_reject_unlisted_recipient"]=$_GET["save_smtpd_reject_unlisted_recipient"];
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}

function SmtpdHeloRequiredSave(){
	$main=new main_cf();
	$main->main_array["smtpd_helo_required"]=$_GET["save_smtpd_helo_required"];
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
	
}

function PostFixCheckHashTable(){
	$field=$_GET["field"];
	$ldap_field=$_GET["PostFixCheckHashTable"];
	$page=CurrentPageName();
	$action_table=array(
	null=>"{select}",
	"OK"=>"ACCEPT",
	"REJECT"=>"REJECT","BCC"=>"BCC","DISCARD"=>"DISCARD","DUNNO"=>"DUNNO","FILTER"=>"FILTER","HOLD"=>"HOLD","PREPEND"=>"PREPEND","REDIRECT"=>"REDIRECT","WARN"=>"WARN","reject_unverified_sender"=>"{reject_unverified_sender}");
	
	
$html="
	<div style='padding:20px'>
	<h3>{".$field."}</h3>
	<div class=caption>{".$field."_text}</div>
	<form name='$field'>
	<input type='hidden' name='field' value='$field'>
	<input type='hidden' name='PostFixCheckHashTableSave' value='$ldap_field'>
	<H4>{add_entry}</h4>
	<table style='width:100%'>
	<tr>
	<td style='text-decoration:underline'><strong>" . icon_help('pattern_action') ."</strong>:</td>
	</tr>
	<tr>
	<td>" . Field_text('value',null) . "</td>
	</tr>
	<tr>
	<td style='text-decoration:underline'><strong>{action}</strong>:</td>
	</tr>	
	<tr>
	<td>" . Field_array_Hash($action_table,'action',null,"PostFixCheckHashTableSelectAction()",null,0,'width:100%') . "</td>
	</tr>	
	<tr>
	<td class=caption><div id='selected'></div></td>
	</tr>	
	<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('$field','$page',true);Load_postfix_security_rules_table();\"></td>
	</tr>		
	<tr>		
	</table>
	
	
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}
function PostFixCheckHashTableSelectAction(){
	$action=$_GET["PostFixCheckHashTableSelectAction"];
	
	switch ($action) {
		case "BCC":$field="<strong>bcc:</strong>&nbsp;".Field_text('action_option',null,'width:100%');break;
		case "DISCARD":$field="<strong>{optional_text}:</strong>&nbsp;".Field_text('action_option',null,'width:100%');break;		
		case "HOLD":$field="<strong>{optional_text}:</strong>&nbsp;".Field_text('action_option',null,'width:100%');break;				
		case "FILTER":$field="<strong><a href=\"javascript:PostFixCheckHashTableSelectFilterAction();\">{click_add_filter}</strong></a><br><br>".Field_text('action_option',null,'width:100%');break;
		case "PREPEND":$field="<strong><a href=\"javascript:PostFixCheckHashTableSelectPrependAction();\">{click_to_add_prepend}</strong></a><br><br>".Field_text('action_option',null,'width:100%');break;	
		case "REDIRECT":$field="<strong>emails:</strong>&nbsp;".Field_text('action_option',null,'width:100%');break;	
		case "WARN":$field="<strong>warn:</strong>&nbsp;".Field_text('action_option',null,'width:100%');break;	
		default:
			break;
	}
	
	$explain="{" . $action . "_help}<p>$field</p>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($explain);
}
function PostFixCheckHashTableSelectFilterAction(){
	$hash=array(null=>"{select}","service"=>"filter","smtp"=>"smtp");
	
	$field=Field_array_Hash($hash,'service_type',null,'PostFixCheckHashTableSelectFilterActionSelect()');
	$html="
	<div style='padding:20px'>
		<div class='caption'>{FILTER_help}</div>
		<table style='width:100%'>
		<tr>
			<td align='right'><strong>{select}:</strong>
			<td>$field</td>
		</tr>
		</table>
		<div id='FilterTableSelected'></div>
		<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' onClick=\"javascript:PostFixCheckHashTableSelectFilterActionSave()\"></div>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
function PostFixCheckHashTableSelectFilterActionSelect(){
	$selected=$_GET["PostFixCheckHashTableSelectFilterActionSelect"];
	$tpl=new templates();
	if($selected=="service"){
		$main=new main_cf();
		$h=$main->HashGetMasterCfServices();
		$h[null]='{select}';
		
		$field=Field_array_Hash($h,'filter',null,null,null,0,'width:100%');
		
		echo $tpl->_ENGINE_parse_body("<strong>{select_filter}</strong>:&nbsp;$field");
		return null;
		}
		
	if($selected=="smtp"){
		$resolve=Field_yesno_checkbox_img('MX_lookups','yes');
		$html="
		<table>
			<tr>
			<td nowrap><strong>{smtp_server_address}:</td>
			<td>" . Field_text('smtp_server_address')."</td>
			</tr>
			<tr>
			<td align='right'><strong>{smtp_server_port}:</td>
			<td>" . Field_text('smtp_server_port','25')."</td>
			</tr>
			<tr>
			<td align='right'>$resolve</td>
			<td><strong>{MX_lookups}</strong></td>
			</tr>			
		</table>";
		echo $tpl->_ENGINE_parse_body($html);
		return null;
	}
}
function PostFixCheckHashTableSelectFilterActionSave(){
	
if($_GET["PostFixCheckHashTableSelectFilterActionSave"]=="service"){
		$value=$_GET["filter"] . ":" . $_GET["filter"];
	}
	
if($_GET["PostFixCheckHashTableSelectFilterActionSave"]=="smtp"){
		$smtp_server_address=$_GET["smtp_server_address"];
		$smtp_server_port=$_GET["smtp_server_port"];
		$MX_lookups=$_GET["MX_lookups"];
		if($MX_lookups=="no"){
			$smtp_server_address="[$smtp_server_address]";
		}
		if($smtp_server_port<>"25"){
			$smtp_server_address=$smtp_server_address .":$smtp_server_port";
		}
	
		$value="smtp:$smtp_server_address";
	}	
	
	$html="<strong><a href=\"javascript:PostFixCheckHashTableSelectFilterAction();\">{click_add_filter}</strong></a><br><br>".Field_text('action_option',$value,'width:100%');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
function PostFixCheckHashTableSelectPrependAction(){
	
	$main=new main_cf();
	
	$h=$main->HashGetHeadersList();
	$h[null]='{select}';
	$field_headers=Field_array_Hash($h,'headers',null,null,null,0,'width:100%');
	
	$html="
	<div style='padding:20px'>
		<div class='caption'>{PREPEND_help}</div>
		<table style='width:100%'>
		<tr>
			<td align='right'><strong>{select}:</strong>
			<td>$field_headers</td>
		</tr>
		<tr>
			<td align='right'><strong>{prepend_text}:</strong>
			<td>" . Field_text('prepend_text',null,'width:100%')."</td>
		</tr>		
		</table>
		<div id='FilterTableSelected'></div>
		<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' onClick=\"javascript:PostFixCheckHashTableSelectPrependActionSave()\"></div>
	</div>";	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function PostFixCheckHashTableSelectPrependActionSave(){
	$datas=$_GET["PostFixCheckHashTableSelectPrependActionSave"] . ":" . $_GET["prepend_text"];
	$html="<strong><a href=\"javascript:PostFixCheckHashTableSelectPrependAction();\">{click_to_add_prepend}</strong></a><br><br>".Field_text('action_option',$datas,'width:100%');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}
function PostFixCheckHashTableSave(){
	writelogs('Save content for ' . $_GET["PostFixCheckHashTableSave"],__FUNCTION__,__FILE__);
	$main=new CheckRulesAccess();
	$main->AddRule($_GET["value"],$_GET["action"] . " " . $_GET["action_option"],$_GET["PostFixCheckHashTableSave"]);
	echo "ok";
	
}
function PostFixCheckHashTableDelete(){
	writelogs('Delete index' . $_GET["array_index"] ." for " . $_GET["PostFixCheckHashTableDelete"],__FUNCTION__,__FILE__);
	$main=new CheckRulesAccess();
	$main->DeleteRule($_GET["PostFixCheckHashTableDelete"],$_GET["array_index"]);
}









?>