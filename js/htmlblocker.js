
var hostname_mem;
var rulename_mem;
var ou_mem;

var x_Blockermove=function(obj){
      LoadAjax('mainconfig','html.blocker.ou.php?main=yes&tab=rules&ou='+ou_mem)
}




function Blockermove(ou,num,move){
        var XHR = new XHRConnection();
        ou_mem=ou;
        XHR.appendData('ou',ou);
        XHR.appendData('Blockermove',num);
        XHR.appendData('move',move);        
        XHR.sendAndLoad('html.blocker.ou.php', 'GET',x_Blockermove);
    }
    

function blockerdelterule(ou,num){
      var XHR = new XHRConnection();
        ou_mem=ou;
        XHR.appendData('ou',ou);
        XHR.appendData('blockerdelterule',num);       
        XHR.sendAndLoad('html.blocker.ou.php', 'GET',x_Blockermove);
      
    
}


function BlockerAddNewRule(ou,num){
      if(!num){num=-1;}
       YahooWin(490,'html.blocker.ou.php?main=yes&tab=addrule&ou='+ou+'&num='+num);
      }