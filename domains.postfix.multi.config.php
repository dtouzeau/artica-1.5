<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.status.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["in-front-ajax"])){popup_js_front();exit;}
	if(isset($_GET["status"])){popup_status();exit;}
	if(isset($_GET["status-server"])){popup_status_server();exit;}
	if(isset($_GET["transport"])){popup_transport();exit;}
	if(isset($_GET["security"])){popup_security();exit;}
	if(isset($_GET["filters"])){filters_security();exit;}
	if(isset($_GET["enable_plugins"])){filters_security_save();exit;}
	
	if(isset($_GET["postfix-network"])){postfix_network();exit;}
	if(isset($_GET['ReloadNetworkTable'])){postfix_network_table();exit;}
	if(isset($_GET["PostfixAddMyNetwork"])){postfix_network_add();exit;}
	if(isset($_GET["PostFixDeleteMyNetwork"])){postfix_network_delete();exit;}
	
	if(isset($_GET["postfix-hostname"])){postfix_hostname();exit;}
	if(isset($_GET["VirtualHostNameToChange"])){postfix_hostname_save();exit;}
	
	if(isset($_GET["instance-reload"])){instance_perform_reload();exit;}
	if(isset($_GET["instance-restart"])){instance_perform_restart();exit;}
	if(isset($_GET["instance-flush"])){instance_perform_flush();exit;}
	if(isset($_GET["instance-reconfigure"])){instance_perform_reconfigure();exit;}
	if(isset($_GET["instance-kill"])){instance_perform_delete();exit;}

	tabs();

	
	
function popup_js_front(){
	$tpl=new templates();
	$page=CurrentPageName();
	if(isset($_GET["encoded"])){
		$ou=base64_decode($_GET["ou"]);
	}
	$html="
	
	$('#BodyContent').load('$page');
	
	";
	echo $html;	
}
	
function tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="network";};
	
	$hostname=$_GET["hostname"];
	$ou=$_GET["ou"];
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	
	$tpl=new templates();
	$filters_settings=$tpl->_ENGINE_parse_body('{filters_settings}');
	if(strlen($filters_settings)>25){$filters_settings=texttooltip(substr($filters_settings,0,22).'...',$filters_settings,null,null,1);}
	
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["transport"]='{transport_settings}';
	$array["security"]='{security_settings}';
	$array["filters"]=$filters_settings;
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname=$hostname&ou=$ou\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_multi_config_postfix style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_multi_config_postfix').tabs();
			
			
			});
		</script>";	
	
	
}

