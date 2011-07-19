<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.html.pages.inc');
	
	if(isset($_GET["edit_mailbox"])){user_page($_GET["edit_mailbox"]);exit;}
	if(isset($_GET["SaveUser_datas"])){SaveUser_datas();exit;}
	if(isset($_GET["users_add_aliases"])){users_add_aliases();exit;}
	if(isset($_GET["users_delete_aliases"])){users_delete_aliases();exit;}
	if(isset($_GET["users_create_mailbox"])){users_create_mailbox();exit;}
	if(isset($_GET["user_set_quota"])){user_set_quota();exit;}
	if(isset($_GET["imap_unlock_session"])){imap_unlock_session();exit;}
	if(isset($_GET["EditLdapUser"])){EditLdapUser();exit;}
	if(isset($_GET["dn"])){SaveLdapUser();}
	if(isset($_GET["TreeUserAddAliases"])){TreeUserAddAliases();exit;}
	if(isset($_GET["TreeUserDeleteAliases"])){TreeUserDeleteAliases();exit;}
	
	
function EditLdapUser(){
	$ldap=new clladp();
	$hash=$ldap->ReadDNInfos($_GET["EditLdapUser"]);
	$pages=new HtmlPages();
	$tpl=new templates();
	echo DIV_SHADOW($pages->PageUser($hash),'windows');
	
	}
	



	

	
function user_page($dn=null){
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];

	if($_GET["tab"]==1){users_aliases($dn);exit;}
	if($_GET["tab"]==2){users_mailbox($dn);exit;}
	
	if($dn==null){$cn="{add_user}";}
		else {
			$ldap=new clladp();
			$hash=$ldap->GetDNValues($dn);
			$dn_path=$dn;
			$array=$hash[0];
			$eMailT=explode('@',$array['mail'][0]);
			$domain=$eMailT[1];
			$email=$eMailT[0];
			$firstname=$array["givenname"][0];
			$lastname=$array["sn"][0];
			$surname=$array["displayname"][0];
			$password=$array["userpassword"][0];
			$userid=$array["uid"][0];
			$cn="$firstname $lastname";
		}

$tabq=user_page_tabs($dn);

	$html= "
	<fieldset style='width:90%'>
		<legend>$cn</legend>
		$tabq
		<input type='hidden' id='dn_path' value='$dn_path'>
		<input type='hidden' id=ou value='$ou'>
		<input type='hidden' id=domain value='$domain'>
		<input type='hidden' id='error_bad_mail_formated' value=\"{error_bad_mail_formated}\">		
		<table>
		<tr>
		<td valign='top' width=1%>
		<img src='img/user-maibox.png'>
		</td>
		<td>
		<table>


		<tr class='rowB'>
		<td align='right'><strong>{firstname}:</strong>
		<td><input type='text' id='firstname' value='$firstname'></td>
		</tr>	
		<tr class='rowB'>
		<td align='right'><strong>{lastname}:</strong>
		<td><input type='text' id='lastname' value='$lastname' OnChange=\"javascript:user_autofill();\"></td>
		</tr>			
		
		<tr class='rowB'>
		<td align='right'><strong>{DisplayName}:</strong>
		<td><input type='text' id='surname' value='$surname'></td>
		</tr>	

		<tr class='rowB'>
		<td align='right'><strong>{email}:</strong>
		<td><input type='text' id='email' value='$email' style='width:30%;clear:both;text-align:right'><code>&nbsp;@$domain</code></td>
		</tr>	
		
		<tr class='rowA'>

		<td align='right'><strong>{userid}:</strong>
		<td><input type='text' id='userid' value='$userid'></td>
		</tr>	
		
		<tr class='rowA'>
		<td align='right'><strong>{password}:</strong>
		<td>" . Field_password('password',$password)."</td>
		</tr>			

		<tr class='rowB'>
		<td align='right' colspan=2 style='padding-right:10px'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:SaveUser();\"></td>
		</tr>				
		
			
		
		</table>	
		</td>
		</tr>
		</table>
		
	</fieldset>
	
	";
	
	
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
}


