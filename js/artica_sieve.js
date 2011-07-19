/**
 * @author touzeau
 */
var Sieve_win;
var win_id;

function SieveAddRuleUser(userid){
		var rulename=prompt(document.getElementById("prompt_add_rule_name").value);
		var XHR = new XHRConnection();
		XHR.appendData('SieveSaveRuleName',rulename);
		XHR.appendData('userid',userid);
		XHR.sendAndLoad('user.sieve.rule.php', 'GET',x_SieveSaveRuleName);	
		SieveLoadRuleUSer(userid);	
		}	
		
function LoadWin(){
	Sieve_win = new Window({className: "artica",width:600, height:500, zIndex: 1000, resizable: true, draggable:true, wiredDrag: true,closable:true})
	win_id=Sieve_win.getId()
}		
		
function SieveListRules(userid,ruleid){
		if(!TestsWin()) {LoadWin();}
		var pars = 'SieveListRules='+userid+'&ruleid='+ruleid;
		Sieve_win.setAjaxContent('user.sieve.rule.php', {method: 'get', parameters: pars});
		//win.setStatusBar(userid); 
		Sieve_win.setDestroyOnClose();
 		Sieve_win.showCenter();
		Sieve_win.toFront();	
	
}

function TestsWin(){
	if(document.getElementById(win_id)){
		return true;
	}return false;
	
}

function SieveLoadRuleUSer(userid){
		if(!TestsWin()) {LoadWin();}  
		var pars = 'SieveLoadRuleUSer='+userid;
		Sieve_win.setAjaxContent('user.sieve.rule.php', {method: 'get', parameters: pars});
		//win.setStatusBar(userid); 
		Sieve_win.setDestroyOnClose();
 		Sieve_win.showCenter();
		Sieve_win.toFront();	
		}


function SieveSelectCondition(){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('fieldConditions');
	XHR.appendData('SieveSelectCondition',document.getElementById("field_header").value);		
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	}
function SieveSelectOperator(){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('FieldString');
	XHR.appendData('SieveSelectOperator',document.getElementById("field_conditions").value);		
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	}
	
var x_SieveSaveRuleName= function (obj){	
	var response=obj.responseText;
	if(response.length>0){alert(response)} 
}	
	
function SieveSaveRuleName(){
	var XHR = new XHRConnection();
	var userid=document.getElementById("userid").value;
	var RuleName=document.getElementById("rulename").value;
	var rulname_refer=document.getElementById("rulname_refer").value;
	var ruleid=document.getElementById("ruleid").value;
	XHR.appendData('SieveSaveRuleName',RuleName);
	XHR.appendData('SaveRuleNameRefer',rulname_refer);
	XHR.appendData('ruleid',ruleid);			
	XHR.appendData('userid',userid);
	XHR.sendAndLoad('user.sieve.rule.php', 'GET',x_SieveSaveRuleName);	
	Sieve_win.destroy();
	SieveListRules(userid,ruleid);
	
	
}	
function SieveSelectAction(){
	var XHR = new XHRConnection();
	var userid=document.getElementById("userid").value;
	XHR.appendData('SieveSelectAction',document.getElementById("action").value);
	XHR.appendData('userid',document.getElementById("userid").value);
	XHR.setRefreshArea('action_text');
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');
}

var x_SaveNewAction= function (obj){	
	var response=obj.responseText;
	if(response.length>0){alert(response);}
}

