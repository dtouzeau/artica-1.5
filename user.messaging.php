<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");
include_once(dirname(__FILE__)."/ressources/class.domains.diclaimers.inc");
if(!isset($_SESSION["uid"])){header("Location: session-fermee.php");die();}

if(isset($_GET["email-infos-js"])){infos_js();exit;}
if(isset($_GET["email-infos-popup"])){infos_popup();exit;}
if(isset($_GET["add-fetchmail-js"])){fetchmail_add_js();exit;}
if(isset($_GET["add-fetchmail-popup"])){fetchmail_add_popup();exit;}
if(isset($_GET["add-fetchmail-add"])){fetchmail_add();exit;}
if(isset($_GET["table-all-mails"])){echo table_allmails();exit;}
if(isset($_GET["disclaimer"])){echo altermime_disclaimer();exit;}
if(isset($_POST["DisclaimerContent"])){altermime_disclaimer();exit;}
if(isset($_GET["add-alias-js"])){alias_add();exit;}
if(isset($_GET["add-alias-add"])){alias_save();exit;}
if(isset($_GET["delete-alias-js"])){aliase_deletejs();exit;}
if(isset($_GET["delete-alias-delete"])){aliase_delete();exit;}
page();

function page(){
	$ldap=new clladp();
	
	$users=new usersMenus();
	$sock=new sockets();
	$page=CurrentPageName();
	$allmails=table_allmails();
	$block1=iconTable("canonical-64.png","{sender_canonical}",'{sender_canonical_text}',"Loadjs('sender.settings.php')");
	
	if(IS_DISCLAIMER()){
		$block4=iconTable("64-templates.png","{disclaimer_tiny}",'{user_disclaimer_explain}',"s_PopUp('$page?disclaimer=yes',800,800)");
	}
	
	if(IS_ARTICA_FILTER()){
		$block5=iconTable("64-templates.png","{vacation_message}",'{menu_OUT_OF_OFFICE_text}',"s_PopUp('vacation.php',800,600)");
	}
	
	if($users->AllowFetchMails){
		$block2=iconTable("fetchmail-rule-64.png","{APP_FETCHMAIL}",'{user_fetchmail_explain}',"Loadjs('user.fetchmail.php')");
		if($users->imapsync_installed){
			$block6=Paragraphe("sync-64.png","{import_mailbox}","{export_mailbox_text}","javascript:Loadjs('mailsync.php?uid={$_SESSION["uid"]}')");
		}
		
	}
	
	
	
	if($users->MAILMAN_INSTALLED){
				if($sock->GET_INFO('MailManEnabled')==1){
					if($users->AsMailManAdministrator){
						$block3=iconTable("mailman-64.png","{APP_MAILMAN}",'{user_mailman_explain}',"Loadjs('mailman.lists.php')");
						
					}
				}
			}	
			
			
		if($users->cyrus_imapd_installed){
			if(!$users->ZARAFA_INSTALLED){
				if($users->spamassassin_installed){
					$block7=iconTable("anti-spam-learning.png",'{EnableUserSpamLearning}','{EnableUserSpamLearning_text}',
					"Loadjs('domains.edit.user.sa.learn.php?uid={$_SESSION["uid"]}');");
				}
				
				$block8=iconTable("poubelle-64.png",'{empty_your_mailbox}','{empty_this_mailbox_text}',
					"Loadjs('domains.edit.user.empty.mailbox.php?userid={$_SESSION["uid"]}');");
				
			}
		}			
			
	
	//$block2=iconTable("canonical-64.png","{sender_canonical}",'{sender_canonical_text}');
	
	$html="
	<h1>{messaging}</H1>
	<table class=table_form style='width:99%'>
	<tr>
		<td widh=50% valign='top'>
		<div id='table_all_mails'>
			$allmails
		</div>
		</td>
		<td valign='top' style=''>
			$block1
			$block2
			$block3
			$block8
		</td>
		<td valign='top' style=''>
		$block4
		$block5
		$block6
		$block7
		</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function SenderCanonical(){
$users=new usersMenus();	
	
	
}


function infos_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{infos}');
	$html="
	
	YahooWin('500','$page?email-infos-popup={$_GET["email-infos-js"]}','$title');
	
	";
	
	echo $html;
	
}

function fetchmail_add_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{fetchmail_aliases}:&nbsp;{add}');
	$html="
	
	YahooWin('500','$page?add-fetchmail-popup=yes','$title');
	
	var x_AddFetchmailAddr= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		$('#dialog').dialog( 'destroy' );
		LoadAjax('table_all_mails','$page?table-all-mails=yes');
		
		}		

		
	function AddFetchmailAddr(){
		var XHR = new XHRConnection();
		XHR.appendData('add-fetchmail-add',document.getElementById('email').value);
		
		XHR.sendAndLoad('$page', 'GET',x_AddFetchmailAddr);	
	}	
	
	";
	
	echo $html;
	
}

function fetchmail_add(){
	$email=$_GET["add-fetchmail-add"];
	$user=new user($_SESSION["uid"]);
	$user->add_alias_fetchmail($email);
	
	
}

