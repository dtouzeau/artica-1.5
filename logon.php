<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	if(!function_exists("session_start")){echo "<div style='margin:200px;padding:10px;border:2px solid red'><center><H1><error>session module is not properly loaded<BR>please restart artica-postfix web daemon using <br> <code>/etc/init.d/artica-postfix restart apache</code></error><div style='color:red;font-size:13px'>Unable to stat session_start function</div></H1></div>";exit;}
	
	if(function_exists("session_start")){session_start();}
	
	$GLOBALS["DEBUG_INCLUDES"]=false;
	unset($_SESSION["LANG_FILES"]);
	if(isset($_POST["php5-ldap-restart"])){restart_phpldap();exit;}
	if(isset($_POST["Changelang"])){applyLang();exit;}
	
	if(isset($_SESSION["uid"])){header("location:users.index.php");exit;}
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);
	

include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.user.inc');
include_once('ressources/class.langages.inc');
include_once('ressources/class.sockets.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.privileges.inc');
include_once('ressources/class.browser.detection.inc');


if($_GET["script"]=="autoaccount"){autoaccount_js();exit;}
if(isset($_GET["first_name"])){autoaccount_submit();exit;}
if(isset($_GET["createaccountForm"])){autoaccount_form();exit;}
if(isset($_GET["submitedform"])){autoaccount_form2();exit;}
if(isset($_GET["lang"])){lang();exit;}
if(isset($_POST["artica_username"])){logon();exit;}
if(isset($_GET["login-form"])){pagelogon();exit;}

$page=CurrentPageName();

