<?php

if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.syslogs.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');
include_once('ressources/class.mysql.inc');

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_POST["username"])){ChangeMysqlRoot();exit;}
if(isset($_GET["events"])){ChangeMysqlRoot();exit;}
js();



function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{mysql_error}");
	$error=urlencode($_GET["error"]);
	$html="YahooWin5('550','$page?popup=yes&error=$error','$title');";
	echo $html;
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$error=base64_decode($_GET["error"]);
	$q=new mysql();
	$html="<div class=explain>
		{mysql_error_popup_credential_text}
		<br><code style='font-size:13px'>$error</div>
	<center>
			<table style='width:80%' class=form>
				<tr>
					<td valign='top' class=legend nowrap>{username}:</td>
					<td valign='top'>". Field_text('username',$q->mysql_admin,"font-size:14px;padding:3px")."</td>
				</tr>
				<tr>
					<td valign='top' class=legend>{password}:</td>
					<td valign='top'>". Field_password('password',$q->mysql_password,"font-size:14px;padding:3px;width:120px")."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'>
						<hr>". button("{change}","UploadMysqlPassword()")."
						
					</td>
				</tr>
			</table>	
		</center>
		<div id='mysqldivForLogs'></div>
	<script>
	
	var x_UploadMysqlPassword= function (obj) {
		LoadAjax('mysqldivForLogs','$page?events=yes');
	}	
	
	function UploadMysqlPassword(){
	
		var username=document.getElementById('username').value;
		var password=document.getElementById('password').value;
		var XHR = new XHRConnection();	
		XHR.appendData('username',username);
		XHR.appendData('password',password);
		AnimateDiv('mysqldivForLogs');
		XHR.sendAndLoad('$page', 'POST',x_UploadMysqlPassword);			
	
	}
</script>	
	";
	
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}


function ChangeMysqlRoot(){
	$page=CurrentPageName();
	$tpl=new templates();		
	unset($_SESSION["MYSQL_PARAMETERS"]);
	unset($GLOBALS["MYSQL_PARAMETERS"]);	
	if(isset($_POST["username"])){
		$username=urlencode(base64_encode($_POST["username"]));
		$password=urlencode(base64_encode($_POST["password"]));
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?ChangeMysqlLocalRoot2=yes&username=$username&password=$password");
		$sock->SET_INFO("ChangeMysqlRootPerformed", 1);
	}
	
	$q=new mysql();
	$img="ok42.png";
	$error_text="{success}";
	$sql="SELECT count(*) FROM admin_cnx";
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		$img="42-red.png";
		$error_text="<code style='font-size:13px'>$q->mysql_error</code><p><code style='font-size:13px'>{username}:$q->mysql_admin<br>Password:$q->mysql_password</code></p>";
	}		
			
			
		
	
	
		
	$html="
	<center style='margin:10px'>
	<table style='width:80%' class=form>
	<tr>
		<td width=1%><img src='img/$img'></td>
		<td width=99%><code style='font-size:13px'>$error_text</code></td>
	</tr>
	</table>
	</center>
	<div style='width:100%;height:250px;overflow:auto'>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		
		<th>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>	
	
	";
	if(is_file("ressources/logs/web/ChangeMysqlLocalRoot2.log")){
		$datas=explode("\n", @file_get_contents("ressources/logs/web/ChangeMysqlLocalRoot2.log"));
	}
	krsort($datas);
	
while (list ($num, $val) = each ($datas) ){	
	if(trim($val)==null){continue;}
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
	<tr class=$classtr>
	<td style='font-size:12px'>$val</td>
	</tr>
	
	";
}
	
echo $tpl->_ENGINE_parse_body($html)."</div>

<script>
	function refreshChangeMysqlLocalRoot2(){
		LoadAjax('mysqldivForLogs','$page?events=yes');
		
	}
	setTimeout('refreshChangeMysqlLocalRoot2()',6500);
	
</script>

";

	
	
	
}

