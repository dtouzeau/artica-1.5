/**
 * @author touzeau
 */

var Working_page="domains.edit.group.php";
var Winid;
var mem_gid;
var mem_uid;
var mem_edit_group_timeout=0;



function GroupDelete(gpid){
	var ou=document.getElementById('ou').value;
	var text=document.getElementById('group_delete_text').value;
	if(!confirm(text)){
		return;
		}
		
	var XHR = new XHRConnection();
	XHR.appendData('ou',document.getElementById('ou').value);
	XHR.appendData('delgroup',gpid);
	
	if(document.getElementById('MembersList')){document.getElementById('MembersList').innerHTML='';}
	if(document.getElementById('GroupSettings')){document.getElementById('GroupSettings').innerHTML='';}
	if(Get_Cookie('ArticaIsDefaultSelectedGroupId')==gpid){
		Delete_Cookie('ArticaIsDefaultSelectedGroupId', '/', '');
	}
	
	XHR.sendAndLoad("domains.edit.group.php", 'GET',x_parseform);
	LoadAjax('grouplist',"domains.edit.group.php"+'?LoadGroupList='+ou);;
}


	
function GroupPrivileges(gid){
	YahooWin(650,"domains.edit.group.php"+'?GroupPriv=' + gid + '&start=yes')
	
	}


function LoadGroupSettings(index){
	var group_id='';
	var index_uri;
	var ou='';
	mem_edit_group_timeout=mem_edit_group_timeout+1;
	if(mem_edit_group_timeout>10){
		alert('timeout');
		return;
	}
	
	if(!document.getElementById('GroupSettings')){
		setTimeout('LoadGroupSettings('+index+')',900);
		return;
	}
	mem_edit_group_timeout=0;
	if(document.getElementById('SelectGroupList')){group_id=document.getElementById('SelectGroupList').value;}
	
	
	if(group_id.length==0){
		group_id=Get_Cookie('ArticaIsDefaultSelectedGroupId');
		if(!group_id){
			if(document.getElementById('SelectGroupList')){
				if(document.getElementById('SelectGroupList')){
					group_id=document.getElementById('SelectGroupList').value;
				}
			}
		}
	}
	
	
	if(group_id.length>0){
		Delete_Cookie('ArticaIsDefaultSelectedGroupId', '/', '');
		Delete_Cookie('ArticaIsDefaultSelectedGroupIdIndex', '/', '');
		Set_Cookie('ArticaIsDefaultSelectedGroupId', group_id, '3600', '/', '', '');
		if(index){
			Set_Cookie('ArticaIsDefaultSelectedGroupIdIndex', index, '3600', '/', '', '');	
		}
	}else{
	  group_id=Get_Cookie('ArticaIsDefaultSelectedGroupId');
	  index=Get_Cookie('ArticaIsDefaultSelectedGroupIdIndex');
	}
	// --> Create cookie 
	if(document.getElementById('SelectOuList')){
		ou=document.getElementById('SelectOuList').value;
	}
	if(ou.length==0){
		ou=document.getElementById('ou').value;
	}
	
	if (index){index_uri='&tab='+index}else{index_uri='';}
	LoadAjax('GroupSettings','domains.edit.group.php?LoadGroupSettings='+ group_id + '&ou='+ou+index_uri);
	if(document.getElementById('MembersList')){document.getElementById('MembersList').innerHTML='';}
	}


function LoadGroups(ou){
	LoadAjax('grouplist','domains.edit.group.php?LoadGroupList=' + ou);	
	}


function LoadMembers(groupid){
	var ou=document.getElementById('ou').value;
	YahooWin(650,'domains.edit.group.php?MembersList='+groupid + '&ou='+ou);
	
	//LoadAjax('MembersList','domains.edit.group.php?MembersList='+groupid + '&ou='+ou)
	}
	

	
function ImportMembers(groupid){
	var ou=document.getElementById('ou').value;
	LoadAjax('MembersList','domains.edit.group.php?ImportMembers='+groupid + '&ou=' + ou);
	
}

function ForbiddenAttach(groupid){
	var ou=document.getElementById('ou').value;
	LoadAjax('MembersList','domains.edit.group.php?ForbiddenAttach='+groupid + '&ou=' + ou)
	}
	
function DansGuardianRules(groupid){
	var ou=document.getElementById('ou').value;
	LoadAjax('MembersList','domains.edit.group.php?DansGuardian_rules='+groupid + '&ou=' + ou)	
	}
	
function SharedFolders(groupid){
var ou=document.getElementById('ou').value;
	LoadAjax('MembersList','domains.edit.group.php?sharedfolders='+groupid + '&ou=' + ou)		
}


