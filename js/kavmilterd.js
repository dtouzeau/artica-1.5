var PolicyRule='';

var x_KavMilterdAddNotify= function (obj) {
	LoadAjax('KavMilterdPolicyZone','milter.index.php?PolicyRule='+PolicyRule+'&PolicyTab=1')
	}

function KavMilterdAddNotify(){
        PolicyRule=document.getElementById("PolicyRule").value
        var XHR = new XHRConnection();
        XHR.appendData('DEST',document.getElementById("DEST").value);
        XHR.appendData('ACT',document.getElementById("ACT").value);
        XHR.appendData('PolicyRule',PolicyRule);
        XHR.sendAndLoad('milter.index.php', 'GET',x_KavMilterdAddNotify);
        
        }
        
function KavMilterdDeleteNotify(user,act){
   PolicyRule=document.getElementById("PolicyRule").value
        var XHR = new XHRConnection();
        XHR.appendData('KavMilterdDeleteNotify',user);
        XHR.appendData('ACTION',act);
        XHR.appendData('PolicyRule',PolicyRule);
        XHR.sendAndLoad('milter.index.php', 'GET',x_KavMilterdAddNotify); 
    
}

function kavmilter_add_key(PolicyRule){
    LoadAjax('license_data','milter.index.php?KavMilterAddkey')    
}

function kavmilter_select_notify_action(PolicyRule){
     var value_select=document.getElementById("DEST").value;
     LoadAjax('notify_rule_action','milter.index.php?kavmilter_select_notify_action='+ value_select + '&SelectedPolicyRule=' + PolicyRule);  
        
}

var x_Kav4proxyAddGroup= function (obj) {
	LoadAjax('mainconfig','kav4proxy.index.php?main=yes&tab=2')
	}

function Kav4proxyAddGroup(){
      var text=document.getElementById("add_group_text").value;
      var groupname=prompt(text);
      if(groupname){
         var XHR = new XHRConnection();
         XHR.appendData('kav4proxy_addnewgroup',groupname);
         XHR.sendAndLoad('kav4proxy.index.php', 'GET',x_Kav4proxyAddGroup);
        }
}

function kav4ProxyEditGroup(number){
     LoadAjax('group_data','kav4proxy.index.php?LoadGroup='+number)   
        
}

var x_Kav4ProxyAddClientIP=function(obj){
       kav4ProxyEditGroup(PolicyRule);
        
}
var x_Kav4ProxyAddClientExclude=function(obj){
      LoadAjax('group_data','kav4proxy.index.php?sec2=1&gid='+PolicyRule);
        
}

function Kav4ProxyAddClientIP(gid){
        PolicyRule=gid;
        var text=document.getElementById("ClientIPExplain").value;
        var rule=prompt(text);
        if(rule){
            var XHR = new XHRConnection();
                XHR.appendData('Kav4ProxyAddClientIP',gid);
                XHR.appendData('rule',rule);
                XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientIP);
        }
}

function Kav4ProxyAddClientURL(gid){
        PolicyRule=gid;
        var text=document.getElementById("ClientURLExplain").value;
        var rule=prompt(text);
        if(rule){
            var XHR = new XHRConnection();
                XHR.appendData('Kav4ProxyAddClientURL',gid);
                XHR.appendData('rule',rule);
                XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientIP);
        }
}


function Kav4ProxyDeleteClientIP(gid,rule){
        PolicyRule=gid;
        var XHR = new XHRConnection();
        XHR.appendData('Kav4ProxyDeleteClientIP',gid);
        XHR.appendData('rule',rule);
        XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientIP);       
        
}
function Kav4ProxyDeleteURL(gid,rule){
        PolicyRule=gid;
        var XHR = new XHRConnection();
        XHR.appendData('Kav4ProxyDeleteURL',gid);
        XHR.appendData('rule',rule);
        XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientIP);       
        }
        
function Kav4ProxyDeleteExcludeMime(gid,rule){
        PolicyRule=gid;
        var XHR = new XHRConnection();
        XHR.appendData('Kav4ProxyDeleteExcludeMime',gid);
        XHR.appendData('rule',rule);
        XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientExclude);       
        }
function Kav4ProxyDeleteExcludeURL(gid,rule){
        PolicyRule=gid;
        var XHR = new XHRConnection();
        XHR.appendData('Kav4ProxyDeleteExcludeURL',gid);
        XHR.appendData('rule',rule);
        XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientExclude);       
        }        
        
        
function Kav4ProxyAddExcludeUrl(gid){
        PolicyRule=gid;
        var text=document.getElementById("ExcludeURLExplain").value;
        var rule=prompt(text);
        if(rule){
            var XHR = new XHRConnection();
                XHR.appendData('Kav4ProxyAddExcludeURL',gid);
                XHR.appendData('rule',rule);
                XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientExclude);
        }
}
function Kav4ProxyAddExcludeMime(gid){
        PolicyRule=gid;
        var text=document.getElementById("ExcludeMimeTypeExplain").value;
        var rule=prompt(text);
        if(rule){
            var XHR = new XHRConnection();
                XHR.appendData('Kav4ProxyAddExcludeMime',gid);
                XHR.appendData('rule',rule);
                XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4ProxyAddClientExclude);
        }
}

function Kav4ProxyDeleteGroup(gid){
   var XHR = new XHRConnection();
    XHR.appendData('Kav4ProxyDeleteGroup',gid);
    XHR.sendAndLoad('kav4proxy.index.php', 'POST',x_Kav4proxyAddGroup); 
        
}


function Kav4ProxyMoveGroup(gid,move){
        var XHR = new XHRConnection();
        XHR.appendData('Kav4ProxyMoveGroup',gid);
        XHR.appendData('move',move);
        XHR.sendAndLoad('kav4proxy.index.php', 'GET',x_Kav4proxyAddGroup);       
}

function kav4proxy_add_key(PolicyRule){
    LoadAjax('license_data','kav4proxy.index.php?Kav4proxyAddkey')    
}

var x_kavmilter_generalConfig= function (obj) {
	LoadAjax('mainconfig','milter.index.php?main=yes&tab=0')
	}

function KavMilterEnable(){
        var XHR = new XHRConnection();
        XHR.appendData('enable_kavmilter',document.getElementById("enable_kavmilter").value);   
        XHR.sendAndLoad('milter.index.php', 'GET',x_kavmilter_generalConfig);       
        
}
