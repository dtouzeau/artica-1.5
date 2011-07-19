
function ChangeTheSystemClock(){
    var mClock=document.getElementById('clock_value').value;
    var newClock=prompt(document.getElementById('changeClock').value,mClock);
    if(newClock){
        var XHR = new XHRConnection();
	XHR.appendData('changeClock',newClock);
	XHR.sendAndLoad('system.hardware.php', 'GET',x_parseform);
    }
    
    LoadAjax('clocks','system.hardware.php?clocks=yes')
}

function ConvertSystemToHard(){
    if(confirm(document.getElementById('convert_clock_tooltip').value + ' ?')){
        var XHR = new XHRConnection();
	XHR.appendData('ConvertSystemToHard','yes');
	XHR.sendAndLoad('system.hardware.php', 'GET',x_parseform);
        LoadAjax('clocks','system.hardware.php?clocks=yes')
    }
    
}


var x_FolderList=function(obj){
      LoadAjax('hardlist','system.harddisk.php?main=config')  ;
}

function SaveFolderList(num,md){
    var path=document.getElementById(md).value;
    var XHR = new XHRConnection();
    XHR.appendData('SaveFolderList',path);
    XHR.appendData('index',num);
    XHR.sendAndLoad('system.harddisk.php', 'GET',x_FolderList);
    }
function AddFolderList(){
    var path=document.getElementById('addfodler').value;
    var XHR = new XHRConnection();
    XHR.appendData('AddFolderList',path);
    XHR.sendAndLoad('system.harddisk.php', 'GET',x_FolderList);
    
}
function DeleteFolderList(num){
    var XHR = new XHRConnection();
    XHR.appendData('DeleteFolderList',num);
    XHR.sendAndLoad('system.harddisk.php', 'GET',x_FolderList);    
    
}