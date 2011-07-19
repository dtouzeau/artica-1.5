<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
if(isset($_GET["js"])){js();exit;}	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["PostFixClassAddNew"])){PostFixClassAddNew();exit;}
if(isset($_GET["PostFixAddRestriction"])){PostFixAddRestriction();exit;}
if(isset($_GET["PostfixSelectedRestriction"])){PostfixSelectedRestriction();exit;}
if(isset($_GET["PostfixSaveRestrictionClass"])){PostfixSaveRestrictionClass();exit();}
if(isset($_GET["PostfixRestrictionMove"])){PostfixRestrictionMove();exit;}
if(isset($_GET["ReloadClassList"])){echo LoadTable_data($_GET["ReloadClassList"]);exit;}
if(isset($_GET["ReloadClass"])){echo LoadTable();exit;}
if(isset($_GET["PostfixRestrictionDelete"])){PostfixRestrictionDelete();exit;}
if(isset($_GET["PostFixRestrictionLoadLdap"])){PostFixRestrictionLoadLdap();exit;}
if(isset($_GET["PostFixRestrictionLoadLdapSelect"])){PostFixRestrictionLoadLdapSelect();exit;}
if(isset($_GET["PostFixRestrictionLoadLdapSave"])){PostFixRestrictionLoadLdapSave();exit();}
if(isset($_GET["PostfixRestrictionReloadLdapTable"])){echo PostFixRestrictionTableList($_GET["PostfixRestrictionReloadLdapTable"],$_GET["table_name"]);exit;}
if(isset($_GET["PostFixClassTableCheckDeleteValue"])){PostFixClassTableCheckDeleteValue();exit;}
if(isset($_GET["PostFixClassTableCheckMoveValue"])){PostFixClassTableCheckMoveValue();exit;}
if(isset($_GET["PostFixRestrictionClassDetailsForm"])){PostFixRestrictionClassDetailsForm();exit;}
if(isset($_GET["PostFixClassRestrictionGenerateConfig"])){PostFixClassRestrictionGenerateConfig();exit();}
if(isset($_GET["popup"])){page_popup();exit;}


page();	
function js(){
$usersmenus=new usersMenus();	
if($usersmenus->AsPostfixAdministrator==false){echo "alert('no privileges');";die();}
$page=CurrentPageName();
$addons=file_get_contents("js/postfix-classes.js");
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{postfix_restrictions_classes}');
$html="
	$addons
	function LoadFirstPage(){
		YahooWinS('800','$page?popup=yes','$title');
	
	}
	
	function SwitchRestrictionsTabs(div){
		document.getElementById('sstandard').style.display='none';
		document.getElementById('susers').style.display='none';
		document.getElementById(div).style.display='block';
	}
	
	LoadFirstPage();";
	
	echo $html;
}

