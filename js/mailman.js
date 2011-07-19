var page='mailman.settings.php';
function LoadMailmanListSettings(mailman_list_name){
    LoadWindows(600,550,'mailman.settings.php','id='+mailman_list_name ,'1000',true);   
}

function mailman_add_moderator(id){
    
    var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_moderator',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?moderators=yes&id='+id);
            
        }
    }
    
function mailman_add_banlist(id){
     var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_banlist',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=1');
            
        }
    }
    
    
function mailman_add_accept_these_nonmembers(id){
      var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_accept_these_nonmembers',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=2');
            
        }   
    
}
function mailman_add_hold_these_nonmembers(id){
      var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_hold_these_nonmembers',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=3');
            
        }   
    }
function mailman_add_reject_these_nonmembers(id){
      var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_reject_these_nonmembers',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=4');
            
        }   
    }    
       
 //discard_these_nonmembers
 function mailman_add_discard_these_nonmembers(id){
      var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_discard_these_nonmembers',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=5');
            
        }   
    } 
    

function mailman_add_moderator2(id){
    
    var mod=prompt(document.getElementById("add_moderator_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_moderator2',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?moderators2=yes&id='+id);
            
        }
    }    
    
function mailman_delete_moderator(id,email){
            var XHR = new XHRConnection();
		XHR.appendData('delete_moderator',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?moderators=yes&id='+id);  
}
function mailman_delete_moderator2(id,email){
            var XHR = new XHRConnection();
		XHR.appendData('delete_moderator2',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?moderators2=yes&id='+id);  
}

function mailman_delete_banlist(id,email){
            var XHR = new XHRConnection();
		XHR.appendData('delete_banlist',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id+'&tab=1');  
}


function mailman_delete_available_languages(id,email){
            var XHR = new XHRConnection();
		XHR.appendData('delete_available_languages',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent_available_languages',page+'?available_languages=yes&id='+id);  
}


function mailman_delete_accept_these_nonmembers(id,email){
            var XHR = new XHRConnection();
		XHR.appendData('delete_accept_these_nonmembers',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=2');
}

function mailman_delete_hold_these_nonmembers(id,email){
            var XHR = new XHRConnection();
		XHR.appendData('delete_hold_these_nonmembers',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=3');
}
function mailman_delete_reject_these_nonmembers(id,email){//reject_these_nonmembers
            var XHR = new XHRConnection();
		XHR.appendData('delete_reject_these_nonmembers',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=4');
}
function mailman_delete_discard_these_nonmembers(id,email){//discard_these_nonmembers
            var XHR = new XHRConnection();
		XHR.appendData('delete_discard_these_nonmembers',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=2&tab1=5');
}
function mailman_delete_acceptable_aliases(id,email){//acceptable_aliases
            var XHR = new XHRConnection();
		XHR.appendData('delete_acceptable_aliases',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=3');
}


function mailman_delete_bounce_matching_headers(id,email){//bounce_matching_headers
            var XHR = new XHRConnection();
		XHR.appendData('delete_bounce_matching_headers',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=4&tab1=1');
}


function mailman_add_acceptable_aliases(id){
var mod=prompt(document.getElementById("acceptable_aliases_input").value);
        if(mod){
            	var XHR = new XHRConnection();
		XHR.appendData('add_acceptable_aliases',mod);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent',page+'?privacy=yes&id='+id + '&tab=3');
            
        }   
    }
function mailman_delete_header_filter_rules(id,email){
  var XHR = new XHRConnection();
		XHR.appendData('delete_header_filter_rules',email);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent','mailman.settings.php?privacy=yes&id=' + id + '&tab=4&tab1=0');   
    
}
function mailman_add_header_filter_rules(){
            var id=document.getElementById("id").value
            var XHR = new XHRConnection();
            var pattern=document.getElementById("header_filter_rules_pattern").value;
                pattern=FileRegex(pattern);
		XHR.appendData('header_filter_rules_pattern',pattern);
                XHR.appendData('header_filter_rules_action',document.getElementById("header_filter_rules_action").value);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'POST');
                LoadAjax(id+'_OptionsContent','mailman.settings.php?privacy=yes&id=' + id + '&tab=4&tab1=0');   
    
}

function mailman_add_bounce_matching_headers(){
   var id=document.getElementById("id").value
            var XHR = new XHRConnection();
            var pattern=document.getElementById("bounce_matching_headers").value;
                pattern=FileRegex(pattern);
		XHR.appendData('bounce_matching_headers',pattern);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_OptionsContent','mailman.settings.php?privacy=yes&id=' + id + '&tab=4&tab1=1');      
    
}

function FileRegex(value){
     value=value.replace(/\+/g,'#p');
     value=value.replace(/\n/g, "#CRLF");
     value=value.replace(/'/g, "\'");
     return value;
    
}


function mailman_move_header_filter_rules(id,num,move){
    var XHR = new XHRConnection();
    XHR.appendData('header_filter_rules_pattern_move',num);
    XHR.appendData('id',id);
    XHR.appendData('move',move);
    XHR.sendAndLoad(page, 'POST');
    LoadAjax(id+'_OptionsContent','mailman.settings.php?privacy=yes&id=' + id + '&tab=4&tab1=0');   
    
}
function filter_filename_extensions_add(tab){
     var id=document.getElementById("id").value
     var ext_text=document.getElementById("filter_filename_extensions_add").value
     var ext=prompt(ext_text);
     if(ext){
        var XHR = new XHRConnection();
        XHR.appendData('filter_filename_extensions_add',ext);
        XHR.appendData('id',id);
        XHR.appendData('tab',tab);
        XHR.sendAndLoad(page, 'GET');
        LoadAjax(id+'_table','mailman.settings.php?content_filtering_table=yes&id=' + id + '&tab=' + tab); 
     }
    
}
function filter_mime_types_add(tab){
     var id=document.getElementById("id").value
     var ext_text=document.getElementById("filter_mime_types_add").value
     var ext=prompt(ext_text);
     if(ext){
        var XHR = new XHRConnection();
        XHR.appendData('filter_mime_types_add',ext);
        XHR.appendData('id',id);
        XHR.appendData('tab',tab);
        XHR.sendAndLoad(page, 'GET');
        LoadAjax(id+'_table','mailman.settings.php?content_filtering_table=yes&id=' + id + '&tab='+ tab); 
     }
    
}

function mailman_delete_filter_mime_types(id,num,tab){
                var XHR = new XHRConnection();
		XHR.appendData('delete_filter_mime_types',num);
                XHR.appendData('id',id);
                XHR.appendData('tab',tab);      
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_table','mailman.settings.php?content_filtering_table=yes&id=' + id + '&tab='+tab); 
    
}
function mailman_delete_filter_filename_extensions(id,num,tab){
                var XHR = new XHRConnection();
		XHR.appendData('delete_filter_filename_extensions',num);
                XHR.appendData('id',id);
                XHR.appendData('tab',tab);   
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_table','mailman.settings.php?content_filtering_table=yes&id=' + id + '&tab='+tab);     
    
}
function mailman_add_topic(){
    var id=document.getElementById("id").value
    var topic_name=document.getElementById("topic_name").value;
    var topic_pattern=document.getElementById("topic_pattern").value;
    var topic_desc=document.getElementById("topic_desc").value;
    topic_desc=FileRegex(topic_desc);
    topic_pattern=FileRegex(topic_pattern);
    var XHR = new XHRConnection();
    XHR.appendData('topic_name',topic_name);
    XHR.appendData('topic_pattern',topic_pattern);
    XHR.appendData('topic_desc',topic_desc);
    XHR.appendData('id',id);
    XHR.sendAndLoad(page, 'GET');
    LoadAjax(id+'_list','mailman.settings.php?topic_list=yes&id=' + id + '');    
    
    
}

function mailman_delete_topics(id,num){
    var XHR = new XHRConnection();
		XHR.appendData('mailman_delete_topics',num);
                XHR.appendData('id',id);                
		XHR.sendAndLoad(page, 'GET');
                LoadAjax(id+'_list','mailman.settings.php?topic_list=yes&id=' + id + '');   
    
}

function mailman_move_topics(id,num,move){
  var XHR = new XHRConnection();
    XHR.appendData('mailman_move_topics',num);
    XHR.appendData('id',id);
    XHR.appendData('move',move);
    XHR.sendAndLoad(page, 'GET');
  LoadAjax(id+'_list','mailman.settings.php?topic_list=yes&id=' + id + '');   
    
}
function affect_org(){
    var id=document.getElementById("id").value;
    var XHR = new XHRConnection();
    XHR.appendData('affected_org',document.getElementById("affectedou").value);
    XHR.appendData('id',id);
    XHR.sendAndLoad(page, 'GET');
    LoadAjax(id+'_OptionsContent','mailman.settings.php?GeneralOptions=yes&id='+id);
    
}

function mailman_applysettings(list_name){
     LoadWindows(500,430,'mailman.settings.php','loadApplySettings=yes&id='+list_name ,'2000',true);   
     setTimeout("LoadAjax('results','mailman.settings.php?action_applysettings=" + list_name + "&id="+list_name+"')",600);
     if(document.getElementById("mailman_lists")){
        setTimeout("LoadMailManList()",1000);
     }
    
}

function mailman_addresses(list_name){
     LoadWindows(395,395,'mailman.settings.php','mailman_addresses=yes&id='+list_name ,'2000',true);
    
    }
function mailman_create_addresses(){
    var id=document.getElementById("id").value
    var domain=document.getElementById("mailman_domain").value
    var XHR = new XHRConnection();
    XHR.appendData('mailman_create_robots_from_domain',domain);
    XHR.appendData('id',id);
    XHR.sendAndLoad(page, 'GET');
     setTimeout("Load_mailman_addresses_list('" + id + "')",1000);
}

function Load_mailman_addresses_list(id){
     LoadAjax(id+'_robots','mailman.settings.php?mailman_addresses_list=yes&id='+id);
    
}
function mailman_modify_robot(email){
    var robot_type=document.getElementById(email+"_mailmanrobottype").value;
    var id=document.getElementById(email+"_id").value;
    var email_new=prompt('',email);
    if(email_new){
        var XHR = new XHRConnection();
        XHR.appendData('id',id);
        XHR.appendData('email_new',email_new);
        XHR.appendData('mailman_change_email_robot',email);
        XHR.appendData('robot_type',robot_type);
        XHR.sendAndLoad(page, 'GET');
        setTimeout("Load_mailman_addresses_list('" + id + "')",1000);
    }
    
}
function mailman_delete_robot(id,email){
    var XHR = new XHRConnection();
     XHR.appendData('id',id);
     XHR.appendData('mailman_delete_robot',email);
     XHR.sendAndLoad(page, 'GET');
     setTimeout("Load_mailman_addresses_list('" + id + "')",1000);     
}
function mailman_add_newlist(){
    var add_mailman_prompt=document.getElementById('add_mailman_prompt').value;
    var MailManListAdminPassword_text=document.getElementById('MailManListAdminPassword_text').value;
    var MailManListAdministrator_text=document.getElementById('MailManListAdministrator_text').value;
    
    var ou=document.getElementById('ou').value;    
    var mailman_list=prompt(add_mailman_prompt);
    var MailManListAdministrator=prompt(MailManListAdministrator_text);
    var MailManListAdminPassword=prompt(MailManListAdminPassword_text);
    
    if (mailman_list){
        var XHR = new XHRConnection();
        XHR.appendData('id',mailman_list);
        XHR.appendData('mailman_add_new_distribution_list',mailman_list);
        XHR.appendData('ou',ou);
        XHR.appendData('MailManListAdministrator',MailManListAdministrator);
        XHR.appendData('MailManListAdminPassword',MailManListAdminPassword);
        XHR.sendAndLoad(page, 'GET');
        setTimeout("LoadMailManList()",1500);
    }
    
}


function mailman_delete_list(id){
    var are_you_sure_to_delete=document.getElementById('are_you_sure_to_delete').value;
    var res=confirm(are_you_sure_to_delete);
    if(res){
        var XHR = new XHRConnection();
        XHR.appendData('id',id);
        XHR.appendData('mailman_delete_distribution_list',id);
         XHR.sendAndLoad(page, 'GET');
        setTimeout("LoadMailManList()",1500);
        
    }
    
    
}


function LoadMailManList(){
    var ou=document.getElementById('ou').value;    
    LoadAjax('mailman_lists','mailman.lists.php?LoadLists=yes&ou='+ ou);
    
}

function LoadMailmanGlobalSettings(){
    LoadWindows(407,305,'mailman.settings.php','gs=yes&id=null','1000',true);   
}

