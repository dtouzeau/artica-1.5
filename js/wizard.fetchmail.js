/**
 * @author touzeau
 */

var Working_page="artica.wizard.fetchmail.php";
var Winid;



function import_local_rules(){
		LoadAjax('left',Working_page + '?import_1=yes');		
		}

function LocalFetchMailRule(num){
	LoadWindows('400',450,'artica.wizard.fetchmail.php','LocalRules='+ num + '&title='+ num,5000)		
	
}


function InstallFetchMail(){
	LoadAjax('left',Working_page + '?InstallFetchmail=yes');
	LoadAjax('fetchmailbuttons',Working_page + '?fetchmailbuttons=yes');	
}

function loadUserRules(){
      var text=document.getElementById('load_user_rules_text').value;
      var user=prompt(text);
      if(user){
           LoadAjax('rightresults',Working_page + '?UserRules='+user);     
                
      }
}

function LoadFetchMailRuleFromUser(uid){
                
        LoadAjax('left',Working_page + '?LoadFetchMailRuleFromUser='+uid);            
}

