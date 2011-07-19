<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.sieve.inc');
session_start();
if(!isset($_SESSION["uid"])){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{SESSION_END}');	
	exit;
	}


if(isset($_GET["SieveAddRuleUser"])){SieveAddRuleUser();exit;}
if(isset($_GET["SieveSelectCondition"])){echo SieveSelectCondition();exit;}
if(isset($_GET["SieveSelectOperator"])){echo SieveSelectOperator($_GET["SieveSelectOperator"]);exit;}
if(isset($_GET["SieveSaveRuleName"])){SieveSaveRuleName();exit;}
if(isset($_GET["SieveLoadRuleUSer"])){echo SieveLoadRuleUSer();exit;}
if(isset($_GET["SieveListRules"])){SieveAddRuleUser();exit;}
if(isset($_POST["rule_number"])){SieveSaveSubRuleNumber();exit;}
if(isset($_GET["SieveSelectAction"])){echo SieveSelectAction();exit;}
if(isset($_GET["SaveNewAction"])){SaveNewAction();exit;}
if(isset($_GET["subrule_id"])){echo FormRule($_GET["ruleid"],$_GET["userid"],$_GET["subrule_id"]);exit;}
if(isset($_GET["DeleteSubRule"])){DeleteSubRule();exit;}
if(isset($_GET["DeleteSubAction"])){DeleteSubAction();exit;}
if(isset($_GET["MoveSubrule"])){MoveSubrule();exit;}
if(isset($_GET["MoveSubAction"])){MoveSubAction();exit;}
if(isset($_GET["subaction_id"])){echo NewAction($_GET["ruleid"],$_GET["userid"]);exit;}
if(isset($_GET["SieveViewScript"])){echo SieveViewScript();exit;}
if(isset($_GET["SieveDeleteMasterRule"])){SieveDeleteMasterRule();exit;}
if(isset($_GET["SieveMoveMasterRule"])){SieveMoveMasterRule();exit;}
if(isset($_GET["SieveGenerateAllScripts"])){echo SieveGenerateAllScripts($_GET["SieveGenerateAllScripts"]);exit();}
if(isset($_GET["SieveSaveToCyrus"])){SieveSaveToCyrus();exit;}


function SieveAddRuleUser(){
	if(isset($_GET["SieveAddRuleUser"])){$userid=$_GET["SieveAddRuleUser"];}
	if(isset($_GET["SieveListRules"])){$userid=$_GET["SieveListRules"];}
	if(isset($_GET["ruleid"])){$ruleid=$_GET["ruleid"];}
	$rulename=$_GET["RuleName"];
	$sieve=new clSieve($userid);

	if(isset($_GET["ruleid"])){
		$ruleid=$_GET["ruleid"];
		$sieve=new clSieve($userid);
		$rulename=$sieve->_params[$ruleid]["RuleName"];
	}
		
	else{$ruleid=$sieve->GetRuleId($rulename);}

$html="
<br>
<div style='padding:3px'>
	<div id=tablist>
		<li><a href=\"javascript:SieveLoadRuleUSer('$userid');\">{sieve rules}</a></li>
		<li><a href=\"javascript:SieveListRules('$userid','$ruleid');\" id=tab_current>$rulename</a></li>
		<li><a href=\"javascript:SieveViewScript('$userid','$ruleid');\">{view_script}</a></li>
	</div>
</div>

<fieldset><legend>
{rule} $rulename</legend>
<input type='hidden' name='userid' value='$userid' id='userid'>
<input type='hidden' name='ruleid' value='$ruleid' id='ruleid'>
<input type='hidden' name='rulname_refer' value='$rulename' id='rulname_refer'>
<form name='FFM'>
<strong>{rule_name}:</strong>&nbsp;" . Field_text('rulename',$rulename,'width:120px') ."&nbsp;<input type=button value='{submit}&nbsp;&raquo;'  style='margin-bottom:0px' OnClick=\"javascript:SieveSaveRuleName();\">
</form>
" . FormRule($ruleid,$userid) .  "<div id=subactions>".NewAction($ruleid,$userid).'</div><hr>' . ListSubRules($ruleid,$userid) . "<hr>" .ListSubActions($ruleid,$userid)."
</fieldset>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}


