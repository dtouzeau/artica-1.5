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
	$title=$tpl->_ENGINE_parse_body('{postmaster_identity}');
	$page=CurrentPageName();
	$prefix="postmaster";
$html="

function POSTFIX_POSTMASTER_IDENT(){
	YahooWin('530','$page?popup=yes','$title');
}
POSTFIX_POSTMASTER_IDENT();";
	
	echo $html;
}	

function popup(){
	
	$page=CurrentPageName();
	$sock=new sockets();
	$PostfixPostmasterSender=$sock->GET_INFO("PostfixPostmasterSender");
	
	$html="
	<div id='postmasteridentdiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/postmaster-identity-90.png'></td>
		<td valign='top'>
			<div style='font-size:16px;padding:5px'>{postmaster_identity_text}
			<br>{postmaster_identity_explain}
			</div>
			<table style='width:100%'>
				<tr>
					<td class=legend style='font-size:13px' nowrap>{postmaster_identity}:</td>
					<td width=1%>". Field_text("postmaster_identity_email",$PostfixPostmasterSender,"font-size:13px;padding:3px;width:220px")."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'>
						<hr>". button("{apply}","SavePostMasterIdentForm()")."</td>
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
	
	function SavePostMasterIdentForm(){
		var XHR = new XHRConnection();
		XHR.appendData('postmaster',document.getElementById('postmaster_identity_email').value);
		document.getElementById('postmasteridentdiv').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SavePostMasterForm);
	}
		
	
	</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function save(){
	
	$email=$_GET["postmaster"];
	
	$sock=new sockets();
	$sock->SET_INFO("PostfixPostmasterSender",$email);
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");
	
	
	
}


?>