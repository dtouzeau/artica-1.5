<?php
session_start();
include_once('ressources/class.templates.inc');
include_once("ressources/class.main_cf_filtering.inc");
$users=new usersMenus();
if($users->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
if(isset($_GET["check_client_access_add"])){check_client_access_add();exit;}
if(isset($_GET["check_client_access_del"])){check_client_access_del();exit;}
if(isset($_GET["import_headers_regex"])){import_headers_regex();exit;}
if(isset($_GET["edit_postfix_regex_rule"])){echo postfix_regex_rule_edit();exit;}
if(isset($_POST["EditPostfixRegexRule"])){postfix_regex_rule_save();exit;}
if(isset($_GET["PostfixRegexDelete"])){postfix_regex_rule_delete();exit;}
if(isset($_GET["delete_headers_regex"])){postfix_regex_rule_deleteall();exit();}
if(isset($_GET["quick_deny_domains"])){macros_quick_deny_domains();exit;}
if(isset($_GET["postfix_regex"])){echo postfix_regex();exit;}
if(isset($_GET["blockips"])){echo postfix_check_client_access();exit;}
if(isset($_GET["ruleform"])){echo rulesdatas();exit;}
if(isset($_GET["ajax"])){echo js();exit;}
if(isset($_GET["load"])){switchpage();exit;}
if(isset($_GET["ajax-page"])){echo mainpage();exit;}

page();


function js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{global_smtp_rules}');
$html="
var hostname_mem;
var innerMem='';
var wbl;

	function StartPage(){
		YahooWinS(800,'$page?ajax-page=yes','$title');
		setTimeout(\"LoadMainRegex()\",1000);
	}
	
	function LoadMainRegex(){
		LoadAjax('ruleform','$page?ruleform=yes');
	}
	
	var x_check_client_access_add=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){
                alert(tempvalue);
      }
      LoadAjax('blockips','smtp.rules.php?blockips=yes');
	}
      
     
var x_import_headers_regex=function(obj){
      RefreshRegexList();
     }
     


function RefreshRegexList(){
 LoadAjax('postfix_regex','smtp.rules.php?postfix_regex=yes');     
}
     
     
function sLoadAjax(div,page){
    Set_Cookie('ARTICA-POSTFIX-REGEX-PAGE-DIV',div,'3600', '/', '', '');
    Set_Cookie('ARTICA-POSTFIX-REGEX-PAGE-URI',page,'3600', '/', '', '');      
    LoadAjax(div,page);
    }
    


function check_client_access_add(){
      
      var data=prompt(document.getElementById('blockip_msg').value)
      if(data){
           var XHR = new XHRConnection();
            XHR.appendData('check_client_access_add',data);
            XHR.sendAndLoad('smtp.rules.php', 'GET',x_check_client_access_add);
      }
}

function check_client_access_del(IP){
     var XHR = new XHRConnection();
      XHR.appendData('check_client_access_del',IP);
      XHR.sendAndLoad('smtp.rules.php', 'GET',x_check_client_access_add);      
}

function import_headers_regex(){
    var tx=document.getElementById('import_headers_regex_text').value;
    if(confirm(tx)){
      var XHR = new XHRConnection();
      XHR.appendData('import_headers_regex','yes');
      BigWait('postfix_regex');
      XHR.sendAndLoad('smtp.rules.php', 'GET',x_import_headers_regex);
      }
}

function postfix_regex_page(e){
      if(checkEnter(e)){
        Set_Cookie('ARTICA-POSTFIX-REGEX-PAGE', document.getElementById('postfixregexgotopage').value, '3600', '/', '', '');
        sLoadAjax('postfix_regex','smtp.rules.php?load=pregex&page='+document.getElementById('postfixregexgotopage').value)     
      }}
function postfix_regex_search_page(e){
 if(checkEnter(e)){
        sLoadAjax('postfix_regex','smtp.rules.php?load=pregex&search='+document.getElementById('postfixregexsearch').value)     
      }}
      
