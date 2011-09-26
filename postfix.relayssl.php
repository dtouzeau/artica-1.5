<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.stunnel4.inc');
	include_once('ressources/class.main_cf.inc');
	
	
	
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsPostfixAdministrator){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
	die();
}

if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;}
if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){tabs();exit;}

if(isset($_GET["stunnel"])){stunnel();exit;}
if(isset($_GET["enable_stunnel"])){stunnel_save();exit;}

if(isset($_GET["relayhost"])){relayhost();exit;}
if(isset($_GET["server"])){relayhost_save();exit;}

if(isset($_GET["popup-auth-mech"])){smtp_sasl_mechanism_filter();exit;}
if(isset($_GET["plain"])){smtp_sasl_mechanism_filter_save();exit;}


if(isset($_GET["stunnel-status"])){echo main_stunnel_status();exit;}
if(isset($_GET["ApplyConfig"])){echo ApplyConfig();exit;}
if(isset($_GET["FillSenderForm"])){echo FillSenderForm();exit;}
if(isset($_POST["smtp_sender_dependent_authentication_email"])){smtp_sender_dependent_authentication_submit();exit();}


function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{smtps_relayhost}');
	$page=CurrentPageName();	
	$include=file_get_contents("js/postfix-tls.js");
	
	
	$html="YahooWin2(750,'$page?popup=yes','$title');";
	
	
	echo $html;
	
	
}

function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$filters_settings=$tpl->_ENGINE_parse_body('{antispam_filters}');
	$array["stunnel"]='{APP_STUNNEL}';
	$array["relayhost"]="{relay_server}";
	$array["popup-auth-mech"]='{auth_mechanism}';
	
	
	//smtp_sasl_mechanism_filter 

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_postfix_relayssl style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfix_relayssl\").tabs();});
		</script>";		
}

function smtp_sasl_mechanism_filter_save(){
	$sock=new sockets();
	$sock->SET_INFO("EnableMechSMTPCramMD5",$_GET["cram-md5"]);
	$sock->SET_INFO("EnableMechSMTPDigestMD5",$_GET["digest-md5"]);
	$sock->SET_INFO("EnableMechSMTPLogin",$_GET["login"]);
	$sock->SET_INFO("EnableMechSMTPPlain",$_GET["plain"]);
	$sock->SET_INFO("EnableMechSMTPText",$_GET["EnableMechSMTPText"]);	
	$sock->getFrameWork("cmd.php?postfix-smtp-sasl=yes");
	
}


