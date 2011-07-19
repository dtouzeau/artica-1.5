/**
 * @author touzeau
 */

var postfix_page='tree.listener.postfix.php';
var winPop1;
var winPop1_id;



function postfix_add_network_v2(){
	if(!test_winPop1()){winPop1 = new Window({className: "artica",width:300, height:450, zIndex: 2000, resizable: true, draggable:true, wiredDrag: true,closable:true})}
	var pars = 'postfix_add_network_v2=yes'
	winPop1.setAjaxContent(postfix_page, {method: 'get', parameters: pars});
	winPop1.setDestroyOnClose();
 	winPop1.showCenter();
	winPop1.toFront();
	winPop1_id=winPop1.getId();
		
	}
	
function postfix_new_network(){	
	ParseForm('postfixaddnetworkv2',postfix_page,true);
	postfix_add_network_v2();	
}
	
function test_winPop1(){
	if(!winPop1_id){return false;}
	if(document.getElementById(winPop1_id)){return true;}return false;
}	
	
function postfix_delete_network_v2(Index){
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixDeleteMyNetwork',Index);
	XHR.sendAndLoad(postfix_page, 'GET');
	postfix_add_network_v2();	
}	



function TreePostfixLoadPage(num){
		var mypage=CurrentPageName();
		
		var XHR = new XHRConnection();
		if(mypage=='domains.php'){
			XHR.setRefreshArea('rightInfos');	
			XHR.appendData('tab',num);
			XHR.appendData('CurrentPage',mypage);
			XHR.sendAndLoad(postfix_page, 'GET');
		}else{
		 	if(!document.getElementById('windows')){LoadWindows(600);}
		 	XHR.setRefreshArea('windows');	
		 	XHR.appendData('tab',num);
		 	XHR.appendData('CurrentPage',mypage);
		 	XHR.sendAndLoad(postfix_page, 'GET');
		}		
		}
		


function TreeEnableSMTPAuth(){
	var myValue=document.getElementById('smtp_sasl_password_maps_enable').value;
	var XHR = new XHRConnection();
	XHR.setRefreshArea('rightInfos');
	XHR.appendData('TreeEnableSMTPAuth',myValue);
	XHR.sendAndLoad(postfix_page, 'GET');	
}				
function TreeSMTPAuthEdit(num){
	var XHR = new XHRConnection();
		XHR.setRefreshArea('rightInfos');
		XHR.appendData('TreeSMTPAuthEdit',num);
		XHR.sendAndLoad(postfix_page, 'GET');		
	}
function TreeSMTPSaslAuthDelete(num){
var XHR = new XHRConnection();
		XHR.setRefreshArea('rightInfos');
		XHR.appendData('TreeSMTPSaslAuthDelete',num);
		XHR.sendAndLoad(postfix_page, 'GET');			
}
function TreePostfixLoadSmtpd_client_restrictions(Sender){
	LoadWindows(450,380);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('Sender',Sender);
	XHR.appendData('TreePostfixLoadSmtpd_client_restrictions','yes');
	XHR.sendAndLoad(postfix_page, 'GET');	
	}
function TreeSmtpd_client_restrictions_addrule(Sender){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('rulelist');
	XHR.appendData('Sender',Sender);
	XHR.appendData('TreeSmtpd_client_restrictions_addrule','yes');
	XHR.sendAndLoad(postfix_page, 'GET');
	Load_postfix_security_rules_table();
	
}	
function TreeSmtpd_client_restrictions_LoadruleForm(Sender){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('ruleform');
	XHR.appendData('Sender',Sender);
	XHR.appendData('TreeSmtpd_client_restrictions_LoadruleForm',document.getElementById('RuleSelected').value);
	XHR.sendAndLoad(postfix_page, 'GET');	
	}
function TreeSmtpd_client_restrictions_moverule(num,array_direction,Sender){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('TreeSmtpd_client_restrictions_moverule',num);
	XHR.appendData('Sender',Sender);
	XHR.appendData('array_direction',array_direction);
	XHR.sendAndLoad(postfix_page, 'GET');
	Load_postfix_security_rules_table();
}
function TreeSmtpd_client_restrictions_deleterule(num,Sender){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('Sender',Sender);
	XHR.appendData('TreeSmtpd_client_restrictions_deleterule',num);
	XHR.sendAndLoad(postfix_page, 'GET');
	Load_postfix_security_rules_table();
	}
	
function PopUpPostFixInterfaces(zswitch){
	LoadWindows(600);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('PopUpPostFixInterfaces','yes');
	XHR.appendData('tab',zswitch);	
	XHR.sendAndLoad(postfix_page, 'GET');		
}	
	
