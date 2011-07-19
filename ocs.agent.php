<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ocs.inc');
	
	

	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["OcsServerDest"])){save();exit;}
js();	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_OCSI_LINUX_CLIENT}');
	$prefix=str_replace(".","_",$page);
	$html="
	
	function {$prefix}LoadMain(){
		YahooWin2('700','$page?popup=yes','$title');
		}	
		
var x_SaveAglnxOCSInfos=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	 {$prefix}LoadMain();
	
}		
		
	function SaveAglnxOCSInfos(){
		var XHR = new XHRConnection();
		XHR.appendData('OcsServerDest',document.getElementById('OcsServerDest').value);
		
		if(document.getElementById('EnableOCSAgent').checked){
			XHR.appendData('EnableOCSAgent',1);
		}else{
			XHR.appendData('EnableOCSAgent',0);
		}
		
		XHR.appendData('OcsServerDestPort',document.getElementById('OcsServerDestPort').value);
		document.getElementById('oscaglnx-form').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveAglnxOCSInfos);	
	}
	
	function RefreshAgentlnxStatus(){
		LoadAjax('oscaglnx-status','$page?status=yes');
	}
		
	

{$prefix}LoadMain();
	
";

echo $html;
}	

function popup(){
	
	
	$sock=new sockets();
	$OcsServerDest=$sock->GET_INFO("OcsServerDest");
	$OcsServerDestPort=$sock->GET_INFO("OcsServerDestPort");
	$EnableOCSAgent=$sock->GET_INFO("EnableOCSAgent");
	if($EnableOCSAgent==null){$EnableOCSAgent=1;}
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'><div id='oscaglnx-form'>
			<table style='width:100%'>
			<tr>
					<td valign='top' class=legend style='font-size:13px'>{ACTIVATE_OCS_AGENT_SERVICE}:</td>
					<td valing='top'>". Field_checkbox("EnableOCSAgent",1,$EnableOCSAgent)."</td>
				</tr>			
				<tr>
					<td valign='top' class=legend style='font-size:13px'>{OCS_SERVER_ADDRESS}:</td>
					<td valing='top'>". Field_text("OcsServerDest",$OcsServerDest,"font-size:13px;padding:3px")."</td>
				</tr>
				<tr>
					<td valign='top' class=legend style='font-size:13px'>{listen_http_port}:</td>
					<td valign='top'>". Field_text("OcsServerDestPort",$OcsServerDestPort,"font-size:13px;padding:3px;width:60px")."</td>
				</tr>
				<tr>
				<td colspan=2 align='right'>
				<hr>
					". button("{apply}","SaveAglnxOCSInfos()")."</td>
				</tr>	
				</table>		
			</div>
		</td>
		<td valing='top'><div id='oscaglnx-status'></div>
	</tr>
	</table>
	<script>
		RefreshAgentlnxStatus();
	</script>
		
	
	";
	
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
	
}

function status(){
	$page=CurrentPageName();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?ocsagntlnx-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_OCSI_LINUX_CLIENT",$ini);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($status);
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("OcsServerDest",$_GET["OcsServerDest"]);
	$sock->SET_INFO("OcsServerDestPort",$_GET["OcsServerDestPort"]);
	$sock->SET_INFO("EnableOCSAgent",$_GET["EnableOCSAgent"]);
	$sock->getFrameWork("cmd.php?ocsagntlnx-restart=yes");
	
}

	

?>