
var hostname_mem;
var rulename_mem;


var x_AddFqdnWL=function(obj){
      LoadAjax('list','sqlgrey.index.php?main=fqdn_list&hostname='+hostname_mem)  ;
}

var x_AddIPWL=function(obj){
      LoadAjax('list','sqlgrey.index.php?main=ipwl_list&hostname='+hostname_mem)  ;
}


function AddFqdnWL(hostname){
      hostname_mem=hostname;
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('AddFqdnWL',document.getElementById('whl_server').value);
      XHR.sendAndLoad('sqlgrey.index.php', 'GET',x_AddFqdnWL);
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
      
      
function explainThisacl(){
      LoadAjax('explainc','milter.greylist.index.php?explainThisacl='+document.getElementById('type').value)  ;
      ChangeForm();
}

var x_ChangeForm=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>0){
          document.getElementById('addform').innerHTML=obj.responseText;
      }
}

function ChangeForm(){
      xclass=document.getElementById('SaveAclID').value;
      xtype=document.getElementById('type').value;
      var XHR = new XHRConnection();
      XHR.appendData('ChangeFormType',xtype);
      XHR.appendData('class',xclass);
      XHR.sendAndLoad('milter.greylist.index.php', 'GET',x_ChangeForm);      
      
}


