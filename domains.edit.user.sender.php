<?php
	session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.user.inc');	
	
	


	$usersprivs=new usersMenus();
	if(!$usersprivs->AllowEditOuSecurity){
		if($_SESSION["uid"]<>$_GET["uid"]){
			$tpl=new templates();
			echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
			die();
		}
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["sender-canonical"])){sender_canonical();exit;}
	if(isset($_GET["sender-allowed"])){sender_allowed();exit;}
	if(isset($_GET["sender-host"])){sender_host();exit;}		
	if(isset($_GET["sender-host-delete"])){sender_host_delete();exit;}
	if(isset($_GET["AllowedSMTPTroughtInternet"])){sender_allowed_save();exit;}
	if(isset($_GET["SaveSenderCanonical"])){sender_canonical_add();exit;}	
	if(isset($_GET["DeleteSenderCanonical"])){sender_canonical_del();exit;}
	if(isset($_GET["RefreshSenderLeftInfos"])){echo leftinfos($_GET["RefreshSenderLeftInfos"]);exit;}
	if(isset($_GET["sasl_username"])){sender_host_save();exit;}
	if(isset($_GET["DeleteUserSenderTransport"])){sender_host_delete();exit;}
	if(isset($_GET["sender-bcc-maps"])){sender_bcc_maps_popup();exit;}
	if(isset($_GET["SenderBccMaps"])){sender_bcc_maps_save();exit;}

js();


function sender_bcc_maps_save(){
	$u=new user($_GET["uid"]);
	$u->add_sender_bcc($_GET["SenderBccMaps"]);
	}

function sender_host_delete(){
	$users=new usersMenus();
	if(!$users->AllowSenderCanonical){return null;}
	$user=new user($_GET["uid"]);
	$user->del_transport();
}	
	

function sender_host_save(){
	$sasl_username=$_GET["sasl_username"];
	$sasl_password=$_GET["sasl_password"];
	$relay_address=$_GET["relay_address"];
	$relay_port=$_GET["relay_port"];
	$MX_lookups=$_GET["MX_lookups"];
	$uid=$_GET["uid"];
	$tpl=new templates();
	
	if($relay_address==null){echo $tpl->javascript_parse_text('{error} {relay_address} = NULL');exit;}
	if(!is_numeric($relay_port)){$relay_port=25;}
		
	$domain=new DomainsTools();
	$line=$domain->transport_maps_implode($relay_address,$relay_port,null,$MX_lookups);
	$user=new user($uid);
	if($user->SenderCanonical==null){
		echo $tpl->javascript_parse_text("{error}: {sender_canonical}=NULL");
		exit;
	}
	
	$user->SenderCanoniCalSMTPRelayAdd($line,$sasl_username,$sasl_password,$relay_address);
	$sock=new sockets();
	if($sock->GET_INFO("smtp_sender_dependent_authentication")<>1){
		$sock->SET_INFO("smtp_sender_dependent_authentication",1);
		
	}
	
	$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
	
	
	
	
}


