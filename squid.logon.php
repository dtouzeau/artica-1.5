<?php
	$GLOBALS["CHANGE_TEMPLATE"]="squid.html";
	$GLOBALS["JQUERY_UI"]="redmond";
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.langages.inc');
	include_once('ressources/class.sockets.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.privileges.inc');
	if($_SESSION["uid"]==null){
			$realm="Authentification Squid analytics";
			Header("WWW-Authenticate: Basic realm='".$realm."'");
			Header("HTTP/1.0  401  Unauthorized");
			
			if(!authenticate()){exit;}
	}

	
	
	//$GLOBALS["DEBUG_TEMPLATE"]=true;

	include_once('ressources/class.squid.inc');
	include_once('ressources/class.status.inc');
	include_once('ressources/class.artica.graphs.inc');
	

	
	
	if(isset($_GET["status"])){echo status();exit;}
	if(isset($_GET["RightMenu1"])){echo RightMenu1();exit;}
	if(isset($_GET["caches-status"])){echo caches_status();exit;}
	
	
	
page();
function page(){	
$title="{SQUID_STATS}";
$page=CurrentPageName();

$html="

<script>
		LoadAjax('content','$page?status=yes')
</script>
";


$tpl=new template_users($title,$html,$_SESSION,0,0,0);
$html=$tpl->web_page;
echo $html;

}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$cache_file=dirname(__FILE__)."/ressources/logs/web/squid.status.html";
	
	$footer=$tpl->_ENGINE_parse_body( "
	<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{today_downloaded_flow}</h1>
	<center>". squid_events_hours()."</center>
	</div>
	
	");
	
	
	$script="<script>LoadAjax('RightMenu1','$page?RightMenu1=yes');</script>";
	if(is_file($cache_file)){echo $tpl->_ENGINE_parse_body(
	"<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{WELCOME_ARTICA_STATS_SQUID}</h1>".
	@file_get_contents($cache_file))."
	</div>$footer$script";return;}
	
	writelogs("$cache_file no such file",__FUNCTION__,__FILE__,__LINE__);
	
	
	$status=new status();
	echo $tpl->_ENGINE_parse_body("<div class='post'>
	<h1 class=\"title\" style='text-transform:capitalize'>{WELCOME_ARTICA_STATS_SQUID}</h1>
	".$status->Squid_status())."
	</div>$footer$script
	
	
	";
}



function RightMenu1(){
	$page=CurrentPageName();
	$sock=new sockets();
	$categories=$sock->GET_INFO("squidStatsCategoriesNum");
	$websitesnums=$sock->GET_INFO("squidStatsWebSitesNum");
	$blocked_today=$sock->GET_INFO("squidStatsBlockedToday");
	$requests=$sock->GET_INFO("squidStatsRequestNumber");	
	$tpl=new templates();
$html="<h2>{status}</h2>
<ul>
	<li><a href=\"#\" >$requests {requests}</a></li>
	<li><a href=\"#\" OnClick=\"javascript:Loadjs('squid.visited.php')\">$websitesnums {visited_websites}</a></li>
	<li><a href=\"#\" OnClick=\"javascript:Loadjs('squid.visited.php')\">$categories {websites_categorized}</a></li>
	<li><a href=\"#\" OnClick=\"javascript:Loadjs('squid.visited.php')\">$blocked_today</a></li>
</ul>
<script>
	LoadAjax('RightMenu2','$page?caches-status=yes');
</script>

";	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function caches_status(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	
	$cacheinfo=unserialize(base64_decode($sock->getFrameWork("cmd.php?squid-cache-infos=yes")));
	if(!is_array($cacheinfo)){return null;}
	$html="<h2>{caches_squid}</h2>";
while (list ($path, $array) = each ($cacheinfo) ){
			$color="#5DD13D";
			if($array["POURC"]>90){$color="#F59C44";}
			if($array["POURC"]>98){$color="#D32D2D";}
			
			$currentsize=FormatBytes($array["CURRENT"]);
			$pourc=pourcentage_basic($array["POURC"],$color,"{$array["POURC"]}%","{used}:$currentsize");
			$path=basename($path);
			
			$max=FormatBytes($array["MAX"]);
			
			$html=$html."
			
			<table>
			<tr>
				<td valign='top' style='font-size:11px;font-weight:bold' nowrap>$path ($max)</td>
			</tr>
			<tr>
				<td valign='top'>$pourc</td>
			</tr>
			<tr>
				<td valign='top' style='font-size:11px;font-weight:bold' nowrap align='right'><i>{used}:$currentsize</i></td>
			</tr>			
			</table>
			";
		}	
	echo $tpl->_ENGINE_parse_body($html);

}


function squid_events_hours(){
	$today=date('Y-m-d');
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/squid.$today.wwsize.png",50);
	if(!$gp->checkfile()){
		return "<img src='ressources/logs/web/squid.$today.wwsize.png'>";
	}
	
	$sql="SELECT hour,hits,www_size FROM squid_events_hours WHERE `day`='$today'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	$count=mysql_num_rows($results);
	
	writelogs($count." rows",__FUNCTION__,__FILE__,__LINE__);
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		
	}	
	
	if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["hour"];
		$ziz=round($ligne["www_size"]/1024);
		$ziz=$ziz/1000;
		$ydata[]=$ziz;
		
	}	
	

	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title="MB";
	$gp->x_title="Hours";
	$gp->title=null;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	return "<img src='ressources/logs/web/squid.$today.wwsize.png'>";
	
	
	
}


function authenticate() {   
	error_log("1) Auth user:{$_SERVER['PHP_AUTH_USER']} password:{$_SERVER['PHP_AUTH_PW']}");


	
	if( !isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW']) ) {return false;} 	
	
	
	
	$_POST["artica_username"]=$_SERVER['PHP_AUTH_USER'];
	$_POST["artica_password"]=$_SERVER['PHP_AUTH_PW'];
	include("ressources/settings.inc");
	
	if($_POST["artica_username"]==$_GLOBAL["ldap_admin"]){
		if($_POST["artica_password"]<>$_GLOBAL["ldap_password"]){
			artica_mysql_events("Failed to logon on the Artica Web console from {$_SERVER["REMOTE_HOST"]}",@implode("\n",$notice),"security","security");
			return false;
		}else{
			
			artica_mysql_events("Success to logon on the Artica Web console from {$_SERVER["REMOTE_HOST"]} as SuperAdmin",@implode("\n",$notice),"security","security");
			//session_start();
			$_SESSION["uid"]='-100';
			$_SESSION["groupid"]='-100';
			$_SESSION["passwd"]=$_POST["artica_password"];
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
		return true;
		}
	}
	
	
	writelogs('This is not Global admin, so test user...',__FUNCTION__,__FILE__);
	$u=new user($_POST["artica_username"]);
	$userPassword=$u->password;
	if(trim($u->uidNumber)==null){
		writelogs('Unable to get user infos abort',__FUNCTION__,__FILE__);
		return false;
	}
	
	if(trim($_POST["artica_password"])<>trim($userPassword)){return false;}
	
	
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
		if(!$users->AsSquidAdministrator){
			artica_mysql_events("failed to logon on the Artica Squid Stats Web console from {$_SERVER["REMOTE_HOST"]} as User",@implode("\n",$notice),"security","security");
			writelogs("[{$_POST["artica_username"]}]: This is not an user =>admin.index.php",__FUNCTION__,__FILE__);
			return false;
		}				
			
		
	}
		
	
	return true;
	
	
}	
