/**
 * @author touzeau
 */
var memory_groupid;
function LoadUsersDatas(userid){
		LoadWindows(700);
		var XHR = new XHRConnection();
		XHR.appendData('LoadUsersDatas',userid);
		XHR.setRefreshArea('windows');
		XHR.sendAndLoad('domains.manage.users.index.php', 'GET');	
		}
function LoadUsersTab(userid,num){
var XHR = new XHRConnection();
		XHR.appendData('userid',userid);
		XHR.appendData('LoadUsersTab',num);
		XHR.setRefreshArea('windows');
		XHR.sendAndLoad('domains.manage.users.index.php', 'GET');	
	
}	
function DeleteUserGroup(group_id,userid){
		var XHR = new XHRConnection();
		XHR.appendData('user',userid);
		XHR.appendData('DeleteUserGroup',group_id);
		XHR.setRefreshArea('windows');
		XHR.sendAndLoad('domains.manage.users.index.php', 'GET');		
		}	
function AddMemberGroup(){
		var XHR = new XHRConnection();
		XHR.appendData('user',document.getElementById("userid").value);
		XHR.appendData('AddMemberGroup',document.getElementById("group_add").value);
		XHR.setRefreshArea('windows');
		XHR.sendAndLoad('domains.manage.users.index.php', 'GET');
		Reload_area_Member_not_affected();
			
		}
		
function DeleteOU(ou){Loadjs('domains.delete.org.php?ou='+ou);}			
		
var x_AddnewMember= function (obj) {
	var response=obj.responseText;
	var reg=response.match(/error/gi);
	if(reg){
		alert(response);
		return false;
		}
	
	
	LoadUsersDatas(response);}
		
		
function Reload_area_Member_not_affected(){
	if(document.getElementById('members_not_affected')){if(document.getElementById('members_not_affected').innerHTML.length>0){LoadMembersNotAffected();}}
}			
		
function AddnewMember(){
	var MyUserName;
	var input_text=document.getElementById('inputbox add user').value;
	var ou=document.getElementById('ou').value;
	MyUserName=prompt(input_text);
	if (MyUserName){
		var XHR = new XHRConnection();
		XHR.appendData('AddnewMember',MyUserName);
		XHR.appendData('ou',ou);
		XHR.sendAndLoad('domains.manage.users.index.php', 'GET',x_AddnewMember);		
		}	
	}
function AddMemberIntoGroup(gid){
	var MyUserName;
	var input_text=document.getElementById('inputbox add user').value;
	var ou=document.getElementById('ou').value;
	if (MyUserName=prompt(input_text)){
		var XHR = new XHRConnection();
		XHR.appendData('AddnewMember',MyUserName);
		XHR.appendData('ou',ou);
		XHR.appendData('group_member_id',gid);
		XHR.sendAndLoad('domains.manage.users.index.php', 'GET',x_AddnewMember);
		if(document.getElementById('members_'+ gid)){
			LoadMembers(gid);
			}		
		}	
}
function LoadMembersNotAffected(){
		var ou=document.getElementById('ou').value;
		var XHR = new XHRConnection();
		XHR.appendData('LoadMembersNotAffected',ou);
		XHR.setRefreshArea('members_not_affected');
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET');
}


function ReloadOrgTable(ou){ReloadGroup(ou)}

function LoadMembers(group_name){
		var XHR = new XHRConnection();
		XHR.appendData('LoadMembers',group_name);
		XHR.setRefreshArea('members_'+group_name);									
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET');	
}


var xReloadGroup= function (obj){
	var response=obj.responseText;
	var reg=new RegExp("\n", "g");
	var reg2=new RegExp(";", "g");
	var tableau=response.split(reg);
	var values;
	for (var i=0; i<tableau.length; i++){
		if(tableau[i].length>0){
			values=tableau[i].split(reg2);	
			document.getElementById('tablelist').innerHTML=document.getElementById('tablelist').innerHTML + "<div id='div_"+values[0]+"'></div>"
			setTimeout('FillGroupTable('+values[0]+')',100);
		}
		
	}
	
	
	}


function LoadDomainList(ou){
		var XHR = new XHRConnection();
		XHR.appendData('LoadDomainList',ou);
		XHR.setRefreshArea('domainlist');									
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET');		
		}

function ReloadGroup(ou){
document.getElementById('tablelist').innerHTML=''
var XHR = new XHRConnection();
XHR.appendData('LoadAjaxGroup',ou);
document.getElementById('wait').innerHTML='<img src=img/frw8at_ajaxldr_7.gif>';
XHR.sendAndLoad('domains.manage.org.index.php', 'GET',xReloadGroup);
LoadDomainList(ou);
setTimeout('LoadMembersNotAffected()',100);
document.getElementById('wait').innerHTML='';
	
}

function FillGroupTable(groupid){
	var XHR = new XHRConnection();
	XHR.appendData('FillGroupTable',groupid);	
	XHR.setRefreshArea('div_'+groupid);	
	XHR.sendAndLoad('domains.manage.org.index.php', 'GET');
	}



