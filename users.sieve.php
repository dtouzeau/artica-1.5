<?php
session_start();
include_once('ressources/class.sieve.inc');

if(isset($_GET["index"])){sieve_index();exit;}
if(isset($_GET["sieve-enable-rule"])){sieve_enable_rule();exit;}
if(isset($_GET["sieve-disable-rules"])){sieve_disable_rules();exit;}
if(isset($_GET["sieve-rule"])){sieve_rule();exit;}
if(isset($_GET["sieve-edit-rule"])){sieve_rule_form();exit;}
if(isset($_GET["sieve-edit-filter"])){sieve_edit_filter();exit;}
if(isset($_GET["sieve-edit-action"])){sieve_edit_action();exit;}
if(isset($_GET["sieve-save-action"])){sieve_save_action();exit;}
if(isset($_GET["sieve-delete-action"])){sieve_delete_action();exit;}
if(isset($_GET["sieve-delete-condition"])){sieve_delete_condition();exit;}
if(isset($_GET["change-condition-form"])){sieve_rule_conditions_form();exit;}
if(isset($_GET["change-action-form"])){sieve_rule_actions_form();exit;}
if(isset($_GET["change-resumes"])){echo sieve_rule_resumer($_GET["change-resumes"],$_GET["uid"]);exit;}
if(isset($_GET["sieve-delete-master-rule"])){sieve_delete_master_rule();exit;}
if(isset($_GET["sieve-add-master-rule"])){sieve_add_master_rule();exit;}
if(isset($_GET["sieve-delete-rulename"])){sieve_delete_rule();exit;}
if(isset($_GET["sieve-add-master-name"])){sieve_add_rulename();exit;}


js();



