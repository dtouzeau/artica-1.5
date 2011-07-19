<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
	die();
	}	
	
	
if(isset($_GET["buttons"])){echo popup_index();exit;}
if(isset($_GET["transport-table"])){routingTable(1);exit;}
if(isset($_GET["local-domain-table"])){LocalTable(1);exit;}
if(isset($_GET["relay-domain-table"])){RelayDomainsTable(1);exit;}
if(isset($_GET["relay-recipient-table"])){RelayRecipientsTable(1);exit;}
if(isset($_GET["relay-sender-table"])){SenderTableLoad(1);exit;}



	
if(isset($_GET["PostfixAddRoutingRuleTableSave"])){PostfixAddRoutingRuleTableSave();exit;}
if(isset($_GET["PostfixAddRoutingTable"])){PostfixAddRoutingRuleTable();exit();}
if(isset($_GET["PostfixAddRoutingTableSave"])){PostfixAddRoutingTableSave();exit;}
if(isset($_GET["PostfixAddRoutingLoadTable"])){echo routingTable();exit;}
if(isset($_GET["LoadRelayDomainsTable"])){echo RelayDomainsTable();exit;}
if(isset($_GET["PostFixDeleteRoutingTable"])){PostFixDeleteRoutingTable();exit;}

if(isset($_GET["relayhost"])){relayhost_tabs();exit;}
if(isset($_GET["relayhost-popup"])){relayhost();exit;}
if(isset($_GET["relayhost-sasl-auth"])){relayhost_sasl_auth();exit;}
if(isset($_GET["relayhost-sasl-config"])){relayhost_sasl_config();exit;}
if(isset($_GET["relayhostSave"])){relayhostSave();exit;}
if(isset($_GET["noanonymous"])){smtp_sasl_security_options_save();exit;}


if(isset($_GET["RelayHostDelete"])){RelayHostDelete();exit;}
if(isset($_GET["PostfixLocalLoadTable"])){echo LocalTable();exit;}
if(isset($_GET["PostfixSenderLoadTable"])){echo SenderTableLoad();exit;}

if(isset($_GET["SenderTable"])){SenderTable();exit();}
if(isset($_GET["SenderTableSave"])){SenderTableSave();exit;}
if(isset($_GET["SenderTableDelete"])){SenderTableDelete();exit;}

if(isset($_GET["PostfixDeleteRelayDomain"])){PostfixDeleteRelayDomain();exit;}
if(isset($_GET["PostfixAddRelayTable"])){PostfixAddRelayTable();exit;}

if(isset($_GET["PostfixDeleteRelayRecipient"])){PostfixDeleteRelayRecipient();exit;}
if(isset($_GET["PostfixRefreshRelayRecipient"])){echo RelayRecipientsTable();exit;}
if(isset($_GET["PostfixAddRelayRecipientTable"])){echo PostfixAddRelayRecipientTable();exit;}
if(isset($_GET["PostfixAddRelayRecipientTableSave"])){PostfixAddRelayRecipientTableSave();exit;}
if(isset($_GET["ArticaSyncTableDelete"])){ArticaSyncTableDelete();exit;}


if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){popup();exit;}
MainRouting();


function relayhost_tabs(){
	
	
	$page=CurrentPageName();
	
	$array["relayhost-popup"]='{config}';
	$array["relayhost-sasl"]="{SASL_STATUS}";
	$array["relayhost-sasl-auth"]="{usable_mechanisms}";
	$array["relayhost-sasl-config"]="{security_options}";
	while (list ($num, $ligne) = each ($array) ){
		if($num=="relayhost-sasl"){
			$tab[]="<li><a href=\"postfix.index.php?popup-auth-status=yes\"><span>$ligne</span></a></li>\n";
			continue;
		}
		$tab[]="<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
			
		}
	$tpl=new templates();
	
	$html="
		<div id='main_relayhost_config' style='background-color:white'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_relayhost_config').tabs({
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
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	
echo $html;	
	
	
}

function smtp_sasl_security_options_save(){
	$main=new maincf_multi("master","master");
	$main->SET_BIGDATA("smtp_sasl_security_options",serialize($_GET));
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sasl=yes");
	}

function relayhost_sasl_config(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi("master","master");
	$datas=unserialize($main->GET_BIGDATA("smtp_sasl_security_options"));
	
	if($datas["noanonymous"]==null){$datas["noanonymous"]=1;}
	
	
	
	$html="
	<div id='smtp_sasl_security_options_div'>
	<div class=explain>{smtp_sasl_security_options_text}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{smtp_sasl_security_options_noanonymous}</td>
		<td>". Field_checkbox("noanonymous",1,$datas["noanonymous"])."</td>
	</tr>
	<tr>
		<td class=legend>{smtp_sasl_security_options_noplaintext}</td>
		<td>". Field_checkbox("noplaintext",1,$datas["noplaintext"])."</td>
	</tr>	
	<tr>
		<td class=legend>{smtp_sasl_security_options_nodictionary}</td>
		<td>". Field_checkbox("nodictionary",1,$datas["nodictionary"])."</td>
	</tr>			
	<tr>
		<td class=legend>{smtp_sasl_security_options_nodictionary}</td>
		<td>". Field_checkbox("mutual_auth",1,$datas["mutual_auth"])."</td>
	</tr>			
	<tr>
		<td align='right'><hr>". button("{apply}","smtp_sasl_security_options_save()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var X_smtp_sasl_security_options_save= function (obj) {
			RefreshTab('main_relayhost_config');
		}	
	
		function smtp_sasl_security_options_save(){
			var XHR=XHRParseElements('smtp_sasl_security_options_div');
			document.getElementById('smtp_sasl_security_options_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',X_smtp_sasl_security_options_save);
		}
	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
}


function relayhost_sasl_auth(){
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?pluginviewer=yes")));
	
while (list ($num, $ligne) = each ($tbl)){
		if(trim($ligne)==null){continue;}
		$ligne=str_replace(" ","&nbsp;",$ligne);
		$ligne=str_replace("\t","<span style='padding-left:40px;'>&nbsp;</span>",$ligne);
		$t=$t."<div><code>$ligne</code></div>\n";
		
	}
	
	
	$html="
	<div style='width:100%;height:300px;overflow:auto'>$t</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}


function js(){
	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{transport_table}');	
$include=file_get_contents("js/postfix-transport.js");
$page=CurrentPageName();
$html="
	$include
	function LoadPostfixRountingTable(){
		YahooWinS(889,'$page?popup=yes','$title');
	
	}
	
var X_PostFixDeleteRoutingTable= function (obj) {
	$('#container-tabs').tabs( 'load' , 1 );
	}

var X_PostfixDeleteRelayDomain= function (obj) {
	$('#container-tabs').tabs( 'load' , 3 );
	}	
var X_PostfixDeleteRelayRecipient= function (obj) {
	$('#container-tabs').tabs( 'load' , 4 );
	}	
	
var X_SenderTableDelete= function (obj) {
	$('#container-tabs').tabs( 'load' , 5 );
	}

	

	
function PostFixDeleteRoutingTable(Routingdomain){
			var XHR = new XHRConnection();
			XHR.appendData('PostFixDeleteRoutingTable',Routingdomain);
			document.getElementById('routing-table').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',X_PostFixDeleteRoutingTable);	
		}

function PostfixDeleteRelayDomain(DomainToDelete){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixDeleteRelayDomain',DomainToDelete);
		document.getElementById('routing-table').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_PostfixDeleteRelayDomain);	
		}	
		
