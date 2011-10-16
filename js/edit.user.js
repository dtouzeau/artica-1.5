/**
 * @author touzeau
 */

var user_working_page="domains.edit.user.php";
var Winid;
var m_userid;
var m_ou;


var X_UserADD= function (obj) {
	//YahooUser
	var results=obj.responseText;
	var groupid='';
	if(document.getElementById('group_id')){groupid=document.getElementById('group_id').value;}
	if(document.getElementById('ajax_return_group_id')){groupid=document.getElementById('ajax_return_group_id').value;}

	
	var reg = new RegExp( "ERROR(.+)","gi" ); 
	if(results.match(reg)){
		alert(results);
		if(document.getElementById('adduser_ajax_newfrm')){
			if(typeof loadAdduser == 'function') {loadAdduser();}else{Loadjs("domains.add.user.php?ou="+m_ou);} 
			
		}
		return false;
		
	}
	
		if(document.getElementById('organization-find')){SearchOrgs();}
	
	
		if(document.getElementById('main_group_config')){
			RefreshTab('main_group_config');
			document.getElementById('adduser_ajax_newfrm').innerHTML='';
			YahooUserHide();
			document.getElementById('YahooUser').innerHTML='';
			return;
		}
		
	
	
		if(document.getElementById('org_user_list')){
			if(document.getElementById('searchstring')){
				var search=document.getElementById('searchstring').value;
				var ou=document.getElementById('org_user_list_ou').value;
				LoadAjax('org_user_list','domains.manage.org.index.php?finduser='+search+'&ou='+ou);
			}
		}
	
	
		if(document.getElementById('groups-section-from-members')){
			groupid=document.getElementById('groups-section-from-members').value;
			YahooUserHide();
			LoadMembers(groupid);
			return;
			
		}
	
	
		
		if(document.getElementById('member_add_to_wait')){
			LoadMembers(groupid);
			return;
		}		
		
		
		if(document.getElementById('bglego')){
			document.getElementById('bglego').src='img/bg_lego.jpg';
			document.getElementById('new_userid').value='';
			document.getElementById('password').value='';
			document.getElementById('group_id').value='';
			document.getElementById('email').value='';
			document.getElementById('user_domain').value='';
			document.getElementById('prefix_email').innerHTML='';
			}
		
		if(document.getElementById('adduser_ajax_newfrm')){
			YahooUserHide();
			YahooUser(800,'domains.edit.user.php?userid='+results+'&ajaxmode=yes','windows: '+results);
			
			if(document.getElementById('members_area')){
				LoadMembers(groupid);
			}			
			
			return false;
		}
		
		if(CurrentPageName()=='domains.edit.user.php'){
			YahooUser(740,'domains.edit.user.php?userid='+results+'&ajaxmode=yes','windows: '+results);
		}
	
		if(CurrentPageName()=='domains.edit.group.php'){
			YahooUser(740,'domains.edit.user.php?userid='+results+'&ajaxmode=yes','windows: '+results);
			LoadMembers(groupid);	
		}
		
		if(document.getElementById('member_add_to_wait')){
			LoadMembers(groupid);	
		}
		
		if(document.getElementById('members_area')){
			LoadMembers(groupid);
		}
		
	
}

function UserAutoChange_eMail(){
	var userid=document.getElementById('new_userid').value;
	userid=userid.replace(/\s/g,'.');
	document.getElementById('new_userid').value=userid;
	document.getElementById('prefix_email').innerHTML=document.getElementById('new_userid').value;
	document.getElementById('email').value=userid;
	}
	
function ChangeAddUsereMail(){
	var prefix_email=prompt('email');
	if(prefix_email){
		document.getElementById('prefix_email').innerHTML=prefix_email;
		document.getElementById('email').value=prefix_email;
	}
	
}

function GroupAddUserPressKey(e){
	if(checkEnter(e)){UserADD();}	 
}

