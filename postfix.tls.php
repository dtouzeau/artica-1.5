<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");


$user=new usersMenus();
$tpl=new Templates();

if(isset($_GET["edit_certificate"])){
	if(!$user->AsAnAdministratorGeneric){header('location:users.index.php');die();}
	echo postfix_tls_certificate();
	exit;
}

if(isset($_GET["TreePostfixTLSCertificateInfosSubmit"])){
	if(!$user->AsAnAdministratorGeneric){header('location:users.index.php');die();}
	postfix_tls_certificate_save();exit;
	}


if($user->AsPostfixAdministrator==false){header('location:users.index.php');}

if(isset($_GET["fingerprint"])){postfix_relay_clientcerts_add();exit;}
if(isset($_GET["x_postfix_relay_clientcerts"])){echo postfix_relay_clientcerts_table();exit;}
if(isset($_GET["postfix_relay_clientcerts_del"])){postfix_relay_clientcerts_del();exit;}

if(isset($_GET["TLSLoggingLevel"])){TLSLoggingLevel();exit;}
if(isset($_GET["TLSLoggingSave"])){TLSLoggingLevelSave();exit;}
if(isset($_GET["TLSStartTLSOffer"])){TLSStartTLSOffer();exit;}
if(isset($_GET["smtp_tls_note_starttls_offer"])){smtp_tls_note_starttls_offer();exit;}
if(isset($_GET["TLSAddSMTPServer"])){TLSAddSMTPServer();exit;}
if(isset($_GET["TLSLoadTable"])){echo smtp_tls_policy_maps();exit;}
if(isset($_GET["TLSAddSMTPServerSave"])){TLSAddSMTPServerSave();exit;}
if(isset($_GET["TLSDeleteSMTPServer"])){TLSDeleteSMTPServer();exit;}
if(isset($_GET["tab"])){postfix_tls_switch();exit;}

if(isset($_GET["TLSCertificateGenerate"])){postfix_tls_certificate_generate();exit;}
if(isset($_GET["smtpd_tls_security_level_infos"])){smtpd_tls_security_level_infos();exit;}
if(isset($_GET["smtpd_tls_security_level"])){smtpd_tls_security_level();exit;}
if(isset($_GET["ActiveTLSMsgbox"])){ActiveTLSMsgbox();exit;}
if(isset($_GET["TestTLSMsgbox"])){TestTLSMsgbox();exit;}
if(isset($_GET["view_certificate"])){view_certificate_js();exit;}
if(isset($_GET["view_certificate_popup"])){view_certificate_popup();exit;}

if(isset($_GET["windows-certificate"])){windows_certificate_js();exit;}
if(isset($_GET["windows-certificate-popup"])){windows_certificate_popup();exit;}
if(isset($_GET["GeneratePostfixCertificate"])){GeneratePostfixCertificate();exit;}
if(isset($_GET["windows-certificate-table"])){echo windows_certificate_links();exit;}

if(isset($_GET["certificate-hosts-js"])){certificate_hosts_js();exit;}
if(isset($_GET["certificate-hosts-popup"])){certificate_hosts_popup();exit;}
if(isset($_GET["certificate-hosts-list"])){certificate_hosts_list();exit;}
if(isset($_GET["certificate-hosts-del"])){certificate_hosts_del();exit;}
if(isset($_GET["certificate-hosts-add"])){certificate_hosts_add();exit;}
if(isset($_GET["certificate-apply"])){certificate_apply();exit;}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["js-certificate"])){js_certificate();exit;}



js();

function certificate_apply(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ChangeSSLCertificate=yes");
}


function certificate_hosts_js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{hosts}','configure.server.php');
$give_server_name=$tpl->javascript_parse_text('{give_server_name}');
$interface_restarted=$tpl->javascript_parse_text("{interface_restarted}");
$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="	
	function LoadHostsCertificatePage(){
		YahooWin4('450','$page?certificate-hosts-popup','$title');
	}
	
var X_AddHostCertificate= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	LoadAjax('certificate_hosts','$page?certificate-hosts-list=yes');
	}	
	
	
	
	function AddHostCertificate(){
	 var host=prompt('$give_server_name');
	 if(host){
	 	  var XHR = new XHRConnection();
		  XHR.appendData('certificate-hosts-add',host);
		  document.getElementById('certificate_hosts').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  XHR.sendAndLoad('$page', 'GET',X_AddHostCertificate);	
		}
	
	}
	
	function DelHostCertificate(host){
 		  var XHR = new XHRConnection();
		  XHR.appendData('certificate-hosts-del',host);
		  document.getElementById('certificate_hosts').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  XHR.sendAndLoad('$page', 'GET',X_AddHostCertificate);		
	}
	
	function ApplySSLCertificate(){
		alert('$interface_restarted');
		var XHR = new XHRConnection();
		XHR.appendData('certificate-apply','yes');
	}
	
	LoadHostsCertificatePage();
	";
	
	echo $html;
}


