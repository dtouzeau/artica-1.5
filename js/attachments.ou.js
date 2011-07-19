/**
 * @author touzeau
 */

var Working_page="global-ext-filters.ou.php";
var Winid;


function BTAddBlackDomain(e){
	if(checkEnter(e)){
		AddBlackDomain();
		}
		}
		
function add_attach_group(){
	var ou=document.getElementById('ou').value;
	var group=prompt(document.getElementById('add_attachment_group_text').value);
	var XHR = new XHRConnection();
	XHR.appendData('add_attach_group',group);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('global-ext-filters.ou.php', 'GET',x_parseform);
	LoadGroup();	
}


function group_attachment_delete(groupname){
	var ou=document.getElementById('ou').value;
	var XHR = new XHRConnection();
	XHR.appendData('delete_attach_group',groupname);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('global-ext-filters.ou.php', 'GET',x_parseform);
	LoadGroup();		
	
	
}

function LoadGroup(){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_list',Working_page +'?LoadGroups=' + ou);
	
}

function group_attachment_edit(groupname){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_list',Working_page +'?group_attachment_edit=' + groupname + '&ou='+ ou);
}	

function addNewSupectExt(groupname){
	var ou=document.getElementById('ou').value;
	var add_attachment_text=document.getElementById('add_attachment_text').value;
	var list=prompt(add_attachment_text);
	LoadAjax('group_list',Working_page +'?addNewSupectExt=' + list + '&ou='+ ou + '&group='+groupname);
}

function EditExtension(num,group){
	var ou=document.getElementById('ou').value;
	var extaction=document.getElementById('ext_action_'+ num).value;
	var ext_name=document.getElementById('ext_name_'+ num).value;
	var XHR = new XHRConnection();
	XHR.appendData('ou',ou);
	XHR.appendData('group',group);
	XHR.appendData('extaction',extaction);
	XHR.appendData('extname',ext_name);
	XHR.appendData('index',num);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);			
	}
	
	
function addDefaultSupectExt(groupname){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_list',Working_page +'?addDefaultSupectExt=' + groupname + '&ou='+ ou);
}

function DeleteExtension(num,groupname){
	var ou=document.getElementById('ou').value;
	LoadAjax('group_list',Working_page +'?DeleteExtension=' + groupname + '&ou='+ ou + '&extension='+num);
}

function SwitchExtRule(num,ou,group){
LoadAjax('group_list',Working_page +'?SwitchExtRule=' + num + '&ou='+ ou + '&group='+group);
}	

function DelDefaultSupectExt(groupname){
var ou=document.getElementById('ou').value;
	LoadAjax('group_list',Working_page +'?DelDefaultSupectExt=' + groupname + '&ou='+ ou);	
}


function DeleteGlobalBlack(num){
	var XHR = new XHRConnection();
	var ou=document.getElementById('ou').value;
	var SearchString=document.getElementById('SearchString').value;
	XHR.appendData('DeleteGlobalBlack',num);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('global-blacklist.ou.php', 'GET',x_parseform);
	
	alert(SearchString);
	if(SearchString.length==0){
		var zpage=document.getElementById('page_requested').value;
		var grouppage=document.getElementById('grouppage').value;
		MyHref('global-blacklist.ou.php?ou=' + ou + '&page='+ zpage + '&grouppage='+ grouppage);
	}else{
		MyHref('global-blacklist.ou.php?ou=' + ou + '&find=' +SearchString);
	}
	
	
	
}

function ExtraInfos(){
	var textz=document.getElementById('div_extra').innerHTML;
	if(textz.length>0){
		document.getElementById('div_extra').innerHTML='';
		document.getElementById('div_extra').style.visibility='hidden';
		
	}else{
	document.getElementById('div_extra').innerHTML=document.getElementById('extra_infos').value;
	document.getElementById('div_extra').style.visibility='visible';
	document.getElementById('div_extra').style.left =xMousePos-500 + "px";
	document.getElementById('div_extra').style.top =yMousePos-500 + "px";
	}	
	}
	
function AddWhiteAndRelease(file){
	var XHR = new XHRConnection();
	XHR.appendData('AddWhiteAndRelease',document.getElementById('spammed_mail_from').value);
	XHR.sendAndLoad(Working_page, 'GET',x_TreeFetchMailApplyConfig);
	ReleaseMail(file);
	
}

function UserEmptyQuarantine(){
	var msg=document.getElementById('empty_quarantine_text_mesgbox').value;
	if(confirm(msg)){
		var XHR = new XHRConnection();
		XHR.appendData('UserEmptyQuarantine','yes');
		XHR.sendAndLoad('user.quarantine.php', 'GET',x_parseform);
		MyHref('user.quarantine.php');
	}
	
}

function QuarantineMessageDelete(vount){
	var XHR = new XHRConnection();
	XHR.appendData('QuarantineMessageDelete',vount);
	XHR.sendAndLoad(Working_page, 'GET');
	document.getElementById('line'+ vount).style.backgroundColor='#005447';
}

function DeleteMailsFrom(){
	var XHR = new XHRConnection();
	XHR.appendData('DeleteMailsFrom',document.getElementById('spammed_mail_from').value);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	RemoveDocumentID(Winid);
	MyHref(Working_page);
}	
	
function QuarantineShowMailFile(file){
	Winid=LoadWindows(650,530,Working_page,'?QuarantineShowMailFile=' + file);
	
}

function ReleaseMail(file){
	var XHR = new XHRConnection();
	XHR.appendData('ReleaseMail',file);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	RemoveDocumentID(Winid);
}

function EditGroupPriv(gid,ou,suffix){
	ParseForm('priv',Working_page,true);
				
}
function DeleteMember(memberid,groupid){
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ memberid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteMember',memberid);
		XHR.sendAndLoad(Working_page, 'GET',x_parseform);
		MyHref(Working_page+ '?ou='+document.getElementById('ou').value)
		}
}
function DeleteGroup(gpid){
	var ou=document.getElementById('ou').value;
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ gpid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteGroup',gpid);
		XHR.sendAndLoad(Working_page, 'GET',x_parseform);
		MyHref(Working_page+ '?ou='+document.getElementById('ou').value)
		}
}

function DeleteGroupFromExtensionsRule(gpid,ou,AttachRulename){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteGroupFromExtensionsRule',gpid);
		XHR.appendData('ExtensionsRule',AttachRulename);
		XHR.appendData('ou',ou);
		XHR.sendAndLoad(Working_page, 'GET');
		SwitchExtRule('2',ou,AttachRulename);
	}
