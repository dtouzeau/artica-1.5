<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.dotclear.inc');
	
	
	if(isset($_GET["UserDotClear"])){UserDotClear();exit;}
	
	$user=new usersMenus();
	if(!$user->AsAnAdministratorGeneric){header('location:users.index.php');exit;};
	if(isset($_GET["status"])){main_status();exit;}
	if($_GET["main"]=="yes"){settings();exit;}
	if(isset($_GET["EnableDotClearHTTPService"])){SaveConf();exit;}
	
	main_page();
	
function main_page(){
	

	
	$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	}
	
var x_RebuildTablesDotClear= function (obj) {
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
}
	
function RebuildTablesDotClear(){
var XHR = new XHRConnection();
	document.getElementById('services_status').innerHTML=\"<center><img src='img/wait_verybig.gif'></center>\";
	XHR.appendData('RebuildTablesDotClear','yes');
	XHR.sendAndLoad('$page', 'GET',x_RebuildTablesDotClear);	
}
	
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg-dotclear.png' style='margin-left:80px;margin-right:80px'></td>
	<td valign='top'><div id='services_status'></div><br><p class=caption>{about}</p></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();LoadAjax('main_config','$page?main=yes');ChargeLogs();</script>
	
	";
	$tpl=new template_users('{APP_DOTCLEAR}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}
	
	
function settings(){
	
	$dotclear=new dotclear();
	$page=CurrentPageName();
	$html="
	<center>
	<form name=FF1>
	<table style='width:90%;border:1px dotted #CCCCCC'>
	<tr>
		<td class=legend>{EnableDotClearHTTPService}:</td>
		<td>" . Field_numeric_checkbox_img('EnableDotClearHTTPService',$dotclear->EnableDotClearHTTPService,'{EnableDotClearHTTPService}')."</td>
	</tr>	
	<tr>
		<td class=legend>{DotClearHttpListenPort}:</td>
		<td>" . Field_text("DotClearHttpListenPort",$dotclear->DotClearHttpListenPort,"width:40px")."</td>
	</tr>
	<tr>
		<td class=legend>{DotClearExternalWebUri}:</td>
		<td>" . Field_text("DotClearExternalWebUri",$dotclear->DotClearExternalWebUri,"width:190px")."</td>
	</tr>
	<tr>
		<td class=legend>{DotClearExternalAdminUri}:</td>
		<td>" . Field_text("DotClearExternalAdminUri",$dotclear->DotClearExternalAdminUri,"width:190px")."</td>
	</tr>
	
	
	
	<tr>
		<td colspan=2 align='right' style='margin-bottom:5px'><input type='button' OnClick=\"javascript:ParseForm('FF1','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	
	
	</table>
	</form>
	</center>
	";
	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		 	
	
}

function SaveConf(){
	
	$dotclear=new dotclear();
	while (list ($num, $val) = each ($_GET) ){
		$dotclear->$num=$val;
	}
	
	$dotclear->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
	
}

function main_status(){
$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('dotclear_status'));	
	$status=DAEMON_STATUS_ROUND("DOTCLEAR",$ini,null);
	$tpl=new templates();
	
	$dotclear=new dotclear();
	if(!$dotclear->TestsDatabase()){
	$badtables="<br><div style='width:97%' class='paragraphe' id='$id' $h3style>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/42-red.png'></td>
	<td valign='top' >
		<H3 style='color:red'>{tables_error}</H3>
		<p class=caption>{tables_error_text}</p>
		<center>
		<input type='button' OnClick=\"javascript:RebuildTablesDotClear();\" value='{rebuild_tables}&nbsp;&raquo;'>
		</center>
	
	</td>
	</tr>
	</table>
	</div>";
		
	}
	
	//DotClearRebuildTables
	
	
	echo $tpl->_ENGINE_parse_body("$status$badtables");		 
}


function UserDotClear(){
	session_start();
	if(!isset($_SESSION["uid"])){die("Session closed");}
	
	$dotclear=new dotclear();
	
	
	$html="
	
		<H1>{user_dotclear_access}</H1>
		<center>
		<table style='width:300px'>
		<tr>
			<td>" . Paragraphe('blog-pref-b.png','{blog_settings}','{blog_settings_text}','new:'.$dotclear->DotClearExternalAdminUri)."</td>
		</tr>
		<tr>
			<td>" . Paragraphe('index.php.png','{blog_access}','{blog_access_text}','new:'.$dotclear->DotClearExternalWebUri."/{$_SESSION["uid"]}/index.php?")."</td>
		</tr>
		</table>
		</center>
		
		
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}


?>