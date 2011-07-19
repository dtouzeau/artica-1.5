<?php
include_once('ressources/class.procmail.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.ini.inc');


if(isset($_GET["TreeProcMailRules"])){TreeProcMailRules();exit;}
if(isset($_GET["ProcmailAddRule"])){ProcmailAddRule();exit;}
if(isset($_GET["procmail_edit_rule"])){procmail_edit_rule();exit;}
if(isset($_GET["ProcMailRuleMove"])){ProcMailRuleMove();exit;}
if(isset($_GET["ProcMailRuleDelete"])){ProcMailRuleDelete();exit;}
if(isset($_GET["TreeProcMailApplyConfig"])){TreeProcMailApplyConfig();exit;}


function TreeProcMailRules(){
	$usr=new usersMenus();
	$tpl=new Templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}
	
	$html="<fieldset><legend>{procmail_rules}</legend>
		<table>
			<tr>
			<td><input type='button' value='&laquo;&nbsp;{add_new_rule}&nbsp;&raquo;' OnClick=\"javascript:ProcmailAddRule('-1');\"></td>
			</tr>
		</table>
	<br>" . table_rules() . "
	
	</fieldset>";
	
	echo DIV_SHADOW($tpl->_ENGINE_parse_body($html),'windows');
	}
	
function table_rules($noform=0){
	$usr=new usersMenus();
	$tpl=new Templates();
	$Sender=0;
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	$proc=new procmail();
	if(is_array($proc->array_rule)){
	while (list ($num, $ligne) = each ($proc->array_rule) ){
			if($noform==0){
			$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"ProcMailRuleMove($num,'up',$Sender)") ."</td>";
			$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"ProcMailRuleMove($num,'down',$Sender)") ."</td>";
			$cell_del="<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"ProcMailRuleDelete($num,$Sender)") ."</td>";	
			$move="<table style='width:20px'><tr>$cell_up$cell_down$cell_del</tr></table>";
			}
	}
		$html=$html . "
		<table  align='center' style='margin:3px;border:1px dotted #CCCCCC;width:90%'>
		<tr>
		<td width=1%><img src='img/alias-18.gif'></td>
		<td width=90%><strong><a href=\"javascript:ProcmailAddRule('$num');\">{$ligne["name"]}:</strong></a></td>
		<td>$move</td>
		</tr>		
		<tr>
		<td>&nbsp;</td>
		<td style='padding-left:5px' colspan=2><i>{if} <strong>{$ligne["header_name"]}</strong> {$proc->condition_array[$ligne["condition"]]} <strong>{$ligne["string"]}</strong> {then} {$proc->action_array[$ligne["action"]]}</i></td>
		</tr>
		</table>";
		
		
	}
	return $tpl->_ENGINE_parse_body("<div id='rules'>$html</div>");
}
	
	
function procmail_edit_rule(){
	$rule_id=$_GET["procmail_edit_rule"];
	$tpl=new templates();
	unset($_GET["procmail_edit_rule"]);
	if($_GET["name"]==null){echo $tpl->_ENGINE_parse_body('{form_error} ->{rule_name}');exit; }
	if($_GET["string"]==null){echo $tpl->_ENGINE_parse_body('{form_error} ->{pattern}');exit; }
	$proc=new procmail();
	if($rule_id==-1){
		$proc->array_rule[]=$_GET;
	}else{$proc->array_rule[$rule_id]=$_GET;}
	if($proc->save_rules()){echo $tpl->_ENGINE_parse_body('{success}');}
	
}

function ProcmailAddRule(){
$usr=new usersMenus();
	$tpl=new Templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	$proc=new procmail();
	$proc->header_database[null]='{select}';
	$if=Field_array_Hash($proc->condition_array,'condition',null);
	
	$rule_id=$_GET["ProcmailAddRule"];
	$proc=new procmail();
	$hash=$proc->array_rule[$rule_id];
	if($_GET["ProcmailAddRule"]==-1){$title='{add_rule}';}else{$title="{edit_rule} {$_GET["ProcmailAddRule"]}";
	$subtitle="<div style='font-size:12px;border-bottom:1px solid #005447;width:100%;font-wieght:bolder;color:#005447;margin-bottom:5px;'><strong>{$hash["name"]}</strong></div>";
	}
	$header_name=Field_array_Hash($proc->header_database,'header_name',$hash["header_name"]);
	
$html="<fieldset><legend>$title</legend>
<form name='proc_rule'>
	<input type='hidden' name='procmail_edit_rule' value='{$_GET["ProcmailAddRule"]}'>
		<table>
			<tr>
			<td><input type='button' value='&laquo;&nbsp;{manage_procmail_rules}&nbsp;&raquo;' OnClick=\"javascript:TreeProcMailRules();\"></td>
			</tr>
			
		</table><br>
		$subtitle
		<table style='margin:3px;border:1px solid #CCCCCC;padding:5px;width:99%'>
		<tr>
		</tr>
		<tr><td colspan=3><strong>{rule_name}</strong>:" . Field_text('name',$hash["name"],'width:70%') . "</td>
		</tr>		
		<td><strong>{if_in}</strong></td>
		<td>$header_name</td>
		 <td>$if</td>
		</tr>
		<tr><td colspan=3><strong>{pattern}</strong>:" . Field_text('string',$hash["string"],'width:80%') . "<br><code>*,?&nbsp;&nbsp;{supported}</code></td>
		</tr>
		<tr>
		<td><strong>{then}:</strong></td>
		<td colspan=2>" . Field_array_Hash($proc->action_array,"action",$hash["action"]) . "</td>
		</table>
		</form>
		<div style='padding-right:10px;text-align:right' align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('proc_rule','procmail.functions.php',true);\"></div>
	
	
	</fieldset>";	

echo DIV_SHADOW($tpl->_ENGINE_parse_body($html),'windows');
}

function ProcMailRuleMove(){
	$usr=new usersMenus();
	$tpl=new Templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	$proc=new procmail();
	if($_GET["direction"]=='up'){
		$proc->array_rule=array_move_element($proc->array_rule,$proc->array_rule[$_GET["ProcMailRuleMove"]],'up');
		
	}else{
	$proc->array_rule=array_move_element($proc->array_rule,$proc->array_rule[$_GET["ProcMailRuleMove"]],'down');	
	}
		
	$proc->save_rules();
	echo table_rules();
}
function ProcMailRuleDelete(){
	$usr=new usersMenus();
	$tpl=new Templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$proc=new procmail();
	unset($proc->array_rule[$_GET["ProcMailRuleDelete"]]);
	$proc->save_rules();
	echo table_rules();
}
function TreeProcMailApplyConfig(){
$usr=new usersMenus();
	$tpl=new Templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}
	$proc=new procmail();
	$proc->SaveItToDisk();
	echo $tpl->_ENGINE_parse_body('{success}');exit;}	

?>