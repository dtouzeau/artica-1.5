<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ini.inc');	
	include_once('ressources/class.ocs.inc');
	
	include_once(dirname(__FILE__). "/ressources/class.cyrus.inc");
	
	if((isset($_GET["uid"])) && (!isset($_GET["userid"]))){$_GET["userid"]=$_GET["uid"];}
	
	
	
	
//permissions	
	$usersprivs=new usersMenus();
	$change_aliases=1;
	$modify_user=1;
	
	if(!$usersprivs->AsAnAdministratorGeneric){
		if(!$usersprivs->AllowEditAliases){
			$change_aliases=0;
			}
		if($_SESSION["uid"]<>$_GET["userid"]){$modify_user=0;}
	}
	

if($change_aliases==0){die();}


		if(isset($_GET["popup"])){echo USER_ALIASES_MAILING_LIST($_GET["uid"]);exit;}
		if(isset($_GET["USER_ALIASES_MAILING_LIST_ADD_JS"])){USER_ALIASES_MAILING_LIST_ADD_JS();exit;}
		if(isset($_GET["USER_ALIASES_MAILING_LIST_DEL_JS"])){USER_ALIASES_MAILING_LIST_DEL_JS();exit;}
		if(isset($_GET["x_AddAliasesMailing"])){echo USER_ALIASES_MAILING_LIST_LIST($_GET["x_AddAliasesMailing"]);exit;}
		if(isset($_GET["MailingListAddressGroupSwitch"])){MailingListAddressGroupSwitch_js();exit;}
		if(isset($_GET["MailingListAddressGroup"])){USER_ALIASES_MAILING_LIST_GROUP_SAVE();exit;}
		if(isset($_GET["aliases-mailing-list"])){echo USER_ALIASES_MAILING_LIST_LIST($_GET["uid"]);exit;}

js();

function MailingListAddressGroupSwitch_js(){
	$page=CurrentPageName();
	$html="
	
var x_MailingListAddressGroupJS= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);
		document.getElementById('MailingListAddressGroup').checked=false;
		return;
	}
	LoadAjax('aliases-mailing-list','$page?x_AddAliasesMailing={$_GET["uid"]}&ou={$_GET["ou"]}&uid={$_GET["uid"]}');	
}	
	
function MailingListAddressGroupJS(){
	var XHR = new XHRConnection();
	XHR.appendData('MailingListAddressGroup','yes');
	XHR.appendData('uid','{$_GET["uid"]}');
	if(document.getElementById('MailingListAddressGroup').checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
	XHR.sendAndLoad('$page', 'GET',x_MailingListAddressGroupJS);
}
	
	
	MailingListAddressGroupJS();";
	echo $html;
	
}

function USER_ALIASES_MAILING_LIST_GROUP_SAVE(){
	$user=new user($_GET["uid"]);
	$user->MaillingListGroupEnable($_GET["enabled"]);
	}