function js(){
	$tpl=new templates();
	if(!isset($_GET["uid"])){$uid=$_SESSION["uid"];}else{
		$users=new usersMenus();
		$uid=$_GET["uid"];
		if(!$users->AsMailBoxAdministrator){
			$title=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
			echo "alert('$title');";exit;
		}
		
	}
	$title=$tpl->_ENGINE_parse_body('{mailboxes_rules}',"domains.edit.user.php");
	$add=$tpl->_ENGINE_parse_body('{give_the_rulename}',"domains.edit.user.php");
	$page=CurrentPageName();
	$html="
		var mem_rulenumber='';
		var mem_rulename='';
		var mem_match='';
		
		function SieveLoad(){
			YahooWin2(400,'$page?index=$uid','$uid::$title');
		}
		
		function SieveAddRuleName(){
		var r=prompt('$add');
			if(r){
			var XHR = new XHRConnection();
			XHR.appendData('sieve-add-master-name',r);
			XHR.appendData('uid','$uid');
			mem_rulename=r;
			XHR.sendAndLoad('$page', 'GET',x_SieveEnableRule);
			}
		}
		
		function SieveEditRule(rulename,ruleindex){
			YahooWin4(600,'$page?sieve-edit-rule='+rulename+'&ruleindex='+ruleindex+'&uid=$uid'+'&section=condition&rulename='+rulename,'$uid::'+rulename+'::'+ruleindex);
		}
		
		function SieveEditAction(rulename,ruleindex){
			YahooWin4(600,'$page?sieve-edit-action='+rulename+'&ruleindex='+ruleindex+'&uid=$uid'+'&section=action&rulename='+rulename,'$uid::'+rulename+'::'+ruleindex);
		}		
		
		function LoadSieveSectionAjax(rulename,index,tab){
			if(tab=='action'){SieveEditAction(rulename,index);}
			if(tab=='condition'){SieveEditRule(rulename,index);}
		}
		
		
	function x_EditSieveRule(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		SieveEditRule(mem_rulename,mem_rulenumber);
		if(document.getElementById('SieveResumers')){LoadAjax('SieveResumers','$page?change-resumes='+mem_rulename+'&uid=$uid');}
		
		}	
		
	function x_SaveActionRule(obj){
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		SieveEditAction(mem_rulename,mem_rulenumber);
		if(document.getElementById('SieveResumers')){LoadAjax('SieveResumers','$page?change-resumes='+mem_rulename+'&uid=$uid');}	
	}
	
	function x_SieveDeleteMasterRule(obj){
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		if(document.getElementById('SieveResumers')){LoadAjax('SieveResumers','$page?change-resumes='+mem_rulename+'&uid=$uid');}	
	}
	
	function SieveDeleteMasterRule(rulename,index){
		var XHR = new XHRConnection();
		XHR.appendData('sieve-delete-master-rule',rulename);
		XHR.appendData('uid','$uid');
		XHR.appendData('master-index',index);
		mem_rulename=rulename;
		document.getElementById('SieveResumers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SieveDeleteMasterRule);	
	
	}
	
	function SieveAddNewRule(rulename){
		var XHR = new XHRConnection();
		XHR.appendData('sieve-add-master-rule',rulename);
		XHR.appendData('uid','$uid');
		mem_rulename=rulename;
		document.getElementById('SieveResumers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SieveDeleteMasterRule);	
	}
		
	function x_SieveEnableRule(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		SieveLoad();
		}			
		
		function SieveEnableRule(rulename){
		var XHR = new XHRConnection();
		XHR.appendData('sieve-enable-rule',rulename);
		XHR.appendData('uid','$uid');
		document.getElementById('sieve_index').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SieveEnableRule);		
		}
		
		function SieveDisableRules(){
		var XHR = new XHRConnection();
		XHR.appendData('sieve-disable-rules','$uid');
		document.getElementById('sieve_index').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SieveEnableRule);			
		}
		
		function EditSieveRule(rulename,rulenumber,filter_index){
			var XHR = new XHRConnection();
			if(document.getElementById(filter_index+'_header')){
				XHR.appendData('header',document.getElementById(filter_index+'_header').value);
			}
			if(document.getElementById('control')){XHR.appendData('control',document.getElementById('control').value);}
			if(document.getElementById('matchAny')){XHR.appendData('matchAny',document.getElementById('matchAny').value);}
			
			
			
			mem_rulenumber=rulenumber;
			mem_rulename=rulename;
			XHR.appendData('sieve-edit-filter','$uid');
			XHR.appendData('sieve-rule-name',rulename);
			XHR.appendData('sieve-rule-number',rulenumber);
			XHR.appendData('sieve-filter-index',filter_index);
			XHR.appendData('condition',document.getElementById(filter_index+'_condition').value);
			XHR.appendData('matchType',document.getElementById(filter_index+'_matchType').value);
			XHR.appendData('matchStr',document.getElementById(filter_index+'_matchStr').value);
			document.getElementById('sieve-rules-img').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_EditSieveRule);	
		}
		
		function ChangeRuleConditionField(index){
			if(mem_match.length==0){
				if(document.getElementById(index+'_header')){
					mem_match=document.getElementById(index+'_header').value;
				}
			}else{
				if(document.getElementById(index+'_header')){
					if(mem_match!==document.getElementById(index+'_header').value){
						mem_match=document.getElementById(index+'_header').value;
					}
				}
			}
		
			LoadAjax(index+'_matchStr_div','$page?change-condition-form='+document.getElementById(index+'_condition').value+'&default='+mem_match+'&condition-index='+index);	
		}
		
		function ChangeRuleActionField(index){
			var ffmv=document.getElementById(index+'_type').value;
			var id='action_field_'+index;
			LoadAjax(id,'$page?change-action-form='+ffmv+'&action-index='+index);
		}
		
		function SaveActionRule(rulename,ruleindex,actionumber){
			mem_rulenumber=ruleindex;
			mem_rulename=rulename;
			var type=document.getElementById(actionumber+'_type').value;
			var XHR = new XHRConnection();
			XHR.appendData('sieve-save-action','$uid');
			XHR.appendData('sieve-rule-name',rulename);
			XHR.appendData('sieve-rule-number',ruleindex);
			XHR.appendData('sieve-action-index',actionumber);
			XHR.appendData('type',type);
			if(document.getElementById(actionumber+'_address')){
				XHR.appendData('address',document.getElementById(actionumber+'_address').value);
			}
			
			if(document.getElementById(actionumber+'_message')){
				XHR.appendData('message',document.getElementById(actionumber+'_message').value);
			}	

			if(document.getElementById(actionumber+'_folder')){
				XHR.appendData('folder',document.getElementById(actionumber+'_folder').value);
			}

			if(document.getElementById(actionumber+'_flag')){
				XHR.appendData('flag',document.getElementById(actionumber+'_flag').value);
			}			
			
			
			document.getElementById('sieve-rules-img').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveActionRule);
			}
			
			function SieveDeleteAction(rulename,indexrule,index_action){
				mem_rulenumber=indexrule;
				mem_rulename=rulename;
				var XHR = new XHRConnection();
				XHR.appendData('sieve-delete-action','$uid');
				XHR.appendData('sieve-rule-name',rulename);
				XHR.appendData('sieve-rule-number',indexrule);
				XHR.appendData('sieve-action-index',index_action);
				XHR.sendAndLoad('$page', 'GET',x_SaveActionRule);				
			
			}
		
			
		function SieveDeleteCondition(rulename,rulenumber,index){
				mem_rulenumber=rulenumber;
				mem_rulename=rulename;
				var XHR = new XHRConnection();
				XHR.appendData('sieve-delete-condition','$uid');
				XHR.appendData('sieve-rule-name',rulename);
				XHR.appendData('sieve-rule-number',rulenumber);
				XHR.appendData('sieve-condition-index',index);
				XHR.sendAndLoad('$page', 'GET',x_EditSieveRule);			
		}
		
		
		function SieveDisplayRule(rulename){
			YahooWin3(600,'$page?sieve-rule='+rulename+'&uid=$uid','$uid::'+rulename);
		}
		
		function SieveDeleteRuleName(rulename){
			var XHR = new XHRConnection();
				XHR.appendData('sieve-delete-rulename',rulename);
				XHR.appendData('uid','$uid');
				XHR.sendAndLoad('$page', 'GET',x_SieveEnableRule);
		}
	
	SieveLoad();
	";
	
	echo $html;
	
	
}