function filters_security(){
	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	$ou_encoded=base64_encode($_GET["ou"]);
	
	$KasxFilterEnabled=$sock->GET_INFO("KasxFilterEnabled");
	$kavmilterEnable=$sock->GET_INFO("kavmilterEnable");
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$EnableDKFilter=$sock->GET_INFO("EnableDKFilter");
	$EnableDkimMilter=$sock->GET_INFO("EnableDkimMilter");
	$EnableCluebringer=$sock->GET_INFO("EnableCluebringer");
	$users=new usersMenus();
	$sock=new sockets();
	if($users->kas_installed){
		if($KasxFilterEnabled==1){
			$array["APP_KAS3"]=$array_filters["APP_KAS3"];
		}
	}
	
	if($users->KAV_MILTER_INSTALLED){
		if($kavmilterEnable==1){
			$array["APP_KAVMILTER"]=$array_filters["APP_KAVMILTER"];
			
		}
	}
	
	if($users->MILTERGREYLIST_INSTALLED){
			$array["APP_MILTERGREYLIST"]=$array_filters["APP_MILTERGREYLIST"];
	}	
	
	if($users->AMAVIS_INSTALLED){
			$array["APP_AMAVIS"]=$array_filters["APP_AMAVIS"];
	}
	
	if($users->OPENDKIM_INSTALLED){
		if($EnableDKFilter==1){
			$array["APP_OPENDKIM"]=$array_filters["APP_OPENDKIM"];
		}
	}
	
	if($users->MILTER_DKIM_INSTALLED){
		if($EnableDkimMilter==1){
			$array["APP_MILTER_DKIM"]=$array_filters["APP_MILTER_DKIM"];
		}	
	}
	
if($users->CLUEBRINGER_INSTALLED){
		if($EnableCluebringer==1){
			$array["APP_CLUEBRINGER"]=$array_filters["APP_CLUEBRINGER"];
		}	
	}	
	
	if($EnableArticaSMTPFilter==1){
		$array["APP_ARTICA_FILTER"]=$array_filters["APP_ARTICA_FILTER"];
	}
	
	$array["APP_POSTFWD2"]=$array_filters["APP_POSTFWD2"];
	
	
	if(!is_array($array)){
		echo $tpl->_ENGINE_parse_body("
		<H2>{$_GET["hostname"]}</H2>
		<H3>{no_plugins_can_be_enabled}</H3>");
		exit;
	}
	
	$kavmilter=Paragraphe('icon-antivirus-64.png','{antivirus}','{antivirus_text}',
	"javascript:Loadjs('domains.edit.kavmilter.ou.php?ou=$ou_encoded')",null,210,null,0,true);
	$extensions_block=Paragraphe("bg_forbiden-attachmt-64.png","{attachment_blocking}","{attachment_blocking_text}","javascript:Loadjs('domains.edit.attachblocking.ou.php?ou=$ou_encoded')",null,210,null,0,true);
	$kas3x=Paragraphe('folder-caterpillar.png','{as_plugin}','{kaspersky_anti_spam_text}'
	,"javascript:Loadjs('domains.edit.kas.php?ou=$ou_encoded')",null,210,null,0,true);
	
	if($array["APP_KAS3"]==1){
		$tr[]=LocalParagraphe("as_plugin","kaspersky_anti_spam_text",
			"Loadjs('domains.edit.kas.php?ou=$ou_encoded')"
			,"folder-caterpillar-32.png");
	}
		
	
	if($array["APP_KAVMILTER"]==1){
			$tr[]=LocalParagraphe("antivirus","antivirus_text",
			"Loadjs('domains.edit.kavmilter.ou.php?ou=$ou_encoded')"
			,"icon-antivirus-32.png");
						
	}
	
	if($array["APP_MILTERGREYLIST"]==1){
		$tr[]=LocalParagraphe("APP_MILTERGREYLIST","APP_MILTERGREYLIST_TEXT",
			"Loadjs('domains.postfix.multi.milter-greylist.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')"
			,"32-milter-greylist.png");
			//milter.greylist.index.php
	}
	
	if($array["APP_AMAVIS"]==1){
		$tr[]=LocalParagraphe("APP_AMAVISD_NEW","APP_AMAVISD_NEW_ICON_TEXT",
			"Loadjs('domains.postfix.multi.amavis.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')"
			,"32-amavis.png");
			//milter.greylist.index.php
	}	
	
	
	if($array["APP_POSTFWD2"]==1){
		$tr[]=LocalParagraphe("APP_POSTFWD2","APP_POSTFWD2_TEXT",
			"Loadjs('postfwd2.php?ou=$ou_encoded&instance={$_GET["hostname"]}&byou=yes')"
			,"Firewall-Secure-32.png");		
		
	}else{
		$tr[]=LocalParagraphe("APP_POSTFWD2","APP_POSTFWD2_TEXT",
			"Loadjs('postfwd2.php?ou=$ou_encoded&instance={$_GET["hostname"]}&byou=yes')"
			,"Firewall-Secure-32-grey.png");			
		
	}
	
	
	
	
	
	$tr[]=LocalParagraphe("attachment_blocking","attachment_blocking_text",
	"Loadjs('domains.edit.attachblocking.ou.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')"
	,"bg_forbiden-attachmt-32.png");
	

	

	

	
	


$tables[]="<table style='width:100%'><tr>";
$t=0;
if(is_array($tr)){
	while (list ($key, $line) = each ($tr) ){
			$line=trim($line);
			if($line==null){continue;}
			$t=$t+1;
			$tables[]="<td valign='top'>$line</td>";
			if($t==2){$t=0;$tables[]="</tr><tr>";}
			
	}
	if($t<2){
		for($i=0;$i<=$t;$i++){
			$tables[]="<td valign='top'>&nbsp;</td>";				
		}
	}
}
				
$tables[]="</table>";
$plugins_conf_g=implode("\n",$tables);	

	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$VirtualHostNameToChange=$main->GET("VirtualHostNameToChange");	
	if($VirtualHostNameToChange<>null){$VirtualHostNameToChange="&nbsp; <span style='font-size:11px'>($VirtualHostNameToChange)</span>";}
	
	$html="<H2>{$_GET["hostname"]}$VirtualHostNameToChange</H2>
	<div id='pluginspostfixmulti'>
	<table style='width:100%'>
	<tr>
		<th colspan=2>{plugins}</th>
		<th>{enable}</th>
	</tr>";
	
	while (list ($num, $ligne) = each ($array) ){
		$html=$html."<tr ". CellRollOver().">
		<td style='width:1%'><img src='img/fw_bold.gif'></td>
		<td style='font-size:12px'>{{$num}}</td>
		<td style='width:1%'>" . Field_checkbox("$num",1,$array[$num],"PostfixMultiEnablePlugin('$num')")."</td>
		</tr>
		
		";
	}
	
	$html=$html."</table>
	</div>
	<hr>$plugins_conf_g
	<script>
		var x_PostfixMultiEnablePlugin= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_multi_config_postfix');
		}	
	
		function PostfixMultiEnablePlugin(product){
			var XHR = new XHRConnection();
			if(document.getElementById(product).checked){XHR.appendData(product,1);}else{XHR.appendData(product,0);}
			XHR.appendData('enable_plugins','yes');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			document.getElementById('pluginspostfixmulti').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_PostfixMultiEnablePlugin);	
		}
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function filters_security_save(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$tpl=new templates();
	$sock=new sockets();
	$array_filters=unserialize(base64_decode($main->GET_BIGDATA("PluginsEnabled")));
	if(!is_array($array_filters)){$array_filters=array();}
	while (list ($num, $ligne) = each ($_GET) ){
		$array_filters[$num]=$ligne;	
	}
	
	$main->SET_BIGDATA("PluginsEnabled",base64_encode(serialize($array_filters)));
	$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");
	$sock->getFrameWork("cmd.php?postfix-multi-mastercf={$_GET["hostname"]}");
	
}


