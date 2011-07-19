
var uid;
var currentpagename='logon.php';

var x_logon= function (obj) {
	tempvalue=obj.responseText;
	
	var reg=tempvalue.match(/<error>(.+)?<\/error>/);
	if(reg){
		if(reg.length>0){
				alert(reg[1]);
				return false;
				}
	}
	
	reg=tempvalue.match(/<link>(.+)?<\/link>/);
	if(reg){
		if(reg.length>0){
			DeleteAllCookies();
			window.location.href= reg[1];
		
		}
	}
	
	
	
	}

function logon(e){
	if(e){
	if(checkEnter(e))
		{
			
		var XHR = new XHRConnection();
		XHR.appendData('username',document.getElementById("username").value);
		XHR.appendData('password',document.getElementById("password").value);
		XHR.sendAndLoad(currentpagename, 'POST',x_logon);
	}}
	}