function sieve_delete_action(){
	$uid=$_GET["sieve-delete-action"];
	$rulename=$_GET["sieve-rule-name"];
	$ruleindex=$_GET["sieve-rule-number"];
	$action_index=$_GET["sieve-action-index"];	
	$sieve=new clSieve($uid);
	$rules=$sieve->GetRules($rulename);		
	unset($rules[$ruleindex]["actions"][$action_index]);
	$content=$sieve->CompileRule($rules,$rulename);
	$sieve->SaveRule($rulename,$content);	
}

function sieve_delete_condition(){
 	$uid=$_GET["sieve-delete-condition"];	
	$rulename=$_GET["sieve-rule-name"];
	$ruleindex=$_GET["sieve-rule-number"];
	$condition_index=$_GET["sieve-condition-index"];	 
	$sieve=new clSieve($uid);
	$rules=$sieve->GetRules($rulename);
	unset($rules[$ruleindex]["conditions"][$condition_index]);
	$content=$sieve->CompileRule($rules,$rulename);
	$sieve->SaveRule($rulename,$content);	
	}

function sieve_save_action(){
	$uid=$_GET["sieve-save-action"];
	$rulename=$_GET["sieve-rule-name"];
	$ruleindex=$_GET["sieve-rule-number"];
	$action_index=$_GET["sieve-action-index"];
	
	$sieve=new clSieve($uid);
	$rules=$sieve->GetRules($rulename);	
	
	$arr["type"]=$_GET["type"];
	
	echo "$rulename($ruleindex)\n";
	echo "action number: $action_index\n";
	echo "type:{$_GET["type"]}\n";
	
	if(isset($_GET["address"])){$arr["address"]=$_GET["address"];}
	if(isset($_GET["message"])){$arr["message"]=$_GET["message"];}
	if(isset($_GET["folder"])){$arr["folder"]=$_GET["folder"];}
	if(isset($_GET["flag"])){
		if(!preg_match('#unk#',$_GET["flag"])){
			$arr["flag"]='\\\\'.$_GET["flag"];
		}else{
		$arr["flag"]=$_GET["flag"];}
	}
	
	
	
	$rules[$ruleindex]["actions"][$action_index]=$arr;
	$content=$sieve->CompileRule($rules,$rulename);
	$sieve->SaveRule($rulename,$content);		
	}
	
