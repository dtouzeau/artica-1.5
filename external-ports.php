<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.roundcube.inc');
	include_once('ressources/class.main_cf.inc');	

	$usersmenus=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");
		die();		
	}	
	
		if(isset($_GET["index"])){external_ports_index();exit;}
		if(isset($_GET["artica_port"])){ARTICA_PORT_SAVE();exit;}
		if(isset($_GET["APP_ROUNDCUBE"])){APP_ROUNDCUBE();exit;}
		if(isset($_GET["APP_OBM2"])){APP_OBM2();exit;}
		if(isset($_GET["APP_APACHE"])){APP_APACHE();APP_GROUPWARE_APACHE();exit;}
		if(isset($_GET["EnableApacheSystem"])){EnableApacheSystem();exit;}
		if(isset($_GET["ApacheGroupware"])){ApacheGroupwareSave();exit;}
		
		
js();
	
	
function js(){

$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{EXTERNAL_PORTS}');

$html="

	function ExternalPortsPage(){
		YahooWin5('550','$page?index=yes','$title');
	
	}
	
	var x_ChangeExternalPorts= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		ExternalPortsPage();
	}

	

	
function ChangeArticaPort(){
		var artica_port=document.getElementById('artica_port').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('artica_port',artica_port);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}

function ChangeRoundCubePort(){
		var APP_ROUNDCUBE=document.getElementById('APP_ROUNDCUBE').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('APP_ROUNDCUBE',APP_ROUNDCUBE);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}
function ChangeOBM2Port(){
		var APP_OBM2=document.getElementById('APP_OBM2').value;
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('APP_OBM2',APP_OBM2);
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);
		 		
	}	

function ChangeApachePort(){
		var XHR = new XHRConnection();
		if(document.getElementById('APP_APACHE').value==document.getElementById('APP_GROUPWARE_APACHE').value){
			alert(document.getElementById('APP_APACHE').value+'='+document.getElementById('APP_GROUPWARE_APACHE').value);
			return;			
		}
		var APP_GROUPWARE_APACHE=document.getElementById('APP_GROUPWARE_APACHE').value;
		XHR.appendData('APP_APACHE',document.getElementById('APP_APACHE').value);
		XHR.appendData('APP_APACHE_SSL',document.getElementById('APP_APACHE_SSL').value);
		XHR.appendData('APP_GROUPWARE_APACHE',APP_GROUPWARE_APACHE);
		document.getElementById('externalportsdiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_ChangeExternalPorts);

}

	function EnableApacheSystemS(){
		var XHR = new XHRConnection();
		if(document.getElementById('EnableApacheSystem').checked){
			XHR.appendData('EnableApacheSystem',1);}else{XHR.appendData('EnableApacheSystem',0);}
		EnableApacheSystemCHK();
		XHR.sendAndLoad('$page', 'GET');
	}
	
	
	function EnableApacheGroupware(){
		var XHR = new XHRConnection();
		if(document.getElementById('ApacheGroupware').checked){XHR.appendData('ApacheGroupware',1);}else{XHR.appendData('ApacheGroupware',0);}
		XHR.sendAndLoad('$page', 'GET');
		EnableApacheGroupwareCheck();	
	
	}
	
	function EnableApacheGroupwareCheck(){
		if(!document.getElementById('ApacheGroupware')){return;}
		document.getElementById('APP_GROUPWARE_APACHE').disabled=true;
		if(document.getElementById('ApacheGroupware').checked){
			document.getElementById('APP_GROUPWARE_APACHE').disabled=false;
		}
	}
	

	function EnableApacheSystemCHK(){
		if(!document.getElementById('EnableApacheSystem')){return;}
		document.getElementById('APP_APACHE').disabled=true;
		document.getElementById('APP_APACHE_SSL').disabled=true;
		if(document.getElementById('EnableApacheSystem').checked){
			document.getElementById('APP_APACHE').disabled=false;
			document.getElementById('APP_APACHE_SSL').disabled=false;
		}
	}
	
	
	
	
	
	
	
ExternalPortsPage();
";
	echo $html;
	
}	