function popup_security(){
	$tpl=new templates();
	$ou_encoded=base64_encode($_GET["ou"]);
	$users=new usersMenus();
	$tr[]=LocalParagraphe("SASL_TITLE","SASL_TEXT",
	"Loadjs('domains.postfix.multi.sasl.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')"
	,"folder-32-routing-secure.png");
	
	$tr[]=LocalParagraphe("SSL_ENABLE","SSL_ENABLE_TEXT",
	"Loadjs('domains.postfix.multi.ssl.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')"
	,"folder-32-routing-secure.png");

	$tr[]=LocalParagraphe("certificate_infos","certificate_infos_modify_text",
	"Loadjs('domains.postfix.multi.certificate.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')"
	,"32-key.png");		

	$tr[]=LocalParagraphe("messages_restriction","messages_restriction_text",
	"Loadjs('domains.postfix.multi.restriction.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')"
	,"folder-message-restriction-32.png");	
	
	$tr[]=LocalParagraphe("smtpd_client_restrictions_icon","smtpd_client_restrictions_icon_text",
	"Loadjs('domains.postfix.multi.client.restriction.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')"
	,"32-sender-check.png");	

	$tr[]=LocalParagraphe("global_smtp_rules","global_smtp_rules_text",
	"Loadjs('domains.postfix.multi.regex.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')"
	,"bg_regex-32.png");		
	
	if($users->POSTSCREEN_INSTALLED){	
		$tr[]=LocalParagraphe("PostScreen","POSTSCREEN_MINI_TEXT",
		"Loadjs('postscreen.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')"
	,"postscreen-32.png");
	}else{
		$tr[]=LocalParagraphe("PostScreen","POSTSCREEN_MINI_TEXT",null,"postscreen-32-grey.png");
	}

	
	$tr[]=LocalParagraphe("HIDE_CLIENT_MUA","HIDE_CLIENT_MUA_TEXT",
	"Loadjs('domains.postfix.hide.headers.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')"
	,"gomme-32.png");		
	
	
	
	

	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$VirtualHostNameToChange=$main->GET("VirtualHostNameToChange");	
	if($VirtualHostNameToChange<>null){$VirtualHostNameToChange="&nbsp; <span style='font-size:11px'>($VirtualHostNameToChange)</span>";}
	
$tables[]="<H2>{$_GET["hostname"]}$VirtualHostNameToChange</H2>";
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);


echo $tpl->_ENGINE_parse_body($html);	
	
	
}
	
