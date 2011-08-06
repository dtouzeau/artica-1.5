<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	include_once('ressources/class.postfix-multi.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["config"])){config();exit;}
	if(isset($_GET["EnableDKFilter"])){save();exit;}
	if(isset($_GET["dns-key"])){dns_keys();exit;}
	if(isset($_GET["dns-key-view"])){dns_keys_display();exit;}
	if(isset($_GET["dns-tests"])){dns_tests_keys();exit;}
	if(isset($_GET["dns-tests-view"])){dns_tests_keys_display();exit;}
	if(isset($_GET["whitelist"])){whitelist();exit;}
	if(isset($_GET["whitelist-list"])){whitelist_list();exit;}							
	if(isset($_GET["whitelist-add"])){whitelist_add();exit;}
	if(isset($_GET["whitelist-del"])){whitelist_del();exit;}	

	
	
js();


function js(){
	
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_OPENDKIM}');

$html="

function APP_OPENDKIM_LOAD(){
	YahooWin3('730','$page?popup=yes','$title');
	
	}
	
APP_OPENDKIM_LOAD();
";


echo $html;	
	
}


function popup(){

	$page=CurrentPageName();
	$array["config"]="{main_settings}";
	$array["dns-key"]="{DNS_RECORDS}";
	$array["dns-tests"]="{DNS_RECORDS_TESTS}";
	$array["whitelist"]="{whitelist}";
	$tpl=new templates();
	
		while (list ($num, $ligne) = each ($array) ){
		
		$a[]="<li><a href=\"$page?$num=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}\"><span>". $tpl->_ENGINE_parse_body("$ligne")."</span></a></li>\n";
		
			
		}	
	
	
	$html="
	<div id='OPENDKIM_TABS' style='background-color:white;width:100%;height:600px;overflow:auto'>
	<ul>
		".implode("\n",$a)."
	</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#OPENDKIM_TABS').tabs({
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
	
	echo $html;
	
	
}

