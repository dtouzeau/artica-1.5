<?php
//dansguardian.exception.sites.php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	
	if(!IsDansGuardianrights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["exceptionsitelist_add"])){main_rules_exceptionsitelist_add();exit;}
	if(isset($_GET["exceptionsitelist_del"])){main_rules_exceptionsitelist_del();exit;}	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=$dansg->Master_rules_index[$rule_main];			
	$title=$tpl->_ENGINE_parse_body("{ExceptionSiteList}");
	$html="
	
	function DANSGUARDIAN_LOAD_EXCEPTIONSITE(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title::$rulename');
	
	}
	
	var x_sectionrules_categoriesexceptionsitelist=function(obj){
	      DANSGUARDIAN_LOAD_EXCEPTIONSITE();
	}	
	
function exceptionsitelist_add(hostname,rule_main){
          
      var site=document.getElementById('except_uri').value;
      if(site.length>0){
      		var XHR = new XHRConnection();
     	 	XHR.appendData('exceptionsitelist_add',site);
      		XHR.appendData('rule_main','{$_GET["rule_main"]}');
      		document.getElementById('main_rules_exceptionsitelist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
      		XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesexceptionsitelist);  
			}       
      }
      
function exceptionsitelist_del(hostname,rule_main,domain){
  var XHR = new XHRConnection();
      rule_main_mem='{$_GET["rule_main"]}';      
      XHR.appendData('exceptionsitelist_del',domain);
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      document.getElementById('main_rules_exceptionsitelist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
      XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesexceptionsitelist);      
      }  

function exceptionsitelist_enter(e){
	if(checkEnter(e)){exceptionsitelist_add();}
}
	
	DANSGUARDIAN_LOAD_EXCEPTIONSITE()";
	
	echo $html;
	
}

function popup(){
		$users=new usersMenus();
		
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=$dansg->Master_rules_index[$rule_main];		

	writelogs("Loading default rule $rule_main...",__FUNCTION__,__FILE__);
	$rules=main_rules_exceptionsitelist_list($rule_main,1);
	$apply=applysettings_dansguardian();
	
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<div class=explain>{ExceptionSiteList_explain}</div>
			<center>
			<table style='width:80%' class=form>
					<tr>
					<td>" . Field_text('except_uri',null,'font-size:13px;padding:3px',null,null,null,false,"exceptionsitelist_enter(event)")."</td>
					<td>
					<td width=1%>". imgtootltip("plus-24.png","{add}","exceptionsitelist_add()")."
					</td>
					</tr>
			</table>
			</center>
		
	<br>
	<div id='main_rules_exceptionsitelist_list' style='width:100%;height:300px;overflow:auto'>
	$rules
	</div>
	";	


	$tpl=new templates();
	echo $tpl->_parse_body("$html<br>$categ<br>");
}
function main_rules_exceptionsitelist_list($rule_main,$noecho=0){
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
		$sql="SELECT * FROM dansguardian_files WHERE filename='exceptionsitelist' AND RuleID=$rule_main ORDER BY ID DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$style=CellRollOver();
	$categ="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=99% colspan=2>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$num=$ligne["ID"];
		$pattern=$ligne["pattern"];
	$categ=$categ . 
		"<tr class=$classtr>
		<td width=99%><strong style='font-size:16px'>$pattern</strong></td>
		<td width=1%>" . imgtootltip('delete-32.png','{delete}',"exceptionsitelist_del('$hostname','$rule_main','$num')") ."</td>
		</tr>
		";
		}
	$categ=$categ . "</table>";
	
	
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$categ");}
	echo $tpl->_ENGINE_parse_body("$categ");		
	$tpl=new templates();

}
function main_rules_exceptionsitelist_add(){
	$rule_main=$_GET["rule_main"];	
	$www=$_GET["exceptionsitelist_add"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->Add_exceptionsitelist($rule_main,$www);
	}
	function main_rules_exceptionsitelist_del(){
	$rule_main=$_GET["rule_main"];	
	$www=$_GET["exceptionsitelist_del"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->DelExceptionsitelist($www);	
	
}	
?>