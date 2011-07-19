var mem_folder_name;

function SambaBrowser(){
    
    YahooTreeFolders(490,'samba.index.php');
}

var x_FindUserGroup=function (obj) {
tempvalue=obj.responseText;
document.getElementById('finduserandgroupsid').innerHTML=tempvalue;
}

var x_RefreshEXT=function (obj) {
    LoadAjax('main_config','mimedefang.index.php?main=bad_exts&hostname=')
}
var x_RefreshDISC=function (obj) {
    LoadAjax('discladdress','mimedefang.index.php?main=discladdress&hostname=')
}


function MimeDefangDeleteExt(num){

        var XHR = new XHRConnection();
	XHR.appendData('MimeDefangDeleteExt',num);
        XHR.sendAndLoad('mimedefang.index.php', 'GET',x_RefreshEXT);
    
    
}

function MimeDefangAddExt(){
    
    var ext=prompt(document.getElementById('add_deny_ext_prompt').value);
    if(ext){
        var XHR = new XHRConnection();
	XHR.appendData('MimeDefangAddExt',ext);
        XHR.sendAndLoad('mimedefang.index.php', 'GET',x_RefreshEXT);
    }
    
}

function AddUserToFolder(uid){
    var XHR = new XHRConnection();
    mem_folder_name=document.getElementById('folder_security_users_ff').value;
    XHR.appendData('AddUserToFolder',uid);
    XHR.appendData('prop',mem_folder_name);
    XHR.sendAndLoad('samba.index.php', 'GET',x_RefreshUserList);
}

function UserSecurityInfos(item){
    document.getElementById('DeleteUserid').value=item;
    mem_folder_name=document.getElementById('folder_security_users_ff').value;
     LoadAjax('UserSecurityInfos','samba.index.php?UserSecurityInfos='+item+'&prop='+mem_folder_name);   
}

function DeleteUserPrivilege(){
    mem_folder_name=document.getElementById('folder_security_users_ff').value;
   var item=document.getElementById('DeleteUserid').value;
   if(item.length==0){
        alert(document.getElementById('selectuserfirst').value);
        return false;
    
   }
    var XHR = new XHRConnection();
    XHR.appendData('SaveFolderProp',mem_folder_name);
    XHR.appendData('SaveUseridPrivileges',item);
    XHR.appendData('read_list','no');
    XHR.appendData('valid_users','no');
    XHR.appendData('write_list','no');
    XHR.sendAndLoad('samba.index.php', 'GET',x_RefreshUserList);
}

var folderTabRefresh=function (obj) {
    LoadAjax('main_config','samba.index.php?main=shared_folders&hostname=');
    
}

function MimeDefangAddDisclamerAddress(){
    var ext=prompt(document.getElementById('disclaimer_servers_q').value);
     if(ext){
        var XHR = new XHRConnection();
	XHR.appendData('MimeDefangAddDisclamerAddress',ext);
        XHR.sendAndLoad('mimedefang.index.php', 'GET',x_RefreshDISC);
    }
    
}

function MimeDefangDelDisclamerAddress(num){
var XHR = new XHRConnection();
	XHR.appendData('MimeDefangDelDisclamerAddress',num);
        XHR.sendAndLoad('mimedefang.index.php', 'GET',x_RefreshDISC);    
}


