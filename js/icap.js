
function icap_edit_service(hostname){
    ParseForm('icap_service_form','squid.index.php',true);
    LoadAjax('icap_section','squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=list-icap-service');
    }

var Refresh_class_table=function(obj){
      LoadAjax('icap_class_section','squid.index.php?main=yes&tab=icap_service&hostname='+hostname_mem+'&subsection=list-icap-class');
      }
var refresh_icap_acl=function(obj){
      icap_access_section(hostname_mem);
      }      
      

function SquidIcapDeleteService(hostname,service){
        var XHR = new XHRConnection();
        XHR.appendData('SquidIcapDeleteService',service);
        XHR.appendData('hostname',hostname);
        XHR.sendAndLoad('squid.index.php', 'GET');
        LoadAjax('icap_section','squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=list-icap-service');
        }

function SquidIcapAddServiceInClass(hostname,class_name){
    LoadAjax('form_class_'+class_name,'squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=form-class-addservice&class_name='+class_name);
    }
    
function icap_access_section(hostname){
    document.getElementById('icap_class_section').innerHTML='';
    LoadAjax('icap_section','squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=list-icap-access-list');
}

function icap_access_newrule(hostname){
    document.getElementById('icap_class_section').innerHTML='';
    LoadAjax('icap_section','squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=add-icap-access-list');
}

function SquidIcapAddServiceFromTable(hostname,class_name){
    var service=document.getElementById(class_name+'_icap_service').value;
    hostname_mem=hostname;
     var XHR = new XHRConnection();
        XHR.appendData('SquidIcapAddServiceFromTable',service);
        XHR.appendData('hostname',hostname);
        XHR.appendData('class_name',class_name);
        XHR.sendAndLoad('squid.index.php', 'GET',Refresh_class_table);
}

function SquidIcapDeleteServiceInClass(hostname,class_name,index){
        hostname_mem=hostname;
        var XHR = new XHRConnection();
        XHR.appendData('SquidIcapDeleteServiceInClass',index);
        XHR.appendData('hostname',hostname);
        XHR.appendData('class_name',class_name);
        XHR.sendAndLoad('squid.index.php', 'GET',Refresh_class_table);    
}

function SquidIcapAddNewClass(hostname){
        hostname_mem=hostname;
        var class_name=document.getElementById('class_name').value;
        var service=document.getElementById('icap_service').value;
        reg = /\s+/g;
        class_name=class_name.replace(reg,'_');        
        var XHR = new XHRConnection();
        XHR.appendData('SquidIcapAddNewClass',class_name);
        XHR.appendData('hostname',hostname);
        XHR.appendData('icap_service',service);
        XHR.sendAndLoad('squid.index.php', 'GET',Refresh_class_table);       
        }
        
function SquidIcapAddAccessRule(hostname){
    hostname_mem=hostname;
    var isnot;
    var class_name=document.getElementById('class_name').value;
    var class_switch=document.getElementById('class_switch').value;
    var icap_acl=document.getElementById('icap_acl').value;
    var SquidIcapAccessInsertRule=document.getElementById('SquidIcapAccessInsertRule').value;
    if(document.getElementById('isnot').checked){isnot='yes'; }else{isnot='no';}
    var XHR = new XHRConnection();
    XHR.appendData('SquidIcapAddAccessRule',class_name);
    XHR.appendData('hostname',hostname);
    XHR.appendData('class_name',class_name);
    XHR.appendData('class_switch',class_switch);
    XHR.appendData('icap_acl',icap_acl);
    XHR.appendData('isnot',isnot);
    XHR.appendData('SquidIcapAccessInsertRule',SquidIcapAccessInsertRule);     
    XHR.sendAndLoad('squid.index.php', 'GET',refresh_icap_acl);
    }
    
function SquidIcapAccessSwitchEnable(hostname,icap_access_index){
    hostname_mem=hostname;
    var XHR = new XHRConnection();
    XHR.appendData('SquidIcapAccessSwitchEnable',icap_access_index);
    XHR.appendData('hostname',hostname);
    XHR.sendAndLoad('squid.index.php', 'GET',refresh_icap_acl);
}
function SquidIcapInsertAccessRule(hostname,index){
  LoadAjax('icap_access_insert_rule_' + index,'squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=add-icap-access-list&SquidIcapAccessInsertRule=' + index);
}
function SquidIcapInsertAclRule(hostname,index){
 LoadAjax('icap_access_insert_rule_' + index,'squid.index.php?main=yes&tab=icap_service&hostname=' + hostname + '&subsection=add-icap-access-acl&SquidIcapInsertAclRule=' + index);   
}

function SquidIcapInsertAclRuleAjax(hostname,index){
    hostname_mem=hostname;
    var XHR = new XHRConnection();
    var isnot;
    var icap_acl=document.getElementById('icap_acl').value;
    if(document.getElementById('isnot').checked){isnot='yes'; }else{isnot='no';}
    XHR.appendData('SquidIcapInsertAclRuleAjax',icap_acl);
    XHR.appendData('index',index);
    XHR.appendData('isnot',isnot);
    XHR.appendData('hostname',hostname);
    XHR.sendAndLoad('squid.index.php', 'GET',refresh_icap_acl);    
    }
    
function SquidIcapDeleteAccessAcl(hostname,acl_index,access_index){
     hostname_mem=hostname;
    var XHR = new XHRConnection();
    XHR.appendData('SquidIcapDeleteAccessAcl',acl_index);
    XHR.appendData('icap_access',access_index);
    XHR.appendData('hostname',hostname);
    XHR.sendAndLoad('squid.index.php', 'GET',refresh_icap_acl);       
}

function SquidIcapDeleteAccessRule(hostname,access_index){
    hostname_mem=hostname;
    var XHR = new XHRConnection();
    XHR.appendData('SquidIcapDeleteAccessRule',access_index);
    XHR.appendData('hostname',hostname);
    XHR.sendAndLoad('squid.index.php', 'GET',refresh_icap_acl);        
}

function SquidIcapAccessMove(hostname,access_index,move){
 hostname_mem=hostname;
    var XHR = new XHRConnection();
    XHR.appendData('SquidIcapAccessMove',access_index);
    XHR.appendData('hostname',hostname);
    XHR.appendData('access_index',access_index);
    XHR.appendData('move',move);
    XHR.sendAndLoad('squid.index.php', 'GET',refresh_icap_acl);       
    
}
   
function SquidIcapDeleteClass(hostname,class_name){
    hostname_mem=hostname;
    var XHR = new XHRConnection();
    XHR.appendData('SquidIcapDeleteClass',class_name);
    XHR.appendData('hostname',hostname);
    XHR.sendAndLoad('squid.index.php', 'GET',Refresh_class_table);        
}
    
        
        