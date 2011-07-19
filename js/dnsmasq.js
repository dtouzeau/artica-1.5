/**
 * @author touzeau
 */

var Working_page="dnsmasq.dns.settings.php";
var Winid;

function PostfixAddFallBackServer(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	Winid=LoadWindows(430,330,Working_page,'PostfixAddFallBackServer=yes&domainName='+Routingdomain)
	}

function XHRPostfixAddFallBackerserverSave(){
	ParseForm('FFM3',Working_page,true);	
	PostfixAddFallBackerserverLoad();
	RemoveDocumentID(Winid);
}
function InterfacesReload(){
	var XHR = new XHRConnection();	
	XHR.appendData('InterfacesReload','yes');	
	XHR.setRefreshArea('dnmasq_interface');
	XHR.sendAndLoad(Working_page, 'GET');	
}


function addressesReload(){
	var XHR = new XHRConnection();	
	XHR.appendData('addressesReload','yes');	
	XHR.setRefreshArea('array_addresses');
	XHR.sendAndLoad(Working_page, 'GET');	
	}
function ListentAddressesReload(){
	var XHR = new XHRConnection();	
	XHR.appendData('ListentAddressesReload','yes');	
	XHR.setRefreshArea('dnsmasq_listen_address');
	XHR.sendAndLoad(Working_page, 'GET');	
	}
function mxHostsReload(){
	var XHR = new XHRConnection();	
	XHR.appendData('mxHostsReload','yes');	
	XHR.setRefreshArea('mx_hosts');
	XHR.sendAndLoad('dnsmasq.records.settings.php', 'GET');	
	}	
	
function DnsmasqDeleteMxHost(num){
	var XHR = new XHRConnection();	
	XHR.appendData('DnsmasqDeleteMxHost',num);	
	XHR.sendAndLoad('dnsmasq.records.settings.php', 'GET');
	mxHostsReload();	
}
function DnsMasqMxMove(num,move){
	var XHR = new XHRConnection();	
	XHR.appendData('DnsMasqMxMove',num);
	XHR.appendData('move',move);	
	XHR.sendAndLoad('dnsmasq.records.settings.php', 'GET');	
	mxHostsReload();	
	
}
	
	
function DnsmasqDeleteListenAddress(num){
	var XHR = new XHRConnection();	
	XHR.appendData('DnsmasqDeleteListenAddress',num);	
	XHR.sendAndLoad(Working_page, 'GET');	
	ListentAddressesReload();	
	
}	

	
	
function DnsmasqDeleteInterface(num){
	var XHR = new XHRConnection();	
	XHR.appendData('DnsmasqDeleteInterface',num);	
	XHR.sendAndLoad(Working_page, 'GET');	
	InterfacesReload();	
	
}




function PostfixAddFallBackServerMove(num,move){
var XHR = new XHRConnection();	
	XHR.appendData('PostfixAddFallBackServerMove',num);
	XHR.appendData('move',num);		
	XHR.sendAndLoad(Working_page, 'GET');
	PostfixAddFallBackerserverLoad();			
	
}