function config(){
	
	$sock=new sockets();
	$page=CurrentPageName();
	$actions=array("accept"=>"{accept}","discard"=>"{discard}","tempfail"=>"{tempfail}");
	$EnableDKFilter=$sock->GET_INFO("EnableDKFilter");
	$conf=unserialize(base64_decode($sock->GET_INFO("OpenDKIMConfig")));
	
	if($EnableDKFilter==null){$EnableDKFilter=0;}
	
	if($conf["On-BadSignature"]==null){$conf["On-BadSignature"]="accept";}
	if($conf["On-NoSignature"]==null){$conf["On-NoSignature"]="accept";}
	if($conf["On-DNSError"]==null){$conf["On-DNSError"]="tempfail";}
	if($conf["On-InternalError"]==null){$conf["On-InternalError"]="accept";}

	if($conf["On-Security"]==null){$conf["On-Security"]="tempfail";}
	if($conf["On-Default"]==null){$conf["On-Default"]="accept";}
	if($conf["ADSPDiscard"]==null){$conf["ADSPDiscard"]="1";}
	if($conf["ADSPNoSuchDomain"]==null){$conf["ADSPNoSuchDomain"]="1";}
	if($conf["DomainKeysCompat"]==null){$conf["DomainKeysCompat"]="0";}
	if($conf["OpenDKIMTrustInternalNetworks"]==null){$conf["OpenDKIMTrustInternalNetworks"]="1";}
	
	
	
	
	
//dkim_key('mondomaine.com', 'domaine', '/var/amavis/dkim/domaine.key.pem');	
	
	
	
	$html="
			<div class=explain>{dkim_about}<br>{dkim_about2}</div>
			<div id='dkim-form'>
			<table style='width:100%'>
				<tr>
					<td class=legend style='font-size:13px'>{enable_opendkim}</td>
					<td>". Field_checkbox("EnableDKFilter",1,$EnableDKFilter,"SaveEnableDKFilter_silent()")."</td>
					<td width=1%>&nbsp;</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{OpenDKIMTrustInternalNetworks}</td>
					<td>". Field_checkbox("OpenDKIMTrustInternalNetworks",1,$conf["OpenDKIMTrustInternalNetworks"])."</td>
					<td width=1%>". help_icon("{OpenDKIMTrustInternalNetworks_text}")."</td>
				</tr>					
				<tr>
					<td class=legend style='font-size:13px'>{DomainKeysCompat}</td>
					<td>". Field_checkbox("DomainKeysCompat",1,$conf["DomainKeysCompat"])."</td>
					<td width=1%>". help_icon("{DomainKeysCompat_text}")."</td>
				</tr>				
				<tr>
					<td class=legend style='font-size:13px'>{ADSPDiscard}</td>
					<td>". Field_checkbox("ADSPDiscard",1,$conf["ADSPDiscard"])."</td>
					<td width=1%>". help_icon("{ADSPDiscard_text}")."</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{ADSPNoSuchDomain}</td>
					<td>". Field_checkbox("ADSPNoSuchDomain",1,$conf["ADSPDiscard"])."</td>
					<td width=1%>". help_icon("{ADSPNoSuchDomain_text}")."</td>
				</tr>												
				<tr>
					<td class=legend style='font-size:13px'>{On-Default}</td>
					<td>". Field_array_Hash($actions,"On-Default",$conf["On-Default"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1%>&nbsp;</td>
				</tr>					
				<tr>
					<td class=legend style='font-size:13px'>{On-BadSignature}</td>
					<td>". Field_array_Hash($actions,"On-BadSignature",$conf["On-BadSignature"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1%>&nbsp;</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{On-NoSignature}</td>
					<td>". Field_array_Hash($actions,"On-NoSignature",$conf["On-NoSignature"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1%>&nbsp;</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{On-DNSError}</td>
					<td>". Field_array_Hash($actions,"On-DNSError",$conf["On-DNSError"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1%>&nbsp;</td>
				</tr>
				<tr>
					<td class=legend style='font-size:13px'>{On-Security}</td>
					<td>". Field_array_Hash($actions,"On-Security",$conf["On-Security"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1%>&nbsp;</td>
				</tr>													
				<tr>
					<td class=legend style='font-size:13px'>{On-InternalError}</td>
					<td>". Field_array_Hash($actions,"On-InternalError",$conf["On-InternalError"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1%>&nbsp;</td>
				</tr>				
				
				<tr>
					<td colspan=2 align='right'><hr>". button("{apply}","SaveOpenDKIMForm()")."</td>
				</tr>			
			</table>
			</div>
			
	
<script>
var x_SaveOpenDKIMForm= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
	RefreshTab('OPENDKIM_TABS');	
}	

function SaveEnableDKFilter_silent(){
	SaveOpenDKIMForm();		
	}
	
		
function SaveOpenDKIMForm(){
	var XHR = new XHRConnection();
	if(document.getElementById('EnableDKFilter').checked){XHR.appendData('EnableDKFilter','1');}else{XHR.appendData('EnableDKFilter','0');}
	if(document.getElementById('ADSPDiscard').checked){XHR.appendData('ADSPDiscard','1');}else{XHR.appendData('ADSPDiscard','0');}
	if(document.getElementById('ADSPNoSuchDomain').checked){XHR.appendData('ADSPNoSuchDomain','1');}else{XHR.appendData('ADSPNoSuchDomain','0');}
	if(document.getElementById('DomainKeysCompat').checked){XHR.appendData('DomainKeysCompat','1');}else{XHR.appendData('DomainKeysCompat','0');}
	if(document.getElementById('OpenDKIMTrustInternalNetworks').checked){XHR.appendData('OpenDKIMTrustInternalNetworks','1');}else{XHR.appendData('OpenDKIMTrustInternalNetworks','0');}
	
	
	
	XHR.appendData('On-Default',document.getElementById('On-Default').value);
	XHR.appendData('On-BadSignature',document.getElementById('On-BadSignature').value);
	XHR.appendData('On-NoSignature',document.getElementById('On-NoSignature').value);
	XHR.appendData('On-DNSError',document.getElementById('On-DNSError').value);
	XHR.appendData('On-Security',document.getElementById('On-Security').value);
	XHR.appendData('On-InternalError',document.getElementById('On-InternalError').value);
	   
	document.getElementById('dkim-form').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveOpenDKIMForm);	
	}
	</script>	
			
			
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}
function save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableDKFilter",$_GET["EnableDKFilter"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"OpenDKIMConfig");
	$sock->getFrameWork("cmd.php?opendkim-restart=yes");
	$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
}

