<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.rtmm.tools.inc');

if(isset($_GET["popup-index"])){index();exit;}
if(isset($_GET["showevents"])){showevents();exit;}
if(isset($_GET["SendQuery"])){SendQuery();exit;}


js();



function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{RTMMail}',"RTMMailConfig.php");
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		if(!$users->AsOrgAdmin){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
		}
	}

$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var stopm='$stop_monitor';
var startm='$start_monitor';

function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(!RTMMailOpen()){return false;}
	
	if ({$prefix}tant < 10 ) {                           
	{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",2000);
      } else {
		{$prefix}tant = 0;
		{$prefix}ChargeLogs();
		{$prefix}demarre(); 
		                              
   }
}

function {$prefix}Loadpage(){
	RTMMail('750','$page?popup-index=yes','$title');
	setTimeout(\"{$prefix}STartALL()\",900);

	}
	
	function {$prefix}STartALL(){	
		{$prefix}demarre();
		{$prefix}ChargeLogs();
	}
	
var x_{$prefix}ChargeLogs= function (obj) {
	var results=obj.responseText;
	document.getElementById('RTMMail_events').innerHTML=results;
	}
		
function {$prefix}ChargeLogs(){
	    var query=document.getElementById('query').value;
	    if(query.length>0){return;}
		var XHR = new XHRConnection();
		XHR.appendData('showevents','yes');
		XHR.sendAndLoad('$page', 'GET', x_{$prefix}ChargeLogs);				
	}	

function RTMMQuery(e){
if(checkEnter(e)){
		RTMMQuerySend();
	}
}

var X_RTMMQuerySend= function (obj) {
	var results=obj.responseText;
	document.getElementById('RTMMail_events').innerHTML=results;
	}

function RTMMQuerySend(){
	var XHR = new XHRConnection();
	var query=document.getElementById('query').value;
	XHR.appendData('SendQuery',query);
	document.getElementById('RTMMail_events').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET', X_RTMMQuerySend);		
}

function RefreshCache(){
	LoadAjax('RTMMail_events','$page?showevents=yes&kill-cache=yes');
}
		

	

	
 {$prefix}Loadpage();
";
	
	echo $html;
}

function index(){
	
$html="
	<H1>{RTMMail}</H1>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend>{find}:</td>
		<td valign='top'>". Field_text('query',null,'width:100%',null,null,null,false,"RTMMQuery(event);")."</td>
		<td valign='top' width=1%>" . imgtootltip('20-refresh.png',"{refresh} cache","RefreshCache()")."</td>
	</tr>
	</table>
	<br>
	".RoundedLightWhite("
	<div id='RTMMail_events' style='with:100%;height:350px;overflow:auto'><center><img src=img/wait_verybig.gif></center></div>");	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"RTMMailConfig.php");
	
}

function showevents(){
	
	if(!isset($_GET["kill-cache"])){unset($_SESSION["RTTM"]["M"]);}
	
	$users=new usersMenus();
	if($users->AsPostfixAdministrator==false){
		if($users->AsOrgAdmin){
			ShoweventsORG();
		}
		return;
	}
	
	$html=file_get_contents("ressources/logs/last-100-mails.html");
	$tpl=new templates();
	$html="<table style='width:100%'>$html</table>";
	
	echo  $tpl->_ENGINE_parse_body($html);	
	
}


function SendQuery(){
	
	$users=new usersMenus();
	if($users->AsPostfixAdministrator==false){
		if($users->AsOrgAdmin){
			ShoweventsORG();
		}
		return;
	}	
	
	$pattern=$_GET["SendQuery"];
	$pattern=str_replace('*','%',$pattern);
	if(strpos("   $pattern",'%')==0){$pattern=$pattern."%";}
if($_SESSION["RTTM"]["M"][md5($pattern)]<>null){echo $_SESSION["RTTM"]["M"][md5($pattern)];return ;}
	
	$q=new mysql();
	$sql="SELECT * FROM smtp_logs WHERE (delivery_user LIKE '$pattern') OR (sender_user LIKE '$pattern') ORDER BY time_stamp DESC limit 0,100";

	$results=$q->QUERY_SQL($sql,"artica_events");
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$tr=$tr.format_line($ligne);
		}	
	
	$tpl=new templates();
	
	$html=$tpl->_parse_body("<table style='width:99%'>$tr</table>");
	$_SESSION["RTTM"]["M"][md5($pattern)]=$html;
	echo $html;
	
	
}

function ShoweventsORG(){
	$ldap=new clladp();
	$pattern=$_GET["SendQuery"];
	$pattern=str_replace('*','%',$pattern);
	if(strpos("   $pattern",'%')==0){$pattern=$pattern."%";}	
	if($_SESSION["RTTM"]["M"][md5($pattern)]<>null){echo $_SESSION["RTTM"]["M"][md5($pattern)];return ;}
	
	$hash=$ldap->hash_get_domains_ou($_SESSION["ou"]);
	if(!is_array($hash)){return null;}
	while (list ($num, $ligne) = each ($hash) ){
		$sender_domain[]="(sender_domain='$num')";
		$sender_domain[]="(delivery_domain='$num')";
		}
	
	$or=implode(" OR ",$sender_domain);
	
	$first="((delivery_user LIKE '$pattern') OR (sender_user LIKE '$pattern'))";
	
	$sql="SELECT * FROM smtp_logs WHERE $first AND ($or) ORDER BY time_stamp DESC limit 0,100";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_events');
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$tr=$tr.format_line($ligne);
		}	
	
	$tpl=new templates();
	$html=$tpl->_parse_body("<table style='width:99%'>$tr</table>");
	$_SESSION["RTTM"]["M"][md5($pattern)]=$html;
	echo $html;
	
}


?>