function popup_transport(){
	if(GET_CACHED(__FILE__,__FUNCTION__,"{$_GET["hostname"]}&ou={$_GET["ou"]}")){return;}
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	
	$postfix_network=$tpl->_ENGINE_parse_body("{postfix_network}");
	$myhostname=$tpl->_ENGINE_parse_body("{myhostname}");
	$relay_host=$tpl->_ENGINE_parse_body("{postfix_network}");
	$ou_encoded=base64_encode($_GET["ou"]);
	

	
	$tr[]=LocalParagraphe("postfix_network",
	"postfix_network_text",
	"YahooWin5(634,'$page?postfix-network=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$postfix_network')",
	"folder-network-32.png");
	
	$tr[]=LocalParagraphe("smtp_virtual_hostname",
	"smtp_virtual_hostname_text",
	"YahooWin5(405,'$page?postfix-hostname=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$myhostname')",
	"32-bulle.png");	
	
	$tr[]=LocalParagraphe("relayhost_title",
	"relayhost_title_text",
	"Loadjs('domains.postfix.multi.relayhost.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')","32-relayhost.png");
	
	$tr[]=LocalParagraphe("smtp_generic_maps",
	"smtp_generic_maps_text",
	"javascript:Loadjs('postfix.smtp.generic.maps.php?ou=$ou_encoded')","generic-maps-32.png");	
	
	$tr[]=LocalParagraphe("title_postfix_tuning",
	"title_postfix_tuning_text",
	"javascript:Loadjs('domains.postfix.performances.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')",
	"32-settings.png");	

	
	$tr[]=LocalParagraphe("domain_throttle",
	"domain_throttle_text",
	"javascript:Loadjs('postfix.smtp.throttle.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')",
	"ecluse-32.png");			

	
	$tr[]=LocalParagraphe("ip_rotator",
	"ip_rotator_text",
	"javascript:Loadjs('postfix.ip.rotator.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')",
	"ip-rotator-32.png");	
	
	$tr[]=LocalParagraphe("InternalRouter",
	"InternalRouterText",
	"javascript:Loadjs('domains.postfix.aiguilleuse.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')",
	"32-nodes.png");	

	$tr[]=LocalParagraphe("postfix_tmpfs",
	"postfix_tmpfs_text",
	"javascript:Loadjs('domains.postfix.memory.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')",
	"memory-32.png");

	
	
	
	
	if($users->POSTFIX_LDAP_COMPLIANCE){
		$tr[]=LocalParagraphe("remote_users_databases",
		"remote_users_databases_text",
		"javascript:Loadjs('postfix.smtp.ldap.maps.php?ou=$ou_encoded&hostname={$_GET["hostname"]}')",
		"databases-add-48.png");
	}else{
		$tr[]=LocalParagraphe("remote_users_databases",
		"remote_users_databases_text",
		"",
		"databases-add-48-grey.png");		
		
	}		
		
	
	
	if($users->fetchmail_installed){
			$tr[]=LocalParagraphe("APP_FETCHMAIL_TINY",
			"APP_FETCHMAIL_TEXT",
			"Loadjs('domains.postfix.multi.fetchmail.php?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')",
			"fetchmail-rule-32.png");
		
	}
	
	$tr[]=LocalParagraphe("POSTFIX_SMTP_NOTIFICATIONS",
			"POSTFIX_SMTP_NOTIFICATIONS_TEXT",
			"Loadjs('Loadjs('postfix.notifs.php?hostname={$_GET["hostname"]}')",
			"recup-remote-mail-48.png");	
	
	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$VirtualHostNameToChange=$main->GET("VirtualHostNameToChange");	
	if($VirtualHostNameToChange<>null){$VirtualHostNameToChange="&nbsp; <span style='font-size:11px'>($VirtualHostNameToChange)</span>";}
	
$tables[]="<H2>{$_GET["hostname"]}$VirtualHostNameToChange</H2>";
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);


$html=$tpl->_ENGINE_parse_body($html);
echo $html;
SET_CACHED(__FILE__,__FUNCTION__,"{$_GET["hostname"]}&ou={$_GET["ou"]}",$html);	
}