function ImportGroupFile(){
	var import_file=document.getElementById('fullpath').value;
	var groupid=document.getElementById('groupid').value;
	var ou=document.getElementById('ou').value;
	LoadAjax('ActionImport','domains.edit.group.php?ImportMembersFile='+import_file + '&groupid='+groupid+'&ou=' +ou);
}

function DeleteNotAffectedUsers(){
	if(confirm(document.getElementById('delete_members_confirm').value)){
		var ou=document.getElementById('ou').value;
		LoadAjax('grouplist','domains.edit.group.php?DeleteNotAffectedUsers=yes&ou='+ ou);
	}
	
}


function DeleteMember(memberid,groupid){
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ memberid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteMember',memberid);
		XHR.sendAndLoad("domains.edit.group.php", 'GET',x_TreeFetchMailApplyConfig);
		MyHref("domains.edit.group.php"+ '?ou='+document.getElementById('ou').value)
		}
}
function DeleteGroup(gpid){
	var ou=document.getElementById('ou').value;
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ gpid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteGroup',gpid);
		XHR.sendAndLoad("domains.edit.group.php", 'GET',x_TreeFetchMailApplyConfig);
		MyHref("domains.edit.group.php"+ '?ou='+document.getElementById('ou').value)
		}
}


function group_add_attach_rule(gpid){
	var ou=document.getElementById('ou').value;
	var XHR = new XHRConnection();
	XHR.appendData('group_add_attach_rule',gpid);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad("domains.edit.group.php", 'GET',x_parseform);
	ForbiddenAttach(gpid);
}


	
	
function EditGroupDansGuardianRule(gpid,ou){
	var dansguardian_rule=document.getElementById('dansguardian_rule').value;
	ou=document.getElementById('ou').value;
	var XHR = new XHRConnection();
	XHR.appendData('save_dansguardian_rule',dansguardian_rule);
	XHR.appendData('gpid',gpid);
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad("domains.edit.group.php", 'GET',x_parseform);	
	DansGuardianRules(gpid);
	}
	
function GroupAdmin_AddMemberGroup(gpid){
	var ou=document.getElementById('ou').value;
	LoadAjax('members_area','domains.edit.group.php?AddMemberIntoGroup=yes&ou='+ ou + '&gpid='+ gpid);
	}
	
function LoadGroupList(){
	var ou=document.getElementById('SelectOuList').value;
	document.getElementById('GroupSettings').innerHTML='';
	LoadAjax('grouplist','domains.edit.group.php?LoadGroupList='+ou);
	}
	
function AddSharedFolder(gid){
	mem_gid=gid;
	var folder=document.getElementById('SharedFolderPath').value;
		var XHR = new XHRConnection();
		XHR.appendData('AddTreeFolders',folder);
		XHR.appendData('groupid',gid);
		document.getElementById('MembersList').innerHTML="<center style='width:100%'><img src='img/wait_verybig.gif'></center>";
		XHR.sendAndLoad("domains.edit.group.php", 'GET',x_SharedFolder);
	
	
}

var x_SharedFolder=function (obj) {
SharedFolders(mem_gid);
}

function SharedFolderDelete(num,gpid){
	var XHR = new XHRConnection();
	XHR.appendData('SharedFolderDelete',num);
	mem_gid=gpid;
	var ou=document.getElementById('ou').value;
	XHR.appendData('gpid',gpid);
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad("domains.edit.group.php", 'GET',x_SharedFolder);		
}

function RemoveSharedsFolders(){
	var txt=document.getElementById('RemoveSharedsFolders').value;
	if(confirm(txt)){
	var XHR = new XHRConnection();
	XHR.appendData('RemoveSharedsFolders','yes');
	XHR.sendAndLoad("domains.edit.group.php", 'GET',x_parseform);
		
	}
	
}

var x_LoadMailingList=function (obj) {
LoadMailingList();
}

function LoadMailingList(){
	var ou=document.getElementById('SelectOuList').value;
	Loadjs('domains.edit.group.php?LoadMailingList-js='+ou);
	
	
	}
function RemoveMailingList(ou,email){
	var RemoveMailingList_text=document.getElementById('RemoveMailingList_text').value;
	if(confirm(RemoveMailingList_text)){
		var XHR = new XHRConnection();
		XHR.appendData('RemoveMailingList',email);
		XHR.appendData('ou',ou);	
		XHR.sendAndLoad("domains.edit.group.php", 'GET',x_LoadMailingList);
	}
	
}


var x_add_computer_selected=function (obj) {
LoadComputerGroup(mem_gid);
}


