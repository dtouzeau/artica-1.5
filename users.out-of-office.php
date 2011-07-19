<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	$uid=$_SESSION["uid"];
	if($uid==null){die('alert("Please logon");');}
	
	if(isset($_GET["start-page"])){echo out_of_office_start();exit;}
	if(isset($_GET["vacationActive"])){SaveVacation();exit;}
	if(isset($_GET["vacationInfo"])){SavevacationInfo();exit;}
	if(isset($_GET["start-message"])){echo out_of_office_message();exit;}
	
js();



function js(){
		$uid=$_SESSION["uid"];
	$tpl=new templates();


	
	$title=$tpl->_ENGINE_parse_body('{OUT_OF_OFFICE}');
	$vacationmsg=$tpl->_ENGINE_parse_body('{vacation_message}');
	$page=CurrentPageName();
	$html="
		YAHOO.namespace(\"example.calendar\");
		YAHOO.example.calendar.init = function() {
				function Cal1eSelect(type,args,obj) {
            		var dates = args[0]; 
            		var date = dates[0];
            		var year = date[0], month = date[1], day = date[2];
            		var selectedDate=year+'-'+month+'-'+day;
            		document.getElementById('vacationStart').value=selectedDate;
        			}
        			
				function Cal2eSelect(type,args,obj) {
            		var dates = args[0]; 
            		var date = dates[0];
            		var year = date[0], month = date[1], day = date[2];
            		var selectedDate=year+'-'+month+'-'+day;
            		document.getElementById('vacationEnd').value=selectedDate;
        			}        			
        			
				YAHOO.example.calendar.cal1 = new YAHOO.widget.Calendar(\"cal1\",\"cal1Container\");
				YAHOO.example.calendar.cal1.selectEvent.subscribe(Cal1eSelect, YAHOO.example.calendar.cal1, true);
				YAHOO.example.calendar.cal1.render();
				
				YAHOO.example.calendar.cal2 = new YAHOO.widget.Calendar(\"cal2\",\"cal2Container\");
				YAHOO.example.calendar.cal2.selectEvent.subscribe(Cal2eSelect, YAHOO.example.calendar.cal2, true);
				YAHOO.example.calendar.cal2.render();				
				
				}
				
				
				
				
				
	function OutOfOfficeLoad(){
		YahooWin3(650,'$page?start-page=yes','$uid:&nbsp;$title');
		setTimeout(\"initCalcs()\",1500);
		}
	
	function initCalcs(){
		YAHOO.util.Event.onDOMReady(YAHOO.example.calendar.init);
		setTimeout(\"UpdateCalcs()\",500);
		}
	
	
	function UpdateCalcs(){
		YAHOO.example.calendar.cal1.select(document.getElementById('vacationStart').value);
		var selectedDates = YAHOO.example.calendar.cal1.getSelectedDates();
        if (selectedDates.length > 0) {
             var firstDate = selectedDates[0];
             YAHOO.example.calendar.cal1.cfg.setProperty(\"pagedate\", (firstDate.getMonth()+1) + \"/\" + firstDate.getFullYear());
             YAHOO.example.calendar.cal1.render();
             }		
		
		YAHOO.example.calendar.cal2.select(document.getElementById('vacationEnd').value);
		selectedDates = YAHOO.example.calendar.cal2.getSelectedDates();
        if (selectedDates.length > 0) {
             firstDate = selectedDates[0];
             YAHOO.example.calendar.cal2.cfg.setProperty(\"pagedate\", (firstDate.getMonth()+1) + \"/\" + firstDate.getFullYear());
             YAHOO.example.calendar.cal2.render();
             }
		
		
		
		
	
	}
	
	var x_SaveOutOfOffice= function (obj) {	
		var results=obj.responseText;
			if (results.length>0){
					alert(results);
				}
		OutOfOfficeLoad();
	}
	
	function VacationMessage(){
		YahooWin4(500,'$page?start-message=yes','$uid:&nbsp;$vacationmsg');
	
	}
	
	var x_SaveVacationInfo= function (obj) {	
		var results=obj.responseText;
			if (results.length>0){
					alert(results);
				}
		VacationMessage();
	}	
	
	function SaveVacationInfo(){
		var XHR = new XHRConnection();
		XHR.appendData('vacationInfo',document.getElementById('vacationInfo').value);
		document.getElementById('vacdiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveVacationInfo);	
	}
	 
	
	function SaveOutOfOffice(){
		var XHR = new XHRConnection();
		XHR.appendData('vacationActive',document.getElementById('vacationActive').value);
		XHR.appendData('vacationEnd',document.getElementById('vacationEnd').value);
		XHR.appendData('vacationStart',document.getElementById('vacationStart').value);
		document.getElementById('outofoff').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveOutOfOffice);	
		}
	
	
	OutOfOfficeLoad();
	;
	";
	
echo $html;	
	
}


function out_of_office_start(){
$user=new user($_SESSION["uid"]);	
if($user->vacationEnabled=="TRUE"){$vacation_active=1;}else{$vacation_active=0;}
$vacationStart=$user->vacationStart;
$vacationEnd=$user->vacationEnd;
$vacationStart_time=date('m/d/Y',$vacationStart);
$vacationEnd_time=date('m/d/Y',$vacationEnd);

$activate=Paragraphe_switch_img("{ENABLE_OUT_OF_OFFICE}","{ENABLE_OUT_OF_OFFICE_TEXT}","vacationActive",$vacation_active);
$activate=RoundedLightWhite($activate);


if($user->vacationActive=="TRUE"){$icon="ok24.png";$textActive="{enabled}";}else{$icon="danger24.png";$textActive="{disabled}";}


$status="
<table style='width:100%' class=table_form>
<tr>
	<td colspan=2><strong style='font-size:16px'>{status}</strong></td>
	<hr>
</tr>
<tr>
	<td width=1%><img src='img/$icon'></td>
	<td><strong style='width:12px'>$textActive</td>
	</tr>
</table>";


$form_time="

<table style='width:100%'>
	<tr>
		<td align='left' style='border-bottom:1px solid #CCCCCC'><span style='font-size:14px;font-weight:bold;text-transform: capitalize;'>{from}:</span></td>
		<td align='left' style='border-bottom:1px solid #CCCCCC'><span style='font-size:14px;font-weight:bold;text-transform: capitalize;'>{to}:</span></td>
	</tr>
	<tr>
		<td align='center' style='padding-left:10px;padding-top:5px'><div id='cal1Container'><img src='img/wait.gif'></div></td>
		<td align='center' style='padding-left:10px;padding-top:5px'><div id='cal2Container'><img src='img/wait.gif'></div></td>
	</tr>	
</table>
<input type='hidden' id='vacationStart' value='$vacationStart_time'>
<input type='hidden' id='vacationEnd' value='$vacationEnd_time'>
<hr>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveOutOfOffice();\"></div>
";
$form_time=RoundedLightWhite($form_time);
$form="
<table style='width:100%'>
<tr>
	<td valign='top'>$activate<br>$status
	<hr>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SaveOutOfOffice();\"></div></td>
	<td valign='top'>$form_time</td>
</tr>
</table>
";

	
$html="
<H1>{OUT_OF_OFFICE}</H1>
<div style='float:right'><input type='button' value='{vacation_message}' OnClick=\"javascript:VacationMessage();\" ></div>
<p class=caption>{OUT_OF_OFFICE_TEXT}</p>
<div id='outofoff'>
$form
</div>


";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);

//$timestamp = mktime(0, 0, 0, $month, $day, $year);
//current=time
	
}

function SaveVacation(){
	$tpl=new templates();
	$users=new user($_SESSION["uid"]);
		if(preg_match('#(.+?)@(.+)#',$users->mail,$re)){
			$alias="{$re[1]}@vacation.{$re[2]}";
		}	
	
	if($_GET["vacationActive"]==1){
		$users->vacationEnabled="TRUE";
		$users->VacationCheck();
		if(!$users->AddAliasesMailing($alias)){
			echo $users->ldap_error;
			exit;
		}

	}else{
		$users->delete_AliasesMailing($alias);
		if($users->VacationDisable()){
			echo $tpl->_ENGINE_parse_body("{disable} {success}\n");
			exit;
		}
	}
	
if(preg_match('#([0-9]+)-([0-9]+)-([0-9]+)#',$_GET["vacationStart"],$re)){
		$timestamp = mktime(0, 0, 0, $re[2], $re[3], $re[1]);
		$users->vacationStart=$timestamp;
}

if(preg_match('#([0-9]+)-([0-9]+)-([0-9]+)#',$_GET["vacationEnd"],$re)){
		$timestamp = mktime(0, 0, 0, $re[2], $re[3], $re[1]);
		$users->vacationEnd=$timestamp;
}

if($users->vacationEnd<$users->vacationStart){
	die($tpl->_ENGINE_parse_body('{error_check_dates}'));
}
writelogs("vacationActive=\"$users->vacationActive\"..",__CLASS__.'/'.__FUNCTION__,__FILE__);
if(!$users->add_user()){echo $users->ldap_error;}
$users->VacationCheck();
}

function SavevacationInfo(){
	$info=$_GET["vacationInfo"];
	$users=new user($_SESSION["uid"]);
	$users->vacationInfo=$info;
	if(!$users->add_user()){echo $users->ldap_error;}
	
	
}

function out_of_office_message(){
	$users=new user($_SESSION["uid"]);
$html="
<H1>{vacation_message}</H1>
<div id='vacdiv'>
<textarea 
	name='vacationInfo' 
	id='vacationInfo'
	style='width:100%;padding:10px;height:250px;overflow:hidden;overflow-x: hidden;overflow-y: auto;font-family:\"Courier New\" Courier monospace;font-size:12px'>
$users->vacationInfo
</textarea>
<hr>
<div style='text-align:right'><input type='button' OnClick=\"javascript:SaveVacationInfo()\" value='{edit}&nbsp;&raquo;'></div>
</div>


";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);	
	
	
}



?>