<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.langages.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.langages.inc");
include_once(dirname(__FILE__)."/ressources/class.groups.inc");

if(!isset($_SESSION["detected_lang"])){
	unset($_SESSION["LANG_FILES"]);
	unset($_SESSION["TRANSLATE"]);
	unset($_SESSION["translation"]);	
	$lang=new articaLang();
	$_SESSION["detected_lang"]=$lang->get_languages();
	setcookie("artica-language", $_SESSION["detected_lang"], time()+172800);
}




if(isset($_GET["confirm"])){confirm();exit;}
if(isset($_GET["create-ou"])){create_ou();exit;}
if(isset($_GET["create-domain"])){create_domain();exit;}
if(isset($_GET["create-user"])){create_user();exit;}
if(isset($_GET["create-group"])){create_group();exit;}
if(isset($_GET["create-groupwares"])){create_groupwares();exit;}
if(isset($_GET["create-conclusion"])){create_conclusion();exit;}
if(isset($_GET["getcredentials"])){getcredentials();exit;}
if(isset($_POST["retreive-email"])){getcredentials_post();exit;}
if(isset($_GET["principal-form"])){principal_form();exit;}
if(isset($_GET["domain-list"])){principal_form_domain_list();exit;}

if(isset($_POST["company"])){post_form();exit;}
$sock=new sockets();
$InternetDomainsAsOnlySubdomains=$sock->GET_INFO("InternetDomainsAsOnlySubdomains");
$AllowInternetUsersCreateOrg=$sock->GET_INFO("AllowInternetUsersCreateOrg");
if($AllowInternetUsersCreateOrg<>1){die("Not authorized ($AllowInternetUsersCreateOrg)");}

$UsersCreateOrgWelComeText=base64_decode($sock->GET_INFO("UsersCreateOrgWelComeText"));
if(preg_match("#(script|php)#",$UsersCreateOrgWelComeText)){
	$UsersCreateOrgWelComeText=htmlentities($UsersCreateOrgWelComeText);
}
$UsersCreateOrgWelComeText=str_replace("[br]","<br>",$UsersCreateOrgWelComeText);
$UsersCreateOrgWelComeText=stripslashes($UsersCreateOrgWelComeText);

$page=CurrentPageName();
$html="
<H1 style='margin-top:-50px'>{create_your_organization}:</H1>
<div style='font-size:12px;margin-top:-15px;margin-bottom:15px'>
{dont_receive_mail_confirmation}:<a href=\"javascript:blur();\" 
OnClick=\"javascript:YahooWin(550,'$page?getcredentials=yes','{dont_receive_mail_confirmation}')\" style='text-decoration:underline'>{click_here}</a>
</div>

<div>$UsersCreateOrgWelComeText</div>
<H2>{register_form}:</H2>
<div id='welcome_organization_register_return' style='color:red;font-family:Verdana;font-size:11px;text-align:center'></div>
<div id='welcome_organization_register'></div>


<script>
LoadAjax('welcome_organization_register','$page?principal-form=yes');

function SaveRegisterOuForm(){
			$.post('$page',  $('#welcome_organization_register_form').serialize(),
				function(data) {
  					$('#welcome_organization_register_return').html(data);
				}
			);
		
		}	

</script>

";
$tpl=new templates();
$mini=new miniadmin($html,false,true,true);
$mini->title=$tpl->_ENGINE_parse_body("{ARTICA_REGISTER_OU}");
$mini->Buildpage();
echo $mini->webpage;
exit;


