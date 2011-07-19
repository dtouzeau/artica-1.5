<?php
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.users.menus.inc');
include_once ('ressources/class.user.inc');
include_once ('ressources/class.computers.inc');

$change_aliases = GetRights_aliases();

if($change_aliases<>1){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["popup-list"])){popup_list();exit;}
if(isset($_GET["AddMemberGroup"])){AddMemberGroup();exit;}
if(isset($_GET["AddNewComputerGroup"])){AddNewComputerGroup();exit;}
//domains.group.user.affect.php

js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add_group=$tpl->_ENGINE_parse_body("{add_group}");
	$html="
		YahooWin4('450','$page?popup=yes&ou={$_GET["ou"]}&uid={$_GET["uid"]}','$add_group');
	";
		
	echo $html;
	
}


function popup() {
	$page=CurrentPageName();
	$tpl=new templates();
	$ADD_USER_GROUP_ASK=$tpl->javascript_parse_text("{ADD_USER_GROUP_ASK}");
	$groupname=$tpl->_ENGINE_parse_body("{groupname}");
	if(strpos($_GET["uid"],'$')>0){
		$add="
		<div style='text-align:right;margin:8px'>". imgtootltip("32-group-icon-add.png","{add_group}","AddNewGroupComp()")."</div>
		
		";
		
	}
	
	$html = "$add
	<div style='width:100%;height:300px;overflow:auto' id='AddGroupAffectDiv'></div>
	
	
	<script>
		
		
		var x_AddPopUpGroupAjaxMode= function (obj) {
			if(document.getElementById('POPUP_MEMBER_GROUP_ID')){
				LoadAjax('POPUP_MEMBER_GROUP_ID','domains.edit.user.php'+'?POPUP_MEMBER_GROUP_ID=yes&ajaxmode=yes&ou={$_GET["ou"]}&userid={$_GET["uid"]}');
			}
	
		}	
	
function AddAjaxPopUpGroupV2(ou,uid,gid,gidname){
	var text='$ADD_USER_GROUP_ASK\\n\\nuid:{$_GET["uid"]}\\nGroup:'+gid+'\\nName:'+gidname;
	if(confirm(text)){
		group=gid;
		var XHR = new XHRConnection();
		XHR.appendData('user','{$_GET["uid"]}');
		XHR.appendData('userid','{$_GET["uid"]}');
		XHR.appendData('AddMemberGroup',group);
		if(document.getElementById('POPUP_MEMBER_GROUP_ID')){document.getElementById('POPUP_MEMBER_GROUP_ID').innerHTML='<center><img src=img/wait_verybig.gif></center>';}
		XHR.sendAndLoad('$page', 'GET',x_AddPopUpGroupAjaxMode);
		}
	
	}
	
		var x_AddNewGroupComp= function (obj) {
			var results=trim(obj.responseText);
			if(results.length>0){alert(results);}
			RefreshGroup();
	
		}

	function AddNewGroupComp(){
		var groupname=prompt('$groupname');
		if(groupname){
			groupname=escape(groupname);
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('AddNewComputerGroup',groupname);
			if(document.getElementById('POPUP_MEMBER_GROUP_ID')){document.getElementById('POPUP_MEMBER_GROUP_ID').innerHTML='<center><img src=img/wait_verybig.gif></center>';}
			XHR.sendAndLoad('$page', 'GET',x_AddNewGroupComp);
			
		}
	}

	function RefreshGroup(){
		LoadAjax('AddGroupAffectDiv','$page?popup-list=yes&ou={$_GET["ou"]}&uid={$_GET["uid"]}');
	}
	RefreshGroup();	
	</script>
	
	";
	$tpl = new Templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );

}


function popup_list(){
	
	$group = new groups ( );
	$ou_con = base64_decode ( $_GET ["ou"] );
	if ($ou_con != null) {$_GET ["ou"] = $ou_con;}
	$hash_group = $group->list_of_groups ( $_GET ["ou"], 1 );
	$hash_group [null] = "{no_group}";
	$uid = $_GET ["uid"];	
	
$html="<table style='width:80%'>";
		
			

	
	while ( list ( $num, $ligne ) = each ( $hash_group ) ) {
		$html = $html . "<tr " . CellRollOver ( "AddAjaxPopUpGroupV2('$ou','$uid',$num,'$ligne');", "{add_group} $ligne" ) . ">
			<td width=1%><img src='img/32-group-icon.png'></td>
			<td valign='middle' style='font-size:14px;font-weight:bold'>$ligne ($num)</a></td>
			</tr>";
	
	}
	
	$html = $html . "</table>";	
	$tpl = new Templates ( );
	echo $tpl->_ENGINE_parse_body ( $html );	
}

function AddMemberGroup() {
	$usr = new usersMenus ( );
	$tpl = new templates ( );
	
	writelogs ( "Adding user {$_GET["user"]} to group {$_GET["AddMemberGroup"]}", __FUNCTION__, __FILE__, __LINE__ );
	
	if ($usr->AllowAddGroup == false) {
		writelogs ( "The administrator have no provileges to execute this operation....", __FUNCTION__, __FILE__, __LINE__ );
		echo $tpl->_ENGINE_parse_body ( '{no_privileges}' );
		echo Page ( $_GET ["user"] );
		exit ();
	}
	
	if (trim ( $_GET ["AddMemberGroup"] == null )) {
		return null;
	}
	$ldap = new clladp ( );
	$ldap->AddUserToGroup ( $_GET ["AddMemberGroup"], $_GET ["user"] );
	if ($ldap->ldap_last_error != null) {
		echo $ldap->ldap_last_error;
	} else {
		$tpl = new templates ( );
		echo html_entity_decode ( $tpl->_ENGINE_parse_body ( "{success}: {$_GET["user"]} to group {$_GET["AddMemberGroup"]}" ) );
		writelogs ( "Adding user {$_GET["user"]} to group {$_GET["AddMemberGroup"]} => SUCCESS", __FUNCTION__, __FILE__, __LINE__ );
	}
	
	die ();
}


function AddNewComputerGroup(){
	$group = new groups ( );
	if(!$group->add_new_group($_GET["AddNewComputerGroup"])){
		echo $group->ldap_error;
		return;
	}
	
	$gpid=$group->GroupIDFromName(null,$_GET["AddNewComputerGroup"]);
	$group=new groups($gpid);
	$group->TransformGroupToSmbGroup();
	
	
}


?>