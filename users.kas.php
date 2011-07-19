<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.kas-filter.inc');
include_once('ressources/class.groups.inc');

	if($_SESSION["uid"]<>-100){
	$tpl=new template_users();
	$priv=$tpl->_ParsePrivieleges($_SESSION["privileges"]["ArticaGroupPrivileges"]);
	if($priv["AllowChangeKas"]<>'yes'){exit;}
	}
	
if(!isset($_GET["tab"])){$_GET["tab"]=0;}
if(isset($_GET["ACTION_SPAM_MODE"])){SaveActions();exit;}
if(isset($_GET["OPT_DNS_DNSBL"])){SaveActionsRules();exit;}
switch ($_GET["tab"]) {
	case 0:Actions_Group();exit;break;
	case 1:DNSSPFChecks();exit;break;
	default:Actions_Group();exit;break;
}



function Tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;}
	$gidNumber=$_GET["gid"];
	$group=new groups($_GET["gid"]);
	
	$page=CurrentPageName();
	$array[]="$group->groupName:&nbsp;{ActionsGroup}";
	$array[]="$group->groupName:&nbsp;{group rules}";

	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadKasTab('$num','$gidNumber');\" $class>$ligne</a></li>\n";
			
		}
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html,'kas.group.rules.php');
	return "<br><div id=tablist>$html</div>";	
	}
	
	