if(!is_file("ressources/settings.inc")){echo "
<div style='margin:200px;padding:10px;border:2px solid red'><center><H1>
<error>Artica-postfix daemon is not running... <BR>please start artica-postfix daemon</error>
<div style='color:red;font-size:13px'>Unable to stat ressources/settings.inc</div>
</H1></div>";exit;}




if(isset($_GET["session"])){session_settings();exit;}
if(isset($_GET["start"])){start_js();exit;}
if(isset($_GET["login-form"])){pagelogon();exit;}

echo buildPage();



function start_js(){
	$page=CurrentPageName();	
	$sock=new sockets();
	$logon_parameters=unserialize(base64_decode($sock->GET_INFO("LogonPageSettings")));
	if($logon_parameters["LANGUAGE_SELECTOR_REMOVE"]==null){$logon_parameters["LANGUAGE_SELECTOR_REMOVE"]="0";}	
	if($logon_parameters["DEFAULT_LANGUAGE"]==null){$logon_parameters["DEFAULT_LANGUAGE"]="en";}	


$html="
var MEM_USERNAME='';
var MEM_PASSWORD='';
var x_FillLogonForm=function(obj){
     var tempvalue=obj.responseText;
	 document.getElementById('loginform').innerHTML=tempvalue;
	
	}	
	
var x_SendLogonStart=function(obj){
	document.getElementById('anim').innerHTML='';
     var tempvalue=obj.responseText;
	 var re = new RegExp(/location:(.+)/);
	 m=re.exec(tempvalue);
	  if(m){
		var url=m[1]; 
		 document.location.href=url;
		 return ;
      }	 
	 alert(tempvalue);
	}		

	function FillLogonForm(){
		var LANGUAGE_SELECTOR_REMOVE={$logon_parameters["LANGUAGE_SELECTOR_REMOVE"]};
		if(LANGUAGE_SELECTOR_REMOVE==1){
			Set_Cookie('artica-language', '{$logon_parameters["DEFAULT_LANGUAGE"]}', '3600', '/', '', '');
		}
		document.getElementById('loginform').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		MEM_USERNAME=escape(MEM_USERNAME);
		MEM_PASSWORD=escape(MEM_PASSWORD);		
		LoadAjax('loginform','$page?login-form=yes&MEM_USERNAME='+MEM_USERNAME+'&MEM_PASSWORD='+MEM_PASSWORD+'&LANGUAGE_SELECTOR_REMOVE={$logon_parameters["LANGUAGE_SELECTOR_REMOVE"]}&DEFAULT_LANGUAGE={$logon_parameters["DEFAULT_LANGUAGE"]}');
	}
	
	function SendLogon(e){
		if(checkEnter(e)){SendLogonStart();}
	}
	
	function SendLogonStart(){
		var XHR = new XHRConnection();
		XHR.appendData('artica_username',document.getElementById('artica_username').value);
		XHR.appendData('artica_password',escape(document.getElementById('artica_password').value));
		Set_Cookie('mem-logon-user', document.getElementById('artica_username').value, '3600', '/', '', '');
		XHR.appendData('lang',document.getElementById('lang').value);
		document.getElementById('anim').innerHTML='<img src=img/wait.gif>';
		XHR.sendAndLoad('$page', 'POST',x_SendLogonStart);
		
	}
	


var X_ChangeDefaultLanguage= function (obj) {
	var results=obj.responseText;
	FillLogonForm(); 
	}
		
function ChangeDefaultLanguage(){
	var lang=document.getElementById('lang').value;
	Set_Cookie('artica-language', lang, '3600', '/', '', '');
	var XHR = new XHRConnection();
	XHR.appendData('Changelang',lang);
	MEM_USERNAME=document.getElementById('artica_username').value;
	MEM_PASSWORD=document.getElementById('artica_password').value;
	XHR.sendAndLoad('logon.php', 'POST',X_ChangeDefaultLanguage);	
}

FillLogonForm();";	
		
echo $html;
	
}

function pagelogon(){
$sock=new sockets();
$user=new usersMenus();

if($user->SQUID_INSTALLED){
	$sock=new sockets();
	$SQUIDEnable=trim($sock->GET_INFO("SQUIDEnable"));
	if($SQUIDEnable==null){$SQUIDEnable=1;}
	if($SQUIDEnable==0){$user->SQUID_INSTALLED=false;}
}

error_log("logon form ". __FILE__. " line ". __LINE__);
$fixed_template=$sock->GET_INFO('ArticaFixedTemplate');
error_log("init fixed template=$fixed_template in ". __FILE__. " line ". __LINE__);
if(trim($fixed_template)<>null){$_COOKIE["artica-template"]=$fixed_template;}
$imglogon="img/logon2.png";




if(!$user->POSTFIX_INSTALLED){
	if($user->SAMBA_INSTALLED){
		$imglogon="img/artica-nas.png";
	}
}
if(!$user->POSTFIX_INSTALLED){
	if($user->SQUID_INSTALLED){
		$imglogon="img/logon-squid.png";
	}
}
if($user->POSTFIX_INSTALLED){if(!$user->SQUID_INSTALLED){$imglogon="img/logon-postfix.png";}}


$persologon=$sock->GET_INFO("ArticaLogonImage");
if(trim($persologon)<>null){
	$imglogon=$persologon;
	$imglogon=str_replace("%TEMPLATE%","ressources/templates/{$_COOKIE["artica-template"]}",$imglogon);
}
	
if($user->KASPERSKY_SMTP_APPLIANCE){$imglogon="img/logon-k.png";}
if($user->KASPERSKY_WEB_APPLIANCE){$imglogon="img/logon-squidk.png";}
if($user->ZARAFA_APPLIANCE){$imglogon="img/logon-zarafa.png";}
if($user->OPENVPN_APPLIANCE){$imglogon="img/logo-openvpn.png";}
$page=CurrentPageName();
$center_bg=null;

$newacc="<div style='float:left;margin-left:-180px;width:179px;margin-top:-50px'>
<table style='width:100%'>
<tr " . CellRollOver("Loadjs('$page?script=autoaccount')","{create_your_user_account}").">
<td width=1%>
	<img src='img/member-64-add.png'>
</td>
<td nowrap valign='top'><H3>{register}</H3>
<p class=caption>{create_your_user_account}</p>
</td>
</tr>
</table>
</div>";

error_log("-> buildFrontEnd  ". __FILE__. " line ". __LINE__);
$sock->getFrameWork("cmd.php?buildFrontEnd=yes");

error_log("-> CheckAutousers  ". __FILE__. " line ". __LINE__);
if(!CheckAutousers()){$newacc=null;}

	if($user->KASPERSKY_WEB_APPLIANCE){
		$GLOBALS["CHANGE_TEMPLATE"]="squid.kav.html";
		$imglogon=null;
		$addedlogo="<div style='float:left;margin-left:-190px;width:537px;height:474px;background-image:url(/img/bg_kavweb-appliance.jpg);background-position:left top;'></div>";
		//
	}

if($imglogon<>null){$imglogon="background-image:url($imglogon);";}
$logonForm=logonForm();
$html="
<script>
function SaveSession(){
	var template=document.getElementById('template').value;
	var lang=document.getElementById('lang').value;
	Set_Cookie('artica-template', template, '3600', '/', '', '');
	Set_Cookie('artica-language', lang, '3600', '/', '', '');
	var XHR = new XHRConnection();
	XHR.appendData('lang',lang);
	XHR.sendAndLoad('$page', 'GET');		
	location.reload();
}

function LoadModal(){
$('#loginform').modal({onOpen: function (dialog) {
	dialog.overlay.fadeIn('slow', function () {
		dialog.container.slideDown('slow', function () {
			dialog.data.fadeIn('slow');
		});
	});
}});



}
</script>
$addedlogo
$newacc

<center>
$logonForm
<div style='width:667px;height:395px;
	background-position:center top;
	$imglogon
	background-repeat:no-repeat;border:1px solid #FFFFFF'>
</div>
</center>";

$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
//SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;
}

function logonForm(){
	include_once(dirname(__FILE__)."/ressources/class.html.tools.inc");
	$sock=new sockets();
	$users=new usersMenus();
	$_SESSION["DisableSSHControl"]=trim($sock->GET_INFO("DisableSSHControl"));
	$AllowInternetUsersCreateOrg=$sock->GET_INFO("AllowInternetUsersCreateOrg");
	$AddInArticaLogonFrontPage=$sock->GET_INFO("AddInArticaLogonFrontPage");
	
	
	
	$html=new htmltools_inc();
	$lang=$html->LanguageArray();
	
	
	
	if($_GET["MEM_USERNAME"]==null){
		if($_COOKIE["mem-logon-user"]<>null){
			$_GET["MEM_USERNAME"]=$_COOKIE["mem-logon-user"];
		}
	}
	
	
	if($_COOKIE["artica-language"]==null){
		$languageClass=new articaLang();
		$defaultlanguage=$languageClass->get_languages();
		if($defaultlanguage=="pt"){$defaultlanguage="po";}
		if($lang[$defaultlanguage]==null){$defaultlanguage="en";}
		setcookie("artica-language", $defaultlanguage, time()+172800);
	
	}else{$defaultlanguage=$_COOKIE["artica-language"];}
	
	
	
	
	ksort($lang);
	$field_lang=Field_array_Hash($lang,'lang',$defaultlanguage,"ChangeDefaultLanguage()");

	
	$language_selector="
		<tr>
			<td align='right' class=legend style='font-size:13px'><strong>{language}:</strong></td>
			<td>$field_lang</td>
		</tr>";

		
	if($_GET["LANGUAGE_SELECTOR_REMOVE"]==1){
		$language_selector="<tr>
			<td colspan=2 style='margin:-1px;padding:-1px>
			<input type='hidden' name='lang' id='lang' value='{$_GET["DEFAULT_LANGUAGE"]}'></td>
			</tr>
		";
			
		$_SESSION["detected_lang"]=$_GET["DEFAULT_LANGUAGE"];
		unset($_SESSION["translation"]);
		setcookie("artica-language", $_GET["DEFAULT_LANGUAGE"], time()+172800);			
			
	}
		

$contour_color="#005447";
	if($users->KASPERSKY_WEB_APPLIANCE){
		$GLOBALS["CHANGE_TEMPLATE"]="squid.kav.html";
		$contour_color="#EB6947";
		//bg_kavweb-appliance.jpg
	}
	
if($AllowInternetUsersCreateOrg==1){
	if($AddInArticaLogonFrontPage==1){
		$addon="
		<div>
		<a href=\"miniadm.register.php\" style='font-size:11px'>{register}</a>&nbsp;|&nbsp;<a href=\"miniadm.php\" style='font-size:11px'>{organization_administrator}</a>
		</div>";
		
	}
}

if(!function_exists('ldap_connect')){
	$ldap_error= "
	<div style='border:3px solid red;font-size:16px;color:red;padding:5px;width:220px;position:absolute;left:0;top:0;background-color:white'>
		<center>Error ldap_connect() try to restart the web service<br><br>or check if php5-ldap is installed
		<br>And restart web server</center>
		<center>
		<form name='FF1' method='POST' action='$page'>
		<input type='hidden' name='php5-ldap-restart' value='1'>
		<input type='submit' value='&nbsp;&nbsp;&nbsp;Restart The web service&nbsp;&nbsp;&nbsp;'>
		</form>
		</center>
	</div>
	";

}

if(!function_exists("posix_getuid")){
	$ldap_error=$ldap_error. "
	<div style='border:3px solid red;font-size:16px;color:red;padding:5px;width:220px;position:absolute;left:250px;top:0;background-color:white'>
	Artica-postfix need <strong>posix &laquo;posix_getuid()&raquo;</strong>  function <BR>... please try to install php-posix
	</div>";
	
}

if(!function_exists('mysql_connect')){
	$ldap_error=$ldap_error. "
	<div style='border:3px solid red;font-size:16px;color:red;padding:5px;width:220px;position:absolute;left:500px;top:0;background-color:white'>
	Artica-postfix need  <strong> &laquo;mysql_connect()&raquo;</strong>  function <BR>... please try to install php-mysql
	</div>";	
	
}
	$browser=browser_detection();
	
	if($browser=="ie"){
		$ldap_error=$ldap_error. "
		<div style='border:3px solid red;font-size:16px;color:red;padding:5px;width:220px;position:absolute;left:200px;top:50;background-color:white'>
		{NOIEPLEASE_TEXT}<br>
		<p style='font-size:12px;text-align:left'>
		<i>{error_no_ie_text}</i></p></div><br>";
	}

	
$html="<div id='loginform'>
<center>
<div style='color:red;font-size:13px;font-weight:bold;width:70%;
font-family:Helvetica,Tahoma,Verdana,sans-serif'>{$_GET["ERROR"]}</div>
</center>
<H1 style='text-align:left'>{logon}</h1>
<form name='logon_{$_SERVER["SERVER_NAME"]}' method='POST'>
				<table style='width:50%;border:3px solid $contour_color;margin:5px;padding:5px;'>
				<tr>
					<td align='right' class=legend style='font-size:13px'><strong>{username}:</strong></td>
					<td><input type='text' id='artica_username' name='artica_username' value='{$_GET["MEM_USERNAME"]}' 
					style='border:1px solid black;width:100%;font-size:13px'
					OnkeyPress=\"javascript:SendLogon(event)\"></td>
				</tr>
				<tr>
					<td align='right' class=legend style='font-size:13px'><strong>{password}:</strong></td>
					<td><input type='password'  id='artica_password' name='artica_password' 
					value='{$_GET["MEM_PASSWORD"]}' style='border:1px solid black;width:100%;font-size:13px'
					OnkeyPress=\"javascript:SendLogon(event)\"></td>
				</tr>
				$language_selector
				<tr>
					<td colspan=2 align='right' ><br>
					<input type='button' value='{logon}&nbsp;&raquo;' OnClick=\"javascript:SendLogonStart();\" OnkeyPress=\"javascript:SendLogon(event)\">
					</td>
				</tr>
				<tr><td colspan=2 align='right'>$addon</td>
				<tr>
				<td colspan=2 align='right' style='padding-top:10px'><span id='anim'></span></td>
				</tr>
				</table>	
	
</div>
$ldap_error
	
$script

";
return $html;
}


function ErrorConnection($ldapClass){


$port=$ldapClass->ldap_port;
$password=$ldapClass->ldap_password;
$admin=$ldapClass->ldap_admin;
$suffix=$ldapClass->suffix;
$ldap_host=$ldapClass->ldap_host;

$dn="cn=admin,$suffix";

writelogs("testing connection dn $dn",__FUNCTION__,__FILE__);

$ldap_connection=ldap_connect($ldap_host, $port );
if(!$ldap_connection){ 
	$error="Please check LDAP connection, it seems that Artica is not allowed to connect to &laquo;$ldap_host:$port&raquo;<br>";}
writelogs("Error after connecting:$error '$ldap_connection'",__FUNCTION__,__FILE__);	
	
if($error==null){
		writelogs("settings ldap options LDAP_OPT_PROTOCOL_VERSION",__FUNCTION__,__FILE__);
		ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		writelogs("settings ldap options LDAP_OPT_REFERRALS",__FUNCTION__,__FILE__);
		ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); 		 
		$ldapbind=@ldap_bind($ldap_connection, $dn, $password);
		writelogs("Bind success",__FUNCTION__,__FILE__);	
		
		if(!$ldapbind){
			$errornumber=ldap_errno($ldap_connection);
			$error_text=ldap_err2str($ldap_connection);
			
			switch ($errornumber) {
					case 0x31:
						$error=$error . "<li>Bad username or password. Please try again.</li>";
						break;
					case 0x32:
						$error=$error . "<li>Insufficient access rights.</li>";
						break;
					case 81:
						$error=$error . "<li>Unable to connect to the LDAP server\n $ldap_host:$port, <br>please,verify if ldap daemon is running or the ldap server address";
						break;						
					case -1:
						$error=$error . "<li>$error_text, <br>it seems that Artica could not connect to the server</li>";
						break;
					default:
						$error=$error . "<li>Could not bind to the LDAP server $error_text</li>";
 				}			

		$error="Error number $errornumber,$error_text<br>$error";}
} 				
	
	$html="
		
		
	


			<center>
				<form>
				<div style='width:667px;height:395px;background-image:url(img/nologon.jpg);background-repeat:no-repeat;border:1px solid #FFFFFF'>
					<div style='float:right;margin-right:35px;margin-top:0px;background-color:#FFFFFF;border:1px solid black;padding:5px;font-size:11px;font-weight:bold;color:red;text-align:left'>
						LDAP error with the following informations : <br>
						<ul>
						<li>$ldap_host:$port</li>
						<li>$dn</li>
						</ul>
						$error
						<br>
						
						
					</div>
				</form>
				</div>
			</center>";

$tpl=new template_users('Artica-postfix {error}',$html,1,0,0,0);
echo $tpl->web_page;	
	
	
}


