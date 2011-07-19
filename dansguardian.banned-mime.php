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
	if(isset($_GET["bannedMimeType_listadd"])){main_extension_bannedMimeTypelist_add();exit;}
	if(isset($_GET["bannedMimeTypelist_switch"])){main_extension_bannedMimeTypelist_switch();exit;}
	if(isset($_GET["bannedMimeTypelist_icon"])){main_extension_bannedMimeTypelist_icon();exit;}
	if(isset($_GET["bannedMimeTypelist_del"])){main_extention_bannedMimeTypelist_del();exit;}
	if(isset($_GET["main_extensions_bannedMimeTypelist_list"])){echo main_extensions_bannedMimeTypelist_list($_GET["rule_main"]);exit;}	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$rulename=base64_decode($_GET["rule-name"]);
	$title=$tpl->_ENGINE_parse_body("{BannedMimetype}","dansguardian.index.php");
	$html="
	function DANSGUARDIAN_LOAD_BANNEDMIME(){
		RTMMail(650,'$page?popup=yes&rule_main={$_GET["rule_main"]}','$title');
	
	}
	
var x_sectionrules_categoriesbannedMimeTypelist=function(obj){
      LoadAjax('main_extensions_bannedMimeTypelist_list','$page?rule_main={$_GET["rule_main"]}&main_extensions_bannedMimeTypelist_list=yes');
}

	var x_bannedMimeTypelist_switch= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		LoadAjax('main_extensions_bannedMimeTypelist_list','$page?rule_main={$_GET["rule_main"]}&main_extensions_bannedMimeTypelist_list=yes');
	}

