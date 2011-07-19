<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");		
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica-meta.inc');
	
	

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["login"])){login();exit;}
	if(isset($_GET["register"])){inscription();exit;}
	if(isset($_GET["register-perform"])){register_perform();exit;}
	if(isset($_GET["join"])){join_perform();exit;}
	if(isset($_GET["unjoin"])){unjoin_perform();exit;}
	if(isset($_GET["ArticaMetaRemoveIndex"])){ArticaMetaRemoveIndexSave();exit;}
	if(isset($_GET["params"])){params();exit;}
	if(isset($_GET["ArticaMetaHostname"])){params_save();exit;}
	
	js();
	
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{meta-console}");
	$html="YahooWin3('650','$page?popup=yes','$title');";
	echo $html;
	
	
}

function login(){
	$sock=new sockets();
	$serial=$sock->GET_INFO("ArticaMetaSerial");
	$DisableArticaMetaAgentInformations=$sock->GET_INFO("DisableArticaMetaAgentInformations");
	$ArticaMetaRemoveIndex=$sock->GET_INFO("ArticaMetaRemoveIndex");
	$meta=new artica_meta();
	$array=unserialize(base64_decode($sock->GET_INFO("ArticaMetaRegisterDatas",true)));
	$page=CurrentPageName();
	$tpl=new templates();
	$email=$tpl->_ENGINE_parse_body("{email}");
	$password=$tpl->_ENGINE_parse_body("{password}");
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	if($array["ERROR"]){$error="<span style='font-size:16px;color:#B61313'>{$array["ERROR_TEXT"]}</span>";}
	$lock=0;
	$button=button("{join}","JoinArticaMeta()");
	
	
	if($array["REGISTERED"]){
		$button=button("{unjoin}","UnjoinArticaMeta()");
		$lock=1;
	}	
			
	$html="
	<div id='artica-join-div'>
	<div class=explain>{ArticaMetaSerial}</div>
	<div style='font-size:16px;text-align:right;margin-bottom:15px'>Artica Meta:<a href='$meta->ArticaMetaHostname'><u>$meta->ArticaMetaHostname</u></a></div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{remove_from_index_page}:</td>
		<td>". Field_checkbox("ArticaMetaRemoveIndex",1,$ArticaMetaRemoveIndex,"ArticaMetaRemoveIndexCheck()")."</td>
	</tr>
	";

	if($DisableArticaMetaAgentInformations<>1){
	$html=$html."
	<tr>
		<td class=legend>{serial}:</td>
		<td>". Field_text("serial",$serial,"font-size:13px;padding:3px","script:JoinArticaMetaCheck(event)")."</td>
	</tr>
	<tr>
		<td class=legend>{email}:</td>
		<td>". Field_text("email",$array["email"],"font-size:13px;padding:3px","")."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("password",$array["password"],"font-size:13px;padding:3px","script:JoinArticaMetaCheck(event)")."</td>
	</tr>
	<tr>
		<td class=legend>{uid}:</td>
		<td><code style='font-size:13px'>$meta->uuid</code></td>
	</tr>	
	";
	}
	$html=$html."
	<tr>
		<td colspan=2 align='right'><hr>$button</td>	
	</tr>	
	</table>
	</div>
	<center>$error</center>
	<script>

	
	function  JoinArticaMetaCheck(e){if(checkEnter(e)){JoinArticaMeta();}}
	
	function LockJoin(){
		var lock=$lock;
		if(lock=='1'){
			document.getElementById('serial').disabled=true;
			document.getElementById('email').disabled=true;
			document.getElementById('password').disabled=true;
		}
	}
	
	
	var x_ArticaMetaRemoveIndexCheck= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('admin_perso_tabs');
	}	
	
	

	function ArticaMetaRemoveIndexCheck(){
		var XHR = new XHRConnection();
		if(document.getElementById('ArticaMetaRemoveIndex').checked){
			XHR.appendData('ArticaMetaRemoveIndex',1);
		}else{
			XHR.appendData('ArticaMetaRemoveIndex',0);
		}
		
		XHR.sendAndLoad('$page', 'GET',x_ArticaMetaRemoveIndexCheck);
	}
	
	var x_JoinArticaMeta= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_articameta');
	}	
	
	
		function JoinArticaMeta(){
			var DisableArticaMetaAgentInformations='$DisableArticaMetaAgentInformations';	
			if(DisableArticaMetaAgentInformations=='1'){alert('$ERROR_NO_PRIVS');return;}	
			var password=document.getElementById('password').value;
			var email=document.getElementById('email').value;
			if(email.length==0){alert('Error: $email');return;}	
			if(password.length==0){alert('Error: $password');return;}			
			var XHR = new XHRConnection();
			XHR.appendData('join','yes');
			XHR.appendData('serial',document.getElementById('serial').value);
			XHR.appendData('email',document.getElementById('email').value);
			XHR.appendData('password',document.getElementById('password').value);
			document.getElementById('artica-join-div').innerHTML='<img src=img/wait_verybig.gif>';
			XHR.sendAndLoad('$page', 'GET',x_JoinArticaMeta);	
			}
			
		function UnjoinArticaMeta(){
			var DisableArticaMetaAgentInformations='$DisableArticaMetaAgentInformations';	
			if(DisableArticaMetaAgentInformations=='1'){alert('$ERROR_NO_PRIVS');return;}	
			var XHR = new XHRConnection();
			XHR.appendData('unjoin','yes');
			document.getElementById('artica-join-div').innerHTML='<img src=img/wait_verybig.gif>';
			XHR.sendAndLoad('$page', 'GET',x_JoinArticaMeta);		
			}
			
			LockJoin();
	</script>	
	
	
	";