function LocalParagraphe($title,$text,$js,$img){
		$js=str_replace("javascript:","",$js);
		$id=md5($js);
		$img_id="{$id}_img";
	$html="
	<table style='width:198px;'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($img,"{{$text}}","$js",null,$img_id)."</td>
	<td><strong style='font-size:12px'>{{$title}}</strong><div style='font-size:11px'>{{$text}}</div></td>
	</tr>
	</table>";
	

return "<div style=\"width:200px;margin:2px\" 
	OnMouseOver=\"javascript:ParagrapheWhiteToYellow('$id',0);this.style.cursor='pointer';\" 
	OnMouseOut=\"javascript:ParagrapheWhiteToYellow('$id',1);this.style.cursor='auto'\" OnClick=\"javascript:$js\">
  <b id='{$id}_1' class=\"RLightWhite\">
  <b id='{$id}_2' class=\"RLightWhite1\"><b></b></b>
  <b id='{$id}_3' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_4' class=\"RLightWhite3\"></b>
  <b id='{$id}_5' class=\"RLightWhite4\"></b>
  <b id='{$id}_6' class=\"RLightWhite5\"></b></b>

  <div id='{$id}_0' class=\"RLightWhitefg\" style='padding:2px;'>
   $html
  </div>

  <b id='{$id}_7' class=\"RLightWhite\">
  <b id='{$id}_8' class=\"RLightWhite5\"></b>
  <b id='{$id}_9' class=\"RLightWhite4\"></b>
  <b id='{$id}_10' class=\"RLightWhite3\"></b>
  <b id='{$id}_11' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_12' class=\"RLightWhite1\"><b></b></b></b>
</div>
";		
		
	
}



function postfix_hostname_save(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$main->SET_VALUE("VirtualHostNameToChange",$_GET["VirtualHostNameToChange"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-reconfigure={$_GET["hostname"]}");	
	}


function postfix_hostname(){
$page=CurrentPageName();
$tpl=new templates();

$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$VirtualHostnameToChange=$main->GET("VirtualHostNameToChange");


$html="
	<div class=explain>{smtp_virtual_hostname_text}</div>
	<div id='VirtualHostNameToChangeID' style='padding:10px'>
	<table style='width:90%' align='center'>
	<tr>
	<td align='right' valign='middle' nowrap class=legend style='font-size:14px'>{myhostname}:</strong></td>
	<td align='left' width=1%>" . Field_text('VirtualHostNameToChange',$VirtualHostnameToChange,'width:220px;font-size:14px;padding:3px') ."</td>
	<td valign='top' width=1% $styleadd>".help_icon('{myhostname_text}')."</td>
	<tr>
	<tr>
	<td colspan=4 align='right'>". button("{apply}","MultiPostfixHostNameSave()")."</td>
	</tr>
	</table>
	</div>
	
	
<script>
	
	var x_MultiPostfixHostNameSave=function (obj) {
		var tempvalue=obj.responseText;
		if (tempvalue.length>0){alert(tempvalue);} 
		YahooWin5Hide();
		RefreshTab('main_multi_config_postfix');
		RefreshPostfixMultiList();
	} 	
	
	function MultiPostfixHostNameSave(){
		var XHR = new XHRConnection();
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		XHR.appendData('VirtualHostNameToChange',document.getElementById('VirtualHostNameToChange').value);
		document.getElementById('VirtualHostNameToChangeID').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_MultiPostfixHostNameSave);
		}		
	
</script>
";


echo $tpl->_ENGINE_parse_body($html);	
	
}


function postfix_network(){
	//$mynetworks_table=mynetworks_table();
	$page=CurrentPageName();
$html="
<span style='font-size:16px;font-weight:bold'>{mynetworks_title}</span>
	<table style='width:100%;margin-top:8px' align='center'>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{give the new network}&nbsp;:</strong></td>
	<td align='left'>" . Field_text('mynetworks',null,'width:80%;padding:3px;font-size:13px',null,null,'{mynetworks_text}') ."</td>
	</tr>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{or} {give_ip_from_ip_to}&nbsp;:</strong></td>
	<td align='left'>" . Field_text('ipfrom',null,'width:100px;padding:3px;font-size:13px',null,'PostfixCalculateMyNetwork()') . 
Field_text('ipto',null,'width:100px;;padding:3px;font-size:13px',null,'PostfixCalculateMyNetwork()') ."</td>
	</tr>
	
	<tr><td colspan=2 align='right'>
		<hr>
		". button("{add}","PostfixAddMyNetwork()")."
	</td>
	</tr>
	</table>	
	<div id='network_table_multi' style='padding:10px'>$mynetworks_table</div>
	
	<script>
	
		var x_ReloadNetworkTable= function (obj) {
			ReloadNetworkTable();
			}	
				
	function PostfixAddMyNetwork(){
		PostfixCalculateMyNetwork();
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddMyNetwork',document.getElementById('mynetworks').value);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('PostfixAddMyNetwork',document.getElementById('mynetworks').value);
		document.getElementById('network_table_multi').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_ReloadNetworkTable);
	}	
	
		function ReloadNetworkTable(){
			LoadAjax('network_table_multi','$page?ReloadNetworkTable=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}');
			}
			
	var x_PostfixCalculateMyNetwork= function (obj) {
		var results=obj.responseText;
		document.getElementById('mynetworks').value=trim(results);
	}


	function PostfixCalculateMyNetwork(){
		if(!document.getElementById('ipfrom')){return false;}
		var ipfrom=document.getElementById('ipfrom').value;
		var ipto=document.getElementById('ipto').value;
		
		if(ipfrom.length>0){
			var ARRAY=ipfrom.split('\.');
			if(ARRAY.length>3){
				if(ipto.length==0){
					document.getElementById('ipto').value=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.255';
					
					}
					}else{return false}
		}else{return false;}
		document.getElementById('ipfrom').value=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.0';
		ipfrom=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.0';
		var XHR = new XHRConnection();
		XHR.appendData('mynet_ipfrom',ipfrom);
		XHR.appendData('mynet_ipto',document.getElementById('ipto').value);
		XHR.sendAndLoad('postfix.network.php', 'GET',x_PostfixCalculateMyNetwork);
		}	

	function PostFixDeleteMyNetwork(num){
		var XHR = new XHRConnection();
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		XHR.appendData('PostFixDeleteMyNetwork',num);
		document.getElementById('network_table_multi').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_ReloadNetworkTable);
		}		
			
	
	ReloadNetworkTable();
	</script>
	
	";