function sender_allowed_save(){
	$userid=$_GET["uid"];
	$user=new user($userid);	
	$user->AllowedSMTPTroughtInternet=$_GET["AllowedSMTPTroughtInternet"];
	if(!$user->AllowedSMTPTroughtInternet_edit()){echo "Failed";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-restricted-users=yes");
	
	}

function sender_canonical_del(){
	$userid=$_GET["uid"];
	$user=new user($userid);
	$user->DeleteCanonical();
	}

function sender_canonical_add(){
	$userid=$_GET["uid"];
	$user=new user($userid);
	
	if(!preg_match("#.+?@.+#",$_GET["SaveSenderCanonical"])){
		echo "Malformed address !\n";
		return;
	}
	
	if(trim($_GET["SaveSenderCanonical"])==null){
		$user->DeleteCanonical();
		return;
	}
	
	$user->SenderCanonical=$_GET["SaveSenderCanonical"];
	$user->add_Canonical();
	}

function js(){

$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();

$title=$tpl->_ENGINE_parse_body('{sender_parameters}');	
$ERROR_NO_PRIVS=$tpl->javascript_parse_text('{ERROR_NO_PRIVS}');
$AllowSenderCanonical="true";
$AllowEditOuSecurity="true";
$AllowedSMTPTroughtInternet=$tpl->_ENGINE_parse_body("{AllowedSMTPTroughtInternet}");
	$usersprivs=new usersMenus();
	if(!$usersprivs->AllowEditOuSecurity){
		$AllowEditOuSecurity="false";
		if(!$usersprivs->AllowSenderCanonical){$AllowSenderCanonical="false";}
	}



$html="
var AllowSenderCanonical=$AllowSenderCanonical;
var AllowEditOuSecurity=$AllowEditOuSecurity;

function {$prefix}Load(){
		YahooWin3(720,'$page?popup={$_GET["uid"]}','$title');
	
	}
	
function LoadUserCanonical(){
	if(!AllowSenderCanonical){alert('$ERROR_NO_PRIVS');return;}
	YahooWin4(450,'$page?sender-canonical=yes&uid={$_GET["uid"]}','{$_GET["uid"]}');
	}
	
function LoadUserAllowedSMTPTroughtInternet(){
	if(!AllowEditOuSecurity){alert('$ERROR_NO_PRIVS');return;}
	YahooWin4(450,'$page?sender-allowed=yes&uid={$_GET["uid"]}','{$_GET["uid"]}::$AllowedSMTPTroughtInternet');
}
function LoadUserTransport(){
	if(!AllowEditOuSecurity){alert('$ERROR_NO_PRIVS');return;}
	YahooWin4(550,'$page?sender-host=yes&uid={$_GET["uid"]}','{$_GET["uid"]}');
}

function SenderBCCMaps(){
	if(!AllowEditOuSecurity){alert('$ERROR_NO_PRIVS');return;}
	YahooWin4(500,'$page?sender-bcc-maps=yes&uid={$_GET["uid"]}','{$_GET["uid"]}');
}

var x_SendBCCMapsSave= function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);SendBCCMapsSave();return;}
	YahooWin4Hide();
	}

function SendBCCMapsSave(){
	var XHR = new XHRConnection();
	XHR.appendData('SenderBccMaps',document.getElementById('SenderBccMaps').value);	
	XHR.appendData('uid','{$_GET["uid"]}');	
	document.getElementById('sender_bcc_maps_id').innerHTML='<center><img src=img/wait-clock.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_SendBCCMapsSave);
	}

var x_SaveSenderCanonicalNew= function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	YahooWin4Hide();
	RefreshSenderLeftInfos();
	}
	
var x_RefreshSenderLeftInfos= function (obj) {
	var results=obj.responseText;
	document.getElementById('senders-left-infos').innerHTML=results;
	}
	
function RefreshSenderLeftInfos(){
	var XHR = new XHRConnection();
	XHR.appendData('RefreshSenderLeftInfos','{$_GET["uid"]}');	
	document.getElementById('senders-left-infos').innerHTML='<center><img src=img/wait-clock.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_RefreshSenderLeftInfos);
	}

function EditSenderCanonical(){
	var SenderCanonical=document.getElementById('SenderCanonical').value;
	var XHR = new XHRConnection();
	XHR.appendData('SaveSenderCanonical',SenderCanonical);
	XHR.appendData('uid','{$_GET["uid"]}');	
	document.getElementById('SaveSenderCanonicalID').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);	
	}
	
function EditSenderCanonicalPress(e){if(checkEnter(e)){EditSenderCanonical();}}
	
function DeleteSenderCanonical(){
	if(!AllowSenderCanonical){alert('$ERROR_NO_PRIVS');return;}
	var XHR = new XHRConnection();
	XHR.appendData('DeleteSenderCanonical','yes');
	XHR.appendData('uid','{$_GET["uid"]}');	
	if(document.getElementById('SaveSenderCanonicalID')){
		document.getElementById('SaveSenderCanonicalID').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	}
	XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);	
	
}

function EditAllowedSMTPTroughtInternet(){
	var AllowedSMTPTroughtInternet=document.getElementById('AllowedSMTPTroughtInternet').value;
	var XHR = new XHRConnection();
	XHR.appendData('AllowedSMTPTroughtInternet',AllowedSMTPTroughtInternet);
	XHR.appendData('uid','{$_GET["uid"]}');	
	document.getElementById('SaveSenderCanonicalID').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);	

}

