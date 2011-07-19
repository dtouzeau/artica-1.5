<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.computers.inc');

	
		$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
		
	}

	
if(isset($_GET["popup-index"])){index();exit;}
if(isset($_GET["scan-parameters"])){scan_parameters();exit;}
if(isset($_GET["SelfExtArchives"])){save_scan_parameters();exit;}
if(isset($_GET["credentials"])){Credentials();exit;}
if(isset($_GET["expcure"])){expcure();exit;}
if(isset($_GET["PerformScanNow"])){PerformScanNow();exit;}
if(isset($_GET["ComputerScanSchedule"])){save_schedule_parameters();exit;}
js();


function save_schedule_parameters(){
	
	$uid=$_GET["ComputerScanSchedule"];
	if(strpos($uid,'$')==0){$uid=$uid.'$';};
	$cron=$_GET["cron"];
	$comp=new computers($uid);
	$comp->Edit_ScanSchedule($cron);
	}

function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{AV_REMOTE_SCAN}',"domains.edit.user.php");
$AV_SCAN_PARAMETERS=$tpl->_ENGINE_parse_body("{AV_SCAN_PARAMETERS}");
$shares_access=$tpl->_ENGINE_parse_body("{shares_access}");
	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
		
	}
	
$uid=$_GET["uid"];

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

function ComputerScanParameters(){
	YahooWin3('400','$page?scan-parameters=$uid','$AV_SCAN_PARAMETERS');
}

function ComputerScanCredentials(){
	YahooWin3('500','$page?credentials=$uid','$shares_access');
}

function {$prefix}Loadpage(){
	RTMMail('650','$page?popup-index=yes&uid=$uid','$title');
	//setTimeout(\"{$prefix}STartALL()\",900);

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
  	if(results.length>0){alert(results);}
	document.getElementById('RTMMail_events').innerHTML=results;
	}

function RTMMQuerySend(){
	var XHR = new XHRConnection();
	var query=document.getElementById('query').value;
	XHR.appendData('SendQuery',query);
	document.getElementById('RTMMail_events').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET', X_RTMMQuerySend);		
}

var X_ChangeCureExplain= function (obj) {
	var results=obj.responseText;
	document.getElementById('curexpl').innerHTML=results;
	}

function ChangeCureExplain(){
	var cure=document.getElementById('cure').value;
	var XHR = new XHRConnection();
	XHR.appendData('expcure',cure);
	XHR.sendAndLoad('$page', 'GET', X_ChangeCureExplain);		
	}
	
var X_ComputerPerformScan= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	 {$prefix}Loadpage();
	}	
	
	
function ComputerPerformScan(){
	var XHR = new XHRConnection();
	XHR.appendData('PerformScanNow','{$_GET["uid"]}');	
	document.getElementById('frontendsettingsAV').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
    XHR.sendAndLoad('$page', 'GET', X_ComputerPerformScan);	

}

var X_SaveComputerScannerOptions= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	ComputerScanParameters();
	}
	
function SaveScanSchedule(cron){
	document.getElementById('ComputerScanSchedule').value=cron;
	var XHR = new XHRConnection();
	XHR.appendData('ComputerScanSchedule','{$_GET["uid"]}');
	XHR.appendData('cron',cron);	
	XHR.sendAndLoad('$page', 'GET',X_ComputerPerformScan);	
}
	
function SaveComputerScannerOptions(){
	var Packed=document.getElementById('Packed').value;
	var Archives=document.getElementById('Archives').value;
	var SelfExtArchives=document.getElementById('SelfExtArchives').value;
	var MailBases=document.getElementById('MailBases').value;
	var MailPlain=document.getElementById('MailPlain').value;	
	var Heuristic=document.getElementById('Heuristic').value;
	var Recursion=document.getElementById('Recursion').value;
	var cure=document.getElementById('cure').value;
	var XHR = new XHRConnection();
	XHR.appendData('Packed',Packed);	
	XHR.appendData('Archives',Archives);
	XHR.appendData('SelfExtArchives',SelfExtArchives);	
	XHR.appendData('MailBases',MailBases);	
	XHR.appendData('MailPlain',MailPlain);	
	XHR.appendData('Heuristic',Heuristic);	
	XHR.appendData('Recursion',Recursion);
	XHR.appendData('cure',cure);
	XHR.appendData('uid','{$_GET["uid"]}');		
	document.getElementById('AV_SCAN_PARAMETERS').innerHTML='<center><img src=img/wait_verybig.gif></center>';	
    XHR.sendAndLoad('$page', 'GET', X_SaveComputerScannerOptions);		
}

function RefreshCache(){
	LoadAjax('RTMMail_events','$page?showevents=yes&kill-cache=yes');
}
 {$prefix}Loadpage();
";
	
	echo $html;
}