function external_ports_index(){

	$sock=new sockets();
	
	$users=new usersMenus();
	
	if($users->APACHE_INSTALLED){
		$port=$sock->GET_INFO("ApacheGroupWarePort");
		$EnableApacheSystem=$sock->GET_INFO("EnableApacheSystem");
		$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
		
		$EnableFreeWeb=$sock->GET_INFO("EnableFreeWeb");
		if($EnableApacheSystem==null){$EnableApacheSystem=1;}
		if($EnableFreeWeb==1){$EnableApacheSystem=1;}
		if($ApacheGroupware==null){$ApacheGroupware=1;}
		
		
		$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
		if($FreeWebListenPort==null){$FreeWebListenPort=80;}	
		$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");			
		if($FreeWebListenSSLPort==null){$FreeWebListenSSLPort="443";}
		
		if(!is_numeric($FreeWebListenSSLPort)){$FreeWebListenSSLPort=443;}
		if(!is_numeric($FreeWebListenPort)){$FreeWebListenPort=80;}			
		
		
		$APACHE_GROUPWARE="
		<tr class=oddRow>
			<td  class=legend nowrap>{APP_APACHE_SYSTEM}:</td>
			<td  nowrap>(HTTP)</td>
			<td align=center>". Field_checkbox("EnableApacheSystem",1,$EnableApacheSystem,"EnableApacheSystemS()")."</td>
			<td  width=1%>" . Field_text('APP_APACHE',$FreeWebListenPort,'width:60px;font-size:13px;padding:3px')."</td>
			<td  width=1%><input type='button' OnClick=\"javascript:ChangeApachePort()\" value='{edit}&nbsp;&raquo;' ></td>
		</tr>

		<tr>
			<td  class=legend nowrap>{APP_APACHE_SYSTEM}:</td>
			<td  nowrap>(SSL)</td>
			<td align=center>&nbsp;</td>
			<td  width=1%>" . Field_text('APP_APACHE_SSL',$FreeWebListenSSLPort,'width:60px;font-size:13px;padding:3px')."</td>
			<td  width=1%><input type='button' OnClick=\"javascript:ChangeApachePort()\" value='{edit}&nbsp;&raquo;' ></td>
		</tr>		
		
		
		<tr class=oddRow>
			<td  class=legend nowrap>{APP_GROUPWARE_APACHE}:</td>
			<td  nowrap>(HTTP)</td>
			<td align=center>". Field_checkbox("ApacheGroupware",1,$ApacheGroupware,"EnableApacheGroupware()")."</td>
			<td  width=1%>" . Field_text('APP_GROUPWARE_APACHE',$port,'width:60px;font-size:13px;padding:3px')."</td>
			<td  width=1%><input type='button' OnClick=\"javascript:ChangeApachePort()\" value='{edit}&nbsp;&raquo;' ></td>
		</tr>";
		
	}else{
		$APACHE_GROUPWARE="
		<tr>
		<td  class=legend nowrap>{APP_GROUPWARE_APACHE}:</td>
		<td  nowrap>(HTTP)</td>
		<td>&nbsp;</td>
		<td  width=1% align='center'>--</td>
		<td  width=1% align='center'>--</td>
		</tr>";
	}
	
	
	
$APP_ROUNDCUBE="
		<tr class=oddRow>
		<td  class=legend nowrap>{APP_ROUNDCUBE}:</td>
		<td  nowrap>(HTTPS)</td>
		<td>&nbsp;</td>
		<td  width=1% align='center'>--</td>
		<td  width=1% align='center'>--</td>
		</tr>";	
	
	if($users->roundcube_installed){
		$round=new roundcube();
		if($round->RoundCubeHTTPEngineEnabled==1){
		$port=$round->https_port;
		$APP_ROUNDCUBE="
		<tr class=oddRow>
		<td  class=legend nowrap>{APP_ROUNDCUBE}:</td>
		<td  nowrap>(HTTPS)</td>
		<td>&nbsp;</td>
		<td  width=1%>" . Field_text('APP_ROUNDCUBE',$port,'width:60px;font-size:13px;padding:3px')."</td>
		<td  width=1%><input type='button' OnClick=\"javascript:ChangeRoundCubePort()\" value='{edit}&nbsp;&raquo;' ></td>
		</tr>";
		}}
	
$APP_OBM2="
		<tr >
		<td  class=legend nowrap>{APP_OBM2}:</td>
		<td  nowrap>(HTTP)</td>
		<td>&nbsp;</td>
		<td  width=1% align='center'>--</td>
		<td  width=1% align='center'>--</td>
		</tr>";

if($users->OBM2_INSTALLED){
		$Obm2ListenPort=trim($sock->GET_INFO('Obm2ListenPort'));
		$APP_OBM2="
		<tr >
		<td  class=legend nowrap>{APP_OBM2}:</td>
		<td  nowrap>(HTTP)</td>
		<td>&nbsp;</td>
		<td  width=1%>" . Field_text('APP_OBM2',$Obm2ListenPort,'width:60px;font-size:13px;padding:3px')."</td>
		<td  width=1%><input type='button' OnClick=\"javascript:ChangeOBM2Port()\" value='{edit}&nbsp;&raquo;' ></td>
		</tr>";
		}
		
		
if($users->POSTFIX_INSTALLED){
		
		$APP_POSTFIX_SMTP="
		<tr class=oddRow>
		<td  class=legend nowrap>{APP_POSTFIX}:</td>
		<td  nowrap>(SMTP)</td>
		<td>&nbsp;</td>
		<td  width=1%><code style='font-size:13px;font-weight:bold'>25</code></td>
		<td  width=1% align=center>--</td>
		</tr>";
		
		$master=new master_cf(1);
		if($master->PostfixEnableMasterCfSSL==1){
			$APP_POSTFIX_SMTPS="
		<tr>
		<td  class=legend nowrap>{APP_POSTFIX}:</td>
		<td  nowrap>(SMTPs)</td>
		<td>&nbsp;</td>
		<td  width=1%><code style='font-size:13px;font-weight:bold'>465</code></td>
		<td  width=1% align=center>--</td>
		</tr>";
		}
		
		$sock=new sockets();
		$PostfixEnableSubmission=$sock->GET_INFO("PostfixEnableSubmission");		
		
		if($PostfixEnableSubmission==1){
			$APP_POSTFIX_SUBMISSION="
		<tr class=oddRow>
		<td  class=legend nowrap>{APP_POSTFIX}:</td>
		<td  nowrap>(subm.)</td>
		<td>&nbsp;</td>
		<td  width=1%><code style='font-size:13px;font-weight:bold'>587</code></td>
		<td  width=1% align=center>--</td>
		</tr>";
		}		
		
		}		
		
		
	
	
	$httpd=new httpd();
	$artica_port=$httpd->https_port;
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td  width=1%><img src='img/64-bind.png'</td>
		<td><div class=explain>{EXTERNAL_PORTS_TEXT}</div></td>
	</tr>
	</table>
	
	
	<div id='externalportsdiv'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
			<th colspan=2>{services}</th>
			<th>{enable}</th>
			<th>{port}</th>
			<th>&nbsp;</th>
	</tr>
</thead>
<tbody>
	
	<tr class=oddRow>
		<td  class=legend nowrap>{APP_ARTICA}:</td>
		<td  nowrap>(HTTPS)</td>
		<td>&nbsp;</td>
		<td  width=1%>" . Field_text('artica_port',$artica_port,'width:60px;font-size:13px;padding:3px')."</td>
		<td  width=1%><input type='button' OnClick=\"javascript:ChangeArticaPort()\" value='{edit}&nbsp;&raquo;' ></td>
	</tr>
	$APACHE_GROUPWARE
	$APP_ROUNDCUBE
	$APP_OBM2
	$APP_POSTFIX_SMTP
	$APP_POSTFIX_SMTPS
	$APP_POSTFIX_SUBMISSION
		
	
	</tbody>
	</table>
	</div>
	<script>
	
	EnableApacheSystemCHK();
	EnableApacheGroupwareCheck();
	
	
	
	function EnableApElements(id){
		DisableFieldsFromId('externalportsdiv');
		EnableFieldsFromId(id);
		EnableApacheSystemCHK();
		EnableApacheGroupwareCheck();		
	}
	
	
	function DisableAll(){
		DisableFieldsFromId('externalportsdiv');
		
	}
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function ARTICA_PORT_SAVE(){
	$httpd=new httpd();
	$httpd->https_port=$_GET["artica_port"];
	$httpd->SaveToServer();
	
}

function APP_GROUPWARE_APACHE(){
	$sock=new sockets();
	$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
	if($FreeWebListenPort==$_GET["APP_GROUPWARE_APACHE"]){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{APP_APACHE_SYSTEM}:$FreeWebListenPort == {APP_GROUPWARE_APACHE}:{$_GET["APP_GROUPWARE_APACHE"]}");
		return;
	}
	
	$sock->SET_INFO("ApacheGroupWarePort",$_GET["APP_GROUPWARE_APACHE"]);
	$sock->getFrameWork("cmd.php?RestartApacheGroupwareNoForce=yes");
	}
	
	
	
function APP_ROUNDCUBE(){
$round=new roundcube();
$round->https_port=$_GET["APP_ROUNDCUBE"];
$round->Save();
	
}

function APP_OBM2(){
	$sock=new sockets();
	$sock->SET_INFO("Obm2ListenPort",$_GET["APP_OBM2"]);
	$sock->getfile("Obm2restart");
	}
	
function APP_APACHE(){
	$sock=new sockets();
	if(!is_numeric($_GET["APP_APACHE"])){$_GET["APP_APACHE"]=80;}
	if(!is_numeric($_GET["APP_APACHE_SSL"])){$_GET["APP_APACHE_SSL"]=443;}
	$sock->SET_INFO("FreeWebListenPort",$_GET["APP_APACHE"]);
	$sock->SET_INFO("FreeWebListenSSLPort",$_GET["APP_APACHE_SSL"]);
	$sock->getFrameWork("cmd.php?restart-apache-src=yes");	
	$sock->DeleteCache();		
}

function EnableApacheSystem(){
	$sock=new sockets();
	$sock->SET_INFO("EnableFreeWeb",$_GET["EnableApacheSystem"]);
	$sock->SET_INFO("EnableApacheSystem",$_GET["EnableApacheSystem"]);
	$sock->getFrameWork("cmd.php?restart-apache-src=yes");	
	
}

function ApacheGroupwareSave(){
	$sock=new sockets();
	$sock->SET_INFO("ApacheGroupware",$_GET["ApacheGroupware"]);
	$sock->SET_INFO("ShowApacheGroupware",$_GET["ApacheGroupware"]);
	$sock->getFrameWork("cmd.php?RestartApacheGroupwareNoForce=yes");	
	$sock->getFrameWork("cmd.php?build-vhosts=yes");
	
	
	
}


?>