function SenderTableDelete(Routingdomain){
		var XHR = new XHRConnection();
		XHR.appendData('SenderTableDelete',Routingdomain);
		document.getElementById('routing-table').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_SenderTableDelete);
		}
		
function PostfixDeleteRelayRecipient(recipient){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixDeleteRelayRecipient',recipient);
		document.getElementById('routing-table').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_PostfixDeleteRelayRecipient);		
		}			
	
function PostfixRelayRecipientTableSave(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddRelayRecipientTableSave','yes');
		XHR.appendData('recipient',document.getElementById('recipient').value);
		document.getElementById('routing-table').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',X_PostfixDeleteRelayRecipient);
	}
	
var X_PostfixAddNewSenderTable= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		$('#container-tabs').tabs( 'load' , 5 );
		YahooWinHide();
	}		
function PostfixAddNewSenderTable(){
		var XHR = new XHRConnection();
		XHR.appendData('SenderTableSave','yes');
		XHR.appendData('email',document.getElementById('email').value);
		XHR.appendData('domain',document.getElementById('domain').value);
		XHR.appendData('relay_address',document.getElementById('relay_address').value);
		XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);
		XHR.sendAndLoad('$page', 'GET',X_PostfixAddNewSenderTable);
		
	}	

		
	
	LoadPostfixRountingTable();
	
";
	
	echo $html;
}

function popup(){
	
	echo MainRouting(1);
	
}


function popup_index(){
	
	
	
$add_routing_rule=Paragraphe("routing-rule.png","{add_routing_rule}","{add_routing_rule}","javascript:PostfixAddRoutingRuleTable()");	
$add_global_routing_rule=Paragraphe("relayhost.png","{add_global_routing_rule}","{add_global_routing_rule}","javascript:relayhost()");
$add_routing_relay_domain_rule=Paragraphe("routing-domain-relay.png","{add_routing_relay_domain_rule}","{add_routing_relay_domain_rule}","javascript:PostfixAddRelayTable()");
$add_routing_relay_recipient_rule=Paragraphe("acl-add-64.png","{add_routing_relay_recipient_rule}","{add_routing_relay_recipient_rule}","javascript:PostfixAddRelayRecipientTable()");
$add_sender_routing_rule=Paragraphe("sender-relay-table.png","{add_sender_routing_rule}","{add_sender_routing_rule}","javascript:SenderTable()");
$sync_artica=Paragraphe("sync-64.png","{smtp_sync_artica}","{smtp_sync_artica_text}","javascript:Loadjs('postfix.artica.smtp-sync.php')");




	$html="<table style='width:100%'>
			<tr>
				<td valign='top'><img src='img/bg_routing-250.png'></td>
				<td valign='top'><div class=explain>{transport_table_explain}</div></td>
			</tr>
			<tr>
			<td colspan=2>
				<table style='width:100%'>
				<tr>
					<td valign='top'>$add_routing_rule</td>
					<td valign='top'>$add_global_routing_rule</td>
					<td valign='top'>$add_routing_relay_domain_rule</td>
				</tr>
				<tr>
					<td valign='top'>$add_routing_relay_recipient_rule</td>
					<td valign='top'>$add_sender_routing_rule</td>
					<td valign='top'>$sync_artica</td>
				</tr>								
				</table>
		</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
		
}


