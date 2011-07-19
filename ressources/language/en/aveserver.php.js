var secs
var timerID = null
var timerRunning = false
var limit="0:20";
var parselimit;


function CurrentPageName(){
		var sPath = window.location.pathname;
		var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
		return sPage;		
		}



function StartTimer_logs(){
if (!document.images){return}
	parselimit=limit.split(":")
	parselimit=parselimit[0]*60+parselimit[1]*1;
	beginrefresh_logs();	
	
}

function Load_m_log(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('log_area');
		XHR.sendAndLoad(CurrentPageName()+ '?viewlogs=yes&logs=yes',"GET");	
}

function beginrefresh_logs(){
	if (parselimit==1){
		Load_m_log();
		StartTimer_logs();
		}
	else{ 
		parselimit-=1
		curmin=Math.floor(parselimit/60)
		cursec=parselimit%60
		setTimeout("beginrefresh_logs()",500)
		}
}

function LicenseDomain_Add(){
		if (document.getElementById("windows").style.left==''){
				document.getElementById("windows").style.left=xMousePos - 250 + 'px';
				document.getElementById("windows").style.top='100px';
				}
			document.getElementById("windows").style.height='auto';
    		
			document.getElementById("windows").style.width='500px';
			document.getElementById("windows").style.zIndex='3000';
    		document.getElementById("windows").style.visibility="visible";
			var XHR = new XHRConnection();
			XHR.setRefreshArea('windows');
			XHR.appendData('LicenseDomain_Add','yes');
			XHR.sendAndLoad(CurrentPageName(), 'GET');		
	}
	
function LicenseDomain_edit(){
	var newdomain=document.getElementById('LicenseDomain').value;
	var XHR = new XHRConnection();
	XHR.setRefreshArea('protected_domain');
	XHR.appendData('LicenseDomain_edit',newdomain);
	XHR.sendAndLoad(CurrentPageName(), 'GET');		
	document.getElementById("windows").style.visibility="hidden";
	document.getElementById("windows").innerHTML='';
	}
	
function LicenseDomain_Delete(xdomain){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('protected_domain');	
	XHR.appendData('LicenseDomain_Delete',xdomain);
	XHR.sendAndLoad(CurrentPageName(), 'GET');
	
}
function action_keepup2date(){
	var XHR = new XHRConnection();
	XHR.appendData('action_keepup2date','yes');
	XHR.sendAndLoad(CurrentPageName(), 'GET');
	alert('Update database launched...')
	
}
	