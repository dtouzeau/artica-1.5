page='firstwizard.php';
YahooWin(440,page+'?start=0');



function firstwizard_Cancel(){
    YAHOO.example.container.dialog1.hide();
    var XHR = new XHRConnection();
    XHR.appendData('cancel','1');
    XHR.sendAndLoad(page, 'GET');	
}

function firstwizard_1(){
    YAHOO.example.container.dialog1.show();
        YahooWin(440,page+'?start=1'+buildParams());
    }
    
    
function firstwizard_2(){YahooWin(440,page+'?start=2'+buildParams());}
    
function firstwizard_3(){
   YahooWin(440,page+'?start=3'+buildParams());   
}

function firstwizard_4(){
   YahooWin(440,page+'?start=4'+buildParams());   
}
function firstwizard_5(){
   YahooWin(440,page+'?start=5'+buildParams());   
}

function firstwizard_6(){
   YahooWin(440,page+'?start=6'+buildParams());   
}


function buildParams(){
    var params='';
   if(document.getElementById('ou')){params='&ou='+document.getElementById('ou').value; }
   if(document.getElementById('group')){params=params+'&group='+document.getElementById('group').value; }
   if(document.getElementById('domain')){params=params+'&domain='+document.getElementById('domain').value; }
   if(document.getElementById('domain_ip')){params=params+'&domain_ip='+document.getElementById('domain_ip').value; }
   if(document.getElementById('uid')){params=params+'&uid='+document.getElementById('uid').value; }
   if(document.getElementById('password')){params=params+'&password='+document.getElementById('password').value; }      
   
   
   
    return params
}


function Build(){
    YahooWin(440,page+'?start=7'+buildParams());  
    }
    
    
var refresh_BuildAction=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('message_'+count_action).innerHTML=tempvalue;
      count_action=count_action+1;
      
      if(count_action<7){
        setTimeout('BuildAction_run('+count_action+')',1500);
      }
}    


function BuildAction(){
    setTimeout('BuildAction_run(1)',1500);
    }


function BuildAction_run(number){
     var XHR = new XHRConnection();
     document.getElementById('message_'+number).innerHTML='<img src="img/wait.gif">';
      count_action=number;
      XHR.appendData('op',number);
      XHR.appendData('ou',document.getElementById('ou').value);
      XHR.appendData('group',document.getElementById('group').value);
      XHR.appendData('domain',document.getElementById('domain').value);
      XHR.appendData('domain_ip',document.getElementById('domain_ip').value);
      XHR.appendData('uid',document.getElementById('uid').value);
      XHR.appendData('password',document.getElementById('password').value);         
      XHR.sendAndLoad(page, 'GET',refresh_BuildAction);
      }