function FormRule($ruleid,$userid,$number=0){
	if($ruleid==null){return null;}
	$page=CurrentPageName();
	$sieve=new clSieve($userid);
	$title="{add} {find_messages_whose}";
	$button_name='{add}';
	
	if(isset($_GET["subrule_id"])){
		$hash=$sieve->ParseSubRules($ruleid);
		$field_header=$hash[$number]["field_header"];
		$field_conditions=$hash[$number]["field_conditions"];
		$field_andor=$hash[$number]["field_andor"];
		$SieveSelectCondition=SieveSelectCondition($field_header,$field_conditions);
		$SieveSelectOperator=SieveSelectOperator($field_conditions,$hash[$number]["string"]);
		$cancel="<input type='button' value='{cancel}&nbsp;&raquo;' OnClick=\"javascript:SieveListRules('$userid','$ruleid');\">&nbsp;&nbsp;";
		$title='{rule_number}:'.$_GET["subrule_id"];
		$button_name='{edit}';
	}
	$field_or=Field_array_Hash(array('or'=>'{or}','and'=>'{and}'),'field_andor',$field_andor,null,null,null,'width:50px');
	$field_header=Field_array_Hash($sieve->array_field_header,'field_header',$field_header,'SieveSelectCondition()');
	$field_conditions=Field_array_Hash($sieve->array_field_conditions,'field_conditions',$field_conditions);
	$headerstyle="style='width:1%' class='caption' align='center'";	
$html ="
<div id='subrules'>
<form name=FFM1>
<H4>$title</H4>
<input type='hidden' name='ruleid' value='$ruleid'>
<input type='hidden' name='rule_number' value='$number'>
<input type='hidden' name='userid' value='$userid'>
<table style='width:100%;border:1px dotted #8e8785;'>
<tr>
<td $headerstyle><strong>{and}/{or}</strong></td>
<td $headerstyle><strong>{in}</strong></td>
<td $headerstyle><strong>{operator}</strong></td>
<td $headerstyle align='left'><strong>{string}</strong></td>
</tr>
<td style='width:50px'>$field_or</td>
<td style='width:1%'>$field_header</td>
<td style='width:1%' align='right'><div id='fieldConditions' style='width:100%'>$SieveSelectCondition</div></td>
<td width=80%><div id='FieldString'>$SieveSelectOperator</div></td>
</tr>
<tr>
<td colspan=4 align='right'>$cancel<input type='button' value='$button_name&nbsp;&raquo;' OnClick=\"javascript:ParseFormPOST('FFM1','user.sieve.rule.php',true);SieveListRules('$userid','$ruleid')\"></td>
</table>
</form>
</div>";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}

function ListSubRules($ruleid,$userid){
	$sieve=new clSieve($userid);
	$hash=$sieve->ParseSubRules($ruleid);
	if(!is_array($hash)){return null;}
	while (list ($key, $value) = each ($hash)){
		$count=$count+1;
		$andor=$value["field_andor"];
		if($andor==null){$andor='or';}
		if($count==1){$andor=null;};
		
		
		$html=$html . "
		<table style='border:1px dotted #8e8785;padding:3px;margin:4px;width:100%' class=caption
		OnMouseOver=\"this.style.background='#eeeeee';this.style.cursor='pointer'\" 
		OnMouseOut=\"this.style.background='transparent';this.style.cursor='default'\"
		>
		<tr>
		<td width=1%'><img src='img/fw_bold.gif'></td>
		<td width=90% 
		OnClick=\"javascript:LoadSubRule('$key');\"
		>$andor&nbsp;{if} <strong>{$value["field_header"]}</strong> {".$value["field_conditions"] ."} {$value["string"]}</div></td>
		<td width=2% nowrap style='padding-left:5px'>&nbsp;&nbsp;" . imgtootltip('arrow_up.gif','{up}',"MoveSubrule('$key','up')") . "&nbsp;&nbsp;</td>
		<td width=2% nowrap>&nbsp;&nbsp;" . imgtootltip('arrow_down.gif','{down}',"MoveSubrule('$key','down')") . "&nbsp;&nbsp;</td>
		<td width=1%>&nbsp;</td>
		<td width=1%>" . imgtootltip('x.gif','{delete}',"DeleteSubRule('$key');")."</td>
		</tr>
		</table>";
		
	}
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}
function ListSubActions($ruleid,$userid){
	$sieve=new clSieve($userid);
	$hash=$sieve->ParseSubActions($ruleid);	
	if(!is_array($hash)){return null;}
	while (list ($key, $value) = each ($hash)){
		$then=" <strong>{".$value["action"] ."}</strong>  {with} ";
		if($value["action"]==null){$then=null;}
		
		if($value["action"]=="vacation" && $value["operation"]<>null){
			$tpbl=explode('\|\|',$value["operation"]);
			$tpbl[2]=str_replace('<br>',' ',$tpbl[2]);
			$value["operation"]="{every} ".$tpbl[0] . " {days} {subject}:&nbsp;`" . $tpbl[1] . "` <br>{body}: " . substr($tpbl[2],0,50) . '...';
		}
		
		$html=$html . "<table style='border:1px dotted #8e8785;padding:3px;margin:4px;width:100%' class=caption 
		OnMouseOver=\"this.style.background='#eeeeee';this.style.cursor='pointer'\" 
		OnMouseOut=\"this.style.background='transparent';this.style.cursor='default'\">
		<tr>
		<td width=1%' valign='top'><img src='img/fw_bold.gif'></td>
		<td width=90% 
		OnClick=\"javascript:LoadSubAction('$key');\"
		class=caption>
		{then} $then <strong>{$value["operation"]}</strong>
		</td>
		<td width=2% nowrap style='padding-left:5px'>&nbsp;&nbsp;" . imgtootltip('arrow_up.gif','{up}',"MoveSubAction('$key','up')") . "&nbsp;&nbsp;</td>
		<td width=2% nowrap>&nbsp;&nbsp;" . imgtootltip('arrow_down.gif','{down}',"MoveSubAction('$key','down')") . "&nbsp;&nbsp;</td>
		<td width=1%>&nbsp;</td>		
		<td width=1%>" . imgtootltip('x.gif','{delete}',"DeleteSubAction('$key');")."</td>
		</tr>
		</table>";
		
	}
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
}

function NewAction($ruleid,$userid,$action_id=null){
	if($ruleid==null){return null;}
	$sieve=new clSieve($userid);
	$title='{add}';
	$button_name='{add}';
	if(isset($_GET["subaction_id"])){
		$action_id=$_GET["subaction_id"];
		$hash=$sieve->ParseSubActions($ruleid);
		$array=$hash[$action_id];
		$default_action=$array["action"];
		$defaultOp=SieveSelectAction($default_action,$array["operation"]);
		$title='{edit}';
		$cancel="<input type='button' value='{cancel}&nbsp;&raquo;' OnClick=\"javascript:SieveListRules('$userid','$ruleid');\">&nbsp;&nbsp;";
		$button_name='{edit}';
	}

	$html=
	"
	<H4>$title {execute_operation}</H4>
	<input type='hidden' id='ruleid' value='$ruleid'>
	<input type='hidden' id='userid' value='$userid'>
	<input type='hidden' id='action_id' value='$action_id'>
	<table style='width:100%;border:1px dotted #8e8785;'>
	<tr>
	<td nowrap width=5%><strong>{execute_operation}:&nbsp;</strong></td>
	<td>" . Field_array_Hash($sieve->array_field_actions,'action',$default_action,'SieveSelectAction()') . "</td>
	</tr>
	<tr><td></td><td align='left'><div id='action_text'>$defaultOp</div></td></tr>
	<tr><td colspan=2 align='right'>$cancel<input type='button' OnClick=\"javascript:SaveNewAction()\" value='$button_name&nbsp;&raquo;'>
	</tr>
	</table>
	";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);		
}

function SieveSelectCondition($header=null,$default=null){
	$sieve=new clSieve();
	$tpl=new templates();
	if($header==null){$header=$_GET["SieveSelectCondition"];}
	switch ($header) {
		case 'Size':
			return  $tpl->_ENGINE_parse_body(Field_array_Hash($sieve->array_field_conditions_size,'field_conditions',$default,'SieveSelectOperator()'));break;
		case "Header Field":
			return  $tpl->_ENGINE_parse_body(Field_array_Hash($sieve->array_field_headers_message,'field_conditions',$default,'SieveSelectOperator()'));break;
		default:
			return  $tpl->_ENGINE_parse_body(Field_array_Hash($sieve->array_field_conditions,'field_conditions',$default,'SieveSelectOperator()'));break;

	}
	
}
function SieveSelectOperator($header=null,$default=null){
	
	switch ($header){
		case 'is regex':return "<textarea name='string' id='string'>$default</textarea>";break;
		case 'is not regex':return "<textarea name='string' id='string'>$default</textarea>";break;
		default:return Field_text('string',$default,'width:90%');
	}
	
	
}
function SieveSaveRuleName(){
	$refer=$_GET["SaveRuleNameRefer"];
	$rulename=$_GET["SieveSaveRuleName"];
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];
	$sieve=new clSieve($userid);
	if($ruleid==null){
		$ruleid=$sieve->GetRuleId($refer);
	}
	$rulename=replace_accents($rulename);
	$sieve->_params[$ruleid]["RuleName"]=$rulename;
	$sieve->SaveToLdap();
	
}
function SieveLoadRuleUSer(){
	$userid=$_GET["SieveLoadRuleUSer"];
	$sieve=new clSieve($userid);
	if(!is_array($sieve->_params)){return null;}
	
	$html="
	<div style='text-align:right;padding-right:10px'>
	
	<input type='button' value='{apply_scripts_mailbox}&nbsp;&raquo;' OnClick=\"javascript:SieveSaveToCyrus('$userid');\">
	&nbsp;&nbsp;<input type='button' value='{view_script}&nbsp;&raquo;' OnClick=\"javascript:SieveGenerateAllScripts('$userid');\"></div>
	<fieldset>
	<legend>{sieve rules}</legend>";
	while (list ($num, $ligne) = each ($sieve->_params) ){
		if($num<>null){
		$html=$html . "
		<table style='width:100%;border:1px dotted #8e8785;padding:5px' OnMouseOver=\"this.style.background='#eeeeee';this.style.cursor='pointer'\" OnMouseOut=\"this.style.background='transparent';this.style.cursor='default'\">
		<tr >
			<td width=1%>
				<img src='img/rule-20.jpg'>
			</td>
			<td 
			OnClick=\"javascript:SieveListRules('$userid','$num');\"
			style='font: normal 19px/21px 'Helvetica', 'Tahoma', 'Verdana', sans-serif;'>
			<strong>{$ligne["RuleName"]}</strong>
			
			</td>
		<td width=2% nowrap style='padding-left:5px'>&nbsp;&nbsp;" . imgtootltip('arrow_up.gif','{up}',"SieveMoveMasterRule('$userid','$num','up')") . "&nbsp;&nbsp;</td>
		<td width=2% nowrap>&nbsp;&nbsp;" . imgtootltip('arrow_down.gif','{down}',"SieveMoveMasterRule('$userid','$num','down')") . "&nbsp;&nbsp;</td>
		<td width=1%>&nbsp;</td>		
		<td width=1%>" . imgtootltip('x.gif','{delete}',"SieveDeleteMasterRule('$num','$userid');")."</td>			
		</tr>
		</table>
		<br>";
		}
	}
	$html=$html . "</table>
	</fieldset>";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}
function SieveSaveSubRuleNumber(){
	$userid=$_POST["userid"];
	$sieve=new clSieve($_POST["userid"]);

	$ruleid=$_POST["ruleid"];
	$rule_number=$_POST["rule_number"];
	if($rule_number==0){
		$hash=$sieve->_ArraySubRules($ruleid);
		$rule_number=count($hash)+1;
	}
	unset($_POST["userid"]);
	unset($_POST["ruleid"]);
	unset($_POST["rule_number"]);
	while (list ($num, $ligne) = each ($_POST) ){
		$ligne=stripslashes($ligne);
		$ligne=str_replace("\n","<br>",$ligne);
		$rule=$rule . "<$num>$ligne</$num>";
	}
	
	$sieve->_params[$ruleid]["rule.$rule_number"]=$rule;
	$sieve->SaveToLDap();
}







function SieveSelectAction($action=null,$default=null){
	if($action<>null){ $_GET["SieveSelectAction"]=$action;}
	$text="{sieve_" . $_GET["SieveSelectAction"] . "_text}";
	$sieve=new clSieve();
	if($_GET["SieveSelectAction"]=='vacation'){
			if($default<>null){
				$tbl=explode('\|\|',$default);
				$default_days=$tbl[0];
				$default_subject=$tbl[1];
				$default_content=$tbl[2];
				$default_content=str_replace('<br>',"\n",$default_content);
			}else{
				$default_content='{vacation_default}';
				$default_subject='i am in vacation';
				$default_days="7";
			}
	}
	$vacation="
	
	<table style='width:100%'>
	<tr>
	<tr>
	<td align='right'><strong>{every}:&nbsp;</td>
	<td>".Field_text('operations_days',$default_days,'width:50px',null) . "&nbsp;<strong>{days}</strong></td>
	</tr>	
	<td align='right'><strong>{subject}</strong>:&nbsp;</td><td>"  . Field_text('operation_subject',$default_subject,'width:300px',null) . "</td>
	</tr>
	<tr>
	<td colspan=2><strong>{body}</strong>:&nbsp;<br>
	<TEXTAREA NAME='operation_message' id='operation_message' ROWS='12' COLS='50'>$default_content</TEXTAREA>
	</td>
	</table>";
	
	
	
	switch ($_GET["SieveSelectAction"]) {
		case 'reject':$field="<br><textarea id='operation' style='width:100%;height:50px'>$default</textarea>";break;
		case 'fileinto':$field=Field_array_Hash($sieve->LoadImapFoldersList($_GET["userid"]),'operation',$default,null,null,0,'width:300px');break;
		case 'redirect':$field=Field_text('operation',$default,'width:300px',null);break;
		case 'vacation':$field=$vacation;break;
		}
	
	$html="<div class=caption>$text</div>
	<div style='width:100%'>$field</div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);		
	
}
function SaveNewAction(){
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];
	$operation=$_GET["operation"];
	
	if(isset($_GET["operations_days"])){
		$operations_days=$_GET["operations_days"];
		$operation_subject=$_GET["operation_subject"];
		$operation_message=$_GET["operation_message"];
		$operation_message=str_replace("\n","<br>",$operation_message);
		$operation="$operations_days||$operation_subject||$operation_message";
	}
	
	$action=$_GET["SaveNewAction"];
	$action_id=$_GET["action_id"];
	if($action==null){return null;}
	$sieve=new clSieve($userid);
	$hash=$sieve->ParseSubActions($ruleid);
	if($action_id==null){$action_id=count($hash);}
	if($operation==null){$operation='NULL';}
	$action=str_replace("\n","<br>",$action);
	$sieve->_params[$ruleid]["action.".$action_id]="<operation>$operation</operation><action>$action</action>";
	$sieve->SaveToLDap();
	
}
function DeleteSubRule(){
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];	
	$sieve=new clSieve($userid);
	unset($sieve->_params[$ruleid]['rule.'.$_GET["DeleteSubRule"]]);
	$sieve->SaveToLDap();
	}