function smtp_sasl_mechanism_filter(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableMechSMTPCramMD5=$sock->GET_INFO("EnableMechSMTPCramMD5");
	$EnableMechSMTPDigestMD5=$sock->GET_INFO("EnableMechSMTPDigestMD5");
	$EnableMechSMTPLogin=$sock->GET_INFO("EnableMechSMTPLogin");
	$EnableMechSMTPPlain=$sock->GET_INFO("EnableMechSMTPPlain");
	if(!is_numeric($EnableMechSMTPCramMD5)){$EnableMechSMTPCramMD5=1;}
	if(!is_numeric($EnableMechSMTPDigestMD5)){$EnableMechSMTPDigestMD5=1;}
	if(!is_numeric($EnableMechSMTPLogin)){$EnableMechSMTPLogin=1;}
	if(!is_numeric($EnableMechSMTPPlain)){$EnableMechSMTPPlain=1;}	
	
	$EnableMechSMTPText=$sock->GET_INFO("EnableMechSMTPText");
	if($EnableMechSMTPText==null){$EnableMechSMTPText="!gssapi, !external, static:all";}
	
	
$html="
	<div id='sasl-auth-smtp-div'>
	<table style='width:100%' class=form>
	<tr>
	<td align='right' class=legend style='font-size:13px'>plain:</stong></td>
	<td>" . Field_checkbox('plain',1,$EnableMechSMTPPlain)."</td>
	<td width=1%></td>
	</tr>

	<tr>
	<td align='right' class=legend style='font-size:13px'>login:</stong></td>
	<td>" . Field_checkbox('login',1,$EnableMechSMTPLogin)."</td>
	<td width=1%></td>
	</tr>	

	<tr>
	<td align='right' class=legend style='font-size:13px'>cram-md5:</stong></td>
	<td>" . Field_checkbox('cram-md5',1,$EnableMechSMTPCramMD5)."</td>
	<td width=1%></td>
	</tr>	
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>digest-md5:</stong></td>
	<td>" . Field_checkbox('digest-md5',1,$EnableMechSMTPDigestMD5)."</td>
	<td width=1%></td>
	</tr>	
	
	<tr>
		<td colspan=3 align='right'>". Field_text('EnableMechSMTPText',"$EnableMechSMTPText","width:420px;font-size:14px;padding:3px")."</td>
	</tr>
	
	<tr>
		<td colspan=3 align='right'><hr>". button('{apply}',"SaveSMTPSMTPAuthMech()")."</td>
	</tr>
	</table>
	</div>
	<script>
	
	var x_SaveSMTPSMTPAuthMech = function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('main_config_postfix_relayssl');
	}	
	
		function SaveSMTPSMTPAuthMech(){
			var XHR=XHRParseElements('sasl-auth-smtp-div');
			
			document.getElementById('sasl-auth-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveSMTPSMTPAuthMech);
		}
	
	</script>
	
";

echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function stunnel(){
$page=CurrentPageName();
$sock=new sockets();

$stunnel=new stunnel4();
$relay_host=$stunnel->main_array["postfix_relayhost"]["connect"];
$localport=$stunnel->main_array["postfix_relayhost"]["accept"];

if(!is_numeric($localport)){
	$sock=new sockets();
	$localport=$sock->RandomPort();
	}
	



$sTunnel4enabled=$sock->GET_INFO('sTunnel4enabled');
$enable=Paragraphe_switch_img('{enable_stunnel}',"{enable_stunnel_text}",'enable_stunnel',$sTunnel4enabled);

$intro="
<div id='ssltunnelid'>
	<table style='width:100%' align=center>
	<tr>
		<td valign='top'>
			<img src='img/postfix-relayhost-ssl-bg.png' style='padding:4px;border:1px dotted #CCCCCC;margin:3px'>
		</td>
		<td valign='top' width='99%'>
			<div id='servinfos_stunnel'></div>
		</td>
	</tr>
		<tr><td colspan=2><p class=explain>{smtps_relayhost_text}</div></td></tr>
	</table>
	<br>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:14px'>{enable_stunnel}</td>
		<td>". Field_checkbox("enable_stunnel",1,$sTunnel4enabled)."</td>
		<td>". help_icon("{enable_stunnel_text}")."</td>
	</tr>
	<tr>
		<td align='right' nowrap style='font-size:14px'><strong>{stunnelport}:&nbsp;</strong></td>
		<td><input type='text' id='localport' value='$localport' style='font-size:14px;width:30%'></td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button('{apply}',"StunnelApply()")."</td>
	</tr>
	</table>
</div>
<script>

	function x_StunnelApply(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_config_postfix_relayssl');
	}

	function StunnelApply(){
		var XHR = new XHRConnection();
		if(document.getElementById('enable_stunnel').checked){
			XHR.appendData('enable_stunnel',1);
		}else{
			XHR.appendData('enable_stunnel',0);
		}
		
		XHR.appendData('localport',document.getElementById('localport').value);
		document.getElementById('ssltunnelid').innerHTML=\"<center style='width:400px'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_StunnelApply);	
	}

	LoadAjax('servinfos_stunnel','$page?stunnel-status=yes&hostname={$_GET["hostname"]}');
</script>


";	


$html="$intro";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

}

function stunnel_save(){
	$stunnel=new stunnel4();
	$sock=new sockets();
	$sock->SET_INFO('sTunnel4enabled',$_GET["enable_stunnel"]);
	
	$stunnel->main_array["postfix_relayhost"]["accept"]=$_GET["localport"];
	$stunnel->SaveConf();
	$stunnel->SaveToserver();
	
}

function relayhost(){
$page=CurrentPageName();
$sock=new sockets();
$tpl=new templates();
$stunnel=new stunnel4();
$relay_host=$stunnel->main_array["postfix_relayhost"]["connect"];
$localport=$stunnel->main_array["postfix_relayhost"]["accept"];
if(!is_numeric($localport)){$localport=0;}
preg_match('#(.+?):([0-9]+)#',$relay_host,$h);
if($h[2]==null){$h[2]=465;}	
$sasl=new smtp_sasl_password_maps();
//print_r($sasl->smtp_sasl_password_hash);
preg_match('#(.+?):(.+)#',$sasl->smtp_sasl_password_hash["[127.0.0.1]:$localport"],$ath);	


	$html="
	<div id='sslRelayHostid'>
		<div class=explain>{relayhost_text}</div>
		<table style='width:100%' class=form>
			<tr>
				<td align='right' nowrap style='font-size:14px'><strong>{yserver}:&nbsp;</strong></td>
				<td><input type='text' id='ssl_relay_server' value='$relay_host' style='font-size:14px'></td>
			</tr>
			<tr>
				<td align='right' nowrap style='font-size:14px'><strong>{yport}:&nbsp;</strong></td>
				<td><input type='text' id='ssl_relay_port' value='{$h[2]}' style='font-size:14px;width:30%'></td>
			</tr>	
				<tr>
					<td align='left' nowrap style='font-size:16px' colspan=2>&nbsp;</td>
				</tr>
					<td align='right' nowrap style='font-size:14px'><strong>{username}:&nbsp;</strong></td>
					<td><input type='text' id='ssl_relay_username' value='{$ath[1]}' style='font-size:14px'></td>
					</tr>
				<tr>
					<td align='right' nowrap style='font-size:14px'><strong>{password}:&nbsp;</strong></td>
					<td>". Field_password("ssl_relay_password",$ath[2],"font-size:14px;")."</td>
				</tr>	
		<tr>
			<td colspan=2 align='right'><hr>". button('{apply}',"SSLRelayHostApply()")."</td>
		</tr>
		</table>
</div>
<script>

	function x_SSLRelayHostApply(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_config_postfix_relayssl');
	}

	function SSLRelayHostApply(){
		var localport=$localport;
		 if(localport>0){
		 	var XHR = new XHRConnection();
			XHR.appendData('server',document.getElementById('ssl_relay_server').value);
			XHR.appendData('port',document.getElementById('ssl_relay_port').value);
			XHR.appendData('username',document.getElementById('ssl_relay_username').value);
			XHR.appendData('password',document.getElementById('ssl_relay_password').value);
			document.getElementById('sslRelayHostid').innerHTML=\"<center style='width:400px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',x_SSLRelayHostApply);
		}else{
			alert('Failed, local stunnel port [$localport] is a wrong value...');
		}
	}
	
	function SSLRelayHostApplyDis(){
	 var localport=$localport;
	 DisableFieldsFromId('sslRelayHostid');
		 if(localport>0){
		 	EnableFieldsFromId('sslRelayHostid');
		 }
	 
	}

	
</script>
";
echo $tpl->_ENGINE_parse_body($html);	
	
}

function relayhost_save(){
	$main=new main_cf();
	$tpl=new templates();
	$stunnel=new stunnel4();
	$continue=true;
	$main->smtp_sasl_password_maps_enable_2();
	$localport=$stunnel->main_array["postfix_relayhost"]["accept"];
	
	$stunnel->main_array["postfix_relayhost"]["connect"]=$_GET["server"];
	$stunnel->SaveConf();
	$stunnel->SaveToserver();
	writelogs("Saving [127.0.0.1]:$localport -> {$_GET["username"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$main->main_array["relayhost"]="[127.0.0.1]:$localport";
	$main->save_conf();
	
	$sock=new sockets();
	$sock->SET_INFO("PostfixRelayHost","[127.0.0.1]:$localport");	
	$sock->getFrameWork("cmd.php?postfix-relayhost=yes");
	
	if($_GET["username"]==null){$continue=false;}
	if($_GET["password"]==null){$continue=false;}
		$saslpswd=new smtp_sasl_password_maps();
		if($continue){
			writelogs("UPDATE [127.0.0.1]:$localport",__FUNCTION__,__FILE__,__LINE__);
			if(!$saslpswd->add("[127.0.0.1]:$localport",$_GET["username"],$_GET["password"])){
				echo $tpl->javascript_parse_text("{err_sasl_saveldap}<br>$saslpswd->ldap_infos");
				return;
			}
			
			$sock->SET_INFO("smtp_sender_dependent_authentication",1);
			$sock->getFrameWork("cmd.php?postfix-hash-senderdependent=yes");
			$sock->getFrameWork("cmd.php?postfix-smtp-sasl=yes");
		}else{
			writelogs("Delete [127.0.0.1]:$localport",__FUNCTION__,__FILE__,__LINE__);
			$saslpswd->delete("[127.0.0.1]:$localport");
		}
	
}




function main_stunnel_status(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->stunnel4_installed){
		return $tpl->_ENGINE_parse_body("<div class=explain style='color:red'>{stunnel_not_installed}</div>");

	}
		
	$ini=new Bs_IniHandler();
	$sock=new sockets();	
	$datas=base64_decode($sock->getFrameWork("cmd.php?stunnel-ini-status=yes"));
	$ini->loadString($datas);
	$status=DAEMON_STATUS_ROUND("STUNNEL",$ini);
	echo $tpl->_ENGINE_parse_body($status);
	
	
}






function FillSenderForm(){
	$html="<H5>{sender_authentication_maps}</H5>
	<strong>{sender_authentication_maps_text}</strong>
	<br><br>
	<table style='width:100%'>
		</tr>
			<td align='right' nowrap style='font-size:14px'><strong>{sender_email}:&nbsp;</strong></td>
			<td><input type='text' id='sender_email' value='' style='font-size:14px'></td>
		</tr>
		</tr>
			<td align='right' nowrap style='font-size:14px'><strong>{username}:&nbsp;</strong></td>
			<td><input type='text' id='smtp_sender_dependent_authentication_username' value='' style='font-size:14px'></td>
		</tr>		
		</tr>
			<td align='right' nowrap style='font-size:14px'><strong>{password}:&nbsp;</strong></td>
			<td><input type='text' id='smtp_sender_dependent_authentication_password' value='' style='font-size:14px'></td>
		</tr>		
		<tr>
			<td align='right' nowrap  colspan=2><input type='button' OnClick=\"javascript:smtp_sender_dependent_authentication_submit();\" value='&laquo;&nbsp;&nbsp;&nbsp;{add}&nbsp;&nbsp;&nbsp;&raquo;'></td>
		</tr>
	</table>
	<div id='table'></div>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function FillSenderForm_table(){
	
$sasl=new smtp_sasl_password_maps();
	
$html="<table style='width:100%'>";

while (list ($num, $val) = each ($sasl->smtp_sasl_password_hash) ){
	preg_match('#(.+?):(.+)#',$val,$ath);
	$html=$html . 
	
	"<tr>
		
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$num</strong></td>
		<td><strong>{$ath[1]}</td>
	</tr>
		
		";
	
}
$html=$html . "</table>";
return RoundedLightGreen($html);
}
function smtp_sender_dependent_authentication_submit(){
	$sasl=new smtp_sasl_password_maps();
	$tpl=new templates();
	if($sasl->add($_POST["smtp_sender_dependent_authentication_email"],$_POST["smtp_sender_dependent_authentication_username"],$_POST["smtp_sender_dependent_authentication_password"])){
		echo $tpl->_ENGINE_parse_body('{success}');
	}else{
		echo $sasl->ldap_infos;
	}
	
}

	
?>	