function MainRouting($return=0){
$page=CurrentPageName();
$users=new usersMenus();
if($users->cyrus_imapd_installed){
	$local=" <li><a href=\"$page?local-domain-table=yes\"><span>{local_domains_table}</span></a></li>";	
}

$tabbarr="
<div id='container-tabs' style='width:99%;background-color:white'>
			<ul>
			<li><a href=\"$page?buttons=yes\"><span>{index}</span></a></li>
            <li><a href=\"$page?transport-table=yes\"><span>{transport_table}</span></a></li>
           	$local
			<li><a href=\"$page?relay-domain-table=yes\"><span>{relay_domains_table}</span></a></li>
			<li><a href=\"$page?relay-recipient-table=yes\"><span>{routing_relay_recipient2}</span></a></li>
			<li><a href=\"$page?relay-sender-table=yes\"><span>{sender_dependent_relayhost_maps_title2}</span></a></li>
			
			
			
			</ul>
		</div>
				<script>
				$(document).ready(function(){
					$('#container-tabs').tabs({
						load: function(event, ui) {\$('a', ui.panel).click(function() {\$(ui.panel).load(this.href);return false;});}
					});
				
				$('#container-tabs').tabs({ spinner: '{loading}' });

			});</script>		

";
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body($tabbarr);
			exit;

$rightbarr="
<div style='text-align:center'>" . RoundedLightWhite("

<table style='width:100%'>

<tr " . CellRollOver("PostfixAddRoutingRuleTable()").">
<td valign='top'>" . imgtootltip('routing-rule.png','{add_routing_rule}',"blur()")."</td>
<td valign='top'><h3 style='font-size:14px'>{add_routing_rule}</h3></td>
</tr>
<tr><td colspan=2><hr></td></tr>
<tr " . CellRollOver("relayhost()").">
<td valign='top'>" . imgtootltip('relayhost.png','{add_global_routing_rule}',"blur()")."</td>
<td valign='top'><h3 style='font-size:14px'>{add_global_routing_rule}</h3></td>
</tr>
<tr><td colspan=2><hr></td></tr>
<tr " . CellRollOver("PostfixAddRelayTable()").">
<td valign='top'>" . imgtootltip('routing-domain-relay.png','{add_routing_relay_domain_rule}',"blur()")."</td>
<td valign='top'><h3 style='font-size:14px'>{add_routing_relay_domain_rule}</h3></td>
</tr>
<tr><td colspan=2><hr></td></tr>
<tr " . CellRollOver("PostfixAddRelayRecipientTable()").">
<td valign='top'>" . imgtootltip('acl-add-64.png','{add_routing_relay_recipient_rule}',"blur()")."</td>
<td valign='top'><h3 style='font-size:14px'>{add_routing_relay_recipient_rule}</h3></td>
</tr>
<tr><td colspan=2><hr></td></tr>
<tr " . CellRollOver("SenderTable()").">
<td valign='top'>" . imgtootltip('sender-relay-table.png','{add_sender_routing_rule}',"blur()")."</td>
<td valign='top'><h3 style='font-size:14px'>{add_sender_routing_rule}</h3></td>
</tr>
</table>")."
</div>";	
	

$sender_table_title="
<table style='width:100%'>
<tr  ". CellRollOver("RtableExCol('sender_table')").">
<td width=1%><img src='img/link_a1.gif' id='img_sender_table'></td>
	<td style='text-align:left;font-size:14px;font-weight:bold'>{sender_dependent_relayhost_maps_title}</td>
</tr>
</table>
";

$routing_table_title="
<table style='width:100%'>
<tr  ". CellRollOver("RtableExCol('routing_table')").">
<td width=1%><img src='img/link_a1.gif' id='img_routing_table'></td>
	<td style='text-align:left;font-size:14px;font-weight:bold;overflow:auto;'>{transport_table}</td>
</tr>
</table>";


$local_domains_table_title="
<table style='width:100%'>
<tr  ". CellRollOver("RtableExCol('local_table')").">
<td width=1%><img src='img/link_a2.gif'  id='img_local_table'></td>
	<td style='text-align:left;font-size:14px;font-weight:bold;overflow:auto;'>{local_domains_table}</td>
</tr>
</table>";


$relay_domains_title="
<table style='width:100%'>
<tr  ". CellRollOver("RtableExCol('relay_domains')").">
<td width=1%><img src='img/link_a1.gif'  id='img_relay_domains'></td>
	<td style='text-align:left;font-size:14px;font-weight:bold'>{relay_domains_table}</td>
</tr>
</table>";

$relay_recipient_title="
<table style='width:100%'>

<tr  ". CellRollOver("RtableExCol('relay_recipient')").">
	<td width=1%><img src='img/link_a1.gif'  id='img_relay_recipient'></td>
	<td style='text-align:left;font-size:14px;font-weight:bold'>{routing_relay_recipient}</td>
</tr>
</table>";


$maintable="
<table width=100%'>
<tr>
<td valign='top'>


$sender_table_title
	<div id='sender_table' style='padding:5px;width:0px;height:0px;visibility:hidden;overflow:auto;'>
		".SenderTableLoad() . "
	</div>

$routing_table_title
	<div id='routing_table' style='margin:5px;width:0px;height:0px;visibility:hidden;;overflow:auto;'>
		".routingTable() . "
	</div>

$local_domains_table_title
	<div id='local_table' style='margin:5px;width:490px;overflow:auto;'>".LocalTable() . "</div>


$relay_domains_title
	<div id='relay_domains' style='margin:5px;width:0px;height:0px;visibility:hidden;overflow:auto;'>".RelayDomainsTable() . "</div>

$relay_recipient_title
	<div id='relay_recipient' style='margin:5px;width:0px;height:0px;visibility:hidden;overflow:auto;'>
		".RelayRecipientsTable() . "
	</div>
</center>
</td>
<td valign='top'>
</td>
</tr>
</table>";

	
$html="
<table style='width:100%'>
<td valign='top'>
		<table style='width:100%'>
			<tr>
				<td valign='top'><img src='img/bg_routing-250.png'></td>
				<td valign='top'><p class=caption>{transport_table_explain}</p></td>
			</tr>
		</table>" . RoundedLightWhite($maintable)."
</td>
<td valign='top'>
	$rightbarr
</td>
</tr>
</table>";



if($return==1){
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}


$cfg ["JS"][]='js/postfix-transport.js';
$tpl=new template_users('{transport_table}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;
}


function SenderTableLoad($echo=1){
$page=CurrentPageName();	
$style="style='border-bottom:2px dotted #8E8785;'";

$html="
<div style='text-align:right;margin:5px;margin-top:0px'>". button("{add_sender_routing_rule}","SenderTable()")."</div>
<table style='width:99%;padding:5px;border:1px dotted #8E8785;' align='center' class=table_form>
	<tr style='background-color:#CCCCCC'>
		<th>&nbsp;</th>
		<th><strong nowrap>{domain}</strong></th>
		<th><strong nowrap>&nbsp;</strong></td>
		<th align='center' nowrap><strong>{service}</strong></th>
		<th nowrap><strong>{relay}</strong></th>
		<th align='center' nowrap><strong>{port}</strong></th>
		<th align='center' nowrap><strong>{MX_lookups}</strong></th>
		<th>&nbsp;</td>
	</tr>";	

	$main=new main_cf();
	if($main->main_array["relayhost"]<>null){
		$tools=new DomainsTools();
		$main->main_array["relayhost"]="smtp:".$main->main_array["relayhost"];
		$relayT=$tools->transport_maps_explode($main->main_array["relayhost"]);
		$html=$html . "<tr>	
		<td width=1%><img src='img/icon_mini_read.gif'></td>
		<td style='font-size:12px'><code $roll><a href=\"javascript:relayhost();\">{others} (*)</a></strong></code></td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td align='center' style='font-size:12px' $roll><a href=\"javascript:relayhost();\">{$relayT[0]}</a></td>
		<td $roll style='font-size:12px'><code><a href=\"javascript:relayhost();\">{$relayT[1]}</a></code></td>
		<td align='center' style='font-size:12px'><code>{$relayT[2]}</code></td>
		<td align='center' style='font-size:12px'><code>{$relayT[3]}</code></td>
		<td align='center' style='font-size:12px' width=1%>" . imgtootltip('ed_delete.gif','{delete}',"RelayHostDelete();PostfixAddRoutingLoadTable()") . "</td>
		</tr>";
	}
	
	$sender=new sender_dependent_relayhost_maps();
	$h=$sender->sender_dependent_relayhost_maps_hash;
	$Tdomain=new DomainsTools();
	if(is_array($h)){
	while (list ($domain, $server) = each ($h) ){
		$array=$Tdomain->transport_maps_explode($server);
		if(substr($domain,0,1)=="@"){$domain=str_replace('@','',$domain);}
		$roll=CellRollOver(null,'{edit}');
		$html=$html . "<tr>
		<td width=1%><img src='img/icon_mini_read.gif'></td>
		<td style='font-size:12px'><code $roll><a href=\"javascript:SenderTable('$domain');\">$domain</a></strong></code></td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td align='center' style='font-size:12px' $roll><a href=\"javascript:SenderTable('$domain');\">{$array[0]}</a></td>
		<td $roll style='font-size:12px'><code><a href=\"javascript:SenderTable('$domain');\">{$array[1]}</a></code></td>
		<td align='center' style='font-size:12px'><code>{$array[2]}</code></td>
		<td align='center' style='font-size:12px'><code>{$array[3]}</code></td>
		<td align='center' style='font-size:12px' width=1%>" . imgtootltip('ed_delete.gif','{delete}',"SenderTableDelete('$domain');PostfixAddRoutingLoadTable()") . "</td>
		</tr>";
	}
	
	
	}
	$html=$html . "</table>";
	$html=RoundedLightWhite("<div style='width:99%;height:350px;overflow:auto' id='routing-table'>$html</div>");
	$tpl=new templates();
	if($echo==1){echo $tpl->_ENGINE_parse_body($html);exit;}
	return $tpl->_ENGINE_parse_body($html);		
	
}

function LocalTable($echo=0){
$page=CurrentPageName();	
$ldap=new clladp();
$h=$ldap->hash_get_local_domains();
if(!is_array($h)){return null;}

$html="

	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
				<th >&nbsp;</th>
				<th ><strong>{domain}</strong></th>
				<th ><strong>&nbsp;</strong></th>
				<th align='center' ><strong>-</strong></th>
				<th ><strong>-</strong></th>
				<th ><strong>-</strong></th>
				<th ><strong-</strong></th>
				<th style='font-size:12px'>&nbsp;</th>
			</tr>
		</thead>
		<tbody class='tbody'>";

while (list ($domain, $ligne) = each ($h) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$html=$html . "<tr class=$classtr>
		<td style='font-size:14px' width=1%><img src='img/internet.png'></td>
		<td style='font-size:14px'><code>$domain</a></strong></code></td>
		<td style='font-size:14px' width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:14px' align='center'>local</td>
		<td ><code></td>
		<td align='center' style='font-size:14px'><code></code></td>
		<td align='center' style='font-size:14px'><code></code></td>
		<td align='center' style='font-size:14px' width=1%></td>
		</tr>";
	}
$html=$html . "</tbody></table>";
$html=RoundedLightWhite("<div style='width:99%;height:350px;overflow:auto' id='routing-table'>$html</div>");
$tpl=new templates();
if($echo==1){echo $tpl->_ENGINE_parse_body($html);exit;}
return $tpl->_ENGINE_parse_body($html);		
}

function routingTable($echo=0){
	$page=CurrentPageName();	
	$ldap=new clladp();
	$transport=new DomainsTools();
$h=$ldap->hash_load_transport();
if(!is_array($h)){return null;}




$html="

<div style='text-align:right;margin:5px;margin-top:0px'>". button("{add_routing_rule}","PostfixAddRoutingRuleTable()")."</div>

	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
				<th>&nbsp;</th>
				<th><strong nowrap>{domain}</strong></th>
				<th><strong nowrap>&nbsp;</strong></th>
				<th align='center' nowrap><strong>&nbsp;</strong></th>
				<th nowraph><strong>{relay}</strong></th>
				<th align='center' nowrap><strong>{port}</strong></th>
				<th align='center' nowrap><strong>{MX_lookups}</strong></th>
				<th>&nbsp;</td>
			</tr>
		</thead>
		<tbody class='tbody'>";


	



	while (list ($domain, $ligne) = each ($h) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$array=$transport->transport_maps_explode($ligne);
		$delete= imgtootltip('delete-24.png','{delete}',"PostFixDeleteRoutingTable('$domain');");
		$edit="PostfixAddRoutingTable('$domain');";
		$edit=CellRollOver($edit,'{edit}');
		if($domain=="xspam@localhost.localdomain"){$delete="&nbsp;";$edit=null;}
		if($domain=="localhost.localdomain"){$delete="&nbsp;";$edit=null;}		
		
		$html=$html . "<tr class=$classtr>
		<td width=1%><img src='img/internet.png'></td>
		<td style='font-size:14px'><code $edit>$domain</strong></code></td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td align='center' style='font-size:14px' $edit>{$array[0]}</td>
		<td $edit style='font-size:14px'><code>{$array[1]}</code></td>
		<td align='center' style='font-size:14px'><code>{$array[2]}</code></td>
		<td align='center' style='font-size:14px'><code>{$array[3]}</code></td>
		<td align='center' style='font-size:14px' width=1%>$delete</td>
		</tr>";
	}
	
	LoadLDAPDBs();
	if(is_array($GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"])){
		while (list ($domain, $ligne) = each ($GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"]) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$array=$transport->transport_maps_explode($ligne);
				$html=$html . "<tr class=$classtr>
				<td width=1%><img src='img/internet.png'></td>
				<td style='font-size:14px'><code style='color:#676767'>$domain</strong></code></td>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td align='center' style='font-size:14px;color:#676767' >{$array[0]}</td>
				<td style='font-size:14px;color:#676767'><code>{$array[1]}</code></td>
				<td align='center' style='font-size:14px;color:#676767'><code>{$array[2]}</code></td>
				<td align='center' style='font-size:14px;color:#676767'><code>{$array[3]}</code></td>
				<td align='center' style='font-size:14px' width=1%>&nbsp;</td>
				</tr>";			
		}
		
		
	}
	
	
	
	

$html=$html . "</table>";
$html="<div style='width:99%;height:350px;overflow:auto' id='routing-table'>$html</div>";

$tpl=new templates();
if($echo==1){echo $tpl->_ENGINE_parse_body($html);exit;}
return $tpl->_ENGINE_parse_body($html);
	
}

function LoadLDAPDBs(){
	$main=new maincf_multi("master","master");
	$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));	
	if(is_array($databases_list)){
		while (list ($dbindex, $array) = each ($databases_list) ){
			if($array["enabled"]<>1){continue;}
			if($array["resolv_domains"]==1){$domains=$main->buidLdapDBDomains($array);}
			$GLOBALS["LDAPDBS"][$array["database_type"]][]="ldap:$targeted_file";
		}	
	}
}


function PostfixAddRoutingRuleTable(){
	$page=CurrentPageName();
	$main=new main_cf();
	$service=$main->HashGetMasterCfServices();
	$service["smtp"]="smtp";
	$ldap=new clladp();
	$ORG=$ldap->hash_get_ou(true);
	ksort($service);
	$ORG[null]='{select}';
	
	if(isset($_GET["domainName"])){
		
		$Table=$ldap->hash_load_transport();
		$t=new DomainsTools();
		$domainName=$_GET["domainName"];
		$line=$Table[$domainName];
		writelogs("LINE=$line for $domainName",__FUNCTION__,__FILE__);
		$conf=$t->transport_maps_explode($Table[$domainName]);		
		$relay_address=$conf[1];
		$smtp_port=$conf[2];
		$MX_lookups=$conf[3];
		$relay_service=$conf[0];
		$orgfound=$ldap->organization_name_from_transporttable($domainName);
	}
	
	$organization=Field_array_Hash($ORG,'org',$orgfound);
	
	
	if($relay_service==null){$relay_service="smtp";}
	
$html="
	<h1>{routing_rule}</H1>
	";
$form="
	<br>" . RoundedLightWhite("
	<form name='FFM3'>
	<input type='hidden' name='PostfixAddRoutingRuleTableSave' value='yes'>
	<table style='width:100%'>
	<tr>
	<td align='right' class=legend>{organization}:</strong></td>
	<td style='font-size:12px'>$organization</td>
	</tr>	
	<tr>
	<td align='right' class=legend>{pattern}:</strong></td>
	<td style='font-size:12px'>" . Field_text('domain',$domainName,'width:50%',null,null,'{transport_maps_pattern_explain}') . "</td>
	</tr>
	<tr>
	<td align='right' class=legend>{service}:</strong></td>
	<td style='font-size:12px'>" . Field_array_Hash($service,'service',$relay_service) . "</td>
	</tr>	
	<td align='right' nowrap class=legend>{address}:</strong></td>
	<td style='font-size:12px'>" . Field_text('relay_address',$relay_address) . "</td>	
	</tr>
	</tr>
	<td align='right' nowrap class=legend>{port}:</strong></td>
	<td style='font-size:12px'>" . Field_text('relay_port',$smtp_port) . "</td>	
	</tr>	
	<tr>
	<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$MX_lookups,'{enable_disable}')."</td>
	<td style='font-size:12px'>{MX_lookups}</td>	
	</tr>
	$sasl
	<tr>
	<td align='right' colspan=2>". button("{apply}","PostfixAddNewRoutingTable()")."</td>
	</tr>		
	<tr>
	<td align='left' colspan=2><hr><p class=caption{MX_lookups}</strong><br>{MX_lookups_text}</p></td>
	</tr>			
		
	</table>
	</FORM>
<script>
var X_PostfixAddNewRoutingTable= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		if(document.getElementById('container-tabs')){
			$('#container-tabs').tabs( 'load' ,1 );
		}
		YahooWinHide();
	}		