function UserADD(){
	var XHR = new XHRConnection();
	var ou=document.getElementById('ou').value;
	
	if(ou.length==0){
		if(document.getElementById('ou-mem-add-form-user')){
			ou=document.getElementById('ou-mem-add-form-user').value;
		}
	}
	
	if(ou.length==0){
		Alert('Unable to stat Organization name (ou field is empty)');
		return;
		
	}
	
	XHR.appendData('ou',ou);
	
	
	
	XHR.appendData('new_userid',document.getElementById('new_userid').value);
	XHR.appendData('password',document.getElementById('password').value);
	XHR.appendData('group_id',document.getElementById('group_id').value);
	XHR.appendData('email',document.getElementById('email').value);
	XHR.appendData('user_domain',document.getElementById('user_domain').value);
	
	if(document.getElementById('adduser_ajax_newfrm')){document.getElementById('adduser_ajax_newfrm').innerHTML='<center><img src="img/wait_verybig.gif"></center>';}
	if(document.getElementById('bglego')){document.getElementById('bglego').src='img/wait_verybig.gif';}
	if(document.getElementById('member_add_to_wait')){document.getElementById('member_add_to_wait').innerHTML='<center><img src="img/wait_verybig.gif"></center>';}
	
	
	
	XHR.sendAndLoad('domains.edit.user.php', 'POST',X_UserADD);	
	}
	

function LoadUserTab(userid,num){
	LoadAjax('userform','domains.edit.user.php?userid='+ userid + '&ajaxmode=yes&section='+num);	
}
function Cyrus_mailbox_apply_settings(userid){
	if(document.getElementById('mailbox_graph')){
		document.getElementById('mailbox_graph').innerHTML='';
	}
	ParseForm('FFUserMailBox','domains.edit.user.php',false);
	YahooWin2(350,'domains.manage.users.index.php?Cyrus_mailbox_apply_settings='+userid)
	LoadUserTab(userid,'mailbox');
	}


var x_AddMemberGroup= function (obj) {
	if(document.getElementById('main_group_config')){
		RefreshTab('main_group_config');
	}
	
	LoadAjax('USER_GROUP','domains.edit.user.php?USER_GROUP_LIST='+m_userid+'&userid='+m_userid+'&nodiv=yes');
	
	
}


function AddAliases(userid){
	m_userid=userid;
	var aliase=document.getElementById('aliases').value;
	var aliase_domain=document.getElementById('user_domain').value;
	aliase=aliase+'@'+aliase_domain;
	var XHR = new XHRConnection();
	XHR.appendData('AddAliases',userid);
	XHR.appendData('aliase',aliase);
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_AddAliases);
	}
	




	
	

function DeleteUserGroup(group_id,userid){
		var XHR = new XHRConnection();
		XHR.appendData('user',userid);
		m_userid=userid;
		XHR.appendData('DeleteUserGroup',group_id);
		XHR.sendAndLoad('domains.edit.user.php', 'GET',x_AddMemberGroup);
				
		}	
function AddMemberGroup(){
		var XHR = new XHRConnection();
		var group=document.getElementById("user_group_add_selected").value;
		if(group.length==0){
			alert('No group set !!');
			return;
		}
		m_userid=document.getElementById("userid").value;
		XHR.appendData('user',m_userid);
		XHR.appendData('AddMemberGroup',group);
		XHR.sendAndLoad('domains.edit.user.php', 'GET',x_AddMemberGroup);
			
		}
		

	

function LoadUserGroupSection(uid){
	LoadAjax('userdatas','domains.edit.user.php?load_user_section_group=' + uid);	
	}




var x_UserDelete= function (obj) {
	var results=obj.responseText;
	var page_name=CurrentPageName();
	if (results.length>0){
		alert(results);
	}else{
       alert('success');		
	   if(document.getElementById('userform')){document.getElementById('userform').innerHTML="<center style='margin:10px'><img src='img/delete-256.png'></center>";}
	   if(page_name=='domains.edit.group.php'){LoadMembers(document.getElementById('group_id').value);	}		   
	   
	   
	}
	
}
	
function UserDelete(uid){
	var delete_this_user=document.getElementById('delete_this_user').value;
	if(confirm(delete_this_user)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteThisUser',uid);
		XHR.sendAndLoad('domains.edit.user.php', 'GET',x_UserDelete);
		YAHOO.example.container.YahooUser.hide();
	}
}

function NmapScanComputer(uid){
	LoadAjax('nmap','domains.edit.user.php?NmapScanComputer=' + uid);
	}



function BindRefresh(){
	var zone=document.getElementById('zone_org').value;	
	var query=document.getElementById('patterfind').value;
	LoadAjax('bind9_hosts_list',Working_page + '?search-hosts='+query+'&zone='+zone);
	
}