function SaveNewAction(){
	var userid=document.getElementById("userid").value;
	var ruleid=document.getElementById("ruleid").value;
	var FiledAction=document.getElementById("action").value;
	var action_id=document.getElementById("action_id").value;
	var XHR = new XHRConnection();
	if(document.getElementById("operations_days")){
		XHR.appendData('operations_days',document.getElementById("operations_days").value);
		XHR.appendData('operation_subject',document.getElementById("operation_subject").value);
		XHR.appendData('operation_message',document.getElementById("operation_message").value);
		}
	
	
	XHR.appendData('SaveNewAction',FiledAction);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.appendData('action_id',action_id);SieveListRules
	if(document.getElementById("operation")){
		XHR.appendData('operation',document.getElementById("operation").value);
	}
	XHR.sendAndLoad('user.sieve.rule.php', 'GET',x_SaveNewAction);
	SieveListRules(userid,ruleid);
}
function LoadSubRule(subrule_id){
	var ruleid=document.getElementById("ruleid").value;
	var userid=document.getElementById("userid").value;
	var XHR = new XHRConnection();
	XHR.appendData('subrule_id',subrule_id);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.setRefreshArea('subrules');	
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');	
}
function LoadSubAction(subaction_id){
	var ruleid=document.getElementById("ruleid").value;
	var userid=document.getElementById("userid").value;
	var XHR = new XHRConnection();
	XHR.appendData('subaction_id',subaction_id);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.setRefreshArea('subactions');	
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');	
}

function DeleteSubRule(subrule_id){
	var ruleid=document.getElementById("ruleid").value;
	var userid=document.getElementById("userid").value;
	var XHR = new XHRConnection();	
	XHR.appendData('DeleteSubRule',subrule_id);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	SieveListRules(userid,ruleid);
	}
function DeleteSubAction(subaction_id){
	var ruleid=document.getElementById("ruleid").value;
	var userid=document.getElementById("userid").value;
	var XHR = new XHRConnection();	
	XHR.appendData('DeleteSubAction',subaction_id);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	SieveListRules(userid,ruleid);
}

function MoveSubrule(subrule_id,move){
	var ruleid=document.getElementById("ruleid").value;
	var userid=document.getElementById("userid").value;	
	var XHR = new XHRConnection();	
	XHR.appendData('MoveSubrule',subrule_id);
	XHR.appendData('move',move);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	SieveListRules(userid,ruleid);	
}
function MoveSubAction(action_id,move){
	var ruleid=document.getElementById("ruleid").value;
	var userid=document.getElementById("userid").value;	
	var XHR = new XHRConnection();	
	XHR.appendData('MoveSubAction',action_id);
	XHR.appendData('move',move);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	SieveListRules(userid,ruleid);	
}

function SieveViewScript(userid,ruleid){
	var XHR = new XHRConnection();
	var pars = 'SieveViewScript='+userid+'&ruleid='+ruleid;	
	Sieve_win.setAjaxContent('user.sieve.rule.php', {method: 'get', parameters: pars});			
}

function SieveMoveMasterRule(userid,ruleid,move){
	var XHR = new XHRConnection();	
	XHR.appendData('SieveMoveMasterRule',ruleid);
	XHR.appendData('move',move);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	SieveLoadRuleUSer(userid);
}

function SieveDeleteMasterRule(ruleid,userid){
	var XHR = new XHRConnection();
	XHR.appendData('SieveDeleteMasterRule',ruleid);
	XHR.appendData('userid',userid);
	XHR.appendData('ruleid',ruleid);	
	XHR.sendAndLoad('user.sieve.rule.php', 'GET');		
	SieveLoadRuleUSer(userid);	
}
function SieveGenerateAllScripts(userid){
	var _win = new Window({className: "artica",width:600, height:400, zIndex: 1000, resizable: true, draggable:true, wiredDrag: true,closable:true})
	var pars = 'SieveGenerateAllScripts='+userid;
	_win.setAjaxContent('user.sieve.rule.php', {method: 'get', parameters: pars});
	_win.setDestroyOnClose();
 	_win.showCenter();
	_win.toFront();	
	_win.setZIndex(5000)	
	}
	
function SieveSaveToCyrus(userid){
	var XHR = new XHRConnection();
	XHR.appendData('SieveSaveToCyrus',userid);	
	XHR.sendAndLoad('user.sieve.rule.php', 'GET',x_SaveNewAction);
	
}	