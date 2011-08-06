<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(isset($_POST["hostname"])){$_GET["hostname"]=$_POST["hostname"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["check_client_access_multi_add"])){check_client_access_multi_add();exit;}
if(isset($_GET["check_client_access_del"])){check_client_access_del();exit;}
if(isset($_GET["import_headers_regex"])){import_headers_regex();exit;}
if(isset($_GET["edit_postfix_regex_rule"])){echo postfix_regex_rule_edit();exit;}
if(isset($_POST["EditPostfixRegexRule"])){postfix_regex_rule_save();exit;}
if(isset($_GET["PostfixRegexDelete"])){postfix_regex_rule_delete();exit;}
if(isset($_GET["delete_headers_regex"])){postfix_regex_rule_deleteall();exit();}
if(isset($_GET["quick_deny_domains"])){macros_quick_deny_domains();exit;}
if(isset($_GET["postfix_regex"])){echo postfix_regex();exit;}
if(isset($_GET["blockips"])){echo postfix_check_client_access();exit;}

if(isset($_GET["SimpleWords"])){SimpleWords();exit;}
if(isset($_GET["SimpleWords-popup"])){SimpleWords_popup();exit;}
if(isset($_GET["SimpleWords-list"])){SimpleWords_list();exit;}
if(isset($_POST["SimpleWords-save"])){SimpleWords_save();exit;}
if(isset($_GET["SimpleWordsDelete"])){SimpleWords_delete();exit;}
if(isset($_GET["SimpleWordsDisable"])){SimpleWords_disable();exit;}
if(isset($_GET["EnableBodyChecks"])){SimpleWords_EnableBodyChecks();exit;}




if(isset($_GET["ruleform"])){echo rulesdatas();exit;}
if(isset($_GET["rule-reject"])){echo rules_reject();exit;}

if(isset($_GET["ajax"])){echo js();exit;}
if(isset($_GET["load"])){switchpage();exit;}


js();


