/**
 * @author touzeau
 */

var Working_page="nmap.index.php";
var Winid;
var mem_gid;



var x_NmapLoadList=function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)}
	LoadAjax('nmap_list',Working_page + '?main=nmap-list&t='+mem_gid)		
}

function nmap_delete_ip(num,t){
	mem_gid=t;
	var XHR = new XHRConnection();
	//
	XHR.appendData('nmap_delete_ip',num);
	XHR.sendAndLoad(Working_page, 'GET',x_NmapLoadList);
	}
	
function nmap_add_network(){
	YahooWin(350,Working_page+'?main=nmap-add','windows');
}


function AddNmapNetwork(){
	var XHR = new XHRConnection();
	mem_gid=document.getElementById('tmp').value
	XHR.appendData('AddNmapNetwork',document.getElementById('nmap_ip').value);
	XHR.appendData('mask',document.getElementById('nmap_mask').value);
	XHR.sendAndLoad(Working_page, 'GET',x_NmapLoadList);
}

function SaveNmapSettings(){
	var XHR = new XHRConnection();
	XHR.appendData('NmapScanEnabled',document.getElementById('NmapScanEnabled').value);
	XHR.appendData('NmapRotateMinutes',document.getElementById('NmapRotateMinutes').value);
	XHR.sendAndLoad(Working_page, 'GET',x_NmapLoadList);	
	}
	
function nmap_logs(){
	YahooWin(750,Working_page+'?main=nmap-log','logs');
	}
	
var x_nmap_scan=function (obj) {
	nmap_logs();	
}
	
function nmap_scan(){
	var XHR = new XHRConnection();
	XHR.appendData('ScanNow','yes');
	XHR.sendAndLoad(Working_page, 'GET',x_nmap_scan);		
	
}