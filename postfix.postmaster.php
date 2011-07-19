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
	if(isset($_GET["postmaster"])){save();exit;}
	
js();	
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{postmaster}');
	$page=CurrentPageName();
	$prefix="postmaster";
$html="

function POSTFIX_POSTMASTER(){
	YahooWin('530','$page?popup=yes','$title');
}
POSTFIX_POSTMASTER();";
	
	echo $html;
}	

function popup(){
	
	$ldap=new clladp();
	$hash=$ldap->AllDomains();
	$page=CurrentPageName();
	$sock=new sockets();
	$PostfixPostmaster=$sock->GET_INFO("PostfixPostmaster");
	if(preg_match("#(.+?)@(.+)#",$PostfixPostmaster,$re)){
		
		$email=$re[1];$domain=$re[2];}
	
	
	$html="
	<div id='postmasterdiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/postmaster-90.png'></td>
		<td valign='top'>
			<div style='font-size:16px;padding:5px'>{postmaster_text}
			<br>{postmaster_explain}
			</div>
			<table style='width:100%'>
				<tr>
					<td class=legend style='font-size:13px'>{postmaster}:</td>
					<td width=1%>". Field_text("postmaster_email",$email,"font-size:13px;padding:3px;width:120px")."</td>
					<td width=1%><strong style='font-size:13px'>@</td>
					<td width=1%>". Field_array_Hash($hash,"postmaster_domain",$domain,null,null,0,"font-size:13px;padding:3px")."</td>
				</tr>
				<tr>
					<td colspan=4 align='right'>
						<hr>". button("{apply}","SavePostMasterForm()")."</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	var x_SavePostMasterForm= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		YahooWinHide();
	}	
	
	function SavePostMasterForm(){
		var XHR = new XHRConnection();
		XHR.appendData('postmaster',document.getElementById('postmaster_email').value+'@'+document.getElementById('postmaster_domain').value);
		document.getElementById('postmasterdiv').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SavePostMasterForm);
	}
		
	
	</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function save(){
	
	$email=$_GET["postmaster"];
	$ldap=new clladp();
	$users=new usersMenus();
	$mustcheck=false;
	if($users->cyrus_imapd_installed){$mustcheck=true;}
	if($users->ZARAFA_INSTALLED){$mustcheck=true;}
	
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
	$sock->SET_INFO("PostfixPostmaster",$email);
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
	$sock->getFrameWork("cmd.php?postmaster-cron=yes");
	
	
	
}


?>