function page_tabs(){
	$tpl=new templates();

	$array["sstandard"]='{standard_classes}';
	$array["susers"]='{restrictions_classes_list}';

	while (list ($num, $ligne) = each ($array) ){
		
		$html=$html . "<li><a href=\"javascript:SwitchRestrictionsTabs('$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<br><div id=tablist>$html</div>";		
}

function page_popup(){
	$apply=applysettings_postfix();
	$table=LoadTable();
	$tabs=page_tabs();
	$html="
	<H1>{postfix_restrictions_classes}</H1>
	<input type='hidden' id='give_class_name' value='{give_class_name}'>
	<div style='text-align:right'>
			<input type='button' value='&laquo;&nbsp;{security_rules}' OnClick=\"javascript:Loadjs('postfix.security.rules.php?js=yes');\">
			&nbsp;&nbsp;<input type='button' value='{add_new_class}&nbsp;&raquo;' OnClick=\"javascript:PostFixClassAddNew();\">
			&nbsp;&nbsp;<input type='button' value='{see_conf}&nbsp;&raquo;' OnClick=\"javascript:PostFixClassRestrictionGenerateConfig();\">
	</div>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><p class=caption>{postfix_restrictions_classes_text}</p>$apply</td>
		<td valign='top'>
		$tabs
		". RoundedLightGrey("
		<div id='class_list' style='width:100%;height:350px;overflow:auto;'>$table")."</td>
		<td width=1% valign='top'></td>
		
	</tr>
	</table>
		
	
	
	";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}



function page(){
	$tabs=page_tabs();
	$apply=applysettings_postfix();
	
	$html="
	<script type=\"text/javascript\" language=\"javascript\" src=\"js/postfix-classes.js\"></script>
	<input type='hidden' id='give_class_name' value='{give_class_name}'>
	$tabs
	<table style='width:100%'>
	<tr>
	<td>
		<p class='caption'>{postfix_restrictions_classes_text}</p>
		
	</td>
	<td width=1% valign='top'>" . RoundedLightGrey($apply)."</td>
	<table width=100%>
	<tr>
		<td>
			<input type='button' value='&laquo;&nbsp;{security_rules}' OnClick=\"javascript:MyHref('postfix.security.rules.php');\">
			&nbsp;&nbsp;<input type='button' value='{add_new_class}&nbsp;&raquo;' OnClick=\"javascript:PostFixClassAddNew();\">
			&nbsp;&nbsp;<input type='button' value='{see_conf}&nbsp;&raquo;' OnClick=\"javascript:PostFixClassRestrictionGenerateConfig();\">
			
		
		</td>
		
	
	</tr>
	<tr>
	<td><div id='class_list'>" . LoadTable() . "</td>
	</tr>	
	</table>
	
	";
	
	$tpl=new template_users('{postfix_restrictions_classes}',$html);
	echo $tpl->web_page;	
	
	
}

function PostFixClassAddNew(){
$ldap=new clladp();
$_GET["PostFixClassAddNew"]=str_replace(" ","_",$_GET["PostFixClassAddNew"]);
$_GET["PostFixClassAddNew"]=str_replace("'","",$_GET["PostFixClassAddNew"]);
$_GET["PostFixClassAddNew"]=str_replace("\"","_",$_GET["PostFixClassAddNew"]);
$_GET["PostFixClassAddNew"]=str_replace('\\',"_",$_GET["PostFixClassAddNew"]);
$_GET["PostFixClassAddNew"]=replace_accents($_GET["PostFixClassAddNew"]);

$ldap->add_restriction_class($_GET["PostFixClassAddNew"]);}

function LoadTable(){
	$ldap=new clladp();
	$table=$ldap->Hash_get_restrictions_classes();
	$cl=new smtpd_restrictions();
	$santardTables=LoadTablesStandard();
	
	$html="<H5>{restrictions_classes_list}</H5>";
	$ldap=new clladp();
	if(is_array($table)){
		$html=$html. "<table style=width:100%'>";
		
		while (list ($num, $ligne) = each ($table) ){
			$ClassData=$ldap->RestrictionClassData($num);
			
		$html=$html . LoadTablesStandard_Format($num,$ClassData["description"],$num);
	
		}
	}
	$html =$html . "</table>";
	
	$newpage="
	<div id='sstandard' style='display:block'>
	$santardTables
	</div>
	<div id='susers' style='display:none'>
	$html
	</div>
	
	
	";
	
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($newpage);
	
}
function LoadTablesStandard_Format($class,$description=null,$class_name=null){
	if($description==null){$description="<H5>{add}...</H5><br>{{$class}_text}";}
	if($class_name==null){$class_name="{{$class}}";}
	
	return "
	<table style='width:100%' class=table_form>
		<tr>
		<td width='1px' valign='top'><img src='img/fw_bold.gif'></td>
		<td valign='top' width='190px' nowrap align='left'>
				<table style='width:100%'>
					<tr>
					<td width='99%'><strong style='font-size:12px'>" . texttooltip($class_name,$description,"PostFixAddRestriction('$class');")."</strong></td>
					<td>" . imgtootltip("add-18.gif",$description,"PostFixAddRestriction('$class');")."</td>
					</tr>
				</table>
	
			</td>
		<td width='120px' valign='top'><div id='restrictions_list_$class' >" . LoadTable_data($class) ."</td>
		</tr>
	</table>	
		";
	
}



function LoadTable_data($class_name){
		$ldap=new clladp();
		$restrictions=$ldap->Hash_get_restrictions_className($class_name);
		$users_class_list=$ldap->Hash_get_restrictions_classes();
		
		
		if(is_array($restrictions)){
			$_restrictions="<table style='width:100%'>";
			$count=0;
			while (list ($index, $restriction) = each ($restrictions) ){
				if(preg_match('#(.+)?="(.+)?"#',$restriction,$reg)){
					$key=$reg[1];
					$value=$reg[2];
				}
				$count=$count+1;
				$tooltip=tooltipjs("{".$key."_text}<br>&laquo;&nbsp;$value&nbsp;&raquo;",1);
				$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"PostfixRestrictionMove('$class_name','$index','up')") ."</td>";
				$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"PostfixRestrictionMove('$class_name','$index','down')") ."</td>";
				$cell_del="<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"PostfixRestrictionDelete('$class_name','$index')") ."</td>";
				$key_name="{{$key}}";
				if($value=="ldap"){
					$value=":&laquo;&nbsp;".imgtootltip('txt_small.gif','{edit_table}',"PostFixRestrictionLoadLdap('$class_name','$key');") ."&nbsp;&raquo;";
					}
				if($users_class_list[$key]==$key){
					$key_name="<strong>{user_class} $key</strong>";
					$class_data=$ldap->RestrictionClassData($key);
					$value=imgtootltip('rule-16.jpg',$class_data["description"]);
					$tooltip=tooltipjs("{$class_data["description"]}",1);
					
				}
				
				if($value=="inet:127.0.0.1:29001"){$cell_del="<td>&nbsp;</td>";}
				
				$_restrictions=$_restrictions . "
				<tr>
					<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>
					<td valign='middle' nowrap style='font-size:9px' width=99%><span $tooltip>$key_name&nbsp;$value</span><td>
					$cell_up
					$cell_down
					$cell_del
				</tr>
				";
				$count=$count+1;
			}$_restrictions=$_restrictions . "</table>";
		}else{return "&nbsp;<img src='img/icon_mini_warning.jpg'>&nbsp;&nbsp;<span style='font-size:9px'>{error_no_datas}</span>";}
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body($_restrictions);	
	}
	