$tpl=new templates();
if($noecho==1){return $tpl->_ENGINE_parse_body($html);}

echo $tpl->_ENGINE_parse_body($html);

}

function postfix_network_add(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$nets=unserialize($main->GET_BIGDATA("mynetworks"));
	if(!is_array($nets)){
		$nets=array();
	}
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)\/([0-9]+)#",$_GET["PostfixAddMyNetwork"],$re)){
		$_GET["PostfixAddMyNetwork"]="{$re[1]}.{$re[2]}.{$re[3]}.0/{$re[5]}";
	}	
	
	$nets[]=$_GET["PostfixAddMyNetwork"];
	$main->SET_BIGDATA("mynetworks",serialize($nets));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-reconfigure={$_GET["hostname"]}");
	
}

function postfix_network_delete(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$nets=unserialize($main->GET_BIGDATA("mynetworks"));	
	unset($nets[$_GET["PostFixDeleteMyNetwork"]]);
	$main->SET_BIGDATA("mynetworks",serialize($nets));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-reconfigure={$_GET["hostname"]}");	
	}

function postfix_network_table(){
	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$nets=unserialize($main->GET_BIGDATA("mynetworks"));
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=3>{networks}</th>
	</tr>
</thead>
<tbody class='tbody'>";		

	if(is_array($nets)){
			while (list ($num, $val) = each ($nets) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if(trim($val)==null){continue;}
				$html=$html . "
				<tr class=$classtr>
					<td width=1%><img src='img/folder-network-32.png'></td>
					<td style='font-size:16px'>$val</td>
					<td  width=1%>" . imgtootltip('delete-32.png','{delete} {network}',"PostFixDeleteMyNetwork($num)") ."</td>
				</tr>";
			}
		}
	
	$html=$html . "
	</tbody>
	</table>
	</center>";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function popup_status_server(){
	 $status=new status(1);
	 $etat=$status->Postfix_multi_status($_GET["hostname"]);
	 $tpl=new templates();
	 echo $tpl->_ENGINE_parse_body($etat);
}

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
function popup_status(){
   
   if(GET_CACHED(__FILE__,__FUNCTION__,"{$_GET["hostname"]}&ou={$_GET["ou"]}")){return;}
   $tpl=new templates();
   $page=CurrentPageName();
   $ou_encoded=base64_encode($_GET["ou"]);
   $delete_postfix_instance_sure=$tpl->javascript_parse_text("{delete_postfix_instance_sure}\n{$_GET["hostname"]}");
   
 	$tr[]=LocalParagraphe("postfix_reload",
 	"postfix_reload_text",
 	"MultipleInstanceReload()",
 	"32-refresh.png");
 	
 	$tr[]=LocalParagraphe("postfix_restart",
 	"postfix_restart_text",
 	"MultipleInstanceRestart()",
 	"service-restart-32.png"); 	
 	
 	$tr[]=LocalParagraphe("postfix_reconfigure",
 	"postfix_reconfigure_text",
 	"MultipleInstanceReconfigure()",
 	"32-settings.png"); 	 	
 	
 	$tr[]=LocalParagraphe("flush_queue",
 	"flush_queue_text",
 	"MultipleInstanceFlush()",
 	"refresh-queue-32.png"); 	 	
 	
 	$tr[]=LocalParagraphe("delete_postfix_instance",
 	"delete_postfix_instance_text",
 	"MultipleInstanceDelete()",
 	"delete-32.png");  	
	
  	$tr[]=LocalParagraphe("pause_the_queue",
 	"pause_the_queue_text",
 	"Loadjs('postfix.freeze.queue.php?hostname={$_GET["hostname"]}&ou={$_GET["ou"]}')",
 	"pause-32.png");  

  	
  	

 	
 	
 $tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$toolbox=implode("\n",$tables);  

$html="
<div style='float:right'>". imgtootltip("20-refresh.png","{refresh}","MultipleInstanceRefreshEtat()")."</div>
<div id='postfix-status-etat'></div>

<div id='postfix-multi-toolbox' style='margin-top:3px'>
	$toolbox
</div>
<script>

	var x_MultipleInstanceReload=function (obj) {
		var tempvalue=obj.responseText;
		if (tempvalue.length>0){alert(tempvalue);} 
		RefreshTab('main_multi_config_postfix');
	} 

	function MultipleInstanceReload(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('instance-reload','{$_GET["hostname"]}');
		document.getElementById('postfix-multi-toolbox').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MultipleInstanceReload);
	}
	
	function MultipleInstanceRestart(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('instance-restart','{$_GET["hostname"]}');
		document.getElementById('postfix-multi-toolbox').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MultipleInstanceReload);
	}	
	
	function MultipleInstanceFlush(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('instance-flush','{$_GET["hostname"]}');
		document.getElementById('postfix-multi-toolbox').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MultipleInstanceReload);
	}

	function MultipleInstanceReconfigure(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('instance-reconfigure','{$_GET["hostname"]}');
		document.getElementById('postfix-multi-toolbox').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MultipleInstanceReload);
	}	
	
	
	
	
	
