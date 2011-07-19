



var x_imap_Load_folders=function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){
            document.getElementById('menus_2').innerHTML=tempvalue;
            ExecuteIDScript('menus_2');
            };
	}

function imap_Load_folders(){
    var XHR = new XHRConnection();
	XHR.appendData('folders','yes');
	XHR.sendAndLoad('imap.index.php', 'GET',x_imap_Load_folders);
    
}


function ImapLoadMessages(branch){
    LoadAjax('webmail_corps','imap.index.php?MessagesFromFolder='+branch.getId());
    }