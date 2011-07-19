/**
 * @author touzeau
 */
var win;
var win_id;

	
function PostfixLoadeMailsQueue(queue_name,total,page_number){
	//, onComplete: showResponse
	if(!page_number){page_number=0;}
	var XHR = new XHRConnection();
		XHR.appendData('PostfixLoadeMailsQueue',queue_name);
		XHR.appendData('total',total);
		XHR.appendData('tab',page_number);
		XHR.setRefreshArea('queuelist');
		XHR.sendAndLoad('postfix.queue.monitoring.php', 'GET');
}	

function LoadMailID(mailid,queue_name,page_number){
	win = new Window({className: "artica",width:600, height:500, zIndex: 1000, resizable: true, draggable:true, wiredDrag: true,closable:true})
	var pars = 'MailID='+mailid+'&queue_name='+queue_name + '&page_number='+page_number
	win.setAjaxContent('postfix.queue.monitoring.php', {method: 'get', parameters: pars});
	win.setDestroyOnClose();
 	win.showCenter();
	win.toFront();
	win_id=win.getId()	
	}
	
function PostQueueF(){
		var XHR = new XHRConnection();
		XHR.appendData('PostQueueF','yes');
		XHR.setRefreshArea('queuelist');
		XHR.sendAndLoad('postfix.queue.monitoring.php', 'GET');
			TableQueue();
		}
		
function TableQueue(){
var XHR = new XHRConnection();
		XHR.appendData('TableQueue','yes');
		XHR.setRefreshArea('table_queue');
		XHR.sendAndLoad('postfix.queue.monitoring.php', 'GET');		
}	

function DeleteMailID(queue_name,page_number,Mailid){
		if (confirm('Delete ! ' + Mailid + ' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteMailID',Mailid);
			XHR.sendAndLoad('postfix.queue.monitoring.php', 'GET');
			TableQueue();
			PostfixLoadeMailsQueue(queue_name,0,page_number);
			if(document.getElementById(win_id)){
				win.destroy();
			}
			
			
		}
}

function PostfixDeleteMailsQeue(queue_name){
	var txt=document.getElementById('remove_mailqueue_text').value;
	if(confirm(txt)){
			var XHR = new XHRConnection();
			XHR.appendData('PostfixDeleteMailsQeue',queue_name);
			XHR.sendAndLoad('postfix.queue.monitoring.php', 'GET');
			TableQueue();
		
	}
	
	
	
}	
	

	