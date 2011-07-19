<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.cyrus.inc');
	
	//if(count($_POST)>0)
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
			$tpl=new templates();
			$error="{ERROR_NO_PRIVS}";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["Confirm"])){Confirm();exit;}
	
//YahooUser_c	
js();
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{delete_this_user_dn}',"domains.edit.user.php");
	$networks=$tpl->_ENGINE_parse_body('{edit_networks}');
	$delete_all_computers_warn=$tpl->_ENGINE_parse_body('{delete_all_computers_warn}');
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$html="
	var rule_mem='';
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	
	function {$prefix}start(){
		YahooLogWatcher(550,'$page?popup=yes&uid={$_GET["uid"]}&dn={$_GET["dn"]}','$title::{$_GET["uid"]}');
		
	
	}
	


	var x_ConfirmDeletionOfUser= function (obj) {
		var results=obj.responseText;
		if(results.length>0){
			alert(results);
			return;
			}
		YahooLogWatcherHide();
		YahooUserHide();
		
		if(YahooSearchUserOpen()){
			FindUser();
		}
		
	}
	
	function ConfirmDeletionOfUser(){
		var XHR = new XHRConnection();
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.appendData('Confirm','yes');
		XHR.appendData('dn','{$_GET["dn"]}');
		document.getElementById('deletion').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_ConfirmDeletionOfUser);
	}
	
//	function BrowseComputerCheckRefresh(e){
//		if(checkEnter(e)){BrowsComputersRefresh();}
//	}
	
{$prefix}start();
	";
	
	
	echo $html;
}


function popup(){
	$uid=$_GET["uid"];
	$user=new user($uid);
	$usersmenus=new usersMenus();
	$dn=base64_decode($_GET["dn"]);
	$picture="<img src='$user->img_identity' style='border:1px dotted #CCCCCC'>";
$user_infos="
<div class=explain>{delete_this_user_dn_text}</div>
<table style='width:100%'>
<tr>
	<td valign='top' width=1%>$picture</td>
	<td valign='top' width=99%>
		<table style='width:100%'>
			<tr>
				<td style='border-bottom:1px solid #CCCCCC'><strong style='font-size:16px;'>$user->uid</strong></td>
			</tr>
			<tr>
				<td align='right'><strong style='font-size:11px' >$dn</strong></td>
			</tr>
			<tr>
				<td><strong style='font-size:12px'>$user->mail</strong></td>
			</tr>
			<tr>
				<td align='right'>
					<hr>
					<input type='button' OnClick=\"javascript:ConfirmDeletionOfUser();\" value='{confirm_deletion_of}:$user->uid&nbsp;&raquo;' style='padding:10px'>
				</td>
			</tr>			
		</table>
	</td>
</tr>
</table>";


$html="
$dn
<div id='deletion'>
$user_infos
</div>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"domains.edit.user.php");
}

function Confirm(){
	$ldap=new clladp();
	$dn=base64_decode($_GET["dn"]);
	if($dn==null){echo "DN IS NULL";return;}
	if(!$ldap->ldap_delete(base64_decode($_GET["dn"]))){
		echo $ldap->ldap_last_error;
	}
	
}

	
	
?>