/**
 * @author touzeau
 */

var operations;
var znumber;


function bogoAddSpamUser(){
	var XHR = new XHRConnection();
	var ou=document.getElementById('ou').value;
	var user=document.getElementById('bogo_spam').value;
	var domain=document.getElementById('bogo_spam_domain').value;
	var type=document.getElementById('bogo_type').value;
	XHR.appendData('bogospam_user',user);
	XHR.appendData('bogospam_domain',domain);
	XHR.appendData('bogospam_type',type);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('bogofilter.ou.php', 'GET',x_parseform);
	LoadAjax('robots','bogofilter.ou.php?GetRobots='+ ou);
}

function BogoFilterAction(){
	var XHR = new XHRConnection();
	var ou=document.getElementById('ou').value;	
	var exceed=document.getElementById('exceed').value;
	var action=document.getElementById('action').value;	
	var prepend=document.getElementById('bogo_prepend').value;
	XHR.appendData('bogospam_action',action);
	XHR.appendData('prepend',prepend);
	XHR.appendData('exceed',exceed);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('bogofilter.ou.php', 'GET',x_parseform);	
	}

function DeleteRobot(uid,ou){
	var XHR = new XHRConnection();
	XHR.appendData('DeleteRobot',uid);	
	XHR.appendData('ou',ou);	
	XHR.sendAndLoad('bogofilter.ou.php', 'GET',x_parseform);
	LoadAjax('robots','bogofilter.ou.php?GetRobots='+ ou);
}
	
 
 
 