function LoadTablesStandard(){
	

	$ldap=new clladp();
	
	$html="<H5>{standard_classes}</H5>";
	$html=$html . "
	<table style='width:100%'>
	<tr><td><br>".LoadTablesStandard_Format("smtpd_client_restrictions") ."</td></tr>".
	"<tr><td><br>".LoadTablesStandard_Format('smtpd_helo_restrictions') . "</td></tr>".
	"<tr><td><br>".LoadTablesStandard_Format('smtpd_sender_restrictions') . "</td></tr>".
	"<tr><td><br>".LoadTablesStandard_Format('smtpd_recipient_restrictions')."</td></tr>".
	"</table>";
	return $html;
	
}


function PostFixAddRestriction(){
	$restriction=new smtpd_restrictions();
	$hash=$restriction->smtpd_all_restrictions_table;
	$class_name=$_GET["PostFixAddRestriction"];
	
	$ldap=new clladp();
	$class_datas=$ldap->RestrictionClassData($class_name);
	$hash_class_users=$ldap->Hash_get_restrictions_classes();
	
	
	while (list ($num, $ligne) = each ($hash[0]) ){
		if($hash_class_users[$ligne]==$ligne){
			$arr[$ligne]=$ligne;
		}else{
			$arr[$ligne]="{" .strtolower($ligne) ."}";}
		}
		
	$arr[null]="{select}";
	unset($arr[$class_name]);
	ksort($arr);
	$rest=Field_array_Hash($arr,'restriction',null,"PostfixSelectedRestriction()");
	
	$class_description=$class_datas['description'];
	
	
	$html="
	<input type='hidden' id='class_name' value='$class_name'>
	
	
	<div style='padding:20px'>
	<H3>{postfix_restrictions_classes}:&nbsp;$class_name&nbsp;</H3>
	
	<form name='PostFixRestrictionClassDetailsForm'>
	<input type='hidden' name='class_name' value='$class_name'>
	<table style='width:100%;margin-bottom:5px'>
	<tr>
	<td><strong>{note}:</strong></td>
	<td>" . Field_text('PostFixRestrictionClassDetailsForm',$class_description) . "</td>
	<td width=1%><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:PostfixClassEditDescription();\"></td>
	</tr>
	</table>
	</form>
	<hr>
	
	
	<table style='width:100%'>
	<tr>
	<td><strong>{select}</strong></td>
	<td><strong>$rest</strong></td>
	</tr>
	</table>
	<div id=selected_restriction></div>
	</div>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
function PostfixSelectedRestriction(){
	$restriction=new smtpd_restrictions();
	$hash=$restriction->smtpd_all_restrictions_table;	
	$settings=$hash[1][$_GET["PostfixSelectedRestriction"]]["datas"];
	$main=new smtpd_restrictions();

	$field=$main->CreateFormDependOnKey($_GET["PostfixSelectedRestriction"]);
	
	if($settings=="class"){
		$ldap=new clladp();
		$class_datas=$ldap->RestrictionClassData($_GET["PostfixSelectedRestriction"]);
		$help="<td><div class=caption><strong>{user_class}:{$_GET["PostfixSelectedRestriction"]}&nbsp;</strong>({$settings["datas"]}):&nbsp;{$class_datas["description"]}</div></td>";
		$title="{user_class}:{$_GET["PostfixSelectedRestriction"]}";
	}else{
		$help="<td><div class=caption><strong>{$_GET["PostfixSelectedRestriction"]}&nbsp;</strong>({$settings["datas"]}):&nbsp;{{$_GET["PostfixSelectedRestriction"]}_text}</div></td>";
		$title="{{$_GET["PostfixSelectedRestriction"]}}";
	}
	
	
	$html="
	<form name='FFM_REST'>
	<input type='hidden' name='PostfixSaveRestrictionClass' value='{$_GET["class_name"]}'>
	<input type='hidden' name='SelectRestrictionList' value='{$_GET["PostfixSelectedRestriction"]}'>
	<table style='width:100%'>
	<tr>
	<td><H4>$title</H4></td>
	</tr>
	<tr>
	<td>$field</td>
	</tr>		
	<tr>
	$help
	</tr>	
	<tr>
	<td align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:PostfixSaveRestriction();ReloadClassList('{$_GET["class_name"]}');\"></td>
	</tr>		
	</table>
	</form>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}
function PostfixSaveRestrictionClass(){
	$tpl=new templates();
	$ldap=new clladp();
	$restrictions=new smtpd_restrictions();
	$class_name=$_GET["PostfixSaveRestrictionClass"];
	$dn="cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	
	if(!$ldap->ExistsDN($dn)){
		if($restrictions->standard_classes_array[$class_name]=$class_name){$obecjtClass="PostFixRestrictionStandardClasses";}else{$obecjtClass="PostFixRestrictionClasses";}
		$upd["cn"][0]=$class_name;
		$upd['objectClass'][0]=$obecjtClass;
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
	}
	
	
	$value="{$_GET["SelectRestrictionList"]}=\"{$_GET["datas"]}\"";
	$update_array["PostFixRestrictionClassList"][]=$value;
	$class_datas=$ldap->RestrictionClassData($_GET["PostfixSaveRestrictionClass"]);
	if($class_datas["rules_source"][$value]==$value){return null;}
	if(!$ldap->Ldap_add_mod($dn,$update_array)){echo $ldap->ldap_last_error;}else{echo $tpl->_ENGINE_parse_body('{success}');}
	}
function PostfixRestrictionMove(){
	$ldap=new clladp();
	$restrictions=$ldap->Hash_get_restrictions_className($_GET["class_name"]);
	$restrictions=array_move_element($restrictions,$restrictions[$_GET["index"]],$_GET["PostfixRestrictionMove"]);
	while (list ($num, $ligne) = each ($restrictions) ){$update_array["PostFixRestrictionClassList"][]=$ligne;}
	
	$dn="cn={$_GET["class_name"]},cn=restrictions_classes,cn=artica,$ldap->suffix";
	if(!$ldap->Ldap_modify($dn,$update_array)){echo $ldap->ldap_last_error;};
	}
	
function PostfixRestrictionDelete(){
	$ldap=new clladp();
	$restrictions=$ldap->Hash_get_restrictions_className($_GET["PostfixRestrictionDelete"]);
	writelogs("delete index {$_GET["index"]} in array of " .count($restrictions) . " rows",__FUNCTION__,__FILE__);
	$dn="cn={$_GET["PostfixRestrictionDelete"]},cn=restrictions_classes,cn=artica,$ldap->suffix";
	unset($restrictions[$_GET["index"]]);
	
	if(count($restrictions)==0){
		writelogs("no rows for this class, delete $dn",__FUNCTION__,__FILE__);
		if(!$ldap->ldap_delete($dn,true)){echo $ldap->ldap_last_error;};
		exit;
	}
	
	while (list ($num, $ligne) = each ($restrictions) ){$update_array["PostFixRestrictionClassList"][]=$ligne;}
	
	if(!$ldap->Ldap_modify($dn,$update_array)){echo $ldap->ldap_last_error;};
}
function PostFixRestrictionLoadLdap(){
	$class_name=$_GET["PostFixRestrictionLoadLdap"];
	$hash_table=$_GET["key"];
	$restrictions=new smtpd_restrictions();
	
	$hash=$restrictions->smtpd_hash_restrictions_table;
	
	$correspondance=$hash[1];
	
	while (list ($num, $ligne) = each ($hash[0]) ){
		if($correspondance[$ligne]["datas"]=="ACTIONS_datas"){
			$arr[$ligne]=$ligne;} 
			else{$arr[$ligne]="{" .strtolower($ligne) ."$suffix}";}
	}
	$arr[null]="{select}";	
	ksort($arr);
	
	//prevent creating new hash table by deleting access keys...
	while (list ($num, $ligne) = each ($restrictions->restriction_rules_key) ){unset($arr[$num]);}
	//-----
	
	
	$field=Field_array_Hash($arr,'pattern_action',null,'PostFixRestrictionLoadLdapSelect()');
	
	$html="
	<input type='hidden' id='PostFixRestrictionLoadLdap_class_name' value='$class_name'>
	<input type='hidden' id='PostFixRestrictionLoadLdap_hash_table' value='$hash_table'>
	<div style='padding:20px'>
	<H3>$class_name:&nbsp;{".$hash_table."}&nbsp;</H3>
		<table style='width:100%'>
	<tr>
	<td><strong>" . icon_help('pattern_action') ."</strong>:</td>
	</tr>
	<tr>	
	<td>" . Field_text('pattern_email',null) . "</td>
	</tr>
	<tr>
	<td><strong>{action}</strong></td>
	</tr>
	<tr>
	<td>$field</td>
	</tr>	
	</table>
	<div id='PostFixRestrictionLoadLdapSecondStep'></div>
	<div id='PostFixRestrictionTableList' style='text-align:center;width:100%;padding:5px;border:1px solid #CCCCCC;margin-top:10px;'>" .PostFixRestrictionTableList($class_name,$hash_table)."</div>
	</div>
	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function PostFixRestrictionTableList($class_name,$table_name){
	$ldap=new clladp();
	$array=$ldap->Hash_get_restrictions_classes_tables($class_name,$table_name);
	if(is_array($array)){
		$html="
		<center><table style='width:450px' align='center' style='font-size:9px'>";
			while (list ($num, $ligne) = each ($array) ){
				$html=$html . "
				<tr style='font-size:9px'>
				<td width=1% valign='middle' class=bottom><img src='img/fw_bold.gif'></td>
				<td width=50% class=bottom><strong><a href=\"javascript:PostfixRestrictionPutEmailIntoField('$num');\">$num</a></strong>
				<td width=50% class=bottom>\n\t" . PostFixRestrictionTableListValue($ligne,$class_name,$table_name,$num) . "\n\t</td>
				</tr>";
				
			}
		
		
	}
	$html=$html . "</table></center>";
	return $html;
}
function PostFixRestrictionTableListValue($value,$class_name,$table_name,$email){
	if(strpos($value,',')==0){$array=array($value);
		
	}else{
		$array=explode(",",$value);
		
	}
	
	$html="\t<table style='width:255px'>\n";
	while (list ($num, $ligne) = each ($array) ){
				$cell_up="<td width=3%>" . imgtootltip('arrow_up.gif','{up}',"PostFixClassTableCheckMoveValue('$class_name','$table_name','$email','$num','up')") ."&nbsp;</td>";
				$cell_down="<td width=3%>" . imgtootltip('arrow_down.gif','{down}',"PostFixClassTableCheckMoveValue('$class_name','$table_name','$email','$num','down')") ."&nbsp;</td>";
						
		
		
				$html=$html . "\t\t<tr style='font-size:9px'>\n";
				$html=$html . "\t\t\t<td width=1% valign='middle'><img src='img/fw_bold.gif'></td>\n";
				$html=$html . "\t\t\t<td>$ligne</td>\n";
				$html=$html . $cell_up;
				$html=$html . $cell_down;
				$html=$html . "\t\t\t<td width=1%>" . imgtootltip('x.gif','{delete}',"PostFixClassTableCheckDeleteValue('$class_name','$table_name','$email','$num');")."</td>\n";
				$html=$html . "\t\t</tr>\n";
				
			}	
$html=$html . "\t</table>\n";
return $html;
}

function PostFixRestrictionLoadLdapSelect(){
	$pattern_email=$_GET["pattern_email"];
	$pattern_action=$_GET["pattern_action"];
	$restrictions=new smtpd_restrictions();
	$hash=$restrictions->smtpd_hash_restrictions_table;
	$correspondance=$hash[1][$pattern_action]["datas"];	
	if($correspondance=="ACTIONS_datas"){
		$help="{".$pattern_action . "_help}";}else{$help="{".$pattern_action."}<br>{".$pattern_action."_text}";}
	
	if($correspondance=="class"){
		$ldap=new clladp();
		$class_data=$ldap->RestrictionClassData($pattern_action);
		$help=$class_data["description"];
	}
		
		
		
	$html="<div class=caption>$help</div>" . $restrictions->CreateFormDependOnKey($pattern_action)	;
	$html=$html . "<div style='text-align:right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:PostFixRestrictionLoadLdapSave();\"></div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);			
}

function PostFixRestrictionLoadLdapSave(){
	$tpl=new templates();
	$class_name=$_GET["PostFixRestrictionLoadLdapSave"];
	$table_name=$_GET["table_name"];
	$email=$_GET["email"];
	$value=$_GET["value"];
	$action=$_GET["action"];
	
	if($value==null){$value=$action;}else{$value="$action\t$value";}
	
	if($email==null){echo $tpl->_ENGINE_parse_body('{error_give_email_or_domain}');exit;}
	$ldap=new clladp();
	$dn1="cn=$table_name,cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	$dn2="cn=$email,$dn1";
	if(!$ldap->ExistsDN($dn1)){
		$upd["cn"][0]=$table_name;
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn1,$upd)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);exit;}
	}
	if(!$ldap->ExistsDN($dn2)){
		unset($upd);
		$upd["cn"][0]=$email;
		$upd['objectClass'][0]='PostFixRestrictionCheckAccess';
		$upd['objectClass'][1]='top';
		$upd['PostFixRestrictionTableAction'][]="$value ";
		if(!$ldap->ldap_add($dn2,$upd)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);exit;}
		}else{
		  unset($upd);
		  $array=$ldap->hash_get_class_RestrictionTableAction($dn2);
		  $array[]=$value;
		  $upd['PostFixRestrictionTableAction'][0]=implode(",",$array);
		  if(!$ldap->Ldap_modify($dn2,$upd)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);exit;}
		}
	
}