function SaveUserSenderTransport(){
	var XHR = new XHRConnection();
	XHR.appendData('sasl_username',document.getElementById('sasl_username').value);
	XHR.appendData('sasl_password',document.getElementById('sasl_password').value);
	XHR.appendData('relay_address',document.getElementById('relay_address').value);
	XHR.appendData('relay_port',document.getElementById('relay_port').value);
	XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);
	
	XHR.appendData('uid','{$_GET["uid"]}');	
	document.getElementById('sasltransport').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);	
}
function DeleteUserSenderTransport(){
	var XHR = new XHRConnection();
	XHR.appendData('DeleteUserSenderTransport','yes');
	XHR.appendData('uid','{$_GET["uid"]}');
	if(document.getElementById('sasltransport')){	
		document.getElementById('sasltransport').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	}
	XHR.sendAndLoad('$page', 'GET',x_SaveSenderCanonicalNew);	
}



{$prefix}Load();";
	
echo $html;	
}

function sender_bcc_maps_popup(){
	
	$user=new user($_GET["uid"]);
	$html="
	<div id='sender_bcc_maps_id'>
	<pc style='font-size:13px'>{sender_bcc_explain}</p>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:14px'>{sender_bcc_to}:</td>
		<td>". Field_text("SenderBccMaps",$user->SenderBccMaps,'font-size:14px;padding:3px')."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>
				". button("{edit}","SendBCCMapsSave()")."
		</td>
	</tr>
	</table>
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function sender_canonical(){
	$user=new user($_GET["uid"]);
	$mail=$user->mail;
	$cano=$user->SenderCanonical;
	
	if(trim($cano)<>null){
		$delete=imgtootltip("32-cancel.png","{delete}","DeleteSenderCanonical()");
	}
	
$html="<div style='font-size:16px;font-weight:bolder;padding:5px'>{sender_canonical}</div>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
			<img src='img/canonical-128.png'>
		</td>
		<td valign='top'>
				<div id='SaveSenderCanonicalID'>
				<div class=explain>{sender_canonical_text} </div>
				<span style='font-weight:bolder;font-size:13px'>{from}: &laquo;$mail&raquo; {to}: &laquo;$cano&raquo;</span></p>
				<center>
					<table style='width:100%'>
						<tr>
							<td>". Field_text("SenderCanonical",$cano,"font-size:16px;width:100%;height:30px;padding:5px",null,null,null,false,"EditSenderCanonicalPress(event)")."</td>
							<td>$delete</td>
						</tr>
					</table>
				</center>
				<hr>
				<div style='text-align:right'>
					
					". button("{edit}","EditSenderCanonical();")."
					
				</div>
				</div>
		</td>
			</tr>
			</table>			
		</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function sender_allowed(){
	$user=new user($_GET["uid"]);
	$users=new usersMenus();
	$sock=new sockets();
	$EnableBlockUsersTroughInternet=$sock->GET_INFO("EnableBlockUsersTroughInternet");
	
	if($EnableBlockUsersTroughInternet<>1){
		$error="
		<p style='font-size:13px;font-weight:bold;color:#B3430D'>{EnableBlockUsersTroughInternet_not_activated_text}</p>
		";
		
		if($users->AsPostfixAdministrator){
			$link=texttooltip("{parameters_quickLink}","{parameters_quickLink}","Loadjs('postfix.internet.deny.php')",null,0,"font-size:13px;font-weight:bolder");
		}
		
		$error="<table>
		<tr>
			<td width=1% valign='top'><img src='img/wizard-warning-48.png'></td>
			<td valign='top'>$error<hr>$link</td>
		</tr>
		</table>";
		
	}
	
	$field=Paragraphe_switch_img('{enable}','{AllowedSMTPTroughtInternet}<hr>{enable_disable}',"AllowedSMTPTroughtInternet",$user->AllowedSMTPTroughtInternet);
	
	
$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
			<img src='img/user-internet-ban-128.png'>
		</td>
		<td valign='top'>
				<div id='SaveSenderCanonicalID'>
				
				$field
				<hr>
				$error
				<div style='text-align:right'>
					". button("{edit}","EditAllowedSMTPTroughtInternet();")."
					
				</div>
				</div>
		</td>
			</tr>
			</table>			
		</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

	
function popup(){
	$sock=new sockets();
	$canonical=Paragraphe("canonical-64.png","{sender_canonical}","{sender_canonical_text}","javascript:LoadUserCanonical()","{sender_canonical}");
	$AllowedSMTPTroughtInternet=Paragraphe("user-internet-ban-64.png","{AllowedSMTPTroughtInternet}","{AllowedSMTPTroughtInternet_text}","javascript:LoadUserAllowedSMTPTroughtInternet()","{AllowedSMTPTroughtInternet}");
	$senderHost=Paragraphe("user-server-64.png","{user_transport}","{user_transport_text}","javascript:LoadUserTransport()","{user_transport_text}");
	$sender_bcc=Paragraphe("duplicate-mails-64.png","{sender_bcc}","{sender_bcc_text}","javascript:SenderBCCMaps()","{sender_bcc_text}");
	//quota-policy-64-grey.png
	$quota_policy=Paragraphe("quota-policy-64-grey.png","{quotas_policies}","{quotas_policies_sender_text}");
	if($sock->GET_INFO("EnableArticaPolicyFilter")==1){
		$quota_policy=Paragraphe("quota-policy-64.png","{quotas_policies}","{quotas_policies_sender_text}","javascript:Loadjs('domains.edit.user.sender.quota.php?uid={$_GET["popup"]}')","{quotas_text}");
	}
	
	$leftinfos=leftinfos($_GET["popup"]);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
			<center><img src='img/agent-internet-128.png'></center>
			<br>
			<div id='senders-left-infos' style='margin-top:8px'>$leftinfos</div>
		</td>
		<td valign='top'>
			<div class=explain>{sender_parameters_text}</div>
			<table style='width:100%'>
			<tr>
			<td valign='top'>$canonical</td>
			<td valign='top'>$senderHost</td>
			</tr>
			<tr>
				<td valign='top'>$AllowedSMTPTroughtInternet</td>
				<td valign='top'>$sender_bcc</td>
			</tr>
			<tr>
				<td valign='top'>$quota_policy</td>
				<td valign='top'>&nbsp;</td>
			</tr>			
			</table>			
		</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function leftinfos($uid){
$tpl=new templates();	
$user=new user($uid);
$users=new usersMenus();
if($user->SenderCanonical==null){$user->SenderCanonical=$user->mail;}
$SenderParams=$user->SenderCanonicalSMTPRelay();
	
	$host=$SenderParams["HOST"];
	$auth=$SenderParams["AUTH"];

$SenderCanonical_text=$user->SenderCanonical;
if(strlen($SenderCanonical_text)>25){$SenderCanonical_text=substr($SenderCanonical_text,0,20)."...";}
$SenderCanonical_text=texttooltip($SenderCanonical_text,"$user->mail --&raquo; $user->SenderCanonical","LoadUserCanonical()",null,0,"font-weight:bold");


if(trim($user->SenderCanonical)<>null){
	$delete=imgtootltip("ed_delete.gif","{delete}","DeleteSenderCanonical()");
	if(!$users->AllowSenderCanonical){$delete=null;}
	$SenderCanonical="
			<tr>
				<td class=legend>C:</td>
				<td><strong style='font-size:11px'>$SenderCanonical_text</strong></td>
				<td width=1%>$delete</td>
			</tr>";
}


if($user->AllowedSMTPTroughtInternet==1){$img="status_ok.gif";}else{$img="lock.gif";}
$AllowedSMTPTroughtInternet_text=$tpl->_ENGINE_parse_body("{AllowedSMTPTroughtInternet}");	
if(strlen($AllowedSMTPTroughtInternet_text)>30){$AllowedSMTPTroughtInternet_text=substr($AllowedSMTPTroughtInternet_text,0,27)."...";}

$AllowedSMTPTroughtInternet_text=texttooltip($AllowedSMTPTroughtInternet_text,"{AllowedSMTPTroughtInternet}","LoadUserAllowedSMTPTroughtInternet()",null,0,"font-weight:bold");


	$AllowedSMTPTroughtInternet="
		<tr>
				<td nowrap colspan=2><strong style='font-size:11px'>$AllowedSMTPTroughtInternet_text</strong></td>
				<td width=1%><img src='img/$img'></td>
		</tr>";


if($host<>null){
		$dom=new DomainsTools();
		$arr=$dom->transport_maps_explode($host);
		$delete=imgtootltip("ed_delete.gif","{delete}","DeleteUserSenderTransport()");
		if(!$users->AllowSenderCanonical){$delete=null;}
	if($arr[2]==null){$arr[2]=25;}
$relay_address="<tr>
				<td class=legend>S:</td>
				<td><strong>{$arr[1]}:{$arr[2]}</strong></td>
				<td width=1%>$delete</td>
			</tr>";
}

$html="<table style='width:100%'>
			$SenderCanonical
			$relay_address
			$AllowedSMTPTroughtInternet
		</table>	
	";
		
	
	return $tpl->_ENGINE_parse_body($html);		
}

	
//	


function USER_SENDER_PARAM($userid){
			$us=new user($userid);
			
			$SenderParams=$us->SenderCanonicalSMTPRelay();
			
			$page=CurrentPageName();
			writelogs("USER_ACCOUNT::$userid",__FUNCTION__,__FILE__);
			
				
			$ldap=new clladp();
			$userarr=$ldap->UserDatas($userid);
			$hash=$ldap->ReadDNInfos($userarr["dn"]);	
			$hash["ou"]=$userarr["ou"];
			$ou=$hash["ou"];
			
			if(preg_match('#(.+?)@(.+)#',$hash["mail"],$reg)){
				$domain=$reg[2];
				$email=$reg[1];	
				}			
			
			$priv=new usersMenus();
			$button="<input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform2','$page',true);\">";
			$buttonSenderCanonical="<input type='button' OnClick=\"EditSenderCanonical('{$_GET["userid"]}')\" value='{edit} {sender_canonical}&nbsp;&raquo'>";
			if($priv->AllowAddUsers==false){$button=null;$delete=null;$buttonSenderCanonical=null;}
	
	
	$styleTDLeft="style='padding:5px;font-size:11px'";			
			
	$main=new main_cf();
	
	if($main->main_array["smtp_sender_dependent_authentication"]=="yes"){
		$sasl=new smtp_sasl_password_maps();
		preg_match('#(.+?):(.+)#',$sasl->smtp_sasl_password_hash[$hash["sendercanonical"]],$ath);
		
		$sasl="
		<tr>
			<td colspan=2 style='font-size:12px;padding:4px;font-weight:bold;border-bottom:1px solid #CCCCCC'>{smtp_sender_dependent_authentication}</td>
		</tr>
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{username}:</strong>
			<td $styleTDLeft>" . Field_text('smtp_sender_dependent_authentication_username',$ath[1])."</td>
		</tr>
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{password}:</strong>
			<td $styleTDLeft>" . Field_password('smtp_sender_dependent_authentication_password',$ath[2])."</td>
		</tr>		
		";
		
	}
	

		$enable_internet="
		<form name='userLdapform3'>
				<input type='hidden' name='ou' value='$ou'>
				<input type='hidden' name='SaveAllowedSMTP' value='yes'>
				<input type='hidden' name='dn' value='{$hash["dn"]}'>
				<input type='hidden' name='mail' value='$email'>
				<input type='hidden' name='user_domain' value='$domain'>
				<input type='hidden' name='uid' value='$userid'>	
		<table style='width:100%'>	
		<tr>
			<td colspan=2 style='font-size:12px;padding:4px;font-weight:bold;border-bottom:1px solid #CCCCCC'>{AllowedSMTPTroughtInternet}<p class=caption>{AllowedSMTPTroughtInternet_text}</p></td>
		</tr>				
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{AllowedSMTPTroughtInternet}:</strong>
			<td $styleTDLeft>" . Field_numeric_checkbox_img('AllowedSMTPTroughtInternet',$us->AllowedSMTPTroughtInternet,'{AllowedSMTPTroughtInternet_text}')."</td>
		</tr>
		<tr>
		<td colspan=2 align='right'>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('userLdapform3','$page',true);\">
		</td>
		</tr>
		</table>
		</form>
		
		";
		
	if($priv->AllowAddUsers==false){$enable_internet=null;}	

	
   
	$html="
		
		<form name='userLdapform2'>
				<input type='hidden' name='ou' value='$ou'>
				<input type='hidden' name='SaveLdapUser' value='yes'>
				<input type='hidden' name='dn' value='{$hash["dn"]}'>
				<input type='hidden' name='mail' value='$email'>
				<input type='hidden' name='user_domain' value='$domain'>
				<input type='hidden' name='uid' value='$userid'>
		<table style='width:100%'>
		<tr>
			<td colspan=2 style='font-size:12px;padding:4px;font-weight:bold;border-bottom:1px solid #CCCCCC'>{sender_canonical}</td>
		</tr>		
		<tr>
			<td align='right' nowrap class=legend $styleTDRight>{sender_canonical}:</strong>
			<td $styleTDLeft>" . Field_text('SaveSenderCanonical',$hash["sendercanonical"],'width:70%')."&nbsp;".imgtootltip('ed_delete.gif','{delete}',"DeleteSenderCanonical('{$_GET["userid"]}');")."</td>
		</tr>
		<tr>
		<td colspan=2 align='right'>$buttonSenderCanonical</td>	
		</tr>
		$sasl
		<tr>
		<td colspan=2 align=right>$button</td>
		</tr>		
		</table>
		</form>
		<br>
		$enable_internet
		
		
	";
	
	$html="<H1>{$_GET["userid"]}:&nbsp;{sender_parameters}</H1>
	".$html;
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function sender_host(){
	$uid=$_GET["uid"];
	$user=new user($uid);
	$SenderParams=$user->SenderCanonicalSMTPRelay();
	
	

	
	
	$host=$SenderParams["HOST"];
	$auth=$SenderParams["AUTH"];
	
	$users=new usersMenus();
	$page=CurrentPageName();
	if($host<>null){
		$dom=new DomainsTools();
		$arr=$dom->transport_maps_explode($host);
	}	
	if($arr[2]==null){$arr[2]=25;}
	
$form="
<div style='font-size:14px;font-weight:bolder;color:#005447'>{user_transport}</div>
<table style='width:100%' class=form>
	<td align='right' nowrap class=legend>{relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$arr[1],'width:150px;font-size:15px;padding:3px') . "</td>	
	<tr>
		<td align='right' nowrap class=legend>{smtp_port}:</strong></td>
		<td>" . Field_text('relay_port',$arr[2],'width:60px;font-size:15px;padding:3px') . "</td>	
	</tr>	
	<tr>
		<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$arr[3],'{MX_lookups_text}')."</td>
		<td>{MX_lookups}</td>	
	</tr>	
	</table>";



	
	if(preg_match("#(.+?):(.+)#",$auth,$re)){
		$username=$re[1];
		$password=$re[2];
	}

	
$form2="
<div style='font-size:14px;font-weight:bolder;color:#005447' >{AUTH_SETTINGS}</div>
<table style='width:100%' class=form>
<tr>
<td colspan=2 align=right style='font-size:13px;font-weight:bolder'>$user->SenderCanonical</td></tr>
	<tr>
		<td valign='top' class=legend nowrap>{username}:</td>
		<td valign='top'>".Field_text('sasl_username',$username,'width:130px;font-size:15px;padding:3px')."</td>
	</tr>
	<tr>
		<td valign='top' class=legend nowrap>{password}:</td>
		<td valign='top'>".Field_password('sasl_password',$password,'width:90px;font-size:15px;padding:3px')."</td>
	</tr>	
	</table>";

$delete=imgtootltip("ed_delete.gif","{delete}","DeleteUserSenderTransport()");
$button="<div style='width:100%;text-align:right'>
". button("{edit}","SaveUserSenderTransport();")."
</div>";

if(!$users->AllowSenderCanonical){$form2=null;$button=null;$delete=null;}
if($arr[1]==null){$delete=null;}

	if($user->SenderCanonical==null){
		$js_disable="DisableSenderForm()";
		$button=null;
		$explain_a="
		<table style='width:100%' class=form>
		<tbody>
		<tr>
		<td width=1%><img src='img/warning-panneau-64.png'></td>
		<td width=99%><a href=\"javascript:blur();\" OnClick=\"javascript:LoadUserCanonical();\" style='font-size:14px;text-decoration:underline'>
		<div style='text-align:right'><i>{click_here}</i></div>{SMTP_SENDER_FILL_SENDER_CANONICAL_FIRST}</a></td>
		</tr>
		</tbody>
		</table>";
	}

$html="

<div id='sasltransport'>
<table style='width:100%'>
<tr>
	<td valign='top'>
		<img src='img/user-server-128.png'>
	</td>
	<td valign='top'>$explain_a
		<table style='width:100%'>
		<tr>
			<td valign='top'>
				$form
				<br>
				$form2
				<hr>
			</td>
			<td valign='top' width=1%>$delete</td>
		</tr>
		</table>
$button
	</td>
</tr>
</table>
</div>

<script>
  function DisableSenderForm(){
  	document.getElementById('sasl_username').disabled=true;
  	document.getElementById('sasl_password').disabled=true;
  	document.getElementById('relay_address').disabled=true;
  	document.getElementById('relay_port').disabled=true;
	}
	
	$js_disable;
</script>




";




	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	return false;

$dusbin="
	<form name='FFMrelayhost'>
<input type='hidden' name='uid' value='$uid'>
	<table style='width:100%'>
	<td align='right' nowrap class=legend>{relay_address}:</strong></td>
	<td>" . Field_text('relay_address',$relayT[1]) . "</td>	
	</tr>
	</tr>
	<td align='right' nowrap class=legend>{smtp_port}:</strong></td>
	<td>" . Field_text('relay_port',$relayT[2]) . "</td>	
	</tr>	
	<tr>
	<td align='right' nowrap>" . Field_yesno_checkbox_img('MX_lookups',$relayT[3],'{enable_disable}')."</td>
	<td>{MX_lookups}</td>	
	</tr>
	<tr>
	<td align='right' colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveUserTransport();\"></td>
	</tr>		
	<tr>
	<td align='left' colspan=2><hr><p class=caption>{MX_lookups}<br>{MX_lookups_text}</p></td>
	</tr>					
	</form>";

	if($user->AlternateSmtpRelay<>null){
		$dom=new DomainsTools();
		$arr=$dom->transport_maps_explode($user->AlternateSmtpRelay);
		$p1=Paragraphe32('AUTH_SETTINGS','AUTH_SETTINGS_TEXT',"Loadjs('$page?smtp-sasl=$user->AlternateSmtpRelay&uid=$uid')","inboux-out-32.png");
		$p2=Paragraphe32('sender_canonical','sender_canonical_text',"Loadjs('$page?sender-email-js=yes&uid=$uid')","32-email-out.png");
		$form="
		<center>
		<table style='width:100%'><tr><td>$p1</td><td>$p2</td></tr></table>
		</center>
			<table style='width:100%' class=table_form>
			<tr>
			<td align='right' nowrap class=legend>{relay_address}:</td>
			<td style='font-size:12px;color:red;font-weight:bold'>$user->AlternateSmtpRelay</td>
			<td width=1%>". imgtootltip('ed_delete.gif','{delete}',"DeleteAlternateSmtpRelay();")."</td>
			</tr>
			
			</table>
		
		";
	}

	
	$html="
	<H1>{user_transport}</H1>
	<p class=caption>{user_transport_text}</p>
	$form
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function USER_TRANSPORT_SALS_SAVE(){
	
	$smtp_sasl_password_maps=new smtp_sasl_password_maps();
	if(!$smtp_sasl_password_maps->add($_GET["sasl_server"],$_GET["sasl_username"],$_GET["sasl_password"])){
		echo "ERROR: $smtp_sasl_password_maps->ldap_infos\nLine: ".__LINE__."\nPage: ".basename(__FILE__)."\n";
	}
	
}

?>