function principal_form(){
$page=CurrentPageName();
$tpl=new templates();	
$welcome_organization_register=$tpl->_ENGINE_parse_body("{welcome_organization_register}");

	$html="
<form id='welcome_organization_register_form'>
	<div style='font-size:14px' class=explain>$welcome_organization_register</div>
	<p><span></span></p>
	<center>
		<table style='width:85%' class=form>
		<tr>
			<td class=legend style='font-size:16px;'>{company}:</td>
			<td>". Field_text("company",null,"font-size:16px;padding:3px;width:220px")."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:16px;'>{organization}:</td>
			<td>". Field_text("organization",null,"font-size:16px;padding:3px;width:220px")."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:16px;'>{domain}:</td>
			<td><span id='domain-field'></span></td>
		</tr>
		
		<tr>
			<td class=legend style='font-size:16px;'>{username}:</td>
			<td>". Field_text("username",null,"font-size:16px;padding:3px;width:220px")."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:16px;'>{password}:</td>
			<td>". Field_password("password",null,"font-size:16px;padding:3px;width:220px")."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:16px;'>{original_mail}:</td>
			<td>". Field_text("email",null,"font-size:16px;padding:3px;width:220px")."</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><hr>". button("{register}","SaveRegisterOuForm()")."</td>
		</tr>
		</table>
	</center>
</form>	
<script>
	LoadAjax('domain-field','$page?domain-list=yes');
</script>
	";
echo $tpl->_ENGINE_parse_body($html);
}

function principal_form_domain_list(){
	$sock=new sockets();
	$tpl=new templates();
	$InternetDomainsAsOnlySubdomains=$sock->GET_INFO("InternetDomainsAsOnlySubdomains");	

	if($InternetDomainsAsOnlySubdomains==0){
		echo Field_text("domain",null,"font-size:16px;padding:3px;width:220px");
		return;
	}
		$sql="SELECT * FROM officials_domains ORDER BY domain";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$domains[$ligne["domain"]]=$ligne["domain"];
		}
		$domains[null]="{select}";
		$domainfield="
				<table>
				<tr>
					<td>". Field_text("subdomain",null,"font-size:16px;padding:3px;width:220px")."</td>
					<td style='font-size:16px;'><strong>&nbsp;.&nbsp;</strong></td>
					<td>". Field_array_Hash($domains,"domain",null,"style:font-size:16px;padding:3px")."</td>
				</tr>
				</table>

		";
	echo $tpl->_ENGINE_parse_body($domainfield);
	
}

function confirm(){
	unset($_SESSION);
	$tpl=new templates();
	$page=CurrentPageName();
	$html="
	
	<div style='font-size:16px' id='conclusion'><center><hr>{please_while_creating_your_organization}<hr></center></div>
	<p>&nbsp;</p>
	<center>
	
	<div id='first-step' style='width:550px'></div>
	</center>
	<script>
		LoadAjax('first-step','$page?create-ou={$_GET["confirm"]}&key={$_GET["confirm"]}');
	</script>
	
	";
$tpl=new templates();
$mini=new miniadmin($html,false,true,true);
$mini->title=$tpl->_ENGINE_parse_body("{ARTICA_REGISTER_OU}");
$mini->Buildpage();
echo $mini->webpage;	
	
}