function PostfixAddNewRoutingTable(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddRoutingRuleTableSave','yes');
		XHR.appendData('org',document.getElementById('org').value);
		XHR.appendData('domain',document.getElementById('domain').value);
		XHR.appendData('service',document.getElementById('service').value);
		XHR.appendData('relay_address',document.getElementById('relay_address').value);
		XHR.appendData('relay_port',document.getElementById('relay_port').value);
		XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);				
		XHR.sendAndLoad('$page', 'GET',X_PostfixAddNewRoutingTable);
	}		
	
</script>	
	
	");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html$form");		
	
	
}

function PostfixAddRelayRecipientTable(){
$page=CurrentPageName();	
$html="
	<h1>{relay_recipient_maps}</H1>
	<p class=caption>{relay_recipient_maps_text}</p>
	" . RoundedLightWhite("
	<form name='FFMPostfixAddRelayRecipientTable'>
	<input type='hidden' name='PostfixAddRelayRecipientTableSave' value='yes'>
	<table style='width:100%'>
	<tr>
	<td align='right' class=legend>{recipient}:</strong></td>
	<td style='font-size:12px'>" . Field_text('recipient',$domainName) . "</td>
	</tr>
	<tr>
	<td align='right'  colspan=2>". button("{edit}","PostfixRelayRecipientTableSave()")."</td>
	</tr>			
	</table>
	</form>");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function PostfixAddRelayTable(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$org=$ldap->hash_get_ou(true);
	$org[null]='{select}';
	$listOrg=Field_array_Hash($org,'org',$org);
	$tls_table=$ldap->hash_Smtp_Tls_Policy_Maps();
	$smtp_server_line=$Table[$domainName];
	$smtp_server_line=str_replace('smtp:','',$smtp_server_line);
	$tls_value=$tls_table[$smtp_server_line];
	writelogs("server \"{$Table[$domainName]}\"=>$smtp_server_line=>".$tls_table[$smtp_server_line] ."($tls_value)",__FUNCTION__,__FILE__);
	
	$main=new main_cf();
	if($main->main_array["smtp_sasl_auth_enable"]=="yes"){
		$field=Field_array_Hash($main->array_field_relay_tls,'smtp_tls_policy_maps',$tls_value);
		$sasl="
		</tr>
			<td align='right' nowrap valign='top' class=legend>{tls_level}:</strong></td>
			<td style='font-size:12px'>$field<div class='caption'>{use_tls_relay_explain}</div></td>	
		</tr>";
		
	}
	
	
	if($smtp_port==null){$smtp_port=25;}
	
	$html="
	<h1>{add_routing_relay_domain_rule}</H1>
	<br>" . RoundedLightWhite("
	<form name='FFM3'>
	<input type='hidden' name='PostfixAddRoutingTableSave' value='yes'>
	<table style='width:100%'>
	<tr>
	<td align='right' class=legend>{organization}:</strong></td>
	<td style='font-size:12px'>$listOrg</td>
	</tr>	
	<tr>
	<td align='right' class=legend>{domainName}:</strong></td>
	<td style='font-size:12px'>" . Field_text('domain',$domainName) . "</td>
	</tr>
	<td align='right' nowrap class=legend>{relay_address}:</strong></td>
	<td style='font-size:12px'>" . Field_text('relay_address',$relay_address) . "</td>	
	</tr>
	<td align='right' nowrap class=legend>{smtp_port}:</strong></td>
	<td style='font-size:12px'>" . Field_text('relay_port',$smtp_port) . "</td>	
	</tr>	
	<tr>
	<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$MX_lookups,'{enable_disable}')."</td>
	<td style='font-size:12px'>{MX_lookups}</td>	
	</tr>
	$sasl
	<tr>
	<td align='right' colspan=2>". button("{apply}","PostfixAddNewRelayTable()")."</td>
	</tr>		
	<tr>
	<td align='left' colspan=2><hr><p class=caption>{MX_lookups}</strong><br>{MX_lookups_text}</p></td>
	</tr>			
		
	</table>
	</FORM>
	//PostfixAddRoutingRuleTableSave
