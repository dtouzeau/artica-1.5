/**
 * @author touzeau
 */
var memory_ou;
var memory_gid;

function LoadKasTab(num,gidnumber){
	var XHR = new XHRConnection();
	XHR.appendData('tab',num);
	XHR.appendData('TreeKasSelect','kas:'+gidnumber);
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('users.kas.php', 'GET');	
	}

function SaveActions(gidnumber){
	
	ParseForm('FFM','users.kas.php',true)
	
}


function Change_ACTION_MODE(firstwords){
	var MODE=document.getElementById(firstwords + '_MODE').value;

	switch(MODE){
		case '0':
			document.getElementById(firstwords + '_SUBJECT_PREFIX').disabled=false;
			document.getElementById(firstwords + '_USERINFO').disabled=false;
			document.getElementById(firstwords + '_EMAIL').disabled=true;
			break;
		case '1':
			document.getElementById(firstwords + '_SUBJECT_PREFIX').disabled=false;
			document.getElementById(firstwords + '_USERINFO').disabled=false;
			document.getElementById(firstwords + '_EMAIL').disabled=false;
			break;
		
		case '2':
			document.getElementById(firstwords + '_SUBJECT_PREFIX').disabled=false;
			document.getElementById(firstwords + '_USERINFO').disabled=false;
			document.getElementById(firstwords + '_EMAIL').disabled=false;
			break;			
			
		case '-1':
			document.getElementById(firstwords + '_SUBJECT_PREFIX').disabled=true;
			document.getElementById(firstwords + '_USERINFO').disabled=true;
			document.getElementById(firstwords + '_EMAIL').disabled=true;		
			break;	
		case '-3':
			document.getElementById(firstwords + '_SUBJECT_PREFIX').disabled=true;
			document.getElementById(firstwords + '_USERINFO').disabled=true;
			document.getElementById(firstwords + '_EMAIL').disabled=true;		
			break;			
		
		
		
	}
	
	
}	
function LoadDNSSPFTAB(num,gidnumber,KasSelect){
var XHR = new XHRConnection();
	XHR.appendData('tab',1);
	XHR.appendData('DNSSPFTAB',num);
	XHR.appendData('TreeKasSelect',KasSelect);
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('users.kas.php', 'GET');	
	
	
}
function LoadACTIONGROUPTAB(num,gidnumber,KasSelect){
var XHR = new XHRConnection();
	XHR.appendData('tab',0);
	XHR.appendData('ACTIONGROUPTAB',num);
	XHR.appendData('TreeKasSelect',KasSelect);
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('users.kas.php', 'GET');	
	}