function  DNSSPF_Tabs(){
	if(!isset($_GET["DNSSPFTAB"])){$_GET["DNSSPFTAB"]=0;}
	$gidNumber=$_GET["gid"];
	$array[]='{rulegeneral}';
	$array[]="{DNSSPFChecks}";
	$array[]="{HeadersChecks}";
	$array[]="{EasternEncodings}";
	$array[]="{RuleObsceneContent}";
	//$array[]="{source config}";
while (list ($num, $ligne) = each ($array) ){
		if($_GET["DNSSPFTAB"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadDNSSPFTAB('$num','$gidNumber','{$_GET["TreeKasSelect"]}');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
	
}
	

	
function GetDatas(){
if(isset($_GET["MEMORY_DATAS"])){return $_GET["MEMORY_DATAS"];}
	$ldap=new clladp();
	$HashGroup=$ldap->GroupDatas($_GET["gid"]);	
	$datas_group=$HashGroup["KasperkyASDatasRules"];
	$kas=new kas_groups();
	$_GET["MEMORY_DATAS"]=$kas->ParseRules($datas_group);	
	return $_GET["MEMORY_DATAS"];
	}

function DNSSPF_DNSSPFChecks($hidden=1){
	$array_datas=GetDatas();
	$OPT_DNS_DNSBL=Field_checkbox('OPT_DNS_DNSBL','1',$array_datas["OPT_DNS_DNSBL"]);
	$OPT_DNS_HOST_IN_DNS=Field_checkbox('OPT_DNS_HOST_IN_DNS','1',$array_datas["OPT_DNS_HOST_IN_DNS"]);
	$OPT_SPF=Field_checkbox('OPT_SPF','1',$array_datas["OPT_SPF"]);
	if($hidden==1){
		return Field_hidden("OPT_DNS_DNSBL",$array_datas["OPT_DNS_DNSBL"]).
			Field_hidden("OPT_DNS_HOST_IN_DNS",$array_datas["OPT_DNS_HOST_IN_DNS"]).
			Field_hidden("OPT_SPF",$array_datas["OPT_SPF"]);
	}

$html="<H5>{DNSSPFChecks}</H5>
		<table style='width:90%'>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{UseofDNSBLservices}:</strong></td>
				<td align='left'>$OPT_DNS_DNSBL</td>
			</tr>			
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_7}:</strong></td>
				<td align='left'>$OPT_DNS_HOST_IN_DNS<span style='font-size9px;padding-left:4px;'><i>{option_8}</i></span></td>
			</tr>		
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_9}:</strong></td>
				<td align='left'>$OPT_SPF</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>";
return RoundedLightGrey($html);
}

function DNSSPF_EasternEncodings($hidden=1){
	
	$array_datas=GetDatas();
	$OPT_LANG_CHINESE=Field_checkbox('OPT_LANG_CHINESE','1',$array_datas["OPT_LANG_CHINESE"]);
	$OPT_LANG_KOREAN=Field_checkbox('OPT_LANG_KOREAN','1',$array_datas["OPT_LANG_KOREAN"]);
	$OPT_LANG_THAI=Field_checkbox('OPT_LANG_THAI','1',$array_datas["OPT_LANG_THAI"]);
	$OPT_LANG_JAPANESE=Field_checkbox('OPT_LANG_JAPANESE','1',$array_datas["OPT_LANG_JAPANESE"]);	
	
	if($hidden==1){
		return Field_hidden("OPT_LANG_CHINESE",$array_datas["OPT_LANG_CHINESE"]).
			Field_hidden("OPT_LANG_KOREAN",$array_datas["OPT_LANG_KOREAN"]).
			Field_hidden("OPT_LANG_THAI",$array_datas["OPT_LANG_THAI"])
			.Field_hidden("OPT_LANG_JAPANESE",$array_datas["OPT_LANG_JAPANESE"]);
	}
	
	
$html="
	<H5>{EasternEncodings}</H5>
	<i>{EasternEncodings_explain}</i>
		<table style='width:90%'>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{option_16}:</strong></td>
				<td align='left'>$OPT_LANG_CHINESE</td>
			</tr>			
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{option_17}:</strong></td>
				<td align='left'>$OPT_LANG_KOREAN</td>
			</tr>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{option_18}:</strong></td>
				<td align='left'>$OPT_LANG_THAI</td>
			</tr>	
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{option_19}:</strong></td>
				<td align='left'>$OPT_LANG_JAPANESE</td>
			</tr>								
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>	";

return RoundedLightGrey($html);
}

function DNSSPF_HeadersChecks($hidden=1){
	$array_datas=GetDatas();
	$OPT_HEADERS_TO_UNDISCLOSED=Field_checkbox('OPT_HEADERS_TO_UNDISCLOSED','1',$array_datas["OPT_HEADERS_TO_UNDISCLOSED"]);
	$OPT_HEADERS_FROM_OR_TO_DIGITS=Field_checkbox('OPT_HEADERS_FROM_OR_TO_DIGITS','1',$array_datas["OPT_HEADERS_FROM_OR_TO_DIGITS"]);
	$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN=Field_checkbox('OPT_HEADERS_FROM_OR_TO_NO_DOMAIN','1',$array_datas["OPT_HEADERS_FROM_OR_TO_NO_DOMAIN"]);
	$OPT_HEADERS_SUBJECT_TOO_LONG=Field_checkbox('OPT_HEADERS_SUBJECT_TOO_LONG','1',$array_datas["OPT_HEADERS_SUBJECT_TOO_LONG"]);
	$OPT_HEADERS_SUBJECT_WS_OR_DOTS=Field_checkbox('OPT_HEADERS_SUBJECT_WS_OR_DOTS','1',$array_datas["OPT_HEADERS_SUBJECT_WS_OR_DOTS"]);
	$OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID=Field_checkbox('OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID','1',$array_datas["OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID"]);
	
if($hidden==1){
		return Field_hidden("OPT_HEADERS_TO_UNDISCLOSED",$array_datas["OPT_HEADERS_TO_UNDISCLOSED"]).
			Field_hidden("OPT_HEADERS_FROM_OR_TO_DIGITS",$array_datas["OPT_HEADERS_FROM_OR_TO_DIGITS"]).
			Field_hidden("OPT_HEADERS_FROM_OR_TO_NO_DOMAIN",$array_datas["OPT_HEADERS_FROM_OR_TO_NO_DOMAIN"])
			.Field_hidden("OPT_HEADERS_SUBJECT_TOO_LONG",$array_datas["OPT_HEADERS_SUBJECT_TOO_LONG"])
			.Field_hidden("OPT_HEADERS_SUBJECT_WS_OR_DOTS",$array_datas["OPT_HEADERS_SUBJECT_WS_OR_DOTS"])
			.Field_hidden("OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID",$array_datas["OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID"])
			;
	}	
	
$html="		<H5>{HeadersChecks}</H5>
		<table style='width:90%'>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{option_10}:</strong></td>
				<td align='left'>$OPT_HEADERS_TO_UNDISCLOSED</td>
			</tr>			
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_11}:</strong></td>
				<td align='left'>$OPT_HEADERS_FROM_OR_TO_DIGITS</td>
			</tr>		
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_12}:</strong></td>
				<td align='left'>$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN</td>
			</tr>	
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_13}:</strong></td>
				<td align='left'>$OPT_HEADERS_SUBJECT_TOO_LONG</td>
			</tr>	
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_14}:</strong></td>
				<td align='left'>$OPT_HEADERS_SUBJECT_WS_OR_DOTS</td>
			</tr>	
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_15}:</strong></td>
				<td align='left' valign='middle'>$OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID</td>
			</tr>										
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>";
return RoundedLightGrey($html);
}
function DNSSPF_RuleObsceneContent($hidden=1){
$array_datas=GetDatas();
$OPT_CF_OBSCENE=Field_checkbox('OPT_CF_OBSCENE','1',$array_datas["OPT_CF_OBSCENE"]);	
if($hidden==1){return Field_hidden('OPT_CF_OBSCENE',$array_datas["OPT_CF_OBSCENE"]);}
$html="

<H5>{RuleObsceneContent}</H5>
	<i>{RuleObsceneContent_text}</i>
		<table style='width:90%'>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{option_20}:</strong></td>
				<td align='left'>$OPT_CF_OBSCENE</td>
			</tr>
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>	
		</table>
</fieldset>	";
return RoundedLightGrey($html);
}
	