<script>
var X_PostfixAddNewRelayTable= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		$('#container-tabs').tabs( 'load' ,3 );
		YahooWinHide();
	}		
function PostfixAddNewRelayTable(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddRoutingTableSave','yes');
		XHR.appendData('org',document.getElementById('org').value);
		XHR.appendData('domain',document.getElementById('domain').value);
		XHR.appendData('relay_address',document.getElementById('relay_address').value);
		XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);
		XHR.appendData('relay_port',document.getElementById('relay_port').value);
		if(document.getElementById('smtp_tls_policy_maps')){
			XHR.appendData('relay_port',document.getElementById('smtp_tls_policy_maps').value);
		}
		
		
		XHR.sendAndLoad('$page', 'GET',X_PostfixAddNewRelayTable);
		
	}		
	
</script>	
	");
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function PostfixAddRelayRecipientTableSave(){
	$ldap=new clladp();
	$ldap->AddRecipientRelayTable("{$_GET["recipient"]}");
	}

function PostfixAddRoutingTableSave(){
	$tpl=new templates();
	if($_GET["relay_port"]==null){$_GET["relay_port"]=25;}
	if($_GET["domain"]==null){echo $tpl->_ENGINE_parse_body("{error_no_domain_specified}");exit;}	
	if($_GET["relay_address"]==null){echo $tpl->_ENGINE_parse_body("{error_no_server_specified}");exit;}

	$ldap=new clladp();
	$ldap->AddDomainTransport($_GET["org"],$_GET["domain"],$_GET["relay_address"],$_GET["relay_port"],'relay',$_GET["MX_lookups"]);
	$ldap->smtp_tls_policy_maps_add($_GET["relay_address"],$_GET["relay_port"],$_GET["MX_lookups"],$_GET["smtp_tls_policy_maps"]);
	$ldap->AddRecipientRelayTable("@{$_GET["domain"]}");
	$ldap->AddDomainRelayTable($_GET["domain"]);
	$sock=new sockets();
	$sock->getFrameWork("services.php?postfix-single=yes");	
	}
