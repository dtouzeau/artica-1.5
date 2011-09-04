<?php

if(isset($_GET["verbose"])){
	$GLOBALS["DEBUG_TEMPLATE"]=true;
	$GLOBALS["VERBOSE"]=true;
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);	
}


	include_once('ressources/class.templates.inc');
	$GLOBALS["CURRENT_PAGE"]=CurrentPageName();
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.samba.inc');
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;}
	//if(count($_POST)>0)
	$usersmenus=new usersMenus();
	if(!$usersmenus->AllowAddUsers){
		writelogs("Wrong account : no AllowAddUsers privileges",__FUNCTION__,__FILE__);
		if(isset($_GET["js"])){
			$tpl=new templates();
			$error="{ERROR_NO_PRIVS}\\n{AllowAddUsers}:False\\n";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();
		}
		header("location:domains.manage.org.index.php?ou={$_GET["ou"]}");
		}
	
		
	if(isset($_GET["js"])){js();exit;}	
	if($_GET["tab"]=="groups"){LIST_GROUPS_FROM_OU();exit;}
	if(isset($_GET["groups-area-search"])){LIST_GROUPS_FROM_OU_search();exit;}
	
	if(isset($_GET["ChangeGroupDescription"])){ChangeGroupDescription();exit;}
	if(isset($_POST["SaveGrouPdescript"])){ChangeGroupDescription_save();exit;}
	
	
	if(isset($_GET["FindInGroup"])){MEMBERS_SEARCH_USERS();exit;}
	if(isset($_POST["groupid"])){MEMBERS_UPLOAD_FILE();exit();}
	if(isset($_POST["DeleteFromGroup"])){MEMBER_DELETE_FROM_GROUP();exit;}
	
	
	if(isset($_GET["addgroup"])){AddGroup();exit;}
	if(isset($_GET["GroupPriv"])){echo GROUP_PRIVILEGES($_GET["GroupPriv"]);exit;}
	if(isset($_GET["PrivilegesGroup"])){EditGroup();exit;}
	if(isset($_GET["DeleteMember"])){DeleteMember();exit;}
	if(isset($_GET["DeleteNotAffectedUsers"])){MEMBERS_NOT_AFFECTED_DELETE($_GET["ou"]);exit;}

	
	if(isset($_GET["DeleteGroup"])){DeleteGroup();exit;}
	if(isset($_GET["LoadGroupList"])){echo GROUPS_LIST($_GET["LoadGroupList"]);exit;}
	if(isset($_GET["MembersList"])){echo MEMBERS_LIST($_GET["MembersList"]);exit;}
	if(isset($_GET["members-area-search"])){echo MEMBERS_LIST_LIST();exit;}

	
	
	
	if(isset($_GET["ImportMembersFile"])){MEMBERS_IMPORT_FILE();exit;}
	if(isset($_GET["DeleteMembersForGroup"])){GROUP_DELETE_MEMBERS($_GET["DeleteMembersForGroup"]);exit;}
	if(isset($_GET["ForbiddenAttach"])){GROUP_ATTACHMENTS($_GET["ForbiddenAttach"]);exit();}
	if(isset($_GET["SaveAttachmentGroup"])){FORBIDDEN_ATTACHMENTS_SAVE();exit;}
	if(isset($_GET["LoadGroupSettings"])){GROUP_SETTINGS_PAGE();exit;}
	if(isset($_GET["group_add_attach_rule"])){FORBIDDEN_ATTACHMENTS_ADDRULE();exit;}
	
	if(isset($_GET["KavMilterGroupAddNewRule"])){echo GROUP_KAVMILTER_ADD_NEW_RULE($_GET["KavMilterGroupAddNewRule"]);exit;}
	
	if(isset($_GET["DansGuardian_rules"])){GROUP_DANSGUARDIAN($_GET["DansGuardian_rules"]);exit;}
	if(isset($_GET["save_dansguardian_rule"])){GROUP_DANSGUARDIAN_SAVE();exit;}
	if(isset($_GET["delgroup"])){DeleteGroup();exit;}
	if(isset($_GET["GetTreeFolders"])){browser();exit;}
	
	
	if(isset($_GET["LoadMailingList-js"])){GROUP_MAILING_LIST_JS();exit();}
	if(isset($_GET["LoadMailingList"])){GROUP_MAILING_LIST();exit();}
	if(isset($_GET["RemoveMailingList"])){GROUP_MAILING_LIST_DEL();exit;}
	
	
	if(isset($_GET["LoadComputerGroup"])){COMPUTERS_LIST();exit;}
	if(isset($_GET["FORM_COMPUTER"])){COMPUTER_FORM_ADD();exit;}
	if(isset($_GET["find_computer"])){COMPUTER_FIND();exit;}
	if(isset($_GET["add_computer_to_group"])){COMPUTER_ADD_TO_GROUP();exit;}
	
	if(isset($_GET["FORM_GROUP"])){GROUP_SAMBA_SETTINGS_TABS();exit;}
	if(isset($_GET["FORM_GROUP2"])){GROUP_SAMBA_SETTINGS();exit;}
	if(isset($_GET["FORM_GROUP_IDENTITY"])){GROUP_SAMBA_IDENTITY();exit;}
	
	
	
	if(isset($_GET["SaveGroupSamba"])){GROUP_SAMBA_SETTINGS_SAVE();exit;}
	
	
	if(isset($_GET["ShowDeleteSelected"])){MEMBERS_ICON_DELETEALL();exit;}
	if(isset($_GET["DeleteUserByUID"])){MEMBERS_DELETE();exit;}
	if(isset($_GET["default_password"])){GROUP_DEFAULT_PASSWORD();exit;}
	if(isset($_GET["ChangeDefaultGroupPassword"])){GROUP_DEFAULT_PASSWORD_SAVE();exit;}
	
	
	if(isset($_GET["GroupPrivilegesjs"])){GroupPrivilegesjs();exit;}
	
	
	if(isset($_GET["sieve-js"])){GROUP_SIEVE_JS();exit;}
	if(isset($_GET["sieve-index"])){GROUP_SIEVE_INDEX();exit;}
	if(isset($_GET["sieve-save-filter"])){GROUP_SIEVE_SAVE();exit;}
	if(isset($_GET["sieve-update-users"])){GROUP_SIEVE_UPDATE();exit;}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-add-group"])){add_group_js();exit;}
	
	
	INDEX();
	
	
	
function GroupPrivilegesjs(){
	$gpid=$_GET["GroupPrivilegesjs"];
	$js=file_get_contents("js/edit.group.js");
	$html="
	$js
	GroupPrivileges($gpid);";
	echo $html;
	
}