function DNSSPF_rulegeneral($hidden=1){
		$array_datas=GetDatas();
		$array_spam_rate=array(1=>'{minimum}',2=>"{standardr}",3=>"{high}");
		$OPT_FILTRATION_ON=Field_checkbox('OPT_FILTRATION_ON','1',$array_datas["OPT_FILTRATION_ON"]);
		$OPT_SPAM_RATE_LIMIT=Field_array_Hash($array_spam_rate,'OPT_SPAM_RATE_LIMIT',$array_datas["OPT_SPAM_RATE_LIMIT"]);
		$OPT_PROBABLE_SPAM_ON=Field_checkbox('OPT_PROBABLE_SPAM_ON','1',$array_datas["OPT_PROBABLE_SPAM_ON"]);
		$OPT_USE_DNS=Field_checkbox('OPT_USE_DNS','1',$array_datas["OPT_USE_DNS"]);
		$OPT_USE_SURBL=Field_checkbox('OPT_USE_SURBL','1',$array_datas["OPT_USE_SURBL"]);
		$OPT_USE_LISTS=Field_checkbox('OPT_USE_LISTS','1',$array_datas["OPT_USE_LISTS"]);
		
		if($hidden==1){
			return Field_hidden("OPT_FILTRATION_ON",$array_datas["OPT_FILTRATION_ON"]).
			Field_hidden("OPT_SPAM_RATE_LIMIT",$array_datas["OPT_SPAM_RATE_LIMIT"]).
			Field_hidden("OPT_PROBABLE_SPAM_ON",$array_datas["OPT_PROBABLE_SPAM_ON"]).
			Field_hidden("OPT_USE_DNS",$array_datas["OPT_USE_DNS"]).
			Field_hidden("OPT_USE_SURBL",$array_datas["OPT_USE_SURBL"]).
			Field_hidden("OPT_USE_LISTS",$array_datas["OPT_USE_LISTS"]);
			}
		
	$html="
<H5>{rulegeneral}</H5>
		<table style='width:90%'>
			<tr >
				<td align='right' nowrap style='width:1%' valign='top'><strong>{detection}:</strong></td>
				<td align='left'>$OPT_FILTRATION_ON&nbsp;
				<span style='font-size9px;padding-left:4px;'><i>{option_21}</i></span></td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%' valign='top'><strong>{detection} {level}:</strong></td>
				<td align='left'>$OPT_SPAM_RATE_LIMIT<br>
				<span style='font-size9px;padding-left:4px;'><i>{option_22}</i></span></td>
			</tr>			
			<tr>
				<td align='right'  style='width:1%' nowrap ><strong>{option_23}:</strong></td>
				<td align='left'>$OPT_PROBABLE_SPAM_ON</td>
			</tr>		
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_24}:</strong></td>
				<td align='left'>$OPT_USE_DNS&nbsp;
				<span style='font-size9px;padding-left:4px;'><i>{option_25}</i></span></td>
			</tr>
			
			<tr>
				<td align='right'  style='width:1%' nowrap valign='top'><strong>{option_26}:</strong></td>
				<td align='left'>$OPT_USE_SURBL&nbsp;
				<span style='font-size9px;padding-left:4px;'><i>{option_27}</i></span></td>
			</tr>			
			<tr >
				<td align='right' nowrap style='width:1%' valign='top'><strong>{option_28}:</strong></td>
				<td align='left'>$OPT_USE_LISTS</td>
			</tr>					
			
				
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>";	
	return RoundedLightGrey($html);
}
	