function edit_postfix_regex_rule(num){
      if(!IsNumeric(num)){num='New';}
      YahooWin(550,'smtp.rules.php?edit_postfix_regex_rule='+num); 
}
function Cancel1(){
      var div=Get_Cookie('ARTICA-POSTFIX-REGEX-PAGE-DIV');
      var uri=Get_Cookie('ARTICA-POSTFIX-REGEX-PAGE-URI');
      if(!div){
            RefreshALL();
            return false;
      }
      
      if(div.length==0){RefreshALL();}else{
          LoadAjax(div,uri);  
      }

}
	function RefreshALL(){
	      LoadAjax('postfix_regex','smtp.rules.php?load=pregex');
	      LoadAjax('blockips','smtp.rules.php?load=blockips');
	}
	
	var x_EditPostfixRegexRule=function(obj){
	      var tempvalue=trim(obj.responseText);
	      if(tempvalue.length>3){
	           alert(tempvalue);
	           document.getElementById('regexruleform').innerHTML=innerMem;
	           return;     
	        }
		RefreshRegexList();
		YahooWinHide();
	}	
	
	
	function EditPostfixRegexRule(id){
	  	  var XHR = new XHRConnection();
	      XHR.appendData('EditPostfixRegexRule',id);
	      XHR.appendData('action',document.getElementById('action').value);
	      XHR.appendData('log',document.getElementById('log').value);
	      XHR.appendData('pattern',document.getElementById('pattern').value);
	      innerMem=document.getElementById('regexruleform').innerHTML;
	      document.getElementById('regexruleform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\";
	      XHR.sendAndLoad('smtp.rules.php', 'POST',x_EditPostfixRegexRule);       
	}

	function postfix_regex_form_macro1(){
		 var header_regex;
		 header_regex=document.getElementById('header_regex').value;
		 
		 var pattern=document.getElementById('pattern').value;
		 if(header_regex.length>0){
		      header_regex='^'+header_regex+': ';
		      pattern=header_regex+pattern;
		      document.getElementById('pattern').value=pattern;
		 }
	}

function PostfixRegexDelete(num){
      var XHR = new XHRConnection();
      XHR.appendData('PostfixRegexDelete',num);
      XHR.sendAndLoad('smtp.rules.php', 'GET',x_EditPostfixRegexRule);
      
}
function delete_headers_regex(){
       var tx=document.getElementById('delete_headers_regex_text').value;
    if(confirm(tx)){
      var XHR = new XHRConnection();
      XHR.appendData('delete_headers_regex','yes');
      BigWait('postfix_regex');
      XHR.sendAndLoad('smtp.rules.php', 'GET',x_import_headers_regex);
      }
}

function BigWait(id){
    document.getElementById(id).innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\";  
}

	StartPage();
";  
      
      
      
      
  
      
      


	
 
	echo $html;
	
}


function switchpage(){

		switch ($_GET["load"]) {
		case "blockips":echo postfix_check_client_access();exit;break;
		case "black":echo BlackList();exit;break;
		case "pregex":echo postfix_regex();exit();break;
		case "postfix_regex":
			writelogs("load={$_GET["load"]} --> postfix_regex()",__FUNCTION__,__FILE__);
			echo postfix_regex();exit;break;
		}
	
}


function page(){
$page=CurrentPageName();	
$content=mainpage();
$html=
"$content
<script>
LoadAjax('ruleform','$page?ruleform=yes');
</script>
";// rulesdatas()


$cfg["JS"][]="js/smtprules.js";
$tpl=new template_users('{global_smtp_rules}',$html,0,0,0,0,$cfg);	
echo $tpl->web_page;

}



function mainpage(){
$new_banserv=Paragraphe('64-bann-server.png','{ADD_BAN_SERVER}','{ADD_BAN_SERVER_TEXT}',"javascript:check_client_access_add()");
$new_regex=Paragraphe('acl-add-64.png','{ADD_FILTER_EXPRESSION}','{ADD_FILTER_EXPRESSION_TEXT}',"javascript:edit_postfix_regex_rule()");	
$html="
<table style='width:100%'>
<tr>
	<td valign='top' width=1%'>
		<img src='img/bg_regex.png' style='padding:20px;border:1px solid #CCCCCC;margin:5px'>
	</td>
	<td valign='top'><div style='float:right'" . applysettings_postfix()."</div>
	<div class=explain>{global_smtp_rules_explain}</div>
	</td>
</tr>
</table>
<table style='width:100%'>
	<td valign='top' width=99%'>
		<div id='ruleform'></div>
	</td>
	<td valign='top' width=1%>".RoundedLightWhite("
	$new_banserv<br><br>
	$new_regex")."
	</td>


</table>
<br>";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
	
}