function create_ou(){
	$ldap=new clladp();
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["key"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$ou=$ligne["ou"];
	if($ligne["ou"]==null){echo $tpl->_ENGINE_parse_body("{please_register_first}");return;}
	if(!$ldap->AddOrganization($ou)){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{organization}: {$ligne["ou"]} {failed}</td>
		</tr>
		</table>
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-green.png'></td>
			<td valign='top' style='font-size:16px'>{organization}: {$ligne["ou"]} {success}</td>
		</tr>
		</table>
		<div id='step-2'></div>
		<script>
			LoadAjax('step-2','$page?create-domain=yes&key={$_GET["key"]}');
		</script>		
		
		
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;	
	
}

function create_domain(){
	$ldap=new clladp();
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["key"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$ou=$ligne["ou"];
	if($ligne["ou"]==null){echo $tpl->_ENGINE_parse_body("{please_register_first}");return;}
	$domain=$ligne["domain"];
if(!$ldap->AddDomainEntity($ou,$domain)){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{domain}: $domain {failed} $ldap->ldap_last_error</td>
		</tr>
		</table>
		<div id='step-3'></div>
		<script>
			LoadAjax('step-3','$page?create-user=yes&key={$_GET["key"]}');
		</script>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-green.png'></td>
			<td valign='top' style='font-size:16px'>{domain}: $domain {success}</td>
		</tr>
		</table>
		<div id='step-3'></div>
		<script>
			LoadAjax('step-3','$page?create-user=yes&key={$_GET["key"]}');
		</script>		
		
		
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;	
	
}

function create_conclusion(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["key"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$ou=$ligne["ou"];
	$domain=$ligne["domain"];
	if($ligne["ou"]==null){echo $tpl->_ENGINE_parse_body("{please_register_first}");return;}
	$sql="UPDATE register_orgs SET sended=1 WHERE `zmd5`='{$_GET["key"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$html="
	<center>
	<hr>	
	<div style='font-size:14px'>{registration_complete}</div>
	<H3>{youcanaccess_to_thefollowing_services}:</H3>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:14px' width=1% nowrap>{webadministration_console}:</td>
		<td><a href='http://admin.$domain' style='font-size:14px'>http://admin.$domain</a></td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px' width=1% nowrap>{webmail}:</td>
		<td><a href='http://webmail.$domain' style='font-size:14px'>http://webmail.$domain</a></td>
	</tr>
	</table>
	<hr>	
	</center>
	";
	
echo $tpl->_ENGINE_parse_body($html);
		return;		
	
	
}

function create_user(){
	$ldap=new clladp();
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["key"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$ou=$ligne["ou"];
	if($ligne["ou"]==null){echo $tpl->_ENGINE_parse_body("{please_register_first}");return;}
	$domain=$ligne["domain"];
	$user=$ligne["username"];
	$password=$ligne["password"];
	
	$u=new user($user);
	$u->mail="$user@$domain";
	$u->domainname=$domain;
	$u->password=$password;
	$u->ou=$ou;
	
if(!$u->add_user()){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{member}: $user {failed} $u->error</td>
		</tr>
		</table>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-green.png'></td>
			<td valign='top' style='font-size:16px'>{member}: $user <i>$u->mail</i> {success}</td>
		</tr>
		</table>
		<div id='step-4'></div>
		<script>
			LoadAjax('step-4','$page?create-group=yes&key={$_GET["key"]}');
		</script>		
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;		
	
}

function create_group(){
	$ldap=new clladp();
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$q=new mysql();
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["key"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$ou=$ligne["ou"];
	if($ligne["ou"]==null){echo $tpl->_ENGINE_parse_body("{please_register_first}");return;}
	$domain=$ligne["domain"];
	$user=$ligne["username"];
	$password=$ligne["password"];

	$gp=new groups();
	if(!$gp->add_new_group("administrators",$ou)){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{group}: administrators {failed} $gp->ldap_error</td>
		</tr>
		</table>";
		echo $tpl->_ENGINE_parse_body($html);
		return;		
		
	}
	$update_array=array();
	$values[]="[AllowEditOuSecurity]=\"yes\"";
	$values[]="[AsOrgPostfixAdministrator]=\"yes\"";
	$values[]="[AsQuarantineAdministrator]=\"yes\"";
	$values[]="[AsOrgStorageAdministrator]=\"yes\"";
	$values[]="[AsMessagingOrg]=\"yes\"";
	$values[]="[AsOrgAdmin]=\"yes\"";
	
	$gppid=$gp->GroupIDFromName($ou,"administrators");
	$gp=new groups($gppid);
	$update_array["ArticaGroupPrivileges"][0]=@implode("\n",$values);
	$ldap->Ldap_modify($gp->dn,$update_array);
	if($ldap->ldap_last_error<>null){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{group}: administrators ($gppid) {permissions} {failed} $ldap->ldap_last_error</td>
		</tr>
		</table>";
		echo $tpl->_ENGINE_parse_body($html);
		return;			
		
	}
	$EnableVirtualDomainsInMailBoxes=$sock->GET_INFO("EnableVirtualDomainsInMailBoxes");
	$uid=$user;
	if($EnableVirtualDomainsInMailBoxes==1){$uid="$user@$domain";}
	
	if(!$gp->AddUsertoThisGroup($uid)){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{group}: administrators ($gppid) {affect} {$uid} $ldap->ldap_last_error</td>
		</tr>
		</table>";
		echo $tpl->_ENGINE_parse_body($html);
		return;			
		
	}
	
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-green.png'></td>
			<td valign='top' style='font-size:16px'>{group}: administrators &raquo $uid {success}</td>
		</tr>
		</table>
		<div id='step-5'></div>
		<script>
			LoadAjax('step-5','$page?create-groupwares=yes&key={$_GET["key"]}');
		</script>		
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;		
	
	
	
	
}

function create_groupwares(){
	include_once(dirname(__FILE__)."/ressources/class.freeweb.inc");
	$ldap=new clladp();
	$tpl=new templates();
	$sock=new sockets();
	$page=CurrentPageName();
	$q=new mysql();
	$EnableVirtualDomainsInMailBoxes=$sock->GET_INFO("EnableVirtualDomainsInMailBoxes");
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["key"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));		
	$ou=$ligne["ou"];
	if($ligne["ou"]==null){echo $tpl->_ENGINE_parse_body("{please_register_first}");return;}
	$domain=$ligne["domain"];
	$user=$ligne["username"];
	$uid=$user;
	if($EnableVirtualDomainsInMailBoxes==1){$uid="$user@$domain";}	
	$password=$ligne["password"];
	$freeweb=new freeweb();
	$freeweb->servername="admin.$domain";
	$freeweb->ou=$ou;
	$freeweb->uid=$uid;
	$freeweb->groupware="ARTICA_MINIADM";
	if($freeweb->CreateSite()){
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-green.png'></td>
			<td valign='top' style='font-size:16px'>{website}: admin.$domain {ARTICA_MINIADM} {success}</td>
		</tr>
		</table>
		";
		
	}else{
		$html="
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{website}: admin.$domain {ARTICA_MINIADM} {failed}</td>
		</tr>
		</table>
		";		
		
	}
	
	$users=new usersMenus();
	if($users->ZARAFA_INSTALLED){
		$freeweb=new freeweb();
		$freeweb->servername="webmail.$domain";
		$freeweb->ou=$ou;
		$freeweb->uid=$uid;
		$freeweb->groupware="ZARAFA";
		if($freeweb->CreateSite()){
		$html=$html."
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-green.png'></td>
			<td valign='top' style='font-size:16px'>{webmail}: webmail.$domain {APP_ZARAFA} {success}</td>
		</tr>
		</table>
		";
		
	}else{
		$html=$html."
		<table style='width:100%'>
		<tr>
			<td width=1%><img src='img/42-red.png'></td>
			<td valign='top' style='font-size:16px'>{website}: webmail.$domain {APP_ZARAFA} {failed}</td>
		</tr>
		</table>
		";		
		
	}		
		
	}
	
	
	$html=$html."
	<script>
	LoadAjax('conclusion','$page?create-conclusion=yes&key={$_GET["key"]}');
	</script>";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);

}



function post_form(){

	
	$_POST["email"]=strtolower(trim($_POST["email"]));
	$_POST["password"]=trim($_POST["password"]);
	$tpl=new templates();
	$sock=new sockets();
	$ldap=new clladp();
	$EnableVirtualDomainsInMailBoxes=$sock->GET_INFO("EnableVirtualDomainsInMailBoxes");
	if(!ValidateMail($_POST["email"])){echo "<H2>". $tpl->_ENGINE_parse_body("{ERROR_INVALID_EMAIL_ADDR}: ({original_mail}:{$_POST["email"]})")."</H2>";exit;}
	
	$domain=trim(strtolower($_POST["domain"]));
	$company=$_POST["company"];
	$password=$_POST["password"];
	$uid=trim(strtolower($_POST["username"]));
	$ou=$_POST["organization"];
	if($ou==null){$ou=$_POST["company"];}	
	
	if($company==null){echo $tpl->_ENGINE_parse_body("<H2>{company}:{ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM}</H2>");exit;	}
	if($password==null){echo $tpl->_ENGINE_parse_body("<H2>{password}:{ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM}</H2>");exit;	}	
	if($_POST["domain"]==null){echo $tpl->_ENGINE_parse_body("<H2>{domain}:{ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM}</H2>");exit;}
	if($uid==null){echo $tpl->_ENGINE_parse_body("<H2>{username}:{ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM}</H2>");exit;}
	
	if(isset($_POST["subdomain"])){
		$_POST["subdomain"]=trim(strtolower($_POST["subdomain"]));
		if($_POST["subdomain"]==null){echo $tpl->_ENGINE_parse_body("<H2>{subdomain}:{ERROR_VALUE_MISSING_PLEASE_FILL_THE_FORM}</H2>");exit;}
		$domain=$_POST["subdomain"].".$domain";				
	}
	
	$hashdoms=$ldap->hash_get_all_domains();
	
	
	if($hashdoms[$domain]<>null){echo $tpl->_ENGINE_parse_body("<H2>{error_domain_exists} &raquo;<strong>$domain</strong></H2");exit;}
	

	
	$ou=$ldap->StripSpecialsChars($ou);
	$uid=$ldap->StripSpecialsChars($uid);
	if($ou=="users"){echo "<H2>Error: Adding\n$ou words not permitted\n</H2>";exit;}
	if($ou=="groups"){echo "<H2>Error: Adding\n$ou words not permitted\n</H2>";exit;}
	if($ou=="computers"){echo "<H2>Error: Adding\n$ou words not permitted\n</H2>";exit;}
	if($ou=="pureftpd"){echo "<H2>Error: Adding\n$ou words not permitted\n</H2>";exit;}	
	$ldap=new clladp();
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		echo $tpl->_ENGINE_parse_body("<H2>{organization}:{ERROR_OBJECT_ALREADY_EXISTS}</H2>");
		exit;
	}
	
	
	if($EnableVirtualDomainsInMailBoxes==1){
		$uidtests="$uid@$domain";
		$u=new user($uidtests);
		if(!$u->DoesNotExists){
			echo $tpl->_ENGINE_parse_body("<H2>{member}: &laquo;$uid&raquo; {ERROR_OBJECT_ALREADY_EXISTS}</H2>");
			exit;	
		}
	}
	
	$u=new user($uid);
	if(!$u->DoesNotExists){
			echo $tpl->_ENGINE_parse_body("<H2>{member}: &laquo;$uid&raquo; {ERROR_OBJECT_ALREADY_EXISTS}</H2>");
			exit;	
		}	
		

	$zmd5=md5("{$_POST["email"]}$ou$company$domain$uid");
	$password=addslashes($password);
	$company=addslashes($company);
	$uid=addslashes($uid);	
	
	
	$sql="INSERT IGNORE INTO register_orgs(`email`,`ou`,`company`,`domain`,`username`,`password`,`zmd5`)
	VALUES('{$_POST["email"]}','$ou','$company','$domain','$uid','$password','$zmd5')
	";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";return;}
	
	$prefix="http://";
	if($_SERVER["HTTPS"]=="on"){$prefix="https://";}
	
  $link=$prefix.$_SERVER["HTTP_HOST"].'/'.CurrentPageName()."?confirm=$zmd5";
  $subject=$tpl->javascript_parse_text("{organization}: $ou {register_form}");
  $message="{sendmail_request_form}\n----------------------\n$link\n----------------------\n";

	$RobotInternetUsers=$sock->GET_INFO("RobotInternetUsers");
	if($RobotInternetUsers==null){
		$RobotInternetUsers="postmaster@$user->fqdn";
	}
  
  $email =$_POST["email"];
  mail($email, "$subject",$message, "From:" . $RobotInternetUsers);	
	
  echo "<H2>".$tpl->_ENGINE_parse_body("{thanks_registration_mail} <strong>$email</strong>");
	
}

function ValidateMail($emailAddress_str) {
$theMailAddress_str = $emailAddress_str;
$openBracket_num = strpos($emailAddress_str, '<');
$closeBracket_num = strpos($emailAddress_str, '>');

// check, if mailaddress has an illegal combination of brackets
if ( (($openBracket_num !== false) and ( $closeBracket_num === false )) or
(($openBracket_num === false) and ( $closeBracket_num !== false)) ) {
return false;
}

// check, if mailaddress has a name (e.g. 'John Smith <john@smith.com>')
// if so, get the emailaddress within the brackets for further checks
if (( $openBracket_num !== false ) and ( $closeBracket_num !== false )) {
$theMailAddress_str = substr( $emailAddress_str, ++$openBracket_num, $closeBracket_num - $openBracket_num );
}

// we now check that there's exactly one @ symbol, and that the lengths are right
if (!ereg("[^@]{1,64}@[^@]{1,255}", $theMailAddress_str)) {
return false;
}

// Split it into sections to make life easier
$email_array = explode("@", $theMailAddress_str);
$local_array = explode(".", $email_array[0]);
foreach ($local_array as $entry) {
if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $entry)) {
return false;
}
}

if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
$domain_array = explode(".", $email_array[1]);
if (sizeof($domain_array) < 2) {
return false; // Not enough parts to domain
}
foreach ($domain_array as $entry) {
if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $entry)) {
return false;
}
}
}
return true;
}

