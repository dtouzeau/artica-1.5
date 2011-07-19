
var hostname_mem;
var rulename_mem;
var x_SquidActionService=function(obj){
      LoadAjax('squid_main_config','squid.index.php?main=yes&tab=status&hostname='+hostname_mem)  ;
}

var x_SquidActionNetwork=function(obj){
      LoadAjax('squid_main_config','squid.index.php?main=yes&tab=networking&hostname='+hostname_mem)  ;
}

var x_SquidActionAcl=function(obj){
      LoadAjax('squid_main_config','squid.index.php?main=yes&tab=acl&hostname='+hostname_mem)  ;
}

var x_SquidActionAclRule=function(obj){
      LoadAjax('aclrules','squid.index.php?SquidAclAddrule=yes&hostname='+hostname_mem+'&rulename=' + rulename_mem);
      LoadAjax('AclDatas','squid.index.php?SquidAclLoadDatas=yes&hostname='+hostname_mem+'&rulename=' + rulename_mem);
}
var x_SquidActionAccess=function(obj){
      LoadAjax('squid_main_config','squid.index.php?main=yes&tab=access&hostname='+hostname_mem + '&rulename=' +rulename_mem );
}
var x_SquidActionCache=function(obj){
      LoadAjax('squid_main_config','squid.index.php?main=yes&tab=cache&hostname='+hostname_mem);
}



function SwitchSquidActionAccess(hostname,main_rule){
      LoadAjax('squid_main_config','squid.index.php?main=yes&tab=access&hostname='+hostname + '&rulename=' +main_rule );
      }

function SquidActionService(hostname,action){
        hostname_mem=hostname;
        var XHR = new XHRConnection();
        XHR.appendData('SquidActionService',hostname);
        XHR.appendData('action',action);
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionService);
        //dansguardian_service
    }
    
function Kav4ProxyActionService(hostname,action){
        hostname_mem=hostname;
        var XHR = new XHRConnection();
        XHR.appendData('Kav4ProxyActionService',hostname);
        XHR.appendData('action',action);
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionService);
    }
    
function DansGuardianActionService(hostname,action){
        hostname_mem=hostname;
        var XHR = new XHRConnection();
        XHR.appendData('DansGuardianActionService',hostname);
        XHR.appendData('action',action);
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionService);
    }    



function squid_http_port_delete(hostname,index,port_type){
        hostname_mem=hostname;
        var XHR = new XHRConnection();
        XHR.appendData('squid_http_port_delete',hostname);
        XHR.appendData('index',index);
        XHR.appendData('port_type',port_type);
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionNetwork);     
      
}

function SquidAclAddrule(hostname,rulename){
     if(rulename){
            LoadAjax('aclrules','squid.index.php?SquidAclAddrule=yes&hostname='+hostname+'&rulename=' + rulename);
            LoadAjax('AclDatas','squid.index.php?SquidAclLoadDatas=yes&hostname='+hostname+'&rulename=' + rulename);
     }else{
            LoadAjax('aclrules','squid.index.php?SquidAclAddrule=yes&hostname='+hostname);
            document.getElementById('AclDatas').innerHTML='';
     }
}
function SquidSelectAcl(){
       var acl_type=document.getElementById('acl_type').value;
       LoadAjax('aclform','squid.index.php?SquidSelectAcl='+acl_type)  ;        
}
function SquidAclActiveRule(rulename,active,hostname){
        hostname_mem=hostname;
        var XHR = new XHRConnection();
        XHR.appendData('SquidAclActiveRule',rulename);
        XHR.appendData('active',active);
        XHR.appendData('hostname',hostname);        
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAcl);
}


function SquidAddAcl(){
      var acl_type=document.getElementById('acl_type').value;
      var acl_name=document.getElementById('acl_name').value;
      
      reg = /\s+/g;
      acl_name=acl_name.replace(reg,'_');
      
      var acl_value=document.getElementById('acl_value').value;
      var hostname=document.getElementById('hostname').value;
      hostname_mem=hostname;
      rulename_mem=acl_name;
        var XHR = new XHRConnection();
        XHR.appendData('acl_type',acl_type);
        XHR.appendData('acl_name',acl_name);
        XHR.appendData('acl_value',acl_value);
        XHR.appendData('hostname',hostname);
        XHR.appendData('addacl','yes');
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAclRule);  
}