function DeleteSubAction(){
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];	
	$sieve=new clSieve($userid);
	unset($sieve->_params[$ruleid]['action.'.$_GET["DeleteSubAction"]]);
	$sieve->SaveToLDap();	
	
}
function MoveSubrule(){
	$subrule_id=$_GET["MoveSubrule"];
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];
	$sieve=new clSieve($userid);
	$array=$sieve->_ArraySubRules($ruleid,true);
	$newhash=array_move_element($array,$array[$subrule_id],$_GET["move"]);
	while (list ($num, $ligne) = each ($newhash) ){
		$count=$count+1;
		$sieve->_params[$ruleid]["rule.$count"]=$ligne;
	}
	$sieve->SaveToLDap();
	
	
}
function MoveSubAction(){
	$subrule_id=$_GET["MoveSubAction"];
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];
	$sieve=new clSieve($userid);
	$array=$sieve->_ArraySubActions($ruleid,true);
	$newhash=array_move_element($array,$array[$subrule_id],$_GET["move"]);
	while (list ($num, $ligne) = each ($newhash) ){
		$count=$count+1;
		$sieve->_params[$ruleid]["action.$count"]=$ligne;
	}
	$sieve->SaveToLDap();	
	
}
function SieveDeleteMasterRule(){
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];
	$sieve=new clSieve($userid);
	unset($sieve->_params[$ruleid]);
	$sieve->SaveToLDap();
	}