function rulesdatas(){
	
	//$main=new main_header_check();
	//$fields=Field_array_Hash($main->array_headers_values,'fields_headers',$rule["fields_headers"]);
	
	$html="
	<input type='hidden' id='blockip_msg' value='{blockip_msg}'>
	<hr><H6>{blockips}</H6>
	<div class=explain>{blockip_text}</div>
		<div id='blockips'>" . postfix_check_client_access(). "</div>
<hr><H6>{postfix_regex}</H6>
	<div class=explain>{postfix_regex_text}</div>	
		<div id='postfix_regex'>" . postfix_regex(). "</div>
	";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}




function postfix_check_client_access(){
	$ldap=new clladp();
	$tpl=new templates();
	if(!$ldap->ExistsDN("cn=check_client_access,cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix")){
		return null;
	}
	
	$hash=$ldap->Hash_get_restrictions_classes_tables("smtpd_client_restrictions","check_client_access");
	
	$html="<table style='width:100%'>
	<tr><td align='right' colspan=3>" . imgtootltip("add-18.gif","{add}","check_client_access_add()")."</td></tr>";	
	
	if(!is_array($hash)){return $tpl->_ENGINE_parse_body(RoundedLightGrey("$html</table><i>{no_rules}</i>"));}

	
	while (list ($num, $ligne) = each ($hash) ){
		$html=$html."<tr " . CellRollOver_jaune().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$num</strong></td>
		<td width=1%>". imgtootltip("x.gif",'{delete}',"check_client_access_del('$num')")."</td>
		</tr>
		
		";
		
	}
	$html=$html . "</table>";
	
	
	return RoundedLightGrey($tpl->_ENGINE_parse_body($html));
	
	
	
}
function check_client_access_add(){
	$ip=$_GET["check_client_access_add"];
	$tpl=new templates();
	$ldap=new clladp();
	if(!$ldap->ExistsDN("cn=restrictions_classes,cn=artica,$ldap->suffix")){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixStructuralClass';
		$upd["cn"]="restrictions_classes";
		$ldap->ldap_add("cn=restrictions_classes,cn=artica,$ldap->suffix",$upd);
		unset($upd);
		}
	if(!$ldap->ExistsDN("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix")){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixRestrictionStandardClasses';
		$upd["PostFixRestrictionClassDescription"][]="bann ip addresses";
		$upd["PostFixRestrictionClassList"][]='check_client_access="ldap"';
		$upd["cn"]="smtpd_client_restrictions";
		$ldap->ldap_add("cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix",$upd);
		unset($upd);
		}		

 	$dn="cn=check_client_access,cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixStructuralClass';
		$upd["cn"]="check_client_access";
		$ldap->ldap_add($dn,$upd);
		unset($upd);
		}		 

	
	$dn="cn=$ip,cn=check_client_access,cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='PostFixRestrictionCheckAccess';
		$upd["PostFixRestrictionTableAction"][]='REJECT';
		$upd["cn"]="$ip";
		if(!$ldap->ldap_add($dn,$upd)){echo "$ip\n$ldap->ldap_last_error\n";}else{
			echo $tpl->_ENGINE_parse_body("$ip:{success}");
		}
		unset($upd);
		}	
}
function check_client_access_del(){
$ldap=new clladp();
$tpl=new templates();
$dn="cn={$_GET["check_client_access_del"]},cn=check_client_access,cn=smtpd_client_restrictions,cn=restrictions_classes,cn=artica,$ldap->suffix";	
if(!$ldap->ldap_delete($dn,true)){
	echo $ldap->ldap_last_error;
}else{
	echo $tpl->_ENGINE_parse_body("{success}:{delete} {rule}:{$_GET["check_client_access_del"]}");
}
}