function SquidDeleteAclData(hostname,rulename,index){
        hostname_mem=hostname;
        rulename_mem=rulename;    
      
      var XHR = new XHRConnection();
        XHR.appendData('acl_name',rulename);
        XHR.appendData('hostname',hostname);
        XHR.appendData('acl_delete',index);   
        XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAclRule);  
}

function SquidAclDeleteRule(hostname,rulename){
      hostname_mem=hostname;
      rulename_mem=rulename;
      
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('rule_acl_delete',rulename);   
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAcl);      
}

function SquidAccessSwitchEnable(hostname,num,keyname){
      hostname_mem=hostname;
      rulename_mem=keyname;
      
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('AccessSwitch',keyname);
      XHR.appendData('AccessSwitchIndex',num);       
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);
      
}

function SquidAccessDelete(hostname,num,keyname){
      hostname_mem=hostname;
      rulename_mem=keyname;
      
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('SquidAccessDelete',keyname);
      XHR.appendData('index',num);       
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);
      }

function SquidBackAccessRules(hostname){
      if (confirm('Sure ???')){
          hostname_mem=hostname;
         var XHR = new XHRConnection();
         XHR.appendData('hostname',hostname);
         XHR.appendData('SquidBackAccessRules','yes');  
         XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);   
            
      }
}

function SquidAccessMove(hostname,num,keyname,move){
        var XHR = new XHRConnection();
         hostname_mem=hostname;
         rulename_mem=keyname;
         XHR.appendData('hostname',hostname);
         XHR.appendData('SquidAccessMove',keyname);
         XHR.appendData('index',num);
         XHR.appendData('move',move); 
         XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);      
}
function AddAccessAclRule(md){
     var XHR = new XHRConnection();
     var hostname=document.getElementById(md+'_hostname').value;
     var rulename=document.getElementById(md+'_rulename').value;
     
     hostname_mem=hostname;
     rulename_mem=rulename;
     
     var index=document.getElementById(md+'_index').value;
     var acl=document.getElementById(md+'_acl').value;
     var isnot='no'
     
      if(document.getElementById(md+'_isnot').checked){isnot='yes';}
     
      var XHR = new XHRConnection();
      hostname_mem=hostname; 
      XHR.appendData('hostname',hostname);
      XHR.appendData('AddAccessAclRule',rulename);
      XHR.appendData('index',index);
      XHR.appendData('acl',acl);
      XHR.appendData('isnot',isnot);
      document.getElementById('access_acl_rule_edit_' +rulename+ '_'+ index).innerHTML='';
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);
      }
      
      
function InsertIntoAccessAclRule(md){
 var XHR = new XHRConnection();
     var hostname=document.getElementById(md+'_hostname').value;
     var rulename=document.getElementById(md+'_rulename').value;
     var index=document.getElementById(md+'_index').value;
     var acl=document.getElementById(md+'_acl').value;
     
     rulename_mem=rulename;
     hostname_mem=hostname;
     
     var isnot='no'
     if(document.getElementById(md+'_isnot').checked){isnot='yes';}
     var XHR = new XHRConnection();
     hostname_mem=hostname; 
     XHR.appendData('hostname',hostname);
     XHR.appendData('InsertIntoAccessAclRule',rulename);
     XHR.appendData('index',index);
     XHR.appendData('acl',acl);
     XHR.appendData('isnot',isnot);
     XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);     
      
}
      
function SquidAccessInsertRule(hostname,index,keyname){
       LoadAjax('access_acl_rule_edit_' + keyname + '_' + index,'squid.index.php?SquidAccessInsertRule=' + keyname + '&hostname='+hostname + '&index=' + index);
      }