function SieveViewScript(){
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["SieveViewScript"];	
	$sieve=new clSieve($userid);
	$rulename=$sieve->_params[$ruleid]["RuleName"];
	$script=$sieve->GenerateScripts($ruleid);
	$script=htmlentities($script);
	$script=nl2br($script);
	$script=str_replace("\t","&nbsp;&nbsp;&nbsp;",$script);
	
$html="
<br>
<div style='padding:3px'>
	<div id=tablist>
		<li><a href=\"javascript:SieveLoadRuleUSer('$userid');\">{sieve rules}</a></li>
		<li><a href=\"javascript:SieveListRules('$userid','$ruleid');\" >$rulename</a></li>
		<li><a href=\"javascript:SieveViewScript('$userid','$ruleid');\" id=tab_current>{view_script}</a></li>
	</div>
</div>
<div style='padding:15px'><code>
$script
</code>
</div>
";	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);		
}

function SieveMoveMasterRule(){
	$ruleid=$_GET["ruleid"];
	$userid=$_GET["userid"];	
	$sieve=new clSieve($userid);
	$array=array_move_element($sieve->_params,$sieve->_params[$ruleid],$_GET["move"]);
	$sieve->_params=$array;
	$sieve->SaveToLDap();
	
}
function SieveGenerateAllScripts($userid){
	$sieve=new clSieve($_GET["SieveGenerateAllScripts"]);
	$rules=$sieve->GenerateAllrules($userid);
	$rules=nl2br($rules);
	$rules=str_replace("\t","&nbsp;&nbsp;&nbsp;",$rules);
	$rules="<div style='margin:5px;padding:5px;'><code>$rules</code></div>";
	return $rules;
}
function SieveSaveToCyrus(){
	$userid=$_GET["SieveSaveToCyrus"];
	$sieve=new clSieve($userid);
	$sieve->SaveToSieve();
	 //and $sieve->error_raw[]
	
}