function add_group_js(){
if(is_base64_encoded($_GET["ou"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
$ou_encrypted=base64_encode($_GET["ou"]);
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->javascript_parse_text("{group name}");
$html="

	var x_addgroup= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			if(document.getElementById('GroupSettings')){
				LoadAjax('GroupSettings','domains.edit.group.php?LoadGroupSettings=&ou=$ou_encrypted&encoded=yes')
			}
		}


function addgroup_js(){
	var gp=prompt('{$_GET["ou"]}\\n$title');
	if(gp){
		var XHR = new XHRConnection();
		XHR.appendData('addgroup',gp);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.sendAndLoad('$page', 'GET',x_addgroup);	
	}	

}

addgroup_js();
";

echo $html;

	
}


function js(){
if(is_base64_encoded($_GET["ou"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
$ou=$_GET["ou"];	
$ou_encrypted=base64_encode($ou);
$cfg[]="js/edit.group.js";
$cfg[]="js/webtoolkit.aim.js";
$cfg[]="js/kavmilterd.js";
$cfg[]="js/edit.user.js";
$cfg[]="js/json.js";
$cfg[]="js/users.kas.php.js";
$title=$ou . ":&nbsp;{groups}";
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body($title);
$warning_delete_all_users=$tpl->javascript_parse_text("{warning_delete_all_users}");
$page=CurrentPageName();
$prefix=str_replace('.','_',$page);

if(isset($_GET["group-id"])){
	$loadgp="LoadAjax('GroupSettings','domains.edit.group.php?LoadGroupSettings={$_GET["group-id"]}&ou=$ou_encrypted&encoded=yes')";
}

while (list ($num, $ligne) = each ($cfg) ){
	$jsadd=$jsadd.file_get_contents($ligne)."\n";
	
}

$start="LoadGroupAjaxSettingsInFront();";


if(isset($_GET["in-front-ajax"])){
	$start="LoadGroupAjaxSettingsInFront();";
}


$html="
{$prefix}timeout=0;
$jsadd

function LoadGroupAjaxSettingsInFront(){
	$('#BodyContent').load('$page?popup=yes&ou=$ou_encrypted&crypted=yes&group-id={$_GET["group-id"]}');
}

function LoadGroupAjaxSettingsPage(){
	YahooWinS('750','$page?popup=yes&ou=$ou_encrypted&crypted=yes&group-id={$_GET["group-id"]}','$title');
	}
	
	function DomainEditGroupPressKey(e){
		if(checkEnter(e)){addgroup();}
	}

	var x_addgroup= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			$start
		}
		
	
	var x_DeleteMembersGroup= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_group_config');
		}
		
function DeleteMembersGroup(groupid){
	if(confirm('$warning_delete_all_users')){
			var XHR = new XHRConnection();
			XHR.appendData('ou','$ou');
			XHR.appendData('DeleteMembersForGroup',groupid);
			XHR.sendAndLoad('$page', 'GET',x_DeleteMembersGroup);
		}
}			

function addgroup(){
	var XHR = new XHRConnection();
	XHR.appendData('addgroup',document.getElementById('group_add').value);
	XHR.appendData('ou','$ou');
	XHR.sendAndLoad('$page', 'GET',x_addgroup);	
	}	
	
function DisplayDivs(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>10){
			{$prefix}timeout=10;
			return;
		}
		if(!document.getElementById('grouplist')){
			setTimeout('DisplayDivs()',900);
		}
		LoadAjax('grouplist','$page?LoadGroupList=$ou_encrypted&encoded=yes');
		LoadGroupSettings();
		{$prefix}timeout=0;
		$loadgp
	}
	
$start	
	";

echo $html;
	
	
}

function popup(){
	$ou=base64_decode($_GET["ou"]);
	$ou_encrypted=$_GET["ou"];
	if($ou==null){$ou=ORGANISTATION_FROM_USER();}
	$page=CurrentPageName();
	$title=$ou . ":&nbsp;{groups}";
	
	$html="
	<input type='hidden' id='inputbox delete' value=\"{are_you_sure_to_delete}\">
	<input type='hidden' id='ou' value='$ou'>
	<input type='hidden' id='warning_delete_all_users' value='{warning_delete_all_users}'>
	<div id='GroupSettings'></div>
	<div id='MembersList'></div>
	<div id='groupprivileges'></div>
	
	
	
	<script>
		LoadAjax('GroupSettings','domains.edit.group.php?LoadGroupSettings={$_GET["group-id"]}&ou=$ou_encrypted&encoded=yes')
	</script>
	
	";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
	
function INDEX(){
	$ou=$_GET["ou"];
	if($ou==null){$ou=ORGANISTATION_FROM_USER();}
	$page=CurrentPageName();
	$title=$ou . ":&nbsp;{groups}";
	$ou_encoded=base64_encode($ou);
	$html="
	<input type='hidden' id='inputbox delete' value=\"{are_you_sure_to_delete}\">
	<input type='hidden' id='ou' value='$ou_encoded'>
	<input type='hidden' id='warning_delete_all_users' value='{warning_delete_all_users}'>
	<span id='grouplist'></span>
	<br>
	<div id='GroupSettings'></div>
	<div id='MembersList'></div>
	<div id='groupprivileges'></div>	
	
	<script>LoadAjax('grouplist','$page?LoadGroupList=$ou');</script>
	<script>LoadGroupSettings();</script>
	
	";
	
	
$cfg["JS"][]="js/edit.group.js";
$cfg["JS"][]="js/webtoolkit.aim.js";
$cfg["JS"][]="js/kavmilterd.js";
$cfg["JS"][]="js/edit.user.js";
$cfg["JS"][]="js/json.js";
$cfg["JS"][]="js/users.kas.php.js";

$tpl=new template_users($title,$html,0,0,0,0,$cfg);	
echo $tpl->web_page;		
}
function ORGANISTATION_FROM_USER(){
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	$ct=new user($_SESSION["uid"]);
	return $ct->ou;
	}
	
function GROUP_SIEVE_JS(){
	$gid=$_GET["sieve-js"];
	$tpl=new templates();
	$gp=new groups($gid);
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("$gp->ou::$gp->groupName::{sieve_auto_script}");
	
	$html="
		function SieveGroupOptions(){
			YahooWin2(500,'$page?sieve-index=$gid','$title');
			}
			
		function x_SieveSaveArticaFilters(obj){
				var tempvalue=obj.responseText;
				if(tempvalue.length>3){alert(tempvalue);}
				SieveGroupOptions();
				YahooWin3(500,'$page?sieve-update-users=$gid','$title');
				}			
			
		function SieveSaveArticaFilters(){
			var XHR = new XHRConnection();
			XHR.appendData('sieve-save-filter',document.getElementById('EnableSieveArticaScript').value);
			XHR.appendData('gid','$gid');
			document.getElementById('div-sieve-filters').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SieveSaveArticaFilters);	
			}
			
		SieveGroupOptions();
	
	";
	echo $html;
	}
	
function GROUP_SIEVE_INDEX(){
	$gid=$_GET["sieve-index"];
	$gp=new groups($gid);
	
	$form=Paragraphe_switch_img("{sieve_auto_script}","{sieve_auto_explain}","EnableSieveArticaScript",$gp->Privileges_array["EnableSieveArticaScript"],null,"100%");
	
	$html="<H1>{sieve_auto_script}</H1>
	<div id='div-sieve-filters'>
		$form
		<div style='text-align:right'><input type='button' OnClick=\"javascript:SieveSaveArticaFilters();\" value='{edit}&nbsp;&raquo;'></div>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function GROUP_SIEVE_SAVE(){
	$gid=$_GET["gid"];
	$value=$_GET["sieve-save-filter"];
	$gp=new groups($gid);
	$gp->Privileges_array["EnableSieveArticaScript"]=$value;
	$gp->SavePrivileges();
	}
	
function GROUP_SIEVE_UPDATE(){
	$gid=$_GET["sieve-update-users"];
	$tpl=new templates();
	$gp=new groups($gid);
	echo $tpl->_ENGINE_parse_body("<H1>{sieve_auto_script}:{events}</H1>");
	
	if($gp->Privileges_array["EnableSieveArticaScript"]==1){
		include_once('ressources/class.sieve.inc');
		if(!is_array($gp->members_array)){
			echo $tpl->_ENGINE_parse_body("<span style='color:red;font-size:14px;font-weight:bold;color:red'>{ERROR_GROUP_STORE_NO_MEMBERS}</span>");
			return null;
		}
		echo "<div style='width:100%;height:200px;overflow:auto;background-color:white'>";
		while (list ($num, $ligne) = each ($gp->members_array) ){
			if(trim($num)==null){continue;}
			$sieve=new clSieve($num);
			$sieve->ECHO_ERROR=false;
			if($sieve->AddAutoScript()){
				
			}else{
				$result=$tpl->_ENGINE_parse_body("{failed}:<div style='margin:4px;font-size:10px;font-weight:bold;color:red'>$sieve->error</div>");
			}
			
			echo "<div style='border:1px dotted #CCCCCC;padding:3px;margin:3px'>
					<div style='font-size:12px;font-weight:bold'>$num:&nbsp;<code>$result</code></div>
				
				</div>";
		}
		
		echo "</div>";
	}
}


function LIST_GROUPS_FROM_OU(){
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
$html="
<center>
<table style='width:95%' class=form>
<tr>
	<td class=legend>$ou&raquo;&nbsp;{groups}:</td>
	<td>". Field_text("groups_area_search",null,"font-size:13px;padding:3px",null,null,null,false,"GroupsGroupWearchCheck(event)")."</td>
	<td width=1%>". button("{search}","GroupsGroupWearch()")."</td>
</tr>
</table>
</center>
<div id='groups-list-from-ou'></div>
	<script>
		function GroupsGroupWearchCheck(e){
			if(checkEnter(e)){GroupsGroupWearch();}
		}
	
	
		function GroupsGroupWearch(){
			var se=escape(document.getElementById('groups_area_search').value);
			LoadAjax('groups-list-from-ou','$page?groups-area-search=yes&ou={$_GET["ou"]}&search='+se);
		
		}
	
		GroupsGroupWearch();
	</script>
";	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}



function LIST_GROUPS_FROM_OU_search(){
	$search=$_GET["search"];
	$GLOBALS["NOUSERSCOUNT"]=false;
	$ou=base64_decode($_GET["ou"]);
	$sock=new sockets();
	$page=CurrentPageName();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	
	
	writelogs("[$search]: EnableManageUsersTroughActiveDirectory = $EnableManageUsersTroughActiveDirectory ",__FUNCTION__,__FILE__);
	
	$addgroup=Paragraphe("64-folder-group-add.png","{add_group}","{add_a_new_group_in_this_org}",
	"javascript:Loadjs('$page?popup-add-group=yes&ou={$_GET["ou"]}')");
	if($EnableManageUsersTroughActiveDirectory==1){
			$GLOBALS["NOUSERSCOUNT"]=true;
			$addgroup=Paragraphe("64-folder-group-add-grey.png","{add_group}","{add_a_new_group_in_this_org}","");
			$ldap=new ldapAD();
			writelogs("[$search]: ->hash_get_groups_from_ou_mysql($ou,$search) ",__FUNCTION__,__FILE__);
			$hash=$ldap->hash_get_groups_from_ou_mysql($ou,$search);
	}else{
		$ldap=new clladp();
		$hash=$ldap->hash_groups($ou,1);
		
	}	
	
	
	
	$tr=array();
	$search=str_replace(".",'\.',$search);
	$search=str_replace("*",'.*?',$search);
	

	
	
	$tr[]=$addgroup;
	
if(is_array($hash)){
			while (list ($num, $line) = each ($hash)){
				if(strtolower($line)=='default_group'){continue;}
				if(strlen($search)>2){if(!preg_match("#$search#",$line)){continue;}}
				
				
				$js="javascript:LoadAjax('GroupSettings','domains.edit.group.php?LoadGroupSettings=$num&ou={$_GET["ou"]}&encoded=yes')";
				if(!$GLOBALS["NOUSERSCOUNT"]){
					$gp=new groups($num);
					$members=count($gp->members_array);	
					$text="{manage_this_group}<br>($members {members})";	
					if($gp->description<>null){$text=$gp->description."<br>($members {members})";}
					$tr[]=Paragraphe("group-64.png",$line,$text,$js);
				}else{
					$text="{manage_this_group}";
					$tr[]=Paragraphe("group-64.png",$line,$text,$js);
				}
			}
		}

		
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	

echo @implode("\n",$tables)."</table>\n";


}
	
	
function GROUPS_LIST($OU){
	writelogs("startup ou=$OU",__FUNCTION__,__FILE__);
	$page=CurrentPageName();
	$ou=$OU;
	if(is_base64_encoded($ou)){$ou=base64_decode($ou);}
	
	
	
	writelogs("Encoded ou ? =\"$ou\" {$_SESSION["uid"]}",__FUNCTION__,__FILE__);
	
	
	$ldap=new clladp();
	$users=new usersMenus();
	
	
	
	if($users->AsArticaAdministrator){
		writelogs("AsArticaAdministrator privileges",__FUNCTION__,__FILE__);
		$org=$ldap->hash_get_ou(true);
		
		while (list ($ou1, $ou2) = each ($org)){
			$orgs_encoded[base64_encode($ou1)]=$ou2;
		}
		
		//$orgs=Field_array_Hash($orgs_encoded,'SelectOuList',base64_encode($ou),"LoadGroupList()",null,0,'width:250px');
		
		
		
		$hash=$ldap->hash_groups($ou,1);
		writelogs("AsArticaAdministrator:: Load " . count($hash) . " groups from ou $ou",__FUNCTION__,__FILE__);
	}else{
		$ou=ORGANISTATION_FROM_USER();
		//$orgs="<strong>$ou</strong><input type='hidden' name=SelectOuList id='SelectOuList' value='$ou'>";
		if(!$users->AsOrgAdmin){$hash=$ldap->UserGetGroups($_SESSION["uid"],1);}
		if($users->AsOrgAdmin){$hash=$ldap->hash_groups($ou,1);}
		
	}
		
		if(is_array($hash)){
			while (list ($num, $line) = each ($hash)){
				if(strtolower($line)=='default_group'){unset($hash["$num"]);}
				$tr[]=$num;
			}
		
		}
	
		$orgs=Field_hidden("SelectOuList",base64_encode($ou));
	
	
	writelogs("Load " . count($hash) . " groups from ou $ou",__FUNCTION__,__FILE__);
	$hash[null]="{select_group}";
	reset($hash);
	$field=Field_array_Hash($hash,'SelectGroupList',null,"LoadGroupSettings()",null,0,'width:250px');
	$html="
	$orgs
	<table style='width:300px'>
	<td width=80%>$field</td>
	<td width=1%>" . imgtootltip('20-refresh.png','{refresh}',"RefreshGroupList()")."</td>
	<td width=1%>". button("{add}","Loadjs('$page?popup-add-group=yes&ou=$ou')")."</td>
	</tr>
	</table>
	
	<script>
	function RefreshGroupList(){
			LoadAjax('grouplist','$page?LoadGroupList=$ou')
		
		}
	
	LoadGroupSettings('{$tr[0]}');	
	</script>
	
	
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function GROUP_DEFAULT_PASSWORD(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	
	$html="<h1>{group_default_password}</H1>
	<div id='GROUP_DEFAULT_PASSWORD'>
	<p class=caption>{group_default_password_text}</p>
	<input type='hidden' id='error_passwords_mismatch' value='{error_passwords_mismatch}'>
	<table style='width:100%' class=table_form>
		<tr>
		<td class=legend>{password}:</td>	
		<td>" . Field_password("default_password1",$group->DefaultGroupPassword)."</td>
		</tr>
		<tr>
		<td class=legend>{confirm}:</td>	
		<td>" . Field_password("default_password2",$group->DefaultGroupPassword)."</td>
		</tr>
		<td class=legend>{change_password_now}:</td>	
		<td>" . Field_onoff_checkbox_img('change_now','no','{group_default_password_change}')."</td>
		</tr>
		<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ChangeDefaultGroupPassword($gpid);\" value='{edit}&nbsp;&raquo;'>
		</tr>
	</table>	
	</div>	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function GROUP_DEFAULT_PASSWORD_SAVE(){
	$gpid=$_GET["ChangeDefaultGroupPassword"];
	$group=new groups($gpid);
	$group->DefaultGroupPassword=$_GET["password"];
	if(!$group->edit_DefaultGroupPassword()){
		echo $group->ldap_error;
		exit;
	}
	
	if($_GET["change_now"]=="on"){
		if(!$group->changeAllMembersPassword()){
			echo "Members password:failed\n";
			exit;
		}
	}
	
	
	
	
}

function GROUP_SETTINGS_PAGE_ACTIVE_DIRECTORY(){
	if(isset($_GET["tab"])){GROUP_SETTINGS_PAGE_CONTENT();exit;}
	$users=new usersMenus();
	$tpl=new templates();
	$no_priv = $tpl->javascript_parse_text ("{ERROR_NO_PRIVS}" );
	$page=CurrentPageName();	
	if($users->AsOrgAdmin){$users->AllowAddUsers=true;}
	if(!$users->AsArticaAdministrator){
		if(!$users->AllowAddUsers){
			if(!$users->AsOrgAdmin){writelogs("AsOrgAdmin:False",__FUNCTION__,__FILE__,__LINE__);}
			if(!$users->AllowAddUsers){writelogs("AllowAddUsers:False",__FUNCTION__,__FILE__,__LINE__);}
			echo "<H1>$no_priv :&laquo;". $tpl->javascript_parse_text("{AllowAddUsers}")."&raquo;</H1>";
			return null;
		}
	}
	$array["members"]='{members}';
	$array["groups"]='{groups} '.base64_decode($_GET["ou"]);
	
	$_GET["LoadGroupSettings"]=urlencode($_GET["LoadGroupSettings"]);
	while (list ($num, $ligne) = each ($array) ){
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		
		if($num=="members"){
			$html[]= "<li><a href=\"$page?MembersList={$_GET["LoadGroupSettings"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$html[]= "<li><a href=\"$page?LoadGroupSettings={$_GET["LoadGroupSettings"]}&tab=$num&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_group_config style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_group_config').tabs();
			
			
			});
		</script>";		
		
	
	
			
}


function GROUP_SETTINGS_PAGE(){
	$ldap=new clladp();
	if($ldap->EnableManageUsersTroughActiveDirectory){
		writelogs("Loading tabs for Active Directory",__FUNCTION__,__FILE__,__LINE__);
		GROUP_SETTINGS_PAGE_ACTIVE_DIRECTORY();
		return;
	}
	
	
	if(isset($_GET["tab"])){GROUP_SETTINGS_PAGE_CONTENT();exit;}
	$users=new usersMenus();
	$page=CurrentPageName();
	$tpl=new templates();
	$no_priv = $tpl->javascript_parse_text ("{ERROR_NO_PRIVS}" );
	
	
	
	
			if(is_numeric($_GET["LoadGroupSettings"])){
				$array["config"]='{group_settings}';
				$array["members"]='{members}';
				
				
				if($users->SQUID_INSTALLED){
					$array["proxy"]='{proxy}';
				}
				$array["options"]='{advanced_options}';
				
				if($users->AsOrgAdmin){$users->AllowAddUsers=true;}
				
				if(!$users->AsArticaAdministrator){
					if(!$users->AllowAddUsers){
						if(!$users->AsOrgAdmin){writelogs("AsOrgAdmin:False",__FUNCTION__,__FILE__,__LINE__);}
						if(!$users->AllowAddUsers){writelogs("AllowAddUsers:False",__FUNCTION__,__FILE__,__LINE__);}
						echo "<H1>$no_priv :&laquo;". $tpl->javascript_parse_text("{AllowAddUsers}")."&raquo;</H1>";
						return null;}
				}
				
				if($users->EnableManageUsersTroughActiveDirectory){
					unset($array["options"]);
					unset($array["asav"]);
					unset($array["proxy"]);
				}
	}
	$array["groups"]='{groups} '.base64_decode($_GET["ou"]);
	
	while (list ($num, $ligne) = each ($array) ){
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		
		if($num=="members"){
			$html[]= "<li><a href=\"$page?MembersList={$_GET["LoadGroupSettings"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$html[]= "<li><a href=\"$page?LoadGroupSettings={$_GET["LoadGroupSettings"]}&tab=$num&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=main_group_config style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_group_config').tabs();
			
			
			});
		</script>";		
	
		
	
}



function GROUP_SETTINGS_PAGE_CONTENT(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$num=$_GET["LoadGroupSettings"];
	$groupID=$num;
	writelogs("Loading group $num",__FUNCTION__,__FILE__,__LINE__);
	if(is_base64_encoded($_GET["ou"])){$_GET["ou"]=base64_decode($_GET["ou"]);}
	$ou_conn=$_GET["ou"];
	
	

	if(!$ldap->EnableManageUsersTroughActiveDirectory){if(!is_numeric($num)){return null;}}
	if(trim($num)==null){$num=0;}
	if($num==0){
		if(isset($_GET["byGroupName"])){
			$num=$ldap->GroupIDFromName($_GET["ou"],$_GET["byGroupName"]);
			writelogs("Numeric identifier=0, try to get numeric identifier by {$_GET["ou"]}/{$_GET["byGroupName"]}=$num",__FUNCTION__,__FILE__,__LINE__);
			if($num==0){return;}
		}
	}
		
	
	$group=new groups($num);
	if(trim($_GET["ou"])<>null){
		if($group->ou<>$_GET["ou"]){
			$tpl=new templates();
			$error="<center style='border:2px solid red;padding:10px;margin:10px'><span style='font-size:13px;font-weight:bold;color:red'>Group: $num/{$_GET["ou"]}<br> {error_group_not_in_your_organization}</span></center>";
			//echo $tpl->_ENGINE_parse_body($error);
			writelogs("ERROR: group $num from organization \"$group->ou\" is different from requested organization \"{$_GET["ou"]}\"",__FUNCTION__,__FILE__);
			return null;
			}
	}
	
	
	$text_disbaled="{ERROR_NO_PRIVILEGES_OR_PLUGIN_DISABLED}";
	$user=new usersMenus();
	$user->LoadModulesEnabled();
	
	$SAMBA_GROUP=Paragraphe('64-group-samba-grey.png','{MK_SAMBA_GROUP}',$text_disbaled,'');
	$mailing_list=Paragraphe('64-mailinglist-grey.png',"{mailing_list}","$text_disbaled");
	//$hash=$ldap->GroupDatas($num);
	
	
	
	$members=count($group->members);
	

	
	if($user->POSTFIX_INSTALLED==true){
		$mailing_list_count=$group->CountMailingListes();
		$js="javascript:Loadjs('domains.edit.group.php?LoadMailingList-js={$_GET['ou']}')";
		$mailing_list=Paragraphe('64-mailinglist.png',"($mailing_list_count) {mailing_list}","{mailing_list_text}",
		"$js");
		
	}
	
	if($user->DANSGUARDIAN_INSTALLED==true){
		$DANSGUARDIAN=Paragraphe('icon-chevallier-564.png','{dansguardian_rules}','{dansguardian_rules_text}',"javascript:DansGuardianRules($num)");
		//
	}
	
	
	
	
	$automount=Paragraphe('folder-64-automount.png','{shared_folders}','{shared_folders_text}',"javascript:Loadjs('SharedFolders.groups.php?gpid=$num')");
		
	
	
	if($user->cyrus_imapd_installed){
		$sieve_auto=Paragraphe('64-learning.png','{sieve_auto_script}','{sieve_auto_script_text}',"javascript:Loadjs('$page?sieve-js=$num')");
	}
	
	
	
	
	if($user->SAMBA_INSTALLED){
		$COMPUTERS=Paragraphe('computers-64.png','{computers}','{computers_text}',"javascript:LoadComputerGroup($num)");
		$SAMBA_GROUP=Paragraphe('64-group-samba.png','{MK_SAMBA_GROUP}','{MK_SAMBA_GROUP_text}',"javascript:Change_group_settings($num)");
		$LOGON_SCRIPT=Paragraphe('script-64.png','{LOGON_SCRIPT}','{LOGON_SCRIPT_TEXT}',"javascript:Loadjs('domains.edit.group.login.script.php?gpid=$num')");
		}
	
	
	
	
	if($DANSGUARDIAN==null){$DANSGUARDIAN=Paragraphe('icon-chevallier-564-grey.png','{dansguardian_rules}',$text_disbaled,'');}
	if($automount==null){$automount=Paragraphe('folder-64-automount-grey.png','{shared_folders}',$text_disbaled,'');}	
	
	if($COMPUTERS==null){$COMPUTERS=Paragraphe('computers-64-grey.png','{computers}',$text_disbaled,'');}
	
	if(!$user->cyrus_imapd_installed){
		if($user->SAMBA_INSTALLED){
			$sieve_auto=$LOGON_SCRIPT;
			$LOGON_SCRIPT=null;
		}
		
	}
	
	$RENAME_GROUP=Paragraphe('group_rename-64.png','{GROUP_RENAME}','{GROUP_RENAME_TEXT}',"javascript:Loadjs('domains.edit.group.rename.php?group-id=$num&ou={$_GET["ou"]}')");
	$OPTIONS_DEFAULT_PASSWORD=Paragraphe('64-key.png','{group_default_password}','{group_default_password_text}',"javascript:YahooWin('400','$page?default_password=yes&gpid=$num')");
	$PRIVILEGES=Paragraphe('members-priv-64.png','{privileges}','{privileges_text}',"javascript:GroupPrivileges($num)");
	
	
	$ou_encoded=base64_encode($_GET["ou"]);
	$delete_group=imgtootltip("32-cancel.png","{delete}::{$group->groupName}","Loadjs('domains.delete.group.php?gpid=$num')");
	
	if($user->EnableManageUsersTroughActiveDirectory){
		$SAMBA_GROUP=Paragraphe('64-group-samba-64.png','{MK_SAMBA_GROUP}','{MK_SAMBA_GROUP_text}');
		$mailing_list=Paragraphe('64-mailinglist-grey.png',"($mailing_list_count) {mailing_list}","{mailing_list_text}");
		$automount=Paragraphe('folder-64-automount-grey.png','{shared_folders}','{shared_folders_text}');
		$DANSGUARDIAN=null;
		$PRIVILEGES=Paragraphe('members-priv-64-grey.png','{privileges}','{privileges_text}');
		$delete_group=null;
	}
	
	
	
	
	$html_tab1="
	<table style='width:100%'>
	<tr>
	<td valign='top'>$PRIVILEGES</td>
	<td valign='top'>$COMPUTERS</td>
	<td valign='top'>$SAMBA_GROUP</td>
	</tr>
	<tr>
	<td valign='top'>$mailing_list</td>
	<td valign='top'>$automount</td>
	<td valign='top'></td>
	</tr>
	</table>";
	
	$html_tab2="	<table style='width:100%'>
	<tr>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	</tr>
	<tr>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	<td valign='top'>&nbsp;</td>
	</tr>
	</table>";
	
	$html_tab3="	
	<table style='width:100%'>
		<tr>
			<td valign='top'>$DANSGUARDIAN</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		</tr>
	</table>";

	
	$html_tab4="
	<table style='width:100%'>
		<tr>
			<td valign='top'>$RENAME_GROUP</td>
			<td valign='top'>$OPTIONS_DEFAULT_PASSWORD</td>
			<td valign='top'>$sieve_auto</td>
			
		</tr>
		<tr>
			<td valign='top'>$LOGON_SCRIPT</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		</tr>
	</table>
	
	";
	
	
	if($_GET["tab"]=='asav'){$html_tab1=$html_tab2;}
	if($_GET["tab"]=='proxy'){$html_tab1=$html_tab3;}
	if($_GET["tab"]=='options'){$html_tab1=$html_tab4;}
	$html=$html_tab1;
	
	$tpl=new templates();
	$group_description=$tpl->_ENGINE_parse_body("{group_description}");
	$barre_principale="
	<input type='hidden' id='group_delete_text' value='{group_delete_text}'>
	<table style='width:100%'>
	<tr>
		<td width=3%><div style='height:1px;border-bottom:1px solid #CCCCCC;width:100%;float:right'>&nbsp;</div></td>
		<td width=1% nowrap><H5 style='border-bottom:0px'>{group}&nbsp;&nbsp;&laquo;&nbsp;{$group->groupName}&nbsp;&raquo;</h5></td>
		<td><div style='height:1px;border-bottom:1px solid #CCCCCC;width:100%;float:right'>&nbsp;</div></td>
		<td width=1%>$delete_group</td>
	</tr>
	<tr>
		<td colspan=4 align='right'><div style='margin-top:-5px;padding-right:50px'><a href=\"javascript:blur();\" OnClick=\"ChangeGroupDescription()\" style='font-size:11px;text-decoration:underline;font-style:italic'>{$group->description}</a></div></td>
	</tr>
	</table>
	
	<script>
		function ChangeGroupDescription(){
			YahooWin5('360','$page?ChangeGroupDescription=yes&gpid=$groupID&ou=$ou_conn','{$group->groupName}::$group_description');
		
		}
		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body("$barre_principale$tab$html");
	}

function GROUP_SETTING_PAGE_TAB(){
	
}




function MEMBERS_LIST_TABS($maxpages,$currentpage){
	$gid=$_GET["MembersList"];
	$page=CurrentPageName();
	$url="$page?MembersList=$gid";
	if(!isset($_GET["next"])){$next=0;}else{$next=$_GET["next"];}
	$nextnext=$next+1;
	$splitPages=4;
	
	// calcul de la page de fin
	$start=($next*$splitPages);
	if($start<0){$start=0;}
	$max=$start+$splitPages;
	$nextpage=$next+$splitPages+1;
	$backpage=$next-1;
	if($maxpages>$splitPages){
		$end="<li><a href=\"javascript:LoadAjax('MembersList','$url&page=$nextpage&next=$nextnext')\" $class>{next}&nbsp;&raquo;&raquo;</a></li>";
		$find="<li><a href=\"javascript:FindInGroup($gid);\">&laquo;&nbsp;{search}&nbsp;&raquo;</a></li>";
		
	}
	
	// calcul de la page de debut.
	if($backpage>=0){
		$start_page="<li><a href=\"javascript:LoadAjax('MembersList','$url&page=$nextpage&next=$backpage')\" $class>&laquo;&laquo;{back}</a></li>";
	}
	
	   
	
	for($i=$start;$i<$max;$i++){
		if($currentpage==$i){$class="id=tab_current";}else{$class=null;}
		$page_name=$i+1;
		
		$html=$html . "<li><a href=\"javascript:LoadAjax('MembersList','$url&page=$i&next=$next')\" $class>&laquo;&nbsp;{page} $page_name&nbsp;&raquo;</a></li>\n";
			
		}
	return "
	<input type='hidden' id='FindInGroup_text' value='{FindInGroup_text}'>
	<div id=tablist>
		$start_page$html$find$end
	</div>
	<br>";			
	
	
}
function MEMBERS_ICON_DELETEALL(){
	
	if($_GET["ShowDeleteSelected"]>0){
		echo "<br>".imgtootltip('64-delete_user.png',"{delete_selected_members} ({$_GET["ShowDeleteSelected"]})","DeleteSelectedMembersGroup()");
	}
	
}


function MEMBERS_LIST($gid){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$users=new usersMenus();
	$group=new groups($gid);
	$js_addmember="Loadjs('domains.add.user.php?ou=$group->ou&gpid=$gid')";
	$js_impotmember="Loadjs('domains.import.user.php?ou=". base64_encode($group->ou)."&gpid=$gid')";
	$add_member=imgtootltip('member-add-64.png','{add_member}',$js_addmember);
	$import_member=imgtootltip('64-import-member.png','{add_already_member}',$js_impotmember);
	$import_members=imgtootltip('member-64-import.png','{import}',"Loadjs('domains.import.members.php?gid=$gid')");
	$delete_members=imgtootltip('member-64-delete.png','{delete_members}',"DeleteMembersGroup($gid)");
	if($users->ARTICA_META_ENABLED){
		if($sock->GET_INFO("AllowArticaMetaAddUsers")<>1){
			$add_member=null;
		}
	}
	$GLOBALS["EnableManageUsersTroughActiveDirectory"]=$users->EnableManageUsersTroughActiveDirectory;
	if($GLOBALS["EnableManageUsersTroughActiveDirectory"]){
		$js_addmember=null;
		$js_impotmember=null;
		$import_member=null;
		$import_members=null;
		$delete_members=null;
		$add_member=imgtootltip('member-add-64-grey.png','{add_member}');
		
	}
	
//".(MEMBERS_LIST_LIST($gid)) ."	
	
$html="
<input type='hidden' id='groups-section-from-members' value='$gid'>
<center>
<table style='width:95%' class=form>
<tr>
	<td class=legend>{$group->groupName}&raquo;&nbsp;{members}:</td>
	<td>". Field_text("members_area_search",null,"font-size:13px;padding:3px",null,null,null,false,"MembersGroupWearchCheck(event)")."</td>
	<td width=1%>". button("{search}","MembersGroupWearch()")."</td>
</tr>
</table>
</center>

<input type='hidden' id='delete_this_user' value='{delete_this_user}'>

	<table style='width:100%'>
	<td valign='top'><div id='members_area' style='width:100%;height:430px;overflow:auto'></div></td>
	<td valign='top' width=5%>
	<table style='width:100%' class=form>
		<tr>
		<td>$add_member</td>
		</tr>
		<tr>
		<td>$import_member</td>
		</tr>
		
		
		<tr>
		<td>$import_members</td>
		</tr>
		<tr>
		<td>$delete_members</td>
		</tr>
		<tr>
		<span id='ShowDeleteAll'></span>		
	</table>
		
	
	</td>
	
	</table>
	<script>
		function MembersGroupWearchCheck(e){
			if(checkEnter(e)){MembersGroupWearch();}
		}
	
	
		function MembersGroupWearch(){
			var se=escape(document.getElementById('members_area_search').value);
			LoadAjax('members_area','$page?members-area-search=yes&gpid=$gid&search='+se);
		
		}
	
		MembersGroupWearch();
	</script>
	
	
	
	
	";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function GROUP_MAILING_LIST_JS(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{mailing_list}");
	$page=CurrentPageName();
	$html="YahooWin(500,'$page?LoadMailingList={$_GET['LoadMailingList-js']}&ou={$_GET['LoadMailingList-js']}','$title')";
	echo $html;
}


function GROUP_MAILING_LIST(){
	$ou=$_GET["LoadMailingList"];
	$group=new groups(null);
	$hash=$group->load_MailingList($ou);
	
	$html="
	<input type='hidden' id='RemoveMailingList_text' value='{RemoveMailingList_text}'>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99%'>
	<thead class='thead'>
		<tr>
		<th width=99% colspan=3>{mailing_list}</th>
		</tr>
	</thead>
	<tbody class='tbody'>";
	
	
	while (list ($num, $ligne) = each ($hash) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
		$ldap=new clladp();
		$uid=$ldap->uid_from_email($num);
		$js=MEMBER_JS($uid,1);
		$delete="RemoveMailingList('$ou','$num');";
		
		$html=$html . "
		<tr class=$classtr>
		<td width=1%>".imgtootltip('24-mailinglist.png','{select}',$js)."</td>
		<td><strong style='font-size:14px'><a href='#' OnClick=\"$js\">$num ($ligne {members})</a></strong></td>
		<td width=1%>".imgtootltip('delete-32.png','{delete}',$delete)."</td>
		</tr>
		
	";
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function GROUP_MAILING_LIST_DEL(){
	$ldap=new clladp();
	$dn="cn={$_GET["RemoveMailingList"]},cn=aliases-mailing,ou={$_GET["ou"]},dc=organizations,$ldap->suffix";
	if(!$ldap->ldap_delete($dn,true)){
		echo $ldap->ldap_last_error;
	}
	
}



function MEMBERS_SEARCH_USERS(){
	$gid=$_GET["FindInGroup"];
	$pattern=$_GET["pattern"];
	$pattern=str_replace('*','',$pattern);
$styleRoll="
	style='border:1px solid white;width:190px;float:left;margin:1px'
	OnMouseOver=\"this.style.backgroundColor='#F3F3DF';this.style.cursor='pointer';this.style.border='1px solid #CCCCCC'\"
	OnMouseOut=\"this.style.backgroundColor='transparent';this.style.cursor='auto';this.style.border='1px solid white'\"
	";	
	
	
	//first we search the users 
	$ldap=new clladp();
	$hash=$ldap->UserSearch(null,$pattern);

	
	//second we load users uids of the group and build the hash
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(gidnumber=$gid)",array("memberUid"));
	if(!$sr){
		writelogs("Search members for (gidnumber=$gid) failed",__FUNCTION__,__FILE__);
		return null;
	
	}
	$entry_id = ldap_first_entry($ldap->ldap_connection,$sr);
	if(!$entry_id){return null;}
	$attrs = ldap_get_attributes($ldap->ldap_connection, $entry_id);
	if(!is_array($attrs["memberUid"])){
		writelogs("memberUid no attrs",__FUNCTION__,__FILE__);
		return null;
		}
	
		$count=$attrs["memberUid"]["count"];
		while (list ($num, $ligne) = each ($attrs["memberUid"]) ){
			$hash_members[$ligne]=true;
		}
	
	//now we parse the search results
	$count=0;
	for($i=0;$i<$hash["count"];$i++){
		if($hash_members[$hash[$i]["uid"][0]]){
			$uid=$hash[$i]["uid"][0];
			
			$count=$count+1;
			$html=$html . "<div $styleRoll id='mainid_{$uid}'>".MEMBERS_SELL($uid)."</div>";
			if($count>41){break;}
			
		}
		
	}
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function MEMBERS_LIST_LIST($gid){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	

	if($gid=="undefined"){$gid=0;}
	if(isset($_GET["gpid"])){$gid=$_GET["gpid"];}
	
	$priv=new usersMenus();
	$search=$_GET["search"];
	writelogs("-> groups($gid,$search)",__FUNCTION__,__FILE__,__LINE__);
	$group=new groups($gid,$search);
	writelogs("-> groups($gid,$search) END",__FUNCTION__,__FILE__,__LINE__);	
	$members=$group->members;
	$search=str_replace(".",'\.',$search);
	$search=str_replace("*",".*?",$search);
	
	$count=count($members);
	$number_of_users=$count;
	$tpl=new templates();
	$sure_to_delete_selected_user=$tpl->javascript_parse_text("{disconnect_from_this_group}");	
	
	writelogs("found $count members for (gidnumber=$gid)",__FUNCTION__,__FILE__,__LINE__);
	if(!is_array($members)){return null;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("refresh-24.png","{refresh}","MembersGroupWearch()")."</th>
	<th colspan=3>{members}:$group->groupName</th>
	</tr>
</thead>
<tbody class='tbody'>";			
				
	$user_img="user-32.png";
	$computer_img="computer-32.png";
	$classtr=null;
	$img=null;
	$already=array();
	writelogs("groups: starting table ",__FUNCTION__,__FILE__,__LINE__);
	for($i=0;$i<=$number_of_users;$i++){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$uid=$members[$i];
		if(trim($uid)==null){continue;}

		if(substr($uid,strlen($uid)-1,1)=='$'){$img=$computer_img;}else{$img=$user_img;}		
		
		if(isset($already[$uid])){continue;}
		$delete=imgtootltip('delete-32.png','{disconnect_from_this_group}',"DeleteUserFromGroup('$uid')");
		if($group->EnableManageUsersTroughActiveDirectory){$delete=imgtootltip('delete-32-grey.png','{disconnect_from_this_group}',"");}
		$already[$uid]=true;
		
		if(strlen($search)>0){if(!preg_match("#$search#",$uid)){continue;}}
		
		$html=$html . "<tr class=$classtr>
		<td width=1%><img src='img/$img'></td>
		<td style='font-size:14px'>$uid</td>
		<td width=1%>$delete</td>
		</tr>
		";
		
		
		
	}
	
	$html=$html."</table>
	<script>
	
	var x_DeleteUserFromGroup= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				MembersGroupWearch();
			}			
	
	
	function DeleteUserFromGroup(uid){
		if(confirm('$sure_to_delete_selected_user: '+uid+' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteFromGroup','$gid');
			XHR.appendData('uid',uid);
			AnimateDiv('members_area');
			XHR.sendAndLoad('$page', 'POST',x_DeleteUserFromGroup);	
			}
		}
		
	</script>
	
	";
	

	
	
	return  $tpl->_ENGINE_parse_body($html);
}


function MEMBERS_SELL($uid,$number=null){
	if($uid==null){return "&nbsp;";}
	$computer_img="base.gif";
	$user_img="user-single-18.gif";
	$tpl=new templates();
	$view_member=$tpl->_ENGINE_parse_body('{view_member}');
	$show=MEMBER_JS($uid);
	if(substr($uid,strlen($uid)-1,1)=='$'){
		$img=$computer_img;
	}else{$img=$user_img;}
	$uid_show=$uid;
	if(strlen($uid)>23){
		$uid_show=substr($uid,0,20)."...";
	}
	
	$delete=imgtootltip('ed_delete.gif','{delete}',"DeleteUID('$uid')");
	if($GLOBALS["EnableManageUsersTroughActiveDirectory"]){$delete=null;}
	
	$html="<table style='width:100%'>
	<tr>
		<td width=1%>
			<img src='img/$img' id='icon_$uid'>
		</td>
		<td $show><strong id='$uid'>".texttooltip($uid_show,'{view_member}')."</strong>
			<input type='hidden' id='deleteuid_$uid' name='deleteuid_$uid' value='0'>
			<input type='hidden' id='orgin_icon_$uid' name='orgin_icon_$uid' value='img/$img'></td>
		<td width=1%>$delete</td>
	</tr>
	</table>
	";
	return $html;
}





function MEMBERS_NOT_AFFECTED_DELETE($ou){
	$ldap=new clladp();
	$hash_users=$ldap->hash_get_users_Only_ou($ou);	
	
	while (list ($num, $ligne) = each ($hash_users) ){
		$ldap=new clladp();
		$dn=$ldap->_Get_dn_userid($ligne);
		if($dn<>null){
			$ldap->ldap_delete($dn,true);
		}
		
	}
	echo GROUPS_LIST($ou);
	
}


function MEMBERS_NOT_AFFECTED($ou){
	
	$ldap=new clladp();
	$hash_users=$ldap->hash_get_users_Only_ou($ou);
	if(!is_array($hash_users)){return null;}
	return count($hash_users);
	$html="
	
	<table style='width:400px;margin-left:10px'>";
	while (list ($num, $ligne) = each ($hash_users) ){
		$arr=$ldap->UserDatas($ligne);
		$mail=$arr["mail"];
		$domain=$arr["domainName"];
		$html=$html . "
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><a href='domains.edit.user.php?userid=$ligne&tab=3'>$ligne</a></td>
		<td>$mail</td>
		<td>$domain</td>
		<td>" . imgtootltip('x.gif','{delete}',"javascript:DeleteMember('$ligne','0')")."</td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
	
}


function AddGroup(){
	$group=$_GET["addgroup"];
	$ou=$_GET["ou"];
	if($ou==null){if($_SESSION["ou"]<>null){$ou=$_SESSION["ou"];}}
	
	$ldap=new clladp();
	include_once(dirname(__FILE__).'/ressources/class.groups.inc');
	
	$groupClass=new groups();
	$list=$groupClass->samba_group_list();
	
	if(is_array($list)){
		while (list ($num, $ligne) = each ($list) ){
			if(trim(strtolower($ligne))==trim(strtolower($group))){
				$tpl=new templates();
				echo $tpl->_ENGINE_parse_body('{no_samba_group_in_ou}');
				exit;
			}
		}	
	}
	
	if(!$ldap->AddGroup($group,$ou)){echo $ldap->ldap_last_error;}
	
}

function MEMBER_DELETE_FROM_GROUP(){
	$gid=$_POST["DeleteFromGroup"];
	$uid=$_POST["uid"];
	$ldap=new clladp();
	$ldap->GroupDeleteUser($gid,$uid);
}

function GROUP_DELETE_MEMBERS($gid){
	
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	
	if(is_array($hash["members"])){
		while (list ($num, $ligne) = each ($hash["members"]) ){
			$ldap->GroupDeleteUser($gid,$num);
			}
	}
	
	
	
	//if(!$ldap->Ldap_del_mod($hash["dn"],$upd["memberUid"])){echo $ldap->ldap_last_error;}
	
	
}


function FORBIDDEN_ATTACHMENTS_SAVE(){
	$ldap=new clladp();
	$gid=$_GET["SaveAttachmentGroup"];
	unset($_GET["SaveAttachmentGroup"]);

	while (list ($num, $ligne) = each ($_GET) ){
		if($ligne=='yes'){
			$ldap->GroupForbiddenAttachment($num,$gid,true);
		}else{$ldap->GroupForbiddenAttachment($num,$gid,false);}
	
	}
	
	
	
	
		
}

function FORBIDDEN_ATTACHMENTS_ADDRULE(){
	
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($_GET["group_add_attach_rule"]);
	$ou=$_GET["ou"];
	$rule=$hash["cn"]. "_attach";
	
	
	
	
	
$dn="cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixStructuralClass';	
		$upd["cn"]="forbidden_attachments";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
	}

	
for($i=1;$i<100;$i++){
	$dn="cn={$rule}-$i,cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';	
		$upd["objectClass"][]='FilterExtensionsGroup';
		$upd["cn"]="{$rule}-$i";
		$rule="{$rule}-$i";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
		break;
	}		
	
}
	

	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';	
		$upd["objectClass"][]='FilterExtensionsGroup';
		$upd["cn"]=$rule;
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;exit;}
		unset($upd);
	}	

	
	$upd["FiltersExtensionsGroupName"]=$rule;
	if($ldap->Ldap_add_mod($hash["dn"],$upd)){echo $ldap->ldap_last_error;}	
	
	
}




function FORBIDDEN_ATTACHMENTS_GROUPS($gid){
	
	
	$ldap=new clladp();	
	$hashG=$ldap->GroupDatas($gid);
	$ou=$hashG["ou"];
	if($ou==null){$ou=$_GET["ou"];}
	$page=CurrentPageName();
	
	$path="cn=forbidden_attachments,ou=$ou,dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($path,'(&(ObjectClass=FilterExtensionsGroup)(cn=*))',array('cn'));
	
	
	$html="
	<center><input type='button' value='&laquo;&nbsp;{add_attach_rule}&nbsp;&raquo;' OnClick=\"javascript:group_add_attach_rule('$gid');\"></center>
	<form name='FFM1'>
	
	<input type='hidden' name='SaveAttachmentGroup' value='$gid'>
	<table style='width:100%;padding:1px;border:1px solid #CCCCCC;margin:20px'>
		<tr style='background-color:#CCCCCC'>
		<th>&nbsp;</th>
		<th><strong>{artica_filtersext_rules}&nbsp;{group}</th>
		<th><strong>{enabled}</th>
		</tr>";
	if(is_array($hash)){
	for($i=0;$i<$hash["count"];$i++){
		$group_name=$hash[$i]["cn"][0];
		if(trim($group_name)<>null){
				if($hashG["FiltersExtensionsGroupName"][$group_name]=="yes"){$value='yes';}else{$value="no";}
				
				$html=$html . "
				<tr style='background-color:#F3F3DF'>
				<td width='1%'><img src='img/red-pushpin-24.png'></td>
				<td><strong style='font-size:13px'>$group_name</strong></td>
				<td width=1% align='center'>" . Field_yesno_checkbox_img($group_name,$value,'{enable_disable}') . "</td>
				</tr>";
			}
		}}
		return $html."
		<tr>
		<td width=1% valign='top' align='right' style='background-color:#F6F5E7' colspan=3>
		<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM1','$page',true);\">
		</td>
		</FORM>
		</table>";	
	
}




function GROUP_KAVMILTER_ADD_NEW_RULE($gid){
	include_once('ressources/class.kavmilterd.inc');
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$milter=new kavmilterd();
	$milter->LoadRule("{$hash["cn"]}.{$hash["ou"]}");
	$milter->SaveRuleToLdap();
	$milter->KavMilterdGroup=$gid;
	$milter->AddRuleToGroup();
	
	
	
}


function GROUP_ATTACHMENTS($gid){
	
		$ldap=new clladp();
    	$hash=$ldap->GroupDatas($gid);
    	$ou=$hash["ou"];	
    	
    	$html="<H5>{artica_filtersext_rules}</H5>" . RoundedLightGreen("
    	<div class=caption>{attachments_deny_text}</div>
   		" . FORBIDDEN_ATTACHMENTS_GROUPS($gid));
    	
    	$tpl=new templates();
    	echo $tpl->_ENGINE_parse_body($html);
    	}
    	
    	
function GROUP_DANSGUARDIAN($gid){
		include_once('ressources/class.dansguardian.inc');
		$users=new usersMenus();
		
	    $ldap=new clladp();
    	$hashG=$ldap->GroupDatas($gid);
    	$ou=$hash["ou"];
    	
    	$dans=new dansguardian($users->hostname);
    	$hash=$dans->Master_rules_index;
    	
    	if(is_array($hash)){
		while (list ($num, $line) = each ($hash)){
			if(preg_match('#(.+?);(.+)#',$line,$re)){
				$rulename=$re[1];
			
			}else{
				$rulename=$line;
			}
			
			$rules[$num]=$rulename;
		}
    	}
    	
    	$rules[0]="{no_rules}";
    	$field=Field_array_Hash($rules,'dansguardian_rule',$hashG["ArticaDansGuardianGroupRuleEnabled"],null,null,0,"width:300px;font-size:13px;padding:5px");
    	
    	
    	$form="
    	<table style='width:100%'>
    	<tr>
    	<td align='right' nowrap><strong>{selected_rule}:&nbsp;</strong></td>
    	<td width=70%>$field</td>
    	<td align=left><input type=button value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditGroupDansGuardianRule('$gid','$ou');\" style='width:200px'></td>
    	</tr>
    	</table>
    	";
    	
    	$form=RoundedLightGreen($form);
    	
    	
    	$html="<br>
    	<H5>{dansguardian_rules}</H5>
    	<p class=caption>{dansguardian_rules_text}</p>
    	<br>
    	$form
    	<br>
    	";
    	
    	
    	$tpl=new templates();
    	echo $tpl->_ENGINE_parse_body($html);
    			
    	}
function GROUP_DANSGUARDIAN_SAVE(){
	$ldap=new clladp();
	$hashG=$ldap->GroupDatas($_GET["gpid"]);
	$upd["ArticaDansGuardianGroupRuleEnabled"][0]=$_GET["save_dansguardian_rule"];
	$ldap->Ldap_modify($hashG["dn"],$upd);
	echo $ldap->ldap_last_error;
	
}


    	

function GROUP_PRIVILEGES_TABS($gid){
	$page=CurrentPageName();
	$users=new usersMenus();
	if($gid==-1){$addon="&ou={$_GET["ou"]}";}
	if($gid==-2){$addon="&userid={$_GET["userid"]}";}
	$time=time();
	$array["U"]='{users_allow}';
	
	if($users->AllowEditOuSecurity){
		$array["G"]='{groups_allow}';
		$array["O"]='{organization_allow}';
		$array["A"]='{administrators_allow}';		
	}
	
	while (list ($num, $ligne) = each ($array) ){
		$a[]="<li><a href=\"$page?GroupPriv=$gid&tab=$num$addon\"><span>$ligne</span></a></li>\n";
			
		}
		
$html="
	<div id='{$time}_priv' style='background-color:white;height:450px;overflow:auto;width:100%'>
	<ul>
		". implode("\n",$a). "
	</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#{$time}_priv').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			$('#{$gid}_priv').tabs('option', 'fx', { opacity: 'toggle' });
			});
		</script>
	
	";

		$tpl=new templates();
    	return $tpl->_ENGINE_parse_body($html);
			
}  






    	
function GROUP_PRIVILEGES($gid){
	    $usr=new usersMenus();
	    
    	if(!isset($_GET["tab"])){
    		echo GROUP_PRIVILEGES_TABS($gid);
    		return;
    		
    	}
    	
    	if(isset($_GET["start"])){
    		if($gid==-1){$oudiv=md5($_GET["ou"]);}
    		if($gid==-2){$oudiv=md5($_GET["userid"]);}
    		$div1="<div id='{$gid}{$oudiv}_priv'>";
    		$div2="</div>";
    		
    	}
    	
    	
		if($gid>1){    	
			$group=new groups($gid);
    		$hash=$group->LoadDatas($gid);
    		if($usr->SAMBA_INSTALLED){$group->TransformGroupToSmbGroup();}
    		$ou=$hash["ou"];
    		$HashPrivieleges=$hash["ArticaGroupPrivileges"];
    		$title_form="{group}: &laquo;{$hash["cn"]}";
		}
    	
		if($gid==-1){
			$ou=base64_decode($_GET["ou"]);
			$ldap=new clladp();
			$hash=$ldap->OUDatas($ou);
			$privs=$hash["ArticaGroupPrivileges"];
			$HashPrivieleges=$ldap->_ParsePrivieleges($privs,array());
			$organization_hidden="<input type='hidden' name='ou' value='$ou'>";
			$title_form="{organization}: &laquo;$ou";
		}

		if($gid==-2){
			$userclass=new user($_GET["userid"]);
			$ou=base64_decode($userclass->ou);
			$ldap=new clladp();
			$hash=$ldap->OUDatas($ou);
			$privs=$userclass->ArticaGroupPrivileges;
			$HashPrivieleges=$ldap->_ParsePrivieleges($privs,array());
			$organization_hidden="<input type='hidden' name='userid' value='{$_GET["userid"]}'>";
			$title_form="{member}: &laquo;{$_GET["userid"]}";
			$warn="<div class=explain>{privileges_users_warning}</div>";
		} 		
    	
    	
    	$priv= new usersMenus();
    	
    	
    	$AllowAddGroup=Field_yesno_checkbox('AllowAddGroup',$HashPrivieleges["AllowAddGroup"]);
    	$AllowAddUsers=Field_yesno_checkbox('AllowAddUsers',$HashPrivieleges["AllowAddUsers"]);
    	$AsArticaAdministrator=Field_yesno_checkbox('AsArticaAdministrator',$HashPrivieleges["AsArticaAdministrator"]);
    	$AllowChangeDomains=Field_yesno_checkbox('AllowChangeDomains',$HashPrivieleges["AllowChangeDomains"]);
    	$AsSystemAdministrator=Field_yesno_checkbox('AsSystemAdministrator',$HashPrivieleges["AsSystemAdministrator"]);
    	$AsSambaAdministrator=Field_yesno_checkbox('AsSambaAdministrator',$HashPrivieleges["AsSambaAdministrator"]);
    	$AsDnsAdministrator=Field_yesno_checkbox('AsDnsAdministrator',$HashPrivieleges["AsDnsAdministrator"]);
    	$AsQuarantineAdministrator=Field_yesno_checkbox('AsQuarantineAdministrator',$HashPrivieleges["AsQuarantineAdministrator"]);
    	$AsMailManAdministrator=Field_yesno_checkbox('AsMailManAdministrator',$HashPrivieleges["AsMailManAdministrator"]);
    	$AsOrgStorageAdministrator=Field_yesno_checkbox('AsOrgStorageAdministrator',$HashPrivieleges["AsOrgStorageAdministrator"]);
    	$AllowManageOwnComputers=Field_yesno_checkbox('AllowManageOwnComputers',$HashPrivieleges["AllowManageOwnComputers"]);
    	$AsOrgPostfixAdministrator=Field_yesno_checkbox('AsOrgPostfixAdministrator',$HashPrivieleges["AsOrgPostfixAdministrator"]);
    	$AsDansGuardianGroupRule=Field_yesno_checkbox('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    	$AsMessagingOrg=Field_yesno_checkbox('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"],"CheckHasOrgAdmin()");
    	$AsOrgAdmin=Field_yesno_checkbox('AsOrgAdmin',$HashPrivieleges["AsOrgAdmin"],"CheckHasOrgAdmin()");
    	$AsInventoryAdmin=Field_yesno_checkbox('AsInventoryAdmin',$HashPrivieleges["AsInventoryAdmin"]);
    	$AsJoomlaWebMaster=Field_yesno_checkbox('AsJoomlaWebMaster',$HashPrivieleges["AsJoomlaWebMaster"]);
    	$AsVirtualBoxManager=Field_yesno_checkbox('AsVirtualBoxManager',$HashPrivieleges["AsVirtualBoxManager"]);
    	$OverWriteRestrictedDomains=Field_yesno_checkbox('OverWriteRestrictedDomains',$HashPrivieleges["OverWriteRestrictedDomains"]);
    	$AsWebMaster=Field_yesno_checkbox('AsWebMaster',$HashPrivieleges["AsWebMaster"]);
    	$AsComplexPassword=Field_yesno_checkbox('AsComplexPassword',$HashPrivieleges["AsComplexPassword"]);
    	$AllowAddGroup=Field_yesno_checkbox('AllowAddGroup',$HashPrivieleges["AllowAddGroup"]);
    	$RestrictNabToGroups=Field_yesno_checkbox('RestrictNabToGroups',$HashPrivieleges["RestrictNabToGroups"]);
    	
    	
    	if($priv->AllowAddUsers==false){
    		$AllowAddUsers="<img src='img/status_critical.gif'>".Field_hidden('AllowAddUsers',$HashPrivieleges["AllowAddUsers"]);
    		$AsDansGuardianGroupRule="<img src='img/status_critical.gif'>".Field_hidden('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    		$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
    		$AsOrgAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsOrgAdmin',$HashPrivieleges["AsOrgAdmin"]);
    		$AsJoomlaWebMaster="<img src='img/status_critical.gif'>".Field_hidden('AsJoomlaWebMaster',$HashPrivieleges["AsJoomlaWebMaster"]);
    		$AsVirtualBoxManager="<img src='img/status_critical.gif'>".Field_hidden('AsVirtualBoxManager',$HashPrivieleges["AsVirtualBoxManager"]);
    		$AsComplexPassword="<img src='img/status_critical.gif'>".Field_hidden('AsComplexPassword',$HashPrivieleges["AsComplexPassword"]);
    		$RestrictNabToGroups="<img src='img/status_critical.gif'>".Field_hidden('RestrictNabToGroups',$HashPrivieleges["RestrictNabToGroups"]);
    	}
    	if($priv->AsArticaAdministrator==false){
    		$AsArticaAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsArticaAdministrator',$HashPrivieleges["AsArticaAdministrator"]);
    		$AsSambaAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsSambaAdministrator',$HashPrivieleges["AsSambaAdministrator"]);
    		$AsDnsAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsDnsAdministrator',$HashPrivieleges["AsDnsAdministrator"]);
    		$AsQuarantineAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsQuarantineAdministrator',$HashPrivieleges["AsQuarantineAdministrator"]);
    		$AsOrgStorageAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgStorageAdministrator',$HashPrivieleges["AsOrgStorageAdministrator"]);
    		$AsOrgPostfixAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgPostfixAdministrator',$HashPrivieleges["AsOrgPostfixAdministrator"]);
    		$AsDansGuardianGroupRule="<img src='img/status_critical.gif'>".Field_hidden('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    		$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
    		$AsOrgAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsOrgAdmin',$HashPrivieleges["AsOrgAdmin"]);
    		$AsInventoryAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsInventoryAdmin',$HashPrivieleges["AsInventoryAdmin"]);
    		$AsVirtualBoxManager="<img src='img/status_critical.gif'>".Field_hidden('AsVirtualBoxManager',$HashPrivieleges["AsVirtualBoxManager"]);
			$OverWriteRestrictedDomains="<img src='img/status_critical.gif'>".Field_hidden('OverWriteRestrictedDomains',$HashPrivieleges["OverWriteRestrictedDomains"]);
    		
    		
		}
		
		if(!$priv->AsOrgAdmin){
			$AsWebMaster="<img src='img/status_critical.gif'>".Field_hidden('AsWebMaster',$HashPrivieleges["AsWebMaster"]);
		}
    		
    		
    	if($priv->AllowAddGroup==false){
    		$AllowAddGroup="<img src='img/status_critical.gif'>".Field_hidden('AllowAddGroup',$HashPrivieleges["AllowAddGroup"]);
    		$AsDansGuardianGroupRule="<img src='img/status_critical.gif'>".Field_hidden('AsDansGuardianGroupRule',$HashPrivieleges["AsDansGuardianGroupRule"]);
    		$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);
    		$AsOrgAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsOrgAdmin',$HashPrivieleges["AsOrgAdmin"]);
    		$AsInventoryAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsInventoryAdmin',$HashPrivieleges["AsInventoryAdmin"]);
    		$AsJoomlaWebMaster="<img src='img/status_critical.gif'>".Field_hidden('AsJoomlaWebMaster',$HashPrivieleges["AsJoomlaWebMaster"]);
    		$AsVirtualBoxManager="<img src='img/status_critical.gif'>".Field_hidden('AsVirtualBoxManager',$HashPrivieleges["AsVirtualBoxManager"]);
    		
    	
    	}
    	if($priv->AllowChangeDomains==false){$AllowChangeDomains="<img src='img/status_critical.gif'>".Field_hidden('AllowChangeDomains',$HashPrivieleges["AllowChangeDomains"]);}
    	if($priv->AsSystemAdministrator==false){$AsSystemAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsSystemAdministrator',$HashPrivieleges["AsSystemAdministrator"]);}
    	if($priv->AsDnsAdministrator==false){$AsDnsAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsDnsAdministrator',$HashPrivieleges["AsDnsAdministrator"]);}
    	if($priv->AsQuarantineAdministrator==false){$AsQuarantineAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsQuarantineAdministrator',$HashPrivieleges["AsQuarantineAdministrator"]);}
		if($priv->AsOrgStorageAdministrator==false){$AsOrgStorageAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgStorageAdministrator',$HashPrivieleges["AsOrgStorageAdministrator"]);}
		if($priv->AsOrgPostfixAdministrator==false){$AsOrgPostfixAdministrator="<img src='img/status_critical.gif'>".Field_hidden('AsOrgPostfixAdministrator',$HashPrivieleges["AsOrgPostfixAdministrator"]);}
		if($priv->AsMessagingOrg==false){$AsMessagingOrg="<img src='img/status_critical.gif'>".Field_hidden('AsMessagingOrg',$HashPrivieleges["AsMessagingOrg"]);}
		if($priv->AsOrgAdmin==false){$AsOrgAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsOrgAdmin',$HashPrivieleges["AsOrgAdmin"]);}
		if($priv->AsInventoryAdmin==false){$AsInventoryAdmin="<img src='img/status_critical.gif'>".Field_hidden('AsInventoryAdmin',$HashPrivieleges["AsInventoryAdmin"]);}
		if($priv->AsJoomlaWebMaster==false){$AsJoomlaWebMaster="<img src='img/status_critical.gif'>".Field_hidden('AsJoomlaWebMaster',$HashPrivieleges["AsJoomlaWebMaster"]);}
		if($priv->AsVirtualBoxManager==false){$AsVirtualBoxManager="<img src='img/status_critical.gif'>".Field_hidden('AsVirtualBoxManager',$HashPrivieleges["AsVirtualBoxManager"]);}
		
		
		
		
    	
    	
    	
$group_allow="&nbsp;{groups_allow}</H3><br>
		<table style='width:100%' class=table_form>
		
			<tr>
				<td align='right'><strong>{AllowAddUsers}:</td><td>$AllowAddUsers</td>
			</tr>
			<tr>
				<td align='right'><strong>{AsDansGuardianGroupRule}:</td><td>$AsDansGuardianGroupRule</td>
			</tr>			
			
			
		</table>
";  	
    	
$user_allow="&nbsp;{users_allow}</H3><br>
					<table style='width:100%' class=table_form>
						
						<tr>
							<td align='right' nowrap><strong>{AllowChangeAntiSpamSettings}:</td><td>" . Field_yesno_checkbox('AllowChangeAntiSpamSettings',$HashPrivieleges["AllowChangeAntiSpamSettings"]) ."</td>
						</tr>											
						<tr>
							<td align='right' nowrap><strong>{AllowChangeUserPassword}:</td><td>" . Field_yesno_checkbox('AllowChangeUserPassword',$HashPrivieleges["AllowChangeUserPassword"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AsComplexPassword}:</td><td>$AsComplexPassword</td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong>{AllowFetchMails}:</td><td>" . Field_yesno_checkbox('AllowFetchMails',$HashPrivieleges["AllowFetchMails"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowChangeUserKas}:</td><td>" . Field_yesno_checkbox('AllowChangeUserKas',$HashPrivieleges["AllowChangeUserKas"]) ."</td>
						</tr>												
						<tr>
							<td align='right' nowrap><strong>{AllowEditAliases}:</td><td>" . Field_yesno_checkbox('AllowEditAliases',$HashPrivieleges["AllowEditAliases"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowChangeMailBoxRules}:</td><td>" . Field_yesno_checkbox('AllowChangeMailBoxRules',$HashPrivieleges["AllowChangeMailBoxRules"]) ."</td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong>{AllowSender_canonical}:</td><td>" . Field_yesno_checkbox('AllowSenderCanonical',$HashPrivieleges["AllowSenderCanonical"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowOpenVPN}:</td><td>" . Field_yesno_checkbox('AllowOpenVPN',$HashPrivieleges["AllowOpenVPN"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowDansGuardianBanned}:</td><td>" . Field_yesno_checkbox('AllowDansGuardianBanned',$HashPrivieleges["AllowDansGuardianBanned"]) ."</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AllowXapianDownload}:</td><td>" . Field_yesno_checkbox('AllowXapianDownload',$HashPrivieleges["AllowXapianDownload"]) ."</td>
						</tr>																									
						<tr>
							<td align='right' nowrap><strong>{AllowManageOwnComputers}:</td><td>" . Field_yesno_checkbox('AllowManageOwnComputers',$HashPrivieleges["AllowManageOwnComputers"]) ."</td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong>{AsJoomlaWebMaster}:</td><td>" . Field_yesno_checkbox('AsJoomlaWebMaster',$HashPrivieleges["AsJoomlaWebMaster"]) ."</td>
						</tr>						
						<tr>
						<td align='right' nowrap><strong>{RestrictNabToGroups}:</td>
						<td>$RestrictNabToGroups</td>
						</tr>
						
						
						<tr>
							<td align='right' nowrap><strong>{AllowEditAsWbl}:</td><td>" . Field_yesno_checkbox('AllowEditAsWbl',$HashPrivieleges["AllowEditAsWbl"]) ."</td>
						</tr>									
					</table>";

$org_allow="&nbsp;{organization_allow}</H3><br>
<table style='width:100%' class=table_form>	
	<tr>
		<td align='right' nowrap><strong>{AsOrgAdmin}:</td>
		<td>$AsOrgAdmin</td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{AsMessagingOrg}:</td>
		<td>$AsMessagingOrg</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{AllowEditOuSecurity}:</td>
		<td>" . Field_yesno_checkbox('AllowEditOuSecurity',$HashPrivieleges["AllowEditOuSecurity"]) ."</td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{AsOrgPostfixAdministrator}:</td>
		<td>$AsOrgPostfixAdministrator</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{AsQuarantineAdministrator}:</td>
		<td>$AsQuarantineAdministrator</td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{AsMailManAdministrator}:</td>
		<td>$AsMailManAdministrator</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{OverWriteRestrictedDomains}:</td>
		<td>$OverWriteRestrictedDomains</td>
	</tr>		
	
	<tr>
		<td align='right' nowrap><strong>{AsOrgStorageAdministrator}:</td>
		<td>$AsOrgStorageAdministrator</td>
	</tr>	

	<tr>
		<td align='right' nowrap><strong>{AsWebMaster}:</td>
		<td>$AsWebMaster</td>
	</tr>	
	<tr>
		<td align='right'><strong>{AllowChangeDomains}:</td><td>$AllowChangeDomains</td>
	</tr>	
</table>					
";


$admin_allow="&nbsp;{administrators_allow}</H3><br>
<table style='width:100%' class=table_form>
				
						<tr>
							<td align='right' nowrap><strong>{AsPostfixAdministrator}:</td>
							<td>" . Field_yesno_checkbox('AsPostfixAdministrator',$HashPrivieleges["AsPostfixAdministrator"]) ."</td>
						</tr>
						
						
						<tr>
							<td align='right' nowrap><strong>{AsSquidAdministrator}:</td>
							<td>" . Field_yesno_checkbox('AsSquidAdministrator',$HashPrivieleges["AsSquidAdministrator"]) ."</td>
						</tr>

						<tr>
							<td align='right' nowrap><strong>{AsSambaAdministrator}:</td>
							<td>$AsSambaAdministrator</td>
						</tr>						
											
						<tr>
							<td align='right' nowrap><strong>{AsArticaAdministrator}:</td>
							<td>$AsArticaAdministrator</td>
						</tr>						
						<tr>
							<td align='right' nowrap><strong>{AsSystemAdministrator}:</td>
							<td>$AsSystemAdministrator</td>
						</tr>	
						<tr>
							<td align='right' nowrap><strong>{AsDnsAdministrator}:</td>
							<td>$AsDnsAdministrator</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AsInventoryAdmin}:</td>
							<td>$AsInventoryAdmin</td>
						</tr>
						<tr>
							<td align='right' nowrap><strong>{AsVirtualBoxManager}:</td>
							<td>$AsVirtualBoxManager</td>
						</tr>																		
						<tr>
							<td align='right' nowrap><strong>{AsMailBoxAdministrator}:</td>
							<td>" . Field_yesno_checkbox('AsMailBoxAdministrator',$HashPrivieleges["AsMailBoxAdministrator"]) ."</td>
						</tr>	
						<tr>
							<td align='right' nowrap><strong>{AllowViewStatistics}:</td>
							<td>" . Field_yesno_checkbox('AllowViewStatistics',$HashPrivieleges["AllowViewStatistics"]) ."</td>
						</tr>																					
						</table>";
$sufform=$_GET["tab"];
switch ($_GET["tab"]) {
	case "G":$g=$group_allow;break;
	case "U":$g=$user_allow;break;
	case "A":$g=$admin_allow;break;
	case "O":$g=$org_allow;break;
	default:$g=$user_allow;break;
}


$page=CurrentPageName();
$html="
	$div1
	$warn
	<div style='padding:20px'>
	$tabs
	<form name='{$sufform}_priv'>
		$organization_hidden
		<input type='hidden' name='PrivilegesGroup' value='$gid'><br>
		<H3>$title_form&raquo;
		$g
		
		</form>
		<div style='text-align:right;'>". button("{submit}","EditGroupPrivileges()")."</div>

		</div>$div2

		<script>
		function EditGroupPrivileges(){
			ParseForm('{$sufform}_priv','$page',true);
			if(document.getElementById('groupprivileges')){document.getElementById('groupprivileges').innerHTML='';}
		}
		
		function CheckHasOrgAdmin(){
			if(!document.getElementById('AsOrgAdmin')){return;}		
			if(document.getElementById('AsOrgAdmin').checked){
				document.getElementById('AsOrgPostfixAdministrator').disabled=true;
				document.getElementById('AsQuarantineAdministrator').disabled=true;
				document.getElementById('AsMailManAdministrator').disabled=true;
				document.getElementById('AsOrgStorageAdministrator').disabled=true;
				document.getElementById('AsMessagingOrg').disabled=true;
				document.getElementById('AsWebMaster').disabled=true;
				document.getElementById('AllowChangeDomains').disabled=true;
				document.getElementById('AllowEditOuSecurity').disabled=true;				
			}else{
				document.getElementById('AsOrgPostfixAdministrator').disabled=false;
				document.getElementById('AsQuarantineAdministrator').disabled=false;
				document.getElementById('AsMailManAdministrator').disabled=false;
				document.getElementById('AsOrgStorageAdministrator').disabled=false;
				document.getElementById('AsMessagingOrg').disabled=false;
				document.getElementById('AsWebMaster').disabled=false;
				document.getElementById('AllowChangeDomains').disabled=false;	
				document.getElementById('AllowEditOuSecurity').disabled=false;
				CheckAsMessagingOrg();						
			}
		
		}
		
		
		function CheckAsMessagingOrg(){
			if(document.getElementById('AsMessagingOrg').checked){
				document.getElementById('AsQuarantineAdministrator').disabled=true;
				document.getElementById('AsMailManAdministrator').disabled=true;
				document.getElementById('AllowChangeDomains').disabled=true;
				CheckAsOrgPostfixAdministrator();
			}else{
				document.getElementById('AsQuarantineAdministrator').disabled=false;
				document.getElementById('AsMailManAdministrator').disabled=false;
				document.getElementById('AllowChangeDomains').disabled=false;			
				CheckAsOrgPostfixAdministrator();
			}
		
		}
		
		function CheckAsOrgPostfixAdministrator(){
			if(document.getElementById('AsOrgPostfixAdministrator').checked){
				document.getElementById('OverWriteRestrictedDomains').disabled=true;
			
			}else{
				document.getElementById('OverWriteRestrictedDomains').disabled=false;
			
			}
		
		}
		
		
		
		CheckHasOrgAdmin();
		</script>
		
		";
    	
	$tpl=new templates();
    	return $tpl->_ENGINE_parse_body($html);
}
function EditGroup(){
	$gid=$_GET["PrivilegesGroup"];
	$ldap=new clladp();
	$update_array=array();
	writelogs("Save privileges for $gid",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	switch ($gid) {
		case -1:
			$Hash=$ldap->OUDatas($_GET["ou"]);
			writelogs("Loading ou datas of \"{$_GET["ou"]}\" ArticaGroupPrivileges=". strlen($Hash["ArticaGroupPrivileges"]) ." bytes",__FUNCTION__,__FILE__,__LINE__);
			
			break;
		case -2:
			$user=new user($_GET["userid"]);
			$Hash=$user->ArticaGroupPrivileges;
			break;
		default:$Hash=$ldap->GroupDatas($gid);break;
	}
	
		
	if(!is_array($Hash["ArticaGroupPrivileges"])){
		$ArticaGroupPrivileges=$ldap->_ParsePrivieleges($Hash["ArticaGroupPrivileges"]);
	}else{
		$ArticaGroupPrivileges=$Hash["ArticaGroupPrivileges"];
	}
	
	
	if(is_array($ArticaGroupPrivileges)){while (list ($num, $val) = each ($ArticaGroupPrivileges) ){$GroupPrivilege[$num]=$val;}}
	while (list ($num, $val) = each ($_GET) ){$GroupPrivilege[$num]=$val;}		
	while (list ($num, $val) = each ($GroupPrivilege) ){if($val=="no"){continue;} $values=$values . "[$num]=\"$val\"\n";}

	
	if($gid==-2){
		$user->SavePrivileges($values);
		return;
	}

	$update_array["ArticaGroupPrivileges"][0]=$values;
	writelogs("Modify: {$Hash["dn"]}",__FUNCTION__,__FILE__,__LINE__);
	if(!$ldap->Ldap_modify($Hash["dn"],$update_array)){
		echo basename(__FILE__)."\nline: ".__LINE__."\n".$ldap->ldap_last_error;
	}
		
	
	
}
function DeleteMember(){
	$usermenu=new usersMenus();
	$tpl=new templates();
	if($usermenu->AllowAddUsers==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	$ldap=new clladp();
	$Userdatas=$ldap->UserDatas($_GET["DeleteMember"]);
	$dn=$Userdatas["dn"];
	$ldap->ldap_delete($dn,false);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}	
	}
function DeleteGroup(){
	
	if(isset($_GET["DeleteGroup"])){$gpid=$_GET["DeleteGroup"];}
	if(isset($_GET["delgroup"])){$gpid=$_GET["delgroup"];}
	$ou=$_GET["ou"];
	
	$ldap=new clladp();
	$tpl=new templates();
	$classGroup=new groups($gpid);
	$hashgroup=$ldap->GroupDatas($gpid);
	$default_dn_nogroup="cn=nogroup,ou=groups,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($default_dn_nogroup)){$ldap->AddGroup("nogroup",$ou);}
	$nogroup_id=$ldap->GroupIDFromName($ou,"nogroup");	
	
	if(is_array($hashgroup["members"])){
		while (list ($num, $val) = each ($hashgroup["members"]) ){
			$ldap->AddUserToGroup($nogroup_id,$num);
		}
	}
	
	$users=new usersMenus();
	if($users->KAV_MILTER_INSTALLED){
		$sock=new sockets();
		$sock->getfile("KavMilterDeleteRule:$classGroup->groupName.$classGroup->ou");
	}
	
	
	$kas_dn="cn=$gpid,cn=kaspersky Antispam 3 rules,cn=artica,$ldap->suffix";
	if($ldap->ExistsDN($kas_dn)){$ldap->ldap_delete($kas_dn,false);}
	$ldap->ldap_delete($hashgroup["dn"],false);
	

	
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;}else{echo $tpl->_ENGINE_parse_body('{success}');}
	}	
	
function browser(){
	$html="
	<input type='hidden' id='YahooSelectedFolders_ask' value='{YahooSelectedFolders_ask}'>
	<div id='folderTree'>
	</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	

	
function COMPUTERS_LIST(){
	$gpid=$_GET["gpid"];
	$computer_list=COMPUTERS_LIST_LIST();
	$js=MEMBER_JS('NewComputer$',1);
	$html="<br>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=60% style='padding:5px'>$computer_list</td>
		<td valign='top'>". RoundedLightGrey(Paragraphe("computer-search-add-64.png","{find_computer}","{addfind_computer_text}","javascript:addComputer($gpid)")).
	"<br>".RoundedLightGrey(Paragraphe("computer-64-add.png","{add_computer}","{add_computer_text}","javascript:YahooUser(670,\"domains.edit.user.php?userid=newcomputer$&ajaxmode=yes&gpid=$gpid\",\"windows: New {add_computer}\");"))."</td>
	</tr>	
	</table>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function COMPUTERS_LIST_LIST(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	$html="<table style='width:100%'>";
	
	while (list ($num, $val) = each ($group->computers_array) ){
		$js=MEMBER_JS($val,1);
		$html=$html.
		"<tr " . CellRollOver().">
			<td width=1%><img src='img/base.gif'></td>
			<td><strong>". texttooltip($val,'{view}',$js)."</td>
		</tr>";
		
	
	}	

	$html=$html  . "</table>";
	return RoundedLightGrey($html);
	
}

function COMPUTER_FORM_ADD(){
$gpid=$_GET["gpid"];
	$html="
	<input type='hidden' id='gpid' value='$gpid'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1% nowrap><strong>{find_computer}:</strong></td>
		<td valign='top'>".Field_text('find_computer',null,'width:100%')."</td>
		<td valign='top' width=1%><input type='button' value='{search}&nbsp;&raquo;' OnClick=\"javascrit:find_computer($gpid);\" style='margin:0px'></td>
		</tr>
	<tr>
	<td colspan=3  valign='top'><div id='computer_to_find'></div></td>
	</tr>
	</table>";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function COMPUTER_FIND(){
$gpid=$_GET["gpid"];
$ou=$_GET["ou"];
$tofind=$_GET["find_computer"];
$ldap=new clladp();
if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
$filter="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";

$attrs=array("uid","ComputerIP","ComputerOS");
$dn="dc=samba,$ldap->suffix";
$html="
<input type='hidden' id='add_computer_confirm' value='{add_computer_confirm}'>
<table style='width:100%'>";

$hash=$ldap->Ldap_search($dn,$filter,$attrs);
for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	$html=$html . 
	"<tr " .CellRollOver().">
	<td width=1%><img src='img/base.gif'></td>
	<td><strong>{$hash[$i]["uid"][0]}</strong></td>
	<td><strong>{$hash[$i][strtolower("ComputerIP")][0]}</strong></td>
	<td><strong>{$hash[$i][strtolower("ComputerOS")][0]}</strong></td>
	<td width=1%>"  . imgtootltip('plus-16.png','{add_computer}',"javascript:add_computer_selected('$gpid','{$hash[$i]["dn"]}','{$hash[$i]["uid"][0]}','$realuid')")."</td>
	</tr>
	";
	}
$html=$html . "</table>";
$html=RoundedLightGrey($html);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);				
	
}

function COMPUTER_ADD_TO_GROUP(){
	$dn=$_GET["add_computer_to_group"];
	$gpid=$_GET["gpid"];
	$uid=$_GET["uid"];
	writelogs("Adding $dn in group $gpid");
	$group=new groups($gpid);
	$group->AddUsertoThisGroup($uid);
	}
	
function GROUP_SAMBA_IDENTITY(){
	$page=CurrentPageName();
	$tpl=new templates();
	$group=new groups($_GET["gpid"]);	
	if($group->sambaSID==null){
		echo $tpl->_ENGINE_parse_body("<div class=explain>{not_group_samba}</div>");
		return;
	}
	
	$samba=new samba();
	$master_password=$samba->GetAdminPassword("administrator");
	$sock=new sockets();
	$password=urlencode(base64_encode($master_password));
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?pdbedit-group=$group->groupName&password=$password")));
	
	$html="<table style='width:100%' class=form>
	<tr>
		<td class=legend>{name}:</td>
		<td><strong style='font-size:12px'>$group->groupName</td>
	</tr>	
	<tr>
		<td class=legend>ID:</td>
		<td><strong style='font-size:12px'>$group->group_id</td>
	</tr>
	<tr>
		<td class=legend>{sambaSID}:</td>
		<td><strong style='font-size:12px'>$group->sambaSID</td>
	</tr>	
	</table>
	<p>&nbsp;</p>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{members}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	while (list ($num, $ligne) = each ($datas["MEMBERS"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
	
		$html=$html . "
		<tr  class=$classtr>
		<td width=100% style='font-size:14px' nowrap>{$ligne}</a></td>
		</tr>";
	}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}
	
function GROUP_SAMBA_SETTINGS_TABS(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["FORM_GROUP2"]='{MK_SAMBA_GROUP}';
	$array["FORM_GROUP_IDENTITY"]='{identity}';
		
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ou={$_GET["ou"]}&gpid={$_GET["gpid"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_smbgroup_{$_GET["gpid"]} style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_smbgroup_{$_GET["gpid"]}\").tabs();});
		</script>";		
	
	
	
}	
	
function GROUP_SAMBA_SETTINGS(){
	$page=CurrentPageName();
	$group=new groups($_GET["gpid"]);
	if($group->sambaSID==null){
		$text="{not_group_samba}";
	}else{$text="{yes_group_samba}";}
	
	$array_group=array(null=>"{select}",5=>"{smbg_typ2}",2=>"{smbg_typ}");
	$array_group=array(2=>"{smbg_typ}");
	
$html="<H5>{MK_SAMBA_GROUP}&raquo;$group->groupName</H5>
<div id='FFM1GPP'>
<input type='hidden' name='gpid' id='gpid' value='{$_GET["gpid"]}'>
<input type='hidden' name='ou' id='ou' value='{$_GET["ou"]}'>
<input type='hidden' name='SaveGroupSamba' id='SaveGroupSamba' value='yes'>
<div class=explain>$text</div>
<table style='width:100%'>
<tr>
	<td align='right' class=legend><strong>{sambaGroupType}</strong>:</td>
	<td>".Field_array_Hash($array_group,'sambaGroupType',$group->sambaGroupType,"style:font-size:13px;padding:3px")."</td>
</tr>
<tr>
	<td align='right' class=legend><strong>{sambaSID}</strong>:</td>
	<td><code style='font-size:11px;font-weight:bold'>$group->sambaSID</code></td>
</tr>
<tr>
	<td colspan=2 align='right'><hr>". button("{edit}","SaveGroupTypeSamba()")."</td>
</tr>
</table>
</div>
<script>
	var x_SaveGroupTypeSamba= function (obj) {
		var results=obj.responseText;
		Loadjs('domains.edit.group.php?ou={$_GET["ou"]}&js=yes');
		YahooWinHide();
	}
	
	function SaveGroupTypeSamba(){
		var XHR = new XHRConnection();
		XHR.appendData('gpid','{$_GET["gpid"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('SaveGroupSamba','yes');
		XHR.appendData('sambaGroupType',document.getElementById('sambaGroupType').value);
		document.getElementById('FFM1GPP').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SaveGroupTypeSamba);
	}	
</script>

";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}
function GROUP_SAMBA_SETTINGS_SAVE(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	$group->sambaGroupType=$_GET["sambaGroupType"];
	$group->EditAsSamba();
	}
	
function MEMBERS_DELETE(){
	$uid=$_GET["DeleteUserByUID"];
	$user=new user($uid);
	$user->DeleteUser();
	}
	
function ChangeGroupDescription(){
	$gpid=$_GET["gpid"];
	$group=new groups($gpid);
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div id='animatGPDIV'></div>
	<textarea id='GROUP_DESc' style='font-size:14px;font-family: Courrier New;border:1px solid;width:100%;height:90px;overflow:auto'>$group->description</textarea>
	<div style='text-align:right'><hr>". button("{apply}","SaveGrouPdescript()")."</div>
	
<script>
	var x_SaveGrouPdescript= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);document.getElementById('animatGPDIV').innerHTML='';return;}
		YahooWin5Hide();
		if(document.getElementById('main_group_config')){RefreshTab('main_group_config');}
	}
	
	function SaveGrouPdescript(){
		var XHR = new XHRConnection();
		XHR.appendData('gpid','{$_GET["gpid"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('SaveGrouPdescript',document.getElementById('GROUP_DESc').value);
		AnimateDiv('animatGPDIV');
		XHR.sendAndLoad('$page', 'POST',x_SaveGrouPdescript);
	}	
</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function ChangeGroupDescription_save(){
	$group=new groups($_POST["gpid"]);
	if(!$group->SaveDescription($_POST["SaveGrouPdescript"])){
		echo $group->ldap_error;
	}
}
	
	

?>	