function SquidAccessDeleteAcl(hostname,keyname, index, acl_index){
     hostname_mem=hostname;
     rulename_mem=keyname;
     var XHR = new XHRConnection();
     XHR.appendData('hostname',hostname);
     XHR.appendData('SquidAccessDeleteAcl',keyname);
     XHR.appendData('index',index);
     XHR.appendData('acl_index',acl_index);
     XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionAccess);      
    }
function SquidUpdateNetworkConfig(hostname){
     var XHR = new XHRConnection();
      hostname_mem=hostname;
       XHR.appendData('hostname',hostname);
       XHR.appendData('UpdateGeneralConfig','yes');
       if(document.getElementById('visible_hostname')){XHR.appendData('visible_hostname',document.getElementById('visible_hostname').value);}
       if(document.getElementById('dead_peer_timeout')){XHR.appendData('dead_peer_timeout',document.getElementById('dead_peer_timeout').value);}
       if(document.getElementById('connect_timeout')){XHR.appendData('connect_timeout',document.getElementById('connect_timeout').value);}
       if(document.getElementById('peer_connect_timeout')){XHR.appendData('dns_timeout',document.getElementById('peer_connect_timeout').value);}
       if(document.getElementById('peer_connect_timeout')){XHR.appendData('peer_connect_timeout',document.getElementById('peer_connect_timeout').value);}
       if(document.getElementById('request_body_max_size')){XHR.appendData('request_body_max_size',document.getElementById('request_body_max_size').value);}
       if(document.getElementById('maximum_object_size').value){XHR.appendData('maximum_object_size',document.getElementById('maximum_object_size').value);}
       if(document.getElementById('cache_mem').value){XHR.appendData('cache_mem',document.getElementById('cache_mem').value);}
       if(document.getElementById('cache_swap_low').value){XHR.appendData('cache_swap_low',document.getElementById('cache_swap_low').value);}
       if(document.getElementById('cache_swap_high').value){XHR.appendData('cache_swap_high',document.getElementById('cache_swap_high').value);}
       if(document.getElementById('minimum_object_size').value){XHR.appendData('minimum_object_size',document.getElementById('minimum_object_size').value);}
       if(document.getElementById('maximum_object_size_in_memory').value){XHR.appendData('maximum_object_size_in_memory',document.getElementById('maximum_object_size_in_memory').value);}
       if(document.getElementById('ipcache_size').value){XHR.appendData('ipcache_size',document.getElementById('ipcache_size').value);}
       if(document.getElementById('ipcache_low').value){XHR.appendData('ipcache_low',document.getElementById('ipcache_low').value);}
       if(document.getElementById('ipcache_high').value){XHR.appendData('ipcache_high',document.getElementById('ipcache_high').value);}
       if(document.getElementById('fqdncache_size').value){XHR.appendData('fqdncache_size',document.getElementById('fqdncache_size').value);}
       
       
      if(document.getElementById('left-cache')){
            XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionCache);
            return true;
      }
      
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionNetwork);
}


function SquidCacheAdd(hostname,path){
    LoadAjax('left-cache','squid.index.php?main=yes&tab=cache&hostname=' + hostname + '&subsection=add-cache&path='+path);
      }
      
function SquidCacheAddAjax(hostname){
      var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('cache_dir',document.getElementById('cache_dir').value);
      XHR.appendData('cache_size',document.getElementById('cache_size').value);
      XHR.appendData('cache_type',document.getElementById('cache_type').value);
      XHR.appendData('cache_dir_level1',document.getElementById('cache_dir_level1').value);
      XHR.appendData('cache_dir_level2',document.getElementById('cache_dir_level2').value);
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionCache);
      }
      
function SquidCacheDelete(hostname,index){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('SquidCacheDelete',index);
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionCache);
      }
      
      
var SquidSimpleNetwork=function(obj){
      LoadAjax('squid_main_config','squid.simple.php?main=network&hostname='+hostname_mem)  ;
}
var SquidSimpleKav=function(obj){
      LoadAjax('squid_main_config','squid.simple.php?main=kavav&hostname='+hostname_mem)  ;
}
var SquidSimpleSafePortsList=function(obj){
      LoadAjax('Safe_Ports','squid.simple.php?main=Safe_ports_list&hostname='+hostname_mem)  ;
}    
var SquidSimpleDenyExt=function(obj){
      document.getElementById('_table_deny_ext').innerHTML='';
      LoadAjax2('_table_deny_ext','squid.simple.php?main=deny_ext_list&hostname='+hostname_mem)  ;
}