function users_mailbox($dn){
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$ldap=new clladp();
	$hash=$ldap->GetDNValues($dn);	
	$uid=$hash[0]["uid"][0];
	$firstname=$hash[0]["givenname"][0];
	$lastname=$hash[0]["sn"][0];		
	$cyrus=new cyrus();
	

	
	if( $_SESSION["LOCK_IMAP"]==false){
		if($cyrus->connection_off==false){
			if(!$cyrus->mailbox_uid($uid)){
					$body="<input type='button' value='{create_mailbox}' OnClick=\"javascript:users_create_mailbox('$dn','$uid');\">";
					
				}else{
					
					$view_quota=users_quota($uid);
					$body="<table style='width:70%'>
					<tr class='rowA'>
					<td>{mailbox_name}</td>
					<td><strong>user/$uid</strong></td>
					</tr>
					<tr class='rowB'>
					<td>{set_quota}</td>
					<td><input type='text' id='quota' value='' style='width:50%'>&nbsp;Kb&nbsp;<input type='button' value='{set_quota}&nbsp;&raquo;' OnClick=\"javascript:user_set_quota('$dn');\" style='margin-bottom:0px'></td>
					</tr>
					$view_quota		
					</table>";
					
				}
		}else{
			$body="<strong>{imap_connection_broken}</strong>";}
	}else{
		$body="<strong>{imap_connection_locked}</strong>
		<center>
		<div style='margin:20px'><input type='button' value='{unlock_imap_session}' OnClick=\"javascript:imap_unlock_session()\"></div></center>";
	}
			
		
		
		
	$tabq=user_page_tabs($dn);
	$html="<fieldset style='width:90%'>
		<legend>$firstname $lastname {mailbox}</legend>
		$tabq
		<input type='hidden' id='dn_path' value='$dn_path'>
		<input type='hidden' id=ou value='$ou'>
		<input type='hidden' id=domain value='$domain'>
		<table>
		<tr>
		<td valign='top' width=1%>
		<img src='img/user-maibox.png'>
		</td>
		<td valign='top'>
			<center><p>$body</p></center>
		</td>
		</tr>
		</table>
		</fieldset>
		</legend>
		";
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');	
	
	
}

function users_quota($uid){
	$cyrus=new cyrus();
	$hash=$cyrus->get_quota_array($uid);
	if(!is_array($hash)){return null;}
	$html="
	<tr class=rowA>
	<td align='right'>{storage_usage}:</td>
	<td><strong>{$hash["STORAGE_USAGE"]} kb</strong></td>
	</tr>
	<tr class=rowB>
	<td align='right'>{storage_limit}:</td>
	<td><strong>{$hash["STORAGE_LIMIT"]} kb</strong></td>
	</tr>	
	<tr class=rowA>
	<td align='right'>{message_usage}:</td>
	<td><strong>{$hash["STORAGE_MESSAGE_USAGE"]}</strong></td>
	</tr>
	<tr class=rowB>
	<td align='right'>{message_limit}:</td>
	<td><strong>{$hash["STORAGE_MESSAGE_LIMIT"]}</strong></td>
	</tr>	";
	return $html;	
	
	
	
}

function users_aliases($dn){
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$ldap=new clladp();
	$hash=$ldap->GetDNValues($dn);	
	$firstname=$hash[0]["givenname"][0];
	$lastname=$hash[0]["sn"][0];	
	$tabq=user_page_tabs($dn);
	$html="<fieldset style='width:90%'>
		<legend>$firstname $lastname {aliases}</legend>
		$tabq
		<input type='hidden' id='dn_path' value='$dn_path'>
		<input type='hidden' id=ou value='$ou'>
		<input type='hidden' id=domain value='$domain'>
		<input type='hidden' id='error_bad_mail_formated' value=\"{error_bad_mail_formated}\">		
		<table>
		<tr>
			<td valign='top' width=1%>
			<img src='img/user-maibox.png'>
			</td>
			<td valign='top'>
				<center>
				<table style='width:80%'>";	
	
	
	if(is_array($hash[0]["mailalias"])){
	while (list ($num, $ligne) = each ($hash[0]["mailalias"]) ){
		if(is_numeric($num)){
			if($class=='rowA'){$class='rowB';}else{$class='rowA';}
			$html=$html . "<tr class=$class>
						<td width=1%><img src='img/emails.gif'></td>
						<td style='padding-left:5px'>$ligne</td>
						<td width=1%><a href=\"#\" OnClick=\"javascript:users_delete_aliases('$dn','$num');\"><img src='img/x-delete.gif' style='border='0'></a></td>
					</tr>";
			}
		}
	}
	
	$html=$html . "
			<tr class='rowA'>
				<td>&nbsp;</td>
				<td>
					<input type='text' id='aliases' value='' style='width:70%' OnKeyPress=\"javascript:users_add_aliases('$dn',event);\">&nbsp;
					<input type='button' value='{add}&nbsp;&raquo;' style='width:20%;margin-bottom:0px' OnClick=\"javascript:users_add_aliases('$dn')\"></td>
				<td width=1%>&nbsp;</td>
			</table>
			</center>
			</td>
		</tr>
	</table>";
	$tpl=new templates();
	echo DIV_SHADOW($tpl->_parse_body($html),'windows');
	}

