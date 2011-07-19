<?php
session_start();
include_once('ressources/class.sieve.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.user.inc');


if(isset($_GET["sieve-index"])){sieve_vacation();exit;}
if(isset($_GET["sieve-save-vacation"])){sieve_save_vacation();exit;}

js();


function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{OUT_OF_OFFICE}');
	$users=new usersMenus();
if(isset($_GET["uid"])){
	if($users->AsMailBoxAdministrator){$uid=$_GET["uid"];}
	}else{
		$uid=$_SESSION["uid"];
	}
	
	if($uid==null){
		
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();
	}
$page=CurrentPageName();	
$html="

function SieveVacantionPop(){
	YahooWin4('600','$page?sieve-index=yes&uid=$uid','$title');
	}
	
	function x_SieveVacantionSave(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		SieveVacantionPop();
		}		
	
	
function SieveVacantionSave(){
			var message=document.getElementById('message').value;
			var days=document.getElementById('days').value;
			var vacation_enabled=document.getElementById('vacation_enabled').value;
			
			var XHR = new XHRConnection();
			XHR.appendData('sieve-save-vacation','$uid');
			XHR.appendData('message',message);
			XHR.appendData('days',days);
			XHR.appendData('vacation_enabled',vacation_enabled);
			document.getElementById('mform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SieveVacantionSave);
			}

SieveVacantionPop();";
	
	
echo $html;	
	
	
}

function sieve_vacation(){
	
	$sieve=new clSieve($_GET["uid"]);
	$sieve->GetRuleVacation();
	$sieve_rule_name=$sieve->sieve_rulename;
	
	if($sieve_rule_name<>null){
		$rules=$sieve->GetRules($sieve_rule_name);
		$vacation_rule=$rules[$sieve->sieve_ruleindex];
	}
	
	$message=$vacation_rule["actions"][0]["message"];
	$message=stripslashes($message);
	for($i=1;$i<90;$i++){
		$days[$i]=$i;
		
	}
	
	
	$day=Field_array_Hash($days,'days',$vacation_rule["actions"][0]["days"]);
	
	switch ($vacation_rule["status"]){
		case "DISABLED":$status=0;break;
		case "ENABLED":$status=1;break;
		default:$status=1;break;
		}
	
		
$enable=Field_numeric_checkbox_img('vacation_enabled',$status,"{enable_disable}");		
	
	
	$addresses="<table style='width:100%'>
	<tr>
		<th>&nbsp;</td>
		<th>{email}</th>
	</tr>	
	";
	if(is_array($vacation_rule["actions"][0]["addresses"])){
		while (list ($num, $email) = each ($vacation_rule["actions"][0]["addresses"]) ){
			$addresses=$addresses.
			
			"<tr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><code>$email</code>
			</tr>";
			
			
		}
		
	}
	$addresses=$addresses."</table>";
	$addresses="<div id='email_drr' style='width:99%;height:55px;overflow;auto'>$addresses</div>";
	$addresses=RoundedLightWhite($addresses)."<br>";

	
	
	
	
	
	$form="
	<table style='width:100%'>
	
	
	<tr>
		<td class=legend valign='top'>{enable}:</td>
		<td valign='top' width=80%>
			$enable
		</td>
	</tr>	
	<tr>
		<td class=legend valign='top'>{vacation_message}:</td>
		<td valign='top' width=80%>
			<textarea name='message' id='message' style='width:100%;height:100px;overflow;auto'>$message</textarea>
		</td>
	</tr>
	<tr>
		<td class=legend valign='top'>{days}:</td>
		<td valign='top'>
			$day
		</td>
	</tr>	
	</table>";
	
	
	$form=RoundedLightWhite($form);
	$html="<H1>{rule}::$sieve_rule_name::{OUT_OF_OFFICE}</H1>
	<p class=caption>{OUT_OF_OFFICE_TEXT}</p>
	<div id='mform'>
	$addresses
	$form
	<hr>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:SieveVacantionSave();\" value='{save}&nbsp;&raquo;'></div>
	</div>
	
	";
	
	
$tpl=new templates();	
	
echo $tpl->_ENGINE_parse_body($html,'users.out-of-office.php');	
	
}

function sieve_save_vacation(){
	
	$sieve=new clSieve($_GET["sieve-save-vacation"]);
	$sieve->GetRuleVacation();
	$sieve_rule_name=$sieve->sieve_rulename;	
	
	writelogs("uid={$_GET["sieve-save-vacation"]}:: rule:$sieve_rule_name,",__FUNCTION__,__FILE__);
	
	if($sieve_rule_name<>null){
		$rules=$sieve->GetRules($sieve_rule_name);
		$vacation_rule=$rules[$sieve->sieve_ruleindex];
	}else{
		writelogs("uid={$_GET["sieve-save-vacation"]}:: create default rule:AntiSpamJunk",__FUNCTION__,__FILE__);
		if($sieve->CreateArticaRule()){
			$sieve=new clSieve($_GET["sieve-save-vacation"]);
			$sieve_rule_name="AntiSpamJunk";
			$rules=$sieve->GetRules($sieve_rule_name);
			$sieve->sieve_ruleindex=count($rules);

		}else{
			writelogs("uid={$_GET["sieve-save-vacation"]}:: create default rule:AntiSpamJunk FAILED!",__FUNCTION__,__FILE__);
		}
	}

	writelogs("{$_GET["sieve-save-vacation"]}:: put rule name \"$sieve_rule_name\"",__FUNCTION__,__FILE__);
	
	switch ($_GET["vacation_enabled"]) {
		case 1:$rules[$sieve->sieve_ruleindex]["status"]="ENABLED";	break;
		case 0:$rules[$sieve->sieve_ruleindex]["status"]="DISABLED";break;
		default:$rules[$sieve->sieve_ruleindex]["status"]="ENABLED";break;
	}
	
	
	
	$_GET["message"]=str_replace("\n","\r\n",$_GET["message"]);
	$rules[$sieve->sieve_ruleindex]["control"]="";
	$rules[$sieve->sieve_ruleindex]["matchAny"]="";
	$rules[$sieve->sieve_ruleindex]["conditions"]=array();
	$rules[$sieve->sieve_ruleindex]["special"]="vacation";
	$rules[$sieve->sieve_ruleindex]["actions"][0]["days"]=$_GET["days"];
	$rules[$sieve->sieve_ruleindex]["actions"][0]["message"]=$_GET["message"];
	$ct=new user($_GET["sieve-save-vacation"]);
	$rules[$sieve->sieve_ruleindex]["actions"][0]["addresses"]=$ct->HASH_ALL_MAILS;	
	$content=$sieve->CompileRule($rules,$sieve_rule_name);
	$sieve->SaveRule($sieve_rule_name,$content);		
	
}

?>