function SquidSimpleAddsrc(hostname){
      var squid_ip_client=document.getElementById('squid_ip_client').value;
      var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('squid_ip_client',squid_ip_client);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleNetwork);      
}

function SquidSimpleDelsrc(hostname,acl_name,index){
   var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('squid_acl_to_delete',acl_name);
      XHR.appendData('index',index);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleNetwork);      
      }
      
function SquidSimpleLdapEnable(hostname){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      var auth_allow;
      if(document.getElementById('auth_allow').checked){
           auth_allow="yes"; 
      }else{auth_allow="no";}
      XHR.appendData('hostname',hostname);
      XHR.appendData('auth_allow',auth_allow);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleNetwork);     
}

function SquidSimpleAddSafePorts(hostname){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      var protocol=document.getElementById('protocol').value;
      XHR.appendData('hostname',hostname);
      XHR.appendData('add_protocol',protocol);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleSafePortsList);      
      }
      
function SquidSimpleDelSafePort(hostname,index){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('Safe_Ports_delete',index);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleSafePortsList);      
}

function SquidSimpleAddDenyExt(hostname){
  var XHR = new XHRConnection();
      hostname_mem=hostname;
      var add_deny_ext=document.getElementById('deny_ext').value;
      XHR.appendData('hostname',hostname);
      XHR.appendData('add_deny_ext',add_deny_ext);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleDenyExt);      
}
function SquidSimpleDelDenyExt(hostname,index){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('del_deny_ext',index);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleDenyExt);          
}

function SquidSimpleEditDansPort(hostname){
      var XHR = new XHRConnection();
      var http_port=document.getElementById('dans_listen_port').value;
      var http_port_ip=document.getElementById('dans_http_port_ip').value;
      var squid_port=document.getElementById('squid_listen_port').value;
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('dans_http_port',http_port);
      XHR.appendData('dans_http_port_ip',http_port_ip);
      XHR.appendData('squid_http_port',squid_port);        
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleNetwork);                
      
}
function SquidSimpleEditSquidPort(hostname){
      var XHR = new XHRConnection();
      var http_port=document.getElementById('listen_port').value;
      var http_port_ip=document.getElementById('http_port_ip').value;
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('squid_http_port',http_port);
      XHR.appendData('squid_http_port_ip',http_port_ip);      
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleNetwork);          
      
}
function squid_dnsserver_delete(hostname,index){
      var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);
      XHR.appendData('squid_dnsserver_delete',index);
      XHR.sendAndLoad('squid.index.php', 'GET',x_SquidActionNetwork);                
      }
      
function SquidSimpleKav4Proxy(hostname){
      var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);   
      XHR.appendData('EnableKav4Proxy',document.getElementById('EnableKav4Proxy').value);
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleKav);      
      }
      
function SquidSimpleKav4ProxyMacro1(hostname){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);   
      XHR.appendData('SquidSimpleKav4ProxyMacro1','yes');
      XHR.sendAndLoad('squid.simple.php', 'GET',SquidSimpleKav);           
}

var X_SQUID_APPLY=function(obj){
      var text;
      var mtext;
      text=obj.responseText;
      if(text.length>0){
            alert(text);
            
      }
      LoadAjax('services_status','squid.index.php?status=yes&hostname='+hostname_mem);
}
function SQUID_APPLY(hostname){
var XHR = new XHRConnection();
      hostname_mem=hostname;
      XHR.appendData('hostname',hostname);   
      XHR.appendData('ApplyConfig','yes');
      XHR.sendAndLoad('squid.index.php', 'GET',X_SQUID_APPLY);
      }

function SquidViewStartError(){
     
     YahooWin3(350,'squid.index.php?StartError=yes','Errors');
      
}

    