function sieve_delete_rule(){
	$rulename=$_GET["sieve-delete-rulename"];
	$uid=$_GET["uid"];
	$sieve=new clSieve($uid);
	$sieve->deleteRule($rulename);		
	
}

function sieve_add_rulename(){
	$rulname=$_GET["sieve-add-master-name"];
	$uid=$_GET["uid"];
	$sieve=new clSieve($uid);
	$sieve->CreateNewRule($rulname);
	
}

function sieve_index(){
	$uid=$_GET["index"];
	$sieve=new clSieve($uid);
	if($sieve->error<>null){$error=$sieve->error;}
	$html="
	
	<h1>{mailboxes_rules}</H1>
	<div style='float:right;text-align:right'><input type='button' OnClick=\"javascript:SieveAddRuleName();\" value='{add}&nbsp;&raquo;'></div>
	<p class=caption>{mailboxes_rules_text}<br>{sieve_rule_explain}</p>
	
	<div style='font-size:13px;font-weight:bold;color:red;width:80%;margin:3px'>$error</div>
	
	";
	
	$rules=$sieve->array_rules;
	
	
	
	$tables="
	
	<div id='sieve_index'>
	<table style='width:100%'>";
	$count=0;
	while (list ($num, $ligne) = each ($rules) ){
		$rulename=$num;
		$md=md5($num);
		$count=$count+1;
		if(!$ligne){
			
			$img="status_critical.gif";
			$link=texttooltip("{enable}","{enable_this_filter}","SieveEnableRule('$rulename')");
			$link="&laquo;&nbsp;$link</strong>&nbsp;&raquo;";
		}else{$link="&nbsp;";$img="status_ok.gif";;}
		
		$tables=$tables."
		<tr " . CellRollOver().">
			<td width=1%><img src='img/$img'></td>
			<td><strong><a href='#' OnClick=\"javascript:SieveDisplayRule('$rulename');\"><code style='font-size:13px;font-weight:bold'>$rulename</code></a></td>
			<td width=1%>" . imgtootltip("txt_small.gif","{edit}","SieveDisplayRule('$rulename');")."<td>
			<td><strong>$link</td>
			<td width=1%>" . imgtootltip("ed_delete.gif","{delete}","SieveDeleteRuleName('$rulename');")."<td>
		</tr>	
			";
		
		
		
	}
	
	if($count>0){
		$tables=$tables."
		<tr>
			<td colspan=4 align='right'><input type='button' OnClick=\"javascript:SieveDisableRules();\" value='{disable_all_filters}&nbsp;&raquo;'></td>
		</tr>
		";
	}
	$tables=$tables."</table>
	
	</div>";
	$tables=RoundedLightWhite($tables);
	$html=$html."$tables";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,"domains.edit.user.php");
	
}
function sieve_enable_rule(){
	$rulename=$_GET["sieve-enable-rule"];
	$user=$_GET["uid"];
	$enabled=$_GET["enabled"];
	$sieve=new clSieve($user);
	$sieve->EnableRule($rulename);
	}
function sieve_disable_rules(){
	$user=$_GET["sieve-disable-rules"];
	$sieve=new clSieve($user);
	$sieve->EnableRule("");	
	}
