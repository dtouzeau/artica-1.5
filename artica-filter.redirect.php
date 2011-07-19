<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ArticaFilterRedirectExternalSQL"])){save();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{REDIRECT_SERVICE_TEXT}");
	$html="YahooWin2(450,'$page?popup=yes','$title');";
	echo $html;	
	
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	
	$ArticaFilterEnableRedirect=$sock->GET_INFO("ArticaFilterEnableRedirect");
	$ArticaFilterRedirectExternalSQL=$sock->GET_INFO("ArticaFilterRedirectExternalSQL");
	
	if($ArticaFilterEnableRedirect==null){$ArticaFilterEnableRedirect=0;}
	if($ArticaFilterRedirectExternalSQL==null){$ArticaFilterRedirectExternalSQL=0;}
	$array=unserialize(base64_decode($sock->GET_INFO("ArticaFilterRedirectExternalSQLDatas")));

	$enable=Paragraphe_switch_img("{enable_redirection_service}","{enable_redirection_service_text}","ArticaFilterEnableRedirect",$ArticaFilterEnableRedirect,null,330);
	
	$html="
	$enable
	<hr>
	
	<div id='ArticaFilterEnableRedirectDiv'>
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{use_external_database}:</td>
		<td>". Field_checkbox("ArticaFilterRedirectExternalSQL",1,$array["ArticaFilterRedirectExternalSQL"],"ArticaFilterRedirectExternalSQLSwitch()")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap style='font-size:13px'>{mysqlserver}:</td>
		<td>". Field_text("mysql_servername",$array["mysql_servername"],"font-size:120px;font-size:13px;padding:3px")."</td>
	</tr>		
	<tr>
		<td class=legend nowrap style='font-size:13px'>{database}:</td>
		<td>". Field_text("mysql_database",$array["mysql_database"],"font-size:90px;font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend nowrap style='font-size:13px'>{mysql_username}:</td>
		<td>". Field_text("mysql_username",$array["mysql_username"],"font-size:90px;font-size:13px;padding:3px")."</td>
	</tr>					
	<tr>
		<td class=legend nowrap style='font-size:13px'>{password}:</td>
		<td>". Field_password("password",$array["password"],"font-size:30px;font-size:13px;padding:3px")."</td>
	</tr>	

	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","ArticaFilterRedirectSave()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
	var x_ArticaFilterRedirectSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		Loadjs('$page');
	}
	
	
	function ArticaFilterRedirectSave(){
		var XHR = new XHRConnection();
		XHR.appendData('ArticaFilterEnableRedirect',document.getElementById('ArticaFilterEnableRedirect').value);
		if(document.getElementById('ArticaFilterRedirectExternalSQL').checked){
			XHR.appendData('ArticaFilterRedirectExternalSQL',1);
		}else{XHR.appendData('ArticaFilterRedirectExternalSQL',0);}
		XHR.appendData('mysql_servername',document.getElementById('mysql_servername').value);
		XHR.appendData('mysql_database',document.getElementById('mysql_database').value);
		XHR.appendData('mysql_username',document.getElementById('mysql_username').value);
		XHR.appendData('password',document.getElementById('password').value);
		document.getElementById('ArticaFilterEnableRedirectDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_ArticaFilterRedirectSave);
	
	}	

	
	function ArticaFilterRedirectExternalSQLSwitch(){
		document.getElementById('mysql_servername').disabled=true;
		document.getElementById('mysql_database').disabled=true;
		document.getElementById('mysql_username').disabled=true;
		document.getElementById('password').disabled=true;
		if(document.getElementById('ArticaFilterRedirectExternalSQL').checked){
			document.getElementById('mysql_servername').disabled=false;
			document.getElementById('mysql_database').disabled=false;
			document.getElementById('mysql_username').disabled=false;
			document.getElementById('password').disabled=false;		
		}
	}
	ArticaFilterRedirectExternalSQLSwitch();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	}	
	
	
function save(){
	$sock=new sockets();
	$sock->SET_INFO("ArticaFilterEnableRedirect",$_GET["ArticaFilterEnableRedirect"]);
	$sock->SET_INFO("ArticaFilterRedirectExternalSQL",$_GET["ArticaFilterRedirectExternalSQL"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"ArticaFilterRedirectExternalSQLDatas");
	}	
	
	
	
?>