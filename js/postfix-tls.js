/**
 * @author touzeau
 */
var stepNum;

var x_tlsconfig=function(obj){
	LoadAjax('TLS_TABLE','postfix.tls.php?main=yes&tab=settings&hostname=')
      }

var x_tlsrouting=function(obj){
		LoadAjax('TLS_TABLE','postfix.tls.php?main=yes&tab=tls_table&hostname=')
      }
	
function TreePostfixTLSCertificateGenerate(){
	var XHR = new XHRConnection();
	XHR.appendData('TLSCertificateGenerate','yes');
	XHR.sendAndLoad('postfix.tls.php', 'GET',x_tlsconfig);		
	}
	
function TreePostfixTLSEnable(num){
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixTLSEnable',num);
	XHR.sendAndLoad('tree.listener.postfix.php', 'GET',x_tlsconfig);		
	}	

	
function TLSLoggingLevelSave(){ParseForm('loglevel','postfix.tls.php',true);}
function TLSStartTLSOffer(){LoadWindows(450,300,'postfix.tls.php','TLSStartTLSOffer=yes');}	
function TLSStartTLSOfferSave(){ParseForm('TLSlogging','postfix.tls.php',true);}

function TLSAddSMTPServer(smtp_server){
	if(!smtp_server){smtp_server=''}
	LoadWindows(450,300,'postfix.tls.php','TLSAddSMTPServer=yes&tls_smtp_server='+smtp_server);}	

function TLSLoadTable(){
	var XHR = new XHRConnection();
	XHR.appendData('TLSLoadTable','yes');
	XHR.setRefreshArea('TLS_TABLE');
	XHR.sendAndLoad('postfix.tls.php', 'GET');		
	}	
function TLSDeleteSMTPServer(server_name){
var XHR = new XHRConnection();
	XHR.appendData('TLSDeleteSMTPServer',server_name);
	XHR.sendAndLoad('postfix.tls.php', 'GET',x_tlsrouting);		
	
}

function smtpd_tls_security_level_choose(){
    var selected=document.getElementById('smtpd_tls_security_level').value;
    LoadAjax('smtpd_tls_security_level_infos','postfix.tls.php?smtpd_tls_security_level_infos='+selected);
    }
    
    
function ActiveTLSMsgbox(){
        
        YahooWin(340,'postfix.tls.php?ActiveTLSMsgbox=-1');
        for(var i=0;i<4;i++){
                setTimeout('ActiveTLSMsgbox_run('+i+')',1500);
        }
        
        
        
}
function ActiveTLSMsgbox_run(number){
       LoadAjax2('message_'+number,'postfix.tls.php?ActiveTLSMsgbox='+number)
        }
        
function TestTLSMsgbox(){
     YahooWin(640,'postfix.tls.php?TestTLSMsgbox=yes');    
        
}

var x_postfix_relay_clientcerts=function(obj){
	LoadAjax('postfix_relay_clientcerts','postfix.tls.php?x_postfix_relay_clientcerts=yes')
      }

function postfix_relay_clientcerts_del(id){
        var XHR = new XHRConnection();
	XHR.appendData('postfix_relay_clientcerts_del',id);
	XHR.sendAndLoad('postfix.tls.php', 'GET',x_postfix_relay_clientcerts);       
        
}

function postfix_relay_clientcerts_add(){
       ParseForm('postfixrelayclientcerts','postfix.tls.php',true);
       LoadAjax('postfix_relay_clientcerts','postfix.tls.php?x_postfix_relay_clientcerts=yes')
        
}

function relayssl_start(){
    enable=document.getElementById("enable_stunnel").value;   
    YahooWin(550,'postfix.relayssl.php?ApplyConfig=yes&enable='+enable);    
    setTimeout('relayssl_num('+0+')',1200);    
}

function relayssl_buildparams(){
    var server=document.getElementById("server").value;
    var port=document.getElementById("port").value;
    var password=document.getElementById("password").value;
    var localport=document.getElementById("localport").value;
    var username=document.getElementById("username").value;
    var smtp_sender_dependent_authentication=document.getElementById("smtp_sender_dependent_authentication").value;
    var XHR = new XHRConnection();
    XHR.appendData('server',server);
    XHR.appendData('port',port);
    XHR.appendData('password',password);
    XHR.appendData('localport',localport);
    XHR.appendData('username',username);
    XHR.appendData('smtp_sender_dependent_authentication',smtp_sender_dependent_authentication);
    return XHR;
    
}

var X_relayssl_num=function(obj){
      var text;
      var mtext;
      text=obj.responseText;
      
reg=text.match(/<err>(.+?)<\/err>/);
	if(reg){
		if(reg.length>0){
		mtext= reg[1];
		document.getElementById('content_postfix').innerHTML=document.getElementById('content_postfix').innerHTML+mtext;
                document.getElementById('stars').innerHTML="";
                return;
		}
	}      
      
      document.getElementById('content_postfix').innerHTML=document.getElementById('content_postfix').innerHTML+text;
      if(stepNum>15){
        document.getElementById('stars').innerHTML="";        
        return;}
      stepNum=stepNum+1;
      setTimeout('relayssl_num('+stepNum+')',500); 
      
}


function relayssl_num(num){
      var XHR=relayssl_buildparams();
      stepNum=num;
      if(document.getElementById('stars')){
        document.getElementById('stars').innerHTML="<img src='img/frw8at_ajaxldr_7.gif'>";
      }
      XHR.appendData('save_step',num);
      XHR.sendAndLoad('postfix.relayssl.php', 'POST',X_relayssl_num); 
}

function smtp_sender_dependent_authentication(){
    YahooWin(550,'postfix.relayssl.php?FillSenderForm=yes');    
}

function smtp_sender_dependent_authentication_submit(){
    var smtp_sender_dependent_authentication_email=document.getElementById("sender_email").value;
    var smtp_sender_dependent_authentication_password=document.getElementById("smtp_sender_dependent_authentication_password").value;
    var smtp_sender_dependent_authentication_username=document.getElementById("smtp_sender_dependent_authentication_username").value;
    var XHR = new XHRConnection();
    XHR.appendData('smtp_sender_dependent_authentication_email',smtp_sender_dependent_authentication_email);
    XHR.appendData('smtp_sender_dependent_authentication_password',smtp_sender_dependent_authentication_password);
    XHR.appendData('smtp_sender_dependent_authentication_username',smtp_sender_dependent_authentication_username);    
    XHR.sendAndLoad('postfix.relayssl.php',"POST",x_parseform);
}


function LoadMasterCf(){
    YahooWin2(750,'postfix.index.php?mastercf=yes','','');      
        
}

function stunnelSwitchdiv(div){
	document.getElementById('stunnel_relayhost').style.visibility="hidden";
	document.getElementById('stunnel_relayhost').style.width="0px";
	document.getElementById('stunnel_relayhost').style.height="0px";
		
	
	
	document.getElementById('stunnel_relayport').style.visibility="hidden";
	document.getElementById('stunnel_relayport').style.width="0px";
	document.getElementById('stunnel_relayport').style.height="0px";
	
	
	
	document.getElementById('stunnel_auth').style.visibility="hidden";
	document.getElementById('stunnel_auth').style.width="0px";
	document.getElementById('stunnel_auth').style.height="0px";
	
	
	
	document.getElementById(div).style.visibility="";
	document.getElementById(div).style.width="100%";
	document.getElementById(div).style.height="";
	
	}






