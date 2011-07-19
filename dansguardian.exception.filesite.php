<?php
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
	if(isset($_GET["ExeptionFileSiteList_listadd"])){main_ExeptionFileSiteList_listadd();exit;}
	if(isset($_GET["ExeptionFileSiteList_icon"])){main_ExeptionFileSiteList_icon();exit;}
	if(isset($_GET["ExeptionFileSiteList_del"])){main_ExeptionFileSiteList_del();exit;}
	if(isset($_GET["main_ExeptionFileSiteList_list"])){echo main_ExeptionFileSiteList_list($_GET["rule_main"]);exit;}	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{ExeptionFileSiteList}","dansguardian.index.php");
	$html="
	function DANSGUARDIAN_LOAD_EXCFILESITE(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
var x_sectionrules_categoriesExeptionFileSiteList=function(obj){
      LoadAjax('main_ExeptionFileSiteList_list','$page?rule_main={$_GET["rule_main"]}&main_ExeptionFileSiteList_list=yes');
}	
	
function ExeptionFileSiteList_listadd(hostname,rule_main){
      var XHR = new XHRConnection();     
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.appendData('ExeptionFileSiteList_listadd',document.getElementById('extension_pattern').value);
      XHR.appendData('info',document.getElementById('extension_description').value);
      document.getElementById('main_ExeptionFileSiteList_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
      XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesExeptionFileSiteList);        
      }
function ExeptionFileSiteList_switch(hostname,rule_main,index,value){
      var XHR = new XHRConnection();
      XHR.appendData('ExeptionFileSiteList_switch',index);
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.appendData('enabled',value);
      XHR.sendAndLoad('dansguardian.index.php', 'GET');
      LoadAjax('image_'+index,'$page?rule_main={$_GET["rule_main"]}&ExeptionFileSiteList_icon=' + index+'&hostname='+ hostname);
      }      
 function ExeptionFileSiteList_del(hostname,rule_main,index){
      document.getElementById('image_' +index).innerHTML='';
      document.getElementById('pattern_' + index).innerHTML='';
      document.getElementById('info_' + index).innerHTML='';
      var XHR = new XHRConnection();
      XHR.appendData('hostname',hostname);
      XHR.appendData('ExeptionFileSiteList_del',index);
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.sendAndLoad('$page', 'GET');          
      }	      
	
	DANSGUARDIAN_LOAD_EXCFILESITE()";
	
	echo $html;
	
}
function popup(){
	
$users=new usersMenus();
	
	$rule_main=$_GET["rule_main"];
	
	if($rule_main=="DansDefault"){
		$dans=new dansguardian_default_rules();
		$rulename="Default rule";
	}else{
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
		$dansg=new dansguardian($_GET["hostname"]);
		$rulename=$dansg->Master_rules_index[$rule_main];		
	}
	
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<p class=caption>{ExeptionFileSiteList_explain}</p>
	<br>
	<table style='width:100%'>
	<tr>
	<td nowrap class=legend>{webiste}</strong></td>
	<td>" . Field_text('extension_pattern',null,'width:200px')."</td>
	<td class=legend>{description}</strong></td>
	<td>" . Field_text('extension_description')."</td>
	<td>
	". imgtootltip("plus-24.png","{add_category}","ExeptionFileSiteList_listadd()")."
	
	</td>
	</tr>
	</table><br>
	<div id='main_ExeptionFileSiteList_list' style='width:100%;height:300px;overflow:auto'>" . main_ExeptionFileSiteList_list($rule_main,1)."</div>
	";	


	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");		
}
function main_ExeptionFileSiteList_list($rule_main,$noecho=0){

	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);	
	$q=new mysql();
	$sql="SELECT * FROM dansguardian_files WHERE filename='exceptionfilesitelist' AND RuleID=$rule_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$style=CellRollOver();
	$categ="
	<table style='width:99%' class=table_form>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$num=$ligne["ID"];
		if($ligne["enabled"]==1){
			$onoff=imgtootltip("icon_ok.gif","{disable}","ExeptionFileSiteList_switch('$hostname','$rule_main','$num','0')",null,"img_{$num}");
		}else{$onoff=imgtootltip("icon_err.gif","{enable}","ExeptionFileSiteList_switch('$hostname','$rule_main','$num','1')",null,"img_{$num}");}
		
		$categ=$categ . 
		"<tr $style>
		<td width=1%><span id='image_{$num}'>$onoff</span></td>
		<td width=1%><strong id='pattern_{$num}'>{$ligne["pattern"]}</strong></td>
		<td width=98%><strong id='info_{$num}'>{$ligne["infos"]}</strong></td>
		<td width=1%>" . imgtootltip('x.gif','{delete}',"ExeptionFileSiteList_del('$hostname','$rule_main','$num')") ."</td>
		</tr>
		";
		}
	$categ=$categ . "</table>";
	
$tpl=new templates();
if($noecho==1){return $tpl->_ENGINE_parse_body($categ);}
echo $tpl->_ENGINE_parse_body($categ);
	
	
}


	

	
function main_ExeptionFileSiteList_listadd(){
	$rule_main=$_GET["rule_main"];		
	$ext=$_GET["ExeptionFileSiteList_listadd"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	if($_GET["info"]==null){$_GET["info"]="no infos...";}
	$dans->Add_ExeptionFileSiteList($rule_main,$ext,$_GET["info"],1);	
	}
	
	function main_ExeptionFileSiteList_del(){
	$users=new usersMenus();
	
	$rule_main=$_GET["rule_main"];		
	$index=$_GET["ExeptionFileSiteList_del"];
	if($rule_main=="DansDefault"){
		$dans=new dansguardian_default_rules();
	}else{
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	}
	
	$dans->DelExeptionFileSiteList($index);
	}
	



	



function main_ExeptionFileSiteList_icon(){

	$rule_main=$_GET["rule_main"];	
	$index=$_GET["ExeptionFileSiteList_icon"];
	$num=$index;
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	
	$sql="SELECT enabled FROM dansguardian_files WHERE ID=$index";
	$q=new mysql();
	$val=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	

	 if($val["enabled"]==1){
			$onoff=imgtootltip("icon_ok.gif","{disable}","ExeptionFileSiteList_switch('$hostname','$rule_main','$num','0')",null,"img_{$num}");
			$dans->Edit_ExeptionFileSiteList($_GET["ExeptionFileSiteList_icon"],0);
		}else{
			$onoff=imgtootltip("icon_err.gif","{enable}","ExeptionFileSiteList_switch('$hostname','$rule_main','$num','1')",null,"img_{$num}");
			$dans->Edit_ExeptionFileSiteList($_GET["ExeptionFileSiteList_icon"],1);
		}
			
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($onoff);		
	
}	

?>