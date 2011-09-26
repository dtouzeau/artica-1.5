<?php
$GLOBALS["ICON_FAMILY"]="user";
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.contacts.inc');
	
if(isset($_GET["SearchUserNull"])){echo SearchUserNull();exit;}
if(isset($_GET["userid"])){echo Page($_GET["userid"]);exit;}
if(isset($_GET["LoadUsersDatas"])){	echo Page($_GET["LoadUsersDatas"]);exit;}
if(isset($_GET["TreeUserMailBoxEdit"])){UserMailBoxEdit();exit;}
if(isset($_GET["Cyrus_mailbox_apply_settings"])){Cyrus_mailbox_apply_settings();exit;}
if(isset($_GET["TreeUserAddAliases"])){TreeUserAddAliases();exit;}
if(isset($_GET["TreeUserDeleteAliases"])){TreeUserDeleteAliases();exit;}
if(isset($_GET["DeleteUserGroup"])){DeleteUserGroup();exit;}
if(isset($_GET["AddMemberGroup"])){AddMemberGroup();exit;}
if(isset($_GET["SaveLdapUser"])){SaveLdapUser();exit;}
if(isset($_GET["AddnewMember"])){AddnewMember();exit();}
if(isset($_GET["DeleteMember"])){DeleteMember();exit;}
if(isset($_GET["finduser"])){finduser();exit;}
if(isset($_GET["UserAddressSubmitedForm"])){AddressInfosSave();exit;}
	
	
function Page($userid){
	$usermenu=new usersMenus();
	if(!isset($_GET["LoadUsersTab"])){$_GET["LoadUsersTab"]=0;}

	switch ($_GET["LoadUsersTab"]) {
		case 0:$body=Main_page_user($userid);break;
		case 1:$body=PageUserMailBoxForm($userid);break;
		case 2:$body=UserAliases($userid);break;
		case 3:$body=move_user($userid);break;
		case 4:$body=AddressInfos($userid);break;
		default:$body=Main_page_user($userid);break;
	}
	
	$ldap=new clladp();
	$userdatas=$ldap->UserDatas($userid);
	
$html="
<table style='width:100%'>
<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td valign='top'> " . Paragraphe('folder-user-64.jpg','{account}','{manage_account_text}',"javascript:LoadUsersTab(\"$userid\",\"0\")") ."</td>
	</tr>
	<tr>
		<td valign='top'> " . Paragraphe('folder-usermailbox-64.jpg','{mailbox}','{manage_mailbox_text}',"javascript:LoadUsersTab(\"$userid\",\"1\")") ."</td>		
	</tr>
	<tr>
		<td valign='top'> " . Paragraphe('folder-useraliases-64.jpg','{aliases}','{manage_aliases_text}',"javascript:LoadUsersTab(\"$userid\",\"2\")") ."</td>		
	</tr>";

	$html=$html . "<tr>
			<td valign='top'> " . Paragraphe('folder-address-64.jpg','{address}','{address_text}',"javascript:LoadUsersTab(\"$userid\",\"4\")") ."</td>		
		</tr>";

if($usermenu->AllowAddGroup==true){	
	$html=$html . "<tr>
		<td valign='top'> " . Paragraphe('folder-usermove-64.jpg','{move_member}','{move_member_text}',"javascript:LoadUsersTab(\"$userid\",\"3\")") ."</td>		
	</tr>	" ;}



	
	
	
if($usermenu->AllowAddUsers==true){
	$useradd= "
	
	<tr>
		<td valign='top' style='padding-top:10px;'> " . Paragraphe('folder-useradd-64.jpg','{add_new_member}','{add_new_member_text}',"javascript:AddnewMember()")."</td>		
	</tr>" ;}

$html=$html . "</table>
		</td>
		<td valign='top'>
			<table style='width:100%'>
				<tr>
					<td>$body</td>
				</tr>
				$useradd
			</table>
		</tr>
</table>

";
	
$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function move_user($userid){
	
	
	$ldap=new clladp();
	$usersData=$ldap->UserDatas($userid);
	$ou=$usersData["ou"];
	if(is_array($usersData["groups"])){
		$gp="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($usersData["groups"]) ){
			$gp=$gp . "
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td valign='top'>$num</td>
			<td valign='top'>" . imgtootltip('x.gif','{delete} '.$num,"DeleteUserGroup($ligne,'$userid')")."</td>
			</tr>";
		
		
		}
		
		$gp=$gp . "</table>";
	}
	$hash_group=$ldap->hash_groups($ou,1);
	$add=Field_array_Hash($hash_group,'group_add',null,null,null,0,'width:150px');

	$html="<fieldset style='width:400px'>
	<legend>{move_member}</legend>
	<form name='ffm1'>
	<input type='hidden' name='userid' id='userid' value='$userid'>
	<strong>{add_member_to_group}:&nbsp;</strong>$add&nbsp;<input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddMemberGroup();\" style='margin-bottom:0px'><br>
	</form>
	<h4>{member_of_group}:</H4>
	$gp
	
	
	</fieldset>";
$tpl=new Templates();
return  $tpl->_ENGINE_parse_body($html);	
	
}


function Main_page_user($userid){
			$ldap=new clladp();
			$userarr=$ldap->UserDatas($userid);
			$hash=$ldap->ReadDNInfos($userarr["dn"]);
			$hash["ou"]=$userarr["ou"];
			$eMailT=explode('@',$array['mail']);
			$domain=$eMailT[1];
			$email=$eMailT[0];	
			$usermenus=new usersMenus();
			$page=CurrentPageName();
			
	if($nobox==0){
		$leftimg="<img src='img/folder-user.gif'>";
	}
	//MailboxActive	
	
	$domains=$ldap->hash_get_domains_ou($hash["ou"]);
	if(is_array($domains)){
		while (list ($num, $ligne) = each ($domains) ){$fDomains[$ligne]=$ligne;}
		
		$domainName="<tr>
					<td align='right'><strong>{domainName}:</strong>
					<td>" . Field_array_Hash($fDomains,'domainName',$hash["domainName"],null,null,0,'width:120px') . "</td>
					</tr>"	;
	}
	
	if($usermenus->cyrus_imapd_installed==true){$button_mailboxes="<input type='button' value='{mailbox settings}&nbsp;&raquo;' OnClick=\"javascript:TreeUserMailBoxForm('$userid');\" style='margin-right:20px'>";}
	
	
	if($hash["displayname"]==null){$hash["displayname"]="unknown";}
	$html= "
	<fieldset>
	
		<legend>{$hash["displayname"]}</legend>
		<table>
			<tr>
			<td valign='top'>
				
				<form name='userLdapform'>
				<input type='hidden' name='SaveLdapUser' value='yes'>
				<input type='hidden' name='dn' value='{$hash["dn"]}'>
				<table>
					<tr>
					<td align='right'><strong>{ou}:</strong>
					<td>{$hash["ou"]}</td>
					</tr>					
					<tr>
					<td align='right'><strong>{firstname}:</strong>
					<td>" . Field_text('givenname',$hash["givenname"])."</td>
					</tr>	
					<tr>
					<td align='right'><strong>{lastname}:</strong>
					<td>" . Field_text('sn',$hash["sn"],'width:98%',null,'user_autofill();')."</td>
					</tr>			
					
					<tr>
					<td align='right'><strong>{DisplayName}:</strong>
					<td>" . Field_text('displayname',$hash["displayname"])."</td>
					</tr>	
			
					<tr>
					<td align='right'><strong>{email}:</strong>
					<td>" . Field_text('mail',$hash["mail"])."</td>
					</tr>
					<tr>
					<td align='right'><strong>{sender_canonical}:</strong>
					<td>" . Field_text('SenderCanonical',$hash["sendercanonical"])."</td>
					</tr>					
					$domainName
					<tr>
					<td align='right'><strong>{userid}:</strong>
					<td>" . Field_text('uid',$hash["uid"])."</td>
					</tr>	
					
					<tr>
					<td align='right'><strong>{password}:</strong>
					<td>" . Field_password("userpassword",$hash["userpassword"])."</td>
					</tr>	
					<tr>
					<td align='right'><strong>{MailboxActive}:</strong>
					<td>" . Field_TRUEFALSE_checkbox_img('MailboxActive',$hash["mailboxactive"]) ."</td>
					</tr>							
			
					<tr>
					
					<td align='right'  colspan=2 ><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform','$page',true);\"></td>
					</tr>				
				</table>
				</form>	
			</td>
		</tr>
		</table>
	</fieldset>";

	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function AddressInfosSave(){
	$userid=$_GET["UserAddressSubmitedForm"];
	unset($_GET["UserAddressSubmitedForm"]);
	$user=new LdapUserInfos($userid);
	while (list ($num, $ligne) = each ($_GET) ){
		$user->$num=$ligne;
		}
	$tpl=new templates();
	if($user->Save()==false){echo $user->error;}else{echo $tpl->_ENGINE_parse_body('{success}');}
	
	
}

function AddressInfos($userid){
	
	$page=CurrentPageName();
	$user=new LdapUserInfos($userid);
	
	$html="<H4><legend>$userid:{address}</H4>
	". RoundedLightGrey("
	<form name='userLdapform'>
	<input type='hidden' name='UserAddressSubmitedForm' value='$userid'>
		<table style='width:400px'>
			<tr>
				<td align='right'><strong>dn:</strong>
				<td class='caption'>$user->dn</td>
			</tr>
			<tr>
				<td align='right'><strong>{phone}:</strong>
				<td>" . Field_text('telephoneNumber',$user->telephoneNumber,'width:150px') ."</td>
			</tr>
			<tr>
				<td align='right'><strong>{mobile}:</strong>
				<td>" . Field_text('mobile',$user->mobile,'width:150px') ."</td>
			</tr>
			<tr><td colspan=2>&nbsp;</td></tr>
			<tr>
				<td align='right'><strong>{postalAddress}:</strong>
				<td>" . Field_text('postalAddress',$user->postalAddress,'width:100%') ."</td>
			</tr>
			<tr>
				<td align='right'><strong>{CP} & {town}</strong>
				<td>" . Field_text('CP',$user->CP,'width:50px') ."&nbsp;" . Field_text('town',$user->town,'width:180px') ."</td>
			</tr>	
			<tr>
				<td align='right'><strong>{BP}:</strong>
				<td>" . Field_text('BP',$user->BP,'width:100px') ."</td>
			</tr>	
			<tr><td colspan=2 style='padding-rigth:10px' align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform','" . basename(__FILE__)."',true);\"></td></tr>		
					
		</table>
		</form>
		
	
	");
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}


      function PageUserMailBoxForm($uid){
      	include_once(dirname(__FILE__). "/ressources/class.cyrus.inc");
      	
      	$ldap=new clladp();
      	$hash=$ldap->UserDatas($uid);
      
      	if($hash["MailboxActive"]=='TRUE'){
      		$cyrus=new cyrus();
      		$res=$cyrus->get_quota_array($uid);
		if($cyrus->connection_off==true OR $cyrus->error==true){return $tpl->_parse_body('{cyrus_connection_error}');}
		$free=$cyrus->USER_STORAGE_LIMIT -$cyrus->USER_STORAGE_USAGE;
		$mailboxInfos="<tr><td>&nbsp;</td><td style='font-size:9px' align='left'><i>$cyrus->USER_STORAGE_LIMIT bytes/$cyrus->USER_STORAGE_USAGE bytes ($free bytes {free})</i></td></tr>";
      	}
      	
      	
      	if($ldap->ldap_last_error<>null){return  nl2br($ldap->ldap_last_error);}
      	$html="<fieldset><legend>{$hash["displayName"]}</legend>
      	<form name='FFUserMailBox'>
      	<input type='hidden' name='TreeUserMailBoxEdit' value='$uid'>
      	<table style='width:100%'>
      	
      	<tr>
	      	
      		<td  align='right' width=1%>" . Field_TRUEFALSE_checkbox_img('MailboxActive',$hash["MailboxActive"]) . "</td>
	      	<td><strong>{MailboxActive}</strong>
	      	
      	</tr>
      	<tr>
	      	<td  align='right' nowrap><strong>{mailbox account}:</strong></td>
	      	<td>$uid</td>
      	</tr>      	
      	<tr>
	      	<td  align='right' nowrap><strong>{mailbox quota}:</strong></td>
	      	<td>" . Field_text('MailBoxMaxSize',$hash["MailBoxMaxSize"],'width:30%')."&nbsp;MB</td>
      	</tr>
      		$mailboxInfos
      	<tr>
      		<td colspan=2 align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFUserMailBox','domains.php',true);\"></td>
      	</tr>
      	<tr>
      		<td colspan=2 align='right'><input type='button' value='&laquo;&nbsp;{cyrus_apply_config}&nbsp;&raquo;' OnClick=\"javascript:Cyrus_mailbox_apply_settings('$uid');\"></td></td>
      	</tr>      	
      	</table>
      	</form>
      	</fieldset>";
      	$tpl=new templates();
      	return DIV_SHADOW($tpl->_ENGINE_parse_body($html),'windows');
      	
      }

function UserMailBoxEdit(){
	$usr=new usersMenus();
	$tpl=new templates();
	include_once('ressources/class.cyrus.inc');
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	$ldap=new clladp();
	$hashuser=$ldap->UserDatas($_GET["TreeUserMailBoxEdit"]);
	

	
	$update_array["MailBoxMaxSize"][]=$_GET["MailBoxMaxSize"];
	$update_array2["MailboxActive"][]=strtoupper($_GET["MailboxActive"]);
	
	if(!is_array_key('MailBoxMaxSize',$hashuser)){$ldap->Ldap_add_mod($hashuser["dn"],$update_array);}
	if(!is_array_key('MailboxActive',$hashuser)){$ldap->Ldap_add_mod($hashuser["dn"],$update_array2);}
		
	
	$ldap->Ldap_modify($hashuser["dn"],$update_array);
	if($ldap->ldap_last_error<>null){echo "\nMailBoxMaxSize:\n$ldap->ldap_last_error";}
	$ldap->ldap_last_error=null;
	$ldap->Ldap_modify($hashuser["dn"],$update_array2);
	if($ldap->ldap_last_error<>null){echo "\nMailboxActive:\n$ldap->ldap_last_error";}
	$quota=$_GET["MailBoxMaxSize"]*1024;
	$cyrus=new cyrus();
	if($cyrus->SetQuotaDN($_GET["TreeUserMailBoxEdit"],$quota)==false){
		echo $cyrus->cyrus_last_error;
	}else{
	if($ldap->ldap_last_error==null){echo $tpl->_ENGINE_parse_body('{success}');}
	}
	
}      
function Cyrus_mailbox_apply_settings(){
	$usr=new usersMenus();
	$tpl=new Templates();
	$show=array();
	$uid=$_GET["Cyrus_mailbox_apply_settings"];
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	$cyrus=new cyrus();
	$ldap=new clladp();
	$show[]="{mailbox_creation_results}: $uid";
	$hash=$ldap->UserDatas($uid);
	if($hash["MailboxActive"]=="TRUE"){
			$show[]="{mailbox_enabled}";
			$createMailbox=true;
			if($cyrus->CreateMailbox($uid)==false){
				$createMailbox=false;
				$error="{failed}:{creating_mailbox}:$uid\n$cyrus->cyrus_last_error\n";
			}
			else{$error="{success}:{creating_mailbox}:$uid\n";}
			$show[]=$cyrus->cyrus_infos;
		}else{
			$show[]="{mailbox_not_enabled}";
		}

	while (list ($num, $ligne) = each ($show) ){
		$html=$html. "<div style='font-size:12px;font-weight:bold;'>$ligne</div>\n";
	}
	echo "<div style='width:100%;height:250px;overflow:auto'>".$tpl->_ENGINE_parse_body($html)."</div>";
	}
	
	
    function UserAliases($userid){
    	$ldap=new clladp();
	$hash=$ldap->UserDatas($userid);
    	$aliases=$hash["mailAlias"];
    	$html="<fieldset>
		<legend>{aliases}</legend>
		<table style='width:400px'>\n";
    	if(is_array($aliases)){
    		while (list ($num, $ligne) = each ($aliases) ){
    		$html=$html . "<tr>
    		<td width=1%><img src='img/mailbox_storage.gif'></td>
    		<td style='padding:3px;' width=91% nowrap align='left'><code>$ligne</code></td>
    		<td  style='padding:3px;' width=1%>" . imgtootltip('x.gif','{delete aliase}',"TreeUserDeleteAliases('$ligne','$userid')")."</td>
    		</tr>
    		";
    		}
    	}
    	$html=$html . "
    	<tr><td colspan=3 style='padding:5px'><strong>{add aliase}:&nbsp;" . Field_text('aliases',null,'width:45%') . "&nbsp;<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:TreeUserAddAliases('$userid');\" style='margin:0px'></td></tr>
    	</table>
    	</fieldset>";
    	return $html;
    }	
function TreeUserAddAliases(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_GET["TreeUserAddAliases"]);
	$updatearray["mailAlias"][]=$_GET["aliase"];
	$ldap->Ldap_add_mod($hash["dn"],$updatearray);
	$_GET["LoadUsersTab"]=2;
	echo Page($_GET["TreeUserAddAliases"]);
	}    
function TreeUserDeleteAliases(){
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_GET["TreeUserDeleteAliases"]);	
	$updatearray["mailAlias"]=$_GET["aliase"];
	$ldap->Ldap_del_mod($hash["dn"],$updatearray);
	$_GET["LoadUsersTab"]=2;
	echo Page($_GET["TreeUserAddAliases"]);
}	
function DeleteUserGroup(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AllowAddGroup==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	$ldap=new clladp();
	$group_datas=$ldap->GroupDatas($_GET["DeleteUserGroup"]);
	unset($group_datas["members"][$_GET["user"]]);
	while (list ($num, $ligne) = each ($group_datas["members"]) ){
		$update_array["memberUid"][]=$num;
	}
	$ldap->Ldap_modify($group_datas["dn"],$update_array);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}
	$_GET["LoadUsersTab"]=3;
	echo Main_page_user($_GET["user"]);
	}
