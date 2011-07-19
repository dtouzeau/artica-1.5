<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf.inc');

	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["relayhostSave"])){relayhostSave();exit;}
	if(isset($_GET["RelayHostDelete"])){RelayHostDelete();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	js();
	
function js(){

$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{relayhost}");
$ou=$_GET["ou"];
$hostname=$_GET["hostname"];
$page=CurrentPageName();
$html="

function PostfixMultiRelayHostLoad(){
	YahooWin3('550','$page?popup=yes&ou=$ou&hostname=$hostname','$hostname::$title');
	}


var X_PostfixSaveRelayHost= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		YahooWin3Hide();
	}		
function PostfixSaveRelayHost(){
		var XHR = new XHRConnection();
		XHR.appendData('relayhostSave','yes');
		XHR.appendData('relay_address',document.getElementById('relay_address').value);
		XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);
		XHR.appendData('relay_port',document.getElementById('relay_port').value);
		XHR.appendData('username',document.getElementById('username').value);
		XHR.appendData('password',document.getElementById('password').value);
		XHR.appendData('ou','$ou');
		XHR.appendData('hostname','$hostname');
		document.getElementById('relayhostdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',X_PostfixSaveRelayHost);
		
	}
function RelayHostDelete(){
		var XHR = new XHRConnection();
		XHR.appendData('RelayHostDelete','yes');
		XHR.appendData('ou','$ou');
		XHR.appendData('hostname','$hostname');		
		XHR.sendAndLoad('$page', 'GET',X_PostfixSaveRelayHost);
	}
PostfixMultiRelayHostLoad();";
	
	echo $html;
}
	
	
function popup(){
	$ou=$_GET["ou"];
	$hostname=$_GET["hostname"];
	
$main=new maincf_multi($hostname,$ou);
$relayhost=$main->GET("relayhost");
if(preg_match("#(.+?):(.+)#",$main->GET("relayhost_authentication"),$re)){
		$username=$re[1];
		$password=$re[2];
	}

$page=CurrentPageName();



if($relayhost<>null){
		$tools=new DomainsTools();
		$relayhost="smtp:$relayhost";
		$relayT=$tools->transport_maps_explode($relayhost);
}	

if($relayT[1]<>null){
	$delete=imgtootltip("delete-48.png","{delete}","RelayHostDelete()");
}
	

$form="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		</td>
		<td valign='top'>
			<div class=explain>{relayhost_text}</div>
			<div id='relayhostdiv'>
					<table style='width:100%'>
					<tr>
						<td valign='top'>
						<table style='width:100%'>
						<td align='right' nowrap class=legend style='font-size:14px'>{relay_address}:</strong></td>
						<td style='font-size:12px'>" . Field_text('relay_address',$relayT[1],"font-size:14px;padding:3px") . "</td>	
						</tr>
						</tr>
							<td align='right' nowrap class=legend style='font-size:14px'>{smtp_port}:</strong></td>
							<td style='font-size:12px'>" . Field_text('relay_port',$relayT[2],";font-size:14px;padding:3px;width:90px") . "</td>	
						</tr>	
						<tr>
							<td align='right' nowrap style='font-size:14px'>" . Field_yesno_checkbox_img('MX_lookups',$relayT[3],'{enable_disable}')."</td>
							<td style='font-size:12px'>{MX_lookups}</td>	
						</tr>
						</tr>
							<td align='right' nowrap class=legend style='font-size:14px'>{username}:</strong></td>
							<td style='font-size:12px'>" . Field_text('username',$username,"font-size:14px;padding:3px") . "</td>	
						</tr>
						</tr>
							<td align='right' nowrap class=legend style='font-size:14px'>{password}:</strong></td>
							<td style='font-size:12px'>" . Field_password('password',$password,"font-size:14px;padding:3px") . "</td>	
						</tr>						
						<tr>
						<td align='right' colspan=2 align='right'>". button("{apply}","PostfixSaveRelayHost()")."</td>
						</tr>		
						<tr>
						<td align='left' colspan=2><hr><div class=explain>{MX_lookups}<br>{MX_lookups_text}</div></td>
						</tr>					
						</form>
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
</table>";



$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$form");		
	
}

function RelayHostDelete(){
	$ou=$_GET["ou"];
	$hostname=$_GET["hostname"];
	$main=new maincf_multi($hostname,$ou);
	$main->DELETE_KEY("relayhost_authentication");
	$main->DELETE_KEY("relayhost");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-relayhost={$_GET["hostname"]}");		
	
	
}

function relayhostSave(){
	$ou=$_GET["ou"];
	$hostname=$_GET["hostname"];
	$tpl=new templates();
	
	if($_GET["relay_port"]==null){$_GET["relay_port"]=25;}
	if($_GET["relay_address"]==null){
		echo $tpl->_ENGINE_parse_body("{error_no_server_specified}");
		exit;
	}	
	$tool=new DomainsTools();
	writelogs("Port={$_GET["relay_port"]} address={$_GET["relay_address"]}",__FUNCTION__,__FILE__);
	$data=$tool->transport_maps_implode($_GET["relay_address"],$_GET["relay_port"],'smtp',$_GET["MX_lookups"]);
	writelogs("Port={$_GET["relay_port"]} address={$_GET["relay_address"]}=$data",__FUNCTION__,__FILE__);
	$data=str_replace('smtp:','',$data);
	
	
	//smtp_sasl_password_maps

	$main=new maincf_multi($hostname,$ou);
	if($_GET["username"]<>null){
		$auth="{$_GET["username"]}:{$_GET["password"]}";
		$main->SET_VALUE("relayhost_authentication",$auth);
	}
	
	$main->SET_VALUE("relayhost",$data);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-relayhost={$_GET["hostname"]}");	
	}
	

?>