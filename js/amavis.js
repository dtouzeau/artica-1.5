
var hostname_mem;
var rulename_mem;


var x_AddFqdnWL=function(obj){
      LoadAjax('list','sqlgrey.index.php?main=fqdn_list&hostname='+hostname_mem)  ;
}

var x_AddIPWL=function(obj){
      LoadAjax('list','sqlgrey.index.php?main=ipwl_list&hostname='+hostname_mem)  ;
}


function AmavisSmtpDomainRule(){
      var domain=document.getElementById('smtp-domain').value;
      LoadAjax('domain_rule','amavis.index.php?section=smtp-domain-rule&domain='+domain)  ;
      }
      
function DelFqdnWL(hostname,num){
 hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('DelFqdnWL',num);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddFqdnWL);      
      }
      
function AddIPWL(hostname){
      hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('AddIPWL',document.getElementById('whl_server').value);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddIPWL);
      }
      
 function DelIPWL(hostname,num){
 hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('DelIPWL',num);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddIPWL);      
      }
      
function LoadMailZulAdmins(){
    LoadAjax('zuladmin','amavis.index.php?zuladmin=yes')  ;  
}

function DeleteZuleAdmin(num){
    LoadAjax('zuladmin','amavis.index.php?zuladmin=yes&delete=' + num)  ;   
}
      
      


