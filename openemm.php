<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");

$user=new usersMenus();
if($user->AsPostfixAdministrator==false){die();}

if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["params"])){params();exit;}
if(isset($_GET["events"])){events();exit;}
if(isset($_GET["events-details"])){events_details();exit;}
if(isset($_POST["OpenEMMServerURL"])){SaveMasterConf();exit;}




js();


function js(){
	
	$page=CurrentPageName();
	echo "AnimateDiv('BodyContent');
	$('#BodyContent').load('$page?tabs=yes');";
	
}

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$array["popup"]="{status}";
	$array["params"]="{parameters}";
	$array["events"]="{events}";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]=  $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_openemm style='width:100%;height:600px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_openemm\").tabs();});
		</script>";		
	
	
	
}

function events(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$html="
	<div style='font-size:16px;font-weight:bold;margin-bottom:10px'>{APP_OPENEMM_SENDMAIL}::{events}</div>
	<div id='sendmail-events' style='width:100%;height:500px;overflow:auto'></div>
	
	<script>
		function RefreshSendMailEvents(){
			LoadAjax('sendmail-events','$page?events-details=yes');
		}
		RefreshSendMailEvents();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function events_details(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$filter="sendmail";
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-tail=yes&filter=$filter")));
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-24.png","{refresh}","RefreshSendMailEvents()")."</th>
		<th>{APP_OPENEMM_SENDMAIL}  v.$users->OPENEMM_SENDMAIL_VERSION</th>
	</tr>
</thead>
<tbody class='tbody'>";	

		$tbl=array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$color="black";
				$html=$html."
				<tr class=$classtr>
					<td colspan=2 style='font-size:12px;font-weight:bold;color:$color'>$val</td>
				</tr>
		";		
		}	
		
		echo $tpl->_ENGINE_parse_body($html."</table>");	
	
	
}

function params(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$users=new usersMenus();
	$OpenEMMServerURL=$sock->GET_INFO("OpenEMMServerURL");
	if($OpenEMMServerURL==null){$OpenEMMServerURL="http://{$_SERVER["SERVER_NAME"]}:8080";}
	
	$OpenEMMMailErrorRecipient=$sock->GET_INFO("OpenEMMMailErrorRecipient");
	if($OpenEMMMailErrorRecipient==null){$OpenEMMMailErrorRecipient="openemm@localhost";}
	
	$OpenEMMNextMTA=$sock->GET_INFO("OpenEMMNextMTA");
	$OpenEMMNextMTAPort=$sock->GET_INFO("OpenEMMNextMTAPort");
	$OpenEMMSendMailPort=$sock->GET_INFO("OpenEMMSendMailPort");
	if($OpenEMMNextMTA==null){$OpenEMMNextMTA="nextsmtp.domain.tld";}
	if(!is_numeric($OpenEMMNextMTAPort)){$OpenEMMNextMTAPort="25";}
	if(!is_numeric($OpenEMMSendMailPort)){$OpenEMMSendMailPort="6880";}
	
	
	$sendmailInstalled=1;
	if(!$users->OPENEMM_SENDMAIL_INSTALLED){$sendmailInstalled=0;}
	
	
	$OpenEMMUserAgent=$sock->GET_INFO("OpenEMMUserAgent");
	if($OpenEMMUserAgent==null){$OpenEMMUserAgent="OpenEMM V2011";}	

$html="
<div id='openemm-params'>
<table style='width:100%' class=form>
	<tr>
		<td class=legend>{server_url}:</td>
		<td>". Field_text("OpenEMMServerURL",$OpenEMMServerURL,"font-size:14px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend>SMTP&nbsp;{useragent}:</td>
		<td>". Field_text("OpenEMMUserAgent",$OpenEMMUserAgent,"font-size:14px;padding:3px;width:180px")."</td>
	</tr>	
	<tr>
		<td class=legend>{mailerror_recipient}:</td>
		<td>". Field_text("OpenEMMMailErrorRecipient",$OpenEMMMailErrorRecipient,"font-size:14px;padding:3px;width:180px")."</td>
	</tr>
	<tr>
		<td class=legend>{OpenEMMSendMailPort}:</td>
		<td>". Field_text("OpenEMMSendMailPort",$OpenEMMSendMailPort,"font-size:14px;padding:3px;width:60px")."</td>
	</tr>
	<tr>
		<td class=legend>{OpenEMMNextMTA}:</td>
		<td>". Field_text("OpenEMMNextMTA",$OpenEMMNextMTA,"font-size:14px;padding:3px;width:180px")."</td>
	</tr>
	<tr>
		<td class=legend>{OpenEMMNextMTAPort}:</td>
		<td>". Field_text("OpenEMMNextMTAPort",$OpenEMMNextMTAPort,"font-size:14px;padding:3px;width:60px")."</td>
	</tr>				
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveOpenEMMConfig()")."</td>
	</tR>
	
	
	</table>
</div>	
	<script>
	var x_SaveOpenEMMConfig= function (obj) {
		var results=obj.responseText;
		RefreshTab('main_config_openemm'); 
		}
		
	function SaveOpenEMMConfig(){
		var XHR = new XHRConnection();
		XHR.appendData('OpenEMMServerURL',document.getElementById('OpenEMMServerURL').value);
		XHR.appendData('OpenEMMUserAgent',document.getElementById('OpenEMMUserAgent').value);
		XHR.appendData('OpenEMMMailErrorRecipient',document.getElementById('OpenEMMMailErrorRecipient').value);
		
		if(document.getElementById('OpenEMMSendMailPort').value==25){alert('25 -> this port is incompatible with local postfix instance(s))');return;}
		XHR.appendData('OpenEMMSendMailPort',document.getElementById('OpenEMMSendMailPort').value);
		XHR.appendData('OpenEMMNextMTA',document.getElementById('OpenEMMNextMTA').value);
		XHR.appendData('OpenEMMNextMTAPort',document.getElementById('OpenEMMNextMTAPort').value);
		
		AnimateDiv('openemm-params');
		XHR.sendAndLoad('$page', 'POST',x_SaveOpenEMMConfig);				
	}	
	
	function CheckIfsendmail(){
		var sendmailInstalled=$sendmailInstalled;
		document.getElementById('OpenEMMSendMailPort').disabled=true;
		document.getElementById('OpenEMMNextMTA').disabled=true;
		document.getElementById('OpenEMMNextMTAPort').disabled=true;
		if(sendmailInstalled==0){return;}
		document.getElementById('OpenEMMSendMailPort').disabled=false;
		document.getElementById('OpenEMMNextMTA').disabled=false;
		document.getElementById('OpenEMMNextMTAPort').disabled=false;		
	}
	CheckIfsendmail();
	</script>
";	
	echo $tpl->_ENGINE_parse_body($html);	
}


