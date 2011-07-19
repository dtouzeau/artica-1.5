/**
 * @author touzeau
 * // made by: Nicolas - http://www.javascript-page.com
 */

var timerID = 0;
var tStart  = null;

	

function UpdateTimer() {
   if(timerID) {
      clearTimeout(timerID);
      clockID  = 0;
   }

   if(!tStart){tStart   = new Date();}

   var   tDate = new Date();
   var   tDiff = tDate.getTime() - tStart.getTime();
   tDate.setTime(tDiff);
   var seconds=tDate.getSeconds();
   var TimerEnd=document.getElementById("ExecSecs").value;
   
  if(seconds>TimerEnd){ 
  	RefreshDatas();
	Reset();
	return true;
	}
   if(document.getElementById("timertext")){document.getElementById("timertext").value = "" + tDate.getMinutes() + ":" + tDate.getSeconds();}
   timerID = setTimeout("UpdateTimer()", 1000);
}

function Start() {
   tStart   = new Date();
   document.getElementById("timertext").value = "00:00";
   timerID  = setTimeout("UpdateTimer()", 1000);
}

function Stop() {
   if(timerID) {
      clearTimeout(timerID);
      timerID  = 0;
   }

   tStart = null;
}

function Reset() {
   tStart = null;
   if(document.getElementById("timertext")){
   document.getElementById("timertext").value = "00:00";
   }
}
	

	
function executeLogMonitor(){s_PopUpScroll('listener.logs.php',800,500,'Logs Monitor');}
function RefreshDatas(){
	
	setTimeout("SendRequests()", 500);
	Start();
}
	
function StartLogMonitor(){
	executeLogMonitor();
	
}
function SendRequests(){
	var XHR = new XHRConnection();
	document.getElementById("logs_windows").innerHTML='';
	XHR.setRefreshArea('logs_windows');
	if(document.getElementById("ExecSecs")){XHR.appendData('Refresh',document.getElementById("ExecSecs").value);}
	XHR.appendData('maillog','yes');
	XHR.appendData('maillog_filter',document.getElementById("maillog_filter").value);
	XHR.sendAndLoad('listener.logs.php', 'GET');	
}
