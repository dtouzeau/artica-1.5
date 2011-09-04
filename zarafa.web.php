<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	$user=new usersMenus();
	if($user->AsMailBoxAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ZarafaApachePort"])){SAVE();exit;}
	if(isset($_GET["DbAttachConverter-popup"])){DbAttachConverter_popup();exit;}
	if(isset($_GET["DbAttachConverterPerform"])){DbAttachConverter_Perform();exit;}
	
	js();
	
	
function js(){
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$start="APP_ZARAFA_WEB()";
if(isset($_GET["in-line"])){$start="APP_ZARAFA_WEB_INLINE()";}
$title=$tpl->_ENGINE_parse_body('{APP_ZARAFA_WEB}');

$html="

function APP_ZARAFA_WEB(){
	YahooWin3('550','$page?popup=yes','$title');
	
	}
	
function APP_ZARAFA_WEB_INLINE(){
	$('#zarafa-inline-config').load('$page?popup=yes');
}
	
var X_APP_ZARAFA_WEB_SAVE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	document.getElementById('zrfa-logo').src='img/zarafa-web-128.png';
	}	
	

	
function APP_ZARAFA_WEB_SAVE(){
	var XHR = new XHRConnection();
	XHR.appendData('ZarafaApachePort',document.getElementById('ZarafaApachePort').value);
	XHR.appendData('ZarafaiCalPort',document.getElementById('ZarafaiCalPort').value);
	XHR.appendData('ZarafaServerListenIP',document.getElementById('ZarafaServerListenIP').value);
	XHR.appendData('ZarafaApacheServerName',document.getElementById('ZarafaApacheServerName').value);
	XHR.appendData('ZarafaStoreOutsidePath',document.getElementById('ZarafaStoreOutsidePath').value);
	XHR.appendData('ZarafaStoreCompressionLevel',document.getElementById('ZarafaStoreCompressionLevel').value);
	XHR.appendData('ZarafaServerSMTPIP',document.getElementById('ZarafaServerSMTPIP').value);
	XHR.appendData('ZarafaServerSMTPPORT',document.getElementById('ZarafaServerSMTPPORT').value);
	
	XHR.appendData('ZarafaPop3Port',document.getElementById('ZarafaPop3Port').value);
	XHR.appendData('ZarafaIMAPPort',document.getElementById('ZarafaIMAPPort').value);
	XHR.appendData('ZarafaPop3sPort',document.getElementById('ZarafaPop3sPort').value);
	XHR.appendData('ZarafaIMAPsPort',document.getElementById('ZarafaIMAPsPort').value);
	
	if(document.getElementById('ZarafaPop3Enable').checked){XHR.appendData('ZarafaPop3Enable',1);}else{XHR.appendData('ZarafaPop3Enable',0);}
	if(document.getElementById('ZarafaPop3sEnable').checked){XHR.appendData('ZarafaPop3sEnable',1);}else{XHR.appendData('ZarafaPop3sEnable',0);}
	if(document.getElementById('ZarafaIMAPEnable').checked){XHR.appendData('ZarafaIMAPEnable',1);}else{XHR.appendData('ZarafaIMAPEnable',0);}
	if(document.getElementById('ZarafaIMAPsEnable').checked){XHR.appendData('ZarafaIMAPsEnable',1);}else{XHR.appendData('ZarafaIMAPsEnable',0);}
	if(document.getElementById('ZarafaAllowToReinstall').checked){XHR.appendData('ZarafaAllowToReinstall',1);}else{XHR.appendData('ZarafaAllowToReinstall',0);}
	if(document.getElementById('ZarafaWebNTLM').checked){XHR.appendData('ZarafaWebNTLM',1);}else{XHR.appendData('ZarafaWebNTLM',0);}

	
	
	if(document.getElementById('ZarafaApacheSSL').checked){
		XHR.appendData('ZarafaApacheSSL',1);
	}else{
		XHR.appendData('ZarafaApacheSSL',0);
	}

	if(document.getElementById('ZarafaiCalEnable').checked){
		XHR.appendData('ZarafaiCalEnable',1);
	}else{
		XHR.appendData('ZarafaiCalEnable',0);
	}		
	
	if(document.getElementById('ZarafaUserSafeMode').checked){
		XHR.appendData('ZarafaUserSafeMode',1);
	}else{
		XHR.appendData('ZarafaUserSafeMode',0);
	}	

	if(document.getElementById('ZarafaStoreOutside').checked){
		XHR.appendData('ZarafaStoreOutside',1);
	}else{
		XHR.appendData('ZarafaStoreOutside',0);
	}

	if(document.getElementById('ZarafaAspellEnabled').checked){
		XHR.appendData('ZarafaAspellEnabled',1);
	}else{
		XHR.appendData('ZarafaAspellEnabled',0);
	}		
	
	XHR.appendData('ou','$ou_decrypted');
	document.getElementById('zrfa-logo').src='img/wait_verybig.gif';
	XHR.sendAndLoad('$page', 'GET',X_APP_ZARAFA_WEB_SAVE);	
}
	
$start
";

echo $html;	
	
}

function SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("ZarafaApachePort",trim($_GET["ZarafaApachePort"]));
	$sock->SET_INFO("ZarafaApacheSSL",trim($_GET["ZarafaApacheSSL"]));
	
	$sock->SET_INFO("ZarafaiCalPort",trim($_GET["ZarafaiCalPort"]));
	$sock->SET_INFO("ZarafaiCalEnable",trim($_GET["ZarafaiCalEnable"]));
	$sock->SET_INFO("ZarafaUserSafeMode",trim($_GET["ZarafaUserSafeMode"]));
	$sock->SET_INFO("ZarafaServerListenIP",trim($_GET["ZarafaServerListenIP"]));
	$sock->SET_INFO("ZarafaApacheServerName",trim($_GET["ZarafaApacheServerName"]));
	$sock->SET_INFO("ZarafaStoreOutside",trim($_GET["ZarafaStoreOutside"]));
	$sock->SET_INFO("ZarafaStoreOutsidePath",trim($_GET["ZarafaStoreOutsidePath"]));
	$sock->SET_INFO("ZarafaStoreCompressionLevel",trim($_GET["ZarafaStoreCompressionLevel"]));
	$sock->SET_INFO("ZarafaAspellEnabled",trim($_GET["ZarafaAspellEnabled"]));
	
	$sock->SET_INFO("ZarafaServerSMTPPORT",trim($_GET["ZarafaServerSMTPPORT"]));
	$sock->SET_INFO("ZarafaServerSMTPIP",trim($_GET["ZarafaServerSMTPIP"]));
	
	$sock->SET_INFO("ZarafaIMAPsPort",trim($_GET["ZarafaIMAPsPort"]));
	$sock->SET_INFO("ZarafaPop3sPort",trim($_GET["ZarafaPop3sPort"]));
	$sock->SET_INFO("ZarafaIMAPPort",trim($_GET["ZarafaIMAPPort"]));
	$sock->SET_INFO("ZarafaPop3Port",trim($_GET["ZarafaPop3Port"]));
	
	$sock->SET_INFO("ZarafaPop3Enable",trim($_GET["ZarafaPop3Enable"]));
	$sock->SET_INFO("ZarafaPop3sEnable",trim($_GET["ZarafaPop3sEnable"]));
	$sock->SET_INFO("ZarafaIMAPEnable",trim($_GET["ZarafaIMAPEnable"]));
	$sock->SET_INFO("ZarafaIMAPsEnable",trim($_GET["ZarafaIMAPsEnable"]));
	$sock->SET_INFO("ZarafaAllowToReinstall",trim($_GET["ZarafaAllowToReinstall"]));
	$sock->SET_INFO("ZarafaWebNTLM",trim($_GET["ZarafaWebNTLM"]));
	
	
	
	 
	
	$sock->getFrameWork("cmd.php?zarafa-restart-web=yes");
	$sock->getFrameWork("cmd.php?zarafa-restart-server=yes");
}

