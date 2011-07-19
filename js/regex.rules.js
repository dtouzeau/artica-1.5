/**
 * @author touzeau
 */
var RulePageID;
var win_id;

var x_results= function (obj){	
	var response=obj.responseText;
	if(response.length>0){alert(response)}
	MyHref('user.content.rules.php');
}	

function AddFilterRule(){
		var XHR = new XHRConnection();
		XHR.appendData('AddFilterRule',document.getElementById("header_field").value);
		XHR.appendData('AddFilterRulePattern',document.getElementById("pattern").value);
		XHR.appendData('AddFilterRuleRegex',document.getElementById("regex").value);
		XHR.appendData('AddFilterRuleAction',document.getElementById("action").value);	
		if(document.getElementById("edit")){
			XHR.appendData('EditID',document.getElementById("edit").value);	
		}
		
		XHR.sendAndLoad('user.content.rules.php', 'GET',x_results);	
		}	
		
function AddNewRegxRule(){
	RulePageID = LoadWindows(450,450,'user.content.rules.php?ADD_PAGE=yes')
	
}	

function EditHeaderRule(num){	
		RulePageID=LoadWindows(450,450,'user.content.rules.php?EDIT_PAGE='+num)
		}	
function DeleteHeaderRule(num){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteHeaderRule',num);
		XHR.sendAndLoad('user.content.rules.php', 'GET',x_results);	
}	