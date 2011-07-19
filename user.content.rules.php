<?php
include_once('ressources/class.templates.inc');
session_start();
if(!isset($_SESSION["uid"])){header('location:logon.php');exit;}

if(isset($_GET["ADD_PAGE"])){ADD_PAGE();exit;}
if(isset($_GET["EDIT_PAGE"])){ADD_PAGE();exit;}
if(isset($_GET["DeleteHeaderRule"])){DeleteHeaderRule();exit;}
if(isset($_GET["AddFilterRule"])){AddFilterRule();exit;}
INDEX();


function INDEX(){
	
	$html="<div style='text-align:right'><input type='button' value='&laquo;&nbsp;{add_a_rule}&nbsp;&raquo;' OnClick=\"javascript:AddNewRegxRule();\"></div>".Tableau();
	
	
	
$JS["JS"][]="js/regex.rules.js";
$tpl=new template_users('{content_rules}',$html,0,0,0,0,$JS);
echo $tpl->web_page;		
	
}

function ADD_PAGE(){
	
	
	if(isset($_GET["EDIT_PAGE"])){
		$ldap=new clladp();
		$hash=$ldap->UserDatas($_SESSION["uid"]);	
		if(preg_match('#<header>(.+?)</header><pattern>(.+?)</pattern><regex>(.+?)</regex><action>(.+?)</action>#',$hash["RegexRules"][$_GET["EDIT_PAGE"]],$reg)){
				$header_field=$reg[1];
				$action_value=$reg[4];
				$regex_value=$reg[3];
				$pattern_value=$reg[2];
				$button_name='{edit}';
				$title='{edit_a_rule}';
				$hidden="<input type='hidden' id='edit' value='{$_GET["EDIT_PAGE"]}'>";
		}
		
	}else{
		$button_name="{add}";
		$title="{add_a_rule}";
		}
	
	$fields=Field_array_Hash(ARRAY_HEADERS_FIELD(),'header_field',$header_field);
	
	$array_action=array(
		"quarantine"=>"{user_quarantine}",
		"delete"=>"{user_delete}",
		"pass"=>"{user_skip_antispam}"
	);
	$action=Field_array_Hash($array_action,'action',$action_value);
	
	$html="
	<div style='padding:20px'>
	$hidden
	<H3>$title</H3>
	<p>&nbsp;</p>
	<table style='width:100%'>
	<tr>
		<td align='right'><strong>{select_header_field}</strong>:</td>
		<td>$fields</td>
	<tr>
		<td align='right'><strong>{match_pattern}</strong></td>
		<td>" . Field_text('pattern',$pattern_value) . ":</td>
	</tr>	
	<tr>
		<td nowrap align='right'><strong>{use_regex}</strong>:</td>
		<td>" . Field_numeric_checkbox_img('regex',$regex_value,'{enable_disable}') . "</td>
	</tr>	
	<tr>
		<td nowrap align='right'><strong>{then}</strong>:</td>
		<td>$action</td>
	</tr>			
	<tr>
	<td colspan=2 class='caption'>{star_explain} {only_regex_off}</td>	
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='$button_name&nbsp;&raquo;' OnClick=\"javascript:AddFilterRule();\"</td>	
	</tr>	
	
	
	
	</table>
	
	";
	//ArticaUserFilterRule
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function Tableau(){
$ldap=new clladp();
	$tpl=new templates();
	$hash=$ldap->UserDatas($_SESSION["uid"]);	
	$page=CurrentPageName();
if(is_array($hash["RegexRules"])){
	while (list ($num, $ligne) = each ($hash["RegexRules"]) ){
	
		if(preg_match('#<header>(.+?)</header><pattern>(.+?)</pattern><regex>(.+?)</regex><action>(.+?)</action>#',$ligne,$reg)){
			
		$count=$count+1;
		$html=$html .  "
		<tr>
		<td width=1% valign='top' class='bottom'>" . imgtootltip('rule-16.jpg','{edit}',"EditHeaderRule('$num')")."</td>
		<td valign='top' class='bottom'><strong>{$reg[1]}</strong></td>
		<td valign='top' class='bottom'><code>{$reg[2]}</code></td>
		<td valign='top' class='bottom' width=1% align='center'>{$reg[3]}</td>
		<td valign='top' class='bottom'>{$reg[4]}</td>
		<td valign='top' class='bottom' align='center'>" . imgtootltip('x.gif','{delete}',"MyHref('$page?DeleteHeaderRule=$num')")."</td>
		</tr>
		";		
		}
		}
}else{return "<span style='color:red'>{no_rules}</span>";}
		
if($count>0){
	$html="
	<H4>{rules_list}</h4>
	<table style='width:550px'>
	<tr style='background-color:#005447'>
	<td>&nbsp;</td>
	<td style='color:white;font-weight:bold'>{select_header_field}</td>
	<td style='color:white;font-weight:bold'>{match_pattern}</td>
	<td style='color:white;font-weight:bold'>regex</td>
	<td style='color:white;font-weight:bold'>{then}</td>
	<td>&nbsp;</td>
	</tr>$html</table>
	";
	return $html;
	
}
	
	
}

function ARRAY_HEADERS_FIELD(){
		$datas=file_get_contents(dirname(__FILE__) . '/ressources/databases/db.headers.txt');
		$datas_table=explode("\n",$datas);
		while (list ($num, $ligne) = each ($datas_table) ){
			if($ligne<>null){
				$arr[trim($ligne)]=trim($ligne);
			}
		}	
	$arr[null]="{select}";
	
	return $arr;
}
function AddFilterRule(){
	$ldap=new clladp();
	$tpl=new templates();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	if($_GET["AddFilterRule"]==null){echo $tpl->_ENGINE_parse_body('{error_header}');return null;}
	if($_GET["AddFilterRulePattern"]==null){echo $tpl->_ENGINE_parse_body('{error_pattern}');return null;}
	
	$pattern="<header>{$_GET["AddFilterRule"]}</header><pattern>{$_GET["AddFilterRulePattern"]}</pattern><regex>{$_GET["AddFilterRuleRegex"]}</regex><action>{$_GET["AddFilterRuleAction"]}</action>";
	writelogs("adding/mod $pattern",__FUNCTION__,__FILE__);
	
	if(is_array($hash["RegexRules"])){
		while (list ($num, $ligne) = each ($hash["RegexRules"]) ){
			if(preg_match('#<header>(.+?)</header><pattern>(.+?)</pattern><regex>(.+?)</regex><action>(.+?)</action>#',$ligne)){
				$upd["ArticaUserFilterRule"][]=$ligne;
			}
	}
	}
	if(isset($_GET["EditID"])){
		$upd["ArticaUserFilterRule"][$_GET["EditID"]]=$pattern;
	}else{
	$upd["ArticaUserFilterRule"][]=$pattern;
	}
	if(!$ldap->Ldap_modify($hash["dn"],$upd)){echo $ldap->ldap_last_error;}
	
	
}
function DeleteHeaderRule(){
$ldap=new clladp();
	$tpl=new templates();
	$hash=$ldap->UserDatas($_SESSION["uid"]);
	$num=$_GET["DeleteHeaderRule"];
	$pattern=$hash["RegexRules"][$num];
	writelogs("Delete pattern $pattern/$num",__FUNCTION__,__FILE__);
	$hash["RegexRules"][$num]=null;

if(is_array($hash["RegexRules"])){
		while (list ($num, $ligne) = each ($hash["RegexRules"]) ){
			if(preg_match('#<header>(.+?)</header><pattern>(.+?)</pattern><regex>(.+?)</regex><action>(.+?)</action>#',$ligne)){
				$upd["ArticaUserFilterRule"][]=$ligne;
			}
			}
	}else{
		writelogs("Delete pattern set to default",__FUNCTION__,__FILE__);
		$upd["ArticaUserFilterRule"][]='DEFAULT';}
	
if(count($upd["ArticaUserFilterRule"])==0){$upd["ArticaUserFilterRule"][]='DEFAULT';}

	if(!$ldap->Ldap_modify($hash["dn"],$upd)){echo $ldap->ldap_last_error;}else{INDEX();}
}