function AddMemberGroup(){
	$usr=new usersMenus();
	$tpl=new templates();
	$_GET["LoadUsersTab"]=3;
	if($usr->AllowAddGroup==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');echo Page($_GET["user"]);exit;}	
	$ldap=new clladp();
	$ldap->AddUserToGroup($_GET["AddMemberGroup"],$_GET["user"]);
	echo Page($_GET["user"]);	
}




function SaveLdapUser(){
	$ldap=new clladp();
	$dn=$_GET["dn"];
	unset($_GET["dn"]);
	unset($_GET["SaveLdapUser"]);
	$hash=$ldap->getobjectDNClass($dn,1);

	if(!isset($hash["ArticaSettings"])){
		$add_array["objectClass"][]="ArticaSettings";
		$ldap->Ldap_add_mod($dn,$add_array);
	}
	
	if(trim($_GET["SenderCanonical"])==null){
		$hash=$ldap->UserDatas($_GET["uid"]);
		if($hash['SenderCanonical']<>null){
			writelogs("delete SenderCanonical:{$_GET["uid"]}=>{$hash['SenderCanonical']}",__FUNCTION__,__FILE__);
			$upd["SenderCanonical"][0]=$hash['SenderCanonical'];
			$ldap->Ldap_del_mod($dn,$upd);
			
		}
		unset($_GET["SenderCanonical"]);
		}
	
	while (list ($num, $ligne) = each ($_GET) ){
		if($ligne=='true'){$ligne='TRUE';}
		if($ligne=='false'){$ligne='FALSE';}
		if($ligne<>null){
			$update_array[$num][]=$ligne;
		}
		
	}
	
	$ldap->Ldap_modify($dn,$update_array);
	if($ldap->ldap_last_error<>null){
		echo $ldap->ldap_last_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}
}
function AddnewMember(){
	$user=$_GET["AddnewMember"];
	$user=replace_accents($user);
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$uid=str_replace(' ','.',$user);

	if(stripos($user,'@')>0){
		$mail=$user;
		$tbl=explode('@',$user);
		$domainName=$tbl[1];
		$user=$tbl[0];
		$uid=str_replace(' ','.',$user);
		
		
		if(preg_match('#([a-z0-9]+)([\.\-_])([a-z0-9_\-\.]+)#',$user,$reg)){
			$firstname=$reg[1];
			$lastname=$reg[3];	
			
			}
		elseif (preg_match('#(.+)\s+(.+)#',$user,$reg)){
			$firstname=$reg[1];
			$lastname=$reg[2];
			
			}
		elseif (preg_match('#(.+)#',$user,$reg)){
			$lastname=$reg[1];
			$firstname=$lastname;
			
			}
	}
	
	else{
		if(preg_match('#([a-z0-9_\-]+)\s+([a-z0-9_\-]+)#',$user,$reg)){
			$lastname=$reg[2];
			$firstname=$reg[1];
			$domainName='none';
			}else{
				$lastname=$user;
				$firstname=$user;
				$domainName='none';
			}	
	}
	
	$dn="cn=$user,ou=users,ou=$ou,dc=organizations,$ldap->suffix";
	
	if($lastname==null){$lastname="unknown";}
	if($firstname==null){$firstname="unknown";}
	if($domainName==null){$domainName="unknown";}
	if($mail==null){$mail="$lastname.$firstname@$domainName";}
	
	$update_array["cn"][]=$user;
	$update_array["uid"][]=$uid;
	$update_array["sn"][]=$lastname;
	$update_array["domainName"][]=$domainName;
	$update_array["homeDirectory"][]="/home/$firstname.$lastname";
	$update_array["accountGroup"][]="0";
	$update_array["accountActive"][]='TRUE';
	$update_array["mailDir"]='cyrus';
	$update_array["objectClass"][]="userAccount";
	$update_array["objectClass"][]="top";	
	$update_array["objectClass"][]="organizationalPerson";

	
	
	$ldap->ldap_add($dn,$update_array);
	if($ldap->ldap_last_error<>null){
		if($ldap->ldap_last_error<>null){echo "Error: Add new member attributes line ". __LINE__ . "\n******\n$ldap->ldap_last_error\n******\n";}	
		exit();
	}
	$ldap->ldap_last_error=null;
	$update_array=null;
	$update_array["givenName"][]=$firstname;
	$update_array["mail"][]=$mail;
	$update_array["DisplayName"][]="$firstname "  . $lastname;
	$update_array["MailBoxActive"][]="FALSE";
	$update_array["objectclass"][]="ArticaSettings";
	$ldap->Ldap_add_mod($dn,$update_array);
	if($ldap->ldap_last_error<>null){echo "Error line" . __LINE__ . "\nModify attributes\n$ldap->ldap_last_error\n";}
	
	$ldap->ldap_last_error=null;
	if(isset($_GET["group_member_id"])){
		$ldap->AddUserToGroup($_GET["group_member_id"],$user);
		}
	
	if($ldap->ldap_last_error==null){echo $uid;}
	
	}
function DeleteMember(){
	$usermenu=new usersMenus();
	$tpl=new templates();
	if($usermenu->AllowAddUsers==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$ldap=new clladp();
	$Userdatas=$ldap->UserDatas($_GET["DeleteMember"]);
	$dn=$Userdatas["dn"];
	$ldap->ldap_delete($dn,false);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}else{echo $tpl->_ENGINE_parse_body('{success}');exit;}	
	}
	
function SearchUserNull(){
	$html="
	<div id='SearchUserNull' style='width:100%;height:400px;overflow:auto'>
		<div style='width:100%'><center style='margin:20px;padding:20px'><img src='img/wait_verybig.gif'></center></div>
	</div>";
	
	echo $html;	
}
	
function finduser(){
	$keycached="{$_GET["finduser"]}";
	if(GET_CACHED(__FILE__,__FUNCTION__,$keycached)){return null;}
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$GLOBALS["OUTPUT_DEBUG"]=false;
	$stringtofind=trim($_GET["finduser"]);
	$users=new usersMenus();
	$sock=new sockets();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	
	
	if(preg_match("#debug:(.+)#",$stringtofind,$re)){
		$GLOBALS["OUTPUT_DEBUG"]=true;
		$stringtofind=trim($re[1]);
	}
	
	if($GLOBALS["OUTPUT_DEBUG"]){echo "Want to search $stringtofind<br>";}
	$tpl=new templates();
	$usermenu=new usersMenus();
	$ldap=new clladp();
	if($usermenu->AsAnAdministratorGeneric==true){
		if($GLOBALS["OUTPUT_DEBUG"]){echo "It is an administrator search in the entire tree<br>";}
		$hash_full=$ldap->UserSearch(null,$stringtofind);
		
	}else{
		$us=$ldap->UserDatas($_SESSION["uid"]);
		if($GLOBALS["OUTPUT_DEBUG"]){echo "It is an user search in the {$us["ou"]} tree<br>";}
		$hash_full=$ldap->UserSearch($us["ou"],$stringtofind);
	}
	
	$hash1=$hash_full[0];
	$hash2=$hash_full[1];
	if($GLOBALS["OUTPUT_DEBUG"]){echo "Search results ".count($hash1) ." users and ".count($hash2)." contacts<br>";}
	
	
	$hash=array();
	$count=0;

	if(is_array($hash1)){
	while (list ($num, $ligne) = each ($hash1) ){
		
		if($EnableManageUsersTroughActiveDirectory==0){	if(($ligne["uid"][0]==null) && ($ligne["employeenumber"][0]==null)){continue;}}
		if(strpos($ligne["dn"],"dc=pureftpd,dc=organizations")>0){continue;}
		$hash[$count]["displayname"][0]=trim($ligne["displayname"][0]);
		$hash[$count]["givenname"][0]=$ligne["givenname"][0];
		if($EnableManageUsersTroughActiveDirectory==1){
			$hash[$count]["uid"][0]=$ligne["samaccountname"][0];
		}else{
			$hash[$count]["uid"][0]=$ligne["uid"][0];
		}
		if(substr($hash[$count]["uid"][0],strlen($hash[$count]["uid"][0])-1,1)=='$'){continue;}
		
		$hash[$count]["employeenumber"][0]=$ligne["employeenumber"][0];
		$hash[$count]["title"][0]=$ligne["title"][0];
		$hash[$count]["uri"][0]=$ligne["uri"][0];
		$hash[$count]["mail"][0]=$ligne["mail"][0];
		$hash[$count]["phone"][0]=$ligne["telephonenumber"][0];
		$hash[$count]["sn"][0]=$ligne["sn"][0];
		$hash[$count]["dn"]=$ligne["dn"];
		$count++;
		
	}}
	
	
	
	if(is_array($hash2)){
	while (list ($num, $ligne) = each ($hash2) ){
	if(($ligne["uid"][0]==null) && ($ligne["employeenumber"][0]==null)){continue;}
		if(strpos($ligne["dn"],"dc=pureftpd,dc=organizations")>0){continue;}
		$hash[$count]["displayname"][0]=$ligne["displayname"][0];
		$hash[$count]["givenname"][0]=$ligne["givenname"][0];
		$hash[$count]["uid"][0]=$ligne["uid"][0];
		$hash[$count]["employeenumber"][0]=$ligne["employeenumber"][0];
		$hash[$count]["title"][0]=$ligne["title"][0];
		$hash[$count]["uri"][0]=$ligne["uri"][0];
		$hash[$count]["mail"][0]=$ligne["mail"][0];
		$hash[$count]["phone"][0]=$ligne["telephonenumber"][0];
		$hash[$count]["sn"][0]=$ligne["sn"][0];
		$hash[$count]["dn"]=$ligne["dn"];
		$count=$count+1;
		
	}}	
	
	
	$count=count($hash);
	writelogs("Search results $count items" ,__FUNCTION__,__FILE__);
	
	if(is_array($hash)){
		
		while (list ($num, $ligne) = each ($hash) ){
			if($GLOBALS["OUTPUT_DEBUG"]){echo "dn:{$ligne["dn"]}<br>";}
			if($GLOBALS["OUTPUT_DEBUG"]){echo "uid:{$ligne["uid"][0]}<br>";}
			if($GLOBALS["OUTPUT_DEBUG"]){echo "employeenumber:{$ligne["employeenumber"][0]}<br>";}
			if(($ligne["uid"][0]==null) && ($ligne["employeenumber"][0]==null)){
				if($GLOBALS["OUTPUT_DEBUG"]){echo "null twice, aborting...<br>";}
				continue;
			}
			
			if($ligne["uid"][0]=="squidinternalauth"){$count=$count-1;continue;}
			
			if($GLOBALS["OUTPUT_DEBUG"]){echo "edit_config_user={$ligne["uid"][0]}<br>";}
			
			$edit_config_user=MEMBER_JS($ligne["uid"][0],1,0,$ligne["dn"]);
			
			if($usermenu->AllowAddUsers==true){$uri=$edit_config_user;}else{$uri=null;}
			if($usermenu->AsOrgAdmin==true){$uri=$edit_config_user;}else{$uri=null;}
			if($usermenu->AsArticaAdministrator==true){$uri=$edit_config_user;}else{$uri=null;}
			
			
			
			$displayname=trim($ligne["displayname"][0]);
			$givenname=$ligne["givenname"][0];
			$mail=$ligne["mail"][0];
			
			if($displayname==null){$displayname=$ligne["uid"][0];}
			if($givenname==null){$givenname='{unknown}';}
			if($mail==null){$mail='{unknown}';}

			if($ligne["employeenumber"][0]<>null){
				$array["employeenumber"]=$ligne["employeenumber"][0];
				$user=new contacts($_SESSION["uid"],$ligne["employeenumber"][0]);
				$array["title"]=$user->displayName;
				$uri="javascript:Loadjs('contact.php?employeeNumber={$ligne["employeenumber"][0]}')";			
				
			}else{
				if($ligne["uid"][0]<>null){
					$array["title"]=$ligne["uid"][0];
					$user=new user($ligne["uid"][0]);
					
					
				}
			}
			
				if(strlen($user->jpegPhoto)>0){$array["img"]=$user->img_identity;}else{$array["img"]="img/contact-unknown-user.png";}
				writelogs("identity:$user->img_identity ",__FUNCTION__,__FILE__);
				$array["uri"]=$uri;
				$array["mail"]=$ligne["mail"][0];;
				$array["phone"]=$ligne["telephonenumber"][0];
				$array["sn"]=$ligne["sn"][0];
				if(!$ldap->EnableManageUsersTroughActiveDirectory){
					if($displayname==null){$displayname="$givenname {$ligne["sn"][0]}";}
				}
				$array["displayname"]=$displayname;								
				$array["givenname"]=$givenname;
				$array["JS"]=$edit_config_user;
				$array["title"]=$ligne["title"][0];;;
				$tr_users[]=finduser_format($array);
			}
		}
	
		
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr_users) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		
}
if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
	
		
	$add=Paragraphe("my-address-book-user-add.png",'{add_new_contact}','{add_new_contact_text}',"javascript:Loadjs('contact.php')");
	if($_SESSION["uid"]==-100){$add=null;}		
	$html="<p style='font-size:18px'>{search}:&laquo;$stringtofind&raquo; ($count {entries})</p>
	
	<center>
	<table style='width:100%'>
	<tr>
	<td valign='top'>". implode("\n",$tables). "
		
	</td>
	<td valign='top'>
	$add
	</td>
	</tr>
	</table>
	
	</center>";
	
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,$keycached,$html);
	echo $html;
	
}



      
?>