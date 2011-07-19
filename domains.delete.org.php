<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');

	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}')");exit;die();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["delete-ou"])){deleteou();exit;}
	
js();


function js(){
	$ou=$_GET["ou"];
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{delete_ou}');
	$prefix=str_replace('.','',$page);
$html="
	function {$prefix}LoadPage(){
		LoadWinORG(450,'$page?popup=yes&ou={$_GET["ou"]}','$title::{$_GET["ou"]}');
		}
		
		
var x_ConFirmDelete= function (obj) {
	var results=obj.responseText;
	WinORGHide();
	WinORG2Hide();
	alert(results);
	Loadjs('domains.index.php?js=yes');
	}	
	
	
	function ConFirmDelete(){
				var XHR = new XHRConnection();
				XHR.appendData('delete-ou','{$_GET["ou"]}');
				XHR.appendData('delete_mailboxes',document.getElementById('delete_mailboxes').value);
				document.getElementById('confirmdeleteou').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_ConFirmDelete);
				
			}	


{$prefix}LoadPage();";	
	
	
	echo $html;
}


function popup(){
	
	
	$users=new usersMenus();
	if($users->cyrus_imapd_installed){
		$cyrus="
			<tr>
				<td class=legend>{delete_mailboxes}</td>
				<td>". Field_numeric_checkbox_img('delete_mailboxes',0,'{delete_mailboxes}')."</td>
			</tr>
		
		";
		
	}else{
		$cyrus="<input type='hidden' id='delete_mailboxes' value='0'>";
	}
	
$html="
<div id='confirmdeleteou'>
<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/org-128.png'></td>
		<td valign='top'>
			<div class=explain>{delete_ou_text}</p>
			<table style='width:100%'>
				$cyrus
			</table>			
		</td>
		<tr>
		<td colspan=2 align='right'><hr></td></tr>
		<tr>
			<td>
			". button("{cancel}","Loadjs('domains.manage.org.index.php?js=yes&ou={$_GET["ou"]}')")."
			</td>
			
		<td align='right'>". button("{confirm}","ConFirmDelete()")."
		</td>
		</tr>
	</tr>
</table>
</div>
";
$tpl=new templates();

echo $tpl->_ENGINE_parse_body($html,'domain.manage.org.index.php');
	
}

function deleteou(){
	//     sys.THREAD_COMMAND_SET(sys.LOCATE_PHP5_BIN()+' /usr/share/artica-postfix/exec.delete-ou.php "'+RegExpr.Match[1]+'" ' +RegExpr.Match[2]);    
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?organization-delete=". base64_encode($_GET["delete-ou"])."&delete-mailboxes={$_GET["delete_mailboxes"]}");
	$sock->getFrameWork("cmd.php?ad-import-remove-schedule=yes&ou=". base64_encode($_GET["delete-ou"]));
	

	
	$tpl=new templates();
	sleep(3);
	echo $tpl->javascript_parse_text('{apply_upgrade_help}');
	REMOVE_CACHED("domains.index.php");
	
}

//org-128.png


	
	
	


?>