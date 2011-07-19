
var hostname_mem;
var rulename_mem;
var ou_mem;

var Refresh_status=function(obj){
      LoadAjax('services_status','backuphtml.ou.php?status=yes&hostname=&ou='+ou_mem)
}
function RefreshRuleList(){
      LoadAjax('rules','backuphtml.ou.php?main=ruleslist&tab=rules&ou='+ou_mem) 
      
}



function artica_backup_rules_add(ou,num){
      
       YahooWin(490,'backuphtml.ou.php?addrule='+num+'&ou='+ou);
      
    }
    
function SaveRule(){
      var ou=document.getElementById('ou').value;
      ou_mem=ou;
      ParseForm('FFM2','backuphtml.ou.php',true);
      YAHOO.example.container.dialog1.hide();
      RefreshRuleList();
      }
      
   
function ArticaEnableBackup(sval,ou){
    if(sval==1){sval=0;}else{sval=1;}  
        var XHR = new XHRConnection();
        ou_mem=ou;
        XHR.appendData('ou',ou);
        XHR.appendData('ArticaEnableBackup',sval);       
        XHR.sendAndLoad('backuphtml.ou.php', 'GET',Refresh_status);      
      }
      
function ArticaBackupMove(ou,num,move){
     var XHR = new XHRConnection(); 
        ou_mem=ou;
        XHR.appendData('ou',ou);
        XHR.appendData('ArticaBackupMove',num);
        XHR.appendData('move',move);    
        XHR.sendAndLoad('backuphtml.ou.php', 'GET',RefreshRuleList);        
}


function ArticaBackupDeleteRule(ou,num){
        var XHR = new XHRConnection(); 
        ou_mem=ou;
        var text=document.getElementById('action_delete_rule').value;
        if(confirm(text)){
            XHR.appendData('ou',ou);
            XHR.appendData('ArticaBackupDeleteRule',num);
            XHR.sendAndLoad('backuphtml.ou.php', 'GET',RefreshRuleList);
        }
      }
