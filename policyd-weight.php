<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.policyd-weight.inc');
	$usersmenus=new usersMenus();
	if($usersmenus->AsPostfixAdministrator==false){die();}
	if(isset($_GET["popup-index"])){popup_index();exit;}
	if(isset($_GET["popup-daemon"])){popup_daemon();exit;}
	if(isset($_GET["popup-notifs"])){popup_notifs();exit;}
	if(isset($_GET["popup-dnsbl"])){popup_dnsbl();exit;}
	if(isset($_GET["MAX_PROC"])){SaveSettings();exit;}
	if(isset($_GET["MAXDNSBLHITS"])){SaveSettings();exit;}
	if(isset($_GET["REJECTMSG"])){SaveSettingsFormatted();exit;}
	if(isset($_GET["list-dnsbl"])){echo dnsbl_list();exit;}
	if(isset($_GET["DNSBL"])){SaveDNSBL();exit;}
	if(isset($_GET["RHSBL"])){SaveRHSBL();exit;}
	if(isset($_GET["DEL_DNSBL"])){DelDNSBL();exit;}
	if(isset($_GET["DEL_RHSBL"])){DelRHSBL();exit;}
	if(isset($_GET["EnablePolicydWeight"])){EnablePolicydWeight();exit;}
	
	
	
	
	js();


function js(){
$page=CurrentPageName();
$prefix=str_replace('.','_',$page);
$prefix=str_replace('-','_',$prefix);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_POLICYD_WEIGHT}');

$html="
function {$prefix}Loadpage(){
	YahooWin2('850','$page?popup-index=yes','$title');
	//setTimeout('{$prefix}DisplayDivs()',900);
	}
	
function PolicydDaemonSettings(){
	YahooWin3('650','$page?popup-daemon=yes','$title');
}

function PolicydDaemonNotifs(){
	YahooWin3('650','$page?popup-notifs=yes','$title');
}

function PolicydDaemonDNSBL(){
	YahooWin3('650','$page?popup-dnsbl=yes','$title');
}

var X_EnablePolicydWeight=function (obj) {
	{$prefix}Loadpage();
	}

var x_ffmpolicy1= function (obj) {
	var results=obj.responseText;
	alert(results);
	PolicydDaemonSettings();
	}
	
var x_ffmpolicy2= function (obj) {
	var results=obj.responseText;
	alert(results);
	PolicydDaemonNotifs();
	}

var x_ffmpolicy3= function (obj) {
	var results=obj.responseText;
	alert(results);
	PolicydDaemonDNSBL();
	}

var X_PolicyDSaveDnsbl= function (obj) {
	var results=obj.responseText;
	alert(results);
	LoadAjax('dnsbllistpolicd','$page?list-dnsbl=yes');
	}	
	
function PolicyDDelDnsbl(md){
		 var DNSBL=document.getElementById(md+'_DNSBL').value;
		 var XHR = new XHRConnection();
		 XHR.appendData('DEL_DNSBL',DNSBL);
		 document.getElementById('dnsbllistpolicd').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		 XHR.sendAndLoad('$page', 'GET',X_PolicyDSaveDnsbl);
}
function PolicyDDelRHSBL(md){
		 var DNSBL=document.getElementById(md+'_RHSBL').value;
		 var XHR = new XHRConnection();
		 XHR.appendData('DEL_RHSBL',DNSBL);
		 document.getElementById('dnsbllistpolicd').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		 XHR.sendAndLoad('$page', 'GET',X_PolicyDSaveDnsbl);
}

function EnablePolicydWeight(){
	var XHR = new XHRConnection();
	XHR.appendData('EnablePolicydWeight',document.getElementById('EnablePolicydWeight').value);
	document.getElementById('enableplicyweightdiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',X_EnablePolicydWeight);

}



	
function PolicyDSaveDnsbl(md,e){
	var r=false;
	if(e==13){r=true;}
	if(!r){
		if(checkEnter(e)){r=true;}
	}

	if(r){
		 var DNSBL=document.getElementById(md+'_DNSBL').value;
		 var HIT=document.getElementById(md+'_HIT').value;
		 var MISS=document.getElementById(md+'_MISS').value;
		 var XHR = new XHRConnection();
		 XHR.appendData('DNSBL',DNSBL);
		 XHR.appendData('HIT',HIT);
		 XHR.appendData('MISS',MISS);
		 document.getElementById('dnsbllistpolicd').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		 XHR.sendAndLoad('$page', 'GET',X_PolicyDSaveDnsbl);
	}		

}