var ComputerRefreshAlias= function (obj) {
	LoadUserTab(m_userid,'computer_aliases');		
}


function ComputerAddAlias(){
	var text=document.getElementById('ComputerAddAlias').value;
	var cp=prompt(text);
	m_userid=document.getElementById('user_id').value;
	if(cp){
		var XHR = new XHRConnection();
		XHR.appendData('ComputerAddAlias',cp);
		XHR.appendData('userid',document.getElementById('user_id').value);
		XHR.sendAndLoad('domains.edit.user.php', 'GET',ComputerRefreshAlias);	
	}
}

function DeletComputerAliases(cp){
	m_userid=document.getElementById('user_id').value;
	var XHR = new XHRConnection();
	XHR.appendData('DeletComputerAliases',cp);
	XHR.appendData('userid',document.getElementById('user_id').value);
	XHR.sendAndLoad('domains.edit.user.php', 'GET',ComputerRefreshAlias);		
	
}

var x_DeleteRealMailBox= function (obj) {
	DeleteElementByID(Winid);	
}

function DeleteRealMailBox(mbx,id){
	var text=document.getElementById('deletemailbox_infos').value;
	if(confirm(text)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteRealMailBox',mbx);
		Winid=id;
		XHR.sendAndLoad('cyrus.index.php', 'GET',x_DeleteRealMailBox);			
	}
	
}



var x_SenderCanonical= function (obj) {
	YahooWin3(450,'domains.edit.user.php?sendparams=yes&userid='+m_userid);
}
var x_Refresh_tab_file_share= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	LoadUserSectionAjax('file_share')
}


function ParseFormFileShare(){
	XHR=ParseForm('userLdapform',CurrentPageName(),false,false,true);
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_Refresh_tab_file_share);	
	
	
}

function DeleteSenderCanonical(uid){
	m_userid=uid;
	var SenderCanonical;
	var XHR = new XHRConnection();
	XHR.appendData('DeleteSenderCanonical',uid);
	SenderCanonical=document.getElementById('SaveSenderCanonical').value;
	document.getElementById('SaveSenderCanonical').value='';
	XHR.appendData('DeleteSenderCanonicalValue',SenderCanonical);
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_SenderCanonical);		
}


function fdm_addrule(uid){
	YahooWin2('400','fdm.index.php?uid='+uid+'&rulename=','');
	}
	
	
var x_Refresh_tab_fdm= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	LoadUserSectionAjax('fetchmail');
}	
	
function fdm_editrule(){
	XHR=ParseForm('FDMRULE','fdm.index.php',false,false,true);
	XHR.sendAndLoad('fdm.index.php', 'GET',x_Refresh_tab_fdm);
}
	
function fdm_ShowRule(uid,rulename){
	YahooWin2('500','fdm.index.php?uid='+uid+'&rulename='+rulename,'');
	}
	
function fdm_ShowScript(uid,rulename){
	YahooWin2('600','fdm.index.php?uid='+uid+'&ScriptRulename='+rulename,'');
	}
	
function fdm_DeleteScript(uid,rulename){
	var XHR = new XHRConnection();
	XHR.appendData('fdm_DeleteScript',uid);
	XHR.appendData('rulename',rulename);
	XHR.sendAndLoad('fdm.index.php', 'GET',x_Refresh_tab_fdm);	
	}
	
function fdm_events(uid){
	YahooWin2('600','fdm.index.php?events='+uid,'');
	}
function user_autofill(){
	if(document.getElementById("surname").value.length>0){return false;}
	var FirstName=document.getElementById("firstname").value;
	var LastName=document.getElementById("lastname").value;	
	if(FirstName.length>0 && LastName.length>0){
		document.getElementById("surname").value=FirstName + " " + 	LastName;
		FirstName=FirstName.toLowerCase();
		LastName=LastName.toLowerCase();
		var FirstNameP=FirstName.replace(/\s+/gi,"-");
		var LastNameP=LastName.replace(/\s+/gi,"-");
		if(document.getElementById("email").value.length==0){
			document.getElementById("email").value=FirstNameP + "." + LastNameP;}
			
		if(document.getElementById("userid").value.length==0){
			document.getElementById("userid").value=FirstNameP + "." + LastNameP;}
			
		}
}

