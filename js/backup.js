var count_action;
var maxcount;
var mem_page;

var refresh_action=function(obj){
      count_action=count_action+1;
      if(count_action<maxcount){
        setTimeout('action_run('+count_action+')',1500);
      }
}

function StartAction(page,maxcountop){
    mem_page=page;
    maxcount=maxcountop;
    YahooWin(440,page+'?op=-1');
    setTimeout('action_run(0)',1500);
       
}

function action_run(number){
     var XHR = new XHRConnection();
      count_action=number;
      XHR.appendData('op',number);
      XHR.setRefreshArea('message_'+number);  
      XHR.sendAndLoad(mem_page, 'GET',refresh_action);
}