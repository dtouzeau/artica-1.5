/**
 * @author touzeau
 */

var Working_page="user.quarantine.query.php";
var Winid;





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
		XHR.sendAndLoad('user.quarantine.php', 'GET',x_TreeFetchMailApplyConfig);
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
	XHR.sendAndLoad(Working_page, 'GET',x_TreeFetchMailApplyConfig);
	RemoveDocumentID(Winid);
	MyHref(Working_page);
}	
	
function QuarantineShowMailFile(file){
	Winid=LoadWindows(650,530,Working_page,'?QuarantineShowMailFile=' + file);
	
}

function ReleaseMail(file){
	var XHR = new XHRConnection();
	XHR.appendData('ReleaseMail',file);
	XHR.sendAndLoad(Working_page, 'GET',x_TreeFetchMailApplyConfig);
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


function ShowMessage(msgid){
	var page=CurrentPageName();
	YahooWin(750,page+'?msgid='+msgid+'&tab=ShowMail');
	
}

function quarantine_showpage(){
	var section=document.getElementById('section').value;
	var main=document.getElementById('section').value;
	var page=document.getElementById('page').value;
	var Search=document.getElementById('Search').value;
	LoadAjax('content_q',CurrentPageName()+'?main='+ main + '&page='+page+'&section='+section+'&search='+Search+'&filter='+main);
	
}

function quarantine_resend(msgid,rcpto){
	var page=CurrentPageName();	
	LoadAjax('smtp_results',CurrentPageName()+'?quarantine_resend='+ msgid + '&rcpto='+rcpto);
	}

function LoadFind(){
	var page=CurrentPageName();
	var Search=document.getElementById('Search').value;
	var text=document.getElementById('search_intro').value;
	Search=prompt(text,Search);
	if(Search){
		document.getElementById('Search').value=Search;
		quarantine_showpage();
	}
	
}