function DNSSPF_Source($hidden=1){
$array_datas=GetDatas();
$gidNumber=$_GET["gid"];	
if($hidden==1){return null;}
while (list ($num, $val) = each ($array_datas) ){
	$html=$html . "$num=$val<br>";
}

return "{source config} ($gidNumber)</H5><code>$html</code></fieldset>";
	
}
function DNSSPFChecks(){
	$array_datas=GetDatas();
	
	if(!isset($_GET["DNSSPFTAB"])){$_GET["DNSSPFTAB"]=0;}
	$array[0]=1;
	$array[1]=1;
	$array[2]=1;
	$array[3]=1;
	$array[4]=1;
	$array[5]=1;
	$array[$_GET["DNSSPFTAB"]]=0;
	$html=tabs() ."<br>".DNSSPF_Tabs() ."





<form name='FFM'>
<input type='hidden' name='gidnumber' id='gidnumber' value='{$_GET["gid"]}'>
<table style='width:100%;'>


	<td valign='top' width=1%>
	<!-- <img src='img/no-spam.gif' style='margin-top:5px;'> -->
	</td>
	<td valign='top' style='padding-top:10px;'>" . 
	DNSSPF_rulegeneral($array[0]).
	DNSSPF_DNSSPFChecks($array[1]).
	DNSSPF_HeadersChecks($array[2]).
	DNSSPF_EasternEncodings($array[3]).
	DNSSPF_RuleObsceneContent($array[4]).
	DNSSPF_Source($array[5]).
	
	"</td>
	</tr>
	</table>
	</form>";

$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo $html;
	
}


function Actions_Group_datas(){
	$gid=$_GET["gid"];
	if(isset($_GET["MEMORY_DATAS"])){return $_GET["MEMORY_DATAS"];}	
	$ldap=new clladp();
	$HashGroup=$ldap->GroupDatas($_GET["gid"]);
	$actions_group=$HashGroup["KasperkyASDatas"];	
	$kas=new kas_groups($actions_group);
	$_GET["MEMORY_DATAS"]=$array_datas=$kas->array_datas;
	return $_GET["MEMORY_DATAS"];
	}
	