function PostFixBounceTemplate(template_name){
	LoadWindows(600);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('PostFixBounceTemplate',template_name);
	XHR.sendAndLoad(postfix_page, 'GET');	
	}	
function PagePostFixQueueTab(num){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('area');
	XHR.appendData('PagePostFixQueueTab',num);
	XHR.sendAndLoad('postfix.storage.rules.php', 'GET');	
	}
	
function Load_postfix_security_rules_table(){
	if(!document.getElementById('postfix_security_rules').innerHTML){return false;}
	var XHR = new XHRConnection();
	XHR.setRefreshArea('postfix_security_rules');
	XHR.appendData('postfix_security_rules','yes');
	XHR.sendAndLoad('postfix.security.rules.php', 'GET');		
}

function LoadSmtpdRejectUnlistedRecipient(){LoadWindows(450,300,postfix_page,'SmtpdRejectUnlistedRecipientLoad=yes');}
function LoadSmtpdHeloRequired(){LoadWindows(450,300,postfix_page,'LoadSmtpdHeloRequired=yes');}		

function SaveEnableSasl(){
	ParseForm('RejectUnlistedRecipient',postfix_page,true);
	Load_postfix_security_rules_table();
}

function SaveEnableHeloRequired(){
ParseForm('SmtpdHeloRequired',postfix_page,true);
	Load_postfix_security_rules_table();	
}

function PostFixCheckHashTable(ldapField,field){
	LoadWindows(400,400,postfix_page,'PostFixCheckHashTable='+ ldapField + '&field='+ field);
	}
function PostFixCheckHashTableSelectAction(){
	var ACTION=document.getElementById('action').value;
	var XHR = new XHRConnection();
	XHR.setRefreshArea('selected');
	XHR.appendData('PostFixCheckHashTableSelectAction',ACTION);
	XHR.sendAndLoad(postfix_page, 'GET');		
	}
function PostFixCheckHashTableSelectFilterAction(){
	LoadWindows(400,400,postfix_page,'PostFixCheckHashTableSelectFilterAction=yes',8000);
	}	
	
function PostFixCheckHashTableSelectPrependAction(){
	LoadWindows(400,400,postfix_page,'PostFixCheckHashTableSelectPrependAction=yes',8000);
	}	
function PostFixCheckHashTableSelectFilterActionSelect(){
	var service_type=document.getElementById('service_type').value;
	var XHR = new XHRConnection();
	XHR.setRefreshArea('FilterTableSelected');
	XHR.appendData('PostFixCheckHashTableSelectFilterActionSelect',service_type);
	XHR.sendAndLoad(postfix_page, 'GET');
	}
function PostFixCheckHashTableSelectFilterActionSave(){
	var xselected=document.getElementById('service_type').value;
	if(xselected.length>0){
		var XHR = new XHRConnection();
		if(xselected=='service'){
			XHR.appendData('PostFixCheckHashTableSelectFilterActionSave',xselected);
			XHR.appendData('filter',document.getElementById('filter').value);
			
			}
		if(xselected=='smtp'){
			XHR.appendData('PostFixCheckHashTableSelectFilterActionSave',xselected);
			XHR.appendData('smtp_server_address',document.getElementById('smtp_server_address').value);
			XHR.appendData('smtp_server_port',document.getElementById('smtp_server_port').value);
			XHR.appendData('MX_lookups',document.getElementById('MX_lookups').value);
		
		}
		XHR.setRefreshArea('selected');
		XHR.sendAndLoad(postfix_page, 'GET');
		alert('OK...');	
	}
}	
function PostFixCheckHashTableSelectPrependActionSave(){
	var XHR = new XHRConnection();
	XHR.appendData('PostFixCheckHashTableSelectPrependActionSave',document.getElementById('headers').value);
	XHR.appendData('prepend_text',document.getElementById('prepend_text').value);
	XHR.setRefreshArea('selected');
	XHR.sendAndLoad(postfix_page, 'GET');
	alert('OK...');		
	}
function PostFixCheckHashTableSave(){
	var Key=document.getElementById('value').value;
	var datas="";
	if(document.getElementById('action_option')){
		datas=document.getElementById('action_option').value;
	}
	
	var XHR = new XHRConnection();
	XHR.appendData('PostFixCheckHashTableSave',key);
	XHR.appendData('datas',datas);
	XHR.sendAndLoad(postfix_page, 'GET',x_parseform);
	}
	
function PostFixCheckHashTableDelete(ldapField,array_index){
	var XHR = new XHRConnection();
	XHR.appendData('PostFixCheckHashTableDelete',ldapField);
	XHR.appendData('array_index',array_index);
	XHR.sendAndLoad(postfix_page, 'GET',x_parseform);
	Load_postfix_security_rules_table();	
}
			



		