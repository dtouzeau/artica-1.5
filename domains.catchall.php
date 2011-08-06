<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	
	


	
	if(!checkrights()){
		$tpl=new templates();
		echo "alert('".$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}')."');";
		die();
		}
		
	if(isset($_GET["index"])){catchall_index();exit;}
	if(isset($_GET["catch-all-addr"])){catchall_save();exit;}		
	if(isset($_GET["sugaradminpassword"])){sugar_save();exit;}
	js();
	
	
function checkrights(){
	$users=new usersMenus();
	if(!isset($_GET["ou"])){return false;}
	if(!isset($_GET["domain"])){return false;}
	if($_SESSION["uid"]<>-100){if($_SESSION["ou"]<>$_GET["ou"]){return false;}}
	if($users->AsMessagingOrg){return true;}
	if($users->AllowEditOuSecurity){return true;}
	if($users->AsAnAdministratorGeneric){return true;}
	return false;
	
	
}
	
function js(){
	
	
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("$ou:&nbsp;{catch_all}");
	$start="LoadCatchALLIndex()";
	if(isset($_GET["bytabs"])){
		$start="LoadCatchALLIndex2()";
		$prefix="<div id='catchall-tabs'></div><script>";
		$suffix="</script>";
	}
	
	$html="
	$prefix
	
	function LoadCatchALLIndex(){
		YahooWin2(500,'$page?index=yes&ou={$_GET["ou"]}&domain=$domain','$title');
	
	}
	
	function LoadCatchALLIndex2(){
		LoadAjax('catchall-tabs','$page?index=yes&ou={$_GET["ou"]}&domain=$domain');
	
	}	
	
	var x_SaveCatchAll= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		LoadCatchALLIndex();
	}	
	
	function SaveCatchAll(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','$ou');
		XHR.appendData('domain','$domain');
		XHR.appendData('catch-all-addr',document.getElementById('catch-all-addr').value);
		document.getElementById('catch-all-content').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_SaveCatchAll);
	}
	
	function SaveCatchAllEvent(e){
		if(checkEnter(e)){
				SaveCatchAll();
			}
	}
	
	$start;
	$suffix
	";
	
	
	echo $html;	
}

function catchall_index(){
	
	$ldap=new clladp();
	$dn="cn=@{$_GET["domain"]},cn={$_GET["ou"]},cn=catch-all,cn=artica,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$hash=$ldap->Ldap_read($dn,"(objectClass=*)",array());
		$addr=$hash[0]["catchallpostfixaddr"][0];
		if(preg_match('#(.+?)@(.+)#',$addr,$re)){
			$addr=$re[1];
		}
	}
	
	
	$form="
	<table style='width:100%'>
		<tr>
			<td class=legend nowrap>{email}:</td>
			<td>" . Field_text('catch-all-addr',$addr,null,null,null,null,false,"SaveCatchAllEvent(event)")."</td>
			<td><strong style='font-size:12px'>@{$_GET["domain"]}</strong></td>
		</tr>
		<tr>
			<td colspan=3 align='right'><hr><input type='button' OnClick=\"javascript:SaveCatchAll();\" value='{edit}&nbsp;&raquo;'></td>
		</tr>
	</table>
			
	";
	
	
	
$html="
	

<div class=explain>{catch_all_mail_text}</div>
	<table style='width:100%'>
	<tr>
		<td>
			<img src='img/128-catch-all.png'>
		</td>
		<td>
		<div id='catch-all-content'>$form</div>
		</td>
	</tr>
	</table>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function catchall_save(){
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$ldap=new clladp();
	
	if(trim($_GET["catch-all-addr"])==null){
		$dn="cn=@$domain,cn=$ou,cn=catch-all,cn=artica,$ldap->suffix";
		if($ldap->ExistsDN($dn)){
			$ldap->ldap_delete($dn);
			return null;
		}
	}
	
	$email=$_GET["catch-all-addr"]."@$domain";
	$tpl=new templates();
	
	$uid=$ldap->uid_from_email($email);
	
	if($uid==null){
		echo $tpl->_ENGINE_parse_body('{error_no_user_exists}');
		return null;
	}
	
	$ct=new user($uid);
	if($ct->ou<>$ou){
		echo $tpl->_ENGINE_parse_body('{error_no_user_exists}');
		return null;
	}
	
	$dn="cn=catch-all,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
			if(!$ldap->ExistsDN($dn)){
			$upd['cn'][0]="catch-all";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return null;}
			unset($upd);		
		}
		
	}
	
	$dn="cn=$ou,cn=catch-all,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
			if(!$ldap->ExistsDN($dn)){
			$upd['cn'][0]="$ou";
			$upd['objectClass'][0]='PostFixStructuralClass';
			$upd['objectClass'][1]='top';
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return null;}
			unset($upd);		
		}
		
	}

	$dn="cn=@$domain,cn=$ou,cn=catch-all,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
			if(!$ldap->ExistsDN($dn)){
			$upd['cn'][0]="@$domain";
			$upd['objectClass'][0]='AdditionalPostfixMaps';
			$upd['objectClass'][1]='top';
			$upd['CatchAllPostfixAddr'][0]="$email";						
			if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return null;}
			unset($upd);		
		}
		
	}	
	
$sock=new sockets();
$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");	
	
}

?>