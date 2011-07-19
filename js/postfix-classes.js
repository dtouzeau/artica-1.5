/**
 * @author touzeau
 */
var Winid;
var Working_page='postfix.restrictions.classes.php';


function PostFixClassAddNew(){
	var XHR = new XHRConnection();
	var class_name=prompt(document.getElementById('give_class_name').value);
	if(class_name){
		XHR.appendData('PostFixClassAddNew',class_name);
		XHR.sendAndLoad(Working_page, 'GET',x_parseform);
		ReloadClass();
		}
	
}
function PostFixAddRestriction(class_name){
	YahooWin(440,Working_page+'?PostFixAddRestriction='+class_name);
	
	}
	
function PostfixSelectedRestriction(){
	var XHR = new XHRConnection();
	var selected_restriction=document.getElementById("restriction").value;
	var class_name=document.getElementById("class_name").value;
	if(!selected_restriction){return null;}
	XHR.setRefreshArea('selected_restriction');
	XHR.appendData('PostfixSelectedRestriction',selected_restriction);
	XHR.appendData('class_name',class_name);
	XHR.sendAndLoad(Working_page, 'GET');	
	
}	



function PostfixSaveRestriction(){
	ParseForm('FFM_REST',Working_page,true);
	YAHOO.example.container.dialog1.hide();
	}
	
	
function PostfixRestrictionMove(class_name,num,array_direction){
	var XHR = new XHRConnection();	
	XHR.appendData('PostfixRestrictionMove',array_direction);
	XHR.appendData('class_name',class_name);
	XHR.appendData('index',num);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	ReloadClassList(class_name);
}

function ReloadClass(){
	var XHR = new XHRConnection();	
	XHR.appendData('ReloadClass','yes');	
	XHR.setRefreshArea('class_list');
	XHR.sendAndLoad(Working_page, 'GET');	
	}

function ReloadClassList(restriction){
	var XHR = new XHRConnection();	
	XHR.appendData('ReloadClassList',restriction);	
	XHR.setRefreshArea('restrictions_list_'+restriction);
	XHR.sendAndLoad(Working_page, 'GET');	
	
}
function PostfixRestrictionDelete(class_name,num){
	var XHR = new XHRConnection();	
	XHR.appendData('PostfixRestrictionDelete',class_name);
	XHR.appendData('index',num);		
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	ReloadClassList(class_name);	
	}
	
function PostFixRestrictionLoadLdap(class_name,key){
	YahooWin(550,Working_page+'?PostFixRestrictionLoadLdap='+class_name+'&key='+ key);
	
	}	
	
function PostFixRestrictionLoadLdapSelect(){
	var pattern_email=document.getElementById("pattern_email").value;
	var pattern_action=document.getElementById("pattern_action").value;
		var XHR = new XHRConnection();	
		XHR.appendData('PostFixRestrictionLoadLdapSelect',pattern_email);
		XHR.appendData('pattern_action',pattern_action);
		XHR.setRefreshArea('PostFixRestrictionLoadLdapSecondStep');
		XHR.sendAndLoad(Working_page, 'GET');
}	

function PostFixRestrictionLoadLdapSave(){
	var table_value='';
	if(document.getElementById("value_1")){
		table_value=document.getElementById("value_1").value + ':' + document.getElementById("value_2").value;
	}
	if(document.getElementById("datas")){
		table_value=document.getElementById("datas").value
	}
	var XHR = new XHRConnection();	
	var class_name=document.getElementById("PostFixRestrictionLoadLdap_class_name").value;
	var table_name=document.getElementById("PostFixRestrictionLoadLdap_hash_table").value;
	var email=document.getElementById("pattern_email").value;
	var table_action=document.getElementById("pattern_action").value;
	XHR.appendData('PostFixRestrictionLoadLdapSave',class_name);
	XHR.appendData('table_name',table_name);
	XHR.appendData('email',email);
	XHR.appendData('value',table_value);
	XHR.appendData('action',table_action);	
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	PostfixRestrictionReloadLdapTable(class_name,table_name);
	}
	
function PostfixRestrictionReloadLdapTable(class_name,table_name){
	var XHR = new XHRConnection();	
	XHR.appendData('PostfixRestrictionReloadLdapTable',class_name);
	XHR.appendData('table_name',table_name);
	XHR.setRefreshArea('PostFixRestrictionTableList');
	XHR.sendAndLoad(Working_page, 'GET');
}	

function PostfixRestrictionPutEmailIntoField(email){
	document.getElementById("pattern_email").value=email;
	
}
function PostFixClassTableCheckDeleteValue(class_name,table_name,email,num){
	var XHR = new XHRConnection();	
	XHR.appendData('table_name',table_name);
	XHR.appendData('email',email);
	XHR.appendData('index',num);
	XHR.appendData('PostFixClassTableCheckDeleteValue',class_name);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	PostfixRestrictionReloadLdapTable(class_name,table_name);
}

function PostFixClassTableCheckMoveValue(class_name,table_name,email,num,move_direction){
	var XHR = new XHRConnection();	
	XHR.appendData('table_name',table_name);
	XHR.appendData('email',email);
	XHR.appendData('index',num);
	XHR.appendData('move_direction',move_direction);	
	XHR.appendData('PostFixClassTableCheckMoveValue',class_name);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	PostfixRestrictionReloadLdapTable(class_name,table_name);	
}
function PostfixClassEditDescription(){
	ParseForm('PostFixRestrictionClassDetailsForm',Working_page,true);
	ReloadClass();
	}

function PostFixClassRestrictionGenerateConfig(){
	YahooWin(550,Working_page+'?PostFixClassRestrictionGenerateConfig=yes');
	}