function PolicyDSaveRHSBL(md,e){
	var r=false;
	if(e==13){r=true;}
	if(!r){
		if(checkEnter(e)){r=true;}
	}

	if(r){
		 var DNSBL=document.getElementById(md+'_RHSBL').value;
		 var HIT=document.getElementById(md+'_HIT').value;
		 var MISS=document.getElementById(md+'_MISS').value;
		 var XHR = new XHRConnection();
		 XHR.appendData('RHSBL',DNSBL);
		 XHR.appendData('HIT',HIT);
		 XHR.appendData('MISS',MISS);
		 document.getElementById('dnsbllistpolicd').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		 XHR.sendAndLoad('$page', 'GET',X_PolicyDSaveDnsbl);
	}		

}




function {$prefix}DisplayDivs(){
		LoadAjax('main_config_postfix','$page?main={$_GET["main"]}&hostname=$hostname')
		{$prefix}demarre();
		{$prefix}ChargeLogs();
		{$prefix}StatusBar();
	}	
	
 {$prefix}Loadpage();
";
	
	echo $html;
}	

function popup_index(){
	
	$sock=new sockets();
	$EnablePolicydWeight=$sock->GET_INFO('EnablePolicydWeight');
	
	$EnablePolicydWeight_field=Paragraphe_switch_img('{EnablePolicydWeight}','{APP_POLICYD_WEIGHT_EXPLAIN}','EnablePolicydWeight',$EnablePolicydWeight,"{enable_disable}",300);
	//64-cop-acls.png
	//64-cop-rules.png
	
	$demons_settings=Paragraphe("64-cop-rules.png","{daemon_settings}","{daemon_settings_text}","javascript:PolicydDaemonSettings()");
	$notif=Paragraphe("64-cop-acls-infos.png","{APP_POLICYD_WEIGHT} {notifications}","{PolicydDaemonNotifs}","javascript:PolicydDaemonNotifs()");
	$dnsbl=Paragraphe("64-cop-acls-dnsbl.png","{DNSBL_settings}","{DNSBL_settings_text}","javascript:PolicydDaemonDNSBL()");
	$instant=Buildicon64("DEF_ICO_MAIL_IPABLES");
	
	$panel="			<table style='width:100%'>
				<tr>
				<td valign='top'>$demons_settings$notif$dnsbl</td>
				<td valign='top'>$instant</td>
				</tr>
			</table>";
	
	$panel=RoundedLightWhite($panel);
	$html="<H1>{APP_POLICYD_WEIGHT}</H1>
	<p class=caption>{APP_POLICYD_WEIGHT_TEXT}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='enableplicyweightdiv'>
			$EnablePolicydWeight_field
			</div>
			<hr>
			<div style='text-align:right'><input type='button' OnClick=\"javascript:EnablePolicydWeight();\" value='{edit}&nbsp;&raquo;'></div>
		</td>
		<td valign='top'>
			$panel

		</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function popup_daemon(){
	
$policy=new policydweight();
$page=CurrentPageName();
$form="
<div id='ffmpolicy1Div'>
<form name='ffmpolicy1'>
<table style='width:100%'>
<tr>
	<td class=legend>{MAX_PROC}:</td>
	<td>" . Field_text('MAX_PROC',$policy->main_array["MAX_PROC"],'width:30px')."</td>
</tr>
<tr>
	<td class=legend>{MIN_PROC}:</td>
	<td>" . Field_text('MIN_PROC',$policy->main_array["MIN_PROC"],'width:30px')."</td>
</tr>
<tr>
	<td class=legend>{SOMAXCONN}:</td>
	<td>" . Field_text('SOMAXCONN',$policy->main_array["SOMAXCONN"],'width:30px')."</td>
</tr>
<tr>
	<td class=legend>{MAXIDLECACHE}:</td>
	<td>" . Field_text('MAXIDLECACHE',$policy->main_array["MAXIDLECACHE"],'width:30px')."</td>
</tr>
<tr>
	<td class=legend>{MAINTENANCE_LEVEL}:</td>
	<td>" . Field_text('MAINTENANCE_LEVEL',$policy->main_array["MAINTENANCE_LEVEL"],'width:30px')."</td>
</tr>
<tr>
	<td colspan=2 align='right'>
		<hr>
		<input type='button' OnClick=\"javascript:ParseForm('ffmpolicy1','$page',false,false,false,'ffmpolicy1Div',null,x_ffmpolicy1);\" value='{edit}&nbsp;&raquo;'>
		
	</td>
</tr>
</table>
</div>
";
$form=RoundedLightWhite($form);	
$html="<H1>{daemon_settings}</H1>
	$form";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_notifs(){
$policy=new policydweight();
$page=CurrentPageName();
$form="
<div id='ffmpolicy2Div'>
<form name='ffmpolicy2'>
<table style='width:100%'>

<tr>
	<td class=legend>{REJECTMSG}:</td>
	<td>" . Field_text('REJECTMSG',$policy->main_array["REJECTMSG"],'width:330px')."</td>
</tr>
<tr>
	<td class=legend>{DNSERRMSG}:</td>
	<td>" . Field_text('DNSERRMSG',$policy->main_array["DNSERRMSG"],'width:330px')."</td>
</tr>
<tr>
	<td class=legend>{MAXDNSERRMSG}:</td>
	<td>" . Field_text('MAXDNSERRMSG',$policy->main_array["MAXDNSERRMSG"],'width:330px')."</td>
</tr>
	<td class=legend>{MAXDNSBLMSG}:</td>
	<td>" . Field_text('MAXDNSBLMSG',$policy->main_array["MAXDNSBLMSG"],'width:330px')."</td>
</tr>



<tr>
	<td colspan=2 align='right'>
		<hr>
		<input type='button' OnClick=\"javascript:ParseForm('ffmpolicy2','$page',false,false,false,'ffmpolicy2Div',null,x_ffmpolicy2);\" value='{edit}&nbsp;&raquo;'>
		
	</td>
</tr>
</table>
</div>
";
$form=RoundedLightWhite($form);	
$html="<H1>{APP_POLICYD_WEIGHT} {notifications}</H1>
	$form";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}


function SaveSettings(){
	$policy=new policydweight();
	while (list ($num, $ligne) = each ($_GET) ){
		$policy->main_array[$num]=$ligne;
	}
	
	$policy->SaveConf();
	
}

function SaveSettingsFormatted(){
	$policy=new policydweight();
	while (list ($num, $ligne) = each ($_GET) ){
		$ligne=str_replace('"','',$ligne);
		$ligne=str_replace("'",'',$ligne);
		$ligne=addslashes($ligne);
		$policy->main_array[$num]=$ligne;
	}
	
	$policy->SaveConf();	
}

function popup_dnsbl(){
	$policy=new policydweight();
	$page=CurrentPageName();
$form="
<div id='ffmpolicy2Div'>
<form name='ffmpolicy2'>
<table style='width:100%'>
<tr>
	<td class=legend>{MAXDNSBLHITS}:</td>
	<td>". Field_text("MAXDNSBLHITS",$policy->main_array["MAXDNSBLHITS"],"width:30px")."</td>
</tr>
<tr>
	<td class=legend>{MAXDNSBLSCORE}:</td>
	<td>". Field_text("MAXDNSBLSCORE",$policy->main_array["MAXDNSBLSCORE"],"width:30px")."</td>
</tr>
<tr>
	<td colspan=2 align='right'>
		<hr>
		<input type='button' OnClick=\"javascript:ParseForm('ffmpolicy2','$page',false,false,false,'ffmpolicy2Div',null,x_ffmpolicy3);\" value='{edit}&nbsp;&raquo;'>
	</td>
</tr>
</table>
</form>
</div>
";	
$form=RoundedLightWhite($form);	
$html="<H1>{DNSBL_settings}</H1>
	$form<br>
	<div id='dnsbllistpolicd' style='width:100%;height:450px;overflow:auto'>
	".dnsbl_list()."</div>
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function dnsbl_list(){
	$policy=new policydweight();
	$page=CurrentPageName();
	
	$data=file_get_contents("ressources/dnsrbl.db");
	$tr=explode("\n",$data);
	while (list ($num, $val) = each ($tr) ){
		if(preg_match("#RBL:(.+)#",$val,$re)){
			$RBL[$re[1]]=$re[1];
		}
	if(preg_match("#RHSBL:(.+)#",$val,$re)){
			$RHSBL[$re[1]]=$re[1];
		}		
	}
	
	$list=Field_array_Hash($RBL,"iii_DNSBL",null);
	$listRHS=Field_array_Hash($RHSBL,"iiy_RHSBL",null);
	

	$html="
	
	<table style='width:99%' class=table_form>
	<tr>
	
	<th colspan=2>DNSBL</th>
	<th>{BAD_SCORE}</th>
	<th>{GOOD_SCORE}</th>
	<th>{log}</th>
	<th>&nbsp;</th>
	</tr>
	";
	while (list ($num, $val) = each ($policy->dnsbl_array) ){
		$md5=md5($num);
		$html=$html ."
		<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>$num</strong></td>
			<td><input type='hidden' id='{$md5}_DNSBL' value='$num'>". Field_text("{$md5}_HIT",$val["HIT"],'width:30px',null,null,null,false,"PolicyDSaveDnsbl('$md5',event)")."</td>
			<td>". Field_text("{$md5}_MISS",$val["MISS"],'width:30px',null,null,null,false,"PolicyDSaveDnsbl('$md5',event)")."</td>
			<td><strong>{$val["LOG"]}</td>
			<td width=1%>". imgtootltip('ed_delete.gif',"{delete}","PolicyDDelDnsbl('$md5')")."</td>
		</tr>
		";
		}
	
	$html=$html . "
	<tr>
		<td width=1%>&nbsp;</td>
		<td colspan=5><hr>$list
		<input type='hidden' id='iii_HIT' value='4.35'>
					  <input type='hidden' id='iii_MISS' value='0'>
					  <input type='button' OnClick=\"javascript:PolicyDSaveDnsbl('iii',13);\" value='{add}&nbsp;&raquo;'></td>
	</tr>		
		
	</table>
	<br><table style='width:99%' class=table_form>
	<tr>
	
	<th colspan=2>RHSBL</th>
	<th>{BAD_SCORE}</th>
	<th>{GOOD_SCORE}</th>
	<th>{log}</th>
	<th>&nbsp;</th>
	</tr>	
	";
	
while (list ($num, $val) = each ($policy->rhsbl_array) ){
		$md5=md5($num);
		$html=$html ."
		<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>$num</strong></td>
			<td><input type='hidden' id='{$md5}_RHSBL' value='$num'>". Field_text("{$md5}_HIT",$val["HIT"],'width:30px',null,null,null,false,"PolicyDSaveRHSBL('$md5',event)")."</td>
			<td>". Field_text("{$md5}_MISS",$val["MISS"],'width:30px',null,null,null,false,"PolicyDSaveRHSBL('$md5',event)")."</td>
			<td><strong>{$val["LOG"]}</td>
			<td width=1%>". imgtootltip('ed_delete.gif',"{delete}","PolicyDDelRHSBL('$md5')")."</td>
		</tr>
		";
		}
	
	$html=$html . "
	<tr>
		<td width=1%>&nbsp;</td>
		<td colspan=5 algin='right'><hr>$listRHS
		<input type='hidden' id='iiy_HIT' value='4.35'>
					  <input type='hidden' id='iiy_MISS' value='0'>
					  <input type='button' OnClick=\"javascript:PolicyDSaveRHSBL('iiy',13);\" value='{add}&nbsp;&raquo;'></td>
	</tr>		
		
	</table>";	
	
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
}

function SaveDNSBL(){
	$policy=new policydweight();
	$kg=$_GET["DNSBL"];
	$kg=str_replace(".","_",$kg);
	$kg=strtoupper($kg);
	$policy->dnsbl_array[$_GET["DNSBL"]]=array("HIT"=>$_GET["HIT"],"MISS"=>$_GET["MISS"],"LOG"=>$kg);
	$policy->SaveConf();
	
}

function SaveRHSBL(){
	$policy=new policydweight();
	$kg=$_GET["RHSBL"];
	$kg=str_replace(".","_",$kg);
	$kg=strtoupper($kg);
	$policy->rhsbl_array[$_GET["RHSBL"]]=array("HIT"=>$_GET["HIT"],"MISS"=>$_GET["MISS"],"LOG"=>$kg);
	$policy->SaveConf();	
}

function DelDNSBL(){
	$policy=new policydweight();
	unset($policy->dnsbl_array[$_GET["DEL_DNSBL"]]);
	$policy->SaveConf();
}
function DelRHSBL(){
	$policy=new policydweight();
	unset($policy->rhsbl_array[$_GET["DEL_RHSBL"]]);
	$policy->SaveConf();	
}

function EnablePolicydWeight(){
	$sock=new sockets();
	$sock->SET_INFO('EnablePolicydWeight',$_GET["EnablePolicydWeight"]);
	$sock->getFrameWork("cmd.php?reconfigure-postfix=yes");
	
}

/*    smtpd_recipient_restrictions =
        permit_mynetworks,
        ...
        reject_unauth_destination,
        check_policy_service inet:127.0.0.1:12525
*/


?>