var Working_page="index.bind9.php";


var x_forwarders=function (obj) {
	var tempvalue=obj.responseText;
	LoadAjax('pdns',Working_page + '?main=yes&tab=forwarders-list')		
}

var x_ZonesList=function (obj) {
	var tempvalue=obj.responseText;
        if(tempvalue.Length>0){
            alert(tempvalue.Length);
        }
	LoadAjax('zones',Working_page + '?show-zones=yes')		
}

function AddForwarder(){
   var forwarder_add;
   forwarder_add=document.getElementById('forwarder_add').value;
   var forward=prompt(forwarder_add);
    var XHR = new XHRConnection();
    XHR.appendData('forwarder_add',forward);
    XHR.sendAndLoad(Working_page, 'GET',x_forwarders);   
    
}


function forwarder_delete(num){
    var XHR = new XHRConnection();
    XHR.appendData('forwarder_delete',num);
    XHR.sendAndLoad(Working_page, 'GET',x_forwarders);   
    
}


function zone_edit(Zone){
    YahooWin(750,Working_page+'?main=yes&tab=zone-edit&zone='+Zone,'Zone: '+Zone);
}
function zone_explain(){
    var zone=document.getElementById('zone_type').value;
    LoadAjax('zone_explain',Working_page + '?main=yes&tab=zone-explain&zone-selected='+zone)	
    
}

function SearchDnsConputer(){
    var search_explain=document.getElementById('search_explain').value;
    var zone=document.getElementById('zone_org').value;
    var query=prompt(search_explain);
    if(query){
        LoadAjax('bind9_hosts_list',Working_page + '?search-hosts='+query+'&zone='+zone);
        
    }
    
}

function AddNewDnsZone(){
    var text=document.getElementById('AddNewDnsZone_explain').value;
    var newzone=prompt(text);
    if(newzone){
        var XHR = new XHRConnection();
        XHR.appendData('AddNewDnsZone',newzone);
        XHR.sendAndLoad(Working_page, 'GET',x_ZonesList);      
    }
}

function zone_delete(zone){
    var ZoneDeleteWarning=document.getElementById('ZoneDeleteWarning').value;
    if(confirm(ZoneDeleteWarning)){
        var XHR = new XHRConnection();
        XHR.appendData('zone_delete',zone);
        XHR.sendAndLoad(Working_page, 'GET',x_ZonesList);   
    }
    
}

function CompileBind9(){
    LoadAjax('apply',Working_page + '?compile-bind=yes');
    
}



