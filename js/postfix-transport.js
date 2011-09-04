/**
 * @author touzeau
 */



function PostfixAddRoutingTable(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	YahooWin(552,'postfix.routing.table.php?PostfixAddRoutingTable=yes&domainName='+Routingdomain,Routingdomain)
	}
	
function PostfixAddRelayTable(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	YahooWin(552,'postfix.routing.table.php?PostfixAddRelayTable=yes&domainName='+Routingdomain,Routingdomain)
	}
	
function PostfixAddRelayRecipientTable(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	YahooWin(552,'postfix.routing.table.php?PostfixAddRelayRecipientTable=yes&domainName='+Routingdomain,Routingdomain)
	}	
		
	
	
	
	
function PostfixAddRoutingRuleTable(Routingdomain){
if(!Routingdomain){Routingdomain='';}
	YahooWin(552,'postfix.routing.table.php?PostfixAddRoutingTable=yes&domainName='+Routingdomain + '&rule=yes',Routingdomain)	
	}	
	
function PostfixAddRoutingTable(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	YahooWin(552,'postfix.routing.table.php?PostfixAddRoutingTable=yes&domainName='+Routingdomain,Routingdomain)
	}
	
	
	

function LoadRelayDomainsTable(){
var XHR = new XHRConnection();
		XHR.setRefreshArea('relay_domains');
		XHR.appendData('LoadRelayDomainsTable','yes');
		XHR.sendAndLoad("postfix.routing.table.php", 'GET');	
	
}


function PostfixAddRoutingLoadTable(){
		PostfixRefreshRoutingTable();
		PostfixLocalLoadTable();
		PostfixSenderLoadTable();
		LoadRelayDomainsTable();
		PostfixRefreshRelayRecipient();	
		}
		
function PostfixRefreshRoutingTable(){
var XHR = new XHRConnection();
		XHR.setRefreshArea('routing_table');
		XHR.appendData('PostfixAddRoutingLoadTable','yes');
		XHR.sendAndLoad("postfix.routing.table.php", 'GET');	
	
}		
		
function PostfixLocalLoadTable(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('local_table');
		XHR.appendData('PostfixLocalLoadTable','yes');
		XHR.sendAndLoad("postfix.routing.table.php", 'GET');	
		}	
		
function PostfixSenderLoadTable(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('sender_table');
		XHR.appendData('PostfixSenderLoadTable','yes');
		XHR.sendAndLoad("postfix.routing.table.php", 'GET');	
		}			
		

		


function PostfixRefreshRelayRecipient(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('relay_recipient');
		XHR.appendData('PostfixRefreshRelayRecipient','yes');
		XHR.sendAndLoad("postfix.routing.table.php", 'GET');	
		}
function relayhost(){
	YahooWin(700,'postfix.routing.table.php?relayhost=yes',"Relay Host");	
}


function RtableExCol(div){
	document.getElementById('sender_table').style.visibility="hidden";
	document.getElementById('sender_table').style.width="0px";
	document.getElementById('sender_table').style.height="0px";
	document.getElementById('img_sender_table').src='img/link_a1.gif';	
	
	
	document.getElementById('routing_table').style.visibility="hidden";
	document.getElementById('routing_table').style.width="0px";
	document.getElementById('routing_table').style.height="0px";
	document.getElementById('img_routing_table').src='img/link_a1.gif';
	
	
	document.getElementById('local_table').style.visibility="hidden";
	document.getElementById('local_table').style.width="0px";
	document.getElementById('local_table').style.height="0px";
	document.getElementById('img_local_table').src='img/link_a1.gif';
	
	document.getElementById('relay_domains').style.visibility="hidden";
	document.getElementById('relay_domains').style.width="0px";
	document.getElementById('relay_domains').style.height="0px";
	document.getElementById('img_relay_domains').src='img/link_a1.gif';
	
	document.getElementById('relay_recipient').style.visibility="hidden";
	document.getElementById('relay_recipient').style.width="0px";
	document.getElementById('relay_recipient').style.height="0px";	
	document.getElementById('img_relay_recipient').src='img/link_a1.gif';
	
	document.getElementById(div).style.visibility="";
	document.getElementById(div).style.width="490px";
	document.getElementById(div).style.height="70px";
	document.getElementById('img_'+div).src='img/link_a2.gif';
	}

function RelayHostDelete(){
	var XHR = new XHRConnection();
	XHR.appendData('RelayHostDelete','yes');
	XHR.sendAndLoad("postfix.routing.table.php", 'GET');			
	}
	
function SenderTable(Routingdomain){
if(!Routingdomain){Routingdomain='';}
	LoadWindows(552,400,"postfix.routing.table.php",'SenderTable=yes&domainName='+Routingdomain);	
	
}