function js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{global_smtp_rules}');
$html="
var hostname_mem;
var innerMem='';
var wbl;

	function PostfixMultiStartPage(){
		YahooWinS(800,'$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
		//setTimeout(\"LoadMainMultiRegex()\",1000);
	}
	
	function LoadMainMultiRegex(){
		LoadAjax('ruleform','$page?ruleform=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}');
	}
	
	var x_check_client_access_multi_add=function(obj){
      var tempvalue=trim(obj.responseText);
      if(tempvalue.length>3){alert(tempvalue);}
      RefreshTab('main_config_regex');
	}
	
      
     
	var x_import_headers_regex=function(obj){
	     RefreshTab('main_config_regex');
	     }

	function RefreshRegexList(){
	 LoadAjax('postfix_regex','$page?postfix_regex=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}');     
	}
     
     
function sLoadAjax(div,page){
    Set_Cookie('ARTICA-POSTFIX-REGEX-PAGE-DIV',div,'3600', '/', '', '');
    Set_Cookie('ARTICA-POSTFIX-REGEX-PAGE-URI',page,'3600', '/', '', '');      
    LoadAjax(div,page);
    }
    


	function check_client_access_multi_add(){
	      var data=prompt(document.getElementById('blockip_msg').value)
	      if(data){
	           var XHR = new XHRConnection();
	            XHR.appendData('check_client_access_multi_add',data);
	            XHR.appendData('VALUE','REJECT');
	            XHR.appendData('ou','{$_GET["ou"]}');
	    		XHR.appendData('hostname','{$_GET["hostname"]}');
	    		XHR.sendAndLoad('$page', 'GET',x_check_client_access_multi_add);
	      	}
	}
	
	function check_client_access_multi_white_add(){
	      var data=prompt(document.getElementById('blockip_msg').value)
	      if(data){
	           var XHR = new XHRConnection();
	            XHR.appendData('check_client_access_multi_add',data);
	            XHR.appendData('VALUE','OK');
	            XHR.appendData('ou','{$_GET["ou"]}');
	    		XHR.appendData('hostname','{$_GET["hostname"]}');
	    		XHR.sendAndLoad('$page', 'GET',x_check_client_access_multi_add);
	      	}
	}	

	function check_client_access_del(IP){
	     var XHR = new XHRConnection();
	      XHR.appendData('check_client_access_del',IP);
	      XHR.appendData('ou','{$_GET["ou"]}');
	      XHR.appendData('hostname','{$_GET["hostname"]}');
	      XHR.sendAndLoad('$page', 'GET',x_check_client_access_multi_add);       
	}

	function import_headers_regex(){
	    var tx=document.getElementById('import_headers_regex_text').value;
	    if(confirm(tx)){
		      var XHR = new XHRConnection();
		      XHR.appendData('import_headers_regex','yes');
		      BigWait('postfix_regex');
		      XHR.appendData('ou','{$_GET["ou"]}');
		      XHR.appendData('hostname','{$_GET["hostname"]}');      
		      XHR.sendAndLoad('$page', 'GET',x_import_headers_regex);
	      }
	}

	function postfix_regex_page(e){
	      if(checkEnter(e)){
	        Set_Cookie('ARTICA-POSTFIX-REGEX-PAGE', document.getElementById('postfixregexgotopage').value, '3600', '/', '', '');
	        sLoadAjax('postfix_regex','$page?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}&load=pregex&page='+document.getElementById('postfixregexgotopage').value)     
	      }}
	      
	function postfix_regex_search_page(e){
	 if(checkEnter(e)){
	        sLoadAjax('postfix_regex','$page?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}&load=pregex&search='+document.getElementById('postfixregexsearch').value)     
	      }}
      
	function edit_postfix_regex_rule(num){
	      if(!IsNumeric(num)){num='New';}
	      YahooWin(550,'$page?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}&edit_postfix_regex_rule='+num); 
		}
		
	function Cancel1(){
	      var div=Get_Cookie('ARTICA-POSTFIX-REGEX-PAGE-DIV');
	      var uri=Get_Cookie('ARTICA-POSTFIX-REGEX-PAGE-URI');
	      if(!div){
	            RefreshALL();
	            return false;
	      }
	      
	      if(div.length==0){RefreshALL();}else{LoadAjax(div,uri);}
	
	}
	function RefreshALL(){
	      LoadAjax('postfix_regex','$page?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}&load=pregex');
	      LoadAjax('blockips','$page?ou={$_GET["ou"]}&hostname={$_GET["hostname"]}&load=blockips');
	}
	
	var x_MultiEditPostfixRegexRule=function(obj){
	      var tempvalue=trim(obj.responseText);
	      if(tempvalue.length>3){
	           alert(tempvalue);
	           return;     
	        }
		RefreshTab('main_config_regex');
		YahooWinHide();
	}	
	
	
	function MultiEditPostfixRegexRule(id){
	  	  var XHR = new XHRConnection();
	      XHR.appendData('EditPostfixRegexRule',id);
	      XHR.appendData('action',document.getElementById('action').value);
	      XHR.appendData('log',document.getElementById('log').value);
	      XHR.appendData('pattern',document.getElementById('pattern').value);
    	  XHR.appendData('ou','{$_GET["ou"]}');
    	  XHR.appendData('hostname','{$_GET["hostname"]}');	      
	      innerMem=document.getElementById('regexruleform').innerHTML;
	      document.getElementById('regexruleform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\";
	      XHR.sendAndLoad('$page', 'POST',x_MultiEditPostfixRegexRule);       
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
      XHR.appendData('ou','{$_GET["ou"]}');
      XHR.appendData('hostname','{$_GET["hostname"]}');      
      XHR.sendAndLoad('$page', 'GET',x_MultiEditPostfixRegexRule);
      
}
function delete_headers_regex(){
       var tx=document.getElementById('delete_headers_regex_text').value;
    if(confirm(tx)){
      var XHR = new XHRConnection();
      XHR.appendData('delete_headers_regex','yes');
      XHR.appendData('ou','{$_GET["ou"]}');
      XHR.appendData('hostname','{$_GET["hostname"]}');      
      BigWait('postfix_regex');
      XHR.sendAndLoad('$page', 'GET',x_import_headers_regex);
      }
}

function BigWait(id){
    document.getElementById(id).innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/wait_verybig.gif'></center></div>\";  
}

	PostfixMultiStartPage();
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

function popup(){
$page=CurrentPageName();
$hostname=$_GET["hostname"];
$ou=$_GET["ou"];	
$html="
<table style='width:100%'>
<tr>
	<td valign='top' width=1%'>
		<img src='img/bg_regex.png' style='padding:20px;border:1px solid #CCCCCC;margin:5px'>
	</td>
	<td valign='top'>
	<div class=explain>{global_smtp_rules_explain}</div>
	</td>
</tr>
</table>

<div id=main_config_regex style='width:100%;height:550px;overflow:auto'>
<ul>
	<li><a href=\"$page?rule-reject=yes&hostname=$hostname&ou=$ou\"><span>{blockips}</span></a></li>
	<li><a href=\"$page?ruleform=yes&hostname=$hostname&ou=$ou\"><span>{postfix_regex}</span></a></li>
	<li><a href=\"$page?SimpleWords=yes&hostname=$hostname&ou=$ou\"><span>{RegexSimpleWords}</span></a></li>
</ul>
<script>
	 $(document).ready(function() {
		$(\"#main_config_regex\").tabs();});
</script>		
";

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);
	
	
}

function rules_reject(){
$page=CurrentPageName();
$hostname=$_GET["hostname"];
$ou=$_GET["ou"];	
$tpl=new templates();
$new_banserv=Paragraphe('64-bann-server.png','{ADD_BAN_SERVER}','{ADD_BAN_SERVER_TEXT}',"javascript:check_client_access_multi_add()");
$html="
	<input type='hidden' id='blockip_msg' value='{blockip_msg}'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div class=explain>{blockip_text}</div>
		<div id='blockips'>" . postfix_check_client_access(). "</div>
	</td>
	<td valign='top' width=1%>$new_banserv</td>
	</tr>
	</table>

";
echo $tpl->_ENGINE_parse_body($html);	
}


function rulesdatas(){
$new_regex=Paragraphe('acl-add-64.png','{ADD_FILTER_EXPRESSION}','{ADD_FILTER_EXPRESSION_TEXT}',"javascript:edit_postfix_regex_rule()");	
$page=CurrentPageName();
$hostname=$_GET["hostname"];
$ou=$_GET["ou"];		
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div class=explain>{postfix_regex_text}</div>	
		<div id='postfix_regex'>" . postfix_regex(). "</div>
	</td>
	<td valign='top' width=1%>$new_regex</td>
	</tr>
	</table>	
	";
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}




function postfix_check_client_access(){
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$hash=unserialize(base64_decode($main->GET_BIGDATA("check_client_access")));
	
	$html="<div style='width:100%;height:120px;overflow:auto'>
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<tr>
			<td align='right' colspan=4>" . imgtootltip("add-18.gif","{add}","check_client_access_multi_white_add()")."</td>
		</tr>
		";	
	
	if(!is_array($hash)){return $tpl->_ENGINE_parse_body("$html</table><i>{no_rules}</i>");}

	
	while (list ($num, $ligne) = each ($hash) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:16px'>$num</strong></td>
			<td width=1%><strong style='font-size:16px'>$ligne&nbsp;&nbsp;</strong></td>
			<td width=1%>". imgtootltip("delete-32.png",'{delete}',"check_client_access_del('$num')")."</td>
			</tr>
		
		";
		
	}
	$html=$html . "</table></div>";
	return $tpl->_ENGINE_parse_body($html);
}
function check_client_access_multi_add(){
	//REJECT
	$ip=$_GET["check_client_access_multi_add"];
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$check_client_access=unserialize(base64_decode($main->GET_BIGDATA("check_client_access")));
	$check_client_access[$ip]=$_GET["VALUE"];
	$main->SET_BIGDATA("check_client_access",base64_encode(serialize($check_client_access)));
	$sock=new sockets();
	if($_GET["hostname"]=="master"){
		$sock->getFrameWork("cmd.php?postfix-smtpd-restrictions=yes");
	}else{
		$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");
	}	
}
function check_client_access_del(){
	$ip=$_GET["check_client_access_del"];
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$check_client_access=unserialize(base64_decode($main->GET_BIGDATA("check_client_access")));
	unset($check_client_access[$ip]);
	$main->SET_BIGDATA("check_client_access",base64_encode(serialize($check_client_access)));
	$sock=new sockets();
	if($_GET["hostname"]=="master"){
		$sock->getFrameWork("cmd.php?postfix-smtpd-restrictions=yes");
	}else{
		$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");
	}
	
}


function postfix_regex(){
	
	$reg=new main_header_check();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$data=base64_decode($main->GET_BIGDATA("header_check"));
	
	writelogs("rules ". strlen($data)." bytes",__FUNCTION__,__FILE__,__LINE__);
	
	$hash=unserialize($data);
	if(!$hash){
		writelogs("Failed to unserialize datas",__FUNCTION__,__FILE__,__LINE__);
	}else{
		writelogs(count($hash)." rows",__FUNCTION__,__FILE__,__LINE__);
	}
	
	
	$phppage=CurrentPageName();
	
	$start=$_GET["start"];
	$end=$_GET["end"];
	$page_number=round(count($hash)/10);
	
	
	
	if(isset($_GET["search"])){
		$tofind=$_GET["search"];
		$tbl=$hash;unset($hash);
		writelogs("find=$tofind",__FUNCTION__,__FILE__,__LINE__);
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
<table cellspacing='0' cellpadding='0' border='0' style='width:100%'>
	<tr>
	<td nowrap><strong>" .count($hash)." {rules},&nbsp;$page_number {pages}&nbsp;&nbsp;&laquo;{page}&nbsp;{$_GET["page"]}&raquo;</strong></td>
	<td align='right' nowrap><a href=\"javascript:import_headers_regex()\">{import_headers_regex}</a></td>
	<td align='right' width=1%>" . imgtootltip("icon_newest_reply.gif",'{import_headers_regex}',"import_headers_regex()") . "</td>
	<td align='right' nowrap><a href=\"javascript:delete_headers_regex()\">{delete_headers_regex}</a></td>
	<td align='right' width=1%>" . imgtootltip("x.gif",'{delete_headers_regex}',"delete_headers_regex()") . "</td>
	<td align='right'>" . imgtootltip("add-18.gif","{add}","edit_postfix_regex_rule()")."</td>
	</tr>
</table>";	
	

	
	if(is_array($hash)){
		$html=$html."
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
		<tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th><strong>{pattern}</strong></th>
		<th><strong>{action}</strong></th>
		<th><strong>{event}</strong></th>
		<th>&nbsp;</th>
		</tr>
		</thead>
		";
		for($i=$start;$i<=$end;$i++){
			$ligne=$hash[$i];
			if(trim($ligne<>null)){
				$array=$reg->ParseRegexLine($ligne);
				$pattern=$array[0];
				$log=$array[2];
				$pattern=wordwrap($pattern,50,'<br>',1);
				$log=wordwrap($array[2],45,'<br>',1);
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$html=$html."
				<tr  class=$classtr>
					<td width=1% valign='top' style='height:auto'><img src='img/20-reaffect.png'></td>
					<td width=1% valign='top' style='height:auto;font-size:13px'>".texttooltip($i,'{edit}',"edit_postfix_regex_rule($i)")."</td>
					<td nowrap valign='top' style='height:auto;font-size:13px'>" .texttooltip($pattern,'{edit}',"edit_postfix_regex_rule($i)")."</td>
					<td valign='top' style='height:auto;font-size:13px'>{$array[1]}</td>
					<td nowrap valign='top' style='height:auto;font-size:13px'>$log</td>
					<td nowrap valign='top' style='height:auto;font-size:13px'>". imgtootltip("22-delete.png","{delete}","PostfixRegexDelete($i)")."</td>
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
		<td align='left'>" . texttooltip("&laquo;&laquo;","{back}","sLoadAjax('postfix_regex','$phppage?load=pregex&page=$revpage&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')")."</td>
		<td align='left'><input type='text' id='postfixregexgotopage' value='{$_GET["page"]}' OnKeyPress=\"javascript:postfix_regex_page(event);\" style='width:50px'></td>
		<td align='left'>" . texttooltip("&raquo;&raquo;","{next}","sLoadAjax('postfix_regex','$phppage?load=pregex&page=$nextpage&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}')")."</td>
		<td width=1% nowrap><strong>{search_string}</strong>:&nbsp;</td>
		<td align='left'><input type='text' id='postfixregexsearch' value='{$_GET["search"]}' OnKeyPress=\"javascript:postfix_regex_search_page(event);\" style='width:170px'></td>
		</tr>
		</table>";
	}
	
	
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html."<br>" . $toolbox);	
	
}


function import_headers_regex(){
	$main_header=new main_header_check();
	$hash=$main_header->import_examples(true);
	writelogs("{$_GET["hostname"]}:: Import ". count($hash)." rules",__FUNCTION__,__FILE__,__LINE__);
	
	if(is_array($hash)){
		$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
		$main->SET_BIGDATA("header_check",base64_encode(serialize($hash)));
		$sock=new sockets();
		if($_GET["hostname"]=="master"){
			$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
		}else{
			$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");	
		}
				
	}
}
	
function postfix_regex_rule_edit(){
	$main=new main_header_check();
	$main2=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$hash=unserialize(base64_decode($main2->GET_BIGDATA("header_check")));

	$id=$_GET["edit_postfix_regex_rule"];
	$rule=$hash[$id];
	writelogs("parsing $rule",__FUNCTION__,__FILE__);
	$array=$main->ParseRegexLine($rule);
	
	if(preg_match("#^\^(.+?):#s",$array[0],$re)){$macro=$re[1];}
	
	$main->array_headers_values[null]="{select}";
	$headers=Field_array_Hash($main->array_headers_values,'header_regex',$macro,"postfix_regex_form_macro1()",null,0,"font-size:13px;padding:3px");	
	$filedact=Field_array_Hash($main->array_human_actions,'action',$array[1],"style:font-size:13px;padding:3px");
	
	$title="<span style='font-size:16px'>{rule} N.$id &laquo;{$array[1]}&raquo;</span>";
	
	$html="
	<div id='regexruleform'>
	<table style='width:100%'>
		<tr>
			<td nowrap width=1% valign='top' align='right' class='legend'>{action}:</td>
			<td>$filedact</td>
			<td class='legend'>macro:</td>
			<td>$headers</td>
		</tr>
 	</table>
			<table style='width:100%'>
			<tr><td colspan=2><hr></td></tR>
			<tr>
			<td nowrap width=1% valign='top' align='right' class='legend'>{log}:
			</td>
			<td>
				".Field_text('log',$array[2],'width:100%;font-size:14px;padding:4px')."
			</td>
			</tr>	
			
			<tr>
				<td nowrap width=1% valign='top'  align='right' class='legend'>{pattern}:
				</td>
				<td>
					<textarea id='pattern'  style='width:100%;padding:5px;font-size:13px' rows=5>{$array[0]}</textarea>
				</td>
			</tr>
			
			
			
			<tr>
			
			<td align='right' colspan=2>". button("{edit}","MultiEditPostfixRegexRule('$id')")."</td>
			</tr>
			</table>
			</div>
	";
	
	
$tpl=new templates();
$body=$tpl->_ENGINE_parse_body($html);
$title=$tpl->_ENGINE_parse_body($title);
	return "$title<br>$body";
	
	
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
	$main=new maincf_multi($_POST["hostname"],$_POST["ou"]);
	$data=unserialize(base64_decode($main->GET_BIGDATA("header_check")));
	if(!is_array($data)){$data=array();}
	if($id=="New"){$data[]=$newrule;}else{$data[$id]=$newrule;}
	
	$serialized=serialize($data);
	//writelogs("{$_POST["hostname"]}:: $serialized",__FUNCTION__,__FILE__,__LINE__);
	$main->SET_BIGDATA("header_check",base64_encode(serialize($data)));	
	$sock=new sockets();
	if($_GET["hostname"]=="master"){
		$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
	}else{
		$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");	
	}
}
function postfix_regex_rule_delete(){
	$id=$_GET["PostfixRegexDelete"];
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$data=unserialize(base64_decode($main->GET_BIGDATA("header_check")));
	unset($data[$id]);
	if(!is_array($data)){$data=array();}
	$main->SET_BIGDATA("header_check",base64_encode(serialize($data)));			
	$sock=new sockets();
	if($_GET["hostname"]=="master"){
		$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
	}else{
		$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");	
	}
	}
function postfix_regex_rule_deleteall(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$main->SET_BIGDATA("header_check","-");	
	$sock=new sockets();
	if($_GET["hostname"]=="master"){
		$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
	}else{
		$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");	
	}
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
			if($_GET["hostname"]=="master"){
				$sock->getFrameWork("cmd.php?headers-check-postfix=yes");
			}else{
				$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");	
			}		
			
		}else{
			writelogs("Domain to block: {$table_pattern[$i]} failed",__FUNCTION__,__FILE__);
		}
	}
}


function SimpleWords_EnableBodyChecks(){
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	if(!$main->SET_VALUE("EnableBodyChecks",$_GET["EnableBodyChecks"])){return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-body-checks={$_GET["hostname"]}");
	
}

function SimpleWords(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add=$tpl->_ENGINE_parse_body("{add}");
	$RegexSimpleWords=$tpl->_ENGINE_parse_body("{RegexSimpleWords}");
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$EnableBodyChecks=$main->GET("EnableBodyChecks");
	if($EnableBodyChecks==null){$EnableBodyChecks=1;}
	$tpl=new templates();
	$page=CurrentPageName();
	$enable=Paragraphe_switch_img("{enable_body_checks}","{SimpleWords_explain}",
	"EnableBodyChecks",$EnableBodyChecks,null,600);	
	
	
	$html="
	
		$enable<hr>
		<div style='text-align:right;width=100%'>". button("{apply}","SaveEnableBodyChecks()")."</div>
	

	
	<center>
	<table class=form>
	<tr>
		<td class=legend>{search}:</td>
		<td>". Field_text("SimpleWordsSearch",null,"font-size:14px;padding:3px;width:450px","script:SimpleWordsSearchSearchEnter(event)")."</td>
		<td>".imgtootltip("plus-24.png","{add} {words}","SimpleWordsAddForm()")."</td>
	</tr>
	</table>
	</center>
	<div id='SimpleWords-list'></div>
	
	<script>
		function SimpleWordsAddForm(){
			YahooWin4('550','$page?SimpleWords-popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{$_GET["hostname"]}::$add::$RegexSimpleWords');
		
		}
		
		function SimpleWordsSearchSearchEnter(e){
			if(checkEnter(e)){SimpleWordsRefresh();}
		}
		
		function SimpleWordsRefresh(){
			var se=escape(document.getElementById('SimpleWordsSearch').value);
			LoadAjax('SimpleWords-list','$page?SimpleWords-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}&search='+se);
		}
		

		
	var x_SaveEnableBodyChecks= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		RefreshTab('main_config_regex');
	}			
		
		function SaveEnableBodyChecks(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableBodyChecks',document.getElementById('EnableBodyChecks').value);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			document.getElementById('SimpleWords-list').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveEnableBodyChecks);			
		}
		
		
		SimpleWordsRefresh();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SimpleWords_popup(){
	$tpl=new templates();
	$page=CurrentPageName();

	$html="
	<div id='simplewords-smtp-div'>
	<div class=explain>{SimpleWords_explain_add}</div>
	<textarea id='simplewords-servers-container' style='width:100%;height:450px;overflow:auto;font-size:14px'></textarea>
	<div style='text-align:right'>". button("{add}","SimpleWordsSave()")."</div>
	</div>
	<script>
	
	var x_SimpleWordsSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		YahooWin4Hide();
		SimpleWordsRefresh();
	}			
		
	function SimpleWordsSave(){
		var XHR = new XHRConnection();
		XHR.appendData('SimpleWords-save',document.getElementById('simplewords-servers-container').value);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		document.getElementById('simplewords-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_SimpleWordsSave);		
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function SimpleWords_save(){
	
	$hostname=$_POST["hostname"];
	$datas=explode("\n",$_POST["SimpleWords-save"]);
	$prefix="INSERT INTO postfix_regex_words (words,hostname) VALUES ";
	
	if(!is_array($datas)){echo "No data";return;}
	while (list ($num, $words) = each ($datas) ){	
		if(trim($words)==null){continue;}
		$words=addslashes($words);
		$ws[]="('$words','$hostname')";
	}
	
	$q=new mysql();
	$q->BuildTables();
	$sql=$prefix.@implode(",",$ws);
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-body-checks={$_GET["hostname"]}");	
	
}

function SimpleWords_delete(){
	if(!is_numeric($_GET["SimpleWordsDelete"])){return null;}
	$sql="DELETE FROM postfix_regex_words WHERE ID='{$_GET["SimpleWordsDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-body-checks={$_GET["hostname"]}");	
}

function SimpleWords_disable(){
	if(!is_numeric($_GET["ID"])){return null;}
	$sql="UPDATE postfix_regex_words SET enabled='{$_GET["SimpleWordsDisable"]}' WHERE ID='{$_GET["ID"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-body-checks={$_GET["hostname"]}");
}

function SimpleWords_list(){
	
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$EnableBodyChecks=$main->GET("EnableBodyChecks");
	if($EnableBodyChecks==null){$EnableBodyChecks=1;}	
	
	$page=CurrentPageName();
	$se="%{$_GET["search"]}%";
	$se=str_replace("*","%",$se);
	$se=str_replace("%%","%",$se);
	
	
	$sql="SELECT * FROM postfix_regex_words WHERE `hostname`='{$_GET["hostname"]}' AND `words` LIKE '$se' ORDER BY words LIMIT 0,100";
	$tpl=new templates();
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{rule}</th>
		<th>{words}</th>
		<th>{enabled}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		
		$disable=Field_checkbox("enabled_{$ligne["ID"]}",1,$ligne["enabled"],"SimpleWordsDisable('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","SimpleWordsDelete('{$ligne["ID"]}')");
		$color="black";
		if($EnableBodyChecks==0){$color="#A8A5A5";}		
		
		$icon="datasource-32.png";
		
		$html=$html . "
		<tr  class=$classtr>
		<td width=1%><img src='img/$icon'></td>
		<td width=1%><strong style='font-size:14px'>{$ligne["ID"]}</td>
		<td><strong style='font-size:14px'><code style='color:$color'>{$ligne["words"]}</code></td>
		<td width=1% align='center'>$disable</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
	$html=$html."</tbody></table>
	
	
	<script>
	var x_SimpleWordsDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		SimpleWordsRefresh();
	}	
	
	var x_SimpleWordsDisable= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		
	}		
	
	function SimpleWordsDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('SimpleWordsDelete',key);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		document.getElementById('SimpleWords-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SimpleWordsDelete);
		}	
		
	function SimpleWordsDisable(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('ou','{$_GET["ou"]}');		
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('SimpleWordsDisable',1);}else{XHR.appendData('SimpleWordsDisable',0);}
		XHR.sendAndLoad('$page', 'GET',x_SimpleWordsDisable);
	}
			
	
	</script>";
	
		
	echo $tpl->_ENGINE_parse_body($html);	
	
}



?>