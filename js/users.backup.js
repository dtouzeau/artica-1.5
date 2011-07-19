

var Refresh_Query=function(obj){
      document.getElementById('query_list').innerHTML=obj.responseText;
      
}


function findBackuphtml(){
    
    var direction=document.getElementById('direction').value;
    var email=document.getElementById('email').value;
    var subject=document.getElementById('subject').value;
    var body=document.getElementById('body').value;
    LoadAjax('query_list','users.backup.php?direction=' + direction + '&email='+email+'&subject='+subject+'&body='+body)
   }


function ShowBackupMail(messid){
      
      YahooWin(890,'users.backup.php?ShowBackupMail='+messid);
}

function ResendSendMail(message_path,email){
    LoadAjax('resendmail','users.backup.php?ResendSendMail=' + message_path + '&email='+email);
 
      
}