function postfix_regex(){
	$reg=new main_header_check();
	$hash=$reg->main_table;	
	$start=$_GET["start"];
	$end=$_GET["end"];
	$page_number=round(count($hash)/10);
	
	
	
	if(isset($_GET["search"])){
		$tofind=$_GET["search"];
		$tbl=$hash;unset($hash);
		
		while (list ($num, $ligne) = each ($tbl)){
			if(preg_match("#$tofind#",$ligne)){
				$hash[$num]=$ligne;
				$end=$num;
			}
			
		}
		
	}
	
	

	if(!is_numeric($start)){$start=0;}
	if(!is_numeric($end)){$end=10;}	
	
	if($_GET["search"]==null){
	if(!is_numeric($_GET["page"])){
		if(isset($_COOKIE["ARTICA-POSTFIX-REGEX-PAGE"])){
			$_GET["page"]=$_COOKIE["ARTICA-POSTFIX-REGEX-PAGE"];
		}
	}}
	
	if(is_numeric($_GET["page"])){
		$start=$_GET["page"]*10;
		$end=$start+10;
	}
	
	
	$html="
	<input type='hidden' id='delete_headers_regex_text' value='{delete_headers_regex_text}'>
	<input type='hidden' id='import_headers_regex_text' value='{import_headers_regex_text}'>
	<table style='width:100%'>
	<tr>
	<td><strong>" .count($hash)." {rules},&nbsp;$page_number {pages}&nbsp;&nbsp;&laquo;{page}&nbsp;{$_GET["page"]}&raquo;</strong></td>
	<td align='right'>
		<a href=\"javascript:import_headers_regex()\">
		{import_headers_regex}</a>&nbsp;" . imgtootltip("icon_newest_reply.gif",'{import_headers_regex}',"import_headers_regex()") . "
	</td>
	<td align='right'>
		<a href=\"javascript:delete_headers_regex()\">
		{delete_headers_regex}</a>&nbsp;" . imgtootltip("x.gif",'{delete_headers_regex}',"delete_headers_regex()") . "
	</td>
		
	

	<td align='right'>" . imgtootltip("add-18.gif","{add}","edit_postfix_regex_rule()")."</td></tr>
	</table>";	
	
	
	

	
	
	if(is_array($hash)){
		$html=$html."
		<table style width:99%' class=table_form>
		<tr style='background-color:#CCCCCC'>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><strong>{pattern}</strong></td>
		<td><strong>{action}</strong></td>
		<td><strong>{event}</strong></td>
		<td>&nbsp;</td>
		</tr>
		
		";
		for($i=$start;$i<=$end;$i++){
			$ligne=$hash[$i];
			if(trim($ligne<>null)){
				$array=$reg->ParseRegexLine($ligne);
				$pattern=$array[0];
				$log=$array[2];
				$pattern=wordwrap($pattern,50,'<br>',1);
				$log=wordwrap($array[2],45,'<br>',1);
				$html=$html."
				<tr "  .CellRollOver_jaune().">
					<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
					<td width=1% valign='top'>".texttooltip($i,'{edit}',"edit_postfix_regex_rule($i)")."</td>
					<td nowrap valign='top'>" .texttooltip($pattern,'{edit}',"edit_postfix_regex_rule($i)")."</td>
					<td valign='top'>{$array[1]}</td>
					<td nowrap valign='top'>$log</td>
					<td nowrap valign='top'>". imgtootltip("x.gif","{delete}","PostfixRegexDelete($i)")."</td>
				</tr>
			";
			
		}
	}
	}
		
	
	$html=$html."</table>";
	
	
	
	
	if($page_number>1){
		$nextpage=$_GET["page"]+1;
		$revpage=$_GET["page"]-1;
		if($nextpage>$page_number){$nextpage=$page_number;}
		if($revpage<0){$revpage=0;}
		
		$toolbox="<br><table style='width:100%'>
		<tr>
		<td width=1% nowrap><strong>{go_to_page}</strong>:&nbsp;</td>
		<td align='left'>
			" . texttooltip("&laquo;&laquo;","{backward}","sLoadAjax('postfix_regex','smtp.rules.php?load=pregex&page=$revpage')")."&nbsp;
		<input type='text' id='postfixregexgotopage' value='{$_GET["page"]}' OnKeyPress=\"javascript:postfix_regex_page(event);\" style='width:50px'>
		&nbsp;" . texttooltip("&raquo;&raquo;","{forward}","sLoadAjax('postfix_regex','smtp.rules.php?load=pregex&page=$nextpage')")."&nbsp;
		</td>
		<td width=1% nowrap><strong>{search_string}</strong>:&nbsp;</td>
		<td align='left'><input type='text' id='postfixregexsearch' value='{$_GET["search"]}' OnKeyPress=\"javascript:postfix_regex_search_page(event);\" style='width:170px'></td>
		</tr>
		</table>";
	}
	
	
	
	
	$tpl=new templates();
	
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html)."<br>" . $toolbox) ;	
	
}