function PostFixDeleteRoutingTable(){
	$ldap=new clladp();
	$dn=$ldap->WhereisDomainTransport($_GET["PostFixDeleteRoutingTable"]);
	$ldap->ldap_delete($dn,true);
	$tpl=new templates();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
	}
function relayhost(){
$main=new main_cf()	;
$page=CurrentPageName();
if($main->main_array["relayhost"]<>null){
	$relayhost=$main->main_array["relayhost"];
}else{
	$sock=new sockets();
	$relayhost=$sock->GET_INFO("PostfixRelayHost");
}	
if($relayhost<>null){
	$tools=new DomainsTools();
	$relayhost="smtp:".$main->main_array["relayhost"];
	$relayT=$tools->transport_maps_explode($relayhost);
}

if($relayT[1]<>null){
	$delete=imgtootltip("delete-48.png","{delete}","RelayHostDelete()");
}

$maps=new smtp_sasl_password_maps();
$pp=str_replace(".","\.",$relayT[1]);
$pp=str_replace("[","\[",$pp);
$pp=str_replace("]","\]",$pp);
while (list ($relaisa, $ligne) = each ($maps->smtp_sasl_password_hash) ){
	if(preg_match("#$pp#i",$relaisa)){
		if(preg_match("#^(.+?):(.+?)$#",$ligne,$re)){$username=$re[1];$password=$re[2];}
		break;
	}
}
$otherisp=Paragraphe("relais-isp.png","{USE_MY_ISP}","{USE_MY_IPS_EXAMPLES_TEXT}","javascript:Loadjs('postfix.index.php?use-my-isp=yes')");
$maptable=Paragraphe("tables-lock-64.png","(". count($maps->smtp_sasl_password_hash).") {items}:{passwords_table}","{passwords_table_text}",
"javascript:Loadjs('postfix.smptp.sasl.passwords.maps.php')");




$form="<div style='font-size:16px'>{relayhost}</div>
<div class=explain>{relayhost_text}</div>
<div style='text-align:right'><code style='font-size:14px;padding:3px'>$relayhost</code></div>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$otherisp<br>$maptable
		</td>
		<td valign='top'>
			
			<div id='relayhostdiv'>
					<table style='width:100%' >
					<tr>
						<td valign='top'>
						<input type='hidden' name='relayhostSave' value='yes'>
						<table style='width:100%' class=form>
							<td align='right' nowrap class=legend>{relay_address}:</strong></td>
							<td style='font-size:12px'>" . Field_text('relay_address',$relayT[1],"font-size:13px;padding:3px") . "</td>	
						</tr>
						</tr>
							<td align='right' nowrap class=legend>{smtp_port}:</strong></td>
							<td style='font-size:12px'>" . Field_text('relay_port',$relayT[2],"font-size:13px;padding:3px") . "</td>	
						</tr>	
						<tr>
							<td align='right' nowrap>" . Field_checkbox('MX_lookups',"yes",$relayT[3],'{enable_disable}')."</td>
							<td style='font-size:12px'>{MX_lookups}</td>	
						</tr>
						</tr>
							<td align='right' nowrap class=legend>{username}:</strong></td>
							<td style='font-size:12px'>" . Field_text('relay_username',$username,"font-size:13px;padding:3px") . "</td>	
						</tr>
						</tr>
							<td align='right' nowrap class=legend>{password}:</strong></td>
							<td style='font-size:12px'>" . Field_password('relay_password',$password,"font-size:13px;padding:3px;width:120px") . "</td>	
						</tr>						
						
						<tr>
							<td align='right' colspan=2 align='right'>". button("{apply}","PostfixSaveRelayHost()")."</td>
						</tr>		
								
						</td>
						</tr>
						</table>
					</td>
						<td valign='top'>$delete</td>
					</tr>
					</table>
			</div>
		</td>
	</tr>
</table>
<div class=explain>{MX_lookups}<br>{MX_lookups_text}</div>
</div>
<script>
var X_PostfixSaveRelayHost= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		$('#container-tabs').tabs( 'load' ,0 );
		YahooWinHide();
	}		
function PostfixSaveRelayHost(){
		var XHR = new XHRConnection();
		XHR.appendData('relayhostSave','yes');
		XHR.appendData('relay_address',document.getElementById('relay_address').value);
		XHR.appendData('relay_username',document.getElementById('relay_username').value);
		XHR.appendData('relay_password',document.getElementById('relay_password').value);
		if(document.getElementById('MX_lookups').checked){
			XHR.appendData('MX_lookups','yes');
		}else{
			XHR.appendData('MX_lookups','no');
		}
		
		XHR.appendData('relay_port',document.getElementById('relay_port').value);
		document.getElementById('relayhostdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',X_PostfixSaveRelayHost);
		
	}
function RelayHostDelete(){
		var XHR = new XHRConnection();
		XHR.appendData('RelayHostDelete','yes');
		XHR.sendAndLoad('$page', 'GET',X_PostfixSaveRelayHost);
		
	}
	
	
</script>";



$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$form");		
}
function relayhostSave(){
	
	
	
	if($_GET["relay_port"]==null){$_GET["relay_port"]=25;}
	$tpl=new templates();
	if($_GET["relay_address"]==null){
		echo $tpl->_ENGINE_parse_body("{error_no_server_specified}");
		exit;
	}	
	$tool=new DomainsTools();
	writepostfixlogs("Port={$_GET["relay_port"]} address={$_GET["relay_address"]}",__FUNCTION__,__FILE__);
	$data=$tool->transport_maps_implode($_GET["relay_address"],$_GET["relay_port"],'smtp',$_GET["MX_lookups"]);
	writepostfixlogs("Port={$_GET["relay_port"]} address={$_GET["relay_address"]}=$data",__FUNCTION__,__FILE__);
	$data=str_replace('smtp:','',$data);
	$main=new main_cf();
	$main->main_array["relayhost"]=$data;
	$sock=new sockets();
	$sock->SET_INFO("PostfixRelayHost",$data);
	$main->save_conf();
	
	if($_GET["relay_username"]<>null){
		$sals=new smtp_sasl_password_maps();
		$sals->add($data,$_GET["relay_username"],$_GET["relay_password"]);
	}
	$sock->getFrameWork("cmd.php?postfix-relayhost=yes");
	
	}
