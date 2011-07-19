/**
 * @author touzeau
 */

var Working_page="system.nic.staticdns.php";
var Winid;

function NicNameServerMove(index,move){
	var XHR = new XHRConnection();
	XHR.appendData('NicNameServerMove',index);
	XHR.appendData('move',move);		
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);	
	ReloadNameServers();	
	}
	
	
function NicNameSearchMove(index,move){
var XHR = new XHRConnection();
	XHR.appendData('NicNameSearchMove',index);
	XHR.appendData('move',move);		
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);	
	ReloadSearchServers();		
	
}
function ReloadNameServers(){
	var XHR = new XHRConnection();
	document.getElementById('nametable').innerHTML='';
	XHR.appendData('ReloadNameServers','yes');	
	XHR.setRefreshArea('nametable');
	XHR.sendAndLoad(Working_page, 'GET');	
	}	
function ReloadSearchServers(){
	var XHR = new XHRConnection();
	document.getElementById('searchTable').innerHTML='';
	XHR.appendData('ReloadSearchServers','yes');	
	XHR.setRefreshArea('searchTable');
	XHR.sendAndLoad(Working_page, 'GET');	
	}	
	
function NicNameServerDelete(num) {
	var XHR = new XHRConnection();
	XHR.appendData('NicNameServerDelete',num);	
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	ReloadNameServers();	
}
function NicNameSearchDelete(num) {
	var XHR = new XHRConnection();
	XHR.appendData('NicNameSearchDelete',num);	
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	ReloadSearchServers();	
}
	
function NicAddSearchDomain(){
	var text=document.getElementById('add_search_domain_text').value;
	var myvalue=prompt(text);
	if(myvalue){
		var XHR = new XHRConnection();
		XHR.appendData('NicAddSearchDomain',myvalue);	
		XHR.sendAndLoad(Working_page, 'GET');
		ReloadSearchServers();	
	}
	
}
function NicAddNameServer(){
	var text=document.getElementById('add_nameserver_text').value;
	var myvalue=prompt(text);
	if(myvalue){
		var XHR = new XHRConnection();
		XHR.appendData('NicAddNameServer',myvalue);	
		XHR.sendAndLoad(Working_page, 'GET');
		ReloadNameServers();	
	}	
	
}