function LDAP_FORM(){
	
	$ldap=new clladp();
	$ldap_admin=$ldap->ldap_admin;
	$ldap_host=$ldap->ldap_host;
	$suffix=$ldap->suffix;

	
	
}

function applyLang(){
	session_start();
	$_SESSION["detected_lang"]=$_POST["Changelang"];
	unset($_SESSION["translation"]);
	setcookie("artica-language", $_POST["Changelang"], time()+172800);
}


function logon(){
	include("ressources/settings.inc");
	
	$_POST["artica_password"]=url_decode_special($_POST["artica_password"]);
	writelogs("Testing logon....{$_POST["artica_username"]}",__FUNCTION__,__FILE__,__LINE__);
	writelogs("Testing logon.... password:{$_POST["artica_password"]}",__FUNCTION__,__FILE__,__LINE__);	
	$_COOKIE["artica-language"]=$_POST["lang"];
	
	$socks=new sockets();
	if(!$socks->TestArticaPort()){
		if(is_file("ressources/logs/boa.start")){
			$boa_error=file_get_contents("ressources/logs/boa.start");
		}
		echo "Unable to connect to Artica daemon port:$boa_error";
		exit;
	}
	
	while (list ($index, $value) = each ($_SERVER) ){
		$notice[]="$index:$value";
	}
	
	
	if($_POST["artica_username"]==$_GLOBAL["ldap_admin"]){
		if($_POST["artica_password"]<>$_GLOBAL["ldap_password"]){
			writelogs("Testing logon.... password:{$_POST["artica_password"]}!==\"{$_GLOBAL["ldap_password"]}\"",__FUNCTION__,__FILE__,__LINE__);	
			artica_mysql_events("Failed to logon on the Artica Web console from {$_SERVER["REMOTE_HOST"]}",@implode("\n",$notice),"security","security");
			echo "bad password";
			return null;
		}else{
			
			artica_mysql_events("Success to logon on the Artica Web console from {$_SERVER["REMOTE_HOST"]} as SuperAdmin",@implode("\n",$notice),"security","security");
			//session_start();
			$_SESSION["uid"]='-100';
			$_SESSION["groupid"]='-100';
			$_SESSION["passwd"]=$_POST["artica_password"];
			$_SESSION["InterfaceType"]="{APP_ARTICA_ADM}";
			setcookie("artica-language", $_POST["lang"], time()+172800);
			$_SESSION["detected_lang"]=$_POST["lang"];
			$_SESSION["privileges"]["ArticaGroupPrivileges"]='
			[AllowAddGroup]="yes"
			[AllowAddUsers]="yes"
			[AllowChangeKav]="yes"
			[AllowChangeKas]="yes"
			[AllowChangeUserPassword]="yes"
			[AllowEditAliases]="yes"
			[AllowEditAsWbl]="yes"
			[AsSystemAdministrator]="yes"
			[AsPostfixAdministrator]="yes"
			[AsArticaAdministrator]="yes"
			';
		$tpl=new templates();
		echo("location:admin.index.php");
		exit;
		}
	}
	
	
	writelogs('This is not Global admin, so test user...',__FUNCTION__,__FILE__);
	$u=new user($_POST["artica_username"]);
	$userPassword=$u->password;
	if(trim($u->uidNumber)==null){
		writelogs('Unable to get user infos abort',__FUNCTION__,__FILE__);
		echo "Unknown user";
		return null;
	}
	if(trim($_POST["artica_password"])==trim($userPassword)){
			$ldap=new clladp();
			$users=new usersMenus();
			$privs=new privileges($u->uid);
			$privileges_array=$privs->privs;
			setcookie("mem-logon-user", $_POST["artica_username"], time()+172800);
			$_SESSION["privileges_array"]=$privs->privs;
			$_SESSION["privs"]=$privileges_array;
			$_SESSION["OU_LANG"]=$privileges_array["ForceLanguageUsers"];
			$_SESSION["uid"]=$_POST["artica_username"];
			$_SESSION["passwd"]=$_POST["artica_password"];
			$_SESSION["privileges"]["ArticaGroupPrivileges"]=$privs->content;
			$_SESSION["groupid"]=$ldap->UserGetGroups($_POST["artica_username"],1);
			$_SESSION["DotClearUserEnabled"]=$u->DotClearUserEnabled;
			$_SESSION["MailboxActive"]=$u->MailboxActive;
			$_SESSION["InterfaceType"]="{APP_ARTICA_ADM}";
			$_SESSION["ou"]=$u->ou;
			$_SESSION["UsersInterfaceDatas"]=trim($u->UsersInterfaceDatas);
			$lang=new articaLang();
			writelogs("[{$_POST["artica_username"]}]: Default organization language={$_SESSION["OU_LANG"]}",__FUNCTION__,__FILE__);
			if(trim($_SESSION["OU_LANG"])<>null){
				$_SESSION["detected_lang"]=$_SESSION["OU_LANG"];
				setcookie("artica-language", $_SESSION["OU_LANG"], time()+172800);
			}else{
				setcookie("artica-language", $_POST["lang"], time()+172800);
				$_SESSION["detected_lang"]=$lang->get_languages();
			}
			

			
			
			$users->_TranslateRights($privileges_array,true);
			if(!$users->IfIsAnuser(true)){
				artica_mysql_events("Success to logon on the Artica Web console from {$_SERVER["REMOTE_HOST"]} as User",@implode("\n",$notice),"security","security");
				writelogs("[{$_POST["artica_username"]}]: This is not an user =>admin.index.php",__FUNCTION__,__FILE__);
				echo("location:admin.index.php");
				return null;
			}
			
			writelogs("[{$_POST["artica_username"]}]: IS AN USER =>../user-backup/logon.php",__FUNCTION__,__FILE__);
			$tpl=new templates();
			$array["USERNAME"]=$_POST["artica_username"];
			$array["PASSWORD"]=md5($_POST["artica_username"]);
			$credentials=base64_encode(serialize($array));
			artica_mysql_events("Success to redirect on the end-user management console from {$_SERVER["REMOTE_HOST"]} as User",@implode("\n",$notice),"security","security");
			echo "location:../user-backup/logon.php?credentials=$credentials";
			return null;
		exit;}else{	
		writelogs("[{$_POST["artica_username"]}]: The password typed  is not the same in ldap database...",__FUNCTION__,__FILE__);
		artica_mysql_events("Failed to logon on the management console as user from {$_SERVER["REMOTE_HOST"]} (bad password)",@implode("\n",$notice),"security","security");
		echo "bad password";
		return null;
	}
	

	
}