echo $tpl->_ENGINE_parse_body($html);	
}

function popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	
	$array["login"]='{login}';
	$array["register"]='{register}';
	$array["params"]='{parameters}';
	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_articameta style='width:100%;height:400px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_articameta').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	
	
}


function inscription(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$DisableArticaMetaAgentInformations=$sock->GET_INFO("DisableArticaMetaAgentInformations");
	$array=unserialize(base64_decode($sock->GET_INFO("ArticaMetaRegisterDatas")));
	$button=button("{submit}","MetaInscription()");
	$lock=0;
	$companyname=$tpl->_ENGINE_parse_body("{company_name}");
	$email=$tpl->_ENGINE_parse_body("{email}");
	$password=$tpl->_ENGINE_parse_body("{password}");

	if($array["ERROR"]){$error="<span style='font-size:16px;color:#B61313'>{$array["ERROR_TEXT"]}</span>";}
	
	if($array["REGISTERED"]){$lock=1;}
	if($array["LOCK"]==1){$lock=1;}
	
	$html="
	<div id='artica-meta-form-inscr'>
	<div class=explain>{artica-meta-create-account-text}</div>
	<div style='font-size:16px;font-weight:bold'>{create_an_account}</div>
	<table style='width:100%'>
	<tr>
		<td class=legend>{company}:</td>
		<td>". Field_text("company",$array["company"],"font-size:13px;padding:3px","script:CheckMetaInscription(event)")."</td>
	</tr>
		
	<tr>
		<td class=legend>{givenname}:</td>
		<td>". Field_text("first_name",$array["first_name"],"font-size:13px;padding:3px","script:CheckMetaInscription(event)")."</td>
	</tr>
	<tr>
		<td class=legend>{sn}:</td>
		<td>". Field_text("last_name",$array["last_name"],"font-size:13px;padding:3px","script:CheckMetaInscription(event)")."</td>
	</tr>
	";
	if($DisableArticaMetaAgentInformations<>1){
	$html=$html."<tr>
		<td class=legend>{email}:</td>
		<td>". Field_text("artica-meta-email",$array["email"],"font-size:13px;padding:3px","")."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("artica-meta-password",$array["password"],"font-size:13px;padding:3px","script:CheckMetaInscription(event)")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>$button</td>
		
	</tr>";
	
	}
	$html=$html."<tr>
	</table>
	<center>$error</center>
	</div>
	
	<script>
	var x_MetaInscription= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_articameta');
		
	}
	
	function  CheckMetaInscription(e){
		var lock='$lock';
		if(lock=='1'){return;}
		if(checkEnter(e)){MetaInscription();}
	}
	
	
		function MetaInscription(){
			var DisableArticaMetaAgentInformations='$DisableArticaMetaAgentInformations';
			var lock='$lock';
			if(lock=='1'){return;}
		
			var company=document.getElementById('company').value;
			var password=document.getElementById('artica-meta-password').value;
			var email=document.getElementById('artica-meta-email').value;
			
			if(DisableArticaMetaAgentInformations=='1'){return;}
			if(company.length==0){alert('Error: $companyname');return;}
			if(email.length==0){alert('Error: $email');return;}	
			if(password.length==0){alert('Error: $password');return;}			
	
			
			var XHR = new XHRConnection();
			XHR.appendData('register-perform','yes');
			XHR.appendData('company',document.getElementById('company').value);
			XHR.appendData('first_name',document.getElementById('first_name').value);
			XHR.appendData('last_name',document.getElementById('last_name').value);
			XHR.appendData('email',document.getElementById('artica-meta-email').value);
			XHR.appendData('password',document.getElementById('artica-meta-password').value);
			document.getElementById('artica-meta-form-inscr').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_MetaInscription);	
			
			
		}
		
	function LockRegister(){
		var lock=$lock;
		if(lock=='1'){
			document.getElementById('first_name').disabled=true;
			document.getElementById('last_name').disabled=true;
			document.getElementById('company').disabled=true;
			document.getElementById('email').disabled=true;
			document.getElementById('password').disabled=true;
		}
	}
	LockInscription();
	</script>
			
		
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}

