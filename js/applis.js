/**
 * @author touzeau
 */

var Working_page="system.applications.php";
var Winid;


function Setp1(){
		var	appli=document.getElementById('prog').value;
		LoadAjax('step1',Working_page + '?setp1='+appli)
	
	}



function PerformAutoInstall(APP_NAME){
	LoadAjax('step2',Working_page + '?PerformAutoInstall='+APP_NAME);
	LoadAjax('apps',Working_page +'?apps=yes');
	}
	
function PerformAutoRemove(APP_NAME){
	LoadAjax('step2',Working_page + '?PerformAutoRemove='+APP_NAME);
	LoadAjax('apps',Working_page +'?apps=yes');
	
}

function EditGroupPriv(gid,ou,suffix){
	ParseForm('priv',Working_page,true);
	document.getElementById('groupprivileges').innerHTML='';
}

function LoadMembers(groupid){
	LoadAjax('MembersList',Working_page + '?MembersList='+groupid)
	}
	
function DeleteMembersGroup(groupid){
	LoadAjax('MembersList',Working_page + '?DeleteMembersForGroup='+groupid)
}	
	
function ImportMembers(groupid){
	LoadAjax('MembersList',Working_page + '?ImportMembers='+groupid)
	
}

function ForbiddenAttach(groupid){
	LoadAjax('MembersList',Working_page + '?ForbiddenAttach='+groupid)
	
}

function ImportGroupFile(){
	var import_file=document.getElementById('fullpath').value;
	var groupid=document.getElementById('groupid').value;
	LoadAjax('ActionImport',Working_page + '?ImportMembersFile='+import_file + '&groupid='+groupid);
}

function DeleteNotAffectedUsers(){
	if(confirm(document.getElementById('delete_members_confirm').value)){
		var ou=document.getElementById('ou').value;
		LoadAjax('grouplist',Working_page + '?DeleteNotAffectedUsers=yes&ou='+ ou);
	}
	
}


function DeleteMember(memberid,groupid){
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ memberid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteMember',memberid);
		XHR.sendAndLoad(Working_page, 'GET',x_TreeFetchMailApplyConfig);
		MyHref(Working_page+ '?ou='+document.getElementById('ou').value)
		}
}
function DeleteGroup(gpid){
	var ou=document.getElementById('ou').value;
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ gpid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteGroup',gpid);
		XHR.sendAndLoad(Working_page, 'GET',x_TreeFetchMailApplyConfig);
		MyHref(Working_page+ '?ou='+document.getElementById('ou').value)
		}
}
