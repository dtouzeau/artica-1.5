/**
 * @author touzeau
 */

var Working_page="postfix.fallback.relay.php";
var Winid;

function PostfixAddFallBackServer(Routingdomain){
	if(!Routingdomain){Routingdomain='';}
	Winid=LoadWindows(430,330,Working_page,'PostfixAddFallBackServer=yes&domainName='+Routingdomain)
	}

function XHRPostfixAddFallBackerserverSave(){
	ParseForm('FFM3',Working_page,true);	
	PostfixAddFallBackerserverLoad();
	RemoveDocumentID(Winid);
}
function PostfixAddFallBackerserverLoad(){
	var XHR = new XHRConnection();	
	XHR.appendData('PostfixAddFallBackerserverLoad','yes');	
	XHR.setRefreshArea('table_list');
	XHR.sendAndLoad(Working_page, 'GET');	
}
function PostfixAddFallBackerserverDelete(num){
	var XHR = new XHRConnection();	
	XHR.appendData('PostfixAddFallBackerserverDelete',num);	
	XHR.sendAndLoad(Working_page, 'GET');	
	PostfixAddFallBackerserverLoad();	
	
}
function PostfixAddFallBackServerMove(num,move){
var XHR = new XHRConnection();	
	XHR.appendData('PostfixAddFallBackServerMove',num);
	XHR.appendData('move',num);		
	XHR.sendAndLoad(Working_page, 'GET');
	PostfixAddFallBackerserverLoad();			
	
}


