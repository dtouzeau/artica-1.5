<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	if(isset($_GET["username"])){ChangeMysqlPassword();exit;}
	if(isset($_GET["viewlogs"])){viewlogs();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	js();
	
function js(){
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){echo "alert('no privileges');";exit;}	
if(GET_CACHED(__FILE__,__FUNCTION__)){return;}

$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{MYSQL_PASSWORD_USER}','mysql.index.php');
$page=CurrentPageName();
$prefix=str_replace('.','_',$page);
$html="
var {$prefix}timerID  = null;
var {$prefix}timerID1  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;

function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=5-{$prefix}tant;
	if(!YahooWin3Open()){return false;}
	if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",3000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ChargeLogs();
		{$prefix}demarre();                                //la boucle demarre !
   }
}


	function {$prefix}STart(){
		YahooWin3(500,'$page?popup=yes','$title');
		}
		
var x_ChangeMysqlPassword= function (obj) {
		var results=obj.responseText;
		if(results.length>0){document.getElementById('mysqldivForLogs').innerHTML=results;return}
		{$prefix}demarre();
	}	

function {$prefix}ChargeLogs(){

	LoadAjax('mysqldivForLogs','$page?viewlogs=yes');
}
		
	function ChangeMysqlPassword(){
		var username=document.getElementById('username').value;
		var password=document.getElementById('password').value;
		var XHR = new XHRConnection();	
		XHR.appendData('username',username);
		XHR.appendData('password',password);
		AnimateDiv('mysqldivForLogs');
		XHR.sendAndLoad('$page', 'GET',x_ChangeMysqlPassword);			
	
	}
	
	
	
	{$prefix}STart();
	
";
SET_CACHED(__FILE__,__FUNCTION__,null,$html);	
echo $html;
	
	
}

function popup(){
	if(GET_CACHED(__FILE__,__FUNCTION__)){return;}
	$html="
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/change-mysql-128.png'>
		</td>
		<td valign='top'><div class=explain>{MYSQL_PASSWORD_USER_TEXT}</div>
			<table style='width:99.5%' class=form>
				<tr>
					<td valign='top' class=legend nowrap>{username}:</td>
					<td valign='top'>". Field_text('username',null,"font-size:14px;padding:3px")."</td>
				</tr>
				<tr>
					<td valign='top' class=legend>{password}:</td>
					<td valign='top'>". Field_password('password',null,"font-size:14px;padding:3px;width:120px")."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'>
						<hr>". button("{change}","ChangeMysqlPassword()")."
						
					</td>
				</tr>
			</table>		
		</td>
	</tr>
	</table><hr>
	<div id='mysqldivForLogs' style='height:250px;overflow:auto'></div>
	";
	
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html,"mysql.index.php");
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
	
}

function ChangeMysqlPassword(){
	$tpl=new templates();
	$localserver=false;
	
	$users=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('<strong style=color:red>{ERROR_NO_PRIVS}</strong>');exit;	}
	
	$q=new mysql();
	if($q->mysql_server=="localhost"){$localserver=true;}
	if($q->mysql_server=="127.0.0.1"){$localserver=true;}
	if(!$localserver){echo $tpl->_ENGINE_parse_body('<strong style=color:red>{ERR_MYSQL_IS_REMOTE}</strong>');exit;}

	
	if($_GET["username"]==null){echo $tpl->_ENGINE_parse_body('<strong style=color:red>{ERR_NO_USERNAME}</strong>');exit;}
	if($_GET["password"]==null){echo $tpl->_ENGINE_parse_body('<strong style=color:red>{ERR_NO_PASS}</strong>');exit;}
	$tpl=new templates();
	$sock=new sockets();
	
	$sock->getFrameWork("cmd.php?ChangeMysqlLocalRoot={$_GET["username"]}&password={$_GET["password"]}");
}

function viewlogs(){
	$tpl=new templates();

if(!is_file("ressources/logs/ChangeMysqlLocalRoot")){echo $tpl->_ENGINE_parse_body('{waiting}...');exit;}
	$tbl=explode("\n",@file_get_contents("ressources/logs/ChangeMysqlLocalRoot"));
	echo "<div style='background-color:white'>";
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		echo "<div><code style='font-size:11px'>".htmlspecialchars($tpl->_ENGINE_parse_body($ligne))."</code></div>\n";
		
	}
	
	echo "</div>";
	
}


?>