function certificate_hosts_popup(){
$page=CurrentPageName();	
$html="
<div style='font-size:14px'>{certificate_hosts}</div>
<div style='text-align:right'>". button("{add}","AddHostCertificate()")."</div>
<div id='certificate_hosts' style='width:100%;height:120px;overflow:auto'></div>

<div style='text-align:right'>". button("{build_certificate}","ApplySSLCertificate()")."</div>
<script>LoadAjax('certificate_hosts','$page?certificate-hosts-list=yes');</script>

";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
	
}

function certificate_hosts_list(){
	$sock=new sockets();
	$tbl=explode("\n",$sock->GET_INFO("CertificateHostsList"));
	$html="<table style='width:99%' class=table_form>";
while (list ($num, $servername) = each ($tbl) ){
		if($servername==null){continue;}
		$html=$html.
		"<tr ". CellRollOver().">
		<td width=1%><img src='img/base.gif'></td>
		<td width=99% style='font-size:13px'>$servername</td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DelHostCertificate('$servername')")."</td>
		</tr>";
				
	}	
	$html=$html."</table>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
}

function certificate_hosts_del(){
	$sock=new sockets();
	$tbl=explode("\n",$sock->GET_INFO("CertificateHostsList"));
	while (list ($num, $servername) = each ($tbl) ){
		if($servername==$_GET["certificate-hosts-del"]){unset($tbl[$num]);continue;}
	}
	
	if(!is_array($tbl)){$conf=null;}else{
		reset($tbl);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)==null){continue;}
			$final_array[]=$val;
		}
		$conf=implode("\n",$final_array);
	}
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"CertificateHostsList");
}

function certificate_hosts_add(){
	$sock=new sockets();
	$tbl=explode("\n",$sock->GET_INFO("CertificateHostsList"));
	$tbl[]=$_GET["certificate-hosts-add"];
	if(!is_array($tbl)){$conf=null;}else{
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)==null){continue;}
			$final_array[]=$val;
		}
		$conf=implode("\n",$final_array);
	}
	$sock=new sockets();
	$sock->SaveConfigFile($conf,"CertificateHostsList");		
}

	
function js_certificate(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{ssl_certificate}&raquo;{certificate infos}','configure.server.php');

	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	$js_addons=file_get_contents("js/postfix-tls.js");
	
$html="	
	function LoadCertificatePage(){
		YahooWin3('550','$page?edit_certificate=yes','$title');
	}


	$js_addons
	LoadCertificatePage();
	";
	
	echo $html;
}
function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{tls_title}');

	
	$users=new usersMenus();
	if(!$users->AsMailBoxAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	$js_addons=file_get_contents("js/postfix-tls.js");
	
$html="	
	function LoadTLSPage(){
		RTMMail('700','$page?popup=yes','$title');
	}


	$js_addons
	LoadTLSPage();
	";
	
	echo $html;
}



function postfix_tls_tabs(){
	
	$tpl=new templates();
	if($_GET["tab"]==null){$_GET["tab"]=="tls_table";}
	$page=CurrentPageName();
	$array["index"]='{index}';
	$array["tls_table"]='{tls_table}';
	$array["relay_clientcerts"]='{client_certificate_fingerprints}';	
	$array["settings"]='{tls_settings}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		if(strlen($ligne)>25){$ligne=texttooltip(substr($ligne,0,22)."...",$ligne,null,null,1);}
		$html[]= "<li><a href=\"$page?main=yes&tab=$num\"><span>$ligne</span></a></li>\n";
		}
	return "
		<div id=TLS_TABLE style='width:100%;height:430px;overflow:auto;background-color:white;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#TLS_TABLE').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>
	
	
	
	";		
	
	
}

function postfix_tls_switch(){
if($_GET["tab"]==null){$_GET["tab"]="tls_table";}
	switch ($_GET["tab"]) {
		case "index":echo index();break;
		case "tls_table":echo smtp_tls_policy_maps();break;
		case "certificate_infos":echo postfix_tls_certificate();break;
		case "settings":echo postfix_tls_settings();break;
		case "relay_clientcerts":echo postfix_relay_clientcerts();break;
		default:echo smtp_tls_policy_maps($_GET["ou"]);break;
	}
		
	
}

