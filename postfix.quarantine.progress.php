<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	include_once('ressources/class.milter.greylist.inc');					
	
	

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["progression"])){echo progression_quarantine();exit;}


js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
	$prefix="QuarantineProgress";
	$title=$tpl->_ENGINE_parse_body('{quarantine_process_progress}',"postfix.index.php");
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;


function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(!YahooWin5Open()){return false;}
	if ({$prefix}tant < 10 ) {                           
	{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
      } else {
			{$prefix}tant = 0;
			{$prefix}ChargeLogs();
			{$prefix}demarre();                                
   }
}	
	
	
	function StartQuarantineProgress(){
		YahooWin5(500,'$page?popup=yes','$title');
		setTimeout('QuarantineProgress()',1000);
	}
	
	function QuarantineProgress(){
		{$prefix}demarre();
		{$prefix}ChargeLogs();
	}
	
var x_{$prefix}ChargeLogs= function (obj) {
	var tempvalue=obj.responseText;
	document.getElementById('progress_quarantine').innerHTML=tempvalue;
}		
	
	function {$prefix}ChargeLogs(){
		var XHR = new XHRConnection();
		XHR.appendData('progression','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs);
	
	}
	

	
	StartQuarantineProgress();
	";
	echo $html;
	}
	
	
function popup(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	
	$res=progression_quarantine();

	$html="<H1>{quarantine_process_progress}</H1>
	<p class=caption>{quarantine_process_progress_text}</p>
	<div id='progress_quarantine'>
			$res
	</div>
";
	
	echo $tpl->_ENGINE_parse_body($html,"postfix.index.php");
}
function Status($pourc){
$color="#5DD13D";	
$html="
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
";	


return $html;
	
}
function progression_quarantine(){
	
	
if(is_file("/usr/share/artica-postfix/ressources/logs/mailarchive-quarantine-progress.ini")){
	$file="/usr/share/artica-postfix/ressources/logs/mailarchive-quarantine-progress.ini";	
}else{
	if(is_file("/usr/share/artica-postfix/ressources/mailarchive-quarantine-progress.ini")){
		$file="/usr/share/artica-postfix/ressources/mailarchive-quarantine-progress.ini";	
	}
}

if($file==null){
	$total=0;
	$current=0;
	$pourc=100;
}
$ini=new Bs_IniHandler($file);
$tot=$ini->get("PROGRESS","total");
$cur=$ini->get("PROGRESS","current");
$pid=$ini->get("PROGRESS","pid");
$quarantine_type=$ini->get("PROGRESS","quarantine");
if($tot>0){
	$pourc=round($cur/$tot,2);
	$pourc=$pourc*100;
	$img="<img src='img/wait.gif'>";
}else{
	$pourc=100;
}
if($pourc==100){$img="&nbsp;";}

$color="#5DD13D";
	
$html="<table style='width:100%'>
	<tr>
		<td align='center' colspan=2><strong style='font-size:13px'>&laquo;$quarantine_type&raquo;&nbsp;$cur/$tot (pid:$pid) </td>
	</tr>
		
	<tr>
		<td width=1%><span id='wait'>$img</span>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_postfix'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
						</div>
					</div>
				</div>
			</td>
			</tr>
			</table>		
		</td>
	</tr>
	</table>";	
return $html;
	
}



?>