function getcredentials(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<div class=explain>{form_get_credentials_upd}</div>
	<div id='retreive-connection-results'></div>
	<form id='retreive-connection'>
	<table class=form style='width:100%'>
	<tr>
		<td class=legend>{email}:</td>
		<td>". Field_text("retreive-email",null,"font-size:16px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("retreive-password",null,"font-size:16px;padding:3px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>". button("{submit}","getcredentials()")."</td>
	</tr>
	</table>
	</form>

<script>
function getcredentials(){
AnimateDiv('retreive-connection-results');
	$.post('$page', $('#retreive-connection').serialize(),
			function(data) {
  				$('#retreive-connection-results').html(data);
			}
		);
	}	

</script>	
";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function getcredentials_post(){
	$tpl=new templates();
	$_POST["retreive-email"]=strtolower(trim($_POST["retreive-email"]));
	$_POST["retreive-password"]=trim($_POST["retreive-password"]);
	$sql="SELECT `zmd5` FROM register_orgs WHERE `email`='{$_POST["retreive-email"]}' AND `password`='{$_POST["retreive-password"]}'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){
		echo $tpl->_ENGINE_parse_body("<center><H2 style='color:red'>$q->mysql_error</H2></center>");
		return;
	}
	if($ligne["zmd5"]==null){
		echo $tpl->_ENGINE_parse_body("<center><H2 style='color:red'>{unknown}</H2></center>");
		return;
	}
	
	$prefix="http://";
	if($_SERVER["HTTPS"]=="on"){$prefix="https://";}
	$link=$prefix.$_SERVER["HTTP_HOST"].'/'.CurrentPageName()."?confirm={$ligne["zmd5"]}";	
	$message="{sendmail_request_form}<hr><a href=\"$link\">$link</a><hr>";
	echo $tpl->_ENGINE_parse_body($message);
}
?>