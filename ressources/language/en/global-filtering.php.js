/**
 * @author touzeau
 */
	function CurrentPageName(){
		var sPath = window.location.pathname;
		var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
		return sPage;		
		}
		
	function add_filter_extension(){
		var extension=document.getElementById('extension').value;
		var XHR = new XHRConnection();
		XHR.setRefreshArea('extension_list');
		XHR.appendData('add_extension',extension);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		alert('Extension:' + extension + ' filtered');
	}
	
	function delete_extension(num){
		if(confirm('Delete this extension ?')){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('extension_list');
		XHR.appendData('del_extension',num);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		alert('Extension:' + extension + ' filtered deleted');
		}

	}
	

	
	function add_subject_rule(){
		var rule=document.getElementById('subject_rule').value;
		var postfix_error=document.getElementById('postfix_error').value;
		var XHR = new XHRConnection();
		XHR.setRefreshArea('array_subject');
		XHR.appendData('add_subject_rule',rule);
		XHR.appendData('add_postfix_error',postfix_error);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		}
	
	function delete_mail_subject_rule(num){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('array_subject');
		XHR.appendData('delete_mail_subject_rule',num);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		}		
	
	function add_mailfrom_rule(){
		var rule=document.getElementById('mail_from_rule').value;
		var postfix_error=document.getElementById('postfix_error').value;
		var XHR = new XHRConnection();
		XHR.setRefreshArea('array_from');
		XHR.appendData('add_mail_from_rule',rule);
		XHR.appendData('add_postfix_error',postfix_error);
		XHR.sendAndLoad(CurrentPageName(), 'GET');		
		}
		
	function delete_mail_from(num){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('array_from');
		XHR.appendData('delete_mail_from',num);
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		}			