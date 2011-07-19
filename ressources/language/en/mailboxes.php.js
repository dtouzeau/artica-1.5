/**
 * @author touzeau
 */

var tempvalue='';

	function AddDomain(){
			var domain=prompt('type your domain:\n eg:domain.tlb','');
			if(domain){
				var XHR = new XHRConnection();
				XHR.appendData('AddDomain',domain);
				XHR.setRefreshArea('domain_list');
				XHR.sendAndLoad(CurrentPageName(), 'GET');
				alert('Your domain [' + domain + '] was added');
				}
			}
			
	function CurrentPageName(){
		var sPath = window.location.pathname;
		var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
		return sPage;		
		}
		
	function adduser(xdomain,user){
		document.getElementById("windows").style.width='450px';
		document.getElementById("windows").style.height='auto';
    	document.getElementById("windows").style.top='100px';
    	document.getElementById("windows").style.left='500px';
		document.getElementById("windows").style.zIndex='3000';
    	document.getElementById("windows").style.visibility="visible";
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('add_maiboxes',xdomain);
		XHR.appendData('add_maiboxes_user',user);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		expand_domain(xdomain);
	}
	
	function MailBoxStorage(mailto,xdomain){
		document.getElementById("windows").style.width='450px';
		document.getElementById("windows").style.height='auto';
    	document.getElementById("windows").style.top='100px';
    	document.getElementById("windows").style.left='500px';
		document.getElementById("windows").style.zIndex='3000';
    	document.getElementById("windows").style.visibility="visible";
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('MailBoxStorage',mailto);
		XHR.appendData('edit_mailbox_storage_domain',xdomain);
		XHR.sendAndLoad(CurrentPageName(), 'GET');			
		
	}
	
	function add_storage_mailbox(email){
		var password;
		var text=document.getElementById('add_pop3_imap_text').value;
		password=prompt(text,'');
		if (password){
			var XHR = new XHRConnection();
			XHR.appendData('add_storage_mailbox',email);
			XHR.appendData('add_storage_mailbox_password',password);
			XHR.setRefreshArea('windows');
			XHR.sendAndLoad(CurrentPageName(), 'GET');	
			alert('POP3/IMAP OK');
			}
		}
		
	function edit_storage_mailbox(email){
		var password=document.getElementById('password').value
		var text=document.getElementById('edit_pop3_imap_text').value;
		if(password==email){password=prompt(text,'');}
		
		if (password){
			var XHR = new XHRConnection();
			XHR.appendData('add_storage_mailbox',email);
			XHR.appendData('add_storage_mailbox_password',password);
			XHR.setRefreshArea('windows');
			XHR.sendAndLoad(CurrentPageName(), 'GET');	
			alert('POP3/IMAP OK');
			}
		
		}		
	
	
	function Edituser(){
		var XHR=BuildXHRForms();
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		alert('document saved');
		var xdomain=document.getElementById('domain_source').value;
		expand_domain(xdomain);
	}
	
	function expand_domain(xdomain){
		var XHR = new XHRConnection();
		document.getElementById('expand_' + xdomain).innerHTML='';
		XHR.setRefreshArea('expand_' + xdomain);
		XHR.appendData('expand_domain',xdomain);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		}
		
var xedit_transport= function (obj) {
	tempvalue=obj.responseText;
	}		
		
	function edit_transport(xdomain){
		var XHR = new XHRConnection();
		XHR.appendData('edit_transport',xdomain);
		XHR.sendAndLoad(CurrentPageName(), 'GET',xedit_transport);
		var newtransport=prompt('Edit the transport target',tempvalue);
		if(newtransport){
		var XHR2 = new XHRConnection();
			XHR2.appendData('save_transport',xdomain);
			XHR2.appendData('save_transport_target',newtransport);
			XHR2.sendAndLoad(CurrentPageName(), 'GET');
			alert('document saved');
			expand_domain(xdomain);
		}
	}
	
var xdelete_transport_text= function (obj) {
	tempvalue=obj.responseText;
	}	
	
	function delete_transport(xdomain){
		var XHR = new XHRConnection();
		XHR.appendData('xdelete_transport_text',xdomain);
		XHR.sendAndLoad(CurrentPageName(), 'GET',xdelete_transport_text);
		if(confirm(tempvalue)){
			var XHR2 = new XHRConnection();
			XHR2.appendData('delete_transport_confirm',xdomain);
			XHR2.sendAndLoad(CurrentPageName(), 'GET');
			alert('document deleted');
			expand_domain(xdomain);
			}
		}
		
	function delete_alias(mail_from,xdomain){
		var XHR = new XHRConnection();
		XHR.appendData('xdelete_alias_text',mail_from);
		XHR.sendAndLoad(CurrentPageName(), 'GET',xdelete_transport_text);
		if(confirm(tempvalue)){
			var XHR2 = new XHRConnection();
			XHR2.appendData('delete_alias_confirm',mail_from);
			XHR2.sendAndLoad(CurrentPageName(), 'GET');
			alert('document deleted');
			expand_domain(xdomain);
			}
		}		
		
	function DelDomain(xdomain){
		var XHR = new XHRConnection();
		XHR.appendData('xdelete_domain_text',xdomain);	
		XHR.sendAndLoad(CurrentPageName(), 'GET',xdelete_transport_text);
		if(confirm(tempvalue)){
			var XHR2 = new XHRConnection();
			XHR2.appendData('delete_domain_confirm',xdomain);
			XHR2.setRefreshArea('domain_list');
			XHR2.sendAndLoad(CurrentPageName(), 'GET');
			alert('document deleted');
			}
		
	}
		
function DisableFormUser(){
	var username_from=document.getElementById("username_from").value;
	var username_to=document.getElementById("username_to").value;
	
	var transport=document.getElementById("remote_server").value;
	if (transport.length>0){
		hideForms('username_from,username_to,allusers');
		return true;
	}
	else{
		FreeForms('username_from,username_to,allusers');
		}
	
	if(username_from.length>0){
		hideForms('remote_server');
		return true;
	}
	
	if(username_to.length>0){
		hideForms('remote_server');
		return true;
	}	
	
	FreeForms('remote_server');
	
}		

function mailbox_EnableAll(){
	if(document.getElementById("allusers").checked==true){
		hideForms('username_from,remote_server');
		document.getElementById("username_from").value='*';
		document.getElementById("username_to").value='*@' + document.getElementById("domain_source").value;
		
	}else{
		if(document.getElementById("username_from").value=='*'){
			document.getElementById("username_from").value='';
		}
		FreeForms('username_from,remote_server');}

	
	
}
		
		
	