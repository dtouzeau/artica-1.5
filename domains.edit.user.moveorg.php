<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.ini.inc');
		

			
	if(!CheckRights()){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["nextou"])){moveuser();exit;}
	

js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$uid=$_GET["userid"];
	$title=$tpl->_ENGINE_parse_body($uid.'::{change_organization}');	
	
	
	
$html="

function user_changeorg_load(){
	YahooWin4('500','$page?popup=yes&userid={$_GET["userid"]}','$title');
	}



		


user_changeorg_load();";	
	
	
echo $html;	
}

function popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$ldap=new clladp();
	$u=new user($_GET["userid"]);
	$oldorg=$u->ou;
	$ous=$ldap->hash_get_ou(true);
	unset($ous[$u->ou]);
	$ous[null]="{select}";
	$success=$tpl->javascript_parse_text("{success}");
	
	$field=Field_array_Hash($ous,"nextou",null,"style:font-size:16px;padding:3px");
	
	$html="
	<div class=explain id='useranimateddiv'>{change_user_organization_text}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{organization}:</td>
		<td>$field</td>
	</tr>
	<tr>
		<td colspan=2 align=right><hr>". button("{move}","MoveUserToOu()")."</td>
	</tr>
	</table>
	
	<script>
	function MoveUserToOu(){
		var XHR = new XHRConnection();
		AnimateDiv('useranimateddiv');
	    XHR.appendData('nextou',document.getElementById('nextou').value);
	    XHR.appendData('ou','$u->ou');
	    XHR.appendData('userid','{$_GET["userid"]}');
	    XHR.sendAndLoad('$page', 'POST',x_MoveUserToOu); 
	
	}

	
	var x_MoveUserToOu= function (obj) {
		var ou=document.getElementById('nextou').value;
		var results=trim(obj.responseText);
		if(results.length>2){alert('<'+results+'>');user_changeorg_load();return;}
		YahooUserHide();
		YahooWin4Hide();
		if(document.getElementById('org_main')){RefreshTab('org_main');}
		alert('$success {$_GET["userid"]} -> '+ou);
	}
</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function moveuser(){
	$u=new user($_POST["userid"]);
	$dn=$u->dn;
	$gplist=$u->Groups_list();
	
	if(preg_match("#^(.+?),#", $dn,$re)){$newRdn=$re[1];}else{$newRdn="cn={$_POST["userid"]}";}
	$ldap=new clladp();
	$newParent="ou=users,ou={$_POST["nextou"]},dc=organizations,$ldap->suffix";
	if(!ldap_rename($ldap->ldap_connection, $dn, $newRdn, $newParent, true)){
		echo 'Error number ' . ldap_errno($ldap->ldap_connection) . "\nAction:LDAP Ldap_rename\ndn:$dn -> $newRdn,$newParent\n" . ldap_err2str(ldap_errno($ldap->ldap_connection));
		return;
	}
	
	
	while ( list ( $gid, $name ) = each ( $gplist ) ) {
		$gp=new groups($gid);
		$gp->DeleteUserFromThisGroup($_POST["userid"]);
	}
	
}



function CheckRights(){
	if(!$_REQUEST["userid"]){return false;}
	$usersprivs=new usersMenus();
	if($usersprivs->AsSystemAdministrator){return true;}
	if($usersprivs->AsAnAdministratorGeneric){return true;}
	if($usersprivs->AllowAddGroup){return true;}
	if($usersprivs->AllowAddUsers){return true;}
	return false;
}