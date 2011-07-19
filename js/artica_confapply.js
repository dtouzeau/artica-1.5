/**
 * @author touzeau
 */

var operations;
var znumber;
var Apply_config_win;
var win_id;
var returned='';
var progress_num=0;
var apps_mem;


var ProgressBarInfos=function(obj){
var text;
var mtext;
text=obj.responseText;

if(text.length>5){
	
reg=text.match(/<text>(.+?)<\/text>/);
	if(reg){
		if(reg.length>0){
		mtext= reg[1];
		document.getElementById('textbar').innerHTML=document.getElementById('textbar').innerHTML+'<table style="width:450px"><tr><td>'+mtext+'</td></table>';
		}
	}	
	
      
}
     
      progress_num=progress_num+5;
      if(progress_num<99){
	Apply(progress_num);
      }else{
	var httprestart=document.getElementById('httprestart').value;
	document.getElementById('textbar').innerHTML=document.getElementById('textbar').innerHTML+'<table style="width:450px"><tr><td>'+httprestart+'</td></table>';
	document.getElementById('button').innerHTML="";
	alert(httprestart);
	ApplyHTTP();
	
      }
}

function ApplyConfigWait2(){
	setTimeout('ApplySingle()',1200);	
}

function ApplyConfig(productname){
	YahooWin(500,'actions.apply.configs.php?Step=Start');

	if(productname){
		returned=productname;
		setTimeout('ApplyConfigWait2()',1200);
	}

}

function ApplyHTTP(){
 
setTimeout('RestartHTTP()',1200);	
	
}

function RestartHTTP(){
var XHR = new XHRConnection();	
 XHR.appendData('ApplyNumber',100);
  XHR.sendAndLoad('actions.apply.configs.php', 'GET'); 	
}

function buttonApply(){
	setTimeout('StartApply()',1200);
}

function StartApply(){
	var i;
	document.getElementById('button').innerHTML="<img src='img/frw8at_ajaxldr_7.gif'>";
	Apply(0);	
	
}


function Apply(num){
  progress_num=num;
  var XHR = new XHRConnection(); 
  XHR.appendData('ApplyNumber',num);
  XHR.sendAndLoad('actions.apply.configs.php', 'GET',ProgressBarInfos);    	
	
}


function ApplySingle(step,area){
	if(returned.length>0){
		step=returned;
		area=returned;
	}
        if(document.getElementById('button')){document.getElementById('button').innerHTML='';}
        LoadAjax('applystart','actions.apply.configs.php?Step='+step);
        
}
function ApplySingleTimeOut(step,area){
		var XHR = new XHRConnection();
		XHR.setRefreshArea(area);
		document.getElementById('img_' + step).style.textDecoration=
		XHR.sendAndLoad('actions.apply.configs.php?Step='+step, 'GET');	
		document.getElementById('img_' + step).src='img/wait.gif';
		document.getElementById('button').innerHTML='<input type="button" OnClick="javascript:buttonApply();" value="Go&nbsp;&raquo;&raquo;">';	
		ReloadLocalPages();
		}


function ApplyConfigPostfix(){
		var XHR = new XHRConnection();
		document.getElementById('button').innerHTML="<img src='img/frw8at_ajaxldr_7.gif'>";
		XHR.setRefreshArea('postfix');
		XHR.sendAndLoad('actions.apply.configs.php?Step=postfix', 'GET');			
		setTimeout('ApplyConfigSqlGrey()',1200);
}

function ApplyConfigSqlGrey(){
		var XHR = new XHRConnection();
		document.getElementById('button').innerHTML="<img src='img/frw8at_ajaxldr_7.gif'>";
		XHR.setRefreshArea('sqlgrey');
		XHR.sendAndLoad('actions.apply.configs.php?Step=sqlgrey', 'GET');			
		setTimeout('ApplyConfigAveServer()',1200);
}

function ApplyConfigAveServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('kavmail');
		XHR.sendAndLoad('actions.apply.configs.php?Step=kavmail', 'GET');	
		setTimeout('ApplyConfigKasServer()',1200);		
	}
	
function ApplyConfigKasServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('kas');
		XHR.sendAndLoad('actions.apply.configs.php?Step=kas', 'GET');
		setTimeout('ApplyConfigMbxServer()',1200);	
		}
function ApplyConfigMbxServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('mbx');
		XHR.sendAndLoad('actions.apply.configs.php?Step=mbx', 'GET');
		setTimeout('ApplyConfigTasksServer()',1200);	
		}
function ApplyConfigTasksServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('tasks');
		XHR.sendAndLoad('actions.apply.configs.php?Step=tasks', 'GET');
		if(CurrentPageName()=='system.tasks.settings.php'){CronReloadMasterTable();}
		setTimeout('ApplyConfigDnsServer()',1200);
		}	
function ApplyConfigDnsServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('dns');
		XHR.sendAndLoad('actions.apply.configs.php?Step=dns', 'GET');
		setTimeout('ApplyConfigTCPServer()',1200);
		}
function ApplyConfigTCPServer(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('tcp');
		XHR.sendAndLoad('actions.apply.configs.php?Step=tcp', 'GET');
		ReloadLocalPages();
		setTimeout('ApplyConfigFetchMail()',1200);
		}
function ApplyConfigFetchMail(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('fetch');
		XHR.sendAndLoad('actions.apply.configs.php?Step=fetch', 'GET');
		ReloadLocalPages();
		setTimeout('ApplyConfigMailman()',1200);
		}
		
function ApplyConfigMailman(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('mailman');
		XHR.sendAndLoad('actions.apply.configs.php?Step=mailman', 'GET');
		ReloadLocalPages();
		setTimeout('ApplyConfigKav4proxy()',1200);
		}
			
function ApplyConfigKav4proxy(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('kav4proxy');
		XHR.sendAndLoad('actions.apply.configs.php?Step=kav4proxy', 'GET');
		ReloadLocalPages();
		setTimeout('ApplyConfigSquid()',1200);
		}
		
function ApplyConfigSquid(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('squid');
		XHR.sendAndLoad('actions.apply.configs.php?Step=squid', 'GET');
		setTimeout('ApplyConfigDansGuardian()',1200);
		}
	
function ApplyConfigDansGuardian(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('dansguardian');
		XHR.sendAndLoad('actions.apply.configs.php?Step=dansguardian', 'GET');
		ReloadLocalPages();
		setTimeout('ApplyConfigPureftpd()',1200);
		}
		
function ApplyConfigPureftpd(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('pure-ftpd');
		XHR.sendAndLoad('actions.apply.configs.php?Step=pure-ftpd', 'GET');
		ReloadLocalPages();
		buttonApplyEnd();
		}		
		
function buttonApplyEnd(){
	document.getElementById('button').innerHTML='<input type="button" OnClick="javascript:buttonApply();" value="Go&nbsp;&raquo;&raquo;">';
	
	
}

function StartStopService(cmd,typ,apps){
		Loadjs('admin.index.php?StartStopService-js=yes&svc=0&cmd='+cmd+'&typ='+typ+'&apps='+apps);
		}
        

        


function ReloadLocalPages(){
		if(CurrentPageName()=='system.nic.config.php'){
			setTimeout('LoadArrayLdap()',2200);
			setTimeout('LoadMainTable()',1200);
			
		}	
		if(CurrentPageName()=='system.tasks.settings.php'){
			CronReloadMasterTable();
		}
		
		if(CurrentPageName()=='squid.index.php'){
			LoadAjax('squid_main_config','squid.index.php?main=yes&tab=status&hostname='+hostname_mem)  ;
		}
	
}	


function applysettings_miltergreylist(){
	YahooWin5('600','milter.greylist.index.php?popup-save=yes');
	
}


 	
	
 
 
 