function  Actions_Group_Tabs(){
	if(!isset($_GET["ACTIONGROUPTAB"])){$_GET["ACTIONGROUPTAB"]=0;}
	$array[]='{spam option 1r}';
	$array[]="{spam option 2r}";
	$array[]="{spam option 3r}";
	$array[]="{spam option 4r}";
	$array[]="{spam option 5r}";
	$array[]="{spam option 6r}";
while (list ($num, $ligne) = each ($array) ){
		if($_GET["ACTIONGROUPTAB"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadACTIONGROUPTAB('$num','{$_GET["gid"]}','{$_GET["TreeKasSelect"]}');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
	
}	
	

	
function Actions_Group_SpamOption6($hidden=1){	
$array_datas=Actions_Group_datas();	

if($hidden==1){
			return Field_hidden("ACTION_TRUSTED_SUBJECT_PREFIX",$array_datas["ACTION_NORMAL_SUBJECT_PREFIX"]).
			Field_hidden("ACTION_TRUSTED_USERINFO",$array_datas["ACTION_NORMAL_USERINFO"]);}

$html="
<H5>{spam option 6}</H5>
		<input type='hidden' id='ACTION_NORMAL_MODE' value='0' name='ACTION_NORMAL_MODE'>
		
		<table style='width:90%'>
	
				
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_NORMAL_SUBJECT_PREFIX',$array_datas["ACTION_NORMAL_SUBJECT_PREFIX"]) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_NORMAL_USERINFO',$array_datas["ACTION_NORMAL_USERINFO"]) . "</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>					
		";
return RoundedLightGrey($html);

}
	
function Actions_Group_SpamOption5($hidden=1){

	$array_datas=Actions_Group_datas();
	if($hidden==1){
			return Field_hidden("ACTION_TRUSTED_SUBJECT_PREFIX",$array_datas["ACTION_TRUSTED_SUBJECT_PREFIX"]).
			Field_hidden("ACTION_TRUSTED_USERINFO",$array_datas["ACTION_TRUSTED_USERINFO"]);}

	$html="
<H5>{spam option 5}</H5>
		<input type='hidden' id='ACTION_TRUSTED_MODE' value='0' name='ACTION_TRUSTED_MODE'>
		
		<table style='width:90%'>
	
				
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_TRUSTED_SUBJECT_PREFIX',$array_datas["ACTION_TRUSTED_SUBJECT_PREFIX"]) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_TRUSTED_USERINFO',$array_datas["ACTION_TRUSTED_USERINFO"]) . "</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>		
	";
	return RoundedLightGrey($html);
	
}
	
function Actions_Group_SpamOption4($hidden=1){
$array_datas=Actions_Group_datas();
	$action_message=array(0=>"{acceptmessage}",1=>"{kassendcopy}",2=>"{kasforward}",-1=>"{kasreject}",-3=>"{kasdelete}");	
	$ACTION_FORMAL_MODE=Field_array_Hash($action_message,'ACTION_FORMAL_MODE',$array_datas["ACTION_FORMAL_MODE"],"Change_ACTION_MODE('ACTION_FORMAL')");
	
if($hidden==1){
			return Field_hidden("ACTION_FORMAL_MODE",$array_datas["ACTION_FORMAL_MODE"]).
			Field_hidden("ACTION_FORMAL_EMAIL",$array_datas["ACTION_FORMAL_EMAIL"]).
			Field_hidden("ACTION_FORMAL_SUBJECT_PREFIX",$array_datas["ACTION_FORMAL_SUBJECT_PREFIX"]).
			Field_hidden("ACTION_FORMAL_USERINFO",$array_datas["ACTION_FORMAL_USERINFO"]);
			
	}

$html="		<H5>{spam option 4}</H5>
		<table style='width:90%'>
			<tr >
				<td colspan=2>$ACTION_FORMAL_MODE</td>
			</tr>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{sendto}:</strong></td>
				<td align='left'>" . Field_text('ACTION_FORMAL_EMAIL',$array_datas["ACTION_FORMAL_EMAIL"]) . "</td>
			</tr>			
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_FORMAL_SUBJECT_PREFIX',$array_datas["ACTION_FORMAL_SUBJECT_PREFIX"]) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_FORMAL_USERINFO',$array_datas["ACTION_FORMAL_USERINFO"]) . "</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>	";	

return RoundedLightGrey($html);

}

