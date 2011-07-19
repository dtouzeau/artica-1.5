/**
 * @author touzeau
 */

var Working_page="system.nic.config.php";
var Winid;

function nicEditConfig(nic,tab,alias,master){
		if(!alias){alias='no';}
		if(!tab){tab='0'}
		if(!master){master='';}
		if(document.getElementById(Winid)){
			RemoveDocumentID(Winid);
			}
			Winid=LoadWindows(500,350,Working_page,'nicEditConfig=yes&nic=' + nic + '&tab='+ tab + '&alias='+alias + '&master='+master);
			setTimeout('NetWorkchangeProto()',1900);
	
		}
		
function NicShowConfig(){
	Winid=LoadWindows(600,600,Working_page,'NicShowConfig=yes');
	
}		
		
function NetWorkchangeProto(){
	if(!document.getElementById("BOOTPROTO")){return null;}
	var proto=document.getElementById("BOOTPROTO").value;
	var xdisa='visible';
	if(proto=='dhcp'){xdisa='hidden';}else{xdisa='visible';}
		document.getElementById("IPADDR").style.visibility=xdisa;
		document.getElementById("NETMASK").style.visibility=xdisa;
		document.getElementById("GATEWAY").style.visibility=xdisa;
		document.getElementById("NETWORK").style.visibility=xdisa;
		document.getElementById("BROADCAST").style.visibility=xdisa;
		document.getElementById("cdr").style.visibility=xdisa;
}		

var xCalcCDR= function (obj) {
	var results=obj.responseText;
	var Table=results.split(";");
	document.getElementById("cdr").innerHTML=Table[0];
	document.getElementById("NETWORK").value=Table[1];
	document.getElementById("BROADCAST").value=Table[2];
	
	
}

function CalcCDR(){
	var ip=document.getElementById("IPADDR").value;
	var netmask=document.getElementById("NETMASK").value;
	var XHR = new XHRConnection();
	XHR.appendData('CalcCDR',ip);
	XHR.appendData('NETMASK',netmask);
	XHR.sendAndLoad(Working_page, 'GET',xCalcCDR);	
	
}
function AddNicaliases(nic){
	var XHR = new XHRConnection();
	XHR.appendData('AddNicaliases',nic);
	XHR.sendAndLoad(Working_page, 'GET');
	LoadNicAliaseTable(nic);
	LoadArrayLdap();
}

function nicDeleteAlias(num,nic){
var XHR = new XHRConnection();
	XHR.appendData('nicDeleteAlias',nic);
	XHR.appendData('alias',num);
	XHR.sendAndLoad(Working_page, 'GET');	
	LoadNicAliaseTable(nic);
	LoadArrayLdap();	
}

function LoadNicAliaseTable(nic){
var XHR = new XHRConnection();
	XHR.appendData('LoadNicAliaseTable',nic);
	XHR.setRefreshArea(nic+'_aliases_table');
	XHR.sendAndLoad(Working_page, 'GET');	
	
}

function LoadArrayLdap(){
var XHR = new XHRConnection();
	XHR.appendData('LoadArrayLdap','yes');
	XHR.setRefreshArea('ldap_table');
	XHR.sendAndLoad("system.nic.config.php", 'GET');	
	
}
function LoadMainTable(){
	document.getElementById('current_table').innerHTML='<center><img src="img/frw8at_ajaxldr_7.gif"></center>';
	setTimeout('LoadMainTable2()',1200);	
	
}
function LoadMainTable2(){
	var XHR = new XHRConnection();
	XHR.appendData('LoadMainTable','yes');
	XHR.setRefreshArea('current_table');
	XHR.sendAndLoad("system.nic.config.php", 'GET');		
}

var X_ApplyConfigToServer=function(obj){
      var text;
      text=obj.responseText;
      alert(text);
      LoadMainTable();
      
}

function ApplyConfigToServer(){
 	var XHR = new XHRConnection();
	XHR.appendData('ApplyConfigToServer','yes');
	XHR.sendAndLoad("system.nic.config.php", 'GET',X_ApplyConfigToServer);
        
                
                
}

