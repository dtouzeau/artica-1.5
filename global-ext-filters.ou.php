<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.renattach.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["add_attach_group"])){add_attach_group();exit;};
if(isset($_GET["LoadGroups"])){LoadGroups();exit;}
if(isset($_GET["group_attachment_edit"])){LoadGroupSettings($_GET["group_attachment_edit"],$_GET["ou"]);exit;}
if(isset($_GET["addDefaultSupectExt"])){Add_default_dangerous_filesext();exit;}
if(isset($_GET["DeleteExtension"])){Delete_Extension();exit;}
if(isset($_GET["SwitchExtRule"])){SwitchExtRule();exit;}
if(isset($_GET["DelDefaultSupectExt"])){Del_default_dangerous_filesext();exit;}
if(isset($_GET["addNewSupectExt"])){Add_new_ext_list();exit;}
if(isset($_GET["search_zip"])){Edit_Notifications();exit();}
if(isset($_GET["extaction"])){Edit_BadExtension();exit;}
if(isset($_GET["DeleteGroupFromExtensionsRule"])){DeleteGroupFromExtensionsRule();exit;}
if(isset($_GET["delete_attach_group"])){delete_attach_group();exit;}

INDEX();


function INDEX(){
	if(!isset($_GET["ou"])){header('location:domains.index.php');exit;}
	$page=CurrentPageName();
	
	$ou=$_GET["ou"];
	$ldap=new clladp();
	VerifyBranchs($ou);
	
	
	
	$html="
	<input type='hidden' id='add_attachment_group_text' value='{add_attachment_group_text}'>
	<input type='hidden' id='add_attachment_text' value='{add_attachment_text}'>
	<input type='hidden' name='ou' value='$ou' id='ou'>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_forbiden-attachmt.jpg'></td>
	<td valign='top' width=99%><p class='caption'>{attachments_explain}</caption>
	<center>
	<table style='width:80%'>
	<tr>
	<td><input type='button' value='{add_attach_group}&nbsp;&raquo;' OnClick=\"javascript:add_attach_group();\"></td>
	</tr>
	</table>
	</center>
	
	</td>
	</tr>
	</table>
	
	<div id='group_list'></div>
	
	<script>LoadAjax('group_list','$page?LoadGroups=$ou');</script>
	";
		
		
		
	
	
$cfg["JS"][]="js/attachments.ou.js";
$tpl=new template_users('{artica_filtersext_rules}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}

function VerifyBranchs($ou){
	$ldap=new clladp();
	$dn="cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixStructuralClass';	
		$upd["cn"]="forbidden_attachments";
		$ldap->ldap_add($dn,$upd);
	}
	
	
}


function add_attach_group(){
	$group=$_GET["add_attach_group"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$dn="cn=$group,cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';	
		$upd["objectClass"][]='FilterExtensionsGroup';
		$upd["cn"]=$group;
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
	}
	
}

function GroupsList($ou){
	$ldap=new clladp();	
	$path="cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($path,'(&(ObjectClass=FilterExtensionsGroup)(cn=*))',array('cn'));
	if(!is_array($hash)){writelogs("no rows...",__CLASS__ . "/".__FUNCTION__,__FILE__);return array();}
	
	for($i=0;$i<$hash["count"];$i++){
		$res[]=$hash[$i]["cn"][0];
		}
		return $res;	
	
}
function LoadGroups(){
	$hash=GroupsList($_GET["LoadGroups"]);
	if(!is_array($hash)){echo "<span></span>";}
	
	$html="<center><table style='width:50%;border:1px solid #CCCCCC;padding:5px;margin:5px'>";
	if(is_array($hash)){
	while (list ($num, $val) = each ($hash) ){
		$html=$html . "<tr ><td width='1%'><img src='img/red-pushpin-24.png'></td>
		<td " .CellRollOver("group_attachment_edit('$val')",'edit')."><strong style='font-size:13px'>$val</strong></td>
		<td width=1% >" . imgtootltip("x.gif","{delete}","group_attachment_delete('$val');") . "</td>
		<td width=1% >" . imgtootltip("icon_edit.gif","{edit}","group_attachment_edit('$val');") . "</td>
		</tr>
		";
			
	}
	
	echo RoundedLightGrey($html . "</table></center>");
	}

}
function LoadGroupSettings($group,$ou){
	$ldap=new clladp();
	$ren=new renattach($group,$ou);
	$page=CurrentPageName();
	$actionsARR=array("r"=>"{rename}","d"=>"{delete}","k"=>"{delete_mail}");
	if(is_array($ren->badlist_array)){
		$badlist="<table>";
		while (list ($num, $val) = each ($ren->badlist_array) ){
			$action=null;
			if(strpos($val,'/')>0){
				$tblO=explode("/",$val);
				$action=$tblO[1];
				$val=$tblO[0];
				}
						
			if($action==null){$action="r";}
			$badlist=$badlist . "<tr>
			<td width=1% valign='top' class='bottom'><img src='img/attachment.gif'></td>
			<td valign='top' class='bottom'>$val<input type='hidden' id='ext_name_$num' value='$val'></td>
			<td style='font-size:9px' class='bottom'>{$ren->knowExtList[strtoupper($val)]}</td>
			<td style='font-size:9px' class='bottom'>" . Field_array_Hash($actionsARR,"ext_action_$num",$action)."</td>
			<td style='font-size:9px' class='bottom'><input type='button' value='{edit}' OnClick=\"javascript:EditExtension($num,'$group');\"></td>
			<td valign='middle' class='bottom'>" . imgtootltip("x.gif","{delete}","DeleteExtension($num,'$group');")."</td>";
		}
		$badlist=$badlist . "</table>";
	}
		
	
	$html=RuleTab($group,$ou)."
	<center>
	<table style='width:80%;border:1px solid #CCCCCC;padding:5px;margin:5px'>
	<tr>
	<td valign='top'>" . imgtootltip('restore-on.png','{back}',"LoadAjax('group_list','$page?LoadGroups=$ou')")."</td>
	<td valign='top'>" . imgtootltip('icon_add_all_files-32.png','{add_default_dangerous_extensions}',"addDefaultSupectExt('$group')")."</td>
	<td valign='top'>" . imgtootltip('icon_add_attach-32.jpeg','{add_a_new_block_ext}',"addNewSupectExt('$group')")."</td>
	<td valign='top'>" . imgtootltip('icon_delete_all_files-32.jpeg','{delete_default_dangerous_extensions}',"DelDefaultSupectExt('$group')")."</td>
	</tr>
	</table>
	</center>	
	<table style='width:90%;border:1px solid #CCCCCC;padding:5px;margin:5px'>
	<tr>
	<td valign='top' nowrap><strong>{artica_filtersext_rules}:</strong></td>
	</tr>
	<tr>
	<td valign='top'>$badlist</td>
	</tr>
	</table>
	
	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function RuleTab($group,$ou){
	if(!isset($_GET["SwitchExtRule"])){$_GET["SwitchExtRule"]=0;};
	$array[]='{filters_list}';
	$array[]='{notifications}';
	$array[]='{users_groups_list}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["SwitchExtRule"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:SwitchExtRule('$num','$ou','$group');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}


function LoadGroupSettingsGroupsList($group,$ou){
	$ldap=new clladp();
	$filter="(&(objectclass=posixGroup)(FiltersExtensionsGroupName=$group))";
	$cols=array("cn");
	$sr = @ldap_search($ldap->ldap_connection,$ldap->suffix,$filter,$cols);
		if ($sr) {
			$table="
			<center>
			<table style='width:90%'>";
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			while (list ($num, $ligne) = each ($hash) ){
				if($hash[$num]["cn"][0]<>null){
				$table=$table."<tr>
				<td width=1%><img src='img/member-24.png'></td>
				<td><strong style='font-size:13px'>". $hash[$num]["cn"][0] . "</strong></td>
				<td>" . imgtootltip('x.gif','{delete}',"DeleteGroupFromExtensionsRule('{$hash[$num]["cn"][0]}','$ou','$group');")."</td>
				</tr>";
			}}
			
			
		$table=$table ."</table></center>";
		}	
	
	$html=RuleTab($group,$ou)."<br>
	<H5>{users_groups_list}</H5>
	<p>{groupslisttext}</p>
	
	
	" . RoundedLightGrey($table);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function LoadGroupSettingsNotifications($group,$ou){
$ldap=new clladp();
	$ren=new renattach($group,$ou);
	$page=CurrentPageName();
	
$html=RuleTab($group,$ou)."
<FORM NAME='FFM1'>
<input type='hidden' name='ou' value='$ou'>
<input type='hidden' name='group' value='$group'>
<table style='width:100%;border:1px solid #CCCCCC;padding:5px;margin:5px'>
<tr><td colspan=3>" . imgtootltip('restore-on.png','{back}',"LoadAjax('group_list','$page?LoadGroups=$ou')")."</td></tR>
	<tr>

			<td valign='top' align='right' nowrap><strong>{search_zip}:</strong></td>
			<td width=1% valign='top' align='left'>".Field_yesno_checkbox_img('search_zip',$ren->arrayDatas["search_zip"]) . "</td>
			<td width=1% class=caption>" .icon_help('search_zip_text',1) ."</td>
	</tr>
	<tr>
			<td valign='top' nowrap align='right'><strong>{subj_deleted}:</strong></td>
			<td width=99% valign='top' align='left'>".Field_text('subj_deleted',$ren->arrayDatas["subj_deleted"],'width:100%') . "</td>
			<td width=1% class=caption>" .icon_help('subj_deleted_text',1) ."</td>
	</tr>
	<tr>
			<td valign='top' nowrap align='right'><strong>{subj_renamed}:</strong></td>
			<td width=99% valign='top' align='left'>".Field_text('subj_renamed',$ren->arrayDatas["subj_renamed"],'width:100%') . "</td>
			<td width=1% class=caption>" .icon_help('subj_renamed_text',1) ."</td>
	</tr>	
	<tr>
			<td valign='top' nowrap align='right'><strong>{add_subject}:</strong></td>
			<td width=99% valign='top' align='left'>".Field_text('add_subject',$ren->arrayDatas["add_subject"],'width:100%') . "</td>
			<td width=1% class=caption>" .icon_help('add_subject_text',1) ."</td>
	</tr>	
	
	<tr>
	<td valign='top' nowrap align='right'><strong>{warning_text}:</strong></td>
			<td width=99% valign='top' align='left'><textarea name='warning_text' id='warning_text' style='width:100%'>{$ren->arrayDatas["warning_text"]}</textarea></td>
			<td width=1% class=caption>" .icon_help('warning_text_text',1) ."</td>
	</tR>
	
	<tr>
	<td valign='top' nowrap align='right'><strong>{warning_html}:</strong></td>
			<td  valign='top' align='left'><textarea name='warning_html' id='warning_html' style='width:100%'>{$ren->arrayDatas["warning_html"]}</textarea></td>
			<td width=1% class=caption>" .icon_help('warning_html_text',1) ."</td>
	</tR>	
	<tr>
	<td colspan=3 align='right'><input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','".CurrentPageName(). "',true);\"></td>
	</tR>
	
		
	
	
        </table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}
	


function Del_default_dangerous_filesext(){
	$ext=explode(',',"ADE,ADP,BAS,BAT,CHM,CMD,COM,CPL,CRT,EXE,HLP,HTA,HTM,HTML,INF,INS,ISP,JS,JSE,LNK,MDB,MDE,MSC,MSI,MSP,MST,OCX,PCD,PIF,REG,SCR,SCT,SHS,URL,VB,VBE,VBS,WSC,WSF,WSH");
	$group=$_GET["DelDefaultSupectExt"];
	$ou=$_GET["ou"];
	
	$ren=new renattach($group,$ou);
	while (list ($num, $val) = each ($ext) ){
		$ren->del_badlist($val);
		}
	
	$ren->Save();
	LoadGroupSettings($group,$ou);		
	
}

function Edit_Notifications(){
	$group=$_GET["group"];
	$ou=$_GET["ou"];
	unset($_GET["group"]);
	unset($_GET["ou"]);
	$ren=new renattach($group,$ou);
	while (list ($num, $val) = each ($_GET) ){
		$ren->arrayDatas[$num]=$val;
	}
	
	$ren->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}

function Edit_BadExtension(){
	$group=$_GET["group"];
	$ou=$_GET["ou"];
	$ren=new renattach($group,$ou);
	$ren->badlist_array[$_GET["index"]]=strtoupper($_GET["extname"]) . "/" . strtolower($_GET["extaction"]);
	$ren->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}' . "\n" . strtoupper($_GET["extname"]) . "/" . strtolower($_GET["extaction"]));	
	}

function Add_default_dangerous_filesext(){
	$ext=explode(',',"ADE,ADP,BAS,BAT,CHM,CMD,COM,CPL,CRT,EXE,HLP,HTA,HTM,HTML,INF,INS,ISP,JS,JSE,LNK,MDB,MDE,MSC,MSI,MSP,MST,OCX,PCD,PIF,REG,SCR,SCT,SHS,URL,VB,VBE,VBS,WSC,WSF,WSH");
	$group=$_GET["addDefaultSupectExt"];
	$ou=$_GET["ou"];
	$ren=new renattach($group,$ou);
	
	while (list ($num, $val) = each ($ext) ){
		$ren->add_badlist($val);
	}
	
	$ren->Save();
	LoadGroupSettings($group,$ou);
	
}

function Add_new_ext_list(){
	$group=$_GET["group"];
	$ou=$_GET["ou"];
	$ren=new renattach($group,$ou);
	$list=explode(",",$_GET["addNewSupectExt"]);
	if(is_array($list)){
		while (list ($num, $val) = each ($list) ){
			$ren->add_badlist($val);
		}
	}
	$ren->Save();
	LoadGroupSettings($group,$ou);
	
}

function DeleteGroupFromExtensionsRule(){
	$ou=$_GET["ou"];
	$gid=$_GET["DeleteGroupFromExtensionsRule"];
	$ExtensionsRule=$_GET["ExtensionsRule"];
	
	$ldap=new clladp();
	
	$dn="cn=$gid,ou=$ou,dc=organizations,$ldap->suffix";

	$upd["FiltersExtensionsGroupName"]=$ExtensionsRule;
	
	writelogs("Delete $dn,FiltersExtensionsGroupName=$ExtensionsRule in $gid");
	$ldap->Ldap_del_mod($dn,$upd);
	
	
}

function delete_attach_group(){
	$ou=$_GET["ou"];
	$rule=$_GET["delete_attach_group"];
	$ldap=new clladp();
	$dn="cn=$rule,cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ldap_delete($dn)){echo $ldap->ldap_last_error;}
	
	
}

function Delete_Extension(){
	$num=$_GET["extension"];
	$ou=$_GET["ou"];
	$group=$_GET["DeleteExtension"];
	
	$ren=new renattach($group,$ou);
	unset($ren->badlist_array[$num]);
	$ren->Save();
	LoadGroupSettings($group,$ou);
}
function SwitchExtRule(){
	switch ($_GET["SwitchExtRule"]) {
		case 0:
			LoadGroupSettings($_GET["group"],$_GET["ou"]);
			break;
		case 1:
			LoadGroupSettingsNotifications($_GET["group"],$_GET["ou"]);
			break;
			case 2:
			LoadGroupSettingsGroupsList($_GET["group"],$_GET["ou"]);
			break;			
		default:
			break;
	}
	
}

?>