function Actions_Group_SpamOption3($hidden=1){
$array_datas=Actions_Group_datas();
	$action_message=array(0=>"{acceptmessage}",1=>"{kassendcopy}",2=>"{kasforward}",-1=>"{kasreject}",-3=>"{kasdelete}");	
	$ACTION_BLACKLISTED_MODE=Field_array_Hash($action_message,'ACTION_BLACKLISTED_MODE',$array_datas["ACTION_BLACKLISTED_MODE"],"Change_ACTION_MODE('ACTION_BLACKLISTED')");
	
	if($hidden==1){return Field_hidden("ACTION_BLACKLISTED_MODE",$array_datas["ACTION_BLACKLISTED_MODE"]).
			Field_hidden("ACTION_BLACKLISTED_EMAIL",$array_datas["ACTION_BLACKLISTED_EMAIL"]).
			Field_hidden("ACTION_BLACKLISTED_SUBJECT_PREFIX",$array_datas["ACTION_BLACKLISTED_SUBJECT_PREFIX"]).
			Field_hidden("ACTION_BLACKLISTED_USERINFO",$array_datas["ACTION_BLACKLISTED_USERINFO"]);
			}	
	
	
$html="<H5>{spam option 3}</H5>
		<table style='width:90%'>
			<tr >
				<td colspan=2>$ACTION_BLACKLISTED_MODE</td>
			</tr>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{sendto}:</strong></td>
				<td align='left'>" . Field_text('ACTION_BLACKLISTED_EMAIL',$array_datas["ACTION_BLACKLISTED_EMAIL"]) . "</td>
			</tr>			
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_BLACKLISTED_SUBJECT_PREFIX',$array_datas["ACTION_BLACKLISTED_SUBJECT_PREFIX"]) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_BLACKLISTED_USERINFO',$array_datas["ACTION_BLACKLISTED_USERINFO"]) . "</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>";	

return RoundedLightGrey($html);
}
	
function Actions_Group_SpamOption2($hidden=1){
	$array_datas=Actions_Group_datas();
	$action_message=array(0=>"{acceptmessage}",1=>"{kassendcopy}",2=>"{kasforward}",-1=>"{kasreject}",-3=>"{kasdelete}");
	$ACTION_PROBABLE_MODE=Field_array_Hash($action_message,'ACTION_PROBABLE_MODE',$array_datas["ACTION_PROBABLE_MODE"],"Change_ACTION_MODE('ACTION_PROBABLE')");	
	
if($hidden==1){
			return Field_hidden("ACTION_PROBABLE_MODE",$array_datas["ACTION_PROBABLE_MODE"]).
			Field_hidden("ACTION_PROBABLE_EMAIL",$array_datas["ACTION_PROBABLE_EMAIL"]).
			Field_hidden("ACTION_PROBABLE_SUBJECT_PREFIX",$array_datas["ACTION_PROBABLE_SUBJECT_PREFIX"]).
			Field_hidden("ACTION_PROBABLE_USERINFO",$array_datas["ACTION_PROBABLE_USERINFO"]);
			
	}	
	
	
$html="		<H5>{spam option 2}</H5>
		<table style='width:90%'>
			<tr >
				<td colspan=2>$ACTION_PROBABLE_MODE</td>
			</tr>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{sendto}:</strong></td>
				<td align='left'>" . Field_text('ACTION_PROBABLE_EMAIL',$array_datas["ACTION_PROBABLE_EMAIL"]) . "</td>
			</tr>			
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_PROBABLE_SUBJECT_PREFIX',$array_datas["ACTION_PROBABLE_SUBJECT_PREFIX"]) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_PROBABLE_USERINFO',$array_datas["ACTION_PROBABLE_USERINFO"]) . "</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>";	
return RoundedLightGrey($html);
	
}
	
