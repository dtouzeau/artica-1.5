<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["popup"])){echo popup();exit;}
	if(isset($_GET["CyrusLMTPListen"])){save();exit;}
	
js();


function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{cyrus_net_behavior}');
	$page=CurrentPageName();
	$prefix="cyrus_lmtp_php";
$html="

function CYRUS_LMTP(){
	YahooWin('550','$page?popup=yes','$title');
}

	function CyrusEnableLMTPUnixSwitch(){
			if(document.getElementById('CyrusEnableLMTPUnix').checked){
				document.getElementById('lmtp_ipaddr').disabled=true;
				document.getElementById('lmtp_port').disabled=true;
			}else{
				document.getElementById('lmtp_ipaddr').disabled=false;
				document.getElementById('lmtp_port').disabled=false;			
			}
			
		}




CYRUS_LMTP();";
	
	echo $html;
}

function popup(){
	
	$sock=new sockets();
	$page=CurrentPageName();
	$CyrusEnableLMTPUnix=$sock->GET_INFO("CyrusEnableLMTPUnix");
	if($CyrusEnableLMTPUnix==null){$CyrusEnableLMTPUnix=1;}
	$nets["127.0.0.1"]="127.0.0.1";
	$net=new networking();
	while (list ($num, $ipaddr) = each ($net->array_TCP) ){
		if($ipaddr==null){continue;}
		$nets[$ipaddr]=$ipaddr;
	}
	
	$CyrusLMTPListen=trim($sock->GET_INFO("CyrusLMTPListen"));
	if($CyrusLMTPListen==null){$CyrusLMTPListen="127.0.0.1:2005";}
	
	
	if(preg_match("#(.+?):(.+)#",$CyrusLMTPListen,$re)){
		$ipaddr_listen=$re[1];
		$port=$re[2];
	}
	
	
	$lmtp_unix="/var/spool/postfix/var/run/cyrus/socket/lmtp";
	
	$html="
	<div id='cyrus_lmtp_div'>
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/database-connect-settings-90.png'></td>
		<td valign='top'>
	<div style='font-size:13px;padding:5px'>{cyrus_lmtp_howto}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{enable_lmtp_unix}:</td>
		<td>". Field_checkbox("CyrusEnableLMTPUnix",1,$CyrusEnableLMTPUnix,"CyrusEnableLMTPUnixSwitch()")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_ip}:</td>
		<td>
			<table style='width:1%'>
			<tr>
				<td width=1%>". Field_array_Hash($nets,"lmtp_ipaddr",$ipaddr_listen,null,null,0,"font-size:13px;padding:3px")."</td>
				<td width=1%>:</td>
				<td width=1%>".Field_text("lmtp_port",$port,"font-size:13px;padding:3px;width:40px")."</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>
			". button("{apply}","CyrusEnableLMTPUnixSave()")."</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</div>
	<script>
	
	var x_CyrusEnableLMTPUnixSave= function (obj) {
		YahooWinHide();
	}	
	
	function CyrusEnableLMTPUnixSave(){
		var XHR = new XHRConnection();
		if(document.getElementById('CyrusEnableLMTPUnix').checked){XHR.appendData('CyrusEnableLMTPUnix',1);}else{XHR.appendData('CyrusEnableLMTPUnix',0);}
		XHR.appendData('CyrusLMTPListen',document.getElementById('lmtp_ipaddr').value+':'+document.getElementById('lmtp_port').value);
		document.getElementById('cyrus_lmtp_div').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_CyrusEnableLMTPUnixSave);
	}
		
		CyrusEnableLMTPUnixSwitch();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("CyrusEnableLMTPUnix",$_GET["CyrusEnableLMTPUnix"]);
	$sock->SET_INFO("CyrusLMTPListen",$_GET["CyrusLMTPListen"]);
	$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes&force=yes");
	
}



?>