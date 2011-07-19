/**
 * @author touzeau
 */
var company_name;
var operations;
var znumber;
var win;

var x_EditWizar1= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)}else{MyHref('domains.edit.domains.php?ou='+company_name);}
	}

function EditWizar1(){
	var nic_hook=document.getElementById('nic_hook').value;
	company_name=document.getElementById('company_name').value;
	var XHR = new XHRConnection();
			XHR.appendData('nic_hook',nic_hook);
			XHR.appendData('company_name',company_name);
			XHR.appendData('finish','true');
			XHR.sendAndLoad('artica.wizard.org.php', 'GET',x_EditWizar1);	
	}
 	
	
 
 
 