function Actions_Group_spamOption1($hidden=1){
	
	$array_datas=Actions_Group_datas();
	$action_message=array(0=>"{acceptmessage}",1=>"{kassendcopy}",2=>"{kasforward}",-1=>"{kasreject}",-3=>"{kasdelete}");
	$ACTION_SPAM_MODE=Field_array_Hash($action_message,'ACTION_SPAM_MODE',$array_datas["ACTION_SPAM_MODE"],"Change_ACTION_MODE('ACTION_SPAM')");	
	
	if($hidden==1){
			return Field_hidden("ACTION_SPAM_MODE",$array_datas["ACTION_SPAM_MODE"]).
			Field_hidden("OPT_SPAM_RATE_LIMIT",$array_datas["OPT_SPAM_RATE_LIMIT"]).
			Field_hidden("ACTION_SPAM_EMAIL",$array_datas["ACTION_SPAM_EMAIL"]).
			Field_hidden("ACTION_SPAM_SUBJECT_PREFIX",$array_datas["ACTION_SPAM_SUBJECT_PREFIX"]).
			Field_hidden("ACTION_SPAM_USERINFO",$array_datas["ACTION_SPAM_USERINFO"]);
	}
	
	$html="
			<H5>{spam option 1}</H5>
		<table style='width:90%'>
			<tr >
				<td colspan=2>$ACTION_SPAM_MODE</td>
			</tr>
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{sendto}:</strong></td>
				<td align='left'>" . Field_text('ACTION_SPAM_EMAIL',$array_datas["ACTION_SPAM_EMAIL"]) . "</td>
			</tr>			
			<tr>
				<td align='right' nowrap style='width:1%'><strong>{prepend to the subject}:</strong></td>
				<td align='left'>" . Field_text('ACTION_SPAM_SUBJECT_PREFIX',$array_datas["ACTION_SPAM_SUBJECT_PREFIX"]) . "</td>
			</tr>		
			<tr >
				<td align='right' nowrap style='width:1%'><strong>{xspamtest}:</strong></td>
				<td align='left'>" . Field_text('ACTION_SPAM_USERINFO',$array_datas["ACTION_SPAM_USERINFO"]) . "</td>
			</tr>	
			<tr><td colspan=2 align='right' style='padding-left:15px'><input type='button' value='{save}' OnClick=\"javascript:SaveActions('{$_GET["gid"]}');\"></td></tr>
		</table>
		</fieldset>";
	
	
	return RoundedLightGrey($html);
}



function Actions_Group(){
	$array_datas=Actions_Group_datas();
	$gidNumber=$_GET["gid"];
	
	
	
	if($gidNumber==-100){return null;}
	if(!isset($_GET["ACTIONGROUPTAB"])){$_GET["ACTIONGROUPTAB"]=0;}
	$array[0]=1;
	$array[1]=1;
	$array[2]=1;
	$array[3]=1;
	$array[4]=1;
	$array[5]=1;
	$array[$_GET["ACTIONGROUPTAB"]]=0;	
		
$html=tabs() ."<br><div style='margin:5px'>" . Actions_Group_Tabs() . "</div>

<form name='FFM'>
<input type='hidden' name='ACTION_PROBABLE_SUBJECT_SUFFIX' id='ACTION_PROBABLE_SUBJECT_SUFFIX' value=''>
<input type='hidden' name='ACTION_SPAM_SUBJECT_SUFFIX' id='ACTION_SPAM_SUBJECT_SUFFIX' value=''>
<input type='hidden' name='ACTION_BLACKLISTED_SUBJECT_SUFFIX' id='ACTION_BLACKLISTED_SUBJECT_SUFFIX' value=''>
<input type='hidden' name='ACTION_FORMAL_SUBJECT_SUFFIX' id='ACTION_FORMAL_SUBJECT_SUFFIX' value=''>
<input type='hidden' name='ACTION_TRUSTED_SUBJECT_SUFFIX' id='ACTION_TRUSTED_SUBJECT_SUFFIX' value=''>
<input type='hidden' name='ACTION_NORMAL_SUBJECT_SUFFIX' id='ACTION_NORMAL_SUBJECT_SUFFIX' value=''>
<input type='hidden' name='gidnumber' id='gidnumber' value='$gidNumber'>
<table style='width:100%;'>
	<td valign='top' width=1%>
	<!-- <img src='img/no-spam.gif' style='margin-top:5px;'> -->
	</td>
	<td valign='top'>
		<h3 style='font-size:14px'><i>{ActionsGroupDESC}</i></h3>" . 
		Actions_Group_spamOption1($array[0]).
		Actions_Group_SpamOption2($array[1]).
		Actions_Group_SpamOption3($array[2]).
		Actions_Group_SpamOption4($array[3]).
		Actions_Group_SpamOption5($array[4]).
		Actions_Group_SpamOption6($array[5]).

	"
		
		
	</td>
	</tr>
	</table>
	<script>
		Change_ACTION_MODE('ACTION_SPAM');
		Change_ACTION_MODE('ACTION_PROBABLE');
		Change_ACTION_MODE('ACTION_BLACKLISTED');
		Change_ACTION_MODE('ACTION_FORMAL');
	</script>
	</form>
";
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
echo $html;
	


}