function config_nokey(){
	$page=CurrentPageName();
	$html="
	<div id='sign-key'>
	<table style='width:100%'>
		<tr>
			<td width=1% valign='top'><img src='img/dkim_bg.jpg'></td>
			<td valign='top'>
			<table style='width:100%'>
				<tr>
					<td width=1%><img src='img/warning64.png'></td>
					<td valign='top'><H3>{generate_a_signing_key}</H3>
					<div style='font-size:14px'>{generate_a_signing_key_nokey_text}</div>
					<center style='margin:10px'>". button("{generate_a_signing_key}","generate_a_signing_key()")."</center>	
					</td>
				</tr>
			</table>
			</td>
		</tr>
	</table>
	</div>
	<script>
	
	var x_generate_a_signing_key= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('OPENDKIM_TABS');	
		}		
	
		
		function generate_a_signing_key(){
				var XHR = new XHRConnection();
	      		XHR.appendData('hostname','{$_GET["hostname"]}');
	      		XHR.appendData('ou','{$_GET["ou"]}');
	      		XHR.appendData('generate_a_signing_key','yes');
				document.getElementById('sign-key').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_generate_a_signing_key);	
		}
</script>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function config_generate_a_signing_key(){
	$sock=new sockets();
	echo base64_decode($sock->getFrameWork("cmd.php?dkim-amavis-build-key={$_GET["hostname"]}"));
	
}

function dns_keys(){
	
	$page=CurrentPageName();
	$html="
	
	<p style='font-size:13px'>{dkim_showkeys_text}</p>
	<div id='dns_key_display'></div>
	
	<script>
		LoadAjax('dns_key_display','$page?dns-key-view=yes');
	</script>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}	