function PostFixClassTableCheckDeleteValue(){
//Delete an entry in access table specified by class_name,table_name
	$tpl=new templates();
	$class_name=$_GET["PostFixClassTableCheckDeleteValue"];
	$table_name=$_GET["table_name"];
	$email=$_GET["email"];	
	$index=$_GET["index"];
	$ldap=new clladp();
	$dn="cn=$email,cn=$table_name,cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	 $array=$ldap->hash_get_class_RestrictionTableAction($dn);
	 unset($array[$index]);
	 if(count($array)==0){
	 	  if(!$ldap->ldap_delete($dn,false)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);exit;}
	 	  exit;
	 }
	 
	  $upd['PostFixRestrictionTableAction'][0]=implode(",",$array);
	  if(!$ldap->Ldap_modify($dn,$upd)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);exit;}
		
}
function PostFixClassTableCheckMoveValue(){
	$tpl=new templates();
	$class_name=$_GET["PostFixClassTableCheckMoveValue"];
	$table_name=$_GET["table_name"];
	$email=$_GET["email"];	
	$index=$_GET["index"];	
	$direction=$_GET["move_direction"];		
	$ldap=new clladp();
	$dn="cn=$email,cn=$table_name,cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	 $array=$ldap->hash_get_class_RestrictionTableAction($dn);	
	$new_array=array_move_element($array,$array[$index],$direction);
	 $upd['PostFixRestrictionTableAction'][0]=implode(",",$new_array);
	   if(!$ldap->Ldap_modify($dn,$upd)){echo $tpl->_ENGINE_parse_body($ldap->ldap_last_error);exit;}
}
function PostFixRestrictionClassDetailsForm(){
	$class_name=$_GET["class_name"];
	$description=$_GET["PostFixRestrictionClassDetailsForm"];
	$ldap=new clladp();
	$dn="cn=$class_name,cn=restrictions_classes,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){return null;}
	$update_array["PostFixRestrictionClassDescription"][0]=$description;
	if(!$ldap->Ldap_modify($dn,$update_array)){echo $ldap->ldap_last_error;}
	}
	
	
function PostFixClassRestrictionGenerateConfig(){
	$main=new smtpd_restrictions();
	$datas=$main->Build();
	$datas1=nl2br($datas);
	
	if(preg_match('#bind_pw([\s=]+)([0-9\w\*\-\#\%\?\&\~\$]+)#s',$datas1,$regs)){
		$paswd=$regs[2];
		$datas1=str_replace($paswd,'****',$datas1);
	}
	$datas1=str_replace(",",", ",$datas1);
	echo RoundedLightWhite("<div style='padding:20px;font-size:10px;overflow:auto;height:300px'><code>$datas1</code></div>");
	
	
}

?>