
var x_AdminDeleteAllSqlEvents=function (obj) {
	var tempvalue=obj.responseText;
        if(tempvalue.Length>0){
            alert(tempvalue.Length);
        }
	switch_tab('warnings','');
}

function AdminDeleteAllSqlEvents(){
  
    var XHR = new XHRConnection();
    XHR.appendData('AdminDeleteAllSqlEvents','yes');
    XHR.sendAndLoad('admin.index.php', 'GET',x_AdminDeleteAllSqlEvents);   
    
}