function SaveActions(){
	$gidNumber=$_GET["gidnumber"];
	unset($_GET["gidnumber"]);
	$ldap=new clladp();
	$HashGroup=$ldap->GroupAllDatas($gidNumber);
	$DN=	$HashGroup["dn"];	
	$tpl=new templates();
	$kas=new kas_groups();
	
	$kas->array_datas=$_GET;
	$SettingsDatas=$kas->SaveArray();
	$HashGroup["KasperskyASGroupNumber"]=$gidNumber;
		
		$action_def=$kas->BuildActionFile($HashGroup,$SettingsDatas);
			
		if(!is_array_key('kasperkyasdatas',$HashGroup)){
			$hash_add_array2["KasperkyASDatas"]=$action_def;
			$hash_add_array2["KasperskyASGroupNumber"]=$gidNumber;
			$ldap->Ldap_add_mod($DN,$hash_add_array2);
			}
	
		
		$error=$ldap->ldap_last_error;
		$hash_update_array["KasperkyASDatas"]=$action_def;
		$hash_update_array["KasperskyASGroupNumber"]=$gidNumber;
		
		$ldap->Ldap_modify($DN,$hash_update_array);
		
		$kas=new kas_groups($action_def);
		$kas->PrepareFiles($gidNumber);
		
		
		$error=$error . " ".$ldap->ldap_last_error;
		if(strlen($error)<5){
			$error="{success}";
		}
		
		echo $tpl->_ENGINE_parse_body($error);
	}
	

	
function SaveActionsRules(){
	$gidNumber=$_GET["gidnumber"];
	unset($_GET["gidnumber"]);
	$ldap=new clladp();
	$tpl=new templates();
	$HashGroup=$ldap->GroupDatas($gidNumber);	
	$DN=	$HashGroup["dn"];	
	
	$HashGroup["KasperskyASGroupNumber"]=$gidNumber;
	
	$tpl=new templates();	
	$kas=new kas_groups();
	$SettingsDatas=$kas->SaveArrayRules();
	$FileToSave=$kas->BuildRulesFile($HashGroup,$SettingsDatas);
	
	
	
	if(!is_array_key('KasperkyASDatasRules',$HashGroup)){
			$hash_add_array2["KasperkyASDatasRules"]=$FileToSave;
			$ldap->Ldap_add_mod($DN,$hash_add_array2);	
			if($ldap->ldap_last_error<>null){echo '{SaveActionsRules} -> add Mod:' . $ldap->ldap_last_error;}
			}
			
	$error=$ldap->ldap_last_error;
	$hash_update_array["KasperkyASDatasRules"]=$FileToSave;
	
	

	
	$ldap->Ldap_modify($DN,$hash_update_array);
	$error=$error . " ".$ldap->ldap_last_error;
	
	$kas=new kas_groups($FileToSave);
	$kas->PrepareFiles($gidNumber);
	
		if(strlen($error)<5){
			$error="{success} {group rules}";
		}
		
		echo $tpl->_ENGINE_parse_body($error);	
	
	}
	
	
	
?>