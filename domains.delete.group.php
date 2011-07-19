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
	$title=$tpl->_ENGINE_parse_body('{delete_group}',"domains.edit.user.php");
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	
	$gp=new groups($_GET["gpid"]);
	
	$html="
	var rule_mem='';
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	
	function {$prefix}start(){
		YahooLogWatcher(550,'$page?popup=yes&gpid={$_GET["gpid"]}','$title::$gp->groupName');
	}
	


	var x_ConfirmDeletionOfGroup= function (obj) {
		var results=obj.responseText;
		YahooLogWatcherHide();
		YahooUserHide();
		YahooWinSHide();
		if(YahooSearchUserOpen()){FindUser();}
		Loadjs('domains.edit.group.php?ou=$gp->ou&js=yes');
		
	}
	
	function ConfirmDeletionOfGroup(){
		var XHR = new XHRConnection();
		XHR.appendData('gpid','{$_GET["gpid"]}');
		XHR.appendData('Confirm','yes');
		if(document.getElementById('DeleteMailBox')){
			XHR.appendData('delete-mailbox',document.getElementById('DeleteMailBox').value);
		}
		
		XHR.appendData('DeleteUsers',document.getElementById('DeleteUsers').value);
		
		document.getElementById('deletion').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_ConfirmDeletionOfGroup);
	}
	
//	function BrowseComputerCheckRefresh(e){
//		if(checkEnter(e)){BrowsComputersRefresh();}
//	}
	
{$prefix}start();
	";
	
	
	echo $html;
}


function popup(){
	
	$group=new groups($_GET["gpid"]);
	
	$usersmenus=new usersMenus();
	
	$delete_mailbox="<tr>
				<td>
					<table style='width:100%'>
						<tr>
						<td class=legend nowrap>{delete_mailbox}:</td>
						<td>". Field_numeric_checkbox_img('DeleteMailBox',0,"{delete_mailbox}")."</td>
						</tr>
					</table>
				</td>
			</tr>";
	
	if(!$usersmenus->cyrus_imapd_installed){
		$delete_mailbox=null;
	}

$user_infos="<table style='width:100%'>
<tr>
	<td valign='top' width=99%>
		<table style='width:100%'>
			<tr>
				<td style='border-bottom:1px solid #CCCCCC'><strong style='font-size:16px;'>$group->groupName&nbsp;|&nbsp;ID:$group->group_id&nbsp;|&nbsp;". count($group->members)." {members}</strong></td>
			</tr>
			$delete_mailbox
			<tr>
			<tr>
				<td>
					<table style='width:100%'>
						<tr>
						<td class=legend nowrap>{delete_users}:</td>
						<td>". Field_numeric_checkbox_img('DeleteUsers',0,"{delete_users}")."</td>
						</tr>
					</table>
				</td>
			</tr>			
				<td align='right'>
					<hr>
					<input type='button' OnClick=\"javascript:ConfirmDeletionOfGroup();\" value='{confirm_deletion_of}:$group->groupName&nbsp;&raquo;' style='padding:10px;font-size:16px'>
				</td>
			</tr>			
		</table>
	</td>
</tr>
</table>";


$html="
<div class=explain>{warning_delete_all_users}</div>
<div id='deletion'>
$user_infos
</div>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"domains.edit.group.php");
}

function Confirm(){
	
	$group=new groups($_GET["gpid"]);
	
	if($_GET["delete-mailbox"]==1){
		if(is_array($gp->members_array)){
			$members_array=$gp->members_array;
			while (list ($num, $ligne) = each ($members_array) ){
				if(trim($num)==null){continue;}
				$sock=new sockets();
				$sock->getFrameWork("cmd.php?DelMbx=$num");	
				}
		}
	
	}
	
	if($_GET["DeleteUsers"]==1){
		if(is_array($gp->members_array)){
				$members_array=$gp->members_array;
				while (list ($num, $ligne) = each ($members_array) ){
					if(trim($num)==null){continue;}
					$user=new user($num);
					$user->DeleteUser();
					}
			}	
	}else{
		$ldap=new clladp();
		$default_dn_nogroup="cn=nogroup,ou=groups,ou=$ou,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($default_dn_nogroup)){$ldap->AddGroup("nogroup",$group->ou);}
		$nogroup_id=$ldap->GroupIDFromName($group->ou,"nogroup");	
		if(is_array($gp->members_array)){
			$members_array=$gp->members_array;
			while (list ($num, $val) = each ($members_array) ){
				$ldap->AddUserToGroup($nogroup_id,$num);
				$group->DeleteUserFromThisGroup($num);
			}
		}		
		
		
	}
	
	
	$group->Delete();
	
	
	
	
	
	
	
}

	
	
?>