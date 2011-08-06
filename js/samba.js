var mem_folder_name;

function SambaBrowser(){
    
    YahooTreeFolders(490,'samba.index.php');
}

function FolderProp(folder){
    YahooWin4(500,'samba.index.php?prop='+folder,folder);
}

var x_FindUserGroup=function (obj) {
tempvalue=obj.responseText;
document.getElementById('finduserandgroupsid').innerHTML=tempvalue;
}

var x_RefreshUserList=function (obj) {
    LoadAjax('userlists','samba.index.php?userlists=yes&prop='+mem_folder_name);
    FindUserGroup();
}

function FindUserGroup(){
	if( document.getElementById('finduserandgroupsid')){
		var XHR = new XHRConnection();
		var IsNFS=document.getElementById('IsNFS').value;
		XHR.appendData('finduserandgroup','yes');
		XHR.appendData('IsNFS',IsNFS);
		XHR.appendData('query',document.getElementById('query').value);
		document.getElementById('finduserandgroupsid').innerHTML='<center><img src="img/wait_verybig.gif"></center>';    
		XHR.sendAndLoad('samba.index.php', 'GET',x_FindUserGroup);
	}
	
    
}





function FindUserGroupClick(e){
	if(checkEnter(e)){
		FindUserGroup();
	}
}


function AddUserToFolder(uid){
    var XHR = new XHRConnection();
    mem_folder_name=document.getElementById('folder_security_users_ff').value;
    XHR.appendData('AddUserToFolder',uid);
    XHR.appendData('prop',mem_folder_name);
    document.getElementById('finduserandgroupsid').innerHTML='<center><img src="img/wait_verybig.gif"></center>';
    XHR.sendAndLoad('samba.index.php', 'POST',x_RefreshUserList);
}

function UserSecurityInfos(item){
    document.getElementById('DeleteUserid').value=item;
    mem_folder_name=document.getElementById('folder_security_users_ff').value;
     LoadAjax('UserSecurityInfos','samba.index.php?UserSecurityInfos='+item+'&prop='+mem_folder_name);   
}

function DeleteUserPrivilege(item){
    mem_folder_name=document.getElementById('folder_security_users_ff').value;
   if(!item){item=document.getElementById('DeleteUserid').value;}
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
    document.getElementById('userlists').innerHTML='<center><img src="img/wait_verybig.gif"></center>';
    document.getElementById('UserSecurityInfos').innerHTML='';    
    XHR.sendAndLoad('samba.index.php', 'POST',x_RefreshUserList);
}

var folderTabRefresh=function (obj) {
	if(document.getElementById('main_config')){LoadAjax('main_config','samba.index.php?main=shared_folders&hostname=');}
	if(document.getElementById('FodPropertiesFrom')){YahooWin4Hide();}
	if(document.getElementById('main_samba_shared_folders')){RefreshTab('main_samba_shared_folders');}
	if(document.getElementById('main_config_samba')){RefreshTab('main_config_samba');}	
	if(document.getElementById('NewusbForm2009')){UUIDINDEXPOPREFRESH();}	
	
	
	
    
}

function FolderDelete(folder){
	var text='Confirm ?';
	if(document.getElementById('del_folder_name')){
		var text=document.getElementById('del_folder_name').value + '\n ' + folder;
	}
    if(confirm(text)){
        var XHR = new XHRConnection();
        XHR.appendData('FolderDelete',folder);
        XHR.sendAndLoad('samba.index.php', 'GET',folderTabRefresh);
        
    }
    
}