function USER_ALIASES_MAILING_LIST_ADD_JS(){
	$page=CurrentPageName();
	$md="A_".md5("{$_GET["mail"]}{$_GET["uid"]}".date('Y-m-d-h:i:s'));
$html="
var $md= function (obj) {
	LoadAjax('aliases-mailing-list','$page?x_AddAliasesMailing={$_GET["uid"]}&ou={$_GET["ou"]}&uid={$_GET["uid"]}');	
}


	var aliase=prompt(document.getElementById('AddAliasesMailing_jstext').value);
	if(aliase){
		var XHR = new XHRConnection();
		XHR.appendData('AddAliasesMailing','{$_GET["uid"]}');
		XHR.appendData('aliase',aliase);
		document.getElementById('aliases-mailing-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('domains.edit.user.php', 'GET',$md);
	}";

echo $html;
}
function USER_ALIASES_MAILING_LIST_DEL_JS(){
	$page=CurrentPageName();
	$md="A_".md5("{$_GET["mail"]}{$_GET["uid"]}".date('Y-m-d-h:i:s'));
$html="
		var $md= function (obj) {
			LoadAjax('aliases-mailing-list','$page?x_AddAliasesMailing={$_GET["uid"]}&ou={$_GET["ou"]}&uid={$_GET["uid"]}');	
		}

		var XHR = new XHRConnection();
		XHR.appendData('DeleteAliasesMailing','{$_GET["uid"]}');
		XHR.appendData('aliase','{$_GET["mail"]}');
		document.getElementById('aliases-mailing-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('domains.edit.user.php', 'GET',$md);
	";

echo $html;
}

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{$_GET["uid"]}: {mailing_list}");
	$page=CurrentPageName();
	$html="
	function mailing_list_load(){
			YahooWin2(630,'$page?popup=yes&uid={$_GET["uid"]}','$title');
		}
	
	
	mailing_list_load();
	";
	
	echo $html;
	
}

	
	
function USER_ALIASES_MAILING_LIST($userid){
   	$page=CurrentPageName();
   	$u=new user($userid);
   	
   
    	   	
   	
   	$tpl=new templates();
   	$priv=new usersMenus();
   	
   	$boutton_on=Paragraphe('64-alias-add.png','{add_new_alias}','{add_new_alias_text}',"javascript:Loadjs('$page?USER_ALIASES_MAILING_LIST_ADD_JS=yes&uid=$userid&ou=$u->ou');");
   	$boutton_off=Paragraphe('64-alias-add-grey.png','{add_new_alias}','{add_new_alias_text}',"");
   	$test_mail=Paragraphe("test-mail.png","{send_a_test_mail}","{send_a_test_mail_text}","javascript:Loadjs('postfix.sendtest.mail.php?rcpt=$u->mail')");
   	
   	$button=button("{add}","Loadjs('$page?USER_ALIASES_MAILING_LIST_ADD_JS=yes&uid=$userid&ou=$u->ou')");
   	
   	
   	if($priv->AllowAddUsers==false){$button=$boutton_off;}else{$button=$boutton_on;}
    $list=USER_ALIASES_MAILING_LIST_LIST($userid);
   	$html="<p style='font-size:13px'>{aliases_mailing_text}:&nbsp;&laquo;<b>{$u->mail}&raquo;</b></p>
    	<br>
    	<input type='hidden' id='AddAliasesMailing_jstext' value='{AddAliasesMailing_jstext}'>
    	<input type='hidden' id='ou' value='$u->ou'>
    	<table style='width:100%'>
    	<tr>
    		<td class=legend>{MailingListAddressGroup}:</td>
    		<td align='left'>". Field_checkbox("MailingListAddressGroup",1,$u->MailingListAddressGroup,"Loadjs('$page?MailingListAddressGroupSwitch=yes&uid=$userid&ou=$u->ou')","{MailingListAddressGroup_text}")."</td>
    	</tr>
    	</table>
    	
    	
		<table style='width:100%'>
		
		<tr>
    	<td colspan=3 style='padding:5px'>
    	<table style='width:100%;border:0px solid #005447'>
    	<tr>
    		<td valign='top' align='center'>
    			<div id='aliases-mailing-list' style='width:360px;height:250px;overflow:auto'></div>	
    		<td valign='top' style='width:250px'>$button$test_mail</td>
    	</tr>
    	</table>
    	</td>
    	</tr>
    	</table>
    	
    	<script>
    		LoadAjax('aliases-mailing-list','$page?aliases-mailing-list=yes&uid=$userid');
    	</script>
    	
    	";
    	return $tpl->_ENGINE_parse_body($html);
    }

    function USER_ALIASES_MAILING_LIST_LIST($userid){    	
    	$u=new user($userid);
    	$page=CurrentPageName();
		$hash=$u->LoadAliasesMailing();
   		$ali="<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>$userid</th>
	</tr>
</thead>";
    		while (list ($num, $ligne) = each ($hash) ){
    			$array[$ligne]=true;
    		}
    		$groups=$u->MailingGroupsLoadAliases(); 
    		while (list ($num, $ligne) = each ($groups) ){
    			if($ligne==null){continue;}  	
    			$array[$ligne]=false;
    		}	
    			
    		
    
    	while (list ($num, $ligne) = each ($array) ){
    		$delete=imgtootltip('x.gif','{delete aliase}',"Loadjs('$page?USER_ALIASES_MAILING_LIST_DEL_JS=yes&mail=$num&uid=$userid&ou=$u->ou')");
    		if(!$ligne){$delete=null;}
    		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
    		$ali=$ali . "<tr class=$classtr>
    		<td width=1%><img src='img/mailbox_storage.gif'></td>
    		<td style='padding:3px;' width=91% nowrap align='left'><code style='font-size:13px'>$num</code></td>
    		<td style='padding:3px;' width=1%>" . imgtootltip('test-mail-22.png','{send_a_test_mail_text}',
    		"javascript:Loadjs('postfix.sendtest.mail.php?rcpt=$num')')")."</td>
    		
    		<td  style='padding:3px;' width=1%>$delete</td>
    		</tr>
    		";
    		}

    		
    		$ali=$ali."</table>";
    		return $ali;    	
    	
}    
	
?>