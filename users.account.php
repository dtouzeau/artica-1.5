<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/class.user.inc');

if(isset($_GET["givenName"])){save_datas();exit;}
if(isset($_GET["pool"])){Save_Fetchmail();exit;}
if(isset($_GET["FetchDelete"])){FetchDelete();exit;}
if(isset($_GET["js"])){account_js();exit;}

users_page();

function FetchPage(){
$mny=new usersMenus();
if($mny->AllowFetchMails==false){users_page();exit;}	
$option[""]="{default}";
$option["pop3"]="POP3";
$option["pop2"]="POP2";
$option["imap"]="IMAP";
$option["imap-k4"]="IMAP-K4";
$option["imap-gss"]="IMAP-GSS";
$option["apop"]="APOP";
$option["kpop"]="KPOP";

$auth[""]="Default";
$auth["password"]="PASSWORD";
$auth["kerberos_v5"]="KERBEROS_V5";
$auth["kerberos_v4"]="KERBEROS_V4";
$auth["gssapi"]="GSSAPI";
$auth["cram-md5"]="CRAM-MD5";
$auth["otp"]="OTP";
$auth["ntlm"]="NTLM";
$auth["ssh"]="SSH";
$status='{add mode}';
if(isset($_GET["Fetchedit"])){
	$ldap=new clladp();
	$hashusr=$ldap->UserDatas($_SESSION["uid"]);
	$datas=$hashusr["fetchmail"][$_GET["Fetchedit"]];
	$id="<input type='hidden' name='array_num'  id='array_num' value='{$_GET["Fetchedit"]}'>";
	$status='{edit mode}';
}
$protocol=Field_array_Hash($option,'proto',$datas["proto"]);
$authent=Field_array_Hash($auth,'auth',$datas["auth"]);
$livemess=Field_yesno_checkbox_img('keep',$datas["keep"]);
$ssl=Field_yesno_checkbox_img('ssl',$datas["ssl"]);
$page=CurrentPageName();
$html=TabsPage() ."
		<legend>{remote-mail retrieval}</legend>
		
		<table>
		<tr>
		<td valign='top' width=1%'><img src='img/updaterX-192.gif'></td>
		<td valign='top'>
		<fieldset style='width:90%'><legend>$status</legend>
		<form name=ffmFetch>$id
		<table style='width:99%'>
		<tr>
			<td valing='top' align='right' colspan=2 style='padding-right:20px'><input type='button' OnClick=\"javascript:document.location.href='$page?tab=1'\" value='{add} {new} {rule}&nbsp;&raquo;' ></td>
		</tr>			
		<tr>
			<td valing='top' align='right'><strong>{remote server mailbox}:</td>
			<td valing='top' align='left'>" . Field_text('pool',$datas["pool"],'width:50%') . "&nbsp;$protocol</td>
		</tr>			
		<tr>
			<td valing='top' align='right'><strong>{remote username}:</td>
			<td valing='top' align='left'>" . Field_text('user',$datas["user"]) . "</td>
		</tr>
		<tr>
			<td valing='top' align='right'><strong>{remote password}:</td>
			<td valing='top' align='left'><input type='password' name='pass' id='pass' style='width:50%' value='{$datas["pass"]}'>&nbsp;$authent</td>
		</tr>	
		<tr>
			<td valing='top' align='right'><strong>{Leave messages on server}:</td>
			<td valing='top' align='left'>$livemess</td>
		</tr>
		<tr>
			<td valing='top' align='right'><strong>{Connect in SSL mode}:</td>
			<td valing='top' align='left'>$ssl</td>
		</tr>
		<tr>
			<td valing='top' align='right' colspan=2><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:SaveFetchForm();\"></td>
		</tr>	
		</table>	
	</form>
	</fieldset>
	<fieldset style='width:90%'><legend>{list}</legend>".fetchmail_datas() ."</fieldset>			
	</td>
	</tr>
	</table>
										
		</FIELDSET>
		
		";	
	
$tpl=new template_users("{remote-mail retrieval}",$html);
echo $tpl->web_page;		
	
}

