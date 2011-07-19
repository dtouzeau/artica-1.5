/**
 * @author touzeau
 */

var memory_ou='';

function LoadDomainSettings(domain,ou){
		var XHR = new XHRConnection();
		LoadWindows(400);
		XHR.appendData('LoadDomainSettings',domain);
		XHR.appendData('LoadDomainSettingsOu',ou);
		XHR.setRefreshArea('windows');
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET');
		}

function AddNewInternetDomain(){
	var Mydomain;
	var ou=document.getElementById("ou").value;
	if (Mydomain=prompt(document.getElementById("explain_1").value,'')){
		var XHR = new XHRConnection();
		XHR.appendData('AddNewInternetDomain',ou);
		XHR.appendData('AddNewInternetDomainDomainName',Mydomain);
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET',x_TreeFetchMailApplyConfig);
		ReloadOrgTable(ou);	
		}
	}
	
function AddNewInternetDomainMTA(){
	var RelayType=document.getElementById("RelayType").value;
	var ou=document.getElementById("ou").value;
	win = new Window({className: "artica", width:300, height:300, zIndex: 1000, resizable: true, draggable:true, wiredDrag: false,closable:true}); 
		var pars = 'AddNewInternetDomainMTA='+RelayType + '&ou=' + ou;
		win.setAjaxContent('domains.manage.org.index.php', {method: 'get', parameters: pars});	
		win.setStatusBar(""); 
		win.setDestroyOnClose();
 		win.showCenter();
		win.toFront();
		SetTimeOut=0;
		//win.getId())
		}	
	
	
var x_Tree_Internet_domain_add_transport= function (obj) {
	var tempvalue=obj.responseText;
	alert(tempvalue);
	}		
		
function AddTransportToDomain(MyDomain,ou,suffix){
	if (MyServer=prompt(document.getElementById("relay explain 3").value,'')){
		var XHR = new XHRConnection();
		XHR.appendData('AddTransportToDomain',MyDomain);
		XHR.appendData('transport_ip',MyServer);
		XHR.appendData('ou',ou);
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET',x_TreeFetchMailApplyConfig);
		LoadDomainSettings(MyDomain,ou);
		ReloadOrgTable(ou);		
		}
}

var x_DeleteInternetDomain= function (obj) {
	var tempvalue=obj.responseText;
	alert('x_DeleteInternetDomain'+tempvalue);
	YahooWinHide();	
	
	if (document.getElementById('LocalDomainsList')){
		LoadAjax('LocalDomainsList','domains.edit.domains.php?LocalDomainList=yes&ou=' + memory_ou);
		return
	}
	
	ReloadOrgTable(ou);
}



function DeleteInternetDomain(num,ou){
		memory_ou=ou;
		var mytext='Are you sure ? ' + num;
		if(document.getElementById("inputbox delete")){
			mytext=document.getElementById("inputbox delete").value + ' '+ num;
		}
		if(confirm(mytext,'')){
		var XHR = new XHRConnection();
		YahooWinHide();
		XHR.appendData('DeleteInternetDomain',num);
		XHR.appendData('ou',ou);
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET',x_DeleteInternetDomain);	
		}
}	
function SaveTransportDomain(MyDomain,ou){
	var XHR = new XHRConnection();
	XHR.appendData('SaveTransportDomain',MyDomain);
	XHR.appendData('transport_ip',document.getElementById("transport_ip").value);
	XHR.appendData('transport_type',document.getElementById("transport_type").value);
	XHR.appendData('transport_port',document.getElementById("transport_port").value);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('domains.manage.org.index.php', 'GET');
	ReloadOrgTable(ou);			
	}
	
function AddNewGroupInOrg(){
	var MyGroupName;
	var input_text=document.getElementById('inputbox add group').value;
	var ou=document.getElementById('ou').value;
	if (MyGroupName=prompt(input_text)){
		var XHR = new XHRConnection();
		XHR.appendData('Tree_group_Add_New',MyGroupName);
		XHR.appendData('ou',ou);
		XHR.sendAndLoad('domains.manage.org.index.php', 'GET');
		ReloadGroup(ou);		
		}
}		