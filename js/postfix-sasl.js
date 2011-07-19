/**
 * @author touzeau
 */
function TreePostfixTLSCertificateInfos(){
	LoadWindows(650);
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixTLSCertificateInfos','yes');
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('tree.listener.postfix.php', 'GET');		
	}
	
function TreePostfixTLSCertificateGenerate(){
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixTLSCertificateGenerate','yes');
	XHR.sendAndLoad('tree.listener.postfix.php', 'GET',x_TreeFetchMailApplyConfig);		
	}
	
function TreePostfixTLSEnable(num){
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixTLSEnable',num);
	XHR.sendAndLoad('tree.listener.postfix.php', 'GET',x_TreeFetchMailApplyConfig);		
	MyHref('postfix.tls.php');
}	

function TLSLoggingLevel(){
	LoadWindows(450,300,'postfix.tls.php','TLSLoggingLevel=yes');
	
}	
function SaveEnableSasl(){
	ParseForm('sasl','postfix.sasl.php',true);
	LoadAjax('infos','postfix.sasl.php?loadinfos=y')
	}
function TLSStartTLSOffer(){LoadWindows(450,300,'postfix.tls.php','TLSStartTLSOffer=yes');}	
function TLSStartTLSOfferSave(){ParseForm('TLSlogging','postfix.tls.php',true);}
	
	
function sasl_add_isp_relay(){
	var isp_server_name=document.getElementById('isp_server_name').value;
	var username=document.getElementById('username').value;
	var password=document.getElementById('password').value;
	var XHR = new XHRConnection();
	XHR.appendData('isp_server_name',isp_server_name);
	XHR.appendData('username',username);
	XHR.appendData('password',password);	
	XHR.sendAndLoad('artica.wizard.ispout.php', 'GET');	
	LoadAjax('sasllist','artica.wizard.ispout.php?loadsaslList=yes');
}
function sasl_delete(servername){
	var XHR = new XHRConnection();
	XHR.appendData('sasl_delete',servername);
	XHR.sendAndLoad('artica.wizard.ispout.php', 'GET');	
	LoadAjax('sasllist','artica.wizard.ispout.php?loadsaslList=yes');	
}

function EditSasl(){
	var XHR = new XHRConnection();
	XHR.appendData('EditSasl',document.getElementById('smtp_sasl_auth_enable').value);
	XHR.sendAndLoad('artica.wizard.ispout.php', 'GET');		
	
}

function EditRelayhost(){
	var XHR = new XHRConnection();
	XHR.appendData('EditRelayhost',document.getElementById('isp_server_ip').value);
	XHR.sendAndLoad('artica.wizard.isprelay.php', 'GET',x_parseform);		
	
	
	
}
