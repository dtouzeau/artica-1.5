/**
 * @author touzeau
 */


	function edit_mailbox_settings(email){
		var XHR = new XHRConnection();
		var password1=document.getElementById('password').value;
		var password2=document.getElementById('password2').value;
		
		if(password1.length==0){
			alert(document.getElementById('no_password').value);
			return false;
			} 
			
		if(password1!==password2){
			alert(document.getElementById('password_no_match').value);
			return false;
		}
		
		if (document.getElementById('max_quota')){
			XHR.appendData('max_quota',document.getElementById('max_quota').value);
		}
			
			XHR.appendData('password',password1);
			XHR.appendData('edit_mailbox_settings',email);						
			XHR.sendAndLoad('mailbox.settings.php', 'GET');
			
		}
		
		
	function set_quota(email){
		var XHR = new XHRConnection();
		XHR.appendData('max_quota',document.getElementById('max_quota').value);
		XHR.appendData('set_quota',email);
		XHR.sendAndLoad('mailbox.settings.php', 'GET');
		alert(document.getElementById('ERR_SET_QUOTA_SUCCESS').value);
		
	}
		

	