function popup(){
	
$users=new usersMenus();
$page=CurrentPageName();
$tpl=new templates();
if(!$users->APACHE_INSTALLED){
$html="
<table style='width:100%'>
<tr>
	<td valign='top'><img id='zrfa-logo' src='img/zarfa-web-error-128.png'></td>
	<td valign='top'>	
		<table style='width:100%'>
		<tr>
			<td colspan=2><H3>{WEBMAIL}</H3>
			<p style='font-size:14px;color:#C61010'>{ZARAFA_ERROR_NO_APACHE}</p>
			
			</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	return;
}


$sock=new sockets();
$users=new usersMenus();

$ZarafaApachePort=$sock->GET_INFO("ZarafaApachePort");
$ZarafaUserSafeMode=$sock->GET_INFO("ZarafaUserSafeMode");
$ZarafaApacheServerName=$sock->GET_INFO("ZarafaApacheServerName");
if(trim($ZarafaApacheServerName)==null){$ZarafaApacheServerName=$users->hostname;}

$enable_ssl=$sock->GET_INFO("ZarafaApacheSSL");	
if($ZarafaApachePort==null){$ZarafaApachePort="9010";}

$ZarafaiCalEnable=$sock->GET_INFO("ZarafaiCalEnable");
$ZarafaiCalPort=$sock->GET_INFO('ZarafaiCalPort');	
if($ZarafaiCalPort==null){$ZarafaiCalPort="8088";}

$ZarafaServerListenIP=$sock->GET_INFO("ZarafaServerListenIP");
$ZarafaServerSMTPIP=$sock->GET_INFO("ZarafaServerSMTPIP");
$ZarafaServerSMTPPORT=$sock->GET_INFO("ZarafaServerSMTPPORT");
if($ZarafaServerSMTPIP==null){$ZarafaServerSMTPIP="127.0.0.1";}
if($ZarafaServerListenIP==null){$ZarafaServerListenIP="127.0.0.1";}
if(!is_numeric($ZarafaServerSMTPPORT)){$ZarafaServerSMTPPORT=25;}


$ZarafaPop3Enable=$sock->GET_INFO("ZarafaPop3Enable");
$ZarafaPop3sEnable=$sock->GET_INFO("ZarafaPop3sEnable");
$ZarafaIMAPEnable=$sock->GET_INFO("ZarafaIMAPEnable");
$ZarafaIMAPsEnable=$sock->GET_INFO("ZarafaIMAPsEnable");
$ZarafaPop3Port=$sock->GET_INFO("ZarafaPop3Port");
$ZarafaIMAPPort=$sock->GET_INFO("ZarafaIMAPPort");
$ZarafaPop3sPort=$sock->GET_INFO("ZarafaPop3sPort");
$ZarafaIMAPsPort=$sock->GET_INFO("ZarafaIMAPsPort");
$ZarafaAllowToReinstall=$sock->GET_INFO("ZarafaAllowToReinstall");

$ZarafaWebNTLM=$sock->GET_INFO("ZarafaWebNTLM");

if(!is_numeric($ZarafaPop3Enable)){$ZarafaPop3Enable=1;}
if(!is_numeric($ZarafaIMAPEnable)){$ZarafaIMAPEnable=1;}

if(!is_numeric($ZarafaPop3Port)){$ZarafaPop3Port=110;}
if(!is_numeric($ZarafaPop3sPort)){$ZarafaPop3sPort=995;}
if(!is_numeric($ZarafaIMAPPort)){$ZarafaIMAPPort=143;}
if(!is_numeric($ZarafaIMAPsPort)){$ZarafaIMAPsPort=993;}
if(!is_numeric($ZarafaAllowToReinstall)){$ZarafaAllowToReinstall=1;}





if($enable_ssl==null){$enable_ssl="0";}
if($ZarafaiCalEnable==null){$ZarafaiCalEnable=0;}
if(!is_numeric($ZarafaUserSafeMode)){
	$sock->SET_INFO("ZarafaUserSafeMode",0);
	$ZarafaUserSafeMode=0;
}

$ZarafaStoreOutside=$sock->GET_INFO("ZarafaStoreOutside");
$ZarafaStoreOutsidePath=$sock->GET_INFO("ZarafaStoreOutsidePath");
$ZarafaStoreCompressionLevel=$sock->GET_INFO("ZarafaStoreCompressionLevel");

$ZarafaAspellEnabled=$sock->GET_INFO("ZarafaAspellEnabled");
if(!is_numeric($ZarafaAspellEnabled)){$ZarafaAspellEnabled=0;}
$ZarafaAspellInstalled=0;
$ZarafaAspellInstalled_text="({not_installed})";

if($users->ASPELL_INSTALLED){
	$ZarafaAspellInstalled=1;
	$ZarafaAspellInstalled_text="({installed})";
}


if(!is_numeric($ZarafaStoreOutside)){$ZarafaStoreOutside=0;}
if(!is_numeric($ZarafaStoreCompressionLevel)){$ZarafaStoreCompressionLevel=6;}
if($ZarafaStoreOutsidePath==null){$ZarafaStoreOutsidePath="/var/lib/zarafa";}

for($i=0;$i<10;$i++){
	$ZarafaStoreCompressionLevelAr[$i]=$i;
}

if($ZarafaUserSafeMode==1){
	$ZarafaUserSafeMode_warn="
	<hr>
	<center>
		<img src='img/error-128.png'>
		<H3>{ZARAFA_SAFEMODE_EXPLAIN}</H3>
	</center>
	
	";
	
}


$net=new networking();
$nets=$net->ALL_IPS_GET_ARRAY();
$nets["0.0.0.0"]="{all}";

$netfield=Field_array_Hash($nets,"ZarafaServerListenIP",$ZarafaServerListenIP,"font-size:13px;padding:3px");
$SMTPfield=Field_array_Hash($nets,"ZarafaServerSMTPIP",$ZarafaServerSMTPIP,"font-size:13px;padding:3px");
$convert_current_attachments_text=$tpl->javascript_parse_text("{convert_current_attachments}");

$html="
<table style='width:100%'>
<tr>
	<td valign='top'><img id='zrfa-logo' src='img/zarafa-web-128.png'>$ZarafaUserSafeMode_warn</td>
	<td valign='top'>
	
		<table style='width:100%' class=form>
		<tr><td colspan=2><H3>{WEBMAIL}</H3></td></tr>
			<tr>
				<td class=legend style='font-size:12px'>{servername}:</td>
				<td>". Field_text("ZarafaApacheServerName",$ZarafaApacheServerName,"font-size:13px;padding:3px;width:210px")."</td>
			</tr>		
			<tr>
				<td class=legend style='font-size:12px'>{listen_port}:</td>
				<td>". Field_text("ZarafaApachePort",$ZarafaApachePort,"font-size:13px;padding:3px;width:60px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{enable_ssl}:</td>
				<td>". Field_checkbox("ZarafaApacheSSL",1,$enable_ssl)."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{ZarafaWebNTLM}:</td>
				<td>". Field_checkbox("ZarafaWebNTLM",1,$ZarafaWebNTLM)."</td>
			</tr>			
			
			
			<tr>
				<td class=legend style='font-size:12px'>{spell_checker}&nbsp;$ZarafaAspellInstalled_text&nbsp;:</td>
				<td>". Field_checkbox("ZarafaAspellEnabled",1,$ZarafaAspellEnabled)."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{ZarafaAllowToReinstall}:</td>
				<td>". Field_checkbox("ZarafaAllowToReinstall",1,$ZarafaAllowToReinstall)."</td>
			</tr>
			<tr><td colspan=2 align='right'><hr>". button("{apply}","APP_ZARAFA_WEB_SAVE()")."</td></tr>							
		</table>
		
		<p>&nbsp;</p>
		
	<table style='width:100%' class=form>
		<tr><td colspan=2><H3>{APP_ZARAFA_SERVER}</H3></td></tr>
			<tr>
				<td class=legend style='font-size:12px'>{mapi_ip}:</td>
				<td>$netfield</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{mapi_port}:</td>
				<td><strong style='font-size:13px'>236</strong></td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{enable_pop3}:</td>
				<td><strong style='font-size:13px'>". Field_checkbox("ZarafaPop3Enable", 1,$ZarafaPop3Enable,"CheckZarafaFields()")."</td>
			</tr>			
			<tr>
				<td class=legend style='font-size:12px'>{pop3_port}:</td>
				<td>". Field_text("ZarafaPop3Port",$ZarafaPop3Port,"width:90px;font-size:13px;padding:3px")."</td>
			</tr>		
			<tr>
				<td class=legend style='font-size:12px'>{enable_pop3s}:</td>
				<td><strong style='font-size:13px'>". Field_checkbox("ZarafaPop3sEnable", 1,$ZarafaPop3sEnable,"CheckZarafaFields()")."</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:12px'>{pop3s_port}:</td>
				<td>". Field_text("ZarafaPop3sPort",$ZarafaPop3sPort,"width:90px;font-size:13px;padding:3px")."</td>
			</tr>	
			
			
			<tr>
				<td class=legend style='font-size:12px'>{enable_imap}:</td>
				<td><strong style='font-size:13px'>". Field_checkbox("ZarafaIMAPEnable", 1,$ZarafaIMAPEnable,"CheckZarafaFields()")."</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:12px'>{imap_port}:</td>
				<td>". Field_text("ZarafaIMAPPort",$ZarafaIMAPPort,"width:90px;font-size:13px;padding:3px")."</td>
			</tr>

			<tr>
				<td class=legend style='font-size:12px'>{enable_imaps}:</td>
				<td><strong style='font-size:13px'>". Field_checkbox("ZarafaIMAPsEnable", 1,$ZarafaIMAPsEnable,"CheckZarafaFields()")."</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:12px'>{imaps_port}:</td>
				<td>". Field_text("ZarafaIMAPsPort",$ZarafaIMAPsPort,"width:90px;font-size:13px;padding:3px")."</td>
			</tr>				
			<tr><td colspan=2>&nbsp;</td>			
			
			<tr>
				<td class=legend style='font-size:12px'>{smtp_server}:</td>
				<td><strong style='font-size:13px'>$SMTPfield</strong></td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{smtp_server_port}:</td>
				<td>". Field_text("ZarafaServerSMTPPORT",$ZarafaServerSMTPPORT,"width:90px;font-size:13px;padding:3px")."</td>
			</tr>
			<tr><td colspan=2>&nbsp;</td>						
			<tr>
				<td class=legend style='font-size:12px'>{ZarafaStoreOutside}:</td>
				<td>". Field_checkbox("ZarafaStoreOutside",1,$ZarafaStoreOutside,"CheckZarafaFields()")."</td>
			</tr>	
			<tr>
				<td class=legend style='font-size:12px'>{attachments_path}:</td>
				<td>". Field_text("ZarafaStoreOutsidePath",$ZarafaStoreOutsidePath,"width:220px;font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:12px'>{attachments_compression_level}:</td>
				<td>". Field_array_Hash($ZarafaStoreCompressionLevelAr,"ZarafaStoreCompressionLevel",$ZarafaStoreCompressionLevel,"style:font-size:13px;padding:3px")."</td>
			</tr>	
			<tr>
				<td colspan=2 align='right'><a href=\"javascript:blur();\" OnClick=\"DbAttachConverter()\" 
				style='font-size:13px;text-decoration:underline'>{convert_current_attachments}</a></td>
			</tr>
			<tr><td colspan=2 align='right'><hr>". button("{apply}","APP_ZARAFA_WEB_SAVE()")."</td></tr>																	
		</table>		

		<p>&nbsp;</p>
		
	<table style='width:100%' class=form>
		<tr><td colspan=2><H3>{APP_ZARAFA_ICAL}</H3></td></tr>
			<tr>
				<td class=legend style='font-size:12px'>{enable}:</td>
				<td>". Field_checkbox("ZarafaiCalEnable",1,$ZarafaiCalEnable)."</td>
			</tr>		
			<tr>
				<td class=legend style='font-size:12px'>{listen_port}:</td>
				<td>". Field_text("ZarafaiCalPort",$ZarafaiCalPort,"font-size:13px;padding:3px;width:60px")."</td>
			</tr>		
		</table>
		
		
		
		
		<p>&nbsp;</p>
		
	<table style='width:100%' class=form>
		<tr>
			<td colspan=2><H3>{other_settings}</H3></td></tr>
			<tr>
				<td class=legend style='font-size:12px'>{user_safe_mode}:</td>
				<td width=1%>". Field_checkbox("ZarafaUserSafeMode",1,$ZarafaUserSafeMode)."</td>
				<td width=1%>". help_icon("{user_safe_mode_text}")."</td>
			</tr>
			</table>	
			
			
			
	</td>
	</tr>
	<tr>
				<td colspan=2 align='right'>
				<hr>
					". button("{apply}","APP_ZARAFA_WEB_SAVE()")."
				</td>
			</tr>	
</table>

<script>
	function DbAttachConverter(){
		YahooWin('550','$page?DbAttachConverter-popup=yes','$convert_current_attachments_text');
	
	}


	function CheckZarafaFields(){
		var ZarafaAspellInstalled=$ZarafaAspellInstalled;
		var ZarafaStoreOutside=$ZarafaStoreOutside;	
		document.getElementById('ZarafaStoreOutsidePath').disabled=true;
		document.getElementById('ZarafaStoreCompressionLevel').disabled=true;
		document.getElementById('ZarafaAspellEnabled').disabled=true;
		
		document.getElementById('ZarafaPop3Port').disabled=true;
		document.getElementById('ZarafaIMAPPort').disabled=true;
		document.getElementById('ZarafaPop3sPort').disabled=true;
		document.getElementById('ZarafaIMAPsPort').disabled=true;
		
		if(document.getElementById('ZarafaPop3Enable').checked){document.getElementById('ZarafaPop3Port').disabled=false;}
		if(document.getElementById('ZarafaPop3sEnable').checked){document.getElementById('ZarafaPop3sPort').disabled=false;}
		if(document.getElementById('ZarafaIMAPEnable').checked){document.getElementById('ZarafaIMAPPort').disabled=false;}
		if(document.getElementById('ZarafaIMAPsEnable').checked){document.getElementById('ZarafaIMAPsPort').disabled=false;}
		
		
		
		
		
		
		if(document.getElementById('ZarafaStoreOutside').checked){
			document.getElementById('ZarafaStoreOutsidePath').disabled=false;
			document.getElementById('ZarafaStoreCompressionLevel').disabled=false;
		}
		
		if(ZarafaAspellInstalled==1){
			document.getElementById('ZarafaAspellEnabled').disabled=false;
		}
		
		
	}
	CheckZarafaFields();
</script>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function DbAttachConverter_popup(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$ZarafaStoreOutside=$sock->GET_INFO("ZarafaStoreOutside");
	if($ZarafaStoreOutside<>1){echo "<script>YahooWinHide();</script>";return;}
	
	$html="
	<div class=explain id='zarafa_store_outside_div'>{zarafa_store_outside_text}</div>
	<center style='margin:10px'>". button("{run}","DbAttachConverterPerform()")."</center>
	
	<script>
var X_DbAttachConverterPerform= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWinHide();
	}	
	

	
function DbAttachConverterPerform(){
	var XHR = new XHRConnection();
	XHR.appendData('DbAttachConverterPerform','yes');
	XHR.sendAndLoad('$page', 'GET',X_DbAttachConverterPerform);		
	}
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function DbAttachConverter_Perform(){
	
	$q=new mysql();
	$sock=new sockets();
	$ZarafaStoreOutsidePath=$sock->GET_INFO("ZarafaStoreOutsidePath");
	if($ZarafaStoreOutsidePath==null){$ZarafaStoreOutsidePath="/var/lib/zarafa";}
	$sqladm=urlencode(base64_encode($q->mysql_admin));
	$sqlpass=urlencode(base64_encode($q->mysql_password));
	$attachpath=urlencode($ZarafaStoreOutsidePath);
	$sock->getFrameWork("zarafa.php?DbAttachConverter=yes&sqladm=$sqladm&mysqlpass=$sqlpass&path=$attachpath");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{cyrreconstruct_wait}");
	
	
}



?>