function bannedMimeType_listadd(){
      var XHR = new XHRConnection();    
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.appendData('bannedMimeType_listadd',document.getElementById('extension_pattern').value);
      XHR.appendData('info',document.getElementById('extension_description').value);
      document.getElementById('main_extensions_bannedMimeTypelist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';   
      XHR.sendAndLoad('$page', 'GET',x_sectionrules_categoriesbannedMimeTypelist);        
      }
      
 function bannedMimeTypelist_del(hostname,rule_main,index){
      document.getElementById('image_' +index).innerHTML='';
      document.getElementById('pattern_' + index).innerHTML='';
      document.getElementById('info_' + index).innerHTML='';
      var XHR = new XHRConnection();
      XHR.appendData('bannedMimeTypelist_del',index);
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.sendAndLoad('$page', 'GET');          
      }  
      
function bannedMimeTypelist_switch(hostname,rule_main,index,value){
      var XHR = new XHRConnection();
      rule_main_mem=rule_main;
      XHR.appendData('bannedMimeTypelist_switch',index);
      XHR.appendData('rule_main','{$_GET["rule_main"]}');
      XHR.appendData('enabled',value);
      document.getElementById('main_extensions_bannedMimeTypelist_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
      XHR.sendAndLoad('$page', 'GET',x_bannedMimeTypelist_switch);
      
      }		      
	
	DANSGUARDIAN_LOAD_BANNEDMIME()";
	
	echo $html;
	
}

function popup(){
		
$users=new usersMenus();
	
	$rule_main=$_GET["rule_main"];
	if($rule_main=="DansDefault"){
		$rulename="Default Rule";
	}else{
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
		$dansg=new dansguardian($_GET["hostname"]);
		$rulename=$dansg->Master_rules_index[$rule_main];
	}
$html="
	<input type='hidden' name='rule_main' value='$rule_main'>
	<p class=caption>{BannedMimetype_explain}</p>
	<table style='width:100%' class='table_form'>
	<tr>
	<td nowrap>{BannedMimetype}</strong></td>
	<td>" . Field_text('extension_pattern',null,'width:100px')."</td>
	<td class=legend>{description}</strong></td>
	<td class=legend>" . Field_text('extension_description')."</td>
	<td>
	". imgtootltip("plus-24.png","{add_extension}","bannedMimeType_listadd()")."
	</td>
	</tr>
	</table><br>
	<div id='main_extensions_bannedMimeTypelist_list' style='width:100%;height:300px;overflow:auto'>
	" . main_extensions_bannedMimeTypelist_list($rule_main,1) . "</div>
	";	


	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
}
function main_extensions_bannedMimeTypelist_list($rule_main,$noecho=0){
		$q=new mysql();
	$sql="SELECT * FROM dansguardian_files WHERE filename='bannedmimetypelist' AND RuleID=$rule_main";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$style=CellRollOver();
	$categ="
	<table style='width:99%' class=table_form>";	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$num=$ligne["ID"];
		if($ligne["enabled"]==1){
			$onoff=imgtootltip("icon_ok.gif","{disable}","bannedMimeTypelist_switch('$hostname','$rule_main','$num','0')",null,"img_{$num}");
		}else{$onoff=imgtootltip("icon_err.gif","{enable}","bannedMimeTypelist_switch('$hostname','$rule_main','$num','1')",null,"img_{$num}");}
		
		$categ=$categ . 
		"<tr $style>
		<td width=1%><span id='image_{$num}'>$onoff</span></td>
		<td width=1%><strong id='pattern_{$num}' nowrap>{$ligne["pattern"]}</strong></td>
		<td width=98%><strong id='info_{$num}'>{$ligne["infos"]}</strong></td>
		<td width=1%>" . imgtootltip('x.gif','{delete}',"bannedMimeTypelist_del('$hostname','$rule_main','$num')") ."</td>
		</tr>
		";
		}
	$categ=$categ . "</table>";
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body($categ);}
	echo $tpl->_ENGINE_parse_body($categ);
	
}
function main_extension_bannedMimeTypelist_add(){
	$rule_main=$_GET["rule_main"];		
	$ext=$_GET["bannedMimeType_listadd"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	if($_GET["info"]==null){$_GET["info"]="no infos...";}
	$dans->Add_bannedmimetype($rule_main,$ext,$_GET["info"],1);	
}
function main_extension_bannedMimeTypelist_switch(){
	$rule_main=$_GET["rule_main"];		
	$index=$_GET["bannedMimeTypelist_switch"];
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	writelogs("Editing line number $index value={$_GET["enabled"]} RULE=$rule_main",__FUNCTION__,__FILE__,__LINE__);
	$dans->Edit_bannedMimeTypelist($index,$_GET["enabled"]);	
	}
	function main_extension_bannedMimeTypelist_icon(){
$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$rule_main=$_GET["rule_main"];	
	$index=$_GET["bannedMimeTypelist_icon"];
	$num=$index;
	$dans=new dansguardian_rules($_GET["hostname"],$rule_main);

	$sql="SELECT enabled FROM dansguardian_files WHERE ID=$index";
	$q=new mysql();
	$val=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	
if($val["enabled"]==1){
		$onoff=imgtootltip("icon_ok.gif","{disable}","bannedMimeTypelist_switch('$hostname','$rule_main','$num','0')",null,"img_{$num}");
		$dans->Edit_bannedMimeTypelist($num,0);
		}
		else{
		$onoff=imgtootltip("icon_err.gif","{enable}","bannedMimeTypelist_switch('$hostname','$rule_main','$num','1')",null,"img_{$num}");
		$dans->Edit_bannedMimeTypelist($num,1);
		}
			
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($onoff);	
	
}	
function main_extention_bannedMimeTypelist_del(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$rule_main=$_GET["rule_main"];		
	$index=$_GET["bannedMimeTypelist_del"];
	if($rule_main=="DansDefault"){
		$dans=new dansguardian_default_rules();
	}else{
		$dans=new dansguardian_rules($_GET["hostname"],$rule_main);
	}
	$dans->DelBannedMimeTypeList($rule_main,$index);
	}	

?>