function scan_parameters(){
	$uid=$_GET["scan-parameters"];
	$computer=new computers($uid.'$');
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->KasperkyAVScanningDatas);
	if($ini->_params["scanner.options"]["cure"]==null){$ini->_params["scanner.options"]["cure"]=0;}
	for($i=0;$i<5;$i++){
		$hash[$i]="{f$i}";
	}
	
	$cure=Field_array_Hash($hash,"cure",$ini->_params["scanner.options"]["cure"],"ChangeCureExplain()");
	
	$form="
	<div style='font-size:12px' id='curexpl'>{i{$ini->_params["scanner.options"]["cure"]}}</div>
	<hr>
	<table style='width:100%'>
	<tr>
		<td class=legend>{SCAN_PACKED}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('Packed',$ini->_params["scanner.options"]["Packed"])."</td>
	</tr>
	<tr>
		<td class=legend>{SCAN_ARCHIVES}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('Archives',$ini->_params["scanner.options"]["Archives"])."</td>
	</tr>	
	<tr>
		<td class=legend>{SelfExtArchives}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('SelfExtArchives',$ini->_params["scanner.options"]["SelfExtArchives"])."</td>
	</tr>
	<tr>
		<td class=legend>{MailBases}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('MailBases',$ini->_params["scanner.options"]["MailBases"])."</td>
	</tr>	
	<tr>
		<td class=legend>{MailPlain}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('MailPlain',$ini->_params["scanner.options"]["MailPlain"])."</td>
	</tr>
	<tr>
		<td class=legend>{Heuristic}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('Heuristic',$ini->_params["scanner.options"]["Heuristic"])."</td>
	</tr>
	<tr>
		<td class=legend>{Recursion}:</td>
		<td valign='top'>" . Field_numeric_checkbox_img('Recursion',$ini->_params["scanner.options"]["Recursion"])."</td>
	</tr>
	<tr>
		<td class=legend>{cure}:</td>
		<td valign='top'>$cure</td>
	</tr>			
	<tr>
	
	
	<td colspan=2 align='right'>
	<hr>
	<input type='button' OnClick=\"javascript:SaveComputerScannerOptions();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	
";
	
$form=RoundedLightWhite($form);	
	
$html="<H1>{AV_SCAN_PARAMETERS}</H1>
<br>
<div id='AV_SCAN_PARAMETERS'>
$form
</div>

";		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function index(){
	$uid=$_GET["uid"];
	if(strpos($uid,'$')==0){$uid=$uid.'$';};
	$computer=new computers($uid);
	
	
	
//KasperkyAVScanningDatas	
	//https://dtouzeau-laptop:9000/img/clock-24.png
	$form="
	<input type='hidden' value='$computer->ComputerScanSchedule' id='ComputerScanSchedule'>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>" . Paragraphe('64-computers-parameters.png','{AV_SCAN_PARAMETERS}','{AV_SCAN_PARAMETERS_TEXT}',"javascript:ComputerScanParameters();")."</td>
		<td valign='top'>" . Paragraphe('64-credentials.png','{shares_access}','{shares_access_text}',"javascript:Loadjs('computer.passwd.php?uid=$uid');")."</td>
	</tr>
	<tr>
		<td valign='top'>" . Paragraphe('64-virus-find.png','{START_SCAN_COMPUTER}','{START_SCAN_COMPUTER_TEXT}',"javascript:ComputerPerformScan();")."</td>
		<td valign='top'>" . Paragraphe('time-64.png','{SCHEDULE_SCAN_COMPUTER}','{SCHEDULE_SCAN_COMPUTER_TEXT}',
		"javascript:Loadjs('cron.php?function=SaveScanSchedule&field=ComputerScanSchedule')")."</td>
	</table>
	";
	
$html="
	<H1>$uid:: {AV_REMOTE_SCAN}</H1>
	<div id='frontendsettingsAV'>
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/128-virus.png'></td>
		<td valign='top'>
		". RoundedLightWhite($form)."
		</td>
	</tr>
	</table>
	</div>
	";	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"domains.edit.user.php");
	
}
function expcure(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{i{$_GET["expcure"]}}");
}

function save_scan_parameters(){
	
	$uid=$_GET["uid"];
	$computer=new computers($uid.'$');
	$ini=new Bs_IniHandler();	
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($_GET) ){
		$ini->_params["scanner.options"][$num]=$ligne;
	}
	
	$datas=$ini->toString();
	$computer->KasperkyAVScanningDatas=$datas;
	if($computer->SaveScannerOptions()){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}else{
		echo $tpl->_ENGINE_parse_body('{failed}');
	}
	
}

function Credentials(){
	$computer=new computers($_GET["credentials"].'$');
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	$username=$ini->_params["ACCOUNT"]["USERNAME"];
	$password=$ini->_params["ACCOUNT"]["PASSWORD"];	
	$from="	
	<table style='width:100%'>
	<tr>
	<td colspan=2 valign='top'><img src='img/128-credentials.png'></td>
	<td valign='top'>
	<p class=caption>{shares_access_text}</p>
	". RoundedLightWhite("
	<table style='width:100%'>
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text('username',$username,'width:120px')."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password('password',$password,'width:120px')."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><hr>
	<input type='button' OnClick=\"javascript:SaveCredentialComputer();\" value='{edit}&nbsp;&raquo;'>
	</td>
	</tr>		
	</table>")."
	</td>
	</table>";
		
	$html="<H1>{shares_access}</H1>
	$from";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function PerformScanNow(){
	$sock=new sockets();
	$sock->getfile("ComputerScanForViruses:{$_GET["PerformScanNow"]}");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{SUCCESS_LAUNCH_SCAN_PC}");
	
	
}



?>