function popup(){
	$html=postfix_tls_tabs();
 	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

	
function index(){
	
	$DEF_ICO_SSL_KEY=Buildicon64('DEF_ICO_SSL_KEY');
	
	$html="
		<H1>{tls_title}</H1>
		". RoundedLightWhite("
		<table style='width:100%'>
		<td valign='top' width=1%'>$DEF_ICO_SSL_KEY</td>
		<td valign='top'><p style='font-size:12px'>{TLS_EXPLAIN}</p></td>
		</tr>
		</table>")."
		<br>";
		

		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}




	
function smtp_tls_note_starttls_offer(){	
	$main=new main_cf();
	$main->main_array["smtp_tls_note_starttls_offer"]=$_GET["smtp_tls_note_starttls_offer"];
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
}
	
function TLSLoggingLevelSave(){
		
	$main=new main_cf();
	$main->main_array["smtpd_tls_loglevel"]=$_GET["TLSLoggingSave"];
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}
function smtp_tls_policy_maps(){
	$tpl=new templates();
	$html="
	<table style='width:100%'>
	<tr>
	<td align='center'><input type='button' value='{add_tls_smtp_server}&nbsp;&raquo;' OnClick=\"javascript:TLSAddSMTPServer()\"></td>
	</tr>
	</table>
	
	<H5 style='text-align:left'>{tls_table}</H5>
	<div class=caption>{tls_table_explain}</div>";
	$ldap=new clladp();
	$hahs=$ldap->hash_Smtp_Tls_Policy_Maps();
	if(!is_array($hahs)){return $tpl->_ENGINE_parse_body($html);}
	$main=new main_cf();
	$html=$html."<center><div style='width:550px'>
	<table style='width:100%'>
	<tr style='background-color:#CCCCCC'>
	<td><strong>&nbsp;</strong></td>
	<td><strong>{smtp_server}</strong></td>
	<td><strong>{smtp_port}</strong></td>
	<td><strong>{MX_lookups}</strong></td>
	<td><strong>{tls_level}</strong></td>
	<td><strong>&nbsp;</strong></td>
	</tr>
	";
	$domTool=new DomainsTools();
	while (list ($domain, $ligne) = each ($hahs) ){
		
		$arr=$domTool->transport_maps_explode($domain);
		$html=$html . "
		<tr>
	<td width=1%><img src='img/icon_mini_read.jpg'></td>
	<td><code><a href=\"javascript:TLSAddSMTPServer('$domain');\">{$arr[1]}</a></code></td>
	<td align='center'><code>{$arr[2]}</code></td>
	<td align='center'><code>{$arr[3]}</code></td>
	<td><strong>{$main->array_field_relay_tls["$ligne"]}</strong></td>
	<td align='center' width=1%>" . imgtootltip('x.gif','{delete}',"TLSDeleteSMTPServer('$domain');TLSLoadTable()") . "</td>
	</tr>";
		
	}
	$html=$html . "</table>";
	return $tpl->_ENGINE_parse_body($html);
}
function TLSAddSMTPServer(){
	
	$main=new main_cf();
	
	if($_GET["tls_smtp_server"]<>null){
		$ldap=new clladp();
		$tool=new DomainsTools();
		$h=$ldap->hash_Smtp_Tls_Policy_Maps();
		$relayT=$tool->transport_maps_explode($_GET["tls_smtp_server"]);
		$tls_value=$h[$_GET["tls_smtp_server"]];
		}
	
	$field=Field_array_Hash($main->array_field_relay_tls,'smtp_tls_policy_maps',$tls_value);	
	
$page=CurrentPageName();	
$html="<div style='padding:20px'>
	<H3>{tls_smtp_server}</H3>
	<form name='tls_smtp_server'>
<input type='hidden' name='TLSAddSMTPServerSave' value='yes'>
	<table style='width:100%'>
	<td align='right' nowrap><strong>{relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$relayT[1]) . "</td>	
	</tr>
	</tr>
	<td align='right' nowrap><strong>{smtp_port}:</strong></td>
	<td>" . Field_text('relay_port',$relayT[2]) . "</td>	
	</tr>	
	<tr>
	<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$relayT[3],'{enable_disable}')."</td>
	<td>{MX_lookups}</td>	
	</tr>
	</tr>
	<td align='right' nowrap valign='top'><strong>{tls_level}:</strong></td>
	<td>$field<div class='caption'>{use_tls_relay_explain}</div></td>	
	</tr>		
	<tr>
	<td align='right' class=caption colspan=2><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('tls_smtp_server','$page',true);TLSLoadTable();\"></td>
	</tr>		
	<tr>
	<td align='left' class=caption colspan=2><strong>{MX_lookups}</strong><br>{MX_lookups_text}</td>
	</tr>					
	</form>";

$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	

}
function TLSAddSMTPServerSave(){
	$ldap=new clladp();
	$tpl=new templates();
	if($_GET["relay_address"]==null){
		echo $tpl->_ENGINE_parse_body('{error_no_server_specified}');
		exit;
	}
	$ldap->smtp_tls_policy_maps_add($_GET["relay_address"],$_GET["relay_port"],$_GET["MX_lookups"],$_GET["smtp_tls_policy_maps"]);
	echo $tpl->_ENGINE_parse_body('{success}');
}
function TLSDeleteSMTPServer(){
	$ldap=new clladp();
	$base_dn="cn={$_GET["TLSDeleteSMTPServer"]},cn=smtp_tls_policy_maps,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($base_dn,true);
}

