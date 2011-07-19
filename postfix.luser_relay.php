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
	if(isset($_GET["luser_relay"])){save();exit;}
	
js();	
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{postmaster}');
	$page=CurrentPageName();
	$prefix="postmaster";
$html="

function POSTFIX_luser_relay(){
	YahooWin('530','$page?popup=yes','$title');
}
POSTFIX_luser_relay();";
	
	echo $html;
}	

function popup(){
	
	$ldap=new clladp();
	$hash=$ldap->AllDomains();
	$page=CurrentPageName();
	$sock=new sockets();
	$luser_relay=$sock->GET_INFO("luser_relay");
	if(preg_match("#(.+?)@(.+)#",$luser_relay,$re)){
		
		$email=$re[1];$domain=$re[2];}
	
	
	$html="
	<div id='luser_relaydiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/unknown-user-90.png'></td>
		<td valign='top'>
			<div style='font-size:16px;padding:5px'>{postfix_unknown_users_tinytext}
			</div>
			<table style='width:100%'>
				<tr>
					<td class=legend style='font-size:13px'>{email}:</td>
					<td width=1%>". Field_text("luser_relay",$email,"font-size:13px;padding:3px;width:120px")."</td>
					<td width=1%><strong style='font-size:13px'>@</td>
					<td width=1%>". Field_array_Hash($hash,"luser_relay_domain",$domain,null,null,0,"font-size:13px;padding:3px")."</td>
				</tr>
				<tr>
					<td colspan=4 align='right'>
						<hr>". button("{apply}","Saveluser_relayForm()")."</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	var x_Saveluser_relayForm= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		YahooWinHide();
	}	
	
	function Saveluser_relayForm(){
		var XHR = new XHRConnection();
		XHR.appendData('luser_relay',document.getElementById('luser_relay').value+'@'+document.getElementById('luser_relay_domain').value);
		document.getElementById('luser_relaydiv').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_Saveluser_relayForm);
	}
		
	
	</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function save(){
	
	$email=$_GET["luser_relay"];
	$ldap=new clladp();
	$users=new usersMenus();
	$mustcheck=false;
	if($users->cyrus_imapd_installed){$mustcheck=true;}
	if($users->ZARAFA_INSTALLED){$mustcheck=true;}
	if(preg_match("#^@(.+)#",$email,$re)){$mustcheck=false;$email=null;}
	
	
	
	
	if($mustcheck){
		$ldap=new clladp();
		$uid=$ldap->uid_from_email($email);
		if($uid==null){
			$tpl=new templates();
			echo $tpl->javascript_parse_text("\n$email\n{mailbox_does_not_exists}");
			return;
		}
	}
	
	$sock=new sockets();
	$sock->SET_INFO("luser_relay",$email);
	$sock->getFrameWork("cmd.php?postfix-luser-relay=yes");
	
	
	
}


?>