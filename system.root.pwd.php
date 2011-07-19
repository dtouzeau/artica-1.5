<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	$user=new usersMenus();
		if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["change_password"])){change();exit;}
js();


function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->javascript_parse_text("{root_password_not_changed}");
	$html="YahooWin5(550,'$page?popup=yes','$title');";
	echo $html;
}


function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$root_error_pass=$tpl->javascript_parse_text("{root_error_pass}");
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><div id='animate-root'><img src='img/cop-lock-128.png' ></div></td>
		<td valign='top'><div class=explain>{root_password_not_changed_text}</div><p>&nbsp;</p>
		<table style='width:95%' class=form>
		<tr>
			<td class=legend style='font-size:16px'>{password}:</td>
			<td>". Field_password("root-pass1",null,"font-size:16px;padding:3px;width:120px",null,null,null,false,"CHRootPwdCheck(event)")."</td>
		</tr>
			<td>&nbsp;</td>
			<td>". Field_password("root-pass2",null,"font-size:16px;padding:3px;width:120px",null,null,null,false,"CHRootPwdCheck(event)")."</td>
		<tr>
		<tr>
			<td colspan=2 align='right'><hr>". button("{apply}","CHRootPwd()")."</td>
		</tr>
		</table>
		
		
		<script>
			function CHRootPwdCheck(e){
				if(checkEnter(e)){CHRootPwd();return;}
			}
			
		var X_CHRootPwd= function (obj) {
			var tempvalue=obj.responseText;
			document.getElementById('animate-root').innerHTML='<img src=img/cop-lock-128.png>';
			if(tempvalue.length>3){alert(tempvalue);return;}
			YahooWin5Hide();
			}			
		
		function CHRootPwd(){
			var pass=document.getElementById('root-pass1').value;
			if(pass.length<3){alert('$root_error_pass');return;}
			if(document.getElementById('root-pass1').value!==document.getElementById('root-pass2').value){alert('$root_error_pass');return;}
			var XHR = new XHRConnection();
			XHR.appendData('change_password',document.getElementById('root-pass1').value);
			AnimateDiv('animate-root');
			XHR.sendAndLoad('$page', 'POST',X_CHRootPwd);
		}
	</script>
		
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}


function change(){
	if(strlen(trim($_POST["change_password"]))>1){
			$sock=new sockets();
			$sock->SET_INFO("RootPasswordChanged", 1);
			$change_password=url_decode_special($_POST["change_password"]);
			include_once(dirname(__FILE__))."/ressources/class.samba.inc";
			$smb=new samba();
			$smb->createRootID($change_password);
	}		
}