function fetchmail_datas(){
	$uiserid=$_SESSION["uid"];
	$ldap=new clladp();
	$hash=$ldap->UserDatas($uiserid);	
	$page=CurrentPageName();
	if(!is_array($hash["fetchmail"])){return null;}
	$html="<table style='width:95%;border:1px solid #CCCCCC;padding:5px;' align='center'>";
	while (list ($num, $ligne) = each ($hash["fetchmail"]) ){
		$html=$html."<tr>
		<td width=1%>" . imgtootltip('updaterX-22.gif','{edit} ' .$ligne["pool"],"document.location.href='$page?tab=1&Fetchedit=$num'")."</td>
		<td>&nbsp;{$ligne["pool"]}</td>
		<td>&nbsp;{$ligne["user"]}</td>
		<td>&nbsp;{$ligne["proto"]}</td>
		<td>" . imgtootltip('x.gif','{delete}',"document.location.href='$page?tab=1&FetchDelete=$num'")."</td>
		</tr>";
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}


function account_js(){
	
	$js=MEMBER_JS($_SESSION["uid"],1,1);
	
	echo $js;
	
	
}

	
function users_page(){
	$tpl=new template_users();
	$users=new usersMenus();
	$uiserid=$_SESSION["uid"];
	
	$us=new user($uiserid);
	
	
	if($us->jpegPhotoError<>null){
		$picture="
		<center>
		<img src='img/pic_add-107.gif' style='border:1px dotted #CCCCCC'>
		</center>
		<div><strong style='color:red'>{error_image_missing}:</strong><br>$us->jpegPhotoError</strong></div>";
		
		
	}else{
		$picture="
		<center>
		<a href=\"javascript:s_PopUp('edit.thumbnail.php?uid=$us->uid',600,300);\">
		<img src='$us->img_identity' style='border:1px dotted #CCCCCC'>
		</a><br><br>
		". texttooltip("&laquo;&nbsp;{edit}&raquo;&nbsp;",'{edit} photo',"s_PopUp('edit.thumbnail.php?uid=$us->uid',600,300)")."
		</center>";
	}
		
		$picture=RoundedLightWhite($picture);
		if(is_array($us->aliases)){
			while (list ($num, $ligne) = each ($us->aliases) ){
				$aliases.="<br><strong style='font-size:12px'>$ligne</strong>";
			}
		}
		
		$html="
		<table style='width:100%'>
		<tr>
			<td valign='top'>$picture</td>
			<td valign='top'>
				". RoundedGrey("<table style='width:100%;margin:5px;'>
						<tr>
							<td valign='top' width=1% align='center'><img src='img/96-bg_mailbox.png'></td>
							<td valign='top'>
								<H3 style='border-bottom:1px solid #CCCCCC'>$us->DisplayName</H3>
								<p class=caption>$us->givenName&nbsp;$us->sn&nbsp;</p>
								<p class=caption>$us->street $us->postalAddress  $us->postOfficeBox<br>
									$us->postalCode&nbsp;$us->town
								</p>
							</td>
						</tr>
						<tr><td colspan=2><hr></td></tr>
						<tr>
							<td valign='top' width=1% align='center'><img src='img/64-phone.png'></td>
							<td valign='top'>
							<p class=caption>$us->telephoneNumber</p>
							<p class=caption>$us->mobile</p>
							</td>
						</tr>
						<tr><td colspan=2><hr></td></tr>
						
						
						<tr>
							<td valign='top' align='center'><img src='img/64-entire-network.png'></td>
							<td valign='top'><strong style='font-size:12px'>$us->mail</strong>$aliases
						</tr>
					</table>
				")."
			
			
			</td>
		</tr>
		</table>
		
		
		";
	
	$html=RoundedLightGrey($html,"javascript:".MEMBER_JS($_SESSION["uid"],1,1),1);
	
$tpl=new template_users("{your} {account}","$html");
echo $tpl->web_page;	
}

function save_datas(){
	
	$smtp_sender_dependent_authentication_password=$_GET["smtp_sender_dependent_authentication_password"];
	$smtp_sender_dependent_authentication_username=$_GET["smtp_sender_dependent_authentication_username"];
	$canonical=$_GET["SenderCanonical"];
	unset($_GET["smtp_sender_dependent_authentication_password"]);
	unset($_GET["smtp_sender_dependent_authentication_username"]);	
	
	if($canonical<>null){
		if($smtp_sender_dependent_authentication_password<>null){
			if($smtp_sender_dependent_authentication_username<>null){
				$sasl=new smtp_sasl_password_maps();
				$sasl->add($canonical,$smtp_sender_dependent_authentication_username,$smtp_sender_dependent_authentication_password);
			}
		}
	}	
	
	
	
	if(isset($_GET["SenderCanonical"])){
	if(trim($_GET["SenderCanonical"])==null){
			$ldap=new clladp();
			$hash=$ldap->UserDatas($_SESSION["uid"]);
			if($hash['SenderCanonical']<>null){
				writelogs("delete SenderCanonical:{$_SESSION["uid"]}=>{$hash['SenderCanonical']}",__FUNCTION__,__FILE__);
				$upd["SenderCanonical"][0]=$hash['SenderCanonical'];
				$ldap->Ldap_del_mod($hash["dn"],$upd);
				}
		unset($_GET["SenderCanonical"]);
		}
	}	
	
	$uiserid=$_SESSION["uid"];
	$ldap=new clladp();
	$hash=$ldap->UserDatas($uiserid);	
	$cn=$hash["dn"];
	
	while (list ($num, $ligne) = each ($_GET) ){
		$update_array[$num][]=$ligne;
		
	}
	$update_array["displayName"][]=$_GET["givenName"] . " " . $_GET["sn"];
	
	$ldap->Ldap_modify($cn,$update_array);
	if($ldap->ldap_last_error<>null){
		echo $ldap->ldap_last_error;
	}else{echo "OK";}
	
}

function FetchDelete(){
	$mny=new usersMenus();
	if($mny->AllowFetchMails==false){return null;}	
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$update_array["FetchMailsRules"]=$hash["FetchMailsRulesSources"][$_GET["FetchDelete"]];	
	$ldap->Ldap_del_mod($hash["dn"],$update_array);
	FetchPage();
}

function Save_Fetchmail(){
	$add=true;
	$mny=new usersMenus();
	if($mny->AllowFetchMails==false){return null;}
	if(isset($_GET["array_num"])){
		$array_num=$_GET["array_num"];
		unset($_GET["array_num"]);
		$add=false;
	}
		
	if($_GET["pool"]==null){return null;}
	while (list ($num, $ligne) = each ($_GET) ){	
		$line=$line."[$num]=\"$ligne\"\n";
	}
	$ldap=new clladp();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	
	
	if($add==true){
		$update_array["FetchMailsRules"][]=$line;
		$ldap->Ldap_add_mod($hash["dn"],$update_array);
	}else{
		$update_array["FetchMailsRules"]=$hash["FetchMailsRulesSources"];
		$update_array["FetchMailsRules"][$array_num]=$line;
		$ldap->Ldap_modify($hash["dn"],$update_array);
	}
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}else{echo "ok";}
	
}


?>