function sieve_rule(){

	$rulename=$_GET["sieve-rule"];
	
	$tables=sieve_rule_resumer($rulename,$_GET["uid"]);
	$html="
	<H1>$rulename:: {rules}</H1>
	<div style='text-align:right;float:right'><input type='button' OnClick=\"javascript:SieveAddNewRule('$rulename');\" value='&nbsp;&nbsp;&nbsp;{add}&nbsp;&nbsp;&nbsp;&raquo;'></div>
	<div id='SieveResumers'>
	$tables
	</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function sieve_delete_master_rule(){
		$sieve=new clSieve($_GET["uid"]);
		$rules=$sieve->GetRules($_GET["sieve-delete-master-rule"]);
		unset($rules[$_GET["master-index"]]);
		$content=$sieve->CompileRule($rules,$_GET["sieve-delete-master-rule"]);
		$sieve->SaveRule($_GET["sieve-delete-master-rule"],$content);		
	}
	
function sieve_add_master_rule(){
	$rulename=$_GET["sieve-add-master-rule"];
	$sieve=new clSieve($_GET["uid"]);
	$sieve->AddNewRule($rulename);
	}
function sieve_rule_resumer($rulename,$uid){
	$sieve=new clSieve($uid);
	$rules=$sieve->GetRules($rulename);
	$rules_headers=$sieve->GetRulesHeaders($rules);
	
	
	
	if(is_array($rules_headers)){
		$tables="
		<span style='font-size:13px;text-transform:capitalize'>".count($rules_headers)." {rules}:</span>
		<table style='width:100%'>";
		while (list ($num, $ligne) = each ($rules_headers) ){
			
			$jsedit=CellRollOver("SieveEditRule('$rulename','$num')");
			if(preg_match("#Artica rule#",$ligne)){
				$ligne=str_replace("Artica rule","<strong style='color:red'>{locked}</strong>",$ligne);
				$jsedit=null;
				}
			$tables=$tables."
			<tr>
				<td width=1% valign='top' ". $jsedit."><img src='img/fw_bold.gif'></td>
				<td><code style='font-size:11px' ". $jsedit.">$ligne</code></td>
				<td valign='top'>" . imgtootltip("ed_delete.gif","{delete}","SieveDeleteMasterRule('$rulename','$num')")."</td>
			</tr>
			<tr>
				<td colspan=2><hr></td>
			</tr>
			";
			
		}
	$tables=$tables."</table>";}
	$tpl=new templates();
	$tables=$tpl->_ENGINE_parse_body($tables);
	
	return $tables=RoundedLightWhite($tables);	
	
}
	
function sieve_rule_tabs(){
	$array["condition"]="{conditions}";
	$array["action"]="{actions}";
	
$rulename=$_GET["rulename"];
	$index=$_GET["ruleindex"];
	
	
while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
			$html=$html . "<li><a href=\"javascript:LoadSieveSectionAjax('$rulename','$index','$num')\" $class>$ligne</a></li>\n";
		}
			
	$html="<div id=tablist style='margin-top:3px;margin-bottom:3px;'>$html</div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
}