var x_MultipleInstanceDelete=function (obj) {
		var tempvalue=obj.responseText;
		if (tempvalue.length>0){alert(tempvalue);} 
		if(document.getElementById('org_main')){
			RefreshTab('org_main');
		}else{
			Loadjs('domains.postfix.multi.php?ou=$ou_encoded&encoded=yes&in-front-ajax=yes');
		}
	} 	
	
	function MultipleInstanceRefreshEtat(){
		LoadAjax('postfix-status-etat','$page?status-server=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
	
	}
	
	
	function MultipleInstanceDelete(){
		var a=confirm('$delete_postfix_instance_sure');
		if(a){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('instance-kill','{$_GET["hostname"]}');
			document.getElementById('postfix-multi-toolbox').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_MultipleInstanceDelete);
		}
			
	
	}
	
	MultipleInstanceRefreshEtat();
	
</script>

";
   
   
   
   
   $html=$tpl->_ENGINE_parse_body($html);
   echo $html;
   SET_CACHED(__FILE__,__FUNCTION__,"{$_GET["hostname"]}&ou={$_GET["ou"]}",$html);
   
	
}    
function instance_perform_reload(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-perform-reload={$_GET["hostname"]}");
	}
function instance_perform_restart(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-perform-restart={$_GET["hostname"]}");
	}
function instance_perform_flush(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-perform-flush={$_GET["hostname"]}");
	}	
	
function instance_perform_reconfigure(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-perform-reconfigure={$_GET["hostname"]}");	
}

function instance_perform_delete(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$main->remove_instance();
	
}
	


?>