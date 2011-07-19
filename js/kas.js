/**
 * @author touzeau
 */

var Working_page="kas.engine.settings.php";
var Winid;


function EditKasDnsBlackListAdd(){
	var XHR = new XHRConnection();
	XHR.appendData('DNS_HOSTNAME',document.getElementById('DNS_HOSTNAME').value);
	XHR.appendData('DNS_RATE',document.getElementById('DNS_RATE').value);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	if(document.getElementById('global_kas_pages')){
		LoadAjax('global_kas_pages','kas.engine.settings.php?page=2&nodiv=yes&ajax-pop=yes&hostname=')
	}else{
	MyHref(Working_page+'?page=2');
	}
	
}
//KasDnsBlackListDelete
function KasDnsBlackListDelete(server_name){
	var XHR = new XHRConnection();
	XHR.appendData('DnsBlackListDelete',server_name);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	if(document.getElementById('global_kas_pages')){
		LoadAjax('global_kas_pages','kas.engine.settings.php?page=2&nodiv=yes&ajax-pop=yes&hostname=')
	}else{
	MyHref(Working_page+'?page=2');
	}		
}

function EditKasDnsBlackList(server_name){
	var XHR = new XHRConnection();
	XHR.appendData('DnsBlackListEdit',server_name);
	XHR.appendData('DNS_RATE',document.getElementById(server_name).value);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	if(document.getElementById('global_kas_pages')){
		LoadAjax('global_kas_pages','kas.engine.settings.php?page=2&nodiv=yes&ajax-pop=yes&hostname=')
	}else{
	MyHref(Working_page+'?page=2');
	}		
}
function KasforceUpdates(){
	var XHR = new XHRConnection();
	XHR.appendData('KasforceUpdates','yes');
	XHR.sendAndLoad('kas.keepupd2date.settings.php', 'GET',x_parseform);
	setTimeout('MyHref("kas.keepupd2date.settings.php")',5200);
	}
	
function KasUpdates(){
	var XHR = new XHRConnection();
	XHR.appendData('KasUpdates','yes');
	XHR.sendAndLoad('kas.keepupd2date.settings.php', 'GET',x_parseform);
	setTimeout('MyHref("kas.keepupd2date.settings.php")',5200);
	}	

function KavUpdates(){
	var XHR = new XHRConnection();
	XHR.appendData('KavUpdates','yes');
	XHR.sendAndLoad('kav.keepupd2date.settings.php', 'GET',x_parseform);
	setTimeout('MyHref("kav.keepupd2date.settings.php")',5200);
	}	