function register_perform(){
	$sock=new sockets();
	$_GET["LOCK"]=1;
	$datas=$sock->SaveConfigFile(base64_encode(serialize($_GET)),"ArticaMetaRegisterDatas");
	$sock->getFrameWork("cmd.php?artica-meta-register=yes");	
}

function join_perform(){
	$sock=new sockets();
	$sock->SET_INFO("ArticaMetaSerial",$_GET["serial"]);
	$array=unserialize(base64_decode($sock->GET_INFO("ArticaMetaRegisterDatas")));
	if(!is_array($array)){$array=array();}
	while (list ($num, $ligne) = each ($_GET) ){
		writelogs("$num=$ligne",__FUNCTION__,__FILE__,__LINE__);
		$array[$num]=$ligne;
	}
	
	$final=serialize($array);
	writelogs("$final",__FUNCTION__,__FILE__,__LINE__);
	$sock->SaveConfigFile(base64_encode($final),"ArticaMetaRegisterDatas");
	$sock->getFrameWork("cmd.php?artica-meta-join=yes");
}

function unjoin_perform(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?artica-meta-unjoin=yes");
}
function ArticaMetaRemoveIndexSave(){
	$sock=new sockets();
	$sock->SET_INFO("ArticaMetaRemoveIndex",$_GET["ArticaMetaRemoveIndex"]);
}

function params(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$meta=new artica_meta();
	$ArticaMetaHostname=$meta->ArticaMetaHostname;
	
	$html="
	<div id='artica-meta-settings'>
	<table style='width:100%'>
	<tr>
	<td class=legend>{meta_console_address}:</td>
	<td>". Field_text("ArticaMetaHostname",$ArticaMetaHostname,"font-size:13px;width:220px;padding:3px")."</td>
	</tr>
	<td colspan=2 align='right'>". button("{apply}","SaveArticaMetaSettings()")."</td>
	</tr>
	</table>
	</div>
<script>
	var x_SaveArticaMetaSettings= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_articameta');
	}	
	
	
		function SaveArticaMetaSettings(){	
			var XHR = new XHRConnection();
			XHR.appendData('ArticaMetaHostname',document.getElementById('ArticaMetaHostname').value);
			document.getElementById('artica-meta-settings').innerHTML='<img src=img/wait_verybig.gif>';
			XHR.sendAndLoad('$page', 'GET',x_SaveArticaMetaSettings);	
			}
</script>				
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function params_save(){
	$sock=new sockets();
	$sock->SET_INFO("ArticaMetaHostname",$_GET["ArticaMetaHostname"]);
	
}

	

?>