function import_headers_regex(){
	$main=new main_header_check();
	$main->import_examples();
	}
	
function postfix_regex_rule_edit(){
	$main=new main_header_check();
	$hash=$main->main_table;	
	$headers=Field_array_Hash($main->array_headers_values,'header_regex',null,"postfix_regex_form_macro1()");
	$id=$_GET["edit_postfix_regex_rule"];
	$rule=$hash[$id];
	writelogs("parsing $rule",__FUNCTION__,__FILE__);
	$array=$main->ParseRegexLine($rule);
	
	$filedact=Field_array_Hash($main->array_human_actions,'action',$array[1]);
	
	$title="<H1>{rule} N.$id &laquo;{$array[1]}&raquo;</H1>";
	
	$html="
	<div id='regexruleform'>
	<table style='width:100%'>
		<tr>
			<td nowrap width=1% valign='top' align='right' class='legend'>{action}:</td>
			<td>
				<table style='width:100%'>
					<tr>
					<td>$filedact</td>
					<td class='legend'>macro:&nbsp;$headers</td>
					</tr>
				</table>
			</td>	
			<tr><td colspan=2><hr></td></tR>
			<tr>
			<td nowrap width=1% valign='top' align='right' class='legend'>{log}:
			</td>
			<td>
				".Field_text('log',$array[2],'width:100%')."
			</td>
			</tr>	
			
			<tr>
				<td nowrap width=1% valign='top'  align='right' class='legend'>{pattern}:
				</td>
				<td>
					<textarea id='pattern'  style='width:100%' rows=5>{$array[0]}</textarea>
				</td>
			</tr>
			
			
			
			<tr>
			
			<td align='right' colspan=2 class=legend><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:EditPostfixRegexRule('$id');\">
			</table>
			</div>
	";
	
	
$tpl=new templates();
$body=$tpl->_ENGINE_parse_body($html);
$title=$tpl->_ENGINE_parse_body($title);
	return "$title<br>". RoundedLightWhite("$body")	;
	
	
}

function postfix_regex_rule_save(){
	$id=$_POST["EditPostfixRegexRule"];
	$action=$_POST["action"];
	$log=$_POST["log"];
	$pattern=$_POST["pattern"];
	$tpl=new templates();
	if(trim($action)==null){
		echo $tpl->javascript_parse_text("{error_noaction}");exit;
	}
	if(trim($log)==null){
		echo $tpl->javascript_parse_text("{error_noevent}");exit;
	}
	if(trim($pattern)==null){
		echo $tpl->javascript_parse_text("{error_nopattern}");exit;
	}			
	$pattern=trim(stripslashes($pattern));
	$newrule="/$pattern/\t$action\t$log";
	
	$main=new main_header_check();
	$main->saverule($newrule,$id);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
	}
function postfix_regex_rule_delete(){
	$id=$_GET["PostfixRegexDelete"];
	$main=new main_header_check();
	$main->delete_rule($id);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?headers-check-postfix=yes");		
	}
function postfix_regex_rule_deleteall(){
	$main=new main_header_check();
	$main->delete_all_rules();	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?headers-check-postfix=yes");	
	
}

function macros_quick_deny_domains(){
	$pattern=$_GET["quick_deny_domains"];
	$table_pattern=explode(',',$pattern);
	$main=new main_header_check();
	$tpl=new templates();
	writelogs("Domain to block:".count($table_pattern),__FUNCTION__,__FILE__);
	for($i=0;$i<count($table_pattern);$i++){
		$new_pattern=$table_pattern[$i];
		$new_pattern=$main->TransformToregex($new_pattern);
		$new_pattern="/^From:.*\@$new_pattern/\tREJECT\tBANNED DOMAIN";
		
		if($main->saverule($new_pattern,null)){
			writelogs("Domain to block: {$table_pattern[$i]} success",__FUNCTION__,__FILE__);
			echo $tpl->_ENGINE_parse_body("{success}:{rule} \"ban '{$table_pattern[$i]}'\"\n{goto_globalsmtprules}");
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?headers-check-postfix=yes");			
			
		}else{
			writelogs("Domain to block: {$table_pattern[$i]} failed",__FUNCTION__,__FILE__);
		}
	}
}



?>