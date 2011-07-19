/**
 * @author touzeau
 */

var Working_page="global-countries-filters.ou.php";
var Winid;



function AddDenyCountry(){
		var country=document.getElementById('country_selected').value;
		var ou=document.getElementById('ou').value;	
		var action=document.getElementById('action').value;	
		var XHR = new XHRConnection();
		XHR.appendData('AddDenyCountry',country);
		XHR.appendData('ou',ou);		
		XHR.appendData('action',action);				
		XHR.sendAndLoad(Working_page, 'GET',x_parseform);
		LoadAjax('CountryList',Working_page + '?LoadDenyCountries='+ou);		
}

function CountryDelete(num){
	var ou=document.getElementById('ou').value;	
	var XHR = new XHRConnection();
	XHR.appendData('CountryDelete',num);
	XHR.appendData('ou',ou);		
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	LoadAjax('CountryList',Working_page + '?LoadDenyCountries='+ou);		
	
}

function AddRblServer(){
	var ou=document.getElementById('ou').value;	
	var rbl_server=document.getElementById('rbl_server').value;
	var XHR = new XHRConnection();	
	XHR.appendData('rbl_server',rbl_server);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('global-countries-rbl.ou.php','GET',x_parseform);
	LoadAjax('rbl-list','global-countries-rbl.ou.php?rbl-list='+ou);	
}

function AddSurblServer(){
	var ou=document.getElementById('ou').value;	
	var rbl_server=document.getElementById('surbl_server').value;
	var XHR = new XHRConnection();	
	XHR.appendData('surbl_server',rbl_server);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('global-countries-surbl.ou.php','GET',x_parseform);
	LoadAjax('rbl-list','global-countries-surbl.ou.php?rbl-list='+ou);	
}


function EditSurblRblServer(my){
	var data=my.value;
	var myid=my.id
	var ou=document.getElementById('ou').value;	
	var XHR = new XHRConnection();	
	XHR.appendData('EditRbl',myid);		
	XHR.appendData('pourc',data);
	XHR.appendData('ou',ou);		
	XHR.sendAndLoad('global-countries-surbl.ou.php','GET',x_parseform);
	LoadAjax('rbl-list','global-countries-surbl.ou.php?rbl-list='+ou);				
	
}



function EditRblServer(my){
	var data=my.value;
	var myid=my.id
	var ou=document.getElementById('ou').value;	
	var XHR = new XHRConnection();	
	XHR.appendData('EditRbl',myid);		
	XHR.appendData('pourc',data);
	XHR.appendData('ou',ou);		
	XHR.sendAndLoad('global-countries-rbl.ou.php','GET',x_parseform);
	LoadAjax('rbl-list','global-countries-rbl.ou.php?rbl-list='+ou);				
	
}
function EditActionRbl(){
	var ou=document.getElementById('ou').value;	
	var action=document.getElementById('action').value;
	var XHR = new XHRConnection();		
	XHR.appendData('rbl_action',action);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('global-countries-rbl.ou.php','GET',x_parseform);	
}

function EditActionSURbl(){
	var ou=document.getElementById('ou').value;	
	var action=document.getElementById('action').value;
	var XHR = new XHRConnection();		
	XHR.appendData('rbl_action',action);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('global-countries-surbl.ou.php','GET',x_parseform);	
}

function RblDelete(num){
	var ou=document.getElementById('ou').value;	
	var XHR = new XHRConnection();
	XHR.appendData('RblDelete',num);
	XHR.appendData('ou',ou);		
	XHR.sendAndLoad('global-countries-rbl.ou.php', 'GET',x_parseform);
	LoadAjax('rbl-list','global-countries-rbl.ou.php?rbl-list='+ou);		
	
}
function SURblDelete(num){
	var ou=document.getElementById('ou').value;	
	var XHR = new XHRConnection();
	XHR.appendData('RblDelete',num);
	XHR.appendData('ou',ou);		
	XHR.sendAndLoad('global-countries-surbl.ou.php', 'GET',x_parseform);
	LoadAjax('rbl-list','global-countries-surbl.ou.php?rbl-list='+ou);		
	
}

