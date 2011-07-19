/**
 * @author touzeau
 */

var Working_page="domains.edit.domains.php";
var Winid;
var memory_ou='';




	



function EditInfosLocalDomain(domain,ou){
	YahooWin(650,"domains.edit.domains.php"+'?EditInfosLocalDomain='+domain+'&ou='+ou,domain);
}


	
	
function GroupPrivileges(gid){
	Winid=LoadWindows(650,530,"domains.edit.domains.php",'?GroupPriv=' + gid);
	
}

function EditGroupPriv(gid,ou,suffix){
	ParseForm('priv',"domains.edit.domains.php",true);
				
}
function DeleteMember(memberid,groupid){
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ memberid)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteMember',memberid);
		XHR.sendAndLoad("domains.edit.domains.php", 'GET',x_TreeFetchMailApplyConfig);
		MyHref("domains.edit.domains.php"+ '?ou='+document.getElementById('ou').value)
		}
}


var x_DeleteInternetDomain= function (obj) {
	var tempvalue=obj.responseText;
	alert(tempvalue);
	
	if (document.getElementById('LocalDomainsList')){
		LoadAjax('LocalDomainsList','domains.edit.domains.php?LocalDomainList=yes&ou=' + memory_ou);
		return
	}
}


function DeleteInternetDomain(domains){
	var ou=document.getElementById('ou').value;
	memory_ou=ou;
	text_del=document.getElementById('inputbox delete').value;
	if(confirm(text_del + ':'+ domains)){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteInternetDomain',domains);
	    	XHR.appendData('ou',ou);		
		XHR.sendAndLoad("domains.edit.domains.php", 'GET',x_DeleteInternetDomain);
		}
}

function AddRemoteDomain_form(ou,index){
	Loadjs('domains.edit.domains.php?remote-domain-add-js=yes&ou='+ou+'&index='+index)
}


