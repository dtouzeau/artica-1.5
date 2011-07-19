/**
 * @author touzeau
 */

var Working_page="user.quarantine.query.php";
var Winid;


function BTAddBlackDomain(e){
	if(checkEnter(e)){
		AddBlackDomain();
		}
		}
		
function AddBlackDomain(){
	var ou=document.getElementById('ou').value;
	var domainz=document.getElementById('add_domain').value;
	var XHR = new XHRConnection();
	XHR.appendData('add_domain',domainz);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('global-blacklist.ou.php', 'GET',x_parseform);	
}


function LoadQuarantineTemplate(template_name){
	var button_text=document.getElementById('close_form').value;
	var ou=document.getElementById('ou').value;
	document.getElementById('template_forms').src='quarantine.ou.php?ou=' + ou + '&template=' + template_name;
	document.getElementById('ButtonCloseIframe').innerHTML='<input type="button" OnClick="javascript:CloseTemplate()" value="' + button_text + '&nbsp;&raquo;&raquo;">';
	document.getElementById('ButtonCloseIframe').style.padding="10px";
	LoadIframe('template_forms')
	document.getElementById('template_forms').height='350px';
	document.getElementById('template_forms').width='100%';
	
}

function CloseTemplate(){
	document.getElementById('template_forms').height='0px';
	document.getElementById('template_forms').width='0px';
	document.getElementById('ButtonCloseIframe').innerHTML='';
	document.getElementById('ButtonCloseIframe').style.padding='0px';
	
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