function SaveUser_datas(){
	$tpl=new templates();
	$firstname=$_GET["firstname"];
	$lastname=$_GET["lastname"];
	$email=$_GET["email"];
	$domain=$_GET["domain"];
	$surname=$_GET["surname"];
	$ou=$_GET["ou"];
	if($firstname==null){$error ="{ERROR_NO_FIRST_NAME}";}
	if($lastname==null){$error ="{ERROR_NO_LAST_NAME}";}
	if($email==null){$error ="{ERROR_NO_EMAIL}";}	
	if($surname==null){$error="{ERROR_NO_DIPLAYNAME}";}
	if($error<>null){echo $tpl->_parse_body($error);exit;}

	$uid=$_GET["userid"];
	$ldap=new clladp();
	$update_array["mail"][]="$email@$domain";
	$update_array["mailAlias"][]="$email@$domain";
	$update_array["uid"][]=$uid;
	$update_array["sn"][]=$lastname;
	$update_array["givenName"][]=$firstname;
	$update_array["DisplayName"][]=$surname;
	$update_array["mailDir"][]="cyrus";
	$update_array["homeDirectory"][]="/home/$firstname.$lastname";
	$update_array["domainName"][]=$domain;
	$update_array["accountGroup"][]="0";
	$update_array["accountActive"][]='TRUE';
	$update_array["preferredLanguage"][]="US";
	$update_array["userpassword"][]=$_GET["password"];
	$update_array["cn"][]=$ldap->noaccents("$lastname $firstname");
	
	//$update_array["SenderCanonical"][]=$_GET["SenderCanonical"];
	
	
	if(strlen($_GET["dn_path"])>2){
		$ldap->Ldap_modify($_GET["dn_path"],$update_array);
		if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
	}else{
		
		$ldap->ADDUsers($ou,$ldap->noaccents("$lastname $firstname"),$update_array);
	}
}	

function user_page_tabs($dn=null){
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	if(is_file("ressources/settings.inc")){include("ressources/settings.inc");}
	
	$tabs[0]="{account_name}";
	$tabs[1]="{aliases}";
	if($_GLOBAL["mailboxes_server"]=="cyrus"){
		$tabs[2]="{manage_mailbox} ";
	}
	


	$html="\n\t<ul id=tablist>
		<ul'>\n";
	while (list ($num, $ligne) = each ($tabs) ){
		if($num==$_GET["tab"]){$sid="id='tab_current'";}else{$sid=null;}
			$html=$html . "\t\t<li ><a href=\"#\" OnClick=\"javascript:editmailbox('$dn','$ou','$domain','$num');\" $sid>$ligne</a></li>\n";
		
	}
	$html=$html . "</ul>";
	$tpl=new templates();
	return  $tpl->_parse_body($html);
	
}

function users_add_aliases(){
	$ldap=new clladp();
	$update_array["mailAlias"]=$_GET["aliases"];
	$res=$ldap->Ldap_add_mod($_GET["users_add_aliases"],$update_array);
	echo users_aliases($_GET["users_add_aliases"]);
	}
	
function users_delete_aliases(){
	$ldap=new clladp();
	$hash=$ldap->GetDNValues($_GET["users_delete_aliases"]);	
	unset($hash[0]["mailalias"][$_GET["aliases_num"]]);
	while (list ($num, $ligne) = each ($hash[0]["mailalias"]) ){
		if(!is_numeric($ligne)){$val[]=$ligne;}
		
	}
	$array["mailalias"]=$val;

	
	$ldap->Ldap_modify($_GET["users_delete_aliases"],$array);
	echo users_aliases($_GET["users_delete_aliases"]);
}

function users_create_mailbox(){
	$dn=$_GET["users_create_mailbox"];
	$cyrus=new cyrus();
	$cyrus->CreateMailbox($_GET["uid"]);
	echo users_mailbox($dn);
	
}

function user_set_quota(){
	
	$dn=$_GET["user_set_quota"];
	$ldap=new clladp();
	$hash=$ldap->GetDNValues($_GET["user_set_quota"]);		
	$cyrus=new cyrus();
	$cyrus->SetQuotaDN($hash[0]["uid"][0],$_GET["quota"]);
	echo users_mailbox($dn);
}

function imap_unlock_session(){
	$_SESSION["LOCK_IMAP"]=false;
	$cyrus=new cyrus();
	if($cyrus->connection_off==true){
		echo "<error>Unable to connect to imap server</error>";
		exit;
	}
	echo "<error>Success imap engine unlocked</error>";
	exit();
}
	

			