function RelayHostDelete(){
	$main=new main_cf();
	unset($main->main_array["relayhost"]);
	$sock=new sockets();
	$sock->SET_INFO('PostfixRelayHost',null);
	$main->save_conf();
	$sock->getFrameWork("cmd.php?postfix-relayhost=yes");

}


function RelayRecipientsTable($echo=0){
	
	
$html="
<div style='text-align:right;margin:5px;margin-top:0px'>". button("{add_routing_relay_recipient_rule}","PostfixAddRelayRecipientTable()")."</div>
<table style='width:99%;padding:5px;border:2px solid #8E8785;' align='center' class=table_form>
	<tr style='background-color:#CCCCCC'>
		<th style='font-size:12px'>&nbsp;</th>
		<th style='font-size:12px'><strong>{recipient}</strong></th>
		<th style='font-size:12px'><strong>&nbsp;</strong></th>
		<th align='center' style='font-size:12px'><strong>-</strong></th>
		<th style='font-size:12px'><strong>-</strong></td>
		<th align='center' style='font-size:12px'><strong>-</strong></th>
		<th align='center' style='font-size:12px'><strong-</strong></th>
		<th style='font-size:12px'>&nbsp;</th>
	</tr>";	
	$ldap=new clladp();
	$hash=$ldap->hash_get_relay_recipients();	
if(is_array($hash)){
while (list ($domain, $ligne) = each ($hash) ){
		$delete=imgtootltip("delete-24.png",'{delete}',"PostfixDeleteRelayRecipient('$domain')");
		if($domain=="@localhost.localdomain"){$delete=null;}
		$html=$html . "
		<tr>
		<td width=1%><img src='img/internet.png'></td>
		<td style='font-size:13px'><code>$domain</a></strong></code></td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td align='center' style='font-size:12px'>{relay}</td>
		<td ><code></td>
		<td align='center' style='font-size:13px'><code></code></td>
		<td align='center' style='font-size:13px'><code></code></td>
		<td align='center' style='font-size:13px' width=1%>$delete</td>
		</tr>";
	}
}
$html=$html . "</table>";
$html="<div style='width:99%;height:350px;overflow:auto' id='routing-table'>$html</div>".ArticaSyncTable($echo);

$tpl=new templates();
if($echo==1){echo $tpl->_ENGINE_parse_body($html);exit;}
return $tpl->_ENGINE_parse_body($html);
}

function ArticaSyncTableDelete(){
	$dn=base64_decode($_GET["ArticaSyncTableDelete"]);
	$ldap=new clladp();
	if(!$ldap->ldap_delete($dn)){echo $ldap->ldap_last_error;}
	
}


function ArticaSyncTable($echo=0){
	$page=CurrentPageName();
	$ldap=new clladp();
	$dn="cn=artica_smtp_sync,cn=artica,$ldap->suffix";
	$filter=array("cn");
	$sr = @ldap_search($ldap->ldap_connection,$dn,'(&(objectclass=ArticaSMTPSyncDB)(cn=*))',$filter);
	if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if(!is_array($hash)){return null;}
			
			for($i=0;$i<$hash["count"];$i++){
				$cn=$hash[$i]["cn"][0];
				if(preg_match("#(.+?):(.+)#",$cn,$re)){
					$mailserver=$re[1];
				}
				$html=$html."<tr>
							<td colspan=4><span style='font-size:14px;color:#005447;font-weight:bold'>{server}:$mailserver<hr></td>
					</tr>";
				$cn_dn="cn=table,cn=$cn,cn=artica_smtp_sync,cn=artica,$ldap->suffix";
				$search = @ldap_search($ldap->ldap_connection,$dn,'(&(objectclass=InternalRecipients)(cn=*))',array("cn"));
				if ($search) {
					
					$hash2=ldap_get_entries($ldap->ldap_connection,$search);
					for($t=0;$t<$hash2["count"];$t++){
						$cn_email=$hash2[$t]["cn"][0];
						$dn=base64_encode($hash2[$t]["dn"]);
						$html=$html."<tr ". CellRollOver().">
								<td style='font-size:12px;font-weight:bold'>$cn_email</td>
								<td width=1%><img src='img/fw_bold.gif'></td>
								<td style='font-size:12px'>$mailserver:25</td>
								<td width=1%>". imgtootltip("ed_delete.gif","{delete}","ArticaSyncTableDelete('$dn')")."</td>
							</tr>";
					}
				}
			}
		}

		$html="
		<H3 style='color:#005447'>{smtp_sync_artica}</H3>
		
		<div style='width:100%;height:320px;overflow:auto;margin-top:8px' id='ArticaSyncTableDiv'>
		<table style='width:99%'>
		$html
		</table>
		</div>
<script>
var X_ArticaSyncTableDelete= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('container-tabs');
	}		
function ArticaSyncTableDelete(dn){
		var XHR = new XHRConnection();
		XHR.appendData('ArticaSyncTableDelete',dn);
		document.getElementById('ArticaSyncTableDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',X_ArticaSyncTableDelete);
		
	}
</script>		
		";
$tpl=new templates();
if($echo==1){echo $tpl->_ENGINE_parse_body($html);exit;}
return $tpl->_ENGINE_parse_body($html);	
}


function RelayDomainsTable($echo=0){
	
	$ldap=new clladp();
	$hash=$ldap->hash_get_relay_domains();
	//$add_routing_relay_domain_rule=Paragraphe("routing-domain-relay.png","{add_routing_relay_domain_rule}","{add_routing_relay_domain_rule}","PostfixAddRelayTable()");
$html="
<div style='text-align:right;margin:5px;margin-top:0px'>". button("{add_routing_relay_domain_rule}","PostfixAddRelayTable()")."</div>
<table style='width:99%;padding:5px;border:2px solid #8E8785;' align='center' class=table_form>
	<tr style='background-color:#CCCCCC'>
		<th style='font-size:12px'>&nbsp;</th>
		<th style='font-size:12px'><strong>{domain}</strong></th>
		<th style='font-size:12px'><strong>&nbsp;</strong></th>
		<th align='center' style='font-size:12px'><strong>-</strong></th>
		<th style='font-size:12px'><strong>-</strong></td>
		<th align='center' style='font-size:12px'><strong>-</strong></th>
		<th align='center' style='font-size:12px'><strong-</strong></th>
		<th style='font-size:12px'>&nbsp;</th>
	</tr>";
if(is_array($hash)){
while (list ($domain, $ligne) = each ($hash) ){
	
		$delete=imgtootltip("ed_delete.gif",'{delete}',"PostfixDeleteRelayDomain('$domain')");
		if($domain=="localhost.localdomain"){$delete="&nbsp;";}
		$html=$html . "<tr>
		<td width=1%><img src='img/internet.png'></td>
		<td style='font-size:13px'><code>$domain</a></strong></code></td>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td align='center' style='font-size:12px'>{relay}</td>
		<td ><code></td>
		<td align='center' style='font-size:12px'><code></code></td>
		<td align='center' style='font-size:12px'><code></code></td>
		<td align='center' style='font-size:12px' width=1%>$delete</td>
		</tr>";
	}
}
$html=$html . "</table>";
$html=RoundedLightWhite("<div style='width:99%;height:350px;overflow:auto' id='routing-table'>$html</div>");

$tpl=new templates();
if($echo==1){echo $tpl->_ENGINE_parse_body($html);exit;}
return $tpl->_ENGINE_parse_body($html);
	
}