function fetchmail_add_popup(){
	$html="
	<table class=table_form>
	<tr>
		<td class=legend nowrap>{email}:</td>
		<td>". Field_text('email')."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
		<hr>". button("{add}","AddFetchmailAddr()")."</td>
	</tr>
	</table>";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function infos_popup(){
	$users=new user($_SESSION["uid"]);
	$HASH_ALL_MAILS=$users->HASH_ALL_MAILS;
	
	while (list ($num, $val) = each ($HASH_ALL_MAILS) ){
		$emails[]=$val;
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(ParagrapheTXT("{email_infos_query}<hr>". implode(", ",$emails)));
	
}

function IS_ARTICA_FILTER(){
	$sock=new sockets();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$POSTFIX_INSTALLED=$users->POSTFIX_INSTALLED;
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");	
	if(!$POSTFIX_INSTALLED){return false;}
	if($EnableArticaSMTPFilter==0){return false;}
	return true;
}

function IS_DISCLAIMER(){
	$disclaimer=true;
	$users=new usersMenus();
	$sock=new sockets();
	$users->LoadModulesEnabled();
	$POSTFIX_INSTALLED=$users->POSTFIX_INSTALLED;
	$ALTERMIME_INSTALLED=$users->ALTERMIME_INSTALLED;
	$EnableAlterMime=$sock->GET_INFO('EnableAlterMime');
	$EnableArticaSMTPFilter=$sock->GET_INFO("EnableArticaSMTPFilter");
	$DisclaimerOrgOverwrite=$sock->GET_INFO("DisclaimerOrgOverwrite");
	if(!$POSTFIX_INSTALLED){$disclaimer=false;}
	if(!$ALTERMIME_INSTALLED){$disclaimer=false;}
	if($EnableAlterMime==1){
		if($EnableArticaSMTPFilter==0){$disclaimer=false;}
	}
	
	if($DisclaimerOrgOverwrite==0){$disclaimer=false;}	
	if($disclaimer==false){return false;}
	
	$user=new user($_SESSION["uid"]);
	if(!preg_match("#(.+?)@(.+)#",$user->mail,$re)){return false;}
	$dd=new domains_disclaimer($user->ou,trim(strtolower($re[2])));
	if($dd->DisclaimerActivate=="FALSE"){return false;}
	if($dd->DisclaimerUserOverwrite=="FALSE"){return false;}
	return $disclaimer;
}

function alias_add(){
	$tpl=new templates();
	$add=$tpl->javascript_parse_text('{email}');
	$page=CurrentPageName();
	$html="
	
var x_AddAlias= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		LoadAjax('table_all_mails','$page?table-all-mails=yes');
		
		}		
		
		function AddAlias(){
			var email=prompt('$add');
			if(email){
				var XHR = new XHRConnection();
				XHR.appendData('add-alias-add',email);
				XHR.sendAndLoad('$page', 'GET',x_AddAlias);	
			}
		}
	
	
	
	AddAlias()";
	echo $html;
}

function aliase_deletejs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$html="
	
var x_DelAlias= function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		LoadAjax('table_all_mails','$page?table-all-mails=yes');
		
		}		
		
		function DelAlias(){
				var XHR = new XHRConnection();
				XHR.appendData('delete-alias-delete','{$_GET["delete-alias-js"]}');
				XHR.sendAndLoad('$page', 'GET',x_DelAlias);	
		}
	
	
	
	DelAlias()";
	echo $html;	
}

function aliase_delete(){
$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);	
	$updatearray["mailAlias"]=$_GET["delete-alias-delete"];
	if(!$ldap->Ldap_del_mod($hash["dn"],$updatearray)){
		echo $ldap->ldap_last_error;
	}	
}
function alias_save(){
	$_GET["aliase"]=$_GET["add-alias-add"];
	$ldap=new clladp();
	$tpl=new templates();
	writelogs("Adding a new alias \"{$_GET["aliase"]}\" for uid={$_GET["AddAliases"]}",__FUNCTION__,__FILE__,__LINE__);
	$uid=$ldap->uid_from_email($_GET["aliase"]);
	writelogs("\"{$_GET["aliase"]}\"=\"$uid\"",__FUNCTION__,__FILE__,__LINE__);
	if(trim($uid)<>null){
		writelogs("Error, this email already exists",__FUNCTION__,__FILE__,__LINE__);
		echo $tpl->javascript_parse_text('{error_alias_exists}');
		exit;
	}
	writelogs("OK, this email did not exists",__FUNCTION__,__FILE__,__LINE__);
	$user=new user($_SESSION["uid"]);
	
	if(substr($_GET["aliase"],0,1)=='*'){
		$_GET["aliase"]=str_replace('*','',$_GET["aliase"]);
	}else{
		if(!$user->isEmailValid($_GET["aliase"])){
			writelogs("Error, this email is invalid",__FUNCTION__,__FILE__,__LINE__);
			echo $tpl->javascript_parse_text('{error_email_invalid}');
			exit;
		}
	}
	
	writelogs("OK, this {$_GET["aliase"]} email is valid add it for uid=$user->uid",__FUNCTION__,__FILE__,__LINE__);
	
	if(!$user->add_alias($_GET["aliase"])){
		writelogs("Error, LDAP DATABASE $user->ldap_error",__FUNCTION__,__FILE__,__LINE__);
		echo $user->ldap_error;
		exit;
	}
	
	echo $tpl->javascript_parse_text('{success}');	
}