function session_settings(){
	$lang=DirFolders('ressources/language');
	$list=DirFolders('ressources/templates');
	unset($list["default"]);
	$list[null]="{default}";
	$lang[null]="{default}";
	$field=Field_array_Hash($list,'template',$_COOKIE["artica-template"]);
	$field_lang=Field_array_Hash($lang,'lang',$_COOKIE["artica-language"]);
	
	$html="<h1>{session_settings}</H1>
	<H3>{template}</H3>
	<p class=caption>{template_text}</p>
	<table style='width:100%'>
	<tr>
	<td class='legend'>{template}:</td>
	<td>$field</td>
	</tr>
	<tr>
	<td class='legend'>{language}:</td>
	<td>$field_lang</td>
	</tr>	
	<td colspan=2 align='right'><input type='button' OnClick=\"javascript:SaveSession();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


function autoaccount_js(){
	$page=CurrentPageName();
	$html="
	
		load();
	
	function load(){
	YahooWin(650,'$page?createaccountForm=yes','','');	
	}
	
	";
	echo $html;
	
}

function autoaccount_form(){
	$page=CurrentPageName();
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	
	
	
	
	$field_domains=Field_array_Hash($domains,"domain");
	
	$html="<h1>{register}</h1>
	<div id='fromadd'>
	<form name='FFMCOMPRESSS'>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/bg_lego.png'></td>
	<td valign='top'>
		<H3>{create_your_user_account}</H3>
	<table class=table_form style='width:100%'>
	<tr>
		<td class=legend>{firstname}:</td>
		<td>" . Field_text('first_name',null,'width:160px')."</td>
	</tr>
	<tr>
		<td class=legend>{lastname}:</td>
		<td>" . Field_text('last_name',null,'width:160px')."</td>
	</tr>	
		<td class=legend>{login}:</td>
		<td>" . Field_text('login',null,'width:100px')."@$field_domains</td>
	</tr>
	</tr>	
		<td class=legend>{password}:</td>
		<td>" . Field_password('password',null,'width:100px')."</td>
	</tr>	
	<tr><td colspan=2><hr></td></tr>
	<tr><td colspan=2 align='right'>
		<input type='button' OnClick=\"javascript:ParseForm('FFMCOMPRESSS','$page',true,false,false,'fromadd','$page?submitedform=yes');\"
		 value='{add}&nbsp;&raquo;'>
	</td>
	</tR>
	</table>
	</form>
	</div>
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function autoaccount_submit(){
	if(!CheckAutousers()){exit;}
	$login=$_GET["login"];
	$email="$login@{$_GET["domain"]}";
	$tpl=new templates();
	$ldap=new clladp();
	$uid=$ldap->uid_from_email($email);
	if($uid<>null){
		echo $tpl->_ENGINE_parse_body('{account_already_exists}');
		exit;
	}
	
	$ou=$ldap->ou_by_smtp_domain($_GET["domain"]);
	$user=new user();
	$user->DisplayName=$_GET["first_name"]." ".$_GET["last_name"];
	$user->sn=$_GET["first_name"];
	$user->cn=$_GET["last_name"];
	$user->mail=$email;
	$user->domainname=$_GET["domain"];
	$user->password=$_GET["password"];
	$user->ou=$ou;
	$user->uid=$login;
	if($user->add_user()){
		echo $tpl->_ENGINE_parse_body("{success}:$email\n");
	}else{
		echo $user->ldap_error;
	}
	
	
}

function CheckAutousers(){
include_once("auto-account.php");
include_once("ressources/class.tcpip.inc");	
$account=new AutoUsers();
$chckip=false;
if($account->AutoCreateAccountEnabled==0){
	writelogs("auto-account is disabled",__FUNCTION__,__FILE__);
	return false;
	}
	$ip=new IP();
	$list=$account->AutoCreateAccountIPArray;
	if(is_array($list)){
		while (list ($num, $val) = each ($list) ){
			if($ip->isInRange($_SERVER['REMOTE_ADDR'],trim($val))){
				writelogs("{$_SERVER['REMOTE_ADDR']} against $val=TRUE",__FUNCTION__,__FILE__);
				return true;
				break;
			}
		}	
	}else{
		writelogs("{$_SERVER['REMOTE_ADDR']} IP List is null",__FUNCTION__,__FILE__);
		return false;
	}

return false;	
}
function autoaccount_form2(){
	$tpl=new templates();
	$html="<H3 style='color:red'>{you_can_close_this_form}</h3>";
	echo $tpl->_ENGINE_parse_body($html);
}
function restart_phpldap(){
	
	if(!isset($_GET["fromClass"])){
		if(function_exists('ldap_connect')){
			echo "<html><head>
			<META HTTP-EQUIV='REFRESH' CONTENT='10'>
			</head>
			<body><center style='margin:100px'<H1>ldap_connect is ok, no need to restart web server</h1></center></body>
			</html>
			";
			exit;
		}
	}
	
	if(!function_exists('ldap_connect')){
		include_once('ressources/class.sockets.inc');
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?restart-apache-no-timeout");
		echo "<html><head>
		<META HTTP-EQUIV='REFRESH' CONTENT='10'>
		</head>
		<body><center style='margin:100px'<H1>Waiting...10 seconds</h1></center></body>
		</html>
		
		";		
		
	}
}
function lang(){
	$sock=new sockets();
	$sock->SET_INFO("session_language",$_GET["lang"]);
}


function buildPage(){
	$users=new usersMenus();
	$logo="logo.gif";
	$logo_bg="bg_header.gif";
	$bg_color="#005447";
	
	
	if($users->KASPERSKY_WEB_APPLIANCE){
		
		$GLOBALS["CHANGE_TEMPLATE"]="squid.kav.html";
		$logo_bg="bg_header_kavweb.gif";
		$logo="logo-kavweb.gif";
		$bg_color="#FFB683";
	}
	
	
$html="<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
	<title></title>
	<meta name='keywords' content=''>
	<meta name='description' content=\"\">
	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=EmulateIE7\" />
	<link href='css/styles_main.css'    rel=\"styleSheet\"  type='text/css' />

	<link href='css/styles_header.css'  rel=\"styleSheet\"  type='text/css' />
	<link href='css/styles_middle.css'  rel=\"styleSheet\"  type='text/css' />
	<link href='css/styles_tables.css'  rel=\"styleSheet\"  type='text/css' />
	<link href=\"css/styles_rounded.css\" rel=\"stylesheet\"  type=\"text/css\" />
	<!--[if lt IE 7]>
	<link rel='stylesheet' type='text/css' href='css/styles_ie.css' />
	<![endif]-->
	<!--[if IE 7]>
	<link rel='stylesheet' type='text/css' href='css/styles_ie7.css' />
	<![endif]-->
		<link rel=\"stylesheet\" type=\"text/css\" rel=\"styleSheet\"  href=\"ressources/templates/default/contact.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" rel=\"styleSheet\"  href=\"ressources/templates/default/menus_top.css\" />
		<link href=\"css/calendar.css\" rel=\"stylesheet\" type=\"text/css\">
		<link href=\"js/jqueryFileTree.css\" rel=\"stylesheet\" type=\"text/css\">
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/uploadify.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/artica-theme/jquery-ui-1.7.2.custom.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.jgrowl.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.cluetip.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.treeview.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/thickbox.css\" media=\"screen\"/>
		<div id='PopUpInfos' style='position:absolute'></div>
		<div id='find' style='position:absolute'></div>
		<script type=\"text/javascript\" language=\"javascript\" src=\"XHRConnection.js\"></script>
		<script type=\"text/javascript\" language=\"JavaScript\" src=\"mouse.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"default.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/cookies.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-1.6.1.min.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jqueryFileTree.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.easing.1.3.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-ui-1.8.custom.min.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/thickbox-compressed.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.simplemodal-1.3.3.min.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.jgrowl_minimized.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.cluetip.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.blockUI.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.treeview.min.js\"></script>
		<!-- js Artica  -->


</head>
<body>
<center>
<div style=\"width:900px;background-image:url(/css/images/$logo_bg);background-repeat:repeat-x;background-position:center top;margin:0px;padding:0px;\">
	<table style=\"width:100%;margin:0px;padding:0px;border:0px;\">
		<tr>
		    <td valign=\"top\" style='padding:0px;margin:0px;border:0px;padding-top:24px'>
			<div style=\"height:72px\">
				<table style=\"padding:0px;margin:0px;border:0px;margin-left:-6px;\">
				<tr>
			   		<td style='padding:0px;border:0px;' valign=\"top\" align=\"left\">
						
							<table style=\"margin:0px;border:0px;padding:0px;\">
							<tr>
			 				<td style=\"margin:0px;padding:0px;background-color:$bg_color\" width=\"160px\">

								<img src='/css/images/$logo' style=\"margin:0px;padding:0px;\">
							</td>
							<td style=\"margin:0px;padding:0px;\" valign=\"middle\">
								<div style=\"margin-top:-7px;padding-left:5px\"> </div>
							</td>
							<td style=\"margin:0px;padding:0px;border:0px solid black\" valign=\"middle\" align='right' width=50%>
								
							</td>
							</tr>

							</table>
						
					  </td>
				</tr>
				<tr>
				  <td style='height:25px'>
					<div id='menus_2'><ul></ul></div id='menus_2'>
				 </td>
				</tr>
				</table>

		</div>
		     </td>
		  
			
		 
	    	</tr>
		<tr>
		<td valign=\"top\" colspan=2 style=\"margin:0px;padding:0px;padding-top:4px;background-color:white;\">	
<div id='middle'>
	<div id='content' style='background-color:white;'>
		<table style='width:100%'>
			<tr>

				<td valign='top' style='padding:0px;margin:0px;width:150px'>
					
				</td>
				<td valign='top' style='padding-left:3px'>
					<div id='template_users_menus'></div>
					<div id='BodyContentTabs'></div>
						<div id='BodyContent'>
							<h1 id='template_title'></h1>
							<!-- content -->
							

<script>
function SaveSession(){
	var template=document.getElementById('template').value;
	var lang=document.getElementById('lang').value;
	Set_Cookie('artica-template', template, '3600', '/', '', '');
	Set_Cookie('artica-language', lang, '3600', '/', '', '');
	var XHR = new XHRConnection();
	XHR.appendData('lang',lang);
	XHR.sendAndLoad('logon.php', 'GET');		
	location.reload();
}

function LoadModal(){
$('#loginform').modal({onOpen: function (dialog) {
	dialog.overlay.fadeIn('slow', function () {
		dialog.container.slideDown('slow', function () {
			dialog.data.fadeIn('slow');
		});
	});
}});



}


</script>


<center>
	<div id='loginform'></div>
</center>
<!-- content end -->
						</div>

				</td>

				<td valign='top'></td>
			</tr>	
	</table>	

	<div class='clearleft'></div>
	<div class='clearright'></div>
	</div id='content'>

</div id='middle'>
</td>
</tr>
<tr>
<td valign='top' align=left colspan=2 >
<div style='background-color:#736e6c;font-size:13px;color:white;height:25px;padding:0px;margin:0px;padding-top:5px;width:900px;text-align:center;margin-left:-5px;margin-bottom:-3px'>

<strong>Artica for postfix. Copyright 2006-". date('Y')."</strong>
</div>
</td>
</tr>
</table>
</div>
</center>
<script>
document.getElementById('loginform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
Loadjs('logon.php?start=yes');</script>
		<div id=\"SetupControl\" style='width:0;height:0'></div>
		<div id=\"dialogS\" style='width:0;height:0'></div> 
		<div id=\"dialogT\" style='width:0;height:0'></div> 
		<div id=\"dialog0\" style='width:0;height:0'></div> 
		<div id=\"dialog1\" style='width:0;height:0'></div>
		<div id=\"dialog2\" style='width:0;height:0'></div> 
		<div id=\"dialog3\" style='width:0;height:0'></div>
		<div id=\"dialog4\" style='width:0;height:0'></div>
		<div id=\"dialog5\" style='width:0;height:0'></div>
		<div id=\"dialog6\" style='width:0;height:0'></div>
		<div id=\"YahooUser\" style='width:0;height:0'></div>
		<div id=\"logsWatcher\" style='width:0;height:0'></div>
		<div id=\"WinORG\" style='width:0;height:0'></div>
		<div id=\"WinORG2\" style='width:0;height:0'></div>
		<div id=\"RTMMail\" style='width:0;height:0'></div>
		<div id=\"Browse\" style='width:0;height:0'></div>
		<div id=\"SearchUser\" style='width:0;height:0'></div>
</body>
</html>";	

return $html;

}



?>