function postfix_tls_certificate(){
	include_once('ressources/class.ssl.certificate.inc');
	$ssl=new ssl_certificate();
	$array=$ssl->array_ssl;
	
	$cc=$array["artica"]["country"]."_".$array["default_ca"]["countryName_value"];
	$country_name=Field_array_Hash($ssl->array_country_codes,'country_code',$cc);
	$page=CurrentPageName();
	$users=new usersMenus();
	$sock=new sockets();
	$CertificateMaxDays=$sock->GET_INFO('CertificateMaxDays');
	if($CertificateMaxDays==null){$CertificateMaxDays='730';}
	
	
	if(isset($_GET["edit_certificate"])){$tabs=null;}
	

	
	if($users->POSTFIX_INSTALLED){
		$download_cert=button("{download_certificates}","Loadjs('$page?windows-certificate=yes')");
		}
	
	$CertificateInfos=button("{certificate_infos}","javascript:Loadjs('$page?view_certificate=yes')");
	$hosts=button("{hosts}","javascript:Loadjs('$page?certificate-hosts-js=yes')");
	
		
	$html="
	<form name='ffm_certificate'>
	<input type='hidden' id='TreePostfixTLSCertificateInfosSubmit' name='TreePostfixTLSCertificateInfosSubmit' value='yes'>
	<table>
		<tr><td align='right' colspan=2>$download_cert&nbsp;&nbsp;$hosts&nbsp;&nbsp;$CertificateInfos</tr>
		<tr><td align='left' colspan=2><strong>{countryName}</strong>:</td></tr>
		<tr><td colspan=2>$country_name</td></tr>
		<tr>
		<td class=legend>{stateOrProvinceName}:</strong></td>
		<td align='left'>" . Field_text("stateOrProvinceName",$array["default_ca"]["stateOrProvinceName_value"])  . "</td>
		</tr>
		<tr>
		<td class=legend>{localityName}:</strong></td>
		<td align='left'>" . Field_text("localityName",$array["default_ca"]["localityName_value"])  . "</td>
		</tr>	
		<tr>
		<td class=legend>{CertificateMaxDays}:</strong></td>
		<td align='left'>" . Field_text("CertificateMaxDays",$CertificateMaxDays,'width:40px')  . "&nbsp;{days}</td>
		</tr>	
		<tr>
		<td class=legend>{organizationName}:</strong></td>
		<td align='left'>" . Field_text("organizationName",$array["default_ca"]["organizationName_value"])  . "</td>
		</tr>				
		<tr>
		<td class=legend>{organizationalUnitName}:</strong></td>
		<td align='left'>" . Field_text("organizationalUnitName",$array["default_ca"]["organizationalUnitName_value"])  . "</td>
		</tr>	
		<tr>
		<td class=legend>{commonName}:</strong></td>
		<td align='left'>" . Field_text("commonName",$array["default_ca"]["commonName_value"])  . "</td>
		</tr>
		<tr>
		<td class=legend>{emailAddress}:</strong></td>
		<td align='left'>" . Field_text("emailAddress",$array["default_ca"]["emailAddress_value"])  . "</td>
		</tr>
		<tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<td class=legend>{smtpd_tls_key_file}:</strong></td>
		<td align='left'>" . Field_text("smtpd_tls_key_file",$array["postfix"]["smtpd_tls_key_file"])  . "</td>
		</tr>
		<tr>
		<td class=legend>{smtpd_tls_cert_file}:</strong></td>
		<td align='left'>" . Field_text("smtpd_tls_cert_file",$array["postfix"]["smtpd_tls_cert_file"])  . "</td>
		</tr>		
		<tr>
		<td class=legend>{smtpd_tls_CAfile}:</strong></td>
		<td align='left'>" . Field_text("smtpd_tls_CAfile",$array["postfix"]["smtpd_tls_CAfile"])  . "</td>
		</tr>		

			
		<tr>
		<td class=legend>{certificate password}:</strong></td>
		<td align='left'><input type='password' name='input_password' id='input_password' value=\"{$array["req"]["input_password"]}\"></td>
		</tr>									
		<tr><td colspan=2 align='right'>
		<hr>
			". button("{submit}","ParseForm('ffm_certificate','$page',true)"). "
		
		
	</table>
	</form>
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function postfix_tls_certificate_save(){
	$mny=new usersMenus();
	if($mny->AsPostfixAdministrator==false){return null;}
	include_once("ressources/class.ssl.certificate.inc");
	while (list ($num, $val) = each ($_GET) ){
		$_GET[$num]=replace_accents($_GET[$num]);		
	}
	
	reset($_GET);
	if(preg_match('#(.+?)_(.+)#',$_GET["country_code"],$re)){
		$countryName=$re[1];
		$country_code=$re[2];
	}
	
	
	$CertificateMaxDays=$_GET["CertificateMaxDays"];
	unset($_GET["CertificateMaxDays"]);
	$sock=new sockets();
	$sock->SET_INFO('CertificateMaxDays',$CertificateMaxDays);
	
	$cert=new ssl_certificate();
	$cert->array_ssl["server_policy"]["countryName"]="supplied";
	$cert->array_ssl["server_policy"]["stateOrProvinceName"]="supplied";
	$cert->array_ssl["server_policy"]["localityName"]="supplied";
	$cert->array_ssl["server_policy"]["organizationName"]="supplied";
	$cert->array_ssl["server_policy"]["organizationalUnitName"]="supplied";
	$cert->array_ssl["server_policy"]["commonName"]="supplied";
	$cert->array_ssl["server_policy"]["emailAddress"]="supplied";
	
	$cert->array_ssl["default_ca"]["countryName"]="Country Code";
	$cert->array_ssl["default_ca"]["countryName_value"]=$country_code;
	
	$cert->array_ssl["default_ca"]["stateOrProvinceName"]="State Name";
	$cert->array_ssl["default_ca"]["stateOrProvinceName_value"]=$_GET["stateOrProvinceName"];
	$cert->array_ssl["default_ca"]["localityName"]="Locality Name";
	$cert->array_ssl["default_ca"]["localityName_value"]=$_GET["localityName"];
	$cert->array_ssl["default_ca"]["organizationName"]="Organization Name";
	$cert->array_ssl["default_ca"]["organizationName_value"]=$_GET["organizationName"];
	$cert->array_ssl["default_ca"]["organizationalUnitName"]="Organizational Unit Name";
	$cert->array_ssl["default_ca"]["organizationalUnitName_value"]=$_GET["organizationalUnitName"];
	$cert->array_ssl["default_ca"]["commonName"]="Common Name";
	$cert->array_ssl["default_ca"]["commonName_value"]=$_GET["commonName"];
	$cert->array_ssl["default_ca"]["emailAddress"]="Email Address";
	$cert->array_ssl["default_ca"]["emailAddress_value"]=$_GET["emailAddress"];
	$cert->array_ssl["artica"]["country"]=$countryName;
	
	$cert->array_ssl["policy_anything"]["countryName"]="optional";
	$cert->array_ssl["policy_anything"]["stateOrProvinceName"]="optional";
	$cert->array_ssl["policy_anything"]["localityName"]="optional";
	$cert->array_ssl["policy_anything"]["organizationName"]="optional";
	$cert->array_ssl["policy_anything"]["organizationalUnitName"]="optional";
	$cert->array_ssl["policy_anything"]["commonName"]="optional";
	$cert->array_ssl["policy_anything"]["emailAddress"]="optional";
	
	
	$cert->array_ssl["user_policy"]["commonName"]="supplied";
	$cert->array_ssl["user_policy"]["emailAddress"]="supplied";
	
	$cert->array_ssl["req"]["input_password"]=$_GET["input_password"];
	$cert->array_ssl["req"]["output_password"]=$_GET["input_password"];
	
	$cert->array_ssl["v3_req"]["input_password"]=$_GET["input_password"];
	$cert->array_ssl["v3_req"]["output_password"]=$_GET["input_password"];
	
	$cert->array_ssl["v3_ca"]["subjectKeyIdentifier"]="hash";
	$cert->array_ssl["v3_ca"]["authorityKeyIdentifier"]="keyid:always,issuer:always";
	$cert->array_ssl["v3_ca"]["basicConstraints"]="CA:true";
	
	$cert->array_ssl["postfix"]["smtpd_tls_CAfile"]=$_GET["smtpd_tls_CAfile"];
	$cert->array_ssl["postfix"]["smtpd_tls_key_file"]=$_GET["smtpd_tls_key_file"];
	$cert->array_ssl["postfix"]["smtpd_tls_cert_file"]=$_GET["smtpd_tls_cert_file"];
	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{interface_restarted}");
	
	if(!$cert->SaveToLdap()){
		echo $cert->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{success}');
	}

	
}

function postfix_tls_settings(){
	$main=new main_cf();
	$hash[null]="{select}";
	$hash["none"]="{disable}";
	$hash["may"]="{tls_may}";
	$hash["encrypt"]="{tls_encrypt}";
	$page=CurrentPageName();
	$smtpd_tls_security_level=Field_array_Hash($hash,"smtpd_tls_security_level",$main->main_array["smtpd_tls_security_level"],"smtpd_tls_security_level_choose()");
	$field_offer=Field_yesno_checkbox_img('smtp_tls_note_starttls_offer',$main->main_array["smtp_tls_note_starttls_offer"],'{enable_disable}');
	$button_generate="<input type='button' OnClick=\"javascript:ActiveTLSMsgbox();\" value='{active_tls}&nbsp;&raquo'>";
	$button_tests="<input type='button' OnClick=\"javascript:TestTLSMsgbox();\" value='{test_TLS}&nbsp;&raquo'>";

//smtpd_tls_wrappermode
		
	$tls_info="
	<form name='ffmtls'>
	<table style='width:100%'>
	<tr>
		<td nowrap class=legend>{tls_label}:</strong>
		<td>$smtpd_tls_security_level</td>
	</tr>
	<tr>
		<td nowrap class=legend>{smtp_tls_mandatory_protocols}:</strong>
		<td>". Field_text('smtp_tls_mandatory_protocols',$main->main_array["smtp_tls_mandatory_protocols"],'width:120px',null,null,'{smtp_tls_mandatory_protocols_text}')."</td>
	</tr>	
	<tr>
	<tr><td colspan=2><span class=caption id='smtpd_tls_security_level_infos'>{{$main->main_array["smtpd_tls_security_level"]}_explain}</span></td></tr>
	
	</tr>
	<tr>
		<td nowrap class=legend>{smtpd_tls_ask_ccert}:</strong>
		<td>" . Field_yesno_checkbox_img('smtpd_tls_ask_ccert',$main->main_array["smtpd_tls_ask_ccert"],'{smtpd_tls_ask_ccert_text}')."</td>
	</tr>
	<tr>
		<td nowrap class=legend>{smtpd_tls_received_header}:</strong>
		<td>" . Field_yesno_checkbox_img('smtpd_tls_received_header',$main->main_array["smtpd_tls_received_header"],'{smtpd_tls_received_header_text}')."</td>
	</tr>			
	
	
	<tr><td colspan=2 align='right'>
		<input type='button' value='&nbsp;&nbsp;{edit}&nbsp;&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffmtls','$page',true);\"></td>
	
	
	</table>
	</form>";
	
$button_postfix="<center><table style='width:100%'><tr>
		<td width=50% align='center'>".RoundedLightGreen($tls_info) . "</TD>
		<td width=50% align='center' valign='top'>".RoundedLightGreen($button_generate."<br>$button_tests") . "</TD>
		</tr>
		</table></center>";
	
	$tls_offer=RoundedLightGrey("
	<form name='TLSlogging'>
	<div style='padding:20px'><h5>{smtp_tls_note_starttls_offer}</h5>
	<div class=caption>{smtp_tls_note_starttls_offer_text}</div>
		<div style='padding:5px;text-align:right'>
		<table style='width:100%'>
		<tr>
		<td align='right'>
		<strong>{enable} {smtp_tls_note_starttls_offer}:</strong></td>
		<td align='left'>$field_offer</td>
		</tr>
		</table>
		<br><br>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:TLSStartTLSOfferSave();\">
		</div>
	</div>
	</form>");
	
	
	$field=Field_array_Hash($main->array_field_tls_logging,'TLSLoggingSave',$main->main_array["smtpd_tls_loglevel"]);
	$tls_log=RoundedLightGrey("
	<form name='loglevel'>
	<div style='padding:20px'><h5>{tls_logging}</h5>
	<div class=caption>{tls_logging_intro}</div>
		<div style='padding:5px;text-align:right'>$field
		<br><br>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:TLSLoggingLevelSave();\">
		</div>
	</div>
	</form>");
	
	
$html="$button_postfix<br>$tls_offer<br>$tls_log";
	
$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function postfix_relay_clientcerts(){
	$page=CurrentPageName();
	$table2=postfix_relay_clientcerts_table();
	
	$table1="
	<form name='postfixrelayclientcerts'>
	<table style='width:100%'>
	<tr>
	<td align='right'><strong style='font-size:12px'>{fingerprint}:</strong></td>
	<td>" . Field_text('fingerprint',null,'width:100%') . "</td>
	</tr>
	<tr>
	<td align='right'><strong style='font-size:12px'>{host}</strong>:</td>
	<td>" . Field_text('host',null,'width:100%') . "</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"postfix_relay_clientcerts_add();\"></td>
	</table>
	</form>";
	
	
	$table1=RoundedLightGreen($table1);

	
	$html="
	<H5>{client_certificate_fingerprints}</H5>
	<div style='float:right;width:290px;'>$table1</div><p class=caption>{client_certificate_fingerprints_text}</p>
	<div id='postfix_relay_clientcerts'>" . postfix_relay_clientcerts_table() . "</div>
	";	
	
$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
}

function postfix_relay_clientcerts_table(){
	$cert=new relay_clientcerts();
	$table=$cert->ParseTable();
	if(!is_array($table)){return null;}
	
	$html="<table style='width:100%'>";
	
	while (list ($num, $val) = each ($table) ){
		$html=$html."
		<tr " . CellRollOver() . ">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong style='font-size:11px'>$num</td>
		<td><strong style='font-size:11px'>$val</td>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"postfix_relay_clientcerts_del('$num');")."</td>
		</tr>
		
		";
		
	}
	
	$tpl=new templates();
	return RoundedLightGrey($tpl->_ENGINE_parse_body($html."</table>"));
	
}
	
	
function postfix_tls_certificate_generate(){
	$mny=new usersMenus();
	if($mny->AsPostfixAdministrator==false){return null;}	
	include_once("ressources/class.ssl.certificate.inc");
	$tpl=new templates();
	$cert=new ssl_certificate();
	$cert->SaveToDisk();
}

function smtpd_tls_security_level_infos(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{{$_GET["smtpd_tls_security_level_infos"]}_explain}");
	
}

function smtpd_tls_security_level(){

	$sock=new sockets();
	$sock->SET_INFO('smtpd_tls_security_level',$_GET["smtpd_tls_security_level"]);
	$main=new main_cf();
	while (list ($num, $ligne) = each ($_GET) ){
		$main->main_array[$num]=$ligne;
		
	}
	$main->save_conf();
	reset($main->main_array);
	$main->EnableTLS();
	$tpl=new templates();
	$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
	echo $tpl->_ENGINE_parse_body('{success} line:'.__LINE__);
	
	
}

function ActiveTLSMsgbox(){
	switch ($_GET["ActiveTLSMsgbox"]) {
		case -1:ActiveTLSMsgbox_start();exit;break;
		case 0:ActiveTLSMsgbox_savecert();exit;break;
		case 1:ActiveTLSMsgbox_gencert();exit;break;
		case 2:ActiveTLSMsgbox_testcert();exit;break;
		case 3:ActiveTLSMsgbox_SavePostfix();exit;break;
	
		default:
			break;
	}
	
}

function ActiveTLSMsgbox_start(){
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<H5>{enable_tls_postfix}</H5>
		<div id='message_0'></div>
		<div id='message_1'></div>
		<div id='message_2'></div>
		<div id='message_3'></div>
	</td>
	<td valign='top' width=1%><div id='myimg'><img src='img/wait.gif'></span></td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
function ActiveTLSMsgbox_savecert(){
	postfix_tls_certificate_generate();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/ok24.png'></td>
	<td><strong>{save_cert_ok}</strong></td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function ActiveTLSMsgbox_gencert(){
	postfix_tls_certificate_generate();
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/ok24.png'></td>
	<td><strong>{generate_cert_ok}</strong></td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	}
	
function ActiveTLSMsgbox_testcert(){
	$cert=new ssl_certificate();
	$smtpd_tls_key_file=$cert->array_ssl["postfix"]["smtpd_tls_key_file"];
	$smtpd_tls_cert_file=$cert->array_ssl["postfix"]["smtpd_tls_cert_file"];
	$smtpd_tls_CAfile=$cert->array_ssl["postfix"]["smtpd_tls_CAfile"];	
	$res=true;
	$sock=new sockets();
	
	$t_smtpd_tls_key_file=trim($sock->getfile("FileExists:/etc/postfix/certificates/$smtpd_tls_key_file"));
	
	if($t_smtpd_tls_key_file=='{TRUE}'){
		$res_text=$res_text ."<div>$smtpd_tls_key_file ok</div>";
	}else{$res_text=$res_text ."<div>/etc/postfix/certificates/$smtpd_tls_key_file failed ! ($t_smtpd_tls_key_file)</div>";$res=false;}	
	
if(trim($sock->getfile("FileExists:/etc/postfix/certificates/$smtpd_tls_cert_file"))=='{TRUE}'){
		$res_text=$res_text ."<div>$smtpd_tls_cert_file ok</div>";
	}else{$res_text=$res_text ."<div>/etc/postfix/certificates/$smtpd_tls_cert_file failed ! </div>";$res=false;}	
	
if(trim($sock->getfile("FileExists:/etc/postfix/certificates/$smtpd_tls_CAfile"))=='{TRUE}'){
		$res_text=$res_text ."<div>$smtpd_tls_CAfile ok</div>";
	}else{$res_text=$res_text ."<div>/etc/postfix/certificates/$smtpd_tls_CAfile failed ! </div>";$res=false;}		
	


if($res==false){
	$html="<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/warning24.png'></td>
	<td><strong>{test_cert_failed}</strong>$res_text</td>
	</tr>
	</table>
	";	
}else{
$html="<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/ok24.png'></td>
	<td><strong>{test_cert_ok}</strong>$res_text</td>
	</tr>
	</table>
	";
}		

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function ActiveTLSMsgbox_SavePostfix(){
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
$html="<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/ok24.png'></td>
	<td><strong>{restarting_postfix}</strong><p class=caption>{you_can_close}</p></td>
	</tr>
	</table>
	";	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function TestTLSMsgbox(){
	$sock=new sockets();
	$datas=$sock->getfile('postfixtesttls');
	$datas=htmlspecialchars($datas);
	$datas=nl2br($datas);
	$datas=str_replace("\n",'',$datas);
	$datas=str_replace("<br /><br />","<br>",$datas);
	$html="<H5>TLS report</H5><br>
	<div style='padding:5px;margin:5px;font-size:9px'><code>$datas</code></div>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function postfix_relay_clientcerts_add(){
	$cert=new relay_clientcerts();
	$cert->add($_GET["fingerprint"],$_GET["host"]);
}
function postfix_relay_clientcerts_del(){
	$cert=new relay_clientcerts();
	$cert->del($_GET["postfix_relay_clientcerts_del"]);
}

function view_certificate_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{certificate_infos}');
	$html="
	function view_certificate(){
			YahooWin5(600,'$page?view_certificate_popup=yes','$title');
		
		}
		
		view_certificate();
	
	";
	echo $html;
	
}

function windows_certificate_js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{download_certificates}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

	
$html="
	function view_windows_certificate(){
			YahooWin5(650,'$page?windows-certificate-popup=yes','$title');
		
		}
		
	var x_{$prefix}GeneratePostfixCertificate= function (obj) {
		var response=obj.responseText;
		document.getElementById('download_windows_certificate').innerHTML=response;
		LoadAjax('windows_certs','$page?windows-certificate-table=yes');
		
		}			
		
	function GeneratePostfixCertificate(){
		var XHR = new XHRConnection();
		document.getElementById('download_windows_certificate').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/ajax-loader.gif\"></center>';
		XHR.appendData('GeneratePostfixCertificate','yes');
		XHR.sendAndLoad('$page', 'GET', x_{$prefix}GeneratePostfixCertificate);		
	
	}
	view_windows_certificate();
	";
	echo $html;	
	}
	
function windows_certificate_popup(){
	



$link=windows_certificate_links();	
	
	
$html="
<H1>{download_windows_certificate}</H1>

<div style='width:100%;text-align:right;margin:5px'><input type='button' OnClick=\"javascript:GeneratePostfixCertificate();\" value='{generate_certificate}&nbsp;&raquo;&raquo;'></div>
<div id='windows_certs'>$link</div>
<hr>
<div id='download_windows_certificate' style='height:120px;overflow:auto;font-size:11px;background-color:white;border:1px solid black;'>
</div>

";


$tpl=new templates();

echo $tpl->_ENGINE_parse_body($html);	
	
}

function windows_certificate_links(){

if(!is_file('certs/OutlookSMTP.p12')){
	
	$OutlookSMTPp12="
	<tr>
		<td width=1%><img src='img/32-key-red.png'></td>
		<td style='font-size:13px;font-weight:bold'>OutlookSMTP.p12</td>
	</tr>";
}else{
	$OutlookSMTPp12="
	<tr>
		<td width=1%><img src='img/32-key.png'></td>
		<td style='font-size:13px;font-weight:bold'><a href='certs/OutlookSMTP.p12' style='font-size:13px;font-weight:bold;text-decoration:underline'>OutlookSMTP.p12</a></td>
	</tr>";	
	
}

if(!is_file('certs/smtp.der')){
	
	$smtp_der="
	<tr>
		<td width=1%><img src='img/32-key-red.png'></td>
		<td style='font-size:13px;font-weight:bold'>smtp.der</td>
	</tr>";
}else{
	$smtp_der="
	<tr>
		<td width=1%><img src='img/32-key.png'></td>
		<td style='font-size:13px;font-weight:bold'><a href='certs/smtp.der' style='font-size:13px;font-weight:bold;text-decoration:underline'>smtp.der</a></td>
	</tr>";	
	
}

$html=RoundedLightWhite("
<table style='width:100%'>
$smtp_der
<tr><td colspan=2><hr></td></tr>
$OutlookSMTPp12
</table>");

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}


function view_certificate_popup(){
	$sock=new sockets();
	$tbl=unserialize(base64_decode(($sock->getFrameWork("cmd.php?certificate-viewinfos=yes"))));

	if(!is_array($tbl)){return null;}
	while (list ($num, $val) = each ($tbl) ){
		if(trim($val)==null){continue;}
		$val=str_replace("\t","&nbsp;&nbsp;&nbsp;",$val);
		
		if(preg_match('#^([a-zA-Z\s]+):(.*)#',$val,$re)){
			$val="<strong>{$re[1]}:</strong>&nbsp;{$re[2]}";
		}
		
		$t=$t."<div><code>$val</code></div>";
	}
	$t=RoundedLightWhite($t);
	$html="<H1>{certificate_infos}</H1>
	<div style='width:99%;height:350px;overflow:auto'>$t</div>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function GeneratePostfixCertificate(){
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-certificate=yes")));
	while (list ($num, $val) = each ($tbl) ){
		if(trim($val)==null){continue;}
		$html=$html . "<div style='padding-left:5px'>$val</div><hr>";
		
	}
	echo $html;
	
}


?>