function table_allmails(){
	
	$users=new usersMenus();
	$user=new user($_SESSION["uid"]);
	$HASH_ALL_MAILS=$user->HASH_ALL_MAILS;
	$count=0;
	$html="
	<div class=thead><H1>{mail}</H1>
		<div class=arrowblack>$user->mail</div>
	</div>";
	$count++;
	$aliases=$user->aliases;
	$add_aliases=imgtootltip("plus-16.png","{add aliase}","Loadjs('user.messaging.php?add-alias-js=yes')");
	if(is_array($aliases)){
		
		$html=$html."
		<div class=thead><H1>{aliases}</H1>";
		
	
	
	
	while (list ($num, $val) = each ($aliases) ){
		$count++;
		$delete=imgtootltip("ed_delete.gif","{delete}:$val","Loadjs('user.messaging.php?delete-alias-js=$val')");
		if(!$users->AllowEditAliases){$delete=null;}
		if(strlen($val)>33){$val=substr($val,0,30)."...";}
		$html=$html."
		<div class=arrowblack>
		$val<div style='float:right;margin:0px;padding:0px'>$delete</div>
		</div>
		";	
	}}
if(!$users->AllowEditAliases){$add_aliases=null;}
	
	$html=$html."
	<div style='text-align:right;padding:5px;border-top:1px solid #CCCCCC'>$add_aliases</div>
	</div>";
	
	$add=imgtootltip("plus-16.png","{fetchmail_aliases}:&nbsp;{add}","Loadjs('user.messaging.php?add-fetchmail-js=yes')");
	
	if(!$users->AllowFetchMails){$add=null;}	
	
	
	$fetch=$user->FetchMailMatchAddresses;
	if(is_array($fetch)){
		
		$html=$html."<div class=thead><H1>{fetchmail_aliases}</H1>";
		
	
	while (list ($num, $val) = each ($fetch) ){
		$count++;
		$html=$html."
		<div class=arrowblack>$val</div>
		";	
	}}	
	
	$html=$html."
	<div style='text-align:right;padding:5px;border-top:1px solid #CCCCCC'>$add</div>
	</div>";
	

	//
	$title="<h3>$count&nbsp; {emails_addresses}</h3>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$title$html");
}
function altermime_disclaimer(){
	
	$user=new user($_SESSION["uid"]);
	writelogs("user_disclaimer:: user_disclaimer($user->ou,$user->uid)",__FUNCTION__,__FILE__,__LINE__);
	$dd=new user_disclaimer($user->ou,$user->uid);
	
	if(isset($_POST["DisclaimerContent"])){
		
		$dd->DisclaimerActivate=$_POST["DisclaimerActivate"];
		$dd->DisclaimerOutbound=$_POST["DisclaimerOutbound"];
		$dd->DisclaimerInbound=$_POST["DisclaimerInbound"];
		$dd->DisclaimerContent=$_POST["DisclaimerContent"];
		$dd->uid=$_SESSION["uid"];
		$dd->ou=$user->ou;
		$dd->SaveDislaimerParameters();
		
	}
	
	
	if($dd->DisclaimerContent==null){$dd->DisclaimerContent=$dd->DisclaimerExample;}
	$tpl=new templates();
	$tiny=TinyMce('DisclaimerContent',stripslashes($dd->DisclaimerContent));
	$page=CurrentPageName();
	
	$html="
	<H1>$user->ou/$user->DisplayName</H1>
	<p class=caption>{edit_disclaimer_text}</p>
	<form name='tinymcedisclaimer' method='post' action=\"$page\">
	<div style='background-color:white;margin:5px'>
	<table style='width:99%' class=table_form style='background-color:white'>
	<tr>
		<td class=legend>{enable_disclaimer}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerActivate",$dd->DisclaimerActivate,"{enable_disable}")."</td>
	</tr>	
	<tr>
		<td class=legend>{enable_outbound}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerOutbound",$dd->DisclaimerOutbound,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td class=legend>{enable_inbound}:</td>
		<td>".Field_TRUEFALSE_checkbox_img("DisclaimerInbound",$dd->DisclaimerInbound,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{edit}","document.forms['tinymcedisclaimer'].submit();")."</td>
	</tr>
	</table>
	</div>
	$tiny
	<hr>
	<center>". button("{edit}","document.forms['tinymcedisclaimer'].submit();")."</center>
	</form>
	
	
	";
	$tpl=new templates();
	$page=$tpl->_ENGINE_parse_body($html);
	echo $tpl->PopupPage($page);
}

function ParagrapheTXT($text=null){
	
	$html="<div class='explain'>
	$text</div>	
	";
	return $html;
}