function LoadComputerGroup(gpid){
	var ou=document.getElementById('ou').value;
	LoadAjax('MembersList','domains.edit.group.php?LoadComputerGroup=yes&ou='+ ou + '&gpid='+ gpid);	
	}
	
function addComputer(gpid){
	var ou=document.getElementById('ou').value;
	YahooWin(440,"domains.edit.group.php"+'?FORM_COMPUTER=yes&ou='+ou+'&gpid='+gpid);
}

function find_computer(gpid){
	var ou=document.getElementById('ou').value;
	var ss=document.getElementById('find_computer').value;
	LoadAjax('computer_to_find','domains.edit.group.php?find_computer='+ss+'&ou='+ ou + '&gpid='+ gpid);
	}
	
function add_computer_selected(gpid,dn,computer,uid){
	var ou=document.getElementById('ou').value;
	var text=document.getElementById('add_computer_confirm').value+'\n'+computer+' => '+gpid;
	if(confirm(text)){
		mem_gid=gpid;
		var XHR = new XHRConnection();
		XHR.appendData('add_computer_to_group',dn);
		XHR.appendData('gpid',gpid);
		XHR.appendData('ou',ou);
		XHR.appendData('uid',uid);	
		XHR.sendAndLoad("domains.edit.group.php", 'GET',x_add_computer_selected);		
	}
}


function Change_group_settings(gpid){
	var ou=document.getElementById('ou').value;
	YahooWin(540,'domains.edit.group.php?FORM_GROUP=yes&ou='+ou+'&gpid='+gpid);	
	}
	
function DeleteUID(uid){
	if(document.getElementById('deleteuid_'+uid).value==0){
	document.getElementById('icon_'+uid).src='img/ed_delete.gif';
	document.getElementById('deleteuid_'+uid).value='1';
	ShowDeleteSelected();
	}else{
	document.getElementById('icon_'+uid).src=document.getElementById('orgin_icon_'+uid).value;
	document.getElementById('deleteuid_'+uid).value='0';
	ShowDeleteSelected();
	}
}
function ShowDeleteSelected(){
var list=GetAllIdElements('deleteuid_');
var c=0;
for(i=0;i<list.length;i++){
	if(document.getElementById(list[i]).value==1){c=c+1;}
	
}
	var XHR = new XHRConnection();
	XHR.appendData('ShowDeleteSelected',c);
	XHR.setRefreshArea('ShowDeleteAll');
	XHR.sendAndLoad('domains.edit.group.php', 'GET');

}

var x_DeleteSelectedMembersGroup=function (obj) {
	
	return;
	
}


function DeleteSelectedMembersGroup(){
	var list=GetAllIdElements('deleteuid_');
	var c=0;
	for(i=0;i<list.length;i++){if(document.getElementById(list[i]).value==1){c=c+1;}}
	var text=document.getElementById('sure_to_delete_selected_user').value + ' ('  +c+' members)';	
	if(confirm(text)){
		for(i=0;i<list.length;i++){
			if(document.getElementById(list[i]).value==1){
				var m=list[i];
				var reg=m.match(/deleteuid_(.+)/);
				if(reg){
					if(reg.length>0){
						var  XHR = new XHRConnection();
						mem_uid=reg[1];
//						style.display = 'none';
						DeleteElementByID('mainid_'+reg[1]);
						XHR.appendData('DeleteUserByUID',reg[1]);
						XHR.sendAndLoad('domains.edit.group.php', 'GET',x_DeleteSelectedMembersGroup);
					}
				}
			}
		}
	}
}


var x_ChangeDefaultGroupPassword= function (obj) {
	alert(obj.responseText);
	YahooWin('400','domains.edit.group.php?default_password=yes&gpid='+mem_gid);
}

function ChangeDefaultGroupPassword(gpid){
	mem_gid=gpid;
	var pass1=document.getElementById('default_password1').value;
	var pass2=document.getElementById('default_password2').value;
	
	if(pass1!==pass2){
		alert(document.getElementById('error_passwords_mismatch').value);
		return;
	}
	
	if(pass1.length==0){
		alert(document.getElementById('error_passwords_mismatch').value);
		return;	
	}
	
	var XHR = new XHRConnection();
	XHR.appendData('ChangeDefaultGroupPassword',gpid);
	XHR.appendData('password',document.getElementById('default_password1').value);
	XHR.appendData('change_now',document.getElementById('change_now').value);
	document.getElementById('GROUP_DEFAULT_PASSWORD').innerHTML="<center style='width:100%'><img src='img/wait_verybig.gif'></center>";
	XHR.sendAndLoad('domains.edit.group.php', 'GET',x_ChangeDefaultGroupPassword);	
	
}

	