function sieve_edit_action(){
	$rulename=$_GET["rulename"];
	$index=$_GET["ruleindex"];
	$uid=$_GET["uid"];
	$sieve=new clSieve($_GET["uid"]);
	$rules=$sieve->GetRules($rulename);
	$tabs=sieve_rule_tabs();
	$rule=$rules[$index];
	$actions=$rule["actions"];

	
	
if(is_array($actions)){
		while (list ($num, $ligne) = each ($actions) ){
			$cond=$cond.sieve_rule_actions($ligne,$index,$rulename,$num);
		}
	}	
	
	$cond=sieve_rule_actions(array(),$index,$rulename,null).$cond;
$html="
	<H1>{rule}::&nbsp;$index</H1>
	$tabs
	
	<div id='sieve-rules-img' style='margin:5px'>
	" . RoundedLightWhite("
	<div style='width:100%;height:300px;overflow:auto'>
		$cond
	</div>")."
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function sieve_rule_actions_form($type=null,$index_action=null,$default=null,$return=false){
	if($type==null){$type=$_GET["change-action-form"];}
	if($index_action==null){$index_action=$_GET["action-index"];}
	
$flag["Seen"]="Seen";
$flag["Deleted"]="Deleted";
$flag["Answered"]="Answered";
$flag["Flagged"]="Flagged";
$flag["Junk"]="Junk";
$flag["NotJunk"]="NotJunk"	;
	
if($type=="fileinto"){
	if(is_array($default)){$default=$default["folder"];}
	$filedstring=Field_text("{$index_action}_folder",$default,"width:120px");
	
}

if($type=="redirect"){
	if(is_array($default)){$default=$default["address"];}
	$filedstring=Field_text("{$index_action}_address",$default,"width:120px");
	
}

if($type=="reject"){
	if(is_array($default)){$default=$default["message"];}
	$filedstring=Field_text("{$index_action}_message",$default,"width:120px");
	
}

if($type=="vacation"){
	if(is_array($default)){$default=$default["message"];}
	$filedstring=Field_text("{$index_action}_message",$default,"width:120px");
	
}

if($type=="addflag"){
	if(is_array($default)){$default=$default["flag"];}
	$default=str_replace('\\','',$default);
	$filedstring=Field_array_Hash($flag,"{$index_action}_flag",$default);
	
}

if($return){return $filedstring;}	
echo $filedstring;
	
}


function sieve_rule_actions($array,$indexrule,$rulename,$index_action){
	
$actions["fileinto"]="mailbox";	
$actions["redirect"]="Forward to address";
$actions["reject"]="Send a reject message";
$actions["discard"]="Discard the message";
//$actions["vacation"]="Send vacation message";
$actions["addflag"]="Set message flag";
$actions["keep"]="Keep a copy in your Inbox";
$actions["stop"]="Stop processing filter rules";
$actions[null]="{select}";	

$action=Field_array_Hash($actions,"{$index_action}_type",$array["type"],"ChangeRuleActionField('$index_action')");
$filedstring=sieve_rule_actions_form($array["type"],$index_action,$array,true);

$html="
<br>
<table class='table_form'>
<tr>
	<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","SieveDeleteAction('$rulename','$indexrule','$index_action')")."</td>	
	<td align=\"right\">$minititle&nbsp;$action:</td>
	<td><span id='action_field_{$index_action}'>$filedstring</span></td>
	<td><input type='button' OnClick=\"javascript:SaveActionRule('$rulename','$indexrule','$index_action');\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>";


	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}
	
	
function sieve_rule_form(){
//sieve-edit-rule='+rulename+'&index='+ruleindex+'&uid=$uid','$uid::'+rulename::'+ruleindex);

	$rulename=$_GET["sieve-edit-rule"];
	$index=$_GET["ruleindex"];
	$uid=$_GET["uid"];
	$sieve=new clSieve($_GET["uid"]);
	$rules=$sieve->GetRules($rulename);
	

	
	
	$control_array=array("if"=>"if","elseif"=>"else if");
	$anyof_array=array("0"=>"all of","4"=>"any of");
	$tabs=sieve_rule_tabs();
	
	$rule=$rules[$index];
	if($rule["actions"][0]["type"]=="vacation"){
		$link=Paragraphe("64-planning.png","{OUT_OF_OFFICE}","{OUT_OF_OFFICE_TEXT}","javascript:Loadjs('sieve.vacation.php?uid=$uid')");
		$html="
		<H1>{vacation}::&nbsp;$index</H1>
		<center>$link</center>
		";	
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		return null;
		
	}
	
	
	
	$control=Field_array_Hash($control_array,'control',$rule["control"]);
	$anyof=Field_array_Hash($anyof_array,'matchAny',$rule["matchAny"]);
	$conditions=$rule["conditions"];
	$actions=$rule["actions"];
	
	
	if(is_array($conditions)){
		while (list ($num, $ligne) = each ($conditions) ){
			$cond=$cond.sieve_rule_conditions($ligne,$num,$rulename,$index);
		}
	}
	
	$cond=sieve_rule_conditions(array(),null,$rulename,$index).$cond;
	
	$html="
	<H1>{rule}::&nbsp;$index</H1>
	$tabs
	<div id='sieve-rules-img'>
	<table style='width:100%'>
	<tr>
		<td>$control&nbsp;$anyof&nbsp;{the_following_matches}:</td>
	</tr>
	</table>
	<div style='width:100%;height:300px;overflow:auto'>
		$cond
	</div>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}

function sieve_rule_conditions_form(){
	$type=$_GET["change-condition-form"];
	$default=$_GET["default"];
	$index=$_GET["condition-index"];

if($type=="header"){
	echo Field_text("{$index}_header",$default,"width:200px");
}	
}

	
function sieve_rule_conditions($array,$index,$rulename,$rulenumber){
 $condition["from"]="If message From:";
 $condition["to"]="If message To:";
 $condition["tocc"]="If message To: or Cc:";
 $condition["subject"]="If message Subject:";
 //$condition["size"]="If message size is";
 $condition["header"]="If message header";
 $condition[null]="{select}";
 
if(is_array($array["header"])){$array["type"]=implode("",$array["header"]);}
if($array["header"]=="to"){$array["type"]="to";}


$matchType[":is"]="is";
$matchType[":notis"]="is not";
$matchType[":contains"]="contains";
$matchType[":notcontains"]="does not contain";
$matchType[":matches"]="matches";
$matchType[":notmatches"]="does not match";
$matchType[":regex"]="matches regular expression";
$matchType[":notregex"]="does not match regular expression";


if($array["type"]=="header"){
	$headerf=Field_text("{$index}_header",$array["header"],"width:200px");
}


$mcondition=Field_array_Hash($condition,"{$index}_condition",$array["type"],"ChangeRuleConditionField('$index')");
$mmatchType=Field_array_Hash($matchType,"{$index}_matchType",$array["matchType"]);

//matchStr

$html="
<table class='table_form'>
<tr>
	<td with=1%>" . imgtootltip('ed_delete.gif',"{delete}","SieveDeleteCondition('$rulename','$rulenumber','$index')")."</td>
	<td align=\"right\">$mcondition:</td>
	<td><div id='{$index}_matchStr_div'>$headerf</div></td>
</tr>
<tr>
	<td align=\"right\" colspan=2 >$mmatchType:</td>
	<td>" . Field_text("{$index}_matchStr",$array["matchStr"],"width:200px")."</td>
</tr>
<tr>
<td colspan=3 align='right'><input type='button' OnClick=\"javascript:EditSieveRule('$rulename','$rulenumber','$index');\" value='{edit}&nbsp;&raquo;'></td>
</tr>
</table>
<br>";

	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
 
}


function sieve_edit_filter(){
	$uid=$_GET["sieve-edit-filter"];
	$rulename=$_GET["sieve-rule-name"];
	$rulenumber=$_GET["sieve-rule-number"];
	$index=$_GET["sieve-filter-index"];
	$condition=$_GET["condition"];
	$matchType=$_GET["matchType"];
	$matchStr=$_GET["matchStr"];
	$header=$_GET["header"];
	
	
	
	
	$sieve=new clSieve($uid);
	$rules=$sieve->GetRules($rulename);

	if(isset($_GET["control"])){
		$rules[$rulenumber]["control"]=$_GET["control"];
	}
	
	if(isset($_GET["control"])){
		$rules[$rulenumber]["control"]=$_GET["control"];
	}	
	
	
	if(isset($_GET["matchAny"])){
		$rules[$rulenumber]["matchAny"]=$_GET["matchAny"];
	}	
	
	
	
	switch ($condition) {
		case "from":
			$arr["type"]="address";
			$arr["header"]="from";
			break;
			
		case "subject":
			$arr["type"]="header";
			$arr["header"]="subject";
			break;	

		case "tocc":
			$arr["type"]="address";
			$arr["header"][]="to";
			$arr["header"][]="cc";
			break;	

		case "to":
			$arr["type"]="address";
			$arr["header"]="to";
			break;	

		case "header":
			$arr["type"]="header";
			$arr["header"]="$header";
		
		default:
			;
		break;
	}
	
	$arr["matchStr"]=$matchStr;
	$arr["matchType"]=$matchType;
	$rules[$rulenumber]["conditions"][$index]=$arr;
	$content=$sieve->CompileRule($rules,$rulename);
	
	$sieve->SaveRule($rulename,$content);
	
	
	
	
}

//X-SpamTest-Status: SPAM
//X-Spam-Flag YES

?>