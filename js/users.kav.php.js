/**
 * @author touzeau
 */
var memory_ou;
var memory_gid;
var win_notify;
var win_notify_id;
function EditKasperskySettings(){

	ParseForm('kasperskyactions','users.kav.php',true);
	
}	

function FilterByName_load(){
	var XHR = new XHRConnection()
	XHR.setRefreshArea('ext_list');
	XHR.appendData('FilterByName_load','yes');
	XHR.appendData('TreeKasSelect',document.getElementById('TreeKasSelect').value);	
	XHR.sendAndLoad('users.kav.php', 'GET');
}


var x_FilterByName_save= function (obj) {
	var tempvalue=obj.responseText;
	alert(tempvalue);
	FilterByName_load();
	}

function FilterByName_add(){
	var XHR = new XHRConnection();
	XHR.appendData('FilterByName_save',document.getElementById('FilterByName').value);
	XHR.appendData('TreeKasSelect',document.getElementById('TreeKasSelect').value);
	XHR.sendAndLoad('users.kav.php', 'GET',x_FilterByName_save);	
	}
	
function FilterByName_delete(num){
	var XHR = new XHRConnection();
	XHR.appendData('FilterByName_delete',num);
	XHR.appendData('TreeKasSelect',document.getElementById('TreeKasSelect').value);	
	XHR.sendAndLoad('users.kav.php', 'GET',x_FilterByName_save);	
	}	

function LoadKavTab(num,gidnumber){
	var XHR = new XHRConnection();
	XHR.appendData('tab',num);
	XHR.appendData('TreeKasSelect','kav:'+gidnumber);
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('users.kav.php', 'GET');	
	}
	
function LoadAvNotifyPopUp(){
	if(!document.getElementById(win_notify_id)){
		win_notify = new Window({className: "artica", width:400, height:500, zIndex: 1000, resizable: true, draggable:true, wiredDrag: true,closable:true}) 
	}
	
}	
	
function LoadAvNotify(t,u){
		LoadAvNotifyPopUp();
		var pars = 'LoadAvNotify='+t;
		if(u){pars=pars+'&LoadAvNotifyType='+u}
		win_notify.setAjaxContent('users.kav.php', {method: 'get', parameters: pars});
		win_notify.setStatusBar(""); 
		win_notify.setDestroyOnClose();
 		win_notify.showCenter();
		win_notify.toFront();
		win_notify_id=win_notify.getId();		
		}	