function popup() {
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();

	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><div id='openemm-status'></div></td>
		<td valign='top' width=100%>
			<div class=explain>{OPENEMM_ABOUT}</div>
			<table class=form>
			<tr>
				<td style='font-size:14px' class=legend><strong>{web_console}:</strong>
				<td style='font-size:14px'>http://{$_SERVER["SERVER_NAME"]}:8080</td>
			</tr>
			<tr>
				<td style='font-size:14px' class=legend><strong>{username}:</strong>
				<td style='font-size:14px'>admin</td>
			</tr>			
			<tr>
				<td style='font-size:14px' class=legend><strong>{password}:</strong>
				<td style='font-size:14px'>openemm</td>
			</tr>			
			</table>
			</td>
	</tr>
	</table>
	
	<script>
	
	
	function RefreshStatus(){
		LoadAjax('openemm-status','$page?status=yes');
	
	}
	RefreshStatus();
	
	</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function status(){
	
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshStatus()")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$datas=$sock->getFrameWork("services.php?openemm-status=yes");
	writelogs(strlen($datas)." bytes for openemm status",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	$ini->loadString(base64_decode($datas));
	$status=DAEMON_STATUS_ROUND("APP_OPENEMM",$ini,null,0)."<br>".DAEMON_STATUS_ROUND("APP_OPENEMM_SENDMAIL",$ini,null,0).$refresh;
	echo $tpl->_ENGINE_parse_body($status);		
	
}

function SaveMasterConf(){
	$sock=new sockets();
	$sock->SET_INFO("OpenEMMServerURL", $_POST["OpenEMMServerURL"]);
	$sock->SET_INFO("OpenEMMMailErrorRecipient", $_POST["OpenEMMMailErrorRecipient"]);
	$sock->SET_INFO("OpenEMMUserAgent", $_POST["OpenEMMUserAgent"]);
	
	$sock->SET_INFO("OpenEMMNextMTA", $_POST["OpenEMMNextMTA"]);
	$sock->SET_INFO("OpenEMMNextMTAPort", $_POST["OpenEMMNextMTAPort"]);
	$sock->SET_INFO("OpenEMMSendMailPort", $_POST["OpenEMMSendMailPort"]);

	$sock->getFrameWork("services.php?restart-openemm=yes");
}