function FindInGroup(gid){
	var FindInGroup_text=document.getElementById("FindInGroup_text").value;
	var pattern=prompt(FindInGroup_text);
	if(pattern){
	   LoadAjax('listofMembersOfThisGroup','domains.edit.group.php?FindInGroup='+gid+'&pattern='+pattern);
	}
}

var x_ChangeUniqueIdentifier= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	YahooWin(740,'domains.edit.user.php?userid='+m_userid+'&ajaxmode=yes','windows: '+m_userid);
	YahooWin3Hide();
	YahooUserHide();
	YahooSearchUserHide();
}

function ChangeUniqueIdentifier(uid){
	var newuid=document.getElementById("uid_to").value;
	if(newuid.length>0){
	var XHR = new XHRConnection();
	XHR.appendData('changeuidFrom',uid);
	XHR.appendData('changeuidTo',newuid);
	document.getElementById('chuiseriddiv').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
	m_userid=newuid;
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_ChangeUniqueIdentifier);
	
	}
	
}



function ComputerRefresh(){
	if(document.getElementById('uid').value=='newcomputer'){return false;}
	var computer=document.getElementById('uid').value;
	var DnsZone=document.getElementById('DnsZoneName').value;
	YahooUser(870,'domains.edit.user.php?userid='+computer+'$&ajaxmode=yes',computer);
	}




var x_ChangeUserPasswordSave= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	YahooWin5Hide();
}
	

function ChangeUserPassword(uid){
	YahooWin5('400','domains.edit.user.php?ChangeUserPassword=yes&uid='+uid,'');
	
}
function ChangeUserPasswordSave(){
	var UserPasswordID=document.getElementById("UserPasswordID").value;
	var password=document.getElementById("UserPassword").value;
	var XHR = new XHRConnection();
	XHR.appendData('ChangeUserPasswordSave',password);
	XHR.appendData('uid',UserPasswordID);
	document.getElementById('ChangeUserPasswordID').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('domains.edit.user.php', 'POST',x_ChangeUserPasswordSave);
	}

function UserChangeEmailAddrSave(){
	var UserID=document.getElementById("UserChangeEmailAddrUID").value;
	var email=document.getElementById("email").value;
	var domain=document.getElementById("UserChangeEmailDomain").value;
	var MailboxActive=document.getElementById("MailboxActive").value;
	
	var XHR = new XHRConnection();
	XHR.appendData('UserChangeEmailAddrSave',email+'@'+domain);
	XHR.appendData('MailboxActive',MailboxActive);
	XHR.appendData('uid',UserID);
	document.getElementById('ChangeUserPasswordID').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_ChangeUserPasswordSave);
	}

function UserSystemInfos(uid){
	YahooWin5('500','domains.edit.user.php?UserSystemInfos=yes&uid='+uid,'');
	
}

function UserEndOfLIfe(uid){
	YahooWin5('500','domains.edit.user.php?UserEndOfLIfe=yes&uid='+uid,'');
	
}

function UserEndOfLIfeSave(){
	var UserID=document.getElementById("USER_SYSTEM_INFOS_UID").value;
	var FinalDateToLive=document.getElementById("FinalDateToLive").value;
	var XHR = new XHRConnection();
	XHR.appendData('FinalDateToLive',FinalDateToLive);
	XHR.appendData('uid',UserID);
	document.getElementById('ChangeUserPasswordID').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_ChangeUserPasswordSave);
	}




var x_RebuildSambaFields= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
	if(document.getElementById('USER_SAMBA_FORM')){
		LoadAjax('USER_SAMBA_FORM',"domains.edit.user.php?USER_SAMBA_FORM=yes&userid="+m_userid);
	}
	
	
}

function RebuildSambaFields(uid){
	m_userid=uid;
	var XHR = new XHRConnection();
	XHR.appendData('RebuildSambaFields',uid);
	XHR.appendData('uid',uid);
	XHR.appendData('SambaAdminServerDefined',document.getElementById('SambaAdminServerDefined').value);
	if(document.getElementById('sambdirs')){
		document.getElementById('sambdirs').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
	}
	XHR.appendData('uid',uid);
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_RebuildSambaFields);
}






