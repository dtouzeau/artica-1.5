<?php
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.langages.inc");

if(isset($_GET["loggoff"])){logoff();}

if(isset($_GET["start-page"])){startpage();exit;}
if(isset($_GET["auth"])){auth_popup();exit;}
if(isset($_POST["username-logon"])){auth_verif();exit;}



$mini=new miniadmin();
echo $mini->webpage;



function startpage(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	if(!isset($_SESSION["uid"])){
		echo $tpl->_ENGINE_parse_body("<script>YahooWin('550','$page?auth=yes','{authentication}:{$_SERVER["SERVER_NAME"]}',true,[172,238]);</script>");
		return;
		
	}
	
	echo "
		<script>
			LoadAjax('BodyContent','miniadm.index.php');
		</script>
		";
	
	
	
}

function auth_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<span id='postresults'></span>
	<form id='authform'>
	<table style='width:100%' >
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text("username-logon",null,"font-size:16px;padding:5px","script:SuBmitAuthCheck(event)")."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("username-password",null,"font-size:16px;padding:5px","script:SuBmitAuthCheck(event)")."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right' style='font-size:16px;padding:5px'>". button("{submit}","SuBmitAuth()")."</td>
	</tr>
	</table>
	</form>
	
	<script>
		function SuBmitAuthCheck(e){
			if(checkEnter(e)){SuBmitAuth();}
		}
	
	
		var x_DeleteAllArticaEvents= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('articaevents','$page?events-table=yes&LockBycontext={$_GET["LockBycontext"]}');
				
		}			
		
		function SuBmitAuth(){
			AnimateDiv('postresults');
			$.post('$page',  $('#authform').serialize(),
				function(data) {
  					$('#postresults').html(data);
				}
			);
		
		}	
	document.title='Artica {$_SERVER["SERVER_NAME"]}'; 
	
	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function auth_verif(){
	$tpl=new templates();
$u=new user($_POST["username-logon"]);
	$userPassword=$u->password;
	if(trim($u->uidNumber)==null){
		writelogs('Unable to get user infos abort',__FUNCTION__,__FILE__);
		echo $tpl->_ENGINE_parse_body("<center><H2 style='color:red'>{unknown_user}</H2></center>");
		return null;
	}
	if(trim($_POST["username-password"])==trim($userPassword)){
			$ldap=new clladp();
			$users=new usersMenus();
			$privs=new privileges($u->uid);
			$privileges_array=$privs->privs;
			$_SESSION["InterfaceType"]="{ARTICA_MINIADM}";
			setcookie("mem-logon-user", $_POST["username-logon"], time()+172800);
			$_SESSION["privileges_array"]=$privs->privs;
			$_SESSION["privs"]=$privileges_array;
			$_SESSION["OU_LANG"]=$privileges_array["ForceLanguageUsers"];
			$_SESSION["uid"]=$_POST["username-logon"];
			$_SESSION["passwd"]=$_POST["username-logon"];
			$_SESSION["privileges"]["ArticaGroupPrivileges"]=$privs->content;
			$_SESSION["groupid"]=$ldap->UserGetGroups($_POST["artica_username"],1);
			$_SESSION["DotClearUserEnabled"]=$u->DotClearUserEnabled;
			$_SESSION["MailboxActive"]=$u->MailboxActive;
			$_SESSION["ou"]=$u->ou;
			$_SESSION["UsersInterfaceDatas"]=trim($u->UsersInterfaceDatas);
			$lang=new articaLang();
			writelogs("[{$_POST["username-logon"]}]: Default organization language={$_SESSION["OU_LANG"]}",__FUNCTION__,__FILE__);
			if(trim($_SESSION["OU_LANG"])<>null){
				$_SESSION["detected_lang"]=$_SESSION["OU_LANG"];
				setcookie("artica-language", $_SESSION["OU_LANG"], time()+172800);
			}else{
				setcookie("artica-language", $_POST["lang"], time()+172800);
				$_SESSION["detected_lang"]=$lang->get_languages();
			}
			
	echo "<script>
			YahooWinHide();
			LoadAjax('BodyContent','miniadm.index.php');
		</script>
		";
	return;			
			
	}else{
		echo $tpl->_ENGINE_parse_body("<center><H2 style='color:red'>{bdu}</H2></center>");
		
		
	}
	

	
	
}

function logoff(){
unset($_SESSION["uid"]);
unset($_SESSION["privileges"]);
unset($_SESSION["qaliases"]);
unset($_SERVER['PHP_AUTH_USER']);
unset($_SESSION["ARTICA_HEAD_TEMPLATE"]);
unset($_SESSION['smartsieve']['authz']);
unset($_SESSION["passwd"]);
unset($_SESSION["LANG_FILES"]);
unset($_SESSION["TRANSLATE"]);
unset($_SESSION["__CLASS-USER-MENUS"]);
$_COOKIE["username"]="";
$_COOKIE["password"]="";


while (list ($num, $ligne) = each ($_SESSION) ){
	unset($_SESSION[$num]);
}




}

function remove_cache(){
$dir=dirname(__FILE__)."/logs/web/cache/{$_SESSION["uid"]}";
foreach (glob("$dir/*") as $filename) {unlink($filename);}
$sock=new sockets();
$sock->DATA_CACHE_EMPTY();
unset($_SESSION["cached-pages"]);	
}

?>