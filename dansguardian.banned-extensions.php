<?php
//dansguardian.banned-extensions.php
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
	if(isset($_GET["bannedextension_listadd"])){main_extension_bannedextensionlist_add();exit;}
	if(isset($_GET["bannedextensionlist_switch"])){main_extension_bannedextensionlist_switch();exit;}
	if(isset($_GET["bannedextensionlist_del"])){main_extention_bannedextensionlist_del();exit;}
	if(isset($_GET["main_extensions_bannedextensionslist_list"])){echo main_extensions_bannedextensionslist_list();exit;}
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{ExceptionSiteList}");
	$html="
	
	function DANSGUARDIAN_LOAD_BANNEDEXT(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
	function ext_enter(e){
		if(checkEnter(e)){bannedextension_listadd();}
	}
	
	DANSGUARDIAN_LOAD_BANNEDEXT()";
	
	echo $html;
	
}


function popup(){
	$page=CurrentPageName();	
$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$rule_main=$_GET["rule_main"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dansg=new dansguardian($_GET["hostname"]);
	$rulename=$dansg->Master_rules_index[$rule_main];
	
		
		
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<p class=caption>{bannedextensionlist_explain}</p>
	<table style='width:100%'>
	<tr>
	<td class=legend>{extension}:</strong></td>
	<td>" . Field_text('extension_pattern',null,'width:100px',null,null,null,false,"ext_enter(event)")."</td>
	<td class=legend>{description}:</strong></td>
	<td>" . Field_text('extension_description')."</td>
	<td>". imgtootltip("plus-24.png","{add_extension}","bannedextension_listadd()")."
	
	</td>
	</tr>
	</table><br>
	<div id='main_extensions_bannedextensionslist_list' style='width:100%;height:300px;overflow:auto'></div>
	
	<script>
	function RefreshBannedExtensionList(){
		LoadAjax('main_extensions_bannedextensionslist_list','$page?rule_main=$rule_main&main_extensions_bannedextensionslist_list=yes');
	}
	
	var x_sectionrules_categoriesbannedextensionlist=function(obj){
	var results=obj.responseText;
	if(results.length>0){alert(results);}
		RefreshBannedExtensionList();
    }

	var x_bannedextensionlist_switch=function(obj){
		var results=obj.responseText;
		if(results.length>0){alert(results);}
    }	    
	
	 function bannedextensionlist_del(hostname,rule_main,index){
      	document.getElementById('image_' +index).innerHTML='';
      	document.getElementById('pattern_' + index).innerHTML='';
      	document.getElementById('info_' + index).innerHTML='';
      	var XHR = new XHRConnection();
      	XHR.appendData('bannedextensionlist_del',index);
      	XHR.appendData('rule_main',rule_main);
      	XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesbannedextensionlist);          
      }	
      
	function bannedextensionlist_switch(index){
      var XHR = new XHRConnection();
      var value='';
      if(document.getElementById('bannedextensionlist_id_'+index).checked){value=1;}else{value=0;}
      XHR.appendData('bannedextensionlist_switch',index);
      XHR.appendData('rule_main','$rule_main');
      XHR.appendData('enabled',value);
      XHR.sendAndLoad('$page', 'GET',x_bannedextensionlist_switch);
      }   

function bannedextension_listadd(){
      var XHR = new XHRConnection();
      XHR.appendData('rule_main','$rule_main');
      XHR.appendData('bannedextension_listadd',document.getElementById('extension_pattern').value);
      XHR.appendData('info',document.getElementById('extension_description').value);
      document.getElementById('main_extensions_bannedextensionslist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
      XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesbannedextensionlist);        
      }      
	
	

    
    RefreshBannedExtensionList();
	</script>
	
	";	

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
}
function main_extensions_bannedextensionslist_list($rule_main=null,$noecho=0){
	$q=new mysql();
	if($rule_main==null){$rule_main=$_GET["rule_main"];}
	$dans=new dansguardian_rules(null,$rule_main);
	writelogs("Loading RuleID=$rule_main",__FUNCTION__,__FILE__);
	$sql="SELECT * FROM dansguardian_files WHERE filename='bannedextensionlist' AND RuleID=$rule_main";
	writelogs("$sql",__FUNCTION__,__FILE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
$style=CellRollOver();
	$categ="
	<table style='width:99%' class=table_form>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$num=$ligne["ID"];
		$onoff=Field_checkbox("bannedextensionlist_id_$num",1,$ligne["enabled"],"bannedextensionlist_switch('$num')");
		$ext=$ligne["pattern"];
		$ext=str_replace('.','',$ext);
		
		$img="img/ext/def_small.gif";
		if(file_exists("img/ext/{$ext}_small.gif")){$img="img/ext/{$ext}_small.gif";}
		$categ=$categ . 
		"<tr $style>
		<td width=1%><IMG SRC='$img' id='image_{$num}'></td>
		<td width=1%>$onoff</td>
		<td width=1% style='font-size:12px'> <strong id='pattern_{$num}'>{$ligne["pattern"]}</strong></td>
		<td width=98% style='font-size:12px'><strong id='info_{$num}'>{$ligne["infos"]}</strong></td>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"bannedextensionlist_del('$hostname','$rule_main','$num')") ."</td>
		</tr>
		";
		}
	$categ=$categ . "</table>";	
	$tpl=new templates();
	$categ=$tpl->_ENGINE_parse_body($categ);
	if($noecho==1){return $categ;}
	echo $categ;
}
function main_extension_bannedextensionlist_add(){
	$dans=new dansguardian_rules(null,$_GET["rule_main"]);
	$rule_main=$_GET["rule_main"];		
	$ext=$_GET["bannedextension_listadd"];
	if($_GET["info"]==null){$_GET["info"]="no infos...";}
	$dans->Add_bannedextensionlist($rule_main,$ext,$_GET["info"],1);
	}
function main_extension_bannedextensionlist_switch(){
	$rule_main=$_GET["rule_main"];		
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->Edit_bannedextensionlist($_GET["bannedextensionlist_switch"],$_GET["enabled"]);
	}
function main_extention_bannedextensionlist_del(){
	$rule_main=$_GET["rule_main"];		
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	$dans->DelBannedExtensionList($rule_main,$_GET["bannedextensionlist_del"]);
	}		

?>