function SenderTable(){
	
$page=CurrentPageName();
$ldap=new clladp();
if($_GET["domainName"]<>null){
	if(strpos($_GET["domainName"],'@')==0){
		$domain_f="@".$_GET["domainName"];
		$domain=$_GET["domainName"];
	}else{
		$domain_f=$_GET["domainName"];
		$email=$domain_f;
	}
	
	$domaintools=new DomainsTools();
	$h=$ldap->hash_Sender_Dependent_Relay_host();
	$table=$domaintools->transport_maps_explode($h[$domain_f]);
	$relay_address=$table[1];
	$MX_lookups=$table[3];
}

	$main=new main_cf();
	if($main->main_array["smtp_sasl_auth_enable"]=="yes"){
		$tls_table=$ldap->hash_Smtp_Tls_Policy_Maps();
		$tls_value=$tls_table[$relay_address];
		writelogs("server \"{$Table[$domainName]}\"=>$smtp_server_line=>".$tls_table[$smtp_server_line] ."($tls_value)",__FUNCTION__,__FILE__);
		
		$field=Field_array_Hash($main->array_field_relay_tls,'smtp_tls_policy_maps',$tls_value);
		$sasl="
		</tr>
			<td align='right' nowrap valign='top'><strong>{tls_level}:</strong></td>
			<td style='font-size:12px'>$field<div class='caption'>{use_tls_relay_explain}</div></td>	
		</tr>";
		
	}


	$html="
	<H1>{sender_dependent_relayhost_maps_title}</H1>
	<p class=caption>{sender_dependent_relayhost_maps_text}</p>
	" . RoundedLightWhite("
	<form name='FFMSenderTable'>
	<input type='hidden' name='SenderTableSave' value='yes'>
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap class=legend>{email}:</strong></td>
	<td style='font-size:12px'>" . Field_text('email',$email) . "</td>	
	</tr>		
	<tr>
	<td align='right' nowrap class=legend>{domain}:</strong></td>
	<td style='font-size:12px'>{or} " . Field_text('domain',$domain) . "</td>	
	</tr>	
	<tr>
	<td align='right' nowrap class=legend>{relay_address}:</strong></td>
	<td style='font-size:12px'>" . Field_text('relay_address',$relay_address) . "</td>	
	</tr>
	<tr>
	<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$MX_lookups,'{enable_disable}')."</td>
	<td style='font-size:12px'>{MX_lookups}</td>	
	</tr>
	$sasl
	<tr>
	<td align='right' colspan=2>". button("{apply}","PostfixAddNewSenderTable()")."</td>
	</tr>		
	</table>
	</FORM>
	
	
	");

$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function SenderTableSave(){
	$tpl=new templates();
	if($_GET["domain"]==null && $_GET["email"]==null){echo $tpl->_ENGINE_parse_body('{error_give_email_or_domain}');exit;}
	if($_GET["domain"]<>null && $_GET["email"]<>null){echo $tpl->_ENGINE_parse_body('{error_choose_email_or_domain}');exit;}			
	if($_GET["relay_address"]==null){echo $tpl->_ENGINE_parse_body('{error_no_server_specified}');exit;}
	
	if($_GET["MX_lookups"]=="no"){$_GET["relay_address"]="[" . $_GET["relay_address"] . "]";}
	if($_GET["domain"]==null){$_GET["domain"]=$_GET["email"];}
	
	$sender=new sender_dependent_relayhost_maps();
	if(!$sender->Add($_GET["domain"],$_GET["relay_address"])){
		echo $sender->last_error;
		exit;
	}
	
	if(isset($_GET["smtp_tls_policy_maps"])){
		$ldap->smtp_tls_policy_maps_add($_GET["domain"],null,$_GET["MX_lookups"],$_GET["smtp_tls_policy_maps"]);
	}
	
		$sock=new sockets();
	$sock->getFrameWork("services.php?postfix-single=yes");
}
function SenderTableDelete(){
	$domain=$_GET["SenderTableDelete"];
	if(strpos($domain,'@')==0){$domain="@".$domain;}
	$ldap=new clladp();
	$dn="cn=$domain,cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($dn,false);
	$sock=new sockets();
	$sock->getFrameWork("services.php?postfix-single=yes");	
	}
	
function PostfixDeleteRelayDomain(){
	$domain=$_GET["PostfixDeleteRelayDomain"];
	$ldap=new clladp();
	$dn="cn=$domain,cn=relay_domains,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($dn,false);
	$sock=new sockets();
	$sock->getFrameWork("services.php?postfix-single=yes");	
	}
function PostfixDeleteRelayRecipient(){
	$domain=$_GET["PostfixDeleteRelayRecipient"];
	$ldap=new clladp();
	$dn="cn=$domain,cn=relay_recipient_maps ,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($dn,false);	
	$sock=new sockets();
	$sock->getFrameWork("services.php?postfix-single=yes");	
	}
	
	
function PostfixAddRoutingRuleTableSave(){
$MX_lookups=$_GET["MX_lookups"];
$domain=$_GET["domain"];
$org=$_GET["org"];
$relay_address=$_GET["relay_address"];
$relay_port	=$_GET["relay_port"];
$service=$_GET["service"];
$tpl=new templates();
if($relay_address==null){echo $tpl->_ENGINE_parse_body('{error_give_address}');return null;}
if($domain==null){echo $tpl->_ENGINE_parse_body('{error_give_pattern}');return null;}
if($org==null){echo $tpl->_ENGINE_parse_body('{error_no_organization}');return null;}
writelogs("organization for this transport table rule=$org",__FUNCTION__,__FILE__);
	$tool=new DomainsTools();
	$line=$tool->transport_maps_implode($relay_address,$relay_port,$service,$MX_lookups);
writelogs("$line",__FUNCTION__,__FILE__);	
	$ldap=new clladp();
	$ldap->AddTransportTable($domain,$line,$org);
	$sock=new sockets();
	$sock->getFrameWork("services.php?postfix-single=yes");

}


function TESTS(){
		$ldap=new clladp();
		$upd['cn'][0]='Sender_Dependent_Relay_host_Maps';
		$dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
		$upd['objectClass'][0]='senderDependentRelayhostMaps';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);	
		
	
}
	
	
?>	