function dns_keys_display(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?opendkim-show-keys=yes")));
	if(is_array($array)){
		$ul[]="<ul id='domains-checklist'>";
		while (list ($domain, $lines) = each ($array) ){
			$ul[]="<li class='domainsli' style='width:550px'>";
			$ul[]="<p style='font-size:16px;color:#005447;border-bottom:1px solid #005447'>$domain</strong>";
			$rlines=explode("\n",$lines);
			while (list ($index, $line) = each ($rlines) ){
				$line=htmlentities($line);
				$line=str_replace(" ","&nbsp;",$line);
				$ul[]="<div><code style='font-size:11px'>$line</code></div>";
				}
			$ul[]="</li>";
			}
		$ul[]="</ul>";
		$html=$html.@implode("\n",$ul);
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function dns_tests_keys(){
	
	$page=CurrentPageName();
	$html="
	
	<p style='font-size:13px'>{dkim_testkeys_text}</p>
	<div id='dns_key_results'></div>
	
	<script>
		LoadAjax('dns_key_results','$page?dns-tests-view=yes');
	</script>";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}
	
function dns_tests_keys_display(){
$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?opendkim-show-tests-keys=yes")));	
	if(is_array($array)){
		$ul[]="<ul id='domains-checklist'>";
		while (list ($domain, $lines) = each ($array) ){
			$ul[]="<li class='domainsli' style='width:550px'>";
			$ul[]="<p style='font-size:16px;color:#005447;border-bottom:1px solid #005447'>$domain</strong>";
			$rlines=explode("\n",$lines);
			while (list ($index, $line) = each ($rlines) ){
				$line=htmlentities($line);
				$line=str_replace(" ","&nbsp;",$line);
				$ul[]="<div><code style='font-size:11px'>$line</code></div>";
				}
			$ul[]="</li>";
			}
		$ul[]="</ul>";
		$html=$html.@implode("\n",$ul);
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
	function whitelist(){
	$tpl=new templates();
	$page=CurrentPageName();
	$add_text=$tpl->javascript_parse_text('{SPF_SPAMMASS_ADD_WL_TEXT}');
	
	$html="
	<table style='width:100%'>
	<tr>
	<td><div style='text-align:right;padding:5px;margin:5px'>". button("{add_default_values}","DKIM_SPAMMASS_LIST_DEFAULT()")."</div></td>
	<td><div style='text-align:right;padding:5px;margin:5px'>". button("{add}","DKIM_SPAMMASS_ADD_WL()")."</div></td>
	</tr>
	</table>
	<p style='font-size:13px'>{DKIM_SPAMASS_WBL_HOWTO}</p>
	
	<div id='whitelistsDKIMspamass' style='height:450px;overflow:auto'></div>
	
	<script>
	
	var x_DKIM_SPAMMASS_ADD_WL= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		DKIM_SPAMMASS_LIST();
		}

	function DKIM_SPAMMASS_LIST(){
		LoadAjax('whitelistsDKIMspamass','$page?whitelist-list=yes');
	}
	
	function DKIM_SPAMMASS_LIST_DEFAULT(){
		LoadAjax('whitelistsDKIMspamass','$page?whitelist-list=yes&add-default=yes');
	}	
	
	
	function DKIM_SPAMMASS_ADD_WL(){
		var email=prompt('$add_text');
		if(email){
			var XHR = new XHRConnection();
			document.getElementById('whitelistsDKIMspamass').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('whitelist-add',email);
			XHR.sendAndLoad('$page', 'GET',x_DKIM_SPAMMASS_ADD_WL);
			}
		}
		
	function DKIM_SPAMMASS_DELETE_WL(ID){
			var XHR = new XHRConnection();
			document.getElementById('whitelistsDKIMspamass').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('whitelist-del',ID);
			XHR.sendAndLoad('$page', 'GET',x_DKIM_SPAMMASS_ADD_WL);
	}
	
	DKIM_SPAMMASS_LIST();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}

function whitelist_list(){
	if(isset($_GET["add-default"])){whitelist_default();}
	$q=new mysql();
	$tpl=new templates();
	$sql="SELECT * FROM spamassassin_dkim_wl ORDER BY ID DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");

	$html="<table style='width:100%'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$html=$html."<tr ". CellRollOver().">";
		$html=$html."<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:13px'>{$ligne["domain"]}</td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","DKIM_SPAMMASS_DELETE_WL({$ligne["ID"]})")."</td>
		</tr>
		";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function whitelist_default(){
	
$d[]="*@ebay.com";
$d[]="*@*.ebay.com";
$d[]="*@ebay.co.uk";
$d[]="*@*.ebay.co.uk";
$d[]="*@ebay.at";
$d[]="*@ebay.ca";
$d[]="*@ebay.de";
$d[]="*@ebay.fr";
$d[]="*@*.paypal.com";
$d[]="*@paypal.com";
$d[]="*@*paypal.com";
$d[]="*@*.paypal.be";
$d[]="*@cern.ch";
$d[]="*@amazon.com";
$d[]="*@springer.delivery.net";
$d[]="*@cisco.com";
$d[]="*@alert.bankofamerica.com";
$d[]="*@bankofamerica.com";
$d[]="*@cnn.com";
$d[]="*@*.cnn.com";
$d[]="*@skype.net";
$d[]="service@youtube.com";
$d[]="*@welcome.skype.com";
$d[]="*@cc.yahoo-inc.com  yahoo-inc.com";
$d[]="*@cc.yahoo-inc.com";
$d[]="rcapotenoy@yahoo.com";
$d[]="googlealerts-noreply@google.com";

while (list ($num, $ligne) = each ($d) ){
	$sql="INSERT INTO spamassassin_dkim_wl(domain) VALUES('$ligne')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
}


}

function whitelist_del(){
	$sql="DELETE FROM spamassassin_dkim_wl WHERE ID='{$_GET["whitelist-del"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?opendkim-whitelistdomains=yes");
}

function whitelist_add(){
	
	$sql="INSERT INTO spamassassin_dkim_wl(domain) VALUES('{$_GET["whitelist-add"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?opendkim-whitelistdomains=yes");
	
	
}	

?>