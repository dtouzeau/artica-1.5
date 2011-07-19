var mem_nic;

var refresh_enable=function(obj){
      LoadAjax('enable_section','iptables.index.php?main=enablesec')  ;
}
var refresh_list=function(obj){
    RefreshTable();  
}

function iptables_edit(nic,num){
     YahooWin(440,'iptables.index.php?editrule='+num+'&nic='+nic);
    
}


function EnableIpTable(){
    var warn=document.getElementById('enable_iptables').value;
    var XHR = new XHRConnection();
    XHR.appendData('enable_iptables',warn);
    XHR.sendAndLoad('iptables.index.php', 'GET',refresh_enable);      
   
}

function iptablesmove(nic,num,dir){
    mem_nic=nic;
      var XHR = new XHRConnection();
      XHR.appendData('iptablesmove',num);
      XHR.appendData('direction',dir);
      XHR.appendData('nic',nic);
      XHR.sendAndLoad('iptables.index.php', 'GET',refresh_list);    
    }
    
function InsertDefaultsRules(){
    
    
}
function iptablesdel(nic,num){
    mem_nic=nic;
      var XHR = new XHRConnection();
      XHR.appendData('iptablesdel',num);
      XHR.appendData('nic',nic);
      XHR.sendAndLoad('iptables.index.php', 'GET',refresh_list);      
    }

function Compile(){
 YahooWin(440,'iptables.index.php?op=-1');
        for(var i=0;i<3;i++){
                setTimeout('iptables_run('+i+')',1500);
        }
}
function iptables_run(number){
        LoadAjax2('message_'+number,'iptables.index.php?op='+number)
        }
        
function RefreshTable(nic){
    if(nic){mem_nic=nic;}
    LoadAjax('serverlist','iptables.index.php?main=rulestable&nic='+mem_nic)  ;
    
}