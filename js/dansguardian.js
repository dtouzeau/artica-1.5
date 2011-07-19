
var hostname_mem;
var rulename_mem;
var rule_main_mem;

var x_sectionrules=function(obj){
      
      if(document.getElementById('rules_lists')){
            LoadAjax('rules_lists','dansguardian.index.php?pop-rules-list=yes');
      }else{
      LoadAjax('squid_main_config','dansguardian.index.php?main=rules&tab=rules&hostname='+ hostname_mem);
      }
}


var x_sectionrules_categoriesWeight=function(obj){
      LoadAjax('main_rules_weightedphraselist_list','dansguardian.index.php?rule_main=' + rule_main_mem + '&tab=categories-weightedphraselist&hostname='+ hostname_mem);
}
var x_sectionrules_categoriesbannedphraselist=function(obj){
      LoadAjax('main_rules_bannedphraselist_list','dansguardian.index.php?rule_main=' + rule_main_mem + '&tab=categories-bannedphraselist&hostname='+ hostname_mem);
}
var x_sectionrules_categoriesexceptionsitelist=function(obj){
      LoadAjax('main_rules_exceptionsitelist_list','dansguardian.index.php?rule_main=' + rule_main_mem + '&tab=ExceptionSiteList-popup&hostname='+ hostname_mem);
}



function dansguardian_addrule(hostname){
 var XHR = new XHRConnection();
      hostname_mem=hostname;
      var dans_add_rule_text=document.getElementById('dans_add_rule_text').value;
        
      XHR.appendData('hostname',hostname);
      var rulename=prompt(dans_add_rule_text);
      if(rulename){
        XHR.appendData('DansGuardian_AddRuleName',rulename);
        XHR.sendAndLoad('dansguardian.index.php', 'GET',x_sectionrules);
      }
        
        
        
}


     
function ApplyDansGuardianSettings(hostname){
    LoadAjax('applysettings','dansguardian.index.php?hostname='+hostname+'&ApplyDansGuardianSettings=yes');
    ChargeLogs();
  
}


var x_SaveDansGuardianTemplate=function(obj){
      YahooWin3(700,'dansguardian.index.php?template=yes','template');
}

function SaveDansGuardianTemplate(){
      var tmpl=document.getElementById('template_content').value;
      var XHR = new XHRConnection();
      XHR.appendData('template_content',tmpl);
      document.getElementById('popup_template').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('dansguardian.index.php', 'POST',x_SaveDansGuardianTemplate);   
}










